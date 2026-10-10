<?php
$dbhandler             = new PM_DBhandler();
$pm_activator          = new Profile_Magic_Activator();
$pmrequests            = new PM_request();
$basicfunctions        = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
$textdomain            = $this->profile_magic;
$path                  =  plugin_dir_url( __FILE__ );
$identifier            = 'GROUPS';
$group_options         = array();
$deactivate_extensions = $pmrequests->pg_check_premium_extension();
$email_template        =  $dbhandler->get_all_result( 'EMAIL_TMPL', array( 'id', 'tmpl_name' ) );
$id                    = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
$tab                   = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$pm_user_profile_page  = $dbhandler->get_global_option_value( 'pm_user_profile_page', '0' );
if ( !isset( $tab ) && empty( $tab ) ) {
    $tab = 'basics';
}

if ( $id==false || $id==null ) {
    $id            =0;
    $is_leader     = '';
    $leader_name   = '';
    $group_leaders = '';

} else {
    $row = $dbhandler->get_row( $identifier, $id );
	if ( $row->group_options!='' ) {
		$group_options = maybe_unserialize( $row->group_options );
		if ( ! is_array( $group_options ) ) {
			$group_options = array();
		}
    }
	if ( !empty( $row ) && $row->leader_rights!='' ) {
		$leader_rights = maybe_unserialize( $row->leader_rights );
	}
        $is_leader     = $row->is_group_leader;
        $leader_name   = $row->leader_username;
        $group_leaders = maybe_unserialize( $row->group_leaders );
	if ( isset( $group_options['group_type'] ) ) {
		$group_type = $group_options['group_type'];
	} else {
		$group_type ='Open';}
	$meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid'=>$row->id ) );
	$date_query       = $pmrequests->pm_get_user_date_query( array( 'gid'=>$row->id ) );
	$user_query       =  $dbhandler->pm_get_all_users_ajax( '', $meta_query_array, '', 0, 6, 'DESC', 'ID' );
        $total_users  = $user_query->get_total();
        $users        = $user_query->get_results();
        $leaders      = $pmrequests->pg_get_group_leaders( $row->id );
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
	$groupid       = filter_input( INPUT_POST, 'group_id' );
        $group_tab = filter_input( INPUT_POST, 'group_tab' );
        $post      = wp_unslash( $_POST );
	if ( ! isset( $post['group_options'] ) || ! is_array( $post['group_options'] ) ) {
		$post['group_options'] = array();
	}
	$add_members_raw = filter_input( INPUT_POST, 'pg_add_members', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	if ( $add_members_raw === null && isset( $_POST['pg_add_members'] ) ) {
		$add_members_raw = wp_unslash( $_POST['pg_add_members'] );
	}
	$add_members     = array_filter( array_map( 'absint', (array) $add_members_raw ) );
	$exclude       = array( '_wpnonce', '_wp_http_referer', 'submit_group', 'group_id', 'group_tab', 'pg_add_members' );
	if ( $groupid!=0 ) {
			if ( $group_tab=='management' ) {
				if ( !isset( $post['group_options']['show_admin_manager'] ) ) {
					$post['group_options']['show_admin_manager'] = 0;
				}
                                if ( !isset( $post['group_options']['flag_group_leader'] ) ) {
					$post['group_options']['flag_group_leader'] = 0;
				}
			}

			if ( $group_tab=='emails' ) {
				if ( !isset( $post['group_options']['enable_notification'] ) ) {
					$post['group_options']['enable_notification'] = 0;
				}
				if ( !isset( $post['group_options']['enable_group_admin_notification'] ) ) {
					$post['group_options']['enable_group_admin_notification'] = 0;
				}
			}

			if ( $group_tab=='display_name_pattern' ) {
				if ( !isset( $post['group_options']['display_name'] ) ) {
					$post['group_options']['display_name'] = 0;
				}
				if ( !isset( $post['group_options']['enable_prefix'] ) ) {
					$post['group_options']['enable_prefix'] = 0;
				}
				if ( !isset( $post['group_options']['enable_postfix'] ) ) {
					$post['group_options']['enable_postfix'] = 0;
				}
			}

			if ( $group_tab=='forums' ) {
				if ( !isset( $post['group_options']['bbpress_enable_tab'] ) ) {
					$post['group_options']['bbpress_enable_tab'] = 0;
				}
				if ( !isset( $post['group_options']['bbpress_create_topics'] ) ) {
					$post['group_options']['bbpress_create_topics'] = 0;
				}
				if ( !isset( $post['group_options']['bbpress_create_replies'] ) ) {
					$post['group_options']['bbpress_create_replies'] = 0;
				}
			}

			if ( $group_tab=='mailchimp' ) {
				if ( !isset( $post['group_options']['enable_mailchimp'] ) ) {
					$post['group_options']['enable_mailchimp'] = 0;
				}
				if ( !isset( $post['group_options']['enable_mailchimp_tab'] ) ) {
					$post['group_options']['enable_mailchimp_tab'] = 0;
				}
                if ( isset( $post['group_options']['mailchimp_tag'] ) ) {
                    do_action( 'pg_mailchimp_tag', $id, $post['group_options']['mailchimp_tag'], $group_options);
				}
			}
                        
                        if ( $group_tab=='mailpoet' ) {
				if ( !isset( $post['group_options']['enable_mailpoet'] ) ) {
					$post['group_options']['enable_mailpoet'] = 0;
				}
				if ( !isset( $post['group_options']['enable_mailpoet_tab'] ) ) {
					$post['group_options']['enable_mailpoet_tab'] = 0;
				}
			}

			if ( $group_tab=='woocommerce' ) {
				if ( !isset( $post['group_options']['woocommerce_vendor_dashboard_tab'] ) ) {
					$post['group_options']['woocommerce_vendor_dashboard_tab'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_cart_tab'] ) ) {
					$post['group_options']['woocommerce_cart_tab'] = 0;
				}

				if ( !isset( $post['group_options']['woocommerce_shop_tab'] ) ) {
					$post['group_options']['woocommerce_shop_tab'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_shop_setting_tab'] ) ) {
					$post['group_options']['woocommerce_shop_setting_tab'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_purchases_tab'] ) ) {
					$post['group_options']['woocommerce_purchases_tab'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_reviews_tab'] ) ) {
					$post['group_options']['woocommerce_reviews_tab'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_orders_in_account'] ) ) {
					$post['group_options']['woocommerce_orders_in_account'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_shipping_address_in_account'] ) ) {
					$post['group_options']['woocommerce_shipping_address_in_account'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_billing_address_in_account'] ) ) {
					$post['group_options']['woocommerce_billing_address_in_account'] = 0;
				}
				if ( !isset( $post['group_options']['woocommerce_show_total_spent'] ) ) {
					$post['group_options']['woocommerce_show_total_spent'] = 0;
				}
				if ( !isset( $post['group_options']['enable_woocommerce_discount'] ) ) {
					$post['group_options']['enable_woocommerce_discount'] = 0;
				}
                                if ( !isset( $post['group_options']['enable_woocommerce_custom_price'] ) ) {
					$post['group_options']['enable_woocommerce_custom_price'] = 0;
				}
			}

			if ( $group_tab=='group_fields' ) {
				if ( !isset( $post['group_options']['group_fields_option'] ) ) {
					$post['group_options']['group_fields_option'] = 0;
				}
                                else{
                                    unset($group_options['group_option_label']);
                                    unset($group_options['group_option_value']);
                                }
                                
			}

			if ( $group_tab=='membership' ) {
				if ( !isset( $post['group_options']['is_paid_group'] ) ) {
					$post['group_options']['is_paid_group'] = 0;
				}
			}

            if ( !isset( $post['group_options']['is_super_group'] ) ) {
                $post['group_options']['is_super_group'] = 0;
            }

            $new_post = array_replace_recursive( $group_options, $post['group_options'] );

            do_action('pg_group_option_additionl_saves',$groupid, $post);
            $post['group_options'] = apply_filters("pg_mailchimp_group_list_update", $new_post, $post['group_options']["mailchimp_list"]);

	}

        $post = $pmrequests->sanitize_request( $post, $identifier, $exclude );
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
		if ( !isset( $group_tab ) || empty( $group_tab ) ) {
			if ( !isset( $post['is_group_limit'] ) ) {
				$post['is_group_limit'] = 0;
			}
			if ( !isset( $post['is_group_leader'] ) ) {
				$post['is_group_leader'] = 0;
			}
			if ( !isset( $post['show_success_message'] ) ) {
				$post['show_success_message'] = 0;
			}
		} else {
			if ( $group_tab=='membership' ) {
				if ( !isset( $post['is_group_limit'] ) ) {
					$post['is_group_limit'] = 0;
				}
			}

			if ( $group_tab=='management' ) {
				if ( !isset( $post['is_group_leader'] ) ) {
					$post['is_group_leader'] = 0;
				}
			}

			if ( $group_tab=='group_registration_form' ) {
				if ( !isset( $post['show_success_message'] ) ) {
					$post['show_success_message'] = 0;
				}
			}
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
	if ( $group_tab=='management' ) {
                do_action( 'pg_groupleader_assign_remove', $gid, $is_leader, $group_leaders, $post['is_group_leader'], $post['group_leaders'] );
            }
	$group_options_saved = array();
	if ( isset( $data['group_options'] ) ) {
		$group_options_saved = maybe_unserialize( $data['group_options'] );
		if ( ! is_array( $group_options_saved ) ) {
			$group_options_saved = array();
		}
	}
	$needs_limit_check = ! empty( $add_members );
	$is_group_limit = 0;
	$available_slots = -1;
	$gid_for_membership = (string) $gid;
	if ( $needs_limit_check ) {
		$is_group_limit = (int) $dbhandler->get_value( 'GROUPS', 'is_group_limit', $gid );
		if ( $is_group_limit === 1 ) {
			$meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid' => $gid ) );
			$user_query = $dbhandler->pm_get_all_users_ajax( '', $meta_query_array );
			$total_users_in_group = (int) $user_query->get_total();
			$group_limit = (int) $dbhandler->get_value( 'GROUPS', 'group_limit', $gid );
			$available_slots = max( 0, $group_limit - $total_users_in_group );
		}
	}
	if ( ! empty( $add_members ) ) {
		foreach ( $add_members as $uid ) {
			$uid = absint( $uid );
			if ( ! $uid ) {
				continue;
			}
			if ( $is_group_limit === 1 && $available_slots <= 0 ) {
				break;
			}
			$user_groups = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_group' );
			$user_groups = $pmrequests->pg_filter_users_group_ids( $user_groups );
			if ( ! is_array( $user_groups ) ) {
				$user_groups = $user_groups ? array( $user_groups ) : array();
			}
			$user_groups = array_map( 'strval', $user_groups );
			if ( in_array( $gid_for_membership, $user_groups, true ) ) {
				continue;
			}
			$pmrequests->profile_magic_join_group_fun( $uid, $gid_for_membership, 'open' );
			if ( $is_group_limit === 1 ) {
				$available_slots--;
			}
		}
	}
        if($groupid == 0){
            $redirect_url = add_query_arg(
				array(
					'page' => 'pm_manage_groups',
				),
				admin_url( 'admin.php' )
			);
            wp_safe_redirect( esc_url_raw( $redirect_url ) );
        }else{
            wp_safe_redirect(esc_url_raw($_SERVER['REQUEST_URI']));  
        }
        exit;
}
if ( filter_input( INPUT_POST, 'delete_group' ) ) {



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
	$selected = filter_input( INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( $selected as $gid ) {
		$data                      =$dbhandler->get_row( $identifier, $gid, 'id', 'ARRAY_A' );
                $group_options_old = maybe_unserialize( $data['group_options'] );
                //$group_options_old['group_type']='open';
                $data['group_leaders']   = '';
                $data['is_group_leader'] = '0';
                $data['group_options']   = maybe_serialize( $group_options_old );
                $oldgid                  = $data['id'];
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


if ( $id==false || $id==null ) {
    $id            =0;
    $is_leader     = '';
    $leader_name   = '';
    $group_leaders = '';
    wp_die( esc_html__( 'Invalid group id.', 'profilegrid-user-profiles-groups-and-communities' ) );
}


?>
<div class="uimagic">
  <form name="pm_add_group" id="pm_add_group" method="post">
      
    <!-----Dialogue Box Starts----->
    <div class="pg-box-border pg-box-h-100 pg-box-white-bg pg-box-row">
        <div class="pg-box-col-3 pg-box-border-right">
            <div class="pg-group-setting-head">
                <div class="pg-group-cover-img">
                    <!-- Group Icon -->
                    <?php
                    if ( !empty( $row ) && $row->group_icon != 0 ) {
                        echo wp_get_attachment_link( $row->group_icon, array( 50, 50 ), false, true, false );
                    } else {
                        ?>
                        <img src="<?php echo esc_url( $path . 'images/pg-icon.png' ); ?>" />
                        <?php
                    }
                    ?>
                   <!-- Group Icon End -->  
                </div>
                
                <!-- Group Name -->
             <div class="pg-group-name"><?php echo esc_html( $row->group_name ); ?></div>
                 <!-- Group Name -->
                 <?php if ( strtolower( $group_type )=='open' ) : ?>
             <div class="pg-add-group-tabview pg-group-status-wrap pg-group-status-open">
            <span class="material-icons">public</span>
            <span><?php echo esc_html( $group_type ); ?></span> <span class="pg-status-sep">&#8901;</span> 
                             <?php
								if ( $row->is_group_limit==1 ) {
									echo esc_html( $total_users ) . '/' . esc_html( $row->group_limit );
								} else {
									echo esc_html( $total_users );}
								?>
                  </div>
            <?php else : ?>
            <div class="pg-add-group-tabview pg-group-status-wrap pg-group-status-closed">
            <span class="material-icons">locked</span>
            <span><?php echo esc_html( $group_type ); ?></span> <span class="pg-status-sep">&#8901;</span> 
                             <?php
								if ( $row->is_group_limit==1 ) {
									echo esc_html( $total_users ) . '/' . esc_html( $row->group_limit );
								} else {
									echo esc_html( $total_users );}
								?>
                  </div>
            <?php endif; ?>
             <?php
				if ( $group_type=='closed' ) :
					$where     = array(
						'gid'    =>$row->id,
						'status' =>'1',
					);
					$requested = $dbhandler->get_all_result( 'REQUESTS', '*', $where, 'results' );
					if ( isset( $requested ) && !empty( $requested ) ) {
						$count = count( $requested );
					} else {
						$count = 0;
					}
					?>
                 <div class="pg-group-requests"> <a href="admin.php?page=pm_requests_manager&pagenum=1&gid=<?php echo esc_attr( $row->id ); ?>" class="pg-d-flex pg-box-center"><span class="material-icons"> person_add </span> 
				 <?php
				 // translators: %d: pending request count.
				 echo sprintf( esc_html__( '%d pending requests', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $count ) );
				 ?>
				 </a></div>
				<?php endif; ?>
            </div>
            
            <!-- Core Setting Menu -->
            
            <div class="pg-options-nav pg-core-options">
                <div class="pg-setting-title"><?php esc_html_e( 'Core Options', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                <ul>
                    <li class="
                    <?php
                    if ( $tab=='basics' ) {
						echo 'pg-active-tab';}?>"><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=basics"><?php esc_html_e( 'Basics', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="
                    <?php
                    if ( $tab=='membership' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=membership"><?php esc_html_e( 'Membership', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="
                    <?php
                    if ( $tab=='management' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=management"><?php esc_html_e( 'Management', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="
                    <?php
                    if ( $tab=='emails' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=emails"><?php esc_html_e( 'Emails', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="
                    <?php
                    if ( $tab=='group_registration_form' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=group_registration_form"><?php esc_html_e( 'Group Registration Form', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                </ul>
            </div> 
            
             <!-- Core Setting Menu End-->
             
            <!-- Extension Setting Menu -->
            
            <div class="pg-options-nav pg-core-options">
                <div class="pg-setting-title">Extension Options</div>
                <ul>
                    <li class="
                    <?php
                    if ( $tab=='display_name_pattern' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=display_name_pattern"><?php esc_html_e( 'Display Name Pattern', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="
                    <?php
                    if ( $tab=='forums' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=forums"><?php esc_html_e( 'Forums', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                     <?php if (class_exists( 'Profilegrid_Mailchimp' )) : ?>
                    <li class="
                    <?php
                    if ( $tab=='mailchimp' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=mailchimp"><?php esc_html_e( 'Mailchimp', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <?php endif;?>
                    <?php if (class_exists( 'Profilegrid_Mailpoet' ) ) : ?> 
                    <li class="
                    <?php
                    if ( $tab=='mailpoet' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=mailpoet"><?php esc_html_e( 'MailPoet', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <?php endif; ?>
                    <li class="
                    <?php
                    if ( $tab=='woocommerce' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=woocommerce"><?php esc_html_e( 'WooCommerce', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <?php if (class_exists( 'Profilegrid_Group_Fields' )) : ?>  
                    <li class="
                    <?php
                    if ( $tab=='group_fields' ) {
						echo 'pg-active-tab';}
					?>
                    "><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $id ); ?>&tab=group_fields"><?php esc_html_e( 'Group Fields', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <?php endif; ?>


                </ul>
            </div> 
            
             <!-- Extension Setting Menu End-->
             
            <!-- Related Links Setting Menu -->
            
            <div class="pg-options-nav pg-core-options">
                <div class="pg-setting-title"><?php esc_html_e('PROFILE FIELDS MANAGER','profilegrid-user-profiles-groups-and-communities'); ?></div>
                <ul>
                    <li><a target="_blank" href="admin.php?page=pm_profile_fields&gid=<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Group Registration Form Fields', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                     <?php if ( $group_type=='closed' ) : ?>
                    <li><a target="_blank" href="admin.php?page=pm_requests_manager&pagenum=1&gid=<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Membership Requests', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <?php endif; ?>

                </ul>
            </div> 
            
             <!-- Related Links Setting Menu End-->
            
            
            
        </div>
        <div class="pg-box-col-9 pg-box-pbt">
            
            
        <?php if ( $id==0 ) : ?>
      <div class="uimheader">
			<?php esc_html_e( 'New Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php else : ?>
      <div class="uimheader pg-options-head pg-justify-content-between">
		  <?php
			$tab_title = strtoupper( str_replace( '_', ' ', $tab ) );
			echo esc_html( $tab_title );
			$check_locked = $pmrequests->check_group_settion_tabcontent_lock( $tab );
			if ( $check_locked===true ) {
				?>
<!--        <span class="pg-premium-options material-icons">locked</span>-->
			<?php } ?>
		  <?php if ( $tab=='woocommerce' ) : ?>
        <div class="pg-box-head-ext-nav pg-box-head-ext-nav-woocommerce">
            <ul>
                <li><a href="https://profilegrid.co/woocommerce-user-profiles-purchases-reviews-social-activity/" target="_blank" class="pg-box-border pg-box-white-bg"><?php esc_html_e( 'Documentation', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="material-icons"> article </span></a></li>
                <li><a href="<?php echo esc_url( get_permalink( $pm_user_profile_page ) ); ?>" target="_blank" class="pg-box-border pg-box-white-bg"><?php esc_html_e( 'Profile Preview', 'profilegrid-user-profiles-groups-and-communities' ); ?><span class="material-icons"> preview </span></a></li>
                
            </ul> 

        </div>
        <?php endif; ?> 
          <?php do_action('pg_group_name_additional_data', $id);?>
      </div>
      <?php endif; ?> 
            
                     
         <!---Pages------>
         
         <div id="add-group-tabs" style="height:80%;">
<!--             <div class="uimrow">
                 <div class="pg-uim-notice">
                     <?php
						//$basicfunctions->null_field_notice();
						//Show subheadings or message or notice
						?>
                 </div>
             </div>-->

             <?php if ( $tab == 'basics' ) : ?>                   
                 <div id="basics" class="pg-group-tabs-panel">
                     <div class="uimrow">
                         <div class="uimfield">
                             <?php esc_html_e( 'Group Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             <sup>*</sup></div>
                         <div class="uiminput pm_required">
                             <input type="text" name="group_name" id="group_name" value="<?php
								if ( !empty( $row ) ) {
									echo esc_attr( $row->group_name );}
								?>" />
                             <div class="errortext"></div>
                         </div>
                         <div class="uimnote">
                             <?php esc_html_e( 'Name of this Group. The name will appear on Single and All Groups page and Member Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
                             
                         </div>
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
												if ( $group_options['group_type'] == 'open' ) {
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
										if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'closed' ) {
											echo 'checked';
										}
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
                             <textarea name="group_desc" id="group_desc"><?php
								if ( !empty( $row ) ) {
									echo esc_attr( $row->group_desc );}
								?></textarea>
                         </div>
                         <div class="uimnote"> <?php esc_html_e( 'Description or details of the group. It will appear on the individual Group page and as intro text on All Groups page.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>

                     <div class="uimrow">
                         <div class="uimfield">
					<?php esc_html_e( 'Group Icon/ Badge', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="uiminput" id="icon_html">
                             <input id="group_icon" type="hidden" name="group_icon" class="icon_id" value="<?php
								if ( !empty( $row ) ) {
									echo esc_attr( $row->group_icon );}
								?>" />
                             <input id="group_icon_button" class="button group_icon_button" type="button" value="<?php esc_attr_e( 'Upload Icon', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
                             <?php
								if ( !empty( $row ) && $row->group_icon != 0 ) {
									echo wp_get_attachment_link( $row->group_icon, array( 50, 50 ), false, true, false );
								}
								?>
                             <img src="" width="50px" id="group_icon_img" style="display:none;" />
                             <?php
								if ( !empty( $row ) && $row->group_icon != 0 ) {
									?>
                                 <input type="button" name="remove_group_icon" id="remove_group_icon" class="remove_icon" value="<?php esc_attr_e( 'Remove Icon', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
									<?php
								}
								?>

                             <div class="errortext" id="icon_error"></div>
                         </div>
                         <div class="uimnote"><?php esc_html_e( 'Group badge, icon or image. This will appear with group description and on member user profiles belonging to this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
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
						'show_option_none'  => esc_html__( 'None', 'profilegrid-user-profiles-groups-and-communities' ),
						'option_none_value' => '0',
						'name'              => 'group_options[group_page]',
					);
					?>
                             <?php
								wp_dropdown_pages(
                                    array(
										'depth'            => 0,
										'child_of'         => 0,
										'selected'         => esc_attr( $group_page ),
										'echo'             => 1,
										'show_option_none' => esc_html__( 'None', 'profilegrid-user-profiles-groups-and-communities' ),
										'option_none_value' => '0',
										'name'             => 'group_options[group_page]',
                                    )
                                );
								?>
                             <div class="errortext"></div>
                         </div>
                         <div class="uimnote"><?php esc_html_e( "Select the page you want to display when users click on this Group's link. Select 'None' if you want to display the default ProfileGrid page for this Group.", 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>
                     
                     <div class="uimrow">
                         <div class="uimfield">
					<?php esc_html_e( 'Profile Template for Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="uiminput">
                             <select name="group_options[group_profile_template]" id="group_profile_template">
                                 <option value=""><?php esc_html_e('None','profilegrid-user-profiles-groups-and-communities'); ?></option>
					<?php
					if ( isset($group_options['group_profile_template']) && !empty( $group_options['group_profile_template'] ) ) {
						$group_template = $group_options['group_profile_template'];
					} else {
						$group_template = '';
					}
					 
			$themename = $pmrequests->profile_magic_get_pm_theme_name();
			$themepath = $pmrequests->profile_magic_get_pm_theme_path();
			$i         =0;
                        foreach ( $themename as $dirname ) {
                            $label_array  = explode( '_', $dirname );
                            $string_array = array( 'Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten' );
                            if ( isset( $label_array[1] ) && is_numeric( $label_array[1] ) ) {
                                $str1  = $label_array[0];
                                $str2  = $string_array[ $label_array[1] ];
                                $label = $str1 . $str2;
                            } else {
                                $label = $dirname;
                            }
                            ?>
                             <option value="<?php echo esc_attr( $dirname ); ?>" <?php selected( $group_template, $dirname ); ?>><?php echo esc_html( ucfirst( $label ) ); ?></option>
                             <?php
                            
                        }
                            ?>
                             </select>
                             
                             <div class="errortext"></div>
                         </div>
                         <div class="uimnote"><?php esc_html_e( "Select a profile template to apply a custom layout for all users in this group. You can preview available templates under 'Profile Templates' in Global Settings. If a user belongs to multiple groups, their profile template will be determined by their primary group.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                     </div>

                     <?php do_action( 'profile_magic_group_core_option', $id, $group_options ); ?>        

                 </div>
<input type="hidden" name="group_tab" value="basics" />
                         <?php endif; ?>
             
              <?php if ( $tab == 'display_name_pattern' ) : ?> 
             <div id="display_name_pattern" class="pg-group-tabs-panel">
					<?php if ( !class_exists( 'Profilegrid_Display_Name' ) ) : ?>
                    <div class="uimrow" id="notification">
                           <div class="uimfield">
                             <?php esc_html_e( 'Display Name Pattern', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                           </div>
                           <div class="uiminput">
                             <input name="" id="display_name" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="" disabled/>
                             <label for=""></label>
                           </div>
                             <div class="uimnote"><?php esc_html_e( 'Turn on customized display names for user profiles for this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                    </div>
                 
                 <div class="uimrow"> 
                             <div class="pg-ui-info-notice pg-premium-extension-notice">
                                 <div class="pg-premium-extension-notice-wrap">
                                     <span class="pg-premium-extension-icon"> <img src="<?php echo esc_url( $path . 'images/display_name.png' ); ?>" class="pg-plugin-icon" alt=""></span>
                                     <div class="pg-premium-extension-text"><?php echo wp_kses_post( 'These options are part of <b>free</b> Display Name Extension.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                         <div class="pg-premium-extensionlink-wrap"><a href="https://profilegrid.co/extensions/user-display-name/?utm_source=pg_plugin&utm_medium=group_options_tab_banner&utm_campaign=free_plugins" target="_blank" class="pg-premium-extension-link">Download <span class="material-icons"> navigate_next </span></a></div>
                                     </div>
                                 </div>

                             </div> 
                         </div>
              
                 
                 <?php endif; ?>
					<?php do_action( 'profile_magic_group_basic_option', $id, $group_options ); ?>
             </div>


<input type="hidden" name="group_tab" value="display_name_pattern" />
              <?php endif; ?>
                         <?php if ( $tab == 'membership' ) : ?>        
                 <div id="membership" class="pg-group-tabs-panel">

                     <div class="uimrow" id="grouplimit">
                         <div class="uimfield">
								<?php esc_html_e( 'Membership Limit', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="uiminput">
                             <input name="is_group_limit" id="is_group_limit" type="checkbox"  class="pm_toggle" value="1" 
								<?php
								if ( !empty( $row ) && $row->is_group_limit == 1 ) {
									echo 'checked';
								}
								?>
                                 style="display:none;"  onClick="pm_show_hide(this,'grouplimit_html')" />
                             <label for="is_group_limit"></label>
                         </div>
                         <div class="uimnote"><?php esc_html_e( 'Limit the number of membership slots for this group. Turn off for unlimited members.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>
                     <div class="childfieldsrow" id="grouplimit_html" style=" 
								<?php
								if ( !empty( $row ) && $row->is_group_limit == 1 ) {
									echo 'display:block;';
								} else {
									echo 'display:none;';
								}
								?>
                        ">
                         <div class="uimrow" id="group_limit_html">
                             <div class="uimfield">
								<?php esc_html_e( 'Max no. of Members', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput 
								<?php
								if ( !empty( $row ) && $row->is_group_limit == 1 ) {
									echo 'pm_required';
								}
								?>
                                ">
                                 <input type="number" name="group_limit" data-minimum="<?php echo esc_attr($total_users);?>" min="<?php if ( !empty( $row ) && $row->is_group_limit == 1 ) { echo esc_attr($total_users); }?>" id="group_limit" value="<?php
									if ( !empty( $row ) ) {
										echo esc_attr( $row->group_limit );}
									?>" />
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
									$group_limit_message = isset($row->group_limit_message) && !empty($row->group_limit_message) ? $row->group_limit_message : '';
								} else {
									$group_limit_message = '';
                                }
								wp_editor( $group_limit_message, 'group_limit_message' );
                                ?>
                                 <div class="errortext"></div>
                             </div>
                             <div class="uimnote"><?php esc_html_e( 'Users trying to register for this group will see this message once all membership slots are filled.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                         </div>
                     </div>

								<?php do_action( 'profile_magic_group_membership_option', $id, $group_options ); ?>
                 </div>   
<input type="hidden" name="group_tab" value="membership" />
             <?php endif; ?>
             <?php if ( $tab == 'management' ) : ?>                        
                 <div id="management" class="pg-group-tabs-panel">
                     <div class="uimrow" id="pm_group_admin_html">
                         <div class="uimfield">
                             <?php esc_html_e( 'Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="uiminput">
                             <input name="is_group_leader" id="is_group_leader" type="checkbox"  class="pm_toggle" value="1" 
                             <?php
								if ( !empty( $row ) && $row->is_group_leader == 1 ) {
									echo 'checked';
								}
								?>
                                 style="display:none;"  onClick="pm_show_hide(this,'groupleaderhtml')" />
                             <label for="is_group_leader"></label>
                             <div class="errortext"></div>
                         </div>
                         <?php if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'closed' ) : ?>

                             <div class="uimnote"><?php esc_html_e( 'You are trying to turn off Group Manager feature for a closed group. This can lead to issues in group membership approval system. It is recommended to assign a Group Manager to each closed group. Alternatively, you can make it an Open group.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                         <?php else : ?>
                             <div class="uimnote"><?php esc_html_e( 'A Group Manager has special privileges to moderate the Group. Group Managers also have their own privacy levels.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
    <?php endif; ?>
                     </div>
                     <div class="childfieldsrow" id="groupleaderhtml" style=" 
                     <?php
						if ( !empty( $row ) && $row->is_group_leader == 1 ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
						?>
                        ">
                            <div class="uimrow">
                                <div class="uimfield">
                                           <?php esc_html_e( 'Group Manager Label', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                </div>
                                <div class="uiminput">
                                    <input type="text" name="group_options[admin_label]" id="group_options[admin_label]" value="<?php
                                                                           if ( !empty( $group_options ) && isset( $group_options['admin_label'] ) ) {
                                                                                   echo esc_attr( $group_options['admin_label'] );}
                                                                           ?>">
                                    <div class="errortext"></div>
                                </div>
                                <div class="uimnote"><?php esc_html_e( "If you wish to rename the label 'Group Manager', you can define it here. For example, Captain, Leader, Instructor etc. Leave empty for the default label.", 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
                            </div>
                            <div class="uimrow">
                                <div class="uimfield">
                                           <?php esc_html_e( 'Group Manager Flag', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                </div>
                                <div class="uiminput">
                                    <input name="group_options[flag_group_leader]" id="flag_group_leader" type="checkbox" class="pm_toggle" value="1" style="display:none;"
                                           <?php if ( !empty( $group_options ) && isset($group_options['flag_group_leader']) && $group_options['flag_group_leader'] == 1 ) {
                                                    echo 'checked';
						}
                                            ?>
                                           >
                                    <label for="flag_group_leader"></label>
                                    <div class="errortext"></div>
                                </div>
                                <div class="uimnote"><?php esc_html_e( "If you wish to show flag on group manager on group page on member card, you can enable it.", 'profilegrid-user-profiles-groups-and-communities' ); ?>  </div>
                            </div>
					<?php if ( !class_exists( 'Profilegrid_Group_Multi_Admins' ) ) : ?>
                             <div class="uimrow">
                                 <div class="uimfield">
                                         <?php esc_html_e( 'Select Group Manager:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                 </div>
                                 <div class="uiminput 
                                 <?php
									if ( !empty( $row ) && $row->is_group_leader == 1 ) {
                                             echo 'pm_group_leader_name pm_select_required';
									}
									?>
                                    ">
                                     <select name="group_leaders[primary]" id="group_leaders">
                                         <option value=""><?php esc_html_e( 'Select a Group Member', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
						<?php foreach ( $all_users as $user ) : ?>
                                             <option value="<?php echo esc_attr( $user->ID ); ?>" 
                                                                       <?php
																		if ( !empty( $group_leaders ) && isset( $group_leaders['primary'] ) && $group_leaders['primary'] == $user->ID ) {
																			echo 'selected';
																		}
																		?>
                                                ><?php echo esc_html( $user->user_login ); ?></option>
                             <?php endforeach; ?>
                                     </select>
                                     <div class="errortext user_name_error"></div>
                                     
                                 </div>
                                 <div class="uimnote"><?php esc_html_e( 'Select from existing users.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                             </div>

						<?php
                 else :
                     do_action( 'profile_magic_multi_admin_option', $id );

                 endif;
					?>


                     </div>

                             <?php do_action( 'profile_magic_group_management_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="management" />
<?php endif; ?>     
<?php if ( $tab == 'group_fields' && class_exists( 'Profilegrid_Group_Fields' )) : ?>                       
                 <div id="group_fields" class="pg-group-tabs-panel">
                    
    <?php do_action( 'profile_magic_group_fields_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="group_fields" />
<?php endif; ?>
<?php if ( $tab == 'emails' ) : ?>                        
                 <div id="emails" class="pg-group-tabs-panel">
                     <div class="uimrow" id="notification">
                         <div class="uimfield">
                                 <?php esc_html_e( 'Group Member Emails', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="uiminput">
                             <input name="group_options[enable_notification]" id="enable_notification" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'notification_html')" 
                             <?php
								if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) {
                                     echo 'checked';
								} if ( $id == 0 ) {
									echo 'checked';
								}
								?>
                                />
                             <label for="enable_notification"></label>
                         </div>
                         <div class="uimnote"><?php esc_html_e( 'Send custom email notifications to relevant users on group events. If you have not created any custom email templates, you can save the group now, and come back later to assign them.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>

                     <div class="childfieldsrow" id="notification_html" style=" 
                     <?php
						if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) {
                                     echo 'display:block;';
						} elseif ( $id == 0 ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
						?>
                        ">

                         <div class="uimrow">
                             <div class="uimfield">
    <?php esc_html_e( 'On Joining Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput 
                             <?php
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_registration'] ) && $group_options['on_registration'] == $tmpl->id ) {
																			  echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'On Joining Group' ) {
																				 echo 'selected';
																	}
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
							if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'open' ) ) {
                                 echo 'display:none';
							}
							?>
                            ">
                             <div class="uimfield">
    <?php esc_html_e( 'On Request Denied', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput 
                             <?php
								if ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'closed' ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_request_denied'] ) && $group_options['on_request_denied'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'On Request Denial' ) {
																				 echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
                                     echo '';
								}
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
																		if ( isset( $group_options['on_user_activate'] ) && $group_options['on_user_activate'] == $tmpl->id ) {
																			  echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'User Account Activated' ) {
																				 echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
                                     echo '';
								}
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
																		if ( isset( $group_options['on_user_deactivate'] ) && $group_options['on_user_deactivate'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'User Account Suspended' ) {
																		echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_password_change'] ) && $group_options['on_password_change'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Password Successfully Changed' ) {
																		echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
                                         echo '';
								}
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
																		if ( isset( $group_options['on_account_deleted'] ) && $group_options['on_account_deleted'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'User Account Deleted' ) {
																		echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_membership_terminate'] ) && $group_options['on_membership_terminate'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Membership Terminated' ) {
																				 echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_published_new_post'] ) && $group_options['on_published_new_post'] == $tmpl->id ) {
																			  echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'New User Blog Post' ) {
																				 echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_notification'] ) && $group_options['enable_notification'] == 1 ) ) {
                                     echo '';
								}
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
																		if ( isset( $group_options['on_admin_reset_password'] ) && $group_options['on_admin_reset_password'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Password Reset by Group Manager' ) {
																				 echo 'selected';
																	}
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
								if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification'] == 1 ) {
									echo 'checked';
								} if ( $id == 0 ) {
									echo 'checked';
								}
								?>
                                />
                             <label for="enable_group_admin_notification"></label>
                         </div>
                         <div class="uimnote"><?php esc_html_e( 'Send relevant custom email notifications to the Group Manager on important group events. If you have not created any custom email templates, you can save the group now, and come back later to assign them.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>

                     <div class="childfieldsrow" id="admin_notification_html" style=" 
                     <?php
						if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification'] == 1 ) {
                                 echo 'display:block;';
						} elseif ( $id == 0 ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
						?>
                        ">


                         <div class="uimrow pg-close-group-related-field" style="
                         <?php
							if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'open' ) ) {
                                 echo 'display:none';
							}
							?>
                            ">
                             <div class="uimfield">
    <?php esc_html_e( 'Membership Request', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput 
                             <?php
								if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification'] == 1 && isset( $group_options['group_type'] ) && $group_options['group_type'] == 'closed' ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_membership_request'] ) && $group_options['on_membership_request'] == $tmpl->id ) {
																			  echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Membership Request' ) {
																				 echo 'selected';
																	}
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
								if ( $id == 0 || ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification'] == 1 ) ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_admin_assignment'] ) && $group_options['on_admin_assignment'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Group Manager assignment' ) {
																				 echo 'selected';
																	}
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
							if ( $id == 0 || ( !empty( $row ) && $row->is_group_leader != 1 ) ) {
								echo 'display:none';
							}
							?>
                            ">
                             <div class="uimfield">
    <?php esc_html_e( 'On Group Manager Removal', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput 
                             <?php
								if ( !empty( $group_options ) && isset( $group_options['enable_group_admin_notification'] ) && $group_options['enable_group_admin_notification'] == 1 ) {
									echo '';
								}
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
																		if ( isset( $group_options['on_admin_removal'] ) && $group_options['on_admin_removal'] == $tmpl->id ) {
																			echo 'selected';
																		}
																	} elseif ( $tmpl->tmpl_name === 'Group Manager Removal' ) {
																		echo 'selected';
																	}
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
                                 <?php do_action( 'profile_magic_group_emails_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="emails" />
<?php endif; ?>   
<?php if ( $tab == 'forums' ) : ?> 
                 <div id="forums" class="pg-group-tabs-panel">
                     
                     <?php if ( !class_exists( 'Profilegrid_Bbpress' ) ) : ?>

                        <div class="uimrow">
                               <div class="uimfield">
                                 <?php esc_html_e( 'Enable Forums Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                               </div>
                               <div class="uiminput">
                                 <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
                                 <label for=""></label>
                               </div>
                                 <div class="uimnote"><?php esc_html_e( 'Display Forums tab in user profile area for members of this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
                                 </div>
                        </div>

                        <div class="uimrow">
                               <div class="uimfield">
                                 <?php esc_html_e( 'Member Can Create New Topics', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                               </div>
                               <div class="uiminput">
                                 <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
                                 <label for=""></label>
                               </div>
                                 <div class="uimnote"><?php esc_html_e( 'Allow members of this group to start new forum topics.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
                                 </div>
                        </div>

                        <div class="uimrow">
                               <div class="uimfield">
                                 <?php esc_html_e( 'Member Can Create New Replies', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                               </div>
                               <div class="uiminput">
                                 <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
                                 <label for=""></label>
                               </div>
                                 <div class="uimnote"><?php esc_html_e( 'Allow members of this group to reply to topics in forums.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
                                 </div>
                        </div>
                     
                     
                     <div class="uimrow"> 
                                 <div class="pg-ui-info-notice pg-premium-extension-notice">
                                     <div class="pg-premium-extension-notice-wrap">
                                         <span class="pg-premium-extension-icon"> <img src="<?php echo esc_url( $path . 'images/bbpress.png' ); ?>" class="pg-plugin-icon" alt=""></span>
                                         <div class="pg-premium-extension-text"><?php echo wp_kses_post( 'These options are part of <b>free</b> bbPress Integration Extension.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                             <div class="pg-premium-extensionlink-wrap"><a href="https://profilegrid.co/extensions/bbpress-integration/?utm_source=pg_plugin&utm_medium=group_options_tab_banner&utm_campaign=free_plugins" target="_blank" class="pg-premium-extension-link">Download <span class="material-icons"> navigate_next </span></a></div>
                                         </div>
                                     </div>

                                 </div> 
                             </div>
                        <?php endif; ?>
                     
                     <?php do_action( 'profile_magic_group_forums_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="forums" />
             <?php endif; ?> 
             <?php if ( $tab == 'mailchimp' && class_exists( 'Profilegrid_Mailchimp' )) : ?>   
             <div id="mailchimp" class="pg-group-tabs-panel">
		<?php do_action( 'profile_magic_group_mailchimp_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="mailchimp" />
<?php endif; ?>  
             <?php if ( $tab == 'mailpoet' && class_exists( 'Profilegrid_Mailpoet' ) ) : ?>   
             <div id="mailpoet" class="pg-group-tabs-panel">
                    <?php do_action( 'profile_magic_group_mailpoet_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="mailpoet" />
<?php endif; ?>  

<?php if ( $tab == 'woocommerce' ) : ?> 
                 <div id="woocommerce" class="pg-group-tabs-panel">
    <?php do_action( 'profile_magic_group_woocommerce_option', $id, $group_options ); ?>
                 <?php if ( !class_exists( 'Profilegrid_Woocommerce' ) ) : ?>

<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Enable Cart Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled />
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Enable this option to display WooCommerce Cart tab in ProfileGrid User Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         </div>
</div>
                     
<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Display Purchases Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled />
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays Purchases tab in user profile page with thumbnails and names of the products purchased by the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         </div>
</div>


<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Show Product Reviews Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays Product Reviews tab in user profile page with reviews of the products that the user has posted.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Show Orders in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays order history and status inside the 'Settings' section of user. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Show Shipping Address in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays and allows editing of shipping address inside the 'Settings' section of user profile. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Show Billing Address in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays and allows editing of billing address inside the 'Settings' section of user. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
						<?php esc_html_e( 'Display Purchases Count and Total Spent', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays total count of products purchased and money spent by the user on their profile headers.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         <a href="https://profilegrid.co/extensions/woocommerce-integration/" target="_blank"><?php esc_html_e( 'Enable this feature', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
         
         </div>
</div>
                     
<div class="uimrow"> 
    <div class="pg-ui-info-notice pg-premium-extension-notice">
        <div class="pg-premium-extension-notice-wrap">
            <span class="pg-premium-extension-icon"> <img src="<?php echo esc_url( $path . 'images/pg-woocommerce.png' ); ?>" class="pg-plugin-icon" alt=""></span>
            <div class="pg-premium-extension-text"><?php echo wp_kses_post( 'These options are part of <b>free</b> WooCommerce Integration Extension.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                <div class="pg-premium-extensionlink-wrap"><a href="https://profilegrid.co/extensions/woocommerce-integration" target="_blank" class="pg-premium-extension-link">Download <span class="material-icons"> navigate_next </span></a></div>
            </div>
        </div>

    </div> 
</div>                  
<?php endif; ?>

	
                 </div>
<input type="hidden" name="group_tab" value="woocommerce" />
<?php endif; ?>                       
<?php if ( $tab == 'group_registration_form' ) : ?>                       
                 <div id="group_registration_form" class="pg-group-tabs-panel">

                     <div class="uimrow">
                         <div class="uimfield">
    <?php esc_html_e( 'Associated WP Role', 'profilegrid-user-profiles-groups-and-communities' ); ?><sup>*</sup>
                         </div>
                         <div class="uiminput pm_select_required">
                             <select name="associate_role" id="associate_role">
                                 <option value=""><?php esc_html_e( 'Select User Role', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
    <?php
    $roles = get_editable_roles();
    foreach ( $roles as $key => $role ) {
        ?>
                                     <option value="<?php echo esc_attr( $key ); ?>" 
                                                               <?php
																if ( !empty( $row ) && $row->associate_role == $key ) {
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
											if ( isset( $group_options['redirect'] ) && $group_options['redirect'] == 'none' ) {
												echo 'checked';
											}
										} else {
											echo 'checked';
										}
										?>
     onClick="pm_show_hide(this,'','redirect_page_html','redirect_url_html')">
    <?php esc_html_e( 'None', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                 </li>
                                 <li>
                                     <input type="radio" name="group_options[redirect]" id="redirect" value="page" 
                                     <?php
										if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect'] == 'page' ) {
											echo 'checked';
										}
										?>
                                         onClick="pm_show_hide(this,'redirect_page_html','redirect_url_html')">
    <?php esc_html_e( 'Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                 </li>
                                 <li>
                                     <input type="radio" name="group_options[redirect]" id="redirect" value="url" 
                                     <?php
										if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect'] == 'url' ) {
											echo 'checked';
										}
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
						if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect'] == 'page' ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
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
						if ( !empty( $group_options ) && isset( $group_options['redirect'] ) && $group_options['redirect'] == 'url' ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
						?>
                        ">

                         <div class="uimrow">
                             <div class="uimfield">
    <?php esc_html_e( 'URL', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput">
                                 <input type="url" name="group_options[redirect_url]" id="group_options[redirect_url]" value="<?php
									if ( !empty( $group_options ) && isset( $group_options['redirect_url'] ) ) {
										echo esc_url( $group_options['redirect_url'] );}
									?>">
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
								if ( !empty( $row ) && $row->show_success_message == 1 ) {
									echo 'checked';
								}
								?>
                                />
                             <label for="show_success_message"></label>
                         </div>
                         <div class="uimnote"><?php esc_html_e( 'Users will see a message after they submit Default Registration form for this Group. If redirection is turned on, it will appear for a few seconds before redirection is triggered.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                     </div>

                     <div class="childfieldsrow" id="success_message_html" style=" 
                     <?php
						if ( !empty( $row ) && $row->show_success_message == 1 ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';
						}
						?>
                        ">
                         <div class="uimrow" id="notification">
                             <div class="uimfield">
    <?php esc_html_e( 'Message Contents', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                             <div class="uiminput">
    <?php
    if ( isset( $row ) ) {
		$success_message = !empty($row->success_message) ? $row->success_message : '';
    } else {
		$success_message = '';
    }
    wp_editor( $success_message, 'success_message' );
    ?>
                             </div>
                             <div class="uimnote"><?php esc_html_e( 'The contents of the message. Rich text is supported.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                         </div>
                     </div>

    <?php do_action( 'profile_magic_group_registration_form_option', $id, $group_options ); ?>
                 </div>
<input type="hidden" name="group_tab" value="group_registration_form" />
<?php endif; ?> 
             
             
             
         </div>
        
         
        <!-- Page Ends -->
        
        
                    
    <!-- Page Footer -->  
              
      <div class="pg-box-button-area pg-d-flex pg-box-content-right"> <a href="admin.php?page=pm_manage_groups">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
          <input type="hidden" name="group_id" id="group_id" value="<?php echo esc_attr( $id ); ?>" />
        <?php wp_nonce_field( 'save_pm_add_group' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_group" id="submit_group" onClick="return add_group_validation()"  />
        <div class="all_error_text" style="display:none;"></div>
      </div>
        
    <!-- Page Footer End --> 
        
        
                            
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
