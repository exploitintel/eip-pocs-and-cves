using System.ComponentModel;
using System.Text.Json;
using Microsoft.Extensions.AI;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// AI Tool for reading, writing, and editing wiki catalog structures.
/// Provides methods for AI agents to manipulate the wiki directory structure.
/// </summary>
public class CatalogTool
{
    private readonly CatalogStorage _storage;
    
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        WriteIndented = true,
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    /// <summary>
    /// Initializes a new instance of CatalogTool with the specified storage.
    /// </summary>
    /// <param name="storage">The catalog storage instance to use for operations.</param>
    public CatalogTool(CatalogStorage storage)
    {
        _storage = storage ?? throw new ArgumentNullException(nameof(storage));
    }

    /// <summary>
    /// Reads the current wiki catalog structure.
    /// </summary>
    /// <returns>JSON string representing the current catalog structure.</returns>
    [Description(@"Reads the current wiki catalog structure.

Returns:
- JSON string containing the complete catalog tree
- Each item has: title, path, order, children (nested items)
- Use this to understand the current document structure before making changes")]
    public async Task<string> ReadAsync(CancellationToken cancellationToken = default)
    {
        return await _storage.GetCatalogJsonAsync(cancellationToken);
    }

    /// <summary>
    /// Writes a complete wiki catalog structure.
    /// Replaces all existing catalog items for the branch language.
    /// </summary>
    /// <param name="catalogJson">JSON string representing the complete catalog structure.</param>
    [Description(@"Writes a complete wiki catalog structure, replacing all existing items.

Usage:
- Provide a complete JSON catalog structure
- This will REPLACE all existing catalog items
- Use ReadAsync first to get current structure if you need to preserve some items

JSON Format:
{
  ""items"": [
    {
      ""title"": ""Getting Started"",
      ""path"": ""1-getting-started"",
      ""order"": 1,
      ""children"": []
    }
  ]
}")]
    public async Task<string> WriteAsync(
        [Description("Complete catalog JSON with 'items' array. Each item needs: title, path, order, children")]
        string catalogJson,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(catalogJson))
        {
            return "ERROR: Catalog JSON cannot be empty. Please provide a valid JSON structure with an 'items' array.";
        }

        // Validate JSON structure before writing
        try
        {
            var root = JsonSerializer.Deserialize<CatalogRoot>(catalogJson, JsonOptions);
            if (root == null)
            {
                return "ERROR: Invalid catalog JSON format - deserialization returned null. Please check your JSON syntax.";
            }

            ValidateCatalogStructure(root);
        }
        catch (JsonException ex)
        {
            return $"ERROR: Invalid catalog JSON format: {ex.Message}. Please ensure the JSON is properly formatted.";
        }
        catch (ArgumentException ex)
        {
            return $"ERROR: Catalog validation failed: {ex.Message}";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to validate catalog: {ex.Message}";
        }

        try
        {
            await _storage.SetCatalogAsync(catalogJson, cancellationToken);
            return "SUCCESS: Catalog has been written successfully.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to write catalog: {ex.Message}";
        }
    }

    /// <summary>
    /// Edits a specific node in the catalog structure.
    /// Merges changes with existing catalog structure while preserving unmodified nodes.
    /// </summary>
    /// <param name="path">The path of the node to edit, e.g., "1-overview".</param>
    /// <param name="nodeJson">JSON string representing the updated node data.</param>
    [Description(@"Edits a specific node in the catalog structure.

Usage:
- Provide the path of the node to edit and the new node data
- Only the specified node will be updated, other nodes are preserved
- Use this for targeted updates instead of rewriting the entire catalog

Example:
path: '1-overview'
nodeJson: {""title"": ""Updated Title"", ""path"": ""1-overview"", ""order"": 1, ""children"": []}")]
    public async Task<string> EditAsync(
        [Description("Path of the catalog node to edit, e.g., '1-overview' or '2-api/2.1-endpoints'")]
        string path,
        [Description("Updated node JSON with title, path, order, and children fields")]
        string nodeJson,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(path))
        {
            return "ERROR: Path cannot be empty. Please provide a valid catalog node path (e.g., '1-overview').";
        }

        if (string.IsNullOrWhiteSpace(nodeJson))
        {
            return "ERROR: Node JSON cannot be empty. Please provide valid JSON with title, path, order, and children fields.";
        }

        // Validate node JSON structure before editing
        try
        {
            var node = JsonSerializer.Deserialize<CatalogItem>(nodeJson, JsonOptions);
            if (node == null)
            {
                return "ERROR: Invalid node JSON format - deserialization returned null. Please check your JSON syntax.";
            }

            ValidateCatalogItem(node);
        }
        catch (JsonException ex)
        {
            return $"ERROR: Invalid node JSON format: {ex.Message}. Please ensure the JSON is properly formatted.";
        }
        catch (ArgumentException ex)
        {
            return $"ERROR: Node validation failed: {ex.Message}";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to validate node: {ex.Message}";
        }

        try
        {
            await _storage.UpdateNodeAsync(path, nodeJson, cancellationToken);
            return $"SUCCESS: Catalog node '{path}' has been updated successfully.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to update catalog node '{path}': {ex.Message}";
        }
    }

    /// <summary>
    /// Validates the catalog structure to ensure all items have required fields.
    /// </summary>
    private static void ValidateCatalogStructure(CatalogRoot root)
    {
        foreach (var item in root.Items)
        {
            ValidateCatalogItem(item);
        }
    }

    /// <summary>
    /// Validates a single catalog item and its children recursively.
    /// </summary>
    private static void ValidateCatalogItem(CatalogItem item)
    {
        if (string.IsNullOrWhiteSpace(item.Title))
        {
            throw new ArgumentException($"Catalog item must have a non-empty title. Path: {item.Path}");
        }

        if (string.IsNullOrWhiteSpace(item.Path))
        {
            throw new ArgumentException($"Catalog item must have a non-empty path. Title: {item.Title}");
        }

        if (item.Order < 0)
        {
            throw new ArgumentException($"Catalog item order must be >= 0. Path: {item.Path}, Order: {item.Order}");
        }

        foreach (var child in item.Children)
        {
            ValidateCatalogItem(child);
        }
    }

    /// <summary>
    /// Gets the list of AI tools provided by this CatalogTool.
    /// </summary>
    /// <returns>List of AITool instances for catalog operations.</returns>
    public List<AITool> GetTools()
    {
        return new List<AITool>
        {
            AIFunctionFactory.Create(ReadAsync, new AIFunctionFactoryOptions
            {
                Name = "ReadCatalog"
            }),
            AIFunctionFactory.Create(WriteAsync, new AIFunctionFactoryOptions
            {
                Name = "WriteCatalog"
            }),
            AIFunctionFactory.Create(EditAsync, new AIFunctionFactoryOptions
            {
                Name = "EditCatalog"
            })
        };
    }
}
