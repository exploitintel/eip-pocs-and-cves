/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Matt Burt 2023
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
 * @file sesexec.c
 * @brief Main program file for session executive process
 * @author Matt Burt
 *
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <ctype.h>
#include <stdarg.h>

#include "arch.h"
#include "ccp.h"
#include "ccp_server.h"
#include "eicp.h"
#include "eicp_server.h"
#include "ercp.h"
#include "ercp_server.h"
#include "login_info.h"
#include "sesexec.h"
#include "sesexec_discover.h"
#include "sesman_config.h"
#include "log.h"
#include "os_calls.h"
#include "session.h"
#include "string_calls.h"
#include "trans.h"
#include "xrdp_sockets.h"

struct startup_params
{
    const char *sesman_ini;
};

enum
{
    MAX_ROBJS = 32, ///< Maximum number of file objects in use at any one time
    XRDP_EXIT_TIMEOUT = 2500 ///< Time to wait for xrdp process to exit
};

/*
 * Program-scope globals
 */
struct config_sesman *g_cfg;
unsigned char g_fixedkey[8] = { 23, 82, 107, 6, 35, 78, 88, 7 };
struct login_info *g_login_info;
struct session_data *g_session_data;

tintptr g_term_event = 0;
tintptr g_sigchld_event = 0;
pid_t g_pid;

struct trans *g_ecp_trans;
struct trans *g_ccp_trans;
char g_client_ip[MAX_PEER_ADDRSTRLEN];
char g_client_name[INFO_CLIENT_NAME_BYTES_UTF8];
time_t g_last_connect_disconnect;

/*
 * Module-scope globals
 */
static pid_t g_ecp_pid;
static int g_terminate_loop = 0;
static int g_terminate_status = 0;

/*****************************************************************************/
/**
 * Command line argument parser
 * @param[in] argc number of command line arguments
 * @param[in] argv pointer array of commandline arguments
 * @param[out] startup_params Returned startup parameters
 * @return 0 on success
 */
static int
process_params(int argc, char **argv,
               struct startup_params *startup_params)
{
    int index;
    const char *option;
    const char *value;

    startup_params->sesman_ini = DEFAULT_SESMAN_INI;

    index = 1;

    while (index < argc)
    {
        option = argv[index];

        if (index + 1 < argc)
        {
            value = argv[index + 1];
        }
        else
        {
            value = "";
        }

        if (g_strcmp(option, "-c") == 0)
        {
            index++;
            startup_params->sesman_ini = value;
        }
        else /* unknown option */
        {
            return index;
        }

        index++;
    }

    return 0;
}

/******************************************************************************/
static int
sesexec_eicp_data_in(struct trans *self)
{
    int rv;
    int available;

    rv = eicp_msg_in_check_available(self, &available);

    if (rv == 0 && available)
    {
        if ((rv = eicp_server(self)) != 0)
        {
            LOG(LOG_LEVEL_ERROR, "%s: eicp_server failed", __func__);
        }
        eicp_msg_in_reset(self);
    }

    return rv;
}

/******************************************************************************/
int
sesexec_ercp_data_in(struct trans *self)
{
    int rv;
    int available;

    rv = ercp_msg_in_check_available(self, &available);

    if (rv == 0 && available)
    {
        if ((rv = ercp_server(self)) != 0)
        {
            LOG(LOG_LEVEL_ERROR, "%s: ercp_server failed", __func__);
        }
        ercp_msg_in_reset(self);
    }

    return rv;
}

/******************************************************************************/
static int
sesexec_ccp_data_in(struct trans *self)
{
    int rv;
    int available;

    rv = ccp_msg_in_check_available(self, &available);

    if (rv == 0 && available)
    {
        if ((rv = ccp_server(self)) != 0)
        {
            LOG(LOG_LEVEL_ERROR, "%s: ccp_server failed", __func__);
        }
        ccp_msg_in_reset(self);
    }

    return rv;
}

/******************************************************************************/
/**
 * Informs the main loop a termination signal has been received
 */
static void
set_term_event(int sig)
{
    /* Don't try to use a wait obj in a child process */
    if (g_getpid() == g_pid)
    {
        g_set_wait_obj(g_term_event);
    }
}

/*****************************************************************************/
/* No-op signal handler.
 */
static void
sig_no_op(int sig)
{
    /* no-op */
}

/******************************************************************************/
/**
 * Informs the main loop a child exiting signal has been received
 */
static void
set_sigchld_event(int sig)
{
    /* Don't try to use a wait obj in a child process */
    if (g_getpid() == g_pid)
    {
        g_set_wait_obj(g_sigchld_event);
    }
}

/******************************************************************************/
int
sesexec_is_term(void)
{
    return g_terminate_loop || g_is_wait_obj_set(g_term_event);
}

/******************************************************************************/
void
sesexec_terminate_main_loop(int status)
{
    // Only take the first request to terminate the loop
    if (!g_terminate_loop)
    {
        g_terminate_loop = 1;
        g_terminate_status = status;
    }
}

/******************************************************************************/
int
sesexec_set_ecp_transport(struct trans *t)
{
    int rv;
    int pid;
    int uid;
    int gid;

    if (t == NULL)
    {
        trans_delete(g_ecp_trans);
        g_ecp_trans = NULL;
        g_ecp_pid = 0;
        rv = 0;
    }
    else if (t == g_ecp_trans)
    {
        // This would break the memory subsystem!
        LOG(LOG_LEVEL_ERROR, "%s: programming error", __func__);
        rv = 1;
    }
    else if ((rv = g_sck_get_peer_cred(t->sck, &pid, &uid, &gid)) != 0)
    {
        LOG(LOG_LEVEL_ERROR, "Can't get credentials of sesman socket [%s]",
            g_get_strerror());
    }
    else if (uid != 0 || gid != 0)
    {
        LOG(LOG_LEVEL_ERROR, "sesman PID %d is running as UID:GID %d:%d",
            pid, uid, gid);
        rv = 1;
    }
    else
    {
        trans_delete(g_ecp_trans);
        g_ecp_trans = t;
        g_ecp_pid = pid;
        rv = 0;
    }

    return rv;
}

/******************************************************************************/
int
sesexec_is_ecp_active(void)
{
    return (g_ecp_trans != NULL &&
            g_ecp_pid != 0 && g_pid_is_active(g_ecp_pid));

}

/******************************************************************************/
static void
sesexec_main_loop_cleanup(void)
{
    login_info_free(g_login_info);

    /* This session is no longer discoverable */
    sesexec_discover_disable();

    /* Don't allow sesexec to terminate with an active
       session, as we can't connect to such a session */
    if (session_active(g_session_data))
    {
        LOG(LOG_LEVEL_INFO,
            "Stopping session on xrdp-sesexec exit");
        session_send_term(g_session_data, 1);
    }
    session_data_free(g_session_data);
}

/******************************************************************************/
/**
 * Close the CCP trans unconditionally
 *
 * Use this call if you are certain the other end has gone away
 */
static void
close_ccp_trans(void)
{
    trans_delete(g_ccp_trans);
    g_ccp_trans = NULL;
}

/******************************************************************************/
/**
 *
 * @brief Starts sesexec main loop
 *
 */
static int
sesexec_main_loop(void)
{
    int error = 0;
    int robjs_count;
    intptr_t robjs[MAX_ROBJS];

    g_terminate_loop = 0;
    g_terminate_status = 0;
    g_login_info = NULL;

    while (!g_terminate_loop)
    {
        robjs_count = 0;
        robjs[robjs_count++] = g_term_event;
        robjs[robjs_count++] = g_sigchld_event;

        // ECP transport may be null if sesman has gone away
        if (g_ecp_trans != NULL)
        {
            error = trans_get_wait_objs(g_ecp_trans, robjs, &robjs_count);
            if (error != 0)
            {
                LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                    "trans_get_wait_objs(ECP) failed");
                sesexec_terminate_main_loop(error);
                continue;
            }
        }

        // CCP transport is set if we have an xrdp connection
        if (g_ccp_trans != NULL)
        {
            error = trans_get_wait_objs(g_ccp_trans, robjs, &robjs_count);
            if (error != 0)
            {
                LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                    "trans_get_wait_objs(CCP) failed");
                sesexec_terminate_main_loop(error);
                continue;
            }
        }

        // Add any objects from the discover module
        error = sesexec_discover_get_wait_objs(robjs, &robjs_count, MAX_ROBJS);
        if (error != 0)
        {
            LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                "sesexec_discover_get_wait_objs() failed");
            sesexec_terminate_main_loop(error);
            continue;
        }

        if (g_obj_wait(robjs, robjs_count, NULL, 0, -1) != 0)
        {
            /* should not get here */
            LOG(LOG_LEVEL_WARNING, "sesexec_main_loop: "
                "Unexpected error from g_obj_wait()");
            g_sleep(100);
            continue;
        }

        if (g_is_wait_obj_set(g_term_event)) /* term */
        {
            g_reset_wait_obj(g_term_event);
            if (session_active(g_session_data))
            {
                LOG(LOG_LEVEL_INFO, "sesexec_main_loop: "
                    "sesexec asked to terminate with active session.");
            }
            else
            {
                LOG(LOG_LEVEL_INFO, "sesexec_main_loop: "
                    "sesexec asked to terminate. "
                    "No session is active");
            }
            sesexec_terminate_main_loop(0);
            continue;
        }

        if (g_is_wait_obj_set(g_sigchld_event)) /* SIGCHLD */
        {
            g_reset_wait_obj(g_sigchld_event);

            // See whether the session goes from active to inactive
            // after processing SIGCHLD
            int session_was_active = session_active(g_session_data);
            session_process_sigchld_event(g_session_data);
            if (session_was_active && !session_active(g_session_data))
            {
                // We've finished the session. Tell sesman, xrdp and
                // finish up.
                if (g_ecp_trans != NULL)
                {
                    (void)ercp_send_session_finished_event(g_ecp_trans);
                }

                sesexec_terminate_connected_xrdp_process(
                    CCP_CLOSE_LOGOFF_BY_USER);

                session_data_free(g_session_data);
                g_session_data = NULL;
                sesexec_terminate_main_loop(0);
                continue;
            }
        }

        if (g_ecp_trans != NULL)
        {
            error = trans_check_wait_objs(g_ecp_trans);
            if (error != 0)
            {
                if (g_ecp_trans->status != TRANS_STATUS_UP &&
                        session_active(g_session_data))
                {
                    // sesman has gone away. We have an active session
                    // to keep track of, so sesman can pick it up when it
                    // restarts
                    LOG(LOG_LEVEL_INFO, "sesexec_main_loop: "
                        "sesman has exited");
                    sesexec_set_ecp_transport(NULL);
                }
                else
                {
                    // A callback has failed, or sesman has gone away and
                    // we have no active session
                    LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                        "trans_check_wait_objs failed for ECP transport");
                    sesexec_terminate_main_loop(error);
                }
                continue;
            }
        }

        if (g_ccp_trans != NULL)
        {
            error = trans_check_wait_objs(g_ccp_trans);
            if (error != 0)
            {
                if (g_ccp_trans->status != TRANS_STATUS_UP)
                {
                    // xrdp has gone away.
                    LOG(LOG_LEVEL_INFO, "sesexec_main_loop: "
                        "xrdp connection has exited (client '%s')",
                        g_client_name);

                    g_client_ip[0] = '\0';
                    g_client_name[0] = '\0';
                    g_last_connect_disconnect = time(NULL);

                    if (g_ecp_trans != NULL)
                    {
                        (void)ercp_send_client_disconnect_event(
                            g_ecp_trans, g_last_connect_disconnect);

                    }
                    close_ccp_trans();
                }
                else
                {
                    // A callback has failed. This shouldn't really happen.
                    // Try to signal a software failure to the xrdp process
                    LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                        "trans_check_wait_objs failed for CCP transport");
                    sesexec_terminate_connected_xrdp_process(
                        CCP_CLOSE_SOFTWARE_FAILURE);

                }
                continue;
            }
        }

        error = sesexec_discover_check_wait_objs();
        if (error != 0)
        {
            LOG(LOG_LEVEL_ERROR, "sesexec_main_loop: "
                "sesexec_discover_check_wait_objs failed");
            sesexec_terminate_main_loop(error);
            continue;
        }
    }

    /* close sesman communications immediately */
    sesexec_set_ecp_transport(NULL);

    /* We should already have notified xrdp of the reason why we are
     * closing, in which case this call has no effect */
    sesexec_terminate_connected_xrdp_process(CCP_CLOSE_SOFTWARE_FAILURE);

    return g_terminate_status;
}

/******************************************************************************/
static int start_logging(const char *sesman_ini)
{
    char text[256];
    int rv = 1;
    if (!g_file_exist(sesman_ini))
    {
        g_printf("Config file %s does not exist\n", sesman_ini);
    }
    else
    {
        enum logReturns log_error;
        log_error = log_start(sesman_ini, "xrdp-sesexec", 0);

        if (log_error != LOG_STARTUP_OK)
        {
            switch (log_error)
            {
                case LOG_ERROR_MALLOC:
                    g_writeln("error on malloc. cannot start logging. quitting.");
                    break;
                case LOG_ERROR_FILE_OPEN:
                    g_writeln("error opening log file [%s]. quitting.",
                              getLogFile(text, sizeof(text) - 1));
                    break;
                default:
                    // Assume sufficient messages have already been generated
                    break;
            }
        }
        else
        {
            rv = 0;
        }
    }

    return rv;
}

/******************************************************************************/
static int
get_eicp_fd(char errstr[], unsigned int errstr_size)
{
    const char *s =  g_getenv("EICP_FD");
    const char *p;
    int fd;

    errstr[0] = '\0';

    if (s == NULL || s[0] == '\0')
    {
        g_snprintf(errstr, errstr_size,
                   "Can't read EICP_FD environment variable");
        return -1;
    }

    for (p = s ; isdigit(*p) ; ++p)
    {
        ;
    }

    if (*p != '\0')
    {
        g_snprintf(errstr, errstr_size, "EICP_FD has non-digit char '%c'", *p);
        return -1;
    }

    if ((p - s) > 4)
    {
        g_snprintf(errstr, errstr_size, "EICP_FD has too many digits");
        return -1;
    }

    fd = g_atoi(s);
    if (!g_file_is_open(fd))
    {
        g_snprintf(errstr, errstr_size, "EICP_FD %d is not open", fd);
        return -1;
    }

    return fd;
}

/******************************************************************************/
int
main(int argc, char **argv)
{
    int error = 1;
    struct startup_params startup_params = {0};
    int errored_argc;
    int eicp_fd;
    char eicp_errstr[128];
    /*
     * Check the EICP transport file descriptor is provided and open
     * before opening any log files, config files, etc. We then open
     * log files, and log errors at that point */
    eicp_fd = get_eicp_fd(eicp_errstr, sizeof(eicp_errstr));

    g_init("xrdp-sesexec");

    //g_sleep(15 * 1000);
    errored_argc = process_params(argc, argv, &startup_params);
    if (errored_argc > 0)
    {
        g_writeln("Unknown option: %s", argv[errored_argc]);
    }
    /* starting logging subsystem
     *
     * For historic reasons, we share a log file with sesman */
    else if (start_logging(startup_params.sesman_ini) == 0)
    {
        /* reading config
         *
         * For historic reasons, we share a config with sesman */
        if ((g_cfg = config_read(startup_params.sesman_ini)) == NULL)
        {
            LOG(LOG_LEVEL_ALWAYS, "error reading config %s: %s",
                startup_params.sesman_ini, g_get_strerror());
        }
        else if (eicp_fd < 0)
        {
            LOG(LOG_LEVEL_ERROR, "%s", eicp_errstr);
        }
        else
        {
            char text[128];
            struct trans *t;

            g_pid = g_getpid();

            /* signal handling */
            g_snprintf(text, sizeof(text), "xrdp_sesexec_%8.8x_main_term",
                       g_pid);
            g_term_event = g_create_wait_obj(text);
            g_snprintf(text, sizeof(text), "xrdp_sesexec_%8.8x_sigchld",
                       g_pid);
            g_sigchld_event = g_create_wait_obj(text);

            // No need to terminate on SIGINT for sesexec. This can
            // also make it hard to debug sessions.
            //g_signal_user_interrupt(set_term_event);
            g_signal_terminate(set_term_event); /* SIGTERM */
            g_signal_pipe(sig_no_op);          /* SIGPIPE */
            g_signal_child_stop(set_sigchld_event);

            /* Set up an EICP process handler
             * Errors are logged by this call if necessary */
            t = eicp_init_trans_from_fd(eicp_fd,
                                        TRANS_TYPE_SERVER,
                                        sesexec_is_term);
            if (t != NULL && sesexec_set_ecp_transport(t) == 0)
            {
                g_ecp_trans->trans_data_in = sesexec_eicp_data_in;
                g_ecp_trans->callback_data = NULL;

                /* start program main loop */
                LOG(LOG_LEVEL_INFO, "starting xrdp-sesexec with pid %d", g_pid);
                error = sesexec_main_loop();
                sesexec_main_loop_cleanup();
            }
            g_delete_wait_obj(g_term_event);
        }
        config_free(g_cfg);
        log_end();
    }

    g_deinit();
    return error;
}

/******************************************************************************/
void
sesexec_terminate_connected_xrdp_process(enum ccp_close_reason_type reason)
{
    if (g_ccp_trans != NULL && g_ccp_trans->status == TRANS_STATUS_UP)
    {
        // Ask xrdp to exit, specifying the reason to return to
        // the RDP client (if possible)
        (void)ccp_send_close_connection_request(g_ccp_trans, reason);

        unsigned int start_ms = g_get_elapsed_ms();
        while (1)
        {
            int robjs_count = 0;
            intptr_t robjs[MAX_ROBJS];

            // How long have we been waiting for xrdp to exit?
            unsigned int elapsed = g_get_elapsed_ms() - start_ms;
            if (elapsed > XRDP_EXIT_TIMEOUT)
            {
                // A timeout has occurred
                LOG(LOG_LEVEL_WARNING,
                    "xrdp process failed to exit after %u ms", elapsed);
                break;
            }

            robjs[robjs_count++] = g_term_event;
            if (trans_get_wait_objs(g_ccp_trans, robjs, &robjs_count) != 0)
            {
                // Transport has gone away
                LOG(LOG_LEVEL_WARNING,
                    "xrdp process exited after %u ms", elapsed);
                break;
            }
            if (g_obj_wait(robjs, robjs_count, NULL, 0,
                           XRDP_EXIT_TIMEOUT - elapsed) != 0)
            {
                /* should not get here */
                g_sleep(100);
            }
            else if (g_is_wait_obj_set(g_term_event))
            {
                // Get out as quickly as possible
                break;
            }
            else
            {
                // This will cause trans_get_wait_objs() to return a failure
                // when the end of the data on the socket is reached.
                (void)trans_check_wait_objs(g_ccp_trans);
            }
        }
    }

    close_ccp_trans();
}

/******************************************************************************/
int
sesexec_set_ccp_trans(struct trans *scp_trans)
{
    int rv = 1;
    pid_t pid;

    if (scp_trans == NULL)
    {
        close_ccp_trans();
        rv = 0;
    }
    else if (scp_trans == g_ccp_trans)
    {
        // This would break the memory subsystem!
        LOG(LOG_LEVEL_ERROR, "%s: programming error", __func__);
        rv = 1;
    }
    else if ((rv = g_sck_get_peer_cred(scp_trans->sck, &pid, NULL, NULL)) != 0)
    {
        LOG(LOG_LEVEL_ERROR, "Can't get credentials of xrdp socket [%s]",
            g_get_strerror());
    }
    else
    {
        // Convert the transport to a CCP transport
        ccp_trans_from_scp_trans(scp_trans,
                                 sesexec_ccp_data_in,
                                 NULL);
        trans_delete(g_ccp_trans);
        g_ccp_trans = scp_trans;
        rv = 0;
    }

    return rv;
}
