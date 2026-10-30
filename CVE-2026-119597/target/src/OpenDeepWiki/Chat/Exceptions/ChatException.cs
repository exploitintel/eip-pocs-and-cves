namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// Chat 系统基础异常
/// </summary>
public class ChatException : Exception
{
    /// <summary>
    /// 错误代码
    /// </summary>
    public string ErrorCode { get; }
    
    /// <summary>
    /// 是否应该重试
    /// </summary>
    public bool ShouldRetry { get; }
    
    public ChatException(string message, string errorCode, bool shouldRetry = false)
        : base(message)
    {
        ErrorCode = errorCode;
        ShouldRetry = shouldRetry;
    }
    
    public ChatException(string message, string errorCode, Exception innerException, bool shouldRetry = false)
        : base(message, innerException)
    {
        ErrorCode = errorCode;
        ShouldRetry = shouldRetry;
    }
}
