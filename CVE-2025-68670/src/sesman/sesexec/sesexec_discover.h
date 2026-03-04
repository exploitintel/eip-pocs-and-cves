/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2024
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
 * @file sesexec_discover.h
 * @brief Declare functionality associated with sesman restart support
 * for sesexec
 *
 * @author Matt Burt
 *
 */

#ifndef SESEXEC_DISCOVER_H
#define SESEXEC_DISCOVER_H

#include <stdint.h>

/**
 * Start the listening object used when sesman restarts
 *
 * @return != 0 for error
 */
int
sesexec_discover_enable(void);

/**
 * Stop the listening object used when sesman restarts, and deallocate all
 * module resources.
 *
 * @return != 0 for error
 */
int
sesexec_discover_disable(void);

/**
 * Add any file descriptors in use by the module to an array
 *
 * @param robjs Array to add fds to
 * @param[in,out] robjs_count Index where elements are to be added
 * @param max_count Max value of robjs_count
 * @return != 0 for error
 *
 * This function can be called before sesexec_discover_enable(),
 * in which case it does nothing.
 */
int
sesexec_discover_get_wait_objs(intptr_t robjs[], int *robjs_count,
                               int max_count);


/**
 * Check any file descriptors in use by the module for actionable events
 * @return != 0 for error
 *
 * This function can be called before sesexec_discover_enable(),
 * in which case it does nothing.
 */
int
sesexec_discover_check_wait_objs(void);

#endif // SESEXEC_DISCOVER_H
