<?php
$textdomain = $this->profile_magic;
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_friends_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings', 'pm_field_list','pm_send_friend_request_email_content' );
        $content = isset($_POST['pm_send_friend_request_email_content']) ? wp_kses_post($_POST['pm_send_friend_request_email_content']) : '';
        $post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
        $post['pm_send_friend_request_email_content'] = $content;
	if ( !isset( $post['pm_friends_panel'] ) ) {
		$post['pm_friends_panel'] = 0;
    }
	if ( $post!=false ) {
		if ( !isset( $post['pm_friends_panel'] ) ) {
			$post['pm_friends_panel'] = 0;
        }
		if ( !isset( $post['pm_sending_email_on_friend_request'] ) ) {
			$post['pm_sending_email_on_friend_request'] =0;
		}
		if ( !isset( $post['pm_send_message_to_anyusers'] ) ) {
			$post['pm_send_message_to_anyusers'] = 0;
        }
		if ( !isset( $post['pm_show_friend_suggestion'] ) ) {
			$post['pm_show_friend_suggestion'] = 0;
        }
		if ( !isset( $post['pm_allow_sending_request_to_rejected_person'] ) ) {
			$post['pm_allow_sending_request_to_rejected_person'] = 0;
        }

		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_user_settings" id="pm_user_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Friends Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">

      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Allow/ Disallow Making Friends:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_friends_panel" id="pm_friends_panel" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_friends_panel' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'friendshtml')"/>
          <label for="pm_friends_panel"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Toggle Friends functionality on/ off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/friends-system/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div> 
    
      <div class="childfieldsrow" id="friendshtml" style=" 
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_friends_panel', '0' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
     
      
     
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Allow sending request to person who rejected earlier request:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <input name="pm_allow_sending_request_to_rejected_person" id="pm_allow_sending_request_to_rejected_person" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_allow_sending_request_to_rejected_person' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'rejectedhtml')" />
          <label for="pm_allow_sending_request_to_rejected_person"></label>
          
        </div>
        <div class="uimnote"><?php esc_html_e( 'Allow resending of friend requests to users who have earlier rejected the request.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
       <div class="childfieldsrow" id="rejectedhtml" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_allow_sending_request_to_rejected_person', '0' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'After how many days?', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input name="pm_send_request_to_rejected_person_after_days" id="pm_send_request_to_rejected_person_after_days" type="number" min="0" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_send_request_to_rejected_person_after_days', '0' ) ); ?>" />
       
          
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define number of days after which a user can resend friend request to another user who has earlier deleted his/ her request.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      </div>
          
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Send Email Notification', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <input name="pm_sending_email_on_friend_request" id="pm_sending_email_on_friend_request" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_sending_email_on_friend_request' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'requestemailhtml')" />
          <label for="pm_sending_email_on_friend_request"></label>
          
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send an email notifying the users when they receive friend request. This is a global option and applies to all groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
       <div class="childfieldsrow" id="requestemailhtml" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_sending_email_on_friend_request', '0' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
           <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Subject', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="pm_send_friend_request_email_subject" id="pm_send_friend_request_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_send_friend_request_email_subject', __( 'New Friend Request', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
                      
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Subject of the notification email.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                <?php
                $settings                             = array(
					'wpautop'           => false,
					'media_buttons'     => true,
                    'textarea_name'     => 'pm_send_friend_request_email_content',
                    'textarea_rows'     => 20,
                    'tabindex'          => '',
                    'tabfocus_elements' => ':prev,:next',
                    'editor_css'        => '',
                    'editor_class'      => '',
                    'teeny'             => false,
                    'dfw'               => false,
                    'tinymce'           => true, // <-----
                    'quicktags'         => true,
                );
                $pm_send_friend_request_email_content = $dbhandler->get_global_option_value( 'pm_send_friend_request_email_content', __( '{{display_name}} send you a friend request.', 'profilegrid-user-profiles-groups-and-communities' ) );
                ?>
	    
                <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                        <?php add_action( 'media_buttons', array( $this, 'pm_fields_list_for_email' ) ); ?>
                        <?php wp_editor( $pm_send_friend_request_email_content, 'pm_send_friend_request_email_content', $settings ); ?>
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Body of the notification email.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
           
      </div>   
          
     
      
      </div>
      
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_friends_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
