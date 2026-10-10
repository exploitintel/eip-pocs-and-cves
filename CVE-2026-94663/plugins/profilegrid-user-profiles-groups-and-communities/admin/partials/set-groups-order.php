<?php
$dbhandler  = new PM_DBhandler();
$textdomain = $this->profile_magic;
$path       = plugin_dir_url( __FILE__ );
$list_order = filter_input( INPUT_POST, 'list_order' );
$list_items = filter_input( INPUT_POST, 'list_items' );
$list_icon  = filter_input( INPUT_POST, 'icon' );

if ( isset( $list_order ) ) {
		$list = explode( ',', $list_order );
		update_option( 'pg_group_menu', $list );
}

if ( isset( $list_items ) ) {
	$list = explode( ',', $list_items );
	update_option( 'pg_group_list', $list );
}

if ( isset( $list_icon ) ) {
	update_option( 'pg_group_icon', $list_icon );
}

