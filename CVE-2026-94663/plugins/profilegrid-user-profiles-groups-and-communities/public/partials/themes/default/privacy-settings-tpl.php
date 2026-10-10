<?php
$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
?>
<div class="pmagic">   
<!-----Form Starts----->
<form class="pmagic-form pm-dbfl pg-group-reg-form" name="pm_privacy_form" id="pm_privacy_form" method="post">
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pm_profile_privacy"><?php esc_html_e('Profile Privacy','profilegrid-user-profiles-groups-and-communities');?></label>
            </div>
            <div class="pm-field-input pm_required">
                <select name="pm_profile_privacy" id="pm_profile_privacy">
                    <option value="1" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),1);?>><?php esc_html_e('Everyone','profilegrid-user-profiles-groups-and-communities');?></option>
                    <option value="2" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),2);?>><?php esc_html_e('Friends','profilegrid-user-profiles-groups-and-communities');?></option>
                    <option value="3" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),3);?>><?php esc_html_e('Group Members','profilegrid-user-profiles-groups-and-communities');?></option>
                    <option value="4" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),4);?>><?php esc_html_e('Friends & Group Members','profilegrid-user-profiles-groups-and-communities');?></option>
                    <option value="5" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),5);?>><?php esc_html_e('Only Me','profilegrid-user-profiles-groups-and-communities');?></option>
                </select>
            </div>
        </div>
    </div>
    <?php if($dbhandler->get_global_option_value('pm_allow_user_to_hide_their_profile','0')==1):?>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pm_hide_my_profile_0"><?php esc_html_e('Hide My Profile From Groups, Directories and Search Results','profilegrid-user-profiles-groups-and-communities');?></label>
            </div>
            <div class="pm-field-input pm_required">
                <div class="pmradio">
                   <div class="pm-radio-option">
                       <input type="radio" class="pg-hide-privacy-profile" id="pm_hide_my_profile_0" name="pm_hide_my_profile" value="0" <?php if($pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile')==0 || $pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile')=='')echo 'checked';?>> 
                       <label class="pg-hide-my-profile" for="pm_hide_my_profile_0"><?php  esc_html_e('No','profilegrid-user-profiles-groups-and-communities'); ?></label>
                   </div>
                    <div class="pm-radio-option">
                       <input type="radio" class="pg-hide-privacy-profile" id="pm_hide_my_profile_1" name="pm_hide_my_profile" value="1" <?php checked($pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile'),1);?>> 
                       <label class="pg-hide-my-profile" for="pm_hide_my_profile_1"> <?php esc_html_e('Yes','profilegrid-user-profiles-groups-and-communities'); ?></label>
                   </div>
                </div>
            </div>
        </div>
    </div>  
    <?php endif;?>
    <div class="buttonarea pm-full-width-container">
        <div id="pm_reset_passerror" style="display:none;"></div>
        <input type="hidden" name="user_id" value="<?php echo esc_attr($uid); ?>" />
        <?php $basic_function->pm_render_nonce_field( 'pm_privacy_settings_form' ); ?>
        <input type="submit" value="<?php esc_attr_e('Submit','profilegrid-user-profiles-groups-and-communities');?>" name="pg_privacy_submit">
    </div>
  </form>
</div>
