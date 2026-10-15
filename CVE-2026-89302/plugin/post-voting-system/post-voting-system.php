<?php

/*
Plugin Name: Post Voting System
Version: 1.0
Description: Provides links to upvote or downvote each of your posts for logged in registered users.
Author: Saptarshi Mandal
*/

/*
Plugin constants
*/
define( 'PVS_ROW_PER_PAGE', 5 );
define( 'PVS_MAX_PAGE_LINK', 7 );
define( 'PVS_NEIGHBOR', 3 );

/*
Check the version compatibility with your current WP version
*/
global $wp_version;

$message = "This plugin requires WordPress 3.5.1 or higher. You are currently running on WordPress-" . $wp_version . ". If you want to continue using this plugin, please upgrade from <a href='http://codex.wordpress.org/Upgrading_WordPress' target='_blank'>here</a>";

// check version compatibility
if ( version_compare( $wp_version, '3.5.1', '<' ) ){
	exit( $message );
}

/*
Activate the plugin
*/
function pvs_activate_post_voting_system(){
	global $wpdb;
	
	// create the table to record up and down votes for each individual posts
	$wpdb->query( "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "post_votes (
					id int(11) not null auto_increment,
					post_id bigint(20) null,
					upvote_count int(11) not null,
					downvote_count int(11) not null,
					primary key (id)
					) ENGINE=INNODB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;" );
					
	// create the table to record which user has voted for which post
	$wpdb->query( "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "user_votes (
					id int(11) not null auto_increment,
					user_id bigint(20) null,
					post_id bigint(20) null,
					primary key (id)
					) ENGINE=INNODB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;" );
}
add_action( 'admin_init', 'pvs_activate_post_voting_system' );

/*
Deactivate the plugin
*/
function pvs_deactivate_post_voting_system(){
	
}
register_deactivation_hook( __FILE__, 'pvs_deactivate_post_voting_system' );

/*
Add plugin menu to the dashboard menu panel
*/
function pvs_add_plugin_menu_tab(){
	add_menu_page( 'PVS', 'PVS', 'manage_options', 'add-post-voting-system-menu-tab', 'pvs_add_post_voting_system_menu_tab', plugins_url( '/images/pvs-icon.gif', __FILE__ ) );
}
add_action( 'admin_menu', 'pvs_add_plugin_menu_tab' );

/*
Display the main plugin page for managing individual post votes
*/
function pvs_add_post_voting_system_menu_tab(){
	if ( ! current_user_can( 'manage_options' ) ){
		exit( 'You don\'t have sufficient permissions to access this page' );
	}
	
	require_once( 'post-votes-management-panel.php' );
}

/*
Add the voting links style
*/
function pvs_add_front_end_style(){
	wp_register_style( 'post-voting-system-front-end-style', plugins_url( '/css/post-voting-system-front-end-style.css', __FILE__ ) );
	wp_enqueue_style( 'post-voting-system-front-end-style' );
}
add_action( 'wp_enqueue_scripts', 'pvs_add_front_end_style' );

/*
Add the ajax vote counter and vote reset counter
*/
function pvs_add_scripts(){
	global $post;
	
	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'post-voting-system-ajax-counter', plugins_url( '/js/post-voting-system-ajax-counter.js', __FILE__ ), array( 'jquery' ), TRUE );
	wp_enqueue_script( 'post-voting-system-ajax-counter' );
	wp_localize_script( 'post-voting-system-ajax-counter', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'id' => $post->ID ) );
	
	wp_register_script( 'post-voting-system-ajax-reset-counter', plugins_url( '/js/post-voting-system-ajax-reset-counter.js', __FILE__ ), array( 'jquery' ), TRUE );
	wp_enqueue_script( 'post-voting-system-ajax-reset-counter' );
	
	wp_register_script( 'post-voting-system-utility', plugins_url( '/js/post-voting-system-utility.js', __FILE__ ), array( 'jquery' ), TRUE );
	wp_enqueue_script( 'post-voting-system-utility' );
}
add_action( 'wp_enqueue_scripts', 'pvs_add_scripts' );
add_action( 'admin_enqueue_scripts', 'pvs_add_scripts' );

/*
Handle the ajax reset count
*/
function pvs_reset_vote_count(){
	global $wpdb;
	
	$row = $_REQUEST[ 'row' ];
	$post_id = end( explode( '_', $row ) );
	
	$query = "UPDATE " . $wpdb->prefix . "post_votes SET upvote_count = 0, downvote_count = 0 WHERE post_id = " . $post_id;
	$wpdb->query( $query );
	
	$query = "DELETE FROM " . $wpdb->prefix . "user_votes WHERE post_id = " . $post_id;
	$wpdb->query( $query );
	
	$query = "SELECT * FROM " . $wpdb->prefix . "post_votes WHERE post_id = " . $post_id;
	$result = $wpdb->get_results( $query, ARRAY_A );
	
	$html = "<th align='center' valign='top' style='text-align:center;'>
				<input type='checkbox' name='reset_selector[]' class='reset_selector'/>
				<input type='hidden' name='post_id[]' value='" . $result[ 0 ][ 'post_id' ] . "'/>
			</th>
			<td align='center' valign='top' style='text-align:center;'>" . pvs_get_post_name( $result[ 0 ][ 'post_id' ] ) . "</td>
			<td align='center' valign='top' style='text-align:center;'>" . $result[ 0 ][ 'upvote_count' ] . "</td>
			<td align='center' valign='top' style='text-align:center;'>" . $result[ 0 ][ 'downvote_count' ] . "</td>
			<td align='center' valign='top' style='text-align:center;'><a href='javascript:void(0);' class='button button-primary button-large reset_button'>Reset Counter</a></td>";
	
	echo json_encode( array( 'response' => $html ) );
	
	die(); 
}
add_action( 'wp_ajax_reset_counter_062613', 'pvs_reset_vote_count' );

/*
Handle multiple post vote counter reset
*/
function pvs_reset_multiple_vote_count(){
	global $wpdb;
	
	$nonce = 'reset_multiple_post_vote_counter';
	if ( ! wp_verify_nonce( $_REQUEST[ '_nonce' ], $nonce ) ){
		die( 'Unauthorized access attempt' );
	}
	
	$reset_selectors = $_REQUEST[ 'reset_selector' ];
	$post_ids = $_REQUEST[ 'post_id' ];
	
	foreach ( $reset_selectors as $key => $value ){
		if ( isset( $value ) ){
			$query = "UPDATE " . $wpdb->prefix . "post_votes SET upvote_count = 0, downvote_count = 0 WHERE post_id = " . $post_ids[ $key ];
			$wpdb->query( $query );
			
			$query = "DELETE FROM " . $wpdb->prefix . "user_votes WHERE post_id = " . $post_ids[ $key ];
			$wpdb->query( $query );
		}
	}
	
	wp_redirect( $_SERVER[ 'HTTP_REFERER' ] );
	
	die();
}
add_action( 'admin_action_reset_multiple_post_vote_counter_081613', 'pvs_reset_multiple_vote_count' );

/*
Pagination methods
*/
function pvs_get_total_page_links( $query ){
	global $wpdb;
	
	$result = $wpdb->get_results( $query, ARRAY_A );	
	$total_rows = count( $result );	
	$total_page_links = ( PVS_ROW_PER_PAGE >= $total_rows ) ? FALSE : ceil( $total_rows / PVS_ROW_PER_PAGE );
	
	return $total_page_links;
}

function pvs_paginate( $query, $total_page_links ){
	global $wpdb;
	
	$p = $_REQUEST[ 'p' ];
		
	if ( $total_page_links ){ // PAGINATION REQUIRED; MODIFY THE MOTHER QUERY
		$start = ( ! $p ) ? 0 : ( $p - 1 ) * PVS_ROW_PER_PAGE;
		
		$query .= " LIMIT " . $start . ", " . PVS_ROW_PER_PAGE;	
	}
	
	$results = $wpdb->get_results( $query, ARRAY_A );
		
	return $results;
}

function pvs_display_page_links( $total_page_links ){
	$p = $_REQUEST[ 'p' ];
	
	$page_links = ( PVS_MAX_PAGE_LINK >= $total_page_links ) ? $total_page_links : PVS_MAX_PAGE_LINK;
	
	if ( ! $p ){
		$lower_limit = 1;
		$upper_limit = $page_links;
	}else if ( $p == $total_page_links ){
		$lower_limit = ( $p - ( $page_links - 1 ) );
		$upper_limit = $p;
	}
	else{
		$lower_limit = ( 1 >= ( $p - PVS_NEIGHBOR ) ) ? 1 : ( $p - PVS_NEIGHBOR );
		
		if ( $total_page_links < ( $p + PVS_NEIGHBOR ) ){
			$upper_limit = $total_page_links;
		}else{
			if ( $page_links > ( $p + PVS_NEIGHBOR ) ){
				$upper_limit = ( $p + PVS_NEIGHBOR ) + ( $page_links - ( $p + PVS_NEIGHBOR ) );
			}
			else{
				$upper_limit = ( $p + PVS_NEIGHBOR );
			}
		}
	}
	
	$redirect_url = admin_url( 'admin.php?page=add-post-voting-system-menu-tab' );
		
	echo "<td align='center' valign='top' style='text-align:center;'><a href='" . $redirect_url . "&p=1'>First</a></td>";
	
	if ( 1 < $p ){
		echo "<td align='center' valign='top' style='text-align:center;'><a href='" . $redirect_url . "&p=" . ( $p - 1 ) . "'>Prev</a></td>";
	}
	
	for ( $i = $lower_limit; $i <= $upper_limit; $i++ ){
		echo "<td align='center' valign='top' style='text-align:center;'><a href='" . $redirect_url . "&p=" . $i . "'>" . $i . "</a></td>";
	}
	
	if ( $p < $total_page_links ){
		echo "<td align='center' valign='top' style='text-align:center;'><a href='" . $redirect_url . "&p=" . ( $p + 1 ) . "'>Next</a></td>";
	}
	
	echo "<td align='center' valign='top' style='text-align:center;'><a href='" . $redirect_url . "&p=" . $total_page_links . "'>Last</a></td>";
}

/*
Fetch all the posts voted for
*/
function pvs_fetch_all_posts_voted_for(){
	global $wpdb;
	
	$query = "SELECT * FROM " . $wpdb->prefix ."post_votes";
	
	$total_page_links = pvs_get_total_page_links( $query );
	
	$testimonials = pvs_paginate( $query, $total_page_links );
	
	return array( $total_page_links, $testimonials );
}

/*
Fetch post name
*/
function pvs_get_post_name( $id ){
	global $wpdb;
	
	$query = "SELECT post_name FROM " . $wpdb->prefix . "posts WHERE ID = " . $id;
	$result = $wpdb->get_results( $query, ARRAY_A );
	
	$post_name = ucwords( str_replace( '-', ' ', $result[ 0 ][ 'post_name' ] ) );
	
	return $post_name;
}

/*
Handle the ajax vote count
*/
function pvs_count_vote(){	
	global $wpdb;
	
	// get the user id for the currently logged-in user 
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;
	
	// search table to see if the current user has voted for this post already
	$query = "SELECT * FROM " . $wpdb->prefix . "user_votes WHERE post_id = " . $_REQUEST[ 'id' ] . " AND user_id = " . $current_user_id;
	$result = $wpdb->get_results( $query, ARRAY_A );
	
	// the current user has already voted for this post. prevent her to re-vote
	if ( is_array( $result ) && ! empty( $result ) ){
		echo json_encode( array( 'response' => 'error' ) ); // return error status
	}else{ // the current user hasn't voted for this post yet
		// get the existing data for this post
		$query1 = "SELECT * FROM " . $wpdb->prefix . "post_votes WHERE post_id = " . $_REQUEST[ 'id' ];	
		$result1 = $wpdb->get_results( $query1, ARRAY_A );
				
		// set the vote counters
		$up_count = 0;
		$down_count = 0;
		
		if ( is_array( $result1 ) && empty( $result1 ) ){ // no votes have been recorded for this post yet, insert record
			$query2 = "INSERT INTO " . $wpdb->prefix . "post_votes VALUES ( NULL, " . $_REQUEST[ 'id' ] .", ";
			
			if ( 'upvote' == $_REQUEST[ 'type' ] ){ // is upvote? first upvote, set upcounter to 1
				$up_count = 1;			
			}else if ( 'downvote' == $_REQUEST[ 'type' ] ){ // is downvote? first downvote, set counter to 1
				$down_count = 1;
			}
			
			$query2 .= $up_count . ", " . $down_count . ")";
		}else{ // this post has been voted for already
			$query2 = "UPDATE " . $wpdb->prefix ."post_votes SET ";
			
			if ( 'upvote' == $_REQUEST[ 'type' ] ){ // is upvote? increment the upcounter, set downcounter to the present downvote count
				$up_count = $result1[ 0 ][ 'upvote_count' ] + 1;
				$down_count = $result1[ 0 ][ 'downvote_count' ];		
			}else if ( 'downvote' == $_REQUEST[ 'type' ] ){ // is downvote? increment the downcounter, set upcounter to the present upvote count
				$down_count = $result1[ 0 ][ 'downvote_count' ] + 1;
				$up_count = $result1[ 0 ][ 'upvote_count' ];
			}
			
			$query2 .= "upvote_count = " . $up_count . ", downvote_count = " . $down_count . " WHERE id = " . $result1[ 0 ][ 'id' ];
		}
		
		$wpdb->query( $query2 );
		
		$query3 = "SELECT * FROM " . $wpdb->prefix . "post_votes WHERE post_id = " . $_REQUEST[ 'id' ];
		$result3 = $wpdb->get_results( $query3, ARRAY_A );
		
		if ( 'upvote' == $_REQUEST[ 'type' ] ){
			$count = $result3[ 0 ][ 'upvote_count' ]; // get the new upvote count
		}else if ( 'downvote' == $_REQUEST[ 'type' ] ){ // get the new downvote count
			$count = $result3[ 0 ][ 'downvote_count' ];
		}
		
		// insert the user-voted-this-post record 
		$query4 = "INSERT INTO " . $wpdb->prefix . "user_votes VALUES ( NULL, " . $current_user_id . ", " . $_REQUEST[ 'id' ] . " )";
		$wpdb->query( $query4 );
		
		echo json_encode( array( 'response' => $count ) ); // print vote count ajax way
	}
		
	die();
}
add_action( 'wp_ajax_count_vote_062613', 'pvs_count_vote' );
add_action( 'wp_ajax_nopriv_count_vote_062613', 'pvs_count_vote' );

/*
Create voting links
*/
function pvs_create_voting_links(){
	global $post;
	global $wpdb;
	$up_count = 0;
	$down_count = 0;
	
	$post_id = $post->ID;	
	
	$query = "SELECT * FROM " . $wpdb->prefix . "post_votes WHERE post_id = " . $post_id;
	$result = $wpdb->get_results( $query, ARRAY_A );
	
	if ( is_array( $result ) && ! empty( $result ) ){
		$up_count = $result[ 0 ][ 'upvote_count' ];
		$down_count = $result[ 0 ][ 'downvote_count' ];
	}
	
	$html = "<div class='main-wrapper'>
			 	<div class='first'><a href='javascript:void(0)' id='up-count' title='Click to like this post'><img valign='middle' src='" . plugins_url( '/images/upvote.gif', __FILE__ ) . "'/></a><div class='counter' id='pvs-up-counter'>" . $up_count . "</div></div>
				<div class='other'><a href='javascript:void(0)' id='down-count' title='Click to dislike this post'><img valign='middle' src='" . plugins_url( '/images/downvote.gif', __FILE__ ) . "'/></a><div class='counter' id='pvs-down-counter'>" . $down_count . "</div></div>
				<div class='voted' id='pvs-message'></div>
			 </div>";
			 
	return $html;
}

/*
Add the voting links below the post content
*/
function pvs_add_voting_links($content){
	if ( is_single() && is_user_logged_in() ){
		return $content . pvs_create_voting_links();
	}else{
		return $content;
	}
}
add_filter( 'the_content', 'pvs_add_voting_links' );

?>