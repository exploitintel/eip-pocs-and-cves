"""CVE Docker lab audit runner.

Phase 1 builds every lab in parallel (no host ports are bound during build).
Phase 2 brings each lab up one at a time, probes it, captures evidence, and
tears it down. Records are immutable and keyed by (repo_sha, cve).
"""

from __future__ import annotations

import argparse
import concurrent.futures
import datetime
import json
import os
import pathlib
import subprocess
import sys
import tempfile
import time

import inventory
import probes
import safety

REPO_DEFAULT = "."
HARNESS_HOME = pathlib.Path(tempfile.gettempdir()) / "cve-lab-audit"
# Never default inside the repository: run output would dirty the working
# tree, and the dirty-tree guard would then refuse every subsequent run.
RUNS_DIR = HARNESS_HOME / "runs"

BUILD_TIMEOUT = 1200
UP_TIMEOUT = 300
PROBE_TIMEOUT = 180
STABLE_SECONDS = 30
BUILD_WORKERS = 6
DISK_FLOOR_GB = safety.DISK_FLOOR_BYTES // 1024 ** 3
# Prune images this run created once free space drops below this. Keep it
# low: a large value fires after every lab on an ordinary disk and destroys
# the layer cache, turning a warm re-run into a cold rebuild.
DISK_HEADROOM_GB = 20

INFRA_PATTERNS = (
    "toomanyrequests",
    "you have reached your pull rate limit",
    "429 too many requests",
    "temporary failure in name resolution",
    "tls handshake timeout",
    "i/o timeout",
    "connection reset by peer",
    "no space left on device",
    # Transient upstream HTTP errors while fetching release assets. CVE-2024-21626
    # failed on a GitHub 503 pulling a runc binary — the lab is fine, the CDN was not.
    "503 service unavailable",
    "502 bad gateway",
    "500 internal server error",
    "connection timed out",
    # containerd content-store race seen under heavy parallel builds
    # (CVE-2026-0766): "failed commit on ref ... commit failed: rename".
    "failed commit on ref",
    "commit failed: rename",
)
UPSTREAM_PATTERNS = (
    "manifest unknown",
    "pull access denied",
    "repository does not exist",
)
PACKAGE_PATTERNS = (
    "unable to locate package",
    "has no installation candidate",
    "no package matching",
)
DEPENDENCY_PATTERNS = (
    "no matching distribution found",
    "could not find a version that satisfies",
    "npm err! 404",
)
# CVE-2026-66713 declares `build: .` for a service, which defaults to
# `Dockerfile` — a file that directory does not contain. The lab cannot build
# at all, which is a repo defect distinct from upstream rot.
MISSING_DOCKERFILE_PATTERNS = (
    "failed to read dockerfile",
    "cannot locate specified dockerfile",
)


class DirtyRepoError(Exception):
    """The repo under audit has uncommitted changes."""


def _now():
    return datetime.datetime.now(datetime.timezone.utc).isoformat()


def classify_build_failure(log: str) -> str:
    """Bucket a failed build log into an actionable root cause."""
    text = (log or "").lower()
    for patterns, label in (
        (INFRA_PATTERNS, "infra"),
        (MISSING_DOCKERFILE_PATTERNS, "missing_dockerfile"),
        (UPSTREAM_PATTERNS, "upstream_gone"),
        (PACKAGE_PATTERNS, "package_gone"),
        (DEPENDENCY_PATTERNS, "dependency_gone"),
    ):
        if any(p in text for p in patterns):
            return label
    return "build_error"


def is_infra_failure(log: str) -> bool:
    return classify_build_failure(log) == "infra"


def repo_sha(repo_root, allow_dirty: bool = False) -> str:
    """Short SHA of the repo under audit. Refuses to run against a dirty tree.

    `allow_dirty` exists only to verify uncommitted lab repairs before they are
    committed. Such a result describes the working tree, not the commit, so the
    SHA is suffixed `-dirty` and must never be presented as an audit of that
    commit.
    """
    dirty = subprocess.run(
        ["git", "status", "--porcelain"],
        cwd=str(repo_root), capture_output=True, text=True, timeout=60,
    )
    is_dirty = bool(dirty.stdout.strip())
    if is_dirty and not allow_dirty:
        raise DirtyRepoError(
            f"{repo_root} has uncommitted changes; results could not be "
            "attributed to a commit (use --allow-dirty to verify local repairs)"
        )
    sha = subprocess.run(
        ["git", "rev-parse", "--short", "HEAD"],
        cwd=str(repo_root), capture_output=True, text=True, timeout=60,
    )
    return sha.stdout.strip() + ("-dirty" if is_dirty else "")


class ConcurrentRunError(Exception):
    """Another audit process is already live in this run directory."""


class RunDir:
    """One audit run's on-disk home. Results are append-only."""

    def __init__(self, path):
        self.path = pathlib.Path(path)
        (self.path / "logs").mkdir(parents=True, exist_ok=True)

    @property
    def lock_file(self):
        return self.path / "run.pid"

    def acquire(self) -> None:
        """Refuse to start when another live process owns this run directory.

        Two runs sharing a run-id probe the same labs at once: they collide on
        pinned container names and compose networks, and both append to
        results.jsonl, so every verdict is an artifact of the race rather than
        of the lab. A stale PID from a crashed or rebooted run is reclaimed.
        """
        if self.lock_file.exists():
            try:
                pid = int(self.lock_file.read_text().strip())
            except (ValueError, OSError):
                pid = None
            if pid and pid != os.getpid():
                try:
                    os.kill(pid, 0)
                except ProcessLookupError:
                    pass  # stale lock, previous run died
                except PermissionError:
                    raise ConcurrentRunError(
                        f"pid {pid} still owns {self.path}"
                    )
                else:
                    raise ConcurrentRunError(
                        f"pid {pid} is already running in {self.path}"
                    )
        self.lock_file.write_text(str(os.getpid()), encoding="utf-8")

    def release(self) -> None:
        try:
            self.lock_file.unlink()
        except OSError:
            pass

    @property
    def results_file(self):
        return self.path / "results.jsonl"

    def append_result(self, record: dict) -> None:
        with self.results_file.open("a", encoding="utf-8") as handle:
            handle.write(json.dumps(record, sort_keys=True) + "\n")

    def write_manifest(self, data: dict) -> None:
        (self.path / "manifest.json").write_text(
            json.dumps(data, indent=2, sort_keys=True), encoding="utf-8"
        )

    def log_path(self, cve: str, name: str):
        directory = self.path / "logs" / cve
        directory.mkdir(parents=True, exist_ok=True)
        return directory / name

    def completed_cves(self) -> set:
        if not self.results_file.exists():
            return set()
        done = set()
        for line in self.results_file.read_text(encoding="utf-8").splitlines():
            line = line.strip()
            if line:
                done.add(json.loads(line)["cve"])
        return done


def compose_cmd(lab: dict, *args) -> list:
    """Full compose argv for a lab, carrying the profile and env-file flags."""
    return inventory.compose_args(lab.get("env_file")) + list(args)


def _compose(lab, *args, timeout):
    """Run docker compose in the lab directory, as a user would."""
    return subprocess.run(
        compose_cmd(lab, *args),
        cwd=lab["path"], capture_output=True, text=True, timeout=timeout,
    )


def build_lab(lab: dict, run_dir: RunDir, timeout: int = BUILD_TIMEOUT) -> dict:
    """Build one lab. Retries once on an infrastructure failure."""
    started = time.monotonic()
    attempts, result, log_text = 0, None, ""

    while attempts < 2:
        attempts += 1
        try:
            # --progress is a global compose flag; placing it after `build`
            # works but compose warns. Keep it global to stay quiet and correct.
            result = _compose(
                lab, "--progress", "plain", "build", timeout=timeout
            )
            log_text = (result.stdout or "") + (result.stderr or "")
        except subprocess.TimeoutExpired as exc:
            # Keep whatever the build managed to emit — without it a timeout is
            # undiagnosable, and we cannot tell a hung build from a slow one.
            partial = ""
            for stream in (exc.stdout, exc.stderr):
                if stream:
                    partial += stream if isinstance(stream, str) else stream.decode(
                        "utf-8", "replace"
                    )
            run_dir.log_path(lab["cve"], "build.log").write_text(
                f"TIMEOUT after {timeout}s\n\n{partial}", encoding="utf-8"
            )
            return {
                "build_state": "TIMEOUT",
                "build_seconds": round(time.monotonic() - started, 1),
                "build_cause": "timeout",
                "build_attempts": attempts,
                "build_timeout_used": timeout,
            }
        if result.returncode == 0 or not is_infra_failure(log_text):
            break
        time.sleep(20)

    run_dir.log_path(lab["cve"], "build.log").write_text(log_text, encoding="utf-8")
    seconds = round(time.monotonic() - started, 1)

    if result.returncode == 0:
        return {
            "build_state": "OK",
            "build_seconds": seconds,
            "build_cause": None,
            "build_attempts": attempts,
        }

    cause = classify_build_failure(log_text)
    return {
        "build_state": "INFRA_ERROR" if cause == "infra" else "FAIL_BUILD",
        "build_seconds": seconds,
        "build_cause": cause,
        "build_attempts": attempts,
    }


def effective_tier(static: str, containers) -> str:
    """Static analysis cannot see ports that only exist at runtime."""
    if static == "T3" and probes.runtime_ports(containers):
        return "T2"
    return static


def _ps(lab):
    try:
        result = _compose(lab, "ps", "-a", "--format", "json", timeout=60)
    except subprocess.TimeoutExpired:
        return []
    return probes.parse_ps(result.stdout)


def _teardown(lab, volumes_before=None, timeout=UP_TIMEOUT):
    """Tear a lab down without `-v`.

    `down -v` removes every named volume the compose file declares, including
    volumes that pre-date the run — it destroyed a user's existing
    `cve-2025-24490_pg_data`. Instead tear down without `-v` and delete only
    volumes that appeared while this lab was up, so state still does not leak
    between labs.
    """
    try:
        _compose(lab, "down", "--remove-orphans", timeout=timeout)
    except subprocess.TimeoutExpired:
        pass
    # A lab pinning `container_name` (e.g. CVE-2025-11539) cannot start while a
    # container of that exact name survives from an earlier interrupted run, and
    # `compose down` misses it when the leftover carries different project
    # labels. Only names this lab's own compose declares are touched.
    for name in lab.get("container_names") or []:
        subprocess.run(["docker", "rm", "-f", name],
                       capture_output=True, text=True, timeout=60)
    if volumes_before is not None:
        return safety.remove_new_volumes(volumes_before)
    return []


def probe_budget(lab: dict, floor: int = PROBE_TIMEOUT) -> int:
    """Seconds to allow a lab to become healthy.

    Honour the lab's own healthcheck declaration when it asks for longer than
    the floor — a slow-booting service like GitLab is not a broken one.
    """
    return int(max(floor, lab.get("health_budget_seconds") or 0))


def probe_lab(lab: dict, run_dir: RunDir, up_timeout: int = UP_TIMEOUT,
              probe_floor: int = PROBE_TIMEOUT) -> dict:
    """Bring one lab up, probe it to a verdict, capture evidence, tear it down."""
    started = time.monotonic()
    budget = probe_budget(lab, probe_floor)
    volumes_before = safety.snapshot()["volumes"]
    _teardown(lab, volumes_before)

    try:
        up = _compose(lab, "up", "-d", timeout=up_timeout)
    except subprocess.TimeoutExpired as exc:
        # Preserve partial output: without it an `up` timeout is undiagnosable,
        # and a slow image pull looks identical to a hung container.
        partial = ""
        for stream in (exc.stdout, exc.stderr):
            if stream:
                partial += stream if isinstance(stream, str) else stream.decode(
                    "utf-8", "replace"
                )
        run_dir.log_path(lab["cve"], "up.log").write_text(
            f"TIMEOUT after {up_timeout}s\n\n{partial}", encoding="utf-8"
        )
        _teardown(lab, volumes_before)
        return {
            "state": "TIMEOUT", "reason": f"up exceeded {up_timeout}s",
            "probe_tier_effective": lab["probe_tier_static"],
            "probe_seconds": round(time.monotonic() - started, 1),
            "probe_budget_used": budget,
            "runtime_ports": [],
        }

    up_log = (up.stdout or "") + (up.stderr or "")
    run_dir.log_path(lab["cve"], "up.log").write_text(up_log, encoding="utf-8")

    if up.returncode != 0:
        containers = _ps(lab)
        _teardown(lab, volumes_before)
        state = "FAIL_UP"
        if "declared as external, but could not be found" in up_log:
            # An unmet documented prerequisite (e.g. a kind cluster), not rot.
            state = "FAIL_PREREQ"
        elif lab["required_env"] and not lab["env_file"]:
            state = "FAIL_ENV_MISSING"
        elif is_infra_failure(up_log):
            state = "INFRA_ERROR"
        tail = up_log.strip().splitlines()
        return {
            "state": state,
            "reason": tail[-1] if tail else "up failed",
            "probe_tier_effective": lab["probe_tier_static"],
            "probe_seconds": round(time.monotonic() - started, 1),
            "runtime_ports": probes.runtime_ports(containers),
        }

    deadline = time.monotonic() + budget
    first_seen = time.monotonic()
    verdict, reason = probes.PENDING, "not started"
    containers, tier, port_results = [], lab["probe_tier_static"], {}

    while time.monotonic() < deadline:
        containers = _ps(lab)
        tier = effective_tier(lab["probe_tier_static"], containers)
        if tier == "T1":
            verdict, reason = probes.verdict_t1(
                containers, lab["healthcheck_services"]
            )
        elif tier == "T2":
            ports = probes.runtime_ports(containers)
            port_results = {p: probes.tcp_probe("127.0.0.1", p) for p in ports}
            verdict, reason = probes.verdict_t2(containers, port_results)
        else:
            stable = int(time.monotonic() - first_seen)
            verdict, reason = probes.verdict_t3(containers, stable, STABLE_SECONDS)

        if verdict in (probes.PASS, probes.FAIL_UP):
            break
        time.sleep(3)

    container_log = ""
    try:
        logs = _compose(lab, "logs", "--tail", "200", timeout=60)
        container_log = (logs.stdout or "") + (logs.stderr or "")
        run_dir.log_path(lab["cve"], "container.log").write_text(
            container_log, encoding="utf-8"
        )
    except subprocess.TimeoutExpired:
        pass

    run_dir.log_path(lab["cve"], "ps.json").write_text(
        json.dumps([c.__dict__ for c in containers], indent=2, sort_keys=True),
        encoding="utf-8",
    )
    if port_results:
        run_dir.log_path(lab["cve"], "probe.json").write_text(
            json.dumps({str(k): v for k, v in port_results.items()}, indent=2),
            encoding="utf-8",
        )

    _teardown(lab, volumes_before)

    state = {
        probes.PASS: "PASS",
        probes.FAIL_UP: "FAIL_UP",
        probes.PENDING: "FAIL_UNHEALTHY",
    }[verdict]

    # A memory-corruption lab whose target aborts on purpose has not "failed to
    # come up" — the abort is the demonstration. Requires BOTH the lab's own
    # declaration and a sanitizer signature in its output, so an ordinary crash
    # can never be laundered into a non-failure.
    if (
        state == "FAIL_UP"
        and lab.get("crash_expected")
        and probes.looks_like_sanitizer_abort(container_log)
    ):
        state = "CRASH_EXPECTED"
        reason = f"sanitizer abort, declared by the lab: {reason}"
    elif state in ("FAIL_UP", "FAIL_UNHEALTHY") and lab["required_env"] and not lab["env_file"]:
        state = "FAIL_ENV_MISSING"

    return {
        "state": state,
        "reason": reason,
        "probe_tier_effective": tier,
        "probe_seconds": round(time.monotonic() - started, 1),
        "probe_budget_used": budget,
        "runtime_ports": probes.runtime_ports(containers),
    }


def _select(labs, args):
    chosen = [l for l in labs if l["cohort"] == inventory.COHORT_DOCKER]
    if args.only:
        wanted = set(args.only)
        chosen = [l for l in chosen if l["cve"] in wanted]
    if getattr(args, "skip_privileged", False):
        chosen = [l for l in chosen if not l["privileged"]]
    # Privileged labs run last: they receive real host-level capability.
    chosen.sort(key=lambda l: (l["privileged"], l["cve"]))
    shards = getattr(args, "shards", 1) or 1
    if shards > 1:
        # Deal round-robin off the sorted list so each shard gets a comparable
        # mix of fast and slow labs rather than one shard inheriting a run of
        # heavy builds.
        chosen = [l for i, l in enumerate(chosen) if i % shards == args.shard]
    if args.limit:
        chosen = chosen[: args.limit]
    return chosen


def run(args) -> int:
    repo = pathlib.Path(args.repo)
    sha = repo_sha(repo, allow_dirty=args.allow_dirty)

    disk_floor = int(args.disk_floor_gb) * 1024 ** 3
    disk_headroom = int(args.disk_headroom_gb) * 1024 ** 3
    if not safety.disk_ok(floor=disk_floor):
        print(
            f"ABORT: free space {safety.free_bytes() // 1024**3} GB is below the "
            f"{args.disk_floor_gb} GB floor",
            file=sys.stderr,
        )
        return 2

    runs_dir = pathlib.Path(args.runs_dir) if args.runs_dir else RUNS_DIR
    runs_dir.mkdir(parents=True, exist_ok=True)
    run_id = args.run_id or (
        f"{sha}-{datetime.datetime.now().strftime('%Y%m%d-%H%M%S')}"
    )
    run_dir = RunDir(runs_dir / run_id)

    try:
        run_dir.acquire()
    except ConcurrentRunError as exc:
        print(f"ABORT: {exc}", file=sys.stderr)
        return 4

    print(f"[*] repo {repo} @ {sha}")
    print(f"[*] run dir {run_dir.path}")

    before = safety.snapshot()
    all_labs = inventory.collect(repo)
    targets = _select(all_labs, args)

    done = run_dir.completed_cves() if args.resume else set()
    if done:
        print(f"[*] resuming, {len(done)} lab(s) already recorded")
        targets = [l for l in targets if l["cve"] not in done]

    base_manifest = {
        "repo": str(repo),
        "repo_sha": sha,
        "started_at": _now(),
        "host": subprocess.run(
            ["hostname"], capture_output=True, text=True
        ).stdout.strip(),
        "docker": subprocess.run(
            ["docker", "version", "--format", "{{.Server.Version}}"],
            capture_output=True, text=True,
        ).stdout.strip(),
        "build_workers": args.build_workers,
        "total_dirs": len(all_labs),
        "targets": [l["cve"] for l in targets],
        "preflight": {k: len(v) for k, v in before.items()},
    }
    run_dir.write_manifest(base_manifest)

    # Non-docker cohorts are recorded once so totals reconcile to all dirs.
    if not args.only and not args.limit:
        for lab in all_labs:
            if lab["cohort"] == inventory.COHORT_DOCKER or lab["cve"] in done:
                continue
            run_dir.append_result({
                "cve": lab["cve"], "repo_sha": sha, "cohort": lab["cohort"],
                "state": {
                    inventory.COHORT_NONE: "NO_LAB",
                    inventory.COHORT_VM: "NOT_DOCKER",
                }.get(lab["cohort"], "SKIPPED"),
                "reason": "not a docker lab", "recorded_at": _now(),
            })

    # Privileged labs excluded by policy are recorded, not silently dropped:
    # a report that omits them reads as "everything passed" when 5 labs were
    # never exercised.
    if args.skip_privileged:
        for lab in all_labs:
            if lab["cohort"] != inventory.COHORT_DOCKER or not lab["privileged"]:
                continue
            if lab["cve"] in done:
                continue
            run_dir.append_result({
                "cve": lab["cve"], "repo_sha": sha, "cohort": lab["cohort"],
                "state": "SKIPPED", "privileged": True,
                "reason": "privileged lab excluded by --skip-privileged",
                "recorded_at": _now(),
            })

    if args.dry_run:
        for lab in targets:
            print(f"    {lab['cve']:<20} tier={lab['probe_tier_static']} "
                  f"privileged={lab['privileged']} env={lab['required_env']}")
        print(f"[*] dry run: {len(targets)} lab(s) would be audited")
        return 0

    builds = {}
    if args.phase in ("build", "all"):
        print(f"[*] phase 1: building {len(targets)} lab(s), "
              f"{args.build_workers} workers")
        with concurrent.futures.ThreadPoolExecutor(args.build_workers) as pool:
            futures = {
                pool.submit(build_lab, l, run_dir, args.build_timeout): l
                for l in targets
            }
            for i, future in enumerate(
                concurrent.futures.as_completed(futures), 1
            ):
                lab = futures[future]
                builds[lab["cve"]] = future.result()
                print(f"    [{i}/{len(targets)}] {lab['cve']:<20} "
                      f"build={builds[lab['cve']]['build_state']} "
                      f"({builds[lab['cve']]['build_seconds']}s)", flush=True)

    if args.phase in ("probe", "all"):
        print(f"[*] phase 2: probing {len(targets)} lab(s), serial")
        for i, lab in enumerate(targets, 1):
            build = builds.get(lab["cve"], {"build_state": "SKIPPED"})
            record = {
                "cve": lab["cve"], "repo_sha": sha, "cohort": lab["cohort"],
                "probe_tier_static": lab["probe_tier_static"],
                "privileged": lab["privileged"],
                "required_env": lab["required_env"],
                "env_file_used": bool(lab["env_file"]),
                "dockerfiles": lab["dockerfiles"],
                "profiles": lab.get("profiles") or [],
                "recorded_at": _now(),
                **build,
            }
            if build["build_state"] in ("FAIL_BUILD", "TIMEOUT", "INFRA_ERROR"):
                record["state"] = build["build_state"]
                if build["build_state"] == "TIMEOUT":
                    record["reason"] = (
                        f"build exceeded "
                        f"{build.get('build_timeout_used', args.build_timeout)}s "
                        f"at {args.build_workers} concurrent worker(s)"
                    )
                else:
                    record["reason"] = f"build failed: {build.get('build_cause')}"
                record["probe_tier_effective"] = lab["probe_tier_static"]
                record["runtime_ports"] = []
            else:
                record.update(
                    probe_lab(lab, run_dir, args.up_timeout,
                              args.probe_timeout)
                )

            run_dir.append_result(record)
            print(f"    [{i}/{len(targets)}] {lab['cve']:<20} "
                  f"{record['state']:<18} {record.get('reason', '')[:60]}",
                  flush=True)

            if not safety.disk_ok(floor=disk_headroom):
                removed = safety.remove_new_images(before, safety.snapshot())
                print(f"    [*] disk headroom low, removed "
                      f"{len(removed)} new image(s)", flush=True)

    after = safety.snapshot()
    try:
        safety.assert_preserved(before, after)
        preserved, note = True, "all pre-existing artifacts intact"
    except safety.HostMutationError as exc:
        preserved, note = False, str(exc)
        print(f"[!] HOST MUTATION: {note}", file=sys.stderr)

    base_manifest.update({
        "finished_at": _now(),
        "postflight": {k: len(v) for k, v in after.items()},
        "host_preserved": preserved,
        "host_note": note,
    })
    run_dir.write_manifest(base_manifest)
    run_dir.release()
    print(f"[*] done -> {run_dir.path}")
    return 0 if preserved else 3


def main():
    parser = argparse.ArgumentParser(description="Audit CVE Docker labs")
    parser.add_argument("--repo", default=REPO_DEFAULT)
    parser.add_argument("--phase", choices=("build", "probe", "all"), default="all")
    parser.add_argument("--only", action="append", default=[])
    parser.add_argument("--limit", type=int, default=0)
    parser.add_argument("--build-workers", type=int, default=BUILD_WORKERS)
    parser.add_argument(
        "--build-timeout", type=int, default=BUILD_TIMEOUT,
        help="Per-lab build timeout in seconds. Raise it when retrying "
             "source-compiling labs serially, which the default suits poorly.",
    )
    parser.add_argument(
        "--up-timeout", type=int, default=UP_TIMEOUT,
        help="Seconds for `compose up -d`. Labs with no build stanza pull "
             "their images here, which the default suits poorly.",
    )
    parser.add_argument(
        "--probe-timeout", type=int, default=PROBE_TIMEOUT,
        help="Floor for the health probe. A lab declaring a longer "
             "healthcheck budget gets its own, larger value.",
    )
    parser.add_argument("--runs-dir", default="")
    parser.add_argument(
        "--disk-floor-gb", type=int, default=DISK_FLOOR_GB,
        help="Abort if free space is below this. CI runners have far less "
             "disk than a lab host, so this must be lowered there.",
    )
    parser.add_argument(
        "--disk-headroom-gb", type=int, default=DISK_HEADROOM_GB,
        help="Below this, remove images this run created before continuing.",
    )
    parser.add_argument("--shards", type=int, default=1,
                        help="Split the lab list into this many shards.")
    parser.add_argument("--shard", type=int, default=0,
                        help="Which shard to run (0-based).")
    parser.add_argument("--skip-privileged", action="store_true",
                        help="Exclude labs that need privileged containers.")
    parser.add_argument("--run-id", default="")
    parser.add_argument("--resume", action="store_true")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument(
        "--allow-dirty", action="store_true",
        help="Audit a dirty working tree to verify uncommitted lab repairs. "
             "The recorded SHA gains a -dirty suffix; such a run is not an "
             "audit of any commit.",
    )
    return run(parser.parse_args())


if __name__ == "__main__":
    sys.exit(main())
