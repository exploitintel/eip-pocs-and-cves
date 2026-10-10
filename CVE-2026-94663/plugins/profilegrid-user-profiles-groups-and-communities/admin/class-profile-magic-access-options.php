<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-profile-magic-request
 *
 * @author ProfileGrid
 */
class Profile_Magic_access_options {
	//put your code here


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
	 * @param      string    $profile_magic       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $profile_magic, $version ) {

		$this->profile_magic = $profile_magic;
		$this->version       = $version;

	}

	public function profile_magic_access_meta_box() {
		add_meta_box( 'profile-magic-access-metabox', __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), array( $this, 'pm_display_meta_box' ), 'page', 'side' );
		add_meta_box( 'profile-magic-access-metabox', __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), array( $this, 'pm_display_meta_box' ), 'post', 'side' );
                add_meta_box( 'profile-magic-access-metabox', __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), array( $this, 'pm_display_meta_box' ), 'profilegrid_blogs', 'side' );
                add_meta_box( 'profile-magic-access-metabox', __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), array( $this, 'pm_display_meta_box' ), 'pg_groupwall', 'side' );
	}

	public function pm_display_meta_box( $post ) {
		include 'partials/access-meta-box.php';
                wp_nonce_field( 'save_post_access_meta', 'pg_meta_box_nonce' );
	}

	public function profile_magic_save_access_meta( $post_id ) {
		if ( ! isset( $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$current_user_id = get_current_user_id();
		$is_author       = ( $current_user_id > 0 && (int) $post->post_author === (int) $current_user_id );
		$can_edit_post   = current_user_can( 'edit_post', $post_id );
		$is_admin        = current_user_can( 'manage_options' );

		if ( ! $can_edit_post && ! $is_author && ! $is_admin ) {
			return;
		}

		if ( ! isset( $_POST['pg_meta_box_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['pg_meta_box_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'save_post_access_meta' ) ) {
			return;
		}

		$post = wp_unslash( $_POST );
		if ( isset( $post['pm_enable_custom_access'] ) ) {
			update_post_meta( $post_id, 'pm_enable_custom_access', sanitize_text_field( $post['pm_enable_custom_access'] ) );
		} else {
			update_post_meta( $post_id, 'pm_enable_custom_access', 0 );
		}

		if ( isset( $post['pm_content_access'] ) ) {
			update_post_meta( $post_id, 'pm_content_access', sanitize_text_field( $post['pm_content_access'] ) );
		}

		if ( isset( $post['pm_content_access_group'] ) ) {
			update_post_meta( $post_id, 'pm_content_access_group', sanitize_text_field( $post['pm_content_access_group'] ) );
		}
	}

	public function profile_magic_check_content_access( $content ) {
        $id                 = get_the_ID();
                $dbhandler  = new PM_DBhandler();
                $author_id  =  get_the_author_meta( 'ID' );
                $pmfriends  = new PM_Friends_Functions();
                $pmrequests = new PM_request();

                $admin_note = get_post_meta( $id, 'pm_admin_note_content', true );
		if ( trim( $admin_note )!='' ) {
			$note          = '<div class="pg-admin-note">' . wp_kses_post( $admin_note ) . '</div>';
			$note_position = get_post_meta( $id, 'pm_admin_note_position', true );
			if ( $note_position=='top' ) {
				$content = $note . $content;
			} else {
				$content = $content . $note;
			}
		}

		if ( get_post_meta( $id, 'pm_enable_custom_access', true )==1 ) {
			if ( get_post_meta( $id, 'pm_content_access', true )==2 ) {
				if ( is_user_logged_in() ) {
									  $uid = get_current_user_id();
					if ( get_post_meta( $id, 'pm_content_access_group', true )!='all' ) {
						$gids                               = maybe_unserialize( get_user_meta( $uid, 'pm_group', true ) );
                                                $user_group = $pmrequests->pg_filter_users_group_ids( $gids );
						if ( !empty( $user_group ) ) {
							if ( !is_array( $user_group ) ) {
								$user_group =array( $user_group );
							}
						} else {
							$user_group = array();
						}
						if ( !in_array( get_post_meta( $id, 'pm_content_access_group', true ), $user_group ) ) {
							 $gid             = get_post_meta( $id, 'pm_content_access_group', true );
							 $groupinfo       = $dbhandler->get_row( 'GROUPS', $gid );
							 $group_page_url  = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
							 $group_page_link = $group_page_url;
							if ( isset( $groupinfo ) ) {
								$group_name = $groupinfo->group_name;
							} else {
								$group_name = '';}
							// translators: 1: group page URL, 2: group name.
							$err          = __( 'Only members of <a href="%1$s" target="_blank">%2$s</a> group can view this page.', 'profilegrid-user-profiles-groups-and-communities' );
								 $error   =  sprintf( $err, $group_page_link, $group_name );
								 $content = $this->profile_magic_content_access_message( $error );
						}
					}
				} else {
					$error   = $pmrequests->profile_magic_get_error_message( 'loginrequired', 'profilegrid-user-profiles-groups-and-communities' );
					$content = $this->profile_magic_content_access_message( $error );
				}
			}

			if ( get_post_meta( $id, 'pm_content_access', true )==3 ) {
				if ( is_user_logged_in() ) {
							$author_friends = $pmfriends->profile_magic_my_friends( $author_id );
							$uid            = get_current_user_id();
					if ( $uid!=$author_id ) {
						if ( !in_array( $uid, $author_friends ) ) {

							$author_profile_url = $pmrequests->pm_get_user_profile_url( $author_id );
							$author_name        = $pmrequests->pm_get_display_name( $author_id );
							// translators: 1: profile URL, 2: display name.
							$err                = __( 'Only those members who are friends of <a href="%1$s" target="_blank">%2$s</a> can view this page.', 'profilegrid-user-profiles-groups-and-communities' );
							$error              =  sprintf( $err, $author_profile_url, $author_name );
							$content            = $this->profile_magic_content_access_message( $error );
						}
					}
				} else {
					  $error   = $pmrequests->profile_magic_get_error_message( 'loginrequired', 'profilegrid-user-profiles-groups-and-communities' );
					  $content = $this->profile_magic_content_access_message( $error );
				}
			}

			if ( get_post_meta( $id, 'pm_content_access', true )==4 ) {
				if ( is_user_logged_in() ) {
							$uid = get_current_user_id();
					if ( $uid!=$author_id ) {

                                            $author_profile_url = $pmrequests->pm_get_user_profile_url( $author_id );
                                            $author_name        = $pmrequests->pm_get_display_name( $author_id );
                                            // translators: 1: profile URL, 2: display name.
                                            $err                = __( 'This content is restricted. Please contact <a href="%1$s" target="_blank">%2$s</a> for more details.', 'profilegrid-user-profiles-groups-and-communities' );
                                            $error              =  sprintf( $err, $author_profile_url, $author_name );
                                            $content            = $this->profile_magic_content_access_message( $error );

					}
				} else {
					  $error   = $pmrequests->profile_magic_get_error_message( 'loginrequired', 'profilegrid-user-profiles-groups-and-communities' );
					  $content = $this->profile_magic_content_access_message( $error );
				}
			}

			if ( get_post_meta( $id, 'pm_content_access', true )==5 ) {
				if ( is_user_logged_in() ) {
                                        $uid = get_current_user_id();
					$author_groups           = get_user_meta( $author_id, 'pm_group', true );
					$user_group              = get_user_meta( $uid, 'pm_group', true );
					if ( !empty( $user_group ) ) {
						if ( !is_array( $user_group ) ) {
							$user_group =array( $user_group );
						}
					} else {
						$user_group = array();
					}

					if ( !empty( $author_groups ) ) {
						if ( !is_array( $author_groups ) ) {
							$author_groups =array( $author_groups );
						}
					} else {
						$author_groups = array();
					}
                                        $is_group_member = array_intersect( $author_groups, $user_group );
					if ( empty( $is_group_member ) ) {
						$string = '';
						foreach ( $author_groups as $agid ) {
							$groupinfo       = $dbhandler->get_row( 'GROUPS', $agid );
							$group_page_url  = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $agid );
							$group_page_link = $group_page_url;
							if ( isset( $groupinfo ) ) {
								$group_name = $groupinfo->group_name;
							} else {
								$group_name = '';}
							$string = $string . '<a href="' . $group_page_link . '" target="_blank">' . $group_name . '</a>, ';
						}

									// translators: %s: list of group links.
									$err    = __( 'Only members of %s group(s) can view this page.', 'profilegrid-user-profiles-groups-and-communities' );
									$string = rtrim( $string, ', ' );
									$error  =  sprintf( $err, $string );
							 $content       = $this->profile_magic_content_access_message( $error );
					}
				} else {
					$error   = $pmrequests->profile_magic_get_error_message( 'loginrequired', 'profilegrid-user-profiles-groups-and-communities' );
					$content = $this->profile_magic_content_access_message( $error );
				}
			}
                        
                        if ( get_post_meta( $id, 'pm_content_access', true )==6 ) {
				if ( is_user_logged_in() ) {
                                                        $leader = false;
                                                        $uid = get_current_user_id();
                                                        $groups = $dbhandler->get_all_result('GROUPS');
                                                        if(!empty($groups))
                                                        {
                                                            foreach($groups as $group)
                                                            {
                                                                $is_leader = $pmrequests->pg_check_in_single_group_is_user_group_leader($uid, $group->id);
                                                                if($is_leader==true)
                                                                {
                                                                    $leader = true;
                                                                }
                                                            }

                                                        }
                                                        
                                                        if($leader==false)
                                                        {
                                                            $error              = __( 'Only group managers can view this page.', 'profilegrid-user-profiles-groups-and-communities' );
                                                            $content            = $this->profile_magic_content_access_message( $error );
                                                        }
                                                        
                                                        
                                                        
				} else {
                                        $error   = $pmrequests->profile_magic_get_error_message( 'loginrequired', 'profilegrid-user-profiles-groups-and-communities' );
                                        $content = $this->profile_magic_content_access_message( $error );
				}
			}
                        
                        
		}

		return $content;
	}

	public function profile_magic_content_access_message( $error ) {
        $content  = '<div class="pm-login-box-error"><span>';
		$content .= $error;
		$content .= '</span>
		</div>';
		return $content;
	}

	public function profile_magic_get_the_excerpt_filter_admin_note( $content ) {
		$id         = get_the_ID();
		$author_id  =  get_the_author_meta( 'ID' );
		$pmfriends  = new PM_Friends_Functions();
		$pmrequests = new PM_request();
		$admin_note = get_post_meta( $id, 'pm_admin_note_content', true );
		if ( trim( $admin_note )!='' ) {
			$content =  str_replace( $admin_note, '', $content );
		}

		return $content;
	}






	// class end
}
