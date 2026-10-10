<?php
class Profile_Magic_Extensions {

    public function block_generator( $data ) {
    }

    public function extensions_list() {
         $list             = array();
        $list['GROUPWALL'] =array(
            'slug'          => 'GROUPWALL',
            'price'         => 'paid',
            'filter'        => 'groups',
            'option_url'    => admin_url( 'admin.php?page=pm_group_wall_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/group-wall/',
            'title'         => __( 'Group Wall', 'profilegrid-user-profiles-groups-and-communities' ),
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-group-wall/profilegrid-group-wall.php',
            'class'         => 'Profilegrid_Group_Wall',
            'image'         => 'pg-groupwall.png',
            'description'   =>__( "A brand new ProfileGrid extension that adds social activity to your User Groups. Now users can create new posts, comment on other users' posts and browse Group timeline. Group wall is accessible from Group page as a new tab.", 'profilegrid-user-profiles-groups-and-communities' ),
        );

        $list['STRIPE'] =array(
            'slug'          => 'STRIPE',
            'price'         => 'paid',
            'filter'        => 'payments',
            'option_url'    => admin_url( 'admin.php?page=pm_payment_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/stripe-payment-system/',
            'title'         => __( 'Stripe Payments', 'profilegrid-user-profiles-groups-and-communities' ),
            'path'          => 'profilegrid-user-profiles-groups-and-communities-stripe-payment-gateway/profilegrid-stripe-payment.php',
            'class'         => 'Profilegrid_Stripe_Payment',
            'image'         => 'stripe-logo.png',
            'description'   =>__( 'Start accepting credit cards on your site for Group memberships and registrations by integrating popular Stripe payment gateway.', 'profilegrid-user-profiles-groups-and-communities' ),
        );

        $list['DISPLAY_NAME'] =array(
            'slug'          => 'DISPLAY_NAME',
            'price'         => 'free',
            'filter'        => 'profiles free',
            'option_url'    => admin_url( 'admin.php?page=pm_display_name_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/user-display-name/',
            'title'         => __( 'User Display Name', 'profilegrid-user-profiles-groups-and-communities' ),
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-display-name/profilegrid-display-name.php',
            'class'         => 'Profilegrid_Display_Name',
            'image'         => 'display_name.png',
            'description'   =>__( "Now take complete control of your users' display names. Mix and match patterns and add predefined suffixes and prefixes. There's a both global and per group option allowing display names in different groups stand out!", 'profilegrid-user-profiles-groups-and-communities' ),
        );

        $list['GROUP_PHOTOS'] =array(
            'slug'          => 'GROUP_PHOTOS',
            'price'         => 'paid',
            'filter'        => 'groups photos',
            'option_url'    => admin_url( 'admin.php?page=pm_group_photos_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/group-photos/',
            'title'         => __( 'Group Photos', 'profilegrid-user-profiles-groups-and-communities' ),
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-group-photos/profilegrid-group-photos.php',
            'class'         => 'Profilegrid_Group_photos',
            'image'         => 'group-photos.png',
            'description'   =>__( "Allow your users to create and share Photo Albums within their Groups. There's also an option for public photos. Users can enlarge and comment on different photos.", 'profilegrid-user-profiles-groups-and-communities' ),
        );

        $list['CUSTOM_PROFILE_SLUG'] =array(
            'slug'          => 'CUSTOM_PROFILE_SLUG',
            'price'         => 'free',
            'filter'        => 'profiles seo free',
            'option_url'    => admin_url( 'admin.php?page=pm_uid_changer_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/user-profile-custom-slugs/',
            'title'         => __( 'Custom User Profile Slugs', 'profilegrid-user-profiles-groups-and-communities' ),
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-userid-slug-changer/profilegrid-userid-slug-changer.php',
            'class'         => 'Profilegrid_Userid_Slug_Changer',
            'image'         => 'userid_slug.png',
            'description'   =>__( "Define how your user profile URL's will appear to site visitors and search engines. Take control of your user profile permalinks and add dynamic slugs.", 'profilegrid-user-profiles-groups-and-communities' ),
        );

        $list['CUSTOM_GROUP_FIELDS'] =array(
            'slug'          => 'CUSTOM_GROUP_FIELDS',
            'price'         => 'paid',
            'filter'        => 'groups',
            'title'         => __( 'Custom Group Properties', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'group-custom-fields.png',
            'description'   =>__( 'Create and add custom fields to groups too! Now your user groups can have more detailed information and personality just like your user profile pages.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_group_fields_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/custom-group-fields/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-ProfileGrid-Custom-Group-Fields/profilegrid-group-fields.php',
            'class'         => 'Profilegrid_Group_Fields',

		);

        $list['GEOLOCATION'] =array(
            'slug'          => 'GEOLOCATION',
            'price'         => 'paid',
            'filter'        => 'profiles integration',
            'title'         => __( 'User Geolocation Maps', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'geolocation.png',
            'description'   =>__( 'Generate maps showing locations of all users or specific groups using simple shortcodes. Get location data from registration form.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_geolocation_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/geolocation/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-geolocation/profilegrid-geolocation.php',
            'class'         => 'Profilegrid_Geolocation',

        );

        $list['FRONTEND_GROUP'] =array(
            'slug'          => 'FRONTEND_GROUP',
            'price'         => 'paid',
            'filter'        => 'groups form',
            'title'         => __( 'Frontend Group Creator', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'frontend-group.png',
            'description'   =>__( 'Allow registered users to create new Groups on front end. These Groups behave and work just like regular ProfileGrid groups.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_front_end_groups_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/frontend-group-creator/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-front-end-groups/profilegrid-front-end-groups.php',
            'class'         => 'Profilegrid_Front_End_Groups',

        );

        $list['BBPRESS'] =array(
            'slug'          => 'BBPRESS',
            'price'         => 'free',
            'filter'        => 'integration free',
            'title'         => __( 'bbPress Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'bbpress.png',
            'description'   =>__( 'Integrate ProfileGrid user profile properties and sign up system with the ever popular bbPress community forums plugin.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_bbpress_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/bbpress-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-bbpress/profilegrid-bbpress.php',
            'class'         => 'Profilegrid_Bbpress',

        );

        $list['WOOCOMMERCE'] =array(
            'slug'          => 'WOOCOMMERCE',
            'price'         => 'free',
            'filter'        => 'woocommerce integration free',
            'title'         => __( 'WooCommerce Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-woocommerce.png',
            'description'   =>__( "Combine the power of ProfileGrid's user groups with WooCommerce cart to provide your users ultimate shopping experience.", 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_woocommerce_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/woocommerce-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce/profilegrid-woocommerce.php',
            //'class'         => 'WOOCOMMERCE',
            'class'         => 'ProfileGrid_Woocommerce'

        );

        $list['MAILCHIMP'] =array(
            'slug'          => 'MAILCHIMP',
            'price'         => 'paid',
            'filter'        => 'integration newsletter',
            'title'         => __( 'MailChimp Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-mailchimp.png',
            'description'   =>__( 'Assign ProfileGrid users to MailChimp lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_mailchimp_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/mailchimp-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-mailchimp/profilegrid-mailchimp.php',
            'class'         => 'Profilegrid_Mailchimp',

        );

        $list['SOCIALLOGIN'] =array(
            'slug'          => 'SOCIALLOGIN',
            'price'         => 'paid',
            'filter'        => 'login integration',
            'title'         => __( 'Social Login', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'social-connect.png',
            'description'   =>__( 'Allow your users to sign up and login using their favourite social network accounts. Social accounts can be managed from Profile settings.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_social_connect_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/social-login/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-social-connect/profilegrid-social-connect.php',
            'class'         => 'Profilegrid_Social_Connect',

        );

        $list['CUSTOM_TAB'] =array(
            'slug'          => 'CUSTOM_TAB',
            'price'         => 'paid',
            'filter'        => 'profiles integration',
            'title'         => __( 'Custom User Profile Tabs', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'custom-profile-tab.png',
            'description'   =>__( 'Add personalized tabs in user profiles to suit your business or industry. Add user authored content from any custom post type or shortcode (or add specific content) with different privacy levels. Open doors to endless possibilities - Integrate user profiles with other plugins supporting custom post or shortcode format.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_user_content_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/custom-user-profile-tabs-content/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-custom-profile-tabs/profilegrid-custom-profile-tabs.php',
            'class'         => 'Profilegrid_User_Content',

        );

        $list['FRONTEND_GROUP_MANAGER'] =array(
            'slug'          => 'FRONTEND_GROUP_MANAGER',
            'price'         => 'paid',
            'filter'        => 'groups',
            'title'         => __( 'Advanced Group Manager', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'frontend-group-manager.png',
            'description'   =>__( 'Offer more power and control to your Group Managers. They can edit Groups, approve membership requests, moderate blogs, manage users, etc. from a dedicated frontend Group management area.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/frontend-group-manager/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-frontend-group-manager/profilegrid-frontend-group-manager.php',
            'class'         => 'Profilegrid_Admin_Power',

        );

        $list['ADVANCED_WOOCOMMERCE'] =array(
            'slug'          => 'ADVANCED_WOOCOMMERCE',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration',
            'title'         => __( 'WooCommerce Extensions Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-woo-advanced-icon.png',
            'description'   =>__( "Enhance the power of ProfileGrid's integration with WooCommerce by adding in integrations with WooCommerce extensions.", 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_advanced_woocommerce_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/advanced-woocommerce-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-advanced-woocommerce-integration/profilegrid-advanced-woocommerce.php',
            'class'         => 'Profilegrid_Advanced_Woocommerce',

        );
        //add filters here
        $list['MULTI_ADMINS'] =array(
            'slug'          => 'MULTI_ADMINS',
            'price'         => 'paid',
            'filter'        => 'groups',
            'title'         => __( 'Multiple Group Managers', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'multi-admins.png',
            'description'   =>__( "Don't stay limited to just one Manager per Group. Unlock the ability to have more than one Managers for your ProfileGrid User Groups now. With all of them sharing the same level of control.", 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/multi-group-managers/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-multi-group-managers/profilegrid-multi-group-managers.php',
            'class'         => 'Profilegrid_Group_Multi_Admins',

        );

        $list['MYCRED'] =array(
            'slug'          => 'MYCRED',
            'price'         => 'paid',
            'filter'        => 'profiles integration',
            'title'         => __( 'myCred Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-mycred-integration.png',
            'description'   =>__( 'Integrate popular points system for WordPress with ProfileGrid to reward your users. Display ranks and badges on user profile pages, give incentive for activities on site or penalize based on pre-set rules.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_mycred_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/mycred-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-ProfileGrid-myCred/profilegrid-mycred.php',
            'class'         => 'Profilegrid_Mycred',

        );

        $list['EVENTS'] =array(
            'slug'          => 'EVENTS',
            'price'         => 'free',
            'filter'        => 'free integration',
            'title'         => __( 'EventPrime Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-ep-integration.png',
            'description'   =>__( 'Create ProfileGrid Group Events by Integrating ProfileGrid User Groups with EventPrime Events.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'http://profilegrid.co/extensions/eventprime-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-eventprime-integration/profilegrid-eventprime-integration.php',
            'class'         => 'Profilegrid_EventPrime_Integration',

        );

        $list['WISHLIST'] =array(
            'slug'          => 'WISHLIST',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration',
            'title'         => __( 'WooCommerce Wishlist Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-wishlist-woocommerce.png',
            'description'   =>__( 'Add WooCommerce products to your Wishlist and manage it completely from your ProfileGrid User Profile.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_woocommerce_wishlist_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/woocommerce-wishlist/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-wishlist/profilegrid-woocommerce-wishlist.php',
            'class'         => 'Profilegrid_Woocommerce_Wishlist',

        );

        $list['INSTAGRAM'] =array(
            'slug'          => 'INSTAGRAM',
            'price'         => 'paid',
            'filter'        => 'photos integration',
            'title'         => __( 'Instagram Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-instagram.png',
            'description'   =>__( 'Show Instagram tab on User Profile page with user’s Instagram photos displayed in the tab.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_instagram_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/instagram-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-instagram-integration/profilegrid-instagram-integration.php',
            'class'         => 'Profilegrid_Instagram_Integration',

        );

        $list['USER_PROFILE_LABELS'] =array(
            'slug'          => 'USER_PROFILE_LABELS',
            'price'         => 'paid',
            'filter'        => 'profiles',
            'title'         => __( 'User Profile Labels', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-user-labels.png',
            'description'   =>__( 'Allow user to add Profile Labels to their User Profiles as an additional way to convey their interests and/or designation.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_profile_labels_menu' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-profile-labels/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-profile-labels/profilegrid-profile-labels.php',
            'class'         => 'Profilegrid_Profile_Labels',

        );

        $list['LOGIN_LOGOUT_MENU'] =array(
            'slug'          => 'LOGIN_LOGOUT_MENU',
            'price'         => 'free',
            'filter'        => 'login free',
            'title'         => __( 'Login Logout Menu', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-logout-icon.png',
            'description'   =>__( 'Now you can add contextual login menu item to your website menu(s) with few simple clicks. The menu item changes based on user login state. Additionally, you have option to add User Profile, User Groups and Password Recovery items too.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_menu_integration_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-login-logout-menu/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-menu-integration/profilegrid-menu-integration.php',
            'class'         => 'Profilegrid_Menu_Integration',
        );

        $list['PROFILE_USER_STATUS'] =array(
            'slug'          => 'PROFILE_USER_STATUS',
            'price'         => 'paid',
            'filter'        => 'profiles',
            'title'         => __( 'User Profile Status', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'profile_status.png',
            'description'   =>__( "Allow users to upload statuses to their user profiles. Users can view statuses on their own profiles and other users' profiles.", 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_profile_user_status_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-user-profile-status/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-profile-status/profilegrid-user-profile-status.php',
            'class'         => 'Profilegrid_User_Profile_Status',
        );

        // data to put into this
        $list['PROFILE_USER_PHOTOS'] =array(
            'slug'          => 'PROFILE_USER_PHOTOS',
            'price'         => 'paid',
            'filter'        => 'profiles photos',
            'title'         => __( 'ProfileGrid User Photos', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'user_photos.png',
            'description'   =>__( 'Allow users to upload and manage personal photos on their user profiles.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_user_photos_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-user-photos/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-photos-extension/profilegrid-user-photos-extension.php',
            'class'         => 'Profilegrid_User_Photos_Extension',
        );

        $list['MENU_RESTRICTIONS'] =array(
            'slug'          => 'MENU_RESTRICTIONS',
            'price'         => 'paid',
            'filter'        => 'content-restriction',
            'title'         => __( 'Menu Restrictions', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'menu_restrictions.png',
            'description'   =>__( "Use in-built ProfileGrid hierarchy to hide or show menu items on your site! You can mark specific menu items to be visible or hidden only to certain group(s). Create specific menu items for Group Managers of selected or all groups. Combine it with ProfileGrid's core content restriction system to build extremely powerful membership sites.", 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-menu-restrictions/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-menu-restrictions/profilegrid-menu-restriction.php',
            'class'         => 'Profilegrid_Menu_Restriction',
        );

        $list['DEMO_CONTENT'] =array(
            'slug'          => 'DEMO_CONTENT',
            'price'         => 'free',
            'filter'        => 'profiles groups free',
            'title'         => __( 'Demo Content', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'demo-content.png',
            'description'   =>__( 'This dynamic extension enables admin to import demo content. The admin can also import these groups with multiple fields, sections and users. Moreover, the admins get an option to choose number of demo groups they want to import.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_demo_content_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-demo-content/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-demo-content/profilegrid-demo-content.php',
            'class'         => 'Profilegrid_Demo_Content',
        );

        $list['WOO_PRODUCT_INTEGRATION'] =array(
            'slug'          => 'WOO_PRODUCT_INTEGRATION',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration',
            'title'         => __( 'WooCommerce Product Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'woocommerce-product-intregration.png',
            'description'   =>__( 'This ravishing extension allows you to integrate WooCommerce products with ProfileGrid Groups. You can assign groups to your users based on the type of products they buy or the amount of purchase they make on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_woocommerce_product_integration_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-woocommerce-product-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-integration/profilegrid-woocommerce-product-integration.php',
            'class'         => 'Profilegrid_Woocommerce_Product_Integration',
        );

        $list['WOO_SUBSCRIPTION_INTEGRATION'] =array(
            'slug'          => 'WOO_SUBSCRIPTION_INTEGRATION',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration payments',
            'title'         => __( 'WooCommerce Subscription Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-wooCommerce-subscription.png',
            'description'   =>__( 'Integrate WooCommerce product subscriptions with ProfileGrid Groups. Assign/Unassign the groups to the users based on WooCommerce subscription.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_woocommerce_subscription_integration_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-woocommerce-subscription-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-subscription-integration/profilegrid-woocommerce-subscription-integration.php',
            'class'         => 'Profilegrid_Woocommerce_Subscription_Integration',
        );

        $list['HERO_BANNER'] =array(
            'slug'          => 'HERO_BANNER',
            'price'         => 'free',
            'filter'        => 'widget profiles free',
            'title'         => __( 'Hero Banner', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'hero-banner.png',
            'description'   =>__( 'With the dynamic Hero Banner feature showcase your group profiles as a striking hero image on your WordPress website. You can add multiple rows and columns of your choice.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-hero-banner/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-hero-banner/profilegrid-hero-banner.php',
            'class'         => 'Profilegrid_Hero_Banner',
        );

        $list['WOO_MEMBER_DISCOUNT'] =array(
            'slug'          => 'WOO_MEMBER_DISCOUNT',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration payments',
            'title'         => __( 'WooCommerce Members Discount', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-member-discount.png',
            'description'   =>__( 'Add custom discount on WooCommerce product purchase for users based on group.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_custom_product_price_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-woocommerce-product-members-discount/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-custom-discount/profilegrid-woocommerce-product-members-discount.php',
            'class'         => 'profilegrid_woocommerce_product_members_discount',
        );

        $list['WOO_CUSTOM_TABS'] =array(
            'slug'          => 'WOO_CUSTOM_TABS',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration',
            'title'         => __( 'WooCommerce Product Tabs', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-woo-custom-tabs.png',
            'description'   =>__( 'Add personalized tabs to WooCommerce Product.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_custom_product_tabs_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-woocommerce-product-custom-tabs/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-custom-tabs/profilegrid-woocommerce-product-custom-tabs.php',
            'class'         => 'profilegrid_woocommerce_product_custom_tabs',
        );

        $list['USERS_ONLINE'] =array(
            'slug'          => 'USERS_ONLINE',
            'price'         => 'paid',
            'filter'        => 'widget',
            'title'         => __( 'Online Users', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'users-online-widget.png',
            'description'   =>__( 'Displays a list of currently online users with their profile images and display names.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_online_members_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-users-online-widget/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-users-online-widget/profilegrid-users-online-widget.php',
            'class'         => 'Profilegrid_Active_Members_Widget',
        );

        $list['USER_ACTIVITIES'] =array(
            'slug'          => 'USER_ACTIVITIES',
            'price'         => 'paid',
            'filter'        => 'widget',
            'title'         => __( 'User Activities', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'user-activities-icon.png',
            'description'   =>__( 'Display various activities by different users inside a beautiful widget, that can fit any widget area of your website.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_user_activities_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-user-activities/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-user-activities/profilegrid-user-activities.php',
            'class'         => 'Profilegrid_User_Activities',
        );

        $list['PRODUCT_RECOMMENDATIONS'] =array(
            'slug'          => 'PRODUCT_RECOMMENDATIONS',
            'price'         => 'paid',
            'filter'        => 'woocommerce integration',
            'title'         => __( 'WooCommerce Product Recommendations', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'product-recommendations.png',
            'description'   =>__( 'Display product suggestions right inside user profiles based on the user’s purchase history.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_woocommerce_related_products_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-woocommerce-product-recommendations/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-product-recommendations/profilegrid-woocommerce-product-recommendations.php',
            'class'         => 'Profilegrid_Woocommerce_Product_Recommendations',
        );

        $list['RECENT_SIGNUP'] =array(
            'slug'          => 'RECENT_SIGNUP',
            'price'         => 'paid',
            'filter'        => 'widget',
            'title'         => __( 'Recent User Signups', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'recent-signup.png',
            'description'   =>__( 'A widget and a shortcode which allows you to display a pre-defined number of recently added users with profile images, and an option to add a custom link.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-recent-signup/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-recent-signup/profilegrid-recent-signup.php',
            'class'         => 'Profilegrid_Recent_Signup',
        );

        $list['PROFILE_REVIEWS'] =array(
            'slug'          => 'PROFILE_REVIEWS',
            'price'         => 'paid',
            'filter'        => 'profiles',
            'title'         => __( 'User Profile Reviews', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'profile-reviews.png',
            'description'   =>__( 'Empower your website members to rate and review other member profiles. Advanced options and restrictions allow you to customize it to fit different scenarios.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_user_reviews_extension_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-member-profile-reviews/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-member-profile-reviews/profilegrid-member-profile-reviews.php',
            'class'         => 'Profilegrid_User_Reviews_Extension',
        );

        $list['GROUPS_SLIDER'] =array(
            'slug'          => 'GROUPS_SLIDER',
            'price'         => 'paid',
            'filter'        => 'widget groups',
            'title'         => __( 'User Groups Slider', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'group-carousel-slider.png',
            'description'   =>__( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_groups_slider_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-groups-carousel-widget/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-groups-carousel-widget/profilegrid-groups-carousel-widget.php',
            'class'         => 'Profilegrid_groups_slider',
        );

        $list['USERS_SLIDER'] =array(
            'slug'          => 'USERS_SLIDER',
            'price'         => 'paid',
            'filter'        => 'widget profiles',
            'title'         => __( 'Users Slider', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'user-carousel-slider.png',
            'description'   =>__( 'A beautiful carousel slider that looks good and fits any widget area of your site. Fully configurable for your requirements.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_users_slider_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-users-carousel-widget/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-users-carousel-widget/profilegrid-users-carousel-widget.php',
            'class'         => 'Profilegrid_user_slider',
        );

        $list['FEATURED_GROUP'] =array(
            'slug'          => 'FEATURED_GROUP',
            'price'         => 'paid',
            'filter'        => 'widget groups',
            'title'         => __( 'Featured Group', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'featured-group.png',
            'description'   =>__( 'A customizable frontend ProfileGrid Widget that allows you to display featured membership groups.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-featured-group/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-featured-group/profilegrid-featured-group.php',
            'class'         => 'Profilegrid_featured_group',
        );

        $list['COMPLETENESS'] =array(
            'slug'          => 'COMPLETENESS',
            'price'         => 'paid',
            'filter'        => 'profiles',
            'title'         => __( 'Profile Completeness', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'profile-completness.png',
            'description'   =>__( 'Displays a profile completeness progress bar on user profile page to encourage the user to fill out profile data.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_profile_completeness_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-profile-completeness/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-profile-completeness/profilegrid-profile-completeness.php',
            'class'         => 'Profilegrid_Profile_Completeness',
        );

        $list['WIDGET_PRIVACY'] =array(
            'slug'          => 'WIDGET_PRIVACY',
            'price'         => 'paid',
            'filter'        => 'content-restriction',
            'title'         => __( 'Widgets Privacy', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'widget-privacy.png',
            'description'   =>__( 'Adds additional multi-level privacy options to all ProfileGrid widgets. Now you can restrict relevant information only to intended audience. You can also create multiple instances of a widget to deliver different information to different types of audiences.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_widgets_privacy_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-widgets-privacy/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-widgets-privacy/profilegrid-widgets-privacy.php',
            'class'         => 'Profilegrid_widgets_privacy',
        );

        $list['ZAPIER'] =array(
            'slug'          => 'ZAPIER',
            'price'         => 'paid',
            'filter'        => 'integration',
            'title'         => __( 'Zapier Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-zapier-icon.png',
            'description'   =>__( 'Extend the power of ProfileGrid using Zapier automation! Connect with over 3000 apps using readymade templates or create custom automations to work with ProfileGrid triggers.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_zapier_integration_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-zapier-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-zapier-integration/profilegrid-zapier-integration.php',
            'class'         => 'Profilegrid_Zapier_Integration',
        );

        $list['MAILPOET'] =array(
            'slug'          => 'MAILPOET',
            'price'         => 'free',
            'filter'        => 'integration newsletter free',
            'title'         => __( 'MailPoet Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'mailpoet.png',
            'description'   =>__( 'Assign ProfileGrid users to MailPoet lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_mailpoet_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-mailpoet/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-mailpoet/profilegrid-mailpoet.php',
            'class'         => 'Profilegrid_Mailpoet',
        );

        $list['ELEMENTOR_CONTENT_RESTRICTIONS'] =array(
            'slug'          => 'ELEMENTOR_CONTENT_RESTRICTIONS',
            'price'         => 'paid',
            'filter'        => 'content-restriction integration',
            'title'         => __( 'Elementor Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'elementor-content-restrictions.png',
            'description'   =>__( 'Profilegrid content restrictions widget provides the feature to restrict content with Elementor page builder.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_profile_user_status_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-elementor-content-restrictions/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-content-restrictions/profilegrid-elementor-content-restrictions.php',
            'class'         => 'Profilegrid_elementor_content_restrictions',
        );

        $list['ELEMENTOR_USER_LOGIN'] =array(
            'slug'          => 'ELEMENTOR_USER_LOGIN',
            'price'         => 'paid',
            'filter'        => 'integration login',
            'title'         => __( 'Elementor User Login', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-elementor-user-login.png',
            'description'   =>__( 'ProfileGrid login box as native Elementor login widget for building beautiful custom login pages.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-elementor-user-login/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-user-login/profilegrid-elementor-user-login.php',
            'class'         => 'Profilegrid_elementor_login_logout_widget',
        );

        $list['ELEMENTOR_INTEGRATION'] =array(
            'slug'          => 'ELEMENTOR_INTEGRATION',
            'price'         => 'free',
            'filter'        => 'integration free',
            'title'         => __( 'Elementor Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'elementor-icon.png',
            'description'   =>__( 'Use ProfileGrid elements as native Elementor widgets and build custom layouts you always wanted with your favorite composer.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_elementor_groups_widget_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-elementor-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-elementor-integration/profilegrid-elementor-integration.php',
            'class'         => 'Profilegrid_elementor_groups_widget',
        );
        
        $list['CUSTOM_GROUP_SLUG'] =array(
            'slug'          => 'CUSTOM_GROUP_SLUG',
            'price'         => 'paid',
            'filter'        => 'integration seo customsolution',
            'title'         => __( 'Customized Group Slugs', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'group-slug.png',
            'description'   =>__( 'Use ProfileGrid to customize Group slugs and update SEO page title and description.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_group_slug_settings' ),
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-customized-group-slugs/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-customized-group-slugs/profilegrid-customized-group-slugs.php',
            'class'         => 'Profilegrid_Custom_Group_Slug',
        );
      
        $list['WOO_PRODUCT_RESTRICTION'] =array(
            'slug'          => 'WOO_PRODUCT_RESTRICTION',
            'price'         => 'paid',
            'filter'        => 'integration woocommerce customsolution',
            'title'         => __( 'WooCommerce Customized Product Restrictions', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-group-product-restriction.png',
            'description'   =>__( 'Adds control over WooCommerce products visibility to ProfileGrid Groups.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-wooCommerce-customized-product-restrictions/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-woocommerce-customized-product-restrictions/profilegrid-woocommerce-customized-product-restrictions.php',
            'class'         => 'Profilegrid_Woocommerce_Product_Restrictions',
        );
        
        $list['TERAWALLET_INTEGRATION'] =array(
            'slug'          => 'TERAWALLET_INTEGRATION',
            'price'         => 'paid',
            'filter'        => 'integration woocommerce customsolution',
            'title'         => __( 'Customized TeraWallet Integration', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-wallet-icon.png',
            'description'   =>__( 'Offers powerful Group based credit control to admins and Group leaders. It integrates with WooCommerce and TeraWallet plugins to provide a workflow enabling Group based credit limit spendable on WooCommerce.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => '',
            'extension_url' =>'https://profilegrid.co/extensions/profilegrid-customized-terawallet-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-customized-terawallet-integration/profilegrid-credit.php',
            'class'         => 'Profilegrid_Credit',
        );
        
        $list['PROFILE_VISITORS'] =array(
            'slug'          => 'PROFILE_VISITORS',
            'price'         => 'paid',
            'filter'        => 'profiles',
            'title'         => __( 'Profile Visitors', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'profile-visitor-icon.png',
            'description'   => __( 'Enable users to see who visited their profile with detailed visitor information, enhancing engagement and interaction. Track and display profile visitors directly on the user profile page.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_profile_visitor_details_settings' ),
            'extension_url' => 'https://profilegrid.co/extensions/profile-visitors/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-profile-visitors/profilegrid-profile-visitors.php',
            'class'         => 'Profilegrid_profile_visitor_details',
        );
        
        $list['TURNSTILE'] =array(
            'slug'          => 'TURNSTILE',
            'price'         => 'paid',
            'filter'        => 'security',
            'title'         => __( 'Turnstile Antispam Security', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'pg-turnstile-icon.png',
            'description'   => __( 'ProfileGrid Turnstile Antispam Security adds Cloudflare Turnstile protection to your user registration and profile forms, preventing automated spam submissions while maintaining a smooth user experience.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_turnstile_security_settings' ),
            'extension_url' => 'https://profilegrid.co/extensions/turnstile-antispam-security/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-turnstile-antispam-security/profilegrid-turnstile-antispam-security.php',
            'class'         => 'Profilegrid_Turnstile_Antispam_Cloudflare',
        );
        $list['PROFILEGRID_F12'] =array(
            'slug'          => 'PROFILEGRID_F12',
            'price'         => 'paid',
            'filter'        => 'security integration form',
            'title'         => __( 'Profilegrid F12', 'profilegrid-user-profiles-groups-and-communities' ),
            'image'         => 'f12-integration.png',
            'description'   => __( 'Integration of F12 Spam Protection plugin with ProfileGrid to comply with GDPR.', 'profilegrid-user-profiles-groups-and-communities' ),
            'option_url'    => admin_url( 'admin.php?page=pm_f12_integration_settings' ),
            'extension_url' => 'https://profilegrid.co/extensions/f12-integration/',
            'path'          => 'profilegrid-user-profiles-groups-and-communities-profilegrid-turnstile-antispam-security/profilegrid-turnstile-antispam-security.php',
            'class'         => 'Profilegrid_F12_Integration',
        );
        return $list;
    }

    public function pg_check_extension_installed( $path ) {
         $plugins = get_plugins();
        return array_key_exists( $path, $plugins );
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

    public function pg_extension_status( $ext ) {
		if ( class_exists( $ext['class'] ) ) {
			echo '<span class="pg-ext-installed">' . esc_html__( 'Installed', 'profilegrid-user-profiles-groups-and-communities' ) . '</span>';
		} elseif ( $this->pg_check_extension_installed( $ext['path'] ) ) {
			echo '<span class="pg-ext-not-installed">' . esc_html__( 'Inactive', 'profilegrid-user-profiles-groups-and-communities' ) . '</span>';
		} else {

			echo '<span class="pg-ext-not-installed">' . esc_html__( 'Not Installed', 'profilegrid-user-profiles-groups-and-communities' ) . '</span>';
		}
    }

    public function pg_get_extension_button( $ext ) {
		if ( class_exists( $ext['class'] ) ) {
            if ( $ext['option_url']!='' ) {
				?>
        
                <a class="pg-install-now-btn pg-more-options" href="<?php echo esc_url( $ext['option_url'] ); ?>"><?php esc_html_e( 'Setting', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>

				<?php
            }
		} elseif ( $this->pg_check_extension_installed( $ext['path'] ) ) {
			?>
         
            <a class="pg-install-now-btn pg-active-now" href="<?php echo esc_url( $this->get_pg_extension_activate_url( $ext['path'] ) ); ?>"><?php esc_html_e( 'Activate Now', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
          
			<?php
		} else {
			?>
                    
                <a class="pg-install-now-btn pg-more-info" target="_blank" href="<?php echo esc_url( $ext['extension_url'] ); ?>"><?php echo esc_html__( 'Download', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>           
            <?php
		}
    }
}
