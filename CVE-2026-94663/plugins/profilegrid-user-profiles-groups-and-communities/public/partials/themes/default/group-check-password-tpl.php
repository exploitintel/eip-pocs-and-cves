<?php $pmrequests = new PM_request;?>
<div class="pmagic">  
 <div class="pm-login-box pm-dbfl pm-border pm-radius5"> 
 <?php if(isset($pm_error) && $pm_error!='' && !is_user_logged_in()):?>
 <div class="pm-login-box-error pm-dbfl pm-pad10 pm-border-bt"><?php echo wp_kses_post($pm_error);?></div>
 <?php endif;?>
 
<!-----Form Starts----->
  <form class="pmagic-form pm-dbfl pm-bg-lt" method="post" action="" id="pm_check_group_password_form" name="pm_check_group_password_form">
  <?php
    $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
    $basic_function->pm_render_nonce_field( 'pm_check_group_password_form' );
    ?>
            <input type="password" name="<?php echo esc_attr('pm_group_password');?>" id="<?php echo esc_attr('pm_group_password');?>" placeholder="<?php esc_attr_e('Password','profilegrid-user-profiles-groups-and-communities');?>" required="required">
            <div class="pm-login-box-bottom-container pm-dbfl pm-bg pm-border">
                <input type="submit" value="<?php esc_attr_e('View Group','profilegrid-user-profiles-groups-and-communities');?>" name="group_password_form_submit" class="pm-difl">
               
            </div>
            
  </form>
   </div>
</div>
