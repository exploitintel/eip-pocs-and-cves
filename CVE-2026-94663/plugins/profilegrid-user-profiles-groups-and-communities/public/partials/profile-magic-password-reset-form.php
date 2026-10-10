<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$pm_error = '';
if ( isset($attributes) && isset($attributes['errors']) && count( $attributes['errors'] ) > 0 )
{
   foreach ( $attributes['errors'] as $error )
   {
	   $pm_error .= '<span>'.$error.'</span>';
   }
}
$themepath = $this->profile_magic_get_pm_theme('password-reset-form-tpl');
include $themepath;
?>
