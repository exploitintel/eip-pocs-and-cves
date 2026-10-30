namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// Represents a grep search result with context.
/// </summary>
public class GrepResult
{
    /// <summary>
    /// The relative file path where the match was found.
    /// </summary>
    public string FilePath { get; set; } = string.Empty;

    /// <summary>
    /// The line number (1-based) where the match was found.
    /// </summary>
    public int LineNumber { get; set; }

    /// <summary>
    /// The content of the matching line (trimmed).
    /// </summary>
    public string LineContent { get; set; } = string.Empty;

    /// <summary>
    /// The context around the match including surrounding lines.
    /// </summary>
    public string Context { get; set; } = string.Empty;
}