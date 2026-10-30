using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 角色实体
/// </summary>
public class Role : AggregateRoot<string>
{
    /// <summary>
    /// 角色名称
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 角色描述
    /// </summary>
    [StringLength(200)]
    public string? Description { get; set; }

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 是否为系统角色（系统角色不能删除和修改）
    /// </summary>
    public bool IsSystemRole { get; set; } = false;

    /// <summary>
    /// 用户角色关联集合
    /// </summary>
    [NotMapped]
    public virtual ICollection<UserRole> UserRoles { get; set; } = new List<UserRole>();
}
