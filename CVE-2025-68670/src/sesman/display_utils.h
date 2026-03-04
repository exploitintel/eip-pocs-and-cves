/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2025
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

/**
 *
 * @file display_utils.h
 * @brief Declaration of utility calls related to display handling
 * @author Matt Burt
 */


#ifndef DISPLAY_UTILS_H
#define DISPLAY_UTILS_H

struct set_int;

/**
 * @brief Gets a free display number
 *
 * @param alloc_displays Displays already allocated (or being allocated) by sesman
 * @return next available display, or -1 if none.
 */
int
display_utils_get_free_display(const struct set_int *alloc_displays);

#endif // DISPLAY_UTILS_H
