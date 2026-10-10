<?php

class Profile_Magic_Basic_Functions {


    private $profile_magic;

    private $version;


    public function __construct( $profile_magic, $version ) {
         $this->profile_magic = $profile_magic;
        $this->version        = $version;
    }

    public function null_field_notice() {
		esc_html_e( 'Some of the options below require selecting fields from your form. Since you are creating new form from scratch, there are no fields in this form yet. You can come back later and modify these field specific options. You can safely ignore them for now and save the settings.', 'profilegrid-user-profiles-groups-and-communities' );
    }

    public function get_all_users_for_combo_box( $arg = array() ) {
         $wp_users = get_users( $arg );
        // Array of WP_User objects.
        if ( !empty( $wp_users ) ) {
			foreach ( $wp_users as $user ) {
                $pm_users[] =  '"' . esc_html( $user->user_login ) . '"';
			}
			$all_users = implode( ',', $pm_users );
        } else {
            return false;
        }
        return $all_users;
    }

    public function get_error_frontend_message() {
         $error                          = array();
		$error['pass_length']            = __( 'Your password should be at least 7 characters long.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['confirm_pass_not_match'] = __( 'Password and confirm password do not match.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['email_not_valid']        = __( 'Please enter a valid e-mail address.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['number_not_valid']       = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['date_not_valid']         = __( 'Please enter a valid date (yyyy-mm-dd format)', 'profilegrid-user-profiles-groups-and-communities' );
		$error['required_field']         = __( 'This is a required field', 'profilegrid-user-profiles-groups-and-communities' );
		$error['file_type_not_valid']    = __( 'This file type is not allowed.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['number_not_valid']       = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['number_not_valid']       = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['number_not_valid']       = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );
		$error['number_not_valid']       = __( 'Please enter a valid number.', 'profilegrid-user-profiles-groups-and-communities' );

		return $error;
    }

    public function get_pg_extension_activate_url( $path ) {
         $plugin = $path;
        if ( strpos( $path, '/' ) ) {
            $path = str_replace( '/', '%2F', $path );
        }
        $activateUrl = sprintf( admin_url( 'plugins.php?action=activate&plugin=%s' ), $path );
        $activateUrl = wp_nonce_url( $activateUrl, 'activate-plugin_' . $plugin );
        return $activateUrl;
    }

    public function pg_extension_install_button( $plugin ) {
         $data                          = new stdClass();
        $data->pg_extsion_install_url   = $this->pg_get_extension_install_url( $plugin );
        $data->pg_extsion_activate_url  = '';
        $data->is_pg_extension_activate = $this->pg_is_extension_activated( $plugin );
        $data->pg_extsion_page_url      = '';
        if ( $data->is_pg_extension_activate ) {
            $data->is_pg_extension_installed = true;
        } else {
            $plugins                         = get_plugins();
            $path                            = $this->pg_extention_plugin_path( $plugin );
            $data->is_pg_extension_installed = array_key_exists( $path, $plugins );
            if ( $data->is_pg_extension_installed ) {
                $data->pg_extsion_activate_url = $this->get_pg_extension_activate_url( $path );
            } else {
                $data->pg_get_extension_install_url = $this->pg_get_extension_install_url( $plugin );
            }
        }
        return $data;
    }

    public function pg_extention_plugin_path( $plugin ) {
		switch ( $plugin ) {
            case 'STRIPE':
                $path = 'profilegrid-user-profiles-groups-and-communities-stripe-payment-gateway/profilegrid-stripe-payment.php';
                break;
            case 'GROUPWALL':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-group-wall/profilegrid-group-wall.php';
                break;
            case 'DISPLAY_NAME':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-display-name/profilegrid-display-name.php';
                break;
            case 'GROUP_PHOTOS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-group-photos/profilegrid-group-photos.php';
                break;
            case 'CUSTOM_PROFILE_SLUG':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-userid-slug-changer/profilegrid-userid-slug-changer.php';
                break;
            case 'CUSTOM_GROUP_FIELDS':
                $path = 'profilegrid-user-profiles-groups-and-communities-ProfileGrid-Custom-Group-Fields/profilegrid-group-fields.php';
                break;
            case 'GEOLOCATION':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-geolocation/profilegrid-geolocation.php';
                break;
            case 'FRONTEND_GROUP':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-front-end-groups/profilegrid-front-end-groups.php';
                break;
            case 'BBPRESS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-bbpress/profilegrid-bbpress.php';
                break;
            case 'WOOCOMMERCE':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce/profilegrid-woocommerce.php';
                break;
            case 'MAILCHIMP':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-mailchimp/profilegrid-mailchimp.php';
                break;
            case 'SOCIALLOGIN':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-social-connect/profilegrid-social-connect.php';
                break;
            case 'CUSTOM_TAB':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-custom-profile-tabs/profilegrid-custom-profile-tabs.php';
                break;
            case 'FRONTEND_GROUP_MANAGER':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-frontend-group-manager/profilegrid-frontend-group-manager.php';
                break;
            case 'MULTI_ADMINS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-multi-group-managers/profilegrid-multi-group-managers.php';
                break;
            case 'ADVANCED_WOOCOMMERCE':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-advanced-woocommerce-integration/profilegrid-advanced-woocommerce.php';
                break;
            case 'MYCRED':
                $path = 'profilegrid-user-profiles-groups-and-communities-ProfileGrid-myCred/profilegrid-mycred.php';
                break;
            case 'EVENTS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-eventprime-integration/profilegrid-eventprime-integration.php';
                break;
            case 'WISHLIST':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-wishlist/profilegrid-woocommerce-wishlist.php';
                break;
            case 'INSTAGRAM':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-instagram-integration/profilegrid-instagram-integration.php';
                break;
            case 'USER_PROFILE_LABELS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-profile-labels/profilegrid-profile-labels.php';
                break;
            case 'LOGIN_LOGOUT_MENU':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-menu-integration/profilegrid-menu-integration.php';
                break;
            case 'PROFILE_USER_STATUS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-profile-status/profilegrid-user-profile-status.php';
                break;
            case 'PROFILE_USER_PHOTOS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-photos-extension/profilegrid-user-photos-extension.php';
                break;
            case 'MENU_RESTRICTIONS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-menu-restrictions/profilegrid-menu-restriction.php';
                break;
            case 'DEMO_CONTENT':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-demo-content/profilegrid-demo-content.php';
                break;
            case 'WOO_PRODUCT_INTEGRATION':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-integration/profilegrid-woocommerce-product-integration.php';
                break;
            case 'HERO_BANNER':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-hero-banner/profilegrid-hero-banner.php';
                break;
            case 'WOO_SUBSCRIPTION_INTEGRATION':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-subscription-integration/profilegrid-woocommerce-subscription-integration.php';
                break;
            case 'WOO_MEMBER_DISCOUNT':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-custom-discount/profilegrid-woocommerce-product-members-discount.php';
                break;
            case 'WOO_CUSTOM_TABS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-custom-tabs/profilegrid-woocommerce-product-custom-tabs.php';
                break;
            case 'USERS_ONLINE':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-users-online-widget/profilegrid-users-online-widget.php';
                break;
            case 'USER_ACTIVITIES':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-activities/profilegrid-user-activities.php';
                break;
            case 'PRODUCT_RECOMMENDATIONS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-recommendations/profilegrid-woocommerce-product-recommendations.php';
                break;
            case 'RECENT_SIGNUP':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-recent-signup/profilegrid-recent-signup.php';
                break;
            case 'PROFILE_REVIEWS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-member-profile-reviews/profilegrid-member-profile-reviews.php';
                break;
            case 'GROUPS_SLIDER':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-groups-carousel-widget/profilegrid-groups-carousel-widget.php';
                break;
            case 'USERS_SLIDER':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-users-carousel-widget/profilegrid-users-carousel-widget.php';
                break;
            case 'FEATURED_GROUP':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-featured-group/profilegrid-featured-group.php';
                break;
            case 'COMPLETENESS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-profile-completeness/profilegrid-profile-completeness.php';
                break;
            case 'WIDGET_PRIVACY':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-widgets-privacy/profilegrid-widgets-privacy.php';
                break;
            case 'ZAPIER':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-zapier-integration/profilegrid-zapier-integration.php';
                break;
            case 'MAILPOET':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-mailpoet/profilegrid-mailpoet.php';
                break;
            case 'ELEMENTOR_CONTENT_RESTRICTIONS':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-content-restrictions/profilegrid-elementor-content-restrictions.php';
                break;
            case 'ELEMENTOR_USER_LOGIN':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-user-login/profilegrid-elementor-user-login.php';
                break;
            case 'ELEMENTOR_INTEGRATION':
                $path = 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-integration/profilegrid-elementor-integration.php';
                break;
		}
        return $path;
    }

    public function pg_is_extension_activated( $plugin ) {
		switch ( $plugin ) {
            case 'STRIPE':
                $is_activate = class_exists( 'Profilegrid_Stripe_Payment' );
                break;
            case 'GROUPWALL':
                $is_activate = class_exists( 'Profilegrid_Group_Wall' );
                break;
            case 'DISPLAY_NAME':
                $is_activate = class_exists( 'Profilegrid_Display_Name' );
                break;
            case 'GROUP_PHOTOS':
                $is_activate = class_exists( 'Profilegrid_Group_photos' );
                break;
            case 'CUSTOM_PROFILE_SLUG':
                $is_activate = class_exists( 'Profilegrid_Userid_Slug_Changer' );
                break;
            case 'CUSTOM_GROUP_FIELDS':
                $is_activate = class_exists( 'Profilegrid_Group_Fields' );
                break;
            case 'GEOLOCATION':
                $is_activate = class_exists( 'Profilegrid_Geolocation' );
                break;
            case 'FRONTEND_GROUP':
                $is_activate = class_exists( 'Profilegrid_Front_End_Groups' );
                break;
            case 'BBPRESS':
                $is_activate = class_exists( 'Profilegrid_Bbpress' );
                break;
            case 'WOOCOMMERCE':
                $is_activate = class_exists( 'Profilegrid_Woocommerce' );
                break;
            case 'MAILCHIMP':
                $is_activate = class_exists( 'Profilegrid_Mailchimp' );
                break;
            case 'SOCIALLOGIN':
                $is_activate = class_exists( 'Profilegrid_Social_Connect' );
                break;
            case 'CUSTOM_TAB':
                $is_activate = class_exists( 'Profilegrid_User_Content' );
                break;
            case 'FRONTEND_GROUP_MANAGER':
                $is_activate = class_exists( 'Profilegrid_Admin_Power' );
                break;
            case 'MULTI_ADMINS':
                $is_activate = class_exists( 'Profilegrid_Group_Multi_Admins' );
                break;
            case 'ADVANCED_WOOCOMMERCE':
                $is_activate = class_exists( 'Profilegrid_Advanced_Woocommerce' );
                break;
            case 'MYCRED':
                $is_activate = class_exists( 'Profilegrid_Mycred' );
                break;
            case 'EVENTS':
                $is_activate = class_exists( 'Profilegrid_EventPrime_Integration' );
                break;
            case 'WISHLIST':
                $is_activate = class_exists( 'Profilegrid_Woocommerce_Wishlist' );
                break;
            case 'INSTAGRAM':
                $is_activate = class_exists( 'Profilegrid_Instagram_Integration' );
                break;
            case 'USER_PROFILE_LABELS':
                $is_activate = class_exists( 'Profilegrid_Profile_Labels' );
                break;
            case 'LOGIN_LOGOUT_MENU':
                $is_activate = class_exists( 'Profilegrid_Menu_Integration' );
                break;
            case 'PROFILE_USER_STATUS':
                $is_activate = class_exists( 'Profilegrid_User_Profile_Status' );
                break;
            case 'PROFILE_USER_PHOTOS':
                $is_activate = class_exists( 'Profilegrid_User_Photos_Extension' );
                break;
            case 'MENU_RESTRICTIONS':
                $is_activate = class_exists( 'Profilegrid_Menu_Restriction' );
                break;
            case 'DEMO_CONTENT':
                $is_activate = class_exists( 'Profilegrid_Demo_Content' );
                break;
            case 'WOO_PRODUCT_INTEGRATION':
                $is_activate = class_exists( 'Profilegrid_Woocommerce_Product_Integration' );
                break;
            case 'HERO_BANNER':
                $is_activate = class_exists( 'Profilegrid_Hero_Banner' );
                break;
            case 'WOO_SUBSCRIPTION_INTEGRATION':
                $is_activate = class_exists( 'Profilegrid_Woocommerce_Subscription_Integration' );
                break;
			case 'WOO_MEMBER_DISCOUNT':
				$is_activate = class_exists( 'profilegrid_woocommerce_product_members_discount' );
                break;
            case 'WOO_CUSTOM_TABS':
                $is_activate = class_exists( 'profilegrid_woocommerce_product_custom_tabs' );
                break;
            case 'USERS_ONLINE':
                $is_activate = class_exists( 'Profilegrid_Active_Members_Widget' );
                break;
            case 'USER_ACTIVITIES':
                $is_activate = class_exists( 'Profilegrid_User_Activities' );
                break;
            case 'PRODUCT_RECOMMENDATIONS':
                $is_activate = class_exists( 'Profilegrid_Woocommerce_Product_Recommendations' );
                break;
            case 'RECENT_SIGNUP':
                $is_activate = class_exists( 'Profilegrid_Recent_Signup' );
                break;
            case 'PROFILE_REVIEWS':
                $is_activate = class_exists( 'Profilegrid_User_Reviews_Extension' );
                break;
            case 'GROUPS_SLIDER':
                $is_activate = class_exists( 'Profilegrid_groups_slider' );
                break;
            case 'USERS_SLIDER':
                $is_activate = class_exists( 'Profilegrid_user_slider' );
                break;
			case 'FEATURED_GROUP':
                $is_activate = class_exists( 'Profilegrid_featured_group' );
                break;
            case 'COMPLETENESS':
                $is_activate = class_exists( 'Profilegrid_Profile_Completeness' );
                break;
            case 'WIDGET_PRIVACY':
                $is_activate = class_exists( 'Profilegrid_widgets_privacy' );
                break;
            case 'ZAPIER':
                $is_activate = class_exists( 'Profilegrid_Zapier_Integration' );
                break;
            case 'MAILPOET':
                $is_activate = class_exists( 'Profilegrid_Mailpoet' );
                break;
            case 'ELEMENTOR_CONTENT_RESTRICTIONS':
                $is_activate = class_exists( 'Profilegrid_elementor_content_restrictions' );
                break;
            case 'ELEMENTOR_USER_LOGIN':
				$is_activate = class_exists( 'Profilegrid_elementor_login_logout_widget' );
                break;
			case 'ELEMENTOR_INTEGRATION':
				$is_activate = class_exists( 'Profilegrid_elementor_groups_widget' );
                break;
		}
        return $is_activate;
    }

    public function pg_get_extension_install_url( $plugin ) {
         $dbhandler = new PM_DBhandler();
        switch ( $plugin ) {
            case 'WOOCOMMERCE':
                $url = 'https://profilegrid.co/extensions/woocommerce-integration/';
                break;

            case 'DISPLAY_NAME':
                $url = 'https://profilegrid.co/extensions/user-display-name/';
                break;

            case 'CUSTOM_PROFILE_SLUG':
                $url = 'https://profilegrid.co/extensions/user-profile-custom-slugs/';
                break;

            case 'BBPRESS':
                $url = 'https://profilegrid.co/extensions/bbpress-integration/';
                break;

			case 'EVENTS':
                $url = 'http://profilegrid.co/extensions/eventprime-integration/';
                break;

            case 'STRIPE':
                $url = 'https://profilegrid.co/extensions/stripe-payment-system/';
                break;

            case 'GROUPWALL':
                $url = 'https://profilegrid.co/extensions/group-wall/';
                break;

            case 'GROUP_PHOTOS':
                $url = 'https://profilegrid.co/extensions/group-photos/';
                break;

            case 'CUSTOM_GROUP_FIELDS':
                 $url = 'https://profilegrid.co/extensions/custom-group-fields/';
                break;

            case 'GEOLOCATION':
                 $url = 'https://profilegrid.co/extensions/geolocation/';
                break;

            case 'FRONTEND_GROUP':
                $url = 'https://profilegrid.co/extensions/frontend-group-creator/';
                break;

            case 'MAILCHIMP':
                $url = 'https://profilegrid.co/extensions/mailchimp-integration/';
                break;

            case 'SOCIALLOGIN':
                $url = 'https://profilegrid.co/extensions/social-login/';
                break;

            case 'CUSTOM_TAB':
                $url = 'https://profilegrid.co/extensions/custom-user-profile-tabs-content/';
                break;

            case 'FRONTEND_GROUP_MANAGER':
                $url = 'https://profilegrid.co/extensions/frontend-group-manager/';
                break;

            case 'ADVANCED_WOOCOMMERCE':
                $url = 'https://profilegrid.co/extensions/advanced-woocommerce-integration/';
                break;

            case 'MULTI_ADMINS':
                $url = 'https://profilegrid.co/extensions/multi-group-managers/';
                break;

            case 'MYCRED':
                $url = 'https://profilegrid.co/extensions/mycred-integration/';
                break;

            case 'WISHLIST':
                $url = 'https://profilegrid.co/extensions/woocommerce-wishlist/';
                break;

            case 'INSTAGRAM':
                $url = 'https://profilegrid.co/extensions/instagram-integration/';
                break;

            case 'USER_PROFILE_LABELS':
                $url = 'https://profilegrid.co/extensions/profilegrid-profile-labels/';
                break;
            case 'LOGIN_LOGOUT_MENU':
                $url = 'https://profilegrid.co/extensions/profilegrid-login-logout-menu/';
                break;
            case 'PROFILE_USER_STATUS':
                $url = 'https://profilegrid.co/extensions/profilegrid-user-profile-status/';
                break;
            case 'PROFILE_USER_PHOTOS':
                $url = 'https://profilegrid.co/extensions/profilegrid-user-photos/';
                break;
            case 'MENU_RESTRICTIONS':
                $url = 'https://profilegrid.co/extensions/profilegrid-menu-restrictions/';
                break;
            case 'DEMO_CONTENT':
                $url = 'https://profilegrid.co/extensions/profilegrid-demo-content/';
                break;
            case 'WOO_PRODUCT_INTEGRATION':
                $url = 'https://profilegrid.co/extensions/profilegrid-woocommerce-product-integration/';
                break;
            case 'HERO_BANNER':
                $url = 'https://profilegrid.co/extensions/profilegrid-hero-banner/';
                break;
            case 'WOO_SUBSCRIPTION_INTEGRATION':
                $url = 'https://profilegrid.co/extensions/profilegrid-woocommerce-subscription-integration/';
                break;
			case 'WOO_MEMBER_DISCOUNT':
				$url = 'https://profilegrid.co/extensions/profilegrid-woocommerce-product-members-discount/';
                break;
            case 'WOO_CUSTOM_TABS':
				$url = 'https://profilegrid.co/extensions/profilegrid-woocommerce-product-custom-tabs/';
                break;
            case 'USERS_ONLINE':
                 $url = 'https://profilegrid.co/extensions/profilegrid-users-online-widget/';
                break;
            case 'USER_ACTIVITIES':
                $url = 'https://profilegrid.co/extensions/profilegrid-user-activities/';
                break;
            case 'PRODUCT_RECOMMENDATIONS':
                $url = 'https://profilegrid.co/extensions/profilegrid-woocommerce-product-recommendations/';
                break;
            case 'RECENT_SIGNUP':
                $url = 'https://profilegrid.co/extensions/profilegrid-recent-signup/';
                break;
            case 'PROFILE_REVIEWS':
                $url = 'https://profilegrid.co/extensions/profilegrid-member-profile-reviews/';
                break;
            case 'GROUPS_SLIDER':
                $url = 'https://profilegrid.co/extensions/profilegrid-groups-carousel-widget/';
                break;
            case 'USERS_SLIDER':
                $url = 'https://profilegrid.co/extensions/profilegrid-users-carousel-widget/';
                break;
            case 'FEATURED_GROUP':
                $url = 'https://profilegrid.co/extensions/profilegrid-featured-group/';
                break;
            case 'COMPLETENESS':
                $url = 'https://profilegrid.co/extensions/profilegrid-profile-completeness/';
                break;
            case 'WIDGET_PRIVACY':
                $url = 'https://profilegrid.co/extensions/profilegrid-widgets-privacy/';
                break;
            case 'ZAPIER':
                $url = 'https://profilegrid.co/extensions/profilegrid-zapier-integration/';
                break;
            case 'MAILPOET':
                $url = 'https://profilegrid.co/extensions/profilegrid-mailpoet/';
                break;
            case 'ELEMENTOR_CONTENT_RESTRICTIONS':
                $url = 'https://profilegrid.co/extensions/profilegrid-elementor-content-restrictions/';
                break;
            case 'ELEMENTOR_USER_LOGIN':
				$url = 'https://profilegrid.co/extensions/profilegrid-elementor-user-login/';
                break;
            case 'ELEMENTOR_INTEGRATION':
                $url = 'https://profilegrid.co/extensions/profilegrid-elementor-integration/';
                break;
            default:
                $url = 'http://profilegrid.co/extensions/';
                break;
        }

        $mgp = $this->get_current_theme_aff_id();
        if ( $mgp ) {
            $url = 'https://metagauss.com/get-profilegrid-for-wordpress/?mgp=' . $mgp;
        }

        return $url;
    }

    public function pg_get_extension_options_url( $plugin ) {
		switch ( $plugin ) {
            case 'STRIPE':
                $url = admin_url( 'admin.php?page=pm_payment_settings' );
                break;
            case 'GROUPWALL':
                $url = admin_url( 'admin.php?page=pm_group_wall_settings' );
                break;
            case 'DISPLAY_NAME':
                $url = admin_url( 'admin.php?page=pm_display_name_settings' );
                break;
            case 'GROUP_PHOTOS':
                $url = admin_url( 'admin.php?page=pm_group_photos_settings' );
                break;
            case 'CUSTOM_PROFILE_SLUG':
                $url = admin_url( 'admin.php?page=pm_uid_changer_settings' );
                break;
            case 'CUSTOM_GROUP_FIELDS':
                $url = admin_url( 'admin.php?page=pm_group_fields_settings' );
                break;
            case 'GEOLOCATION':
                $url = admin_url( 'admin.php?page=pm_geolocation_settings' );
                break;
            case 'FRONTEND_GROUP':
                $url = admin_url( 'admin.php?page=pm_front_end_groups_settings' );
                break;
            case 'BBPRESS':
                $url = admin_url( 'admin.php?page=pm_bbpress_settings' );
                break;
            case 'WOOCOMMERCE':
                $url = admin_url( 'admin.php?page=pm_woocommerce_settings' );
                break;
            case 'MAILCHIMP':
                $url = admin_url( 'admin.php?page=pm_mailchimp_settings' );
                break;
            case 'SOCIALLOGIN':
                $url = admin_url( 'admin.php?page=pm_social_connect_settings' );
                break;
            case 'CUSTOM_TAB':
                $url = admin_url( 'admin.php?page=pm_user_content_settings' );
                break;
            case 'ADVANCED_WOOCOMMERCE':
                $url = admin_url( 'admin.php?page=pm_advanced_woocommerce_settings' );
                break;
            case 'MYCRED':
                $url = admin_url( 'admin.php?page=pm_mycred_settings' );
                break;
            case 'WISHLIST':
                $url = admin_url( 'admin.php?page=pm_woocommerce_wishlist_settings' );
                break;
            case 'INSTAGRAM':
                $url = admin_url( 'admin.php?page=pm_instagram_settings' );
                break;
            case 'USER_PROFILE_LABELS':
                $url = admin_url( 'admin.php?page=pm_profile_labels_menu' );
                break;
            case 'LOGIN_LOGOUT_MENU':
                $url = admin_url( 'admin.php?page=pm_menu_integration_settings' );
                break;
            case 'PROFILE_USER_STATUS':
                $url = admin_url( 'admin.php?page=pm_profile_user_status_settings' );
                break;
            case 'PROFILE_USER_PHOTOS':
                $url = admin_url( 'admin.php?page=pm_user_photos_settings' );
                break;
            case 'DEMO_CONTENT':
                $url = admin_url( 'admin.php?page=pm_demo_content_settings' );
                break;
            case 'WOO_PRODUCT_INTEGRATION':
                $url = admin_url( 'admin.php?page=pm_woocommerce_product_integration_settings' );
                break;
            case 'HERO_BANNER':
                 $url = admin_url( 'admin.php?page=pm_settings' );
                break;
            case 'WOO_SUBSCRIPTION_INTEGRATION':
                 $url = admin_url( 'admin.php?page=pm_woocommerce_subscription_integration_settings' );
                break;
            case 'WOO_MEMBER_DISCOUNT':
				$url = admin_url( 'admin.php?page=pm_custom_product_price_settings' );
                break;
            case 'WOO_CUSTOM_TABS':
				$url = admin_url( 'admin.php?page=pm_custom_product_tabs_settings' );
                break;
            case 'USERS_ONLINE':
                $url = admin_url( 'admin.php?page=pm_online_members_settings' );
                break;
            case 'USER_ACTIVITIES':
                $url = admin_url( 'admin.php?page=pm_user_activities_settings' );
                break;
            case 'PRODUCT_RECOMMENDATIONS':
                $url = admin_url( 'admin.php?page=pm_woocommerce_related_products_settings' );
                break;
            case 'RECENT_SIGNUP':
                $url = admin_url( 'admin.php?page=pm_settings' );
                break;
            case 'PROFILE_REVIEWS':
                $url = admin_url( 'admin.php?page=pm_user_reviews_extension_settings' );
                break;
            case 'GROUPS_SLIDER':
                $url = admin_url( 'admin.php?page=pm_groups_slider_settings' );
                break;
            case 'USERS_SLIDER':
                $url = admin_url( 'admin.php?page=pm_users_slider_settings' );
                break;
			case 'COMPLETENESS':
                $url = admin_url( 'admin.php?page=pm_profile_completeness_settings' );
                break;
            case 'WIDGET_PRIVACY':
                $url = admin_url( 'admin.php?page=pm_widgets_privacy_settings' );
                break;
            case 'ZAPIER':
                $url = admin_url( 'admin.php?page=pm_zapier_integration_settings' );
                break;
            case 'MAILPOET':
                $url = admin_url( 'admin.php?page=pm_mailpoet_settings' );
                break;
            case 'ELEMENTOR_INTEGRATION':
                $url = admin_url( 'admin.php?page=pm_elementor_groups_widget_settings' );
                break;
            default:
                 $url = admin_url( 'admin.php?page=pm_settings' );
                break;
		}
        return $url;
    }

    public function pg_get_title_link( $plugin ) {
		return $this->pg_get_extension_install_url( $plugin );
    }

    public function pg_get_extension_button( $plugin ) {
         $data     = $this->pg_extension_install_button( $plugin );
        $optionurl = $this->pg_get_extension_options_url( $plugin );
        if ( $data->is_pg_extension_activate ) {
            if ( $plugin !='FRONTEND_GROUP_MANAGER' && $plugin !='MULTI_ADMINS' && $plugin !='EVENTS' && $plugin !='MENU_RESTRICTIONS' && $plugin !='HERO_BANNER' && $plugin !='RECENT_SIGNUP' && $plugin !='FEATURED_GROUP' && $plugin !='ELEMENTOR_CONTENT_RESTRICTIONS' && $plugin !='ELEMENTOR_USER_LOGIN' ) :
				?>
            <li>
                <a class="install-now button pg-install-now-btn pg-more-options" href="<?php echo esc_url( $optionurl ); ?>"><?php esc_html_e( 'OPTIONS', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
            </li>
				<?php
            endif;
        } elseif ( $data->is_pg_extension_installed ) {
            ?>
            <li>
            <a class="install-now button pg-install-now-btn pg-active-now" href="<?php echo esc_url( $data->pg_extsion_activate_url ); ?>"><?php esc_html_e( 'Activate Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
            </li>
			<?php
        } else {
            if ( $plugin=='CUSTOM_PROFILE_SLUG' || $plugin=='DISPLAY_NAME' || $plugin=='BBPRESS' || $plugin=='WOOCOMMERCE' || $plugin=='EVENTS' || $plugin=='LOGIN_LOGOUT_MENU' || $plugin=='DEMO_CONTENT' || $plugin=='HERO_BANNER' || $plugin=='USER_ACTIVITIES' || $plugin=='RECENT_SIGNUP' || $plugin=='GROUPS_SLIDER' || $plugin=='USERS_SLIDER' || $plugin=='FEATURED_GROUP' || $plugin=='ZAPIER' || $plugin=='MAILPOET' || $plugin=='ELEMENTOR_CONTENT_RESTRICTIONS' || $plugin=='ELEMENTOR_INTEGRATION' ) {
                $title = __( 'Free', 'profilegrid-user-profiles-groups-and-communities' );
                $class = 'pg-free-extension';
                ?>
            <li class="<?php echo esc_attr( $class ); ?>-wrap">
                <a class="install-now button pg-install-now-btn <?php echo esc_attr( $class ); ?>" target="_blank" href="<?php echo esc_url( $data->pg_get_extension_install_url ); ?>"><?php echo esc_html( $title ); ?></a>
                </li>
                <?php

            }
            ?>
            <li>
                <a class="install-now button pg-install-now-btn pg-more-info" target="_blank" href="<?php echo esc_url( $data->pg_get_extension_install_url ); ?>"><?php echo esc_html__( 'More Info', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
            </li>
            <li class="pg-not-installed"><?php esc_html_e( 'Not Installed', 'profilegrid-user-profiles-groups-and-communities' ); ?></li>   
            <?php
        }
    }

    public function pg_get_extension_shortcode( $plugin ) {
         $data     = $this->pg_extension_install_button( $plugin );
        $optionurl = $this->pg_get_extension_options_url( $plugin );
        if ( $data->is_pg_extension_installed ) {
            $link =  '<a class="pg_shortcode_link" href="' . $data->pg_extsion_activate_url . '">' . __( 'Activate Now', 'profilegrid-user-profiles-groups-and-communities' ) . '</a>';

        } else {

            $link = '<a class="pg_shortcode_link" target="_blank" href="' . $data->pg_get_extension_install_url . '">' . __( 'More Info', 'profilegrid-user-profiles-groups-and-communities' ) . '</a>';

        }
        return $link;

    }

    public function pg_check_free_extension( $plugin ) {
		switch ( $plugin ) {
            case 'DISPLAY_NAME':
                return true;
			   break;
            case 'CUSTOM_PROFILE_SLUG':
                return true;
			   break;
            default:
                return false;
			   break;
		}
    }

    public function get_current_theme_aff_id() {
        $current_theme = $this->pg_get_current_theme_name();
        $set_of_themes = $this->pg_get_theme_list_obj();
        if ( !empty( $set_of_themes ) && property_exists( $set_of_themes, $current_theme ) ) {
            return intval( $set_of_themes->$current_theme );
        } else {
			return false;
        }
    }

    public function pg_get_current_theme_name() {
        $theme_obj = wp_get_theme();
        return $theme_obj->__get( 'title' );
    }

    public function pg_get_theme_list_obj() {
        $path      =  plugin_dir_url( __FILE__ );
        $file_path = $path . 'theme-list.json';
        $raw_json  = file_get_contents( $file_path );
        if ( $raw_json != false ) {
            $raw_json = mb_convert_encoding( $raw_json, 'UTF-8', 'ISO-8859-1' );
            return json_decode( $raw_json );
        } else {
            return false;
        }
    }

    /**
     * Wrapper over wp_nonce_field() for templates.
     *
     * @param string $action Nonce action.
     * @param string $name   Optional nonce field name.
     */
    public function pm_render_nonce_field( $action, $name = '' ) {
        $field_name = ! empty( $name ) ? $name : '_wpnonce';
        $nonce      = wp_create_nonce( $action );
        echo '<input type="hidden" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $nonce ) . '" />';
    }

    /**
     * Check if a user is a manager/leader for a group.
     *
     * @param int $user_id User ID being checked.
     * @param int $gid     Group ID.
     * @return bool
     */
    public function pm_user_is_group_manager( $user_id, $gid ) {
        $user_id = absint( $user_id );
        $gid     = is_scalar( $gid ) ? trim( (string) $gid ) : '';
        if ( 0 === $user_id || '' === $gid ) {
            return false;
        }

        $pmrequests = new PM_request();
        $is_leader  = $pmrequests->pg_check_in_single_group_is_user_group_leader( $user_id, $gid );

        return (bool) apply_filters( 'pm_user_is_group_manager', $is_leader, $user_id, $gid );
    }



}

