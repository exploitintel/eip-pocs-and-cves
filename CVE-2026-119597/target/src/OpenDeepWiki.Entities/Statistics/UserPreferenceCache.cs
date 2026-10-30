using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户偏好缓存实体
/// 定期聚合计算用户的语言和主题偏好
/// </summary>
public class UserPreferenceCache : AggregateRoot<string>
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 语言偏好权重（JSON格式）
    /// 例如: {"C#": 0.5, "TypeScript": 0.3, "Python": 0.2}
    /// </summary>
    [StringLength(2000)]
    public string? LanguageWeights { get; set; }

    /// <summary>
    /// 主题偏好权重（JSON格式）
    /// 例如: {"web": 0.4, "api": 0.3, "database": 0.3}
    /// </summary>
    [StringLength(2000)]
    public string? TopicWeights { get; set; }

    /// <summary>
    /// 用户私有仓库的语言分布（JSON格式）
    /// 从用户自己添加的仓库中提取
    /// </summary>
    [StringLength(2000)]
    public string? PrivateRepoLanguages { get; set; }

    /// <summary>
    /// 最后计算时间
    /// </summary>
    public DateTime LastCalculatedAt { get; set; } = DateTime.UtcNow;
}
