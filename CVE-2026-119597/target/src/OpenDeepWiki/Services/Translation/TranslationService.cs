using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Translation;

/// <summary>
/// 翻译服务实现
/// </summary>
public class TranslationService : ITranslationService
{
    private readonly IContext _context;
    private readonly ILogger<TranslationService> _logger;

    public TranslationService(IContext context, ILogger<TranslationService> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task<TranslationTask?> CreateTaskAsync(
        string repositoryId,
        string repositoryBranchId,
        string sourceBranchLanguageId,
        string targetLanguageCode,
        CancellationToken cancellationToken = default)
    {
        // 检查是否已存在相同的任务（待处理或处理中）
        var existingTask = await _context.TranslationTasks
            .FirstOrDefaultAsync(t => 
                t.RepositoryBranchId == repositoryBranchId &&
                t.TargetLanguageCode == targetLanguageCode &&
                !t.IsDeleted &&
                (t.Status == TranslationTaskStatus.Pending || t.Status == TranslationTaskStatus.Processing),
                cancellationToken);

        if (existingTask != null)
        {
            _logger.LogDebug(
                "Translation task already exists. BranchId: {BranchId}, TargetLang: {TargetLang}, Status: {Status}",
                repositoryBranchId, targetLanguageCode, existingTask.Status);
            return null;
        }

        // 检查目标语言是否已存在
        var existingLanguage = await _context.BranchLanguages
            .FirstOrDefaultAsync(l => 
                l.RepositoryBranchId == repositoryBranchId &&
                l.LanguageCode == targetLanguageCode &&
                !l.IsDeleted,
                cancellationToken);

        if (existingLanguage != null)
        {
            _logger.LogDebug(
                "Target language already exists. BranchId: {BranchId}, TargetLang: {TargetLang}",
                repositoryBranchId, targetLanguageCode);
            return null;
        }

        var task = new TranslationTask
        {
            Id = Guid.NewGuid().ToString(),
            RepositoryId = repositoryId,
            RepositoryBranchId = repositoryBranchId,
            SourceBranchLanguageId = sourceBranchLanguageId,
            TargetLanguageCode = targetLanguageCode.ToLowerInvariant(),
            Status = TranslationTaskStatus.Pending
        };

        _context.TranslationTasks.Add(task);
        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation(
            "Translation task created. TaskId: {TaskId}, BranchId: {BranchId}, SourceLang: {SourceLangId}, TargetLang: {TargetLang}",
            task.Id, repositoryBranchId, sourceBranchLanguageId, targetLanguageCode);

        return task;
    }

    /// <inheritdoc />
    public async Task<List<TranslationTask>> CreateTasksAsync(
        string repositoryId,
        string repositoryBranchId,
        string sourceBranchLanguageId,
        IEnumerable<string> targetLanguageCodes,
        CancellationToken cancellationToken = default)
    {
        var createdTasks = new List<TranslationTask>();

        foreach (var targetLang in targetLanguageCodes)
        {
            var task = await CreateTaskAsync(
                repositoryId,
                repositoryBranchId,
                sourceBranchLanguageId,
                targetLang,
                cancellationToken);

            if (task != null)
            {
                createdTasks.Add(task);
            }
        }

        return createdTasks;
    }

    /// <inheritdoc />
    public async Task<TranslationTask?> GetNextPendingTaskAsync(CancellationToken cancellationToken = default)
    {
        return await _context.TranslationTasks
            .Where(t => t.Status == TranslationTaskStatus.Pending && !t.IsDeleted)
            .OrderBy(t => t.CreatedAt)
            .FirstOrDefaultAsync(cancellationToken);
    }

    /// <inheritdoc />
    public async Task MarkAsProcessingAsync(string taskId, CancellationToken cancellationToken = default)
    {
        var task = await _context.TranslationTasks
            .FirstOrDefaultAsync(t => t.Id == taskId && !t.IsDeleted, cancellationToken);

        if (task == null)
        {
            _logger.LogWarning("Translation task not found. TaskId: {TaskId}", taskId);
            return;
        }

        task.Status = TranslationTaskStatus.Processing;
        task.StartedAt = DateTime.UtcNow;
        task.UpdateTimestamp();

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogDebug("Translation task marked as processing. TaskId: {TaskId}", taskId);
    }

    /// <inheritdoc />
    public async Task MarkAsCompletedAsync(string taskId, CancellationToken cancellationToken = default)
    {
        var task = await _context.TranslationTasks
            .FirstOrDefaultAsync(t => t.Id == taskId && !t.IsDeleted, cancellationToken);

        if (task == null)
        {
            _logger.LogWarning("Translation task not found. TaskId: {TaskId}", taskId);
            return;
        }

        task.Status = TranslationTaskStatus.Completed;
        task.CompletedAt = DateTime.UtcNow;
        task.UpdateTimestamp();

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogInformation("Translation task completed. TaskId: {TaskId}", taskId);
    }

    /// <inheritdoc />
    public async Task MarkAsFailedAsync(string taskId, string errorMessage, CancellationToken cancellationToken = default)
    {
        var task = await _context.TranslationTasks
            .FirstOrDefaultAsync(t => t.Id == taskId && !t.IsDeleted, cancellationToken);

        if (task == null)
        {
            _logger.LogWarning("Translation task not found. TaskId: {TaskId}", taskId);
            return;
        }

        task.RetryCount++;
        task.ErrorMessage = errorMessage.Length > 2000 ? errorMessage[..2000] : errorMessage;

        if (task.RetryCount >= task.MaxRetryCount)
        {
            task.Status = TranslationTaskStatus.Failed;
            _logger.LogError(
                "Translation task failed after max retries. TaskId: {TaskId}, RetryCount: {RetryCount}, Error: {Error}",
                taskId, task.RetryCount, errorMessage);
        }
        else
        {
            task.Status = TranslationTaskStatus.Pending; // 重置为待处理，等待重试
            _logger.LogWarning(
                "Translation task failed, will retry. TaskId: {TaskId}, RetryCount: {RetryCount}/{MaxRetry}, Error: {Error}",
                taskId, task.RetryCount, task.MaxRetryCount, errorMessage);
        }

        task.UpdateTimestamp();
        await _context.SaveChangesAsync(cancellationToken);
    }

    /// <inheritdoc />
    public async Task<List<TranslationTask>> GetTasksByRepositoryAsync(
        string repositoryId,
        CancellationToken cancellationToken = default)
    {
        return await _context.TranslationTasks
            .Where(t => t.RepositoryId == repositoryId && !t.IsDeleted)
            .OrderByDescending(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }
}
