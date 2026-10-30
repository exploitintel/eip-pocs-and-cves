namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// 限流异常
/// </summary>
public class RateLimitException : ProviderException
{
    /// <summary>
    /// 重试等待时间
    /// </summary>
    public TimeSpan RetryAfter { get; }
    
    public RateLimitException(string platform, TimeSpan retryAfter)
        : base(platform, "Rate limit exceeded", "RATE_LIMIT", shouldRetry: true)
    {
        RetryAfter = retryAfter;
    }
    
    public RateLimitException(string platform, TimeSpan retryAfter, string message)
        : base(platform, message, "RATE_LIMIT", shouldRetry: true)
    {
        RetryAfter = retryAfter;
    }
}
