<?php

/**
 * The Gutenberg Block functionality of the plugin.
 *
 * @link       https://profilegrid.co
 * @since      1.0.0
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/block
 */

class Profile_Magic_Block {

	private $profile_magic;
	private $version;

	public function enqueue_scripts() {
		$index_js = 'index.js';
		wp_enqueue_script(
			'profilegrid-blocks-group-registration',
			plugins_url( $index_js, __FILE__ ),
			array(
				'wp-blocks',
				'wp-editor',
				'wp-i18n',
				'wp-element',
				'wp-components',
			),
            $this->version,
			true
		);
		$this->localize_editor_options();
		wp_localize_script( 'profilegrid-blocks-group-registration', 'pm_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	private function localize_editor_options() {
		$options = array( 'groups' => array(), 'users' => array(), 'pages' => array() );
		// Preserve the administrator-only access of the former option endpoints.
		if ( current_user_can( 'manage_options' ) ) {
			$options['groups'] = $this->pm_load_groups();
			$options['users'] = $this->pm_load_users();
			$options['pages'] = $this->pm_load_pages();
		}
		wp_localize_script( 'profilegrid-blocks-group-registration', 'pg_block_options', $options );
	}

	public function pm_default_group() {
		$dbhandler = new PM_DBhandler();
		$results   = $dbhandler->get_all_result( 'GROUPS', array( 'id' ), 1, 'results', 0, 1, null, false, '', 'ARRAY_A' );
		if($results)
		{
			return $results;
		}
		else
		{
			return array();
		}
	}

	public function pm_load_groups() {
		$return = array();
		$dbhandler = new PM_DBhandler();
		$results   = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ), 1, 'results', 0, false, null, false, '', 'ARRAY_A' );
		foreach ( $results as $res ) {
			if ( $res['id'] ) {
				$res['value'] = $res['id'];
			}
			unset( $res['id'] );

			if ( $res['group_name'] ) {
				$res['label'] = $res['group_name'];
			}
			unset( $res['group_name'] );
			$return[] = $res;
		}
		return $return;
	}

	public function pm_load_users() {
		$return = array();
		$dbhandler = new PM_DBhandler();
		$results   = get_users(array(
			'fields' => array('ID', 'user_login')
		));
		$results_array = array_map(function ($user) {
			return (array)$user;
		}, $results);

		foreach ( $results_array as $res ) {
			if ( $res['ID'] ) {
				$res['value'] = $res['ID'];
			}
			unset( $res['ID'] );
			unset( $res['id'] );

			if ( $res['user_login'] ) {
				$res['label'] = $res['user_login'];
			}
			unset( $res['user_login'] );
			$return[] = $res;
		}
		return $return;
	}
	
	public function pm_load_pages() {
		$return = array();
		$pages = get_pages();
		$temp = []; 
		foreach ( $pages as $page ) {
			$temp['label'] = $page->post_title;
			$temp['value'] = $page->ID;
			$return[] = $temp;
		}

		return $return;
	}

	public function profilegrid_add_block_categories ($categories) {
		$category_slug = wp_list_pluck ($categories, 'slug');

		$return = in_array ('profilegrid', $category_slug, true) ? $categories :
			array_merge (
				[
					[
						'slug' => 'profilegrid',
						'title' => 'ProfileGrid',
						'icon' => ''
					]
				],
				$categories
			);

		return $return;
	}

	public function profilegrid_block_register() {
		global $pagenow;

			// Skip block registration if Gutenberg is not enabled/merged.
			$group = $this->pm_default_group();
		if ( isset( $group[0]['id'] ) ) {
			$gid = $group[0]['id'];
		} else {
			$gid = '';
		}
		if ( ! function_exists( 'register_block_type' ) ) {
				return;
		}
			$dir = dirname( __FILE__ );

			$index_js = 'index.js';
		if ( $pagenow !== 'widgets.php' ) {
			wp_register_script(
				'profilegrid-blocks-group-registration',
				plugins_url( $index_js, __FILE__ ),
				array(
					'wp-blocks',
					'wp-editor',
					'wp-i18n',
					'wp-element',
					'wp-components',
				),
				filemtime( "$dir/$index_js" ),false
			);
		} else {
			wp_register_script(
				'profilegrid-blocks-group-registration',
				plugins_url( $index_js, __FILE__ ),
				array(
					'wp-blocks',
					'wp-edit-widgets',
					'wp-i18n',
					'wp-element',
					'wp-components',
				),
				filemtime( "$dir/$index_js" ),false
			);
		}
			$this->localize_editor_options();
			wp_localize_script( 'profilegrid-blocks-group-registration', 'pg_groups', array($this->pm_default_group(), get_current_user_id()) );

			wp_register_style( 'pg-gutenberg', plugins_url( 'profile-magic-gutenberg.css', __FILE__ ),array(), $this->version, 'all' );

			register_block_type(
				'profilegrid-blocks/group-registration',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_group_registration_block_handler' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
						'type' => array(
							'default' => '',
							'type'    => 'string',
						),

					),
				)
			);

			register_block_type(
				'profilegrid-blocks/login-form',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_login_form_block_handler' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/all-groups',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_all_groups_block_handler' ),
					'attributes'      => array(

						'view'             => array(
							'default' => 'grid',
							'type'    => 'string',
						),
						'sortby'           => array(
							'default' => 'newest',
							'type'    => 'string',
						),
						'sorting_dropdown' => array(
							'default' => true,
							'type'    => 'boolean',
						),
						'view_icon'        => array(
							'default' => true,
							'type'    => 'boolean',
						),
						'search_box'       => array(
							'default' => true,
							'type'    => 'boolean',
						),
						'include'          => array(
							'default' => '',
							'type'    => 'string',
						),
						'exclude'          => array(
							'default' => '',
							'type'    => 'string',
						),

					),
				)
			);
                        
            register_block_type(
				'profilegrid-blocks/all-users',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_all_users_block_handler' )
                )
			);

			register_block_type(
				'profilegrid-blocks/group-page',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_group_page_block_handler' ),
					'attributes'      => array(
						'gid' => array(
							'default' => $gid,
							'type'    => 'string',
						),

					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-blogs',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_user_blogs_block_handler' ),
					'attributes'      => array(
						'wpblog' => array(
							'default' => true,
							'type'    => 'boolean',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/blog-submission',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_blog_submission_block_handler' ),
					'attributes'      => array(
						'wpblog' => array(
							'default' => true,
							'type'    => 'boolean',
						),

					),
				)
			);

			register_block_type(
				'profilegrid-blocks/password-recovery-form',
				array (
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_password_recovery' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-profile',
				array (
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_user_profile' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/blogs',
				array (
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_blogs' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-description',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_group_description' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-first-name',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_user_first_name' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/messaging',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_messaging' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-member-count',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_group_member_count' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-last-name',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_user_last_name' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/notifications',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_notifications' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-manager-count',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_group_manager_count' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-email',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_user_email' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/friends',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_friends' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-managers',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_group_managers' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
						'sep'  => array(
							'default' => ",",
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-profile-image',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_user_profile_image' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-managers-list',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_group_managers_list' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-cover-image',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_block_user_cover_image' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/settings',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_settings' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-member-cards',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_group_member_cards' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
						'sortby'           => array(
							'default' => 'newest',
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-default-group',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_block_user_default_group' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/account',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_account' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-manager-cards',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_group_manager_cards' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-groups',
				array(
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'render_callback' => array( $this, 'profilegrid_blocks_user_groups' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
						'sep'           => array(
							'default' => ',',
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/change-password',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_change_password' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/user-group-badges',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_user_group_badges' ),
					'attributes'      => array(
						'uid'  => array(
							'default' => (string)get_current_user_id(),
							'type'    => 'string',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/unread-notification-count',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_unread_notification_count' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/privacy',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_privacy' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/unread-message-count',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_unread_message_count' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/delete-account',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_delete_account' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/about',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_about' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-cards',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_block_group_cards' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => [ $gid ],
							'type'    => 'array',
						),
					),
				)
			);

			register_block_type(
				'profilegrid-blocks/groups',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_blocks_groups' ),
				)
			);

			register_block_type(
				'profilegrid-blocks/group-name',
				array(
					'category' => 'ProfileGrid',
					'editor_script'   => 'profilegrid-blocks-group-registration',
					'editor_style'    => 'pg-gutenberg',
					'render_callback' => array( $this, 'profilegrid_block_group_name' ),
					'attributes'      => array(
						'gid'  => array(
							'default' => $gid,
							'type'    => 'string',
						),

						'link' => array(
							'default' => false,
							'type'    => 'boolean',
						),
					),
				)
			);
	}


	public function profilegrid_blocks_blog_submission_block_handler( $atts ) {
		 $public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_add_blog( $atts );
	}

	public function profilegrid_blocks_user_blogs_block_handler( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_get_template_html( 'profile-magic-user-blogs', $atts );

	}

	public function profilegrid_blocks_group_registration_block_handler( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_get_template_html( 'profile-magic-registration-form', $atts );
	}
        
	public function profilegrid_blocks_all_users_block_handler()
	{
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		$atts    = array();
		return $public->profile_magic_user_search( $atts );
	}

	public function profilegrid_blocks_login_form_block_handler() {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		$atts    = array();
		return $public->profile_magic_login_form( $atts );
	}

	public function profilegrid_blocks_all_groups_block_handler( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		wp_enqueue_style( 'pm_material_icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all' );
		if ( is_array( $atts ) ) {
			$atts['pg_source'] = 'block';
		} else {
			$atts = array( 'pg_source' => 'block' );
		}
		return $public->profile_magic_get_template_html( 'profile-magic-groups', $atts );

	}

	public function profilegrid_blocks_group_page_block_handler( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_get_template_html( 'profile-magic-group', $atts );
	}

	public function profilegrid_blocks_password_recovery( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_forget_password( '' );
	}

	public function profilegrid_blocks_user_profile( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_profile_view( $atts );
	}

	public function profilegrid_blocks_blogs( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_blog_area( $atts );
	}

	public function profilegrid_blocks_group_description ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_description( $atts );
	}

	public function profilegrid_blocks_user_first_name ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_first_name( $atts );
	}

	public function profilegrid_blocks_messaging ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_messaging_area( $atts );
	}

	public function profilegrid_group_member_count ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_member_count( $atts );
	}

	public function profilegrid_user_last_name ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_last_name( $atts );
	}
	public function profilegrid_blocks_notifications ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_notification_area( $atts );
	}
	
	public function profilegrid_group_manager_count ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_manager_count( $atts );
	}

	public function profilegrid_user_email ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_email( $atts );
	}

	public function profilegrid_blocks_friends ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_friends_area( $atts );
	}

	public function profilegrid_group_managers ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_manager_display_name( $atts );
	}

	public function profilegrid_user_profile_image ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_image( $atts );
	}

	public function profilegrid_blocks_group_managers_list ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_manager_display_name_in_list( $atts );
	}

	public function profilegrid_block_user_cover_image ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_cover_image( $atts );
	}

	public function profilegrid_blocks_settings ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_settings_area( $atts );
	}

	public function profilegrid_blocks_group_member_cards ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_members_cards( $atts );
	}

	public function profilegrid_block_user_default_group ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_default_group( $atts );
	}

	public function profilegrid_blocks_account ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_account_details( $atts );
	}

	public function profilegrid_blocks_group_manager_cards ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_group_managers_cards( $atts );
	}

	public function profilegrid_blocks_user_groups ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_all_groups( $atts );
	}

	public function profilegrid_blocks_change_password ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_change_password_tab( $atts );
	}

	public function profilegrid_user_group_badges ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_group_badges( $atts );
	}

	public function profilegrid_blocks_unread_notification_count ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_unread_notifications_count( $atts );
	}

	public function profilegrid_blocks_privacy ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_privacy_tab( $atts );
	}

	public function profilegrid_blocks_unread_message_count ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_unread_messages_count( $atts );
	}

	public function profilegrid_blocks_delete_account ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_delete_account_tab( $atts );
	}

	public function profilegrid_blocks_about ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_about_area( $atts );
	}

	public function profilegrid_block_group_cards ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		$str_atts = implode(',', $atts['gid']);
		$atts['gid'] = $str_atts;
		return $public->profile_magic_shortcode_group_cards( $atts );
	}

	public function profilegrid_blocks_groups ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		return $public->profile_magic_shortcode_user_groups_area( $atts );
	}

	public function profilegrid_block_group_name ( $atts ) {
		$public = new Profile_Magic_Public( $this->profile_magic, $this->version );
		if ((isset($atts['link'])) && $atts['link']){
			$pmrequests = new PM_request();
			$group_url  = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$atts['gid']);
			$html = "<a href=" . esc_url($group_url) . ">" . esc_html($public->profile_magic_shortcode_group_name( $atts )) . "</a>";
		}else{
			$html = $public->profile_magic_shortcode_group_name( $atts );
		}
		return $html;
	}
}
