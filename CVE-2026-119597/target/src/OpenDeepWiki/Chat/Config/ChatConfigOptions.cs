namespace OpenDeepWiki.Chat.Config;

/// <summary>
/// Chat 配置选项
/// </summary>
public class ChatConfigOptions
{
    /// <summary>
    /// 配置节名称
    /// </summary>
    public const string SectionName = "Chat";
    
    /// <summary>
    /// 是否在启动时验证配置
    /// </summary>
    public bool ValidateOnStartup { get; set; } = true;
    
    /// <summary>
    /// 配置缓存过期时间（秒）
    /// </summary>
    public int CacheExpirationSeconds { get; set; } = 300;
    
    /// <summary>
    /// 是否启用配置热重载
    /// </summary>
    public bool EnableHotReload { get; set; } = true;
}
