using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户角色关联实体（多对多关系）
/// </summary>
public class UserRole : AggregateRoot<string>
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 角色ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RoleId { get; set; } = string.Empty;

    /// <summary>
    /// 用户实体导航属性
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }

    /// <summary>
    /// 角色实体导航属性
    /// </summary>
    [ForeignKey("RoleId")]
    public virtual Role? Role { get; set; }
}
