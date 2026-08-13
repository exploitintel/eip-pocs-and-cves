"""Summarise a lab-health run for the CI job summary, and set the exit status.

Reads the combined results.jsonl from every shard, prints Markdown, and exits
non-zero only when a lab is genuinely broken. States that describe the
environment rather than the lab -- an unmet documented prerequisite, a
registry hiccup, a target that aborts by design -- are reported but do not
fail the run, so the signal stays trustworthy.
"""

import collections
import json
import pathlib
import sys

# A lab defect: the lab itself is broken and someone should fix it.
FAILING = (
    "FAIL_BUILD", "FAIL_UP", "FAIL_UNHEALTHY", "FAIL_ENV_MISSING", "TIMEOUT",
)
# Real outcomes that are not lab defects.
INFORMATIONAL = (
    "FAIL_PREREQ",     # needs something this runner does not provide (e.g. a kind cluster)
    "CRASH_EXPECTED",  # sanitizer abort is the demonstration
    "INFRA_ERROR",     # registry/network hiccup
    "SKIPPED",         # excluded by policy, e.g. privileged labs in CI
    "NO_LAB",          # not a Docker lab
    "NOT_DOCKER",
)


def main() -> int:
    if len(sys.argv) < 2:
        print("usage: ci_summary.py <results.jsonl>", file=sys.stderr)
        return 2

    path = pathlib.Path(sys.argv[1])
    if not path.exists() or not path.stat().st_size:
        print("## Lab health\n\nNo results were produced — every shard failed "
              "before writing output.")
        return 1

    # Later records win, so a lab retried within a run is counted once.
    latest = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        if line.strip():
            record = json.loads(line)
            latest[record["cve"]] = record
    records = sorted(latest.values(), key=lambda r: r["cve"])

    counts = collections.Counter(r["state"] for r in records)
    broken = [r for r in records if r["state"] in FAILING]
    passed = [r for r in records if r["state"] == "PASS"]

    print("## Lab health\n")
    print(f"**{len(passed)} of {len(records)} labs healthy.**\n")

    print("| State | Count |")
    print("|---|---|")
    for state, count in sorted(counts.items(), key=lambda kv: (-kv[1], kv[0])):
        print(f"| `{state}` | {count} |")

    if broken:
        print(f"\n### Broken labs ({len(broken)})\n")
        print("| Lab | State | Detail |")
        print("|---|---|---|")
        for r in broken:
            detail = (r.get("reason") or "").replace("|", "\\|")[:90]
            print(f"| `{r['cve']}` | {r['state']} | {detail} |")

    noted = [r for r in records if r["state"] in INFORMATIONAL
             and r["state"] not in ("NO_LAB", "NOT_DOCKER")]
    if noted:
        print("\n### Not counted as failures\n")
        for r in noted:
            detail = (r.get("reason") or "")[:90]
            print(f"- `{r['cve']}` — {r['state']}: {detail}")

    if broken:
        print(f"\nFailing because {len(broken)} lab(s) are broken.")
        return 1

    print("\nAll labs that this runner can exercise are healthy.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
