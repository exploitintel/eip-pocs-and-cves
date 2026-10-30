using System.Text.RegularExpressions;

namespace OpenDeepWiki.Services.Prompts;

/// <summary>
/// File-based implementation of IPromptPlugin.
/// Loads prompt templates from markdown files and supports {{variable}} substitution.
/// </summary>
public partial class FilePromptPlugin : IPromptPlugin
{
    private readonly string _promptsDirectory;

    /// <summary>
    /// Regex pattern to match {{variable}} placeholders.
    /// </summary>
    [GeneratedRegex(@"\{\{(\w+)\}\}", RegexOptions.Compiled)]
    private static partial Regex VariablePlaceholderRegex();

    /// <summary>
    /// Initializes a new instance of FilePromptPlugin.
    /// </summary>
    /// <param name="promptsDirectory">The directory containing prompt template files.</param>
    /// <exception cref="ArgumentException">Thrown when promptsDirectory is null or empty.</exception>
    public FilePromptPlugin(string promptsDirectory)
    {
        if (string.IsNullOrWhiteSpace(promptsDirectory))
        {
            throw new ArgumentException("Prompts directory cannot be null or empty.", nameof(promptsDirectory));
        }

        _promptsDirectory = promptsDirectory;
    }

    /// <inheritdoc />
    public async Task<string> LoadPromptAsync(
        string promptName,
        Dictionary<string, string>? variables = null,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(promptName))
        {
            throw new ArgumentException("Prompt name cannot be null or empty.", nameof(promptName));
        }

        var filePath = Path.Combine(_promptsDirectory, $"{promptName}.md");

        if (!File.Exists(filePath))
        {
            throw new FileNotFoundException(
                $"Prompt file '{promptName}.md' not found in directory '{_promptsDirectory}'.",
                filePath);
        }

        var template = await File.ReadAllTextAsync(filePath, cancellationToken);

        if (variables == null || variables.Count == 0)
        {
            return template;
        }

        return SubstituteVariables(template, variables);
    }

    /// <summary>
    /// Substitutes {{variable}} placeholders in the template with provided values.
    /// </summary>
    /// <param name="template">The template string containing placeholders.</param>
    /// <param name="variables">Dictionary of variable names and their values.</param>
    /// <returns>The template with all matching placeholders replaced.</returns>
    internal static string SubstituteVariables(string template, Dictionary<string, string> variables)
    {
        if (string.IsNullOrEmpty(template) || variables.Count == 0)
        {
            return template;
        }

        return VariablePlaceholderRegex().Replace(template, match =>
        {
            var variableName = match.Groups[1].Value;
            return variables.TryGetValue(variableName, out var value) ? value : match.Value;
        });
    }
}
