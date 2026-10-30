namespace OpenDeepWiki.Models.Admin;

/// <summary>
/// 管理端部门 DTO
/// </summary>
public class AdminDepartmentDto
{
    public string Id { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string? ParentId { get; set; }
    public string? ParentName { get; set; }
    public string? Description { get; set; }
    public int SortOrder { get; set; }
    public bool IsActive { get; set; }
    public DateTime CreatedAt { get; set; }
    public List<AdminDepartmentDto> Children { get; set; } = new();
}

/// <summary>
/// 创建部门请求
/// </summary>
public class CreateDepartmentRequest
{
    public string Name { get; set; } = string.Empty;
    public string? ParentId { get; set; }
    public string? Description { get; set; }
    public int SortOrder { get; set; } = 0;
    public bool IsActive { get; set; } = true;
}

/// <summary>
/// 更新部门请求
/// </summary>
public class UpdateDepartmentRequest
{
    public string? Name { get; set; }
    public string? ParentId { get; set; }
    public string? Description { get; set; }
    public int? SortOrder { get; set; }
    public bool? IsActive { get; set; }
}


/// <summary>
/// 部门用户 DTO
/// </summary>
public class DepartmentUserDto
{
    public string Id { get; set; } = string.Empty;
    public string UserId { get; set; } = string.Empty;
    public string UserName { get; set; } = string.Empty;
    public string? Email { get; set; }
    public string? Avatar { get; set; }
    public bool IsManager { get; set; }
    public DateTime CreatedAt { get; set; }
}

/// <summary>
/// 部门仓库 DTO
/// </summary>
public class DepartmentRepositoryDto
{
    public string Id { get; set; } = string.Empty;
    public string RepositoryId { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public string OrgName { get; set; } = string.Empty;
    public string? GitUrl { get; set; }
    public int Status { get; set; }
    public string? AssigneeUserName { get; set; }
    public DateTime CreatedAt { get; set; }
}

/// <summary>
/// 添加用户到部门请求
/// </summary>
public class AddUserToDepartmentRequest
{
    public string UserId { get; set; } = string.Empty;
    public bool IsManager { get; set; } = false;
}

/// <summary>
/// 分配仓库到部门请求
/// </summary>
public class AssignRepositoryRequest
{
    public string RepositoryId { get; set; } = string.Empty;
    public string AssigneeUserId { get; set; } = string.Empty;
}
