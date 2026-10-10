<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<?php
$path        =  plugin_dir_url( __FILE__ );
$textdomain  = $this->profile_magic;
$pg_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
$dbhandler   = new PM_DBhandler();
$mgp         = $pg_function->get_current_theme_aff_id();
if ( $mgp ) {
    $url = 'https://metagauss.com/get-profilegrid-for-wordpress/?mgp=' . $mgp;
} else {
    $url = 'https://profilegrid.co/extensions/';
}
?>

<div class="pmagic pg-wide-wrap pg-extensions">
    
    <div class="pg-box-row-wrap">
        
        <div class="pg-box-row pg-box-white-bg">
            <div class="pg-box-col-3 pg-premium-icon-col">
                <div class="pg-premium-icon"><img src="<?php echo esc_url( $path . 'images/svg/pg-premium-icon.svg' ); ?>" class="aligncenter pg-box-h-100" alt="img" title="ProfileGrid"></div>
            </div>
            <div class="pg-premium-text-col pg-box-col-9 pg-d-flex pg-box-center pg-box-text-center pg-flex-direction-col pg-d-flex-v-center">
                <div class="pg-premium-banner-head pg-card-mb-16"><img src="<?php echo esc_url( $path . 'images/svg/head-text.svg' ); ?>" class="aligncenter" alt="img" title="ProfileGrid"></div>
                <div class="pg-premium-banner-subhead pg-card-mb-16"> Get all ProfileGrid extensions as part of <strong>single heavily discounted price!</strong> This also includes all future extensions.</div>
                    <div class="pg_pitch_action_button">
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank"><button class="pg_pr_action"><?php esc_html_e( 'Buy Now', 'profilegrid-user-profiles-groups-and-communities' ); ?> </button></a>
                    </div>
            </div>
            
        </div>
        
    </div>
    

    <div class="pg-scblock "> 

        <div class="pg-scblock pg-scpagetitle">
            <b><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></b> <span class="pg-brand"><?php esc_html_e( 'Extensions', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            <div class="pg-scblock-mg-logo"><img src="<?php echo esc_url( $path . 'images/mg-logo.png' ); ?>"></div>
        </div> 
        <div class="pg-ext-list" id="the-list">

            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a href="<?php echo esc_url( $pg_function->pg_get_title_link( 'GROUPWALL' ) ); ?>" class=" open-plugin-details-modal" target="_blank">
                               <?php esc_html_e( 'Group Wall', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-groupwall.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                           <?php $pg_function->pg_get_extension_button( 'GROUPWALL' ); ?>
                        </ul></div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "A brand new ProfileGrid extension that adds social activity to your User Groups. Now users can create new posts, comment on other users' posts and browse Group timeline. Group wall is accessible from Group page as a new tab.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'STRIPE' ) ); ?>">
                               <?php esc_html_e( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/stripe-logo.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'STRIPE' ); ?>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Start accepting credit cards on your site for Group memberships and registrations by integrating popular Stripe payment gateway.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
                <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'DISPLAY_NAME' ) ); ?>">
                               <?php esc_html_e( 'User Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/display_name.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'DISPLAY_NAME' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Now take complete control of your users' display names. Mix and match patterns and add predefined suffixes and prefixes. There's a both global and per group option allowing display names in different groups stand out!", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
                 <div class="plugin-card pg-ext-card ">
        
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank" href="<?php echo esc_url( $pg_function->pg_get_title_link( 'GROUP_PHOTOS' ) ); ?>" class="open-plugin-details-modal">
                               <?php esc_html_e( 'Group Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?> 

                            <img src="<?php echo esc_url( $path . 'images/group-photos.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'GROUP_PHOTOS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Allow your users to create and share Photo Albums within their Groups. There's also an option for public photos. Users can enlarge and comment on different photos. ", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a href="https://profilegrid.co/extensions/" target="_blank"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'CUSTOM_PROFILE_SLUG' ) ); ?>">
                              <?php esc_html_e( 'Custom User Profile Slugs', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/userid_slug.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'CUSTOM_PROFILE_SLUG' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Define how your user profile URL's will appear to site visitors and search engines. Take control of your user profile permalinks and add dynamic slugs.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'CUSTOM_GROUP_FIELDS' ) ); ?>">
                            <?php esc_html_e( 'Custom Group Properties', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/group-custom-fields.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'CUSTOM_GROUP_FIELDS' ); ?>
                        </ul>			</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Create and add custom fields to groups too! Now your user groups can have more detailed information and personality just like your user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
                <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'GEOLOCATION' ) ); ?>">
                              <?php esc_html_e( 'User Geolocation Maps', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/geolocation.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'GEOLOCATION' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Generate maps showing locations of all users or specific groups using simple shortcodes. Get location data from registration form.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>

                <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'FRONTEND_GROUP' ) ); ?>">
                              <?php esc_html_e( 'Frontend Group Creator', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/frontend-group.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'FRONTEND_GROUP' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Allow registered users to create new Groups on front end. These Groups behave and work just like regular ProfileGrid groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
                <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'BBPRESS' ) ); ?>" style="text-transform:none;">
                           <?php esc_html_e( 'bbPress Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/bbpress.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                       <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'BBPRESS' ); ?>
                        </ul>			</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Integrate ProfileGrid user profile properties and sign up system with the ever popular bbPress community forums plugin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
           
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WOOCOMMERCE' ) ); ?>">
                            <?php esc_html_e( 'WooCommerce Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-woocommerce.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WOOCOMMERCE' ); ?>
                        </ul>					</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Combine the power of ProfileGrid's user groups with WooCommerce cart to provide your users ultimate shopping experience.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'MAILCHIMP' ) ); ?>">
                            <?php esc_html_e( 'MailChimp Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-mailchimp.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'MAILCHIMP' ); ?>
                        </ul>					</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Assign ProfileGrid users to MailChimp lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
      
           <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'SOCIALLOGIN' ) ); ?>">
                              <?php esc_html_e( 'Social Login', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/social-connect.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'SOCIALLOGIN' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Allow your users to sign up and login using their favourite social network accounts. Social accounts can be managed from Profile settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'CUSTOM_TAB' ) ); ?>">
                              <?php esc_html_e( 'Custom User Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/custom-profile-tab.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'CUSTOM_TAB' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Add personalized tabs in user profiles to suit your business or industry. Add user authored content from any custom post type or shortcode (or add specific content) with different privacy levels. Open doors to endless possibilities - Integrate user profiles with other plugins supporting custom post or shortcode format.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'FRONTEND_GROUP_MANAGER' ) ); ?>">
                              <?php esc_html_e( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/frontend-group-manager.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'FRONTEND_GROUP_MANAGER' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Offer more power and control to your Group Managers. They can edit Groups, approve membership requests, moderate blogs, manage users, etc. from a dedicated frontend Group management area.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'ADVANCED_WOOCOMMERCE' ) ); ?>">
                              <?php esc_html_e( 'WooCommerce Extensions Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-woo-advanced-icon.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'ADVANCED_WOOCOMMERCE' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Enhance the power of ProfileGrid's integration with WooCommerce by adding in integrations with WooCommerce extensions.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>

            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'MULTI_ADMINS' ) ); ?>">
                              <?php esc_html_e( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/multi-admins.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'MULTI_ADMINS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Don't stay limited to just one Manager per Group. Unlock the ability to have more than one Managers for your ProfileGrid User Groups now. With all of them sharing the same level of control.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'MYCRED' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'myCred Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-mycred-integration.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'MYCRED' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Integrate popular points system for WordPress with ProfileGrid to reward your users. Display ranks and badges on user profile pages, give incentive for activities on site or penalize based on pre-set rules.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'EVENTS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-ep-integration.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'EVENTS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Create ProfileGrid Group Events by Integrating ProfileGrid User Groups with EventPrime Events.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WISHLIST' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-wishlist-woocommerce.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WISHLIST' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Add WooCommerce products to your Wishlist and manage it completely from your ProfileGrid User Profile.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'INSTAGRAM' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Instagram Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-instagram.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'INSTAGRAM' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Show Instagram tab on User Profile page with user’s Instagram photos displayed in the tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'USER_PROFILE_LABELS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-user-labels.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'USER_PROFILE_LABELS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Allow user to add Profile Labels to their User Profiles as an additional way to convey their interests and/or designation.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>

            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'LOGIN_LOGOUT_MENU' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Login Logout Menu', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-logout-icon.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'LOGIN_LOGOUT_MENU' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Now you can add contextual login menu item to your website menu(s) with few simple clicks. The menu item changes based on user login state. Additionally, you have option to add User Profile, User Groups and Password Recovery items too.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
              <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'PROFILE_USER_STATUS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'User Profile Status', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/profile_status.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'PROFILE_USER_STATUS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Allow users to upload statuses to their user profiles. Users can view statuses on their own profiles and other users' profiles.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'PROFILE_USER_PHOTOS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'ProfileGrid User Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/user_photos.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'PROFILE_USER_PHOTOS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Allow users to upload and manage personal photos on their user profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'MENU_RESTRICTIONS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/menu_restrictions.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'MENU_RESTRICTIONS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( "Use in-built ProfileGrid hierarchy to hide or show menu items on your site! You can mark specific menu items to be visible or hidden only to certain group(s). Create specific menu items for Group Managers of selected or all groups. Combine it with ProfileGrid's core content restriction system to build extremely powerful membership sites.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'DEMO_CONTENT' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Demo Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/demo-content.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'DEMO_CONTENT' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'This dynamic extension enables admin to import demo content. The admin can also import these groups with multiple fields, sections and users. Moreover, the admins get an option to choose number of demo groups they want to import.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WOO_PRODUCT_INTEGRATION' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Product Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/woocommerce-product-intregration.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WOO_PRODUCT_INTEGRATION' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'This ravishing extension allows you to integrate WooCommerce products with ProfileGrid Groups. You can assign groups to your users based on the type of products they buy or the amount of purchase they make on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WOO_SUBSCRIPTION_INTEGRATION' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Subscription Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-wooCommerce-subscription.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WOO_SUBSCRIPTION_INTEGRATION' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Integrate WooCommerce product subscriptions with ProfileGrid Groups. Assign/Unassign the groups to the users based on WooCommerce subscription.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'HERO_BANNER' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/hero-banner.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'HERO_BANNER' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'With the dynamic Hero Banner feature showcase your group profiles as a striking hero image on your WordPress website. You can add multiple rows and columns of your choice.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WOO_MEMBER_DISCOUNT' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Members Discount', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-member-discount.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WOO_MEMBER_DISCOUNT' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Add custom discount on WooCommerce product purchase for users based on group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WOO_CUSTOM_TABS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Product Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-woo-custom-tabs.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WOO_CUSTOM_TABS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Add personalized tabs to WooCommerce Product.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'USERS_ONLINE' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Online Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/users-online-widget.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'USERS_ONLINE' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Displays a list of currently online users with their profile images and display names.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'USER_ACTIVITIES' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'User Activities', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/user-activities-icon.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'USER_ACTIVITIES' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Display various activities by different users inside a beautiful widget, that can fit any widget area of your website.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'PRODUCT_RECOMMENDATIONS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'WooCommerce Product Recommendations', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/product-recommendations.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'PRODUCT_RECOMMENDATIONS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Display product suggestions right inside user profiles based on the user’s purchase history.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'RECENT_SIGNUP' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Recent User Signups', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/recent-signup.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'RECENT_SIGNUP' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'A widget and a shortcode which allows you to display a pre-defined number of recently added users with profile images, and an option to add a custom link.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'PROFILE_REVIEWS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'User Profile Reviews', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/profile-reviews.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'PROFILE_REVIEWS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Empower your website members to rate and review other member profiles. Advanced options and restrictions allow you to customize it to fit different scenarios.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'GROUPS_SLIDER' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'User Groups Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/group-carousel-slider.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'GROUPS_SLIDER' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'USERS_SLIDER' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Users Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/user-carousel-slider.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'USERS_SLIDER' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'FEATURED_GROUP' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/featured-group.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'FEATURED_GROUP' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'A customizable frontend ProfileGrid Widget that allows you to display featured membership groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'COMPLETENESS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Profile Completeness', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/profile-completness.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'COMPLETENESS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Displays a profile completeness progress bar on user profile page to encourage the user to fill out profile data.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'WIDGET_PRIVACY' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Widgets Privacy', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/widget-privacy.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'WIDGET_PRIVACY' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Adds additional multi-level privacy options to all ProfileGrid widgets. Now you can restrict relevant information only to intended audience. You can also create multiple instances of a widget to deliver different information to different types of audiences.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
            <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'ZAPIER' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Zapier Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-zapier-icon.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'ZAPIER' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Extend the power of ProfileGrid using Zapier automation! Connect with over 3000 apps using readymade templates or create custom automations to work with ProfileGrid triggers.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'MAILPOET' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'MailPoet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/mailpoet.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'MAILPOET' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Assign ProfileGrid users to MailPoet lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'ELEMENTOR_CONTENT_RESTRICTIONS' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/elementor-content-restrictions.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'ELEMENTOR_CONTENT_RESTRICTIONS' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Profilegrid content restrictions widget provides the feature to restrict content with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'ELEMENTOR_USER_LOGIN' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/pg-elementor-user-login.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'ELEMENTOR_USER_LOGIN' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'ProfileGrid login box as native Elementor login widget for building beautiful custom login pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
             <div class="plugin-card pg-ext-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a target="_blank"  href="<?php echo esc_url( $pg_function->pg_get_title_link( 'ELEMENTOR_USER_LOGIN' ) ); ?>" style="text-transform: none;">
                              <?php esc_html_e( 'Elementor Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>

                            <img src="<?php echo esc_url( $path . 'images/elementor-icon.png' ); ?>" class="plugin-icon" alt="">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <?php $pg_function->pg_get_extension_button( 'ELEMENTOR_INTEGRATION' ); ?>
                        </ul>				</div>
                    <div class="desc column-description">
                        <p class="pg-col-desc"><?php esc_html_e( 'Use ProfileGrid elements as native Elementor widgets and build custom layouts you always wanted with your favorite composer.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                        <p class="authors"> <cite><?php esc_html_e( 'By', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></cite></p>
                        <p>&nbsp;</p>
                    </div>
                </div>

            </div>
            
        </div>


    </div>
    
    <div class="pg-box-row-wrap">
        
        <div class="pg-box-row pg-box-white-bg">
            <div class="pg-box-col-3 pg-premium-icon-col">
                <div class="pg-premium-icon"><img src="<?php echo esc_url( $path . 'images/svg/pg-premium-icon.svg' ); ?>" class="aligncenter pg-box-h-100" alt="img" title="ProfileGrid"></div>
            </div>
            <div class="pg-premium-text-col pg-box-col-9 pg-d-flex pg-box-center pg-box-text-center pg-flex-direction-col pg-d-flex-v-center">
                <div class="pg-premium-banner-head pg-card-mb-16"><img src="<?php echo esc_url( $path . 'images/svg/head-text.svg' ); ?>" class="aligncenter" alt="img" title="ProfileGrid"></div>
                <div class="pg-premium-banner-subhead pg-card-mb-16"> Get all ProfileGrid extensions as part of <strong>single heavily discounted price!</strong> This also includes all future extensions.</div>
                    <div class="pg_pitch_action_button">
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank"><button class="pg_pr_action"><?php esc_html_e( 'Buy Now', 'profilegrid-user-profiles-groups-and-communities' ); ?> </button></a>
                    </div>
            </div>
            
        </div>
        
    </div>
    



</div>
