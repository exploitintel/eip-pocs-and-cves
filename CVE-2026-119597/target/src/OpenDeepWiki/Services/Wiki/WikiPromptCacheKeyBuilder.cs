using System.Security.Cryptography;
using System.Text;
using Microsoft.Extensions.AI;
using OpenDeepWiki.Agents;
using OpenDeepWiki.Services.AI;

namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Builds stable, low-conflict prompt cache keys for wiki generation workflows.
/// </summary>
public static class WikiPromptCacheKeyBuilder
{
    public const string EmptyToolsetHash = "no-tools";

    public static string Build(
        ResolvedAiModel ai,
        AiExecutionContext executionContext,
        string? toolsetHash)
    {
        ArgumentNullException.ThrowIfNull(ai);
        ArgumentNullException.ThrowIfNull(executionContext);

        return string.Join(
            ':',
            "odw",
            NormalizeSegment(ai.ProviderId),
            NormalizeSegment(ai.ModelId),
            NormalizeSegment(executionContext.BusinessTag),
            NormalizeSegment(executionContext.RepositoryId ?? executionContext.Repository),
            NormalizeSegment(executionContext.Branch),
            NormalizeSegment(executionContext.Language),
            NormalizeSegment(toolsetHash ?? EmptyToolsetHash));
    }

    public static string BuildToolsetHash(IEnumerable<AITool>? tools)
    {
        if (tools == null)
        {
            return EmptyToolsetHash;
        }

        var entries = tools
            .OrderBy(tool => tool.Name, StringComparer.Ordinal)
            .ThenBy(tool => tool.Description, StringComparer.Ordinal)
            .Select(tool => tool is AIFunctionDeclaration function
                ? $"{function.Name}\u001f{function.Description}\u001f{function.JsonSchema.GetRawText()}"
                : $"{tool.Name}\u001f{tool.Description}");

        var payload = string.Join('\n', entries);
        return payload.Length == 0 ? EmptyToolsetHash : ShortHash(payload);
    }

    private static string NormalizeSegment(string? value)
    {
        if (string.IsNullOrWhiteSpace(value))
        {
            return "none";
        }

        var normalized = new StringBuilder(value.Length);
        var lastWasSeparator = false;

        foreach (var character in value.Trim().ToLowerInvariant())
        {
            if (char.IsLetterOrDigit(character) || character is '-' or '_' or '.')
            {
                normalized.Append(character);
                lastWasSeparator = false;
                continue;
            }

            if (!lastWasSeparator)
            {
                normalized.Append('-');
                lastWasSeparator = true;
            }
        }

        var segment = normalized.ToString().Trim('-');
        if (segment.Length == 0)
        {
            return "none";
        }

        return segment.Length <= 48
            ? segment
            : $"{segment[..32]}-{ShortHash(segment)}";
    }

    private static string ShortHash(string value)
    {
        var hash = SHA256.HashData(Encoding.UTF8.GetBytes(value));
        return Convert.ToHexString(hash).ToLowerInvariant()[..16];
    }
}
