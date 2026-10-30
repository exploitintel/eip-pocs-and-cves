namespace OpenDeepWiki.Chat.Providers;

/// <summary>
/// 发送结果
/// </summary>
public record SendResult(
    bool Success,
    string? MessageId = null,
    string? ErrorCode = null,
    string? ErrorMessage = null,
    bool ShouldRetry = false
);
