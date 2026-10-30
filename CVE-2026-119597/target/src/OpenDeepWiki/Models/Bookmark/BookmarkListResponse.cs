namespace OpenDeepWiki.Models.Bookmark;

/// <summary>
/// 收藏列表响应
/// </summary>
public class BookmarkListResponse
{
    /// <summary>
    /// 收藏项列表
    /// </summary>
    public List<BookmarkItemResponse> Items { get; set; } = [];

    /// <summary>
    /// 总数
    /// </summary>
    public int Total { get; set; }

    /// <summary>
    /// 当前页码
    /// </summary>
    public int Page { get; set; }

    /// <summary>
    /// 每页大小
    /// </summary>
    public int PageSize { get; set; }
}
