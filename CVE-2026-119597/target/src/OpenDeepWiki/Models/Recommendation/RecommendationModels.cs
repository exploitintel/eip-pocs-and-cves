namespace OpenDeepWiki.Models.Recommendation;

/// <summary>
/// 推荐配置
/// </summary>
public class RecommendationConfig
{
    /// <summary>
    /// 热度权重 (stars, forks, watchers)
    /// </summary>
    public double PopularityWeight { get; set; } = 0.20;

    /// <summary>
    /// 订阅数权重
    /// </summary>
    public double SubscriptionWeight { get; set; } = 0.15;

    /// <summary>
    /// 时间衰减权重
    /// </summary>
    public double TimeDecayWeight { get; set; } = 0.10;

    /// <summary>
    /// 用户偏好权重（基于历史行为）
    /// </summary>
    public double UserPreferenceWeight { get; set; } = 0.20;

    /// <summary>
    /// 私有仓库语言匹配权重
    /// </summary>
    public double PrivateRepoLanguageWeight { get; set; } = 0.20;

    /// <summary>
    /// 协同过滤权重
    /// </summary>
    public double CollaborativeWeight { get; set; } = 0.15;

    /// <summary>
    /// 时间窗口内的趋势热度权重，仅在指定时间窗口时生效
    /// </summary>
    public double TrendingWeight { get; set; } = 0.00;
}

/// <summary>
/// 推荐请求
/// </summary>
public class RecommendationRequest
{
    /// <summary>
    /// 用户ID（可选，匿名用户使用热度推荐）
    /// </summary>
    public string? UserId { get; set; }

    /// <summary>
    /// 返回数量
    /// </summary>
    public int Limit { get; set; } = 20;

    /// <summary>
    /// 推荐策略：default, popular, personalized, explore
    /// </summary>
    public string Strategy { get; set; } = "default";

    /// <summary>
    /// 语言过滤（可选）
    /// </summary>
    public string? LanguageFilter { get; set; }

    /// <summary>
    /// 时间窗口天数（可选），例如 7 表示只看最近一周活跃或新增的仓库
    /// </summary>
    public int? TimeWindowDays { get; set; }
}

/// <summary>
/// 推荐响应
/// </summary>
public class RecommendationResponse
{
    /// <summary>
    /// 推荐仓库列表
    /// </summary>
    public List<RecommendedRepository> Items { get; set; } = new();

    /// <summary>
    /// 使用的推荐策略
    /// </summary>
    public string Strategy { get; set; } = string.Empty;

    /// <summary>
    /// 总候选数量
    /// </summary>
    public int TotalCandidates { get; set; }

    /// <summary>
    /// 实际生效的时间窗口天数，未启用时为 null
    /// </summary>
    public int? TimeWindowDays { get; set; }

    /// <summary>
    /// 时间窗口内没有任何数据，已回退到全量排序
    /// </summary>
    public bool TimeWindowFallback { get; set; }
}

/// <summary>
/// 推荐仓库项
/// </summary>
public class RecommendedRepository
{
    public string Id { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public string OrgName { get; set; } = string.Empty;
    public string? PrimaryLanguage { get; set; }
    public int StarCount { get; set; }
    public int ForkCount { get; set; }
    public int BookmarkCount { get; set; }
    public int SubscriptionCount { get; set; }
    public int ViewCount { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }

    /// <summary>
    /// 综合推荐得分
    /// </summary>
    public double Score { get; set; }

    /// <summary>
    /// 得分明细
    /// </summary>
    public ScoreBreakdown? ScoreBreakdown { get; set; }

    /// <summary>
    /// 推荐理由
    /// </summary>
    public string? RecommendReason { get; set; }
}

/// <summary>
/// 得分明细
/// </summary>
public class ScoreBreakdown
{
    public double Popularity { get; set; }
    public double Subscription { get; set; }
    public double TimeDecay { get; set; }
    public double UserPreference { get; set; }
    public double PrivateRepoLanguage { get; set; }
    public double Collaborative { get; set; }
    public double Trending { get; set; }
}

/// <summary>
/// 记录用户活动请求
/// </summary>
public class RecordActivityRequest
{
    public string UserId { get; set; } = string.Empty;
    public string? RepositoryId { get; set; }
    public string ActivityType { get; set; } = string.Empty;
    public int? Duration { get; set; }
    public string? SearchQuery { get; set; }
    public string? Language { get; set; }
}

/// <summary>
/// 记录用户活动响应
/// </summary>
public class RecordActivityResponse
{
    public bool Success { get; set; }
    public string? ErrorMessage { get; set; }
}

/// <summary>
/// 标记不感兴趣请求
/// </summary>
public class DislikeRequest
{
    public string UserId { get; set; } = string.Empty;
    public string RepositoryId { get; set; } = string.Empty;
    public string? Reason { get; set; }
}

/// <summary>
/// 标记不感兴趣响应
/// </summary>
public class DislikeResponse
{
    public bool Success { get; set; }
    public string? ErrorMessage { get; set; }
}

/// <summary>
/// 可用语言列表响应
/// </summary>
public class AvailableLanguagesResponse
{
    public List<LanguageInfo> Languages { get; set; } = new();
}

/// <summary>
/// 语言信息
/// </summary>
public class LanguageInfo
{
    public string Name { get; set; } = string.Empty;
    public int Count { get; set; }
}
