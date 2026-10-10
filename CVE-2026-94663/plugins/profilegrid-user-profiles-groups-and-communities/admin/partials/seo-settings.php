<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_seo_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
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
  <form name="pm_seo_settings" id="pm_seo_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'SEO', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Profile Page Title', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_user_profile_seo_title" id="pg_user_profile_seo_title" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pg_user_profile_seo_title', '{{display_name}} | ' . get_bloginfo( 'name' ) ) ); ?>" />

        </div>
        <div class="uimnote"><?php esc_html_e( 'Define pattern for HTML page title for user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Profile Description (HTML Meta)', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <textarea name="pg_user_profile_seo_desc" id="pg_user_profile_seo_desc"><?php echo wp_kses_post( $dbhandler->get_global_option_value( 'pg_user_profile_seo_desc', "{{display_name}} is on {{site_name}}. Join {{site_name}} to view {{display_name}}'s profile" ) ); ?></textarea>

        </div>
        <div class="uimnote"><?php esc_html_e( 'Define pattern for HTML page description meta for user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
       
    <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_seo_settings' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
