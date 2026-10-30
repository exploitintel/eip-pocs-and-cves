using OpenDeepWiki.Models.Auth;
using OpenDeepWiki.Models.UserProfile;

namespace OpenDeepWiki.Services.UserProfile;

/// <summary>
/// 用户资料服务接口
/// </summary>
public interface IUserProfileService
{
    /// <summary>
    /// 更新用户资料
    /// </summary>
    Task<UserInfo> UpdateProfileAsync(string userId, UpdateProfileRequest request);

    /// <summary>
    /// 修改密码
    /// </summary>
    Task ChangePasswordAsync(string userId, ChangePasswordRequest request);

    /// <summary>
    /// 获取用户设置
    /// </summary>
    Task<UserSettingsDto> GetSettingsAsync(string userId);

    /// <summary>
    /// 更新用户设置
    /// </summary>
    Task<UserSettingsDto> UpdateSettingsAsync(string userId, UserSettingsDto settings);
}
