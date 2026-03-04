/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2015
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
 * @file sesman_access.c
 * @brief User access control code
 * @author Simone Fedele
 *
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <stdio.h>

#include "arch.h"

#include "sesman_access.h"
#include "sesman_config.h"
#include "log.h"
#include "os_calls.h"

/******************************************************************************/
/**
 * user is root
 *
 * @return 1 if user is UID 0
 */
static int
user_is_root(const char *user)
{
    int uid = -1;
    (void)g_getuser_info_by_name(user, &uid, NULL, NULL, NULL, NULL);
    return (uid == 0);
}

/******************************************************************************/
int
access_login_allowed(const struct config_security *cfg_sec, const char *user)
{
    int ok = 0;

    if (!cfg_sec->allow_root && user_is_root(user))
    {
        LOG(LOG_LEVEL_ERROR,
            "ROOT login attempted, but root login is disabled");
    }
    else
    {
        const char *group = cfg_sec->ts_users;
        const char *param = "TerminalServerUsers";
        int gid = -1;

        if (group == NULL || group[0] == '\0')
        {
            /* Group is not defined. Default access depends on whether
             * we must have the group or not */
            if (cfg_sec->ts_always_group_check)
            {
                LOG(LOG_LEVEL_ERROR,
                    "%s group is not defined. Access denied for %s",
                    param, user);
            }
            else
            {
                LOG(LOG_LEVEL_INFO,
                    "%s group is not defined. Access granted for %s",
                    param, user);
                ok = 1;
            }
        }
        else if (g_getgroup_info(group, &gid) != 0)
        {
            /* Group is defined but doesn't exist. Default access depends
             * on whether we must have the group or not */
            if (cfg_sec->ts_always_group_check)
            {
                LOG(LOG_LEVEL_ERROR,
                    "%s group %s doesn't exist. Access denied for %s",
                    param, group, user);
            }
            else
            {
                LOG(LOG_LEVEL_INFO,
                    "%s group %s doesn't exist. Access granted for %s",
                    param, group, user);
                ok = 1;
            }
        }
        else if (0 != g_check_user_in_group(user, gid, &ok))
        {
            LOG(LOG_LEVEL_ERROR, "Error checking %s group %s. "
                "Access denied for %s", param, group, user);
        }
        else if (!ok)
        {
            LOG(LOG_LEVEL_ERROR, "User %s is not in %s group %s. Access denied",
                user, param, group);
        }
        else
        {
            LOG(LOG_LEVEL_INFO, "User %s is in %s group %s. Access granted",
                user, param, group);
        }
    }

    return ok;
}

/******************************************************************************/
int
access_login_is_admin(const struct config_security *cfg_sec,
                      const char *user)
{
    int ok = 0;

    const char *group = cfg_sec->ts_admins;
    const char *param = "TerminalServerAdmins";
    int gid = -1;

    if (group == NULL || group[0] == '\0')
    {
        if (cfg_sec->ts_always_group_check)
        {
            LOG(LOG_LEVEL_ERROR, "%s group is not defined", param);
        }
    }
    else if (g_getgroup_info(group, &gid) != 0)
    {
        /* Group is defined but doesn't exist */
        if (cfg_sec->ts_always_group_check)
        {
            LOG(LOG_LEVEL_ERROR, "%s group %s doesn't exist.", param, group);
        }
    }
    else if (0 != g_check_user_in_group(user, gid, &ok))
    {
        LOG(LOG_LEVEL_ERROR, "Error checking %s group %s.", param, group);
    }

    // Root always has access. Do other checks and logging first
    if (!ok && user_is_root(user))
    {
        ok = 1;
    }

    if (ok)
    {
        LOG(LOG_LEVEL_INFO, "User %s is in %s group %s", user, param, group);
    }

    return ok;
}
