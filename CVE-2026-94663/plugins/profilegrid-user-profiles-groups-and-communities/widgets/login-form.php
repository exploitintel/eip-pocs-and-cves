<?php
$dbhandler = new PM_DBhandler;
$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
//$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$pmrequests = new PM_request;
$html_creator = new PM_HTML_Creator('profilegrid-user-profiles-groups-and-communities','1');
$register_link =  $dbhandler->get_global_option_value('pm_show_register_link','0');
$registration_url  = $pmrequests->profile_magic_get_frontend_url('pm_registration_page','');
$forget_password_url = $pmrequests->profile_magic_get_frontend_url('pm_forget_password_page',site_url('/wp-login.php?action=lostpassword'));
// Retrieve possible errors from request parameters
$pm_sanitizer = new PM_sanitizer;
$request = $pm_sanitizer->sanitize($_REQUEST);
$post = $pm_sanitizer->sanitize($_POST);
$attributes['errors'] = array();
if ( isset( $request['errors'] ) ) {
    $error_codes = explode( ',', $request['errors'] );
    foreach ( $error_codes as $error_code ) {
        $attributes['errors'][]=  $pmrequests->profile_magic_get_error_message($error_code,'profilegrid-user-profiles-groups-and-communities');
    }
}
// Check if the user just requested a new password 
$pm_error = '';
$attributes['password_updated'] = isset( $request['password'] ) && $request['password'] == 'changed';
$attributes['user_activated'] = isset( $request['activated'] ) && $request['activated'] == 'success';
$attributes['lost_password_sent'] = isset( $request['checkemail'] ) && $request['checkemail'] == 'confirm';
if ( $attributes['lost_password_sent'] ) $pm_error .= '<span class="pm_info">'.__( 'Check your email for a link to reset your password.','profilegrid-user-profiles-groups-and-communities' ).'</span>';
if ( $attributes['password_updated'] ) $pm_error .= '<span class="pm_info">'.__( 'Your password has been changed. You can sign in now.','profilegrid-user-profiles-groups-and-communities' ).'</span>';
if ( $attributes['user_activated'] ) $pm_error .= '<span class="pm_info">'.__( 'Your account has been successfully activated.','profilegrid-user-profiles-groups-and-communities' ).'</span>';

if ( count( $attributes['errors'] ) > 0 )
{
	foreach ( $attributes['errors'] as $error )
	{
		$pm_error .= '<span>'.$error.'</span>';
	}
}
//print_r($post);die();
// Check if the logout action has been triggered.
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Verify the nonce.
    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'pg-logout')) {
        die(esc_html__('Security check failed','profilegrid-user-profiles-groups-and-communities'));
    }
 
    // Nonce verification passed. Perform logout actions.
    wp_logout();
 
    // Redirect or display a message after logout.
    wp_safe_redirect(home_url()); // You can change the URL to your desired redirect location.
    exit;
}

if(isset($post['pg_widget_login_form_submit']))
{
    	$nonce = true;
        if(!isset($post['_wpnonce']) || !wp_verify_nonce($post['_wpnonce'],'pm_widget-login_form')){
            $nonce = false;
        }
	if($pmrequests->profile_magic_show_captcha('pm_enable_recaptcha_in_login'))
	{
		$response = isset( $post['g-recaptcha-response'] ) ?  $post['g-recaptcha-response'] : '';
		$remote_ip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP);
		$check_captcha = $pmrequests->profile_magic_captcha_verification($response,$remote_ip);
	}
	else
	{
		$check_captcha=true;
	}
    $check_captcha = apply_filters('pm_captcha_validation_in_login', $check_captcha);

	if($nonce){
            if($check_captcha==true)
            {

                    $username = $post['user_login'];
                    $password = $post['user_pass'];
                    $remeberme = isset($post['rememberme']) ? true : false;
                    
                    $secure_cookie = is_ssl();

                    if (filter_var($username, FILTER_VALIDATE_EMAIL)) 
                    { //Invalid Email
                            $user = get_user_by('email', $username);
                    } 
                    else 
                    {
                            $user = get_user_by('login', $username);
                    }

                    if ($user) 
                    {
                            if(wp_check_password( $password, $user->data->user_pass, $user->ID))
                            {
                                    $creds = array('user_login' => $user->data->user_login, 'user_password' => $password, 'remember'=> $remeberme);
                                    $user = wp_signon( $creds, $secure_cookie );
                                    //wp_safe_redirect('/members/'.$user->data->user_login.'/courses');
                                    $pm_redirect_after_login = $dbhandler->get_global_option_value('pm_redirect_after_login','0');
                                    if($pm_redirect_after_login==0)
                                    {
                                            $url = home_url('wp-admin');
                                    }
                                    else
                                    {
                                            $url = get_permalink($pm_redirect_after_login);	
                                    }

                                    wp_safe_redirect( esc_url_raw($url) );exit;
                            }
                            else
                            {
                                    $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
                                    $redirect_url = add_query_arg( 'errors','incorrect_password', $redirect_url );
                                    wp_safe_redirect( esc_url_raw( $redirect_url ) );exit;
                            }
                    }
                    else
                    {
                            $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
                            $redirect_url = add_query_arg( 'errors','invalid_username', $redirect_url );
                            wp_safe_redirect( esc_url_raw( $redirect_url ) );exit;
                    }
            }
            else
            {
                    $pm_error .= '<p class="pm_error">'.__( 'Captcha Failed','profilegrid-user-profiles-groups-and-communities' ).'</p>';	
            }
        }else{
            $pm_error .= '<p class="pm_error">'. esc_html__('Failed security check.','profilegrid-user-profiles-groups-and-communities' ).'</p>';
        }
}

?>
<div class="pmagic">  
 <div class="pm-widget-login-box pm-dbfl"> 
 <?php if(isset($pm_error) && $pm_error!='' && !is_user_logged_in()):?>
 <div class="pm-login-box-error pm-dbfl pm-pad10 pm-border-bt"><?php echo wp_kses_post($pm_error);?></div>
 <?php endif;?>
<?php 
if ( is_user_logged_in()) : 
    $current_user = wp_get_current_user();
    $uid = $current_user->ID;
    $user_info = get_userdata($current_user->ID);
    $gids = maybe_unserialize($pmrequests->profile_magic_get_user_field_value($uid,'pm_group'));
    $gid = $pmrequests->pg_filter_users_group_ids($gids);
    $group_page_link = $pmrequests->profile_magic_get_frontend_url('pm_group_page','');
    if(!empty($gid))
    {
        $group_page_link = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$gid[0]);
        $primary_gid = $pmrequests->pg_get_primary_group_id($gid);
        //$group_page_link = add_query_arg( 'gid',$gid[0],$group_page_link );
        $groupinfo = $dbhandler->get_row('GROUPS',$gid[0]);
        $group_leader = maybe_unserialize($groupinfo->group_leaders);
    }
    else
    {
        $gid='';
        $primary_gid = '';
    }
    ?>
 <div class="pg-user-content pm-dbfl pm-border">
     <div class="pg-user-content-img pm-difl">
         <?php if(isset($pg_show_dp) && $pg_show_dp=="1"):?>
        <div id="pm-show-profile-image" class="pm-profile-image pm-difl pm-pad10"> <?php echo get_avatar($user_info->user_email,50,'',false,array('class'=>'pm-user','force_display'=>true));?>
        
        </div>
         <?php endif;?>
     </div>
     <div class="pg-loggedin-user-info pm-difl">
          <div class="pm-profile-title pm-difl pm-pad10">
          <div class="pm-user-name pm-dbfl pm-clip"><?php echo wp_kses_post($pmrequests->pm_get_display_name($uid,true));?></div>
           <?php if(!empty($gid) && isset($pg_show_group_name) && $pg_show_group_name=='1'):?>
          <div class="pm-user-group-name pm-dbfl pm-clip">
              <a href='<?php echo esc_url($group_page_link ); ?>'>
                  <i class="fa fa-users" aria-hidden="true"></i>
                  <?php echo wp_kses_post($groupinfo->group_name);?>
              </a>
               <?php $total_assign_group = count(array_unique($gid));if(!empty($gid) && is_array($gid) && $total_assign_group >1):?>
              <?php if($total_assign_group>2){ $group_count_String = esc_html__('more groups','profilegrid-user-profiles-groups-and-communities');}else{$group_count_String = esc_html__('more group','profilegrid-user-profiles-groups-and-communities');} ?>
              <div class="pg-more-groups"><a onclick="pg_open_group_tab()">+<?php echo count(array_unique($gid))-1 .' '.esc_html($group_count_String); ?> </a></div>
               <?php endif;?>
               <a></a> 
               
          </div>
          <?php endif;?>
           <?php do_action('profile_magic_show_additional_header_info',$uid);?>
        </div>
         
     </div>
     <div class="pm-group-icon pm-widget-user-group pm-difr pm-pad10">
              
        <?php if(!empty($gid) && isset($pg_show_group_icon) && $pg_show_group_icon=='1'):?>
            <div id="pg-group-badge">
                <div id="pg-group-badge-dock">
                 <?php $pmrequests->pg_get_user_groups_badge_slider($uid);?>
                </div>
            </div> 
        <?php endif;?>    
              

          </div>
     <?php $login_page_url_widget ='';
     if (is_user_logged_in())
            {
            $login_page_url_widget = get_permalink();
            
       ?>
     <div class="pg_widget_logout_link pm-dbfl pm-border pm-pad10"><a href="<?php echo esc_url($login_page_url_widget); ?>/?action=logout&_wpnonce=<?php echo esc_attr(wp_create_nonce('pg-logout'));?>"><?php esc_html_e('Logout','profilegrid-user-profiles-groups-and-communities');?></a></div>
     
     <?php
            }
       ?>
     
 </div>
<?php
else:
?>
		
 
<!-----Form Starts----->
    <form class="pmagic-form pm-dbfl pm-bg-lt" method="post" action="" id="pm_widget-login_form" name="pm_widget-login_form">
    <?php $basic_function->pm_render_nonce_field( 'pm_widget-login_form' ); ?>
        <div class="pm_widget-login-row pm-difl">
            <label for="<?php echo esc_attr('user_login');?>" class="pm-dbfl"><?php esc_html_e('Email or Username','profilegrid-user-profiles-groups-and-communities');?></label>
            <input type="text" name="<?php echo esc_attr('user_login');?>" id="<?php echo esc_attr('user_login');?>" placeholder="<?php esc_attr_e('Email or Username','profilegrid-user-profiles-groups-and-communities');?>" required="required">
        </div>
        <div class="pm_widget-login-row pm-difl">
            <label for="<?php echo esc_attr('user_pass');?>" class="pm-dbfl"><?php esc_html_e('Password','profilegrid-user-profiles-groups-and-communities');?></label>
            <input type="password" name="<?php echo esc_attr('user_pass');?>" id="<?php echo esc_attr('user_pass');?>" placeholder="<?php esc_attr_e('Password','profilegrid-user-profiles-groups-and-communities');?>" required="required">
        </div>
        <div class="pg-login-remember">
            <label>
            <input name="<?php echo esc_attr('rememberme');?>" type="checkbox" id="pg-rememberme" value="1" />
            <?php esc_html_e('Remember Me','profilegrid-user-profiles-groups-and-communities');?>
            </label>
        </div>
              <?php do_action('profile_magic_show_captcha_in_login');?>
              <div class="pm-login-box-bottom-container pm-dbfl">
                  <input type="submit" value="<?php esc_attr_e('Login','profilegrid-user-profiles-groups-and-communities');?>" name="pg_widget_login_form_submit" class="">
                  <?php if($register_link):?>
                  <a href="<?php echo esc_url($registration_url);?>" class="pm-difl pg-registration-button"><?php esc_html_e('Register','profilegrid-user-profiles-groups-and-communities');?> </a> 
                  <?php endif; ?>
                  <div class="pm-login-links-box pm-dbfl">
                      <a href="<?php echo esc_url($forget_password_url);?>"><?php esc_html_e('Forgot Password?','profilegrid-user-profiles-groups-and-communities');?></a>
                  </div>
              </div>

    </form>
    <?php endif;?>
   </div>
</div>

