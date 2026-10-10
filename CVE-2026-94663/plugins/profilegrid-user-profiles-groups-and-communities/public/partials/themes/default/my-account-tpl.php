
<div class="pmagic">   
<!-----Form Starts----->
  <div class="pm-group-view pm-dbfl">
<form class="pmagic-form pm-dbfl pg-group-reg-form" method="post" action="" id="pm_my_account" name="pm_my_account" onsubmit="return pm_save_account_setting(this);">
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="user_name"><?php esc_html_e('Username','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input disabled="disabled" autocomplete="off" type="text" class="" value="<?php echo esc_attr($pmrequests->profile_magic_get_user_field_value($uid,'user_login'));?>" id="user_name" name="user_name" >
            </div>
        </div>
    </div>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="first_name"><?php esc_html_e('First Name','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="text" class="" value="<?php echo esc_attr($pmrequests->profile_magic_get_user_field_value($uid,'first_name'));?>" id="first_name" name="first_name" >
            <div class="errortext" style="display:none;"></div>
            </div>
        </div>
    </div>  
      
    <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="last_name"><?php esc_html_e('Last Name','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="text" class="" value="<?php echo esc_attr($pmrequests->profile_magic_get_user_field_value($uid,'last_name'));?>" id="last_name" name="last_name" >
           <div class="errortext" style="display:none;"></div>
            </div>
        </div>
    </div>  
      
    <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="user_email"><?php esc_html_e('Email','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_email pm_user_email pm_required">
                <input title="" type="email" class="" value="<?php echo esc_attr($pmrequests->profile_magic_get_user_field_value($uid,'user_email'));?>" id="user_email" name="user_email" <?php if($dbhandler->get_global_option_value('pm_allow_user_to_change_email',0)==0){echo "disabled='disabled' autocomplete='off'";}?>>
                <div class="errortext" style="display:none;"></div>
                <?php 
                
                if(isset($error)){
                    $account_error = $error;
                    if($error=='email_exists'){
                    $account_error = esc_html__("This email is already registered. Please try with different email.",'profilegrid-user-profiles-groups-and-communities');
                    }
                    if($error == 'no_changes')
                    {
                     $account_error = esc_html__("No changes were made to the account details to be saved.",'profilegrid-user-profiles-groups-and-communities');
                    }
                    if($error =='invalid_password')
                    {
                        $account_error  = '';
                    }
                    
                    ?>
                <div class="useremailerror" style="color:red;"><?php echo wp_kses_post($account_error);?></div>
                <?php } ?>
                
            </div>
        </div>
    </div>
      
    <div class="buttonarea pm-full-width-container">
        <div class="all_errors" style="display:none;"></div>
        <?php 
        $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
            $basic_function->pm_render_nonce_field( 'pm_my_account_settings_form' ); 
        ?>
      <input type="submit" value="<?php esc_attr_e('Submit','profilegrid-user-profiles-groups-and-communities');?>" name="my_account_submit">
    </div>
  </form>
  </div>
</div>
