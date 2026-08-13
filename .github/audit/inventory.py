"""Derive the audit universe from the repo. No hardcoded CVE lists."""

from __future__ import annotations

import json
import pathlib
import re
import subprocess

COHORT_DOCKER = "docker_lab"
COHORT_VM = "not_docker"
COHORT_NONE = "no_lab"
COHORT_UNKNOWN = "unknown"

COMPOSE_FILE = "docker-compose.yml"
UNSET_VAR_RE = re.compile(r'The \\?"([A-Z_][A-Z0-9_]*)\\?" variable is not set')

# A lab may declare that its target is *meant* to die: memory-corruption labs
# set a sanitizer to abort so the container exit code is the demonstration.
# CVE-2026-64608's compose says so in a comment: abort_on_error=1 makes a memory
# error surface as SIGABRT (exit 134) "so the container exit code is unambiguous
# signal evidence". Such a lab must not be scored as if it failed to come up.
CRASH_ENV_MARKERS = ("abort_on_error=1", "asan_options", "ubsan_options", "msan_options")


class ComposeConfigError(Exception):
    """`docker compose config` exited non-zero."""


def classify(lab_dir: pathlib.Path) -> str:
    """Cohort for one CVE directory. Rules are ordered; first match wins.

    The VM lab check must precede the no-lab check: CVE-2026-50540 has neither
    a compose file nor a Dockerfile, so it would otherwise be miscounted.
    """
    if (lab_dir / COMPOSE_FILE).is_file():
        return COHORT_DOCKER
    if (lab_dir / "run.sh").is_file() and (lab_dir / "vm-assets").is_dir():
        return COHORT_VM
    if not list(lab_dir.glob("Dockerfile.*")):
        return COHORT_NONE
    return COHORT_UNKNOWN


def parse_required_env(stderr: str) -> list:
    """Variable names compose warned were unset, sorted and deduped."""
    return sorted({m.group(1) for m in UNSET_VAR_RE.finditer(stderr or "")})


def compose_args(env_file=None) -> list:
    """Base `docker compose` arguments shared by inventory and the runner.

    `--profile "*"` activates every declared profile. Without it, a lab whose
    services are all profile-gated resolves to zero services and `up` silently
    starts nothing — which would look like a crash rather than a config gate.
    CVE-2026-20348 is the case in point. Verified harmless on labs that declare
    no profiles.
    """
    args = ["docker", "compose", "--profile", "*"]
    if env_file:
        args += ["--env-file", str(env_file)]
    return args


def compose_config(lab_dir: pathlib.Path, env_file=None):
    """Resolved compose config plus stderr. Raises ComposeConfigError on failure."""
    cmd = compose_args(env_file) + ["config", "--format", "json"]
    result = subprocess.run(
        cmd, cwd=str(lab_dir), capture_output=True, text=True, timeout=180
    )
    if result.returncode != 0:
        raise ComposeConfigError(result.stderr.strip())
    return json.loads(result.stdout), result.stderr


def services_info(config: dict) -> dict:
    """Per-service facts needed to run and judge the lab.

    A port entry without a `published` key means the mapping used an unset
    variable; Docker will assign a random host port at runtime.
    """
    info = {}
    for name, svc in (config.get("services") or {}).items():
        svc = svc or {}
        health = svc.get("healthcheck") or {}
        env = svc.get("environment") or {}
        if isinstance(env, list):
            env = dict(
                (item.split("=", 1) + [""])[:2] for item in env if isinstance(item, str)
            )
        env_text = " ".join(f"{k}={v}" for k, v in env.items()).lower()
        static_ports, dynamic = [], False
        for port in svc.get("ports") or []:
            published = (port or {}).get("published")
            if published in (None, "", 0, "0"):
                dynamic = True
            else:
                static_ports.append(int(published))
        info[name] = {
            "healthcheck": bool(health) and not health.get("disable", False),
            "static_ports": sorted(set(static_ports)),
            "dynamic_ports": dynamic,
            "privileged": bool(svc.get("privileged")),
            "profiles": list(svc.get("profiles") or []),
            "crash_expected": any(m in env_text for m in CRASH_ENV_MARKERS),
            "healthcheck_spec": health,
            "container_name": svc.get("container_name") or "",
        }
    return info


DURATION_RE = re.compile(r"([0-9]*\.?[0-9]+)(ms|us|ns|h|m|s)")
DURATION_UNITS = {
    "ns": 1e-9, "us": 1e-6, "ms": 1e-3, "s": 1.0, "m": 60.0, "h": 3600.0
}


def parse_duration(value) -> float:
    """Parse a Go-style compose duration ('10s', '5m0s', '1h30m') to seconds."""
    if value is None or value == "":
        return 0.0
    if isinstance(value, (int, float)):
        # Compose may emit raw nanoseconds for durations set programmatically.
        return float(value) / 1e9 if value > 1e6 else float(value)
    total, matched = 0.0, False
    for amount, unit in DURATION_RE.findall(str(value)):
        total += float(amount) * DURATION_UNITS[unit]
        matched = True
    return total if matched else 0.0


def health_budget(services: dict) -> float:
    """How long the labs themselves say they may need to become healthy.

    A fixed probe timeout is wrong: CVE-2022-0735 is GitLab CE declaring
    start_period 5m with 60 retries at 10s, i.e. up to 900s. Deriving the
    budget from the lab's own healthcheck avoids failing a slow-but-working
    lab, without giving every lab the same generous allowance.
    """
    budgets = [0.0]
    for svc in services.values():
        health = svc.get("healthcheck_spec") or {}
        if not health:
            continue
        start = parse_duration(health.get("start_period"))
        interval = parse_duration(health.get("interval")) or 30.0
        retries = int(health.get("retries") or 3)
        budgets.append(start + interval * retries)
    return max(budgets)


def static_tier(services: dict) -> str:
    """Provisional tier. Upgraded to T2 at runtime if dynamic ports appear."""
    if any(s["healthcheck"] for s in services.values()):
        return "T1"
    if any(s["static_ports"] for s in services.values()):
        return "T2"
    return "T3"


def build_lab(lab_dir: pathlib.Path) -> dict:
    """Full inventory record for one CVE directory."""
    cohort = classify(lab_dir)
    record = {
        "cve": lab_dir.name,
        "path": str(lab_dir),
        "cohort": cohort,
        "compose_file": COMPOSE_FILE if cohort == COHORT_DOCKER else None,
        "services": [],
        "dockerfiles": sorted(p.name for p in lab_dir.glob("Dockerfile.*")),
        "static_host_ports": [],
        "dynamic_ports": False,
        "required_env": [],
        "env_file": None,
        "healthcheck_services": [],
        "privileged": False,
        "profiles": [],
        "crash_expected": False,
        "container_names": [],
        "external_networks": [],
        "health_budget_seconds": 0.0,
        "probe_tier_static": None,
        "config_error": None,
    }
    if cohort != COHORT_DOCKER:
        return record

    example = lab_dir / ".env.example"
    if example.is_file():
        record["env_file"] = str(example)

    try:
        config, stderr = compose_config(lab_dir, env_file=record["env_file"])
    except ComposeConfigError as exc:
        record["config_error"] = str(exc)
        return record

    services = services_info(config)
    record["services"] = sorted(services)
    record["required_env"] = parse_required_env(stderr)
    record["static_host_ports"] = sorted(
        {p for s in services.values() for p in s["static_ports"]}
    )
    record["dynamic_ports"] = any(s["dynamic_ports"] for s in services.values())
    record["healthcheck_services"] = sorted(
        n for n, s in services.items() if s["healthcheck"]
    )
    record["privileged"] = any(s["privileged"] for s in services.values())
    record["profiles"] = sorted({p for s in services.values() for p in s["profiles"]})
    record["crash_expected"] = any(s["crash_expected"] for s in services.values())
    # Networks the lab expects to already exist. CVE-2026-44182 requires a kind
    # cluster (its README lists kind + kubectl as requirements), so a missing
    # external network is an unmet prerequisite, not lab rot.
    record["external_networks"] = sorted(
        name for name, net in (config.get("networks") or {}).items()
        if (net or {}).get("external")
    )
    record["container_names"] = sorted(
        s["container_name"] for s in services.values() if s["container_name"]
    )
    record["health_budget_seconds"] = round(health_budget(services), 1)
    record["probe_tier_static"] = static_tier(services)
    return record


def collect(repo_root) -> list:
    """Inventory every CVE-* directory, sorted by name."""
    root = pathlib.Path(repo_root)
    return [build_lab(d) for d in sorted(root.glob("CVE-*")) if d.is_dir()]
