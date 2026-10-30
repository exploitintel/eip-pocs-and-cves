using System.ComponentModel;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.AI;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// AI Tool for writing and editing document content.
/// Provides methods for AI agents to create and modify wiki documents.
/// </summary>
public class DocTool
{
    private readonly IContext _context;
    private readonly string _branchLanguageId;
    private readonly string? _catalogPath;
    private readonly GitTool? _gitTool;
    private readonly int? _maxAppendOperations;
    private int _appendOperations;
    private const int MaxDatabaseWriteAttempts = 3;
    private const int DatabaseWriteRetryBaseDelayMs = 250;

    /// <summary>
    /// Initializes a new instance of DocTool with the specified context, branch language, and catalog path.
    /// </summary>
    /// <param name="context">The database context.</param>
    /// <param name="branchLanguageId">The branch language ID to operate on.</param>
    /// <param name="catalogPath">The catalog item path this tool operates on.</param>
    /// <param name="gitTool">Optional GitTool instance to track read files.</param>
    /// <param name="maxAppendOperations">Optional maximum AppendDoc calls for this document.</param>
    public DocTool(
        IContext context,
        string branchLanguageId,
        string? catalogPath,
        GitTool? gitTool = null,
        int? maxAppendOperations = null)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _branchLanguageId = branchLanguageId ?? throw new ArgumentNullException(nameof(branchLanguageId));
        _catalogPath = NormalizeCatalogPath(catalogPath);
        _gitTool = gitTool;
        _maxAppendOperations = maxAppendOperations is > 0 ? maxAppendOperations : null;
    }

    /// <summary>
    /// Writes document content for the catalog path specified in constructor.
    /// Creates a new document and associates it with the catalog item.
    /// Automatically records source files from GitTool if available.
    /// </summary>
    /// <param name="content">The Markdown content for the document.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    [Description(@"Writes document content for the current catalog item.

Usage:
- Creates a new document or updates existing one
- Content should be in Markdown format
- The catalog item must exist before writing content
- Source files are automatically tracked from files you read

Example:
content: '# Overview\n\nThis is the overview section...'")]
    public async Task<string> WriteAsync(
        [Description("Document content in Markdown format")]
        string content,
        [Description("Optional catalog path. Omit this when writing the current catalog item; provide it for incremental updates.")]
        string path = "",
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(content))
        {
            return "ERROR: Content cannot be empty. Please provide valid Markdown content for the document.";
        }

        content = MermaidMarkdownNormalizer.Normalize(content);

        var catalogPath = ResolveCatalogPath(path);
        if (catalogPath == null)
        {
            return "ERROR: Catalog path is required. Provide the path parameter for incremental updates.";
        }

        try
        {
            // Find the catalog item
            var catalog = await _context.DocCatalogs
                .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId &&
                                          c.Path == catalogPath &&
                                          !c.IsDeleted, cancellationToken);

            if (catalog == null)
            {
                return $"ERROR: Catalog item with path '{catalogPath}' not found. Please ensure the catalog item exists before writing content.";
            }

            if (await HasChildCatalogsAsync(catalog.Id, cancellationToken))
            {
                catalog.DocFileId = null;
                catalog.UpdateTimestamp();
                await SaveChangesWithRetryAsync(cancellationToken);
                return $"ERROR: Catalog item '{catalogPath}' has child catalog items and is a navigation node. Documents can only be written to leaf catalog items.";
            }

            // 从 GitTool 获取读取的文件列表
            string? sourceFilesJson = null;
            if (_gitTool != null)
            {
                var readFiles = _gitTool.GetReadFiles();
                if (readFiles.Count > 0)
                {
                    sourceFilesJson = JsonSerializer.Serialize(readFiles);
                }
            }

            // Check if document already exists
            if (!string.IsNullOrEmpty(catalog.DocFileId))
            {
                var existingDoc = await _context.DocFiles
                    .FirstOrDefaultAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);

                if (existingDoc != null)
                {
                    // Update existing document
                    existingDoc.Content = content;
                    existingDoc.SourceFiles = sourceFilesJson;
                    existingDoc.UpdateTimestamp();
                    await SaveChangesWithRetryAsync(cancellationToken);
                    return $"SUCCESS: Document '{catalogPath}' has been updated successfully.";
                }
            }

            // Create new document
            var docFile = new DocFile
            {
                Id = Guid.NewGuid().ToString(),
                BranchLanguageId = _branchLanguageId,
                Content = content,
                SourceFiles = sourceFilesJson
            };

            _context.DocFiles.Add(docFile);

            // Associate with catalog item
            catalog.DocFileId = docFile.Id;
            catalog.UpdateTimestamp();

            await SaveChangesWithRetryAsync(cancellationToken);
            return $"SUCCESS: Document '{catalogPath}' has been created successfully.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to write document '{catalogPath}': {ex.Message}";
        }
    }

    /// <summary>
    /// Appends content to the end of an existing document, creating it if it does not exist.
    /// Enables building long documents incrementally across multiple tool calls,
    /// so the final document can far exceed a single response's token limit.
    /// </summary>
    /// <param name="content">The Markdown content to append.</param>
    /// <param name="path">Optional catalog path. Omit for the current catalog item.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    [Description(@"Appends Markdown content to the END of the current catalog item's document.

Usage:
- Use this to build a long, comprehensive document in multiple steps without hitting a single-response size limit
- First call WriteDoc with the title + opening sections, then call AppendDoc repeatedly to add more sections
- If no document exists yet, AppendDoc creates one with the given content
- Content is appended exactly as provided; include a leading blank line / heading so sections stay separated
- Source files are automatically tracked from files you read

Example:
content: '\n## Failure Modes\n\nThe service handles ... '")]
    public async Task<string> AppendAsync(
        [Description("Markdown content to append to the end of the document")]
        string content,
        [Description("Optional catalog path. Omit this when appending to the current catalog item; provide it for incremental updates.")]
        string path = "",
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(content))
        {
            return "ERROR: Content cannot be empty. Please provide valid Markdown content to append.";
        }

        content = MermaidMarkdownNormalizer.Normalize(content);

        var catalogPath = ResolveCatalogPath(path);
        if (catalogPath == null)
        {
            return "ERROR: Catalog path is required. Provide the path parameter for incremental updates.";
        }

        try
        {
            if (_maxAppendOperations.HasValue && _appendOperations >= _maxAppendOperations.Value)
            {
                return $"SUCCESS: Append budget reached ({_appendOperations}/{_maxAppendOperations.Value}). The document already has persisted content; stop appending and provide the final summary.";
            }

            // Find the catalog item
            var catalog = await _context.DocCatalogs
                .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId &&
                                          c.Path == catalogPath &&
                                          !c.IsDeleted, cancellationToken);

            if (catalog == null)
            {
                return $"ERROR: Catalog item with path '{catalogPath}' not found. Please ensure the catalog item exists before writing content.";
            }

            if (await HasChildCatalogsAsync(catalog.Id, cancellationToken))
            {
                catalog.DocFileId = null;
                catalog.UpdateTimestamp();
                await SaveChangesWithRetryAsync(cancellationToken);
                return $"ERROR: Catalog item '{catalogPath}' has child catalog items and is a navigation node. Documents can only be appended to leaf catalog items.";
            }

            // 从 GitTool 获取读取的文件列表
            string? sourceFilesJson = null;
            if (_gitTool != null)
            {
                var readFiles = _gitTool.GetReadFiles();
                if (readFiles.Count > 0)
                {
                    sourceFilesJson = JsonSerializer.Serialize(readFiles);
                }
            }

            // Append to existing document if present
            if (!string.IsNullOrEmpty(catalog.DocFileId))
            {
                var existingDoc = await _context.DocFiles
                    .FirstOrDefaultAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);

                if (existingDoc != null)
                {
                    existingDoc.Content = string.Concat(existingDoc.Content, content);
                    if (sourceFilesJson != null)
                    {
                        existingDoc.SourceFiles = sourceFilesJson;
                    }
                    existingDoc.UpdateTimestamp();
                    await SaveChangesWithRetryAsync(cancellationToken);
                    _appendOperations++;
                    return $"SUCCESS: Appended content to document '{catalogPath}'. Current length: {existingDoc.Content.Length} characters.";
                }
            }

            // No document yet — create one with the provided content
            var docFile = new DocFile
            {
                Id = Guid.NewGuid().ToString(),
                BranchLanguageId = _branchLanguageId,
                Content = content,
                SourceFiles = sourceFilesJson
            };

            _context.DocFiles.Add(docFile);
            catalog.DocFileId = docFile.Id;
            catalog.UpdateTimestamp();

            await SaveChangesWithRetryAsync(cancellationToken);
            _appendOperations++;
            return $"SUCCESS: Created document '{catalogPath}' and wrote initial content. Current length: {content.Length} characters.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to append to document '{catalogPath}': {ex.Message}";
        }
    }

    /// <summary>
    /// Edits existing document content by replacing specified text.
    /// </summary>
    /// <param name="oldContent">The content to be replaced.</param>
    /// <param name="newContent">The new content to replace with.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    [Description(@"Edits existing document content by replacing specified text.

Usage:
- Performs exact string replacement in the document
- oldContent must exist in the document (exact match required)
- Use ReadAsync first to see current content
- For large changes, consider using WriteAsync instead

Example:
oldContent: '## Old Section'
newContent: '## New Section\n\nUpdated content here'")]
    public async Task<string> EditAsync(
        [Description("Exact text to find and replace (must exist in document)")]
        string oldContent,
        [Description("New text to replace the old content with")]
        string newContent,
        [Description("Optional catalog path. Omit this when editing the current catalog item; provide it for incremental updates.")]
        string path = "",
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(oldContent))
        {
            return "ERROR: Old content cannot be empty. Please provide the exact text you want to replace.";
        }

        var catalogPath = ResolveCatalogPath(path);
        if (catalogPath == null)
        {
            return "ERROR: Catalog path is required. Provide the path parameter for incremental updates.";
        }

        try
        {
            // Find the catalog item
            var catalog = await _context.DocCatalogs
                .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId &&
                                          c.Path == catalogPath &&
                                          !c.IsDeleted, cancellationToken);

            if (catalog == null)
            {
                return $"ERROR: Catalog item with path '{catalogPath}' not found.";
            }

            if (await HasChildCatalogsAsync(catalog.Id, cancellationToken))
            {
                return $"ERROR: Catalog item '{catalogPath}' has child catalog items and is a navigation node. Documents can only be edited on leaf catalog items.";
            }

            if (string.IsNullOrEmpty(catalog.DocFileId))
            {
                return $"ERROR: No document associated with catalog item '{catalogPath}'. Use WriteAsync to create a document first.";
            }

            // Find the document
            var docFile = await _context.DocFiles
                .FirstOrDefaultAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);

            if (docFile == null)
            {
                return $"ERROR: Document not found for catalog item '{catalogPath}'.";
            }

            // Check if old content exists in the document
            if (!docFile.Content.Contains(oldContent))
            {
                return $"ERROR: The specified content to replace was not found in the document. Please use ReadAsync to see the current content and ensure the text matches exactly.";
            }

            // Replace content, then normalize the full document so Mermaid
            // repairs still apply after a partial edit.
            docFile.Content = MermaidMarkdownNormalizer.Normalize(
                docFile.Content.Replace(oldContent, newContent));
            docFile.UpdateTimestamp();

            await SaveChangesWithRetryAsync(cancellationToken);
            return $"SUCCESS: Document '{catalogPath}' has been edited successfully.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to edit document '{catalogPath}': {ex.Message}";
        }
    }

    /// <summary>
    /// Reads the document content for the catalog path specified in constructor.
    /// </summary>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The document content or null if not found.</returns>
    [Description(@"Reads the document content for the current catalog item.

Returns:
- The Markdown content of the document
- null if no document exists for the path

Usage:
- Use before EditAsync to see current content
- Use to verify document was written correctly")]
    public async Task<string?> ReadAsync(
        [Description("Optional catalog path. Omit this when reading the current catalog item; provide it for incremental updates.")]
        string path = "",
        CancellationToken cancellationToken = default)
    {
        var catalogPath = ResolveCatalogPath(path);
        if (catalogPath == null)
        {
            return "ERROR: Catalog path is required. Provide the path parameter for incremental updates.";
        }

        // Find the catalog item
        var catalog = await _context.DocCatalogs
            .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId && 
                                      c.Path == catalogPath && 
                                      !c.IsDeleted, cancellationToken);

        if (catalog == null || string.IsNullOrEmpty(catalog.DocFileId) ||
            await HasChildCatalogsAsync(catalog.Id, cancellationToken))
        {
            return null;
        }

        // Find the document
        var docFile = await _context.DocFiles
            .FirstOrDefaultAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);

        return docFile?.Content;
    }

    /// <summary>
    /// Checks if a document exists for the catalog path specified in constructor.
    /// </summary>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>True if a document exists, false otherwise.</returns>
    [Description(@"Checks if a document exists for the current catalog item.

Returns:
- true if document exists and is not deleted
- false if no document or catalog item not found

Usage:
- Quick check before writing to avoid overwriting
- Verify document creation was successful")]
    public async Task<bool> ExistsAsync(
        [Description("Optional catalog path. Omit this when checking the current catalog item; provide it for incremental updates.")]
        string path = "",
        CancellationToken cancellationToken = default)
    {
        var catalogPath = ResolveCatalogPath(path);
        if (catalogPath == null)
        {
            return false;
        }

        var catalog = await _context.DocCatalogs
            .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId && 
                                      c.Path == catalogPath && 
                                      !c.IsDeleted, cancellationToken);

        if (catalog == null || string.IsNullOrEmpty(catalog.DocFileId) ||
            await HasChildCatalogsAsync(catalog.Id, cancellationToken))
        {
            return false;
        }

        return await _context.DocFiles
            .AnyAsync(d => d.Id == catalog.DocFileId && !d.IsDeleted, cancellationToken);
    }

    /// <summary>
    /// Gets the list of AI tools provided by this DocTool.
    /// </summary>
    /// <returns>List of AITool instances for document operations.</returns>
    public List<AITool> GetTools()
    {
        return new List<AITool>
        {
            AIFunctionFactory.Create(WriteAsync, new AIFunctionFactoryOptions
            {
                Name = "WriteDoc"
            }),
            AIFunctionFactory.Create(AppendAsync, new AIFunctionFactoryOptions
            {
                Name = "AppendDoc"
            }),
            AIFunctionFactory.Create(EditAsync, new AIFunctionFactoryOptions
            {
                Name = "EditDoc"
            }),
            AIFunctionFactory.Create(ReadAsync, new AIFunctionFactoryOptions
            {
                Name = "ReadDoc"
            }),
            AIFunctionFactory.Create(ExistsAsync, new AIFunctionFactoryOptions
            {
                Name = "DocExists"
            })
        };
    }

    private string? ResolveCatalogPath(string? path)
    {
        return NormalizeCatalogPath(path) ?? _catalogPath;
    }

    private async Task<bool> HasChildCatalogsAsync(string catalogId, CancellationToken cancellationToken)
    {
        return await _context.DocCatalogs
            .AnyAsync(c => c.BranchLanguageId == _branchLanguageId &&
                           c.ParentId == catalogId &&
                           !c.IsDeleted, cancellationToken);
    }

    private static string? NormalizeCatalogPath(string? path)
    {
        if (string.IsNullOrWhiteSpace(path))
        {
            return null;
        }

        return path.Trim().Trim('/');
    }

    private async Task SaveChangesWithRetryAsync(CancellationToken cancellationToken)
    {
        for (var attempt = 1;; attempt++)
        {
            try
            {
                await _context.SaveChangesAsync(cancellationToken);
                return;
            }
            catch (Exception ex) when (attempt < MaxDatabaseWriteAttempts && IsRetryableDatabaseWriteException(ex))
            {
                var delay = DatabaseWriteRetryBaseDelayMs * attempt + Random.Shared.Next(0, 150);
                await Task.Delay(delay, cancellationToken);
            }
        }
    }

    private static bool IsRetryableDatabaseWriteException(Exception ex)
    {
        var message = ex.ToString().ToLowerInvariant();
        return message.Contains("database is locked") ||
               message.Contains("database table is locked") ||
               message.Contains("sqlite_busy") ||
               message.Contains("database is busy");
    }
}
