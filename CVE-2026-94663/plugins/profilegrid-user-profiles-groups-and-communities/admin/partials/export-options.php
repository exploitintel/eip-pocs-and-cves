<?php
$dbhandler      = new PM_DBhandler();
$pmrequests     = new PM_request();
$pmexportimport = new PM_Export_Import();
$path           =  plugin_dir_url( __FILE__ );
if ( filter_input( INPUT_POST, 'export_options' ) ) {
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( !wp_verify_nonce( $retrieved_nonce, 'pm_export_options' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    $pmexportimport->pm_generate_options_json( 'export-options' );
}
?>

<div class="uimagic">
  <form name="pm_export_options" id="pm_export_options" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Export Options', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
   <div class="uimrow">
      <div class="pg-uim-notice">
          <?php esc_html_e( 'Here you can download your current Global Settings option values. Keep this safe as you can use it as a backup should anything go wrong, or you can use it to restore your settings on this site (or any other site).', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        
      </div>   
   </div>
      
    
  
   
      <div class="buttonarea"> <a href="admin.php?page=pm_tools">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'pm_export_options' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Export', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="export_options" id="export_options" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
