namespace OpenDeepWiki.EFCore;

/// <summary>
/// Factory interface for creating IContext instances in parallel operations.
/// This is necessary because DbContext is not thread-safe.
/// </summary>
public interface IContextFactory
{
    /// <summary>
    /// Creates a new IContext instance.
    /// The caller is responsible for disposing the context.
    /// </summary>
    IContext CreateContext();
}
