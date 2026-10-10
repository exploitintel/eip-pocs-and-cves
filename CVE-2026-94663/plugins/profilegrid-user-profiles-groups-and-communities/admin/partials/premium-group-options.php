
<div class="pg-group-promo-options" ><span>More Options<span id="pg-group-promo-toggle" data-text-swap="<?php esc_attr_e( 'Hide', 'profilegrid-user-profiles-groups-and-communities' ); ?>">Show</span></span></div>
<div class="pg-group-promo-content" style="display: none;">
    <div class="pg_buy_pro_wrap"><span class="pg_buy_pro_inline">Add these options to ProfileGrid by upgrading to <strong>ProfileGrid Premium Bundle</strong> <a href="admin.php?page=pm_extensions" target="blank">More Info</a></span></p></div>   
    
<?php
$pmrequest             = new PM_request();
$deactivate_extensions = $pmrequest->pg_check_premium_extension();


if ( !class_exists( 'Profilegrid_Display_Name' ) ) :
	?>
<div class="uimrow" id="notification">
       <div class="uimfield">
         <?php esc_html_e( 'Display Name Pattern', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="display_name" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  onClick="" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Turn on customized display names for user profiles for this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
</div>
<?php endif; ?>

<?php if ( !class_exists( 'Profilegrid_Group_Fields' ) ) : ?>

<div class="uimrow" id="group_field_options_html">
        <div class="uimfield">
          <?php esc_html_e( 'Custom Group Properties:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Turn on customized Group fields options for this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div> 

<?php endif; ?>

<?php if ( !class_exists( 'Profilegrid_Bbpress' ) ) : ?>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Enable Forums Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Display Forums tab in user profile area for members of this group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Member Can Create New Topics', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Allow members of this group to start new forum topics.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Member Can Create New Replies', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Allow members of this group to reply to topics in forums.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>
<?php endif; ?>

<?php if ( !class_exists( 'Profilegrid_Mailchimp' ) ) : ?>
<div class="uimrow">
  <div class="uimfield">
    <?php esc_html_e( 'Enable MailChimp:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
  </div>
  <div class="uiminput">
    <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;"  disabled/>
    <label for=""></label>
  </div>
    <div class="uimnote"><?php esc_html_e( 'Assign ProfileGrid users to MailChimp lists with custom field mapping and options for users to manage subscriptions.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<?php endif; ?>


<?php if ( !class_exists( 'Profilegrid_Woocommerce' ) ) : ?>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Display Purchases Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled />
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays Purchases tab in user profile page with thumbnails and names of the products purchased by the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>


<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Show Product Reviews Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays Product Reviews tab in user profile page with reviews of the products that the user has posted.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Show Orders in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays order history and status inside the 'Settings' section of user. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Show Shipping Address in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays and allows editing of shipping address inside the 'Settings' section of user profile. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Show Billing Address in User Account', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays and allows editing of billing address inside the 'Settings' section of user. This is only accessible to the logged in user.", 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Display Purchases Count and Total Spent', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays total count of products purchased and money spent by the user on their profile headers.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
</div>
<?php endif; ?>

<?php if ( !class_exists( 'Profilegrid_Advanced_Woocommerce' ) ) : ?>
<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Display Vendor Dashboard Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled />
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays the Vendor Dashboard tab on the user profile page with all the vendor details in one place. Visible only on user's own profile.", 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Display Shop Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( 'Displays the Shop tab on the user profile page from where customers can purchase products uploaded by the user.', 'profilegrid-user-profiles-groups-and-communities' ); ?>         
         </div>
</div>

<div class="uimrow">
       <div class="uimfield">
         <?php esc_html_e( 'Display Shop Settings Tab', 'profilegrid-user-profiles-groups-and-communities' ); ?>
       </div>
       <div class="uiminput">
         <input name="" id="" type="checkbox"  class="pm_toggle" value="1" style="display:none;" disabled/>
         <label for=""></label>
       </div>
         <div class="uimnote"><?php esc_html_e( "Displays the Shop Settings tab on the user profile page from where user can configure shop details. Visible only on user's own profile.", 'profilegrid-user-profiles-groups-and-communities' ); ?>
         </div>
</div>
<?php endif; ?>
    
 </div>
