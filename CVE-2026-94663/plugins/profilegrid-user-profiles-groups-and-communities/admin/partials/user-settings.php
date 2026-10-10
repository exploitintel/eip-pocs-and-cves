<?php
$dbhandler              = new PM_DBhandler();
$textdomain             = $this->profile_magic;
$pmrequests             = new PM_request();
$path                   = plugin_dir_url( __FILE__ );
$identifier             = 'SETTINGS';
$pm_default_avatar      = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
$pm_default_cover_image = $dbhandler->get_global_option_value( 'pm_default_cover_image', '' );
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( ! wp_verify_nonce( $retrieved_nonce, 'save_user_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
	}
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	if ( ! isset( $_POST['pm_auto_approval'] ) ) {
		$_POST['pm_auto_approval'] = 0;
	}
	if ( ! isset( $_POST['pm_show_change_password'] ) ) {
		$_POST['pm_show_change_password'] = 0;
	}
	if ( ! isset( $_POST['pm_show_privacy_settings'] ) ) {
		$_POST['pm_show_privacy_settings'] = 0;
	}
	if ( ! isset( $_POST['pm_show_delete_profile'] ) ) {
		$_POST['pm_show_delete_profile'] = 0;
	}
	if ( ! isset( $_POST['pm_allow_user_to_change_email'] ) ) {
		$_POST['pm_allow_user_to_change_email'] = 0;
	}
	if ( ! isset( $_POST['pm_allow_user_to_hide_their_profile'] ) ) {
		$_POST['pm_allow_user_to_hide_their_profile'] = 0;
	}
	if ( ! isset( $_POST['pm_send_user_activation_link'] ) ) {
		$_POST['pm_send_user_activation_link'] = 0;
	}
        if ( ! isset( $_POST['pm_show_account_details_settings'] ) ) {
		$_POST['pm_show_account_details_settings'] = 0;
	}
	$post = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post != false ) {
		foreach ( $post as $key => $value ) {
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
		<?php esc_html_e( 'User Accounts', 'profilegrid-user-profiles-groups-and-communities' ); ?>
	  </div>
	  
	  <div class="uimsubheader">
		<?php
		// Show subheadings or message or notice
		?>
	  </div>
	   
	   
	  <div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'WP Registration Auto Approval:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_auto_approval" id="pm_auto_approval" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_auto_approval' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'enable_auto_approval_html')" />
		  <label for="pm_auto_approval"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Automatically activate user accounts after registration form is submitted. Keep this setting off if you want to manually approve each registering user.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/user-accounts-settings/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
	  </div>
		
	  <div class="childfieldsrow" id="enable_auto_approval_html" style=" 
	  <?php
		if ( $dbhandler->get_global_option_value( 'pm_auto_approval', 0 ) == 1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
		">
			 <div class="uimrow">
				<div class="uimfield">
				  <?php esc_html_e( 'Send user Activation link in email:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
				</div>
				<div class="uiminput">
				   <input name="pm_send_user_activation_link" id="pm_send_user_activation_link" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_send_user_activation_link' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'send_user_activation_email_html')" />
				  <label for="pm_send_user_activation_link"></label>
				</div>
				<div class="uimnote"><?php esc_html_e( 'Send an activation link to user in an email. Keep this setting "off", if you want to automatically approve each registered user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			 </div>
		   
		   
		  <div class="childfieldsrow" id="send_user_activation_email_html" style=" 
		  <?php
			if ( $dbhandler->get_global_option_value( 'pm_send_user_activation_link', 0 ) == 1 ) {
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
					 <input type="text" name="pm_activation_email_subject" id="pm_activation_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_activation_email_subject', __( 'Your Registration is Pending Approval', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
					   
					</div>
					<div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
				 </div>
				<?php
				$settings                 = array(
					'wpautop'           => false,
					'media_buttons'     => true,
					'textarea_name'     => 'pm_activation_email_body',
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
				$message                  = __( 'You are now registered at {{site_name}}.', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
				$message                 .= __( 'Before you can login, you need to activate your account by visiting this link:', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
				$message                 .= "<a href='{{pm_activation_code}}'>{{pm_activation_code}}</a>";
				$message                 .= "<br />\r\n\r\n";
				$message                 .= __( 'Thanks!', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n";
				$pm_activation_email_body = $dbhandler->get_global_option_value( 'pm_activation_email_body', $message );
				?>
		
				<div class="uimrow">
					<div class="uimfield">
					  <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
					</div>
					<div class="uiminput">
						<?php wp_editor( $pm_activation_email_body, 'pm_activation_email_body', $settings ); ?>
					</div>
					<div class="uimnote"><?php esc_html_e( 'Content of the email sent to the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
				 </div>
				
		   
		   
		   
	  </div>
		   
		   
	  </div>
            <div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Password reset content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
                <div class="uiminput">
                    <?php
				$reset_settings                 = array(
					'wpautop'           => false,
					'media_buttons'     => true,
					'textarea_name'     => 'pm_password_reset_email_content',
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
				$reset_message                  = __( 'Hello', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
				
                                $reset_message                  .= __( 'You asked us to reset your password for your account using the email address {{pg_user_email}} .', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
				$reset_message                 .= __( 'If this was a mistake, or you didn\'t ask for a password reset, just ignore this email and nothing will happen.', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n\r\n";
				$reset_message                 .= __('To reset your password, visit the following address','profilegrid-user-profiles-groups-and-communities') . "\r\n\r\n";
                                $reset_message                 .= "<a href='{{pg_password_reset_link}}'>{{pg_password_reset_link}}</a>";
				$reset_message                 .= "<br />\r\n\r\n";
				$reset_message                 .= __( 'Thanks!', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n";
				$pm_password_reset_email_content = $dbhandler->get_global_option_value( 'pm_password_reset_email_content', $reset_message );
				?>
                    <?php wp_editor( $pm_password_reset_email_content, 'pm_password_reset_email_content', $reset_settings ); ?>
		</div>
		<div class="uimnote"><?php esc_html_e( "HTML content of the password reset email. Use Variable {{pg_user_email}}, {{pg_password_reset_link}}", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
            </div>
	   <div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Display Account Details', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_show_account_details_settings" id="pm_show_account_details_settings" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_account_details_settings', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
		  <label for="pm_show_account_details_settings"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( "Turn this setting on to display account details under the profile's 'Settings' tab.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
            
            <div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Display Password Change', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_show_change_password" id="pm_show_change_password" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_change_password', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
		  <label for="pm_show_change_password"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Display an option for logged in users on their profile pages to change their passwords.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>

		<div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Show Privacy Options', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_show_privacy_settings" id="pm_show_privacy_settings" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_privacy_settings' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
		  <label for="pm_show_privacy_settings"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Display a tab for modifying privacy settings in user account section of user profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		
		 <div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Allow Profile Deletion', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_show_delete_profile" id="pm_show_delete_profile" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_delete_profile' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_show_delete_profile_html')" />
		  <label for="pm_show_delete_profile"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Allow users to delete their own profiles. If selected, users will see this option in user account section of their user profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		
	  <div class="childfieldsrow" id="pm_show_delete_profile_html" style=" 
	  <?php
		if ( $dbhandler->get_global_option_value( 'pm_show_delete_profile', 0 ) == 1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
		">
			 <div class="uimrow">
				<div class="uimfield">
				  <?php esc_html_e( 'Account Deletion Warning Text', 'profilegrid-user-profiles-groups-and-communities' ); ?>
				</div>
				<div class="uiminput">
					<textarea name="pm_account_deletion_alert_text" id="pm_account_deletion_alert_text"><?php echo wp_kses_post( $dbhandler->get_global_option_value( 'pm_account_deletion_alert_text', __( 'Are you sure you want to delete your account? This will erase all of your account data from the site. To delete your account enter your password below', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?></textarea> 
				</div>
				<div class="uimnote"><?php esc_html_e( 'Users will see this text as warning when they try to delete their profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			 </div>
	  </div>
	   
		<div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Allow Email Change', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_allow_user_to_change_email" id="pm_allow_user_to_change_email" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_allow_user_to_change_email' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
		  <label for="pm_allow_user_to_change_email"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Allow users to change their registered emails when editing their profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		
		<div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Allow Users to Hide Their Profiles from Directory and Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
		   <input name="pm_allow_user_to_hide_their_profile" id="pm_allow_user_to_hide_their_profile" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_allow_user_to_hide_their_profile' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
		  <label for="pm_allow_user_to_hide_their_profile"></label>
		</div>
		<div class="uimnote"><?php esc_html_e( 'Users can opt not to display their profile cards in user directories and groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		
		<div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Default Profile Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
			   
		  <input id="pm_default_avatar" type="hidden" name="pm_default_avatar" class="icon_id" value="<?php echo esc_attr( $pm_default_avatar ); ?>" />
		  <input id="field_icon_button" name="field_icon_button" class="button pm_choose_image_btn" type="button" value="<?php esc_attr_e( 'Upload Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
		  <br />
		  <span class="pg_profile_image_container" style="
		  <?php
			if ( $pm_default_avatar == '' ) {
				echo 'display:none;';}
			?>
			">
			  <img src="<?php echo esc_url( $pmrequests->pg_get_default_avtar_src() ); ?>" class="pg_upload_image_preview pm_preview_img" width="50" />
								<input type="button" name="pg_remove_image" id="pg_remove_image" class="button" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="pg_remove_profile_image()"/>
								</span>
		  <div class="errortext"></div>
		   
		</div>
		<div class="uimnote"><?php esc_html_e( 'Displays this image when a user has not selected or removed his/ her profile image.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		
		
		<div class="uimrow">
		<div class="uimfield">
		  <?php esc_html_e( 'Default Cover Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		<div class="uiminput">
			   
		  <input id="pm_default_cover_image" type="hidden" name="pm_default_cover_image" class="cover_icon_id" value="<?php echo esc_attr( $pm_default_cover_image ); ?>" />
		  <input id="field_cover_icon_button" name="field_cover_icon_button" class="button" type="button" value="<?php esc_attr_e( 'Upload Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
		  <br />
		  <span class="pg_cover_image_container" style="
		  <?php
			if ( $pm_default_cover_image == '' ) {
				echo 'display:none;';}
			?>
			">
			  <img src="<?php echo esc_url( $pmrequests->pg_get_default_cover_image_src() ); ?>" class="pg_upload_cover_image_preview pm_preview_img" width="50" />
								<?php // echo get_avatar($user->user_email,50,'',false,array('class'=>'pm-user','id'=>'pg_upload_image_preview')); ?>
								<input type="button" name="pg_remove_cover_image" id="pg_remove_cover_image" class="button" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="pm_remove_cover_image()"/>
								</span>
		  <div class="errortext"></div>
		   
		</div>
		<div class="uimnote"><?php esc_html_e( 'Displays this image when a user has not selected or removed his/ her cover image.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	  </div>
		<?php do_action('profilemagic_user_settings_field'); ?>
	  <div class="buttonarea"> <a href="admin.php?page=pm_settings">
		<div class="cancel">&#8592; &nbsp;
		  <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		</div>
		</a>
		<?php wp_nonce_field( 'save_user_settings' ); ?>
		<input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
		<div class="all_error_text" style="display:none;"></div>
	  </div>
	</div>
  </form>
</div>
