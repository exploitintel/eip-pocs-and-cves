using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// 管理端工具配置端点
/// </summary>
public static class AdminToolsEndpoints
{
    public static RouteGroupBuilder MapAdminToolsEndpoints(this RouteGroupBuilder group)
    {
        var toolsGroup = group.MapGroup("/tools")
            .WithTags("管理端-工具配置");

        MapMcpEndpoints(toolsGroup);
        MapSkillEndpoints(toolsGroup);
        MapAiProviderEndpoints(toolsGroup);
        MapAiModelEndpoints(toolsGroup);
        MapModelEndpoints(toolsGroup);

        return group;
    }

    private static void MapMcpEndpoints(RouteGroupBuilder group)
    {
        var mcpGroup = group.MapGroup("/mcps");

        mcpGroup.MapGet("/", async ([FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetMcpConfigsAsync();
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminGetMcps");

        mcpGroup.MapPost("/", async (
            [FromBody] McpConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.CreateMcpConfigAsync(request);
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminCreateMcp");

        mcpGroup.MapPut("/{id}", async (
            string id,
            [FromBody] McpConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.UpdateMcpConfigAsync(id, request);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminUpdateMcp");

        mcpGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.DeleteMcpConfigAsync(id);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminDeleteMcp");
    }

    private static void MapSkillEndpoints(RouteGroupBuilder group)
    {
        var skillGroup = group.MapGroup("/skills");

        // 获取所有 Skills
        skillGroup.MapGet("/", async ([FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetSkillConfigsAsync();
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminGetSkills");

        // 获取 Skill 详情
        skillGroup.MapGet("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetSkillDetailAsync(id);
            return result != null 
                ? Results.Ok(new { success = true, data = result })
                : Results.NotFound(new { success = false, message = "Skill 不存在" });
        }).WithName("AdminGetSkillDetail");

        // 上传 Skill（ZIP 压缩包）
        skillGroup.MapPost("/upload", async (
            HttpRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            if (!request.HasFormContentType)
            {
                return Results.BadRequest(new { success = false, message = "请使用 multipart/form-data 格式" });
            }

            var form = await request.ReadFormAsync();
            var file = form.Files.GetFile("file");
            
            if (file == null || file.Length == 0)
            {
                return Results.BadRequest(new { success = false, message = "请上传 ZIP 文件" });
            }

            if (!file.FileName.EndsWith(".zip", StringComparison.OrdinalIgnoreCase))
            {
                return Results.BadRequest(new { success = false, message = "只支持 ZIP 格式" });
            }

            try
            {
                using var stream = file.OpenReadStream();
                var result = await toolsService.UploadSkillAsync(stream, file.FileName);
                return Results.Ok(new { success = true, data = result });
            }
            catch (InvalidOperationException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        }).WithName("AdminUploadSkill")
          .DisableAntiforgery();

        // 更新 Skill（仅管理字段）
        skillGroup.MapPut("/{id}", async (
            string id,
            [FromBody] SkillUpdateRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.UpdateSkillAsync(id, request);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminUpdateSkill");

        // 删除 Skill
        skillGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.DeleteSkillAsync(id);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminDeleteSkill");

        // 获取 Skill 文件内容
        skillGroup.MapGet("/{id}/files/{*filePath}", async (
            string id,
            string filePath,
            [FromServices] IAdminToolsService toolsService) =>
        {
            try
            {
                var content = await toolsService.GetSkillFileContentAsync(id, filePath);
                return content != null 
                    ? Results.Ok(new { success = true, data = content })
                    : Results.NotFound(new { success = false, message = "文件不存在" });
            }
            catch (UnauthorizedAccessException)
            {
                return Results.BadRequest(new { success = false, message = "非法路径" });
            }
        }).WithName("AdminGetSkillFile");

        // 从磁盘刷新 Skills
        skillGroup.MapPost("/refresh", async ([FromServices] IAdminToolsService toolsService) =>
        {
            await toolsService.RefreshSkillsFromDiskAsync();
            return Results.Ok(new { success = true, message = "刷新完成" });
        }).WithName("AdminRefreshSkills");
    }

    private static void MapModelEndpoints(RouteGroupBuilder group)
    {
        var modelGroup = group.MapGroup("/models");

        modelGroup.MapGet("/", async ([FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetModelConfigsAsync();
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminGetModels");

        modelGroup.MapPost("/", async (
            [FromBody] ModelConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.CreateModelConfigAsync(request);
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminCreateModel");

        modelGroup.MapPut("/{id}", async (
            string id,
            [FromBody] ModelConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.UpdateModelConfigAsync(id, request);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminUpdateModel");

        modelGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.DeleteModelConfigAsync(id);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminDeleteModel");
    }

    private static void MapAiProviderEndpoints(RouteGroupBuilder group)
    {
        var providerGroup = group.MapGroup("/ai-providers");

        providerGroup.MapGet("/", async ([FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetAiProvidersAsync();
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminGetAiProviders");

        providerGroup.MapPost("/", async (
            [FromBody] AiProviderConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.CreateAiProviderAsync(request);
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminCreateAiProvider");

        providerGroup.MapPut("/{id}", async (
            string id,
            [FromBody] AiProviderConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.UpdateAiProviderAsync(id, request);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminUpdateAiProvider");

        providerGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.DeleteAiProviderAsync(id);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminDeleteAiProvider");

        providerGroup.MapPost("/{id}/discover-models", async (
            string id,
            [FromServices] IAdminToolsService toolsService,
            CancellationToken cancellationToken) =>
        {
            try
            {
                var result = await toolsService.DiscoverAiModelsAsync(id, cancellationToken);
                return Results.Ok(new { success = true, data = result });
            }
            catch (InvalidOperationException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
            catch (HttpRequestException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        }).WithName("AdminDiscoverAiModels");

        providerGroup.MapPost("/{id}/test-connectivity", async (
            string id,
            [FromBody] AiProviderConnectivityTestRequest request,
            [FromServices] IAdminToolsService toolsService,
            CancellationToken cancellationToken) =>
        {
            try
            {
                var result = await toolsService.TestAiProviderConnectivityAsync(id, request, cancellationToken);
                return result.Success
                    ? Results.Ok(new { success = true, data = result })
                    : Results.BadRequest(new { success = false, message = result.Message, data = result });
            }
            catch (InvalidOperationException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
            catch (HttpRequestException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
            catch (TaskCanceledException ex) when (!cancellationToken.IsCancellationRequested)
            {
                return Results.BadRequest(new { success = false, message = $"Connectivity check timed out: {ex.Message}" });
            }
        }).WithName("AdminTestAiProviderConnectivity");
    }

    private static void MapAiModelEndpoints(RouteGroupBuilder group)
    {
        var modelGroup = group.MapGroup("/ai-models");

        modelGroup.MapGet("/", async (
            [FromQuery] string? providerId,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.GetAiModelsAsync(providerId);
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminGetAiModels");

        modelGroup.MapPost("/", async (
            [FromBody] AiModelConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.CreateAiModelAsync(request);
            return Results.Ok(new { success = true, data = result });
        }).WithName("AdminCreateAiModel");

        modelGroup.MapPut("/{id}", async (
            string id,
            [FromBody] AiModelConfigRequest request,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.UpdateAiModelAsync(id, request);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminUpdateAiModel");

        modelGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminToolsService toolsService) =>
        {
            var result = await toolsService.DeleteAiModelAsync(id);
            return result ? Results.Ok(new { success = true })
                : Results.NotFound(new { success = false });
        }).WithName("AdminDeleteAiModel");
    }
}
