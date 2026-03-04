#if defined(HAVE_CONFIG_H)
#include "config_ac.h"
#endif

#include "xrdp_tconfig.h"
#include "test_xrdp.h"
#include "xrdp.h"

#define GFXCONF_STUBDIR XRDP_TOP_SRCDIR "/tests/xrdp/gfx/"

START_TEST(test_tconfig_gfx_always_success)
{
    ck_assert_int_eq(1, 1);
}
END_TEST

START_TEST(test_tconfig_gfx_h264_oh264)
{
    struct xrdp_tconfig_gfx gfxconfig;
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_h264_encoder_openh264.toml", &gfxconfig);

    /* H.264 encoder is OpenH264 */
    ck_assert_int_eq(gfxconfig.h264_encoder, XTC_H264_OPENH264);
}

START_TEST(test_tconfig_gfx_h264_x264)
{
    struct xrdp_tconfig_gfx gfxconfig;
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_h264_encoder_x264.toml", &gfxconfig);

    /* H.264 encoder is x264 */
    ck_assert_int_eq(gfxconfig.h264_encoder, XTC_H264_X264);
}

START_TEST(test_tconfig_gfx_h264_undefined)
{
    struct xrdp_tconfig_gfx gfxconfig;
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_h264_encoder_undefined.toml", &gfxconfig);

    /* H.264 encoder is x264 if undefined */
    ck_assert_int_eq(gfxconfig.h264_encoder, XTC_H264_X264);
}

START_TEST(test_tconfig_gfx_h264_invalid)
{
    struct xrdp_tconfig_gfx gfxconfig;
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_h264_encoder_invalid.toml", &gfxconfig);

    /* H.264 encoder is x264 if invalid, unknown encoder specified */
    ck_assert_int_eq(gfxconfig.h264_encoder, XTC_H264_X264);
}

START_TEST(test_tconfig_gfx_oh264_load_basic)
{
    struct xrdp_tconfig_gfx gfxconfig;
    int rv = tconfig_load_gfx(GFXCONF_STUBDIR "/gfx.toml", &gfxconfig);

    ck_assert_int_eq(rv, 0);

    /* default */
    ck_assert_int_eq(gfxconfig.openh264_param[0].EnableFrameSkip, 0);
    ck_assert_int_eq(gfxconfig.openh264_param[0].TargetBitrate, 20000000);
    ck_assert_int_eq(gfxconfig.openh264_param[0].MaxBitrate, 0);
    ck_assert_float_eq(gfxconfig.openh264_param[0].MaxFrameRate, 60.0);
}

START_TEST(test_tconfig_gfx_x264_load_basic)
{
    struct xrdp_tconfig_gfx gfxconfig;
    int rv = tconfig_load_gfx(GFXCONF_STUBDIR "/gfx.toml", &gfxconfig);

    ck_assert_int_eq(rv, 0);

    /* default */
    ck_assert_str_eq(gfxconfig.x264_param[0].preset, "ultrafast");
    ck_assert_str_eq(gfxconfig.x264_param[0].tune, "zerolatency");
    ck_assert_str_eq(gfxconfig.x264_param[0].profile, "main");
    ck_assert_int_eq(gfxconfig.x264_param[0].vbv_max_bitrate, 0);
    ck_assert_int_eq(gfxconfig.x264_param[0].vbv_buffer_size, 0);
    ck_assert_int_eq(gfxconfig.x264_param[0].fps_num, 60);
    ck_assert_int_eq(gfxconfig.x264_param[0].fps_den, 1);

}
END_TEST

START_TEST(test_tconfig_gfx_codec_order)
{
    struct xrdp_tconfig_gfx gfxconfig;

    /* H264 earlier */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_h264_preferred.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 2);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_H264);
    ck_assert_int_eq(gfxconfig.codec.codecs[1], XTC_RFX);

    /* H264 only */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_h264_only.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 1);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_H264);

    /* RFX earlier */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_rfx_preferred.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 2);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_RFX);
    ck_assert_int_eq(gfxconfig.codec.codecs[1], XTC_H264);

    /* RFX appears twice like: RFX, H264, RFX */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_rfx_preferred_odd.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 2);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_RFX);
    ck_assert_int_eq(gfxconfig.codec.codecs[1], XTC_H264);

    /* RFX only */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_rfx_only.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 1);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_RFX);

    /* H264 is preferred if order undefined */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_codec_order_undefined.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 2);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_H264);
    ck_assert_int_eq(gfxconfig.codec.codecs[1], XTC_RFX);
}
END_TEST

START_TEST(test_tconfig_gfx_missing_file)
{
    struct xrdp_tconfig_gfx gfxconfig;

    /* Check RFX config is returned if the file doesn't exist */
    tconfig_load_gfx(GFXCONF_STUBDIR "/no_such_file.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 1);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_RFX);
}
END_TEST

START_TEST(test_tconfig_gfx_missing_h264)
{
    struct xrdp_tconfig_gfx gfxconfig;

    /* Check RFX config only is returned if H.264 parameters are missing */
    tconfig_load_gfx(GFXCONF_STUBDIR "/gfx_missing_h264.toml", &gfxconfig);
    ck_assert_int_eq(gfxconfig.codec.codec_count, 1);
    ck_assert_int_eq(gfxconfig.codec.codecs[0], XTC_RFX);
}
END_TEST

/******************************************************************************/
Suite *
make_suite_tconfig_load_gfx(void)
{
    Suite *s;
    TCase *tc_tconfig_load_gfx;

    s = suite_create("GfxLoad");

    tc_tconfig_load_gfx = tcase_create("xrdp_tconfig_load_gfx");
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_always_success);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_x264_load_basic);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_codec_order);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_missing_file);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_missing_h264);

    /* OpenH264 */
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_oh264_load_basic);

    /* H.264 encoder */
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_h264_oh264);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_h264_x264);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_h264_undefined);
    tcase_add_test(tc_tconfig_load_gfx, test_tconfig_gfx_h264_invalid);

    suite_add_tcase(s, tc_tconfig_load_gfx);

    return s;
}
