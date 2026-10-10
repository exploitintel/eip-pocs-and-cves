<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$pmemails   = new PM_Emails();
$textdomain = $this->profile_magic;
$path       = plugin_dir_url( __FILE__ );
$id         = filter_input( INPUT_GET, 'id' );

$user_info = get_userdata( $id );
if ( $user_info != false ) {
	$avatar       = get_avatar( $user_info->user_email, 300, '', false, array( 'force_display' => true ) );
	$userrole     = $pmrequests->get_userrole_name( $id );
	$customfields = $pmrequests->get_user_custom_fields_data( $id );
	$gids         = $pmrequests->profile_magic_get_user_field_value( $id, 'pm_group' );
	$gid          = $pmrequests->pg_filter_users_group_ids( $gids );
	if ( ! empty( $gid ) ) {
		$gid_in = 'gid in(' . implode( ',', $gid ) . ')';
	} else {
		$gid_in = '';
	}
	if ( isset( $gid[0] ) ) {
		$groupinfo = $dbhandler->get_row( 'GROUPS', $gid[0] );
	}
}
$current_user = wp_get_current_user();

if ( isset( $groupinfo ) ) {
	if ( $groupinfo->is_group_leader != 0 ) {
		$group_leader = username_exists( $groupinfo->leader_username );
	} else {
		$group_leader = 0;}
	$sections = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), 1, 'results', 0, false, 'ordering', false, $gid_in );
}

if ( filter_input( INPUT_POST, 'deactivate' ) ) {
    if(wp_verify_nonce(filter_input( INPUT_POST, '_wpnonce' ), 'pm_single_user_action')){
	
	$uid = filter_input( INPUT_POST, 'uid' );
	if ( $uid != $current_user->ID ) {
		update_user_meta( $uid, 'rm_user_status', '1' );
				do_action( 'pg_user_suspended', $uid );
		if ( ! empty( $gid ) ) :
			$pmemails->pm_send_group_based_notification( $gid[0], $uid, 'on_user_deactivate' );
				endif;
	}
    }else{
        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
}

if ( filter_input( INPUT_POST, 'activate' ) ) {
    if(wp_verify_nonce(filter_input( INPUT_POST, '_wpnonce' ), 'pm_single_user_action')){
	$uid = filter_input( INPUT_POST, 'uid' );
	if ( $uid != $current_user->ID ) {
		update_user_meta( $uid, 'rm_user_status', '0' );
		if ( ! empty( $gid ) ) :
			$pmemails->pm_send_group_based_notification( $gid[0], $uid, 'on_user_activate' );
                        do_action('pm_user_activated', $uid);
                endif;
	}
    }else{
        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
}

if ( filter_input( INPUT_POST, 'delete' ) ) {
    if(wp_verify_nonce(filter_input( INPUT_POST, '_wpnonce' ), 'pm_single_user_action')){
	
	$uid = filter_input( INPUT_POST, 'uid' );
	if ( $uid != $current_user->ID ) {
		wp_delete_user( $uid );
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_user_manager' ) );
	exit;
    }else{
        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
}

if ( $id == $current_user->ID ) {
	$class = 'rm_current_user';
} else {
	$class = '';
}

if ( $user_info == false ) {
	echo '<div class="pmagic"><div class="pm_message">' . esc_html__( 'This user no longer exists.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div></div>';
} else {
	?>
<div class="pmagic"> 
  
  <!-----Operationsbar Starts----->
  
  <div class="operationsbar">
	<div class="pmtitle"><?php echo esc_html( $user_info->user_login ); ?></div>
	<div class="icons"> </div>
	<form name="pm_single_user" id="pm_single_user" method="post">
	<input type="hidden" value="<?php echo esc_attr( $id ); ?>" name="uid" />
        <?php wp_nonce_field('pm_single_user_action'); ?>
	<div class="nav">
	  <ul>
		<li><a href="user-edit.php?user_id=<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Edit', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
		<?php if ( $id != $current_user->ID ) { ?>
		<li><input type="submit" name="delete" value="<?php esc_attr_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="return pg_confirm('<?php esc_attr_e( 'Are you sure you want to delete this user permanently? You cannot undo this action.', 'profilegrid-user-profiles-groups-and-communities' ); ?>')" /></li>
			<?php if ( $pmrequests->profile_magic_get_user_field_value( $id, 'rm_user_status' ) == 1 ) : ?>
		<li><input type="submit" name="activate" value="<?php esc_attr_e( 'Activate', 'profilegrid-user-profiles-groups-and-communities' ); ?>" /></li>
		<?php else : ?>
		<li><input type="submit" name="deactivate" value="<?php esc_attr_e( 'Deactivate', 'profilegrid-user-profiles-groups-and-communities' ); ?>" /></li>
		<?php endif; } ?>
		
	  </ul>
	</div>
   </form>
	
  </div>
  <!--------Operationsbar Ends-----> 
  
  <!----User Area Starts---->
  
  <div class="pm-user-area">
	<div class="pm-user-info">
	  <div class="pm-profile-image"><?php echo wp_kses_post( $avatar ); ?> </div>
	  <div class="pm-profile-fields">
		<div class="pm-profile-field-row">
		  <div class="pm-field-label"><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
		  <div class="pm-field-value"><?php echo esc_html( $user_info->first_name ); ?></div>
		</div>
		<div class="pm-profile-field-row">
		  <div class="pm-field-label"><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
		  <div class="pm-field-value"><?php echo esc_html( $user_info->last_name ); ?></div>
		</div>
		<div class="pm-profile-field-row">
		  <div class="pm-field-label"><?php esc_html_e( 'Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
		  <div class="pm-field-value"><?php echo esc_html( $user_info->user_email ); ?></div>
		</div>
		<div class="pm-profile-field-row">
		  <div class="pm-field-label"><?php esc_html_e( 'Role', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
		  <div class="pm-field-value"><?php echo esc_html( $userrole ); ?></div>
		</div>
		<div class="pm-profile-field-row">
		  <div class="pm-field-label"><?php esc_html_e( 'Bio', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
		  <div class="pm-field-value"><?php echo wp_kses_post( $user_info->description ); ?></div>
		</div>
	  </div>
	</div>
	<div id="tabs">
	<?php
	if ( isset( $groupinfo ) ) :
		?>
	<ul class="pm-profile-nav">
		<?php
		foreach ( $sections as $section ) :
			echo '<li class="pm-profile-nav-item"><a href="#' . sanitize_key( $section->section_name . '_' . $section->id ) . '">' . esc_html( $section->section_name ) . '</a></li>';
		endforeach;
                do_action('pm_user_profile_tabs', $id);
		?>
		<?php do_action('profilegrid_dashboard_member_profile_top_menus');?>
	</ul>
	
	
		<?php
		foreach ( $sections as $section ) :
			?>
	  <div class="pm-user-content" id="<?php echo sanitize_key( $section->section_name . '_' . $section->id ); ?>">
	  <div class="pm-profile-fields">
			<?php
			$fields = $pmrequests->pm_get_backend_user_meta( $id, $gid, $group_leader, '', $section->id, '"first_name","last_name","description","user_avatar","user_pass","user_name","user_email","heading","paragraph","confirm_pass"' );

			if ( ! empty( $fields ) ) :
				foreach ( $fields as $field ) :
					?>
					<?php
					$field_value = $pmrequests->profile_magic_get_user_field_value( $id, $field->field_key, $field->field_type );
					$field_value = maybe_unserialize( $field_value );
					$value       = '';

					if ( is_array( $field_value ) ) :
						if ( $field->field_type == 'address' ) {
							$options = maybe_unserialize( $field->field_options );
							foreach ( $field_value as $key => $fv ) {
								if ( ! isset( $options[ $key ] ) ) {
									unset( $field_value[ $key ] );
								}
							}
						}
						if ( $field->field_type == 'checkbox' ) {
							foreach ( $field_value as $key => $fv ) {
								if ( $fv == 'chl_other' ) {
									unset( $field_value[ $key ] );
								}
							}
						}

						foreach ( $field_value as $val ) {
							if ( $val != '' ) {
								$value .= '<div class="rm-field-multiple-value">' . $val . '</div>';
							}
						}
				else :
						$value = $field_value;
				endif;
				?>

					<?php if ( $value != '' ) : ?>
		<div class="pm-profile-field-row">
		  <div class="pm-field-label">
			<div class="pm-user-field-icon">
						<?php
						if ( isset( $field ) && $field->field_icon != 0 ) :
							echo wp_get_attachment_image( $field->field_icon, array( 16, 16 ), true, false );
			   endif;
						?>
			</div>
						<?php echo esc_html( $field->field_name ); ?>:</div>
		  <div class="pm-field-value"><?php echo wp_kses_post(apply_filters('pg_user_profile_meta_fields_html',wp_kses_post( $value ), $field->field_type)); ?></div>
		</div>
						<?php
				endif;
				endforeach;

				else :
					?>
		  <div class="pmnotice"><?php esc_html_e( 'No User Profile Fields in this section.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
					<?php
				endif;
				?>
	  </div>
	  </div>
			<?php
	endforeach;
        do_action('pm_user_profile_tab_content', $id);
		?>
		<?php do_action('profilegrid_dashboard_member_profile_top_menus_content');?>
		<?php
	else :
		echo '<div class="pg-uim-notice">' . esc_html__( 'This profile is not yet associated with any parent profile group. To make it visible please associate it with a group first.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
	endif;
	?>
	
	</div>
  </div>
</div>
	<?php
}

