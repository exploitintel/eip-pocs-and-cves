using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// API Key entity for headless/M2M authentication
/// </summary>
public class ApiKey : AggregateRoot<string>
{
    /// <summary>
    /// Human-readable label for the API key
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// First 8 chars of random part for lookup
    /// </summary>
    [Required]
    [StringLength(12)]
    public string KeyPrefix { get; set; } = string.Empty;

    /// <summary>
    /// SHA-256 hex of full token
    /// </summary>
    [Required]
    [StringLength(64)]
    public string KeyHash { get; set; } = string.Empty;

    /// <summary>
    /// Foreign key to User
    /// </summary>
    [Required]
    [StringLength(36)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// Permission scope
    /// </summary>
    [StringLength(50)]
    public string Scope { get; set; } = "mcp:read";

    /// <summary>
    /// Expiration date (null = never expires)
    /// </summary>
    public DateTime? ExpiresAt { get; set; }

    /// <summary>
    /// Last time this key was used
    /// </summary>
    public DateTime? LastUsedAt { get; set; }

    /// <summary>
    /// IP address of last usage
    /// </summary>
    [StringLength(50)]
    public string? LastUsedIp { get; set; }

    /// <summary>
    /// User navigation property
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }
}
