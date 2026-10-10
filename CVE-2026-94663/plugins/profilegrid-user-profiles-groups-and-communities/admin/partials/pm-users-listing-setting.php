<?php
$textdomain = $this->profile_magic;
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_users_lisitng_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings', 'pm_field_list' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( !isset( $post['pm_show_search_bar'] ) ) {
			$post['pm_show_search_bar'] = 0;
        }
		if ( !isset( $post['pm_show_users_count'] ) ) {
			$post['pm_show_users_count'] =0;
		}
		if ( !isset( $post['pm_show_advance_search_button'] ) ) {
			$post['pm_show_advance_search_button'] = 0;
        }
		if ( !isset( $post['pm_show_search_reset_button'] ) ) {
			$post['pm_show_search_reset_button'] = 0;
        }
		if ( !isset( $post['pm_show_search_sortby'] ) ) {
			$post['pm_show_search_sortby'] = 0;
        }

		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
$pm_default_user_sorting = $dbhandler->get_global_option_value( 'pm_default_user_sorting', 'oldest_first' );
$pm_default_search_field = $dbhandler->get_global_option_value( 'pm_default_search_field', 'first_name' );
?>

<div class="uimagic">
  <form name="pm_users_listing_settings" id="pm_user_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'All Users Listing', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">

      </div>
        
        
        
         <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Users on Each Page', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_number_of_users_on_search_page" id="pm_number_of_users_on_search_page" class="pg-number-of-users-group" type="number" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_number_of_users_on_search_page', '20' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define maximum number of users to display on a single user directory listing page, before pagination begins.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'User Image Size', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_profile_image_size_on_search_page" id="pm_profile_image_size_on_search_page" class="pg-profile-image-size" min="100" type="number" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_profile_image_size_on_search_page', '100' ) ); ?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define user image thumbnail size (in pixels) on user directory page. This options is used to increase or decrease user card size, and consequently the number of users in each row.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Display User Count', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_show_users_count" id="pm_show_users_count" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_users_count', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;"/>
          <label for="pm_show_users_count"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Enable to display count of all the users within the user directory.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Display Search and Filters', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_show_search_bar" id="pm_show_search_bar" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_search_bar', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'searchhtml')"/>
          <label for="pm_show_search_bar"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Enable to allow the visitors to search users inside the user directory.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div> 
    
      <div class="childfieldsrow" id="searchhtml" style=" 
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_show_search_bar', '1' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">

          <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default Search Field', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_search_field" id="pm_default_search_field">
            <option value="first_name" <?php selected( 'first_name', $pm_default_search_field ); ?>><?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="last_name"  <?php selected( 'last_name', $pm_default_search_field ); ?>><?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="both"  <?php selected( 'both', $pm_default_search_field ); ?>><?php esc_html_e( 'First Name or Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="default"  <?php selected( 'default', $pm_default_search_field ); ?>><?php esc_html_e( 'WordPress Default', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Use data from this profile field for searches when the visitor has not manually selected field(s) for performing the search.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( "Display 'More Filters' Option", 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <input name="pm_show_advance_search_button" id="pm_show_advance_search_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_advance_search_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_advance_search_button"></label>
          
        </div>
        <div class="uimnote"><?php esc_html_e( "Display 'More Filters' under search box on user directory pages allowing visitors to reveal extra filtering options based on user profile fields.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
          
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Display Reset Link', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <input name="pm_show_search_reset_button" id="pm_show_search_reset_button" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_search_reset_button', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_show_search_reset_button"></label>
          
        </div>
        <div class="uimnote"><?php esc_html_e( 'Reset Link allows the visitors to clear search parameters and restart their searches.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
     
          
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Allow Sorting', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
        <input name="pm_show_search_sortby" id="pm_show_search_sortby" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_show_search_sortby', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'sortbyhtml')" />
          <label for="pm_show_search_sortby"></label>
          
        </div>
        <div class="uimnote"><?php esc_html_e( 'Allow the visitors to sort directory listing and search results using a dropdown.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
       <div class="childfieldsrow" id="sortbyhtml" style=" 
       <?php
		if ( $dbhandler->get_global_option_value( 'pm_show_search_sortby', '1' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
      
          
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Default Sorting', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <select name="pm_default_user_sorting" id="pm_default_user_sorting">
            <option value="oldest_first" <?php selected( 'oldest_first', $pm_default_user_sorting ); ?>><?php esc_html_e( 'Oldest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="latest_first"  <?php selected( 'latest_first', $pm_default_user_sorting ); ?>><?php esc_html_e( 'Newest', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="first_name_asc"  <?php selected( 'first_name_asc', $pm_default_user_sorting ); ?>><?php esc_html_e( 'First Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="first_name_desc"  <?php selected( 'first_name_desc', $pm_default_user_sorting ); ?>><?php esc_html_e( 'First Name Alphabetically Z - A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="last_name_asc"  <?php selected( 'last_name_asc', $pm_default_user_sorting ); ?>><?php esc_html_e( 'Last Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
            <option value="last_name_desc"  <?php selected( 'last_name_desc', $pm_default_user_sorting ); ?>><?php esc_html_e( 'Last Name Alphabetically Z- A', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Set the default order of users when the visitor first opens the user directory page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
           
      </div>   
          
     
      
      </div>
      
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_users_lisitng_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
