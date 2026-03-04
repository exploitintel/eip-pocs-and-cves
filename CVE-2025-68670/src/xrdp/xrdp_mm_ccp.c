/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2014
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
 * module manager
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif
#include "ccp.h"
#include "xrdp.h"
#include "log.h"

/*****************************************************************************/
/**
 * We've been asked by sesman/sesexec to close the connection
 */
static int
xrdp_mm_process_close_ccp_connection_request(struct xrdp_mm *self)
{
    enum ccp_close_reason_type reason;

    int rv = ccp_get_close_connection_request(self->sesman_trans, &reason);
    if (rv == 0)
    {
        int errinfo;
        char buff[64];

        switch (reason)
        {
            case CCP_CLOSE_RPC_INITIATED_DISCONNECT:
                errinfo = ERRINFO_RPC_INITIATED_DISCONNECT;
                break;

            case CCP_CLOSE_DISCONNECTED_BY_OTHERCONNECTION:
                errinfo = ERRINFO_DISCONNECTED_BY_OTHERCONNECTION;
                break;

            case CCP_CLOSE_LOGOFF_BY_USER:
                errinfo = ERRINFO_LOGOFF_BY_USER;
                break;

            case CCP_CLOSE_SOFTWARE_FAILURE:
                errinfo = ERRINFO_SERVER_CSRSS_CRASH;
                break;

            default:
                LOG(LOG_LEVEL_WARNING, "Unexpected close connection reason %d",
                    (int)reason);
                errinfo = ERRINFO_LOGOFF_BY_USER;
        }

        LOG(LOG_LEVEL_INFO, "Request to close connection : '%s'",
            ccp_close_reason_to_str(reason, buff, sizeof(buff)));
        xrdp_mm_set_fatal(self, errinfo);
    }

    return rv;
}

/*****************************************************************************/
int
xrdp_mm_ccp_data_in(struct trans *trans)
{
    int rv = 0;
    int available;

    rv = ccp_msg_in_check_available(trans, &available);
    if (rv == 0 && available)
    {
        struct xrdp_mm *self = (struct xrdp_mm *)(trans->callback_data);
        enum ccp_msg_code msgno;

        switch ((msgno = ccp_msg_in_get_msgno(trans)))
        {
            case E_CCP_CLOSE_CONNECTION_REQUEST:
                rv = xrdp_mm_process_close_ccp_connection_request(self);
                break;

            default:
            {
                char buff[64];
                ccp_msgno_to_str(msgno, buff, sizeof(buff));
                LOG(LOG_LEVEL_ERROR, "Ignored CCP message %s from sesman",
                    buff);
            }
        }

        ccp_msg_in_reset(trans);
    }

    return rv;
}
