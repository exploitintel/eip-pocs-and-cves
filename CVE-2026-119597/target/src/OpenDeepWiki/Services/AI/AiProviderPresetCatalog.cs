using System.Reflection;
using System.Text.Json;
using System.Text.Json.Serialization;

namespace OpenDeepWiki.Services.AI;

public interface IAiProviderPresetCatalog
{
    IReadOnlyList<AiProviderPreset> Presets { get; }
}

public sealed class AiProviderPresetCatalog : IAiProviderPresetCatalog
{
    private const string ResourceSuffix = "Services.AI.BuiltinProviderPresets.json";
    private static readonly JsonSerializerOptions JsonOptions = new(JsonSerializerDefaults.Web);
    private readonly Lazy<IReadOnlyList<AiProviderPreset>> _presets = new(LoadPresets);

    public IReadOnlyList<AiProviderPreset> Presets => _presets.Value;

    private static IReadOnlyList<AiProviderPreset> LoadPresets()
    {
        var assembly = typeof(AiProviderPresetCatalog).Assembly;
        var resourceName = assembly
            .GetManifestResourceNames()
            .FirstOrDefault(name => name.EndsWith(ResourceSuffix, StringComparison.Ordinal));

        if (resourceName == null)
        {
            throw new InvalidOperationException(
                $"Embedded AI provider preset resource '{ResourceSuffix}' was not found.");
        }

        using var stream = assembly.GetManifestResourceStream(resourceName)
            ?? throw new InvalidOperationException(
                $"Embedded AI provider preset resource '{resourceName}' could not be opened.");

        var presets = JsonSerializer.Deserialize<List<AiProviderPreset>>(stream, JsonOptions);
        return presets?.Where(p => !string.IsNullOrWhiteSpace(p.BuiltinId)).ToList()
               ?? [];
    }
}

public sealed class AiProviderPreset
{
    [JsonPropertyName("builtinId")]
    public string BuiltinId { get; init; } = string.Empty;

    [JsonPropertyName("name")]
    public string Name { get; init; } = string.Empty;

    [JsonPropertyName("type")]
    public string Type { get; init; } = "openai-chat";

    [JsonPropertyName("defaultBaseUrl")]
    public string DefaultBaseUrl { get; init; } = string.Empty;

    [JsonPropertyName("defaultModels")]
    public List<AiModelPreset> DefaultModels { get; init; } = [];

    [JsonPropertyName("deprecatedModelIds")]
    public List<string>? DeprecatedModelIds { get; init; }

    [JsonPropertyName("defaultEnabled")]
    public bool? DefaultEnabled { get; init; }

    [JsonPropertyName("requiresApiKey")]
    public bool? RequiresApiKey { get; init; }

    [JsonPropertyName("homepage")]
    public string? Homepage { get; init; }

    [JsonPropertyName("apiKeyUrl")]
    public string? ApiKeyUrl { get; init; }

    [JsonPropertyName("useSystemProxy")]
    public bool? UseSystemProxy { get; init; }

    [JsonPropertyName("userAgent")]
    public string? UserAgent { get; init; }

    [JsonPropertyName("defaultModel")]
    public string? DefaultModel { get; init; }

    [JsonPropertyName("authMode")]
    public string? AuthMode { get; init; }

    [JsonPropertyName("oauthConfig")]
    public JsonElement? OAuthConfig { get; init; }

    [JsonPropertyName("channelConfig")]
    public JsonElement? ChannelConfig { get; init; }

    [JsonPropertyName("requestOverrides")]
    public JsonElement? RequestOverrides { get; init; }

    [JsonPropertyName("instructionsPrompt")]
    public string? InstructionsPrompt { get; init; }

    [JsonPropertyName("ui")]
    public JsonElement? Ui { get; init; }

    [JsonPropertyName("websocketUrl")]
    public string? WebsocketUrl { get; init; }

    [JsonPropertyName("websocketMode")]
    public string? WebsocketMode { get; init; }

    [JsonExtensionData]
    public Dictionary<string, JsonElement>? ExtensionData { get; init; }
}

public sealed class AiModelPreset
{
    [JsonPropertyName("id")]
    public string Id { get; init; } = string.Empty;

    [JsonPropertyName("name")]
    public string Name { get; init; } = string.Empty;

    [JsonPropertyName("type")]
    public string? Type { get; init; }

    [JsonPropertyName("category")]
    public string? Category { get; init; }

    [JsonPropertyName("enabled")]
    public bool? Enabled { get; init; }

    [JsonPropertyName("contextLength")]
    public int? ContextLength { get; init; }

    [JsonPropertyName("maxOutputTokens")]
    public int? MaxOutputTokens { get; init; }

    [JsonPropertyName("inputPrice")]
    public decimal? InputPrice { get; init; }

    [JsonPropertyName("outputPrice")]
    public decimal? OutputPrice { get; init; }

    [JsonPropertyName("cacheHitPrice")]
    public decimal? CacheHitPrice { get; init; }

    [JsonPropertyName("cacheCreationPrice")]
    public decimal? CacheCreationPrice { get; init; }

    [JsonPropertyName("supportsThinking")]
    public bool? SupportsThinking { get; init; }

    [JsonPropertyName("supportsVision")]
    public bool? SupportsVision { get; init; }

    [JsonPropertyName("supportsFunctionCall")]
    public bool? SupportsFunctionCall { get; init; }

    [JsonPropertyName("supportsComputerUse")]
    public bool? SupportsComputerUse { get; init; }

    [JsonPropertyName("thinkingConfig")]
    public JsonElement? ThinkingConfig { get; init; }

    [JsonPropertyName("requestOverrides")]
    public JsonElement? RequestOverrides { get; init; }

    [JsonExtensionData]
    public Dictionary<string, JsonElement>? ExtensionData { get; init; }
}
