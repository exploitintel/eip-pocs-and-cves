<?php
$dbhandler = new PM_DBhandler();
$pmrequests = new PM_request();
$pmemails = new PM_Emails();
$textdomain = $this->profile_magic;
$path = plugin_dir_url(__FILE__);
wp_enqueue_script('profilegrid-moment');
wp_enqueue_script('profilegrid-daterangepicker');
wp_enqueue_style('profilegrid-daterangepicker');

$pagenum = filter_input(INPUT_GET, 'pagenum',FILTER_SANITIZE_NUMBER_INT);
$gid = filter_input(INPUT_GET, 'gid',FILTER_SANITIZE_NUMBER_INT);
$assign_gid = filter_input(INPUT_GET, 'pg_assign_group', FILTER_VALIDATE_INT);
$gid = $gid ? $gid : $assign_gid;
$pg_add_members = filter_input(INPUT_GET, 'pg_add_members', FILTER_VALIDATE_INT);
$field_identifier = 'FIELDS';
$group_identifier = 'GROUPS';
$current_user = wp_get_current_user();
$pagenum = isset($pagenum) ? absint($pagenum) : 1;
$limit = (filter_input(INPUT_GET, 'limit',FILTER_SANITIZE_NUMBER_INT))?filter_input(INPUT_GET, 'limit',FILTER_SANITIZE_NUMBER_INT):10; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$orderby = (filter_input(INPUT_GET, 'orderby',FILTER_SANITIZE_FULL_SPECIAL_CHARS))?filter_input(INPUT_GET, 'orderby',FILTER_SANITIZE_FULL_SPECIAL_CHARS):'ID';
$sort = (filter_input(INPUT_GET, 'sort',FILTER_SANITIZE_FULL_SPECIAL_CHARS))?filter_input(INPUT_GET, 'sort',FILTER_SANITIZE_FULL_SPECIAL_CHARS):'ASC';
$action = filter_input(INPUT_GET, 'action',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if(isset($action) && !empty($action))
{
    $retrieved_nonce = filter_input(INPUT_GET, '_wpnonce');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_user_manager')) {
        die(esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities'));
    }
     $selected = filter_input(INPUT_GET, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    if (isset($selected))
    {
        if($action=='activate')
        {
            foreach ($selected as $uid) {
                update_user_meta($uid, 'rm_user_status', '0');
                $ugids = get_user_meta($uid, 'pm_group', true);
                $ugid = $pmrequests->pg_filter_users_group_ids($ugids);
                $primary_group = $pmrequests->pg_get_primary_group_id($ugid);
                $pmemails->pm_send_group_based_notification($primary_group, $uid, 'on_user_activate');
                do_action('pm_user_activated', $uid);
            }
        }
        
        if($action=='deactivate')
        {
            foreach ($selected as $uid) {
                update_user_meta($uid, 'rm_user_status', '1');
                do_action('pg_user_suspended', $uid);
                $ugids = get_user_meta($uid, 'pm_group', true);
                $ugid = $pmrequests->pg_filter_users_group_ids($ugids);
                $primary_group = $pmrequests->pg_get_primary_group_id($ugid);
                $pmemails->pm_send_group_based_notification($primary_group, $uid, 'on_user_deactivate');
            }
        }
        
        if($action=='delete')
        {
            foreach ($selected as $uid) {
                wp_delete_user($uid);
            }
        }
    }
    else
    {    
        $uid = filter_input(INPUT_GET, 'user');
        if($action=='activate')
        {
            update_user_meta($uid, 'rm_user_status', '0');
            $ugids = get_user_meta($uid, 'pm_group', true);
            $ugid = $pmrequests->pg_filter_users_group_ids($ugids);
            $primary_group = $pmrequests->pg_get_primary_group_id($ugid);
            $pmemails->pm_send_group_based_notification($primary_group, $uid, 'on_user_activate');
            do_action('pm_user_activated', $uid);
        }
        
        if($action=='deactivate')
        {
            
            update_user_meta($uid, 'rm_user_status', '1');
            do_action('pg_user_suspended', $uid);
            $ugids = get_user_meta($uid, 'pm_group', true);
            $ugid = $pmrequests->pg_filter_users_group_ids($ugids);
            $primary_group = $pmrequests->pg_get_primary_group_id($ugid);
            $pmemails->pm_send_group_based_notification($primary_group, $uid, 'on_user_deactivate');

        }
        
        if($action=='delete')
        {
            wp_delete_user($uid);
        }
        
    }
    wp_safe_redirect(esc_url_raw('admin.php?page=pm_user_manager'));
    exit;
}

if (filter_input(INPUT_GET, 'move')) {
    $retrieved_nonce = filter_input(INPUT_GET, '_wpnonce');
    $move_user_id = filter_input(INPUT_GET, 'move_user_id');
    $pm_move_group = filter_input(INPUT_GET, 'pm_group');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_user_manager')) {
        die(esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities'));
    }
    $pmrequests->profile_magic_join_group_fun($move_user_id, $pm_move_group, 'open');
    if ( ! empty( $pg_add_members ) || ! empty( $assign_gid ) ) {
        $pm_move_group = absint( $pm_move_group );
        $redirect_args = array(
            'page' => 'pm_user_manager',
        );
        if ( $pm_move_group ) {
            $redirect_args['gid']             = $pm_move_group;
            $redirect_args['pg_assign_group'] = $pm_move_group;
        }
        if ( ! empty( $pg_add_members ) ) {
            $redirect_args['pg_add_members'] = 1;
        }
        $redirect_args['pagenum'] = 1;
        wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
        exit;
    }
}

if (filter_input(INPUT_GET, 'delete')) {
    $retrieved_nonce = filter_input(INPUT_GET, '_wpnonce');
    $delete_user_id = filter_input(INPUT_GET, 'delete_user_id');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_user_manager')) {
        die(esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities'));
    }
     wp_delete_user($delete_user_id);
}

do_action('profilegrid_dashboard_user_manager_action_area');

if (filter_input(INPUT_GET, 'reset')) {
    $retrieved_nonce = filter_input(INPUT_GET, '_wpnonce');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_user_manager')) {
        die(esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities'));
    }

    wp_safe_redirect(esc_url_raw('admin.php?page=pm_user_manager'));
    exit;
}

$query_args = $pmrequests->pm_get_user_meta_query_usermanager(filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$meta_query_array = array('relation' => 'OR', $query_args);

$date_query = $pmrequests->pm_get_user_date_query_new_ui(filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$pg_interval = isset($_GET['pg_interval']) ? sanitize_text_field($_GET['pg_interval']) : '';
$start_date = '';
$end_date = '';
if(!empty($pg_interval)){
   $interval = explode('-',$pg_interval);
   $start_date = $interval[0];
   $end_date = $interval[1];
}
if (isset($_GET['search'])) {
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
} else {
    $search = '';
}

$groups = $dbhandler->get_all_result('GROUPS', array('id', 'group_name'));
$user_query = $dbhandler->pm_get_all_users_ajax($search, $meta_query_array, '', $offset, $limit,$sort,$orderby, array(), $date_query);
$total_users = $user_query->get_total();

//$qry_active_arg = $query_args;
$qry_active_arg = array('key' => 'rm_user_status','value'=>0,'compare'=>'=');
$meta_query_array_activate = array('relation' => 'OR', $qry_active_arg);
$user_query_activate_users = $dbhandler->pm_get_all_users_ajax($search, $meta_query_array_activate, '', $offset, $limit, $sort,$orderby, array(), $date_query);
$total_activate_users = $user_query_activate_users->get_total();


//$qry_inactive_arg = $query_args;
$qry_inactive_arg = array('key' => 'rm_user_status','value'=>1,'compare'=>'=');
$meta_query_array_inactive = array('relation' => 'OR', $qry_inactive_arg);
$user_query_inactivate_users = $dbhandler->pm_get_all_users_ajax($search, $meta_query_array_inactive, '', $offset, $limit, $sort,$orderby, array(), $date_query);
$total_inactivate_users = $user_query_inactivate_users->get_total();


$users = $user_query->get_results();
$num_of_pages = ceil($total_users / $limit);
$pagination = $dbhandler->pm_get_pagination_new_ui($num_of_pages, $pagenum);

?>

<div class="wrap">
<div class="pmagic pmagic-wide pg-custom-table"> 
    
<h1 class="wp-heading-inline"><?php esc_html_e('Members','profilegrid-user-profiles-groups-and-communities');?></h1>
<a href="user-new.php" class="page-title-action"><?php esc_html_e('New User','profilegrid-user-profiles-groups-and-communities');?></a>
<hr class="wp-header-end">
<?php if ( ! empty( $pg_add_members ) && ! empty( $gid ) ) : ?>
    <?php
    $new_user_url = add_query_arg( 'pg_group_id', $gid, admin_url( 'user-new.php' ) );
    $notice_text = sprintf(
        /* translators: %s: New User admin link. */
        __( 'Tip: Select users and use "Assign to group" to add them here. To create a new user already assigned to this group, use <a href="%s">New User</a>.', 'profilegrid-user-profiles-groups-and-communities' ),
        esc_url( $new_user_url )
    );
    ?>
    <div class="notice notice-info is-dismissible">
        <p><?php echo wp_kses( $notice_text, array( 'a' => array( 'href' => array() ) ) ); ?></p>
    </div>
<?php endif; ?>


    <!-----Operationsbar Starts----->
    <form name="user_manager" id="user_manager" action="" method="get">
        <input type="hidden" name="page" value="pm_user_manager" />
        <input type="hidden" name="status" id="status" value="all" />
        <input type="hidden" name="orderby" id="orderby" value="ID" />
        <input type="hidden" name="sort" id="sort" value="ASC" />
        <input type="hidden" id="pagenum" name="pagenum" value="<?php echo esc_attr( $pagenum ); ?>" />
        <?php if ( ! empty( $assign_gid ) ) : ?>
            <input type="hidden" name="pg_assign_group" value="<?php echo esc_attr( $assign_gid ); ?>" />
        <?php endif; ?>
        <?php if ( ! empty( $pg_add_members ) ) : ?>
            <input type="hidden" name="pg_add_members" value="1" />
        <?php endif; ?>
 
        <!--------Operationsbar Ends-----> 

        <!-------Contentarea Starts-----> 

    <div class="pm-popup pm-move-to-group pm-popup-height-auto" >
            <div class="pm-popup-header">
                <div class="pm-popup-title"><?php esc_html_e('Assign to group', 'profilegrid-user-profiles-groups-and-communities'); ?>   </div>
                <img class="pm-popup-close" src="<?php echo esc_url($path . '/images/close-pm.png'); ?>">
            </div>
            <div class="pm-popup-field-name" style="padding:15px;" >
                <select name="pm_group" id="gid" >
                    <option value=""><?php esc_html_e('Select A Group', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <?php
                    foreach ($groups as $group) {
                        ?>
                        <option value="<?php echo esc_attr($group->id); ?>" 
                        <?php
                        if (!empty($gid)) {
                            selected($gid, $group->id);
                        }
                        ?>
                                ><?php echo esc_html($group->group_name); ?></option>
                                <?php
                            }
                            ?>
                </select>
                <input type="submit" name="move" class="button button-primary" value="<?php esc_attr_e('Assign', 'profilegrid-user-profiles-groups-and-communities'); ?>"style="margin-left:20px;"/>
                <input type="hidden" id="move_user_id" name="move_user_id" value="" />
                <div class="pg-uim-notice-wrap pg-assign-user-group-waring"><div class="pg-uim-notice"> <?php esc_html_e('You are adding this user(s) to new group. All data associated with profile fields of old group will be merged and the user will have to edit and fill profile fields associated with the new group.', 'profilegrid-user-profiles-groups-and-communities'); ?></div></div>

            </div>
        </div>

        <div class="pm-popup pm-delete-to-group pm-popup-height-auto" >
            <div class="pm-popup-header">
                <div class="pm-popup-title"><?php esc_html_e('Please Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?>   </div>
                <img class="pm-popup-close" src="<?php echo esc_url($path . '/images/close-pm.png'); ?>">
            </div>

            <div class="pm-popup-field-name" style="padding:15px;" >
                <p class=""> <?php esc_html_e('You are about to remove selected user(s) from their respective groups and delete their user accounts. This action is irreversible. Please confirm to proceed.', 'profilegrid-user-profiles-groups-and-communities'); ?></p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="delete_user_id" name="delete_user_id" value="" />
                
                <input type="button" id="cancel-delete" class="pm-popup-close button " value="<?php esc_attr_e('Cancel', 'profilegrid-user-profiles-groups-and-communities'); ?> " />
                <input type="submit" name="delete" class="button button-primary" value="<?php esc_attr_e('Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?>" />
            </div>
        </div>
    <!-- top Filters---->
            
    <ul class="subsubsub">

        <li class="all"><a href="javascript:void(0)" class="all current" aria-current="page" onclick="jQuery('#pagenum').val(1);jQuery('#status').val('all');jQuery('#user_manager').trigger( 'submit' )"><?php esc_html_e('All','profilegrid-user-profiles-groups-and-communities');?> <span class="count">(<?php echo esc_html(get_user_count()); ?>)</span></a> |</li>

        <li class="active"><a href="javascript:void(0)" class="" onclick="jQuery('#pagenum').val(1);jQuery('#status').val('0');jQuery('#user_manager').trigger( 'submit' )"><?php esc_html_e('Active','profilegrid-user-profiles-groups-and-communities');?> <span class="count">(<?php echo esc_html( $total_activate_users ); ?>)</span></a> |</li>
        <li class="pending"><a href="javascript:void(0)" class="" onclick="jQuery('#pagenum').val(1);jQuery('#status').val('1');jQuery('#user_manager').trigger( 'submit' )"><?php esc_html_e('Inactive','profilegrid-user-profiles-groups-and-communities');?> <span class="count">(<?php echo esc_html( $total_inactivate_users ); ?>)</span></a></li>
    </ul>
             
    <p class="search-box">
        <label class="screen-reader-text" for="user-search-input"><?php esc_attr_e('Search User', 'profilegrid-user-profiles-groups-and-communities'); ?>:</label>
        <input type="search" id="user-search-input" name="search" value="<?php echo esc_attr( $search ); ?>">
        <input type="submit" id="search-submit" class="button"  value="<?php esc_attr_e('Search Users', 'profilegrid-user-profiles-groups-and-communities'); ?>">
    </p>
         
    <div class="tablenav top rm-tablenav-top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top"  class="screen-reader-text"><?php esc_html_e('Select bulk action','profilegrid-user-profiles-groups-and-communities');?></label>
                        <select name="action" id="pg_user_bulk_actions">
                            <option value=""><?php esc_attr_e('Bulk actions','profilegrid-user-profiles-groups-and-communities');?></option>
                            <option value="activate"><?php esc_attr_e('Activate','profilegrid-user-profiles-groups-and-communities');?></option>
                            <option value="deactivate"><?php esc_attr_e('Deactivate','profilegrid-user-profiles-groups-and-communities');?></option>
                            <option value="delete"><?php esc_attr_e('Delete','profilegrid-user-profiles-groups-and-communities');?></option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e('Apply','profilegrid-user-profiles-groups-and-communities');?>">
                    </div>

                    <div class="alignleft actions">
                        
                        <input type="text" name="pg_interval" id="pg_users_date" placeholder="<?php esc_attr_e('All Dates','profilegrid-user-profiles-groups-and-communities');?>" value="<?php esc_attr($pg_interval);?>" readonly/>
                       
                    </div>

                    <div class="alignleft actions">
                     <select name="gid" id="gid" >
                            <option value=""><?php esc_html_e('Select A Group', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                            <?php
                            foreach ($groups as $group) {
                                ?>
                                <option value="<?php echo esc_attr($group->id); ?>" 
                                <?php
                                if (!empty($gid)) {
                                    selected($gid, $group->id);
                                }
                                ?>
                                        ><?php echo esc_html($group->group_name); ?></option>
                                        <?php
                                    }
                                    ?>
                            <option value="0" <?php selected($gid, 0); ?>><?php esc_html_e('None', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                        </select>
                        
                        <select name="limit" id="limit" >
                            <option value="10" <?php selected($limit,10);?>><?php esc_html_e('10','profilegrid-user-profiles-groups-and-communities');?></option>
                           <option value="20" <?php selected($limit,20);?>><?php esc_html_e('20','profilegrid-user-profiles-groups-and-communities');?></option>
                           <option value="30" <?php selected($limit,30);?>><?php esc_html_e('30','profilegrid-user-profiles-groups-and-communities');?></option>
                           <option value="50" <?php selected($limit,50);?>><?php esc_html_e('50','profilegrid-user-profiles-groups-and-communities');?></option>
                           <option value="100" <?php selected($limit,100);?>><?php esc_html_e('100','profilegrid-user-profiles-groups-and-communities');?></option>
                       </select>
                        
                        <input type="submit" id="pg_update_role" class="button action" value="<?php esc_attr_e('Filter','profilegrid-user-profiles-groups-and-communities');?>">
                        <?php if (isset($_GET['search'])):?>
                        <input type="submit" name="reset" class="button action" value="<?php esc_attr_e('Reset','profilegrid-user-profiles-groups-and-communities');?>">
                        <?php endif;?>
                    </div>

                  
                    <h2 class="screen-reader-text"><?php esc_html_e('User list navigation', 'profilegrid-user-profiles-groups-and-communities'); ?></h2>
                    <div  class="tablenav-pages <?php  if($num_of_pages==1){echo 'one-page';} ?>">
                        <span class="displaying-num">
                        <?php
                        // translators: %d: item count.
                        printf( esc_html__( '%d items', 'profilegrid-user-profiles-groups-and-communities' ), intval( $total_users ) );
                        ?>
                        </span>
                        <?php echo wp_kses_post($pagination); ?>
                    </div>
                    
                 
                </div>
    
    <!---Top FIlters-End--->  
  
  <!----Table---->
  
        <table class="pg-users-list-table wp-list-table widefat striped table-view-list users pg-table-striped">
          <caption class="screen-reader-text"><?php esc_html_e('Table ordered by Hierarchical Menu Order and Title. Ascending.','profilegrid-user-profiles-groups-and-communities');?></caption>
          <thead>
	  <tr>
		<td scope="col" class="manage-column check-column"><input type="checkbox" id="selectall" class="css-checkbox " name="selectall"/></td>
                <th scope="col" class="manage-column column-username column-primary <?php if($orderby=='user_login'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_login');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Username', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <!--
                <th scope="col" class="manage-column column-firstname <?php if($orderby=='first_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('first_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
               
                
                <th scope="col" class="manage-column column-lastname <?php if($orderby=='last_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('last_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                -->
                <th scope="col" class="manage-column column-displayname <?php if($orderby=='display_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('display_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <th scope="col" class="manage-column column-email <?php if($orderby=='user_email'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_email');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
		<th><?php esc_html_e( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
                <th scope="col" class="manage-column column-user_registered <?php if($orderby=='user_registered'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_registered');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Registered On', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <!--<th><?php esc_html_e( 'Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>-->
		<!--<th><?php esc_html_e( 'Action', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>-->
	  </tr>
          </thead>
	  <?php
		if ( ! empty( $users ) ) {
			foreach ( $users as $entry ) {
					$avatar     = get_avatar( $entry->user_email, 30, '', false, array( 'force_display' => true ) );
					$userstatus = get_user_meta( $entry->ID, 'rm_user_status', true );
				if ( $entry->ID == $current_user->ID ) {
                                    $class = 'pm_current_user';
                                    $attr  = 'disabled="disabled"';
				} else {
                                        $attr  = '';
                                        $class = 'pm_selectable';
				}
                                
				?>
          <tr class="<?php echo esc_attr( $class ); ?> <?php if( $userstatus == 1 ){echo esc_attr('pm-deactivate-user');} ?>">
			<th scope="row" class="check-column"><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $entry->ID ); ?>" <?php echo esc_attr( $attr ); ?> /></th>
                        <td class="username column-username has-row-actions column-primary pg-column-username" data-colname="Username">
                            <?php echo wp_kses_post( $avatar ); ?>
                            <strong>
                                <a href="admin.php?page=pm_profile_view&id=<?php echo esc_attr( $entry->ID ); ?>" class="row-title"><?php echo esc_html( $entry->user_login ); ?></a>
                            <?php if( $userstatus == 1 ){?>
                                <span class="rm-user-status post-state">— <?php esc_html_e('Inactive','profilegrid-user-profiles-groups-and-communities');?></span>
                         
                             <?php }?>
                                
                            </strong>
                            <div class="row-actions">
                                <span class="pg-user-view"><a href="admin.php?page=pm_profile_view&id=<?php echo esc_attr( $entry->ID ); ?>"><?php esc_html_e( 'View', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> |</span>
                                <?php if( empty($userstatus) ){ 
                                    
                                    $profile_url = $pmrequests->pm_get_user_profile_url( $entry->ID );?>
                                <span class=""><a href="<?php echo esc_url($profile_url);?>" target="__blank"><?php esc_html_e( 'Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> |</span>
                                <span class="pg-deactivate"><a href="<?php echo esc_url($pmrequests->pm_generate_user_manager_action_url('deactivate',$entry->ID,'pg_user_manager'));?>"><?php esc_html_e( 'Deactivate', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> |</span>
                                <?php }else{ ?>
                                <span class="pg-activate"><a href="<?php echo esc_url($pmrequests->pm_generate_user_manager_action_url('activate',$entry->ID,'pg_user_manager'));?>"><?php esc_html_e( 'Activate', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> |</span>
                                
                                <?php }?>
                                <?php if ( $entry->ID !== $current_user->ID ) {?>
                                <span class="pg-delete-user trash"><a onclick="pg_delete_user('<?php echo esc_attr($entry->ID);?>')" href="<?php //echo esc_url($pmrequests->pm_generate_user_manager_action_url('delete',$entry->ID,'pg_user_manager'));?>"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> |</span>
                                <?php }?>
                                <span class="pg-assign-group"><a href="" onclick="pg_assign_group('<?php echo esc_attr($entry->ID);?>')"><?php esc_html_e( 'Assign Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
                            </div>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></button>
                            
                        </td>
                        <!--
			<td class="first-name column-first-name" data-colname="first-name"><?php echo esc_html(($pmrequests->profile_magic_get_user_field_value( $entry->ID, 'first_name' )!='')?$pmrequests->profile_magic_get_user_field_value( $entry->ID, 'first_name' ):'—');?></td>
			<td class="last-name column-last-name" data-colname="last-name"><?php echo esc_html(($pmrequests->profile_magic_get_user_field_value( $entry->ID, 'last_name' )!='')?$pmrequests->profile_magic_get_user_field_value( $entry->ID, 'last_name' ):'—');?></td>
			-->
                        <td class="pg-display-name-col column-display-name-col" data-colname="<?php esc_html_e( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>"><?php echo esc_html( $entry->display_name ); ?></td>
			<td class="pg-email-col column-email-col" data-colname="<?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?>"><?php echo esc_html( $entry->user_email ); ?></td>
                        <td class="pg-group-col column-group-col" data-colname="<?php esc_html_e( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?>">
                            <?php $icons = $pmrequests->pg_get_user_groups_icon_with_more($entry->ID); echo (!empty($icons))?wp_kses_post($icons):'—'; ?>
                        </td>
                        <td class="pg-user-register-date column-register-date-col" data-colname="<?php esc_html_e( 'Registered On', 'profilegrid-user-profiles-groups-and-communities' ); ?>"><?php echo esc_html(gmdate('M d, Y @ h:i a', strtotime($entry->user_registered))); ?></td>
				<?php
				if ( $pmrequests->profile_magic_get_user_field_value( $entry->ID, 'rm_user_status' ) == '' || $pmrequests->profile_magic_get_user_field_value( $entry->ID, 'rm_user_status' ) == null ) {
					$userstatus = 0;
				} else {
					$userstatus = $pmrequests->profile_magic_get_user_field_value( $entry->ID, 'rm_user_status' );
				}
				?>
			<!--<td><?php echo esc_html( ( $userstatus == 1 ) ? __( 'Inactive', 'profilegrid-user-profiles-groups-and-communities' ) : __( 'Active', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></td>-->
			<!--<td><a href="admin.php?page=pm_profile_view&id=<?php echo esc_attr( $entry->ID ); ?>"><?php esc_html_e( 'View', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></td>-->
		  </tr>
				<?php
			}
		} else {
			echo '<tr><td></td><td>';
			 esc_html_e( 'No user matches your search.', 'profilegrid-user-profiles-groups-and-communities' );
			echo '<td><td></td><td></td><td></td><td></td></tr>';
		}
		?>
                  
                  <tfoot>	 
                      <tr>
		<td scope="col" class="manage-column check-column"><input type="checkbox" id="selectall" class="css-checkbox " name="selectall"/></td>
                <th scope="col" class="manage-column column-username column-primary <?php if($orderby=='user_login'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_login');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Username', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <!--
                <th scope="col" class="manage-column column-firstname <?php if($orderby=='first_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('first_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <th scope="col" class="manage-column column-lastname <?php if($orderby=='last_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('last_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
               -->
                <th scope="col" class="manage-column column-displayname <?php if($orderby=='display_name'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('display_name');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <th scope="col" class="manage-column column-email <?php if($orderby=='user_email'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_email');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
		<th><?php esc_html_e( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
                <th scope="col" class="manage-column column-user_registered <?php if($orderby=='user_registered'){ echo esc_attr("sorted ".strtolower($sort));}else{ echo esc_attr(" sortable"); }?>" onclick="jQuery('#pagenum').val(1);jQuery('#orderby').val('user_registered');jQuery('#sort').val('<?php echo esc_attr(($sort=='ASC')?'DESC':'ASC'); ?>');jQuery('#user_manager').trigger( 'submit' )">
                    <a>
                        <span><?php esc_html_e( 'Registered On', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>  
                        </span>
                    </a>
                </th>
                <!--<th><?php esc_html_e( 'Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>-->
		<!--<th><?php esc_html_e( 'Action', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>-->
	  </tr></tfoot>
	</table>
    <!----Table---->
   
  

<?php wp_nonce_field('pg_user_manager'); ?>
    </form>
</div>
</div>

<script>
    jQuery(function() {
            var start = moment(<?php if(!empty($start_date)){echo "'".esc_html($start_date)."'";}?>);
            var end = moment(<?php if(!empty($end_date)){echo "'".esc_html($end_date)."'";}?>);
            function cb(start, end) {
                jQuery('#pg_users_date').val(start.format('MM/DD/YYYY') + '-' + end.format('MM/DD/YYYY'));
            }

            jQuery('#pg_users_date').daterangepicker({
                startDate: start,
                endDate: end,
                maxDate: new Date(),
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: { cancelLabel: 'Clear' }  
            }, cb);
            jQuery('#pg_users_date').on('cancel.daterangepicker', function(ev, picker) {
                //do something, like clearing an input
                jQuery('#pg_users_date').val('');
                //jQuery('.pg-user-clear-date').show();
              });
            jQuery('#pg_users_date').on('apply.daterangepicker', function(ev, picker) {
                jQuery(this).val(picker.startDate.format('MM/DD/YYYY') + '-' + picker.endDate.format('MM/DD/YYYY'));
                //jQuery('.pg-user-clear-date').show();
            });  
            cb(start, end);
            <?php if(empty($pg_interval)){?>
                    jQuery('#pg_users_date').val('');
            <?php } ?>
        });
        
        function rm_clear_date_format(element){
            jQuery('#pg_users_date').val('');
            jQuery(element).hide();
        }
</script>
