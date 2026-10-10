<?php
$path            =  plugin_dir_url( __FILE__ );
$textdomain      = $this->profile_magic;
$extensions      = new Profile_Magic_Extensions();
$extensions_list = $extensions->extensions_list();
$pg_function     = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
$dbhandler       = new PM_DBhandler();
$mgp             = $pg_function->get_current_theme_aff_id();
if ( $mgp ) {
    $url = 'https://metagauss.com/get-profilegrid-for-wordpress/?mgp=' . $mgp;
} else {
    $url = 'https://profilegrid.co/extensions/';
}
?>

<div class="pmagic pg-wide-wrap pg-extensions">
    
    
    <div class="pg-box-wrap pg-premium-box-wrap">       
        <div class="pg-box-row">
            <div class="pg-box-col-12">
                <a href="<?php echo esc_url( $url ); ?>" target="_blank"><img src="<?php echo esc_url( $path . 'images/svg/pg-premium-banner.svg' ); ?>" class="aligncenter" alt="img" title="ProfileGrid"></a>
            </div>

        </div>        
    </div>
    
    
    

    <div class="pg-ext-list-wrap"> 
        
        <div class="pg-ext-list-title"><?php esc_html_e( 'Official Extensions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>

        <div class="pg-ext-list" id="the-list">
         <div id="pgmyBtnContainer">
             
             <span class="pg-filter-lable">Filter</span>
             <div class="pg-filter-wrap">
                <button class="pgbtn pgactive" onclick="filterSelection('all')">Show all</button>
                <button class="pgbtn" onclick="filterSelection('free')">Free</button>
                <button class="pgbtn" onclick="filterSelection('payments')">Payments</button>
                <button class="pgbtn" onclick="filterSelection('profiles')">Profiles</button>
                <button class="pgbtn" onclick="filterSelection('woocommerce')">WooCommerce</button>
                <button class="pgbtn" onclick="filterSelection('integration')">Integration</button>
                <button class="pgbtn" onclick="filterSelection('groups')">Groups</button>
                <button class="pgbtn" onclick="filterSelection('seo')">SEO</button>
                <button class="pgbtn" onclick="filterSelection('login')">Login</button>
                <button class="pgbtn" onclick="filterSelection('photos')">Photos</button>
                <button class="pgbtn" onclick="filterSelection('content-restriction')">Content Restriction</button>
                <button class="pgbtn" onclick="filterSelection('widget')">Widget</button>
                <button class="pgbtn" onclick="filterSelection('newsletter')">Newsletter</button>
                <button class="pgbtn" onclick="filterSelection('security')">Security</button>
                <button class="pgbtn" onclick="filterSelection('form')">Form</button>
                <button class="pgbtn" onclick="filterSelection('customsolution')">Custom Solution</button>
                </div>
            </div>   
            <div class="pgcontainer pg-box-wrap">
                <div class="pg-box-wrap">
                    <div class="pg-box-row">
         <?php
			foreach ( $extensions_list as $ext ) :
				$ext_image = $path . 'images/' . $ext['image'];
				?>
            <div class="pg-box-col-4 pg-card-mb-16 pg-ext-card pgfilterDiv <?php echo esc_attr($ext['filter']); ?>">
                <div class="pg-box-border pg-box-p-18 pg-box-white-bg pg-box-h-100">
                    <div class="pg-box-row pg-box-h-100">
                        <div class="pg-box-col-8">
      
                            <div class="pg-ext-box-title"><?php echo esc_html( $ext['title'] ); ?></div>
                            <div class="pg-ext-installation-status"><?php $extensions->pg_extension_status( $ext ); ?></div>
                            
                            
                    <div class="pg-ext-box-description">
                        <p class="pg-col-desc"><?php echo esc_html( $ext['description'] ); ?></p>
                        <p class="authors" style="display:none"> <cite><?php  esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                            
                            <div class="pg-ext-box-button"><?php $extensions->pg_get_extension_button( $ext ); ?></div>
                            
                        </div> 
                        <div class="pg-box-col-4 pg-d-flex pg-d-flex-v-center pg-flex-direction-col">
                            <?php if ( $ext['price']==='free' ) : ?>
                            <div class="pg-ext-box-price"><span><?php echo esc_html( $ext['price'] ); ?></span></div>
                            <?php endif; ?>
                            <div class="pg-ext-box-icon"> <img src="<?php echo esc_url( $ext_image ); ?>" class="pg-ext-icon" alt=""></div>
                        </div> 
                    </div>
                    

            
               
                </div>

            </div>
			<?php endforeach; ?>  
                        <?php do_action('pg_customization_extension_html');?>
                          </div>
            </div>
            </div>
            
        </div>


    </div>
    
    <div class="pg-box-wrap pg-premium-box-wrap">       
        <div class="pg-box-row">
            <div class="pg-box-col-12">
                <a href="<?php echo esc_url( $url ); ?>" target="_blank"><img src="<?php echo esc_url( $path . 'images/svg/pg-premium-banner.svg' ); ?>" class="aligncenter" alt="img" title="ProfileGrid"></a>
            </div>

        </div>        
    </div>
    



</div>
