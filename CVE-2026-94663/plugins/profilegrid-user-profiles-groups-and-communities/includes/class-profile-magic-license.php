<?php
/**
 * Class for license
 */

defined( 'ABSPATH' ) || exit;

class Profile_Magic_License {
    // activate license
    public function pg_activate_license($license,$item_id,$prefix)
    {
        $dbhandler   = new PM_DBhandler();
        $return = array();
        $error_status = '';
        $pg_store_url = "https://profilegrid.co/";
        $home_url = home_url();
        // data to send in our API request
           $api_params = array(
               'edd_action' => 'activate_license',
               'license'    => $license,
               'item_id'    => $item_id,
               'url'        => $home_url
           );

           // Call the custom API.
           $response = wp_remote_post( $pg_store_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );
           
            // make sure the response came back okay
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                $error_status = $license_data->error;
                if ( false === $license_data->success ) {
                    if( isset( $license_data->error ) ){
                        switch( $license_data->error ) {
                            case 'expired' :
                                /* translators: %s: License expiry date. */
                                $message = sprintf(
                                    __( 'Your license key expired on %s.', 'profilegrid-user-profiles-groups-and-communities' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.' , 'profilegrid-user-profiles-groups-and-communities' );
                                break;
                            case 'missing' :
                                $message = __( 'Your license key is invalid.' , 'profilegrid-user-profiles-groups-and-communities' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.' , 'profilegrid-user-profiles-groups-and-communities' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __( 'The key you have entered seems to be invalid. Please verify and try again.', 'profilegrid-user-profiles-groups-and-communities'  );
                                break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.', 'profilegrid-user-profiles-groups-and-communities'  );
                                break;
                            default :
                                $message = __( 'The key you have entered seems to be invalid. Please verify and try again.', 'profilegrid-user-profiles-groups-and-communities'  );
                                break;
                        }
                    }
                }
            }

            // Check if anything passed on a message constituting a failure
            if ( ! empty( $message ) ) {
            }
            
            if( !empty( $license_data ) ){
                // $license_data->license will be either "valid" or "invalid"
                $license_status  = ( isset( $license_data->license ) && ! empty( $license_data->license ) && $license_data->license == 'valid' ) ? $license_data->license : '';
                $license_response  = ( isset( $license_data ) && ! empty( $license_data ) ) ? $license_data : '';
                $dbhandler->update_global_option_value( $prefix.'_license_status', $license_status );
                $dbhandler->update_global_option_value( $prefix.'_license_response', $license_response );
                $dbhandler->update_global_option_value( $prefix.'_item_id', $item_id );
            }
            
            if( isset( $license_data->expires ) && ! empty( $license_data->expires ) ) {
                if( $license_data->expires == 'lifetime' ){
                    $expire_date = __( 'Your license key is activated for lifetime', 'profilegrid-user-profiles-groups-and-communities' );
                }else{
                    /* translators: %s: License expiry date. */
                    $expire_date = sprintf( __( 'Your license Key expires on %s.', 'profilegrid-user-profiles-groups-and-communities' ), gmdate( 'F d, Y', strtotime($license_data->expires) ) );
                }
            }else{
                $expire_date = '';
            }   
            
            ob_start(); ?>
                <?php if( isset( $license_data->license ) && $license_data->license == 'valid' ){ ?>
                    <button type="button" class="button action pg-my-2 pg_license_deactivate" data-prefix="<?php echo esc_attr( $item_id ); ?>" name="<?php echo esc_attr( $prefix); ?>_license_deactivate" id="<?php echo esc_attr( $prefix ); ?>_license_deactivate" value="<?php esc_html_e( 'Deactivate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Deactivate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php }elseif( isset( $license_data->license ) && $license_data->license == 'invalid' ){ ?>
                    <button type="button" class="button action pg-my-2 pg_license_activate" data-prefix="<?php echo esc_attr( $item_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>_license_activate" id="<?php echo esc_attr( $prefix ); ?>_license_activate" value="<?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php }else{ ?>
                    <button type="button" class="button action pg-my-2 pg_license_activate" data-prefix="<?php echo esc_attr($item_id); ?>" name="<?php echo esc_attr( $prefix ); ?>_license_activate" id="<?php echo esc_attr( $prefix ); ?>_license_activate" value="<?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php } ?>      
            <?php
            $license_status_block = ob_get_clean();

            if ( empty( $message ) || $license_data->license == 'valid' ) {
                if( isset( $license_data->license ) && $license_data->license == 'valid' ){
                    $message = __( 'Your License key is activated.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'invalid' ){
                    $message = __( 'Your license key is invalid.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'deactivated' ){
                    $message = __( 'Your License key is deactivated.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'failed' ){
                    $message = __( 'Your License key deactivation failed. Please try after some time.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
            }

            $return = array( 'license_data' => $license_data, 'license_status_block' => $license_status_block, 'expire_date' => $expire_date, 'message' => $message );
        
            return $return;
           
    }

      // deactivate license
    public function pg_deactivate_license($license,$item_id,$prefix)
    {
        $dbhandler   = new PM_DBhandler();
        $return = array();
        $error_status = '';
        $pg_store_url = "https://profilegrid.co/";
        $home_url = home_url();
        // data to send in our API request
           $api_params = array(
               'edd_action' => 'deactivate_license',
               'license'    => $license,
               'item_id'    => $item_id,
               'url'        => $home_url
           );
        
         // Call the custom API.
            $response = wp_remote_post( $pg_store_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );
            
            // make sure the response came back okay
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                $error_status = $license_data->error;
                if ( false === $license_data->success ) {
                    if( isset( $license_data->error ) ){
                        switch( $license_data->error ) {
                            case 'expired' :
                                /* translators: %s: License expiry date. */
                                $message = sprintf(
                                    __( 'Your license key expired on %s.' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.', 'profilegrid-user-profiles-groups-and-communities'   );
                                break;
                            case 'missing' :
                                $message = __( 'Your license key is invalid.', 'profilegrid-user-profiles-groups-and-communities'   );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.', 'profilegrid-user-profiles-groups-and-communities'   );
                                break;
                            case 'item_name_mismatch' :
                                /* translators: %s: Product name. */
                                $message = sprintf( __( 'This appears to be an invalid license key for %s.', 'profilegrid-user-profiles-groups-and-communities'   ), $item_name );
                                break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.', 'profilegrid-user-profiles-groups-and-communities'   );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.', 'profilegrid-user-profiles-groups-and-communities'   );
                                break;
                        }
                    }
                }
            }

            // Check if anything passed on a message constituting a failure
            if ( ! empty( $message ) ) {

            }  
            
            if( !empty( $license_data ) ){
                // $license_data->license will be either "valid" or "invalid"
                $license_status  = ( isset( $license_data->license ) && ! empty( $license_data->license ) && $license_data->license == 'valid' ) ? $license_data->license : '';
                $license_response  = ( isset( $license_data ) && ! empty( $license_data ) ) ? $license_data : '';
                $dbhandler->update_global_option_value( $prefix.'_license_status', $license_status );
                $dbhandler->update_global_option_value( $prefix.'_license_response', $license_response );
                $dbhandler->update_global_option_value( $prefix.'_item_id', $item_id );
            }
            
            if( isset( $license_data->expires ) && ! empty( $license_data->expires ) ) {
                if( $license_data->expires == 'lifetime' ){
                    $expire_date = __( 'Your license key is activated for lifetime', 'profilegrid-user-profiles-groups-and-communities' );
                }else{
                    /* translators: %s: License expiry date. */
                    $expire_date = sprintf( __( 'Your License Key expires on %s.', 'profilegrid-user-profiles-groups-and-communities' ), gmdate('F d, Y', strtotime( $license_data->expires ) ) );
                }
            }else{
                $expire_date = '';
            }           
            
            ob_start(); ?>
                <?php if( isset( $license_data->license ) && $license_data->license == 'valid' ){ ?>
                    <button type="button" class="button action ep-my-2 pg_license_deactivate" data-prefix="<?php echo esc_attr( $item_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>_license_deactivate" id="<?php echo esc_attr( $prefix ); ?>_license_deactivate" value="<?php esc_html_e( 'Deactivate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Deactivate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php }elseif( isset( $license_data->license ) && $license_data->license == 'invalid' ){ ?>
                    <button type="button" class="button action ep-my-2 pg_license_activate" data-prefix="<?php echo esc_attr($item_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>_license_activate" id="<?php echo esc_attr($prefix); ?>_license_activate" value="<?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php }elseif( isset( $license_data->license ) && $license_data->license == 'failed' ){ ?>
                    <button type="button" class="button action ep-my-2 pg_license_activate" data-prefix="<?php echo esc_attr( $item_id); ?>" name="<?php echo esc_attr($prefix); ?>_license_activate" id="<?php echo esc_attr( $prefix); ?>_license_activate" value="<?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php }else{ ?>
                    <button type="button" class="button action ep-my-2 pg_license_activate" data-prefix="<?php echo esc_attr($item_id); ?>" name="<?php echo esc_attr($prefix); ?>_license_activate" id="<?php echo esc_attr($prefix); ?>_license_activate" value="<?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?>"><?php esc_html_e( 'Activate License', 'profilegrid-user-profiles-groups-and-communities' );?></button>
                <?php } ?>    
            <?php
            $license_status_block = ob_get_clean();

            if ( empty( $message ) || $license_data->license == 'valid' ) {
                if( isset( $license_data->license ) && $license_data->license == 'valid' ){
                    $message = __( 'Your License key is activated.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'invalid' ){
                    $message = __( 'Your license key is invalid.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'deactivated' ){
                    $message = __( 'Your License key is deactivated.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
                if( isset( $license_data->license ) && $license_data->license == 'failed' ){
                    $message = __( 'Your License key deactivation failed. Please try after some time.', 'profilegrid-user-profiles-groups-and-communities'  );
                }
            }

            $return = array( 'license_data' => $license_data, 'license_status_block' => $license_status_block, 'expire_date' => $expire_date, 'message' => $message );
          

            return $return;
          
    }

    public function pg_get_activate_extensions() {
                $ext = array(
                    /*'Profilegrid_Woocommerce'=>array(70255,'WooCommerce Integration'),*/
                    'Profilegrid_Woocommerce'=>array('',''),
                    'Profilegrid_Display_Name'=>array(70246,'User Display Name'),
                    'Profilegrid_Userid_Slug_Changer'=>array(70247,'Custom User Profile Slugs'),
                    'Profilegrid_Bbpress'=>array(70253,'bbPress Integration'),
                    'Profilegrid_EventPrime_Integration'=>array(70268,'EventPrime Integration'),
                    'Profilegrid_Menu_Integration'=>array(70274,'Login Logout Menu'),
                    'Profilegrid_Demo_Content'=>array(70288,'Demo Content'),
                    'Profilegrid_Hero_Banner'=>array(70294,'Hero Banner'),
                    'Profilegrid_User_Activities' => array(70300, 'User Activities'),
                    'Profilegrid_Recent_Signup' => array(70301, 'Recent User Signups'),
                    'Profilegrid_groups_slider' => array(70304, 'User Groups Slider'),
                    'Profilegrid_user_slider' => array(70309, 'Users Slider'),
                    'Profilegrid_featured_group' => array(70308, 'Featured Group'),
                    'Profilegrid_Zapier_Integration' => array(70313, 'Zapier Integration'),
                    'Profilegrid_Mailpoet' => array(70314, 'MailPoet Integration'),
                    'Profilegrid_elementor_content_restrictions' => array(70315, 'Elementor Content Restrictions'),
                    'Profilegrid_Our_Team' => array('', ''),
                    'Profilegrid_User_Bookmarks' => array('', ''),
                    'Profilegrid_popup_login' => array('', ''),
                    'Profilegrid_elementor_groups_widget' => array(70324, 'Elementor Integration'),
                    'Profilegrid_Group_photos' => array(70250, 'Group Photos'),
                    'Profilegrid_Group_Fields' => array(70251, 'Custom Group Properties'),
                    'Profilegrid_Geolocation' => array(70252, 'User Geolocation Maps'),
                    'Profilegrid_Front_End_Groups' => array(70249, 'Frontend Group Creator'),
                    'Profilegrid_Mailchimp' => array(70256, 'Mailchimp Integration'),
                    'Profilegrid_Social_Connect' => array(70259, 'Social Login'),
                    'Profilegrid_User_Content' => array(70260, 'Custom User Profile Tabs'),
                    'Profilegrid_Mycred' => array(70267, 'myCred Integration'),
                    'Profilegrid_Woocommerce_Wishlist' => array(70270, 'WooCommerce Wishlist Integration'),
                    'Profilegrid_Instagram_Integration' => array(70312, 'Instagram Integration'),
                    'Profilegrid_Group_Wall' => array(70245, 'Group Wall'),
                    'Profilegrid_Menu_Restriction' => array(70283, 'Menu Restrictions'),
                    'Profilegrid_Advanced_Woocommerce' => array(70265, 'WooCommerce Extensions Integration'),
                    'Profilegrid_Admin_Power' => array(70262, 'Advanced Group Manager'),
                    'Profilegrid_Group_Multi_Admins' => array(70266, 'Multiple Group Managers'),
                    'Profilegrid_Profile_Labels' => array(70272, 'User Profile Labels'),
                    'Profilegrid_Stripe_Payment' => array(70248, 'Stripe Payments'),
                    'Profilegrid_User_Profile_Status' => array(70277, 'User Profile Status'),
                    'Profilegrid_User_Photos_Extension' => array(70282, 'User Photos'),
                    'Profilegrid_Woocommerce_Product_Integration' => array(70289, 'WooCommerce Product Integration'),
                    'Profilegrid_Woocommerce_Subscription_Integration' => array(70293, 'WooCommerce Subscription Integration'),
                    'profilegrid_woocommerce_product_members_discount' => array(70297, 'WooCommerce Members Discount'),
                    'profilegrid_woocommerce_product_custom_tabs' => array(70296, 'WooCommerce Product Tabs'),
                    'Profilegrid_Active_Members_Widget' => array(70298, 'Online Users'),
                    'Profilegrid_Woocommerce_Product_Recommendations' => array(70299, 'WooCommerce Product Recommendations'),
                    'Profilegrid_User_Reviews_Extension' => array(70302, 'User Profile Reviews'),
                    'Profilegrid_Profile_Completeness' => array(70310, 'Profile Completeness'),
                    'Profilegrid_widgets_privacy' => array(70311, 'Widgets Privacy'),
                    'Profilegrid_elementor_login_logout_widget' => array(70317, 'Elementor User Login'),
                    'profilegrid_woocommerce_custom_product_price' => array('', ''),
                    'Profilegrid_Custom_Group_Slug' => array(70488, 'Customized Group Slugs'),
                    'Profilegrid_Woocommerce_Product_Restrictions' => array(70490, 'WooCommerce Customized Product Restrictions'),
                    'Profilegrid_User_Invitation_Field' => array('', ''),
                    'Profilegrid_Credit' => array(70336, 'Customized TeraWallet Integration'),
                    'Profilegrid_profile_visitor_details'  => array(77696, 'Profile Visitors'),
                    'Profilegrid_Turnstile_Antispam_Cloudflare'  => array(77691, 'Turnstile Antispam Security'),
                    'Profilegrid_F12_Integration'  => array(81211, 'Profilegrid F12 Integration')
                    
                );

		$activate = array();
                //$activate['pg_premium'] =  array(70264, 'ProfileGrid Premium');
                //$activate['pg_premium_plus'] =  array(70261, 'ProfileGrid Premium+');
		foreach ( $ext as $key=>$value ) {
			if ( class_exists( $key ) ) {
                            $activate[$key] = $value;
			}
		}

		return $activate;
	}
    
}
