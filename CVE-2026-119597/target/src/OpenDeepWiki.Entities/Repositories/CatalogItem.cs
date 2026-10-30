using System.Text.Json.Serialization;

namespace OpenDeepWiki.Entities;

/// <summary>
/// Catalog item model for JSON serialization.
/// Represents a node in the wiki catalog tree structure.
/// </summary>
public class CatalogItem : IEquatable<CatalogItem>
{
    /// <summary>
    /// The display title of the catalog item.
    /// </summary>
    [JsonPropertyName("title")]
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// URL-friendly path identifier, e.g., "1-overview", "2-architecture".
    /// </summary>
    [JsonPropertyName("path")]
    public string Path { get; set; } = string.Empty;

    /// <summary>
    /// Sort order within the parent level.
    /// </summary>
    [JsonPropertyName("order")]
    public int Order { get; set; }

    /// <summary>
    /// Child catalog items (for tree structure).
    /// </summary>
    [JsonPropertyName("children")]
    public List<CatalogItem> Children { get; set; } = new();

    /// <summary>
    /// Determines whether the specified CatalogItem is equal to the current CatalogItem.
    /// </summary>
    public bool Equals(CatalogItem? other)
    {
        if (other is null) return false;
        if (ReferenceEquals(this, other)) return true;

        return Title == other.Title &&
               Path == other.Path &&
               Order == other.Order &&
               ChildrenEqual(Children, other.Children);
    }

    /// <summary>
    /// Determines whether the specified object is equal to the current CatalogItem.
    /// </summary>
    public override bool Equals(object? obj)
    {
        return Equals(obj as CatalogItem);
    }

    /// <summary>
    /// Returns a hash code for the current CatalogItem.
    /// </summary>
    public override int GetHashCode()
    {
        return HashCode.Combine(Title, Path, Order, Children.Count);
    }

    /// <summary>
    /// Compares two lists of CatalogItem for equality.
    /// </summary>
    private static bool ChildrenEqual(List<CatalogItem> a, List<CatalogItem> b)
    {
        if (a.Count != b.Count) return false;
        for (int i = 0; i < a.Count; i++)
        {
            if (!a[i].Equals(b[i])) return false;
        }
        return true;
    }
}

/// <summary>
/// Root container for the catalog structure.
/// </summary>
public class CatalogRoot : IEquatable<CatalogRoot>
{
    /// <summary>
    /// The list of top-level catalog items.
    /// </summary>
    [JsonPropertyName("items")]
    public List<CatalogItem> Items { get; set; } = new();

    /// <summary>
    /// Determines whether the specified CatalogRoot is equal to the current CatalogRoot.
    /// </summary>
    public bool Equals(CatalogRoot? other)
    {
        if (other is null) return false;
        if (ReferenceEquals(this, other)) return true;

        if (Items.Count != other.Items.Count) return false;
        for (int i = 0; i < Items.Count; i++)
        {
            if (!Items[i].Equals(other.Items[i])) return false;
        }
        return true;
    }

    /// <summary>
    /// Determines whether the specified object is equal to the current CatalogRoot.
    /// </summary>
    public override bool Equals(object? obj)
    {
        return Equals(obj as CatalogRoot);
    }

    /// <summary>
    /// Returns a hash code for the current CatalogRoot.
    /// </summary>
    public override int GetHashCode()
    {
        return HashCode.Combine(Items.Count);
    }
}
