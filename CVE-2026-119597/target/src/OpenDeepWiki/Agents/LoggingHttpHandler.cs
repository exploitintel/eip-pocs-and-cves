using System.Net;
using System.Text;
using System.Text.Json;
using System.Text.Json.Nodes;
using Serilog;

namespace OpenDeepWiki.Agents;

/// <summary>
/// 自定义 HTTP 消息处理器，用于拦截和记录请求/响应状态
/// 支持 502/429 错误自动重试
/// </summary>
public class LoggingHttpHandler(HttpMessageHandler innerHandler) : DelegatingHandler(innerHandler)
{
    private static readonly Serilog.ILogger Logger = Log.ForContext<LoggingHttpHandler>();
    private const int MaxRetryAttempts = 3;
    private static readonly TimeSpan DefaultRetryDelay = TimeSpan.FromSeconds(5);
    private static readonly TimeSpan MaxRetryDelay = TimeSpan.FromSeconds(60);

    public LoggingHttpHandler() : this(new HttpClientHandler())
    {
    }

    protected override async Task<HttpResponseMessage> SendAsync(
        HttpRequestMessage request,
        CancellationToken cancellationToken)
    {
        var requestId = Guid.NewGuid().ToString("N")[..8];
        var startTime = DateTime.UtcNow;
        var aiContext = AiExecutionScope.Current?.ToSummary() ?? "tag=unlabeled | desc=未标记AI请求";

        await NormalizeToolCallArgumentsAsync(requestId, request, cancellationToken);

        Logger.Information(
            "[{RequestId}] [{AiContext}] >>> Request: {Method} {RequestUri}",
            requestId,
            aiContext,
            request.Method,
            request.RequestUri);

        var attempt = 0;
        HttpResponseMessage? response = null;

        while (attempt < MaxRetryAttempts)
        {
            attempt++;

            try
            {
                // 如果是重试，需要克隆请求（因为原请求可能已被消费）
                var requestToSend = attempt == 1 ? request : await CloneRequestAsync(request);

                response = await base.SendAsync(requestToSend, cancellationToken);

                // 检查是否需要重试
                if (ShouldRetry(response.StatusCode) && attempt < MaxRetryAttempts)
                {
                    var retryDelay = GetRetryDelay(response, attempt);
                    Logger.Warning(
                        "[{RequestId}] [{AiContext}] Retry scheduled after response. DelaySeconds: {DelaySeconds}, NextAttempt: {NextAttempt}, StatusCode: {StatusCode}",
                        requestId,
                        aiContext,
                        retryDelay.TotalSeconds,
                        attempt + 1,
                        (int)response.StatusCode);

                    response.Dispose();
                    await Task.Delay(retryDelay, cancellationToken);
                    continue;
                }

                break;
            }
            catch (Exception ex) when (attempt < MaxRetryAttempts && IsTransientException(ex))
            {
                var retryDelay = GetExponentialDelay(attempt);
                Logger.Warning(
                    ex,
                    "[{RequestId}] [{AiContext}] Transient request error, retrying. DelaySeconds: {DelaySeconds}, NextAttempt: {NextAttempt}",
                    requestId,
                    aiContext,
                    retryDelay.TotalSeconds,
                    attempt + 1);

                await Task.Delay(retryDelay, cancellationToken);
            }
            catch (Exception ex)
            {
                var elapsed = DateTime.UtcNow - startTime;
                Logger.Error(
                    ex,
                    "[{RequestId}] [{AiContext}] Request failed. DurationMs: {DurationMs}",
                    requestId,
                    aiContext,
                    elapsed.TotalMilliseconds);
                throw;
            }
        }

        var totalElapsed = DateTime.UtcNow - startTime;

        if (response != null)
        {
            Logger.Information(
                "[{RequestId}] [{AiContext}] <<< Response: {StatusCode} {StatusName} | DurationMs: {DurationMs} | Attempts: {Attempts}",
                requestId,
                aiContext,
                (int)response.StatusCode,
                response.StatusCode,
                totalElapsed.TotalMilliseconds,
                attempt);

            if (!response.IsSuccessStatusCode)
            {
                var content = await response.Content.ReadAsStringAsync(cancellationToken);
                Logger.Warning(
                    "[{RequestId}] [{AiContext}] Error response body: {ErrorBody}",
                    requestId,
                    aiContext,
                    content[..Math.Min(500, content.Length)]);
            }
        }

        return response!;
    }

    /// <summary>
    /// Rewrites <c>messages[].tool_calls[].function.arguments</c> values that are
    /// null / JSON null / empty into <c>"{}"</c>.
    /// <para>
    /// Zero-parameter tools (e.g. <c>ReadCatalog</c>) are often called with no
    /// arguments. The OpenAI .NET serializer then re-emits that history entry as
    /// <c>"arguments":"null"</c>. Strict JSON chat backends (for example MiniMax)
    /// reject that payload. Normalizing to an empty JSON object keeps the request
    /// valid for every provider.
    /// </para>
    /// <para>
    /// The JSON DOM is mutated field-by-field rather than via string replace, so
    /// document or catalog content that happens to contain the literal text
    /// <c>"arguments":"null"</c> is never corrupted. Best-effort: any failure
    /// leaves the request untouched.
    /// </para>
    /// </summary>
    private static async Task NormalizeToolCallArgumentsAsync(
        string requestId,
        HttpRequestMessage request,
        CancellationToken cancellationToken)
    {
        try
        {
            if (request.Content == null)
            {
                return;
            }

            var mediaType = request.Content.Headers.ContentType?.MediaType;
            if (mediaType != null && !mediaType.Contains("json", StringComparison.OrdinalIgnoreCase))
            {
                return;
            }

            var body = await request.Content.ReadAsStringAsync(cancellationToken);
            if (body.Length == 0 || !body.Contains("\"tool_calls\"", StringComparison.Ordinal))
            {
                return;
            }

            JsonNode? root;
            try
            {
                root = JsonNode.Parse(body);
            }
            catch (JsonException)
            {
                return;
            }

            if (root is not JsonObject rootObject || rootObject["messages"] is not JsonArray messages)
            {
                return;
            }

            var fixedCount = 0;
            foreach (var message in messages)
            {
                if (message is not JsonObject messageObject ||
                    messageObject["tool_calls"] is not JsonArray toolCalls)
                {
                    continue;
                }

                foreach (var toolCall in toolCalls)
                {
                    if (toolCall is not JsonObject toolCallObject ||
                        toolCallObject["function"] is not JsonObject functionObject)
                    {
                        continue;
                    }

                    if (NeedsEmptyArguments(functionObject["arguments"]))
                    {
                        functionObject["arguments"] = "{}";
                        fixedCount++;
                    }
                }
            }

            if (fixedCount == 0)
            {
                return;
            }

            var rewritten = root.ToJsonString();
            request.Content = new StringContent(rewritten, Encoding.UTF8, mediaType ?? "application/json");

            Logger.Information(
                "[{RequestId}] Normalized {FixedCount} tool-call argument(s) from null/empty to '{{}}'",
                requestId,
                fixedCount);
        }
        catch (Exception ex)
        {
            Logger.Warning(
                ex,
                "[{RequestId}] Tool-call argument normalization skipped",
                requestId);
        }
    }

    /// <summary>
    /// True when a tool-call <c>arguments</c> node is null, JSON null, or a
    /// string that is empty/whitespace or the literal <c>"null"</c>.
    /// </summary>
    public static bool NeedsEmptyArguments(JsonNode? argumentsNode)
    {
        if (argumentsNode is null)
        {
            return true;
        }

        if (argumentsNode.GetValueKind() == JsonValueKind.Null)
        {
            return true;
        }

        if (argumentsNode.GetValueKind() == JsonValueKind.String)
        {
            var value = argumentsNode.GetValue<string>();
            return string.IsNullOrWhiteSpace(value) ||
                   string.Equals(value.Trim(), "null", StringComparison.OrdinalIgnoreCase);
        }

        return false;
    }

    private static bool ShouldRetry(HttpStatusCode statusCode)
    {
        var i = (int)statusCode;
        if (i >= 500)
        {
            return true;
        }

        return statusCode is HttpStatusCode.BadGateway or HttpStatusCode.TooManyRequests
            or HttpStatusCode.ServiceUnavailable or HttpStatusCode.GatewayTimeout;
    }

    private static bool IsTransientException(Exception ex)
    {
        return ex is HttpRequestException or TaskCanceledException { InnerException: TimeoutException };
    }

    private static TimeSpan GetRetryDelay(HttpResponseMessage response, int attempt)
    {
        // 优先使用 Retry-After 头
        if (response.Headers.RetryAfter != null)
        {
            if (response.Headers.RetryAfter.Delta.HasValue)
            {
                var delay = response.Headers.RetryAfter.Delta.Value;
                return delay > MaxRetryDelay ? MaxRetryDelay : delay;
            }

            if (response.Headers.RetryAfter.Date.HasValue)
            {
                var delay = response.Headers.RetryAfter.Date.Value - DateTimeOffset.UtcNow;
                if (delay > TimeSpan.Zero)
                {
                    return delay > MaxRetryDelay ? MaxRetryDelay : delay;
                }
            }
        }

        // 使用指数退避
        return GetExponentialDelay(attempt);
    }

    private static TimeSpan GetExponentialDelay(int attempt)
    {
        var delay = TimeSpan.FromSeconds(Math.Pow(2, attempt - 1) * DefaultRetryDelay.TotalSeconds);
        return delay > MaxRetryDelay ? MaxRetryDelay : delay;
    }

    private static async Task<HttpRequestMessage> CloneRequestAsync(HttpRequestMessage request)
    {
        var clone = new HttpRequestMessage(request.Method, request.RequestUri)
        {
            Version = request.Version
        };

        // 复制内容
        if (request.Content != null)
        {
            var content = await request.Content.ReadAsByteArrayAsync();
            clone.Content = new ByteArrayContent(content);

            // 复制内容头
            foreach (var header in request.Content.Headers)
            {
                clone.Content.Headers.TryAddWithoutValidation(header.Key, header.Value);
            }
        }

        // 复制请求头
        foreach (var header in request.Headers)
        {
            clone.Headers.TryAddWithoutValidation(header.Key, header.Value);
        }

        // 复制选项
        foreach (var option in request.Options)
        {
            clone.Options.TryAdd(option.Key, option.Value);
        }

        return clone;
    }
}
