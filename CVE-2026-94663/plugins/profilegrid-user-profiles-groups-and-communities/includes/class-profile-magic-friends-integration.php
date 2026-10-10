<?php
class PM_Friends_Functions {

    public function profile_magic_my_friends( $uid ) {
		$dbhandler   = new PM_DBhandler();
		$identifier  = 'FRIENDS';
		$where       = array(
			'user1'  =>$uid,
			'status' =>2,
		);
		$additional  = "or user2=$uid and status=2";
		$friends     = $dbhandler->get_all_result( $identifier, array( 'user1', 'user2' ), $where, 'results', 0, false, 'id', 'DESC', $additional );
		$friends_id  = array();
		$friends_ids = array();
		if ( isset( $friends ) ) :
			foreach ( $friends as $friend ) {
                        $user1 = get_userdata( $friend->user1 );
                        $user2 = get_userdata( $friend->user2 );

				if ( $user1 ) {
					$user1_status = get_user_meta( $user1->ID, 'rm_user_status', true );
					if ( $user1_status==0 ) {
						$friends_id[] = $friend->user1;
					}
				}

				if ( $user2 ) {
					$user2_status = get_user_meta( $user2->ID, 'rm_user_status', true );
					if ( $user2_status==0 ) {
						$friends_id[] = $friend->user2;
					}
				}
			}
			$friends_ids =  array_diff( array_unique( $friends_id ), array( $uid ) );
		endif;
		return $friends_ids;

	}

	public function pm_count_my_friends( $uid ) {
		$friends = $this->profile_magic_my_friends( $uid );
		return count( $friends );
	}

	public function pm_count_my_friend_requests( $uid, $send = 0 ) {
        $friends = $this->profile_magic_my_friends_requests( $uid, $send );
		return count( $friends );
	}

	public function profile_magic_my_friends_requests( $uid, $send = 0 ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'FRIENDS';
		if ( $send==1 ) {
			$where = array(
				'user1'  =>$uid,
				'status' =>1,
			);
		} else {
			$where = array(
				'user2'  =>$uid,
				'status' =>1,
			);
		}

			$friends     = $dbhandler->get_all_result( $identifier, array( 'user1', 'user2' ), $where, 'results', 0, false, 'id', 'DESC' );
			$friends_id  = array();
			$friends_ids = array();
		if ( isset( $friends ) ) :
			foreach ( $friends as $friend ) {
                    $user1     = get_userdata( $friend->user1 );
                        $user2 = get_userdata( $friend->user2 );

				if ( $user1 ) {
					$user1_status = get_user_meta( $user1->ID, 'rm_user_status', true );
					if ( $user1_status==0 ) {
						$friends_id[] = $friend->user1;
					}
				}

				if ( $user2 ) {
					$user2_status = get_user_meta( $user2->ID, 'rm_user_status', true );
					if ( $user2_status==0 ) {
						$friends_id[] = $friend->user2;
					}
				}
			}
			$friends_ids =  array_diff( array_unique( $friends_id ), array( $uid ) );
		endif;
			return $friends_ids;

	}

	public function profile_magic_get_friends_of_friends( $uid ) {
		$friends_of_friends = array();
		$identifier         = 'FRIENDS';
		$suggestions        = array();
		$my_friends         = $this->profile_magic_my_friends( $uid );
		foreach ( $my_friends as $myfriend ) {
			$suggestion  = $this->profile_magic_my_friends( $myfriend );
			$suggestions =  array_merge( $suggestion, $suggestions );
		}

		$friends_of_friends = array_diff( $suggestions, $my_friends );

		return array_diff( array_unique( $friends_of_friends ), array( $uid ) );
	}

	public function profile_magic_group_member_suggestion( $uid, $exclude = array() ) {
         $dbhandler = new PM_DBhandler();
		$members    = array();
		$gid        = get_user_meta( $uid, 'pm_group', true );
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'pm_group',
				'value'   => $gid,
				'compare' => 'IN',
			),
			array(
				'key'     => 'rm_user_status',
				'value'   => '0',
				'compare' => '=',
			),
		);

		$users =  $dbhandler->pm_get_all_users( '', $meta_query, '', '', '', 'ASC', 'ID', $exclude );
		foreach ( $users as $user ) {
			 $members[] = $user->ID;
		}
		return $members;

	}

	public function profile_magic_site_member_suggestion( $uid, $exclude = array() ) {
		$dbhandler  = new PM_DBhandler();
		$members    = array();
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'pm_user_status',
				'value'   => '1',
				'compare' => '=',
			),
		);

		$users =  $dbhandler->pm_get_all_users( '', $meta_query, '', '', '', 'ASC', 'ID', $exclude );
		foreach ( $users as $user ) {
			 $members[] = $user->ID;
		}
		return $members;

	}

	public function profile_magic_friends_suggestion( $uid ) {
                 $dbhandler = new PM_DBhandler();
		$suggested_members  = array();
		$myfriends          = $this->profile_magic_my_friends( $uid );
		$friends_of_friends = $this->profile_magic_get_friends_of_friends( $uid );
		//
		if ( empty( $friends_of_friends ) ) {
			$friends_of_friends =array();
        }
		$group_members      = $this->profile_magic_group_member_suggestion( $uid, $friends_of_friends );
		$suggestions        =  array_merge( $friends_of_friends, $group_members );
		$other_group_member = $this->profile_magic_site_member_suggestion( $uid, $suggestions );
		$suggested_members  =  array_merge( $suggestions, $other_group_member );

		$myfriends[]       = $uid;
		$suggested_friends = array_diff( $suggested_members, $myfriends );
		$final_result      = array();
		foreach ( $suggested_friends as $suggested ) {
                    $is_removed_from_suggestion = $this->profile_magic_is_exist_in_table( $uid, $suggested );
			if ( $dbhandler->get_global_option_value( 'pm_allow_sending_request_to_rejected_person', '0' ) ) :
				$days = $dbhandler->get_global_option_value( 'pm_send_request_to_rejected_person_after_days', '0' );
				if ( $days > 0 && isset( $is_removed_from_suggestion ) ) {
					$current_date = gmdate( 'Y-m-d h:i:s' );
					$diff         = ( strtotime( $current_date ) - strtotime( $is_removed_from_suggestion->action_date ) ) / ( 60 * 60 * 24 );
					if ( $diff > $days ) {
						$final_result[] = $suggested;
					}
				} else {
					$final_result[] = $suggested;
				}

                    else :
                        if ( !isset( $is_removed_from_suggestion ) ) {
							$final_result[] = $suggested;
						}
                    endif;

		}
		return $final_result;
	}

	public function profile_magic_friends_result_html( $users, $uid, $view = 1 ) {
                 $pmrequests  = new PM_request();
		$dbhandler            = new PM_DBhandler();
                $PM_Messanger = new PM_Messenger();
		$path                 =  plugin_dir_url( __FILE__ );
		$current_user         = wp_get_current_user();
		$profile_page         = get_permalink( $dbhandler->get_global_option_value( 'pm_user_profile_page' ) );
		$sign                 = strpos( $profile_page, '?' )?'&':'?';
		foreach ( $users as $entry ) :
			$avatar          = get_avatar( $entry->user_email, 30, '', false, array( 'force_display'=>true ) );
			$gids            = $pmrequests->profile_magic_get_user_field_value( $entry->ID, 'pm_group' );
                        $gid = $pmrequests->pg_filter_users_group_ids( $gids );
			if ( !empty( $gid ) ) {
				$groupinfo = $dbhandler->get_row( 'GROUPS', $gid[0] );
			}
			$profile_url              = $pmrequests->pm_get_user_profile_url( $entry->ID );
                        $login_status = ( $pmrequests->pm_get_user_online_status( $entry->ID )==1 ?'pm-online':'pm-offline' );
                        $u2           = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $entry->ID );
			?>

            <div class="pm-myfriends-list-wrap pm-difl">
                <div class="pm-myfriends-list ">
                <?php echo get_avatar( $entry->user_email, 85, '', false, array( 'force_display'=>true ) ); ?>
                    <div class="pm-myfriends-overlay"></div>
                    
                    <?php $this->profile_magic_friends_action_button( $uid, $entry->ID, $view ); ?>
                </div>
                
                <div class="pm-friend-info pm-dbfl">
                                <span class="pm-friend-status <?php echo esc_attr( $login_status ); ?>"></span>
                                <div class="pm-friend-name "><a href="<?php echo esc_url( $profile_url ); ?>"><?php echo wp_kses_post( $pmrequests->pm_get_display_name( $entry->ID ) ); ?></a></div>
                            </div> 
                
            </div>
            <?php
            endforeach;
	}

	public function profile_magic_friends_action_button( $uid, $uid2, $view ) {
         $pmrequests = new PM_request();

		$PM_Messanger = new PM_Messenger();
		$u1           = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $uid );
		$u2           = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $uid2 );
		$current_user = wp_get_current_user();
		if ( $uid==$current_user->ID ) :
            switch ( $view ) {
                case 1:
                    $this->profile_magic_friend_select_button( $u2, 'pm-my-friends-select-checkbox' );
                    $this->profile_magic_message_button( $uid2 );
                    $this->profile_magic_unfriend_button( $u1, $u2 );
                    break;
                case 2:
                    $this->profile_magic_friend_select_button( $u2, 'pm-request-friends-select-checkbox' );
                    $this->profile_magic_accept_button( $u1, $u2 );
                    $this->profile_magic_reject_button( $u1, $u2 );
                    break;
                case 3:
                    $this->profile_magic_friend_select_button( $u2, 'pm-request-sent-select-checkbox' );
                    $this->profile_magic_cancel_request_button( $u1, $u2 );
                    break;
            }
            endif;
	}

	public function profile_magic_message_button( $uid ) {
		$dbhandler    = new PM_DBhandler();
		$PM_Messanger = new PM_Messenger();
		if ( $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' )==1 ) :
				$messenger_url = $PM_Messanger->pm_get_message_url( $uid );
			?>
                    <div class="pm-friend-message-button">
                        <a id="pm-message-url" class="pm-color" href="<?php echo esc_url( $messenger_url ); ?>" ><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
                    </div>
                <?php
                endif;
	}

	public function profile_magic_unfriend_button( $u1, $u2 ) {
		?>
                <div class="pm-friend-message-Remove"><a onclick="pm_unfriend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)" ><?php esc_html_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
            <?php
	}

	public function profile_magic_friend_select_button( $u2, $class ) {
		?>
               <div class="pm-friend-select">
                          <label class="pm-color"> 
                              <input type="checkbox" name="<?php echo esc_attr( $class ) . '[]'; ?>" id="<?php echo esc_attr( $class ); ?>" class="pm-friends-select-checkbox <?php echo esc_attr( $class ); ?>" value="<?php echo esc_attr( $u2 ); ?>" onclick="pm_select_friend_checkbox(this)" /> <?php esc_html_e( 'Select', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                        </label>
                    </div>
            <?php
	}

	public function profile_magic_accept_button( $u1, $u2 ) {
		?>
                <div class="pm-friend-message-button pm-friend-accept_button"><a onclick="pm_confirm_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)" ><?php esc_html_e( 'Accept', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
            <?php
	}

	public function profile_magic_reject_button( $u1, $u2 ) {
		?>
                <div class="pm-friend-message-Remove pm-friend-reject_button"><a onclick="pm_reject_friend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)" ><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
            <?php
	}

	public function profile_magic_cancel_request_button( $u1, $u2 ) {
		?>
                <div class="pm-friend-message-Remove pm-friend-cancel_button"><a onclick="pm_cancel_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)" ><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
            <?php
	}

	public function profile_magic_friend_list_button( $user1, $user2, $action = 0 ) {
             $pmrequests    = new PM_request();
            $dbhandler      = new PM_DBhandler();
            $identifier     = 'FRIENDS';
            $exist_in_table = $this->profile_magic_is_exist_in_table( $user1, $user2 );
            $u1             = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $user1 );
            $u2             = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $user2 );
		if ( isset( $exist_in_table ) ) {
			switch ( $exist_in_table->status ) {
				case 1:
					if ( $user1 == $exist_in_table->user1 ) {
						?>
                                    <span class="pm-color pm_add_friend_request" onclick="pm_cancel_request_rightside('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Cancel Request', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
							<?php
					} else {
						?>
                                    <span class="pm-color pm_add_friend_request" onclick="pm_confirm_request_right_side('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Accept', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                                    <span class="pm-color pm_add_friend_request" onclick="pm_reject_friend_request_right_side('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
							<?php
					}
			        break;
				case 2:
					?>
                                <span class="pm-color pm_add_friend_request" onclick="pm_unfriend_request_rightside('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'UnFriend', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
							<?php
                    break;
				case 3:
				case 6:
					if ( $dbhandler->get_global_option_value( 'pm_allow_sending_request_to_rejected_person', '0' )==1 ) :
							$days = $dbhandler->get_global_option_value( 'pm_send_request_to_rejected_person_after_days', '0' );
						if ( $days > 0 ) {
							$current_date = gmdate( 'Y-m-d h:i:s' );
							$diff         = ( strtotime( $current_date ) - strtotime( $exist_in_table->action_date ) ) / ( 60 * 60 * 24 );
							if ( $diff > $days ) {
								?>
                                                <span class="pm_add_friend_request" onclick="pm_add_friend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Add Friend', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
										<?php
										$dbhandler->remove_row( $identifier, 'id', $exist_in_table->id, '%d' );
							}
						} else {
							?>
                                                <span class="pm_add_friend_request" onclick="pm_add_friend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Add Friend', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
										<?php
										$dbhandler->remove_row( $identifier, 'id', $exist_in_table->id, '%d' );
						}
                                endif;
			        break;
				default:
					?>
                                <span class="pm_add_friend_request" onclick="pm_add_friend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Add Friend', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
							<?php
                    break;

			}
		} else {
				// user able to send request
			if ( is_user_logged_in() ) :
				?>
                                <span class="pm-color pm-add-friend" onclick="pm_add_friend_request('<?php echo esc_attr( $u1 ); ?>','<?php echo esc_attr( $u2 ); ?>',this)"><?php esc_html_e( 'Add Friend', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                <?php
                else :
                    $login_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
                    $username2 = $pmrequests->pm_get_display_name( $user2 );
                    ?>
                                <span><a href="<?php echo esc_url( $login_url ); ?>"><?php echo esc_html__( 'Add Friend', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
                    <?php
						endif;
		}
	}

	public function profile_magic_is_exist_in_table( $user1, $user2 ) {
         $dbhandler = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1' =>$user1,
			'user2' =>$user2,
		);
		$additional = "OR user1=$user2 AND user2=$user1";
		$is_friend  = $dbhandler->get_all_result( $identifier, '*', $where, 'row', 0, false, null, false, $additional );

		return $is_friend;
	}

	public function profile_magic_is_my_friends( $user1, $user2 ) {
         $dbhandler = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1'  =>$user1,
			'user2'  =>$user2,
			'status' =>2,
		);
		$additional = "OR user1=$user2 AND user2=$user1 AND status=2";
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false, $additional );
		return $is_friend;
	}

	public function profile_magic_is_removed_suggestion( $user1, $user2 ) {
         $dbhandler = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1'  =>$user1,
			'user2'  =>$user2,
			'status' =>5,
		);
		$additional = "OR user1=$user2 AND user2=$user1 AND status=5";
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false, $additional );
		return $is_friend;
	}

	public function profile_magic_is_reject_request( $user1, $user2 ) {
         $dbhandler = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1'  =>$user1,
			'user2'  =>$user2,
			'status' =>3,
		);
		$additional = "OR user1=$user2 AND user2=$user1 AND status=3";
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false, $additional );
		return $is_friend;
	}

	public function profile_magic_is_owner_requested( $user1, $user2 ) {
        $dbhandler  = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1'  =>$user1,
			'user2'  =>$user2,
			'status' =>1,
		);
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false );
		return $is_friend;
	}

	public function profile_magic_is_viewver_requested( $user1, $user2 ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user2'  =>$user1,
			'user1'  =>$user2,
			'status' =>1,
		);
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false );
		return $is_friend;
	}

	public function profile_magic_is_viewver_blocked( $user1, $user2 ) {
        $dbhandler  = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user2'  =>$user1,
			'user1'  =>$user2,
			'status' =>4,
		);
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false );
		return $is_friend;
	}

	public function profile_magic_is_owner_blocked( $user1, $user2 ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'FRIENDS';
		$where      = array(
			'user1'  =>$user1,
			'user2'  =>$user2,
			'status' =>4,
		);
		$is_friend  = $dbhandler->get_all_result( $identifier, 'id', $where, 'var', 0, false, null, false );
		return $is_friend;
	}


}
