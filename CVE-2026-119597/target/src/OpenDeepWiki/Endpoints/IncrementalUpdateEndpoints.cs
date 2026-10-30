using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Repositories;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 增量更新端点日志类（用于泛型日志记录器）
/// </summary>
public class IncrementalUpdateEndpointsLogger { }

/// <summary>
/// 增量更新 API 端点
/// 提供手动触发增量更新、查询任务状态和重试失败任务的功能
/// </summary>
public static class IncrementalUpdateEndpoints
{
    /// <summary>
    /// 注册所有增量更新相关端点
    /// </summary>
    public static IEndpointRouteBuilder MapIncrementalUpdateEndpoints(this IEndpointRouteBuilder app)
    {
        // 仓库增量更新触发端点
        var repoGroup = app.MapGroup("/api/v1/repositories")
            .WithTags("增量更新");

        repoGroup.MapPost("/{repositoryId}/branches/{branchId}/incremental-update", TriggerIncrementalUpdateAsync)
            .WithName("TriggerIncrementalUpdate")
            .WithSummary("手动触发增量更新")
            .WithDescription("为指定仓库和分支创建一个高优先级的增量更新任务");

        // 增量更新任务管理端点
        var taskGroup = app.MapGroup("/api/v1/incremental-updates")
            .WithTags("增量更新任务");

        taskGroup.MapGet("/{taskId}", GetTaskStatusAsync)
            .WithName("GetIncrementalUpdateTaskStatus")
            .WithSummary("获取任务状态")
            .WithDescription("获取指定增量更新任务的详细状态");

        taskGroup.MapPost("/{taskId}/retry", RetryFailedTaskAsync)
            .WithName("RetryFailedIncrementalUpdateTask")
            .WithSummary("重试失败任务")
            .WithDescription("重试一个失败的增量更新任务");

        return app;
    }

    /// <summary>
    /// 手动触发增量更新
    /// POST /api/v1/repositories/{repositoryId}/branches/{branchId}/incremental-update
    /// </summary>
    private static async Task<IResult> TriggerIncrementalUpdateAsync(
        string repositoryId,
        string branchId,
        [FromServices] IIncrementalUpdateService updateService,
        [FromServices] IContext context,
        [FromServices] ILogger<IncrementalUpdateEndpointsLogger> logger,
        CancellationToken cancellationToken)
    {
        logger.LogInformation(
            "Manual incremental update requested. RepositoryId: {RepositoryId}, BranchId: {BranchId}",
            repositoryId, branchId);

        try
        {
            // 验证仓库是否存在
            var repository = await context.Repositories
                .FirstOrDefaultAsync(r => r.Id == repositoryId, cancellationToken);

            if (repository == null)
            {
                logger.LogWarning("Repository not found. RepositoryId: {RepositoryId}", repositoryId);
                return Results.NotFound(new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "仓库不存在",
                    ErrorCode = "REPOSITORY_NOT_FOUND"
                });
            }

            // 验证分支是否存在
            var branch = await context.RepositoryBranches
                .FirstOrDefaultAsync(b => b.Id == branchId && b.RepositoryId == repositoryId, cancellationToken);

            if (branch == null)
            {
                logger.LogWarning("Branch not found. BranchId: {BranchId}", branchId);
                return Results.NotFound(new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "分支不存在",
                    ErrorCode = "BRANCH_NOT_FOUND"
                });
            }

            // 触发增量更新
            var taskId = await updateService.TriggerManualUpdateAsync(repositoryId, branchId, cancellationToken);

            // 获取任务状态
            var task = await context.IncrementalUpdateTasks
                .FirstOrDefaultAsync(t => t.Id == taskId, cancellationToken);

            logger.LogInformation(
                "Incremental update task created/found. TaskId: {TaskId}, Status: {Status}",
                taskId, task?.Status);

            return Results.Ok(new TriggerIncrementalUpdateResponse
            {
                Success = true,
                TaskId = taskId,
                Status = task?.Status.ToString() ?? "Unknown",
                Message = task?.Status == IncrementalUpdateStatus.Processing
                    ? "任务正在处理中"
                    : "增量更新任务已创建"
            });
        }
        catch (Exception ex)
        {
            logger.LogError(ex,
                "Failed to trigger incremental update. RepositoryId: {RepositoryId}, BranchId: {BranchId}",
                repositoryId, branchId);

            return Results.Json(
                new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "触发增量更新失败",
                    ErrorCode = "TRIGGER_FAILED",
                    Details = ex.Message
                },
                statusCode: StatusCodes.Status500InternalServerError);
        }
    }


    /// <summary>
    /// 获取任务状态
    /// GET /api/v1/incremental-updates/{taskId}
    /// </summary>
    private static async Task<IResult> GetTaskStatusAsync(
        string taskId,
        [FromServices] IContext context,
        [FromServices] ILogger<IncrementalUpdateEndpointsLogger> logger,
        CancellationToken cancellationToken)
    {
        logger.LogDebug("Getting task status. TaskId: {TaskId}", taskId);

        try
        {
            var task = await context.IncrementalUpdateTasks
                .Include(t => t.Repository)
                .Include(t => t.Branch)
                .FirstOrDefaultAsync(t => t.Id == taskId, cancellationToken);

            if (task == null)
            {
                logger.LogWarning("Task not found. TaskId: {TaskId}", taskId);
                return Results.NotFound(new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "任务不存在",
                    ErrorCode = "TASK_NOT_FOUND"
                });
            }

            return Results.Ok(new IncrementalUpdateTaskResponse
            {
                Success = true,
                TaskId = task.Id,
                RepositoryId = task.RepositoryId,
                RepositoryName = task.Repository != null
                    ? $"{task.Repository.OrgName}/{task.Repository.RepoName}"
                    : null,
                BranchId = task.BranchId,
                BranchName = task.Branch?.BranchName,
                Status = task.Status.ToString(),
                Priority = task.Priority,
                IsManualTrigger = task.IsManualTrigger,
                PreviousCommitId = task.PreviousCommitId,
                TargetCommitId = task.TargetCommitId,
                RetryCount = task.RetryCount,
                ErrorMessage = task.ErrorMessage,
                CreatedAt = task.CreatedAt,
                StartedAt = task.StartedAt,
                CompletedAt = task.CompletedAt
            });
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Failed to get task status. TaskId: {TaskId}", taskId);

            return Results.Json(
                new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "获取任务状态失败",
                    ErrorCode = "GET_STATUS_FAILED",
                    Details = ex.Message
                },
                statusCode: StatusCodes.Status500InternalServerError);
        }
    }

    /// <summary>
    /// 重试失败任务
    /// POST /api/v1/incremental-updates/{taskId}/retry
    /// </summary>
    private static async Task<IResult> RetryFailedTaskAsync(
        string taskId,
        [FromServices] IContext context,
        [FromServices] ILogger<IncrementalUpdateEndpointsLogger> logger,
        CancellationToken cancellationToken)
    {
        logger.LogInformation("Retry requested for task. TaskId: {TaskId}", taskId);

        try
        {
            var task = await context.IncrementalUpdateTasks
                .FirstOrDefaultAsync(t => t.Id == taskId, cancellationToken);

            if (task == null)
            {
                logger.LogWarning("Task not found. TaskId: {TaskId}", taskId);
                return Results.NotFound(new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "任务不存在",
                    ErrorCode = "TASK_NOT_FOUND"
                });
            }

            // 只能重试失败的任务
            if (task.Status != IncrementalUpdateStatus.Failed)
            {
                logger.LogWarning(
                    "Cannot retry task with status {Status}. TaskId: {TaskId}",
                    task.Status, taskId);

                return Results.BadRequest(new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = $"只能重试失败的任务，当前状态: {task.Status}",
                    ErrorCode = "INVALID_TASK_STATUS"
                });
            }

            // 重置任务状态
            task.Status = IncrementalUpdateStatus.Pending;
            task.RetryCount++;
            task.ErrorMessage = null;
            task.StartedAt = null;
            task.CompletedAt = null;
            task.UpdatedAt = DateTime.UtcNow;

            await context.SaveChangesAsync(cancellationToken);

            logger.LogInformation(
                "Task reset for retry. TaskId: {TaskId}, RetryCount: {RetryCount}",
                taskId, task.RetryCount);

            return Results.Ok(new RetryTaskResponse
            {
                Success = true,
                TaskId = task.Id,
                Status = task.Status.ToString(),
                RetryCount = task.RetryCount,
                Message = "任务已重置，将在下次轮询时重新处理"
            });
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Failed to retry task. TaskId: {TaskId}", taskId);

            return Results.Json(
                new IncrementalUpdateErrorResponse
                {
                    Success = false,
                    Error = "重试任务失败",
                    ErrorCode = "RETRY_FAILED",
                    Details = ex.Message
                },
                statusCode: StatusCodes.Status500InternalServerError);
        }
    }
}


#region 响应模型

/// <summary>
/// 触发增量更新响应
/// </summary>
public class TriggerIncrementalUpdateResponse
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 任务ID
    /// </summary>
    public string TaskId { get; set; } = string.Empty;

    /// <summary>
    /// 任务状态
    /// </summary>
    public string Status { get; set; } = string.Empty;

    /// <summary>
    /// 消息
    /// </summary>
    public string Message { get; set; } = string.Empty;
}

/// <summary>
/// 增量更新任务详情响应
/// </summary>
public class IncrementalUpdateTaskResponse
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 任务ID
    /// </summary>
    public string TaskId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID
    /// </summary>
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称 (org/repo)
    /// </summary>
    public string? RepositoryName { get; set; }

    /// <summary>
    /// 分支ID
    /// </summary>
    public string BranchId { get; set; } = string.Empty;

    /// <summary>
    /// 分支名称
    /// </summary>
    public string? BranchName { get; set; }

    /// <summary>
    /// 任务状态
    /// </summary>
    public string Status { get; set; } = string.Empty;

    /// <summary>
    /// 任务优先级
    /// </summary>
    public int Priority { get; set; }

    /// <summary>
    /// 是否为手动触发
    /// </summary>
    public bool IsManualTrigger { get; set; }

    /// <summary>
    /// 上次处理的 Commit ID
    /// </summary>
    public string? PreviousCommitId { get; set; }

    /// <summary>
    /// 目标 Commit ID
    /// </summary>
    public string? TargetCommitId { get; set; }

    /// <summary>
    /// 重试次数
    /// </summary>
    public int RetryCount { get; set; }

    /// <summary>
    /// 错误信息
    /// </summary>
    public string? ErrorMessage { get; set; }

    /// <summary>
    /// 创建时间
    /// </summary>
    public DateTime CreatedAt { get; set; }

    /// <summary>
    /// 开始处理时间
    /// </summary>
    public DateTime? StartedAt { get; set; }

    /// <summary>
    /// 完成时间
    /// </summary>
    public DateTime? CompletedAt { get; set; }
}

/// <summary>
/// 重试任务响应
/// </summary>
public class RetryTaskResponse
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 任务ID
    /// </summary>
    public string TaskId { get; set; } = string.Empty;

    /// <summary>
    /// 任务状态
    /// </summary>
    public string Status { get; set; } = string.Empty;

    /// <summary>
    /// 重试次数
    /// </summary>
    public int RetryCount { get; set; }

    /// <summary>
    /// 消息
    /// </summary>
    public string Message { get; set; } = string.Empty;
}

/// <summary>
/// 增量更新错误响应
/// </summary>
public class IncrementalUpdateErrorResponse
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 错误信息
    /// </summary>
    public string Error { get; set; } = string.Empty;

    /// <summary>
    /// 错误代码
    /// </summary>
    public string ErrorCode { get; set; } = string.Empty;

    /// <summary>
    /// 详细信息
    /// </summary>
    public string? Details { get; set; }
}

#endregion
