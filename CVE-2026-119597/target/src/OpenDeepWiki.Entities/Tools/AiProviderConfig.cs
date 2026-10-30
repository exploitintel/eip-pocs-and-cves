using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

public class AiProviderConfig : AggregateRoot<string>
{
    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    [StringLength(100)]
    public string? DisplayName { get; set; }

    [Required]
    [StringLength(50)]
    public string ProviderType { get; set; } = "OpenAI";

    [Required]
    [StringLength(500)]
    public string BaseUrl { get; set; } = string.Empty;

    [StringLength(1000)]
    public string? ApiKey { get; set; }

    [StringLength(50)]
    public string AuthType { get; set; } = "ApiKey";

    public bool IsBuiltIn { get; set; }

    public bool IsActive { get; set; } = true;

    public bool SupportsModelDiscovery { get; set; } = true;

    [StringLength(500)]
    public string? ModelsEndpoint { get; set; }

    [StringLength(100)]
    public string? DefaultModelId { get; set; }

    [StringLength(500)]
    public string? SystemProxyUrl { get; set; }

    public string? OAuthConfigJson { get; set; }

    public string? ChannelConfigJson { get; set; }

    public string? AccountsJson { get; set; }

    public string? RequestOverridesJson { get; set; }

    [StringLength(500)]
    public string? IconUrl { get; set; }

    [StringLength(1000)]
    public string? Description { get; set; }

    public int SortOrder { get; set; }
}
