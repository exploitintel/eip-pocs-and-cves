<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Profile_Magic
 * @subpackage Profile_Magic/includes
 * @author     ProfileGrid <support@profilegrid.co>
 */
class Profile_Magic_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		global $wpdb;
		update_site_option('pg_redirect_to_group_page', 1);
		if ( is_multisite() ) {
			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->create_table();
				restore_current_blog();
			}
		} else {
			$this->create_table(); }
	}

	public function create_table() {
		global $wpdb;
		if ( version_compare( get_bloginfo( 'version' ), '6.1' ) < 0 ) {
			require_once ABSPATH . 'wp-includes/wp-db.php';
		} else {
			require_once ABSPATH . 'wp-includes/class-wpdb.php';
		}
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// Ensures proper charset support. Also limits support for WP v3.5+.
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $this->get_db_table_name( 'GROUPS' );
		$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_name` varchar(255) NOT NULL,
        `group_desc` longtext DEFAULT NULL,
        `group_icon` int(11) DEFAULT NULL,
        `is_group_limit` int(11) NOT NULL DEFAULT '0',
        `group_limit` int(11) NOT NULL DEFAULT '0',
        `group_limit_message` longtext DEFAULT NULL,
        `associate_role` varchar(255) NOT NULL,
        `is_group_leader` int(11) NOT NULL DEFAULT '0',
        `leader_username` varchar(255) NOT NULL,
        `group_leaders` longtext DEFAULT NULL,
        `leader_rights` longtext,
        `group_slug` varchar(255),
        `show_success_message` int(11) NOT NULL DEFAULT '0',
        `success_message` longtext DEFAULT NULL,
        `group_options` longtext,
        PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'FIELDS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `field_id` int(11) NOT NULL AUTO_INCREMENT,
        `field_name` varchar(255) NOT NULL,
        `field_desc` longtext DEFAULT NULL,
        `field_type` varchar(255) NOT NULL,
        `field_options` longtext DEFAULT NULL,
        `field_icon` int(11) DEFAULT NULL,
        `associate_group` int(11) NOT NULL DEFAULT '0',
        `associate_section` int(11) NOT NULL DEFAULT '0',
        `show_in_signup_form` int(11) NOT NULL DEFAULT '0',
        `is_required` int(11) NOT NULL DEFAULT '0',
        `is_editable` int(11) NOT NULL DEFAULT '0',
        `display_on_profile` int(11) NOT NULL DEFAULT '0',
        `display_on_group` int(11) NOT NULL DEFAULT '0',
        `visibility` int(11) NOT NULL DEFAULT '0',
        `ordering` int(11) NOT NULL,
        `field_key` varchar(255) NOT NULL,
        PRIMARY KEY (`field_id`)
		)$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'PAYPAL_LOG' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `txn_id` varchar(600) NOT NULL,
        `log` longtext NOT NULL,
		`posted_date` datetime NOT NULL,
        `gid` int(11) NOT NULL,
        `status` varchar(255) NOT NULL,
        `invoice` varchar(255) NOT NULL,
        `amount` int(11) NOT NULL,
        `currency` varchar(255) NOT NULL,
        `pay_processor` varchar(255) NOT NULL,
        `pay_type` varchar(255) NOT NULL,
        `uid` int(11) NOT NULL,
		PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'SECTION' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
		`gid` int(11) NOT NULL,
        `section_name` varchar(600) NOT NULL,
        `ordering` int(11) NOT NULL DEFAULT '0',
        `section_options` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'EMAIL_TMPL' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tmpl_name` varchar(600) NOT NULL,
        `email_subject` varchar(255) NOT NULL,
        `email_body` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'FRIENDS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user1` int(11) NOT NULL,
        `user2` int(11) NOT NULL,
        `created_date` datetime NOT NULL,
        `action_date` datetime NOT NULL,
        `status` int(11) NOT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'MSG_THREADS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `t_id` int(11) NOT NULL AUTO_INCREMENT,
        `s_id` int(11) NOT NULL,
        `r_id` int(11) NOT NULL,
        `timestamp` datetime NOT NULL ,
        `title` varchar(255),
        `status` int(11),
        `thread_desc` longtext DEFAULT NULL,
        PRIMARY KEY (`t_id`))$charset_collate;";
		dbDelta( $sql );

		$table_name        = $this->get_db_table_name( 'MSG_CONVERSATION' );
		$foreign_key_table = $this->get_db_table_name( 'MSG_THREADS' );
		$sql               = "CREATE TABLE IF NOT EXISTS $table_name (
        `m_id` int(11) NOT NULL AUTO_INCREMENT,
        `s_id` int(11) NOT NULL,
        `t_id` int(11) NOT NULL,
        `content` longtext DEFAULT NULL,
        `timestamp` datetime NOT NULL ,
        `subject` varchar(255),
        `status` int(11),
        `msg_desc` longtext DEFAULT NULL,
        FOREIGN KEY (`t_id`) REFERENCES $foreign_key_table(`t_id`),
        PRIMARY KEY (`m_id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'NOTIFICATION' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `type` varchar(255) NOT NULL,
        `sid` int(11) NOT NULL,
        `rid` int(11) NOT NULL,
        `timestamp` datetime NOT NULL,
        `description` longtext DEFAULT NULL,
        `status` int(11) NOT NULL,
        `meta` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'REQUESTS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `gid` int(11) NOT NULL,
        `uid` int(11) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '0',
        `options` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );
                
                $table_name = $this->get_db_table_name( 'GROUP_REQUESTS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_name` varchar(255) DEFAULT NULL,
        `group_desc` longtext DEFAULT NULL,
        `group_icon` int(11) DEFAULT NULL,
        `gid` int(11) NOT NULL,
        `uid` int(11) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '0',
        `options` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$this->create_pages();
				$this->add_default_options();
				$this->create_default_email_templates();
	}

	public function upgrade_db() {
		global $wpdb;
		if ( version_compare( get_bloginfo( 'version' ), '6.1' ) < 0 ) {
			require_once ABSPATH . 'wp-includes/wp-db.php';
		} else {
			require_once ABSPATH . 'wp-includes/class-wpdb.php';
		}
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$dbhandler = new PM_DBhandler();

		// Ensures proper charset support. Also limits support for WP v3.5+.
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $this->get_db_table_name( 'FRIENDS' );
		$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user1` int(11) NOT NULL,
        `user2` int(11) NOT NULL,
        `created_date` datetime NOT NULL ,
        `action_date` datetime NOT NULL,
        `status` int(11) NOT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'MSG_THREADS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `t_id` int(11) NOT NULL AUTO_INCREMENT,
        `s_id` int(11) NOT NULL,
        `r_id` int(11) NOT NULL,
        `timestamp` datetime NOT NULL ,
        `title` varchar(255),
        `status` int(11),
        `thread_desc` longtext DEFAULT NULL,
        PRIMARY KEY (`t_id`))$charset_collate;";
		dbDelta( $sql );

		$table_name        = $this->get_db_table_name( 'MSG_CONVERSATION' );
		$foreign_key_table = $this->get_db_table_name( 'MSG_THREADS' );
		$sql               = "CREATE TABLE IF NOT EXISTS $table_name (
        `m_id` int(11) NOT NULL AUTO_INCREMENT,
        `s_id` int(11) NOT NULL,
        `t_id` int(11) NOT NULL,
        `content` longtext DEFAULT NULL,
        `timestamp` datetime NOT NULL ,
        `subject` varchar(255),
        `status` int(11),
        `msg_desc` longtext DEFAULT NULL,
        FOREIGN KEY (`t_id`) REFERENCES $foreign_key_table(`t_id`),
        PRIMARY KEY (`m_id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'NOTIFICATION' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `type` varchar(255) NOT NULL,
        `sid` int(11) NOT NULL,
        `rid` int(11) NOT NULL,
        `timestamp` datetime NOT NULL,
        `description` longtext DEFAULT NULL,
        `status` int(11) NOT NULL,
        `meta` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$table_name = $this->get_db_table_name( 'REQUESTS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `gid` int(11) NOT NULL,
        `uid` int(11) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '0',
        `options` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );
                
                $table_name = $this->get_db_table_name( 'GROUP_REQUESTS' );
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_name` varchar(255) DEFAULT NULL,
        `group_desc` longtext DEFAULT NULL,
        `group_icon` int(11) DEFAULT NULL,
        `gid` int(11) NOT NULL,
        `uid` int(11) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '0',
        `options` longtext DEFAULT NULL,
        PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );

		$groups     = $dbhandler->get_all_result( 'GROUPS' );
		$table_name = $this->get_db_table_name( 'GROUPS' );
		if ( ! empty( $groups ) ) {
			$checkfield = $dbhandler->get_row( 'GROUPS', $groups[0]->id, false, 'ARRAY_A' );
			if ( ! array_key_exists( 'group_leaders', $checkfield ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name built internally and sanitized.
				$query = "ALTER TABLE " . esc_sql( $table_name ) . " 
                ADD COLUMN `group_leaders` longtext DEFAULT NULL AFTER `leader_username`";
				$wpdb->query( $query );
			}

			$existing_pg_db_version = floatval( get_option( 'progrid_db_version', '1.0' ) );
			if ( $existing_pg_db_version < '4.2' ) {
				$this->migration_group_data();
				$this->migration_user_data();
			}

			if ( $existing_pg_db_version < '4.3' ) {
				$dbhandler  = new PM_DBhandler();
				$pmrequest  = new PM_request();
				$is_created = $dbhandler->get_global_option_value( 'pg_email_templates_created_upgrade', '0' );
				if ( $is_created == '0' ) {
					$resutls = $dbhandler->get_all_result( 'EMAIL_TMPL', '*', array( 'tmpl_name' => 'Group Manager Removal' ) );
					if ( empty( $resutls ) || $resutls == null ) {
						$pmrequest->pg_auto_create_default_template_during_update();
					}
				}
			}

			if ( $existing_pg_db_version < '4.4' ) {
				$dbhandler  = new PM_DBhandler();
				$pmrequest  = new PM_request();
				$is_created = $dbhandler->get_global_option_value( 'pg_email_templates_created_new_upgrade', '0' );
				if ( $is_created == '0' ) {
					$resutls = $dbhandler->get_all_result( 'EMAIL_TMPL', '*', array( 'tmpl_name' => 'Membership Terminated' ) );
					if ( empty( $resutls ) || $resutls == null ) {
						$pmrequest->pg_auto_create_new_default_template_during_update();
					}
				}
			}

			if ( $existing_pg_db_version < '4.5' ) {
				$this->pm_maybe_migrate_legacy_pm_group_storage();
			}
		}

		$paypallog  = $dbhandler->get_row( 'PAYPAL_LOG', '1' );
		$table_name = $this->get_db_table_name( 'PAYPAL_LOG' );
		// Add column if not present.
		if ( ! empty( $paypallog ) && ! isset( $paypallog->gid ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name built internally and sanitized.
			$query = "ALTER TABLE " . esc_sql( $table_name ) . " 
            ADD COLUMN `gid` int(11) NOT NULL AFTER `posted_date`,
            ADD COLUMN `status` VARCHAR(255) NOT NULL AFTER `gid`,
            ADD COLUMN `invoice` VARCHAR(255) NOT NULL AFTER `status`,
            ADD COLUMN `amount` int(11) NOT NULL AFTER `invoice`,
            ADD COLUMN `currency` VARCHAR(255) NOT NULL AFTER `amount`,
            ADD COLUMN `pay_processor` VARCHAR(255) NOT NULL AFTER `currency`,
            ADD COLUMN `pay_type` VARCHAR(255) NOT NULL AFTER `pay_processor`,
            ADD COLUMN `uid` VARCHAR(255) NOT NULL AFTER `pay_type`";
			$wpdb->query( $query );

			$this->upgrade_paypal_tbl();
		}
			update_option( 'progrid_db_version', PROGRID_DB_VERSION );
	}

	public function pm_maybe_migrate_legacy_pm_group_storage() {
		$migration_flag = 'pg_pm_group_gid_storage_migrated_v1';
		if ( get_option( $migration_flag, '' ) === '1' ) {
			return;
		}

		$users         = get_users(
			array(
				'fields'      => 'ID',
				'number'      => -1,
				'meta_key'    => 'pm_group',
				'count_total' => false,
			)
		);
		$updated_count = 0;
		if ( ! empty( $users ) ) {
			foreach ( $users as $user_id ) {
				$raw_groups = get_user_meta( $user_id, 'pm_group', true );
				$is_legacy  = false;
				$converted  = $this->pm_convert_legacy_gid_to_string_groups( $raw_groups, $is_legacy );

				if ( $is_legacy && ! empty( $converted ) ) {
					update_user_meta( $user_id, 'pm_group', $converted );
					$updated_count++;
				}
			}
		}

		update_option( $migration_flag, '1', false );
		update_option( 'pg_pm_group_gid_storage_migrated_v1_count', absint( $updated_count ), false );
	}

	private function pm_convert_legacy_gid_to_string_groups( $raw_groups, &$is_legacy = false ) {
		if ( $raw_groups === '' || $raw_groups === null || $raw_groups === false ) {
			return array();
		}

		if ( is_string( $raw_groups ) ) {
			$unserialized = maybe_unserialize( $raw_groups );
			if ( $unserialized !== $raw_groups ) {
				return $this->pm_convert_legacy_gid_to_string_groups( $unserialized, $is_legacy );
			}
		}

		if ( is_numeric( $raw_groups ) ) {
			$is_legacy = true;
			$group_id  = absint( $raw_groups );
			return $group_id > 0 ? array( (string) $group_id ) : array();
		}

		if ( ! is_array( $raw_groups ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $raw_groups as $group_id ) {
			if ( is_int( $group_id ) ) {
				$is_legacy = true;
				$group_id  = (string) absint( $group_id );
			} elseif ( is_string( $group_id ) ) {
				$group_id = trim( $group_id );
			} elseif ( is_numeric( $group_id ) ) {
				$is_legacy = true;
				$group_id  = (string) absint( $group_id );
			} else {
				continue;
			}

			if ( $group_id !== '' && $group_id !== '0' ) {
				$normalized[] = $group_id;
			}
		}

		return array_values( array_unique( $normalized ) );
	}

	public function upgrade_paypal_tbl() {
		$dbhandler = new PM_DBhandler();

		$results = $dbhandler->get_all_result( 'PAYPAL_LOG' );
		if ( ! empty( $results ) ) :
			foreach ( $results as $result ) {
				$log                   = maybe_unserialize( $result->log );
				$data                  = array();
				$gid                   = get_user_meta( $log['custom'], 'pm_group', true );
				$data['gid']           = $gid;
				$data['status']        = $log['payment_status'];
				$data['invoice']       = $log['invoice'];
				$data['amount']        = $log['mc_gross'];
				$data['currency']      = $log['mc_currency'];
				$data['pay_processor'] = 'paypal';
				$data['pay_type']      = 'one_time';
				$data['uid']           = $log['custom'];
				$dbhandler->update_row( 'PAYPAL_LOG', 'id', $result->id, $data );
				unset( $data );
			}
		endif;
	}

	public function get_db_table_name( $identifier ) {
		global $wpdb;
		$plugin_prefix    = $wpdb->prefix . 'promag_';
		$rm_plugin_prefix = $wpdb->prefix . 'rm_';

		switch ( $identifier ) {
			case 'FORMS':
				$table_name = $rm_plugin_prefix . 'forms';
				break;
			case 'GROUPS':
				$table_name = $plugin_prefix . 'groups';
				break;
			case 'WP_OPTION':
				$table_name = $wpdb->prefix . 'options';
				break;
			case 'FIELDS':
				$table_name = $plugin_prefix . 'fields';
				break;
			case 'PAYPAL_LOG':
				$table_name = $plugin_prefix . 'paypal_log';
				break;
			case 'SECTION':
				$table_name = $plugin_prefix . 'sections';
				break;
			case 'EMAIL_TMPL':
				$table_name = $plugin_prefix . 'email_templates';
				break;
			case 'FRIENDS':
				$table_name = $plugin_prefix . 'friends';
				break;
			case 'MSG_THREADS':
				$table_name = $plugin_prefix . 'msg_threads';
				break;
			case 'MSG_CONVERSATION':
				$table_name = $plugin_prefix . 'msg_conversation';
				break;
			case 'NOTIFICATION':
				$table_name = $plugin_prefix . 'notification';
				break;
			case 'REQUESTS':
				$table_name = $plugin_prefix . 'group_requests';
				break;
			case 'FORMS':
				$table_name = $rm_plugin_prefix . 'forms';
				break;
			case 'FORM_FIELDS':
				$table_name = $rm_plugin_prefix . 'fields';
				break;
                        case 'GROUP_REQUESTS':
				$table_name = $plugin_prefix . 'group_update_request';
				break;

			default:
				$classname = "PM_Helper_$identifier";
				if ( class_exists( $classname ) ) {
					$externalclass = new $classname();
					$table_name    = $externalclass->get_db_table_name( $identifier );
				} else {
					return false; }
		}
		return $table_name;
	}

	public function create_pages() {
		$dbhandler                               = new PM_DBhandler();
				$pmrequest                       = new PM_request();
		$pages['profilegrid_register']           = array(
			'post_type'    => 'page',
			'post_title'   => __( 'Registration', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_register gid="1"]',
		);
		$pages['profilegrid_group']              = array(
			'post_type'    => 'page',
			'post_title'   => __( 'Default User Group', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_group gid="1"]',
		);
		$pages['profilegrid_groups']             = array(
			'post_type'    => 'page',
			'post_title'   => __( 'All Groups', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_groups]',
		);
		$pages['profilegrid_login']              = array(
			'post_type'    => 'page',
			'post_title'   => __( 'Login', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_login]',
		);
		$pages['profilegrid_profile']            = array(
			'post_type'    => 'page',
			'post_title'   => __( 'My Profile', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_profile]',
		);
		$pages['profilegrid_forgot_password']    = array(
			'post_type'    => 'page',
			'post_title'   => __( 'Forgot Password', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_forgot_password]',
		);
		$pages['profilegrid_submit_blog']        = array(
			'post_type'    => 'page',
			'post_title'   => __( 'Submit New Blog Post', 'profilegrid-user-profiles-groups-and-communities' ),
			'post_status'  => 'publish',
			'post_name'    => '',
			'post_content' => '[profilegrid_submit_blog]',
		);
				$pages['profilegrid_users']      = array(
					'post_type'    => 'page',
					'post_title'   => __( 'Search Users', 'profilegrid-user-profiles-groups-and-communities' ),
					'post_status'  => 'publish',
					'post_name'    => '',
					'post_content' => '[profilegrid_users]',
				);
				$pages['profilegrid_user_blogs'] = array(
					'post_type'    => 'page',
					'post_title'   => __( 'User Blogs', 'profilegrid-user-profiles-groups-and-communities' ),
					'post_status'  => 'publish',
					'post_name'    => '',
					'post_content' => '[profilegrid_user_blogs]',
				);
				// The Query
				foreach ( $pages as $key => $page ) {
					$string   = '[' . $key;
					$my_query = new WP_Query(
						array(
							'post_type'   => 'any',
							'post_status' => 'publish',
							's'           => $string,
							'fields'      => 'ids',
						)
					);
					if ( empty( $my_query->posts ) ) {
						$page_id[ $key ] = wp_insert_post( $page );  
                                        } 
				}
                                if(isset($page_id) && !empty($page_id))
                                {
                                    foreach ( $page_id as $key => $id ) {
					if ( $key == 'profilegrid_register' ) {
						$field = 'pm_registration_page';
					}
					if ( $key == 'profilegrid_group' ) {
						$field = 'pm_group_page';
					}
					if ( $key == 'profilegrid_groups' ) {
						$field = 'pm_groups_page';
					}
					if ( $key == 'profilegrid_login' ) {
						$field = 'pm_user_login_page';
					}
					if ( $key == 'profilegrid_profile' ) {
						$field = 'pm_user_profile_page';
					}
					if ( $key == 'profilegrid_forgot_password' ) {
						$field = 'pm_forget_password_page';
					}
					if ( $key == 'profilegrid_submit_blog' ) {
						$field = 'pm_submit_blog';
					}
					if ( $key == 'profilegrid_users' ) {
						$field = 'pm_search_page';
					}
					if ( $key == 'profilegrid_user_blogs' ) {
						$field = 'pm_user_blogs_page';
					}
					$dbhandler->update_global_option_value( $field, $id );
					if ( $key == 'profilegrid_profile' ) {
						$dbhandler->update_global_option_value( 'pm_redirect_after_login', $id );
					}
				}
                                }
				if ( $dbhandler->pm_count( 'GROUPS' ) == 0 ) 
                                {
					$data         = array(
						'group_name'           => __( 'Default User Group', 'profilegrid-user-profiles-groups-and-communities' ),
						'associate_role'       => 'subscriber',
						'group_desc'           => '',
						'show_success_message' => 1,
						'success_message'      => __( 'Thank you for signing up.', 'profilegrid-user-profiles-groups-and-communities' ),
					);
					$arg          = array( '%s', '%s', '%s', '%d', '%s' );
					$gid          = $dbhandler->insert_row( 'GROUPS', $data, $arg );
					$section_data = array(
						'gid'          => $gid,
						'section_name' => __( 'Personal Details', 'profilegrid-user-profiles-groups-and-communities' ),
						'ordering'     => $gid,
					);
					$section_arg  = array( '%d', '%s', '%d' );
					$sid          = $dbhandler->insert_row( 'SECTION', $section_data, $section_arg );
						$pmrequest->pg_auto_create_default_fields( $gid, $sid );

				
                                        $arg   = array(
                                                'meta_query' => array(
                                                        'relation' => 'OR',
                                                        array(
                                                                'key'     => 'pm_group',
                                                                'value'   => '',
                                                                'compare' => 'NOT EXISTS',
                                                        ),
                                                        array(
                                                                'key'     => 'rm_user_status',
                                                                'value'   => '',
                                                                'compare' => 'NOT EXISTS',
                                                        ),
                                                ),
                                        );
                                        $users = get_users( $arg );
                                        if ( ! empty( $users ) ) {
                                                foreach ( $users as $user ) {
                                                        add_user_meta( $user->ID, 'pm_group', array( "$gid" ), true );
                                                        add_user_meta( $user->ID, 'rm_user_status', 0, true );
                                                }
                                        }
                                }
	}

	public function get_db_table_unique_field_name( $identifier ) {

		switch ( $identifier ) {
			case 'GROUPS':
				$unique_field_name = 'id';
				break;
			case 'WP_OPTION':
				$unique_field_name = 'option_id';
				break;
			case 'FIELDS':
				$unique_field_name = 'field_id';
				break;
			case 'PAYPAL_LOG':
				$unique_field_name = 'id';
				break;
			case 'EMAIL_TMPL':
				$unique_field_name = 'id';
				break;
			case 'SECTION':
				$unique_field_name = 'id';
				break;
			case 'NOTIFICATION':
					$unique_field_name = 'id';
				break;
			case 'REQUESTS':
					$unique_field_name = 'id';
				break;
                        case 'GROUP_REQUESTS':
					$unique_field_name = 'id';
				break;
			case 'FORMS':
					$unique_field_name = 'form_id';
				break;
			case 'FORM_FIELDS':
					$unique_field_name = 'field_id';
				break;

			default:
				$classname = "PM_Helper_$identifier";
				if ( class_exists( $classname ) ) {
					$externalclass     = new $classname();
					$unique_field_name = $externalclass->get_db_table_unique_field_name( $identifier );
				} else {
					return false; }
		}
		return $unique_field_name;
	}

	public function get_db_table_field_type( $identifier, $field ) {
		$functionname = 'get_field_format_type_' . $identifier;
		if ( method_exists( 'Profile_Magic_Activator', $functionname ) ) {
			$format = $this->$functionname( $field );
		} else {
			$classname = "PM_Helper_$identifier";
			if ( class_exists( $classname ) ) {
				$externalclass = new $classname();
				$format        = $externalclass->get_db_table_field_type( $identifier, $field );
			} else {
				return false; }
		}
		return $format;
	}

	public function get_field_format_type_FORMS( $field ) {
		switch ( $field ) {
			case 'form_type':
				$format = '%d';
				break;
			case 'gid':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_MSG_THREADS( $field ) {

		switch ( $field ) {
			case 't_id':
				$format = '%d';
				break;
			case 's_id':
				$format = '%d';
				break;
			case 'r_id':
				$format = '%d';
				break;
			case 'status':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_MSG_CONVERSATION( $field ) {

		switch ( $field ) {
			case 't_id':
				$format = '%d';
				break;
			case 's_id':
				$format = '%d';
				break;
			case 'm_id':
				$format = '%d';
				break;
			case 'status':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_FORM_FIELDS( $field ) {
		switch ( $field ) {
			case 'field_label':
				$format = '%s';
				break;
			case 'field_type':
				$format = '%s';
				break;
			case 'field_value':
					$format = '%s';
				break;
			case 'field_options':
					$format = '%s';
				break;
			default:
				$format = '%d';
		}
		return $format;
	}

	public function get_field_format_type_SECTION( $field ) {
		switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			case 'gid':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_REQUESTS( $field ) {
		switch ( $field ) {
			case 'options':
				$format = '%s';
				break;
			default:
				$format = '%d';
		}
		return $format;
	}
        
        public function get_field_format_type_GROUP_REQUESTS( $field ) {
		switch ( $field ) {
			case 'group_name':
                        case 'group_desc':
                        case 'options':
				$format = '%s';
				break;
			default:
				$format = '%d';
		}
		return $format;
	}

	public function get_field_format_type_EMAIL_TMPL( $field ) {
		switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_PAYPAL_LOG( $field ) {
		switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			case 'gid':
				$format = '%d';
				break;
			case 'amount':
				$format = '%d';
				break;
			case 'uid':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_NOTIFICATION( $field ) {
		switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_GROUPS( $field ) {
		switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			case 'is_group_limit':
				$format = '%d';
				break;
			case 'is_group_leader':
				$format = '%d';
				break;
			case 'group_limit':
				$format = '%d';
				break;
			case 'group_icon':
				$format = '%d';
				break;
			case 'show_success_message':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function get_field_format_type_FIELDS( $field ) {
		switch ( $field ) {
			case 'field_id':
				$format = '%d';
				break;
			case 'field_icon':
				$format = '%d';
				break;
			case 'associate_group':
				$format = '%d';
				break;
			case 'show_in_signup_form':
				$format = '%d';
				break;
			case 'is_required':
				$format = '%d';
				break;
			case 'is_editable':
				$format = '%d';
				break;
			case 'display_on_profile':
				$format = '%d';
				break;
			case 'display_on_group':
				$format = '%d';
				break;
			case 'visibility':
				$format = '%d';
				break;
			case 'ordering':
				$format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}

	public function add_default_options() {
		 add_option( 'pm_enable_blog', '1' );
		add_option( 'pm_blog_feature_image', '1' );
		add_option( 'pm_blog_tags', '1' );
		add_option( 'pm_blog_editor', '1' );
		add_option( 'pm_blog_privacy_level', '1' );
		add_option( 'pm_blog_notification_user', '1' );
		add_option( 'pm_blog_notification_admin', '1' );
		add_option( 'pm_friends_panel', '1' );
		add_option( 'pm_show_privacy_settings', '1' );
		add_option( 'pm_allow_user_to_hide_their_profile', '1' );
		add_option( 'pm_show_delete_profile', '1' );
		add_option( 'pm_admin_notification', '1' );
		add_option( 'pm_admin_account_review_notification', '1' );
		add_option( 'pm_admin_account_deletion_notification', '1' );
		add_option( 'pm_auto_approval', '1' );
		add_option( 'pm_send_user_activation_link', '1' );
		add_option( 'pm_blog_post_from', 'both' );
                add_option( 'pm_encrypt_secret_key',wp_generate_password( 16, false ) );
		add_option( 'pm_encrypt_secret_iv', wp_generate_password( 16, false ) );
                
                
	}

	public function migration_group_data() {
		$dbhandler = new PM_DBhandler();
		$groups    = $dbhandler->get_all_result( 'GROUPS' );
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $row ) {
				$leader       = array();
				$data         = array();
				$group_leader = username_exists( $row->leader_username );
				if ( $group_leader ) {
					$leader['primary'] = $group_leader;
				}
				$data['group_leaders'] = maybe_serialize( $leader );
				$arg                   = array( '%s' );
				$dbhandler->update_row( 'GROUPS', 'id', $row->id, $data, $arg, '%d' );
				unset( $data );
				unset( $leader );
			}
		}
	}

	public function migration_user_data() {
		 $dbhandler = new PM_DBhandler();
		$user_query = $dbhandler->pm_get_all_users_ajax();
		$users      = $user_query->get_results();
		foreach ( $users as $user ) {
			$pm_group = get_user_meta( $user->ID, 'pm_group', true );
			if ( ! empty( $pm_group ) && ! is_array( $pm_group ) ) {
				$new_group = array( $pm_group );
				update_user_meta( $user->ID, 'pm_group', $new_group );
			}
			unset( $new_group );
			unset( $pm_group );
		}
	}

	public function create_default_email_templates() {
		$dbhandler  = new PM_DBhandler();
		$pmrequest  = new PM_request();
		$gid        = $dbhandler->get_all_result( 'GROUPS', 'id', 1, 'var', 0, 1, 'id', 'DESC' );
		$is_created = $dbhandler->get_global_option_value( 'pg_email_templates_created', '0' );
		if ( $is_created == '0' ) {
				$resutls = $dbhandler->get_all_result( 'EMAIL_TMPL', '*', array( 'tmpl_name' => __( 'User Account Activated', 'profilegrid-user-profiles-groups-and-communities' ) ) );
			if ( empty( $resutls ) || $resutls == null ) {
				$pmrequest->pg_auto_create_default_email_template( $gid );
			}
		}

	}
}
