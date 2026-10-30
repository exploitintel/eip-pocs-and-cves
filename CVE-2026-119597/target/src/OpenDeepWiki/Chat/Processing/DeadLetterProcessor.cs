using Microsoft.Extensions.Logging;
using OpenDeepWiki.Chat.Queue;

namespace OpenDeepWiki.Chat.Processing;

/// <summary>
/// 死信队列处理器
/// 提供死信队列的监控和管理功能
/// Requirements: 10.4
/// </summary>
public interface IDeadLetterProcessor
{
    /// <summary>
    /// 获取死信队列统计信息
    /// </summary>
    Task<DeadLetterStats> GetStatsAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// 获取死信消息列表
    /// </summary>
    Task<IReadOnlyList<DeadLetterMessage>> GetMessagesAsync(
        int skip = 0, 
        int take = 100, 
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 重新处理单条死信消息
    /// </summary>
    Task<bool> ReprocessAsync(string messageId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 批量重新处理死信消息
    /// </summary>
    Task<int> ReprocessBatchAsync(
        IEnumerable<string> messageIds, 
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 重新处理所有死信消息
    /// </summary>
    Task<int> ReprocessAllAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// 删除单条死信消息
    /// </summary>
    Task<bool> DeleteAsync(string messageId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 清空死信队列
    /// </summary>
    Task<int> ClearAllAsync(CancellationToken cancellationToken = default);
}

/// <summary>
/// 死信队列统计信息
/// </summary>
public record DeadLetterStats(
    int TotalCount,
    int IncomingCount,
    int OutgoingCount,
    int RetryCount,
    DateTimeOffset? OldestMessageTime,
    DateTimeOffset? NewestMessageTime
);

/// <summary>
/// 死信队列处理器实现
/// </summary>
public class DeadLetterProcessor : IDeadLetterProcessor
{
    private readonly IMessageQueue _messageQueue;
    private readonly ILogger<DeadLetterProcessor> _logger;

    public DeadLetterProcessor(
        IMessageQueue messageQueue,
        ILogger<DeadLetterProcessor> logger)
    {
        _messageQueue = messageQueue;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task<DeadLetterStats> GetStatsAsync(CancellationToken cancellationToken = default)
    {
        var messages = await _messageQueue.GetDeadLetterMessagesAsync(0, int.MaxValue, cancellationToken);
        
        var incomingCount = messages.Count(m => m.OriginalType == QueuedMessageType.Incoming);
        var outgoingCount = messages.Count(m => m.OriginalType == QueuedMessageType.Outgoing);
        var retryCount = messages.Count(m => m.OriginalType == QueuedMessageType.Retry);
        
        var oldestMessage = messages.MinBy(m => m.CreatedAt);
        var newestMessage = messages.MaxBy(m => m.FailedAt);

        return new DeadLetterStats(
            messages.Count,
            incomingCount,
            outgoingCount,
            retryCount,
            oldestMessage?.CreatedAt,
            newestMessage?.FailedAt
        );
    }

    /// <inheritdoc />
    public async Task<IReadOnlyList<DeadLetterMessage>> GetMessagesAsync(
        int skip = 0, 
        int take = 100, 
        CancellationToken cancellationToken = default)
    {
        return await _messageQueue.GetDeadLetterMessagesAsync(skip, take, cancellationToken);
    }

    /// <inheritdoc />
    public async Task<bool> ReprocessAsync(string messageId, CancellationToken cancellationToken = default)
    {
        var result = await _messageQueue.ReprocessDeadLetterAsync(messageId, cancellationToken);
        if (result)
        {
            _logger.LogInformation("死信消息已重新入队处理: {MessageId}", messageId);
        }
        return result;
    }

    /// <inheritdoc />
    public async Task<int> ReprocessBatchAsync(
        IEnumerable<string> messageIds, 
        CancellationToken cancellationToken = default)
    {
        var successCount = 0;
        foreach (var messageId in messageIds)
        {
            if (await _messageQueue.ReprocessDeadLetterAsync(messageId, cancellationToken))
            {
                successCount++;
            }
        }
        
        _logger.LogInformation("批量重新处理死信消息完成，成功: {SuccessCount}", successCount);
        return successCount;
    }

    /// <inheritdoc />
    public async Task<int> ReprocessAllAsync(CancellationToken cancellationToken = default)
    {
        var messages = await _messageQueue.GetDeadLetterMessagesAsync(0, int.MaxValue, cancellationToken);
        var messageIds = messages.Select(m => m.Id);
        
        return await ReprocessBatchAsync(messageIds, cancellationToken);
    }

    /// <inheritdoc />
    public async Task<bool> DeleteAsync(string messageId, CancellationToken cancellationToken = default)
    {
        var result = await _messageQueue.DeleteDeadLetterAsync(messageId, cancellationToken);
        if (result)
        {
            _logger.LogInformation("死信消息已删除: {MessageId}", messageId);
        }
        return result;
    }

    /// <inheritdoc />
    public async Task<int> ClearAllAsync(CancellationToken cancellationToken = default)
    {
        var count = await _messageQueue.ClearDeadLetterQueueAsync(cancellationToken);
        _logger.LogInformation("死信队列已清空，删除 {Count} 条消息", count);
        return count;
    }
}
