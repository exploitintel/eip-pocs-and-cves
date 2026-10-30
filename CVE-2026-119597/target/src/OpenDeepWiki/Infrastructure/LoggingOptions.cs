namespace OpenDeepWiki.Infrastructure;

/// <summary>
/// Configuration options for Serilog logging.
/// </summary>
public class LoggingOptions
{
    /// <summary>
    /// Configuration section name in appsettings.json.
    /// </summary>
    public const string SectionName = "Logging";

    /// <summary>
    /// Directory path for log files. Defaults to "logs".
    /// </summary>
    public string LogDirectory { get; set; } = "logs";

    /// <summary>
    /// Maximum number of log files to retain. Defaults to 31 (one month of daily logs).
    /// </summary>
    public int RetainedFileCountLimit { get; set; } = 31;

    /// <summary>
    /// Minimum log level for general logging. Defaults to "Information".
    /// </summary>
    public string MinimumLevel { get; set; } = "Information";
}
