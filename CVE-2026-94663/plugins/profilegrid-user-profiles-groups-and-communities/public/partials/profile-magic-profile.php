<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$current_user = wp_get_current_user();
$pm_error = '';
do_action('pg_update_user_status');
$pm_sanitizer = new PM_sanitizer;
$post = $pm_sanitizer->sanitize($_POST);
if(isset($post['upload_image']))
{
	
	$filefield = $_FILES['user_image'];
	$allowed_ext ='jpg|jpeg|png|gif|webp|avif';
	if($post['user_id']==$current_user->ID)
	{
		$attachment_id = $pmrequests->make_upload_and_get_attached_id($filefield,$allowed_ext);
		update_user_meta($post['user_id'],$post['user_meta'],$attachment_id);	
	}
	$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',esc_url(site_url('/wp-login.php')));
	wp_safe_redirect( esc_url_raw($redirect_url ) );
	exit;
}

if(isset($post['remove_image']))
{
	
	if($post['user_id']==$current_user->ID)
	{
		update_user_meta($post['user_id'],$post['user_meta'],'');	
                if($post['user_meta']=='pm_user_avatar')
                {
                    do_action('pm_remove_profile_image',$post['user_id']);
                }
                
                if( $post['user_meta']=='pm_cover_image')
                {
                    do_action('pm_remove_cover_image',$post['user_id']);
                }
	}
	$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
	wp_safe_redirect( esc_url_raw($redirect_url) );
	exit;
}

if(isset($post['edit_profile']))
{
    
   //print_r($post);die;
        $allowed_user_id = apply_filters('pg_edit_user_profile_user_id',$current_user->ID,$post);
        
        if($post['euid']==$allowed_user_id)
        {
           
            $gids = $pmrequests->profile_magic_get_user_field_value($post['euid'],'pm_group');
            $gid = $pmrequests->pg_filter_users_group_ids($gids);
            if(is_array($gid)){$gid_array = $gid;} else{$gid_array = array($gid);}
            if(!empty($gid_array))
            {
                $exclude = "associate_group in(".implode(',',$gid_array).") and field_type not in('user_name','user_avatar','user_pass','confirm_pass','paragraph','heading','term_checkbox','read_only')";
                $fields =  $dbhandler->get_all_result('FIELDS', $column = '*',1,'results',0,false, $sort_by = 'ordering',false,$exclude);
            }
            else
            {
                $fields = array();
            }

            if(empty($errors))
            {
                if($dbhandler->get_global_option_value('pm_admin_approval_require_before_update','0')=='1')
                {
                    do_action('profile_magic_send_updated_data_for_approval',$post,$_FILES,$_SERVER,$gid,$fields,$post['euid'],$textdomain);
                }
                else
                {
                    $pmrequests->pm_update_user_custom_fields_data($post,$_FILES,$_SERVER,$gid,$fields,$post['euid']);
                    do_action('profile_magic_update_user_meta',$post,$_FILES,$_SERVER,$gid,$fields,$post['euid'],$textdomain);
                }
            }
        }
        if(isset($post['pg_rd']))
        {
            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$post['gid']);
            //$redirect_url = $redirect_url = add_query_arg('gid',$post['gid'],$redirect_url);
        }
        else
        {
            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
        }
	
	wp_safe_redirect( esc_url_raw($redirect_url) );
	exit;
}

if(isset($post['canel_edit_profile']))
{
    if(isset($post['pg_rd']))
    {
        $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$post['gid']);
        //$redirect_url = $redirect_url = add_query_arg('gid',$post['gid'],$redirect_url);
    }
    else
    {
        $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
    }
    wp_safe_redirect( esc_url_raw($redirect_url) );
    exit;
}

//if(isset($content['id']))$uid = $content['id'];else $uid = rtrim(filter_input(INPUT_GET, 'uid'),'/\\');
if(isset($content['id'])){
    $uid = $content['id'];}
else {
    //$uid = rtrim(filter_input(INPUT_GET, 'uid'),'/\\');
    $uid = get_query_var('uid');
    if(!$uid){
        if(!empty(filter_input(INPUT_GET, 'uid'))){
            $uid = rtrim(filter_input(INPUT_GET, 'uid'),'/\\');
        }
    }
}
if(isset($uid))
{
     $uid = $pmrequests->pm_get_uid_from_profile_slug($uid);
}
if((!isset($uid) || empty($uid)) && is_user_logged_in()){$uid = $current_user->ID;}

if ( is_user_logged_in() && isset( $uid ) && (int) $uid === (int) $current_user->ID ) {
    $page_id = get_queried_object_id();
    $page_id = $page_id ? absint( $page_id ) : 0;
    $selector = $page_id ? 'body.page-id-' . $page_id . '.sticky-menu' : 'body.sticky-menu';
    echo '<style id="pg-profile-sticky-fix">' . esc_html( $selector ) . '{position:static !important;overflow:auto !important;width:auto !important;top:auto !important;}</style>';
}

if($dbhandler->get_global_option_value('pm_enable_private_profile')=='1')
{
    if($uid != $current_user->ID)
    {
        echo wp_kses_post($dbhandler->get_global_option_value('pm_private_profile_message', esc_html__('You are not authorized to view contents of this page.','profilegrid-user-profiles-groups-and-communities')));
        return false;
    }
    
}

if(isset($uid) && !empty($uid))
{
    $user_info = get_userdata($uid);
    if(!empty($user_info)):
	$avatar = get_avatar($user_info->user_email, 274,'',false,array('class'=>'pm-user','force_display'=>true));
	$userrole = $pmrequests->get_userrole_name($uid);
	
	$gids = maybe_unserialize($pmrequests->profile_magic_get_user_field_value($uid,'pm_group'));
        $gid = $pmrequests->pg_filter_users_group_ids($gids);
	
        if(!empty($gid))
        {
            $gid_in = "gid in(".implode(',',$gid).")";
            $sections =  $dbhandler->get_all_result('SECTION',array('id','section_name'),1,'results',0,false,'gid,ordering',false,$gid_in);
        }
        if(is_user_logged_in())$filter_user_id = $pmrequests->pm_get_profile_slug_by_id($current_user->ID);
        if(filter_input(INPUT_GET, 'user_id') && (is_super_admin($current_user->ID ) || filter_input(INPUT_GET, 'user_id')==$filter_user_id || $pmrequests->pg_check_in_single_group_is_user_group_leader($current_user->ID,filter_input(INPUT_GET, 'gid'))))
        {
            $edit_uid = filter_input(INPUT_GET, 'user_id');
            $edit_uid = $pmrequests->pm_get_uid_from_profile_slug($edit_uid);
            $gids = maybe_unserialize($pmrequests->profile_magic_get_user_field_value($edit_uid,'pm_group'));
            $gid = $pmrequests->pg_filter_users_group_ids($gids);
            $gid = apply_filters('pg_edit_profile_user_gids', $gid);
            if(!empty($gid))
            {
                $gid_in = "gid in(".implode(',',$gid).")";
                $sections =  $dbhandler->get_all_result('SECTION',array('id','section_name'),1,'results',0,false,'gid,ordering',false,$gid_in);
            }
            if(!isset($sections))
            {
                $sections = array();
            }
            $themepath = $this->profile_magic_get_pm_theme('edit-profile-tpl',$uid);
            include $themepath;
        }
        else
        {
            if(!isset($sections))
            {
                $sections = array();
            }
            if($pmrequests->profile_magic_check_profile_access_permission($uid))
            {
            $themepath = $this->profile_magic_get_pm_theme('profile-tpl',$uid);
            include $themepath;
            echo '<input type="hidden" value="'. esc_attr($uid).'" name="pm-uid" id="pm-uid" />';
            }
            else 
            {
                 esc_html_e('You are not authorized to view this profile','profilegrid-user-profiles-groups-and-communities');
            }
        }
            
       
    else:
        echo '<div class="pm_message">'. esc_html__('User not found.','profilegrid-user-profiles-groups-and-communities').'</div>';
    endif;   
	
}
else
{
	$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
	$redirect_url = add_query_arg( 'errors','loginrequired', $redirect_url );
	wp_safe_redirect( esc_url_raw( $redirect_url ) );
	exit;	
}
if($dbhandler->get_global_option_value('pm_enable_live_notification','1')=='1')
{
    wp_enqueue_script( 'profile-magic-heartbeat.js', plugin_dir_url( __FILE__ ) . '../js/profile-magic-heartbeat.js', array( 'jquery' ), $this->version, true );
}              
?>
