<?php
class PM_HTML_Creator {

	private $profile_magic;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Basic functions helper.
	 *
	 * @var Profile_Magic_Basic_Functions
	 */
	private $basic_functions;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $profile_magic       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $profile_magic, $version ) {

		$this->profile_magic = $profile_magic;
		$this->version       = $version;
		$this->basic_functions = new Profile_Magic_Basic_Functions( $profile_magic, $version );

	}

	public function get_custom_fields_html( $gid, $fields, $col = 1 ) {
                 $dbhandler      = new PM_DBhandler();
                $pm_customfields = new PM_Custom_Fields();
		$col                     = $dbhandler->get_global_option_value( 'pm_reg_form_cols', 1 );
		if ( $lastRec=count( $fields ) ) {
			echo '<div class="pmrow">';
			$i =0;
			foreach ( $fields as $field ) {
				if ( $i!=0 && ( $i % $col == 0 ) && ( $i<$lastRec ) ) {
					echo '</div><div class="pmrow">';
                }
				$pm_customfields->pm_get_custom_form_fields( $field, '', 'profilegrid-user-profiles-groups-and-communities' );
				$i++;
			}
			echo '</div>';
		}
	}

	public function get_custom_fields_html_multipage( $gid, $fields, $col = 1 ) {
                 $dbhandler      = new PM_DBhandler();
                $pm_customfields = new PM_Custom_Fields();
		$sections                =  $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid'=>$gid ), 'results', 0, false, 'ordering' );
		 $j                      =0;
		foreach ( $sections as $section ) {
			$fields      =  $dbhandler->get_all_result(
                'FIELDS',
                $column  = '*',
                array(
					'associate_group'     =>$gid,
					'associate_section'   =>$section->id,
					'show_in_signup_form' =>1,
                ),
                'results',
                0,
                false,
                $sort_by = 'ordering'
            );

			if ( isset( $fields ) && !empty( $fields ) ) {
				echo '<fieldset id="fieldset_' . esc_attr($section->id) . '" >';
				echo '<legend>' . esc_html($section->section_name) . '</legend>';
				foreach ( $fields as $field ) {
					echo '<div class="pmrow">';
					$pm_customfields->pm_get_custom_form_fields( $field, '', 'profilegrid-user-profiles-groups-and-communities' );
					echo '</div>';
				}
				echo '<div class="all_errors" style="display:none;"></div></fieldset>';

			}
		}
	}

	public function get_custom_fields_html_singlepage( $gid, $fields, $col = 1, $value = '' ) {
         $dbhandler      = new PM_DBhandler();
        $pmrequests      = new PM_request();
        $pm_customfields = new PM_Custom_Fields();
        if ( is_user_logged_in() ) {
            $exclude = "and field_type not in('user_name','user_avatar','user_pass','confirm_pass','paragraph','heading','read_only')";
        } else {
			$exclude = "and field_type not in('read_only')";}
        $filled_section = $dbhandler->get_all_result(
            'FIELDS',
            'associate_section',
            array(
				'associate_group'     =>$gid,
				'show_in_signup_form' =>1,
            ),
            'results',
            0,
            false,
            null,
            false,
            '',
            'OBJECT',
            true
        );
        foreach ( $filled_section as $sectionid ) {
            $sectionidarray[] = $sectionid->associate_section;
        }
        $additional = 'AND `id` IN (' . implode( ',', $sectionidarray ) . ')';
        $sections   = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid'=>$gid ), 'results', 0, false, 'ordering', false, $additional );
		$count      = count( $sections );
		$j          =1;
		foreach ( $sections as $section ) {
				$fields      =  $dbhandler->get_all_result(
                    'FIELDS',
                    $column  = '*',
                    array(
						'associate_group'     =>$gid,
						'associate_section'   =>$section->id,
						'show_in_signup_form' =>1,
                    ),
                    'results',
                    0,
                    false,
                    $sort_by = 'ordering',
                    false,
                    $exclude
                );

			if ( isset( $fields ) && !empty( $fields ) ) {
						echo '<fieldset id="fieldset_' . esc_attr($section->id) . '">';
						echo '<legend>' . esc_html($section->section_name) . '</legend>';
                        if($j==1)
                                {
                                    do_action('profile_magic_pg_registration_form_field',$gid);
                                }
				foreach ( $fields as $field ) {
					if ( $field->field_options != '' ) {
								   $field_options = maybe_unserialize( $field->field_options );
					}

					if ( !empty( $field_options ) && isset( $field_options['admin_only'] ) && $field_options['admin_only']=='1' ) {
							   continue;
					}

					if ( $value!='' ) {
						if ( isset( $value[ $field->field_key ] ) ) {
							$field_value = $value[ $field->field_key ];
						} elseif ( is_numeric( $value ) ) {
							$field_value = $pmrequests->profile_magic_get_user_field_value( $value, $field->field_key );
						} else {
							$field_value ='';
						}
					} else {
								$field_value ='';
					}
							echo '<div class="pmrow">';
							$pm_customfields->pm_get_custom_form_fields( $field, $field_value, 'profilegrid-user-profiles-groups-and-communities' );
							echo '</div>';
				}
				if ( $count == $j ) {
					do_action( 'profile_magic_custom_fields_html', $gid );
					do_action( 'profile_magic_show_captcha', $gid );
				}
						echo '<div class="all_errors" style="display:none;"></div></fieldset>';

			}

				$j++;
		}
	}

	public function get_custom_login_form_html( $fields, $col = 1 ) {
                 $pm_customfields = new PM_Custom_Fields();
		if ( $lastRec=count( $fields ) ) {
			echo '<div class="pmrow">';
			$i =0;
			foreach ( $fields as $field ) {
				if ( $i!=0 && ( $i % $col == 0 ) && ( $i<$lastRec ) ) {
					echo '</div><div class="pmrow">';
                }
				$pm_customfields->pm_get_custom_login_fields( $field, $this->profile_magic );
				$i++;
			}
			echo '</div>';
		}

	}

	public function get_group_page_fields_html( $uid, $gid, $group_leader, $imgsize = '', $arg = '', $hide_profile_link = '' ) {
                 $dbhandler = new PM_DBhandler();
                $pmrequests = new PM_request();
		$profile_url        = $pmrequests->pm_get_user_profile_url( $uid );

		if ( $uid == $group_leader ) {
			$class = 'pm-group-leader-large';
		} else {
			$class ='';
        }
		if ( !empty( $arg ) ) {
			$profile_args                  = $arg;
			$profile_args['force_display'] =true;
		} else {
			$profile_args                  = array();
			$profile_args['force_display'] =true;
		}
		?>
        
        <div class="pm-user-card pm-difl pm-border pm-radius5">
            <?php do_action('pm_before_group_member_card',$uid, $gid, $group_leader, $imgsize, $arg = '', $hide_profile_link = '' );?>
            <div class="pm-user-card-cover pm-dbfl">
                <?php
				echo wp_kses_post( $pmrequests->profile_magic_get_cover_image( $uid, $imgsize, '', false, $arg ) );
				?>
                </div>
            <?php if ( $hide_profile_link=='' ) : ?>
            <a href="<?php echo esc_url( $profile_url ); ?>"><div class="pm-user-image pm-dbfl pm-bg-lt <?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( get_avatar( $uid, $imgsize, '', false, $profile_args ) ); ?></div></a>
            <?php else : ?>
            <div class="pm-user-image pm-dbfl pm-bg-lt <?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( get_avatar( $uid, $imgsize, '', false, $profile_args ) ); ?></div>
            <?php endif; ?>
            <div class="pm-user-description pm-dbfl pm-bg-lt">
                <div class="pm-user-card-title pm-dbfl pm-pad10 pm-bg-lt pm-clip">
                <?php if ( $hide_profile_link=='' ) : ?>
                    <a href="<?php echo esc_url( $profile_url ); ?>"><?php echo wp_kses_post( $pmrequests->pm_get_display_name( $uid, true ) ); ?> </a>
                <?php else : ?>
                    <?php echo wp_kses_post( $pmrequests->pm_get_display_name( $uid, true ) ); ?>
                <?php endif; ?>
                    <?php 
                    $is_leader = $pmrequests->pg_check_in_single_group_is_user_group_leader($uid, $gid);
                    $admin_flag = $pmrequests->pm_get_group_admin_flag($gid);
                    if($is_leader && $admin_flag){
                        echo '<div class="pg-card-leader">'.wp_kses_post($pmrequests->pm_get_group_admin_label($gid)).'</div>';
                    }
                    ?>
                </div>
                  <?php
					$exclude = "'user_name','user_avatar','user_pass','confirm_pass','paragraph','heading'";
					$fields  = $pmrequests->pm_get_frontend_user_meta( $uid, $gid, $group_leader, 'group', '', $exclude );
                            $this->get_user_meta_fields_html( $fields, $uid );
                            do_action('pm_additional_message_button_html',$uid,$gid,$hide_profile_link);
					?>
                
            </div>
            <?php do_action('pm_before_group_member_card',$uid, $gid, $group_leader, $imgsize = '', $arg = '', $hide_profile_link = '' );?>
          </div>         
      	<?php
        }

	public function get_user_meta_fields_html( $fields, $uid ) {
                 $pmrequests = new PM_request();
		if ( isset( $fields ) ) :
			echo '<div class="pm-section-wrapper">';
			foreach ( $fields as $field ) :
				?>
				<?php
				$field_value =  $pmrequests->profile_magic_get_user_field_value( $uid, $field->field_key, $field->field_type );
				$value       = '';
				if ( !empty( $field_value ) ) {
					if ( $field->field_options != '' ) {
						$field_options = maybe_unserialize( $field->field_options );
					}

					if ( !empty( $field_options ) && isset( $field_options['admin_only'] ) && $field_options['admin_only']=='1' && !is_super_admin() ) {
                              continue;
					}

                            //$field_value = maybe_unserialize( $field_value );
                            if (is_string($field_value) && preg_match('/[oc]:\d+:/i', $field_value)) {
                                $field_value = ''; // Block PHP Object Injection attempt
                            } else {
                                // Step 2: Safely unserialize
                                $field_value = maybe_unserialize($field_value);
                            }
                            // Prevent PHP Object Injection
                            if (is_object($field_value) || is_resource($field_value)) {
                                $field_value = ''; // Block unsafe objects
                            }

                            // Ensure `$field_value` is either a string or an array
                            if (!is_string($field_value) && !is_array($field_value)) {
                                $field_value = ''; // Block unexpected types
                            }
                            
                            // Step 4: Convert unserialized data to JSON and back to ensure safety
                            $field_value = json_decode(json_encode($field_value), true);
                            
					if ( $field->field_type=='checkbox' || $field->field_type=='repeatable_text' ) {
						if ( !is_array( $field_value ) ) {
							$field_value = explode( ',', (string)$field_value );
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

							if ( trim( $address_value )=='' ) {
								continue;
							}
						}
						$repeat_field = '';
						foreach ( $field_value as $val ) {
								$repeat_field .= $val;
							if ( $val=='chl_other' ) {
								continue;
							}
							if ( $val!='' ) {
									$value .= '<div class="pm-field-multiple-value pm-difl pm-radius5">' . wp_kses_post($val) . '</div>';
							}
						}

						if ( trim( $repeat_field )=='' ) {
							continue;
						}
					} else {
						$value = wp_kses_post($field_value);
					}
					?>
			
		
			<div class="pm-user-description-row pm-dbfl pm-border">
                            <div class="pm-card-label pm-difl">
                            <?php
                            if ( isset( $field ) && $field->field_icon!=0 ) :
                                    echo wp_get_attachment_image( $field->field_icon, array( 16, 16 ), true, false );
                            endif;
                            ?>
                            <?php 
                            echo wp_kses_post(apply_filters('pm_field_card_link_label_html_filter',$field->field_name,$uid,$field->field_type,$value,$field));
                            ?>
                            </div>
                            <?php
                            switch ( $field->field_type ) {
                                case 'youtube':
                                    preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $value, $match );
                                    if(!is_array($match) || !isset($match[1])){
                                    	$html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'"><a href="'. esc_url( $value ).'" target="_blank">'.wp_kses_post( $value ).'</a></div>';
                                    	echo wp_kses_post(apply_filters('pm_field_card__link_value_html_filter',$html,$uid,$field->field_type,$value,$field));
	                                    break;
                                    }
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
                                   
                                    $html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'"><a href="'. esc_url( $value ).'" target="_blank">'.wp_kses_post( $value ).'</a></div>';
                                    echo wp_kses_post(apply_filters('pm_field_card__link_value_html_filter',$html,$uid,$field->field_type,$value,$field));
                                    
                                    break;
                                case 'term_checkbox':
                                    $html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'">'. esc_html__('Yes','profilegrid-user-profiles-groups-and-communities').'</div>';
                                    echo wp_kses_post($html);
                                    break;
                                case 'description':
                                    $formatted = wpautop( $value );
                                    $html = '<div class="pm-card-value pm-difl pm_collapsable_' . esc_attr($field->field_type).'">' . wp_kses_post( $formatted ) . '</div>';
                                    echo wp_kses_post( apply_filters( 'pm_field_card_value_html_filter', $html, $uid, $field->field_type, $value, $field ) );
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
                                else
                                {
                                    do_action('pm_blank_field_value_html',$field,$uid);
                                }
			endforeach;
                                
                        echo '</div>';
		endif;
	}

	public function pm_get_captcha_html() {
                 $dbhandler = new PM_DBhandler();
		$publickey          = $dbhandler->get_global_option_value( 'pm_recaptcha_site_key' )
		?>
        <div class="pmrow">
        <div class="pm-col">
			<div class="pmfield"> </div>
			  <div class="pminput pm_recaptcha">
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $publickey ); ?>"></div>
				<div class="errortext" style="display:none;"></div>
			  </div>
			</div>
            </div>
        <?php
	}

	public function pm_field_captcha_error( $errors ) {
		?>
         <!--HTML for showing error when recaptcha does not matches-->
		<div class="errortext pm_captcha_error">
    	     <?php esc_html_e( 'Sorry, you didn\'t enter the correct captcha code.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
		<?php
	}

	public function pm_get_user_blog_posts( $uid, $pagenum = 1, $limit = 10 ) {
          $dbhandler = new PM_DBhandler();
		 $pmrequests = new PM_request();
		 $post_type  = $dbhandler->get_global_option_value( 'pm_blog_post_from', 'profilegrid_blogs' );
		if ( $post_type=='both' ) {
			$post_type = array( 'profilegrid_blogs', 'post' );}
		 $displayname = $pmrequests->pm_get_display_name( $uid );
		 $offset      = ( $pagenum - 1 ) * $limit;
		 $args        = array(
			 'orderby'        => 'date',
			 'order'          => 'DESC',
			 'post_type'      => $post_type,
			 'author'         => $uid,
			 'post_status'    => 'publish',
			 'posts_per_page' => -1,
		 );
		 $total_posts = count( get_posts( $args ) );

		 $args['posts_per_page'] = $limit;
		 $args['offset']         = $offset;
		 $posts_array            = get_posts( $args );

		 $num_of_pages = ceil( $total_posts/$limit );

		 $pagination = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );
		 if ( $pagenum<=$num_of_pages ) {

			 $path =  plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );

			 $query = new WP_Query( $args );

			 while ( $query->have_posts() ) :
				 $query->the_post();
				 $comments_count = wp_count_comments();

					?>
                    <div class="pm-blog-post-wrap pm-dbfl">
					  <?php if ( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_thumbnail', '1' )=='1' || $dbhandler->get_global_option_value( 'pm_show_user_blog_post_time', '1' )=='1' || $dbhandler->get_global_option_value( 'pm_show_user_blog_post_comment_count', '1' )=='1' ) { ?>
                         <div class="pm-blog-img-wrap pm-difl">
                             <?php if ( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_thumbnail', '1' )=='1' ) { ?>
                            <div class="pm-blog-img pm-difl">
									<?php
									if ( has_post_thumbnail() ) {
										the_post_thumbnail( 'post-thumbnail' );
									} else {
										?>
                                <img src="<?php echo esc_url( $path ); ?>" alt="<?php the_title(); ?>" class="pm-user" />
									<?php } ?>
                            </div>
									<?php
                             } else {
									?>
                                 <div class="pm-blog-img pm-no-blog-img pm-difl"></div> 
                                 <?php
                             }
								?>
                            <div class="pm-blog-status pm-difl">
                               <?php if ( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_time', '1' )=='1' ) : ?>
                                <?php // translators: %s: human readable time difference. ?>
                                <span class="pm-blog-time "><?php printf( esc_html_x( '%s ago', '%s = human-readable time difference', 'profilegrid-user-profiles-groups-and-communities' ), esc_html(human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )) ); ?></span>
                               <?php endif; ?>
                                <?php if ( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_comment_count', '1' )=='1' ) : ?>
                                <span class="pm-blog-comment"><?php comments_number( __( 'no Comment', 'profilegrid-user-profiles-groups-and-communities' ), __( '1 Comment', 'profilegrid-user-profiles-groups-and-communities' ), __( '% Comments', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
							<?php

					  } else {
							?>
                             <div class="pm-blog-img-wrap pm-no-blog-img-wrap pm-difl"></div> 
                             <?php
					  }

						?>
                        <div class="pm-blog-desc-wrap pm-difl">
                            <div class="pm-blog-title">
                                <a href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span></a>
                            </div>
                            <div class="pm-blog-desc">
						  <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    wp_reset_postdata();
                endwhile;
			 if ( $pagenum<$num_of_pages ) :
					?>
                    <div class="pg-load-more-container pm-dbfl">
                        <div class="pm-loader" style="display:none;"></div>
                        <input type="hidden" id="pg_next_blog_page" value="<?php echo esc_attr( $pagenum + 1 ); ?>" />
                        <input type="submit" class="pm-load-more-blogs" onclick ="load_more_pg_blogs('<?php echo esc_attr( $uid ); ?>')" value="<?php esc_attr_e( 'Load More', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
                    </div>
				 <?php
                endif;

		 } else {

			 $current_user = wp_get_current_user();
			 if ( $uid == $current_user->ID ) {
				 echo "<div class='pg-alert-warning pg-alert-info'> ";
				  esc_html_e( 'You have not written any blog posts yet. Once you do, they will appear here.', 'profilegrid-user-profiles-groups-and-communities' );
				 echo '</div>';
			 } else {
				 echo "<div class='pg-alert-warning pg-alert-info'>";
				 // translators: %s: display name.
				 /* translators: %s: Display name. */
				 echo sprintf( esc_html__( 'Sorry, %s has not made any blog posts yet.', 'profilegrid-user-profiles-groups-and-communities' ), wp_kses_post($displayname) );
				 echo '</div>';
			 }
		 }
	}

	public function pm_get_user_messenger( $receiver_uid ) {
		$dbhandler       = new PM_DBhandler();
		$pmrequests      = new PM_request();
		$textdomain      = $this->profile_magic;
		$permalink       = get_permalink();
		$current_user    = wp_get_current_user();
		$pmmessenger     = new PM_Messenger();
		$return          =$pmmessenger->pm_messenger_show_threads( '' );
		$message_display ='';
		if ( $receiver_uid!='' ) {
            $receiver_user = $pmmessenger->pm_messenger_show_thread_user( $receiver_uid );
            $tid           = $pmrequests->get_thread_id( $receiver_uid, $current_user->ID );
            if ( $tid!=false ) {
				$message_display = $pmmessenger->pm_messenger_show_messages( $tid, '', $loadnum=1, 0 );
				$return          =$pmmessenger->pm_messenger_show_threads( $tid );
            }
		}
		?>
         
          <div class="pm-group-view">
        <div class="pm-section pm-dbfl" > 
            <svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
    <path d="M0-.5h24v24H0z" fill="none"/>
</svg>
            <div class="pm-section-left-panel pm-section-nav-vertical pm-difl " id="thread_pane">
                
                <div class="dbfl pm-new-message-area"><a title="Click here to compose the message" id="new_message_btn" onclick="create_new_message()"><i class="fa fa-plus" aria-hidden="true"></i><?php esc_html_e( 'New Message', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                <ul class="dbfl" id="threads_ul">
				<?php echo wp_kses_post( $return ); ?>
                </ul>
            </div>

<div class="pm-section-right-panel">
            <div class="pm-blog-desc-wrap pm-difl pm-section-content pm-message-thread-section">
                <div id="pm-msg-overlay" class="pm-msg-overlay  
                <?php
                if ( ( $return=='You have no conversations yet.' )&& !isset( $receiver_user ) ) {
					echo 'pm-overlay-show1';}
				?>
                    "> </div>
                <form id="chat_message_form" onsubmit="pm_messenger_send_chat_message(event);">  
                <div  class="pm-user-display-area pm-dbfl ">
                    <div class="pm-user-send-to pm-difl">To</div>
                    <div class="pm-user-send-box pm-difl">   
                    <input type="text" id="receipent_field"  value="<?php
                    if ( isset( $receiver_user ) ) {
						echo '@' . esc_attr($receiver_user['name']);}
					?>" placeholder="@Username" style="min-width: 100%;" onblur="pm_get_rid_by_uname(this.value)"/>
                    <input type="hidden" id="receipent_field_rid" name="rid" value="<?php
                    if ( isset( $receiver_user ) ) {
						echo esc_attr( $receiver_user['uid'] );}
					?>"  />   
                    </div></div>
                
                <div id="pm-autocomplete"></div>
                <div id="pm-username-error" class="pm-dbfl"></div>
                <div id="message_display_area" class="pm-difl pm_full_width_profile"  style="min-height:200px;max-height:200px;max-width: 550px;overflow-y:auto;">
				<?php echo wp_kses_post($message_display); ?>
			<?php $path =  plugins_url( '../public/partials/images/typing_image.gif', __FILE__ ); ?>
               
                </div>
                    
                <div id="typing_on"  class="pm-user-description-row pm-dbfl pm-border"><div class="pm-typing-inner"><img height="9px" width="40px" src="<?php echo esc_url( $path ); ?>"/></div></div>
             
                <div class="pm-dbfl pm-chat-messenger-box">
				<?php $this->basic_functions->pm_render_nonce_field( 'pg_send_new_message' ); ?>
                      <input type="hidden" name="action" value='pm_messenger_send_new_message' /> 
                    <input type="hidden" id="thread_hidden_field" name="tid" value=""/>
                    <div class="emoji-container">
                        <div class="pm-messenger-user-profile-pic">
                        <?php
                        $avatar =get_avatar(
                            $current_user->ID,
                            50,
                            '',
                            false,
                            array(
								'class'         => 'pm-user-profile',
								'force_display' =>true,
                            )
                        );
																   echo wp_kses_post( $avatar );
						?>
                        </div>
                    <textarea id="messenger_textarea" data-emojiable="true"  name="content" style="min-width: 100%;height:100px;"
                        
                               form="chat_message_form" placeholder="<?php esc_attr_e( 'Type your message..', 'profilegrid-user-profiles-groups-and-communities' ); ?>" ></textarea> 
                    <input type="hidden" disabled  maxlength="4" size="4" value="1000" id="counter">
                    <input type="hidden" name="sid" value="" />   
                    <div class="pm-messenger-button">
                        <label>
                          <input id="send_msg_btn" type="submit" name="send" value="<?php esc_attr_e( 'send', 'profilegrid-user-profiles-groups-and-communities' ); ?>"/>
                    <svg width="100%" height="100%" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill:#ccc">
    <g transform="matrix(1.05995e-15,17.3103,-17.3103,1.05995e-15,22248.8,-22939.9)">
        <path d="M1340,1256C1340,1256 1350.4,1279.2 1352.6,1284.1C1352.68,1284.28 1352.65,1284.49 1352.53,1284.65C1352.41,1284.81 1352.22,1284.89 1352.02,1284.86C1349.73,1284.54 1344.07,1283.75 1342.5,1283.53C1342.26,1283.5 1342.07,1283.3 1342.04,1283.06C1341.71,1280.61 1340,1268 1340,1268C1340,1268 1338.33,1280.61 1338.01,1283.06C1337.98,1283.31 1337.79,1283.5 1337.54,1283.53C1335.97,1283.75 1330.28,1284.54 1327.98,1284.86C1327.78,1284.89 1327.58,1284.81 1327.46,1284.65C1327.35,1284.49 1327.32,1284.28 1327.4,1284.1C1329.6,1279.2 1340,1256 1340,1256Z"/>
    </g>
    </svg>
                        </label>      
                    </div>
                </div>
                    </div>
            </form>
                
               

        </div>
</div>

        </div> </div> 
                
            <?php
	}

	public function pm_get_notification_html( $uid ) {
        ?>
         
         <!-----PM Notification----->
         
         <div id="pm_notification_view_area" class="pm-notification-view-area"> 
         
		 <?php
			$pm_notification = new Profile_Magic_Notification();
			$pm_notification->pm_generate_notification_without_heartbeat();
			?>
         
         </div>
         
         
         
         
         
         
         <?php
	}

	public function pm_get_friends_action_bar_html( $u1, $view ) {
		switch ( $view ) {
			case 1:
				?>
                    <div class="pm-friend-action-bar pm-dbfl">
                        <button class="pm-difr pm-delete" onclick="pm_multiple_friends_remove('<?php echo esc_attr( $u1 ); ?>')"><?php esc_html_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                    </div>
                    <?php
				break;
			case 2:
				?>
                    <div class="pm-friend-action-bar pm-dbfl">
                        <button class="pm-difr pm-delete" onclick="pm_multiple_friends_request_delete('<?php echo esc_attr( $u1 ); ?>')"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                        <button class="pm-difr " onclick="pm_multiple_friends_request_accept('<?php echo esc_attr( $u1 ); ?>')"><?php esc_html_e( 'Accept', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                    </div>
                    <?php
				break;
			case 3:
				?>
                    <div class="pm-friend-action-bar pm-dbfl">
                        <button class="pm-difr pm-delete" onclick="pm_multiple_friends_request_cancel('<?php echo esc_attr( $u1 ); ?>')"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                    </div>
                    <?php
				break;

			default:
				break;
		}
	}

	public function pm_get_my_friends_html( $uid, $pagenum, $pm_f_search, $limit, $view = 1 ) {
         $pmfriends       = new PM_Friends_Functions();
		$pmrequests       = new PM_request();
		$dbhandler        = new PM_DBhandler();
		$identifier       = 'FRIENDS';
		$path             =  plugin_dir_url( __FILE__ );
		$current_user     = wp_get_current_user();
		$pagenum          = isset( $pagenum ) ? absint( $pagenum ) : 1;
		$offset           = ( $pagenum - 1 ) * $limit;
		$meta_query_array = $pmrequests->pm_get_user_meta_query( filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		$date_query       = $pmrequests->pm_get_user_date_query( filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		switch ( $view ) {
			case 1:
				 $myfriends = $pmfriends->profile_magic_my_friends( $uid );
				if ( $uid== get_current_user_id() ) {
                                        //$error = __('You do not have any friends yet. You can add friends by sending friendship requests to people from their user profiles.','profilegrid-user-profiles-groups-and-communities');
                                        $error = '<span class="pg-alert-warning pg-alert-info">'.esc_html__( 'You do not have any friends yet. You can add friends by sending friendship requests to people from their user profiles.', 'profilegrid-user-profiles-groups-and-communities' ) .'</span>';
					//$error = __( '<div class="pg-alert-warning pg-alert-info">You do not have any friends yet. You can add friends by sending friendship requests to people from their user profiles.</div> ', 'profilegrid-user-profiles-groups-and-communities' );
				} else {
					$display_name = $pmrequests->pm_get_display_name( $uid );
					/* translators: %s: Display name. */
					$error        = '<span class="pg-alert-warning pg-alert-info">'. sprintf( __( '%s does not have any friends yet.', 'profilegrid-user-profiles-groups-and-communities' ), $display_name ).'</span>';
				}
				break;
			case 2:
				 $myfriends = $pmfriends->profile_magic_my_friends_requests( $uid );
				 $error     = __( '<div class="pg-alert-warning pg-alert-info">No friend requests waiting for response</div>', 'profilegrid-user-profiles-groups-and-communities' );
				break;
			case 3:
				 $myfriends = $pmfriends->profile_magic_my_friends_requests( $uid, 1 );
				$error      = __( '<div class="pg-alert-warning pg-alert-info">No pending friend requests.</div> ', 'profilegrid-user-profiles-groups-and-communities' );
				break;
		}
		$u1 = $pmrequests->pm_encrypt_decrypt_pass( 'encrypt', $uid );
		if ( isset( $myfriends ) && !empty( $myfriends ) ) {
				$my_friends_users       =  $dbhandler->pm_get_all_users( $pm_f_search, $meta_query_array, '', $offset, $limit, 'ASC', 'ID', array(), $date_query, $myfriends );
				$my_friends_total_users = count( $dbhandler->pm_get_all_users( $pm_f_search, $meta_query_array, '', '', '', 'ASC', 'ID', array(), $date_query, $myfriends ) );
				$num_of_friends_pages   = ceil( $my_friends_total_users/$limit );
				$pagination             = $dbhandler->pm_get_pagination( $num_of_friends_pages, $pagenum );
		}

		?>
		<?php
		if ( isset( $myfriends ) && !empty( $myfriends ) ) :
            if ( $current_user->ID==$uid ) {
				$this->pm_get_friends_action_bar_html( $u1, $view );}
            ?>
            
                <div class="pm-my-friends">
			<?php $pmfriends->profile_magic_friends_result_html( $my_friends_users, $uid, $view ); ?>
                </div>
			<?php else : ?>
                <div class="pm-my-friends">
						<?php echo wp_kses_post( $error ); ?>
                </div>
                <?php endif; ?>
			<?php
			if ( isset( $myfriends ) && !empty( $myfriends ) && $num_of_friends_pages>1 ) :
                echo wp_kses_post( $pagination );
                endif;
	}

	public function pg_blog_popup_html_generator( $type, $id, $gid, $select_type = '', $count = '', $uids = '' ) {
		switch ( $type ) {
			case 'change_status':
                   $html = $this->change_blog_status_popup( $id );
			    break;
			case 'access_control':
				$html = $this->change_blog_access_control_popup( $id, $gid );
                break;
			case 'edit':
				$html = $this->edit_blog_post_popup( $id, $gid );
                break;
			case 'add_admin_note':
				$html = $this->add_admin_note_on_blog_post_popup( $id, $gid );
                break;
			case 'message':
				$html = $this->send_message_to_author_popup( $id, $gid );
                break;
			case 'select_all':
				$html = $this->select_all_blog_popup( $id, $gid );
                break;
			case 'change_status_bulk':
				$html = $this->change_bulk_blog_status_popup( $id, $gid );
                break;
			case 'access_control_bulk':
				$html = $this->change_bulk_blog_access_control_popup( $id, $gid );
                break;
			case 'add_admin_note_bulk':
				$html = $this->add_admin_note_on_bulk_blog_post_popup( $id, $gid );
                break;
			case 'message_bulk':
				$html = $this->send_message_to_bulk_author_popup( $id, $gid );
                break;
			default:
				$html ='';
                break;
		}
        if(!empty($html)){  
            echo wp_kses_post( $html );
        }
        else{
            echo '';
        }
		
	}

	public function select_all_blog_popup( $total, $single ) {
		$path        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_status = get_post_status( $post_id );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
        
                        <div class="pm-field-input">
                           <div class="pmradio">
                               <div class="pm-radio-option">
                                   <input type="radio" name="pm_blog_select_type" id="pm_blog_select_type" value="this_page" checked="checked">
							<?php /* translators: %s: Number of blogs on current page. */ printf( esc_html__( 'Select blogs on this page(%s)', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $single ) ); ?>
                               </div>
                                <div class="pm-radio-option">
                                <input type="radio" name="pm_blog_select_type" id="pm_blog_select_type" value="all">
							<?php /* translators: %s: Total number of blogs. */ printf( esc_html__( 'Select All(%s)', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $total ) ); ?>                
                                </div>
                               
                            </div>
                        </div>             
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_select_blog_posts()"><?php esc_html_e( 'Select', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function change_blog_status_popup( $post_id ) {
		$path        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_status = get_post_status( $post_id );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Change Blog Post Status', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_change_post_status_form" id="pg_change_post_status_form">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
        
                        <div class="pm-field-input">
                           <div class="pmradio">
                               <div class="pm-radio-option">
                                   <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="publish" <?php checked( 'publish', $post_status ); ?>>
							<?php esc_html_e( 'Published', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                               </div>
                                <div class="pm-radio-option">
                                <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="pending" <?php checked( 'pending', $post_status ); ?>>
							<?php esc_html_e( 'Pending Review', 'profilegrid-user-profiles-groups-and-communities' ); ?>                   </div>
                               <div class="pm-radio-option">
                                <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="draft" <?php checked( 'draft', $post_status ); ?>>
							<?php esc_html_e( 'Draft', 'profilegrid-user-profiles-groups-and-communities' ); ?>                 </div>
                               
                            </div>
                        </div>             
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_status()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_post_status" />
            <input type="hidden" name="pg_action" id="pg_action" value="pm_save_post_status" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_post_status' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
        </form>
            <?php
	}

	public function change_blog_status_success_popup( $post_status ) {
		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of blog posts. */
			$content = sprintf( __( 'Status of<b> %d</b> blog post(s) was changed successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
            ( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %s: New post status. */
			$content                          = sprintf( __( 'The status of post was successfully changed to %s ', 'profilegrid-user-profiles-groups-and-communities' ), $post_status );
		}
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
							echo wp_kses_post( $content );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
         
            <?php
	}

	public function change_blog_access_control_popup( $post_id, $gid ) {
        $path                    =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_content_access       = get_post_meta( $post_id, 'pm_content_access', true );
		$pm_content_access_group = get_post_meta( $post_id, 'pm_content_access_group', true );
		if ( $pm_content_access=='2' && $pm_content_access_group!='all' ) {
			$pm_content_access = '5';
		}
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Access Control', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_change_post_content_access_level" id="pg_change_post_content_access_level">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow pg-info-message">        
                   
					<?php esc_html_e( 'The post will be accessible to:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
               
                </div>
                <div class="pmrow">        
                    <div class="pm-col">
        
                        <div class="pm-field-input">
               <div class="pmradio">
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_popup_1" value="1" <?php checked( '1', $pm_content_access ); ?>/>
                    <label for="pm_content_access_popup_1"> <?php esc_html_e( 'Public', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                   </div>
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_popup_2" value="2" <?php checked( '2', $pm_content_access ); ?> />
                    <label for="pm_content_access_popup_2"> <?php esc_html_e( 'Logged In Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></label> 
                   </div>
                   
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_popup_5" value="5" <?php checked( '5', $pm_content_access ); ?> />
                    <label for="pm_content_access_popup_5"> <?php esc_html_e( 'Only Group Members', 'profilegrid-user-profiles-groups-and-communities' ); ?></label> 
                   </div>
                   
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_popup_3" value="3" <?php checked( '3', $pm_content_access ); ?> />
                    <label for="pm_content_access_popup_3"> <?php esc_html_e( "Only Author's Friends", 'profilegrid-user-profiles-groups-and-communities' ); ?></label> 
                   </div>
                   
                    
                    
                </div>
            </div>   
                                  
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_access_content()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_post_content_access_level" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_post_content_access_level' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
        </form>
            <?php
	}

	public function change_blog_access_control_success_popup( $post_status ) {
		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );

		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of blog posts. */
			$content = sprintf( __( 'Access level of <b>%d</b> blog post(s) was changed successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
			( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			switch ( $post_status ) {
				case 1:
					$new_status = __( 'Public', 'profilegrid-user-profiles-groups-and-communities' );
					break;
				case 2:
					$new_status = __( 'Logged in users', 'profilegrid-user-profiles-groups-and-communities' );
					break;
				case 3:
					$new_status = __( "Only Author's Friends", 'profilegrid-user-profiles-groups-and-communities' );
					break;
				case 5:
					$new_status = __( 'Only Group Members', 'profilegrid-user-profiles-groups-and-communities' );
					break;
				default:
					$new_status = __( 'Public', 'profilegrid-user-profiles-groups-and-communities' );
					break;
			}
			/* translators: %s: New visibility label. */
			$content = sprintf( __( 'The post will now be visible to %s', 'profilegrid-user-profiles-groups-and-communities' ), $new_status );
		}

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
						esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
                            esc_html_e( "You changed post's privacy level.", 'profilegrid-user-profiles-groups-and-communities' );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()"  class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function edit_blog_post_popup( $post_id, $gid ) {
        wp_enqueue_script( 'jquery-form' );
		$dbhandler = new PM_DBhandler();
		$post      = get_post( $post_id );
		$desc      = '';
		$path      =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$tiny      = ( $dbhandler->get_global_option_value( 'pm_blog_editor', '0' )==0?false:true );
		$settings  = array(
			'wpautop'           => false,
			'media_buttons'     => false,
			'textarea_name'     => 'blog_description',
			'textarea_rows'     => 10,
			'tabindex'          => '',
			'tabfocus_elements' => ':prev,:next',
			'editor_css'        => '',
			'editor_class'      => '',
			'teeny'             => false,
			'dfw'               => false,
			'tinymce'           => $tiny,
			'quicktags'         => false,
		);
		if ( isset( $post ) ) {
			$desc = $post->post_content;}
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Edit Post', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_edit_blog_post" id="pg_edit_blog_post">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
             <div class="pmrow">
               <div class="pg-users-send-box pm-dbfl">        
        
      
                    <div class="pm-field-lable pm-difl">
                            <label for="blog_title"><?php esc_html_e( 'Title', 'profilegrid-user-profiles-groups-and-communities' ); ?><sup class="pm_estric">*</sup></label>
                    </div>
                    <div class="pm-field-input pm_required pm-difl">
                          <input title="Enter your title" type="text" class="" maxlength="" value="<?php
							if ( isset( $post ) ) {
								echo esc_attr( $post->post_title );}
							?>" id="blog_title" name="blog_title" placeholder="">
                          <div class="errortext" style="display:none;"></div>
                    </div>

            </div>
      
               <div class="pg-users-send-box pm-dbfl">        


            <div class="pm-field-lable pm-difl">
                    <label for="blog_description"><?php esc_html_e( 'Description', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
              </div>
              <div class="pm-field-input pm-difl">
			  <?php wp_editor( $desc, 'blog_description', $settings ); ?>                
                    <div class="errortext" style="display:none;"></div>
              </div>
 
        </div>
            </div>
            </div>
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_edit_blog_post()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_edit_blog_post" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_edit_blog_post' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
            
        </form>
            <?php
					 _WP_Editors::enqueue_scripts();
			_WP_Editors::editor_js();
			print_footer_scripts();
	}

	public function sav_blog_post_success_popup( $post_status ) {
		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );

		( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
							esc_html_e( 'The post was updated successfully!', 'profilegrid-user-profiles-groups-and-communities' );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function add_admin_note_on_blog_post_popup( $post_id, $gid ) {
		$path                   =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_admin_note_content  = get_post_meta( $post_id, 'pm_admin_note_content', true );
		$lenght                 = strlen( $pm_admin_note_content );
		$char_limit             = 5000-$lenght;
		$pm_admin_note_position = get_post_meta( $post_id, 'pm_admin_note_position', true );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Manager Note', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_add_admin_note" id="pg_add_admin_note">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow">
                <div class="pg-users-send-box pm-dbfl">        
               
                        <div class="pm-field-lable pm-difl">
                                <label for="pm_admin_note_content"><?php esc_html_e( 'Content', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl">
                            <textarea name="pm_admin_note_content" id="pm_admin_note_content" maxlength="5000" size="5000" onkeyup="pg_count_left_charactors('pm_admin_note_content','pg_text_counter','{CHAR} <?php esc_html_e( 'characters left', 'profilegrid-user-profiles-groups-and-communities' ); ?>','5000')">
                                                                                                                                                                                                                                 <?php
																																																									if ( isset( $pm_admin_note_content ) && !empty( trim( $pm_admin_note_content ) ) ) {
																																																										echo wp_kses_post( $pm_admin_note_content );}
																																																									?>
                            </textarea>          
                            <div class="errortext" style="display:none;"></div>
                            <div id="pg_text_counter">
                                <?php
                                /* translators: %d: Remaining character limit. */
                                printf( esc_html__( '%d characters left', 'profilegrid-user-profiles-groups-and-communities' ), $char_limit );
                                ?>
                            </div>
                        </div>

                </div>
                <div class="pg-users-send-box pm-dbfl">        
                        <div class="pm-field-lable pm-difl">
                                <label for="pm_admin_note_position"><?php esc_html_e( 'Position:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl">
               <div class="pmradio">
                   <div class="pm-radio-option pm-difl">
                    <input type="radio" name="pm_admin_note_position" id="pm_admin_note_position" value="top" 
                    <?php
                    checked( 'top', $pm_admin_note_position );
					if ( $pm_admin_note_position=='' ) {
						echo 'checked';}
                    ?>
                    />
			<?php esc_html_e( 'Top', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                    <div class="pm-radio-option pm-difl">
                    <input type="radio" name="pm_admin_note_position" id="pm_admin_note_position" value="bottom" <?php checked( 'bottom', $pm_admin_note_position ); ?> />
				<?php esc_html_e( 'Bottom', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                  
                    
                </div>
            </div>   
                                  
                </div>
                    
                </div>
                
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
			<?php
			if ( $char_limit=='5000' ) {
				?>
                   <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_admin_note_content()"><?php esc_html_e( 'Add', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <?php
			} else {
				?>
                    <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_admin_note_content()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                    <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_delete_admin_note_content()"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                
                <?php
			}
			?>
                
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_admin_note_content" />
             <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_admin_note_content' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
           
        </form>
            <?php
	}

	public function delete_admin_note_popup( $postid ) {
        $path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $postid=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_delete_admin_note" id="pg_delete_admin_note">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
						esc_html_e( 'Please confirm you wish to delete this Manager Note from the post.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_delete_admin_note()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_delete_admin_note" />
             <?php $this->basic_functions->pm_render_nonce_field( 'delete_pm_admin_note' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $postid ); ?>" />
         </form>
            
            <?php
	}

	public function pm_delete_admin_note_success_popup( $post_status ) {
        $path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );

		( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
							esc_html_e( 'The Manager Note was deleted successfully.', 'profilegrid-user-profiles-groups-and-communities' );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}


	public function save_admin_note_success_popup( $post_status ) {
		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of blog posts. */
			$content = sprintf( __( 'The Manager Note of <b>%d</b> blog post(s) was added successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
			( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			$content                          = __( 'The Manager Note was added successfully to the post.', 'profilegrid-user-profiles-groups-and-communities' );
		}
		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()"> 
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
							echo wp_kses_post( $content );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function send_message_to_author_popup( $post_id, $gid, $type = 'blog' ) {
        $pm_request = new PM_request();
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		if ( $type=='blog' ) {
            $post      = get_post( $post_id );
            $author_id = $post->post_author;
		} else {
			$author_id = $post_id;
		}
		$username     = $pm_request->profile_magic_get_user_field_value( $author_id, 'display_name' );
		$first_name   = $pm_request->profile_magic_get_user_field_value( $author_id, 'first_name' );
		$last_name    = $pm_request->profile_magic_get_user_field_value( $author_id, 'last_name' );
		$display_name = $pm_request->pm_get_display_name( $author_id );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'New Message', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_send_author_message" id="pg_send_author_message">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow">        
                    <div class="pg-users-send-box pm-dbfl">
                     
                        <div class="pm-field-lable pm-difl">
                                <label><?php esc_html_e( 'To:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl">
                            <span class="pm-message-username"><?php echo '@' . esc_html( $username ); ?></span>
						<?php if ( $first_name!='' && $last_name!='' ) : ?>
                            <span class="pm-message-dn">(<?php echo wp_kses_post( $display_name ); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="pmrow">        
                    <div class="pg-users-send-box pm-dbfl">
                       
                        <div class="pm-field-lable pm-difl">
                                <label for="pm_author_message"><?php esc_html_e( 'Message:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
					<?php if ( $type=='blog' ) : ?>
                        <div class="pm-field-input pm-difl">
                           <textarea name="pm_author_message" id="pm_author_message" ><?php /* translators: %s: Blog post title. */ printf( esc_html__( 'About your blog post: %s', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $post->post_title ) ); ?></textarea>          
                            <div class="errortext" style="display:none;"></div>
                            
                        </div>   
                        <?php else : ?>
                        <div class="pm-field-input pm-difl">
                            <textarea name="pm_author_message" id="pm_author_message" placeholder="<?php esc_attr_e( 'Type your message here', 'profilegrid-user-profiles-groups-and-communities' ); ?>"></textarea>          
                            <div class="errortext" style="display:none;"></div>
                            
                        </div> 
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_author_message()"><?php esc_html_e( 'Send', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_send_message_to_author" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'send_pm_message_to_author' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            <input type="hidden" id="type" name="type" value="<?php echo esc_attr( $type ); ?>" />
           
        </form>
            <?php
	}

	public function author_msg_send_success_popup( $post_status ) {
         $path      =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_request = new PM_request();
		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of recipients. */
			$content = sprintf( __( 'Your message was successfully sent to <b>%d</b> recipient(s).', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
			( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			$display_name                     = $pm_request->profile_magic_get_user_field_value( $post_status, 'display_name' );
			/* translators: %s: Recipient display name. */
			$content                          = sprintf( __( 'Your Message to <b> %s </b> was sent successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $display_name );
		}
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
                            echo wp_kses_post( $content );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}



	public function pg_member_popup_html_generator( $type, $id, $gid ) {
        switch ( $type ) {
			case 'add_user':
                   $html = $this->add_user_in_group_popup( $gid );
			    break;
			case 'remove_user':
				$html = $this->remove_user_in_group_popup( $id, $gid );
                break;
			case 'activate_user':
				$html = $this->activate_user_popup( $id, $gid );
                break;
			case 'deactivate_user':
				$html = $this->deactivate_user_popup( $id, $gid );
                break;
			case 'reset_password':
				$html = $this->reset_password_user_popup( $id, $gid );
                break;
			case 'message':
				$html = $this->send_message_to_author_popup( $id, $gid, 'member' );
                break;
			case 'edit':
				$html = $this->edit_user_popup( $id, $gid );
                break;
			case 'select_all':
				$html = $this->select_all_blog_popup( $id, $gid );
                break;
			case 'remove_user_bulk':
				$html = $this->remove_bulk_user_in_group_popup( $id, $gid );
                break;
			case 'deactivate_user_bulk':
				$html = $this->deactivate_bulk_user_popup( $id, $gid );
                break;
			case 'message_bulk':
				$html = $this->send_message_to_bulk_author_popup( $id, $gid, $type );
                break;
			default:
				$html ='';
                break;
		}
		if(!empty($html)){  
            echo wp_kses_post( $html );
        }
        else{
            echo '';
        }
	}

	public function pg_admin_popup_html_generator( $type, $id, $gid ) {
         $popoup_html = new Profilegrid_Group_Multi_Admins_Html_Creator();
		switch ( $type ) {
			case 'add_admin':
                   $html = $popoup_html->add_admin_in_group_popup( $gid );
			    break;
			case 'remove_user':
				$html = $popoup_html->remove_admin_in_group_popup( $id, $gid );
                break;
			case 'message':
				$html = $popoup_html->send_message_to_admin_popup( $id, $gid );
                break;
			case 'remove_admin_bulk':
				$html = $popoup_html->remove_bulk_admin_in_group_popup( $id, $gid );
                break;
			case 'message_admins_bulk':
				$html = $popoup_html->send_message_to_bulk_admin_popup( $id, $gid );
                break;
			case 'add_cog_admin':
				$html = $popoup_html->add_admin_in_group_cog_popup( $id, $gid );
                break;
			default:
				$html ='';
                break;
		}
        if(!empty($html)){  
            echo wp_kses_post( $html );
        }
        else{
            echo '';
        }
	}

	public function add_user_in_group_popup( $gid ) {
		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Add User', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_add_user" id="pg_add_user">
                    <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                        <div class="pmrow"> 
                     <div class="pm-dbfl pg-info-message">        
                    
							<?php
                            /* translators: %s: Maximum number of users that can be added. */
                            echo sprintf(wp_kses(__( 'You can simultaneously add upto <strong>%s</strong> to your group using this method.', 'profilegrid-user-profiles-groups-and-communities' ), array( 'strong' => array() )),esc_html( '10 users' ) );
							?>
                                         

                        </div>
                    <div class="group-popup-container">
                        <div class="pm-field-input pm-pad10 pm-dbfl">
                            <div class="pm_repeat pm-pad10">
                                <input type="email" id="pm_email_address" name="pm_email_address[]" placeholder="Enter Email Address" value="">
                                <a class="pg-add-user"><span onClick="pm_add_repeat(this)"><?php esc_html_e( 'Add More', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></a><a class="pg-remove-user pm-remove"><span class="remove" onClick="pm_remove_repeat(this)"><?php esc_html_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></a>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                        </div>
                        
                    </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_invite_user()"><?php esc_html_e( 'Invite', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a onclick="pg_edit_popup_close()" class="pm-remove"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_invite_user" />
             <?php $this->basic_functions->pm_render_nonce_field( 'invite_pm_user' ); ?>
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
           
        </form>
            <?php
	}

	public function invitation_send_result_success_popup( $result, $post_status = 'results' ) {
		$path                             =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_request                       = new PM_request();
		( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Results', 'profilegrid-user-profiles-groups-and-communities' );

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-dbfl pg-info-message">
					<?php esc_html_e( 'Here are the results of User Invites you just sent.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
                    </div>
                </div>
                
                <div class="pmrow">        
                    <div class="pm-col">
					<?php echo wp_kses_post( $result ); ?>         
                    </div>
                </div>
                
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function remove_user_in_group_popup( $id, $gid ) {
		$path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $id=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_remove_user_in_group" id="pg_remove_user_in_group">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
						esc_html_e( 'Please confirm you really want to remove the user from the group.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_remove_user_from_group()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_remove_user_from_group" />
             <?php $this->basic_functions->pm_render_nonce_field( 'remove_pm_user_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            </form>
            <?php
	}

	public function remove_admin_in_group_popup( $id, $gid ) {
		$path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $id=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
                 <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_remove_admin" id="pg_remove_admin">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
						$user = get_user_by( 'ID', $id );
						/* translators: %s: Username being removed from group manager list. */
						printf( wp_kses_post( __( 'You are going to remove <strong>%s</strong> from the Group Manager List. The user will still remain a member of the group. Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $user->user_login ) );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_remove_admin_from_group()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_remove_admin" />
             <?php $this->basic_functions->pm_render_nonce_field( 'remove_pm_admin_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
                 </form>
            <?php
	}

	public function pm_remove_user_success_popup( $post_status ) {
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_request = new PM_request();
		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of users. */
			$content = sprintf( __( '<b>%d</b> User(s) were removed from the group successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
			( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			$content                          = __( 'User successfully removed.', 'profilegrid-user-profiles-groups-and-communities' );

		}

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
                            echo wp_kses_post( $content );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function deactivate_user_popup( $id, $gid ) {
		$gid              = absint( $gid );
		$current_user_id  = get_current_user_id();
        $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		// SECURITY: only privileged users should see the deactivate controls/nonce.
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			echo '<div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap"><div class="pmrow"><div class="pm-col">';
			esc_html_e( 'You are not authorized to perform this action.', 'profilegrid-user-profiles-groups-and-communities' );
			echo '</div></div></div>';
			return;
		}
		$id                          = absint( $id );
		$postid                      = $id;
        $path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $postid=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Confirm', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_deactivate_user_in_group" id="pg_deactivate_user_in_group">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
						esc_html_e( 'Please confirm you really want to suspend the user from the group.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_deactivate_user_from_group()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_deactivate_user_from_group" />
             <?php $this->basic_functions->pm_render_nonce_field( 'deactivate_pm_user_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
         </form>
            
            <?php
	}

	public function pm_deactivate_user_success_popup( $post_status ) {
		$path                             =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_request                       = new PM_request();
		( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
		if ( is_array( $post_status ) ) {
			$title   = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			/* translators: %d: Number of users. */
			$content = sprintf( __( '<b>%d</b> User(s) were Suspended from the group successfully.', 'profilegrid-user-profiles-groups-and-communities' ), $post_status['count'] );
		} else {
			( $post_status=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Success!', 'profilegrid-user-profiles-groups-and-communities' );
			$content                          = __( 'User successfully Suspended.', 'profilegrid-user-profiles-groups-and-communities' );

		}
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					if ( $post_status=='failed' ) :
                        esc_html_e( 'Something went wrong. Please try again or contact the admin.', 'profilegrid-user-profiles-groups-and-communities' );
                        else :
							echo wp_kses_post( $content );
                        endif;
                        ?>
                                 
                    
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function reset_password_user_popup( $id, $gid ) {
        $path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $id=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Reset Password', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_reset_user_password" id="pg_reset_user_password">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap"> 
                
                <div class="pmrow">
                    <div class="pm-dbfl pg-info-message">
					<?php
						esc_html_e( 'Enter a new password manually or autogenerate.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                
                <div class="group-popup-container">
                     <div class="pm-field-input">
                         <input type='password' id='pm_new_pass' name='pm_new_pass' placeholder="<?php esc_attr_e( 'Enter New Password', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onkeyup="pg_check_password_strenth()" />	
                         <div id="pg_password_result" class="pm-dbfl">
                             <div id="pg_password_meter_outer" class="pm-difl" style="display:none;">
                                 <div id="pg_password_meter_inner" class="pm-difr"></div>
                             </div>  
                             <div id="pg_password_strenth_text"></div>
                         </div>
                     </div>
                     <div class="pm-field-input pm-dbfr">
                         <a onclick="pg_password_auto_generate('pm_new_pass')"><?php esc_html_e( 'Autogenerate', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
                     </div>  
                    
                 <div class="pg-email-password pm-dbfl">

                             <div class="pm-field-input">
                                 <label for="pm_email_password_to_user">
                                 <input type="checkbox" name="pm_email_password_to_user" id="pm_email_password_to_user" value="1" checked="checked">
							 <?php esc_html_e( 'Email new password to the user', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                 </label>
                             </div>             
                         </div>
                   
             </div>
                </div>
       
            </div>
             
             
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a id="pm_member_reset_password_link" class="pg-setting-disabled"><?php esc_html_e( 'Reset', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_reset_user_password" />
             <?php $this->basic_functions->pm_render_nonce_field( 'reset_pm_user_password' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
         </form>
            
            <?php
	}

	public function pm_reset_user_password_success_popup( $name, $is_emailed ) {
        $path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_request = new PM_request();

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                <?php esc_html_e( 'Success!', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                <div class="pm-popup-close pm-difr" onclick="pg_edit_popup_close()">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
			<?php
                        if ( $is_emailed ) :
                            /* translators: %s: user name */
                            printf( wp_kses_post( __( 'Password for user <b>%s</b> was reset and emailed successfully.', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $name ) );
                        else :
                            /* translators: %s: user name */
                            printf( wp_kses_post( __( 'Password for user <b>%s</b> was reset successfully.', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $name ) );
                        endif;
                        ?>       
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Close', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            
            <?php
	}

	public function change_bulk_blog_status_popup( $id, $gid ) {
        $pm_request  = new PM_request();
		$path        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_status = 'publish';

		$post_id = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count   = count( $id );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Change Blog Post Status', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_change_post_status_form" id="pg_change_post_status_form">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                
                 <div class="pmrow pg-info-message">        
                    <?php
                    /* translators: %d: count of selected blog posts */
                    printf( wp_kses_post( __( 'You are going to change status of <b>%d</b> selected blog posts.', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
                    ?>
                 </div>
                <div class="pmrow">        
                    <div class="pm-col">
        
                        <div class="pm-field-input">
                           <div class="pmradio">
                               <div class="pm-radio-option">
                                   <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="publish" <?php checked( 'publish', $post_status ); ?>>
							<?php esc_html_e( 'Published', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                               </div>
                                <div class="pm-radio-option">
                                <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="pending" <?php checked( 'pending', $post_status ); ?>>
							<?php esc_html_e( 'Pending Review', 'profilegrid-user-profiles-groups-and-communities' ); ?>                   </div>
                               <div class="pm-radio-option">
                                <input type="radio" name="pm_change_blog_status" id="pm_change_blog_status" value="draft" <?php checked( 'draft', $post_status ); ?>>
							<?php esc_html_e( 'Draft', 'profilegrid-user-profiles-groups-and-communities' ); ?>                 </div>
                               
                            </div>
                        </div>             
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_status()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_post_status" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_post_status' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
        </form>
            <?php
	}

	public function change_bulk_blog_access_control_popup( $id, $gid ) {
        $pm_request        = new PM_request();
		$path              =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_content_access = '1';

		$post_id = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count   = count( $id );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Access Control', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_change_post_content_access_level" id="pg_change_post_content_access_level">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow pg-info-message">        
                    <?php
                        /* translators: %d: count of selected posts */
                        printf( wp_kses_post( __( 'Selected <b>%d</b> post(s) will be accessible to:', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
                    ?>
                </div>
                <div class="pmrow">        
                    <div class="pm-col">
        
                        <div class="pm-field-input">
               <div class="pmradio">
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_post_access_public" value="1" <?php checked( '1', $pm_content_access ); ?>/>
                    <label for="pm_post_access_public"><?php esc_html_e( 'Public', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                   </div>
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_post_access_logged-in-users" value="2" <?php checked( '2', $pm_content_access ); ?> />
                    <label for="pm_post_access_logged-in-users"> <?php esc_html_e( 'Logged In Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                   </div>
                   
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_post_access-group-member" value="5" <?php checked( '5', $pm_content_access ); ?> />
                    <label for="pm_post_access-group-member"><?php esc_html_e( 'Only Group Members', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                   </div>
                   
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_post_access-user-friends" value="3" <?php checked( '3', $pm_content_access ); ?> />
                    <label for="pm_post_access-user-friends"><?php esc_html_e( "Only Author's Friends", 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                   </div>
                    
                </div>
            </div>   
                                  
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_access_content()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_post_content_access_level" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_post_content_access_level' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
        </form>
            <?php
	}

	public function add_admin_note_on_bulk_blog_post_popup( $id, $gid ) {
		$pm_request        = new PM_request();
		$path              =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$pm_content_access = '1';

		$post_id                = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count                  = count( $id );
		$pm_admin_note_content  = '';
		$lenght                 = strlen( $pm_admin_note_content );
		$char_limit             = 5000-$lenght;
		$pm_admin_note_position = 'top';
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'Manager Note', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_add_admin_note" id="pg_add_admin_note">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow ">        
                    <div class="pg-users-send-box pm-dbfl">

                        <div class="pm-field-lable pm-difl">
                                <label for="pm_admin_note_content"><?php esc_html_e( 'Content', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl">
                            <textarea name="pm_admin_note_content" id="pm_admin_note_content" maxlength="5000" size="5000" onkeyup="pg_count_left_charactors('pm_admin_note_content','pg_text_counter','{CHAR} <?php esc_html_e( 'characters left', 'profilegrid-user-profiles-groups-and-communities' ); ?>','5000')">
                                                                                                                                                                                                                                 <?php
																																																									if ( isset( $pm_admin_note_content ) ) {
																																																										echo wp_kses_post( $pm_admin_note_content );}
																																																									?>
                            </textarea>          
                            <div class="errortext" style="display:none;"></div>
                            <div id="pg_text_counter">
                                <?php
                                /* translators: %d: Remaining character limit. */
                                printf( wp_kses_post( __( '<b>%d</b> characters left', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $char_limit ) );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmrow">        
                    <div class="pg-users-send-box pm-dbfl">

                        <div class="pm-field-lable pm-difl">
                                <label for="pm_admin_note_position"><?php esc_html_e( 'Position:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl">
               <div class="pmradio">
                   <div class="pm-radio-option pm-difl">
                    <input type="radio" name="pm_admin_note_position" id="pm_admin_note_position" value="top" 
                    <?php
                    checked( 'top', $pm_admin_note_position );
					if ( $pm_admin_note_position=='' ) {
						echo 'checked';}
                    ?>
                    />
			<?php esc_html_e( 'Top', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                    <div class="pm-radio-option pm-difl">
                    <input type="radio" name="pm_admin_note_position" id="pm_admin_note_position" value="bottom" <?php checked( 'bottom', $pm_admin_note_position ); ?> />
				<?php esc_html_e( 'Bottom', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                  
                    
                </div>
            </div>   
                                  
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
			<?php
			if ( $char_limit=='5000' ) {
				?>
                   <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_admin_note_content()"><?php esc_html_e( 'Add', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <?php
			} else {
				?>
                    <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_post_admin_note_content()"><?php esc_html_e( 'Update', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                    <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_delete_admin_note_content()"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                
                <?php
			}
			?>
                
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_save_admin_note_content" />
             <?php $this->basic_functions->pm_render_nonce_field( 'save_pm_admin_note_content' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
           
        </form>
            <?php
	}

	public function send_message_to_bulk_author_popup( $post_id, $gid, $type = 'blog' ) {
		$pm_request = new PM_request();

		$path =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		if ( $type=='blog' ) {
			$id = array();
			foreach ( $post_id as $pid ) {
				 $post = get_post( $pid );
				 $id[] = $post->post_author;
			}
			$id        = array_unique( $id );
			$count     = count( $id );
			$author_id = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );

		} else {
			$count     = count( $post_id );
			$author_id = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $post_id ) );
		}

		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php esc_html_e( 'New Message', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_send_author_message" id="pg_send_author_message">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">
                <div class="pmrow">        
                    <div class="pg-users-send-box pm-dbfl">
                        <div class="pm-field-lable pm-difl">
                                <label><?php esc_html_e( 'To:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                        <div class="pm-field-input pm-difl pm-popup-title">
                        <?php /* translators: %d: Number of recipients. */ printf( esc_html__( '%d Recipient(s)', 'profilegrid-user-profiles-groups-and-communities' ), $count ); ?>
                        </div>
                    </div>
                </div>
                <div class="pmrow">        
                    <div class="pg-users-send-box pm-dbfl">

                        <div class="pm-field-lable pm-difl">
                                <label for="pm_author_message"><?php esc_html_e( 'Message:', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                        </div>
                       
                        <div class="pm-field-input pm-difl">
                            <textarea name="pm_author_message" id="pm_author_message" placeholder="<?php esc_html_e( 'Type your message here', 'profilegrid-user-profiles-groups-and-communities' ); ?>"></textarea>          
                            <div class="errortext" style="display:none;"></div>
                            
                        </div> 
                        
                    </div>
                </div>
            </div>
        
            <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_submit_author_message()"><?php esc_html_e( 'Send', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_send_message_to_author" />
		 <?php $this->basic_functions->pm_render_nonce_field( 'send_pm_message_to_author' ); ?>
            <input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $author_id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            <input type="hidden" id="type" name="type" value="<?php echo esc_attr( $type ); ?>" />
           
        </form>
            <?php
	}

	public function remove_bulk_user_in_group_popup( $id, $gid ) {
		$pm_request            = new PM_request();
		$path                  =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_id               = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count                 = count( $id );
		$pm_admin_note_content = '';
		$title                 = __( 'Remove Users', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_remove_user_in_group" id="pg_remove_user_in_group">

            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
						printf( wp_kses_post( __( 'You are going to remove <b>%d</b> user(s). Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_remove_user_from_group()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_remove_user_from_group" />
             <?php $this->basic_functions->pm_render_nonce_field( 'remove_pm_user_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            </form>
            
            <?php
	}

	public function deactivate_bulk_user_popup( $id, $gid ) {
		$gid              = absint( $gid );
		$current_user_id  = get_current_user_id();
        $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
		// SECURITY: only privileged users should see the deactivate controls/nonce.
		if ( ! current_user_can( 'manage_options' ) && ! is_super_admin( $current_user_id ) && ! $basic_function->pm_user_is_group_manager( $current_user_id, $gid ) ) {
			echo '<div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap"><div class="pmrow"><div class="pm-col">';
			esc_html_e( 'You are not authorized to perform this action.', 'profilegrid-user-profiles-groups-and-communities' );
			echo '</div></div></div>';
			return;
		}
		$pm_request            = new PM_request();
		$path                  =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_id               = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count                 = count( $id );
		$pm_admin_note_content = '';
		$title                 = __( 'Suspend Users', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_deactivate_user_in_group" id="pg_deactivate_user_in_group">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					 <?php
						printf( wp_kses_post( __( 'You are going to suspend <b>%d</b> user(s). Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
?>
						?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_deactivate_user_from_group()"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_deactivate_user_from_group" />
             <?php $this->basic_functions->pm_render_nonce_field( 'deactivate_pm_user_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
         </form>
            
            <?php
	}

	public function pg_get_pending_post_count_html( $gid ) {
        $html       = '';
		$dbhandler  = new PM_DBhandler();
		$meta_query = array(
			'relation' =>'AND',
			array(
				'key'     =>'pm_group',
				'value'   =>sprintf( ':"%s";', $gid ),
				'compare' =>'like',
			),
		);
		$users      =  $dbhandler->pm_get_all_users( '', $meta_query );
		$author_ids = array( '0' );
		foreach ( $users as $user ) {
			array_push( $author_ids, $user->ID );
		}
		$args        = array(
			'post_type'      => 'profilegrid_blogs',
			'posts_per_page' => -1,
			'post_status'    => 'pending',
			'author__in'     => $author_ids,
		);
		$posts       = get_posts( $args );
		$total_posts = count( $posts );
		if ( $total_posts>0 ) :
			$html = '<b id="pg_pending_posts" class="pg-pending-posts">' . $total_posts . '</b>';
            endif;

		return $html;
	}

	public function pg_get_pending_request_count_html( $gid ) {
         $html     = '';
		$dbhandler = new PM_DBhandler();
		$where     = array(
			'gid'    =>$gid,
			'status' =>'1',
		);
		$row       = $dbhandler->get_all_result( 'REQUESTS', '*', $where );
		$requests  = array();
		if ( isset( $row ) && !empty( $row ) ) {
			foreach ( $row as $data ) :
				$user = get_userdata( $data->uid );
				if ( $user ) {
					$requests[] =$data->uid;
				}
                endforeach;
                $total_posts = count( $requests );
		} else {
			$total_posts = 0;
		}
		if ( $total_posts>0 ) :
			$html = '<b id="pg_pending_requests" class="pg-pending-posts">' . $total_posts . '</b>';
            endif;

			 return $html;
	}

	public function pg_group_popup_html_generator( $type, $id, $gid ) {
		switch ( $type ) {
			case 'remove_group':
                   $html = $this->pg_remove_group_in_user_profile_popup( $id, $gid );
			    break;
			case 'decline_request':
				$html = $this->pg_decline_request_to_join_group_popup( $id, $gid );
                break;
			case 'accept_request':
				$html = $this->pg_accept_request_to_join_group_popup( $id, $gid );
                break;
			case 'message_bulk':
				$html = $this->send_message_to_bulk_author_popup( $id, $gid, $type );
                break;
			case 'decline_request_bulk':
				$html = $this->pg_decline_request_bulk_to_join_group_popup( $id, $gid );
                break;
			case 'accept_request_bulk':
				$html = $this->pg_accept_request_bulk_to_join_group_popup( $id, $gid );
                break;
			default:
				$html ='';
                break;
		}
		if(!empty($html)){  
            echo wp_kses_post( $html );
        }
        else{
            echo '';
        }
	}

	public function pg_remove_group_in_user_profile_popup( $id, $gid ) {
        $dbhandler           = new PM_DBhandler();
		$pm_request          = new PM_request();
		$groupinfo           = $dbhandler->get_row( 'GROUPS', $gid );
		$is_leader           = $pm_request->pg_check_in_single_group_is_user_group_leader( $id, $gid );
		$group_manager_label = $pm_request->pm_get_group_admin_label( $gid );

		$path                        =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		( $id=='failed' )?$title = __( 'Failed!', 'profilegrid-user-profiles-groups-and-communities' ):$title = __( 'Leave Group', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_remove_group_in_user_profile" id="pg_remove_group_in_user_profile">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">  
				<?php if ( $is_leader ) : ?>
                     <div class="pm-col">
                        <?php
                            /* translators: 1: Group manager label, 2: Group manager label, 3: Group manager label, 4: Group manager label. */
                            echo sprintf( esc_html__( 'You are currently %1$s of this group. Leaving the group will remove your %2$s privileges along with group membership. To regain %3$s privileges in future, another %4$s must assign you the role first. Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $group_manager_label ), esc_html( $group_manager_label ), esc_html( $group_manager_label ), esc_html( $group_manager_label ) );
						?>
                                
                    </div>
                    <?php else : ?>
                    <div class="pm-col">
                        <?php
                            /* translators: %s: Group name. */
                            echo sprintf( esc_html__( 'You are about to leave %s group. You will no longer have access to Group updates or Group related information. Do you wish to continue?', 'profilegrid-user-profiles-groups-and-communities' ), esc_html( $groupinfo->group_name ) );
						?>
                                
                    </div>
                    <?php endif; ?>
                    
                    
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_remove_user_group('<?php echo esc_attr( $id ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Yes', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'No', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_remove_user_from_group" />
             <?php $this->basic_functions->pm_render_nonce_field( 'remove_pm_user_from_group' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            </form>
            <?php
	}

	public function pg_decline_request_to_join_group_popup( $id, $gid ) {
		$dbhandler  = new PM_DBhandler();
		$pm_request = new PM_request();
		$user       = get_user_by( 'ID', $id );
		$first_name = $pm_request->profile_magic_get_user_field_value( $id, 'first_name' );
		$last_name  = $pm_request->profile_magic_get_user_field_value( $id, 'last_name' );
		$user_name  = $pm_request->profile_magic_get_user_field_value( $id, 'display_name' );
		$groupinfo  = $dbhandler->get_row( 'GROUPS', $gid );
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html__( 'Decline Request', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_decline_request_to_join_group" id="pg_decline_request_to_join_group">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">  
                    <div class="pm-dbfl pg-info-message">
					<?php
					   esc_html_e( 'You are about to decline membership request by:', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                      </div>
                <div class="pmrow">  
                    <div class="pm-col">
                        <div class="pg-group-user-info-box  pm-pad10 pm-bg pm-dbfl">
                            <div class="pg-group-user-avatar pm-difl">
							<?php echo get_avatar( $user->user_email, 26, '', false, array( 'force_display'=>true ) ); ?>
                 
                            </div>
                            <div class="pg-group-user-info pm-dbfl">
                            <div class="pg-group-user-email pm-difl">
						<?php echo esc_html( $user_name ); ?>
                            </div>
                            <div class="pm-difr">
                            <div class="pg-group-user-view-link pm-difr">
                                <a href="<?php echo esc_url( $pm_request->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php esc_html_e( 'View Profile' ); ?></a>
                            </div>
                            </div>
                            </div>
                        </div>     
                    </div>
                </div>

              
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_decline_join_request('<?php echo esc_attr( $id ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Decline', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_decline_join_request" />
             <?php $this->basic_functions->pm_render_nonce_field( 'decline_pm_join_request' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            </form>
            <?php
	}

	public function pg_accept_request_to_join_group_popup( $id, $gid ) {
        $dbhandler  = new PM_DBhandler();
		$pm_request = new PM_request();
		$user       = get_user_by( 'ID', $id );
		$first_name = $pm_request->profile_magic_get_user_field_value( $id, 'first_name' );
		$last_name  = $pm_request->profile_magic_get_user_field_value( $id, 'last_name' );
		$user_name  = $pm_request->profile_magic_get_user_field_value( $id, 'display_name' );
		$groupinfo  = $dbhandler->get_row( 'GROUPS', $gid );
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html__( 'Approve Request', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_decline_request_to_join_group" id="pg_decline_request_to_join_group">
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">  
                    <div class="pm-dbfl pg-info-message">
					<?php
					   esc_html_e( 'You are about to approve membership request by:', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                                
                    </div>
                </div>
                
                 <div class="pmrow">  
                    <div class="pm-col">
                        <div class="pg-group-user-info-box test  pm-pad10 pm-bg pm-dbfl">
                            <div class="pg-group-user-avatar pm-difl">
							<?php echo get_avatar( $user->user_email, 26, '', false, array( 'force_display'=>true ) ); ?>
                 
                            </div>
                            <div class="pg-group-user-info  pm-dbfl">
                            <div class="pg-group-user-email pm-difl">
							<?php
							 echo esc_html( $user_name );
							?>
                            </div>
                            <div class="pm-difr">
                            <div class="pg-group-user-view-link pm-difr">
                                <a href="<?php echo esc_url( $pm_request->pm_get_user_profile_url( $user->ID ) ); ?>" target="_blank"><?php esc_html_e( 'View Profile' ); ?></a>
                            </div>
                            </div>
                            </div>
                        </div>     
                    </div>
                 </div>
            
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pg_approve_join_request('<?php echo esc_attr( $id ); ?>','<?php echo esc_attr( $gid ); ?>')"><?php esc_html_e( 'Approve', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_decline_join_request" />
             <?php $this->basic_functions->pm_render_nonce_field( 'decline_pm_join_request' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
           <input type="hidden" id="gid" name="gid" value="<?php echo esc_attr( $gid ); ?>" />
            </form>
            <?php
	}

	public function pg_join_paid_group_html( $gid, $uid ) {
		?>
            <div class="pmagic">
            <div class="pg-group-payment-form">
            <form method="post" class="pmagic-form pm-dbfl">
                <fieldset>
                <legend><?php esc_html_e( 'Payment', 'profilegrid-user-profiles-groups-and-communities' ); ?></legend>
			<?php do_action( 'profile_magic_custom_fields_html', $gid ); ?>
                <div class="all_errors" style="display:none;"></div>
                <input type="hidden" name="pg_uid" id="pg_uid" value="<?php echo esc_attr( $uid ); ?>" />
                <input type="hidden" name="pg_join_gid" id="pg_join_gid" value="<?php echo esc_attr( $gid ); ?>" />
                <input type="hidden" name="pg_join_paid_group" id="pg_join_paid_group" value="1" />
                <input type="hidden" name="action" value="process" />
                <input type="hidden" name="cmd" value="_cart" /> 
                <input type="hidden" name="invoice" value="<?php echo esc_attr( gmdate( 'His' ) . wp_rand( 1234, 9632 ) ); ?>" />
                 </fieldset>
                <div class="buttonarea pm-full-width-container">
                <input type="submit" name="pg_join_paid_group_form" id="pg_join_paid_group_form" class="pm_button" value="<?php esc_html_e( 'Join Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
                </div>
            </form>
            </div>
            </div>
            <?php
	}

	public function pg_decline_request_bulk_to_join_group_popup( $id, $gid ) {
		$pm_request = new PM_request();
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_id    = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count      = count( $id );
		$title      = __( 'Decline Requests', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
			<?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_decline_bulk_join_group_requests" id="pg_decline_bulk_join_group_requests">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					 <?php
						printf( wp_kses_post( __( 'You are going to decline <b>%d</b> group membership user requests. Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
						?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_decline_bulk_join_group_requests()"><?php esc_html_e( 'Decline', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_decline_bulk_join_group_requests" />
             <?php $this->basic_functions->pm_render_nonce_field( 'decline_pm_bulk_join_group_requests' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $post_id ); ?>" />
         </form>
            
            <?php
	}

	public function pg_accept_request_bulk_to_join_group_popup( $id, $gid ) {
		$pm_request = new PM_request();
		$path       =  plugins_url( '../public/partials/images/popup-close.png', __FILE__ );
		$post_id    = $pm_request->pm_encrypt_decrypt_pass( 'encrypt', maybe_serialize( $id ) );
		$count      = count( $id );
		$title      = __( 'Approve Requests', 'profilegrid-user-profiles-groups-and-communities' );
		?>
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                <?php echo esc_html( $title ); ?>
                  <div class="pm-popup-close pm-difr">
                      <img src="<?php echo esc_url( $path ); ?>" height="24px" width="24px">
                  </div>
            </div>
         <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" name="pg_decline_bulk_join_group_requests" id="pg_decline_bulk_join_group_requests">
            
            <div class="pm-dbfl pm-pad10 pg-group-setting-popup-wrap">  
                <div class="pmrow">        
                    <div class="pm-col">
					<?php
					   printf( wp_kses_post( __( 'You are going to approve <b>%d</b> group membership user requests. Do you wish to proceed?', 'profilegrid-user-profiles-groups-and-communities' ) ), esc_html( $count ) );
					?>
                                
                    </div>
                </div>
            </div>
        
           <div class="pg-group-setting-popup-footer pm-dbfl">
                <div class="pg-group-setting-bt pm-difl"><a onclick="pm_approve_bulk_join_group_requests()"><?php esc_html_e( 'Approve', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
                <div class="pg-group-setting-bt pg-group-setting-close-btn pm-difl"><a class="pm-remove" onclick="pg_edit_popup_close()"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div> 
            </div>
            <input type="hidden" name="action" value="pm_approve_bulk_join_group_requests" />
             <?php $this->basic_functions->pm_render_nonce_field( 'approve_pm_bulk_join_group_requests' ); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $post_id ); ?>" />
           
            
            <?php
	}


	public function pg_get_profile_sections_tab_header( $uid, $group_leader = array() ) {
        $dbhandler   = new PM_DBhandler();
		$pm_request  = new PM_request();
		$gids        = maybe_unserialize( $pm_request->profile_magic_get_user_field_value( $uid, 'pm_group' ) );
		$user_groups = $pm_request->pg_filter_users_group_ids( $gids );

		if ( !empty( $user_groups ) ) {
			foreach ( $user_groups as $group ) {
				$sections = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid'=>$group ), 'results', 0, false, 'ordering' );
				if ( !empty( $sections ) ) :
                    foreach ( $sections as $section ) :
                        $fields = $pm_request->pm_get_frontend_user_meta( $uid, $group, $group_leader, '', $section->id, '"user_avatar","user_pass","user_name","heading","paragraph","confirm_pass"' );
                        if ($fields){
                            $pm_show_about_section_group_name = $dbhandler->get_global_option_value( 'pm_show_about_section_group_name', '1' );
                            $group_name = $dbhandler->get_value( 'GROUPS', 'group_name', $group );
                            if($pm_show_about_section_group_name == 1){
                                echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#' . sanitize_key( $section->section_name ) . esc_attr($section->id) . '">' . esc_html( $section->section_name ) . '<span class="pm-section-group-name">'.esc_html($group_name).'</span></a></li>';
                            }else{
                                echo '<li class="pm-dbfl pm-border-bt pm-pad10"><a class="pm-dbfl" href="#' . sanitize_key( $section->section_name ) . esc_attr($section->id) . '">' . esc_html( $section->section_name ) . '</a></li>';
                                
                            }
                            
                        }
						
                    endforeach;
                    endif;
			}
		}
	}

	public function pm_get_user_blogs_shortcode_posts( $author, $post_type, $pagenum = 1, $limit = 10 ) {
		$dbhandler  = new PM_DBhandler();
		$pmrequests = new PM_request();
                $limit = apply_filters('pg_blogs_limit_per_page', $limit);
                $show_thumbnail = apply_filters('pg_blog_list_true_thumbnail', true);
                $show_date_comments = apply_filters('pg_blog_list_show_date_comments', true);
                $show_left_section = apply_filters('pg_blog_list_show_left_sections', true);
		$offset = ( $pagenum - 1 ) * $limit;
		$args   = array(
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		if ( !empty( $author ) ) {
			$args['author__in'] = $author;
		}
		$authors = implode( ',', $author );

		if ( is_array( $post_type ) ) {
			$posttypes = implode( ',', $post_type );
		} else {
			$posttypes = $post_type;
		}

		$total_posts = count( get_posts( $args ) );

		$args['posts_per_page'] = $limit;
		$args['offset']         = $offset;

		$num_of_pages = ceil( $total_posts/$limit );

		$pagination = $dbhandler->pm_get_pagination( $num_of_pages, $pagenum );
		if ( $pagenum<=$num_of_pages ) {

			$path =  plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );

			$query = new WP_Query( $args );

			while ( $query->have_posts() ) :
				$query->the_post();
                                $comments_count = wp_count_comments();
                                
				?>
                    <div class="pm-blog-post-wrap pm-dbfl">
                        <?php if(!empty($show_left_section)){?>
                        <div class="pm-blog-img-wrap pm-difl">
                            <?php if(!empty($show_thumbnail)){?>
                            <div class="pm-blog-img pm-difl">
				<?php
				if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( 'post-thumbnail' );
				} else {?>
                                <img src="<?php echo esc_url( $path ); ?>" alt="<?php the_title(); ?>" class="pm-user" />
                                <?php } ?>
                            </div>
                            <?php } ?>
                            <?php if(!empty($show_date_comments)){?>
                            <div class="pm-blog-status pm-difl">
                                <span class="pm-blog-time "><?php printf( esc_html_x( '%s ago', '%s = human-readable time difference', 'profilegrid-user-profiles-groups-and-communities' ), esc_html(human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) )); ?></span>
                                <span class="pm-blog-comment"><?php comments_number( esc_html__( 'no Comment', 'profilegrid-user-profiles-groups-and-communities' ), esc_html__( '1 Comment', 'profilegrid-user-profiles-groups-and-communities' ), esc_html__( '% Comments', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="pm-blog-desc-wrap pm-difl">
                            <div class="pm-blog-title">
                                <a href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span></a>
                            </div>
                            <?php do_action('pg_user_blog_below_title',get_the_ID() );?>
                            <div class="pm-blog-desc">
                             <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    wp_reset_postdata();
                endwhile;
			if ( $pagenum<$num_of_pages ) :
                ?>
                    <div class="pg-load-more-container pm-dbfl">
                        <div class="pm-loader" style="display:none;"></div>
                        <input type="hidden" id="pg_next_blog_page" value="<?php echo esc_attr( $pagenum + 1 ); ?>" />
                        <input type="submit" class="pm-load-more-blogs" onclick ="load_more_user_blogs_shortcode_posts('<?php echo esc_attr( $authors ); ?>','<?php echo esc_attr( $posttypes ); ?>')" value="<?php esc_attr_e( 'Load More', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
                    </div>
                <?php
                endif;

		} else {
			if ( !empty( $author ) ) {
				if ( count( $author )>1 ) {
					echo "<div class='pg-alert-warning pg-alert-info'> ";
					esc_html_e( 'There are no user blog posts yet.', 'profilegrid-user-profiles-groups-and-communities' );
					echo '</div>';
				} else {
					$displayname = $pmrequests->pm_get_display_name( $author[0] );
					echo "<div class='pg-alert-warning pg-alert-info'> ";
					 /* translators: %s: Display name. */
					 echo sprintf( esc_html__( 'There are no user blog posts from %s yet.', 'profilegrid-user-profiles-groups-and-communities' ), wp_kses_post($displayname) );
					echo '</div>';
				}
			} else {
				 echo "<div class='pg-alert-warning pg-alert-info'> ";
				 esc_html_e( 'There are no user blog posts yet.', 'profilegrid-user-profiles-groups-and-communities' );
				echo '</div>';
			}
		}
	}

	public function pm_get_user_chats( $receiver_uid ) {
        $pmrequests   = new PM_request();
		$current_user = wp_get_current_user();
		$pmmessenger  = new ProfileMagic_Chat();
		$return       = $pmmessenger->pm_messenger_show_threads( '' );
		?>
         
          <div class="pm-group-view">
        <div class="pm-section pm-dbfl" > 
            <svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
    <path d="M0-.5h24v24H0z" fill="none"/>
</svg>
            <div class="pm-section-left-panel pm-section-nav-vertical pm-difl " id="thread_pane">
                
                <div class="dbfl pm-new-message-area">
                    <div  class="pm-user-display-area pm-dbfl ">
                    <div class="pm-user-send-to pm-difl">To</div>
                    <div class="pm-user-send-box pm-difl">   
                        <input type="text" id="receipent_field" autocomplete="off" value="<?php
                        if ( isset( $receiver_user ) ) {
							echo esc_attr( $receiver_user['name'] );}
						?>" placeholder="Username" style="min-width: 100%;" onblur="pm_get_rid_by_uname(this.value)"/>
                    <input type="hidden" id="receipent_field_rid" name="rid" value="<?php
                    if ( isset( $receiver_user ) ) {
						echo esc_attr( $receiver_user['uid'] );}
					?>"  />   
                    </div>
                    
                    <div id="pm-autocomplete"></div>
                    <div id="pm-username-error" class="pm-dbfl"></div>
                    </div>
                </div>
                <ul class="dbfl" id="threads_ul">
				<?php echo wp_kses_post( $return ); ?>
                </ul>
            </div>

<div class="pm-section-right-panel">
            <div class="pm-blog-desc-wrap pm-difl pm-section-content pm-message-thread-section">
                <div id="pm-msg-overlay" class="pm-msg-overlay  
                <?php
                if ( ( $return=='You have no conversations yet.' )&& !isset( $receiver_user ) ) {
					echo 'pm-overlay-show1';}
				?>
                    "> </div>
                <form id="chat_message_form" onsubmit="pm_messenger_send_chat_message(event);">  
                    <div  class="pm-user-display-area pm-dbfl ">
                        
                    </div>
                
                
                <div id="message_display_area" class="pm-difl pm_full_width_profile"  style="min-height:200px;max-height:200px;max-width: 550px;overflow-y:auto;">
                    
			<?php $path =  plugins_url( '../public/partials/images/typing_image.gif', __FILE__ ); ?>
               
                </div>
                    
                <div id="typing_on"  class="pm-user-description-row pm-dbfl pm-border"><div class="pm-typing-inner"><img height="9px" width="40px" src="<?php echo esc_url( $path ); ?>"/></div></div>
             
                <div class="pm-dbfl pm-chat-messenger-box">
				<?php $this->basic_functions->pm_render_nonce_field( 'pg_send_new_message' ); ?>
                      <input type="hidden" name="action" value='pm_messenger_send_new_message' /> 
                    <input type="hidden" id="thread_hidden_field" name="tid" value=""/>
                    <div class="emoji-container">
                        <div class="pm-messenger-user-profile-pic">
                        <?php
                        $avatar =get_avatar(
                            $current_user->ID,
                            50,
                            '',
                            false,
                            array(
								'class'         => 'pm-user-profile',
								'force_display' =>true,
                            )
                        );
																   echo wp_kses_post( $avatar );
						?>
                        </div>
                    <textarea id="messenger_textarea" data-emojiable="true"  name="content" style="min-width: 100%;height:100px;"
                        
                               form="chat_message_form" placeholder="<?php esc_attr_e( 'Type your message..', 'profilegrid-user-profiles-groups-and-communities' ); ?>" ></textarea> 
                    <input type="hidden" disabled  maxlength="4" size="4" value="1000" id="counter">
                    <input type="hidden" name="sid" value="" />   
                    <div class="pm-messenger-button">
                        <label>
                          <input id="send_msg_btn" type="submit" name="send" value="<?php esc_attr_e( 'send', 'profilegrid-user-profiles-groups-and-communities' ); ?>"/>
                    <svg width="100%" height="100%" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill:#ccc">
    <g transform="matrix(1.05995e-15,17.3103,-17.3103,1.05995e-15,22248.8,-22939.9)">
        <path d="M1340,1256C1340,1256 1350.4,1279.2 1352.6,1284.1C1352.68,1284.28 1352.65,1284.49 1352.53,1284.65C1352.41,1284.81 1352.22,1284.89 1352.02,1284.86C1349.73,1284.54 1344.07,1283.75 1342.5,1283.53C1342.26,1283.5 1342.07,1283.3 1342.04,1283.06C1341.71,1280.61 1340,1268 1340,1268C1340,1268 1338.33,1280.61 1338.01,1283.06C1337.98,1283.31 1337.79,1283.5 1337.54,1283.53C1335.97,1283.75 1330.28,1284.54 1327.98,1284.86C1327.78,1284.89 1327.58,1284.81 1327.46,1284.65C1327.35,1284.49 1327.32,1284.28 1327.4,1284.1C1329.6,1279.2 1340,1256 1340,1256Z"/>
    </g>
    </svg>
                        </label>      
                    </div>
                </div>
                    </div>
            </form>
                
               

        </div>
</div>

        </div> </div> 
                
            <?php
	}



}
