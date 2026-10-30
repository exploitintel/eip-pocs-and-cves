using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Recommendation;

namespace OpenDeepWiki.Services.Recommendation;

/// <summary>
/// 推荐服务
/// 实现基于多维度的混合推荐算法
/// </summary>
public class RecommendationService
{
    private readonly IContext _context;
    private readonly ILogger<RecommendationService> _logger;

    public RecommendationService(IContext context, ILogger<RecommendationService> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <summary>
    /// 获取推荐仓库列表
    /// </summary>
    public async Task<RecommendationResponse> GetRecommendationsAsync(
        RecommendationRequest request,
        CancellationToken cancellationToken = default)
    {
        var config = GetConfigByStrategy(request.Strategy);

        // 1. 获取候选仓库池（公开且已完成处理的仓库）
        var candidateQuery = _context.Repositories
            .AsNoTracking()
            .Where(r => r.IsPublic && r.Status == RepositoryStatus.Completed && !r.IsDeleted);

        // 语言过滤
        if (!string.IsNullOrEmpty(request.LanguageFilter))
        {
            candidateQuery = candidateQuery.Where(r => r.PrimaryLanguage == request.LanguageFilter);
        }

        // 排除用户标记为不感兴趣的仓库
        if (!string.IsNullOrEmpty(request.UserId))
        {
            var dislikedRepoIds = await _context.UserDislikes
                .AsNoTracking()
                .Where(d => d.UserId == request.UserId)
                .Select(d => d.RepositoryId)
                .ToListAsync(cancellationToken);

            if (dislikedRepoIds.Count > 0)
            {
                candidateQuery = candidateQuery.Where(r => !dislikedRepoIds.Contains(r.Id));
            }
        }

        var candidates = await candidateQuery.ToListAsync(cancellationToken);
        var timeWindowDays = NormalizeTimeWindowDays(request.TimeWindowDays);

        if (candidates.Count == 0)
        {
            return new RecommendationResponse
            {
                Items = new List<RecommendedRepository>(),
                Strategy = request.Strategy,
                TotalCandidates = 0,
                TimeWindowDays = timeWindowDays
            };
        }

        // 2. 按时间窗口收敛候选池，并计算窗口内的趋势热度
        var trendingScores = new Dictionary<string, double>();
        var timeWindowFallback = false;

        if (timeWindowDays.HasValue)
        {
            var since = DateTime.UtcNow.AddDays(-timeWindowDays.Value);
            trendingScores = await CalculateTrendingScoresAsync(
                since, candidates.Select(c => c.Id).ToList(), cancellationToken);

            var windowed = candidates
                .Where(r => trendingScores.ContainsKey(r.Id) || (r.UpdatedAt ?? r.CreatedAt) >= since)
                .ToList();

            if (windowed.Count > 0)
            {
                candidates = windowed;
                config = ApplyTrendingWeight(config);
            }
            else
            {
                // 窗口内没有任何活跃或新增数据，回退到全量排序，避免返回空列表
                trendingScores.Clear();
                timeWindowFallback = true;
            }
        }

        var totalCandidates = candidates.Count;

        // 3. 获取用户相关数据（如果有用户ID）
        UserPreferenceData? userPreference = null;
        if (!string.IsNullOrEmpty(request.UserId))
        {
            userPreference = await GetUserPreferenceDataAsync(request.UserId, cancellationToken);
        }

        // 4. 获取协同过滤数据
        var collaborativeScores = new Dictionary<string, double>();
        if (!string.IsNullOrEmpty(request.UserId) && config.CollaborativeWeight > 0)
        {
            collaborativeScores = await CalculateCollaborativeScoresAsync(
                request.UserId, candidates.Select(c => c.Id).ToList(), cancellationToken);
        }

        // 5. 计算每个仓库的综合得分
        var scoredRepos = candidates.Select(repo =>
        {
            var breakdown = CalculateScoreBreakdown(
                repo, userPreference, collaborativeScores, trendingScores);
            var finalScore = CalculateFinalScore(breakdown, config);

            return new RecommendedRepository
            {
                Id = repo.Id,
                RepoName = repo.RepoName,
                OrgName = repo.OrgName,
                PrimaryLanguage = repo.PrimaryLanguage,
                StarCount = repo.StarCount,
                ForkCount = repo.ForkCount,
                BookmarkCount = repo.BookmarkCount,
                SubscriptionCount = repo.SubscriptionCount,
                ViewCount = repo.ViewCount,
                CreatedAt = repo.CreatedAt,
                UpdatedAt = repo.UpdatedAt,
                Score = finalScore,
                ScoreBreakdown = breakdown,
                RecommendReason = GenerateRecommendReason(
                    breakdown, userPreference, timeWindowFallback ? null : timeWindowDays)
            };
        })
        .OrderByDescending(r => r.Score)
        .Take(request.Limit)
        .ToList();

        return new RecommendationResponse
        {
            Items = scoredRepos,
            Strategy = request.Strategy,
            TotalCandidates = totalCandidates,
            TimeWindowDays = timeWindowDays,
            TimeWindowFallback = timeWindowFallback
        };
    }

    /// <summary>
    /// 归一化时间窗口天数，超出范围的输入视为不启用窗口
    /// </summary>
    private static int? NormalizeTimeWindowDays(int? days)
    {
        if (days is not > 0) return null;
        return Math.Min(days.Value, 365);
    }

    /// <summary>
    /// 启用时间窗口时重新分配权重，让窗口内的趋势热度占据主导
    /// </summary>
    private static RecommendationConfig ApplyTrendingWeight(RecommendationConfig config)
    {
        const double trendingWeight = 0.50;
        var scale = 1.0 - trendingWeight;

        return new RecommendationConfig
        {
            PopularityWeight = config.PopularityWeight * scale,
            SubscriptionWeight = config.SubscriptionWeight * scale,
            TimeDecayWeight = config.TimeDecayWeight * scale,
            UserPreferenceWeight = config.UserPreferenceWeight * scale,
            PrivateRepoLanguageWeight = config.PrivateRepoLanguageWeight * scale,
            CollaborativeWeight = config.CollaborativeWeight * scale,
            TrendingWeight = trendingWeight
        };
    }

    /// <summary>
    /// 统计时间窗口内的仓库热度
    /// 数据来源为窗口内新增的用户活动、收藏和订阅，结果归一化到 0-1
    /// </summary>
    private async Task<Dictionary<string, double>> CalculateTrendingScoresAsync(
        DateTime since,
        List<string> candidateRepoIds,
        CancellationToken cancellationToken)
    {
        const double bookmarkWeight = 3.0;
        const double subscriptionWeight = 4.0;

        var rawScores = new Dictionary<string, double>();

        var activities = await _context.UserActivities
            .AsNoTracking()
            .Where(a => a.CreatedAt >= since
                        && a.RepositoryId != null
                        && candidateRepoIds.Contains(a.RepositoryId))
            .GroupBy(a => a.RepositoryId!)
            .Select(g => new { RepoId = g.Key, Weight = g.Sum(a => a.Weight) })
            .ToListAsync(cancellationToken);

        foreach (var item in activities)
        {
            rawScores[item.RepoId] = item.Weight;
        }

        var bookmarks = await _context.UserBookmarks
            .AsNoTracking()
            .Where(b => b.CreatedAt >= since && candidateRepoIds.Contains(b.RepositoryId))
            .GroupBy(b => b.RepositoryId)
            .Select(g => new { RepoId = g.Key, Count = g.Count() })
            .ToListAsync(cancellationToken);

        foreach (var item in bookmarks)
        {
            rawScores[item.RepoId] = rawScores.GetValueOrDefault(item.RepoId) + item.Count * bookmarkWeight;
        }

        var subscriptions = await _context.UserSubscriptions
            .AsNoTracking()
            .Where(s => s.CreatedAt >= since && candidateRepoIds.Contains(s.RepositoryId))
            .GroupBy(s => s.RepositoryId)
            .Select(g => new { RepoId = g.Key, Count = g.Count() })
            .ToListAsync(cancellationToken);

        foreach (var item in subscriptions)
        {
            rawScores[item.RepoId] = rawScores.GetValueOrDefault(item.RepoId) + item.Count * subscriptionWeight;
        }

        if (rawScores.Count == 0)
        {
            return rawScores;
        }

        // 使用对数归一化，避免单个高热仓库压制其余结果
        var normalized = rawScores.ToDictionary(kvp => kvp.Key, kvp => Math.Log10(kvp.Value + 1));
        var maxScore = normalized.Values.Max();

        return maxScore <= 0
            ? rawScores.ToDictionary(kvp => kvp.Key, _ => 0.0)
            : normalized.ToDictionary(kvp => kvp.Key, kvp => kvp.Value / maxScore);
    }


    /// <summary>
    /// 根据策略获取配置
    /// </summary>
    private RecommendationConfig GetConfigByStrategy(string strategy)
    {
        return strategy.ToLower() switch
        {
            // 热门策略：侧重热度和订阅
            "popular" => new RecommendationConfig
            {
                PopularityWeight = 0.40,
                SubscriptionWeight = 0.30,
                TimeDecayWeight = 0.20,
                UserPreferenceWeight = 0.05,
                PrivateRepoLanguageWeight = 0.05,
                CollaborativeWeight = 0.00
            },
            // 个性化策略：侧重用户偏好
            "personalized" => new RecommendationConfig
            {
                PopularityWeight = 0.10,
                SubscriptionWeight = 0.10,
                TimeDecayWeight = 0.10,
                UserPreferenceWeight = 0.30,
                PrivateRepoLanguageWeight = 0.25,
                CollaborativeWeight = 0.15
            },
            // 探索策略：增加随机性和长尾内容
            "explore" => new RecommendationConfig
            {
                PopularityWeight = 0.15,
                SubscriptionWeight = 0.10,
                TimeDecayWeight = 0.25,
                UserPreferenceWeight = 0.15,
                PrivateRepoLanguageWeight = 0.15,
                CollaborativeWeight = 0.20
            },
            // 默认混合策略
            _ => new RecommendationConfig()
        };
    }

    /// <summary>
    /// 计算热度得分
    /// 使用对数归一化，避免大数值主导
    /// </summary>
    private double CalculatePopularityScore(Repository repo)
    {
        const double starWeight = 0.5;
        const double forkWeight = 0.3;
        const double viewWeight = 0.2;
        const double maxScore = 6.0; // log10(1000000) ≈ 6

        var normalizedStars = Math.Log10(repo.StarCount + 1);
        var normalizedForks = Math.Log10(repo.ForkCount + 1);
        var normalizedViews = Math.Log10(repo.ViewCount + 1);

        var score = (normalizedStars * starWeight +
                     normalizedForks * forkWeight +
                     normalizedViews * viewWeight) / maxScore;

        return Math.Min(score, 1.0);
    }

    /// <summary>
    /// 计算订阅得分
    /// 订阅数反映用户的持续关注意愿
    /// </summary>
    private double CalculateSubscriptionScore(Repository repo)
    {
        const double maxScore = 4.0; // log10(10000) = 4
        var normalizedSubs = Math.Log10(repo.SubscriptionCount + 1);
        return Math.Min(normalizedSubs / maxScore, 1.0);
    }

    /// <summary>
    /// 计算时间衰减得分
    /// 最近更新的仓库得分更高
    /// </summary>
    private double CalculateTimeDecayScore(Repository repo)
    {
        var lastUpdate = repo.UpdatedAt ?? repo.CreatedAt;
        var daysSinceUpdate = (DateTime.UtcNow - lastUpdate).TotalDays;

        // 半衰期设为60天
        const double halfLife = 60.0;
        return Math.Exp(-0.693 * daysSinceUpdate / halfLife);
    }

    /// <summary>
    /// 计算用户偏好得分
    /// 基于用户历史行为的语言偏好
    /// </summary>
    private double CalculateUserPreferenceScore(Repository repo, UserPreferenceData? userPref)
    {
        if (userPref == null) return 0.5; // 无用户数据时返回中性分数

        double score = 0;

        // 语言匹配 (60%)
        if (!string.IsNullOrEmpty(repo.PrimaryLanguage) &&
            userPref.LanguageWeights.TryGetValue(repo.PrimaryLanguage, out var langWeight))
        {
            score += 0.6 * langWeight;
        }

        // 排除已浏览/收藏的仓库，给新内容加分 (40%)
        if (!userPref.ViewedRepoIds.Contains(repo.Id) &&
            !userPref.BookmarkedRepoIds.Contains(repo.Id))
        {
            score += 0.4;
        }

        return Math.Min(score, 1.0);
    }

    /// <summary>
    /// 计算私有仓库语言匹配得分
    /// 基于用户自己添加的仓库的语言分布
    /// </summary>
    private double CalculatePrivateRepoLanguageScore(Repository repo, UserPreferenceData? userPref)
    {
        if (userPref == null || string.IsNullOrEmpty(repo.PrimaryLanguage))
            return 0.3; // 无数据时返回较低分数

        if (userPref.PrivateRepoLanguages.TryGetValue(repo.PrimaryLanguage, out var weight))
        {
            return weight;
        }

        return 0.1; // 语言不匹配时给予较低分数
    }


    /// <summary>
    /// 计算得分明细
    /// </summary>
    private ScoreBreakdown CalculateScoreBreakdown(
        Repository repo,
        UserPreferenceData? userPref,
        Dictionary<string, double> collaborativeScores,
        Dictionary<string, double> trendingScores)
    {
        return new ScoreBreakdown
        {
            Popularity = CalculatePopularityScore(repo),
            Subscription = CalculateSubscriptionScore(repo),
            TimeDecay = CalculateTimeDecayScore(repo),
            UserPreference = CalculateUserPreferenceScore(repo, userPref),
            PrivateRepoLanguage = CalculatePrivateRepoLanguageScore(repo, userPref),
            Collaborative = collaborativeScores.GetValueOrDefault(repo.Id, 0.3),
            Trending = trendingScores.GetValueOrDefault(repo.Id, 0.0)
        };
    }

    /// <summary>
    /// 计算最终得分
    /// </summary>
    private double CalculateFinalScore(ScoreBreakdown breakdown, RecommendationConfig config)
    {
        return breakdown.Popularity * config.PopularityWeight +
               breakdown.Subscription * config.SubscriptionWeight +
               breakdown.TimeDecay * config.TimeDecayWeight +
               breakdown.UserPreference * config.UserPreferenceWeight +
               breakdown.PrivateRepoLanguage * config.PrivateRepoLanguageWeight +
               breakdown.Collaborative * config.CollaborativeWeight +
               breakdown.Trending * config.TrendingWeight;
    }

    /// <summary>
    /// 生成推荐理由
    /// </summary>
    private string GenerateRecommendReason(
        ScoreBreakdown breakdown,
        UserPreferenceData? userPref,
        int? timeWindowDays)
    {
        var reasons = new List<string>();

        if (timeWindowDays.HasValue && breakdown.Trending > 0.5)
            reasons.Add($"近{timeWindowDays.Value}天热门");
        if (breakdown.Popularity > 0.7)
            reasons.Add("热门项目");
        if (breakdown.Subscription > 0.6)
            reasons.Add("高订阅量");
        if (breakdown.TimeDecay > 0.8)
            reasons.Add("近期活跃");
        if (breakdown.UserPreference > 0.6 && userPref != null)
            reasons.Add("符合您的偏好");
        if (breakdown.PrivateRepoLanguage > 0.6 && userPref != null)
            reasons.Add("与您的技术栈匹配");
        if (breakdown.Collaborative > 0.5)
            reasons.Add("相似用户推荐");

        return reasons.Count > 0 ? string.Join("、", reasons) : "综合推荐";
    }

    /// <summary>
    /// 获取用户偏好数据
    /// </summary>
    private async Task<UserPreferenceData> GetUserPreferenceDataAsync(
        string userId,
        CancellationToken cancellationToken)
    {
        var result = new UserPreferenceData();

        // 1. 获取用户已浏览的仓库ID
        var viewedRepoIds = await _context.UserActivities
            .AsNoTracking()
            .Where(a => a.UserId == userId && a.RepositoryId != null)
            .Select(a => a.RepositoryId!)
            .Distinct()
            .ToListAsync(cancellationToken);
        result.ViewedRepoIds = viewedRepoIds.ToHashSet();

        // 2. 获取用户已收藏的仓库ID
        var bookmarkedRepoIds = await _context.UserBookmarks
            .AsNoTracking()
            .Where(b => b.UserId == userId)
            .Select(b => b.RepositoryId)
            .ToListAsync(cancellationToken);
        result.BookmarkedRepoIds = bookmarkedRepoIds.ToHashSet();

        // 3. 计算语言偏好（基于用户活动）
        var languageActivities = await _context.UserActivities
            .AsNoTracking()
            .Where(a => a.UserId == userId && a.Language != null)
            .GroupBy(a => a.Language!)
            .Select(g => new { Language = g.Key, TotalWeight = g.Sum(a => a.Weight) })
            .ToListAsync(cancellationToken);

        if (languageActivities.Count > 0)
        {
            var maxWeight = languageActivities.Max(l => l.TotalWeight);
            foreach (var lang in languageActivities)
            {
                result.LanguageWeights[lang.Language] = (double)lang.TotalWeight / maxWeight;
            }
        }

        // 4. 获取用户私有仓库的语言分布
        var userRepoLanguages = await _context.Repositories
            .AsNoTracking()
            .Where(r => r.OwnerUserId == userId && r.PrimaryLanguage != null)
            .GroupBy(r => r.PrimaryLanguage!)
            .Select(g => new { Language = g.Key, Count = g.Count() })
            .ToListAsync(cancellationToken);

        if (userRepoLanguages.Count > 0)
        {
            var totalCount = userRepoLanguages.Sum(l => l.Count);
            foreach (var lang in userRepoLanguages)
            {
                result.PrivateRepoLanguages[lang.Language] = (double)lang.Count / totalCount;
            }
        }

        return result;
    }

    /// <summary>
    /// 计算协同过滤得分
    /// 基于相似用户的行为推荐
    /// </summary>
    private async Task<Dictionary<string, double>> CalculateCollaborativeScoresAsync(
        string userId,
        List<string> candidateRepoIds,
        CancellationToken cancellationToken)
    {
        var scores = new Dictionary<string, double>();

        // 1. 获取目标用户的收藏和订阅
        var userRepoIds = await _context.UserBookmarks
            .AsNoTracking()
            .Where(b => b.UserId == userId)
            .Select(b => b.RepositoryId)
            .Union(_context.UserSubscriptions
                .AsNoTracking()
                .Where(s => s.UserId == userId)
                .Select(s => s.RepositoryId))
            .ToListAsync(cancellationToken);

        if (userRepoIds.Count == 0)
        {
            return scores;
        }

        // 2. 找到收藏/订阅了相同仓库的其他用户
        var similarUserIds = await _context.UserBookmarks
            .AsNoTracking()
            .Where(b => userRepoIds.Contains(b.RepositoryId) && b.UserId != userId)
            .Select(b => b.UserId)
            .Union(_context.UserSubscriptions
                .AsNoTracking()
                .Where(s => userRepoIds.Contains(s.RepositoryId) && s.UserId != userId)
                .Select(s => s.UserId))
            .Distinct()
            .Take(100) // 限制相似用户数量
            .ToListAsync(cancellationToken);

        if (similarUserIds.Count == 0)
        {
            return scores;
        }

        // 3. 获取相似用户收藏/订阅的仓库及其频次
        var similarUserRepos = await _context.UserBookmarks
            .AsNoTracking()
            .Where(b => similarUserIds.Contains(b.UserId) && candidateRepoIds.Contains(b.RepositoryId))
            .GroupBy(b => b.RepositoryId)
            .Select(g => new { RepoId = g.Key, Count = g.Count() })
            .ToListAsync(cancellationToken);

        var subscriptionRepos = await _context.UserSubscriptions
            .AsNoTracking()
            .Where(s => similarUserIds.Contains(s.UserId) && candidateRepoIds.Contains(s.RepositoryId))
            .GroupBy(s => s.RepositoryId)
            .Select(g => new { RepoId = g.Key, Count = g.Count() })
            .ToListAsync(cancellationToken);

        // 4. 合并并计算得分
        var combinedScores = new Dictionary<string, int>();
        foreach (var item in similarUserRepos)
        {
            combinedScores[item.RepoId] = item.Count;
        }
        foreach (var item in subscriptionRepos)
        {
            if (combinedScores.ContainsKey(item.RepoId))
                combinedScores[item.RepoId] += item.Count * 2; // 订阅权重更高
            else
                combinedScores[item.RepoId] = item.Count * 2;
        }

        if (combinedScores.Count > 0)
        {
            var maxCount = combinedScores.Values.Max();
            foreach (var kvp in combinedScores)
            {
                scores[kvp.Key] = (double)kvp.Value / maxCount;
            }
        }

        return scores;
    }

    /// <summary>
    /// 记录用户活动
    /// </summary>
    public async Task<bool> RecordActivityAsync(
        RecordActivityRequest request,
        CancellationToken cancellationToken = default)
    {
        try
        {
            if (!Enum.TryParse<UserActivityType>(request.ActivityType, true, out var activityType))
            {
                _logger.LogWarning("Invalid activity type: {ActivityType}", request.ActivityType);
                return false;
            }

            var weight = activityType switch
            {
                UserActivityType.View => 1,
                UserActivityType.Search => 2,
                UserActivityType.Bookmark => 3,
                UserActivityType.Subscribe => 4,
                UserActivityType.Analyze => 5,
                _ => 1
            };

            var activity = new UserActivity
            {
                Id = Guid.NewGuid().ToString(),
                UserId = request.UserId,
                RepositoryId = request.RepositoryId,
                ActivityType = activityType,
                Weight = weight,
                Duration = request.Duration,
                SearchQuery = request.SearchQuery,
                Language = request.Language
            };

            _context.UserActivities.Add(activity);
            await _context.SaveChangesAsync(cancellationToken);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to record user activity");
            return false;
        }
    }

    /// <summary>
    /// 更新用户偏好缓存
    /// </summary>
    public async Task UpdateUserPreferenceCacheAsync(
        string userId,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var prefData = await GetUserPreferenceDataAsync(userId, cancellationToken);

            var cache = await _context.UserPreferenceCaches
                .FirstOrDefaultAsync(p => p.UserId == userId, cancellationToken);

            if (cache == null)
            {
                cache = new UserPreferenceCache
                {
                    Id = Guid.NewGuid().ToString(),
                    UserId = userId
                };
                _context.UserPreferenceCaches.Add(cache);
            }

            cache.LanguageWeights = JsonSerializer.Serialize(prefData.LanguageWeights);
            cache.PrivateRepoLanguages = JsonSerializer.Serialize(prefData.PrivateRepoLanguages);
            cache.LastCalculatedAt = DateTime.UtcNow;
            cache.UpdateTimestamp();

            await _context.SaveChangesAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update user preference cache for user {UserId}", userId);
        }
    }

    /// <summary>
    /// 标记仓库为不感兴趣
    /// </summary>
    public async Task<bool> MarkAsDislikedAsync(
        DislikeRequest request,
        CancellationToken cancellationToken = default)
    {
        try
        {
            // 检查是否已标记
            var existing = await _context.UserDislikes
                .FirstOrDefaultAsync(d => d.UserId == request.UserId && d.RepositoryId == request.RepositoryId, cancellationToken);

            if (existing != null)
            {
                return true; // 已经标记过了
            }

            var dislike = new UserDislike
            {
                Id = Guid.NewGuid().ToString(),
                UserId = request.UserId,
                RepositoryId = request.RepositoryId,
                Reason = request.Reason
            };

            _context.UserDislikes.Add(dislike);
            await _context.SaveChangesAsync(cancellationToken);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to mark repository as disliked");
            return false;
        }
    }

    /// <summary>
    /// 取消不感兴趣标记
    /// </summary>
    public async Task<bool> RemoveDislikeAsync(
        string userId,
        string repositoryId,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var dislike = await _context.UserDislikes
                .FirstOrDefaultAsync(d => d.UserId == userId && d.RepositoryId == repositoryId, cancellationToken);

            if (dislike == null)
            {
                return true;
            }

            _context.UserDislikes.Remove(dislike);
            await _context.SaveChangesAsync(cancellationToken);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove dislike");
            return false;
        }
    }

    /// <summary>
    /// 获取可用的编程语言列表
    /// </summary>
    public async Task<AvailableLanguagesResponse> GetAvailableLanguagesAsync(
        CancellationToken cancellationToken = default)
    {
        var languages = await _context.Repositories
            .AsNoTracking()
            .Where(r => r.IsPublic && r.Status == RepositoryStatus.Completed && !r.IsDeleted && r.PrimaryLanguage != null)
            .GroupBy(r => r.PrimaryLanguage!)
            .Select(g => new LanguageInfo
            {
                Name = g.Key,
                Count = g.Count()
            })
            .OrderByDescending(l => l.Count)
            .ToListAsync(cancellationToken);

        return new AvailableLanguagesResponse { Languages = languages };
    }
}

/// <summary>
/// 用户偏好数据（内存中使用）
/// </summary>
public class UserPreferenceData
{
    public HashSet<string> ViewedRepoIds { get; set; } = new();
    public HashSet<string> BookmarkedRepoIds { get; set; } = new();
    public Dictionary<string, double> LanguageWeights { get; set; } = new();
    public Dictionary<string, double> PrivateRepoLanguages { get; set; } = new();
}
