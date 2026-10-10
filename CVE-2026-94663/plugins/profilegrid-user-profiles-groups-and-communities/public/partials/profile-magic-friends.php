<?php
$pmrequests = new PM_request;
$dbhandler = new PM_DBhandler;
$pmfriends = new PM_Friends_Functions;
$pmhtmlcreator = new PM_HTML_Creator($this->profile_magic,$this->version);
$identifier = 'FRIENDS';
$profilemagic_profile_url = $profile_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page','');
$profilemagic_my_friend_url = esc_url( add_query_arg( 'pm_tab',1,$profilemagic_profile_url ) );
$profilemagic_my_suggestion_url = esc_url( add_query_arg( 'pm_tab',2,$profilemagic_profile_url ) );
$profilemagic_my_request_url = esc_url( add_query_arg( 'pm_tab',3,$profilemagic_profile_url ) );
$current_user = wp_get_current_user();

if(!isset($uid) && is_user_logged_in()){$uid = $current_user->ID;}
$path =  plugin_dir_url(__FILE__);
$pagenum = filter_input(INPUT_GET, 'pagenum');
$activefriendtab = filter_input(INPUT_GET, 'pm_tab');
$activefriendtab = isset($activefriendtab) ? absint($activefriendtab) : 1;
$pm_f_search = filter_input(INPUT_GET,'pm_f_search');
$pagenum = isset($pagenum) ? absint($pagenum) : 1;
$limit = 20; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$meta_query_array = $pmrequests->pm_get_user_meta_query( filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS) );
$date_query = $pmrequests->pm_get_user_date_query( filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS) );
$total_myfriends = $pmfriends->pm_count_my_friends($uid);
$total_myfriends_requests = $pmfriends->pm_count_my_friend_requests($uid);
$total_send_requests = $pmfriends->pm_count_my_friend_requests($uid,1);
if($uid==$current_user->ID):
 $u1 = $pmrequests->pm_encrypt_decrypt_pass('encrypt',$uid);
?>
<div class="pm-group-view">
<div class="pm-section pm-dbfl" id="pg-friends-container">
    <svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
    <path d="M0-.5h24v24H0z" fill="none"/>
</svg>
 <div class="pm-section-left-panel pm-section-nav-vertical pm-difl">
     
    <ul class="dbfl">
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-myfriends"><?php esc_html_e('My Friends','profilegrid-user-profiles-groups-and-communities');?><span class="pm-difr notification-count"><?php echo wp_kses_post($total_myfriends);?></span></a></li>
        <?php
            $show_friend_requests = true;
            $show_friend_requests = apply_filters('pm_show_friend_requests', $show_friend_requests, $uid);
            if($show_friend_requests){
        ?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-friend-requests"><?php esc_html_e('Friend Requests','profilegrid-user-profiles-groups-and-communities');?><span class="pm-difr notification-count"><?php echo wp_kses_post($total_myfriends_requests);?></span></a></li>
        <?php } ?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-requests-sent"><?php esc_html_e('Requests Sent','profilegrid-user-profiles-groups-and-communities');?><span class="pm-difr notification-count"><?php echo wp_kses_post($total_send_requests);?></span></a></li>
    </ul>
</div>
    <div class="pm-section-right-panel">
<div id="pg-myfriends" class="pm-blog-desc-wrap pm-difl pm-section-content">
   
   <?php 
   $pmhtmlcreator->pm_get_my_friends_html($uid,$pagenum,$pm_f_search,$limit,1);
   ?>
</div>
<?php
    $show_friend_requests = true;
    $show_friend_requests = apply_filters('pm_show_friend_requests', $show_friend_requests, $uid);
    if($show_friend_requests){
?>
<div id="pg-friend-requests" class="pm-blog-desc-wrap pm-difl pm-section-content">
   
   <?php $pmhtmlcreator->pm_get_my_friends_html($uid,$pagenum,$pm_f_search,$limit,2);?>
</div>
<?php } ?>
<div id="pg-requests-sent" class="pm-blog-desc-wrap pm-difl pm-section-content">
    
   <?php $pmhtmlcreator->pm_get_my_friends_html($uid,$pagenum,$pm_f_search,$limit,3);?>
</div>
</div>
</div>
</div>
<?php else:?>
<div id="pg-myfriends" class="pm-blog-desc-wrap pm-difl pm-section-content pm-thirdparty-friends">
   
   <?php 
   $pmhtmlcreator->pm_get_my_friends_html($uid,$pagenum,$pm_f_search,$limit,1);
   ?>
</div>
<?php endif; ?>


