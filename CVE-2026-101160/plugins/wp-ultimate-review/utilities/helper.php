<?php

namespace XsReview\Utilities;

defined('ABSPATH') || exit;

/**
 * Global static class
 *
 * @since 1.0.0
 *
 */
class Helper {

	/**
	 * Get xs_review current version
	 * @since 1.0.0
	 * @access public
	 */
	public static function current_version() {
		return WUR_REVIEW_VERSION;
	}


	/**
	 * Get xs_review older version if has any
	 * @since 1.0.0
	 * @access public
	 */
	public static function old_version() {
		$version = get_option('WUR_REVIEW_VERSION');

		return null == $version ? -1 : $version;
	}


	/**
	 * Set xs_review installed version as current version
	 * @since 1.0.0
	 * @access public
	 */
	public static function set_version() {
		return update_option('WUR_REVIEW_VERSION', WUR_REVIEW_VERSION);
	}


	/**
	 * Auto generate classname from path
	 * @since 1.0.0
	 * @access public
	 */
	public static function make_classname($dirname) {
		$dirname    = pathinfo($dirname, PATHINFO_FILENAME);
		$class_name = explode('-', $dirname);
		$class_name = array_map('ucfirst', $class_name);
		$class_name = implode('_', $class_name);

		return $class_name;
	}
}
