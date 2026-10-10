<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
$groups     =  $dbhandler->get_all_result( 'GROUPS', '*', 1, 'results', 0, false, 'id' );

if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_elements_visibility_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( !isset( $post['pm_show_profile_cover_image'] ) ) {
			$post['pm_show_profile_cover_image'] = 0;
		}
		if ( !isset( $post['pm_show_change_profile_cover_image_option'] ) ) {
			$post['pm_show_change_profile_cover_image_option'] = 0;
		}
		if ( !isset( $post['pm_show_profile_image'] ) ) {
			$post['pm_show_profile_image'] = 0;
		}
		if ( !isset( $post['pm_show_change_profile_image_option'] ) ) {
			$post['pm_show_change_profile_image_option'] = 0;
		}
		if ( class_exists( 'Profilegrid_Profile_Labels' ) ) {
			if ( !isset( $post['pm_show_profile_tags'] ) ) {
				$post['pm_show_profile_tags'] = 0;
			}
		}
		if ( !isset( $post['pm_show_user_group_badges'] ) ) {
			$post['pm_show_user_group_badges'] = 0;
		}
		if ( !isset( $post['pm_show_user_display_name'] ) ) {
			$post['pm_show_user_display_name'] = 0;
		}
		if ( !isset( $post['pm_show_user_group_name'] ) ) {
			$post['pm_show_user_group_name'] = 0;
		}
		if ( !isset( $post['pm_show_user_edit_profile_button'] ) ) {
			$post['pm_show_user_edit_profile_button'] = 0;
		}
		if ( !isset( $post['pm_show_user_left_menu'] ) ) {
			$post['pm_show_user_left_menu'] = 0;
		}
                if ( !isset( $post['pm_show_about_section_group_name'] ) ) {
			$post['pm_show_about_section_group_name'] = 0;
		}
		if ( !isset( $post['pm_show_user_email'] ) ) {
			$post['pm_show_user_email'] = 0;
		}
		if ( !isset( $post['pm_show_user_new_blog_post_button'] ) ) {
			$post['pm_show_user_new_blog_post_button'] = 0;
		}
		if ( !isset( $post['pm_show_user_blog_post_time'] ) ) {
			$post['pm_show_user_blog_post_time'] = 0;
		}
		if ( !isset( $post['pm_show_user_blog_post_comment_count'] ) ) {
			$post['pm_show_user_blog_post_comment_count'] = 0;
		}
		if ( !isset( $post['pm_show_user_blog_post_thumbnail'] ) ) {
			$post['pm_show_user_blog_post_thumbnail'] = 0;
		}
		if ( !isset( $post['pm_show_notification_view_links'] ) ) {
			$post['pm_show_notification_view_links'] = 0;
		}
		if ( !isset( $post['pm_show_notification_title_links'] ) ) {
			$post['pm_show_notification_title_links'] = 0;
		}
		if ( !isset( $post['pm_show_user_group_title_links'] ) ) {
			$post['pm_show_user_group_title_links'] = 0;
		}
		if ( !isset( $post['pm_show_user_group_card_menu'] ) ) {
			$post['pm_show_user_group_card_menu'] = 0;
		}
		if ( !isset( $post['pm_groups_show_type_label'] ) ) {
			$post['pm_groups_show_type_label'] = 0;
		}
		if ( !isset( $post['pm_groups_show_icon'] ) ) {
			$post['pm_groups_show_icon'] = 0;
		}
		if ( !isset( $post['pm_groups_show_member_count'] ) ) {
			$post['pm_groups_show_member_count'] = 0;
		}
		if ( !isset( $post['pm_groups_show_member_limit'] ) ) {
			$post['pm_groups_show_member_limit'] = 0;
		}
		if ( !isset( $post['pm_groups_show_seats_left'] ) ) {
			$post['pm_groups_show_seats_left'] = 0;
		}
		if ( !isset( $post['pm_groups_show_paid_badge'] ) ) {
			$post['pm_groups_show_paid_badge'] = 0;
		}
		if ( !isset( $post['pm_groups_show_group_manager'] ) ) {
			$post['pm_groups_show_group_manager'] = 0;
		}
		if ( !isset( $post['pm_groups_show_action_button'] ) ) {
			$post['pm_groups_show_action_button'] = 0;
		}
		if ( !isset( $post['pm_show_group_card_menu_group_page'] ) ) {
			$post['pm_show_group_card_menu_group_page'] = 0;
		}
		if ( !isset( $post['pm_show_group_card_menu_group_wall'] ) ) {
			$post['pm_show_group_card_menu_group_wall'] = 0;
		}
		if ( !isset( $post['pm_show_group_card_menu_group_photos'] ) ) {
			$post['pm_show_group_card_menu_group_photos'] = 0;
		}
		if ( !isset( $post['pm_show_group_card_menu_leave_group'] ) ) {
			$post['pm_show_group_card_menu_leave_group'] = 0;
		}

		if ( !isset( $post['pm_show_group_card'] ) ) {
			$post['pm_show_group_card'] = 0;
		}
		if ( !isset( $post['pm_show_group_leave_group_button'] ) ) {
			$post['pm_show_group_leave_group_button'] = 0;
		}
		if ( !isset( $post['pm_show_group_managers_field'] ) ) {
			$post['pm_show_group_managers_field'] = 0;
		}
		if ( !isset( $post['pm_show_group_members_field'] ) ) {
			$post['pm_show_group_members_field'] = 0;
		}
		if ( !isset( $post['pm_show_group_details'] ) ) {
			$post['pm_show_group_details'] = 0;
		}
		if ( !isset( $post['pm_show_group_members_tab'] ) ) {
			$post['pm_show_group_members_tab'] = 0;
		}
		if ( !isset( $post['pm_show_group_discussions_tab'] ) ) {
			$post['pm_show_group_discussions_tab'] = 0;
		}
		if ( !isset( $post['pm_show_group_photos_tab'] ) ) {
			$post['pm_show_group_photos_tab'] = 0;
		}
		if ( !isset( $post['pm_show_group_settings_tab'] ) ) {
			$post['pm_show_group_settings_tab'] = 0;
		}
		if ( !isset( $post['pm_show_group_settings_subtab_group'] ) ) {
			$post['pm_show_group_settings_subtab_group'] = 0;
		}
		if ( !isset( $post['pm_show_group_settings_subtab_members'] ) ) {
			$post['pm_show_group_settings_subtab_members'] = 0;
		}
		if ( !isset( $post['pm_show_group_settings_subtab_blog'] ) ) {
			$post['pm_show_group_settings_subtab_blog'] = 0;
		}
		if ( class_exists( 'Profilegrid_Group_Multi_Admins' ) ) :
			if ( !isset( $post['pm_show_group_settings_subtab_group_manager'] ) ) {
				$post['pm_show_group_settings_subtab_group_manager'] = 0;
			}
            endif;
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$option_name = 'pm_show_group_on_groups_page_' . $group->id;
				if ( !isset( $post[ $option_name ] ) ) {
					$post[ $option_name ] = 0;
				}
			}
		}


		foreach ( $post as $key=>$value ) {
				$dbhandler->update_global_option_value( $key, $value );
		}
	}
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_elements_visibility_settings" id="pm_elements_visibility_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'Elements Visibility', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <div class="uimrow" id="pm_elements_visibilityp-page-head">
          <div class="uimfield">  <h2><?php esc_html_e( 'Profiles Page', 'profilegrid-user-profiles-groups-and-communities' ); ?></h2></div>
       <div class="pg-uim-notice"><?php esc_html_e( 'Use toggle buttons below to turn on or off labelled elements.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        <div class="uimrow" id="default_registration_form_heading">
            <h3><?php esc_html_e( 'User Profile Header Area', 'profilegrid-user-profiles-groups-and-communities' ); ?></h3>
      </div>
        
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Profile Cover Image:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_profile_cover_image" id="pm_show_profile_cover_image" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_profile_cover_image', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_profile_cover_image_html')" />
          <label for="pm_show_profile_cover_image"></label>
        </div>
         
      </div>
       <div class="childfieldsrow" id="enable_profile_cover_image_html" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_show_profile_cover_image', 1 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show option to change cover image:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_change_profile_cover_image_option" id="pm_show_change_profile_cover_image_option" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_change_profile_cover_image_option', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_change_profile_cover_image_option"></label>
        </div>
         
      </div>  
       </div>
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Profile Image:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_profile_image" id="pm_show_profile_image" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_profile_image', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_profile_image_html')" />
          <label for="pm_show_profile_image"></label>
        </div>
          
      </div>
       <div class="childfieldsrow" id="enable_profile_image_html" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_show_profile_image', 1 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show option to change Profile image:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_change_profile_image_option" id="pm_show_change_profile_image_option" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_change_profile_image_option', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_change_profile_image_option"></label>
        </div>
         
      </div>
       </div>
        <?php if ( class_exists( 'Profilegrid_Profile_Labels' ) ) : ?>
       <div class="uimrow">
        <div class="uimfield">
			<?php esc_html_e( 'Profile Tags:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_profile_tags" id="pm_show_profile_tags" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_profile_tags', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_profile_tags"></label>
        </div>
         
      </div>
        <?php endif; ?>
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Display Name:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_display_name" id="pm_show_user_display_name" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_display_name', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_display_name"></label>
        </div>
         
      </div>
        
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Group(s) Name:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_group_name" id="pm_show_user_group_name" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_group_name', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_group_name"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Group Badges:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_group_badges" id="pm_show_user_group_badges" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_group_badges', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_group_badges"></label>
        </div>
         
      </div>  
        <div class="uimrow">
            <h3><?php esc_html_e( 'User Profile Content Area', 'profilegrid-user-profiles-groups-and-communities' ); ?></h3>
      </div>
        <div class="uimrow">
            <h4><?php esc_html_e( 'About Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Edit Profile Button:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_edit_profile_button" id="pm_show_user_edit_profile_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_edit_profile_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_edit_profile_button"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Left Menu:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_left_menu" id="pm_show_user_left_menu" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_left_menu', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_left_menu"></label>
        </div>
         
      </div>
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Group Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_about_section_group_name" id="pm_show_about_section_group_name" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_about_section_group_name', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_about_section_group_name"></label>
        </div>
         
      </div>
        
<!--        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Email:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_email" id="pm_show_user_email" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_email', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_email"></label>
        </div>
         
      </div> -->
        
        <div class="uimrow">
            <h4><?php esc_html_e( 'Blog Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
      </div>
        
         
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'New Blog Post Button:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_new_blog_post_button" id="pm_show_user_new_blog_post_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_new_blog_post_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_new_blog_post_button"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Blog Post Time:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_blog_post_time" id="pm_show_user_blog_post_time" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_time', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_blog_post_time"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Blog Post Comment Count:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_blog_post_comment_count" id="pm_show_user_blog_post_comment_count" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_comment_count', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_blog_post_comment_count"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Blog Post Thumbnail:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_blog_post_thumbnail" id="pm_show_user_blog_post_thumbnail" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_blog_post_thumbnail', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_blog_post_thumbnail"></label>
        </div>
         
      </div>  
        <div class="uimrow" id="default_registration_form_heading">
            <h4><?php esc_html_e( 'Notification Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
      </div>
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show View Links:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_notification_view_links" id="pm_show_notification_view_links" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_notification_view_links', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_notification_view_links"></label>
        </div>
         
      </div>  
        
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Title Links:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_notification_title_links" id="pm_show_notification_title_links" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_notification_title_links', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_notification_title_links"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
            <h4><?php esc_html_e( 'Groups Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
      </div>
        
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Title Links to Group Pages:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_group_title_links" id="pm_show_user_group_title_links" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_group_title_links', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_user_group_title_links"></label>
        </div>
         
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Card Menu:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_user_group_card_menu" id="pm_show_user_group_card_menu" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_user_group_card_menu', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'enable_profile_group_card_menu_html')" />
          <label for="pm_show_user_group_card_menu"></label>
        </div>
         
      </div>  
        <div class="childfieldsrow" id="enable_profile_group_card_menu_html" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_show_user_group_card_menu', 1 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Group Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_card_menu_group_page" id="pm_show_group_card_menu_group_page" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_card_menu_group_page', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_card_menu_group_page"></label>
                </div>
                 
            </div>
            <?php if ( class_exists( 'Profilegrid_Group_Wall' ) && $dbhandler->get_global_option_value( 'pm_enable_wall', '0' )=='1' ) : ?>
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Group Wall:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_card_menu_group_wall" id="pm_show_group_card_menu_group_wall" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_card_menu_group_wall', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_card_menu_group_wall"></label>
                </div>
                 
            </div>
            <?php endif; ?>
            <?php if ( class_exists( 'Profilegrid_Group_photos' ) && $dbhandler->get_global_option_value( 'pm_enable_photos', '0' )=='1' ) : ?>
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Group Photos:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_card_menu_group_photos" id="pm_show_group_card_menu_group_photos" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_card_menu_group_photos', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_card_menu_group_photos"></label>
                </div>
                 
            </div>
            <?php endif; ?>
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Leave Group:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_card_menu_leave_group" id="pm_show_group_card_menu_leave_group" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_card_menu_leave_group', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_card_menu_leave_group"></label>
                </div>
                 
            </div>
            
            
        </div>  
        <div class="uimrow">
            <h2><?php esc_html_e( 'Individual Group Page (Applies to all Groups)', 'profilegrid-user-profiles-groups-and-communities' ); ?></h2>
        </div>
        
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Card:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_card" id="pm_show_group_card" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_card', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'enable_group_card_html')" />
          <label for="pm_show_group_card"></label>
        </div>
         
      </div>  
        <div class="childfieldsrow" id="enable_group_card_html" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_show_group_card', 1 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Leave Group Button:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_leave_group_button" id="pm_show_group_leave_group_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_leave_group_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_leave_group_button"></label>
        </div>
         
      </div>  
            
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Managers Field:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_managers_field" id="pm_show_group_managers_field" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_managers_field', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_managers_field"></label>
        </div>
         
      </div>
            
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Members Field:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_members_field" id="pm_show_group_members_field" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_members_field', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_members_field"></label>
        </div>
         
      </div>  
            
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Details:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_details" id="pm_show_group_details" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_details', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_details"></label>
        </div>
         
      </div>
        </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Members', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_members_tab" id="pm_show_group_members_tab" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_members_tab', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_members_tab"></label>
        </div>
         
      </div>
         <?php if ( class_exists( 'Profilegrid_Group_Wall' ) && $dbhandler->get_global_option_value( 'pm_enable_wall', '0' )=='1' ) : ?>
         <div class="uimrow">
        <div class="uimfield">
				<?php esc_html_e( 'Discussions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_discussions_tab" id="pm_show_group_discussions_tab" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_discussions_tab', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_discussions_tab"></label>
        </div>
         
      </div>
      <?php endif; ?>
         <?php if ( class_exists( 'Profilegrid_Group_photos' ) && $dbhandler->get_global_option_value( 'pm_enable_photos', '0' )=='1' ) : ?>
         <div class="uimrow">
        <div class="uimfield">
				<?php esc_html_e( 'Photos', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_photos_tab" id="pm_show_group_photos_tab" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_photos_tab', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_group_photos_tab"></label>
        </div>
         
      </div>
      <?php endif; ?>
         <?php if ( class_exists( 'Profilegrid_Admin_Power' ) ) : ?>
        <div class="uimrow">
        <div class="uimfield">
				<?php esc_html_e( 'Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_show_group_settings_tab" id="pm_show_group_settings_tab" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_settings_tab', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'enable_group_settings_tab_html')" />
          <label for="pm_show_group_settings_tab"></label>
        </div>
         
      </div>
        
        <div class="childfieldsrow" id="enable_group_settings_tab_html" style=" 
				<?php
				if ( $dbhandler->get_global_option_value( 'pm_show_group_settings_tab', 1 )==1 ) {
					echo 'display:block;';
				} else {
					echo 'display:none;';}
				?>
        ">
        
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_settings_subtab_group" id="pm_show_group_settings_subtab_group" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_settings_subtab_group', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_settings_subtab_group"></label>
                </div>
                 
            </div>
            
            <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Members', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_settings_subtab_members" id="pm_show_group_settings_subtab_members" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_settings_subtab_members', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_settings_subtab_members"></label>
                </div>
                 
            </div>
            
             <div class="uimrow">
                <div class="uimfield">
                  <?php esc_html_e( 'Blog', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_settings_subtab_blog" id="pm_show_group_settings_subtab_blog" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_settings_subtab_blog', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_settings_subtab_blog"></label>
                </div>
                 
            </div>
				<?php if ( class_exists( 'Profilegrid_Group_Multi_Admins' ) ) : ?>
            <div class="uimrow">
                <div class="uimfield">
					<?php esc_html_e( 'Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_show_group_settings_subtab_group_manager" id="pm_show_group_settings_subtab_group_manager" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_group_settings_subtab_group_manager', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                  <label for="pm_show_group_settings_subtab_group_manager"></label>
                </div>
                 
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="uimrow">
            <div class="uimfield"><h2><?php esc_html_e( 'All Groups Page', 'profilegrid-user-profiles-groups-and-communities' ); ?></h2></div>
      <div class="pg-uim-notice"><?php esc_html_e( 'Choose which elements and groups to display on the All Groups page. Shortcode parameters override block settings, which override these global settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
     </div>
     <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Group Type Label', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_type_label" id="pm_groups_show_type_label" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_type_label', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_type_label"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show open/closed label on group cards.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Group Icon', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_icon" id="pm_groups_show_icon" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_icon', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_icon"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show group image/icon on cards.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Member Count', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_member_count" id="pm_groups_show_member_count" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_member_count', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_member_count"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show total number of members in the group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Member Limit', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_member_limit" id="pm_groups_show_member_limit" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_member_limit', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_member_limit"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show member limit when enabled for a group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Seats Left', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_seats_left" id="pm_groups_show_seats_left" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_seats_left', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_seats_left"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show urgency label when seats are limited.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Paid Badge', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_paid_badge" id="pm_groups_show_paid_badge" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_paid_badge', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_paid_badge"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Display PAID badge for groups with membership fee.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Group Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_group_manager" id="pm_groups_show_group_manager" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_group_manager', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_group_manager"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Display group manager’s name (if assigned).', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Join/Details Button', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_groups_show_action_button" id="pm_groups_show_action_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_groups_show_action_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_groups_show_action_button"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Show CTA button linking to group page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
<div class="pg-admin-element-group-wrap">
        <?php
        if ( !empty( $groups ) ) {
            ?>
            <div class="pg-admin-element-group-row">
                <?php
				$col = 0;
				foreach ( $groups as $group ) {
					?>

					<?php
					if ( $col && !( $col%3 ) ) {
						echo '</div><div class="pg-admin-element-group-row">';}
					$option_name = 'pm_show_group_on_groups_page_' . $group->id;
					?>
                <div class="pg-element-group-col">
                    <div class="col-uimfield" title="<?php echo esc_attr( $group->group_name ); ?>">
					<?php echo esc_html( $group->group_name ); ?>
                </div>
                <div class="col-uiminput">
                    <input name="pm_show_group_on_groups_page_<?php echo esc_attr( $group->id ); ?>" id="pm_show_group_on_groups_page_<?php echo esc_attr( $group->id ); ?>" type="checkbox" <?php checked( $dbhandler->get_global_option_value( $option_name, '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
                    <label for="pm_show_group_on_groups_page_<?php echo esc_attr( $group->id ); ?>"></label>
                </div>
                 
            </div>
					<?php
					$col++;
				}
                ?>
            </div>
                <?php
        }
        ?>
</div>
      <div class="buttonarea"> 
          <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_elements_visibility_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
