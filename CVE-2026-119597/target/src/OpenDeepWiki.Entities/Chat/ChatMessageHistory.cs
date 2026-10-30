using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 消息历史实体
/// 记录对话会话中的消息历史
/// </summary>
public class ChatMessageHistory : AggregateRoot<Guid>
{
    /// <summary>
    /// 关联的会话ID
    /// </summary>
    [Required]
    public Guid SessionId { get; set; }

    /// <summary>
    /// 消息ID（平台消息ID）
    /// </summary>
    [Required]
    [StringLength(200)]
    public string MessageId { get; set; } = string.Empty;

    /// <summary>
    /// 发送者标识
    /// </summary>
    [Required]
    [StringLength(200)]
    public string SenderId { get; set; } = string.Empty;

    /// <summary>
    /// 消息内容
    /// </summary>
    [Required]
    public string Content { get; set; } = string.Empty;

    /// <summary>
    /// 消息类型（Text/Image/File/Audio/Video/RichText/Card/Unknown）
    /// </summary>
    [Required]
    [StringLength(20)]
    public string MessageType { get; set; } = "Text";

    /// <summary>
    /// 消息角色（User/Assistant）
    /// </summary>
    [Required]
    [StringLength(20)]
    public string Role { get; set; } = "User";

    /// <summary>
    /// 消息时间戳
    /// </summary>
    public DateTime MessageTimestamp { get; set; }

    /// <summary>
    /// 消息元数据（JSON）
    /// </summary>
    public string? Metadata { get; set; }

    /// <summary>
    /// 关联的会话
    /// </summary>
    [ForeignKey("SessionId")]
    public virtual ChatSession Session { get; set; } = null!;
}
