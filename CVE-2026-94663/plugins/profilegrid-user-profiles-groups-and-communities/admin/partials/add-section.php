<?php
$dbhandler    = new PM_DBhandler();
$pm_activator = new Profile_Magic_Activator();
$pmrequests   = new PM_request();
$textdomain   = $this->profile_magic;
$path         =  plugin_dir_url( __FILE__ );
$identifier   = 'SECTION';
$id           = filter_input( INPUT_GET, 'id' );
$gid          = filter_input( INPUT_POST, 'gid' );
if ( $gid==false || $gid==null ) {
	$gid = filter_input( INPUT_GET, 'gid' );
}
$groups =  $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ) );
if ( $id==false || $id==null ) {
    $id       =0;
	$lastrow  = $dbhandler->pm_count( $identifier );
	$lastrow  = $dbhandler->get_all_result( $identifier, 'id', 1, 'var', 0, 1, 'id', 'DESC' );
	$ordering = $lastrow + 1;
} else {
    $row = $dbhandler->get_row( $identifier, $id );

	if ( !empty( $row ) ) {
		if ( $row->section_options!='' ) {
			$field_options = maybe_unserialize( $row->section_options );
        }
		$ordering = $row->ordering;
		$gid      = $row->gid;
	}
}
if ( filter_input( INPUT_POST, 'submit_section' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_pm_add_section' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$section_id = filter_input( INPUT_POST, 'section_id' );
	$exclude    = array( '_wpnonce', '_wp_http_referer', 'submit_section', 'section_id' );
	$post       = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		foreach ( $post as $key=>$value ) {
			$data[ $key ] = $value;
			$arg[]        = $pm_activator->get_db_table_field_type( $identifier, $key );
		}
	}
	if ( $section_id==0 ) {
	    $dbhandler->insert_row( $identifier, $data, $arg );
	} else {
		$dbhandler->update_row( $identifier, 'id', $section_id, $data, $arg, '%d' );
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_profile_fields&gid=' . $post['gid'] ) );
	exit;
}

if ( filter_input( INPUT_GET, 'delete' ) ) {
	$selected = filter_input( INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

	foreach ( $selected as $sid ) {
		$dbhandler->remove_row( $identifier, 'id', $sid, '%d' );
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_profile_fields&gid=' . $post['gid'] ) );
	exit;
}

if ( filter_input( INPUT_POST, 'duplicate' ) ) {
	$selected = filter_input( INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( $selected as $sid ) {
		$data = $dbhandler->get_row( $identifier, $sid, 'id', 'ARRAY_A' );
		unset( $data['id'] );
		$dbhandler->insert_row( $identifier, $data );
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_profile_sections&gid=' . $gid ) );
	exit;
}

if ( filter_input( INPUT_GET, 'action' )=='delete' ) {
	$dbhandler->remove_row( 'FIELDS', 'associate_section', $id, '%d' );
	$dbhandler->remove_row( $identifier, 'id', $id, '%d' );

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_profile_fields&gid=' . filter_input( INPUT_GET, 'gid' ) ) );
	exit;
}


?>

<div class="uimagic">
  <form name="pm_add_section" id="pm_add_section" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <?php if ( $id==0 ) : ?>
      <div class="uimheader">
			<?php esc_html_e( 'New Section', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php else : ?>
      <div class="uimheader">
		  <?php esc_html_e( 'Edit Section', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php endif; ?>
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Section Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          <sup>*</sup></div>
        <div class="uiminput pm_required">
          <input type="text" name="section_name" id="section_name" value="<?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->section_name );}
			?>" />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Enter a new name for this Profile Section.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
  
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Associate with Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_select_required">
          <select name="gid" id="gid">
            <option value=""><?php esc_html_e( 'Select A Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
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
        <div class="uimnote"><?php esc_html_e( 'Move this Profile section to another Group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
    
  
      
      <div class="buttonarea"> <a href="admin.php?page=pm_profile_fields&gid=<?php echo esc_attr( $gid ); ?>">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <input type="hidden" name="section_id" id="section_id" value="<?php echo esc_attr( $id ); ?>" />
        <input type="hidden" name="ordering" id="ordering" value="<?php echo esc_attr( $ordering ); ?>" />
        <?php wp_nonce_field( 'save_pm_add_section' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_section" id="submit_section" onClick="return add_section_validation()"  />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
