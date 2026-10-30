using System.Text;
using System.Text.RegularExpressions;

namespace OpenDeepWiki.Agents.Tools;

public sealed class RepositoryFileFilter
{
    private readonly string _workingDirectory;
    private readonly List<GitIgnoreRule> _gitIgnoreRules;

    public RepositoryFileFilter(string workingDirectory)
    {
        _workingDirectory = Path.GetFullPath(workingDirectory);
        _gitIgnoreRules = ParseGitIgnore(_workingDirectory);
    }

    public bool IsIgnored(string fullPath)
    {
        var relativePath = GetRelativePath(fullPath);
        return IsHiddenPath(relativePath) || IsIgnoredByGitIgnore(relativePath);
    }

    public bool IsLowValueFile(string fullPath)
    {
        var fileName = Path.GetFileName(fullPath);
        if (fileName.EndsWith(".min.js", StringComparison.OrdinalIgnoreCase) ||
            fileName.EndsWith(".map", StringComparison.OrdinalIgnoreCase) ||
            fileName.EndsWith(".lock", StringComparison.OrdinalIgnoreCase) ||
            fileName.EndsWith(".snap", StringComparison.OrdinalIgnoreCase) ||
            fileName.EndsWith(".snapshot", StringComparison.OrdinalIgnoreCase))
        {
            return true;
        }

        return IsBinaryFile(fullPath);
    }

    public string GetRelativePath(string fullPath)
    {
        return Path.GetRelativePath(_workingDirectory, fullPath).Replace('\\', '/');
    }

    public static bool IsBinaryFile(string filePath)
    {
        var binaryExtensions = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            ".exe", ".dll", ".so", ".dylib", ".bin", ".obj", ".o",
            ".png", ".jpg", ".jpeg", ".gif", ".bmp", ".ico", ".svg",
            ".pdf", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx",
            ".zip", ".tar", ".gz", ".tgz", ".rar", ".7z",
            ".mp3", ".mp4", ".avi", ".mov", ".wav",
            ".ttf", ".otf", ".woff", ".woff2", ".eot",
            ".db", ".sqlite", ".mdb"
        };

        return binaryExtensions.Contains(Path.GetExtension(filePath));
    }

    public static List<GitIgnoreRule> ParseGitIgnore(string workingDirectory)
    {
        var rules = new List<GitIgnoreRule>();
        var normalizedRoot = Path.GetFullPath(workingDirectory);
        IEnumerable<string> gitignorePaths;

        try
        {
            gitignorePaths = Directory.EnumerateFiles(normalizedRoot, ".gitignore", SearchOption.AllDirectories)
                .OrderBy(path => Path.GetRelativePath(normalizedRoot, path).Replace('\\', '/'))
                .ToArray();
        }
        catch
        {
            gitignorePaths = File.Exists(Path.Combine(normalizedRoot, ".gitignore"))
                ? new[] { Path.Combine(normalizedRoot, ".gitignore") }
                : Array.Empty<string>();
        }

        foreach (var gitignorePath in gitignorePaths)
        {
            var baseDirectory = Path.GetDirectoryName(gitignorePath) ?? normalizedRoot;
            var baseRelativePath = Path.GetRelativePath(normalizedRoot, baseDirectory)
                .Replace('\\', '/');
            if (baseRelativePath == ".")
            {
                baseRelativePath = string.Empty;
            }

            try
            {
                foreach (var line in File.ReadAllLines(gitignorePath))
                {
                    var trimmedLine = line.Trim();
                    if (string.IsNullOrEmpty(trimmedLine) || trimmedLine.StartsWith('#'))
                    {
                        continue;
                    }

                    var rule = ParseGitIgnorePattern(trimmedLine, baseRelativePath);
                    if (rule != null)
                    {
                        rules.Add(rule);
                    }
                }
            }
            catch
            {
                continue;
            }
        }

        return rules;
    }

    private bool IsHiddenPath(string relativePath)
    {
        var parts = relativePath.Split('/', '\\');
        return parts.Any(p => p.StartsWith('.') && p != ".");
    }

    private bool IsIgnoredByGitIgnore(string relativePath)
    {
        if (_gitIgnoreRules.Count == 0)
        {
            return false;
        }

        var normalizedPath = relativePath.Replace('\\', '/');
        var isIgnored = false;
        foreach (var rule in _gitIgnoreRules)
        {
            if (rule.Pattern.IsMatch(normalizedPath))
            {
                isIgnored = !rule.IsNegation;
            }
        }

        return isIgnored;
    }

    private static GitIgnoreRule? ParseGitIgnorePattern(string pattern, string baseRelativePath)
    {
        var rule = new GitIgnoreRule
        {
            OriginalPattern = pattern,
            IsNegation = false,
            DirectoryOnly = false
        };

        var workingPattern = pattern;
        if (workingPattern.StartsWith('!'))
        {
            rule.IsNegation = true;
            workingPattern = workingPattern[1..];
        }

        if (workingPattern.EndsWith('/'))
        {
            rule.DirectoryOnly = true;
            workingPattern = workingPattern.TrimEnd('/');
        }

        var anchoredToGitIgnoreDirectory = workingPattern.StartsWith('/');
        if (anchoredToGitIgnoreDirectory)
        {
            workingPattern = workingPattern[1..];
        }

        try
        {
            rule.Pattern = new Regex(
                GitIgnorePatternToRegex(workingPattern, anchoredToGitIgnoreDirectory, baseRelativePath),
                RegexOptions.IgnoreCase | RegexOptions.Compiled);
            return rule;
        }
        catch
        {
            return null;
        }
    }

    private static string GitIgnorePatternToRegex(string pattern, bool anchoredToGitIgnoreDirectory, string baseRelativePath)
    {
        var normalizedBase = baseRelativePath.Trim('/');
        var sb = new StringBuilder();
        var containsSlash = pattern.Contains('/');

        if (string.IsNullOrEmpty(normalizedBase))
        {
            sb.Append(anchoredToGitIgnoreDirectory || containsSlash ? '^' : "(^|/)");
        }
        else if (anchoredToGitIgnoreDirectory || containsSlash)
        {
            sb.Append('^');
            AppendGitIgnorePattern(sb, normalizedBase);
            sb.Append('/');
        }
        else
        {
            sb.Append('^');
            AppendGitIgnorePattern(sb, normalizedBase);
            sb.Append("(/.*/|/)");
        }

        AppendGitIgnorePattern(sb, pattern);
        sb.Append("(/.*)?$");
        return sb.ToString();
    }

    private static void AppendGitIgnorePattern(StringBuilder sb, string pattern)
    {
        for (var i = 0; i < pattern.Length; i++)
        {
            var c = pattern[i];
            switch (c)
            {
                case '*':
                    if (i + 1 < pattern.Length && pattern[i + 1] == '*')
                    {
                        if (i + 2 < pattern.Length && pattern[i + 2] == '/')
                        {
                            sb.Append("(.*/)?");
                            i += 2;
                        }
                        else
                        {
                            sb.Append(".*");
                            i++;
                        }
                    }
                    else
                    {
                        sb.Append("[^/]*");
                    }
                    break;
                case '?':
                    sb.Append("[^/]");
                    break;
                case '.':
                case '(':
                case ')':
                case '[':
                case ']':
                case '{':
                case '}':
                case '+':
                case '^':
                case '$':
                case '|':
                case '\\':
                    sb.Append('\\').Append(c);
                    break;
                default:
                    sb.Append(c);
                    break;
            }
        }
    }
}
