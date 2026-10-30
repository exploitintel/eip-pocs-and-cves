using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Agents.Tools;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Repositories;

public sealed class RepositoryScanPlanResolver(IOptionsMonitor<WikiGeneratorOptions> optionsMonitor)
    : IRepositoryScanPlanResolver
{
    private const int MinDirectoryDepth = 2;
    private const int MaxDirectoryDepth = 4;
    private const int MaxTreeNodeBudget = 1500;
    private const int MaxFilesPerDirectoryBudget = 30;
    private const int MaxTotalFileBudget = 800;

    public ResolvedRepositoryScanPlan Resolve(Repository repository)
    {
        var options = optionsMonitor.CurrentValue;
        var defaultPlan = BuildDefaultPlan(repository, options, "Global");

        if (repository.ScanDepthMode == RepositoryScanDepthMode.Manual)
        {
            return BuildPlanFromRepository(repository, options, "Manual");
        }

        if (HasSavedAutoPlan(repository))
        {
            return BuildPlanFromRepository(repository, options, "Auto");
        }

        return defaultPlan;
    }

    public async Task<ResolvedRepositoryScanPlan> ResolveAndEnsureAsync(
        IContext context,
        Repository repository,
        string? workingDirectory,
        CancellationToken cancellationToken = default)
    {
        if (repository.ScanDepthMode != RepositoryScanDepthMode.Auto || HasSavedAutoPlan(repository))
        {
            return Resolve(repository);
        }

        if (string.IsNullOrWhiteSpace(workingDirectory) || !Directory.Exists(workingDirectory))
        {
            return Resolve(repository);
        }

        return await ReevaluateAsync(context, repository, workingDirectory, cancellationToken);
    }

    public async Task<ResolvedRepositoryScanPlan> ReevaluateAsync(
        IContext context,
        Repository repository,
        string workingDirectory,
        CancellationToken cancellationToken = default)
    {
        var profile = BuildStructureProfile(workingDirectory);
        var options = optionsMonitor.CurrentValue;
        var plan = DecideByRules(repository, profile, options);

        repository.ScanDepthMode = RepositoryScanDepthMode.Auto;
        repository.DirectoryTreeDepthOverride = plan.DirectoryTreeDepth;
        repository.FileListDepthOverride = plan.FileListDepth;
        repository.MaxTreeNodes = plan.MaxTreeNodes;
        repository.MaxFilesPerDirectory = plan.MaxFilesPerDirectory;
        repository.MaxTotalFiles = plan.MaxTotalFiles;
        repository.ExtraExcludedDirsJson = JsonSerializer.Serialize(plan.ExtraExcludedDirs);
        repository.ScanProfileHash = profile.Hash;
        repository.ScanProfileReason = plan.Reason;
        repository.ScanProfileConfidence = plan.Confidence;
        repository.ScanProfileUpdatedAt = DateTime.UtcNow;
        repository.UpdateTimestamp();

        context.Repositories.Update(repository);
        await context.SaveChangesAsync(cancellationToken);

        return Resolve(repository);
    }

    private static ResolvedRepositoryScanPlan DecideByRules(
        Repository repository,
        RepositoryStructureProfile profile,
        WikiGeneratorOptions options)
    {
        var directoryDepth = profile.TotalFiles switch
        {
            > 20000 => 2,
            > 5000 => 3,
            _ => 4
        };

        if (profile.MaxDirectoryWidth > 250)
        {
            directoryDepth = Math.Min(directoryDepth, 3);
        }

        var fileDepth = Math.Min(directoryDepth, profile.TotalFiles > 10000 ? 1 : 2);
        var maxTreeNodes = profile.TotalFiles > 20000 ? 900 : Math.Min(options.MaxTreeNodes, 1200);
        var maxFilesPerDirectory = profile.MaxDirectoryWidth > 200 ? 15 : Math.Min(options.MaxFilesPerDirectory, 20);
        var maxTotalFiles = profile.TotalFiles > 20000 ? 350 : Math.Min(options.MaxTotalTreeFiles, 500);
        var extraExcludedDirs = profile.SourceDirectoryCandidates.Contains("vendor", StringComparer.OrdinalIgnoreCase)
            ? new[] { "vendor" }
            : Array.Empty<string>();

        return new ResolvedRepositoryScanPlan(
            Source: "Auto",
            Mode: RepositoryScanDepthMode.Auto,
            DirectoryTreeDepth: Clamp(directoryDepth, MinDirectoryDepth, MaxDirectoryDepth),
            FileListDepth: Clamp(fileDepth, 0, directoryDepth),
            MaxTreeNodes: Clamp(maxTreeNodes, 1, MaxTreeNodeBudget),
            MaxFilesPerDirectory: Clamp(maxFilesPerDirectory, 1, MaxFilesPerDirectoryBudget),
            MaxTotalFiles: Clamp(maxTotalFiles, 1, MaxTotalFileBudget),
            ExtraExcludedDirs: extraExcludedDirs,
            ProfileHash: profile.Hash,
            Reason: BuildRuleReason(profile, options.EnableAiScanProfile),
            Confidence: profile.TotalFiles > 0 ? 0.78 : 0.55,
            UpdatedAt: repository.ScanProfileUpdatedAt);
    }

    private static RepositoryStructureProfile BuildStructureProfile(string workingDirectory)
    {
        var root = new DirectoryInfo(workingDirectory);
        var filter = new RepositoryFileFilter(root.FullName);
        var directoriesByDepth = new Dictionary<int, int>();
        var filesByDepth = new Dictionary<int, int>();
        var extensionCounts = new Dictionary<string, int>(StringComparer.OrdinalIgnoreCase);
        var keyFiles = new List<string>();
        var sourceDirs = new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        var filtered = 0;
        var maxDepth = 0;
        var maxWidth = 0;

        void Walk(DirectoryInfo dir, int depth)
        {
            maxDepth = Math.Max(maxDepth, depth);
            directoriesByDepth[depth] = directoriesByDepth.GetValueOrDefault(depth) + 1;

            FileSystemInfo[] entries;
            try
            {
                entries = dir.GetFileSystemInfos();
            }
            catch
            {
                filtered++;
                return;
            }

            maxWidth = Math.Max(maxWidth, entries.Length);
            foreach (var entry in entries)
            {
                if (filter.IsIgnored(entry.FullName))
                {
                    filtered++;
                    continue;
                }

                if (entry is DirectoryInfo childDir)
                {
                    if (IsSourceDirectoryName(childDir.Name))
                    {
                        sourceDirs.Add(filter.GetRelativePath(childDir.FullName));
                    }

                    Walk(childDir, depth + 1);
                }
                else if (entry is FileInfo file)
                {
                    filesByDepth[depth] = filesByDepth.GetValueOrDefault(depth) + 1;
                    var extension = Path.GetExtension(file.Name);
                    if (!string.IsNullOrWhiteSpace(extension))
                    {
                        extensionCounts[extension] = extensionCounts.GetValueOrDefault(extension) + 1;
                    }

                    if (IsKeyFile(file.Name))
                    {
                        keyFiles.Add(filter.GetRelativePath(file.FullName));
                    }
                }
            }
        }

        Walk(root, 0);

        var hashSource = JsonSerializer.Serialize(new
        {
            directoriesByDepth,
            filesByDepth,
            extensionCounts = extensionCounts.OrderBy(item => item.Key),
            keyFiles = keyFiles.OrderBy(item => item).Take(200),
            sourceDirs = sourceDirs.OrderBy(item => item).Take(200)
        });

        var hash = Convert.ToHexString(SHA256.HashData(Encoding.UTF8.GetBytes(hashSource)))[..16];

        return new RepositoryStructureProfile(
            TotalDirectories: directoriesByDepth.Values.Sum(),
            TotalFiles: filesByDepth.Values.Sum(),
            MaxDepth: maxDepth,
            MaxDirectoryWidth: maxWidth,
            DirectoriesByDepth: directoriesByDepth,
            FilesByDepth: filesByDepth,
            ExtensionCounts: extensionCounts,
            KeyFiles: keyFiles.OrderBy(item => item).Take(200).ToArray(),
            SourceDirectoryCandidates: sourceDirs.OrderBy(item => item).Take(200).ToArray(),
            FilteredEntries: filtered,
            Hash: hash);
    }

    private static ResolvedRepositoryScanPlan BuildPlanFromRepository(
        Repository repository,
        WikiGeneratorOptions options,
        string source)
    {
        var directoryDepth = Clamp(
            repository.DirectoryTreeDepthOverride ?? options.DirectoryTreeMaxDepth,
            MinDirectoryDepth,
            MaxDirectoryDepth);
        var fileDepth = Clamp(
            repository.FileListDepthOverride ?? options.FileListMaxDepth,
            0,
            directoryDepth);

        return new ResolvedRepositoryScanPlan(
            Source: source,
            Mode: repository.ScanDepthMode,
            DirectoryTreeDepth: directoryDepth,
            FileListDepth: fileDepth,
            MaxTreeNodes: Clamp(repository.MaxTreeNodes ?? options.MaxTreeNodes, 1, MaxTreeNodeBudget),
            MaxFilesPerDirectory: Clamp(repository.MaxFilesPerDirectory ?? options.MaxFilesPerDirectory, 1, MaxFilesPerDirectoryBudget),
            MaxTotalFiles: Clamp(repository.MaxTotalFiles ?? options.MaxTotalTreeFiles, 1, MaxTotalFileBudget),
            ExtraExcludedDirs: ParseExcludedDirs(repository.ExtraExcludedDirsJson),
            ProfileHash: repository.ScanProfileHash,
            Reason: repository.ScanProfileReason,
            Confidence: repository.ScanProfileConfidence,
            UpdatedAt: repository.ScanProfileUpdatedAt);
    }

    private static ResolvedRepositoryScanPlan BuildDefaultPlan(
        Repository repository,
        WikiGeneratorOptions options,
        string source)
    {
        var directoryDepth = Clamp(options.DirectoryTreeMaxDepth, MinDirectoryDepth, MaxDirectoryDepth);
        return new ResolvedRepositoryScanPlan(
            Source: source,
            Mode: repository.ScanDepthMode,
            DirectoryTreeDepth: directoryDepth,
            FileListDepth: Clamp(options.FileListMaxDepth, 0, directoryDepth),
            MaxTreeNodes: Clamp(options.MaxTreeNodes, 1, MaxTreeNodeBudget),
            MaxFilesPerDirectory: Clamp(options.MaxFilesPerDirectory, 1, MaxFilesPerDirectoryBudget),
            MaxTotalFiles: Clamp(options.MaxTotalTreeFiles, 1, MaxTotalFileBudget),
            ExtraExcludedDirs: [],
            ProfileHash: repository.ScanProfileHash,
            Reason: repository.ScanProfileReason,
            Confidence: repository.ScanProfileConfidence,
            UpdatedAt: repository.ScanProfileUpdatedAt);
    }

    private static bool HasSavedAutoPlan(Repository repository)
    {
        return repository.DirectoryTreeDepthOverride.HasValue ||
               repository.FileListDepthOverride.HasValue ||
               repository.MaxTreeNodes.HasValue ||
               repository.MaxFilesPerDirectory.HasValue ||
               repository.MaxTotalFiles.HasValue;
    }

    private static IReadOnlyList<string> ParseExcludedDirs(string? json)
    {
        if (string.IsNullOrWhiteSpace(json))
        {
            return [];
        }

        try
        {
            return JsonSerializer.Deserialize<string[]>(json)?
                .Where(IsSafeExcludedDir)
                .Distinct(StringComparer.OrdinalIgnoreCase)
                .ToArray() ?? [];
        }
        catch
        {
            return [];
        }
    }

    public static string SerializeExcludedDirs(IEnumerable<string>? dirs)
    {
        return JsonSerializer.Serialize((dirs ?? Enumerable.Empty<string>())
            .Where(IsSafeExcludedDir)
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToArray());
    }

    private static bool IsSafeExcludedDir(string dir)
    {
        return !string.IsNullOrWhiteSpace(dir) &&
               !Path.IsPathRooted(dir) &&
               !dir.Contains("..", StringComparison.Ordinal) &&
               dir.IndexOfAny(Path.GetInvalidPathChars()) < 0;
    }

    private static bool IsKeyFile(string fileName)
    {
        return fileName.StartsWith("README", StringComparison.OrdinalIgnoreCase) ||
               fileName is "Makefile" or "CMakeLists.txt" or "Kconfig" or "package.json" or "go.mod" or "pom.xml" or "pyproject.toml" ||
               fileName.EndsWith(".mk", StringComparison.OrdinalIgnoreCase) ||
               fileName.EndsWith(".cmake", StringComparison.OrdinalIgnoreCase);
    }

    private static bool IsSourceDirectoryName(string name)
    {
        return name is "src" or "include" or "services" or "app" or "apps" or "middleware" or "drivers" or "osdrv" or "kernel" or "build" or "vendor";
    }

    private static string BuildRuleReason(RepositoryStructureProfile profile, bool aiEnabled)
    {
        var suffix = aiEnabled
            ? " AI-assisted profiling is enabled but rule validation remains authoritative."
            : " AI-assisted profiling is disabled; rule result was used.";
        return $"Rule profile: {profile.TotalFiles} files, {profile.TotalDirectories} directories, max depth {profile.MaxDepth}, max directory width {profile.MaxDirectoryWidth}.{suffix}";
    }

    private static int Clamp(int value, int min, int max)
    {
        return Math.Min(Math.Max(value, min), max);
    }
}
