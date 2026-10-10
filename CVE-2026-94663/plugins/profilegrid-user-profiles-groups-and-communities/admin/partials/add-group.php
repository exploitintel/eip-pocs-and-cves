<?php
$dbhandler      = new PM_DBhandler();
$pm_activator   = new Profile_Magic_Activator();
$pmrequests     = new PM_request();
$basicfunctions = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
$textdomain     = $this->profile_magic;
$path           =  plugin_dir_url( __FILE__ );
$identifier     = 'GROUPS';
$group_options  = array();

$email_template =  $dbhandler->get_all_result( 'EMAIL_TMPL', array( 'id', 'tmpl_name' ) );
$id             = filter_input( INPUT_GET, 'id' );

if ( $id==false || $id==null ) {
    $id            =0;
    $is_leader     = '';
    $leader_name   = '';
    $group_leaders = '';
} else {
    $row = $dbhandler->get_row( $identifier, $id );
	if ( $row->group_options!='' ) {
		$group_options = maybe_unserialize( $row->group_options );
    }
	if ( !empty( $row ) && $row->leader_rights!='' ) {
		$leader_rights = maybe_unserialize( $row->leader_rights );
	}
        $is_leader     = $row->is_group_leader;
        $leader_name   = $row->leader_username;
        $group_leaders = maybe_unserialize( $row->group_leaders );
}

$args      = array(
	'meta_key'     => 'pm_group',
	'meta_value'   => sprintf( ':"%s";', $id ),
	'meta_compare' => 'like',
);
$all_users = get_users( $args );
if ( filter_input( INPUT_POST, 'submit_group' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_pm_add_group' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$groupid  = filter_input( INPUT_POST, 'group_id' );
	$exclude  = array( '_wpnonce', '_wp_http_referer', 'submit_group', 'group_id' );
        $post = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( isset( $post['group_leaders'] ) && is_array( $post['group_leaders'] ) ) {
		if ( isset( $post['group_leaders']['primary'] ) ) {
                $primary_admin[]  = $post['group_leaders']['primary'];
                $secondary_admins =  array_diff( $post['group_leaders'], $primary_admin );
                $key              = array_search( $primary_admin[0], $secondary_admins, true );
		} else {
			$primary_admin    = array();
			$secondary_admins =  $post['group_leaders'];
		}

		if ( $key ) {
			unset( $post['group_leaders'][ $key ] );
		}
	}

	if ( $post!=false ) {
		if ( !isset( $post['is_group_limit'] ) ) {
			$post['is_group_limit'] = 0;
        }
		if ( !isset( $post['is_group_leader'] ) ) {
			$post['is_group_leader'] = 0;
        }
		if ( !isset( $post['show_success_message'] ) ) {
			$post['show_success_message'] = 0;
        }

		foreach ( $post as $key=>$value ) {
			$data[ $key ] = $value;
			$arg[]        = $pm_activator->get_db_table_field_type( $identifier, $key );
		}
	}
	if ( $groupid==0 ) {
	    $gid = $dbhandler->insert_row( $identifier, $data, $arg );
            $pmrequests->profile_magic_set_group_leader( $gid );
            $section_data = array(
				'gid'          =>$gid,
				'section_name' =>'Personal Details',
				'ordering'     =>$gid,
			);
            $section_arg  = array( '%d', '%s', '%d' );
            $sid          = $dbhandler->insert_row( 'SECTION', $section_data, $section_arg );
            $lastrow      = $dbhandler->pm_count( 'FIELDS' );
            $lastrow      = $dbhandler->get_all_result( 'FIELDS', 'field_id', 1, 'var', 0, 1, 'field_id', 'DESC' );
            $ordering     = $lastrow + 1;
            $field_option = 'a:15:{s:17:"place_holder_text";s:0:"";s:19:"css_class_attribute";s:0:"";s:14:"maximum_length";s:0:"";s:13:"default_value";s:0:"";s:12:"first_option";s:0:"";s:21:"dropdown_option_value";s:0:"";s:18:"radio_option_value";a:1:{i:0;s:0:"";}s:14:"paragraph_text";s:0:"";s:7:"columns";s:0:"";s:4:"rows";s:0:"";s:18:"term_and_condition";s:0:"";s:18:"allowed_file_types";s:0:"";s:12:"heading_text";s:0:"";s:11:"heading_tag";s:2:"h1";s:5:"price";s:0:"";}';
            $field_data   = array(
				'field_name'          =>'Email',
				'field_type'          =>'user_email',
				'field_options'       =>$field_option,
				'field_icon'          =>0,
				'associate_group'     =>$gid,
				'associate_section'   =>$sid,
				'show_in_signup_form' =>1,
				'is_required'         =>1,
				'ordering'            =>$ordering,
				'field_key'           =>'user_email',
			);
            $field_arg    = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
            $newgid       = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );
            do_action( 'ProfileGrid_after_create_group', $gid );

	} else {
		$gid = $groupid;
		$dbhandler->update_row( $identifier, 'id', $groupid, $data, $arg, '%d' );
                do_action( 'ProfileGrid_after_update_group_setting', $gid, $data, $group_options );
                do_action( 'profilegrid_group_update', $data, $row, $groupid );
	}
	do_action( 'pg_groupleader_assign_remove', $gid, $is_leader, $group_leaders, $post['is_group_leader'], $post['group_leaders'] );
        wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_manage_groups' ) );
	exit;
}
if ( filter_input( INPUT_POST, 'delete' ) ) {

    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( !wp_verify_nonce( $retrieved_nonce, 'delete_pm_group' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }

	$selected = filter_input( INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( $selected as $gid ) {
                do_action( 'profilegrid_group_delete', $gid );
                $get              = array( 'gid'=>$gid );
                $meta_query_array = $pmrequests->pm_get_user_meta_query( $get );
                $user_query       =  $dbhandler->pm_get_all_users_ajax( '', $meta_query_array );
                $users            = $user_query->get_results();
                //echo count($users);die;
		foreach ( $users as $user ) {
			$pmrequests->pg_unassign_group_during_delete_group( $user->ID, $gid );
		}

                $dbhandler->remove_row( 'FIELDS', 'associate_group', $gid, '%d' );
                $dbhandler->remove_row( 'SECTION', 'gid', $gid, '%d' );
		$dbhandler->remove_row( $identifier, 'id', $gid, '%d' );


	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_manage_groups' ) );
	exit;
}

if ( filter_input( INPUT_POST, 'duplicate' ) ) {
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( !wp_verify_nonce( $retrieved_nonce, 'duplicate_pm_group' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    
	$selected = filter_input( INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( $selected as $gid ) {
		$data                                    =$dbhandler->get_row( $identifier, $gid, 'id', 'ARRAY_A' );
                $group_options_old               = maybe_unserialize( $data['group_options'] );
                $group_options_old['group_type'] ='open';
                $data['group_leaders']           = '';
                $data['is_group_leader']         = '0';
                $data['group_options']           = maybe_serialize( $group_options_old );
                $oldgid                          = $data['id'];
		unset( $data['id'] );
		$newgid = $dbhandler->insert_row( $identifier, $data );
                unset( $data );
                $sections =  $dbhandler->get_all_result( 'SECTION', '*', array( 'gid'=>$oldgid ), 'results', 0, false, null, false, '', 'ARRAY_A' );
		foreach ( $sections as $section ) {
			$oldsectionid = $section['id'];
			unset( $section['id'] );
			$section['gid'] =$newgid;
			$newsection_id  = $dbhandler->insert_row( 'SECTION', $section );
			unset( $section );
			$fields =  $dbhandler->get_all_result(
                'FIELDS',
                '*',
                array(
					'associate_group'   =>$oldgid,
					'associate_section' =>$oldsectionid,
                ),
                'results',
                0,
                false,
                null,
                false,
                '',
                'ARRAY_A'
            );
			foreach ( $fields as $field ) {
				unset( $field['field_id'] );
				$lastrow                    = $dbhandler->get_all_result( 'FIELDS', 'field_id', 1, 'var', 0, 1, 'field_id', 'DESC' );
				$ordering                   = $lastrow + 1;
				$field['ordering']          = $ordering;
				$field['field_key']         = $pmrequests->get_field_key( $field['field_type'], $ordering );
				$field['associate_group']   = $newgid;
				$field['associate_section'] =$newsection_id;
				$dbhandler->insert_row( 'FIELDS', $field );
				unset( $field );
			}
		}
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_manage_groups' ) );
	exit;
}

?>

<div class="uimagic">
  <form name="pm_add_group" id="pm_add_group" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <?php if ( $id==0 ) : ?>
      <div class="uimheader">
			<?php esc_html_e( 'New Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php else : ?>
      <div class="uimheader">
		  <?php esc_html_e( 'Edit Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php endif; ?>
        <div class="uimrow">
      <div class="pg-uim-notice">
        <?php
        $basicfunctions->null_field_notice();
		//Show subheadings or message or notice
		?>
      </div>
        </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          <sup>*</sup></div>
        <div class="uiminput pm_required">
          <input type="text" name="group_name" id="group_name" value="
          <?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->group_name );}
			?>
            " />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Name of this Group. The name will appear on Single and All Groups page and Member Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Type', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_radio_required">
         <ul class="uimradio">
              <li>
                  <input type="radio" name="group_options[group_type]" id="group_type" value="open" onclick="pm_show_hide_group_type_options('hide')" 
                  <?php
					if ( !empty( $group_options ) ) {
						if ( isset( $group_options['group_type'] ) ) {
							if ( $group_options['group_type']=='open' ) {
								echo 'checked';
							}
						} else {
							 echo 'checked';
						}
					} else {
						echo 'checked';
					}
					?>
                 >
                <?php esc_html_e( 'Open', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
               <li>
                <input type="radio" name="group_options[group_type]" id="group_type" value="closed" onclick="pm_show_hide_group_type_options('show')" 
                <?php
                if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='closed' ) {
					echo 'checked';}
				?>
                >
                <?php esc_html_e( 'Closed', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
          </ul>
            <div class="errortext"></div>
        </div>
           <div class="uimnote"><?php esc_html_e( 'An Open Type Group can be directly joined by filling up registration form, or by clicking Join button on the Group page. Closed groups require Group Managers(or Site Admin) approval, or an invite to join the Group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Description:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <textarea name="group_desc" id="group_desc">
          <?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->group_desc );}
			?>
</textarea>
        </div>
        <div class="uimnote"> <?php esc_html_e( 'Description or details of the group. It will appear on the individual Group page and as intro text on All Groups page.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Icon/ Badge', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput" id="icon_html">
          <input id="group_icon" type="hidden" name="group_icon" class="icon_id" value="
          <?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->group_icon );}
			?>
            " />
          <input id="group_icon_button" class="button group_icon_button" type="button" value="<?php esc_attr_e( 'Upload Icon', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
          <?php
			if ( !empty( $row ) && $row->group_icon!=0 ) {
				echo wp_get_attachment_link( $row->group_icon, array( 50, 50 ), false, true, false );
			}
			?>
          <img src="" width="50px" id="group_icon_img" style="display:none;" />
           <?php
			if ( !empty( $row ) && $row->group_icon!=0 ) {
				?>
			<input type="button" name="remove_group_icon" id="remove_group_icon" class="remove_icon" value="<?php esc_attr_e( 'Remove Icon', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
				<?php
			}
			?>
          
          <div class="errortext" id="icon_error"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Group badge, icon or image. This will appear with group description and on member user profiles belonging to this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
      </div>
        <!--
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Hide Group Card', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="group_options[is_hide_group_card]" id="is_hide_group_card" type="checkbox"  class="pm_toggle" value="1" 
          <?php
			if ( !empty( $group_options ) && isset( $group_options['is_hide_group_card'] ) && $group_options['is_hide_group_card']==1 ) {
				echo 'checked';}
			?>
             style="display:none;" />
          <label for="is_hide_group_card"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Hides the Group card (displaying Group details) on its Group page.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
        -->
         <div class="uimrow" id="grouplimit">
        <div class="uimfield">
          <?php esc_html_e( 'Membership Limit', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="is_group_limit" id="is_group_limit" type="checkbox"  class="pm_toggle" value="1" 
          <?php
			if ( !empty( $row ) && $row->is_group_limit==1 ) {
				echo 'checked';}
			?>
             style="display:none;"  onClick="pm_show_hide(this,'grouplimit_html')" />
          <label for="is_group_limit"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Limit the number of membership slots for this group. Turn off for unlimited members.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div class="childfieldsrow" id="grouplimit_html" style=" 
      <?php
		if ( !empty( $row ) && $row->is_group_limit==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        <div class="uimrow">
          <div class="uimfield">
            <?php esc_html_e( 'Max no. of Members', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput 
          <?php
			if ( !empty( $row ) && $row->is_group_limit==1 ) {
				echo 'pm_required';}
			?>
            ">
            <input type="number" name="group_limit" min="0" id="group_limit" value="
            <?php
            if ( !empty( $row ) ) {
				echo esc_attr( $row->group_limit );}
			?>
            " />
            <div class="errortext"></div>
          </div>
          <div class="uimnote"><?php esc_html_e( 'Define the maximum number of members allowed for this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
        </div>
        <div class="uimrow" id="group_limit_message_html">
          <div class="uimfield">
            <?php esc_html_e( 'Limit Reached Message', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput">
            <?php
            if ( isset( $row ) ) {
				$group_limit_message = $row->group_limit_message;
			} else {
				$group_limit_message = '';
			}
			wp_editor( $group_limit_message, 'group_limit_message'/*,array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => true, 'textarea_rows' => 8) */ );
			?>
             <div class="errortext"></div>
          </div>
          <div class="uimnote"><?php esc_html_e( 'Users trying to register for this group will see this message once all membership slots are filled.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
        </div>
      </div>
      
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
         <div class="uiminput">
          <?php

			if ( !empty( $group_options['group_page'] ) ) {
				$group_page = $group_options['group_page'];
			} else {
				$group_page = '0';
			}
			$group_page_args = array(
				'depth'             => 0,
				'child_of'          => 0,
				'selected'          => esc_attr( $group_page ),
				'echo'              => 1,
				'show_option_none'  => esc_attr__( 'None', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' => '0',
				'name'              => 'group_options[group_page]',
			);
			?>
        <?php
        wp_dropdown_pages(
            array(
				'depth'             => 0,
				'child_of'          => 0,
				'selected'          => esc_attr( $group_page ),
				'echo'              => 1,
				'show_option_none'  => esc_attr__( 'None', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' => '0',
				'name'              => 'group_options[group_page]',
            )
        );

        ?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( "Select the page you want to display when users click on this Group's link. Select 'None' if you want to display the default ProfileGrid page for this Group.", 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Associated WP Role', 'profilegrid-user-profiles-groups-and-communities' ); ?><sup>*</sup>
        </div>
        <div class="uiminput pm_select_required">
          <select name="associate_role" id="associate_role">
            <option value=""><?php esc_html_e( 'Select User Role', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			  $roles = get_editable_roles();
			foreach ( $roles as $key=>$role ) {
				?>
            <option value="<?php echo esc_attr( $key ); ?>" 
                                      <?php
										if ( !empty( $row ) && $row->associate_role==$key ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $role['name'] ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Inherit the access rights for the members of this group based on WP User Role.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
        <div class="uimrow" id="pm_group_admin_html">
        <div class="uimfield">
          <?php esc_html_e( 'Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="is_group_leader" id="is_group_leader" type="checkbox"  class="pm_toggle" value="1" 
          <?php
			if ( !empty( $row ) && $row->is_group_leader==1 ) {
				echo 'checked';}
			?>
             style="display:none;"  onClick="pm_show_hide(this,'groupleaderhtml')" />
          <label for="is_group_leader"></label>
          <div class="errortext"></div>
        </div>
            <?php if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='closed' ) : ?>
            
            <div class="uimnote"><?php esc_html_e( 'You are trying to turn off Group Manager feature for a closed group. This can lead to issues in group membership approval system. It is recommended to assign a Group Manager to each closed group. Alternatively, you can make it an Open group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
          <?php else : ?>
          <div class="uimnote"><?php esc_html_e( 'A Group Manager has special privileges to moderate the Group. Group Managers also have their own privacy levels.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
          <?php endif; ?>
      </div>
      <div class="childfieldsrow" id="groupleaderhtml" style=" 
      <?php
		if ( !empty( $row ) && $row->is_group_leader==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        <div class="uimrow">
            <div class="uimfield">
              <?php esc_html_e( 'Group Manager Label', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="uiminput">
                <input type="text" name="group_options[admin_label]" id="group_options[admin_label]" value="
                <?php
                if ( !empty( $group_options ) && isset( $group_options['admin_label'] ) ) {
					echo esc_attr( $group_options['admin_label'] );}
				?>
                ">
              <div class="errortext"></div>
            </div>
            <div class="uimnote"><?php esc_html_e( "If you wish to rename the label 'Group Manager', you can define it here. For example, Captain, Leader, Instructor etc. Leave empty for the default label.", 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
        </div>
          <?php if ( !class_exists( 'Profilegrid_Group_Multi_Admins' ) ) : ?>
          <div class="uimrow">
          <div class="uimfield">
				<?php esc_html_e( 'Select Group Manager:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput 
				<?php
				if ( !empty( $row ) && $row->is_group_leader==1 ) {
					echo 'pm_group_leader_name pm_select_required';}
				?>
            ">
                <select name="group_leaders[primary]" id="group_leaders">
                  <option value=""><?php esc_html_e( 'Select a Group Member', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                  <?php foreach ( $all_users as $user ) : ?>
                  <option value="<?php echo esc_attr( $user->ID ); ?>" 
                                            <?php
											if ( !empty( $group_leaders ) && isset( $group_leaders['primary'] ) && $group_leaders['primary']==$user->ID ) {
												echo 'selected';}
											?>
                    ><?php echo esc_html( $user->user_login ); ?></option>
                  <?php endforeach; ?>
                </select>
              <div class="errortext"></div>
            <div class="user_name_error"></div>
          </div>
            <div class="uimnote"><?php esc_html_e( 'Select from existing users.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
        </div>
          
				<?php
          else :
              do_action( 'profile_magic_multi_admin_option', $id );

          endif;
			?>
          
       
      </div>
        
      <?php do_action( 'profile_magic_group_option', $id, $group_options ); ?>
       
       <div class="uimrow" id="redirection">
        <div class="uimfield">
          <?php esc_html_e( 'After Registration, Redirect to', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pm_checkbox_required">
         <ul class="uimradio">
              <li>
                <input type="radio" name="group_options[redirect]" id="redirect" value="none" 
                <?php
                if ( !empty( $group_options ) ) {
					if ( isset( $group_options['redirect'] ) && $group_options['redirect']=='none' ) {
						echo 'checked';
					}
				} else {
					echo 'checked';}
				?>
                         onClick="pm_show_hide(this,'','redirect_page_html','redirect_url_html')">
                <?php esc_html_e( 'None', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
               <li>
                <input type="radio" name="group_options[redirect]" id="redirect" value="page" 
                <?php
                if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect']=='page' ) {
					echo 'checked';}
				?>
                 onClick="pm_show_hide(this,'redirect_page_html','redirect_url_html')">
                <?php esc_html_e( 'Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
               <li>
                <input type="radio" name="group_options[redirect]" id="redirect" value="url" 
                <?php
                if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect']=='url' ) {
					echo 'checked';}
				?>
                 onClick="pm_show_hide(this,'redirect_url_html','redirect_page_html')">
                <?php esc_html_e( 'URL', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
          </ul>
        </div>
           <div class="uimnote"><?php esc_html_e( 'Redirect users to a page or URL after they successfully submit the Default Registration Form. Custom Registration forms have redirection settings in their form Dashboards.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div class="childfieldsrow" id="redirect_page_html" style=" 
      <?php
		if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect']=='page' ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <?php
			if ( !empty( $group_options['redirect_page_id'] ) ) {
				$selected = $group_options['redirect_page_id'];
			} else {
				$selected = 0;
			}

			?>
        <?php
        wp_dropdown_pages(
            array(
				'depth'    => 0,
				'child_of' => 0,
				'selected' => esc_attr( $selected ),
				'echo'     => 1,
				'name'     => 'group_options[redirect_page_id]',
            )
        );
		?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Select the page where user will be redirected after registration. Usually this page will have relevant information related to the group of registration process.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      
      </div>
      
      <div class="childfieldsrow" id="redirect_url_html" style=" 
      <?php
		if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect']=='url' ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'URL', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input type="url" name="group_options[redirect_url]" id="group_options[redirect_url]" value="
          <?php
			if ( !empty( $group_options ) && isset( $group_options['redirect_url'] ) ) {
				echo esc_url( $group_options['redirect_url'] );}
			?>
                ">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Enter the URL of the page where the user will be redirected.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
      </div>
      
      </div>
      
      
      <div class="uimrow" id="sucess_message">
        <div class="uimfield">
          <?php esc_html_e( 'Display a Message after Registration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="show_success_message" id="show_success_message" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'success_message_html')" 
          <?php
			if ( !empty( $row ) && $row->show_success_message==1 ) {
				echo 'checked';}
			?>
                />
          <label for="show_success_message"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Users will see a message after they submit Default Registration form for this Group. If redirection is turned on, it will appear for a few seconds before redirection is triggered.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
       <div class="childfieldsrow" id="success_message_html" style=" 
       <?php
		if ( !empty( $row ) && $row->show_success_message==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
       <div class="uimrow" id="notification">
        <div class="uimfield">
          <?php esc_html_e( 'Message Contents', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
        if ( isset( $row ) ) {
			$success_message = $row->success_message;
		} else {
			$success_message = '';
		}
		wp_editor( $success_message, 'success_message' );
		?>
        </div>
           <div class="uimnote"><?php esc_html_e( 'The contents of the message. Rich text is supported.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
       </div>
      
      
      <div class="uimrow" id="notification">
        <div class="uimfield">
          <?php esc_html_e( 'Group Member Emails', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="group_options[enable_notification]" id="enable_notification" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'notification_html')" 
          <?php
			if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) {
				echo 'checked';
			} if ( $id==0 ) {
				echo 'checked';}
			?>
            />
          <label for="enable_notification"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Send custom email notifications to relevant users on group events. If you have not created any custom email templates, you can save the group now, and come back later to assign them.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      <div class="childfieldsrow" id="notification_html" style=" 
      <?php
		if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) {
			echo 'display:block;';
		} elseif ( $id==0 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
       
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On Joining Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            " >
          <select name="group_options[on_registration]" id="on_registration">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
                          <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                                    <?php
													if ( !empty( $group_options ) ) {
														if ( isset( $group_options['on_registration'] ) && $group_options['on_registration']==$tmpl->id ) {
															  echo 'selected';
														}
													} elseif ( $tmpl->tmpl_name === 'On Joining Group' ) {
														echo 'selected';}
													?>
                            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send this message to the users on successfully joining this Group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      <div class="uimrow pg-close-group-related-field" style="
      <?php
		if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='open' ) ) {
			echo 'display:none';}
		?>
            ">
          <div class="uimfield">
            <?php esc_html_e( 'On Request Denied', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput 
          <?php
			if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 && isset( $group_options['group_type'] ) && $group_options['group_type']=='closed' ) {
				echo '';}
			?>
                " >
            <select name="group_options[on_request_denied]" id="on_request_denied">
              <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
              <?php
				foreach ( $email_template as $tmpl ) {
					?>
              <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                        <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_request_denied'] ) && $group_options['on_request_denied']==$tmpl->id ) {
															  echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'On Request Denial' ) {
											echo 'selected';}
										?>
                ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
					<?php
				}
				?>
            </select>
            <div class="errortext"></div>
          </div>
          <div class="uimnote"><?php esc_html_e( 'Send this message to the users when their membership request is denied by the Group Manager. Relevant for Closed Group Types.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
        </div>    
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On User Activate', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_user_activate]" id="on_user_activate">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_user_activate'] ) && $group_options['on_user_activate']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'User Account Activated' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send this message to the users when their user account is activated. Also works when user account is reactivated after a deactivation.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On User Deactivate', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_user_deactivate]" id="on_user_deactivate">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_user_deactivate'] ) && $group_options['on_user_deactivate']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'User Account Suspended' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( "Send this message to the users when their user account is deactivated from 'User Profiles' section.", 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
       
         
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On Password Change', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_password_change]" id="on_password_change">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_password_change'] ) && $group_options['on_password_change']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'Password Successfully Changed' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send this message to the users on password change.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>

        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On Account Deletion', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_account_deleted]" id="on_account_deleted">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
                          <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                                    <?php
													if ( !empty( $group_options ) ) {
														if ( isset( $group_options['on_account_deleted'] ) && $group_options['on_account_deleted']==$tmpl->id ) {
															  echo 'selected';
														}
													} elseif ( $tmpl->tmpl_name === 'User Account Deleted' ) {
														echo 'selected';}
													?>
                            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send this message to the users on account deletion.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
          
           <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On Membership Terminate', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_membership_terminate]" id="on_membership_terminate">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_membership_terminate'] ) && $group_options['on_membership_terminate']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'Membership Terminated' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Send this message to the users when their Membership is terminated by group manager or site admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
          

        <div class="uimrow">
     <div class="uimfield">
       <?php esc_html_e( ' On Publishing New Post', 'profilegrid-user-profiles-groups-and-communities' ); ?>
     </div>
     <div class="uiminput 
     <?php
		if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
       <select name="group_options[on_published_new_post]" id="on_published_new_post">
         <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
         <?php
			foreach ( $email_template as $tmpl ) {
				?>
         <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                   <?php
									if ( !empty( $group_options ) ) {
										if ( isset( $group_options['on_published_new_post'] ) && $group_options['on_published_new_post']==$tmpl->id ) {
													 echo 'selected';
										}
									} elseif ( $tmpl->tmpl_name === 'New User Blog Post' ) {
										echo 'selected';}
									?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
			}
			?>
       </select>
       <div class="errortext"></div>
     </div>
     <div class="uimnote"><?php esc_html_e( 'Send this message to the users when their blog post is approved and published.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
    </div>
          
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Manager Resets Password', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_admin_reset_password]" id="on_admin_reset_password">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
                          <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                                    <?php
													if ( !empty( $group_options ) ) {
														if ( isset( $group_options['on_admin_reset_password'] ) && $group_options['on_admin_reset_password']==$tmpl->id ) {
															echo 'selected';
														}
													} elseif ( $tmpl->tmpl_name === 'Password Reset by Group Manager' ) {
														echo 'selected';}
													?>
                            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Sends an email to the user when Group Manager changes password.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
          
          
          
       </div>
       
        
         <div class="uimrow" id="admin_notification">
        <div class="uimfield">
          <?php esc_html_e( 'Group Manager Emails', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="group_options[enable_group_admin_notification]" id="enable_group_admin_notification" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'admin_notification_html')" 
          <?php
			if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 ) {
				echo 'checked';
			} if ( $id==0 ) {
				echo 'checked';}
			?>
            />
          <label for="enable_group_admin_notification"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Send relevant custom email notifications to the Group Manager on important group events. If you have not created any custom email templates, you can save the group now, and come back later to assign them.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      
      <div class="childfieldsrow" id="admin_notification_html" style=" 
      <?php
		if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 ) {
			echo 'display:block;';
		} elseif ( $id==0 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
        
        <div class="uimrow pg-close-group-related-field" style="
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='open' ) ) {
			echo 'display:none';}
		?>
            ">
        <div class="uimfield">
          <?php esc_html_e( 'Membership Request', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 && $group_options['group_type']=='closed' ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_membership_request]" id="on_membership_request">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
                          <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                                    <?php
													if ( !empty( $group_options ) ) {
														if ( isset( $group_options['on_membership_request'] ) && $group_options['on_membership_request']==$tmpl->id ) {
															  echo 'selected';
														}
													} elseif ( $tmpl->tmpl_name === 'Membership Request' ) {
														echo 'selected';}
													?>
                            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Group Manager receives this message when a user requests membership if Group Type is closed.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
          
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'On Group Manager Assignment', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $id==0 || ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 ) ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_admin_assignment]" id="on_admin_assignment">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_admin_assignment'] ) && $group_options['on_admin_assignment']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'Group Manager assignment' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Sends an email to the user who has been assigned as Manager of this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
          
        <div class="uimrow pg-no-group-manager-related-field" style="
        <?php
        if ( $id==0 || ( !empty( $row ) && $row->is_group_leader!=1 ) ) {
			echo 'display:none';}
		?>
            ">
        <div class="uimfield">
          <?php esc_html_e( 'On Group Manager Removal', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification']==1 ) {
			echo '';}
		?>
            ">
          <select name="group_options[on_admin_removal]" id="on_admin_removal">
            <option value=""><?php esc_html_e( 'Select Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <?php
			foreach ( $email_template as $tmpl ) {
				?>
            <option value="<?php echo esc_attr( $tmpl->id ); ?>" 
                                      <?php
										if ( !empty( $group_options ) ) {
											if ( isset( $group_options['on_admin_removal'] ) && $group_options['on_admin_removal']==$tmpl->id ) {
												echo 'selected';
											}
										} elseif ( $tmpl->tmpl_name === 'Group Manager Removal' ) {
											echo 'selected';}
										?>
            ><?php echo esc_html( $tmpl->tmpl_name ); ?></option>
				<?php
            }
			?>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Sends an email to the user who has been removed as Manager of this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>  
        
      </div>
        
      <?php
		do_action( 'profile_magic_premium_group_option', $id, $group_options );
        ?>
      <div class="buttonarea"> <a href="admin.php?page=pm_manage_groups">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
          <input type="hidden" name="group_id" id="group_id" value="<?php echo esc_attr( $id ); ?>" />
        <?php wp_nonce_field( 'save_pm_add_group' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_group" id="submit_group" onClick="return add_group_validation()"  />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
<style>
  .ui-autocomplete {
    max-height: 100px;
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
  }
  /* IE 6 doesn't support max-height
   * we use height instead, but this forces the menu to always be this tall
   */
  * html .ui-autocomplete {
    height: 100px;
  }
  </style>
