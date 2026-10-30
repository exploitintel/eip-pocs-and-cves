namespace OpenDeepWiki.Chat.Sessions;

/// <summary>
/// 会话管理器配置选项
/// </summary>
public class SessionManagerOptions
{
    /// <summary>
    /// 配置节名称
    /// </summary>
    public const string SectionName = "Chat:Session";
    
    /// <summary>
    /// 最大历史消息数量，默认100条
    /// </summary>
    public int MaxHistoryCount { get; set; } = 100;
    
    /// <summary>
    /// 会话过期时间（分钟），默认30分钟
    /// </summary>
    public int SessionExpirationMinutes { get; set; } = 30;
    
    /// <summary>
    /// 缓存过期时间（分钟），默认10分钟
    /// </summary>
    public int CacheExpirationMinutes { get; set; } = 10;
    
    /// <summary>
    /// 是否启用缓存，默认启用
    /// </summary>
    public bool EnableCache { get; set; } = true;
}
