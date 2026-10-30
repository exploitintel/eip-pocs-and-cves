using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 聊天分享快照实体
/// 用于存储创建分享时的对话内容和元数据
/// </summary>
public class ChatShareSnapshot : AggregateRoot<Guid>
{
    /// <summary>
    /// 对外分享ID（随机Token）
    /// </summary>
    [Required]
    [StringLength(100)]
    public string ShareId { get; set; } = string.Empty;

    /// <summary>
    /// 分享标题
    /// </summary>
    [Required]
    [StringLength(200)]
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// 分享描述
    /// </summary>
    [StringLength(500)]
    public string? Description { get; set; }

    /// <summary>
    /// 分享创建者（可为空）
    /// </summary>
    [StringLength(200)]
    public string? CreatedBy { get; set; }

    /// <summary>
    /// 分享内容快照（JSON）
    /// </summary>
    [Required]
    public string SnapshotJson { get; set; } = string.Empty;

    /// <summary>
    /// 分享元数据（JSON）
    /// </summary>
    public string? Metadata { get; set; }

    /// <summary>
    /// 分享创建时间（UTC）
    /// </summary>
    public new DateTime CreatedAt { get; set; } = DateTime.UtcNow;

    /// <summary>
    /// 分享过期时间（UTC）
    /// </summary>
    public DateTime? ExpiresAt { get; set; }

    /// <summary>
    /// 分享撤销时间（UTC）
    /// </summary>
    public DateTime? RevokedAt { get; set; }
}
