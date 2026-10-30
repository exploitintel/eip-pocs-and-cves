using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

/// <summary>
/// 管理端部门管理端点
/// </summary>
public static class AdminDepartmentEndpoints
{
    public static RouteGroupBuilder MapAdminDepartmentEndpoints(this RouteGroupBuilder group)
    {
        var deptGroup = group.MapGroup("/departments")
            .WithTags("管理端-部门管理");

        // 获取部门列表
        deptGroup.MapGet("/", async ([FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.GetDepartmentsAsync();
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetDepartments")
        .WithSummary("获取部门列表");

        // 获取部门树形结构
        deptGroup.MapGet("/tree", async ([FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.GetDepartmentTreeAsync();
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetDepartmentTree")
        .WithSummary("获取部门树形结构");

        // 获取部门详情
        deptGroup.MapGet("/{id}", async (
            string id,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.GetDepartmentByIdAsync(id);
            if (result == null)
                return Results.NotFound(new { success = false, message = "部门不存在" });
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetDepartment")
        .WithSummary("获取部门详情");

        // 创建部门
        deptGroup.MapPost("/", async (
            [FromBody] CreateDepartmentRequest request,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            try
            {
                var result = await deptService.CreateDepartmentAsync(request);
                return Results.Ok(new { success = true, data = result });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminCreateDepartment")
        .WithSummary("创建部门");

        // 更新部门
        deptGroup.MapPut("/{id}", async (
            string id,
            [FromBody] UpdateDepartmentRequest request,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            try
            {
                var result = await deptService.UpdateDepartmentAsync(id, request);
                if (!result)
                    return Results.NotFound(new { success = false, message = "部门不存在" });
                return Results.Ok(new { success = true, message = "更新成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminUpdateDepartment")
        .WithSummary("更新部门");

        // 删除部门
        deptGroup.MapDelete("/{id}", async (
            string id,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            try
            {
                var result = await deptService.DeleteDepartmentAsync(id);
                if (!result)
                    return Results.NotFound(new { success = false, message = "部门不存在" });
                return Results.Ok(new { success = true, message = "删除成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminDeleteDepartment")
        .WithSummary("删除部门");

        // 获取部门用户列表
        deptGroup.MapGet("/{id}/users", async (
            string id,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.GetDepartmentUsersAsync(id);
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetDepartmentUsers")
        .WithSummary("获取部门用户列表");

        // 添加用户到部门
        deptGroup.MapPost("/{id}/users", async (
            string id,
            [FromBody] AddUserToDepartmentRequest request,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            try
            {
                await deptService.AddUserToDepartmentAsync(id, request.UserId, request.IsManager);
                return Results.Ok(new { success = true, message = "添加成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminAddUserToDepartment")
        .WithSummary("添加用户到部门");

        // 从部门移除用户
        deptGroup.MapDelete("/{id}/users/{userId}", async (
            string id,
            string userId,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.RemoveUserFromDepartmentAsync(id, userId);
            if (!result)
                return Results.NotFound(new { success = false, message = "用户不在该部门中" });
            return Results.Ok(new { success = true, message = "移除成功" });
        })
        .WithName("AdminRemoveUserFromDepartment")
        .WithSummary("从部门移除用户");

        // 获取部门仓库列表
        deptGroup.MapGet("/{id}/repositories", async (
            string id,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.GetDepartmentRepositoriesAsync(id);
            return Results.Ok(new { success = true, data = result });
        })
        .WithName("AdminGetDepartmentRepositories")
        .WithSummary("获取部门仓库列表");

        // 分配仓库到部门
        deptGroup.MapPost("/{id}/repositories", async (
            string id,
            [FromBody] AssignRepositoryRequest request,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            try
            {
                await deptService.AssignRepositoryToDepartmentAsync(id, request.RepositoryId, request.AssigneeUserId);
                return Results.Ok(new { success = true, message = "分配成功" });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("AdminAssignRepositoryToDepartment")
        .WithSummary("分配仓库到部门");

        // 从部门移除仓库
        deptGroup.MapDelete("/{id}/repositories/{repositoryId}", async (
            string id,
            string repositoryId,
            [FromServices] IAdminDepartmentService deptService) =>
        {
            var result = await deptService.RemoveRepositoryFromDepartmentAsync(id, repositoryId);
            if (!result)
                return Results.NotFound(new { success = false, message = "仓库未分配给该部门" });
            return Results.Ok(new { success = true, message = "移除成功" });
        })
        .WithName("AdminRemoveRepositoryFromDepartment")
        .WithSummary("从部门移除仓库");

        return group;
    }
}
