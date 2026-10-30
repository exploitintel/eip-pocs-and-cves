namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 消息队列配置选项
/// </summary>
public class MessageQueueOptions
{
    /// <summary>
    /// 配置节名称
    /// </summary>
    public const string SectionName = "Chat:MessageQueue";
    
    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;
    
    /// <summary>
    /// 默认重试延迟（秒）
    /// </summary>
    public int DefaultRetryDelaySeconds { get; set; } = 30;
    
    /// <summary>
    /// 消息发送间隔（毫秒）
    /// </summary>
    public int MessageIntervalMs { get; set; } = 500;
    
    /// <summary>
    /// 短消息合并阈值（字符数）
    /// </summary>
    public int MergeThreshold { get; set; } = 500;
    
    /// <summary>
    /// 短消息合并时间窗口（毫秒）
    /// </summary>
    public int MergeWindowMs { get; set; } = 2000;
}
