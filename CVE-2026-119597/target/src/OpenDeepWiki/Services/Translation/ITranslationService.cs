using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Translation;

/// <summary>
/// 翻译服务接口
/// 管理翻译任务的创建和查询
/// </summary>
public interface ITranslationService
{
    /// <summary>
    /// 创建翻译任务
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="repositoryBranchId">仓库分支ID</param>
    /// <param name="sourceBranchLanguageId">源语言分支ID</param>
    /// <param name="targetLanguageCode">目标语言代码</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>创建的翻译任务，如果任务已存在则返回null</returns>
    Task<TranslationTask?> CreateTaskAsync(
        string repositoryId,
        string repositoryBranchId,
        string sourceBranchLanguageId,
        string targetLanguageCode,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 批量创建翻译任务
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="repositoryBranchId">仓库分支ID</param>
    /// <param name="sourceBranchLanguageId">源语言分支ID</param>
    /// <param name="targetLanguageCodes">目标语言代码列表</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>创建的翻译任务列表</returns>
    Task<List<TranslationTask>> CreateTasksAsync(
        string repositoryId,
        string repositoryBranchId,
        string sourceBranchLanguageId,
        IEnumerable<string> targetLanguageCodes,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// 获取下一个待处理的翻译任务
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>待处理的翻译任务，如果没有则返回null</returns>
    Task<TranslationTask?> GetNextPendingTaskAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// 更新任务状态为处理中
    /// </summary>
    /// <param name="taskId">任务ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task MarkAsProcessingAsync(string taskId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 更新任务状态为已完成
    /// </summary>
    /// <param name="taskId">任务ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task MarkAsCompletedAsync(string taskId, CancellationToken cancellationToken = default);

    /// <summary>
    /// 更新任务状态为失败
    /// </summary>
    /// <param name="taskId">任务ID</param>
    /// <param name="errorMessage">错误信息</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task MarkAsFailedAsync(string taskId, string errorMessage, CancellationToken cancellationToken = default);

    /// <summary>
    /// 获取仓库的翻译任务列表
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>翻译任务列表</returns>
    Task<List<TranslationTask>> GetTasksByRepositoryAsync(
        string repositoryId,
        CancellationToken cancellationToken = default);
}
