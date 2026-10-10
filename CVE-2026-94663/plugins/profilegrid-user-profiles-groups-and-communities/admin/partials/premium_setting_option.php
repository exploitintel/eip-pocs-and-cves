<?php
$path =  plugin_dir_url( __FILE__ );
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$pmrequest             = new PM_request();
$deactivate_extensions = $pmrequest->pg_check_premium_extension();

?>

<?php if ( !empty( $deactivate_extensions ) ) : ?>

	<?php if ( in_array( 'Profilegrid_Userid_Slug_Changer', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-custom-profile-slugs" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/userid_slug.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Custom User Profile Slugs', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Edit and define slugs in profile permalinks.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
  </div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Group_photos', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-group-photos" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/group-photos.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Group Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Group Photos on/ off.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
   </div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Display_Name', $deactivate_extensions ) ) : ?>

<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-display-name" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/display_name.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Customize display names, define patterns.', 'profilegrid-user-profiles-groups-and-communities' ); ?> 
    <span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
  </div>
 <?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Group_Fields', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-custom-group-fields" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/group-custom-fields.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Custom Group Properties', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Customized group fields.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> 
  </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Bbpress', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-bbPress-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/bbpress.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title" style="text-transform: none;">
		<?php esc_html_e( 'bbPress Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure bbPress related settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> 
  </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Geolocation', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-geolocation" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/geolocation.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Geolocation Maps', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Map APIs and settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Front_End_Groups', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-frontend-group" onclick="CallExtensionModal(this)"> 
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/frontend-group.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Frontend Group Creator', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Customized Front End Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> 
  </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Mailchimp', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-mailchimp-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-mailchimp.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'MailChimp Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Enable or disable MailChimp integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> 
  </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Woocommerce', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woocommerce-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-woocommerce.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> 
  </div> 
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Social_Connect', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-social-login" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/social-connect.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Social Login', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure social networks.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> 
  </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_User_Content', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-custom-Profile-tabs" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/custom-profile-tab.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Custom User Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Display user authored custom post type data or shortcode.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
 </div>
<?php endif; ?>


	<?php if ( in_array( 'Profilegrid_Mycred', $deactivate_extensions ) ) : ?>

<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-mycred-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-mycred-integration.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'myCred Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Integrate myCRED with User Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
   </div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_User_Photos_Extension', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-user-photos-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/user_photos.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure Photos and Photo Albums for User Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
   </div>
 <?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Menu_Restriction', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-menu-restrictions-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/menu_restrictions.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define groups to show/ hide menus.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
   </div>
    <?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Woocommerce_Wishlist', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-wishlist-woocommerce" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-wishlist-woocommerce.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define WooCommerce Wishlist integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
  </div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Instagram_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-instagram-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-instagram.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Instagram Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure Instagram Integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Group_Wall', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-groupwall-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-groupwall.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Group Wall', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configuration for Group Wall Extension.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Menu_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-login-logout-menu-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-logout-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Login Logout Menu', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure menu items.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Advanced_Woocommerce', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woo-advanced-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-woo-advanced-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Extensions Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define Advanced WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_EventPrime_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-ep-integration-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-ep-integration.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure integration with events.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Admin_Power', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-frontend-group-manager-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/frontend-group-manager.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'More power to your group managers!', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Group_Multi_Admins', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-multi-group-managers-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/multi-admins.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define additional group managers.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Profile_Labels', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-user-labels-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-user-labels.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Add and edit label properties.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Stripe_Payment', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-stripe-payment-system" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/stripe-logo.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Stripe Keys, Currency etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_User_Profile_Status', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension  pg-extension-modal" data-popup="pg-profile_status" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/profile_status.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Profile Status', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Enable/ Disable User Profile Status and set User Profile Status visibility duration.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
  </div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Demo_Content', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-import-demo-content" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/demo-content.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Demo Content', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Import demo content like groups fields and users.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Woocommerce_Product_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woo-product-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/woocommerce-product-intregration.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Product Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define WooCommerce Product integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Hero_Banner', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-hero-banner-ext" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/hero-banner.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define which images to display on banner.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'Profilegrid_Woocommerce_Subscription_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-wooCommerce-subscription" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-wooCommerce-subscription.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Subscription Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define WooCommerce Product/Subscription integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
  </div>
<?php endif; ?>

	<?php if ( in_array( 'profilegrid_woocommerce_product_members_discount', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension  pg-extension-modal" data-popup="pg-woo-member-discount" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-member-discount.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Members Discount', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Enable/Disable the Custom Discount on Products.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
	<?php if ( in_array( 'profilegrid_woocommerce_product_custom_tabs', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woo-custom-tabs" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-woo-custom-tabs.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Product Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Enable/Disable the WooCommerce Product Tabs.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Active_Members_Widget', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-users-online-widget" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/users-online-widget.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Online Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Online Users on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_User_Activities', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-user-activities" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/user-activities-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Activities', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Manage user activities.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Woocommerce_Product_Recommendations', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woo-product-recommendations" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/product-recommendations.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Woocommerce Product Recommendations', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define Woocommerce Product Recommendations integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>


	<?php if ( in_array( 'Profilegrid_Recent_Signup', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-recent-signup" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/recent-signup.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Recent User Signups', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Displays a list of recently signup users.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_User_Reviews_Extension', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-user-reviews" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/profile-reviews.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Profile Reviews', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configuration for User Profile Reviews.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_groups_slider', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-group-slider" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/group-carousel-slider.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'User Groups Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Groups Carousel Widget on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_user_slider', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-user-slider" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/user-carousel-slider.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Users Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Users Carousel Widget on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_featured_group', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-featured-group" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/featured-group.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Featured Group on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Profile_Completeness', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-profile-completeness" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/profile-completness.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Profile Completeness', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Configure profile completeness settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_widgets_privacy', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-widgets-privacy" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/widget-privacy.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Widgets Privacy', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Widgets Privacy on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Zapier_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-zapier-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-zapier-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Zapier Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Define Zapier integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_Mailpoet', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-mailpoet" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/mailpoet.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'MailPoet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Enable or disable MailPoet integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_elementor_content_restrictions', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-elementor-content-restrictions" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/elementor-content-restrictions.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Elementor Content Restrictions on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_elementor_login_logout_widget', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-elementor-user-login" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-elementor-user-login.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Elementor User Login on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

	<?php if ( in_array( 'Profilegrid_elementor_groups_widget', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-elementor-integration" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/elementor-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Elementor Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn Elementor Integration on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( in_array( 'Profilegrid_Custom_Group_Slug', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-custom-group-slug" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/group-slug.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'Customized Group Slugs', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Edit and define slugs in group permalinks.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Customized', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( in_array( 'Profilegrid_Woocommerce_Product_Restrictions', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-inactive-extension pg-extension-modal" data-popup="pg-woocommerce-product-restrictions" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-group-product-restriction.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
		<?php esc_html_e( 'WooCommerce Customized Product Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
		<?php esc_html_e( 'Turn WooCommerce Customized Product Restrictions on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Customized', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
    </span> </div>
</div>
<?php endif; ?>



<?php endif; ?>
