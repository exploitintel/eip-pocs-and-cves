namespace OpenDeepWiki.Services.Repositories;

/// <summary>
/// 增量更新服务接口
/// 封装增量更新的核心业务逻辑
/// </summary>
public interface IIncrementalUpdateService
{
    /// <summary>
    /// 处理单个仓库的增量更新
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="branchId">分支ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>更新结果</returns>
    Task<IncrementalUpdateResult> ProcessIncrementalUpdateAsync(
        string repositoryId,
        string branchId,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 检查仓库是否需要增量更新
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="branchId">分支ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>是否需要更新及变更信息</returns>
    Task<UpdateCheckResult> CheckForUpdatesAsync(
        string repositoryId,
        string branchId,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 手动触发增量更新
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="branchId">分支ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>创建的任务ID</returns>
    Task<string> TriggerManualUpdateAsync(
        string repositoryId,
        string branchId,
        CancellationToken cancellationToken = default);
}

/// <summary>
/// 增量更新结果
/// </summary>
public class IncrementalUpdateResult
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 错误信息
    /// </summary>
    public string? ErrorMessage { get; set; }

    /// <summary>
    /// 上次处理的 Commit ID
    /// </summary>
    public string? PreviousCommitId { get; set; }

    /// <summary>
    /// 当前 Commit ID
    /// </summary>
    public string? CurrentCommitId { get; set; }

    /// <summary>
    /// 变更文件数量
    /// </summary>
    public int ChangedFilesCount { get; set; }

    /// <summary>
    /// 更新的文档数量
    /// </summary>
    public int UpdatedDocumentsCount { get; set; }

    /// <summary>
    /// 处理耗时
    /// </summary>
    public TimeSpan Duration { get; set; }
}

/// <summary>
/// 更新检查结果
/// </summary>
public class UpdateCheckResult
{
    /// <summary>
    /// 是否需要更新
    /// </summary>
    public bool NeedsUpdate { get; set; }

    /// <summary>
    /// 上次处理的 Commit ID
    /// </summary>
    public string? PreviousCommitId { get; set; }

    /// <summary>
    /// 当前 Commit ID
    /// </summary>
    public string? CurrentCommitId { get; set; }

    /// <summary>
    /// 变更文件列表
    /// </summary>
    public string[]? ChangedFiles { get; set; }
}
