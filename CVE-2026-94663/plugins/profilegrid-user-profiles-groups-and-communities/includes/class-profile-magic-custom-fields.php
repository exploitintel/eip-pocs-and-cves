<?php

class PM_Custom_Fields {

    /**
     * Tracks how many username fields this instance has rendered to avoid duplicate IDs without relying on static state.
     */
    private $user_name_field_render_count = 0;

    public function pm_get_custom_form_fields($row, $value = '', $textdomain = '') {
        if ($textdomain == '')
            $textdomain = 'profilegrid-user-profiles-groups-and-communities';
        $function = 'pm_get_custom_form_field_' . $row->field_type;
        
//        // Step 1: Decode URL encoding only if the value is a string
//        $decoded_val =  $value;
//
//        // Step 2: Strong Serialized Object Injection Protection (handles escaped characters too)
//        if (preg_match('/[oc]:\d+:\{?.*?\}?/i', stripslashes($decoded_val))) {
//            $value = ''; // 🚫 Block PHP Object Injection before unserialization
//        }
//
//        // Step 3: Double Check with `is_serialized()`
//        if (is_string($decoded_val)) {
//            $value = ''; // 🚫 Block serialized input
//        }
//
//        if($value!=''){$value = maybe_unserialize($value);}
//        
//        // Step 4: Ensure all values are plain data (avoid objects)


       // Step 1: Decode URL encoding only if the value is a string
        $value = is_string($value) ? urldecode($value) : $value;

        // Step 2: Strong Serialized Object Injection Protection (handles escaped characters too)
        if (is_string($value) && preg_match('/[oc]:\d+:\{?.*?\}?/i', stripslashes($value))) {
            $value = ''; // 🚫 Block PHP Object Injection before unserialization
            
        }
        
        if($value!=''){$value = maybe_unserialize($value);}
        if(is_object($value))
        {
            $value ='';
        }
        
        if (!is_array($value)) {
            $value = json_decode(json_encode($value), true);
        }
        
        if(method_exists($this,$function))
        {
            $this->$function($row, $value, $textdomain);
        }
        else
        {
            do_action('pg_add_custom_field_form_html',$row,$value,$row->field_type);
            do_action('pg_add_custom_field_'.$row->field_type,$row,$value,$row->field_type);
        }
        
    }

    public function pm_get_custom_form_field_mailchimp($row, $value = '', $textdomain = '') {
        if ($textdomain == '')
            $textdomain = 'profilegrid-user-profiles-groups-and-communities';
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        $function = 'pm_get_custom_form_field_mailchimp_' . $field_options['mailchimp_field_type'];
        $this->$function($row, $value, $textdomain);
    }

    public function pm_get_custom_login_fields($type, $textdomain = '') {
        if ($textdomain == '')
            $textdomain = 'profilegrid-user-profiles-groups-and-communities';
        $function = 'pm_get_custom_login_field_' . $type;
        $this->$function($textdomain);
    }

    public function pm_get_custom_login_field_username($textdomain = '') {
        ?>        
        <div class="pm-col">

            <div class="pm-field-lable">
                <label for="user_login"><?php esc_html_e('Username or Email','profilegrid-user-profiles-groups-and-communities'); ?></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="text" name="<?php echo esc_attr('user_login'); ?>" id="<?php echo esc_attr('user_login'); ?>" required="required">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_login_field_password($textdomain = '') {
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <label for="user_pass"><?php esc_html_e('Password','profilegrid-user-profiles-groups-and-communities'); ?></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="password" name="<?php echo esc_attr('user_pass'); ?>" id="<?php echo esc_attr('user_pass'); ?>" required="required">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_login_field_remember_me($textdomain = '') {
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">

            </div>
            <div class="pm-field-input">
                <input type="checkbox" name="<?php echo esc_attr('remember_me'); ?>" id="<?php echo esc_attr('remember_me'); ?>">
                <label for="remember_me"><?php esc_html_e('Remember Me','profilegrid-user-profiles-groups-and-communities'); ?></label>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_first_name($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_pagebreak($row, $value = '', $textdomain = '') {
        
    }

    public function pm_get_custom_form_field_last_name($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }
    
    public function pm_get_custom_form_field_nickname($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_user_name($row, $value) {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];

        $this->user_name_field_render_count++;
        $base_id = $row->field_key;
        $input_id = ($this->user_name_field_render_count > 1) ? $base_id . '_' . $this->user_name_field_render_count : $base_id;
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($input_id); ?>"><?php echo esc_attr($row->field_name); ?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_user_name pm_required">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>" onkeyup="pm_frontend_check_username()" onblur="pm_frontend_check_username()">
                <div class="errortext" style="display:none;"></div>
                <div class="usernameerror" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_user_email($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_email pm_user_email pm_required">
                <?php    
                    if (has_action('profilegrid_group_register_onlogin')){
                        ?>
                        <input title="<?php echo esc_attr($row->field_desc); ?>" type="email" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>"  >
                        <?php
                    }else{
                        ?>
                        <input title="<?php echo esc_attr($row->field_desc); ?>" type="email" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>"  value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>" onkeyup="pm_frontend_check_useremail('<?php echo esc_attr($value); ?>')" onblur="pm_frontend_check_useremail('<?php echo esc_attr($value); ?>')" <?php if(is_user_logged_in())echo 'disabled';?>>
                        <?php
                    }
                ?>
                <div class="errortext" style="display:none;"></div>
                <div class="useremailerror" style="display:none;"></div>
            </div>
        </div>

        <?php
    }
    
    public function pm_get_custom_form_field_read_only($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_user_url($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_user_url <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }
    
    public function pm_get_custom_form_field_url($row, $value = '', $textdomain = '') 
    {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options))
        {
            $value = array('text'=>'','url'=>'');
        }
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_user_url <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo (is_array($value) && isset($value['text'])) ? esc_attr($value['text']) : ''; ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>[text]" placeholder="<?php esc_attr_e('Link Text','profilegrid-user-profiles-groups-and-communities'); ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo (is_array($value) && isset($value['url'])) ? esc_attr($value['url']) : ''; ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>[url]" placeholder="<?php esc_attr_e('URL','profilegrid-user-profiles-groups-and-communities'); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
            
        </div>

        <?php
    }

    public function pm_get_custom_form_field_user_pass($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_user_pass pm_required">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="password" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($field_options['default_value']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_confirm_pass($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_confirm_pass pm_required">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="password" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($field_options['default_value']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_heading($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
            <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
            </div>
            <div class="pm-field-input">
                <<?php if (!empty($field_options)) echo esc_attr($field_options['heading_tag']); ?> class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>"><?php if (!empty($field_options)) echo esc_attr($field_options['heading_text']); ?></<?php if (!empty($field_options)) echo esc_attr($field_options['heading_tag']); ?>>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_paragraph($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
            <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
            </div>
            <div class="pm-field-input">
                <p class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>"><?php if (!empty($field_options)) echo wp_kses_post($field_options['paragraph_text']); ?></p>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_description($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1){ if(is_user_logged_in()){echo 'pm_rich_editor_required';}else{ echo 'pm_textarearequired';}} ?>">
                <?php if(is_user_logged_in()): 
                    
                $editor_id = esc_attr($row->field_key);
                $settings = array('wpautop' => false,'media_buttons' => true,
                   'teeny' => false,
                    'dfw' => false,
                    'tinymce' => true, 
                    'quicktags' => true
                );
                wp_editor($value, $editor_id,$settings );
                else: ?>
                 <textarea title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" cols="<?php if (!empty($field_options)) echo esc_attr($field_options['columns']); ?>" rows="<?php if (!empty($field_options)) echo esc_attr($field_options['rows']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>"><?php echo wp_kses_post($value); ?></textarea>
                <?php   
                endif; ?>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_text($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_select($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_select_required'; ?>">
                <select title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>">
                    <?php
                    if (!empty($field_options)) {
                        if ($field_options['first_option'] != '') {
                            echo '<option value="">' . esc_html($field_options['first_option']) . '</option>';
                        }
                        $arr = explode(',', $field_options['dropdown_option_value']);
                        foreach ($arr as $ar) {
                            ?>
                            <option value="<?php echo esc_attr($ar); ?>" <?php if ($ar == $value) echo 'selected'; ?>><?php echo esc_html($ar); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_radio($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_radiorequired'; ?>">
                <div class="pmradio">
                    <?php
                    if (!empty($field_options) && !empty($field_options['radio_option_value'])) {

                        foreach ($field_options['radio_option_value'] as $radio) {
                            if(!empty($radio)){
                            ?>
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" type="radio" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key.'_'.str_replace(' ', '_', strtolower($radio))); ?>" name="<?php echo esc_attr($row->field_key); ?>" value="<?php echo esc_attr($radio); ?>" <?php if ($radio == $value) echo 'checked'; ?>> <label for="<?php echo esc_attr($row->field_key.'_'.str_replace(' ', '_', strtolower($radio))); ?>"><?php echo esc_attr($radio); ?></label></div>
                            <?php
                            }
                        }
                    }
                    ?>
                </div>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_checkbox($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if (empty($value) && !empty($field_options))
            $value = $field_options['default_value'];
        if (is_array($value)) {
            $array_value = $value;
        } else {
            $array_value = explode(',', $value);
        }
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_checkboxrequired'; ?>">
                <div class="pmradio">
        <?php
        if (!empty($field_options) && !empty($field_options['radio_option_value'])) {
            foreach ($field_options['radio_option_value'] as $radio) {
                $checked = '';
                if (!empty($array_value) && is_array($array_value)) {
                    if (in_array($radio, $array_value) == true) {
                        $checked = 'checked';
                    }
                }
                if ($radio == 'chl_other') {
                    if (is_array($array_value) && in_array('chl_other', $array_value))
                        $chl_other = array_diff($array_value, $field_options['radio_option_value']);
                    ?>
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" value="<?php echo esc_attr($radio); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" type="checkbox" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" onClick="pm_show_hide(this, 'pm_otherbox')" <?php echo esc_attr($checked); ?> id="<?php echo esc_attr($row->field_key.'_other'); ?>"><label for="<?php echo esc_attr($row->field_key.'_other'); ?>"> <?php echo 'Other'; ?></label></div>
                                <?php
                                if (isset($chl_other) && !empty($chl_other)) :
                                    $other = implode(',', $chl_other);
                                    ?>
                                    <div class="pm-radio-option pm_otherbox">
                                        <input type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" value="<?php echo esc_attr($other); ?>">
                                    </div>
                                <?php else: ?>


                                    <div id="pm_otherbox" class="pm-radio-option pm_otherbox" style="display:none;">
                                        <input type="text" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" value="">
                                    </div>
                    <?php endif; ?>



                    <?php
                    continue;
                }
                ?>
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" type="checkbox" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" value="<?php echo esc_attr($radio); ?>" <?php echo esc_attr($checked); ?> id="<?php echo esc_attr($row->field_key.'_'.str_replace(' ', '_', strtolower($radio))); ?>"><label for="<?php echo esc_attr($row->field_key.'_'.str_replace(' ', '_', strtolower($radio))); ?>"><?php echo esc_attr($radio); ?></label></div>
                <?php
            }
        }
        ?>
                </div>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
                    <?php
                }

                public function pm_get_custom_form_field_textarea($row, $value = '', $textdomain = '') {
                    if ($row->field_options != "")
                        $field_options = maybe_unserialize($row->field_options);
                    if($value == '' && !empty($field_options) && isset($field_options['default_value']))
                        $value = $field_options['default_value'];
                    ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_textarearequired'; ?>">
                <textarea title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" cols="<?php if (!empty($field_options)) echo esc_attr($field_options['columns']); ?>" rows="<?php if (!empty($field_options)) echo esc_attr($field_options['rows']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>"><?php echo wp_kses_post($value); ?></textarea>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_DatePicker($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        $min_dob = '';
        $max_dob = '';
        if ( ! empty( $field_options ) && ! empty( $field_options['set_dob_range'] ) ) {
            if ( ! empty( $field_options['min_dob'] ) ) {
                $min_dob = $field_options['min_dob'];
            }
            if ( ! empty( $field_options['max_dob'] ) ) {
                $max_dob = $field_options['max_dob'];
            }
        }
        $min_attr = ( $min_dob !== '' ) ? ' data-min_date="' . esc_attr( $min_dob ) . '"' : '';
        $max_attr = ( $max_dob !== '' ) ? ' data-max_date="' . esc_attr( $max_dob ) . '"' : '';
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_datepicker <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="text"<?php echo $min_attr . $max_attr; ?> title="<?php echo esc_attr($row->field_desc); ?>" class="pm_calendar <?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_email($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_email <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="email" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_number($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_number <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_country($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_select_required'; ?>">
                <select title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>">
        <?php include 'country_option_list.php'; ?>
                </select>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_timezone($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_select_required'; ?>">
                <select title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>">
                    <?php include 'time_zone_option_list.php'; ?>
                </select>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_term_checkbox($row, $value = '', $textdomain = '') {
        $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        
        ?>        

        <div class="pm-col">
            <div class="pm-field-lable">
                 <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_checkboxrequired'; ?>">
                <div class="pmradio">
                    <div class="pm-radio-option"><input type="checkbox" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" value="yes" <?php checked($value, 'yes') ?> />
                        <?php 
                        if(isset($field_options['term_and_condition_label']) && !empty($field_options['term_and_condition_label'])){
                            $label = esc_attr($field_options['term_and_condition_label']);
                        }else{
                            $label = esc_attr($row->field_name); 
                        }
                        if(isset($field_options['term_and_condition_url']) && !empty($field_options['term_and_condition_url'])){
                        ?>
                        <a href="<?php echo esc_url($field_options['term_and_condition_url']);?>" target="_blank"><?php echo wp_kses_post($label);?></a>
                        <?php
                        }else{
                            echo wp_kses_post($label);
                        }
                        ?>
                    </div>
                </div>
                <?php if(!empty($field_options['term_and_condition'])){?>
                <textarea disabled rows="4" class="termandcondition"><?php echo wp_kses_post($field_options['term_and_condition']); ?></textarea>
                <?php } ?>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_file($row, $value = '', $textdomain = '') {
        $dbhandler = new PM_DBhandler;
        $pmrequests = new PM_request;
        if ($value != '')
            $row->is_required = 0;
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_fileinput <?php if ($row->is_required == 1) echo 'pm_repeat_required'; ?>">
                <?php if($value!= '') echo wp_kses_post( $pmrequests->profile_magic_edit_user_attachment($value,$row->field_key) ); ?>
                <div class="pm_repeat">
                    <input title="<?php echo esc_attr($row->field_desc); ?>" type="file" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" data-filter-placeholder="<?php if (!empty($field_options)) echo esc_attr(trim($field_options['allowed_file_types'])); ?>" />
                    <?php if ($dbhandler->get_global_option_value('pm_allow_multiple_attachments') == 1): ?>
                        <a class="add"><span onClick="pm_add_repeat(this)"><?php esc_html_e('Add','profilegrid-user-profiles-groups-and-communities'); ?></span></a>
                        <a class="removebutton"><span class="remove" onClick="pm_remove_repeat(this)"><?php esc_html_e('Remove','profilegrid-user-profiles-groups-and-communities'); ?></span></a>
                    <?php endif; ?>
                    <div class="errortext" style="display:none;"></div>
                </div>

            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_user_avatar($row, $value = '', $textdomain = '') {
        $dbhandler = new PM_DBhandler;
        $pg_profile_image_max_file_size= $dbhandler->get_global_option_value('pg_profile_image_max_file_size','');
        $pg_profile_photo_minimum_width = $dbhandler->get_global_option_value('pg_profile_photo_minimum_width','DEFAULT');
        if($pg_profile_photo_minimum_width=='DEFAULT')$pg_profile_photo_minimum_width = 150;
        if($pg_profile_image_max_file_size=='')
        {
            // translators: 1: min width, 2: min height.
            $message = sprintf( __( 'Size Restrictions: Please make sure the image size is equal to or larger than %1$d by %2$d pixels.','profilegrid-user-profiles-groups-and-communities' ),$pg_profile_photo_minimum_width ,$pg_profile_photo_minimum_width);
        }
        else
        {
            // translators: 1: min width, 2: min height, 3: max bytes.
            $message = sprintf( __( 'Size Restrictions: Please make sure the image size is equal to or larger than %1$d by %2$d pixels and does not exceeds total size of %3$d bytes.','profilegrid-user-profiles-groups-and-communities' ),$pg_profile_photo_minimum_width ,$pg_profile_photo_minimum_width,$pg_profile_image_max_file_size);
        }
        if ($value != '')
            $row->is_required = 0;
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                 <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_fileinput <?php if ($row->is_required == 1) echo 'pm_repeat_required'; ?>">
                <div class="pm_repeat">
                    <input title="<?php echo esc_attr($row->field_desc); ?>" type="file" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" data-filter-placeholder="<?php if (!empty($field_options)) echo esc_attr(trim($field_options['allowed_file_types'])); ?>" />
                    <div class="pm-field-note"><?php echo wp_kses_post($message);?></div>
                    <div class="errortext" style="display:none;"></div>
                </div>

            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_repeatable_text($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        if (empty($value))
            $value = '';
        if (is_array($value))
            $values = $value;
        else
            $values = explode(',', $value);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_repeat_required'; ?>">
                <?php foreach ($values as $val): ?>
                    <div class="pm_repeat">
                        <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($val); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                        <a class="add"><span onClick="pm_add_repeat(this)"><?php esc_html_e('Add','profilegrid-user-profiles-groups-and-communities'); ?></span></a><a class="removebutton"><span class="remove" onClick="pm_remove_repeat(this)"><?php esc_html_e('Remove','profilegrid-user-profiles-groups-and-communities'); ?></span></a>
                        <div class="errortext" style="display:none;"></div>
                    </div>
        <?php endforeach; ?>

            </div>
        </div>

        <?php
    }
    
    public function pm_get_custom_form_field_user_invitation($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        if (empty($value))
            $value = '';
        if (is_array($value))
            $values = $value;
        else
            $values = explode(',', $value);
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_repeat_required'; ?>">
                <?php foreach ($values as $val): ?>
                    <div class="pm_repeat">
                        <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($val); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key) . '[]'; ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                        <a class="add"><span onClick="pm_add_repeat(this)"><?php esc_html_e('Add','profilegrid-user-profiles-groups-and-communities'); ?></span></a><a class="removebutton"><span class="remove" onClick="pm_remove_repeat(this)"><?php esc_html_e('Remove','profilegrid-user-profiles-groups-and-communities'); ?></span></a>
                        <div class="errortext" style="display:none;"></div>
                    </div>
        <?php endforeach; ?>

            </div>
        </div>

        <?php
    }

    public function pm_get_custom_form_field_pricing($row, $value = '', $textdomain = '') {
        $dbhandler = new PM_DBhandler;
        $pmrequests = new PM_request;
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['price'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <?php if ($dbhandler->get_global_option_value('pm_currency_position', 'before') == 'before'): ?>
            <?php echo wp_kses_post($pmrequests->pm_get_currency_symbol()); ?>
        <?php endif; ?>
        <?php echo wp_kses_post($value); ?>
        <?php if ($dbhandler->get_global_option_value('pm_currency_position', 'before') == 'after'): ?>
            <?php echo wp_kses_post($pmrequests->pm_get_currency_symbol()); ?>
        <?php endif; ?>
                <input type="hidden" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($row->field_key); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>

        <?php
    }

    //new custom fields sarthak
    public function pm_get_custom_form_field_mobile_number($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_mobile_number <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_phone_number($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                 <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_phone_number <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    public function pm_get_custom_form_field_gender($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        


        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_radiorequired'; ?>">
                <div class="pmradio pm_gender">
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" type="radio" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" value="Male" <?php if ($value == 'Male') echo 'checked'; ?>> <?php esc_html_e('Male', 'profilegrid-user-profiles-groups-and-communities') ?></div>
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" type="radio" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" value="Female" <?php if ($value == 'Female') echo 'checked'; ?>> <?php esc_html_e('Female', 'profilegrid-user-profiles-groups-and-communities') ?></div>
                    <div class="pm-radio-option"><input title="<?php echo esc_attr($row->field_desc); ?>" type="radio" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" value="Others" <?php if ($value == 'Others') echo 'checked'; ?>> <?php esc_html_e('Others', 'profilegrid-user-profiles-groups-and-communities') ?></div>
                </div>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }

    
    public function pm_get_custom_form_field_language($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        


        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
           <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_select_required'; ?>">
                <select title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>">
                  <?php include 'language_option_list.php'; ?>
                </select>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
    
      public function pm_get_custom_form_field_birth_date($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_datepicker <?php if ($row->is_required == 1) echo 'pm_required'; ?>">              
                <input type="text" title="<?php echo esc_attr($row->field_desc); ?>" class="pm_calendar <?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
      public function pm_get_custom_form_field_divider($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
        <div class="pm-col pm-col-divider">
            <hr title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>">
        </div>
        <?php
    }
    
    public function pm_get_custom_form_field_spacing($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
            <div class="pm-col pm-col-spacer">
            </div>
        <?php
    }
    
    public function pm_get_custom_form_field_multi_dropdown($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
           if (is_array($value)) {
            $array_value = $value;
        } else {
            $array_value = explode(',', $value);
        }
        ?>        
        <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input <?php if ($row->is_required == 1) echo 'pm_select_required'; ?>">
                <select multiple title="<?php echo esc_attr($row->field_desc); ?>" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>[]">
                    <?php
                    if (!empty($field_options)) {
                        if ($field_options['first_option'] != '') {
                            echo '<option value="">' . esc_html($field_options['first_option']) . '</option>';
                        }
                        $arr = explode(',', $field_options['dropdown_option_value']);
                        foreach ($arr as $ar) {
                             $selected = '';
                            if (!empty($array_value) && is_array($array_value))
                            {
                         if (in_array($ar, $array_value) == true) {
                            $selected = 'selected';
                        }
                        }
                            
                            ?>
                    <option value="<?php echo esc_attr($ar); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($ar); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
                    
                    
    }
    
   
      public function pm_get_custom_form_field_facebook($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_facebook_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>


   
        <?php
    }
    
     public function pm_get_custom_form_field_twitter($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_twitter_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
     public function pm_get_custom_form_field_google($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_google_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
         public function pm_get_custom_form_field_linked_in($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_linked_in_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
         public function pm_get_custom_form_field_youtube($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                  <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_youtube_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
    
    public function pm_get_custom_form_field_mixcloud($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                 <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_mixcloud_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
     public function pm_get_custom_form_field_soundcloud($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_soundcloud_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
        
         public function pm_get_custom_form_field_instagram($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_instagram_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>
        <?php
    }
    
    
      public function pm_get_custom_form_field_map($row, $value = '', $textdomain = '') {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        if($value == '' && !empty($field_options) && isset($field_options['default_value']))
            $value = $field_options['default_value'];
        ?>        
      <div class="pm-col">
            <div class="pm-field-lable">
                 <span class="pm-form-field-icon"><?php echo wp_get_attachment_image($row->field_icon, array(16, 16), false, false); ?></span>
                <label for="<?php echo esc_attr($row->field_key); ?>"><?php echo esc_attr($row->field_name); ?><?php if ($row->is_required == 1): ?><sup class="pm_estric">*</sup><?php endif; ?></label>
            </div>
            <div class="pm-field-input pm_instagram_url  <?php if ($row->is_required == 1) echo 'pm_required'; ?>">
                <input title="<?php echo esc_attr($row->field_desc); ?>" type="url" class="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" maxlength="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($row->field_key); ?>" name="<?php echo esc_attr($row->field_key); ?>" placeholder="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>">
                <div class="errortext" style="display:none;"></div>
            </div>
        </div>


 <div id="locationField">
      <input id="autocomplete" placeholder="<?php esc_attr_e('Enter your address','profilegrid-user-profiles-groups-and-communities');?>"
             onFocus="" type="text"></input>
    </div>
<div>
     <input id="autocomplete" placeholder="<?php esc_attr_e('Enter your address','profilegrid-user-profiles-groups-and-communities');?>" onFocus="" type="text"></input>
     <div>
         <input class="field" id="street_number" placeholder="<?php esc_attr_e('street','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>
         <input class="field" id="route" placeholder="<?php esc_attr_e('route','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>
         <input class="field" id="locality" placeholder="<?php esc_attr_e('city','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>
         <input class="field" id="administrative_area_level_1" placeholder="<?php esc_attr_e('state','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>
          <input class="field" id="postal_code" placeholder="<?php esc_attr_e('zip code','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>
          <input class="field" id="country" placeholder="<?php esc_attr_e('Country','profilegrid-user-profiles-groups-and-communities');?>" disabled="true"></input>     
     </div>
</div>


        <?php
    }
     
}
