using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.DependencyInjection;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using System.Text.RegularExpressions;

namespace OpenDeepWiki.Services.Repositories;

/// <summary>
/// 处理日志服务实现
/// </summary>
public class ProcessingLogService : IProcessingLogService
{
    private static readonly Regex DocumentsToGenerateRegex = new(
        @"\b(\d+)\s+documents?\s+to\s+generate\b",
        RegexOptions.IgnoreCase | RegexOptions.Compiled);

    private static readonly Regex DocumentProgressRegex = new(
        @"\bDocument\s+(?:complete|progress)\s*\(\s*(\d+)\s*/\s*(\d+)\s*\)",
        RegexOptions.IgnoreCase | RegexOptions.Compiled);

    private readonly IServiceScopeFactory _scopeFactory;

    public ProcessingLogService(IServiceScopeFactory scopeFactory)
    {
        _scopeFactory = scopeFactory;
    }

    /// <inheritdoc />
    public async Task LogAsync(
        string repositoryId,
        ProcessingStep step,
        string message,
        bool isAiOutput = false,
        string? toolName = null,
        CancellationToken cancellationToken = default)
    {
        await LogAsync(repositoryId, null, null, step, message, isAiOutput, toolName, cancellationToken);
    }

    /// <inheritdoc />
    public async Task LogAsync(
        string repositoryId,
        string? branchId,
        string? generationTaskId,
        ProcessingStep step,
        string message,
        bool isAiOutput = false,
        string? toolName = null,
        CancellationToken cancellationToken = default)
    {
        // 使用独立的 scope 来保存日志，避免影响其他操作
        if (isAiOutput || !string.IsNullOrWhiteSpace(toolName))
        {
            return;
        }

        var normalizedMessage = NormalizeProgressMessage(step, message);
        if (normalizedMessage is null)
        {
            return;
        }

        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();

        var log = new RepositoryProcessingLog
        {
            Id = Guid.NewGuid().ToString(),
            RepositoryId = repositoryId,
            BranchId = branchId,
            GenerationTaskId = generationTaskId,
            Step = step,
            Message = normalizedMessage,
            IsAiOutput = isAiOutput,
            ToolName = toolName,
            CreatedAt = DateTime.UtcNow
        };

        context.RepositoryProcessingLogs.Add(log);
        await context.SaveChangesAsync(cancellationToken);
    }

    /// <inheritdoc />
    public Task<ProcessingLogResponse> GetLogsAsync(
        string repositoryId,
        DateTime? since = null,
        int limit = 100,
        CancellationToken cancellationToken = default)
    {
        return GetLogsAsync(repositoryId, null, null, since, limit, cancellationToken);
    }

    /// <inheritdoc />
    public async Task<ProcessingLogResponse> GetLogsAsync(
        string repositoryId,
        string? branchId,
        string? generationTaskId,
        DateTime? since,
        int limit,
        CancellationToken cancellationToken = default)
    {
        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();

        var query = context.RepositoryProcessingLogs
            .Where(log => log.RepositoryId == repositoryId);

        if (!string.IsNullOrWhiteSpace(branchId))
        {
            query = query.Where(log => log.BranchId == branchId);
        }

        if (!string.IsNullOrWhiteSpace(generationTaskId))
        {
            query = query.Where(log => log.GenerationTaskId == generationTaskId);
        }

        if (since.HasValue)
        {
            query = query.Where(log => log.CreatedAt > since.Value);
        }

        var logs = await query
            .OrderByDescending(log => log.CreatedAt)
            .Take(limit)
            .Select(log => new ProcessingLogItem
            {
                Id = log.Id,
                BranchId = log.BranchId,
                GenerationTaskId = log.GenerationTaskId,
                Step = log.Step,
                Message = log.Message,
                IsAiOutput = log.IsAiOutput,
                ToolName = log.ToolName,
                CreatedAt = log.CreatedAt
            })
            .ToListAsync(cancellationToken);

        // 反转列表，使其按时间正序排列（最早的在前）
        logs.Reverse();

        // 获取当前步骤（最新日志的步骤）
        var currentStep = logs.LastOrDefault()?.Step ?? ProcessingStep.Workspace;

        // 解析文档生成进度
        var (totalDocuments, completedDocuments) = ParseDocumentProgress(logs);

        // 获取开始时间（第一条日志的时间）
        var startedAt = logs.FirstOrDefault()?.CreatedAt;

        return new ProcessingLogResponse
        {
            CurrentStep = currentStep,
            Logs = logs,
            TotalDocuments = totalDocuments,
            CompletedDocuments = completedDocuments,
            StartedAt = startedAt
        };
    }

    /// <summary>
    /// 从日志中解析文档生成进度
    /// </summary>
    private static (int total, int completed) ParseDocumentProgress(List<ProcessingLogItem> logs)
    {
        int total = 0;
        int completed = 0;

        foreach (var log in logs)
        {
            if (log.Step != ProcessingStep.Content || log.IsAiOutput || !string.IsNullOrEmpty(log.ToolName))
                continue;

            var documentProgressMatch = DocumentProgressRegex.Match(log.Message);
            if (documentProgressMatch.Success)
            {
                completed = Math.Max(completed, int.Parse(documentProgressMatch.Groups[1].Value));
                if (total == 0)
                {
                    total = int.Parse(documentProgressMatch.Groups[2].Value);
                }
                continue;
            }

            var documentsToGenerateMatch = DocumentsToGenerateRegex.Match(log.Message);
            if (documentsToGenerateMatch.Success)
            {
                total = int.Parse(documentsToGenerateMatch.Groups[1].Value);
                continue;
            }

            // 匹配 "发现 X 个文档需要生成" 格式
            var totalMatch = System.Text.RegularExpressions.Regex.Match(
                log.Message, @"发现\s*(\d+)\s*个文档");
            if (totalMatch.Success)
            {
                total = int.Parse(totalMatch.Groups[1].Value);
                continue;
            }

            // 匹配 "文档完成 (X/Y)" 格式（以完成为准）
            var completedMatch = System.Text.RegularExpressions.Regex.Match(
                log.Message, @"文档完成\s*\((\d+)/(\d+)\)");
            if (completedMatch.Success)
            {
                completed = Math.Max(completed, int.Parse(completedMatch.Groups[1].Value));
                if (total == 0)
                {
                    total = int.Parse(completedMatch.Groups[2].Value);
                }
                continue;
            }

            // 匹配 "开始生成文档 (X/Y)" 或旧格式 "正在生成文档 (X/Y)" - 仅用于补全总数
            var progressMatch = System.Text.RegularExpressions.Regex.Match(
                log.Message, @"(开始生成文档|正在生成文档)\s*\((\d+)/(\d+)\)");
            if (progressMatch.Success)
            {
                if (total == 0)
                {
                    total = int.Parse(progressMatch.Groups[3].Value);
                }
                continue;
            }

            // 匹配 "文档生成完成" 格式
            if (log.Message.Contains("文档生成完成"))
            {
                completed = total;
            }
        }

        return (total, completed);
    }

    private static string? NormalizeProgressMessage(ProcessingStep step, string message)
    {
        if (step == ProcessingStep.Workspace &&
            (message.StartsWith("Resolved scan plan:", StringComparison.Ordinal) ||
             message.StartsWith("Scan plan:", StringComparison.Ordinal) ||
             message.StartsWith("Repository directory tree collected. ScanPlanSource:", StringComparison.Ordinal)))
        {
            return message;
        }

        if (step != ProcessingStep.Content)
        {
            return $"Step progress ({step})";
        }

        var progressMatch = DocumentProgressRegex.Match(message);
        if (progressMatch.Success)
        {
            return $"Document progress ({progressMatch.Groups[1].Value}/{progressMatch.Groups[2].Value})";
        }

        var totalMatch = DocumentsToGenerateRegex.Match(message);
        if (totalMatch.Success)
        {
            return $"Document progress (0/{totalMatch.Groups[1].Value})";
        }

        return null;
    }

    /// <inheritdoc />
    public async Task ClearLogsAsync(string repositoryId, CancellationToken cancellationToken = default)
    {
        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();

        var logs = await context.RepositoryProcessingLogs
            .Where(log => log.RepositoryId == repositoryId)
            .ToListAsync(cancellationToken);

        context.RepositoryProcessingLogs.RemoveRange(logs);
        await context.SaveChangesAsync(cancellationToken);
    }
}
