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
class Profile_Magic_Public {

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
                private $pm_theme;
	public function __construct( $profile_magic, $version ) {
			$dbhandler           = new PM_DBhandler();
			$this->profile_magic = $profile_magic;
			$this->version       = $version;
			$this->pm_theme      = $dbhandler->get_global_option_value( 'pm_style', 'default' );

	}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
	public function enqueue_styles() {
		$dbhandler = new PM_DBhandler();
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
		// tell WordPress to load jQuery UI tabs

		wp_enqueue_style( 'jquery-ui-styles' );
		wp_enqueue_style( $this->profile_magic, plugin_dir_url( __FILE__ ) . 'css/profile-magic-public.css', array(), $this->version, 'all' );
		if ( is_user_logged_in() ) :
			wp_enqueue_style( 'jquery.Jcrop.css', plugin_dir_url( __FILE__ ) . 'css/jquery.Jcrop.css', array(), $this->version, 'all' );
			// wp_enqueue_style( 'pm-emoji-picker', plugin_dir_url( __FILE__ ) . 'css/emoji.css', array(), $this->version, 'all' );
			// wp_enqueue_style( 'pm-emoji-picker-nanoscroller', plugin_dir_url( __FILE__ ) . 'css/nanoscroller.css', array(), $this->version, 'all' );
			endif;
		wp_enqueue_style( 'pm-font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'pg-password-checker', plugin_dir_url( __FILE__ ) . 'css/pg-password-checker.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'pg-profile-menu', plugin_dir_url( __FILE__ ) . 'css/pg-profile-menu.css', array(), $this->version, 'all' );
		if ( $dbhandler->get_global_option_value( 'pm_theme_type', 'light' ) == 'dark' ) {
			wp_enqueue_style( 'pg-dark-theme', plugin_dir_url( __FILE__ ) . 'css/pg-dark-theme.css', array(), $this->version, 'all' );
		}
		 wp_enqueue_style( 'pg-responsive', plugin_dir_url( __FILE__ ) . 'css/pg-responsive-public.css', array(), $this->version, 'all' );

		$theme_css = $this->profile_magic_get_pm_theme_css();
		if ( $theme_css != '' ) {
			wp_enqueue_style( $this->pm_theme, $theme_css, array(), $this->version, 'all' );
		}
	}

		/**
		 * Register the JavaScript for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
	public function register_scripts() {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$pm_sanitizer = new PM_sanitizer;
                $request = $pm_sanitizer->sanitize($_REQUEST);
		wp_register_script( 'pg-profile-menu.js', plugin_dir_url( __FILE__ ) . 'js/pg-profile-menu.js', array( 'jquery' ), $this->version, false );
		wp_register_script( $this->profile_magic, plugin_dir_url( __FILE__ ) . 'js/profile-magic-public.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'modernizr-custom.min.js', plugin_dir_url( __FILE__ ) . 'js/modernizr-custom.min.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'profile-magic-multistep-form', plugin_dir_url( __FILE__ ) . 'js/profile-magic-multistep-form.js', array( 'jquery', $this->profile_magic ), $this->version, true );
		wp_register_script( 'profile-magic-footer.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-footer.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'profile-magic-friends-public.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-friends-public.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'pg-password-checker.js', plugin_dir_url( __FILE__ ) . 'js/pg-password-checker.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'profile-magic-admin-power.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-admin-power.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->profile_magic,
			'pm_ajax_object',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'plugin_emoji_url' => plugin_dir_url( __FILE__ ) . 'partials/images/img',
				'nonce'            => wp_create_nonce( 'ajax-nonce' ),
			)
		);

		$reg_sub_page                         = array();
			$reg_sub_page['registration_tab'] = isset( $request['rm_reqpage_sub'] ) || isset( $request['rm_reqpage_pay'] ) || isset( $request['rm_reqpage_inbox'] ) ? 1 : 0;
			wp_localize_script( 'profile-magic-footer.js', 'show_rm_sumbmission_tab', $reg_sub_page );
			$error                                 = array();
			$error['valid_email']                  = esc_html__( 'Please enter a valid e-mail address.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_number']                 = esc_html__( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_date']                   = $pmrequests->pg_wp_date_format_error();
			$error['required_field']               = esc_html__( 'This is a required field.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['required_comman_field']        = esc_html__( 'Please fill all the required fields.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['file_type']                    = esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['short_password']               = esc_html__( 'Your password should be at least 7 characters long.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['pass_not_match']               = esc_html__( 'Password and confirm password do not match.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['user_exist']                   = esc_html__( 'Sorry, username already exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['email_exist']                  = esc_html__( 'Sorry, email already exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['show_more']                    = esc_html__( 'More...', 'profilegrid-user-profiles-groups-and-communities' );
			$error['show_less']                    = esc_html__( 'Show less', 'profilegrid-user-profiles-groups-and-communities' );
			$error['user_not_exit']                = esc_html__( 'Username does not exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['password_change_successfully'] = esc_html__( 'Password changed Successfully', 'profilegrid-user-profiles-groups-and-communities' );
			$allow_file_ext                        = $dbhandler->get_global_option_value( 'pm_allow_file_types', 'jpg|jpeg|png|gif|webp|avif' );
			$allow_file_ext                        = implode( '|', array_unique( array_filter( explode( '|', strtolower( $allow_file_ext . '|webp|avif' ) ) ) ) );
			$error['allow_file_ext']               = $allow_file_ext;
			$error['valid_phone_number']           = esc_html__( 'Please enter a valid phone number.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['valid_mobile_number']              = esc_html__( 'Please enter a valid mobile number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_facebook_url']           = esc_html__( 'Please enter a valid Facebook url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_twitter_url']            = esc_html__( 'Please enter a X url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_google_url']             = esc_html__( 'Please enter a valid Google url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_linked_in_url']          = esc_html__( 'Please enter a Linked In url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_youtube_url']            = esc_html__( 'Please enter a valid Youtube url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_mixcloud_url']           = esc_html__( 'Please enter a valid Mixcloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_soundcloud_url']         = esc_html__( 'Please enter a valid SoundCloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_instagram_url']          = esc_html__( 'Please enter a valid Instagram url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['crop_alert_error']             = esc_html__( 'Please select a crop region then press submit.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['admin_note_error']             = esc_html__( 'Unable to add an empty note. Please write something and try again.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['empty_message_error']          = esc_html__( 'Unable to send an empty message. Please type something.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['invite_limit_error']           = esc_html__( 'Only ten users can be invited at a time.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['no_more_result']               = esc_html__( 'No More Result Found', 'profilegrid-user-profiles-groups-and-communities' );
			$error['delete_friend_request']        = esc_html__( 'This will delete friend request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['remove_friend']                = esc_html__( 'This will remove selected user(s) from your friends list. Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['accept_friend_request_conf']   = esc_html__( 'This will accept request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['cancel_friend_request']        = esc_html__( 'This will cancel request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['next']                         = esc_html__( 'Next', 'profilegrid-user-profiles-groups-and-communities' );
			$error['back']                         = esc_html__( 'Back', 'profilegrid-user-profiles-groups-and-communities' );
			$error['submit']                       = esc_html__( 'Submit', 'profilegrid-user-profiles-groups-and-communities' );
			$error['empty_chat_message']           = esc_html__( "I am sorry, I can't send an empty message. Please write something and try sending it again.", 'profilegrid-user-profiles-groups-and-communities' );

			$pw_login_url       = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
			$pw_login_url       = add_query_arg( 'password', 'changed', $pw_login_url );
			$error['login_url'] = esc_url_raw( $pw_login_url );
			wp_localize_script( $this->profile_magic, 'pm_error_object', $error );
			wp_register_script( 'profile-magic-auto-logout', plugin_dir_url( __FILE__ ) . 'js/profile-magic-auto-logout.js', array( 'jquery' ), $this->version, false );
				$autologout_obj                          = array();
				$autologout_obj['pm_auto_logout_time']   = $dbhandler->get_global_option_value( 'pm_auto_logout_time', '600' );
				$autologout_obj['pm_show_logout_prompt'] = $dbhandler->get_global_option_value( 'pm_show_logout_prompt', '0' );
				wp_localize_script( 'profile-magic-auto-logout', 'pm_autologout_obj', $autologout_obj );
			wp_register_script( 'pg-emojiarea', plugin_dir_url( __FILE__ ) . 'js/emojionearea.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( 'pg-messaging', plugin_dir_url( __FILE__ ) . 'js/pg-messaging.js', array( 'jquery' ), $this->version, true );

			$object                       = array();
			$object['ajax_url']           = admin_url( 'admin-ajax.php' );
			$object['empty_chat_message'] = esc_html__( "I am sorry, I can't send an empty message. Please write something and try sending it again.", 'profilegrid-user-profiles-groups-and-communities' );
		$object['plugin_emoji_url']   = plugin_dir_url( __FILE__ ) . 'partials/images/img';
		$object['seding_text']        = esc_html__( 'Sending', 'profilegrid-user-profiles-groups-and-communities' );
		$object['remove_msg']         = esc_html__( 'This message has been deleted.', 'profilegrid-user-profiles-groups-and-communities' );
		$object['nonce']            = wp_create_nonce( 'ajax-nonce' );
		$object['pg_delete_msg_nonce'] = wp_create_nonce( 'pg_delete_msg_nonce' );
		wp_localize_script( 'pg-messaging', 'pg_msg_object', $object );

	}

	public function enqueue_scripts() {
			$dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
                        $pm_sanitizer = new PM_sanitizer;
                        $request = $pm_sanitizer->sanitize($_REQUEST);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			// wp_enqueue_script('jquery-ui-tabs');
		if ( is_user_logged_in() ) :
			// wp_enqueue_script( 'profile-magic-nanoscroller.js', plugin_dir_url( __FILE__ ) . 'js/nanoscroller.min.js', array( 'jquery' ), $this->version, true );
			// wp_enqueue_script( 'profile-magic-tether.js', plugin_dir_url( __FILE__ ) . 'js/tether.min.js', array( 'jquery' ), $this->version, true );
			// wp_enqueue_script( 'profile-magic-emoji-set.js', plugin_dir_url( __FILE__ ) . 'js/emoji-set.js', array( 'jquery' ), $this->version, true );
			// wp_enqueue_script( 'profile-magic-emoji-util.js', plugin_dir_url( __FILE__ ) . 'js/util.js', array( 'jquery' ), $this->version, true );
			// wp_enqueue_script( 'profile-magic-emojiarea.js', plugin_dir_url( __FILE__ ) . 'js/jquery.emojiarea.js', array( 'jquery' ), $this->version, true );
			// wp_enqueue_script( 'profile-magic-emoji-picker.js', plugin_dir_url( __FILE__ ) . 'js/emoji-picker.js', array( 'jquery' ), $this->version, true );
                        $classes = get_body_class();
                        if (!in_array('fusion-builder-panel-main',$classes)) {
                            wp_enqueue_media();
                        }
			
			wp_enqueue_script( 'jquery-form' );
			wp_enqueue_script( 'jcrop' );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'jquery-effects-core' );
		endif;
			wp_enqueue_script( 'pg-profile-menu.js', plugin_dir_url( __FILE__ ) . 'js/pg-profile-menu.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->profile_magic, plugin_dir_url( __FILE__ ) . 'js/profile-magic-public.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'modernizr-custom.min.js', plugin_dir_url( __FILE__ ) . 'js/modernizr-custom.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'profile-magic-footer.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-footer.js', array( 'jquery' ), $this->version, true );
		if ( is_user_logged_in() ) :
			wp_enqueue_script( 'profile-magic-friends-public.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-friends-public.js', array( 'jquery' ), $this->version, false );
				endif;
		if ( $dbhandler->get_global_option_value( 'pm_enable_live_notification', '1' ) == '1' ) {
			wp_enqueue_script( 'heartbeat' );
		}

			wp_enqueue_script( 'pg-password-checker.js', plugin_dir_url( __FILE__ ) . 'js/pg-password-checker.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'profile-magic-admin-power.js', plugin_dir_url( __FILE__ ) . 'js/profile-magic-admin-power.js', array( 'jquery' ), $this->version, true );
			wp_localize_script(
				$this->profile_magic,
				'pm_ajax_object',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'plugin_emoji_url' => plugin_dir_url( __FILE__ ) . 'partials/images/img',
					'nonce'            => wp_create_nonce( 'ajax-nonce' ),
				)
			);
			$reg_sub_page                     = array();
			$reg_sub_page['registration_tab'] = isset( $request['rm_reqpage_sub'] ) || isset( $request['rm_reqpage_pay'] ) || isset( $request['rm_reqpage_inbox'] ) ? 1 : 0;
			wp_localize_script( 'profile-magic-footer.js', 'show_rm_sumbmission_tab', $reg_sub_page );
			wp_localize_script(
				'profile-magic-footer.js',
				'pm_ajax_object',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'plugin_emoji_url' => plugin_dir_url( __FILE__ ) . 'partials/images/img',
					'nonce'            => wp_create_nonce( 'ajax-nonce' ),
				)
			);
			$error                                 = array();
			$error['valid_email']                  = esc_html__( 'Please enter a valid e-mail address.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_number']                 = esc_html__( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_date']                   = $pmrequests->pg_wp_date_format_error();
			$error['required_field']               = esc_html__( 'This is a required field.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['required_comman_field']        = esc_html__( 'Please fill all the required fields.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['file_type']                    = esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['short_password']               = esc_html__( 'Your password should be at least 7 characters long.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['pass_not_match']               = esc_html__( 'Password and confirm password do not match.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['user_exist']                   = esc_html__( 'Sorry, username already exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['email_exist']                  = esc_html__( 'Sorry, email already exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['show_more']                    = esc_html__( 'More...', 'profilegrid-user-profiles-groups-and-communities' );
			$error['show_less']                    = esc_html__( 'Show less', 'profilegrid-user-profiles-groups-and-communities' );
			$error['user_not_exit']                = esc_html__( 'Username does not exists.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['password_change_successfully'] = esc_html__( 'Password changed Successfully', 'profilegrid-user-profiles-groups-and-communities' );
			$allow_file_ext                        = $dbhandler->get_global_option_value( 'pm_allow_file_types', 'jpg|jpeg|png|gif|webp|avif' );
			$allow_file_ext                        = implode( '|', array_unique( array_filter( explode( '|', strtolower( $allow_file_ext . '|webp|avif' ) ) ) ) );
			$error['allow_file_ext']               = $allow_file_ext;
			$error['valid_phone_number']           = esc_html__( 'Please enter a valid phone number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_mobile_number']          = esc_html__( 'Please enter a valid mobile number.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_facebook_url']           = esc_html__( 'Please enter a valid Facebook url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_twitter_url']            = esc_html__( 'Please enter a X url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_google_url']             = esc_html__( 'Please enter a valid Google url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_linked_in_url']          = esc_html__( 'Please enter a Linked In url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_youtube_url']            = esc_html__( 'Please enter a valid Youtube url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_mixcloud_url']           = esc_html__( 'Please enter a valid Mixcloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_soundcloud_url']         = esc_html__( 'Please enter a valid SoundCloud url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['valid_instagram_url']          = esc_html__( 'Please enter a valid Instagram url.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['crop_alert_error']             = esc_html__( 'Please select a crop region then press submit.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['admin_note_error']             = esc_html__( 'Unable to add an empty note. Please write something and try again.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['empty_message_error']          = esc_html__( 'Unable to send an empty message. Please type something.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['invite_limit_error']           = esc_html__( 'Only ten users can be invited at a time.', 'profilegrid-user-profiles-groups-and-communities' );
			$error['no_more_result']               = esc_html__( 'No More Result Found', 'profilegrid-user-profiles-groups-and-communities' );
			$error['delete_friend_request']        = esc_html__( 'This will delete friend request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['remove_friend']                = esc_html__( 'This will remove selected user(s) from your friends list. Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['accept_friend_request_conf']   = esc_html__( 'This will accept request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['cancel_friend_request']        = esc_html__( 'This will cancel request from selected user(s). Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' );
			$error['next']                         = esc_html__( 'Next', 'profilegrid-user-profiles-groups-and-communities' );
			$error['back']                         = esc_html__( 'Back', 'profilegrid-user-profiles-groups-and-communities' );
			$error['submit']                       = esc_html__( 'Submit', 'profilegrid-user-profiles-groups-and-communities' );
			$error['empty_chat_message']           = esc_html__( "I am sorry, I can't send an empty message. Please write something and try sending it again.", 'profilegrid-user-profiles-groups-and-communities' );

			$pw_login_url       = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
			$pw_login_url       = add_query_arg( 'password', 'changed', $pw_login_url );
			$error['login_url'] = esc_url_raw( $pw_login_url );
			wp_localize_script( $this->profile_magic, 'pm_error_object', $error );
                        wp_localize_script(
				$this->profile_magic,
				'pm_fields_object',
				array(
                                    'dateformat' => $pmrequests->pg_wp_date_format_php_to_js()
				)
			);
                        wp_localize_script('profile-magic-admin-power.js', 'pm_error_object', $error );
                        wp_localize_script(
				'profile-magic-admin-power.js',
				'pm_fields_object',
				array(
                                    'dateformat' => $pmrequests->pg_wp_date_format_php_to_js()
				)
			);
		if ( $dbhandler->get_global_option_value( 'pm_enable_auto_logout_user', '0' ) == '1' && is_user_logged_in() ) :
			wp_enqueue_script( 'profile-magic-auto-logout', plugin_dir_url( __FILE__ ) . 'js/profile-magic-auto-logout.js', array( 'jquery' ), $this->version );
			$autologout_obj                          = array();
			$autologout_obj['pm_auto_logout_time']   = $dbhandler->get_global_option_value( 'pm_auto_logout_time', '600' );
			$autologout_obj['pm_show_logout_prompt'] = $dbhandler->get_global_option_value( 'pm_show_logout_prompt', '0' );
			wp_localize_script( 'profile-magic-auto-logout', 'pm_autologout_obj', $autologout_obj );
				endif;

	}

	public function register_shortcodes() {
			 add_shortcode( 'PM_Registration', array( $this, 'profile_magic_registration_form' ) );
			add_shortcode( 'profilegrid_register', array( $this, 'profile_magic_registration_form' ) );
			add_shortcode( 'PM_Group', array( $this, 'profile_magic_group_view' ) );
			add_shortcode( 'profilegrid_group', array( $this, 'profile_magic_group_view' ) );
			add_shortcode( 'PM_Groups', array( $this, 'profile_magic_groups_view' ) );
			add_shortcode( 'profilegrid_groups', array( $this, 'profile_magic_groups_view' ) );
			add_shortcode( 'PM_Login', array( $this, 'profile_magic_login_form' ) );
			add_shortcode( 'profilegrid_login', array( $this, 'profile_magic_login_form' ) );
			add_shortcode( 'PM_Profile', array( $this, 'profile_magic_profile_view' ) );
			add_shortcode( 'profilegrid_profile', array( $this, 'profile_magic_profile_view' ) );
			add_shortcode( 'PM_Forget_Password', array( $this, 'profile_magic_forget_password' ) );
			add_shortcode( 'profilegrid_forgot_password', array( $this, 'profile_magic_forget_password' ) );
			add_shortcode( 'PM_Password_Reset_Form', array( $this, 'profile_magic_password_reset_form' ) );
			add_shortcode( 'PM_Search', array( $this, 'profile_magic_user_search' ) );
			add_shortcode( 'profilegrid_users', array( $this, 'profile_magic_user_search' ) );
			add_shortcode( 'PM_Messenger', array( $this, 'profile_magic_messenger' ) );
			add_shortcode( 'PM_User_Blogs', array( $this, 'profile_magic_user_blogs' ) );
			add_shortcode( 'profilegrid_user_blogs', array( $this, 'profile_magic_user_blogs' ) );
			add_shortcode( 'PM_Add_Blog', array( $this, 'profile_magic_add_blog' ) );
			add_shortcode( 'profilegrid_submit_blog', array( $this, 'profile_magic_add_blog' ) );

			add_shortcode( 'profilegrid_user_image', array( $this, 'profile_magic_shortcode_user_image' ) );
			add_shortcode( 'profilegrid_user_display_name', array( $this, 'profile_magic_shortcode_user_display_name' ) );
			add_shortcode( 'profilegrid_user_first_name', array( $this, 'profile_magic_shortcode_user_first_name' ) );
			add_shortcode( 'profilegrid_user_last_name', array( $this, 'profile_magic_shortcode_user_last_name' ) );
			add_shortcode( 'profilegrid_user_email', array( $this, 'profile_magic_shortcode_user_email' ) );
			add_shortcode( 'profilegrid_user_cover_image', array( $this, 'profile_magic_shortcode_user_cover_image' ) );
			add_shortcode( 'profilegrid_user_default_group', array( $this, 'profile_magic_shortcode_user_default_group' ) );
			add_shortcode( 'profilegrid_user_all_groups', array( $this, 'profile_magic_shortcode_user_all_groups' ) );
			add_shortcode( 'profilegrid_user_group_badges', array( $this, 'profile_magic_shortcode_user_group_badges' ) );
			add_shortcode( 'profilegrid_unread_notifications', array( $this, 'profile_magic_shortcode_user_unread_notifications_count' ) );
			add_shortcode( 'profilegrid_unread_messages', array( $this, 'profile_magic_shortcode_user_unread_messages_count' ) );
			add_shortcode( 'profilegrid_user_about_area', array( $this, 'profile_magic_shortcode_user_about_area' ) );
			add_shortcode( 'profilegrid_user_groups_area', array( $this, 'profile_magic_shortcode_user_groups_area' ) );
			add_shortcode( 'profilegrid_blog_area', array( $this, 'profile_magic_shortcode_user_blog_area' ) );
			add_shortcode( 'profilegrid_messaging_area', array( $this, 'profile_magic_shortcode_user_messaging_area' ) );
			add_shortcode( 'profilegrid_notification_area', array( $this, 'profile_magic_shortcode_user_notification_area' ) );
			add_shortcode( 'profilegrid_friends_area', array( $this, 'profile_magic_shortcode_user_friends_area' ) );
			add_shortcode( 'profilegrid_settings_area', array( $this, 'profile_magic_shortcode_user_settings_area' ) );
			add_shortcode( 'profilegrid_account_options', array( $this, 'profile_magic_shortcode_user_account_details' ) );
			add_shortcode( 'profilegrid_password_options', array( $this, 'profile_magic_shortcode_user_change_password_tab' ) );
			add_shortcode( 'profilegrid_privacy_options', array( $this, 'profile_magic_shortcode_user_privacy_tab' ) );
			add_shortcode( 'profilegrid_delete_options', array( $this, 'profile_magic_shortcode_user_delete_account_tab' ) );
			add_shortcode( 'profilegrid_group_cards', array( $this, 'profile_magic_shortcode_group_cards' ) );
			add_shortcode( 'profilegrid_group_name', array( $this, 'profile_magic_shortcode_group_name' ) );
			add_shortcode( 'profilegrid_group_description', array( $this, 'profile_magic_shortcode_group_description' ) );
			add_shortcode( 'profilegrid_member_count', array( $this, 'profile_magic_shortcode_group_member_count' ) );
			add_shortcode( 'profilegrid_manager_count', array( $this, 'profile_magic_shortcode_group_manager_count' ) );
			add_shortcode( 'profilegrid_group_manager', array( $this, 'profile_magic_shortcode_group_manager_display_name' ) );
			add_shortcode( 'profilegrid_group_manager_list', array( $this, 'profile_magic_shortcode_group_manager_display_name_in_list' ) );
			add_shortcode( 'profilegrid_group_members_display_name_in_list', array( $this, 'profile_magic_shortcode_group_members_display_name_in_list' ) );
			add_shortcode( 'profilegrid_members_cards', array( $this, 'profile_magic_shortcode_group_members_cards' ) );
			add_shortcode( 'profilegrid_manager_cards', array( $this, 'profile_magic_shortcode_group_managers_cards' ) );
			add_shortcode( 'profilegrid_show', array( $this, 'profile_magic_shortcode_content_visible' ) );
			add_shortcode( 'profilegrid_restrict', array( $this, 'profile_magic_shortcode_content_visible' ) );
			add_shortcode( 'profilegrid_hide', array( $this, 'profile_magic_shortcode_content_not_visible' ) );
			add_shortcode( 'profilegrid_show_managers', array( $this, 'profile_magic_shortcode_content_visible_to_managers' ) );
                        add_shortcode( 'profilegrid_section', array( $this, 'profile_magic_shortcode_section' ) );
                        add_shortcode( 'profilegrid_field', array( $this, 'profile_magic_shortcode_field' ) );
                        add_shortcode( 'profilegrid_edit_profile', array( $this, 'profile_magic_edit_profile_view' ) );

	}

	public function profile_magic_get_template_html( $template_name, $content, $attributes = null ) {
		if ( ! $attributes ) {
			$attributes = array();
		}
			ob_start();
			do_action( 'profile_magic_before_' . $template_name, $template_name, $content );
			require 'partials/' . $template_name . '.php';
			do_action( 'profile_magic_after_' . $template_name );
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
	}

	public function profile_magic_get_pm_theme_tmpl( $type, $gid, $fields ) {
		   $path = $this->profile_magic_get_pm_theme( $type );
			require $path;
	}

	public function profile_magic_get_pm_theme_css() {
		$plugin_path            = plugin_dir_path( __FILE__ );
		$wp_theme_dir           = get_stylesheet_directory();
		$override_pm_theme_path = $wp_theme_dir . '/profilegrid-user-profiles-groups-and-communities/themes/';
		$override_pm_theme      = $override_pm_theme_path . $this->pm_theme . '/' . $this->pm_theme . '.css';
		$default_pm_theme       = $plugin_path . 'partials/themes/' . $this->pm_theme . '/' . $this->pm_theme . '.css';
		if ( file_exists( $override_pm_theme ) ) {

			$wp_theme_dir = get_stylesheet_directory_uri();

			$override_pm_theme_path = $wp_theme_dir . '/profilegrid-user-profiles-groups-and-communities/themes/';
			$override_pm_theme      = $override_pm_theme_path . $this->pm_theme . '/' . $this->pm_theme . '.css';

			$path = $override_pm_theme;
		} elseif ( file_exists( $default_pm_theme ) ) {
			$plugin_path      = plugin_dir_url( __FILE__ );
			$default_pm_theme = $plugin_path . 'partials/themes/' . $this->pm_theme . '/' . $this->pm_theme . '.css';
			$path             = $default_pm_theme;
		} else {
			$path = $plugin_path . 'partials/themes/default/' . $this->pm_theme . '.css';
			if ( file_exists( $path ) ) {
				$plugin_path = plugin_dir_url( __FILE__ );
				$path        = $plugin_path . 'partials/themes/default/' . $this->pm_theme . '.css';
			} else {
				return '';
			}
		}

		return apply_filters('pm_filter_group_theme_css', $path, plugin_dir_path( __FILE__ ), get_stylesheet_directory(), $plugin_path, $wp_theme_dir );
	}


	public function profile_magic_get_pm_theme( $type, $id='' ) {
		$plugin_path            = plugin_dir_path( __FILE__ );
		$wp_theme_dir           = get_stylesheet_directory();
                if(!empty($id))
                {
                    $this->pm_theme = $this->pm_get_group_specific_theme($id,$type,$this->pm_theme);
                }
		$override_pm_theme_path = $wp_theme_dir . '/profilegrid-user-profiles-groups-and-communities/themes/';
		$override_pm_theme      = $override_pm_theme_path . $this->pm_theme . '/' . $type . '.php';
		$default_pm_theme       = $plugin_path . 'partials/themes/' . $this->pm_theme . '/' . $type . '.php';
		if ( file_exists( $override_pm_theme ) ) {
			$path = $override_pm_theme;
		} elseif ( file_exists( $default_pm_theme ) ) {
			$path = $default_pm_theme;
		} else {
			$path = $plugin_path . 'partials/themes/default/' . $type . '.php';
		}
		
		return apply_filters('pm_filter_group_theme', $path, $type, $plugin_path, $wp_theme_dir);
	}
        
        public function pm_get_group_specific_theme($id,$type,$theme)
        {
            $dbhandler = new PM_DBhandler();
            $pmrequests = new PM_request();
            if($type=='profile-tpl' || $type=='edit-profile-tpl')
            {   
                $gids         = $pmrequests->profile_magic_get_user_field_value( $id, 'pm_group' );
		$ugid         = $pmrequests->pg_filter_users_group_ids( $gids );
		$gid          = $pmrequests->pg_get_primary_group_id( $ugid ); 
            }
            else
            {
                $gid = $id;
            }
            
            $group_options = array();
            $row  = $dbhandler->get_row( 'GROUPS', $gid );
            if ( isset( $row ) && isset( $row->group_options ) && $row->group_options != '' ) {
                    $group_options = maybe_unserialize( $row->group_options );
            }

            if ( isset($group_options['group_profile_template']) && ! empty( $group_options['group_profile_template'] )) {
                    $theme = $group_options['group_profile_template'];
            }
            
            
            
            return $theme;
        }
        
	public function profile_magic_messenger( $content ) {
		return $this->profile_magic_get_template_html( 'profile-magic-messenger', $content );
	}

	public function profile_magic_add_blog( $content ) {
		$dbhandler = new PM_DBhandler();
		if ( $dbhandler->get_global_option_value( 'pm_enable_blog', '1' ) == 1 ) :
			return $this->profile_magic_get_template_html( 'profile-magic-add-blog', $content );
			else :
				return '<div class="pm-login-box-error">' . esc_html__( 'Admin has disabled blog submissions. You cannot submit a new blog post at the moment.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
			endif;

	}

	public function profile_magic_user_search( $content ) {
		 return $this->profile_magic_get_template_html( 'profile-magic-search', $content );
	}

	public function profile_magic_login_form( $attributes, $content = null ) {
            $dbhandler = new PM_DBhandler();
		if ( class_exists( 'Registration_Magic' ) && $dbhandler->get_global_option_value('pm_login_form_from','rm')=='rm') :
			   return do_shortcode( '[RM_Login]' );
		 else :
			 return $this->profile_magic_get_template_html( 'profile-magic-login-form', $content, $attributes );
			 endif;

	}
	public function profile_magic_registration_form( $content ) {
			$pg_args              = array();
			$pg_args['form_type'] = 'register';

			$is_ip_blocked = $this->pm_blocked_ips( $pg_args );
		if ( $is_ip_blocked ) {
			return $is_ip_blocked;
		}
		if ( class_exists( 'Registration_Magic' ) ) :
			$group_id = filter_input( INPUT_GET, 'gid' );
			if ( ! isset( $group_id ) ) {
				if ( isset( $content['id'] ) ) {
					$group_id = $content['id'];
				}
				if ( isset( $content['gid'] ) ) {
					$group_id = $content['gid'];
				}
			}
			$pmrequests = new PM_request();
			$rm_form_id = $pmrequests->pm_check_if_group_associate_with_rm_form( $group_id );
			if ( $rm_form_id ) {
				return do_shortcode( "[RM_Forms id='" . $rm_form_id . "']" );
			} else {
				return $this->profile_magic_get_template_html( 'profile-magic-registration-form', $content );
			}
				else :
					return $this->profile_magic_get_template_html( 'profile-magic-registration-form', $content );
				endif;
	}

	public function profile_magic_group_view( $content ) {
			return $this->profile_magic_get_template_html( 'profile-magic-group', $content );
	}

	public function profile_magic_groups_view( $content ) {
			wp_enqueue_style( 'pm_material_icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all' );
			if ( is_array( $content ) ) {
				$content['pg_source'] = 'shortcode';
			} else {
				$content = array( 'pg_source' => 'shortcode' );
			}
			return $this->profile_magic_get_template_html( 'profile-magic-groups', $content );
	}

	public function profile_magic_profile_view( $content ) {
			return $this->profile_magic_get_template_html( 'profile-magic-profile', $content );
	}
        
        public function profile_magic_edit_profile_view($content)
        {
            return $this->profile_magic_get_template_html( 'profile-magic-edit-profile', $content );
        }

	public function profile_magic_forget_password( $attributes, $content = null ) {
                        $pmrequests         = new PM_request();
                        $pm_sanitizer = new PM_sanitizer;
                        $request = $pm_sanitizer->sanitize($_REQUEST);
			$default_attributes  = array( 'show_title' => false );
                        $attributes  = shortcode_atts( $default_attributes, $attributes );
			$attributes['lost_password_sent'] = isset( $request['checkemail'] ) && $request['checkemail'] == 'confirm';
			return $this->profile_magic_get_template_html( 'profile-magic-forget-password', $content );
	}

	public function profile_magic_password_reset_form( $attributes, $content = null ) {
			// Parse shortcode attributes
			$pmrequests         = new PM_request();
                        $pm_sanitizer = new PM_sanitizer;
			$default_attributes = array( 'show_title' => false );
			$attributes         = shortcode_atts( $default_attributes, $attributes );
                        $request = $pm_sanitizer->sanitize($_REQUEST);
		if ( is_user_logged_in() ) {
			return $this->profile_magic_get_template_html( 'profile-magic-password-reset-form', $content, $attributes );

		} else {
			if ( isset( $request['login'] ) && isset( $request['key'] ) ) {
					$attributes['login'] = $request['login'];
					$attributes['key']   = $request['key'];
						// Error messages
						$errors = array();
				if ( isset( $request['error'] ) ) {
					$error_codes = explode( ',', $request['error'] );
					foreach ( $error_codes as $code ) {
									$errors [] = $pmrequests->profile_magic_get_error_message( $code, $this->profile_magic );
					}
				}
						$attributes['errors'] = $errors;

						return $this->profile_magic_get_template_html( 'profile-magic-password-reset-form', $content, $attributes );
			} else {
					return esc_html__( 'Invalid password reset link.', 'profilegrid-user-profiles-groups-and-communities' );
			}
		}
	}

	public function profile_magic_do_password_reset() {
            $pm_sanitizer = new PM_sanitizer;
            $request = $pm_sanitizer->sanitize($_REQUEST);
            $post = $pm_sanitizer->sanitize($_POST);
		 $pmrequests = new PM_request();
		$checkurl    = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', '' );
		if ( ! empty( $checkurl ) ) {
			if ( isset($_SERVER['REQUEST_METHOD']) && 'POST' == sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )) {
					$rp_key   = $request['rp_key'];
					$rp_login = $request['rp_login'];

					$user = check_password_reset_key( $rp_key, $rp_login );

				if ( ! $user || is_wp_error( $user ) ) {

					if ( $user && $user->get_error_code() === 'expired_key' ) {
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'errors', 'expiredkey', $redirect_url );
					} else {
							$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
							$redirect_url = add_query_arg( 'errors', 'invalidkey', $redirect_url );
					}
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
				}

				if ( isset( $post['pass1'] ) ) {
					if ( $post['pass1'] != $post['pass2'] ) {
							// Passwords don't match
							$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( '/wp-login.php' ) );
							$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
							$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
							$redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							exit;
					}

					if ( empty( $post['pass1'] ) ) {
							// Password is empty
							$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( '/wp-login.php' ) );
							$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
							$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
							$redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							exit;
					}

					if ( strlen( $post['pass1'] ) < 7 ) {
							$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( '/wp-login.php' ) );
							$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
							$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
							$redirect_url = add_query_arg( 'error', 'password_too_short', $redirect_url );
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							exit;
					}

						// Parameter checks OK, reset password
						reset_password( $user, $post['pass1'] );
						do_action( 'profilegrid_user_change_password', $user->ID );
						delete_user_meta( $user->ID, 'pm_pw_reset_attempt' );
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'password', 'changed', $redirect_url );
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
				} else {
						 esc_html_e( 'Invalid request.', 'profilegrid-user-profiles-groups-and-communities' );
				}

					exit;
			}
		}
	}

	public function profile_magic_send_email_after_password_reset( $user, $new_pass ) {
		 $pmrequests = new PM_request();
		$pmemail     = new PM_Emails();
		$userid      = $user->ID;

		delete_user_meta( $userid, 'user_pass' );
		$gids = $pmrequests->profile_magic_get_user_field_value( $userid, 'pm_group' );
		$ugid = $pmrequests->pg_filter_users_group_ids( $gids );
		$gid  = $pmrequests->pg_get_primary_group_id( $ugid );
		if ( isset( $gid ) ) {
			$pmemail->pm_send_group_based_notification( $gid, $userid, 'on_password_change' );
		}
	}

	public function profile_magic_do_password_lost() {
		$pmrequests  = new PM_request();
                 $pm_sanitizer = new PM_sanitizer;
                 $post = $pm_sanitizer->sanitize($_POST);
                
		$is_page_set = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', '' );
		if ( ! empty( $is_page_set ) ) {
			// add code for reset password limit
			$dbhandler = new PM_DBhandler();
			if ( $dbhandler->get_global_option_value( 'pm_enable_reset_password_limit', '0' ) == 1 ) {
				if ( isset( $post['user_login'] ) ) {
					$login = $post['user_login'];
					if ( is_email($post['user_login'] ) ) {
                                            $user_data = get_user_by( 'email', $login );
					} else {
                                            $user_data = get_user_by( 'login', $login );
					}
						$user_id  = $user_data->ID;
						$attempt  = (int) $pmrequests->profile_magic_get_user_field_value( $user_id, 'pm_pw_reset_attempt' );
						$limit    = (int) $dbhandler->get_global_option_value( 'pm_reset_password_limit', '0' );
						$is_admin = user_can( intval( $user_id ), 'manage_options' );
					if ( $dbhandler->get_global_option_value( 'pm_disabled_admin_reset_password_limit', '0' ) == '1' && $is_admin ) {
						$reset_process = true;
					} else {
						if ( $limit <= $attempt ) {
							$reset_process = false;
						} else {
							update_user_meta( $user_id, 'pm_pw_reset_attempt', $attempt + 1 );
							$reset_process = true;
						}
					}
				}
			} else {
				$reset_process = true;
			}
			if ( isset($_SERVER['REQUEST_METHOD']) && 'POST' == sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) && $reset_process ) {
					$errors = retrieve_password();
				if ( is_wp_error( $errors ) ) {
						// Errors found
					if ( function_exists( 'is_wpe' ) ) :
						$forgot_pwd_url = '/wp-login.php?action=lostpassword&wpe-login=true';
						else :
							$forgot_pwd_url = '/wp-login.php?action=lostpassword';
							endif;
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( $forgot_pwd_url ) );
						$redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
				} else {
						// Email sent
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
				}
					wp_safe_redirect( esc_url_raw( $redirect_url ) );
					exit;
			} else {
				if ( function_exists( 'is_wpe' ) ) :
					$forgot_pwd_url = '/wp-login.php?action=lostpassword&wpe-login=true';
					else :
						$forgot_pwd_url = '/wp-login.php?action=lostpassword';
					endif;
					$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( $forgot_pwd_url ) );
					$redirect_url = add_query_arg( 'errors', 'pm_reset_pw_limit_exceed', $redirect_url );
					wp_safe_redirect( esc_url_raw( $redirect_url ) );
					exit;
			}
		}
	}

	public function profile_magice_retrieve_password_message( $message, $key, $user_login, $user_data ) {
            $dbhandler              = new PM_DBhandler();
            $msg = $dbhandler->get_global_option_value( 'pm_password_reset_email_content' );
            $user_email = !empty($user_data->user_email) ? $user_data->user_email : $user_login;
            if(!empty($msg)){
                add_filter( 'wp_mail_content_type', array($this,'reset_password_wp_email_content_type') );
                $msg = str_replace('{{pg_user_email}}',$user_email, $msg);
                $reset_url = site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );
                $msg = str_replace('{{pg_password_reset_link}}',$reset_url, $msg);
                
            }else{
            
                // Create new message
                $msg  = __( 'Hello!', 'profilegrid-user-profiles-groups-and-communities' ) . "\r\n\r\n";
                /* translators: %s: User email address. */
                $msg .= sprintf( __( 'You asked us to reset your password for your account using the email address %s.', 'profilegrid-user-profiles-groups-and-communities' ), $user_email ) . "\r\n\r\n";
                $msg .= __( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'profilegrid-user-profiles-groups-and-communities' ) . "\r\n\r\n";
                $msg .= __( 'To reset your password, visit the following address:', 'profilegrid-user-profiles-groups-and-communities' ) . "\r\n\r\n";
                $msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
                $msg .= __( 'Thanks!', 'profilegrid-user-profiles-groups-and-communities' ) . "\r\n";

            }
            return $msg;
	}
        
        public function reset_password_wp_email_content_type(){
            return 'text/html';
        }

	public function profile_magic_redirect_to_password_reset() {
		$pmrequests = new PM_request();
                $pm_sanitizer = new PM_sanitizer;
                $request = $pm_sanitizer->sanitize($_REQUEST);
		$checkurl   = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', '' );
		if ( ! empty( $checkurl ) ) {
			if ( isset($_SERVER['REQUEST_METHOD']) && 'GET' == sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) ) {
					// Verify key / login combo
					$user = check_password_reset_key($request['key'], $request['login'] );
				if ( ! $user || is_wp_error( $user ) ) {
					if ( $user && $user->get_error_code() === 'expired_key' ) {
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'login', 'expiredkey', $redirect_url );
					} else {
							$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
							$redirect_url = add_query_arg( 'login', 'invalidkey', $redirect_url );
					}
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
				}
				if ( function_exists( 'is_wpe' ) ) :
					$forgot_pwd_url = '/wp-login.php?action=lostpassword&wpe-login=true';
						else :
							$forgot_pwd_url = '/wp-login.php?action=lostpassword';
						endif;
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( $forgot_pwd_url ) );
						$redirect_url = add_query_arg( 'login', $request['login'], $redirect_url );
						$redirect_url = add_query_arg( 'key', $request['key'], $redirect_url );

						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
			}
		}
	}

	public function profile_magic_lost_password_form() {
			$pmrequests = new PM_request();
			$checkurl   = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', '' );
		if ( ! empty( $checkurl ) ) {
			$url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( '/wp-login.php?action=lostpassword' ) );
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	public function profile_magic_check_login_status( $user_login, $user ) {
			// Get user meta
			$pmrequests = new PM_request();
			$disabled   = get_user_meta( $user->ID, 'rm_user_status', true );

			// Is the use logging in disabled?
		if ( $disabled == '1' ) {
				// Clear cookies, a.k.a log user out
				 wp_clear_auth_cookie();
				// Build login URL and then redirect
				$login_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );

				$login_url      = add_query_arg( 'disabled', '1', $login_url );
				$gids           = get_user_meta( $user->ID, 'pm_group', true );
				$ugid           = $pmrequests->pg_filter_users_group_ids( $gids );
				$gid            = $pmrequests->pg_get_primary_group_id( $ugid );
				$payment_status = get_user_meta( $user->ID, 'pm_user_payment_status', true );
                                $group_payment_array = maybe_unserialize(get_user_meta($user->ID,'pm_group_payment_status', true));
                                if(!empty($group_payment_array))
                                foreach ($group_payment_array as $key=>$status)
                                {
                                    if($status=='pending')
                                    {
                                        $gid = $key;
                                        break;
                                    }
                                }
                                
				$price = $pmrequests->profile_magic_check_paid_group( $gid );
			if ( $price > 0 && $payment_status == 'pending' ) {
				$login_url = add_query_arg( 'errors', 'payment_pending', $login_url );
				$login_url = add_query_arg( 'id', $user->ID, $login_url );
			} else {
					$login_url = add_query_arg( 'errors', 'account_disabled', $login_url );
			}

				wp_safe_redirect( esc_url_raw( $login_url ) );
				exit;
		}
			update_user_meta( $user->ID, 'pm_last_active_time', time() );
			update_user_meta( $user->ID, 'pm_last_login', time() );
			update_user_meta( $user->ID, 'pm_login_status', 1 );
	}

	public function profile_magic_update_logout_status() {
		$dbhandler    = new PM_DBhandler();
		$pmrequests   = new PM_request();
		$current_user = wp_get_current_user();
		update_user_meta( $current_user->ID, 'pm_login_status', 0 );
		$this->profile_magic_set_logged_out_status( $current_user->ID );
		$redirect = $dbhandler->get_global_option_value( 'pm_redirect_after_logout', '0' );
		if ( $redirect != '0' ) {
			$redirect_url = get_permalink( $redirect );
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		} else {
			$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

	}
	public function profile_magic_login_notice( $message ) {
			// Show the error message if it seems to be a disabled user
                $pm_sanitizer = new PM_sanitizer;
                $get = $pm_sanitizer->sanitize($_GET);
		$pmrequests = new PM_request();
		if ( isset( $get['disabled'] ) && $get['disabled'] == 1 ) {
				$message = '<div id="login_error">' . esc_html__( 'Account disabled', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
		}
		if ( isset( $get['errors'] ) ) {
				$message = '<div id="login_error">' . $pmrequests->profile_magic_get_error_message( sanitize_text_field( wp_unslash( (string) filter_input( INPUT_GET, 'errors', FILTER_UNSAFE_RAW ) ) ), 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
		}
		if ( isset( $get['activated'] ) && $get['activated'] == 'success' ) {
				$message = '<div class="message">' . esc_html__( 'Your account has been successfully activated.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
		}
		if ( isset( $get['pgr'] ) && $get['pgr'] == 1 ) {
			$dbhandler = new PM_DBhandler();
			$allowed   = $dbhandler->get_global_option_value( 'pm_guest_allow_backend_login_screen', '1' );
			if ( $allowed == 0 ) {
				$message = '<div class="message">' . esc_html__( 'Dashboard login disabled for Guests users. Try logging in using log in form on the website.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
			}
		}

			return $message;
	}

	public function profile_magic_default_registration_url( $default_registration_url ) {
		   $pmrequests    = new PM_request();
			$register_url = $pmrequests->profile_magic_get_frontend_url( 'pm_default_regisration_page', site_url( '/wp-login.php?action=register' ) );
			return $register_url;
	}

	public function profile_magic_redirect_after_login( $redirect_to, $request, $user ) {
		   // is there a user to check?
			$pmrequests              = new PM_request();
			$pm_redirect_after_login = $pmrequests->profile_magic_get_frontend_url( 'pm_redirect_after_login', $redirect_to );
		if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
			if ( $user->has_cap( 'administrator' ) ) {
				$pm_redirect_after_login = admin_url();
			}
		}
			return $pm_redirect_after_login;
	}

	public function profile_magic_get_default_user_image( $size, $args ) {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
		if ( $avatarid == '' ) {
			$default_avatar_path = $path . '/partials/images/default-user.png';
			$pm_avatar           = '<img src="' . $default_avatar_path . '" width="' . $size . '" height="' . $size . '" class="user-profile-image" />';
		} else {
                        // FIX FOR WPDISCUZ ---START
                        if ( isset( $args['wpdiscuz_current_user'] ) ) {
                            unset( $args['wpdiscuz_current_user'] );
                        }
                        if ( isset( $args['wpdiscuz_comment'] ) ) {
                            unset( $args['wpdiscuz_comment'] );
                        }
                        // FIX FOR WPDISCUZ ---END
			$pm_avatar = wp_get_attachment_image( $avatarid, array( $size, $size ), false, $args );
		}

		return $pm_avatar;
	}
        
        public function profile_magic_get_default_user_image_src( $size, $args ) {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
		if ( $avatarid == '' ) {
			$default_avatar_path = $path . '/partials/images/default-user.png';
			
		} else {
			$pm_avatar = wp_get_attachment_image_src($avatarid);
                        $default_avatar_path = $pm_avatar[0];
		}

		return $default_avatar_path;
	}

	public function profile_magic_default_avatar( $avatar_defaults ) {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
		if ( $avatarid != '' ) {
			$src       = wp_get_attachment_image_src( $avatarid );
			$pm_avatar = $src[0];
			$avatar    = get_option( 'avatar_default' );
			if ( $avatar != $pm_avatar ) {
				update_option( 'avatar_default', $pm_avatar );
			}

			$avatar_defaults[ $pm_avatar ] = 'Default Avatar';
		}

		return $avatar_defaults;

	}
        
        public function profile_magic_bp_core_fetch_avatar_url($avatar_url, $params )
        {
            $user_id = absint( $params['item_id'] );
            return $this->profile_magic_get_avatar_url($avatar_url,$user_id,$params);
        }
        
        public function profile_magic_bp_core_fetch_avatar($avatar_img_tag, $params)
        {
            $user_id = absint( $params['item_id'] );
            $width = ($params['width'])?$params['width']:100;
            return  $this->profile_magic_get_avatar($avatar_img_tag, $user_id,$width);
        }
        
   
        
        public function profile_magic_get_avatar_url($avatar,$id_or_email,$args)
        {
            $path       = plugin_dir_url( __FILE__ );
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		if ( is_numeric( $id_or_email ) ) {
			$id   = (int) $id_or_email;
			$user = get_user_by( 'id', $id );
		} elseif ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
					$id   = (int) $id_or_email->user_id;
					$user = get_user_by( 'id', $id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email );
		}

		if ( $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 1 ) {
			$avatar;
		} else {
			$avatar = $this->profile_magic_get_default_user_image_src( 'thumbnail', $args );
		}

		if ( isset( $user ) && ! empty( $user ) ) {
			$avatarid = $pmrequests->profile_magic_get_user_field_value( $user->data->ID, 'pm_user_avatar' );
		}
		if ( isset( $avatarid ) && $avatarid != '' ) {
			if ( isset( $args['wpdiscuz_current_user'] ) ) {
				unset( $args['wpdiscuz_current_user'] );
			}
                        if ( isset( $args['wpdiscuz_comment'] ) ) {
                            unset( $args['wpdiscuz_comment'] );
                        }
				 $pm_avatar = wp_get_attachment_image_src( $avatarid);
			if ( ! empty( $pm_avatar ) ) {
					  $avatar =  $pm_avatar[0];
                                          //echo $avatar;die;
			} 
		} 
                return $avatar;
        }
        
	public function profile_magic_get_avatar( $avatar, $id_or_email, $size, $default='', $alt='', $args=array() ) {
		$path       = plugin_dir_url( __FILE__ );
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		if ( is_numeric( $id_or_email ) ) {
			$id   = (int) $id_or_email;
			$user = get_user_by( 'id', $id );
		} elseif ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
					$id   = (int) $id_or_email->user_id;
					$user = get_user_by( 'id', $id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email );
		}

		if ( $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 1 ) {
			$default_avatar = $avatar;
		} else {
			$default_avatar = $this->profile_magic_get_default_user_image( $size, $args );
		}

		if ( isset( $user ) && ! empty( $user ) ) {
			$avatarid = $pmrequests->profile_magic_get_user_field_value( $user->data->ID, 'pm_user_avatar' );
		}
		if ( isset( $avatarid ) && $avatarid != '' ) {
			if ( isset( $args['wpdiscuz_current_user'] ) ) {
				unset( $args['wpdiscuz_current_user'] );
			}
                        if ( isset( $args['wpdiscuz_comment'] ) ) {
                            unset( $args['wpdiscuz_comment'] );
                        }
				 $pm_avatar = wp_get_attachment_image( $avatarid, array( $size, $size ), false, $args );
			if ( ! empty( $pm_avatar ) ) {
					  return $pm_avatar;
			} else {
				if ( is_multisite() ) {
					  $subsites = get_sites();
					  $found    = false;
					foreach ( $subsites as $subsite ) {
						if ( ! $found ) {
							switch_to_blog( $subsite->blog_id );
							$pm_avatar = wp_get_attachment_image( $avatarid, array( $size, $size ), false, $args );
							if ( ! empty( $pm_avatar ) ) {
								$found = true;
								
							}
                                                        restore_current_blog();
						}
					}
					  return $pm_avatar;

				} else {
					if ( isset( $user ) && ! empty( $user ) && is_super_admin( $user->ID ) && $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 0 ) {
						 $default_avatar_path = $path . '/partials/images/admin-default-user.png';
						 $default_avatar      = '<img src="' . $default_avatar_path . '" width="' . $size . '" height="' . $size . '" class="user-profile-image" />';
					}
						  return $default_avatar;
				}
			}
		} else {
			if ( isset( $user ) && ! empty( $user ) && is_super_admin( $user->ID ) && $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 0 ) {
					$default_avatar_path = $path . '/partials/images/admin-default-user.png';
					$default_avatar      = '<img src="' . $default_avatar_path . '" width="' . $size . '" height="' . $size . '" class="user-profile-image" />';
			}
				return $default_avatar;
		}
	}

	public function pm_update_user_profile() {
		$pm_sanitizer = new PM_sanitizer;
		$nonce        = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( wp_unslash( $nonce ), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		$post            = $pm_sanitizer->sanitize( $_POST );
		$target_user_id  = isset( $post['user_id'] ) ? absint( $post['user_id'] ) : 0;
		$current_user_id = get_current_user_id();
		if ( $target_user_id <= 0 || ( $current_user_id !== $target_user_id && ! current_user_can( 'edit_user', $target_user_id ) ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$allowed_meta_keys = array(
			'first_name',
			'last_name',
			'description',
			'pm_profile_privacy',
			'pm_hide_my_profile',
			'pm_user_avatar',
			'pm_cover_image',
		);
		$meta_key          = isset( $post['user_meta'] ) ? sanitize_key( $post['user_meta'] ) : '';
		if ( ! in_array( $meta_key, $allowed_meta_keys, true ) ) {
			wp_send_json_error( 'Invalid field', 400 );
		}

		$meta_value = isset( $post['user_meta_value'] ) ? $post['user_meta_value'] : '';
		$update     = update_user_meta( $target_user_id, $meta_key, $meta_value );
		echo esc_html( $update );
		die;
	}

	public function pm_send_change_password_email() {
		$current_user = wp_get_current_user();
		$userid       = $current_user->ID;
		$pmrequests   = new PM_request();
		$pmemail      = new PM_Emails();
		$gids         = $pmrequests->profile_magic_get_user_field_value( $userid, 'pm_group' );
		$ugid         = $pmrequests->pg_filter_users_group_ids( $gids );
		$gid          = $pmrequests->pg_get_primary_group_id( $ugid );
		$pmemail->pm_send_group_based_notification( $gid, $userid, 'on_password_change' );
	}

	public function pm_send_change_pass_email() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$current_user = wp_get_current_user();
		$userid       = $current_user->ID;
		$pmrequests   = new PM_request();
		$pmemail      = new PM_Emails();
		$gids         = $pmrequests->profile_magic_get_user_field_value( $userid, 'pm_group' );
		$ugid         = $pmrequests->pg_filter_users_group_ids( $gids );
		$gid          = $pmrequests->pg_get_primary_group_id( $ugid );
		$pmemail->pm_send_group_based_notification( $gid, $userid, 'on_password_change' );
		die;
	}

	public function pm_advance_search_get_search_fields_by_gid() {
		$gid            = filter_input( INPUT_POST, 'gid' );
		  $match_fields = filter_input( INPUT_POST, 'match_fields' );
		  $dbhandler    = new PM_DBhandler();

		if ( $gid == '' ) {
			$additional = " field_type not in('file', 'user_avatar', 'heading', 'paragraph', 'confirm_pass', 'user_pass','user_url','user_name')";
			$fields     = $dbhandler->get_all_result( 'FIELDS', '*', 1, 'results', 0, false, 'ordering', false, $additional );
		} else {
				 $additional = "and field_type not in('file', 'user_avatar', 'heading', 'paragraph', 'confirm_pass', 'user_pass','user_url','user_name')";
			  $fields        = $dbhandler->get_all_result( 'FIELDS', '*', array( 'associate_group' => $gid ), 'results', 0, false, 'ordering', false, $additional );
		}
			  $resp = ' ';
		foreach ( $fields as $field ) {
				$ischecked = ' ';
			if ( $field->field_options != '' ) {
					 $field_options = maybe_unserialize( $field->field_options );
			}
			if ( is_array( $match_fields ) && in_array( $field->field_key, $match_fields ) ) {
					  $ischecked = 'checked';
			} elseif ( $field->field_key == $match_fields ) {
				 $ischecked = 'checked';
			} else {
				$ischecked = ' ';
			}
			if ( isset( $field_options['display_on_search'] ) && ( $field_options['display_on_search'] == 1 ) ) {
				if ( isset( $field_options['admin_only'] ) && $field_options['admin_only'] == '1' && ! is_super_admin() ) {
                                    continue;
				}
				$field_html = ' <li class="pm-filter-item"><input class="pm-filter-checkbox" type="checkbox" name="match_fields" onclick="pm_advance_user_search()" ' . $ischecked . ' value="' . $field->field_key . '" ><span class="pm-filter-value">' . esc_html( $field->field_name ) . '</span></li>';
                                $resp .= apply_filters('pg_advance_search_field_html',$field_html, $field);
                        }
		}
					echo wp_kses( $resp, array(
                                                'li' => array(
                                                    'class' => array(),
                                                ),
                                                'input' => array(
                                                    'class' => array(),
                                                    'type' => array(),
                                                    'name' => array(),
                                                    'onclick' => array(),
                                                    'checked' => array(),
                                                    'value' => array(),
                                                ),
                                                'select' => array(
                                                    'id' => array(),
                                                    'data-placeholder' => array(),
                                                    'onchange' => array(),
                                                    'class' => array(),
                                                    'name' => array(),
                                                    'onclick' => array(),
                                                ),
                                                'option' => array(
                                                    'class' => array(),
                                                    'value' => array(),
                                                ),
                                                'label' => array(
                                                    'class' => array(),
                                                ),
                                                'div' => array(
                                                    'class' => array(),
                                                ),
                                                'span' => array(
                                                    'class' => array(),
                                                ),
                                            ) );
					die;

	}

	public function pm_messenger_show_thread_user() {
		$pmmessenger = new PM_Messenger();
                $nonce = filter_input( INPUT_POST, 'nonce' );
                if ( !isset( $nonce ) || ! wp_verify_nonce( wp_unslash($nonce), 'ajax-nonce' ) ) {
                    die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
                }
		$uid         = filter_input( INPUT_POST, 'uid' );
		$return      = $pmmessenger->pm_messenger_show_thread_user( $uid );
		 $return     = wp_json_encode( $return );
		echo wp_kses_post($return);
		die;
	}


	public function pm_messenger_show_threads() {
		$pmmessenger = new ProfileMagic_Chat();
                $nonce = filter_input( INPUT_POST, 'nonce' );
                if ( !isset( $nonce ) || ! wp_verify_nonce( wp_unslash($nonce), 'ajax-nonce' ) ) {
                    die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
                }
		 $active_tid = '';
if ( isset( $_POST['tid'] ) ) {
    $active_tid = sanitize_text_field( wp_unslash( $_POST['tid'] ) );
}
		$result      = $pmmessenger->pm_messenger_show_threads( $active_tid );
		echo wp_kses_post( $result );
		die;

	}

	public function pm_messenger_send_new_message() {
            $pm_sanitizer = new PM_sanitizer;
            $retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
            if (!wp_verify_nonce($retrieved_nonce, 'pg_send_new_message' ) ) die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            $post = $pm_sanitizer->sanitize($_POST);
            $pmmessenger = new ProfileMagic_Chat();
		if ( isset( $post ) ) {
                        if(isset($post['rid']) && !empty($post['rid'])){
                            $rid     = intval($post['rid']);
                        }else{
                            $rid = 0;
                        }
                        
                        if(isset($post['mid']) && !empty($post['mid'])){
                            $mid     = intval($post['mid']);
                        }else{
                            $mid = '';
                        }
                        if(isset($post['content']) && !empty($post['content'])){
                            $content = wp_kses_post($post['content']);
                        }else{
                            $content = '';
                        }
                        
                        if(isset($post['tid'])){
                            $tid = intval($post['tid']);
                        }else{
                            $tid = 0;
                        }
                        //echo $rid.' '.$content.' '.$tid;
			if ( $mid == '' ) {
				if ($tid == 0){
					if ( $rid > 0 && $content !== '' ) {
						$result = $pmmessenger->pm_messenger_send_new_message( $rid, $content );
					} else {
						$result = __( 'not sent', 'profilegrid-user-profiles-groups-and-communities' );
					}
			}else{
				if ( $rid > 0 && $content !== '' ) {
					$result = $pmmessenger->pm_messenger_send_new_message( $rid, $content,$tid );
				} else {
					$result = __( 'not sent', 'profilegrid-user-profiles-groups-and-communities' );
				}
				}
			} else {
				$result = $pmmessenger->pm_messenger_send_edit_message( $rid, $mid, $content );
			}
			echo wp_kses_post( $result );
		} else {
			 esc_html_e( ' no post created', 'profilegrid-user-profiles-groups-and-communities' );
		}
		die;
	}


	public function pm_messenger_show_messages() {
		$pmmessenger = new ProfileMagic_Chat();
                $pmrequests   = new PM_request();
		$tid         = filter_input( INPUT_POST, 'tid' );
		$tid         = absint( $tid );
		$loadnum     = filter_input( INPUT_POST, 'loadnum' );
		$timezone    = filter_input( INPUT_POST, 'timezone' );
		$nonce    = filter_input( INPUT_POST, 'nonce' );
		
		if ( !isset($nonce ) || ! wp_verify_nonce( wp_unslash($nonce), 'ajax-nonce' ) ) {
			
			die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
		}
		$return = $pmmessenger->pm_messenger_show_messages( $tid, $loadnum, $timezone );
		// update_option('pm_update_status_test', 'yoyo');
                
                $allowed_html = $pmrequests->pg_allowed_html_wp_kses();
		echo wp_kses($return,$allowed_html);
                
		die;
	}

        

	public function pm_get_messenger_notification() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmmessenger = new PM_Messenger();
		$timestamp   = filter_input( INPUT_GET, 'timestamp', FILTER_VALIDATE_INT );
		$activity    = filter_input( INPUT_GET, 'activity', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$tid         = absint( filter_input( INPUT_GET, 'tid', FILTER_VALIDATE_INT ) );
		$uid         = get_current_user_id();
		if ( $tid > 0 ) {
			$thread = $this->pg_get_authorized_thread( $tid, $uid );
			if ( false === $thread ) {
				wp_send_json_error( 'Unauthorized', 403 );
			}
		}
		if ( $tid != 0 ) {
			$return = $pmmessenger->pm_get_messenger_notification( $timestamp, $activity, $tid );
			echo wp_kses_post( $return );
		}
		die;
	}




	public function pm_messenger_delete_threads() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$uid         = get_current_user_id();
		$pmmessenger = new ProfileMagic_Chat();
		$tid         = absint( filter_input( INPUT_POST, 'tid', FILTER_VALIDATE_INT ) );
		$mid         = absint( filter_input( INPUT_POST, 'mid', FILTER_VALIDATE_INT ) );
		$thread      = $this->pg_get_authorized_thread( $tid, $uid );
		if ( false === $thread ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		$delete      = $pmmessenger->pm_messenger_delete_threads( $tid, $uid, $mid );
		echo wp_kses_post( $delete );
		die;
	}

	public function pm_messenger_notification_extra_data() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmmessenger = new ProfileMagic_Chat();
		$return      = $pmmessenger->pm_messenger_notification_extra_data();
		echo wp_kses_post( $return );
		die;
	}

	public function pm_unread_message_summary() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'Not logged in', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		check_ajax_referer( 'ajax-nonce', 'nonce' );

		wp_send_json_success( $this->pg_get_unread_message_summary_payload( get_current_user_id() ) );
	}

	private function pg_get_unread_message_summary_payload( $uid ) {
		$pmrequests = new PM_request();
		$summary    = $pmrequests->pm_get_unread_message_summary( $uid );
		$count      = isset( $summary['count'] ) ? (int) $summary['count'] : 0;
		$latest_ts  = isset( $summary['latest'] ) ? (int) $summary['latest'] : 0;
		$latest_tid = isset( $summary['latest_tid'] ) ? (int) $summary['latest_tid'] : 0;
		$latest_rid = isset( $summary['latest_rid'] ) ? (int) $summary['latest_rid'] : 0;
		$dismissed  = (int) get_user_meta( $uid, 'pg_msg_unread_dismissed_at', true );

		return array(
			'count'     => $count,
			'latest_ts' => $latest_ts,
			'latest_tid'=> $latest_tid,
			'latest_rid'=> $latest_rid,
			'dismissed' => $dismissed,
		);
	}

	public function pm_autocomplete_user_search() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$dbhandler          = new PM_DBhandler();
		$pmrequests         = new PM_request();
		$uid                = get_current_user_id();
		$name               = filter_input( INPUT_POST, 'name' );
		$search             = trim( $name );
		$meta_args          = array( 'status' => '0' );
		$limit              = '';
                $include            = apply_filters('pm_allowed_users_for_messaging',array());
		$hide_users         = $pmrequests->pm_get_hide_users_array();
                $exclude            = apply_filters( 'pm_restricted_users_to_messaging_search', $hide_users );
		$exclude[]          = $uid;
		$meta_query_array   = $pmrequests->pm_get_user_meta_query( $meta_args );
		$meta_query_array[] = array(
			'relation' => 'OR',
			array(
				'key'     => 'first_name',
				'value'   => $search,
				'compare' => 'LIKE',
			),
			array(
				'key'     => 'last_name',
				'value'   => $search,
				'compare' => 'LIKE',
			),
		);

		$users = $dbhandler->pm_get_all_users( '', $meta_query_array, '', 0, $limit, 'ASC', 'ID', $exclude );
		if ( empty( $users ) ) {
			 $meta_query                   = $pmrequests->pm_get_user_meta_query( $meta_args );
			 $meta_query['search_columns'] = array( 'user_login', 'user_nicename', 'user_email' );
			 $users                        = $dbhandler->pm_get_all_users( $search, $meta_query, '', 0, $limit, 'ASC', 'ID', $exclude );
			// print_r($meta_query);die;
		}
		// print_r($meta_query_array);die;
		$return = array();
		if ( ! empty( $users ) ) {

			foreach ( $users as $user ) {
                                if(!empty($include) && !in_array($user->ID, $include))
                                {
                                    continue;
                                }
				if ( $user->ID != $uid ) {
					$user_info['id']    = $user->ID;
					$user_info['label'] = wp_strip_all_tags( $pmrequests->pm_get_display_name( $user->ID ) );
					$return[]           = $user_info;
				}
			}
		} else {
			$user_info['id']        = '';
				$user_info['label'] = esc_html__( 'No User Found', 'profilegrid-user-profiles-groups-and-communities' );
				$return[]           = $user_info;
		}
		  $data = wp_json_encode( $return );
		  echo wp_kses_post($data);
		  die;
	}

	public function pm_advance_user_search() {
            $pm_sanitizer = new PM_sanitizer;
            $nonce             = filter_input( INPUT_POST, 'nonce' );
            $search_form_nonce = filter_input( INPUT_POST, 'pm_search_nonce' );
            $nonce_ok          = false;

            if ( isset( $nonce ) && wp_verify_nonce( wp_unslash( $nonce ), 'ajax-nonce' ) ) {
                $nonce_ok = true;
            } elseif ( isset( $search_form_nonce ) && wp_verify_nonce( wp_unslash( $search_form_nonce ), 'pm_search_action' ) ) {
                $nonce_ok = true;
            }

            if ( ! $nonce_ok ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
            $post = $pm_sanitizer->sanitize($_POST);
		  $dbhandler    = new PM_DBhandler();
			$pmrequests = new PM_request();
                        $allowed_html = $pmrequests->pg_allowed_html_wp_kses();
			$pagenum    = filter_input( INPUT_POST, 'pagenum' );
			$sortby     = filter_input( INPUT_POST, 'member_sort_by' );
                        $search_term = isset( $post['pm_search'] ) ? trim( (string) $post['pm_search'] ) : '';

		switch ( $sortby ) {
			case 'name_asc':
				$sortby = 'display_name';
				$order  = 'ASC';
				break;
			case 'name_desc':
				$sortby = 'display_name';
				$order  = 'DESC';
				break;
			case 'latest_first':
				$sortby = 'registered';
				$order  = 'DESC';
				break;
			case 'oldest_first':
				  $sortby = 'registered';
				  $order  = 'ASC';
				break;
			case 'suspended':
				$sortby        = 'registered';
				$order         = 'DESC';
				$get['status'] = '1';
				break;
			case 'first_name_asc':
				$sortby = 'first_name';
				$order  = 'ASC';
				break;
			case 'first_name_desc':
				$sortby = 'first_name';
				$order  = 'DESC';
				break;
			case 'last_name_asc':
				$sortby = 'last_name';
				$order  = 'ASC';
				break;
			case 'last_name_desc':
				$sortby = 'last_name';
				$order  = 'DESC';
				break;
			default:
				$sortby = 'display_name';
				$order  = 'ASC';
				break;

		}

			$gid = filter_input( INPUT_POST, 'gid' );

		if ( isset( $post['match_fields'] ) ) {

			$search           = '';
                        $meta_query_array = $pmrequests->pm_get_user_advance_search_meta_query( $post );

		} else {
                        $pm_default_search_field = $dbhandler->get_global_option_value('pm_default_search_field','first_name');
                        if($pm_default_search_field=='default'):
                            $search = $search_term;
                        else:
                            $search ='';
                        endif;
			$meta_query_array = $pmrequests->pm_get_user_meta_query($post);

		}
                        $current_user = wp_get_current_user();
			$pagenum      = isset( $pagenum ) ? absint( $pagenum ) : 1;
                        // $limit = 20; // number of rows in page
			$limit        = $dbhandler->get_global_option_value( 'pm_number_of_users_on_search_page', '20' );
			$offset       = ( $pagenum - 1 ) * $limit;
			$date_query   = $pmrequests->pm_get_user_date_query($post);
			$exclude      = $pmrequests->pm_get_hide_users_array();
			$user_query   = $dbhandler->pm_get_all_users_ajax( $search, $meta_query_array, '', $offset, $limit, $order, $sortby, $exclude, $date_query );
                        $total_users  = $user_query->get_total();
			$users        = $user_query->get_results();

                if ( empty( $users ) && ! isset( $post['match_fields'] ) && $search === '' && $search_term !== '' ) {
                        $fallback_post                         = $post;
                        unset( $fallback_post['pm_search'], $fallback_post['field_value'] );
                        $fallback_meta_query                   = $pmrequests->pm_get_user_meta_query( $fallback_post );
                        $fallback_meta_query['search']         = $search_term;
                        $fallback_meta_query['search_columns'] = array( 'user_login', 'user_nicename', 'user_email', 'display_name' );
                        $user_query                            = $dbhandler->pm_get_all_users_ajax( '', $fallback_meta_query, '', $offset, $limit, $order, $sortby, $exclude, $date_query );
                        $total_users                           = $user_query->get_total();
                        $users                                 = $user_query->get_results();
                }

			$num_of_pages = ceil( $total_users / $limit );
			$pagination   = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );
			$user_info    = array();
			$return       = '';
                        $show_total_count = $dbhandler->get_global_option_value('pm_show_users_count','1');
                        $image_size = $dbhandler->get_global_option_value('pm_profile_image_size_on_search_page','100');
		if ( isset( $total_users ) && $show_total_count==1 ) {
			$return .= '<div  class="pm-all-members pm-dbfl pm-pad10">'
				. esc_html__( 'Total ', 'profilegrid-user-profiles-groups-and-communities' ) . '<b>' . $total_users
				. '</b>' . esc_html__( ' members', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>';
		}

		if ( ! empty( $users ) ) {
                        $return .= '<div class="pg-search-result-wrapper">';
			foreach ( $users as $user ) {
						$user_info['avatar']      = get_avatar(
							$user->user_email,
							$image_size,
							'',
							'',
							array(
								'class'         => 'pm-user-profile',
								'force_display' => true,
                                                            'width' =>$image_size,
                                                            'height' => $image_size,
                                                            'style'=>"width:".$image_size."px;height:".$image_size."px"
							)
						);
						$user_info['id']          = $user->ID;
						$profile_url              = $pmrequests->pm_get_user_profile_url( $user->ID );
						$user_info['profile_url'] = $profile_url;
						$user_info['name']        = $pmrequests->pm_get_display_name( $user->ID, true );
						$group_leader_class       = '';
				if ( isset( $user_info['group_leader'] ) ) {
					$group_leader_class = 'pm-group-leader-medium';
				}
						$return .= "<div class=\"search_result pm-user pm-difl $group_leader_class \"> " .
						'<a href=' . $user_info['profile_url'] . '>'
											. $user_info['avatar']
											. '<div class="pm-user-name pm-dbfl pm-clip">' . $user_info['name'] . '</div></a></div>';

			}
                        $return .= '</div>';
		} else {
			$return = '<div class="pm-message pm-dbfl pm-pad10">'
					. esc_html__( 'Sorry, your search returned no results.', 'profilegrid-user-profiles-groups-and-communities' )
					. '</div>';
		}

		if ( isset( $pagination ) ) {
			$return .= '<div class="pm_clear"></div>' . $pagination;
		}

			  echo wp_kses( $return,$allowed_html );
			die;
	}

	public function pm_change_frontend_user_pass() {
            $pm_sanitizer = new PM_sanitizer;
            
             if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'ajax-nonce')) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
            $post = $pm_sanitizer->sanitize($_POST);
			$textdomain   = $this->profile_magic;
			$pmrequests   = new PM_request();
			$current_user = wp_get_current_user();
		if ( isset( $current_user->ID ) && ! empty( $post['pass1'] ) ) {
			if ( strlen( $post['pass1'] ) < 7 ) {
				$pm_error = esc_html__( 'Password is too short. At least 7 characters please!', 'profilegrid-user-profiles-groups-and-communities' );
			} else {
				if ( $post['pass1'] == $post['pass2'] ) {

					delete_user_meta( $current_user->ID, 'user_pass' );
					$this->pm_send_change_password_email();
					$this->profile_magic_set_logged_out_status( $current_user->ID );
					wp_set_password( $post['pass1'], $current_user->ID );
					do_action( 'profilegrid_user_change_password', $current_user->ID );
					$pm_error = true;

				} else {
						$pm_error = esc_html__( 'New Password and Repeat password does not match.', 'profilegrid-user-profiles-groups-and-communities' );
				}
			}
		} else {
				$pm_error = esc_html__( 'Password didn\'t changed.', 'profilegrid-user-profiles-groups-and-communities' );
		}
			echo wp_kses_post( $pm_error );
			die;
	}

	public function profile_magic_recapcha_field( $gid ) {
		$dbhandler    = new PM_DBhandler();
		$pmrequests   = new PM_request();
		$html_creator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		if ( $pmrequests->profile_magic_show_captcha( 'pm_enable_recaptcha_in_reg' ) ) {
			  $lang = $dbhandler->get_global_option_value( 'pm_recaptcha_lang', 'en' );
			  wp_enqueue_script( 'crf-recaptcha-api', "https://www.google.com/recaptcha/api.js?hl=$lang" );
			  $html_creator->pm_get_captcha_html();
		}
	}

	public function pm_submit_user_registration( $post, $files, $server, $gid, $fields, $user_id, $textdomain ) {
		   $dbhandler              = new PM_DBhandler();
			$pmemails              = new PM_Emails();
			$pmrequests            = new PM_request();
			$pm_admin_notification = $dbhandler->get_global_option_value( 'pm_admin_notification', 0 );
		if ( $pm_admin_notification == 1 ) {
				$exclude             = array( 'user_avatar', 'file', 'user_pass', 'confirm_pass', 'heading', 'paragraph' );
				$subject             = esc_html__( 'New User Created', 'profilegrid-user-profiles-groups-and-communities' );
				$admin_email_subject = $dbhandler->get_global_option_value( 'pm_new_user_create_admin_email_subject', $subject );
				$admin_message       = '<p>' . esc_html__( 'New user created', 'profilegrid-user-profiles-groups-and-communities' ) . '</p>';
				$admin_email_message = $dbhandler->get_global_option_value( 'pm_new_user_create_admin_email_body', $admin_message );
				$attached_email_body = $dbhandler->get_global_option_value( 'pm_attached_submission_data_admin_email_body', 0 );
			if ( $attached_email_body == 1 ) {
				$admin_html           = $pmrequests->pm_admin_notification_message_html( $post, $gid, $fields, $exclude );
				$admin_email_message .= $admin_html;
			}
				$admin_email_message = $pmemails->pm_filter_email_content( $admin_email_message, $user_id );
				$pmemails->pm_send_admin_notification( $admin_email_subject, $admin_email_message );
		}

			// $pmemails->pm_send_group_based_notification($gid,$user_id,'on_registration');
			$autoapproval              = $dbhandler->get_global_option_value( 'pm_auto_approval', 0 );
			$send_user_activation_link = $dbhandler->get_global_option_value( 'pm_send_user_activation_link', 0 );
		if ( $autoapproval == '1' && $pmrequests->profile_magic_check_paid_group( $gid ) == '0' ) {
			if ( $send_user_activation_link == '1' ) {
				$userstatus = '1';
				$pmrequests->pm_update_user_activation_code( $user_id );
				$pmemails->pm_send_activation_link( $user_id, $this->profile_magic );
			} else {
				$userstatus = '0';
				$pmemails->pm_send_group_based_notification( $gid, $user_id, 'on_user_activate' );
			}
		} else {
			$userstatus                = '1';
			$accnt_review_notification = $dbhandler->get_global_option_value( 'pm_admin_account_review_notification', 0 );
			if ( $pm_admin_notification == 1 && $accnt_review_notification == 1 ) {
				$review_subject = $dbhandler->get_global_option_value( 'pm_account_review_email_subject', esc_html__( 'New user awaiting review', 'profilegrid-user-profiles-groups-and-communities' ) );
				$review_body    = $dbhandler->get_global_option_value( 'pm_account_review_email_body', esc_html__( '{{display_name}} has just registered in {{group_name}} group and waiting to be reviewed. To review this member please click the following link: {{profile_link}}', 'profilegrid-user-profiles-groups-and-communities' ) );
				$review_body    = $pmemails->pm_filter_email_content( $review_body, $user_id, false, $gid );
				$pmemails->pm_send_admin_notification( $review_subject, $review_body );
			}
		}
			update_user_meta( $user_id, 'rm_user_status', $userstatus );

	}

	public function pm_submit_user_registration_paypal( $post, $files, $server, $gid, $fields, $user_id, $textdomain ) {
			 $pmrequests        = new PM_request();
			 $pm_payapl_request = new PM_paypal_request();
		if ( $pmrequests->profile_magic_check_paid_group( $gid ) > 0 ) {

			switch ( $post['pm_payment_method'] ) {
				case 'paypal':
					$pm_payapl_request->profile_magic_payment_process( $post, $post['action'], $gid, $user_id, $textdomain );
					break;
				default:
					 do_action( 'profile_magic_custom_payment_process', $post, $gid, $user_id );
					break;
			}
		}
	}

	public function pm_join_paid_group_payment( $post, $gid, $user_id ) {
		   $pmrequests         = new PM_request();
			$pm_payapl_request = new PM_paypal_request();
		if ( $pmrequests->profile_magic_check_paid_group( $gid ) > 0 && isset( $post['pm_payment_method'] ) ) {

			switch ( $post['pm_payment_method'] ) {
				case 'paypal':
					$pm_payapl_request->profile_magic_join_group_payment_process( $post, $post['action'], $gid, $user_id );
					break;
				default:
					do_action( 'profile_magic_join_group_custom_payment_process', $post, $gid, $user_id );
					break;
			}
		}
	}

	public function pm_payment_process( $post, $request, $gid, $textdomain ) {
		$dbhandler         = new PM_DBhandler();
		$pmrequests        = new PM_request();
		$pm_payapl_request = new PM_paypal_request();
		if ( isset( $request['action'] ) && $request['action'] != 'process' ) {
			if ( isset( $request['uid'] ) ) {
				$uid = $request['uid'];
			} else {
				$uid = false;
			}

			if ( $request['action'] == 're_process' ) {
								$additional  = "uid = $uid";
								$payment_log = $dbhandler->get_all_result( 'PAYPAL_LOG', '*', 1, 'results', 0, 1, 'id', 'DESC', $additional );
				if ( isset( $payment_log ) ) {
					$payment_method = $payment_log[0]->pay_processor;
				} else {
					$payment_method = 'paypal';
				}

				if ( $payment_method == 'paypal' ) {
					$pm_payapl_request->profile_magic_repayment_process( $uid, $gid );
				} else {
					do_action( 'profile_magic_custom_repayment_process', $uid, $gid, $payment_method );
				}
			} else {
				$pm_payapl_request->profile_magic_payment_process( $post, $request['action'], $gid, $uid, $textdomain );
			}

			return false;
		}

	}

	public function pm_upload_image() {
		 require 'partials/crop.php';
		die;
	}

	public function pm_upload_cover_image() {
		require 'partials/coverimg_crop.php';
		die;
	}

	public function pg_create_post_type() {
            $dbhandler         = new PM_DBhandler();
            $pm_enable_blog = $dbhandler->get_global_option_value( 'pm_enable_blog', 0 );
            if($pm_enable_blog==1){
                    register_post_type(
                            'profilegrid_blogs',
                            array(
                                    'labels'        => array(
                                            'name'          => esc_html__( 'User Blogs', 'profilegrid-user-profiles-groups-and-communities' ),
                                            'singular_name' => esc_html__( 'User Blog', 'profilegrid-user-profiles-groups-and-communities' ),
                                    ),
                                    'public'        => true,
                                    'has_archive'   => false,
                                    'rewrite'       => array( 'slug' => 'profilegrid_blogs' ),
                                    'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
                                    'taxonomies'    => array( 'blog_tag' ),
                                    'show_in_rest'  => true,
                                    'menu_position' => 85,
                                     // 'menu_icon' =>'dashicons-testimonial'
                            )
                    );

                    add_theme_support( 'post-thumbnails' );
                    register_taxonomy('blog_tag', ['profilegrid_blogs'], [

                        'label' => esc_html__('Tags', 'profilegrid-user-profiles-groups-and-communities'),
                        'hierarchical' => true,
                        'rewrite' => ['slug' => 'blog_tag'],
                        'show_admin_column' => true,
                        'show_in_rest' => true,
                        'labels' => [
                                'singular_name' => esc_html__('Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'all_items' => esc_html__('All Tags', 'profilegrid-user-profiles-groups-and-communities'),
                                'edit_item' => esc_html__('Edit Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'view_item' => esc_html__('View Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'update_item' => esc_html__('Update Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'add_new_item' => esc_html__('Add New Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'new_item_name' => esc_html__('New Tag Name', 'profilegrid-user-profiles-groups-and-communities'),
                                'search_items' => esc_html__('Search Tags', 'profilegrid-user-profiles-groups-and-communities'),
                                'parent_item' => esc_html__('Parent Tag', 'profilegrid-user-profiles-groups-and-communities'),
                                'parent_item_colon' => esc_html__('Parent Tag:', 'profilegrid-user-profiles-groups-and-communities'),
                                'not_found' => esc_html__('No Tags found', 'profilegrid-user-profiles-groups-and-communities'),
                            ]

                    ]);
                    register_taxonomy_for_object_type( 'blog_tag', 'profilegrid_blogs' );
            }  
	}

	public function pm_load_pg_blogs() {
            $pm_sanitizer = new PM_sanitizer;
            
             if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
            $post = $pm_sanitizer->sanitize($_POST);
		$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pmhtmlcreator->pm_get_user_blog_posts($post['uid'],$post['page'] );
		die;
	}

	public function pm_get_rid_by_uname() {
            $pm_sanitizer = new PM_sanitizer;
            
             if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
            $post = $pm_sanitizer->sanitize($_POST);
		 $current_user = wp_get_current_user();
		$user          = get_user_by( 'login', $post['uname'] );
		if ( $user ) {
			if ( get_user_meta( $user->ID, 'rm_user_status', true ) == 0 ) :
				if ( $current_user->ID != $user->ID ) {
					echo esc_html( $user->ID );
				}
				endif;
		}
		die;
	}
	public function pm_show_friends_tab( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		if ( $dbhandler->get_global_option_value( 'pm_friends_panel', '0' ) ) {
			echo '<li class="pm-profile-tab pg-friend-tab pm-pad10"><a class="pm-dbfl" href="#pg-friends">' . esc_html__( 'Friends', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></li>';
		}
	}





	private function pm_validate_ajax_nonce_or_403( $context_action ) {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_nonce',
					'message' => esc_html__( 'Security check failed.', 'profilegrid-user-profiles-groups-and-communities' ),
					'action'  => sanitize_key( (string) $context_action ),
				),
				403
			);
		}
	}

	public function pm_fetch_my_friends() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_fetch_my_friends' );
		 $pmrequests       = new PM_request();
		$dbhandler         = new PM_DBhandler();
			$pmfriends     = new PM_Friends_Functions();
			$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$uid               = filter_input( INPUT_POST, 'uid', FILTER_VALIDATE_INT );
		$path              = plugin_dir_url( __FILE__ );
			$pm_f_search   = filter_input( INPUT_POST, 'pm_f_search' );
			$view          = filter_input( INPUT_POST, 'pm_friend_view' );
			$limit         = 20; // number of rows in page

		$pagenum = filter_input( INPUT_POST, 'pagenum' );

		if ( $pagenum ) {
					$pmhtmlcreator->pm_get_my_friends_html( $uid, $pagenum, $pm_f_search, $limit, $view );
		}
		die;
	}

	public function pm_fetch_friend_list_counter() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_fetch_friend_list_counter' );
		$pmfriends = new PM_Friends_Functions();
		$uid       = filter_input( INPUT_POST, 'uid', FILTER_VALIDATE_INT );
		$view      = filter_input( INPUT_POST, 'pm_friend_view' );
		switch ( $view ) {
			case 1:
				echo wp_kses_post( $pmfriends->pm_count_my_friends( $uid ) );
				break;
			case 2:
				echo wp_kses_post( $pmfriends->pm_count_my_friend_requests( $uid ) );
				break;
			case 3:
				echo wp_kses_post( $pmfriends->pm_count_my_friend_requests( $uid, 1 ) );
				break;
		}
		die;
	}

	public function pm_fetch_my_suggestion() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmrequests    = new PM_request();
		$dbhandler     = new PM_DBhandler();
			$pmfriends = new PM_Friends_Functions();
		$identifier    = 'FRIENDS';
		$uid           = filter_input( INPUT_POST, 'uid', FILTER_VALIDATE_INT );
		$path          = plugin_dir_url( __FILE__ );
		$pagenum       = filter_input( INPUT_POST, 'pagenum' );
		$suggestions   = $pmfriends->profile_magic_friends_suggestion( $uid );
		if ( $pagenum ) {
			$pm_u_search      = filter_input( INPUT_POST, 'pm_u_search' );
			$limit            = 10; // number of rows in page
			$pagenum          = isset( $pagenum ) ? absint( $pagenum ) : 1;
			$offset           = ( $pagenum - 1 ) * $limit;
			$meta_query_array = $pmrequests->pm_get_user_meta_query( is_array( filter_input_array( INPUT_GET, FILTER_UNSAFE_RAW ) ) ? map_deep( wp_unslash( filter_input_array( INPUT_GET, FILTER_UNSAFE_RAW ) ), 'sanitize_text_field' ) : array() );
			$date_query       = $pmrequests->pm_get_user_date_query( is_array( filter_input_array( INPUT_GET, FILTER_UNSAFE_RAW ) ) ? map_deep( wp_unslash( filter_input_array( INPUT_GET, FILTER_UNSAFE_RAW ) ), 'sanitize_text_field' ) : array() );
			$suggestions      = $pmfriends->profile_magic_friends_suggestion( $uid );

			$users = $dbhandler->pm_get_all_users( $pm_u_search, $meta_query_array, '', $offset, $limit, 'ASC', 'include', array(), $date_query, $suggestions );

			$pmfriends->profile_magic_friends_result_html( $users, $uid );
		}
		die;
	}

	public function pm_add_friend_request() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_add_friend_request' );
		$pmrequests           = new PM_request();
		$dbhandler            = new PM_DBhandler();
			$pmnotification   = new Profile_Magic_Notification();
		$identifier           = 'FRIENDS';
		$user1                = filter_input( INPUT_POST, 'user1' );
		$user2                = filter_input( INPUT_POST, 'user2' );
		$u1                   = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2                   = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
                $current_user         = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
		$data                 = array();
		$data['user1']        = $u1;
		$data['user2']        = $u2;
		$date                 = gmdate( 'Y-m-d h:i:s' );
		$data['created_date'] = $date;
		$data['action_date']  = $date;
		$data['status']       = 1;
		$id                   = $dbhandler->insert_row( $identifier, $data );
			$pmnotification->pm_friend_request_notification( $u2, $u1 );

			$send_email = $dbhandler->get_global_option_value( 'pm_sending_email_on_friend_request', 0 );
		if ( $send_email == 1 ) {
			$pmemails = new PM_Emails();
			$pmemails->pm_send_email( $u1, $u2 );
		}

		?>
		<span><?php esc_html_e( 'Request Sent', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
		
		<?php
		die;
	}

	public function pm_remove_friend_suggestion() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_remove_friend_suggestion' );
		 $pmrequests          = new PM_request();
		$dbhandler            = new PM_DBhandler();
		$identifier           = 'FRIENDS';
		$user1                = filter_input( INPUT_POST, 'user1' );
		$user2                = filter_input( INPUT_POST, 'user2' );
		$u1                   = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2                   = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
		$current_user       = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
                $data                 = array();
		$data['user1']        = $u1;
		$data['user2']        = $u2;
		$date                 = gmdate( 'Y-m-d h:i:s' );
		$data['created_date'] = $date;
		$data['action_date']  = $date;
		$data['status']       = 5;
		$id                   = $dbhandler->insert_row( $identifier, $data );
		echo esc_html( $id );
		die;
	}

	public function pm_confirm_friend_request() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_confirm_friend_request' );
		$pmrequests         = new PM_request();
		$dbhandler          = new PM_DBhandler();
			$pmfriends      = new PM_Friends_Functions();
			$pmnotification = new Profile_Magic_Notification();
		$identifier         = 'FRIENDS';
		$user1              = filter_input( INPUT_POST, 'user1' );
		$user2              = filter_input( INPUT_POST, 'user2' );
		$u1                 = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2                 = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
                $current_user       = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
		$data               = array();
		// $data['user1'] = $u1;
		// $data['user2'] = $u2;
		$date = gmdate( 'Y-m-d h:i:s' );
		// $data['created_date'] = $date;
		$data['action_date'] = $date;
		$data['status']      = 2;
		$requests            = $pmfriends->profile_magic_is_exist_in_table( $u1, $u2 );
			$pmnotification->pm_friend_added_notification( $u2, $u1 );
		$dbhandler->update_row( $identifier, 'id', $requests->id, $data, array( '%s', '%d' ), '%d' );
		do_action( 'pm_friend_request_accepted', $u2, $u1 );
			echo '<b>' . esc_html__( 'Request Accepted!', 'profilegrid-user-profiles-groups-and-communities' ) . '</b><br />' . esc_html__( 'You are now friends', 'profilegrid-user-profiles-groups-and-communities' );
		die;
	}

	public function pm_reject_friend_request() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_reject_friend_request' );
		$pmrequests    = new PM_request();
		$dbhandler     = new PM_DBhandler();
			$pmfriends = new PM_Friends_Functions();
		$identifier    = 'FRIENDS';
		$user1         = filter_input( INPUT_POST, 'user1' );
		$user2         = filter_input( INPUT_POST, 'user2' );
		$u1            = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2            = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
                $current_user       = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
		$data          = array();
		// $data['user1'] = $u1;
		// $data['user2'] = $u2;
		$date = gmdate( 'Y-m-d h:i:s' );
		// $data['created_date'] = $date;
		$data['action_date'] = $date;
		$data['status']      = 3;
		$requests            = $pmfriends->profile_magic_is_exist_in_table( $u1, $u2 );
		$dbhandler->update_row( $identifier, 'id', $requests->id, $data, array( '%s', '%d' ), '%d' );
			$username2 = $pmrequests->pm_get_display_name( $u2 );
			do_action( 'pm_friend_request_rejected', $u2, $u1 );
			/* translators: %s: Display name. */
			echo '<b>' . esc_html__( 'Request Rejected!', 'profilegrid-user-profiles-groups-and-communities' ) . '</b><br />' . sprintf( esc_html__( 'You cancelled friend request from %s.', 'profilegrid-user-profiles-groups-and-communities' ), esc_html($username2) );
		die;
	}

	public function pm_block_friend() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_block_friend' );
		 $pmrequests   = new PM_request();
		$dbhandler     = new PM_DBhandler();
			$pmfriends = new PM_Friends_Functions();
		$identifier    = 'FRIENDS';
		$user1         = filter_input( INPUT_POST, 'user1' );
		$user2         = filter_input( INPUT_POST, 'user2' );
		$u1            = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2            = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
                $current_user       = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
		$data          = array();
		// $data['user1'] = $u1;
		// $data['user2'] = $u2;
		$date = gmdate( 'Y-m-d h:i:s' );
		// $data['created_date'] = $date;
		$data['action_date'] = $date;
		$data['status']      = 4;
		$requests            = $pmfriends->profile_magic_is_exist_in_table( $u1, $u2 );
		$dbhandler->update_row( $identifier, 'id', $requests->id, $data, array( '%s', '%d' ), '%d' );
		echo '<b>' . esc_html__( 'Friend Blocked!', 'profilegrid-user-profiles-groups-and-communities' ) . '</b><br />' . esc_html__( 'You have blocked this user', 'profilegrid-user-profiles-groups-and-communities' );
		die;
	}

	public function pm_unfriend_friend() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_unfriend_friend' );
		$pmrequests         = new PM_request();
		$dbhandler          = new PM_DBhandler();
			$pmfriends      = new PM_Friends_Functions();
		$identifier         = 'FRIENDS';
		$user1              = filter_input( INPUT_POST, 'user1' );
		$user2              = filter_input( INPUT_POST, 'user2' );
			$cancel_request = filter_input( INPUT_POST, 'cancel_request' );
		$u1                 = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user1 );
		$u2                 = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user2 );
                $current_user       = get_current_user_id();
                if($u1 != $current_user || $u2 == $current_user ){
                    die;
                }
		$data               = array();
		// $data['user1'] = $u1;
		// $data['user2'] = $u2;
		$date = gmdate( 'Y-m-d h:i:s' );
		// $data['created_date'] = $date;
		$data['action_date'] = $date;
		$data['status']      = 6;
		$requests            = $pmfriends->profile_magic_is_exist_in_table( $u1, $u2 );
			$dbhandler->update_row( $identifier, 'id', $requests->id, $data, array( '%s', '%d' ), '%d' );
		if ( $cancel_request == 1 ) :
				$dbhandler->remove_row( $identifier, 'id', $requests->id, '%d' );
				echo '<b>' . esc_html__( 'Request Removed!', 'profilegrid-user-profiles-groups-and-communities' ) . '</b>';
			else :
					 $username2 = $pmrequests->pm_get_display_name( $u2 );
				/* translators: %s: Display name. */
				echo '<b>' . esc_html__( 'Friend Removed!', 'profilegrid-user-profiles-groups-and-communities' ) . '</b><br />' . sprintf( esc_html__( 'You have removed %s from your friend list.', 'profilegrid-user-profiles-groups-and-communities' ), wp_kses_post($username2) );
				endif;

			die;
	}

	public function pm_get_friends_notification() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_get_friends_notification' );
		$dbhandler   = new PM_DBhandler();
		$identifier  = 'FRIENDS';
		$timestamp   = filter_input( INPUT_GET, 'timestamp', FILTER_VALIDATE_INT );
		$current_user = wp_get_current_user();
		$uid          = $current_user->ID;
		$last_ajax_call = ! empty( $timestamp ) ? (int) $timestamp : 0;
		$where          = array(
			'user2'  => $uid,
			'status' => 1,
		);
		$requests       = $dbhandler->get_all_result( $identifier, '*', $where );
		$last_change_ts = 0;
		if ( ! empty( $requests ) ) {
			$last_row       = end( $requests );
			$last_change_ts = ! empty( $last_row->action_date ) ? (int) strtotime( $last_row->action_date ) : 0;
		}
		$data_count = count( $requests );
		if ( $last_ajax_call > 0 && $last_change_ts <= $last_ajax_call ) {
			$data_count = 0;
		}
		echo wp_kses_post(
			wp_json_encode(
				array(
					'data_from_file' => (string) $data_count,
					'timestamp'      => $last_change_ts,
				)
			)
		);
		die;
	}


	public function pm_right_side_options( $uid, $gid ) {
		$pmrequests   = new PM_request();
		$dbhandler    = new PM_DBhandler();
		$pmfriends    = new PM_Friends_Functions();
		$PM_Messanger = new PM_Messenger();
		$current_user = wp_get_current_user();
        // Allow developers to override this behavior
        $pm_override_right_side_options = false;
        $pm_override_right_side_options = apply_filters( 'pm_override_right_side_options', false, $uid, $gid );
        if ( $pm_override_right_side_options ) {
            return;
        }
		if ( $uid != $current_user->ID && $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' ) == 1 ) :
			$messenger_url = $PM_Messanger->pm_get_message_url( $uid );
			?>
			  <div class="pm-difr pm-pad20">
				  <a id="message_user" href="<?php echo esc_url( $messenger_url ); ?>" ><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
			</div>
			<?php
			endif;

		if ( $uid != $current_user->ID && $dbhandler->get_global_option_value( 'pm_friends_panel', '0' ) == 1 ) :
			echo '<div class="pm-difr pm-pad20">';
			$pmfriends->profile_magic_friend_list_button( $current_user->ID, $uid );
			echo '</div>';
		 endif;

	}

	public function pm_delete_notification() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_delete_notification' );
		$notif_id        = filter_input( INPUT_POST, 'id' );
		$pm_notification = new Profile_Magic_Notification();
		$return          = $pm_notification->pm_delete_notification( $notif_id );
		echo wp_kses_post( $return );
		die;
	}

	public function pm_load_more_notification() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_load_more_notification' );
		$loadnum         = filter_input( INPUT_POST, 'loadnum' );
		$pm_notification = new Profile_Magic_Notification();
		$pm_notification->pm_generate_notification_without_heartbeat( $loadnum );
		die;

	}

	public function pm_read_all_notification() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_read_all_notification' );
		$uid             = get_current_user_id();
		$pm_notification = new Profile_Magic_Notification();
		$pm_notification->pm_mark_all_notification_as_read( $uid );
		die;

	}

	public function pm_refresh_notification() {
		$this->pm_validate_ajax_nonce_or_403( 'pm_refresh_notification' );
		$pm_notification = new Profile_Magic_Notification();
		$pm_notification->pm_generate_notification_without_heartbeat();
		die;
	}

	public function profile_magic_custom_payment_fields( $gid ) {
		$pmrequests    = new PM_request();
		$dbhandler     = new PM_DBhandler();
		$paypal_enable = $dbhandler->get_global_option_value( 'pm_enable_paypal', '0' );

		if ( $pmrequests->profile_magic_check_paid_group( $gid ) > 0 ) :
			?>
					 
		
		<div class="pmrow">
	
				<div class="pm-col">
					<div class="pm-form-field-icon"></div>
					<div class="pm-field-lable">
						<label><?php esc_html_e( 'Price', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
					</div>
					<div class="pm-field-input">
						<div class="pm_group_price">
			  <?php
				if ( $dbhandler->get_global_option_value( 'pm_currency_position', 'before' ) == 'before' ) :
					echo wp_kses_post( $pmrequests->pm_get_currency_symbol() . ' ' . $pmrequests->profile_magic_check_paid_group( $gid ) );
				else :
					echo wp_kses_post( $pmrequests->profile_magic_check_paid_group( $gid ) . ' ' . $pmrequests->pm_get_currency_symbol() );
				endif;
				?>
			</div>
						<div class="errortext" style="display:none;"></div>
						
					</div>
				</div>
				
			</div>
		<div class="pmrow">
				<div class="pm-col">
					<div class="pm-form-field-icon"></div>
					<div class="pm-field-lable">
						<label for="pm_payment_method"><?php esc_html_e( 'Payment Method', 'profilegrid-user-profiles-groups-and-communities' ); ?><sup>*</sup></label>
					</div>
					<div class="pm-field-input pm_radiorequired">
						<div class="pmradio">
						<?php if ( $paypal_enable == 1 ) : ?>
							<div class="pm-radio-option"><input title="<?php esc_attr_e( 'PayPal', 'profilegrid-user-profiles-groups-and-communities' ); ?>" type="radio"  id="pm_payment_method" name="pm_payment_method" value="paypal" checked><?php esc_html_e( 'PayPal', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
							<?php endif; ?>
						 <?php do_action( 'profile_magic_additional_payment_options', $gid ); ?>
						</div>
						<div class="errortext" style="display:none;"></div>
					</div>
				</div>
		</div>
			 <?php
			endif;
	}

	public function profile_magic_check_paypal_config( $msg ) {
		 $dbhandler    = new PM_DBhandler();
		$paypal_enable = $dbhandler->get_global_option_value( 'pm_enable_paypal', '0' );
		if ( $paypal_enable == 1 ) {
			$paypal_email = trim( $dbhandler->get_global_option_value( 'pm_paypal_email' ) );
			if ( $paypal_email == '' ) {
				$msg = esc_html__( 'Oops! It looks like the PayPal payment system is not configured properly. Please check its settings.', 'profilegrid-user-profiles-groups-and-communities' );
			} else {
				$msg = '';
			}
		} else {
				$msg = 'disabled';
		}
		return $msg;
	}

	public function profile_magic_author_link( $link, $author_id ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		if ( $dbhandler->get_global_option_value( 'pm_auto_redirect_author_to_profile', '0' ) == 1 ) {
			$link = $pmrequests->pm_get_user_profile_url( $author_id );
		}
		return $link;
	}

	public function profile_magic_allow_backend_screen_for_guest() {
		global $pagenow;
		// For Login screen
                $pm_sanitizer = new PM_sanitizer;
                $request = $pm_sanitizer->sanitize($_REQUEST);
		if ( isset( $pagenow ) && $pagenow == 'wp-login.php' && ! is_user_logged_in() && ! isset( $request['action'] ) && ! isset( $request['pgr'] ) ) {
			$dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
			$allowed    = $dbhandler->get_global_option_value( 'pm_guest_allow_backend_login_screen', '1' );
			$allowed    = apply_filters( 'pg_whitelisted_wpadmin_access', $allowed );

			if ( $allowed == 0 ) {
				$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php?pgr=1' ) );
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				exit;
			}
		}

		if ( isset( $pagenow ) && $pagenow == 'wp-login.php' && ! is_user_logged_in() && isset( $request['action'] ) && $request['action'] == 'register' && ! isset( $request['pgr'] ) ) {
			$dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
			$allowed    = $dbhandler->get_global_option_value( 'pm_guest_allow_backend_register_screen', '1' );
			$allowed    = apply_filters( 'pg_whitelisted_wpadmin_access', $allowed );
			if ( $allowed == 0 ) {
				$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_registration_page', site_url( '/wp-login.php?action=register&pgr=1' ) );
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				exit;
			}
		}

	}

	public function pm_auto_logout_user() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		 $dbhandler   = new PM_DBhandler();
		$pmrequests   = new PM_request();
		$redirect_url = '';
		$show_prompt  = $dbhandler->get_global_option_value( 'pm_show_logout_prompt', '0' );
		if ( $dbhandler->get_global_option_value( 'pm_enable_auto_logout_user', '0' ) == '1' ) :
			if ( is_user_logged_in() ) {
				$is_admin = user_can( intval( get_current_user_id() ), 'manage_options' );
				if ( ! $is_admin ) {
					$this->profile_magic_set_logged_out_status( get_current_user_id() );
					update_user_meta( get_current_user_id(), 'pm_login_status', 0 );
					wp_clear_auth_cookie();
					$redirect = $dbhandler->get_global_option_value( 'pm_redirect_after_logout', '0' );
					if ( $redirect != '0' ) {
						$redirect_url = get_permalink( $redirect );
						if ( $show_prompt == '0' ) {
							$redirect_url = add_query_arg( 'errors', 'inactivity', $redirect_url );
						}
					} else {
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						if ( $show_prompt == '0' ) {
							$redirect_url = add_query_arg( 'errors', 'inactivity', $redirect_url );
						}
					}

					$redirect_url = esc_url_raw( $redirect_url );

				}
			}
			endif;

		echo wp_kses_post( $redirect_url );
		die;

	}

	public function profile_magic_auto_logout_prompt_html() {
		$is_admin = user_can( intval( get_current_user_id() ), 'manage_options' );
		if ( is_user_logged_in() && ! $is_admin ) {
			require 'partials/pm-autologout-prompt.php';
		}
	}

	public function pg_whitelisted_wpadmin_access( $allowed ) {
			 $dbhandler  = new PM_DBhandler();
			$pmrequests  = new PM_request();
			$allowed_ips = $dbhandler->get_global_option_value( 'pm_wpadmin_allow_ips', '' );
		if ( $allowed_ips == '' ) {
			return $allowed;
		}

		$ips     = array_map( 'rtrim', explode( ',', $allowed_ips ) );
		$user_ip = $pmrequests->pm_user_ip();

		if ( in_array( $user_ip, $ips ) ) {
			$allowed = 1;
		}
		return $allowed;

	}

	public function pm_blocked_ips( $args ) {
		$dbhandler   = new PM_DBhandler();
		$pmrequests  = new PM_request();
		$blocked_ips = $dbhandler->get_global_option_value( 'pm_blocked_ips', '' );
		// return $blocked_ips;
		if ( $blocked_ips == '' ) {
			return;
		}

		$ips     = array_map( 'rtrim', explode( ',', $blocked_ips ) );
		$user_ip = $pmrequests->pm_user_ip();
		// return $user_ip;
		foreach ( $ips as $ip ) {
			$ip = str_replace( '*', '', $ip );
			if ( ! empty( $ip ) && strpos( $user_ip, $ip ) === 0 ) {
				if ( isset( $args['form_type'] ) && $args['form_type'] == 'register' ) {
					return $pmrequests->profile_magic_get_error_message( 'blocked_ip', 'profilegrid-user-profiles-groups-and-communities' );
				} else {
					$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
					$redirect_url = add_query_arg( 'errors', 'blocked_ip', $redirect_url );
					wp_safe_redirect( esc_url_raw( $redirect_url ) );
					exit();
				}
			}
		}
	}

	public function pm_check_ip_during_login( $user, $username, $password ) {

		if ( ! empty( $username ) ) {

			do_action( 'pg_blocked_user_ip', $args = array() );
			do_action( 'pg_blocked_user_email', $args = array( 'username' => $username ) );

		}

		return $user;
	}

	public function pg_blocked_emails( $args ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		if ( is_email( $args['username'] ) ) {
			$useremail = $args['username'];
		} else {
			$user = get_user_by( 'login', $args['username'] );
			if ( isset( $user ) && isset( $user->user_email ) ) {
				$useremail = $user->user_email;
			}
		}

		$blocked_emails = $dbhandler->get_global_option_value( 'pm_blocked_emails', '' );
		if ( $blocked_emails == '' ) {
			return;
		}
		$emails = array_map( 'rtrim', explode( ',', $blocked_emails ) );

		if ( isset( $useremail ) && is_email( $useremail ) ) {
			$domain       = explode( '@', $useremail );
			$check_domain = str_replace( $domain[0], '*', $useremail );

			if ( in_array( $useremail, $emails ) ) {
				if ( isset( $args['form_type'] ) && $args['form_type'] == 'register' ) {
					return $pmrequests->profile_magic_get_error_message( 'blocked_email', 'profilegrid-user-profiles-groups-and-communities' );
				} else {
					$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
					$redirect_url = add_query_arg( 'errors', 'blocked_email_on_login', $redirect_url );
					wp_safe_redirect( esc_url_raw( $redirect_url ) );
					exit();
				}
			}

			if ( in_array( $check_domain, $emails ) ) {
				if ( isset( $args['form_type'] ) && $args['form_type'] == 'register' ) {
					return $pmrequests->profile_magic_get_error_message( 'blocked_domain', 'profilegrid-user-profiles-groups-and-communities' );
				} else {
					$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
					$redirect_url = add_query_arg( 'errors', 'blocked_domain', $redirect_url );
					wp_safe_redirect( esc_url_raw( $redirect_url ) );
					exit();
				}
			}
		}

	}

	public function pg_blocked_emails_wp_registration( $errors, $sanitized_user_login, $user_email ) {
		$args               = array();
		$args['username']   = $user_email;
		$args['form_type']  = 'register';
		$is_blocked         = $this->pg_blocked_emails( $args );
		$is_blocked_ip      = $this->pm_blocked_ips( $args );
		$post               = array();
		$error              = array();
		$post['user_login'] = $sanitized_user_login;
		$post['user_email'] = $user_email;
		$is_blocked_word    = $this->pm_check_blocked_word_during_registration( $error, $post );
		if ( ! empty( $is_blocked ) ) {
				$errors->add( 'blocked_email', $is_blocked );
		}

		if ( ! empty( $is_blocked_ip ) ) {
			$errors->add( 'blocked_ip', $is_blocked_ip );
		}

		if ( ! empty( $is_blocked_word ) ) {
			$errors->add( 'blocked_words', $is_blocked_word[0] );
		}
			return $errors;
	}

	public function pm_check_blocked_email_during_registration( $error, $post ) {
            $pm_sanitizer = new PM_sanitizer;
		if ( ! isset( $post['user_email']) ) {
			return $error;}
		$useremail         = sanitize_email($post['user_email']);
		$args              = array();
		$args['username']  = sanitize_email($post['user_email']);
		$args['form_type'] = 'register';
		$is_blocked        = $this->pg_blocked_emails( $args );
		if ( ! empty( $is_blocked ) ) {
			   $error[] = $is_blocked;
		}
			return $error;
	}


	public function pm_check_blocked_word_during_registration( $error, $post ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		if ( ! isset( $post['user_login'] ) || ! isset( $post['user_email'] ) ) {
			return $error;
		}

		if ( isset( $post['user_login'] ) ) {
			$useremail = strtolower( $post['user_login'] );
		} else {
			$useremail = strtolower( substr( $post['user_email'], 0, strrpos( $post['user_email'], '@' ) ) );

		}

		$words = strtolower( $dbhandler->get_global_option_value( 'pm_blacklist_word', '' ) );
		if ( $words != '' ) {
			$words = array_map( 'rtrim', explode( ',', $words ) );
			if ( in_array( $useremail, $words ) ) {
				$error[] = $pmrequests->profile_magic_get_error_message( 'blocked_words', 'profilegrid-user-profiles-groups-and-communities' );
			}
		}
		return $error;
	}

	public function pm_account_deletion_notification( $user_id ) {
		$dbhandler  = new PM_DBhandler();
		$pmemails   = new PM_Emails();
		$pmrequests = new PM_request();

		$dbhandler->remove_row( 'FRIENDS', 'user1', $user_id, '%d' );
		$dbhandler->remove_row( 'FRIENDS', 'user2', $user_id, '%d' );

		$gids = $pmrequests->profile_magic_get_user_field_value( $user_id, 'pm_group' );
		$ugid = $pmrequests->pg_filter_users_group_ids( $gids );
		$gid  = $pmrequests->pg_get_primary_group_id( $ugid );
		if ( isset( $gid ) ) {
			$pmemails->pm_send_group_based_notification( $gid, $user_id, 'on_account_deleted' );
		}

		$pm_admin_notification                  = $dbhandler->get_global_option_value( 'pm_admin_notification', 0 );
		$pm_admin_account_deletion_notification = $dbhandler->get_global_option_value( 'pm_admin_account_deletion_notification', 0 );
		if ( $pm_admin_notification == 1 && $pm_admin_account_deletion_notification == 1 ) {
			$subject = $dbhandler->get_global_option_value( 'pm_account_delete_email_subject', esc_html__( 'Account deleted', 'profilegrid-user-profiles-groups-and-communities' ) );
			$body    = $dbhandler->get_global_option_value( 'pm_account_delete_email_body', esc_html__( '{{display_name}} has just deleted their account.', 'profilegrid-user-profiles-groups-and-communities' ) );
			$message = $pmemails->pm_filter_email_content( $body, $user_id );
			$pmemails->pm_send_admin_notification( $subject, $message );
		}
	}

	public function pg_user_profile_pagetitle( $title, $sep = '' ) {
		$dbhandler     = new PM_DBhandler();
		$pmemails      = new PM_Emails();
		$pmrequests    = new PM_request();
                $pm_sanitizer = new PM_sanitizer;
                $request = $pm_sanitizer->sanitize($_REQUEST);
		$profile_title = $dbhandler->get_global_option_value( 'pg_user_profile_seo_title', '{{display_name}} | ' . get_bloginfo( 'name' ) );
		if ( get_the_ID() == $dbhandler->get_global_option_value( 'pm_user_profile_page', 0 ) ) {
			if ( isset( $request['uid'] ) ) {
				$uid = $pmrequests->pm_get_uid_from_profile_slug($request['uid']);
			} else {
				$current_user = wp_get_current_user();
				$uid          = $current_user->ID;
			}

			$title = $pmemails->pm_filter_email_content( $profile_title, $uid );

		}

		return $title;

	}

	public function pg_user_profile_metadesc() {
		$dbhandler    = new PM_DBhandler();
		$pmemails     = new PM_Emails();
		$pmrequests   = new PM_request();
                 $pm_sanitizer = new PM_sanitizer;
                $request = $pm_sanitizer->sanitize($_REQUEST);
		$meta_content = $dbhandler->get_global_option_value( 'pg_user_profile_seo_desc' );

		if ( get_the_ID() == $dbhandler->get_global_option_value( 'pm_user_profile_page', 0 ) ) {
			if ( isset( $request['uid'] ) ) {
				$uid = $pmrequests->pm_get_uid_from_profile_slug( $request['uid'] );
			} else {
				$current_user = wp_get_current_user();
				$uid          = $current_user->ID;
			}
			$user_info = get_user_by( 'ID', $uid );
			if ( $user_info ) :
				$content = $pmemails->pm_filter_email_content( $meta_content, $uid );
				$avatar  = get_avatar(
					$user_info->user_email,
					150,
					'',
					false,
					array(
						'class'         => 'pm-user',
						'force_display' => true,
					)
				);
				$string  = $pmrequests->pm_get_display_name( $uid );
				$title   = $pmrequests->pg_get_strings_between_tags( $string, 'span' );
				if ( $title == '' ) {
					$title = $pmrequests->pm_get_display_name( $uid );
				}
				?>
				<meta name="description" content="<?php echo esc_attr(str_replace( '\\', '', $content )); ?>">
				<meta property="og:title" content="<?php echo esc_attr( $title ); ?>" />
				<meta property="og:type" content="article" />
				
				<meta property="og:url" content="<?php echo esc_url( $pmrequests->pm_get_user_profile_url( $uid ) ); ?>" />
				<meta property="og:description" content="<?php  echo esc_attr( str_replace( '\\', '', $content )); ?>" />
				<?php
				endif;
		}
	}


        public function pm_get_comment_author( $author, $comment_ID ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();

		if ( $dbhandler->get_global_option_value( 'pm_auto_redirect_author_to_profile', '0' ) == 1 ) {
			global $comment;
			$comment = get_comment( $comment_ID );
			if ( isset( $comment->user_id ) && ! empty( $comment->user_id ) ) {
				$displayname = $pmrequests->pm_get_display_name( $comment->user_id );
				$author =  wp_strip_all_tags($displayname);
			}
		}
		return $author;

	}


	public function pm_comment_author( $author, $comment_ID ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();

		if ( $dbhandler->get_global_option_value( 'pm_auto_redirect_author_to_profile', '0' ) == 1 ) {
			global $comment;
			$comment = get_comment( $comment_ID );
			if ( isset( $comment->user_id ) && ! empty( $comment->user_id ) ) {
				$link        = $pmrequests->pm_get_user_profile_url( $comment->user_id );
				$displayname = $pmrequests->pm_get_display_name( $comment->user_id );
				$author      = "<a href='" . $link . "'>" . $displayname . '</a>';
			}
		}
		return $author;

	}

	public function pg_post_published_notification( $ID, $post ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$pmemail    = new PM_Emails();
		$userid     = $post->post_author; /* Post author ID. */

		$pm_blog_notification_user = $dbhandler->get_global_option_value( 'pm_blog_notification_user' );
		$gids                      = $pmrequests->profile_magic_get_user_field_value( $userid, 'pm_group' );
		$gid                       = $pmrequests->pg_filter_users_group_ids( $gids );

		if ( isset( $gid ) && ! empty( $gid ) && $pm_blog_notification_user == '1' ) {
			if ( isset( $gid[0] ) ) {
				$groupid = $gid[0];
				$pmemail->pm_send_group_based_notification( $groupid, $userid, 'on_published_new_post', $ID );
			}
		}
	}

	public function pg_set_toolbar() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$hide_tb           = get_option( 'pm_hide_wp_toolbar', $default = 'no' );
		$visible_for_admin = get_option( 'pm_hide_admin_toolbar', $default = 'no' );
		if ( $hide_tb === 'yes' ) {
			if ( $visible_for_admin == 'yes' ) {
				if ( current_user_can( 'manage_options' ) ) {
					show_admin_bar( true );
				} else {
					show_admin_bar( false );
				}
			} else {
				show_admin_bar( false );
			}
		} else {
			show_admin_bar( true );
		}
	}

	public function pg_comment_link_to_profile( $return,$author = '', $comment_ID ='' ) {
		   $dbhandler   = new PM_DBhandler();
			$pmrequests = new PM_request();
			$comment    = get_comment( $comment_ID );
		if ( $dbhandler->get_global_option_value( 'pm_auto_redirect_author_to_profile', '0' ) == 1 && isset( $comment->user_id ) && ! empty( $comment->user_id ) ) {
			$user = get_userdata( $comment->user_id );
                        if($user!==false)
                        {
                            $link        = $pmrequests->pm_get_user_profile_url( $comment->user_id );
                            $displayname = $pmrequests->pm_get_display_name( $comment->user_id );
                            $return      = "<a href='" . $link . "'>" . $displayname . '</a>';
                        }

		}
			return $return;
	}

	public function pm_remove_file_attachment() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$key = filter_input( INPUT_POST, 'key' );
		$value = filter_input( INPUT_POST, 'value' );
		$pmrequests = new PM_request();
		$result = $pmrequests->pg_remove_profile_attachment( get_current_user_id(), $key, $value );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message(), 403 );
		}
		echo esc_html( $result );
		die;
	}

	public function pm_edit_group_popup_html() {
             $pm_sanitizer = new PM_sanitizer;
            if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
                $post = $pm_sanitizer->sanitize($_POST);
		$html_generator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$tab            = $post['tab'];
		$type           = $post['type'];

		if ( is_array( $post['id'] ) ) {
			$id = $post['id'];
		} else {
			$id = filter_input( INPUT_POST, 'id' );
		}

		$gid = filter_input( INPUT_POST, 'gid' );
		if ( $tab == 'blog' && in_array( $type, array( 'add_admin_note', 'edit_admin_note', 'delete_admin_note', 'add_admin_note_bulk' ), true ) ) {
			if ( ! is_user_logged_in() ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}

			$current_user_id = get_current_user_id();
			$pmrequests      = new PM_request();
			$gid             = is_scalar( $gid ) ? trim( (string) $gid ) : '';
			$is_group_leader = ( '' !== $gid ) ? $pmrequests->pg_check_in_single_group_is_user_group_leader( $current_user_id, $gid ) : false;

			$ids_to_check = is_array( $id ) ? $id : array( $id );

			if ( empty( $ids_to_check ) ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}

			foreach ( $ids_to_check as $post_id ) {
				$post_author_id   = (int) get_post_field( 'post_author', $post_id );
				$post_belongs_gid = ( $post_author_id > 0 && '' !== $gid ) ? $pmrequests->profile_magic_check_is_group_member( $gid, $post_author_id ) : false;
				$allowed          = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
					|| ( $is_group_leader && $post_belongs_gid );
				if ( ! $allowed ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
			}
		}
		if ( $tab == 'blog' ) {
			$html_generator->pg_blog_popup_html_generator( $type, $id, $gid );
		}
		if ( $tab == 'member' ) {
			$html_generator->pg_member_popup_html_generator( $type, $id, $gid );
		}
		if ( $tab == 'group' ) {
			$html_generator->pg_group_popup_html_generator( $type, $id, $gid );
		}
		if ( $tab == 'admins' ) {
			$html_generator->pg_admin_popup_html_generator( $type, $id, $gid );
		}

		die;
	}

	public function pm_save_post_status() {
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_request      = new PM_request();
		$postid          = filter_input( INPUT_POST, 'post_id' );
		$blog_status     = filter_input( INPUT_POST, 'pm_change_blog_status' );
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'save_pm_post_status' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$current_user_id = get_current_user_id();
		if ( is_numeric( $postid ) ) {
			$post_author_id  = (int) get_post_field( 'post_author', $postid );
			$post_groups     = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
			$post_groups     = $pm_request->pg_filter_users_group_ids( $post_groups );
			$post_groups     = is_array( $post_groups ) ? $post_groups : array( $post_groups );
			$is_group_leader = false;
			foreach ( $post_groups as $post_group_id ) {
				if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
					$is_group_leader = true;
					break;
				}
			}
			$allowed = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
				|| $current_user_id === $post_author_id || $is_group_leader;
			if ( ! $allowed ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}
			$change_status = wp_update_post(
				array(
					'ID'          => $postid,
					'post_status' => $blog_status,
				)
			);
			update_post_meta( $postid, 'pm_enable_custom_access', '1' );
			$html_generator->change_blog_status_success_popup( $blog_status );
		} else {
			global $wpdb;
			$ids = PM_sanitizer::safe_unserialize_array( $pm_request->pm_encrypt_decrypt_pass( 'decrypt', $postid ) );
			$ids = is_array( $ids ) ? $ids : array();
			foreach ( $ids as $id ) {
				$post_author_id  = (int) get_post_field( 'post_author', $id );
				$post_groups     = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
				$post_groups     = $pm_request->pg_filter_users_group_ids( $post_groups );
				$post_groups     = is_array( $post_groups ) ? $post_groups : array( $post_groups );
				$is_group_leader = false;
				foreach ( $post_groups as $post_group_id ) {
					if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
						$is_group_leader = true;
						break;
					}
				}
				$allowed = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
					|| $current_user_id === $post_author_id || $is_group_leader;
				if ( ! $allowed ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
			}
			$i   = 0;
			foreach ( $ids as $id ) {
				 $is_update = $wpdb->update( $wpdb->posts, array( 'post_status' => $blog_status ), array( 'ID' => $id ) );
				 update_post_meta( $id, 'pm_enable_custom_access', '1' );
				 clean_post_cache( $id );
				if ( $is_update ) {
					$i++;
				}
			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = $i;
			$html_generator->change_blog_status_success_popup( $change_status );
		}

		die;
	}

	public function pm_save_post_content_access_level() {
		$html_generator    = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_request        = new PM_request();
		$postid            = filter_input( INPUT_POST, 'post_id' );
		$gid               = filter_input( INPUT_POST, 'gid' );
		$pm_content_access = filter_input( INPUT_POST, 'pm_content_access' );
		$retrieved_nonce   = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'save_pm_post_content_access_level' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$current_user_id = get_current_user_id();

		if ( is_numeric( $postid ) ) {
			$post_author_id  = (int) get_post_field( 'post_author', $postid );
			$post_groups     = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
			$post_groups     = $pm_request->pg_filter_users_group_ids( $post_groups );
			$post_groups     = is_array( $post_groups ) ? $post_groups : array( $post_groups );
			$is_group_leader = false;
			$gid_for_check   = is_scalar( $gid ) ? trim( (string) $gid ) : '';
			if ( '' !== $gid_for_check ) {
				$post_belongs_gid = $pm_request->profile_magic_check_is_group_member( $gid_for_check, $post_author_id );
				if ( $post_belongs_gid && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, $gid_for_check ) ) {
					$is_group_leader = true;
				}
			}
			if ( ! $is_group_leader ) {
				foreach ( $post_groups as $post_group_id ) {
					if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
						$is_group_leader = true;
						break;
					}
				}
			}
			$allowed = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
				|| $current_user_id === $post_author_id || $is_group_leader;
			if ( ! $allowed ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}
			if ( isset( $pm_content_access ) ) :
				if ( $pm_content_access == 5 ) {
					update_post_meta( $postid, 'pm_content_access', '2' );
					update_post_meta( $postid, 'pm_content_access_group', $gid );
					update_post_meta( $postid, 'pm_enable_custom_access', '1' );
				} else {
					if ( $pm_content_access == 2 ) {
						update_post_meta( $postid, 'pm_content_access_group', 'all' );
					}

					update_post_meta( $postid, 'pm_content_access', $pm_content_access );
					update_post_meta( $postid, 'pm_enable_custom_access', '1' );
				}
				$html_generator->change_blog_access_control_success_popup( $pm_content_access );
			else :
				$html_generator->change_blog_access_control_success_popup( 'failed' );
			endif;
		} else {
			$ids = PM_sanitizer::safe_unserialize_array( $pm_request->pm_encrypt_decrypt_pass( 'decrypt', $postid ) );
			$ids = is_array( $ids ) ? $ids : array();
			foreach ( $ids as $id ) {
				$post_author_id  = (int) get_post_field( 'post_author', $id );
				$post_groups     = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
				$post_groups     = $pm_request->pg_filter_users_group_ids( $post_groups );
				$post_groups     = is_array( $post_groups ) ? $post_groups : array( $post_groups );
				$is_group_leader = false;
				$gid_for_check   = is_scalar( $gid ) ? trim( (string) $gid ) : '';
				if ( '' !== $gid_for_check ) {
					$post_belongs_gid = $pm_request->profile_magic_check_is_group_member( $gid_for_check, $post_author_id );
					if ( $post_belongs_gid && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, $gid_for_check ) ) {
						$is_group_leader = true;
					}
				}
				if ( ! $is_group_leader ) {
					foreach ( $post_groups as $post_group_id ) {
						if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
							$is_group_leader = true;
							break;
						}
					}
				}
				$allowed = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
					|| $current_user_id === $post_author_id || $is_group_leader;
				if ( ! $allowed ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
			}
			$i   = 0;
			foreach ( $ids as $id ) {
				if ( $pm_content_access == 5 ) {
					$is_update = update_post_meta( $id, 'pm_content_access', '2' );
					update_post_meta( $id, 'pm_content_access_group', $gid );
					update_post_meta( $id, 'pm_enable_custom_access', '1' );
				} else {
					if ( $pm_content_access == 2 ) {
						update_post_meta( $id, 'pm_content_access_group', 'all' );
					}
					$is_update = update_post_meta( $id, 'pm_content_access', $pm_content_access );
					update_post_meta( $id, 'pm_enable_custom_access', '1' );
				}

				if ( $is_update ) {
					$i++;
				}
			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = $i;
			$html_generator->change_blog_access_control_success_popup( $change_status );
		}

		die;
	}

	public function pm_save_edit_blog_post() {
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_request      = new PM_request();
		$postid          = filter_input( INPUT_POST, 'post_id' );
		$post_title      = filter_input( INPUT_POST, 'blog_title' );
		$post_content    = filter_input( INPUT_POST, 'blog_description' );
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'save_pm_edit_blog_post' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$current_user_id = get_current_user_id();
		$post_author_id  = (int) get_post_field( 'post_author', $postid );
		$post_groups     = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
		$post_groups     = $pm_request->pg_filter_users_group_ids( $post_groups );
		$post_groups     = is_array( $post_groups ) ? $post_groups : array( $post_groups );
		$is_group_leader = false;
		foreach ( $post_groups as $post_group_id ) {
			if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
				$is_group_leader = true;
				break;
			}
		}
		$allowed = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
			|| $current_user_id === $post_author_id || $is_group_leader;
		if ( ! $allowed ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$change_status = wp_update_post(
			array(
				'ID'           => $postid,
				'post_title'   => $post_title,
				'post_content' => $post_content,
			)
		);
		if ( $change_status ) {
			$html_generator->sav_blog_post_success_popup( 'success' );
		} else {
			$html_generator->sav_blog_post_success_popup( 'failed' );
		}
		die;
	}

	public function pm_save_admin_note_content() {
		$html_generator      = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_request          = new PM_request();
		$postid              = filter_input( INPUT_POST, 'post_id' );
		$is_delete_request   = filter_input( INPUT_POST, 'delete_note' );
		$admin_note_content  = filter_input( INPUT_POST, 'pm_admin_note_content', FILTER_UNSAFE_RAW );
		$admin_note_position = filter_input( INPUT_POST, 'pm_admin_note_position' );
		$retrieved_nonce     = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'save_pm_admin_note_content' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}

		$admin_note_content  = wp_unslash( $admin_note_content );
		$admin_note_content  = substr( $admin_note_content, 0, 5000 );
		$admin_note_content  = wp_kses_post( $admin_note_content );
		$admin_note_position = sanitize_text_field( wp_unslash( $admin_note_position ) );
		if ( $admin_note_position !== 'top' && $admin_note_position !== 'bottom' ) {
			$admin_note_position = 'top';
		}

		$current_user_id = get_current_user_id();

		if ( is_numeric( $postid ) ) {
			$post_author_id   = (int) get_post_field( 'post_author', $postid );
			$post_groups      = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
			$post_groups      = $pm_request->pg_filter_users_group_ids( $post_groups );
			$post_groups      = is_array( $post_groups ) ? $post_groups : array( $post_groups );
			$is_group_leader  = false;
			foreach ( $post_groups as $post_group_id ) {
				if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
					$is_group_leader = true;
					break;
				}
			}
			$allowed          = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
				|| $is_group_leader;
			if ( ! $allowed ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}
			if ( $is_delete_request == 1 ) {
				 $html_generator->delete_admin_note_popup( $postid );
			} else {
				update_post_meta( $postid, 'pm_admin_note_content', $admin_note_content );
				update_post_meta( $postid, 'pm_admin_note_position', $admin_note_position );
				$html_generator->save_admin_note_success_popup( 'success' );
			}
		} else {
			$ids = PM_sanitizer::safe_unserialize_array( $pm_request->pm_encrypt_decrypt_pass( 'decrypt', $postid ) );
			foreach ( $ids as $id ) {
				$post_author_id   = (int) get_post_field( 'post_author', $id );
				$post_groups      = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
				$post_groups      = $pm_request->pg_filter_users_group_ids( $post_groups );
				$post_groups      = is_array( $post_groups ) ? $post_groups : array( $post_groups );
				$is_group_leader  = false;
				foreach ( $post_groups as $post_group_id ) {
					if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
						$is_group_leader = true;
						break;
					}
				}
				$allowed          = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
					|| $is_group_leader;
				if ( ! $allowed ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
			}
			foreach ( $ids as $id ) {
				update_post_meta( $id, 'pm_admin_note_content', $admin_note_content );
				update_post_meta( $id, 'pm_admin_note_position', $admin_note_position );
			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = count( $ids );
			$html_generator->save_admin_note_success_popup( $change_status );
		}

		die;
	}

	public function pm_delete_admin_note() {
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_request      = new PM_request();
		$postid          = filter_input( INPUT_POST, 'post_id' );
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'delete_pm_admin_note' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$current_user_id = get_current_user_id();
		$post_author_id   = (int) get_post_field( 'post_author', $postid );
		$post_groups      = $pm_request->profile_magic_get_user_field_value( $post_author_id, 'pm_group' );
		$post_groups      = $pm_request->pg_filter_users_group_ids( $post_groups );
		$post_groups      = is_array( $post_groups ) ? $post_groups : array( $post_groups );
		$is_group_leader  = false;
		foreach ( $post_groups as $post_group_id ) {
			if ( ! empty( $post_group_id ) && $pm_request->pg_check_in_single_group_is_user_group_leader( $current_user_id, (string) $post_group_id ) ) {
				$is_group_leader = true;
				break;
			}
		}
		$allowed          = current_user_can( 'manage_options' ) || is_super_admin( $current_user_id )
			|| $is_group_leader;
		if ( ! $allowed ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		$is_delete = delete_post_meta( $postid, 'pm_admin_note_content' );
		$is_delete = delete_post_meta( $postid, 'pm_admin_note_position' );
		if ( $is_delete ) {
			$html_generator->pm_delete_admin_note_success_popup( 'success' );
		} else {
			// echo 'failed';
			$html_generator->pm_delete_admin_note_success_popup( 'failed' );
		}
		die;

	}

	public function pm_send_message_to_author() {
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pmrequests      = new PM_request();
		$postid          = filter_input( INPUT_POST, 'post_id' );
		$gid             = isset( $_POST['gid'] ) ? trim( (string) wp_unslash( $_POST['gid'] ) ) : '';
		$type            = filter_input( INPUT_POST, 'type' );
		$content         = filter_input( INPUT_POST, 'pm_author_message', FILTER_UNSAFE_RAW );
		$current_user    = wp_get_current_user();
		$sid             = $current_user->ID;
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! is_user_logged_in() || 0 === $sid ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}
		if ( ! wp_verify_nonce( $retrieved_nonce, 'send_pm_message_to_author' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
		if ( is_numeric( $postid ) ) {
			if ( $type == 'blog' ) {
				$post = get_post( $postid );
				if ( ! $post ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
				$rid  = $post->post_author;
			} else {
				$rid = absint( $postid );
			}
			if ( $rid <= 0 || ! get_userdata( $rid ) ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}
			$is_msg_sent = $pmrequests->pm_create_message( $sid, $rid, $content );
			if ( $is_msg_sent ) {
				$html_generator->author_msg_send_success_popup( $rid );
			} else {
				// echo 'failed';
				$html_generator->author_msg_send_success_popup( 'failed' );
			}
		} else {
			$basic_functions = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
			$is_group_leader = ( '' !== $gid ) ? $pmrequests->pg_check_in_single_group_is_user_group_leader( $sid, $gid ) : false;
			$is_group_manager = ( '' !== $gid ) ? $basic_functions->pm_user_is_group_manager( $sid, $gid ) : false;
			if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $sid ) && ! $is_group_leader && ! $is_group_manager ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}

			$decrypted_post_id = $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $postid );
			if ( false === $decrypted_post_id || '' === $decrypted_post_id ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}

			$ids = $this->pg_safe_unserialize_array( $decrypted_post_id );
			if ( ! is_array( $ids ) ) {
				wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
			}
			// print_r($ids);
			$i = 0;
			foreach ( $ids as $id ) {
				$id = absint( $id );
				if ( 0 === $id ) {
					continue;
				}
				if ( '' !== $gid && ! $pmrequests->profile_magic_check_is_group_member( $gid, $id ) ) {
					wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
				}
				if ( $id == $sid ) {
					continue;
				}
				$result = $pmrequests->pm_create_message( $sid, $id, $content );
				// print_r($result);
				if ( $result ) {
					$i = $i + 1;
				}
			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = $i;
			$html_generator->author_msg_send_success_popup( $change_status );
		}
		die;

	}

	private function pg_user_belongs_to_group( $user_id, $gid ) {
		$user_id = absint( $user_id );
		$gid     = is_scalar( $gid ) ? trim( (string) $gid ) : '';
		if ( 0 === $user_id || '' === $gid ) {
			return false;
		}

		$pmrequests = new PM_request();
		return $pmrequests->profile_magic_check_is_group_member( $gid, $user_id );
	}

	private function pg_safe_unserialize_array( $value ) {
		return PM_sanitizer::safe_unserialize_array( $value );
	}

	public function pm_get_all_user_blogs_from_group() {
             $pm_sanitizer = new PM_sanitizer;
                
            if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
                $post = $pm_sanitizer->sanitize($_POST);
		$pmrequest    = new PM_request();
		$gid          = (isset($post['gid']))?$post['gid']:'';
		$search_in    = (isset($post['search_in']))?$post['search_in']:'post_title';
		$sort_by      = (isset($post['sortby']))?$post['sortby']:'title-asc';
		$search       = (isset($post['search']))?$post['search']:'';
		$pagenum      = (isset($post['pagenum']))?$post['pagenum']:1;
		$limit        = 10;
		$current_user = wp_get_current_user();
		update_user_meta( $current_user->ID, 'pg_blog_sort_limit', $limit );
		$pmrequest->pm_get_all_group_blogs( $gid, $pagenum, $limit, $sort_by, $search_in, $search );
		die;
	}

	public function pm_invite_user() {
            $pm_sanitizer = new PM_sanitizer;
            
            $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'invite_pm_user' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                $post = $pm_sanitizer->sanitize($_POST);
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pmrequest       = new PM_request();
		$pm_emails       = new PM_Emails();
		$dbhandler       = new PM_DBhandler();
		$gid             = isset( $_POST['gid'] ) && is_scalar( $_POST['gid'] ) ? trim( (string) wp_unslash( $_POST['gid'] ) ) : '';
		$emails          = $post['pm_email_address'];
		$current_user_id = get_current_user_id();
		$basic_functions = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );

		if ( '' === $gid || ! ctype_digit( $gid ) ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}

		$is_group_leader = $pmrequest->pg_check_in_single_group_is_user_group_leader( $current_user_id, $gid );
		$is_group_manager = $basic_functions->pm_user_is_group_manager( $current_user_id, $gid );
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $is_group_manager && ! $is_group_leader ) {
			wp_die( __( 'Unauthorized', 'profilegrid-user-profiles-groups-and-communities' ), '', array( 'response' => 403 ) );
		}

		$group_type    = $pmrequest->profile_magic_get_group_type( $gid );
		$is_paid_group = (float) $pmrequest->profile_magic_check_paid_group( $gid ) > 0;
		
		$message      = '';
		$has_success  = false;
		$has_failure  = false;
		foreach ( $emails as $email ) {
			$user_id = email_exists( sanitize_email( $email) );
			if ( $user_id ) {
				$profile_url = $pmrequest->pm_get_user_profile_url( $user_id );
				$gids        = $pmrequest->profile_magic_get_user_field_value( $user_id, 'pm_group' );
				$exist_group = $pmrequest->pg_filter_users_group_ids( $gids );

				if ( is_array( $exist_group ) ) {
					$gid_array = $exist_group;
				} else {
					if ( $exist_group != '' && $exist_group != null ) {
						$gid_array = array( $exist_group );
					} else {
						$gid_array = array();
					}
				}

				if ( ! in_array( $gid, $gid_array ) ) {
                                    $send_invitation = $dbhandler->get_global_option_value('pm_allow_registered_users_to_accept_invitation', '0');
                                        if($send_invitation==0 && 'open' === $group_type && ! $is_paid_group)
                                        {
                                            $pmrequest->profile_magic_join_group_fun( $user_id, $gid, $group_type );
                                            $message .= '<div class="pg-invited-user-result pg-group-user-info-box pg-invitation-failed pm-pad10 pm-bg pm-dbfl">
                                                <div class="pm-difl pg-invited-user">' . get_avatar( $email, 26, '', false, array( 'force_display' => true ) ) . '</div>
                                                <div class="pm-difl pg-invited-user-info">
                                                    <div class="pg-invited-user-email pm-dbfl">' . $email . ' &nbsp;</div>
                                                    <div class="pm-dbfl">' . esc_html__( 'User added to the group', 'profilegrid-user-profiles-groups-and-communities' ) . '
                                                        <div class="pm-difr"><a href="' . $profile_url . '" target="_blank">' . esc_html__( 'View Profile', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>
                                                    </div>
                                                </div>
                                            </div>';
                                        }
					else
                                        {
                                            $pm_emails->pm_send_invite_link( $email, $gid );
                                            $message .= '<div class="pg-invited-user-result pg-group-user-info-box pg-invitation-success  pm-pad10 pm-bg pm-dbfl">
                                                <div class="pm-difl pg-invited-user">' . get_avatar( $email, 26, '', false, array( 'force_display' => true ) ) . '</div>
                                                <div class="pm-difl pg-invited-user-info">
                                                        <div class="pg-invited-user-email pm-dbfl">' . $email . ' &nbsp;</div>
                                                    <div class="pm-dbfl">' . esc_html__( 'Invitation sent successfully.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
                                                </div>
                                            </div>';
                                        }

					
				} else {
					$group_name = $dbhandler->get_value( 'GROUPS', 'group_name', $exist_group[0] );
					$group_link = $pmrequest->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
					//$group_link = add_query_arg( 'gid', $gid, $group_link );

					$has_failure = true;
					$message    .= ' <div class="pg-invited-user-result pg-group-user-info-box pg-invitation-failed pm-pad10 pm-bg pm-dbfl">
                        <div class="pm-difl pg-invited-user">' . get_avatar( $email, 26, '', false, array( 'force_display' => true ) ) . '</div>
                        <div class="pm-difl pg-invited-user-info">
                           <div class="pg-invited-user-email pm-dbfl">' . $email . ' &nbsp;</div>
                            <div class="pm-dbfl">' . esc_html__( 'The user you are trying to add is already a member of this group', 'profilegrid-user-profiles-groups-and-communities' ) . '
                                <div class="pm-difr"><a href="' . $profile_url . '" target="_blank">' . esc_html__( 'View Profile', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></div>
                            </div>
                        </div>
                    </div>';

				}
			} else {
				// echo 'test';
				$pm_emails->pm_send_invite_link( $email, $gid );
				$has_success = true;
				$message .= '<div class="pg-invited-user-result pg-group-user-info-box pg-invitation-success  pm-pad10 pm-bg pm-dbfl">
                        <div class="pm-difl pg-invited-user">' . get_avatar( $email, 26, '', false, array( 'force_display' => true ) ) . '</div>
                        <div class="pm-difl pg-invited-user-info">
                                <div class="pg-invited-user-email pm-dbfl">' . $email . ' &nbsp;</div>
                            <div class="pm-dbfl">' . esc_html__( 'Invitation sent successfully.', 'profilegrid-user-profiles-groups-and-communities' ) . '</div>
                        </div>
                    </div>';

			}
		}

		$invite_status = ( $has_failure && ! $has_success ) ? 'failed' : 'results';
		$html_generator->invitation_send_result_success_popup( $message, $invite_status );
		die;
	}

	public function pm_remove_user_from_group() {
		// SECURITY: authentication required.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		$pmrequests      = new PM_request();
		$pm_emails       = new PM_Emails();
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$user_id_raw     = isset( $_POST['user_id'] ) ? wp_unslash( $_POST['user_id'] ) : '';
		$gid             = isset( $_POST['gid'] ) ? trim( (string) wp_unslash( $_POST['gid'] ) ) : '';
		$current_user    = wp_get_current_user();
		$retrieved_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		$basic_functions = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );

		// SECURITY: CSRF check.
		if ( ! wp_verify_nonce( $retrieved_nonce, 'remove_pm_user_from_group' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		// SECURITY: authorization check.
		$current_user_id = get_current_user_id();
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_functions->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		if ( is_numeric( $user_id_raw ) ) {
			$user_id = absint( $user_id_raw );
			$result  = $pmrequests->pg_leave_group( $user_id, $gid );
			if ( $current_user->ID != $user_id ) {
				$pm_emails->pm_send_group_based_notification( $gid, $user_id, 'on_membership_terminate' );
				/* $pm_emails->pm_send_remove_from_group_user_notification($user_id, $gid); */
			}
			$html_generator->pm_remove_user_success_popup( $result );
		} else {
			$user_id_raw = sanitize_text_field( $user_id_raw );
			$ids         = PM_sanitizer::safe_unserialize_array( $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user_id_raw ) );
			foreach ( $ids as $id ) {
				$id     = absint( $id );
				$result = $pmrequests->pg_leave_group( $id, $gid );
				$pm_emails->pm_send_group_based_notification( $gid, $id, 'on_membership_terminate' );

			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = count( $ids );
			$html_generator->pm_remove_user_success_popup( $change_status );
		}

		die;
	}
        
        public function pg_send_notification_on_leave_group($uid, $gid)
        {
            $notification = new Profile_Magic_Notification();
            $notification->pm_removed_old_group_notification( $uid, $gid );
            
        }

	public function pm_activate_user_in_group() {
            // SECURITY: authentication required.
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }
            $pm_sanitizer = new PM_sanitizer;

            $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            $nonce_ok = false;
            if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'activate_pm_user_in_group' ) ) {
                $nonce_ok = true;
            } else {
                // Fallback to existing AJAX nonce.
                check_ajax_referer( 'ajax-nonce', 'nonce', true );
                $nonce_ok = true;
            }
            $post = $pm_sanitizer->sanitize($_POST);
		$pmrequests = new PM_request();
		$pmemails   = new PM_Emails();
		$user_id    = isset( $post['uid'] ) ? $post['uid'] : array();
		$gid        = isset( $post['gid'] ) ? trim( (string) $post['gid'] ) : '';
		$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );

            // SECURITY: authorization check.
            $current_user_id = get_current_user_id();
            if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
                wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }

		if ( is_array( $user_id ) ) {
			foreach ( $user_id as $id ) {
                $id = absint( $id );
				if ( ! $this->pg_user_belongs_to_group( $id, $gid ) ) {
					continue;
				}
				update_user_meta( $id, 'rm_user_status', '0' );
				if ( ! empty( $gid ) ) {
					$pmemails->pm_send_group_based_notification( $gid, $id, 'on_user_activate' );
				}
			}
		} else {
			$user_id = absint( $user_id );
			if ( ! $this->pg_user_belongs_to_group( $user_id, $gid ) ) {
				wp_send_json_error( esc_html__( 'User does not belong to this group', 'profilegrid-user-profiles-groups-and-communities' ), 403 );
				return;
			}
			update_user_meta( $user_id, 'rm_user_status', '0' );
			if ( ! empty( $gid ) ) {
				$pmemails->pm_send_group_based_notification( $gid, $user_id, 'on_user_activate' );
			}
		}
		die;
	}

	public function pm_get_all_users_from_group() {
		$view = filter_input( INPUT_POST, 'view' );
		$gid  = trim( (string) filter_input( INPUT_POST, 'gid' ) );

		if ( 'grid' === $view && $this->pg_can_publicly_view_group_members( $gid ) ) {
			$pmrequest = new PM_request();
			$dbhandler = new PM_DBhandler();
			$search_in = filter_input( INPUT_POST, 'search_in' );
			$sort_by   = filter_input( INPUT_POST, 'sortby' );
			$search    = filter_input( INPUT_POST, 'search' );
			$pagenum   = filter_input( INPUT_POST, 'pagenum' );
			$limit     = filter_input( INPUT_POST, 'limit' );
			if ( ! isset( $limit ) || empty( $limit ) ) {
				$limit = $dbhandler->get_global_option_value( 'pm_number_of_users_on_group_page', '10' );
			}
			$current_user = wp_get_current_user();
			if ( is_user_logged_in() && ! empty( $current_user->ID ) ) {
				update_user_meta( $current_user->ID, 'pg_member_sort_limit', $limit );
			}
			$pmrequest->pm_get_all_users_from_group_grid_view( $gid, $pagenum, $limit, $sort_by, $search_in, $search, $this->profile_magic, $this->version );
			echo '<script>pg_primary_ajustment_during_ajax();</script>';
			die;
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		$pmrequest   = new PM_request();
		$dbhandler    = new PM_DBhandler();
		$gid          = trim( (string) filter_input( INPUT_POST, 'gid' ) );
		$search_in    = filter_input( INPUT_POST, 'search_in' );
		$sort_by      = filter_input( INPUT_POST, 'sortby' );
		$search       = filter_input( INPUT_POST, 'search' );
		$pagenum      = filter_input( INPUT_POST, 'pagenum' );
                $limit        = filter_input( INPUT_POST, 'limit' );
                if(!isset($limit) || empty($limit))
                {
                    $limit        = $dbhandler->get_global_option_value( 'pm_number_of_users_on_group_page', '10' ); // number of rows in page
                }
		$current_user = wp_get_current_user();
		$current_user_id = get_current_user_id();
		$basic_function  = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		if ( is_user_logged_in() && ! empty( $current_user->ID ) ) {
			update_user_meta( $current_user->ID, 'pg_member_sort_limit', $limit );
		}
		if ( $view == '' ) {
			$pmrequest->pm_get_all_users_from_group( $gid, $pagenum, $limit, $sort_by, $search_in, $search );
		} else {
			$pmrequest->pm_get_all_users_from_group_grid_view( $gid, $pagenum, $limit, $sort_by, $search_in, $search, $this->profile_magic, $this->version );
			echo '<script>pg_primary_ajustment_during_ajax();</script>';
		}
		die;
	}

	public function pm_deactivate_user_from_group() {
		// SECURITY: block unauthenticated requests.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		$pmrequests      = new PM_request();
		$pmemails        = new PM_Emails();
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$user_id_raw     = filter_input( INPUT_POST, 'user_id' );
		$gid             = trim( (string) filter_input( INPUT_POST, 'gid' ) );
		$retrieved_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		// SECURITY: CSRF check on the specific action nonce.
		if ( ! wp_verify_nonce( $retrieved_nonce, 'deactivate_pm_user_from_group' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		$current_user_id = get_current_user_id();
		$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		// SECURITY: capability/authorization check.
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
		}

		if ( is_numeric( $user_id_raw ) ) {
			$user_id = absint( $user_id_raw );
			if ( ! $this->pg_user_belongs_to_group( $user_id, $gid ) ) {
				wp_send_json_error( esc_html__( 'User does not belong to this group', 'profilegrid-user-profiles-groups-and-communities' ), 403 );
				return;
			}
			update_user_meta( $user_id, 'rm_user_status', '1' );
			do_action( 'pg_user_suspended', $user_id );
			if ( ! empty( $gid ) ) {
				$pmemails->pm_send_group_based_notification( $gid, $user_id, 'on_user_deactivate' );
			}
			$html_generator->pm_deactivate_user_success_popup( 'success' );
		} else {
			$user_id       = sanitize_text_field( $user_id_raw );
			$ids           = PM_sanitizer::safe_unserialize_array( $pmrequests->pm_encrypt_decrypt_pass( 'decrypt', $user_id ) );
			foreach ( $ids as $id ) {
				$id = absint( $id );
				if ( 0 === $id ) {
					continue;
				}
				if ( ! $this->pg_user_belongs_to_group( $id, $gid ) ) {
					continue;
				}
				update_user_meta( $id, 'rm_user_status', '1' );
				do_action( 'pg_user_suspended', $id );
				if ( ! empty( $gid ) ) {
					$pmemails->pm_send_group_based_notification( $gid, $id, 'on_user_deactivate' );
				}
			}
			$change_status                  = array();
			$change_status['change_status'] = 'bulk';
			$change_status['count']         = count( $ids );
			$html_generator->pm_deactivate_user_success_popup( $change_status );
		}

		die;
	}
	public function pm_generate_auto_password() {
		echo wp_kses_post(wp_generate_password());
		die;
	}

	public function pm_reset_user_password() {
		// SECURITY: authentication required.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}
		$html_generator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pmrequests      = new PM_request();
		$pmemail         = new PM_Emails();
		$user_id         = filter_input( INPUT_POST, 'user_id' );
		$gid             = filter_input( INPUT_POST, 'gid' );
		$password        = filter_input( INPUT_POST, 'pm_new_pass' );
		$send_email      = filter_input( INPUT_POST, 'pm_email_password_to_user' );
		$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
		// SECURITY: CSRF check.
		if ( ! wp_verify_nonce( $retrieved_nonce, 'reset_pm_user_password' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}
		// SECURITY: authorization check.
		$current_user_id = get_current_user_id();
		$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}
                if ( ! current_user_can( 'edit_user', absint( $user_id ) ) ) {
                        die( esc_html__( 'You are not authorized to perform this operation.', 'profilegrid-user-profiles-groups-and-communities' ) );
                }

		$name    = $pmrequests->pm_get_display_name( $user_id );
		delete_user_meta( $user_id, 'user_pass' );
		wp_set_password( $password, $user_id );
		do_action( 'profilegrid_group_manager_resets_password', $user_id );
		$this->profile_magic_set_logged_out_status( $user_id );
		if ( $send_email ) {
			$pmemail->pm_send_group_based_notification( $gid, $user_id, 'on_admin_reset_password' );
		}

		$html_generator->pm_reset_user_password_success_popup( $name, $send_email );
		die;
	}

	public function pm_get_pending_post_from_group() {
		$html_generator = new PM_HTML_Creator();
		$gid            = filter_input( INPUT_POST, 'gid' );
		echo wp_kses_post( $html_generator->pg_get_pending_post_count_html( $gid ) );
		die;
	}



	public function pm_remove_user_group() {
		$pmrequests = new PM_request();
		$uid        = filter_input( INPUT_POST, 'uid' );
		$gid        = filter_input( INPUT_POST, 'gid' );
		$current_user    = wp_get_current_user();
		$retrieved_nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! wp_verify_nonce( $retrieved_nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                if ( $current_user->ID == $uid ) {
                    $result     = $pmrequests->pg_leave_group( $uid, $gid );
                    if ( $result == 'success' ) {
                            echo 'success';
                    }
                    
                }
                else
                {
                    die( esc_html__( 'access denied', 'profilegrid-user-profiles-groups-and-communities' ) );
                }
		
		die;
	}

	public function pm_decline_join_group_request() {
            // SECURITY: authentication required.
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }
            $pm_sanitizer = new PM_sanitizer;

            $nonce     = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            $nonce_ok  = false;
            if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'decline_pm_join_request' ) ) {
                $nonce_ok = true;
            } else {
                // Fallback to existing AJAX nonce parameter.
                check_ajax_referer( 'ajax-nonce', 'nonce', true );
                $nonce_ok = true;
            }
            $post = $pm_sanitizer->sanitize($_POST);
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$pmemails   = new PM_Emails();
                $current_user = wp_get_current_user();
		$uid        = isset( $post['uid'] ) ? $post['uid'] : '';
                $gid        = isset( $_POST['gid'] ) ? trim( (string) wp_unslash( $_POST['gid'] ) ) : '';
			$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
            // SECURITY: authorization check.
            $current_user_id = get_current_user_id();
            if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
                wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }
                $is_leader = $pmrequests->pg_check_in_single_group_is_user_group_leader($current_user->ID, $gid);
                if($is_leader==true || current_user_can( 'manage_options' ))
                {
                    if ( is_numeric( $uid ) ) {

                            $where      = array(
                                    'gid' => $gid,
                                    'uid' => absint( $uid ),
                            );
                            $data       = array( 'status' => '2' );
                            $request_id = $dbhandler->get_value_with_multicondition( 'REQUESTS', 'id', $where );
                            // $dbhandler->update_row('REQUESTS','id', $request_id,$data);
                            $dbhandler->remove_row( 'REQUESTS', 'id', $request_id );
                            $pmemails->pm_send_group_based_notification( $gid, absint( $uid ), 'on_request_denied' );
                            do_action( 'pm_user_membership_request_denied', $gid, absint( $uid ) );
                    } else {
                            $ids = PM_sanitizer::safe_unserialize_array( $uid );
                            foreach ( $ids as $id ) {

                                    $where      = array(
                                            'gid' => $gid,
                                            'uid' => absint( $id ),
                                    );
                                    $data       = array( 'status' => '2' );
                                    $request_id = $dbhandler->get_value_with_multicondition( 'REQUESTS', 'id', $where );
                                    // $dbhandler->update_row('REQUESTS','id', $request_id,$data);
                                    $dbhandler->remove_row( 'REQUESTS', 'id', $request_id );
                                    $pmemails->pm_send_group_based_notification( $gid, $id, 'on_request_denied' );
                                    do_action( 'pm_user_membership_request_denied', $gid, $id );
                            }
                    }

                    echo 'success';
                }
                
		die;
	}

	public function pm_approve_join_group_request() {
            // SECURITY: authentication required.
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }
            $pm_sanitizer = new PM_sanitizer;

            $nonce     = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            $nonce_ok  = false;
            if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'approve_pm_join_request' ) ) {
                $nonce_ok = true;
            } else {
                // Fallback to existing AJAX nonce parameter.
                check_ajax_referer( 'ajax-nonce', 'nonce', true );
                $nonce_ok = true;
            }
            $post = $pm_sanitizer->sanitize($_POST);
		$pmrequest            = new PM_request();
		$dbhandler            = new PM_DBhandler();
                $current_user = wp_get_current_user();
                $path                 = plugins_url( '/partials/images/popup-close.png', __FILE__ );
		$gid                  = isset( $_POST['gid'] ) ? trim( (string) wp_unslash( $_POST['gid'] ) ) : '';
		$uid                  = isset( $post['uid'] ) ? $post['uid'] : '';
		$basic_functions	  = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
            // SECURITY: authorization check.
            $current_user_id = get_current_user_id();
            if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_functions->pm_user_is_group_manager( $current_user_id, $gid ) ) {
                wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
                return;
            }
		$meta_query_array     = $pmrequest->pm_get_user_meta_query( array( 'gid' => $gid ) );
		$is_group_limit       = $dbhandler->get_value( 'GROUPS', 'is_group_limit', $gid );
		$limit                = $dbhandler->get_value( 'GROUPS', 'group_limit', $gid );
		$user_query           = $dbhandler->pm_get_all_users_ajax( '', $meta_query_array );
		$total_users_in_group = $user_query->get_total();
                $is_leader = $pmrequest->pg_check_in_single_group_is_user_group_leader($current_user->ID, $gid);
                if($is_leader==true || current_user_can( 'manage_options' ))
                {
                    
                
		if ( $is_group_limit == 1 ) {
			if ( $limit > $total_users_in_group ) {
				if ( is_numeric( $uid ) ) {

					$pmrequest->profile_magic_join_group_fun( absint( $uid ), $gid, 'open' );
					do_action( 'pm_user_membership_request_approve', $gid, absint( $uid ) );
				} else {
					$ids = PM_sanitizer::safe_unserialize_array( $uid );
                                        
					foreach ( $ids as $id ) {
						$pmrequest->profile_magic_join_group_fun( absint( $id ), $gid, 'open' );
						 do_action( 'pm_user_membership_request_approve', $gid, absint( $id ) );
					}
				}
				echo 'success';
				die;
			} else {
				$message = $dbhandler->get_value( 'GROUPS', 'group_limit_message', $gid );
				?>
				<div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
					<?php echo esc_html__( 'User Limit Reached', 'profilegrid-user-profiles-groups-and-communities' ); ?>
					  <div class="pm-popup-close pm-difr">
						  <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
					  </div>
				</div>
				<div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
					<div class="pmrow">  
						<div class="pm-col">
							<p><?php echo esc_html( $message ); ?> </p>         
						</div>
					</div>            
				</div>

			   <div class="pg-group-setting-popup-footer pm-dbfl">
					<div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
				</div>
				<?php
				die;
			}
		} else {
			if ( is_numeric( $uid ) ) {

				$pmrequest->profile_magic_join_group_fun( absint( $uid ), $gid, 'open' );
				 do_action( 'pm_user_membership_request_approve', $gid, absint( $uid ) );
			} else {
				$ids = PM_sanitizer::safe_unserialize_array( $uid );
				foreach ( $ids as $id ) {
					$pmrequest->profile_magic_join_group_fun( absint( $id ), $gid, 'open' );
					 do_action( 'pm_user_membership_request_approve', $gid, absint( $id ) );
				}
			}
			echo 'success';
			die;
		}
                }
	}

	public function pm_get_all_requests_from_group() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'Authentication required', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'approve_pm_join_request' ) ) {
		} elseif ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'decline_pm_join_request' ) ) {
		} else {
			check_ajax_referer( 'ajax-nonce', 'nonce', true );
		}

		$pmrequests = new PM_request();
		$gid        = trim( (string) filter_input( INPUT_POST, 'gid' ) );
		$sort_by    = filter_input( INPUT_POST, 'sortby' );
		$search     = filter_input( INPUT_POST, 'search' );
		$pagenum    = filter_input( INPUT_POST, 'pagenum' );
		$current_user_id = get_current_user_id();
		$basic_function  = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'profilegrid-user-profiles-groups-and-communities' ) );
			return;
		}

		echo wp_kses_post( $pmrequests->pm_get_all_join_group_requests( $gid, $pagenum, $limit = 10, $sort_by, $search ) );
		die;
	}

	private function pg_can_publicly_view_group_members( $gid ) {
		$gid = absint( $gid );
		if ( $gid <= 0 ) {
			return false;
		}

		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$group      = $dbhandler->get_row( 'GROUPS', $gid );

		if ( empty( $group ) ) {
			return false;
		}

		if ( '1' !== (string) $dbhandler->get_global_option_value( 'pm_show_group_members_tab', '1' ) ) {
			return false;
		}


		return true;
	}

	public function user_online_status() {
		// get the user activity the list
		$logged_in_users = get_transient( 'rm_user_online_status' );
                if(!is_array($logged_in_users)){
                    $logged_in_users= array();
                }
		// get current user ID
		$user = wp_get_current_user();

		// check if the current user needs to update his online status;
		// he does if he doesn't exist in the list
		$no_need_to_update = isset( $logged_in_users[ $user->ID ] )

			// and if his "last activity" was less than let's say ...15 minutes ago
			&& $logged_in_users[ $user->ID ] > ( time() - ( 15 * 60 ) );

		// update the list if needed
		if ( ! $no_need_to_update ) {
			$logged_in_users[ $user->ID ] = time();
			set_transient( 'rm_user_online_status', $logged_in_users, $expire_in = ( 30 * 60 ) ); // 30 mins
		}

		//wp_schedule_single_event( time(), 'twicedaily', 'clean_user_online_status' );
	}

	public function clean_user_online_status() {
		$logged_in_users = get_transient( 'rm_user_online_status' );
		foreach ( $logged_in_users as $user => $time ) {
			if ( time() >= $time + 3600 ) {
				unset( $logged_in_users[ $user ] );
			}
		}
		set_transient( 'rm_user_online_status', $logged_in_users, $expire_in = ( 30 * 60 ) );
	}

	public function profile_magic_set_logged_out_status( $uid = '' ) {
		if ( $uid == '' ) {
			$current_user = wp_get_current_user();
			$uid          = $current_user->ID;
		}
		 $logged_in_users = get_transient( 'rm_user_online_status' );

		if ( isset( $logged_in_users ) && is_array( $logged_in_users ) && ! empty( $logged_in_users ) && isset( $logged_in_users[ $uid ] ) ) {
			unset( $logged_in_users[ $uid ] );
		}
		 set_transient( 'rm_user_online_status', $logged_in_users, $expire_in = ( 30 * 60 ) );
	}



	public function profile_magic_rm_form_submission( $form_id, $user_id, $rm_data ) {
		$pmrequests = new PM_request();
                $form_factory = defined('REGMAGIC_ADDON') ? new RM_Form_Factory_Addon() : new RM_Form_Factory();
                $fe_form = $form_factory->create_form($form_id);
              
                
		if ( is_array( $user_id ) ) {
			$uid = $user_id['user_id'];
		} else {
			$uid = $user_id;}

		$form_type = $pmrequests->pm_check_rm_form_type( $form_id );
		if ( $form_type == '1' && is_user_logged_in() && $user_id == null ) {
			$user_id = get_current_user_id();
			$uid     = $user_id;
		}
		if ( $form_type == '1' && isset( $user_id ) && $user_id != null) {
			$associate_groups = $pmrequests->pm_check_rm_form_associate_with_groups( $form_id );

			if ( ! empty( $associate_groups ) ) {
				foreach ( $associate_groups as $group ) {
					$group_limit = $pmrequests->pm_check_group_limit( $group );
					if ( $group_limit != '' ) {
						echo esc_html( $group_limit );
						continue;}
					$group_type = $pmrequests->profile_magic_get_group_type( $group );
                                        if($fe_form->has_price_field()===false)
                                        {
                                            $pmrequests->profile_magic_join_group_fun( $uid, $group, $group_type );
                                        }
					$mapping_fields = $pmrequests->pm_get_map_fields_with_rm_form( $group );

					foreach ( $mapping_fields as $key => $map_field ) {
						$map_with = $map_field['field_map_with'];
						if ( isset( $map_with ) && $map_with != '' ) {
							$rmvalue = $pmrequests->pg_get_filter_rm_value( $map_field, $rm_data, $uid );
							update_user_meta( $uid, $key, $rmvalue );
						}
					}
					unset( $mapping_fields );
				}
			}
		}
	}
        
        public function profile_magic_rm_form_submission_payment_completed($user_email, $form, $sub_id)
        {
          
                $pmrequests = new PM_request();
                $user = get_user_by('email', $user_email);
                $uid = $user->ID;
                $form_id = $form->get_form_id();

		$form_type = $pmrequests->pm_check_rm_form_type( $form_id );
		if ( $form_type == '1' && is_user_logged_in() && $uid == null ) {
			$user_id = get_current_user_id();
			$uid     = $user_id;
		}
		if ( $form_type == '1' && isset( $uid ) && $uid != null) {
			$associate_groups = $pmrequests->pm_check_rm_form_associate_with_groups( $form_id );

			if ( ! empty( $associate_groups ) ) {
				foreach ( $associate_groups as $group ) {
					$group_limit = $pmrequests->pm_check_group_limit( $group );
					if ( $group_limit != '' ) {
						echo esc_html( $group_limit );
						continue;}
					$group_type = $pmrequests->profile_magic_get_group_type( $group );
                                        $pmrequests->profile_magic_join_group_fun( $uid, $group, $group_type );
                                        
					
				}
			}
		}
        }
	public function pg_rm_registration_tab( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_registrations_title', esc_html__( 'Registration', 'profilegrid-user-profiles-groups-and-communities' ) );
		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_registrations_tab', '0' ) == 1 && class_exists( 'Registration_Magic' ) ) {
			echo '<li class="pm-dbfl pg-rm-registration-tab pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_registration_tab">' . esc_html($title) . '</a></li>';
		}
	}

	public function pg_rm_registration_tab_content( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_registrations_tab', '0' ) == 1 && class_exists( 'Registration_Magic' ) ) {

			echo '<div id="pg_rm_registration_tab" class="pm-blog-desc-wrap pm-difl pg-rm-registration-tab pm-section-content"> <div class="rmagic">';

			echo do_shortcode( '[RM_Front_Submissions view="registrations"]' );

			echo '</div> </div>';
		}
	}

	public function pg_rm_payment_tab( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_payments_title', esc_html__( 'Payment History', 'profilegrid-user-profiles-groups-and-communities' ) );
		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_payments_tab', '0' ) == 1 && class_exists( 'Registration_Magic' ) ) {
			echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_payment_tab">' . esc_html($title) . '</a></li>';
		}
	}

	public function pg_rm_payment_tab_content( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$user       = get_user_by( 'ID', $uid );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_payments_tab', '0' ) == 1 && class_exists( 'Registration_Magic' ) ) {
			echo '<div id="pg_rm_payment_tab" class="pm-blog-desc-wrap pm-difl pm-section-content"><div class="rmagic">';
			echo do_shortcode( '[RM_Front_Submissions view="payments"]' );
			echo '</div></div>';
		}
	}

	public function pg_rm_inbox_tab( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_inbox_title', esc_html__( 'Inbox', 'profilegrid-user-profiles-groups-and-communities' ) );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_inbox_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) ) {
			$inbox      = new RM_Front_Service();
			$user       = get_user_by( 'ID', $uid );
			$user_email = $user->user_email;
			$count      = $inbox->get_email_unread_count( $user_email );
			echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_inbox_tab">' . esc_html($title) . '<span id="pg_show_inbox"><b id="pg_show_inboxs" class="pg-rm-inbox">' .esc_html($count) . '</b></span></a></li>';
		}
	}

	public function pg_rm_inbox_tab_content( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$user       = get_user_by( 'ID', $uid );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_inbox_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) ) {
			echo '<div id="pg_rm_inbox_tab" class="pm-blog-desc-wrap pm-difl pm-section-content"><div class="rmagic">';
			echo do_shortcode( '[RM_Front_Submissions view="inbox"]' );
			echo '</div></div>';
		}
	}

	public function pg_rm_orders_tab( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_orders_title', esc_html__( 'Orders', 'profilegrid-user-profiles-groups-and-communities' ) );
		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_orders_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_orders_tab">' . esc_html($title) . '</a></li>';
		}
	}

	public function pg_rm_orders_tab_content( $uid, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$user       = get_user_by( 'ID', $uid );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_orders_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<div id="pg_rm_orders_tab" class="pm-blog-desc-wrap pm-difl pm-section-content"><div class="rmagic">';
			
			echo do_shortcode( '[RM_Front_Submissions view="orders"]' );
			echo '</div></div>';
		}
	}

	public function pg_rm_downloads_tab( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_downloads_title', esc_html__( 'Downloads', 'profilegrid-user-profiles-groups-and-communities' ) );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_downloads_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_download_tab">' . esc_html($title) . '</a></li>';
		}
	}

	public function pg_rm_downloads_tab_content( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$user       = get_user_by( 'ID', $uid );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_downloads_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<div id="pg_rm_download_tab" class="pm-blog-desc-wrap pm-difl pm-section-content"><div class="rmagic">';
			
			echo do_shortcode( '[RM_Front_Submissions view="downloads"]' );
			echo '</div></div>';
		}
	}

	public function pg_rm_addresses_tab( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$title      = $dbhandler->get_global_option_value( 'pm_rm_addresses_title', esc_html__( 'Addresses', 'profilegrid-user-profiles-groups-and-communities' ) );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_addresses_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg_rm_addresses_tab">' . esc_html($title) . '</a></li>';
		}
	}

	public function pg_rm_addresses_tab_content( $uid, $gid ) {
		 $dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$user       = get_user_by( 'ID', $uid );

		if ( $dbhandler->get_global_option_value( 'pm_enable_rm_addresses_tab', '0' ) == 1 && defined( 'REGMAGIC_GOLD' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<div id="pg_rm_addresses_tab" class="pm-blog-desc-wrap pm-difl pm-section-content"><div class="rmagic">';
			// echo RM_DBManager::get_latest_submission_for_user($user->user_email);
			echo do_shortcode( '[RM_Front_Submissions view="addresses"]' );
			echo '</div></div>';
		}
	}

	public function pg_group_membership_settings_tab( $uid, $gid ) {
		$current_user = get_current_user_id();

		if ( ! $current_user || absint( $uid ) !== $current_user ) {
			return;
		}

		echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#pg-group-membership-tab">' . esc_html__( 'Group Membership', 'profilegrid-user-profiles-groups-and-communities' ) . '</a></li>';
	}

	public function pg_group_membership_settings_tab_content( $uid, $gid ) {
		$current_user = get_current_user_id();

		if ( ! $current_user || absint( $uid ) !== $current_user ) {
			return;
		}

		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();

		$paged = filter_input( INPUT_GET, 'pg_gm_page', FILTER_VALIDATE_INT );
		$paged = ( $paged && $paged > 0 ) ? $paged : 1;

		$per_page = apply_filters( 'pg_group_membership_user_payments_per_page', 10 );
		$per_page = max( 1, absint( $per_page ) );

		$query_args = array(
			'user_id' => $current_user,
			'paged'   => $paged,
			'per_page'=> $per_page,
			'orderby' => 'posted_date',
			'order'   => 'DESC',
		);

		$payments = $pmrequests->pg_get_group_membership_payments( $query_args );
		$items    = isset( $payments['items'] ) ? $payments['items'] : array();
		$total    = isset( $payments['total'] ) ? (int) $payments['total'] : 0;

		$total_pages = ( $per_page > 0 ) ? ceil( $total / $per_page ) : 1;
		$total_pages = max( 1, $total_pages );

		$group_names = array();
		if ( ! empty( $items ) ) {
			$group_ids = array_unique( array_filter( array_map( 'intval', wp_list_pluck( $items, 'gid' ) ) ) );
			foreach ( $group_ids as $group_id ) {
				$group_names[ $group_id ] = $dbhandler->get_value( 'GROUPS', 'group_name', $group_id, 'id' );
			}
		}

		$current_request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$current_url     = $current_request ? home_url( $current_request ) : $pmrequests->pm_get_user_profile_url( $uid );
		$base_url        = remove_query_arg( 'pg_gm_page', $current_url );

		$pagination_links = paginate_links(
			array(
				'base'      => add_query_arg( 'pg_gm_page', '%#%', $base_url ) . '#pg-group-membership-tab',
				'format'    => '',
				'current'   => $paged,
				'total'     => $total_pages,
				'add_args'  => false,
				'prev_text' => __( '&laquo;', 'profilegrid-user-profiles-groups-and-communities' ),
				'next_text' => __( '&raquo;', 'profilegrid-user-profiles-groups-and-communities' ),
			)
		);

		?>
		<div id="pg-group-membership-tab" class="pm-blog-desc-wrap pm-difl pm-section-content pg-group-membership-tab">
			<div class="pg-group-membership-table-wrap">
				<table class="pg-group-membership-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
							<th><?php esc_html_e( 'Gateway', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
							<th><?php esc_html_e( 'Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
							<th><?php esc_html_e( 'Date', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
							<th><?php esc_html_e( 'Reference #', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $items ) ) : ?>
							<tr>
								<td colspan="6"><?php esc_html_e( 'No group membership payments found yet.', 'profilegrid-user-profiles-groups-and-communities' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $items as $item ) : ?>
								<?php
								$group_label = isset( $group_names[ $item->gid ] ) ? $group_names[ $item->gid ] : sprintf( __( 'Group #%d', 'profilegrid-user-profiles-groups-and-communities' ), (int) $item->gid );
								$group_amount = $pmrequests->profile_magic_check_paid_group( (int) $item->gid );
								if ( is_numeric( $group_amount ) ) {
									$amount = number_format_i18n( (float) $group_amount, 2 );
								} elseif ( is_numeric( $item->amount ) ) {
									$amount = number_format_i18n( (float) $item->amount, 2 );
								} else {
									$amount = $item->amount;
								}
								$currency    = ! empty( $item->currency ) ? strtoupper( $item->currency ) : '';
								$amount_text = trim( $currency . ' ' . $amount );
								$date_format = get_option( 'date_format' );
								$date_value  = '';
								$raw_date    = isset( $item->posted_date ) ? $item->posted_date : '';
								if ( ! empty( $raw_date ) && $raw_date !== '0000-00-00' && $raw_date !== '0000-00-00 00:00:00' ) {
									if ( is_numeric( $raw_date ) ) {
										$timestamp = (int) $raw_date;
										if ( $timestamp > 0 ) {
											$date_value = wp_date( $date_format, $timestamp, wp_timezone() );
										}
									} else {
										$timestamp = strtotime( $raw_date );
										if ( $timestamp && $timestamp > 0 ) {
											$date_value = mysql2date( $date_format, $raw_date, true );
										}
									}
								}
								if ( empty( $date_value ) ) {
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
										$date_value = wp_date( $date_format, $timestamp, wp_timezone() );
									}
								}
								if ( empty( $date_value ) ) {
									$txn_id = isset( $item->txn_id ) ? $item->txn_id : '';
									if ( is_string( $txn_id ) && preg_match( '/_(\\d{9,})$/', $txn_id, $matches ) ) {
										$timestamp = (int) $matches[1];
										if ( $timestamp > 0 ) {
											$date_value = wp_date( $date_format, $timestamp, wp_timezone() );
										}
									}
								}
								$date_value = $date_value ? $date_value : __( 'N/A', 'profilegrid-user-profiles-groups-and-communities' );
								$status      = ! empty( $item->status ) ? ucfirst( $item->status ) : '';
								$processor   = ! empty( $item->pay_processor ) ? $pmrequests->pg_get_payment_processor_label( $item->pay_processor ) : '';
								$reference   = ! empty( $item->invoice ) ? $item->invoice : $item->txn_id;
								$reference   = $reference ? $reference : __( 'N/A', 'profilegrid-user-profiles-groups-and-communities' );
								?>
								<tr>
									<td><?php echo esc_html( $group_label ); ?></td>
									<td><?php echo esc_html( $amount_text ); ?></td>
									<td><?php echo esc_html( $processor ); ?></td>
									<td><?php echo esc_html( $status ); ?></td>
									<td><?php echo esc_html( $date_value ); ?></td>
									<td><?php echo esc_html( $reference ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $pagination_links ) : ?>
					<div class="pg-pagination pg-group-membership-pagination">
						<?php echo wp_kses_post( (string) $pagination_links ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function pg_log_payment_complete( $gid, $uid ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();

		$existing = $dbhandler->get_all_result(
			'PAYPAL_LOG',
			'id',
			array(
				'uid'    => (int) $uid,
				'gid'    => (int) $gid,
				'status' => 'completed',
			),
			'results',
			0,
			1
		);
		if ( ! empty( $existing ) ) {
			return;
		}

		$processor     = 'paypal';
		$group_status  = maybe_unserialize( get_user_meta( $uid, 'pm_group_payment_status', true ) );
		if ( is_array( $group_status ) && isset( $group_status[ $gid ] ) ) {
			if ( 'succeeded' === $group_status[ $gid ] || 'stripe' === $group_status[ $gid ] ) {
				$processor = 'stripe';
			}
		}

		$txn_id  = get_user_meta( $uid, 'pm_txn_id', true );
		$invoice = get_user_meta( $uid, 'pm_invoice_' . $gid, true );
		if ( empty( $invoice ) ) {
			$invoice = get_user_meta( $uid, 'pm_invoice', true );
		}

		$pmrequests->pg_insert_payment_log_entry(
			$uid,
			$gid,
			$processor,
			'completed',
			null,
			'',
			$txn_id,
			$invoice,
			array( 'source' => 'profilegrid_payment_complete' )
		);

		// Safety: ensure membership is attached if not already present.
		$user_groups = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$user_groups = $pmrequests->pg_filter_users_group_ids( $user_groups );
		$already_in  = is_array( $user_groups ) ? in_array( $gid, $user_groups, true ) : ( (int) $user_groups === (int) $gid );
		if ( ! $already_in ) {
			$group_type = $pmrequests->profile_magic_get_group_type( $gid );
			$pmrequests->profile_magic_join_group_fun( $uid, $gid, $group_type );
		}
	}

	public function pg_log_woo_wallet_payment( $order_id, $response = null ) {
		if ( ! class_exists( 'WC_Order' ) ) {
			return;
		}

		$order = wc_get_order( absint( $order_id ) );
		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_user_id();
		if ( ! $user_id ) {
			return;
		}

		$pmrequests = new PM_request();
		$gids       = $pmrequests->profile_magic_get_user_field_value( $user_id, 'pm_group' );
		$gid        = $pmrequests->pg_filter_users_group_ids( $gids );
		if ( is_array( $gid ) ) {
			$gid = reset( $gid );
		}
		if ( empty( $gid ) ) {
			return;
		}

		$txn_id   = $order->get_transaction_id();
		$invoice  = $order->get_id();
		$amount   = $order->get_total();
		$currency = $order->get_currency();
		$status   = $order->get_status();
		if ( 'completed' !== $status && 'processing' !== $status && 'succeeded' !== $status ) {
			return;
		}

		$pmrequests->pg_insert_payment_log_entry(
			$user_id,
			$gid,
			'terawallet',
			'completed',
			$amount,
			$currency,
			$txn_id ? $txn_id : '',
			$invoice,
			array( 'source' => 'woo_wallet_payment_processed', 'order_id' => $order_id )
		);
	}

	public function pg_log_mycred_payment( $order_id ) {
		if ( ! class_exists( 'WC_Order' ) ) {
			return;
		}

		$order = wc_get_order( absint( $order_id ) );
		if ( ! $order ) {
			return;
		}

		$payment_method = $order->get_payment_method();
		if ( false === stripos( $payment_method, 'mycred' ) ) {
			return;
		}

		$user_id = $order->get_user_id();
		if ( ! $user_id ) {
			return;
		}

		$pmrequests = new PM_request();
		$gids       = $pmrequests->profile_magic_get_user_field_value( $user_id, 'pm_group' );
		$gid        = $pmrequests->pg_filter_users_group_ids( $gids );
		if ( is_array( $gid ) ) {
			$gid = reset( $gid );
		}
		if ( empty( $gid ) ) {
			return;
		}

		$txn_id   = $order->get_transaction_id();
		$invoice  = $order->get_id();
		$amount   = $order->get_total();
		$currency = $order->get_currency();
		$status   = $order->get_status();
		if ( 'completed' !== $status && 'processing' !== $status && 'succeeded' !== $status ) {
			return;
		}

		$pmrequests->pg_insert_payment_log_entry(
			$user_id,
			$gid,
			'mycred',
			'completed',
			$amount,
			$currency,
			$txn_id ? $txn_id : '',
			$invoice,
			array( 'source' => 'woocommerce_payment_complete', 'order_id' => $order_id )
		);
	}

	public function pg_forget_password_page( $lostpassword_url, $redirect ) {
		$pmrequests          = new PM_request();
		$forget_password_url = $pmrequests->profile_magic_get_frontend_url( 'pm_forget_password_page', '' );
		if ( ! empty( $forget_password_url ) ) {
			return $forget_password_url;
		} else {
			return $lostpassword_url;
		}
	}

	public function pm_send_message_notification( $mid, $args ) {
		$sid         = $args[1];
		$rid         = $args[2];
		$content     = $args[3];
		$identifier  = 'MSG_CONVERSATION';
		$dbhandler   = new PM_DBhandler();
		$pmrequests  = new PM_request();
		$status      = $dbhandler->get_value( $identifier, 'status', $mid, 'm_id' );
		$tid         = $pmrequests->fetch_or_create_thread( $sid, $rid );
		$option      = 'pg_send_email_for_unread_message_' . $tid;
		$alreay_sent = $dbhandler->get_global_option_value( $option, 1 );
		if ( $status == 2 ) {
			$notification = new Profile_Magic_Notification();
			$notification->pm_added_new_message_notification( $rid, $sid, $content );
			$send_email = $dbhandler->get_global_option_value( 'pm_unread_message_notification', '0' );
			if ( $send_email == '1' && $alreay_sent == 0 ) {
				$dbhandler->update_global_option_value( $option, 1 );
				$pmemail = new PM_Emails();
				$pmemail->pm_send_unread_message_notification( $sid, $rid );
			}
		}
		// Get the timestamp for the next event.
		$timestamp = wp_next_scheduled( 'pm_send_message_notification' );
		wp_unschedule_event( $timestamp, 'pm_send_message_notification', array( $mid, $args ) );
		wp_clear_scheduled_hook( 'pm_send_message_notification', array( $mid, $args ) );
	}

	public function profile_magic_user_blogs( $content ) {
		return $this->profile_magic_get_template_html( 'profile-magic-user-blogs', $content );
	}

	public function pm_load_user_blogs_shortcode_posts() {
            $pm_sanitizer = new PM_sanitizer;
                
            if ( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
                die(esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
            }
                $post = $pm_sanitizer->sanitize($_POST);
		$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		if ( $post['authors'] != '' ) {
			$author = explode( ',',$post['authors']);
		} else {
			$author = array();
		}
		 $post_type = explode( ',',$post['posttypes']);
		 
			$pmhtmlcreator->pm_get_user_blogs_shortcode_posts( $author, $post_type,$post['page']);
			die;
	}

	public function pg_get_group_page_link( $page, $default, $gid ) {
		$url = $default;
		if ( $page == 'pm_group_page' && $gid != '' ) {
			$dbhandler     = new PM_DBhandler();
			$identifier    = 'GROUPS';
			$group_options = array();
			$row           = $dbhandler->get_row( $identifier, $gid );
			if ( isset( $row ) && isset( $row->group_options ) && $row->group_options != '' ) {
				$group_options = maybe_unserialize( $row->group_options );
			}

			if ( ! empty( $group_options['group_page'] ) && $group_options['group_page'] != '0' ) {
				$group_page  = $group_options['group_page'];
				$post_status = get_post_status( $group_page );
				if ( $post_status == 'publish' ) {
					$url = get_permalink( $group_page );
				}
			}
                        else
                        {
                            $url = add_query_arg( 'gid',$gid, $default );
                        }
                        
                        $url = apply_filters( 'pg_group_page_permalink',$url,$gid);
		}

		return $url;
	}

	public function pm_get_all_groups() {
		$pmrequest    = new PM_request();
		$dbhandler    = new PM_DBhandler();
		$sort_by      = filter_input( INPUT_POST, 'sortby', FILTER_SANITIZE_SPECIAL_CHARS );
		$search       = sanitize_text_field(filter_input( INPUT_POST, 'search' ,FILTER_SANITIZE_SPECIAL_CHARS ));
                //echo $search;die;
		$pagenum      = filter_input( INPUT_POST, 'pagenum', FILTER_SANITIZE_SPECIAL_CHARS );
		$limit_raw    = filter_input( INPUT_POST, 'limit', FILTER_SANITIZE_SPECIAL_CHARS );
		$limit        = absint( $limit_raw );
		if ( empty( $limit ) ) {
			$limit = $dbhandler->get_global_option_value( 'pm_default_no_of_groups', '10' );
		}
		$current_user = wp_get_current_user();
		$view         = filter_input( INPUT_POST, 'view' );
		$type         = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS );
		$include_raw  = filter_input( INPUT_POST, 'include', FILTER_SANITIZE_SPECIAL_CHARS );
		$exclude_raw  = filter_input( INPUT_POST, 'exclude', FILTER_SANITIZE_SPECIAL_CHARS );
		$paid_raw     = filter_input( INPUT_POST, 'paid', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( is_user_logged_in() && ! empty( $current_user->ID ) ) {
			update_user_meta( $current_user->ID, 'pg_member_sort_limit', $limit );
		}

		$include_ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $include_raw, -1, PREG_SPLIT_NO_EMPTY ) ) );
		$exclude_ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $exclude_raw, -1, PREG_SPLIT_NO_EMPTY ) ) );
		$paid = null;
		if ( $paid_raw !== null && $paid_raw !== '' ) {
			$paid = filter_var( $paid_raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		}
		$filters = array(
			'include' => $include_ids,
			'exclude' => $exclude_ids,
			'type'    => in_array( $type, array( 'open', 'closed' ), true ) ? $type : '',
			'paid'    => $paid,
		);

		$pmrequest->pm_get_all_groups_data( $view, $pagenum, $limit, $sort_by, $search, $filters, $this->profile_magic, $this->version );

		die;
	}

	public function pgrm_profile_image_url( $profile_image_url, $uid ) {
			$path       = plugin_dir_url( __FILE__ );
			$dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
			$size       = 512;

		if ( $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 1 ) {
			$default_avatar_path = $profile_image_url;
		} else {
			$avatarid = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
			if ( $avatarid == '' ) {
				$default_avatar_path = $path . '/partials/images/default-user.png';
			} else {
				$avatar_src = wp_get_attachment_image_src( $avatarid, array( $size, $size ), false );
				if ( is_array( $avatar_src ) ) {
					$default_avatar_path = $avatar_src[0];
				} else {
					$default_avatar_path = $path . '/partials/images/default-user.png';
				}
			}
		}

			$avatarid = $pmrequests->profile_magic_get_user_field_value( $uid, 'pm_user_avatar' );
		if ( isset( $avatarid ) && $avatarid != '' ) {

				 $pm_avatar_src = wp_get_attachment_image_src( $avatarid, array( $size, $size ), false );

			if ( is_array( $pm_avatar_src ) ) {
				return $pm_avatar_src[0];
			} else {
				if ( is_super_admin( $uid ) && $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 0 ) {
					$default_avatar_path = $path . '/partials/images/admin-default-user.png';
				}
				return $default_avatar_path;

			}
		} else {
			if ( is_super_admin( $uid ) && $dbhandler->get_global_option_value( 'pm_enable_gravatars', '0' ) == 0 ) {
						$default_avatar_path = $path . '/partials/images/admin-default-user.png';
			}
				return $default_avatar_path;
		}

	}

	public function profile_magic_profile_tabs( $uid, $gid, $primary_gid ) {
		include 'partials/profile-magic-tabs.php';
	}

	public function pm_profile_about_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		$dbhandler     = new PM_DBhandler();
		$pmrequests    = new PM_request();
		$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		if ( ! empty( $primary_gid ) ) :
			$groupinfo = $dbhandler->get_row( 'GROUPS', $primary_gid );
			if ( ! empty( $groupinfo ) ) {
				$group_leader = maybe_unserialize( $groupinfo->group_leaders );
			}
		endif;
		$current_user = wp_get_current_user();
		if ( ! empty( $gid ) ) {
			$gid_in   = 'gid in(' . implode( ',', $gid ) . ')';
			$sections = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), 1, 'results', 0, false, 'gid,ordering', false, $gid_in );
		}
		?>
			  <div id="pg-about" class="pm-difl pg-about-tab pg-profile-tab-content">
		
			<div class="pm-section pm-dbfl" id="sections">
		<?php
		if ( $uid == $current_user->ID && $dbhandler->get_global_option_value( 'pm_show_user_edit_profile_button', '1' ) == '1' ) :
				$filter_uid = $pmrequests->pm_get_profile_slug_by_id( $uid );
			$redirect_url   = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', site_url( '/wp-login.php' ) );
			$redirect_url   = add_query_arg( 'user_id', $filter_uid, $redirect_url );
			?>
	  <div class="pm-dbfl">    
	  <div class="pm-edit-user pm-difl pm-pad10"> <a href="<?php echo esc_url( $redirect_url ); ?>" class="pm-dbfl">
		  <i class="fa fa-pencil" aria-hidden="true"></i>
			<?php esc_html_e( 'Edit Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> </div>
	  </div>
		<?php endif; ?>
		<?php if ( ! empty( $sections ) && count( $sections ) > 1 && $dbhandler->get_global_option_value( 'pm_show_user_left_menu', '1' ) == '1' ) : ?>
				<svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
	<path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
	<path d="M0-.5h24v24H0z" fill="none"/>
</svg>
	  <div class="pm-section-left-panel pm-section-nav-vertical pm-difl pm-border pm-radius5 pm-bg">
		   
		<ul class="dbfl">
			<?php
			do_action( 'profile_magic_before_profile_section_tab', $uid, $primary_gid );
			// foreach($sections as $section):
			// echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#'.sanitize_key($section->section_name).$section->id.'">'.$section->section_name.'</a></li>';
			// endforeach;

				$pmhtmlcreator->pg_get_profile_sections_tab_header( $uid, $group_leader );


				do_action( 'profile_magic_after_profile_section_tab', $uid, $primary_gid );
				?>
			</ul>
	  	</div>
		<?php else : ?>
<div class="pm-section-left-panel pm-section-no-left-panel pm-section-nav-vertical pm-difl pm-border pm-radius5 pm-bg">
	
</div>
		<?php endif; ?>
		<?php
		do_action( 'profile_magic_before_profile_section_content', $uid, $primary_gid );
		if ( ! empty( $sections ) ) :
			if ( count( $sections ) > 1 ) {
				echo '<div class="pm-section-right-panel">';}
			foreach ( $sections as $section ) :
				?>
	  <div id="<?php echo esc_attr(sanitize_key( $section->section_name ) . $section->id); ?>" class="pm-section-content pm-difl <?php
							if ( count( $sections ) == 1 ) {
								echo 'pm_full_width_profile';}
							?>">
				<?php
				$exclude = apply_filters('pm_exclude_default', '"user_avatar","user_pass","user_name","heading","paragraph","confirm_pass"');
				$fields = $pmrequests->pm_get_frontend_user_meta( $uid, $gid, $group_leader, '', $section->id, $exclude );
				
				if ($fields){
					$pmhtmlcreator->get_user_meta_fields_html( $fields, $uid );
				}
				?>
	  </div>
				<?php
	  endforeach;
			if ( count( $sections ) > 1 ) {
				echo '</div>';}
	  endif;
		do_action( 'profile_magic_after_profile_section_content', $uid, $primary_gid );
		?>
	</div>
			
		</div>   
		<?php
	}

	public function pm_profile_groups_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		echo '<div id="pg-groups" class="pm-dbfl pg-group-tab pg-profile-tab-content">';
		include 'partials/profile-magic-group-tab.php';
		echo '</div>';
	}

	public function pm_profile_blog_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		$dbhandler           = new PM_DBhandler();
		$pmrequests          = new PM_request();
		$current_user        = wp_get_current_user();
		$pmhtmlcreator       = new PM_HTML_Creator( $this->profile_magic, $this->version );
		$pm_submit_blog_page = esc_url_raw( $pmrequests->profile_magic_get_frontend_url( 'pm_submit_blog', '' ) );
		if ( $pm_submit_blog_page != '' ) {
			$string = 'href="' . esc_url( $pm_submit_blog_page ) . '"';
		} else {
			$string = 'id="pm_submit_blog_page"';
		}
		?>
		<div id="pg-blog" class="pm-difl pg-profile-tab-content pg-blog-tab">
		   <?php if ( $uid == $current_user->ID && $dbhandler->get_global_option_value( 'pm_show_user_new_blog_post_button', '1' ) == '1' ) : ?>
			<div class="pg-blog-head pm-dbfl">
			<div class="pg-new-blog-button pm-border">
				<a <?php echo wp_kses_post($string); ?>><?php esc_html_e( 'New Blog Post', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
			</div>
			</div>
			<?php endif; ?>
			<div id="pg-blog-container" class="pm-dbfl">
		   <?php
			$pmhtmlcreator->pm_get_user_blog_posts( $uid );
			?>
			</div>
		</div>
		<?php
	}
	public function pm_profile_messages_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		wp_enqueue_style( 'pg-emojiarea', plugin_dir_url( __FILE__ ) . 'css/emojionearea.min.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'pg-emojiarea', plugin_dir_url( __FILE__ ) . 'js/emojionearea.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'pg-messaging', plugin_dir_url( __FILE__ ) . 'js/pg-messaging.js', array( 'jquery' ), $this->version, true );

		$object                       = array();
		$object['ajax_url']           = admin_url( 'admin-ajax.php' );
		$object['empty_chat_message'] = esc_html__( "I am sorry, I can't send an empty message. Please write something and try sending it again.", 'profilegrid-user-profiles-groups-and-communities' );
		$object['plugin_emoji_url']   = plugin_dir_url( __FILE__ ) . 'partials/images/img';
		$object['seding_text']        = esc_html__( 'Sending', 'profilegrid-user-profiles-groups-and-communities' );
		$object['remove_msg']         = esc_html__( 'This message has been deleted.', 'profilegrid-user-profiles-groups-and-communities' );
                $object['nonce']            = wp_create_nonce( 'ajax-nonce' );
		wp_localize_script( 'pg-messaging', 'pg_msg_object', $object );
		$rid          = absint( filter_input( INPUT_GET, 'rid', FILTER_VALIDATE_INT ) );
		$rid_from_url = $rid;
		$current_user = wp_get_current_user();
		$profilechat  = new ProfileMagic_Chat();
		$pmrequests   = new PM_request();
                $dbhandler = new PM_DBhandler;
                $enable_private_profile = $dbhandler->get_global_option_value( 'pm_enable_private_profile' );
		if ( $uid == $current_user->ID && $enable_private_profile!='1' ) :
			?>
		<div id="pg-messages" class="pm-dbfl pg-profile-tab-content pg-message-tab">
			<?php
			if ( ! $rid ) {
				$threads = $pmrequests->pm_get_user_all_threads( $uid, 1, 1 );
				if ( ! empty( $threads ) ) {
					if ( $uid == $threads[0]->r_id ) {
						$rid = $threads[0]->s_id;
					} else {
						$rid = $threads[0]->r_id;
					}

					$tid = $threads[0]->t_id;
				}
			}
			if ( ! isset( $tid ) ) {
				$tid = $pmrequests->get_thread_id( $rid, $uid );
			}

			if ( $tid == false ) {
				$tid = 0;
			}
			if ( $rid_from_url > 0 && $tid > 0 ) {
				$pmrequests->update_message_status_to_read( $tid );
			}

			$profilechat->pg_show_message_tab_html( $uid, $rid, $tid );
			?>
			
                            <div id="pm-edit-group-popup" style="display: none;">
                                <div class="pm-popup-container" id="pg_message_html_container">


                                </div>
                            </div>
			</div>
			<?php
		endif;
	}
	public function pm_profile_notification_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		$current_user  = wp_get_current_user();
		$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		if ( $uid == $current_user->ID ) :
			?>
		<div id="pg-notifications" class="pm-difl pg-profile-tab-content pg-notification-tab">
			<?php $pmhtmlcreator->pm_get_notification_html( $uid ); ?>
		 </div>
			<?php
		endif;
	}

	public function pm_profile_friends_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		echo '<div id="pg-friends" class="pm-dbfl pg-friend-tab pg-profile-tab-content">';
		include 'partials/profile-magic-friends.php';
		echo '</div>';

	}

	public function pm_profile_settings_tab_content( $id, $tab, $uid, $gids, $gid ) {
		$current_user = wp_get_current_user();
		if ( $current_user->ID == $uid ) {
				echo '<div id="pg-settings" class="pm-dbfl pg-setting-tab pg-profile-tab-content">';
				include 'partials/profile-magic-settings.php';
				echo '</div>';
		}
	}

	public function pm_activate_new_thread() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$return      = array();
		$pmrequests  = new PM_request();
		$pmmessenger = new ProfileMagic_Chat();
		$sid         = get_current_user_id();
		$rid         = $other_uid  = filter_input( INPUT_POST, 'uid' , FILTER_VALIDATE_INT);
		$tid         = $thread_status = $pmrequests->fetch_or_create_thread( $sid, $rid );

		
		$return['tid']     = $tid;
		$return['rid']     = $rid;
		$return['sid']     = $sid;
		$return['threads'] = $pmmessenger->pm_messenger_show_threads( $tid );

		echo wp_json_encode( $return );
		die;
	}

	public function pm_activate_last_thread() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		 $pmrequests = new PM_request();
		$pmmessenger = new ProfileMagic_Chat();
		$return      = array();
		$sid         = $uid = get_current_user_id();
		$threads     = $pmrequests->pm_get_user_all_threads( $sid, 1, 1 );
		if ( ! empty( $threads ) ) {
			if ( $uid == $threads[0]->r_id ) {
				$rid = $threads[0]->s_id;
			} else {
				$rid = $threads[0]->r_id;
			}

			$tid = $threads[0]->t_id;
		}

		if ( ! isset( $tid ) ) {
			$tid = $pmrequests->get_thread_id( $rid, $uid );
		}

		// $pmrequests->pm_update_thread_time($tid,2);
		// $pmrequests->pm_update_thread_status($tid,2);
		$return['tid']     = $tid;
		$return['rid']     = $rid;
		$return['sid']     = $sid;
		$return['threads'] = $pmmessenger->pm_messenger_show_threads( $tid );
		echo wp_json_encode( $return );
		die;
	}

	public function pm_get_active_thread_header() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		 $pmrequests = new PM_request();
		$rid         = filter_input( INPUT_POST, 'uid', FILTER_VALIDATE_INT );
		$profile_url = $pmrequests->pm_get_user_profile_url( $rid );
		$r_avatar    = get_avatar(
			$rid,
			50,
			'',
			false,
			array(
				'class'         => 'pm-user-profile',
				'force_display' => true,
			)
		);
		$r_name      = $pmrequests->pm_get_display_name( $rid, false );
		echo '<div class="pm-conversation-box-user pm-difl"><a href="' . esc_url($profile_url) . '">' . wp_kses_post($r_avatar) . '</a></div>';
		echo '<p>' . wp_kses_post($r_name) . '</p>';
		die;
	}

	public function pm_messages_mark_as_read() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmrequests = new PM_request();
		$tid        = absint( filter_input( INPUT_POST, 'tid', FILTER_VALIDATE_INT ) );
		$thread     = $this->pg_get_authorized_thread( $tid, get_current_user_id() );
		if ( false === $thread ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		$pmrequests->update_message_status_to_read( $tid );
		die;
	}

	public function pm_messages_mark_as_unread() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmrequests = new PM_request();
		$tid        = absint( filter_input( INPUT_POST, 'tid', FILTER_VALIDATE_INT ) );
		$thread     = $this->pg_get_authorized_thread( $tid, get_current_user_id() );
		if ( false === $thread ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		$messages   = $pmrequests->update_message_status_to_unread( $tid );
		if ( ! empty( $messages ) ) {
			echo 'success';
		}

		die;
	}

	public function pg_show_all_threads() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$tid        = absint( filter_input( INPUT_POST, 'tid', FILTER_VALIDATE_INT ) );
		$pmmessenger = new ProfileMagic_Chat();
                $pmrequests = new PM_request();
                $allowed_html = $pmrequests->pg_allowed_html_wp_kses();
		if ( $tid > 0 && false === $this->pg_get_authorized_thread( $tid, get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		$return      = $pmmessenger->pm_messenger_show_threads( $tid );
		echo wp_kses( $return,$allowed_html );
		die;
	}

	public function pg_search_threads() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$search      = filter_input( INPUT_POST, 'search' );
                if(!empty($search))
                { 
                    $search = sanitize_text_field($search);
                }
		$pmmessenger = new ProfileMagic_Chat();
                $pmrequests = new PM_request();
                $allowed_html = $pmrequests->pg_allowed_html_wp_kses();
		$return      = $pmmessenger->pm_messenger_search_threads( $search );
		echo wp_kses( $return,$allowed_html );
		die;
	}

	public function profile_magic_shortcode_user_image( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$useremail = $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'user_email' );
			echo get_avatar(
				$useremail,
				150,
				'',
				false,
				array(
					'class'         => 'pm-user',
					'force_display' => true,
				)
			);
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_display_name( $content ) {
		 $pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post( $pmrequests->pm_get_display_name( $attributes['uid'], true ) );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_first_name( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		  $user_exists      = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post($pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'first_name' ));
		 endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_last_name( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post($pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'last_name' ));
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_email( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post($pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'user_email' ));
		 endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_cover_image( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post($pmrequests->profile_magic_get_cover_image( $attributes['uid'], 'pm-cover-image' ));
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_default_group( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo wp_kses_post($pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'group_name' ));
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_all_groups( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array(
			'uid' => $current_user->ID,
			'sep' => ', ',
		);
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$sep         = $attributes['sep'];
			$gids        = get_user_meta( $attributes['uid'], 'pm_group', true );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$group_array = array();
			if ( ! empty( $gid ) ) :
				foreach ( $gid as $group ) {
					$group_array[] = $dbhandler->get_value( 'GROUPS', 'group_name', $group );
				}
		endif;
			echo wp_kses_post(implode( $sep, $group_array ));
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_group_badges( $content ) {
		 $pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			echo '<div id="pg-group-badge"><div id="pg-group-badge-dock">';
			echo wp_kses_post( $pmrequests->pg_get_user_groups_badge_slider( $attributes['uid'] ) );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_unread_notifications_count( $content ) {
		$pmrequests      = new PM_request();
		$pm_notification = new Profile_Magic_Notification();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$attributes['uid']  = $current_user->ID;
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$unread_notification = $pm_notification->pm_get_user_unread_notification_count( $attributes['uid'] );
			echo wp_kses_post( $unread_notification );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_unread_messages_count( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$attributes['uid']  = $current_user->ID;
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$threads      = $pmrequests->pm_get_user_all_threads( $attributes['uid'] );
			$thread_count = 0;
			$i            = 0;
			if ( ! empty( $threads ) ) {
				foreach ( $threads as $thread ) {
					$thread_status = $thread->status;
					if ( $thread_status == 2 ) {
						$unread_message_count = $pmrequests->get_unread_msg_count( $thread->t_id );
						if ( $unread_message_count ) {
							$thread_count = $thread_count + $unread_message_count;
						}
					}
				}
			}
			echo wp_kses_post( $thread_count );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_user_about_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_about_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_groups_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );        if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_groups_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		 $html = ob_get_contents();
		 ob_end_clean();
		 return $html;

	}

	public function profile_magic_shortcode_user_blog_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_blog_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_messaging_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_messages_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_notification_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_notification_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_friends_area( $content ) {
		 $pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );

			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			echo '<input type="hidden" name="pm-uid" id="pm-uid" value="' . esc_attr($attributes['uid']) . '" />';
			$this->pm_profile_friends_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_settings_area( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) && is_user_logged_in() ) :
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			echo '<div class="pmagic"><div class="pm-group-view pg-shortcode-content">';
			$this->pm_profile_settings_tab_content( '', '', $attributes['uid'], $gid, $primary_gid );
			echo '</div></div>';
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_account_details( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
                $pm_sanitizer = new PM_sanitizer;
                
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) && is_user_logged_in() ) {
			$uid = $current_user->ID;

			if ( isset( $_POST['my_account_submit'] ) ) {
                            
                            $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
                            if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_my_account_settings_form' ) ) {
                                    die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
                            }
                            $post = $pm_sanitizer->sanitize($_POST);
				$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', site_url( '/wp-login.php' ) );

				$isupdate = update_user_meta( $current_user->ID, 'first_name', $post['first_name'] );
				$isupdate = update_user_meta( $current_user->ID, 'last_name',$post['last_name'] );
				if ( $dbhandler->get_global_option_value( 'pm_allow_user_to_change_email', 0 ) == 1 ) {
					if ( isset( $post['user_email'] ) ) {
						// check if user is really updating the value
						if ( $current_user->user_email != $post['user_email']) {

								// check if email is free to use
							if ( email_exists( $post['user_email'] ) ) {
								$redirect_url = add_query_arg( 'errors', 'email_exists', $redirect_url );
								// Email exists, do not update value.
								// Maybe output a warning.
							} else {
								$isupdate                 = true;
								$current_user->user_email = $post['user_email'];
								$args                     = array(
									'ID'         => $current_user->ID,
									'user_email' => $current_user->user_email,
								);
								wp_update_user( $args );
								do_action( 'pg_update_setting_during_email_change', $current_user->ID, $current_user->user_email );
							}
						}
					}
				}

				$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', site_url( '/wp-login.php' ) );
				if ( $isupdate == false ) {
					$redirect_url = add_query_arg( 'errors', 'no_changes', $redirect_url );
				}
				

			}

			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			?>
		<div id="pg-edit-profile" class="pg-group-reg-form pm-blog-desc-wrap pm-difl pm-section-content">
			<?php
			$themepath = $this->profile_magic_get_pm_theme( 'my-account-tpl' );
			include $themepath;
			?>
		</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}


	public function profile_magic_shortcode_user_change_password_tab( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		 $user_exists       = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) && is_user_logged_in() ) {
			$uid         = $attributes['uid'];
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			?>
		<div id="pg-change-password" class="pg-group-reg-form pm-blog-desc-wrap pm-difl pm-section-content">
			<?php
			$themepath = $this->profile_magic_get_pm_theme( 'change-password-tpl' );
			include $themepath;
			?>
		</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_privacy_tab( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
                $pm_sanitizer = new PM_sanitizer;
                
		ob_start();

		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) && is_user_logged_in() ) {
			if ( isset( $_POST['pg_privacy_submit'] ) ) {
                                $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
                                if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_privacy_settings_form' ) ) {
                                        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
                                }
                                $post = $pm_sanitizer->sanitize($_POST);
				update_user_meta( $current_user->ID, 'pm_profile_privacy', $post['pm_profile_privacy']);
				update_user_meta( $current_user->ID, 'pm_hide_my_profile', $post['pm_hide_my_profile']);
				$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', site_url( '/wp-login.php' ) );
				
			}
			$uid         = $current_user->ID;
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			?>
		<div id="pg-privacy" class="pm-blog-desc-wrap pm-difl pm-section-content">
			<?php
			$themepath = $this->profile_magic_get_pm_theme( 'privacy-settings-tpl' );
			include $themepath;
			?>
		</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_user_delete_account_tab( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
                $pm_sanitizer = new PM_sanitizer;
                
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$user_exists        = $pmrequests->pm_check_user_exist_by_id( $attributes['uid'] );
		if ( $attributes['uid'] !== 0 && is_object( $user_exists ) && is_user_logged_in() ) {
			$uid   = $current_user->ID;
			$error = filter_input( INPUT_GET, 'errors' );
			if ( $error == 'invalid_password' ) {
				$delete_error = esc_html__( 'You entered incorrect password. Please try again.', 'profilegrid-user-profiles-groups-and-communities' );
			}
			if ( isset( $_POST['pm_delete_account'] ) ) {
                            $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
                                if ( ! wp_verify_nonce( $retrieved_nonce, 'pm_delete_account_form' ) ) {
                                        die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
                                }
                                $post = $pm_sanitizer->sanitize($_POST);
				if ( wp_check_password( $post['password'], $current_user->data->user_pass, $current_user->ID ) ) {
					// remove user
					if ( is_multisite() ) {
						if ( ! function_exists( 'wpmu_delete_user' ) ) {
							require_once ABSPATH . 'wp-admin/includes/ms.php';
						}
						wpmu_delete_user( $current_user->ID );
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'errors', 'account_deleted', $redirect_url );
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
					} else {
						if ( ! function_exists( 'wp_delete_user' ) ) {
							require_once ABSPATH . 'wp-admin/includes/user.php';
						}
						wp_delete_user( $current_user->ID );
						$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
						$redirect_url = add_query_arg( 'errors', 'account_deleted', $redirect_url );
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;
					}
				} else {

					 $redirect_url = get_permalink();
					 $redirect_url = add_query_arg( 'errors', 'invalid_password', $redirect_url );
					 wp_safe_redirect( esc_url_raw( $redirect_url . '#pg-delete-account' ) );
					 exit;
				}
			}
			$gids        = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $attributes['uid'], 'pm_group' ) );
			$gid         = $pmrequests->pg_filter_users_group_ids( $gids );
			$primary_gid = $pmrequests->pg_get_primary_group_id( $gid );
			?>
		<div id="pg-delete-account" class="pm-blog-desc-wrap pm-difl pm-section-content">
			<?php
			$themepath = $this->profile_magic_get_pm_theme( 'delete-account-tpl' );
			include $themepath;
			?>
		</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;

	}

	public function profile_magic_shortcode_group_cards( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$default_attributes = array( 'gid' => '' );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$groups             = explode( ',', $attributes['gid'] );
		if ( ! empty( $groups ) ) :
			$gids = $pmrequests->pg_filter_users_group_ids( $groups );
			echo '<div class="pmagic">';
			foreach ( $gids as $gid ) {
				if ( ! empty( $gid ) ) :
					$pmrequests->pg_group_card_html( $gid );
				endif;
			}
			echo '</div>';
			?>
				<div class="pm-popup-mask"></div>    

				<div id="pm-edit-group-popup" style="display: none;">
					<div class="pm-popup-container" id="pg_edit_group_html_container">


					</div>
				</div>
			<?php
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_name( $content ) {
		$dbhandler = new PM_DBhandler();
		ob_start();
		$default_attributes = array( 'gid' => 0 );
		$attributes         = shortcode_atts( $default_attributes, $content );
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
			$groupinfo = $dbhandler->get_row( 'GROUPS', $attributes['gid'] );
			if ( isset( $groupinfo ) ) {
				$value = $groupinfo->group_name;
			} else {
				$value = '';}
			echo wp_kses_post( $value );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_description( $content ) {
		 $dbhandler = new PM_DBhandler();
		ob_start();
		$default_attributes = array( 'gid' => 0 );
		$attributes         = shortcode_atts( $default_attributes, $content );
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
			$groupinfo = $dbhandler->get_row( 'GROUPS', $attributes['gid'] );
			if ( isset( $groupinfo ) ) {
				$value = $groupinfo->group_desc;
			} else {
				$value = '';}
			echo wp_kses_post( $value );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_member_count( $content ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		ob_start();
		$default_attributes = array( 'gid' => '' );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		if ( ! empty( $gid ) && strpos( $gid, ',' ) == false ) :
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
			$user_query  = $dbhandler->pm_get_all_users_ajax( '', $meta_query, '', 0, 10, 'ASC', 'ID', $hide_users );
			$total_users = $user_query->get_total();
			echo wp_kses_post( $total_users );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_manager_count( $content ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		ob_start();
		$default_attributes = array( 'gid' => '' );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		if ( ! empty( $gid ) && strpos( $gid, ',' ) == false ) :
			$leaders = $pmrequests->pg_get_group_leaders( $gid );
			echo count( $leaders );
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_manager_display_name( $content ) {
		$pmrequests = new PM_request();
		$name       = array();
		ob_start();
		$default_attributes = array(
			'gid' => 0,
			'sep' => ', ',
		);
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		$sep                = $attributes['sep'];
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
			$leaders = $pmrequests->pg_get_group_leaders( $gid );
			if ( ! empty( $leaders ) ) {
				foreach ( $leaders as $leader ) {
					$name[] = $pmrequests->pm_get_display_name( $leader );
				}
			}
			if ( ! empty( $name ) ) {

				echo wp_kses_post(implode( $sep, array_unique( $name ) ));
			}
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_manager_display_name_in_list( $content ) {
		$pmrequests = new PM_request();
		ob_start();
		$default_attributes = array( 'gid' => 0 );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
			$leaders = $pmrequests->pg_get_group_leaders( $gid );
			if ( ! empty( $leaders ) ) {
				echo '<ul>';
				foreach ( $leaders as $leader ) {
					echo '<li>' . wp_kses_post($pmrequests->pm_get_display_name( $leader )) . '</li>';
				}
				echo '</ul>';
			}
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_members_display_name_in_list( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
		ob_start();
		$default_attributes = array( 'gid' => 0 );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
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
			$user_query  = $dbhandler->pm_get_all_users_ajax( '', $meta_query, '', 0, -1, 'ASC', 'ID', $hide_users );
			$total_users = $user_query->get_total();
			$users       = $user_query->get_results();
			if ( ! empty( $users ) ) {
				echo '<ul>';
				foreach ( $users as $user ) {
					echo '<li>' . wp_kses_post($pmrequests->pm_get_display_name( $user->ID )) . '</li>';
				}
				echo '</ul>';
			}
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_members_cards( $content ) {
		$pmrequests = new PM_request();
		$dbhandler  = new PM_DBhandler();
		ob_start();
		$default_attributes = array(
			'gid'    => '',
			'sortby' => '',
		);
		$attributes         = shortcode_atts( $default_attributes, $content );

		
		if ( isset( $attributes['sortby'] ) && in_array( $attributes['sortby'], array( 'oldest_first', 'latest_first', 'first_name_asc', 'first_name_desc', 'last_name_asc', 'last_name_desc' ) ) ) {
			$sortby = $attributes['sortby'];
		}

		if ( empty( $sortby ) ) {
			$sortby = $dbhandler->get_global_option_value( 'pm_default_group_sorting', 'oldest_first' );

		}

		switch ( $sortby ) {
			case 'name_asc':
				$sort  = 'display_name';
				$order = 'ASC';
				break;
			case 'name_desc':
				$sort  = 'display_name';
				$order = 'DESC';
				break;
			case 'latest_first':
				$sort  = 'registered';
				$order = 'DESC';
				break;
			case 'oldest_first':
				$sort  = 'registered';
				$order = 'ASC';
				break;
			case 'suspended':
				$sort          = 'registered';
				$order         = 'DESC';
				$get['status'] = '1';
				break;
			case 'first_name_asc':
				$sort  = 'first_name';
				$order = 'ASC';
				break;
			case 'first_name_desc':
				$sort  = 'first_name';
				$order = 'DESC';
				break;
			case 'last_name_asc':
				$sort  = 'last_name';
				$order = 'ASC';
				break;
			case 'last_name_desc':
				$sort  = 'last_name';
				$order = 'DESC';
				break;
			default:
				$sort  = 'display_name';
				$order = 'ASC';
				break;

		}

		$gid = $attributes['gid'];
		if ( ! empty( $gid ) && strpos( $gid, ',' ) == false ) :
			$pagenum      = filter_input( INPUT_GET, 'pagenum' );
			$pagenum      = isset( $pagenum ) ? absint( $pagenum ) : 1;
			$limit        = $dbhandler->get_global_option_value( 'pm_number_of_users_on_group_page', '10' );
			$offset       = ( $pagenum - 1 ) * $limit;
			$hide_users   = $pmrequests->pm_get_hide_users_array();
			$meta_query   = array(
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
			$user_query   = $dbhandler->pm_get_all_users_ajax( '', $meta_query, '', $offset, $limit, $order, $sort, $hide_users );
			$total_users  = $user_query->get_total();
			$users        = $user_query->get_results();
			$num_of_pages = ceil( $total_users / $limit );
			
			$pagination = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );
			$leaders    = $pmrequests->pg_get_group_leaders( $gid );

			if ( $dbhandler->get_global_option_value( 'pm_enable_private_profile' ) == '1' ) {
				if ( $dbhandler->get_global_option_value( 'pm_show_user_profile_on_group_page' ) == '1' ) {
					$show_members      = 1;
					$hide_profile_link = 1;
				} else {
					$show_members      = 0;
					$hide_profile_link = 1;
				}
			} else {
				$show_members      = 1;
				$hide_profile_link = '';
			}

			?>
			   <div class="pmagic">
				<div id="pg_members" class="pm-dbfl">
					<select class="pg-custom-select" name="member_sort_by_grid" id="member_sort_by_grid" onchange="pm_get_all_users_from_group_grid_view(1,'grid')" style="display:none;">
					<option value="oldest_first" <?php selected( 'oldest_first', $sortby ); ?>><?php esc_html_e( 'Oldest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
					<option value="latest_first"  <?php selected( 'latest_first', $sortby ); ?>><?php esc_html_e( 'Newest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
					<option value="first_name_asc"  <?php selected( 'first_name_asc', $sortby ); ?>><?php esc_html_e( 'First Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
					<option value="first_name_desc"  <?php selected( 'first_name_desc', $sortby ); ?>><?php esc_html_e( 'First Name Alphabetically Z - A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
					<option value="last_name_asc"  <?php selected( 'last_name_asc', $sortby ); ?>><?php esc_html_e( 'Last Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
					<option value="last_name_desc"  <?php selected( 'last_name_desc', $sortby ); ?>><?php esc_html_e( 'Last Name Alphabetically Z- A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>

				</select>
			<div id="pg_members_grid_view">    
			<input type="hidden" name="pg-groupid" id="pg-groupid" value="<?php echo esc_attr( $gid ); ?>" />
			<input type="hidden" id="pg-gid" name="pg-gid"  value="<?php echo esc_attr( $gid ); ?>" />
			
			<?php
			$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					$pmhtmlcreator->get_group_page_fields_html( $user->ID, $gid, $leaders, 150, array( 'class' => 'user-profile-image' ), $hide_profile_link );
				}
			} else {
				echo '<div class="pg-alert-warning pg-alert-info">';
				esc_html_e( 'No User Profile is registered in this Group', 'profilegrid-user-profiles-groups-and-communities' );
				echo '</div>';
			}

			echo '<div class="pm_clear"></div>';
			echo '<div class="pm-member-pagination-grid">' . wp_kses_post($pagination) . '</div>';

			?>
			</div>
				</div>
			   </div>
			<?php
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_group_managers_cards( $content ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		ob_start();
		$default_attributes = array( 'gid' => 0 );
		$attributes         = shortcode_atts( $default_attributes, $content );
		$gid                = $attributes['gid'];
		if ( ! empty( $attributes['gid'] ) && strpos( $attributes['gid'], ',' ) == false ) :
			$leaders = $pmrequests->pg_get_group_leaders( $gid );

			if ( $dbhandler->get_global_option_value( 'pm_enable_private_profile' ) == '1' ) {
				if ( $dbhandler->get_global_option_value( 'pm_show_user_profile_on_group_page' ) == '1' ) {
					$show_members      = 1;
					$hide_profile_link = 1;
				} else {
					$show_members      = 0;
					$hide_profile_link = 1;
				}
			} else {
				$show_members      = 1;
				$hide_profile_link = '';
			}
			?>
		<div class="pmagic">
				<div id="pg_members" class="pm-dbfl">
		<div id="pg_members_grid_view">    
			
			
			<?php
			$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
			if ( ! empty( $leaders ) ) {
				foreach ( $leaders as $user ) {
					$pmhtmlcreator->get_group_page_fields_html( $user, $gid, $leaders, 150, array( 'class' => 'user-profile-image' ), $hide_profile_link );
				}
			}

			echo '<div class="pm_clear"></div>';

			?>
			</div>
				</div>
			   </div>
			<?php
		endif;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function profile_magic_shortcode_content_visible( $args, $content ) {
		$dbhandler          = new PM_DBhandler();
		$pmrequests         = new PM_request();
		$current_user       = wp_get_current_user();
		$default_attributes = array(
			'gid'           => '',
			'min_blog'      => '',
			'min_wc_spent'  => '',
			'min_edd_spent' => '',
		);
		$attributes         = shortcode_atts( $default_attributes, $args );

		$gids  = explode( ',', $attributes['gid'] );
		$ugid  = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $current_user->ID, 'pm_group' ) );
		$gid   = $pmrequests->pg_filter_users_group_ids( $ugid );
		$check = array_intersect( $gids, $gid );
		if ( ! empty( $check ) && is_user_logged_in() ) {
			$visible = 1;
			if ( $attributes['min_blog'] != '' ) {
				$args        = array(
					'orderby'        => 'date',
					'order'          => 'DESC',
					'post_type'      => 'profilegrid_blogs',
					'post_status'    => 'publish',
					'author'         => $current_user->ID,
					'posts_per_page' => -1,
				);
				$total_posts = count( get_posts( $args ) );
				if ( $total_posts >= $attributes['min_blog'] ) {
					$visible = 1;
				} else {
					return '';
				}
			}

			if ( $attributes['min_wc_spent'] != '' && function_exists( 'wc_get_customer_total_spent' ) ) {
				$spends = number_format( wc_get_customer_total_spent( $current_user->ID ) );
				if ( $spends >= $attributes['min_wc_spent'] ) {
					$visible = 1;
				} else {
					return '';
				}
			}
			
			if ( $attributes['min_edd_spent'] != '' ) {
				if ( class_exists( 'Easy_Digital_Downloads' ) ) {
					$customer = new EDD_Customer( $current_user->ID, true );
					
					if ( $customer->purchase_value >= $attributes['min_edd_spent'] ) {
						$visible = 1;
					} else {
						return '';
					}
				} else {
					return '';
				}
			}

			if ( $visible == 1 ) {
				return $content;
			} else {
				return '';
			}
		} else {
			return '';
		}

	}

	public function profile_magic_shortcode_content_not_visible( $args, $content ) {
		$dbhandler          = new PM_DBhandler();
		$pmrequests         = new PM_request();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'gid' => '' );
		$attributes         = shortcode_atts( $default_attributes, $args );

		$gids  = explode( ',', $attributes['gid'] );
		$ugid  = maybe_unserialize( $pmrequests->profile_magic_get_user_field_value( $current_user->ID, 'pm_group' ) );
		$gid   = $pmrequests->pg_filter_users_group_ids( $ugid );
		$check = array_intersect( $gids, $gid );
		if ( ! empty( $check ) && is_user_logged_in() ) {
			 return '';
		} else {
			return $content;

		}
	}

	public function profile_magic_shortcode_content_visible_to_managers( $args, $content ) {
		$dbhandler          = new PM_DBhandler();
		$pmrequests         = new PM_request();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'gid' => '' );
		$attributes         = shortcode_atts( $default_attributes, $args );
		$gid                = $attributes['gid'];
		$gid_array          = array();
		$group_leaders      = array();
		if ( $gid == '' ) {
			$groups = $dbhandler->get_all_result( 'GROUPS' );
			foreach ( $groups as $group ) {
				$gid_array[] = $group->id;
			}
		} else {
			$gid_array = explode( ',', $gid );
		}
		if ( ! empty( $gid_array ) ) {
			foreach ( $gid_array as $g ) {
				$leaders = $pmrequests->pg_get_group_leaders( $g );
				foreach ( $leaders as $leader ) {
					$group_leaders[] = $leader;
				}
			}
		}

		if ( ! empty( $group_leaders ) && in_array( $current_user->ID, $group_leaders ) ) {
			return $content;
		} else {
			return '';
		}

	}

	// Scripts retain WordPress loading so user-specific data stays in the response.
	public function pg_merge_all_css_footer() {
		 $dbhandler         = new PM_DBhandler();
		$enable_performance = $dbhandler->get_global_option_value( 'pm_combine_css', '0' );
		if ( $enable_performance == '1' ) {
			global $wp_styles;
			$footer_css = array( 'jquery-ui-styles', $this->profile_magic, 'jquery.Jcrop.css', 'pm-emoji-picker', 'pm-emoji-picker-nanoscroller', 'pg-password-checker', 'pg-profile-menu', 'pg-dark-theme', 'pg-responsive', $this->pm_theme, 'profilegrid-group-wall', 'profilegrid-user-display-name', 'profilegrid-group-photos', 'profilegrid-custom-profile-slugs', 'profilegrid-custom-group-fields', 'profilegrid-geolocation', 'profilegrid-custom-profile-tabs', 'profilegrid-frontend-group-manager', '$profilegrid-group-multi-admins', 'profilegrid_select2_css', 'profilegrid-profile-labels', 'profilegrid-menu-integration', 'profilegrid-user-profile-status', 'profilegrid-menu-restriction', 'profilegrid-hero-banner', 'profilegrid-woocommerce-product-members-discount', 'profilegrid-woocommerce-product-custom-tabs', 'profilegrid-users-online-widget', 'profilegrid-user-activities', 'profilegrid-woocommerce-product-recommendations', 'profilegrid-woocommerce', 'profilegrid-recent-signup', 'profilegrid-bbpress', 'profilegrid-mailchimp', 'profilegrid-social-connect', 'profilegrid-advanced-woocommerce-integration', 'profilegrid-mycred-integration', 'profilegrid-woocommerce-wishlist' );
			
			$wp_styles->all_deps( $wp_styles->queue );
			$merged_file_location = plugin_dir_path( __FILE__ ) . 'css/merged-css-footer.css';
			$merged_css           = '';
			// Loop javascript files and save to $merged_script variable,'
			foreach ( $wp_styles->to_do as $handle ) {
				if ( ! in_array( $handle, $footer_css ) ) {
					continue;
				}
					$src = strtok( $wp_styles->registered[ $handle ]->src, '?' );
				if ( strpos( $src, 'http' ) !== false ) {
					// Get our site url
					$site_url = site_url();
					if ( strpos( $src, $site_url ) !== false ) {
							$css_file_path = str_replace( $site_url, '', $src );
					} else {
							$css_file_path = $src;
					}
					$css_file_path = ltrim( $css_file_path, '/' );
				} else {
					$css_file_path = ltrim( $src, '/' );
				}

				if ( file_exists( $css_file_path ) ) {
					$localize = '';
					if ( key_exists( 'data', $wp_styles->registered[ $handle ]->extra ) ) {
						$localize = $wp_styles->registered[ $handle ]->extra['data'] . ';';
					}
                                        $filecontent = file_get_contents($css_file_path);
					$merged_css .= $localize . $filecontent;
				}
			}
			file_put_contents( $merged_file_location, $merged_css );
			wp_enqueue_style( 'merged-css-footer', plugin_dir_url( __FILE__ ) . 'css/merged-css-footer.css', array(), $this->version, 'all' );

			foreach ( $wp_styles->to_do as $handle ) {
				if ( ! in_array( $handle, $footer_css ) ) {
					continue;
				}

					wp_deregister_style( $handle );
			}
		}

	}

	public function pg_show_msg_panel() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		$pmrequests = new PM_request();
		$rid        = absint( filter_input( INPUT_POST, 'rid', FILTER_VALIDATE_INT ) );
		$tid        = absint( filter_input( INPUT_POST, 'tid', FILTER_VALIDATE_INT ) );
		$search     = sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'search' ) ) );
		$uid        = get_current_user_id();

		// Use the explicit thread id from the UI when available; falling back to a user-pair lookup
		// breaks valid legacy/custom thread records even though the thread itself still exists.
		$thread = false;
		if ( $tid > 0 ) {
			$thread = $this->pg_get_authorized_thread( $tid, $uid );
		}

		if ( false === $thread && $rid > 0 ) {
			$tid    = absint( $pmrequests->get_thread_id( $rid, $uid ) );
			$thread = $this->pg_get_authorized_thread( $tid, $uid );
		}

		if ( false === $thread ) {
			wp_send_json_error( 'Thread not found', 404 );
		}

		$rid = ( absint( $thread->s_id ) === $uid ) ? absint( $thread->r_id ) : absint( $thread->s_id );
		$chat   = new ProfileMagic_Chat();
		$chat->pg_show_thread_message_panel( $uid, $rid, $tid, $search );
		$pmrequests->update_message_status_to_read( $tid );
		die;
	}

	public function pg_delete_msg() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		$nonce_ok = check_ajax_referer( 'pg_delete_msg_nonce', 'nonce', false );
		if ( ! $nonce_ok ) {
			// Backward compatibility with older clients that send the generic ajax nonce.
			$nonce_ok = check_ajax_referer( 'ajax-nonce', 'nonce', false );
		}
		if ( ! $nonce_ok ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}
		
		$mid = filter_input( INPUT_POST, 'mid' );
		$tid = filter_input( INPUT_POST, 'tid' );
		$mid = absint( $mid );
		$tid = absint( $tid );
		if ( 0 === $mid ) {
			wp_send_json_error( 'Invalid message ID', 400 );
		}

		$dbhandler = new PM_DBhandler();
		$message   = $dbhandler->get_row( 'MSG_CONVERSATION', $mid, 'm_id' );
		if ( empty( $message ) ) {
			wp_send_json_error( 'Message not found', 404 );
		}

		$sender_id       = isset( $message->s_id ) ? (int) $message->s_id : 0;
		$current_user_id = (int) get_current_user_id();
		if ( $sender_id !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$thread_id = isset( $message->t_id ) ? absint( $message->t_id ) : 0;
		$deleted = $dbhandler->remove_row( 'MSG_CONVERSATION', 'm_id', $mid );
		if ( ! $deleted ) {
			wp_send_json_error( 'Message not found', 404 );
		}

		if ( 0 !== $thread_id ) {
			$pmrequests = new PM_request();
			$pmrequests->pm_update_thread_time( $thread_id, 2 );
		}

		wp_send_json_success(
			array(
				'deleted' => $mid,
			)
		);
	}

	private function pg_get_authorized_thread( $tid, $uid ) {
		$tid = absint( $tid );
		$uid = absint( $uid );
		if ( $tid === 0 || $uid === 0 ) {
			return false;
		}

		$dbhandler = new PM_DBhandler();
		$thread    = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		if ( empty( $thread ) ) {
			return false;
		}

		$sender_id   = isset( $thread->s_id ) ? absint( $thread->s_id ) : 0;
		$receiver_id = isset( $thread->r_id ) ? absint( $thread->r_id ) : 0;
		if ( $sender_id !== $uid && $receiver_id !== $uid ) {
			return false;
		}

		return $thread;
	}

	public function pg_msg_delete_thread_popup_html() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Authentication required', 401 );
		}

		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		 $uid  = filter_input( INPUT_POST, 'uid' );
		$mid   = filter_input( INPUT_POST, 'mid' );
		$tid   = filter_input( INPUT_POST, 'tid' );
		$thread = $this->pg_get_authorized_thread( absint( $tid ), get_current_user_id() );
		if ( false === $thread ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}
		$path  = plugins_url( 'partials/images/popup-close.png', __FILE__ );
		$title = esc_html__( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' );
		?>
		<div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
			  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
				  <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
			  </div>
		</div>

		<div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
			<div class="pmrow">        
				<div class="pm-col">
					<?php
						esc_html_e( 'Please confirm you wish to delete this thread.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
							
				</div>
			</div>
		</div>

	   <div class="pg-group-setting-popup-footer pm-dbfl">
		   <div class="pg-group-setting-bt pm-difl"><a onclick="pg_msg_delete_thread(<?php echo esc_attr( $tid ); ?>,<?php echo esc_attr( $uid ); ?>,<?php echo esc_attr( $mid ); ?>)"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
			<div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
		</div>

		<?php
		die;
	}

	public function profile_magic_check_user_exist() {
            $pm_sanitizer = new PM_sanitizer;
                
		if ( !isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
		}
                $post = $pm_sanitizer->sanitize($_POST);
			$pmrequests = new PM_request();

		if ( isset( $post['previous_data'] ) && $post['previous_data'] == $post['userdata'] ) {
			echo 'false';
			die;
		}
		switch ( $post['type'] ) {
			case 'validateUserName':
				if ( $pmrequests->profile_magic_check_username_exist( $post['userdata'] ) ) {
									echo 'true';
				} else {
					echo 'false';
				}

				break;
			case 'validateUserEmail':
				if ( $pmrequests->profile_magic_check_user_email_exist($post['userdata']) ) {
									echo 'true';
				} else {
					echo 'false';
				}
				break;
		}

		die;
	}
        
        
        public function profile_magic_shortcode_section( $content ) {
		$pmrequests    = new PM_request();
		$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID, 'section_id' =>1 ,'gid' =>1);
                $uid    = filter_input( INPUT_GET, 'uid' );
		$attributes         = shortcode_atts( $default_attributes, $content );
                
                if(isset($uid) && !empty($uid))
                {
                    $attributes['uid'] = $uid;
                }
                echo '<div class="pm-shortcode-section-fields">';
                $exclude = '"user_avatar","user_pass","user_name","heading","paragraph","confirm_pass"';

				$exclude = apply_filters("pm_exclude_default", $exclude);

                $fields = $pmrequests->pm_get_frontend_user_meta( $attributes['uid'], $attributes['gid'], array(), '',$attributes['section_id'], $exclude );
				
                $pmhtmlcreator->get_user_meta_fields_html( $fields, $attributes['uid'] );
                echo '</div>';
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
        
        
	public function profile_magic_shortcode_field( $content ) {
            
                $pmrequests    = new PM_request();
		ob_start();
		$current_user       = wp_get_current_user();
		$default_attributes = array( 'uid' => $current_user->ID, 'field_id' =>0, 'show_label'=>false);
                $uid    = filter_input( INPUT_GET, 'uid' );
		$attributes         = shortcode_atts( $default_attributes, $content );
                if(isset($uid) && !empty($uid))
                {
                    $attributes['uid'] = $uid;
                }
                echo '<div class="pm-shortcode-single-field">';
                $pmrequests->pg_get_single_field_value( $attributes['field_id'], $attributes['uid'],$attributes['show_label']);
                echo '</div>';
                $html = ob_get_contents();
		ob_end_clean();
                return $html;
	}
        
        public function pg_filter_hide_groups_on_group_card($additional)
        {
            $pmrequests    = new PM_request();
            $hide_groups = $pmrequests->pg_get_hide_groups_on_group_card();
            if(!empty($hide_groups))
            {
                if($additional!='')
                {
                    $connector = ' AND ';
                }
                else
                {
                    $connector = '';
                }
                $qry = 'id not in('. implode(',', $hide_groups).')';
                $additional .=$connector.$qry;
            }
            
            return $additional;
        }
        
        public function add_theme_class_to_body($classes) {
            $active_theme = wp_get_theme();
            $theme_name = sanitize_html_class($active_theme->get('Name'));
            $classes[] = 'theme-' . strtolower($theme_name);
            return $classes;
        }
        
        public function pm_change_comment_form_edit_profile_link($defaults) 
        {
            
            $dbhandler         = new PM_DBhandler();
            $pmrequests    = new PM_request();
            if (is_user_logged_in() && $dbhandler->get_global_option_value( 'pm_show_user_edit_profile_button', '1' ) == '1' )
            {
                global $user_identity,$required_text,$post_id;
                $uid = get_current_user_id();
                $filter_uid = $pmrequests->pm_get_profile_slug_by_id( $uid );
                $redirect_url   = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', get_edit_user_link() );
                $redirect_url   = add_query_arg( 'user_id', $filter_uid, $redirect_url );
                $defaults['logged_in_as']         = sprintf(
			'<p class="logged-in-as">%s%s</p>',
			sprintf(
				/* translators: 1: User name, 2: Edit user link, 3: Logout URL. */
				__( 'Logged in as %1$s. <a href="%2$s">Edit your profile</a>. <a href="%3$s">Log out?</a>', 'profilegrid-user-profiles-groups-and-communities' ),
				$user_identity,
				$redirect_url,
				/** This filter is documented in wp-includes/link-template.php */
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
			),
			$required_text
		);
            }
           

            return $defaults;
        }

		public function pg_unread_message_toast_html() {
			if ( ! is_user_logged_in() ) {
				return;
			}

			$dbhandler = new PM_DBhandler();
			if ( $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' ) != '1' ) {
				return;
			}

			$pmrequests  = new PM_request();
			$current_uid = get_current_user_id();
			$summary     = $pmrequests->pm_get_unread_message_summary( $current_uid );
			$unread      = isset( $summary['count'] ) ? (int) $summary['count'] : 0;
			$latest_ts   = isset( $summary['latest'] ) ? (int) $summary['latest'] : 0;
			$dismissed   = (int) get_user_meta( $current_uid, 'pg_msg_unread_dismissed_at', true );
			$show_toast  = ( $unread > 0 && ( $latest_ts === 0 || $dismissed < $latest_ts ) );

			$message_url = $pmrequests->pm_get_user_profile_url( $current_uid );
			$latest_rid  = isset( $summary['latest_rid'] ) ? (int) $summary['latest_rid'] : 0;
			$latest_tid  = isset( $summary['latest_tid'] ) ? (int) $summary['latest_tid'] : 0;
			$base_url    = $message_url;
			if ( $message_url && $latest_rid > 0 ) {
				$message_url = add_query_arg( 'rid', $latest_rid, $message_url );
			}
			$message_url = $message_url ? $message_url . '#pg-messages' : '';

			if ( $message_url === '' ) {
				return;
			}

			?>
			<div id="pg-unread-toast" class="pg-unread-toast" data-target="<?php echo esc_url( $message_url ); ?>" data-base-target="<?php echo esc_url( $base_url ); ?>" data-count="<?php echo esc_attr( $unread ); ?>" data-latest="<?php echo esc_attr( $latest_ts ); ?>" data-latest-rid="<?php echo esc_attr( $latest_rid ); ?>" data-latest-tid="<?php echo esc_attr( $latest_tid ); ?>" data-dismissed="<?php echo esc_attr( $dismissed ); ?>" data-show="<?php echo esc_attr( $show_toast ? 1 : 0 ); ?>" data-single-label="<?php echo esc_attr__( 'You have 1 unread message', 'profilegrid-user-profiles-groups-and-communities' ); ?>" data-multi-label="<?php echo esc_attr__( 'You have {{count}} unread messages', 'profilegrid-user-profiles-groups-and-communities' ); ?>">
				<span class="pg-unread-toast__text"></span>
				<button type="button" class="pg-unread-toast__action"><?php esc_html_e( 'Open', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
				<button type="button" class="pg-unread-toast__close" aria-label="<?php esc_attr_e( 'Dismiss', 'profilegrid-user-profiles-groups-and-communities' ); ?>">×</button>
			</div>
			<?php
		}

		public function pm_dismiss_unread_message_toast() {
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( __( 'Not logged in', 'profilegrid-user-profiles-groups-and-communities' ) );
			}

			check_ajax_referer( 'ajax-nonce', 'nonce' );

			$latest_ts = isset( $_POST['latest_ts'] ) ? absint( wp_unslash( $_POST['latest_ts'] ) ) : 0;
			$uid       = get_current_user_id();

			update_user_meta( $uid, 'pg_msg_unread_dismissed_at', $latest_ts );

			wp_send_json_success(
				array(
					'dismissed_at' => $latest_ts,
				)
			);
		}
}
