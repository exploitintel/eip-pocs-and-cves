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

#ifndef _XRDP_ACCEL_ASSIST_GLX_H
#define _XRDP_ACCEL_ASSIST_GLX_H

int
xrdp_accel_assist_inf_glx_init(void);
int
xrdp_accel_assist_inf_glx_create_image(Pixmap pixmap, inf_image_t *inf_image);
int
xrdp_accel_assist_inf_glx_destroy_image(inf_image_t inf_image);
int
xrdp_accel_assist_inf_glx_bind_tex_image(inf_image_t inf_image);
int
xrdp_accel_assist_inf_glx_release_tex_image(inf_image_t inf_image);

#endif
