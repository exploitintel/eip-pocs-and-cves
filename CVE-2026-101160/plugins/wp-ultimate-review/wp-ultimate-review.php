<?php

/**
 * Plugin Name: Wp Ultimate Review
 * Description: The most advanced WordPress Review Plugin
 * Plugin URI: https://wpmet.com/
 * Author: Wpmet
 * Version: 2.4.3
 * Author URI: https://wpmet.com/
 * Text Domain: wp-ultimate-review
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */

defined('ABSPATH') || exit;

/**
 * Defining static values as global constants
 * @since 1.0.0
 */
define('WUR_REVIEW_VERSION', '2.4.3');

define('WUR_REVIEW_PREVIOUS_STABLE_VERSION', '2.4.2');

define('WUR_REVIEW_KEY', 'wp_ultimate_review');

define('WUR_TEXT_DOMAIN', 'wp-ultimate-review');

define('WUR_REVIEW_FILE_', __FILE__);

define("WUR_REVIEW_PLUGIN_PATH", plugin_dir_path(WUR_REVIEW_FILE_));

define('WUR_REVIEW_PLUGIN_URL', plugin_dir_url(WUR_REVIEW_FILE_));

define('WUR_REST_NAMESPACE', 'wp-ultimate-review');

// initiate actions
add_action('plugins_loaded', 'wur_review_load_plugin_textdomain', 128);


/**
 * Load WUR Review textdomain.
 * @since 1.0.0
 * @return void
 */
function wur_review_load_plugin_textdomain() {
	load_plugin_textdomain('wp-ultimate-review', false, basename(dirname(__FILE__)) . '/languages');

	/**
	 * Load Review Loader main page.
	 * @since 1.0.0
	 * @return plugin output
	 */
	require_once(WUR_REVIEW_PLUGIN_PATH . 'init.php');

	new \WurReview\Init();

	do_action('wur_review/plugin_loaded');

	// custom function added
	if(file_exists(WUR_REVIEW_PLUGIN_PATH . 'inc/custom-function.php')) {
		include(WUR_REVIEW_PLUGIN_PATH . 'inc/custom-function.php');
	}
}




