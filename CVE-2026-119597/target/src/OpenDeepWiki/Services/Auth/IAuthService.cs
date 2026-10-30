using OpenDeepWiki.Models.Auth;

namespace OpenDeepWiki.Services.Auth;

/// <summary>
/// 认证服务接口
/// </summary>
public interface IAuthService
{
    /// <summary>
    /// 用户登录
    /// </summary>
    Task<LoginResponse> LoginAsync(LoginRequest request);

    /// <summary>
    /// 用户注册
    /// </summary>
    Task<LoginResponse> RegisterAsync(RegisterRequest request);

    /// <summary>
    /// 获取用户信息
    /// </summary>
    Task<UserInfo?> GetUserInfoAsync(string userId);
}
