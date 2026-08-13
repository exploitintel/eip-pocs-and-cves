"""Verdict logic for ci_summary, especially the coverage guard.

Written as real TestCases rather than a script: anything living in a directory
that `unittest discover` scans gets imported, so module-level work and a
module-level sys.exit() would hijack the whole suite.
"""
import json
import pathlib
import subprocess
import sys
import tempfile
import unittest

HERE = pathlib.Path(__file__).resolve().parent
# The script sits beside this test in the scratch copy and one level up once
# vendored into .github/audit/tests/.
SCRIPT = next(
    c for c in (HERE / "ci_summary.py", HERE.parent / "ci_summary.py") if c.exists()
)


def run_summary(labs, records):
    """Run ci_summary over a synthetic repo and return (returncode, stdout)."""
    with tempfile.TemporaryDirectory() as tmp:
        repo = pathlib.Path(tmp) / "repo"
        repo.mkdir()
        for lab in labs:
            (repo / lab).mkdir()
            (repo / lab / "docker-compose.yml").write_text("services: {}\n")
        results = pathlib.Path(tmp) / "results.jsonl"
        results.write_text("\n".join(json.dumps(r) for r in records) + "\n")
        proc = subprocess.run(
            [sys.executable, str(SCRIPT), str(results), "--repo", str(repo)],
            capture_output=True, text=True,
        )
        return proc.returncode, proc.stdout


class TestVerdict(unittest.TestCase):
    def test_all_healthy_passes(self):
        rc, _ = run_summary(
            ["CVE-1", "CVE-2"],
            [{"cve": "CVE-1", "state": "PASS"}, {"cve": "CVE-2", "state": "PASS"}],
        )
        self.assertEqual(rc, 0)

    def test_a_broken_lab_fails_the_run(self):
        rc, out = run_summary(
            ["CVE-1", "CVE-2"],
            [{"cve": "CVE-1", "state": "PASS"},
             {"cve": "CVE-2", "state": "FAIL_BUILD", "reason": "missing_dockerfile"}],
        )
        self.assertEqual(rc, 1)
        self.assertIn("CVE-2", out)

    def test_a_lab_that_never_reported_fails_the_run(self):
        """A shard dying before it writes an artifact must not pass silently."""
        rc, out = run_summary(
            ["CVE-1", "CVE-2", "CVE-3"],
            [{"cve": "CVE-1", "state": "PASS"}, {"cve": "CVE-2", "state": "PASS"}],
        )
        self.assertEqual(rc, 1)
        self.assertIn("Never reported", out)
        self.assertIn("CVE-3", out)

    def test_prerequisite_and_crash_by_design_do_not_fail(self):
        rc, _ = run_summary(
            ["CVE-1", "CVE-2", "CVE-3"],
            [{"cve": "CVE-1", "state": "PASS"},
             {"cve": "CVE-2", "state": "FAIL_PREREQ", "reason": "needs kind"},
             {"cve": "CVE-3", "state": "CRASH_EXPECTED", "reason": "sanitizer abort"}],
        )
        self.assertEqual(rc, 0)

    def test_skipped_privileged_labs_are_reported_and_do_not_fail(self):
        rc, out = run_summary(
            ["CVE-1", "CVE-2"],
            [{"cve": "CVE-1", "state": "PASS"},
             {"cve": "CVE-2", "state": "SKIPPED", "reason": "privileged"}],
        )
        self.assertEqual(rc, 0)
        self.assertIn("CVE-2", out)

    def test_duplicate_records_across_shards_collapse(self):
        rc, _ = run_summary(
            ["CVE-1"],
            [{"cve": "CVE-1", "state": "FAIL_UP"}, {"cve": "CVE-1", "state": "PASS"}],
        )
        self.assertEqual(rc, 0)

    def test_empty_results_fail(self):
        with tempfile.TemporaryDirectory() as tmp:
            empty = pathlib.Path(tmp) / "results.jsonl"
            empty.write_text("")
            proc = subprocess.run(
                [sys.executable, str(SCRIPT), str(empty), "--repo", tmp],
                capture_output=True, text=True,
            )
            self.assertEqual(proc.returncode, 1)


if __name__ == "__main__":
    unittest.main()
