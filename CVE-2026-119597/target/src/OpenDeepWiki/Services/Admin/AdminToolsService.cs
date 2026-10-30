using System.IO.Compression;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using System.Text.Json.Nodes;
using System.Text.RegularExpressions;
using System.Diagnostics;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Entities.Tools;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.AI;
using OpenDeepWiki.Agents;
using YamlDotNet.Serialization;
using YamlDotNet.Serialization.NamingConventions;
using System.Globalization;

namespace OpenDeepWiki.Services.Admin;

public class AdminToolsService : IAdminToolsService
{
    private readonly IContext _context;
    private readonly ILogger<AdminToolsService> _logger;
    private readonly IHttpClientFactory _httpClientFactory;
    private readonly string _skillsBasePath;

    public AdminToolsService(
        IContext context,
        ILogger<AdminToolsService> logger,
        IConfiguration configuration,
        IHttpClientFactory httpClientFactory)
    {
        _context = context;
        _logger = logger;
        _httpClientFactory = httpClientFactory;
        _skillsBasePath = configuration["Skills:BasePath"] ?? Path.Combine(AppContext.BaseDirectory, "skills");
        if (!Directory.Exists(_skillsBasePath)) Directory.CreateDirectory(_skillsBasePath);
    }

    public async Task<List<McpConfigDto>> GetMcpConfigsAsync()
    {
        return await _context.McpConfigs.Where(m => !m.IsDeleted).OrderBy(m => m.SortOrder)
            .Select(m => new McpConfigDto
            {
                Id = m.Id, Name = m.Name, Description = m.Description, ServerUrl = m.ServerUrl,
                HasApiKey = !string.IsNullOrEmpty(m.ApiKey), IsActive = m.IsActive,
                SortOrder = m.SortOrder, CreatedAt = m.CreatedAt
            }).ToListAsync();
    }

    public async Task<McpConfigDto> CreateMcpConfigAsync(McpConfigRequest request)
    {
        var config = new McpConfig
        {
            Id = Guid.NewGuid().ToString(), Name = request.Name, Description = request.Description,
            ServerUrl = request.ServerUrl, ApiKey = request.ApiKey, IsActive = request.IsActive,
            SortOrder = request.SortOrder, CreatedAt = DateTime.UtcNow
        };
        _context.McpConfigs.Add(config);
        await _context.SaveChangesAsync();
        return new McpConfigDto
        {
            Id = config.Id, Name = config.Name, Description = config.Description,
            ServerUrl = config.ServerUrl, HasApiKey = !string.IsNullOrEmpty(config.ApiKey),
            IsActive = config.IsActive, SortOrder = config.SortOrder, CreatedAt = config.CreatedAt
        };
    }


    public async Task<bool> UpdateMcpConfigAsync(string id, McpConfigRequest request)
    {
        var config = await _context.McpConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (config == null) return false;
        config.Name = request.Name; config.Description = request.Description;
        config.ServerUrl = request.ServerUrl;
        if (request.ApiKey != null) config.ApiKey = request.ApiKey;
        config.IsActive = request.IsActive; config.SortOrder = request.SortOrder;
        config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteMcpConfigAsync(string id)
    {
        var config = await _context.McpConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (config == null) return false;
        config.IsDeleted = true; config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<SkillConfigDto>> GetSkillConfigsAsync()
    {
        var skills = await _context.SkillConfigs
            .Where(s => !s.IsDeleted)
            .OrderBy(s => s.SortOrder)
            .ThenBy(s => s.Name)
            .ToListAsync();

        var result = new List<SkillConfigDto>(skills.Count);
        foreach (var skill in skills)
        {
            var dto = MapSkillConfig(skill);
            dto.Frontmatter = await LoadSkillFrontmatterAsync(skill);
            result.Add(dto);
        }

        return result;
    }

    public async Task<SkillDetailDto?> GetSkillDetailAsync(string id)
    {
        var skill = await _context.SkillConfigs.FirstOrDefaultAsync(s => s.Id == id && !s.IsDeleted);
        if (skill == null) return null;
        var skillPath = Path.Combine(_skillsBasePath, skill.FolderPath);
        var detail = new SkillDetailDto(MapSkillConfig(skill));
        detail.Frontmatter = await LoadSkillFrontmatterAsync(skill);
        var skillMdPath = Path.Combine(skillPath, "SKILL.md");
        if (File.Exists(skillMdPath)) detail.SkillMdContent = await File.ReadAllTextAsync(skillMdPath);
        detail.Scripts = ListDirectoryFiles(Path.Combine(skillPath, "scripts"));
        detail.References = ListDirectoryFiles(Path.Combine(skillPath, "references"));
        detail.Assets = ListDirectoryFiles(Path.Combine(skillPath, "assets"));
        return detail;
    }

    private SkillConfigDto MapSkillConfig(SkillConfig skill)
    {
        return new SkillConfigDto
        {
            Id = skill.Id,
            Name = skill.Name,
            Description = skill.Description,
            License = skill.License,
            Compatibility = skill.Compatibility,
            AllowedTools = skill.AllowedTools,
            FolderPath = skill.FolderPath,
            IsActive = skill.IsActive,
            SortOrder = skill.SortOrder,
            Author = skill.Author,
            Version = skill.Version,
            Source = skill.Source.ToString().ToLower(),
            SourceUrl = skill.SourceUrl,
            HasScripts = skill.HasScripts,
            HasReferences = skill.HasReferences,
            HasAssets = skill.HasAssets,
            SkillMdSize = skill.SkillMdSize,
            TotalSize = skill.TotalSize,
            CreatedAt = skill.CreatedAt
        };
    }

    private async Task<Dictionary<string, object?>> LoadSkillFrontmatterAsync(SkillConfig skill)
    {
        try
        {
            var skillMdPath = Path.Combine(_skillsBasePath, skill.FolderPath, "SKILL.md");
            if (!File.Exists(skillMdPath))
            {
                return new Dictionary<string, object?>();
            }

            var content = await File.ReadAllTextAsync(skillMdPath);
            var (frontmatter, _) = ParseSkillMd(content);
            return frontmatter;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to load SKILL.md frontmatter for {Skill}", skill.Name);
            return new Dictionary<string, object?>();
        }
    }


    public async Task<SkillConfigDto> UploadSkillAsync(Stream zipStream, string fileName)
    {
        var tempDir = Path.Combine(Path.GetTempPath(), Guid.NewGuid().ToString());
        Directory.CreateDirectory(tempDir);
        try
        {
            using (var archive = new ZipArchive(zipStream, ZipArchiveMode.Read))
                archive.ExtractToDirectory(tempDir);
            var skillMdPath = FindSkillMd(tempDir) ?? throw new InvalidOperationException("压缩包中未找到 SKILL.md");
            var skillRootDir = Path.GetDirectoryName(skillMdPath)!;
            var (frontmatter, _) = ParseSkillMd(await File.ReadAllTextAsync(skillMdPath));
            if (!frontmatter.TryGetValue("name", out var nameObj) || string.IsNullOrEmpty(nameObj?.ToString()))
                throw new InvalidOperationException("SKILL.md 缺少 name 字段");
            var name = nameObj.ToString()!;
            if (!Regex.IsMatch(name, @"^[a-z0-9]+(-[a-z0-9]+)*$"))
                throw new InvalidOperationException("name 格式无效");
            if (!frontmatter.TryGetValue("description", out var descObj) || string.IsNullOrEmpty(descObj?.ToString()))
                throw new InvalidOperationException("SKILL.md 缺少 description 字段");
            if (await _context.SkillConfigs.AnyAsync(s => s.Name == name && !s.IsDeleted))
                throw new InvalidOperationException($"已存在同名 Skill: {name}");
            var targetPath = Path.Combine(_skillsBasePath, name);
            if (Directory.Exists(targetPath)) Directory.Delete(targetPath, true);
            Directory.Move(skillRootDir, targetPath);
            var config = new SkillConfig
            {
                Id = Guid.NewGuid().ToString(), Name = name, Description = descObj.ToString()!,
                License = frontmatter.TryGetValue("license", out var l) ? l?.ToString() : null,
                Compatibility = frontmatter.TryGetValue("compatibility", out var c) ? c?.ToString() : null,
                AllowedTools = frontmatter.TryGetValue("allowed-tools", out var t) ? t?.ToString() : null,
                FolderPath = name, IsActive = true, SortOrder = 0, Version = "1.0.0", Source = SkillSource.Local,
                HasScripts = Directory.Exists(Path.Combine(targetPath, "scripts")),
                HasReferences = Directory.Exists(Path.Combine(targetPath, "references")),
                HasAssets = Directory.Exists(Path.Combine(targetPath, "assets")),
                SkillMdSize = new FileInfo(Path.Combine(targetPath, "SKILL.md")).Length,
                TotalSize = CalculateDirectorySize(targetPath), CreatedAt = DateTime.UtcNow
            };
            if (frontmatter.TryGetValue("metadata", out var meta) && meta is Dictionary<object, object> metaDict)
            {
                if (metaDict.TryGetValue("author", out var a)) config.Author = a?.ToString();
                if (metaDict.TryGetValue("version", out var v)) config.Version = v?.ToString() ?? "1.0.0";
            }
            _context.SkillConfigs.Add(config);
            await _context.SaveChangesAsync();
            _logger.LogInformation("上传 Skill: {Name}", name);
            return new SkillConfigDto
            {
                Id = config.Id, Name = config.Name, Description = config.Description, License = config.License,
                Compatibility = config.Compatibility, AllowedTools = config.AllowedTools, FolderPath = config.FolderPath,
                IsActive = config.IsActive, SortOrder = config.SortOrder, Author = config.Author, Version = config.Version,
                Source = config.Source.ToString().ToLower(), HasScripts = config.HasScripts,
                HasReferences = config.HasReferences, HasAssets = config.HasAssets,
                SkillMdSize = config.SkillMdSize, TotalSize = config.TotalSize, CreatedAt = config.CreatedAt
            };
        }
        finally { if (Directory.Exists(tempDir)) try { Directory.Delete(tempDir, true); } catch { } }
    }

    public async Task<bool> UpdateSkillAsync(string id, SkillUpdateRequest request)
    {
        var config = await _context.SkillConfigs.FirstOrDefaultAsync(s => s.Id == id && !s.IsDeleted);
        if (config == null) return false;
        if (request.IsActive.HasValue) config.IsActive = request.IsActive.Value;
        if (request.SortOrder.HasValue) config.SortOrder = request.SortOrder.Value;
        config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteSkillAsync(string id)
    {
        var config = await _context.SkillConfigs.FirstOrDefaultAsync(s => s.Id == id && !s.IsDeleted);
        if (config == null) return false;
        var skillPath = Path.Combine(_skillsBasePath, config.FolderPath);
        if (Directory.Exists(skillPath)) Directory.Delete(skillPath, true);
        config.IsDeleted = true; config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        _logger.LogInformation("删除 Skill: {Name}", config.Name);
        return true;
    }

    public async Task<string?> GetSkillFileContentAsync(string id, string filePath)
    {
        var config = await _context.SkillConfigs.FirstOrDefaultAsync(s => s.Id == id && !s.IsDeleted);
        if (config == null) return null;
        var normalizedPath = Path.GetFullPath(Path.Combine(_skillsBasePath, config.FolderPath, filePath));
        var skillBasePath = Path.GetFullPath(Path.Combine(_skillsBasePath, config.FolderPath));
        if (!normalizedPath.StartsWith(skillBasePath)) throw new UnauthorizedAccessException("非法路径");
        return File.Exists(normalizedPath) ? await File.ReadAllTextAsync(normalizedPath) : null;
    }


    public async Task RefreshSkillsFromDiskAsync()
    {
        if (!Directory.Exists(_skillsBasePath)) return;
        var existingNames = (await _context.SkillConfigs.Where(s => !s.IsDeleted).ToListAsync()).Select(s => s.Name).ToHashSet();
        foreach (var dir in Directory.GetDirectories(_skillsBasePath))
        {
            var skillMdPath = Path.Combine(dir, "SKILL.md");
            if (!File.Exists(skillMdPath)) continue;
            var folderName = Path.GetFileName(dir);
            if (existingNames.Contains(folderName)) continue;
            try
            {
                var (frontmatter, _) = ParseSkillMd(await File.ReadAllTextAsync(skillMdPath));
                if (!frontmatter.TryGetValue("name", out var nameObj)) continue;
                var name = nameObj?.ToString();
                if (string.IsNullOrEmpty(name) || name != folderName) continue;
                if (!frontmatter.TryGetValue("description", out var descObj)) continue;
                var config = new SkillConfig
                {
                    Id = Guid.NewGuid().ToString(), Name = name, Description = descObj?.ToString() ?? "",
                    License = frontmatter.TryGetValue("license", out var l) ? l?.ToString() : null,
                    Compatibility = frontmatter.TryGetValue("compatibility", out var c) ? c?.ToString() : null,
                    AllowedTools = frontmatter.TryGetValue("allowed-tools", out var t) ? t?.ToString() : null,
                    FolderPath = folderName, IsActive = true,
                    HasScripts = Directory.Exists(Path.Combine(dir, "scripts")),
                    HasReferences = Directory.Exists(Path.Combine(dir, "references")),
                    HasAssets = Directory.Exists(Path.Combine(dir, "assets")),
                    SkillMdSize = new FileInfo(skillMdPath).Length,
                    TotalSize = CalculateDirectorySize(dir), CreatedAt = DateTime.UtcNow
                };
                _context.SkillConfigs.Add(config);
                _logger.LogInformation("发现 Skill: {Name}", name);
            }
            catch (Exception ex) { _logger.LogWarning(ex, "解析失败: {Path}", dir); }
        }
        await _context.SaveChangesAsync();
    }

    public async Task<List<AiProviderConfigDto>> GetAiProvidersAsync()
    {
        return await _context.AiProviderConfigs
            .Where(p => !p.IsDeleted)
            .OrderBy(p => p.SortOrder)
            .ThenBy(p => p.Name)
            .Select(p => new AiProviderConfigDto
            {
                Id = p.Id,
                Name = p.Name,
                DisplayName = p.DisplayName,
                ProviderType = p.ProviderType,
                BaseUrl = p.BaseUrl,
                HasApiKey = !string.IsNullOrEmpty(p.ApiKey),
                AuthType = p.AuthType,
                IsBuiltIn = p.IsBuiltIn,
                IsActive = p.IsActive,
                SupportsModelDiscovery = p.SupportsModelDiscovery,
                ModelsEndpoint = p.ModelsEndpoint,
                DefaultModelId = p.DefaultModelId,
                SystemProxyUrl = p.SystemProxyUrl,
                OAuthConfigJson = p.OAuthConfigJson,
                ChannelConfigJson = p.ChannelConfigJson,
                AccountsJson = p.AccountsJson,
                RequestOverridesJson = p.RequestOverridesJson,
                IconUrl = p.IconUrl,
                Description = p.Description,
                SortOrder = p.SortOrder,
                CreatedAt = p.CreatedAt
            })
            .ToListAsync();
    }

    public async Task<AiProviderConfigDto> CreateAiProviderAsync(AiProviderConfigRequest request)
    {
        var provider = new AiProviderConfig
        {
            Id = Guid.NewGuid().ToString(),
            Name = request.Name,
            DisplayName = request.DisplayName,
            ProviderType = request.ProviderType,
            BaseUrl = request.BaseUrl.TrimEnd('/'),
            ApiKey = request.ApiKey,
            AuthType = request.AuthType,
            IsBuiltIn = request.IsBuiltIn,
            IsActive = request.IsActive,
            SupportsModelDiscovery = request.SupportsModelDiscovery,
            ModelsEndpoint = request.ModelsEndpoint,
            DefaultModelId = request.DefaultModelId,
            SystemProxyUrl = request.SystemProxyUrl,
            OAuthConfigJson = request.OAuthConfigJson,
            ChannelConfigJson = request.ChannelConfigJson,
            AccountsJson = request.AccountsJson,
            RequestOverridesJson = request.RequestOverridesJson,
            IconUrl = request.IconUrl,
            Description = request.Description,
            SortOrder = request.SortOrder,
            CreatedAt = DateTime.UtcNow
        };

        _context.AiProviderConfigs.Add(provider);
        await _context.SaveChangesAsync();
        return MapAiProvider(provider);
    }

    public async Task<bool> UpdateAiProviderAsync(string id, AiProviderConfigRequest request)
    {
        var provider = await _context.AiProviderConfigs.FirstOrDefaultAsync(p => p.Id == id && !p.IsDeleted);
        if (provider == null) return false;

        provider.Name = request.Name;
        provider.DisplayName = request.DisplayName;
        provider.ProviderType = request.ProviderType;
        provider.BaseUrl = request.BaseUrl.TrimEnd('/');
        if (request.ApiKey != null) provider.ApiKey = request.ApiKey;
        provider.AuthType = request.AuthType;
        provider.IsBuiltIn = request.IsBuiltIn;
        provider.IsActive = request.IsActive;
        provider.SupportsModelDiscovery = request.SupportsModelDiscovery;
        provider.ModelsEndpoint = request.ModelsEndpoint;
        provider.DefaultModelId = request.DefaultModelId;
        provider.SystemProxyUrl = request.SystemProxyUrl;
        provider.OAuthConfigJson = request.OAuthConfigJson;
        provider.ChannelConfigJson = request.ChannelConfigJson;
        provider.AccountsJson = request.AccountsJson;
        provider.RequestOverridesJson = request.RequestOverridesJson;
        provider.IconUrl = request.IconUrl;
        provider.Description = request.Description;
        provider.SortOrder = request.SortOrder;
        provider.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteAiProviderAsync(string id)
    {
        var provider = await _context.AiProviderConfigs.FirstOrDefaultAsync(p => p.Id == id && !p.IsDeleted);
        if (provider == null) return false;
        provider.IsDeleted = true;
        provider.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<AiModelConfigDto>> GetAiModelsAsync(string? providerId = null)
    {
        var query = _context.AiModelConfigs.Where(m => !m.IsDeleted);
        if (!string.IsNullOrWhiteSpace(providerId))
        {
            query = query.Where(m => m.ProviderId == providerId);
        }

        var models = await query
            .OrderByDescending(m => m.IsDefault)
            .ThenBy(m => m.SortOrder)
            .ThenBy(m => m.Name)
            .ToListAsync();

        var providerIds = models.Select(m => m.ProviderId).Distinct().ToList();
        var providers = providerIds.Count == 0
            ? new Dictionary<string, string>()
            : await _context.AiProviderConfigs
                .Where(p => providerIds.Contains(p.Id) && !p.IsDeleted)
                .ToDictionaryAsync(p => p.Id, p => p.DisplayName ?? p.Name);

        return models.Select(m => MapAiModel(m, providers.GetValueOrDefault(m.ProviderId))).ToList();
    }

    public async Task<AiModelConfigDto> CreateAiModelAsync(AiModelConfigRequest request)
    {
        var model = new AiModelConfig
        {
            Id = Guid.NewGuid().ToString(),
            ProviderId = request.ProviderId,
            ModelId = request.ModelId,
            Name = string.IsNullOrWhiteSpace(request.Name) ? request.ModelId : request.Name,
            DisplayName = request.DisplayName,
            ModelType = request.ModelType,
            ProviderType = AiProviderResolver.NormalizeModelProviderType(request.ProviderType, request.ModelId),
            ContextWindow = request.ContextWindow,
            MaxOutputTokens = request.MaxOutputTokens,
            InputTokenPrice = request.InputTokenPrice,
            OutputTokenPrice = request.OutputTokenPrice,
            CacheHitTokenPrice = request.CacheHitTokenPrice,
            CacheCreationTokenPrice = request.CacheCreationTokenPrice,
            SupportsThinking = request.SupportsThinking,
            SupportsVision = request.SupportsVision,
            SupportsTools = request.SupportsTools,
            SupportsJsonMode = request.SupportsJsonMode,
            IsDefault = request.IsDefault,
            IsActive = request.IsActive,
            CapabilitiesJson = request.CapabilitiesJson,
            ThinkingConfigJson = request.ThinkingConfigJson,
            RequestOverridesJson = request.RequestOverridesJson,
            TagsJson = request.TagsJson,
            Description = request.Description,
            SortOrder = request.SortOrder,
            CreatedAt = DateTime.UtcNow
        };

        _context.AiModelConfigs.Add(model);
        await _context.SaveChangesAsync();
        return MapAiModel(model, null);
    }

    public async Task<bool> UpdateAiModelAsync(string id, AiModelConfigRequest request)
    {
        var model = await _context.AiModelConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (model == null) return false;

        model.ProviderId = request.ProviderId;
        model.ModelId = request.ModelId;
        model.Name = string.IsNullOrWhiteSpace(request.Name) ? request.ModelId : request.Name;
        model.DisplayName = request.DisplayName;
        model.ModelType = request.ModelType;
        model.ProviderType = AiProviderResolver.NormalizeModelProviderType(request.ProviderType, request.ModelId);
        model.ContextWindow = request.ContextWindow;
        model.MaxOutputTokens = request.MaxOutputTokens;
        model.InputTokenPrice = request.InputTokenPrice;
        model.OutputTokenPrice = request.OutputTokenPrice;
        model.CacheHitTokenPrice = request.CacheHitTokenPrice;
        model.CacheCreationTokenPrice = request.CacheCreationTokenPrice;
        model.SupportsThinking = request.SupportsThinking;
        model.SupportsVision = request.SupportsVision;
        model.SupportsTools = request.SupportsTools;
        model.SupportsJsonMode = request.SupportsJsonMode;
        model.IsDefault = request.IsDefault;
        model.IsActive = request.IsActive;
        model.CapabilitiesJson = request.CapabilitiesJson;
        model.ThinkingConfigJson = request.ThinkingConfigJson;
        model.RequestOverridesJson = request.RequestOverridesJson;
        model.TagsJson = request.TagsJson;
        model.Description = request.Description;
        model.SortOrder = request.SortOrder;
        model.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteAiModelAsync(string id)
    {
        var model = await _context.AiModelConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (model == null) return false;
        model.IsDeleted = true;
        model.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<AiModelConfigDto>> DiscoverAiModelsAsync(
        string providerId,
        CancellationToken cancellationToken = default)
    {
        var provider = await _context.AiProviderConfigs
            .FirstOrDefaultAsync(p => p.Id == providerId && !p.IsDeleted, cancellationToken);
        if (provider == null)
        {
            throw new InvalidOperationException("AI provider does not exist.");
        }

        var endpoint = string.IsNullOrWhiteSpace(provider.ModelsEndpoint)
            ? $"{provider.BaseUrl.TrimEnd('/')}/models"
            : provider.ModelsEndpoint;

        var client = _httpClientFactory.CreateClient();
        using var request = new HttpRequestMessage(HttpMethod.Get, endpoint);
        if (!string.IsNullOrWhiteSpace(provider.ApiKey))
        {
            if (provider.ProviderType.Equals("Anthropic", StringComparison.OrdinalIgnoreCase))
            {
                request.Headers.Add("x-api-key", provider.ApiKey);
                request.Headers.Add("anthropic-version", "2023-06-01");
            }
            else
            {
                request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", provider.ApiKey);
            }
        }

        using var response = await client.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();
        var json = await response.Content.ReadAsStringAsync(cancellationToken);

        using var document = JsonDocument.Parse(json);
        var modelElements = EnumerateModelElements(document.RootElement);
        return modelElements
            .Select(model => new AiModelConfigDto
            {
                ProviderId = provider.Id,
                ProviderName = provider.DisplayName ?? provider.Name,
                ModelId = model.ModelId,
                Name = model.Name ?? model.ModelId,
                DisplayName = model.DisplayName ?? model.Name ?? model.ModelId,
                ModelType = "chat",
                ProviderType = AiProviderResolver.NormalizeModelProviderType(null, model.ModelId) ?? provider.ProviderType,
                InputTokenPrice = model.InputTokenPrice,
                OutputTokenPrice = model.OutputTokenPrice,
                CacheHitTokenPrice = model.CacheHitTokenPrice,
                CacheCreationTokenPrice = model.CacheCreationTokenPrice,
                SupportsTools = true,
                IsActive = true
            })
            .ToList();
    }

    public async Task<AiProviderConnectivityTestResult> TestAiProviderConnectivityAsync(
        string providerId,
        AiProviderConnectivityTestRequest request,
        CancellationToken cancellationToken = default)
    {
        var provider = await _context.AiProviderConfigs
            .FirstOrDefaultAsync(p => p.Id == providerId && !p.IsDeleted, cancellationToken);
        if (provider == null)
        {
            throw new InvalidOperationException("AI provider does not exist.");
        }

        request ??= new AiProviderConnectivityTestRequest();
        var modelId = request.ModelId?.Trim();
        if (string.IsNullOrWhiteSpace(modelId))
        {
            modelId = provider.DefaultModelId;
        }

        if (string.IsNullOrWhiteSpace(modelId))
        {
            modelId = await _context.AiModelConfigs
                .Where(m => m.ProviderId == provider.Id && m.IsActive && !m.IsDeleted)
                .OrderByDescending(m => m.IsDefault)
                .ThenBy(m => m.SortOrder)
                .ThenBy(m => m.Name)
                .Select(m => m.ModelId)
                .FirstOrDefaultAsync(cancellationToken);
        }

        if (string.IsNullOrWhiteSpace(modelId))
        {
            throw new InvalidOperationException("Please select a model before checking connectivity.");
        }

        var model = await _context.AiModelConfigs.FirstOrDefaultAsync(m =>
                m.ProviderId == provider.Id &&
                m.ModelId == modelId &&
                !m.IsDeleted,
            cancellationToken);

        var providerType = AiProviderResolver.ResolveEffectiveProviderType(
            string.IsNullOrWhiteSpace(request.ProviderType) ? provider.ProviderType : request.ProviderType,
            model?.ProviderType);
        var baseUrl = NormalizeConnectivityBaseUrl(
            string.IsNullOrWhiteSpace(request.BaseUrl) ? provider.BaseUrl : request.BaseUrl,
            providerType);
        var apiKey = request.ApiKey ?? provider.ApiKey;

        using var message = CreateConnectivityRequest(
            providerType,
            baseUrl,
            apiKey,
            modelId,
            model);
        var client = _httpClientFactory.CreateClient();
        using var timeout = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);
        timeout.CancelAfter(TimeSpan.FromSeconds(30));
        var stopwatch = Stopwatch.StartNew();

        using var response = await client.SendAsync(message, timeout.Token);
        stopwatch.Stop();
        var content = await response.Content.ReadAsStringAsync(cancellationToken);

        if (response.IsSuccessStatusCode)
        {
            return new AiProviderConnectivityTestResult
            {
                Success = true,
                Message = $"Connectivity check succeeded for model '{modelId}'.",
                ProviderType = providerType,
                ModelId = modelId,
                StatusCode = (int)response.StatusCode,
                LatencyMs = stopwatch.ElapsedMilliseconds
            };
        }

        var error = string.IsNullOrWhiteSpace(content)
            ? response.ReasonPhrase
            : content.Trim();
        if (error is { Length: > 600 })
        {
            error = error[..600];
        }

        return new AiProviderConnectivityTestResult
        {
            Success = false,
            Message = $"Connectivity check failed with HTTP {(int)response.StatusCode}: {error}",
            ProviderType = providerType,
            ModelId = modelId,
            StatusCode = (int)response.StatusCode,
            LatencyMs = stopwatch.ElapsedMilliseconds
        };
    }

    public async Task<List<ModelConfigDto>> GetModelConfigsAsync()
    {
        var configs = await _context.ModelConfigs
            .Where(m => !m.IsDeleted)
            .OrderByDescending(m => m.IsDefault)
            .ThenBy(m => m.Name)
            .ToListAsync();

        var providerIds = configs
            .Where(m => !string.IsNullOrEmpty(m.AiProviderId))
            .Select(m => m.AiProviderId!)
            .Distinct()
            .ToList();

        var providers = providerIds.Count == 0
            ? new Dictionary<string, AiProviderConfig>()
            : await _context.AiProviderConfigs
                .Where(p => providerIds.Contains(p.Id) && !p.IsDeleted)
                .ToDictionaryAsync(p => p.Id);

        var aiModels = providerIds.Count == 0
            ? new Dictionary<string, AiModelConfig>(StringComparer.OrdinalIgnoreCase)
            : await _context.AiModelConfigs
                .Where(m => !m.IsDeleted && providerIds.Contains(m.ProviderId))
                .ToDictionaryAsync(
                    m => CreateModelBindingKey(m.ProviderId, m.ModelId),
                    StringComparer.OrdinalIgnoreCase);

        return configs.Select(m =>
        {
            providers.TryGetValue(m.AiProviderId ?? string.Empty, out var provider);
            aiModels.TryGetValue(
                CreateModelBindingKey(m.AiProviderId ?? string.Empty, m.ModelId),
                out var aiModel);
            var providerType = AiProviderResolver.ResolveEffectiveProviderType(
                provider?.ProviderType ?? m.Provider,
                aiModel?.ProviderType);
            return new ModelConfigDto
            {
                Id = m.Id,
                Name = m.Name,
                AiProviderId = m.AiProviderId,
                AiProviderName = provider?.DisplayName ?? provider?.Name,
                Provider = providerType,
                ModelId = m.ModelId,
                Endpoint = null,
                HasApiKey = provider != null && !string.IsNullOrEmpty(provider.ApiKey),
                IsDefault = m.IsDefault,
                IsActive = m.IsActive,
                Description = m.Description,
                CreatedAt = m.CreatedAt
            };
        }).ToList();
    }

    public async Task<ModelConfigDto> CreateModelConfigAsync(ModelConfigRequest request)
    {
        var provider = !string.IsNullOrWhiteSpace(request.AiProviderId)
            ? await _context.AiProviderConfigs.FirstOrDefaultAsync(p => p.Id == request.AiProviderId && !p.IsDeleted)
            : null;
        var providerType = provider == null
            ? AiProviderResolver.ResolveEffectiveProviderType(request.Provider, null)
            : await ResolveModelProviderTypeAsync(provider, request.ModelId);

        var config = new ModelConfig
        {
            Id = Guid.NewGuid().ToString(), Name = request.Name, Provider = providerType,
            AiProviderId = request.AiProviderId, ModelId = request.ModelId, Endpoint = null, ApiKey = null,
            IsDefault = request.IsDefault, IsActive = request.IsActive, Description = request.Description,
            CreatedAt = DateTime.UtcNow
        };
        _context.ModelConfigs.Add(config);
        await _context.SaveChangesAsync();
        return new ModelConfigDto
        {
            Id = config.Id, Name = config.Name, AiProviderId = config.AiProviderId,
            AiProviderName = provider?.DisplayName ?? provider?.Name, Provider = providerType,
            ModelId = config.ModelId, Endpoint = null, HasApiKey = provider != null && !string.IsNullOrEmpty(provider.ApiKey),
            IsDefault = config.IsDefault, IsActive = config.IsActive, Description = config.Description,
            CreatedAt = config.CreatedAt
        };
    }

    public async Task<bool> UpdateModelConfigAsync(string id, ModelConfigRequest request)
    {
        var config = await _context.ModelConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (config == null) return false;
        var provider = !string.IsNullOrWhiteSpace(request.AiProviderId)
            ? await _context.AiProviderConfigs.FirstOrDefaultAsync(p => p.Id == request.AiProviderId && !p.IsDeleted)
            : null;
        var providerType = provider == null
            ? AiProviderResolver.ResolveEffectiveProviderType(request.Provider, null)
            : await ResolveModelProviderTypeAsync(provider, request.ModelId);
        config.Name = request.Name; config.Provider = providerType; config.ModelId = request.ModelId;
        config.AiProviderId = request.AiProviderId;
        config.Endpoint = null;
        config.ApiKey = null;
        config.IsDefault = request.IsDefault; config.IsActive = request.IsActive;
        config.Description = request.Description; config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteModelConfigAsync(string id)
    {
        var config = await _context.ModelConfigs.FirstOrDefaultAsync(m => m.Id == id && !m.IsDeleted);
        if (config == null) return false;
        config.IsDeleted = true; config.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    private async Task<string> ResolveModelProviderTypeAsync(AiProviderConfig provider, string modelId)
    {
        var model = await _context.AiModelConfigs.FirstOrDefaultAsync(m =>
            m.ProviderId == provider.Id &&
            m.ModelId == modelId &&
            !m.IsDeleted);

        return AiProviderResolver.ResolveEffectiveProviderType(
            provider.ProviderType,
            model?.ProviderType);
    }

    private static HttpRequestMessage CreateConnectivityRequest(
        string providerType,
        string baseUrl,
        string? apiKey,
        string modelId,
        AiModelConfig? model)
    {
        var requestType = AiProviderResolver.ParseRequestType(providerType);
        return requestType switch
        {
            AiRequestType.OpenAIResponses => CreateJsonRequest(
                HttpMethod.Post,
                AppendEndpointPath(baseUrl, "responses", "responses"),
                apiKey,
                new
                {
                    model = modelId,
                    input = "ping",
                    max_output_tokens = 1,
                    stream = false
                }),
            AiRequestType.Anthropic => CreateJsonRequest(
                HttpMethod.Post,
                AppendEndpointPath(baseUrl, "v1/messages", "messages"),
                apiKey,
                new
                {
                    model = modelId,
                    max_tokens = 1,
                    messages = new[]
                    {
                        new { role = "user", content = "ping" }
                    }
                },
                configure: message =>
                {
                    if (!string.IsNullOrWhiteSpace(apiKey))
                    {
                        message.Headers.Remove("Authorization");
                        message.Headers.Add("x-api-key", apiKey);
                    }

                    message.Headers.Add("anthropic-version", "2023-06-01");
                }),
            AiRequestType.DeepSeekOpenAI => CreateJsonRequest(
                HttpMethod.Post,
                AppendEndpointPath(baseUrl, "chat/completions", "chat/completions"),
                apiKey,
                CreateDeepSeekConnectivityBody(modelId, model)),
            AiRequestType.AzureOpenAI => CreateAzureOpenAIConnectivityRequest(baseUrl, apiKey, modelId),
            _ => CreateJsonRequest(
                HttpMethod.Post,
                AppendEndpointPath(baseUrl, "chat/completions", "chat/completions"),
                apiKey,
                new
                {
                    model = modelId,
                    max_tokens = 1,
                    stream = false,
                    messages = new[]
                    {
                        new { role = "user", content = "ping" }
                    }
                })
        };
    }

    private static JsonObject CreateDeepSeekConnectivityBody(string modelId, AiModelConfig? model)
    {
        var body = new JsonObject
        {
            ["model"] = modelId,
            ["max_tokens"] = 1,
            ["stream"] = false,
            ["messages"] = new JsonArray
            {
                new JsonObject
                {
                    ["role"] = "user",
                    ["content"] = "ping"
                }
            }
        };

        if (model?.SupportsThinking == true &&
            !string.IsNullOrWhiteSpace(model.ThinkingConfigJson))
        {
            try
            {
                if (JsonNode.Parse(model.ThinkingConfigJson) is JsonObject config &&
                    config["bodyParams"] is JsonObject bodyParams)
                {
                    foreach (var pair in bodyParams)
                    {
                        body[pair.Key] = pair.Value?.DeepClone();
                    }
                }
            }
            catch (JsonException)
            {
                // Connectivity checks should still verify the base protocol if model metadata is malformed.
            }
        }

        return body;
    }

    private static HttpRequestMessage CreateAzureOpenAIConnectivityRequest(
        string baseUrl,
        string? apiKey,
        string modelId)
    {
        var endpoint = baseUrl.Contains("/chat/completions", StringComparison.OrdinalIgnoreCase)
            ? baseUrl
            : $"{baseUrl.TrimEnd('/')}/openai/deployments/{Uri.EscapeDataString(modelId)}/chat/completions?api-version=2024-10-21";
        return CreateJsonRequest(
            HttpMethod.Post,
            endpoint,
            apiKey,
            new
            {
                max_tokens = 1,
                stream = false,
                messages = new[]
                {
                    new { role = "user", content = "ping" }
                }
            },
            message =>
            {
                message.Headers.Remove("Authorization");
                if (!string.IsNullOrWhiteSpace(apiKey))
                {
                    message.Headers.Add("api-key", apiKey);
                }
            });
    }

    private static HttpRequestMessage CreateJsonRequest(
        HttpMethod method,
        string endpoint,
        string? apiKey,
        object body,
        Action<HttpRequestMessage>? configure = null)
    {
        var message = new HttpRequestMessage(method, endpoint)
        {
            Content = new StringContent(
                JsonSerializer.Serialize(body),
                Encoding.UTF8,
                "application/json")
        };

        if (!string.IsNullOrWhiteSpace(apiKey))
        {
            message.Headers.Authorization = new AuthenticationHeaderValue("Bearer", apiKey);
        }

        configure?.Invoke(message);
        return message;
    }

    private static string NormalizeConnectivityBaseUrl(string? baseUrl, string providerType)
    {
        if (!string.IsNullOrWhiteSpace(baseUrl))
        {
            var normalized = baseUrl.Trim().TrimEnd('/');
            return AiProviderResolver.NormalizeProviderType(providerType).Equals("Anthropic", StringComparison.OrdinalIgnoreCase) &&
                   normalized.EndsWith("/v1", StringComparison.OrdinalIgnoreCase)
                ? normalized[..^3]
                : normalized;
        }

        return AiProviderResolver.NormalizeProviderType(providerType) switch
        {
            "Anthropic" => "https://api.anthropic.com",
            "DeepSeekOpenAI" => "https://api.deepseek.com/v1",
            _ => "https://api.openai.com/v1"
        };
    }

    private static string AppendEndpointPath(string baseUrl, string path, string terminalPath)
    {
        var trimmed = baseUrl.TrimEnd('/');
        if (trimmed.EndsWith($"/{terminalPath}", StringComparison.OrdinalIgnoreCase))
        {
            return trimmed;
        }

        return $"{trimmed}/{path.TrimStart('/')}";
    }

    private static string CreateModelBindingKey(string providerId, string modelId)
    {
        return $"{providerId}:{modelId}";
    }

    private static AiProviderConfigDto MapAiProvider(AiProviderConfig provider)
    {
        return new AiProviderConfigDto
        {
            Id = provider.Id,
            Name = provider.Name,
            DisplayName = provider.DisplayName,
            ProviderType = provider.ProviderType,
            BaseUrl = provider.BaseUrl,
            HasApiKey = !string.IsNullOrEmpty(provider.ApiKey),
            AuthType = provider.AuthType,
            IsBuiltIn = provider.IsBuiltIn,
            IsActive = provider.IsActive,
            SupportsModelDiscovery = provider.SupportsModelDiscovery,
            ModelsEndpoint = provider.ModelsEndpoint,
            DefaultModelId = provider.DefaultModelId,
            SystemProxyUrl = provider.SystemProxyUrl,
            OAuthConfigJson = provider.OAuthConfigJson,
            ChannelConfigJson = provider.ChannelConfigJson,
            AccountsJson = provider.AccountsJson,
            RequestOverridesJson = provider.RequestOverridesJson,
            IconUrl = provider.IconUrl,
            Description = provider.Description,
            SortOrder = provider.SortOrder,
            CreatedAt = provider.CreatedAt
        };
    }

    private static AiModelConfigDto MapAiModel(AiModelConfig model, string? providerName)
    {
        return new AiModelConfigDto
        {
            Id = model.Id,
            ProviderId = model.ProviderId,
            ProviderName = providerName,
            ModelId = model.ModelId,
            Name = model.Name,
            DisplayName = model.DisplayName,
            ModelType = model.ModelType,
            ProviderType = model.ProviderType,
            ContextWindow = model.ContextWindow,
            MaxOutputTokens = model.MaxOutputTokens,
            InputTokenPrice = model.InputTokenPrice,
            OutputTokenPrice = model.OutputTokenPrice,
            CacheHitTokenPrice = model.CacheHitTokenPrice,
            CacheCreationTokenPrice = model.CacheCreationTokenPrice,
            SupportsThinking = model.SupportsThinking,
            SupportsVision = model.SupportsVision,
            SupportsTools = model.SupportsTools,
            SupportsJsonMode = model.SupportsJsonMode,
            IsDefault = model.IsDefault,
            IsActive = model.IsActive,
            CapabilitiesJson = model.CapabilitiesJson,
            ThinkingConfigJson = model.ThinkingConfigJson,
            RequestOverridesJson = model.RequestOverridesJson,
            TagsJson = model.TagsJson,
            Description = model.Description,
            SortOrder = model.SortOrder,
            CreatedAt = model.CreatedAt
        };
    }

    private static IReadOnlyList<DiscoveredAiModel> EnumerateModelElements(JsonElement root)
    {
        var source = root;
        if (root.ValueKind == JsonValueKind.Object &&
            root.TryGetProperty("data", out var data) &&
            data.ValueKind == JsonValueKind.Array)
        {
            source = data;
        }

        if (source.ValueKind != JsonValueKind.Array)
        {
            return [];
        }

        var models = new List<DiscoveredAiModel>();
        var seen = new HashSet<string>(StringComparer.OrdinalIgnoreCase);

        foreach (var item in source.EnumerateArray())
        {
            var model = ParseDiscoveredAiModel(item);
            if (model == null || !seen.Add(model.ModelId))
            {
                continue;
            }

            models.Add(model);
        }

        return models;
    }

    private static DiscoveredAiModel? ParseDiscoveredAiModel(JsonElement item)
    {
        if (item.ValueKind == JsonValueKind.String)
        {
            var stringModelId = item.GetString();
            return string.IsNullOrWhiteSpace(stringModelId)
                ? null
                : new DiscoveredAiModel(stringModelId, null, null, null, null, null, null);
        }

        if (item.ValueKind != JsonValueKind.Object)
        {
            return null;
        }

        var modelId = ReadOptionalString(item, "id");
        if (string.IsNullOrWhiteSpace(modelId))
        {
            return null;
        }

        return new DiscoveredAiModel(
            modelId,
            ReadOptionalString(item, "name"),
            ReadOptionalString(item, "displayName"),
            ReadOptionalDecimal(item, "inputPrice") ?? ReadOptionalDecimal(item, "promptPrice"),
            ReadOptionalDecimal(item, "outputPrice") ?? ReadOptionalDecimal(item, "completionPrice"),
            ReadOptionalDecimal(item, "cacheHitPrice"),
            ReadOptionalDecimal(item, "cacheCreationPrice"));
    }

    private static string? ReadOptionalString(JsonElement item, string propertyName)
    {
        return item.TryGetProperty(propertyName, out var value) && value.ValueKind == JsonValueKind.String
            ? value.GetString()
            : null;
    }

    private static decimal? ReadOptionalDecimal(JsonElement item, string propertyName)
    {
        if (!item.TryGetProperty(propertyName, out var value))
        {
            return null;
        }

        return value.ValueKind switch
        {
            JsonValueKind.Number when value.TryGetDecimal(out var decimalValue) => decimalValue,
            JsonValueKind.String when decimal.TryParse(
                value.GetString(),
                NumberStyles.Number,
                CultureInfo.InvariantCulture,
                out var stringValue) => stringValue,
            _ => null
        };
    }

    private sealed record DiscoveredAiModel(
        string ModelId,
        string? Name,
        string? DisplayName,
        decimal? InputTokenPrice,
        decimal? OutputTokenPrice,
        decimal? CacheHitTokenPrice,
        decimal? CacheCreationTokenPrice);

    private static string? FindSkillMd(string directory)
    {
        var skillMd = Path.Combine(directory, "SKILL.md");
        if (File.Exists(skillMd)) return skillMd;
        foreach (var subDir in Directory.GetDirectories(directory))
        {
            skillMd = Path.Combine(subDir, "SKILL.md");
            if (File.Exists(skillMd)) return skillMd;
        }
        return null;
    }

    private static (Dictionary<string, object?> frontmatter, string body) ParseSkillMd(string content)
    {
        var frontmatter = new Dictionary<string, object?>();
        var body = content;
        if (content.StartsWith("---"))
        {
            var endIndex = content.IndexOf("---", 3);
            if (endIndex > 0)
            {
                var yamlContent = content[3..endIndex].Trim();
                body = content[(endIndex + 3)..].Trim();
                try
                {
                    var deserializer = new DeserializerBuilder().WithNamingConvention(HyphenatedNamingConvention.Instance).Build();
                    frontmatter = deserializer.Deserialize<Dictionary<string, object?>>(yamlContent) ?? new();
                }
                catch { }
            }
        }
        return (frontmatter, body);
    }

    private static List<SkillFileInfo> ListDirectoryFiles(string directory)
    {
        var files = new List<SkillFileInfo>();
        if (!Directory.Exists(directory)) return files;
        foreach (var file in Directory.GetFiles(directory, "*", SearchOption.AllDirectories))
        {
            var info = new FileInfo(file);
            files.Add(new SkillFileInfo { FileName = info.Name, RelativePath = Path.GetRelativePath(directory, file), Size = info.Length, LastModified = info.LastWriteTimeUtc });
        }
        return files;
    }

    private static long CalculateDirectorySize(string directory) =>
        !Directory.Exists(directory) ? 0 : Directory.GetFiles(directory, "*", SearchOption.AllDirectories).Sum(f => new FileInfo(f).Length);
}
