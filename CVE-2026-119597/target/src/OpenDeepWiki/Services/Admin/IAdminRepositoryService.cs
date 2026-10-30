using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端仓库服务接口
/// </summary>
public interface IAdminRepositoryService
{
    Task<AdminRepositoryListResponse> GetRepositoriesAsync(int page, int pageSize, string? search, int? status);
    Task<AdminRepositoryDto?> GetRepositoryByIdAsync(string id);
    Task<bool> UpdateRepositoryAsync(string id, UpdateRepositoryRequest request);
    Task<bool> DeleteRepositoryAsync(string id);
    Task<bool> UpdateRepositoryStatusAsync(string id, int status);
    
    /// <summary>
    /// 同步单个仓库的统计信息（star、fork等）
    /// </summary>
    Task<SyncStatsResult> SyncRepositoryStatsAsync(string id);
    
    /// <summary>
    /// 批量同步仓库统计信息
    /// </summary>
    Task<BatchSyncStatsResult> BatchSyncRepositoryStatsAsync(string[] ids);

    /// <summary>
    /// 批量触发仓库全量重生成
    /// </summary>
    Task<BatchRegenerateResult> BatchRegenerateRepositoriesAsync(string[] ids);
    
    /// <summary>
    /// 批量删除仓库
    /// </summary>
    Task<BatchDeleteResult> BatchDeleteRepositoriesAsync(string[] ids);

    /// <summary>
    /// 获取仓库深度管理信息（分支、语言、增量任务）
    /// </summary>
    Task<AdminRepositoryManagementDto?> GetRepositoryManagementAsync(string id);

    Task<AdminRepositoryScanPlanDto?> GetScanPlanAsync(string id);

    Task<AdminRepositoryScanPlanDto?> UpdateScanPlanAsync(string id, UpdateRepositoryScanPlanRequest request);

    Task<AdminRepositoryScanPlanOperationResult?> ReevaluateScanPlanAsync(string id, CancellationToken cancellationToken = default);

    /// <summary>
    /// 管理端触发全量重生成
    /// </summary>
    Task<AdminRepositoryOperationResult> RegenerateRepositoryAsync(string id);

    /// <summary>
    /// 管理端触发指定文档重生成
    /// </summary>
    Task<AdminRepositoryOperationResult> RegenerateDocumentAsync(string id, RegenerateRepositoryDocumentRequest request, CancellationToken cancellationToken = default);

    /// <summary>
    /// 管理端手动更新指定文档内容
    /// </summary>
    Task<AdminRepositoryOperationResult> UpdateDocumentContentAsync(string id, UpdateRepositoryDocumentContentRequest request, CancellationToken cancellationToken = default);
}
