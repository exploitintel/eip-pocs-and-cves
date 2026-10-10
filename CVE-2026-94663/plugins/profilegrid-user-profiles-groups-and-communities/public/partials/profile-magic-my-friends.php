<?php 
$myfriends = $pmfriends->profile_magic_my_friends($uid);
if(isset($myfriends) && !empty($myfriends))
{
	$my_friends_users =  $dbhandler->pm_get_all_users($pm_f_search,$meta_query_array,'',$offset,$limit,'ASC','ID',array(),$date_query,$myfriends);
	$my_friends_total_users = count($dbhandler->pm_get_all_users($pm_f_search,$meta_query_array,'','','','ASC','ID',array(),$date_query,$myfriends));
	$num_of_friends_pages = ceil( $my_friends_total_users/$limit);
        $pagination = $dbhandler->pm_get_pagination($num_of_friends_pages,$pagenum);
}

?>
<?php if(isset($myfriends) && !empty($myfriends)): ?>
 <div class="pm-friend-action-bar pm-dbfl">
        <button class="pm-difr pm-delete" onclick="pm_multiple_friends_remove('<?php echo esc_attr($u1);?>')"><?php esc_html_e('Remove','profilegrid-user-profiles-groups-and-communities');?></button>
    </div>
<div id="pg-my-friends-container">
    <div class="pm-my-friends">
    <?php $pmfriends->profile_magic_friends_result_html($my_friends_users,$uid);?>
    </div>
    <?php else : ?>
    <div class="pm-my-friends">
    <?php esc_html_e('No any users found in your friends list. ','profilegrid-user-profiles-groups-and-communities');?>
    </div>
    <?php endif;?>
    <?php if(isset($myfriends) && !empty($myfriends) && $num_of_friends_pages>1):
    echo wp_kses_post($pagination);
    endif;?>
</div>