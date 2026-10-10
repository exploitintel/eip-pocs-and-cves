<?php
$pm_sanitizer = new PM_sanitizer();

$nonce = filter_input( INPUT_POST, 'nonce' );

if ( !isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
    die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
}
$post         = $pm_sanitizer->sanitize( $_POST );
$name      = $post['name'];
$blogusers = get_users( array( 'search' => $name ) );
// Array of WP_User objects.
foreach ( $blogusers as $user ) {
	echo '<span>' . esc_html( $user->user_email ) . '</span>';
}

