/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2022-2024
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#include <X11/Xlib.h>

#include <epoxy/gl.h>
#include <epoxy/glx.h>

#include "arch.h"
#include "os_calls.h"
#include "string_calls.h"
#include "xrdp_accel_assist.h"
#include "xrdp_accel_assist_x11.h"
#include "xrdp_accel_assist_glx.h"
#include "log.h"

static int g_n_fbconfigs = 0;
static int g_n_pixconfigs = 0;
static GLXFBConfig *g_fbconfigs = NULL;
static GLXFBConfig *g_pixconfigs = NULL;
static GLXContext g_gl_context = 0;

/* X11 */
extern Display *g_display; /* in xrdp_accel_assist_x11.c */
extern Window g_root_window; /* in xrdp_accel_assist_x11.c */
extern int g_screen_num; /* in xrdp_accel_assist_x11.c */

static const int g_fbconfig_attrs[] =
{
    GLX_DRAWABLE_TYPE, GLX_WINDOW_BIT,
    GLX_RENDER_TYPE,   GLX_RGBA_BIT,
    GLX_DOUBLEBUFFER,  True,
    GLX_RED_SIZE,      8,
    GLX_GREEN_SIZE,    8,
    GLX_BLUE_SIZE,     8,
    None
};

static const int g_pixconfig_attrs[] =
{
    GLX_BIND_TO_TEXTURE_RGBA_EXT,       True,
    GLX_DRAWABLE_TYPE,                  GLX_PIXMAP_BIT,
    GLX_BIND_TO_TEXTURE_TARGETS_EXT,    GLX_TEXTURE_2D_BIT_EXT,
    GLX_DOUBLEBUFFER,                   False,
    GLX_Y_INVERTED_EXT,                 True,
    None
};

static const int g_pixmap_attribs[] =
{
    GLX_TEXTURE_TARGET_EXT, GLX_TEXTURE_2D_EXT,
    GLX_TEXTURE_FORMAT_EXT, GLX_TEXTURE_FORMAT_RGBA_EXT,
    None
};

/*****************************************************************************/
int
xrdp_accel_assist_inf_glx_init(void)
{
    const char *ext_str;
    int glx_ver;
    int ok;

    glx_ver = epoxy_glx_version(g_display, g_screen_num);
    LOG(LOG_LEVEL_INFO, "glx_ver %d", glx_ver);
    if (glx_ver < 11) /* GLX version 1.1 */
    {
        LOG(LOG_LEVEL_INFO, "glx_ver too old %d", glx_ver);
        return 1;
    }
    if (!epoxy_has_glx_extension(g_display, g_screen_num,
                                 "GLX_EXT_texture_from_pixmap"))
    {
        ext_str = glXQueryExtensionsString(g_display, g_screen_num);
        LOG(LOG_LEVEL_INFO, "GLX_EXT_texture_from_pixmap not present [%s]",
            ext_str);
        return 1;
    }
    LOG(LOG_LEVEL_INFO, "GLX_EXT_texture_from_pixmap present");
    g_fbconfigs = glXChooseFBConfig(g_display, g_screen_num,
                                    g_fbconfig_attrs, &g_n_fbconfigs);
    LOG(LOG_LEVEL_INFO, "g_fbconfigs %p", g_fbconfigs);
    g_gl_context = glXCreateNewContext(g_display, g_fbconfigs[0],
                                       GLX_RGBA_TYPE, NULL, 1);
    LOG(LOG_LEVEL_INFO, "g_gl_context %p", g_gl_context);
    ok = glXMakeCurrent(g_display, g_root_window, g_gl_context);
    LOG(LOG_LEVEL_INFO, "ok %d", ok);
    g_pixconfigs = glXChooseFBConfig(g_display, g_screen_num,
                                     g_pixconfig_attrs, &g_n_pixconfigs);
    LOG(LOG_LEVEL_INFO, "g_pixconfigs %p g_n_pixconfigs %d",
        g_pixconfigs, g_n_pixconfigs);
    return 0;
}

/*****************************************************************************/
int
xrdp_accel_assist_inf_glx_create_image(Pixmap pixmap, inf_image_t *inf_image)
{
    *inf_image = (inf_image_t)glXCreatePixmap(g_display, g_pixconfigs[0],
                 pixmap, g_pixmap_attribs);
    return 0;
}

/*****************************************************************************/
int
xrdp_accel_assist_inf_glx_destroy_image(inf_image_t inf_image)
{
    glXDestroyPixmap(g_display, (GLXPixmap)inf_image);
    return 0;
}

/*****************************************************************************/
int
xrdp_accel_assist_inf_glx_bind_tex_image(inf_image_t inf_image)
{
    glXBindTexImageEXT(g_display, (GLXPixmap)inf_image, GLX_FRONT_EXT, NULL);
    return 0;
}

/*****************************************************************************/
int
xrdp_accel_assist_inf_glx_release_tex_image(inf_image_t inf_image)
{
    glXReleaseTexImageEXT(g_display, (GLXPixmap)inf_image, GLX_FRONT_EXT);
    return 0;
}
