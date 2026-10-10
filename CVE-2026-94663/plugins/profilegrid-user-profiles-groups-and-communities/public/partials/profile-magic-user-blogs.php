<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$permalink = get_permalink();
$current_user = wp_get_current_user();
$themepath = $this->profile_magic_get_pm_theme('user-blogs-tpl');
include $themepath;
?>

