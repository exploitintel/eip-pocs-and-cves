import types
import unittest
import unittest.mock

import safety


def snap(images=("img_pre",), tags=("repo:tag",), volumes=("vol_pre",),
         networks=("net_pre",)):
    return {
        "images": set(images),
        "image_tags": set(tags),
        "volumes": set(volumes),
        "networks": set(networks),
    }


class TestArtifactDiff(unittest.TestCase):
    def setUp(self):
        self.before = snap()

    def test_new_artifacts_reports_only_additions(self):
        after = snap(images=("img_pre", "img_new"))
        self.assertEqual(safety.new_artifacts(self.before, after)["images"],
                         {"img_new"})

    def test_missing_artifacts_reports_only_removals(self):
        after = snap(images=())
        self.assertEqual(safety.missing_artifacts(self.before, after)["images"],
                         {"img_pre"})

    def test_assert_preserved_passes_when_nothing_removed(self):
        after = snap(images=("img_pre", "img_new"))
        self.assertIsNone(safety.assert_preserved(self.before, after))


class TestPreservationSemantics(unittest.TestCase):
    """Regression guard from the pilot run.

    Rebuilding a lab moves its tag to a fresh image ID and leaves the old ID
    unreferenced. That is normal Docker behaviour and destroys nothing, so it
    must not be reported as host mutation. Losing a *tag*, volume or network is
    real destruction and must always raise.
    """

    def test_rebuild_replacing_image_id_is_not_mutation(self):
        before = snap(images=("old_id",), tags=("cve-lab:local",))
        after = snap(images=("new_id",), tags=("cve-lab:local",))
        self.assertIsNone(safety.assert_preserved(before, after))

    def test_losing_a_tag_is_mutation(self):
        before = snap(tags=("wordpress:6.5", "cve-lab:local"))
        after = snap(tags=("cve-lab:local",))
        with self.assertRaises(safety.HostMutationError) as ctx:
            safety.assert_preserved(before, after)
        self.assertIn("wordpress:6.5", str(ctx.exception))

    def test_losing_a_volume_is_mutation(self):
        with self.assertRaises(safety.HostMutationError) as ctx:
            safety.assert_preserved(snap(), snap(volumes=()))
        self.assertIn("vol_pre", str(ctx.exception))

    def test_losing_a_network_is_mutation(self):
        with self.assertRaises(safety.HostMutationError):
            safety.assert_preserved(snap(), snap(networks=()))

    def test_image_ids_are_not_protected(self):
        self.assertNotIn("images", safety.PROTECTED_KINDS)
        for kind in ("image_tags", "volumes", "networks"):
            self.assertIn(kind, safety.PROTECTED_KINDS)


class TestDiskGuard(unittest.TestCase):
    def test_disk_ok_true_when_above_floor(self):
        self.assertTrue(safety.disk_ok("/", floor=1))

    def test_disk_ok_false_when_floor_unreachable(self):
        self.assertFalse(safety.disk_ok("/", floor=10 ** 18))

    def test_floor_is_one_hundred_gib(self):
        self.assertEqual(safety.DISK_FLOOR_BYTES, 100 * 1024 ** 3)


class TestVolumeTeardownIsScoped(unittest.TestCase):
    """Regression guard for a real loss during the full run.

    `docker compose down -v` deleted `cve-2025-24490_pg_data`, a volume that
    existed before the audit started. Teardown must only remove volumes that
    appeared while the lab was up.
    """

    def test_preexisting_volume_is_never_a_removal_candidate(self):
        before = {"cve-2025-24490_pg_data"}
        # Nothing new appeared, so nothing may be removed.
        with unittest.mock.patch.object(safety, "_ids", return_value=before):
            self.assertEqual(safety.remove_new_volumes(before), [])

    def test_only_the_new_volume_is_targeted(self):
        before = {"preexisting_vol"}
        now = {"preexisting_vol", "lab_created_vol"}
        calls = []

        def fake_docker(*args, **kwargs):
            calls.append(args)
            return types.SimpleNamespace(returncode=0)

        with unittest.mock.patch.object(safety, "_ids", return_value=now), \
                unittest.mock.patch.object(safety, "_docker", fake_docker):
            removed = safety.remove_new_volumes(before)
        self.assertEqual(removed, ["lab_created_vol"])
        self.assertEqual(calls, [("volume", "rm", "-f", "lab_created_vol")])


class TestRemoveNewImagesIsScoped(unittest.TestCase):
    def test_refuses_to_remove_preexisting(self):
        before = snap(images=("keep",))
        after = snap(images=("keep",))
        self.assertEqual(safety.remove_new_images(before, after), [])


if __name__ == "__main__":
    unittest.main()
