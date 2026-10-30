using System.ComponentModel;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.AI;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// AI Tool for reading document content in chat assistant.
/// Provides methods for AI agents to read wiki documents within the current repository context.
/// </summary>
public class DocReadTool
{
    private readonly IContext _context;
    private readonly string _owner;
    private readonly string _repo;
    private readonly string _branch;
    private readonly string _language;

    /// <summary>
    /// Initializes a new instance of DocReadTool with the specified context and repository information.
    /// </summary>
    /// <param name="context">The database context. Can be null for access validation only.</param>
    /// <param name="owner">The repository owner (organization name).</param>
    /// <param name="repo">The repository name.</param>
    /// <param name="branch">The branch name.</param>
    /// <param name="language">The language code.</param>
    public DocReadTool(IContext? context, string owner, string repo, string branch, string language)
    {
        _context = context!;
        _owner = owner ?? throw new ArgumentNullException(nameof(owner));
        _repo = repo ?? throw new ArgumentNullException(nameof(repo));
        _branch = branch ?? throw new ArgumentNullException(nameof(branch));
        _language = language ?? throw new ArgumentNullException(nameof(language));
    }

    /// <summary>
    /// Gets the repository owner.
    /// </summary>
    public string Owner => _owner;

    /// <summary>
    /// Gets the repository name.
    /// </summary>
    public string Repo => _repo;

    /// <summary>
    /// Gets the branch name.
    /// </summary>
    public string Branch => _branch;

    /// <summary>
    /// Gets the language code.
    /// </summary>
    public string Language => _language;

    /// <summary>
    /// Reads document content by path within the current repository context.
    /// Only documents within the same owner/repo/branch/language can be accessed.
    /// </summary>
    /// <param name="path">The document path, e.g., "getting-started/introduction".</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The document content or an error message in JSON format.</returns>
    [Description(@"Reads document content by path within the current repository.

Usage:
- Provide the document path from the catalog menu
- Only documents in the current repository can be accessed
- Returns the Markdown content of the document

Example:
path: 'getting-started/introduction'
path: 'api/endpoints'

Returns:
- Document content in Markdown format if found
- Error message if document not found or access denied")]
    public async Task<string> ReadDocumentAsync(
        [Description("Document path from the catalog menu, e.g., 'getting-started/introduction'")] 
        string path,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(path))
        {
            return JsonSerializer.Serialize(new { error = true, message = "文档路径不能为空" });
        }

        try
        {
            // Find the repository by owner and repo name
            var repository = await _context.Repositories
                .FirstOrDefaultAsync(r => r.OrgName == _owner && 
                                          r.RepoName == _repo && 
                                          !r.IsDeleted, cancellationToken);

            if (repository == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"仓库 '{_owner}/{_repo}' 不存在" });
            }

            // Find the branch
            var branch = await _context.RepositoryBranches
                .FirstOrDefaultAsync(b => b.RepositoryId == repository.Id && 
                                          b.BranchName == _branch && 
                                          !b.IsDeleted, cancellationToken);

            if (branch == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"分支 '{_branch}' 不存在" });
            }

            // Find the branch language
            var branchLanguage = await _context.BranchLanguages
                .FirstOrDefaultAsync(bl => bl.RepositoryBranchId == branch.Id && 
                                           bl.LanguageCode == _language && 
                                           !bl.IsDeleted, cancellationToken);

            if (branchLanguage == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"语言 '{_language}' 不存在" });
            }

            // Find the catalog item by path
            var catalog = await _context.DocCatalogs
                .FirstOrDefaultAsync(c => c.BranchLanguageId == branchLanguage.Id && 
                                          c.Path == path && 
                                          !c.IsDeleted, cancellationToken);

            if (catalog == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"文档 '{path}' 不存在" });
            }

            if (string.IsNullOrEmpty(catalog.DocFileId))
            {
                return JsonSerializer.Serialize(new { error = true, message = $"文档 '{path}' 没有内容" });
            }

            // Find the document file
            var docFile = await _context.DocFiles
                .FirstOrDefaultAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);

            if (docFile == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"文档 '{path}' 内容不存在" });
            }

            return docFile.Content;
        }
        catch (Exception ex)
        {
            return JsonSerializer.Serialize(new { error = true, message = $"读取文档失败: {ex.Message}" });
        }
    }


    /// <summary>
    /// Validates if a document path belongs to the current repository context.
    /// This is used internally to enforce access control.
    /// </summary>
    /// <param name="requestedOwner">The requested owner.</param>
    /// <param name="requestedRepo">The requested repository.</param>
    /// <param name="requestedBranch">The requested branch.</param>
    /// <returns>True if the request is within the allowed context, false otherwise.</returns>
    public bool ValidateAccess(string requestedOwner, string requestedRepo, string requestedBranch)
    {
        return string.Equals(_owner, requestedOwner, StringComparison.OrdinalIgnoreCase) &&
               string.Equals(_repo, requestedRepo, StringComparison.OrdinalIgnoreCase) &&
               string.Equals(_branch, requestedBranch, StringComparison.OrdinalIgnoreCase);
    }

    /// <summary>
    /// Lists all available documents in the current repository context.
    /// </summary>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>A list of available document paths.</returns>
    [Description(@"Lists all available documents in the current repository.

Usage:
- Call this to see what documents are available
- Returns a list of document paths that can be read

Returns:
- List of document paths in the catalog")]
    public async Task<string> ListDocumentsAsync(
        CancellationToken cancellationToken = default)
    {
        try
        {
            // Find the repository by owner and repo name
            var repository = await _context.Repositories
                .FirstOrDefaultAsync(r => r.OrgName == _owner && 
                                          r.RepoName == _repo && 
                                          !r.IsDeleted, cancellationToken);

            if (repository == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"仓库 '{_owner}/{_repo}' 不存在" });
            }

            // Find the branch
            var branch = await _context.RepositoryBranches
                .FirstOrDefaultAsync(b => b.RepositoryId == repository.Id && 
                                          b.BranchName == _branch && 
                                          !b.IsDeleted, cancellationToken);

            if (branch == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"分支 '{_branch}' 不存在" });
            }

            // Find the branch language
            var branchLanguage = await _context.BranchLanguages
                .FirstOrDefaultAsync(bl => bl.RepositoryBranchId == branch.Id && 
                                           bl.LanguageCode == _language && 
                                           !bl.IsDeleted, cancellationToken);

            if (branchLanguage == null)
            {
                return JsonSerializer.Serialize(new { error = true, message = $"语言 '{_language}' 不存在" });
            }

            // Get all catalog items with documents
            var catalogs = await _context.DocCatalogs
                .Where(c => c.BranchLanguageId == branchLanguage.Id && 
                            !c.IsDeleted && 
                            !string.IsNullOrEmpty(c.DocFileId))
                .OrderBy(c => c.Order)
                .Select(c => new { c.Title, c.Path })
                .ToListAsync(cancellationToken);

            return JsonSerializer.Serialize(new { documents = catalogs });
        }
        catch (Exception ex)
        {
            return JsonSerializer.Serialize(new { error = true, message = $"获取文档列表失败: {ex.Message}" });
        }
    }

    /// <summary>
    /// Gets the list of AI tools provided by this DocReadTool.
    /// </summary>
    /// <returns>List of AITool instances for document reading operations.</returns>
    public List<AITool> GetTools()
    {
        return new List<AITool>
        {
            AIFunctionFactory.Create(ReadDocumentAsync, new AIFunctionFactoryOptions
            {
                Name = "ReadDocument"
            }),
            AIFunctionFactory.Create(ListDocumentsAsync, new AIFunctionFactoryOptions
            {
                Name = "ListDocuments"
            })
        };
    }
}
