using OpenDeepWiki.Models.Auth;

namespace OpenDeepWiki.Services.OAuth;

/// <summary>
/// OAuth服务接口
/// </summary>
public interface IOAuthService
{
    /// <summary>
    /// 获取OAuth授权URL
    /// </summary>
    Task<string> GetAuthorizationUrlAsync(string providerName, string? state = null);

    /// <summary>
    /// 处理OAuth回调
    /// </summary>
    Task<LoginResponse> HandleCallbackAsync(string providerName, string code, string? state = null);
}
