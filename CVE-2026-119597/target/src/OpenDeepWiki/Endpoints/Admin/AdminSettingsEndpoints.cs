using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// Admin settings endpoints
/// </summary>
public static class AdminSettingsEndpoints
{
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNameCaseInsensitive = true
    };

    public static RouteGroupBuilder MapAdminSettingsEndpoints(this RouteGroupBuilder group)
    {
        var settingsGroup = group.MapGroup("/settings")
            .WithTags("管理端-设置");

        // 获取设置列表
        settingsGroup.MapGet("/", async (
            [FromQuery] string? category,
            [FromServices] IAdminSettingsService settingsService) =>
        {
            var settings = await settingsService.GetSettingsAsync(category);
            return Results.Ok(new { success = true, data = settings });
        })
        .WithName("AdminGetSettings")
        .WithSummary("获取设置列表");

        // 获取单个设置
        settingsGroup.MapGet("/{key}", async (
            string key,
            [FromServices] IAdminSettingsService settingsService) =>
        {
            var setting = await settingsService.GetSettingByKeyAsync(key);
            if (setting == null)
                return Results.NotFound(new { success = false, message = "设置不存在" });
            return Results.Ok(new { success = true, data = setting });
        })
        .WithName("AdminGetSettingByKey")
        .WithSummary("获取单个设置");

        // 更新设置
        settingsGroup.MapPut("/", async (
            [FromBody] List<UpdateSettingRequest> requests,
            [FromServices] IAdminSettingsService settingsService,
            [FromServices] IDynamicConfigManager configManager) =>
        {
            await settingsService.UpdateSettingsAsync(requests);

            // 刷新配置以应用新的设置
            await configManager.RefreshWikiGeneratorOptionsAsync();

            return Results.Ok(new { success = true, message = "设置更新成功" });
        })
        .WithName("AdminUpdateSettings")
        .WithSummary("更新设置");

        // List available models from a configured provider
        settingsGroup.MapPost("/list-provider-models", async (
            [FromBody] ListProviderModelsRequest request,
            [FromServices] IAdminToolsService toolsService,
            CancellationToken cancellationToken) =>
        {
            if (string.IsNullOrWhiteSpace(request.ProviderId))
            {
                return Results.BadRequest(new { success = false, message = "providerId is required." });
            }

            try
            {
                var configured = await toolsService.GetAiModelsAsync(request.ProviderId);
                var models = configured
                    .Where(m => m.IsActive)
                    .Select(m => new ProviderModelInfo(m.ModelId, m.DisplayName ?? m.Name))
                    .ToList();

                if (models.Count == 0)
                {
                    var discovered = await toolsService.DiscoverAiModelsAsync(request.ProviderId, cancellationToken);
                    models = discovered
                        .Where(m => m.IsActive)
                        .Select(m => new ProviderModelInfo(m.ModelId, m.DisplayName ?? m.Name))
                        .ToList();
                }

                return Results.Ok(new { success = true, data = new { models } });
            }
            catch (TaskCanceledException)
            {
                return Results.BadRequest(new
                {
                    success = false,
                    message = "Request to provider timed out after 15 seconds."
                });
            }
            catch (HttpRequestException ex)
            {
                return Results.BadRequest(new
                {
                    success = false,
                    message = $"Failed to connect to provider: {ex.Message}"
                });
            }
            catch (JsonException ex)
            {
                return Results.BadRequest(new
                {
                    success = false,
                    message = $"Failed to parse provider response: {ex.Message}"
                });
            }
        })
        .WithName("AdminListProviderModels")
        .WithSummary("List available models from a provider endpoint");

        return group;
    }

    /// <summary>
    /// Request body for listing provider models.
    /// </summary>
    internal record ListProviderModelsRequest(string ProviderId);

    /// <summary>
    /// Model info returned from a provider.
    /// </summary>
    internal record ProviderModelInfo(string Id, string DisplayName);
}
