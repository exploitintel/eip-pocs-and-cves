namespace OpenDeepWiki.Models.Auth;

/// <summary>
/// OAuth回调请求
/// </summary>
public class OAuthCallbackRequest
{
    /// <summary>
    /// 授权码
    /// </summary>
    public string Code { get; set; } = string.Empty;

    /// <summary>
    /// 状态码（用于防止CSRF攻击）
    /// </summary>
    public string? State { get; set; }
}
