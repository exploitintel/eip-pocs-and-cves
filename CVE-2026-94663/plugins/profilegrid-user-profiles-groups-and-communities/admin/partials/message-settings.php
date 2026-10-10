<?php
$dbhandler  = new PM_DBhandler();
$textdomain = $this->profile_magic;
$pmrequests = new PM_request();
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_message_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	if ( !isset( $_POST['pm_enable_private_messaging'] ) ) {
		$_POST['pm_enable_private_messaging'] = 0;
    }
	if ( !isset( $_POST['pm_unread_message_notification'] ) ) {
		$_POST['pm_unread_message_notification'] = 0;
	}
	$post = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_message_settings" id="pm_message_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Messaging Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
    
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Enable Private Messaging', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_private_messaging" id="pm_enable_private_messaging" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_enable_private_messaging_html')" />
          <label for="pm_enable_private_messaging"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on private messaging system for your site users. Registered users can start conversations with each other.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/private-messaging/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div class="childfieldsrow" id="pm_enable_private_messaging_html" style=" 
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_private_messaging', 1 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Enable Unread Message Notification', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_unread_message_notification" id="pm_unread_message_notification" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_unread_message_notification' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'pm_unread_message_notification_html')" />
                  <label for="pm_unread_message_notification"></label>
                </div>
                <div class="uimnote"><?php esc_html_e( "User will be notified when there's a new unread private message.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
            </div>
            
          <div class="childfieldsrow" id="pm_unread_message_notification_html" style=" 
          <?php
			if ( $dbhandler->get_global_option_value( 'pm_unread_message_notification', 0 )==1 ) {
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
                     <input type="text" name="pm_unread_message_email_subject" id="pm_unread_message_email_subject" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_unread_message_email_subject', __( 'New Private Message from {{sender_name}}', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?>" />
                      
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                <?php
                $settings                     = array(
					'wpautop'           => false,
					'media_buttons'     => true,
                    'textarea_name'     => 'pm_unread_message_email_body',
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
                $pm_unread_message_email_body = $dbhandler->get_global_option_value( 'pm_unread_message_email_body', __( 'Hi {{display_name}},<br /><br />You just received a new private message from {{sender_name}}. Visit your profile at {{profile_link}} to make sure you are not missing out on the latest updates.', 'profilegrid-user-profiles-groups-and-communities' ) );
                ?>
	    
                <div class="uimrow">
                    <div class="uimfield">
                      <?php esc_html_e( 'Email Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                    <div class="uiminput">
                        <?php wp_editor( $pm_unread_message_email_body, 'pm_unread_message_email_body', $settings ); ?>
                    </div>
                    <div class="uimnote"><?php esc_html_e( 'Content of the email sent to the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>
                
                
            </div> 
            
        </div>
        
   
      <div class="buttonarea"> 
          <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_message_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
   
  </form>
</div>
