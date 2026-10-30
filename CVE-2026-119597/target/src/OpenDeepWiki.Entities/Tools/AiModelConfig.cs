using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

public class AiModelConfig : AggregateRoot<string>
{
    [Required]
    [StringLength(100)]
    public string ProviderId { get; set; } = string.Empty;

    [Required]
    [StringLength(100)]
    public string ModelId { get; set; } = string.Empty;

    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    [StringLength(100)]
    public string? DisplayName { get; set; }

    [StringLength(50)]
    public string ModelType { get; set; } = "chat";

    [StringLength(50)]
    public string? ProviderType { get; set; }

    public int? ContextWindow { get; set; }

    public int? MaxOutputTokens { get; set; }

    public decimal? InputTokenPrice { get; set; }

    public decimal? OutputTokenPrice { get; set; }

    public decimal? CacheHitTokenPrice { get; set; }

    public decimal? CacheCreationTokenPrice { get; set; }

    public bool SupportsThinking { get; set; }

    public bool SupportsVision { get; set; }

    public bool SupportsTools { get; set; } = true;

    public bool SupportsJsonMode { get; set; }

    public bool IsDefault { get; set; }

    public bool IsActive { get; set; } = true;

    public string? CapabilitiesJson { get; set; }

    public string? ThinkingConfigJson { get; set; }

    public string? RequestOverridesJson { get; set; }

    public string? TagsJson { get; set; }

    [StringLength(1000)]
    public string? Description { get; set; }

    public int SortOrder { get; set; }
}
