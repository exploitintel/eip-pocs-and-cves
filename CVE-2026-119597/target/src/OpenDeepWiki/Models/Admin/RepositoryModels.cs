using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Models.Admin;

/// <summary>
/// 管理端仓库列表响应
/// </summary>
public class AdminRepositoryListResponse
{
    public List<AdminRepositoryDto> Items { get; set; } = new();
    public int Total { get; set; }
    public int Page { get; set; }
    public int PageSize { get; set; }
}

/// <summary>
/// 管理端仓库 DTO
/// </summary>
public class AdminRepositoryDto
{
    public string Id { get; set; } = string.Empty;
    public string GitUrl { get; set; } = string.Empty;
    public RepositorySourceType SourceType { get; set; } = RepositorySourceType.Git;
    public string SourceTypeName => SourceType.ToString();
    public string SourceLocation { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public string OrgName { get; set; } = string.Empty;
    public bool IsPublic { get; set; }
    public bool GenerateSkill { get; set; }
    public int Status { get; set; }
    public string StatusText { get; set; } = string.Empty;
    public string ScanDepthMode { get; set; } = RepositoryScanDepthMode.Auto.ToString();
    public AdminRepositoryScanPlanDto? ScanPlan { get; set; }
    public int StarCount { get; set; }
    public int ForkCount { get; set; }
    public int BookmarkCount { get; set; }
    public int ViewCount { get; set; }
    public int BranchGenerationActiveCount { get; set; }
    public int BranchGenerationFailedCount { get; set; }
    public string? OwnerUserId { get; set; }
    public string? OwnerUserName { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
}

/// <summary>
/// 更新仓库请求
/// </summary>
public class UpdateRepositoryRequest
{
    public bool? IsPublic { get; set; }
    public string? AuthAccount { get; set; }
    public string? AuthPassword { get; set; }
}

/// <summary>
/// 更新状态请求
/// </summary>
public class UpdateStatusRequest
{
    public int Status { get; set; }
}

/// <summary>
/// 同步统计信息结果
/// </summary>
public class SyncStatsResult
{
    public bool Success { get; set; }
    public string? Message { get; set; }
    public int StarCount { get; set; }
    public int ForkCount { get; set; }
}

/// <summary>
/// 批量同步统计信息结果
/// </summary>
public class BatchSyncStatsResult
{
    public int TotalCount { get; set; }
    public int SuccessCount { get; set; }
    public int FailedCount { get; set; }
    public List<BatchSyncItemResult> Results { get; set; } = new();
}

/// <summary>
/// 批量同步单项结果
/// </summary>
public class BatchSyncItemResult
{
    public string Id { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public bool Success { get; set; }
    public string? Message { get; set; }
    public int StarCount { get; set; }
    public int ForkCount { get; set; }
}

/// <summary>
/// 批量触发仓库全量重生成结果
/// </summary>
public class BatchRegenerateResult
{
    public int TotalCount { get; set; }
    public int SuccessCount { get; set; }
    public int FailedCount { get; set; }
    public List<BatchRegenerateItemResult> Results { get; set; } = new();
}

/// <summary>
/// 批量触发仓库全量重生成单项结果
/// </summary>
public class BatchRegenerateItemResult
{
    public string Id { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public bool Success { get; set; }
    public string? Message { get; set; }
}

/// <summary>
/// 批量删除结果
/// </summary>
public class BatchDeleteResult
{
    public int TotalCount { get; set; }
    public int SuccessCount { get; set; }
    public int FailedCount { get; set; }
    public List<string> FailedIds { get; set; } = new();
}

/// <summary>
/// 管理端仓库深度管理信息
/// </summary>
public class AdminRepositoryManagementDto
{
    public string RepositoryId { get; set; } = string.Empty;
    public string OrgName { get; set; } = string.Empty;
    public string RepoName { get; set; } = string.Empty;
    public int Status { get; set; }
    public string StatusText { get; set; } = string.Empty;
    public List<AdminRepositoryBranchDto> Branches { get; set; } = new();
    public List<AdminIncrementalTaskDto> RecentIncrementalTasks { get; set; } = new();
    public List<AdminBranchGenerationTaskDto> RecentBranchGenerationTasks { get; set; } = new();
    public AdminRepositoryScanPlanDto? ScanPlan { get; set; }
}

public class AdminRepositoryScanPlanDto
{
    public string Source { get; set; } = string.Empty;
    public string Mode { get; set; } = RepositoryScanDepthMode.Auto.ToString();
    public int DirectoryTreeDepth { get; set; }
    public int FileListDepth { get; set; }
    public int MaxTreeNodes { get; set; }
    public int MaxFilesPerDirectory { get; set; }
    public int MaxTotalFiles { get; set; }
    public List<string> ExtraExcludedDirs { get; set; } = new();
    public string? ProfileHash { get; set; }
    public string? Reason { get; set; }
    public double? Confidence { get; set; }
    public DateTime? UpdatedAt { get; set; }
}

public class UpdateRepositoryScanPlanRequest
{
    public string Mode { get; set; } = RepositoryScanDepthMode.Auto.ToString();
    public int? DirectoryTreeDepth { get; set; }
    public int? FileListDepth { get; set; }
    public int? MaxTreeNodes { get; set; }
    public int? MaxFilesPerDirectory { get; set; }
    public int? MaxTotalFiles { get; set; }
    public List<string>? ExtraExcludedDirs { get; set; }
}

public class AdminRepositoryScanPlanOperationResult
{
    public bool Success { get; set; }
    public string? Message { get; set; }
    public AdminRepositoryScanPlanDto? ScanPlan { get; set; }
}

/// <summary>
/// 管理端分支信息
/// </summary>
public class AdminRepositoryBranchDto
{
    public string Id { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string? LastCommitId { get; set; }
    public DateTime? LastProcessedAt { get; set; }
    public string? GenerationStatus { get; set; }
    public string? LastGenerationTaskId { get; set; }
    public string? LastGenerationError { get; set; }
    public DateTime? LastGenerationStartedAt { get; set; }
    public DateTime? LastGenerationCompletedAt { get; set; }
    public List<AdminBranchLanguageDto> Languages { get; set; } = new();
}

/// <summary>
/// 管理端分支语言信息
/// </summary>
public class AdminBranchLanguageDto
{
    public string Id { get; set; } = string.Empty;
    public string LanguageCode { get; set; } = string.Empty;
    public bool IsDefault { get; set; }
    public int CatalogCount { get; set; }
    public int DocumentCount { get; set; }
    public DateTime CreatedAt { get; set; }
}

/// <summary>
/// 管理端增量更新任务信息
/// </summary>
public class AdminIncrementalTaskDto
{
    public string TaskId { get; set; } = string.Empty;
    public string BranchId { get; set; } = string.Empty;
    public string? BranchName { get; set; }
    public string Status { get; set; } = string.Empty;
    public int Priority { get; set; }
    public bool IsManualTrigger { get; set; }
    public int RetryCount { get; set; }
    public string? PreviousCommitId { get; set; }
    public string? TargetCommitId { get; set; }
    public string? ErrorMessage { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? StartedAt { get; set; }
    public DateTime? CompletedAt { get; set; }
}

/// <summary>
/// 管理端 branch full generation 任务信息
/// </summary>
public class AdminBranchGenerationTaskDto
{
    public string TaskId { get; set; } = string.Empty;
    public string RepositoryId { get; set; } = string.Empty;
    public string BranchId { get; set; } = string.Empty;
    public string? BranchName { get; set; }
    public string Status { get; set; } = string.Empty;
    public string Mode { get; set; } = string.Empty;
    public int Priority { get; set; }
    public bool IsManualTrigger { get; set; }
    public int RetryCount { get; set; }
    public string? ErrorMessage { get; set; }
    public string? RequestedBy { get; set; }
    public string? TargetCommitId { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? StartedAt { get; set; }
    public DateTime? CompletedAt { get; set; }
}

/// <summary>
/// 管理端文档重生成请求
/// </summary>
public class RegenerateRepositoryDocumentRequest
{
    public string BranchId { get; set; } = string.Empty;
    public string LanguageCode { get; set; } = string.Empty;
    public string DocumentPath { get; set; } = string.Empty;
}

/// <summary>
/// 管理端文档内容更新请求
/// </summary>
public class UpdateRepositoryDocumentContentRequest
{
    public string BranchId { get; set; } = string.Empty;
    public string LanguageCode { get; set; } = string.Empty;
    public string DocumentPath { get; set; } = string.Empty;
    public string Content { get; set; } = string.Empty;
}

public class GenerateGraphifyArtifactRequest
{
    public string? BranchId { get; set; }
}

public class AdminGraphifyArtifactDto
{
    public string Id { get; set; } = string.Empty;
    public string RepositoryId { get; set; } = string.Empty;
    public string RepositoryBranchId { get; set; } = string.Empty;
    public string BranchName { get; set; } = string.Empty;
    public int Status { get; set; }
    public string StatusName { get; set; } = string.Empty;
    public string? CommitId { get; set; }
    public string? ErrorMessage { get; set; }
    public DateTime CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
    public DateTime? StartedAt { get; set; }
    public DateTime? CompletedAt { get; set; }
}

/// <summary>
/// 管理端操作统一响应
/// </summary>
public class AdminRepositoryOperationResult
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public int? StatusCode { get; set; }
}

/// <summary>
/// 批量操作请求
/// </summary>
public class BatchOperationRequest
{
    public string[] Ids { get; set; } = Array.Empty<string>();
}
