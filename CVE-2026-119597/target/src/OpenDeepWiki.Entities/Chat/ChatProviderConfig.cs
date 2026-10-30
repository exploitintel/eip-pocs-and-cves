using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// Provider 配置实体
/// 存储各平台的接入配置
/// </summary>
public class ChatProviderConfig : AggregateRoot<Guid>
{
    /// <summary>
    /// 平台标识
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Platform { get; set; } = string.Empty;

    /// <summary>
    /// 显示名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string DisplayName { get; set; } = string.Empty;

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsEnabled { get; set; } = true;

    /// <summary>
    /// 配置数据（加密JSON）
    /// </summary>
    [Required]
    public string ConfigData { get; set; } = string.Empty;

    /// <summary>
    /// Webhook URL
    /// </summary>
    [StringLength(500)]
    public string? WebhookUrl { get; set; }

    /// <summary>
    /// 消息发送间隔（毫秒）
    /// </summary>
    public int MessageInterval { get; set; } = 500;

    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;
}
