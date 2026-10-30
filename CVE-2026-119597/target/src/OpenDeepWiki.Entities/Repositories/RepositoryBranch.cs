using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 仓库分支实体
/// </summary>
public class RepositoryBranch : AggregateRoot<string>
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 分支名称
    /// </summary>
    [Required]
    [StringLength(200)]
    public string BranchName { get; set; } = string.Empty;

    /// <summary>
    /// 最后处理的 commit ID
    /// </summary>
    [StringLength(40)]
    public string? LastCommitId { get; set; }

    /// <summary>
    /// 最后处理时间（UTC）
    /// </summary>
    public DateTime? LastProcessedAt { get; set; }

    /// <summary>
    /// Branch-scoped full generation status for UI and retry recovery.
    /// </summary>
    public BranchGenerationTaskStatus? GenerationStatus { get; set; }

    /// <summary>
    /// Latest branch generation task ID.
    /// </summary>
    [StringLength(36)]
    public string? LastGenerationTaskId { get; set; }

    /// <summary>
    /// Last branch generation failure message.
    /// </summary>
    public string? LastGenerationError { get; set; }

    public DateTime? LastGenerationStartedAt { get; set; }

    public DateTime? LastGenerationCompletedAt { get; set; }

    /// <summary>
    /// 仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }
}
