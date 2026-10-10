<?php $pmrequests = new PM_request;?>
<?php 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Verify the nonce.
    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'pg-logout')) {
        die(esc_html__('Security check failed','profilegrid-user-profiles-groups-and-communities'));
    }
 
    // Nonce verification passed. Perform logout actions.
    wp_logout();
 
    // Redirect or display a message after logout.
    wp_safe_redirect(home_url()); // You can change the URL to your desired redirect location.
    exit;
}
?>
<div class="pmagic">  
 <div class="pm-login-box pm-dbfl pg-theme-bg pm-border pm-radius5"> 
 <?php if(isset($pm_error) && $pm_error!='' && !is_user_logged_in()):?>
 <div class="pm-login-box-error pm-dbfl pm-pad10 pm-border-bt"><?php echo wp_kses_post($pm_error);?></div>
 <?php endif;?>
<?php 
if ( is_user_logged_in()) : ?>
	<?php
			$redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_profile_page','');
	?> 
	  <div class="pm-login-header pm-dbfl pm-bg pm-pad10 pm-border-bt">
		  <h3><?php esc_html_e( 'You have successfully logged in.','profilegrid-user-profiles-groups-and-communities' );?></h3>
		  <p><?php esc_html_e('PROCEED TO','profilegrid-user-profiles-groups-and-communities');?></p>
	  </div>
	   <div class="pm-login-header-buttons pm-dbfl pm-pad10">
		   <div class="pm-center-button pm-difl pm-pad10"><a href="<?php echo esc_url($redirect_url);?>" class="pm_button"><?php esc_html_e('My Profile','profilegrid-user-profiles-groups-and-communities');?></a></div>
                   <div class="pm-center-button pm-difl pm-pad10"><a href="<?php echo esc_url(get_permalink()); ?>/?action=logout&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'pg-logout' ) ); ?>" class="pm_button"><?php esc_html_e('Logout','profilegrid-user-profiles-groups-and-communities');?></a></div>
	   </div>
	 <?php
else:
?>
		
 
<!-----Form Starts----->
  <form class="pmagic-form 1 pm-dbfl pm-bg-lt" method="post" action="" id="pm_login_form" name="pm_login_form">
  <?php
  $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
  $basic_function->pm_render_nonce_field( 'pm_login_form' ); 
   ?>
            <input type="text" name="<?php echo esc_attr('user_login');?>" id="<?php echo esc_attr('user_login');?>" placeholder="<?php esc_attr_e('Email or Username','profilegrid-user-profiles-groups-and-communities');?>" required="required">
            <input type="password" name="<?php echo esc_attr('user_pass');?>" id="<?php echo esc_attr('user_pass');?>" placeholder="<?php esc_attr_e('Password','profilegrid-user-profiles-groups-and-communities');?>" required="required">
            <span id="pg-toggle-password" class="pg-toggle-password fa fa-fw fa-eye-slash"></span>
            <div class="pg-login-remember">
                <label>
                    <input name="<?php echo esc_attr('rememberme');?>" type="checkbox" id="pg-rememberme" value="1" />
                    <?php esc_html_e('Remember Me','profilegrid-user-profiles-groups-and-communities');?>
                </label>
            </div>
            <?php do_action('profile_magic_show_captcha_in_login');?>
            <div class="pm-login-box-bottom-container pm-dbfl pm-bg pm-border">
                <input type="submit" value="<?php esc_attr_e('Login','profilegrid-user-profiles-groups-and-communities');?>" name="login_form_submit" class="pm-difl">
                <?php if($register_link):?>
                <a href="<?php echo esc_url($registration_url);?>" class="pm-difl pg-registration-button"><?php esc_html_e('Register','profilegrid-user-profiles-groups-and-communities');?> </a> 
                <?php endif; ?>
                <div class="pm-login-links-box pm-difr pm-pad10">
                <a href="<?php echo esc_url($forget_password_url);?>"><?php esc_html_e('Forgot Password?','profilegrid-user-profiles-groups-and-communities');?></a>
                </div>
            </div>
            
  </form>
  <?php endif;?>
   </div>
</div>
