using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端用户服务接口
/// </summary>
public interface IAdminUserService
{
    Task<AdminUserListResponse> GetUsersAsync(int page, int pageSize, string? search, string? roleId);
    Task<AdminUserDto?> GetUserByIdAsync(string id);
    Task<AdminUserDto> CreateUserAsync(CreateUserRequest request);
    Task<bool> UpdateUserAsync(string id, UpdateUserRequest request);
    Task<bool> DeleteUserAsync(string id);
    Task<bool> UpdateUserStatusAsync(string id, int status);
    Task<bool> UpdateUserRolesAsync(string id, List<string> roleIds);
    Task<bool> ResetPasswordAsync(string id, string newPassword);
}
