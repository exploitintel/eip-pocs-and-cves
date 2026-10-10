<?php
$pm_u_search = filter_input(INPUT_GET,'pm_u_search');
$requests = $pmfriends->profile_magic_my_friends_requests($uid,1);
if(!empty($requests))
{
	$user_query =  $dbhandler->pm_get_all_users_ajax($pm_u_search,$meta_query_array,'',$offset,$limit,'ASC','include',array(),$date_query,$requests);
	$total_users = $user_query->get_total();
        $users = $user_query->get_results();
        $num_of_requests_pages = ceil( $total_users/$limit);
        $pagination = $dbhandler->pm_get_pagination($num_of_requests_pages,$pagenum);
	?>	
        <div class="pm-friend-action-bar pm-dbfl">
            <button class="pm-difr pm-delete" onclick="pm_multiple_friends_request_cancel('<?php echo esc_attr($u1);?>')"><?php esc_html_e('Cancel','profilegrid-user-profiles-groups-and-communities');?></button>
        </div>
<div id="pg-friends-requests-sent-container">
	<div class="pm-my-requests">
	<?php $pmfriends->profile_magic_friends_result_html($users,$uid,3);?>
	</div>
	<?php if($num_of_requests_pages>1):
            echo wp_kses_post($pagination);
	endif;
        ?>
</div>
<?php
}
else
{
	echo '<div id="pg-friends-requests-sent-container"><p class="pm-no-result-found">'. esc_html__('No new sent requests','profilegrid-user-profiles-groups-and-communities').'</p></div>';
	
}
?>
