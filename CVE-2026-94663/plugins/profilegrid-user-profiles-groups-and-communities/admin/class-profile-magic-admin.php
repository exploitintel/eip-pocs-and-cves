<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://profilegrid.co
 * @since      1.0.0
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/admin
 * @author     ProfileGrid <support@profilegrid.co>
 */

class Profile_Magic_Admin {

	public function pg_remove_legacy_password_copies() {
		if ( ! current_user_can( 'manage_options' ) || get_option( 'pg_password_meta_removed' ) ) {
			return;
		}
		// WordPress stores the actual password hashes in the users table.
		delete_metadata( 'user', 0, 'user_pass', '', true );
		update_option( 'pg_password_meta_removed', 1, false );
	}


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
	 * @param      string $profile_magic       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
        private $pm_theme_path;
        private $pm_theme_path_in_wptheme;
	public function __construct( $profile_magic, $version ) {

		$this->profile_magic                    = $profile_magic;
		$this->version                          = $version;
		$this->pm_theme_path                    = plugin_dir_path( __FILE__ ) . '../public/partials/themes/';
				$theme_path                     = get_stylesheet_directory();
				$override_template              = $theme_path . '/profilegrid-user-profiles-groups-and-communities/themes/';
				$this->pm_theme_path_in_wptheme = $override_template;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function pg_activation_redirect() {
    	if (get_option('pg_redirect_to_group_page', false)) {
            delete_option('pg_redirect_to_group_page');
            wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=pm_manage_groups' ) ) );
            exit;
        }
    }

	public function pg_init_all_groups_page_visibility_defaults() {
		$toggle_keys = array(
			'pm_groups_show_type_label',
			'pm_groups_show_icon',
			'pm_groups_show_member_count',
			'pm_groups_show_member_limit',
			'pm_groups_show_seats_left',
			'pm_groups_show_paid_badge',
			'pm_groups_show_group_manager',
			'pm_groups_show_action_button',
		);

		foreach ( $toggle_keys as $key ) {
			if ( get_option( $key, null ) === null ) {
				add_option( $key, '1' );
			}
		}
	}

    public function activate_sitewide_plugins( $blog_id ) {
		 // Switch to new website
		$dbhandler = new PM_DBhandler();
		switch_to_blog( $blog_id );
		// Activate
		foreach ( array_keys( get_site_option( 'active_sitewide_plugins' ) ) as $plugin ) {
			do_action( 'activate_' . $plugin, false );
			do_action( 'activate' . '_plugin', $plugin, false );
		}
		// Restore current website
		restore_current_blog();
	}

	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Profile_Magic_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Profile_Magic_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_user_logged_in() ) :
			global $wp_scripts;
			// tell WordPress to load jQuery UI tabs
			wp_enqueue_script( 'jquery-ui-tabs' );
			// get registered script object for jquery-ui
			wp_enqueue_style( 'wp-color-picker' );
			$screen = get_current_screen();
			if ( isset( $screen ) && 'admin_page_pm_profile_fields' !== $screen->base && 'admin_page_pm_profile_view' !== $screen->base ) {
                            wp_enqueue_style( 'jquery-ui-smoothness', plugin_dir_url( __FILE__ ) . 'css/smoothness-jquery-ui.min.css', array(), $this->version, 'all' );

			}
                        wp_enqueue_style( $this->profile_magic, plugin_dir_url( __FILE__ ) . 'css/profile-magic-admin.css', array(), $this->version, 'all' );
			wp_register_style( 'profilegrid-daterangepicker', plugin_dir_url( __FILE__ ) . 'css/daterangepicker.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'pm-font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'thickbox' );
			wp_register_style( 'pm_googleFonts', 'https://fonts.googleapis.com/css?family=Titillium+Web:400,600', array(), $this->version, 'all' );
			wp_enqueue_style( 'pm_googleFonts' );
                            wp_register_style( 'pm_material_icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all' );
                            wp_enqueue_style( 'pm_material_icon' );
			// endif;
				endif;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
				$pmrequests = new PM_request();
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Profile_Magic_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Profile_Magic_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_user_logged_in() ) :
			$dbhandler = new PM_DBhandler();
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_Script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'jquery-form' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_enqueue_media();
			wp_enqueue_script( $this->profile_magic, plugin_dir_url( __FILE__ ) . 'js/profile-magic-admin.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'profile-magic-admin-footer', plugin_dir_url( __FILE__ ) . 'js/profile-magic-admin-footer.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );
                        wp_register_script( 'profilegrid-moment', plugin_dir_url( __FILE__ ) . 'js/profilegrid-moment.js', array( 'jquery' ), $this->version, false );
                        wp_register_script( 'profilegrid-daterangepicker', plugin_dir_url( __FILE__ ) . 'js/daterangepicker.min.js', array( 'jquery' ), $this->version, false );
                        
                        wp_enqueue_script( 'profile-magic-license', plugin_dir_url( __FILE__ ) . 'js/profile-magic-license.js', array( 'jquery',), $this->version, true );
                        wp_localize_script(
							'profile-magic-license',
							'pg_admin_license_settings',
							array(
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'nonce'    => wp_create_nonce( 'pg-license-nonce' ),
							)
						);
                        
						wp_localize_script(
							$this->profile_magic,
							'pm_ajax_object',
							array(
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'nonce'    => wp_create_nonce( 'ajax-nonce' ),
							)
						);
			$error                              = array();
			$error['valid_email']               = __( 'Please enter a valid e-mail address.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_number']              = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_date']                = $pmrequests->pg_wp_date_format_error();
			$error['required_field']            = __( 'This is a required field.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['file_type']                 = __( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['short_password']            = __( 'Your password should be at least 7 characters long.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['pass_not_match']            = __( 'Password and confirm password do not match.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['user_exist']                = __( 'Sorry, username already exist.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['email_exist']               = __( 'Sorry, email already exist.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_facebook_url']        = __( 'Please enter a valid facebook url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_twitter_url']         = __( 'Please enter a valid x url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_google_url']          = __( 'Please enter a valid google+ url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_linked_in_url']       = __( 'Please enter a valid linkedin url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_youtube_url']         = __( 'Please enter a valid youtube url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_mixcloud_url']        = __( 'Please enter a valid Mixcloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_soundcloud_url']      = __( 'Please enter a valid SoundCloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_instagram_url']       = __( 'Please enter a valid instagram url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['atleast_one_field']         = __( 'Select at least one field.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['seprator_not_empty']        = __( 'Seperator field can not be empty.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['choose_image']              = __( 'Choose Image', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_image']               = __( 'This is not a valid image', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_group_name']          = __( 'Please enter a valid group name.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['group_manager_first']       = __( 'You must define a Group Manager first, before making a Group closed.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['delete']                    = __( 'Delete', 'profilegrid-user-profiles-groups-and-communities' );
			$error['success']                   = __( 'Success', 'profilegrid-user-profiles-groups-and-communities' );
			$error['failure']                   = __( 'Failure', 'profilegrid-user-profiles-groups-and-communities' );
			$error['select_group']              = __( 'please select a group', 'profilegrid-user-profiles-groups-and-communities' );
			$error['no_user_search']            = __( 'Sorry, no user with this username in this group.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['select_field_completeness'] = __( 'Select a Multiple Fields.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['completeness_no_fields']    = __( 'Sorry, there are no fields like this here.', 'profilegrid-user-profiles-groups-and-communities' );

			$error['change_group']   = __( 'You are changing the group of this user. All data associated with profile fields of old group will be hidden and the user will have to edit and fill profile fields associated with the new group. Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$allow_file_ext          = $dbhandler->get_global_option_value( 'pm_allow_file_types', 'jpg|jpeg|png|gif|webp|avif' );
			$allow_file_ext          = implode( '|', array_unique( array_filter( explode( '|', strtolower( $allow_file_ext . '|webp|avif' ) ) ) ) );
			$error['allow_file_ext'] = $allow_file_ext;
			wp_localize_script( $this->profile_magic, 'pm_error_object', $error );
                        wp_localize_script(
				$this->profile_magic,
				'pm_fields_object',
				array(
                                    'dateformat' => $pmrequests->pg_wp_date_format_php_to_js()
				)
			);
			$upload_requirements                                   = array();
			$upload_requirements['pg_profile_image_max_file_size'] = $dbhandler->get_global_option_value( 'pg_profile_image_max_file_size', '' );
			$upload_requirements['pg_cover_image_max_file_size']   = $dbhandler->get_global_option_value( 'pg_cover_image_max_file_size', '' );
			$upload_requirements['pg_profile_photo_minimum_width'] = $dbhandler->get_global_option_value( 'pg_profile_photo_minimum_width', 'DEFAULT' );
			$upload_requirements['pg_cover_photo_minimum_width']   = $dbhandler->get_global_option_value( 'pg_cover_photo_minimum_width', '800' );
			$upload_requirements['pg_image_quality']               = $dbhandler->get_global_option_value( 'pg_image_quality', '90' );
			if ( ! isset( $upload_requirements['pg_cover_photo_minimum_width'] ) || empty( $upload_requirements['pg_cover_photo_minimum_width'] ) ) {
					$upload_requirements['pg_cover_photo_minimum_width'] = '800';
			}
					// translators: %1$d: max bytes.
					$upload_requirements['error_max_profile_filesize'] = sprintf( __( 'Image size exceeds the maximum limit. Maximum allowed image size is %1$d byte.', 'profilegrid-user-profiles-groups-and-communities' ), $upload_requirements['pg_profile_image_max_file_size'] );
					// translators: 1: min width, 2: min height.
					$upload_requirements['error_min_profile_width']    = sprintf( __( 'Image dimensions are too small. Minimum size is %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $upload_requirements['pg_profile_photo_minimum_width'], $upload_requirements['pg_profile_photo_minimum_width'] );
					// translators: 1: min width, 2: min height.
					$upload_requirements['error_min_cover_width']      = sprintf( __( 'Image dimensions are too small. Minimum size is %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $upload_requirements['pg_cover_photo_minimum_width'],300 );
					wp_localize_script( $this->profile_magic, 'pm_upload_object', $upload_requirements );
					// endif;
				endif;

	}

	public function profile_magic_admin_menu_for_extensions() {
		add_submenu_page( 'pm_manage_groups', 'Extensions', 'Extensions', 'manage_options', 'pm_extensions', array( $this, 'pm_extensions' ) );
	}

	public function profile_magic_admin_menu() {
		add_menu_page( __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), __( 'ProfileGrid', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_manage_groups', array( $this, 'pm_manage_groups' ), 'dashicons-groups', 26 );
		add_submenu_page( 'pm_manage_groups', __( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_manage_groups', array( $this, 'pm_manage_groups' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'New Group', 'profilegrid-user-profiles-groups-and-communities' ), __( 'New Group', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_add_group', array( $this, 'pm_add_group' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Profile Fields', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Profile Fields', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_profile_fields', array( $this, 'pm_profile_fields' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'New Field', 'profilegrid-user-profiles-groups-and-communities' ), __( 'New Field', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_add_field', array( $this, 'pm_add_field' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'New Section', 'profilegrid-user-profiles-groups-and-communities' ), __( 'New Section', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_add_section', array( $this, 'pm_add_section' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Profile Templates', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Profile Templates', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_theme_settings', array( $this, 'pm_theme_settings' ) );
		add_submenu_page( 'pm_manage_groups', __( 'Members', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Members', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_user_manager', array( $this, 'pm_user_manager' ) );
		add_submenu_page( 'pm_manage_groups', __( 'Requests', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Requests', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_requests_manager', array( $this, 'pm_requests_manager' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Profile View', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Profile View', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_profile_view', array( $this, 'pm_profile_view' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Edit User', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Edit User', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_user_edit', array( $this, 'pm_user_edit' ) );
		add_submenu_page( 'pm_manage_groups', __( 'Email Templates', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Email Templates', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_email_templates', array( $this, 'pm_email_templates' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Add Email Template', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Add Email Template', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_add_email_template', array( $this, 'pm_add_email_template' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Email Preview', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Email Preview', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_email_preview', array( $this, 'pm_email_preview' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Analytics', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Analytics', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_analytics', array( $this, 'pm_analytics' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Membership', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Membership', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_membership', array( $this, 'pm_membership' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Membership Payments', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Payments', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_group_membership_payments', array( $this, 'pm_group_membership_payments' ) );
		add_submenu_page( 'pm_manage_groups', __( 'Shortcodes', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Shortcodes', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_shortcodes', array( $this, 'pm_shortcodes' ) );
                add_submenu_page( 'pm_manage_groups', __( 'Global Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Global Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_settings', array( $this, 'pm_settings' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'General Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'General Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_general_settings', array( $this, 'pm_general_settings' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Anti Spam Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Anti Spam Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_security_settings', array( $this, 'pm_security_settings' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'User Accounts Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'User Accounts Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_user_settings', array( $this, 'pm_user_settings' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Email Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Email Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_email_settings', array( $this, 'pm_email_settings' ) );
		add_submenu_page( 'pm_manage_groups_hide', __( 'Third Party Integrations', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Third Party Integrations', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_third_party_settings', array( $this, 'pm_third_party_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Payments Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Payments Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_payment_settings', array( $this, 'pm_payment_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Tools', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Tools', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_tools', array( $this, 'pm_tools' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Export Users', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Export Users', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_export_users', array( $this, 'pm_export_users' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Import Users', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Import Users', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_import_users', array( $this, 'pm_import_users' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Blog Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Blog Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_blog_settings', array( $this, 'pm_blog_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Message Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Message Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_message_settings', array( $this, 'pm_message_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Friends Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Friends Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_friend_settings', array( $this, 'pm_friend_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Upload Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Upload Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_upload_settings', array( $this, 'pm_upload_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'SEO Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'SEO Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_seo_settings', array( $this, 'pm_seo_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Export Options', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Export Options', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_export_options', array( $this, 'pm_export_options' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Import Options', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Import Options', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_import_options', array( $this, 'pm_import_options' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_content_restrictions', array( $this, 'pm_content_restrictions' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Woocommerce Extension', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Woocommerce Extension', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_woocommerce_extension', array( $this, 'pm_woocommerce_extension' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Advanced Woocommerce Extension', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Advanced Woocommerce Extension', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_woocommerce_advanced_extension', array( $this, 'pm_woocommerce_advanced_extension' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'RegistrationMagic Integrations', 'profilegrid-user-profiles-groups-and-communities' ), __( 'RegistrationMagic Integrations', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_rm_integration', array( $this, 'pm_rm_integration' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Profile Notifications', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Profile Notifications', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_profile_notification_settings', array( $this, 'pm_profile_notification_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Woocommerce Wishlist Extension', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Woocommerce Wishlist Extension', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_woocommerce_wishlist_extension', array( $this, 'pm_woocommerce_wishlist_extension' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Profile Tabs Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Profile Tabs Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_profile_tabs_settings', array( $this, 'pm_profile_tabs_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Private Profile Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Private Profile Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_private_profile_settings', array( $this, 'pm_private_profile_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Elements Visibility Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Elements Visibility Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_elements_visibility_settings', array( $this, 'pm_elements_visibility_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Performance Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Performance Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_performance_options', array( $this, 'pm_performance_options' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'Activation Wizard', 'profilegrid-user-profiles-groups-and-communities' ), __( 'Activation Wizard', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_activation_wizard', array( $this, 'pm_activation_wizard' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'All Users Listing Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'All Users Listing Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_users_listing_settings', array( $this, 'pm_users_listing_settings' ) );
                add_submenu_page( 'pm_manage_groups_hide', __( 'License Settings', 'profilegrid-user-profiles-groups-and-communities' ), __( 'License Settings', 'profilegrid-user-profiles-groups-and-communities' ), 'manage_options', 'pm_license_settings', array( $this, 'pm_license_settings' ) );
                
                
	}
        
        public function pm_license_settings()
        {
            include 'partials/pg-license.php';
        }

	public function pm_offers() {
		include 'partials/pg-offers.php';
	}

	public function pm_group_membership_payments() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();

		$status_filter    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$processor_filter = isset( $_GET['processor'] ) ? sanitize_text_field( wp_unslash( $_GET['processor'] ) ) : '';
		$group_filter     = isset( $_GET['group'] ) ? absint( $_GET['group'] ) : 0;
		$user_search      = isset( $_GET['user'] ) ? sanitize_text_field( wp_unslash( $_GET['user'] ) ) : '';
		$orderby          = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'posted_date';
		$order            = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'DESC';
		$order            = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
		$paged            = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page         = apply_filters( 'pg_group_membership_payments_per_page', 20 );

		$query_args = array(
			'paged'     => $paged,
			'per_page'  => $per_page,
			'status'    => $status_filter,
			'processor' => $processor_filter,
			'group'     => $group_filter,
			'search'    => $user_search,
			'orderby'   => $orderby,
			'order'     => $order,
		);

		$payments = $pmrequests->pg_get_group_membership_payments( $query_args );
		$items    = isset( $payments['items'] ) ? $payments['items'] : array();
		$total    = isset( $payments['total'] ) ? (int) $payments['total'] : 0;

		$total_pages = ( $per_page > 0 ) ? ceil( $total / $per_page ) : 1;

		$group_names = array();
		$user_names  = array();
		if ( ! empty( $items ) ) {
			$unique_groups = array_unique( array_map( 'intval', wp_list_pluck( $items, 'gid' ) ) );
			foreach ( $unique_groups as $gid ) {
				if ( $gid > 0 ) {
					$group_names[ $gid ] = $dbhandler->get_value( 'GROUPS', 'group_name', $gid, 'id' );
				}
			}

			$unique_users = array_unique( array_map( 'intval', wp_list_pluck( $items, 'uid' ) ) );
			if ( ! empty( $unique_users ) ) {
				$user_query = new WP_User_Query(
					array(
						'include' => $unique_users,
						'fields'  => array( 'ID', 'display_name', 'user_email' ),
					)
				);
				foreach ( $user_query->get_results() as $user ) {
					$user_names[ $user->ID ] = $user->display_name ? $user->display_name : $user->user_email;
				}
			}
		}

		$status_options    = $pmrequests->pg_get_payment_distinct_values( 'status' );
		$processor_options = $pmrequests->pg_get_payment_distinct_values( 'pay_processor' );
		$group_options     = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ), 1, 'results', 0, false, 'group_name', false );

		$build_sort_url = function( $column ) use ( $status_filter, $processor_filter, $group_filter, $user_search, $orderby, $order, $paged ) {
			$sort_order = ( $orderby === $column && 'ASC' === $order ) ? 'DESC' : 'ASC';
			$params     = array(
				'page'      => 'pm_group_membership_payments',
				'status'    => $status_filter,
				'processor' => $processor_filter,
				'group'     => $group_filter,
				'user'      => $user_search,
				'orderby'   => $column,
				'order'     => $sort_order,
				'paged'     => $paged,
			);
			return esc_url( add_query_arg( array_filter( $params, 'strlen' ), admin_url( 'admin.php' ) ) );
		};
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Membership Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pm_payment_settings' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
			<hr class="wp-header-end">
			<form method="get" class="pg-filter-form">
				<input type="hidden" name="page" value="pm_group_membership_payments" />
				<div class="tablenav top">
					<div class="alignleft actions">
						<label for="pg-payment-status" class="screen-reader-text"><?php esc_html_e( 'Filter by status', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						<select name="status" id="pg-payment-status">
							<option value=""><?php esc_html_e( 'All Statuses', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
							<?php foreach ( $status_options as $status_option ) : ?>
								<option value="<?php echo esc_attr( $status_option ); ?>" <?php selected( $status_filter, $status_option ); ?>><?php echo esc_html( ucfirst( $status_option ) ); ?></option>
							<?php endforeach; ?>
						</select>

						<label for="pg-payment-processor" class="screen-reader-text"><?php esc_html_e( 'Filter by processor', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						<select name="processor" id="pg-payment-processor">
							<option value=""><?php esc_html_e( 'All Processors', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
							<?php foreach ( $processor_options as $processor_option ) : ?>
								<option value="<?php echo esc_attr( $processor_option ); ?>" <?php selected( $processor_filter, $processor_option ); ?>><?php echo esc_html( $pmrequests->pg_get_payment_processor_label( $processor_option ) ); ?></option>
							<?php endforeach; ?>
						</select>

						<label for="pg-payment-group" class="screen-reader-text"><?php esc_html_e( 'Filter by group', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						<select name="group" id="pg-payment-group">
							<option value="0"><?php esc_html_e( 'All Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
							<?php if ( ! empty( $group_options ) ) : ?>
								<?php foreach ( $group_options as $group ) : ?>
									<option value="<?php echo esc_attr( $group->id ); ?>" <?php selected( $group_filter, (int) $group->id ); ?>><?php echo esc_html( $group->group_name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>

						<label for="pg-payment-user" class="screen-reader-text"><?php esc_html_e( 'Filter by user', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						<input type="search" name="user" id="pg-payment-user" value="<?php echo esc_attr( $user_search ); ?>" placeholder="<?php esc_attr_e( 'Search user...', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />

						<?php submit_button( __( 'Filter', 'profilegrid-user-profiles-groups-and-communities' ), 'secondary', '', false ); ?>
					</div>
					<div class="tablenav-pages">
						<?php
						$pagination_links = paginate_links(
							array(
								'base'      => esc_url(
									add_query_arg(
										array(
											'page'      => 'pm_group_membership_payments',
											'status'    => $status_filter,
											'processor' => $processor_filter,
											'group'     => $group_filter,
											'user'      => $user_search,
											'orderby'   => $orderby,
											'order'     => $order,
											'paged'     => '%#%',
										),
										admin_url( 'admin.php' )
									)
								),
								'format'    => '',
								'current'   => $paged,
								'total'     => max( 1, $total_pages ),
								'add_args'  => false,
								'prev_text' => __( '&laquo;', 'profilegrid-user-profiles-groups-and-communities' ),
								'next_text' => __( '&raquo;', 'profilegrid-user-profiles-groups-and-communities' ),
							)
						);
						if ( ! empty( $pagination_links ) ) {
							echo wp_kses_post( $pagination_links );
						}
						?>
					</div>
				</div>
			</form>

			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'user' ) ); ?>"><?php esc_html_e( 'Member', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'group' ) ); ?>"><?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'amount' ) ); ?>"><?php esc_html_e( 'Amount', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'processor' ) ); ?>"><?php esc_html_e( 'Gateway', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'status' ) ); ?>"><?php esc_html_e( 'Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
						<th scope="col"><a href="<?php echo esc_url( $build_sort_url( 'posted_date' ) ); ?>"><?php esc_html_e( 'Date', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $items ) ) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'No payments found.', 'profilegrid-user-profiles-groups-and-communities' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $items as $item ) : ?>
							<?php
							$user_label  = isset( $user_names[ $item->uid ] ) ? $user_names[ $item->uid ] : sprintf( __( 'User #%d', 'profilegrid-user-profiles-groups-and-communities' ), $item->uid );
							$group_label = isset( $group_names[ $item->gid ] ) ? $group_names[ $item->gid ] : sprintf( __( 'Group #%d', 'profilegrid-user-profiles-groups-and-communities' ), $item->gid );
							$group_amount = $pmrequests->profile_magic_check_paid_group( (int) $item->gid );
							if ( is_numeric( $group_amount ) ) {
								$amount = number_format_i18n( (float) $group_amount, 2 );
							} elseif ( is_numeric( $item->amount ) ) {
								$amount = number_format_i18n( (float) $item->amount, 2 );
							} else {
								$amount = $item->amount;
							}
							$currency    = ! empty( $item->currency ) ? strtoupper( $item->currency ) : '';
							$date     = '';
							$raw_date = isset( $item->posted_date ) ? $item->posted_date : '';
							if ( ! empty( $raw_date ) && $raw_date !== '0000-00-00' && $raw_date !== '0000-00-00 00:00:00' ) {
								if ( is_numeric( $raw_date ) ) {
									$timestamp = (int) $raw_date;
									if ( $timestamp > 0 ) {
										$date = wp_date( get_option( 'date_format' ), $timestamp, wp_timezone() );
									}
								} else {
									$timestamp = strtotime( $raw_date );
									if ( $timestamp && $timestamp > 0 ) {
										$date = mysql2date( get_option( 'date_format' ), $raw_date, true );
									}
								}
							}
							if ( empty( $date ) ) {
								$timestamp = 0;
								$raw_log   = isset( $item->log ) ? $item->log : '';
								if ( ! empty( $raw_log ) ) {
									$log_data = maybe_unserialize( $raw_log );
									if ( is_string( $log_data ) ) {
										$decoded = json_decode( $log_data, true );
										if ( json_last_error() === JSON_ERROR_NONE ) {
											$log_data = $decoded;
										}
									}
									if ( is_object( $log_data ) ) {
										$log_data = (array) $log_data;
									}
									if ( is_array( $log_data ) ) {
										$date_keys = array( 'payment_date', 'payment_date_time', 'create_time', 'created', 'created_at', 'time_created', 'timestamp', 'date', 'payment_time' );
										foreach ( $date_keys as $date_key ) {
											if ( ! empty( $log_data[ $date_key ] ) ) {
												$candidate = $log_data[ $date_key ];
												if ( is_numeric( $candidate ) ) {
													$timestamp = (int) $candidate;
													if ( $timestamp > 0 ) {
														break;
													}
												}
												$candidate_timestamp = strtotime( (string) $candidate );
												if ( $candidate_timestamp ) {
													$timestamp = $candidate_timestamp;
													break;
												}
											}
										}
									}
								}
								if ( $timestamp > 0 ) {
									$date = wp_date( get_option( 'date_format' ), $timestamp, wp_timezone() );
								}
							}
							if ( empty( $date ) ) {
								$txn_id = isset( $item->txn_id ) ? $item->txn_id : '';
								if ( is_string( $txn_id ) && preg_match( '/_(\\d{9,})$/', $txn_id, $matches ) ) {
									$timestamp = (int) $matches[1];
									if ( $timestamp > 0 ) {
										$date = wp_date( get_option( 'date_format' ), $timestamp, wp_timezone() );
									}
								}
							}
							$date = $date ? $date : __( 'N/A', 'profilegrid-user-profiles-groups-and-communities' );
							?>
							<tr>
								<td><?php echo esc_html( $user_label ); ?></td>
								<td><?php echo esc_html( $group_label ); ?></td>
								<td><?php echo esc_html( trim( "{$currency} {$amount}" ) ); ?></td>
								<td><?php echo esc_html( $pmrequests->pg_get_payment_processor_label( $item->pay_processor ) ); ?></td>
								<td>
									<span class="pg-status-label pg-status-<?php echo esc_attr( sanitize_html_class( $item->status ) ); ?>">
										<?php echo esc_html( ucfirst( $item->status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $date ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function pm_activation_wizard() {

			include 'partials/pg-activation-wizard.php';

	}

	public function pg_fetch_offers() {
		 $url          = 'https://profilegrid.co/pg-offers.json';
				 $html = wp_remote_get( $url );
		$json          = json_decode( $html['body'] );
		if ( ! empty( $json ) ) {
			foreach ( $json as $offer ) {
				?>
					<div class="pg-offer">
						<div class="pg-offer-wrap">
					<?php if ( isset( $offer->title ) ) : ?>
							<span class="pg-offer-title"><strong><?php echo wp_kses_post( $offer->title ); ?></strong></span>
						<?php endif; ?>
					 <?php if ( isset( $offer->offer ) ) : ?>
						<span class="pg-offer-desc"><?php echo wp_kses_post( $offer->offer ); ?></span>
						<?php endif; ?>
					<?php if ( isset( $offer->code ) ) : ?>
						<span class="pg-offer-code"><strong><?php echo wp_kses_post( $offer->code ); ?></strong></span>
						<?php endif; ?>
						</div>
					<?php if ( isset( $offer->link ) ) : ?>
                                        <div class="pg-buy-btn"><a href="<?php echo esc_url( $offer->link ); ?>">
                                                <?php
                                                if ( isset( $offer->link_title ) && ! empty( $offer->link_title ) ) {
                                                        echo wp_kses_post( $offer->link_title );

                                                } else {
                                                        esc_html_e( 'Buy Now', 'profilegrid-user-profiles-groups-and-communities' );

                                                }
                                                ?>
                                        </a>
                                        </div>
						<?php endif; ?>
					</div>    
					<?php
			}
		} else {
			?>
                            <div class="pg-no-offer"><?php esc_html_e( 'Sorry, no offers available right now.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                            <?php
		}

		die;
	}

	public function pm_performance_options() {
		include 'partials/performance-setting.php';
	}

	public function pm_users_listing_settings() {
		include 'partials/pm-users-listing-setting.php';
	}

	public function pm_elements_visibility_settings() {
		 include 'partials/elements-visibility-setting.php';
	}

	public function pm_profile_tabs_settings() {
		include 'partials/profile-tab-setting.php';
	}

	public function pm_premium_options() {
		include 'partials/premium-features.php';
	}

	public function pm_premium_options_profile_labels() {
		include 'partials/premium-features-profile-labels.php';
	}

	public function pm_premium_options_custom_tabs() {
		include 'partials/premium-features-custom-tabs.php';
	}

	public function pm_premium_options_custom_product_tabs() {
		include 'partials/premium-features-custom-product-tabs.php';
	}

	public function pm_premium_options_mailchimp() {
		include 'partials/premium-features-mailchimp.php';
	}

	public function pm_premium_options_mailpoet() {
		include 'partials/premium-features-mailpoet.php';
	}

	public function pm_private_profile_settings() {
		 include 'partials/private-profile-setting.php';
	}

	public function pm_theme_settings() {
		include 'partials/theme-setting.php';
	}

	public function pm_woocommerce_wishlist_extension() {
		include 'partials/woocommerce-wishlist-extension.php';
	}


	public function pm_requests_manager() {
		 include 'partials/pm-membership-requests.php';
                 $this->pg_get_footer_banner();
	}

	public function pm_message_settings() {
		 include 'partials/message-settings.php';
	}

	public function pm_profile_notification_settings() {
		include 'partials/profile-notification-settings.php';
	}


	public function pm_rm_integration() {
		if ( class_exists( 'Profile_Magic' ) && class_exists( 'Registration_Magic' ) ) {
				include 'partials/rmagic_settings.php';
		} else {
			include 'partials/rmagic_banner_settings.php';
		}
	}

	public function pm_woocommerce_extension() {
		include 'partials/woocommerce-extension.php';
	}

	public function pm_woocommerce_advanced_extension() {
		include 'partials/woocommerce-advanced-extension.php';
	}

	public function pm_content_restrictions() {
		 include 'partials/content-restrictions.php';
	}

	public function pm_import_options() {
		include 'partials/import-options.php';
	}

	public function pm_export_options() {
		include 'partials/export-options.php';
	}

	public function pm_seo_settings() {
		 include 'partials/seo-settings.php';
	}

	public function pm_upload_settings() {
		include 'partials/upload-settings.php';
	}

	public function pm_extensions() {
		include 'partials/pm_extensions_new.php';
	}

	public function pm_friend_settings() {
		include 'partials/friends-settings.php';
	}

	public function pm_tools() {
			include 'partials/pm-tools.php';
	}

	public function pm_payment_settings() {
		 include 'partials/payment-settings.php';
	}

	public function pm_blog_settings() {
		include 'partials/blog-settings.php';
	}

	public function pm_export_users() {
			 include 'partials/pm-export-users.php';
	}

	public function pm_import_users() {
			 include 'partials/pm-import-users.php';
	}

	public function pm_profile_magic_add_group_option( $gid, $group_options ) {
		 include 'partials/profile-magic-group-option.php';
	}

	public function pm_profile_magic_premium_group_option( $gid, $group_options ) {
		 include 'partials/premium-group-options.php';
	}

	public function pm_profile_magic_add_option_setting_page() {
		include 'partials/profile-magic-paypal-admin-display.php';
	}

	public function pm_add_email_template() {
		include 'partials/email-template.php';
				$this->pg_get_footer_banner();
	}

	public function pm_shortcodes() {
		include 'partials/shortcode.php';
		$this->pg_get_footer_banner();
	}
	public function pm_email_templates() {
		include 'partials/email-templates-list.php';
				$this->pg_get_footer_banner();
	}

	public function pm_email_preview() {
		include 'partials/email-preview.php';
	}

	public function pm_template_preview_button() {
		echo '<a href="admin.php?page=pm_email_preview&TB_iframe=false&width=600&height=550inlineId=wpbody" class="thickbox" onClick="return preview()">Preview</a>';

	}

	public function pm_manage_groups() {
		include 'partials/manage-groups.php';
	}

	public function pg_get_eventprime_promo_data() {
		$rival_plugins = array(
			'tec' => array(
				'label'     => 'The Events Calendar',
				'plugins'   => array(
					'the-events-calendar/the-events-calendar.php',
				),
				'classes'   => array(
					'Tribe__Events__Main',
				),
				'constants' => array(),
			),
			'wp_event_manager' => array(
				'label'     => 'WP Event Manager',
				'plugins'   => array(
					'wp-event-manager/wp-event-manager.php',
				),
				'classes'   => array(
					'WP_Event_Manager',
				),
				'constants' => array(),
			),
			'modern_events_calendar' => array(
				'label'     => 'Modern Events Calendar',
				'plugins'   => array(
					'modern-events-calendar-lite/modern-events-calendar-lite.php',
					'modern-events-calendar/mec-init.php',
				),
				'classes'   => array(
					'MEC',
				),
				'constants' => array(
					'MEC_VERSION',
				),
			),
			'eventin' => array(
				'label'     => 'Eventin',
				'plugins'   => array(
					'wp-event-solution/eventin.php',
				),
				'classes'   => array(
					'Wpeventin',
				),
				'constants' => array(
					'EVENTIN_VERSION',
				),
			),
			'amelia' => array(
				'label'     => 'Amelia',
				'plugins'   => array(
					'ameliabooking/ameliabooking.php',
				),
				'classes'   => array(
					'AmeliaBooking\\Plugin',
				),
				'constants' => array(
					'AMELIA_VERSION',
				),
			),
			'events_manager' => array(
				'label'     => 'Events Manager',
				'plugins'   => array(
					'events-manager/events-manager.php',
				),
				'classes'   => array(
					'EM_Object',
				),
				'constants' => array(
					'EM_VERSION',
				),
			),
		);

		foreach ( $rival_plugins as $plugin_key => $plugin_data ) {
			if ( $this->pg_is_eventprime_rival_active( $plugin_data ) ) {
				return array(
					'key'   => $plugin_key,
					'label' => $plugin_data['label'],
					'url'   => $this->pg_get_eventprime_promo_url( $plugin_key ),
				);
			}
		}

		return false;
	}

	public function pg_render_eventprime_promo() {
		$dbhandler  = new PM_DBhandler();
		$promo_data = $this->pg_get_eventprime_promo_data();
		$notice_id  = 'pg_eventprime_promo_notice';

		if ( '1' === $dbhandler->get_global_option_value( $notice_id, '0' ) ) {
			return;
		}

		if ( empty( $promo_data['url'] ) || empty( $promo_data['label'] ) ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible pg-dismissible" id="<?php echo esc_attr( $notice_id ); ?>">
			<p>
				<?php $is_tec = 'tec' === $promo_data['key']; ?>
				<strong><?php echo esc_html( $is_tec ? __( 'Using The Events Calendar?', 'profilegrid-user-profiles-groups-and-communities' ) : __( 'Need an event plugin alternative?', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></strong>
				<?php echo ' ' . esc_html( $is_tec ? __( 'See how EventPrime handles events, registration, and paid event workflows inside WordPress.', 'profilegrid-user-profiles-groups-and-communities' ) : __( 'Explore EventPrime for WordPress-native event management and paid event workflows.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?>
				<a href="<?php echo esc_url( $promo_data['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $is_tec ? __( 'Discover Why Users Switch to EventPrime', 'profilegrid-user-profiles-groups-and-communities' ) : __( 'Explore EventPrime', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></a>
			</p>
		</div>
		<?php
	}

	public function pg_eventprime_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! $this->pg_is_eventprime_notice_screen() ) {
			return;
		}

		$this->pg_render_eventprime_promo();
	}

	private function pg_get_eventprime_promo_url( $plugin_key ) {
		$homepage_url = 'https://theeventprime.com?utm_source=profilegrid_plugin&utm_medium=admin_banner&utm_campaign=eventprime_promo';
		$tec_url      = 'https://theeventprime.com/the-events-calendar-alternative?utm_source=profilegrid_plugin&utm_medium=admin_banner&utm_campaign=eventprime_tec_promo';

		if ( 'tec' === $plugin_key ) {
			return apply_filters( 'profilegrid_eventprime_tec_promo_url', $tec_url, $plugin_key );
		}

		return apply_filters( 'profilegrid_eventprime_promo_url', $homepage_url, $plugin_key );
	}

	private function pg_is_eventprime_rival_active( $plugin_data ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! empty( $plugin_data['plugins'] ) ) {
			foreach ( $plugin_data['plugins'] as $plugin_file ) {
				if ( is_plugin_active( $plugin_file ) ) {
					return true;
				}
			}
		}

		if ( ! empty( $plugin_data['classes'] ) ) {
			foreach ( $plugin_data['classes'] as $class_name ) {
				if ( class_exists( $class_name ) ) {
					return true;
				}
			}
		}

		if ( ! empty( $plugin_data['constants'] ) ) {
			foreach ( $plugin_data['constants'] as $constant_name ) {
				if ( defined( $constant_name ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function pg_is_eventprime_notice_screen() {
		return 'pm_manage_groups' === filter_input( INPUT_GET, 'page' );
	}

	public function pm_add_group() {
		include 'partials/add-group-tabview.php';
	}

	public function pm_add_field() {
		include 'partials/add-field.php';
	}

	public function pm_add_section() {
		include 'partials/add-section.php';
	}

	public function pm_profile_fields() {
		include 'partials/manage-fields.php';
	}

	public function pm_user_manager() {
		 include 'partials/user-manager.php';
				$this->pg_get_footer_banner();
	}

	public function pm_profile_view() {
		 include 'partials/user-profile.php';
	}

	public function pm_third_party_settings() {
		 include 'partials/thirdparty-settings.php';
	}

	public function pm_email_settings() {
		include 'partials/email-settings.php';
	}

	public function pm_user_settings() {
		include 'partials/user-settings.php';
	}

	public function pm_general_settings() {
		 include 'partials/general-settings.php';

	}

	public function pm_security_settings() {
		include 'partials/security-settings.php';
	}

	public function pm_settings() {
			include 'partials/global-settings.php';
			$this->pg_get_footer_banner();
	}




	public function profile_magic_set_field_order() {
		$this->pg_validate_reorder_ajax_request();
		include 'partials/set-fields-order.php';
		die;
	}

	public function profile_magic_set_group_order() {
		$this->pg_validate_reorder_ajax_request();
		include 'partials/set-groups-order.php';
		die;
	}

	public function profile_magic_set_group_items() {
		$this->pg_validate_reorder_ajax_request();
		include 'partials/set-groups-order.php';
		die;
	}

	private function pg_validate_reorder_ajax_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), 403 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ), 403 );
		}
	}

	public function profile_magic_set_section_order() {
                if ( !current_user_can('manage_options') ) {
                    die;
                }
                if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'pm_section_reorder_nonce')) {
                    // Nonce is valid, process the form
                
                    $dbhandler = new PM_DBhandler();
                    $textdomain         = $this->profile_magic;
                    $path               = plugin_dir_url( __FILE__ );
                    $identifier         = 'SECTION';
                    $list_order         = filter_input( INPUT_POST, 'list_order' );
                    if ( isset( $list_order ) ) {
                            $list = explode( ',', $list_order );
                            $i    = 1;
                            foreach ( $list as $id ) {
                                    $id = absint( $id );
                                    if ( 0 === $id ) {
                                            continue;
                                    }
                                    $dbhandler->update_row( $identifier, 'id', $id, array( 'ordering' => $i ), array( '%d' ), '%d' );

                                    $i++;
                            }
                    }
                } else {
                    die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
                }
		die;
	}

	public function profile_magic_section_dropdown() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		$gid       = absint( filter_input( INPUT_POST, 'gid' ) );
		$dbhandler = new PM_DBhandler();
		$sections  = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid' => $gid ) );
		foreach ( $sections as $section ) {
			?>
			<option value="<?php echo esc_attr( $section->id ); ?>" 
									  <?php
										if ( ! empty( $row ) ) {
											selected( $row->associate_section, $section->id );}
										?>
			><?php echo esc_html( $section->section_name ); ?></option>
			<?php
		}
		die;
	}


	public function profile_magic_check_smtp_connection() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                
                if ( current_user_can( 'manage_options' ) ) 
                {
			$dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
                        $identifier     = 'SETTINGS';
                        $exclude        = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
                        $post           = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
                        if ( $post != false ) 
                        {
                            if ( isset( $post['pm_smtp_password'] ) && $post['pm_smtp_password'] != '' ) {
                                                            $post['pm_smtp_password'] = $post['pm_smtp_password'];
                            } else {
                                    unset( $post['pm_smtp_password'] );
                            }
                            foreach ( $post as $key => $value ) {
                                    $dbhandler->update_global_option_value( $key, $value );
                            }
                        }
                        $dbhandler->update_global_option_value( 'pm_enable_smtp', 1 );
                        $to = $dbhandler->get_global_option_value( 'pm_smtp_test_email_address' );
                        $from_email_address = $pmrequests->profile_magic_get_from_email();
                        $headers            = "MIME-Version: 1.0\r\n";
                        $headers           .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers           .= 'From:' . $from_email_address . "\r\n";
                        echo esc_html( wp_mail( $to, 'Test SMTP Connection', 'Test', $headers ) );
                }
                die;

	}



	public function pm_fields_list_for_email() {
		$dbhandler = new PM_DBhandler();
		$exclude   = "and field_type not in('file','user_avatar','heading','paragraph','confirm_pass','user_pass','divider','spacing','birth_date','user_email')";
		$groups    = $dbhandler->get_all_result( 'GROUPS' );
		echo '<select name="pm_field_list" class="pm_field_list pg-insert-field-in-email" onchange="pm_insert_field_in_email(this.value)">';
		echo '<option>' . esc_html__( 'Select A Field', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
		echo '<optgroup label="' . esc_attr__( 'Common Fields', 'profilegrid-user-profiles-groups-and-communities' ) . '" >';
		echo '<option value="{{user_login}}">' . esc_html__( 'User Name', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
		echo '<option value="{{user_pass}}">' . esc_html__( 'User Password', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
		echo '<option value="{{display_name}}">' . esc_html__( 'Display Name', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
                echo '<option value="{{user_email}}">' . esc_html__( 'Email', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
		echo '</optgroup>';
		if ( isset( $groups ) ) :
			foreach ( $groups as $group ) {
				$fields = $dbhandler->get_all_result( 'FIELDS', '*', array( 'associate_group' => $group->id ), 'results', 0, false, 'ordering', false, $exclude );
				if ( isset( $fields ) ) :
                                echo '<optgroup label="' . esc_attr( $group->group_name ) . '" >';
				
					foreach ( $fields as $field ) {
						echo '<option value="{{' . esc_attr( $field->field_key ) . '}}">' . esc_html( $field->field_name ) . '</option>';
					}
				

				echo '</optgroup>';
                                endif;
			}
			echo '<optgroup label="' . esc_html__( 'Other Fields', 'profilegrid-user-profiles-groups-and-communities' ) . '" >';
			echo '<option value="{{post_name}}">' . esc_html__( 'Post Name', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
			echo '<option value="{{edit_post_link}}">' . esc_html__( 'Review Post Link', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
			echo '<option value="{{post_link}}">' . esc_html__( 'Post Link', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
			echo '<option value="{{group_name}}">' . esc_html__( 'User Group Name', 'profilegrid-user-profiles-groups-and-communities' ) . '</option>';
						echo '</optgroup>';
			echo '</select>';
		endif;

	}

	public function profile_magic_show_user_fields( $user ) {
			   $dbhandler                       = new PM_DBhandler();
				$pg_profile_image_max_file_size = $dbhandler->get_global_option_value( 'pg_profile_image_max_file_size', '' );
				$pg_cover_image_max_file_size   = $dbhandler->get_global_option_value( 'pg_cover_image_max_file_size', '' );
				$pg_profile_photo_minimum_width = $dbhandler->get_global_option_value( 'pg_profile_photo_minimum_width', 'DEFAULT' );
				$pg_cover_photo_minimum_width   = $dbhandler->get_global_option_value( 'pg_cover_photo_minimum_width', 'DEFAULT' );

		if ( $pg_profile_photo_minimum_width == 'DEFAULT' ) {
			$pg_profile_photo_minimum_width = 150;
		}
		if ( $pg_cover_photo_minimum_width == 'DEFAULT' ) {
			$pg_cover_photo_minimum_width = 800;
		}
		if ( $pg_profile_image_max_file_size == '' ) {
			// translators: 1: min width, 2: min height.
			$message = sprintf( __( 'File Restrictions: Please make sure your image size fits within %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $pg_profile_photo_minimum_width, $pg_profile_photo_minimum_width );
		} else {
			// translators: 1: min width, 2: min height, 3: max bytes.
			$message = sprintf( __( 'File Restrictions: Please make sure your image size fits within %1$d by %2$d pixels and does not exceeds total size of %3$d bytes.', 'profilegrid-user-profiles-groups-and-communities' ), $pg_profile_photo_minimum_width, $pg_profile_photo_minimum_width, $pg_profile_image_max_file_size );
		}

		if ( $pg_cover_image_max_file_size == '' ) {
			// translators: 1: min width, 2: min height.
			$message2 = sprintf( __( 'File Restrictions: Please make sure your image size fits within %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $pg_cover_photo_minimum_width, 300 );
		} else {
			// translators: 1: min width, 2: min height, 3: max bytes.
			$message2 = sprintf( __( 'File Restrictions: Please make sure your image size fits within %1$d by %2$d pixels and does not exceeds total size of %3$d bytes.', 'profilegrid-user-profiles-groups-and-communities' ), $pg_cover_photo_minimum_width, 300, $pg_cover_image_max_file_size );
		}

				$pm_customfields = new PM_Custom_Fields();
				$pmrequests      = new PM_request();
		if ( is_object( $user ) ) {
						$uid = $user->ID;
			$gids            = $pmrequests->profile_magic_get_user_field_value( $user->ID, 'pm_group' );
						$gid = $pmrequests->pg_filter_users_group_ids( $gids );
		} else {
			$gid             = array();
						$uid = 0;
		}
		$prefill_gid = filter_input( INPUT_GET, 'pg_group_id', FILTER_VALIDATE_INT );
		if ( $prefill_gid && empty( $gid ) ) {
			$gid = array( $prefill_gid );
		}

		$fields = array();
		if ( ! empty( $gid ) ) :
			if ( current_user_can( 'manage_options' ) ) {
				$exclude = 'associate_group in(' . implode( ',', $gid ) . ") and field_type not in('first_name','last_name','user_name','user_email','user_url','user_pass','confirm_pass','description','user_avatar','heading','paragraph')";
			} else {
				$exclude = 'associate_group in(' . implode( ',', $gid ) . ") and field_type not in('first_name','last_name','user_name','user_email','user_url','user_pass','confirm_pass','description','user_avatar','heading','paragraph','read_only')";
			}
			$section_field_filters = 'AND ' . $exclude;

			foreach ( $gid as $group_id ) {
				$sections = $dbhandler->get_all_result( 'SECTION', '*', array( 'gid' => $group_id ), 'results', 0, false, 'ordering' );

				if ( empty( $sections ) ) {
					continue;
				}

				foreach ( $sections as $section ) {
					$section_fields = $dbhandler->get_all_result(
						'FIELDS',
						'*',
						array( 'associate_section' => $section->id ),
						'results',
						0,
						false,
						'ordering',
						false,
						$section_field_filters
					);

					if ( ! empty( $section_fields ) ) {
						$fields = array_merge( $fields, $section_fields );
					}
				}
			}

			$unsectioned_fields = $dbhandler->get_all_result(
				'FIELDS',
				'*',
				array( 'associate_section' => 0 ),
				'results',
				0,
				false,
				'ordering',
				false,
				$section_field_filters
			);

			if ( ! empty( $unsectioned_fields ) ) {
				$fields = array_merge( $fields, $unsectioned_fields );
			}
		endif;
		$col = $dbhandler->get_global_option_value( 'pm_reg_form_cols', 1 );

		$profile_pic                = ( is_object( $user ) ) ? get_user_meta( $uid, 'pm_user_avatar', true ) : false;
		$groups                     = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ), $where = 1, $result_type = 'results', $offset = 0, $limit = false, $sort_by = 'group_name', $descending = false );
                
				$pm_profile_privacy = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_profile_privacy' );
				$pm_hide_my_profile = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_hide_my_profile' );
		if ( empty( $pm_hide_my_profile ) ) {
			$pm_hide_my_profile = '0';
		}
				$cover_image = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_cover_image' );
		?>
			<table class="form-table"> 
				<tbody>
						<tr>
							
							<th class="pm-field-lable">
								<label for="pm_field_37"><?php esc_html_e( 'Profile Picture', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							</th>
							<td class="pm-field-input">              
								<input id="pm_user_avatar" type="hidden" name="pm_user_avatar" class="icon_id" value="<?php
								if ( isset( $profile_pic ) ) {
									echo esc_attr( $profile_pic );}
								?>" />
																<input id="field_icon_button" name="field_icon_button" class="button group_icon_button" type="button" value="<?php esc_attr_e( 'Upload Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
								<span class="pg_profile_image_container" style="
								<?php
								if ( ! is_object( $user ) || $profile_pic == false ) {
									echo 'display: none;';}
								?>
								">
								<?php
								if ( is_object( $user ) && isset( $user->user_email ) ) {
									echo get_avatar(
										$user->user_email,
										100,
										'',
										false,
										array(
											'class'         => 'pm-user user-profile-image',
											'id'            => 'pg_upload_image_preview',
											'force_display' => true,
										)
									);
								}
								?>
																	<input type="button" name="pg_remove_image" id="pg_remove_image" class="button" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="pg_remove_profile_image()"/>
								</span>
								<p class="description"><?php echo wp_kses_post( $message ); ?></p>
								<div class="errortext" style="display:none;"></div>
							</td> 
						</tr>
						
						<tr>
							
							<th class="pm-field-lable">
								<label for="pm_field_37"><?php esc_html_e( 'Cover Image', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							</th>
							<td class="pm-field-input">              
								<input id="pm_cover_image" type="hidden" name="pm_cover_image" class="cover_icon_id" value="<?php
								if ( isset( $cover_image ) ) {
									echo esc_attr( $cover_image );}
								?>" />
																<input id="cover_image_button" name="cover_image_button" class="button cover_image_button" type="button" value="<?php esc_attr_e( 'Upload Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
								<span class="pg_cover_image_container" style="
								<?php
								if ( ! is_object( $user ) || $cover_image == false ) {
									echo 'display: none;';}
								?>
								">
								<?php
								$src = wp_get_attachment_image_src( $cover_image, array( 100, 100 ) );

								?>
									<img src="
									<?php
									if ( isset( $src['0'] ) ) {
										echo esc_url( $src['0'] );}
									?>
									" width="100" height="100" class="pm-user" id="pg_upload_cover_image_preview" />
																		<input type="button" name="pg_remove_cover_image" id="pg_remove_cover_image" class="button" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="pm_remove_cover_image()"/>
								</span>
								<p class="description"><?php echo wp_kses_post( $message2 ); ?></p>
								<div class="errortext" style="display:none;"></div>
							</td> 
						</tr>
						<?php
						$pm_show_privacy_settings = $dbhandler->get_global_option_value( 'pm_show_privacy_settings', '' );
						if ( $pm_show_privacy_settings == 1 ) {
							?>
						<tr>
						   <th class="pm-field-lable">
								<label for="pm_profile_privacy"><?php esc_html_e( 'Profile Privacy', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							</th>
							<td class="pm-field-input">              
								 <select name="pm_profile_privacy" id="pm_profile_privacy">
									<option value="1" <?php selected( $pm_profile_privacy, '1' ); ?>><?php esc_html_e( 'Everyone', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
									<option value="2" <?php selected( $pm_profile_privacy, '2' ); ?>><?php esc_html_e( 'Friends', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
									<option value="3" <?php selected( $pm_profile_privacy, '3' ); ?>><?php esc_html_e( 'Group Members', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
									<option value="4" <?php selected( $pm_profile_privacy, '4' ); ?>><?php esc_html_e( 'Friends &amp; Group Members', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
									<option value="5" <?php selected( $pm_profile_privacy, '5' ); ?>><?php esc_html_e( 'Only Me', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
								</select>
							</td> 
						</tr>
						<?php } ?>
						
						<?php
						$allowhiddenusers = $dbhandler->get_global_option_value( 'pm_allow_user_to_hide_their_profile', '' );
						if ( $allowhiddenusers == 1 ) {
							?>
						<tr>
							<th class="pm-field-lable">
								<label for="pm_hide_my_profile"><?php esc_html_e( 'Hide My Profile From Groups, Directories and Search Results', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							</th>
							<td class="pm-field-input">              
								 <div class="pmradio">
									<div class="pm-radio-option">
										<input type="radio" class="pg-hide-privacy-profile" name="pm_hide_my_profile" value="0" <?php checked( $pm_hide_my_profile, '0' ); ?>> 
										<label class="pg-hide-my-profile"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
									</div>
									 <div class="pm-radio-option">
										<input type="radio" class="pg-hide-privacy-profile" name="pm_hide_my_profile" value="1" <?php checked( $pm_hide_my_profile, '1' ); ?> > 
										<label class="pg-hide-my-profile"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?> </label>
									</div>

								 </div>
							</td> 
						</tr>
			  
						<?php } ?>
						
				</tbody>
			</table>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<table class="form-table">        
						<tr>

							<th class="pm-field-lable">
								<label for="pm_field_37"><?php esc_html_e( 'User Group(s)', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							</th>
							<td class="pm-field-input">              
								<select multiple name="pm_group[]" id="pm_group">
											<?php
											foreach ( $groups as $group ) {
												?>
									<option value="<?php echo esc_attr( $group->id ); ?>" 
															  <?php
																if ( ! empty( $gid ) ) {
																	if ( in_array( $group->id, $gid ) ) {
																		echo 'selected';}
																}
																?>
																><?php echo esc_html( $group->group_name ); ?></option>
									<?php } ?>
								</select>
								<p class="description"><?php esc_html_e( 'Press ctrl or ⌘ (in Mac) while clicking to assign multiple ProfileGrid Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
								<div class="errortext" style="display:none;"></div>
							</td>
						</tr>
					</table>
		
			<?php
				endif;
		if ( isset( $fields ) && ! empty( $fields ) ) :
			if ( $lastRec = count( $fields ) && ! empty( $gid ) ) {
				echo '<div class="pm_dashboard_custom_fields">';
				$i = 0;
				foreach ( $fields as $field ) {
					$value = $pmrequests->profile_magic_get_user_field_value( $user->ID, $field->field_key );
					$pm_customfields->pm_get_custom_form_fields( $field, $value, $this->profile_magic );
					$i++;
				}
				echo '</div>';
			}
				endif;
		if ( is_object( $user ) ) {
			echo '<input type="hidden" id="pg_user_id" name="pg_user_id" value="' . esc_attr( isset( $user->ID ) ? absint( $user->ID ) : get_current_user_id() ) . '" />';
		}
				wp_nonce_field( 'pg_user_profile', 'pg_user_profile_nonce' );
				echo '<div class="all_errors" style="display:none;"></div>';

	}

	public function profile_magic_update_user_fields( $user_id ) {
			$pm_sanitizer = new PM_sanitizer();
			$nonce        = filter_input( INPUT_POST, 'pg_user_profile_nonce' );
		if ( isset( $nonce ) && wp_verify_nonce( $nonce, 'pg_user_profile' ) ) {

                        $post      = $pm_sanitizer->sanitize( $_POST );
                        $dbhandler = new PM_DBhandler();
			$pmrequests            = new PM_request();
			$pm_emails             = new PM_Emails();
			$notification          = new Profile_Magic_Notification();
			$current_user          = wp_get_current_user();
			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}
			if ( ! isset( $post['reg_form_submit'] ) ) {
				if ( isset( $post['pm_user_avatar'] ) ) {
					$pm_user_avatar = $post['pm_user_avatar'];
				} else {
					$pm_user_avatar = '';
				}
				$is_update_profile_image = update_user_meta( $user_id, 'pm_user_avatar', $pm_user_avatar );
				if ( $is_update_profile_image ) {
					if ( $pm_user_avatar == '' ) {
						do_action( 'pm_remove_profile_image', $user_id );
					} else {
						do_action( 'pm_update_profile_image', $user_id );
					}
				}
				if ( isset( $post['pm_cover_image'] ) ) {
					$pm_cover_image = $post['pm_cover_image'];
				} else {
					$pm_cover_image = '';
				}
						$is_update_cover_image = update_user_meta( $user_id, 'pm_cover_image', $pm_cover_image );

				if ( $is_update_cover_image ) {
					if ( $pm_cover_image == '' ) {
						do_action( 'pm_remove_cover_image', $user_id );
					} else {
						do_action( 'pm_update_cover_image', $user_id );
					}
				}

						$allowhiddenusers = $dbhandler->get_global_option_value( 'pm_allow_user_to_hide_their_profile', '' );
				if ( $allowhiddenusers == 1 ) {
					update_user_meta( $user_id, 'pm_hide_my_profile', $post['pm_hide_my_profile'] );
				}

						$pm_show_privacy_settings = $dbhandler->get_global_option_value( 'pm_show_privacy_settings', '' );
				if ( $pm_show_privacy_settings == 1 ) {
					update_user_meta( $user_id, 'pm_profile_privacy', $post['pm_profile_privacy'] );
				}
						$gids = $pmrequests->profile_magic_get_user_field_value( $user_id, 'pm_group' );
						$gid  = $pmrequests->pg_filter_users_group_ids( $gids );
				if ( ! empty( $gid ) ) {
							$exclude = 'associate_group in(' . implode( ',', $gid ) . ") and field_type not in('first_name','last_name','user_name','user_email','user_url','user_pass','confirm_pass','description','user_avatar','heading','paragraph')";
							$fields  = $dbhandler->get_all_result( 'FIELDS', '*', 1, 'results', 0, false, 'ordering', false, $exclude );
				} else {
					$fields = array();
				}
						$pmrequests->pm_update_user_custom_fields_data( $_POST, $_FILES, $_SERVER, $gid, $fields, $user_id );
				if ( isset( $post['pm_group'] ) ) {
					$pm_group = $post['pm_group'];
				}
				if ( ! isset( $pm_group ) ) {
					$pm_group = array();
				}

						add_user_meta( $user_id, 'rm_user_status', 0, true );

				if ( current_user_can( 'manage_options' ) ) {
					
					$new_groups = array_diff( $pm_group, $gid );
					$old_groups = array_diff( $gid, $pm_group );
                                        
                                        if(!empty($new_groups))
                                        {
                                            foreach ( $new_groups as $nl_gid ) {
                                                $is_group_limit = $dbhandler->get_value('GROUPS','is_group_limit',$nl_gid);
                                                if($is_group_limit==1)
                                                {
                                                    $meta_query_array = $pmrequests->pm_get_user_meta_query(array('gid'=>$nl_gid));
                                                    $user_query = $dbhandler->pm_get_all_users_ajax('',$meta_query_array);
                                                    $total_users_in_group = $user_query->get_total();
                                                    $limit = $dbhandler->get_value('GROUPS','group_limit',$nl_gid);

                                                        if($limit <= $total_users_in_group)
                                                        {
                                                            $id = array_search($nl_gid, $pm_group);
                                                            unset($pm_group[$id]);
                                                        }
                                                }
                                                    
                                            }
                                        }
                                        
                                        update_user_meta( $user_id, 'pm_group', $pm_group );
					foreach ( $old_groups as $o_id ) {
						$notification->pm_removed_old_group_notification( $user_id, $o_id );
						if ( $current_user->ID != $user_id ) {
							$pm_emails->pm_send_group_based_notification( $o_id, $user_id, 'on_membership_terminate' );
						}
					}

					foreach ( $new_groups as $n_gid ) {
						$notification->pm_joined_new_group_notification( $user_id, $n_gid );
						do_action( 'profile_magic_join_group_additional_process', $n_gid, $user_id );
					}
				}
			}
		}
                elseif ( ! isset( $post['reg_form_submit'] ) )
                {
                    update_user_meta( $user_id, 'rm_user_status', '0' );
                }
	}

	public function profile_magic_activate_user_by_email() {
		$pmemails           = new PM_Emails();
		$req_raw            = filter_input( INPUT_GET, 'user' );
		$req                = sanitize_text_field( wp_unslash( $req_raw ) );
		$pmrequests         = new PM_request();
		$req_deco           = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $req );
		$user_data          = json_decode( $req_deco );
		$redirect_url       = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
				$nonce_name = 'user_activation_link_' . $user_data->user_id;
				$nonce      = filter_input( INPUT_GET, '_wpnonce' );

		if ( wp_verify_nonce( $nonce, $nonce_name ) && $user_data->activation_code === get_user_meta( $user_data->user_id, 'pm_activation_code', true ) ) {
			$gids          = get_user_meta( $user_data->user_id, 'pm_group', true );
			$gid           = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_group = $pmrequests->pg_get_primary_group_id( $gid );
			if ( ! empty( $gid ) ) {
				$pmemails->pm_send_group_based_notification( $primary_group, $user_data->user_id, 'on_user_activate' );
			}
			update_user_meta( $user_data->user_id, 'rm_user_status', 0 );
			if ( ! delete_user_meta( $user_data->user_id, 'pm_activation_code' ) ) {
				 $redirect_url = add_query_arg( 'errors', 'ajx_failed_del', $redirect_url );
			} else {
				$message      = __( 'You have successfully activated the user.', 'profilegrid-user-profiles-groups-and-communities' );
				$redirect_url = add_query_arg( 'activated', 'success', $redirect_url );
			}
		} else {
			 $message      = __( 'Failed to update user information. Can not activate user', 'profilegrid-user-profiles-groups-and-communities' );
			 $redirect_url = add_query_arg( 'errors', 'invalid_code', $redirect_url );
		}
		wp_safe_redirect( esc_url_raw( $redirect_url ) );
		exit;
	}

	public function pm_load_export_fields_dropdown() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		include 'partials/export-fields.php';
		die;
	}

	public function pm_upload_csv() {
            
                        if ( current_user_can( 'manage_options' ) ) {
                            include 'partials/pm-import-ajax.php';
                        }
			die;
	}

	public function pg_custom_upload_mimes( $mimes = array() ) {
		$mimes['json'] = 'text/plain';
		return $mimes;
	}

	private function pg_is_allowed_import_option_name( $option_name ) {
		$option_name = is_string( $option_name ) ? trim( $option_name ) : '';
		if ( '' === $option_name ) {
			return false;
		}

		return 0 === strpos( $option_name, 'pm_' ) || 0 === strpos( $option_name, 'pg_' );
	}

	public function pm_upload_json() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_import_options' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		$dbhandler      = new PM_DBhandler();
		$pmrequests     = new PM_request();
		$current_user   = wp_get_current_user();
		$pmexportimport = new PM_Export_Import();
		$post           = isset( $_POST ) ? $_POST : array();

		$allowed_ext = 'json';
		if ( isset( $_FILES['uploadjson'] ) && ! empty( $_FILES['uploadjson'] ) ) {
					$filefield = $_FILES['uploadjson'];
			$attachment_id     = $pmrequests->make_upload_and_get_attached_id( $filefield, $allowed_ext );
			if ( ! is_numeric( $attachment_id ) ) {
				add_filter( 'upload_mimes', array( $this, 'pg_custom_upload_mimes' ) );
				$attachment_id = $pmrequests->make_upload_and_get_attached_id( $filefield, $allowed_ext );
			}
			if ( is_numeric( $attachment_id ) ) {
				$filepath                    = wp_get_attachment_url( $attachment_id );
				$content                     = wp_remote_get( $filepath );
								$filecontent = $content['body'];
				$options_data                = json_decode( $filecontent );
				foreach ( $options_data as $data ) {
					if ( is_object( $data ) ) {
						$option_name  = isset( $data->option_name ) ? sanitize_key( $data->option_name ) : '';
						$option_value = isset( $data->option_value ) ? $data->option_value : '';
						if ( $this->pg_is_allowed_import_option_name( $option_name ) ) {
							 $dbhandler->update_global_option_value( $option_name, $option_value );
						}
					} elseif ( is_array( $data ) ) {
						$option_name  = isset( $data[0] ) ? sanitize_key( $data[0] ) : '';
						$option_value = isset( $data[1] ) ? $data[1] : '';
						if ( $this->pg_is_allowed_import_option_name( $option_name ) ) {
							 $dbhandler->update_global_option_value( $option_name, $option_value );
						}
					}
				}
				echo '<div class="uimrow">' . esc_html__( 'Your configuration file was successfully imported and included settings have been applied.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';

			} else {
				echo '<div class="uimrow" style="color:red;">' . esc_html( $attachment_id ) . '</div>';
			}
		} else {
			echo '<div class="uimrow" style="color:red;">' . esc_html__( 'Select a JSON file earlier exported from ProfileGrid.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
		}

		die;
	}

	public function profile_grid_myme_types( $mime_types ) {
		$mime_types['csv']  = 'text/csv';
		$mime_types['json'] = 'application/json';
		$mime_types['webp'] = 'image/webp';
		$mime_types['avif'] = 'image/avif';
		return $mime_types;
	}

	public function profile_magic_show_feedback_form() {
		$currentScreen = get_current_screen();
		if ( $currentScreen->id == 'plugins' ) {
			$path = plugin_dir_url( __FILE__ );
			?>
				<div class="pmagic uimagic">
				<div id="pg-deactivate-feedback-dialog-wrapper" class="pg-modal-view" style="display: none">
					<div class="pg-modal-overlay" style="display: none"></div>

				   <div class="pg-modal-wrap pg-deactivate-feedback"> 
					   <div class="pg-modal-titlebar">
								   <div class="pg-modal-title"><?php esc_html_e( 'ProfileGrid Feedback', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>
								   <div class="pg-modal-close">&times;</div>
							   </div>

					   <form id="pg-deactivate-feedback-dialog-form" method="post">
						   <input type="hidden" name="action" value="pg_deactivate_feedback" />
					   <div class="pg-modal-container">
					<div class="uimrow">
					<div id="pg-deactivate-feedback-dialog-form-caption"><?php esc_html_e( 'If you have a moment, please share why you are deactivating ProfileGrid:', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
					<div id="pg-deactivate-feedback-dialog-form-body">
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-feature_not_available" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="feature_not_available">
							<label for="pg-deactivate-feedback-feature_not_available" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f61e;</span><?php esc_html_e( "Doesn't have the feature I need", 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
														<div class="pginput" id="pg_reason_feature_not_available" style="display:none"><input class="pg-feedback-text" type="text" name="pg_reason_feature_not_available" placeholder="<?php esc_attr_e( 'Please let us know the missing feature...', 'profilegrid-user-profiles-groups-and-communities' ); ?>"></div>
						</div>
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-feature_not_working" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="feature_not_working" >
							<label for="pg-deactivate-feedback-feature_not_working" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f615;</span><?php esc_html_e( "One of the features didn't worked", 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
														<div class="pginput" id="pg_reason_feature_not_working" style="display:none"><input class="pg-feedback-text" type="text" name="pg_reason_feature_not_working" placeholder="<?php esc_attr_e( "Please let us know the feature, like 'emails notifications'", 'profilegrid-user-profiles-groups-and-communities' ); ?>"></div>
						</div>
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-found_a_better_plugin" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="found_a_better_plugin" >
							<label for="pg-deactivate-feedback-found_a_better_plugin" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f60a;</span><?php esc_html_e( 'Moved to a different plugin', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
														<div class="pginput" id="pg_reason_found_a_better_plugin" style="display:none"><input class="pg-feedback-text" type="text" name="pg_reason_found_a_better_plugin" placeholder="<?php esc_attr_e( "Could you please share the plugin's name", 'profilegrid-user-profiles-groups-and-communities' ); ?>"></div>
						</div>
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-plugin_broke_site" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="plugin_broke_site">
							<label for="pg-deactivate-feedback-plugin_broke_site" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f621;</span><?php esc_html_e( 'The plugin broke my site', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						</div>
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-plugin_stopped_working" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="plugin_stopped_working">
							<label for="pg-deactivate-feedback-plugin_stopped_working" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f620;</span><?php esc_html_e( 'The plugin suddenly stopped working', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						</div>
						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-temporary_deactivation" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="temporary_deactivation">
							<label for="pg-deactivate-feedback-temporary_deactivation" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f60a;</span><?php esc_html_e( "It's a temporary deactivation", 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
						</div>

						<div class="pg-deactivate-feedback-dialog-input-wrapper">
							<input id="pg-deactivate-feedback-other" class="pg-deactivate-feedback-dialog-input" type="radio" name="pg_feedback_key" value="other">
							<label for="pg-deactivate-feedback-other" class="pg-deactivate-feedback-dialog-label"><span class="pg-feedback-emoji">&#x1f610;</span><?php esc_html_e( 'Other', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
							<div class="pginput" id="pg_reason_other"  style="display:none"><input class="pg-feedback-text" type="text" name="pg_reason_other" placeholder="<?php esc_attr_e( 'Please share the reason', 'profilegrid-user-profiles-groups-and-communities' ); ?>"></div>
						</div>
					</div>

				</div>
					   </div>

							  <div class="pg-ajax-loader" style="display:none">
								  <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
									<span class=""><?php esc_html_e( 'Loading...', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
									</div>

						   <div class="pg-modal-footer uimrow">
									<input type="button" id="pg-feedback-btn" value="<?php esc_attr_e( 'Submit & Deactivate', 'profilegrid-user-profiles-groups-and-communities' ); ?>"/>
									<input type="button" id="pg-feedback-cancel-btn" value="<?php esc_attr_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>"/>
									</div>
												   <?php wp_nonce_field( 'pm_feedback_form' ); ?>

					   </form>
				   </div>


	</div>
				</div>
				<?php
		}
	}

	public function pg_post_feedback() {
			$retrieved_nonce      = filter_input( INPUT_POST, '_wpnonce' );
                        $msg      = filter_input( INPUT_POST, 'msg' );
                        $feedback = filter_input( INPUT_POST, 'feedback' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_feedback_form' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( isset( $msg ) ) {
					$msg = sanitize_text_field( $msg );
		} else {
                    $msg = '';
		}
		if ( isset( $feedback ) ) {
			$feedback = sanitize_text_field( $feedback );
		} else {
                    $feedback = '';
		}
		$message            = '';
		$pmrequests         = new PM_request();
		$from_email_address = $pmrequests->profile_magic_get_from_email();
		switch ( $feedback ) {
			case 'feature_not_available':
				$body = 'Feature not available: ';
				break;
			case 'feature_not_working':
				$body = 'Feature not working: ';
				break;
			case 'found_a_better_plugin':
				$body = 'Found a better plugin: ';
				break;
			case 'plugin_broke_site':
				$body = 'Plugin broke my site.';
				break;
			case 'plugin_stopped_working':
				$body = 'Plugin stopped working';
				break;
			case 'temporary_deactivation':
				$body = "It's a temporary deactivation";
				break;
			case 'upgrade':
				$body = 'Upgrading to premium ';
				break;
			case 'other':
				$body = 'Other: ';
				break;
			default:
				return;
		}
		if ( ! empty( $feedback ) ) {
			$message .= $body . "\n\r";
			if ( ! empty( $msg ) ) {
				$message .= $msg . "\n\r";
			}
			$message .= "\n\r ProfileGrid Version - " . PROGRID_PLUGIN_VERSION;
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8\r\n";
			$headers .= 'From:' . $from_email_address . "\r\n";
			wp_mail( 'feedback@profilegrid.co', 'PG Feedback', $message, $headers );
			die;
		}
	}

	public function pg_frontend_group_short_code() {
		$pg_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		$link        = $pg_function->pg_get_extension_shortcode( 'FRONTEND_GROUP' );
		$path        = plugin_dir_url( __FILE__ );
		$html        = '
            <div class="pg-scsubblock">
            <div class="pg-scblock pg-sctitle">' . __( 'Group Creation Form', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pg-scblock"><span class="pg-code">' . $link . '</span></div>
            <div class="pg-scblock"><img class="pg-scimg" src="' . $path . 'partials/images/sc-12.png"></div>
            <div class="pg-scblock pg-scdesc">' . __( 'Allow registered users to create new Groups on front end. These Groups behave and work just like regular ProfileGrid groups.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            </div>';
		$html        = apply_filters( 'pg_filter_frontend_group_shortcode', $html );
		echo wp_kses_post( $html );
	}

	public function pg_geolocation_short_code() {
		$pg_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		$link        = $pg_function->pg_get_extension_shortcode( 'GEOLOCATION' );
		$path        = plugin_dir_url( __FILE__ );
		$html        = '
            <div class="pg-scsubblock">
            <div class="pg-scblock pg-sctitle">' . __( 'Generate User Map', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pg-scblock"><span class="pg-code">' . $link . '</span></div>
            <div class="pg-scblock"><img class="pg-scimg" src="' . $path . 'partials/images/sc-11.png"></div>
            <div class="pg-scblock pg-scdesc">' . __( 'Generate maps showing locations of all users or specific groups using simple shortcodes. Get location data from registration form.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            </div>';
		$html        = apply_filters( 'pg_filter_geolocation_shortcode', $html );
		echo wp_kses_post( $html );
	}

	public function pg_groupwall_short_code() {
		 $pg_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		$link         = $pg_function->pg_get_extension_shortcode( 'GROUPWALL' );
		$path         = plugin_dir_url( __FILE__ );
		$html         = '
            <div class="pg-scsubblock">
            <div class="pg-scblock pg-sctitle">' . __( 'Wall Post Submission Form', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            <div class="pg-scblock"><span class="pg-code">' . $link . '</span></div>
            <div class="pg-scblock"><img class="pg-scimg" src="' . $path . 'partials/images/sc-13.jpg"></div>
            <div class="pg-scblock pg-scdesc">' . __( 'Allows group members to write and submit posts to their group wall. Users can also upload and attach images to their wall posts.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
            </div>';
		$html         = apply_filters( 'pg_filter_groupwall_shortcode', $html );
		echo wp_kses_post( $html );
	}

	public function pg_get_footer_banner() {
            $pmrequests         = new PM_request();
            $dbhandler    = new PM_DBhandler();
            $notice_name = $dbhandler->get_global_option_value( 'pg_dismissible_customization_banner', '0' );
            $activate_extensions = $pmrequests->pg_get_activate_extensions();
		if ( ! empty( $activate_extensions['paid'] ) ) {
                    
                    if($notice_name==0)
                    {
                    $path         = plugin_dir_url( __FILE__ );
			?>
                        <div class="pmagic" id="pg_customization_footer_banner">
                            <div class="pg-customize-banner-row pg-box-row">
                                <div class="pg-box-col-12">
                                    <div class="pg-customize-banner-wrap pg-d-flex pg-justify-content-between pg-box-center pg-p-3 pg-box-w-100 pg-white-bg">
                                        <div class="pg-position-absolute pg-banner-close-icon pg-cursor pg-dismissible" id="pg_dismissible_customization_banner">&#x2715</div> 
                                        <div class="pg-customize-banner-logo"><img width="128" src="<?php echo esc_url($path . 'partials/images/svg/pg-logo.png'); ?>"></div>
                                        <div class="pg-banner-pitch-content-wrap pg-lh-normal">
                                            <div class="pg-banner-pitch-head pg-fs-2 pg-fw-bold">
                                                <?php esc_html_e('Customize ProfileGrid', 'profilegrid-user-profiles-groups-and-communities'); ?>
                                            </div>
                                            <div class="pg-banner-pitch-content pg-fs-5 pg-text-muted">
                                                <?php esc_html_e('Have our team build the exact feature that you need.', 'profilegrid-user-profiles-groups-and-communities'); ?>
                                            </div>
                                        </div>

                                        <div class="pg-banner-btn-wrap">
                                            <a target="_blank" href="https://profilegrid.co/help-support/customizations/" class=""><button class="button button-primary pg-customize-banner-btn"><?php esc_html_e('Get Help Now', 'profilegrid-user-profiles-groups-and-communities'); ?></button></a>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                else
                {
                    $path = plugin_dir_url( __FILE__ ) . 'partials/images/extension_banner.png';

                    ?>
                            <div class="pg-footer-banner"><a href="admin.php?page=pm_extensions"><img src="<?php echo esc_url( $path ); ?>" /></a></div>
                    <?php
                }
                        
		 
	}

	public function pm_dismissible_notice() {
		$dbhandler    = new PM_DBhandler();
		 $pmrequests  = new PM_request();
		 $notice_name = $dbhandler->get_global_option_value( 'pg_dismissible_plugin', '0' );
		 $is_pg_page  = $pmrequests->is_pg_dashboard_page();
		if ( $notice_name == '1' ) {
			return;}
		if ( $is_pg_page == false ) {
			return;}
		?>
		<div class="notice notice-info is-dismissible pg-dismissible" id="pg_dismissible_plugin">
		<p><?php esc_html_e( "If you are testing multiple user profile plugins for WordPress, there's a chance that one or more of them can override ProfileGrid's functionality. If something is not working as expected, please try turning them off. A very common example is profile image upload feature not working.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
		</div>
		<?php
	}

	public function pm_dismissible_woo_bundle_notice() {
		 $dbhandler   = new PM_DBhandler();
		 $pmrequests  = new PM_request();
		 $notice_name = $dbhandler->get_global_option_value( 'pg_woo_bundle_dismissible_plugin', '0' );
		 $is_pg_page  = $pmrequests->is_pg_dashboard_page();
		if ( $notice_name == '1' ) {
			return;}
		if ( $is_pg_page == false ) {
			return;}
		 $activate_extensions = $pmrequests->pg_get_activate_extensions();
		if ( ! empty( $activate_extensions['paid'] ) ) {
			return;}
		if ( class_exists( 'Profile_Magic' ) && class_exists( 'WooCommerce' ) && class_exists( 'Profilegrid_Woocommerce' ) ) {

			?>
		   <div class="notice notice-success is-dismissible pg-dismissible" id="pg_woo_bundle_dismissible_plugin">
			   <p><?php esc_html_e( 'Do you want even more comprehensive WooCommerce integration with your user profiles and user groups? Upgrade to WooCommerce Premium Bundle for ProfileGrid.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a href="https://profilegrid.co/extensions/woocommerce-membership-extensions/?utm_source=pg_plugin&utm_medium=wp_notice&utm_campaign=woo_bundle_promo"><?php esc_html_e( 'Learn more', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></p>
		   </div>
			<?php
		}
	}

	public function pm_dismissible_notice_ajax() {
			$pm_sanitizer = new PM_sanitizer();

                        $nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                if ( current_user_can( 'manage_options' ) ) 
                {
                    $post = $pm_sanitizer->sanitize( $_POST );
                    $dbhandler    = new PM_DBhandler();
                    if ( isset( $post['notice_name'] ) ) {
                            $notice_name = $post['notice_name'];
                            $dbhandler->update_global_option_value( $notice_name, '1' );

                    }
                    $default = array();
                    if ( isset( $post['rm_form_id'] ) ) {
                            $rmformid  = $post['rm_form_id'];
                            $get_value = maybe_unserialize( get_option( 'pg_rm_change_form_type', $default ) );
                            if ( isset( $get_value[ $rmformid ] ) ) {
                                    unset( $get_value[ $rmformid ] );
                                    $dbhandler->update_global_option_value( 'pg_rm_change_form_type', $get_value );

                            }
                            delete_option( 'pg_rm_change_form_type_' . $rmformid );
                            delete_option( 'pg_rm_change_form_type_group_name_' . $rmformid );
                    }
                }
		die;
	}

	public function pm_dismissible_woocommerce_notice() {
		$dbhandler    = new PM_DBhandler();
		 $pmrequests  = new PM_request();
		 $url         = 'https://profilegrid.co/extensions/woocommerce-integration/?utm_source=plugin&utm_medium=wp_notice';
		 $notice_name = $dbhandler->get_global_option_value( 'pg_woocommerce_ext_notice', '0' );
		 $is_pg_page  = $pmrequests->is_pg_dashboard_page();
		if ( $notice_name == '1' ) {
			return;}
		if ( class_exists( 'Profile_Magic' ) && class_exists( 'WooCommerce' ) && ! class_exists( 'Profilegrid_Woocommerce' ) ) {
			?>
			<div class="notice notice-info is-dismissible pg-dismissible" id="pg_woocommerce_ext_notice">
							<p><?php echo sprintf( wp_kses_post( "If you wish to integrate WooCommerce data with ProfileGrid user profiles, please download WooCommerce extension from <a target='_blank' href='%s'>here.</a>", 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $url ) ); ?></p>
			</div>
			<?php
		}
	}

	public function pm_dismissible_custom_profile_tab_notice() {
		$dbhandler    = new PM_DBhandler();
		 $pmrequests  = new PM_request();
		 $url         = 'https://profilegrid.co/extensions/custom-user-profile-tabs-content/';
		 $notice_name = $dbhandler->get_global_option_value( 'pg_custom_tab_ext_notice', '0' );
		 $is_pg_page  = $pmrequests->is_pg_dashboard_page();
		if ( $notice_name == '1' ) {
			return;}
		if ( $is_pg_page == false ) {
			return;}

		if ( class_exists( 'Profile_Magic' ) && class_exists( 'WooCommerce' ) && class_exists( 'Profilegrid_Woocommerce' ) && ! class_exists( 'Profilegrid_User_Content' ) ) {
			?>
			<div class="notice notice-info is-dismissible pg-dismissible" id="pg_custom_tab_ext_notice">
							<p><?php echo sprintf( wp_kses_post( "Do you wish to display information from WooCommerce extensions and other WordPress plugins inside frontend user profiles? Try our Custom User Profile Tabs extension, which can turn user profiles into powerful hubs with all user specific information in one place! <a target='_blank' href='%s'>Get it here.</a>", 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $url ) ); ?></p>
			</div>
			<?php
		}
	}

	public function pm_dismissible_bbpress_notice() {
		$dbhandler    = new PM_DBhandler();
		 $pmrequests  = new PM_request();
		 $url         = 'https://profilegrid.co/extensions/custom-user-profile-tabs-content/';
		 $notice_name = $dbhandler->get_global_option_value( 'pg_bbpress_ext_notice', '0' );
		 $is_pg_page  = $pmrequests->is_pg_dashboard_page();
		if ( $notice_name == '1' ) {
			return;}

		if ( class_exists( 'Profile_Magic' ) && is_plugin_active( 'bbpress/bbpress.php' ) && class_exists( 'Profilegrid_Bbpress' ) && ! class_exists( 'Profilegrid_User_Content' ) ) {
			?>
			<div class="notice notice-info is-dismissible pg-dismissible" id="pg_bbpress_ext_notice">
							<p><?php echo sprintf( wp_kses_post( "Do you wish to display information from bbPress extensions and other WordPress plugins inside frontend user profiles? Try our Custom User Profile Tabs extension, which can turn user profiles into powerful hubs with all user specific information in one place! <a target='_blank' href='%s'>Get it here.</a>", 'profilegrid-user-profiles-groups-and-communities' ), esc_url( $url ) ); ?></p>
			</div>
			<?php
		}
	}



	public function pm_check_associate_email_tmpl() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
			$pm_sanitizer = new PM_sanitizer();

                        $nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                $post = $pm_sanitizer->sanitize( $_POST );
		$pmrequests   = new PM_request();
		if ( isset( $post['searchIDs'] ) ) {
			$selected = $post['searchIDs'];
			if ( isset( $selected['ID'] ) ) {
				unset( $selected['ID'] );
			}
			if ( isset( $selected['filter'] ) ) {
				unset( $selected['filter'] );
			}
		}

		$count_selected = count( $selected );
		$msg            = '';
		foreach ( $selected as $tid ) {
				$exist_tmpl = $pmrequests->pg_check_email_template_if_used_in_any_group( $tid );
			if ( $exist_tmpl != false ) {
				if ( $count_selected > 1 ) {
					$msg = __( 'One or more email templates you are trying to delete are being used for notifications by a group. Please disassociate them before attempting to delete.', 'profilegrid-user-profiles-groups-and-communities' );
				} else {
					$msg = __( 'The Email Template you are trying to delete is being used for notifications by 1 or more user groups. Disassociate the template from all associated groups before deleting.', 'profilegrid-user-profiles-groups-and-communities' );
				}
			}
		}
				echo esc_html( $msg );
		die;
	}

	public function pm_groups_widget() {
		register_widget( 'Profilegrid_Groups_Menu' );
	}

        public function pm_merge_quniue_value($a,$b)
        {
            $mergedArray = array_merge($a, $b);
            // Remove duplicates while preserving the order
            $uniqueArray = array_unique($mergedArray);
            // If you want the final array to have consecutive integer keys starting from 0, you can use array_values
            $finalArray = array_values($uniqueArray);
            return $finalArray;

        }
	public function pm_group_option_update() {
		// This option sync is only needed on normal wp-admin page loads.
		if ( ! is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( ! class_exists( 'PM_DBhandler' ) || ! class_exists( 'PM_request' ) ) {
			return;
		}

		$dbhandler = new PM_DBhandler();
		$pmrequest = new PM_request();

		$pg_main_groups = $dbhandler->get_all_result( 'GROUPS', array( 'id' ), $where = 1, $result_type = 'results', $offset = 0, $limit = false, $sort_by = null, $descending = false, $additional = '', $output = 'ARRAY_A', $distinct = false );
		if ( ! empty( $pg_main_groups ) ) {
			$pg_groups  = $pmrequest->pm_to_array( $pg_main_groups );
			$group_menu = get_option( 'pg_group_menu' );
			if ( ! empty( $group_menu ) && is_array($group_menu)) {
                                $pg_group_menu = $this->pm_merge_quniue_value($group_menu, $pg_groups);
				update_option( 'pg_group_menu', $pg_group_menu );
			} else {
				if ( isset( $pg_groups ) ) :
					$tmp = $group_menu;
					if ( isset( $group_menu ) && is_array( $group_menu ) && ! empty( $group_menu ) ) {
						sort( $group_menu );
					}
					if ( isset( $pg_groups ) && is_array( $pg_groups ) && ! empty( $pg_groups ) ) {
						sort( $pg_groups );
					}
				endif;
				if ( $group_menu == $pg_groups ) :
					update_option( 'pg_group_menu', $tmp );
				else :
					update_option( 'pg_group_menu', $pg_groups );
					update_option( 'pg_group_list', $pg_groups );
				endif;
			}
			$group_list = get_option( 'pg_group_list' );
			if ( ! $group_list ) {
				update_option( 'pg_group_list', $pg_groups );
			}
			$pg_group_icon = get_option( 'pg_group_icon' );
			if ( ! $pg_group_icon ) {
				update_option( 'pg_group_icon', 'yes' );
			}
		}
	}

	public function pg_groupleader_assign_remove_fun( $gid, $prev_is_leader, $prev_group_leaders, $new_is_leader, $new_group_leaders ) {
		$pmemails              = new PM_Emails();
			$is_remove         = false;
			$is_assign         = false;
			$new_group_leaders = maybe_unserialize( $new_group_leaders );

		if ( $prev_is_leader != '' && $prev_is_leader == 1 && $new_is_leader == 0 ) {
			if ( ! empty( $prev_group_leaders ) ) {
				foreach ( $prev_group_leaders as $old_admin ) {
					$user_data = get_user_by( 'ID', $old_admin );
					$pmemails->pm_send_group_based_notification( $gid, $user_data->ID, 'on_admin_removal' );
					do_action( 'pm_unassign_group_manager_privilege', $gid, $user_data->ID );
				}
			}
		} else {
			if ( empty( $prev_group_leaders ) ) {
				$prev_group_leaders = array();
			}
			if ( empty( $new_group_leaders ) ) {
				$new_group_leaders = array();
			}
			$admins_removed = array_diff( $prev_group_leaders, $new_group_leaders );
			$admins_added   = array_diff( $new_group_leaders, $prev_group_leaders );

			if ( ! empty( $admins_added ) ) {
				foreach ( $admins_added as $new_admin ) {
					$user_data = get_user_by( 'ID', $new_admin );
					$pmemails->pm_send_group_based_notification( $gid, $user_data->ID, 'on_admin_assignment' );
					 do_action( 'pm_assign_group_manager_privilege', $gid, $user_data->ID );
				}
			}
			if ( ! empty( $admins_removed ) ) {
				foreach ( $admins_removed as $old_admin ) {
					$user_data = get_user_by( 'ID', $old_admin );
					$pmemails->pm_send_group_based_notification( $gid, $user_data->ID, 'on_admin_removal' );
					do_action( 'pm_unassign_group_manager_privilege', $gid, $user_data->ID );
				}
			}
		}
		return;
	}

	public function rm_form_type_changed_fun( $form_id, $form_type, $previous_form_type ) {
		 $pmrequest   = new PM_request();
		$dbhandler    = new PM_DBhandler();
		$is_associate = $pmrequest->pm_check_rm_form_associate_with_groups( $form_id );
		if ( ! empty( $is_associate ) && $form_type != '1' && $previous_form_type == '1' ) {
			$group_name = array();
			foreach ( $is_associate as $group ) {
				$group_name[]                = $dbhandler->get_value( 'GROUPS', 'group_name', $group );
				$group_options               = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_options', $group ) );
				$group_options['pg_rm_form'] = '0';

				$dbhandler->update_row( 'GROUPS', 'id', $group, array( 'group_options' => maybe_serialize( $group_options ) ), array( '%s' ), '%d' );
			}
			if ( ! empty( $group_name ) ) {
				$name = implode( ',', $group_name );
			} else {
				$name = '';
			}

			$default     = array();
			$get_value   = maybe_unserialize( get_option( 'pg_rm_change_form_type', $default ) );
			$get_value[] = $form_id;
			update_option( 'pg_rm_change_form_type', $get_value );
			update_option( 'pg_rm_change_form_type_' . $form_id, $form_id );
			update_option( 'pg_rm_change_form_type_group_name_' . $form_id, $name );
		}
	}

	public function pm_dismissible_rm_form_type_changed() {
		 $pmrequest = new PM_request();
		$dbhandler  = new PM_DBhandler();
		$default    = array();
		$get_value  = maybe_unserialize( get_option( 'pg_rm_change_form_type', $default ) );

		if ( ! empty( $get_value ) && is_array( $get_value ) ) {
			foreach ( $get_value as $form_id ) {
				$get_form_option = get_option( "pg_rm_change_form_type_$form_id", '' );
				if ( $get_form_option != '' ) {
					$name = get_option( "pg_rm_change_form_type_group_name_$form_id", '' );
					?>
						<div class="notice notice-info is-dismissible pgrm-dismissible" id="pg_rm_change_form_type_<?php echo esc_attr( $form_id ); ?>" data-rmid="<?php echo esc_attr( $form_id ); ?>">
							<p><?php echo sprintf( wp_kses_post( '%s registration form has been reverted to default ProfileGrid form since the associated RegistrationMagic form was deleted or its type was changed.', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $name ) ); ?></p>
						</div>
					<?php
				}
			}
		}
	}

	public function rm_user_deactivated( $uid ) {
		$pmrequests    = new PM_request();
		$pmemails      = new PM_Emails();
		$ugids         = get_user_meta( $uid, 'pm_group', true );
		$ugid          = $pmrequests->pg_filter_users_group_ids( $ugids );
		$primary_group = $pmrequests->pg_get_primary_group_id( $ugid );
		$pmemails->pm_send_group_based_notification( $primary_group, $uid, 'on_user_deactivate' );
	}


	public function rm_form_deleted_fun( $form_id ) {
		$this->rm_form_type_changed_fun( $form_id, '2', '1' );
	}

	public function pm_get_rm_helptext() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
			$pm_sanitizer = new PM_sanitizer();
			$nonce        = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                $post = $pm_sanitizer->sanitize( $_POST );
		$dbhandler    = new PM_DBhandler();
		if ( isset( $post['id'] ) ) {
			$form_id = $post['id'];
		} else {
					$form_id = '0';
		}
		if ( $form_id == '0' ) {
			echo "'Default' sets up the group registration form using this group's profile fields";
		} else {
			$form_name = $dbhandler->get_value( 'FORMS', 'form_name', $form_id );
			echo "This sets up the RegistrationMagic form <a target='_blank' href='admin.php?page=rm_form_sett_manage&rm_form_id=" . esc_attr( $form_id ) . "'>" . esc_html( $form_name ) . "</a> as this group's registration form";
		}
		die;
	}
	public function profilegrid_user_blogs_widgets() {
		register_widget( 'Profilegrid_User_Blogs' );
	}

	public function profilegrid_user_login_widgets() {
		register_widget( 'Profilegrid_User_login' );
	}
	// register our meta box for our links
	public function individual_user_group_add_meta_box() {
		add_meta_box(
			'group_pages_menu_metabox',
			__( 'Individual User Group', 'profilegrid-user-profiles-groups-and-communities' ),
			array( $this, 'individual_user_group_display_meta_box' ),
			'nav-menus',
			'side',
			'low'
		);
		add_meta_box(
			'user_profile_pages_menu_metabox',
			__( 'Individual User Profile', 'profilegrid-user-profiles-groups-and-communities' ),
			array( $this, 'individual_user_profile_display_meta_box' ),
			'nav-menus',
			'side',
			'low'
		);

	}

	public function individual_user_group_display_meta_box() {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$groups     = $dbhandler->get_all_result( 'GROUPS' );

		?>
		
	<div id="posttype-group-pages" class="posttypediv">
		<div id="tabs-panel-group-pages" class="tabs-panel tabs-panel-active">
			
			<ul id="group-pages" class="categorychecklist form-no-clear">
				<!--Custom -->
				<?php
				// loop through all registered content types that have 'has-group' enabled

				if ( ! empty( $groups ) ) {
					$counter = -1;
					foreach ( $groups as $group ) {
						$group_name      = $group->group_name;
						$group_page_link = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $group->id );
						//$group_page_link = add_query_arg( 'gid', $group->id, $group_page_link );
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-object-id]" value="-1"/><?php echo esc_html( $group_name ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-type]" value="custom"/>
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-title]" value="<?php echo esc_attr( $group_name ); ?>"/>
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-url]" value="<?php echo esc_url( $group_page_link ); ?>"/>
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-classes]"/>
						</li>
						<?php
						$counter--;
					}
				}
				?>
					 
			</ul>
		</div>
		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-group-pages' ) ); ?>" class="select-all"> <?php esc_html_e( 'Select All', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
			</span>
			<span class="add-to-menu">
				<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="add-post-type-menu-item" id="submit-posttype-group-pages">
				<span class="spinner"></span>
			</span>
		</p>
	</div>    
		<?php
	}

	public function individual_user_profile_display_meta_box() {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$meta_query = $pmrequests->pm_get_user_meta_query( array() );
		$user_query = $dbhandler->pm_get_all_users_ajax( '', $meta_query );

		$users = $user_query->get_results();

		?>
		
	<div id="posttype-user-pages" class="posttypediv">
		<div id="tabs-panel-user-pages" class="tabs-panel tabs-panel-active">
			
			<ul id="user-pages" class="categorychecklist form-no-clear">
				<!--Custom -->
				<?php
				// loop through all registered content types that have 'has-group' enabled

				if ( ! empty( $users ) ) {
					$counter = -1;
					foreach ( $users as $user ) {
						$group_name                          = $user->display_name;
						$uid                                 = $user->ID;
								$profile_url = $pmrequests->pm_get_user_profile_url( $uid );
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-object-id]" value="-1"/><?php echo esc_html( $group_name ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-type]" value="custom"/>
														<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-title]" value="<?php echo esc_attr( $group_name ); ?>"/>
														<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-url]" value="<?php echo esc_url( $profile_url ); ?>"/>
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $counter ); ?>][menu-item-classes]"/>
						</li>
						<?php
						$counter--;
					}
				}
				?>
					 
			</ul>
		</div>
		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-user-pages' ) ); ?>" class="select-all"> <?php esc_html_e( 'Select All', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
			</span>
			<span class="add-to-menu">
				<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="add-post-type-menu-item" id="submit-posttype-user-pages">
				<span class="spinner"></span>
			</span>
		</p>
	</div>    
		<?php
	}

	public function pg_create_group_page() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		
		if ( current_user_can( 'manage_options' ) ) 
		{
			$dbhandler  = new PM_DBhandler();
			$gid        = absint( filter_input( INPUT_POST, 'gid' ) );
			$identifier = 'GROUPS';
			$row        = $dbhandler->get_row( $identifier, $gid );
			if ( $row->group_options != '' ) {
				$group_options = maybe_unserialize( $row->group_options );
			}

			$group_name = 'User Group - ' . $dbhandler->get_value( 'GROUPS', 'group_name', $gid );
			$arg = array(
				'post_type'    => 'page',
				'post_title'   => $group_name,
				'post_status'  => 'publish',
				'post_content' => '[profilegrid_register gid="' . $gid . '"]',
			);

			$arg = apply_filters( 'pm_group_registration_form', $arg, $gid, $group_name, $group_options );
			
			$id         = wp_insert_post( $arg );

			$group_options['group_page'] = $id;
			$options                     = maybe_serialize( $group_options );
			$data                        = array( 'group_options' => $options );
			$args                        = array( '%s' );
			$dbhandler->update_row( $identifier, 'id', $gid, $data, $args, '%d' );

			echo esc_html( $id );
		}
		die;
	}

	public function pg_action_user_edit_form_tag() {
		 echo ' enctype="multipart/form-data"';
	}

	public function pm_remove_file_attachment() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$key = filter_input( INPUT_POST, 'key' );
		$value = filter_input( INPUT_POST, 'value' );
		$pmrequests = new PM_request();
		$result = $pmrequests->pg_remove_profile_attachment( absint( filter_input( INPUT_POST, 'uid', FILTER_VALIDATE_INT ) ), $key, $value );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message(), 403 );
		}
		echo esc_html( $result );
		die;
	}


	public function profile_magic_premium_setting_option() {
		include 'partials/premium_setting_option.php';
	}

	public function pm_change_users_blog_post_types_slug( $args, $post_type ) {
		  $dbhandler      = new PM_DBhandler();
		 $newslug         = $dbhandler->get_global_option_value( 'pm_blog_base', 'profilegrid_blogs' );
		 $group_wall_base = $dbhandler->get_global_option_value( 'pm_group_wall_base', 'pg_groupwalls' );
		/*item post type slug*/
		if ( 'profilegrid_blogs' === $post_type && $newslug != 'profilegrid_blogs' ) {
			$args['rewrite']['slug'] = $newslug;
		}

		if ( class_exists( 'Profilegrid_Group_Wall' ) && 'pg_groupwalls' === $post_type && $group_wall_base != 'pg_groupwalls' ) {
			$args['rewrite']['slug'] = $group_wall_base;
		}
		   return $args;
	}

	public function pm_save_custom_permalink_option() {
		if ( ! is_admin() ) {
			return;
		}
                $nonce = filter_input( INPUT_POST, 'pg-permalinks-nonce' );
		if ( !isset( $nonce ) || !wp_verify_nonce( $nonce, 'pg-permalinks' ) ) {
			return;
		}
                $pm_sanitizer = new PM_sanitizer();
                $post         = $pm_sanitizer->sanitize( $_POST );
		if ( isset( $post['permalink_structure'], $post['pm_blog_base'] ) ) {
                    $dbhandler = new PM_DBhandler();
                    $dbhandler->update_global_option_value( 'pm_blog_base', $post['pm_blog_base'] );

			if ( isset( $post['pm_group_wall_base'] ) ) {
					$dbhandler->update_global_option_value( 'pm_group_wall_base', $post['pm_group_wall_base'] );
			}
		}
	}

	public function pm_custom_permalink_option() {
		/* Create settings section */

		add_settings_section(
			'profilegrid_permalink_section', // ID
			'ProfileGrid User Blogs System Settings ', // Section title
			array( $this, 'pg_permalink_html' ), // Callback for your function
			'permalink' // Location (Settings > Permalinks)
		);

		/* Create settings field */
		add_settings_field(
			'pm_blog_base',       // Field ID
			'User Blogs Base',       // Field title
			array( $this, 'pm_blog_base_callback' ), // Field callback function
			'permalink',                    // Settings page slug
			'profilegrid_permalink_section'               // Section ID
		);

		if ( class_exists( 'Profilegrid_Group_Wall' ) ) {
			/* Create settings field */
			add_settings_field(
				'pm_group_wall_base',       // Field ID
				'Group Wall Base',       // Field title
				array( $this, 'pm_group_wall_base_callback' ), // Field callback function
				'permalink',                    // Settings page slug
				'profilegrid_permalink_section'               // Section ID
			);
		}

	}

	public function pg_permalink_html() {
		echo esc_html__( 'You can define the slugs for posts created through ProfileGrid’s native user blogs system and Group Wall extension.', 'profilegrid-user-profiles-groups-and-communities' );
	}



	/* Settings Field Callback */
	public function pm_blog_base_callback() {

		$dbhandler     = new PM_DBhandler();
			$blog_base = $dbhandler->get_global_option_value( 'pm_blog_base', 'profilegrid_blogs' );
		?>
		<label for="pm_blog_base">
			<input name="pm_blog_base" id="pm_blog_base" type="text" value="<?php echo esc_attr( $blog_base ); ?>" class="regular-text code" autocomplete="off">
		</label>
		<?php
		wp_nonce_field( 'pg-permalinks', 'pg-permalinks-nonce' );
	}

	 /* Settings Field Callback */
	public function pm_group_wall_base_callback() {
		$dbhandler       = new PM_DBhandler();
		$group_wall_base = $dbhandler->get_global_option_value( 'pm_group_wall_base', 'pg_groupwalls' );
		?>
		<label for="pm_group_wall_base">
			<input name="pm_group_wall_base" id="pm_group_wall_base" type="text" value="<?php echo esc_attr( $group_wall_base ); ?>" class="regular-text code" autocomplete="off">
		</label>
		<?php
	}

	public function pm_wizard_update_group_icon() 
        {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ajax-nonce' ) ) {
                die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
            }

            if ( current_user_can( 'manage_options' ) ) 
            {
                $dbhandler = new PM_DBhandler();
                $gid        = absint( filter_input( INPUT_POST, 'gid' ) );
                $group_icon = absint( filter_input( INPUT_POST, 'group_icon' ) );
                $data       = array( 'group_icon' => $group_icon );
                $dbhandler->update_row( 'GROUPS', 'id', $gid, $data );
            }
            die;
	}

	public function pm_submit_group_wizard_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}

		 $dbhandler      = new PM_DBhandler();
		$pm_activator    = new Profile_Magic_Activator();
		$pmrequests      = new PM_request();
		$identifier      = 'GROUPS';
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_group_wizard_form' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		$groupid = absint( filter_input( INPUT_POST, 'group_id' ) );
		$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_group', 'group_id', 'pg-switch-two', 'action' );

		$post = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );

		if ( $post != false ) {
			foreach ( $post as $key => $value ) {
				$data[ $key ] = $value;
				$arg[]        = $pm_activator->get_db_table_field_type( $identifier, $key );
			}
		}

		  $gid = $groupid;
		$dbhandler->update_row( $identifier, 'id', $groupid, $data, $arg, '%d' );
				die;
	}

	public function pm_get_groups_details() {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
                
		   $dbhandler   = new PM_DBhandler();
		$identifier     = 'GROUPS';
			$pmrequests = new PM_request();
		$pm_sanitizer   = new PM_sanitizer();
            $path       = plugin_dir_url( __FILE__ );

                        
                $post = $pm_sanitizer->sanitize( $_POST );
		if ( isset( $post['gids'] ) && ! empty( $post['gids'] ) ) {
			foreach ( $post['gids'] as $gid ) {
				$gid = absint( $gid );
				if ( 0 === $gid ) {
					continue;
				}
				$row              = $dbhandler->get_row( $identifier, $gid );
				$meta_query_array = $pmrequests->pm_get_user_meta_query( array( 'gid' => $row->id ) );
				$user_query       = $dbhandler->pm_get_all_users_ajax( '', $meta_query_array, '', 0, 6, 'DESC', 'ID' );
				$total_users      = $user_query->get_total();
				echo '<div class="pg-box-row pg-box-center pg-card-mb-16">';
				echo '<input type="hidden" name="selected[]" value="' . esc_attr( $row->id ) . '" />';
					echo '<div class="pg-group-row-icon pg-box-col-2">';

				if ( ! empty( $row ) && $row->group_icon != 0 ) {
					echo wp_get_attachment_link( $row->group_icon, array( 50, 50 ), false, true, false );
				} else {
					?>
									<img src="<?php echo esc_url( $path . 'partials/images/pg-icon.png' ); ?>" />
						<?php
				}
						echo '</div>';
						echo '<div class="pg-group-row-meta pg-box-col-10">';
						echo '<div class="pg-group-card-title">' . esc_html( $row->group_name ) . '</div>';
						echo '<div class="pg-group-card-subtitle">' . esc_html( $total_users ) . ' ' . esc_html__( 'members', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
						echo '</div>';
						echo '</div>';
			}
		}
			die;
	}
        
        public function pg_customization_extension_html()
        {
            $path  =  plugin_dir_url( __FILE__ );
            ?>
        <div class="pg-box-col-4 pg-card-mb-16 pg-ext-card ">
            <div class="pg-box-border pg-box-p-18 pg-box-white-bg pg-box-h-100">
                <div class="pg-box-row pg-box-h-100">
                    <div class="pg-box-col-8">
                        <div class="pg-ext-box-title"><?php echo esc_html__('Build a custom extension', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                        <div class="pg-ext-installation-status"><span class="pg-ext-not-installed">Request a Quote</span></div>
                        <div class="pg-ext-box-description">
                            <p class="pg-col-desc"><?php echo esc_html__('Have our team build a custom extension for you to meet your specific needs.', 'profilegrid-user-profiles-groups-and-communities'); ?></p>
                            <p class="authors" style="display:none"> <cite><?php esc_html_e('By', 'profilegrid-user-profiles-groups-and-communities'); ?> <a target="_blank" href="https://profilegrid.co/extensions/"><?php esc_html_e('ProfileGrid', 'profilegrid-user-profiles-groups-and-communities'); ?></a></cite></p>
                        </div>
                        <div class="pg-ext-box-button">
                            <a class="pg-install-now-btn pg-more-info" target="_blank" href="https://profilegrid.co/help-support/customizations/"> <?php esc_html_e('Get Help Now', 'profilegrid-user-profiles-groups-and-communities'); ?></a>
                        </div>
                    </div> 
                    <div class="pg-box-col-4 pg-d-flex pg-d-flex-v-center pg-flex-direction-col">
                    <div class="pg-ext-box-icon"> <img src="<?php echo esc_url($path); ?>partials/images/pg-customization-icon.png" class="pg-ext-icon" alt=""></div>
                    </div> 
                </div>
            </div>
        </div>                                                       
            <?php                                                                
        }
        
        public function profilegrid_activate_license()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ) ), 403 );
            }
            $retrieved_nonce = filter_input( INPUT_POST, 'nonce' );
            if ( !wp_verify_nonce( $retrieved_nonce, 'pg-license-nonce' ) ) {
                    die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
            }
            $dbhandler   = new PM_DBhandler();
            $pg_license_activate = sanitize_text_field(filter_input( INPUT_POST, 'pg_license_activate' ));
            $license_key = sanitize_text_field(filter_input( INPUT_POST, 'pg_license' ));
            $item_id = sanitize_text_field(filter_input( INPUT_POST, 'pg_item_id' ));
            $item_key = sanitize_text_field(filter_input( INPUT_POST, 'pg_item_key' ));
            $dbhandler->update_global_option_value( $item_key.'_license_key', $license_key );
            $dbhandler->update_global_option_value( $item_key.'_license_id', $item_id );
            
            
            $response = array();
            if( isset( $pg_license_activate ) && ! empty( $pg_license_activate ) ){
                $license = new Profile_Magic_License();
                $response = $license->pg_activate_license($license_key,$item_id,$item_key);
                wp_send_json_success( $response );
            }
            else
            {
                wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please refresh the page and try again later.', 'profilegrid-user-profiles-groups-and-communities' ) ) );
            }
            
    }

    public function profilegrid_deactivate_license(){
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ) ), 403 );
            }
        
        $retrieved_nonce = filter_input( INPUT_POST, 'nonce' );
            if ( !wp_verify_nonce( $retrieved_nonce, 'pg-license-nonce' ) ) {
                    die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
            }
            $dbhandler   = new PM_DBhandler();
            $pg_license_deactivate = sanitize_text_field(filter_input( INPUT_POST, 'pg_license_deactivate' ));
            $license_key = sanitize_text_field(filter_input( INPUT_POST, 'pg_license' ));
            $item_id = sanitize_text_field(filter_input( INPUT_POST, 'pg_item_id' ));
            $item_key = sanitize_text_field(filter_input( INPUT_POST, 'pg_item_key' ));
            $dbhandler->update_global_option_value( $item_key.'_license_key', $license_key );
            $dbhandler->update_global_option_value( $item_key.'_license_id', $item_id );
            $response = array();
            if( isset( $pg_license_deactivate ) && ! empty( $pg_license_deactivate ) ){
                $license = new Profile_Magic_License();
                $response = $license->pg_deactivate_license($license_key,$item_id,$item_key);
                wp_send_json_success( $response );
            }
            else
            {
                wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please refresh the page and try again later.', 'profilegrid-user-profiles-groups-and-communities' ) ) );
            }
        
    }
    
    
    public function pg_dismissible_sale_promotion(){
        $notice_name  = get_option( 'pg_dismissible_discount_sale_banner', '0' );
        $today = new DateTime();
        $year = (int) $today->format('Y');
        $last_date = DateTime::createFromFormat('Y-m-d', "$year-07-31");

        if ( $today > $last_date ) {
            return;
        }
        if ( $notice_name == '1') {
                return;
        }
        ?>
            <!-- <div class="notice notice-info pg-brand-notice is-dismissible pg-dismissible" id="pg_dismissible_discount_sale_banner" style="display: none;">
            <div class="pg-content pg-d-flex pg-box-center">
                <div class="pg-icon">🎉</div>
                <div class="pg-message">
                <?php
                // echo wp_kses_post(
                //         __('<strong>Save 30% on ProfileGrid Premium – Limited Time!</strong>
                //         Use code <strong>PGJULY30</strong> at checkout. Offer valid until <strong>July 31</strong>.','profilegrid-user-profiles-groups-and-communities')
                //     );
                ?>
                    
           
                </div>
                     <span class="pg-cta">
                    <a href="<?php echo esc_url('https://profilegrid.co/extensions/?utm_source=plugin&utm_medium=banner&utm_campaign=pgjuly30');?>" class="button button-primary" target="_blank"><?php esc_html_e('Upgrade Now','profilegrid-user-profiles-groups-and-communities');?></a>
                </span>
            </div>
            
            
           
        </div> -->
    <?php
    }

}
