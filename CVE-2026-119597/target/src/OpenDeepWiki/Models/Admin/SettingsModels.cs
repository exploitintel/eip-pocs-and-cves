namespace OpenDeepWiki.Models.Admin;

/// <summary>
/// 系统设置 DTO
/// </summary>
public class SystemSettingDto
{
    public string Id { get; set; } = string.Empty;
    public string Key { get; set; } = string.Empty;
    public string? Value { get; set; }
    public string? Description { get; set; }
    public string Category { get; set; } = string.Empty;
}

/// <summary>
/// 更新设置请求
/// </summary>
public class UpdateSettingRequest
{
    public string Key { get; set; } = string.Empty;
    public string? Value { get; set; }
}
