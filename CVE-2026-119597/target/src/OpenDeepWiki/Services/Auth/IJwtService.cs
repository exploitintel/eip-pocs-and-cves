using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Auth;

/// <summary>
/// JWT服务接口
/// </summary>
public interface IJwtService
{
    /// <summary>
    /// 生成JWT令牌
    /// </summary>
    string GenerateToken(User user, List<string> roles);

    /// <summary>
    /// 验证JWT令牌
    /// </summary>
    bool ValidateToken(string token, out string userId);
}
