using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Services.Auth;
using OpenDeepWiki.Services.Chat;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 用户应用管理端点
/// 提供应用CRUD、统计查询、提问记录等API
/// </summary>
public static class ChatAppEndpoints
{
    /// <summary>
    /// 注册用户应用管理端点
    /// </summary>
    public static IEndpointRouteBuilder MapChatAppEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/v1/apps")
            .WithTags("用户应用")
            .RequireAuthorization();

        // 获取用户应用列表
        group.MapGet("/", GetUserAppsAsync)
            .WithName("GetUserApps")
            .WithSummary("获取当前用户的应用列表");

        // 创建应用
        group.MapPost("/", CreateAppAsync)
            .WithName("CreateApp")
            .WithSummary("创建新应用");

        // 获取应用详情
        group.MapGet("/ai-providers", GetAiProvidersAsync)
            .WithName("GetAppAiProviders");

        group.MapGet("/ai-providers/{providerId}/models", GetAiModelsAsync)
            .WithName("GetAppAiModels");

        group.MapGet("/{id:guid}", GetAppByIdAsync)
            .WithName("GetAppById")
            .WithSummary("获取应用详情");

        // 更新应用
        group.MapPut("/{id:guid}", UpdateAppAsync)
            .WithName("UpdateApp")
            .WithSummary("更新应用配置");

        // 删除应用
        group.MapDelete("/{id:guid}", DeleteAppAsync)
            .WithName("DeleteApp")
            .WithSummary("删除应用");

        // 重新生成密钥
        group.MapPost("/{id:guid}/regenerate-secret", RegenerateSecretAsync)
            .WithName("RegenerateAppSecret")
            .WithSummary("重新生成应用密钥");

        // 获取应用统计
        group.MapGet("/{id:guid}/statistics", GetAppStatisticsAsync)
            .WithName("GetAppStatistics")
            .WithSummary("获取应用使用统计");

        // 获取提问记录
        group.MapGet("/{id:guid}/logs", GetAppLogsAsync)
            .WithName("GetAppLogs")
            .WithSummary("获取应用提问记录");

        return app;
    }


    /// <summary>
    /// 获取当前用户的应用列表
    /// </summary>
    private static async Task<IResult> GetUserAppsAsync(
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var apps = await chatAppService.GetUserAppsAsync(userContext.UserId, cancellationToken);
        return Results.Ok(apps);
    }

    /// <summary>
    /// 创建新应用
    /// </summary>
    private static async Task<IResult> CreateAppAsync(
        [FromBody] CreateChatAppDto dto,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        if (string.IsNullOrWhiteSpace(dto.Name))
        {
            return Results.BadRequest(new { message = "应用名称不能为空" });
        }

        var app = await chatAppService.CreateAppAsync(userContext.UserId, dto, cancellationToken);
        return Results.Created($"/api/v1/apps/{app.Id}", app);
    }

    /// <summary>
    /// 获取应用详情
    /// </summary>
    private static async Task<IResult> GetAiProvidersAsync(
        [FromServices] IContext context,
        CancellationToken cancellationToken)
    {
        var providers = await context.AiProviderConfigs
            .Where(p => p.IsActive && !p.IsDeleted)
            .OrderBy(p => p.SortOrder)
            .ThenBy(p => p.Name)
            .Select(p => new
            {
                p.Id,
                Name = p.DisplayName ?? p.Name,
                p.ProviderType,
                p.DefaultModelId
            })
            .ToListAsync(cancellationToken);

        return Results.Ok(providers);
    }

    private static async Task<IResult> GetAiModelsAsync(
        string providerId,
        [FromServices] IContext context,
        CancellationToken cancellationToken)
    {
        var models = await context.AiModelConfigs
            .Where(m => m.ProviderId == providerId && m.IsActive && !m.IsDeleted)
            .OrderByDescending(m => m.IsDefault)
            .ThenBy(m => m.SortOrder)
            .ThenBy(m => m.Name)
            .Select(m => new
            {
                m.Id,
                m.ModelId,
                Name = m.DisplayName ?? m.Name,
                m.IsDefault
            })
            .ToListAsync(cancellationToken);

        return Results.Ok(models);
    }

    private static async Task<IResult> GetAppByIdAsync(
        Guid id,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var app = await chatAppService.GetAppByIdAsync(id, userContext.UserId, cancellationToken);
        if (app == null)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        return Results.Ok(app);
    }


    /// <summary>
    /// 更新应用配置
    /// </summary>
    private static async Task<IResult> UpdateAppAsync(
        Guid id,
        [FromBody] UpdateChatAppDto dto,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var app = await chatAppService.UpdateAppAsync(id, userContext.UserId, dto, cancellationToken);
        if (app == null)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        return Results.Ok(app);
    }

    /// <summary>
    /// 删除应用
    /// </summary>
    private static async Task<IResult> DeleteAppAsync(
        Guid id,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var success = await chatAppService.DeleteAppAsync(id, userContext.UserId, cancellationToken);
        if (!success)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        return Results.NoContent();
    }

    /// <summary>
    /// 重新生成应用密钥
    /// </summary>
    private static async Task<IResult> RegenerateSecretAsync(
        Guid id,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        var newSecret = await chatAppService.RegenerateSecretAsync(id, userContext.UserId, cancellationToken);
        if (newSecret == null)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        return Results.Ok(new { appSecret = newSecret });
    }


    /// <summary>
    /// 获取应用使用统计
    /// </summary>
    private static async Task<IResult> GetAppStatisticsAsync(
        Guid id,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IAppStatisticsService statisticsService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken,
        [FromQuery] DateTime? startDate = null,
        [FromQuery] DateTime? endDate = null)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        // Verify the app belongs to the user
        var app = await chatAppService.GetAppByIdAsync(id, userContext.UserId, cancellationToken);
        if (app == null)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        // Default to last 30 days if not specified
        var start = startDate ?? DateTime.UtcNow.AddDays(-30);
        var end = endDate ?? DateTime.UtcNow;

        var statistics = await statisticsService.GetStatisticsAsync(app.AppId, start, end, cancellationToken);
        return Results.Ok(statistics);
    }

    /// <summary>
    /// 获取应用提问记录
    /// </summary>
    private static async Task<IResult> GetAppLogsAsync(
        Guid id,
        [FromServices] IChatAppService chatAppService,
        [FromServices] IChatLogService chatLogService,
        [FromServices] IUserContext userContext,
        CancellationToken cancellationToken,
        [FromQuery] DateTime? startDate = null,
        [FromQuery] DateTime? endDate = null,
        [FromQuery] string? keyword = null,
        [FromQuery] int page = 1,
        [FromQuery] int pageSize = 20)
    {
        if (string.IsNullOrEmpty(userContext.UserId))
        {
            return Results.Unauthorized();
        }

        // Verify the app belongs to the user
        var app = await chatAppService.GetAppByIdAsync(id, userContext.UserId, cancellationToken);
        if (app == null)
        {
            return Results.NotFound(new { message = "应用不存在" });
        }

        var query = new ChatLogQueryDto
        {
            AppId = app.AppId,
            StartDate = startDate,
            EndDate = endDate,
            Keyword = keyword,
            Page = page,
            PageSize = Math.Min(pageSize, 100) // Limit max page size
        };

        var logs = await chatLogService.GetLogsAsync(query, cancellationToken);
        return Results.Ok(logs);
    }
}
