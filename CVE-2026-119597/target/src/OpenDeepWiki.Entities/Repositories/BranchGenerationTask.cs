using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

public enum BranchGenerationTaskStatus
{
    Pending = 0,
    Processing = 1,
    Completed = 2,
    Failed = 3,
    Cancelled = 4
}

public enum BranchGenerationTaskMode
{
    Full = 0
}

public class BranchGenerationTask : AggregateRoot<string>
{
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    [Required]
    [StringLength(36)]
    public string BranchId { get; set; } = string.Empty;

    public BranchGenerationTaskStatus Status { get; set; } = BranchGenerationTaskStatus.Pending;

    public BranchGenerationTaskMode Mode { get; set; } = BranchGenerationTaskMode.Full;

    public int Priority { get; set; }

    public bool IsManualTrigger { get; set; }

    public int RetryCount { get; set; }

    public string? ErrorMessage { get; set; }

    public DateTime? StartedAt { get; set; }

    public DateTime? CompletedAt { get; set; }

    [StringLength(36)]
    public string? RequestedBy { get; set; }

    [StringLength(40)]
    public string? TargetCommitId { get; set; }

    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    [ForeignKey("BranchId")]
    public virtual RepositoryBranch? Branch { get; set; }
}
