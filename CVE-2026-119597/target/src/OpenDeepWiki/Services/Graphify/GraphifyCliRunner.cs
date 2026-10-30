using System.Diagnostics;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using System.Text.RegularExpressions;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Services.AI;
using OpenDeepWiki.Services.Admin;
using OpenDeepWiki.Services.Repositories;

namespace OpenDeepWiki.Services.Graphify;

public class GraphifyOptions
{
    public string Command { get; set; } = "graphify";

    public string PythonCommand { get; set; } = "python3";

    public string? Backend { get; set; }

    public string? Model { get; set; }

    public string? OpenAiBaseUrl { get; set; }

    public string? OpenAiApiKey { get; set; }

    public string? OutputRoot { get; set; }

    public bool EnableLlmCommunityLabels { get; set; } = true;

    public int CommunityLabelMaxNodes { get; set; } = 12;

    public int TimeoutMinutes { get; set; } = 60;

    public int MaxLogBytes { get; set; } = 200_000;
}

public sealed record GraphifyRunResult(
    string OutputRoot,
    string EntryFilePath,
    string GraphJsonPath,
    string ReportPath,
    string CommitId,
    string LogOutput);

public interface IGraphifyCliRunner
{
    Task<GraphifyRunResult> GenerateAsync(
        RepositoryWorkspace workspace,
        CancellationToken cancellationToken = default);
}

public class GraphifyCliRunner : IGraphifyCliRunner
{
    private readonly GraphifyOptions _options;
    private readonly ILogger<GraphifyCliRunner> _logger;
    private readonly HttpClient _httpClient;
    private readonly IAiProviderResolver? _aiProviderResolver;
    private readonly IAdminSettingsService? _settingsService;

    public GraphifyCliRunner(
        IOptions<GraphifyOptions> options,
        ILogger<GraphifyCliRunner> logger,
        HttpClient? httpClient = null)
    {
        _options = options.Value;
        _logger = logger;
        _httpClient = httpClient ?? new HttpClient();
    }

    public GraphifyCliRunner(
        IOptions<GraphifyOptions> options,
        ILogger<GraphifyCliRunner> logger,
        IAiProviderResolver aiProviderResolver,
        IAdminSettingsService settingsService,
        HttpClient? httpClient = null)
        : this(options, logger, httpClient)
    {
        _aiProviderResolver = aiProviderResolver;
        _settingsService = settingsService;
    }

    public async Task<GraphifyRunResult> GenerateAsync(
        RepositoryWorkspace workspace,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(workspace.WorkingDirectory) ||
            !Directory.Exists(workspace.WorkingDirectory))
        {
            throw new DirectoryNotFoundException(
                $"Repository workspace does not exist: {workspace.WorkingDirectory}");
        }

        var artifactRoot = ResolveArtifactRoot(workspace);
        Directory.CreateDirectory(artifactRoot);

        var graphifyOut = Path.Combine(artifactRoot, "graphify-out");
        var graphJsonPath = Path.Combine(graphifyOut, "graph.json");
        var entryFilePath = Path.Combine(graphifyOut, "graph.html");
        var reportPath = Path.Combine(graphifyOut, "GRAPH_REPORT.md");

        using var timeoutCts = CreateTimeoutTokenSource(cancellationToken);
        var token = timeoutCts.Token;
        var graphifyAi = await ResolveGraphifyAiAsync(token);

        var logBuilder = new StringBuilder();

        await RunGraphifyCommandAsync(
            workspace.WorkingDirectory,
            BuildExtractArguments(workspace.WorkingDirectory, artifactRoot, graphifyAi),
            logBuilder,
            token,
            ai: graphifyAi);

        if (!File.Exists(graphJsonPath))
        {
            throw new FileNotFoundException("Graphify did not produce graph.json", graphJsonPath);
        }

        await EnsureCommunityLabelsAsync(graphifyOut, graphJsonPath, graphifyAi, token);

        await RunGraphifyCommandAsync(
            workspace.WorkingDirectory,
            ["cluster-only", artifactRoot, "--graph", graphJsonPath],
            logBuilder,
            token,
            graphifyAi,
            new Dictionary<string, string>
            {
                ["GRAPHIFY_OUT"] = graphifyOut
            });

        if (!File.Exists(reportPath))
        {
            throw new FileNotFoundException("Graphify did not produce GRAPH_REPORT.md", reportPath);
        }

        if (!File.Exists(entryFilePath))
        {
            throw new FileNotFoundException("Graphify did not produce graph.html", entryFilePath);
        }

        return new GraphifyRunResult(
            artifactRoot,
            entryFilePath,
            graphJsonPath,
            reportPath,
            workspace.CommitId,
            TrimLog(logBuilder.ToString()));
    }

    private string[] BuildExtractArguments(
        string workspacePath,
        string artifactRoot,
        ResolvedAiModel? ai)
    {
        var args = new List<string> { "extract", workspacePath, "--out", artifactRoot };

        var backend = ResolveBackend(ai);
        if (!string.IsNullOrWhiteSpace(backend))
        {
            args.Add("--backend");
            args.Add(backend);
        }

        var model = ai?.ModelId ?? _options.Model;
        if (!string.IsNullOrWhiteSpace(model))
        {
            args.Add("--model");
            args.Add(model);
        }

        return args.ToArray();
    }

    private async Task EnsureCommunityLabelsAsync(
        string graphifyOut,
        string graphJsonPath,
        ResolvedAiModel? ai,
        CancellationToken cancellationToken)
    {
        var labelsPath = Path.Combine(graphifyOut, ".graphify_labels.json");
        if (File.Exists(labelsPath) && await HasMeaningfulCommunityLabelsAsync(labelsPath, cancellationToken))
        {
            _logger.LogInformation("Graphify community labels already exist, skipping LLM labeling");
            return;
        }

        var analysisPath = Path.Combine(graphifyOut, ".graphify_analysis.json");
        if (!File.Exists(analysisPath))
        {
            return;
        }

        var summaries = await LoadCommunitySummariesAsync(analysisPath, graphJsonPath, cancellationToken);
        if (summaries.Count == 0)
        {
            return;
        }

        var labels = await TryGenerateLlmCommunityLabelsAsync(summaries, ai, cancellationToken);
        if (labels == null)
        {
            labels = BuildFallbackCommunityLabels(summaries);
            _logger.LogInformation(
                "Graphify community labels generated with fallback. CommunityCount: {CommunityCount}",
                labels.Count);
        }
        else
        {
            _logger.LogInformation(
                "Graphify community labels generated with LLM. CommunityCount: {CommunityCount}",
                labels.Count);
        }

        await WriteCommunityLabelsAsync(labelsPath, labels, cancellationToken);
    }

    private static async Task<bool> HasMeaningfulCommunityLabelsAsync(
        string labelsPath,
        CancellationToken cancellationToken)
    {
        try
        {
            await using var stream = File.OpenRead(labelsPath);
            using var document = await JsonDocument.ParseAsync(stream, cancellationToken: cancellationToken);
            if (document.RootElement.ValueKind != JsonValueKind.Object)
            {
                return false;
            }

            var labels = document.RootElement
                .EnumerateObject()
                .Where(label => label.Value.ValueKind == JsonValueKind.String)
                .Select(label => label.Value.GetString())
                .Where(label => !string.IsNullOrWhiteSpace(label))
                .ToList();

            return labels.Count > 0 &&
                   labels.Any(label => !Regex.IsMatch(label!, @"^community\s+\d+$", RegexOptions.IgnoreCase));
        }
        catch
        {
            return false;
        }
    }

    private static async Task<List<CommunitySummary>> LoadCommunitySummariesAsync(
        string analysisPath,
        string graphJsonPath,
        CancellationToken cancellationToken)
    {
        await using var stream = File.OpenRead(analysisPath);
        using var document = await JsonDocument.ParseAsync(stream, cancellationToken: cancellationToken);
        if (!document.RootElement.TryGetProperty("communities", out var communities) ||
            communities.ValueKind != JsonValueKind.Object)
        {
            return [];
        }

        var nodesById = await LoadGraphNodesAsync(graphJsonPath, cancellationToken);
        var degreeByNodeId = await LoadGraphDegreesAsync(graphJsonPath, cancellationToken);
        var summaries = new List<CommunitySummary>();

        foreach (var community in communities.EnumerateObject())
        {
            if (community.Value.ValueKind != JsonValueKind.Array)
            {
                continue;
            }

            var nodeIds = community.Value
                .EnumerateArray()
                .Where(item => item.ValueKind == JsonValueKind.String)
                .Select(item => item.GetString())
                .Where(id => !string.IsNullOrWhiteSpace(id))
                .Select(id => id!)
                .ToList();

            var nodes = nodeIds
                .Where(nodesById.ContainsKey)
                .Select(id => nodesById[id])
                .OrderByDescending(node => degreeByNodeId.GetValueOrDefault(node.Id))
                .ThenBy(node => node.Label, StringComparer.OrdinalIgnoreCase)
                .ToList();

            summaries.Add(new CommunitySummary(
                community.Name,
                nodeIds.Count,
                nodes
                    .Select(node => CleanNodeLabel(node.Label))
                    .Where(label => !string.IsNullOrWhiteSpace(label))
                    .Distinct(StringComparer.OrdinalIgnoreCase)
                    .Take(12)
                    .ToList(),
                nodes
                    .Select(node => node.SourceFile)
                    .Where(source => !string.IsNullOrWhiteSpace(source))
                    .Select(source => source!)
                    .GroupBy(source => source)
                    .OrderByDescending(group => group.Count())
                    .ThenBy(group => group.Key, StringComparer.OrdinalIgnoreCase)
                    .Select(group => group.Key)
                    .Take(5)
                    .ToList()));
        }

        return summaries;
    }

    private static async Task<Dictionary<string, GraphNode>> LoadGraphNodesAsync(
        string graphJsonPath,
        CancellationToken cancellationToken)
    {
        await using var stream = File.OpenRead(graphJsonPath);
        using var document = await JsonDocument.ParseAsync(stream, cancellationToken: cancellationToken);
        if (!document.RootElement.TryGetProperty("nodes", out var nodes) ||
            nodes.ValueKind != JsonValueKind.Array)
        {
            return [];
        }

        var result = new Dictionary<string, GraphNode>();
        foreach (var node in nodes.EnumerateArray())
        {
            var id = GetStringProperty(node, "id");
            if (string.IsNullOrWhiteSpace(id))
            {
                continue;
            }

            result[id] = new GraphNode(
                id,
                GetStringProperty(node, "label") ?? id,
                GetStringProperty(node, "source_file"),
                GetStringProperty(node, "file_type"));
        }

        return result;
    }

    private static async Task<Dictionary<string, int>> LoadGraphDegreesAsync(
        string graphJsonPath,
        CancellationToken cancellationToken)
    {
        await using var stream = File.OpenRead(graphJsonPath);
        using var document = await JsonDocument.ParseAsync(stream, cancellationToken: cancellationToken);
        if (!document.RootElement.TryGetProperty("links", out var links) ||
            links.ValueKind != JsonValueKind.Array)
        {
            return [];
        }

        var degreeByNodeId = new Dictionary<string, int>();
        foreach (var edge in links.EnumerateArray())
        {
            IncrementDegree(degreeByNodeId, GetNodeReference(edge, "source"));
            IncrementDegree(degreeByNodeId, GetNodeReference(edge, "target"));
        }

        return degreeByNodeId;
    }

    private async Task<Dictionary<string, string>?> TryGenerateLlmCommunityLabelsAsync(
        IReadOnlyCollection<CommunitySummary> summaries,
        ResolvedAiModel? ai,
        CancellationToken cancellationToken)
    {
        var apiKey = ai?.ApiKey;
        if (!_options.EnableLlmCommunityLabels ||
            string.IsNullOrWhiteSpace(apiKey))
        {
            return null;
        }

        var endpoint = (ai?.BaseUrl ?? "https://api.openai.com/v1").TrimEnd('/');
        var model = ai!.ModelId;
        var requestPayload = new
        {
            model,
            temperature = 0,
            messages = new object[]
            {
                new
                {
                    role = "system",
                    content = "You label code knowledge-graph communities. Return only JSON. Each label must be 2-5 English words, concrete, technical, and must not be 'Community N'."
                },
                new
                {
                    role = "user",
                    content = BuildCommunityLabelPrompt(summaries)
                }
            }
        };

        try
        {
            using var request = new HttpRequestMessage(HttpMethod.Post, $"{endpoint}/chat/completions");
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", apiKey);
            request.Content = new StringContent(
                JsonSerializer.Serialize(requestPayload),
                Encoding.UTF8,
                "application/json");

            using var response = await _httpClient.SendAsync(request, cancellationToken);
            var responseBody = await response.Content.ReadAsStringAsync(cancellationToken);
            if (!response.IsSuccessStatusCode)
            {
                _logger.LogWarning(
                    "Graphify community LLM labeling failed. StatusCode: {StatusCode}, Body: {Body}",
                    response.StatusCode,
                    TrimForLog(responseBody, 1000));
                return null;
            }

            Dictionary<string, string>? labels;
            try
            {
                labels = ParseLlmCommunityLabels(responseBody, summaries);
            }
            catch (Exception ex) when (ex is JsonException or KeyNotFoundException or InvalidOperationException)
            {
                _logger.LogWarning(
                    ex,
                    "Graphify community LLM labeling response parse failed. ErrorType: {ErrorType}, Body: {Body}",
                    ex.GetType().Name,
                    TrimForLog(responseBody, 1000));
                return null;
            }

            if (labels == null)
            {
                _logger.LogWarning(
                    "Graphify community LLM labeling returned invalid JSON shape. Body: {Body}",
                    TrimForLog(responseBody, 1000));
            }

            return labels;
        }
        catch (Exception ex) when (ex is not OperationCanceledException)
        {
            _logger.LogWarning(ex, "Graphify community LLM labeling failed");
            return null;
        }
    }

    private static string BuildCommunityLabelPrompt(IReadOnlyCollection<CommunitySummary> summaries)
    {
        var payload = summaries
            .OrderBy(summary => int.TryParse(summary.Id, out var id) ? id : int.MaxValue)
            .ThenBy(summary => summary.Id, StringComparer.OrdinalIgnoreCase)
            .Select(summary => new
            {
                id = summary.Id,
                nodeCount = summary.NodeCount,
                topNodes = summary.TopLabels,
                sourceFiles = summary.TopSourceFiles
            });

        return """
Create concise labels for these code graph communities.
Return exactly this JSON shape:
{"labels":{"0":"Short Technical Name"}}

Communities:
""" + JsonSerializer.Serialize(payload);
    }

    private static Dictionary<string, string>? ParseLlmCommunityLabels(
        string responseBody,
        IReadOnlyCollection<CommunitySummary> summaries)
    {
        using var responseDocument = JsonDocument.Parse(responseBody);
        if (TryParseCommunityLabelsFromElement(responseDocument.RootElement, summaries, out var rootLabels))
        {
            return rootLabels;
        }

        var content = ExtractLlmResponseContent(responseDocument.RootElement);

        if (string.IsNullOrWhiteSpace(content))
        {
            return null;
        }

        content = StripMarkdownCodeFence(content);
        content = ExtractFirstJsonObject(content) ?? content;
        using var contentDocument = JsonDocument.Parse(content);
        return TryParseCommunityLabelsFromElement(contentDocument.RootElement, summaries, out var labels)
            ? labels
            : null;
    }

    private static string? ExtractLlmResponseContent(JsonElement root)
    {
        if (root.TryGetProperty("choices", out var choices) &&
            choices.ValueKind == JsonValueKind.Array &&
            choices.GetArrayLength() > 0)
        {
            var firstChoice = choices[0];
            if (firstChoice.TryGetProperty("message", out var message) &&
                message.ValueKind == JsonValueKind.Object &&
                message.TryGetProperty("content", out var messageContent) &&
                messageContent.ValueKind == JsonValueKind.String)
            {
                return messageContent.GetString();
            }

            if (firstChoice.TryGetProperty("text", out var text) &&
                text.ValueKind == JsonValueKind.String)
            {
                return text.GetString();
            }
        }

        if (root.TryGetProperty("content", out var content) &&
            content.ValueKind == JsonValueKind.String)
        {
            return content.GetString();
        }

        if (root.TryGetProperty("text", out var rootText) &&
            rootText.ValueKind == JsonValueKind.String)
        {
            return rootText.GetString();
        }

        return null;
    }

    private static bool TryParseCommunityLabelsFromElement(
        JsonElement root,
        IReadOnlyCollection<CommunitySummary> summaries,
        out Dictionary<string, string> labels)
    {
        labels = [];

        if (!root.TryGetProperty("labels", out var labelsElement) ||
            labelsElement.ValueKind != JsonValueKind.Object)
        {
            return false;
        }

        var expectedIds = summaries.Select(summary => summary.Id).ToHashSet(StringComparer.Ordinal);
        foreach (var label in labelsElement.EnumerateObject())
        {
            if (!expectedIds.Contains(label.Name) || label.Value.ValueKind != JsonValueKind.String)
            {
                continue;
            }

            var value = NormalizeCommunityLabel(label.Value.GetString());
            if (!string.IsNullOrWhiteSpace(value))
            {
                labels[label.Name] = value;
            }
        }

        if (labels.Count == 0)
        {
            return false;
        }

        foreach (var summary in summaries)
        {
            labels.TryAdd(summary.Id, BuildFallbackCommunityLabel(summary));
        }

        return true;
    }

    private static Dictionary<string, string> BuildFallbackCommunityLabels(
        IReadOnlyCollection<CommunitySummary> summaries)
    {
        return summaries.ToDictionary(summary => summary.Id, BuildFallbackCommunityLabel);
    }

    private static string BuildFallbackCommunityLabel(CommunitySummary summary)
    {
        var candidates = summary.TopLabels
            .Select(CleanNodeLabel)
            .Where(label => !string.IsNullOrWhiteSpace(label))
            .Take(3)
            .ToList();

        if (candidates.Count == 0)
        {
            return $"Community {summary.Id}";
        }

        var label = string.Join(" ", candidates)
            .Replace("_", " ", StringComparison.Ordinal)
            .Replace("-", " ", StringComparison.Ordinal);

        return NormalizeCommunityLabel(label) ?? $"Community {summary.Id}";
    }

    private static async Task WriteCommunityLabelsAsync(
        string labelsPath,
        Dictionary<string, string> labels,
        CancellationToken cancellationToken)
    {
        var json = JsonSerializer.Serialize(labels, new JsonSerializerOptions
        {
            WriteIndented = true
        });
        await File.WriteAllTextAsync(labelsPath, json, cancellationToken);
    }

    private static string? NormalizeCommunityLabel(string? value)
    {
        if (string.IsNullOrWhiteSpace(value))
        {
            return null;
        }

        var cleaned = Regex.Replace(value.Trim(), @"\s+", " ");
        cleaned = cleaned.Trim('"', '\'', '.', ':', ';', '-', ' ');
        if (string.IsNullOrWhiteSpace(cleaned) ||
            Regex.IsMatch(cleaned, @"^community\s+\d+$", RegexOptions.IgnoreCase))
        {
            return null;
        }

        var words = cleaned.Split(' ', StringSplitOptions.RemoveEmptyEntries);
        if (words.Length > 6)
        {
            cleaned = string.Join(' ', words.Take(6));
        }

        return cleaned.Length > 80 ? cleaned[..80] : cleaned;
    }

    private static string CleanNodeLabel(string value)
    {
        var label = value.Trim();
        if (label.EndsWith("()", StringComparison.Ordinal))
        {
            label = label[..^2];
        }

        var extension = Path.GetExtension(label);
        if (!string.IsNullOrWhiteSpace(extension) && extension.Length <= 6)
        {
            label = Path.GetFileNameWithoutExtension(label);
        }

        return label
            .Replace("_", " ", StringComparison.Ordinal)
            .Replace("-", " ", StringComparison.Ordinal)
            .Trim();
    }

    private static string? GetStringProperty(JsonElement element, string propertyName)
    {
        return element.TryGetProperty(propertyName, out var property) &&
               property.ValueKind == JsonValueKind.String
            ? property.GetString()
            : null;
    }

    private static string? GetNodeReference(JsonElement edge, string propertyName)
    {
        if (!edge.TryGetProperty(propertyName, out var property))
        {
            return null;
        }

        return property.ValueKind switch
        {
            JsonValueKind.String => property.GetString(),
            JsonValueKind.Number => property.GetRawText(),
            _ => null
        };
    }

    private static void IncrementDegree(Dictionary<string, int> degreeByNodeId, string? nodeId)
    {
        if (string.IsNullOrWhiteSpace(nodeId))
        {
            return;
        }

        degreeByNodeId[nodeId] = degreeByNodeId.GetValueOrDefault(nodeId) + 1;
    }

    private static string StripMarkdownCodeFence(string value)
    {
        var trimmed = value.Trim();
        if (!trimmed.StartsWith("```", StringComparison.Ordinal))
        {
            return trimmed;
        }

        var firstNewline = trimmed.IndexOf('\n');
        var lastFence = trimmed.LastIndexOf("```", StringComparison.Ordinal);
        if (firstNewline < 0 || lastFence <= firstNewline)
        {
            return trimmed;
        }

        return trimmed[(firstNewline + 1)..lastFence].Trim();
    }

    private static string? ExtractFirstJsonObject(string value)
    {
        var start = value.IndexOf('{');
        if (start < 0)
        {
            return null;
        }

        var depth = 0;
        var inString = false;
        var escaped = false;

        for (var i = start; i < value.Length; i++)
        {
            var ch = value[i];
            if (escaped)
            {
                escaped = false;
                continue;
            }

            if (ch == '\\' && inString)
            {
                escaped = true;
                continue;
            }

            if (ch == '"')
            {
                inString = !inString;
                continue;
            }

            if (inString)
            {
                continue;
            }

            if (ch == '{')
            {
                depth++;
            }
            else if (ch == '}')
            {
                depth--;
                if (depth == 0)
                {
                    return value[start..(i + 1)];
                }
            }
        }

        return null;
    }

    private static string TrimForLog(string value, int maxLength)
    {
        return value.Length <= maxLength ? value : value[..maxLength];
    }

    private string? ResolveBackend(ResolvedAiModel? ai)
    {
        if (ai != null)
        {
            return "openai";
        }

        if (!string.IsNullOrWhiteSpace(_options.Backend))
        {
            return _options.Backend;
        }

        return null;
    }

    private async Task<ResolvedAiModel?> ResolveGraphifyAiAsync(CancellationToken cancellationToken)
    {
        if (_aiProviderResolver == null || _settingsService == null)
        {
            return null;
        }

        var providerId = (await _settingsService.GetSettingByKeyAsync(SystemSettingDefaults.GraphifyProviderId))?.Value;
        var modelId = (await _settingsService.GetSettingByKeyAsync(SystemSettingDefaults.GraphifyModelId))?.Value;

        if (string.IsNullOrWhiteSpace(providerId) || string.IsNullOrWhiteSpace(modelId))
        {
            return null;
        }

        return await _aiProviderResolver.ResolveAsync(providerId, modelId, cancellationToken);
    }

    private async Task RunGraphifyCommandAsync(
        string workingDirectory,
        IReadOnlyList<string> arguments,
        StringBuilder logBuilder,
        CancellationToken cancellationToken,
        ResolvedAiModel? ai = null,
        IReadOnlyDictionary<string, string>? environment = null)
    {
        var startInfo = CreateGraphifyStartInfo(workingDirectory, arguments, ai, environment);

        _logger.LogInformation(
            "Running Graphify command. Command: {Command}, Arguments: {Arguments}, WorkingDirectory: {WorkingDirectory}",
            startInfo.FileName,
            string.Join(" ", startInfo.ArgumentList.Select(MaskArgumentForLog)),
            workingDirectory);

        using var process = new Process { StartInfo = startInfo, EnableRaisingEvents = true };

        if (!process.Start())
        {
            throw new InvalidOperationException($"Failed to start Graphify command: {startInfo.FileName}");
        }

        var stdoutTask = DrainStreamAsync(process.StandardOutput, logBuilder, cancellationToken);
        var stderrTask = DrainStreamAsync(process.StandardError, logBuilder, cancellationToken);

        try
        {
            await process.WaitForExitAsync(cancellationToken);
            await Task.WhenAll(stdoutTask, stderrTask);
        }
        catch
        {
            TryKill(process);
            throw;
        }

        if (process.ExitCode != 0)
        {
            throw new InvalidOperationException(
                $"Graphify command failed with exit code {process.ExitCode}: {TrimLog(logBuilder.ToString())}");
        }
    }

    private ProcessStartInfo CreateGraphifyStartInfo(
        string workingDirectory,
        IReadOnlyList<string> arguments,
        ResolvedAiModel? ai,
        IReadOnlyDictionary<string, string>? environment)
    {
        var openAiBaseUrl = ai?.BaseUrl;
        var openAiApiKey = ai?.ApiKey;
        var usePythonLauncher = !string.IsNullOrWhiteSpace(openAiBaseUrl);
        var startInfo = new ProcessStartInfo
        {
            FileName = usePythonLauncher ? _options.PythonCommand : _options.Command,
            WorkingDirectory = workingDirectory,
            RedirectStandardOutput = true,
            RedirectStandardError = true,
            UseShellExecute = false
        };

        if (usePythonLauncher)
        {
            startInfo.ArgumentList.Add("-c");
            startInfo.ArgumentList.Add(BuildOpenAiBaseUrlLauncher());
            startInfo.ArgumentList.Add(openAiBaseUrl!);
        }

        foreach (var argument in arguments)
        {
            startInfo.ArgumentList.Add(argument);
        }

        if (!string.IsNullOrWhiteSpace(openAiApiKey))
        {
            startInfo.Environment["OPENAI_API_KEY"] = openAiApiKey;
        }

        if (environment != null)
        {
            foreach (var item in environment)
            {
                startInfo.Environment[item.Key] = item.Value;
            }
        }

        return startInfo;
    }

    private static string BuildOpenAiBaseUrlLauncher()
    {
        return """
import sys
from graphify import llm
llm.BACKENDS["openai"]["base_url"] = sys.argv[1]
from graphify.__main__ import main
sys.argv = ["graphify"] + sys.argv[2:]
main()
""";
    }

    private async Task DrainStreamAsync(
        StreamReader reader,
        StringBuilder logBuilder,
        CancellationToken cancellationToken)
    {
        while (true)
        {
            var line = await reader.ReadLineAsync(cancellationToken);
            if (line == null)
            {
                break;
            }

            if (logBuilder.Length < _options.MaxLogBytes)
            {
                logBuilder.AppendLine(line);
            }
        }
    }

    private CancellationTokenSource CreateTimeoutTokenSource(CancellationToken cancellationToken)
    {
        var timeoutMinutes = _options.TimeoutMinutes <= 0 ? 60 : _options.TimeoutMinutes;
        var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);
        cts.CancelAfter(TimeSpan.FromMinutes(timeoutMinutes));
        return cts;
    }

    private string ResolveArtifactRoot(RepositoryWorkspace workspace)
    {
        if (string.IsNullOrWhiteSpace(_options.OutputRoot))
        {
            return Path.Combine(
                Directory.GetParent(workspace.WorkingDirectory)?.FullName ?? workspace.WorkingDirectory,
                "graphify",
                SanitizePathComponent(workspace.BranchName));
        }

        return Path.Combine(
            _options.OutputRoot,
            SanitizePathComponent(workspace.Organization),
            SanitizePathComponent(workspace.RepositoryName),
            SanitizePathComponent(workspace.BranchName));
    }

    private string TrimLog(string value)
    {
        if (value.Length <= _options.MaxLogBytes)
        {
            return value;
        }

        return value[^_options.MaxLogBytes..];
    }

    private static string SanitizePathComponent(string value)
    {
        var invalidChars = Path.GetInvalidFileNameChars();
        var sanitized = new string(value
            .Select(ch => invalidChars.Contains(ch) || ch is '/' or '\\' ? '_' : ch)
            .ToArray());

        return string.IsNullOrWhiteSpace(sanitized) ? "default" : sanitized;
    }

    private static string MaskArgumentForLog(string argument)
    {
        return argument.Contains("key", StringComparison.OrdinalIgnoreCase) ? "***" : argument;
    }

    private static void TryKill(Process process)
    {
        try
        {
            if (!process.HasExited)
            {
                process.Kill(entireProcessTree: true);
            }
        }
        catch
        {
            // Best-effort cleanup after cancellation/timeout.
        }
    }

    private sealed record GraphNode(
        string Id,
        string Label,
        string? SourceFile,
        string? FileType);

    private sealed record CommunitySummary(
        string Id,
        int NodeCount,
        IReadOnlyList<string> TopLabels,
        IReadOnlyList<string> TopSourceFiles);
}
