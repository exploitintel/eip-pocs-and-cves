using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.Agents;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.AI;

public sealed record ResolvedAiModel(
    string ProviderId,
    string ProviderName,
    string ProviderType,
    string ModelId,
    string ModelName,
    string? BaseUrl,
    string? ApiKey,
    AiRequestType RequestType,
    int? ContextWindow,
    int? MaxOutputTokens,
    decimal? InputTokenPrice,
    decimal? OutputTokenPrice,
    decimal? CacheHitTokenPrice,
    decimal? CacheCreationTokenPrice,
    bool SupportsThinking,
    string? ProviderRequestOverridesJson,
    string? ModelThinkingConfigJson,
    string? ModelRequestOverridesJson)
{
    public AiRequestOptions ToRequestOptions() => new()
    {
        Endpoint = BaseUrl,
        ApiKey = ApiKey,
        RequestType = RequestType,
        SupportsThinking = SupportsThinking,
        ThinkingConfigJson = ModelThinkingConfigJson,
        ProviderRequestOverridesJson = ProviderRequestOverridesJson,
        ModelRequestOverridesJson = ModelRequestOverridesJson
    };
}

public interface IAiProviderResolver
{
    Task<ResolvedAiModel> ResolveAsync(
        string? providerId,
        string? modelId,
        CancellationToken cancellationToken = default);

    Task<ResolvedAiModel> ResolveModelConfigAsync(
        ModelConfig modelConfig,
        CancellationToken cancellationToken = default);
}

public class AiProviderResolver : IAiProviderResolver
{
    private readonly IContextFactory _contextFactory;

    public AiProviderResolver(IContextFactory contextFactory)
    {
        _contextFactory = contextFactory ?? throw new ArgumentNullException(nameof(contextFactory));
    }

    public async Task<ResolvedAiModel> ResolveModelConfigAsync(
        ModelConfig modelConfig,
        CancellationToken cancellationToken = default)
    {
        ArgumentNullException.ThrowIfNull(modelConfig);
        return await ResolveAsync(modelConfig.AiProviderId, modelConfig.ModelId, cancellationToken);
    }

    public async Task<ResolvedAiModel> ResolveAsync(
        string? providerId,
        string? modelId,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(providerId))
        {
            throw new InvalidOperationException(
                "AI provider is not configured. Bind the task to a provider and model in system settings.");
        }

        if (string.IsNullOrWhiteSpace(modelId))
        {
            throw new InvalidOperationException(
                "AI model is not configured. Bind the task to a provider and model in system settings.");
        }

        using var context = _contextFactory.CreateContext();

        var provider = await context.AiProviderConfigs
            .AsNoTracking()
            .FirstOrDefaultAsync(p => p.Id == providerId && p.IsActive && !p.IsDeleted, cancellationToken);

        if (provider == null)
        {
            throw new InvalidOperationException($"AI provider '{providerId}' is not available.");
        }

        var model = await context.AiModelConfigs
            .AsNoTracking()
            .FirstOrDefaultAsync(m =>
                    m.ProviderId == provider.Id &&
                    m.ModelId == modelId &&
                    m.IsActive &&
                    !m.IsDeleted,
                cancellationToken);

        if (model == null)
        {
            throw new InvalidOperationException(
                $"AI model '{modelId}' is not available for provider '{provider.Name}'.");
        }

        var effectiveProviderType = ResolveEffectiveProviderType(
            provider.ProviderType,
            model.ProviderType);

        return new ResolvedAiModel(
            provider.Id,
            provider.DisplayName ?? provider.Name,
            effectiveProviderType,
            model.ModelId,
            model.DisplayName ?? model.Name,
            NormalizeBaseUrl(provider, effectiveProviderType),
            provider.ApiKey,
            ParseRequestType(effectiveProviderType),
            model.ContextWindow,
            model.MaxOutputTokens,
            model.InputTokenPrice,
            model.OutputTokenPrice,
            model.CacheHitTokenPrice,
            model.CacheCreationTokenPrice,
            model.SupportsThinking,
            provider.RequestOverridesJson,
            model.ThinkingConfigJson,
            model.RequestOverridesJson);
    }

    public static string NormalizeProviderType(string? providerType)
    {
        if (string.IsNullOrWhiteSpace(providerType))
        {
            return "OpenAI";
        }

        return providerType.Trim().ToLowerInvariant() switch
        {
            "azure" or "azure-openai" or "azureopenai" => "AzureOpenAI",
            "openai-responses" or "openairesponses" or "responses" => "OpenAIResponses",
            "anthropic" or "claude" => "Anthropic",
            "deepseek" or "deepseek-openai" or "deepseek-openai-chat" or "deepseek-chat" => "DeepSeekOpenAI",
            "openai-chat" or "openai" => "OpenAI",
            _ => providerType.Trim()
        };
    }

    public static string? NormalizeOptionalProviderType(string? providerType)
    {
        return string.IsNullOrWhiteSpace(providerType) ? null : NormalizeProviderType(providerType);
    }

    public static string? NormalizeModelProviderType(string? providerType, string modelId)
    {
        return NormalizeOptionalProviderType(providerType)
               ?? (IsOpenAIResponsesModelId(modelId) ? "OpenAIResponses" : null);
    }

    public static string ResolveEffectiveProviderType(
        string? providerType,
        string? modelProviderType) =>
        string.IsNullOrWhiteSpace(modelProviderType)
            ? NormalizeProviderType(providerType)
            : NormalizeProviderType(modelProviderType);

    public static bool IsOpenAIResponsesModelId(string? modelId)
    {
        if (string.IsNullOrWhiteSpace(modelId))
        {
            return false;
        }

        var normalized = modelId.Trim().ToLowerInvariant();
        if (!normalized.StartsWith("gpt-", StringComparison.Ordinal))
        {
            return false;
        }

        var versionStart = "gpt-".Length;
        var versionEnd = versionStart;
        while (versionEnd < normalized.Length && char.IsDigit(normalized[versionEnd]))
        {
            versionEnd++;
        }

        return versionEnd > versionStart &&
               int.TryParse(normalized[versionStart..versionEnd], out var majorVersion) &&
               majorVersion >= 5;
    }

    public static AiRequestType ParseRequestType(string? providerType)
    {
        return NormalizeProviderType(providerType) switch
        {
            "AzureOpenAI" => AiRequestType.AzureOpenAI,
            "OpenAIResponses" => AiRequestType.OpenAIResponses,
            "Anthropic" => AiRequestType.Anthropic,
            "DeepSeekOpenAI" => AiRequestType.DeepSeekOpenAI,
            _ => AiRequestType.OpenAI
        };
    }

    private static string? NormalizeBaseUrl(AiProviderConfig provider, string effectiveProviderType)
    {
        if (string.IsNullOrWhiteSpace(provider.BaseUrl))
        {
            return NormalizeProviderType(effectiveProviderType) switch
            {
                "Anthropic" => "https://api.anthropic.com",
                "DeepSeekOpenAI" => "https://api.deepseek.com/v1",
                _ => "https://api.openai.com/v1"
            };
        }

        var baseUrl = provider.BaseUrl.TrimEnd('/');
        return NormalizeProviderType(effectiveProviderType).Equals("Anthropic", StringComparison.OrdinalIgnoreCase) &&
               baseUrl.EndsWith("/v1", StringComparison.OrdinalIgnoreCase)
            ? baseUrl[..^3]
            : baseUrl;
    }
}
