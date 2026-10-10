<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
?>

<div class="uimagic">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php esc_html_e( 'Global Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div> 
     
     <div class="uimrow"> 
    <a href="admin.php?page=pm_general_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/general.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'General', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Form look, Default pages, Attachment settings etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div> 
      
      
    <div class="uimrow"> 
    <a href="admin.php?page=pm_theme_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/pg-layout-icon.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Profile Templates', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Option to choose new templates.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_security_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/security.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Security', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Spam Protection, Blacklists and more.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_user_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/usersettings.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'User Accounts', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Activation link, Manual Approvals etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_email_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/autoresponder.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Email Notifications', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Admin Notifications, Multiple Email Notifications, From Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_tools">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/tools.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Tools', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Import/ Export Options', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a>
    </div>
    <div class="uimrow"> 
          <a href="admin.php?page=pm_blog_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/userblogs.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'User Blogs', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Default post status, privacy settings etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
          <a href="admin.php?page=pm_message_settings">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/privatemessaging.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Private Messaging', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Turn Private Messaging on/ off', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    <div class="uimrow"> 
        <a href="admin.php?page=pm_friend_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/friends.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Friends System', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Turn Friends System on or off and more', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    
    <div class="uimrow"> 
        <a href="admin.php?page=pm_upload_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pm_upload.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Uploads', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Image widths, sizes, quality etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    
    <div class="uimrow"> 
        <a href="admin.php?page=pm_seo_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pm_seo.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'SEO', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'All SEO related options.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    
     <div class="uimrow"> 
        <a href="admin.php?page=pm_content_restrictions">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/content-privacy-guide.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'How to restrict content for members.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    
    <?php
    if ( class_exists( 'Profile_Magic' ) &&  class_exists( 'WooCommerce' ) && !class_exists( 'Profilegrid_Woocommerce' ) ) {
		?>
    <div class="uimrow"> 
        <a href="admin.php?page=pm_woocommerce_extension">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/woocommerce.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Woocommerce', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Define WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    <?php } ?>
    
    <?php
    if ( class_exists( 'Profile_Magic' ) &&  class_exists( 'WooCommerce' ) && !class_exists( 'Profilegrid_Advanced_Woocommerce' ) ) {
		?>
    <div class="uimrow"> 
        <a href="admin.php?page=pm_woocommerce_advanced_extension">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pg-woo-advanced-icon.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'WooCommerce Extensions Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Define Advanced WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
    <?php } ?>
    
     <?php
		if ( class_exists( 'Profile_Magic' ) &&  class_exists( 'WooCommerce' ) && !class_exists( 'Profilegrid_Woocommerce_Wishlist' ) ) {
			?>
    <div class="uimrow"> 
        <a href="admin.php?page=pm_woocommerce_wishlist_extension">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pg-wishlist-woocommerce.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Define WooCommerce Wishlist integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
	   <?php } ?>
    
    <div class="uimrow"> 
        <a href="admin.php?page=pm_rm_integration">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pg-rm-integration.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Registration Forms', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Configure RegistrationMagic Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
 
    <div class="uimrow"> 
        <a href="admin.php?page=pm_profile_notification_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/profile-notification-icon.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Profile Notifications', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Enable/ Disable Live Profile Notifications', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>
      
    <div class="uimrow"> 
        <a href="admin.php?page=pm_profile_tabs_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/profile-tabs-icon.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Enable/ Disable Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
         </a> 
    </div>

    <div class="uimrow"> 
      <a href="admin.php?page=pm_private_profile_settings">
          <div class="pm_setting_image"> 
              <img src="<?php echo esc_url( $path . 'images/pg-private-profile-icon.png' ); ?>" class="options" alt="options"> 
          </div>
          <div class="pm-setting-heading"> 
              <span class="pm-setting-icon-title"><?php esc_html_e( 'Private Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
              <span class="pm-setting-description"><?php esc_html_e( 'Enable/ Disable Private Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
          </div>
       </a> 
    </div>
      
    <div class="uimrow"> 
        <a href="admin.php?page=pm_elements_visibility_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pg-elements-visibility-icon.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'Elements Visibility', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Enable/ Disable Elements Visibility', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
        </a> 
    </div>
      
    <?php do_action( 'profile_magic_setting_option' ); ?>
    
 
  </div>
</div>
