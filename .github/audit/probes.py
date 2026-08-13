"""Verdict logic — pure functions over normalized container records.

The caller performs all Docker I/O and passes results in. `PENDING` means the
lab has not failed but has not yet satisfied its tier; the caller keeps polling
until the probe timeout, then records FAIL_UNHEALTHY.
"""

from __future__ import annotations

import json
import socket
from dataclasses import dataclass, field

PASS = "PASS"
PENDING = "PENDING"
FAIL_UP = "FAIL_UP"

# Runtime evidence that a container died inside a sanitizer rather than from
# ordinary breakage. Required *in addition to* the lab declaring crash-abort
# semantics before a non-zero exit is treated as intended behaviour.
SANITIZER_MARKERS = (
    "addresssanitizer",
    "undefinedbehaviorsanitizer",
    "memorysanitizer",
    "leaksanitizer",
    "runtime error:",
    "==aborting",
)


def looks_like_sanitizer_abort(log: str) -> bool:
    """True when container output carries a sanitizer crash signature."""
    text = (log or "").lower()
    return any(marker in text for marker in SANITIZER_MARKERS)


@dataclass
class Container:
    service: str
    name: str
    state: str
    health: str
    exit_code: int
    published: list = field(default_factory=list)


def parse_ps(raw: str) -> list:
    """Parse `docker compose ps -a --format json`.

    Compose 5.4.0 on the runner emits NDJSON (verified). A JSON array is also
    accepted so the harness survives a compose upgrade.
    """
    raw = (raw or "").strip()
    if not raw:
        return []
    if raw.startswith("["):
        objects = json.loads(raw)
    else:
        objects = [json.loads(line) for line in raw.splitlines() if line.strip()]

    containers = []
    for obj in objects:
        ports = {
            int(p["PublishedPort"])
            for p in (obj.get("Publishers") or [])
            if p.get("PublishedPort")
        }
        containers.append(
            Container(
                service=obj.get("Service", ""),
                name=obj.get("Name") or obj.get("Names", ""),
                state=(obj.get("State") or "").lower(),
                health=(obj.get("Health") or "").lower(),
                exit_code=int(obj.get("ExitCode") or 0),
                published=sorted(ports),
            )
        )
    return containers


def container_ok(c: Container):
    """Running is fine. Exited-0 is a completed one-shot, also fine."""
    if c.state == "running":
        return True, None
    if c.state == "exited" and c.exit_code == 0:
        return True, None
    return False, f"{c.service}: state={c.state} exit={c.exit_code}"


def _crashed(containers):
    return [why for c in containers for ok, why in [container_ok(c)] if not ok]


def runtime_ports(containers) -> list:
    return sorted({p for c in containers for p in c.published})


def verdict_t1(containers, healthcheck_services):
    if not containers:
        return FAIL_UP, "no containers created"
    crashed = _crashed(containers)
    if crashed:
        return FAIL_UP, "; ".join(crashed)
    wanted = set(healthcheck_services or [])
    pending = [
        f"{c.service}: health={c.health or 'none'}"
        for c in containers
        if c.service in wanted and c.state == "running" and c.health != "healthy"
    ]
    if pending:
        return PENDING, "; ".join(pending)
    return PASS, f"{len(containers)} container(s) up, healthchecks satisfied"


def verdict_t2(containers, probe_results):
    if not containers:
        return FAIL_UP, "no containers created"
    crashed = _crashed(containers)
    if crashed:
        return FAIL_UP, "; ".join(crashed)
    if not probe_results:
        return PENDING, "no published ports observed yet"
    dead = sorted(port for port, ok in probe_results.items() if not ok)
    if dead:
        return PENDING, f"ports not answering: {dead}"
    return PASS, f"all {len(probe_results)} published port(s) answering"


def verdict_t3(containers, stable_seconds, required_stable=30):
    if not containers:
        return FAIL_UP, "no containers created"
    crashed = _crashed(containers)
    if crashed:
        return FAIL_UP, "; ".join(crashed)
    if stable_seconds < required_stable:
        return PENDING, f"stable {stable_seconds}s of {required_stable}s"
    return PASS, f"{len(containers)} container(s) stable for {required_stable}s"


def tcp_probe(host: str, port: int, timeout: float = 3.0) -> bool:
    try:
        with socket.create_connection((host, port), timeout=timeout):
            return True
    except OSError:
        return False
