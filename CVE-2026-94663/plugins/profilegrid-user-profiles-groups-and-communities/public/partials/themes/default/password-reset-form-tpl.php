<?php
global $wpdb;

$path       = plugin_dir_url( __FILE__ );
$pmrequests = new PM_request;

// Create object to render nonce field (no standalone function calls)
$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );

if ( function_exists( 'is_wpe' ) ) :
	$reset_pwd_url = '/wp-login.php?action=resetpass&wpe-login=true';
else :
	$reset_pwd_url = '/wp-login.php?action=resetpass';
endif;
?>
<div class="pmagic">
	<div class="pm-login-box pm-dbfl pm-border">
		<?php if ( isset( $pm_error ) && $pm_error != '' && ! is_user_logged_in() ) : ?>
			<div class="pm-login-box-error"><?php echo wp_kses_post( $pm_error ); ?></div>
		<?php endif; ?>

		<?php
		if ( is_user_logged_in() ) :
			$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
			?>
			<div class="pm-login-header pm-dbfl pm-bg pm-border-bt">
				<h4><?php esc_html_e( "You can reset your password by accessing Change Password tab in your profile's Settings section.", 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
				<p><?php esc_html_e( 'PROCEED TO', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
			</div>
			<div class="pm-login-header-buttons pm-dbfl pm-pad10">
				<div class="pm-center-button pm-difl pm-pad10">
					<a href="<?php echo esc_url( $redirect_url ); ?>" class="pm_button">
						<?php esc_html_e( 'My Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?>
					</a>
				</div>
				<div class="pm-center-button pm-difl pm-pad10">
					<a href="<?php echo esc_url( wp_logout_url( $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', '' ) ) ); ?>" class="pm_button">
						<?php esc_html_e( 'Logout', 'profilegrid-user-profiles-groups-and-communities' ); ?>
					</a>
				</div>
			</div>
		<?php
		else :
			?>
			<form name="resetpassform" id="resetpassform" action="<?php echo esc_url( site_url( $reset_pwd_url ) ); ?>" method="post" autocomplete="off">

				<?php $basic_function->pm_render_nonce_field( 'pm_password_action', 'pm_password_nonce' ); ?>

				<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
				<input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />
				<input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" placeholder="<?php esc_attr_e( 'New password', 'profilegrid-user-profiles-groups-and-communities' ); ?>" required="required" />
				<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" placeholder="<?php esc_attr_e( 'Repeat new password', 'profilegrid-user-profiles-groups-and-communities' ); ?>" required="required" />
				<div class="pm-login-box-bottom-container">
					<input type="submit" name="submit" id="resetpass-button" class="button" value="<?php esc_attr_e( 'Reset Password', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
				</div>
			</form>

		<?php endif; ?>
	</div>
</div>
