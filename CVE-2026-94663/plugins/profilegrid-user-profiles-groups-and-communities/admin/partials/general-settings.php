<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_general_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( !isset( $post['pm_allow_multiple_attachments'] ) ) {
			$post['pm_allow_multiple_attachments'] = 0;
        }
		if ( !isset( $post['pm_auto_redirect_author_to_profile'] ) ) {
			$post['pm_auto_redirect_author_to_profile'] =0;
		}
		if ( !isset( $post['pm_enable_gravatars'] ) ) {
			$post['pm_enable_gravatars'] =0;
        }
		if ( !isset( $post['pm_show_register_link'] ) ) {
			$post['pm_show_register_link'] =0;
		}
		if ( !isset( $post['pm_guest_allow_backend_login_screen'] ) ) {
			$post['pm_guest_allow_backend_login_screen'] =0;
        }
		if ( !isset( $post['pm_guest_allow_backend_register_screen'] ) ) {
			$post['pm_guest_allow_backend_register_screen'] =0;
		}
		if ( !isset( $post['pm_hide_wp_toolbar'] ) ) {
			$post['pm_hide_wp_toolbar'] ='no';
		}
		if ( !isset( $post['pm_hide_admin_toolbar'] ) ) {
			$post['pm_hide_admin_toolbar'] ='no';
		}
		if ( !isset( $post['pm_enable_reset_password_limit'] ) ) {
			$post['pm_enable_reset_password_limit'] =0;
		}
		if ( !isset( $post['pm_hide_wp_toolbar'] ) ) {
			$post['pm_hide_wp_toolbar'] ='no';
		}
		if ( !isset( $post['pm_hide_admin_toolbar'] ) ) {
			$post['pm_hide_admin_toolbar'] ='no';
		}
		if ( !isset( $post['pm_disabled_admin_reset_password_limit'] ) ) {
			$post['pm_disabled_admin_reset_password_limit'] =0;
		}
		if ( !isset( $post['pm_save_ip_browser_info'] ) ) {
			$post['pm_save_ip_browser_info'] = 0;
		}
                if ( !isset( $post['pm_group_update_require_admin_approval'] ) ) {
			$post['pm_group_update_require_admin_approval'] = 0;
		}

		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}

$pm_default_group_sorting       = $dbhandler->get_global_option_value( 'pm_default_group_sorting', 'oldest_first' );
$pm_default_groups_sorting      = $dbhandler->get_global_option_value( 'pm_default_groups_sorting', 'newest' );
$pm_default_groups_view         = $dbhandler->get_global_option_value( 'pm_default_groups_view', 'grid' );
$pm_default_groups_grid_columns = $dbhandler->get_global_option_value( 'pm_default_groups_grid_columns', '3' );

?>

<div class="uimagic">
    <form name="pm_general_settings" id="pm_general_settings" method="post" onsubmit="return add_section_validation()">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'General', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Theme', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_theme_type" id="pm_theme_type">
            <option value="light" <?php selected( $dbhandler->get_global_option_value( 'pm_theme_type', 'light' ), 'light' ); ?>><?php esc_html_e( 'Light', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="dark" <?php selected( $dbhandler->get_global_option_value( 'pm_theme_type', 'light' ), 'dark' ); ?>><?php esc_html_e( 'Dark', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
        </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'While Light will fit most of the themes, choose Dark if your WordPress theme has black or dark background.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default Sorting on All Groups Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_groups_sorting" id="pm_default_groups_sorting">
                <option value="newest" <?php selected( 'newest', $pm_default_groups_sorting ); ?>><?php esc_html_e( 'Newest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                <option value="oldest" <?php selected( 'oldest', $pm_default_groups_sorting ); ?>><?php esc_html_e( 'Oldest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                <option value="name_asc" <?php selected( 'name_asc', $pm_default_groups_sorting ); ?>><?php esc_html_e( 'Alphabetical (A-Z)', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                <option value="name_desc" <?php selected( 'name_desc', $pm_default_groups_sorting ); ?>><?php esc_html_e( 'Alphabetical (Z-A)', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
           </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Choose sorting of group cards when users visit All Groups page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>

        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default View on All Groups Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_groups_view" id="pm_default_groups_view">
                <option value="grid" <?php selected( 'grid', $pm_default_groups_view ); ?>><?php esc_html_e( 'Grid', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                <option value="list" <?php selected( 'list', $pm_default_groups_view ); ?>><?php esc_html_e( 'List', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
           </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Choose the default layout for the All Groups page. Shortcode parameters override block settings, which override these global settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>

        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Grid Columns on All Groups Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_groups_grid_columns" id="pm_default_groups_grid_columns">
                <option value="1" <?php selected( '1', $pm_default_groups_grid_columns ); ?>>1</option>
                <option value="2" <?php selected( '2', $pm_default_groups_grid_columns ); ?>>2</option>
                <option value="3" <?php selected( '3', $pm_default_groups_grid_columns ); ?>>3</option>
                <option value="4" <?php selected( '4', $pm_default_groups_grid_columns ); ?>>4</option>
           </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Sets the maximum number of columns for grid view (1–4). Layout is responsive and may reduce columns on smaller screens.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>

        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default Sorting on Group Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_group_sorting" id="pm_default_group_sorting">
            <option value="oldest_first" <?php selected( 'oldest_first', $pm_default_group_sorting ); ?>><?php esc_html_e( 'Oldest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="latest_first"  <?php selected( 'latest_first', $pm_default_group_sorting ); ?>><?php esc_html_e( 'Newest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="first_name_asc"  <?php selected( 'first_name_asc', $pm_default_group_sorting ); ?>><?php esc_html_e( 'First Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="first_name_desc"  <?php selected( 'first_name_desc', $pm_default_group_sorting ); ?>><?php esc_html_e( 'First Name Alphabetically Z - A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="last_name_asc"  <?php selected( 'last_name_asc', $pm_default_group_sorting ); ?>><?php esc_html_e( 'Last Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="last_name_desc"  <?php selected( 'last_name_desc', $pm_default_group_sorting ); ?>><?php esc_html_e( 'Last Name Alphabetically Z- A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Choose sorting of user cards on individual group pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
       
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'No. of Groups', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_default_no_of_groups" id="pm_default_no_of_groups" class="pg-number-of-users-group"  type="number" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_default_no_of_groups', '10' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define maximum number of group cards to display on All Groups page before pagination begins.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'No. of Users on Group Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_number_of_users_on_group_page" id="pm_number_of_users_on_group_page" class="pg-number-of-users-group"  type="number" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_number_of_users_on_group_page', '10' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define maximum number of user cards to display on individual group pages before pagination begins.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
     
       
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Automatically Redirect Author Page to their Profile?', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_auto_redirect_author_to_profile" id="pm_auto_redirect_author_to_profile" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_auto_redirect_author_to_profile' ), '1' ); ?>   />
          <label for="pm_auto_redirect_author_to_profile"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( "When visitors accesses author page, they will be redirected to author's profile page.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Enable Gravatar', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_enable_gravatars" id="pm_enable_gravatars" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_gravatars' ), '1' ); ?>   />
          <label for="pm_enable_gravatars"></label>
        </div>
            <div class="uimnote"><?php printf( wp_kses_post( 'When enabled, if user has not uploaded a profile image, ProfileGrid will fetch profile image associated with user email on <a href="%s" target="_blank">Gravatar</a>, a WordPress service for uploading and managing user avatars.', 'profilegrid-user-profiles-groups-and-communities' ), 'https://gravatar.com' ); ?></div>
      </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Register Link on Login Form', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_show_register_link" id="pm_show_register_link" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_show_register_link' ), '1' ); ?>   />
          <label for="pm_show_register_link"></label>
        </div>
          <div class="uimnote"><?php printf( esc_html__( 'When enabled, Register link appear on Login Form.', 'profilegrid-user-profiles-groups-and-communities' ), 'https://gravatar.com' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Allow Dashboard Login Page Access to Guests', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_guest_allow_backend_login_screen" id="pm_guest_allow_backend_login_screen" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_guest_allow_backend_login_screen', '1' ), '1' ); ?>   />
          <label for="pm_guest_allow_backend_login_screen"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( "Users will be allowed to login using WordPress' default dashboard login page. When turned off, guests will be redirected to ProfileGrid login page when trying to access dashboard login page directly.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
        <div class="uimrow">
          <div class="uimfield">
            <?php esc_html_e( 'Allow Dashboard Register Page access to Guests', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput">
            <input name="pm_guest_allow_backend_register_screen" id="pm_guest_allow_backend_register_screen" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_guest_allow_backend_register_screen', '1' ), '1' ); ?>   />
            <label for="pm_guest_allow_backend_register_screen"></label>
          </div>
            <div class="uimnote"><?php esc_html_e( "Users will be allowed to register using WordPress' default dashboard registration page. When turned off, guests will be redirected to ProfileGrid's default registration page when trying to access dashboard register page directly.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        

      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Hide WordPress Toolbar', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_hide_wp_toolbar" id="pm_hide_wp_toolbar" type="checkbox" class="pm_toggle" value="yes" style="display:none;"  onclick="pm_show_hide(this,'pm_hide_admin_toolbar_html')" <?php checked( $dbhandler->get_global_option_value( 'pm_hide_wp_toolbar' ), 'yes' ); ?>   />
          <label for="pm_hide_wp_toolbar"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Hides the top WordPress admin bar for logged in users.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>  
        <div class="childfieldsrow" id="pm_hide_admin_toolbar_html" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_hide_wp_toolbar', 'no' )== 'yes' ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
                                                                            ">
                <div class="uimrow">
                  <div class="uimfield">
                    <?php esc_html_e( 'Keep it visible for admin', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  </div>
                <div class="uiminput">
                       <input name="pm_hide_admin_toolbar" id="pm_hide_admin_toolbar" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_hide_admin_toolbar' ), 'yes' ); ?> class="pm_toggle" value="yes" style="display:none;" />
                      <label for="pm_hide_admin_toolbar"></label>
                </div>
                <div class="uimnote"><?php esc_html_e( 'Show WordPress admin bar only to admin users', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>

      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Set Limit for Password Reset Tries', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_enable_reset_password_limit" id="pm_enable_reset_password_limit" type="checkbox" class="pm_toggle" value="1" style="display:none;" onclick="pm_show_hide(this,'pm_reset_password_limt_html')" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_reset_password_limit' ), '1' ); ?>   />
          <label for="pm_enable_reset_password_limit"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Define number of times user can try to reset password.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>  
        
    <div class="childfieldsrow" id="pm_reset_password_limt_html" style=" 
    <?php
    if ( $dbhandler->get_global_option_value( 'pm_enable_reset_password_limit', 0 )==1 ) {
		echo 'display:block;';
	} else {
		echo 'display:none;';}
	?>
                                                                         ">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Number of Allowed Tries', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_reset_password_limit', 0 )==1 ) {
			echo 'pm_required';}
		?>
        ">
            <input name="pm_reset_password_limit" id="pm_reset_password_limit" type="number" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_reset_password_limit', '' ) ); ?>"  />
         <div class="errortext"></div>
        </div>
          <div class="uimnote"><?php esc_html_e( 'User will not be allowed to reset password more than this defined number. If exceeded, user will see an error next time he/ she tries to reset the password.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Disable Password Reset Rule for Admins', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_disabled_admin_reset_password_limit" id="pm_disabled_admin_reset_password_limit" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_disabled_admin_reset_password_limit' ), '1' ); ?>   />
          <label for="pm_disabled_admin_reset_password_limit"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'The password reset limit rule will not apply to the admin.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>  

    </div>  
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Allow Multiple Attachments', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_allow_multiple_attachments" id="pm_allow_multiple_attachments" type="checkbox" class="pm_toggle" value="1" style="display:none;" <?php checked( $dbhandler->get_global_option_value( 'pm_allow_multiple_attachments' ), '1' ); ?>   />
          <label for="pm_allow_multiple_attachments"></label>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Allow users to attach more than one file to file upload fields.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        <?php if ( class_exists( 'Registration_Magic' ) ) : ?>
        <div class="uimrow">
        <div class="uimfield">
			<?php esc_html_e( 'Use Login Form from', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <select name="pm_login_form_from" id="pm_login_form_from">
                <option value="pg" <?php selected( 'pg', $dbhandler->get_global_option_value( 'pm_login_form_from', 'rm' ) ); ?>><?php echo esc_html( 'ProfileGrid' ); ?></option>
                <option value="rm" <?php selected( 'rm', $dbhandler->get_global_option_value( 'pm_login_form_from', 'rm' ) ); ?>><?php echo esc_html( 'Registration Magic' ); ?></option>
            </select>
        </div>
          <div class="uimnote"><?php esc_html_e( 'Both RegistrationMagic and ProfileGrid offer their own login forms. RegistrationMagic login form has many advanced features and analytics and is therefore recommended in most cases.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      <?php endif; ?>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default WP Registration Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$default_registration_url = $dbhandler->get_global_option_value( 'pm_default_regisration_page', '0' );
			wp_dropdown_pages( array(
				'depth'             => 0,
				'child_of'          => 0,
				'selected'          => esc_attr($default_registration_url),
				'echo'              => 1,
				'show_option_none'  => esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
    			'option_none_value' => 0,
				'name'              => 'pm_default_regisration_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Redirect all registration links to this page on your site. This helps in hiding the default WP registration form.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'After Login Redirect User to:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$pm_redirect_after_login = $dbhandler->get_global_option_value( 'pm_redirect_after_login', '0' );
			wp_dropdown_pages( array(
				'depth'             => 0,
				'child_of'          => 0,
				'selected'          => esc_attr($pm_redirect_after_login),
				'echo'              => 1,
				'show_option_none'  => esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
    			'option_none_value' => 0,
				'name'              => 'pm_redirect_after_login',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'When the user logs in, he/ she will be redirected to this page. This is usually a member specific area, like user profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'After Logout Redirect User to:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$pm_redirect_after_logout = $dbhandler->get_global_option_value( 'pm_redirect_after_logout', '0' );
			wp_dropdown_pages( array(
				'depth'             => 0,
				'child_of'          => 0,
				'selected'          => esc_attr($pm_redirect_after_logout),
				'echo'              => 1,
				'show_option_none'  => esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
    			'option_none_value' => 0,
				'name'              => 'pm_redirect_after_logout',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'When the user logout, he/ she will be redirected to this page. This is usually a member specific area, like user profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>  
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'All Groups Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$pm_groups_page = $dbhandler->get_global_option_value( 'pm_groups_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_groups_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_groups_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'All Groups page displays all the groups on your site beautifully on a single page. A great way to allow visitors to decide and sign up for relevant group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Registration Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$pm_registration_page = $dbhandler->get_global_option_value( 'pm_registration_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_registration_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_registration_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'This will add Username and Password fields to this form.', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Profile Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
		$pm_user_profile_page = $dbhandler->get_global_option_value( 'pm_user_profile_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_user_profile_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_user_profile_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Default member profile page. Make sure it has profile shortcode pasted inside it.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Login Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
	   		$pm_user_login_page = $dbhandler->get_global_option_value( 'pm_user_login_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_user_login_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_user_login_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'The page where users can log in. It should have the login shortcode pasted inside.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Password Recovery Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
	   		$pm_forget_password_page = $dbhandler->get_global_option_value( 'pm_forget_password_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_forget_password_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_forget_password_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'This page will allows users to start password recovery process.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
     
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Group Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <?php
	   		$pm_group_page = $dbhandler->get_global_option_value( 'pm_group_page', '0' );
			wp_dropdown_pages( array(
				'depth'             =>0,
				'child_of'          =>0,
				'selected'          => esc_attr($pm_group_page),
				'echo'              =>1,
				'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
				'option_none_value' =>0,
				'name'              =>'pm_group_page',
			) );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Single group page where users can see group details and other members of the group.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>

        <div class="uimrow">
          <div class="uimfield">
            <?php esc_html_e( 'User Blog Submission Page:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          </div>
          <div class="uiminput">
            <?php
                            $pm_submit_blog = $dbhandler->get_global_option_value( 'pm_submit_blog', '0' );
                            wp_dropdown_pages( array(
                'depth'             =>0,
                'child_of'          =>0,
                'selected'          => esc_attr($pm_submit_blog),
                'echo'              =>1,
                'show_option_none'  =>esc_attr__( 'Select Page', 'profilegrid-user-profiles-groups-and-communities' ),
                'option_none_value' =>0,
                'name'              =>'pm_submit_blog',
              ) );
              ?>
            <div class="errortext"></div>
          </div>
          <div class="uimnote"><?php esc_html_e( 'Page from where users can submit new blog posts, which will then appear in Blogs tab of their profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        
        <div class="uimrow">
            <div class="uimfield">
              <?php esc_html_e( 'Date Format:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="uiminput">
                <?php  $pm_date_format = $dbhandler->get_global_option_value( 'pm_date_format', '0' ); ?>
                <select name="pm_date_format" id="pm_date_format">
                    <option value="Y-m-d" <?php selected( 'Y-m-d', $pm_date_format ); ?>><?php echo esc_html(gmdate('Y-m-d').'( Y-m-d )'); ?></option>
                    <option value="d-m-Y"  <?php selected( 'd-m-Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('d-m-Y').'( d-m-Y )'); ?></option>
                    <option value="m-d-Y"  <?php selected( 'm-d-Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('m-d-Y').'( m-d-Y )'); ?></option>
                    
                    <option value="Y/m/d"  <?php selected( 'Y/m/d', $pm_date_format ); ?>><?php echo esc_html(gmdate('Y/m/d').'( Y/m/d )'); ?></option>
                    <option value="d/m/Y"  <?php selected( 'd/m/Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('d/m/Y').'( d/m/Y )'); ?></option>
                    <option value="m/d/Y"  <?php selected( 'm/d/Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('m/d/Y').'( m/d/Y )'); ?></option>
                    
                    <option value="Y.m.d"  <?php selected( 'Y.m.d', $pm_date_format ); ?>><?php echo esc_html(gmdate('Y.m.d').'( Y.m.d )'); ?></option>
                    <option value="d.m.Y"  <?php selected( 'd.m.Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('d.m.Y').'( d.m.Y )'); ?></option>
                    <option value="m.d.Y"  <?php selected( 'm.d.Y', $pm_date_format ); ?>><?php echo esc_html(gmdate('m.d.Y').'( m.d.Y )'); ?></option>
                </select>
                <div class="errortext"></div>
            </div>
            <div class="uimnote"><?php esc_html_e( 'Select desire date format for the date field in the forms.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
     <?php do_action('pm_general_setting_option_html'); ?>
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_general_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings"/>
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
