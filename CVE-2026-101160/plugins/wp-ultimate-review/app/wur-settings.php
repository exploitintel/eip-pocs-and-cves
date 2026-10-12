<?php

namespace WurReview\App;


class Wur_Settings {

	private static $instance;

	public static $ok_review_display_settings = 'xs_review_display';
	public static $ok_review_global_settings = 'xs_review_global';
	public static $xs_review_setting_criteria_key = 'xs_review_criteria';
	public static $xs_review_captcha_setting_key = 'xs_review_captcha';

	private $post_type = 'xs_review';

	private $global_settings;
	private $display_settings;
	private $criteria_settings;
	private $captcha_settings;

	private $post_meta;


	public static function instance() {

		if(!self::$instance) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	public function load() {

		$sett_display = get_option(self::$ok_review_display_settings, []);
		$sett_global  = get_option(self::$ok_review_global_settings, []);
		$set_captcha_data = get_option(self::$xs_review_captcha_setting_key, []);

		$this->global_settings = $sett_global;
		$this->display_settings = $sett_display;
		$this->captcha_settings = $set_captcha_data;		
	}


	public function _load_settings_global() {

		$sett_global = get_option(self::$ok_review_global_settings, []);

		$this->global_settings = $sett_global;
	}


	public function _load_settings_display() {

		$sett_display = get_option(self::$ok_review_display_settings, []);

		$this->display_settings = $sett_display;
	}

	public function set_criteria_settings() {
		$this->criteria_settings = get_option(self::$xs_review_setting_criteria_key, []);
	}


	public function get_criteria_settings() {
		return $this->criteria_settings;
	}

	/**
	 * get_captcha_settings
	 * 
	 * @since 2.2.0
	 * @access public
	 * @return array
	 */
	public function get_captcha_settings() {
		return $this->captcha_settings;
	}

	/**
	 * is_captcha_enabled
	 * 
	 * @since 2.2.0
	 * @access public
	 * @return bool
	 */
	public function is_captcha_enabled() {
		return !empty($this->captcha_settings['wur_captcha']['enable']);
	}

	/**
	 * get_captcha_version
	 * 
	 * @since 2.2.0
	 * @access public
	 * @return string
	 */
	public function get_captcha_version(){
		return isset($this->captcha_settings['captcha_version']) ? $this->captcha_settings['captcha_version'] : '';
	}

	/**
	 * get_captcha_site_key
	 * 
	 * @since 2.2.0
	 * @access public
	 * @return string
	 */
	public function get_captcha_site_key(){
		return isset($this->captcha_settings['captcha_site_key']) ? $this->captcha_settings['captcha_site_key'] : '';
	}

	/**
	 * get_captcha_secret_key
	 * 
	 * @since 2.2.0
	 * @access public
	 * @return string
	 */
	public function get_captcha_secret_key(){
		return isset($this->captcha_settings['captcha_secret_key']) ? $this->captcha_settings['captcha_secret_key'] : '';
	}

	public function is_author_review_enabled() {

		return !empty($this->global_settings['author_review']);
	}


	public function is_user_review_enabled() {

		return !empty($this->global_settings['user_review']);
	}


	public function is_reviewer_profile_enabled() {

		return !empty($this->display_settings['form']['xs_reviwer_profile_image_data']['display']['enable']);
	}


	public function is_reviewer_name_enabled() {

		return !empty($this->display_settings['form']['xs_reviwer_name_data']['display']['enable']);
	}


	public function is_reviewer_email_enabled() {

		return !empty($this->display_settings['form']['xs_reviwer_email_data']['display']['enable']);
	}


	public function is_reviewer_website_enabled() {

		return !empty($this->display_settings['form']['xs_reviwer_website_data']['display']['enable']);
	}


	public function is_reviewer_rating_enabled() {

		return !empty($this->display_settings['form']['xs_reviwer_ratting_data']['display']['enable']);
	}


	public function is_reviewer_rating_date_enabled() {

		return !empty($this->display_settings['form']['post_date_data']['display']['enable']);
	}

	public function is_review_title_showing_enabled() {

		return !empty($this->display_settings['form']['xs_reviw_title_data']['display']['enable']);
	}


	public function is_review_text_showing_enabled() {

		return !empty($this->display_settings['form']['xs_reviw_summery_data']['display']['enable']);
	}


	public function get_enabled_post_types($include_self = true) {

		$types = empty($this->display_settings['page']['data']) ? ['post'] : $this->display_settings['page']['data'];

		if($include_self === true) {
			$types[] = $this->post_type;
		}

		return $types;
	}


	public function is_review_enable_for_post_type($type) {

		$stack = $this->get_enabled_post_types();

		return in_array($type, $stack);
	}


	public static function get_xs_post_meta($post_id, $key = 'xs_review_overview_settings') {

		$metaDataOverviewJson = get_post_meta($post_id, $key, true);

		return empty($metaDataOverviewJson) ? [] : json_decode($metaDataOverviewJson);
	}


	/*******************************************************************
	 *
	 * Getters & Setters
	 *
	 *******************************************************************/

	/**
	 * @return mixed
	 */
	public function getGlobalSettings() {
		return $this->global_settings;
	}


	/**
	 * @return mixed
	 */
	public function getDisplaySettings() {
		return $this->display_settings;
	}


	/**
	 * @param mixed $post_meta
	 */
	public function setPostMeta($post_meta) {
		$this->post_meta = $post_meta;
	}

}