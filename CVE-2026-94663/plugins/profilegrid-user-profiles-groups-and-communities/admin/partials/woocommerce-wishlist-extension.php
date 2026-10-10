<?php
$path = plugin_dir_url( __FILE__ );
$url  = 'https://profilegrid.co/extensions/woocommerce-wishlist/';
?>
<div class="uimagic">
    <form name="pm_woocommerce_extension" id="pm_woocommerce_extension" method="post">
        <!-----Dialogue Box Starts----->
        <div class="content">
           
                <div class="uimheader">
                    <?php esc_html_e( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
    
        
            
            <div class="uimrow">
                
                <div class="update-nag">
                 <?php
					// translators: %s: URL to WooCommerce wishlist integration page.
					echo sprintf( esc_html__( 'Add WooCommerce products to your Wishlist. Your <a target="_blank" href="%s">WooCommerce Wishlist Integration</a> will be visible on your ProfileGrid User Profile from where you can manage it completely.', 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $url ) );
					?>
                </div>
            </div>
            
            <div class="uimrow">
                <img class="pg-woocommerce-extension-img" src="<?php echo esc_url( $path . 'images/pg-woocommerce-wishlist-extension.png' ); ?>"
            </div>

            <div class="buttonarea"> <a href="admin.php?page=pm_settings">
                    <div class="cancel">&#8592; &nbsp;
<?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                </a>
                <?php wp_nonce_field( 'pg_woocommerce_wishlist_settings', 'pg_woocommerce_wishlist_settings_nonce' ); ?>
                
            </div>
        </div>
</div>
    </form>
</div>
