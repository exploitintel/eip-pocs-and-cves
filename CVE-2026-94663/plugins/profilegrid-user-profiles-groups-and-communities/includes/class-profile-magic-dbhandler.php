<?php
class PM_DBhandler {

	private $pm_table_columns_cache = array();

    public function insert_row( $identifier, $data, $format = null ) {
        global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        $result       = $wpdb->insert( $table, $data, $format );

        if ( $result !== false ) {
			return $wpdb->insert_id; } else {
			return false; }
    }

    public function update_row( $identifier, $unique_field, $unique_field_value, $data, $format = null, $where_format = null ) {
        global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        if ( $unique_field === false ) {
            $unique_field = $pm_activator->get_db_table_unique_field_name( $identifier );
        }
        $unique_field = esc_sql( $unique_field );

        if ( is_numeric( $unique_field_value ) ) {
            $unique_field_value = (int) $unique_field_value;
            $query              = $wpdb->prepare( "SELECT * from `{$table}` where `{$unique_field}` = %d", $unique_field_value );
        } else {
            $query = $wpdb->prepare( "SELECT * from `{$table}` where `{$unique_field}` = %s", $unique_field_value );
        }

        if ( $query != null ) {
            $result = $wpdb->get_row( $query );
        }

        if ( $result === null ) {
			return false; }

		$where = array( $unique_field => $unique_field_value );
        return $wpdb->update( $table, $data, $where, $format, $where_format );
    }

    public function remove_row( $identifier, $unique_field, $unique_field_value, $where_format = null ) {
        global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        if ( $unique_field === false ) {
			$unique_field = $pm_activator->get_db_table_unique_field_name( $identifier );
        }
        $unique_field = esc_sql( $unique_field );

        if ( is_numeric( $unique_field_value ) ) {
            $unique_field_value = (int) $unique_field_value;
            $query              = $wpdb->prepare( "SELECT * from `{$table}` WHERE `{$unique_field}` = %d", $unique_field_value );
        } else {
            $query = $wpdb->prepare( "SELECT * from `{$table}` WHERE `{$unique_field}` = %s", $unique_field_value );
        }

        if ( $query != null ) {
            $result = $wpdb->get_row( $query );
        }

        if ( $result === null ) {
			return false; }

		$where = array( $unique_field => $unique_field_value );
        return $wpdb->delete( $table, $where, $where_format );
    }

    public function get_row( $identifier, $unique_field_value, $unique_field = false, $output_type = 'OBJECT' ) {
        global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        $result       = null;
        if ( $unique_field === false ) {
			$unique_field = $pm_activator->get_db_table_unique_field_name( $identifier );
        }
        $unique_field = esc_sql( $unique_field );

        if ( is_numeric( $unique_field_value ) ) {
            $unique_field_value = (int) $unique_field_value;
            $query              = $wpdb->prepare( "SELECT * from `{$table}` where `{$unique_field}` = %d", $unique_field_value );
        } else {
            $query = $wpdb->prepare( "SELECT * from `{$table}` where `{$unique_field}` = %s", $unique_field_value );
        }

        if ( $query != null ) {
            $result = $wpdb->get_row( $query, $output_type );
        }

        if ( $result != null ) {
			return $result; }
    }

    public function get_value( $identifier, $field, $unique_field_value, $unique_field = false ) {
         global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );

        if ( $unique_field === false ) {
			$unique_field = $pm_activator->get_db_table_unique_field_name( $identifier );
        }
        $unique_field = esc_sql( $unique_field );

        if ( is_numeric( $unique_field_value ) ) {
            $unique_field_value = (int) $unique_field_value;
            $query              = $wpdb->prepare( "SELECT {$field} from `{$table}` where `{$unique_field}` = %d", $unique_field_value );
        } else {
            $query = $wpdb->prepare( "SELECT {$field} from `{$table}` where `{$unique_field}` = %s", $unique_field_value );
        }

        if ( $query != null ) {
            $result = $wpdb->get_var( $query );
        }

        if ( isset( $result ) && $result != null ) {
			return $result; }
    }

    public function get_value_with_multicondition( $identifier, $field, $where ) {
         global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table        = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        $qry          = "SELECT {$field} from `{$table}` where";
        $i            = 0;
        $args         = array();
        foreach ( $where as $column_name => $column_value ) {

			if ( $i !== 0 ) {
				$qry .= ' AND'; }

                $format      = $pm_activator->get_db_table_field_type( $identifier, $column_name );
                $safe_column = esc_sql( $column_name );
                $qry        .= " {$safe_column} = {$format}";

			if ( is_numeric( $column_value ) ) {
				$args[] = (int) $column_value; } else {
				$args[] = $column_value; }

                $i++;
		}
             $results = $wpdb->get_var( $wpdb->prepare( $qry, $args ) );
             return $results;
    }

    public function get_all_result( $identifier, $column = '*', $where = 1, $result_type = 'results', $offset = 0, $limit = false, $sort_by = null, $descending = false, $additional = '', $output = 'OBJECT', $distinct = false ) {
        global $wpdb;
        $pm_activator   = new Profile_Magic_Activator();
        $table          = esc_sql( $pm_activator->get_db_table_name( $identifier ) );
        $unique_id_name = esc_sql( $pm_activator->get_db_table_unique_field_name( $identifier ) );
        $args           = array();

        if ( $table === '' || $unique_id_name === '' ) {
            return null;
        }

        if ( !$sort_by ) {
            $sort_by = $unique_id_name;
        }
        if ( is_string( $column ) && strpos( $column, 'distinct' ) ) {
            $column   = str_replace( 'distinct ', '', $column );
            $distinct = true;
        } elseif ( is_string( $column ) && strpos( $column, 'DISTINCT' ) ) {
            $column   = str_replace( 'DISTINCT ', '', $column );
            $distinct = true;
        }

        $column = $this->pm_validate_query_columns( $identifier, $table, $column );
        if ( $column === false ) {
            return null;
        }

        $sort_by = $this->pm_validate_sort_columns( $identifier, $table, $sort_by, $unique_id_name );
        if ( $sort_by === false ) {
            $sort_by = $unique_id_name;
        }

        if ( $column != '' && !is_array( $column ) && $distinct == false ) {
            $qry = "SELECT $column FROM $table WHERE";
        } elseif ( $column != '' && !is_array( $column ) && $distinct == true ) {
            $qry = "SELECT DISTINCT $column FROM $table WHERE";
        } elseif ( is_array( $column ) ) {
            $qry = 'SELECT ' . implode( ', ', $column ) . " FROM $table WHERE";
        }

        if ( is_array( $where ) ) {
            $i = 0;
            foreach ( $where as $column_name => $column_value ) {

                if ( $i !== 0 ) {
					$qry .= ' AND'; }

                if ( ! $this->pm_is_allowed_identifier( $identifier, $table, $column_name ) ) {
                    return null;
                }

                $format = $pm_activator->get_db_table_field_type( $identifier, $column_name );
                $qry   .= " $column_name = $format";

                if ( is_numeric( $column_value ) ) {
					$args[] = (int) $column_value; } else {
					$args[] = $column_value; }

					$i++;
            }
			if ( $additional!='' ) {
                             $additional = $this->pm_filter_addtional_query_parameter($identifier, $table, $additional);
                if ( $additional === '' ) {
                    return null;
                }
                $qry .= ' ' . $additional;
			}
        } elseif ( $where == 1 ) {
            if ( $additional!='' ) {
                $additional = $this->pm_filter_addtional_query_parameter($identifier, $table, $additional);
                if ( $additional === '' ) {
                    return null;
                }
                $qry .= ' ' . $additional;
            } else {
                $qry .= ' 1';
            }
        }

        if ( $descending === false ) {
            $qry .= " ORDER BY $sort_by";
        } else {
            $qry .= " ORDER BY $sort_by DESC";
        }

		if ( $limit===false ) {
            $qry .= '';
        } else {
            $limit = max( 0, (int) $limit );
            $offset = max( 0, (int) $offset );
            $qry .= " LIMIT $limit OFFSET $offset";
        }

        if ( $result_type === 'results' || $result_type === 'row' || $result_type === 'var' ) {
            $method_name = 'get_' . $result_type;
            if ( count( $args ) === 0 ) {
                if ( $result_type === 'results' ) :
                    $results = $wpdb->$method_name( $qry, $output );
                else :
                    $results = $wpdb->$method_name( $qry );
                endif;
            } else {
                if ( $result_type === 'results' ) :
                    $results = $wpdb->$method_name( $wpdb->prepare( $qry, $args ), $output );
                else :
                    $results = $wpdb->$method_name( $wpdb->prepare( $qry, $args ) );
                endif;
            }
        } else {
            return null;
        }

        if ( is_array( $results ) && count( $results )===0 ) {
            return null;
        }
        return $results;
    }

    public function pm_count( $identifier, $where = 1, $data_specifiers = '' ) {
        global $wpdb;
        $pm_activator = new Profile_Magic_Activator();
        $table_name   = $pm_activator->get_db_table_name( $identifier );
        if ( $data_specifiers=='' ) {
            $unique_id_name = $pm_activator->get_db_table_unique_field_name( $identifier );
            if ( $unique_id_name === false ) {
				return false; }
        } else {
			$unique_id_name = $data_specifiers; }

        $safe_table     = esc_sql( $table_name );
        $safe_unique_id = esc_sql( $unique_id_name );
        $qry            = "SELECT COUNT({$safe_unique_id}) FROM `{$safe_table}` WHERE ";
        $args           = array();

        if ( is_array( $where ) ) {
            $i = 0;
            foreach ( $where as $column_name => $column_value ) {
                if ( $i !== 0 ) {
					$qry .= 'AND '; }
                $safe_column = esc_sql( $column_name );
                if ( is_numeric( $column_value ) ) {
                    $qry   .= "{$safe_column} = %d ";
                    $args[] = (int) $column_value;
                } else {
                    $qry   .= "{$safe_column} = %s ";
                    $args[] = $column_value;
                }
                $i++;
            }
        } elseif ( $where == 1 ) {
			$qry .= '1 '; }

        if ( !empty( $args ) ) {
            $qry = $wpdb->prepare( $qry, $args );
        }

        $count = $wpdb->get_var( $qry );

        if ( $count === null ) {
			return false; }

        return (int) $count;
    }

	public function pm_add_user( $user_name, $password, $user_email, $user_role = 'subscriber' ) {
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( email_exists( $user_email ) ) {
				 $user_id = email_exists( $user_email );
				if ( !is_user_member_of_blog( $user_id, $blog_id ) ) {
					add_user_to_blog( $blog_id, $user_id, $user_role );
				}
			} else {
                                $user_id = wp_create_user( $user_name, $password, $user_email );
				if ( is_wp_error( $user_id ) ) {
					return $user_id;
				}
				if ( is_numeric( $user_id ) ) {
                    $user_id = wp_update_user(
                        array(
							'ID'   => $user_id,
							'role' => $user_role,
                        )
                    );
                    if ( !is_user_member_of_blog( $user_id, $blog_id ) ) {
                        add_user_to_blog( $blog_id, $user_id, $user_role );
                    }
                }
			}
		} else {

			$user_id = wp_create_user( $user_name, $password, $user_email );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
			if ( is_numeric( $user_id ) ) {
                $user_id = wp_update_user(
                    array(
						'ID'   => $user_id,
						'role' => $user_role,
                    )
                );
            }
		}
		return $user_id;
	}

    public function get_global_option_value( $option, $default = '' ) {
            $value = get_option( $option, $default );
		if ( !isset( $value ) || $value=='' ) {
			$value = $default; }
            $value = maybe_unserialize( $value );
            $value = apply_filters('pm_modify_global_option_value',$value, $option, $default);
            return $value;
    }

    public function update_global_option_value( $option, $value ) {
            update_option( $option, $value );
    }

    public function pm_get_all_users_ajax( $search = '', $meta_query = array(), $role = '', $offset = '', $limit = '', $order = 'ASC', $orderby = 'ID', $exclude = array(), $datequery = array(), $include = array() ) {
         $args = array(
			 'order'       => $order,
			 'orderby'     => $orderby,
			 'count_total' => true,
		 );

		 if ( $orderby=='first_name' || $orderby=='last_name' ) {
			 $args['orderby']  = 'meta_value';
			 $args['meta_key'] = $orderby;
		 }
		 if ( $offset!='' ) {
			 $args['offset'] = $offset; }
		 if ( $limit!='' ) {
			 $args['number'] = $limit; }
		 if ( $role!='' ) {
			 $args['role'] = $role; }
		 if ( $search!='' ) {
			 $args['search'] = '*' . esc_attr( $search ) . '*'; }
		 if ( $role!='' ) {
			 $args['role'] = $role; }
		 if ( !empty( $meta_query ) ) {
			 if ( isset( $meta_query['search'] ) ) {
				 $args['search'] = '*' . esc_attr( $meta_query['search'] ) . '*';
				 unset( $meta_query['search'] );
			 }
			 if ( isset( $meta_query['search_columns'] ) ) {
				 $args['search_columns'] = is_array( $meta_query['search_columns'] ) ? $meta_query['search_columns'] : array( $meta_query['search_columns'] );
				 unset( $meta_query['search_columns'] );
			 }
				$args['meta_query'] = $meta_query;

		 }
		 if ( !empty( $exclude ) ) {
			 $args['exclude'] = $exclude; }
		 if ( !empty( $include ) ) {
			 $args['include'] = $include; }
		 if ( !empty( $datequery ) ) {
			 $args['date_query'] = $datequery; }

		 $user_query = new WP_User_Query( $args );

		 return $user_query;
    }

	public function pm_get_all_users( $search = '', $meta_query = array(), $role = '', $offset = '', $limit = '', $order = 'ASC', $orderby = 'ID', $exclude = array(), $datequery = array(), $include = array() ) {
		$args = array(
			'order'       => $order,
			'orderby'     => $orderby,
			'count_total' => true,
		);

		if ( $orderby=='first_name' || $orderby=='last_name' ) {
			$args['orderby']  = 'meta_value';
			$args['meta_key'] = $orderby;
		}

		if ( $offset!='' ) {
			$args['offset'] = $offset; }
		if ( $limit!='' ) {
			$args['number'] = $limit; }
		if ( $role!='' ) {
			$args['role'] = $role; }
		if ( $search!='' ) {
			$args['search'] = '*' . esc_attr( $search ) . '*'; }
		if ( $role!='' ) {
			$args['role'] = $role; }
		if ( !empty( $meta_query ) ) {
			if ( isset( $meta_query['search'] ) ) {
				$args['search'] = '*' . esc_attr( $meta_query['search'] ) . '*';
				unset( $meta_query['search'] );
			}
			if ( isset( $meta_query['search_columns'] ) ) {
				$args['search_columns'] = is_array( $meta_query['search_columns'] ) ? $meta_query['search_columns'] : array( $meta_query['search_columns'] );
				unset( $meta_query['search_columns'] );
			}
                    $args['meta_query'] = $meta_query;

		}

		if ( !empty( $exclude ) ) {
			$args['exclude'] = $exclude; }
		if ( !empty( $include ) ) {
			$args['include'] = $include; }
		if ( !empty( $datequery ) ) {
			$args['date_query'] = $datequery; }
		$users = get_users( $args );

		return $users;
	}

	public function pm_get_pagination( $num_of_pages, $pagenum, $base = '' ) {
		if ( $pagenum=='' ) {
			$pagenum =1; }
        if ( $base=='' ) {
			$base = esc_url_raw( add_query_arg( 'pagenum', '%#%' ) ); }
		$args = array(
			'base'               => $base,
			'format'             => '',
			'total'              => $num_of_pages,
			'current'            => $pagenum,
			'show_all'           => false,
			'end_size'           => 1,
			'mid_size'           => 2,
			'prev_next'          => true,
			'prev_text'          => __( '&laquo;', 'profilegrid-user-profiles-groups-and-communities' ),
			'next_text'          => __( '&raquo;', 'profilegrid-user-profiles-groups-and-communities' ),
			'type'               => 'list',
			'add_args'           => false,
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => '',
		);

		$page_links = paginate_links( $args );
		return $page_links;
	}
        
        public function pm_get_pagination_new_ui( $num_of_pages, $pagenum, $base = '' ) {
		if ( $pagenum=='' ) {
			$pagenum =1; }
        if ( $base=='' ) {
			$base = esc_url_raw( add_query_arg( 'pagenum', '%#%' ) ); }
		$args = array(
			'base'               => $base,
			'format'             => '',
			'total'              => $num_of_pages,
			'current'            => $pagenum,
			'show_all'           => true,
			'end_size'           => 1,
			'mid_size'           => 2,
			'prev_next'          => true,
			'prev_text'          => __( '&laquo;', 'profilegrid-user-profiles-groups-and-communities' ),
			'next_text'          => __( '&raquo;', 'profilegrid-user-profiles-groups-and-communities' ),
			'type'               => 'array',
			'add_args'           => false,
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => '',
		);

		$page_links = paginate_links( $args );
                $disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;
                if ( 1 == $pagenum ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $num_of_pages == $pagenum ) {
			$disable_last = true;
			$disable_next = true;
		}

                
                //print_r($page_links);
               // echo '<br/>'. sanitize_text_field($page_links[0]);
                $pagination_html = '';
                 // Prepare pagination UI HTML
                if(isset($page_links) && !empty($page_links))
                {
                    $pagination_html .='<span class="pagination-links">';
                    $pre_links = ($pagenum==1)?$pagenum:$pagenum-1;
                    $next_links = ($pagenum==1)?$pagenum:$pagenum+1;
                    if($disable_first)
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                    }
                    else
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button" aria-hidden="true">'. str_replace('">1','">«', $page_links[1]).'</span>';
                    }
                    if($disable_prev)
                    {
                        
                        $pagination_html .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
                    }
                    else
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button" aria-hidden="true">'. str_replace('">'.($pagenum-1),'">‹', $page_links[$pre_links]).'</span>';
                    }
                    
                    $pagination_html .= '<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="pagenum" value="' . $pagenum . '" size="1" aria-describedby="table-paging" ><span class="tablenav-paging-text"> '.$pagenum.' of <span class="total-pages">' . $num_of_pages . '</span></span></span>';
                    
                    if($disable_next)
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                    }
                    else
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button" aria-hidden="true">'. str_replace('">'.($pagenum+1),'">›', $page_links[$next_links]).'</span>';
                    }
                    
                    if($disable_last)
                    {
                        $pagination_html .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
                    }
                    else
                    {
                        //$pagination_html .= '<span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">'.$page_links[$num_of_pages].'</span>';
                        $pagination_html .= '<span class="tablenav-pages-navspan button" aria-hidden="true">'. str_replace('">'.$num_of_pages,'">»',$page_links[$num_of_pages]).'</span>';
                    }
                    
                      $pagination_html .= '</span>';
                    
                   
                }
                else
                {
                    $pagination_html = '<span class="pagination-links"><span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span></span>';
                }

                return $pagination_html;
	}

    public function pm_get_all_groups_ajax( $search, $offset = 0, $limit = '10', $order = 'DESC', $sort_by = 'members' ) {
    }

    private function pm_get_table_columns( $identifier, $table ) {
        global $wpdb;

        if ( isset( $this->pm_table_columns_cache[ $identifier ] ) ) {
            return $this->pm_table_columns_cache[ $identifier ];
        }

        $columns = $wpdb->get_col( "SHOW COLUMNS FROM `{$table}`", 0 );
        if ( ! is_array( $columns ) ) {
            $columns = array();
        }

        $this->pm_table_columns_cache[ $identifier ] = array_map( 'strtolower', $columns );
        return $this->pm_table_columns_cache[ $identifier ];
    }

    private function pm_is_allowed_identifier( $identifier, $table, $identifier_name ) {
        $identifier_name = trim( str_replace( '`', '', (string) $identifier_name ) );
        if ( $identifier_name === '' ) {
            return false;
        }

        return in_array( strtolower( $identifier_name ), $this->pm_get_table_columns( $identifier, $table ), true );
    }

    private function pm_validate_query_columns( $identifier, $table, $column ) {
        if ( is_array( $column ) ) {
            $validated_columns = array();
            foreach ( $column as $single_column ) {
                $single_column = trim( (string) $single_column );
                if ( ! $this->pm_is_allowed_identifier( $identifier, $table, $single_column ) ) {
                    return false;
                }
                $validated_columns[] = $single_column;
            }

            return $validated_columns;
        }

        $column = trim( (string) $column );
        if ( $column === '*' ) {
            return $column;
        }

        if ( preg_match( '/^COUNT\(\s*([A-Za-z0-9_]+)\s*\)(?:\s+AS\s+([A-Za-z0-9_]+))?$/i', $column, $matches ) ) {
            if ( ! $this->pm_is_allowed_identifier( $identifier, $table, $matches[1] ) ) {
                return false;
            }

            $validated = 'COUNT(' . $matches[1] . ')';
            if ( ! empty( $matches[2] ) ) {
                $validated .= ' AS ' . $matches[2];
            }

            return $validated;
        }

        if ( $this->pm_is_allowed_identifier( $identifier, $table, $column ) ) {
            return $column;
        }

        return false;
    }

    private function pm_validate_sort_columns( $identifier, $table, $sort_by, $default ) {
        $sort_by = trim( (string) $sort_by );
        if ( $sort_by === '' ) {
            return $default;
        }

        $columns           = array_map( 'trim', explode( ',', $sort_by ) );
        $validated_columns = array();
        foreach ( $columns as $column ) {
            if ( ! $this->pm_is_allowed_identifier( $identifier, $table, $column ) ) {
                return false;
            }
            $validated_columns[] = $column;
        }

        return implode( ',', $validated_columns );
    }

    private function pm_strip_sql_literals( $sql ) {
        $sql = preg_replace( "/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/", "''", $sql );
        $sql = preg_replace( '/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/', '""', $sql );
        $sql = preg_replace( '/\\b\\d+\\b/', '0', $sql );
        return $sql;
    }

    public function pm_filter_addtional_query_parameter( $identifier, $table, $additional ) {
        $additional = trim( (string) $additional );
        if ( $additional === '' ) {
            return '';
        }

        if ( preg_match( '/\\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|EXEC|EXECUTE|DECLARE|SLEEP|BENCHMARK|INTO\\s+OUTFILE|LOAD_FILE)\\b/i', $additional ) ) {
            return '';
        }

        $normalized = $this->pm_strip_sql_literals( $additional );
        // Keep quoted literals allowed after normalization so safe clauses like NOT IN ('read_only') still validate.
        if ( preg_match( '/[;#]/', $normalized ) || strpos( $normalized, '--' ) !== false ) {
            return '';
        }
        if ( preg_match( '/[^A-Za-z0-9_\\s(),.=<>!%`\'"]/', $normalized ) ) {
            return '';
        }

        preg_match_all( '/[A-Za-z_][A-Za-z0-9_]*/', $normalized, $matches );
        $allowed_keywords = array(
            'AND',
            'OR',
            'IN',
            'NOT',
            'LIKE',
            'IS',
            'NULL',
            'ASC',
            'DESC',
        );

        foreach ( $matches[0] as $token ) {
            if ( in_array( strtoupper( $token ), $allowed_keywords, true ) ) {
                continue;
            }

            if ( ! $this->pm_is_allowed_identifier( $identifier, $table, $token ) ) {
                return '';
            }
        }

        return $additional;
    }


}
