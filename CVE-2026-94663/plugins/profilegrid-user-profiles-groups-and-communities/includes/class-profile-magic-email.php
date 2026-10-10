<?php
class PM_Emails {

	private function pg_get_user_email_by_id( $user_id ) {
		$user = get_userdata( absint( $user_id ) );
		if ( ! $user || empty( $user->user_email ) || ! is_email( $user->user_email ) ) {
			return '';
		}

		return $user->user_email;
	}

	private function pg_normalize_mail_text( $value, $fallback = '' ) {
		if ( null === $value || false === $value ) {
			return (string) $fallback;
		}

		return is_string( $value ) ? $value : (string) $fallback;
	}

	public function pm_send_group_based_notification( $gid, $userid, $event = '', $postid = false ) {
                 $dbhandler = new PM_DBhandler();
		$row                = $dbhandler->get_row( 'GROUPS', $gid );
		$tmpl_id            = '';

		if ( !empty( $row ) ) {
			$group_options = maybe_unserialize( $row->group_options );
		}
		if ( isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 && $group_options[ $event ]!='' && $event!='on_admin_removal' && $event!='on_admin_assignment' ) {
			$tmpl_id = $group_options[ $event ];
		}

		if ( isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 && $group_options[ $event ]!='' && $event=='on_admin_assignment' ) {
			$tmpl_id = $group_options[ $event ];
		}

		if ( isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 && $group_options[ $event ]!='' && $event=='on_admin_removal' ) {
			$tmpl_id = $group_options[ $event ];
		}

		if ( $tmpl_id!='' ) {
			$this->pm_send_user_notification( $tmpl_id, $userid, $postid, false, $gid );
		}

	}

	public function pm_send_group_based_notification_to_group_admin( $gid, $userid, $event = '', $leader = false ) {
            $dbhandler = new PM_DBhandler();
		$row           = $dbhandler->get_row( 'GROUPS', $gid );
		$tmpl_id       = '';

		if ( !empty( $row ) ) {
			$group_options = maybe_unserialize( $row->group_options );
		}
		if ( isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 && $group_options[ $event ]!='' ) {
			$tmpl_id = $group_options[ $event ];
		}

		if ( $tmpl_id!='' ) {
			$this->pm_send_user_notification( $tmpl_id, $userid, false, $leader, $gid );
		}

	}

	public function pm_send_admin_notification( $subject, $message ) {
         $pmrequests         = new PM_request();
        $from_email_address  = $pmrequests->profile_magic_get_from_email();
		$admin_email_address = $pmrequests->profile_magic_get_admin_email();
		$admin_email_address = apply_filters("pg_dislike_profile_notifications_admin", $admin_email_address);
		$headers             = "MIME-Version: 1.0\r\n";
		$headers            .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers            .= 'From:' . $from_email_address . "\r\n";
		if ( is_string( $admin_email_address ) ) {
            wp_mail( maybe_unserialize( $admin_email_address ), $subject, $message, $headers );
        } else {
            wp_mail( $admin_email_address, $subject, $message, $headers );
        }
	}


	public function pm_send_remove_from_group_user_notification( $uid, $gid ) {
         $pmrequests         = new PM_request();
		$from_email_address  = $pmrequests->profile_magic_get_from_email();
		$admin_email_address = $pmrequests->profile_magic_get_admin_email();
		$subject             = __( 'Membership Terminated', 'profilegrid-user-profiles-groups-and-communities' );
		$message             = __( 'Hi,', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
		$message            .= __( 'Your membership for the group {{group_name}} has been terminated by the {{group_admin_label}} on {{site_name}}. You no longer will have access to private areas of the group.', 'profilegrid-user-profiles-groups-and-communities' );
		$message            .= "<br />\r\n\r\n";
		$message            .= __( 'Regards', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n";
		$message             = $this->pm_filter_email_content( $message, $uid, false, $gid );
		$headers             = "MIME-Version: 1.0\r\n";
		$headers            .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers            .= 'From:' . $from_email_address . "\r\n";
		$user_info           = get_userdata( $uid );
		$user_email          = $user_info->user_email;
		wp_mail( $user_email, $subject, $message, $headers );

	}


	public function pm_send_user_notification( $id, $userid, $postid = false, $leader = false, $gid = false ) {
             $pmrequests        = new PM_request();
            $from_email_address = $pmrequests->profile_magic_get_from_email();
            $headers            = "MIME-Version: 1.0\r\n";
            $headers           .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers           .= 'From:' . $from_email_address . "\r\n";
		if ( $leader==false ) {
			$user_info = get_userdata( $userid );
			if ( isset( $user_info->user_email ) ) {
				$user_email = $user_info->user_email;
			}
		} else {
			$user_info = get_userdata( $leader );
			if ( isset( $user_info->user_email ) ) {
				$user_email = $user_info->user_email;
			}
			$is_group_leader = $pmrequests->pg_check_in_single_group_is_user_group_leader( $leader, $gid );

			if ( $is_group_leader==false ) {
				$additional_message =__( 'Note: You are receiving this email from {{group_name}} group since it is without a {{group_admin_label}}. Please define a new {{group_admin_label}} for this group by going to {{edit_group_url}} or consider making it an open group.', 'profilegrid-user-profiles-groups-and-communities' );
			}
		}
            $subject = $this->pm_get_email_subject( $id, $userid, $postid, $gid );
            $message = $this->pm_get_email_content( $id, $userid, $postid, $gid );
		if ( isset( $additional_message ) ) {
			$message .= "<br />\n\r\n\r\n\r";
			$message .= $this->pm_filter_email_content( $additional_message, $userid, $postid, $gid );

		}
		if ( isset( $user_email ) && is_string( $user_email ) ) {
			wp_mail( maybe_unserialize( $user_email ), $subject, $message, $headers ); //Sends email to user on successful registration
		} else {
			wp_mail( $user_email, $subject, $message, $headers ); //Sends email to user on successful registration
		}
	}

	public function getInbetweenStrings( $start, $end, $str ) {
         $matches = array();
		$regex    = "/$start([a-zA-Z0-9_]*)$end/";
		preg_match_all( $regex, $str, $matches );
		return $matches;
	}

	public function pm_get_email_content( $id, $userid, $postid = false, $gid = false ) {
                         $dbhandler = new PM_DBhandler();
                        $pmrequests = new PM_request();
			$row                    = $dbhandler->get_row( 'EMAIL_TMPL', $id );
		if ( !empty( $row ) ) {
			$email_body =$row->email_body;
		} else {
			$email_body = '';
		}
			$message = $this->pm_filter_email_content( $email_body, $userid, $postid, $gid );
			return $message;
	}

	public function pm_filter_email_content( $message, $userid, $postid = false, $gid = false ) {
		$pmrequests = new PM_request();
		$matches    = $this->getInbetweenStrings( '{{', '}}', $message );
		$result     = $matches[1];

		foreach ( $result as $field ) {
				$search = '{{' . $field . '}}';
				$value  = $pmrequests->profile_magic_get_user_field_value( $userid, $field );
			if ( 'user_pass' === $field ) {
				$value = esc_html__( 'Set or reset your password:', 'profilegrid-user-profiles-groups-and-communities' ) . ' ' . esc_url( wp_lostpassword_url() );
			}
			if ( $field=='pm_activation_code' ) {
				$value = $pmrequests->pm_create_user_activation_link( $userid, $value );
			}

			if ( $field == 'post_name' || $field == 'post_link' || $field == 'edit_post_link' ) {
				$value = $pmrequests->pg_get_blog_post_data( $postid, $field );
			}

			if ( $field=='sender_name' ) {
				$value = $pmrequests->profile_magic_get_user_field_value( $postid, 'display_name' );
			}
                        
                        if ( $field=='site_name' ) {
				$value = get_bloginfo( 'name' ) ;
			}

			if ( $field=='group_admin_label' && $gid!=false ) {
				$value = $pmrequests->pm_get_group_admin_label( $gid );
			}

			if ( $field=='group_name' && $gid!=false ) {
				$dbhandler = new PM_DBhandler();
				$group     = $dbhandler->get_row( 'GROUPS', $gid );
				$value     = ( $group && isset( $group->group_name ) ) ? $group->group_name : '';
			}
                        if ( $field=='registration_url' && $gid!=false ) {
                            
				$registration_url = $pmrequests->profile_magic_get_frontend_url( 'pm_registration_page', '' );
				$value = add_query_arg( 'gid', $gid, $registration_url );
				
			}
                        
                        if ( $field=='group_url' && $gid!=false ) {
                            
                            $value = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
				
			}
                        
                       
				//$group_url        = add_query_arg( 'gid', $group->id, $group_url );
                                

			if ( $field=='edit_group_url' && $gid!=false ) {
				$value = admin_url( 'admin.php?page=pm_add_group&id=' ) . $gid;
			}

				$message = str_replace( $search, $value, $message );
		}
		return $message;
	}

	public function pm_get_email_subject( $id, $userid = false, $postid = false, $gid = false ) {
                         $dbhandler = new PM_DBhandler();
			$subject                = $dbhandler->get_value( 'EMAIL_TMPL', 'email_subject', $id, 'id' );
		if ( $userid!=false ) {
			$subject = $this->pm_filter_email_content( $subject, $userid, $postid, $gid );
		}
			return $subject;
	}



	public function pm_send_activation_link( $userid, $textdomain = 'profilegrid-user-profiles-groups-and-communities' ) {
		$pmrequests         = new PM_request();
		$dbhandler          = new PM_DBhandler();
		$from_email_address = $pmrequests->profile_magic_get_from_email();
		$tmpl_id            = $dbhandler->get_global_option_value( 'pm_user_activation_email_tmpl', 0 );
		$headers            = "MIME-Version: 1.0\r\n";
		$headers           .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers           .= 'From:' . $from_email_address . "\r\n";
		//$user_email = get_user_meta($userid,'user_email',true);
		$user_email               = $pmrequests->profile_magic_get_user_field_value( $userid, 'user_email' );
		$subject                  = $dbhandler->get_global_option_value( 'pm_activation_email_subject', __( 'Your Registration is Pending Approval', 'profilegrid-user-profiles-groups-and-communities' ) );
		$message                  = __( 'You are now registered at {{site_name}}.', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
		$message                 .= __( 'Before you can login, you need to activate your account by visiting this link:', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
		$message                 .= "<a href='{{pm_activation_code}}'>{{pm_activation_code}}</a>";
		$message                 .= "<br />\r\n\r\n";
		$message                 .= __( 'Thanks!', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n";
		$pm_activation_email_body = $dbhandler->get_global_option_value( 'pm_activation_email_body', $message );

		$pm_activation_email_body_content = $this->pm_filter_email_content( $pm_activation_email_body, $userid );

		if ( isset( $user_email ) && is_string( $user_email ) ) {
			wp_mail( maybe_unserialize( $user_email ), $subject, $pm_activation_email_body_content, $headers );//Sends email to user on successful registration
		} elseif ( isset( $user_email ) ) {
			wp_mail( $user_email, $subject, $pm_activation_email_body_content, $headers );//Sends email to user on successful registration
		}
	}

	public function pm_send_invite_link( $email_id, $gid ) {
        $pmrequests         = new PM_request();
		$dbhandler          = new PM_DBhandler();
		$from_email_address = $pmrequests->profile_magic_get_from_email();
		$headers            = "MIME-Version: 1.0\r\n";
		$headers           .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers           .= 'From:' . $from_email_address . "\r\n";
		$user_email         = $email_id;
		$group_name         = $dbhandler->get_value( 'GROUPS', 'group_name', $gid );
		// translators: %s: group name.
		$subject            = sprintf( __( 'Invitation to Join Group - %s', 'profilegrid-user-profiles-groups-and-communities' ), $group_name );
		$registration_url   = $pmrequests->profile_magic_get_frontend_url( 'pm_registration_page', '' );
		$registration_url   = add_query_arg( 'gid', $gid, $registration_url );
		$group_admin_label  = $pmrequests->pm_get_group_admin_label( $gid );
		// translators: 1: group admin label, 2: group name, 3: registration URL.
		$message            = sprintf( __( 'Hello,<br /><br />You have been invited by a %1$s to join group %2$s. Please visit this link to sign up:<br /><br /> %3$s <br /><br /> Regards.', 'profilegrid-user-profiles-groups-and-communities' ), $group_admin_label, $group_name, $registration_url );
		$subject = apply_filters('pm_send_invite_group_email_subject', $subject,$gid);
                $message = apply_filters('pm_send_invite_group_email_content', $message,$gid);
                return wp_mail( $user_email, $subject, $message, $headers ); //Sends email to user on successful registration
	}

	public function pm_send_unread_message_notification( $sid, $rid ) {
         $pmrequests = new PM_request();
		$dbhandler   = new PM_DBhandler();
		$subject     = $this->pg_normalize_mail_text(
			$dbhandler->get_global_option_value( 'pm_unread_message_email_subject', __( 'New Private Message from {{sender_name}}', 'profilegrid-user-profiles-groups-and-communities' ) ),
			__( 'New Private Message from {{sender_name}}', 'profilegrid-user-profiles-groups-and-communities' )
		);
		$message     = $this->pg_normalize_mail_text(
			$dbhandler->get_global_option_value( 'pm_unread_message_email_body', __( 'Hi {{display_name}},<br /><br />You just received a new private message from {{sender_name}}. Visit your profile at {{profile_link}} to make sure you are not missing out on the latest updates.', 'profilegrid-user-profiles-groups-and-communities' ) ),
			__( 'Hi {{display_name}},<br /><br />You just received a new private message from {{sender_name}}. Visit your profile at {{profile_link}} to make sure you are not missing out on the latest updates.', 'profilegrid-user-profiles-groups-and-communities' )
		);

		$from_email_address = $pmrequests->profile_magic_get_from_email();

		$subject    = $this->pm_filter_email_content( $subject, $rid, $sid );
		$message    = $this->pm_filter_email_content( $message, $rid, $sid );
		$headers    = "MIME-Version: 1.0\r\n";
		$headers   .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers   .= 'From:' . $from_email_address . "\r\n";
		$user_email = $this->pg_get_user_email_by_id( $rid );
		if ( '' === $user_email ) {
			return false;
		}

		return wp_mail( $user_email, $subject, $message, $headers );

	}

	public function pm_send_email( $sid, $rid ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();

		$subject            = $this->pg_normalize_mail_text(
			$dbhandler->get_global_option_value( 'pm_send_friend_request_email_subject', __( 'New Friend Request', 'profilegrid-user-profiles-groups-and-communities' ) ),
			__( 'New Friend Request', 'profilegrid-user-profiles-groups-and-communities' )
		);
		$message            = $this->pg_normalize_mail_text(
			$dbhandler->get_global_option_value( 'pm_send_friend_request_email_content', __( '{{display_name}} send you a friend request.', 'profilegrid-user-profiles-groups-and-communities' ) ),
			__( '{{display_name}} send you a friend request.', 'profilegrid-user-profiles-groups-and-communities' )
		);
		$from_email_address = $pmrequests->profile_magic_get_from_email();
		$subject            = $this->pm_filter_email_content( $subject, $sid );
		$message            = $this->pm_filter_email_content( $message, $sid );
		$headers            = "MIME-Version: 1.0\r\n";
		$headers           .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers           .= 'From:' . $from_email_address . "\r\n";
		$user_email         = $this->pg_get_user_email_by_id( $rid );
		if ( '' === $user_email ) {
			return false;
		}

		return wp_mail( $user_email, $subject, $message, $headers );

	}
}

