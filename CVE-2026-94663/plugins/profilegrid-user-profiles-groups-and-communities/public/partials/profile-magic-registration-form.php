<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$pm_sanitizer = new PM_sanitizer();
$textdomain = $this->profile_magic;
$path = plugin_dir_url(__FILE__);

// Get group ID from various sources
$gid = filter_input(INPUT_GET, 'gid', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if(!isset($gid))
{
    if(isset($content['id']))
    {
        $gid = $content['id'];
    }
    if(isset($content['gid']))
    {
        $gid = $content['gid'];
    }
}

// Check if group is associated with RegistrationMagic form
$rmformid = $pmrequests->pm_check_if_group_associate_with_rm_form($gid);
if ($rmformid != 0 && class_exists('Registration_Magic')) {
    echo do_shortcode('[RM_Form id="' . absint($rmformid) . '"]');
} else {
    // Determine form type
    if (isset($content['type']) && ($content['type'] == 'multipage' || $content['type'] == 'paged')) {
        $type = 'reg-form-multipage-tpl';
    } else {
        $type = 'reg-form-tpl';
    }
    
    // Check if required email field exists
    $check = $pmrequests->pm_check_field_exist($gid, 'user_email', true);
    
    // Get group user count and limit
    $meta_query_array = $pmrequests->pm_get_user_meta_query(array('gid' => $gid));
    $user_query = $dbhandler->pm_get_all_users_ajax('', $meta_query_array);
    $total_users_in_group = $user_query->get_total();
    // Use the table's default unique field (id) to avoid bad placeholders in the where clause.
    $limit = $dbhandler->get_value('GROUPS', 'group_limit', $gid);
    $is_group_limit = $dbhandler->get_value('GROUPS', 'is_group_limit', $gid);
    
    // Sanitize input
    $req_obj = $pm_sanitizer->sanitize($_REQUEST);
    $post_obj = $pm_sanitizer->sanitize($_POST);
    
    // Check if this is a form preview/display request (not submission)
    if (isset($req_obj["action"]) && $req_obj["action"] != 'process') {
        do_action('profile_magic_before_registration_form', $post_obj, $req_obj, $gid, $textdomain);
        return false;
    }
    
    // Determine which fields to exclude based on login status
    if (is_user_logged_in() && !has_action('profilegrid_group_register_onlogin')) {
        $exclude = "AND field_type NOT IN ('user_name', 'user_email', 'user_avatar', 'user_pass', 'confirm_pass', 'paragraph', 'heading', 'read_only')";
    } else {
        $exclude = "AND field_type NOT IN ('read_only')";
    }
    
    // Get all form fields for this group
    $fields = $dbhandler->get_all_result(
        'FIELDS', 
        '*', 
        array('associate_group' => $gid, 'show_in_signup_form' => 1), 
        'results', 
        0, 
        false, 
        'ordering', 
        false, 
        $exclude
    );
    
    // Check if form was submitted
    if (isset($post_obj['reg_form_submit']) || isset($post_obj['pm_payment_method'])) {
        $errors = array();
        $registration_nonce = isset($post_obj['pm_registration_nonce']) ? sanitize_text_field($post_obj['pm_registration_nonce']) : '';

        if (!wp_verify_nonce($registration_nonce, 'pm_registration_action')) {
            $errors[] = esc_html__('Failed security check', 'profilegrid-user-profiles-groups-and-communities');
        }

        
        // CAPTCHA validation
        if ($pmrequests->profile_magic_show_captcha('pm_enable_recaptcha_in_reg')) {
            $response = isset($post_obj['g-recaptcha-response']) ? sanitize_text_field($post_obj['g-recaptcha-response']) : '';
            $remote_ip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP);
            $check_captcha = $pmrequests->profile_magic_captcha_verification($response, $remote_ip);
        } else {
            $check_captcha = true;
        }
        
        $error_message = apply_filters('pm_captcha_error', esc_html__('Captcha Failed', 'profilegrid-user-profiles-groups-and-communities'));
        $check_captcha = apply_filters('pm_captcha_validation_in_reg', $check_captcha);
        
        if ($check_captcha == true && empty($errors)) {
            // Frontend server validation
            $errors = $pmrequests->profile_magic_frontend_server_validation($post_obj, $_FILES, $_SERVER, $fields, $textdomain);
            
            // Check group limit
            if ($is_group_limit == 1) {
                if ($limit > $total_users_in_group) {
                    // Group has space
                } else {
                    $message = $dbhandler->get_value('GROUPS', 'group_limit_message', $gid);
                    $errors[] = $message;
                }
            }
            
            if (empty($errors)) {
                // Process registration
                if (is_user_logged_in() && !has_action('profilegrid_group_register_onlogin')) {
                    // Existing user joining group
                    $current_user_id = get_current_user_id();
                    $pmrequests->pm_update_user_custom_fields_data($post_obj, $_FILES, $_SERVER, $gid, $fields, $current_user_id);
                    $group_type = $pmrequests->profile_magic_get_group_type($gid);
                    $is_paid_group = $pmrequests->profile_magic_check_paid_group($gid);
                    
                    do_action('profile_magic_join_group_registration_process', $post_obj, $gid, $current_user_id);
                    
                    if ($is_paid_group == "0") {
                        $pmrequests->profile_magic_join_group_fun($current_user_id, $gid, $group_type);
                    }
                    
                    do_action('profile_magic_join_paid_group_process', $post_obj, $gid, $current_user_id);
                } else {
                    // New user registration
                    $user_id = $pmrequests->profile_magic_frontend_registration_request($post_obj, $_FILES, $_SERVER, $gid, $fields);
                    if (is_wp_error($user_id)) {
                        foreach ($user_id->get_error_messages() as $error) {
                            echo '<div class="pm-error">' . wp_kses_post($error) . '</div>';
                        }
                    } else {
                        do_action('profile_magic_registration_process', $post_obj, $_FILES, $_SERVER, $gid, $fields, $user_id, $textdomain);
                        
                        // Show success message if configured
                        if (!isset($post_obj['action']) && $dbhandler->get_value('GROUPS', 'show_success_message', $gid) == 1) {
                            echo wp_kses_post($dbhandler->get_value('GROUPS', 'success_message', $gid));
                        }
                        
                        // Redirect if configured
                        if ($pmrequests->pm_get_user_redirect($gid) != '') {
                            wp_redirect($pmrequests->pm_get_user_redirect($gid));
                            exit;
                        }
                    }
                }
            } else {
                // Display errors
                foreach ($errors as $error) {
                    echo '<div class="pm-error">' . wp_kses_post($error) . '</div>';
                }
            }
        } else {
            // CAPTCHA failed
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<div class="pm-error">' . wp_kses_post($error) . '</div>';
                }
            } else {
                echo '<div class="pm-error">' . esc_html($error_message) . '</div>';
            }
        }
    } else {
        // Form not submitted yet - display the form
        if ($check == false) {
            $message = esc_html__('Required Useremail field', 'profilegrid-user-profiles-groups-and-communities');
        } elseif ($is_group_limit == 1) {
            if ($limit > $total_users_in_group) {
                $message = '';
            } else {
                $message = $dbhandler->get_value('GROUPS', 'group_limit_message', $gid);
            }
        } elseif ($pmrequests->profile_magic_check_paid_group($gid) > 0) {
            $message = apply_filters('profile_magic_check_payment_config', '');
            if ($message == 'disabled') {
                $message = esc_html__('Payment system is not configured to accept payments. Please configure at least one payment processor for this to work.', 'profilegrid-user-profiles-groups-and-communities');
            }
        } else {
            $message = '';
        }
        
        if ($message != '') {
            echo '<div class="pm-message">' . wp_kses_post($message) . '</div>';
        } else {
            // Load the appropriate template
            $this->profile_magic_get_pm_theme_tmpl($type, $gid, $fields);
        }
    }
    
    // Enqueue multistep form JS if needed
    if (isset($content['type']) && ($content['type'] == 'paged' || $content['type'] == 'multipage') && !isset($post_obj['reg_form_submit']) && !is_user_logged_in()) {
        $script_version = defined('PROGRID_PLUGIN_VERSION') ? PROGRID_PLUGIN_VERSION : false;
        
        if (wp_script_is('profile-magic-multistep-form', 'registered')) {
            wp_enqueue_script('profile-magic-multistep-form');
        } else {
            wp_enqueue_script(
                'profile-magic-multistep-form',
                plugin_dir_url(__FILE__) . '../js/profile-magic-multistep-form.js',
                array('jquery', 'profilegrid-user-profiles-groups-and-communities'),
                $script_version,
                true
            );
        }
    }
}
?>
