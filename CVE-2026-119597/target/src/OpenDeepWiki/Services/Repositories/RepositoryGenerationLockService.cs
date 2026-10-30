using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Repositories;

public interface IRepositoryGenerationLockService
{
    Task<RepositoryGenerationLock?> GetLockAsync(
        string repositoryId,
        CancellationToken cancellationToken = default);

    Task<bool> TryAcquireAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        CancellationToken cancellationToken = default,
        bool bindToCurrentInstance = false);

    Task HeartbeatAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default);

    Task UnbindAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default);

    Task ReleaseAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default);

    Task RecoverStaleLocksAsync(
        IContext context,
        CancellationToken cancellationToken = default);
}

public sealed class RepositoryGenerationLockService : IRepositoryGenerationLockService
{
    private readonly IContext _rootContext;
    private readonly WikiGenerationInstanceIdentity _instanceIdentity;
    private readonly IOptionsMonitor<WikiGeneratorOptions>? _wikiOptions;

    public RepositoryGenerationLockService(IContext rootContext)
        : this(rootContext, new WikiGenerationInstanceIdentity(), null)
    {
    }

    public RepositoryGenerationLockService(
        IContext rootContext,
        WikiGenerationInstanceIdentity instanceIdentity,
        IOptionsMonitor<WikiGeneratorOptions>? wikiOptions)
    {
        _rootContext = rootContext;
        _instanceIdentity = instanceIdentity;
        _wikiOptions = wikiOptions;
    }

    public Task<RepositoryGenerationLock?> GetLockAsync(
        string repositoryId,
        CancellationToken cancellationToken = default)
    {
        return _rootContext.RepositoryGenerationLocks
            .AsNoTracking()
            .FirstOrDefaultAsync(item => item.RepositoryId == repositoryId && !item.IsDeleted, cancellationToken);
    }

    public async Task<bool> TryAcquireAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        CancellationToken cancellationToken = default,
        bool bindToCurrentInstance = false)
    {
        var existing = await context.RepositoryGenerationLocks
            .FirstOrDefaultAsync(item => item.RepositoryId == repositoryId && !item.IsDeleted, cancellationToken);

        if (existing is null)
        {
            var generationLock = CreateLock(repositoryId, ownerType, ownerId, scope, bindToCurrentInstance);
            context.RepositoryGenerationLocks.Add(generationLock);

            try
            {
                await context.SaveChangesAsync(cancellationToken);
                return true;
            }
            catch (DbUpdateException)
            {
                if (context is DbContext dbContext)
                {
                    dbContext.Entry(generationLock).State = EntityState.Detached;
                }

                return false;
            }
        }

        if (IsStale(existing))
        {
            await RecoverLockWorkAsync(context, existing, cancellationToken);
            ApplyOwnership(existing, ownerType, ownerId, scope, bindToCurrentInstance);
            await context.SaveChangesAsync(cancellationToken);
            return true;
        }

        if (existing.OwnerType != ownerType || existing.OwnerId != ownerId)
        {
            return false;
        }

        if (!bindToCurrentInstance)
        {
            return true;
        }

        if (CanBindToCurrentInstance(existing))
        {
            BindToCurrentInstance(existing);
            await context.SaveChangesAsync(cancellationToken);
            return true;
        }

        return false;
    }

    public async Task HeartbeatAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        var instanceId = _instanceIdentity.InstanceId;

        if (EfContextCapabilities.SupportsExecuteUpdate(context))
        {
            await context.RepositoryGenerationLocks
                .Where(item =>
                    item.RepositoryId == repositoryId &&
                    item.OwnerType == ownerType &&
                    item.OwnerId == ownerId &&
                    item.InstanceId == instanceId &&
                    !item.IsDeleted)
                .ExecuteUpdateAsync(
                    setters => setters
                        .SetProperty(item => item.HeartbeatAt, now)
                        .SetProperty(item => item.UpdatedAt, now),
                    cancellationToken);
            return;
        }

        var generationLock = await context.RepositoryGenerationLocks
            .FirstOrDefaultAsync(item =>
                item.RepositoryId == repositoryId &&
                item.OwnerType == ownerType &&
                item.OwnerId == ownerId &&
                item.InstanceId == instanceId &&
                !item.IsDeleted,
                cancellationToken);

        if (generationLock is null)
        {
            return;
        }

        generationLock.HeartbeatAt = now;
        generationLock.UpdateTimestamp();
        await context.SaveChangesAsync(cancellationToken);
    }

    public async Task UnbindAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default)
    {
        var generationLock = await context.RepositoryGenerationLocks
            .FirstOrDefaultAsync(item =>
                item.RepositoryId == repositoryId &&
                item.OwnerType == ownerType &&
                item.OwnerId == ownerId &&
                item.InstanceId == _instanceIdentity.InstanceId &&
                !item.IsDeleted,
                cancellationToken);

        if (generationLock is null)
        {
            return;
        }

        generationLock.InstanceId = null;
        generationLock.HeartbeatAt = null;
        generationLock.UpdateTimestamp();
        await context.SaveChangesAsync(cancellationToken);
    }

    public async Task ReleaseAsync(
        IContext context,
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        CancellationToken cancellationToken = default)
    {
        var generationLock = await context.RepositoryGenerationLocks
            .FirstOrDefaultAsync(item =>
                item.RepositoryId == repositoryId &&
                item.OwnerType == ownerType &&
                item.OwnerId == ownerId &&
                !item.IsDeleted,
                cancellationToken);

        if (generationLock is null)
        {
            return;
        }

        context.RepositoryGenerationLocks.Remove(generationLock);
        await context.SaveChangesAsync(cancellationToken);
    }

    public async Task RecoverStaleLocksAsync(
        IContext context,
        CancellationToken cancellationToken = default)
    {
        var staleBefore = GetStaleBefore();
        var staleLocks = await context.RepositoryGenerationLocks
            .Where(item =>
                !item.IsDeleted &&
                item.InstanceId != null &&
                item.HeartbeatAt != null &&
                item.HeartbeatAt < staleBefore)
            .Take(50)
            .ToListAsync(cancellationToken);

        foreach (var generationLock in staleLocks)
        {
            await RecoverLockWorkAsync(context, generationLock, cancellationToken);
            generationLock.InstanceId = null;
            generationLock.HeartbeatAt = null;
            generationLock.UpdateTimestamp();
        }

        if (staleLocks.Count > 0)
        {
            await context.SaveChangesAsync(cancellationToken);
        }
    }

    private RepositoryGenerationLock CreateLock(
        string repositoryId,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        bool bindToCurrentInstance)
    {
        var now = DateTime.UtcNow;
        return new RepositoryGenerationLock
        {
            Id = Guid.NewGuid().ToString(),
            RepositoryId = repositoryId,
            OwnerType = ownerType,
            OwnerId = ownerId,
            Scope = scope,
            AcquiredAt = now,
            CreatedAt = now,
            InstanceId = bindToCurrentInstance ? _instanceIdentity.InstanceId : null,
            HeartbeatAt = bindToCurrentInstance ? now : null
        };
    }

    private void ApplyOwnership(
        RepositoryGenerationLock generationLock,
        RepositoryGenerationLockOwnerType ownerType,
        string ownerId,
        RepositoryGenerationLockScope scope,
        bool bindToCurrentInstance)
    {
        generationLock.OwnerType = ownerType;
        generationLock.OwnerId = ownerId;
        generationLock.Scope = scope;
        generationLock.AcquiredAt = DateTime.UtcNow;
        generationLock.InstanceId = bindToCurrentInstance ? _instanceIdentity.InstanceId : null;
        generationLock.HeartbeatAt = bindToCurrentInstance ? DateTime.UtcNow : null;
        generationLock.UpdateTimestamp();
    }

    private void BindToCurrentInstance(RepositoryGenerationLock generationLock)
    {
        var now = DateTime.UtcNow;
        generationLock.InstanceId = _instanceIdentity.InstanceId;
        generationLock.HeartbeatAt = now;
        generationLock.UpdateTimestamp();
    }

    private bool CanBindToCurrentInstance(RepositoryGenerationLock generationLock)
    {
        return string.IsNullOrWhiteSpace(generationLock.InstanceId) ||
               string.Equals(generationLock.InstanceId, _instanceIdentity.InstanceId, StringComparison.Ordinal) ||
               IsStale(generationLock);
    }

    private bool IsStale(RepositoryGenerationLock generationLock)
    {
        return !string.IsNullOrWhiteSpace(generationLock.InstanceId) &&
               generationLock.HeartbeatAt is { } heartbeat &&
               heartbeat < GetStaleBefore();
    }

    private DateTime GetStaleBefore()
    {
        var timeoutSeconds = _wikiOptions?.CurrentValue.GetGenerationLeaseTimeoutSeconds() ??
                             WikiGeneratorOptions.DefaultGenerationLeaseTimeoutSeconds;
        return DateTime.UtcNow.AddSeconds(-timeoutSeconds);
    }

    private static async Task RecoverLockWorkAsync(
        IContext context,
        RepositoryGenerationLock generationLock,
        CancellationToken cancellationToken)
    {
        if (generationLock.OwnerType == RepositoryGenerationLockOwnerType.Repository)
        {
            var repository = await context.Repositories
                .FirstOrDefaultAsync(item => item.Id == generationLock.RepositoryId && !item.IsDeleted, cancellationToken);
            if (repository is { Status: RepositoryStatus.Processing })
            {
                repository.Status = RepositoryStatus.Pending;
                repository.UpdateTimestamp();
            }

            return;
        }

        if (generationLock.OwnerType == RepositoryGenerationLockOwnerType.BranchTask)
        {
            var task = await context.BranchGenerationTasks
                .FirstOrDefaultAsync(item => item.Id == generationLock.OwnerId && !item.IsDeleted, cancellationToken);
            if (task is { Status: BranchGenerationTaskStatus.Processing })
            {
                task.Status = BranchGenerationTaskStatus.Pending;
                task.StartedAt = null;
                task.ErrorMessage = null;
                task.UpdateTimestamp();
            }

            var branch = await context.RepositoryBranches
                .FirstOrDefaultAsync(item =>
                    item.Id == (task != null ? task.BranchId : string.Empty) && !item.IsDeleted,
                    cancellationToken);
            if (branch is { GenerationStatus: BranchGenerationTaskStatus.Processing })
            {
                branch.GenerationStatus = BranchGenerationTaskStatus.Pending;
                branch.LastGenerationError = null;
                branch.UpdateTimestamp();
            }

            return;
        }

        if (generationLock.OwnerType == RepositoryGenerationLockOwnerType.IncrementalTask)
        {
            var task = await context.IncrementalUpdateTasks
                .FirstOrDefaultAsync(item => item.Id == generationLock.OwnerId && !item.IsDeleted, cancellationToken);
            if (task is { Status: IncrementalUpdateStatus.Processing })
            {
                task.Status = IncrementalUpdateStatus.Pending;
                task.StartedAt = null;
                task.ErrorMessage = null;
                task.UpdatedAt = DateTime.UtcNow;
            }
        }
    }
}
