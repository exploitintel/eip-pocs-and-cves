namespace OpenDeepWiki.Chat.Providers.QQ;

/// <summary>
/// QQ 机器人 Provider 配置选项
/// </summary>
public class QQProviderOptions : ProviderOptions
{
    /// <summary>
    /// QQ 机器人 App ID
    /// </summary>
    public string AppId { get; set; } = string.Empty;
    
    /// <summary>
    /// QQ 机器人 App Secret
    /// </summary>
    public string AppSecret { get; set; } = string.Empty;
    
    /// <summary>
    /// QQ 机器人 Token（用于 Webhook 验证）
    /// </summary>
    public string Token { get; set; } = string.Empty;
    
    /// <summary>
    /// QQ 开放平台 API 基础 URL
    /// </summary>
    public string ApiBaseUrl { get; set; } = "https://api.sgroup.qq.com";
    
    /// <summary>
    /// 沙箱环境 API 基础 URL
    /// </summary>
    public string SandboxApiBaseUrl { get; set; } = "https://sandbox.api.sgroup.qq.com";
    
    /// <summary>
    /// 是否使用沙箱环境
    /// </summary>
    public bool UseSandbox { get; set; } = false;
    
    /// <summary>
    /// Access Token 缓存时间（秒），默认 7000 秒
    /// </summary>
    public int TokenCacheSeconds { get; set; } = 7000;
    
    /// <summary>
    /// 心跳间隔（毫秒），默认 30000 毫秒（30秒）
    /// </summary>
    public int HeartbeatInterval { get; set; } = 30000;
    
    /// <summary>
    /// WebSocket 重连间隔（毫秒）
    /// </summary>
    public int ReconnectInterval { get; set; } = 5000;
    
    /// <summary>
    /// 最大重连次数
    /// </summary>
    public int MaxReconnectAttempts { get; set; } = 10;
}
