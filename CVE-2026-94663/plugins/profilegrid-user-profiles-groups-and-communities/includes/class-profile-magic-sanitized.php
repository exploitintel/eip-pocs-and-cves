<?php
class PM_sanitizer {

	public static function safe_unserialize_array( $value ) {
		if ( ! is_string( $value ) || strlen( $value ) > 1048576 || ! is_serialized( $value ) ) {
			return array();
		}
		$data = @unserialize( $value, array( 'allowed_classes' => false ) );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$pending = array( $data );
		$visited = 0;
		while ( $pending ) {
			foreach ( array_pop( $pending ) as $item ) {
				if ( ++$visited > 10000 || is_object( $item ) ) {
					return array();
				}
				if ( is_array( $item ) ) {
					$pending[] = $item;
				}
			}
		}
		return $data;
	}


	public function get_sanitized_fields( $identifier, $field, $value ) {
		if ( is_null( $value ) ) {
			$value = '';
		}
		$sanitize_method = 'get_sanitized_' . strtolower( $identifier ) . '_field';

		if ( method_exists( $this, $sanitize_method ) ) {
			$sanitized_value = $this->$sanitize_method( $field, $value );
		} else {
			$classname = "PM_Helper_$identifier";
		}

		if ( isset( $classname ) && class_exists( $classname ) ) {
			$externalclass   = new $classname();
			$sanitized_value = $externalclass->get_sanitized_fields( $identifier, $field, $value );
		}

		return $sanitized_value;
	}

	public function get_sanitized_section_field( $field, $value ) {
		switch ( $field ) {
			case 'id':
				$value = sanitize_text_field( $value );
				break;
			case 'gid':
				$value = sanitize_text_field( $value );
				break;
			case 'section_name':
				$value = sanitize_text_field( $value );
				break;
			case 'section_options':
				$value = sanitize_text_field( $value );
				break;
			default:
				$value = sanitize_text_field( $value );

		}
			return $value;
	}

	public function get_sanitized_notification_field( $field, $value ) {
		switch ( $field ) {
			case 'id':
				$value = sanitize_text_field( $value );
				break;
			case 'gid':
				$value = sanitize_text_field( $value );
				break;
			case 'section_name':
				$value = sanitize_text_field( $value );
				break;
			case 'section_options':
				$value = sanitize_text_field( $value );
				break;
			default:
				$value = sanitize_text_field( $value );

		}
		return $value;
	}

	public function get_sanitized_email_tmpl_field( $field, $value ) {
		switch ( $field ) {
			case 'id':
				$value = sanitize_text_field( $value );
				break;
			case 'tmpl_name':
				$value = sanitize_text_field( $value );
				break;
			case 'email_subject':
				$value = sanitize_text_field( $value );
				break;
			case 'email_body':
				$value = wp_kses_post( $value );
				break;
			default:
				$value = sanitize_text_field( $value );

		}
		return $value;
	}

	public function get_sanitized_blog_field( $field, $value ) {
		switch ( $field ) {
			case 'blog_title':
				$value = sanitize_text_field( $value );
				break;
			case 'blog_description':
				$value = wp_kses_post( $value );
				break;
			case 'blog_tags':
				$value = sanitize_text_field( $value );
				break;
			default:
				$value = sanitize_text_field( $value );

		}
		return $value;
	}

	public function get_sanitized_groups_field( $field, $value ) {
		switch ( $field ) {
			case 'id':
				$value = sanitize_text_field( $value );
				break;
			case 'group_name':
				$value = sanitize_text_field( $value );
				break;
			case 'group_desc':
				$value = sanitize_text_field( $value );
				break;
			case 'group_icon':
				$value = sanitize_file_name( $value );
				break;
			case 'is_group_limit':
				$value = sanitize_text_field( $value );
				break;
			case 'group_limit':
				$value = sanitize_text_field( $value );
				break;
			case 'group_limit_message':
				$value = wp_kses_post( $value );
				break;
			case 'associate_role':
				$value = sanitize_text_field( $value );
				break;
			case 'admin_label':
				$value = preg_replace( '/[^A-Za-z0-9 ]/', '', (string) $value );
				break;
			case 'is_group_leader':
				$value = sanitize_text_field( $value );
				break;
			case 'leader_username':
				$value = sanitize_text_field( $value );
				break;
			case 'leader_rights':
				$value = sanitize_text_field( $value );
				break;
			case 'group_slug':
				$value = sanitize_text_field( $value );
				break;
			case 'group_options':
				$value = wp_kses_post( $value );
				break;
			case 'success_message':
				$value = wp_kses_post( $value );
				break;
			default:
				$value = wp_kses_post( $value );
                                break;
		}
			return $value;
	}

	public function get_sanitized_fields_field( $field, $value ) {
		switch ( $field ) {
			case 'field_id':
				$value = sanitize_text_field( $value );
				break;
			case 'field_name':
				$value = sanitize_text_field( $value );
				break;
			case 'field_desc':
				$value = sanitize_text_field( $value );
				break;
			case 'field_type':
				$value = sanitize_text_field( $value );
				break;
			case 'field_options':
				$value = $value;
				break;
			case 'field_icon':
				$value = sanitize_text_field( $value );
				break;
			case 'associate_group':
				$value = sanitize_text_field( $value );
				break;
			case 'show_in_signup_form':
				$value = sanitize_text_field( $value );
				break;
			case 'is_required':
				$value = sanitize_text_field( $value );
				break;
			case 'is_editable':
				$value = sanitize_text_field( $value );
				break;
			case 'display_on_profile':
				$value = sanitize_text_field( $value );
				break;
			case 'display_on_group':
				$value = sanitize_text_field( $value );
				break;
			case 'visibility':
				$value = sanitize_text_field( $value );
				break;
			case 'ordering':
				$value = sanitize_text_field( $value );
				break;
			default:
				$value = sanitize_text_field( $value );

		}
			return $value;
	}

	public function get_sanitized_frontend_field( $type, $value ) {
		switch ( $type ) {
			case 'user_name':
				$value = sanitize_user( $value, true );
				break;
			case 'user_login':
				$value = sanitize_user( $value, true );
				break;
			case 'user_email':
				$value = sanitize_email( $value );
				break;
			case 'email':
				$value = sanitize_email( $value );
				break;
			case 'first_name':
				$value = sanitize_text_field( $value );
				break;
			case 'last_name':
				$value = sanitize_text_field( $value );
				break;
			case 'radio':
				$value = sanitize_text_field( $value );
				break;
			case 'description':
				$value = wp_kses_post( wp_rel_nofollow( $value ) );
				break;
			case 'text':
				$value = sanitize_text_field( $value );
				break;
			case 'select':
				$value = sanitize_text_field( $value );
				break;
			case 'heading':
				$value = sanitize_text_field( $value );
				break;
			case 'paragraph':
				$value = sanitize_text_field( $value );
				break;
			case 'number':
				$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
				break;
			case 'country':
				$value = sanitize_text_field( $value );
				break;
			case 'timezone':
				$value = sanitize_text_field( $value );
				break;
			case 'term_checkbox':
				$value = sanitize_text_field( $value );
				break;
			case 'user_url':
				$value = esc_url( $value );
				break;
			case 'textarea':
				$value = wp_kses_post( $value );
				break;
			case 'DatePicker':
				//$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
				//$value = filter_var (preg_replace("([^0-9/] | [^0-9-])","",htmlentities($value)));
                                $value = sanitize_text_field($value);
                                break;
			case 'mobile_number':
				$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
				break;
			case 'phone_number':
				$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
				break;
			case 'gender':
				$value = sanitize_text_field( $value );
				break;
			case 'language':
				$value = sanitize_text_field( $value );
				break;
			case 'birth_date':
				//$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
                                //$value = filter_var (preg_replace("([^0-9/] | [^0-9-])","",htmlentities($value)));
                                $value = sanitize_text_field($value);
				break;
			case 'multi_dropdown':
				$value = sanitize_text_field( $value );
				break;
			case 'facebook':
				$value = esc_url( $value );
				break;
			case 'twitter':
				$value = esc_url( $value );
				break;
			case 'google':
				$value = esc_url( $value );
				break;
			case 'linked_in':
				$value = esc_url( $value );
				break;
			case 'youtube':
				$value = esc_url( $value );
				break;
			case 'instagram':
				$value = esc_url( $value );
				break;
			case 'address':
				$value = esc_url( $value );
				break;

			default:
				$value = $value;
		}
		return $value;
	}

	public function get_sanitized_settings_field( $field, $value ) {
		switch ( $field ) {
			case 'pm_logout_prompt_text':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_wpadmin_allow_ips':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_blocked_ips':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_blocked_emails':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_blacklist_word':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_account_deletion_alert_text':
				$value = sanitize_textarea_field( $value );
				break;
			case 'pm_recaptcha_site_key':
				$value = sanitize_text_field( $value );
				break;
			case 'pm_recaptcha_secret_key':
				$value = sanitize_text_field( $value );
				break;
			case 'pm_account_review_email_subject':
				$value = sanitize_text_field( $value );
				break;
			case 'pm_account_delete_email_subject':
				$value = sanitize_text_field( $value );
				break;
			case 'pm_from_email_name':
				$value = sanitize_text_field( $value );
				break;
			case 'pm_admin_email':
				$value = sanitize_email( $value );
				break;
			case 'pm_from_email_address':
				$value = sanitize_email( $value );
				break;
			case 'pm_paypal_email':
				$value = sanitize_email( $value );
				break;
			case 'pm_account_review_email_body':
				$value = wp_kses_post( $value );
				break;
			case 'pm_account_delete_email_body':
				$value = wp_kses_post( $value );
				break;
			case 'pm_unread_message_email_body':
				$value = wp_kses_post( $value );
				break;
			case 'pm_activation_email_body':
				$value = wp_kses_post( $value );
				break;
			case 'pg_user_profile_seo_title':
				$value = wp_kses_post( $value );
				break;
			case 'pg_user_profile_seo_desc':
				$value = wp_kses_post( $value );
				break;
                        case 'pg_group_seo_title':
				$value = wp_kses_post( $value );
				break;
			case 'pg_group_seo_desc':
				$value = wp_kses_post( $value );
				break;    
                            
			case 'pm_blog_notification_email_body':
				$value = wp_kses_post( $value );
				break;
			case 'pm_new_user_create_admin_email_body':
				$value = wp_kses_post( $value );
				break;
                        case 'pm_user_invitation_email_body':
				$value = wp_kses_post( $value );
				break;
                        case 'pm_user_invite_email_body':
				$value = wp_kses_post( $value );
				break;
                        case 'pm_user_invite_email_subject':
				$value = wp_kses_post( $value );
				break;
			case 'pm_user_profile_base':
					$value = sanitize_key( $value );
				break;
                        case 'pm_password_reset_email_content':
				$value = wp_kses_post( $value );
				break;
			default:
				$value = sanitize_text_field( $value );
		}
			return $value;
	}

	public function get_sanitized_msg_threads_field( $field, $value ) {
		switch ( $field ) {
			default:
				$value = sanitize_text_field( $value );

		}
		 return $value;
	}


	public function get_sanitized_msg_conversation_field( $field, $value ) {
		switch ( $field ) {
			case 'content':
				$value                 = wp_kses_post( (string) $value );
								$url   = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
								$value = preg_replace( $url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', (string) $value );

				break;
			default:
				$value = sanitize_text_field( $value );

		}
			 return $value;
	}

	public function remove_magic_quotes( $input ) {
		foreach ( $input as $key => $value ) {
			if ( is_array( $value ) ) {
				$input[ $key ] = $this->remove_magic_quotes( $value );
			} elseif ( is_string( $value ) ) {
				$input[ $key ] = stripslashes( $value );
			}
		}
		return $input;
	}

	public function sanitize_old( $input ) {
		// Initialize the new array that will hold the sanitize values
		$new_input = array();
		// Loop through the input and sanitize each of the values
		foreach ( $input as $key => $val ) {
                    
                         // Prevent PHP Object Injection
                        if (is_string($val) && preg_match('/[oc]:\d+:/i', $val)) {
                            $new_input[$key] = ''; // Reject serialized input
                            continue;
                        }
                        
                        // Prevent PHP Object Injection by rejecting serialized input
                        if (is_string($val) && preg_match('/(^|;|{|})[oc]:\d+:/i', $val)) {
                            $new_input[$key] = ''; // Reject serialized input
                            continue;
                        }
                        
                         // Prevent PHP Object Injection by rejecting serialized input
                        if (is_string($val) && (preg_match('/(^|;|{|})[oc]:\d+:/i', $val) || preg_match('/(^|;|{|})[ars]:\d+:/i', $val))) {
                            $new_input[$key] = ''; // Reject serialized input
                            continue;
                        }
                        
                       // Prevent PHP Object Injection by rejecting serialized input
                       if (is_string($val) && is_serialized($val)) {
                           $new_input[$key] = ''; // Reject serialized input
                           continue;
                       }

                        // Ensure all values are treated as plain data (avoid objects)
                        if (!is_array($val)) {
                            $val = json_decode(json_encode($val), true);
                        }
                        
                        if(empty($val)){
                            $new_input[ $key ] = $val;
                            continue;
                        }
			if ( is_array( $val ) ) {
				$new_input[ $key ] = $this->sanitize( $val );
			} else {
				switch ( $key ) {
					case 'login':
					case 'uname':
						$new_input[ $key ] = sanitize_user( $val );
						break;
					case 'user_email':
					case 'pm_email_address':
						$new_input[ $key ] = sanitize_email( $val );
						break;
					case 'key':
						$new_input[ $key ] = sanitize_text_field( $val );
						break;
					case 'nonce':
					case 'pg_user_profile_nonce':
					case 'pg-permalinks-nonce':
					case '_wpnonce':
						$new_input[ $key ] = sanitize_key( $val );
						break;
					case 'checkemail':
						 $new_input[ $key ] = sanitize_text_field( $val );
						break;
					case 'user_login':
					case 'userdata':
						if ( is_email( $val ) ) {
							$new_input[ $key ] = sanitize_email( $val );
						} else {
							$new_input[ $key ] = sanitize_user( $val );
						}
						break;
					case 'authors':
					case 'posttypes':
					case 'content':
						$new_input[ $key ] = wp_kses_post( $val );
						break;
					default:
						if ( is_email( $val ) ) {
							$new_input[ $key ] = sanitize_email( $val );
						} else {
							$new_input[ $key ] = wp_kses_post( $val );
						}

						break;
				}
			}
		}
		return $new_input;

	}
        
        public function sanitize( $input ) {
    // Initialize sanitized data array
    $new_input = array();

    foreach ( $input as $key => $val ) {

        // Step 1: Decode URL encoding only if the value is a string
        $decoded_val = is_string($val) ? urldecode($val) : $val;

        // Step 2: Strong Serialized Object Injection Protection (handles escaped characters too)
        if (is_string($decoded_val) && preg_match('/[oc]:\d+:\{?.*?\}?/i', stripslashes($decoded_val))) {
            $new_input[$key] = ''; // 🚫 Block PHP Object Injection before unserialization
            continue;
        }

        // Step 3: Double Check with `is_serialized()`
        if (is_string($decoded_val) && is_serialized($decoded_val)) {
            $new_input[$key] = ''; // 🚫 Block serialized input
            continue;
        }

        // Step 4: Ensure all values are plain data (avoid objects)
        if (!is_array($val)) {
            $val = json_decode(json_encode($val), true);
        }

        // Step 5: Handle empty values
        if (empty($val)) {
            $new_input[$key] = $val;
            continue;
        }

        // Step 6: Recursive sanitization for arrays
        if (is_array($val)) {
            $new_input[$key] = $this->sanitize($val);
        } else {
            switch ( $key ) {
                case 'login':
                case 'uname':
                    $new_input[$key] = sanitize_user($val);
                    break;
                case 'user_email':
                case 'user_pass':
                    $new_input[$key] = !empty($val) ? trim($val) : '';
                    break;
                case 'confirm_pass':
                    $new_input[$key] = !empty($val) ? trim($val) : '';
                    break;
                case 'pass1':
                    $new_input[$key] = !empty($val) ? trim($val) : '';
                    break;
                case 'pass2':
                    $new_input[$key] = !empty($val) ? trim($val) : '';
                    break;
                case 'pm_email_address':
                    $new_input[$key] = sanitize_email($val);
                    break;
                case 'key':
                case 'checkemail':
                    $new_input[$key] = sanitize_text_field($val);
                    break;
                case 'nonce':
                case 'pg_user_profile_nonce':
                case 'pg-permalinks-nonce':
                case '_wpnonce':
                    $new_input[$key] = sanitize_key($val);
                    break;
                case 'user_login':
                case 'userdata':
                    if (is_email($val)) {
                        $new_input[$key] = sanitize_email($val);
                    } else {
                        $new_input[$key] = sanitize_user($val);
                    }
                    break;
                case 'authors':
                case 'posttypes':
                case 'content':
                    $new_input[$key] = wp_kses_post($val);
                    break;
                case 'description':
                    $new_input[$key] = wp_kses_post($val);
                    break;
                default:
                    if (is_email($val)) {
                        $new_input[$key] = sanitize_email($val);
                    } else {
                        $new_input[$key] = sanitize_text_field($val); // Use text field sanitization
                    }
                    break;
            }
        }
    }
    return $new_input;
}


}
