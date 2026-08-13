"""Render an audit run's results.jsonl into AUDIT-REPORT.md."""

from __future__ import annotations

import collections
import json
import pathlib
import sys

PASS_STATES = ("PASS",)
FAIL_STATES = (
    "FAIL_BUILD", "FAIL_UP", "FAIL_UNHEALTHY", "FAIL_ENV_MISSING", "TIMEOUT"
)
# CRASH_EXPECTED is neither: the lab did what it was built to do, but we did not
# verify a healthy service. It gets its own section for human confirmation.
# FAIL_PREREQ is not a lab defect: the lab needs an external dependency its
# README documents (a kind cluster, say) that this host does not provide.
EXCLUDED_STATES = (
    "NO_LAB", "NOT_DOCKER", "SKIPPED", "INFRA_ERROR", "CRASH_EXPECTED",
    "FAIL_PREREQ",
)


def load(results_path) -> list:
    path = pathlib.Path(results_path)
    return [
        json.loads(line)
        for line in path.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]


def merge(record_sets) -> list:
    """Combine runs, later ones winning per CVE.

    A retry re-tests a subset under corrected settings; its verdict supersedes
    the original. Within one set the last record for a CVE also wins, so a
    resumed run does not double-count.
    """
    latest = {}
    for records in record_sets:
        for record in records:
            latest[record["cve"]] = record
    return sorted(latest.values(), key=lambda r: r["cve"])


def summarise(records) -> dict:
    return dict(collections.Counter(r["state"] for r in records))


def advisories(records) -> list:
    """Real defects that are not 'lab down'."""
    out = []
    for r in sorted(records, key=lambda x: x["cve"]):
        if r.get("state") != "PASS":
            continue
        if r.get("required_env") and not r.get("env_file_used"):
            out.append(
                f"`{r['cve']}` — came up, but requires env vars with no default "
                f"and no `.env.example`: {', '.join(r['required_env'])}. "
                "Host port is assigned randomly."
            )
    return out


def render(records, manifest) -> str:
    counts = summarise(records)
    tested = [r for r in records if r["state"] not in EXCLUDED_STATES]
    passed = [r for r in records if r["state"] in PASS_STATES]
    failed = [r for r in records if r["state"] in FAIL_STATES]

    lines = [
        "# CVE Docker Lab Audit — Report",
        "",
        f"**Repo SHA:** `{manifest.get('repo_sha', 'unknown')}`  ",
        f"**Host:** {manifest.get('host', 'unknown')}  ",
        f"**Finished:** {manifest.get('finished_at', 'unknown')}  ",
        f"**Host artifacts preserved:** {manifest.get('host_preserved', 'unknown')}",
        "",
        "Pass bar: the lab builds and comes up healthy. This audit does **not**",
        "test whether the PoC still proves the vulnerability.",
        "",
        "## Summary",
        "",
        f"- Directories recorded: **{len(records)}**",
        f"- Labs tested: **{len(tested)}**",
        f"- Passed: **{len(passed)}**",
        f"- Failed: **{len(failed)}**",
        "",
        "| State | Count |",
        "|---|---|",
    ]
    for state, count in sorted(counts.items(), key=lambda kv: (-kv[1], kv[0])):
        lines.append(f"| `{state}` | {count} |")

    if failed:
        lines += ["", "## Failures grouped by root cause", ""]
        by_cause = collections.defaultdict(list)
        for r in failed:
            by_cause[r.get("build_cause") or r["state"]].append(r)
        for cause, group in sorted(by_cause.items()):
            lines += [f"### {cause} ({len(group)})", ""]
            for r in sorted(group, key=lambda x: x["cve"]):
                lines.append(f"- `{r['cve']}` — {r.get('reason', '')}")
            lines.append("")

    crashers = [r for r in records if r["state"] == "CRASH_EXPECTED"]
    if crashers:
        lines += [
            "", "## Crash-by-design targets", "",
            "These labs declare sanitizer abort semantics and their containers "
            "died with a sanitizer signature. The abort *is* the demonstration, "
            "so they are not counted as failures — but neither was a healthy "
            "service verified. Confirm by hand.", "",
        ]
        for r in sorted(crashers, key=lambda x: x["cve"]):
            lines.append(f"- `{r['cve']}` — {r.get('reason', '')}")
        lines.append("")

    notes = advisories(records)
    if notes:
        lines += ["## Advisory findings", "",
                  "Labs that came up but carry a real defect:", ""]
        lines += [f"- {n}" for n in notes]
        lines.append("")

    lines += ["## Per-lab results", "",
              "| CVE | State | Tier | Ports | Build (s) | Detail |",
              "|---|---|---|---|---|---|"]
    for r in sorted(records, key=lambda x: x["cve"]):
        ports = ", ".join(str(p) for p in r.get("runtime_ports") or []) or "—"
        lines.append(
            f"| `{r['cve']}` | {r['state']} | "
            f"{r.get('probe_tier_effective') or '—'} | {ports} | "
            f"{r.get('build_seconds', '—')} | {(r.get('reason') or '')[:80]} |"
        )
    return "\n".join(lines) + "\n"


def main():
    if len(sys.argv) < 2:
        print(
            "usage: python3 report.py runs/<run-id> [runs/<retry-id> ...]\n"
            "Later runs supersede earlier ones per CVE.",
            file=sys.stderr,
        )
        return 1
    run_paths = [pathlib.Path(p) for p in sys.argv[1:]]
    records = merge(load(p / "results.jsonl") for p in run_paths)

    manifest = {}
    for path in run_paths:
        manifest_path = path / "manifest.json"
        if manifest_path.exists():
            manifest.update(json.loads(manifest_path.read_text()))
    manifest["runs_merged"] = [p.name for p in run_paths]

    out = run_paths[0] / "AUDIT-REPORT.md"
    out.write_text(render(records, manifest), encoding="utf-8")
    print(f"wrote {out} ({len(records)} records from {len(run_paths)} run(s))")
    return 0


if __name__ == "__main__":
    sys.exit(main())
