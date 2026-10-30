namespace OpenDeepWiki.Services.Prompts;

/// <summary>
/// Interface for loading and processing prompt templates.
/// Supports loading prompts by name and variable substitution.
/// </summary>
public interface IPromptPlugin
{
    /// <summary>
    /// Loads a prompt template by name and optionally substitutes variables.
    /// </summary>
    /// <param name="promptName">The name of the prompt to load (without file extension).</param>
    /// <param name="variables">Optional dictionary of variables to substitute in the template.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The processed prompt content with variables substituted.</returns>
    /// <exception cref="FileNotFoundException">Thrown when the prompt file is not found.</exception>
    Task<string> LoadPromptAsync(
        string promptName, 
        Dictionary<string, string>? variables = null,
        CancellationToken cancellationToken = default);
}
