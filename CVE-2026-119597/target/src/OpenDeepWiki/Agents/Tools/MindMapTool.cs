using System.ComponentModel;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.AI;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Agents.Tools;

/// <summary>
/// AI Tool for writing project architecture mind map content.
/// Provides methods for AI agents to create mind map documentation.
/// </summary>
public class MindMapTool
{
    private readonly IContext _context;
    private readonly string _branchLanguageId;

    /// <summary>
    /// Initializes a new instance of MindMapTool.
    /// </summary>
    /// <param name="context">The database context.</param>
    /// <param name="branchLanguageId">The branch language ID to operate on.</param>
    public MindMapTool(IContext context, string branchLanguageId)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _branchLanguageId = branchLanguageId ?? throw new ArgumentNullException(nameof(branchLanguageId));
    }

    /// <summary>
    /// Writes the project architecture mind map content.
    /// The content should use markdown-like format with # for hierarchy.
    /// </summary>
    /// <param name="content">The mind map content in hierarchical format.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    [Description(@"Writes the project architecture mind map content.

Format:
- Use # for level 1 (main components)
- Use ## for level 2 (sub-modules)
- Use ### for level 3 (details)
- Append :file_path after title to link to source file

Example:
# 系统架构
## 前端应用:web
### 页面路由:web/app
## 后端服务:src/Api
### 控制器:src/Api/Controllers

Usage:
- Call this tool once with the complete mind map content
- Content will be stored and displayed in the repository sidebar")]
    public async Task<string> WriteMindMapAsync(
        [Description("Mind map content in hierarchical format using # ## ### for levels")]
        string content,
        CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(content))
        {
            return "ERROR: Mind map content cannot be empty. Please provide hierarchical content using # ## ### for levels.";
        }

        // Validate format - should contain at least one # header
        if (!content.Contains('#'))
        {
            return "ERROR: Mind map content must contain at least one # header. Use # for level 1, ## for level 2, ### for level 3.";
        }

        try
        {
            // Find the branch language
            var branchLanguage = await _context.BranchLanguages
                .FirstOrDefaultAsync(bl => bl.Id == _branchLanguageId && !bl.IsDeleted, cancellationToken);

            if (branchLanguage == null)
            {
                return $"ERROR: BranchLanguage with ID '{_branchLanguageId}' not found.";
            }

            // Update the mind map content
            branchLanguage.MindMapContent = content;
            branchLanguage.MindMapStatus = MindMapStatus.Completed;
            branchLanguage.UpdateTimestamp();

            await _context.SaveChangesAsync(cancellationToken);
            return "SUCCESS: Mind map has been written successfully.";
        }
        catch (Exception ex)
        {
            return $"ERROR: Failed to write mind map: {ex.Message}";
        }
    }

    /// <summary>
    /// Gets the available AI tools for mind map generation.
    /// </summary>
    /// <returns>Array of AI tools.</returns>
    public AITool[] GetTools()
    {
        return [AIFunctionFactory.Create(WriteMindMapAsync)];
    }
}
