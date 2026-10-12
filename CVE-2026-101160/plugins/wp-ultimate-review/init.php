<?php

namespace WurReview;


use WurReview\App\Application;
use WurReview\App\License\License_Helper;
use WurReview\App\License\License_Menu;
use WurReview\App\Updater\Pro_Plugin_Updater;

defined( 'ABSPATH') || exit;

/**
 * Class Name : Init - This main class for review plugin
 * Class Type : Normal class
 *
 * initiate all necessary classes, hooks, configs
 *
 * @since 1.0.0
 * @access Public
 */
Class Init {

	/**
	 * veriable for meta box type - $controls
	 * Set Attribute : title_name, type, class, id, name, options data
	 * @since 1.0.0
	 * @access private
	 */
	private $controls = [
		'xs_reviwer_ratting' => [
			'title_name' => 'Rating',
			'type'       => 'select',
			'id'         => 'xs_ratting_id',
			'require'    => 'Yes',
			'class'      => 'xs_rating_class',
			'options'    => [
				'1' => '1 Star',
				'2' => '2 Star',
				'3' => '3 Star',
				'4' => '4 Star',
				'5' => '5 Star',
			],
		],
		'xs_reviw_title'     => [
			'title_name' => 'Review Title',
			'type'       => 'text',
			'require'    => 'Yes',
			'options'    => [],
		],

		'xs_reviwer_name'    => [
			'title_name' => 'Reviewer Name',
			'type'       => 'text',
			'require'    => 'No',
			'options'    => [],
		],
		'xs_reviwer_email'   => [
			'title_name' => 'Reviewer Email',
			'type'       => 'text',
			'require'    => 'Yes',
			'options'    => [],
		],
		'xs_reviwer_website' => [
			'title_name' => 'Website',
			'type'       => 'text',
			'require'    => 'No',
			'options'    => [],
		],
		'xs_reviw_summery'   => [
			'title_name' => 'Review Summary',
			'type'       => 'textarea',
			'require'    => 'Yes',
			'options'    => [],
		],
	];

	/**
	 * veriable for review schema - $schema
	 * Set Attribute : title_name, type, class, id, name, options data
	 * @since 1.0.0
	 * @access private
	 */
	private $schema = [
		'Article'             => 'Article',
		'Book'                => 'Book',
		'Game'                => 'Game',
		'Movie'               => 'Movie',
		'MusicRecording'      => 'MusicRecording',
		'Painting'            => 'Painting',
		'Place'               => 'Place',
		'Product'             => 'Product',
		'Recipe'              => 'Recipe',
		'Restaurant'          => 'Restaurant',
		'SoftwareApplication' => 'SoftwareApplication',
		'Store'               => 'Store',
		'Thing'               => 'Thing',
		'TVSeries'            => 'TVSeries',
		'WebSite'             => 'WebSite',
	];

	/**
	 * veriable for meta box post type - $post_type
	 * @since 1.0.0
	 * @access private
	 */
	private $post_type = 'xs_review';

	/**
	 * veriable for review type - $review_style
	 * @since 1.0.0
	 * @access private
	 */
	private $review_style = [
		'point'      => [
			'title'     => 'Point',
			'thumbnail' => WUR_REVIEW_PLUGIN_URL."assets/images/review-graph/point.png"
		],
		'star'       => [
			'title'     => 'Star',
			'thumbnail' => WUR_REVIEW_PLUGIN_URL."assets/images/review-graph/star.png"
		],
		'percentage' => [
			'Percentage' => 'Point',
			'thumbnail'  => WUR_REVIEW_PLUGIN_URL."assets/images/review-graph/percentage.png"
		],
		'pie'        => [
			'title'     => 'Pie Chart',
			'thumbnail' => WUR_REVIEW_PLUGIN_URL."assets/images/review-graph/pie.png"
		],
	];


	/**
	 * veriable for review type - $review_type
	 * @since 1.0.0
	 * @access private
	 */
	private $review_type = [
		'star'   => 'Star',
		'slider' => 'Slider',
		'bar'    => 'Bar',
		'square' => 'Square',
		'movie'  => 'Movie',
		'pill'   => 'Pill',
	];

	// public $review_type = ['star' => 'Star'];
	//, 'woocommerce'  => 'Woocommerce Product Single page'

	/**
	 * veriable for page enable - $page_enable
	 * @since 1.0.0
	 * @access private
	 */
	private $page_enable = ['post' => 'Post', 'page' => 'Page'];


	/**
	 * Construct the plugin object
	 * @since 1.0.0
	 * @access private
	 */
	public function __construct() {
		$this->review_autoloder();

		// Security/compat: this constructor runs on the 'plugins_loaded' hook,
		// which fires before 'after_setup_theme'/'init'. Calling translation
		// functions (esc_html__, etc.) here triggers WordPress's
		// "Translation loading ... triggered too early" _doing_it_wrong notice
		// (added in WP 6.7). Defer the label translation and the dependent
		// Content::init() call to the 'init' action, same as wur_add_custom_post.
		add_action('init', [$this, 'wur_translate_controls_and_init_content'], 5);
		add_action('init', [$this, 'wur_add_custom_post']);

		if(Application::pro_version_exist()){
			$this->add_updater();
		}

		/**
		 * Initializes the Template Library of the Gutenkit plugin
		 * 
		 * This code block checks if certain conditions are met and then initializes the Template Library of the Gutenkit plugin.
		 * 
		 * Conditions:
		 * - The class '\WurReview\Utilities\Template_Library\Init' exists.
		 * - The plugin 'gutenkit-blocks-addon' is not active or install.
		 * 
		 * If any of the above conditions are met, the Template Library is initialized by creating a new instance of 
		 * the class '\WurReview\Utilities\Template_Library\Init'.
		 * 
		 * @since 2.3.3
		 */
		if ( class_exists('\WurReview\Utilities\Template_Library\Init' ) && ! did_action( 'gutenkit/init' ) ) {
			new \WurReview\Utilities\Template_Library\Init();
		}
	}


	/**
	 * Translate meta box field labels and bootstrap Content hooks.
	 *
	 * Runs on the 'init' action (see __construct) so translation functions
	 * are never called before 'after_setup_theme', avoiding the
	 * "Translation loading ... triggered too early" notice.
	 *
	 * @since 2.4.3
	 * @access public
	 */
	public function wur_translate_controls_and_init_content() {
		$this->controls['xs_reviwer_ratting']['title_name'] = esc_html__('Rating', 'wp-ultimate-review');
		$this->controls['xs_reviw_title']['title_name']     = esc_html__('Review Title', 'wp-ultimate-review');
		$this->controls['xs_reviwer_name']['title_name']    = esc_html__('Reviewer Name', 'wp-ultimate-review');
		$this->controls['xs_reviwer_email']['title_name']   = esc_html__('Reviewer Email', 'wp-ultimate-review');
		$this->controls['xs_reviwer_website']['title_name'] = esc_html__('Website', 'wp-ultimate-review');
		$this->controls['xs_reviw_summery']['title_name']   = esc_html__('Review Summary', 'wp-ultimate-review');

		App\Content::instance()->init($this->controls, $this->post_type);
	}


	/**
	 * Run pro plugin updater here....
	 *
	 */
	private function add_updater(){

		add_action( 'admin_init', function () {
				$plugin_dir_and_filename = '';

				$active_plugins = is_multisite() ? get_site_option( 'active_sitewide_plugins' ) : get_option( 'active_plugins' );

				foreach ( $active_plugins as $active_plugin ) {
					if ( false !== strpos( $active_plugin, 'wp-ultimate-review-pro.php' ) ) {
						$plugin_dir_and_filename = $active_plugin;
						break;
					}
				}

				if ( ! empty( $plugin_dir_and_filename ) ) {
					$license_helper = ( new License_Helper() )->get_license();
					$license_key    = explode( '-', trim( $license_helper['key'] ) );
					$license_key    = ! isset( $license_key[0] ) ? '' : $license_key[0];

					$data = [
						'version' => Application::version(), // current version number.
						'license' => $license_key,
						'item_id' => Application::product_id(), // id of this product in EDD.
						'author'  => Application::author_name(), // author of this plugin.
						'url'     => home_url(),
					];


					if ( class_exists( 'WurReviewPro\Bootstrap\Bootstrap' ) ) {
						$updater = new Pro_Plugin_Updater( Application::account_url(), $plugin_dir_and_filename, $data );
						$updater->initiate();
					}
				}
			} );
	}


	/**
	 * Review wur_add_custom_post.
	 * Method Description: added menu menu in wordpress dashboard.
	 * @since 1.0.0
	 * @access public
	 */
	public function wur_add_custom_post() {
		$labels = [
			'name'               => _x('Wp Reviews', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'singular_name'      => _x('Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'add_new'            => _x('Add Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'add_new_item'       => _x('Add New Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'edit_item'          => _x('Edit Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'new_item'           => _x('New Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'all_items'          => _x('All Reviews', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'view_item'          => _x('View Reviews', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'search_items'       => _x('Search Review', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'not_found'          => _x('No Review found', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'not_found_in_trash' => _x('No Review found in Trash', 'WP Ultimate Reviews', 'wp-ultimate-review'),
			'parent_item_colon'  => '',
			'menu_name'          => _x('WP Reviews', 'WP Ultimate Reviews', 'wp-ultimate-review'),
		];

		register_post_type($this->post_type,
			[
				'labels'              => $labels,
				'supports'            => ['editor'],
				'public'              => true,
				'publicly_queryable'  => true,
				'query_var'           => true,
				'has_archive'         => true,
				'rewrite'             => false,
				'menu_position'       => 108,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'menu_icon'           => WUR_REVIEW_PLUGIN_URL . 'assets/admin/img/icon-menu.png',
				'capability_type'     => 'post',
				'capabilities'        => [
					'create_posts' => 'do_not_allow',
				],
				'map_meta_cap'        => true,
			]
		);

		$this->page_enable = self::__cpt_list();

		new App\Cpt($this->controls, $this->post_type, $this->review_type, $this->review_style, $this->page_enable);
		new App\Settings($this->controls, $this->post_type, $this->review_type, $this->review_style, $this->page_enable);
 
		(new License_Menu())->add_menu();
	}


	/**
	 * Review review_autoloder.
	 * xs_review autoloader loads all the classes needed to run the plugin.
	 * @since 1.0.0
	 * @access private
	 */

	private function review_autoloder() {
		require_once WUR_REVIEW_PLUGIN_PATH . '/autoloader.php';
		Autoloader::run_plugin();
	}


	public static function __cpt_list() {
		//global $wp_post_types;

		$list               = [];
		$exclude            = ['attachment', 'elementor_library', 'xs_review'];
		$post_types_objects = get_post_types(
			[
				'public' => true,
			], 'objects'
		);

		foreach($post_types_objects as $cpt_slug => $post_type) {
			if(in_array($cpt_slug, $exclude)) {
				continue;
			}
			$list[$cpt_slug] = ucfirst($post_type->labels->name);
		}

		return $list;
	}
}

