<?php
class PM_Export_Import {

	public function pm_generate_csv( $type, $post, $filename ) {
        ob_clean();
        switch ( $type ) {
            case 'USERS':
                $csvFile = $this->pm_generate_user_csv( $post );
                break;
            default:
                $csvFile = $this->pm_generate_user_csv( $post );
		}

        //Getting the text generated to download
        $generatedDate = gmdate( 'd-m-Y His' );
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '-' . $generatedDate . '.csv"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        echo wp_kses_post($csvFile);                                              //Whatever is echoed here will be in the csv file
        exit;
	}

	public function pm_generate_options_json( $filename ) {
         ob_clean();
        $dbhandler     = new PM_DBhandler();
        $additional    = " `option_name` LIKE 'pg_%' or `option_name` LIKE 'pm_%'";
        $options       = $dbhandler->get_all_result( 'WP_OPTION', array( 'option_name', 'option_value' ), 1, 'results', 0, false, $sort_by = 'option_id', false, $additional, 'ARRAY_A' );
        $data          = wp_json_encode( $options );
        $generatedDate = gmdate( 'd-m-Y His' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '-' . $generatedDate . '.json"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        echo wp_kses_post($data);                                              //Whatever is echoed here will be in the csv file
        exit;
	}

	public function pm_generate_user_csv( $post ) {
         $csv_output = '';

		if ( isset( $post['pm_separator'] ) ) {
			$seprator = $post['pm_separator'];
		} else {
			$seprator = ',';
		}

        $csv_output .= $this->pm_get_user_fields( $post, $seprator );
		$csv_output .= $this->pm_get_user_fields_value( $post, $seprator );
        $csv_output = apply_filters("profilegrid_generate_user_csv_output", $csv_output, $post ) ;

		return $csv_output;
	}

	public function pm_get_user_fields_value( $post, $sep ) {
		$csv_output       = '';
		$dbhandler        = new PM_DBhandler();
		$meta_query_array = array();
		$gids             = $post['pm_groups'];
		$fids             =$post['pm_fields'];
		if ( !empty( $gids ) ) {
            $meta_query_array['relation'] = 'OR';
            foreach ( $gids as $gid ) {
                $meta_query_array[] =array(
					'key'     => 'pm_group',
					'value'   => sprintf( ':"%s";', $gid ),
					'compare' => 'like',
				);
			}
		}
		$users =  $dbhandler->pm_get_all_users( '', $meta_query_array );
		if ( count( $users )>0 ) {
			foreach ( $users as $user ) {
				$csv_output .= $this->pm_get_csv_single_user_row( $user->ID, $fids, $sep );
				$csv_output .= "\n";
			}
		}
		return $csv_output;
	}

	public function pm_get_csv_single_user_row( $uid, $fids, $sep ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
		$csv_output = '';
		if ( count( $fids )>0 ) {

			foreach ( $fids as $fid ) {
				$key         = $dbhandler->get_value( 'FIELDS', 'field_key', $fid );
				$field_value = $pmrequests->profile_magic_get_user_field_value( $uid, $key );
				$field_value = maybe_unserialize( $field_value );
				if ( is_array( $field_value ) ) {
					$value = implode( ',', $field_value );
				} else {
					$value = $field_value;
				}
				$value       = $this->escape_csv( $value );
				$csv_output .='"' . $value . '"' . $sep;
			}
		}
		$csv_output = apply_filters('Profilegrid_customize_export_single_user_row', $csv_output, $uid);
		return rtrim( $csv_output, $sep );
	}

    public function escape_csv( $payload ) {
		$payload = (string) $payload;
		// Protect the quoted cell boundary and spreadsheet formula interpretation.
		if ( preg_match( '/^[\x00-\x20]*[=+@|%\-]/', $payload ) || preg_match( '/^[\t\r\n]/', $payload ) ) {
			$payload = "'" . $payload;
		}
		return str_replace( '"', '""', $payload );
    }

	public function pm_get_user_fields( $post, $sep ) {
         $csv_output = '';
		$dbhandler   = new PM_DBhandler();
		$ids         = implode( ',', $post['pm_fields'] );
		$additional  = "field_id in($ids)";
		$fields      = $dbhandler->get_all_result( 'FIELDS', '*', 1, 'results', 0, false, null, false, $additional );
		if ( count( $fields ) >0 ) {
			foreach ( $fields as $field ) {
				$csv_output .= '"' . $this->escape_csv( $field->field_name ) . '"' . $sep;
			}
		}
		$csv_output = apply_filters('Profilegrid_customize_export_user_fields', $csv_output);
		$csv_output  = rtrim( $csv_output, $sep );
		$csv_output .= "\n";

		return $csv_output;
	}

	public function pm_import_users_from_csv( $post ) {
                $dbhandler = new PM_DBhandler();
                $html      = '';
		$gid               = $post['import_pm_group'];
		$sid               = $post['pm_import_section'];
		$delimiter         = $post['pm_separator'];

		$filepath = get_attached_file( $post['attachment_id'] ); // Full path
		$file     = new SplFileObject( $filepath );
		$file->seek( 0 );
		$headings = explode( $post['pm_separator'], $file->current() );
		$i        = 0;
		for ( $i=0;count( $headings )>$i;$i++ ) {
			if ( $post[ "pm_map_field_$i" ]==0 ) {

				$post[ "pm_map_field_with_$i" ] = $this->pm_create_new_field( $gid, $sid, $headings[ $i ] );
			}
		}

		$message = '';
		$skipped = 0;
		$created = 0;
		$updated = 0;
		while ( $data = $file->fgetcsv( $delimiter ) ) :

			$log = $this->pm_import_user( $data, $post, $headings );
			if ( isset( $log['message'] ) ) {
				$message .= '<li>' . $log['message'] . '</li>';
			}
			if ( isset( $log['status'] ) && $log['status']==0 ) {
				$skipped = $skipped +1;
			}
			if ( isset( $log['status'] ) && $log['status']==1 ) {
				$created = $created +1;
			}
			if ( isset( $log['status'] ) && $log['status']==2 ) {
				$updated = $updated +1;
			}
			unset( $log );
			$i++;
	  endwhile;

		$html .= '<div class="pm-import-result"><div class="pm-import-note">';
		$html .= '<strong>' . esc_html__( 'Import Process Complete', 'profilegrid-user-profiles-groups-and-communities' ) . '</strong><br />';
		$html .= '<i class="fa fa-user-plus" aria-hidden="true"></i> ' . esc_html__( 'New Users Created', 'profilegrid-user-profiles-groups-and-communities' ) . ' - ' . $created . '<br/>';
		$html .= '<i class="fa fa-user" aria-hidden="true"></i> ' . esc_html__( 'Existing Users Updated', 'profilegrid-user-profiles-groups-and-communities' ) . ' - ' . $updated . '<br/>';
		$html .= '<i class="fa fa-user-times" aria-hidden="true"></i> ' . esc_html__( 'Total Users Skipped', 'profilegrid-user-profiles-groups-and-communities' ) . ' - ' . $skipped . '<br/></div>';
		$html .= '<b>' . esc_html__( 'Import Details', 'profilegrid-user-profiles-groups-and-communities' ) . '</b><br/>';
		$html .= '<div class="pm-import-details"><ul>';
		$html .= wp_kses_post( $message );
		$html .= '</ul></div></div>';
                echo wp_kses_post( $html );
	}

	public function pm_create_new_field( $gid, $sid, $heading ) {
         $dbhandler   = new PM_DBhandler();
        $pmsanitizer  = new PM_sanitizer();
        $pmrequest    = new PM_request();
        $lastrow      = $dbhandler->get_all_result( 'FIELDS', 'field_id', 1, 'var', 0, 1, 'field_id', 'DESC' );
        $ordering     = $lastrow + 1;
        $field_key    = $pmrequest->get_field_key( 'text', $ordering );
        $field_name   = $pmsanitizer->get_sanitized_groups_field( 'field_name', $heading );
        $field_option = 'a:15:{s:17:"place_holder_text";s:0:"";s:19:"css_class_attribute";s:0:"";s:14:"maximum_length";s:0:"";s:13:"default_value";s:0:"";s:12:"first_option";s:0:"";s:21:"dropdown_option_value";s:0:"";s:18:"radio_option_value";a:1:{i:0;s:0:"";}s:14:"paragraph_text";s:0:"";s:7:"columns";s:0:"";s:4:"rows";s:0:"";s:18:"term_and_condition";s:0:"";s:18:"allowed_file_types";s:0:"";s:12:"heading_text";s:0:"";s:11:"heading_tag";s:2:"h1";s:5:"price";s:0:"";}';
        $field_data   = array(
			'field_name'          =>$field_name,
			'field_type'          =>'text',
			'field_options'       =>$field_option,
			'field_icon'          =>0,
			'associate_group'     =>$gid,
			'associate_section'   =>$sid,
			'show_in_signup_form' =>0,
			'is_required'         =>0,
			'ordering'            =>$ordering,
			'field_key'           =>$field_key,
		);
        $field_arg    = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' );
        $fid          = $dbhandler->insert_row( 'FIELDS', $field_data, $field_arg );

        return $fid;
	}
	public function pm_import_user( $data, $post, $headings ) {
         $uemail = $data[ $post['pm_map_user_email_field'] ];
		$uname   = $data[ $post['pm_map_user_name_field'] ];
		$gid     = $post['import_pm_group'];
		$sid     = $post['pm_import_section'];
		if ( empty( $uemail ) ) {
			$log['status']  = 0;
			$log['message'] = __( 'User is skipped due to empty record in CSV', 'profilegrid-user-profiles-groups-and-communities' );
			return $log;
		}
		$dbhandler   = new PM_DBhandler();
		$pmsanitizer = new PM_sanitizer();
		$pmrequest   = new PM_request();
		$user_email  = $pmsanitizer->get_sanitized_frontend_field( 'user_email', $uemail );

		$user_role = $dbhandler->get_value( 'GROUPS', 'associate_role', $gid, 'id' );
		$user_name = $pmsanitizer->get_sanitized_frontend_field( 'user_login', $uname );
		$password  = $pmrequest->profile_magic_generate_password();
		$log       = array();
		$userexist = email_exists( $user_email );
		if ( $userexist ) {
			if ( isset( $post['update_existing_users'] ) && $post['update_existing_users']==1 ) {
				$user_id       = $userexist;
				$log['status'] = 2;
			} else {
				// return log
				$log['status']  = 0;
				// translators: %s: user email.
				$log['message'] = sprintf( __( 'There\'s already a user with <b>%s</b> email. So we skipped it.', 'profilegrid-user-profiles-groups-and-communities' ), $user_email );
				return $log;
			}
		} else {

			if ( username_exists( $user_name ) ) {
				$user_name = $user_name . $pmrequest->profile_magic_generate_password();
				$user_name = $pmsanitizer->get_sanitized_frontend_field( 'user_login', $user_name );
			}

			if ( is_email( $user_email ) ) {

				$user_id = $dbhandler->pm_add_user( $user_name, $password, $user_email, $user_role );

			} else {
				$log['status']  = 0;
				// translators: %s: user email.
				$log['message'] = sprintf( __( 'Invalid Email format for User <b>%s</b>', 'profilegrid-user-profiles-groups-and-communities' ), $uemail );
				return $log;
			}
			if ( is_numeric( $user_id ) ) :

				delete_user_meta( $user_id, 'user_pass' );
				$log['status'] = 1;
			else :
				$log['status']  = 0;
				// translators: 1: username, 2: email.
				$log['message'] = sprintf( __( 'User not created with following username <b>%1$s</b> and email <b>%2$s<b>', 'profilegrid-user-profiles-groups-and-communities' ), $user_name, $user_email );
				return $log;
			endif;
		}

            $ugids       = $pmrequest->profile_magic_get_user_field_value( $user_id, 'pm_group' );
            $user_groups = $pmrequest->pg_filter_users_group_ids( $ugids );
		if ( is_array( $user_groups ) ) {
			$gid_array = $user_groups;
		} else {
			if ( $user_groups != '' && $user_groups != null ) {
				$gid_array = array( $user_groups );
			} else {
				$gid_array = array();
			}
		}

            $gid_array = array_merge( $gid_array, array( $gid ) );
            update_user_meta( $user_id, 'pm_group', $gid_array );

		update_user_meta( $user_id, 'rm_user_status', '0' );
		for ( $i=0;$i<count( $data );$i++ ) {
			$field_id   = $post[ "pm_map_field_with_$i" ];
			$field_key  = $dbhandler->get_value( 'FIELDS', 'field_key', $field_id, 'field_id' );
			$field_type = $dbhandler->get_value( 'FIELDS', 'field_type', $field_id, 'field_id' );
			if ( $field_type=='address' && $data[ $i ]!='' ) {
				$address_array = explode( ',', $data[ $i ] );
				$part          = count( $address_array );
				switch ( $part ) {
					case '6':
						$field_value['address_line_1'] = $address_array[0];
						$field_value['address_line_2'] = $address_array[1];
						$field_value['city']           = $address_array[2];
						$field_value['state']          = $address_array[3];
						$field_value['country']        = $address_array[4];
						$field_value['zip_code']       = $address_array[5];
						break;
					case '5':
						$field_value['address_line_1'] = $address_array[0];
						$field_value['city']           = $address_array[1];
						$field_value['state']          = $address_array[2];
						$field_value['country']        = $address_array[3];
						$field_value['zip_code']       = $address_array[4];
					    break;
					case '4':
						$field_value['address_line_1'] = $address_array[0];
						$field_value['city']           = $address_array[1];
						$field_value['state']          = $address_array[2];
						$field_value['country']        = $address_array[3];
						break;
					default:
						$field_value['address_line_1'] = $data[ $i ];
					    break;
				}
				$value      = maybe_serialize( $field_value );
				$data[ $i ] = $value;
				if ( class_exists( 'Profilegrid_Geolocation_Public' ) ) {
					 $gelocation = new Profilegrid_Geolocation_Public( 'profilegrid-user-profiles-groups-and-communities', '1.0' );
					 $address    = implode( ' ', $address_array );
					 $address    = rawurlencode( $address );
					 $gelocation->pg_update_user_lat_long_using_address( $address, $user_id );
				}

				unset( $field_value );
				unset( $address_array );

			}

			if ( $field_type=='url' && $data[ $i ]!='' ) {
				$url_array = explode( ',', $data[ $i ] );
				$url_part  = count( $url_array );
				switch ( $url_part ) {
					case '2':
						$field_value['text'] = $url_array[0];
						$field_value['url']  = $url_array[1];
						break;
					default:
						$field_value['text'] = $data[ $i ];
						$field_value['url']  = $data[ $i ];
						break;
				}
				$value      = maybe_serialize( $field_value );
				$data[ $i ] = $value;
				unset( $field_value );
				unset( $url_array );
			}

			if ( $field_type=='user_url' || $field_type=='first_name' || $field_type=='last_name' ||$field_type== 'user_email' ) {
				wp_update_user(
                    array(
						'ID'       => $user_id,
						$field_key => $data[ $i ],
                    )
                );
			}

			update_user_meta( $user_id, $field_key, $data[ $i ] );
		}
		return $log;

	}

	public function pm_generate_mapping_table( $post ) {
         $dbhandler = new PM_DBhandler();
        $gid        = $post['import_pm_group'];
        $sections   = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid'=>$gid ) );
        $fields     = $dbhandler->get_all_result( 'FIELDS', '*', array( 'associate_group'=>$gid ) );
        $filepath   = get_attached_file( $post['attachment_id'] ); // Full path
        $file       = new SplFileObject( $filepath );
        $file->seek( 0 );
        $headings = explode( $post['pm_separator'], $file->current() );?>
        <div class="uimrow">
            <div class="uimfield">
					<?php esc_html_e( 'Section', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput pm_select_required">
                    <select name="pm_import_section" id="pm_import_section">
					  <?php
						foreach ( $sections as $section ) {
							?>
                        <option value="<?php echo esc_attr( $section->id ); ?>" 
                                                  <?php
													if ( !empty( $row ) ) {
														selected( $row->associate_section, $section->id );}
													?>
                        ><?php echo esc_html( $section->section_name ); ?></option>
							<?php
						}

						?>
                    </select>
                  <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php esc_html_e( 'Step 5: Select a section inside this Group where all imported fields will go. You can later redistribute the fields among different sections in Fields Manager.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div> 
        </div>

        <div class="uimrow">
            <div class="uimfield">
					<?php esc_html_e( 'User Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput pm_select_required">
                    <select name="pm_map_user_name_field" id="pm_map_user_name_field">
                    <?php
                    foreach ( $headings as $key => $heading ) {
						?>
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $heading ); ?></option>
						<?php
					}
                    ?>
                    </select>
                  <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php esc_html_e( 'Step 6a: Map a column in your CSV to username field of this Group. New users will be assigned these usernames.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div> 
        </div>

        <div class="uimrow">
            <div class="uimfield">
					<?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput pm_select_required">
                    <select name="pm_map_user_email_field" id="pm_map_user_email_field">
                    <?php
                    foreach ( $headings as $key => $heading ) {
						?>
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $heading ); ?></option>
						<?php
					}
                    ?>
                    </select>
                  <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php esc_html_e( 'Step 6b: Map a column in your CSV to User email field of this Group. Make sure the column contain email IDs.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div> 
        </div>

        <div class="uimrow">
        <div class="pmagic-table">
                <table class="pg-import-table">
                    <tr class="pm_import_row">
                        <th><?php esc_html_e( 'CSV Mapping Scheme', 'profilegrid-user-profiles-groups-and-communities' ); ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
		  <?php
			$i =0;
			foreach ( $headings as $heading ) {
				?>
                <tr class="pm_import_row" id="pm_import_row_<?php echo esc_attr( $i ); ?>">
                <td><i class="fa fa-check" aria-hidden="true"></i> <?php echo esc_html( $heading ); ?></td>
                
                <td><input type="radio" name="pm_map_field_<?php echo esc_attr( $i ); ?>" value="0" onclick="pm_map_field_show_hide(0,'#pm_map_field_with_<?php echo esc_attr( $i ); ?>')" checked> <?php esc_html_e( 'Create new field', 'profilegrid-user-profiles-groups-and-communities' ); ?></li></td>
                <td><input type="radio" name="pm_map_field_<?php echo esc_attr( $i ); ?>" value="1" onclick="pm_map_field_show_hide(1,'#pm_map_field_with_<?php echo esc_attr( $i ); ?>')"><?php esc_html_e( 'Map to an existing field', 'profilegrid-user-profiles-groups-and-communities' ); ?></td>
                <td id="map_existing_field_html_<?php echo esc_attr( $i ); ?>">
                    <select name="pm_map_field_with_<?php echo esc_attr( $i ); ?>" id="pm_map_field_with_<?php echo esc_attr( $i ); ?>" style="display:none">
                    <?php
                    foreach ( $fields as $field ) {
						?>
                    <option value="<?php echo esc_attr( $field->field_id ); ?>"><?php echo esc_html( $field->field_name ); ?></option>
						<?php
					}
                    ?>
                    </select>
                    
                </td>
            </tr>
				<?php
				$i++;
			}
			?>
             </table>
        </div></div>
		  <?php

	}



}
