using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端统计服务接口
/// </summary>
public interface IAdminStatisticsService
{
    /// <summary>
    /// 获取仪表盘统计数据
    /// </summary>
    Task<DashboardStatisticsResponse> GetDashboardStatisticsAsync(int days);

    /// <summary>
    /// 获取 Token 消耗统计
    /// </summary>
    Task<TokenUsageStatisticsResponse> GetTokenUsageStatisticsAsync(int days);
}
