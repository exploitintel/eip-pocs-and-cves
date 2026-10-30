using OpenDeepWiki.Chat.Abstractions;
using OpenDeepWiki.Chat.Providers;

namespace OpenDeepWiki.Chat.Callbacks;

/// <summary>
/// 消息回调接口
/// 用于将 Agent 响应发送回用户
/// </summary>
public interface IMessageCallback
{
    /// <summary>
    /// 发送消息给用户
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="userId">目标用户ID</param>
    /// <param name="message">要发送的消息</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>发送结果</returns>
    Task<SendResult> SendAsync(
        string platform, 
        string userId, 
        IChatMessage message, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 批量发送消息给用户
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="userId">目标用户ID</param>
    /// <param name="messages">要发送的消息集合</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>发送结果集合</returns>
    Task<IEnumerable<SendResult>> SendBatchAsync(
        string platform, 
        string userId, 
        IEnumerable<IChatMessage> messages, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 发送流式消息（实时输出）
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="userId">目标用户ID</param>
    /// <param name="contentStream">内容流</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>发送结果流</returns>
    IAsyncEnumerable<SendResult> SendStreamAsync(
        string platform, 
        string userId, 
        IAsyncEnumerable<string> contentStream, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 追踪发送状态
    /// </summary>
    /// <param name="messageId">消息ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>发送状态</returns>
    Task<SendStatus> GetSendStatusAsync(
        string messageId, 
        CancellationToken cancellationToken = default);
}

/// <summary>
/// 发送状态
/// </summary>
public enum SendStatus
{
    /// <summary>
    /// 等待发送
    /// </summary>
    Pending,
    
    /// <summary>
    /// 发送中
    /// </summary>
    Sending,
    
    /// <summary>
    /// 发送成功
    /// </summary>
    Sent,
    
    /// <summary>
    /// 发送失败
    /// </summary>
    Failed,
    
    /// <summary>
    /// 重试中
    /// </summary>
    Retrying,
    
    /// <summary>
    /// 未知
    /// </summary>
    Unknown
}
