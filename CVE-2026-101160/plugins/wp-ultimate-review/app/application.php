<?php


namespace WurReview\App;


class Application {

	/**
	 * Plugin Full Name
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public static function name() {
		return 'WP Ultimate Review';
	}


	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 * @var string The plugin version.
	 */
	public static function version() {
		return WUR_REVIEW_VERSION;
	}


	public static function pro_version_exist() {
		return class_exists( \WurReviewPro\Bootstrap\Application::class );
	}

	/**
	 * Package type
	 *
	 * @since 1.1.0
	 * @var string The plugin purchase type [pro/ free].
	 */
	public static function package_type() {
	   return apply_filters('wp_ultimate_review/package_type', 'free');
	}

	/**
	 * Product ID
	 *
	 * @since 1.2.6
	 * @var string The plugin ID in our server.
	 */
	public static function product_id() {
		return '126109';
	}

	/**
	 * Author Name
	 *
	 * @since 1.3.1
	 * @var string The plugin author.
	 */
	public static function author_name() {
		return 'Wpmet';
	}

	/**
	 * Store Name
	 *
	 * @since 1.3.1
	 * @var string The store name: self site, envato.
	 */
	public static function store_name() {
		return 'wpmet';
	}


	/**
	 * API url
	 *
	 * @since 1.0.0
	 * @var string for license, layout notification related functions.
	 */
	static function api_url() {
		return 'https://api.wpmet.com/public/';
	}

	/**
	 * Account url
	 *
	 * @since 1.2.6
	 * @var string for plugin update notification, user account page.
	 */
	static function account_url() {
		return 'https://account.wpmet.com';
	}


	public static function landing_page($part = '') {
		return trailingslashit( 'https://products.wpmet.com/review/' . $part);
	}
}