<?php


namespace WurReview\App\Updater;


trait Updater_Cache {

	public function get_cache( $cache_key ) {
		$cache = get_option( $cache_key );
		if ( ! $cache ) {
			return false;
		}

		if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
			return false; // Cache is expired
		}

		/**
		 *  We need to turn the icons into an array
		 */
		$cache['value'] = (object)json_decode( $cache['value'], true );

		if ( ! empty( $cache['value']->icons ) ) {
			$cache['value']->icons = (array) $cache['value']->icons;
		}

		return $cache['value'];
	}

	public function set_cache( $value, $cache_key) {

		$data = [
			'timeout' => strtotime( '+3 hours', time() ),
			'value'   => json_encode( $value ),
		];

		update_option( $cache_key, $data, 'no' );
	}

}