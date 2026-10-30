using System.Collections.Concurrent;
using System.ComponentModel;
using System.Text;
using System.Text.RegularExpressions;
using Microsoft.Extensions.AI;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// Unified AI Tool for repository file operations including read, search, and list.
/// All file paths are relative to the repository root - actual filesystem paths are abstracted.
/// </summary>
public class GitTool
{
    private readonly string _workingDirectory;
    private readonly List<GitIgnoreRule> _gitIgnoreRules;
    private readonly HashSet<string> _readFiles = new(StringComparer.OrdinalIgnoreCase);

    /// <summary>
    /// Default maximum number of lines to read from a file.
    /// </summary>
    private const int DefaultReadLimit = 2000;

    /// <summary>
    /// Maximum characters per line before truncation.
    /// </summary>
    private const int MaxLineLength = 2000;

    /// <summary>
    /// Default context lines for grep results.
    /// </summary>
    private const int DefaultContextLines = 2;

    /// <summary>
    /// Initializes a new instance of GitTool with the specified working directory.
    /// </summary>
    /// <param name="workingDirectory">The absolute path to the repository working directory.</param>
    public GitTool(string workingDirectory)
    {
        if (string.IsNullOrWhiteSpace(workingDirectory))
        {
            throw new ArgumentException("Working directory cannot be empty.", nameof(workingDirectory));
        }

        _workingDirectory = Path.GetFullPath(workingDirectory);

        if (!Directory.Exists(_workingDirectory))
        {
            throw new DirectoryNotFoundException($"Working directory does not exist: {_workingDirectory}");
        }

        // 解析 .gitignore 文件
        _gitIgnoreRules = RepositoryFileFilter.ParseGitIgnore(_workingDirectory);
    }

    /// <summary>
    /// Gets the list of files that have been read by this tool instance.
    /// </summary>
    /// <returns>A list of relative file paths that were read.</returns>
    public List<string> GetReadFiles()
    {
        return _readFiles.OrderBy(f => f).ToList();
    }

    /// <summary>
    /// Clears the list of tracked read files.
    /// </summary>
    public void ClearReadFiles()
    {
        _readFiles.Clear();
    }

    /// <summary>
    /// Enumerates files matching a glob pattern.
    /// Supports *, **, and ? wildcards.
    /// Automatically filters out files matching .gitignore rules.
    /// </summary>
    /// <param name="glob">Glob pattern (e.g., "*.cs", "**/*.json", "src/**/*.ts")</param>
    /// <returns>Enumerable of matching file paths (full paths)</returns>
    private IEnumerable<string> EnumerateFilesWithGlob(string? glob)
    {
        if (string.IsNullOrWhiteSpace(glob))
        {
            // No pattern - return all files (filtered by gitignore)
            foreach (var file in Directory.EnumerateFiles(_workingDirectory, "*", SearchOption.AllDirectories))
            {
                var relativePath = GetRelativePath(file);
                if (!IsIgnoredByGitIgnore(relativePath))
                {
                    yield return file;
                }
            }

            yield break;
        }

        // Convert glob to regex pattern
        var globRegex = GlobToRegex(glob);

        foreach (var file in Directory.EnumerateFiles(_workingDirectory, "*", SearchOption.AllDirectories))
        {
            var relativePath = GetRelativePath(file);
            if (!IsIgnoredByGitIgnore(relativePath) && globRegex.IsMatch(relativePath))
            {
                yield return file;
            }
        }
    }

    /// <summary>
    /// Converts a glob pattern to a regex pattern.
    /// Supports: * (any chars except /), ** (any chars including /), ? (single char)
    /// </summary>
    private static Regex GlobToRegex(string glob)
    {
        var pattern = new StringBuilder("^");

        for (int i = 0; i < glob.Length; i++)
        {
            var c = glob[i];
            switch (c)
            {
                case '*':
                    if (i + 1 < glob.Length && glob[i + 1] == '*')
                    {
                        // ** matches any path including /
                        pattern.Append(".*");
                        i++; // Skip next *
                        // Skip trailing / after **
                        if (i + 1 < glob.Length && (glob[i + 1] == '/' || glob[i + 1] == '\\'))
                        {
                            i++;
                        }
                    }
                    else
                    {
                        // * matches any chars except /
                        pattern.Append("[^/\\\\]*");
                    }

                    break;
                case '?':
                    pattern.Append("[^/\\\\]");
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
                    pattern.Append('\\').Append(c);
                    break;
                case '\\':
                case '/':
                    pattern.Append("[/\\\\]");
                    break;
                default:
                    pattern.Append(c);
                    break;
            }
        }

        pattern.Append('$');
        return new Regex(pattern.ToString(), RegexOptions.IgnoreCase | RegexOptions.Compiled);
    }

    /// <summary>
    /// Reads the content of a file at the specified relative path.
    /// </summary>
    /// <param name="relativePath">The path relative to the repository root.</param>
    /// <param name="offset">Line number to start reading from (1-based).</param>
    /// <param name="limit">Maximum number of lines to read.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The file content as a string with line numbers.</returns>
    [Description(@"Reads a file from the repository.

Usage:
- The path parameter must be relative to the repository root (e.g., 'src/main.cs', 'README.md')
- By default reads up to 2000 lines starting from the beginning
- You can optionally specify offset and limit for large files
- Lines longer than 2000 characters will be truncated
- Results include line numbers in 'N: content' format
- Binary files (images, executables, etc.) are not supported
- Hidden files/directories (starting with .) are accessible but filtered in search results")]
    public async Task<string> ReadAsync(
        [Description("Relative path to the file from repository root, e.g., 'src/main.cs' or 'docs/README.md'")]
        string relativePath,
        [Description("Line number to start reading from (1-based). Use for large files. Default: 1")]
        int offset = 1,
        [Description("Maximum number of lines to read. Use for large files. Default: 2000")]
        int limit = DefaultReadLimit,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(relativePath))
        {
            return "ERROR: Relative path cannot be empty. Please provide a valid file path relative to the repository root.";
        }

        try
        {
            var normalizedPath = NormalizePath(relativePath);
            var fullPath = Path.GetFullPath(Path.Combine(_workingDirectory, normalizedPath));

            if (!fullPath.StartsWith(_workingDirectory, StringComparison.OrdinalIgnoreCase))
            {
                return $"ERROR: Access denied. The path '{relativePath}' is outside the repository boundaries. Please use a path relative to the repository root.";
            }

            if (!File.Exists(fullPath))
            {
                return $"ERROR: File not found at path '{relativePath}'. Please verify the file path is correct and the file exists in the repository.";
            }

            var lines = await File.ReadAllLinesAsync(fullPath, cancellationToken);
            var startIndex = Math.Max(0, offset - 1);
            var endIndex = Math.Min(lines.Length, startIndex + limit);

            // 记录读取的文件
            _readFiles.Add(normalizedPath);

            var result = new StringBuilder();
            for (int i = startIndex; i < endIndex; i++)
            {
                var line = lines[i];
                if (line.Length > MaxLineLength)
                {
                    line = line[..MaxLineLength] + "... [truncated]";
                }

                result.AppendLine($"{i + 1}: {line}");
            }

            if (endIndex < lines.Length)
            {
                result.AppendLine($"[{lines.Length - endIndex} more lines not shown. Use offset/limit to read more.]");
            }

            return result.ToString();
        }
        catch (UnauthorizedAccessException)
        {
            return $"ERROR: Permission denied when trying to read '{relativePath}'. The file may have restricted access permissions.";
        }
        catch (IOException ex)
        {
            return $"ERROR: Failed to read file '{relativePath}'. IO error: {ex.Message}";
        }
        catch (Exception ex)
        {
            return $"ERROR: Unexpected error reading file '{relativePath}': {ex.Message}";
        }
    }

    /// <summary>
    /// Searches for patterns in repository files.
    /// </summary>
    /// <param name="pattern">The search pattern (supports regex).</param>
    /// <param name="glob">Optional glob pattern to filter files.</param>
    /// <param name="caseSensitive">Whether the search is case sensitive.</param>
    /// <param name="contextLines">Number of context lines before and after each match.</param>
    /// <param name="maxResults">Maximum number of results to return.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    [Description(@"A powerful search tool for finding patterns in repository files.

Usage:
- Supports full regex syntax (e.g., 'log.*Error', 'function\s+\w+', 'class\s+\w+')
- Filter files with glob parameter (e.g., '*.cs', '*.ts', '**/*.json')
- Use caseSensitive=false for case-insensitive search (default)
- Context lines show surrounding code for better understanding
- Binary files and hidden directories (starting with .) are automatically skipped
- Results are capped at maxResults to prevent overwhelming output

Pattern Examples:
- Find class definitions: 'class\s+\w+'
- Find TODO comments: 'TODO|FIXME|HACK'
- Find function calls: 'functionName\s*\('
- Find imports: '^using|^import'")]
    public async Task<GrepResult[]> GrepAsync(
        [Description("The regex pattern to search for in file contents. Supports full regex syntax.")]
        string pattern,
        [Description("Glob pattern to filter files (e.g., '*.cs', '*.ts', '**/*.json'). Default: all files")]
        string glob = "",
        [Description("Whether the search is case sensitive. Default: false")]
        bool caseSensitive = false,
        [Description("Number of context lines to show before and after each match. Default: 2")]
        int contextLines = DefaultContextLines,
        [Description("Maximum number of results to return. Default: 50")]
        int maxResults = 50,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(pattern))
        {
            return new[]
            {
                new GrepResult
                {
                    FilePath = "ERROR",
                    LineNumber = 0,
                    LineContent = "Search pattern cannot be empty. Please provide a valid regex pattern to search for.",
                    Context = "Example patterns: 'class\\s+\\w+' for class definitions, 'TODO|FIXME' for comments, '^using|^import' for imports"
                }
            };
        }

        try
        {
            var regexOptions = RegexOptions.Compiled;
            if (!caseSensitive)
            {
                regexOptions |= RegexOptions.IgnoreCase;
            }

            var regex = new Regex(pattern, regexOptions);
            var results = new ConcurrentBag<GrepResult>();
            var resultCount = 0;

        await Task.Run(() =>
        {
            try
            {
                var files = EnumerateFilesWithGlob(glob)
                    .Where(f => !RepositoryFileFilter.IsBinaryFile(f) && !IsHiddenPath(f));

                Parallel.ForEach(files, new ParallelOptions
                    {
                        CancellationToken = cancellationToken,
                        MaxDegreeOfParallelism = Environment.ProcessorCount
                    },
                    (file, state) =>
                    {
                        if (Volatile.Read(ref resultCount) >= maxResults)
                        {
                            state.Stop();
                            return;
                        }

                        try
                        {
                            SearchInFile(file, regex, contextLines, maxResults, results, ref resultCount);
                        }
                        catch
                        {
                            // Skip files that cannot be read
                        }
                    });
            }
            catch (OperationCanceledException)
            {
                // Cancelled
            }
            catch
            {
                // Return empty results if directory enumeration fails
            }
        }, cancellationToken);

        return [.. results.OrderBy(r => r.FilePath).ThenBy(r => r.LineNumber).Take(maxResults)];
        }
        catch (ArgumentException ex)
        {
            return new[]
            {
                new GrepResult
                {
                    FilePath = "ERROR",
                    LineNumber = 0,
                    LineContent = $"Invalid regex pattern: {ex.Message}",
                    Context = "Please check your regex syntax. Common issues: unescaped special characters, unmatched brackets, invalid escape sequences."
                }
            };
        }
        catch (Exception ex)
        {
            return new[]
            {
                new GrepResult
                {
                    FilePath = "ERROR",
                    LineNumber = 0,
                    LineContent = $"Search failed: {ex.Message}",
                    Context = "An unexpected error occurred during the search operation."
                }
            };
        }
    }

    /// <summary>
    /// Searches for pattern matches in a single file.
    /// </summary>
    private void SearchInFile(string file, Regex regex, int contextLines, int maxResults,
        ConcurrentBag<GrepResult> results, ref int resultCount)
    {
        var relativePath = GetRelativePath(file);
        using var reader = new StreamReader(file);
        var lineBuffer = new Queue<string>(contextLines + 1);
        var lineNumber = 0;
        string? line;

        while ((line = reader.ReadLine()) != null && Volatile.Read(ref resultCount) < maxResults)
        {
            lineNumber++;

            if (regex.IsMatch(line))
            {
                var contextBuilder = new StringBuilder();

                // Add buffered context lines before
                var bufferLineNum = lineNumber - lineBuffer.Count;
                foreach (var bufLine in lineBuffer)
                {
                    contextBuilder.AppendLine($"  {bufferLineNum}: {TruncateLine(bufLine)}");
                    bufferLineNum++;
                }

                // Add matching line
                contextBuilder.AppendLine($"> {lineNumber}: {TruncateLine(line)}");

                // Read context lines after
                for (int j = 0; j < contextLines && Volatile.Read(ref resultCount) < maxResults; j++)
                {
                    var nextLine = reader.ReadLine();
                    if (nextLine == null) break;
                    lineNumber++;
                    contextBuilder.AppendLine($"  {lineNumber}: {TruncateLine(nextLine)}");

                    // Check if next line also matches
                    if (regex.IsMatch(nextLine))
                    {
                        lineBuffer.Clear();
                        lineBuffer.Enqueue(nextLine);
                    }
                }

                results.Add(new GrepResult
                {
                    FilePath = relativePath,
                    LineNumber = lineNumber - contextLines,
                    LineContent = line.Trim(),
                    Context = contextBuilder.ToString().TrimEnd()
                });

                Interlocked.Increment(ref resultCount);
                lineBuffer.Clear();
            }
            else
            {
                // Maintain rolling buffer for context
                if (lineBuffer.Count >= contextLines)
                {
                    lineBuffer.Dequeue();
                }

                lineBuffer.Enqueue(line);
            }
        }
    }

    /// <summary>
    /// Truncates a line if it exceeds the maximum length.
    /// </summary>
    private static string TruncateLine(string line)
    {
        return line.Length > MaxLineLength ? line[..MaxLineLength] + "..." : line;
    }

    /// <summary>
    /// Lists files in the repository matching the specified pattern.
    /// </summary>
    /// <param name="glob">Optional glob pattern filter.</param>
    /// <param name="maxResults">Maximum number of files to return.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>Array of relative file paths.</returns>
    [Description(@"Lists files in the repository matching the specified pattern.

Usage:
- Returns relative file paths from repository root
- Supports full glob syntax: *, **, ?
- Hidden files/directories (starting with .) are excluded by default
- Results are sorted alphabetically
- Use maxResults to limit output for large repositories

Glob Examples:
- List all C# files: glob='*.cs'
- List all files recursively: glob='**/*'
- List TypeScript in src: glob='src/**/*.ts'
- List JSON configs: glob='**/*.json'
- Single char wildcard: glob='file?.txt'")]
    public async Task<string[]> ListFilesAsync(
        [Description("Glob pattern (e.g., '*.cs', 'src/**/*.ts', '**/*.json'). Default: all files")]
        string glob = "",
        [Description("Maximum number of files to return. Default: 50. Use higher values (100-200) for comprehensive discovery.")]
        int maxResults = 50,
        CancellationToken cancellationToken = default)
    {
        return await Task.Run(() =>
        {
            return EnumerateFilesWithGlob(glob)
                .Where(f => !IsHiddenPath(f))
                .Select(GetRelativePath)
                .OrderBy(f => f)
                .Take(maxResults)
                .ToArray();
        }, cancellationToken);
    }

    /// <summary>
    /// Normalizes a path by replacing backslashes with forward slashes and removing leading slashes.
    /// </summary>
    private static string NormalizePath(string path)
    {
        // Replace backslashes with forward slashes
        var normalized = path.Replace('\\', '/');

        // Remove leading slashes
        normalized = normalized.TrimStart('/');

        // Remove any ".." components to prevent directory traversal
        var parts = normalized.Split('/').Where(p => p != ".." && p != ".").ToArray();

        return string.Join(Path.DirectorySeparatorChar.ToString(), parts);
    }

    /// <summary>
    /// Gets the relative path from the working directory.
    /// </summary>
    private string GetRelativePath(string fullPath)
    {
        var relativePath = Path.GetRelativePath(_workingDirectory, fullPath);
        // Always use forward slashes for consistency
        return relativePath.Replace('\\', '/');
    }

    /// <summary>
    /// Checks if a file is likely binary based on extension.
    /// </summary>
    private static bool IsBinaryFile(string filePath)
    {
        var binaryExtensions = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            ".exe", ".dll", ".so", ".dylib", ".bin", ".obj", ".o",
            ".png", ".jpg", ".jpeg", ".gif", ".bmp", ".ico", ".svg",
            ".pdf", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx",
            ".zip", ".tar", ".gz", ".rar", ".7z",
            ".mp3", ".mp4", ".avi", ".mov", ".wav",
            ".ttf", ".otf", ".woff", ".woff2", ".eot",
            ".db", ".sqlite", ".mdb"
        };

        var extension = Path.GetExtension(filePath);
        return binaryExtensions.Contains(extension);
    }

    /// <summary>
    /// Checks if a path contains hidden directories (starting with .).
    /// </summary>
    private bool IsHiddenPath(string fullPath)
    {
        var relativePath = GetRelativePath(fullPath);
        var parts = relativePath.Split('/', '\\');

        // Check if any directory component starts with . (except current directory)
        return parts.Any(p => p.StartsWith('.') && p != ".");
    }

    /// <summary>
    /// Checks if a relative path should be ignored based on .gitignore rules.
    /// </summary>
    /// <param name="relativePath">The relative path to check.</param>
    /// <returns>True if the path should be ignored, false otherwise.</returns>
    private bool IsIgnoredByGitIgnore(string relativePath)
    {
        if (_gitIgnoreRules.Count == 0)
        {
            return false;
        }

        // Normalize path separators
        var normalizedPath = relativePath.Replace('\\', '/');

        // Check each rule in order (later rules can override earlier ones)
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

    /// <summary>
    /// Parses .gitignore file and returns a list of ignore rules.
    /// </summary>
    /// <param name="workingDirectory">The repository root directory.</param>
    /// <returns>List of parsed gitignore rules.</returns>
    private static List<GitIgnoreRule> ParseGitIgnore(string workingDirectory)
    {
        var rules = new List<GitIgnoreRule>();
        var gitignorePath = Path.Combine(workingDirectory, ".gitignore");

        if (!File.Exists(gitignorePath))
        {
            return rules;
        }

        try
        {
            var lines = File.ReadAllLines(gitignorePath);
            foreach (var line in lines)
            {
                var trimmedLine = line.Trim();

                // Skip empty lines and comments
                if (string.IsNullOrEmpty(trimmedLine) || trimmedLine.StartsWith('#'))
                {
                    continue;
                }

                var rule = ParseGitIgnorePattern(trimmedLine);
                if (rule != null)
                {
                    rules.Add(rule);
                }
            }
        }
        catch
        {
            // If we can't read the file, return empty rules
        }

        return rules;
    }

    /// <summary>
    /// Parses a single gitignore pattern into a rule.
    /// </summary>
    /// <param name="pattern">The gitignore pattern string.</param>
    /// <returns>A GitIgnoreRule or null if the pattern is invalid.</returns>
    private static GitIgnoreRule? ParseGitIgnorePattern(string pattern)
    {
        var rule = new GitIgnoreRule
        {
            OriginalPattern = pattern,
            IsNegation = false,
            DirectoryOnly = false
        };

        var workingPattern = pattern;

        // Check for negation
        if (workingPattern.StartsWith('!'))
        {
            rule.IsNegation = true;
            workingPattern = workingPattern[1..];
        }

        // Check for directory-only pattern
        if (workingPattern.EndsWith('/'))
        {
            rule.DirectoryOnly = true;
            workingPattern = workingPattern.TrimEnd('/');
        }

        // Remove leading slash (anchors to root)
        var anchoredToRoot = workingPattern.StartsWith('/');
        if (anchoredToRoot)
        {
            workingPattern = workingPattern[1..];
        }

        // Convert gitignore pattern to regex
        var regexPattern = GitIgnorePatternToRegex(workingPattern, anchoredToRoot);

        try
        {
            rule.Pattern = new Regex(regexPattern, RegexOptions.IgnoreCase | RegexOptions.Compiled);
            return rule;
        }
        catch
        {
            // Invalid regex pattern
            return null;
        }
    }

    /// <summary>
    /// Converts a gitignore pattern to a regex pattern.
    /// </summary>
    private static string GitIgnorePatternToRegex(string pattern, bool anchoredToRoot)
    {
        var sb = new StringBuilder();

        // If pattern contains /, it's relative to root; otherwise match anywhere
        var containsSlash = pattern.Contains('/');

        if (anchoredToRoot || containsSlash)
        {
            sb.Append('^');
        }
        else
        {
            sb.Append("(^|/)");
        }

        for (int i = 0; i < pattern.Length; i++)
        {
            var c = pattern[i];
            switch (c)
            {
                case '*':
                    if (i + 1 < pattern.Length && pattern[i + 1] == '*')
                    {
                        // ** matches everything including /
                        if (i + 2 < pattern.Length && pattern[i + 2] == '/')
                        {
                            // **/ matches zero or more directories
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
                        // * matches everything except /
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

        sb.Append("(/.*)?$");
        return sb.ToString();
    }

    public List<AITool> GetTools()
    {
        return new List<AITool>
        {
            AIFunctionFactory.Create(ReadAsync, new AIFunctionFactoryOptions
            {
                Name = "ReadFile"
            }),
            AIFunctionFactory.Create(ListFilesAsync, new AIFunctionFactoryOptions
            {
                Name = "ListFiles"
            }),
            AIFunctionFactory.Create(GrepAsync, new AIFunctionFactoryOptions
            {
                Name = "Grep"
            })
        };
    }
}
