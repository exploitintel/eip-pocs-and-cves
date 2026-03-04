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
 * @file libipm/ccp_application_types.h
 * @brief ccp type declarations intended for use in the application
 * @author Simone Fedele/ Matt Burt
 */

#ifndef CCP_APPLICATION_TYPES_H
#define CCP_APPLICATION_TYPES_H

#include <sys/types.h>

/**
 * Select the reason for a close connection request
 */
enum ccp_close_reason_type
{
    CCP_CLOSE_RPC_INITIATED_DISCONNECT = 1,
    CCP_CLOSE_DISCONNECTED_BY_OTHERCONNECTION,
    CCP_CLOSE_LOGOFF_BY_USER,
    CCP_CLOSE_SOFTWARE_FAILURE
};

/**
 * Convert an ccp_close_reason_type to a readable string for output
 * @param n Message code
 * @param buff to contain string
 * @param buff_size length of buff
 * @return buff is returned for convenience.
 */
const char *
ccp_close_reason_to_str(enum ccp_close_reason_type n,
                        char *buff, unsigned int buff_size);

#endif /* CCP_APPLICATION_TYPES_H */
