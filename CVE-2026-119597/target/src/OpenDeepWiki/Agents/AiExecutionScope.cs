using Microsoft.Extensions.Logging;
using Serilog.Context;

namespace OpenDeepWiki.Agents;

/// <summary>
/// Carries business context for outbound AI requests so logs can identify
/// which workflow is currently invoking a model.
/// </summary>
public sealed class AiExecutionContext
{
    public string BusinessTag { get; init; } = string.Empty;
    public string Description { get; init; } = string.Empty;
    public string? RepositoryId { get; init; }
    public string? Repository { get; init; }
    public string? Branch { get; init; }
    public string? Language { get; init; }
    public string? DocumentPath { get; init; }
    public string? AppId { get; init; }
    public string? SessionId { get; init; }
    public string? MessageId { get; init; }
    public string? Platform { get; init; }
    public string? UserId { get; init; }
    public string? ModelId { get; init; }

    public IReadOnlyDictionary<string, object> ToScopeValues()
    {
        var values = new Dictionary<string, object>
        {
            ["AiBusinessTag"] = BusinessTag,
            ["AiBusinessDescription"] = Description,
            ["AiContextSummary"] = ToSummary()
        };

        AddIfPresent(values, "AiRepositoryId", RepositoryId);
        AddIfPresent(values, "AiRepository", Repository);
        AddIfPresent(values, "AiBranch", Branch);
        AddIfPresent(values, "AiLanguage", Language);
        AddIfPresent(values, "AiDocumentPath", DocumentPath);
        AddIfPresent(values, "AiAppId", AppId);
        AddIfPresent(values, "AiSessionId", SessionId);
        AddIfPresent(values, "AiMessageId", MessageId);
        AddIfPresent(values, "AiPlatform", Platform);
        AddIfPresent(values, "AiUserId", UserId);
        AddIfPresent(values, "AiModelId", ModelId);

        return values;
    }

    public string ToSummary()
    {
        var parts = new List<string>
        {
            $"tag={BusinessTag}",
            $"desc={Description}"
        };

        AddIfPresent(parts, "repo", Repository);
        AddIfPresent(parts, "branch", Branch);
        AddIfPresent(parts, "lang", Language);
        AddIfPresent(parts, "path", DocumentPath);
        AddIfPresent(parts, "app", AppId);
        AddIfPresent(parts, "session", SessionId);
        AddIfPresent(parts, "message", MessageId);
        AddIfPresent(parts, "platform", Platform);
        AddIfPresent(parts, "user", UserId);
        AddIfPresent(parts, "model", ModelId);

        return string.Join(" | ", parts);
    }

    private static void AddIfPresent(IDictionary<string, object> values, string key, string? value)
    {
        if (!string.IsNullOrWhiteSpace(value))
        {
            values[key] = value;
        }
    }

    private static void AddIfPresent(ICollection<string> parts, string key, string? value)
    {
        if (!string.IsNullOrWhiteSpace(value))
        {
            parts.Add($"{key}={value}");
        }
    }
}

/// <summary>
/// Ambient scope for flowing AI business context into request logs.
/// </summary>
public sealed class AiExecutionScope : IDisposable
{
    private static readonly AsyncLocal<AiExecutionContext?> CurrentHolder = new();

    private readonly AiExecutionContext? _previous;
    private readonly Stack<IDisposable> _disposables = new();
    private bool _disposed;

    private AiExecutionScope(AiExecutionContext? previous)
    {
        _previous = previous;
    }

    public static AiExecutionContext? Current => CurrentHolder.Value;

    public static AiExecutionScope Begin(AiExecutionContext context, ILogger? logger = null)
    {
        ArgumentNullException.ThrowIfNull(context);

        var scope = new AiExecutionScope(CurrentHolder.Value);
        CurrentHolder.Value = context;

        var scopeValues = context.ToScopeValues();
        if (logger != null)
        {
            var loggerScope = logger.BeginScope(scopeValues);
            if (loggerScope != null)
            {
                scope._disposables.Push(loggerScope);
            }
        }

        foreach (var property in scopeValues)
        {
            scope._disposables.Push(LogContext.PushProperty(property.Key, property.Value));
        }

        return scope;
    }

    public static AiExecutionScope Begin(ILogger logger, AiExecutionContext context)
    {
        return Begin(context, logger);
    }

    public void Dispose()
    {
        if (_disposed)
        {
            return;
        }

        _disposed = true;

        while (_disposables.Count > 0)
        {
            _disposables.Pop().Dispose();
        }

        CurrentHolder.Value = _previous;
    }
}
