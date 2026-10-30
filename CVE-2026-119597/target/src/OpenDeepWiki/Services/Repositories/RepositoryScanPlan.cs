using OpenDeepWiki.Entities;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Services.Repositories;

public sealed record ResolvedRepositoryScanPlan(
    string Source,
    RepositoryScanDepthMode Mode,
    int DirectoryTreeDepth,
    int FileListDepth,
    int MaxTreeNodes,
    int MaxFilesPerDirectory,
    int MaxTotalFiles,
    IReadOnlyList<string> ExtraExcludedDirs,
    string? ProfileHash,
    string? Reason,
    double? Confidence,
    DateTime? UpdatedAt);

public sealed record RepositoryStructureProfile(
    int TotalDirectories,
    int TotalFiles,
    int MaxDepth,
    int MaxDirectoryWidth,
    IReadOnlyDictionary<int, int> DirectoriesByDepth,
    IReadOnlyDictionary<int, int> FilesByDepth,
    IReadOnlyDictionary<string, int> ExtensionCounts,
    IReadOnlyList<string> KeyFiles,
    IReadOnlyList<string> SourceDirectoryCandidates,
    int FilteredEntries,
    string Hash);

public interface IRepositoryScanPlanResolver
{
    ResolvedRepositoryScanPlan Resolve(Repository repository);

    Task<ResolvedRepositoryScanPlan> ResolveAndEnsureAsync(
        IContext context,
        Repository repository,
        string? workingDirectory,
        CancellationToken cancellationToken = default);

    Task<ResolvedRepositoryScanPlan> ReevaluateAsync(
        IContext context,
        Repository repository,
        string workingDirectory,
        CancellationToken cancellationToken = default);
}
