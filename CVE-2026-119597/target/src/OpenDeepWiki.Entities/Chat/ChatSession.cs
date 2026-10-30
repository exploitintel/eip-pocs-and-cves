using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 对话会话实体
/// 维护用户与 Agent 之间的对话上下文
/// </summary>
public class ChatSession : AggregateRoot<Guid>
{
    /// <summary>
    /// 用户标识（平台用户ID）
    /// </summary>
    [Required]
    [StringLength(200)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 平台标识
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Platform { get; set; } = string.Empty;

    /// <summary>
    /// 会话状态（Active/Processing/Waiting/Expired/Closed）
    /// </summary>
    [Required]
    [StringLength(20)]
    public string State { get; set; } = "Active";

    /// <summary>
    /// 最后活动时间
    /// </summary>
    public DateTime LastActivityAt { get; set; } = DateTime.UtcNow;

    /// <summary>
    /// 会话元数据（JSON）
    /// </summary>
    public string? Metadata { get; set; }

    /// <summary>
    /// 关联的消息历史
    /// </summary>
    public virtual ICollection<ChatMessageHistory> Messages { get; set; } = new List<ChatMessageHistory>();
}
