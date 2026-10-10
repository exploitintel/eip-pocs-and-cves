<?php
class PM_Messenger {
    public function pm_messenger_show_thread_user( $uid ) {
        $dbhandler         = new PM_DBhandler();
        $pmrequests        = new PM_request();
        $user              = get_userdata( $uid );
        $user_info['name'] = $user->user_login;
        $user_info['uid']  = $uid;

        return $user_info;

    }


    public function pm_get_messenger_notification( $timestamp, $activity, $tid ) {
        return wp_json_encode( $this->pm_get_messenger_notification_data( $timestamp, $activity, $tid ) );
    }

    public function pm_get_messenger_notification_data( $timestamp, $activity, $tid ) {
        $pmrequests   = new PM_request();
        $current_user = wp_get_current_user();
        $uid          = $current_user->ID;
        $tid          = absint( $tid );

        if ( $tid > 0 && $activity != '' ) {
            $pmrequests->update_typing_timestamp( $tid, $activity );
        }

        $last_ajax_call = $timestamp != '' ? (int) $timestamp : null;
        $threads        = $pmrequests->pm_get_user_all_threads( $uid );

        if ( empty( $threads ) ) {
            return array();
        }

        $last_change_time      = $threads[0]->timestamp;
        $last_change_in_thread = strtotime( $last_change_time );
        $last_change_in_typing = 0;

        if ( $tid > 0 ) {
            $typing_timestamp      = $pmrequests->get_typing_timestamp( $tid );
            $last_change_in_typing = ( $typing_timestamp ) ? strtotime( $typing_timestamp ) : 0;
        }

        if ( $last_change_in_thread > $last_ajax_call && ( $last_ajax_call != null || $tid == 0 ) ) {
            if ( $tid == 0 ) {
                return array();
            }

            return array(
                'activity'         => $activity,
                'data_changed'     => true,
                'typing_timestamp' => $last_change_in_typing,
                'timestamp'        => $last_change_in_thread,
            );
        }

        if ( $tid > 0 ) {
            $activity = $pmrequests->get_typing_status( $tid );
        } else {
            $activity = 'nottyping';
        }

        return array(
            'activity'         => $activity,
            'data_changed'     => false,
            'typing_timestamp' => $last_change_in_typing,
            'timestamp'        => $last_change_in_thread,
            'timexxx'          => $timestamp,
            'last_ajax'        => $last_ajax_call,
        );
    }
    public function pm_messenger_delete_threads( $tid ) {
        $dbhandler    = new PM_DBhandler();
        $pmrequests   = new PM_request();
        $current_user = wp_get_current_user();
        $uid          = $current_user->ID;
        $pmrequests->update_message_status_to_read( $tid );
        $delete_thread = $pmrequests->delete_thread( $tid );
        if ( $delete_thread > 0 ) {
            return 'true';
        } else {
            return 'false';
        }

    }
    public function pm_messenger_notification_extra_data() {
        $dbhandler    = new PM_DBhandler();
        $pmrequests   = new PM_request();
        $current_user = wp_get_current_user();
        $uid          = $current_user->ID;
        $threads      = $pmrequests->pm_get_user_all_threads( $uid );
        if ( !empty( $threads ) ) {
            $threads = $pmrequests->pm_filter_deleted_threads( $threads );
        }
        $extra_notification_data = array();
        $thread_count            = 0;
        if ( !empty( $threads ) ) {
            foreach ( $threads as $thread ) {
                $thread_status = $thread->status;
                if ( $thread_status == $uid ) {
                    $thread_count++;
                }
            }
        }
        $extra_notification_data['unread_threads'] = $thread_count;
        return wp_json_encode( $extra_notification_data );

    }

    public function pm_get_message_url( $uid ) {
         $messenger_url = '';
        $pmrequests     = new PM_request();
		$dbhandler      = new PM_DBhandler();
        $current_user   = wp_get_current_user();
        //$cur_user_gid = $pmrequests->profile_magic_get_user_field_value($current_user->ID,'pm_group');
        if ( $uid !=$current_user->ID && $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' )==1 ) :
            if ( is_user_logged_in() ) {
                $messenger_url =  $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
                $messenger_url = add_query_arg( 'rid', $uid, $messenger_url );
                if ( false === strpos( $messenger_url, '#pg-messages' ) ) {
                    $messenger_url .= '#pg-messages';
                }
            } else {
                $messenger_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
                $messenger_url = add_query_arg( 'errors', 'loginrequired', $messenger_url );
            }
        endif;

        return esc_url( $messenger_url );
    }
}
