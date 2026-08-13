import json
import os
import pathlib
import subprocess
import tempfile
import unittest

import audit
import probes


class TestBuildFailureClassification(unittest.TestCase):
    def test_rate_limit_is_infra(self):
        log = "toomanyrequests: You have reached your pull rate limit"
        self.assertEqual(audit.classify_build_failure(log), "infra")
        self.assertTrue(audit.is_infra_failure(log))

    def test_dns_failure_is_infra(self):
        log = "Temporary failure in name resolution"
        self.assertEqual(audit.classify_build_failure(log), "infra")

    def test_disk_full_is_infra(self):
        self.assertEqual(
            audit.classify_build_failure("no space left on device"), "infra"
        )

    def test_missing_manifest_is_upstream_gone(self):
        log = "manifest for someimage:1.2.3 not found: manifest unknown"
        self.assertEqual(audit.classify_build_failure(log), "upstream_gone")
        self.assertFalse(audit.is_infra_failure(log))

    def test_apt_package_missing_is_package_gone(self):
        log = "E: Unable to locate package libfoo-dev"
        self.assertEqual(audit.classify_build_failure(log), "package_gone")

    def test_pip_resolution_is_dependency_gone(self):
        log = "ERROR: No matching distribution found for flask==0.1"
        self.assertEqual(audit.classify_build_failure(log), "dependency_gone")

    def test_upstream_503_is_infra_not_lab_defect(self):
        """CVE-2024-21626: GitHub returned 503 for a runc release asset."""
        log = "wget: server returned error: HTTP/1.1 503 Service Unavailable"
        self.assertEqual(audit.classify_build_failure(log), "infra")

    def test_containerd_commit_race_is_infra(self):
        """CVE-2026-0766 under a load spike of 29 on 12 cores."""
        log = ('failed to solve: failed commit on ref "layer-sha256:82ba": '
               "commit failed: rename /var/lib/containerd/... "
               "no such file or directory")
        self.assertEqual(audit.classify_build_failure(log), "infra")

    def test_missing_dockerfile_still_wins_over_generic_no_such_file(self):
        """Both messages contain 'no such file or directory'; don't confuse them."""
        log = ("target web: failed to solve: failed to read dockerfile: "
               "open Dockerfile: no such file or directory")
        self.assertEqual(audit.classify_build_failure(log), "missing_dockerfile")

    def test_missing_dockerfile_is_its_own_cause(self):
        """Real failure text from CVE-2026-66713 during the pilot."""
        log = ("target web: failed to solve: failed to read dockerfile: "
               "open Dockerfile: no such file or directory")
        self.assertEqual(audit.classify_build_failure(log), "missing_dockerfile")

    def test_unrecognised_is_generic_build_error(self):
        self.assertEqual(audit.classify_build_failure("syntax error"), "build_error")


class TestRunDir(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.run = audit.RunDir(pathlib.Path(self.tmp.name) / "run-1")

    def tearDown(self):
        self.tmp.cleanup()

    def test_creates_directory_structure(self):
        self.assertTrue(self.run.path.is_dir())
        self.assertTrue((self.run.path / "logs").is_dir())

    def test_append_result_writes_one_json_object_per_line(self):
        self.run.append_result({"cve": "CVE-1", "state": "PASS"})
        self.run.append_result({"cve": "CVE-2", "state": "FAIL_BUILD"})
        lines = (self.run.path / "results.jsonl").read_text().strip().splitlines()
        self.assertEqual(len(lines), 2)
        self.assertEqual(json.loads(lines[1])["cve"], "CVE-2")

    def test_completed_cves_supports_resume(self):
        self.run.append_result({"cve": "CVE-1", "state": "PASS"})
        self.assertEqual(self.run.completed_cves(), {"CVE-1"})

    def test_completed_cves_empty_before_any_results(self):
        self.assertEqual(self.run.completed_cves(), set())

    def test_log_path_is_namespaced_per_cve(self):
        p = self.run.log_path("CVE-2025-1", "build.log")
        self.assertTrue(p.parent.is_dir())
        self.assertTrue(str(p).endswith("logs/CVE-2025-1/build.log"))


class TestRepoShaDirtyHandling(unittest.TestCase):
    """A verification run of uncommitted repairs must never look like an audit
    of the commit it happens to sit on."""

    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.repo = pathlib.Path(self.tmp.name)
        for cmd in (
            ["git", "init", "-q"],
            ["git", "config", "user.email", "dev@exploit-intel.com"],
            ["git", "config", "user.name", "Exploit Intelligence Platform"],
        ):
            subprocess.run(cmd, cwd=self.repo, capture_output=True)
        (self.repo / "a.txt").write_text("one")
        subprocess.run(["git", "add", "-A"], cwd=self.repo, capture_output=True)
        subprocess.run(["git", "commit", "-qm", "init"], cwd=self.repo,
                       capture_output=True)

    def tearDown(self):
        self.tmp.cleanup()

    def test_clean_tree_returns_plain_sha(self):
        sha = audit.repo_sha(self.repo)
        self.assertTrue(sha)
        self.assertNotIn("-dirty", sha)

    def test_dirty_tree_raises_by_default(self):
        (self.repo / "a.txt").write_text("two")
        with self.assertRaises(audit.DirtyRepoError):
            audit.repo_sha(self.repo)

    def test_dirty_tree_allowed_explicitly_is_marked(self):
        (self.repo / "a.txt").write_text("two")
        self.assertTrue(audit.repo_sha(self.repo, allow_dirty=True).endswith("-dirty"))


class TestRunLock(unittest.TestCase):
    """Two runs sharing a run-id corrupt each other's results.

    It happened for real: a duplicate retry produced two records per CVE
    seconds apart, with container-name and network collisions between them.
    """

    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.path = pathlib.Path(self.tmp.name) / "run-1"

    def tearDown(self):
        self.tmp.cleanup()

    def test_second_live_process_is_refused(self):
        first = audit.RunDir(self.path)
        first.acquire()
        # Simulate a different, living process owning the lock.
        first.lock_file.write_text("1")  # pid 1 always exists
        second = audit.RunDir(self.path)
        with self.assertRaises(audit.ConcurrentRunError):
            second.acquire()

    def test_stale_lock_from_dead_process_is_reclaimed(self):
        run = audit.RunDir(self.path)
        run.lock_file.write_text("999999")  # not a live pid
        run.acquire()
        self.assertEqual(run.lock_file.read_text().strip(), str(os.getpid()))

    def test_release_removes_the_lock(self):
        run = audit.RunDir(self.path)
        run.acquire()
        run.release()
        self.assertFalse(run.lock_file.exists())

    def test_reacquiring_own_lock_is_allowed(self):
        run = audit.RunDir(self.path)
        run.acquire()
        run.acquire()
        self.assertTrue(run.lock_file.exists())


class TestEffectiveTier(unittest.TestCase):
    def _c(self, published):
        return probes.Container(
            service="web", name="n", state="running", health="",
            exit_code=0, published=published,
        )

    def test_t3_with_runtime_ports_upgrades_to_t2(self):
        """CVE-2026-66713: ports only exist once the lab is running."""
        self.assertEqual(audit.effective_tier("T3", [self._c([32768])]), "T2")

    def test_t3_without_ports_stays_t3(self):
        self.assertEqual(audit.effective_tier("T3", [self._c([])]), "T3")

    def test_t1_never_downgraded(self):
        self.assertEqual(audit.effective_tier("T1", [self._c([8080])]), "T1")

    def test_t2_unchanged(self):
        self.assertEqual(audit.effective_tier("T2", [self._c([8080])]), "T2")


class TestComposeInvocationUsesProfiles(unittest.TestCase):
    """The profile flag must reach every runtime compose call, not just config."""

    def test_compose_cmd_starts_with_profile_wildcard(self):
        lab = {"path": "/x", "env_file": None}
        self.assertEqual(
            audit.compose_cmd(lab, "up", "-d"),
            ["docker", "compose", "--profile", "*", "up", "-d"],
        )

    def test_compose_cmd_includes_env_file_when_present(self):
        lab = {"path": "/x", "env_file": "/x/.env.example"}
        self.assertEqual(
            audit.compose_cmd(lab, "ps"),
            ["docker", "compose", "--profile", "*",
             "--env-file", "/x/.env.example", "ps"],
        )


class TestSelect(unittest.TestCase):
    def _lab(self, cve, privileged=False):
        return {"cve": cve, "cohort": "docker_lab", "privileged": privileged}

    def _args(self, **kw):
        defaults = {"only": [], "limit": 0}
        defaults.update(kw)
        return type("Args", (), defaults)()

    def test_privileged_labs_are_ordered_last(self):
        labs = [
            self._lab("CVE-A", privileged=True),
            self._lab("CVE-B"),
            self._lab("CVE-C"),
        ]
        chosen = [l["cve"] for l in audit._select(labs, self._args())]
        self.assertEqual(chosen, ["CVE-B", "CVE-C", "CVE-A"])

    def test_non_docker_cohorts_excluded(self):
        labs = [self._lab("CVE-A"), {"cve": "CVE-Z", "cohort": "no_lab",
                                     "privileged": False}]
        chosen = [l["cve"] for l in audit._select(labs, self._args())]
        self.assertEqual(chosen, ["CVE-A"])

    def test_only_filter_restricts_selection(self):
        labs = [self._lab("CVE-A"), self._lab("CVE-B")]
        chosen = [l["cve"] for l in audit._select(labs, self._args(only=["CVE-B"]))]
        self.assertEqual(chosen, ["CVE-B"])


if __name__ == "__main__":
    unittest.main()


class TestSharding(unittest.TestCase):
    """CI splits the labs across runners; every lab must land in exactly one
    shard, and shards should interleave so one does not inherit all the slow
    builds."""

    def _labs(self, n):
        return [{"cve": f"CVE-{i:04d}", "cohort": "docker_lab", "privileged": False}
                for i in range(n)]

    def _args(self, **kw):
        defaults = {"only": [], "limit": 0, "shards": 1, "shard": 0,
                    "skip_privileged": False}
        defaults.update(kw)
        return type("Args", (), defaults)()

    def test_shards_partition_every_lab_exactly_once(self):
        labs = self._labs(23)
        seen = []
        for i in range(4):
            seen += [l["cve"] for l in audit._select(labs, self._args(shards=4, shard=i))]
        self.assertEqual(sorted(seen), sorted(l["cve"] for l in labs))
        self.assertEqual(len(seen), len(set(seen)))

    def test_shards_interleave_rather_than_block(self):
        labs = self._labs(8)
        first = [l["cve"] for l in audit._select(labs, self._args(shards=2, shard=0))]
        self.assertEqual(first, ["CVE-0000", "CVE-0002", "CVE-0004", "CVE-0006"])

    def test_single_shard_returns_everything(self):
        labs = self._labs(5)
        self.assertEqual(len(audit._select(labs, self._args())), 5)

    def test_skip_privileged_excludes_them(self):
        labs = self._labs(3)
        labs.append({"cve": "CVE-PRIV", "cohort": "docker_lab", "privileged": True})
        chosen = [l["cve"] for l in audit._select(labs, self._args(skip_privileged=True))]
        self.assertNotIn("CVE-PRIV", chosen)
        self.assertEqual(len(chosen), 3)


class TestDiskDefaults(unittest.TestCase):
    """A large headroom default fires on every ordinary disk, pruning the
    images just built and turning warm re-runs into cold rebuilds."""

    def test_headroom_default_is_modest(self):
        self.assertLessEqual(audit.DISK_HEADROOM_GB, 50)

    def test_headroom_is_below_the_abort_floor(self):
        # Pruning must engage before the run aborts, never after.
        self.assertLess(audit.DISK_HEADROOM_GB, audit.DISK_FLOOR_GB)


class TestRunsDirDefaultIsOutsideRepo(unittest.TestCase):
    """Writing run output into the audited repo dirties the tree, and the
    dirty-tree guard then refuses every subsequent run."""

    def test_runs_dir_is_not_inside_the_audited_repo(self):
        import pathlib as _p
        repo = _p.Path(audit.REPO_DEFAULT).resolve()
        runs = _p.Path(audit.RUNS_DIR).resolve()
        self.assertFalse(
            runs.is_relative_to(repo),
            f"RUNS_DIR {runs} must not live inside the audited repo {repo}: "
            "run output would dirty the tree and the dirty-tree guard would "
            "then refuse every subsequent run",
        )
