namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Maps wiki / chat language codes to English display names for prompts.
/// Unknown codes are returned as-is so custom WIKI_LANGUAGES values still work.
/// </summary>
public static class WikiLanguageNames
{
    /// <summary>
    /// Default comma-separated wiki language list.
    /// Keep in sync with <c>wikiLanguageCodes</c> in <c>web/i18n/config.ts</c>.
    /// </summary>
    public const string DefaultLanguages = "en,zh,zh-tw,ja,ko,es,fr,de,pt-br,pl,ru,ar";

    public static string Normalize(string? languageCode)
    {
        return string.IsNullOrWhiteSpace(languageCode)
            ? string.Empty
            : languageCode.Trim().ToLowerInvariant();
    }

    public static string GetEnglishName(string? languageCode)
    {
        return Normalize(languageCode) switch
        {
            "" or "en" or "en-us" or "en-gb" => "English",
            "zh" or "zh-cn" or "zh-hans" => "Chinese (Simplified)",
            "zh-tw" or "zh-hant" or "zh-hk" => "Chinese (Traditional)",
            "ja" or "ja-jp" => "Japanese",
            "ko" or "ko-kr" => "Korean",
            "es" or "es-es" or "es-mx" => "Spanish",
            "fr" or "fr-fr" => "French",
            "de" or "de-de" => "German",
            "pt" or "pt-br" or "pt-pt" => "Portuguese (Brazil)",
            "pl" or "pl-pl" => "Polish",
            "ru" or "ru-ru" => "Russian",
            "ar" or "ar-sa" => "Arabic",
            "it" or "it-it" => "Italian",
            "nl" or "nl-nl" => "Dutch",
            "tr" or "tr-tr" => "Turkish",
            "vi" or "vi-vn" => "Vietnamese",
            "th" or "th-th" => "Thai",
            "id" or "id-id" => "Indonesian",
            "hi" or "hi-in" => "Hindi",
            "uk" or "uk-ua" => "Ukrainian",
            "sv" or "sv-se" => "Swedish",
            "cs" or "cs-cz" => "Czech",
            _ => string.IsNullOrWhiteSpace(languageCode) ? "English" : languageCode.Trim()
        };
    }
}
