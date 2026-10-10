<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$pmrequests = new PM_request();
?>

<div class="pm-setting-wrapper">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php esc_html_e( 'Core Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div> 
      <div class="pm-setting-wrap"> 
     
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
          
      <div class="uimrow"> <a href="admin.php?page=pm_license_settings">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg_license.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'License', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Add license keys for auto-updates.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
  </a> </div>       
      
      
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
          
    <div class="uimrow"> 
        <a href="admin.php?page=pm_users_listing_settings">
            <div class="pm_setting_image"> 
                <img src="<?php echo esc_url( $path . 'images/pg-user-directory.png' ); ?>" class="options" alt="options"> 
            </div>
            <div class="pm-setting-heading"> 
                <span class="pm-setting-icon-title"><?php esc_html_e( 'User Directory', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
                <span class="pm-setting-description"><?php esc_html_e( 'Options for publishing user lists.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
            </div>
        </a> 
    </div>
      
   <div class="uimrow"> <a href="admin.php?page=pm_payment_settings">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg_payments.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Currency, Symbol Position, Checkout Page etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
  </a> </div>
          
        

    </div>
 
  </div>
</div>

<?php
$activate_extensions = $pmrequests->pg_get_activate_extensions();
if ( !empty( $activate_extensions['paid'] || !empty( $activate_extensions['free'] ) ) ) :
	?>
<div class="pm-setting-wrapper">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php esc_html_e( 'Extensions Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div> 
      
            <div class="pm-setting-wrap"> 
      <?php do_action( 'profile_magic_setting_option' ); ?>
            </div>
  </div>
</div>
<?php endif; ?>
<?php
/*
$available_extensions = $pmrequests->pg_check_premium_extension();
if ( !empty( $available_extensions ) ) :
	?>
<div class="pm-setting-wrapper">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php esc_html_e( 'Other Available Extension Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div> 
      
            <div class="pm-setting-wrap"> 
      <?php do_action( 'profile_magic_available_extensions' ); ?>
            </div>
  </div>
</div>
<?php endif; */ ?>




    <div id="pg-setting-popup" class="pg-setting-modal-view" style="display: none;">
        <div class="pg-setting-modal-overlay pg-setting-popup-overlay-fade-in"></div>
        <div class="pg-setting-modal-wrap pg-setting-popup-out">
            <div class="pg-setting-modal-titlebar">
                <span class="pg-setting-modal-close">×</span>
            </div>
            <div class="pg-setting-container">

    <!--Custom User Profile Slugs-->
                
    <div class="pg-extension-wrap" id="pg-custom-profile-slugs" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/userid_slug.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Custom User Profile Slugs', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Edit and define slugs in profile permalinks', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span>
        </div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Define how your user profile URL\'s will appear to site visitors and search engines. Take control of your user profile permalinks and add dynamic slugs.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/user-profile-custom-slugs" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Custom User Profile Slugs End-->
      
    <!--Group Photos-->
                
    <div class="pg-extension-wrap" id="pg-group-photos" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/group-photos.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Group Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Group Photos on/ off', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Allow your users to create and share Photo Albums within their Groups. There\'s also an option for public photos. Users can enlarge and comment on different photos.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Group Photos End--> 
    
    
    <!--User Display Name-->
                
    <div class="pg-extension-wrap" id="pg-display-name" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/display_name.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Customize display names, define patterns.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Now take complete control of your users\' display names. Mix and match patterns and add predefined suffixes and prefixes. There\'s a both global and per group option allowing display names in different groups stand out!', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/user-display-name" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--User Display Name End--> 
    
    
    
    <!--Custom Group Properties-->
                
    <div class="pg-extension-wrap" id="pg-custom-group-fields" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/group-custom-fields.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Custom Group Properties', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Customized group fields', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Create and add custom fields to groups too! Now your user groups can have more detailed information and personality just like your user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Custom Group Properties--> 
    
    
    <!--bbPress Integration-->
                
    <div class="pg-extension-wrap" id="pg-bbPress-integration" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/bbpress.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'bbPress Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure bbPress related settings', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Integrate ProfileGrid user profile properties and sign up system with the ever popular bbPress community forums plugin.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/bbpress-integration" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--bbPress Integration End--> 
    
    
        <!--Geolocation Integration-->
                
    <div class="pg-extension-wrap" id="pg-geolocation" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/geolocation.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Geolocation Maps', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Map APIs and settings', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Generate maps showing locations of all users or specific groups using simple shortcodes. Get location data from registration form.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Geolocations Integration End--> 
    
    
    
        <!--Frontend Group Creator-->
                
    <div class="pg-extension-wrap" id="pg-frontend-group" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/frontend-group.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Frontend Group Creator', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Customized Front End Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Allow registered users to create new Groups on front end. These Groups behave and work just like regular ProfileGrid groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Frontend Group Creator End--> 
    
    
    <!--MailChimp Integration-->
                
    <div class="pg-extension-wrap" id="pg-mailchimp-integration" style="display: none">
        <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-mailchimp.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'MailChimp Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Enable or disable MailChimp integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Assign ProfileGrid Users to MailChimp lists with custom field mapping and options for Users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--MailChimp Integration End--> 
    
    
    
        <!--WooCommerce Integration-->
                
    <div class="pg-extension-wrap" id="pg-woocommerce-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-woocommerce.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Combine the power of ProfileGrid\'s User Profiles, Groups & Communities with WooCommerce shopping cart to provide your Users the ultimate shopping experience.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/woocommerce-integration/?utm_source=plugin&utm_medium=gs_modal" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Integration End--> 
    
    <!--Social Login-->
                
    <div class="pg-extension-wrap" id="pg-social-login" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/social-connect.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Social Login', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure social networks.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Allow your users to sign up and login using their favourite social network accounts. Social accounts can be managed from Profile settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Social Login--> 
    
    <!--Custom Profile Tabs-->
                
    <div class="pg-extension-wrap" id="pg-custom-Profile-tabs" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/custom-profile-tab.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Custom User Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Display user authored custom post type data or shortcode', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Create and add custom tabs to user profiles with data fetched from other plugins. Ultimate tool for plugging in content from different plugins enhancing functionality of user profiles manifold. Be it events, business listings, classified ads, job postings, downloads, products or anything else that is available in WordPress repository - now you can filter them by user and display as part of user\'s profile.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Custom Profile Tabs--> 
    
    
    <!--myCRED-->
                
    <div class="pg-extension-wrap" id="pg-mycred-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-mycred-integration.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'myCred Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Integrate myCRED with User Profiles', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Integrate popular points system for WordPress with ProfileGrid to reward your users. Display ranks and badges on user profile pages, give incentive for activities on site or penalize based on pre-set rules.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--myCREDs--> 
    
    
    <!--User Photos-->
                
    <div class="pg-extension-wrap" id="pg-user-photos-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/user_photos.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure Photos and Photo Albums for User Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Allow users to upload and manage personal photos on their user profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--User Photos-->
    
    
    
        <!--Menu Restrictions-->
                
    <div class="pg-extension-wrap" id="pg-menu-restrictions-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/menu_restrictions.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define groups to show/ hide menus.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Use in-built ProfileGrid hierarchy to hide or show menu items on your site! You can mark specific menu items to be visible or hidden only to certain group(s). Create specific menu items for Group Managers of selected or all groups. Combine it with ProfileGrid\'s core content restriction system to build extremely powerful membership sites.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Menu Restrictions End--> 
    
    <!--WooCommerce Wishlist-->
                
    <div class="pg-extension-wrap" id="pg-wishlist-woocommerce" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-wishlist-woocommerce.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define WooCommerce Wishlist integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Add WooCommerce products to your Wishlist to purchase them easily later. Your WooCommerce Wishlist will be visible to you from your ProfileGrid User Profile from where you can manage it completely.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Wishlist End-->
    
    
        <!--Instagram-->
                
    <div class="pg-extension-wrap" id="pg-instagram-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-instagram.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Instagram Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure Instagram Integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Show Instagram tab on User Profile page with user’s Instagram photos displayed in the tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Instagram End-->
    
    
        
    <!--Group Wall-->
                
    <div class="pg-extension-wrap" id="pg-groupwall-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-groupwall.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Group Wall', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configuration for Group Wall Extension.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'A brand new ProfileGrid extension that adds social activity to your User Groups. Now users can create new posts, comment on other users’ posts and browse Group timeline. Group Wall is accessible from Group page as a new tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Group Wall End-->
    
    
    <!--Login Logout Menu-->
                
    <div class="pg-extension-wrap" id="pg-login-logout-menu-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-logout-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Login Logout Menu', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure menu items.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Now you can add contextual login menu item to your website menu(s) with few simple clicks. The menu item changes based on user login state. Additionally, you have option to add User Profile, User Groups and Password Recovery items.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/profilegrid-login-logout-menu" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Login Logout Menu End-->
    
    
    <!--WooCommerce Extensions Integration)-->
                
    <div class="pg-extension-wrap" id="pg-woo-advanced-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-woo-advanced-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Extensions Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define Advanced WooCommerce integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Enhance the power of ProfileGrid\'s integration with WooCommerce by adding in integrations with WooCommerce extensions.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Extensions Integration End-->
    
    
        <!--EventPrime Integration-->
                
    <div class="pg-extension-wrap" id="pg-ep-integration-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-ep-integration.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure integration with events.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Create ProfileGrid Group Events by Integrating ProfileGrid User Groups with EventPrime Events. Use the power of EventPrime\'s amazing event management tools to create and manage your very own ProfileGrid Group Events.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/eventprime-integration" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--EventPrime Integration End-->
    
    
    <!--Advanced Group Manager-->
                
    <div class="pg-extension-wrap" id="pg-frontend-group-manager-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/frontend-group-manager.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'More power to your group managers.!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Offer more power and control to your Group Managers. They can edit Groups, approve membership requests, moderate blogs, manage users, etc. from a dedicated frontend Group management area.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Advanced Group Manager End-->
    
    
    <!--Multi Group Managers-->
                
    <div class="pg-extension-wrap" id="pg-multi-group-managers-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/multi-admins.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define additional group managers.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Don\'t stay limited to just one Manager per Group. Unlock the ability to have more than one Managers for your ProfileGrid User Groups now. With all of them sharing the same level of control.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Multi Group Managers End-->
    
    
    
        <!--User Profile Labels-->
                
    <div class="pg-extension-wrap" id="pg-user-labels-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-user-labels.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Add and edit label properties.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Allow user to add Profile Labels to their User Profiles as an additional way to list important information, such as user interests and/or designation.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--User Profile Labels End-->
    
    <!--Stripe Payments-->
                
    <div class="pg-extension-wrap" id="pg-stripe-payment-system" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/stripe-logo.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Stripe Keys, Currency etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Start accepting credit cards on your site for Group memberships and registrations by integrating popular Stripe payment gateway.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Stripe Payments End-->
    
    
        
    <!--User Profile Status-->
                
    <div class="pg-extension-wrap" id="pg-profile_status" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/profile_status.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Profile Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Enable/ Disable User Profile Status and set User Profile Status visibility duration.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Now users can upload statuses to their user profiles. Users can view statuses on their own profiles and other users\' profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--User Profile Status End-->
    
    
        <!--Demo Content-->
                
    <div class="pg-extension-wrap" id="pg-import-demo-content" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/demo-content.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Demo Content', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Import demo content like groups fields and users.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'This dynamic extension enables admin to import demo content. The admin can also import these groups with multiple fields, sections and users. Moreover, the admins get an option to choose number of demo groups they want to import. This striking extension further allow admins to set demo profile pictures for the groups they want to import.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/profilegrid-demo-content" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Demo Content End-->
    
    
    <!--WooCommerce Product Integration-->
                
    <div class="pg-extension-wrap" id="pg-woo-product-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/woocommerce-product-intregration.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Product Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define WooCommerce Product integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'This ravishing extension allows you to integrate WooCommerce products with ProfileGrid Groups. You can assign groups to your users based on the type of products they buy or the amount of purchase they make on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Product Integration End-->
    
    
        <!--Hero Banner-->
                
    <div class="pg-extension-wrap" id="pg-hero-banner-ext" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/hero-banner.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define which images to display on banner.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'With the dynamic Hero Banner feature showcase your group profiles as a striking hero image on your WordPress website. You can add multiple rows and columns of your choice.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="https://profilegrid.co/extensions/profilegrid-hero-banner" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--Hero Banner End-->
    
    
    <!--WooCommerce Subscription Integration-->
                
    <div class="pg-extension-wrap" id="pg-wooCommerce-subscription" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-wooCommerce-subscription.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Subscription Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define WooCommerce Product/Subscription integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Integrate WooCommerce product subscriptions with ProfileGrid Groups. Assign/Unassign the groups to the users based on WooCommerce subscription.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Subscription Integration End-->
    
    
    <!--WooCommerce Members Discount-->
                
    <div class="pg-extension-wrap" id="pg-woo-member-discount" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-member-discount.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Members Discount', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Enable/Disable the Custom Discount on Products', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'This extension allows adding discounts on WooCommerce products purchase based on group membership. It allows enabling/disabling the discount on specific products. Discounts can be a fixed amount or a percentage of the product price.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Members Discount End-->
    
    
        <!--WooCommerce Product Tabs-->
                
    <div class="pg-extension-wrap" id="pg-woo-custom-tabs" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-woo-custom-tabs.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Product Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Enable/Disable the WooCommerce Product Tabs', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'This extension allows adding custom tabs on WooCommerce products. It allows enabling/disabling the custom tabs on specific products.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--WooCommerce Product Tabs End-->
    
     <!--users online widget-->
                
    <div class="pg-extension-wrap" id="pg-users-online-widget" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/users-online-widget.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Online Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Online Users on/off', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Displays a list of currently online users with their profile images and display names.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--users online widget End-->
    
    <!--user activities widget-->
                
    <div class="pg-extension-wrap" id="pg-user-activities" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/user-activities-icon.png' ); ?>" class="options" alt="options"></div>
       <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Activities', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Manage User activities', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Display various activities by different users inside a beautiful widget, that can fit any widget area of your website.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-user-activities/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
        
    </div>
    
    <!--user activities widget End-->
    
    <!--woocommerce product recommendations-->
                
    <div class="pg-extension-wrap" id="pg-woo-product-recommendations" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/product-recommendations.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Woocommerce Product Recommendations', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define Woocommerce Product Recommendations integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Display product suggestions right inside user profiles based on the user’s purchase history.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
    
    <!--woocommerce product recommendations End-->
    
     <!--woocommerce product recommendations-->
                
    <div class="pg-extension-wrap" id="pg-recent-signup" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/recent-signup.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Recent User Signups', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Displays a list of recently signup users', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'A widget and a shortcode which allows you to display a pre-defined number of recently added users with profile images, and an option to add a custom link.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-recent-signup/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
     
     <div class="pg-extension-wrap" id="pg-user-reviews" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/profile-reviews.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Profile Reviews', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configuration for User Profile Reviews.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Empower your website members to rate and review other member profiles. Advanced options and restrictions allow you to customize it to fit different scenarios.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
     
     
     <div class="pg-extension-wrap" id="pg-group-slider" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/group-carousel-slider.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Groups Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Groups Carousel Widget on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-groups-carousel-widget/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
     
      <div class="pg-extension-wrap" id="pg-user-slider" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/user-carousel-slider.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Users Slider', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Users Carousel Widget on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-users-carousel-widget/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
     
      <div class="pg-extension-wrap" id="pg-featured-group" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/featured-group.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Featured Group on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'A customizable frontend ProfileGrid Widget that allows you to display featured membership groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-featured-group/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    
        
    </div>
    
    <!--woocommerce product recommendations End-->
    
     <!--users online widget-->
                
    <div class="pg-extension-wrap" id="pg-profile-completeness" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/profile-completness.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Profile Completeness', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure profile completeness settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Displays a profile completeness progress bar on user profile page to encourage the user to fill out profile data.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--users online widget End-->
    
    
    <!--Widgets Privacy -->
                
    <div class="pg-extension-wrap" id="pg-widgets-privacy" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/widget-privacy.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Widgets Privacy', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Widgets Privacy on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Adds additional multi-level privacy options to all ProfileGrid widgets. Now you can restrict relevant information only to intended audience. You can also create multiple instances of a widget to deliver different information to different types of audiences.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--ProfileGrid widgets Privacy End-->
    
    <!--Zapier Integration -->
                
    <div class="pg-extension-wrap" id="pg-zapier-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-zapier-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Zapier Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define Zapier integration parameters.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Extend the power of ProfileGrid using Zapier automation! Connect with over 3000 apps using readymade templates or create custom automations to work with ProfileGrid triggers.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-zapier-integration/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        
        </div>
    </div>
    
    <!--Zapier Integration End-->
    
    <!--ProfileGrid Mailpoet -->
                
    <div class="pg-extension-wrap" id="pg-mailpoet" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/mailpoet.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'MailPoet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Enable or disable MailPoet integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Assign ProfileGrid users to MailPoet lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-mailpoet/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        
        </div>
    </div>
    <!--ProfileGrid Elementor Content Restrictions -->
    <div class="pg-extension-wrap" id="pg-elementor-content-restrictions" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/elementor-content-restrictions.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Elementor Content Restrictions on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Profilegrid content restrictions widget provides the feature to restrict content with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="https://profilegrid.co/extensions/profilegrid-elementor-content-restrictions/" target="_blank"><?php esc_html_e( 'Download Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        
        </div>
    </div>
    
    
     <!--ProfileGrid Elementor User Login -->
    <div class="pg-extension-wrap" id="pg-elementor-user-login" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-elementor-user-login.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Elementor User Login on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'ProfileGrid login box as native Elementor login widget for building beautiful custom login pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
     
       <!--ProfileGrid Elementor Integration -->
    <div class="pg-extension-wrap" id="pg-elementor-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/elementor-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Elementor Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Elementor Integration on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Free', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Use ProfileGrid elements as native Elementor widgets and build custom layouts you always wanted with your favorite composer.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
       
         <!--ProfileGrid Customized Group Slugs -->

    <div class="pg-extension-wrap" id="pg-custom-group-slug" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/group-slug.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Customized Group Slugs', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Edit and define slugs in group permalinks.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Customized', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'ProfileGrid Customized Group Slugs allows ProfileGrid to customize Group slugs and update SEO page title and description.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
         
         
          <!--ProfileGrid WooCommerce Customized Product Restrictions-->

    <div class="pg-extension-wrap" id="pg-woocommerce-product-restrictions" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-group-product-restriction.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Customized Product Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn WooCommerce Customized Product Restrictions on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Customized', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Adds control over WooCommerce products visibility to ProfileGrid Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
          
           <!--ProfileGrid Customized TeraWallet Integration-->

    <div class="pg-extension-wrap" id="pg-terawallet-integration" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-wallet-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Customized TeraWallet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Customized TeraWallet Integration on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="pg-ext-label"><?php esc_html_e( 'Customized', 'profilegrid-user-profiles-groups-and-communities' ); ?> </span></div>
       
        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Offers powerful Group based credit control to admins and Group leaders. It integrates with WooCommerce and TeraWallet plugins to provide a workflow enabling Group based credit limit spendable on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        <span><a href="admin.php?page=pm_extensions" target="_blank"><?php esc_html_e( 'Interested? Checkout more information', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        </div>
    </div>
    
    <!--MailPoet Integration End-->
    
    
        
    <!--Activated Extensions Popup-->
    
     <!--ProfileGrid Customized TeraWallet Integration  Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-terawallet-integration-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-wallet-icon.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Customized TeraWallet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Offers powerful Group based credit control to admins and Group leaders. It integrates with WooCommerce and TeraWallet plugins to provide a workflow enabling Group based credit limit spendable on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
        <div class="pg-extension-modal-des">
            <?php echo wp_kses_post( __( 'Offers powerful Group based credit control to admins and Group leaders. It integrates with WooCommerce and TeraWallet plugins to provide a workflow enabling Group based credit limit spendable on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?>
        </div>
    </div>
    
    <!--ProfileGrid WooCommerce Customized Product Restrictions End--> 
    
    <!--ProfileGrid WooCommerce Customized Product Restrictions  Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-woocommerce-product-restrictions-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-group-product-restriction.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'WooCommerce Customized Product Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Adds control over WooCommerce products visibility to ProfileGrid Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
        <div class="pg-extension-modal-des">
            <?php echo wp_kses_post( __( 'Adds control over WooCommerce products visibility to ProfileGrid Groups.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?>
        </div>
    </div>
    
    <!--ProfileGrid WooCommerce Customized Product Restrictions End--> 
    
    <!--Menu Restrictions Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-menu-restrictions-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/menu_restrictions.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define groups to show/ hide menus.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
        <div class="pg-extension-modal-des">
            <?php echo wp_kses_post( __( 'You can find options for this extension inside Dashboard &#8594; Appearance &#8594; Menus. When you edit or add a new menu item in one of your menus, you will now see a new option to show or hide it for specific group members. You can find more details', 'profilegrid-user-profiles-groups-and-communities' ) ); ?>
        
           <a href="https://profilegrid.co/apply-menu-restrictions/" target="_blank"><?php esc_html_e( 'here', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
        </div>
    </div>
    
    <!--Menu Restrictions Active End--> 
    
    
    
    <!--EventPrime Integration Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-ep-integration-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-ep-integration.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Configure integration with events.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php
            $eventprime_url = 'https://wordpress.org/plugins/eventprime-event-calendar-management/';
            echo wp_kses_post(
                sprintf(
                    /* translators: %s is the EventPrime plugin page link on WordPress.org. */
                    __( 'You will find event specific group options on installing our EventPrime plugin. You can download and install it from the official WordPress repository by visiting <a href="%s" target="_blank" rel="noopener noreferrer">page</a> Once it is installed and activated, on creating or editing events, you will see options to tie the event to specific membership groups. You can find more details', 'profilegrid-user-profiles-groups-and-communities' ),
                    esc_url( $eventprime_url )
                )
            );
            ?>
        
            <a href="https://profilegrid.co/enable-users-to-create-group-events/" target="_blank"><?php esc_html_e( 'here', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
        </div>
    </div>
    
    <!--EventPrime Integration Active End-->
    
     <!--Stripe Payment Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-stripe-payment-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/stripe-logo.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Stripe Keys, Currency etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Stripe specific options are available in Payments section of the Core Settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <a href="admin.php?page=pm_payment_settings"><?php esc_html_e( 'Go there now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
        </div>
    </div>
    
    <!--Stripe Payment Active End-->
    
    
    
        <!--Advanced Group Manager Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-frontend-group-manager-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/frontend-group-manager.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'More power to your group managers!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Congratulations, your group managers now have additional powers to moderate group content and members! Group managers will now see a new Settings tab on group pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
    </div>
    
    <!--Advanced Group Manager Active End-->
    
    
       <!--Multi Group Managers Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-multi-group-managers-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/multi-admins.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define additional group managers.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php
            $multi_manager_details_url = 'https://profilegrid.co/allow-multiple-group-managers-wordpress/';
            $agm_url                   = 'https://profilegrid.co/extensions/frontend-group-manager/';
            echo wp_kses_post(
                sprintf(
                    /* translators: 1: documentation link, 2: Advanced Group Manager extension link. */
                    __( 'Now add more than one group manager to a single group! To do this, edit a group in your Dashboard &#8594; ProfileGrid &#8594; Groups. You will now see a new option to add additional group managers. You can find more details <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a>. If you have <a href="%2$s" target="_blank" rel="noopener noreferrer">Advanced Group Manager</a> installed, current group managers can also add other members as managers. ', 'profilegrid-user-profiles-groups-and-communities' ),
                    esc_url( $multi_manager_details_url ),
                    esc_url( $agm_url )
                )
            );
            ?>
        

        </div>
    </div>
    
    <!--Multi Group Managers Active End-->
    
    
    <!--User Profile Labels Active-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-user-labels-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-user-labels.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Add and edit label properties.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'Your users can now add labels to their profiles to highlight additional information. The option will automatically appear when they edit their profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
            <a href="https://profilegrid.co/allow-users-add-profile-labels/" target="_blank"><?php esc_html_e( 'Here are more details about this feature.', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
        </div>
    </div>
    
    <!--User Profile Labels Active End-->
    
    
    
    <!--Hero Banner-->
                
    <div class="pg-extension-wrap pg-extension-active" id="pg-hero-banner-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/hero-banner.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Define which images to display on banner.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php
            $hero_banner_url = 'https://profilegrid.co/add-user-hero-banner-wordpress/';
            echo wp_kses_post(
                sprintf(
                    /* translators: %s is a link to the Hero Banner documentation. */
                    __( 'You can now add banners on your website which show profile images of your users as attractive grids! Just head on to Dashboard &#8594; Appearance &#8594; Widgets, and find a new widget named Hero Banner. It has a bunch of configurable option to match your site\'s look and theme and can fit inside any widget position. <a href="%s" target="_blank" rel="noopener noreferrer"> Learn more.</a> ', 'profilegrid-user-profiles-groups-and-communities' ),
                    esc_url( $hero_banner_url )
                )
            );
            ?>
        </div>
    </div>
    
    <div class="pg-extension-wrap pg-extension-active" id="pg-featured-group-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/featured-group.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Turn Featured Group on/off.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php echo wp_kses_post( __( 'You can now show featured membership groups on your website as widgets! Install the extension and head on to Dashboard &#8594; Appearance &#8594; Widgets, and find a new widget named Featured Group. It has a bunch of configurable option to match your site’s look and theme and can fit inside any widget position.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?>
        
        </div>
    </div>
    
    <div class="pg-extension-wrap pg-extension-active" id="pg-elementor-content-restrictions-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/elementor-content-restrictions.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Restrict content with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'You can now restrict content with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
        </div>
    </div>
    
     <div class="pg-extension-wrap pg-extension-active" id="pg-elementor-user-login-ext-active" style="display: none">
    <div class="pg-extension-modal-icon"> <img src="<?php echo esc_url( $path . 'images/pg-elementor-user-login.png' ); ?>" class="options" alt="options"></div>
       
        <div class="pg-extension-modal-title"> <?php esc_html_e( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-subhead"><?php esc_html_e( 'Building beautiful custom login pages with Elementor', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        <div class="pg-extension-modal-active-extension"><?php echo wp_kses_post( __( '<strong>Congratulations</strong>, you have successfully installed and activated this extension!', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>

        <div class="pg-extension-modal-des">
            <?php esc_html_e( 'You can now building beautiful custom login pages with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
        </div>
    </div>
    
    <!--Hero Banner End-->
                
<?php do_action( 'activated_extension_popup' ); ?>
    
    <!--Activated Extensions Popup End-->
    
    
    
    
    
    

            </div>
        </div>
    </div>
