#!/usr/bin/env python3
"""Keep repository housekeeping ignores out of published CVE packages."""

from pathlib import Path
import subprocess
import sys


ROOT = Path(__file__).resolve().parents[2]
ROOT_FILE_GLOBS = {"*.pyc", "*.log", "*.pid"}


def violations(contents: str) -> list[str]:
    found = []
    for number, line in enumerate(contents.splitlines(), start=1):
        if not line or line.startswith("#"):
            continue
        if line != line.strip():
            found.append(f"line {number}: whitespace changes Git ignore semantics")
            continue
        if line.startswith("!"):
            found.append(f"line {number}: package paths must not need ignore exceptions")
            continue
        if not line.startswith("/") or "**" in line or "\\" in line:
            found.append(f"line {number}: ignore rules must be anchored to the repository root")
            continue
        first = line[1:].split("/", 1)[0]
        if not first:
            found.append(f"line {number}: missing root path")
        elif first.upper().startswith("CVE-") or (
            any(ch in first for ch in "*?[")
            and ("/" in line[1:] or first not in ROOT_FILE_GLOBS)
        ):
            found.append(f"line {number}: ignore rule can cover a CVE package")
    return found


def runtime_artifacts(paths: list[str]) -> list[str]:
    found = []
    for name in paths:
        parts = name.split("/")
        if len(parts) < 2 or not parts[0].startswith("CVE-"):
            continue
        if (
            "poc-run-logs" in parts[1:-1]
            or "__pycache__" in parts[1:-1]
            or parts[-1] in {".DS_Store", "reg-page-id.txt"}
            or parts[-1].endswith((".pid", ".pyc"))
        ):
            found.append(name)
    return found


def self_test() -> None:
    assert violations("/docs/\n/source/\n/data/\n/poc-run-logs/\n/*.log\n/.env\n") == []
    for rule in (
        "docs/", "**/source/", "**/data/", "poc-run-logs/", "*.log", "**/.env",
        "/CVE-*/source/",
        "/CVE-2026-76183/", "/*/logs/", "/*",
    ):
        assert violations(rule + "\n"), rule
    assert runtime_artifacts(["CVE-2099-00001/poc-run-logs/run.txt", "CVE-2099-00001/.DS_Store"])
    assert runtime_artifacts(["CVE-2099-00001/data/fixture.bin", "CVE-2099-00001/evidence/run.log"]) == []


if __name__ == "__main__":
    if sys.argv[1:] == ["--self-test"]:
        self_test()
        print("PASS: ignore-policy negative controls")
    elif len(sys.argv) == 1:
        errors = violations((ROOT / ".gitignore").read_text(encoding="utf-8"))
        tree = subprocess.run(
            ["git", "ls-tree", "-r", "--name-only", "-z", "HEAD"],
            cwd=ROOT, capture_output=True, check=True,
        ).stdout
        tracked = [name.decode("utf-8", "replace") for name in tree.split(b"\0") if name]
        artifacts = runtime_artifacts(tracked)
        if errors:
            print("FAIL: repository .gitignore can affect CVE packages:")
            for error in errors:
                print(error)
        if artifacts:
            print("FAIL: tracked package contains generated runtime artifacts:")
            for name in artifacts[:20]:
                print(name)
            if len(artifacts) > 20:
                print(f"... and {len(artifacts) - 20} more")
        if errors or artifacts:
            raise SystemExit(1)
        print("PASS: repository ignore scope and package runtime artifacts")
    else:
        raise SystemExit("usage: check_package_ignore.py [--self-test]")
