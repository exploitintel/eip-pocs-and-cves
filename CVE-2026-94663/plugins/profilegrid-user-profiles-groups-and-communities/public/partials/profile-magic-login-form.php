<?php
$dbhandler = new PM_DBhandler;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$pmrequests = new PM_request;
$html_creator = new PM_HTML_Creator($this->profile_magic,$this->version);
$forget_password_url = $pmrequests->profile_magic_get_frontend_url('pm_forget_password_page',site_url('/wp-login.php?action=lostpassword'));
$registration_url  = $pmrequests->profile_magic_get_frontend_url('pm_registration_page','');
$register_link =  $dbhandler->get_global_option_value('pm_show_register_link','0');
// Retrieve possible errors from request parameters
$pm_sanitizer = new PM_sanitizer;
$request = $pm_sanitizer->sanitize($_REQUEST);
$post = $pm_sanitizer->sanitize($_POST);
$attributes['errors'] = array();
if ( isset( $request['errors'] ) ) {
    $error_codes = explode( ',', $request['errors'] );
    foreach ( $error_codes as $error_code ) {
        $attributes['errors'][]=  $pmrequests->profile_magic_get_error_message($error_code,$textdomain);
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

if(isset($post['login_form_submit']))
{
        $nonce = true;
        if(!isset($post['_wpnonce']) || !wp_verify_nonce($post['_wpnonce'],'pm_login_form')){
            $nonce = false;
        }
	if($pmrequests->profile_magic_show_captcha('pm_enable_recaptcha_in_login'))
	{
		$response = isset( $post['g-recaptcha-response'] ) ? $post['g-recaptcha-response']  : '';
		$remote_ip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP);
		$check_captcha = $pmrequests->profile_magic_captcha_verification($response,$remote_ip);
	}
	else
	{
		$check_captcha=true;
	}
        $error_message = apply_filters('pm_captcha_error', esc_html__( 'Captcha Failed','profilegrid-user-profiles-groups-and-communities' ));
        $check_captcha = apply_filters('pm_captcha_validation_in_login', $check_captcha);
        
	if($nonce){
            if($check_captcha==true)
            {
                    $username = $post['user_login'];
                    if(is_email($post['user_login']))
                    {
                        $user = get_user_by('email', $username);
                    }
                    else
                    {
                        $user = get_user_by('login', $username);
                    }
                    $password = $post['user_pass'];
                    $remeberme = isset($post['rememberme']) ? true : false;
                    
                    $secure_cookie = is_ssl();
                    if ($user) 
                    {
                            if(wp_check_password( $password, $user->data->user_pass, $user->ID))
                            {
                                    $creds = array('user_login' => $user->data->user_login, 'user_password' => $password, 'remember'=> $remeberme);
                                    $user = wp_signon( $creds, $secure_cookie );

                                    $pm_redirect_after_login = $dbhandler->get_global_option_value('pm_redirect_after_login','0');
                                    if($pm_redirect_after_login==0)
                                    {
                                            $url = home_url('wp-admin');
                                    }
                                    else
                                    {
                                            $url = get_permalink($pm_redirect_after_login);	
                                    }
                                    $url = apply_filters( 'pg_login_redirect',$url,$url, $user );
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
                $pm_error .= '<p class="pm_error">'. $error_message .'</p>';	
            }
        }else{
            $pm_error .= '<p class="pm_error">'. esc_html__('Failed security check.','profilegrid-user-profiles-groups-and-communities' ).'</p>';
        }
}
$themepath = $this->profile_magic_get_pm_theme('login-form-tpl');
include $themepath;
?>
