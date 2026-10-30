using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Chat.Exceptions;
using OpenDeepWiki.Services.Auth;
using OpenDeepWiki.Services.Chat;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 对话助手端点
/// 提供文档对话助手的SSE流式API
/// </summary>
public static class ChatAssistantEndpoints
{
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    /// <summary>
    /// 注册对话助手端点
    /// </summary>
    public static IEndpointRouteBuilder MapChatAssistantEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/v1/chat")
            .WithTags("对话助手");

        // 获取助手配置
        group.MapGet("/config", GetChatConfigAsync)
            .WithName("GetChatAssistantConfig")
            .WithSummary("获取对话助手配置");

        // 获取可用模型列表
        group.MapGet("/models", GetAvailableModelsAsync)
            .WithName("GetChatAssistantModels")
            .WithSummary("获取可用模型列表");

        // SSE流式对话
        group.MapPost("/stream", StreamChatAsync)
            .WithName("StreamChat")
            .WithSummary("SSE流式对话");

        // 创建分享
        group.MapPost("/share", CreateChatShareAsync)
            .WithName("CreateChatShare")
            .WithSummary("创建对话分享")
            .RequireAuthorization();

        // 获取分享详情
        group.MapGet("/share/{shareId}", GetChatShareAsync)
            .WithName("GetChatShare")
            .WithSummary("获取对话分享详情")
            .AllowAnonymous();

        // 撤销分享
        group.MapDelete("/share/{shareId}", RevokeChatShareAsync)
            .WithName("RevokeChatShare")
            .WithSummary("撤销对话分享")
            .RequireAuthorization();

        return app;
    }

    /// <summary>
    /// 获取对话助手配置
    /// </summary>
    private static async Task<IResult> GetChatConfigAsync(
        [FromServices] IChatAssistantService chatAssistantService,
        CancellationToken cancellationToken)
    {
        var config = await chatAssistantService.GetConfigAsync(cancellationToken);
        return Results.Ok(config);
    }

    /// <summary>
    /// 获取可用模型列表
    /// </summary>
    private static async Task<IResult> GetAvailableModelsAsync(
        [FromServices] IChatAssistantService chatAssistantService,
        CancellationToken cancellationToken)
    {
        var models = await chatAssistantService.GetAvailableModelsAsync(cancellationToken);
        return Results.Ok(models);
    }

    /// <summary>
    /// 创建对话分享
    /// </summary>
    private static async Task<IResult> CreateChatShareAsync(
        [FromBody] CreateChatShareRequest request,
        [FromServices] IChatShareService chatShareService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (!userContext.IsAuthenticated || string.IsNullOrWhiteSpace(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        request.CreatedBy = userContext.UserId;
        var share = await chatShareService.CreateShareAsync(request, cancellationToken);
        return Results.Ok(share);
    }

    /// <summary>
    /// 获取对话分享
    /// </summary>
    private static async Task<IResult> GetChatShareAsync(
        string shareId,
        [FromServices] IChatShareService chatShareService,
        CancellationToken cancellationToken)
    {
        var share = await chatShareService.GetShareAsync(shareId, cancellationToken);
        if (share == null)
        {
            return Results.NotFound(new { message = "分享不存在或已失效" });
        }

        return Results.Ok(share);
    }

    /// <summary>
    /// 撤销对话分享
    /// </summary>
    private static async Task<IResult> RevokeChatShareAsync(
        string shareId,
        [FromServices] IChatShareService chatShareService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (!userContext.IsAuthenticated || string.IsNullOrWhiteSpace(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var success = await chatShareService.RevokeShareAsync(shareId, userContext.UserId, cancellationToken);
        if (!success)
        {
            return Results.NotFound(new { message = "分享不存在或无权限撤销" });
        }

        return Results.NoContent();
    }


    /// <summary>
    /// SSE流式对话
    /// </summary>
    private static async Task StreamChatAsync(
        HttpContext httpContext,
        [FromBody] ChatRequest request,
        [FromServices] IChatAssistantService chatAssistantService,
        CancellationToken cancellationToken)
    {
        // 设置SSE响应头
        httpContext.Response.ContentType = "text/event-stream";
        httpContext.Response.Headers.CacheControl = "no-cache";
        httpContext.Response.Headers.Connection = "keep-alive";
        httpContext.Response.Headers["X-Accel-Buffering"] = "no";

        try
        {
            await httpContext.Response.StartAsync(cancellationToken);
            await httpContext.Response.WriteAsync(": connected\n\n", cancellationToken);
            await httpContext.Response.Body.FlushAsync(cancellationToken);

            await foreach (var sseEvent in chatAssistantService.StreamChatAsync(request, cancellationToken))
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
    /// 格式化SSE事件
    /// </summary>
    private static string FormatSSEEvent(SSEEvent sseEvent)
    {
        var sb = new StringBuilder();
        sb.Append("data: ");
        
        // 统一使用JSON格式，包含type和data字段
        var eventPayload = new
        {
            type = sseEvent.Type,
            data = sseEvent.Data
        };
        sb.AppendLine(JsonSerializer.Serialize(eventPayload, JsonOptions));
        
        sb.AppendLine(); // SSE事件之间需要空行分隔
        return sb.ToString();
    }
}
