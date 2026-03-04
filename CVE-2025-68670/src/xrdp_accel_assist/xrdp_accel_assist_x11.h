/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2020-2024
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

#ifndef _XRDP_ACCEL_ASSIST_X11_H
#define _XRDP_ACCEL_ASSIST_X11_H

/* generic type that can hold either a GLXPixmap(XID, unsigned int or long)
 * or EGLSurface(void*) */
typedef intptr_t inf_image_t;

/* forward declaration used in xrdp_accel_assist_nvenc and
   xrdp_accel_assist_yami */
struct enc_info;

int
xrdp_accel_assist_x11_init(void);
int
xrdp_accel_assist_x11_get_wait_objs(intptr_t *objs, int *obj_count);
int
xrdp_accel_assist_x11_check_wait_objs(void);
int
xrdp_accel_assist_x11_delete_all_pixmaps(void);
int
xrdp_accel_assist_x11_create_pixmap(int width, int height, int magic,
                                    int con_id, int mon_id);
enum encoder_result
xrdp_accel_assist_x11_encode_pixmap(int left, int top, int width, int height,
                                    int mon_id, int num_crects,
                                    struct xh_rect *crects,
                                    void *cdata, int *cdata_bytes,
                                    int flags);

#endif
