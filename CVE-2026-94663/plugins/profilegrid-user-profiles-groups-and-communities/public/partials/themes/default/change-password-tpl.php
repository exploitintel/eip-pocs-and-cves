<div class="pmagic">   
<!-----Form Starts----->
<form class="pmagic-form pm-dbfl" name="resetpassform" id="resetpassform" method="post" autocomplete="off" onsubmit="return pm_frontend_change_password(this)">
<?php 
if ( class_exists( 'Profile_Magic_Basic_Functions' ) ) {
    $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
    if ( method_exists( $basic_function, 'pm_render_nonce_field' ) ) {
        $basic_function->pm_render_nonce_field( 'pm_password_action', 'pm_password_nonce' );
    }
}
?>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pass1"><?php esc_html_e('New password','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="password" size="20" value="" autocomplete="off" id="pass1" name="pass1" >
            </div>
        </div>
    </div>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pass2"><?php esc_html_e('Repeat new password','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="password" size="20" value="" autocomplete="off" id="pass2" name="pass2" >
            </div>
        </div>
    </div>  
    
    <div class="buttonarea pm-full-width-container">
        <div id="pm_reset_passerror" style="display:none;"></div>
        <input type="hidden" name="user_id" value="<?php echo esc_attr($uid); ?>" />
        <input type="submit" value="<?php esc_attr_e('Submit','profilegrid-user-profiles-groups-and-communities');?>" name="my_account_submit">
    </div>
  </form>
</div>
