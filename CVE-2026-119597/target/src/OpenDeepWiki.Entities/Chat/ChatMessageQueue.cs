using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 消息队列实体
/// 用于处理连续消息发送和平台限流
/// </summary>
public class ChatMessageQueue : AggregateRoot<Guid>
{
    /// <summary>
    /// 关联的会话ID（可选）
    /// </summary>
    public Guid? SessionId { get; set; }

    /// <summary>
    /// 目标用户ID
    /// </summary>
    [Required]
    [StringLength(200)]
    public string TargetUserId { get; set; } = string.Empty;

    /// <summary>
    /// 平台标识
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Platform { get; set; } = string.Empty;

    /// <summary>
    /// 消息内容（JSON）
    /// </summary>
    [Required]
    public string MessageContent { get; set; } = string.Empty;

    /// <summary>
    /// 队列类型（Incoming/Outgoing/Retry）
    /// </summary>
    [Required]
    [StringLength(20)]
    public string QueueType { get; set; } = "Incoming";

    /// <summary>
    /// 处理状态（Pending/Processing/Completed/Failed）
    /// </summary>
    [Required]
    [StringLength(20)]
    public string Status { get; set; } = "Pending";

    /// <summary>
    /// 重试次数
    /// </summary>
    public int RetryCount { get; set; } = 0;

    /// <summary>
    /// 计划执行时间
    /// </summary>
    public DateTime? ScheduledAt { get; set; }

    /// <summary>
    /// 错误信息
    /// </summary>
    public string? ErrorMessage { get; set; }
}
