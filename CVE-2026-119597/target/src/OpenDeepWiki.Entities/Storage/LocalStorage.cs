using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 本地存储实体（用于存储文件）
/// </summary>
public class LocalStorage : AggregateRoot<string>
{
    /// <summary>
    /// 文件名称
    /// </summary>
    [Required]
    [StringLength(255)]
    public string FileName { get; set; } = string.Empty;

    /// <summary>
    /// 文件扩展名
    /// </summary>
    [Required]
    [StringLength(50)]
    public string FileExtension { get; set; } = string.Empty;

    /// <summary>
    /// 文件大小（字节）
    /// </summary>
    public long FileSize { get; set; } = 0;

    /// <summary>
    /// 文件类型（MIME类型）
    /// </summary>
    [Required]
    [StringLength(100)]
    public string ContentType { get; set; } = string.Empty;

    /// <summary>
    /// 文件存储路径（相对路径）
    /// </summary>
    [Required]
    [StringLength(500)]
    public string FilePath { get; set; } = string.Empty;

    /// <summary>
    /// 文件哈希值（MD5/SHA256）
    /// </summary>
    [StringLength(128)]
    public string? FileHash { get; set; }

    /// <summary>
    /// 文件分类（如：avatar, document, image, video, audio）
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Category { get; set; } = string.Empty;

    /// <summary>
    /// 上传者ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UploaderId { get; set; } = string.Empty;

    /// <summary>
    /// 关联业务ID（如：用户ID、仓库ID等）
    /// </summary>
    [StringLength(36)]
    public string? BusinessId { get; set; }

    /// <summary>
    /// 关联业务类型（如：User, Warehouse, Document）
    /// </summary>
    [StringLength(50)]
    public string? BusinessType { get; set; }

    /// <summary>
    /// 文件状态：0-临时，1-永久，2-已删除
    /// </summary>
    public int Status { get; set; } = 1;

    /// <summary>
    /// 是否公开访问
    /// </summary>
    public bool IsPublic { get; set; } = false;

    /// <summary>
    /// 访问次数
    /// </summary>
    public int AccessCount { get; set; } = 0;

    /// <summary>
    /// 最后访问时间
    /// </summary>
    public DateTime? LastAccessAt { get; set; }

    /// <summary>
    /// 过期时间（临时文件）
    /// </summary>
    public DateTime? ExpiredAt { get; set; }

    /// <summary>
    /// 元数据（JSON格式，存储自定义属性）
    /// </summary>
    [StringLength(2000)]
    public string? Metadata { get; set; }

    /// <summary>
    /// 用户实体导航属性（上传者）
    /// </summary>
    [ForeignKey("UploaderId")]
    public virtual User? Uploader { get; set; }

    /// <summary>
    /// 标记为已删除
    /// </summary>
    public override void MarkAsDeleted()
    {
        base.MarkAsDeleted();
        Status = 2;
    }

    /// <summary>
    /// 增加访问次数
    /// </summary>
    public void IncrementAccessCount()
    {
        AccessCount++;
        LastAccessAt = DateTime.UtcNow;
    }
}
