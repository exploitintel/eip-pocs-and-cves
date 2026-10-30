using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// 管理端用户管理端点
/// </summary>
public static class AdminUserEndpoints
{
    public static RouteGroupBuilder MapAdminUserEndpoints(this RouteGroupBuilder group)
    {
        var userGroup = group.MapGroup("/users")
            .WithTags("管理端-用户管理");

        // 获取用户列表
        userGroup.MapGet("/", async (
            [FromQuery] int page,
            [FromQuery] int pageSize,
            [FromQuery] string? search,
            [FromQuery] string? roleId,
            [FromServices] IAdminUserService userService) =>
        {
            if (page <= 0) page = 1;
            if (pageSize <= 0) pageSize = 20;
            var result = await userService.GetUsersAsync(page, pageSize, search, roleId);
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetUsers")
        .WithSummary("获取用户列表");

        // 获取用户详情
        userGroup.MapGet("/{id}", async (
            string id,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.GetUserByIdAsync(id);
            if (result == null)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetUser")
        .WithSummary("获取用户详情");

        // 创建用户
        userGroup.MapPost("/", async (
            [FromBody] CreateUserRequest request,
            [FromServices] IAdminUserService userService) =>
        {
            try
            {
                var result = await userService.CreateUserAsync(request);
                return Results.Ok(new { success = true, data = result });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminCreateUser")
        .WithSummary("创建用户");

        // 更新用户
        userGroup.MapPut("/{id}", async (
            string id,
            [FromBody] UpdateUserRequest request,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.UpdateUserAsync(id, request);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, message = "更新成功" });
        })
        .WithName("AdminUpdateUser")
        .WithSummary("更新用户");

        // 删除用户
        userGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.DeleteUserAsync(id);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, message = "删除成功" });
        })
        .WithName("AdminDeleteUser")
        .WithSummary("删除用户");

        // 更新用户状态
        userGroup.MapPut("/{id}/status", async (
            string id,
            [FromBody] UpdateStatusRequest request,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.UpdateUserStatusAsync(id, request.Status);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, message = "状态更新成功" });
        })
        .WithName("AdminUpdateUserStatus")
        .WithSummary("更新用户状态");

        // 更新用户角色
        userGroup.MapPut("/{id}/roles", async (
            string id,
            [FromBody] UpdateUserRolesRequest request,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.UpdateUserRolesAsync(id, request.RoleIds);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, message = "角色更新成功" });
        })
        .WithName("AdminUpdateUserRoles")
        .WithSummary("更新用户角色");

        // 重置密码
        userGroup.MapPost("/{id}/reset-password", async (
            string id,
            [FromBody] ResetPasswordRequest request,
            [FromServices] IAdminUserService userService) =>
        {
            var result = await userService.ResetPasswordAsync(id, request.NewPassword);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不存在" });
            return Results.Ok(new { success = true, message = "密码重置成功" });
        })
        .WithName("AdminResetPassword")
        .WithSummary("重置用户密码");

        return group;
    }
}
