<?php
global $wpdb;
$path       = plugin_dir_url(__FILE__);
$pmrequests = new PM_request;

// Create object to render nonce field (required change)
$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );

if ( function_exists('is_wpe') ):
    $forgot_pwd_url = "/wp-login.php?action=lostpassword&wpe-login=true";
else:
    $forgot_pwd_url = "/wp-login.php?action=lostpassword";
endif;

?>
<div class="pmagic">  
 <div class="pm-login-box pm-dbfl pm-border pm-radius5"> 

 <?php if ( isset($pm_error) && $pm_error!='' && !is_user_logged_in() ): ?>
     <div class="pm-login-box-error pm-dbfl pm-pad10 pm-border-bt">
         <?php echo wp_kses_post($pm_error); ?>
     </div>
 <?php endif; ?>

 <?php 
 if ( is_user_logged_in() ) : ?>
    <?php
        $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page','');
    ?> 
      <div class="pm-login-header pm-dbfl pm-bg pm-border-bt pm-pad10">
          <h4><?php esc_html_e( 
              "You can reset your password by accessing Change Password tab in your profile's Settings section.",
              'profilegrid-user-profiles-groups-and-communities' 
          ); ?></h4>
          <p><?php esc_html_e('PROCEED TO','profilegrid-user-profiles-groups-and-communities');?></p>
      </div>

      <div class="pm-login-header-buttons pm-dbfl pm-pad10">
           <div class="pm-center-button pm-difl pm-pad10">
               <a href="<?php echo esc_url($redirect_url);?>" class="pm_button">
                   <?php esc_html_e('My Profile','profilegrid-user-profiles-groups-and-communities'); ?>
               </a>
           </div>
           <div class="pm-center-button pm-difl pm-pad10">
               <a href="<?php echo esc_url( wp_logout_url( 
                   $pmrequests->profile_magic_get_frontend_url('pm_user_login_page','') 
               ) ); ?>" class="pm_button">
                   <?php esc_html_e('Logout','profilegrid-user-profiles-groups-and-communities'); ?>
               </a>
           </div>
      </div>

 <?php else: ?>

 <form id="lostpasswordform" class="pmagic-form pm-dbfl pm-bg-lt"
       action="<?php echo esc_url( site_url($forgot_pwd_url) ); ?>" method="post">

     <?php $basic_function->pm_render_nonce_field( 'pm_password_action', 'pm_password_nonce' ); ?>

     <div class="pm-login-header pm-dbfl pm-bg pm-border-bt pm-pad10">
         <?php esc_html_e(
             'If you have lost your password, we can help you reset it. To initiate password reset process, please enter your username or email in the box below and click on the button.',
             'profilegrid-user-profiles-groups-and-communities'
         ); ?>
     </div>

     <input type="text" 
            name="<?php echo esc_attr('user_login');?>" 
            id="user_login" 
            placeholder="<?php esc_attr_e('Email or Username','profilegrid-user-profiles-groups-and-communities');?>">

     <div class="pm-login-box-bottom-container pm-dbfl pm-bg pm-border">
         <input type="submit" name="submit" class="lostpassword-button"
                value="<?php esc_attr_e( 'Reset Password','profilegrid-user-profiles-groups-and-communities' ); ?>"/>
     </div>

 </form>

 <?php endif; ?>

 </div>
</div>
