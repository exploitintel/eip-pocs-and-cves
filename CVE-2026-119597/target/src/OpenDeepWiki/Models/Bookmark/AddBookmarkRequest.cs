using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Models.Bookmark;

/// <summary>
/// 添加收藏请求
/// </summary>
public class AddBookmarkRequest
{
    /// <summary>
    /// 用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;
}
