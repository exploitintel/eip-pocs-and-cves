<?php

namespace WurReview\App\License;

use WurReview\App\Application;

defined( 'ABSPATH') || exit;

/**
 * Allows plugins to use their own update API.
 *
 * @version 1.1.4
 */
class License_Helper {

	protected $oppai_key = 'wp-ultimate-review_oppai';
	protected $license_key = 'wp-ultimate-review_license_key';

	public static function check_license($data, $oppai_key) {

		if(strlen($data['key']) < 28) {
			$data['error']   = 'yes';
			$data['message'] = 'Invalid license key';

			return (object)$data;
		}

		$data['oppai']       = get_option($oppai_key, '');
		$data['action']      = 'activate';
		$data['marketplace'] = Application::store_name();
		$data['author_name'] = Application::author_name();
		$data['v']           = Application::version();


		$url = Application::api_url() . 'license?' . http_build_query($data);

		$args = [
			'timeout'     => 60,
			'redirection' => 3,
			'httpversion' => '1.0',
			'blocking'    => true,
			'sslverify'   => true,
		];


		$res = wp_remote_get($url, $args);


		return (object)json_decode((string)$res['body']);
	}

	public function get_license() {

		$cached = wp_cache_get($this->license_key);

		if(false !== $cached) {
			return $cached;
		}

		$oppai = get_option($this->oppai_key);
		$key   = get_option($this->license_key);

		$license = [
			'checksum' => $oppai,
			'key'      => $key,
		];

		wp_cache_set($this->license_key, $license);

		return $license;
	}

}
