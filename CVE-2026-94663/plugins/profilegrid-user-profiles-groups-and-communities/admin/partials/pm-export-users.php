<?php
$dbhandler      = new PM_DBhandler();
$pmrequests     = new PM_request();
$pmexportimport = new PM_Export_Import();
$path           =  plugin_dir_url( __FILE__ );
$groups         =  $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ) );
if ( filter_input( INPUT_POST, 'export_users' ) ) {
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( !wp_verify_nonce( $retrieved_nonce, 'pm_export_users' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    $pmexportimport->pm_generate_csv( 'USERS', $_POST, 'export-users' );

}
?>

<div class="uimagic">
  <form name="pm_export_users" id="pm_export_users" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Export Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">

      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Select Group(s):', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
          <select name="pm_groups[]" id="pm_groups" multiple onchange="pm_ajax_export_fields_dropdown()">
            <?php
            foreach ( $groups as $group ) {
				?>
                <option value="<?php echo esc_attr( $group->id ); ?>" 
                                          <?php
											if ( !empty( $gid ) ) {
												selected( $gid, $group->id );}
											?>
                ><?php echo esc_html( $group->group_name ); ?></option>
				<?php
            }
            ?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Step 1: Select the Group(s) from which you wish to export users. Press ctrl on Windows/Linux PC and ⌘ on Mac while selecting to choose multiple Groups.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/tools/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div id="pm-fields-container">
            
            
      </div>
      
     <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Columns separated by:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
            <input type="text" name="pm_separator" id="pm_separator" value="," />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Step 3: Select the separator to separate columns in your CSV file. "," is most common separator and MS Excel, Apple Numbers and most other popular spreadsheet programs will open it normally. But if you want to import CSV in a specific app, do check what it recommends for separator.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="buttonarea"> <a href="admin.php?page=pm_tools">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'pm_export_users' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Export', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="export_users" id="export_users" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
