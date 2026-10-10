<?php
$path          = plugin_dir_url( __FILE__ );
$pmrequests    = new PM_request;
$basicfunctions = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
$html_creator  = new PM_HTML_Creator( $this->profile_magic, $this->version );
$errors        = $basicfunctions->get_error_frontend_message();
$paymentpage   = $pmrequests->profile_magic_check_paid_group( $gid );
$current_user_id = get_current_user_id();
$group_member  = $pmrequests->profile_magic_check_is_group_member( $gid, $current_user_id );
$pm_sanitizer  = new PM_sanitizer;
$get           = $pm_sanitizer->sanitize( $_GET );

if ( isset( $get['profile'] ) ) {
    $value = PM_sanitizer::safe_unserialize_array( $get['profile'] );
    if ( is_user_logged_in() ) {
        if ( ! isset( $value['user_email'] ) || '' === $value['user_email'] ) {
            $userinfo            = get_user_by( 'ID', $current_user_id );
            $value['user_email'] = $userinfo->user_email;
        }
    }
} else {
    $value = '';
    if ( is_user_logged_in() ) {
        $value = $current_user_id;
    }
}
?>

<?php 
if ( is_user_logged_in() && $group_member && ! has_action( 'profilegrid_group_register_onlogin' ) ) : ?>
	<?php
	$redirect_url = $pmrequests->profile_magic_get_frontend_url( 'pm_user_profile_page', '' );
	?> 
    <div class="pmagic"> 
        <div class="pm-login-box pm-dbfl pm-radius5 pm-border"> 
	        <div class="pm-login-header pm-dbfl pm-bg pm-border-bt">
		        <h4><?php esc_html_e( 'You have successfully logged in.', 'profilegrid-user-profiles-groups-and-communities' ); ?></h4>
		        <p><?php esc_html_e( 'PROCEED TO', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
	        </div>
	        <div class="pm-login-header-buttons pm-dbfl pm-pad10">
		        <div class="pm-center-button pm-difl pm-pad10">
                    <a href="<?php echo esc_attr( $redirect_url ); ?>" class="pm_button">
                        <?php esc_html_e( 'My Profile', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </a>
                </div>
		        <div class="pm-center-button pm-difl pm-pad10">
                    <a href="<?php echo esc_attr( wp_logout_url( $pmrequests->profile_magic_get_frontend_url( 'pm_user_login_page', '' ) ) ); ?>" class="pm_button">
                        <?php esc_html_e( 'Logout', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </a>
                </div>
	        </div>
        </div>
    </div>
<?php
else :
?>

<div class="pmagic">   
    <!-----Form Starts----->
    <div class="pg-group-box-wrap pg-group-reg-form pg-group-form-field-icon pm-dbfl">
        <form class="pmagic-form pm-dbfl"
              method="post"
              action=""
              id="pm_regform_<?php echo esc_attr( $gid ); ?>"
              name="pm_regform_<?php echo esc_attr( $gid ); ?>"
              onsubmit="return profile_magic_frontend_validation(this)"
              enctype="multipart/form-data">

            <?php
            // UPDATED: use class object for nonce, with fallback to function
            if ( is_object( $basicfunctions ) && method_exists( $basicfunctions, 'pm_render_nonce_field' ) ) {
                $basicfunctions->pm_render_nonce_field( 'pm_registration_action', 'pm_registration_nonce' );
            } elseif ( function_exists( 'pm_render_nonce_field' ) ) {
                pm_render_nonce_field( 'pm_registration_action', 'pm_registration_nonce' );
            }
            ?>

            <?php
            $html_creator->get_custom_fields_html_singlepage( $gid, $fields, 1, $value );
            ?>

            <div class="buttonarea pm-full-width-container">
                <div class="all_errors" style="display:none;"></div>

                <?php if ( $paymentpage > 0 ) : ?>
                    <input type="hidden" name="action" value="process" />
                    <input type="hidden" name="cmd" value="_cart" /> 
                    <input type="hidden" name="invoice" value="<?php echo esc_attr( gmdate( 'His' ) . wp_rand( 1234, 9632 ) ); ?>" />
                <?php endif; ?>     

                <?php
                /*
                 * for auto fill social registration
                 */
                if ( isset( $get['profile'] ) ) :
                    $value = $get['profile'];
                    ?>
                    <input type="hidden" name="socialaction" value="social" />
                    <?php
                    foreach ( $value as $key => $val ) {
                        if ( $val !== '' ) :
                            ?>
                            <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
                            <?php
                        endif;
                    }
                endif;
                ?>

                <input type="submit"
                       value="<?php esc_attr_e( 'Submit', 'profilegrid-user-profiles-groups-and-communities' ); ?>"
                       name="reg_form_submit">
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
