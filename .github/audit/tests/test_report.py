import json
import pathlib
import tempfile
import unittest

import report

RECORDS = [
    {"cve": "CVE-1", "state": "PASS", "cohort": "docker_lab",
     "probe_tier_effective": "T1", "reason": "ok", "required_env": [],
     "runtime_ports": [8080], "build_seconds": 12.0},
    {"cve": "CVE-2", "state": "FAIL_BUILD", "cohort": "docker_lab",
     "build_cause": "upstream_gone", "reason": "build failed: upstream_gone",
     "required_env": [], "probe_tier_effective": "T1", "build_seconds": 4.0},
    {"cve": "CVE-3", "state": "NO_LAB", "cohort": "no_lab",
     "reason": "not a docker lab"},
    {"cve": "CVE-4", "state": "PASS", "cohort": "docker_lab",
     "probe_tier_effective": "T2", "reason": "ok", "required_env": ["WEB_PORT"],
     "env_file_used": False, "runtime_ports": [32768], "build_seconds": 30.0},
]


class TestSummarise(unittest.TestCase):
    def test_counts_by_state(self):
        self.assertEqual(
            report.summarise(RECORDS),
            {"PASS": 2, "FAIL_BUILD": 1, "NO_LAB": 1},
        )


class TestAdvisories(unittest.TestCase):
    def test_flags_lab_that_passed_but_needs_undocumented_env(self):
        found = report.advisories(RECORDS)
        self.assertTrue(any("CVE-4" in a and "WEB_PORT" in a for a in found))

    def test_no_advisory_for_clean_lab(self):
        self.assertFalse(any("CVE-1" in a for a in report.advisories(RECORDS)))


class TestRender(unittest.TestCase):
    def test_report_contains_totals_and_each_cve(self):
        text = report.render(RECORDS, {"repo_sha": "abc1234"})
        self.assertIn("abc1234", text)
        for record in RECORDS:
            self.assertIn(record["cve"], text)

    def test_failures_grouped_by_cause(self):
        text = report.render(RECORDS, {"repo_sha": "abc1234"})
        self.assertIn("upstream_gone", text)

    def test_counts_exclude_non_lab_cohorts_from_tested(self):
        text = report.render(RECORDS, {"repo_sha": "abc1234"})
        self.assertIn("Labs tested: **3**", text)
        self.assertIn("Directories recorded: **4**", text)


class TestMerge(unittest.TestCase):
    """A retry supersedes the original verdict for the CVEs it re-tested."""

    def test_later_run_wins_per_cve(self):
        original = [{"cve": "CVE-1", "state": "TIMEOUT"},
                    {"cve": "CVE-2", "state": "PASS"}]
        retry = [{"cve": "CVE-1", "state": "PASS"}]
        merged = report.merge([original, retry])
        by_cve = {r["cve"]: r["state"] for r in merged}
        self.assertEqual(by_cve, {"CVE-1": "PASS", "CVE-2": "PASS"})

    def test_no_double_counting(self):
        merged = report.merge([
            [{"cve": "CVE-1", "state": "FAIL_UP"}],
            [{"cve": "CVE-1", "state": "PASS"}],
        ])
        self.assertEqual(len(merged), 1)

    def test_duplicate_records_within_one_run_collapse(self):
        """A concurrent-run bug once wrote two records per CVE."""
        merged = report.merge([[
            {"cve": "CVE-1", "state": "FAIL_UP"},
            {"cve": "CVE-1", "state": "PASS"},
        ]])
        self.assertEqual(len(merged), 1)
        self.assertEqual(merged[0]["state"], "PASS")

    def test_output_is_sorted_by_cve(self):
        merged = report.merge([[{"cve": "CVE-9", "state": "PASS"},
                                {"cve": "CVE-1", "state": "PASS"}]])
        self.assertEqual([r["cve"] for r in merged], ["CVE-1", "CVE-9"])


class TestLoad(unittest.TestCase):
    def test_reads_jsonl(self):
        with tempfile.TemporaryDirectory() as tmp:
            path = pathlib.Path(tmp) / "results.jsonl"
            path.write_text("\n".join(json.dumps(r) for r in RECORDS) + "\n")
            self.assertEqual(len(report.load(path)), 4)


if __name__ == "__main__":
    unittest.main()


class TestPrereqNotCountedAsFailure(unittest.TestCase):
    def test_fail_prereq_is_excluded_from_tested_and_failed(self):
        records = [
            {"cve": "CVE-A", "state": "PASS"},
            {"cve": "CVE-B", "state": "FAIL_PREREQ",
             "reason": "network kind declared as external"},
        ]
        text = report.render(records, {"repo_sha": "x"})
        self.assertIn("Labs tested: **1**", text)
        self.assertIn("Failed: **0**", text)
