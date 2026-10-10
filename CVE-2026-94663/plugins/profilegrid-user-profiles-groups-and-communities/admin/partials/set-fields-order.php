<?php
$dbhandler  = new PM_DBhandler();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'FIELDS';
$list_order = filter_input( INPUT_POST, 'list_order' );
if ( isset( $list_order ) ) {
	$list = explode( ',', $list_order );
	$i    = 1;
	foreach ( $list as $id ) {
		$dbhandler->update_row( $identifier, 'field_id', $id, array( 'ordering' => $i ), array( '%d' ), '%d' );
		$i++;
	}
}

