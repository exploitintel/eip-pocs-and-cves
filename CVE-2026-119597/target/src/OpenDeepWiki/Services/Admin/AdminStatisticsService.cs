using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端统计服务实现
/// </summary>
public class AdminStatisticsService : IAdminStatisticsService
{
    private readonly IContext _context;

    public AdminStatisticsService(IContext context)
    {
        _context = context;
    }

    public async Task<DashboardStatisticsResponse> GetDashboardStatisticsAsync(int days)
    {
        var startDate = DateTime.UtcNow.Date.AddDays(-days + 1);
        var response = new DashboardStatisticsResponse();

        // 获取仓库统计
        var repoStats = await _context.Repositories
            .Where(r => !r.IsDeleted && r.CreatedAt >= startDate)
            .GroupBy(r => r.CreatedAt.Date)
            .Select(g => new
            {
                Date = g.Key,
                SubmittedCount = g.Count(),
                ProcessedCount = g.Count(r => r.Status == Entities.RepositoryStatus.Completed)
            })
            .ToListAsync();

        // 获取用户统计
        var userStats = await _context.Users
            .Where(u => !u.IsDeleted && u.CreatedAt >= startDate)
            .GroupBy(u => u.CreatedAt.Date)
            .Select(g => new { Date = g.Key, Count = g.Count() })
            .ToListAsync();

        // 填充每日数据
        for (var date = startDate; date <= DateTime.UtcNow.Date; date = date.AddDays(1))
        {
            var repoStat = repoStats.FirstOrDefault(r => r.Date == date);
            response.RepositoryStats.Add(new DailyRepositoryStatistic
            {
                Date = date,
                SubmittedCount = repoStat?.SubmittedCount ?? 0,
                ProcessedCount = repoStat?.ProcessedCount ?? 0
            });

            var userStat = userStats.FirstOrDefault(u => u.Date == date);
            response.UserStats.Add(new DailyUserStatistic
            {
                Date = date,
                NewUserCount = userStat?.Count ?? 0
            });
        }

        return response;
    }

    public async Task<TokenUsageStatisticsResponse> GetTokenUsageStatisticsAsync(int days)
    {
        var startDate = DateTime.UtcNow.Date.AddDays(-days + 1);
        var response = new TokenUsageStatisticsResponse();

        var tokenStats = await _context.TokenUsages
            .Where(t => !t.IsDeleted && t.RecordedAt >= startDate)
            .GroupBy(t => t.RecordedAt.Date)
            .Select(g => new
            {
                Date = g.Key,
                InputTokens = g.Sum(t => (long)t.InputTokens),
                OutputTokens = g.Sum(t => (long)t.OutputTokens),
                CachedInputTokens = g.Sum(t => (long)t.CachedInputTokens),
                CacheCreationInputTokens = g.Sum(t => (long)t.CacheCreationInputTokens),
                InputCost = g.Sum(t => t.InputCost),
                OutputCost = g.Sum(t => t.OutputCost),
                TotalCost = g.Sum(t => t.TotalCost)
            })
            .ToListAsync();

        // 填充每日数据
        for (var date = startDate; date <= DateTime.UtcNow.Date; date = date.AddDays(1))
        {
            var stat = tokenStats.FirstOrDefault(s => s.Date == date);
            var inputTokens = stat?.InputTokens ?? 0;
            var outputTokens = stat?.OutputTokens ?? 0;
            var cachedInputTokens = stat?.CachedInputTokens ?? 0;
            var cacheCreationInputTokens = stat?.CacheCreationInputTokens ?? 0;
            var inputCost = stat?.InputCost ?? 0m;
            var outputCost = stat?.OutputCost ?? 0m;
            var totalCost = stat?.TotalCost ?? 0m;

            response.DailyUsages.Add(new DailyTokenUsage
            {
                Date = date,
                InputTokens = inputTokens,
                OutputTokens = outputTokens,
                CachedInputTokens = cachedInputTokens,
                CacheCreationInputTokens = cacheCreationInputTokens,
                TotalTokens = inputTokens + outputTokens,
                InputCacheHitRate = CalculateHitRate(cachedInputTokens, inputTokens),
                InputCost = inputCost,
                OutputCost = outputCost,
                TotalCost = totalCost
            });

            response.TotalInputTokens += inputTokens;
            response.TotalOutputTokens += outputTokens;
            response.TotalCachedInputTokens += cachedInputTokens;
            response.TotalCacheCreationInputTokens += cacheCreationInputTokens;
            response.TotalInputCost += inputCost;
            response.TotalOutputCost += outputCost;
            response.TotalCost += totalCost;
        }

        response.TotalTokens = response.TotalInputTokens + response.TotalOutputTokens;
        response.InputCacheHitRate = CalculateHitRate(response.TotalCachedInputTokens, response.TotalInputTokens);
        return response;
    }

    private static decimal CalculateHitRate(long cachedInputTokens, long inputTokens)
    {
        return inputTokens <= 0
            ? 0m
            : Math.Round((decimal)cachedInputTokens / inputTokens, 4);
    }
}
