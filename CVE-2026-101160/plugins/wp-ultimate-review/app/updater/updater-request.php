<?php


namespace WurReview\App\Updater;


trait Updater_Request {


	private $health_check_timeout = 5;


	private function api_request( $url, $data ) {
		global $edd_plugin_url_available;

		$verify_ssl = $this->verify_ssl();


		$store_hash = md5( $url );

		if ( ! is_array( $edd_plugin_url_available ) || ! isset( $edd_plugin_url_available[ $store_hash ] ) ) {
			$url_parts = parse_url( $url );

			$scheme = $url_parts['scheme'] ?? 'http';
			$host   = $url_parts['host'] ?? '';
			$port   = ! empty( $url_parts['port'] ) ? ':' . $url_parts['port'] : '';

			if ( empty( $host ) ) {
				$edd_plugin_url_available[ $store_hash ] = false;
			} else {
				$new_url                                 = $scheme . '://' . $host . $port;
				$response                                = wp_remote_get( $new_url,
				                                                          [ 'timeout'   => $this->health_check_timeout,
				                                                            'sslverify' => $verify_ssl
				                                                          ] );
				$edd_plugin_url_available[ $store_hash ] = ! is_wp_error( $response );
			}
		}

		if ( false === $edd_plugin_url_available[ $store_hash ] ) {
			return false;
		}

		if ( $url == trailingslashit( home_url() ) ) {
			return false; // Don't allow a plugin to ping itself
		}

		$post_data = [
			'edd_action' => 'get_version',
			'license'    => $data['license'] ?? '',
			'item_name'  => $data['item_name'] ?? false,
			'item_id'    => $data['item_id'] ?? false,
			'version'    => $data['version'] ?? false,
			'slug'       => $data['slug'],
			'author'     => $data['author'],
			'url'        => home_url(),
			'beta'       => $data['beta'] ?? false,
		];

		$request = wp_remote_post( $url, [ 'timeout' => 15, 'sslverify' => $verify_ssl, 'body' => $post_data ] );

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ) );
		}

		if ( $request ) {
			$request->sections = $this->object_to_array( maybe_unserialize( $request->sections ?? [] ) );
			$request->banners  = $this->object_to_array( maybe_unserialize( $request->banners ?? [] ) );
			$request->icons    = $this->object_to_array( maybe_unserialize( $request->icons ?? [] ) );

			if ( $request->sections ) {
				foreach ( $request->sections as $key => $section ) {
					$request->$key = (array) $section;
				}
			}

			if( ! isset( $_data->plugin ) ) {
				$request->plugin = $data['name'];
			}

		} else {
			$request = false;
		}


		return $request;
	}


	private function object_to_array( $data ) {
		$new_data = array();

		foreach ( $data as $key => $value ) {
			$new_data[ $key ] = $value;
		}

		return $new_data;
	}

	/**
	 * Returns if the SSL of the store should be verified.
	 *
	 * @return bool
	 * @since  1.6.13
	 */
	private function verify_ssl() {
		return (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this );
	}

}