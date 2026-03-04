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

#if defined(HAVE_CONFIG_H)
#include "config_ac.h"
#endif

#include "os_calls.h"
#include "timers.h"

struct timers_oneshot
{
    unsigned int add_time;  // Time event was added from_g_get_elapsed_ms()
    int trigger_time;
};

/******************************************************************************/
struct timers_oneshot *
timers_oneshot_init(int ms)
{
    struct timers_oneshot *t = (struct timers_oneshot *)malloc(sizeof(*t));
    if (t != NULL)
    {
        t->add_time = g_get_elapsed_ms();
        t->trigger_time = (ms <= 0) ? 0 : ms;
    }
    return t;
}

/******************************************************************************/
int
timers_oneshot_get_remaining(struct timers_oneshot *timer,
                             unsigned int now)
{
    int rv = -1;
    if (timer != NULL)
    {
        if (timer->trigger_time == 0)
        {
            rv = 0;
        }
        else
        {
            rv = timer->trigger_time - (int)(now - timer->add_time);
            if (rv <= 0)
            {
                rv = 0;
                // (pathological) Make sure the timer doesn't stop
                // triggering if it isn't attended to for the rollover
                // period (~20 days for a 32-bit timer).
                timer->trigger_time = 0;
            }
        }
    }

    return rv;
}

/******************************************************************************/
void
timers_oneshot_update_poll(struct timers_oneshot *timer, unsigned int now,
                           int *timeout)
{
    if (timer != NULL && timeout != NULL)
    {
        int remaining = timers_oneshot_get_remaining(timer, now);
        if (*timeout < 0 || *timeout > remaining)
        {
            *timeout = remaining;
        }
    }
}
