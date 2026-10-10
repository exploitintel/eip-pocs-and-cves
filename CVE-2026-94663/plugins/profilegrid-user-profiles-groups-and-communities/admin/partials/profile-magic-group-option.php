<?php
$textdomain = 'profilegrid-user-profiles-groups-and-communities';
$path       =  plugin_dir_url( __FILE__ );
$pmrequests = new PM_request();
$dbhandler  = new PM_DBhandler();
$rm_url     = $pmrequests->pg_get_rm_installation_plugin_url();
$pg_url     = 'https://profilegrid.co/profilegrid-registrationmagic-integration/';
?>
<div class="uimrow" id="paidgroup">
  <div class="uimfield">
    <?php esc_html_e( 'Membership Charge', 'profilegrid-user-profiles-groups-and-communities' ); ?>
  </div>
  <div class="uiminput">
    <input name="group_options[is_paid_group]" id="is_paid_group" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'paidgrouphtml')" 
    <?php
    if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
		echo 'checked';}
	?>
    />
    <label for="is_paid_group"></label>
  </div>
  <div class="uimnote"><?php esc_html_e( 'Charge registering users a one time fee for joining this group. Only works if you have selected Default option in Group Registration Form below. For custom registration forms, you can add more complex payment plans in respective form’s Dashboard.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
</div>
<div class="childfieldsrow" id="paidgrouphtml" style=" 
<?php
if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
	echo 'display:block;';
} else {
	echo 'display:none;';}
?>
">
  <div class="uimrow">
    <div class="uimfield">
      <?php esc_html_e( 'Group Membership Price', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div>
    <div class="uiminput 
    <?php
    if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
		echo 'pm_required';}
	?>
    ">
        <input type="number" name="group_options[group_price]" id="group_price" value="<?php
        if ( !empty( $group_options ) && isset( $group_options['group_price'] ) ) {
			echo esc_attr( $group_options['group_price'] );}
		?>" />
      <div class="errortext"></div>
    </div>
    <div class="uimnote"><?php esc_html_e( 'While signing up, users will be charged this amount for successful registration. User account will be automatically activated after successful payment. Make sure you have properly configured payment settings for this to work.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
  </div>
</div>

<?php if ( class_exists( 'Registration_Magic' ) ) : ?>
<div class="uimrow">
  <div class="uimfield">
    <?php esc_html_e( 'Group Registration Form', 'profilegrid-user-profiles-groups-and-communities' ); ?>
  </div>
  <div class="uiminput">
      <select name="group_options[pg_rm_form]" id="pg_rm_form">
          <option value="0"><?php esc_html_e( 'ProfileGrid Default Group Form', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <?php
			if ( !empty( $group_options ) && isset( $group_options['pg_rm_form'] ) ) {
				$selected = $group_options['pg_rm_form'];
			} else {
				$selected = 0;
			}
			$pmrequests->pm_get_all_rm_registration_form_dropdown_list( $selected );
            ?>
      </select>
       
  </div>
  <div class="uimnote"><?php esc_html_e( "Select a user registration form for this group. ProfileGrid Default Group Form option will automatically create a form using profile fields in this group. Other form options are fetched from RegistrationMagic. For more information ", 'profilegrid-user-profiles-groups-and-communities' );?> <a href='<?php esc_url( $pg_url );?>' target='_blank'><?php esc_html_e('CLICK HERE','profilegrid-user-profiles-groups-and-communities');?></a></div>
</div>

<?php endif; ?>
