import unittest

import probes

# Captured verbatim from `docker compose ps -a --format json` on eip.local,
# Compose 5.4.0. Note: NDJSON, and Publishers lists each port twice (v4 + v6).
REAL_NDJSON = (
    '{"Command":"\\"sh -c \'exit 0\'\\"","ExitCode":0,"Health":"",'
    '"ID":"e1750364bfe9","Name":"schema-check-oneshot-1","Publishers":[],'
    '"Service":"oneshot","State":"exited","Status":"Exited (0) 12 seconds ago"}\n'
    '{"Command":"\\"sh -c \'while true;\\"","ExitCode":0,"Health":"healthy",'
    '"ID":"60503fe0f02d","Name":"schema-check-web-1","Publishers":['
    '{"URL":"0.0.0.0","TargetPort":8099,"PublishedPort":18099,"Protocol":"tcp"},'
    '{"URL":"::","TargetPort":8099,"PublishedPort":18099,"Protocol":"tcp"}],'
    '"Service":"web","State":"running","Status":"Up 12 seconds (healthy)"}\n'
)


def make(service, state="running", health="", exit_code=0, published=None):
    return probes.Container(
        service=service,
        name=f"proj-{service}-1",
        state=state,
        health=health,
        exit_code=exit_code,
        published=published or [],
    )


class TestParsePs(unittest.TestCase):
    def test_parses_real_ndjson_output(self):
        containers = probes.parse_ps(REAL_NDJSON)
        self.assertEqual(len(containers), 2)
        by_service = {c.service: c for c in containers}
        self.assertEqual(by_service["web"].state, "running")
        self.assertEqual(by_service["web"].health, "healthy")
        self.assertEqual(by_service["oneshot"].state, "exited")
        self.assertEqual(by_service["oneshot"].exit_code, 0)

    def test_dedupes_ipv4_and_ipv6_publishers(self):
        containers = probes.parse_ps(REAL_NDJSON)
        web = next(c for c in containers if c.service == "web")
        self.assertEqual(web.published, [18099])

    def test_accepts_json_array_form(self):
        containers = probes.parse_ps(
            '[{"Service":"a","State":"running","Health":"","ExitCode":0,'
            '"Name":"x","Publishers":[]}]'
        )
        self.assertEqual(len(containers), 1)

    def test_empty_output_is_empty_list(self):
        self.assertEqual(probes.parse_ps("  \n"), [])

    def test_ignores_published_port_zero(self):
        containers = probes.parse_ps(
            '{"Service":"a","State":"running","Health":"","ExitCode":0,"Name":"x",'
            '"Publishers":[{"PublishedPort":0,"TargetPort":80,"Protocol":"tcp"}]}'
        )
        self.assertEqual(containers[0].published, [])


class TestContainerOk(unittest.TestCase):
    def test_running_is_ok(self):
        self.assertEqual(probes.container_ok(make("a")), (True, None))

    def test_exited_zero_is_completed_oneshot(self):
        ok, _ = probes.container_ok(make("a", state="exited", exit_code=0))
        self.assertTrue(ok)

    def test_exited_nonzero_fails(self):
        ok, why = probes.container_ok(make("a", state="exited", exit_code=1))
        self.assertFalse(ok)
        self.assertIn("exit=1", why)

    def test_restarting_fails(self):
        ok, why = probes.container_ok(make("a", state="restarting"))
        self.assertFalse(ok)
        self.assertIn("restarting", why)


class TestVerdictT1(unittest.TestCase):
    def test_all_healthy_passes(self):
        containers = [make("web", health="healthy")]
        self.assertEqual(probes.verdict_t1(containers, ["web"])[0], "PASS")

    def test_starting_is_pending_not_failure(self):
        containers = [make("web", health="starting")]
        self.assertEqual(probes.verdict_t1(containers, ["web"])[0], "PENDING")

    def test_unhealthy_is_pending_until_caller_times_out(self):
        containers = [make("web", health="unhealthy")]
        self.assertEqual(probes.verdict_t1(containers, ["web"])[0], "PENDING")

    def test_crashed_container_fails_immediately(self):
        containers = [make("web", state="exited", exit_code=137, health="")]
        self.assertEqual(probes.verdict_t1(containers, ["web"])[0], "FAIL_UP")

    def test_oneshot_sidecar_does_not_block_pass(self):
        containers = [
            make("web", health="healthy"),
            make("setup", state="exited", exit_code=0),
        ]
        self.assertEqual(probes.verdict_t1(containers, ["web"])[0], "PASS")

    def test_no_containers_fails(self):
        self.assertEqual(probes.verdict_t1([], ["web"])[0], "FAIL_UP")


class TestVerdictT2(unittest.TestCase):
    def test_all_ports_answering_passes(self):
        containers = [make("web", published=[8080])]
        self.assertEqual(probes.verdict_t2(containers, {8080: True})[0], "PASS")

    def test_dead_port_is_pending(self):
        containers = [make("web", published=[8080])]
        self.assertEqual(probes.verdict_t2(containers, {8080: False})[0], "PENDING")

    def test_no_ports_observed_is_pending(self):
        self.assertEqual(probes.verdict_t2([make("web")], {})[0], "PENDING")


class TestVerdictT3(unittest.TestCase):
    def test_stable_long_enough_passes(self):
        self.assertEqual(probes.verdict_t3([make("a")], 30)[0], "PASS")

    def test_not_yet_stable_is_pending(self):
        self.assertEqual(probes.verdict_t3([make("a")], 5)[0], "PENDING")

    def test_crash_fails(self):
        containers = [make("a", state="exited", exit_code=2)]
        self.assertEqual(probes.verdict_t3(containers, 60)[0], "FAIL_UP")


class TestSanitizerDetection(unittest.TestCase):
    """Captured from CVE-2026-64608's real container output during the pilot."""

    REAL_ASAN = (
        "target-1  | AddressSanitizer:DEADLYSIGNAL\n"
        "target-1  | ==7==ERROR: AddressSanitizer: SEGV on unknown address\n"
        "target-1  | ==7==ABORTING\n"
    )

    def test_detects_real_addresssanitizer_output(self):
        self.assertTrue(probes.looks_like_sanitizer_abort(self.REAL_ASAN))

    def test_ordinary_crash_is_not_a_sanitizer_abort(self):
        self.assertFalse(
            probes.looks_like_sanitizer_abort("nginx: bind() to 0.0.0.0:80 failed")
        )

    def test_empty_log_is_not_a_sanitizer_abort(self):
        self.assertFalse(probes.looks_like_sanitizer_abort(""))


class TestRuntimePorts(unittest.TestCase):
    def test_collects_and_sorts_across_containers(self):
        containers = [make("a", published=[8081]), make("b", published=[8080, 8081])]
        self.assertEqual(probes.runtime_ports(containers), [8080, 8081])


if __name__ == "__main__":
    unittest.main()
