using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

public enum RepositoryGenerationLockOwnerType
{
    Repository = 0,
    BranchTask = 1,
    IncrementalTask = 2
}

public enum RepositoryGenerationLockScope
{
    Repository = 0,
    Branch = 1
}

public class RepositoryGenerationLock : AggregateRoot<string>
{
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    public RepositoryGenerationLockOwnerType OwnerType { get; set; }

    [Required]
    [StringLength(36)]
    public string OwnerId { get; set; } = string.Empty;

    public RepositoryGenerationLockScope Scope { get; set; }

    public DateTime AcquiredAt { get; set; } = DateTime.UtcNow;

    /// <summary>
    /// Instance currently executing against this lock. Null means a reservation
    /// that any healthy worker may bind to.
    /// </summary>
    [StringLength(128)]
    public string? InstanceId { get; set; }

    public DateTime? HeartbeatAt { get; set; }

    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }
}
