using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Repositories;

public interface IBranchGenerationTaskService
{
    Task<BranchGenerationTaskResult> EnqueueFullGenerationAsync(
        string repositoryId,
        string branchId,
        string? requestedBy = null,
        int priority = 100,
        CancellationToken cancellationToken = default);

    Task<BranchGenerationTaskResult> RetryAsync(
        string taskId,
        CancellationToken cancellationToken = default);

    Task<BranchGenerationTaskResult> CancelAsync(
        string taskId,
        CancellationToken cancellationToken = default);
}

public sealed record BranchGenerationTaskResult(
    bool Success,
    BranchGenerationTask? Task = null,
    string? ErrorCode = null,
    string? ErrorMessage = null,
    RepositoryGenerationLock? ActiveLock = null);

public sealed class BranchGenerationTaskService(
    IContext context,
    IRepositoryGenerationLockService lockService,
    IBranchFullGenerationCleaner branchCleaner) : IBranchGenerationTaskService
{
    public async Task<BranchGenerationTaskResult> EnqueueFullGenerationAsync(
        string repositoryId,
        string branchId,
        string? requestedBy = null,
        int priority = 100,
        CancellationToken cancellationToken = default)
    {
        var repository = await context.Repositories
            .FirstOrDefaultAsync(item => item.Id == repositoryId && !item.IsDeleted, cancellationToken);

        if (repository is null)
        {
            return new BranchGenerationTaskResult(false, ErrorCode: "REPOSITORY_NOT_FOUND", ErrorMessage: "仓库不存在");
        }

        var branch = await context.RepositoryBranches
            .FirstOrDefaultAsync(item => item.Id == branchId && item.RepositoryId == repositoryId && !item.IsDeleted, cancellationToken);

        if (branch is null)
        {
            return new BranchGenerationTaskResult(false, ErrorCode: "BRANCH_NOT_FOUND", ErrorMessage: "分支不存在");
        }

        if (repository.Status is RepositoryStatus.Pending or RepositoryStatus.Processing)
        {
            return new BranchGenerationTaskResult(false, ErrorCode: "REPOSITORY_GENERATION_ACTIVE", ErrorMessage: "仓库正在处理中，无法创建 branch 生成任务");
        }

        var activeBranchTask = await FindActiveBranchTaskAsync(repositoryId, branchId, cancellationToken);
        if (activeBranchTask is not null)
        {
            return new BranchGenerationTaskResult(false, activeBranchTask, "BRANCH_GENERATION_ACTIVE", "已有进行中的 branch 生成任务");
        }

        var task = new BranchGenerationTask
        {
            Id = Guid.NewGuid().ToString(),
            RepositoryId = repositoryId,
            BranchId = branchId,
            Status = BranchGenerationTaskStatus.Pending,
            Mode = BranchGenerationTaskMode.Full,
            Priority = priority,
            IsManualTrigger = true,
            RequestedBy = requestedBy,
            CreatedAt = DateTime.UtcNow
        };

        await using var transaction = await EfContextTransaction.BeginIfSupportedAsync(context, cancellationToken);
        var lockAcquired = await lockService.TryAcquireAsync(
            context,
            repositoryId,
            RepositoryGenerationLockOwnerType.BranchTask,
            task.Id,
            RepositoryGenerationLockScope.Branch,
            cancellationToken);

        if (!lockAcquired)
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync(cancellationToken);
            }

            return new BranchGenerationTaskResult(
                false,
                ErrorCode: "GENERATION_LOCK_CONFLICT",
                ErrorMessage: "仓库已有生成任务正在排队或处理中",
                ActiveLock: await lockService.GetLockAsync(repositoryId, cancellationToken));
        }

        branch.GenerationStatus = BranchGenerationTaskStatus.Pending;
        branch.LastGenerationTaskId = task.Id;
        branch.LastGenerationError = null;
        branch.LastGenerationStartedAt = null;
        branch.LastGenerationCompletedAt = null;
        branch.UpdateTimestamp();

        context.BranchGenerationTasks.Add(task);
        context.RepositoryBranches.Update(branch);
        try
        {
            await context.SaveChangesAsync(cancellationToken);
            if (transaction is not null)
            {
                await transaction.CommitAsync(cancellationToken);
            }
        }
        catch
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync(CancellationToken.None);
            }
            else
            {
                EfContextTransaction.ClearPendingChanges(context);
                await lockService.ReleaseAsync(
                    context,
                    repositoryId,
                    RepositoryGenerationLockOwnerType.BranchTask,
                    task.Id,
                    CancellationToken.None);
            }

            throw;
        }

        return new BranchGenerationTaskResult(true, task);
    }

    public async Task<BranchGenerationTaskResult> RetryAsync(
        string taskId,
        CancellationToken cancellationToken = default)
    {
        var task = await context.BranchGenerationTasks
            .FirstOrDefaultAsync(item => item.Id == taskId && !item.IsDeleted, cancellationToken);

        if (task is null)
        {
            return new BranchGenerationTaskResult(false, ErrorCode: "TASK_NOT_FOUND", ErrorMessage: "任务不存在");
        }

        if (task.Status is not (BranchGenerationTaskStatus.Failed or BranchGenerationTaskStatus.Cancelled))
        {
            return new BranchGenerationTaskResult(false, task, "INVALID_TASK_STATUS", $"只能重试 Failed/Cancelled 任务，当前状态: {task.Status}");
        }

        var activeBranchTask = await FindActiveBranchTaskAsync(task.RepositoryId, task.BranchId, cancellationToken);
        if (activeBranchTask is not null)
        {
            return new BranchGenerationTaskResult(false, activeBranchTask, "BRANCH_GENERATION_ACTIVE", "已有进行中的 branch 生成任务");
        }

        await using var transaction = await EfContextTransaction.BeginIfSupportedAsync(context, cancellationToken);
        var lockAcquired = await lockService.TryAcquireAsync(
            context,
            task.RepositoryId,
            RepositoryGenerationLockOwnerType.BranchTask,
            task.Id,
            RepositoryGenerationLockScope.Branch,
            cancellationToken);

        if (!lockAcquired)
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync(cancellationToken);
            }

            return new BranchGenerationTaskResult(
                false,
                task,
                "GENERATION_LOCK_CONFLICT",
                "仓库已有生成任务正在排队或处理中",
                await lockService.GetLockAsync(task.RepositoryId, cancellationToken));
        }

        var branch = await context.RepositoryBranches
            .FirstOrDefaultAsync(item => item.Id == task.BranchId && !item.IsDeleted, cancellationToken);

        if (branch is null)
        {
            return new BranchGenerationTaskResult(false, task, "BRANCH_NOT_FOUND", "分支不存在");
        }

        await branchCleaner.CleanAsync(context, branch, cancellationToken);

        task.Status = BranchGenerationTaskStatus.Pending;
        task.RetryCount++;
        task.ErrorMessage = null;
        task.StartedAt = null;
        task.CompletedAt = null;
        task.UpdateTimestamp();

        branch.LastGenerationTaskId = task.Id;
        context.BranchGenerationTasks.Update(task);
        try
        {
            await context.SaveChangesAsync(cancellationToken);
            if (transaction is not null)
            {
                await transaction.CommitAsync(cancellationToken);
            }
        }
        catch
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync(CancellationToken.None);
            }
            else
            {
                EfContextTransaction.ClearPendingChanges(context);
                await lockService.ReleaseAsync(
                    context,
                    task.RepositoryId,
                    RepositoryGenerationLockOwnerType.BranchTask,
                    task.Id,
                    CancellationToken.None);
            }

            throw;
        }

        return new BranchGenerationTaskResult(true, task);
    }

    public async Task<BranchGenerationTaskResult> CancelAsync(
        string taskId,
        CancellationToken cancellationToken = default)
    {
        var task = await context.BranchGenerationTasks
            .FirstOrDefaultAsync(item => item.Id == taskId && !item.IsDeleted, cancellationToken);

        if (task is null)
        {
            return new BranchGenerationTaskResult(false, ErrorCode: "TASK_NOT_FOUND", ErrorMessage: "任务不存在");
        }

        if (task.Status != BranchGenerationTaskStatus.Pending)
        {
            return new BranchGenerationTaskResult(false, task, "INVALID_TASK_STATUS", $"首期只支持取消 Pending 任务，当前状态: {task.Status}");
        }

        task.Status = BranchGenerationTaskStatus.Cancelled;
        task.CompletedAt = DateTime.UtcNow;
        task.UpdateTimestamp();

        var branch = await context.RepositoryBranches
            .FirstOrDefaultAsync(item => item.Id == task.BranchId && !item.IsDeleted, cancellationToken);

        if (branch is not null)
        {
            branch.GenerationStatus = BranchGenerationTaskStatus.Cancelled;
            branch.LastGenerationTaskId = task.Id;
            branch.LastGenerationCompletedAt = task.CompletedAt;
            branch.UpdateTimestamp();
        }

        await lockService.ReleaseAsync(
            context,
            task.RepositoryId,
            RepositoryGenerationLockOwnerType.BranchTask,
            task.Id,
            cancellationToken);

        await context.SaveChangesAsync(cancellationToken);
        return new BranchGenerationTaskResult(true, task);
    }

    private async Task<BranchGenerationTask?> FindActiveBranchTaskAsync(
        string repositoryId,
        string branchId,
        CancellationToken cancellationToken)
    {
        return await context.BranchGenerationTasks
            .Where(item => !item.IsDeleted &&
                           item.RepositoryId == repositoryId &&
                           (item.Status == BranchGenerationTaskStatus.Pending ||
                            item.Status == BranchGenerationTaskStatus.Processing))
            .OrderByDescending(item => item.Priority)
            .ThenBy(item => item.CreatedAt)
            .FirstOrDefaultAsync(cancellationToken);
    }
}
