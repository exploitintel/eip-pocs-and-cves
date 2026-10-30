using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Services.Notifications;

/// <summary>
/// 订阅者通知服务的空实现
/// 从数据库获取订阅者列表，但不发送实际通知
/// 用于在通知渠道未配置时提供默认行为
/// </summary>
public class NullSubscriberNotificationService : ISubscriberNotificationService
{
    private readonly IContext _context;
    private readonly ILogger<NullSubscriberNotificationService> _logger;

    public NullSubscriberNotificationService(
        IContext context,
        ILogger<NullSubscriberNotificationService> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task NotifySubscribersAsync(
        RepositoryUpdateNotification notification,
        CancellationToken cancellationToken = default)
    {
        // 获取订阅者列表
        var subscribers = await GetSubscribersAsync(notification.RepositoryId, cancellationToken);

        if (subscribers.Count == 0)
        {
            _logger.LogDebug(
                "No subscribers found for repository {RepositoryId} ({RepositoryName})",
                notification.RepositoryId,
                notification.RepositoryName);
            return;
        }

        // 空实现：仅记录日志，不发送实际通知
        _logger.LogInformation(
            "Skipping notification for repository {RepositoryName} (branch: {BranchName}, commit: {CommitId}). " +
            "Would notify {SubscriberCount} subscriber(s). Changed files: {ChangedFilesCount}",
            notification.RepositoryName,
            notification.BranchName,
            notification.CommitId,
            subscribers.Count,
            notification.ChangedFilesCount);

        // 记录每个订阅者（调试级别）
        foreach (var subscriberId in subscribers)
        {
            _logger.LogDebug(
                "Would notify subscriber {SubscriberId} about update to {RepositoryName}",
                subscriberId,
                notification.RepositoryName);
        }
    }

    /// <inheritdoc />
    public async Task<IReadOnlyList<string>> GetSubscribersAsync(
        string repositoryId,
        CancellationToken cancellationToken = default)
    {
        var subscribers = await _context.UserSubscriptions
            .Where(s => s.RepositoryId == repositoryId)
            .Select(s => s.UserId)
            .ToListAsync(cancellationToken);

        _logger.LogDebug(
            "Found {SubscriberCount} subscriber(s) for repository {RepositoryId}",
            subscribers.Count,
            repositoryId);

        return subscribers;
    }
}
