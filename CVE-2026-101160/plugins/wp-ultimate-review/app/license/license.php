<?php


namespace WurReview\App\License;

use WurReview\App\Application;
use WurReview\Utilities\Singleton;

class License{
	use Singleton;

	protected $oppai_key = 'wp-ultimate-review_oppai';
	protected $license_key = 'wp-ultimate-review_license_key';

	public function status()
	{
		$cached = wp_cache_get('xs_review_license_status');
		if ($cached) {
			return $cached;
		}

		$oppai = get_option($this->oppai_key);
		$key = get_option($this->license_key);

		$status = 'invalid';

		if ($oppai != '' && $key != '') {
			$status = 'valid';
		}
		wp_cache_set('xs_review_license_status', $status);
		return $status;
	}


	public function deactivate() {
		delete_option( $this->oppai_key );
		update_option( $this->license_key, '' );

		return [
			'message' => esc_html__( 'Successfully deactivated', 'wp-ultimate-review' ),
		];
	}

	public function activate( $key ) {
		if ( empty( $key ) ) {
			return [
				'message' => esc_html__( 'License key is empty', 'wp-ultimate-review' ),
			];
		}


		$data = [
			'key' => $key,
			'id'  => Application::product_id(),
		];

		$response = License_Helper::check_license( $data, $this->oppai_key );

		if ( isset( $response->validate ) && $response->validate == 1 ) {
			update_option( $this->oppai_key, $response->oppai );
			update_option( $this->license_key, $response->key );

			$response->is_activated = true;
		}

		if ( ! empty( $response->is_activated ) ) {
			return [
				'success' => 'ok',
				'data'    => $response,
				'message' => esc_html__( 'Successfully activated', 'wp-ultimate-review' ),
			];
		}

		if ( ! empty( $response->error ) ) {
			return [
				'success' => 'danger',
				'message' => $response->message,
			];
		}


		return [
			'success' => 'danger',
			'message' => esc_html__( 'Unsupported pro version', 'wp-ultimate-review' ),
		];
	}


}