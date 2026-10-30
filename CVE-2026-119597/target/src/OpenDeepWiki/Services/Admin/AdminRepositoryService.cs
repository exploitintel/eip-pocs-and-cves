using Microsoft.AspNetCore.Http;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;
using OpenDeepWiki.Services.Repositories;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端仓库服务实现
/// </summary>
public class AdminRepositoryService : IAdminRepositoryService
{
    private readonly IContext _context;
    private readonly IGitPlatformService _gitPlatformService;
    private readonly IRepositoryAnalyzer _repositoryAnalyzer;
    private readonly IWikiGenerator _wikiGenerator;
    private readonly IRepositoryFullRegenerationCleaner _fullRegenerationCleaner;
    private readonly IRepositoryScanPlanResolver _scanPlanResolver;
    private readonly IRepositoryGenerationLockService _generationLockService;

    public AdminRepositoryService(
        IContext context,
        IGitPlatformService gitPlatformService,
        IRepositoryAnalyzer repositoryAnalyzer,
        IWikiGenerator wikiGenerator,
        IRepositoryFullRegenerationCleaner fullRegenerationCleaner,
        IRepositoryScanPlanResolver scanPlanResolver,
        IRepositoryGenerationLockService generationLockService)
    {
        _context = context;
        _gitPlatformService = gitPlatformService;
        _repositoryAnalyzer = repositoryAnalyzer;
        _wikiGenerator = wikiGenerator;
        _fullRegenerationCleaner = fullRegenerationCleaner;
        _scanPlanResolver = scanPlanResolver;
        _generationLockService = generationLockService;
    }

    public async Task<AdminRepositoryListResponse> GetRepositoriesAsync(int page, int pageSize, string? search, int? status)
    {
        var query = _context.Repositories.Where(r => !r.IsDeleted);

        if (!string.IsNullOrWhiteSpace(search))
        {
            query = query.Where(r => r.RepoName.Contains(search) || r.OrgName.Contains(search) || r.GitUrl.Contains(search));
        }

        if (status.HasValue)
        {
            query = query.Where(r => (int)r.Status == status.Value);
        }

        var total = await query.CountAsync();
        var items = await query
            .OrderByDescending(r => r.CreatedAt)
            .Skip((page - 1) * pageSize)
            .Take(pageSize)
            .Select(r => new AdminRepositoryDto
            {
                Id = r.Id,
                GitUrl = r.SourceLocation,
                SourceType = r.SourceType,
                SourceLocation = r.SourceLocation,
                RepoName = r.RepoName,
                OrgName = r.OrgName,
                IsPublic = r.IsPublic,
                GenerateSkill = r.GenerateSkill,
                Status = (int)r.Status,
                StatusText = GetStatusText(r.Status),
                ScanDepthMode = r.ScanDepthMode.ToString(),
                StarCount = r.StarCount,
                ForkCount = r.ForkCount,
                BookmarkCount = r.BookmarkCount,
                ViewCount = r.ViewCount,
                OwnerUserId = r.OwnerUserId,
                CreatedAt = r.CreatedAt,
                UpdatedAt = r.UpdatedAt
            })
            .ToListAsync();

        var itemIds = items.Select(item => item.Id).ToArray();
        if (itemIds.Length > 0)
        {
            var generationBranches = await _context.RepositoryBranches
                .AsNoTracking()
                .Where(branch => itemIds.Contains(branch.RepositoryId) &&
                                 !branch.IsDeleted &&
                                 branch.GenerationStatus != null)
                .Select(branch => new
                {
                    branch.RepositoryId,
                    branch.GenerationStatus
                })
                .ToListAsync();

            var generationSummary = generationBranches
                .GroupBy(branch => branch.RepositoryId)
                .ToDictionary(
                    group => group.Key,
                    group => new
                    {
                        Active = group.Count(branch =>
                            branch.GenerationStatus is BranchGenerationTaskStatus.Pending
                                or BranchGenerationTaskStatus.Processing),
                        Failed = group.Count(branch => branch.GenerationStatus == BranchGenerationTaskStatus.Failed)
                    });

            foreach (var item in items)
            {
                if (generationSummary.TryGetValue(item.Id, out var summary))
                {
                    item.BranchGenerationActiveCount = summary.Active;
                    item.BranchGenerationFailedCount = summary.Failed;
                }
            }
        }

        return new AdminRepositoryListResponse
        {
            Items = items,
            Total = total,
            Page = page,
            PageSize = pageSize
        };
    }

    public async Task<AdminRepositoryDto?> GetRepositoryByIdAsync(string id)
    {
        var repo = await _context.Repositories
            .Where(r => r.Id == id && !r.IsDeleted)
            .FirstOrDefaultAsync();

        if (repo == null) return null;

        return new AdminRepositoryDto
        {
            Id = repo.Id,
            GitUrl = repo.SourceLocation,
            SourceType = repo.SourceType,
            SourceLocation = repo.SourceLocation,
            RepoName = repo.RepoName,
            OrgName = repo.OrgName,
            IsPublic = repo.IsPublic,
            GenerateSkill = repo.GenerateSkill,
            Status = (int)repo.Status,
            StatusText = GetStatusText(repo.Status),
            ScanDepthMode = repo.ScanDepthMode.ToString(),
            ScanPlan = ToScanPlanDto(_scanPlanResolver.Resolve(repo)),
            BranchGenerationActiveCount = await _context.RepositoryBranches
                .AsNoTracking()
                .CountAsync(branch => branch.RepositoryId == repo.Id &&
                                      !branch.IsDeleted &&
                                      branch.GenerationStatus != null &&
                                      (branch.GenerationStatus == BranchGenerationTaskStatus.Pending ||
                                       branch.GenerationStatus == BranchGenerationTaskStatus.Processing)),
            BranchGenerationFailedCount = await _context.RepositoryBranches
                .AsNoTracking()
                .CountAsync(branch => branch.RepositoryId == repo.Id &&
                                      !branch.IsDeleted &&
                                      branch.GenerationStatus == BranchGenerationTaskStatus.Failed),
            StarCount = repo.StarCount,
            ForkCount = repo.ForkCount,
            BookmarkCount = repo.BookmarkCount,
            ViewCount = repo.ViewCount,
            OwnerUserId = repo.OwnerUserId,
            CreatedAt = repo.CreatedAt,
            UpdatedAt = repo.UpdatedAt
        };
    }

    public async Task<bool> UpdateRepositoryAsync(string id, UpdateRepositoryRequest request)
    {
        var repo = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);

        if (repo == null) return false;

        if (request.IsPublic.HasValue)
            repo.IsPublic = request.IsPublic.Value;
        if (request.AuthAccount != null)
            repo.AuthAccount = request.AuthAccount;
        if (request.AuthPassword != null)
            repo.AuthPassword = request.AuthPassword;

        repo.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteRepositoryAsync(string id)
    {
        var repo = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id);

        if (repo == null) return false;

        await DeleteRepositoryDataAsync([repo.Id]);
        repo.MarkAsDeleted();
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> UpdateRepositoryStatusAsync(string id, int status)
    {
        var repo = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);

        if (repo == null) return false;

        repo.Status = (RepositoryStatus)status;
        repo.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    private async Task ClearRepositoryReferencesAsync(IReadOnlyCollection<string> repositoryIds)
    {
        if (repositoryIds.Count == 0)
        {
            return;
        }

        var repositoryIdArray = repositoryIds.Distinct().ToArray();

        var branchLanguageIds = await (
            from branch in _context.RepositoryBranches
            where repositoryIdArray.Contains(branch.RepositoryId)
            join language in _context.BranchLanguages on branch.Id equals language.RepositoryBranchId
            select language.Id)
            .Distinct()
            .ToListAsync();

        if (branchLanguageIds.Count > 0)
        {
            var catalogs = await _context.DocCatalogs
                .Where(catalog => catalog.ParentId != null && branchLanguageIds.Contains(catalog.BranchLanguageId))
                .ToListAsync();

            foreach (var catalog in catalogs)
            {
                catalog.ParentId = null;
            }
        }

        var tokenUsages = await _context.TokenUsages
            .Where(usage => usage.RepositoryId != null && repositoryIdArray.Contains(usage.RepositoryId))
            .ToListAsync();
        foreach (var tokenUsage in tokenUsages)
        {
            tokenUsage.RepositoryId = null;
            tokenUsage.UpdateTimestamp();
        }

        var userActivities = await _context.UserActivities
            .Where(activity => activity.RepositoryId != null && repositoryIdArray.Contains(activity.RepositoryId))
            .ToListAsync();
        foreach (var userActivity in userActivities)
        {
            userActivity.RepositoryId = null;
            userActivity.UpdateTimestamp();
        }
    }

    private async Task DeleteRepositoryDataAsync(IReadOnlyCollection<string> repositoryIds)
    {
        if (repositoryIds.Count == 0)
        {
            return;
        }

        var repositoryIdArray = repositoryIds.Distinct().ToArray();
        await ClearRepositoryReferencesAsync(repositoryIdArray);

        var repositoryAssignments = await _context.RepositoryAssignments
            .Where(assignment => repositoryIdArray.Contains(assignment.RepositoryId))
            .ToListAsync();
        if (repositoryAssignments.Count > 0)
        {
            _context.RepositoryAssignments.RemoveRange(repositoryAssignments);
        }

        var userBookmarks = await _context.UserBookmarks
            .Where(bookmark => repositoryIdArray.Contains(bookmark.RepositoryId))
            .ToListAsync();
        if (userBookmarks.Count > 0)
        {
            _context.UserBookmarks.RemoveRange(userBookmarks);
        }

        var userSubscriptions = await _context.UserSubscriptions
            .Where(subscription => repositoryIdArray.Contains(subscription.RepositoryId))
            .ToListAsync();
        if (userSubscriptions.Count > 0)
        {
            _context.UserSubscriptions.RemoveRange(userSubscriptions);
        }

        var userDislikes = await _context.UserDislikes
            .Where(dislike => repositoryIdArray.Contains(dislike.RepositoryId))
            .ToListAsync();
        if (userDislikes.Count > 0)
        {
            _context.UserDislikes.RemoveRange(userDislikes);
        }

        var repositoryLogs = await _context.RepositoryProcessingLogs
            .Where(log => repositoryIdArray.Contains(log.RepositoryId))
            .ToListAsync();
        if (repositoryLogs.Count > 0)
        {
            _context.RepositoryProcessingLogs.RemoveRange(repositoryLogs);
        }

        var branchIds = await _context.RepositoryBranches
            .Where(branch => repositoryIdArray.Contains(branch.RepositoryId))
            .Select(branch => branch.Id)
            .ToListAsync();

        var branchIdArray = branchIds.Distinct().ToArray();
        var branchLanguageIds = branchIdArray.Length == 0
            ? new List<string>()
            : await _context.BranchLanguages
                .Where(language => branchIdArray.Contains(language.RepositoryBranchId))
                .Select(language => language.Id)
                .ToListAsync();

        var branchLanguageIdArray = branchLanguageIds.Distinct().ToArray();

        var docCatalogs = branchLanguageIdArray.Length == 0
            ? new List<DocCatalog>()
            : await _context.DocCatalogs
                .Where(catalog => branchLanguageIdArray.Contains(catalog.BranchLanguageId))
                .ToListAsync();
        if (docCatalogs.Count > 0)
        {
            _context.DocCatalogs.RemoveRange(docCatalogs);
        }

        var docFiles = branchLanguageIdArray.Length == 0
            ? new List<DocFile>()
            : await _context.DocFiles
                .Where(file => branchLanguageIdArray.Contains(file.BranchLanguageId))
                .ToListAsync();
        if (docFiles.Count > 0)
        {
            _context.DocFiles.RemoveRange(docFiles);
        }

        var translationTasks = await _context.TranslationTasks
            .Where(task => repositoryIdArray.Contains(task.RepositoryId) ||
                           branchIdArray.Contains(task.RepositoryBranchId) ||
                           branchLanguageIdArray.Contains(task.SourceBranchLanguageId))
            .ToListAsync();
        if (translationTasks.Count > 0)
        {
            _context.TranslationTasks.RemoveRange(translationTasks);
        }

        var incrementalTasks = await _context.IncrementalUpdateTasks
            .Where(task => repositoryIdArray.Contains(task.RepositoryId) ||
                           branchIdArray.Contains(task.BranchId))
            .ToListAsync();
        if (incrementalTasks.Count > 0)
        {
            _context.IncrementalUpdateTasks.RemoveRange(incrementalTasks);
        }

        var graphifyArtifacts = await _context.GraphifyArtifacts
            .Where(artifact => repositoryIdArray.Contains(artifact.RepositoryId) ||
                               branchIdArray.Contains(artifact.RepositoryBranchId))
            .ToListAsync();
        if (graphifyArtifacts.Count > 0)
        {
            _context.GraphifyArtifacts.RemoveRange(graphifyArtifacts);
        }

        if (branchLanguageIdArray.Length > 0)
        {
            var branchLanguages = await _context.BranchLanguages
                .Where(language => branchLanguageIdArray.Contains(language.Id))
                .ToListAsync();
            if (branchLanguages.Count > 0)
            {
                _context.BranchLanguages.RemoveRange(branchLanguages);
            }
        }

        if (branchIdArray.Length > 0)
        {
            var repositoryBranches = await _context.RepositoryBranches
                .Where(branch => branchIdArray.Contains(branch.Id))
                .ToListAsync();
            if (repositoryBranches.Count > 0)
            {
                _context.RepositoryBranches.RemoveRange(repositoryBranches);
            }
        }
    }

    private static string GetStatusText(RepositoryStatus status) => status switch
    {
        RepositoryStatus.Pending => "待处理",
        RepositoryStatus.Processing => "处理中",
        RepositoryStatus.Completed => "已完成",
        RepositoryStatus.Failed => "失败",
        _ => "未知"
    };

    private static AdminRepositoryScanPlanDto ToScanPlanDto(ResolvedRepositoryScanPlan plan)
    {
        return new AdminRepositoryScanPlanDto
        {
            Source = plan.Source,
            Mode = plan.Mode.ToString(),
            DirectoryTreeDepth = plan.DirectoryTreeDepth,
            FileListDepth = plan.FileListDepth,
            MaxTreeNodes = plan.MaxTreeNodes,
            MaxFilesPerDirectory = plan.MaxFilesPerDirectory,
            MaxTotalFiles = plan.MaxTotalFiles,
            ExtraExcludedDirs = plan.ExtraExcludedDirs.ToList(),
            ProfileHash = plan.ProfileHash,
            Reason = plan.Reason,
            Confidence = plan.Confidence,
            UpdatedAt = plan.UpdatedAt
        };
    }

    public async Task<SyncStatsResult> SyncRepositoryStatsAsync(string id)
    {
        var repo = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);

        if (repo == null)
        {
            return new SyncStatsResult { Success = false, Message = "仓库不存在" };
        }

        var stats = await _gitPlatformService.GetRepoStatsAsync(repo.GitUrl);
        if (stats == null)
        {
            return new SyncStatsResult { Success = false, Message = "无法获取仓库统计信息，可能是私有仓库或不支持的平台" };
        }

        repo.StarCount = stats.StarCount;
        repo.ForkCount = stats.ForkCount;
        repo.UpdatedAt = DateTime.UtcNow;

        // Sync visibility with actual Git platform state
        await SyncVisibilityAsync(repo);

        await _context.SaveChangesAsync();

        return new SyncStatsResult
        {
            Success = true,
            Message = "同步成功",
            StarCount = stats.StarCount,
            ForkCount = stats.ForkCount
        };
    }

    public async Task<BatchSyncStatsResult> BatchSyncRepositoryStatsAsync(string[] ids)
    {
        var result = new BatchSyncStatsResult
        {
            TotalCount = ids.Length
        };

        var repos = await _context.Repositories
            .Where(r => ids.Contains(r.Id) && !r.IsDeleted)
            .ToListAsync();

        foreach (var repo in repos)
        {
            var itemResult = new BatchSyncItemResult
            {
                Id = repo.Id,
                RepoName = $"{repo.OrgName}/{repo.RepoName}"
            };

            var stats = await _gitPlatformService.GetRepoStatsAsync(repo.GitUrl);
            if (stats != null)
            {
                repo.StarCount = stats.StarCount;
                repo.ForkCount = stats.ForkCount;
                repo.UpdatedAt = DateTime.UtcNow;

                // Sync visibility with actual Git platform state
                await SyncVisibilityAsync(repo);

                itemResult.Success = true;
                itemResult.StarCount = stats.StarCount;
                itemResult.ForkCount = stats.ForkCount;
                result.SuccessCount++;
            }
            else
            {
                itemResult.Success = false;
                itemResult.Message = "无法获取统计信息";
                result.FailedCount++;
            }

            result.Results.Add(itemResult);
        }

        // 处理不存在的仓库
        var foundIds = repos.Select(r => r.Id).ToHashSet();
        foreach (var id in ids.Where(id => !foundIds.Contains(id)))
        {
            result.Results.Add(new BatchSyncItemResult
            {
                Id = id,
                Success = false,
                Message = "仓库不存在"
            });
            result.FailedCount++;
        }

        await _context.SaveChangesAsync();
        return result;
    }

    public async Task<BatchRegenerateResult> BatchRegenerateRepositoriesAsync(string[] ids)
    {
        var repositoryIds = (ids ?? Array.Empty<string>())
            .Where(id => !string.IsNullOrWhiteSpace(id))
            .Select(id => id.Trim())
            .Distinct(StringComparer.Ordinal)
            .ToArray();

        if (repositoryIds.Length == 0)
        {
            return new BatchRegenerateResult();
        }

        var repositories = await _context.Repositories
            .AsNoTracking()
            .Where(repository => repositoryIds.Contains(repository.Id) && !repository.IsDeleted)
            .Select(repository => new
            {
                repository.Id,
                repository.OrgName,
                repository.RepoName
            })
            .ToListAsync();
        var repositoryNames = repositories.ToDictionary(
            repository => repository.Id,
            repository => $"{repository.OrgName}/{repository.RepoName}");

        var result = new BatchRegenerateResult
        {
            TotalCount = repositoryIds.Length
        };

        // Regeneration mutates the shared DbContext and holds a repository-level lock.
        // Keep this sequential so each repository preserves the same transaction and lock
        // semantics as the existing single-repository operation.
        foreach (var repositoryId in repositoryIds)
        {
            var repoName = repositoryNames.TryGetValue(repositoryId, out var name)
                ? name
                : repositoryId;

            try
            {
                var operation = await RegenerateRepositoryAsync(repositoryId);
                result.Results.Add(new BatchRegenerateItemResult
                {
                    Id = repositoryId,
                    RepoName = repoName,
                    Success = operation.Success,
                    Message = operation.Message
                });

                if (operation.Success)
                {
                    result.SuccessCount++;
                }
                else
                {
                    result.FailedCount++;
                }
            }
            catch
            {
                // A failed per-repository transaction must not leave tracked changes that
                // affect the remaining selected repositories.
                EfContextTransaction.ClearPendingChanges(_context);
                result.Results.Add(new BatchRegenerateItemResult
                {
                    Id = repositoryId,
                    RepoName = repoName,
                    Success = false,
                    Message = "触发全量重生成失败，请稍后重试"
                });
                result.FailedCount++;
            }
        }

        return result;
    }

    public async Task<BatchDeleteResult> BatchDeleteRepositoriesAsync(string[] ids)
    {
        var result = new BatchDeleteResult
        {
            TotalCount = ids.Length
        };

        var repos = await _context.Repositories
            .Where(r => ids.Contains(r.Id))
            .ToListAsync();

        if (repos.Count > 0)
        {
            await DeleteRepositoryDataAsync(repos.Select(r => r.Id).ToArray());
            foreach (var repo in repos)
            {
                repo.MarkAsDeleted();
            }
            result.SuccessCount = repos.Count;
        }

        // 记录不存在的仓库
        var foundIds = repos.Select(r => r.Id).ToHashSet();
        result.FailedIds = ids.Where(id => !foundIds.Contains(id)).ToList();
        result.FailedCount = result.FailedIds.Count;

        await _context.SaveChangesAsync();
        return result;
    }

    public async Task<AdminRepositoryManagementDto?> GetRepositoryManagementAsync(string id)
    {
        var repository = await _context.Repositories
            .AsNoTracking()
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);

        if (repository == null)
        {
            return null;
        }

        var branches = await _context.RepositoryBranches
            .AsNoTracking()
            .Where(b => b.RepositoryId == id && !b.IsDeleted)
            .OrderBy(b => b.CreatedAt)
            .ToListAsync();

        var branchIds = branches.Select(b => b.Id).ToList();
        var branchNameMap = branches.ToDictionary(b => b.Id, b => b.BranchName);

        var languages = await _context.BranchLanguages
            .AsNoTracking()
            .Where(l => branchIds.Contains(l.RepositoryBranchId) && !l.IsDeleted)
            .OrderBy(l => l.CreatedAt)
            .ToListAsync();

        var languageIds = languages.Select(l => l.Id).ToList();

        var catalogStats = await _context.DocCatalogs
            .AsNoTracking()
            .Where(c => languageIds.Contains(c.BranchLanguageId) && !c.IsDeleted)
            .GroupBy(c => c.BranchLanguageId)
            .Select(g => new
            {
                BranchLanguageId = g.Key,
                CatalogCount = g.Count(),
                DocumentCount = g.Count(c => c.DocFileId != null)
            })
            .ToListAsync();

        var statsMap = catalogStats.ToDictionary(
            item => item.BranchLanguageId,
            item => (item.CatalogCount, item.DocumentCount));

        var languageGroups = languages
            .GroupBy(l => l.RepositoryBranchId)
            .ToDictionary(g => g.Key, g => g.ToList());

        var branchDtos = branches.Select(branch =>
        {
            languageGroups.TryGetValue(branch.Id, out var branchLanguages);
            var languageDtos = (branchLanguages ?? new List<BranchLanguage>())
                .Select(language =>
                {
                    var stats = statsMap.TryGetValue(language.Id, out var value) ? value : (0, 0);
                    return new AdminBranchLanguageDto
                    {
                        Id = language.Id,
                        LanguageCode = language.LanguageCode,
                        IsDefault = language.IsDefault,
                        CatalogCount = stats.Item1,
                        DocumentCount = stats.Item2,
                        CreatedAt = language.CreatedAt
                    };
                })
                .OrderByDescending(language => language.IsDefault)
                .ThenBy(language => language.LanguageCode)
                .ToList();

            return new AdminRepositoryBranchDto
            {
                Id = branch.Id,
                Name = branch.BranchName,
                LastCommitId = branch.LastCommitId,
                LastProcessedAt = branch.LastProcessedAt,
                GenerationStatus = branch.GenerationStatus?.ToString(),
                LastGenerationTaskId = branch.LastGenerationTaskId,
                LastGenerationError = branch.LastGenerationError,
                LastGenerationStartedAt = branch.LastGenerationStartedAt,
                LastGenerationCompletedAt = branch.LastGenerationCompletedAt,
                Languages = languageDtos
            };
        }).ToList();

        var recentTasks = await _context.IncrementalUpdateTasks
            .AsNoTracking()
            .Where(t => t.RepositoryId == id && !t.IsDeleted)
            .OrderByDescending(t => t.CreatedAt)
            .Take(20)
            .ToListAsync();

        var taskDtos = recentTasks.Select(task =>
            new AdminIncrementalTaskDto
            {
                TaskId = task.Id,
                BranchId = task.BranchId,
                BranchName = branchNameMap.GetValueOrDefault(task.BranchId),
                Status = task.Status.ToString(),
                Priority = task.Priority,
                IsManualTrigger = task.IsManualTrigger,
                RetryCount = task.RetryCount,
                PreviousCommitId = task.PreviousCommitId,
                TargetCommitId = task.TargetCommitId,
                ErrorMessage = task.ErrorMessage,
                CreatedAt = task.CreatedAt,
                StartedAt = task.StartedAt,
                CompletedAt = task.CompletedAt
            }).ToList();

        var branchGenerationTasks = await _context.BranchGenerationTasks
            .AsNoTracking()
            .Where(t => t.RepositoryId == id && !t.IsDeleted)
            .OrderByDescending(t => t.CreatedAt)
            .Take(20)
            .ToListAsync();

        var branchTaskDtos = branchGenerationTasks.Select(task =>
            new AdminBranchGenerationTaskDto
            {
                TaskId = task.Id,
                RepositoryId = task.RepositoryId,
                BranchId = task.BranchId,
                BranchName = branchNameMap.GetValueOrDefault(task.BranchId),
                Status = task.Status.ToString(),
                Mode = task.Mode.ToString(),
                Priority = task.Priority,
                IsManualTrigger = task.IsManualTrigger,
                RetryCount = task.RetryCount,
                ErrorMessage = task.ErrorMessage,
                RequestedBy = task.RequestedBy,
                TargetCommitId = task.TargetCommitId,
                CreatedAt = task.CreatedAt,
                StartedAt = task.StartedAt,
                CompletedAt = task.CompletedAt
            }).ToList();

        return new AdminRepositoryManagementDto
        {
            RepositoryId = repository.Id,
            OrgName = repository.OrgName,
            RepoName = repository.RepoName,
            Status = (int)repository.Status,
            StatusText = GetStatusText(repository.Status),
            Branches = branchDtos,
            RecentIncrementalTasks = taskDtos,
            RecentBranchGenerationTasks = branchTaskDtos,
            ScanPlan = ToScanPlanDto(_scanPlanResolver.Resolve(repository))
        };
    }

    public async Task<AdminRepositoryScanPlanDto?> GetScanPlanAsync(string id)
    {
        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);
        return repository == null ? null : ToScanPlanDto(_scanPlanResolver.Resolve(repository));
    }

    public async Task<AdminRepositoryScanPlanDto?> UpdateScanPlanAsync(string id, UpdateRepositoryScanPlanRequest request)
    {
        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);
        if (repository == null)
        {
            return null;
        }

        var previousMode = repository.ScanDepthMode;
        var requestedMode = ParseScanDepthMode(request.Mode);
        var hasExplicitPlanValues =
            request.DirectoryTreeDepth.HasValue ||
            request.FileListDepth.HasValue ||
            request.MaxTreeNodes.HasValue ||
            request.MaxFilesPerDirectory.HasValue ||
            request.MaxTotalFiles.HasValue ||
            request.ExtraExcludedDirs is { Count: > 0 };

        repository.ScanDepthMode = requestedMode;
        if (requestedMode == RepositoryScanDepthMode.Auto && !hasExplicitPlanValues)
        {
            if (previousMode == RepositoryScanDepthMode.Manual)
            {
                repository.DirectoryTreeDepthOverride = null;
                repository.FileListDepthOverride = null;
                repository.MaxTreeNodes = null;
                repository.MaxFilesPerDirectory = null;
                repository.MaxTotalFiles = null;
                repository.ExtraExcludedDirsJson = null;
                repository.ScanProfileHash = null;
                repository.ScanProfileReason = null;
                repository.ScanProfileConfidence = null;
                repository.ScanProfileUpdatedAt = null;
            }
        }
        else
        {
            repository.DirectoryTreeDepthOverride = request.DirectoryTreeDepth;
            repository.FileListDepthOverride = request.FileListDepth;
            repository.MaxTreeNodes = request.MaxTreeNodes;
            repository.MaxFilesPerDirectory = request.MaxFilesPerDirectory;
            repository.MaxTotalFiles = request.MaxTotalFiles;
            repository.ExtraExcludedDirsJson = RepositoryScanPlanResolver.SerializeExcludedDirs(request.ExtraExcludedDirs);
        }

        if (repository.ScanDepthMode == RepositoryScanDepthMode.Manual)
        {
            repository.ScanProfileHash = null;
            repository.ScanProfileReason = null;
            repository.ScanProfileConfidence = null;
            repository.ScanProfileUpdatedAt = null;
        }
        repository.UpdateTimestamp();

        _context.Repositories.Update(repository);
        await _context.SaveChangesAsync();

        return ToScanPlanDto(_scanPlanResolver.Resolve(repository));
    }

    public async Task<AdminRepositoryScanPlanOperationResult?> ReevaluateScanPlanAsync(string id, CancellationToken cancellationToken = default)
    {
        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted, cancellationToken);
        if (repository == null)
        {
            return null;
        }

        if (repository.Status is RepositoryStatus.Pending or RepositoryStatus.Processing)
        {
            return new AdminRepositoryScanPlanOperationResult
            {
                Success = false,
                Message = "仓库正在处理中，无法重新评估扫描策略",
                ScanPlan = ToScanPlanDto(_scanPlanResolver.Resolve(repository))
            };
        }

        var branch = await _context.RepositoryBranches
            .Where(b => b.RepositoryId == id && !b.IsDeleted)
            .OrderByDescending(b => b.LastProcessedAt)
            .ThenBy(b => b.CreatedAt)
            .FirstOrDefaultAsync(cancellationToken);
        if (branch == null)
        {
            return new AdminRepositoryScanPlanOperationResult
            {
                Success = false,
                Message = "仓库没有可评估的分支",
                ScanPlan = ToScanPlanDto(_scanPlanResolver.Resolve(repository))
            };
        }

        var workspace = await _repositoryAnalyzer.PrepareWorkspaceAsync(repository, branch.BranchName, branch.LastCommitId, cancellationToken);
        try
        {
            var plan = await _scanPlanResolver.ReevaluateAsync(_context, repository, workspace.WorkingDirectory, cancellationToken);
            return new AdminRepositoryScanPlanOperationResult
            {
                Success = true,
                Message = "扫描策略已重新评估",
                ScanPlan = ToScanPlanDto(plan)
            };
        }
        finally
        {
            await _repositoryAnalyzer.CleanupWorkspaceAsync(workspace, cancellationToken);
        }
    }

    private static RepositoryScanDepthMode ParseScanDepthMode(string? mode)
    {
        return Enum.TryParse<RepositoryScanDepthMode>(mode, ignoreCase: true, out var parsed)
            ? parsed
            : RepositoryScanDepthMode.Auto;
    }

    public async Task<AdminRepositoryOperationResult> RegenerateRepositoryAsync(string id)
    {
        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted);

        if (repository == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库不存在"
            };
        }

        if (repository.Status == RepositoryStatus.Pending || repository.Status == RepositoryStatus.Processing)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库正在处理中，无法重复触发",
                StatusCode = StatusCodes.Status409Conflict
            };
        }

        await using var transaction = await EfContextTransaction.BeginIfSupportedAsync(_context, CancellationToken.None);
        var lockAcquired = await _generationLockService.TryAcquireAsync(
            _context,
            repository.Id,
            RepositoryGenerationLockOwnerType.Repository,
            repository.Id,
            RepositoryGenerationLockScope.Repository);

        if (!lockAcquired)
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync();
            }

            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库已有 branch 生成任务正在排队或处理中",
                StatusCode = StatusCodes.Status409Conflict
            };
        }

        try
        {
            await _fullRegenerationCleaner.CleanAsync(_context, repository);

            repository.Status = RepositoryStatus.Pending;
            repository.UpdateTimestamp();
            await _context.SaveChangesAsync();
            if (transaction is not null)
            {
                await transaction.CommitAsync();
            }
        }
        catch
        {
            if (transaction is not null)
            {
                await transaction.RollbackAsync();
            }
            else
            {
                await _generationLockService.ReleaseAsync(
                    _context,
                    repository.Id,
                    RepositoryGenerationLockOwnerType.Repository,
                    repository.Id,
                    CancellationToken.None);
            }

            throw;
        }

        return new AdminRepositoryOperationResult
        {
            Success = true,
            Message = "已触发全量重生成"
        };
    }

    public async Task<AdminRepositoryOperationResult> RegenerateDocumentAsync(
        string id,
        RegenerateRepositoryDocumentRequest request,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(request.BranchId) ||
            string.IsNullOrWhiteSpace(request.LanguageCode) ||
            string.IsNullOrWhiteSpace(request.DocumentPath))
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "请求参数不完整"
            };
        }

        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted, cancellationToken);
        if (repository == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库不存在"
            };
        }

        var branch = await _context.RepositoryBranches
            .FirstOrDefaultAsync(
                b => b.Id == request.BranchId && b.RepositoryId == id && !b.IsDeleted,
                cancellationToken);
        if (branch == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "分支不存在"
            };
        }

        var normalizedLanguage = request.LanguageCode.Trim();
        var branchLanguage = await _context.BranchLanguages
            .FirstOrDefaultAsync(
                l => l.RepositoryBranchId == branch.Id &&
                     !l.IsDeleted &&
                     l.LanguageCode.ToLower() == normalizedLanguage.ToLower(),
                cancellationToken);
        if (branchLanguage == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "语言不存在"
            };
        }

        var normalizedPath = NormalizeDocPath(request.DocumentPath);
        var catalog = await _context.DocCatalogs.FirstOrDefaultAsync(
            c => c.BranchLanguageId == branchLanguage.Id &&
                 c.Path == normalizedPath &&
                 !c.IsDeleted,
            cancellationToken);
        if (catalog == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "文档不存在"
            };
        }

        var hasChildren = await _context.DocCatalogs.AnyAsync(
            c => c.BranchLanguageId == branchLanguage.Id &&
                 c.ParentId == catalog.Id &&
                 !c.IsDeleted,
            cancellationToken);
        if (hasChildren)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "Navigation catalog nodes do not generate documents. Select a child document to regenerate."
            };
        }

        if (_wikiGenerator is WikiGenerator generator)
        {
            generator.SetCurrentRepository(repository.Id, $"{repository.OrgName}/{repository.RepoName}");
        }

        try
        {
            var workspace = await _repositoryAnalyzer.PrepareWorkspaceAsync(
                repository,
                branch.BranchName,
                branch.LastCommitId,
                cancellationToken);

            try
            {
                await _wikiGenerator.RegenerateDocumentAsync(
                    workspace,
                    branchLanguage,
                    normalizedPath,
                    cancellationToken);
            }
            finally
            {
                await _repositoryAnalyzer.CleanupWorkspaceAsync(workspace, cancellationToken);
            }

            repository.UpdateTimestamp();
            await _context.SaveChangesAsync(cancellationToken);

            return new AdminRepositoryOperationResult
            {
                Success = true,
                Message = "文档重生成已完成"
            };
        }
        catch (Exception ex)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = $"文档重生成失败: {ex.Message}"
            };
        }
    }

    public async Task<AdminRepositoryOperationResult> UpdateDocumentContentAsync(
        string id,
        UpdateRepositoryDocumentContentRequest request,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(request.BranchId) ||
            string.IsNullOrWhiteSpace(request.LanguageCode) ||
            string.IsNullOrWhiteSpace(request.DocumentPath))
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "请求参数不完整"
            };
        }

        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == id && !r.IsDeleted, cancellationToken);
        if (repository == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库不存在"
            };
        }

        var branch = await _context.RepositoryBranches
            .FirstOrDefaultAsync(
                b => b.Id == request.BranchId && b.RepositoryId == id && !b.IsDeleted,
                cancellationToken);
        if (branch == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "分支不存在"
            };
        }

        var normalizedLanguage = request.LanguageCode.Trim();
        var branchLanguage = await _context.BranchLanguages
            .FirstOrDefaultAsync(
                l => l.RepositoryBranchId == branch.Id &&
                     !l.IsDeleted &&
                     l.LanguageCode.ToLower() == normalizedLanguage.ToLower(),
                cancellationToken);
        if (branchLanguage == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "语言不存在"
            };
        }

        var normalizedPath = NormalizeDocPath(request.DocumentPath);
        var catalog = await _context.DocCatalogs
            .FirstOrDefaultAsync(
                c => c.BranchLanguageId == branchLanguage.Id &&
                     c.Path == normalizedPath &&
                     !c.IsDeleted,
                cancellationToken);

        if (catalog == null || string.IsNullOrWhiteSpace(catalog.DocFileId))
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "文档不存在或不可编辑"
            };
        }

        var hasChildren = await _context.DocCatalogs.AnyAsync(
            c => c.BranchLanguageId == branchLanguage.Id &&
                 c.ParentId == catalog.Id &&
                 !c.IsDeleted,
            cancellationToken);
        if (hasChildren)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "Navigation catalog nodes cannot be edited as documents. Select a child document instead."
            };
        }

        var docFile = await _context.DocFiles
            .FirstOrDefaultAsync(f => f.Id == catalog.DocFileId && !f.IsDeleted, cancellationToken);
        if (docFile == null)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "文档文件不存在"
            };
        }

        docFile.Content = MermaidMarkdownNormalizer.Normalize(request.Content ?? string.Empty);
        docFile.UpdateTimestamp();
        repository.UpdateTimestamp();

        _context.RepositoryProcessingLogs.Add(new RepositoryProcessingLog
        {
            Id = Guid.NewGuid().ToString(),
            RepositoryId = repository.Id,
            Step = ProcessingStep.Content,
            Message = $"管理端手动更新文档：{normalizedPath}",
            IsAiOutput = false,
            ToolName = "AdminDocEditor",
            CreatedAt = DateTime.UtcNow
        });

        await _context.SaveChangesAsync(cancellationToken);

        return new AdminRepositoryOperationResult
        {
            Success = true,
            Message = "文档内容已保存"
        };
    }

    /// <summary>
    /// Sync repository visibility with the actual Git platform state
    /// </summary>
    private async Task SyncVisibilityAsync(Repository repo)
    {
        try
        {
            if (!IsPublicPlatform(repo.GitUrl) ||
                string.IsNullOrWhiteSpace(repo.OrgName) ||
                string.IsNullOrWhiteSpace(repo.RepoName))
            {
                return;
            }

            var repoInfo = await _gitPlatformService.CheckRepoExistsAsync(repo.OrgName, repo.RepoName);
            if (!repoInfo.Exists)
            {
                return;
            }

            var shouldBePublic = !repoInfo.IsPrivate;
            if (repo.IsPublic != shouldBePublic)
            {
                repo.IsPublic = shouldBePublic;
            }
        }
        catch
        {
            // Visibility sync is best-effort; don't fail the parent operation
        }
    }

    /// <summary>
    /// Check if the git URL is from a supported public platform
    /// </summary>
    private static bool IsPublicPlatform(string gitUrl)
    {
        try
        {
            var uri = new Uri(gitUrl);
            var host = uri.Host.ToLowerInvariant();
            return host is "github.com" or "gitee.com" or "gitlab.com";
        }
        catch
        {
            return false;
        }
    }

    private static string NormalizeDocPath(string path)
    {
        return path.Trim().Trim('/');
    }
}
