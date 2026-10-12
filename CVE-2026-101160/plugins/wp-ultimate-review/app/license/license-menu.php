<?php


namespace WurReview\App\License;


use WurReview\App\Application;

class License_Menu {

	public function add_menu(){

		add_action('admin_menu', [$this, 'license_menu']);
		add_action('admin_init', [$this, 'license_action']);
		add_action('admin_enqueue_scripts', [$this, 'add_enqueue_for_license_page']);
	}

	public function license_menu(){

		if(Application::package_type() === 'pro'){

			add_submenu_page(
				'edit.php?post_type=xs_review',
				esc_html__('License', 'wp-ultimate-review'),
				esc_html__('License', 'wp-ultimate-review'),
				'manage_options',
				'xs_license',
				[$this, 'license_page_content_handler'],
				200
			);

		}else{

			add_submenu_page(
				'edit.php?post_type=xs_review',
				esc_html__('Upgrade to Premium', 'wp-ultimate-review'),
				esc_html__('Upgrade to Premium', 'wp-ultimate-review'),
				'manage_options',
				Application::landing_page(),
				'',
				200
			);
		}
	}

	public function license_page_content_handler(){

	  include WUR_REVIEW_PLUGIN_PATH.'views/admin/license-page-content.php' ;
	}


	public function add_enqueue_for_license_page() {

		wp_enqueue_style('license_page_css', WUR_REVIEW_PLUGIN_URL . 'assets/admin/css/license-page-style.css', [], WUR_REVIEW_VERSION);
	}


	public function license_action(){

		if(isset( $_POST['xs-review-pro-settings-page-action'])) {

			$key = !isset($_POST['xs-review-pro-settings-page-key']) ? '' : sanitize_text_field(wp_unslash($_POST['xs-review-pro-settings-page-key']));

			if( !check_admin_referer('xs-review-pro-settings-page', 'xs-review-pro-settings-page')){
				return;
			}
			

			switch($_POST['xs-review-pro-settings-page-action']){
				case 'activate':
					 License::instance()->activate($key);
					break;
				case 'deactivate':
					 License::instance()->deactivate();
					break;
			}
		}
	}

}