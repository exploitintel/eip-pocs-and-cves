using OpenDeepWiki.Agents;

namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Configuration options for the Wiki Generator.
/// Supports separate model configurations for catalog and content generation.
/// </summary>
public class WikiGeneratorOptions
{
    /// <summary>
    /// Configuration section name in appsettings.json.
    /// </summary>
    public const string SectionName = "WikiGenerator";

    /// <summary>
    /// The AI model to use for catalog structure generation.
    /// Default: gpt-4o-mini (faster and cheaper for structural tasks).
    /// </summary>
    public string CatalogModel { get; set; } = "gpt-5-mini";

    /// <summary>
    /// The AI provider bound to catalog and mind map generation.
    /// </summary>
    public string? CatalogProviderId { get; set; }

    /// <summary>
    /// The AI model to use for document content generation.
    /// Default: gpt-4o (better quality for content generation).
    /// </summary>
    public string ContentModel { get; set; } = "gpt-5.2";

    /// <summary>
    /// The AI provider bound to content generation.
    /// </summary>
    public string? ContentProviderId { get; set; }

    /// <summary>
    /// The directory containing prompt template files.
    /// Default: prompts
    /// </summary>
    public string PromptsDirectory { get; set; } = "prompts";

    /// <summary>
    /// Maximum retry attempts for AI generation operations.
    /// Default: 3
    /// </summary>
    public int MaxRetryAttempts { get; set; } = 3;

    /// <summary>
    /// Delay between retry attempts in milliseconds.
    /// Default: 1000
    /// </summary>
    public int RetryDelayMs { get; set; } = 1000;

    /// <summary>
    /// Maximum number of parallel document generation tasks.
    /// Default: 5. Configure through system settings.
    /// </summary>
    public int ParallelCount { get; set; } = 5;

    public const int DefaultMaxConcurrentGenerations = 1;
    public const int MaxConcurrentGenerationsLimit = 32;
    public const int DefaultGenerationLeaseTimeoutSeconds = 180;
    public const int DefaultGenerationHeartbeatIntervalSeconds = 30;

    /// <summary>
    /// Maximum number of repository wiki generations that may run at the same
    /// time across the entire cluster. Default: 1.
    /// </summary>
    public int MaxConcurrentGenerations { get; set; } = DefaultMaxConcurrentGenerations;

    /// <summary>
    /// Seconds without a heartbeat before a bound lock or slot is treated as stale.
    /// </summary>
    public int GenerationLeaseTimeoutSeconds { get; set; } = DefaultGenerationLeaseTimeoutSeconds;

    /// <summary>
    /// Interval for refreshing lock and slot heartbeats while generation is running.
    /// </summary>
    public int GenerationHeartbeatIntervalSeconds { get; set; } = DefaultGenerationHeartbeatIntervalSeconds;

    public int GetMaxConcurrentGenerations()
    {
        return Math.Clamp(MaxConcurrentGenerations, 1, MaxConcurrentGenerationsLimit);
    }

    public int GetGenerationLeaseTimeoutSeconds()
    {
        return Math.Max(60, GenerationLeaseTimeoutSeconds);
    }

    public int GetGenerationHeartbeatIntervalSeconds()
    {
        return Math.Clamp(GenerationHeartbeatIntervalSeconds, 5, 60);
    }

    /// <summary>
    /// Maximum output tokens per AI response.
    /// This bounds a SINGLE model response, not the whole document. The content
    /// generator builds long documents incrementally across multiple tool calls
    /// (WriteDoc + AppendDoc), so the final page can far exceed this limit.
    /// Default: 32000
    /// </summary>
    public int MaxOutputTokens { get; set; } = 32000;

    /// <summary>
    /// Maximum AppendDoc calls allowed for a single generated document.
    /// This prevents one page from monopolizing full regeneration with an
    /// unbounded write/append loop. Set to 0 or less to disable the budget.
    /// </summary>
    public int MaxDocumentAppendOperations { get; set; } = 4;

    /// <summary>
    /// Maximum source-discovery tool calls allowed for a single generated document.
    /// This caps ReadFile/ListFiles/Grep loops before the model must write and finish.
    /// Set to 0 or less to disable the budget.
    /// </summary>
    public int MaxDocumentSourceToolCalls { get; set; } = 6;

    /// <summary>
    /// Maximum total tool calls allowed for a single generated document before
    /// the service may stop the agent early once document content has persisted.
    /// </summary>
    public int MaxDocumentToolCalls { get; set; } = 14;

    /// <summary>
    /// Maximum source-discovery tool calls allowed during catalog generation.
    /// The catalog prompt already includes the collected directory tree and README,
    /// so source tools should be used only for quick confirmation.
    /// </summary>
    public int MaxCatalogSourceToolCalls { get; set; } = 4;

    /// <summary>
    /// Timeout in minutes for document generation tasks.
    /// Default: 30 minutes
    /// </summary>
    public int DocumentGenerationTimeoutMinutes { get; set; } = 60;

    /// <summary>
    /// Timeout in minutes for translation tasks.
    /// Default: 20 minutes
    /// </summary>
    public int TranslationTimeoutMinutes { get; set; } = 45;

    /// <summary>
    /// Timeout in minutes for catalog title translation tasks.
    /// Default: 2 minutes
    /// </summary>
    public int TitleTranslationTimeoutMinutes { get; set; } = 2;

    /// <summary>
    /// Maximum length for README content before truncation.
    /// Default: 4000 characters
    /// </summary>
    public int ReadmeMaxLength { get; set; } = 4000;

    /// <summary>
    /// Maximum depth for directory tree traversal.
    /// Default: 2 levels
    /// </summary>
    public int DirectoryTreeMaxDepth { get; set; } = 2;

    /// <summary>
    /// Maximum depth for file listing inside the directory tree.
    /// </summary>
    public int FileListMaxDepth { get; set; } = 1;

    /// <summary>
    /// Maximum number of tree nodes emitted into the catalog prompt.
    /// </summary>
    public int MaxTreeNodes { get; set; } = 1200;

    /// <summary>
    /// Maximum number of files shown in each directory.
    /// </summary>
    public int MaxFilesPerDirectory { get; set; } = 20;

    /// <summary>
    /// Maximum total number of files shown in the directory tree.
    /// </summary>
    public int MaxTotalTreeFiles { get; set; } = 500;

    /// <summary>
    /// Whether Auto scan profiling may use an AI model. Rules are always the default and fallback.
    /// </summary>
    public bool EnableAiScanProfile { get; set; } = false;

    /// <summary>
    /// Comma-separated list of language codes for multi-language wiki generation.
    /// Example: "en,zh,ja,ko,es,fr,de,pt-br". Can be configured via WIKI_LANGUAGES environment variable.
    /// The primary language selected by user will be generated first, then translated to other languages.
    /// </summary>
    public string? Languages { get; set; } = WikiLanguageNames.DefaultLanguages;

    /// <summary>
    /// The AI model to use for translation.
    /// Default: uses ContentModel if not specified.
    /// </summary>
    public string? TranslationModel { get; set; }

    /// <summary>
    /// The AI provider bound to translation.
    /// </summary>
    public string? TranslationProviderId { get; set; }

    /// <summary>
    /// Gets the list of target languages for translation (excluding the primary language).
    /// </summary>
    /// <param name="primaryLanguage">The primary language code to exclude.</param>
    /// <returns>List of language codes to translate to.</returns>
    public List<string> GetTranslationLanguages(string primaryLanguage)
    {
        if (string.IsNullOrWhiteSpace(Languages))
        {
            return new List<string>();
        }

        return Languages
            .Split(',', StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries)
            .Select(l => l.ToLowerInvariant())
            .Where(l => !string.Equals(l, primaryLanguage, StringComparison.OrdinalIgnoreCase))
            .Distinct()
            .ToList();
    }

    /// <summary>
    /// Gets the model to use for translation.
    /// Falls back to ContentModel if not specified.
    /// </summary>
    public string GetTranslationModel()
    {
        return string.IsNullOrWhiteSpace(TranslationModel) ? ContentModel : TranslationModel;
    }
    /// <summary>
    /// Whether to throw an exception when some documents fail to generate.
    /// When set to false, the generator will log warnings but continue processing.
    /// Default: false (continue on partial failure)
    /// </summary>
    public bool ThrowOnPartialFailure { get; set; } = false;
}
