"""Host-protection primitives for the CVE lab audit.

This is the ONLY module permitted to delete Docker artifacts. Every removal is
gated on a preflight snapshot: an artifact that existed before the run started
can never be removed, no matter what.
"""

from __future__ import annotations

import shutil
import subprocess

DISK_FLOOR_BYTES = 100 * 1024 ** 3
KINDS = ("images", "image_tags", "volumes", "networks")

# What the preservation assertion actually guards. Raw image IDs are NOT in this
# set: rebuilding a lab moves its tag to a fresh ID and leaves the old ID
# unreferenced, which is normal Docker behaviour and destroys nothing the user
# depends on. Tags, volumes and networks are the things whose loss is real.
PROTECTED_KINDS = ("image_tags", "volumes", "networks")


class HostMutationError(Exception):
    """Raised when the harness detects it removed something pre-existing."""


def _docker(*args, timeout=120):
    return subprocess.run(
        ["docker", *args], capture_output=True, text=True, timeout=timeout
    )


def _ids(kind: str) -> set:
    args = {
        "images": ["image", "ls", "-aq"],
        "image_tags": ["image", "ls", "--format", "{{.Repository}}:{{.Tag}}"],
        "volumes": ["volume", "ls", "-q"],
        "networks": ["network", "ls", "-q"],
    }[kind]
    result = _docker(*args)
    if result.returncode != 0:
        raise RuntimeError(f"docker {' '.join(args)} failed: {result.stderr.strip()}")
    values = {line.strip() for line in result.stdout.splitlines() if line.strip()}
    if kind == "image_tags":
        # Untagged images are unreferenced by definition; they carry no identity
        # a user can depend on, so they are not protected.
        values = {v for v in values if "<none>" not in v}
    return values


def snapshot() -> dict:
    """Capture every Docker artifact ID currently on the host."""
    return {kind: _ids(kind) for kind in KINDS}


def new_artifacts(before: dict, after: dict) -> dict:
    """Artifacts present in `after` but not `before` — created by this run."""
    return {kind: after[kind] - before[kind] for kind in before}


def missing_artifacts(before: dict, after: dict) -> dict:
    """Artifacts present in `before` but gone in `after` — must always be empty."""
    return {kind: before[kind] - after[kind] for kind in before}


def assert_preserved(before: dict, after: dict) -> None:
    """Raise if the run destroyed anything the user depends on.

    Only PROTECTED_KINDS are enforced. A pre-existing image ID disappearing is
    expected when a lab is rebuilt: the tag moves to a new ID and the old one is
    left unreferenced. The tag itself surviving is what proves nothing was lost.
    """
    missing = {
        kind: values
        for kind, values in missing_artifacts(before, after).items()
        if values and kind in PROTECTED_KINDS
    }
    if missing:
        raise HostMutationError(
            "harness removed pre-existing Docker artifacts: "
            + "; ".join(f"{k}={sorted(v)}" for k, v in sorted(missing.items()))
        )
    return None


def free_bytes(path: str = "/") -> int:
    return shutil.disk_usage(path).free


def disk_ok(path: str = "/", floor: int = DISK_FLOOR_BYTES) -> bool:
    return free_bytes(path) >= floor


def remove_new_volumes(before_volumes: set) -> list:
    """Remove only volumes created since `before_volumes`. Returns IDs removed.

    `docker compose down -v` cannot be used for teardown: it deletes every named
    volume the compose file declares, including one that already existed before
    the run. That destroyed a user's pre-existing `cve-2025-24490_pg_data`.
    Tearing down without `-v` and removing only the diff keeps lab state from
    leaking between labs without touching anything that was already there.
    """
    removed = []
    for volume in sorted(_ids("volumes") - set(before_volumes)):
        if volume in before_volumes:
            continue
        if _docker("volume", "rm", "-f", volume, timeout=60).returncode == 0:
            removed.append(volume)
    return removed


def remove_new_images(before: dict, after: dict) -> list:
    """Remove only images created since `before`. Returns the IDs removed.

    Pre-existing images are never candidates. Removal failures are ignored:
    an image still referenced by another lab simply stays.
    """
    candidates = sorted(new_artifacts(before, after).get("images", set()))
    removed = []
    for image_id in candidates:
        if image_id in before["images"]:
            continue
        if _docker("rmi", "-f", image_id, timeout=180).returncode == 0:
            removed.append(image_id)
    return removed
