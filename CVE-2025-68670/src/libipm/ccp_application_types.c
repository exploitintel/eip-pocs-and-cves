/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2022, all xrdp contributors
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
 * @file libipm/ccp_application_types.c
 * @brief Support routines for types in ccp_application_types.h
 * @author Matt Burt
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <stdio.h>

#include "ccp_application_types.h"

/*****************************************************************************/
const char *
ccp_close_reason_to_str(enum ccp_close_reason_type n,
                        char *buff, unsigned int buff_size)
{
    const char *str =
        (n == CCP_CLOSE_RPC_INITIATED_DISCONNECT)
        ? "Connection closed by administrator request" :
        (n == CCP_CLOSE_DISCONNECTED_BY_OTHERCONNECTION)
        ? "Another connection was made to the session" :
        (n == CCP_CLOSE_LOGOFF_BY_USER)
        ? "The user logged out of the session" :
        (n == CCP_CLOSE_SOFTWARE_FAILURE)
        ? "A software failure has occurred" :
        /* Default */ NULL;

    if (str == NULL)
    {
        (void)snprintf(buff, buff_size, "[ccp reason code #%d]", (int)n);
    }
    else
    {
        (void)snprintf(buff, buff_size, "%s", str);
    }

    return buff;
}
