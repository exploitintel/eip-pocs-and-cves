<?php
$path = plugin_dir_url( __FILE__ );
$url  = 'https://profilegrid.co/extensions/advanced-woocommerce-integration/';
?>
<div class="uimagic">
    <form name="pm_woocommerce_extension" id="pm_woocommerce_extension" method="post">
        <!-----Dialogue Box Starts----->
        <div class="content">
           
                <div class="uimheader">
                    <?php esc_html_e( 'Advanced WooCommerce Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
    
        
            
            <div class="uimrow">
                
                <div class="update-nag">
                 <?php
					// translators: %s: URL to advanced WooCommerce integration page.
					echo sprintf( esc_html__( 'Combine the power of ProfileGrid User Groups with WooCommerce extensions to provide the ultimate shopping experience to your users. Check out the <a target="_blank" href="%s">ProfileGrid Advanced WooCommerce Integration</a> now!', 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $url ) );
					?>
                </div>
            </div>
            
            <div class="uimrow">
                <img class="pg-woocommerce-extension-img" src="<?php echo esc_url( $path . 'images/pg-advanced-woocommerce-extension.png' ); ?>"
            </div>

            <div class="buttonarea"> <a href="admin.php?page=pm_settings">
                    <div class="cancel">&#8592; &nbsp;
<?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                </a>
                <?php wp_nonce_field( 'pg_woocommerce_advanced_settings', 'pg_woocommerce_advanced_settings_nonce' ); ?>
                
            </div>
        </div>
</div>
    </form>
</div>
