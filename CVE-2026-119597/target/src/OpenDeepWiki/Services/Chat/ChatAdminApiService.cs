using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Chat.Config;
using OpenDeepWiki.Chat.Queue;
using OpenDeepWiki.Chat.Routing;

namespace OpenDeepWiki.Services.Chat;

[MiniApi(Route = "/api/chat/admin")]
[Tags("Chat 管理")]
[Authorize(Policy = "AdminOnly")]
public class ChatAdminApiService(IChatConfigService configService, IMessageRouter messageRouter, IMessageQueue messageQueue, ILogger<ChatAdminApiService> logger)
{
    /// <summary>
    /// 获取所有 Provider 配置
    /// </summary>
    [HttpGet("/providers")]
    public async Task<IResult> GetAllProvidersAsync()
    {
        var configs = await configService.GetAllConfigsAsync();
        var providers = messageRouter.GetAllProviders();

        var result = configs.Select(c => new ProviderStatusResponse
        {
            Platform = c.Platform,
            DisplayName = c.DisplayName,
            IsEnabled = c.IsEnabled,
            IsRegistered = providers.Any(p => p.PlatformId == c.Platform),
            WebhookUrl = c.WebhookUrl,
            MessageInterval = c.MessageInterval,
            MaxRetryCount = c.MaxRetryCount
        });

        return Results.Ok(result);
    }

    /// <summary>
    /// 获取指定 Provider 配置
    /// </summary>
    [HttpGet("/providers/{platform}")]
    public async Task<IResult> GetProviderConfigAsync(string platform)
    {
        var config = await configService.GetConfigAsync(platform);
        if (config == null)
        {
            return Results.NotFound(new { error = $"Provider config not found for platform: {platform}" });
        }

        var provider = messageRouter.GetProvider(platform);

        return Results.Ok(new ProviderStatusResponse
        {
            Platform = config.Platform,
            DisplayName = config.DisplayName,
            IsEnabled = config.IsEnabled,
            IsRegistered = provider != null,
            WebhookUrl = config.WebhookUrl,
            MessageInterval = config.MessageInterval,
            MaxRetryCount = config.MaxRetryCount,
            ConfigData = config.ConfigData
        });
    }

    /// <summary>
    /// 保存 Provider 配置
    /// </summary>
    [HttpPost("/providers")]
    public async Task<IResult> SaveProviderConfigAsync([FromBody] ProviderConfigDto config)
    {
        var validationResult = configService.ValidateConfig(config);
        if (!validationResult.IsValid)
        {
            return Results.BadRequest(new
            {
                error = "Configuration validation failed",
                errors = validationResult.Errors,
                missingFields = validationResult.MissingFields
            });
        }

        await configService.SaveConfigAsync(config);

        return Results.Ok(new { status = "ok", platform = config.Platform });
    }

    /// <summary>
    /// 删除 Provider 配置
    /// </summary>
    [HttpDelete("/providers/{platform}")]
    public async Task<IResult> DeleteProviderConfigAsync(string platform)
    {
        var config = await configService.GetConfigAsync(platform);
        if (config == null)
        {
            return Results.NotFound(new { error = $"Provider config not found for platform: {platform}" });
        }

        await configService.DeleteConfigAsync(platform);

        return Results.Ok(new { status = "ok", platform });
    }

    /// <summary>
    /// 启用 Provider
    /// </summary>
    [HttpPost("/providers/{platform}/enable")]
    public async Task<IResult> EnableProviderAsync(string platform)
    {
        var config = await configService.GetConfigAsync(platform);
        if (config == null)
        {
            return Results.NotFound(new { error = $"Provider config not found for platform: {platform}" });
        }

        config.IsEnabled = true;
        await configService.SaveConfigAsync(config);
        await configService.ReloadConfigAsync(platform);

        return Results.Ok(new { status = "ok", platform, isEnabled = true });
    }

    /// <summary>
    /// 禁用 Provider
    /// </summary>
    [HttpPost("/providers/{platform}/disable")]
    public async Task<IResult> DisableProviderAsync(string platform)
    {
        var config = await configService.GetConfigAsync(platform);
        if (config == null)
        {
            return Results.NotFound(new { error = $"Provider config not found for platform: {platform}" });
        }

        config.IsEnabled = false;
        await configService.SaveConfigAsync(config);
        await configService.ReloadConfigAsync(platform);

        return Results.Ok(new { status = "ok", platform, isEnabled = false });
    }

    /// <summary>
    /// 重载 Provider 配置
    /// </summary>
    [HttpPost("/providers/{platform}/reload")]
    public async Task<IResult> ReloadProviderConfigAsync(string platform)
    {
        var config = await configService.GetConfigAsync(platform);
        if (config == null)
        {
            return Results.NotFound(new { error = $"Provider config not found for platform: {platform}" });
        }

        await configService.ReloadConfigAsync(platform);

        return Results.Ok(new { status = "ok", platform, message = "Configuration reloaded" });
    }

    /// <summary>
    /// 获取队列状态
    /// </summary>
    [HttpGet("/queue/status")]
    public async Task<IResult> GetQueueStatusAsync()
    {
        var queueLength = await messageQueue.GetQueueLengthAsync();
        var deadLetterCount = await messageQueue.GetDeadLetterCountAsync();

        return Results.Ok(new QueueStatusResponse
        {
            PendingCount = queueLength,
            DeadLetterCount = deadLetterCount,
            Timestamp = DateTimeOffset.UtcNow
        });
    }

    /// <summary>
    /// 获取死信队列消息
    /// </summary>
    [HttpGet("/queue/deadletter")]
    public async Task<IResult> GetDeadLetterMessagesAsync([FromQuery] int skip, [FromQuery] int take)
    {
        if (take <= 0) take = 20;
        if (take > 100) take = 100;
        if (skip < 0) skip = 0;

        var messages = await messageQueue.GetDeadLetterMessagesAsync(skip, take);
        var total = await messageQueue.GetDeadLetterCountAsync();

        return Results.Ok(new DeadLetterListResponse
        {
            Messages = messages.Select(m => new DeadLetterMessageResponse
            {
                MessageId = m.Id,
                Platform = m.Message.Platform,
                TargetUserId = m.TargetUserId,
                Content = m.Message.Content,
                FailureReason = m.ErrorMessage ?? string.Empty,
                RetryCount = m.RetryCount,
                CreatedAt = m.CreatedAt,
                FailedAt = m.FailedAt
            }).ToList(),
            Total = total,
            Skip = skip,
            Take = take
        });
    }

    /// <summary>
    /// 重新处理死信消息
    /// </summary>
    [HttpPost("/queue/deadletter/{messageId}/reprocess")]
    public async Task<IResult> ReprocessDeadLetterAsync(string messageId)
    {
        var success = await messageQueue.ReprocessDeadLetterAsync(messageId);

        if (!success)
        {
            return Results.NotFound(new { error = $"Dead letter message not found: {messageId}" });
        }

        logger.LogInformation("Dead letter message {MessageId} requeued for processing", messageId);

        return Results.Ok(new { status = "ok", messageId, message = "Message requeued for processing" });
    }

    /// <summary>
    /// 删除死信消息
    /// </summary>
    [HttpDelete("/queue/deadletter/{messageId}")]
    public async Task<IResult> DeleteDeadLetterAsync(string messageId)
    {
        var success = await messageQueue.DeleteDeadLetterAsync(messageId);

        if (!success)
        {
            return Results.NotFound(new { error = $"Dead letter message not found: {messageId}" });
        }

        logger.LogInformation("Dead letter message {MessageId} deleted", messageId);

        return Results.Ok(new { status = "ok", messageId, message = "Message deleted" });
    }

    /// <summary>
    /// 清空死信队列
    /// </summary>
    [HttpDelete("/queue/deadletter")]
    public async Task<IResult> ClearDeadLetterQueueAsync()
    {
        var count = await messageQueue.ClearDeadLetterQueueAsync();

        logger.LogInformation("Dead letter queue cleared, {Count} messages deleted", count);

        return Results.Ok(new { status = "ok", deletedCount = count });
    }
}

public class ProviderStatusResponse
{
    public string Platform { get; set; } = string.Empty;
    public string DisplayName { get; set; } = string.Empty;
    public bool IsEnabled { get; set; }
    public bool IsRegistered { get; set; }
    public string? WebhookUrl { get; set; }
    public int MessageInterval { get; set; }
    public int MaxRetryCount { get; set; }
    public string? ConfigData { get; set; }
}

public class QueueStatusResponse
{
    public int PendingCount { get; set; }
    public int DeadLetterCount { get; set; }
    public DateTimeOffset Timestamp { get; set; }
}

public class DeadLetterListResponse
{
    public List<DeadLetterMessageResponse> Messages { get; set; } = new();
    public int Total { get; set; }
    public int Skip { get; set; }
    public int Take { get; set; }
}

public class DeadLetterMessageResponse
{
    public string MessageId { get; set; } = string.Empty;
    public string Platform { get; set; } = string.Empty;
    public string TargetUserId { get; set; } = string.Empty;
    public string Content { get; set; } = string.Empty;
    public string FailureReason { get; set; } = string.Empty;
    public int RetryCount { get; set; }
    public DateTimeOffset CreatedAt { get; set; }
    public DateTimeOffset FailedAt { get; set; }
}
