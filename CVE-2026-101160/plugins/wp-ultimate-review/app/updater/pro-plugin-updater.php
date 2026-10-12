<?php


namespace WurReview\App\Updater;

use stdClass;
use WurReview\App\Updater\Updater_Cache;
use WurReview\App\Updater\Updater_Request;

class Pro_Plugin_Updater {

	use Updater_Request;
	use Updater_Cache;

	/**
	 * updater api endpoint
	 * @var string
	 */
	protected $endpoint;

	/**
	 * @var string
	 */
	protected $plugin_basename;

	/**
	 * need for api endpoint
	 * @var array
	 */
	protected $data;


	/**
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * @var mixed
	 */
	protected $plugin_version;

	/**
	 * @var boolean
	 */
	protected $wp_override;

	/**
	 * @var boolean
	 */
	protected $beta;

	/**
	 * @var string
	 */
	protected $cache_hash;

	/**
	 * @var string
	 */
	protected $cache_key;


	public function __construct( $endpoint, $plugin_basename, $data = [] ) {
		
		$this->endpoint        = $endpoint;
		$this->plugin_basename = $plugin_basename;
		$this->data            = $data;

		$this->plugin_slug    = basename( $plugin_basename, '.php' );
		$this->plugin_version = $data['version'];
		$this->wp_override    = $data['wp_override'] ?? false;
		$this->beta           = $data['beta'] ?? false;
		$this->cache_hash     = md5( serialize( $this->plugin_slug . $data['license'] . $this->beta ) );
		$this->cache_key      = 'wur_updater_cache_' . $this->cache_hash;


		$this->data['name'] = $this->plugin_basename;
		$this->data['slug'] = $this->plugin_slug;


		$edd_plugin_data[ $this->plugin_slug ] = $data;

		/**
		 * Fires after the $edd_plugin_data is setup.
		 *
		 * @param array $edd_plugin_data Array of EDD SL plugin data.
		 *
		 */
		do_action( 'post_edd_sl_plugin_updater_setup', $edd_plugin_data );
	}


	public function initiate() {
		/**
		 * check plugin latest version and Update
		 */
		add_filter( 'site_transient_update_plugins', [ $this, 'check_latest_version' ] );

		/**
		 * allows a plugin to override the WordPress.org
		 * Plugin Installation API entirely.
		 * @returns true
		 * @default false
		 */
		add_filter( 'plugins_api', [ $this, 'override_plugins_installation_api' ], 10, 3 );

		/*
		 * Fires after each specific row in the Plugins list table.
		 */
		remove_action( 'after_plugin_row_' . $this->plugin_basename, 'wp_plugin_update_row', 10 );
	 	add_action( 'after_plugin_row_' . $this->plugin_basename, [ $this, 'show_update_notification' ], 10, 3 );


		add_action( 'admin_init', [ $this, 'show_changelog' ] );
	}


	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * @param mixed $transient_data Update array build by WordPress.
	 */
	public function check_latest_version( $transient_data ) {
		global $pagenow;

		if ( ! is_object( $transient_data ) ) {
			$transient_data = new stdClass;
		}

		if ( 'plugins.php' == $pagenow && is_multisite() ) {
			return $transient_data;
		}

		if ( ! empty( $transient_data->response ) && ! empty( $transient_data->response[ $this->plugin_basename ] ) && false === $this->wp_override ) {
			return $transient_data;
		}

		$latest_version_data = $this->get_cache($this->cache_key);

		if ( false === $latest_version_data ) {
			$this->data['action'] = 'plugin_latest_version';
			$latest_version_data  = $this->api_request( $this->endpoint, $this->data );

			$this->set_cache( $latest_version_data, $this->cache_key );
		}

		if ( false !== $latest_version_data && is_object( $latest_version_data ) && isset( $latest_version_data->new_version ) ) {

			if ( version_compare( $this->plugin_version, $latest_version_data->new_version, '<' ) ) {

				$transient_data->response[ $this->plugin_basename ] = $latest_version_data;

				// Make sure the plugin property is set to the plugin's name/location. See issue 1463 on Software Licensing GitHub repo.
				$transient_data->response[ $this->plugin_basename ]->plugin = $this->plugin_basename;
			}

			$transient_data->last_checked                      = time();
			$transient_data->checked[ $this->plugin_basename ] = $this->plugin_version;
		}

		return $transient_data;
	}


	/**
	 *  override wordpress default plugin installation api from wordpress.org
	 *
	 * @param false|object|array $result The result object or array. Default false.
	 * @param string $action The type of information being requested from the Plugin Installation API.
	 * @param object $args Plugin API arguments.
	 */
	public function override_plugins_installation_api( $result, $action, $args ) {
		if ( $action != 'plugin_information' ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || ( $args->slug != $this->plugin_slug ) ) {
			return $result;
		}

		$cache_key = 'installation_api_' . $this->cache_hash;


		$plugins_installation_data = $this->get_cache( $cache_key );

		if ( ! $plugins_installation_data ) {
			$this->data['action']      = 'plugin_information';
			$plugins_installation_data = $this->api_request( $this->endpoint, $this->data );

			$this->set_cache( $plugins_installation_data, $cache_key );
		}

		if ( ! isset( $plugins_installation_data->plugin ) ) {
			$plugins_installation_data->plugin = $this->plugin_basename;
		}

		return $plugins_installation_data;
	}



	/**
	 * show update notification row -- needed for multisite subsites, because WP won't tell you otherwise!
	 *
	 ** @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array $plugin_data An array of plugin data.
	 * @param string $status Status filter currently applied to the plugin list. Possible
	 *                            values are: 'all', 'active', 'inactive', 'recently_activated',
	 *                            'upgrade', 'mustuse', 'dropins', 'search', 'paused',
	 *                            'auto-update-enabled', 'auto-update-disabled'.
	 */
	public function show_update_notification( $plugin_file, $plugin_data, $status ) {
		if ( is_network_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		if ( ! is_multisite() ) {
			return false;
		}

		if ( $this->plugin_basename != $plugin_file ) {
			return false;
		}

		// Remove our filter on the site transient
		remove_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_latest_version' ], 10 );

		$get_update_transient_data = get_site_transient( 'update_plugins' );

		$update_transient_data = is_object( $get_update_transient_data ) ? $get_update_transient_data : new stdClass();

		if ( empty( $update_transient_data->response ) || empty( $update_transient_data->response[ $this->plugin_basename ] ) ) {
			$latest_version_data = $this->get_cache($this->cache_key);

			if ( false === $latest_version_data ) {
				$this->data['action'] = 'plugin_latest_version';
				$latest_version_data  = $this->api_request( $this->endpoint, $this->data );
				if ( ! $latest_version_data ) {
					return false;
				}

				$this->set_cache( $latest_version_data, $this->cache_key );
			}

			if ( version_compare( $this->plugin_version, $latest_version_data->new_version, '<' ) ) {
				$update_transient_data->response[ $this->plugin_basename ] = $latest_version_data;
			}

			$update_transient_data->last_checked                      = time();
			$update_transient_data->checked[ $this->plugin_basename ] = $this->plugin_version;

			set_site_transient( 'update_plugins', $update_transient_data );
		} else {
			$update_transient_data = $update_transient_data->response[ $this->plugin_basename ];
		}

		// Restore our filter
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_latest_version' ] );

		if ( ! empty( $update_cache->response[ $this->plugin_basename ] ) && version_compare( $this->plugin_version,
		                                                                                      $update_transient_data->new_version,
		                                                                                      '<' ) ) {
			echo '<tr class="plugin-update-tr" id="' . esc_attr($this->plugin_basename) . '-update" data-slug="' . esc_attr($this->plugin_slug) . '" data-plugin="' . esc_attr($this->plugin_slug . '/' . $plugin_file) . '">';
			echo '<td colspan="3" class="plugin-update colspanchange">';
			echo '<div class="update-message notice inline notice-warning notice-alt">';

			$changelog_link = wp_nonce_url(self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->plugin_basename . '&slug=' . $this->plugin_slug . '&TB_iframe=true&width=772&height=911' ));

			if ( empty( $latest_version_data->download_link ) ) {
				printf(
					esc_html__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'wp-ultimate-review' ),
					esc_html( $latest_version_data->name ),
					'<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
					esc_html( $latest_version_data->new_version ),
					'</a>'
				);
			} else {
				printf(
					esc_html__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.',
					    'wp-ultimate-review' ),
					esc_html( $latest_version_data->name ),
					'<a target="_blank" class="thickbox" href="' . esc_url( $changelog_link ) . '">',
					esc_html( $latest_version_data->new_version ),
					'</a>',
					'<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->plugin_basename,
					                                     'upgrade-plugin_' . $this->plugin_basename ) ) . '">',
					'</a>'
				);
			}

			do_action( "in_plugin_update_message-{$plugin_file}", $plugin_data, $latest_version_data );

			echo '</div></td></tr>';
		}
	}


	public function show_changelog() {
		global $edd_plugin_data;

		if((!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']))))){
			return false;
		}

		if(empty($_REQUEST['edd_sl_action']) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action']) {
			return false;
		}

		if(empty($_REQUEST['plugin'])) {
			return false;
		}

		if(empty($_REQUEST['slug'])) {
			return false;
		}

		if(!current_user_can('update_plugins')) {
			wp_die(esc_html__('You do not have permission to install plugin updates', 'wp-ultimate-review'), esc_html__('Error', 'wp-ultimate-review'), ['response' => 403]);
		}

		$data         = sanitize_text_field( wp_unslash($edd_plugin_data[$_REQUEST['slug']]));
		$beta         = !empty($data['beta']);
		$cache_key    = md5('edd_plugin_' . sanitize_key($_REQUEST['plugin']) . '_' . $beta . '_version_info');
		$latest_version_data = $this->get_cache($cache_key);

		if(false === $latest_version_data) {

			$api_params = [
				'edd_action' => 'get_version',
				'item_name'  => $data['item_name'] ?? false,
				'item_id'    => $data['item_id'] ?? false,
				'slug'       => sanitize_text_field(wp_unslash($_REQUEST['slug'])),
				'author'     => $data['author'],
				'url'        => home_url(),
				'beta'       => !empty($data['beta']),
			];

			$verify_ssl = $this->verify_ssl();
			$request    = wp_remote_post($this->endpoint, ['timeout' => 15, 'sslverify' => $verify_ssl, 'body' => $api_params]);

			if(!is_wp_error($request)) {
				$latest_version_data = json_decode(wp_remote_retrieve_body($request));
			}

			if($request){
				$request->sections = $this->object_to_array( maybe_unserialize( $request->sections ?? [] ) );
				$request->banners  = $this->object_to_array( maybe_unserialize( $request->banners ?? [] ) );
				$request->icons    = $this->object_to_array( maybe_unserialize( $request->icons ?? [] ) );

				if ( $request->sections ) {
					foreach ( $request->sections as $key => $section ) {
						$request->$key = (array) $section;
					}
				}

				if( ! isset( $_data->plugin ) ) {
					$request->plugin = $this->plugin_basename;
				}

			}

			if(!empty($latest_version_data)) {
				foreach($latest_version_data->sections as $key => $section) {
					$latest_version_data->$key = (array)$section;
				}
			}

			$this->set_cache($latest_version_data, $cache_key);

		}

		if(!empty($latest_version_data) && isset($latest_version_data->sections['changelog'])) {
			echo '<div style="background:#fff;padding:10px;">' . $latest_version_data->sections['changelog'] . '</div>';  //phpcs:ignore
		}

		exit;
	}

}