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
 * @file libipm/ccp.c
 * @brief CCP definitions
 * @author Matt Burt
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <stddef.h>
#include <stdio.h>

#include "ccp.h"
#include "libipm.h"
#include "trans.h"

/*****************************************************************************/
static const char *
msgno_to_str(unsigned short n)
{
    return
        (n == E_CCP_CLOSE_CONNECTION_REQUEST) ? "E_CCP_CLOSE_CONNECTION_REQUEST" :
        NULL;
}

/*****************************************************************************/
const char *
ccp_msgno_to_str(enum ccp_msg_code n, char *buff, unsigned int buff_size)
{
    const char *str = msgno_to_str((unsigned short)n);

    if (str == NULL)
    {
        (void)snprintf(buff, buff_size, "[code #%d]", (int)n);
    }
    else
    {
        (void)snprintf(buff, buff_size, "%s", str);
    }

    return buff;
}

/*****************************************************************************/
void
ccp_trans_from_scp_trans(struct trans *trans,
                         ttrans_data_in callback_func,
                         void *callback_data)
{
    libipm_change_facility(trans, LIBIPM_FAC_SCP, LIBIPM_FAC_CCP);
    trans->trans_data_in = callback_func;
    trans->callback_data = callback_data;
}

/*****************************************************************************/
int
ccp_msg_in_check_available(struct trans *trans, int *available)
{
    return libipm_msg_in_check_available(trans, available);
}

/*****************************************************************************/
enum ccp_msg_code
ccp_msg_in_get_msgno(const struct trans *trans)
{
    return (enum ccp_msg_code)libipm_msg_in_get_msgno(trans);
}

/*****************************************************************************/
void
ccp_msg_in_reset(struct trans *trans)
{
    libipm_msg_in_reset(trans);
}

/*****************************************************************************/
int
ccp_send_close_connection_request(struct trans *trans,
                                  enum ccp_close_reason_type reason)
{
    return libipm_msg_out_simple_send(
               trans,
               (int)E_CCP_CLOSE_CONNECTION_REQUEST,
               "i", reason);
}

/*****************************************************************************/
int
ccp_get_close_connection_request(struct trans *trans,
                                 enum ccp_close_reason_type *reason)
{
    /* Intermediate values */
    int32_t i_reason;

    int rv = libipm_msg_in_parse( trans, "i", &i_reason);
    if (rv == 0)
    {
        *reason = (enum ccp_close_reason_type)i_reason;
    }

    return rv;
}
