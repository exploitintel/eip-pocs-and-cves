using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Models;

/// <summary>
/// 仓库指派请求
/// </summary>
public class RepositoryAssignRequest
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 部门ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string DepartmentId { get; set; } = string.Empty;

    /// <summary>
    /// 指派用户ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string AssigneeUserId { get; set; } = string.Empty;
}
