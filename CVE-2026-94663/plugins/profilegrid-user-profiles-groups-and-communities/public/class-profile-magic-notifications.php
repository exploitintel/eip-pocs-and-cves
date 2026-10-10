<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profilegrid.co
 * @since      1.0.0
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/public
 * @author     ProfileGrid <support@profilegrid.co>
 */
class Profile_Magic_Notification {

	/*
	 * NOTIFICATION STATUS
	 * status = 1---------NEW NOTIFICATION
	 * status = 2---------READ
	 * status = 3---------DELETE
	 * status = 4---------SENT but UNREAD
	 *
	 *
	 *      */


		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $profile_magic    The ID of this plugin.
		 */
		private $profile_magic;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $profile_magic       The name of the plugin.
		 * @param      string $version    The version of this plugin.
		 */



	public function pm_notification_heartbeat_received( $response, $data ) {

		$data['pm_notify'] = array();
		if ( isset( $data['pm_notify_status'] ) && $data['pm_notify_status'] != 'ready' ) {
				return $response;
		}
		$dbhandler      = new PM_DBhandler();
		$current_uid    = get_current_user_id();
		$notification   = $dbhandler->get_all_result(
			'NOTIFICATION',
			'*',
			array(
				'status' => 1,
				'rid'    => $current_uid,
			),
			'results',
			$offset     = 0,
			$limit      = false,
			$sort_by    = 'timestamp',
			$descending = true
		);
		$data['unread_notif'] = $this->pm_get_user_unread_notification_count( $current_uid );

		// return $notification;

		if ( empty( $notification ) ) {
				return $data;
		} else {

			foreach ( $notification as $db_notification ) {
				// set id of each notification
				$id   = $db_notification->id;
				$type = $db_notification->type;
				switch ( $type ) {
					case 'comment':
							$data['pm_notify'][ $id ] = $this->pm_generate_comment_notice( $db_notification, $id );
						break;
					case 'BlogPost':
							$data['pm_notify'][ $id ] = $this->pm_generate_blog_post_notice( $db_notification, $id );
						break;
					case 'BlogPostOwner':
							$data['pm_notify'][ $id ] = $this->pm_generate_blog_post_owner_notice( $db_notification, $id );
						break;
					case 'FriendAdded':
							$data['pm_notify'][ $id ] = $this->pm_generate_friend_added_notice( $db_notification, $id );
						break;
					case 'FriendRequest':
							$data['pm_notify'][ $id ] = $this->pm_generate_friend_request_notice( $db_notification, $id );
						break;
					case 'WallPost':
							$data['pm_notify'][ $id ] = $this->pm_generate_wall_post_notice( $db_notification, $id );
						break;
					case 'WallPostOwner':
							$data['pm_notify'][ $id ] = $this->pm_generate_wall_post_owner_notice( $db_notification, $id );
						break;
					case 'JoinGroup':
							$data['pm_notify'][ $id ] = $this->pm_generate_group_join_owner_notice( $db_notification, $id );
						break;
					case 'RemoveGroup':
							$data['pm_notify'][ $id ] = $this->pm_generate_group_remove_owner_notice( $db_notification, $id );
						break;
					case 'Message':
							$data['pm_notify'][ $id ] = $this->pm_generate_message_notice( $db_notification, $id );
						break;
					case 'Mcred_badge_earned':
							$data['pm_notify'][ $id ] = $this->pm_generate_mycred_badge_earned_notice( $db_notification, $id );
						break;
					case 'GroupEvent':
							$data['pm_notify'][ $id ] = $this->pm_generate_group_event_notice( $db_notification, $id );
						break;
					case 'UserStatus':
							$data['pm_notify'][ $id ] = $this->pm_generate_user_status_notice( $db_notification, $id );
						break;

				}

				$this->pm_change_notification_status( $id, 4 );

			}
			 return $data;
		}

	}
	public function exclude_deactivate_extension_notification() {
		$exclude = array();
		if ( ! class_exists( 'Profilegrid_Group_Wall' ) ) {
			$exclude[] = 'WallPost';
			$exclude[] = 'WallPostOwner';
		}

		if ( ! defined( 'myCRED_BADGE' ) ) {
			$exclude[] = 'Mcred_badge_earned';
		}

		if ( ! class_exists( 'EventM_Factory' ) ) {
			$exclude[] = 'GroupEvent';
		}

		if ( ! class_exists( 'Profilegrid_User_Profile_Status' ) ) {
			$exclude[] = 'UserStatus';
		}

		return $exclude;

	}

	public function pm_generate_notification_without_heartbeat( $loadnum = 1 ) {

		$dbhandler   = new PM_DBhandler();
                $pmrequests   = new PM_request();
		$current_uid = get_current_user_id();
		$loadnum     = isset( $loadnum ) ? absint( $loadnum ) : 1;
		$limit       = 15;
		$offset      = ( $loadnum - 1 ) * $limit;
                
                $allowed_html = $pmrequests->pg_allowed_html_wp_kses();
		$where   = 1;
		$exclude = $this->exclude_deactivate_extension_notification();

		$additional = " status in (1,2,4) AND rid= $current_uid ";
		if ( ! empty( $exclude ) ) {
			$additional .= " AND type NOT IN ( '" . implode( "', '", $exclude ) . "' )";
		}

		$notification = $dbhandler->get_all_result( 'NOTIFICATION', '*', $where, 'results', $offset, $limit, $sort_by = 'timestamp', true, $additional );
		$count        = 0;

		if ( empty( $notification ) ) {
			?>
				  <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'Thats it for today. You are all caught up!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			<?php
		} else {

			foreach ( $notification as $db_notification ) {
				$count++;
				$id     = $db_notification->id;
				$type   = $db_notification->type;
				$status = $db_notification->status;
				switch ( $type ) {
					case 'comment':
						$data['pm_notify'][ $id ] = $this->pm_generate_comment_notice( $db_notification, $id );
						break;
					case 'BlogPost':
						$data['pm_notify'][ $id ] = $this->pm_generate_blog_post_notice( $db_notification, $id );
						break;
					case 'BlogPostOwner':
						$data['pm_notify'][ $id ] = $this->pm_generate_blog_post_owner_notice( $db_notification, $id );
						break;
					case 'FriendAdded':
						$data['pm_notify'][ $id ] = $this->pm_generate_friend_added_notice( $db_notification, $id );
						break;
					case 'FriendRequest':
						$data['pm_notify'][ $id ] = $this->pm_generate_friend_request_notice( $db_notification, $id );
						break;
					case 'WallPost':
						$data['pm_notify'][ $id ] = $this->pm_generate_wall_post_notice( $db_notification, $id );
						break;
					case 'WallPostOwner':
						$data['pm_notify'][ $id ] = $this->pm_generate_wall_post_owner_notice( $db_notification, $id );
						break;
					case 'JoinGroup':
						$data['pm_notify'][ $id ] = $this->pm_generate_group_join_owner_notice( $db_notification, $id );
						break;
					case 'RemoveGroup':
						$data['pm_notify'][ $id ] = $this->pm_generate_group_remove_owner_notice( $db_notification, $id );
						break;
					case 'Message':
						$data['pm_notify'][ $id ] = $this->pm_generate_message_notice( $db_notification, $id );
						break;
					case 'Mcred_badge_earned':
						$data['pm_notify'][ $id ] = $this->pm_generate_mycred_badge_earned_notice( $db_notification, $id );
						break;
					case 'GroupEvent':
						$data['pm_notify'][ $id ] = $this->pm_generate_group_event_notice( $db_notification, $id );
						break;
					case 'UserStatus':
						$data['pm_notify'][ $id ] = $this->pm_generate_user_status_notice( $db_notification, $id );
						break;
					default:
							$data['pm_notify'][ $id ] = esc_html__( 'no new notification', 'profilegrid-user-profiles-groups-and-communities' );
						break;
				}
				if ( $data['pm_notify'][ $id ] != '' ) {
					echo wp_kses($data['pm_notify'][ $id ],$allowed_html);
				}
				if ( $status == 1 ) {
					$this->pm_change_notification_status( $id, 4 );
				}
			}
			if ( $count == $limit ) {
				$new_loadnum = $loadnum + 1;
				echo ' <div id="pm_load_more_notif" class="pm-dbfl" onclick="pm_load_more_notification(' . esc_attr($new_loadnum) . ')" >' . esc_html__( 'Load More..', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
			}
		}

	}


	/*-----------------NOTIFICATION CREATION FUNCTIONS------------------*/


	public function pm_add_comment_notification( $comment_ID, $comment_approved ) {
		$dbhandler = new PM_DBhandler();
		$comment   = get_comment( $comment_ID );
		$rid       = get_post_field( 'post_author', $comment->comment_post_ID );
		$post_type = get_post_type( $comment->comment_post_ID );
		if ( $post_type == 'pg_groupwalls' || $post_type == 'profilegrid_blogs' || $post_type == 'attachment' ) {
			if ( $rid != $comment->user_id ) :
				$timestamp          = current_time( 'mysql', true );
				$title              = get_the_title( $comment->comment_post_ID );
				$meta               = array();
				$meta['comment_id'] = $comment->comment_ID;
				$meta['posttype']   = $post_type . '_' . $comment->comment_ID;
				$meta               = maybe_serialize( $meta );
				$data               = array(
					'type'        => 'comment',
					'sid'         => $comment->user_id,
					'rid'         => $rid,
					'timestamp'   => $timestamp,
					'description' => $title,
					'status'      => 1,
					'meta'        => $meta,
				);
				$arg                = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
				$gid                = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
				endif;
		}
	}

	public function pg_wallpost_published_notification( $meta_id, $post_id, $meta_key = '', $meta_value = '' ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$post       = get_post( $post_id );
		$ID         = $post_id;
		if ( !empty($post) && $post->post_type == 'pg_groupwalls' ) {
			$author          = $post->post_author; /* Post author ID. */
			$title           = $post->post_title;
			$timestamp       = current_time( 'mysql', true );
			$meta            = array();
			$meta['post_id'] = $post_id;
			$meta            = maybe_serialize( $meta );
			// $meta_query_array = array();
			// $meta_query_array['relation'] = 'AND';
			// $gids = get_user_meta($author,'pm_group',true);
			$gids = maybe_unserialize( get_post_meta( $post_id, 'pm_accessible_groups', true ) );
			// print_r($gids);die;
			$uids = array();
                        if(isset($gids) && is_array($gids) && !empty($gids)){
                            foreach ( $gids as $gid ) {
                                    $meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid' => $gid ) );
                                    $users            = $dbhandler->pm_get_all_users( '', $meta_query_array );
                                    foreach ( $users as $user ) {
                                            $uids[] = $user->ID;
                                    }
                            }
                        }
			$unique_ids  = array_unique( $uids );
			$post_status = get_post_status( $ID );
			$is_added    = get_post_meta( $ID, 'pg_notification_added', true );
			if ( ! empty( $unique_ids ) && empty( $is_added ) ) {
				foreach ( $unique_ids as $uid ) {
					if ( $uid != $author && $post_status == 'publish' ) {
						$data = array(
							'type'        => 'WallPost',
							'sid'         => $author,
							'rid'         => $uid,
							'timestamp'   => $timestamp,
							'description' => $title,
							'status'      => 1,
							'meta'        => $meta,
						);
						$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
						$gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
						add_post_meta( $ID, 'pg_notification_added', '1' );

					}
					if ( $uid == $author && $post_status == 'publish' ) {
						$data = array(
							'type'        => 'WallPostOwner',
							'sid'         => $author,
							'rid'         => $uid,
							'timestamp'   => $timestamp,
							'description' => $title,
							'status'      => 1,
							'meta'        => $meta,
						);
						$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
						$gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
						add_post_meta( $ID, 'pg_notification_added', '1' );

					}
				}
			}
		}
	}


	public function pg_new_group_event_notification( $ID, $gids ) {
		 $dbhandler      = new PM_DBhandler();
		$pmrequests      = new PM_request();
		$post            = get_post( $ID );
		$author          = $post->post_author; /* Post author ID. */
		$title           = $post->post_title;
		$timestamp       = current_time( 'mysql', true );
		$meta            = array();
		$meta['post_id'] = $ID;
		$meta            = maybe_serialize( $meta );

		// $is_added = get_post_meta($ID,'pg_notification_added',true);
		$uids = array();
		foreach ( $gids as $gid ) {
			$meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid' => $gid ) );
			$users            = $dbhandler->pm_get_all_users( '', $meta_query_array );
			foreach ( $users as $user ) {
				$uids[] = $user->ID;
			}
		}
		$userids = array_unique( $uids );

		if ( ! empty( $userids ) ) {
			foreach ( $userids as $uid ) {
				$data = array(
					'type'        => 'GroupEvent',
					'sid'         => $author,
					'rid'         => $uid,
					'timestamp'   => $timestamp,
					'description' => $title,
					'status'      => 1,
					'meta'        => $meta,
				);
				$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
				$gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
				add_post_meta( $ID, 'pg_notification_added', '1' );

			}
		}
	}


	public function pm_blog_post_published( $meta_id, $post_id, $meta_key = '', $meta_value = '' ) {
		$dbhandler                    = new PM_DBhandler();
		$pmrequests                   = new PM_request();
		$pmfriends                    = new PM_Friends_Functions();
		$post                         = get_post( $post_id );
                if(!empty($post))
                {
                    $author                       = $post->post_author; /* Post author ID. */
                    $title                        = $post->post_title;
                    $ID                           = $post_id;
                    $post_status                  = get_post_status( $post_id );
                    $timestamp                    = current_time( 'mysql', true );
                    $meta                         = array();
                    $meta['post_id']              = $ID;
                    $meta                         = maybe_serialize( $meta );
                    $meta_query_array             = array();
                    $meta_query_array['relation'] = 'AND';
                    if ( $post->post_type == 'profilegrid_blogs' && ( $meta_key == 'pm_content_access' || $meta_key == 'pm_content_access_group' ) ) :
                            switch ( get_post_meta( $ID, 'pm_content_access', true ) ) {
                                    case 1:
                                            $meta_query_array[] = array( 'key' => 'pm_group' );
                                            $users              = $dbhandler->pm_get_all_users( '', $meta_query_array );
                                            break;
                                    case 2:
                                            if ( get_post_meta( $ID, 'pm_content_access_group', true ) != 'all' ) {
                                                    $gid                = get_post_meta( $ID, 'pm_content_access_group', true );
                                                    $meta_query_array[] = array(
                                                            'key'     => 'pm_group',
                                                            'value'   => sprintf( ':"%s";', $gid ),
                                                            'compare' => 'like',
                                                    );
                                                    $users              = $dbhandler->pm_get_all_users( '', $meta_query_array );
                                            } else {
                                                    $meta_query_array[] = array( 'key' => 'pm_group' );
                                                    $users              = $dbhandler->pm_get_all_users( '', $meta_query_array );
                                            }
                                            break;
                                    case 3:
                                                    $myfriends = $pmfriends->profile_magic_my_friends( $author );
                                                    $users     = $dbhandler->pm_get_all_users( '', $meta_query_array, '', '', '', 'ASC', 'ID', array(), array(), $myfriends );
                                            break;
                                    case 4:
                                                    $users = array();
                                            break;
                                    case 5:
                                                    $users                = array();
                                                    $author_groups        = get_user_meta( $author, 'pm_group', true );
                                                    $author_filter_groups = $pmrequests->pg_filter_users_group_ids( $author_groups );
                                            foreach ( $author_filter_groups as $gid ) {
                                                    $meta_query_array[] = array(
                                                            'key'     => 'pm_group',
                                                            'value'   => sprintf( ':"%s";', $gid ),
                                                            'compare' => 'like',
                                                    );
                                                    $group_users        = $dbhandler->pm_get_all_users( '', $meta_query_array );
                                                    $users              = array_merge( $users, $group_users );
                                            }

                                            break;
                                    default:
                                                    $users = array();
                                            break;

                            }
                            $is_added = get_post_meta( $ID, 'pg_notification_added', true );
                            if ( ! empty( $users ) && empty( $is_added ) ) {
                                    $owneradd = 0;
                                    foreach ( $users as $user ) {
                                            if ( $user->ID != $author && $post_status == 'publish' ) {
                                                    $data = array(
                                                            'type'        => 'BlogPost',
                                                            'sid'         => $author,
                                                            'rid'         => $user->ID,
                                                            'timestamp'   => $timestamp,
                                                            'description' => $title,
                                                            'status'      => 1,
                                                            'meta'        => $meta,
                                                    );
                                                    $arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
                                                    $gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
                                                    add_post_meta( $post_id, 'pg_notification_added', '1' );
                                            }

                                            if ( $user->ID == $author && $post_status == 'publish' ) {
                                                    if ( $owneradd == 0 ) :
                                                            $data     = array(
                                                                    'type'        => 'BlogPostOwner',
                                                                    'sid'         => $author,
                                                                    'rid'         => $user->ID,
                                                                    'timestamp'   => $timestamp,
                                                                    'description' => $title,
                                                                    'status'      => 1,
                                                                    'meta'        => $meta,
                                                            );
                                                            $arg      = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
                                                            $gid      = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
                                                            $owneradd = 1;
                                                            add_post_meta( $post_id, 'pg_notification_added', '1' );
                                                     endif;
                                            }
                                    }
                            }
                    endif;
                }
	}

	public function pg_blog_post_change_status( $new_status, $old_status, $post ) {
		  $is_added = get_post_meta( $post->ID, 'pg_notification_added', true );
		if ( empty( $is_added ) && $old_status != 'publish' && $old_status != 'new' && $new_status == 'publish' && $post->post_type == 'profilegrid_blogs' ) {

			// A function to perform actions when a post status changes from any to publish status.
			$this->pm_blog_post_published( 1, $post->ID, 'pm_content_access' );
		}
		if ( empty( $is_added ) && $old_status != 'publish' && $old_status != 'new' && $new_status == 'publish' && $post->post_type == 'pg_groupwalls' ) {

			// A function to perform actions when a post status changes from any to publish status.
			$this->pg_wallpost_published_notification( $post->ID, $post );
		}
	}
	public function pm_friend_request_notification( $rid, $sid ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array();
		// add something to meta if you want
		$meta = maybe_serialize( $meta );
		$data = array(
			'type'        => 'FriendRequest',
			'sid'         => $sid,
			'rid'         => $rid,
			'timestamp'   => $timestamp,
			'description' => '',
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );

	}

	public function pg_new_user_status_notification( $status_id, $data ) {
		$dbhandler   = new PM_DBhandler();
		$pmfriends   = new PM_Friends_Functions();
		$timestamp   = current_time( 'mysql', true );
		$meta        = array( 'status_id' => $status_id );
		$sid         = $data['status_uid'];
		$status_type = $data['status_type'];
		if ( $status_type == 'image' ) {
			$description = $data['image_caption'];
		} else {
			$description = $data['status_text'];
		}
		// add something to meta if you want
		$meta_query_array             = array();
		$meta_query_array['relation'] = 'AND';
		$myfriends                    = $pmfriends->profile_magic_my_friends( $sid );
		$users                        = $dbhandler->pm_get_all_users( '', $meta_query_array, '', '', '', 'ASC', 'ID', array(), array(), $myfriends );
		$meta                         = maybe_serialize( $meta );
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$data1 = array(
					'type'        => 'UserStatus',
					'sid'         => $sid,
					'rid'         => $user->ID,
					'timestamp'   => $timestamp,
					'description' => $description,
					'status'      => 1,
					'meta'        => $meta,
				);
				$arg   = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
				$gid   = $dbhandler->insert_row( 'NOTIFICATION', $data1, $arg );
			}
		}
	}

	public function pm_friend_added_notification( $rid, $sid ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array();
		// add something to meta if you want
		$meta = maybe_serialize( $meta );
		$data = array(
			'type'        => 'FriendAdded',
			'sid'         => $sid,
			'rid'         => $rid,
			'timestamp'   => $timestamp,
			'description' => '',
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$gid  = $dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
	}

	public function pm_joined_new_group_notification( $rid, $sid ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array();
		// add something to meta if you want
		$meta = maybe_serialize( $meta );
		$data = array(
			'type'        => 'JoinGroup',
			'sid'         => $sid,
			'rid'         => $rid,
			'timestamp'   => $timestamp,
			'description' => '',
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
	}

	public function pm_removed_old_group_notification( $rid, $sid ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array();
		// add something to meta if you want
		$meta = maybe_serialize( $meta );
		$data = array(
			'type'        => 'RemoveGroup',
			'sid'         => $sid,
			'rid'         => $rid,
			'timestamp'   => $timestamp,
			'description' => '',
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
	}

	public function pm_added_new_message_notification( $rid, $sid, $message ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array();
		// add something to meta if you want
		$meta = maybe_serialize( $meta );
		$data = array(
			'type'        => 'Message',
			'sid'         => $sid,
			'rid'         => $rid,
			'timestamp'   => $timestamp,
			'description' => $message,
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg  = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
	}

	public function pm_added_earned_new_badge_notification( $uid, $new_level, $badge ) {
		$dbhandler = new PM_DBhandler();
		// $rid = get_post_field( 'post_author',$comment->comment_post_ID);
		$timestamp = current_time( 'mysql', true );
		$meta      = array(
			'new_level' => $new_level,
			'badge'     => $badge,
		);
		// add something to meta if you want
		$message = '';
		$meta    = maybe_serialize( $meta );
		$data    = array(
			'type'        => 'Mcred_badge_earned',
			'sid'         => $uid,
			'rid'         => $uid,
			'timestamp'   => $timestamp,
			'description' => $message,
			'status'      => 1,
			'meta'        => $meta,
		);
		$arg     = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$dbhandler->insert_row( 'NOTIFICATION', $data, $arg );
	}


	/*    ----------NOTIFICATION DISPLAY FUNCTIONS-------------*/



	public function pm_generate_blog_post_notice( $db_notification, $id ) {
		$pmrequests      = new PM_request();
		$dbhandler       = new PM_DBhandler();
		$current_uid     = get_current_user_id();
		$notif           = $db_notification;
		$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

		$meta      = maybe_unserialize( $notif->meta );
		$post_id   = $meta['post_id'];
		$permalink = get_permalink( $post_id );
		// $receivers = maybe_unserialize( $notif->receivers);
		// $current_user_group = $pmrequests->profile_magic_get_user_field_value($current_uid,'pm_group');
		$return = '';
		// if((in_array($current_user_group, $receivers['group']) || in_array($current_uid, $receivers['uid']) ) && !in_array($current_uid, $receivers['exclude']))

		$notif_sender_id    = $notif->sid;
		$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
		$sender_profile_url = $profile_url;
		$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
		$sender_avatar = get_avatar(
			$notif_sender_id,
			50,
			'',
			false,
			array(
				'class'         => 'pm-user-profile',
				'force_display' => true,
			)
		);
		$sender_name   = $pmrequests->pm_get_display_name( $notif_sender_id );
		$description   = $notif->description;

		$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Group Blog Post', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
				else :
					$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
					endif;
				   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
				if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
					$return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
				$return .= '</div>
                
            </div>
        </div>';

				// $this->pm_change_notification_status($id,2);
				return $return;

	}

	public function pm_generate_comment_notice( $db_notification, $id ) {
		$pmrequests         = new PM_request();
		$dbhandler          = new PM_DBhandler();
		$current_uid        = get_current_user_id();
		$notif              = $db_notification;
		$notif_timestamp    = human_time_diff( strtotime( $notif->timestamp ), time() );
		$notif_sender_id    = $notif->sid;
		$status             = $notif->status;
		$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
		$sender_profile_url = $profile_url;

		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
		$sender_avatar   = get_avatar(
			$notif_sender_id,
			50,
			'',
			false,
			array(
				'class'         => 'pm-user-profile',
				'force_display' => true,
			)
		);
		$sender_name     = $pmrequests->pm_get_display_name( $notif_sender_id );
		$description     = $notif->description;
		$meta            = maybe_unserialize( $notif->meta );
		$comment_id      = $meta['comment_id'];
		$comment_content = get_comment( $comment_id );
		if ( ! empty( $comment_content ) ) {
			$link      = get_comment_link( $comment_id );
			$permalink = '<a href="' . $link . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a>';
		} else {
			$permalink = 'Deleted';
		}
		$return = '';
		$title  = $this->pm_comment_notification_title( $db_notification );
		$return = '<div id="notif_' . $id . '" class="pm-notification pm-new-post-comment-notice ">
                 <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
                <div class="pm-notification-card pm-dbfl">
                <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>   <div class="pm-notification-title pm-pad10 ">' . $bold . $title . $bold_close . '</div>
                    <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                        <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                        
                    <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
				else :
					$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
					endif;
				   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                    </div>

                    </div>
                    <div class="pm-notification-footer pm-dbfl">';
				if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
					$return .= '<div class="pm-notification-buttons">' . $permalink . '</div>';
				 endif;
				$return .= '</div>
                </div>
            </div>';
				// $this->pm_change_notification_status($id,2);
				return $return;
	}

	public function pm_comment_notification_title( $notification ) {
		$meta = maybe_unserialize( $notification->meta );

		$comment_id = $meta['comment_id'];
		if ( isset( $meta['posttype'] ) ) {
			$posttype = str_replace( '_' . $comment_id, '', $meta['posttype'] );
		} else {
			$posttype = '';
		}
		switch ( $posttype ) {
			case 'pg_groupwalls':
				$title = esc_html__( 'New Comment on Wall', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'profilegrid_blogs':
				$title = esc_html__( 'New Comment on Blog', 'profilegrid-user-profiles-groups-and-communities' );

				break;
			case 'attachment':
				$title = esc_html__( 'New Comment on Photo', 'profilegrid-user-profiles-groups-and-communities' );

				break;
			default:
				$title = esc_html__( 'New Comment', 'profilegrid-user-profiles-groups-and-communities' );

				break;
		}

		return apply_filters( 'pm_comment_notification_title', $title, $notification );
	}

	public function pm_generate_friend_request_notice( $db_notification, $id ) {
		$dbhandler       = new PM_DBhandler();
		$pmrequests      = new PM_request();
		$pmfriends       = new PM_Friends_Functions();
		$current_uid     = get_current_user_id();
		$notif           = $db_notification;
		$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );
		$notif_sender_id = $notif->sid;
		$user_exist      = get_userdata( $notif_sender_id );
		if ( $user_exist === false ) {
			// user id does not exist
			$sender_profile_url = '';
			$sender_name        = '';
			$sender_avatar      = '';
			$description        = esc_html__( 'This user is no longer registered.', 'profilegrid-user-profiles-groups-and-communities' );
			$button             = '';
		} else {
			// user id exists
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$sender_avatar      = get_avatar(
				$notif_sender_id,
				50,
				'',
				false,
				array(
					'class'         => 'pm-user-profile',
					'force_display' => true,
				)
			);
			$sender_name        = $pmrequests->pm_get_display_name( $notif_sender_id );
			$no_of_friends      = $pmfriends->pm_count_my_friends( $notif_sender_id );
			$description        = $no_of_friends . ' Friends';
			$u2                 = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $notif_sender_id );
			$u1                 = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $current_uid );
			$button             = '<div class="pm-notification-buttons"><a  onClick="pm_confirm_request_from_notification(\'' . $u1 . '\',\'' . $u2 . '\',this,' . $id . ')">' . __( 'Accept', 'profilegrid-user-profiles-groups-and-communities' ) . '</a><a onClick="pm_reject_friend_request_from_notification(\'' . $u1 . '\',\'' . $u2 . '\',this,' . $id . ')">' . __( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
		}

		$status = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}

		$return = '';
		$return = '   <div id="notif_' . $id . '" class="pm-notification pm-friend-request-notice ">
                          <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
                                    <div class="pm-notification-card pm-dbfl">
                             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>     <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Friend Request', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                                    <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                                        <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                                         <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
										else :
											$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
											endif;
										   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                                        </div>
                    
                                    </div>
                                    <div class="pm-notification-footer pm-dbfl">' . $button . '</div>
                                </div>
                            </div>';
										return $return;
	}

	public function pm_generate_friend_added_notice( $db_notification, $id ) {
		$pmrequests      = new PM_request();
		$dbhandler       = new PM_DBhandler();
		$pmmessenger     = new PM_Messenger();
		$pmfriends       = new PM_Friends_Functions();
		$current_uid     = get_current_user_id();
		$notif           = $db_notification;
		$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );
		$notif_sender_id = $notif->sid;
		$status          = $notif->status;

		$user_exist = get_userdata( $notif_sender_id );
		if ( $user_exist === false ) {
			// user id does not exist
			$sender_profile_url = '';
			$sender_name        = '';
			$sender_avatar      = '';
			$description        = esc_html__( 'This user is no longer registered.', 'profilegrid-user-profiles-groups-and-communities' );
			$button             = '';
		} else {
			// user id exists
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$sender_avatar      = get_avatar(
				$notif_sender_id,
				50,
				'',
				false,
				array(
					'class'         => 'pm-user-profile',
					'force_display' => true,
				)
			);
			$sender_name        = $pmrequests->pm_get_display_name( $notif_sender_id );
			$no_of_friends      = $pmfriends->pm_count_my_friends( $notif_sender_id );
			$description        = $no_of_friends . __( ' Friends', 'profilegrid-user-profiles-groups-and-communities' );
			$send_msg_link      = $pmmessenger->pm_get_message_url( $notif_sender_id );
			$u2                 = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $notif_sender_id );
			$u1                 = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $current_uid );
			$button             = '<div class="pm-notification-buttons"><a href="' . $send_msg_link . '">' . __( 'Message', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
		}

		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}

		$return = '';
		$return = '   <div id="notif_' . $id . '" class="pm-notification pm-new-friend-added-notice ">
                        <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
                        <div class="pm-notification-card pm-dbfl">
                   <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>        <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Friend Added', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                            <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                                <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                                
                            <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
							else :
								$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
								endif;
							   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                            </div>
                            </div>
                            <div class="pm-notification-footer pm-dbfl">' . $button . '</div>
                        </div>
                    </div>';
							return $return;
	}



	/*----------------EXTRA NOTIFICATION FUNCTIONS------------------*/



	public function pm_change_notification_status( $notif_id, $status = 2 ) {
		$dbhandler   = new PM_DBhandler();
		$current_uid = get_current_user_id();
		$updated     = $dbhandler->update_row( 'NOTIFICATION', 'id', $notif_id, array( 'status' => $status ) );
	}

	public function pm_get_all_users_with_gid( $gid ) {
		$pmrequests       = new PM_request();
		$dbhandler        = new PM_DBhandler();
		$meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid' => $gid ) );
		$users            = $dbhandler->pm_get_all_users( '', $meta_query_array, '', 0, '', 'DESC', 'ID' );

		return $users;
	}

	public function pm_delete_notification( $id ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'NOTIFICATION';
		$id         = absint( $id );
		if ( 0 === $id ) {
			return false;
		}

		$notification = $dbhandler->get_row( $identifier, $id );
		if ( empty( $notification ) ) {
			return false;
		}

		$current_user_id = get_current_user_id();
		$recipient_id    = isset( $notification->rid ) ? absint( $notification->rid ) : 0;
		if ( $recipient_id !== $current_user_id && ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) ) {
			return false;
		}

		$return = $dbhandler->remove_row( $identifier, 'id', $id );

		return $return;
	}

	public function pm_get_user_unread_notification_count( $uid ) {
		if ( $uid ) {
			$dbhandler  = new PM_DBhandler();
			$identifier = 'NOTIFICATION';
			$where      = 1;
			$additional = ' rid = ' . $uid . ' AND status  in (1,4)';
			$unread     = $dbhandler->get_all_result( $identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true, $additional );
			if ( ! empty( $unread ) ) {
				$unread_notif = count( $unread );
			} else {
				$unread_notif = 0;
			}
			return $unread_notif;
		}
	}

	public function pm_mark_all_notification_as_read( $uid ) {
		if ( $uid ) {
			  $dbhandler  = new PM_DBhandler();
			  $identifier = 'NOTIFICATION';
			  $where      = 1;
			  $additional = ' rid = ' . $uid . ' AND status  in (1,4)';
			  $unread     = $dbhandler->get_all_result( $identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true, $additional );
			if ( ! empty( $unread ) ) {
				foreach ( $unread as $notification ) {
					$updated = $dbhandler->update_row( 'NOTIFICATION', 'id', $notification->id, array( 'status' => '2' ) );
				}
			}
		}
	}

	public function pm_generate_blog_post_owner_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta    = maybe_unserialize( $notif->meta );
			$post_id = $meta['post_id'];

			$permalink = get_permalink( $post_id );
		// $receivers = maybe_unserialize( $notif->receivers);
		// $current_user_group = $pmrequests->profile_magic_get_user_field_value($current_uid,'pm_group');
			$return = '';
		   // if((in_array($current_user_group, $receivers['group']) || in_array($current_uid, $receivers['uid']) ) && !in_array($current_uid, $receivers['exclude']))

			$notif_sender_id    = $notif->sid;
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
			$description = get_the_title( $post_id );
			// $sender_avatar = get_avatar($notif_sender_id, 50, '', false, array('class' => 'pm-user-profile'));
		   $default_featured_image = plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );

			$sender_avatar = get_the_post_thumbnail( $post_id, 50, array( 'class' => 'pm-user-profile' ) );
		if ( $sender_avatar == '' ) {
			$sender_avatar = '<img src="' . $default_featured_image . '" alt="' . $description . '" class="pm-user" />';
		}
			$sender_name = $pmrequests->pm_get_display_name( $notif_sender_id );

		   $return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'Blog Post Published', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">
                        
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
			 $return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
				$return .= '</div>
            </div>
        </div>';

		// $this->pm_change_notification_status($id,2);
			return $return;
	}

	public function pm_generate_wall_post_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta    = maybe_unserialize( $notif->meta );
			$post_id = $meta['post_id'];
			$gids    = maybe_unserialize( get_post_meta( $post_id, 'pm_accessible_groups', true ) );
		if ( ! empty( $gids ) && is_array( $gids ) ) {
			$gid = $gids[0];
		} else {
			$gids = $pmrequests->profile_magic_get_user_field_value( $notif->sid, 'pm_group' );
			$ugid = $pmrequests->pg_filter_users_group_ids( $gids );
			$gid  = $pmrequests->pg_get_primary_group_id( $ugid );
		}

			// $permalink = get_permalink($post_id);

			$permalink = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', get_permalink( $post_id ), $gid );
			//$permalink = add_query_arg( 'gid', $gid, $permalink );
		// $receivers = maybe_unserialize( $notif->receivers);
		// $current_user_group = $pmrequests->profile_magic_get_user_field_value($current_uid,'pm_group');
			$return = '';
		   // if((in_array($current_user_group, $receivers['group']) || in_array($current_uid, $receivers['uid']) ) && !in_array($current_uid, $receivers['exclude']))

			$notif_sender_id    = $notif->sid;
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
			$sender_avatar = get_avatar(
				$notif_sender_id,
				50,
				'',
				false,
				array(
					'class'         => 'pm-user-profile',
					'force_display' => true,
				)
			);
			$sender_name   = $pmrequests->pm_get_display_name( $notif_sender_id );
			$description   = $notif->description;

		   $return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Post on Group Wall', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
					else :
						$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
					endif;
					   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
					if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
						 $return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
					$return .= '</div>
                
            </div>
        </div>';

					// $this->pm_change_notification_status($id,2);
					return $return;

	}

	public function pm_generate_wall_post_owner_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$current_uid     = get_current_user_id();
			$dbhandler       = new PM_DBhandler();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta    = maybe_unserialize( $notif->meta );
			$post_id = $meta['post_id'];

			$permalink = get_permalink( $post_id );

			$gids = maybe_unserialize( get_post_meta( $post_id, 'pm_accessible_groups', true ) );
		if ( ! empty( $gids ) && is_array( $gids ) ) {
			$gid = $gids[0];
		} else {
			$gids = $pmrequests->profile_magic_get_user_field_value( $notif->sid, 'pm_group' );
			$ugid = $pmrequests->pg_filter_users_group_ids( $gids );
			$gid  = $pmrequests->pg_get_primary_group_id( $ugid );
		}

			$permalink = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', $permalink, $gid );
			//$permalink = add_query_arg( 'gid', $gid, $permalink );
		// $receivers = maybe_unserialize( $notif->receivers);
		// $current_user_group = $pmrequests->profile_magic_get_user_field_value($current_uid,'pm_group');
			$return = '';
		   // if((in_array($current_user_group, $receivers['group']) || in_array($current_uid, $receivers['uid']) ) && !in_array($current_uid, $receivers['exclude']))

			$notif_sender_id    = $notif->sid;
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
			$description = get_the_title( $post_id );
			// $sender_avatar = get_avatar($notif_sender_id, 50, '', false, array('class' => 'pm-user-profile'));
		   $default_featured_image = plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );

			$sender_avatar = get_the_post_thumbnail( $post_id, 50, array( 'class' => 'pm-user-profile' ) );
		if ( $sender_avatar == '' ) {
			$sender_avatar = '<img src="' . $default_featured_image . '" alt="' . $description . '" class="pm-user" />';
		}
			$sender_name = $pmrequests->pm_get_display_name( $notif_sender_id );

		   $return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'Wall Post Published', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">
                        
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
			 $return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
				$return .= '</div>
                
            </div>
        </div>';

		// $this->pm_change_notification_status($id,2);
			return $return;
	}

	public function pm_generate_group_join_owner_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta      = maybe_unserialize( $notif->meta );
			$gid       = $notif->sid;
			$permalink = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
			//$permalink = add_query_arg( 'gid', $gid, $permalink );
			$return    = '';
			$row       = $dbhandler->get_row( 'GROUPS', $gid );
		if ( empty( $row ) ) {
			return '';
		}
			$group_icon = $pmrequests->profile_magic_get_group_icon( $row );
			$status     = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}

			$hide_users  = $pmrequests->pm_get_hide_users_array();
			$meta_query  = array(
				'relation' => 'AND',
				array(
					'key'     => 'pm_group',
					'value'   => sprintf( ':"%s";', $gid ),
					'compare' => 'like',
				),
				array(
					'key'     => 'rm_user_status',
					'value'   => '0',
					'compare' => '=',
				),
			);
			$total_users = count( $dbhandler->pm_get_all_users( '', $meta_query, '', '', '', 'ASC', 'ID', $hide_users ) );
			if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
				$description = '<a href="' . $permalink . '">' . $row->group_name . '</a>';
			else :
				$description = $row->group_name;
			endif;
			$description .= '<p>' . $total_users . ' Members</p>';

			$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'Joined New Group', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $group_icon . '</div>
                    <div class="pm-notification-description pm-difl">
                        
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
			if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
				$return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
				$return .= '</div>
               
            </div>
        </div>';

			// $this->pm_change_notification_status($id,2);
			return $return;
	}

	public function pm_generate_group_remove_owner_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta       = maybe_unserialize( $notif->meta );
			$gid        = $notif->sid;
			$permalink  = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
			//$permalink  = add_query_arg( 'gid', $gid, $permalink );
			$return     = '';
			$row        = $dbhandler->get_row( 'GROUPS', $gid );
                        if(isset($row) && !empty($row))
                        {
			$group_icon = $pmrequests->profile_magic_get_group_icon( $row );
			$status     = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}

			$hide_users  = $pmrequests->pm_get_hide_users_array();
			$meta_query  = array(
				'relation' => 'AND',
				array(
					'key'     => 'pm_group',
					'value'   => sprintf( ':"%s";', $gid ),
					'compare' => 'like',
				),
				array(
					'key'     => 'rm_user_status',
					'value'   => '0',
					'compare' => '=',
				),
			);
			$user_query  = $dbhandler->pm_get_all_users_ajax( '', $meta_query, '', '', '', 'ASC', 'ID', $hide_users );
			$total_users = $user_query->get_total();
			if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
				$description = '<a href="' . $permalink . '">' . $row->group_name . '</a>';
			else :
				 $description = $row->group_name;
			endif;
			/* translators: %d: Number of group members. */
			$description .= '<p>' . sprintf( __( '%d Members', 'profilegrid-user-profiles-groups-and-communities' ), $total_users ) . '</p>';

			$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . sprintf( esc_html__( '%s ago', 'profilegrid-user-profiles-groups-and-communities' ), $notif_timestamp ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'Removed from Group', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $group_icon . '</div>
                    <div class="pm-notification-description pm-difl">
                        
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
            </div>
        </div>';
                        }
                        else
                        {
                            $return ='';
                        }

			// $this->pm_change_notification_status($id,2);
			return $return;
	}

	public function pm_generate_message_notice( $db_notification, $id ) {
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta               = maybe_unserialize( $notif->meta );
			$return             = '';
			$notif_sender_id    = $notif->sid;
			$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
			$sender_profile_url = $profile_url;
			$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
			$sender_avatar  = get_avatar(
				$notif_sender_id,
				50,
				'',
				false,
				array(
					'class'         => 'pm-user-profile',
					'force_display' => true,
				)
			);
			$sender_name    = $pmrequests->pm_get_display_name( $notif_sender_id );
			$description    = $notif->description;
			$my_profile_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
			$msg_url        = add_query_arg( 'rid', $notif_sender_id, $my_profile_url );
		   $return          = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Private Message', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '">' . $sender_name . '</a></div>';
					else :
						$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
					endif;
					   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
					if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
						 $return .= '<div class="pm-notification-buttons"><a href="' . $msg_url . '#pg-messages">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
					$return .= '</div>
               
            </div>
        </div>';

					// $this->pm_change_notification_status($id,2);
					return $return;

	}

	public function pm_generate_mycred_badge_earned_notice( $db_notification, $id ) {
		$return = '';
		if ( defined( 'myCRED_BADGE' ) ) :
			$pmrequests      = new PM_request();
			$current_uid     = get_current_user_id();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta = maybe_unserialize( $notif->meta );

			$notif_sender_id = $notif->sid;

			$status = $notif->status;
			if ( $status == 4 ) {
				$bold       = '<b>';
				$bold_close = '</b>';
			} else {
				$bold       = '';
				$bold_close = '';
			}
			$badge_id = $meta['badge'];
			$badge    = mycred_get_badge( $badge_id, $meta['new_level'] );

			$sender_avatar = $badge->level_image;
			$sender_name   = $badge->title;
			$description   = '';

			$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'Awarded New Badge', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">
                        <div class="pm-notification-user pm-color">' . $sender_name . '</div>
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl"><div class="pm-notification-buttons"><a></a></div></div>
            </div>
        </div>';

		   endif;
			return $return;

	}



	public function pm_generate_group_event_notice( $db_notification, $id ) {
		if ( ! class_exists( 'EventM_Factory' ) ) {
			  return '';
		}
			$pmrequests      = new PM_request();
			$dbhandler       = new PM_DBhandler();
			$notif           = $db_notification;
			$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

			$meta    = maybe_unserialize( $notif->meta );
			$post_id = $meta['post_id'];

			$setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );

			$event_service = EventM_Factory::get_service( 'EventM_Service' );
			$event         = $event_service->load_model_from_db( $post_id );

			// print_r($event);die;

			$global_settings = $setting_service->load_model_from_db();
			$permalink       = get_permalink( $global_settings->events_page );
			$permalink       = add_query_arg( 'event', $post_id, $permalink );
			$return          = '';

			$notif_sender_id = $notif->sid;

			$status = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
			$sender_avatar = wp_get_attachment_image( $event->cover_image_id, array( 50, 50 ), false, array( 'class' => 'pm-user-profile' ) );
			$sender_name   = $notif->description;
			$start_date    = em_showDateTime( $event->start_date, false, 'm/d/Y' );
			$end_date      = em_showDateTime( $event->end_date, false, 'm/d/Y' );
			$description   = '<b>Start Date</b> ' . $start_date . '<br /> <b>End Date</b> ' . $end_date;

			$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New Group Event', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">
                        <div class="pm-notification-user pm-color"><a href="' . $permalink . '">' . $sender_name . '</a></div>
                        <div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-buttons"><a href="' . $permalink . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
			 endif;
			$return .= '</div>
                
            </div>
        </div>';

			// $this->pm_change_notification_status($id,2);
			return $return;

	}

	public function pm_generate_user_status_notice( $db_notification, $id ) {
		if ( ! class_exists( 'Profilegrid_User_Profile_Status' ) ) {
			return '';
		}
			$dbhandler   = new PM_DBhandler();
		$pmrequests      = new PM_request();
		$current_uid     = get_current_user_id();
		$notif           = $db_notification;
		$notif_timestamp = human_time_diff( strtotime( $notif->timestamp ), time() );

		$meta               = maybe_unserialize( $notif->meta );
		$return             = '';
		$notif_sender_id    = $notif->sid;
		$profile_url        = $pmrequests->pm_get_user_profile_url( $notif_sender_id );
		$sender_profile_url = $profile_url;
		$status             = $notif->status;
		if ( $status == 4 ) {
			$bold       = '<b>';
			$bold_close = '</b>';
		} else {
			$bold       = '';
			$bold_close = '';
		}
		$sender_avatar = get_avatar(
			$notif_sender_id,
			50,
			'',
			false,
			array(
				'class'         => 'pm-user-profile',
				'force_display' => true,
			)
		);
		$sender_name   = $pmrequests->pm_get_display_name( $notif_sender_id );
		$description   = $notif->description;

		$return = '  
            <div id="notif_' . $id . '" class="pm-notification  pm-group-blog-post-notice ">
            <div class="pm-notification-date">' . $notif_timestamp . __( ' ago', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pm-notification-card pm-dbfl">
             <div onClick="pm_delete_notification(' . $id . ')" class="pm-notification-close"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></div>
                <div class="pm-notification-title pm-pad10 ">' . $bold . __( 'New User Profile Status Added', 'profilegrid-user-profiles-groups-and-communities' ) . $bold_close . '</div>
                <div class="pm-notification-description-wrap pm-dbfl pm-pad10  ">
                    <div class="pm-notification-profile-image pm-difl">' . $sender_avatar . '</div>
                    <div class="pm-notification-description pm-difl">';
		if ( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ) == '1' ) :
			$return .= '<div class="pm-notification-user pm-color"><a href="' . $sender_profile_url . '" target="_blank">' . $sender_name . '</a></div>';
				else :
					$return .= '<div class="pm-notification-user pm-color">' . $sender_name . '</div>';
					endif;
				   $return .= '<div class="pm-notification-user-activity">' . $description . '</div>
                    </div>
                </div>
                <div class="pm-notification-footer pm-dbfl">';
				if ( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ) == '1' ) :
					 $return .= '<div class="pm-notification-buttons"><a href="' . $sender_profile_url . '">' . __( 'View', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>';
				 endif;
				$return .= '</div>
         
            </div>
        </div>';

				// $this->pm_change_notification_status($id,2);
				return $return;

	}


}
