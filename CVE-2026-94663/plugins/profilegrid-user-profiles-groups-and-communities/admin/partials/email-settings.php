<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_email_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( !isset( $post['pm_admin_notification'] ) ) {
			$post['pm_admin_notification'] = 0;
        }
		if ( !isset( $post['pm_enable_smtp'] ) ) {
			$post['pm_enable_smtp'] = 0;
        }
        if ( !isset( $post['pm_admin_account_review_notification'] ) ) {
			$post['pm_admin_account_review_notification'] = 0;
        }
        if ( !isset( $post['pm_admin_account_deletion_notification'] ) ) {
			$post['pm_admin_account_deletion_notification'] = 0;
        }
        if ( !isset( $post['pm_attached_submission_data_admin_email_body'] ) ) {
			$post['pm_attached_submission_data_admin_email_body'] = 0;
        }

        if ( !isset( $post['pm_admin_email'] ) ) {
			$post['pm_admin_email'] = array();
        }
		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
$admin_email = maybe_unserialize( $dbhandler->get_global_option_value( 'pm_admin_email' ) );
if ( !is_array( $admin_email ) ) {
	$admin_email = array( '' );
}
?>

<div class="uimagic">
  <form name="pm_security_settings" id="pm_security_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Email Notification', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Send Notification To Site Admin:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_admin_notification" id="pm_admin_notification" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_admin_notification' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_admin_notification_html')" />
          <label for="pm_admin_notification"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'The site administrator will be notified for each individual registration.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/email-notifications/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
       <div class="childfieldsrow" id="enable_admin_notification_html" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_admin_notification', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
       
       <div class="uimrow" id="field_options_radio_html">
        <div class="uimfield">
          <?php esc_html_e( 'Or Define Recipients Manually:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <ul class="uimradio" id="radio_option_ul_li_field">
              <?php
				foreach ( $admin_email as $optionvalue ) {
					?>
				  <li class="pm_radio_option_field">
				  <span class="pm_handle"></span>
					<input type="text" name="pm_admin_email[]" value="<?php
                    if ( !empty( $optionvalue ) ) {
						echo esc_attr( $optionvalue );}
					?>">
					<span class="pm_remove_field" onClick="remove_pm_radio_option(this)"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
					</li>
							<?php
				}


				?>
         </ul>
         
        <ul class="uimradio pg-add-other-options" id="pm_radio_field_other_option_html">
            <li><a class="pm_click_add_option pg-add-options" maxlength="0" onClick="add_pm_admin_email_option()" onKeyUp="add_pm_admin_email_option()"><?php esc_html_e( 'Click to add option', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
      </ul>
      
         <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( "If you want to notify multiple people about registrations, enter each one's email address individually.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
           
            <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Subject', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                     <input type="text" name="pm_new_user_create_admin_email_subject" id="pm_new_user_create_admin_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_new_user_create_admin_email_subject', __( 'New User Created', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
                      
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                <?php
                $settings                            = array(
					'wpautop'           => false,
					'media_buttons'     => true,
                    'textarea_name'     => 'pm_new_user_create_admin_email_body',
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
                $pm_new_user_create_admin_email_body = $dbhandler->get_global_option_value( 'pm_new_user_create_admin_email_body', __( 'New user created', 'profilegrid-user-profiles-groups-and-communities' ) );
                ?>
	    
                <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                        <?php wp_editor( $pm_new_user_create_admin_email_body, 'pm_new_user_create_admin_email_body', $settings ); ?>
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Content of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
           
           <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Include Form Data', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_attached_submission_data_admin_email_body" id="pm_attached_submission_data_admin_email_body" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_attached_submission_data_admin_email_body' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_attached_submission_data_admin_email_body"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Append field values from registration form submitted by the user during registration process.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
           
           <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Account Needs Review Notification', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_admin_account_review_notification" id="pm_admin_account_review_notification" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_admin_account_review_notification' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'pm_admin_account_review_notification_html')" />
          <label for="pm_admin_account_review_notification"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Administrator will be notified about user account that needs review.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
            <div class="childfieldsrow" id="pm_admin_account_review_notification_html" style=" 
            <?php
            if ( $dbhandler->get_global_option_value( 'pm_admin_account_review_notification', 0 )==1 ) {
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
                     <input type="text" name="pm_account_review_email_subject" id="pm_account_review_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_account_review_email_subject', __( 'New user awaiting review', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
                      
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                <?php
                $settings                     = array(
					'wpautop'           => false,
					'media_buttons'     => true,
                    'textarea_name'     => 'pm_account_review_email_body',
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
                $pm_account_review_email_body = $dbhandler->get_global_option_value( 'pm_account_review_email_body', __( '{{display_name}} has just registered in {{group_name}} group and waiting to be reviewed. To review this member please click the following link: {{profile_link}}', 'profilegrid-user-profiles-groups-and-communities' ) );
                ?>
	    
                <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                        <?php wp_editor( $pm_account_review_email_body, 'pm_account_review_email_body', $settings ); ?>
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Content of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                
                
            </div> 
     
           <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Account Deletion Notification', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_admin_account_deletion_notification" id="pm_admin_account_deletion_notification" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_admin_account_deletion_notification' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'pm_admin_account_deletion_notification_html')" />
          <label for="pm_admin_account_deletion_notification"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Administrator will be notified when a user deletes their account.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
            <div class="childfieldsrow" id="pm_admin_account_deletion_notification_html" style=" 
            <?php
            if ( $dbhandler->get_global_option_value( 'pm_admin_account_deletion_notification', 0 )==1 ) {
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
                        <input type="text" name="pm_account_delete_email_subject" id="pm_account_delete_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_account_delete_email_subject', __( 'Account deleted', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
                      
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                <?php
                $settings                     = array(
					'wpautop'           => false,
					'media_buttons'     => true,
                    'textarea_name'     => 'pm_account_delete_email_body',
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
                $pm_account_delete_email_body = $dbhandler->get_global_option_value( 'pm_account_delete_email_body', __( '{{display_name}} has just deleted their account.', 'profilegrid-user-profiles-groups-and-communities' ) );
                ?>
                <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                    <?php wp_editor( $pm_account_delete_email_body, 'pm_account_delete_email_body', $settings ); ?>
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Content of the email sent to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
            
            </div> 
       </div>
        
        
       
       <div class="uimrow" id="from_email_name_html" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_smtp', 0 )==0 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        <div class="uimfield">
          <?php esc_html_e( 'From Email Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_from_email_name" id="pm_from_email_name" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_from_email_name' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( "The <i>Sender's Name</i> inside the header of emails delivering notifications to the admin and the members.", 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      <div class="uimrow" id="from_email_address_html" style=" 
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_smtp', 0 )==0 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        <div class="uimfield">
          <?php esc_html_e( 'From Email Address', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input type="text" name="pm_from_email_address" id="pm_from_email_address" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_from_email_address' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( "The <i>Reply-to</i> email address inside the emails delivering notifications to the admin and the members. It is a good idea to use an email address different from the admin's email address to avoid being trapped by spam filters. Also users, may directly reply to notifications emails - therefore you can either user an actively monitored email or specifically mention in your email templates that any replies to automated emails will be ignored.", 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      <?php do_action('profile_magic_notification_template'); ?>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Enable SMTP:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_smtp" id="pm_enable_smtp" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_smtp' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_smtp_html','from_email_name_html','from_email_address_html')" />
          <label for="pm_enable_smtp"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( "Route emails from a dedicated email services instead of using your server's mail functionality. Allows a lot more control and better chances to avoid overzealous spam filters.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      
      <div class="childfieldsrow" id="enable_smtp_html" style="
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_smtp', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
      
   	 <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'SMTP Host', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_host" id="pm_smtp_host" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_host' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( 'Host Server name. For e.g.<i>smtp.gmail.com</i> if you wish to use Gmail. Consult your SMTP service provider for exact name.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Type of Encryption:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <select name="pm_smtp_encription" id="pm_smtp_encription">
           <option value="false" <?php selected( $dbhandler->get_global_option_value( 'pm_smtp_encription' ), 'false' ); ?>><?php esc_html_e( 'None', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
           <option value="tls" <?php selected( $dbhandler->get_global_option_value( 'pm_smtp_encription' ), 'tls' ); ?>><?php esc_html_e( 'TLS', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
           <option value="ssl" <?php selected( $dbhandler->get_global_option_value( 'pm_smtp_encription' ), 'ssl' ); ?>><?php esc_html_e( 'SSL', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
      	 </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Encryption supported by your SMTP provider.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'SMTP Port:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_port" id="pm_smtp_port" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_port' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( 'SMTP port. Usually number. For e.g. <i>465</i>', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'SMTP Authentication:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <select name="pm_smtp_authentication" id="pm_smtp_authentication">
           <option value="true" <?php selected( $dbhandler->get_global_option_value( 'pm_smtp_authentication' ), 'true' ); ?>><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
           <option value="false" <?php selected( $dbhandler->get_global_option_value( 'pm_smtp_authentication' ), 'false' ); ?>><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
      	 </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Authentication supported by your SMTP service provider.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'SMTP Username:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_username" id="pm_smtp_username" autocomplete="off" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_username' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Your SMTP Username', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'SMTP Password:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="password" name="pm_smtp_password" id="pm_smtp_password" autocomplete="off"  value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_password' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Your SMTP Password', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'From Email Name:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_from_email_name" id="pm_smtp_from_email_name" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_from_email_name' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( 'The <i>From</i> name in your outgoing emails. For e.g. <i>John Doe</i>, <i>Acme Corp.</i> etc.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'From Email Address:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_from_email_address" id="pm_smtp_from_email_address" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_from_email_address' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Your SMTP Email Address', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Test Outgoing Connection:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input type="text" name="pm_smtp_test_email_address" id="pm_smtp_test_email_address" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_smtp_test_email_address' ) ); ?>">
          <span id="smtptestconn">
      <a class="cancel_button" onclick="pm_test_smtp_connection()"><?php esc_html_e( 'Test', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
      <img src="<?php echo esc_url( $path . 'images/ajax-loader.gif' ); ?>" style="display:none;">
      <span class="result"></span>
      </span>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'For Testing Purpose Only. Once you have filled in all required SMTP details, you can enter an email address here, click "TEST" button and check if the email is sent successfully.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      </div>
     
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_email_settings' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
