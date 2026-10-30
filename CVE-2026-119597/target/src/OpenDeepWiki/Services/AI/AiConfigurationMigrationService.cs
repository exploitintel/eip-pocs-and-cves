using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.Agents;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Admin;
using System.Security.Cryptography;
using System.Text;

namespace OpenDeepWiki.Services.AI;

public interface IAiConfigurationMigrationService
{
    Task MigrateAsync(CancellationToken cancellationToken = default);
}

public class AiConfigurationMigrationService : IAiConfigurationMigrationService
{
    private static readonly string[] TaskScopedLegacyProviderPrefixes =
    [
        "legacy-wiki-catalog-",
        "legacy-wiki-content-",
        "legacy-wiki-translation-",
        "legacy-graphify-"
    ];

    private readonly IContext _context;
    private readonly IConfiguration _configuration;
    private readonly IAiProviderPresetSeeder _presetSeeder;
    private readonly ILogger<AiConfigurationMigrationService> _logger;

    public AiConfigurationMigrationService(
        IContext context,
        IConfiguration configuration,
        IAiProviderPresetSeeder presetSeeder,
        ILogger<AiConfigurationMigrationService> logger)
    {
        _context = context;
        _configuration = configuration;
        _presetSeeder = presetSeeder;
        _logger = logger;
    }

    public async Task MigrateAsync(CancellationToken cancellationToken = default)
    {
        await _presetSeeder.EnsureBuiltInProvidersAsync(cancellationToken);
        await ConsolidateTaskScopedLegacyProvidersAsync(cancellationToken);

        var aiFallback = new LegacyAiSettings(
            GetConfig("AI:Endpoint") ?? GetConfig("ENDPOINT"),
            GetConfig("AI:ApiKey") ?? GetConfig("CHAT_API_KEY"),
            GetConfig("AI:RequestType") ?? GetConfig("CHAT_REQUEST_TYPE"),
            null);

        var catalog = await MigrateBindingAsync(
            "wiki-catalog",
            "Wiki Catalog",
            GetConfig("WIKI_CATALOG_MODEL") ?? GetConfig("WikiGenerator:CatalogModel"),
            new LegacyAiSettings(
                GetConfig("WIKI_CATALOG_ENDPOINT") ?? GetConfig("WikiGenerator:CatalogEndpoint") ?? aiFallback.Endpoint,
                GetConfig("WIKI_CATALOG_API_KEY") ?? GetConfig("WikiGenerator:CatalogApiKey") ?? aiFallback.ApiKey,
                GetConfig("WIKI_CATALOG_REQUEST_TYPE") ?? GetConfig("WikiGenerator:CatalogRequestType") ?? aiFallback.RequestType,
                aiFallback),
            cancellationToken);

        var content = await MigrateBindingAsync(
            "wiki-content",
            "Wiki Content",
            GetConfig("WIKI_CONTENT_MODEL") ?? GetConfig("WikiGenerator:ContentModel"),
            new LegacyAiSettings(
                GetConfig("WIKI_CONTENT_ENDPOINT") ?? GetConfig("WikiGenerator:ContentEndpoint") ?? aiFallback.Endpoint,
                GetConfig("WIKI_CONTENT_API_KEY") ?? GetConfig("WikiGenerator:ContentApiKey") ?? aiFallback.ApiKey,
                GetConfig("WIKI_CONTENT_REQUEST_TYPE") ?? GetConfig("WikiGenerator:ContentRequestType") ?? aiFallback.RequestType,
                aiFallback),
            cancellationToken);

        var translation = await MigrateBindingAsync(
            "wiki-translation",
            "Wiki Translation",
            GetConfig("WIKI_TRANSLATION_MODEL") ?? GetConfig("WikiGenerator:TranslationModel") ?? content.ModelId,
            new LegacyAiSettings(
                GetConfig("WIKI_TRANSLATION_ENDPOINT") ?? GetConfig("WikiGenerator:TranslationEndpoint") ?? content.Endpoint,
                GetConfig("WIKI_TRANSLATION_API_KEY") ?? GetConfig("WikiGenerator:TranslationApiKey") ?? content.ApiKey,
                GetConfig("WIKI_TRANSLATION_REQUEST_TYPE") ?? GetConfig("WikiGenerator:TranslationRequestType") ?? content.RequestType,
                content),
            cancellationToken);

        await UpsertSettingAsync(SystemSettingDefaults.WikiCatalogProviderId, catalog.ProviderId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.WikiCatalogModelId, catalog.ModelId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.WikiContentProviderId, content.ProviderId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.WikiContentModelId, content.ModelId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.WikiTranslationProviderId, translation.ProviderId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.WikiTranslationModelId, translation.ModelId, cancellationToken);

        var graphify = await MigrateBindingAsync(
            "graphify",
            "Graphify",
            GetConfig("GRAPHIFY_MODEL") ?? GetConfig("Graphify:Model") ?? content.ModelId,
            new LegacyAiSettings(
                GetConfig("GRAPHIFY_OPENAI_BASE_URL") ?? GetConfig("OPENAI_BASE_URL") ?? GetConfig("Graphify:OpenAiBaseUrl") ?? content.Endpoint,
                GetConfig("GRAPHIFY_OPENAI_API_KEY") ?? GetConfig("OPENAI_API_KEY") ?? GetConfig("Graphify:OpenAiApiKey") ?? content.ApiKey,
                "OpenAI",
                content),
            cancellationToken);

        await UpsertSettingAsync(SystemSettingDefaults.GraphifyProviderId, graphify.ProviderId, cancellationToken);
        await UpsertSettingAsync(SystemSettingDefaults.GraphifyModelId, graphify.ModelId, cancellationToken);

        await MigrateModelConfigsAsync(cancellationToken);
        await MigrateChatAppsAsync(cancellationToken);
        await ConsolidateTaskScopedLegacyProvidersAsync(cancellationToken);
        await _context.SaveChangesAsync(cancellationToken);
    }

    private async Task<LegacyAiSettings> MigrateBindingAsync(
        string key,
        string displayName,
        string? modelId,
        LegacyAiSettings legacy,
        CancellationToken cancellationToken)
    {
        var effective = legacy.WithFallback();
        modelId = string.IsNullOrWhiteSpace(modelId) ? effective.ModelId : modelId;

        var provider = await GetOrCreateProviderAsync(displayName, effective, cancellationToken);
        if (string.IsNullOrWhiteSpace(modelId))
        {
            modelId = provider.DefaultModelId ?? "gpt-4o-mini";
        }

        var model = await GetOrCreateModelAsync(provider, modelId, displayName, cancellationToken);

        provider.DefaultModelId ??= model.ModelId;

        return effective with
        {
            ProviderId = provider.Id,
            ModelId = model.ModelId
        };
    }

    private async Task<AiProviderConfig> GetOrCreateProviderAsync(
        string displayName,
        LegacyAiSettings settings,
        CancellationToken cancellationToken)
    {
        var providerType = NormalizeProviderType(settings.RequestType);
        var endpoint = NormalizeEndpoint(settings.Endpoint, providerType);
        var name = BuildLegacyProviderName(providerType, endpoint, settings.ApiKey);

        var provider = FindLocalProviderByName(name) ??
                       await _context.AiProviderConfigs
                           .FirstOrDefaultAsync(p => p.Name == name, cancellationToken);

        if (provider != null)
        {
            RestoreProvider(provider);
            FillMissingOperationalFields(provider, settings.ApiKey);
            return provider;
        }

        provider = await FindMatchingBuiltInProviderAsync(providerType, endpoint, settings.ApiKey, cancellationToken);
        if (provider != null)
        {
            FillMissingOperationalFields(provider, settings.ApiKey);

            return provider;
        }

        provider = await FindMatchingCustomProviderAsync(providerType, endpoint, settings.ApiKey, cancellationToken);
        if (provider != null)
        {
            if (IsTaskScopedLegacyProvider(provider))
            {
                provider.Name = name;
                provider.DisplayName = BuildLegacyProviderDisplayName(providerType, endpoint);
                provider.Description ??= "Migrated from legacy AI settings and shared by all bindings with the same endpoint and credentials.";
                provider.UpdatedAt = DateTime.UtcNow;
            }

            FillMissingOperationalFields(provider, settings.ApiKey);
            return provider;
        }

        provider = new AiProviderConfig
        {
            Id = Guid.NewGuid().ToString(),
            Name = name,
            DisplayName = BuildLegacyProviderDisplayName(providerType, endpoint),
            ProviderType = providerType,
            BaseUrl = endpoint,
            ApiKey = settings.ApiKey,
            AuthType = "ApiKey",
            SupportsModelDiscovery = true,
            IsActive = true,
            Description = $"Migrated from {displayName} legacy settings and shared by bindings with the same endpoint and credentials.",
            CreatedAt = DateTime.UtcNow
        };

        _context.AiProviderConfigs.Add(provider);
        _logger.LogInformation("Migrated legacy AI provider {Name}", provider.Name);
        return provider;
    }

    private async Task<AiProviderConfig?> FindMatchingBuiltInProviderAsync(
        string providerType,
        string endpoint,
        string? apiKey,
        CancellationToken cancellationToken)
    {
        var candidates = await _context.AiProviderConfigs
            .Where(p => p.IsBuiltIn && p.ProviderType == providerType && !p.IsDeleted)
            .ToListAsync(cancellationToken);

        return candidates.FirstOrDefault(p =>
            NormalizeEndpoint(p.BaseUrl, p.ProviderType)
                .Equals(endpoint, StringComparison.OrdinalIgnoreCase) &&
            IsApiKeyCompatible(p.ApiKey, apiKey));
    }

    private async Task<AiProviderConfig?> FindMatchingCustomProviderAsync(
        string providerType,
        string endpoint,
        string? apiKey,
        CancellationToken cancellationToken)
    {
        var candidates = await _context.AiProviderConfigs
            .Where(p => !p.IsBuiltIn && p.ProviderType == providerType && !p.IsDeleted)
            .ToListAsync(cancellationToken);

        return candidates
            .OrderBy(p => IsTaskScopedLegacyProvider(p) ? 1 : 0)
            .ThenBy(p => p.CreatedAt)
            .FirstOrDefault(p =>
                NormalizeEndpoint(p.BaseUrl, p.ProviderType)
                    .Equals(endpoint, StringComparison.OrdinalIgnoreCase) &&
                IsApiKeyCompatible(p.ApiKey, apiKey));
    }

    private async Task<AiModelConfig> GetOrCreateModelAsync(
        AiProviderConfig provider,
        string modelId,
        string displayName,
        CancellationToken cancellationToken)
    {
        var model = _context.AiModelConfigs
            .Local
            .FirstOrDefault(m =>
                m.ProviderId == provider.Id &&
                m.ModelId.Equals(modelId, StringComparison.OrdinalIgnoreCase) &&
                !m.IsDeleted);

        if (model != null)
        {
            return model;
        }

        model = await _context.AiModelConfigs
            .FirstOrDefaultAsync(m =>
                    m.ProviderId == provider.Id &&
                    m.ModelId == modelId &&
                    !m.IsDeleted,
                cancellationToken);

        if (model != null)
        {
            return model;
        }

        model = new AiModelConfig
        {
            Id = Guid.NewGuid().ToString(),
            ProviderId = provider.Id,
            ModelId = modelId,
            Name = modelId,
            DisplayName = modelId,
            ModelType = "chat",
            ProviderType = AiProviderResolver.NormalizeModelProviderType(null, modelId) ?? provider.ProviderType,
            SupportsTools = true,
            IsActive = true,
            Description = $"Migrated from {displayName} legacy settings",
            CreatedAt = DateTime.UtcNow
        };

        _context.AiModelConfigs.Add(model);
        return model;
    }

    private async Task MigrateModelConfigsAsync(CancellationToken cancellationToken)
    {
        var legacyModels = await _context.ModelConfigs
            .Where(m => !m.IsDeleted && string.IsNullOrEmpty(m.AiProviderId))
            .ToListAsync(cancellationToken);

        foreach (var modelConfig in legacyModels)
        {
            var migrated = await MigrateBindingAsync(
                $"model-{modelConfig.Id}",
                modelConfig.Name,
                modelConfig.ModelId,
                new LegacyAiSettings(
                    modelConfig.Endpoint,
                    modelConfig.ApiKey,
                    modelConfig.Provider,
                    null),
                cancellationToken);

            modelConfig.AiProviderId = migrated.ProviderId;
        }
    }

    private async Task MigrateChatAppsAsync(CancellationToken cancellationToken)
    {
        var chatApps = await _context.ChatApps
            .Where(a => !a.IsDeleted && string.IsNullOrEmpty(a.AiProviderId))
            .ToListAsync(cancellationToken);

        foreach (var app in chatApps)
        {
            var modelId = !string.IsNullOrWhiteSpace(app.DefaultModel)
                ? app.DefaultModel
                : "gpt-4o-mini";

            var migrated = await MigrateBindingAsync(
                $"chat-app-{app.Id}",
                app.Name,
                modelId,
                new LegacyAiSettings(app.BaseUrl, app.ApiKey, app.ProviderType, null),
                cancellationToken);

            app.AiProviderId = migrated.ProviderId;
            app.DefaultModel = migrated.ModelId;
        }
    }

    private async Task UpsertSettingAsync(
        string key,
        string? value,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrWhiteSpace(value))
        {
            return;
        }

        var setting = await _context.SystemSettings
            .FirstOrDefaultAsync(s => s.Key == key && !s.IsDeleted, cancellationToken);

        if (setting == null)
        {
            _context.SystemSettings.Add(new SystemSetting
            {
                Id = Guid.NewGuid().ToString(),
                Key = key,
                Value = value,
                Category = "ai",
                CreatedAt = DateTime.UtcNow
            });
            return;
        }

        if (string.IsNullOrWhiteSpace(setting.Value))
        {
            setting.Value = value;
            setting.UpdatedAt = DateTime.UtcNow;
            return;
        }

        if (IsProviderBindingSettingKey(key) &&
            !string.Equals(setting.Value, value, StringComparison.Ordinal) &&
            await ShouldRepairProviderBindingAsync(setting.Value, cancellationToken))
        {
            setting.Value = value;
            setting.UpdatedAt = DateTime.UtcNow;
        }
        else if (IsModelBindingSettingKey(key) &&
                 !string.Equals(setting.Value, value, StringComparison.Ordinal) &&
                 await ShouldRepairModelBindingAsync(key, setting.Value, cancellationToken))
        {
            setting.Value = value;
            setting.UpdatedAt = DateTime.UtcNow;
        }
    }

    private async Task ConsolidateTaskScopedLegacyProvidersAsync(CancellationToken cancellationToken)
    {
        var providers = await _context.AiProviderConfigs
            .Where(p => !p.IsDeleted)
            .OrderBy(p => p.CreatedAt)
            .ToListAsync(cancellationToken);

        foreach (var source in providers.Where(IsTaskScopedLegacyProvider).ToList())
        {
            var providerType = NormalizeProviderType(source.ProviderType);
            var endpoint = NormalizeEndpoint(source.BaseUrl, providerType);
            var target = await GetOrCreateProviderAsync(
                source.DisplayName ?? source.Name,
                new LegacyAiSettings(endpoint, source.ApiKey, providerType, null),
                cancellationToken);

            if (target.Id == source.Id)
            {
                continue;
            }

            await MoveProviderBindingsAsync(source, target, cancellationToken);
        }
    }

    private async Task MoveProviderBindingsAsync(
        AiProviderConfig source,
        AiProviderConfig target,
        CancellationToken cancellationToken)
    {
        FillMissingOperationalFields(target, source.ApiKey);
        target.DefaultModelId ??= source.DefaultModelId;
        target.UpdatedAt = DateTime.UtcNow;

        var settings = await _context.SystemSettings
            .Where(s => !s.IsDeleted && s.Value == source.Id)
            .ToListAsync(cancellationToken);
        foreach (var setting in settings)
        {
            setting.Value = target.Id;
            setting.UpdatedAt = DateTime.UtcNow;
        }

        var modelConfigs = await _context.ModelConfigs
            .Where(m => !m.IsDeleted && m.AiProviderId == source.Id)
            .ToListAsync(cancellationToken);
        foreach (var modelConfig in modelConfigs)
        {
            modelConfig.AiProviderId = target.Id;
            modelConfig.UpdatedAt = DateTime.UtcNow;
        }

        var chatApps = await _context.ChatApps
            .Where(a => !a.IsDeleted && a.AiProviderId == source.Id)
            .ToListAsync(cancellationToken);
        foreach (var app in chatApps)
        {
            app.AiProviderId = target.Id;
            app.UpdatedAt = DateTime.UtcNow;
        }

        var targetModels = await _context.AiModelConfigs
            .Where(m => m.ProviderId == target.Id && !m.IsDeleted)
            .ToListAsync(cancellationToken);
        var targetModelIds = targetModels
            .Select(m => m.ModelId)
            .ToHashSet(StringComparer.OrdinalIgnoreCase);

        var sourceModels = await _context.AiModelConfigs
            .Where(m => m.ProviderId == source.Id && !m.IsDeleted)
            .ToListAsync(cancellationToken);
        foreach (var model in sourceModels)
        {
            if (targetModelIds.Contains(model.ModelId))
            {
                model.IsDeleted = true;
                model.DeletedAt = DateTime.UtcNow;
                model.UpdatedAt = DateTime.UtcNow;
                continue;
            }

            model.ProviderId = target.Id;
            model.UpdatedAt = DateTime.UtcNow;
            targetModelIds.Add(model.ModelId);
        }

        source.IsDeleted = true;
        source.DeletedAt = DateTime.UtcNow;
        source.UpdatedAt = DateTime.UtcNow;
        _logger.LogInformation(
            "Consolidated task-scoped legacy AI provider {SourceName} into {TargetName}",
            source.Name,
            target.Name);
    }

    private async Task<bool> ShouldRepairProviderBindingAsync(
        string? providerId,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrWhiteSpace(providerId))
        {
            return true;
        }

        var provider = await _context.AiProviderConfigs
            .FirstOrDefaultAsync(p => p.Id == providerId, cancellationToken);

        return provider == null || provider.IsDeleted || IsTaskScopedLegacyProvider(provider);
    }

    private async Task<bool> ShouldRepairModelBindingAsync(
        string modelSettingKey,
        string? modelId,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrWhiteSpace(modelId))
        {
            return true;
        }

        var providerSettingKey = modelSettingKey switch
        {
            SystemSettingDefaults.WikiCatalogModelId => SystemSettingDefaults.WikiCatalogProviderId,
            SystemSettingDefaults.WikiContentModelId => SystemSettingDefaults.WikiContentProviderId,
            SystemSettingDefaults.WikiTranslationModelId => SystemSettingDefaults.WikiTranslationProviderId,
            SystemSettingDefaults.GraphifyModelId => SystemSettingDefaults.GraphifyProviderId,
            _ => null
        };

        if (providerSettingKey == null)
        {
            return false;
        }

        var providerSetting = await _context.SystemSettings
            .FirstOrDefaultAsync(s => s.Key == providerSettingKey && !s.IsDeleted, cancellationToken);
        if (providerSetting == null || string.IsNullOrWhiteSpace(providerSetting.Value))
        {
            return true;
        }

        var provider = await _context.AiProviderConfigs
            .FirstOrDefaultAsync(p => p.Id == providerSetting.Value, cancellationToken);
        if (provider == null || provider.IsDeleted)
        {
            return true;
        }

        var model = await _context.AiModelConfigs
            .FirstOrDefaultAsync(m =>
                m.ProviderId == provider.Id &&
                m.ModelId == modelId &&
                m.IsActive &&
                !m.IsDeleted,
                cancellationToken);

        return model == null;
    }

    private AiProviderConfig? FindLocalProviderByName(string name)
    {
        return _context.AiProviderConfigs.Local.FirstOrDefault(p =>
            p.Name.Equals(name, StringComparison.OrdinalIgnoreCase));
    }

    private string? GetConfig(string key)
    {
        var value = _configuration[key];
        return string.IsNullOrWhiteSpace(value) ? null : value;
    }

    private static string NormalizeProviderType(string? requestType)
    {
        return AiProviderResolver.ParseRequestType(requestType).ToString();
    }

    private static string NormalizeEndpoint(string? endpoint, string providerType)
    {
        if (!string.IsNullOrWhiteSpace(endpoint))
        {
            return endpoint.TrimEnd('/');
        }

        return providerType.Equals(AiRequestType.Anthropic.ToString(), StringComparison.OrdinalIgnoreCase)
            ? "https://api.anthropic.com"
            : "https://api.openai.com/v1";
    }

    private static string BuildLegacyProviderName(
        string providerType,
        string endpoint,
        string? apiKey)
    {
        var providerSlug = StableSlug(providerType);
        var endpointSlug = StableSlug(endpoint);
        var endpointHash = ShortHash(endpoint);
        var apiKeyHash = string.IsNullOrWhiteSpace(apiKey) ? null : ShortHash(apiKey);
        var suffix = apiKeyHash == null ? endpointHash : $"{endpointHash}-{apiKeyHash}";
        var prefix = $"legacy-{providerSlug}-";
        var maxEndpointSlugLength = Math.Max(12, 100 - prefix.Length - suffix.Length - 1);
        if (endpointSlug.Length > maxEndpointSlugLength)
        {
            endpointSlug = endpointSlug[..maxEndpointSlugLength].Trim('-');
        }

        return $"{prefix}{endpointSlug}-{suffix}";
    }

    private static string BuildLegacyProviderDisplayName(string providerType, string endpoint)
    {
        var host = TryGetHost(endpoint);
        return string.IsNullOrWhiteSpace(host)
            ? $"Migrated {providerType} Provider"
            : $"Migrated {providerType} ({host})";
    }

    private static string? TryGetHost(string endpoint)
    {
        return Uri.TryCreate(endpoint, UriKind.Absolute, out var uri)
            ? uri.Host
            : null;
    }

    private static bool IsApiKeyCompatible(string? existingApiKey, string? incomingApiKey)
    {
        return string.IsNullOrWhiteSpace(existingApiKey) ||
               string.IsNullOrWhiteSpace(incomingApiKey) ||
               string.Equals(existingApiKey, incomingApiKey, StringComparison.Ordinal);
    }

    private static void FillMissingOperationalFields(AiProviderConfig provider, string? apiKey)
    {
        if (!string.IsNullOrWhiteSpace(apiKey) && string.IsNullOrWhiteSpace(provider.ApiKey))
        {
            provider.ApiKey = apiKey;
            provider.UpdatedAt = DateTime.UtcNow;
        }
    }

    private static void RestoreProvider(AiProviderConfig provider)
    {
        if (!provider.IsDeleted)
        {
            return;
        }

        provider.IsDeleted = false;
        provider.DeletedAt = null;
        provider.IsActive = true;
        provider.UpdatedAt = DateTime.UtcNow;
    }

    private static bool IsProviderBindingSettingKey(string key)
    {
        return key is SystemSettingDefaults.WikiCatalogProviderId
            or SystemSettingDefaults.WikiContentProviderId
            or SystemSettingDefaults.WikiTranslationProviderId
            or SystemSettingDefaults.GraphifyProviderId;
    }

    private static bool IsModelBindingSettingKey(string key)
    {
        return key is SystemSettingDefaults.WikiCatalogModelId
            or SystemSettingDefaults.WikiContentModelId
            or SystemSettingDefaults.WikiTranslationModelId
            or SystemSettingDefaults.GraphifyModelId;
    }

    private static bool IsTaskScopedLegacyProvider(AiProviderConfig provider)
    {
        return TaskScopedLegacyProviderPrefixes.Any(prefix =>
            provider.Name.StartsWith(prefix, StringComparison.OrdinalIgnoreCase));
    }

    private static string ShortHash(string value)
    {
        var bytes = SHA256.HashData(Encoding.UTF8.GetBytes(value));
        return Convert.ToHexString(bytes)[..10].ToLowerInvariant();
    }

    private static string StableSlug(string value)
    {
        var chars = value
            .ToLowerInvariant()
            .Select(ch => char.IsLetterOrDigit(ch) ? ch : '-')
            .ToArray();

        return string.Join('-', new string(chars)
            .Split('-', StringSplitOptions.RemoveEmptyEntries))
            .Trim('-');
    }

    private sealed record LegacyAiSettings(
        string? Endpoint,
        string? ApiKey,
        string? RequestType,
        LegacyAiSettings? Fallback)
    {
        public string? ProviderId { get; init; }
        public string? ModelId { get; init; }

        public LegacyAiSettings WithFallback()
        {
            if (Fallback == null)
            {
                return this;
            }

            return this with
            {
                Endpoint = Endpoint ?? Fallback.Endpoint,
                ApiKey = ApiKey ?? Fallback.ApiKey,
                RequestType = RequestType ?? Fallback.RequestType
            };
        }
    }
}
