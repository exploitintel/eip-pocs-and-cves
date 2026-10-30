using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// 管理端对话助手配置端点
/// </summary>
public static class AdminChatAssistantEndpoints
{
    public static RouteGroupBuilder MapAdminChatAssistantEndpoints(this RouteGroupBuilder group)
    {
        var chatAssistantGroup = group.MapGroup("/chat-assistant")
            .WithTags("管理端-对话助手配置");

        // 获取对话助手配置（包含可选项列表）
        chatAssistantGroup.MapGet("/config", async (
            [FromServices] IAdminChatAssistantService chatAssistantService) =>
        {
            var result = await chatAssistantService.GetConfigWithOptionsAsync();
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetChatAssistantConfig")
        .WithSummary("获取对话助手配置")
        .WithDescription("获取对话助手配置，包含可选的模型、MCP和Skill列表");

        // 更新对话助手配置
        chatAssistantGroup.MapPut("/config", async (
            [FromBody] UpdateChatAssistantConfigRequest request,
            [FromServices] IAdminChatAssistantService chatAssistantService) =>
        {
            var result = await chatAssistantService.UpdateConfigAsync(request);
            return Results.Ok(new { success = true, data = result, message = "配置更新成功" });
        })
        .WithName("AdminUpdateChatAssistantConfig")
        .WithSummary("更新对话助手配置")
        .WithDescription("更新对话助手的启用状态、可用模型、MCP和Skill配置");

        return group;
    }
}
