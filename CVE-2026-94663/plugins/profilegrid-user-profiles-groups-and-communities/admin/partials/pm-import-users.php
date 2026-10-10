<?php
$dbhandler      = new PM_DBhandler();
$pmrequests     = new PM_request();
$pmexportimport = new PM_Export_Import();
$path           =  plugin_dir_url( __FILE__ );
$groups         =  $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ) );
?>

<div class="uimagic">
    <form name="pm_import_users" id="pm_import_users" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" >
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Import Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">

      </div>
      
      
     <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Upload CSV File', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_required">
            <input type="file" name="uploadcsv" id="uploadcsv" onchange="pm_upload_csv()" />
            <input type="hidden" name="action" value="pm_upload_csv" id="action" />
            <input type="hidden" name="pm_import_step" id="pm_import_step" value="1" />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Step 1: Alrighty then, let\'s get rolling! First step is simple. Select the CSV file containing users you wish to import to ProfileGrid.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/tools/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
        <div id="import_hidden_form">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Update Existing Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
             <div class="uiminput">
                <input name="update_existing_users" id="update_existing_users" type="checkbox"  class="pm_toggle" value="1"/>
                <label for="update_existing_users"></label>
              </div>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Step 2: Now there\'s a chance that this CSV file might have user(s) with same email as those already registered on your site. Turning this option ON will update the existing users with field data from this sheet. If you keep it OFF, ProfileGrid will simply ignore the data for these users. Secondly, if username is same (but email is different), ProfileGrid will create a new user with a random number appended to username in the CSV.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Columns separated by:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
             <div class="uiminput">
                 <input type="text" value="," id="pm_separator"  name="pm_separator" class="pm_separator" />
              </div>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Step 3: Select the column separator in your sheet. If you are not sure, keep it as comma (,) the most common CSV separator.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
            <select name="import_pm_group" id="import_pm_group">
            <option value=""><?php esc_html_e( 'Select Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
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
        <div class="uimnote"><?php esc_html_e( 'Step 4: The Group in which you want to import the users from CSV.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div id="preview-csv-file">
     
      </div>
       
  
       
        <div id="pm_import_hidden_field"></div> 
        <div id="pm_import_preview"></div> 
    </div>
      <div class="buttonarea"> <a href="admin.php?page=pm_tools">    
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'pm_import_users' ); ?>
          <input form="pm_import_users2" type="submit" value="<?php esc_attr_e( 'Import', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="import_users" id="import_users" />
          <div id="pm_import_user_loader" style="display:none;">
              <img src="<?php echo esc_url( $path . 'images/ajax-loader.gif' ); ?>" />
          </div>
      </div>
    </div>
  </form>
</div>
 <div class="pg-import-notification uimagic">
    <p><?php esc_html_e( "Before mapping the fields, try to make sure that the 'type' of the mapped field is same as the data contained in CSV field. For example, map number data to a number field for best results. If you create a new field during the import, you can always set its type later from Fields Manager. By default, ProfileGrid saves all data as text strings to make sure mismatches don't interrupt import process.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
</div>
