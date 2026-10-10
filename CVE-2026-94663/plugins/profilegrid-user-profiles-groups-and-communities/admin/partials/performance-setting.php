<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = 'profilegrid-user-profiles-groups-and-communities';
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_performance_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( !isset( $post['pm_combine_js'] ) ) {
		$post['pm_combine_js'] = 0;
	}
	if ( !isset( $post['pm_combine_css'] ) ) {
		$post['pm_combine_css'] = 0;
	}
        //print_r($post);
	if ( $post!=false ) {


		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}
	do_action( 'profile_magic_save_performance_setting', $post );
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_performance_settings" id="pm_performance_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'ProfileGrid Peformance', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
        
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Combine CSS', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_combine_css" id="pm_combine_css" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_combine_css', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_combine_css"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turning this option on will combine most CSS files being used by ProfileGrid. This may improve the performance by minimizing the number of stylesheet files being loaded.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      
      <?php do_action( 'profile_magic_performance_setting_option' ); ?>
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_performance_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
