using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户活动类型
/// </summary>
public enum UserActivityType
{
    /// <summary>
    /// 浏览仓库
    /// </summary>
    View = 0,

    /// <summary>
    /// 搜索
    /// </summary>
    Search = 1,

    /// <summary>
    /// 收藏
    /// </summary>
    Bookmark = 2,

    /// <summary>
    /// 订阅
    /// </summary>
    Subscribe = 3,

    /// <summary>
    /// 分析仓库
    /// </summary>
    Analyze = 4
}

/// <summary>
/// 用户活动记录实体
/// 用于记录用户行为，支持推荐算法
/// </summary>
public class UserActivity : AggregateRoot<string>
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID（可选，搜索行为可能没有）
    /// </summary>
    [StringLength(36)]
    public string? RepositoryId { get; set; }

    /// <summary>
    /// 活动类型
    /// </summary>
    public UserActivityType ActivityType { get; set; }

    /// <summary>
    /// 活动权重（用于推荐算法）
    /// View=1, Search=2, Bookmark=3, Subscribe=4, Analyze=5
    /// </summary>
    public int Weight { get; set; } = 1;

    /// <summary>
    /// 浏览时长（秒），仅对 View 类型有效
    /// </summary>
    public int? Duration { get; set; }

    /// <summary>
    /// 搜索关键词，仅对 Search 类型有效
    /// </summary>
    [StringLength(500)]
    public string? SearchQuery { get; set; }

    /// <summary>
    /// 相关语言（用于语言偏好统计）
    /// </summary>
    [StringLength(50)]
    public string? Language { get; set; }

    /// <summary>
    /// 用户导航属性
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }

    /// <summary>
    /// 仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }
}
