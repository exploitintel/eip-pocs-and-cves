/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2023
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
 * @file eicp_server.c
 * @brief eicp (executive initialisation control protocol) server function
 * @author Matt Burt
 *
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include "trans.h"

#include "ccp.h"
#include "ccp_server.h"

/******************************************************************************/
int
ccp_server(struct trans *self)
{
    int rv = 0;
    enum ccp_msg_code msgno;

    switch ((msgno = ccp_msg_in_get_msgno(self)))
    {
        default:
        {
            char buff[64];
            ccp_msgno_to_str(msgno, buff, sizeof(buff));
            LOG(LOG_LEVEL_ERROR, "Ignored CCP message %s", buff);
        }
    }
    return rv;
}
