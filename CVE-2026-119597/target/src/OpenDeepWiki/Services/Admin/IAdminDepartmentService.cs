using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端部门服务接口
/// </summary>
public interface IAdminDepartmentService
{
    Task<List<AdminDepartmentDto>> GetDepartmentsAsync();
    Task<List<AdminDepartmentDto>> GetDepartmentTreeAsync();
    Task<AdminDepartmentDto?> GetDepartmentByIdAsync(string id);
    Task<AdminDepartmentDto> CreateDepartmentAsync(CreateDepartmentRequest request);
    Task<bool> UpdateDepartmentAsync(string id, UpdateDepartmentRequest request);
    Task<bool> DeleteDepartmentAsync(string id);
    Task<List<DepartmentUserDto>> GetDepartmentUsersAsync(string departmentId);
    Task<bool> AddUserToDepartmentAsync(string departmentId, string userId, bool isManager = false);
    Task<bool> RemoveUserFromDepartmentAsync(string departmentId, string userId);
    Task<List<DepartmentRepositoryDto>> GetDepartmentRepositoriesAsync(string departmentId);
    Task<bool> AssignRepositoryToDepartmentAsync(string departmentId, string repositoryId, string assigneeUserId);
    Task<bool> RemoveRepositoryFromDepartmentAsync(string departmentId, string repositoryId);
}
