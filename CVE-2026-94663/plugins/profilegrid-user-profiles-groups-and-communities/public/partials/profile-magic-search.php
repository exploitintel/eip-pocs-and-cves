<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$permalink = get_permalink();
$path =  plugin_dir_url(__FILE__);
$groups =  $dbhandler->get_all_result('GROUPS',array('id','group_name'));
$themepath = $this->profile_magic_get_pm_theme('search-tpl');
include $themepath;
wp_enqueue_script( 'pg-search.js', plugin_dir_url( __FILE__ ) . '../js/pg-search.js', array( 'jquery' ), $this->version, true );
                
?>
