<?php
class PM_Helper_FRIENDS {

	public function get_db_table_name( $identifier ) {
		global $wpdb;
		$plugin_prefix = $wpdb->prefix . 'pm_';
		$table_name    = $plugin_prefix . 'friends';
		return $table_name;
	}

	public function get_db_table_unique_field_name( $identifier ) {
         $unique_field_name = 'id';
		return $unique_field_name;
	}

	public function get_db_table_field_type( $identifier, $field ) {
        switch ( $field ) {
			case 'id':
				$format = '%d';
				break;
			case 'user1':
				$format = '%d';
				break;
			case 'user2':
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


	public function get_sanitized_fields( $identifier, $field, $value ) {
		switch ( $field ) {
			case 'id':
				$sanitized_value = sanitize_text_field( $value );
				break;
			case 'user1':
				$sanitized_value = sanitize_text_field( $value );
				break;
			case 'user2':
				$sanitized_value = sanitize_text_field( $value );
				break;
			case 'status':
				$sanitized_value = sanitize_text_field( $value );
				break;
			default:
				$sanitized_value = sanitize_text_field( $value );
		}

		return $sanitized_value;
	}

}


