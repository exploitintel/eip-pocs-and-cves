using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端系统设置服务接口
/// </summary>
public interface IAdminSettingsService
{
    Task<List<SystemSettingDto>> GetSettingsAsync(string? category);
    Task<SystemSettingDto?> GetSettingByKeyAsync(string key);
    Task UpdateSettingsAsync(List<UpdateSettingRequest> requests);
}
