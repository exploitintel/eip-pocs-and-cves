using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// 管理端角色管理端点
/// </summary>
public static class AdminRoleEndpoints
{
    public static RouteGroupBuilder MapAdminRoleEndpoints(this RouteGroupBuilder group)
    {
        var roleGroup = group.MapGroup("/roles")
            .WithTags("管理端-角色管理");

        // 获取角色列表
        roleGroup.MapGet("/", async ([FromServices] IAdminRoleService roleService) =>
        {
            var result = await roleService.GetRolesAsync();
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetRoles")
        .WithSummary("获取角色列表");

        // 获取角色详情
        roleGroup.MapGet("/{id}", async (
            string id,
            [FromServices] IAdminRoleService roleService) =>
        {
            var result = await roleService.GetRoleByIdAsync(id);
            if (result == null)
                return Results.NotFound(new { success = false, message = "角色不存在" });
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetRole")
        .WithSummary("获取角色详情");

        // 创建角色
        roleGroup.MapPost("/", async (
            [FromBody] CreateRoleRequest request,
            [FromServices] IAdminRoleService roleService) =>
        {
            try
            {
                var result = await roleService.CreateRoleAsync(request);
                return Results.Ok(new { success = true, data = result });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminCreateRole")
        .WithSummary("创建角色");

        // 更新角色
        roleGroup.MapPut("/{id}", async (
            string id,
            [FromBody] UpdateRoleRequest request,
            [FromServices] IAdminRoleService roleService) =>
        {
            try
            {
                var result = await roleService.UpdateRoleAsync(id, request);
                if (!result)
                    return Results.NotFound(new { success = false, message = "角色不存在" });
                return Results.Ok(new { success = true, message = "更新成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminUpdateRole")
        .WithSummary("更新角色");

        // 删除角色
        roleGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminRoleService roleService) =>
        {
            try
            {
                var result = await roleService.DeleteRoleAsync(id);
                if (!result)
                    return Results.NotFound(new { success = false, message = "角色不存在" });
                return Results.Ok(new { success = true, message = "删除成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminDeleteRole")
        .WithSummary("删除角色");

        return group;
    }
}
