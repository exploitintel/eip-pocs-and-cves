<?php
$dbhandler    = new PM_DBhandler();
$pmrequests   = new PM_request();
$pmemails     = new PM_Emails();
$path         =  plugin_dir_url( __FILE__ );
$pagenum      = filter_input( INPUT_GET, 'pagenum',FILTER_SANITIZE_NUMBER_INT );
$gid          = filter_input( INPUT_GET, 'gid',FILTER_SANITIZE_NUMBER_INT );
$current_user = wp_get_current_user();
$pagenum      = isset( $pagenum ) ? absint( $pagenum ) : 1;
$limit        = 10; // number of rows in page
$offset       = ( $pagenum - 1 ) * $limit;
$bulk_action = filter_input(INPUT_GET, 'bulk_action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ( ! empty( $bulk_action ) && in_array( $bulk_action, array( 'approve', 'decline' ), true ) ) {
	$retrieved_nonce = filter_input( INPUT_GET, '_wpnonce' );
	if ( ! wp_verify_nonce( $retrieved_nonce, 'pg_request_manager' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
	}
	if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user->ID ) ) {
		wp_die( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
	}
}

if ( !empty($bulk_action) && $bulk_action == 'approve') {
	$selected = filter_input( INPUT_GET, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	if ( isset( $selected ) ) :
            $message = '';
		foreach ( $selected as $id ) 
                {
                    $id      = absint( $id );
                    if ( empty( $id ) ) {
                        continue;
                    }
                    $request = $dbhandler->get_row( 'REQUESTS', $id, 'id' );
                    if ( empty( $request ) ) {
                        continue;
                    }
                    if($pmrequests->pg_check_group_limit_available($request->gid))
                    {
                        $update  = $pmrequests->profile_magic_join_group_fun( $request->uid, $request->gid, 'open' );
                        do_action( 'pm_user_membership_request_approve', $request->gid, $request->uid );
                    }
                    else
                    {
                        $message  = $dbhandler->get_value('GROUPS','group_limit_message',$request->gid);
                    }
		}
	endif;
        if($message=='')
        {
            wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_requests_manager' ) );
            exit;
        }
        else
        {
            echo '<div class="pmagic"> <div class="pg-notice pg-alert pg-shortcode-alert">'. esc_html($message).'</div></div>'; 
        }
	
}

if ( !empty($bulk_action) && $bulk_action == 'decline') { 
	$selected = filter_input( INPUT_GET, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	if ( isset( $selected ) ) :
		foreach ( $selected as $id ) {
                $id      = absint( $id );
                if ( empty( $id ) ) {
                    continue;
                }
                $request = $dbhandler->get_row( 'REQUESTS', $id, 'id' );
                if ( empty( $request ) ) {
                    continue;
                }
			$dbhandler->remove_row( 'REQUESTS', 'id', $id );
                $pmemails->pm_send_group_based_notification( $request->gid, $request->uid, 'on_request_denied' );
                do_action( 'pm_user_membership_request_denied', $request->gid, $request->uid );
		}
	endif;
        wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_requests_manager' ) );
	exit;
}


if ( isset( $_GET['search'] ) ) {
	$search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
} else {
	$search = '';
}

if (filter_input(INPUT_GET, 'reset',FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
    $retrieved_nonce = filter_input(INPUT_GET, '_wpnonce');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_request_manager')) {
        die(esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities'));
    }

    wp_safe_redirect(esc_url_raw('admin.php?page=pm_requests_manager'));
    exit;
}

$groups       =  $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ) );
$user_request = array();
if ( isset( $gid ) && $gid!='' ) {

    $where = array(
		'gid'    =>$gid,
		'status' =>'1',
	);
} else {
     $where = array( 'status'=>'1' );
}
 $requested  = $dbhandler->get_all_result( 'REQUESTS', '*', $where, 'results' );
 $additional = '';
if ( !empty( $requested ) ) {
    foreach ( $requested as $request ) {
        $user = get_user_by( 'ID', $request->uid );
        if ( isset( $user ) && !empty( $user ) ) {
            $user_request[] = $request->id;
        }
    }
    if ( !empty( $user_request ) ) {
		$rid        = implode( ',', $user_request );
		$additional = 'and id in(' . $rid . ')';
    } else {
        $additional = 'and id in(0)';
    }
}
$results      = $dbhandler->get_all_result( 'REQUESTS', '*', $where, 'results', $offset, $limit, 'id', 'asc', $additional );
$total_users  = count( $user_request );
$num_of_pages = ceil( $total_users/$limit );
$pagination   = $dbhandler->pm_get_pagination_new_ui( $num_of_pages, $pagenum );
?>
<div class="wrap">
<div class="pmagic pmagic-wide pg-custom-table"> 
    
    <h1 class="wp-heading-inline"><?php esc_html_e('Membership Requests','profilegrid-user-profiles-groups-and-communities');?></h1>
    <hr class="wp-header-end">
  
    <!-----Operationsbar Starts----->
    <form name="request_manager" id="request_manager" action="" method="get">
        <input type="hidden" name="page" value="pm_requests_manager" />
        
        <input type="hidden" id="pagenum" name="pagenum" value="<?php echo esc_attr( $pagenum ); ?>" />
 
        <!--------Operationsbar Ends-----> 

        <!-------Contentarea Starts-----> 


    <!-- top Filters---->
    
                
         
                <div class="tablenav top rm-tablenav-top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e('Select bulk action','profilegrid-user-profiles-groups-and-communities');?></label>
                        <select name="bulk_action" id="pg_request_bulk_actions">
                            <option value=""><?php esc_attr_e('Bulk actions','profilegrid-user-profiles-groups-and-communities');?></option>
                            <option value="approve"><?php esc_attr_e('Approve','profilegrid-user-profiles-groups-and-communities');?></option>
                            <option value="decline"><?php esc_attr_e('Decline','profilegrid-user-profiles-groups-and-communities');?></option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e('Apply','profilegrid-user-profiles-groups-and-communities');?>">
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
                        
                        
                        <input type="submit" id="pg_update_role" class="button action" value="<?php esc_attr_e('Filter','profilegrid-user-profiles-groups-and-communities');?>">
                        <?php if (isset($_GET['search'])):?>
                        <input type="submit" name="reset" class="button action" value="<?php esc_attr_e('Reset','profilegrid-user-profiles-groups-and-communities');?>">
                        <?php endif;?>
                    </div>

                  
                    <h2 class="screen-reader-text"><?php esc_html_e('User list navigation', 'profilegrid-user-profiles-groups-and-communities'); ?></h2>
                    <div  class="tablenav-pages pg-mb-2 <?php  if($num_of_pages==1){echo 'one-page';} ?>">
                        <span class="displaying-num">
                        <?php
                        // translators: %d: request count.
                        printf( esc_html__( '%d request', 'profilegrid-user-profiles-groups-and-communities' ), intval( $total_users ) );
                        ?>
                        </span>
                        <?php
                            if($total_users){
                                echo wp_kses_post($pagination);
                            }
                        ?>
                    </div>
                    
                 
                </div>
    
    <!---Top FIlters-End--->  
    
   
    <table class="pg-request-list-table wp-list-table widefat striped table-view-list pg-request pg-table-striped">
         <caption class="screen-reader-text">Table ordered by Hierarchical Menu Order and Title. Ascending.</caption>
         <thead>
            <tr>
               <td scope="col" class="manage-column check-column">
                   <input type="checkbox" id="selectall" class="css-checkbox " name="selectall">
               </td>
               <th><?php esc_html_e( 'Username', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               <th><?php esc_html_e( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               <th><?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               <th><?php esc_html_e( 'Request Date', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               <th><?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               
            </tr>
         </thead>
         <tbody>
             <?php
             if (!empty($results)) {
                 $i = 1 + $offset;
                 foreach ($results as $entry) {

                     $user = get_user_by('ID', $entry->uid);
                     if (!$user || empty($user)) {
                         continue;
                     }
                     $avatar = get_avatar($user->user_email, 30, '', false, array('force_display' => true));
                     $request_options = maybe_unserialize($entry->options);
                     $groupname = $dbhandler->get_value('GROUPS', 'group_name', $entry->gid);
                     ?>
            <tr>
            <th class="check-column">
                <input type="checkbox" name="selected[]" value="<?php echo esc_attr( $entry->id ); ?>" />
            </th>
            <td class="username column-username has-row-actions column-primary">
                <?php echo wp_kses_post($avatar); ?>      
                <strong> <a href="admin.php?page=pm_profile_view&id=<?php echo esc_attr( $user->ID ); ?>" class="row-title"><?php echo esc_html( $user->user_login ); ?></a></strong>
                <div class="row-actions">
                            <span class="pg-user-view"><a href="admin.php?page=pm_profile_view&id=<?php echo esc_attr($user->ID); ?>" target="__blank"><?php esc_html_e('View', 'profilegrid-user-profiles-groups-and-communities'); ?></a> |</span>
                            <span class="pg-assign-group"><a href="javascript:void()" onclick="pm_request_user_action(this,'approve')"><?php esc_html_e('Approve', 'profilegrid-user-profiles-groups-and-communities'); ?></a> |</span>
                            <span class="pg-assign-group"><a href="javascript:void()" onclick="pm_request_user_action(this,'decline')"><?php esc_html_e('Decline', 'profilegrid-user-profiles-groups-and-communities'); ?></a></span>
                </div>
            </td>
            <td><?php echo esc_html( $user->display_name ); ?></td>
            <td><?php echo esc_html( $user->user_email ); ?></td>
            <td><?php echo esc_html( $pmrequests->pm_change_date_in_different_format( $request_options['request_date'], 'request' ) ); ?></td>
            <td><?php echo esc_html( $groupname ); ?></td>
            
            <?php
                    $i++;
                }
            } else {
                echo wp_kses_post('<td colspan="7">');
                if (isset($gid) && $gid != '') {
                    esc_html_e('No membership requests matches your search.', 'profilegrid-user-profiles-groups-and-communities');
                } else {
                echo '<strong>' . esc_html__('Tip:', 'profilegrid-user-profiles-groups-and-communities') . '</strong> ';
                esc_html_e(
                        'If you’ve assigned Group Managers and have the Frontend Group Manager extension active, they can handle membership requests from the Group Management page. As the site admin, you can also approve or reject requests here—useful if no managers are assigned or the extension isn’t installed.',
                        'profilegrid-user-profiles-groups-and-communities'
                );
            }

                echo wp_kses_post('</td>');
            }
            ?>
            
          </tr>
    </tbody>
    <tfoot>
        <tr>
            <td scope="col" class="manage-column check-column">
                <input type="checkbox" id="selectall" class="css-checkbox " name="selectall">
            </td>
            <th><?php esc_html_e( 'Username', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
            <th><?php esc_html_e( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
            <th><?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
            <th><?php esc_html_e( 'Request Date', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
            <th><?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
               
        </tr>
    </tfoot>
    </table>
    <?php wp_nonce_field('pg_request_manager'); ?>
  </form>
</div>
</div>
