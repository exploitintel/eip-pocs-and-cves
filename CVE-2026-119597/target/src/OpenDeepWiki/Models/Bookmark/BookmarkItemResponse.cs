namespace OpenDeepWiki.Models.Bookmark;

/// <summary>
/// 收藏项响应
/// </summary>
public class BookmarkItemResponse
{
    /// <summary>
    /// 收藏记录ID
    /// </summary>
    public string BookmarkId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID
    /// </summary>
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称
    /// </summary>
    public string RepoName { get; set; } = string.Empty;

    /// <summary>
    /// 组织名称
    /// </summary>
    public string OrgName { get; set; } = string.Empty;

    /// <summary>
    /// 仓库描述
    /// </summary>
    public string? Description { get; set; }

    /// <summary>
    /// Star 数量
    /// </summary>
    public int StarCount { get; set; }

    /// <summary>
    /// Fork 数量
    /// </summary>
    public int ForkCount { get; set; }

    /// <summary>
    /// 收藏数量
    /// </summary>
    public int BookmarkCount { get; set; }

    /// <summary>
    /// 收藏时间
    /// </summary>
    public DateTime BookmarkedAt { get; set; }
}
