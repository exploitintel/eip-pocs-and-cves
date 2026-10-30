using System.Text.RegularExpressions;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// Represents a single .gitignore rule with its pattern and properties.
/// </summary>
public class GitIgnoreRule
{
    /// <summary>
    /// The compiled regex pattern for matching.
    /// </summary>
    public Regex Pattern { get; set; } = null!;

    /// <summary>
    /// Whether this is a negation rule (starts with !).
    /// </summary>
    public bool IsNegation { get; set; }

    /// <summary>
    /// Whether this rule only matches directories (ends with /).
    /// </summary>
    public bool DirectoryOnly { get; set; }

    /// <summary>
    /// The original pattern string for debugging.
    /// </summary>
    public string OriginalPattern { get; set; } = string.Empty;
}