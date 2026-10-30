using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 仓库指派实体
/// </summary>
public class RepositoryAssignment : AggregateRoot<string>
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 部门ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string DepartmentId { get; set; } = string.Empty;

    /// <summary>
    /// 指派用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string AssigneeUserId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    /// <summary>
    /// 部门导航属性
    /// </summary>
    [ForeignKey("DepartmentId")]
    public virtual Department? Department { get; set; }

    /// <summary>
    /// 指派用户导航属性
    /// </summary>
    [ForeignKey("AssigneeUserId")]
    public virtual User? AssigneeUser { get; set; }
}
