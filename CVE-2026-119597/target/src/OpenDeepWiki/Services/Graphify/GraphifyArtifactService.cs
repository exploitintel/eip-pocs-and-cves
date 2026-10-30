using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Graphify;

public interface IGraphifyArtifactService
{
    Task<AdminRepositoryOperationResult> EnqueueGenerationAsync(
        string repositoryId,
        string? branchId,
        CancellationToken cancellationToken = default);

    Task<List<AdminGraphifyArtifactDto>> GetRepositoryArtifactsAsync(
        string repositoryId,
        CancellationToken cancellationToken = default);

    Task<GraphifyArtifact?> GetCompletedArtifactAsync(
        string owner,
        string repo,
        string? branch,
        CancellationToken cancellationToken = default);
}

public class GraphifyArtifactService : IGraphifyArtifactService
{
    private readonly IContext _context;

    public GraphifyArtifactService(IContext context)
    {
        _context = context;
    }

    public async Task<AdminRepositoryOperationResult> EnqueueGenerationAsync(
        string repositoryId,
        string? branchId,
        CancellationToken cancellationToken = default)
    {
        var repository = await _context.Repositories
            .FirstOrDefaultAsync(r => r.Id == repositoryId && !r.IsDeleted, cancellationToken);

        if (repository == null)
        {
            return new AdminRepositoryOperationResult { Success = false, Message = "仓库不存在" };
        }

        if (repository.Status == RepositoryStatus.Pending || repository.Status == RepositoryStatus.Processing)
        {
            return new AdminRepositoryOperationResult
            {
                Success = false,
                Message = "仓库仍在处理中，请完成后再生成 Graphify"
            };
        }

        var branch = await ResolveBranchAsync(repository.Id, branchId, cancellationToken);
        if (branch == null)
        {
            return new AdminRepositoryOperationResult { Success = false, Message = "分支不存在" };
        }

        var artifact = await _context.GraphifyArtifacts
            .FirstOrDefaultAsync(a => a.RepositoryBranchId == branch.Id && !a.IsDeleted, cancellationToken);

        if (artifact == null)
        {
            artifact = new GraphifyArtifact
            {
                Id = Guid.NewGuid().ToString(),
                RepositoryId = repository.Id,
                RepositoryBranchId = branch.Id,
                CreatedAt = DateTime.UtcNow
            };
            _context.GraphifyArtifacts.Add(artifact);
        }
        else if (artifact.Status == GraphifyArtifactStatus.Processing)
        {
            return new AdminRepositoryOperationResult
            {
                Success = true,
                Message = "Graphify generation is already running"
            };
        }

        artifact.Status = GraphifyArtifactStatus.Pending;
        artifact.CommitId = null;
        artifact.EntryFilePath = null;
        artifact.GraphJsonPath = null;
        artifact.ReportPath = null;
        artifact.ErrorMessage = null;
        artifact.StartedAt = null;
        artifact.CompletedAt = null;
        artifact.UpdateTimestamp();

        await _context.SaveChangesAsync(cancellationToken);

        return new AdminRepositoryOperationResult
        {
            Success = true,
            Message = $"Graphify generation queued for branch {branch.BranchName}"
        };
    }

    public async Task<List<AdminGraphifyArtifactDto>> GetRepositoryArtifactsAsync(
        string repositoryId,
        CancellationToken cancellationToken = default)
    {
        return await _context.GraphifyArtifacts
            .AsNoTracking()
            .Where(a => a.RepositoryId == repositoryId && !a.IsDeleted)
            .Join(
                _context.RepositoryBranches.AsNoTracking(),
                artifact => artifact.RepositoryBranchId,
                branch => branch.Id,
                (artifact, branch) => new AdminGraphifyArtifactDto
                {
                    Id = artifact.Id,
                    RepositoryId = artifact.RepositoryId,
                    RepositoryBranchId = artifact.RepositoryBranchId,
                    BranchName = branch.BranchName,
                    Status = (int)artifact.Status,
                    StatusName = artifact.Status.ToString(),
                    CommitId = artifact.CommitId,
                    ErrorMessage = artifact.ErrorMessage,
                    CreatedAt = artifact.CreatedAt,
                    UpdatedAt = artifact.UpdatedAt,
                    StartedAt = artifact.StartedAt,
                    CompletedAt = artifact.CompletedAt
                })
            .OrderBy(a => a.BranchName)
            .ToListAsync(cancellationToken);
    }

    public async Task<GraphifyArtifact?> GetCompletedArtifactAsync(
        string owner,
        string repo,
        string? branch,
        CancellationToken cancellationToken = default)
    {
        var repository = await _context.Repositories
            .AsNoTracking()
            .FirstOrDefaultAsync(
                r => r.OrgName == owner && r.RepoName == repo && !r.IsDeleted,
                cancellationToken);

        if (repository == null)
        {
            return null;
        }

        var branchEntity = await ResolveBranchAsync(repository.Id, branch, cancellationToken);
        if (branchEntity == null)
        {
            return null;
        }

        return await _context.GraphifyArtifacts
            .AsNoTracking()
            .FirstOrDefaultAsync(
                a => a.RepositoryBranchId == branchEntity.Id &&
                     a.Status == GraphifyArtifactStatus.Completed &&
                     !a.IsDeleted,
                cancellationToken);
    }

    private async Task<RepositoryBranch?> ResolveBranchAsync(
        string repositoryId,
        string? branchIdOrName,
        CancellationToken cancellationToken)
    {
        var query = _context.RepositoryBranches
            .Where(b => b.RepositoryId == repositoryId && !b.IsDeleted);

        if (!string.IsNullOrWhiteSpace(branchIdOrName))
        {
            return await query.FirstOrDefaultAsync(
                b => b.Id == branchIdOrName || b.BranchName == branchIdOrName,
                cancellationToken);
        }

        var branches = await query
            .OrderBy(b => b.CreatedAt)
            .ToListAsync(cancellationToken);

        return branches.FirstOrDefault(b => string.Equals(b.BranchName, "main", StringComparison.OrdinalIgnoreCase))
            ?? branches.FirstOrDefault(b => string.Equals(b.BranchName, "master", StringComparison.OrdinalIgnoreCase))
            ?? branches.FirstOrDefault();
    }
}
