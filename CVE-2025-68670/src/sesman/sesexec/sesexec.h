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
 * @file sesexec.h
 * @brief Main include file
 * @author Jay Sorg
 *
 */

#ifndef SESEXEC_H
#define SESEXEC_H

#include <sys/types.h>

#include "ccp_application_types.h"
#include "xrdp_constants.h"

struct config_sesman;
struct trans;
struct login_info;
struct session_data;

#if defined(__FreeBSD__) || defined(__FreeBSD_kernel__)
#define USE_BSD_SETLOGIN
#endif

/* Globals */
/**
 * Pointer to config data for sesman/sesexec
 */
extern struct config_sesman *g_cfg;

/**
 * DES key used to obfuscate VNC password files.
 *
 * This key is not documented in RFC6143, but can readily be found by
 * searching VNC sources.
 *
 * You can also find the 'reversed' form "e84ad660c4721ae0"
 * on the net, particulary for openssl one-liners to decrypt VNC
 * password files.
 */
extern unsigned char g_fixedkey[8];

/**
 * Information about the logged-in user
 *
 * This is set when the user successfully passes authentication
 */
extern struct login_info *g_login_info;

/**
 * Information about the user session (opaque type)
 *
 * Set when the user is succesfully logged in
 */
extern struct session_data *g_session_data;

/**
 * Program has received a termination event
 */
extern tintptr g_term_event;

/**
 * Program has received one or more SIGCHLD events
 */
extern tintptr g_sigchld_event;

/**
 * PID of sesexec process
 *
 * Used to detect when we are running in a form of the original process
 */
extern pid_t g_pid;

/**
 * EICP/ERCP transport
 *
 * Used to communicate with the sesman process
 *
 * Use sesexec_is_ecp_active() if you need to check sesman is
 * truly there.
 */
extern struct trans *g_ecp_trans;

/**
 * CCP transport
 *
 * Used to communicate with the currently connected xrdp process
 */
extern struct trans *g_ccp_trans;

/**
 * Last connected client IP address
 */
extern char g_client_ip[MAX_PEER_ADDRSTRLEN];

/**
 * Last connected client name
 */
extern char g_client_name[INFO_CLIENT_NAME_BYTES_UTF8];

/**
 * Last connect / disconnect time
 */
extern time_t g_last_connect_disconnect;

/**
 * Callback to process incoming ERCP data
 */
int
sesexec_ercp_data_in(struct trans *self);

/**
 * Check for termination
 *
 * @return boolean. Set if program has been asked to terminate
 */
int
sesexec_is_term(void);

/**
 * Terminate the sesexec main loop
 *
 * @param status Status to return to the OS
 */
void
sesexec_terminate_main_loop(int status);

/**
 * Sets the ECP (sesman) transport
 *
 * @param t Transport supposedly connected to the ECP protocol
 *          provider (sesman), or NULL to clear the transport
 * @return 0 for success
 *
 * Peer credentials are checked for root:root
 */
int
sesexec_set_ecp_transport(struct trans *t);

/**
 * Is the ECP transport still active?
 *
 * @result boolean
 *
 * This is intended to be used as a guard to prevent an active ECP
 * transport being overwritten. Do not use it to check if sesman is
 * active before sending messages, as this introduces a race condition.
 */
int
sesexec_is_ecp_active(void);

/**
 * Terminate an active xrdp process
 *
 * @param reason Reason to pass back to the xrdp process (if connected)
 *
 * After this call, g_ccp_trans will be NULL
 */
void
sesexec_terminate_connected_xrdp_process(enum ccp_close_reason_type reason);

/**
 * Set the CCP transport from an SCP transport
 *
 * This call is intended to be used at the end of a connection
 * event, to record our end of the connection to the xrdp process
 */
int
sesexec_set_ccp_trans(struct trans *scp_trans);

#endif // SESEXEC_H
