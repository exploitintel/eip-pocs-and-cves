<?php
$path                  =  plugin_dir_url( __FILE__ );
$pmrequest             = new PM_request();
$deactivate_extensions = $pmrequest->pg_check_premium_extension();
?>
<?php if ( !in_array( 'Profilegrid_Menu_Restriction', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-menu-restrictions-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/menu_restrictions.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Define groups to show/ hide menus.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
   </div>
<?php endif; ?>
<?php if ( !in_array( 'Profilegrid_EventPrime_Integration', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-ep-integration-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-ep-integration.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Configure integration with events.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_Stripe_Payment', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-stripe-payment-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/stripe-logo.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Stripe Keys, Currency etc.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_Admin_Power', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-frontend-group-manager-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/frontend-group-manager.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'More power to your group managers!', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
<?php if ( !in_array( 'Profilegrid_Group_Multi_Admins', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-multi-group-managers-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/multi-admins.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Define additional group managers.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
<?php if ( !in_array( 'Profilegrid_Profile_Labels', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-user-labels-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-user-labels.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Add and edit label properties.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
<?php if ( !in_array( 'Profilegrid_Hero_Banner', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-hero-banner-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/hero-banner.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Define which images to display on banner.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_featured_group', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-featured-group-ext-active" onclick="CallExtensionModal(this)">
    <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/featured-group.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Featured Group Widget.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_elementor_content_restrictions', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-elementor-content-restrictions-ext-active" onclick="CallExtensionModal(this)">
  <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/elementor-content-restrictions.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Restrict content with Elementor page builder', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_elementor_login_logout_widget', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-elementor-user-login-ext-active" onclick="CallExtensionModal(this)">
    <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-elementor-user-login.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Building beautiful custom login pages with Elementor', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_Woocommerce_Product_Restrictions', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-woocommerce-product-restrictions-ext-active" onclick="CallExtensionModal(this)">
    <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-group-product-restriction.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'WooCommerce Customized Product Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Adds control over WooCommerce products visibility to ProfileGrid Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>

<?php if ( !in_array( 'Profilegrid_Credit', $deactivate_extensions ) ) : ?>
<div class="uimrow pg-no-setting-extension pg-extension-modal" data-popup="pg-terawallet-integration-ext-active" onclick="CallExtensionModal(this)">
    <div class="pm_setting_image"> <img src="<?php echo esc_url( $path . 'images/pg-wallet-icon.png' ); ?>" class="options" alt="options"> </div>
  <div class="pm-setting-heading"> <span class="pm-setting-icon-title">
    <?php esc_html_e( 'Customized TeraWallet Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> <span class="pm-setting-description">
    <?php esc_html_e( 'Offers powerful Group based credit control to admins and Group leaders.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </span> </div>
</div>
<?php endif; ?>
