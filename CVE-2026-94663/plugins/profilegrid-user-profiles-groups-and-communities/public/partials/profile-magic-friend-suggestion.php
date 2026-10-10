<?php
$pm_u_search = filter_input(INPUT_GET,'pm_u_search');
$suggestions = $pmfriends->profile_magic_friends_suggestion($uid);
if(empty($suggestions)){
    echo '<p class="pm-no-result-found">'. esc_html__('No suggestions available for friends','profilegrid-user-profiles-groups-and-communities').'</p>';
}else{
$suggestions_users =  $dbhandler->pm_get_all_users($pm_u_search,$meta_query_array,'',$offset,$limit,'ASC','include',array(),$date_query,$suggestions);
$suggestions_total_users = count($dbhandler->pm_get_all_users($pm_u_search,$meta_query_array,'','','','ASC','include',array(),$date_query,$suggestions));
$num_of_suggestions_pages = ceil( $suggestions_total_users/$limit);
?>
<div class="pm_search_form">
<form name="pm_search_users" id="pm_search_users">
<?php 
if ( class_exists( 'Profile_Magic_Basic_Functions' ) ) {
    $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
    if ( method_exists( $basic_function, 'pm_render_nonce_field' ) ) {
        $basic_function->pm_render_nonce_field( 'pm_friend_action', 'pm_friend_nonce' );
    }
}
?>
<input type="text" id="pm_u_search" name="pm_u_search" value="<?php echo esc_attr($pm_u_search); ?>" />
<input type="hidden" id="pm_tab" name="pm_tab" value="2" />
<input type="submit" name="pm_u_search_button" id="pm_u_search_button" value="<?php esc_html_e('Search','profilegrid-user-profiles-groups-and-communities');?>" />
</form>
</div>

<div class="pm-my-suggestions">
<?php $pmfriends->profile_magic_friends_result_html($suggestions_users,$uid);?>
</div>
<?php if($num_of_suggestions_pages>1):?>
<div class="pm-more-suggestion-result">
    <button id="pm_load_more_suggestion" value="2" onclick="pm_load_more_suggestion(<?php echo esc_attr($uid);?>,this.value,<?php echo esc_attr($num_of_suggestions_pages);?>)"><?php esc_html_e('Load More Result','profilegrid-user-profiles-groups-and-communities');?></button>
<div class="pm-loader-img-suggestion"><img src="<?php echo esc_url($path.'images/ajax-loader.gif');?>" width="" height=""/></div>
</div>
<?php endif;?>
<?php } ?>
