namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 队列消息类型
/// </summary>
public enum QueuedMessageType
{
    /// <summary>
    /// 入站消息（从平台接收）
    /// </summary>
    Incoming,
    
    /// <summary>
    /// 出站消息（发送到平台）
    /// </summary>
    Outgoing,
    
    /// <summary>
    /// 重试消息
    /// </summary>
    Retry
}
