namespace OpenDeepWiki.Chat.Providers;

/// <summary>
/// Webhook 验证结果
/// </summary>
public record WebhookValidationResult(
    bool IsValid,
    string? Challenge = null,
    string? ErrorMessage = null
);
