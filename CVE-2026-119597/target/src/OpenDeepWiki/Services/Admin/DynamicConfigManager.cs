using Microsoft.Extensions.Options;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 动态配置管理器，用于从系统设置中更新配置
/// </summary>
public interface IDynamicConfigManager
{
    /// <summary>
    /// 刷新WikiGeneratorOptions配置
    /// </summary>
    Task RefreshWikiGeneratorOptionsAsync();
}

/// <summary>
/// 动态配置管理器实现
/// </summary>
public class DynamicConfigManager : IDynamicConfigManager
{
    private readonly IOptionsMonitor<WikiGeneratorOptions> _optionsMonitor;
    private readonly IAdminSettingsService _settingsService;
    private readonly ILogger<DynamicConfigManager> _logger;

    public DynamicConfigManager(
        IOptionsMonitor<WikiGeneratorOptions> optionsMonitor,
        IAdminSettingsService settingsService,
        ILogger<DynamicConfigManager> logger)
    {
        _optionsMonitor = optionsMonitor;
        _settingsService = settingsService;
        _logger = logger;
    }

    /// <summary>
    /// 刷新WikiGeneratorOptions配置
    /// </summary>
    public async Task RefreshWikiGeneratorOptionsAsync()
    {
        try
        {
            // 获取当前配置
            var currentOptions = _optionsMonitor.CurrentValue;
            
            // 获取所有AI相关的系统设置
            var aiSettings = await _settingsService.GetSettingsAsync("ai");
            
            // 应用设置到配置对象
            foreach (var setting in aiSettings)
            {
                SystemSettingDefaults.ApplySettingToOption(currentOptions, setting.Key, setting.Value ?? string.Empty);
            }

            _logger.LogDebug("WikiGeneratorOptions配置已从系统设置刷新");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "刷新WikiGeneratorOptions配置失败");
            throw;
        }
    }
}
