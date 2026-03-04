/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2025, all xrdp contributors
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
 * @file libipm/ccp.h
 * @brief CCP declarations
 * @author Matt Burt
 *
 * Functions in this file use the following naming conventions:-
 *
 * E_CCP_{msg}_REQUEST is sent by ccp_send_{msg}_request()
 * E_CCP_{msg}_REQUEST is parsed by ccp_get_{msg}_request()
 * E_CCP_{msg}_RESPONSE is sent by ccp_send_{msg}_response()
 * E_CCP_{msg}_RESPONSE is parsed by ccp_get_{msg}_response()
 * E_CCP_{msg}_EVENT is sent by ccp_send_{msg}_event()
 * E_CCP_{msg}_EVENT is parsed by ccp_get_{msg}_event()
 */

#ifndef CCP_H
#define CCP_H

#include "arch.h"
#include "trans.h"
#include "ccp_application_types.h"

/* Message codes */
enum ccp_msg_code
{
    E_CCP_CLOSE_CONNECTION_REQUEST // sesexec -> xrdp
    // No E_CCP_CLOSE_CONNECTION_RESPONSE
};

/* Common facilities */

/**
 * Convert a message code to a string for output
 * @param n Message code
 * @param buff to contain string
 * @param buff_size length of buff
 * @return buff is returned for convenience.
 */
const char *
ccp_msgno_to_str(enum ccp_msg_code n, char *buff, unsigned int buff_size);

/* Connection management facilities */

/**
 * Converts an SCP transport to an CCP transport.
 *
 * This is done following successful transmission or receipt of an
 * E_SCP_CONNECT_SESSION_RESPONSE
 *
 * @param trans connected endpoint
 * @param callback_func New callback function for CCP messages.
 * @param callback_data New argument for callback function
 */
void
ccp_trans_from_scp_trans(struct trans *trans,
                         ttrans_data_in callback_func,
                         void *callback_data);

/**
 * Checks an CCP transport to see if a complete message is
 * available for parsing
 *
 * @param trans CCP transport
 * @param[out] available != 0 if a complete message is available
 * @return != 0 for error
 */
int
ccp_msg_in_check_available(struct trans *trans, int *available);

/**
 * Gets the CCP message number of an incoming message
 *
 * @param trans CCP transport
 * @return message in the buffer
 *
 * The results of calling this routine before ccp_msg_in_check_available()
 * states a message is available are undefined.
 */
enum ccp_msg_code
ccp_msg_in_get_msgno(const struct trans *trans);

/**
 * Resets an CCP message buffer ready to receive the next message
 *
 * @param trans libipm transport
 */
void
ccp_msg_in_reset(struct trans *trans);

/* -------------------- Session messages--------------------  */
/**
 * Send an E_CCP_CLOSE_CONNECTION_REQUEST
 *
 * Direction : sesexec -> xrdp
 *
 * @param trans CCP transport
 * @param reason reason_code
 * @return != 0 for error
 */
int
ccp_send_close_connection_request(struct trans *trans,
                                  enum ccp_close_reason_type reason);

/**
 * Parse an incoming E_CCP_CLOSE_CONNECTION_REQUEST
 *
 * Direction : sesexec -> xrdp
 *
 * @param trans CCP transport
 * @param[out] reason reason_code
 * @return != 0 for error
 */
int
ccp_get_close_connection_request(struct trans *trans,
                                 enum ccp_close_reason_type *reason);

#endif /* CCP_H */
