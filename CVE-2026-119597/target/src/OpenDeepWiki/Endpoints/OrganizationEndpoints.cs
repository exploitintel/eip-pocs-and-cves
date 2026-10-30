using System.Security.Claims;
using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Services.Organizations;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 组织端点
/// </summary>
public static class OrganizationEndpoints
{
    public static IEndpointRouteBuilder MapOrganizationEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/organizations")
            .RequireAuthorization()
            .WithTags("组织");

        // 获取当前用户的部门列表
        group.MapGet("/my-departments", async (
            ClaimsPrincipal user,
            [FromServices] IOrganizationService orgService) =>
        {
            var userId = user.FindFirstValue(ClaimTypes.NameIdentifier);
            if (string.IsNullOrEmpty(userId))
                return Results.Unauthorized();

            var result = await orgService.GetUserDepartmentsAsync(userId);
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("GetMyDepartments")
        .WithSummary("获取当前用户的部门列表");

        // 获取当前用户部门下的仓库列表
        group.MapGet("/my-repositories", async (
            ClaimsPrincipal user,
            [FromServices] IOrganizationService orgService) =>
        {
            var userId = user.FindFirstValue(ClaimTypes.NameIdentifier);
            if (string.IsNullOrEmpty(userId))
                return Results.Unauthorized();

            var result = await orgService.GetDepartmentRepositoriesAsync(userId);
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("GetMyDepartmentRepositories")
        .WithSummary("获取当前用户部门下的仓库列表");

        return app;
    }
}
