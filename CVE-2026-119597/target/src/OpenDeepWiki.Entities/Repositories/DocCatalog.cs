using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 文档目录实体（支持树形结构）
/// </summary>
public class DocCatalog : AggregateRoot<string>
{
    /// <summary>
    /// 分支语言ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string BranchLanguageId { get; set; } = string.Empty;

    /// <summary>
    /// 父目录ID（null 表示根节点）
    /// </summary>
    [StringLength(36)]
    public string? ParentId { get; set; }

    /// <summary>
    /// 目录标题
    /// </summary>
    [Required]
    [StringLength(500)]
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// URL 友好的路径，如 "1-overview"
    /// </summary>
    [Required]
    [StringLength(1000)]
    public string Path { get; set; } = string.Empty;

    /// <summary>
    /// 排序顺序
    /// </summary>
    public int Order { get; set; }

    /// <summary>
    /// 关联的文档文件ID
    /// </summary>
    [StringLength(36)]
    public string? DocFileId { get; set; }

    /// <summary>
    /// 父目录导航属性
    /// </summary>
    [ForeignKey("ParentId")]
    public virtual DocCatalog? Parent { get; set; }

    /// <summary>
    /// 子目录集合
    /// </summary>
    public virtual ICollection<DocCatalog> Children { get; set; } = new List<DocCatalog>();

    /// <summary>
    /// 分支语言导航属性
    /// </summary>
    [ForeignKey("BranchLanguageId")]
    public virtual BranchLanguage? BranchLanguage { get; set; }

    /// <summary>
    /// 文档文件导航属性
    /// </summary>
    [ForeignKey("DocFileId")]
    public virtual DocFile? DocFile { get; set; }
}
