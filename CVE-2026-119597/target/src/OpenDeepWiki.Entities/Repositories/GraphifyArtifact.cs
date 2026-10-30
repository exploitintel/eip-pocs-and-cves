using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// Graphify artifact generation status for a repository branch.
/// </summary>
public class GraphifyArtifact : AggregateRoot<string>
{
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    [Required]
    [StringLength(36)]
    public string RepositoryBranchId { get; set; } = string.Empty;

    public GraphifyArtifactStatus Status { get; set; } = GraphifyArtifactStatus.Pending;

    [StringLength(80)]
    public string? CommitId { get; set; }

    [StringLength(500)]
    public string? OutputRoot { get; set; }

    [StringLength(500)]
    public string? EntryFilePath { get; set; }

    [StringLength(500)]
    public string? GraphJsonPath { get; set; }

    [StringLength(500)]
    public string? ReportPath { get; set; }

    public string? ErrorMessage { get; set; }

    public DateTime? StartedAt { get; set; }

    public DateTime? CompletedAt { get; set; }

    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    [ForeignKey("RepositoryBranchId")]
    public virtual RepositoryBranch? RepositoryBranch { get; set; }
}

public enum GraphifyArtifactStatus
{
    Pending = 0,
    Processing = 1,
    Completed = 2,
    Failed = 3
}
