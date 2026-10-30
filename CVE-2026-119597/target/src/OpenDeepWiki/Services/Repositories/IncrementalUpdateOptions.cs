namespace OpenDeepWiki.Services.Repositories;

/// <summary>
/// Configuration options for incremental repository updates.
/// </summary>
public class IncrementalUpdateOptions
{
    /// <summary>
    /// Configuration section name.
    /// </summary>
    public const string SectionName = "IncrementalUpdate";

    /// <summary>
    /// Whether automatic scheduled scans are enabled.
    /// Manual tasks are still processed when this is disabled.
    /// </summary>
    public bool Enabled { get; set; } = true;

    /// <summary>
    /// Polling interval for the background worker in seconds.
    /// </summary>
    public int PollingIntervalSeconds { get; set; } = 60;

    /// <summary>
    /// Default interval between scheduled update checks in minutes.
    /// </summary>
    public int DefaultUpdateIntervalMinutes { get; set; } = 60;

    /// <summary>
    /// Minimum allowed scheduled update interval in minutes.
    /// </summary>
    public int MinUpdateIntervalMinutes { get; set; } = 5;

    /// <summary>
    /// Maximum repositories scanned in one polling cycle.
    /// </summary>
    public int MaxRepositoriesPerPoll { get; set; } = 10;

    /// <summary>
    /// Maximum workspace preparation retry attempts.
    /// </summary>
    public int MaxRetryAttempts { get; set; } = 3;

    /// <summary>
    /// Base retry delay in milliseconds.
    /// </summary>
    public int RetryBaseDelayMs { get; set; } = 1000;

    /// <summary>
    /// Priority assigned to manually triggered tasks.
    /// </summary>
    public int ManualTriggerPriority { get; set; } = 100;
}
