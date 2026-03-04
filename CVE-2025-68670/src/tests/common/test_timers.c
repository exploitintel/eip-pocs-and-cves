
#if defined(HAVE_CONFIG_H)
#include "config_ac.h"
#endif

#include <limits.h>
#include "os_calls.h"
#include "timers.h"

#include "test_common.h"

/******************************************************************************/
START_TEST(test_timers__null_timer)
{
    unsigned int now = g_get_elapsed_ms();
    struct timers_oneshot *timer = NULL;
    int v = timers_oneshot_get_remaining(timer, now);
    ck_assert_int_eq(v, -1);

    // Check any value of 'v' is not changed with a NULL timer
    timers_oneshot_update_poll(timer, now, &v);
    ck_assert_int_eq(v, -1);

    v = 0;
    timers_oneshot_update_poll(timer, now, &v);
    ck_assert_int_eq(v, 0);

    v = INT_MAX;
    timers_oneshot_update_poll(timer, now, &v);
    ck_assert_int_eq(v, INT_MAX);
}
END_TEST

/******************************************************************************/
START_TEST(test_timers__two_secs)
{
#define TOLERANCE 25    // Percent
#define HALF_WAIT 1000   // A second
    struct timers_oneshot *timer = timers_oneshot_init(2 * HALF_WAIT);
    ck_assert_ptr_ne(timer, NULL);

    // Wait for half the total period and check the elapsed timer is
    // within limits
    g_sleep(HALF_WAIT);
    int remaining = timers_oneshot_get_remaining(timer, g_get_elapsed_ms());

    ck_assert_int_ge(remaining, HALF_WAIT - (HALF_WAIT * TOLERANCE / 100));
    ck_assert_int_le(remaining, HALF_WAIT + (HALF_WAIT * TOLERANCE / 100));

    // Wait for the rest of the period and check the timer is zero (or near it)
    g_sleep(remaining);
    unsigned int now = g_get_elapsed_ms();
    int v = timers_oneshot_get_remaining(timer, now);
    ck_assert_int_ge(v, 0);
    ck_assert_int_le(v, 1);

    // Check the timer is zero in the future
    v = timers_oneshot_get_remaining(timer, now + 1000); // Second
    ck_assert_int_eq(v, 0);
    v = timers_oneshot_get_remaining(timer, now + 3600 * 1000); // Hour
    ck_assert_int_eq(v, 0);
    v = timers_oneshot_get_remaining(timer, now + 86400 * 1000); // Day
    ck_assert_int_eq(v, 0);
    v = timers_oneshot_get_remaining(timer, now + 7 * 86400 * 1000); // Week
    ck_assert_int_eq(v, 0);

    free(timer);
#undef TOLERANCE
#undef HALF_WAIT
}
END_TEST

/******************************************************************************/
Suite *
make_suite_test_timers(void)
{
    Suite *s;
    TCase *tc_timers;

    s = suite_create("timers");

    tc_timers = tcase_create("timers");
    suite_add_tcase(s, tc_timers);
    tcase_add_test(tc_timers, test_timers__null_timer);
    tcase_add_test(tc_timers, test_timers__two_secs);

    return s;
}
