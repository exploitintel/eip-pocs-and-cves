using System.Text;
using System.Text.RegularExpressions;

namespace OpenDeepWiki.Services.Wiki;

/// <summary>
/// Repairs Mermaid snippets emitted by LLMs before wiki Markdown is persisted.
/// </summary>
public static partial class MermaidMarkdownNormalizer
{
    private static readonly Regex MermaidFencePattern = new(
        @"(?<fence>```[ \t]*mermaid[^\r\n]*\r?\n)(?<code>.*?)(?<closing>\r?\n```)",
        RegexOptions.IgnoreCase | RegexOptions.Singleline | RegexOptions.Compiled);

    /// <summary>
    /// Normalizes Mermaid code fences inside Markdown while leaving all other
    /// fenced code blocks and prose untouched.
    /// </summary>
    public static string Normalize(string content)
    {
        if (string.IsNullOrEmpty(content))
        {
            return content;
        }

        return MermaidFencePattern.Replace(content, match =>
        {
            var code = match.Groups["code"].Value;
            var normalized = NormalizeMermaidCode(code);

            return $"{match.Groups["fence"].Value}{normalized}{match.Groups["closing"].Value}";
        });
    }

    private static string NormalizeMermaidCode(string code)
    {
        if (IsFlowchart(code))
        {
            return NormalizeFlowchartCode(code);
        }

        if (IsErDiagram(code))
        {
            return NormalizeErDiagramCode(code);
        }

        if (IsClassDiagram(code))
        {
            return NormalizeClassDiagramCode(code);
        }

        return code;
    }

    private static string NormalizeFlowchartCode(string code)
    {
        var normalized = MermaidFlowchartIdNormalizer.Normalize(code);

        normalized = FlowchartSquareLabelPattern().Replace(normalized, match =>
        {
            var label = match.Groups["label"].Value;
            var trimmed = label.TrimStart();

            // Already quoted/backtick labels are safe. Labels that start with a
            // shape marker such as "(" should keep their Mermaid shape syntax.
            if (IsQuotedFlowchartLabel(label) ||
                trimmed.StartsWith('(') ||
                trimmed.StartsWith('[') ||
                trimmed.StartsWith('{'))
            {
                return match.Value;
            }

            return $"{match.Groups["prefix"].Value}[\"{EscapeLabel(label)}\"]";
        });

        normalized = FlowchartDiamondLabelPattern().Replace(normalized, match =>
        {
            var label = match.Groups["label"].Value;
            if (IsQuotedFlowchartLabel(label))
            {
                return match.Value;
            }

            return $"{match.Groups["prefix"].Value}{{\"{EscapeLabel(label)}\"}}";
        });

        return FlowchartEdgeLabelPattern().Replace(normalized, match =>
        {
            var label = match.Groups["label"].Value;

            if (IsQuotedFlowchartLabel(label))
            {
                return match.Value;
            }

            return $"{match.Groups["operator"].Value}|\"{EscapeLabel(label)}\"|";
        });
    }

    private static string NormalizeErDiagramCode(string code)
    {
        var lines = code.Split('\n');
        var normalizedLines = new string[lines.Length];
        var inEntityBlock = false;

        for (var i = 0; i < lines.Length; i++)
        {
            var line = lines[i];
            var carriageReturn = line.EndsWith('\r') ? "\r" : string.Empty;
            var logicalLine = carriageReturn.Length > 0 ? line[..^1] : line;

            normalizedLines[i] = NormalizeErDiagramLine(logicalLine, ref inEntityBlock) + carriageReturn;
        }

        return string.Join('\n', normalizedLines);
    }

    private static string NormalizeClassDiagramCode(string code)
    {
        var lines = code.Split('\n');
        var normalizedLines = new string[lines.Length];
        var inClassBlock = false;

        for (var i = 0; i < lines.Length; i++)
        {
            var line = lines[i];
            var carriageReturn = line.EndsWith('\r') ? "\r" : string.Empty;
            var logicalLine = carriageReturn.Length > 0 ? line[..^1] : line;
            var trimmed = logicalLine.Trim();

            if (inClassBlock && trimmed.Equals("end", StringComparison.OrdinalIgnoreCase))
            {
                var indent = logicalLine[..(logicalLine.Length - logicalLine.TrimStart().Length)];
                normalizedLines[i] = $"{indent}}}{carriageReturn}";
                inClassBlock = false;
                continue;
            }

            normalizedLines[i] = line;

            if (!inClassBlock && ClassBlockStartPattern().IsMatch(logicalLine))
            {
                inClassBlock = true;
            }
            else if (inClassBlock && trimmed.StartsWith("}", StringComparison.Ordinal))
            {
                inClassBlock = false;
            }
        }

        return string.Join('\n', normalizedLines);
    }

    private static string NormalizeErDiagramLine(string line, ref bool inEntityBlock)
    {
        var trimmed = line.Trim();
        if (trimmed.Length == 0 || trimmed.StartsWith("%%", StringComparison.Ordinal))
        {
            return line;
        }

        if (!inEntityBlock)
        {
            var entityBlock = ErEntityBlockStartPattern().Match(line);
            if (entityBlock.Success)
            {
                inEntityBlock = true;

                return $"{entityBlock.Groups["indent"].Value}{NormalizeIdentifierToken(entityBlock.Groups["entity"].Value)} {{{entityBlock.Groups["suffix"].Value}";
            }

            return ReplaceDottedIdentifiersOutsideQuotes(line);
        }

        if (trimmed.StartsWith('}'))
        {
            inEntityBlock = false;
            return line;
        }

        return NormalizeErAttributeLine(line);
    }

    private static string NormalizeErAttributeLine(string line)
    {
        var indentLength = line.Length - line.TrimStart().Length;
        var indent = line[..indentLength];
        var content = line[indentLength..].TrimEnd();

        if (content.Length == 0 || content.StartsWith("%%", StringComparison.Ordinal))
        {
            return line;
        }

        var tokens = ErTokenPattern()
            .Matches(content)
            .Select(match => match.Value)
            .ToList();

        if (tokens.Count == 0 || IsQuotedToken(tokens[0]))
        {
            return line;
        }

        if (IsEnumLikeAttribute(tokens))
        {
            var enumValue = NormalizeIdentifierToken(tokens[0]);
            tokens[0] = enumValue;
            tokens.Insert(1, $"value_{enumValue}");

            return indent + string.Join(' ', tokens);
        }

        if (tokens[0].Equals("repeated", StringComparison.OrdinalIgnoreCase) &&
            tokens.Count >= 3 &&
            !IsQuotedToken(tokens[1]) &&
            !IsQuotedToken(tokens[2]))
        {
            var originalType = $"{tokens[0]} {tokens[1]}";
            tokens[0] = $"{NormalizeIdentifierToken(tokens[0])}_{NormalizeIdentifierToken(tokens[1])}";
            tokens.RemoveAt(1);
            tokens[1] = NormalizeIdentifierToken(tokens[1]);

            NormalizeRemainingErTokens(tokens, startIndex: 2);
            AppendOriginalTypeCommentIfNeeded(tokens, originalType);

            return indent + string.Join(' ', tokens);
        }

        var originalFirstToken = tokens[0];
        tokens[0] = NormalizeIdentifierToken(tokens[0]);

        if (tokens.Count >= 2 && !IsQuotedToken(tokens[1]))
        {
            tokens[1] = NormalizeIdentifierToken(tokens[1]);
        }

        NormalizeRemainingErTokens(tokens, startIndex: 2);
        AppendOriginalTypeCommentIfNeeded(tokens, originalFirstToken);

        return indent + string.Join(' ', tokens);
    }

    private static void NormalizeRemainingErTokens(List<string> tokens, int startIndex)
    {
        for (var i = startIndex; i < tokens.Count; i++)
        {
            if (!IsQuotedToken(tokens[i]))
            {
                tokens[i] = NormalizeIdentifierToken(tokens[i]);
            }
        }
    }

    private static bool IsEnumLikeAttribute(IReadOnlyList<string> tokens)
    {
        return tokens.Count == 1 || (tokens.Count == 2 && IsQuotedToken(tokens[1]));
    }

    private static void AppendOriginalTypeCommentIfNeeded(List<string> tokens, string originalType)
    {
        if (!originalType.Contains('.', StringComparison.Ordinal))
        {
            return;
        }

        var originalComment = $"original: {EscapeErComment(originalType)}";
        var lastIndex = tokens.Count - 1;

        if (lastIndex >= 0 && IsQuotedToken(tokens[lastIndex]))
        {
            if (tokens[lastIndex].Contains("original:", StringComparison.OrdinalIgnoreCase))
            {
                return;
            }

            tokens[lastIndex] = tokens[lastIndex].Length == 2
                ? $"\"{originalComment}\""
                : $"{tokens[lastIndex][..^1]}; {originalComment}\"";
            return;
        }

        tokens.Add($"\"{originalComment}\"");
    }

    private static string ReplaceDottedIdentifiersOutsideQuotes(string line)
    {
        var result = new StringBuilder(line.Length);
        var segment = new StringBuilder();
        var inQuote = false;
        var escaped = false;

        foreach (var character in line)
        {
            if (character == '"' && !escaped)
            {
                if (!inQuote)
                {
                    result.Append(NormalizeIdentifierToken(segment.ToString()));
                    segment.Clear();
                    result.Append(character);
                    inQuote = true;
                }
                else
                {
                    result.Append(character);
                    inQuote = false;
                }

                escaped = false;
                continue;
            }

            if (inQuote)
            {
                result.Append(character);
                escaped = character == '\\' && !escaped;
                if (character != '\\')
                {
                    escaped = false;
                }
                continue;
            }

            segment.Append(character);
        }

        result.Append(NormalizeIdentifierToken(segment.ToString()));

        return result.ToString();
    }

    private static string NormalizeIdentifierToken(string value)
    {
        return DottedErIdentifierPattern().Replace(value, match => match.Value.Replace('.', '_'));
    }

    private static bool IsQuotedToken(string token)
    {
        return token.Length >= 2 && token.StartsWith('"') && token.EndsWith('"');
    }

    private static bool IsQuotedFlowchartLabel(string label)
    {
        var trimmed = label.TrimStart();
        return trimmed.StartsWith('"') || trimmed.StartsWith('`');
    }

    private static string EscapeErComment(string comment)
    {
        return comment.Replace("\\", "\\\\").Replace("\"", "\\\"");
    }

    private static bool IsFlowchart(string code)
    {
        foreach (var line in code.Split('\n'))
        {
            var trimmed = line.Trim();
            if (trimmed.Length == 0 || trimmed.StartsWith("%%", StringComparison.Ordinal))
            {
                continue;
            }

            return trimmed.StartsWith("flowchart", StringComparison.OrdinalIgnoreCase) ||
                   trimmed.StartsWith("graph", StringComparison.OrdinalIgnoreCase);
        }

        return false;
    }

    private static bool IsErDiagram(string code)
    {
        foreach (var line in code.Split('\n'))
        {
            var trimmed = line.Trim();
            if (trimmed.Length == 0 || trimmed.StartsWith("%%", StringComparison.Ordinal))
            {
                continue;
            }

            return trimmed.StartsWith("erDiagram", StringComparison.OrdinalIgnoreCase);
        }

        return false;
    }

    private static bool IsClassDiagram(string code)
    {
        foreach (var line in code.Split('\n'))
        {
            var trimmed = line.Trim();
            if (trimmed.Length == 0 || trimmed.StartsWith("%%", StringComparison.Ordinal))
            {
                continue;
            }

            return trimmed.StartsWith("classDiagram", StringComparison.OrdinalIgnoreCase);
        }

        return false;
    }

    private static string EscapeLabel(string label)
    {
        return label.Replace("\\", "\\\\").Replace("\"", "\\\"");
    }

    [GeneratedRegex(@"(?<prefix>\b[A-Za-z_][A-Za-z0-9_]*\s*)\[(?<label>[^\]\r\n]+)\]")]
    private static partial Regex FlowchartSquareLabelPattern();

    [GeneratedRegex(@"(?<prefix>\b[A-Za-z_][A-Za-z0-9_]*\s*)\{(?<label>[^{}\r\n]+)\}")]
    private static partial Regex FlowchartDiamondLabelPattern();

    [GeneratedRegex(@"(?<operator>(?:-{2,}(?:[ox>])?|={2,}>?|-\.{1,}->?))\|(?<label>[^|\r\n]+)\|")]
    private static partial Regex FlowchartEdgeLabelPattern();

    [GeneratedRegex(@"^\s*class\s+[A-Za-z_][A-Za-z0-9_.-]*\s*\{\s*$")]
    private static partial Regex ClassBlockStartPattern();

    [GeneratedRegex(@"^(?<indent>\s*)(?<entity>[A-Za-z_][A-Za-z0-9_.-]*)\s*\{(?<suffix>\s*(?:%%.*)?)$")]
    private static partial Regex ErEntityBlockStartPattern();

    [GeneratedRegex(@"""(?:\\.|[^""\\])*""|\S+")]
    private static partial Regex ErTokenPattern();

    [GeneratedRegex(@"\b[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)+\b")]
    private static partial Regex DottedErIdentifierPattern();
}
