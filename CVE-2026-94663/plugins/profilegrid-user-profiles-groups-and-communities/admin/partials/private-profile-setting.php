<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_private_profile_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( !isset( $post['pm_enable_private_profile'] ) ) {
			$post['pm_enable_private_profile'] = 0;
        }
		if ( !isset( $post['pm_show_user_profile_on_group_page'] ) ) {
			$post['pm_show_user_profile_on_group_page'] = 0;
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
    <form name="pm_private_profile_settings" id="pm_private_profile_settings" method="post" onsubmit="return add_section_validation()">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Private Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
    
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Enable Private Profile Mode', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_private_profile" id="pm_enable_private_profile" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_private_profile' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_private_profile_html')" />
          <label for="pm_enable_private_profile"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'When enabled, all user profiles will be set to private, accessible only by their owners. Secondary features, such as the chat system will also be hidden to ensure a fully private experience. Please note that any profile-related interactions will be disabled for other users.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="childfieldsrow" id="enable_private_profile_html" style="
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_private_profile', 0 )==1 ) {
			echo esc_attr( 'display:block;' );
		} else {
			echo esc_attr( 'display:none;' );}
		?>
        ">
     
          <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Notice for Other Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <textarea name="pm_private_profile_message" id="pm_private_profile_message"><?php echo esc_html( $dbhandler->get_global_option_value( 'pm_private_profile_message', __( 'You are not authorized to view contents of this page.', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?></textarea>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'This notice will be visible to users who directly reach individual profile page of other users through a link.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
          
          <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Display Profile Cards on Group Pages', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_profile_on_group_page" id="pm_show_user_profile_on_group_page" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_profile_on_group_page', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_profile_on_group_page"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Decide whether you wish to continue displaying user profile cards on Group pages or not. If you choose to display, the cards will no longer link to their respective user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
          
      </div>
     
      
     
     
        
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_private_profile_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
