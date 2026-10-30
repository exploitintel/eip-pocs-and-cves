using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户实体
/// </summary>
public class User : AggregateRoot<string>
{
    /// <summary>
    /// 用户名
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 邮箱
    /// </summary>
    [Required]
    [EmailAddress]
    [StringLength(100)]
    public string Email { get; set; } = string.Empty;

    /// <summary>
    /// 密码（加密存储）
    /// </summary>
    [StringLength(255)]
    public string? Password { get; set; }

    /// <summary>
    /// 头像URL
    /// </summary>
    [StringLength(500)]
    public string? Avatar { get; set; }

    /// <summary>
    /// 手机号
    /// </summary>
    [StringLength(20)]
    public string? Phone { get; set; }

    /// <summary>
    /// 用户状态：0-禁用，1-正常，2-待验证
    /// </summary>
    public int Status { get; set; } = 1;

    /// <summary>
    /// 是否为系统用户
    /// </summary>
    public bool IsSystem { get; set; } = false;

    /// <summary>
    /// 最后登录时间
    /// </summary>
    public DateTime? LastLoginAt { get; set; }

    /// <summary>
    /// 最后登录IP
    /// </summary>
    [StringLength(50)]
    public string? LastLoginIp { get; set; }

    /// <summary>
    /// 用户角色关联集合
    /// </summary>
    [NotMapped]
    public virtual ICollection<UserRole> UserRoles { get; set; } = new List<UserRole>();

    /// <summary>
    /// OAuth登录关联集合
    /// </summary>
    [NotMapped]
    public virtual ICollection<UserOAuth> UserOAuths { get; set; } = new List<UserOAuth>();
}
