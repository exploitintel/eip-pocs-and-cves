<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$pmrequests = new PM_request;
$pm_sanitizer = new PM_sanitizer();
$pm_error = '';
// Retrieve possible errors from request parameters
$attributes['errors'] = array();
$request_obj      = $pm_sanitizer->sanitize( $_REQUEST );
//print_r($request_obj);
if ( isset( $request_obj['errors'] ) ) {
    $error_codes = explode( ',', $request_obj['errors'] ); 
    foreach ( $error_codes as $error_code ) 
	{
        $attributes['errors'][]=  $pmrequests->profile_magic_get_error_message($error_code,$textdomain);
    }
}

if ( count( $attributes['errors'] ) > 0 )
{
	foreach ( $attributes['errors'] as $error )
	{
		$pm_error .= '<span>'.$error.'</span>';
	}
}
if(isset($request_obj['key']) && isset($request_obj['login']))
{
   
    $attributes['login'] = $request_obj['login'];
    $attributes['key'] = $request_obj['key'];
    // Error messages
    $errors = array();
    if ( isset( $request_obj['error'] ) ) 
    {
            $error_codes = explode( ',', $request_obj['error'] );
            foreach ( $error_codes as $code ) 
            {
                    $errors []= $pmrequests->profile_magic_get_error_message($code,$this->profile_magic);
            }
    }
    $attributes['errors'] = $errors;
    
    if ( count( $attributes['errors'] ) > 0 )
    {
            foreach ( $attributes['errors'] as $error )
            {
                    $pm_error .= '<span>'.$error.'</span>';
            }
    }
                 
    $themepath = $this->profile_magic_get_pm_theme('password-reset-form-tpl');
}
else
{
    $themepath = $this->profile_magic_get_pm_theme('forget-password-form-tpl');
}
include $themepath;
?>
