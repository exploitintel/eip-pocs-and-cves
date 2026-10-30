using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Chat.Exceptions;
using OpenDeepWiki.Services.Chat;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 嵌入脚本API端点
/// 提供嵌入模式的配置获取和SSE流式对话
/// </summary>
public static class EmbedEndpoints
{
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    /// <summary>
    /// 注册嵌入脚本端点
    /// </summary>
    public static IEndpointRouteBuilder MapEmbedEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/v1/embed")
            .WithTags("嵌入脚本");

        // 获取嵌入配置
        group.MapGet("/config", GetEmbedConfigAsync)
            .WithName("GetEmbedConfig")
            .WithSummary("获取嵌入配置（验证AppId和域名）");

        // 嵌入模式SSE流式对话
        group.MapPost("/stream", StreamEmbedChatAsync)
            .WithName("StreamEmbedChat")
            .WithSummary("嵌入模式SSE流式对话");

        return app;
    }

    /// <summary>
    /// 获取嵌入配置
    /// 验证AppId和域名，返回应用配置
    /// </summary>
    private static async Task<IResult> GetEmbedConfigAsync(
        HttpContext httpContext,
        [FromQuery] string appId,
        [FromServices] IEmbedService embedService,
        CancellationToken cancellationToken)
    {
        // Get the origin domain from request headers
        var origin = httpContext.Request.Headers.Origin.FirstOrDefault();
        var referer = httpContext.Request.Headers.Referer.FirstOrDefault();
        var domain = ExtractDomain(origin) ?? ExtractDomain(referer);

        var config = await embedService.GetAppConfigAsync(appId, domain, cancellationToken);

        if (!config.Valid)
        {
            return Results.Json(config, statusCode: config.ErrorCode == "DOMAIN_NOT_ALLOWED" ? 403 : 401);
        }

        return Results.Ok(config);
    }

    /// <summary>
    /// 嵌入模式SSE流式对话
    /// 使用应用配置的模型和API进行对话
    /// </summary>
    private static async Task StreamEmbedChatAsync(
        HttpContext httpContext,
        [FromBody] EmbedChatRequest request,
        [FromServices] IEmbedService embedService,
        CancellationToken cancellationToken)
    {
        // 设置SSE响应头
        httpContext.Response.ContentType = "text/event-stream";
        httpContext.Response.Headers.CacheControl = "no-cache";
        httpContext.Response.Headers.Connection = "keep-alive";

        // 添加CORS头以支持跨域请求
        httpContext.Response.Headers.AccessControlAllowOrigin = "*";
        httpContext.Response.Headers.AccessControlAllowMethods = "POST, OPTIONS";
        httpContext.Response.Headers.AccessControlAllowHeaders = "Content-Type";

        // Get the origin domain from request headers
        var origin = httpContext.Request.Headers.Origin.FirstOrDefault();
        var referer = httpContext.Request.Headers.Referer.FirstOrDefault();
        var sourceDomain = ExtractDomain(origin) ?? ExtractDomain(referer);

        try
        {
            await foreach (var sseEvent in embedService.StreamEmbedChatAsync(request, sourceDomain, cancellationToken))
            {
                var eventData = FormatSSEEvent(sseEvent);
                await httpContext.Response.WriteAsync(eventData, cancellationToken);
                await httpContext.Response.Body.FlushAsync(cancellationToken);
            }
        }
        catch (TaskCanceledException ex) when (ex.CancellationToken != cancellationToken)
        {
            // 请求超时（非客户端取消）
            var errorEvent = new SSEEvent
            {
                Type = SSEEventType.Error,
                Data = SSEErrorResponse.CreateRetryable(
                    ChatErrorCodes.REQUEST_TIMEOUT,
                    "请求超时，请重试",
                    2000)
            };
            var eventData = FormatSSEEvent(errorEvent);
            await httpContext.Response.WriteAsync(eventData, cancellationToken);
            await httpContext.Response.Body.FlushAsync(cancellationToken);
        }
        catch (OperationCanceledException)
        {
            // 客户端断开连接，正常退出
        }
        catch (HttpRequestException ex)
        {
            // 连接失败
            var errorEvent = new SSEEvent
            {
                Type = SSEEventType.Error,
                Data = SSEErrorResponse.CreateRetryable(
                    ChatErrorCodes.CONNECTION_FAILED,
                    $"连接失败: {ex.Message}",
                    1000)
            };
            var eventData = FormatSSEEvent(errorEvent);
            await httpContext.Response.WriteAsync(eventData, cancellationToken);
            await httpContext.Response.Body.FlushAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            // 发送错误事件
            var errorEvent = new SSEEvent
            {
                Type = SSEEventType.Error,
                Data = SSEErrorResponse.CreateRetryable(
                    ChatErrorCodes.INTERNAL_ERROR,
                    ex.Message,
                    3000)
            };
            var eventData = FormatSSEEvent(errorEvent);
            await httpContext.Response.WriteAsync(eventData, cancellationToken);
            await httpContext.Response.Body.FlushAsync(cancellationToken);
        }
    }

    /// <summary>
    /// 从URL中提取域名
    /// </summary>
    private static string? ExtractDomain(string? url)
    {
        if (string.IsNullOrWhiteSpace(url))
        {
            return null;
        }

        try
        {
            var uri = new Uri(url);
            return uri.Host;
        }
        catch
        {
            return url;
        }
    }

    /// <summary>
    /// 格式化SSE事件
    /// </summary>
    private static string FormatSSEEvent(SSEEvent sseEvent)
    {
        var sb = new StringBuilder();
        sb.Append("event: ");
        sb.AppendLine(sseEvent.Type);
        sb.Append("data: ");

        if (sseEvent.Data is string strData)
        {
            sb.AppendLine(strData);
        }
        else
        {
            sb.AppendLine(JsonSerializer.Serialize(sseEvent.Data, JsonOptions));
        }

        sb.AppendLine(); // SSE事件之间需要空行分隔
        return sb.ToString();
    }
}
