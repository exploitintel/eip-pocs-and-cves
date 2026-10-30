using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Repositories;

namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Interface for Wiki generation operations.
/// Uses AI agents to generate catalog structures and document content.
/// </summary>
public interface IWikiGenerator
{
    /// <summary>
    /// Generates the project architecture mind map for a repository.
    /// Uses AI to analyze the repository and create a hierarchical mind map.
    /// This should be called before GenerateCatalogAsync and runs independently.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="branchLanguage">The branch language to generate mind map for.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    Task GenerateMindMapAsync(
        RepositoryWorkspace workspace,
        BranchLanguage branchLanguage,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Generates the wiki catalog structure for a repository.
    /// Uses AI to analyze the repository and create a hierarchical catalog.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="branchLanguage">The branch language to generate catalog for.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    Task GenerateCatalogAsync(
        RepositoryWorkspace workspace,
        BranchLanguage branchLanguage,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Generates document content for all catalog items.
    /// Uses AI to create Markdown content for each wiki page.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="branchLanguage">The branch language to generate documents for.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    Task GenerateDocumentsAsync(
        RepositoryWorkspace workspace,
        BranchLanguage branchLanguage,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Regenerates a specific document by catalog path using the original document generation flow.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="branchLanguage">The branch language to regenerate document for.</param>
    /// <param name="documentPath">The catalog path of target document.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    Task RegenerateDocumentAsync(
        RepositoryWorkspace workspace,
        BranchLanguage branchLanguage,
        string documentPath,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Performs incremental update of wiki content based on changed files.
    /// Only regenerates documents affected by the changes.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="branchLanguage">The branch language to update.</param>
    /// <param name="changedFiles">Array of relative file paths that changed.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    Task IncrementalUpdateAsync(
        RepositoryWorkspace workspace,
        BranchLanguage branchLanguage,
        string[] changedFiles,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Translates wiki content from source language to target language.
    /// Creates new BranchLanguage, translates catalog structure and all documents.
    /// </summary>
    /// <param name="workspace">The prepared repository workspace.</param>
    /// <param name="sourceBranchLanguage">The source branch language to translate from.</param>
    /// <param name="targetLanguageCode">The target language code to translate to.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>The newly created BranchLanguage for the target language.</returns>
    Task<BranchLanguage> TranslateWikiAsync(
        RepositoryWorkspace workspace,
        BranchLanguage sourceBranchLanguage,
        string targetLanguageCode,
        CancellationToken cancellationToken = default);
}
