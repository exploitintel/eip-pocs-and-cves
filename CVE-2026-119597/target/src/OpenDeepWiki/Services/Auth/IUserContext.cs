using System.Security.Claims;

namespace OpenDeepWiki.Services.Auth;

/// <summary>
/// 用户上下文接口，用于获取当前登录用户信息
/// </summary>
public interface IUserContext
{
    /// <summary>
    /// 当前用户ID，未登录时为null
    /// </summary>
    string? UserId { get; }

    /// <summary>
    /// 当前用户名称
    /// </summary>
    string? UserName { get; }

    /// <summary>
    /// 当前用户邮箱
    /// </summary>
    string? Email { get; }

    /// <summary>
    /// 是否已认证
    /// </summary>
    bool IsAuthenticated { get; }

    /// <summary>
    /// 获取当前用户的所有Claims
    /// </summary>
    ClaimsPrincipal? User { get; }
}
