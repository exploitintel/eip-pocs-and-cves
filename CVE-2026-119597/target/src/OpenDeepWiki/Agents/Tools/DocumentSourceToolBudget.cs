using System.ComponentModel;
using Microsoft.Extensions.AI;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// Budgeted source-discovery facade for document generation.
/// </summary>
public sealed class DocumentSourceToolBudget
{
    private const int DefaultMaxListResults = 20;
    private const int DefaultMaxGrepResults = 12;
    private const int DefaultReadLimit = 240;
    private readonly GitTool _gitTool;
    private readonly int? _maxSourceToolCalls;
    private int _sourceToolCalls;

    public DocumentSourceToolBudget(GitTool gitTool, int? maxSourceToolCalls)
    {
        _gitTool = gitTool ?? throw new ArgumentNullException(nameof(gitTool));
        _maxSourceToolCalls = maxSourceToolCalls is > 0 ? maxSourceToolCalls : null;
    }

    [Description(@"Reads a bounded file excerpt from the repository.

Use this only for the most relevant files. When the source-tool budget is reached,
stop exploring and write the document with the evidence already collected.")]
    public async Task<string> ReadAsync(
        [Description("Relative path to the file from repository root.")]
        string relativePath,
        [Description("Line number to start reading from (1-based). Default: 1")]
        int offset = 1,
        [Description("Maximum number of lines to read. Default and maximum: 240")]
        int limit = DefaultReadLimit,
        CancellationToken cancellationToken = default)
    {
        if (!TryUseSourceTool(out var budgetMessage))
        {
            return budgetMessage;
        }

        return await _gitTool.ReadAsync(
            relativePath,
            offset,
            Math.Min(Math.Max(1, limit), DefaultReadLimit),
            cancellationToken);
    }

    [Description(@"Lists a small set of repository files matching the specified pattern.

Use this only for initial targeting. When the source-tool budget is reached,
stop exploring and write the document with the evidence already collected.")]
    public async Task<string[]> ListFilesAsync(
        [Description("Glob pattern (e.g., '*.cs', 'src/**/*.ts', '**/*.json'). Default: all files")]
        string glob = "",
        [Description("Maximum number of files to return. Default and maximum: 20")]
        int maxResults = DefaultMaxListResults,
        CancellationToken cancellationToken = default)
    {
        if (!TryUseSourceTool(out var budgetMessage))
        {
            return [budgetMessage];
        }

        return await _gitTool.ListFilesAsync(
            glob,
            Math.Min(Math.Max(1, maxResults), DefaultMaxListResults),
            cancellationToken);
    }

    [Description(@"Searches for a small set of source matches.

Use targeted patterns. When the source-tool budget is reached, stop exploring
and write the document with the evidence already collected.")]
    public async Task<GrepResult[]> GrepAsync(
        [Description("The regex pattern to search for in file contents.")]
        string pattern,
        [Description("Glob pattern to filter files (e.g., '*.cs', '*.ts', '**/*.json'). Default: all files")]
        string glob = "",
        [Description("Whether the search is case sensitive. Default: false")]
        bool caseSensitive = false,
        [Description("Number of context lines to show before and after each match. Default and maximum: 1")]
        int contextLines = 1,
        [Description("Maximum number of results to return. Default and maximum: 12")]
        int maxResults = DefaultMaxGrepResults,
        CancellationToken cancellationToken = default)
    {
        if (!TryUseSourceTool(out var budgetMessage))
        {
            return
            [
                new GrepResult
                {
                    FilePath = "BUDGET",
                    LineNumber = 0,
                    LineContent = budgetMessage,
                    Context = "Stop source exploration and write the document using the evidence already collected."
                }
            ];
        }

        return await _gitTool.GrepAsync(
            pattern,
            glob,
            caseSensitive,
            Math.Min(Math.Max(0, contextLines), 1),
            Math.Min(Math.Max(1, maxResults), DefaultMaxGrepResults),
            cancellationToken);
    }

    public List<AITool> GetTools()
    {
        return
        [
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
        ];
    }

    private bool TryUseSourceTool(out string budgetMessage)
    {
        if (_maxSourceToolCalls.HasValue && _sourceToolCalls >= _maxSourceToolCalls.Value)
        {
            budgetMessage = $"SOURCE_TOOL_BUDGET_REACHED ({_sourceToolCalls}/{_maxSourceToolCalls.Value}). Stop source exploration now; call WriteDoc or AppendDoc with the evidence already collected, then finish.";
            return false;
        }

        _sourceToolCalls++;
        budgetMessage = string.Empty;
        return true;
    }
}
