<?php 
$dbhandler = new PM_DBhandler;
$pmemails = new PM_Emails;
$pmrequests = new PM_request;
$pm_sanitizer = new PM_sanitizer;
$current_user = wp_get_current_user();
$error = filter_input(INPUT_GET, 'errors');

$post_obj = $pm_sanitizer->sanitize($_POST);
if($error =='invalid_password')
{
    $delete_error = esc_html__("You entered incorrect password. Please try again.",'profilegrid-user-profiles-groups-and-communities');
}
do_action('profile_magic_update_frontend_user_settings',$post_obj,$current_user->ID);
if(isset($post_obj['pg_privacy_submit']))
{
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_privacy_settings_form' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    update_user_meta($current_user->ID,'pm_profile_privacy', sanitize_text_field($post_obj['pm_profile_privacy']));	
    update_user_meta($current_user->ID,'pm_hide_my_profile',sanitize_text_field($post_obj['pm_hide_my_profile']));	
    $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
    wp_safe_redirect( esc_url_raw( $redirect_url ) );
    exit;
    
}

if(isset($post_obj['pm_delete_account']))
{
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_delete_account_form' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    if(wp_check_password(sanitize_text_field($post_obj['password']), $current_user->data->user_pass, $current_user->ID))
    {
    // remove user
        if ( is_multisite() ) 
        {
            if ( !function_exists('wpmu_delete_user') ) {
                    require_once( ABSPATH . 'wp-admin/includes/ms.php' );
            }
            wpmu_delete_user( $current_user->ID);
             $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
            $redirect_url = add_query_arg( 'errors', 'account_deleted', $redirect_url );
            wp_safe_redirect( esc_url_raw( $redirect_url ) );exit;
        } 
        else 
        {
            if ( !function_exists('wp_delete_user') ) {
                    require_once( ABSPATH . 'wp-admin/includes/user.php' );
            }
            wp_delete_user( $current_user->ID);
            do_action('profilegrid_user_delete_own_account',$current_user->ID);
            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
            $redirect_url = add_query_arg( 'errors', 'account_deleted', $redirect_url );
            wp_safe_redirect( esc_url_raw( $redirect_url ) );exit;
        }
    }
    else
    {
         
         $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
         $redirect_url = add_query_arg( 'errors','invalid_password', $redirect_url );
         wp_safe_redirect( esc_url_raw( $redirect_url . '#pg-delete-account' ) );
         exit;
    }
}
if(isset($post_obj['my_account_submit']))
{
    $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
    if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_my_account_settings_form' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
    $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
    
    $isupdate_first_name = update_user_meta( $current_user->ID,'first_name',sanitize_text_field($post_obj['first_name']));
    $isupdate_last_name = update_user_meta( $current_user->ID,'last_name',sanitize_text_field($post_obj['last_name']));
    if($dbhandler->get_global_option_value('pm_allow_user_to_change_email',0)==1)
    {
        if (isset( $post_obj['user_email'])) {
            // check if user is really updating the value
            if ($current_user->user_email != $post_obj['user_email']) {  
                
                // check if email is free to use
                if (email_exists( sanitize_email($post_obj['user_email']) )){
                    $redirect_url = add_query_arg( 'errors', 'email_exists', $redirect_url );
                    // Email exists, do not update value.
                    // Maybe output a warning.
                } else {
                    $isupdate_email = true;
                    $current_user->user_email = sanitize_email( $post_obj['user_email'] );
                    $args = array(
                        'ID'         => $current_user->ID,
                        'user_email' => $current_user->user_email
                    );            
                    wp_update_user( $args );
                    do_action('pg_update_setting_during_email_change',$current_user->ID,$current_user->user_email);
               }   
           }
        }
    }
    
if($isupdate_email==true || $isupdate_first_name==true || $isupdate_last_name == true )
{
    $isupdate = true;
}
else
{
    $isupdate = false;
}
    
    $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php'));
    if($isupdate==false)
    {
        $redirect_url = add_query_arg( 'errors', 'no_changes', $redirect_url );                
    }
    wp_safe_redirect( esc_url_raw($redirect_url.'#pg-settings') );
	exit;
    
}

?>
<div class="pm-group-view">
<div class="pm-section pm-dbfl" id="pg-settings-container">

    <svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
    <path d="M0-.5h24v24H0z" fill="none"/>
</svg>
 
    
 <div class="pm-section-left-panel pm-section-nav-vertical pm-difl">
     
    <ul class="dbfl">
        <?php if($dbhandler->get_global_option_value('pm_show_account_details_settings','1')==1):?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl active" href="#pg-edit-profile"><?php esc_html_e('Account Details','profilegrid-user-profiles-groups-and-communities');?></a></li>
        <?php endif;?>
        <?php if($dbhandler->get_global_option_value('pm_show_change_password','1')==1):?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-change-password"><?php esc_html_e('Change Password','profilegrid-user-profiles-groups-and-communities');?></a></li>
        <?php endif;?>
        <?php do_action( 'profile_magic_profile_settings_tab',$uid,$gid);?>
        <?php if($dbhandler->get_global_option_value('pm_show_privacy_settings','0')==1):?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-privacy"><?php esc_html_e('Privacy','profilegrid-user-profiles-groups-and-communities');?></a></li>
        <?php endif;?>
        <?php if($dbhandler->get_global_option_value('pm_show_delete_profile','0')==1):?>
        <li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-delete-account"><?php esc_html_e('Delete Account','profilegrid-user-profiles-groups-and-communities');?></a></li>
        <?php endif;?>
    </ul>
</div>
<div class="pm-section-right-panel"> 
    <?php if($dbhandler->get_global_option_value('pm_show_account_details_settings','1')==1):?>
    <div id="pg-edit-profile" class="pm-blog-desc-wrap pm-difl pm-section-content">
      <?php 
      $themepath = $this->profile_magic_get_pm_theme('my-account-tpl');
      include $themepath;
      ?>
    </div>
    <?php endif;?>   
 <?php if($dbhandler->get_global_option_value('pm_show_change_password','1')==1):?>    
<div id="pg-change-password" class="pg-group-reg-form pm-blog-desc-wrap pm-difl pm-section-content">
   
     <?php 
  $themepath = $this->profile_magic_get_pm_theme('change-password-tpl');
  include $themepath;
  ?>
   
</div>
<?php endif;?>    
<?php if($dbhandler->get_global_option_value('pm_show_privacy_settings','0')==1):?>    
<div id="pg-privacy" class="pm-blog-desc-wrap pm-difl pm-section-content">
  <?php 
  $themepath = $this->profile_magic_get_pm_theme('privacy-settings-tpl');
  include $themepath;
  ?>
  
</div>
<?php endif;?>    
<?php if($dbhandler->get_global_option_value('pm_show_delete_profile','0')==1):?>    
<div id="pg-delete-account" class="pm-blog-desc-wrap pm-difl pm-section-content">
   <?php 
  $themepath = $this->profile_magic_get_pm_theme('delete-account-tpl');
  include $themepath;
  ?>
   
</div>
<?php endif;?>    
    <?php do_action( 'profile_magic_profile_settings_tab_content',$uid,$gid);?>
    </div>    
</div>
</div>
