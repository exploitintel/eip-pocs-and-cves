namespace OpenDeepWiki.Services.Notifications;

/// <summary>
/// 订阅者通知服务接口
/// 负责在仓库更新后通知订阅用户
/// </summary>
public interface ISubscriberNotificationService
{
    /// <summary>
    /// 发送仓库更新通知给所有订阅者
    /// </summary>
    /// <param name="notification">通知内容</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task NotifySubscribersAsync(
        RepositoryUpdateNotification notification,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 获取仓库的所有订阅者
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>订阅者ID列表</returns>
    Task<IReadOnlyList<string>> GetSubscribersAsync(
        string repositoryId,
        CancellationToken cancellationToken = default);
}

/// <summary>
/// 仓库更新通知
/// </summary>
public class RepositoryUpdateNotification
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称 (org/repo)
    /// </summary>
    public string RepositoryName { get; set; } = string.Empty;

    /// <summary>
    /// 分支名称
    /// </summary>
    public string BranchName { get; set; } = string.Empty;

    /// <summary>
    /// 更新摘要
    /// </summary>
    public string Summary { get; set; } = string.Empty;

    /// <summary>
    /// 变更文件数量
    /// </summary>
    public int ChangedFilesCount { get; set; }

    /// <summary>
    /// 更新时间戳
    /// </summary>
    public DateTime UpdatedAt { get; set; }

    /// <summary>
    /// 新的 Commit ID
    /// </summary>
    public string CommitId { get; set; } = string.Empty;
}
