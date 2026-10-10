<?php
$dbhandler = new PM_DBhandler();
$dbhandler->update_global_option_value( 'pg_activation_popup', '0' );
$pmrequests  = new PM_request();
$textdomain  = $this->profile_magic;
$path        =  plugin_dir_url( __FILE__ );
$identifier  = 'GROUPS';
$user        = wp_get_current_user();
$groups      =  $dbhandler->get_all_result( $identifier );
$displayname = $pmrequests->pm_get_display_name( $user->ID );
foreach ( $groups as $group ) {
    $row = $group;
    break;
}
if ( $row->group_options!='' ) {
	$group_options = maybe_unserialize( $row->group_options );
}

?>

<div id="pg-wizard-modal" class="pm-wizard-modal">
   <div class="pg-modal-box-overlay"></div> 
   <div class="pg-wizard-steps-container">
       <ul>
           <li class="pg-wizard-step pg-wizard-step-step-1 pg-wizard-step-passed"><span></span></li>
           <li class="pg-wizard-step pg-pg-wizard-step-2"><span></span></li>
           <li class="pg-wizard-step pg-pg-wizard-step-3"><span></span></li>
           <li class="pg-wizard-step pg-pg-wizard-step-4"><span></span></li>
           <li class="pg-wizard-step pg-pg-wizard-step-5"><span></span></li>
           <li class="pg-wizard-step pg-pg-wizard-step-6"><span></span></li>


       </ul>
   </div>

   <div class="pg-wizard-box-main">
       <div class="pg-wizard-box-slides">
   <div class="pg-wizard-step-box-wrap">

    <div class="pg-wizard-step-header"><span class="pg-steps-header-userinfo"><span class="pg-steps-header-img">
    <?php
    echo get_avatar(
        $user->user_email,
        40,
        '',
        false,
        array(
			'class'         =>'pm-user',
			'force_display' =>true,
        )
    );
	?>
    </span> <span class="pg-steps-header-name"><?php esc_html_e( 'Hello', 'profilegrid-user-profiles-groups-and-communities' ); ?> <strong><?php echo wp_kses_post( $displayname ); ?></strong><span></span></div>

       <div class="pg-wizard-container">
         
           <form name="pm_add_group" class="pg-wizard-steps-panel" id="pm_add_group" method="post"  action="<?php echo esc_url(admin_url( 'admin-ajax.php' )); ?>">
             
            <div class="pg-wzrad-step pg-wizard-step-0">
                
                <div class="pg-wizard-step-head"><?php esc_html_e( 'Thank you for installing ProfileGrid!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                <div class="pg-wizard-step-subhead"><?php esc_html_e( 'ProfileGrid needs to create a default user group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>?</div>
                 
            <div class="pg-wizard-switch-field">
                <input type="radio" id="radio-two" name="pg-switch-two" class="pg-next-slide pg-action-button" value="<?php esc_attr_e( 'Yes, I will create my first user group now', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
		<label for="radio-two"><?php esc_html_e( 'I will setup default user group now.', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                <input type="radio" id="radio-one" name="pg-switch-one" value="<?php esc_attr_e( 'No, I will think about it later', 'profilegrid-user-profiles-groups-and-communities' ); ?>" onclick="window.location.href='admin.php?page=pm_manage_groups'" />
		<label for="radio-one"> <?php esc_html_e( 'I will set it up later.', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                
            </div>
                
                <div class="pg-wizard-step-desc"><?php esc_html_e( 'Default user group allows you to build default user registration form for new users on your website. It also allows you to setup email notifications for user related activities.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 

             </div>
             
             
             <div class="pg-wzrad-step pg-wizard-step-1">
                 <div class="pg-wzard-row">
                     <div class="pg-wzard-field-wrap">
                         <div class="pg-wzrad-field">
                             <?php esc_html_e( 'Group Name', 'profilegrid-user-profiles-groups-and-communities' ); ?><span>  <?php esc_html_e( '(Required)', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                             </div>
                         <div class="pg-wzrad-input pm_required">
                             <input type="text" name="group_name" id="group_name" value="<?php
								if ( !empty( $row ) ) {
									echo esc_attr( $row->group_name );}
								?>" onkeyup="change_wizard_group_name(this.value)" />
                             <div class="errortext"></div>
                         </div>
                     </div>
                     <div class="pg-wzard-field-note"><?php esc_html_e( 'The name will appear on single and All Groups page and User Profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                 </div>

                 <div class="pg-wzard-row">
                     <div class="pg-wzard-field-wrap">
                         <div class="pg-wzrad-field">
                             <?php esc_html_e( 'Group Description:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                             </div>
                         <div class="pg-wzrad-input">
                        <textarea name="group_desc" id="group_desc" onkeyup="change_wizard_group_desc(this.value)">
                        <?php
                        if ( !empty( $row ) ) {
							echo esc_attr( $row->group_desc );}
						?>
                        </textarea>
                             <div class="errortext"></div>
                         </div>
                     </div>
                     <div class="pg-wzard-field-note"> <?php esc_html_e( 'Group description will appear on the individual group page and as intro text on All Groups page.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?><i class="fa fa-external-link" aria-hidden="true"></i></a></div>
                 </div>
                 

                     
                 <div class="pg-wizard-footer">
                    <input type="button" name="previous" class="pg-previous-slide pg-action-button" value="Back" />
                    <input type="button" name="next" class="pg-next-slide pg-action-button" value="Next" /> 
                 </div>
             </div>
             
               <div class="pg-wzrad-step pg-wizard-step-2">
                   <div class="pg-group-type-wrap">
                       <div class="pg-group-type-title"><?php esc_html_e( 'Group Type', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                       <label class="pg-group-type pg-open-group" for="group_type_open">
                           <input checked type="radio" name="group_options[group_type]" id="group_type_open" value="open" 
                           <?php
							if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='open' ) {
								echo 'checked';}
							?>
                             onclick="change_wizard_group_type(this.value)">
                           <div class="pg-group-type-content">
                                   <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM4 12c0-.61.08-1.21.21-1.78L8.99 15v1c0 1.1.9 2 2 2v1.93C7.06 19.43 4 16.07 4 12zm13.89 5.4c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41C17.92 5.77 20 8.65 20 12c0 2.08-.81 3.98-2.11 5.4z"/></svg>                               <div class="pg-group-type-details">
                                   <span><?php esc_html_e( 'Open or Public Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                                   <p><?php esc_html_e( 'Can be joined directly without administrator’s approval.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                               </div>
                           </div>
                       </label>

                       <label class="pg-group-type pg-close-group" for="group_type_close">
                           <input type="radio" id="group_type_close" name="group_options[group_type]" value="closed" 
                           <?php
							if ( !empty( $group_options ) && isset( $group_options['group_type'] ) && $group_options['group_type']=='closed' ) {
								echo 'checked';}
							?>
                             onclick="change_wizard_group_type(this.value)">
                           <div class="pg-group-type-content">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/><path d="M11,8.17L6.49,3.66C8.07,2.61,9.96,2,12,2c5.52,0,10,4.48,10,10c0,2.04-0.61,3.93-1.66,5.51l-1.46-1.46 C19.59,14.87,20,13.48,20,12c0-3.35-2.07-6.22-5-7.41V5c0,1.1-0.9,2-2,2h-2V8.17z M21.19,21.19l-1.41,1.41l-2.27-2.27 C15.93,21.39,14.04,22,12,22C6.48,22,2,17.52,2,12c0-2.04,0.61-3.93,1.66-5.51L1.39,4.22l1.41-1.41L21.19,21.19z M11,18 c-1.1,0-2-0.9-2-2v-1l-4.79-4.79C4.08,10.79,4,11.38,4,12c0,4.08,3.05,7.44,7,7.93V18z"/></g></svg>                               <div class="pg-group-type-details">
                                   <span><?php esc_html_e( 'Closed or Private Group', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                                   <p><?php esc_html_e( 'Requires administrator’s approval, or an invite to join.', 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
                               </div>
                           </div>
                       </label>
                       <div class="pg-wzard-field-note"> <?php esc_html_e( 'You can always change group type later from group settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div> 
                       
                   </div>
                   
                   
                   
         
                   
                   
                 <div class="pg-wizard-footer">
                    <input type="button" name="previous" class="pg-previous-slide pg-action-button" value="Back" />
                    <input type="button" name="next" class="pg-next-slide pg-action-button" value="Next" /> 
                 </div>
               </div>
             
              <div class="pg-wzrad-step pg-wizard-step-3">
 
                 <div class="pg-wzard-row">
                     <div class="pg-wzard-field-wrap">
                         <div class="pg-wzrad-field">
                             <?php esc_html_e( 'Assign Role', 'profilegrid-user-profiles-groups-and-communities' ); ?><span>(Required)</span>
                         </div>
                         <div class="pg-wzrad-input pm_select_required">
                             <select name="associate_role" id="associate_role">
                                 <option value=""><?php esc_html_e( 'Select User Role', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
                                 <?php
									$roles = get_editable_roles();
									foreach ( $roles as $key => $role ) {
										?>
                                     <option value="<?php echo esc_attr( $key ); ?>" 
                                                               <?php
																if ( !empty( $row ) && $row->associate_role == $key ) {
																	echo 'selected';}
																?>
                                        ><?php echo esc_html( $role['name'] ); ?></option>
										<?php
									}
									?>
                             </select>
                             <div class="errortext"></div>
                         </div>
                     </div>
                     <div class="pg-wzard-field-note"><?php esc_html_e( 'This user role will be automatically assigned to users who submit registration form. Existing users will not be affected.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?><i class="fa fa-external-link" aria-hidden="true"></i></a></div>
                 </div>
 
                 <div class="pg-wzard-row">
                     <div class="pg-wzard-field-wrap">
                         <div class="pg-wzrad-field">
                             <?php esc_html_e( 'Message after Registration', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                           <div class="pg-wzrad-input">
                        <textarea name="success_message" id="success_message" >
                        <?php
                        if ( !empty( $row ) ) {
							echo esc_attr( $row->success_message );}
						?>
                        </textarea>
                             <div class="errortext"></div>
                         </div>
                        
                     </div>
                     <div class="pg-wzard-field-note"> <?php esc_html_e( 'Users will see this message after they successfully submit registration form for this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></div>
                 </div>
 
                 <div class="pg-wizard-footer">
                     <input type="button" name="previous" class="pg-previous-slide pg-action-button" value="Back" />
                     <input type="button" name="next" class="pg-next-slide pg-action-button" value="Next" /> 
                 </div>
 
             </div>

            
               <div class="pg-wzrad-step pg-wizard-step-4">
                   <div class="pg-group-type-wrap">
                       <div class="pg-group-type-title"><?php esc_html_e( 'Group Fee', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                       <label class="pg-group-type pg-open-group" for="is_free_group">
                           <input type="radio" name="group_options[is_paid_group]" id="is_free_group" onClick="show_hide_wizard(this,'paidgrouphtml')" value="0" 
                           <?php
							if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
								echo 'checked';}
							?>
                            >
                           <div class="pg-group-type-content">
                               <svg height="48" viewBox="0 0 48 48" width="48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48h-48z" fill="none"/><path d="M8 24h32v12h-32z" fill="#fff"/><path d="M40 8h-32c-2.21 0-3.98 1.79-3.98 4l-.02 24c0 2.21 1.79 4 4 4h32c2.21 0 4-1.79 4-4v-24c0-2.21-1.79-4-4-4zm0 28h-32v-12h32v12zm0-20h-32v-4h32v4z"/></svg>
                               <div class="pg-group-type-details">
                                   <span><?php esc_html_e( 'No fee/ Decide Later', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                               </div>
                           </div>
                       </label>

                       <label class="pg-group-type pg-close-group" for="is_paid_group">
                           <input name="group_options[is_paid_group]" id="is_paid_group" type="radio" value="1" onClick="show_hide_wizard(this,'paidgrouphtml')" 
                           <?php
							if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
								echo 'checked';}
							?>
                            />
                           
                          <div class="pg-group-type-content">
                          <svg xmlns="http://www.w3.org/2000/svg" version="1" viewBox="0 0 24 24" enable-background="new 0 0 24 24"><path d="M 3.4375 2.03125 L 2.03125 3.4375 L 3.59375 5.03125 C 2.6845703 5.2185547 2 6.0375 2 7 L 2 17 C 2 18.1 2.9 19 4 19 L 17.59375 19 L 20.5625 21.96875 L 21.96875 20.5625 L 3.4375 2.03125 z M 9 5 L 11 7 L 20 7 L 20 9 L 13 9 L 16 12 L 20 12 L 20 16 L 21.8125 17.8125 C 21.9125 17.6125 22 17.3 22 17 L 22 7 C 22 5.9 21.1 5 20 5 L 9 5 z M 4 7 L 5.59375 7 L 7.59375 9 L 4 9 L 4 7 z M 4 12 L 10.59375 12 L 15.59375 17 L 4 17 L 4 12 z"/></svg>
                              
                          <div class="pg-group-type-details">
                                   <span><?php esc_html_e( 'Yes, there will be a joining fee', 'profilegrid-user-profiles-groups-and-communities' ); ?></span>
                                   </div>
                           </div>
                       </label>
                       
                        <div class="pg-wzard-row" id="paidgrouphtml" style=" 
                        <?php
                        if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
							echo 'display:block;';
						} else {
							echo 'display:none;';}
						?>
                        ">
                     <div class="pg-wzard-field-wrap">
                         <div class="pg-wzrad-field">
                            <?php esc_html_e( 'Group Membership Fee', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                         </div>
                         <div class="pg-wzrad-input 
                         <?php
							if ( !empty( $group_options ) && isset( $group_options['is_paid_group'] ) && $group_options['is_paid_group']==1 ) {
								echo 'pm_required';}
							?>
                            ">
                             <input type="number" min="0" name="group_options[group_price]" id="group_price" value="<?php
								if ( !empty( $group_options ) && isset( $group_options['group_price'] ) ) {
									echo esc_attr($group_options['group_price']);}
								?>" />
                            <div class="errortext"></div>
                          </div>
                         
                     </div>
                            <div class="pg-wzard-field-note"> <?php esc_html_e( 'User account will not be activated until the payment is complete. Currency and payment settings can be configured later in Global Settings.', 'profilegrid-user-profiles-groups-and-communities' ); ?> <a target="_blank" href="https://profilegrid.co/documentation/new-group-or-edit-group/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?><i class="fa fa-external-link" aria-hidden="true"></i></a></div>
                 </div>
                       <div class="pg-wzard-row" id="wizard_form_submit">
                           
                       </div>
                       
                   </div>
                   
                   
                   
         
                   
                   
                 <div class="pg-wizard-footer">
                     <input type="hidden" name="group_id" id="group_id" value="<?php
						if ( !empty( $row ) ) {
							echo esc_attr( $row->id );}
						?>" />
                     <?php wp_nonce_field( 'pm_group_wizard_form' ); ?>
                     <input type="hidden" name="action" value="pm_submit_group_wizard_form" id="action" />
                    <input type="button" name="previous" class="pg-previous-slide pg-action-button" value="Back" />
                    <input type="button" name="next" class="pg-next-slide pg-action-button" value="Save & Next" /> 
                 </div>
               </div>
               
               <div class="pg-wzrad-step pg-wizard-step-5">                 
                     <div class="pg-wzard-field-wrap pg-wzard-final-slide-wrap">
                         <div class="pg-wzard-final-title"><strong><?php esc_html_e( 'Congratulations', 'profilegrid-user-profiles-groups-and-communities' ); ?></strong>, <?php esc_html_e( 'your new default user group', 'profilegrid-user-profiles-groups-and-communities' ); ?> <strong> 
                                                                                     <?php
																						if ( !empty( $row ) ) {
																							echo esc_attr( $row->group_name );}
																						?>
                            </strong> <?php esc_html_e( 'is now ready!', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
  
                         <div>
                             <div class="success-checkmark">
                                 <div class="check-icon">
                                     <span class="icon-line line-tip"></span>
                                     <span class="icon-line line-long"></span>
                                     <div class="icon-circle"></div>
                                     <div class="icon-fix"></div>
                                 </div>
                             </div>
                             </div>
                         
                         <div class="pg-wzard-links-wrap">
                             <div class="pg-wzard-links-group">
                             <div class="pg-wzard-links-title"><?php esc_html_e( "Here's what you can do next:", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                             <div class="pg-wzard-links"><?php esc_html_e( 'Publish Login Form using shortcode', 'profilegrid-user-profiles-groups-and-communities' ); ?> <span class='pg_wizard_shortcode' id="pg_wizard_login_shortcode">[profilegrid_login]</span> <a href="javascript:void(0)" onclick="pg_copy_wizard_shortcode(document.getElementById('pg_wizard_login_shortcode'))"> <span class="pg-wizard-copy-icon"> <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> </span></a><span style="display: none" class="pg-wizard-shorcode-copied"><?php esc_html_e( 'Copied!', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></div>
                             <div class="pg-wzard-links"><?php esc_html_e( 'Publish Registration Form using shortcode', 'profilegrid-user-profiles-groups-and-communities' ); ?> <span class='pg_wizard_shortcode' id="pg_wizard_register_shortcode">[profilegrid_register gid="
                                                                           <?php
																			if ( !empty( $row ) ) {
																				echo esc_attr( $row->id );}
																			?>
                                "]</span><a href="javascript:void(0)" onclick="pg_copy_wizard_shortcode(document.getElementById('pg_wizard_register_shortcode'))"><span class="pg-wizard-copy-icon"> <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg> </span></a><span style="display: none" class="pg-wizard-shorcode-copied"><?php esc_html_e( 'Copied!', 'profilegrid-user-profiles-groups-and-communities' ); ?></span></div>
                             <div class="pg-wzard-links"><?php esc_html_e( 'Edit Default Registration Form ', 'profilegrid-user-profiles-groups-and-communities' ); ?><a href="admin.php?page=pm_profile_fields&gid=
                                                                           <?php
																			if ( !empty( $row ) ) {
																				echo esc_attr( $row->id );}
																			?>
                                "> <?php esc_html_e( ' Fields', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                             <div class="pg-wzard-links"><?php esc_html_e( 'Explore all ', 'profilegrid-user-profiles-groups-and-communities' ); ?><a href="admin.php?page=pm_add_group&id=
                                                                           <?php
																			if ( !empty( $row ) ) {
																				echo esc_attr( $row->id );}
																			?>
                                "> <?php esc_html_e( ' Group Settings', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                             
                             </div>
                             
                         </div>
                         
                         <div class="pg-modify-group-setting-link"><?php esc_html_e( '* If you are using Gutenberg Block Editor, you will find all ProfileGrid Blocks in widgets section.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                         
                     </div>                                                 
                 <div class="pg-wizard-footer">
                    <input type="button" name="previous" class="pg-previous-slide pg-action-button" value="Back" />
                    <input type="button" name="next" class="pg-action-button" value="Close" onclick="window.location.href='admin.php?page=pm_manage_groups'" /> 
                 </div>
               </div>
             
           </form>
            
    
         
           
       </div>
                
     
                
   </div>
           
        <div class="pg-wizard-dashboard" onclick="window.location.href='admin.php?page=pm_manage_groups'"><span class="dashicons dashicons-arrow-left-alt2"></span>Back to WordPress Dashboard</div>  
       </div>
   
   <div class="pg-wizard-steps-ready">

       <div class="pg-wizard-group-wrap">
           <div class="pg-wizard-group">
               <div class="pg-wizard-group-title"><span class="pg-wizard-group-title-span">
               <?php
				if ( !empty( $row ) ) {
					echo esc_attr( $row->group_name );}
				?>
                </span>
                   <div class="pg-wizard-group-status-tag"><span class="pg-wizard-group-type-span">
                   <?php
					if ( isset( $group_options['group_type'] ) ) {
						echo esc_html( $group_options['group_type'] );
					} else {
						echo 'open';}
					?>
                    </span></div>
               </div>
               <div class="pg-wizard-group-feature-image">
                   
         <div class="uiminput" id="icon_html">
          <input id="group_icon" type="hidden" name="group_icon" class="icon_id" value="<?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->group_icon );}
			?>" />
          <input id="wizard_group_id" type="hidden" name="wizard_group_id" class="wizard_group_id" value="<?php
			if ( !empty( $row ) ) {
				echo esc_attr( $row->id );}
			?>" />
          
          <div class="pm-group-logo-img">
                        <?php echo wp_kses_post($pmrequests->profile_magic_get_group_icon( $row, 'wizard_group_icon_button' )); ?>
                        </div>
                        <div class="pm-group-bg">
                        <?php echo wp_kses_post($pmrequests->profile_magic_get_group_icon( $row, 'wizard_group_icon_button' )); ?>
                        </div>
          
          <div class="errortext" id="icon_error"></div>
        </div>
                   
               </div>
               <div class="pg-wizard-group-description">
               <?php
				if ( !empty( $row ) ) {
					echo esc_attr( $row->group_desc );}
				?>
                </div>
               <div class="pg-wizard-group-button"><button><?php esc_html_e( 'Details', 'profilegrid-user-profiles-groups-and-communities' ); ?></button></div>

           </div>


       </div>


   </div>  
       
     </div>  
   
   
   
</div>



