using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户-部门关联实体
/// </summary>
public class UserDepartment : AggregateRoot<string>
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 部门ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string DepartmentId { get; set; } = string.Empty;

    /// <summary>
    /// 是否为部门主管
    /// </summary>
    public bool IsManager { get; set; } = false;

    /// <summary>
    /// 用户导航属性
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }

    /// <summary>
    /// 部门导航属性
    /// </summary>
    [ForeignKey("DepartmentId")]
    public virtual Department? Department { get; set; }
}
