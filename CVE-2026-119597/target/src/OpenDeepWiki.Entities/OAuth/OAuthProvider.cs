using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// OAuth2提供商实体
/// </summary>
public class OAuthProvider : AggregateRoot<string>
{
    /// <summary>
    /// 提供商名称（如：github, google, microsoft, feishu, gitee）
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 提供商显示名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string DisplayName { get; set; } = string.Empty;

    /// <summary>
    /// OAuth2授权端点URL
    /// </summary>
    [Required]
    [StringLength(500)]
    public string AuthorizationUrl { get; set; } = string.Empty;

    /// <summary>
    /// OAuth2令牌端点URL
    /// </summary>
    [Required]
    [StringLength(500)]
    public string TokenUrl { get; set; } = string.Empty;

    /// <summary>
    /// OAuth2用户信息端点URL
    /// </summary>
    [Required]
    [StringLength(500)]
    public string UserInfoUrl { get; set; } = string.Empty;

    /// <summary>
    /// OAuth2客户端ID
    /// </summary>
    [Required]
    [StringLength(200)]
    public string ClientId { get; set; } = string.Empty;

    /// <summary>
    /// OAuth2客户端密钥（加密存储）
    /// </summary>
    [Required]
    [StringLength(500)]
    public string ClientSecret { get; set; } = string.Empty;

    /// <summary>
    /// 回调URL
    /// </summary>
    [Required]
    [StringLength(500)]
    public string RedirectUri { get; set; } = string.Empty;

    /// <summary>
    /// 授权范围（多个用空格分隔）
    /// </summary>
    [StringLength(500)]
    public string? Scope { get; set; }

    /// <summary>
    /// 用户信息映射配置（JSON格式）
    /// </summary>
    /// <example>
    /// {
    ///   "id": "id",
    ///   "name": "name",
    ///   "email": "email",
    ///   "avatar": "avatar_url"
    /// }
    /// </example>
    [StringLength(1000)]
    public string? UserInfoMapping { get; set; }

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 是否需要邮箱验证
    /// </summary>
    public bool RequireEmailVerification { get; set; } = false;

    /// <summary>
    /// OAuth用户关联集合
    /// </summary>
    [NotMapped]
    public virtual ICollection<UserOAuth> UserOAuths { get; set; } = new List<UserOAuth>();
}
