namespace OpenDeepWiki.Models;

/// <summary>
/// 更新仓库可见性响应
/// </summary>
public class UpdateVisibilityResponse
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    public string Id { get; set; } = string.Empty;

    /// <summary>
    /// 是否公开
    /// </summary>
    public bool IsPublic { get; set; }

    /// <summary>
    /// 操作是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 错误消息（仅在失败时有值）
    /// </summary>
    public string? ErrorMessage { get; set; }
}
