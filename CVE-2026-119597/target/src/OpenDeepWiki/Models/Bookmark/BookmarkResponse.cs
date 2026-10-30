namespace OpenDeepWiki.Models.Bookmark;

/// <summary>
/// 收藏操作响应
/// </summary>
public class BookmarkResponse
{
    /// <summary>
    /// 操作是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 错误消息（仅在失败时有值）
    /// </summary>
    public string? ErrorMessage { get; set; }

    /// <summary>
    /// 收藏记录ID（仅在成功时有值）
    /// </summary>
    public string? BookmarkId { get; set; }
}
