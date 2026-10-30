using System.Security.Claims;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.UserProfile;

namespace OpenDeepWiki.Services.UserProfile;

/// <summary>
/// 用户资料API服务
/// </summary>
[MiniApi(Route = "/api/user")]
[Tags("用户资料")]
[Authorize]
public class UserProfileApiService(IUserProfileService profileService, IHttpContextAccessor httpContextAccessor)
{
    private string? GetCurrentUserId()
    {
        return httpContextAccessor.HttpContext?.User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
    }

    /// <summary>
    /// 更新个人资料
    /// </summary>
    [HttpPut("/profile")]
    public async Task<IResult> UpdateProfileAsync([FromBody] UpdateProfileRequest request)
    {
        var userId = GetCurrentUserId();
        if (string.IsNullOrEmpty(userId))
        {
            return Results.Unauthorized();
        }

        try
        {
            var userInfo = await profileService.UpdateProfileAsync(userId, request);
            return Results.Ok(new { success = true, data = userInfo });
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { success = false, message = ex.Message });
        }
    }

    /// <summary>
    /// 修改密码
    /// </summary>
    [HttpPut("/password")]
    public async Task<IResult> ChangePasswordAsync([FromBody] ChangePasswordRequest request)
    {
        var userId = GetCurrentUserId();
        if (string.IsNullOrEmpty(userId))
        {
            return Results.Unauthorized();
        }

        try
        {
            await profileService.ChangePasswordAsync(userId, request);
            return Results.Ok(new { success = true, message = "密码修改成功" });
        }
        catch (UnauthorizedAccessException)
        {
            return Results.BadRequest(new { success = false, message = "当前密码错误" });
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { success = false, message = ex.Message });
        }
    }

    /// <summary>
    /// 获取用户设置
    /// </summary>
    [HttpGet("/settings")]
    public async Task<IResult> GetSettingsAsync()
    {
        var userId = GetCurrentUserId();
        if (string.IsNullOrEmpty(userId))
        {
            return Results.Unauthorized();
        }

        try
        {
            var settings = await profileService.GetSettingsAsync(userId);
            return Results.Ok(new { success = true, data = settings });
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { success = false, message = ex.Message });
        }
    }

    /// <summary>
    /// 更新用户设置
    /// </summary>
    [HttpPut("/settings")]
    public async Task<IResult> UpdateSettingsAsync([FromBody] UserSettingsDto request)
    {
        var userId = GetCurrentUserId();
        if (string.IsNullOrEmpty(userId))
        {
            return Results.Unauthorized();
        }

        try
        {
            var settings = await profileService.UpdateSettingsAsync(userId, request);
            return Results.Ok(new { success = true, data = settings });
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { success = false, message = ex.Message });
        }
    }
}
