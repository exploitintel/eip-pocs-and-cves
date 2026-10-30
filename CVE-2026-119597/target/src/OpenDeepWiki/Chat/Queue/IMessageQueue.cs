namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 消息队列接口
/// 用于处理连续消息发送和平台限流
/// </summary>
public interface IMessageQueue
{
    /// <summary>
    /// 入队消息
    /// </summary>
    /// <param name="message">要入队的消息</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task EnqueueAsync(QueuedMessage message, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 出队消息（获取下一个待处理的消息）
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>队列消息，如果队列为空则返回 null</returns>
    Task<QueuedMessage?> DequeueAsync(CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 获取队列长度
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>队列中待处理的消息数量</returns>
    Task<int> GetQueueLengthAsync(CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 标记消息完成
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task CompleteAsync(string messageId, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 标记消息失败
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="reason">失败原因</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task FailAsync(string messageId, string reason, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 将消息加入重试队列
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="delaySeconds">延迟秒数</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task RetryAsync(string messageId, int delaySeconds = 30, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 获取死信队列中的消息数量
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>死信队列中的消息数量</returns>
    Task<int> GetDeadLetterCountAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// 获取死信队列中的消息
    /// </summary>
    /// <param name="skip">跳过的消息数</param>
    /// <param name="take">获取的消息数</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>死信队列消息列表</returns>
    Task<IReadOnlyList<DeadLetterMessage>> GetDeadLetterMessagesAsync(
        int skip = 0, 
        int take = 100, 
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 重新处理死信队列中的消息
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>是否成功</returns>
    Task<bool> ReprocessDeadLetterAsync(string messageId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 删除死信队列中的消息
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>是否成功</returns>
    Task<bool> DeleteDeadLetterAsync(string messageId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 清空死信队列
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>删除的消息数量</returns>
    Task<int> ClearDeadLetterQueueAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// 将消息移入死信队列
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="reason">移入原因</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task MoveToDeadLetterAsync(string messageId, string reason, CancellationToken cancellationToken = default);
}
