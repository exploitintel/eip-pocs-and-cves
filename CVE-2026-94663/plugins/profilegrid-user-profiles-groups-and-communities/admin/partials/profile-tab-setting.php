<?php
$dbhandler    = new PM_DBhandler();
$pmrequests   = new PM_request();
$textdomain   = $this->profile_magic;
$path         =  plugin_dir_url( __FILE__ );
$identifier   = 'SETTINGS';
$profile_tabs = $pmrequests->pm_profile_tabs();
$pm_sanitizer = new PM_sanitizer();

if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
        $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_profile_tabs_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    $postobj      = $pm_sanitizer->sanitize( $_POST );
        $tabs = array();
	if ( isset( $postobj['pm_profile_tabs_order_status'] ) ) {
		foreach ( $postobj['pm_profile_tabs_order_status'] as $key=> $pm_tab ) {
			if ( !isset( $pm_tab['status'] ) ) {
				$pm_tab['status'] = '0';
			}
			$tabs[ $key ] = $pm_tab;
		}
	}

	$exclude                                   = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
        $_POST['pm_profile_tabs_order_status'] = $tabs;
	$post                                      = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
               // print_r($post);
		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}

?>

<div class="uimagic">
  <form name="pm_profile_tabs_settings" id="pm_profile_tabs_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Profile Tabs Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
        <div class="pg-profile-tabs-wrap">
        <ul id="pm_sortable_profile_tabs">
        <?php
        if ( !empty( $profile_tabs ) ) :
            foreach ( $profile_tabs as $key=>$tab ) :
				?>
                <li id="<?php echo esc_attr( $key ); ?>">
                    <div class="pm-custom-field-page-slab pg_profile_tab">
                        <div class="pm-slab-drag-handle">&nbsp;</div>
                        <div class="pm-slab-info"><?php echo esc_html( $tab['title'] ); ?></div>
                        <div class="pm-slab-buttons"><span class="dashicons dashicons-arrow-down"></span></div>
                    </div>
                    <div class="pg_profile_tab-setting" style="display:none;">
                      <div class="uimrow">
                        <div class="uimfield">
                              <label for="<?php echo esc_attr( $tab['id'] . '-title' ); ?>"><?php esc_html_e( 'Tab Title', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>       
                         </div>
                          <div class="uiminput">
                              <input type="text" name="pm_profile_tabs_order_status[<?php echo esc_attr( $key ); ?>][title]" id="<?php echo esc_attr( $tab['id'] . '-title' ); ?>" autocomplete="off" value="<?php echo esc_attr( $tab['title'] ); ?>">
                           </div>
                           <div class="uimnote"><?php esc_html_e( 'Tab title for profile tab', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                         </div>


                         <div class="uimrow">
                        <div class="uimfield">
                            <?php esc_html_e( 'Show Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>      
                         </div>
                          <div class="uiminput">
                          <input name="pm_profile_tabs_order_status[<?php echo esc_attr( $key ); ?>][status]" id="<?php echo esc_attr( $tab['id'] . '-status' ); ?>" type="checkbox" <?php checked( $tab['status'], '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                          <label for="<?php echo esc_attr( $tab['id'] . '-status' ); ?>"></label>
                           </div>
                           <div class="uimnote"><?php esc_html_e( 'Profile Tab Show/Hide', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                         </div>


                        <input type="hidden" name="pm_profile_tabs_order_status[<?php echo esc_attr( $key ); ?>][id]" id="<?php echo esc_attr( $tab['id'] . '-id' ); ?>" value="<?php echo esc_attr( $tab['id'] ); ?>" />
                        <input type="hidden" name="pm_profile_tabs_order_status[<?php echo esc_attr( $key ); ?>][class]" id="<?php echo esc_attr( $tab['id'] . '-class' ); ?>" value="<?php echo esc_attr( $tab['class'] ); ?>" />


                    </div>

                </li>
                <?php
            endforeach;
        endif;
        ?>
        
        </ul>
        </div>
        
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_profile_tabs_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
