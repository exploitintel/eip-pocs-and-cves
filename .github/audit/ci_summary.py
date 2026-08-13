"""Summarise a lab-health run for the CI job summary and set the exit status.

Reads the combined results.jsonl from every shard, prints Markdown, and exits
non-zero when a lab is broken *or* when a lab was never reported at all.

That second check matters as much as the first. Coverage is assembled from
independent shards, so a shard that dies before writing its artifact removes
labs from the run silently. Without an expected-set comparison the summary
would happily report "96 of 96 healthy" while 14 labs went untested — the same
green-but-meaningless signal this workflow exists to replace.

The state taxonomy lives in report.py so the CI verdict and the generated
report can never disagree about what counts as a failure.
"""

import argparse
import collections
import json
import pathlib
import sys

import report

# Real outcomes that describe the environment rather than a lab defect.
NOT_A_DEFECT = tuple(s for s in report.EXCLUDED_STATES if s not in ("NO_LAB", "NOT_DOCKER"))


def expected_labs(repo: pathlib.Path) -> set:
    """Every directory that is a Docker lab, straight from the filesystem.

    Deliberately not using inventory.collect(): this runs in a job with no
    images built and no need for a Docker daemon, and the question here is only
    "which labs should have been reported".
    """
    return {
        d.name for d in repo.glob("CVE-*")
        if d.is_dir() and (d / "docker-compose.yml").is_file()
    }


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("results")
    parser.add_argument("--repo", default=".")
    args = parser.parse_args()

    path = pathlib.Path(args.results)
    if not path.exists() or not path.stat().st_size:
        print("## Lab health\n\nNo results were produced — every shard failed "
              "before writing output.")
        return 1

    latest = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        if line.strip():
            record = json.loads(line)
            latest[record["cve"]] = record
    records = sorted(latest.values(), key=lambda r: r["cve"])

    expected = expected_labs(pathlib.Path(args.repo))
    missing = sorted(expected - set(latest))

    counts = collections.Counter(r["state"] for r in records)
    broken = [r for r in records if r["state"] in report.FAIL_STATES]
    passed = [r for r in records if r["state"] in report.PASS_STATES]

    print("## Lab health\n")
    print(f"**{len(passed)} passed, {len(broken)} broken, "
          f"{len(expected)} labs expected.**\n")

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

    if missing:
        print(f"\n### Never reported ({len(missing)})\n")
        print("These labs produced no result at all — a shard most likely died "
              "before writing its artifact. They were **not** tested.\n")
        for cve in missing:
            print(f"- `{cve}`")

    noted = [r for r in records if r["state"] in NOT_A_DEFECT]
    if noted:
        print("\n### Not counted as failures\n")
        for r in noted:
            detail = (r.get("reason") or "")[:90]
            print(f"- `{r['cve']}` — {r['state']}: {detail}")

    if broken or missing:
        reasons = []
        if broken:
            reasons.append(f"{len(broken)} lab(s) broken")
        if missing:
            reasons.append(f"{len(missing)} lab(s) never reported")
        print(f"\nFailing: {', '.join(reasons)}.")
        return 1

    print("\nEvery expected lab reported, and all that this runner can "
          "exercise are healthy.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
