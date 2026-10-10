<?php
$dbhandler  = new PM_DBhandler();
$textdomain = $this->profile_magic;
$pmrequests = new PM_request();
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';
if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_message_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	if ( !isset( $_POST['pm_enable_rm_registrations_tab'] ) ) {
		$_POST['pm_enable_rm_registrations_tab'] = 0;
    }
	if ( !isset( $_POST['pm_enable_rm_payments_tab'] ) ) {
		$_POST['pm_enable_rm_payments_tab'] = 0;
	}
	if ( !isset( $_POST['pm_enable_rm_inbox_tab'] ) ) {
		$_POST['pm_enable_rm_inbox_tab'] = 0;
	}
	if ( !isset( $_POST['pm_enable_rm_orders_tab'] ) ) {
		$_POST['pm_enable_rm_orders_tab'] = 0;
	}
	if ( !isset( $_POST['pm_enable_rm_downloads_tab'] ) ) {
		$_POST['pm_enable_rm_downloads_tab'] = 0;
	}
	if ( !isset( $_POST['pm_enable_rm_addresses_tab'] ) ) {
		$_POST['pm_enable_rm_addresses_tab'] = 0;
	}
        // titles

	if ( !isset( $_POST['pm_rm_registrations_title'] ) ) {
		$_POST['pm_rm_registrations_title'] = '';
	}
	if ( !isset( $_POST['pm_rm_payments_title'] ) ) {
		$_POST['pm_rm_payments_title'] = '';
	}
	if ( !isset( $_POST['pm_rm_inbox_title'] ) ) {
		$_POST['pm_rm_inbox_title'] = '';
	}
	if ( !isset( $_POST['pm_rm_orders_title'] ) ) {
		$_POST['pm_rm_orders_title'] = '';
	}
	if ( !isset( $_POST['pm_rm_downloads_title'] ) ) {
		$_POST['pm_rm_downloads_title'] = '';
	}
	if ( !isset( $_POST['pm_rm_addresses_title'] ) ) {
		$_POST['pm_rm_addresses_title'] = '';
	}

	$post = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_rm_settings" id="pm_rm_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php esc_html_e( 'RegistrationMagic Integration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
          
      </div>
        
       <!-----Registration Tab----->
       
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Registrations Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_rm_registrations_tab" id="pm_enable_rm_registrations" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_registrations_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_registrations_title')"/>
          <label for="pm_enable_rm_registrations"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic’s Registration tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        <div class="childfieldsrow" id="pm_rm_registrations_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_registrations_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
            <div class="uimrow">
                <div class="uimfield">
                    <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  </div>
              <div class="uiminput">
                  <input name="pm_rm_registrations_title" id="pm_rm_registrations_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_registrations_title', '' ) ); ?>"  />
               <div class="errortext"></div>
              </div>
                <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Registrations Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
            </div> 
        </div>
        
       <!-----Payment Tab----->
       
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Payment History Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_rm_payments_tab" id="pm_enable_rm_payments" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_payments_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_payments_title')"/>
          <label for="pm_enable_rm_payments"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic’s Payment History tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        <div class="childfieldsrow" id="pm_rm_payments_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_payments_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
                <div class="uimrow">
                    <div class="uimfield">
                        <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                      </div>
                  <div class="uiminput">
                      <input name="pm_rm_payments_title" id="pm_rm_payments_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_payments_title', '' ) ); ?>"  />
                   <div class="errortext"></div>
                  </div>
                    <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Payment History Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>
        
       <!-----Inbox Tab----->
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Inbox Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
            <input name="pm_enable_rm_inbox_tab" id="pm_enable_rm_inbox" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_inbox_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_inbox_title')" 
                                                                                                        <?php
																										if ( !defined( 'REGMAGIC_GOLD' ) || !defined( 'RM_ADDON_PLUGIN_VERSION' ) ) {
																											echo 'disabled="disabled"';}
																										?>
            />
          <label for="pm_enable_rm_inbox"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic Premium’s Inbox tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="childfieldsrow" id="pm_rm_inbox_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_inbox_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
                <div class="uimrow">
                    <div class="uimfield">
                        <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                      </div>
                  <div class="uiminput">
                      <input name="pm_rm_inbox_title" id="pm_rm_inbox_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_inbox_title', '' ) ); ?>"  />
                   <div class="errortext"></div>
                  </div>
                    <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Inbox Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>
      
       <!-----Orders Tab----->
       
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Orders Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_rm_orders_tab" id="pm_enable_rm_orders" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_orders_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_orders_title')" 
                                                                                                         <?php
																											if ( !class_exists( 'WooCommerce' ) || !defined( 'REGMAGIC_GOLD' ) || !defined( 'RM_ADDON_PLUGIN_VERSION' ) ) {
																												echo 'disabled="disabled"';}
																											?>
             />
          <label for="pm_enable_rm_orders"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic Premium’s Orders tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="childfieldsrow" id="pm_rm_orders_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_orders_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
                <div class="uimrow">
                    <div class="uimfield">
                        <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                      </div>
                  <div class="uiminput">
                      <input name="pm_rm_orders_title" id="pm_rm_orders_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_orders_title', '' ) ); ?>"  />
                   <div class="errortext"></div>
                  </div>
                    <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Orders Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>
       
       <!-----Downloads Tab----->
        
       <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Downloads Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_rm_downloads_tab" id="pm_enable_rm_downloads" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_downloads_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_downloads_title')" 
                                                                                                               <?php
																												if ( !class_exists( 'WooCommerce' ) || !defined( 'REGMAGIC_GOLD' ) || !defined( 'RM_ADDON_PLUGIN_VERSION' ) ) {
																													echo 'disabled="disabled"';}
																												?>
             />
          <label for="pm_enable_rm_downloads"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic Premium’s Downloads tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="childfieldsrow" id="pm_rm_downloads_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_downloads_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
                <div class="uimrow">
                    <div class="uimfield">
                        <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                      </div>
                  <div class="uiminput">
                      <input name="pm_rm_downloads_title" id="pm_rm_downloads_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_downloads_title', '' ) ); ?>"  />
                   <div class="errortext"></div>
                  </div>
                    <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Downloads Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>
        
       <!-----Addresses Tab----->
       
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Show Addresses Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_rm_addresses_tab" id="pm_enable_rm_addresses" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_rm_addresses_tab', '0' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_rm_addresses_title')" 
                                                                                                               <?php
																												if ( !class_exists( 'WooCommerce' ) || !defined( 'REGMAGIC_GOLD' ) || !defined( 'RM_ADDON_PLUGIN_VERSION' ) ) {
																													echo 'disabled="disabled"';}
																												?>
            />
          <label for="pm_enable_rm_addresses"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on to display RegistrationMagic Premium’s Addresses tab from User Area in Settings section of ProfileGrid profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
        
        <div class="childfieldsrow" id="pm_rm_addresses_title" style=" 
        <?php
        if ( $dbhandler->get_global_option_value( 'pm_enable_rm_addresses_tab', 0 )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">
                <div class="uimrow">
                    <div class="uimfield">
                        <?php esc_html_e( 'Title of the Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                      </div>
                  <div class="uiminput">
                      <input name="pm_rm_addresses_title" id="pm_rm_addresses_title" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_rm_addresses_title', '' ) ); ?>"  />
                   <div class="errortext"></div>
                  </div>
                    <div class="uimnote"><?php esc_html_e( 'Specify the label to be displayed for Addresses Tab.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                </div> 
        </div>
        
   
      <div class="buttonarea"> 
          <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_message_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
   
  </form>
</div>
