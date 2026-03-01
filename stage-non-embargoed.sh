#!/bin/bash
# Stage all non-embargoed CVE directories for commit
#
# Usage: ./stage-non-embargoed.sh [--dry-run]
#
# Scans all CVE-* directories and stages those WITHOUT EMBARGO.md files.
# Use --dry-run to preview what would be staged without actually staging.

set -euo pipefail

DRY_RUN=false
if [[ "${1:-}" == "--dry-run" ]]; then
    DRY_RUN=true
    echo "🔍 DRY RUN MODE - showing what would be staged"
    echo
fi

STAGED_COUNT=0
SKIPPED_COUNT=0

for dir in CVE-*/; do
    # Skip if not a directory
    [ ! -d "$dir" ] && continue

    cve=$(basename "$dir")

    if [ -f "$dir/EMBARGO.md" ]; then
        echo "⏭️  Skipping $cve (embargoed)"
        ((SKIPPED_COUNT++))
    else
        echo "✅ Adding $cve"
        if [ "$DRY_RUN" = false ]; then
            git add "$dir"
        fi
        ((STAGED_COUNT++))
    fi
done

echo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ "$DRY_RUN" = true ]; then
    echo "Summary (dry run): Would stage $STAGED_COUNT CVEs, skip $SKIPPED_COUNT embargoed"
else
    echo "Summary: Staged $STAGED_COUNT CVEs, skipped $SKIPPED_COUNT embargoed"
    if [ $STAGED_COUNT -gt 0 ]; then
        echo
        echo "Next steps:"
        echo "  git status          # Review staged changes"
        echo "  git commit          # Commit the staged CVEs"
    fi
fi
