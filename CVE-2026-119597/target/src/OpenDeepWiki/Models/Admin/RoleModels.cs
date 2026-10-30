namespace OpenDeepWiki.Models.Admin;

/// <summary>
/// 管理端角色 DTO
/// </summary>
public class AdminRoleDto
{
    public string Id { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string? Description { get; set; }
    public bool IsActive { get; set; }
    public bool IsSystemRole { get; set; }
    public int UserCount { get; set; }
    public DateTime CreatedAt { get; set; }
}

/// <summary>
/// 创建角色请求
/// </summary>
public class CreateRoleRequest
{
    public string Name { get; set; } = string.Empty;
    public string? Description { get; set; }
    public bool IsActive { get; set; } = true;
}

/// <summary>
/// 更新角色请求
/// </summary>
public class UpdateRoleRequest
{
    public string? Name { get; set; }
    public string? Description { get; set; }
    public bool? IsActive { get; set; }
}
