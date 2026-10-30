namespace OpenDeepWiki.Chat.Config;

/// <summary>
/// Chat 配置服务接口
/// 提供 Provider 配置的管理功能
/// </summary>
public interface IChatConfigService
{
    /// <summary>
    /// 获取指定平台的配置
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>配置对象，如果不存在则返回 null</returns>
    Task<ProviderConfigDto?> GetConfigAsync(string platform, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 获取所有配置
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>所有配置列表</returns>
    Task<IEnumerable<ProviderConfigDto>> GetAllConfigsAsync(CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 保存配置（新增或更新）
    /// </summary>
    /// <param name="config">配置对象</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task SaveConfigAsync(ProviderConfigDto config, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 删除配置
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task DeleteConfigAsync(string platform, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 验证配置完整性
    /// </summary>
    /// <param name="config">配置对象</param>
    /// <returns>验证结果</returns>
    ConfigValidationResult ValidateConfig(ProviderConfigDto config);
    
    /// <summary>
    /// 验证所有配置
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>验证结果列表</returns>
    Task<IEnumerable<ConfigValidationResult>> ValidateAllConfigsAsync(CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 注册配置变更回调
    /// </summary>
    /// <param name="callback">回调函数</param>
    /// <returns>取消注册的 IDisposable</returns>
    IDisposable OnConfigChanged(Action<string> callback);
    
    /// <summary>
    /// 触发配置重载
    /// </summary>
    /// <param name="platform">平台标识，如果为 null 则重载所有配置</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task ReloadConfigAsync(string? platform = null, CancellationToken cancellationToken = default);
}

/// <summary>
/// Provider 配置 DTO
/// </summary>
public class ProviderConfigDto
{
    /// <summary>
    /// 平台标识
    /// </summary>
    public string Platform { get; set; } = string.Empty;
    
    /// <summary>
    /// 显示名称
    /// </summary>
    public string DisplayName { get; set; } = string.Empty;
    
    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsEnabled { get; set; } = true;
    
    /// <summary>
    /// 配置数据（明文 JSON）
    /// </summary>
    public string ConfigData { get; set; } = string.Empty;
    
    /// <summary>
    /// Webhook URL
    /// </summary>
    public string? WebhookUrl { get; set; }
    
    /// <summary>
    /// 消息发送间隔（毫秒）
    /// </summary>
    public int MessageInterval { get; set; } = 500;
    
    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;
}

/// <summary>
/// 配置验证结果
/// </summary>
public class ConfigValidationResult
{
    /// <summary>
    /// 平台标识
    /// </summary>
    public string Platform { get; set; } = string.Empty;
    
    /// <summary>
    /// 是否验证通过
    /// </summary>
    public bool IsValid { get; set; }
    
    /// <summary>
    /// 错误信息列表
    /// </summary>
    public List<string> Errors { get; set; } = new();
    
    /// <summary>
    /// 缺失的配置项
    /// </summary>
    public List<string> MissingFields { get; set; } = new();
    
    /// <summary>
    /// 创建成功的验证结果
    /// </summary>
    public static ConfigValidationResult Success(string platform) => new()
    {
        Platform = platform,
        IsValid = true
    };
    
    /// <summary>
    /// 创建失败的验证结果
    /// </summary>
    public static ConfigValidationResult Failure(string platform, params string[] errors) => new()
    {
        Platform = platform,
        IsValid = false,
        Errors = errors.ToList()
    };
}
