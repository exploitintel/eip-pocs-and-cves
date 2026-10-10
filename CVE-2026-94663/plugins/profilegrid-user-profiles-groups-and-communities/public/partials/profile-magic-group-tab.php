<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$path =  plugin_dir_url(__FILE__);
$identifier = 'GROUPS';
$gids = $pmrequests->profile_magic_get_user_field_value($uid,'pm_group');
$gid = $pmrequests->pg_filter_users_group_ids($gids);
if(!empty($gid) && is_array($gid))
{
  $gid_array = array_unique($gid);
} 
else
{
    $gid_array = array($gid);
}
if(!empty($gid)):
$additional = "id in(".implode(',',$gid).") ";
$pagenum = filter_input(INPUT_GET, 'pagenum');
$pagenum = isset($pagenum) ? absint($pagenum) : 1;
$limit = 10; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$total_groups = count($dbhandler->get_all_result($identifier,'*',1,'results',0,false,null,false,$additional));
$num_of_pages = ceil( $total_groups/$limit);
$pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum);
$groups = $dbhandler->get_all_result($identifier,'*',1,'results', $offset, $limit,null,false,$additional);
if(!empty($groups))
{
        $themepath = $this->profile_magic_get_pm_theme('user-group-tab-tpl');
	include $themepath;
}
else
{
    echo '<div class="pg-alert-warning pg-alert-info">';
    echo sprintf(wp_kses_post( '%s is currently not a member of any User Groups.','profilegrid-user-profiles-groups-and-communities' ),wp_kses_post($pmrequests->pm_get_display_name($uid))); 
    echo '</div>';    
}
else:
    echo '<div class="pg-alert-warning pg-alert-info">';
    echo sprintf(wp_kses_post( '%s is currently not a member of any User Groups.','profilegrid-user-profiles-groups-and-communities' ),wp_kses_post($pmrequests->pm_get_display_name($uid))); 
    echo '</div>';
endif;
    
?>