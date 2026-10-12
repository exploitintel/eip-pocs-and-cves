<?php

namespace WurReview\App;

defined('ABSPATH') || exit;

abstract class Rest_Api_Base {

	public $prefix = '';
	public $param = '';
	public $request = null;


	abstract public function config();
	abstract public function check_permissions($request);


	public function __construct() {
		$this->config();
		$this->init();
	}


	public function init() {
		add_action('rest_api_init', function() {
			register_rest_route(untrailingslashit(WUR_REST_NAMESPACE.'/v1/' . $this->prefix), '/(?P<action>\w+)/' . ltrim($this->param, '/'), array(
				'methods'  => \WP_REST_Server::ALLMETHODS,
				'callback' => [$this, 'action'],
				'permission_callback' => [$this, 'check_permissions'],
			));
		});
	}


	public function action($request) {
		$this->request = $request;
		// Sanitize inputs
		$method = strtolower($this->request->get_method());
		$action = isset($this->request['action']) ? sanitize_key($this->request['action']) : '';		
		// Build handler method name (e.g., 'post_create', 'get_list')
		$action_class = $method . '_' . $action;

		if(method_exists($this, $action_class)) {
			return $this->{$action_class}();
		}
		
		// Handler method not found - return error
		return new \WP_Error(
			'rest_invalid_action',
			sprintf(
				esc_html__('Action "%s" not found.', 'wp-ultimate-review'),
				esc_html($action_class)
			),
			array('status' => 404)
		);
	}

}