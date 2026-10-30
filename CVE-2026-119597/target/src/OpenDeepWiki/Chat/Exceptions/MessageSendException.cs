namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// 消息发送异常
/// </summary>
public class MessageSendException : ProviderException
{
    /// <summary>
    /// 消息ID
    /// </summary>
    public string? MessageId { get; }
    
    public MessageSendException(string platform, string? messageId, string message, string errorCode, bool shouldRetry = true)
        : base(platform, message, errorCode, shouldRetry)
    {
        MessageId = messageId;
    }
    
    public MessageSendException(string platform, string? messageId, string message, string errorCode, Exception innerException, bool shouldRetry = true)
        : base(platform, message, errorCode, innerException, shouldRetry)
    {
        MessageId = messageId;
    }
}
