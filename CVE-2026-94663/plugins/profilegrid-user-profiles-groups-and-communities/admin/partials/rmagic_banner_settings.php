<?php
$plugins                           = get_plugins();
        $path                      = 'custom-registration-form-builder-with-submission-manager/registration_magic.php';
        $is_pg_extension_installed = array_key_exists( $path, $plugins );
if ( $is_pg_extension_installed ) {

    $plugin = $path;
	if ( strpos( $path, '/' ) ) {
		$path = str_replace( '/', '%2F', $path );
	}

        $activateUrl = sprintf( admin_url( 'plugins.php?action=activate&plugin=%s' ), $path );
        $activateUrl = wp_nonce_url( $activateUrl, 'activate-plugin_' . $plugin );
} else {
    $plugin = $path;
	if ( strpos( $path, '/' ) ) {
		$path = str_replace( '/', '%2F', $path );
	}

        $activateUrl = 'https://wordpress.org/plugins/custom-registration-form-builder-with-submission-manager/';
}

?>

<div class="uimagic">
   <div class="content">
      <div class="uimheader"><?php esc_html_e( 'Registration Forms', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>    
   </div>
    
    
<div class="uimrow"> 
    <div class="pg-uim-notice">   
        <?php printf( wp_kses_post( 'While ProfileGrid comes packed with its own forms, if you require a more robust and powerful form system you can optionally try our dedicated plugin - RegistrationMagic. It integrates perfectly with ProfileGrid and brings with itself a host of new features. <a href="%1$s" target="new">Click Here</a> to install it now. You can learn more about the integration <a href="%2$s" target="new">here</a>.', 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $activateUrl ), 'https://profilegrid.co/profilegrid-registrationmagic-integration/' ); ?>
    </div>  
    
    <div class="uimrow"> </div>
</div>

</div>
