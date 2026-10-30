using System.Net.Http.Headers;
using System.Reflection;
using System.Text;
using System.Text.Json;
using System.Text.Json.Nodes;
using Microsoft.Extensions.Options;

namespace OpenDeepWiki.Services.Graphify;

/// <summary>
/// Opt-in publish to the understand-quickly registry of code-knowledge graphs.
/// https://github.com/looptech-ai/understand-quickly
/// </summary>
public class UnderstandQuicklyOptions
{
    public const string SectionName = "UnderstandQuickly";

    public bool Enabled { get; set; } = false;
    public string? Token { get; set; }
    public string RegistryRepo { get; set; } = "looptech-ai/understand-quickly";
    public string Schema { get; set; } = "gitnexus@1";
}

public sealed record UnderstandQuicklyPublishResult(
    bool MetadataStamped,
    bool Dispatched,
    string? Error = null);

public interface IUnderstandQuicklyPublisher
{
    Task<UnderstandQuicklyPublishResult> PublishAsync(
        GraphifyRunResult result,
        string repoSlug,
        CancellationToken cancellationToken = default);
}

public class UnderstandQuicklyPublisher : IUnderstandQuicklyPublisher
{
    private const string ToolName = "opendeepwiki";
    private const string DispatchEventType = "uq-publish";

    private readonly UnderstandQuicklyOptions _options;
    private readonly ILogger<UnderstandQuicklyPublisher> _logger;
    private readonly HttpClient _httpClient;

    public UnderstandQuicklyPublisher(
        IOptions<UnderstandQuicklyOptions> options,
        ILogger<UnderstandQuicklyPublisher> logger,
        HttpClient httpClient)
    {
        _options = options.Value;
        _logger = logger;
        _httpClient = httpClient;
    }

    public async Task<UnderstandQuicklyPublishResult> PublishAsync(
        GraphifyRunResult result,
        string repoSlug,
        CancellationToken cancellationToken = default)
    {
        if (!_options.Enabled)
        {
            return new UnderstandQuicklyPublishResult(false, false);
        }
        if (string.IsNullOrWhiteSpace(result.GraphJsonPath) || !File.Exists(result.GraphJsonPath))
        {
            return new UnderstandQuicklyPublishResult(false, false,
                $"graph.json not found at {result.GraphJsonPath}");
        }

        var toolVersion = Assembly.GetExecutingAssembly().GetName().Version?.ToString() ?? "unknown";

        JsonObject root;
        try
        {
            await using (var read = File.OpenRead(result.GraphJsonPath))
            {
                var node = await JsonNode.ParseAsync(read, cancellationToken: cancellationToken)
                    ?? new JsonObject();
                root = node.AsObject();
            }

            var metadata = root["metadata"]?.AsObject() ?? new JsonObject();
            metadata["tool"] = ToolName;
            metadata["tool_version"] = toolVersion;
            metadata["generated_at"] = DateTime.UtcNow.ToString("yyyy-MM-ddTHH:mm:ssZ");
            if (!string.IsNullOrWhiteSpace(result.CommitId))
            {
                metadata["commit"] = result.CommitId;
            }
            root["metadata"] = metadata;

            await File.WriteAllTextAsync(
                result.GraphJsonPath,
                root.ToJsonString(new JsonSerializerOptions { WriteIndented = true }),
                cancellationToken);
        }
        catch (OperationCanceledException) when (cancellationToken.IsCancellationRequested)
        {
            throw;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to stamp metadata into {Path}", result.GraphJsonPath);
            return new UnderstandQuicklyPublishResult(false, false, ex.Message);
        }

        if (string.IsNullOrWhiteSpace(_options.Token))
        {
            _logger.LogInformation(
                "UnderstandQuickly: stamped {Path}; token unset, skipping registry dispatch.",
                result.GraphJsonPath);
            return new UnderstandQuicklyPublishResult(true, false);
        }
        if (string.IsNullOrWhiteSpace(repoSlug) || !repoSlug.Contains('/'))
        {
            return new UnderstandQuicklyPublishResult(true, false, $"invalid repo slug: '{repoSlug}'");
        }

        // The understand-quickly registry fetches graphs from raw.githubusercontent.com,
        // so it expects a repo-relative path (e.g. "graphify-out/graph.json"), not the
        // absolute on-disk path under <outputRoot>. Fall back to the file name when
        // OutputRoot can't be resolved.
        var graphPath = ToRepoRelativePath(result.OutputRoot, result.GraphJsonPath);

        var payload = new JsonObject
        {
            ["event_type"] = DispatchEventType,
            ["client_payload"] = new JsonObject
            {
                ["repo"] = repoSlug,
                ["schema"] = _options.Schema,
                ["graph_path"] = graphPath,
                ["tool"] = ToolName,
                ["tool_version"] = toolVersion,
                ["commit"] = result.CommitId,
            },
        };

        try
        {
            using var request = new HttpRequestMessage(HttpMethod.Post,
                $"https://api.github.com/repos/{_options.RegistryRepo}/dispatches")
            {
                Content = new StringContent(payload.ToJsonString(), Encoding.UTF8, "application/json"),
            };
            request.Headers.Accept.Add(new MediaTypeWithQualityHeaderValue("application/vnd.github+json"));
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _options.Token);
            request.Headers.UserAgent.ParseAdd($"{ToolName}/{toolVersion}");
            request.Headers.Add("X-GitHub-Api-Version", "2022-11-28");

            using var response = await _httpClient.SendAsync(request, cancellationToken);
            if (response.IsSuccessStatusCode)
            {
                _logger.LogInformation(
                    "UnderstandQuickly: dispatched to {Registry} for {Slug} (HTTP {Code}).",
                    _options.RegistryRepo, repoSlug, (int)response.StatusCode);
                return new UnderstandQuicklyPublishResult(true, true);
            }
            if (response.StatusCode == System.Net.HttpStatusCode.NotFound)
            {
                _logger.LogInformation(
                    "UnderstandQuickly: {Slug} not in registry — register once with `npx @understand-quickly/cli add`.",
                    repoSlug);
                return new UnderstandQuicklyPublishResult(true, false, "repo not registered");
            }
            // Surface enough detail for ops triage on 401/403/422/etc.
            string responseBody;
            try
            {
                responseBody = await response.Content.ReadAsStringAsync(cancellationToken);
            }
            catch
            {
                responseBody = string.Empty;
            }
            if (responseBody.Length > 500)
            {
                responseBody = responseBody[..500];
            }
            _logger.LogWarning(
                "UnderstandQuickly: dispatch to {Registry} for {Slug} returned HTTP {Code}. Body: {Body}",
                _options.RegistryRepo, repoSlug, (int)response.StatusCode, responseBody);
            return new UnderstandQuicklyPublishResult(true, false, $"HTTP {(int)response.StatusCode}");
        }
        catch (OperationCanceledException) when (cancellationToken.IsCancellationRequested)
        {
            throw;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "UnderstandQuickly: dispatch failed; metadata still stamped.");
            return new UnderstandQuicklyPublishResult(true, false, ex.Message);
        }
    }

    /// <summary>
    /// Convert an absolute graph.json path under <paramref name="outputRoot"/> into
    /// a forward-slash, repo-relative path. Avoids leaking server filesystem paths
    /// into the registry payload (and into GitHub Action logs).
    /// </summary>
    public static string ToRepoRelativePath(string? outputRoot, string graphJsonPath)
    {
        if (string.IsNullOrWhiteSpace(graphJsonPath))
        {
            return string.Empty;
        }
        if (!string.IsNullOrWhiteSpace(outputRoot))
        {
            try
            {
                var rel = Path.GetRelativePath(outputRoot, graphJsonPath);
                if (!rel.StartsWith("..", StringComparison.Ordinal) &&
                    !Path.IsPathRooted(rel))
                {
                    return rel.Replace(Path.DirectorySeparatorChar, '/');
                }
            }
            catch (ArgumentException)
            {
                // Fall through to the file-name fallback.
            }
        }
        return Path.GetFileName(graphJsonPath);
    }
}
