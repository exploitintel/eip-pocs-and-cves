<?php

namespace WurReview\App;

defined('ABSPATH') || exit;

use WurReview\App\Cpt;

/**
 * Class Name : Settings - This access for admin
 * Class Type : Normal class
 *
 * initiate all necessary classes, hooks, configs
 *
 * @since 1.0.0
 * @access Public
 */
class Settings {

	private $post_type;
	private $review_type;
	private $review_style;
	private $page_enable;
	private $settingsName = 'Settings';
	private $settingsTitle = 'Review Settings';
	private $controls;



	/**
	 * review score type
	 * @var array
	 */
	public $review_score_input_style = [
		'star'   => [
			'title'     => 'Star',
			'thumbnail' => WUR_REVIEW_PLUGIN_URL."assets/images/review-score/star.png"
		],
		'slider' => [
			'title'     => 'Slider',
			'thumbnail' =>  WUR_REVIEW_PLUGIN_URL."assets/images/review-score/slider.png"
		],
		'bar'    => [
			'Percentage' => 'Bar',
			'thumbnail'  =>  WUR_REVIEW_PLUGIN_URL."assets/images/review-score/bar.png"
		],
		'square' => [
			'title'     => 'Square',
			'thumbnail' => WUR_REVIEW_PLUGIN_URL."assets/images/review-score/square.png"
		],
		'movie'  => [
			'title'     => 'Movie',
			'thumbnail' =>  WUR_REVIEW_PLUGIN_URL."assets/images/review-score/movie.png"
		],
		'pill'   => [
			'title'     => 'Pill',
			'thumbnail' =>  WUR_REVIEW_PLUGIN_URL."assets/images/review-score/pill.png"
		],
	];



	/**
	 * Construct the cpt object
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(array $controls, $post_type, array $review_type, array $review_style, array $page_enable) {
		// Declear public controls
		$this->controls = $controls;

		// Declear public post type
		$this->post_type = $post_type;

		// Declear public review type
		$this->review_type = $review_type;

		// Declear public review type 
		$this->review_style = $review_style;

		// Declear publicpage enable

		$this->page_enable = $page_enable;

		// add admin menu of settings
		add_action('admin_menu', [$this, 'wur_add_admin_menu_settings']);

		// Load css file for settings page
		add_action('admin_enqueue_scripts', [$this, 'wur_settings_css_loader']);

		// Load script file for settings page
		add_action('admin_enqueue_scripts', [$this, 'wur_settings_script_loader']);
	}

	/**
	 * Review wur_add_admin_menu_settings
	 * Method Description: Added admin menu for settings page
	 * @since 1.0.0
	 * @access public
	 */
	public function wur_add_admin_menu_settings() {
		// added new sub menu in custom post type
		add_submenu_page(
			'edit.php?post_type=' . $this->post_type . '',
			esc_html__('Review Settings', 'wp-ultimate-review'),
			esc_html__('Settings', 'wp-ultimate-review'),
			'manage_options',
			'xs_settings',
			[$this, 'wur_settings_view']
		);
	}


	/**
	 * Review wur_settings_view.
	 * Method Description: Settings template view page
	 * @since 1.0.0
	 * @access public
	 */
	public function wur_settings_view() {

		// check current user permisson
		if(!current_user_can( 'manage_options' )){
			wp_die( 'Unauthorized' );
		}
		
		$getAdminEmail = get_option('admin_email');
		$message_status = 'hide';
		$message_text   = '';
		$is_pro_exist = Application::pro_version_exist();

		$global_setting_optionKey = 'xs_review_global';
		$global_setting_criteria_key = 'xs_review_criteria';
		$display_setting_optionKey = 'xs_review_display';
		$captcha_setting_optionKey = 'xs_review_captcha';
		
		// check nonce verification
		if (isset($_POST['wur_form_submit'])
		 && isset($_POST['_wpnonce']) 
		 && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'global_setting_review_form_nonce')) {
		
			
			/**
			 * Global Setting Section
			 * Global Options Key : xs_review_global
			 * Save data 'wp_options' table
			 */
			if(isset($_POST['global_setting_review_form'])) {

				$option_value_global_setting = isset($_POST[$global_setting_optionKey]) ? $_POST[$global_setting_optionKey] : array(); //phpcs:ignore sanitization done in array with self custom function

				$option_value_global_setting = self::sanitize($option_value_global_setting);
				if(update_option($global_setting_optionKey, $option_value_global_setting, 'Yes')) {
					$message_status = 'show';
					$message_text   = esc_html__('Global Settings', 'wp-ultimate-review');
				}
			}
			// output for global settings

			/**
			 * Display Setting Section
			 * Global Options Key : xs_review_display
			 * Save data 'wp_options' table
			 */
			if(isset($_POST['display_setting_review_form'])) {
				$option_value_global_setting = isset($_POST[$display_setting_optionKey]) ? $_POST[$display_setting_optionKey] : array(); //phpcs:ignore sanitization done in array with self custom function

				$option_value_global_setting = self::sanitize($option_value_global_setting);
				if(update_option($display_setting_optionKey, $option_value_global_setting, 'Yes')) {
					$message_status = 'show';
					$message_text   = esc_html__('Display Settings', 'wp-ultimate-review');
				}
			}


			/**
			 * Captcha Setting Section
			 * Global Options Key : xs_review_captcha
			 * Save data 'wp_options' table
			 */
			
			if(isset($_POST['captcha_setting_form'])) {

				$option_value_captcha_setting = isset($_POST[$captcha_setting_optionKey]) ? $_POST[$captcha_setting_optionKey] : array(); //phpcs:ignore sanitization done in array with self custom function
				$option_value_captcha_setting = self::sanitize($option_value_captcha_setting);

				if(update_option($captcha_setting_optionKey, $option_value_captcha_setting, 'Yes')) {
					$message_status = 'show';
					$message_text   = esc_html__('Captcha Settings', 'wp-ultimate-review');
				}
			}


			/**
			 * Crieteria Setting Section
			 * Options Key : xs_review_criteria
			 * Save data 'wp_options' table
			 */

			if(isset($_POST['global_setting_criteria'])) {
				$option_value_global_setting = isset($_POST[$global_setting_criteria_key]) ? $_POST[$global_setting_criteria_key] : []; //phpcs:ignore sanitization done in array with self custom function


				$option_value_global_setting = self::sanitize($option_value_global_setting);
				if(update_option($global_setting_criteria_key, $option_value_global_setting, 'Yes')) {
					$message_status = 'show';
					$message_text   = esc_html__('Criteria Settings', 'wp-ultimate-review');
				}
			}

			
		}

		$return_data_display_setting = get_option($display_setting_optionKey, '');
		$return_data_captcha_setting = get_option($captcha_setting_optionKey, '');
		$return_data_global_setting = get_option($global_setting_optionKey);
		$global_setting_criteria_setting = get_option($global_setting_criteria_key, []);
		
		$defaultLimits = ['review_graph_style' => 4, 'score_input_style' => 4, 'post_criteria' => 3,  'product_criteria' => 3 ];
		$limits =  apply_filters('wp_ultimate_review_settings_limits', $defaultLimits) ;


		require_once(WUR_REVIEW_PLUGIN_PATH . 'views/admin/global-settings-html.php');
	}


	/**
	 * Review wur_settings_css_loader .
	 * Method Description: Settings Css Loader
	 * @since 1.0.0
	 * @access public
	 */
	public function wur_settings_css_loader() {
		wp_enqueue_style( 'wur-wp-dashboard', WUR_REVIEW_PLUGIN_URL . 'assets/admin/css/wur-wp-dashboard.css', [], WUR_REVIEW_VERSION );

		wp_enqueue_style('wur_settings_cute_alert', WUR_REVIEW_PLUGIN_URL . 'assets/public/css/style.css');
		wp_enqueue_style('wur_font_style_css', WUR_REVIEW_PLUGIN_URL . 'assets/admin/css/font-style.css');
		wp_register_style('wur_settings_css', WUR_REVIEW_PLUGIN_URL . 'assets/admin/css/admin-settings.css');
		wp_enqueue_style('wur_settings_css');
		wp_enqueue_style('wp-color-picker');
	}


	/**
	 * Review wur_settings_script_loader .
	 * Method Description: Settings Script Loader
	 * @since 1.0.0
	 * @access public
	 */
	public function wur_settings_script_loader() {

		wp_register_script('wur_settings_cute_alert_script', WUR_REVIEW_PLUGIN_URL . 'assets/public/script/cute-alert.js');
		wp_enqueue_script('wur_settings_cute_alert_script');

		wp_register_script('wur_settings_script1', WUR_REVIEW_PLUGIN_URL . 'assets/admin/script/jquery.form-repeater.js', array('jquery'));
		wp_enqueue_script('wur_settings_script1');

		wp_register_script('wur_settings_script', WUR_REVIEW_PLUGIN_URL . 'assets/admin/script/admin-settings.js', array(
			'jquery',
			'wp-color-picker',
			'wp-i18n',
		), WUR_REVIEW_VERSION);
		wp_set_script_translations('wur_settings_script', 'wp-ultimate-review', WUR_REVIEW_PLUGIN_PATH . 'languages');
		wp_enqueue_script('wur_settings_script');

		wp_enqueue_script('wur_review_content_script', WUR_REVIEW_PLUGIN_URL . 'assets/public/script/content-page.js', ['jquery'], WUR_REVIEW_VERSION);
	}


	public static function sanitize($value, $senitize_func = 'sanitize_text_field') {
		$senitize_func = (in_array($senitize_func, [
			'sanitize_email',
			'sanitize_file_name',
			'sanitize_hex_color',
			'sanitize_hex_color_no_hash',
			'sanitize_html_class',
			'sanitize_key',
			'sanitize_meta',
			'sanitize_mime_type',
			'sanitize_sql_orderby',
			'sanitize_option',
			'sanitize_text_field',
			'sanitize_title',
			'sanitize_title_for_query',
			'sanitize_title_with_dashes',
			'sanitize_user',
			'esc_url_raw',
			'wp_filter_nohtml_kses',
		])) ? $senitize_func : 'sanitize_text_field';

		if(!is_array($value)) {
			return $senitize_func($value);
		} else {
			return array_map(function($inner_value) use ($senitize_func) {
				return self::sanitize($inner_value, $senitize_func);
			}, $value);
		}
	}

	/**
	 * ksess function can return apply allowed tags 
	 * var $raw is the value
	 * var $return_array is by default false if true passed it will return the allowedd tags array
	 */
	public static function kses($raw, $return_array=false) {

		$allowed_tags = array(
			'a'                             => array(
				'class' => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
			),
			'abbr'                          => array(
				'title' => array(),
			),
			'b'                             => array(),
			'blockquote'                    => array(
				'cite' => array(),
			),
			'cite'                          => array(
				'title' => array(),
			),
			'code'                          => array(),
			'del'                           => array(
				'datetime' => array(),
				'title'    => array(),
			),
			'dd'                            => array(),
			'div'                           => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl'                            => array(),
			'dt'                            => array(),
			'em'                            => array(),
			'h1'                            => array(
				'class' => array(),
			),
			'h2'                            => array(
				'class' => array(),
			),
			'h3'                            => array(
				'class' => array(),
			),
			'h4'                            => array(
				'class' => array(),
			),
			'h5'                            => array(
				'class' => array(),
			),
			'h6'                            => array(
				'class' => array(),
			),
			'i'                             => array(
				'class' => array(),
			),
			'img'                           => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'li'                            => array(
				'class' => array(),
			),
			'ol'                            => array(
				'class' => array(),
			),
			'p'                             => array(
				'class' => array(),
			),
			'q'                             => array(
				'cite'  => array(),
				'title' => array(),
			),
			'span'                          => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'iframe'                        => array(
				'width'       => array(),
				'height'      => array(),
				'scrolling'   => array(),
				'frameborder' => array(),
				'allow'       => array(),
				'src'         => array(),
			),
			'strike'                        => array(),
			'br'                            => array(),
			'strong'                        => array(),
			'data-wow-duration'             => array(),
			'data-wow-delay'                => array(),
			'data-wallpaper-options'        => array(),
			'data-stellar-background-ratio' => array(),
			'ul'                            => array(
				'class' => array(),
			),
		);

		// return the allowed tags array for wp_kses();
		if(true === $return_array){
			return $allowed_tags;
		}
		if(function_exists('wp_kses')) { // WP is here
			return wp_kses($raw, $allowed_tags);
		} else {
			return $raw;
		}
	}


	public static function _encode_json($str = '') {
		return json_encode($str, JSON_HEX_APOS);
	}

	public static function _encode_json_unicode($str = '') {
		return json_encode( $str, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE );
	}
}
