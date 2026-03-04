
#if defined(HAVE_CONFIG_H)
#include "config_ac.h"
#endif

#include <limits.h>

#include "set_int.h"

#include "test_common.h"

START_TEST(test_basic__bad_args)
{
    struct set_int *set = set_int_init(10, 1);
    ck_assert_ptr_eq(set, NULL);

    // Check for no segfaults with a NULL set
    set_int_add(set, 10);
    set_int_remove(set, 10);
    ck_assert_int_eq(set_int_contains(set, 10), 0);
    set_int_add_all(set);
    set_int_remove_all(set);

    int val = 0;
    ck_assert_int_eq(set_int_get_next(set, &val), 0);

    set_int_delete(set);
}
END_TEST

START_TEST(test_basic__one_to_ten)
{
    int i;

    struct set_int *set = set_int_init(1, 10);
    ck_assert_ptr_ne(set, NULL);

    set_int_add(set, 0);  // out-of-range
    for (i = 1 ; i <= 5; ++i)
    {
        set_int_add(set, i);
    }
    set_int_add(set, 11); // out-of-range

    ck_assert_int_eq(set_int_contains(set, 0), 0);
    ck_assert_int_eq(set_int_contains(set, 1), 1);
    ck_assert_int_eq(set_int_contains(set, 2), 1);
    ck_assert_int_eq(set_int_contains(set, 3), 1);
    ck_assert_int_eq(set_int_contains(set, 4), 1);
    ck_assert_int_eq(set_int_contains(set, 5), 1);
    ck_assert_int_eq(set_int_contains(set, 6), 0);
    ck_assert_int_eq(set_int_contains(set, 7), 0);
    ck_assert_int_eq(set_int_contains(set, 8), 0);
    ck_assert_int_eq(set_int_contains(set, 9), 0);
    ck_assert_int_eq(set_int_contains(set, 10), 0);
    ck_assert_int_eq(set_int_contains(set, 11), 0);

    set_int_delete(set);
}
END_TEST

START_TEST(test_basic__add_remove_all)
{
    struct set_int *set = set_int_init(1, 10);
    ck_assert_ptr_ne(set, NULL);

    set_int_add(set, 0);  // out-of-range
    set_int_add_all(set);
    set_int_add(set, 11); // out-of-range

    ck_assert_int_eq(set_int_contains(set, 0), 0);
    ck_assert_int_eq(set_int_contains(set, 1), 1);
    ck_assert_int_eq(set_int_contains(set, 2), 1);
    ck_assert_int_eq(set_int_contains(set, 3), 1);
    ck_assert_int_eq(set_int_contains(set, 4), 1);
    ck_assert_int_eq(set_int_contains(set, 5), 1);
    ck_assert_int_eq(set_int_contains(set, 6), 1);
    ck_assert_int_eq(set_int_contains(set, 7), 1);
    ck_assert_int_eq(set_int_contains(set, 8), 1);
    ck_assert_int_eq(set_int_contains(set, 9), 1);
    ck_assert_int_eq(set_int_contains(set, 10), 1);
    ck_assert_int_eq(set_int_contains(set, 11), 0);

    set_int_remove_all(set);
    ck_assert_int_eq(set_int_contains(set, 1), 0);
    ck_assert_int_eq(set_int_contains(set, 2), 0);
    ck_assert_int_eq(set_int_contains(set, 3), 0);
    ck_assert_int_eq(set_int_contains(set, 4), 0);
    ck_assert_int_eq(set_int_contains(set, 5), 0);
    ck_assert_int_eq(set_int_contains(set, 6), 0);
    ck_assert_int_eq(set_int_contains(set, 7), 0);
    ck_assert_int_eq(set_int_contains(set, 8), 0);
    ck_assert_int_eq(set_int_contains(set, 9), 0);
    ck_assert_int_eq(set_int_contains(set, 10), 0);

    set_int_delete(set);
}
END_TEST

START_TEST(test_basic__single_element)
{
#define VAL -1000

    struct set_int *set = set_int_init(VAL, VAL);
    ck_assert_ptr_ne(set, NULL);

    set_int_add(set, VAL - 1);  // out-of-range
    set_int_add(set, VAL);
    set_int_add(set, VAL + 1);  // out-of-range

    ck_assert_int_eq(set_int_contains(set, VAL - 1), 0);
    ck_assert_int_eq(set_int_contains(set, VAL), 1);
    ck_assert_int_eq(set_int_contains(set, VAL + 1), 0);

    set_int_add_all(set);
    ck_assert_int_eq(set_int_contains(set, VAL - 1), 0);
    ck_assert_int_eq(set_int_contains(set, VAL), 1);
    ck_assert_int_eq(set_int_contains(set, VAL + 1), 0);

    set_int_remove(set, VAL);
    ck_assert_int_eq(set_int_contains(set, VAL), 0);

    set_int_delete(set);
#undef VAL
}
END_TEST

START_TEST(test_basic__get_next)
{
    int i;
    struct set_int *set = set_int_init(0, 1000);
    ck_assert_ptr_ne(set, NULL);

    for (i = 1 ; i <= 10; ++i)
    {
        set_int_add(set, i);
    }

    set_int_add(set, 500);

    int val = INT_MIN;
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 1);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 2);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 3);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 4);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 5);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 6);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 7);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 8);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 9);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 10);
    ck_assert_int_eq(set_int_get_next(set, &val), 1);
    ck_assert_int_eq(val, 500);
    ck_assert_int_eq(set_int_get_next(set, &val), 0);

    set_int_delete(set);
}
END_TEST

/******************************************************************************/

Suite *
make_suite_test_set_int(void)
{
    Suite *s;
    TCase *tc_basic;

    s = suite_create("SetInt");

    tc_basic = tcase_create("basic");
    suite_add_tcase(s, tc_basic);
    tcase_add_test(tc_basic, test_basic__bad_args);
    tcase_add_test(tc_basic, test_basic__one_to_ten);
    tcase_add_test(tc_basic, test_basic__add_remove_all);
    tcase_add_test(tc_basic, test_basic__single_element);
    tcase_add_test(tc_basic, test_basic__get_next);

    return s;
}
