<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-profile-magic-request
 *
 * @author ProfileGrid
 */
class PM_request {

	/** Remove only an attachment ID belonging to a configured file field. */
	public function pg_remove_profile_attachment( $user_id, $key, $attachment_id ) {
		$user_id = absint( $user_id );
		if ( ! is_user_logged_in() || ! $user_id || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) {
			return new WP_Error( 'pg_attachment_forbidden', 'Unauthorized' );
		}
		if ( ! is_string( $key ) || ! is_scalar( $attachment_id ) || ! ctype_digit( (string) $attachment_id ) || (int) $attachment_id < 1 ) {
			return new WP_Error( 'pg_attachment_invalid', 'Invalid attachment' );
		}
		$dbhandler = new PM_DBhandler();
		$fields = $dbhandler->get_all_result( 'FIELDS', array( 'field_key' ), array( 'field_key' => $key, 'field_type' => 'file' ), 'results', 0, 1 );
		if ( empty( $fields ) ) {
			return new WP_Error( 'pg_attachment_invalid_field', 'Invalid attachment field' );
		}
		$stored = get_user_meta( $user_id, $key, true );
		if ( ! is_string( $stored ) || '' === $stored ) {
			return new WP_Error( 'pg_attachment_not_found', 'Attachment not found' );
		}
		$attachments = explode( ',', $stored );
		$index = array_search( (string) $attachment_id, $attachments, true );
		if ( false === $index ) {
			return new WP_Error( 'pg_attachment_not_found', 'Attachment not found' );
		}
		unset( $attachments[$index] );
		return empty( $attachments ) ? delete_user_meta( $user_id, $key ) : update_user_meta( $user_id, $key, implode( ',', $attachments ) );
	}

	// put your code here
	public function sanitize_request( $post, $identifier, $exclude = array() ) {
		$pmsanitizer = new PM_sanitizer();

		$post = $pmsanitizer->remove_magic_quotes( $post );

		foreach ( $post as $key => $value ) {
			if ( ! in_array( $key, $exclude ) ) {
				if ( ! is_array( $value ) ) {
					$data[ $key ] = $pmsanitizer->get_sanitized_fields( $identifier, $key, $value );
				} else {
                                        if($key=='group_options')
                                        {
                                            $data[ $key ] = maybe_serialize( $this->sanitize_group_option_key( $value ) );
                                        }
                                        else
                                        {
                                            $data[ $key ] = maybe_serialize( $this->sanitize_request_array( $value, $identifier ) );
                                        }
					
				}
			}
		}

		if ( isset( $data ) ) {
			return $data; } else {
			return null; }
	}

	public function sanitize_request_array( $post, $identifier ) {
		$pmsanitizer = new PM_sanitizer();

		foreach ( $post as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->sanitize_request_array( $value, $identifier );
			} else {
				$data[ $key ] = $pmsanitizer->get_sanitized_fields( $identifier, $key, $value );
			}
		}

		if ( isset( $data ) ) {
			return $data; } else {
			return null; }
	}
        
        public function sanitize_group_option_key($post)
        {
            $data = array();
            foreach ( $post as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->sanitize_group_option_key( $value );
			} else {
				$data[ $key ] = wp_kses_post($value);
			}
		}

            return $data;
        }

	public function get_field_key( $type, $id ) {
		switch ( $type ) {
			case 'first_name':
				$key = $type;
				break;
			case 'last_name':
				$key = $type;
				break;
			case 'nickname':
				$key = $type;
				break;
			case 'description':
				$key = $type;
				break;
			case 'user_name':
				$key = 'user_login';
				break;
			case 'user_email':
				$key = $type;
				break;
			case 'user_pass':
				$key = $type;
				break;
			case 'confirm_pass':
				$key = $type;
				break;
			case 'user_url':
				$key = $type;
				break;
			case 'user_avatar':
				$key = 'pm_user_avatar';
				break;
			default:
				$key = 'pm_field_' . $id;
		}
		return sanitize_key( $key );
	}





	public function get_default_key_type( $type ) {
		switch ( $type ) {
			case 'first_name':
			case 'last_name':
			case 'description':
			case 'user_name':
			case 'user_email':
			case 'user_pass':
			case 'confirm_pass':
			case 'user_url':
			case 'user_avatar':
			case 'nickname':
				$value = true;
				break;
			default:
				$value = false;
		}
		return $value;
	}

	public function get_userrole_name( $userid ) {
		global $wp_roles;
		$user_info = get_userdata( $userid );
		$roles     = $user_info->roles;
		$role      = array_shift( $roles );
		return isset( $wp_roles->role_names[ $role ] ) ? translate_user_role( $wp_roles->role_names[ $role ] ) : false;
	}

	public function get_user_custom_fields_data( $id, $visibility = false ) {
			   $dbhandler = new PM_DBhandler();
		$gids             = get_user_meta( $id, 'pm_group', true );
				$gid      = $this->pg_filter_users_group_ids( $gids );
		if ( ! isset( $gid ) || $gid == '' ) {
			$gid = array();
		}

				$gid_in = 'associate_group in(' . implode( ',', $gid ) . ')';
		$data           = array();
		if ( $gid != '' && $gid != false && ! empty( $gid_in ) ) {
			if ( $visibility == false ) {
				$where = 1;
			} else {
				$where = array( 'visibility' => $visibility );
			}
			$fields = $dbhandler->get_all_result( 'FIELDS', $column = '*', $where, 'results', 0, false, $sort_by = 'ordering', false, $gid_in );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					$data[ $field->field_name ] = get_user_meta( $id, $field->field_key, true );
				}
			}
		}
		return $data;
	}

	private function pm_extend_image_extensions( $extensions ) {
		$extensions_array = array_filter( array_map( 'trim', explode( '|', strtolower( $extensions ) ) ) );
		$extensions_array = array_unique( array_merge( $extensions_array, array( 'webp', 'avif' ) ) );

		return implode( '|', $extensions_array );
	}

	private function pm_maybe_extend_image_types( $extensions ) {
		$normalized       = strtolower( trim( $extensions ) );
		$extensions_array = array_filter( array_map( 'trim', explode( '|', $normalized ) ) );
		$non_images       = array_diff( $extensions_array, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif' ) );

		if ( empty( $non_images ) ) {
			return $this->pm_extend_image_extensions( implode( '|', $extensions_array ) );
		}

		return $extensions;
	}

	private function pm_get_allowed_extensions_array( $extensions ) {
		$normalized = strtolower( trim( (string) $extensions ) );
		return array_values( array_unique( array_filter( array_map( 'trim', explode( '|', $normalized ) ) ) ) );
	}

	public function make_upload_and_get_attached_id( $filefield, $allowed_ext, $require_imagesize = array(), $parent_post_id = 0 ) {
		$allowed_extensions = $this->pm_get_allowed_extensions_array( $allowed_ext );
		$attach_id          = '';
		if ( is_array( $filefield ) && ! empty( $filefield ) ) {
			$file = array(
				'name'     => $filefield['name'],
				'type'     => $filefield['type'],
				'tmp_name' => $filefield['tmp_name'],
				'error'    => $filefield['error'],
				'size'     => $filefield['size'],
			);

			if ( ! empty( $require_imagesize ) && ! empty( $file['tmp_name'] ) ) {
				$imagesize     = @getimagesize( $file['tmp_name'] );
				$image_width   = is_array( $imagesize ) && isset( $imagesize[0] ) ? $imagesize[0] : 0;
				$image_height  = is_array( $imagesize ) && isset( $imagesize[1] ) ? $imagesize[1] : 0;

				if ( isset( $require_imagesize[2] ) && $file['size'] > $require_imagesize[2] ) {
							   $too_small = sprintf( esc_html__( 'Image size exceeds the maximum limit. Maximum allowed image size is %d byte.', 'profilegrid-user-profiles-groups-and-communities' ), $require_imagesize['2'] );
				} elseif ( $image_width && $image_height && ( $image_width < $require_imagesize['0'] || $image_height < $require_imagesize['1'] ) ) {
								$too_small = sprintf( esc_html__( 'Image dimensions are too small. Minimum size is %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $require_imagesize['0'], $require_imagesize['1'] );
				} else {
					$too_small = false;
				}
			} else {
				$too_small = false;
			}

			$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
			if ( ! in_array( $extension, $allowed_extensions, true ) ) {
				return esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
			}
			if ( false !== $too_small ) {
				return $too_small;
			}
			if ( $filefield['error'] === 0 ) {
				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
				}
				$upload_overrides = array( 'test_form' => false );
				$movefile         = wp_handle_upload( $file, $upload_overrides );
				if ( isset( $movefile['error'] ) ) {
					return $movefile['error'];
				}
				if ( ! empty( $movefile['file'] ) ) {
					// $filename should be the path to a file in the upload directory.
					$filename = $movefile['file'];
					// The ID of the post this attachment is for.

					// Check the type of tile. We'll use this as the 'post_mime_type'.
					$filetype          = wp_check_filetype( basename( $filename ), null );
					$current_file_type = strtolower( $filetype['ext'] );
					if ( in_array( $current_file_type, $allowed_extensions, true ) && $too_small == false ) {

						// Get the path to the upload directory.
						$wp_upload_dir = wp_upload_dir();
						// Prepare an array of post data for the attachment.
						$attachment = array(
							'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
							'post_mime_type' => $filetype['type'],
							'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( (string) $filename ) ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);
						// Insert the attachment.
						include_once ABSPATH . 'wp-admin/includes/image.php';
						$attach_id   = wp_insert_attachment( $attachment, $filename, $parent_post_id );
						$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
						wp_update_attachment_metadata( $attach_id, $attach_data );
                                                do_action('pg_media_file_uploaded', $attach_id, $attachment);

					} else {
						if ( ! in_array( $current_file_type, $allowed_extensions, true ) ) {
							return esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
						} else {
							return $too_small;
						}
					}
				}
			}
		}
		return $attach_id;

	}

	public function pm_check_field_exist( $gid, $type, $signup = false ) {
		$dbhandler = new PM_DBhandler();
		if ( isset( $gid ) && isset( $type ) ) {
			$where = array(
				'associate_group' => $gid,
				'field_type'      => $type,
			);
			if ( $signup == true ) {
				$where['show_in_signup_form'] = 1;
			}
			$result = $dbhandler->get_all_result( 'FIELDS', '*', $where, 'results', 0, false, 'ordering' );

			if ( $result == null ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}

	}

	public function pm_get_currency_symbol() {
			  $dbhandler = new PM_DBhandler();
		$currency        = $dbhandler->get_global_option_value( 'pm_paypal_currency', 'USD' );
		switch ( $currency ) {
			case 'USD':
				$sign = '&#36;';
				break;
			case 'EUR':
				$sign = '&#0128;';
				break;
			case 'GBP':
				$sign = '&#163;';
				break;
			case 'AUD':
				$sign = '&#36;';
				break;
			case 'BRL':
				$sign = 'R&#36;';
				break;
			case 'CAD':
				$sign = '&#36;';
				break;
			case 'HKD':
				$sign = '&#36;';
				break;
			case 'ILS':
				$sign = '&#8362;';
				break;
			case 'JPY':
				$sign = '&#165;';
				break;
			case 'MXN':
				$sign = '&#36;';
				break;
			case 'NZD':
				$sign = '&#36;';
				break;
			case 'SGD':
				$sign = '&#36;';
				break;
			case 'THB':
				$sign = '&#3647;';
				break;
			case 'INR':
				$sign = '&#8377;';
				break;
			case 'TRY':
				$sign = '&#8378;';
				break;
			default:
				$sign = $currency;
		}
		return $sign;

	}

	public function pm_encrypt_decrypt_pass( $action, $string ) {
                $dbhandler = new PM_DBhandler();
		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$secret_key     = (string) $dbhandler->get_global_option_value( 'pm_encrypt_secret_key', '' );
                $secret_iv      = (string) $dbhandler->get_global_option_value( 'pm_encrypt_secret_iv', '' );
		if ( '' === $secret_key || '' === $secret_iv ) {
			return false;
		}
		// hash
		$key = hash( 'sha256', $secret_key );
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
		if ( $action == 'encrypt' ) {
			if ( function_exists( 'openssl_encrypt' ) ) {
				$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
				$output = base64_encode( $output );
			} else {
				$output = base64_encode( $string );
			}
		} elseif ( $action == 'decrypt' ) {

			if ( function_exists( 'openssl_decrypt' ) ) {
				$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
			} else {
				$output = base64_decode( $string );
			}
		}
		return $output;
	}

	public function profile_magic_captcha_verification( $response, $remote_ip ) {
				$dbhandler = new PM_DBhandler();
		$secret_key        = $dbhandler->get_global_option_value( 'pm_recaptcha_secret_key' );
		// make a GET request to the Google reCAPTCHA Server
		$request = wp_remote_get(
			'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response . '&remoteip=' . $remote_ip
		);
		// get the request response body
		$response_body = wp_remote_retrieve_body( $request );
		$result        = json_decode( $response_body, true );
		return $result['success'];
	}

	public function profile_magic_check_username_exist( $username ) {
		if ( username_exists( $username ) ) {
			if ( is_multisite() && is_user_member_of_blog( username_exists( $username ) ) == false ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function profile_magic_check_user_email_exist( $email ) {
		if ( email_exists( $email ) ) {
			if ( is_multisite() && is_user_member_of_blog( email_exists( $email ) ) == false ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function profile_magic_frontend_server_validation( $post, $files, $server, $fields, $textdomain, $type = '' ) {
				$dbhandler = new PM_DBhandler();
				$pmsanitizer = new PM_sanitizer();
                                
		$error             = array();
		$has_username_field = false;
		if ( isset( $fields ) && ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$field_options = maybe_unserialize( $field->field_options );
				if ( ! empty( $field_options ) && isset( $field_options['admin_only'] ) && $field_options['admin_only'] == '1' && ! is_super_admin() ) {
					continue;
				}
					$field_key = $field->field_key;
				if ( $field->field_type == 'user_email' ) {

					if ( ! isset( $post[ $field_key ] ) || $post[ $field_key ] == '' ) {
						$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' );
					} else {
						$useremail = sanitize_email( $post[ $field_key ] );
						if ( is_email( $useremail ) == false ) {
								$error[] = esc_html__( 'Please enter a valid e-mail address', 'profilegrid-user-profiles-groups-and-communities' );
						}

						if ( $this->profile_magic_check_user_email_exist( $useremail ) ) {
								$error[] = esc_html__( 'This user is already registered. Please try with different email.', 'profilegrid-user-profiles-groups-and-communities' );
						}
					}
				}
				if ( $field->field_type == 'user_name' ) {
					$has_username_field = true;

					if ( ! isset( $post[ $field_key ] ) || $post[ $field_key ] == '' ) {
							$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' );
					} else {
							$username = sanitize_user( $post[ $field_key ], true );
						if ( $username == '' || $username == null ) {
								$error[] = esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'profilegrid-user-profiles-groups-and-communities' );
						}

						if ( $this->profile_magic_check_username_exist( $username ) ) {
										$error[] = esc_html__( 'Sorry, username already exist.', 'profilegrid-user-profiles-groups-and-communities' );
						}
					}
				}
				if ( $field->field_type == 'user_pass' ) {
					if ( ! isset( $post[ $field_key ] ) || $post[ $field_key ] == '' ) {
							$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					} else {
						if ( strlen( $post[ $field_key ] ) < 7 ) {
								$error[] .= esc_html__( 'Password is too short. At least 7 characters please!', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
						}
					}
				}
				if ( $field->field_type == 'confirm_pass' ) {
					if ( ! isset( $post[ $field_key ] ) || $post[ $field_key ] == '' ) {
							$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					} else {
						if ( $post[ $field_key ] !== $post['user_pass'] ) {
								$error[] .= esc_html__( 'Password and confirm password do not match.', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
						}
					}
				}
				if ( $field->is_required == 1 && $field->field_type != 'file' && $field->field_type != 'user_avatar' && $field->field_type != 'user_name' && $field->field_type != 'user_email' && $field->field_type != 'user_pass' && $field->field_type != 'confirm_pass' ) {
					if ( ! isset( $post[ $field_key ] ) || $post[ $field_key ] == '' ) {
							$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					} else {
						if ( is_array( $post[ $field_key ] ) ) {
								$value = implode( ',', $post[ $field_key ] );
							if ( ! isset( $value ) || $value == '' ) {
								$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
							}
						}
					}
				}
				if ( $field->is_required == 1 && ( $field->field_type == 'file' || $field->field_type == 'user_avatar' ) && $type != 'edit_profile' ) {
					$filefield = $files[ $field_key ];
					if ( is_array( $filefield ) && empty( $filefield['name'][0] ) ) {
							$error[] = $field->field_name . esc_html__( ' is a required field', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					}
				}

				if ( ( $field->field_type == 'file' || $field->field_type == 'user_avatar' ) && isset( $files[ $field_key ] ) && ! empty( $files[ $field_key ]['name'][0] ) ) {
					$field_options = maybe_unserialize( $field->field_options );
					$allowed_ext   = ( ( $field_options['allowed_file_types'] != '' ) ? $field_options['allowed_file_types'] : $dbhandler->get_global_option_value( 'pm_allow_file_types', 'jpg|jpeg|png|gif|webp|avif' ) );
					// $current_file_type = '';
					if ( $field->field_type == 'user_avatar' ) {
							$allowed_ext       = $this->pm_extend_image_extensions( 'jpg|jpeg|png|gif|webp|avif' );
							$require_imagesize = $this->pm_get_minimum_requirement_user_avatar();
					}
                                        else
                                        {
                                            $allowed_ext       = $this->pm_maybe_extend_image_types( $allowed_ext );
                                            $require_imagesize = false;
                                        }
					$allowed_extensions = $this->pm_get_allowed_extensions_array( $allowed_ext );
					$filefield        = $files[ $field_key ];

					if ( is_array( $filefield ) ) {

						for ( $i = 0; $i < count( $filefield['name'] ); $i++ ) {
								$file               = array(
									'name'     => $filefield['name'][ $i ],
									'type'     => $filefield['type'][ $i ],
									'tmp_name' => $filefield['tmp_name'][ $i ],
									'error'    => $filefield['error'][ $i ],
									'size'     => $filefield['size'][ $i ],
								);
								 $filetype          = wp_check_filetype( basename( $file['name'] ), null );
								 $current_file_type = strtolower( $filetype['ext'] );
								if ( empty( $current_file_type ) || $current_file_type == '' ) {
														  $error[] = esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
								} elseif ( ! in_array( $current_file_type, $allowed_extensions, true ) ) {
														   $error[] = esc_html__( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
								}

								if ( !empty( $require_imagesize ) ) {
									$imagesize     = getimagesize( $file['tmp_name'] );
									 $image_width  = $imagesize[0];
									 $image_height = $imagesize[1];

									if ( isset( $require_imagesize[2] ) && $file['size'] > $require_imagesize[2] ) {
																	  $error[] = sprintf( esc_html__( 'Image size exceeds the maximum limit. Maximum allowed image size is %d byte.', 'profilegrid-user-profiles-groups-and-communities' ), $require_imagesize['2'] );
									} elseif ( $image_width < $require_imagesize['0'] || $image_height < $require_imagesize['1'] ) {
																							  $error[] = sprintf( esc_html__( 'Image dimensions are too small. Minimum size is %1$d by %2$d pixels.', 'profilegrid-user-profiles-groups-and-communities' ), $require_imagesize['0'], $require_imagesize['1'] );
									}
								}
						}
					}
				}

				if ( $field->field_type == 'email' && isset( $post[ $field_key ] ) && $post[ $field_key ] != '' ) {
					if ( is_email( $post[ $field_key ] ) == false ) {
						   $error[] = esc_html__( 'Please enter a valid e-mail address', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					}
				}

				if ( $field->field_type == 'number' && isset( $post[ $field_key ] ) && $post[ $field_key ] != '' ) {
					if ( is_numeric( $post[ $field_key ] ) == false ) {
						   $error[] = esc_html__( 'Please enter a valid number', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					}
				}
				if ( $field->field_type == 'pricing' && isset( $post[ $field_key ] ) && $post[ $field_key ] != '' ) {
					if ( is_numeric( $post[ $field_key ] ) == false ) {
						   $error[] = esc_html__( 'Please enter a valid amount', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					}
				}
				if ( $field->field_type == 'DatePicker' && isset( $post[ $field_key ] ) && $post[ $field_key ] != '' ) {
                                        $pattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
					$sel_pattern = $this->pg_wp_date_format_php_to_js();
                                        if($sel_pattern == 'dd-mm-yy'){
                                            $pattern = '/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/';
                                        }elseif($sel_pattern == 'mm-dd-yy'){
                                            $pattern = '/^(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-[0-9]{4}$/';
                                        }elseif($sel_pattern == 'yy/mm/dd'){
                                            $pattern = '/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/';
                                        }elseif($sel_pattern == 'dd/mm/yy'){
                                            $pattern = '/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/';
                                        }elseif($sel_pattern == 'mm/dd/yy'){
                                            $pattern = '/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[0-9]{4}$/';
                                        }
                                        if ( ! preg_match( $pattern, $post[ $field_key ] ) ) {
                                                   $error[] = $this->pg_wp_date_format_error() .' '.$pattern; 
                                                   //$error[] = esc_html__( 'Please enter a valid date (yyyy-mm-dd format)', 'profilegrid-user-profiles-groups-and-communities' ) . '<br />';
					}
				}
			}
		}
		if ( 'edit_profile' !== $type && ! $has_username_field ) {
			$username_source = isset( $post['user_login'] ) ? $post['user_login'] : ( isset( $post['user_email'] ) ? $post['user_email'] : '' );
			$username        = $pmsanitizer->get_sanitized_frontend_field( 'user_login', $username_source );

			if ( $username == '' || $username == null ) {
				$error[] = esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'profilegrid-user-profiles-groups-and-communities' );
			} elseif ( $this->profile_magic_check_username_exist( $username ) ) {
				$error[] = esc_html__( 'Sorry, username already exist.', 'profilegrid-user-profiles-groups-and-communities' );
			}
		}
				$error = apply_filters( 'pm_frontend_server_validation', $error, $post );
				return $error;
	}

	public function profile_magic_show_captcha( $option ) {
				 $dbhandler    = new PM_DBhandler();
		$enable_recaptcha      = $dbhandler->get_global_option_value( 'pm_enable_recaptcha' );
		$enable_recaptcha_form = $dbhandler->get_global_option_value( $option );
		if ( $enable_recaptcha == 1 && $enable_recaptcha_form == 1 ) {
			return true;
		} else {
			return false;
		}

	}

	public function profile_magic_generate_password() {
		 $password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
		return $password;
	}

	public function profile_magic_check_paid_group( $gid ) {
				$dbhandler = new PM_DBhandler();
		$options           = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_options', $gid, 'id' ) );

		if ( ! empty( $options ) && isset( $options['is_paid_group'] ) && $options['is_paid_group'] == 1 ) {
			$price = $options['group_price'];
		} else {
			$price = 0;}
		return $price;
	}

	public function profile_magic_get_frontend_url( $page, $default, $gid = '' ) {
			  $dbhandler    = new PM_DBhandler();
		 $profile_magic_url = $dbhandler->get_global_option_value( $page, '0' );

		if ( $profile_magic_url == 0 ) {
			$url = $default;
		} else {
				   $post_status = get_post_status( $profile_magic_url );
			if ( $post_status == 'publish' ) {
				$url = get_permalink( $profile_magic_url );
			} else {
				$url = $default;
			}
		}

		
		 $url = apply_filters( 'profile_magic_get_frontend_url', $page, $url, $gid );
		
		 return $url;
	}

	public function profile_magic_get_error_message( $error_code, $textdomain ) {
		switch ( $error_code ) {
			case 'empty_username':
				$message = esc_html__( 'You do have an email address, right?', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'empty_password':
				$message = esc_html__( 'You need to enter a password to login.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'invalid_username':
				$message = esc_html__( "We don't have any users with that email address. Maybe you used a different one when signing up?", 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'incorrect_password':
				$err = wp_kses_post( __("You entered incorrect password. Please try again or click on <a href='%s'>“Forgot Password”</a>", 'profilegrid-user-profiles-groups-and-communities' ));
				if ( function_exists( 'is_wpe' ) ) :
					$forgot_pwd_url = '/wp-login.php?action=lostpassword&wpe-login=true';
							else :
								$forgot_pwd_url = '/wp-login.php?action=lostpassword';
							endif;
								$forget_password_url = $this->profile_magic_get_frontend_url( 'pm_forget_password_page', site_url( $forgot_pwd_url ) );
							$message                 = sprintf( $err, $forget_password_url );
				break;
			case 'empty_username':
				$message = esc_html__( 'You need to enter your email address to continue.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'account_disabled':
				$message = esc_html__( 'Account disabled.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'payment_pending':
				$url     = wp_kses_post( "Your account has been deactivated due to a pending payment. <a href='%s'>Do you wish to pay now?</a>", 'profilegrid-user-profiles-groups-and-communities' );
				$message = sprintf( $url, $this->pm_get_repayment_url(sanitize_text_field(wp_unslash($_REQUEST['id']) )) );
				break;

			case 'invalid_email':
				$message = esc_html__( 'We could not recognize the email address you just entered. Please check back and try again.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'invalidcombo':
				$message = esc_html__( 'We could not recognize the username or email address you just entered. Please check back and try again.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'expiredkey':
				$message = esc_html__( 'The password reset link you used is not valid anymore.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'invalidkey':
				$message = esc_html__( 'The password reset link you used is not valid anymore.', 'profilegrid-user-profiles-groups-and-communities' );
				break;

			case 'password_reset_mismatch':
				$message = esc_html__( "The two passwords you entered don't match.", 'profilegrid-user-profiles-groups-and-communities' );
				break;

			case 'password_reset_empty':
				$message = esc_html__( "Sorry, we don't accept empty passwords.", 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'password_too_short':
				$message = esc_html__( 'Password is too short. At least 7 characters please!', 'profilegrid-user-profiles-groups-and-communities' );
				break;

			case 'loginrequired':
								$err            = __("Login required to view this page. Please ", 'profilegrid-user-profiles-groups-and-communities' );
                                                                $err            .= "<a href='%s'>".__("Login",'profilegrid-user-profiles-groups-and-communities')."</a>.";
								$login_page_url = $this->profile_magic_get_frontend_url( 'pm_user_login_page', site_url( '/wp-login.php' ) );
				$message                        = sprintf( $err, $login_page_url );
				break;
			case 'not_permitted':
				$message = esc_html__( 'You do not have permission to view this page.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'ajx_failed_del':
				$message = esc_html__( 'Failed to update user information. Can not activate user.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'invalid_code':
				$message = esc_html__( 'Invalid Activation code.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'pm_reset_pw_limit_exceed':
					$message = esc_html__( 'You have reached the limit for requesting password change for this user.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'inactivity':
				$message = esc_html__( 'You have been logged out due to inactivity.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'blocked_ip':
				$message = esc_html__( "Your IP has been Banned. You don't access this page.", 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'blocked_email':
				$message = esc_html__( 'Sorry, you cannot register since this email has been blocked by a site administrator.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'blocked_domain':
				$message = esc_html__( "You don't able to logged in due to your domain has been blocked by administrator.", 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'blocked_words':
				$message = esc_html__( 'Sorry, you cannot registered with this username since it uses a word blocked by a site administrator.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'blocked_email_on_login':
				$message = esc_html__( 'Sorry, you are not allowed to login. This email has been blocked by a site administrator.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'account_deleted':
				 $message = esc_html__( 'Your account was successfully removed and all profile data deleted.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 'need_activation':
				 $message = esc_html__( 'An activation link is sent to your email. Please verify.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			default:
				$message = esc_html__( 'An unknown error occurred. Please try again later.', 'profilegrid-user-profiles-groups-and-communities' );
				break;
		}
		return $message;
	}

	public function pg_get_blog_post_data( $postid, $field ) {
		if ( $postid ) {
			$post = get_post( $postid );
			switch ( $field ) {
				case 'post_name':
					$value = $post->post_title;
					break;
				case 'edit_post_link':
					$value = $this->pg_get_edit_blog_post_link( $postid );

					break;
				case 'post_link':
					$value = get_permalink( $postid );
					break;
				default:
					$value = '';
					break;
			}
		} else {
			$value = '';
		}

		return $value;
	}

	public function profile_magic_get_user_field_value( $userid, $field, $field_type = '',$edit= false ) {
		$user_info = get_userdata( $userid );
		$dbhandler = new PM_DBhandler();
		if ( $user_info == false ) {
			return '';
		} else {
			switch ( $field ) {

				case 'user_login':
						$value = $user_info->user_login;
					break;
				case 'user_pass':
						// Passwords are never returned as profile data.
						$value = '';
					break;
				case 'user_nicename':
						$value = $user_info->user_nicename;
					break;
				case 'user_email':
						$value = $user_info->user_email;
					break;
				case 'user_url':
							$value = $user_info->user_url;
					break;
				case 'user_registered':
						$value = $user_info->user_registered;
					break;
				case 'display_name':
						$value = $user_info->display_name;
					break;
				case 'first_name':
						$value = $user_info->first_name;
					break;
				case 'last_name':
						$value = $user_info->last_name;
					break;
				case 'nickname':
						$value = $user_info->nickname;
					break;
				case 'description':
						$value = $user_info->description;
					break;
				case 'wp_capabilities':
						$value = $user_info->wp_capabilities;
					break;
				case 'admin_color':
						$value = $user_info->admin_color;
					break;
				case 'closedpostboxes_page':
						$value = $user_info->closedpostboxes_page;
					break;
				case 'primary_blog':
						$value = $user_info->primary_blog;
					break;
				case 'rich_editing':
						$value = $user_info->rich_editing;
					break;
				case 'source_domain':
						$value = $user_info->source_domain;
					break;
				case 'profile_link':
						$value = $this->pm_get_user_profile_url( $userid );
					break;
				case 'site_name':
						$value = get_bloginfo( 'name' );
					break;
				case 'group_name':
						$gids = get_user_meta( $userid, 'pm_group', true );
						$gid  = $this->pg_filter_users_group_ids( $gids );
					if ( isset( $gid ) && is_array( $gid ) ) {
						$gid = $this->pg_get_primary_group_id( $gid );}
						$groupinfo = $dbhandler->get_row( 'GROUPS', $gid );
					if ( isset( $groupinfo ) ) {
						$value = $groupinfo->group_name;
					} else {
						$value = '';}
					break;
				case 'group_admin_label':
						$gids = get_user_meta( $userid, 'pm_group', true );
						$gid  = $this->pg_filter_users_group_ids( $gids );
					if ( isset( $gid ) && is_array( $gid ) ) {
						$gid = $this->pg_get_primary_group_id( $gid );}
						$groupinfo = $dbhandler->get_row( 'GROUPS', $gid );
					if ( isset( $groupinfo ) ) {
						$group_options = maybe_unserialize( $groupinfo->group_options );}
					if ( isset( $group_options['admin_label'] ) ) {
						$value = $group_options['admin_label'];
					} else {
						$value = '';}
					break;
				default:
						$value      = get_user_meta( $userid, $field, true );
                                                if ($value == "Female"){
							$value = esc_html__("Female", 'profilegrid-user-profiles-groups-and-communities');
						}
						if ($value == "Male"){
							$value = esc_html__("Male", 'profilegrid-user-profiles-groups-and-communities');
						}
                                                if ($value == "Others"){
							$value = esc_html__("Others", 'profilegrid-user-profiles-groups-and-communities');
						}
                                                if ($value == "Taiwan, Province of China"){
							$value = esc_html__("Taiwan", 'profilegrid-user-profiles-groups-and-communities');
						}
                                        if($field_type!='' && $field_type=='term_checkbox' && $value != '')
                                        {
                                            $value = esc_html__('Yes','profilegrid-user-profiles-groups-and-communities');
                                        }
                                                
						$additional = maybe_unserialize( $value );
					if ( is_array( $additional ) && isset( $additional['rm_file_field'] ) ) {
						unset( $additional['rm_file_field'] );
						$additional_value = implode( ',', $additional );
						$value            = $this->profile_magic_get_user_attachment( $additional_value );
					}
					if ( $field_type != '' && $field_type == 'file' && $value != '' && $edit!==true) {
							$value = $this->profile_magic_get_user_attachment( $value );

					}
					if ( $field_type != '' && $field_type == 'user_avatar' && $value != '' ) {
							$value = $this->profile_magic_get_user_attachment( $value );

					}
					if ( $field_type != '' && $field_type == 'url' && $value != '' && $edit!==true ) {
						$value = $this->profile_magic_get_link_field( $additional );

					}
                                        
                                        if ( $field_type != '' && $field_type == 'DatePicker' && $value != '' ) {
						$value = $this->profile_magic_change_date_format( $additional );

					}
					break;
			}
                        $value = json_decode(json_encode($value), true);
                        
                        if(is_object($value))
                        {
                            $value = '';
                        }
                        
                        $value = apply_filters( 'pm_get_user_field_value', $value, $field, $field_type, $userid );
			return $value;
		}
	}
        
        public function profile_magic_change_date_format_old($value)
        {
            $dateFormat = $this->pg_date_format();
            $dateTime = new DateTime($value);
            // Format the date in the desired format
            $formattedDate = $dateTime->format($dateFormat);
            return $formattedDate;
        }
        
        public function profile_magic_change_date_format($value)
        {
            $dateFormat = $this->pg_date_format(); // Target format you want to display

            // Try to create DateTime object from dd/mm/yyyy format
            $dateTime = DateTime::createFromFormat($dateFormat, $value);

            // If parsing failed, handle gracefully
            if (!$dateTime) {
                // Attempt with d/m/y format if needed
                $dateTime = DateTime::createFromFormat($dateFormat, $value);

                if (!$dateTime) {
                    // Optionally log error or return original value
                    return $value;
                }
            }

            // Format the date in the desired format
            $formattedDate = $dateTime->format($dateFormat);
            return $formattedDate;
        }

        

	public function profile_magic_get_link_field( $value ) {
		$attachment_html = '';
		if ( ! empty( $value ) ) {
			if ( ! empty( $value['url'] ) ) {
				$link = $value['url'];
			} else {
				$link = '';
			}

			if ( ! empty( $value['text'] ) ) {
				$text = $value['text'];
			} else {
				$text = $link;
			}
			if ( ! empty( $text ) ) {
				$attachment_html = '<a href="' . $link . '" target="_blank">' . $text . '</a>';
			}
		}
		return $attachment_html;
	}

        public function profile_magic_get_user_attachment( $value, $key = '' ) {
		$attachment_html = '';
                if(!is_array($value)){
                    $values          = explode( ',', $value );
                }elseif(isset($value['rm_field_type']) && isset($value[0])){
                    $values = array($value[0]);
                }else{
                    $values = array();
                }
                if(!empty($values)){
                    foreach ( $values as $fileid ) {
                                            $attachment_html .= '<span class="pm_frontend_attachment">';
                            $attachment_html         .= '<span class="attachment_icon">' . wp_get_attachment_link( $fileid, 'thumbnail', false, true, false ) . '</span>';
                            $attachment_html         .= '<span class="pm-attachment-title pm-dbfl"><a href="' . wp_get_attachment_url( $fileid ) . '">' . get_the_title( $fileid ) . '</a></span>';
                            if ( $key != '' ) {
                                    $attachment_html .= '<a class="removebutton"><span onClick="pm_remove_attachment(this,\'' . $key . '\',' . $fileid . ')" class="remove">' . esc_html__( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ) . '</span></a>';
                            }
                                            $attachment_html .= '</span>';
                    }
 
                    unset( $values );
                }
		return $attachment_html;
	}
 
	public function profile_magic_edit_user_attachment( $value, $key = '' ) {
		if(!is_array($value)){
                    $values          = explode( ',', $value );
                }elseif(isset($value['rm_field_type']) && isset($value[0])){
                    $values = array($value[0]);
                }else{
                    $values = array();
                }
                if(!empty($values)){
                    foreach ( $values as $fileid ) {
                            ?>
<span class="pm_frontend_attachment
<?php
                                            if ( $key != '' ) {
                                                    echo ' pm_edit_attachment'; }
                                            ?>
                                            ">
<span class="attachment_icon"> <?php echo wp_get_attachment_link( $fileid, 'full', false, true, false ); ?></span>
<a class="pm_removebutton"><span onClick="pm_remove_attachment(this, '<?php echo esc_attr( $key ); ?>', <?php echo esc_attr( $fileid ); ?>)" class="remove"><?php esc_html_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></a>
</span>
<?php
                    }
                    unset( $values );
                }
		return;
	}
            
	public function profile_magic_get_from_email() {
				$dbhandler = new PM_DBhandler();
		if ( $dbhandler->get_global_option_value( 'pm_enable_smtp' ) == 1 ) {
			$email_address = $dbhandler->get_global_option_value( 'pm_smtp_from_email_address', get_option( 'admin_email' ) );
		} else {
			$from_name     = $dbhandler->get_global_option_value( 'pm_from_email_name', get_bloginfo( 'name' ) );
			$email_address = $from_name . ' <' . $dbhandler->get_global_option_value( 'pm_from_email_address', get_option( 'admin_email' ) ) . '>';
		}
		return $email_address;
	}

	public function profile_magic_get_admin_email() {
			   $dbhandler      = new PM_DBhandler();
				$email_address = '';
				$email_array   = maybe_unserialize( $dbhandler->get_global_option_value( 'pm_admin_email' ) );
		if ( ! empty( $email_array ) && is_array( $email_array ) ) {
			$email_address = implode( ',', $email_array );
		}
		if ( $email_address == '' || empty( $email_address ) ) {
			$email_address = get_option( 'admin_email' );
		}
		return $email_address;
	}

	public function profile_magic_frontend_registration_request( $post, $files, $server, $gid, $fields ) {
			  $dbhandler     = new PM_DBhandler();
				$pmsanitizer = new PM_sanitizer();
		$user_email          = $pmsanitizer->get_sanitized_frontend_field( 'user_email', $post['user_email'] );
		$user_role           = $this->pg_get_frontend_registration_role( $gid );
		$password            = ( isset( $post['user_pass'] ) ? $post['user_pass'] : $this->profile_magic_generate_password() );

		if ( isset( $post['user_login'] ) ) {
			$user_name = $pmsanitizer->get_sanitized_frontend_field( 'user_login', $post['user_login'] );
		} else {
			$user_name = $pmsanitizer->get_sanitized_frontend_field( 'user_login', $post['user_email'] );
		}

		$user_id               = $dbhandler->pm_add_user( $user_name, $password, $user_email, $user_role );
		if ( is_wp_error( $user_id ) || ! is_numeric( $user_id ) ) {
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			return new WP_Error(
				'pm_registration_failed',
				esc_html__( 'An unknown error occurred. Please try again later.', 'profilegrid-user-profiles-groups-and-communities' )
			);
		}
				$is_paid_group = $this->profile_magic_check_paid_group( $gid );
				$group_type    = $this->profile_magic_get_group_type( $gid );
				do_action( 'profile_magic_submit_data_before_join_group', $post, $files, $server, $gid, $fields, $user_id, 'profilegrid-user-profiles-groups-and-communities' );

		delete_user_meta( $user_id, 'user_pass' );
		update_user_meta( $user_id, 'rm_user_status', '1' );
		$this->pm_update_user_custom_fields_data( $post, $files, $server, $gid, $fields, $user_id );
		if ( $is_paid_group == '0' ) {
					$this->profile_magic_join_group_fun( $user_id, $gid, $group_type );
		}

				return $user_id;
	}

	public function pg_is_group_open_for_frontend_registration( $gid ) {
		return 'open' === $this->profile_magic_get_group_type( $gid );
	}

	public function pg_get_frontend_registration_role( $gid ) {
		$dbhandler = new PM_DBhandler();
		$user_role = sanitize_key( (string) $dbhandler->get_value( 'GROUPS', 'associate_role', $gid, 'id' ) );
		$default_role = sanitize_key( (string) get_option( 'default_role', 'subscriber' ) );
		if ( '' === $default_role ) {
			$default_role = 'subscriber';
		}

		$wp_roles = wp_roles();
		if ( ! $wp_roles || ! isset( $wp_roles->roles[ $default_role ] ) ) {
			return 'subscriber';
		}

		if ( '' === $user_role || ! isset( $wp_roles->roles[ $user_role ] ) ) {
			return $default_role;
		}

		if ( $this->pg_is_role_allowed_for_frontend_registration( $user_role, $default_role, $wp_roles ) ) {
			return $user_role;
		}

		return $default_role;
	}

	private function pg_is_role_allowed_for_frontend_registration( $user_role, $default_role, $wp_roles = null ) {
		$user_role = sanitize_key( (string) $user_role );
		$default_role = sanitize_key( (string) $default_role );
		$wp_roles = $wp_roles instanceof WP_Roles ? $wp_roles : wp_roles();

		if ( ! $wp_roles || ! isset( $wp_roles->roles[ $user_role ], $wp_roles->roles[ $default_role ] ) ) {
			return false;
		}

		$user_caps = isset( $wp_roles->roles[ $user_role ]['capabilities'] ) && is_array( $wp_roles->roles[ $user_role ]['capabilities'] )
			? $wp_roles->roles[ $user_role ]['capabilities']
			: array();
		$default_caps = isset( $wp_roles->roles[ $default_role ]['capabilities'] ) && is_array( $wp_roles->roles[ $default_role ]['capabilities'] )
			? $wp_roles->roles[ $default_role ]['capabilities']
			: array();

		foreach ( $user_caps as $capability => $granted ) {
			if ( empty( $granted ) ) {
				continue;
			}

			if ( empty( $default_caps[ $capability ] ) ) {
				return false;
			}
		}

		return true;
	}

	public function pm_admin_notification_message_html( $post, $gid, $fields, $exclude = array() ) {
		$html = '';
		if ( ! empty( $fields ) ) {
			$html .= '<table>';
			foreach ( $fields as $field ) {
				if ( in_array( $field->field_type, $exclude ) ) {
					continue;
				}
				if ( isset( $post[ $field->field_key ] ) ) {
					if ( is_array( $post[ $field->field_key ] ) ) {
							$value = implode( ',', $post[ $field->field_key ] );
					} else {
							$value = $post[ $field->field_key ];
					}
					$html .= '<tr><td>' . $field->field_name . '</td><td>' . $value . '</td></tr>';
				}
			}
			$html .= '</table>';
		}
		return $html;
	}

	public function pm_get_minimum_requirement_user_avatar() {
		$dbhandler     = new PM_DBhandler();
		$minimum_width = trim( $dbhandler->get_global_option_value( 'pg_profile_photo_minimum_width', 'DEFAULT' ) );
		if ( $minimum_width == '' || $minimum_width == 'DEFAULT' ) {
			$minimum_height = 150;
		} else {
			$minimum_height = $minimum_width;
		}

		$maximum_size    = trim( $dbhandler->get_global_option_value( 'pg_profile_image_max_file_size', '' ) );
		$minimum_require = array();
		if ( $minimum_width == '' || $minimum_width == 'DEFAULT' ) {
			$minimum_require[0] = 150;
		} else {
			$minimum_require[0] = $minimum_width;
		}
		$minimum_require[1] = $minimum_height;

		if ( $maximum_size != '' ) {
			$minimum_require[2] = $maximum_size;
		}

		return $minimum_require;
	}

	public function pm_update_user_custom_fields_data( $post, $files, $server, $gid, $fields, $user_id ) {
			  $dbhandler     = new PM_DBhandler();
                          
				$pmsanitizer = new PM_sanitizer();
                                $post = $pmsanitizer->sanitize($post);
				if(isset($post['pg_group']) && !empty($post['pg_group']))
                {
					update_user_meta( $user_id,'pg_select_user_group', sanitize_text_field($post['pg_group']) );
                }
		if ( isset( $fields ) && ! empty( $fields ) ) :
                        foreach ( $fields as $field ) {
                                $value         = '';
				$field_key     = $field->field_key;
				if ( $field->field_type == 'user_url' ) {
					$value = $pmsanitizer->get_sanitized_frontend_field( $field->field_type, $post[ $field_key ] );
                                        if ( $value != '' ) 
                                        {
                                            wp_update_user(
                                                        array(
                                                                'ID'       => $user_id,
                                                                'user_url' => $value,
                                                        )
                                                );
                                        }
                                        else
                                        {
                                            wp_update_user(
                                                    array(
                                                            'ID'       => $user_id,
                                                            'user_url' => '',
                                                    )
                                            );
                                        }
				}
                        }
                    
			foreach ( $fields as $field ) {

				if ( $field->field_type == 'user_pass' || $field->field_type == 'confirm_pass' || $field->field_type == 'user_url' ) {
					continue;
				}
				$value         = '';
				$field_key     = $field->field_key;
				$field_options = maybe_unserialize( $field->field_options );
				if ( ! empty( $field_options ) && isset( $field_options['admin_only'] ) && $field_options['admin_only'] == '1' && ! is_super_admin() ) {
					continue;
				}
				if ( $field->field_type == 'file' || $field->field_type == 'user_avatar' ) {
					$allowed_ext = ( ( $field_options['allowed_file_types'] != '' ) ? $field_options['allowed_file_types'] : $dbhandler->get_global_option_value( 'pm_allow_file_types', 'jpg|jpeg|png|gif|webp|avif' ) );
					if ( $field->field_type == 'user_avatar' ) {
						$allowed_ext         = $this->pm_extend_image_extensions( 'jpg|jpeg|png|gif|webp|avif' );
					} else {
						$allowed_ext         = $this->pm_maybe_extend_image_types( $allowed_ext );
					}
					$filefield   = $files[ $field_key ];
					if ( is_array( $filefield ) ) {
						$attchment_id = array();
                                                $arr_count = count( $filefield['name']);
						for ( $i = 0; $i < $arr_count; $i++ ) {
							$file = array(
								'name'     => $filefield['name'][ $i ],
								'type'     => $filefield['type'][ $i ],
								'tmp_name' => $filefield['tmp_name'][ $i ],
								'error'    => $filefield['error'][ $i ],
								'size'     => $filefield['size'][ $i ],
							);
							if ( $field->field_type == 'user_avatar' ) {
								$minimum_requirement = $this->pm_get_minimum_requirement_user_avatar();
							} else {
								$minimum_requirement = array();
							}
							$attchmentid = $this->make_upload_and_get_attached_id( $file, $allowed_ext, $minimum_requirement );
							if ( is_numeric( $attchmentid ) && (int) $attchmentid > 0 ) {
								$attchment_id[] = $attchmentid;
							}
						}
						if ( ! empty( $attchment_id ) ) {
							$value = implode( ',', $attchment_id );
							unset( $attchment_id );
						}
					}
				} else {
					if ( isset( $post[ $field_key ] ) ) {
						if ( is_array( $post[ $field_key ] ) ) {
							
												$value = maybe_serialize($pmsanitizer->sanitize( $pmsanitizer->remove_magic_quotes($post[ $field_key ]) ));
						} else {
							$value = $pmsanitizer->get_sanitized_frontend_field( $field->field_type, $post[ $field_key ] );
						}
					}
				}
				if ( $field->field_type == 'user_url' ) {
                                    if ( $value != '' ) {
					wp_update_user(
						array(
							'ID'       => $user_id,
							'user_url' => $value,
						)
					);
                                    }
                                    else
                                    {
                                        wp_update_user(
						array(
							'ID'       => $user_id,
							'user_url' => '',
						)
					);
                                    }
				} elseif ( $field->field_type == 'user_email' ) {
                                    if ( $value != '' ) {
							wp_update_user(
								array(
									'ID'         => $user_id,
									'user_email' => $value,
								)
							);
                                    }
				} else {
					if ( $field->field_type == 'file' ) {
						if ( $value != '' ) {
											$user_attachment = get_user_meta( $user_id, $field_key, true );

							if ( $user_attachment != '' && $dbhandler->get_global_option_value( 'pm_allow_multiple_attachments' ) == 1 ) {
								$oldids          = explode( ',', $user_attachment );
								$newids          = explode( ',', $value );
								$all_attachments = array_merge( $oldids, $newids );
								$values          = implode( ',', $all_attachments );
								update_user_meta( $user_id, $field_key, $values );
							} else {
								update_user_meta( $user_id, $field_key, $value );
							}
						}
					} elseif ( $field->field_type == 'user_avatar' ) {
						if ( $value != '' ) {
							update_user_meta( $user_id, $field_key, $value );
						}
					} else {
						update_user_meta( $user_id, $field_key, $value );
					}
				}
			}
				do_action( 'pm_update_user_profile', $user_id );
		endif;
	}
	public function pm_get_user_avatar( $userid ) {
		$avatar = get_avatar(
			$userid,
			274,
			'',
			false,
			array(
				'class'         => 'pm-user',
				'force_display' => true,
			)
		);
		
		return $avatar;
	}

	public function pm_get_repayment_url( $uid ) {
		$gids     = get_user_meta( $uid, 'pm_group', true );
		$gid      = $this->pg_filter_users_group_ids( $gids );
                $group_payment_array = maybe_unserialize(get_user_meta($uid,'pm_group_payment_status', true));
                if(!empty($group_payment_array))
                foreach ($group_payment_array as $key=>$status)
                {
                    if($status=='pending')
                    {
                        $gid = $key;
                        break;
                    }
                }               
		$registration_url = $this->profile_magic_get_frontend_url( 'pm_registration_page', '' );
		$registration_url = add_query_arg( 'gid', $gid, $registration_url );
		$registration_url = add_query_arg( 'uid', $uid, $registration_url );
		$registration_url = add_query_arg( 'action', 're_process', $registration_url );
		return esc_url( $registration_url );

	}

	public function pm_get_user_redirect( $gid ) {
			  $dbhandler = new PM_DBhandler();
		$options         = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_options', $gid ) );
		$url             = '';
		if ( ! empty( $options ) && isset( $options['redirect'] ) ) {
			switch ( $options['redirect'] ) {
				case 'url':
					$url = $options['redirect_url'];
					break;
				case 'page':
					$url = get_permalink( $options['redirect_page_id'] );
					break;
				default:
					$url = '';
					break;
			}
		}
		return $url;
	}
        
        public function pm_generate_user_manager_action_url($action,$user_id,$nonce)
        {
            $url = admin_url('admin.php?page=pm_user_manager');
            $url = add_query_arg('action',$action,$url);
            $url = add_query_arg('user',$user_id,$url);
            $url = add_query_arg( '_wpnonce', wp_create_nonce( $nonce ), $url );
            return $url;
        }

        public function pm_get_user_date_query_new_ui( $get ) {
            $date_query = array();
            if ( isset( $get['pg_interval'] ) && $get['pg_interval'] != '' ) {
                $date = explode('-', $get['pg_interval']);
                if ( isset( $date[0] ) && $date[1] ) {
						$end_date     = strtotime( $date[1] );
						$start_date   = strtotime( $date[0] );
						$date_query[] = array(
							'after'     => array(
								'year'  => gmdate( 'Y', $start_date ),
								'month' => gmdate( 'm', $start_date ),
								'day'   => gmdate( 'd', $start_date ),
							),
							'before'    => array(
								'year'  => gmdate( 'Y', $end_date ),
								'month' => gmdate( 'm', $end_date ),
								'day'   => gmdate( 'd', $end_date ),
							),
							'inclusive' => true,
						);
					}
		}
		return $date_query;
        }
	public function pm_get_user_date_query( $get ) {
		$date_query = array();
		if ( isset( $get['time'] ) && $get['time'] != '' && $get['time'] != 'all' ) {
			switch ( $get['time'] ) {
				case 'today':
					$today        = getdate();
					$date_query[] = array(
						'year'  => $today['year'],
						'month' => $today['mon'],
						'day'   => $today['mday'],
					);
					break;
				case 'yesterday':
					$yesterday    = getdate( strtotime( '-1 day' ) );
					$date_query[] = array(
						'year'  => $yesterday['year'],
						'month' => $yesterday['mon'],
						'day'   => $yesterday['mday'],
					);
					break;
				case 'this_week':
					$start_date   = gmdate( 'Y-m-d', strtotime( 'previous monday' ) );
					$newend_date  = strtotime( $start_date );
					$today        = getdate();
					$date_query[] = array(
						'after'     => array(
							'year'  => gmdate( 'Y', $newend_date ),
							'month' => gmdate( 'm', $newend_date ),
							'day'   => gmdate( 'd', $newend_date ),
						),
						'before'    => array(
							'year'  => $today['year'],
							'month' => $today['mon'],
							'day'   => $today['mday'],
						),
						'inclusive' => true,

					);
					break;
				case 'last_week':
					$end_date     = gmdate( 'Y-m-d', strtotime( 'previous sunday' ) );
					$now          = strtotime( $end_date );
					$WeekMon      = mktime( 0, 0, 0, gmdate( 'm', $now ), gmdate( 'd', $now ) - 6, gmdate( 'Y', $now ) );
					$start_date   = getdate( $WeekMon );
					$newend_date  = strtotime( $end_date );
					$date_query[] = array(
						'after'     => array(
							'year'  => $start_date['year'],
							'month' => $start_date['mon'],
							'day'   => $start_date['mday'],
						),

						'before'    => array(
							'year'  => gmdate( 'Y', $newend_date ),
							'month' => gmdate( 'm', $newend_date ),
							'day'   => gmdate( 'd', $newend_date ),
						),
						'inclusive' => true,
					);

					break;
				case 'this_month':
					$date_query[] = array(
						'year'  => gmdate( 'Y' ),
						'month' => gmdate( 'm' ),
					);
					break;
				case 'this_year':
					$date_query[] = array(
						'year' => gmdate( 'Y' ),
					);
					break;
				case 'specific':
					if ( isset( $get['start_date'] ) && $get['end_date'] ) {
						$end_date     = strtotime( $get['end_date'] );
						$start_date   = strtotime( $get['start_date'] );
						$date_query[] = array(
							'after'     => array(
								'year'  => gmdate( 'Y', $start_date ),
								'month' => gmdate( 'm', $start_date ),
								'day'   => gmdate( 'd', $start_date ),
							),
							'before'    => array(
								'year'  => gmdate( 'Y', $end_date ),
								'month' => gmdate( 'm', $end_date ),
								'day'   => gmdate( 'd', $end_date ),
							),
							'inclusive' => true,
						);
					}
					break;
			}
		}
		return $date_query;
	}
	public function pm_get_user_meta_query_usermanager( $get ) {
		$meta_query_array = array();

		if ( isset( $get['gid'] ) && $get['gid'] === '0' ) {
			$meta_query_array[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'pm_group',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'pm_group',
					'value'   => 'a:0:{}',
					'compare' => 'LIKE',
				),
			);

			return $meta_query_array;

		}

		if ( isset( $get['gid'] ) && $get['gid'] != '' && $get['gid'] !== '0' ) {
			$meta_query_array['relation'] = 'AND';
			$meta_query_array[]           = array(
				'key'     => 'pm_group',
				'value'   => sprintf( ':"%s";', $get['gid'] ),
				'compare' => 'like',
			);
		} 

		if ( isset( $get['status'] ) && $get['status'] != '' ) {
			if ( $get['status'] != 'all' ) {

									$meta_query_array[] = array(
										'key'     => 'rm_user_status',
										'value'   => $get['status'],
										'compare' => '=',
									);

			}
		}

		if ( isset( $get['pm_user_label'] ) && $get['pm_user_label'] != '' ) {
			if ( $get['pm_user_label'] != 'none' ) {
									$meta_query_array[] = array(
										'key'     => 'pm_user_label',
										'value'   => sprintf( ':"%s";', $get['pm_user_label'] ),
										'compare' => 'like',
									);
			} else {
					 $meta_query_array[] = array(
						 'relation' => 'OR',
						 array(
							 'key'     => 'pm_user_label',
							 'compare' => 'NOT EXISTS',
						 ),
						 array(
							 'key'     => 'pm_user_label',
							 'value'   => 'a:0:{}',
							 'compare' => 'LIKE',
						 ),
					 );
			}
		}
                if ( isset( $get['pm_user_advfieldopt'] ) && $get['pm_user_advfieldopt'] != '' ) {
			if ( $get['pm_user_advfieldopt'] != 'none' ) {
				$meta_query_array[] = array(
                                    'key'     => 'pm_user_advfieldopt',
                                    'value'   => $get['pm_user_advfieldopt'],
                                    'compare' => '=',
				);
			} else {
					 $meta_query_array[] = array(
						 'relation' => 'OR',
						 array(
							 'key'     => 'pm_user_advfieldopt',
							 'compare' => 'NOT EXISTS',
						 ),
						 array(
							 'key'     => 'pm_user_advfieldopt',
							 'value'   => '',
							 'compare' => 'LIKE',
						 ),
					 );
			}
		}
                $meta_query_array = apply_filters('pg_additional_filters_query',$meta_query_array, $get);
                
		if ( isset( $get['pg_user_avg_rating'] ) && $get['pg_user_avg_rating'] != '' ) {
			if ( $get['pg_user_avg_rating'] != 'none' ) {
				$rating       = intval( $get['pg_user_avg_rating'] );
				$below_rating = $rating + 1;
				$rating_range = $below_rating . ' and ' . $rating;

                                $meta_query_array[] = array(
                                        'relation' => 'AND',
                                        array(
                                                'key'     => 'pg_user_avg_rating',
                                                'value'   => $below_rating,
                                                'compare' => '<',
                                        ),

                                        array(
                                                'key'     => 'pg_user_avg_rating',
                                                'value'   => $rating,
                                                'compare' => '>=',
                                        ),
                                );

			} else {
				 $meta_query_array[] = array(
					 'relation' => 'OR',
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'compare' => 'NOT EXISTS',
					 ),
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'value'   => 'a:0:{}',
						 'compare' => 'LIKE',
					 ),
				 );
			}
		}

		if ( isset( $get['connection'] ) && $get['connection'] != '' ) {
			$meta_query_array[] = array(
				'key'     => 'pm_' . $get['connection'] . '_connected',
				'value'   => 1,
				'compare' => '=',
			);
		}

		if ( isset( $get['match_field'] ) && $get['match_field'] != '' && isset( $get['field_value'] ) && $get['field_value'] != '' ) {

			if ( in_array( $get['match_field'], array( 'user_url', 'user_email', 'user_nicename', 'user_login' ) ) ) {

				if ( $get['match_field'] == 'user_login' ) {

					$pos = strpos( $get['field_value'], '@' );
					if ( ! empty( $pos ) ) {

						$field_value = substr( $get['field_value'], 0, $pos );
					} else {

						 $field_value = $get['field_value'];
					}
				} else {

					$field_value = $get['field_value'];
				}
				$search_columns                     = $get['match_field'];
				$meta_query_array['search']         = "*{$field_value}*";
				$meta_query_array['search_columns'] = $search_columns;

			} else {

				$meta_query_array[] = array(
					'key'     => $get['match_field'],
					'value'   => $get['field_value'],
					'compare' => 'LIKE',
				);
			}
		}
                else
                {
                    $mqry = $this->pm_get_default_search_field_meta_query($get);
                    if($mqry)
                    {
                        $meta_query_array[] = $mqry;
                    }
                }

		return $meta_query_array;
	}
        
        
        public function pm_get_user_meta_query( $get ) {
		$meta_query_array = array();

		if ( isset( $get['gid'] ) && $get['gid'] === '0' ) {
			$meta_query_array[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'pm_group',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'pm_group',
					'value'   => 'a:0:{}',
					'compare' => 'LIKE',
				),
			);

			return $meta_query_array;

		}

		if ( isset( $get['gid'] ) && $get['gid'] != '' && $get['gid'] !== '0' ) {
			$meta_query_array['relation'] = 'AND';
			$meta_query_array[]           = array(
				'key'     => 'pm_group',
				'value'   => sprintf( ':"%s";', $get['gid'] ),
				'compare' => 'like',
			);
		} else {
			$meta_query_array['relation'] = 'AND';
			$meta_query_array[]           = array(
				'key'          => 'pm_group',
				'meta_value'   => '',
				'meta_compare' => '!=',
			);
		}

		if ( isset( $get['status'] ) && $get['status'] != '' ) {
			if ( $get['status'] != 'all' ) {

									$meta_query_array[] = array(
										'key'     => 'rm_user_status',
										'value'   => $get['status'],
										'compare' => '=',
									);

			}
		}

		if ( isset( $get['pm_user_label'] ) && $get['pm_user_label'] != '' ) {
			if ( $get['pm_user_label'] != 'none' ) {
									$meta_query_array[] = array(
										'key'     => 'pm_user_label',
										'value'   => sprintf( ':"%s";', $get['pm_user_label'] ),
										'compare' => 'like',
									);
			} else {
					 $meta_query_array[] = array(
						 'relation' => 'OR',
						 array(
							 'key'     => 'pm_user_label',
							 'compare' => 'NOT EXISTS',
						 ),
						 array(
							 'key'     => 'pm_user_label',
							 'value'   => 'a:0:{}',
							 'compare' => 'LIKE',
						 ),
					 );
			}
		}
                if ( isset( $get['pm_user_advfieldopt'] ) && $get['pm_user_advfieldopt'] != '' ) {
			if ( $get['pm_user_advfieldopt'] != 'none' ) {
				$meta_query_array[] = array(
                                    'key'     => 'pm_user_advfieldopt',
                                    'value'   => $get['pm_user_advfieldopt'],
                                    'compare' => '=',
				);
			} else {
					 $meta_query_array[] = array(
						 'relation' => 'OR',
						 array(
							 'key'     => 'pm_user_advfieldopt',
							 'compare' => 'NOT EXISTS',
						 ),
						 array(
							 'key'     => 'pm_user_advfieldopt',
							 'value'   => '',
							 'compare' => 'LIKE',
						 ),
					 );
			}
		}
                $meta_query_array = apply_filters('pg_additional_filters_query',$meta_query_array, $get);
                
		if ( isset( $get['pg_user_avg_rating'] ) && $get['pg_user_avg_rating'] != '' ) {
			if ( $get['pg_user_avg_rating'] != 'none' ) {
				$rating       = intval( $get['pg_user_avg_rating'] );
				$below_rating = $rating + 1;
				$rating_range = $below_rating . ' and ' . $rating;

                                $meta_query_array[] = array(
                                        'relation' => 'AND',
                                        array(
                                                'key'     => 'pg_user_avg_rating',
                                                'value'   => $below_rating,
                                                'compare' => '<',
                                        ),

                                        array(
                                                'key'     => 'pg_user_avg_rating',
                                                'value'   => $rating,
                                                'compare' => '>=',
                                        ),
                                );

			} else {
				 $meta_query_array[] = array(
					 'relation' => 'OR',
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'compare' => 'NOT EXISTS',
					 ),
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'value'   => 'a:0:{}',
						 'compare' => 'LIKE',
					 ),
				 );
			}
		}

		if ( isset( $get['connection'] ) && $get['connection'] != '' ) {
			$meta_query_array[] = array(
				'key'     => 'pm_' . $get['connection'] . '_connected',
				'value'   => 1,
				'compare' => '=',
			);
		}

		if ( isset( $get['match_field'] ) && $get['match_field'] != '' && isset( $get['field_value'] ) && $get['field_value'] != '' ) {

			if ( in_array( $get['match_field'], array( 'user_url', 'user_email', 'user_nicename', 'user_login' ) ) ) {

				if ( $get['match_field'] == 'user_login' ) {

					$pos = strpos( $get['field_value'], '@' );
					if ( ! empty( $pos ) ) {

						$field_value = substr( $get['field_value'], 0, $pos );
					} else {

						 $field_value = $get['field_value'];
					}
				} else {

					$field_value = $get['field_value'];
				}
				$search_columns                     = $get['match_field'];
				$meta_query_array['search']         = "*{$field_value}*";
				$meta_query_array['search_columns'] = $search_columns;

			} else {

				$meta_query_array[] = array(
					'key'     => $get['match_field'],
					'value'   => $get['field_value'],
					'compare' => 'LIKE',
				);
			}
		}
                else
                {
                    $mqry = $this->pm_get_default_search_field_meta_query($get);
                    if($mqry)
                    {
                        $meta_query_array[] = $mqry;
                    }
                }

		return $meta_query_array;
	}       
        
        public function pm_get_default_search_field_meta_query($get)
        {
            $dbhandler = new PM_DBhandler();
            $search = '';
            if(isset($get['pm_search']))
            {
                $search = $get['pm_search'];
            }
            
            if(isset($get['field_value']))
            {
                $search = $get['field_value'];
            }
            if($search=='')
            {
                return false;
            }
            $pm_default_search_field = $dbhandler->get_global_option_value('pm_default_search_field','first_name');
            switch($pm_default_search_field)
            {
                case 'first_name':
                case 'last_name':
                    $meta_query_array = array(
					'key'     => $pm_default_search_field,
					'value'   => $get['pm_search'],
					'compare' => 'LIKE',
				);
                    break;
                case 'both':
                    $meta_query_array =  array(
                            'relation' => 'OR',
                              array(
                                'key' => 'first_name',
                                'value' => $get['pm_search'],
                                'compare' => 'LIKE'
                              ),
                            array(
                                'key' => 'last_name',
                                'value' => $get['pm_search'],
                                'compare' => 'LIKE'
                              )
                            );
                    break;
                case 'default':
                    $meta_query_array = false;
                    break;
            }
            
            return $meta_query_array;
        }

	public function pm_get_user_advance_search_meta_query( $get ) {
		 $meta_query_array = array();
		 $search_string     = esc_attr( trim( $get['pm_search'] ) );
		// MATCH GID FOR SEARCH
		if ( isset( $get['gid'] ) && $get['gid'] != '' ) {
				$meta_query_array['relation'] = 'AND';
				$meta_query_array[]           = array(
					'key'     => 'pm_group',
					'value'   => sprintf( ':"%s";', $get['gid'] ),
					'compare' => 'like',
				);
		} else {
				$meta_query_array['relation'] = 'AND';
				$meta_query_array[]           = array(
					'key'          => 'pm_group',
					'meta_value'   => '',
					'meta_compare' => '>',
				);
		}

		if ( isset( $get['status'] ) && $get['status'] != '' ) {
			if ( $get['status'] != 'all' ) {

									$meta_query_array[] = array(
										'key'     => 'rm_user_status',
										'value'   => $get['status'],
										'compare' => '=',
									);

			}
		}

		if ( isset( $get['pm_user_label'] ) && $get['pm_user_label'] != '' ) {
			if ( $get['pm_user_label'] != 'none' ) {
									$meta_query_array[] = array(
										'key'     => 'pm_user_label',
										'value'   => sprintf( ':"%s";', $get['pm_user_label'] ),
										'compare' => 'like',
									);
			} else {
				   $meta_query_array[] = array(
					   'relation' => 'OR',
					   array(
						   'key'     => 'pm_user_label',
						   'compare' => 'NOT EXISTS',
					   ),
					   array(
						   'key'     => 'pm_user_label',
						   'value'   => 'a:0:{}',
						   'compare' => 'LIKE',
					   ),
				   );
			}
		}
		if ( isset( $get['pg_user_avg_rating'] ) && $get['pg_user_avg_rating'] != '' ) {
			if ( $get['pg_user_avg_rating'] != 'none' ) {
				$rating       = intval( $get['pg_user_avg_rating'] );
				$below_rating = $rating + 1;
				$rating_range = $below_rating . ' and ' . $rating;

									$meta_query_array[] = array(
										'relation' => 'AND',
										array(
											'key'     => 'pg_user_avg_rating',
											'value'   => $below_rating,
											'compare' => '<',
										),

										array(
											'key'     => 'pg_user_avg_rating',
											'value'   => $rating,
											'compare' => '>=',
										),
									);

			} else {
				 $meta_query_array[] = array(
					 'relation' => 'OR',
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'compare' => 'NOT EXISTS',
					 ),
					 array(
						 'key'     => 'pg_user_avg_rating',
						 'value'   => 'a:0:{}',
						 'compare' => 'LIKE',
					 ),
				 );
			}
		}

		if ( isset( $get['connection'] ) && $get['connection'] != '' ) {
			$meta_query_array[] = array(
				'key'     => 'pm_' . $get['connection'] . '_connected',
				'value'   => 1,
				'compare' => '=',
			);
		}

		if ( isset( $get['match_fields'] ) && isset( $get['pm_search'] ) && $get['pm_search'] != '' ) {
			if ( is_array( $get['match_fields'] ) ) {
				   $match_field_array['relation'] = 'OR';
				foreach ( $get['match_fields'] as $value ) {
					  $match_field_array[] = array(
						  'key'     => $value,
						  'value'   => $search_string,
						  'compare' => 'LIKE',
					  );
				}

					$meta_query_array[] = $match_field_array;
			} else {
				  $meta_query_array[] = array(
					  'key'     => $get['match_fields'],
					  'value'   => $search_string,
					  'compare' => 'LIKE',
				  );
			}
		}
                else
                {
                    $mqry = $this->pm_get_default_search_field_meta_query($get);
                    if($mqry)
                    {
                        $meta_query_array[] = $mqry;
                    }
                }

		return $meta_query_array;
	}


	public function pm_get_hide_users_array() {
		 $dbhandler       = new PM_DBhandler();
		$id               = array();
		$allowhiddenusers = $dbhandler->get_global_option_value( 'pm_allow_user_to_hide_their_profile', '' );
		if ( $allowhiddenusers == 1 ) {
			$args = array(
				'meta_query' => array(
					array(
						'key'     => 'pm_hide_my_profile',
						'value'   => '1',
						'compare' => '=',
					),
				),
			);

			$users = get_users( $args );
			foreach ( $users as $user ) {
				   $id[] = $user->ID;
			}
		}
		return $id;
	}

	public function pm_get_frontend_user_meta( $uid, $gid, $group_leader, $view = '', $section = '', $exclude = '' ) {
			  $dbhandler = new PM_DBhandler();
		$data            = array();
		if ( is_array( $gid ) ) {
			$gid_array = $gid;
		} else {
			$gid_array = array( $gid );}
				$additional = ' AND associate_group in(' . implode( ',', $gid_array ) . ')';
		$where              = array( 'display_on_profile' => 1 );
		if ( $view == 'group' ) {
			$where['display_on_group']            = 1;
						$where['associate_group'] = $gid;
		}

		if ( $section != '' ) {
			$where['associate_section'] = $section;
		}

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( ( ! empty( $group_leader ) && in_array( $current_user->ID, $group_leader ) ) || $current_user->ID == $uid ) {
				$additional .= ' AND visibility in(1,2,3)';
			} else {
				$additional .= ' AND visibility in(1,2)';
			}
		} else {
			$additional .= ' AND visibility = 1';
		}
		if ( $exclude != '' ) {
			$additional .= ' AND field_type not in(' . $exclude . ')';
		}
                $additional = apply_filters('pg_user_privacy_fields_visibility_qry', $additional, $uid, $gid, $group_leader, $view, $section, $exclude);
		
                $fields = $dbhandler->get_all_result( 'FIELDS', $column = '*', $where, 'results', 0, false, $sort_by = 'ordering', false, $additional );
                
                return apply_filters( 'pg_user_privacy_fields',$fields, $uid );
	}

	public function pm_get_backend_user_meta( $uid, $gid, $group_leader, $view = '', $section = '', $exclude = '' ) {
			   $dbhandler = new PM_DBhandler();
		$data             = array();
		if ( is_array( $gid ) ) {
			$gid_array = $gid;
		} else {
			$gid_array = array( $gid );}
				$additional = 'associate_group in(' . implode( ',', $gid_array ) . ')';
		$where              = array();
		if ( $section != '' ) {
			$where['associate_section'] = $section;
		}

		if ( $exclude != '' ) {
			$additional = ' AND field_type not in(' . $exclude . ')';
		}
		if ( empty( $where ) ) {
			$where = 1;
		}
		$fields = $dbhandler->get_all_result( 'FIELDS', $column = '*', $where, 'results', 0, false, $sort_by = 'ordering', false, $additional );
		return $fields;
	}

	public function profile_magic_check_is_group_leader( $userid, $gid = false ) {
			  $dbhandler = new PM_DBhandler();
		$group_leader_id = false;
		if ( $gid == false ) {
			$gids = get_user_meta( $userid, 'pm_group', true );
			$ugid = $this->pg_filter_users_group_ids( $gids );
			$gid  = $this->pg_get_primary_group_id( $ugid );
		}
		$is_group_leader   = $dbhandler->get_value( 'GROUPS', 'is_group_leader', $gid, 'id' );
		$group_leader_name = $dbhandler->get_value( 'GROUPS', 'leader_username', $gid, 'id' );
		if ( isset( $group_leader_name ) && $group_leader_name != '' && $is_group_leader != 0 ) {
			$group_leader_id = username_exists( $group_leader_name );
		}
		return $group_leader_id;
	}

	public function profile_magic_set_group_leader( $gid ) {
				$dbhandler = new PM_DBhandler();
		$is_group_leader   = $dbhandler->get_value( 'GROUPS', 'is_group_leader', $gid, 'id' );
		$group_leader_name = $dbhandler->get_value( 'GROUPS', 'leader_username', $gid, 'id' );
		if ( isset( $group_leader_name ) && $group_leader_name != '' && $is_group_leader != 0 ) {
			$userid = username_exists( $group_leader_name );
			update_user_meta( $userid, 'pm_group', $gid );
		}
	}

	public function profile_magic_get_group_icon( $group = null, $class = '', $path = '', $size = 'full' ) {
		if ( $path == '' ) :
                    $path = $this->pg_get_default_group_image_src();
			//$path = plugins_url( '../public/partials/images/default-group.png', __FILE__ );
			endif;

		if ( isset( $group ) && $group->group_icon != 0 ) :
			$image = wp_get_attachment_image( $group->group_icon, $size, true, array( 'class' => $class ) );
			else :
				$image = '<img src="' . $path . '" class="' . $class . '" />';
			endif;

			if ( isset( $group ) && ! wp_attachment_is_image( $group->group_icon ) ) {
				$image = '<img src="' . $path . '" class="' . $class . '" />';
			}
			return $image;
	}

	public function profile_magic_get_cover_image( $uid, $class = '' ) {
		$dbhandler = new PM_DBhandler();
		$imageid   = $this->profile_magic_get_user_field_value( $uid, 'pm_cover_image' );
		$path      = plugins_url( '../public/partials/images/default-cover.jpg', __FILE__ );
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_cover_image', '' );

		$admin_path = plugins_url( '../public/partials/images/admin-default-cover.jpg', __FILE__ );
		if ( isset( $imageid ) && ! empty( $imageid ) ) :

			if ( is_multisite() ) {
							$subsites = get_sites();
							$found    = false;
				foreach ( $subsites as $subsite ) {
					if ( ! $found ) {
								switch_to_blog( $subsite->blog_id );
								$image = wp_get_attachment_image( $imageid, 'full', false, array( 'class' => $class ) );
						if ( ! empty( $image ) ) {
							$found = true;
							
						}
                                                restore_current_blog();
					}
				}
			} else {
				  $image = wp_get_attachment_image( $imageid, 'full', false, array( 'class' => $class ) );
			}
			 elseif ( $avatarid != '' ) :
				 $image = wp_get_attachment_image( $avatarid, 'full', false, array( 'class' => $class ) );
			 else :
				 if ( is_super_admin( $uid ) ) {
					 $image = '<img src="' . $admin_path . '" class="' . $class . '" />';
				 } else {
					 $image = '<img src="' . $path . '" class="' . $class . '" />';
				 }

			endif;
                        return  apply_filters( 'profile_magic_filter_cover_image', $image, $uid, $imageid, $class );

	}


	public function profile_magic_get_pm_theme_name() {
		 $dirname               = array();
		$pm_theme_path          = plugin_dir_path( __FILE__ ) . '../public/partials/themes/';
		$wp_theme_dir           = get_stylesheet_directory();
		$override_pm_theme_path = $wp_theme_dir . '/profilegrid-user-profiles-groups-and-communities/themes/';
		if ( file_exists( $pm_theme_path ) ) {
			foreach ( glob( $pm_theme_path . '*', GLOB_ONLYDIR ) as $dir ) {
				  $dirname[] = basename( $dir );
			}
		}

		if ( file_exists( $override_pm_theme_path ) ) {
			foreach ( glob( $override_pm_theme_path . '*', GLOB_ONLYDIR ) as $dir2 ) {
				  $dirname[] = basename( $dir2 );
			}
		}
		return array_unique( $dirname );
	}

	public function profile_magic_get_pm_theme_path() {
		 $dirname               = array();
		$pm_theme_path          = plugin_dir_path( __FILE__ ) . '../public/partials/themes/';
		$wp_theme_dir           = get_stylesheet_directory();
		$override_pm_theme_path = $wp_theme_dir . '/profilegrid-user-profiles-groups-and-communities/themes/';
		if ( file_exists( $pm_theme_path ) ) {
			foreach ( glob( $pm_theme_path . '*', GLOB_ONLYDIR ) as $dir ) {
				  $dirname[] = plugin_dir_url( __FILE__ ) . '../public/partials/themes/' . basename( $dir );
			}
		}

		if ( file_exists( $override_pm_theme_path ) ) {
			foreach ( glob( $override_pm_theme_path . '*', GLOB_ONLYDIR ) as $dir2 ) {
				  $dirname[] = get_stylesheet_directory_uri() . '/profilegrid-user-profiles-groups-and-communities/themes/' . basename( $dir2 );
			}
		}
		return array_unique( $dirname );
	}

	public function pm_update_user_activation_code( $user_id ) {
		if ( (int) $user_id ) {
			$pass            = wp_generate_password( 10, false );
			$activation_code = md5( $pass );
			update_user_meta( $user_id, 'pm_activation_code', $activation_code );
		}
	}

	public function pm_create_user_activation_link( $user_id, $activation_code ) {
		$user_data_obj                  = new stdClass();
		$user_data_obj->user_id         = $user_id;
		$user_data_obj->activation_code = $activation_code;
		$user_data_json                 = wp_json_encode( $user_data_obj );
		$user_data_enc                  = rawurlencode( $this->pm_encrypt_decrypt_pass( 'encrypt', $user_data_json ) );
		$user_activation_link           = admin_url( 'admin-ajax.php' ) . '?action=pm_activate_user_by_email&user=' . $user_data_enc;
		$user_activation_link = add_query_arg( '_wpnonce', wp_create_nonce( 'user_activation_link_'.$user_id ), $user_activation_link );
                return $user_activation_link;
	}

	public function pm_get_display_name( $uid, $labels = false ) {
		$user = get_userdata( $uid );
		if ( isset( $user ) && $user != false ) {
			   $firstname   = $user->first_name;
			   $lastname    = $user->last_name;
			   $username    = $user->display_name;
			   $displayname = $username;
			if ( isset( $firstname ) && ( $firstname != '' ) ) {
				if ( isset( $lastname ) && $lastname != '' ) {
					$displayname = $firstname . ' ' . $lastname;
				} else {
					  $displayname = $firstname;
				}
			}

			   $displayname = apply_filters( 'profile_magic_filter_display_name', $displayname, $uid, $labels );

			   return $displayname;
		}
	}




	public function pm_five_star_review_banner() {
		?>
<div class="pm_five_star_Banner">
	
	<div class="pm_five_star_Banner_wrap">
	<p align="center"><?php esc_html_e( 'Do you like ProfileGrid? Help us  make it better…Please rate it ', 'profilegrid-user-profiles-groups-and-communities' ); ?>
		<span class="pm-star">
			<i class="fa fa-star" aria-hidden="true"></i>
			<i class="fa fa-star" aria-hidden="true"></i>
			<i class="fa fa-star" aria-hidden="true"></i>
			<i class="fa fa-star" aria-hidden="true"></i>
			<i class="fa fa-star" aria-hidden="true"></i>
		</span><?php esc_html_e( ' stars on ', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://wordpress.org/support/plugin/profilegrid-user-profiles-groups-and-communities/reviews/?rate=5#new-post"><?php esc_html_e( 'WordPress.org', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></p>
	</div>
	</div>

		 <?php
	}


	public function pm_get_user_all_threads( $uid, $limit = false, $active = false ) {
		if ( $uid ) {
			global $wpdb;
			$uid          = absint( $uid );
			$limit        = ( false === $limit ) ? false : absint( $limit );
			$pm_activator = new Profile_Magic_Activator();
			$table        = esc_sql( $pm_activator->get_db_table_name( 'MSG_THREADS' ) );
			$query        = "SELECT * FROM {$table} WHERE status = %d AND (s_id = %d OR r_id = %d)";
			$query_args   = array( 2, $uid, $uid );

			if ( $active ) {
				$query .= ' AND title IS NULL';
			}

			$additional = apply_filters( 'pm_get_user_all_threads_additional', '', $uid, $active );
			if ( is_string( $additional ) && trim( $additional ) !== '' ) {
				// Keep legacy filter support for trusted in-repo extensions without depending on PM_DBhandler query assembly.
				$query .= ' ' . trim( $additional );
			}

			$query .= ' ORDER BY timestamp DESC';

			$threads = $wpdb->get_results( $wpdb->prepare( $query, $query_args ) );
			if ( ! empty( $threads ) ) {
				$deduped_threads = array();
				$seen_pairs      = array();
				foreach ( $threads as $thread ) {
					$pair = array( (int) $thread->s_id, (int) $thread->r_id );
					sort( $pair );
					$pair_key = implode( ':', $pair );
					if ( isset( $seen_pairs[ $pair_key ] ) ) {
						continue;
					}
					$seen_pairs[ $pair_key ] = true;
					$deduped_threads[]       = $thread;
				}

				if ( false !== $limit && $limit > 0 ) {
					$deduped_threads = array_slice( $deduped_threads, 0, $limit );
				}

				return $deduped_threads;
			}
			return null;
		}
	}
        
        public function pg_check_user_can_send_message($sid, $rid)
        {
            $dbhandler = new PM_DBhandler();
            $access = $this->profile_magic_check_profile_access_permission($rid);
            $return = true;
            $current_user_id         = get_current_user_id();
            if ( $dbhandler->get_global_option_value( 'pm_enable_private_profile' ) == '1' || $access==false || $current_user_id == $rid ) {
                $return = false;
            }
            return $return;
        }

	public function pm_create_message( $sid, $rid, $content, $tid = '' ) {
		$mid = 'false';
                $check_capability = $this->pg_check_user_can_send_message($sid, $rid);
		if ( $sid != '' && $rid != '' && $check_capability == true ) {
                        
			$dbhandler = new PM_DBhandler();
                        
			$pmemail = new PM_Emails;

			$identifier   = 'MSG_CONVERSATION';
			$status       = apply_filters('pm_default_chat_status',2, $sid);
			$content      = $this->pg_sanitize_message_content( $content );
                        $tid          = $this->pg_resolve_message_thread_id( $sid, $rid, $tid );
                        if ( empty( $tid ) ) {
				return false;
			}
			$data = array(
				's_id'      => $sid,
				't_id'      => $tid,
				'content'   => $content,
				'status'    => $status,
				'timestamp' => current_time( 'mysql', true ),
			);
			
			$mid         = $dbhandler->insert_row( $identifier, $data );
			$args        = array( $mid, $sid, $rid, $content );
			$option      = 'pg_send_email_for_unread_message_' . $tid;
			$alreay_sent = $dbhandler->get_global_option_value( $option, '' );
			if ( $alreay_sent == '' ) {
				$dbhandler->update_global_option_value( $option, 0 );
			}
			
                         $send_email = $dbhandler->get_global_option_value('pm_unread_message_notification','0');
			 if($send_email=='1')
			 {
                            $pmemail->pm_send_unread_message_notification($sid,$rid);
                            //wp_schedule_single_event( time() + 3600, 'pm_send_message_notification', array( $mid, $args ) );

			 }
		}
		if ( $mid == 'false' ) {
			return false;
		} else {

			$this->pm_update_thread_time( $tid, 2 );
			// $this->pm_update_thread_status($tid,2); //RID is sent for status of thread
			return $tid;
		}

	}

	public function pm_edit_message( $rid, $mid, $content ) {
		$current_user = wp_get_current_user();
		$sid          = $current_user->ID;
                $dbhandler    = new PM_DBhandler();
                $identifier   = 'MSG_CONVERSATION';
                $orignal_msg = $dbhandler->get_row($identifier,$mid,'m_id');
		$content      = $this->pg_sanitize_message_content( $content );
                
                if($sid!=$orignal_msg->s_id)
                {
                    return false;
                }
		if ( $sid != '' && $rid != '' ) {
			//$tid          = $this->fetch_or_create_thread( $sid, $rid );
			$tid = $orignal_msg->t_id;
			$data = array( 'content' => $content );
			$data = apply_filters("pm_edit_msg_restriction", $data);
			
			$mid = $dbhandler->update_row( $identifier, 'm_id', $mid, $data );

		}
		if ( $mid == 'false' ) {
			return false;
		} else {
			$this->pm_update_thread_time( $tid, 2 );
			return $tid;
		}
                
	}

	public function pm_update_thread_time( $tid, $status = '' ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'MSG_THREADS';
		$title      = array();
		$data       = array(
			'timestamp' => current_time( 'mysql', true ),
			'title'     => $title,
		);
		if ( $status != '' ) {
			$data['status'] = $status;
		}
		$data    = $this->sanitize_request( $data, $identifier );
		$updated = $dbhandler->update_row( $identifier, 't_id', $tid, $data );

	}

	public function pm_update_thread_status( $tid, $status ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'MSG_THREADS';
		$data       = array( 'status' => $status );
		
		$data       = $this->sanitize_request( $data, $identifier );
		$updated    = $dbhandler->update_row( $identifier, 't_id', $tid, $data );

	}



	public function fetch_or_create_thread( $sid, $rid ) {
		$dbhandler   = new PM_DBhandler();
		 $identifier = 'MSG_THREADS';

		$tid = $this->get_thread_id( $sid, $rid );
		if ( ! empty( $tid ) ) {
                        do_action('pg_update_thread_desc_add_members',$tid,$sid,$rid);
		} else {

			$thread_desc                               = array();
			$thread_desc[ "$sid" ]['typing_timestamp'] = 0;
			$thread_desc[ "$sid" ]['delete_mid']       = 0;
			$thread_desc[ "$sid" ]['typing_status']    = 'nottyping';
			$thread_desc[ "$rid" ]['typing_timestamp'] = 0;
			$thread_desc[ "$rid" ]['delete_mid']       = 0;
			$thread_desc[ "$rid" ]['typing_status']    = 'nottyping';
			$value                                     = apply_filters('pm_thread_desc', $thread_desc, $sid, $rid );
			$data                                      = array(
				's_id'        => $sid,
				'r_id'        => $rid,
				'thread_desc' => maybe_serialize($value),
				'timestamp'   => current_time( 'mysql', true ),
			);
			$data = $this->sanitize_request( $data, $identifier );
			$tid  = $dbhandler->insert_row( $identifier, $data );
			
		}
		return $tid;
	}

	public function is_thread_exsist( $sid, $rid ) {
		$sid = absint( $sid );
		$rid = absint( $rid );
		if ( $sid > 0 && $rid > 0 ) {
			$thread = $this->pg_get_best_thread_for_pair( $sid, $rid );
			if ( ! empty( $thread ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function get_thread_id( $sid, $rid ) {
		$sid = absint( $sid );
		$rid = absint( $rid );
		if ( $sid > 0 && $rid > 0 ) {
			$thread = $this->pg_get_best_thread_for_pair( $sid, $rid );
			if ( isset( $thread->t_id ) ) {
				return (int) $thread->t_id;
			}
			return false;
		} else {
			return false;
		}

	}

	private function pg_get_best_thread_for_pair( $sid, $rid ) {
		global $wpdb;
		$pm_activator = new Profile_Magic_Activator();
		$threads_table = esc_sql( $pm_activator->get_db_table_name( 'MSG_THREADS' ) );
		$messages_table = esc_sql( $pm_activator->get_db_table_name( 'MSG_CONVERSATION' ) );

		$query = $wpdb->prepare(
			"SELECT t.*, COUNT(m.m_id) AS message_count
			FROM {$threads_table} t
			LEFT JOIN {$messages_table} m ON m.t_id = t.t_id
			WHERE t.s_id IN (%d,%d) AND t.r_id IN (%d,%d)
			GROUP BY t.t_id
			ORDER BY
				CASE WHEN COUNT(m.m_id) > 0 THEN 0 ELSE 1 END,
				CASE WHEN t.status = 2 THEN 0 ELSE 1 END,
				t.timestamp DESC
			LIMIT 1",
			$sid,
			$rid,
			$sid,
			$rid
		);

		$thread = $wpdb->get_row( $query );
		if ( ! empty( $thread ) ) {
			return $thread;
		}

		return false;
	}

	private function pg_sanitize_message_content( $content ) {
		$content      = wp_unslash( (string) $content );
		$allowed_html = $this->pg_allowed_html_wp_kses();

		foreach ( $allowed_html as $tag => $attributes ) {
			if ( ! is_array( $attributes ) ) {
				continue;
			}

			foreach ( array_keys( $attributes ) as $attribute ) {
				if ( 0 === strpos( $attribute, 'on' ) ) {
					unset( $allowed_html[ $tag ][ $attribute ] );
				}
			}
		}

		return wp_kses( $content, $allowed_html );
	}

	public function pm_get_unread_message_summary( $uid ) {
		$summary = array(
			'count'      => 0,
			'latest'     => 0,
			'latest_tid' => 0,
			'latest_rid' => 0,
		);

		$uid = absint( $uid );
		if ( $uid === 0 ) {
			return $summary;
		}

		global $wpdb;
		$pm_activator      = new Profile_Magic_Activator();
		$conversation_table = esc_sql( $pm_activator->get_db_table_name( 'MSG_CONVERSATION' ) );
		$thread_table      = esc_sql( $pm_activator->get_db_table_name( 'MSG_THREADS' ) );

		$thread_active_status = 2;
		$message_unread_status = 2;

		$query = $wpdb->prepare(
			"SELECT COUNT(mc.m_id) AS unread_count, MAX(mc.timestamp) AS latest_ts
			FROM {$conversation_table} mc
			INNER JOIN {$thread_table} mt ON mc.t_id = mt.t_id
			WHERE mt.status = %d
				AND ( mt.s_id = %d OR mt.r_id = %d )
				AND mc.s_id != %d
				AND mc.status = %d",
			$thread_active_status,
			$uid,
			$uid,
			$uid,
			$message_unread_status
		);

		$result = $wpdb->get_row( $query );
		if ( $result ) {
			$summary['count']  = isset( $result->unread_count ) ? (int) $result->unread_count : 0;
			$summary['latest'] = ( isset( $result->latest_ts ) && ! empty( $result->latest_ts ) ) ? strtotime( $result->latest_ts ) : 0;
		}

		if ( $summary['count'] > 0 ) {
			$latest_unread = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT mc.t_id, mc.s_id
					FROM {$conversation_table} mc
					INNER JOIN {$thread_table} mt ON mc.t_id = mt.t_id
					WHERE mt.status = %d
						AND ( mt.s_id = %d OR mt.r_id = %d )
						AND mc.s_id != %d
						AND mc.status = %d
					ORDER BY mc.timestamp DESC, mc.m_id DESC
					LIMIT 1",
					$thread_active_status,
					$uid,
					$uid,
					$uid,
					$message_unread_status
				)
			);

			if ( $latest_unread ) {
				$summary['latest_tid'] = isset( $latest_unread->t_id ) ? (int) $latest_unread->t_id : 0;
				$summary['latest_rid'] = isset( $latest_unread->s_id ) ? (int) $latest_unread->s_id : 0;
			}
		}

		return $summary;
	}
	public function get_unread_msg_count( $tid ) {
		$tid = absint( $tid );
		if ( $tid === 0 ) {
			return 0;
		}

		global $wpdb;
		$pm_activator = new Profile_Magic_Activator();
		$table        = esc_sql( $pm_activator->get_db_table_name( 'MSG_CONVERSATION' ) );
		$uid          = get_current_user_id();
		$unread_status = 2;

		$query = $wpdb->prepare(
			"SELECT COUNT(m_id) FROM {$table} WHERE t_id = %d AND s_id != %d AND status = %d",
			$tid,
			$uid,
			$unread_status
		);

		$count = (int) $wpdb->get_var( $query );
		return $count;

	}


	public function get_message_of_thread( $tid, $limit = false, $offset = 0, $descending = true, $search = '' ) {
		global $wpdb;
		$dbhandler   = new PM_DBhandler();
		$uid         = get_current_user_id();
		$tid         = absint( $tid );
		$offset      = absint( $offset );
		$limit       = ( false === $limit ) ? false : absint( $limit );
		$thread_desc = maybe_unserialize( $dbhandler->get_value( 'MSG_THREADS', 'thread_desc', $tid, 't_id' ) );
		if ( isset( $thread_desc ) && isset( $thread_desc[ "$uid" ]['delete_mid'] ) ) {
			$delete_mid = absint( $thread_desc[ "$uid" ]['delete_mid'] );
		} else {
			$delete_mid = 0;
		}
		$pm_activator   = new Profile_Magic_Activator();
		$table          = esc_sql( $pm_activator->get_db_table_name( 'MSG_CONVERSATION' ) );
		$order          = $descending ? 'DESC' : 'ASC';
		$query          = "SELECT * FROM {$table} WHERE t_id = %d";
		$query_args     = array( $tid );

		if ( '' === $search ) {
			$query      .= ' AND m_id > %d';
			$query_args[] = $delete_mid;
		} else {
			$query      .= ' AND content LIKE %s';
			$query_args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$query .= " ORDER BY timestamp {$order}";
		if ( false !== $limit && $limit > 0 ) {
			$query      .= ' LIMIT %d OFFSET %d';
			$query_args[] = $limit;
			$query_args[] = $offset;
		}

		$message = $wpdb->get_results( $wpdb->prepare( $query, $query_args ) );
		if ( isset( $message ) && ! empty( $message ) ) :
			if ( count( $message ) > 0 ) {
				return $message;
			}
		endif;
	}
	public function update_message_status_to_unread( $tid ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'MSG_CONVERSATION';
		$uid        = wp_get_current_user()->ID;
		$where      = 1;
		$status     = 1;
		$additional = " t_id = $tid AND s_id NOT IN ($uid) AND status =$status ";
		$messages   = $dbhandler->get_all_result( $identifier, $column = 'm_id', $where, 'results', 0, 1, $sort_by = 'timestamp', true, $additional );
		$data       = array( 'status' => '2' );
		$updated    = array();
		$data       = $this->sanitize_request( $data, $identifier );
		if ( is_array( $messages ) && count( $messages ) > 0 ) {
			foreach ( $messages as $message ) {
				$updated[ $message->m_id ] = $dbhandler->update_row( $identifier, 'm_id', $message->m_id, $data );

			}
		}
		$option      = 'pg_send_email_for_unread_message_' . $tid;
		$alreay_sent = $dbhandler->get_global_option_value( $option, '' );
		if ( $alreay_sent == '' ) {
			$dbhandler->update_global_option_value( $option, 0 );
		}
		return $updated;
	}
	public function update_message_status_to_read( $tid ) {
		$tid = absint( $tid );
		if ( $tid === 0 ) {
			return;
		}

		global $wpdb;
		$pm_activator  = new Profile_Magic_Activator();
		$table         = esc_sql( $pm_activator->get_db_table_name( 'MSG_CONVERSATION' ) );
		$uid           = get_current_user_id();
		$unread_status = 2;
		$read_status   = 1;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET status = %d WHERE t_id = %d AND s_id != %d AND status = %d",
				$read_status,
				$tid,
				$uid,
				$unread_status
			)
		);

		$option = 'pg_send_email_for_unread_message_' . $tid;
		delete_option( $option );

	}

	public function delete_thread( $tid, $uid, $mid ) {
		$dbhandler                           = new PM_DBhandler();
		$identifier                          = 'MSG_THREADS';
		$thread                              = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		$thread_desc                         = maybe_unserialize( $thread->thread_desc );
		$thread_desc[ "$uid" ]['delete_mid'] = $mid;
		$value                               = maybe_serialize( $thread_desc );
		if ( ! empty( $thread->title ) ) {
			$data = array(
				'thread_desc' => $value,
				'status'      => 1,
			);
		} else {
			$data = array(
				'thread_desc' => $value,
				'title'       => "$uid",
			);
		}

		$return = $dbhandler->update_row( $identifier, 't_id', $tid, $data );
		return $return;
	}

	public function update_typing_timestamp( $tid, $activity ) {
		$dbhandler                                 = new PM_DBhandler();
		$identifier                                = 'MSG_THREADS';
		$uid                                       = $this->get_other_uid_of_thread( $tid );
		$thread                                    = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		$thread_desc                               = maybe_unserialize( $thread->thread_desc );
		$thread_desc[ "$uid" ]['typing_timestamp'] = current_time( 'mysql', true );
		$thread_desc[ "$uid" ]['typing_status']    = $activity;
		$value                                     = maybe_serialize( $thread_desc );
		$data                                      = array( 'thread_desc' => $value );
		$return                                    = $dbhandler->update_row( 'MSG_THREADS', 't_id', $tid, $data );

		return $return;
	}

	public function get_typing_timestamp( $tid ) {
		$dbhandler   = new PM_DBhandler();
		$identifier  = 'MSG_THREADS';
		$uid         = wp_get_current_user()->ID;
		$thread      = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		$thread_desc = maybe_unserialize( $thread->thread_desc );
		return $thread_desc[ "$uid" ]['typing_timestamp'];
	}

	public function get_typing_status( $tid ) {
		 $dbhandler  = new PM_DBhandler();
		$identifier  = 'MSG_THREADS';
		$uid         = wp_get_current_user()->ID;
		$thread      = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		$thread_desc = maybe_unserialize( $thread->thread_desc );
		return $thread_desc[ "$uid" ]['typing_status'];
	}

	public function get_other_uid_of_thread( $tid ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'MSG_THREADS';
		$uid        = wp_get_current_user()->ID;
		$thread     = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
		if ( $thread->s_id == $uid ) {
			$other_uid = $thread->r_id;
		} else {
			$other_uid = $thread->s_id;
		}
		return $other_uid;
	}

	public function pm_filter_deleted_threads( $threads ) {
		 $filtered_threads = array();
		$current_user      = wp_get_current_user();
		$uid               = $current_user->ID;
		$last_mid          = '';
		if ( ! empty( $threads ) && ( is_array( $threads ) || is_object( $threads ) ) ) :
			foreach ( $threads as $thread ) {
				$thread_desc = maybe_unserialize( $thread->thread_desc );
				$delete_mid  = ( isset( $thread_desc[ "$uid" ]['delete_mid'] ) ) ? $thread_desc[ "$uid" ]['delete_mid'] : 0;
				$message     = $this->get_message_of_thread( $thread->t_id, '1' );
				if ( isset( $message[0]->m_id ) ) {
					$last_mid = $message[0]->m_id;
				}

				if ( $delete_mid < $last_mid ) {
					  $filtered_threads[] = $thread;
				}
			}
		endif;
		return $filtered_threads;
	}

	public function pm_filter_deleted_message( $messages, $tid ) {
		$dbhandler         = new PM_DBhandler();
		$uid               = wp_get_current_user()->ID;
		$filtered_messages = array();

		$thread = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );

		$thread_desc = maybe_unserialize( $thread->thread_desc );
		if ( isset( $thread_desc ) && isset( $thread_desc[ "$uid" ]['delete_mid'] ) ) {
			   $delete_mid = $thread_desc[ "$uid" ]['delete_mid'];
		} else {
				   $delete_mid = 0;
		}
		foreach ( $messages as $message ) {

			   $message_mid = $message->m_id;
			if ( $message_mid > $delete_mid ) {
				  $filtered_messages[] = $message;
			}
		}
				return $filtered_messages;
	}

	public function get_data_of_thread( $tid ) {
		$dbhandler  = new PM_DBhandler();
		$identifier = 'MSG_THREADS';
		$where      = 1;
		$additional = " t_id in ($tid) ";
		$thread     = $dbhandler->get_all_result( $identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true, $additional );
		return $thread;
	}

	public function pm_get_user_online_status( $uid ) {
		 // return get_user_meta( $uid, 'pm_login_status', true );
		return $this->is_user_online( $uid );
	}

	public function pm_get_profile_slug_by_id( $uid ) {
		 $slug = $uid;
		$slug  = apply_filters( 'profile_magic_get_filter_slug_by_id', $slug, $uid );
		return $slug;
	}

	public function pm_get_uid_from_profile_slug( $slug ) {
		 $uid = $slug;
		$uid  = apply_filters( 'profile_magic_get_filter_uid_by_slug', $uid, $slug );
		return $uid;
	}
        
        public function pm_get_group_slug_by_id( $gid ) {
		$slug = $gid;
		$slug  = apply_filters( 'profile_magic_get_group_filter_slug_by_id', $slug, $gid );
		return $slug;
	}

        public function pm_get_gid_from_group_slug( $slug ) {
		 $uid = $slug;
		$uid  = apply_filters( 'profile_magic_get_filter_gid_by_group_slug', $uid, $slug );
		return $uid;
	}

	public function pg_get_enabled_payment_processors() {
		$processors = array(
			'paypal' => __( 'PayPal', 'profilegrid-user-profiles-groups-and-communities' ),
		);

		if ( $this->pg_is_payment_processor_enabled( 'stripe' ) ) {
			$processors['stripe'] = __( 'Stripe', 'profilegrid-user-profiles-groups-and-communities' );
		}

		if ( $this->pg_is_payment_processor_enabled( 'mycred' ) ) {
			$processors['mycred'] = __( 'myCred', 'profilegrid-user-profiles-groups-and-communities' );
		}

		if ( $this->pg_is_payment_processor_enabled( 'terawallet' ) ) {
			$processors['terawallet'] = __( 'TeraWallet', 'profilegrid-user-profiles-groups-and-communities' );
		}

		/**
		 * Allow extensions to modify the available payment processors.
		 *
		 * @since 5.9.7.0
		 *
		 * @param array $processors Associative array of slug => label.
		 */
		return apply_filters( 'pg_enabled_payment_processors', $processors );
	}

	public function pg_get_payment_processor_label( $processor ) {
		$processors = $this->pg_get_enabled_payment_processors();
		if ( isset( $processors[ $processor ] ) ) {
			return $processors[ $processor ];
		}

		return ucfirst( $processor );
	}

	private function pg_is_payment_processor_enabled( $processor ) {
		switch ( $processor ) {
			case 'stripe':
				return class_exists( 'Profilegrid_Stripe_Payment' ) || class_exists( 'Profilegrid_Stripe_Payment_Public' );
			case 'mycred':
				return class_exists( 'Profilegrid_Mycred' ) || class_exists( 'Profilegrid_Mycred_Public' );
			case 'terawallet':
				return class_exists( 'Profilegrid_Credit' ) || class_exists( 'Profilegrid_Credit_Public' );
			case 'paypal':
				return true;
			default:
				/**
				 * Allow third-party processors to control their availability.
				 *
				 * @since 5.9.7.0
				 */
				return apply_filters( 'pg_is_payment_processor_enabled', false, $processor );
		}
	}

	public function pg_get_group_membership_payments( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'paged'     => 1,
			'per_page'  => 20,
			'status'    => '',
			'processor' => '',
			'group'     => 0,
			'user_id'   => 0,
			'search'    => '',
			'orderby'   => 'posted_date',
			'order'     => 'DESC',
			'allowed_processors' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$allowed_processors = array();
		if ( is_array( $args['allowed_processors'] ) && ! empty( $args['allowed_processors'] ) ) {
			$allowed_processors = array_map( 'sanitize_key', $args['allowed_processors'] );
		}

		$table = $wpdb->prefix . 'promag_paypal_log';

		$order_map = array(
			'posted_date' => 'posted_date',
			'amount'      => 'amount',
			'status'      => 'status',
			'processor'   => 'pay_processor',
			'group'       => 'gid',
			'user'        => 'uid',
		);

		$order_column = isset( $order_map[ $args['orderby'] ] ) ? $order_map[ $args['orderby'] ] : 'posted_date';
		$order        = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';

		$where_clauses = array();
		$sql_params    = array();

		if ( ! empty( $args['status'] ) ) {
			$where_clauses[] = 'status = %s';
			$sql_params[]    = $args['status'];
		}

		if ( ! empty( $args['processor'] ) ) {
			$processor = sanitize_key( $args['processor'] );
			$where_clauses[] = 'pay_processor = %s';
			$sql_params[]    = $processor;
		}

		if ( ! empty( $args['group'] ) ) {
			$where_clauses[] = 'gid = %d';
			$sql_params[]    = absint( $args['group'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where_clauses[] = 'uid = %d';
			$sql_params[]    = absint( $args['user_id'] );
		}

		if ( ! empty( $args['search'] ) ) {
			$user_ids = $this->pg_get_payment_user_ids_by_keyword( $args['search'] );
			if ( empty( $user_ids ) ) {
				$where_clauses[] = '1=0';
			} else {
				$placeholders    = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
				$where_clauses[] = "uid IN ({$placeholders})";
				foreach ( $user_ids as $search_uid ) {
					$sql_params[] = absint( $search_uid );
				}
			}
		}

		if ( ! empty( $allowed_processors ) ) {
			$placeholders    = implode( ',', array_fill( 0, count( $allowed_processors ), '%s' ) );
			$where_clauses[] = "pay_processor IN ({$placeholders})";
			foreach ( $allowed_processors as $allowed_processor ) {
				$sql_params[] = $allowed_processor;
			}
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
		}

		$per_page = max( 1, absint( $args['per_page'] ) );
		$paged    = max( 1, absint( $args['paged'] ) );
		$offset   = ( $paged - 1 ) * $per_page;

		// Base: fetch from PAYPAL_LOG table
		$data_sql  = "SELECT * FROM {$table} {$where_sql} ORDER BY {$order_column} {$order}";
		$prepared_data_sql  = ! empty( $sql_params ) ? $wpdb->prepare( $data_sql, $sql_params ) : $data_sql;
		$paypal_items = $wpdb->get_results( $prepared_data_sql );

		$all_items = (array) $paypal_items;

		// Merge in other processors (WooCommerce orders) when requested and plugin is active
		// TeraWallet integration marks orders with post meta 'pg_credit'
		$allow_terawallet = empty( $allowed_processors ) || in_array( 'terawallet', $allowed_processors, true );
		if ( ( empty( $args['processor'] ) || 'terawallet' === $args['processor'] ) && $allow_terawallet && class_exists( 'Profilegrid_Credit_Public' ) && class_exists( 'WC_Order' ) ) {
			$meta_key = 'pg_credit';
			$order_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $meta_key, '1' ) );
			if ( ! empty( $order_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( absint( $order_id ) );
					if ( ! $order ) {
						continue;
					}
					$ord_obj = new stdClass();
					$ord_obj->id = 'order_' . $order->get_id();
					$ord_obj->txn_id = $order->get_transaction_id() ? $order->get_transaction_id() : 'order_' . $order->get_id();
					$ord_obj->log = maybe_serialize( array( 'order_id' => $order->get_id() ) );
					$ord_obj->posted_date = $order->get_date_created()
						? $order->get_date_created()->date( 'Y-m-d H:i:s' )
						: ( $order->get_date_paid() ? $order->get_date_paid()->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' ) );
					$ord_obj->gid = 0; // No reliable mapping from order -> group available here
					$ord_obj->status = $order->get_status();
					$ord_obj->invoice = $order->get_id();
					$ord_obj->amount = (float) $order->get_total();
					$ord_obj->currency = $order->get_currency();
					$ord_obj->pay_processor = 'terawallet';
					$ord_obj->uid = (int) $order->get_user_id();
					$all_items[] = $ord_obj;
				}
			}
		}

		// myCred: include WooCommerce orders with payment method indicating mycred or orders marked by myCred integration
		$allow_mycred = empty( $allowed_processors ) || in_array( 'mycred', $allowed_processors, true );
		if ( ( empty( $args['processor'] ) || 'mycred' === $args['processor'] ) && $allow_mycred && class_exists( 'Profilegrid_Mycred_Public' ) && class_exists( 'WC_Order' ) ) {
			// Try to find orders where payment method mentions mycred or has meta set by myCred
			$mycred_like      = '%' . $wpdb->esc_like( 'mycred' ) . '%';
			$mycred_order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s AND pm.meta_value LIKE %s WHERE p.post_type = %s",
					'_payment_method',
					$mycred_like,
					'shop_order'
				)
			);
			// also include orders where custom meta 'pg_mycred' exists
			$extra_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s", 'pg_mycred' ) );
			$mycred_order_ids = array_unique( array_merge( (array) $mycred_order_ids, (array) $extra_ids ) );
			if ( ! empty( $mycred_order_ids ) ) {
				foreach ( $mycred_order_ids as $order_id ) {
					$order = wc_get_order( absint( $order_id ) );
					if ( ! $order ) {
						continue;
					}
					$ord_obj = new stdClass();
					$ord_obj->id = 'order_' . $order->get_id();
					$ord_obj->txn_id = $order->get_transaction_id() ? $order->get_transaction_id() : 'order_' . $order->get_id();
					$ord_obj->log = maybe_serialize( array( 'order_id' => $order->get_id() ) );
					$ord_obj->posted_date = $order->get_date_created()
						? $order->get_date_created()->date( 'Y-m-d H:i:s' )
						: ( $order->get_date_paid() ? $order->get_date_paid()->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' ) );
					$ord_obj->gid = 0;
					$ord_obj->status = $order->get_status();
					$ord_obj->invoice = $order->get_id();
					$ord_obj->amount = (float) $order->get_total();
					$ord_obj->currency = $order->get_currency();
					$ord_obj->pay_processor = 'mycred';
					$ord_obj->uid = (int) $order->get_user_id();
					$all_items[] = $ord_obj;
				}
			}
		}

		// Normalize and sort the merged results in PHP
		$map_status = function( $status ) {
			// Normalize common statuses
			if ( in_array( $status, array( 'completed', 'wc-completed' ), true ) ) {
				return 'completed';
			}
			if ( in_array( $status, array( 'refunded', 'wc-refunded' ), true ) ) {
				return 'refunded';
			}
			if ( in_array( $status, array( 'pending', 'wc-pending' ), true ) ) {
				return 'pending';
			}
			return $status;
		};

		foreach ( $all_items as $idx => $it ) {
			// ensure object
			if ( is_array( $it ) ) {
				$it = (object) $it;
			}
			$all_items[ $idx ]->status = isset( $it->status ) ? $map_status( $it->status ) : '';
			$all_items[ $idx ]->pay_processor = isset( $it->pay_processor ) ? $it->pay_processor : '';
			$all_items[ $idx ]->posted_date = isset( $it->posted_date ) ? $it->posted_date : ( isset( $it->posted ) ? $it->posted : '' );
		}

		// Sort
		usort( $all_items, function ( $a, $b ) use ( $order_column, $order ) {
			$a_col = isset( $a->{$order_column} ) ? $a->{$order_column} : '';
			$b_col = isset( $b->{$order_column} ) ? $b->{$order_column} : '';

			if ( $a_col == $b_col ) {
				return 0;
			}

			if ( 'ASC' === strtoupper( $order ) ) {
				return ( $a_col < $b_col ) ? -1 : 1;
			}

			return ( $a_col > $b_col ) ? -1 : 1;
		} );

		$total = count( $all_items );
		$items = array_slice( $all_items, $offset, $per_page );

		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	private function pg_get_payment_user_ids_by_keyword( $keyword ) {
		global $wpdb;

		$keyword = trim( $keyword );
		if ( '' === $keyword ) {
			return array();
		}

		$like = '%' . $wpdb->esc_like( $keyword ) . '%';

		$sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s OR user_login LIKE %s OR user_email LIKE %s",
			$like,
			$like,
			$like
		);

		return $wpdb->get_col( $sql );
	}

	public function pg_get_payment_distinct_values( $column ) {
		global $wpdb;

		$map = array(
			'status'        => 'status',
			'pay_processor' => 'pay_processor',
		);

		if ( ! isset( $map[ $column ] ) ) {
			return array();
		}

		$field = $map[ $column ];
		$table = $wpdb->prefix . 'promag_paypal_log';

		if ( 'pay_processor' === $column ) {
			$db_values    = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT pay_processor FROM {$table} WHERE pay_processor != %s ORDER BY pay_processor ASC",
					''
				)
			);
			$enabled_list = array_keys( $this->pg_get_enabled_payment_processors() );
			$results      = array_unique( array_merge( (array) $db_values, (array) $enabled_list ) );
			return array_filter( array_map( 'sanitize_text_field', (array) $results ) );
		}

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT {$field} FROM {$table} WHERE {$field} != %s ORDER BY {$field} ASC",
				''
			)
		);

		return array_filter( array_map( 'sanitize_text_field', (array) $results ) );
	}

	/**
	 * Insert a payment log entry if it does not already exist.
	 *
	 * @since 5.9.7.0
	 *
	 * @param int    $uid       User ID.
	 * @param int    $gid       Group ID.
	 * @param string $processor Payment processor slug.
	 * @param string $status    Payment status.
	 * @param float  $amount    Amount.
	 * @param string $currency  Currency code.
	 * @param string $txn_id    Transaction id.
	 * @param string $invoice   Invoice id/reference.
	 * @param array  $log       Extra log payload.
	 */
	public function pg_insert_payment_log_entry( $uid, $gid, $processor, $status = 'completed', $amount = null, $currency = '', $txn_id = '', $invoice = '', $log = array() ) {
		$dbhandler = new PM_DBhandler();
		global $wpdb;

		$table = $wpdb->prefix . 'promag_paypal_log';

		// Prevent duplicates by transaction id.
		if ( ! empty( $txn_id ) ) {
			$existing = $dbhandler->pm_count( 'PAYPAL_LOG', array( 'txn_id' => $txn_id ), '*' );
			if ( $existing > 0 ) {
				return;
			}
		}

		// Prevent duplicates by user/group/status/processor if already logged.
		$existing_row = $dbhandler->get_all_result(
			'PAYPAL_LOG',
			'*',
			array(
				'uid'          => (int) $uid,
				'gid'          => (int) $gid,
				'status'       => sanitize_text_field( $status ),
				'pay_processor'=> sanitize_text_field( $processor ),
			),
			'results',
			0,
			1
		);
		if ( ! empty( $existing_row ) ) {
			return;
		}

		if ( null === $amount ) {
			$amount = $this->profile_magic_check_paid_group( $gid );
		}
		$amount   = is_numeric( $amount ) ? (float) $amount : 0;
		$currency = $currency ? $currency : $dbhandler->get_global_option_value( 'pm_paypal_currency', 'USD' );

		if ( empty( $txn_id ) ) {
			$txn_id = 'pg_' . sanitize_key( $processor ) . '_' . (int) $uid . '_' . (int) $gid . '_' . time();
		}

		if ( empty( $invoice ) ) {
			$invoice_gid = get_user_meta( $uid, 'pm_invoice_' . $gid, true );
			$invoice     = $invoice_gid ? $invoice_gid : get_user_meta( $uid, 'pm_invoice', true );
		}
		if ( empty( $invoice ) ) {
			$invoice = $txn_id;
		}

		$log_payload = empty( $log ) ? array( 'source' => 'pg_auto_log' ) : $log;
		$log_payload = maybe_serialize( $log_payload );

		$dbhandler->insert_row(
			'PAYPAL_LOG',
			array(
				'txn_id'        => sanitize_text_field( $txn_id ),
				'log'           => $log_payload,
				'posted_date'   => current_time( 'mysql' ),
				'gid'           => (int) $gid,
				'status'        => sanitize_text_field( $status ),
				'invoice'       => sanitize_text_field( $invoice ),
				'amount'        => $amount,
				'currency'      => sanitize_text_field( $currency ),
				'pay_processor' => sanitize_key( $processor ),
				'pay_type'      => 'one_time',
				'uid'           => (int) $uid,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%d' )
		);
	}

	public function pm_get_user_profile_url( $uid ) {
		$slug        = $this->pm_get_profile_slug_by_id( $uid );
		$profile_url = $this->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
		$profile_url = add_query_arg( 'uid', $slug, $profile_url );
                $profile_url  = apply_filters( 'profile_magic_get_safe_profile_url',$profile_url, $uid); 
                return esc_url( $profile_url );
	}

	public function pm_get_user_edit_profile_url( $uid, $gid ) {
		$slug        = $this->pm_get_profile_slug_by_id( $uid );
		$profile_url = $this->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
		$profile_url = add_query_arg( 'user_id', $uid, $profile_url );
		$profile_url = add_query_arg( 'gid', $gid, $profile_url );
		$profile_url = add_query_arg( 'rd', 'members', $profile_url );

		return esc_url( $profile_url );
	}

	public function pm_is_eligable_for_edit_profile( $uid, $gid, $user_id ) {
		$dbhandler    = new PM_DBhandler();
		$row          = $dbhandler->get_row( 'GROUPS', $gid );
		$group_leader = username_exists( $row->leader_username );
		if ( is_user_logged_in() ) {
			$filter_user_id = $this->pm_get_profile_slug_by_id( $uid );
			if ( $user_id == $filter_user_id ) {
				return true;
			} elseif ( $group_leader == $uid || is_super_admin() ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	public function pm_user_ip() {
		$ip = '127.0.0.1';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// check ip from share internet
			$ip = filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP);
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// to check ip is pass from proxy
			$ip = filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP);
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		}

		return apply_filters( 'pm_user_ip', $ip );
	}

	public function pg_get_strings_between_tags( $string, $tagname ) {
		$pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
		preg_match( $pattern, $string, $matches );
		if ( ! empty( $matches ) ) {
			return $matches[1];
		} else {
			return $string;
		}
	}

	public function pg_get_default_avtar_src() {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_avatar', '' );
		if ( $avatarid == '' ) {
			if ( ! is_admin() ) {
				 $pm_avatar = $path . '/partials/images/default-user.png';
			} else {
				 $pm_avatar = $path . '../admin/partials/images/default-user.png';
			}

			
		} else {
			$pm_avatar = wp_get_attachment_url( $avatarid );
		}

		 return $pm_avatar;
	}

	public function pg_get_default_cover_image_src() {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_cover_image', '' );
		if ( $avatarid == '' ) {
			if ( ! is_admin() ) {

				 $pm_avatar = $path . '/partials/images/default-cover.jpg';
			} else {
				 $pm_avatar = $path . '../admin/partials/images/default-cover.jpg';
			}
                        
		} else {
			$pm_avatar = wp_get_attachment_url( $avatarid );
		}

		 return $pm_avatar;
	}
        
        public function pg_get_default_group_image_src() {
		$path      = plugin_dir_url( __FILE__ );
		$dbhandler = new PM_DBhandler();
		$avatarid  = $dbhandler->get_global_option_value( 'pm_default_group_image', '' );
		if ( $avatarid == '' ) {
                        if(is_admin())
                        {
                            $pm_avatar = $path . '../admin/partials/images/pg-icon.png';
                        }
                        else
                        {
                            $pm_avatar = $path . '../public/partials/images/default-group.png';
                        }
			
                        
		} else {
			$pm_avatar = wp_get_attachment_url( $avatarid );
		}

		 return $pm_avatar;
	}

	public function profile_magic_check_profile_access_permission( $profile_id ) {
		$current_user_id         = get_current_user_id();
			$access_level        = $this->profile_magic_get_user_field_value( $profile_id, 'pm_profile_privacy' );
			$profile_user_groups = $this->profile_magic_get_user_field_value( $profile_id, 'pm_group' );
			$profile_user_group  = $this->pg_filter_users_group_ids( $profile_user_groups );

			$current_user_groups = $this->profile_magic_get_user_field_value( $current_user_id, 'pm_group' );
			$current_user_group  = $this->pg_filter_users_group_ids( $current_user_groups );

		if ( ! is_array( $profile_user_group ) ) {
			$profile_user_group = array( $profile_user_group );}
		if ( ! is_array( $current_user_group ) ) {
			$current_user_group = array( $current_user_group );}
			$is_group_member = array_intersect( $profile_user_group, $current_user_group );
			$pmfriends       = new PM_Friends_Functions();
			$pmrequests      = new PM_request();
			$access          = false;
			$is_my_friend    = $pmfriends->profile_magic_is_my_friends( $profile_id, $current_user_id );
		switch ( $access_level ) {
			case '1':
				$access = true;
				break;
			case '2':
				if ( is_user_logged_in() && isset( $is_my_friend ) && ! empty( $is_my_friend ) ) {
					$access = true;
				}
				break;
			case '3':
				if ( is_user_logged_in() && is_array( $is_group_member ) && ! empty( $is_group_member ) ) {
					$access = true;
				}
				break;
			case '4':
				if ( ( is_user_logged_in() && is_array( $is_group_member ) && ! empty( $is_group_member ) ) || ( is_user_logged_in() && isset( $is_my_friend ) && ! empty( $is_my_friend ) ) ) {
					$access = true;
				}
				break;
			case '5':
				if ( $current_user_id == $profile_id ) {
					$access = true;
				}
				break;
			default:
					$access = true;
				break;
		}
		if ( $current_user_id == $profile_id ) {
			$access = true;
		}
                    return apply_filters('pm_check_profile_access_permission',$access,$profile_id);
	}

	public function pg_auto_create_default_fields( $gid, $sid ) {
		$dbhandler    = new PM_DBhandler();
		$lastrow      = $dbhandler->get_all_result( 'FIELDS', 'field_id', 1, 'var', 0, 1, 'field_id', 'DESC' );
		$field_option = 'a:15:{s:17:"place_holder_text";s:0:"";s:19:"css_class_attribute";s:0:"";s:14:"maximum_length";s:0:"";s:13:"default_value";s:0:"";s:12:"first_option";s:0:"";s:21:"dropdown_option_value";s:0:"";s:18:"radio_option_value";a:1:{i:0;s:0:"";}s:14:"paragraph_text";s:0:"";s:7:"columns";s:0:"";s:4:"rows";s:0:"";s:18:"term_and_condition";s:0:"";s:18:"allowed_file_types";s:0:"";s:12:"heading_text";s:0:"";s:11:"heading_tag";s:2:"h1";s:5:"price";s:0:"";}';

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Username',
			'field_type'          => 'user_name',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'user_login',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'First Name',
			'field_type'          => 'first_name',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'first_name',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Last Name',
			'field_type'          => 'last_name',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'last_name',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Email',
			'field_type'          => 'user_email',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'user_email',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Password',
			'field_type'          => 'user_pass',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'ordering'            => $ordering,
			'field_key'           => 'user_pass',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Confirm Password',
			'field_type'          => 'confirm_pass',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'ordering'            => $ordering,
			'field_key'           => 'confirm_pass',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Website',
			'field_type'          => 'user_url',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'user_url',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		$ordering   = $lastrow + 1;
		$field_data = array(
			'field_name'          => 'Biographical Info',
			'field_type'          => 'description',
			'field_options'       => $field_option,
			'field_icon'          => 0,
			'associate_group'     => $gid,
			'associate_section'   => $sid,
			'show_in_signup_form' => 1,
			'is_required'         => 1,
			'display_on_profile'  => 1,
			'visibility'          => 1,
			'ordering'            => $ordering,
			'field_key'           => 'description',
		);
		$field_arg  = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
		$lastrow    = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

		return $lastrow;
	}

	public function pg_get_edit_blog_post_link( $id = 0, $context = 'display' ) {
		if ( ! $post = get_post( $id ) ) {
				return;
		}

		if ( 'revision' === $post->post_type ) {
			$action = '';
		} elseif ( 'display' == $context ) {
			$action = '&amp;action=edit';
		} else {
			$action = '&action=edit';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return;
		}

		if ( $post_type_object->_edit_link ) {
			$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
		} else {
			$link = '';
		}

		/**
		 * Filters the post edit link.
		 *
		 * @since 2.3.0
		 *
		 * @param string $link    The edit link.
		 * @param int    $post_id Post ID.
		 * @param string $context The link context. If set to 'display' then ampersands
		 *                        are encoded.
		 */
		return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
	}

	public function pg_auto_create_default_email_template( $gid ) {
		 $dbhandler    = new PM_DBhandler();
		$group_options = array();
		$group         = $dbhandler->get_row( 'GROUPS', $gid );
		if ( $group->group_options != '' ) {
			$group_options = maybe_unserialize( $group->group_options );
		}
		$group_options['enable_notification'] = '1';
		$body                                 = 'Dear {{first_name}} {{last_name}},<br /><br />You are now a member of {{group_name}} Group on {{site_name}}.<br /><br />Kind Regards.';
		$tmpl_data                            = array(
			'tmpl_name'     => 'On Joining Group',
			'email_subject' => 'Welcome to your new Group!',
			'email_body'    => $body,
		);
		$tmpl_arg                             = array( '%s', '%s', '%s' );
		$group_options['on_registration']     = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                              = "Dear {{first_name}} {{last_name}},<br /><br />Admin has activated your user account on {{site_name}}. You can now login and access your member's area. <br /><br />Kind Regards.";
		$tmpl_data                         = array(
			'tmpl_name'     => 'User Account Activated',
			'email_subject' => 'User Account Activated',
			'email_body'    => $body,
		);
		$tmpl_arg                          = array( '%s', '%s', '%s' );
		$group_options['on_user_activate'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                = 'Dear {{first_name}} {{last_name}},<br /><br />Admin has deactivated your user account on {{site_name}}. For now, you cannot login or access your member area.<br /><br />Kind Regards.';
		$tmpl_data                           = array(
			'tmpl_name'     => 'User Account Deactivated',
			'email_subject' => 'User Account Deactivated',
			'email_body'    => $body,
		);
		$tmpl_arg                            = array( '%s', '%s', '%s' );
		$group_options['on_user_deactivate'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                = 'Dear {{first_name}} {{last_name}},<br /><br />Password for your user account on {{site_name}} was successfully changed. If you did not changed it, please contact the Manager immediately.<br /><br />Kind Regards.';
		$tmpl_data                           = array(
			'tmpl_name'     => 'Password Successfully Changed',
			'email_subject' => 'Password Successfully Changed',
			'email_body'    => $body,
		);
		$tmpl_arg                            = array( '%s', '%s', '%s' );
		$group_options['on_password_change'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                = 'Dear {{first_name}} {{last_name}},<br /><br />Your user account on {{site_name}} associated with username {{user_login}} has been deleted successfully.<br /><br />Kind Regards.';
		$tmpl_data                           = array(
			'tmpl_name'     => 'User Account Deleted',
			'email_subject' => 'User Account Deleted',
			'email_body'    => $body,
		);
		$tmpl_arg                            = array( '%s', '%s', '%s' );
		$group_options['on_account_deleted'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                   = 'Dear {{first_name}} {{last_name}},<br /><br />Your blog post titled {{post_name}} was approved and published successfully.<br /><br />Kind Regards.';
		$tmpl_data                              = array(
			'tmpl_name'     => 'New User Blog Post',
			'email_subject' => 'Your Post was Published Successfully!',
			'email_body'    => $body,
		);
		$tmpl_arg                               = array( '%s', '%s', '%s' );
		$group_options['on_published_new_post'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                 = 'Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you have been made {{group_admin_label}} of {{group_name}} on {{site_name}}. You can manage your group by logging in and accessing settings on the Group page.<br /><br />Regards.';
		$tmpl_data                            = array(
			'tmpl_name'     => 'Group Manager assignment',
			'email_subject' => 'Congratulation! You have been made {{group_admin_label}} of {{group_name}}',
			'email_body'    => $body,
		);
		$tmpl_arg                             = array( '%s', '%s', '%s' );
		$group_options['on_admin_assignment'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                              = 'Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you are no longer {{group_admin_label}} of {{group_name}} on {{site_name}}. Consequently, you will not be able to access and manage group settings.<br /><br />Regards.';
		$tmpl_data                         = array(
			'tmpl_name'     => 'Group Manager Removal',
			'email_subject' => 'You are no longer {{group_admin_label}} of {{group_name}}',
			'email_body'    => $body,
		);
		$tmpl_arg                          = array( '%s', '%s', '%s' );
		$group_options['on_admin_removal'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                     = 'Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that a {{group_admin_label}} on {{site_name}} has reset your password. Your new password is:<br /><br />{{user_pass}}<br /><br />You can now login using your new password.<br /><br />Regards.';
		$tmpl_data                                = array(
			'tmpl_name'     => 'Password Reset by Group Manager',
			'email_subject' => 'Your password was reset successfully',
			'email_body'    => $body,
		);
		$tmpl_arg                                 = array( '%s', '%s', '%s' );
		$group_options['on_admin_reset_password'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                               = 'Dear {{first_name}} {{last_name}},<br /><br />Sorry, Your membership request for {{group_name}} was not approved.<br /><br />Kind Regards.';
		$tmpl_data                          = array(
			'tmpl_name'     => 'On Request Denial',
			'email_subject' => 'Membership request not approved',
			'email_body'    => $body,
		);
		$tmpl_arg                           = array( '%s', '%s', '%s' );
		$group_options['on_request_denied'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                     = 'Hi {{first_name}} {{last_name}},<br /><br />Your membership for the group {{group_name}} has been terminated on {{site_name}}. You no longer will have access to private areas of the group.<br /><br />Regards.';
		$tmpl_data                                = array(
			'tmpl_name'     => 'Membership Terminated',
			'email_subject' => 'Membership Terminated',
			'email_body'    => $body,
		);
		$tmpl_arg                                 = array( '%s', '%s', '%s' );
		$group_options['on_membership_terminate'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                   = 'Dear {{group_admin_label}},<br /><br />You have received a new membership request for your {{group_name}} Group. You can approve or reject the request by visiting the Group management area.<br /><br />Kind Regards.';
		$tmpl_data                              = array(
			'tmpl_name'     => 'Membership Request',
			'email_subject' => 'You have a new membership request',
			'email_body'    => $body,
		);
		$tmpl_arg                               = array( '%s', '%s', '%s' );
		$group_options['on_membership_request'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$group_data = maybe_serialize( $group_options );
		$result     = $dbhandler->update_row( 'GROUPS', 'id', $gid, array( 'group_options' => $group_data ), array( '%s' ), '%d' );
		$dbhandler->update_global_option_value( 'pg_email_templates_created', '1' );
		$dbhandler->update_global_option_value( 'pg_email_templates_created_upgrade', '1' );
	}

	public function pg_auto_create_default_template_during_update() {
		$dbhandler     = new PM_DBhandler();
		$group_options = array();

		$body                              = 'Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you are no longer {{group_admin_label}} of {{group_name}} on {{site_name}}. Consequently, you will not be able to access and manage group settings.<br /><br />Regards.';
		$tmpl_data                         = array(
			'tmpl_name'     => 'Group Manager Removal',
			'email_subject' => 'You are no longer {{group_admin_label}} of {{group_name}}',
			'email_body'    => $body,
		);
		$tmpl_arg                          = array( '%s', '%s', '%s' );
		$group_options['on_admin_removal'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                     = 'Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that a {{group_admin_label}} on {{site_name}} has reset your password. Your new password is:<br /><br />{{user_pass}}<br /><br />You can now login using your new password.<br /><br />Regards.';
		$tmpl_data                                = array(
			'tmpl_name'     => 'Password Reset by Group Manager',
			'email_subject' => 'Your password was reset successfully',
			'email_body'    => $body,
		);
		$tmpl_arg                                 = array( '%s', '%s', '%s' );
		$group_options['on_admin_reset_password'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                               = 'Dear {{first_name}} {{last_name}},<br /><br />Sorry, Your membership request for {{group_name}} was not approved.<br /><br />Kind Regards.';
		$tmpl_data                          = array(
			'tmpl_name'     => 'On Request Denial',
			'email_subject' => 'Membership request not approved',
			'email_body'    => $body,
		);
		$tmpl_arg                           = array( '%s', '%s', '%s' );
		$group_options['on_request_denied'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$body                                   = 'Dear {{group_admin_label}},<br /><br />You have received a new membership request for your {{group_name}} Group. You can approve or reject the request by visiting the Group management area.<br /><br />Kind Regards.';
		$tmpl_data                              = array(
			'tmpl_name'     => 'Membership Request',
			'email_subject' => 'You have a new membership request',
			'email_body'    => $body,
		);
		$tmpl_arg                               = array( '%s', '%s', '%s' );
		$group_options['on_membership_request'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );
		$dbhandler->update_global_option_value( 'pg_email_templates_created_upgrade', '1' );

	}

	public function pg_auto_create_new_default_template_during_update() {
		$dbhandler     = new PM_DBhandler();
		$group_options = array();

		$body                                     = 'Hi {{first_name}} {{last_name}},<br /><br />Your membership for the group {{group_name}} has been terminated on {{site_name}}. You no longer will have access to private areas of the group.<br /><br />Regards.';
		$tmpl_data                                = array(
			'tmpl_name'     => 'Membership Terminated',
			'email_subject' => 'Membership Terminated',
			'email_body'    => $body,
		);
		$tmpl_arg                                 = array( '%s', '%s', '%s' );
		$group_options['on_membership_terminate'] = $dbhandler->insert_row( 'EMAIL_TMPL', $tmpl_data, $tmpl_arg );

		$dbhandler->update_global_option_value( 'pg_email_templates_created_new_upgrade', '1' );

	}


	public function pg_check_email_template_if_used_in_any_group( $tid ) {
		$dbhandler = new PM_DBhandler();
		$groups    = $dbhandler->get_all_result( 'GROUPS', '*', 1, 'results' );
		foreach ( $groups as $group ) {
			if ( $group->group_options != '' ) {
				$options = maybe_unserialize( $group->group_options );
				if ( isset( $options['on_registration'] ) && $options['on_registration'] == $tid ) {
					return $group->group_name;
				}
				if ( isset( $options['on_user_activate'] ) && $options['on_user_activate'] == $tid ) {
					return $group->group_name;
				}
				if ( isset( $options['on_user_deactivate'] ) && $options['on_user_deactivate'] == $tid ) {
					return $group->group_name;
				}
				if ( isset( $options['on_password_change'] ) && $options['on_password_change'] == $tid ) {
					return $group->group_name;
				}
				if ( isset( $options['on_account_deleted'] ) && $options['on_account_deleted'] == $tid ) {
					return $group->group_name;
				}
				if ( isset( $options['on_published_new_post'] ) && $options['on_published_new_post'] == $tid ) {
					return $group->group_name;
				}
				unset( $options );
			}
		}
		return false;
		// print_r(count($groups));die;
	}

	public function is_pg_dashboard_page() {
		$page     = filter_input( INPUT_GET, 'page' );
		$plugin   = get_current_screen();
		$pg_pages = array(
			'pm_general_settings',
			'pm_security_settings',
			'pm_user_settings',
			'pm_email_settings',
			'pm_third_party_settings',
			'pm_import_options',
			'pm_export_options',
			'pm_seo_settings',
			'pm_upload_settings',
			'pm_friend_settings',
			'pm_message_settings',
			'pm_payment_settings',
			'pm_tools',
			'pm_export_users',
			'pm_import_users',
			'pm_blog_settings',
			'pm_manage_groups',
			'pm_add_group',
			'pm_profile_fields',
			'pm_add_field',
			'pm_add_section',
			'pm_user_manager',
			'pm_user_edit',
			'pm_profile_view',
			'pm_add_email_template',
			'pm_shortcodes',
			'pm_settings',
			'pm_extensions',
			'pm_email_templates',
			'pm_email_preview',
			'pm_analytics',
			'pm_membership',
			'pm_content_restrictions',
			'pm_bbpress_settings',
			'pm_group_fields_settings',
			'pm_display_name_settings',
			'pm_front_end_groups_settings',
			'pm_geolocation_settings',
			'pm_group_photos_settings',
			'pm_group_wall_settings',
			'pm_mailchimp',
			'pm_add_mailchimp_list',
			'pm_mailchimp_settings',
			'pm_uid_changer_settings',
			'pm_woocommerce_settings',
			'pm_social_connect_settings',
		);
		if ( in_array( $page, $pg_pages ) ) {
			return true;
		} else {
			return true;
		}

	}

	public function pm_to_array( $groups ) {
		if ( is_array( $groups ) ) {
			$group_ids = array();
			foreach ( $groups as $key => $group ) {
				$group_ids[ $key ] = $group['id'];
			}
		}

		return $group_ids;
	}

	public function pm_get_group_admin_label( $gid ) {
		$dbhandler   = new PM_DBhandler();
		$group       = $dbhandler->get_row( 'GROUPS', $gid );
		$admin_label = 'Group Manager';
		if ( $group->group_options != '' ) {
			$group_options = maybe_unserialize( $group->group_options );
			if ( isset( $group_options['admin_label'] ) && trim( $group_options['admin_label'] ) != '' ) {
				$admin_label = $group_options['admin_label'];
			}
		}

		return apply_filters( 'pm_filter_group_admin_label', $admin_label, $gid );
	}

	public function pm_get_all_group_blogs( $gid, $pagenum = 1, $limit = 10, $sort_by = 'title-asc', $search_in = 'post_title', $search = '' ) {
		$dbhandler  = new PM_DBhandler();
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'pm_group',
				'value'   => sprintf( ':"%s";', $gid ),
				'compare' => 'like',
			),
		);
		$users      = $dbhandler->pm_get_all_users( '', $meta_query );
		$author_ids = array( '0' );
		$path       = plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );
		foreach ( $users as $user ) {
			array_push( $author_ids, $user->ID );
		}
		$offset = ( $pagenum - 1 ) * $limit;

		$args = array(
			'post_type'      => 'profilegrid_blogs',
			'posts_per_page' => -1,
			'post_status'    => 'publish,pending,draft',
			'author__in'     => $author_ids,
		);

		$args = $this->generate_blog_args( $args, $sort_by, $search_in, $search, $gid );

		// print_r($args);die;
		$posts = get_posts( $args );

		$total_posts = count( $posts );
		$offset      = ( $pagenum - 1 ) * $limit;

		$args['posts_per_page'] = $limit;
		$args['offset']         = $offset;
		$posts_array            = get_posts( $args );

		$num_of_pages = ceil( $total_posts / $limit );

		$pagination = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );
		?>
<input type="hidden" name="pg_blogs_uids" id="pg_blogs_uids" value="<?php echo esc_attr( maybe_serialize( $author_ids ) ); ?>" />
<input type="hidden" name="pg_blog_select_type" id="pg_blog_select_type" value="this_page" />
<input type="hidden" name="pg_total_blog_post" id="pg_total_blog_post" value="<?php echo esc_attr( $total_posts ); ?>" />
<input type="hidden" name="pg_total_blog_post_sigle_page" id="pg_total_blog_post_sigle_page" value="<?php echo esc_attr( $limit ); ?>" />
		<?php if ( ! empty( $posts_array ) ) { ?> 
<table class="pg-group-members pg-blog-setting">
	<tbody>
		<tr>
			<th><input class="pg-blog-checked-all" type="checkbox" value="1" name="" onclick="pg_checked_all_blogs(this)"></th>
			<th>&nbsp;</th>
			<th><?php esc_html_e( 'Title', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th><?php esc_html_e( 'Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th><?php esc_html_e( 'Last Modified', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th><?php esc_html_e( 'Author', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
		<!--********************************-->
		
			<?php foreach ( $posts_array as $post ) : ?>
				<?php
				$pm_admin_note_content = trim( get_post_meta( $post->ID, 'pm_admin_note_content', true ) );
				$is_user_active        = $this->pm_is_user_active( $post->post_author );
				?>
		<tr>
			<td><input class="pg-blog-checked <?php
				if ( $is_user_active == false ) {
					echo 'inactive';
				} else {
					echo 'active';
				}
				?>" type="checkbox" value="<?php echo esc_attr( $post->ID ); ?>" name="pg_edit_blog_id[]" onclick="pm_show_hide_batch_operation('blog')"></td>
			<td>
				<div class="pg-member-avatar">
					<?php if ( $post->post_status === 'publish' ) { ?> 
					<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank">
					<?php } ?>
						<?php
						if ( has_post_thumbnail( $post->ID ) ) {
								 echo get_the_post_thumbnail( $post->ID, array( 26, 26 ), array( 'class' => 'avatar avatar-96 photo' ) );
						} else {
							?>
						<img src="<?php echo esc_url( $path ); ?>" width="26" height="26" alt="<?php the_title(); ?>" class="avatar avatar-96 photo" />
								<?php
						}
						?>
						<?php
						if ( $post->post_status === 'publish' ) {
							?>
							</a> <?php } ?>

				</div>
			</td>
			<td class="pg-blog-title">
				<?php
				if ( $post->post_status === 'publish' ) {
					?>
				<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank"><?php echo esc_html( $post->post_title ); ?></a>
									<?php
				} else {
										  echo '<div class="pg-pending-blog-title">' . esc_html( $post->post_title ) . '</div>'; }
				?>
										  </td>

			<td><?php echo esc_html( $post->post_status ); ?></td>
			<td><?php echo wp_kses_post( $this->pm_change_date_in_different_format( $post->post_modified ) ); ?></td>
			<td><?php echo wp_kses_post( $this->pm_get_author_name_and_link( $post->post_author ) ); ?></td>
			<td>
				<?php
				if ( $pm_admin_note_content != '' ) {
					?>
				<a onclick="pg_edit_blog_popup('blog','add_admin_note','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><span class="pg-update-message"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
	<path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
	<path d="M0 0h24v24H0z" fill="none"/>
</svg>
</span></a>
					<?php
				} else {
					echo '';}
				?>
				</td>            
						<td>     
							<div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
								<div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
								<ul class="pg-dropdown-menu">
									<li><a onclick="pg_edit_blog_popup('blog','change_status','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Change Status', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<li><a onclick="pg_edit_blog_popup('blog','access_control','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Access Control', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<li><a onclick="pg_edit_blog_popup('blog','edit','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Edit', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<li><a onclick="pg_edit_blog_popup('blog','add_admin_note','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Add Note', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<?php if ( $is_user_active ) : ?>
									<li><a onclick="pg_edit_blog_popup('blog','message','<?php echo esc_attr( $post->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<?php else : ?>
									<li><a class="pg-setting-disabled"><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<?php endif; ?>
								</ul>
							</div>
						</td>
		</tr>
		<?php endforeach; ?>



	</tbody>
</table>

		<?php } elseif ( ! empty( $search ) ) { ?> 
	 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'No blog posts matches your search.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
				 <?php
		} else {
			?>
			 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'No blog posts have been written by members of this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
																   <?php
		}
		?>
		<?php
		if (isset($pagination)){
			echo '<div class="pm-blog-pagination">' . wp_kses_post( $pagination ) . '</div>';
		}else{
			echo '<div class="pm-blog-pagination"></div>';
		}

	}

	public function generate_blog_args( $args, $sort_by, $search_in, $search, $gid ) {
		$dbhandler = new PM_DBhandler();
		switch ( $sort_by ) {
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'modified_desc':
				$args['orderby'] = 'modified';
				$args['order']   = 'DESC';
				break;
			case 'modified_asc':
				$args['orderby'] = 'modified';
				$args['order']   = 'ASC';
				break;
			case 'pending_post':
				$args['orderby']     = 'date';
				$args['order']       = 'DESC';
				$args['post_status'] = 'pending';
				break;
			default:
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;

		}

		if ( trim( $search ) != '' ) :
			switch ( $search_in ) {
				case 'post_title':
					$args['s'] = $search;
					break;
				case 'post_tag':
                                   $args['tax_query'] = array(
                                            array(
                                                'taxonomy' => 'blog_tag',
                                                'field' => 'slug',
                                                'terms' => $search
                                            )
                                        );
					break;
				case 'author_name':
					$meta_query = array(
						'search'         => $search,
						'search_columns' => array( 'user_login', 'display_name' ),
					);
					$users      = $dbhandler->pm_get_all_users( $search, $meta_query );

					$author_ids = array();
					foreach ( $users as $user ) {
						array_push( $author_ids, $user->ID );
					}

					if ( empty( $author_ids ) ) {
							$author_ids[0] = '0';
					}

					$args['author__in'] = $author_ids;
					break;
				default:
					$args['s'] = $search;
					break;
			}
		endif;

		return $args;
	}

	public function pm_change_date_in_different_format( $date, $tab = '' ) {
		if ( empty( $date ) ) {
			return '';
		}
		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return '';
		}
		$timezone     = wp_timezone();
		$current_year = wp_date( 'Y', time(), $timezone );
		$actual_year  = wp_date( 'Y', $timestamp, $timezone );
		switch ( $tab ) {
			case 'request':
				if ( $current_year > $actual_year ) {
					$date = wp_date( 'h:iA jS M Y', $timestamp, $timezone );
				} else {
					$date = wp_date( 'h:iA jS M', $timestamp, $timezone );
				}
				break;
			default:
				if ( $current_year > $actual_year ) {
					$date = wp_date( 'jS M Y', $timestamp, $timezone );
				} else {
					$date = wp_date( 'jS M', $timestamp, $timezone );
				}
				break;
		}

		return $date;
	}

	public function pm_is_user_active( $uid ) {
		 $is_active = get_user_meta( $uid, 'rm_user_status', true );
		if ( $is_active == '1' ) {
			return false;
		} else {
			return true;
		}
	}

	public function pm_get_author_name_and_link( $uid ) {
		$user      = get_user_by( 'ID', $uid );
		$name      = $user->display_name;
		$url       = $this->pm_get_user_profile_url( $uid );
		$is_active = $this->pm_is_user_active( $uid );
		if ( $is_active ) {
			return '<a href="' . $url . '" target="_blank">' . $name . '</a>';

		} else {
			return '<span title="' . esc_attr__( 'Author is suspended', 'profilegrid-user-profiles-groups-and-communities' ) . '">' . $name . '</span>';
		}
	}

	public function pm_get_all_users_from_group( $gid, $pagenum = 1, $limit = 10, $sort_by = 'first_name_asc', $search_in = 'user_login', $search = '' ) {
		$dbhandler = new PM_DBhandler();
		$offset    = ( $pagenum - 1 ) * $limit;
		if ( empty( $search_in ) ) {
			$search_in = 'user_login';
		}
		$get       = array( 'gid' => $gid );
		if ( trim( $search ) != '' ) {
			$get['match_field'] = $search_in;
			$get['field_value'] = $search;
			$search             = '';
			$is_search          = 1;
		}

		switch ( $sort_by ) {
			case 'name_asc':
				$sortby = 'display_name';
				$order  = 'ASC';
				break;
			case 'name_desc':
				$sortby = 'display_name';
				$order  = 'DESC';
				break;
			case 'latest_first':
				$sortby = 'registered';
				$order  = 'DESC';
				break;
			case 'oldest_first':
				$sortby = 'registered';
				$order  = 'ASC';
				break;
			case 'suspended':
				$sortby        = 'registered';
				$order         = 'DESC';
				$get['status'] = '1';
				break;
			case 'first_name_asc':
				$sortby = 'first_name';
				$order  = 'ASC';
				break;
			case 'first_name_desc':
				$sortby = 'first_name';
				$order  = 'DESC';
				break;
			case 'last_name_asc':
				$sortby = 'last_name';
				$order  = 'ASC';
				break;
			case 'last_name_desc':
				$sortby = 'last_name';
				$order  = 'DESC';
				break;
			default:
				$sortby = 'display_name';
				$order  = 'ASC';
				break;

		}
		$current_user = wp_get_current_user();

		$meta_query_array = $this->pm_get_user_meta_query( $get );

		$user_query   = $dbhandler->pm_get_all_users_ajax( $search, $meta_query_array, '', $offset, $limit, $order, $sortby, array( $current_user->ID ) );
		$total_users  = $user_query->get_total();
		$users        = $user_query->get_results();
		$num_of_pages = ceil( $total_users / $limit );
		$pagination   = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );

		?>
		<?php if ( ! empty( $users ) ) { ?>
<input type="hidden" name="pg_member_select_type" id="pg_member_select_type" value="this_page" />
<input type="hidden" name="pg_total_group_members" id="pg_total_group_members" value="<?php echo esc_attr( $total_users ); ?>" />
<input type="hidden" name="pg_total_group_members_sigle_page" id="pg_total_group_members_sigle_page" value="<?php echo esc_attr( $limit ); ?>" />
 <table class="pg-group-members">
	<tbody>
		<tr>
			<th><input class="pg-member-checked-all" type="checkbox"   value="1" name="" onclick="pg_checked_all_member(this)"></th>
			<th>&nbsp;</th>
			<th class="pg-group-member-name"><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th class="pg-group-member-name"><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th>&nbsp;</th>
			<th><?php esc_html_e( 'Joined on', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th> 
		</tr>
		<!--********************************-->
		
			<?php foreach ( $users as $user ) : ?>
		<tr>
				<?php $user_status = get_user_meta( $user->ID, 'rm_user_status', true ); ?>
			<td><input class="pg-member-checked <?php
				if ( $user_status == '1' ) {
					echo 'inactive';
				} else {
					echo 'active';
				}
				?>" type="checkbox"   value="<?php echo esc_attr( $user->ID ); ?>" name=""onclick="pm_show_hide_batch_operation('member')"></td>
			<td>
				<div class="pg-member-avatar">
					<a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank">
						<?php echo get_avatar( $user->user_email, 26, '', false, array( 'force_display' => true ) ); ?>
					</a>
				</div>
			</td>
			<td class="pg-group-member-name"><a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php echo esc_html( $user->first_name ); ?></a></td>
			<td class="pg-group-member-name"><a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php echo esc_html( $user->last_name ); ?></a></td>
			
			<td><?php $login_status = ( $this->pm_get_user_online_status( $user->ID ) == 1 ? 'pm-online' : 'pm-offline' ); ?> <span class="pm-friend-status <?php echo esc_attr( $login_status ); ?>"></span></td>
			<td><?php echo wp_kses_post( $this->pg_get_group_joining_date( $gid, $user->ID ) ); ?></td>
			<td><a class="pm-remove" onclick="pg_edit_blog_popup('member','remove_user','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Remove','profilegrid-user-profiles-groups-and-communities' ); ?></a></td>
				<?php
				$user_status = get_user_meta( $user->ID, 'rm_user_status', true );
				if ( $user_status == '1' ) :
					?>
			<td><a onclick="pg_activate_user('<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Activate','profilegrid-user-profiles-groups-and-communities' ); ?></a></td>
				 <?php else : ?>
			<td><a onclick="pg_edit_blog_popup('member','deactivate_user','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Suspend','profilegrid-user-profiles-groups-and-communities' ); ?></a></td>
			 <?php endif; ?>
			
						<td>     
							<div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
								<div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
								<ul class="pg-dropdown-menu">
									
									<?php $is_user_active = $this->pm_is_user_active( $user->ID ); if ( $is_user_active ) : ?>
                                                                        <?php if (current_user_can( 'edit_users' ) ){ ?>
									 <li><a onclick="pg_edit_blog_popup('member','reset_password','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Reset Password', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                                                                        <?php } ?>
                                                                         <li><a onclick="pg_edit_blog_popup('member','message','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<li><a href="<?php echo esc_url( $this->pm_get_user_edit_profile_url( $user->ID, $gid ) ); ?>"><?php esc_html_e( 'Edit Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<?php else : ?>
                                                                        <?php if (current_user_can( 'edit_users' ) ){ ?>
									<li> <a class="pg-setting-disabled"><?php esc_html_e( 'Reset Password', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									 <?php } ?>
                                                                        <li><a class="pg-setting-disabled"><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									<li><a class="pg-setting-disabled"><?php esc_html_e( 'Edit Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
									  
									<?php endif; ?>
									 <?php do_action( 'pm_cog_option', $user->ID, $gid ); ?>
								</ul>
							</div>
						</td>
		</tr>
		<?php endforeach; ?>



	</tbody>
</table>
			<?php

		} elseif ( ! empty( $is_search ) ) {
			?>
	 
	 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'No user matches your search.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			<?php
		} else {
			?>
			 
	 <div class='pg-alert-warning pg-alert-info'><?php printf( wp_kses_post( __( 'There are no members in this group. You can start inviting new members by clicking <i>Add</i> Button.', 'profilegrid-user-profiles-groups-and-communities' ) ) ); ?></div>
			<?php
		}

		?>
		<?php
		if (isset($pagination)){
			echo '<div class="pm-member-pagination">' . wp_kses_post( $pagination ) . '</div>';
		} else {
			echo '<div class="pm-member-pagination"></div>';
		}

	}


	public function pm_get_all_users_from_group_grid_view( $gid, $pagenum = 1, $limit = 10, $sort_by = 'first_name_asc', $search_in = 'user_login', $search = '', $profile_magic = 'profilegrid-user-profiles-groups-and-communities', $pg_version = '5.1.3' ) {
		$dbhandler     = new PM_DBhandler();
		$pmhtmlcreator = new PM_HTML_Creator( $profile_magic, $pg_version );
		$leaders       = array();
                $row = $dbhandler->get_row( 'GROUPS', $gid );
                $limit = $dbhandler->get_global_option_value('pm_number_of_users_on_group_page','10'); // number of rows in page
		if ( $row->is_group_leader != 0 ) {
			$leaders = $this->pg_get_group_leaders( $gid );
		}
		if ( $leaders == '' ) {
			$leaders = array();
		}

		$offset = ( $pagenum - 1 ) * $limit;
		if ( empty( $search_in ) ) {
			$search_in = 'user_login';
		}
		$get    = array(
			'gid'    => $gid,
			'status' => '0',
		);
		if ( trim( $search ) != '' ) {
			$get['match_field'] = $search_in;
			$get['field_value'] = $search;
			$search             = '';
			$is_search          = 1;
		}

		switch ( $sort_by ) {
			case 'name_asc':
				$sortby = 'display_name';
				$order  = 'ASC';
				break;
			case 'name_desc':
				$sortby = 'display_name';
				$order  = 'DESC';
				break;
			case 'latest_first':
				$sortby = 'registered';
				$order  = 'DESC';
				break;
			case 'oldest_first':
				$sortby = 'registered';
				$order  = 'ASC';
				break;
			case 'suspended':
				$sortby        = 'registered';
				$order         = 'DESC';
				$get['status'] = '1';
				break;
			case 'first_name_asc':
				$sortby = 'first_name';
				$order  = 'ASC';
				break;
			case 'first_name_desc':
				$sortby = 'first_name';
				$order  = 'DESC';
				break;
			case 'last_name_asc':
				$sortby = 'last_name';
				$order  = 'ASC';
				break;
			case 'last_name_desc':
				$sortby = 'last_name';
				$order  = 'DESC';
				break;
			default:
				$sortby = 'display_name';
				$order  = 'ASC';
				break;

		}
		$current_user     = wp_get_current_user();
		$meta_query_array = $this->pm_get_user_meta_query( $get );

		$hide_users   = $this->pm_get_hide_users_array();
		$user_query   = $dbhandler->pm_get_all_users_ajax( $search, $meta_query_array, '', $offset, $limit, $order, $sortby, $hide_users );
		$total_users  = $user_query->get_total();
		$users        = $user_query->get_results();
		$num_of_pages = ceil( $total_users / $limit );
		$pagination   = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );

		?>
		<?php if ( ! empty( $users ) ) { ?>
	 <input type="hidden" name="pg-groupid" id="pg-groupid" value="<?php echo esc_attr( $gid ); ?>" />
			<input type="hidden" id="pg-gid" name="pg-gid"  value="<?php echo esc_attr( $gid ); ?>" />
<input type="hidden" name="pg_member_select_type_grid" id="pg_member_select_type_grid" value="this_page" />
<input type="hidden" name="pg_total_group_members_grid" id="pg_total_group_members_grid" value="<?php echo esc_attr( $total_users ); ?>" />
<input type="hidden" name="pg_total_group_members_sigle_page_grid" id="pg_total_group_members_sigle_page_grid" value="<?php echo esc_attr( $limit ); ?>" />
 
		
			<?php
			foreach ( $users as $user ) :
				$pmhtmlcreator->get_group_page_fields_html( $user->ID, $gid, $leaders, 150, array( 'class' => 'user-profile-image' ) );

				 endforeach;
			?>


			<?php

		} elseif ( ! empty( $is_search ) ) {
			?>
	 
	 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'No user matches your search.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			<?php
		} else {
			?>
			 
	 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'There are no members in this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			<?php
		}

		?>
		<?php
		echo '<div class="pm_clear"></div>';
        if(!empty($pagination)){
			echo '<div class="pm-member-pagination-grid">' . wp_kses_post( $pagination ) . '</div>';
        }else{
            echo '<div class="pm-member-pagination-grid"></div>';    
        }
	}

	public function generate_members_args( $args, $sort_by, $search_in, $search, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$get        =
		$meta_query = $this->pm_get_user_meta_query( $get );
		switch ( $sort_by ) {
			case 'name_asc':
				$args['orderby'] = 'login';
				$args['order']   = 'ASC';
				break;
			case 'name_desc':
				$args['orderby'] = 'login';
				$args['order']   = 'DESC';
				break;
			case 'latest_first':
				$args['orderby'] = 'registered';
				$args['order']   = 'DESC';
				break;
			case 'oldest_first':
				$args['orderby'] = 'registered';
				$args['order']   = 'ASC';
				break;
			case 'suspended':
				$args['orderby']     = 'registered';
				$args['order']       = 'DESC';
				$args['post_status'] = 'pending';
				break;
			default:
				$args['orderby'] = 'login';
				$args['order']   = 'ASC';
				break;

		}

		if ( trim( $search ) != '' ) :
			switch ( $search_in ) {
				case 'post_title':
					$args['s'] = $search;
					break;
				case 'post_tag':
					$args['tag'] = $search;
					break;
				case 'author_name':
					$meta_query = array(
						'relation' => 'OR',
						array(
							'key'     => 'first_name',
							'value'   => $search,
							'compare' => 'LIKE',
						),
						array(
							'key'     => 'last_name',
							'value'   => $search,
							'compare' => 'LIKE',
						),
					);
					$users      = $dbhandler->pm_get_all_users( $search, $meta_query );
					$author_ids = array();
					foreach ( $users as $user ) {
						array_push( $author_ids, $user->ID );
					}
					if ( isset( $args['author__in'] ) && is_array( $args['author__in'] ) ) {
						$args['author__in'] = array_intersect( $author_ids, $args['author__in'] );
					} else {
						$args['author__in'] = $author_ids;
					}
					break;
				default:
					$args['s'] = $search;
					break;
			}
		endif;

		return $args;
	}

	public function pg_get_user_groups_badge_slider( $uid ) {
		$dbhandler = new PM_DBhandler();

		$user_groups = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$gid_array   = $this->pg_filter_users_group_ids( $user_groups );
		if ( ! empty( $gid_array ) ) {
			if ( count( $gid_array ) == 1 ) {
				$class = 'pm-single-group-badge';
			} else {
				$class = '';}
			echo '<ul class="' . esc_attr( $class ) . '">';
			$gid_array = array_reverse( $gid_array );
			$i         = 0;
			foreach ( $gid_array as $gid ) {
				if ( $i >= 4 ) {
					continue;}
				$group_page_url  = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
				$group_page_link = $group_page_url;
				$groupinfo       = $dbhandler->get_row( 'GROUPS', $gid );
				if ( ! empty( $groupinfo ) ) {
					?>
				<li>
					<a href='<?php echo esc_url( $group_page_link ); ?>' title="<?php echo esc_attr( $groupinfo->group_name ); ?>" >
						<?php echo wp_kses_post( $this->profile_magic_get_group_icon( $groupinfo, 'pm-group-badge' ) ); ?>
					</a> 
				</li>
					<?php
				}
				$i++;
			}
			echo '</ul>';
		}
	}

	public function pg_leave_group( $uid, $gid ) {
		$dbhandler   = new PM_DBhandler();
		$pm_emails   = new PM_Emails();
		$user_group  = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$user_groups = $this->pg_filter_users_group_ids( $user_group );
		$is_leader   = $this->pg_check_in_single_group_is_user_group_leader( $uid, $gid );
		if ( $is_leader ) {
			$row                 = $dbhandler->get_row( 'GROUPS', $gid );
			$group_leaders       = maybe_unserialize( $row->group_leaders );
			$group_leaders_array = maybe_serialize( array_merge( array_diff( $group_leaders, array( $uid ) ) ) );
			$first_name          = $this->profile_magic_get_user_field_value( $uid, 'first_name' );
			$last_name           = $this->profile_magic_get_user_field_value( $uid, 'last_name' );
			$user_name           = $this->profile_magic_get_user_field_value( $uid, 'user_login' );
			$group_page_url      = admin_url( 'admin.php?page=pm_add_group' );
			$group_page_url      = add_query_arg( 'id', $gid, $group_page_url );
                        $group_options       = maybe_unserialize($row->group_options);
			$dbhandler->update_row( 'GROUPS', 'id', $gid, array( 'group_leaders' => $group_leaders_array ) );
			$subject  = esc_html__( 'A Manager Left the Group', 'profilegrid-user-profiles-groups-and-communities' );
			$message  = esc_html__( 'Hello,', 'profilegrid-user-profiles-groups-and-communities' );
			$message .= "<br />\r\n\r\n";
			if(isset($group_options['group_type']) && $group_options['group_type'] == 'open'){
                            $message .= esc_html__( 'A {{group_admin_label}} has left the group {{group_name}}. Consequently, this group is without a {{group_admin_label}}. Since this is an Open user group, it can continue to function without a designated {{group_admin_label}}. However, appointing a new leader is still recommended to ensure effective group coordination and communication. You may assign a new {{group_admin_label}} to this group by visiting {{edit_group_url}}. For now, all messages for {{group_admin_label}} of {{group_name}} will be redirected to you.', 'profilegrid-user-profiles-groups-and-communities' );    
                        }else{
                            $message .= esc_html__( 'A {{group_admin_label}} has left the group {{group_name}}. Consequently, this group is without a {{group_admin_label}}. Closed user groups require a {{group_admin_label}} for proper group management. Please assign a new {{group_admin_label}} to this group by visiting {{edit_group_url}}. Alternatively, consider making it an Open group. For now, all messages for {{group_admin_label}} of {{group_name}} will be redirected to you.', 'profilegrid-user-profiles-groups-and-communities' );
                        }
                        $message .= "<br />\r\n\r\n";
			$message .= esc_html__( 'Regards.', 'profilegrid-user-profiles-groups-and-communities' ) . "<br />\r\n";
			$message  = $pm_emails->pm_filter_email_content( $message, $uid, false, $gid );
			$pm_emails->pm_send_admin_notification( $subject, $message );
		}
		if ( is_array( $user_groups ) ) {
			$gid_array = $user_groups;
		} else {
			$gid_array = array( $user_groups );
		}

		$gid_array = array_merge( array_diff( $gid_array, array( $gid ) ) );

		$update = update_user_meta( $uid, 'pm_group', $gid_array );
		if ( $update ) {
			do_action( 'pg_user_leave_group', $uid, $gid );
			return 'success';
		} else {
			return 'failed';
		}
	}

	public function pg_unassign_group_during_delete_group( $uid, $gid ) {
		$user_group  = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$user_groups = $this->pg_filter_users_group_ids( $user_group );
		if ( is_array( $user_groups ) ) {
			$gid_array = $user_groups;
		} else {
			$gid_array = array( $user_groups );
		}

		$gid_array = array_merge( array_diff( $gid_array, array( $gid ) ) );

		if ( ! empty( $gid_array ) ) {
			update_user_meta( $uid, 'pm_group', $gid_array );
		} else {
			delete_user_meta( $uid, 'pm_group' );
		}

	}

	public function pg_get_group_leaders( $gid ) {
		$dbhandler       = new PM_DBhandler();
		$leaders         = array();
		$is_group_leader = $dbhandler->get_value( 'GROUPS', 'is_group_leader', $gid, 'id' );

		if ( $is_group_leader != 0 ) {
			$group_leaders = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_leaders', $gid, 'id' ) );
			if ( is_array( $group_leaders ) ) {
				$leaders = $group_leaders;

				if ( ! isset( $leaders['primary'] ) ) {
					foreach ( $group_leaders as $primary ) {
						$leaders['primary'] = $primary;
						break;
					}
				}
			} else {
				$leaders['primary'] = $group_leaders;
			}

			if ( !class_exists( 'Profilegrid_Group_Multi_Admins' ) ) {
				
				if ( isset( $leaders['primary'] ) ) {
					$array = array( 'primary' => $leaders['primary'] );
				} else {
					$array = array();
				}

				$leaders = $array;
			}
		}
                return apply_filters('pm_group_leaders_list',$leaders, $gid);
	}
	public function pg_check_in_single_group_is_user_group_leader( $userid, $gid ) {
		do_action("pg_check_is_super_group_member", $userid);
			$leaders = $this->pg_get_group_leaders( $gid );
		if ( ! empty( $leaders ) ) {
			if ( in_array( $userid, $leaders ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function pg_resolve_message_thread_id( $sid, $rid, $tid = '' ) {
		$tid = absint( $tid );
		if ( $tid > 0 ) {
			$dbhandler = new PM_DBhandler();
			$thread    = $dbhandler->get_row( 'MSG_THREADS', $tid, 't_id' );
			if ( isset( $thread->s_id, $thread->r_id ) ) {
				$participants = array( (int) $thread->s_id, (int) $thread->r_id );
				sort( $participants );
				$expected = array( absint( $sid ), absint( $rid ) );
				sort( $expected );
				if ( $participants === $expected ) {
					return $tid;
				}
			}
		}

		$pair_thread_id = $this->get_thread_id( $sid, $rid );
		if ( ! empty( $pair_thread_id ) ) {
			return $pair_thread_id;
		}

		return $this->fetch_or_create_thread( $sid, $rid );
	}

	public function profile_magic_get_group_type( $gid ) {
		$dbhandler     = new PM_DBhandler();
		$group_options = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_options', $gid ) );
		if ( isset( $group_options['group_type'] ) ) {
			return $group_options['group_type'];
		} else {
			return 'open';
		}
	}

	public function profile_magic_check_is_group_member( $gid, $uid ) {
		 $user_group = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$pm_group    = $this->pg_filter_users_group_ids( $user_group );
		if ( is_array( $pm_group ) && ! empty( $pm_group ) ) {
			if ( in_array( $gid, $pm_group ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function profile_magic_check_is_requested_to_join_group( $gid, $uid ) {
		$dbhandler = new PM_DBhandler();
		$where     = array(
			'gid'    => $gid,
			'uid'    => $uid,
			'status' => '1',
		);
		$row       = $dbhandler->get_all_result( 'REQUESTS', '*', $where );
		return $row;
	}
	public function profile_magic_get_join_group_button( $gid, $show_action = 'no' ) {
		$dbhandler        = new PM_DBhandler();
		$pmrequests       = new PM_request();
		$registration_url = $this->profile_magic_get_frontend_url( 'pm_registration_page', '' );
		$registration_url = add_query_arg( 'gid', $gid, $registration_url );
		if ( $show_action == 'yes' ) {
			$group_page = $this->profile_magic_get_frontend_url( 'pm_group_page', '' ,$gid );
			//$group_page = add_query_arg( 'gid', $gid, $group_page );
			$action     = 'action="' . $group_page . '"';
		} else {
			$action = '';
		}
		$is_group_limit       = $dbhandler->get_value( 'GROUPS', 'is_group_limit', $gid );
		$meta_query_array     = $pmrequests->pm_get_user_meta_query( array( 'gid' => $gid ) );
		$limit                = $dbhandler->get_value( 'GROUPS', 'group_limit', $gid );
		$total_users_in_group = count( $dbhandler->pm_get_all_users( '', $meta_query_array ) );
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$requested    = $this->profile_magic_check_is_requested_to_join_group( $gid, $current_user->ID );
			if ( $requested == null ) {
				if ( $this->profile_magic_check_is_group_member( $gid, $current_user->ID ) ) {
					if ( $dbhandler->get_global_option_value( 'pm_show_group_leave_group_button', '1' ) ) :
						?>
					<div class="pm-group-signup">
				   <button class="pm_button" onclick="pg_edit_blog_popup('group','remove_group','<?php echo esc_attr( $current_user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')">
					   <?php esc_html_e( 'Leave Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
				   </button>
					</div>
						<?php
					endif;
				} else {
					if ( $is_group_limit == 1 ) {
						if ( $limit > $total_users_in_group ) {
							?>
							<div class="pm-group-signup">
                            <?php 
                                $basic_function = class_exists( 'Profile_Magic_Functions' ) ? new Profile_Magic_Functions($this->profile_magic, $this->version) : null;
                                $nonce_field    = '';
                                if ( $basic_function && method_exists( $basic_function, 'pm_render_nonce_field' ) ) {
                                    ob_start();
                                    $basic_function->pm_render_nonce_field( 'pg_join_group_action', 'pg_join_group_nonce' );
                                    $nonce_field = ob_get_clean();
                                }
                                $form_html = '<form method="post" ' . wp_kses_post($action) . '>
                                <input type="hidden" name="pg_uid" id="pg_uid" value="' . esc_attr($current_user->ID) . '" />
                                <input type="hidden" name="pg_join_gid" id="pg_join_gid" value="' . esc_attr($gid) . '" />
                                <input type="hidden" name="pg_join_group" id="pg_join_group" value="1" />
                                ' . $nonce_field . '
                                <button type="submit" class="pm_button">' . esc_html(apply_filters('profilegrid_join_group_label', __('Join Group', 'profilegrid-user-profiles-groups-and-communities'))) . '</button>
                                
                                </form>';

                                // Apply a filter to the entire form HTML
                                $form_html = apply_filters('profilegrid_join_group_form_html', $form_html, $gid);

                                // Output the form
                                echo $form_html;
                            ?>
							</div>
							<?php
						} else {

								$message = $dbhandler->get_value( 'GROUPS', 'group_limit_message', $gid );
								echo '<div class="pg-group-limit-message">' . wp_kses_post( $message ) . '</div>';
						}
					} else {
						?>
						<div class="pm-group-signup">
                        <?php 
                            $basic_function = class_exists( 'Profile_Magic_Functions' ) ? new Profile_Magic_Functions($this->profile_magic, $this->version) : null;
                            $nonce_field    = '';
                            if ( $basic_function && method_exists( $basic_function, 'pm_render_nonce_field' ) ) {
                                ob_start();
                                $basic_function->pm_render_nonce_field( 'pg_join_group_action', 'pg_join_group_nonce' );
                                $nonce_field = ob_get_clean();
                            }
                            $form_html = '<form method="post" ' . wp_kses_post($action) . '>
                            <input type="hidden" name="pg_uid" id="pg_uid" value="' . esc_attr($current_user->ID) . '" />
                            <input type="hidden" name="pg_join_gid" id="pg_join_gid" value="' . esc_attr($gid) . '" />
                            <input type="hidden" name="pg_join_group" id="pg_join_group" value="1" />
                            ' . $nonce_field . '
                            <button type="submit" class="pm_button">' . esc_html(apply_filters('profilegrid_join_group_label', __('Join Group', 'profilegrid-user-profiles-groups-and-communities'))) . '</button>
                            
                            </form>';

                            // Apply a filter to the entire form HTML
                            $form_html = apply_filters('profilegrid_join_group_form_html', $form_html, $gid);

                            // Output the form
                            echo $form_html;
                        ?>
						</div>
						<?php
					}
				}
			}
		} else {
                    $allowed_tags = array(
                        'button' => array(
                            'class' => true,
                            'onclick' => true,
                        ),
                    );
			if ( $is_group_limit == 1 ) {
				
				if ( $limit > $total_users_in_group ) {
					?>
					
				<div class="pm-group-signup">
                                    <?php 
                                       $link = '<button onclick="window.location.href=\'' . esc_url( $registration_url ) . '\'" class="pm_button">'.esc_html__( 'Join Group', 'profilegrid-user-profiles-groups-and-communities' ).'</button>';
                                       $link = apply_filters('profilegrid_group_page_button_link',$link,$gid);
                                       echo wp_kses($link, $allowed_tags);
                                    ?>
                                    
				</div>
					<?php
				} else {
						$message = $dbhandler->get_value( 'GROUPS', 'group_limit_message', $gid );
						echo wp_kses_post( $message );
				}
			} else {
				?>
				<div class="pm-group-signup">
                                    <?php 
                                        $link = '<button onclick="window.location.href=\'' . esc_url( $registration_url ) . '\'" class="pm_button">' . esc_html__( 'Join Group', 'profilegrid-user-profiles-groups-and-communities' ) . '</button>';
                                        $link = apply_filters('profilegrid_group_page_button_link', $link, $gid);
                                        echo wp_kses($link, $allowed_tags);

                                    ?>
				</div>                
				<?php
			}
		}

	}

	public function profile_magic_join_group_fun( $uid, $gid, $type ) {
		 $pmemails    = new PM_Emails();
		$dbhandler    = new PM_DBhandler();
		$notification = new Profile_Magic_Notification();
                $is_group_limit = $dbhandler->get_value('GROUPS','is_group_limit',$gid);
                if($is_group_limit==1)
                {
                    $meta_query_array = $this->pm_get_user_meta_query(array('gid'=>$gid));
                    $user_query = $dbhandler->pm_get_all_users_ajax('',$meta_query_array);
                    $total_users_in_group = $user_query->get_total();
                    $limit = $dbhandler->get_value('GROUPS','group_limit',$gid);
                    
                        if($limit <= $total_users_in_group)
                        {
                            $message  = $dbhandler->get_value('GROUPS','group_limit_message',$gid);
                            echo $message;    
                            return false;
                        }
                }
		if ( $type == 'open' ) {
			$user_group    = maybe_unserialize( $this->profile_magic_get_user_field_value( $uid, 'pm_group' ) );
			$user_groups   = $this->pg_filter_users_group_ids( $user_group );
			$joining_dates = $this->profile_magic_get_user_field_value( $uid, 'pm_joining_date' );

			if ( is_array( $user_groups ) ) {
				$gid_array = $user_groups;
			} else {
				if ( $user_groups != '' && $user_groups != null ) {
					$gid_array = array( $user_groups );
				} else {
					$gid_array = array();
				}
			}

			if ( is_array( $joining_dates ) && ! empty( $joining_dates ) ) {
				$joining_dates[ $gid ] = gmdate( 'Y-m-d' );
			} else {
				$joining_dates         = array();
				$joining_dates[ $gid ] = gmdate( 'Y-m-d' );

			}
			if ( ! in_array( $gid, $gid_array ) ) {
				$gid_array = array_merge( $gid_array, array( $gid ) );
			}
			$update     = update_user_meta( $uid, 'pm_joining_date', $joining_dates );
			$update     = update_user_meta( $uid, 'pm_group', $gid_array );
			$where      = array(
				'gid' => $gid,
				'uid' => $uid,
			);
			$data       = array( 'status' => '3' );
			$request_id = $dbhandler->get_value_with_multicondition( 'REQUESTS', 'id', $where );
			if ( ! empty( $request_id ) ) {
				$dbhandler->remove_row( 'REQUESTS', 'id', $request_id );
			}

			do_action( 'profile_magic_join_group_additional_process', $gid, $uid );
			$pmemails->pm_send_group_based_notification( $gid, $uid, 'on_registration' );
			$notification->pm_joined_new_group_notification( $uid, $gid );
			return $update;
		}
		else if($type == 'form'){
			do_action("pg_handle_group_type_form_functionality",$uid, $gid, $type);
		} 
		else{
			$date    = gmdate( 'Y-m-d H:i:s' );
			$options = maybe_serialize( array( 'request_date' => $date ) );
			$data    = array(
				'gid'     => $gid,
				'uid'     => $uid,
				'status'  => '1',
				'options' => $options,
			);
			// check if request already submitted
			$where      = array(
				'gid' => $gid,
				'uid' => $uid,
			);
			$request_id = $dbhandler->get_value_with_multicondition( 'REQUESTS', 'id', $where );
			if ( $request_id == '' || $request_id == null ) {
				$dbhandler->insert_row( 'REQUESTS', $data, array( '%d', '%d', '%d', '%s' ) );
			}
			$groupleaders = $this->pg_get_group_leaders( $gid );
			if ( empty( $groupleaders ) ) {
				$email_address = get_option( 'admin_email' );
				$leader_info   = get_user_by( 'email', $email_address );
				if ( ! empty( $leader_info ) && isset( $leader_info->ID ) ) {
					$groupleaders[] = $leader_info->ID;
				}
			}
			if ( ! empty( $groupleaders ) ) :
				foreach ( $groupleaders as $leader ) {

					 $pmemails->pm_send_group_based_notification_to_group_admin( $gid, $uid, 'on_membership_request', $leader );
				}
			endif;
			do_action( 'profilegrid_join_group_request', $gid, $uid );
			return true;
		}
		
	}

	public function pm_get_all_join_group_requests( $gid, $pagenum = 1, $limit = 10, $sort_by = 'first_name_asc', $search = '', $search_in = 'user_login' ) {

		$dbhandler    = new PM_DBhandler();
		$offset       = ( $pagenum - 1 ) * $limit;
		$user_request = array();
		$requested    = $dbhandler->get_all_result(
			'REQUESTS',
			'*',
			array(
				'gid'    => $gid,
				'status' => '1',
			)
		);
		if ( ! empty( $requested ) ) {
			foreach ( $requested as $request ) {
				$user_request[] = $request->uid;
			}
		} else {
			$user_request[] = 0;
		}

		if ( trim( $search ) != '' ) {
			$meta_query_array = array(
				'key'     => $search_in,
				'value'   => $search,
				'compare' => 'LIKE',
			);
		} else {
			$meta_query_array = array();
		}

		switch ( $sort_by ) {
			case 'name_asc':
				$sortby = 'login';
				$order  = 'ASC';
				break;
			case 'name_desc':
				$sortby = 'login';
				$order  = 'DESC';
				break;
			case 'latest_first':
				$sortby = 'registered';
				$order  = 'DESC';
				break;
			case 'oldest_first':
				$sortby = 'registered';
				$order  = 'ASC';
				break;
			case 'suspended':
				$sortby        = 'registered';
				$order         = 'DESC';
				$get['status'] = '1';
				break;
			case 'first_name_asc':
				$sortby = 'first_name';
				$order  = 'ASC';
				break;
			case 'first_name_desc':
				$sortby = 'first_name';
				$order  = 'DESC';
				break;
			case 'last_name_asc':
				$sortby = 'last_name';
				$order  = 'ASC';
				break;
			case 'last_name_desc':
				$sortby = 'last_name';
				$order  = 'DESC';
				break;
			default:
				$sortby = 'login';
				$order  = 'ASC';
				break;

		}

		$current_user = wp_get_current_user();
		if ( ! empty( $get ) ) {
			$meta_query_array = $this->pm_get_user_meta_query( $get ); }
		$user_query   = $dbhandler->pm_get_all_users_ajax( $search, $meta_query_array, '', $offset, $limit, $order, $sortby, array( $current_user->ID ), array(), $user_request );
		$total_users  = $user_query->get_total();
		$users        = $user_query->get_results();
		$num_of_pages = ceil( $total_users / $limit );
		$pagination   = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );

		?>
<input type="hidden" name="pg_request_select_type" id="pg_request_select_type" value="this_page" />
<input type="hidden" name="pg_total_request_users" id="pg_total_request_users" value="<?php echo esc_attr( $total_users ); ?>" />
<input type="hidden" name="pg_total_request_users_sigle_page" id="pg_total_request_users_sigle_page" value="<?php echo esc_attr( $limit ); ?>" />
		<?php if ( ! empty( $users ) ) { ?> 
<table class="pg-group-requests pg-group-members">
	<tbody>
		<tr>
			<th><input class="pg-requests-checked-all" type="checkbox"   value="1" name="" onclick="pg_checked_all_requests(this)"></th>
			<th>&nbsp;</th>
			<th class="pg-group-member-name"><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th class="pg-group-member-name"><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th>&nbsp;</th>
			<th><?php esc_html_e( 'Request Sent On', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
		<!--********************************-->
			<?php
			foreach ( $users as $user ) :
				$requested           = $dbhandler->get_all_result(
					'REQUESTS',
					'*',
					array(
						'gid'    => $gid,
						'uid'    => $user->ID,
						'status' => '1',
					)
				);
					$request_options = maybe_unserialize( $requested[0]->options );
				?>
		<tr>
			<td><input class="pg-request-checked" type="checkbox"   value="<?php echo esc_attr( $user->ID ); ?>" name=""onclick="pm_show_hide_batch_operation('requests')"></td>
			<td>
				<div class="pg-member-avatar">
					<a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank">
						<?php echo get_avatar( $user->user_email, 26, '', false, array( 'force_display' => true ) ); ?>
					</a>
				</div>
			</td>
			<td class="pg-group-member-name"><a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php echo esc_html( $user->first_name ); ?></a></td>
			<td class="pg-group-member-name"><a href="<?php echo esc_url( $this->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php echo esc_html( $user->last_name ); ?></a></td>
			<td><?php $login_status = ( $this->pm_get_user_online_status( $user->ID ) == 1 ? 'pm-online' : 'pm-offline' ); ?> <span class="pm-friend-status <?php echo esc_attr( $login_status ); ?>"></span></td>
			<td><?php echo wp_kses_post( $this->pm_change_date_in_different_format( $request_options['request_date'], 'request' ) ); ?></td>
			<td><a class="pm-remove" onclick="pg_edit_blog_popup('group','decline_request','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Decline' ); ?></a></td>
			<td><a onclick="pg_edit_blog_popup('group','accept_request','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Approve' ); ?></a></td>
			
			
						<td>     
							<div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
								<div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
								<ul class="pg-dropdown-menu">
									<li><a onclick="pg_edit_blog_popup('member','message','<?php echo esc_attr( $user->ID ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
								</ul>
							</div>
						</td>
		</tr>
			<?php endforeach; ?>
	</tbody>
</table>
		<?php } else { ?> 
	 <div class='pg-alert-warning pg-alert-info'><?php esc_html_e( 'There are no pending membership requests.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
			<?php
		}
		if (isset($pagination)){
			echo '<div class="pm-request-pagination">' . wp_kses_post( $pagination ) . '</div>';
		}else{
			echo '<div class="pm-request-pagination"></div>';
		}
	}

	public function pg_get_primary_group_id( $gid ) {
		if ( is_array( $gid ) ) {
			if ( isset( $gid[0] ) ) {
				$single_group = $gid[0];
			} else {
				$single_group = array( '0' );
			}
		} else {
			$single_group = $gid;
		}
		return $single_group;
	}

	public function pm_get_all_rm_registration_form_dropdown_list( $selected ) {
		$dbhandler = new PM_DBhandler();
		$forms     = $dbhandler->get_all_result( 'FORMS', '*', array( 'form_type' => '1' ) );
		// print_r($forms);die;
		if ( ! empty( $forms ) ) :
			foreach ( $forms as $form ) {
				?>
			<option value="<?php echo esc_attr( $form->form_id ); ?>" <?php selected( $selected, $form->form_id ); ?>><?php echo esc_html( $form->form_name ); ?></option>
				<?php
			}
		endif;
	}

	public function pm_get_all_rm_registration_form_fields_dropdown_list( $selected, $form_id ) {
		$dbhandler = new PM_DBhandler();
		$fields    = $dbhandler->get_all_result( 'FORM_FIELDS', '*', array( 'form_id' => $form_id ) );
		// print_r($forms);die;
		foreach ( $fields as $field ) {
			if ( in_array( $field->field_type, array( 'UserPassword', 'Username' ) ) ) {
				continue;
			}
			?>
			<option value="<?php echo esc_attr( $field->field_id ); ?>" <?php selected( $selected, $field->field_id ); ?>><?php echo esc_html( $field->field_label ); ?></option>
			<?php
		}
	}

	public function pm_check_if_group_associate_with_rm_form( $gid ) {
		$dbhandler     = new PM_DBhandler();
		$group_options = maybe_unserialize( $dbhandler->get_value( 'GROUPS', 'group_options', $gid, 'id' ) );

		if ( isset( $group_options ) && isset( $group_options['pg_rm_form'] ) && $group_options['pg_rm_form']!=0 ) {
			$form_type = $this->pm_check_rm_form_type( $group_options['pg_rm_form'] );
			if ( $form_type ) {
				return $group_options['pg_rm_form'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function pm_check_rm_form_type( $form_id ) {
		 $dbhandler = new PM_DBhandler();
		$type       = $dbhandler->get_value( 'FORMS', 'form_type', $form_id, 'form_id' );
		return $type;
	}

	public function pm_check_rm_form_associate_with_groups( $form_id ) {
		$dbhandler        = new PM_DBhandler();
		$groups           = $dbhandler->get_all_result( 'GROUPS' );
		$associate_groups = array();
		foreach ( $groups as $group ) {
			$group_options = maybe_unserialize( $group->group_options );
			if ( isset( $group_options ) && isset( $group_options['pg_rm_form'] ) && $group_options['pg_rm_form'] == $form_id ) {
				$associate_groups[] = $group->id;
			}
			unset( $group_options );
		}
		return $associate_groups;
	}

	public function pm_get_map_fields_with_rm_form( $gid ) {
		$dbhandler = new PM_DBhandler();
		$mapping   = array();
		$fields    = $dbhandler->get_all_result( 'FIELDS', '*', array( 'associate_group' => $gid ) );
                if(isset($fields) && !empty($fields))
                {
                    foreach ( $fields as $field ) {
                            $field_options = maybe_unserialize( $field->field_options );
                            if ( ! empty( $field_options ) && isset( $field_options['field_map_with'] ) ) {
                                    $mapping[ $field->field_key ] = array(
                                            'field_map_with' => $field_options['field_map_with'],
                                            'field_type'     => $field->field_type,
                                            'field_key'      => $field->field_key,
                                    );
                            }
                            unset( $field_options );
                    }
                }
		return $mapping;

	}

	public function pm_check_group_limit( $gid ) {
		$dbhandler            = new PM_DBhandler();
		$message              = '';
		$limit                = $dbhandler->get_value( 'GROUPS', 'group_limit', $gid );
		$is_group_limit       = $dbhandler->get_value( 'GROUPS', 'is_group_limit', $gid );
		$meta_query_array     = $this->pm_get_user_meta_query( array( 'gid' => $gid ) );
		$total_users_in_group = count( $dbhandler->pm_get_all_users( '', $meta_query_array ) );
		if ( $is_group_limit == 1 ) {
			if ( $limit > $total_users_in_group ) {
					$message = '';
			} else {
					$message = $dbhandler->get_value( 'GROUPS', 'group_limit_message', $gid );
			}
		}

		return $message;
	}


	public function pg_get_group_joining_date( $gid, $uid ) {
		$joining_dates = $this->profile_magic_get_user_field_value( $uid, 'pm_joining_date' );
		if ( is_array( $joining_dates ) && ! empty( $joining_dates ) && isset( $joining_dates[ $gid ] ) ) {
			$date = $joining_dates[ $gid ];
		} else {
			$user = get_user_by( 'ID', $uid );
			$date = $user ? $user->user_registered : '';
		}

		$formatted_date = $this->pm_change_date_in_different_format( $date );
		return $formatted_date ? $formatted_date : '';
	}

	public function is_user_online( $user_to_check ) {
		// get the user activity the list
		$logged_in_users = get_transient( 'rm_user_online_status' );

		$online = isset( $logged_in_users[ $user_to_check ] )
		&& ( $logged_in_users[ $user_to_check ] > ( time() - ( 15 * 60 ) ) );

		return $online;
	}

	public function pg_filter_users_group_ids( $gids ) {
		$dbhandler = new PM_DBhandler();
		$groups    = array();
		if ( isset( $gids ) && ! empty( $gids ) ) {
			if ( is_array( $gids ) ) {
				$usergroup = array_unique( $gids );
				foreach ( $usergroup as $gid ) {
					$groupinfo = $dbhandler->get_row( 'GROUPS', $gid );
					if ( isset( $groupinfo ) && ! empty( $groupinfo ) ) {
						$groups[] = $gid;
					}
				}
			} elseif ( is_numeric( $gids ) ) {
				$groupinfo = $dbhandler->get_row( 'GROUPS', $gids );
				if ( isset( $groupinfo ) && ! empty( $groupinfo ) ) {
					$groups[] = $gids;
				}
			}
		}

		return $groups;
	}

	public function pg_get_rm_installation_plugin_url() {
		$plugin_slug = 'custom-registration-form-builder-with-submission-manager';
		$installUrl  = admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug );
		$installUrl  = wp_nonce_url( $installUrl, 'install-plugin_' . $plugin_slug );

		return $installUrl;
	}

	public function pg_get_filter_rm_value( $map_field, $rm_data, $user_id ) {
		$field_type = $map_field['field_type'];
		$map_with   = $map_field['field_map_with'];
		if ( isset( $rm_data[ $map_with ] ) ) {
			$value         = $rm_data[ $map_with ]->value;
			$rm_field_type = $rm_data[ $map_with ]->type;
		} else {
			$value         = '';
			$rm_field_type = '';
		}
		if ( is_array( $value ) ) {
			if ( isset( $value['rm_field_type'] ) ) {
				$rm_field_type = $value['rm_field_type'];
				unset( $value['rm_field_type'] );
			}
		}
		if ( ! empty( $value ) ) {
			if ( isset( $rm_field_type ) && ( $rm_field_type == 'File' || $rm_field_type == 'Image' ) ) {
				if ( $field_type == 'file' || $field_type == 'user_avatar' ) {
					$values = implode( ',', $value );
				} else {
					$value['rm_file_field'] = 1;
					$values                 = maybe_serialize( $value );
				}
			} else {
				if ( $rm_field_type == 'Address' ) {
					if ( isset( $value['address1'] ) ) {
						$value['address_line_1'] = $value['address1'];
						unset( $value['address1'] );
					}
					if ( isset( $value['address2'] ) ) {
						$value['address_line_2'] = $value['address2'];
						unset( $value['address2'] );
					}

					if ( isset( $value['original'] ) ) {
						$value['address_line_1'] = $value['original'];
						unset( $value['original'] );
					}
					if ( isset( $value['st_number'] ) ) {
						$value['address_line_2'] = $value['st_number'];
						unset( $value['st_number'] );
					}
					if ( isset( $value['st_route'] ) ) {
						$value['address_line_2'] = $value['address_line_2'] . ' ' . $value['st_route'];
						unset( $value['st_route'] );
					}

					if ( isset( $value['city'] ) ) {
						$tmp = $value['city'];
						unset( $value['city'] );
						$value['city'] = $tmp;
					}

					if ( isset( $value['state'] ) ) {
						$tmp = $value['state'];
						unset( $value['state'] );
						$value['state'] = $tmp;
					}

					if ( isset( $value['country'] ) ) {
						$tmp = $value['country'];
						unset( $value['country'] );
						$value['country'] = $tmp;
					}

					if ( isset( $value['zip'] ) ) {
						$value['zip_code'] = $value['zip'];
						unset( $value['zip'] );
					}

					$this->pg_update_user_lat_long_using_address_using_rm( $value, $user_id );

				}

				if ( $field_type == 'user_avatar' ) {
					$value = '';
				}

				if ( $rm_field_type == 'Terms' && $field_type == 'term_checkbox' ) {
					if ( $value == 'on' ) {
						$value = 'yes';
					} else {
						$value = '';
					}
				}

				if ( $rm_field_type == 'Terms' && $field_type == 'text' ) {
					if ( $value == 'on' ) {
						$value = esc_html__( 'Accepted Terms & Condition', 'profilegrid-user-profiles-groups-and-communities' );
					} else {
						$value = esc_html__( 'Rejected Terms & Conditions', 'profilegrid-user-profiles-groups-and-communities' );
					}
				}

				if ( $rm_field_type == 'Country' && $field_type == 'country' ) {
					$value = substr( $value, 0, -4 );
				}

				if ( $rm_field_type == 'Checkbox' && $field_type == 'checkbox' ) {
					$field_model = new RM_Fields();
					$field_model->load_from_db( $map_with );
					$rm_values = $field_model->get_field_value();
					$diff      = array_diff( $value, $rm_values );
					if ( ! empty( $diff ) ) {
						$value[] = 'chl_other';
					}
				}

				if ( ( $field_type == 'file' || $field_type == 'user_avatar' ) && ( $rm_field_type != 'File' || $rm_field_type != 'Image' ) ) {
					$value = '';
				}

				$values = maybe_serialize( $value );
			}
		} else {
			$values = '';

			if ( $rm_field_type == 'Terms' && $field_type == 'text' ) {
				$value  = esc_html__( 'Rejected Terms & Conditions', 'profilegrid-user-profiles-groups-and-communities' );
				$values = maybe_serialize( $value );
			}
		}
		return $values;
	}

	public function pg_update_user_lat_long_using_address_using_rm( $value, $user_id ) {
		if ( class_exists( 'Profilegrid_Geolocation_Public' ) ) {
			$addresss     = implode( ' ', $value );
			$address      = rawurlencode( $addresss );
			$pg_publicgeo = new Profilegrid_Geolocation_Public( '', '' );
			$pg_publicgeo->pg_update_user_lat_long_using_address( $address, $user_id );
		}
	}

	public function pg_get_shortcode_page_id( $shortcode ) {
		$string   = '[' . $shortcode;
		$page_id  = 0;
		$my_query = new WP_Query(
			array(
				'post_type' => 'any',
				's'         => $string,
				'fields'    => 'ids',
			)
		);
		if ( ! empty( $my_query->posts ) ) {
			$page_id = $my_query->posts[0];
		}
		return $page_id;
	}

	public function pg_get_group_card_icon_link( $gid ) {
		$dbhandler     = new PM_DBhandler();
		$identifier    = 'GROUPS';
		$group_options = array();
		$row           = $dbhandler->get_row( $identifier, $gid );
		if ( $row->group_options != '' ) {
			$group_options = maybe_unserialize( $row->group_options );
		}

		if ( ! empty( $group_options['group_page'] ) ) {
				$group_page = $group_options['group_page'];
		} else {
				$group_page = '0';
		}

		if ( $group_page == '0' ) {
			$html = '<a href="#" class="pg-create-group-page" data-gid="' . $gid . '" title="' . esc_attr__( 'Click to create page for this group', 'profilegrid-user-profiles-groups-and-communities' ) . '"><i class="fa fa-plus"></i></a>';
		} else {
			$link = get_edit_post_link( $group_page );
			$html = '<a href="' . $link . '" target="_blank" title="' . esc_attr__( 'Click to edit the page for this group', 'profilegrid-user-profiles-groups-and-communities' ) . '"><i class="fa fa-file"></i></a>';
		}
		return $html;
	}

	public function pg_check_if_group_exist( $gid ) {
		$dbhandler = new PM_DBhandler();
		$group     = $dbhandler->get_row( 'GROUPS', $gid );
		if ( isset( $group ) && $group != null ) {
			return true;
		} else {
			return false;
		}
	}

	public function pm_get_all_groups_data( $view = 'grid', $pagenum = 1, $limit = 10, $sort_by = 'newest', $search = '', $filters = array(), $plugin = '', $version = '' ) {
		$dbhandler = new PM_DBhandler();
		$limit = absint( $limit );
		if ( empty( $limit ) ) {
			$limit = 10;
		}
		$pagenum = absint( $pagenum );
		if ( empty( $pagenum ) ) {
			$pagenum = 1;
		}
		$offset    = ( $pagenum - 1 ) * $limit;

		$search_str = trim( $search );
		$is_search  = false;
		$additional_parts = array();
		if ( $search_str != '' ) {
			$is_search  = true;
			$additional_parts[] = "group_name like '%" . $search_str . "%'";
		}

		if ( ! is_array( $filters ) ) {
			$filters = array();
		}
		$include_ids = array();
		$exclude_ids = array();
		if ( ! empty( $filters['include'] ) ) {
			$include_ids = array_filter( array_map( 'absint', (array) $filters['include'] ) );
		}
		if ( ! empty( $filters['exclude'] ) ) {
			$exclude_ids = array_filter( array_map( 'absint', (array) $filters['exclude'] ) );
		}
		if ( ! empty( $include_ids ) ) {
			$additional_parts[] = 'id in(' . implode( ',', $include_ids ) . ')';
		}
		if ( ! empty( $exclude_ids ) ) {
			$additional_parts[] = 'id not in(' . implode( ',', $exclude_ids ) . ')';
		}

		$additional = implode( ' AND ', array_filter( $additional_parts ) );
		$additional = apply_filters( 'pm_get_all_groups_data_additional', $additional );

		switch ( $sort_by ) {
			case 'newest':
				$sortby = 'id';
				$order  = 'DESC';
				break;
			case 'oldest':
				$sortby = 'id';
				$order  = false;
				break;
			case 'name_asc':
				$sortby = 'group_name';
				$order  = false;
				break;
			case 'name_desc':
				$sortby = 'group_name';
				$order  = 'DESC';
				break;
			default:
				$sortby = 'id';
				$order  = 'DESC';
				break;

		}

		$filter_type = ( ! empty( $filters['type'] ) && in_array( $filters['type'], array( 'open', 'closed' ), true ) ) ? $filters['type'] : '';
		$filter_paid = isset( $filters['paid'] ) ? $filters['paid'] : null;
		$requires_post_filter = ( $filter_type !== '' || $filter_paid !== null );
		$groups = array();
		$total_groups = 0;

		if ( $requires_post_filter ) {
			$groups_all = $dbhandler->get_all_result( 'GROUPS', '*', 1, 'results', 0, false, $sortby, $order, $additional );
			$groups_filtered = array();
			if ( ! empty( $groups_all ) ) {
				foreach ( $groups_all as $group ) {
					$group_type = $this->profile_magic_get_group_type( $group->id );
					if ( $filter_type !== '' && $group_type !== $filter_type ) {
						continue;
					}
					if ( $filter_paid !== null ) {
						$is_paid = ( $this->profile_magic_check_paid_group( $group->id ) > 0 );
						if ( $filter_paid === true && ! $is_paid ) {
							continue;
						}
						if ( $filter_paid === false && $is_paid ) {
							continue;
						}
					}
					$groups_filtered[] = $group;
				}
			}

			if ( ! empty( $groups_filtered ) ) {
				usort(
					$groups_filtered,
					function( $a, $b ) use ( $sort_by ) {
						if ( $sort_by === 'name_asc' || $sort_by === 'name_desc' ) {
							$first = strtolower( $a->group_name );
							$second = strtolower( $b->group_name );
							if ( $first === $second ) {
								return 0;
							}
							$result = ( $first < $second ) ? -1 : 1;
							return ( $sort_by === 'name_desc' ) ? $result * -1 : $result;
						}
						$first_id = (int) $a->id;
						$second_id = (int) $b->id;
						if ( $first_id === $second_id ) {
							return 0;
						}
						$result = ( $first_id < $second_id ) ? -1 : 1;
						return ( $sort_by === 'oldest' ) ? $result : $result * -1;
					}
				);
			}

			$total_groups = count( $groups_filtered );
			$groups = array_slice( $groups_filtered, $offset, $limit );
		} else {
			$groups = $dbhandler->get_all_result( 'GROUPS', '*', 1, 'results', $offset, $limit, $sortby, $order, $additional );
			$tot_group = $dbhandler->get_all_result( 'GROUPS', '*', 1, 'results', 0, false, null, false, $additional );
			$total_groups = ! empty( $tot_group ) ? count( $tot_group ) : 0;
		}

		$num_of_pages = ceil( $total_groups / $limit );
		$pagination   = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );

		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) :
				$this->pm_get_group_html( $group, $view );
			endforeach;

		} else {
			?>
	 <div class="pg-groups-empty pm-dbfl">
		 <span class="material-icons">group_off</span>
		 <div class="pg-groups-empty-text"><?php esc_html_e( 'No groups found. Check back later or create a new group!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
	 </div>
			<?php
		}

		?>
	 <div class="pm_clear"></div>
		<?php   
        if(!empty($pagination)){
		echo '<div class="pm-groups-pagination">' . wp_kses_post( $pagination ) . '</div>';
        } else {
        	echo '<div class="pm-groups-pagination"></div>';
        }
	}

	public function pm_get_group_html( $group, $view = 'grid' ) {
		$dbhandler = new PM_DBhandler();
		$option    = 'pm_show_group_on_groups_page_' . $group->id;
                $pmgroupoption = maybe_unserialize($group->group_options);
                $is_global_password_protected = $dbhandler->get_global_option_value('pm_enable_group_password_option',0);
                $allowed_tags = array(
                        'button' => array(
                            'class' => true,
                            'onclick' => true,
                        ),
                        'a' => array(
                            'class' => true,
                            'href' => true,
                            'target' => true,
                            'rel' => true,
                        ),
                    );
                $allowed_html = $this->pg_allowed_html_wp_kses();
                $is_password_protected = (isset($pmgroupoption['enable_password_protection']))?$pmgroupoption['enable_password_protection']:0;
                $password_html = ($is_password_protected==1 && $is_global_password_protected==1)?'<i class="fa fa-lock"></i>':'';
		$show_type_label = $dbhandler->get_global_option_value( 'pm_groups_show_type_label', '1' ) == '1';
		$show_icon = $dbhandler->get_global_option_value( 'pm_groups_show_icon', '1' ) == '1';
		$show_member_count = $dbhandler->get_global_option_value( 'pm_groups_show_member_count', '1' ) == '1';
		$show_member_limit = $dbhandler->get_global_option_value( 'pm_groups_show_member_limit', '1' ) == '1';
		$show_seats_left = $dbhandler->get_global_option_value( 'pm_groups_show_seats_left', '1' ) == '1';
		$show_paid_badge = $dbhandler->get_global_option_value( 'pm_groups_show_paid_badge', '1' ) == '1';
		$show_group_manager = $dbhandler->get_global_option_value( 'pm_groups_show_group_manager', '1' ) == '1';
		$show_action_button = $dbhandler->get_global_option_value( 'pm_groups_show_action_button', '1' ) == '1';

		$group_type = $this->profile_magic_get_group_type( $group->id );
		$group_type_icon = ( $group_type == 'open' ) ? 'lock_open' : 'lock';
		$member_count = (int) $this->pm_count_group_members( $group->id );
		$has_limit = ( isset( $group->is_group_limit ) && (int) $group->is_group_limit === 1 && ! empty( $group->group_limit ) );
		$group_limit = $has_limit ? (int) $group->group_limit : 0;
		$seats_left = $has_limit ? max( 0, $group_limit - $member_count ) : 0;
		$group_price = $this->profile_magic_check_paid_group( $group->id );
		$is_paid = ( $group_price > 0 );
		$price_display = '';
		if ( $is_paid ) {
			if ( $dbhandler->get_global_option_value( 'pm_currency_position', 'before' ) == 'before' ) {
				$price_display = $this->pm_get_currency_symbol() . ' ' . $group_price;
			} else {
				$price_display = $group_price . ' ' . $this->pm_get_currency_symbol();
			}
		}

		$leader_display = '';
		$leader_tooltip = '';
		$leader_more_html = '';
		$leader_names = array();
		$leader_names_plain = array();
		$group_leaders = $this->pg_get_group_leaders( $group->id );
		if ( ! empty( $group_leaders ) && is_array( $group_leaders ) ) {
			$leader_ids = array();
			if ( isset( $group_leaders['primary'] ) ) {
				$primary_id = absint( $group_leaders['primary'] );
				if ( $primary_id ) {
					$leader_ids[] = $primary_id;
				}
			}
			foreach ( $group_leaders as $leader_id ) {
				$leader_id = absint( $leader_id );
				if ( ! $leader_id || in_array( $leader_id, $leader_ids, true ) ) {
					continue;
				}
				$leader_ids[] = $leader_id;
			}

			foreach ( $leader_ids as $leader_id ) {
				$leader_name = $this->pm_get_display_name( $leader_id, true );
				if ( empty( $leader_name ) ) {
					$user = get_userdata( $leader_id );
					if ( $user ) {
						$leader_name = $user->display_name;
					}
				}
				if ( ! empty( $leader_name ) ) {
					$leader_names[] = $leader_name;
					$leader_plain = wp_strip_all_tags( $leader_name );
					if ( $leader_plain !== '' ) {
						$leader_names_plain[] = $leader_plain;
					}
				}
			}
		}
		if ( ! empty( $leader_names ) ) {
			$leader_tooltip = implode( ', ', $leader_names_plain );
			$leader_display = $leader_names[0];
			if ( count( $leader_names ) > 1 ) {
				$leader_more_count = count( $leader_names ) - 1;
				$leader_more_label = sprintf(
					esc_html__( '+%d more', 'profilegrid-user-profiles-groups-and-communities' ),
					$leader_more_count
				);
				$leader_more_html = ' <span class="pg-group-leader-more" title="' . esc_attr( $leader_tooltip ) . '">' . esc_html( $leader_more_label ) . '</span>';
			}
		}

		$show_image_area = $show_icon || $show_type_label || ( $show_paid_badge && $is_paid );
		$show_meta = $show_member_count || $show_member_limit || $show_seats_left || $show_group_manager;
		if ( $dbhandler->get_global_option_value( $option, '1' ) == '1' ) {
			if ( $view == 'grid' ) {

				$group_url        = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $group->id );
				//$group_url        = add_query_arg( 'gid', $group->id, $group_url );

				$registration_url = $this->profile_magic_get_frontend_url( 'pm_registration_page', '' );
				$registration_url = add_query_arg( 'gid', $group->id, $registration_url );
				$img_link = ( is_user_logged_in() ) ? $group_url : $registration_url;
				$img_link = apply_filters( 'profilegrid_group_image_link', $img_link, $group->id );

				$groupdesc = '';
				if ( ! empty( $group->group_desc ) && strlen( $group->group_desc ) > 150 ) {
					$groupdesc  = substr( $group->group_desc, 0, 150 );
					$groupdesc .= '...';
				} else {
					$groupdesc = $group->group_desc;
				}
				$groupdesc = apply_filters( 'profilegrid_group_description', $groupdesc, $group );

				$image_html = '';
				if ( $show_icon ) {
					if ( ! empty( $group->group_icon ) && wp_attachment_is_image( $group->group_icon ) ) {
						$image_html = wp_get_attachment_image( $group->group_icon, 'full', true, array( 'class' => 'pg-group-icon', 'alt' => esc_attr( $group->group_name ) ) );
					} else {
						$image_html = '<div class="pg-group-fallback"><svg class="pg-group-fallback-svg" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><rect width="120" height="120" rx="16" ry="16"></rect></svg><span class="material-icons">groups</span></div>';
					}
				}

				$member_label = '';
				if ( $has_limit && $show_member_limit ) {
					if ( $show_member_count ) {
						$member_label = sprintf( esc_html__( '%1$d/%2$d Members', 'profilegrid-user-profiles-groups-and-communities' ), $member_count, $group_limit );
					} else {
						$member_label = sprintf( esc_html__( '%d Member Limit', 'profilegrid-user-profiles-groups-and-communities' ), $group_limit );
					}
				} elseif ( $show_member_count ) {
					$member_label = sprintf( _n( '%d Member', '%d Members', $member_count, 'profilegrid-user-profiles-groups-and-communities' ), $member_count );
				}

				$join_label = ( $group_type === 'closed' ) ? esc_html__( 'Request Access', 'profilegrid-user-profiles-groups-and-communities' ) : esc_html__( 'Join Group', 'profilegrid-user-profiles-groups-and-communities' );
				$button_label = is_user_logged_in() ? esc_html__( 'Details', 'profilegrid-user-profiles-groups-and-communities' ) : $join_label;
				$button_url = is_user_logged_in() ? $group_url : $registration_url;
				$card_classes = 'pg-group-card pm-group pm-difl pm-border pm-radius5 pm-bg-lt pm50';
				$card_classes .= ( ! $show_image_area && ! $show_meta && ! $show_action_button ) ? ' pg-group-card-minimal' : '';
				?>
				<div class="<?php echo esc_attr( $card_classes ); ?>">
					<?php if ( $show_image_area ) : ?>
					<div class="pg-group-image pm-group-logo">
						<?php if ( ! empty( $image_html ) ) : ?>
							<a href="<?php echo esc_url( $img_link ); ?>" class="pm-group-image-link"><?php echo wp_kses_post( $image_html ); ?></a>
						<?php endif; ?>
						<?php if ( $show_type_label ) : ?>
							<span class="pg-group-type material-icons"><?php echo esc_html( $group_type_icon ); ?></span>
						<?php endif; ?>
						<?php if ( $show_paid_badge && $is_paid ) : ?>
							<span class="pg-paid-badge"><?php esc_html_e( 'PAID', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<div class="pg-group-content pm-group-info">
						<h3 class="pg-group-title pm-group-heading pm-clip"><?php echo wp_kses_post( $password_html ); ?><a href="<?php echo esc_url( $group_url ); ?>"><?php echo esc_html( $group->group_name ); ?></a></h3>
						<?php if ( ! empty( $groupdesc ) ) : ?>
							<p class="pg-group-desc pm-group-desc"><?php echo wp_kses_post( $groupdesc ); ?></p>
						<?php endif; ?>
						<?php if ( $show_meta || $is_paid ) : ?>
						<div class="pg-group-meta">
							<?php if ( $is_paid ) : ?>
								<span class="pg-group-meta-item pg-group-meta-price"><?php echo wp_kses_post( $price_display ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $member_label ) ) : ?>
								<span class="pg-group-meta-item pg-group-meta-members"><span class="material-icons">group</span> <?php echo esc_html( $member_label ); ?></span>
							<?php endif; ?>
                                                        <?php if ( $show_group_manager && ! empty( $leader_display ) ) : ?>
                                                                <span class="pg-group-meta-item pg-group-meta-manager"><span class="material-icons">person</span> <?php echo wp_kses( $leader_display, $allowed_html ); ?><?php echo wp_kses_post( $leader_more_html ); ?></span>
                                                        <?php endif; ?>
							<?php if ( $show_seats_left && $has_limit && $member_count > 0 && $seats_left > 0 ) : ?>
								<span class="pg-urgency"><?php echo esc_html( sprintf( _n( '%d Seat Left', '%d Seats Left', $seats_left, 'profilegrid-user-profiles-groups-and-communities' ), $seats_left ) ); ?></span>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<?php if ( $show_action_button ) : ?>
							<?php
							$link = '<a href="' . esc_url( $button_url ) . '" class="pm_button pg-group-button">' . esc_html( $button_label ) . '</a>';
							$link = apply_filters( 'profilegrid_all_groups_page_button_link', $link, $group->id, $group_url, $registration_url );
							echo wp_kses( $link, $allowed_tags );
							?>
						<?php endif; ?>
					</div>
				</div>
				<?php
			} else {
				$group_url       = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $group->id );
				$registration_url = $this->profile_magic_get_frontend_url( 'pm_registration_page', '' );
				$registration_url = add_query_arg( 'gid', $group->id, $registration_url );
				$img_link = ( is_user_logged_in() ) ? $group_url : $registration_url;
				$img_link = apply_filters( 'profilegrid_group_image_link', $img_link, $group->id );

				$groupdesc = '';
				if ( ! empty( $group->group_desc ) && strlen( $group->group_desc ) > 150 ) {
					$groupdesc  = substr( $group->group_desc, 0, 150 );
					$groupdesc .= '...';
				} else {
					$groupdesc = $group->group_desc;
				}
				$groupdesc = apply_filters( 'profilegrid_group_description', $groupdesc, $group );

				$image_html = '';
				if ( $show_icon ) {
					if ( ! empty( $group->group_icon ) && wp_attachment_is_image( $group->group_icon ) ) {
						$image_html = wp_get_attachment_image( $group->group_icon, 'full', true, array( 'class' => 'pg-group-icon', 'alt' => esc_attr( $group->group_name ) ) );
					} else {
						$image_html = '<div class="pg-group-fallback"><svg class="pg-group-fallback-svg" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><rect width="120" height="120" rx="16" ry="16"></rect></svg><span class="material-icons">groups</span></div>';
					}
				}

				$member_label = '';
				if ( $has_limit && $show_member_limit ) {
					if ( $show_member_count ) {
						$member_label = sprintf( esc_html__( '%1$d/%2$d Members', 'profilegrid-user-profiles-groups-and-communities' ), $member_count, $group_limit );
					} else {
						$member_label = sprintf( esc_html__( '%d Member Limit', 'profilegrid-user-profiles-groups-and-communities' ), $group_limit );
					}
				} elseif ( $show_member_count ) {
					$member_label = sprintf( _n( '%d Member', '%d Members', $member_count, 'profilegrid-user-profiles-groups-and-communities' ), $member_count );
				}

				$join_label = ( $group_type === 'closed' ) ? esc_html__( 'Request Access', 'profilegrid-user-profiles-groups-and-communities' ) : esc_html__( 'Join Group', 'profilegrid-user-profiles-groups-and-communities' );
				$button_label = is_user_logged_in() ? esc_html__( 'Details', 'profilegrid-user-profiles-groups-and-communities' ) : $join_label;
				$button_url = is_user_logged_in() ? $group_url : $registration_url;
				$card_classes = 'pg-group-card pg-group-card-list pm-group pm-group-list-view pm-dbfl';
				$card_classes .= ( ! $show_image_area && ! $show_meta && ! $show_action_button ) ? ' pg-group-card-minimal' : '';
				?>
		 <div class="<?php echo esc_attr( $card_classes ); ?>">
			 <?php if ( $show_image_area ) : ?>
			 <div class="pg-group-image pm-group-logo pm-difl">
				 <?php if ( ! empty( $image_html ) ) : ?>
					 <a href="<?php echo esc_url( $img_link ); ?>" class="pm-group-image-link"><?php echo wp_kses_post( $image_html ); ?></a>
				 <?php endif; ?>
				 <?php if ( $show_type_label ) : ?>
					 <span class="pg-group-type material-icons"><?php echo esc_html( $group_type_icon ); ?></span>
				 <?php endif; ?>
				 <?php if ( $show_paid_badge && $is_paid ) : ?>
					 <span class="pg-paid-badge"><?php esc_html_e( 'PAID', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
				 <?php endif; ?>
			 </div>
			 <?php endif; ?>
			 <div class="pg-group-content pm-group-name-desc pm-difl">
				 <h3 class="pg-group-title pm-group-heading pm-pad10 pm-clip"><?php echo wp_kses_post( $password_html ); ?><a href="<?php echo esc_url( $group_url ); ?>"><?php echo esc_html( $group->group_name ); ?></a></h3>
				 <?php if ( ! empty( $groupdesc ) ) : ?>
					 <p class="pg-group-desc pm-group-desc pm-pad10"><?php echo wp_kses_post( $groupdesc ); ?></p>
				 <?php endif; ?>
				 <?php if ( $show_meta || $is_paid ) : ?>
				 <div class="pg-group-meta">
					 <?php if ( $is_paid ) : ?>
						 <span class="pg-group-meta-item pg-group-meta-price"><?php echo wp_kses_post( $price_display ); ?></span>
					 <?php endif; ?>
					 <?php if ( ! empty( $member_label ) ) : ?>
						 <span class="pg-group-meta-item pg-group-meta-members"><span class="material-icons">group</span> <?php echo esc_html( $member_label ); ?></span>
					 <?php endif; ?>
                                         <?php if ( $show_group_manager && ! empty( $leader_display ) ) : ?>
                                                 <span class="pg-group-meta-item pg-group-meta-manager"><span class="material-icons">person</span> <?php echo wp_kses( $leader_display, $allowed_html ); ?><?php echo wp_kses_post( $leader_more_html ); ?></span>
                                         <?php endif; ?>
					 <?php if ( $show_seats_left && $has_limit && $member_count > 0 && $seats_left > 0 ) : ?>
						 <span class="pg-urgency"><?php echo esc_html( sprintf( _n( '%d Seat Left', '%d Seats Left', $seats_left, 'profilegrid-user-profiles-groups-and-communities' ), $seats_left ) ); ?></span>
					 <?php endif; ?>
				 </div>
				 <?php endif; ?>
			 </div>
			 <?php if ( $show_action_button ) : ?>
			 <div class="pm-group-button pm-difr">
				 <?php
				 $link = '<a href="' . esc_url( $button_url ) . '" class="pm_button pg-group-button">' . esc_html( $button_label ) . '</a>';
				 $link = apply_filters( 'profilegrid_all_groups_page_button_link', $link, $group->id, $group_url, $registration_url );
				 echo wp_kses( $link, $allowed_tags );
				 ?>
			 </div>
			 <?php endif; ?>

		 </div>
				<?php
			}
		}
	}


	public function pm_count_group_members( $gid ) {
		$dbhandler        = new PM_DBhandler();
		$meta_query_array = $this->pm_get_user_meta_query( array( 'gid' => $gid ) );
		$user_query       = $dbhandler->pm_get_all_users_ajax( '', $meta_query_array );
		$total_users      = $user_query->get_total();
		return $total_users;
	}

	public function pm_show_profile_image_profile_page( $uid, $cuid, $user_info ) {
		 $dbhandler = new PM_DBhandler();
		$id         = ( $uid != $cuid ) ? 'id="pm-show-profile-image"' : '';
		if ( $dbhandler->get_global_option_value( 'pm_show_profile_image', '1' ) == '1' ) {
			$html = '<div ' . $id . ' class="pm-profile-image pm-difl pm-pad10">' . get_avatar(
				$user_info->user_email,
				150,
				'',
				false,
				array(
					'class'         => 'pm-user',
					'force_display' => true,
				)
			);
			if ( $uid == $cuid && $dbhandler->get_global_option_value( 'pm_show_change_profile_image_option', '1' ) == '1' ) :
				$html  .= '<div class="pm-bg-dk pg-profile-change-img">
                <div id="pm-change-image" class="pg-item-image-change">
                        <i class="fa fa-camera-retro" aria-hidden="true"></i>';
				 $html .= esc_html__( 'Update Image', 'profilegrid-user-profiles-groups-and-communities' );
				$html  .= '</div>
                </div>';
			   endif;
			$html .= '</div>';
			return apply_filters( 'pm_show_profile_image_html', $html, $uid, $cuid, $user_info );
		} else {
			$html = '<div ' . $id . ' class="pm-profile-image pm-no-profile-image pm-difl pm-pad10"></div>';
			return $html;
		}
	}

	public function pm_profile_tabs() {
		 $dbhandler               = new PM_DBhandler();
		$tabs                     = array();
		$blog_tab_status          = $dbhandler->get_global_option_value( 'pm_enable_blog', '1' );
		$message_tab_status       = $dbhandler->get_global_option_value( 'pm_enable_private_messaging', '1' );
		$friends_tab_status       = $dbhandler->get_global_option_value( 'pm_friends_panel', '0' );
		$tabs['pg-about']         = array(
			'id'     => 'pg-about',
			'title'  => esc_html__( 'About', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => '1',
			'class'  => 'pg-about-tab',
		);
		$tabs['pg-groups']        = array(
			'id'     => 'pg-groups',
			'title'  => esc_html__( 'Groups', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => '1',
			'class'  => 'pg-group-tab',
		);
		$tabs['pg-blog']          = array(
			'id'     => 'pg-blog',
			'title'  => esc_html__( 'Blog', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => $blog_tab_status,
			'class'  => 'pg-blog-tab',
		);
		$tabs['pg-messages']      = array(
			'id'     => 'pg-messages',
			'title'  => esc_html__( 'Messages', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => $message_tab_status,
			'class'  => 'pg-message-tab',
		);
		$tabs['pg-notifications'] = array(
			'id'     => 'pg-notifications',
			'title'  => esc_html__( 'Notifications', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => '1',
			'class'  => 'pg-notification-tab',
		);
		$tabs['pg-friends']       = array(
			'id'     => 'pg-friends',
			'title'  => esc_html__( 'Friends', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => $friends_tab_status,
			'class'  => 'pg-friend-tab',
		);
		$tabs['pg-settings']      = array(
			'id'     => 'pg-settings',
			'title'  => esc_html__( 'Settings', 'profilegrid-user-profiles-groups-and-communities' ),
			'status' => '1',
			'class'  => 'pg-setting-tab',
		);

		$pm_profile_tabs_order_status = maybe_unserialize( $dbhandler->get_global_option_value( 'pm_profile_tabs_order_status', $tabs ) );
		return apply_filters( 'pm_profile_tabs', $pm_profile_tabs_order_status );
	}

	public function generate_profile_tab_links( $id, $tab, $uid, $gid, $primary_gid ) {
		 $path = plugins_url( '../public/partials/images/sounds/msg_tone.mp3', __FILE__ );

		 $path = apply_filters('custom_chat_notification_sound', $path);
                
		$current_user = wp_get_current_user();
                $dbhandler = new PM_DBhandler;
                $enable_private_profile = $dbhandler->get_global_option_value( 'pm_enable_private_profile' );
		
		if ( isset( $tab ) && $tab['status'] == '1' ) {
			switch ( $id ) {
				case 'pg-messages':
					if ( $uid == $current_user->ID && $enable_private_profile!='1') :
                                            $profile_chats = new ProfileMagic_Chat();
                                            $unread_message_obj = json_decode($profile_chats->pm_messenger_notification_extra_data());
                                            //print_r($unread_message_obj->unread_threads);
						?>
					<audio id="msg_tone" src="<?php echo esc_url( $path ); ?>"></audio>
					<li class="pm-profile-tab pm-pad10 <?php echo esc_attr( $tab['class'] ); ?>"><a class="pm-dbfl" href="#<?php echo esc_attr( $tab['id'] ); ?>" onClick="pg_msg_open_tab()" ><?php esc_html_e( $tab['title'], 'profilegrid-user-profiles-groups-and-communities' ); ?><b id="unread_thread_count" class="thread-count-show"><?php echo !empty($unread_message_obj->unread_threads)?$unread_message_obj->unread_threads:''; ?></b></a></li>

						<?php
					endif;
					break;

				case 'pg-notifications':
					if ( $uid == $current_user->ID ) {
						$pm_notification     = new Profile_Magic_Notification();
						$unread_notification = $pm_notification->pm_get_user_unread_notification_count( $current_user->ID );
						if ( $unread_notification == 0 ) {
							$unread_notification       = '';
							$unread_notification_class = '';
						} else {
							$unread_notification_class = 'thread-count-show';
						}
						?>
								
										<li id="notification_tab" onclick="read_notification();" class="pm-profile-tab pm-pad10 <?php echo esc_attr( $tab['class'] ); ?>"><a class="pm-dbfl" href="#<?php echo esc_attr( $tab['id'] ); ?>" onclick="read_notification();"><?php esc_html_e( $tab['title'], 'profilegrid-user-profiles-groups-and-communities' ); ?><b id="unread_notification_count" class="<?php echo esc_attr( $unread_notification_class ); ?>"><?php echo esc_html( $unread_notification ); ?></b></a></li>
						<?php
					}
					break;
				case 'pg-about':
				case 'pg-groups':
				case 'pg-blog':
				case 'pg-friends':
					?>
					<li class="pm-profile-tab pm-pad10 <?php echo esc_attr( $tab['class'] ); ?>"><a class="pm-dbfl" href="#<?php echo esc_attr( $tab['id'] ); ?>"><?php esc_html_e( $tab['title'], 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
					<?php
					break;

				case 'pg-settings':
					if ( $uid == $current_user->ID ) {
						?>
							<li class="pm-profile-tab pm-pad10 <?php echo esc_attr( $tab['class'] ); ?>"><a class="pm-dbfl" href="#<?php echo esc_attr( $tab['id'] ); ?>"><?php esc_html_e( $tab['title'], 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
						<?php
					}
					break;

				default:
					do_action( 'profile_magic_profile_tab_link', $id, $tab, $uid, $gid, $primary_gid );
					break;

			}
		}
	}

	public function generate_profile_tab_content( $id, $tab, $uid, $gid, $primary_gid ) {
		if ( isset( $tab ) && $tab['status'] == '1' ) {
			switch ( $id ) {
				case 'pg-about':
				case 'pg-groups':
				case 'pg-blog':
				case 'pg-friends':
				case 'pg-settings':
				case 'pg-messages':
				case 'pg-notifications':
					do_action( 'profile_magic_profile_tab_content_' . $id, $id, $tab, $uid, $gid, $primary_gid );
					break;
				default:
					do_action( 'profile_magic_profile_tab_extension_content', $id, $tab, $uid, $gid, $primary_gid );
					break;

			}
		}
	}

	public function pg_check_premium_extension() {
		$classes         = array(
			'Profilegrid_Userid_Slug_Changer',
			'Profilegrid_Group_photos',
			'Profilegrid_Display_Name',
			'Profilegrid_Group_Fields',
			'Profilegrid_Bbpress',
			'Profilegrid_Geolocation',
			'Profilegrid_Front_End_Groups',
			'Profilegrid_Mailchimp',
			'Profilegrid_Woocommerce',
			'Profilegrid_Social_Connect',
			'Profilegrid_User_Content',
			'Profilegrid_Mycred',
			'Profilegrid_Woocommerce_Wishlist',
			'Profilegrid_Instagram_Integration',
			'Profilegrid_Group_Wall',
			'Profilegrid_Menu_Integration',
			'Profilegrid_Advanced_Woocommerce',
			'Profilegrid_EventPrime_Integration',
			'Profilegrid_Admin_Power',
			'Profilegrid_Group_Multi_Admins',
			'Profilegrid_Profile_Labels',
			'Profilegrid_Stripe_Payment',
			'Profilegrid_User_Profile_Status',
			'Profilegrid_Menu_Restriction',
			'Profilegrid_User_Photos_Extension',
			'Profilegrid_Woocommerce_Product_Integration',
			'Profilegrid_Demo_Content',
			'Profilegrid_Woocommerce_Subscription_Integration',
			'profilegrid_woocommerce_product_members_discount',
			'profilegrid_woocommerce_product_custom_tabs',
			'Profilegrid_Hero_Banner',
			'Profilegrid_Active_Members_Widget',
			'Profilegrid_User_Activities',
			'Profilegrid_Woocommerce_Product_Recommendations',
			'Profilegrid_Recent_Signup',
			'Profilegrid_User_Reviews_Extension',
			'Profilegrid_groups_slider',
			'Profilegrid_user_slider',
			'Profilegrid_featured_group',
			'Profilegrid_Profile_Completeness',
			'Profilegrid_widgets_privacy',
			'Profilegrid_Zapier_Integration',
			'Profilegrid_Mailpoet',
			'Profilegrid_elementor_content_restrictions',
			'Profilegrid_elementor_login_logout_widget',
			'Profilegrid_Our_Team',
			'Profilegrid_User_Bookmarks',
			'Profilegrid_popup_login',
                        'Profilegrid_elementor_groups_widget',
                        'profilegrid_woocommerce_custom_product_price',
                        'Profilegrid_Custom_Group_Slug',
                        'Profilegrid_Woocommerce_Product_Restrictions',
                        'Profilegrid_User_Invitation_Field',
                        'Profilegrid_Credit',
                        'Profilegrid_profile_visitor_details',
                        'Profilegrid_Turnstile_Antispam_Cloudflare',
                        'Profilegrid_F12_Integration'
		);
		$class_not_exist = array();
		foreach ( $classes as $class ) {
			if ( ! class_exists( $class ) ) {
				$class_not_exist[] = $class;
			}
		}
		return $class_not_exist;
	}

	public function pg_get_activate_extensions() {
		$free       = array(
			'Profilegrid_Woocommerce',
			'Profilegrid_Display_Name',
			'Profilegrid_Userid_Slug_Changer',
			'Profilegrid_Bbpress',
			'Profilegrid_EventPrime_Integration',
			'Profilegrid_Menu_Integration',
			'Profilegrid_Demo_Content',
			'Profilegrid_Hero_Banner',
			'Profilegrid_User_Activities',
			'Profilegrid_Recent_Signup',
			'Profilegrid_groups_slider',
			'Profilegrid_user_slider',
			'Profilegrid_featured_group',
			'Profilegrid_Zapier_Integration',
			'Profilegrid_Mailpoet',
			'Profilegrid_elementor_content_restrictions',
			'Profilegrid_Our_Team',
			'Profilegrid_User_Bookmarks',
			'Profilegrid_popup_login',
                        'Profilegrid_elementor_groups_widget'
		);
		$paid       = array(
			'Profilegrid_Group_photos',
			'Profilegrid_Group_Fields',
			'Profilegrid_Geolocation',
			'Profilegrid_Front_End_Groups',
			'Profilegrid_Mailchimp',
			'Profilegrid_Social_Connect',
			'Profilegrid_User_Content',
			'Profilegrid_Mycred',
			'Profilegrid_Woocommerce_Wishlist',
			'Profilegrid_Instagram_Integration',
			'Profilegrid_Group_Wall',
			'Profilegrid_Menu_Restriction',
			'Profilegrid_Advanced_Woocommerce',
			'Profilegrid_Admin_Power',
			'Profilegrid_Group_Multi_Admins',
			'Profilegrid_Profile_Labels',
			'Profilegrid_Stripe_Payment',
			'Profilegrid_User_Profile_Status',
			'Profilegrid_User_Photos_Extension',
			'Profilegrid_Woocommerce_Product_Integration',
			'Profilegrid_Woocommerce_Subscription_Integration',
			'profilegrid_woocommerce_product_members_discount',
			'profilegrid_woocommerce_product_custom_tabs',
			'Profilegrid_Active_Members_Widget',
			'Profilegrid_Woocommerce_Product_Recommendations',
			'Profilegrid_User_Reviews_Extension',
			'Profilegrid_Profile_Completeness',
			'Profilegrid_widgets_privacy',
			'Profilegrid_elementor_login_logout_widget',
                        'profilegrid_woocommerce_custom_product_price',
                        'Profilegrid_Custom_Group_Slug',
                        'Profilegrid_Woocommerce_Product_Restrictions',
                        'Profilegrid_User_Invitation_Field',
                        'Profilegrid_Credit',
                        'Profilegrid_profile_visitor_details',
                        'Profilegrid_Turnstile_Antispam_Cloudflare',
                        'Profilegrid_F12_Integration'
		);
		$free_exist = array();
		foreach ( $free as $fc ) {
			if ( class_exists( $fc ) ) {
				$free_exist[] = $fc;
			}
		}

		$paid_exist = array();
		foreach ( $paid as $pc ) {
			if ( class_exists( $pc ) ) {
				$paid_exist[] = $pc;
			}
		}

		return array(
			'free' => $free_exist,
			'paid' => $paid_exist,
		);
	}

	public function pg_group_card_html( $gid ) {
		$dbhandler    = new PM_DBhandler();
		$current_user = wp_get_current_user();
		$row          = $dbhandler->get_row( 'GROUPS', $gid );
		$options      = maybe_unserialize( $row->group_options );
		$pagenum      = filter_input( INPUT_GET, 'pagenum' );

		$pagenum    = isset( $pagenum ) ? absint( $pagenum ) : 1;
		$limit      = 10; // number of rows in page
		$offset     = ( $pagenum - 1 ) * $limit;
		$hide_users = $this->pm_get_hide_users_array();
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'pm_group',
				'value'   => sprintf( ':"%s";', $gid ),
				'compare' => 'like',
			),
			array(
				'key'     => 'rm_user_status',
				'value'   => '0',
				'compare' => '=',
			),

		);
		$user_query  = $dbhandler->pm_get_all_users_ajax( '', $meta_query, '', $offset, $limit, 'ASC', 'ID', $hide_users );
		$total_users = $user_query->get_total();
		$leaders     = array();
		if ( $row->is_group_leader != 0 ) {
			$leaders = $this->pg_get_group_leaders( $gid );
		}
		if ( $leaders == '' ) {
			$leaders = array();
		}
                $group_url        = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $gid);
				
		?>
			   <div class="pm-group-card-box pm-dbfl pm-border-bt">
		 <div class="pm-group-card pm-dbfl pm-border pm-bg pm-radius5">
			<div class="pm-group-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<a class="pm-group-cards-shortcode-title" href="<?php echo esc_url($group_url);?>">
                            <i class="fa fa-users" aria-hidden="true"></i>
                            <?php echo esc_html( $row->group_name ); ?>
                        </a>
			<?php

			if ( is_user_logged_in() && in_array( $current_user->ID, $leaders ) ) :
				$edit_group = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
				//$edit_group = add_query_arg( 'gid', $gid, $edit_group );
				$edit_group = add_query_arg( 'edit', '1', $edit_group );
				?>
                            <div class="pm-edit-group"><a href="<?php echo esc_url( $edit_group ); ?>" class="pm_button"><?php esc_html_e( 'Edit', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
			<?php endif; ?>
			</div>
			 <div class="pm-group-image pm-difl pm-border">
				  <?php echo wp_kses_post( $this->profile_magic_get_group_icon( $row ) ); ?>
			 </div>
			 <div class="pm-group-description pm-difl pm-bg pm-pad10 pm-border">
		  
				<?php
				if ( ! class_exists( 'Profilegrid_Group_Multi_Admins' ) ) :
					if ( isset( $leaders ) && ! empty( $leaders ) && $pagenum == 1 ) :
						
                                                $profile_url = $this->pm_get_user_profile_url($leaders['primary']);

						?>
								<div class="pm-card-row pm-dbfl">
																	<div class="pm-card-label pm-difl"><?php echo esc_html( $this->pm_get_group_admin_label( $gid ) ); ?></div>
									<div class="pm-card-value pm-difl pm-group-leader-small pm-difl">
										 <a href="<?php echo esc_url( $profile_url ); ?>">                          								 <?php
											echo get_avatar(
												$leaders['primary'],
												16,
												'',
												false,
												array(
													'class' => 'pm-infl',
													'force_display' => true,
												)
												  );
													?>
								 <?php echo wp_kses_post( $this->pm_get_display_name( $leaders['primary'], true ) ); ?>
										 </a>
								
										</div>
								 </div>
						<?php
				  endif;
							else :
								do_action( 'pm_show_multi_admins', $gid );
							endif;

							?>
		  
		  
				  
				  
				 <div class="pm-card-row pm-dbfl">
					<div class="pm-card-label pm-difl"><?php esc_html_e( 'Members', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
					<div class="pm-card-value pm-difl"><?php echo esc_html( $total_users ); ?></div>
				 </div>
				  
				 <?php if ( ! empty( $row->group_desc ) ) : ?>
				  <div class="pm-card-row pm-dbfl">
					<div class="pm-card-label pm-difl"><?php esc_html_e( 'Details', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
					<div class="pm-card-value pm-difl"><?php echo wp_kses_post( $row->group_desc ); ?></div>
				 </div>
				 <?php endif; ?>
				 <?php do_action( 'profile_magic_show_group_fields_option', $options ); ?>
			 </div>
		   </div>
			
			
		</div>              
		<?php
	}

	public function pm_check_user_exist_by_id( $user_id ) {
		 $dbhandler   = new PM_DBhandler();
		$current_user = wp_get_current_user();
		$user         = get_user_by( 'ID', $user_id );
		$access       = $this->profile_magic_check_profile_access_permission( $user_id );
		if ( $dbhandler->get_global_option_value( 'pm_enable_private_profile' ) == '1' ) {
			if ( $user_id != $current_user->ID ) {
				return false;
			}
		}
		if ( $access == false ) {
			return false;
		}
		return $user;
	}
        
        public function check_group_settion_tabcontent_lock($tab)
        {
            $return = false;
            switch($tab)
            {
                case 'display_name_pattern':
                    if(!class_exists('Profilegrid_Display_Name'))
                    {
                        $return = true;
                    }
                    break;
                case 'group_fields':
                    if(!class_exists('Profilegrid_Group_Fields'))
                    {
                        $return = true;
                    }
                    break;
                    
                case 'forums':
                    if(!class_exists('Profilegrid_Bbpress'))
                    {
                        $return = true;
                    }
                    break;
                
                case 'mailchimp':
                    if(!class_exists('Profilegrid_Mailchimp'))
                    {
                        $return = true;
                    }
                    break;
                    
                case 'woocommerce':
                    if(!class_exists('Profilegrid_Woocommerce') && !class_exists('Profilegrid_Advanced_Woocommerce'))
                    {
                        $return = true;
                    }
                    break;
                default:
                    $return = false;
                    break;
            }
            
            return $return;
        }
        
        public function pg_get_single_field_value($id,$uid,$show_label= false)
        {
            $dbhandler   = new PM_DBhandler();
            $field = $dbhandler->get_row('FIELDS', $id);
            $field_value =  $this->profile_magic_get_user_field_value( $uid, $field->field_key, $field->field_type );
				$value       = '';
				if ( !empty( $field_value ) ) {
					if ( $field->field_options != '' ) {
						$field_options = maybe_unserialize( $field->field_options );
					}

					

                            $field_value = maybe_unserialize( $field_value );
					if ( $field->field_type=='checkbox' || $field->field_type=='repeatable_text' ) {
						if ( !is_array( $field_value ) ) {
							$field_value = explode( ',', $field_value );
						}
					}

					if ( is_array( $field_value ) ) {
						if ( $field->field_type=='address' ) {
							$address_value = '';
							$options       = maybe_unserialize( $field->field_options );
							foreach ( $field_value as $key=>$fv ) {
								if ( !isset( $options[ $key ] ) ) {
									unset( $field_value[ $key ] );
								} else {
									$address_value .= $field_value[ $key ];
								}
							}

							
						}
						$repeat_field = '';
						foreach ( $field_value as $val ) {
								$repeat_field .= $val;
							if ( $val=='chl_other' ) {
								continue;
							}
							if ( $val!='' ) {
									$value .= '<div class="pm-field-multiple-value pm-difl pm-radius5">' . $val . '</div>';
							}
						}
                                                
					} else {
						$value = $field_value;
					}
					?>
			
		
			<div class="pm-user-description-row pm-dbfl pm-border">
                            <?php if($show_label==true){ ?>
                            <div class="pm-card-label pm-difl">
                            <?php
                            if ( isset( $field ) && $field->field_icon!=0 ) :
                                    echo wp_get_attachment_image( $field->field_icon, array( 16, 16 ), true, false );
                            endif;
                            ?>
                            <?php echo esc_html( $field->field_name ); ?>
                            </div>
                            <?php
                            }
                            
                            switch ( $field->field_type ) {
                                case 'youtube':
                                    preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $value, $match );
                                    $youtube_id = $match[1];
									?>
                                    
                                    <div class="pm-card-value pm-difl <?php echo 'pm_collapsable_' . esc_attr($field->field_type); ?>">
                                        <iframe style="border:
                                        <?php
                                        if ( isset( $field_options['video_frame_border'] ) ) {
											echo esc_attr( $field_options['video_frame_border'] . 'px solid' );}
										?>
                                            " frameborder="
                                            <?php
											if ( isset( $field_options['video_frame_border'] ) ) {
																						echo esc_attr( $field_options['video_frame_border'] );}
											?>
                                            " width="<?php echo ( $field_options['video_width']!='' )?esc_attr($field_options['video_width']):640; ?>" height="<?php echo ( $field_options['video_height']!='' )?esc_attr($field_options['video_height']):360; ?>" type="text/html" src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_id ); ?>?mute=1&autoplay=<?php echo ( isset( $field_options['video_auto_play'] ) && $field_options['video_auto_play']=='1' )?'1':'0'; ?>&fs=<?php echo ( isset( $field_options['video_fullscreen'] ) && $field_options['video_fullscreen']=='1' )?'1':'0'; ?>&controls=<?php echo ( isset( $field_options['video_player_control'] ) && $field_options['video_player_control']=='1' )?'1':'0'; ?>&loop=<?php echo ( isset( $field_options['video_loop'] ) && $field_options['video_loop']=='1' )?'1&playlist=' . esc_attr($youtube_id):'0'; ?>" allowfullscreen allowautoplay></iframe>
                                    </div>
                                    <?php
                                    break;

                                case 'mixcloud':
                                    $feed = rawurlencode( substr( $value, strpos( $value, 'mixcloud.com' )+12 ) );

									?>
                                    
                                    <div class="pm-card-value pm-difl <?php echo 'pm_collapsable_' . esc_attr($field->field_type); ?>">
                                        <iframe style="border:<?php echo esc_attr( $field_options['mixcloud_frame_border'] . 'px solid' ); ?>" width="<?php echo ( $field_options['mixcloud_width']!='' )?esc_attr($field_options['mixcloud_width']):640; ?>" height="<?php echo ( $field_options['mixcloud_height']!='' )?esc_attr($field_options['mixcloud_height']):360; ?>" src="https://www.mixcloud.com/widget/iframe/?hide_cover=
                                                                         <?php
																			if ( isset( $field_options['mixcloud_hide_cover'] ) ) {
																				echo esc_attr( $field_options['mixcloud_hide_cover'] );}
																			?>
                                            &light=
                                            <?php
											if ( isset( $field_options['mixcloud_light'] ) ) {
																						echo esc_attr( $field_options['mixcloud_light'] );}
											?>
                                            &mini=
                                            <?php
											if ( isset( $field_options['mixcloud_mini'] ) ) {
																						echo esc_attr( $field_options['mixcloud_mini'] );}
											?>
                                            &hide_artwork=
									<?php
									if ( isset( $field_options['mixcloud_hide_artwork'] ) ) {
											echo esc_attr( $field_options['mixcloud_hide_artwork'] );}
									?>
&autoplay=
                                            <?php
											if ( isset( $field_options['mixcloud_auto_play'] ) ) {
												echo esc_attr( $field_options['mixcloud_auto_play'] );}
											?>
&feed=<?php echo esc_attr( $feed ); ?>" frameborder="<?php echo esc_attr( $field_options['mixcloud_frame_border'] ); ?>" allow="autoplay"></iframe>
                                    </div>
                                    <?php
                                    break;

                                case 'soundcloud':
									?>
                                    
                                    <div class="pm-card-value pm-difl <?php echo esc_attr( 'pm_collapsable_' . $field->field_type ); ?>">
                                       <iframe style="border:<?php echo esc_attr( $field_options['soundcloud_frame_border'] ) . 'px solid'; ?>" width="<?php echo ( $field_options['soundcloud_width']!='' )?esc_attr( $field_options['soundcloud_width'] ):640; ?>" height="<?php echo ( $field_options['soundcloud_height']!='' )?esc_attr( $field_options['soundcloud_height'] ):360; ?>" scrolling="no" frameborder="<?php echo esc_attr( $field_options['soundcloud_frame_border'] ); ?>" allow="autoplay" src="https://w.soundcloud.com/player/?url=<?php echo esc_url( $value ); ?>&color=<?php echo esc_attr( rawurlencode( $field_options['soundcloud_play_button_color'] ) ); ?>&auto_play=<?php echo ( isset( $field_options['soundcloud_auto_play'] ) && $field_options['soundcloud_auto_play']=='1' )?'true':'false'; ?>&buying=<?php echo ( isset( $field_options['soundcloud_buy_button'] ) && $field_options['soundcloud_buy_button']=='1' )?'true':'false'; ?>&sharing=<?php echo ( isset( $field_options['soundcloud_share_button'] ) && $field_options['soundcloud_share_button']=='1' )?'true':'false'; ?>&download=<?php echo ( isset( $field_options['soundcloud_download_button'] ) && $field_options['soundcloud_download_button']=='1' )?'true':'false'; ?>&show_artwork=<?php echo ( isset( $field_options['soundcloud_show_artwork'] ) && $field_options['soundcloud_show_artwork']=='1' )?'true':'false'; ?>&show_playcount=<?php echo ( isset( $field_options['soundcloud_show_playcount'] ) && $field_options['soundcloud_show_playcount']=='1' )?'true':'false'; ?>&show_user=<?php echo ( isset( $field_options['soundcloud_show_user'] ) && $field_options['soundcloud_show_user']=='1' )?'true':'false'; ?>&single_active=<?php echo ( isset( $field_options['soundcloud_single_active'] ) && $field_options['soundcloud_single_active']=='1' )?'true':'false'; ?>"></iframe>
                                    </div>
                                    <?php
                                    break;

                                case 'user_url':
                                case 'facebook':
                                case 'google':
                                case 'twitter':
                                case 'linked_in':
                                case 'instagram':
                                    ?>
                                     
                                    <div class="pm-card-value pm-difl <?php echo 'pm_collapsable_' . esc_attr($field->field_type); ?>"><a href="<?php echo esc_url( $value ); ?>" target="_blank"><?php echo wp_kses_post( $value ); ?></a></div>
                                    <?php
                                    break;
                                case 'term_checkbox':
                                    $html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'">'. esc_html__('Yes','profilegrid-user-profiles-groups-and-communities').'</div>';
                                    echo wp_kses_post($html);
                                    break;
                                default:
                                    $html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'">'.wp_kses_post( $value ).'</div>';
                                    echo wp_kses_post(apply_filters('pm_field_card_value_html_filter',$html,$uid,$field->field_type,$value,$field));
                                    break;

                            }
                            ?>
                        </div>
					<?php
            }
                                
        }
        
        public function pg_check_group_limit_available($gid)
        {
            $dbhandler   = new PM_DBhandler();
            $pmrequests = new PM_request;
            $limit = $dbhandler->get_value('GROUPS','group_limit',$gid);
            $is_group_limit = $dbhandler->get_value('GROUPS','is_group_limit',$gid);
            if($is_group_limit==1)
            { 
                    $meta_query_array = $pmrequests->pm_get_user_meta_query(array('gid'=>$gid));
                    $user_query = $dbhandler->pm_get_all_users_ajax('',$meta_query_array);
                    $total_users_in_group = $user_query->get_total();
            
                    if($limit > $total_users_in_group)
                    {
                            return true;	
                    }
                    else
                    {
                            return false;
                    }
            }
            else
            {
                return true;
            }
        }
        
        public function pg_get_hide_groups_on_group_card()
        {
                $dbhandler = new PM_DBhandler();
                $groups = $dbhandler->get_all_result('GROUPS');
                $hide = array();
                if(isset($groups) && !empty($groups))
                {
                    foreach($groups as $group)
                    {
                        $option    = 'pm_show_group_on_groups_page_' . $group->id;
                        if ( $dbhandler->get_global_option_value( $option, '1' ) == '0' ) {
                            $hide[] = $group->id;
                        }
                    }
                }
		return $hide;
        }
        
        public function pg_get_user_groups_icon( $uid ) {
		$dbhandler = new PM_DBhandler();

		$user_groups = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$gid_array   = $this->pg_filter_users_group_ids( $user_groups );
		if ( ! empty( $gid_array ) ) {
			if ( count( $gid_array ) == 1 ) {
				$class = 'pm-single-group-badge';
			} else {
				$class = '';}
			echo '<div class="pg-user-joined-groups">';
			$gid_array = array_reverse( $gid_array );
			$i         = 0;
			foreach ( $gid_array as $gid ) {
				if ( $i >= 4 ) {
					continue;}
				$group_page_url  = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
				$group_page_link = $group_page_url;
				$groupinfo       = $dbhandler->get_row( 'GROUPS', $gid );
				if ( ! empty( $groupinfo ) ) {
					?>
				<div class="pg-user-joined-group">
                                    <?php echo wp_kses_post( $this->profile_magic_get_group_icon( $groupinfo, 'user-profile-image', '',array(30,30) ) ); ?>
                                </div>
					
					<?php
				}
				$i++;
			}
			echo '</div>';
		}
	}
        
        public function pg_get_user_groups_icon_return( $uid ) {
			
		ob_start();
		$this->pg_get_user_groups_icon($uid);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
        
        public function pg_get_user_groups_icon_with_more( $uid ) {
		$dbhandler = new PM_DBhandler();
                ob_start();
		$user_groups = $this->profile_magic_get_user_field_value( $uid, 'pm_group' );
		$gid_array   = $this->pg_filter_users_group_ids( $user_groups );
		if ( ! empty( $gid_array ) ) {
			if ( count( $gid_array ) == 1 ) {
				$class = 'pm-single-group-badge';
			} else {
				$class = '';}
			echo '<div class="pg-user-joined-groups">';
			$gid_array = array_reverse( $gid_array );
                        $total_groups = count($gid_array);
			$i         = 0;
			foreach ( $gid_array as $gid ) {
				if ( $i >= 3 ) {
					continue;}
				$group_page_url  = $this->profile_magic_get_frontend_url( 'pm_group_page', '', $gid );
				$group_page_link = $group_page_url;
				$groupinfo       = $dbhandler->get_row( 'GROUPS', $gid );
				if ( ! empty( $groupinfo ) ) {
					?>
				<div class="pg-user-joined-group">
                                    <?php echo wp_kses_post( $this->profile_magic_get_group_icon( $groupinfo, 'user-profile-image', '',array(30,30) ) ); ?>
                                </div>
					
					<?php
				}
				$i++;
			}
                        if($total_groups > 3){?>
                            <div class="pg-more-groups">+ <?php echo esc_html($total_groups - 3);?> <?php esc_html_e('more groups','profilegrid-user-profiles-groups-and-communities');?></div>
                            <?php
                        }
			echo '</div>';
		}
                $html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
        
        public function pg_date_format(){
            $dbhandler  = new PM_DBhandler();
            $date_format = $dbhandler->get_global_option_value( 'pm_date_format', '0' );
            if(empty($date_format) || $date_format=='0'){
                $date_format = 'Y-m-d';
            }
            return $date_format;
        }
        
        public function pg_wp_date_format_php_to_js( $dateFormat='') {
                if(empty($dateFormat)){
                    $dateFormat = $this->pg_date_format();
                }
		$chars = array(
			// Day
			'd' => 'dd',
			'j' => 'd',
			'l' => 'DD',
			'D' => 'D',
			// Month
			'm' => 'mm',
			'n' => 'm',
			'F' => 'MM',
			'M' => 'M',
			// Year
			'Y' => 'yy',
			'y' => 'y',
		);
		return strtr( (string) $dateFormat, $chars );
	}
        
        public function pg_wp_date_format_error(){
            $dateFormat = $this->pg_date_format();
            $chars = array(
                // Day
		'd' => 'dd',
		'j' => 'dd',
		'l' => 'dd',
		'D' => 'dd',
		// Month
		'm' => 'mm',
		'n' => 'mm',
		'F' => 'mm',
		'M' => 'mm',
		// Year
		'Y' => 'yyyy',
		'y' => 'yyyy',
            );
            $date_format = strtr( (string) $dateFormat, $chars );
            return esc_html__(sprintf("Please enter a valid date (%s format).",$date_format),'profilegrid-user-profiles-groups-and-communities');
        }

	// class end
        
        public function pm_get_group_admin_flag( $gid ) {
		$dbhandler   = new PM_DBhandler();
		$group       = $dbhandler->get_row( 'GROUPS', $gid );
		$admin_flag = 0;
		if ( $group->group_options != '' ) {
			$group_options = maybe_unserialize( $group->group_options );
			if ( isset( $group_options['flag_group_leader'] ) && trim( $group_options['flag_group_leader'] ) == 1 ) {
				$admin_flag = 1;
			}
		}
                return $admin_flag;
	}
        
        public function pg_allowed_html_wp_kses()
        {
            $allowed_html = array(
                
                'table' => array(
                    'class' => array(),
                    'border' => array(),
                    'cellspacing' => array(),
                    'cellpadding' => array(),
                    'width' => array(),
                    'style' => array(),
                ),
                'tbody' => array(),
                'tr' => array(
                    'valign' => array(),
                ),
                'th' => array(
                    'scope' => array(),
                    'class' => array(),
                ),
                'td' => array(
                    'class' => array(),
                    'colspan' => array(),
                    'style' => array(),
                    'align' => array(),
                    'valign' => array(),
                ),
                'label' => array(
                    'for' => array(),
                ),
                'span' => array(
                    'class' => array(),
                    'style' => array(),
                    'aria-current' => true,
                    'data-*' => true, // Allowing data attributes
                ),
                'br' => array(),
                'input' => array(
                    'type' => array(),
                    'name' => array(),
                    'id' => array(),
                    'class' => array(),
                    'value' => array(),
                    'required' => array(),
                ),
                'textarea' => array(
                    'class' => array(),
                    'rows' => array(),
                    'cols' => array(),
                    'name' => array(),
                    'id' => array(),
                    'style' => array(),
                    'aria-hidden' => array(),
                    'data-*' => true, // Allowing data attributes
                ),
                'a' => array(
                    'href' => array(),
                    'target' => array(),
                    'class' => array(),
                    'id' => array(),
                    'data-*' => true, // Allowing data attributes
                ),
                'h1' => array(
                    'style' => array(),
                ),
                'div' => array(
                    'id' => array(),
                    'title'=>array(),
                    'class' => array(),
                    'style' => array(),
                    'aria-hidden' => array(),
                    'data-*' => true, // Allowing data attributes
                ),
                'button' => array(
                    'type' => array(),
                    'id' => array(),
                    'class' => array(),
                    'aria-label' => array(),
                    'aria-pressed' => array(),
                    'pagenum' => array(),
                    'data-*' => true, // Allowing data attributes
                ),
                'strong' => array(),
                'em' => array(),
                'i' => array(
                    'class' => array(),
                ),
                'p' => array(
                    'class' => array(),
                ),
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                    'width' => array(),
                    'height' => array(),
                    'style' => array(),
                ),    
                'svg' => array(
                    'xmlns' => array(),
                    'height' => array(),
                    'viewBox' => array(),
                    'width' => array(),
                    'fill' => array(),
                ),
                'path' => array(
                    'd' => array(),
                    'fill' => array(),
                ),
                'img' => array(
                    'width' => array(),
                    'height' => array(),
                    'src' => array(),
                    'class' => array(),
                    'alt' => array(),
                    'size' => array(),
                    'default' => array(),
                    'force_default' => array(),
                    'rating' => array(),
                    'scheme' => array(),
                    'processed_args' => array(),
                    'extra_attr' => array(),
                    'force_display' => array(),
                    'loading' => array(),
                    'fetchpriority' => array(),
                    'decoding' => array(),
                    'found_avatar' => array(),
                    'url' => array(),
                    'srcset' => array(),
                    'sizes' => array(),
                    'style' => array(),
                ),
                'ul' => array(
                    'style' => array(),
                    'class' => array(),
                ),
                'li' => array(
                    'class' => array(),
                    'data-*' => true, // Allowing data attributes
                ),
            );
            return $allowed_html;
        }
        
        public function pg_delete_attachment($attachment_id) 
        {

            // Check if the attachment belongs to the current user
            $attachment_owner = get_post_field('post_author', $attachment_id);
            $current_user_id = get_current_user_id();

            if (current_user_can('delete_post', $attachment_id) || $attachment_owner === $current_user_id) {
                // Delete the attachment
                $delete = wp_delete_attachment($attachment_id, true);
            } else {
                $delete = false;
            }
            
            return $delete;
        }
        
        public function pg_file_fullpath_validation($fullpath) {
            // Step 1: Block URLs
            if ( filter_var($fullpath, FILTER_VALIDATE_URL) ) {
                return false;
            }
            // Step 2: Block path traversal and absolute paths
            if ( strpos($fullpath, '..') !== false || preg_match('/^(\/|[A-Z]:\\\\)/i', $fullpath) ) {
                // Note: You might want to allow absolute paths depending on use case
            }
            // Step 3: Resolve realpath
            $realpath = realpath($fullpath);
            if ($realpath === false) {
                return false;
            }
            // Step 4: Normalize slashes for Windows
            $realpath_normalized = str_replace('\\', '/', $realpath);
            $upload_dir = str_replace('\\', '/', wp_get_upload_dir()['basedir']);

            // Step 5: Ensure realpath is within uploads dir
            if ( strpos($realpath_normalized, $upload_dir) !== 0 ) {
                return false;
            }
            return true;
        }


}
