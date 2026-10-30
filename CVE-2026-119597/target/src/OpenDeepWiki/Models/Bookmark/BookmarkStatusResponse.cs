namespace OpenDeepWiki.Models.Bookmark;

/// <summary>
/// 收藏状态响应
/// </summary>
public class BookmarkStatusResponse
{
    /// <summary>
    /// 是否已收藏
    /// </summary>
    public bool IsBookmarked { get; set; }

    /// <summary>
    /// 收藏时间（仅在已收藏时有值）
    /// </summary>
    public DateTime? BookmarkedAt { get; set; }
}
