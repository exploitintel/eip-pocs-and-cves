namespace OpenDeepWiki.Models.Admin;

/// <summary>
/// 仪表盘统计数据响应
/// </summary>
public class DashboardStatisticsResponse
{
    /// <summary>
    /// 每日仓库统计
    /// </summary>
    public List<DailyRepositoryStatistic> RepositoryStats { get; set; } = new();

    /// <summary>
    /// 每日用户统计
    /// </summary>
    public List<DailyUserStatistic> UserStats { get; set; } = new();
}

/// <summary>
/// 每日仓库统计
/// </summary>
public class DailyRepositoryStatistic
{
    public DateTime Date { get; set; }
    public int ProcessedCount { get; set; }
    public int SubmittedCount { get; set; }
}

/// <summary>
/// 每日用户统计
/// </summary>
public class DailyUserStatistic
{
    public DateTime Date { get; set; }
    public int NewUserCount { get; set; }
}

/// <summary>
/// Token 消耗统计响应
/// </summary>
public class TokenUsageStatisticsResponse
{
    public List<DailyTokenUsage> DailyUsages { get; set; } = new();
    public long TotalInputTokens { get; set; }
    public long TotalOutputTokens { get; set; }
    public long TotalCachedInputTokens { get; set; }
    public long TotalCacheCreationInputTokens { get; set; }
    public long TotalTokens { get; set; }
    public decimal InputCacheHitRate { get; set; }
    public decimal TotalInputCost { get; set; }
    public decimal TotalOutputCost { get; set; }
    public decimal TotalCost { get; set; }
}

/// <summary>
/// 每日 Token 消耗
/// </summary>
public class DailyTokenUsage
{
    public DateTime Date { get; set; }
    public long InputTokens { get; set; }
    public long OutputTokens { get; set; }
    public long CachedInputTokens { get; set; }
    public long CacheCreationInputTokens { get; set; }
    public long TotalTokens { get; set; }
    public decimal InputCacheHitRate { get; set; }
    public decimal InputCost { get; set; }
    public decimal OutputCost { get; set; }
    public decimal TotalCost { get; set; }
}
