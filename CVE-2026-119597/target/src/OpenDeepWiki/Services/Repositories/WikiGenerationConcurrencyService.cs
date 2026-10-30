using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Repositories;

public enum WikiGenerationAcquireStatus
{
    Acquired = 0,
    RepositoryBusy = 1,
    ClusterFull = 2
}

public sealed class WikiGenerationWorkLease
{
    public required string SlotId { get; init; }
    public required int SlotIndex { get; init; }
    public required string RepositoryId { get; init; }
    public required RepositoryGenerationLockOwnerType OwnerType { get; init; }
    public required string OwnerId { get; init; }
    public required RepositoryGenerationLockScope Scope { get; init; }
    public required WikiGenerationWorkType WorkType { get; init; }
}

public interface IWikiGenerationCoordinator
{
    Task RecoverStaleWorkAsync(IContext context, CancellationToken cancellationToken = default);

    Task<(WikiGenerationAcquireStatus Status, WikiGenerationWorkLease? Lease)> TryBeginAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        WikiGenerationWorkType workType,
        CancellationToken cancellationToken = default);

    Task HeartbeatAsync(
        IContext context,
        WikiGenerationWorkLease lease,
        CancellationToken cancellationToken = default);

    Task ReleaseAsync(
        IContext context,
        WikiGenerationWorkLease lease,
        CancellationToken cancellationToken = default);
}

public sealed class WikiGenerationCoordinator(
    IRepositoryGenerationLockService lockService,
    WikiGenerationInstanceIdentity instanceIdentity,
    IOptionsMonitor<WikiGeneratorOptions> wikiOptions) : IWikiGenerationCoordinator
{
    public async Task RecoverStaleWorkAsync(IContext context, CancellationToken cancellationToken = default)
    {
        await RecoverStaleSlotsAsync(context, cancellationToken);
        await lockService.RecoverStaleLocksAsync(context, cancellationToken);
    }

    public async Task<(WikiGenerationAcquireStatus Status, WikiGenerationWorkLease? Lease)> TryBeginAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        WikiGenerationWorkType workType,
        CancellationToken cancellationToken = default)
    {
        var existingLock = await context.RepositoryGenerationLocks
            .AsNoTracking()
            .FirstOrDefaultAsync(item => item.RepositoryId == repositoryId && !item.IsDeleted, cancellationToken);
        var hadReservation = existingLock is not null &&
                             existingLock.OwnerType == ownerType &&
                             existingLock.OwnerId == ownerId;

        var lockAcquired = await lockService.TryAcquireAsync(
            context,
            repositoryId,
            ownerType,
            ownerId,
            scope,
            cancellationToken,
            bindToCurrentInstance: true);

        if (!lockAcquired)
        {
            return (WikiGenerationAcquireStatus.RepositoryBusy, null);
        }

        var slot = await TryClaimSlotAsync(context, repositoryId, ownerId, workType, cancellationToken);
        if (slot is null)
        {
            if (hadReservation)
            {
                await lockService.UnbindAsync(context, repositoryId, ownerType, ownerId, cancellationToken);
            }
            else
            {
                await lockService.ReleaseAsync(context, repositoryId, ownerType, ownerId, cancellationToken);
            }

            return (WikiGenerationAcquireStatus.ClusterFull, null);
        }

        return (WikiGenerationAcquireStatus.Acquired, new WikiGenerationWorkLease
        {
            SlotId = slot.Id,
            SlotIndex = slot.SlotIndex,
            RepositoryId = repositoryId,
            OwnerType = ownerType,
            OwnerId = ownerId,
            Scope = scope,
            WorkType = workType
        });
    }

    public async Task HeartbeatAsync(
        IContext context,
        WikiGenerationWorkLease lease,
        CancellationToken cancellationToken = default)
    {
        await lockService.HeartbeatAsync(
            context,
            lease.RepositoryId,
            lease.OwnerType,
            lease.OwnerId,
            cancellationToken);

        var now = DateTime.UtcNow;
        var instanceId = instanceIdentity.InstanceId;

        if (EfContextCapabilities.SupportsExecuteUpdate(context))
        {
            await context.WikiGenerationSlots
                .Where(item => item.Id == lease.SlotId && item.InstanceId == instanceId && !item.IsDeleted)
                .ExecuteUpdateAsync(
                    setters => setters
                        .SetProperty(item => item.HeartbeatAt, now)
                        .SetProperty(item => item.UpdatedAt, now),
                    cancellationToken);
            return;
        }

        var slot = await context.WikiGenerationSlots
            .FirstOrDefaultAsync(item => item.Id == lease.SlotId && item.InstanceId == instanceId && !item.IsDeleted, cancellationToken);
        if (slot is null)
        {
            return;
        }

        slot.HeartbeatAt = now;
        slot.UpdateTimestamp();
        await context.SaveChangesAsync(cancellationToken);
    }

    public async Task ReleaseAsync(
        IContext context,
        WikiGenerationWorkLease lease,
        CancellationToken cancellationToken = default)
    {
        try
        {
            await ClearSlotAsync(context, lease.SlotId, cancellationToken);
        }
        finally
        {
            await lockService.ReleaseAsync(
                context,
                lease.RepositoryId,
                lease.OwnerType,
                lease.OwnerId,
                cancellationToken);
        }
    }

    private async Task RecoverStaleSlotsAsync(IContext context, CancellationToken cancellationToken)
    {
        var staleBefore = GetStaleBefore();
        var staleSlots = await context.WikiGenerationSlots
            .Where(item =>
                !item.IsDeleted &&
                item.InstanceId != null &&
                item.HeartbeatAt != null &&
                item.HeartbeatAt < staleBefore)
            .Take(50)
            .ToListAsync(cancellationToken);

        foreach (var slot in staleSlots)
        {
            ClearHolder(slot);
        }

        if (staleSlots.Count > 0)
        {
            await context.SaveChangesAsync(cancellationToken);
        }
    }

    private async Task<WikiGenerationSlot?> TryClaimSlotAsync(
        IContext context,
        string repositoryId,
        string ownerId,
        WikiGenerationWorkType workType,
        CancellationToken cancellationToken)
    {
        await EnsureSlotsAsync(context, cancellationToken);

        var max = wikiOptions.CurrentValue.GetMaxConcurrentGenerations();
        var staleBefore = GetStaleBefore();
        var candidates = await context.WikiGenerationSlots
            .Where(item =>
                !item.IsDeleted &&
                item.SlotIndex < max &&
                (item.InstanceId == null || item.HeartbeatAt == null || item.HeartbeatAt < staleBefore))
            .OrderBy(item => item.SlotIndex)
            .ToListAsync(cancellationToken);

        foreach (var candidate in candidates)
        {
            if (await TryBindSlotAsync(context, candidate, repositoryId, ownerId, workType, staleBefore, cancellationToken))
            {
                return candidate;
            }
        }

        return null;
    }

    private async Task EnsureSlotsAsync(IContext context, CancellationToken cancellationToken)
    {
        var max = wikiOptions.CurrentValue.GetMaxConcurrentGenerations();
        var existingIndexes = await context.WikiGenerationSlots
            .Where(item => !item.IsDeleted && item.SlotIndex < max)
            .Select(item => item.SlotIndex)
            .ToListAsync(cancellationToken);

        var existing = existingIndexes.ToHashSet();
        var added = false;
        for (var index = 0; index < max; index++)
        {
            if (existing.Contains(index))
            {
                continue;
            }

            context.WikiGenerationSlots.Add(new WikiGenerationSlot
            {
                Id = Guid.NewGuid().ToString(),
                SlotIndex = index,
                CreatedAt = DateTime.UtcNow
            });
            added = true;
        }

        if (!added)
        {
            return;
        }

        try
        {
            await context.SaveChangesAsync(cancellationToken);
        }
        catch (DbUpdateException)
        {
            if (context is DbContext dbContext)
            {
                foreach (var entry in dbContext.ChangeTracker.Entries<WikiGenerationSlot>()
                             .Where(entry => entry.State == EntityState.Added)
                             .ToList())
                {
                    entry.State = EntityState.Detached;
                }
            }
        }
    }

    private async Task<bool> TryBindSlotAsync(
        IContext context,
        WikiGenerationSlot slot,
        string repositoryId,
        string ownerId,
        WikiGenerationWorkType workType,
        DateTime staleBefore,
        CancellationToken cancellationToken)
    {
        var now = DateTime.UtcNow;
        var instanceId = instanceIdentity.InstanceId;

        if (EfContextCapabilities.SupportsExecuteUpdate(context))
        {
            var updated = await context.WikiGenerationSlots
                .Where(item =>
                    item.Id == slot.Id &&
                    !item.IsDeleted &&
                    (item.InstanceId == null || item.HeartbeatAt == null || item.HeartbeatAt < staleBefore))
                .ExecuteUpdateAsync(
                    setters => setters
                        .SetProperty(item => item.InstanceId, instanceId)
                        .SetProperty(item => item.RepositoryId, repositoryId)
                        .SetProperty(item => item.OwnerId, ownerId)
                        .SetProperty(item => item.WorkType, workType)
                        .SetProperty(item => item.AcquiredAt, now)
                        .SetProperty(item => item.HeartbeatAt, now)
                        .SetProperty(item => item.UpdatedAt, now),
                    cancellationToken);

            if (updated != 1)
            {
                return false;
            }

            slot.InstanceId = instanceId;
            slot.RepositoryId = repositoryId;
            slot.OwnerId = ownerId;
            slot.WorkType = workType;
            slot.AcquiredAt = now;
            slot.HeartbeatAt = now;
            return true;
        }

        if (slot.IsHeld && slot.HeartbeatAt is { } heartbeat && heartbeat >= staleBefore)
        {
            return false;
        }

        slot.InstanceId = instanceId;
        slot.RepositoryId = repositoryId;
        slot.OwnerId = ownerId;
        slot.WorkType = workType;
        slot.AcquiredAt = now;
        slot.HeartbeatAt = now;
        slot.UpdateTimestamp();
        await context.SaveChangesAsync(cancellationToken);
        return true;
    }

    private async Task ClearSlotAsync(IContext context, string slotId, CancellationToken cancellationToken)
    {
        if (EfContextCapabilities.SupportsExecuteUpdate(context))
        {
            await context.WikiGenerationSlots
                .Where(item => item.Id == slotId && !item.IsDeleted)
                .ExecuteUpdateAsync(
                    setters => setters
                        .SetProperty(item => item.InstanceId, (string?)null)
                        .SetProperty(item => item.RepositoryId, (string?)null)
                        .SetProperty(item => item.OwnerId, (string?)null)
                        .SetProperty(item => item.WorkType, (WikiGenerationWorkType?)null)
                        .SetProperty(item => item.AcquiredAt, (DateTime?)null)
                        .SetProperty(item => item.HeartbeatAt, (DateTime?)null)
                        .SetProperty(item => item.UpdatedAt, DateTime.UtcNow),
                    cancellationToken);
            return;
        }

        var slot = await context.WikiGenerationSlots
            .FirstOrDefaultAsync(item => item.Id == slotId && !item.IsDeleted, cancellationToken);
        if (slot is null)
        {
            return;
        }

        ClearHolder(slot);
        await context.SaveChangesAsync(cancellationToken);
    }

    private static void ClearHolder(WikiGenerationSlot slot)
    {
        slot.InstanceId = null;
        slot.RepositoryId = null;
        slot.OwnerId = null;
        slot.WorkType = null;
        slot.AcquiredAt = null;
        slot.HeartbeatAt = null;
        slot.UpdateTimestamp();
    }

    private DateTime GetStaleBefore()
    {
        return DateTime.UtcNow.AddSeconds(-wikiOptions.CurrentValue.GetGenerationLeaseTimeoutSeconds());
    }
}
