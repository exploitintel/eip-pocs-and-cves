namespace OpenDeepWiki.Chat.Providers.WeChat;

/// <summary>
/// 微信客服 Provider 配置选项
/// </summary>
public class WeChatProviderOptions : ProviderOptions
{
    /// <summary>
    /// 微信公众号/小程序 AppID
    /// </summary>
    public string AppId { get; set; } = string.Empty;
    
    /// <summary>
    /// 微信公众号/小程序 AppSecret
    /// </summary>
    public string AppSecret { get; set; } = string.Empty;
    
    /// <summary>
    /// 微信服务器配置的 Token（用于验证消息来源）
    /// </summary>
    public string Token { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息加解密密钥（EncodingAESKey）
    /// </summary>
    public string EncodingAesKey { get; set; } = string.Empty;
    
    /// <summary>
    /// 微信 API 基础 URL
    /// </summary>
    public string ApiBaseUrl { get; set; } = "https://api.weixin.qq.com";
    
    /// <summary>
    /// Access Token 缓存时间（秒），默认 7000 秒（略小于微信的 7200 秒有效期）
    /// </summary>
    public int TokenCacheSeconds { get; set; } = 7000;
    
    /// <summary>
    /// 消息加密模式：plain（明文）、compatible（兼容）、safe（安全）
    /// </summary>
    public string EncryptMode { get; set; } = "safe";
}
