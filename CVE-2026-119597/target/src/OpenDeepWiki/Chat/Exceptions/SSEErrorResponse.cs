using System.Text.Json.Serialization;

namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// SSE错误响应数据
/// 用于统一SSE流中的错误事件格式
/// Requirements: 11.1, 11.2, 11.3
/// </summary>
public class SSEErrorResponse
{
    /// <summary>
    /// 错误码
    /// </summary>
    [JsonPropertyName("code")]
    public string Code { get; set; } = string.Empty;

    /// <summary>
    /// 错误消息
    /// </summary>
    [JsonPropertyName("message")]
    public string Message { get; set; } = string.Empty;

    /// <summary>
    /// 是否可重试
    /// </summary>
    [JsonPropertyName("retryable")]
    public bool Retryable { get; set; }

    /// <summary>
    /// 重试延迟（毫秒），仅当Retryable为true时有效
    /// </summary>
    [JsonPropertyName("retryAfterMs")]
    public int? RetryAfterMs { get; set; }

    /// <summary>
    /// 额外的错误详情（可选）
    /// </summary>
    [JsonPropertyName("details")]
    public object? Details { get; set; }

    /// <summary>
    /// 创建一个SSE错误响应
    /// </summary>
    /// <param name="code">错误码</param>
    /// <param name="message">错误消息，如果为空则使用默认消息</param>
    /// <param name="details">额外详情</param>
    /// <returns>SSE错误响应</returns>
    public static SSEErrorResponse Create(string code, string? message = null, object? details = null)
    {
        return new SSEErrorResponse
        {
            Code = code,
            Message = message ?? ChatErrorCodes.GetDefaultMessage(code),
            Retryable = ChatErrorCodes.IsRetryable(code),
            RetryAfterMs = ChatErrorCodes.IsRetryable(code) ? GetRetryDelay(code) : null,
            Details = details
        };
    }

    /// <summary>
    /// 创建一个可重试的错误响应
    /// </summary>
    /// <param name="code">错误码</param>
    /// <param name="message">错误消息</param>
    /// <param name="retryAfterMs">重试延迟（毫秒）</param>
    /// <returns>SSE错误响应</returns>
    public static SSEErrorResponse CreateRetryable(string code, string? message = null, int retryAfterMs = 1000)
    {
        return new SSEErrorResponse
        {
            Code = code,
            Message = message ?? ChatErrorCodes.GetDefaultMessage(code),
            Retryable = true,
            RetryAfterMs = retryAfterMs
        };
    }

    /// <summary>
    /// 创建一个不可重试的错误响应
    /// </summary>
    /// <param name="code">错误码</param>
    /// <param name="message">错误消息</param>
    /// <returns>SSE错误响应</returns>
    public static SSEErrorResponse CreateNonRetryable(string code, string? message = null)
    {
        return new SSEErrorResponse
        {
            Code = code,
            Message = message ?? ChatErrorCodes.GetDefaultMessage(code),
            Retryable = false,
            RetryAfterMs = null
        };
    }

    /// <summary>
    /// 获取错误码对应的默认重试延迟
    /// </summary>
    private static int GetRetryDelay(string code)
    {
        return code switch
        {
            ChatErrorCodes.RATE_LIMIT_EXCEEDED => 5000,  // 限流需要等待更长时间
            ChatErrorCodes.REQUEST_TIMEOUT => 2000,
            ChatErrorCodes.CONNECTION_FAILED => 1000,
            ChatErrorCodes.STREAM_INTERRUPTED => 1000,
            ChatErrorCodes.INTERNAL_ERROR => 3000,
            _ => 1000
        };
    }
}
