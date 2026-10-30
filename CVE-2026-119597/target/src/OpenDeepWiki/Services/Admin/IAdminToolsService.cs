using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端工具配置服务接口
/// </summary>
public interface IAdminToolsService
{
    // MCP 配置
    Task<List<McpConfigDto>> GetMcpConfigsAsync();
    Task<McpConfigDto> CreateMcpConfigAsync(McpConfigRequest request);
    Task<bool> UpdateMcpConfigAsync(string id, McpConfigRequest request);
    Task<bool> DeleteMcpConfigAsync(string id);

    // Skill 配置（遵循 Agent Skills 标准）
    Task<List<SkillConfigDto>> GetSkillConfigsAsync();
    Task<SkillDetailDto?> GetSkillDetailAsync(string id);
    Task<SkillConfigDto> UploadSkillAsync(Stream zipStream, string fileName);
    Task<bool> UpdateSkillAsync(string id, SkillUpdateRequest request);
    Task<bool> DeleteSkillAsync(string id);
    Task<string?> GetSkillFileContentAsync(string id, string filePath);
    Task RefreshSkillsFromDiskAsync();

    // 模型配置
    Task<List<AiProviderConfigDto>> GetAiProvidersAsync();
    Task<AiProviderConfigDto> CreateAiProviderAsync(AiProviderConfigRequest request);
    Task<bool> UpdateAiProviderAsync(string id, AiProviderConfigRequest request);
    Task<bool> DeleteAiProviderAsync(string id);

    Task<List<AiModelConfigDto>> GetAiModelsAsync(string? providerId = null);
    Task<AiModelConfigDto> CreateAiModelAsync(AiModelConfigRequest request);
    Task<bool> UpdateAiModelAsync(string id, AiModelConfigRequest request);
    Task<bool> DeleteAiModelAsync(string id);
    Task<List<AiModelConfigDto>> DiscoverAiModelsAsync(string providerId, CancellationToken cancellationToken = default);
    Task<AiProviderConnectivityTestResult> TestAiProviderConnectivityAsync(
        string providerId,
        AiProviderConnectivityTestRequest request,
        CancellationToken cancellationToken = default);

    Task<List<ModelConfigDto>> GetModelConfigsAsync();
    Task<ModelConfigDto> CreateModelConfigAsync(ModelConfigRequest request);
    Task<bool> UpdateModelConfigAsync(string id, ModelConfigRequest request);
    Task<bool> DeleteModelConfigAsync(string id);
}
