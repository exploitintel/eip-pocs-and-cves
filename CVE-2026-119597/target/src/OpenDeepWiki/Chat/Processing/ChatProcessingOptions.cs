namespace OpenDeepWiki.Chat.Processing;

/// <summary>
/// 消息处理配置选项
/// </summary>
public class ChatProcessingOptions
{
    /// <summary>
    /// 配置节名称
    /// </summary>
    public const string SectionName = "Chat:Processing";

    /// <summary>
    /// 最大并发处理数
    /// </summary>
    public int MaxConcurrency { get; set; } = 5;

    /// <summary>
    /// 队列轮询间隔（毫秒）
    /// </summary>
    public int PollingIntervalMs { get; set; } = 1000;

    /// <summary>
    /// 错误后延迟（毫秒）
    /// </summary>
    public int ErrorDelayMs { get; set; } = 5000;

    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;

    /// <summary>
    /// 基础重试延迟（秒）
    /// </summary>
    public int BaseRetryDelaySeconds { get; set; } = 30;

    /// <summary>
    /// 是否启用 Worker
    /// </summary>
    public bool Enabled { get; set; } = true;
}
