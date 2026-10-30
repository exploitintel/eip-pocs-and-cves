using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Recommendation;

namespace OpenDeepWiki.Services.Recommendation;

/// <summary>
/// 推荐API服务
/// </summary>
[MiniApi(Route = "/api/v1/recommendations")]
[Tags("推荐")]
public class RecommendationApiService(RecommendationService recommendationService)
{
    /// <summary>
    /// 获取推荐仓库列表
    /// </summary>
    [HttpGet]
    public async Task<RecommendationResponse> GetRecommendationsAsync(
        [FromQuery] string? userId,
        [FromQuery] string? strategy,
        [FromQuery] string? language,
        [FromQuery] int limit = 20,
        [FromQuery] int? windowDays = null,
        CancellationToken cancellationToken = default)
    {
        if (limit < 1) limit = 20;
        if (limit > 100) limit = 100;

        var request = new RecommendationRequest
        {
            UserId = userId,
            Limit = limit,
            Strategy = strategy ?? "default",
            LanguageFilter = language,
            TimeWindowDays = windowDays
        };

        return await recommendationService.GetRecommendationsAsync(request, cancellationToken);
    }

    /// <summary>
    /// 获取热门仓库
    /// </summary>
    [HttpGet("/popular")]
    public async Task<RecommendationResponse> GetPopularReposAsync(
        [FromQuery] string? language,
        [FromQuery] int limit = 20,
        [FromQuery] int? windowDays = null,
        CancellationToken cancellationToken = default)
    {
        if (limit < 1) limit = 20;
        if (limit > 100) limit = 100;

        var request = new RecommendationRequest
        {
            UserId = null,
            Limit = limit,
            Strategy = "popular",
            LanguageFilter = language,
            TimeWindowDays = windowDays
        };

        return await recommendationService.GetRecommendationsAsync(request, cancellationToken);
    }

    /// <summary>
    /// 获取可用的编程语言列表
    /// </summary>
    [HttpGet("/languages")]
    public async Task<AvailableLanguagesResponse> GetAvailableLanguagesAsync(
        CancellationToken cancellationToken = default)
    {
        return await recommendationService.GetAvailableLanguagesAsync(cancellationToken);
    }

    /// <summary>
    /// 记录用户活动
    /// </summary>
    [HttpPost("/activity")]
    public async Task<RecordActivityResponse> RecordActivityAsync(
        [FromBody] RecordActivityRequest request,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(request.UserId))
        {
            return new RecordActivityResponse
            {
                Success = false,
                ErrorMessage = "用户ID不能为空"
            };
        }

        if (string.IsNullOrWhiteSpace(request.ActivityType))
        {
            return new RecordActivityResponse
            {
                Success = false,
                ErrorMessage = "活动类型不能为空"
            };
        }

        var success = await recommendationService.RecordActivityAsync(request, cancellationToken);

        return new RecordActivityResponse
        {
            Success = success,
            ErrorMessage = success ? null : "记录活动失败"
        };
    }


    /// <summary>
    /// 标记仓库为不感兴趣
    /// </summary>
    [HttpPost("/dislike")]
    public async Task<DislikeResponse> MarkAsDislikedAsync(
        [FromBody] DislikeRequest request,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(request.UserId))
        {
            return new DislikeResponse
            {
                Success = false,
                ErrorMessage = "用户ID不能为空"
            };
        }

        if (string.IsNullOrWhiteSpace(request.RepositoryId))
        {
            return new DislikeResponse
            {
                Success = false,
                ErrorMessage = "仓库ID不能为空"
            };
        }

        var success = await recommendationService.MarkAsDislikedAsync(request, cancellationToken);

        return new DislikeResponse
        {
            Success = success,
            ErrorMessage = success ? null : "标记失败"
        };
    }

    /// <summary>
    /// 取消不感兴趣标记
    /// </summary>
    [HttpDelete("/dislike/{repositoryId}")]
    public async Task<IResult> RemoveDislikeAsync(
        string repositoryId,
        [FromQuery] string userId,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(userId))
        {
            return Results.BadRequest(new { error = "用户ID不能为空" });
        }

        var success = await recommendationService.RemoveDislikeAsync(userId, repositoryId, cancellationToken);

        return success
            ? Results.Ok(new { success = true })
            : Results.Json(new { success = false, error = "取消失败" }, statusCode: StatusCodes.Status500InternalServerError);
    }

    /// <summary>
    /// 刷新用户偏好缓存
    /// </summary>
    [HttpPost("/refresh-preference/{userId}")]
    public async Task<IResult> RefreshUserPreferenceAsync(
        string userId,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(userId))
        {
            return Results.BadRequest(new { error = "用户ID不能为空" });
        }

        await recommendationService.UpdateUserPreferenceCacheAsync(userId, cancellationToken);

        return Results.Ok(new { success = true, message = "用户偏好缓存已刷新" });
    }
}
