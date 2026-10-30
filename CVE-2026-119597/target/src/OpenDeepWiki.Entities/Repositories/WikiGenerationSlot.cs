using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

public enum WikiGenerationWorkType
{
    Repository = 0,
    BranchTask = 1,
    IncrementalTask = 2
}

/// <summary>
/// Cluster-wide wiki generation slot. Only slots with
/// <see cref="SlotIndex"/> below the configured max concurrent count may be claimed.
/// </summary>
public class WikiGenerationSlot : AggregateRoot<string>
{
    public int SlotIndex { get; set; }

    public WikiGenerationWorkType? WorkType { get; set; }

    [StringLength(36)]
    public string? RepositoryId { get; set; }

    [StringLength(36)]
    public string? OwnerId { get; set; }

    [StringLength(128)]
    public string? InstanceId { get; set; }

    public DateTime? AcquiredAt { get; set; }

    public DateTime? HeartbeatAt { get; set; }

    [NotMapped]
    public bool IsHeld => !string.IsNullOrWhiteSpace(InstanceId);
}
