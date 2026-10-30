namespace OpenDeepWiki.Services.Wiki;

internal static class MermaidFlowchartIdNormalizer
{
    public static string Normalize(string code)
    {
        var lines = code.Split('\n');
        var normalizedLines = new string[lines.Length];
        var nodeIds = new HashSet<string>(StringComparer.Ordinal);
        var subgraphs = new List<SubgraphDeclaration>();

        for (var i = 0; i < lines.Length; i++)
        {
            var line = lines[i];
            normalizedLines[i] = line;

            var carriageReturn = line.EndsWith('\r') ? "\r" : string.Empty;
            var logicalLine = carriageReturn.Length > 0 ? line[..^1] : line;

            if (TryParseExplicitSubgraphDeclaration(i, logicalLine, carriageReturn, out var subgraph))
            {
                subgraphs.Add(subgraph);
                continue;
            }

            if (StartsWithKeyword(logicalLine, "subgraph"))
            {
                continue;
            }

            CollectNodeIds(logicalLine, nodeIds);
        }

        if (subgraphs.Count == 0)
        {
            return code;
        }

        var occupiedIds = new HashSet<string>(nodeIds, StringComparer.Ordinal);
        foreach (var subgraph in subgraphs)
        {
            occupiedIds.Add(subgraph.Id);
        }

        var seenSubgraphIds = new HashSet<string>(StringComparer.Ordinal);
        foreach (var subgraph in subgraphs)
        {
            var isDuplicateSubgraphId = !seenSubgraphIds.Add(subgraph.Id);
            if (!nodeIds.Contains(subgraph.Id) && !isDuplicateSubgraphId)
            {
                continue;
            }

            var replacementId = AllocateSubgraphId(subgraph.Id, occupiedIds);
            occupiedIds.Add(replacementId);
            normalizedLines[subgraph.LineIndex] = subgraph.Rewrite(replacementId);
        }

        return string.Join('\n', normalizedLines);
    }

    private static bool TryParseExplicitSubgraphDeclaration(
        int lineIndex,
        string line,
        string carriageReturn,
        out SubgraphDeclaration declaration)
    {
        declaration = default;

        var index = 0;
        while (index < line.Length && char.IsWhiteSpace(line[index]))
        {
            index++;
        }

        var keywordStart = index;
        const string keyword = "subgraph";
        if (!line.AsSpan(keywordStart).StartsWith(keyword, StringComparison.OrdinalIgnoreCase))
        {
            return false;
        }

        index += keyword.Length;
        if (index < line.Length && IsIdentifierChar(line[index]))
        {
            return false;
        }

        if (index >= line.Length || !char.IsWhiteSpace(line[index]))
        {
            return false;
        }

        while (index < line.Length && char.IsWhiteSpace(line[index]))
        {
            index++;
        }

        if (index >= line.Length || line[index] == '"')
        {
            return false;
        }

        if (!IsIdentifierStart(line[index]))
        {
            return false;
        }

        var idStart = index;
        index++;
        while (index < line.Length && IsIdentifierChar(line[index]))
        {
            index++;
        }

        var remainder = line[index..];
        var trimmedRemainder = remainder.AsSpan().TrimStart();
        if (trimmedRemainder.Length == 0 || IsWhitespaceOrComment(trimmedRemainder))
        {
            declaration = new SubgraphDeclaration(
                lineIndex,
                carriageReturn,
                line[..idStart],
                line[idStart..index],
                remainder,
                HasExplicitLabel: false);
            return true;
        }

        if (trimmedRemainder[0] != '[')
        {
            return false;
        }

        var labelOffset = remainder.Length - trimmedRemainder.Length;
        if (!TrySkipBalancedRegion(remainder, labelOffset + 1, remainder.Length, '[', ']',
                out var labelEnd))
        {
            return false;
        }

        if (!IsWhitespaceOrComment(remainder.AsSpan(labelEnd).TrimStart()))
        {
            return false;
        }

        declaration = new SubgraphDeclaration(
            lineIndex,
            carriageReturn,
            line[..idStart],
            line[idStart..index],
            remainder,
            HasExplicitLabel: true);
        return true;
    }

    private static void CollectNodeIds(string line, ISet<string> nodeIds)
    {
        var scanLimit = FindCommentStart(line);
        var index = 0;

        while (index < scanLimit)
        {
            if (!IsIdentifierStart(line[index]) ||
                (index > 0 && IsIdentifierChar(line[index - 1])))
            {
                index++;
                continue;
            }

            var idStart = index;
            index++;
            while (index < scanLimit && IsIdentifierChar(line[index]))
            {
                index++;
            }

            if (index >= scanLimit)
            {
                continue;
            }

            var identifier = line[idStart..index];
            var openerIndex = index;
            while (openerIndex < scanLimit && IsHorizontalWhitespace(line[openerIndex]))
            {
                openerIndex++;
            }

            if (openerIndex >= scanLimit)
            {
                continue;
            }

            if (line[openerIndex] == '@' && openerIndex + 1 < scanLimit && line[openerIndex + 1] == '{')
            {
                nodeIds.Add(identifier);
                if (!TrySkipBalancedRegion(line, openerIndex + 2, scanLimit, '{', '}',
                        out var nextIndex))
                {
                    break;
                }

                index = nextIndex;
                continue;
            }

            if (!IsNodeOpener(line[openerIndex]))
            {
                continue;
            }

            nodeIds.Add(identifier);
            if (!TrySkipNodeLabel(line, openerIndex, scanLimit, out var labelEnd))
            {
                break;
            }

            index = labelEnd;
        }
    }

    private static bool TrySkipNodeLabel(
        string line,
        int openerIndex,
        int scanLimit,
        out int nextIndex)
    {
        nextIndex = openerIndex;
        return line[openerIndex] switch
        {
            '[' => TrySkipBalancedRegion(line, openerIndex + 1, scanLimit, '[', ']', out nextIndex),
            '(' => TrySkipBalancedRegion(line, openerIndex + 1, scanLimit, '(', ')', out nextIndex),
            '{' => TrySkipBalancedRegion(line, openerIndex + 1, scanLimit, '{', '}', out nextIndex),
            '>' => TrySkipUntilClosingBracket(line, openerIndex + 1, scanLimit, out nextIndex),
            _ => false
        };
    }

    private static bool TrySkipBalancedRegion(
        string line,
        int startIndex,
        int scanLimit,
        char open,
        char close,
        out int nextIndex)
    {
        var depth = 1;
        var found = TryFindOutsideQuotedRegions(
            line,
            startIndex,
            scanLimit,
            (_, character) =>
            {
                if (character == open)
                {
                    depth++;
                    return false;
                }

                if (character != close)
                {
                    return false;
                }

                depth--;
                return depth == 0;
            },
            out var closingIndex);

        nextIndex = found ? closingIndex + 1 : scanLimit;
        return found;
    }

    private static bool TrySkipUntilClosingBracket(
        string line,
        int startIndex,
        int scanLimit,
        out int nextIndex)
    {
        var found = TryFindOutsideQuotedRegions(
            line,
            startIndex,
            scanLimit,
            (_, character) => character == ']',
            out var closingIndex);

        nextIndex = found ? closingIndex + 1 : scanLimit;
        return found;
    }

    private static int FindCommentStart(string line)
    {
        return TryFindOutsideQuotedRegions(
            line,
            startIndex: 0,
            scanLimit: line.Length,
            (index, character) =>
                character == '%' &&
                index + 1 < line.Length &&
                line[index + 1] == '%',
            out var commentIndex)
            ? commentIndex
            : line.Length;
    }

    private static bool TryFindOutsideQuotedRegions(
        string line,
        int startIndex,
        int scanLimit,
        Func<int, char, bool> shouldStop,
        out int matchIndex)
    {
        var inDoubleQuote = false;
        var inBacktick = false;
        var escaped = false;

        for (var i = startIndex; i < scanLimit; i++)
        {
            var character = line[i];
            if (inDoubleQuote)
            {
                if (character == '"' && !escaped)
                {
                    inDoubleQuote = false;
                }

                escaped = character == '\\' && !escaped;
                continue;
            }

            if (inBacktick)
            {
                if (character == '`')
                {
                    inBacktick = false;
                }

                continue;
            }

            if (character == '"')
            {
                inDoubleQuote = true;
                escaped = false;
                continue;
            }

            if (character == '`')
            {
                inBacktick = true;
                continue;
            }

            if (shouldStop(i, character))
            {
                matchIndex = i;
                return true;
            }
        }

        matchIndex = scanLimit;
        return false;
    }

    private static string AllocateSubgraphId(string originalId, ISet<string> occupiedIds)
    {
        var candidate = $"sg_{originalId}";
        if (!occupiedIds.Contains(candidate))
        {
            return candidate;
        }

        var suffix = 2;
        while (occupiedIds.Contains($"{candidate}_{suffix}"))
        {
            suffix++;
        }

        return $"{candidate}_{suffix}";
    }

    private static bool StartsWithKeyword(string line, string keyword)
    {
        var index = 0;
        while (index < line.Length && char.IsWhiteSpace(line[index]))
        {
            index++;
        }

        if (!line.AsSpan(index).StartsWith(keyword, StringComparison.OrdinalIgnoreCase))
        {
            return false;
        }

        var end = index + keyword.Length;
        return end >= line.Length || !IsIdentifierChar(line[end]);
    }

    private static bool IsWhitespaceOrComment(ReadOnlySpan<char> value)
    {
        return value.Length == 0 ||
               value.StartsWith("%%", StringComparison.Ordinal);
    }

    private static bool IsIdentifierStart(char character)
    {
        return character == '_' ||
               (character >= 'A' && character <= 'Z') ||
               (character >= 'a' && character <= 'z');
    }

    private static bool IsIdentifierChar(char character)
    {
        return IsIdentifierStart(character) ||
               (character >= '0' && character <= '9');
    }

    private static bool IsNodeOpener(char character)
    {
        return character is '[' or '(' or '{' or '>';
    }

    private static bool IsHorizontalWhitespace(char character)
    {
        return character != '\n' &&
               character != '\r' &&
               char.IsWhiteSpace(character);
    }

    private readonly record struct SubgraphDeclaration(
        int LineIndex,
        string CarriageReturn,
        string Prefix,
        string Id,
        string Tail,
        bool HasExplicitLabel)
    {
        public string Rewrite(string replacementId)
        {
            if (HasExplicitLabel)
            {
                return $"{Prefix}{replacementId}{Tail}{CarriageReturn}";
            }

            return $"{Prefix}{replacementId}[\"{Id}\"]{Tail}{CarriageReturn}";
        }
    }
}
