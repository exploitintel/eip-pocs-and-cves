using System.Collections.Concurrent;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Repositories;

public sealed class BranchGenerationWorker(
    IServiceScopeFactory scopeFactory,
    ILogger<BranchGenerationWorker> logger,
    IOptionsMonitor<WikiGeneratorOptions> wikiOptions) : BackgroundService
{
    private static readonly TimeSpan PollingInterval = TimeSpan.FromSeconds(15);
    private readonly ConcurrentDictionary<string, Task> _inFlight = new();

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        logger.LogInformation(
            "Branch generation worker started. Polling interval: {PollingInterval}s, MaxConcurrentGenerations: {MaxConcurrent}",
            PollingInterval.TotalSeconds,
            wikiOptions.CurrentValue.GetMaxConcurrentGenerations());

        try
        {
            while (!stoppingToken.IsCancellationRequested)
            {
                try
                {
                    await DispatchPendingTasksAsync(stoppingToken);
                }
                catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
                {
                    break;
                }
                catch (Exception ex)
                {
                    logger.LogError(ex, "Branch generation polling failed");
                }

                await Task.Delay(PollingInterval, stoppingToken);
            }
        }
        finally
        {
            await WaitForInFlightAsync();
        }
    }

    private async Task DispatchPendingTasksAsync(CancellationToken stoppingToken)
    {
        PruneCompletedJobs();

        using var scope = scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();
        var coordinator = scope.ServiceProvider.GetRequiredService<IWikiGenerationCoordinator>();
        var processingLogService = scope.ServiceProvider.GetService<IProcessingLogService>();

        await coordinator.RecoverStaleWorkAsync(context, stoppingToken);
        await RecoverProcessingTasksWithoutLocksAsync(context, processingLogService, stoppingToken);

        var inFlightIds = _inFlight.Keys.ToHashSet();
        var pendingTasks = await context.BranchGenerationTasks
            .AsNoTracking()
            .Where(item => !item.IsDeleted &&
                           item.Status == BranchGenerationTaskStatus.Pending &&
                           !inFlightIds.Contains(item.Id))
            .OrderByDescending(item => item.Priority)
            .ThenBy(item => item.CreatedAt)
            .Take(20)
            .ToListAsync(stoppingToken);

        foreach (var task in pendingTasks)
        {
            stoppingToken.ThrowIfCancellationRequested();

            var (status, lease) = await coordinator.TryBeginAsync(
                context,
                task.RepositoryId,
                RepositoryGenerationLockOwnerType.BranchTask,
                task.Id,
                RepositoryGenerationLockScope.Branch,
                WikiGenerationWorkType.BranchTask,
                stoppingToken);

            if (status == WikiGenerationAcquireStatus.ClusterFull)
            {
                logger.LogInformation(
                    "Wiki generation cluster is full. MaxConcurrentGenerations: {MaxConcurrent}",
                    wikiOptions.CurrentValue.GetMaxConcurrentGenerations());
                break;
            }

            if (status != WikiGenerationAcquireStatus.Acquired || lease is null)
            {
                logger.LogDebug(
                    "Skipping branch generation task because the repository is busy. TaskId: {TaskId}, RepositoryId: {RepositoryId}",
                    task.Id, task.RepositoryId);
                continue;
            }

            var job = ProcessTaskJobAsync(task.Id, lease, stoppingToken);
            if (!_inFlight.TryAdd(task.Id, job))
            {
                await ReleaseLeaseAsync(lease);
                continue;
            }

            _ = job.ContinueWith(
                _ => _inFlight.TryRemove(task.Id, out var _),
                CancellationToken.None,
                TaskContinuationOptions.ExecuteSynchronously,
                TaskScheduler.Default);
        }
    }

    private async Task ProcessTaskJobAsync(
        string taskId,
        WikiGenerationWorkLease lease,
        CancellationToken stoppingToken)
    {
        using var scope = scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();
        var coordinator = scope.ServiceProvider.GetRequiredService<IWikiGenerationCoordinator>();
        var branchProcessor = scope.ServiceProvider.GetRequiredService<IRepositoryBranchProcessor>();
        var processingLogService = scope.ServiceProvider.GetService<IProcessingLogService>();

        await using var heartbeat = WikiGenerationHeartbeat.Start(
            scopeFactory,
            lease,
            wikiOptions.CurrentValue,
            logger,
            stoppingToken);

        var claim = await TryClaimTaskAsync(context, taskId, stoppingToken);
        if (claim is null)
        {
            logger.LogInformation("Branch generation task was not claimed. TaskId: {TaskId}", taskId);
            await coordinator.ReleaseAsync(context, lease, CancellationToken.None);
            return;
        }

        var task = claim.Task;

        try
        {
            var repository = claim.Repository;
            var branch = claim.Branch;

            if (repository is null || branch is null)
            {
                throw new InvalidOperationException($"Repository or branch not found. RepositoryId: {task.RepositoryId}, BranchId: {task.BranchId}");
            }

            if (processingLogService is not null)
            {
                await processingLogService.LogAsync(
                    task.RepositoryId,
                    task.BranchId,
                    task.Id,
                    ProcessingStep.Workspace,
                    $"Starting branch full generation: {branch.BranchName}",
                    cancellationToken: stoppingToken);
            }

            var targetCommitId = await branchProcessor.ProcessBranchAsync(
                context,
                repository,
                branch,
                task.Id,
                forceFullGeneration: true,
                stoppingToken);

            task.TargetCommitId = targetCommitId;
            task.Status = BranchGenerationTaskStatus.Completed;
            task.CompletedAt = DateTime.UtcNow;
            task.UpdateTimestamp();

            branch.GenerationStatus = BranchGenerationTaskStatus.Completed;
            branch.LastGenerationTaskId = task.Id;
            branch.LastGenerationError = null;
            branch.LastGenerationCompletedAt = task.CompletedAt;
            branch.UpdateTimestamp();

            await context.SaveChangesAsync(stoppingToken);
        }
        catch (OperationCanceledException ex) when (stoppingToken.IsCancellationRequested)
        {
            logger.LogWarning(ex, "Branch generation task cancelled while processing. TaskId: {TaskId}", task.Id);
            await MarkTaskFailedAsync(
                context,
                processingLogService,
                task,
                "Branch generation was cancelled while processing",
                CancellationToken.None);
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Branch generation task failed. TaskId: {TaskId}", task.Id);
            await MarkTaskFailedAsync(context, processingLogService, task, ex.Message, CancellationToken.None);
        }
        finally
        {
            await coordinator.ReleaseAsync(context, lease, CancellationToken.None);
        }
    }

    private async Task<ClaimedBranchGenerationTask?> TryClaimTaskAsync(
        IContext context,
        string taskId,
        CancellationToken stoppingToken)
    {
        var now = DateTime.UtcNow;

        if (EfContextCapabilities.SupportsExecuteUpdate(context))
        {
            await using var transaction = await ((DbContext)context).Database.BeginTransactionAsync(stoppingToken);
            var updatedRows = await context.BranchGenerationTasks
                .Where(item => item.Id == taskId &&
                               !item.IsDeleted &&
                               item.Status == BranchGenerationTaskStatus.Pending)
                .ExecuteUpdateAsync(
                    setters => setters
                        .SetProperty(item => item.Status, BranchGenerationTaskStatus.Processing)
                        .SetProperty(item => item.StartedAt, now)
                        .SetProperty(item => item.CompletedAt, (DateTime?)null)
                        .SetProperty(item => item.ErrorMessage, (string?)null)
                        .SetProperty(item => item.UpdatedAt, now),
                    stoppingToken);

            if (updatedRows != 1)
            {
                await transaction.RollbackAsync(stoppingToken);
                return null;
            }

            var task = await context.BranchGenerationTasks
                .FirstAsync(item => item.Id == taskId, stoppingToken);
            var repository = await context.Repositories
                .FirstOrDefaultAsync(item => item.Id == task.RepositoryId && !item.IsDeleted, stoppingToken);
            var branch = await context.RepositoryBranches
                .FirstOrDefaultAsync(item => item.Id == task.BranchId && item.RepositoryId == task.RepositoryId && !item.IsDeleted, stoppingToken);

            if (branch is not null)
            {
                branch.GenerationStatus = BranchGenerationTaskStatus.Processing;
                branch.LastGenerationTaskId = task.Id;
                branch.LastGenerationError = null;
                branch.LastGenerationStartedAt = now;
                branch.LastGenerationCompletedAt = null;
                branch.UpdateTimestamp();
                await context.SaveChangesAsync(stoppingToken);
            }

            await transaction.CommitAsync(stoppingToken);
            return new ClaimedBranchGenerationTask(task, repository, branch);
        }

        var fallbackTask = await context.BranchGenerationTasks
            .FirstOrDefaultAsync(item => item.Id == taskId &&
                                         !item.IsDeleted &&
                                         item.Status == BranchGenerationTaskStatus.Pending,
                stoppingToken);
        if (fallbackTask is null)
        {
            return null;
        }

        var fallbackRepository = await context.Repositories
            .FirstOrDefaultAsync(item => item.Id == fallbackTask.RepositoryId && !item.IsDeleted, stoppingToken);
        var fallbackBranch = await context.RepositoryBranches
            .FirstOrDefaultAsync(item => item.Id == fallbackTask.BranchId && item.RepositoryId == fallbackTask.RepositoryId && !item.IsDeleted, stoppingToken);

        fallbackTask.Status = BranchGenerationTaskStatus.Processing;
        fallbackTask.StartedAt = now;
        fallbackTask.CompletedAt = null;
        fallbackTask.ErrorMessage = null;
        fallbackTask.UpdateTimestamp();

        if (fallbackBranch is not null)
        {
            fallbackBranch.GenerationStatus = BranchGenerationTaskStatus.Processing;
            fallbackBranch.LastGenerationTaskId = fallbackTask.Id;
            fallbackBranch.LastGenerationError = null;
            fallbackBranch.LastGenerationStartedAt = now;
            fallbackBranch.LastGenerationCompletedAt = null;
            fallbackBranch.UpdateTimestamp();
        }

        await context.SaveChangesAsync(stoppingToken);
        return new ClaimedBranchGenerationTask(fallbackTask, fallbackRepository, fallbackBranch);
    }

    private async Task RecoverProcessingTasksWithoutLocksAsync(
        IContext context,
        IProcessingLogService? processingLogService,
        CancellationToken cancellationToken)
    {
        var orphanedTasks = await context.BranchGenerationTasks
            .Where(task => !task.IsDeleted &&
                           task.Status == BranchGenerationTaskStatus.Processing &&
                           !context.RepositoryGenerationLocks.Any(generationLock =>
                               !generationLock.IsDeleted &&
                               generationLock.RepositoryId == task.RepositoryId &&
                               generationLock.OwnerType == RepositoryGenerationLockOwnerType.BranchTask &&
                               generationLock.OwnerId == task.Id))
            .Take(10)
            .ToListAsync(cancellationToken);

        foreach (var task in orphanedTasks)
        {
            logger.LogWarning(
                "Recovering branch generation task stuck in Processing without active lock. TaskId: {TaskId}, RepositoryId: {RepositoryId}, BranchId: {BranchId}",
                task.Id,
                task.RepositoryId,
                task.BranchId);
            await MarkTaskFailedAsync(
                context,
                processingLogService,
                task,
                "Branch generation stopped before reaching a terminal state",
                cancellationToken);
        }
    }

    private async Task MarkTaskFailedAsync(
        IContext context,
        IProcessingLogService? processingLogService,
        BranchGenerationTask task,
        string errorMessage,
        CancellationToken cancellationToken)
    {
        task.Status = BranchGenerationTaskStatus.Failed;
        task.ErrorMessage = errorMessage;
        task.CompletedAt = DateTime.UtcNow;
        task.RetryCount++;
        task.UpdateTimestamp();

        var branch = await context.RepositoryBranches
            .FirstOrDefaultAsync(item => item.Id == task.BranchId && !item.IsDeleted, cancellationToken);

        if (branch is not null)
        {
            branch.GenerationStatus = BranchGenerationTaskStatus.Failed;
            branch.LastGenerationTaskId = task.Id;
            branch.LastGenerationError = errorMessage;
            branch.LastGenerationCompletedAt = task.CompletedAt;
            branch.UpdateTimestamp();
        }

        if (processingLogService is not null)
        {
            await processingLogService.LogAsync(
                task.RepositoryId,
                task.BranchId,
                task.Id,
                ProcessingStep.Content,
                $"Branch generation failed: {errorMessage}",
                cancellationToken: cancellationToken);
        }

        await context.SaveChangesAsync(cancellationToken);
    }

    private async Task ReleaseLeaseAsync(WikiGenerationWorkLease lease)
    {
        try
        {
            using var scope = scopeFactory.CreateScope();
            var context = scope.ServiceProvider.GetRequiredService<IContext>();
            var coordinator = scope.ServiceProvider.GetRequiredService<IWikiGenerationCoordinator>();
            await coordinator.ReleaseAsync(context, lease, CancellationToken.None);
        }
        catch (Exception ex)
        {
            logger.LogWarning(ex, "Failed to release wiki generation lease. Task owner: {OwnerId}", lease.OwnerId);
        }
    }

    private void PruneCompletedJobs()
    {
        foreach (var (taskId, task) in _inFlight.ToArray())
        {
            if (task.IsCompleted)
            {
                _inFlight.TryRemove(taskId, out _);
            }
        }
    }

    private async Task WaitForInFlightAsync()
    {
        var running = _inFlight.Values.ToArray();
        if (running.Length == 0)
        {
            return;
        }

        try
        {
            await Task.WhenAll(running);
        }
        catch (Exception ex)
        {
            logger.LogDebug(ex, "One or more branch generation jobs ended with an error during shutdown");
        }
    }

    private sealed record ClaimedBranchGenerationTask(
        BranchGenerationTask Task,
        Repository? Repository,
        RepositoryBranch? Branch);
}
