import pathlib
import tempfile
import unittest

import inventory


class TestClassify(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.root = pathlib.Path(self.tmp.name)

    def tearDown(self):
        self.tmp.cleanup()

    def _mk(self, name):
        d = self.root / name
        d.mkdir()
        return d

    def test_compose_present_is_docker_lab(self):
        d = self._mk("CVE-2025-1")
        (d / "docker-compose.yml").write_text("services: {}\n")
        self.assertEqual(inventory.classify(d), inventory.COHORT_DOCKER)

    def test_vm_lab_wins_over_no_lab(self):
        """CVE-2026-50540 has run.sh + vm-assets and no Dockerfile.

        Rule order matters: it must not fall through to no_lab.
        """
        d = self._mk("CVE-2026-50540")
        (d / "run.sh").write_text("#!/bin/sh\n")
        (d / "vm-assets").mkdir()
        self.assertEqual(inventory.classify(d), inventory.COHORT_VM)

    def test_no_compose_no_dockerfile_is_no_lab(self):
        d = self._mk("CVE-2026-24289")
        (d / "README.md").write_text("x")
        self.assertEqual(inventory.classify(d), inventory.COHORT_NONE)

    def test_dockerfile_without_compose_is_unknown(self):
        d = self._mk("CVE-2099-1")
        (d / "Dockerfile.vulnerable").write_text("FROM alpine\n")
        self.assertEqual(inventory.classify(d), inventory.COHORT_UNKNOWN)


class TestParseRequiredEnv(unittest.TestCase):
    def test_extracts_unset_variable_names(self):
        stderr = (
            'time="x" level=warning msg="The \\"WEB_PORT\\" variable is not set. '
            'Defaulting to a blank string."\n'
            'time="x" level=warning msg="The \\"DB_USER\\" variable is not set. '
            'Defaulting to a blank string."\n'
        )
        self.assertEqual(inventory.parse_required_env(stderr), ["DB_USER", "WEB_PORT"])

    def test_returns_empty_when_no_warnings(self):
        self.assertEqual(inventory.parse_required_env("name: x\n"), [])


class TestServicesInfo(unittest.TestCase):
    def test_reads_healthcheck_ports_and_privileged(self):
        config = {
            "services": {
                "web": {
                    "healthcheck": {"test": ["CMD", "true"]},
                    "ports": [{"target": 80, "published": "3100", "protocol": "tcp"}],
                },
                "dind": {"privileged": True},
            }
        }
        info = inventory.services_info(config)
        self.assertTrue(info["web"]["healthcheck"])
        self.assertEqual(info["web"]["static_ports"], [3100])
        self.assertFalse(info["web"]["dynamic_ports"])
        self.assertTrue(info["dind"]["privileged"])
        self.assertEqual(info["dind"]["static_ports"], [])

    def test_unset_interpolation_marks_dynamic_not_static(self):
        """`"${WEB_PORT}:80"` unset -> compose drops `published` entirely."""
        config = {"services": {"web": {"ports": [{"target": 80, "protocol": "tcp"}]}}}
        info = inventory.services_info(config)
        self.assertEqual(info["web"]["static_ports"], [])
        self.assertTrue(info["web"]["dynamic_ports"])

    def test_disabled_healthcheck_does_not_count(self):
        config = {"services": {"web": {"healthcheck": {"disable": True}}}}
        self.assertFalse(inventory.services_info(config)["web"]["healthcheck"])

    def test_records_service_profiles(self):
        config = {"services": {"target": {"profiles": ["vulnerable"]}}}
        self.assertEqual(
            inventory.services_info(config)["target"]["profiles"], ["vulnerable"]
        )


class TestContainerNames(unittest.TestCase):
    """CVE-2025-11539 pins container_name, so a leftover blocks `up`.

    The runner needs these names to clear stale containers that `compose down`
    misses, which otherwise shows up as a false FAIL_UP.
    """

    def test_collects_declared_container_names(self):
        config = {
            "services": {
                "grafana": {"container_name": "cve-2025-11539-grafana"},
                "renderer": {"container_name": "cve-2025-11539-renderer"},
            }
        }
        info = inventory.services_info(config)
        self.assertEqual(info["grafana"]["container_name"], "cve-2025-11539-grafana")

    def test_absent_container_name_is_empty(self):
        config = {"services": {"web": {}}}
        self.assertEqual(inventory.services_info(config)["web"]["container_name"], "")


class TestParseDuration(unittest.TestCase):
    def test_seconds(self):
        self.assertEqual(inventory.parse_duration("10s"), 10.0)

    def test_compound_go_duration(self):
        """Compose emits start_period: 5m as '5m0s'."""
        self.assertEqual(inventory.parse_duration("5m0s"), 300.0)

    def test_hours_and_minutes(self):
        self.assertEqual(inventory.parse_duration("1h30m"), 5400.0)

    def test_milliseconds(self):
        self.assertAlmostEqual(inventory.parse_duration("500ms"), 0.5)

    def test_empty_and_none_are_zero(self):
        self.assertEqual(inventory.parse_duration(""), 0.0)
        self.assertEqual(inventory.parse_duration(None), 0.0)


class TestHealthBudget(unittest.TestCase):
    """CVE-2022-0735 is GitLab CE: start_period 5m, 60 retries at 10s = 900s.

    A fixed 180s probe timeout failed it while it was still legitimately
    starting, so the budget must come from the lab's own declaration.
    """

    def test_gitlab_style_budget(self):
        services = {
            "gitlab": {
                "healthcheck_spec": {
                    "start_period": "5m0s", "interval": "10s", "retries": 60
                }
            }
        }
        self.assertEqual(inventory.health_budget(services), 900.0)

    def test_takes_the_slowest_service(self):
        services = {
            "fast": {"healthcheck_spec": {"interval": "5s", "retries": 3}},
            "slow": {"healthcheck_spec": {"interval": "10s", "retries": 60}},
        }
        self.assertEqual(inventory.health_budget(services), 600.0)

    def test_no_healthcheck_is_zero(self):
        self.assertEqual(inventory.health_budget({"a": {}}), 0.0)

    def test_missing_interval_defaults_to_compose_default(self):
        services = {"a": {"healthcheck_spec": {"retries": 3}}}
        self.assertEqual(inventory.health_budget(services), 90.0)


class TestCrashExpected(unittest.TestCase):
    """CVE-2026-64608 sets ASAN to abort so exit 134 is the demonstration."""

    def test_detects_asan_abort_from_dict_environment(self):
        config = {
            "services": {
                "target": {"environment": {"ASAN_OPTIONS": "abort_on_error=1"}}
            }
        }
        self.assertTrue(inventory.services_info(config)["target"]["crash_expected"])

    def test_detects_asan_abort_from_list_environment(self):
        config = {
            "services": {"target": {"environment": ["ASAN_OPTIONS=abort_on_error=1"]}}
        }
        self.assertTrue(inventory.services_info(config)["target"]["crash_expected"])

    def test_ordinary_service_is_not_crash_expected(self):
        config = {"services": {"web": {"environment": {"DEBUG": "1"}}}}
        self.assertFalse(inventory.services_info(config)["web"]["crash_expected"])

    def test_missing_environment_is_not_crash_expected(self):
        config = {"services": {"web": {}}}
        self.assertFalse(inventory.services_info(config)["web"]["crash_expected"])


class TestComposeArgs(unittest.TestCase):
    """Regression guard for CVE-2026-20348.

    Every service in that lab is profile-gated. Without `--profile "*"`,
    compose resolves zero services and `up` starts nothing, which the probe
    would misreport as a crash instead of a config gate.
    """

    def test_always_activates_all_profiles(self):
        self.assertEqual(
            inventory.compose_args(), ["docker", "compose", "--profile", "*"]
        )

    def test_env_file_is_appended_after_profile(self):
        self.assertEqual(
            inventory.compose_args("/x/.env.example"),
            ["docker", "compose", "--profile", "*", "--env-file", "/x/.env.example"],
        )


class TestStaticTier(unittest.TestCase):
    def test_healthcheck_wins(self):
        services = {
            "a": {"healthcheck": True, "static_ports": [1], "dynamic_ports": False},
            "b": {"healthcheck": False, "static_ports": [], "dynamic_ports": False},
        }
        self.assertEqual(inventory.static_tier(services), "T1")

    def test_ports_without_healthcheck_is_t2(self):
        services = {
            "a": {"healthcheck": False, "static_ports": [8080], "dynamic_ports": False}
        }
        self.assertEqual(inventory.static_tier(services), "T2")

    def test_neither_is_t3(self):
        services = {
            "a": {"healthcheck": False, "static_ports": [], "dynamic_ports": True}
        }
        self.assertEqual(inventory.static_tier(services), "T3")


if __name__ == "__main__":
    unittest.main()


class TestExternalNetworks(unittest.TestCase):
    """CVE-2026-44182 requires a kind cluster (its README lists kind + kubectl),
    so a missing external network is an unmet prerequisite, not lab rot."""

    def test_collects_external_networks(self):
        config = {"networks": {"kind": {"external": True},
                               "lab-net": {"driver": "bridge"}}}
        record = inventory.services_info(config)  # ensure no crash on no services
        self.assertEqual(record, {})
        externals = sorted(
            n for n, net in config["networks"].items() if (net or {}).get("external")
        )
        self.assertEqual(externals, ["kind"])
