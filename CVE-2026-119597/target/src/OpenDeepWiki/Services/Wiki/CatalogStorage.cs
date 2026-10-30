using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Provides storage operations for wiki catalog structures.
/// Interacts with the DocCatalog database entity.
/// </summary>
public class CatalogStorage
{
    private readonly IContext _context;
    private readonly string _branchLanguageId;
    
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        WriteIndented = true,
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    /// <summary>
    /// Initializes a new instance of CatalogStorage for a specific branch language.
    /// </summary>
    /// <param name="context">The database context.</param>
    /// <param name="branchLanguageId">The branch language ID to operate on.</param>
    public CatalogStorage(IContext context, string branchLanguageId)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _branchLanguageId = branchLanguageId ?? throw new ArgumentNullException(nameof(branchLanguageId));
    }

    /// <summary>
    /// Gets the current catalog structure as JSON.
    /// </summary>
    /// <returns>JSON string representing the catalog structure.</returns>
    public async Task<string> GetCatalogJsonAsync(CancellationToken cancellationToken = default)
    {
        var catalogs = await _context.DocCatalogs
            .Where(c => c.BranchLanguageId == _branchLanguageId && !c.IsDeleted)
            .OrderBy(c => c.Order)
            .ToListAsync(cancellationToken);

        var root = BuildCatalogTree(catalogs);
        return JsonSerializer.Serialize(root, JsonOptions);
    }

    /// <summary>
    /// Sets the complete catalog structure from JSON.
    /// Replaces all existing catalog items for the branch language.
    /// </summary>
    /// <param name="catalogJson">JSON string representing the catalog structure.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    public async Task SetCatalogAsync(string catalogJson, CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(catalogJson))
        {
            throw new ArgumentException("Catalog JSON cannot be empty.", nameof(catalogJson));
        }

        var root = JsonSerializer.Deserialize<CatalogRoot>(catalogJson, JsonOptions);
        if (root == null)
        {
            throw new ArgumentException("Invalid catalog JSON format.", nameof(catalogJson));
        }

        // Mark existing catalogs as deleted
        var existingCatalogs = await _context.DocCatalogs
            .Where(c => c.BranchLanguageId == _branchLanguageId && !c.IsDeleted)
            .ToListAsync(cancellationToken);

        // Refuse a destructive replacement: a populated catalog must not shrink drastically.
        // A partial-view agent run must use EditCatalog instead of rewriting everything.
        static int CountItems(List<CatalogItem> items) => items.Sum(i => 1 + CountItems(i.Children));
        var newCount = CountItems(root.Items);
        if (existingCatalogs.Count >= 10 && newCount < existingCatalogs.Count / 2)
        {
            throw new InvalidOperationException(
                $"Refusing to replace catalog: new structure has {newCount} items but {existingCatalogs.Count} exist. Use EditCatalog for partial updates.");
        }

        foreach (var catalog in existingCatalogs)
        {
            catalog.MarkAsDeleted();
        }

        // Create new catalog items
        await CreateCatalogItemsAsync(root.Items, null, cancellationToken);
        await _context.SaveChangesAsync(cancellationToken);
    }

    /// <summary>
    /// Updates a specific node in the catalog structure.
    /// </summary>
    /// <param name="path">The path of the node to update.</param>
    /// <param name="nodeJson">JSON string representing the updated node data.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    public async Task UpdateNodeAsync(string path, string nodeJson, CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(path))
        {
            throw new ArgumentException("Path cannot be empty.", nameof(path));
        }

        if (string.IsNullOrWhiteSpace(nodeJson))
        {
            throw new ArgumentException("Node JSON cannot be empty.", nameof(nodeJson));
        }

        var updatedItem = JsonSerializer.Deserialize<CatalogItem>(nodeJson, JsonOptions);
        if (updatedItem == null)
        {
            throw new ArgumentException("Invalid node JSON format.", nameof(nodeJson));
        }

        var existingCatalog = await _context.DocCatalogs
            .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId && 
                                      c.Path == path && 
                                      !c.IsDeleted, cancellationToken);

        if (existingCatalog == null)
        {
            throw new InvalidOperationException($"Catalog node with path '{path}' not found.");
        }

        // Update the existing catalog
        existingCatalog.Title = updatedItem.Title;
        existingCatalog.Order = updatedItem.Order;
        if (updatedItem.Children.Count > 0)
        {
            existingCatalog.DocFileId = null;
        }
        existingCatalog.UpdateTimestamp();

        // Handle children updates if provided
        if (updatedItem.Children.Count > 0)
        {
            // Mark existing children as deleted
            var existingChildren = await _context.DocCatalogs
                .Where(c => c.ParentId == existingCatalog.Id && !c.IsDeleted)
                .ToListAsync(cancellationToken);

            foreach (var child in existingChildren)
            {
                child.MarkAsDeleted();
            }

            // Create new children
            await CreateCatalogItemsAsync(updatedItem.Children, existingCatalog.Id, cancellationToken);
        }

        await _context.SaveChangesAsync(cancellationToken);
    }

    /// <summary>
    /// Gets a specific catalog node by path.
    /// </summary>
    /// <param name="path">The path of the node to retrieve.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The catalog item or null if not found.</returns>
    public async Task<CatalogItem?> GetNodeAsync(string path, CancellationToken cancellationToken = default)
    {
        var catalog = await _context.DocCatalogs
            .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId && 
                                      c.Path == path && 
                                      !c.IsDeleted, cancellationToken);

        if (catalog == null)
        {
            return null;
        }

        // Get all descendants
        var allCatalogs = await _context.DocCatalogs
            .Where(c => c.BranchLanguageId == _branchLanguageId && !c.IsDeleted)
            .OrderBy(c => c.Order)
            .ToListAsync(cancellationToken);

        return BuildCatalogItemWithChildren(catalog, allCatalogs);
    }

    /// <summary>
    /// Builds the catalog tree structure from flat list of DocCatalog entities.
    /// </summary>
    private CatalogRoot BuildCatalogTree(List<DocCatalog> catalogs)
    {
        var root = new CatalogRoot();
        var catalogDict = catalogs.ToDictionary(c => c.Id);

        // Find root items (no parent)
        var rootItems = catalogs.Where(c => c.ParentId == null).OrderBy(c => c.Order);

        foreach (var item in rootItems)
        {
            root.Items.Add(BuildCatalogItemWithChildren(item, catalogs));
        }

        return root;
    }

    /// <summary>
    /// Recursively builds a CatalogItem with its children.
    /// </summary>
    private CatalogItem BuildCatalogItemWithChildren(DocCatalog catalog, List<DocCatalog> allCatalogs)
    {
        var item = new CatalogItem
        {
            Title = catalog.Title,
            Path = catalog.Path,
            Order = catalog.Order,
            Children = new List<CatalogItem>()
        };

        var children = allCatalogs
            .Where(c => c.ParentId == catalog.Id)
            .OrderBy(c => c.Order);

        foreach (var child in children)
        {
            item.Children.Add(BuildCatalogItemWithChildren(child, allCatalogs));
        }

        return item;
    }

    /// <summary>
    /// Recursively creates or updates DocCatalog entities from CatalogItems.
    /// Handles existing records (including soft-deleted ones) to avoid unique constraint violations.
    /// </summary>
    private async Task CreateCatalogItemsAsync(List<CatalogItem> items, string? parentId, CancellationToken cancellationToken)
    {
        foreach (var item in items)
        {
            // Check if a record with the same path exists (including soft-deleted)
            var existingCatalog = await _context.DocCatalogs
                .FirstOrDefaultAsync(c => c.BranchLanguageId == _branchLanguageId &&
                                          c.Path == item.Path, cancellationToken);

            string catalogId;
            if (existingCatalog != null)
            {
                // Reuse existing record - update it instead of creating new
                existingCatalog.ParentId = parentId;
                existingCatalog.Title = item.Title;
                existingCatalog.Order = item.Order;
                existingCatalog.IsDeleted = false;
                if (item.Children.Count > 0)
                {
                    existingCatalog.DocFileId = null;
                }
                existingCatalog.UpdateTimestamp();
                catalogId = existingCatalog.Id;
            }
            else
            {
                // Create new record
                var catalog = new DocCatalog
                {
                    Id = Guid.NewGuid().ToString(),
                    BranchLanguageId = _branchLanguageId,
                    ParentId = parentId,
                    Title = item.Title,
                    Path = item.Path,
                    Order = item.Order
                };

                _context.DocCatalogs.Add(catalog);
                catalogId = catalog.Id;
            }

            if (item.Children.Count > 0)
            {
                await CreateCatalogItemsAsync(item.Children, catalogId, cancellationToken);
            }
        }
    }
}
