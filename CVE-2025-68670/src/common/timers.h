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
 *
 */

/**
 * @file common/timers.h
 * @brief Timers and related functions (declarations)
 * @author Matt Burt
  */

#ifndef TIMERS_H
#define TIMERS_H

#include "arch.h"

struct timers_oneshot;

/**
 * Initialise a one-shot timer
 * @param ms Milliseconds until timer fires (>= 0)
 * @return pointer to timer
 *
 * Returns NULL if no memory.
 *
 * When the timer is no longer required, it can simply be passed to free()
 */
struct timers_oneshot *
timers_oneshot_init(int ms);

/**
 * Return ms remaining on a one-shot timer
 * @param timer pointer to timer (or NULL)
 * @param now Value of g_get_elapsed_ms()
 * @return remaining ms
 *
 * Once this routine has returned 0 for a particular timer, it will never
 * return anything else. Don't pass anything to 'now' apart from a recent
 * value from g_get_elapsed_ms()
 *
 * If the timer is NULL, -1 is returned.
 */
int
timers_oneshot_get_remaining(struct timers_oneshot *timer,
                             unsigned int now);

/**
 * Variant of timers_oneshot_get_remaining() for g_obj_wait()
 * @param timer pointer to timer (or NULL)
 * @param now Value of g_get_elapsed_ms()
 * @param[in,out] timeout timeout
 *
 * Use this to update a timeout passed to g_obj_wait() (or poll()). The
 * timeout is updated if the timer will fire before the current timeout.
 */
void
timers_oneshot_update_poll(struct timers_oneshot *timer, unsigned int now,
                           int *timeout);

#endif // TIMERS_H
