namespace OpenDeepWiki.Chat.Providers.Feishu;

/// <summary>
/// 飞书 Provider 配置选项
/// </summary>
public class FeishuProviderOptions : ProviderOptions
{
    /// <summary>
    /// 飞书应用 App ID
    /// </summary>
    public string AppId { get; set; } = string.Empty;
    
    /// <summary>
    /// 飞书应用 App Secret
    /// </summary>
    public string AppSecret { get; set; } = string.Empty;
    
    /// <summary>
    /// 飞书 Verification Token（用于验证 Webhook 请求）
    /// </summary>
    public string VerificationToken { get; set; } = string.Empty;
    
    /// <summary>
    /// 飞书 Encrypt Key（用于消息加解密，可选）
    /// </summary>
    public string? EncryptKey { get; set; }
    
    /// <summary>
    /// 飞书 API 基础 URL
    /// </summary>
    public string ApiBaseUrl { get; set; } = "https://open.feishu.cn/open-apis";
    
    /// <summary>
    /// Access Token 缓存时间（秒），默认 7000 秒（略小于飞书的 7200 秒有效期）
    /// </summary>
    public int TokenCacheSeconds { get; set; } = 7000;
}
