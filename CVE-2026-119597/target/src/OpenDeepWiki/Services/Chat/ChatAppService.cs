using System.Security.Cryptography;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.AI;

namespace OpenDeepWiki.Services.Chat;

/// <summary>
/// DTO for creating a chat app.
/// </summary>
public class CreateChatAppDto
{
    public string Name { get; set; } = string.Empty;
    public string? Description { get; set; }
    public string? IconUrl { get; set; }
    public bool EnableDomainValidation { get; set; }
    public List<string>? AllowedDomains { get; set; }
    public string? AiProviderId { get; set; }
    public string ProviderType { get; set; } = "OpenAI";
    public string? ApiKey { get; set; }
    public string? BaseUrl { get; set; }
    public List<string>? AvailableModels { get; set; }
    public string? DefaultModel { get; set; }
    public int? RateLimitPerMinute { get; set; }
}

/// <summary>
/// DTO for updating a chat app.
/// </summary>
public class UpdateChatAppDto
{
    public string? Name { get; set; }
    public string? Description { get; set; }
    public string? IconUrl { get; set; }
    public bool? EnableDomainValidation { get; set; }
    public List<string>? AllowedDomains { get; set; }
    public string? AiProviderId { get; set; }
    public string? ProviderType { get; set; }
    public string? ApiKey { get; set; }
    public string? BaseUrl { get; set; }
    public List<string>? AvailableModels { get; set; }
    public string? DefaultModel { get; set; }
    public int? RateLimitPerMinute { get; set; }
    public bool? IsActive { get; set; }
}


/// <summary>
/// DTO for chat app response.
/// </summary>
public class ChatAppDto
{
    public Guid Id { get; set; }
    public string UserId { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string? Description { get; set; }
    public string? IconUrl { get; set; }
    public string AppId { get; set; } = string.Empty;
    public string? AppSecret { get; set; }
    public bool EnableDomainValidation { get; set; }
    public List<string> AllowedDomains { get; set; } = new();
    public string? AiProviderId { get; set; }
    public string ProviderType { get; set; } = string.Empty;
    public string? ApiKey { get; set; }
    public string? BaseUrl { get; set; }
    public List<string> AvailableModels { get; set; } = new();
    public string? DefaultModel { get; set; }
    public int? RateLimitPerMinute { get; set; }
    public bool IsActive { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
}

/// <summary>
/// Interface for chat app service.
/// </summary>
public interface IChatAppService
{
    /// <summary>
    /// Creates a new chat app for the user.
    /// </summary>
    Task<ChatAppDto> CreateAppAsync(string userId, CreateChatAppDto dto, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets all apps for a user.
    /// </summary>
    Task<List<ChatAppDto>> GetUserAppsAsync(string userId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets an app by its database ID.
    /// </summary>
    Task<ChatAppDto?> GetAppByIdAsync(Guid id, string userId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets an app by its public AppId.
    /// </summary>
    Task<ChatAppDto?> GetAppByAppIdAsync(string appId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Updates an existing app.
    /// </summary>
    Task<ChatAppDto?> UpdateAppAsync(Guid id, string userId, UpdateChatAppDto dto, CancellationToken cancellationToken = default);

    /// <summary>
    /// Deletes an app.
    /// </summary>
    Task<bool> DeleteAppAsync(Guid id, string userId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Regenerates the app secret.
    /// </summary>
    Task<string?> RegenerateSecretAsync(Guid id, string userId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Generates a unique AppId.
    /// </summary>
    string GenerateAppId();

    /// <summary>
    /// Generates a secure AppSecret.
    /// </summary>
    string GenerateAppSecret();
}


/// <summary>
/// Chat app service implementation.
/// </summary>
public class ChatAppService : IChatAppService
{
    private readonly IContext _context;
    private readonly ILogger<ChatAppService> _logger;

    public ChatAppService(IContext context, ILogger<ChatAppService> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task<ChatAppDto> CreateAppAsync(string userId, CreateChatAppDto dto, CancellationToken cancellationToken = default)
    {
        var aiProviderId = dto.AiProviderId ?? await CreateProviderFromLegacyAppConfigAsync(
            dto.Name,
            dto.ProviderType,
            dto.BaseUrl,
            dto.ApiKey,
            dto.DefaultModel ?? dto.AvailableModels?.FirstOrDefault(),
            cancellationToken);

        var app = new ChatApp
        {
            Id = Guid.NewGuid(),
            UserId = userId,
            Name = dto.Name,
            Description = dto.Description,
            IconUrl = dto.IconUrl,
            AppId = GenerateAppId(),
            AppSecret = GenerateAppSecret(),
            EnableDomainValidation = dto.EnableDomainValidation,
            AllowedDomains = dto.AllowedDomains != null ? JsonSerializer.Serialize(dto.AllowedDomains) : null,
            AiProviderId = aiProviderId,
            ProviderType = dto.ProviderType,
            ApiKey = dto.ApiKey,
            BaseUrl = dto.BaseUrl,
            AvailableModels = dto.AvailableModels != null ? JsonSerializer.Serialize(dto.AvailableModels) : null,
            DefaultModel = dto.DefaultModel,
            RateLimitPerMinute = dto.RateLimitPerMinute,
            IsActive = true,
            CreatedAt = DateTime.UtcNow
        };

        _context.ChatApps.Add(app);
        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Created chat app {AppId} for user {UserId}", app.AppId, userId);

        return MapToDto(app, includeSecret: true);
    }

    /// <inheritdoc />
    public async Task<List<ChatAppDto>> GetUserAppsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var apps = await _context.ChatApps
            .Where(a => a.UserId == userId && !a.IsDeleted)
            .OrderByDescending(a => a.CreatedAt)
            .ToListAsync(cancellationToken);

        return apps.Select(a => MapToDto(a, includeSecret: false)).ToList();
    }

    /// <inheritdoc />
    public async Task<ChatAppDto?> GetAppByIdAsync(Guid id, string userId, CancellationToken cancellationToken = default)
    {
        var app = await _context.ChatApps
            .FirstOrDefaultAsync(a => a.Id == id && a.UserId == userId && !a.IsDeleted, cancellationToken);

        return app != null ? MapToDto(app, includeSecret: true) : null;
    }


    /// <inheritdoc />
    public async Task<ChatAppDto?> GetAppByAppIdAsync(string appId, CancellationToken cancellationToken = default)
    {
        var app = await _context.ChatApps
            .FirstOrDefaultAsync(a => a.AppId == appId && !a.IsDeleted, cancellationToken);

        return app != null ? MapToDto(app, includeSecret: false) : null;
    }

    /// <inheritdoc />
    public async Task<ChatAppDto?> UpdateAppAsync(Guid id, string userId, UpdateChatAppDto dto, CancellationToken cancellationToken = default)
    {
        var app = await _context.ChatApps
            .FirstOrDefaultAsync(a => a.Id == id && a.UserId == userId && !a.IsDeleted, cancellationToken);

        if (app == null)
        {
            return null;
        }

        if (dto.Name != null) app.Name = dto.Name;
        if (dto.Description != null) app.Description = dto.Description;
        if (dto.IconUrl != null) app.IconUrl = dto.IconUrl;
        if (dto.EnableDomainValidation.HasValue) app.EnableDomainValidation = dto.EnableDomainValidation.Value;
        if (dto.AllowedDomains != null) app.AllowedDomains = JsonSerializer.Serialize(dto.AllowedDomains);
        if (dto.AiProviderId != null) app.AiProviderId = dto.AiProviderId;
        else if (string.IsNullOrWhiteSpace(app.AiProviderId) &&
                 (dto.ApiKey != null || dto.BaseUrl != null || dto.ProviderType != null))
        {
            app.AiProviderId = await CreateProviderFromLegacyAppConfigAsync(
                app.Name,
                dto.ProviderType ?? app.ProviderType,
                dto.BaseUrl ?? app.BaseUrl,
                dto.ApiKey ?? app.ApiKey,
                dto.DefaultModel ?? app.DefaultModel,
                cancellationToken);
        }
        if (dto.ProviderType != null) app.ProviderType = dto.ProviderType;
        if (dto.ApiKey != null) app.ApiKey = dto.ApiKey;
        if (dto.BaseUrl != null) app.BaseUrl = dto.BaseUrl;
        if (dto.AvailableModels != null) app.AvailableModels = JsonSerializer.Serialize(dto.AvailableModels);
        if (dto.DefaultModel != null) app.DefaultModel = dto.DefaultModel;
        if (dto.RateLimitPerMinute.HasValue) app.RateLimitPerMinute = dto.RateLimitPerMinute;
        if (dto.IsActive.HasValue) app.IsActive = dto.IsActive.Value;

        app.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Updated chat app {AppId}", app.AppId);

        return MapToDto(app, includeSecret: true);
    }

    /// <inheritdoc />
    public async Task<bool> DeleteAppAsync(Guid id, string userId, CancellationToken cancellationToken = default)
    {
        var app = await _context.ChatApps
            .FirstOrDefaultAsync(a => a.Id == id && a.UserId == userId && !a.IsDeleted, cancellationToken);

        if (app == null)
        {
            return false;
        }

        app.IsDeleted = true;
        app.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Deleted chat app {AppId}", app.AppId);

        return true;
    }


    /// <inheritdoc />
    public async Task<string?> RegenerateSecretAsync(Guid id, string userId, CancellationToken cancellationToken = default)
    {
        var app = await _context.ChatApps
            .FirstOrDefaultAsync(a => a.Id == id && a.UserId == userId && !a.IsDeleted, cancellationToken);

        if (app == null)
        {
            return null;
        }

        app.AppSecret = GenerateAppSecret();
        app.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Regenerated secret for chat app {AppId}", app.AppId);

        return app.AppSecret;
    }

    /// <inheritdoc />
    public string GenerateAppId()
    {
        // Generate a unique AppId with prefix "app_" followed by 24 random hex characters
        var bytes = new byte[12];
        RandomNumberGenerator.Fill(bytes);
        return $"app_{Convert.ToHexString(bytes).ToLowerInvariant()}";
    }

    /// <inheritdoc />
    public string GenerateAppSecret()
    {
        // Generate a secure AppSecret with prefix "sk_" followed by 48 random hex characters
        var bytes = new byte[24];
        RandomNumberGenerator.Fill(bytes);
        return $"sk_{Convert.ToHexString(bytes).ToLowerInvariant()}";
    }

    private async Task<string?> CreateProviderFromLegacyAppConfigAsync(
        string appName,
        string? providerType,
        string? baseUrl,
        string? apiKey,
        string? modelId,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrWhiteSpace(apiKey) && string.IsNullOrWhiteSpace(baseUrl))
        {
            return null;
        }

        providerType = string.IsNullOrWhiteSpace(providerType) ? "OpenAI" : providerType;
        baseUrl = string.IsNullOrWhiteSpace(baseUrl)
            ? providerType.Equals("Anthropic", StringComparison.OrdinalIgnoreCase)
                ? "https://api.anthropic.com"
                : "https://api.openai.com/v1"
            : baseUrl.TrimEnd('/');

        var provider = new AiProviderConfig
        {
            Id = Guid.NewGuid().ToString(),
            Name = $"chat-app-{Guid.NewGuid():N}",
            DisplayName = $"{appName} Provider",
            ProviderType = providerType,
            BaseUrl = baseUrl,
            ApiKey = apiKey,
            AuthType = "ApiKey",
            IsActive = true,
            SupportsModelDiscovery = true,
            DefaultModelId = modelId,
            CreatedAt = DateTime.UtcNow
        };

        _context.AiProviderConfigs.Add(provider);

        if (!string.IsNullOrWhiteSpace(modelId))
        {
            _context.AiModelConfigs.Add(new AiModelConfig
            {
                Id = Guid.NewGuid().ToString(),
                ProviderId = provider.Id,
                ModelId = modelId,
                Name = modelId,
                DisplayName = modelId,
                ModelType = "chat",
                ProviderType = AiProviderResolver.NormalizeModelProviderType(null, modelId) ?? providerType,
                SupportsTools = true,
                IsActive = true,
                IsDefault = true,
                CreatedAt = DateTime.UtcNow
            });
        }

        await _context.SaveChangesAsync(cancellationToken);
        return provider.Id;
    }

    /// <summary>
    /// Maps a ChatApp entity to a DTO.
    /// </summary>
    private static ChatAppDto MapToDto(ChatApp app, bool includeSecret)
    {
        return new ChatAppDto
        {
            Id = app.Id,
            UserId = app.UserId,
            Name = app.Name,
            Description = app.Description,
            IconUrl = app.IconUrl,
            AppId = app.AppId,
            AppSecret = includeSecret ? app.AppSecret : null,
            EnableDomainValidation = app.EnableDomainValidation,
            AllowedDomains = ParseJsonArray(app.AllowedDomains),
            AiProviderId = app.AiProviderId,
            ProviderType = app.ProviderType,
            ApiKey = includeSecret ? app.ApiKey : MaskApiKey(app.ApiKey),
            BaseUrl = app.BaseUrl,
            AvailableModels = ParseJsonArray(app.AvailableModels),
            DefaultModel = app.DefaultModel,
            RateLimitPerMinute = app.RateLimitPerMinute,
            IsActive = app.IsActive,
            CreatedAt = app.CreatedAt,
            UpdatedAt = app.UpdatedAt
        };
    }

    /// <summary>
    /// Parses a JSON array string to a list of strings.
    /// </summary>
    private static List<string> ParseJsonArray(string? json)
    {
        if (string.IsNullOrEmpty(json))
        {
            return new List<string>();
        }

        try
        {
            return JsonSerializer.Deserialize<List<string>>(json) ?? new List<string>();
        }
        catch
        {
            return new List<string>();
        }
    }

    /// <summary>
    /// Masks an API key for display.
    /// </summary>
    private static string? MaskApiKey(string? apiKey)
    {
        if (string.IsNullOrEmpty(apiKey) || apiKey.Length < 8)
        {
            return apiKey;
        }

        return $"{apiKey[..4]}****{apiKey[^4..]}";
    }
}
