using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 部门实体
/// </summary>
public class Department : AggregateRoot<string>
{
    /// <summary>
    /// 部门名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 父部门ID
    /// </summary>
    [StringLength(36)]
    public string? ParentId { get; set; }

    /// <summary>
    /// 部门描述
    /// </summary>
    [StringLength(500)]
    public string? Description { get; set; }

    /// <summary>
    /// 排序号
    /// </summary>
    public int SortOrder { get; set; } = 0;

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 父部门导航属性
    /// </summary>
    [ForeignKey("ParentId")]
    public virtual Department? Parent { get; set; }
}
