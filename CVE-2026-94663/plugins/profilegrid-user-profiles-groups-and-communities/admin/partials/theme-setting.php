<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_general_settings' ) ) {
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
    <form name="pm_general_settings" id="pm_general_settings" method="post" onsubmit="return add_section_validation()">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Templates', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     <div class="uimsubheader">
         
     </div>

        <div class="uimrow">
           <?php
			$themename = $pmrequests->profile_magic_get_pm_theme_name();
			$themepath = $pmrequests->profile_magic_get_pm_theme_path();
			$i         =0;
            foreach ( $themename as $dirname ) {
                $label_array  = explode( '_', $dirname );
                $string_array = array( 'Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten' );
                if ( isset( $label_array[1] ) && is_numeric( $label_array[1] ) ) {
                    $str1  = $label_array[0];
                    $str2  = $string_array[ $label_array[1] ];
                    $label = $str1 . $str2;
                } else {
                    $label = $dirname;
                }
                ?>
            <div class="pg-select-theme">
                <img src="<?php echo esc_url( $themepath[ $i ] . '/theme-image.jpg' ); ?>" />           
                <input type="radio" name="pm_style" id="style-<?php echo esc_attr( $dirname ); ?>" value="<?php echo esc_attr( $dirname ); ?>" <?php checked( $dbhandler->get_global_option_value( 'pm_style', 'default' ), $dirname ); ?> />
                <label class="pg-theme-name" for="style-<?php echo esc_attr( $dirname ); ?>"><?php echo esc_html( ucfirst( $label ) ); ?></label>
            
            </div>
            <?php $i++; } ?>           
            
        </div>
        
    <div class="pg-uim-notice-wrap"><div class="pg-uim-notice pg-template-notice"><?php esc_html_e( 'You can also create new templates by copying and renaming "default" folder (&#128194;) inside "[plugin root]/public/partials/themes" to "[your current theme directory]/profilegrid-user-profiles-groups-and-communities/themes".', 'profilegrid-user-profiles-groups-and-communities' ); ?></div></div>
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_general_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings"/>
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
