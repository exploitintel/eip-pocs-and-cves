namespace OpenDeepWiki.Chat.Abstractions;

/// <summary>
/// 统一消息抽象接口
/// </summary>
public interface IChatMessage
{
    /// <summary>
    /// 消息唯一标识
    /// </summary>
    string MessageId { get; }
    
    /// <summary>
    /// 发送者标识（平台用户ID）
    /// </summary>
    string SenderId { get; }
    
    /// <summary>
    /// 接收者标识（可选，用于群聊场景）
    /// </summary>
    string? ReceiverId { get; }
    
    /// <summary>
    /// 消息内容
    /// </summary>
    string Content { get; }
    
    /// <summary>
    /// 消息类型
    /// </summary>
    ChatMessageType MessageType { get; }
    
    /// <summary>
    /// 平台来源
    /// </summary>
    string Platform { get; }
    
    /// <summary>
    /// 消息时间戳
    /// </summary>
    DateTimeOffset Timestamp { get; }
    
    /// <summary>
    /// 附加数据（平台特定信息）
    /// </summary>
    IDictionary<string, object>? Metadata { get; }
}
