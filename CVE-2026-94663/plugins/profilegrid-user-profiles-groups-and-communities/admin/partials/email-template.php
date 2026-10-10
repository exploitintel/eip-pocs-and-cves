<?php
$dbhandler    = new PM_DBhandler();
$pm_activator = new Profile_Magic_Activator();
$pmrequests   = new PM_request();
$textdomain   = $this->profile_magic;
$path         =  plugin_dir_url( __FILE__ );
$identifier   = 'EMAIL_TMPL';
$id           = filter_input( INPUT_GET, 'id' );
if ( $id==false || $id==null ) {
    $id =0;
} else {
    $row = $dbhandler->get_row( $identifier, $id );
}
if ( filter_input( INPUT_POST, 'submit_tmpl' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_pm_add_email_tmpl' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$tmpl_id = filter_input( INPUT_POST, 'tmpl_id' );
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_tmpl', 'tmpl_id', 'pm_field_list' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		foreach ( $post as $key=>$value ) {
			$data[ $key ] = $value;
			$arg[]        = $pm_activator->get_db_table_field_type( $identifier, $key );
		}
	}
	if ( $tmpl_id==0 ) {
	    $dbhandler->insert_row( $identifier, $data, $arg );
	} else {
		$dbhandler->update_row( $identifier, 'id', $tmpl_id, $data, $arg, '%d' );
	}

	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_email_templates' ) );
	exit;
}
?>
<div class="uimagic">
  <form name="pm_add_email_template" id="pm_add_email_template" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <?php if ( $id==0 ) : ?>
      <div class="uimheader">
			<?php esc_html_e( 'New Template', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php else : ?>
      <div class="uimheader">
		  <?php esc_html_e( 'Edit Template', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
      <?php endif; ?>
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Title', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          <sup>*</sup></div>
        <div class="uiminput pm_required">
          <input type="text" name="tmpl_name" id="tmpl_name" value="<?php
            if ( !empty( $row ) ) {
                    echo esc_attr( $row->tmpl_name );}
            ?>" />
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Define a name for this template. Front-end user never sees this. Only for your reference.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Email Subject', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="email_subject" id="email_subject" value="<?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->email_subject );}
			?>" />
          <div class="errortext" id="icon_error"></div>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Subject of the email sent to the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Email Template', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput pg-add-email-template-body">
          <?php
			if ( !empty( $row ) ) {
				$email_body =  $row->email_body;
			} else {
				$email_body ='';}
			$settings = array(
				'wpautop'           => false,
				'media_buttons'     => true,
				'textarea_name'     => 'email_body',
				'textarea_rows'     => 20,
				'tabindex'          => '',
				'tabfocus_elements' => ':prev,:next',
				'editor_css'        => '',
				'editor_class'      => '',
				'teeny'             => false,
				'dfw'               => false,
				'tinymce'           => true, // <-----
				'quicktags'         => true,
			);

			add_action( 'media_buttons', array( $this, 'pm_fields_list_for_email' ) );

			wp_editor( $email_body, 'email_body', $settings );
			?>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php echo wp_kses_post( __( 'Content of the email sent to the user. You can add profile field values using <i>Select A Field</i> dropdown. <hr/><strong>Important</strong> - if you do not have Password field in your Group sign up form, add Password field to the email template you plan to assign to user activation event. This will make sure that new users receive an auto-generated password inside their account activation email.', 'profilegrid-user-profiles-groups-and-communities' ) ); ?></div>
      </div>
      
      
      
     
      <div class="buttonarea"> <a href="admin.php?page=pm_email_templates">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
          <input type="hidden" name="tmpl_id" id="tmpl_id" value="<?php echo esc_attr( $id ); ?>" />
        <?php wp_nonce_field( 'save_pm_add_email_tmpl' ); ?>
        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_tmpl" id="submit_tmpl" onClick="return add_group_validation()"  />
        <div class="all_error_text" style="display:none;"></div>
        <div class="user_name_error" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
