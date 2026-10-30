using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端对话助手配置服务接口
/// </summary>
public interface IAdminChatAssistantService
{
    /// <summary>
    /// 获取对话助手配置（包含可选项列表）
    /// </summary>
    Task<ChatAssistantConfigOptionsDto> GetConfigWithOptionsAsync();

    /// <summary>
    /// 获取对话助手配置
    /// </summary>
    Task<ChatAssistantConfigDto> GetConfigAsync();

    /// <summary>
    /// 更新对话助手配置
    /// </summary>
    Task<ChatAssistantConfigDto> UpdateConfigAsync(UpdateChatAssistantConfigRequest request);
}
