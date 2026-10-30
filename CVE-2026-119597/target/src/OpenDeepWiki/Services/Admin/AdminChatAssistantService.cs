using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.AI;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端对话助手配置服务实现
/// </summary>
public class AdminChatAssistantService : IAdminChatAssistantService
{
    private readonly IContext _context;
    private readonly ILogger<AdminChatAssistantService> _logger;

    public AdminChatAssistantService(IContext context, ILogger<AdminChatAssistantService> logger)
    {
        _context = context;
        _logger = logger;
    }

    public async Task<ChatAssistantConfigOptionsDto> GetConfigWithOptionsAsync()
    {
        var config = await GetOrCreateConfigAsync();
        var enabledModelIds = ParseJsonArray(config.EnabledModelIds);
        var enabledMcpIds = ParseJsonArray(config.EnabledMcpIds);
        var enabledSkillIds = ParseJsonArray(config.EnabledSkillIds);

        // 获取所有可用的模型
        var providers = await _context.AiProviderConfigs
            .Where(p => !p.IsDeleted)
            .OrderBy(p => p.SortOrder)
            .ThenBy(p => p.DisplayName ?? p.Name)
            .ToListAsync();

        var providerById = providers.ToDictionary(p => p.Id, StringComparer.OrdinalIgnoreCase);

        var aiModels = await _context.AiModelConfigs
            .Where(m => !m.IsDeleted)
            .OrderByDescending(m => m.IsDefault)
            .ThenBy(m => m.SortOrder)
            .ThenBy(m => m.DisplayName ?? m.Name)
            .ToListAsync();

        var aiModelBindingKeys = aiModels
            .Select(m => CreateModelBindingKey(m.ProviderId, m.ModelId))
            .ToHashSet(StringComparer.OrdinalIgnoreCase);

        var modelConfigs = await _context.ModelConfigs
            .Where(m => !m.IsDeleted)
            .OrderByDescending(m => m.IsDefault)
            .ThenBy(m => m.Name)
            .ToListAsync();

        var modelConfigById = modelConfigs.ToDictionary(m => m.Id, StringComparer.OrdinalIgnoreCase);
        var modelConfigByBinding = modelConfigs
            .Where(m => !string.IsNullOrWhiteSpace(m.AiProviderId))
            .GroupBy(m => CreateModelBindingKey(m.AiProviderId!, m.ModelId), StringComparer.OrdinalIgnoreCase)
            .ToDictionary(g => g.Key, g => g.First(), StringComparer.OrdinalIgnoreCase);

        var models = new List<SelectableModelItemDto>();

        foreach (var aiModel in aiModels)
        {
            if (!providerById.TryGetValue(aiModel.ProviderId, out var provider))
            {
                continue;
            }

            var directId = AiModelSelectionIds.Create(aiModel.ProviderId, aiModel.ModelId);
            modelConfigByBinding.TryGetValue(
                CreateModelBindingKey(aiModel.ProviderId, aiModel.ModelId),
                out var modelConfig);

            var providerName = provider.DisplayName ?? provider.Name;
            var modelName = aiModel.DisplayName ?? aiModel.Name ?? aiModel.ModelId;

            models.Add(new SelectableModelItemDto
            {
                Id = directId,
                Name = $"{providerName} / {modelName}",
                Description = modelConfig?.Description ?? aiModel.Description,
                IsActive = provider.IsActive && aiModel.IsActive && (modelConfig?.IsActive ?? true),
                IsSelected = enabledModelIds.Contains(directId, StringComparer.OrdinalIgnoreCase) ||
                             (modelConfig != null && enabledModelIds.Contains(modelConfig.Id, StringComparer.OrdinalIgnoreCase)),
                AiProviderId = provider.Id,
                AiProviderName = providerName,
                AiProviderType = AiProviderResolver.ResolveEffectiveProviderType(
                    provider.ProviderType,
                    aiModel.ProviderType),
                AiProviderIsActive = provider.IsActive,
                ModelId = aiModel.ModelId,
                ModelName = aiModel.Name,
                ModelDisplayName = aiModel.DisplayName,
                ContextWindow = aiModel.ContextWindow,
                SupportsThinking = aiModel.SupportsThinking,
                SupportsVision = aiModel.SupportsVision,
                SupportsTools = aiModel.SupportsTools,
                SupportsJsonMode = aiModel.SupportsJsonMode
            });
        }

        foreach (var modelConfig in modelConfigs.Where(m =>
                     string.IsNullOrWhiteSpace(m.AiProviderId) ||
                     !aiModelBindingKeys.Contains(CreateModelBindingKey(m.AiProviderId!, m.ModelId))))
        {
            providerById.TryGetValue(modelConfig.AiProviderId ?? string.Empty, out var provider);
            var providerName = provider?.DisplayName ?? provider?.Name ?? modelConfig.Provider;

            models.Add(new SelectableModelItemDto
            {
                Id = modelConfig.Id,
                Name = $"{providerName} / {modelConfig.Name}",
                Description = modelConfig.Description,
                IsActive = modelConfig.IsActive && (provider?.IsActive ?? true),
                IsSelected = enabledModelIds.Contains(modelConfig.Id, StringComparer.OrdinalIgnoreCase),
                AiProviderId = modelConfig.AiProviderId,
                AiProviderName = providerName,
                AiProviderType = provider?.ProviderType ?? modelConfig.Provider,
                AiProviderIsActive = provider?.IsActive ?? true,
                ModelId = modelConfig.ModelId,
                ModelName = modelConfig.Name,
                ModelDisplayName = modelConfig.Name,
                SupportsTools = true
            });
        }

        models = models
            .OrderByDescending(m => m.IsSelected)
            .ThenBy(m => m.AiProviderName)
            .ThenBy(m => m.ModelDisplayName ?? m.ModelName ?? m.ModelId)
            .ToList();

        var configDto = MapToDto(config);
        configDto.EnabledModelIds = configDto.EnabledModelIds
            .Select(id => NormalizeModelSelectionId(id, modelConfigById, aiModelBindingKeys))
            .Where(id => !string.IsNullOrWhiteSpace(id))
            .Select(id => id!)
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToList();
        configDto.DefaultModelId = NormalizeModelSelectionId(configDto.DefaultModelId, modelConfigById, aiModelBindingKeys);
        if (!string.IsNullOrWhiteSpace(configDto.DefaultModelId) &&
            !configDto.EnabledModelIds.Contains(configDto.DefaultModelId, StringComparer.OrdinalIgnoreCase))
        {
            configDto.DefaultModelId = null;
        }

        // 获取所有可用的MCP
        var mcps = await _context.McpConfigs
            .Where(m => !m.IsDeleted)
            .OrderBy(m => m.SortOrder)
            .ThenBy(m => m.Name)
            .Select(m => new SelectableItemDto
            {
                Id = m.Id,
                Name = m.Name,
                Description = m.Description,
                IsActive = m.IsActive,
                IsSelected = enabledMcpIds.Contains(m.Id)
            })
            .ToListAsync();

        // 获取所有可用的Skill
        var skills = await _context.SkillConfigs
            .Where(s => !s.IsDeleted)
            .OrderBy(s => s.SortOrder)
            .ThenBy(s => s.Name)
            .Select(s => new SelectableItemDto
            {
                Id = s.Id,
                Name = s.Name,
                Description = s.Description,
                IsActive = s.IsActive,
                IsSelected = enabledSkillIds.Contains(s.Id)
            })
            .ToListAsync();

        return new ChatAssistantConfigOptionsDto
        {
            Config = configDto,
            AvailableModels = models,
            AvailableMcps = mcps,
            AvailableSkills = skills
        };
    }

    public async Task<ChatAssistantConfigDto> GetConfigAsync()
    {
        var config = await GetOrCreateConfigAsync();
        return MapToDto(config);
    }

    public async Task<ChatAssistantConfigDto> UpdateConfigAsync(UpdateChatAssistantConfigRequest request)
    {
        var config = await GetOrCreateConfigAsync();
        var enabledModelIds = request.EnabledModelIds
            .Where(id => !string.IsNullOrWhiteSpace(id))
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToList();
        var enabledMcpIds = request.EnabledMcpIds
            .Where(id => !string.IsNullOrWhiteSpace(id))
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToList();
        var enabledSkillIds = request.EnabledSkillIds
            .Where(id => !string.IsNullOrWhiteSpace(id))
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToList();
        var defaultModelId = !string.IsNullOrWhiteSpace(request.DefaultModelId) &&
                             enabledModelIds.Contains(request.DefaultModelId, StringComparer.OrdinalIgnoreCase)
            ? request.DefaultModelId
            : null;

        config.IsEnabled = request.IsEnabled;
        config.EnabledModelIds = SerializeJsonArray(enabledModelIds);
        config.EnabledMcpIds = SerializeJsonArray(enabledMcpIds);
        config.EnabledSkillIds = SerializeJsonArray(enabledSkillIds);
        config.DefaultModelId = defaultModelId;
        config.EnableImageUpload = request.EnableImageUpload;
        config.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();

        _logger.LogInformation("对话助手配置已更新: IsEnabled={IsEnabled}, Models={ModelCount}, MCPs={McpCount}, Skills={SkillCount}, EnableImageUpload={EnableImageUpload}",
            config.IsEnabled, enabledModelIds.Count, enabledMcpIds.Count, enabledSkillIds.Count, config.EnableImageUpload);

        return MapToDto(config);
    }

    private async Task<ChatAssistantConfig> GetOrCreateConfigAsync()
    {
        var config = await _context.ChatAssistantConfigs
            .FirstOrDefaultAsync(c => !c.IsDeleted);

        if (config == null)
        {
            config = new ChatAssistantConfig
            {
                Id = Guid.NewGuid(),
                IsEnabled = false,
                CreatedAt = DateTime.UtcNow
            };
            _context.ChatAssistantConfigs.Add(config);
            await _context.SaveChangesAsync();
            _logger.LogInformation("创建了新的对话助手配置");
        }

        return config;
    }

    private static ChatAssistantConfigDto MapToDto(ChatAssistantConfig config)
    {
        return new ChatAssistantConfigDto
        {
            Id = config.Id.ToString(),
            IsEnabled = config.IsEnabled,
            EnabledModelIds = ParseJsonArray(config.EnabledModelIds),
            EnabledMcpIds = ParseJsonArray(config.EnabledMcpIds),
            EnabledSkillIds = ParseJsonArray(config.EnabledSkillIds),
            DefaultModelId = config.DefaultModelId,
            EnableImageUpload = config.EnableImageUpload,
            CreatedAt = config.CreatedAt,
            UpdatedAt = config.UpdatedAt
        };
    }

    private static List<string> ParseJsonArray(string? json)
    {
        if (string.IsNullOrWhiteSpace(json))
            return new List<string>();

        try
        {
            return JsonSerializer.Deserialize<List<string>>(json) ?? new List<string>();
        }
        catch
        {
            return new List<string>();
        }
    }

    private static string? SerializeJsonArray(List<string>? items)
    {
        if (items == null || items.Count == 0)
            return null;

        return JsonSerializer.Serialize(items);
    }

    private static string CreateModelBindingKey(string providerId, string modelId)
    {
        return $"{providerId}:{modelId}";
    }

    private static string? NormalizeModelSelectionId(
        string? id,
        Dictionary<string, ModelConfig> modelConfigById,
        HashSet<string> aiModelBindingKeys)
    {
        if (string.IsNullOrWhiteSpace(id) || AiModelSelectionIds.TryParse(id, out _, out _))
        {
            return id;
        }

        if (!modelConfigById.TryGetValue(id, out var modelConfig) ||
            string.IsNullOrWhiteSpace(modelConfig.AiProviderId))
        {
            return id;
        }

        return aiModelBindingKeys.Contains(CreateModelBindingKey(modelConfig.AiProviderId!, modelConfig.ModelId))
            ? AiModelSelectionIds.Create(modelConfig.AiProviderId!, modelConfig.ModelId)
            : id;
    }
}
