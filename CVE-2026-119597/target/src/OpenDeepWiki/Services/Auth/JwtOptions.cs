namespace OpenDeepWiki.Services.Auth;

/// <summary>
/// JWT配置选项
/// </summary>
public class JwtOptions
{
    /// <summary>
    /// 密钥
    /// </summary>
    public string SecretKey { get; set; } = string.Empty;

    /// <summary>
    /// 发行者
    /// </summary>
    public string Issuer { get; set; } = "OpenDeepWiki";

    /// <summary>
    /// 受众
    /// </summary>
    public string Audience { get; set; } = "OpenDeepWiki";

    /// <summary>
    /// 过期时间（分钟）
    /// </summary>
    public int ExpirationMinutes { get; set; } = 1440; // 默认24小时
}
