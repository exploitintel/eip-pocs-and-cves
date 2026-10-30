using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Admin;

public static class SystemSettingDefaults
{
    public const string WikiCatalogProviderId = "WIKI_CATALOG_PROVIDER_ID";
    public const string WikiCatalogModelId = "WIKI_CATALOG_MODEL_ID";
    public const string WikiContentProviderId = "WIKI_CONTENT_PROVIDER_ID";
    public const string WikiContentModelId = "WIKI_CONTENT_MODEL_ID";
    public const string WikiTranslationProviderId = "WIKI_TRANSLATION_PROVIDER_ID";
    public const string WikiTranslationModelId = "WIKI_TRANSLATION_MODEL_ID";
    public const string GraphifyProviderId = "GRAPHIFY_PROVIDER_ID";
    public const string GraphifyModelId = "GRAPHIFY_MODEL_ID";

    public static readonly (string Key, string Category, string Description)[] WikiGeneratorDefaults =
    [
        (WikiCatalogProviderId, "ai", "AI provider used for catalog and mind map generation"),
        (WikiCatalogModelId, "ai", "AI model used for catalog and mind map generation"),
        (WikiContentProviderId, "ai", "AI provider used for content generation"),
        (WikiContentModelId, "ai", "AI model used for content generation"),
        (WikiTranslationProviderId, "ai", "AI provider used for translation"),
        (WikiTranslationModelId, "ai", "AI model used for translation"),
        (GraphifyProviderId, "ai", "AI provider used for Graphify community labels"),
        (GraphifyModelId, "ai", "AI model used for Graphify community labels"),
        ("WIKI_LANGUAGES", "ai", "Supported languages (comma-separated)"),
        ("WIKI_PARALLEL_COUNT", "ai", "Number of parallel document generation tasks"),
        ("WIKI_MAX_CONCURRENT_GENERATIONS", "ai", "Maximum number of repository wiki generations running at the same time across the cluster"),
        ("WIKI_MAX_OUTPUT_TOKENS", "ai", "Maximum output token count"),
        ("WIKI_MAX_DOCUMENT_APPEND_OPERATIONS", "ai", "Maximum AppendDoc calls per generated document"),
        ("WIKI_MAX_DOCUMENT_SOURCE_TOOL_CALLS", "ai", "Maximum ReadFile/ListFiles/Grep calls per generated document"),
        ("WIKI_MAX_DOCUMENT_TOOL_CALLS", "ai", "Maximum total tool calls per generated document before early completion"),
        ("WIKI_MAX_CATALOG_SOURCE_TOOL_CALLS", "ai", "Maximum ReadFile/ListFiles/Grep calls during catalog generation"),
        ("WIKI_DOCUMENT_GENERATION_TIMEOUT_MINUTES", "ai", "Document generation timeout (minutes)"),
        ("WIKI_TRANSLATION_TIMEOUT_MINUTES", "ai", "Translation timeout (minutes)"),
        ("WIKI_TITLE_TRANSLATION_TIMEOUT_MINUTES", "ai", "Title translation timeout (minutes)"),
        ("WIKI_README_MAX_LENGTH", "ai", "Maximum README content length"),
        ("WIKI_DIRECTORY_TREE_MAX_DEPTH", "ai", "Maximum directory tree depth"),
        ("WIKI_FILE_LIST_MAX_DEPTH", "ai", "Maximum file list depth"),
        ("WIKI_MAX_TREE_NODES", "ai", "Maximum directory tree nodes"),
        ("WIKI_MAX_FILES_PER_DIRECTORY", "ai", "Maximum files shown per directory"),
        ("WIKI_MAX_TOTAL_TREE_FILES", "ai", "Maximum total files shown in directory tree"),
        ("WIKI_ENABLE_AI_SCAN_PROFILE", "ai", "Enable AI-assisted repository scan profiling"),
        ("WIKI_MAX_RETRY_ATTEMPTS", "ai", "Maximum retry attempts"),
        ("WIKI_RETRY_DELAY_MS", "ai", "Retry delay (milliseconds)"),
        ("WIKI_PROMPTS_DIRECTORY", "ai", "Prompt templates directory")
    ];

    public static async Task InitializeDefaultsAsync(IConfiguration configuration, IContext context)
    {
        var existingSettings = await context.SystemSettings
            .Where(s => !s.IsDeleted)
            .ToListAsync();

        var existingByKey = existingSettings.ToDictionary(s => s.Key);
        var wikiOptionDefaults = new WikiGeneratorOptions();
        configuration.GetSection(WikiGeneratorOptions.SectionName).Bind(wikiOptionDefaults);
        WikiGeneratorOptionsConfigurator.Apply(wikiOptionDefaults, configuration);

        var settingsToAdd = new List<SystemSetting>();
        var hasChanges = false;

        foreach (var (key, category, description) in WikiGeneratorDefaults)
        {
            if (existingByKey.TryGetValue(key, out var existing))
            {
                if (existing.Description != description || existing.Category != category)
                {
                    existing.Description = description;
                    existing.Category = category;
                    hasChanges = true;
                }

                continue;
            }

            settingsToAdd.Add(new SystemSetting
            {
                Id = Guid.NewGuid().ToString(),
                Key = key,
                Value = GetOptionDefaultValue(wikiOptionDefaults, key) ?? string.Empty,
                Description = description,
                Category = category,
                CreatedAt = DateTime.UtcNow
            });
        }

        if (settingsToAdd.Count > 0)
        {
            context.SystemSettings.AddRange(settingsToAdd);
            hasChanges = true;
        }

        if (hasChanges)
        {
            await context.SaveChangesAsync();
        }
    }

    public static async Task ApplyToWikiGeneratorOptions(
        WikiGeneratorOptions options,
        IAdminSettingsService settingsService)
    {
        foreach (var def in WikiGeneratorDefaults)
        {
            var setting = await settingsService.GetSettingByKeyAsync(def.Key);
            if (setting?.Value != null)
            {
                ApplySettingToOption(options, def.Key, setting.Value);
            }
        }
    }

    private static string? GetOptionDefaultValue(WikiGeneratorOptions options, string key)
    {
        return key switch
        {
            WikiCatalogProviderId => options.CatalogProviderId,
            WikiCatalogModelId => options.CatalogModel,
            WikiContentProviderId => options.ContentProviderId,
            WikiContentModelId => options.ContentModel,
            WikiTranslationProviderId => options.TranslationProviderId,
            WikiTranslationModelId => options.TranslationModel,
            "WIKI_LANGUAGES" => options.Languages,
            "WIKI_PARALLEL_COUNT" => options.ParallelCount.ToString(),
            "WIKI_MAX_CONCURRENT_GENERATIONS" => options.GetMaxConcurrentGenerations().ToString(),
            "WIKI_MAX_OUTPUT_TOKENS" => options.MaxOutputTokens.ToString(),
            "WIKI_MAX_DOCUMENT_APPEND_OPERATIONS" => options.MaxDocumentAppendOperations.ToString(),
            "WIKI_MAX_DOCUMENT_SOURCE_TOOL_CALLS" => options.MaxDocumentSourceToolCalls.ToString(),
            "WIKI_MAX_DOCUMENT_TOOL_CALLS" => options.MaxDocumentToolCalls.ToString(),
            "WIKI_MAX_CATALOG_SOURCE_TOOL_CALLS" => options.MaxCatalogSourceToolCalls.ToString(),
            "WIKI_DOCUMENT_GENERATION_TIMEOUT_MINUTES" => options.DocumentGenerationTimeoutMinutes.ToString(),
            "WIKI_TRANSLATION_TIMEOUT_MINUTES" => options.TranslationTimeoutMinutes.ToString(),
            "WIKI_TITLE_TRANSLATION_TIMEOUT_MINUTES" => options.TitleTranslationTimeoutMinutes.ToString(),
            "WIKI_README_MAX_LENGTH" => options.ReadmeMaxLength.ToString(),
            "WIKI_DIRECTORY_TREE_MAX_DEPTH" => options.DirectoryTreeMaxDepth.ToString(),
            "WIKI_FILE_LIST_MAX_DEPTH" => options.FileListMaxDepth.ToString(),
            "WIKI_MAX_TREE_NODES" => options.MaxTreeNodes.ToString(),
            "WIKI_MAX_FILES_PER_DIRECTORY" => options.MaxFilesPerDirectory.ToString(),
            "WIKI_MAX_TOTAL_TREE_FILES" => options.MaxTotalTreeFiles.ToString(),
            "WIKI_ENABLE_AI_SCAN_PROFILE" => options.EnableAiScanProfile.ToString(),
            "WIKI_MAX_RETRY_ATTEMPTS" => options.MaxRetryAttempts.ToString(),
            "WIKI_RETRY_DELAY_MS" => options.RetryDelayMs.ToString(),
            "WIKI_PROMPTS_DIRECTORY" => options.PromptsDirectory,
            _ => null
        };
    }

    public static void ApplySettingToOption(WikiGeneratorOptions options, string key, string value)
    {
        switch (key)
        {
            case WikiCatalogProviderId:
                options.CatalogProviderId = value;
                break;
            case WikiCatalogModelId:
                options.CatalogModel = value;
                break;
            case WikiContentProviderId:
                options.ContentProviderId = value;
                break;
            case WikiContentModelId:
                options.ContentModel = value;
                break;
            case WikiTranslationProviderId:
                options.TranslationProviderId = value;
                break;
            case WikiTranslationModelId:
                options.TranslationModel = value;
                break;
            case "WIKI_LANGUAGES":
                options.Languages = value;
                break;
            case "WIKI_PARALLEL_COUNT" when int.TryParse(value, out var parallelCount):
                options.ParallelCount = parallelCount;
                break;
            case "WIKI_MAX_CONCURRENT_GENERATIONS" when int.TryParse(value, out var maxConcurrentGenerations):
                options.MaxConcurrentGenerations = maxConcurrentGenerations;
                break;
            case "WIKI_MAX_OUTPUT_TOKENS" when int.TryParse(value, out var maxTokens):
                options.MaxOutputTokens = maxTokens;
                break;
            case "WIKI_MAX_DOCUMENT_APPEND_OPERATIONS" when int.TryParse(value, out var maxDocumentAppendOperations):
                options.MaxDocumentAppendOperations = maxDocumentAppendOperations;
                break;
            case "WIKI_MAX_DOCUMENT_SOURCE_TOOL_CALLS" when int.TryParse(value, out var maxDocumentSourceToolCalls):
                options.MaxDocumentSourceToolCalls = maxDocumentSourceToolCalls;
                break;
            case "WIKI_MAX_DOCUMENT_TOOL_CALLS" when int.TryParse(value, out var maxDocumentToolCalls):
                options.MaxDocumentToolCalls = maxDocumentToolCalls;
                break;
            case "WIKI_MAX_CATALOG_SOURCE_TOOL_CALLS" when int.TryParse(value, out var maxCatalogSourceToolCalls):
                options.MaxCatalogSourceToolCalls = maxCatalogSourceToolCalls;
                break;
            case "WIKI_DOCUMENT_GENERATION_TIMEOUT_MINUTES" when int.TryParse(value, out var docTimeout):
                options.DocumentGenerationTimeoutMinutes = docTimeout;
                break;
            case "WIKI_TRANSLATION_TIMEOUT_MINUTES" when int.TryParse(value, out var transTimeout):
                options.TranslationTimeoutMinutes = transTimeout;
                break;
            case "WIKI_TITLE_TRANSLATION_TIMEOUT_MINUTES" when int.TryParse(value, out var titleTimeout):
                options.TitleTranslationTimeoutMinutes = titleTimeout;
                break;
            case "WIKI_README_MAX_LENGTH" when int.TryParse(value, out var readmeLength):
                options.ReadmeMaxLength = readmeLength;
                break;
            case "WIKI_DIRECTORY_TREE_MAX_DEPTH" when int.TryParse(value, out var treeDepth):
                options.DirectoryTreeMaxDepth = treeDepth;
                break;
            case "WIKI_FILE_LIST_MAX_DEPTH" when int.TryParse(value, out var fileDepth):
                options.FileListMaxDepth = fileDepth;
                break;
            case "WIKI_MAX_TREE_NODES" when int.TryParse(value, out var maxTreeNodes):
                options.MaxTreeNodes = maxTreeNodes;
                break;
            case "WIKI_MAX_FILES_PER_DIRECTORY" when int.TryParse(value, out var maxFilesPerDirectory):
                options.MaxFilesPerDirectory = maxFilesPerDirectory;
                break;
            case "WIKI_MAX_TOTAL_TREE_FILES" when int.TryParse(value, out var maxTotalTreeFiles):
                options.MaxTotalTreeFiles = maxTotalTreeFiles;
                break;
            case "WIKI_ENABLE_AI_SCAN_PROFILE" when bool.TryParse(value, out var enableAiScanProfile):
                options.EnableAiScanProfile = enableAiScanProfile;
                break;
            case "WIKI_MAX_RETRY_ATTEMPTS" when int.TryParse(value, out var retryAttempts):
                options.MaxRetryAttempts = retryAttempts;
                break;
            case "WIKI_RETRY_DELAY_MS" when int.TryParse(value, out var retryDelay):
                options.RetryDelayMs = retryDelay;
                break;
            case "WIKI_PROMPTS_DIRECTORY":
                options.PromptsDirectory = value;
                break;
        }
    }
}
