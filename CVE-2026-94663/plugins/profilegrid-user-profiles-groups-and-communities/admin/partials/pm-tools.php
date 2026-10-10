<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
?>

<div class="uimagic">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php esc_html_e( 'Tools', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div>
    <div class="uimsubheader"> </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_export_users">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/export-users.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Export Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Exporting made super simple!', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_import_users">
      <div class="pm_setting_image"> 
          <img src="<?php echo esc_url( $path . 'images/import-users.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Import Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Different options to add users to your site from CSV file', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_export_options">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/export-options.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Save Configuration', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Download plugin settings file.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_import_options">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/import-options.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'Load Configuration', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Upload plugin settings file.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_performance_options">
      <div class="pm_setting_image"> 
      	<img src="<?php echo esc_url( $path . 'images/performance.png' ); ?>" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php esc_html_e( 'performance', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
          <span class="pm-setting-description"><?php esc_html_e( 'Increase site performance.', 'profilegrid-user-profiles-groups-and-communities' ); ?></span> 
      </div>
    </a> 
    </div>
      
      <div class="buttonarea">
          <a href="admin.php?page=pm_settings">
              <div class="cancel">&#8592; &nbsp;
                  <?php esc_html_e( 'Back', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </div>
          </a>
      </div>
      
  </div>
</div>
