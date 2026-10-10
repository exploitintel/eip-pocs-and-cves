<?php
$dbhandler      = new PM_DBhandler();
$pmrequests     = new PM_request();
$pmexportimport = new PM_Export_Import();
$path           =  plugin_dir_url( __FILE__ );
?>

<div class="uimagic">
    <form name="pm_import_options" id="pm_import_options" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" >
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Import Options', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
          
      </div>
      
      
     <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Upload Json File', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_required">
            <input type="file" name="uploadjson" id="uploadjson"/>
            <input type="hidden" name="action" value="pm_upload_json" id="action" />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"></div>
      </div>
        <div id="pm_import_result"></div> 
      <div class="buttonarea"> <a href="admin.php?page=pm_tools">    
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'pm_import_options' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Import', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="import_options" id="import_options" onclick="return pm_upload_jsonfile()" />
          <div id="pm_import_options_loader" style="display:none;">
              <img src="<?php echo esc_url( $path . 'images/ajax-loader.gif' ); ?>" />
          </div>
      </div>
    </div>
  </form>
</div>
 <div class="pg-import-notification uimagic">
     <p style="color:red;"><?php esc_html_e( 'WARNING! This will overwrite all existing Global Settings option values, please proceed with caution!', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
</div>
