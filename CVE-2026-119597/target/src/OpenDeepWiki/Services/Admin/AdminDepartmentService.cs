using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端部门服务实现
/// </summary>
public class AdminDepartmentService : IAdminDepartmentService
{
    private readonly IContext _context;

    public AdminDepartmentService(IContext context)
    {
        _context = context;
    }

    public async Task<List<AdminDepartmentDto>> GetDepartmentsAsync()
    {
        var departments = await _context.Departments
            .OrderBy(d => d.SortOrder)
            .ThenBy(d => d.Name)
            .ToListAsync();

        var parentIds = departments.Where(d => d.ParentId != null).Select(d => d.ParentId).Distinct().ToList();
        var parentNames = await _context.Departments
            .Where(d => parentIds.Contains(d.Id))
            .ToDictionaryAsync(d => d.Id, d => d.Name);

        return departments.Select(d => new AdminDepartmentDto
        {
            Id = d.Id,
            Name = d.Name,
            ParentId = d.ParentId,
            ParentName = d.ParentId != null && parentNames.TryGetValue(d.ParentId, out var name) ? name : null,
            Description = d.Description,
            SortOrder = d.SortOrder,
            IsActive = d.IsActive,
            CreatedAt = d.CreatedAt
        }).ToList();
    }


    public async Task<List<AdminDepartmentDto>> GetDepartmentTreeAsync()
    {
        var departments = await GetDepartmentsAsync();
        return BuildTree(departments, null);
    }

    private List<AdminDepartmentDto> BuildTree(List<AdminDepartmentDto> departments, string? parentId)
    {
        return departments
            .Where(d => d.ParentId == parentId)
            .Select(d =>
            {
                d.Children = BuildTree(departments, d.Id);
                return d;
            })
            .ToList();
    }

    public async Task<AdminDepartmentDto?> GetDepartmentByIdAsync(string id)
    {
        var department = await _context.Departments.FirstOrDefaultAsync(d => d.Id == id);
        if (department == null) return null;

        string? parentName = null;
        if (department.ParentId != null)
        {
            parentName = await _context.Departments
                .Where(d => d.Id == department.ParentId)
                .Select(d => d.Name)
                .FirstOrDefaultAsync();
        }

        return new AdminDepartmentDto
        {
            Id = department.Id,
            Name = department.Name,
            ParentId = department.ParentId,
            ParentName = parentName,
            Description = department.Description,
            SortOrder = department.SortOrder,
            IsActive = department.IsActive,
            CreatedAt = department.CreatedAt
        };
    }

    public async Task<AdminDepartmentDto> CreateDepartmentAsync(CreateDepartmentRequest request)
    {
        var exists = await _context.Departments.AnyAsync(d => d.Name == request.Name);
        if (exists) throw new InvalidOperationException("部门名称已存在");

        if (request.ParentId != null)
        {
            var parentExists = await _context.Departments.AnyAsync(d => d.Id == request.ParentId);
            if (!parentExists) throw new InvalidOperationException("父部门不存在");
        }

        var department = new Department
        {
            Id = Guid.NewGuid().ToString(),
            Name = request.Name,
            ParentId = request.ParentId,
            Description = request.Description,
            SortOrder = request.SortOrder,
            IsActive = request.IsActive,
            CreatedAt = DateTime.UtcNow
        };

        _context.Departments.Add(department);
        await _context.SaveChangesAsync();

        return (await GetDepartmentByIdAsync(department.Id))!;
    }

    public async Task<bool> UpdateDepartmentAsync(string id, UpdateDepartmentRequest request)
    {
        var department = await _context.Departments.FirstOrDefaultAsync(d => d.Id == id);
        if (department == null) return false;

        if (request.Name != null)
        {
            var exists = await _context.Departments.AnyAsync(d => d.Name == request.Name && d.Id != id);
            if (exists) throw new InvalidOperationException("部门名称已存在");
            department.Name = request.Name;
        }

        if (request.ParentId != null)
        {
            if (request.ParentId == id) throw new InvalidOperationException("不能将自己设为父部门");
            var parentExists = await _context.Departments.AnyAsync(d => d.Id == request.ParentId);
            if (!parentExists) throw new InvalidOperationException("父部门不存在");
            department.ParentId = request.ParentId;
        }

        if (request.Description != null) department.Description = request.Description;
        if (request.SortOrder.HasValue) department.SortOrder = request.SortOrder.Value;
        if (request.IsActive.HasValue) department.IsActive = request.IsActive.Value;

        department.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteDepartmentAsync(string id)
    {
        var department = await _context.Departments.FirstOrDefaultAsync(d => d.Id == id);
        if (department == null) return false;

        var hasChildren = await _context.Departments.AnyAsync(d => d.ParentId == id);
        if (hasChildren) throw new InvalidOperationException("该部门下还有子部门，不能删除");

        _context.Departments.Remove(department);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<DepartmentUserDto>> GetDepartmentUsersAsync(string departmentId)
    {
        var userDepts = await _context.UserDepartments
            .Where(ud => ud.DepartmentId == departmentId && !ud.IsDeleted)
            .ToListAsync();

        var userIds = userDepts.Select(ud => ud.UserId).ToList();
        var users = await _context.Users
            .Where(u => userIds.Contains(u.Id) && !u.IsDeleted)
            .ToDictionaryAsync(u => u.Id);

        return userDepts.Select(ud => new DepartmentUserDto
        {
            Id = ud.Id,
            UserId = ud.UserId,
            UserName = users.TryGetValue(ud.UserId, out var user) ? user.Name : "",
            Email = user?.Email,
            Avatar = user?.Avatar,
            IsManager = ud.IsManager,
            CreatedAt = ud.CreatedAt
        }).ToList();
    }

    public async Task<bool> AddUserToDepartmentAsync(string departmentId, string userId, bool isManager = false)
    {
        var deptExists = await _context.Departments.AnyAsync(d => d.Id == departmentId);
        if (!deptExists) throw new InvalidOperationException("部门不存在");

        var userExists = await _context.Users.AnyAsync(u => u.Id == userId && !u.IsDeleted);
        if (!userExists) throw new InvalidOperationException("用户不存在");

        var exists = await _context.UserDepartments.AnyAsync(ud => 
            ud.DepartmentId == departmentId && ud.UserId == userId && !ud.IsDeleted);
        if (exists) throw new InvalidOperationException("用户已在该部门中");

        var userDept = new UserDepartment
        {
            Id = Guid.NewGuid().ToString(),
            UserId = userId,
            DepartmentId = departmentId,
            IsManager = isManager,
            CreatedAt = DateTime.UtcNow
        };

        _context.UserDepartments.Add(userDept);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> RemoveUserFromDepartmentAsync(string departmentId, string userId)
    {
        var userDept = await _context.UserDepartments.FirstOrDefaultAsync(ud =>
            ud.DepartmentId == departmentId && ud.UserId == userId && !ud.IsDeleted);
        if (userDept == null) return false;

        userDept.IsDeleted = true;
        userDept.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<DepartmentRepositoryDto>> GetDepartmentRepositoriesAsync(string departmentId)
    {
        var assignments = await _context.RepositoryAssignments
            .Where(ra => ra.DepartmentId == departmentId && !ra.IsDeleted)
            .ToListAsync();

        var repoIds = assignments.Select(a => a.RepositoryId).ToList();
        var userIds = assignments.Select(a => a.AssigneeUserId).Distinct().ToList();

        var repos = await _context.Repositories
            .Where(r => repoIds.Contains(r.Id))
            .ToDictionaryAsync(r => r.Id);

        var users = await _context.Users
            .Where(u => userIds.Contains(u.Id))
            .ToDictionaryAsync(u => u.Id);

        return assignments.Select(a => new DepartmentRepositoryDto
        {
            Id = a.Id,
            RepositoryId = a.RepositoryId,
            RepoName = repos.TryGetValue(a.RepositoryId, out var repo) ? repo.RepoName : "",
            OrgName = repo?.OrgName ?? "",
            GitUrl = repo?.GitUrl,
            Status = (int)(repo?.Status ?? 0),
            AssigneeUserName = users.TryGetValue(a.AssigneeUserId, out var user) ? user.Name : null,
            CreatedAt = a.CreatedAt
        }).ToList();
    }

    public async Task<bool> AssignRepositoryToDepartmentAsync(string departmentId, string repositoryId, string assigneeUserId)
    {
        var deptExists = await _context.Departments.AnyAsync(d => d.Id == departmentId);
        if (!deptExists) throw new InvalidOperationException("部门不存在");

        var repoExists = await _context.Repositories.AnyAsync(r => r.Id == repositoryId);
        if (!repoExists) throw new InvalidOperationException("仓库不存在");

        var exists = await _context.RepositoryAssignments.AnyAsync(ra =>
            ra.DepartmentId == departmentId && ra.RepositoryId == repositoryId && !ra.IsDeleted);
        if (exists) throw new InvalidOperationException("该仓库已分配给此部门");

        var assignment = new RepositoryAssignment
        {
            Id = Guid.NewGuid().ToString(),
            DepartmentId = departmentId,
            RepositoryId = repositoryId,
            AssigneeUserId = assigneeUserId,
            CreatedAt = DateTime.UtcNow
        };

        _context.RepositoryAssignments.Add(assignment);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> RemoveRepositoryFromDepartmentAsync(string departmentId, string repositoryId)
    {
        var assignment = await _context.RepositoryAssignments.FirstOrDefaultAsync(ra =>
            ra.DepartmentId == departmentId && ra.RepositoryId == repositoryId && !ra.IsDeleted);
        if (assignment == null) return false;

        assignment.IsDeleted = true;
        assignment.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }
}