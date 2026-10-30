using System.ComponentModel;
using System.Text;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.AI;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities.Tools;

namespace OpenDeepWiki.Services.Chat;

/// <summary>
/// Interface for converting Skill configurations to AI tools.
/// Follows the Anthropic Agent Skills open standard (agentskills.io).
/// </summary>
public interface ISkillToolConverter
{
    /// <summary>
    /// Converts enabled Skill configurations to AI tools.
    /// </summary>
    /// <param name="skillIds">List of Skill configuration IDs to convert.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>List of AI tools created from Skill configurations.</returns>
    Task<List<AITool>> ConvertSkillConfigsToToolsAsync(
        List<string> skillIds,
        CancellationToken cancellationToken = default);
}

/// <summary>
/// Converts Skill configurations to AI tools that can be used by the chat assistant.
/// Each Skill follows the agentskills.io standard with SKILL.md as the core instruction file.
/// Produces a single LoadSkills tool: description lists all available skills (name + description),
/// accepts a skill name parameter, and returns the SKILL.md prompts content for loading into context.
/// </summary>
public class SkillToolConverter : ISkillToolConverter
{
    private readonly IContextFactory _contextFactory;
    private readonly ILogger<SkillToolConverter> _logger;
    private readonly string _skillsBasePath;

    public SkillToolConverter(
        IContextFactory contextFactory,
        ILogger<SkillToolConverter> logger,
        IConfiguration configuration)
    {
        _contextFactory = contextFactory ?? throw new ArgumentNullException(nameof(contextFactory));
        _logger = logger;
        _skillsBasePath = configuration["Skills:BasePath"] ?? Path.Combine(AppContext.BaseDirectory, "skills");
    }

    /// <inheritdoc />
    public async Task<List<AITool>> ConvertSkillConfigsToToolsAsync(
        List<string> skillIds,
        CancellationToken cancellationToken = default)
    {
        var tools = new List<AITool>();

        if (skillIds == null || skillIds.Count == 0)
        {
            return tools;
        }

        // Use a fresh context because wiki generation builds tools from parallel document tasks.
        using var context = _contextFactory.CreateContext();

        var skillConfigs = await context.SkillConfigs
            .AsNoTracking()
            .Where(s => skillIds.Contains(s.Id) && s.IsActive && !s.IsDeleted)
            .OrderBy(s => s.SortOrder)
            .ThenBy(s => s.Name)
            .ToListAsync(cancellationToken);

        if (skillConfigs.Count == 0)
        {
            return tools;
        }

        // Create the single LoadSkills tool
        var loadSkillsTool = CreateLoadSkillsTool(skillConfigs);
        tools.Add(loadSkillsTool);
        _logger.LogInformation("Created LoadSkills tool with {Count} available skills", skillConfigs.Count);

        return tools;
    }

    /// <summary>
    /// Creates the LoadSkills AITool.
    /// The tool description contains a catalog of all available skills (name + description).
    /// When called with a skill name, reads and returns the SKILL.md prompts for that skill.
    /// </summary>
    private AITool CreateLoadSkillsTool(List<SkillConfig> skillConfigs)
    {
        // Build a lookup for quick access
        var skillLookup = skillConfigs.ToDictionary(s => s.Name, s => s, StringComparer.OrdinalIgnoreCase);

        // The actual function: accepts a skill name, returns SKILL.md prompts
        var loadSkillAsync = async (
            [Description("The name of the skill to load (select from available skills listed in this tool's description)")]
            string name,
            CancellationToken cancellationToken) =>
        {
            return await LoadSkillInternalAsync(name, skillLookup);
        };

        // Build description with skills catalog
        var description = new StringBuilder();
        description.AppendLine("""
Execute a skill within the main conversation
<skills_instructions>
When users ask you to perform tasks, check if any of the available skills below can help complete the task more effectively. Skills provide specialized capabilities and domain knowledge.
How to use skills:
- Invoke skills using this tool with the skill name only (no arguments)
- When you invoke a skill, you will see <command-message>The "{name}" skill is loading</command-message>
- The skill's prompt will expand and provide detailed instructions on how to complete the task
- Examples:
  - `skill: "pdf"` - invoke the pdf skill
  - `skill: "xlsx"` - invoke the xlsx skill
  - `skill: "ms-office-suite:pdf"` - invoke using fully qualified name

Important:
    - Only use skills listed in <available_skills> below
    - Do not invoke a skill that is already running
    - Do not use this tool for built-in CLI commands (like /help, /clear, etc.)
</skills_instructions>

<available_skills>
""");
        foreach (var skill in skillConfigs)
        {
            description.AppendLine($"- name: {skill.Name} - {skill.Description}");
        }
        
        description.Append("</available_skills>");

        return AIFunctionFactory.Create(
            loadSkillAsync,
            new AIFunctionFactoryOptions
            {
                Name = "Skill",
                Description = description.ToString()
            });
    }

    /// <summary>
    /// Internal implementation: reads SKILL.md for the specified skill and returns its prompts content.
    /// </summary>
    private async Task<string> LoadSkillInternalAsync(
        string name,
        Dictionary<string, SkillConfig> skillLookup)
    {
        if (string.IsNullOrWhiteSpace(name))
        {
            return JsonSerializer.Serialize(new { error = true, message = "Skill name cannot be empty." });
        }

        if (!skillLookup.TryGetValue(name, out var skill))
        {
            var availableNames = string.Join(", ", skillLookup.Keys);
            return JsonSerializer.Serialize(new
            {
                error = true,
                message = $"Skill '{name}' not found. Available skills: {availableNames}"
            });
        }

        var skillMdPath = Path.Combine(_skillsBasePath, skill.FolderPath, "SKILL.md");

        if (!File.Exists(skillMdPath))
        {
            return JsonSerializer.Serialize(new
            {
                error = true,
                message = $"SKILL.md not found for skill '{name}'."
            });
        }

        try
        {
            var content = await File.ReadAllTextAsync(skillMdPath);

            // Strip YAML frontmatter if present, return only the prompts body
            var prompts = ExtractPromptsBody(content);

            return prompts;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to load SKILL.md for skill: {Name}", name);
            return JsonSerializer.Serialize(new
            {
                error = true,
                message = $"Failed to load skill: {ex.Message}"
            });
        }
    }

    /// <summary>
    /// Extracts the prompts body from SKILL.md content by stripping YAML frontmatter.
    /// If no frontmatter is present, returns the entire content.
    /// </summary>
    private static string ExtractPromptsBody(string content)
    {
        if (!content.StartsWith("---"))
        {
            return content;
        }

        var endIndex = content.IndexOf("---", 3);
        if (endIndex < 0)
        {
            return content;
        }

        return content[(endIndex + 3)..].Trim();
    }
}
