namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// Provider 异常
/// </summary>
public class ProviderException : ChatException
{
    /// <summary>
    /// 平台标识
    /// </summary>
    public string Platform { get; }
    
    public ProviderException(string platform, string message, string errorCode, bool shouldRetry = false)
        : base(message, errorCode, shouldRetry)
    {
        Platform = platform;
    }
    
    public ProviderException(string platform, string message, string errorCode, Exception innerException, bool shouldRetry = false)
        : base(message, errorCode, innerException, shouldRetry)
    {
        Platform = platform;
    }
}
