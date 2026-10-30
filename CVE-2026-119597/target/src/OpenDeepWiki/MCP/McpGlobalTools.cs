using System.ComponentModel;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using ModelContextProtocol.Server;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.MCP;

/// <summary>
/// MCP tools for the unscoped /api/mcp endpoint. These tools route user questions
/// across repositories before reading specific generated wiki pages.
/// </summary>
[McpServerToolType]
public class McpGlobalTools
{
    private const int MaxRepositoryResults = 20;
    private const int MaxDocumentResults = 50;
    private const int DefaultSnippetLength = 500;

    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    [McpServerTool, Description("List repositories available in OpenDeepWiki with branch/language metadata. Use this to discover repository owners and names.")]
    public static async Task<string> ListRepositories(
        IContext context,
        [Description("Optional text used to filter owner, repository name, description, or primary language.")] string? query = null,
        [Description("Maximum repositories to return. Default: 50, max: 200.")] int maxResults = 50,
        CancellationToken cancellationToken = default)
    {
        maxResults = Clamp(maxResults, 1, 200);
        var normalizedQuery = Normalize(query);

        var repositoryRows = await context.Repositories
            .AsNoTracking()
            .Where(repository => !repository.IsDeleted && repository.Status == RepositoryStatus.Completed)
            .Select(repository => new
            {
                repository.Id,
                repository.OrgName,
                repository.RepoName,
                repository.Description,
                repository.PrimaryLanguage,
                repository.LastUpdateCheckAt
            })
            .ToListAsync(cancellationToken);

        var repositoryIds = repositoryRows.Select(repository => repository.Id).ToHashSet();
        var branches = await context.RepositoryBranches
            .AsNoTracking()
            .Where(branch => !branch.IsDeleted && repositoryIds.Contains(branch.RepositoryId))
            .Select(branch => new
            {
                branch.RepositoryId,
                branch.BranchName
            })
            .ToListAsync(cancellationToken);

        var languages = await context.RepositoryBranches
            .AsNoTracking()
            .Where(branch => !branch.IsDeleted && repositoryIds.Contains(branch.RepositoryId))
            .Join(
                context.BranchLanguages.AsNoTracking().Where(language => !language.IsDeleted),
                branch => branch.Id,
                language => language.RepositoryBranchId,
                (branch, language) => new
                {
                    branch.RepositoryId,
                    language.LanguageCode
                })
            .ToListAsync(cancellationToken);

        var languageLookup = languages
            .GroupBy(item => item.RepositoryId)
            .ToDictionary(
                group => group.Key,
                group => group.Select(item => item.LanguageCode)
                    .Distinct(StringComparer.OrdinalIgnoreCase)
                    .OrderBy(item => item, StringComparer.OrdinalIgnoreCase)
                    .ToList());

        var branchLookup = branches
            .GroupBy(branch => branch.RepositoryId)
            .ToDictionary(
                group => group.Key,
                group => group.Select(branch => branch.BranchName)
                    .Distinct(StringComparer.OrdinalIgnoreCase)
                    .OrderBy(item => item, StringComparer.OrdinalIgnoreCase)
                    .ToList());

        var repositories = repositoryRows
            .Select(repository => new RepositoryListItem(
                repository.Id,
                repository.OrgName,
                repository.RepoName,
                repository.Description,
                repository.PrimaryLanguage,
                repository.LastUpdateCheckAt,
                branchLookup.TryGetValue(repository.Id, out var branchList)
                    ? branchList
                    : [],
                languageLookup.TryGetValue(repository.Id, out var languageList)
                    ? languageList
                    : []))
            .Where(item => string.IsNullOrWhiteSpace(normalizedQuery) || MatchesAny(
                normalizedQuery,
                item.Owner,
                item.Repo,
                item.Description,
                item.PrimaryLanguage,
                string.Join(' ', item.Languages)))
            .OrderBy(item => item.Owner, StringComparer.OrdinalIgnoreCase)
            .ThenBy(item => item.Repo, StringComparer.OrdinalIgnoreCase)
            .Take(maxResults)
            .ToList();

        return ToJson(new
        {
            query = normalizedQuery,
            count = repositories.Count,
            repositories
        });
    }

    [McpServerTool, Description("Route a user question to the most relevant repositories by scoring repository metadata, wiki titles, and wiki content.")]
    public static async Task<string> SearchRepositories(
        IContext context,
        [Description("User question or search query used to identify relevant repositories.")] string query,
        [Description("Preferred wiki language code. Default: zh. Pass empty to search every language.")] string language = "zh",
        [Description("Maximum repositories to return. Default: 5, max: 20.")] int maxResults = 5,
        CancellationToken cancellationToken = default)
    {
        var normalizedQuery = Normalize(query);
        if (string.IsNullOrWhiteSpace(normalizedQuery))
        {
            return ToJson(new { error = true, message = "Search query is required" });
        }

        maxResults = Clamp(maxResults, 1, MaxRepositoryResults);
        var tokens = Tokenize(normalizedQuery);
        var repositories = await LoadRepositorySearchRowsAsync(context, Normalize(language), cancellationToken);

        var ranked = repositories
            .Select(row => ScoreRepository(row, tokens, normalizedQuery))
            .Where(IsRelevantMatch)
            .OrderByDescending(result => result.Score)
            .ThenBy(result => result.Owner, StringComparer.OrdinalIgnoreCase)
            .ThenBy(result => result.Repo, StringComparer.OrdinalIgnoreCase)
            .Take(maxResults)
            .ToList();

        return ToJson(new
        {
            query = normalizedQuery,
            language = Normalize(language),
            count = ranked.Count,
            repositories = ranked
        });
    }

    [McpServerTool, Description("Search generated wiki documentation. If owner/repo are omitted, routes the query to relevant repositories first and searches across them.")]
    public static async Task<string> SearchDocs(
        IContext context,
        [Description("User question or search query.")] string query,
        [Description("Optional repository owner/org. If omitted, relevant repositories are selected automatically.")] string? owner = null,
        [Description("Optional repository name. If omitted, relevant repositories are selected automatically.")] string? repo = null,
        [Description("Preferred wiki language code. Default: zh. Pass empty to search every language.")] string language = "zh",
        [Description("Maximum routed repositories when owner/repo are omitted. Default: 5, max: 20.")] int maxRepositories = 5,
        [Description("Maximum document results. Default: 10, max: 50.")] int maxResults = 10,
        CancellationToken cancellationToken = default)
    {
        var normalizedQuery = Normalize(query);
        if (string.IsNullOrWhiteSpace(normalizedQuery))
        {
            return ToJson(new { error = true, message = "Search query is required" });
        }

        maxRepositories = Clamp(maxRepositories, 1, MaxRepositoryResults);
        maxResults = Clamp(maxResults, 1, MaxDocumentResults);
        var normalizedOwner = Normalize(owner);
        var normalizedRepo = Normalize(repo);
        var normalizedLanguage = Normalize(language);
        var tokens = Tokenize(normalizedQuery);

        List<RepositoryRouteResult> routedRepositories;
        if (!string.IsNullOrWhiteSpace(normalizedOwner) && !string.IsNullOrWhiteSpace(normalizedRepo))
        {
            var exists = await context.Repositories
                .AsNoTracking()
                .AnyAsync(repository => !repository.IsDeleted &&
                                        repository.Status == RepositoryStatus.Completed &&
                                        repository.OrgName.ToLower() == normalizedOwner.ToLower() &&
                                        repository.RepoName.ToLower() == normalizedRepo.ToLower(),
                    cancellationToken);

            if (!exists)
            {
                return ToJson(new
                {
                    error = true,
                    message = $"Repository {normalizedOwner}/{normalizedRepo} not found"
                });
            }

            routedRepositories =
            [
                new RepositoryRouteResult(normalizedOwner!, normalizedRepo!, 0, 0, true, "explicit repository scope")
            ];
        }
        else
        {
            routedRepositories = (await LoadRepositorySearchRowsAsync(context, normalizedLanguage, cancellationToken))
                .Select(row => ScoreRepository(row, tokens, normalizedQuery))
                .Where(IsRelevantMatch)
                .OrderByDescending(result => result.Score)
                .ThenBy(result => result.Owner, StringComparer.OrdinalIgnoreCase)
                .ThenBy(result => result.Repo, StringComparer.OrdinalIgnoreCase)
                .Take(maxRepositories)
                .ToList();
        }

        if (routedRepositories.Count == 0)
        {
            return ToJson(new
            {
                query = normalizedQuery,
                language = normalizedLanguage,
                routedRepositories,
                count = 0,
                results = Array.Empty<object>()
            });
        }

        var routeKeys = routedRepositories
            .Select(repository => $"{repository.Owner}\n{repository.Repo}")
            .ToHashSet(StringComparer.OrdinalIgnoreCase);

        var documents = await LoadDocumentSearchRowsAsync(context, normalizedLanguage, cancellationToken);
        var scoredDocuments = documents
            .Where(document => routeKeys.Contains($"{document.Owner}\n{document.Repo}"))
            .Select(document => ScoreDocument(document, tokens, normalizedQuery))
            .Where(IsRelevantMatch)
            .OrderByDescending(result => result.Score)
            .ThenBy(result => result.Owner, StringComparer.OrdinalIgnoreCase)
            .ThenBy(result => result.Repo, StringComparer.OrdinalIgnoreCase)
            .ThenBy(result => result.Path, StringComparer.OrdinalIgnoreCase)
            .Take(maxResults)
            .ToList();

        return ToJson(new
        {
            query = normalizedQuery,
            language = normalizedLanguage,
            routedRepositories,
            count = scoredDocuments.Count,
            results = scoredDocuments
        });
    }

    [McpServerTool, Description("Read one generated wiki document by repository owner, repository name, document path, and optional language/branch.")]
    public static async Task<string> ReadDoc(
        IContext context,
        [Description("Repository owner/org, for example YD_HW/services.")] string owner,
        [Description("Repository name.")] string repo,
        [Description("Wiki document path returned by SearchDocs.")] string path,
        [Description("Preferred wiki language code. Default: zh. Pass empty to allow any language.")] string language = "zh",
        [Description("Optional branch name. If omitted, the first matching processed branch is used.")] string? branch = null,
        CancellationToken cancellationToken = default)
    {
        var normalizedOwner = Normalize(owner);
        var normalizedRepo = Normalize(repo);
        var normalizedPath = NormalizePath(path);
        var normalizedLanguage = Normalize(language);
        var normalizedBranch = Normalize(branch);

        if (string.IsNullOrWhiteSpace(normalizedOwner) ||
            string.IsNullOrWhiteSpace(normalizedRepo) ||
            string.IsNullOrWhiteSpace(normalizedPath))
        {
            return ToJson(new { error = true, message = "owner, repo, and path are required" });
        }

        var documents = await LoadDocumentSearchRowsAsync(context, normalizedLanguage, cancellationToken);
        var document = documents
            .Where(item => string.Equals(item.Owner, normalizedOwner, StringComparison.OrdinalIgnoreCase) &&
                           string.Equals(item.Repo, normalizedRepo, StringComparison.OrdinalIgnoreCase) &&
                           string.Equals(item.Path, normalizedPath, StringComparison.OrdinalIgnoreCase) &&
                           (string.IsNullOrWhiteSpace(normalizedBranch) ||
                            string.Equals(item.Branch, normalizedBranch, StringComparison.OrdinalIgnoreCase)))
            .OrderByDescending(item => string.Equals(item.Language, normalizedLanguage, StringComparison.OrdinalIgnoreCase))
            .ThenByDescending(item => item.IsDefaultLanguage)
            .FirstOrDefault();

        if (document == null && !string.IsNullOrWhiteSpace(normalizedLanguage))
        {
            documents = await LoadDocumentSearchRowsAsync(context, null, cancellationToken);
            document = documents
                .Where(item => string.Equals(item.Owner, normalizedOwner, StringComparison.OrdinalIgnoreCase) &&
                               string.Equals(item.Repo, normalizedRepo, StringComparison.OrdinalIgnoreCase) &&
                               string.Equals(item.Path, normalizedPath, StringComparison.OrdinalIgnoreCase) &&
                               (string.IsNullOrWhiteSpace(normalizedBranch) ||
                                string.Equals(item.Branch, normalizedBranch, StringComparison.OrdinalIgnoreCase)))
                .OrderByDescending(item => item.IsDefaultLanguage)
                .FirstOrDefault();
        }

        if (document == null)
        {
            return ToJson(new
            {
                error = true,
                message = $"Document {normalizedOwner}/{normalizedRepo}:{normalizedPath} not found"
            });
        }

        return ToJson(new
        {
            repository = $"{document.Owner}/{document.Repo}",
            document.Owner,
            document.Repo,
            document.Branch,
            document.Language,
            document.Title,
            document.Path,
            sourceFiles = TryParseSourceFiles(document.SourceFiles),
            content = document.Content
        });
    }

    private static async Task<List<RepositorySearchRow>> LoadRepositorySearchRowsAsync(
        IContext context,
        string? language,
        CancellationToken cancellationToken)
    {
        var repositoryRows = await context.Repositories
            .AsNoTracking()
            .Where(repository => !repository.IsDeleted && repository.Status == RepositoryStatus.Completed)
            .Select(repository => new
            {
                repository.Id,
                repository.OrgName,
                repository.RepoName,
                repository.Description,
                repository.PrimaryLanguage,
                repository.LastUpdateCheckAt
            })
            .ToListAsync(cancellationToken);

        var metadata = repositoryRows.ToDictionary(
            repository => repository.Id,
            repository => new RepositorySearchAccumulator(
                repository.Id,
                repository.OrgName,
                repository.RepoName,
                repository.Description,
                repository.PrimaryLanguage,
                repository.LastUpdateCheckAt));

        var documentRows = await LoadDocumentSearchRowsAsync(context, language, cancellationToken);
        foreach (var document in documentRows)
        {
            var repositoryId = document.RepositoryId;
            if (!metadata.TryGetValue(repositoryId, out var accumulator))
            {
                continue;
            }

            accumulator.DocumentTitles.Add(document.Title);
            accumulator.DocumentPaths.Add(document.Path);
            if (!string.IsNullOrWhiteSpace(document.Content))
            {
                accumulator.ContentSamples.Add(document.Content);
            }
        }

        return metadata.Values
            .Select(item => new RepositorySearchRow(
                item.Id,
                item.Owner,
                item.Repo,
                item.Description,
                item.PrimaryLanguage,
                item.LastUpdateCheckAt,
                item.DocumentTitles,
                item.DocumentPaths,
                item.ContentSamples))
            .ToList();
    }

    private static async Task<List<DocumentSearchRow>> LoadDocumentSearchRowsAsync(
        IContext context,
        string? language,
        CancellationToken cancellationToken)
    {
        var query = context.Repositories
            .AsNoTracking()
            .Where(repository => !repository.IsDeleted && repository.Status == RepositoryStatus.Completed)
            .Join(
                context.RepositoryBranches.AsNoTracking().Where(branch => !branch.IsDeleted),
                repository => repository.Id,
                branch => branch.RepositoryId,
                (repository, branch) => new { repository, branch })
            .Join(
                context.BranchLanguages.AsNoTracking().Where(branchLanguage => !branchLanguage.IsDeleted),
                pair => pair.branch.Id,
                branchLanguage => branchLanguage.RepositoryBranchId,
                (pair, branchLanguage) => new { pair.repository, pair.branch, branchLanguage })
            .Join(
                context.DocCatalogs.AsNoTracking().Where(catalog => !catalog.IsDeleted &&
                                                                    catalog.DocFileId != null &&
                                                                    catalog.DocFileId != string.Empty),
                pair => pair.branchLanguage.Id,
                catalog => catalog.BranchLanguageId,
                (pair, catalog) => new { pair.repository, pair.branch, pair.branchLanguage, catalog })
            .Join(
                context.DocFiles.AsNoTracking().Where(docFile => !docFile.IsDeleted),
                pair => pair.catalog.DocFileId!,
                docFile => docFile.Id,
                (pair, docFile) => new
                {
                    RepositoryId = pair.repository.Id,
                    Owner = pair.repository.OrgName,
                    Repo = pair.repository.RepoName,
                    Branch = pair.branch.BranchName,
                    Language = pair.branchLanguage.LanguageCode,
                    pair.branchLanguage.IsDefault,
                    Title = pair.catalog.Title,
                    Path = pair.catalog.Path,
                    Content = docFile.Content,
                    docFile.SourceFiles
                });

        if (!string.IsNullOrWhiteSpace(language))
        {
            query = query.Where(item => item.Language == language);
        }

        var rows = await query.ToListAsync(cancellationToken);
        return rows
            .Select(row => new DocumentSearchRow(
                row.RepositoryId,
                row.Owner,
                row.Repo,
                row.Branch,
                row.Language,
                row.IsDefault,
                row.Title,
                row.Path,
                row.Content,
                row.SourceFiles))
            .ToList();
    }

    private static RepositoryRouteResult ScoreRepository(
        RepositorySearchRow row,
        IReadOnlyList<string> tokens,
        string query)
    {
        var score = 0;
        var reasons = new List<string>();
        var matchedTokens = new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        score += ScoreText(row.Owner, tokens, 8, reasons, "owner", matchedTokens);
        score += ScoreText(row.Repo, tokens, 10, reasons, "repo", matchedTokens);
        score += ScoreText(row.Description, tokens, 6, reasons, "description", matchedTokens);
        score += ScoreText(row.PrimaryLanguage, tokens, 3, reasons, "primary language", matchedTokens);

        var titleScore = row.DocumentTitles.Sum(title => ScoreText(title, tokens, 4, reasons, "wiki title", matchedTokens));
        var pathScore = row.DocumentPaths.Sum(path => ScoreText(path, tokens, 3, reasons, "wiki path", matchedTokens));
        var contentScore = row.ContentSamples.Sum(content => ScoreText(content, tokens, 1, reasons, "wiki content", matchedTokens));
        score += Math.Min(titleScore, 40);
        score += Math.Min(pathScore, 30);
        score += Math.Min(contentScore, 80);

        return new RepositoryRouteResult(
            row.Owner,
            row.Repo,
            score,
            matchedTokens.Count,
            ContainsFullQuery(row, query),
            string.Join(", ", reasons.Distinct(StringComparer.OrdinalIgnoreCase).Take(6)));
    }

    private static DocumentSearchResult ScoreDocument(
        DocumentSearchRow row,
        IReadOnlyList<string> tokens,
        string query)
    {
        var reasons = new List<string>();
        var matchedTokens = new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        var score = 0;
        score += ScoreText(row.Owner, tokens, 5, reasons, "owner", matchedTokens);
        score += ScoreText(row.Repo, tokens, 6, reasons, "repo", matchedTokens);
        score += ScoreText(row.Title, tokens, 8, reasons, "title", matchedTokens);
        score += ScoreText(row.Path, tokens, 5, reasons, "path", matchedTokens);
        score += ScoreText(row.Content, tokens, 2, reasons, "content", matchedTokens);

        return new DocumentSearchResult(
            row.Owner,
            row.Repo,
            row.Branch,
            row.Language,
            row.Title,
            row.Path,
            score,
            matchedTokens.Count,
            ContainsFullQuery(row, query),
            string.Join(", ", reasons.Distinct(StringComparer.OrdinalIgnoreCase).Take(6)),
            BuildSnippet(row.Content, tokens, query, DefaultSnippetLength));
    }

    private static int ScoreText(
        string? text,
        IReadOnlyList<string> tokens,
        int weight,
        List<string> reasons,
        string reason,
        HashSet<string> matchedTokens)
    {
        if (string.IsNullOrWhiteSpace(text))
        {
            return 0;
        }

        var normalized = text.ToLowerInvariant();
        var score = 0;
        foreach (var token in tokens)
        {
            if (!normalized.Contains(token, StringComparison.OrdinalIgnoreCase))
            {
                continue;
            }

            score += weight;
            matchedTokens.Add(token);
            reasons.Add(reason);
        }

        return score;
    }

    private static bool IsRelevantMatch(ISearchResult result)
    {
        return result.Score > 0 && (result.FullQueryMatched || result.MatchedTokenCount >= 2);
    }

    private static bool ContainsFullQuery(RepositorySearchRow row, string query)
    {
        return ContainsFullQuery(row.Owner, query) ||
               ContainsFullQuery(row.Repo, query) ||
               ContainsFullQuery(row.Description, query) ||
               ContainsFullQuery(row.PrimaryLanguage, query) ||
               row.DocumentTitles.Any(title => ContainsFullQuery(title, query)) ||
               row.DocumentPaths.Any(path => ContainsFullQuery(path, query)) ||
               row.ContentSamples.Any(content => ContainsFullQuery(content, query));
    }

    private static bool ContainsFullQuery(DocumentSearchRow row, string query)
    {
        return ContainsFullQuery(row.Owner, query) ||
               ContainsFullQuery(row.Repo, query) ||
               ContainsFullQuery(row.Title, query) ||
               ContainsFullQuery(row.Path, query) ||
               ContainsFullQuery(row.Content, query);
    }

    private static bool ContainsFullQuery(string? text, string query)
    {
        return !string.IsNullOrWhiteSpace(text) &&
               !string.IsNullOrWhiteSpace(query) &&
               text.Contains(query, StringComparison.OrdinalIgnoreCase);
    }

    private static string BuildSnippet(
        string content,
        IReadOnlyList<string> tokens,
        string query,
        int maxLength)
    {
        if (string.IsNullOrWhiteSpace(content))
        {
            return string.Empty;
        }

        var firstMatch = -1;
        foreach (var token in tokens)
        {
            firstMatch = content.IndexOf(token, StringComparison.OrdinalIgnoreCase);
            if (firstMatch >= 0)
            {
                break;
            }
        }

        if (firstMatch < 0)
        {
            firstMatch = content.IndexOf(query, StringComparison.OrdinalIgnoreCase);
        }

        var start = firstMatch < 0 ? 0 : Math.Max(0, firstMatch - 160);
        var length = Math.Min(maxLength, content.Length - start);
        var snippet = content.Substring(start, length).Trim();

        if (start > 0)
        {
            snippet = "..." + snippet;
        }

        if (start + length < content.Length)
        {
            snippet += "...";
        }

        return snippet;
    }

    private static bool MatchesAny(string query, params string?[] values)
    {
        return values.Any(value => !string.IsNullOrWhiteSpace(value) &&
                                   value.Contains(query, StringComparison.OrdinalIgnoreCase));
    }

    private static List<string> Tokenize(string query)
    {
        var parts = query
            .ToLowerInvariant()
            .Split([' ', '\t', '\r', '\n', ',', '.', ';', ':', '/', '\\', '-', '_', '(', ')', '[', ']', '{', '}'],
                StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries)
            .Where(token => token.Length > 1);

        var tokens = new List<string>();
        foreach (var part in parts)
        {
            tokens.Add(part);
            if (!ContainsCjk(part))
            {
                continue;
            }

            for (var size = 2; size <= 3; size++)
            {
                if (part.Length < size)
                {
                    continue;
                }

                for (var index = 0; index <= part.Length - size; index++)
                {
                    tokens.Add(part.Substring(index, size));
                }
            }
        }

        return tokens
            .Distinct(StringComparer.OrdinalIgnoreCase)
            .ToList();
    }

    private static bool ContainsCjk(string value)
    {
        return value.Any(character => character is >= '\u4e00' and <= '\u9fff');
    }

    private static List<string> TryParseSourceFiles(string? value)
    {
        if (string.IsNullOrWhiteSpace(value))
        {
            return [];
        }

        try
        {
            return JsonSerializer.Deserialize<List<string>>(value) ?? [];
        }
        catch
        {
            return [];
        }
    }

    private static int Clamp(int value, int min, int max)
    {
        return Math.Min(Math.Max(value, min), max);
    }

    private static string? Normalize(string? value)
    {
        return string.IsNullOrWhiteSpace(value) ? null : value.Trim();
    }

    private static string NormalizePath(string? value)
    {
        return Normalize(value)?.Trim('/') ?? string.Empty;
    }

    private static string ToJson<T>(T value)
    {
        return JsonSerializer.Serialize(value, JsonOptions);
    }

    private sealed record RepositoryListItem(
        string Id,
        string Owner,
        string Repo,
        string? Description,
        string? PrimaryLanguage,
        DateTime? LastUpdateCheckAt,
        List<string> Branches,
        List<string> Languages);

    private sealed record RepositorySearchAccumulator(
        string Id,
        string Owner,
        string Repo,
        string? Description,
        string? PrimaryLanguage,
        DateTime? LastUpdateCheckAt)
    {
        public List<string> DocumentTitles { get; } = [];
        public List<string> DocumentPaths { get; } = [];
        public List<string> ContentSamples { get; } = [];
    }

    private sealed record RepositorySearchRow(
        string Id,
        string Owner,
        string Repo,
        string? Description,
        string? PrimaryLanguage,
        DateTime? LastUpdateCheckAt,
        List<string> DocumentTitles,
        List<string> DocumentPaths,
        List<string> ContentSamples);

    private interface ISearchResult
    {
        int Score { get; }
        int MatchedTokenCount { get; }
        bool FullQueryMatched { get; }
    }

    private sealed record RepositoryRouteResult(
        string Owner,
        string Repo,
        int Score,
        int MatchedTokenCount,
        bool FullQueryMatched,
        string Reason) : ISearchResult;

    private sealed record DocumentSearchRow(
        string RepositoryId,
        string Owner,
        string Repo,
        string Branch,
        string Language,
        bool IsDefaultLanguage,
        string Title,
        string Path,
        string Content,
        string? SourceFiles);

    private sealed record DocumentSearchResult(
        string Owner,
        string Repo,
        string Branch,
        string Language,
        string Title,
        string Path,
        int Score,
        int MatchedTokenCount,
        bool FullQueryMatched,
        string Reason,
        string Snippet) : ISearchResult;
}
