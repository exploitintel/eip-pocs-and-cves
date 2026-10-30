namespace OpenDeepWiki.Chat.Providers;

/// <summary>
/// Provider 配置选项
/// </summary>
public class ProviderOptions
{
    /// <summary>
    /// 是否启用
    /// </summary>
    public bool Enabled { get; set; } = true;
    
    /// <summary>
    /// 消息发送间隔（毫秒）
    /// </summary>
    public int MessageInterval { get; set; } = 500;
    
    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;
    
    /// <summary>
    /// 重试延迟基数（毫秒）
    /// </summary>
    public int RetryDelayBase { get; set; } = 1000;
    
    /// <summary>
    /// 请求超时时间（秒）
    /// </summary>
    public int RequestTimeout { get; set; } = 30;
}
