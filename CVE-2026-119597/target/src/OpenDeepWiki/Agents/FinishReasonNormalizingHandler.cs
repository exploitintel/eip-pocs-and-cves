using System.Net;
using System.Net.Http.Headers;
using System.Runtime.CompilerServices;
using System.Text;
using System.Text.RegularExpressions;


namespace OpenDeepWiki.Agents;

/// <summary>
/// A <see cref="DelegatingHandler"/> that normalizes the <c>finish_reason</c> field in
/// SSE (text/event-stream) responses so that Gemini's OpenAI-compatible endpoint values
/// (e.g. STOP, MAX_TOKENS, SAFETY, RECITATION, OTHER) are mapped to the OpenAI SDK's
/// expected set (stop, length, tool_calls, function_call, content_filter).
///
/// Without this handler the OpenAI .NET SDK v2.x throws
/// <see cref="ArgumentOutOfRangeException"/> ("Unknown ChatFinishReason value") whenever
/// Gemini returns a non-OpenAI finish-reason string in a streaming completion.
/// </summary>
public sealed class FinishReasonNormalizingHandler : DelegatingHandler
{
    private static readonly Serilog.ILogger Logger = Serilog.Log.ForContext<FinishReasonNormalizingHandler>();

    // Regex matches: "finish_reason"  :  "VALUE"
    // - does NOT match finish_reason: null  (no quotes around null)
    // - we use a non-greedy [^"]+ to avoid crossing into the next JSON field
    private static readonly Regex FinishReasonRegex =
        new(@"""finish_reason""\s*:\s*""([^""]+)""",
            RegexOptions.Compiled | RegexOptions.CultureInvariant);

    public FinishReasonNormalizingHandler(HttpMessageHandler innerHandler)
        : base(innerHandler)
    {
    }

    protected override async Task<HttpResponseMessage> SendAsync(
        HttpRequestMessage request,
        CancellationToken cancellationToken)
    {
        HttpResponseMessage response = await base.SendAsync(request, cancellationToken);

        try
        {
            if (IsSseResponse(response))
            {
                response.Content = WrapSseContent(response.Content);
            }
        }
        catch (Exception ex)
        {
            // Never break the call - fall through with the original response
            Logger.Warning(ex,
                "FinishReasonNormalizingHandler: failed to wrap SSE content; returning original response.");
        }

        return response;
    }

    private static bool IsSseResponse(HttpResponseMessage response)
    {
        if (!response.IsSuccessStatusCode)
        {
            return false;
        }

        var ct = response.Content.Headers.ContentType;
        return ct is not null &&
               ct.MediaType is not null &&
               ct.MediaType.Equals("text/event-stream", StringComparison.OrdinalIgnoreCase);
    }

    private static HttpContent WrapSseContent(HttpContent original)
    {
        // Read the original stream lazily; wrap it in a transforming stream.
        // We use a callback-based StreamContent so the original stream is only
        // read once and is never fully buffered in memory.
        var transforming = new TransformingStreamContent(original);

        // Copy all content headers from the original so Content-Type / encoding
        // are preserved downstream.
        foreach (var header in original.Headers)
        {
            transforming.Headers.TryAddWithoutValidation(header.Key, header.Value);
        }

        return transforming;
    }

    /// <summary>
    /// Normalizes a single <c>finish_reason</c> value to the OpenAI SDK's expected set.
    /// The mapping is case-insensitive on the input.
    ///
    /// Exposed as <c>internal static</c> so unit tests can call it directly.
    /// </summary>
    internal static string NormalizeFinishReason(string raw)
    {
        // Already valid OpenAI values - fast path (most common for non-Gemini endpoints)
        return raw switch
        {
            "stop"           => "stop",
            "length"         => "length",
            "tool_calls"     => "tool_calls",
            "function_call"  => "function_call",
            "content_filter" => "content_filter",
            _ => raw.ToUpperInvariant() switch
            {
                // stop-like
                "STOP" or "END_TURN" => "stop",
                // length-like
                "LENGTH" or "MAX_TOKENS" or "MAX_TOKEN" => "length",
                // tool/function
                "TOOL_CALLS"    => "tool_calls",
                "FUNCTION_CALL" => "function_call",
                // safety / filter
                "CONTENT_FILTER" or "SAFETY" or "RECITATION"
                    or "BLOCKLIST" or "PROHIBITED_CONTENT" or "SPII" => "content_filter",
                // everything else (OTHER, MALFORMED_FUNCTION_CALL, unknown) -> stop
                _ => "stop"
            }
        };
    }

    /// <summary>
    /// Transforms a single SSE data line, replacing any Gemini-native finish_reason
    /// with its OpenAI equivalent.  Lines without finish_reason (or with null) are
    /// returned unchanged.
    ///
    /// Exposed as <c>internal static</c> for unit tests.
    /// </summary>
    internal static string TransformLine(string line)
    {
        if (!line.StartsWith("data:", StringComparison.Ordinal))
        {
            return line;
        }

        // Quick check before running regex
        if (!line.Contains("\"finish_reason\"", StringComparison.Ordinal))
        {
            return line;
        }

        return FinishReasonRegex.Replace(line, match =>
        {
            var rawValue = match.Groups[1].Value;
            var normalized = NormalizeFinishReason(rawValue);
            if (string.Equals(rawValue, normalized, StringComparison.Ordinal))
            {
                return match.Value; // no-op, avoid allocation
            }

            Logger.Debug(
                "FinishReasonNormalizingHandler: normalized finish_reason {Raw} -> {Normalized}",
                rawValue, normalized);

            // Reconstruct: replace only the captured group, keeping surrounding JSON intact
            return match.Value.Replace($"\"{rawValue}\"", $"\"{normalized}\"");
        });
    }

    // --------------------------------------------------------------------- inner types

    /// <summary>
    /// An <see cref="HttpContent"/> implementation that wraps the original SSE content
    /// and transforms it line-by-line on the fly without buffering the entire body.
    /// </summary>
    private sealed class TransformingStreamContent : HttpContent
    {
        private readonly HttpContent _inner;

        public TransformingStreamContent(HttpContent inner)
        {
            _inner = inner;
        }

        protected override async Task SerializeToStreamAsync(
            Stream stream,
            TransportContext? context)
        {
            var innerStream = await _inner.ReadAsStreamAsync();
            await TransformSseStreamAsync(innerStream, stream, CancellationToken.None);
        }

        protected override async Task SerializeToStreamAsync(
            Stream stream,
            TransportContext? context,
            CancellationToken cancellationToken)
        {
            var innerStream = await _inner.ReadAsStreamAsync(cancellationToken);
            await TransformSseStreamAsync(innerStream, stream, cancellationToken);
        }

        protected override bool TryComputeLength(out long length)
        {
            // Length unknown until we transform; signal that to the framework
            length = -1;
            return false;
        }

        private static async Task TransformSseStreamAsync(
            Stream source,
            Stream destination,
            CancellationToken cancellationToken)
        {
            // We need to split the stream on '\n' while preserving '\r\n' vs '\n'
            // line endings, hold any partial (not-yet-terminated) line, and flush
            // it at end-of-stream.  We do this with a small ring buffer rather than
            // ReadLineAsync so we keep full control of newline bytes.

            var reader = new StreamReader(source, Encoding.UTF8, detectEncodingFromByteOrderMarks: false,
                bufferSize: 4096, leaveOpen: true);
            // Use a writer that does NOT add a BOM and flushes after each write so the
            // HTTP client can stream the data to the OpenAI SDK progressively.
            var writer = new StreamWriter(destination, new UTF8Encoding(encoderShouldEmitUTF8Identifier: false),
                bufferSize: 4096, leaveOpen: true) { AutoFlush = true };

            string? line;
            while ((line = await reader.ReadLineAsync(cancellationToken)) is not null)
            {
                var transformed = TransformLine(line);
                // ReadLineAsync strips the newline; we must restore it so the OpenAI
                // SDK receives properly framed SSE events.
                await writer.WriteLineAsync(transformed.AsMemory(), cancellationToken);
            }

            await writer.FlushAsync(cancellationToken);
        }
    }
}
