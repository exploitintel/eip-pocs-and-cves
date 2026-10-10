<?php
$dbhandler = new PM_DBhandler;
$pm_activator = new Profile_Magic_Activator;
$pmrequests = new PM_request;
$pm_sanitizer = new PM_sanitizer();
$html_creator = new PM_HTML_Creator($this->profile_magic,$this->version);
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$gid = filter_input(INPUT_GET, 'gid');
$password_match = false;
if(empty($gid))
{
   $gid = get_query_var('gid');
}

//echo $gid;die;
if(isset($gid) && !empty($gid))
{
     $gid = $pmrequests->pm_get_gid_from_group_slug($gid);
}

$identifier = 'GROUPS';
if(!isset($gid) || empty($gid))
{
    if(isset($content['id']))
    {
        $gid = $content['id'];
    }
    else
    {
        $gid = isset($content['gid']) ? $content['gid'] : '';
    }
    
}
$current_user = wp_get_current_user();
$row = $dbhandler->get_row('GROUPS',$gid);
$pmgroupoption = isset($row->group_options) ? maybe_unserialize($row->group_options) : array();
$is_require_admin_approval = $dbhandler->get_global_option_value('pm_group_update_require_admin_approval',0);
$request_obj = $pm_sanitizer->sanitize($_REQUEST);
if(isset($request_obj["action"]) && $request_obj["action"]!='process')
{
    if(isset($request_obj["uid"]))$uid = $request_obj["uid"];else $uid = false;
    $pm_payapl_request = new PM_paypal_request();
    $post_obj = $pm_sanitizer->sanitize($_POST);
    $pm_payapl_request->profile_magic_join_group_payment_process($post_obj, $request_obj["action"],$gid,$uid);
    return false;
}
if(isset($_POST['group_password_form_submit']))
{
    $retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
    if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_check_group_password_form' ) ) {
        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    $upass = filter_input(INPUT_POST, 'pm_group_password');
    $cpassword = (isset($pmgroupoption['password']))?$pmgroupoption['password']:'';
    if($upass===$cpassword)
    {
        $password_match = true;
    }
    else
    {
        $password_match = false;
    }
}

if(isset($_POST['remove_image']))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_pm_edit_group' ) ) die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
	$groupid = filter_input(INPUT_POST,'group_id');
	
	if($groupid!=0 && $is_require_admin_approval==0)
	{
		$data = array('group_icon'=>'');
		$arg = array('%d');
	    $dbhandler->update_row($identifier,'id',$groupid,$data,$arg,'%d');
	}
        else
        {
            do_action('profilegrid_group_update_approval',$data,$row,$groupid);
        }
	$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$groupid);
	//$redirect_url = add_query_arg('gid',$groupid,$redirect_url);
	wp_safe_redirect( esc_url_raw( $redirect_url ) );
	exit;
	
}

if(isset($_POST['cancel']))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_pm_edit_group' ) ) die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
	$groupid = filter_input(INPUT_POST,'group_id');
	$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$groupid);
	//$redirect_url = add_query_arg('gid',$groupid,$redirect_url);
	wp_safe_redirect( esc_url_raw( $redirect_url ) );
	exit;
}

if(isset($_POST['edit_group']))
{
        $groupid = filter_input(INPUT_POST,'group_id');
	if($row->is_group_leader!=0)
        {
            $leaders = $pmrequests->pg_get_group_leaders($groupid);
        }
        else
        {
            $leaders = array();
        }
        
        if( ( filter_input(INPUT_GET, 'edit') || filter_input(INPUT_POST, 'edit_group')) && in_array($current_user->ID,$leaders) && is_user_logged_in())
	{
            $retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
            if (!wp_verify_nonce($retrieved_nonce, 'save_pm_edit_group' ) ) die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            
            $exclude = array("_wpnonce","_wp_http_referer","edit_group","group_id");
            $post = $pmrequests->sanitize_request($_POST,$identifier,$exclude);
            if(isset($_FILES['group_icon'])){
                $filefield = $_FILES['group_icon'];
                $allowed_ext ='jpg|jpeg|png|gif|webp|avif';
                if(isset($filefield) && !empty($filefield))
                {
                        $attachment_id = $pmrequests->make_upload_and_get_attached_id($filefield,$allowed_ext);
                        $post['group_icon'] = $attachment_id;
                }
            }
            if($post!=false)
            {
                    foreach($post as $key=>$value)
                    {
                      $data[$key] = $value;
                      $arg[] = $pm_activator->get_db_table_field_type($identifier,$key);
                    }
                    if(isset($data['associate_role']))
                    {
                        unset($data['associate_role']);
                    }
            }
            if($groupid!=0 && $is_require_admin_approval==0)
            {
                $dbhandler->update_row($identifier,'id',$groupid,$data,$arg,'%d');
                do_action('profilegrid_group_update',$data,$row,$groupid);
            }
            else
            {
                do_action('profilegrid_group_update_approval',$data,$row,$groupid);
            }
            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$groupid);
            //$redirect_url = add_query_arg('gid',$groupid,$redirect_url);
            wp_safe_redirect( esc_url_raw( $redirect_url ) );
            exit;	
        }
}

if(isset($_POST['pg_join_group']))
{
    /*$pg_uid = filter_input(INPUT_POST, 'pg_uid');*/
    $pg_uid = get_current_user_id();
    $pg_join_gid = filter_input(INPUT_POST, 'pg_join_gid');
    $group_type = $pmrequests->profile_magic_get_group_type($pg_join_gid);
    $is_paid_group = $pmrequests->profile_magic_check_paid_group($pg_join_gid);
    if($is_paid_group>0)
    {
        $message = apply_filters( 'profile_magic_check_payment_config','');
            if($message == 'disabled')
            {
                esc_html_e('Payment system is not configured to accept payments. Please configure at least one payment processor for this to work.','profilegrid-user-profiles-groups-and-communities');
            }else{
                $html_creator->pg_join_paid_group_html($pg_join_gid, $pg_uid);
            }
        
    }
    else
    {
        $result = $pmrequests->profile_magic_join_group_fun($pg_uid, $pg_join_gid,$group_type);
       
        if($result==true)
        {
            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$pg_join_gid);
            //$redirect_url = add_query_arg('gid',$pg_join_gid,$redirect_url);
            wp_safe_redirect( esc_url_raw( $redirect_url ) );
            exit;	
        }
        
    }
}

if(isset($_POST['pg_join_paid_group']))
{
    /*$pg_uid = filter_input(INPUT_POST, 'pg_uid');*/
    $pg_uid = get_current_user_id();
    $pg_join_gid = filter_input(INPUT_POST, 'pg_join_gid');
    do_action('profile_magic_join_group_registration_process',$_POST,$pg_join_gid,$pg_uid);
    do_action('profile_magic_join_paid_group_process',$_POST,$pg_join_gid,$pg_uid);
}

if(!isset($_POST['pg_join_group']) && !isset($_POST['pg_join_paid_group'])):
if(!empty($row))
{
	$pagenum = filter_input(INPUT_GET, 'pagenum');
	
	$pagenum = isset($pagenum) ? absint($pagenum) : 1;
        $pm_default_group_sorting = $dbhandler->get_global_option_value('pm_default_group_sorting','oldest_first');
        switch($pm_default_group_sorting)
        {
            case 'name_asc':
                $sortby = 'display_name';
                $order = 'ASC';
                break;
            case 'name_desc':
                $sortby = 'display_name';
                $order = 'DESC';
                break;
            case 'latest_first':
                $sortby = 'registered';
                $order = 'DESC';
                break;
             case 'oldest_first':
                $sortby = 'registered';
                $order = 'ASC';
                break;
            case 'suspended':
                $sortby = 'registered';
                $order = 'DESC';
                $get['status'] = '1';
                break;
            case 'first_name_asc':
                $sortby = 'first_name';
                $order = 'ASC';
                break;
            case 'first_name_desc':
                $sortby = 'first_name';
                $order = 'DESC';
                break;
            case 'last_name_asc':
                $sortby = 'last_name';
                $order = 'ASC';
                break;
            case 'last_name_desc':
                $sortby = 'last_name';
                $order = 'DESC';
                break;
            default:
                $sortby = 'display_name';
                $order = 'ASC';
                break;
            
        }
        
	$limit = (int) $dbhandler->get_global_option_value('pm_number_of_users_on_group_page','10'); // number of rows in page
	if ( $limit < 0 ) {
		$limit = 0;
	}

	$offset = 0;
	$users = array();
	$total_users = 0;
	$num_of_pages = 0;
	$pagination = '';

	if ( $limit > 0 ) {
		$offset = ( $pagenum - 1 ) * $limit;
		$hide_users = $pmrequests->pm_get_hide_users_array();
		$query_args = array(
						'relation' => 'AND',
						array(
							'key'     => 'pm_group',
							'value'   => sprintf(':"%s";',$gid),
							'compare' => 'like'
						),
						array(
							'key'     => 'rm_user_status',
							'value'   => '0',
							'compare' => '='
						)
						
					);
        
	if($row->is_group_leader!=0)
        {
            $leaders = $pmrequests->pg_get_group_leaders($gid);
        }
        else
        {
            $leaders = array();
        }
	if(isset($group_leader))$exclude = array($group_leader);else{ $exclude = array(); $group_leader = 0;}
        $meta_query = array( 'relation' => 'OR', $query_args );
	$user_query =  $dbhandler->pm_get_all_users_ajax('',$meta_query,'',$offset,$limit,$order,$sortby,$hide_users);
	$total_users = $user_query->get_total();
        $users = $user_query->get_results();
        $num_of_pages = ceil( $total_users/$limit);
	$pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum);
	}
        $is_global_password_protected = $dbhandler->get_global_option_value('pm_enable_group_password_option',0);
        $is_group_password_protected = (isset($pmgroupoption['enable_password_protection']))?$pmgroupoption['enable_password_protection']:0;
        
	if(filter_input(INPUT_GET, 'edit') && in_array($current_user->ID,$leaders) && is_user_logged_in())
	{
                 $themepath = $this->profile_magic_get_pm_theme('edit-group-tpl',$gid);
                 include $themepath;
	}
	else
	{
                
                if($is_group_password_protected==1 && $is_global_password_protected==1)
                {
                    
                    if($password_match==false)
                    {
                        $themepath = $this->profile_magic_get_pm_theme('group-check-password-tpl',$gid);
                        include $themepath;
                    }
                    else
                    {
                        $themepath = $this->profile_magic_get_pm_theme('group-tpl',$gid);
                        include $themepath;
                    }
                }
                else
                {
                    $themepath = $this->profile_magic_get_pm_theme('group-tpl',$gid);
                    include $themepath;
                }
                 	
	}
	
}
else
{
        echo '<div class="pmagic"><div class="pg-no-group-found-warning"><div class="pmrow pg-alert-info pg-alert-warning">'. esc_html__( 'Sorry, this group is currently not accessible. Either it was deleted or its ID does not match.','profilegrid-user-profiles-groups-and-communities' ).'</div></div></div>'; 
}
endif;
?>

