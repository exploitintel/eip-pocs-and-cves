<?php
$dbhandler  = new PM_DBhandler();
$textdomain = $this->profile_magic;
$pmrequests = new PM_request();
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_profile_nofication_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	if ( !isset( $_POST['pm_enable_live_notification'] ) ) {
		$_POST['pm_enable_live_notification'] = 0;
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
  <form name="pm_profile_notification_settings" id="pm_profile_notification_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Profile Notifications Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
    
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Enable Live Profile Notifications', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_live_notification" id="pm_enable_live_notification" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_live_notification', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_enable_live_notification"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'This option allows you to control whether the notifications on user profiles will update live. If disabled, the notifications will update after each page refresh.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
     
        
   
      <div class="buttonarea"> 
          <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_profile_nofication_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
   
  </form>
</div>
