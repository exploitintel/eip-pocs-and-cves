using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Models.Subscription;

/// <summary>
/// 添加订阅请求
/// </summary>
public class AddSubscriptionRequest
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;
}
