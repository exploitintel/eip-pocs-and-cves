using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户OAuth登录关联实体
/// </summary>
public class UserOAuth : AggregateRoot<string>
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// OAuth提供商ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string OAuthProviderId { get; set; } = string.Empty;

    /// <summary>
    /// OAuth提供商用户ID（第三方平台的用户ID）
    /// </summary>
    [Required]
    [StringLength(200)]
    public string OAuthUserId { get; set; } = string.Empty;

    /// <summary>
    /// OAuth提供商用户名称
    /// </summary>
    [StringLength(200)]
    public string? OAuthUserName { get; set; }

    /// <summary>
    /// OAuth提供商用户邮箱
    /// </summary>
    [StringLength(200)]
    public string? OAuthUserEmail { get; set; }

    /// <summary>
    /// OAuth提供商用户头像
    /// </summary>
    [StringLength(500)]
    public string? OAuthUserAvatar { get; set; }

    /// <summary>
    /// 访问令牌（加密存储）
    /// </summary>
    [StringLength(1000)]
    public string? AccessToken { get; set; }

    /// <summary>
    /// 刷新令牌（加密存储）
    /// </summary>
    [StringLength(1000)]
    public string? RefreshToken { get; set; }

    /// <summary>
    /// 令牌过期时间
    /// </summary>
    public DateTime? TokenExpiresAt { get; set; }

    /// <summary>
    /// 令牌作用域
    /// </summary>
    [StringLength(500)]
    public string? Scope { get; set; }

    /// <summary>
    /// 令牌类型
    /// </summary>
    [StringLength(50)]
    public string? TokenType { get; set; }

    /// <summary>
    /// 是否已绑定（true表示已绑定到现有用户，false表示临时绑定）
    /// </summary>
    public bool IsBound { get; set; } = false;

    /// <summary>
    /// 最后登录时间
    /// </summary>
    public DateTime? LastLoginAt { get; set; }

    /// <summary>
    /// 用户实体导航属性
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }

    /// <summary>
    /// OAuth提供商实体导航属性
    /// </summary>
    [ForeignKey("OAuthProviderId")]
    public virtual OAuthProvider? OAuthProvider { get; set; }
}
