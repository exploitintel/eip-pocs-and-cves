<?php
$pmhtmlcreator = new PM_HTML_Creator( $this->profile_magic, $this->version );
$pmrequests    = new PM_request;
$pagenum       = filter_input( INPUT_GET, 'pagenum' );
$rid           = filter_input( INPUT_GET, 'rid' );
$pagenum       = isset( $pagenum ) ? absint( $pagenum ) : 1;
$group_page_link = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '' );
$pm_sanitizer  = new PM_sanitizer;
$post_obj      = $pm_sanitizer->sanitize( $_POST );

// New: basic_function helper for nonce rendering
$basic_function = null;
if ( class_exists( 'Profile_Magic_Basic_Functions' ) ) {
    $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
}

if ( ! empty( $gid ) ) {
    $primary_gid     = $pmrequests->pg_get_primary_group_id( $gid );
    $group_page_link = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $primary_gid );
    //$group_page_link = add_query_arg( 'gid',$primary_gid,$group_page_link );
    $groupinfo    = $dbhandler->get_row( 'GROUPS', $primary_gid );
    $group_leader = maybe_unserialize( $groupinfo->group_leaders );
} else {
    $gid         = '';
    $primary_gid = '';
}

?>
<div class="pmagic"> 
  <!-----Operationsbar Starts----->
  <div class="pm-group-view pg-theme-one pm-dbfl">
    <?php if ( $dbhandler->get_global_option_value( 'pm_show_profile_image', '1' ) == '1' || $dbhandler->get_global_option_value( 'pm_show_user_display_name', '1' ) == '1' || $dbhandler->get_global_option_value( 'pm_show_user_group_name', '1' ) == '1' || $dbhandler->get_global_option_value( 'pm_show_user_group_badges', '1' ) == '1' ) : ?>    
    <div class="pm-header-section pm-dbfl pm-bg pm-border pm-radius5"> 
      <!-- header section -->
      <div class="pm-profile-title-header pm-dbfl">
          <div class="pg-profile-head-wrap">
        <?php echo wp_kses_post( $pmrequests->pm_show_profile_image_profile_page( $uid, $current_user->ID, $user_info ) ); ?>
        <div class="pm-profile-title pm-difl pm-pad10">
            <?php if ( $dbhandler->get_global_option_value( 'pm_show_user_display_name', '1' ) == '1' ) : ?>  
          <div class="pm-user-name pm-dbfl pm-clip"><?php echo wp_kses_post( $pmrequests->pm_get_display_name( $uid, true ) ); ?></div>
           <?php endif; ?>
           <?php if ( ! empty( $gid ) && $dbhandler->get_global_option_value( 'pm_show_user_group_name', '1' ) == '1' ) : ?>
          <div class="pm-user-group-name pm-dbfl pm-clip">
              <a href="<?php echo esc_url( $group_page_link ); ?>">
                  <span> <i class="fa fa-users" aria-hidden="true"></i>
                  <?php echo wp_kses_post( $groupinfo->group_name ); ?>
                  </span>
              </a>
               <?php $total_assign_group = count( array_unique( $gid ) ); if ( ! empty( $gid ) && is_array( $gid ) && $total_assign_group > 1 ) : ?>
              <?php if ( $total_assign_group > 2 ) { $group_count_String = esc_html__( 'more groups', 'profilegrid-user-profiles-groups-and-communities' ); } else { $group_count_String = esc_html__( 'more group', 'profilegrid-user-profiles-groups-and-communities' ); } ?>
              <div class="pg-more-groups"><a onclick="pg_open_group_tab()"><span>+<?php echo wp_kses_post( count( array_unique( $gid ) ) - 1 . ' ' . $group_count_String ); ?> </span></a></div>
               <?php endif; ?>
               <a></a> 
          </div>
          <?php endif; ?>
           <?php do_action( 'profile_magic_show_additional_header_info', $uid ); ?>
        </div>
        <?php do_action( 'profile_magic_show_additional_header_info2', $uid ); ?>
      </div> 
          
          <div class="pm-group-icon pm-difr pm-pad10">
              
        <?php if ( ! empty( $gid ) && $dbhandler->get_global_option_value( 'pm_show_user_group_badges', '1' ) == '1' ) : ?>
            <div id="pg-group-badge">
                <div id="pg-group-badge-dock">
                 <?php $pmrequests->pg_get_user_groups_badge_slider( $uid ); ?>
                </div>
            </div> 
        <?php endif; ?>    
              

          </div>
      </div>
    </div>
   <?php endif; ?>
      <?php do_action( 'profile_magic_profile_tabs', $uid, $gid, $primary_gid, $sections ); ?>
  </div>
  
  <?php if ( $uid == $current_user->ID ) : ?>
  <div class="pm-popup-mask"></div>
    <div id="pm-change-image-dialog">
    <div class="pm-popup-container pm-update-image-container pm-radius5">
      <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
          <i class="fa fa-camera-retro" aria-hidden="true"></i>
        <?php esc_html_e( 'Change Profile Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          <div class="pm-popup-close pm-difr">
              <img src="<?php echo esc_url( $path . 'images/popup-close.png' ); ?>" height="24px" width="24px">
          </div>
      </div>
      <div class="pm-popup-image pm-dbfl pm-bg pm-pad10"> 
          <?php echo get_avatar( $user_info->user_email, 150, '', false, array( 'class' => 'pm-user', 'id' => 'avatar-edit-img', 'force_display' => true ) ); ?>
        <div class="pm-popup-action">
          <a type="button" class="btn btn-primary" id="change-pic"><?php esc_html_e( 'Change Image', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
	  <div id="changePic" class="" style="display:none">
            <form id="cropimage" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                <?php
                if ( $basic_function && is_callable( array( $basic_function, 'pm_render_nonce_field' ) ) ) {
                    $basic_function->pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
                } elseif ( function_exists( 'pm_render_nonce_field' ) ) {
                    pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
                }
                ?>
                <div class="pm-dbfl">
	           <label><?php esc_html_e( 'Upload your image', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
                <input type="file" name="photoimg" id="photoimg" />
                    </div>
            <input type="hidden" name="action" value="pm_upload_image" />
            <input type="hidden" name="status" value="" id="status" />
            <input type="hidden" name="filepath" id="filepath" value="<?php echo esc_url( $path ); ?>" />
            <input type="hidden" name="user_id" value="<?php echo esc_attr( isset( $user_info->ID ) ? absint( $user_info->ID ) : get_current_user_id() ); ?>" />
            <input type="hidden" name="user_meta" id="user_meta" value="<?php echo esc_attr( 'pm_user_avatar' ); ?>" />
            <input type="hidden" id="x" name="x" />
            <input type="hidden" id="y" name="y" />
            <input type="hidden" id="w" name="w" />
            <input type="hidden" id="h" name="h" />
            <div id="preview-avatar-profile"></div>
	    <div id="thumbs" style="padding:5px; width:600px"></div>	
            </form>
            <div class="modal-footer">
                <button type="button" id="btn-cancel" class="btn btn-default"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                <button type="button" id="btn-crop" class="btn btn-primary"><?php esc_html_e( 'Crop & Save', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
            </div>
          </div>
          <form method="post" action="" enctype="multipart/form-data" onsubmit="return pg_prevent_double_click(this);">
            <?php
            if ( $basic_function && is_callable( array( $basic_function, 'pm_render_nonce_field' ) ) ) {
                $basic_function->pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            } elseif ( function_exists( 'pm_render_nonce_field' ) ) {
                pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            }
            ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_info->ID ); ?>" />
            <input type="hidden" name="user_meta" value="<?php echo esc_attr( 'pm_user_avatar' ); ?>" />
            <input type="submit" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="remove_image" id="pg_remove_profile_image_btn" />
          </form>
        </div>
        <p class="pm-popup-info pm-dbfl pm-pad10">
          <?php esc_html_e( 'For best visibility choose square image with minimum size of 200 x 200 pixels', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </p>
      </div>
    </div>
  </div>
<div class="pm-popup-mask"></div>
  <div id="pm-change-cover-image-dialog">
    <div class="pm-popup-container pm-update-image-container pm-radius5">
      <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
        <?php esc_html_e( 'Change Cover Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>
          <div class="pm-popup-close pm-difr">
              <img src="<?php echo esc_url( $path . 'images/popup-close.png' ); ?>" height="24px" width="24px">
          </div>
      </div>
      <div class="pm-popup-image pm-dbfl pm-pad10 pm-bg"> 
          <?php echo wp_get_attachment_image( $pmrequests->profile_magic_get_user_field_value( $user_info->ID, 'pm_cover_image' ), array( 85, 85 ), true, array( 'class' => 'pm-cover-image', 'id' => 'cover-edit-img' ) ); ?>
        <div class="pm-popup-action pm-dbfl pm-pad10">
          <a type="button" class="btn btn-primary" id="change-cover-pic"><?php esc_html_e( 'Change Cover Image', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
	  <div id="changeCoverPic" class="" style="display:none">
            <form id="cropcoverimage" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
            <?php
            if ( $basic_function && is_callable( array( $basic_function, 'pm_render_nonce_field' ) ) ) {
                $basic_function->pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            } elseif ( function_exists( 'pm_render_nonce_field' ) ) {
                pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            }
            ?>
	    <label><?php esc_html_e( 'Upload Your Cover Image', 'profilegrid-user-profiles-groups-and-communities' ); ?></label>
            <input type="file" name="coverimg" id="coverimg"  />
            <input type="hidden" name="action" value="pm_upload_cover_image" />
            <input type="hidden" name="cover_status" value="" id="cover_status" />
            <input type="hidden" name="cover_filepath" id="cover_filepath" value="<?php echo esc_url( $path ); ?>" />
            <input type="hidden" name="user_id" value="<?php echo esc_attr( isset( $user_info->ID ) ? absint( $user_info->ID ) : get_current_user_id() ); ?>" />
            <input type="hidden" id="cx" name="cx" />
            <input type="hidden" id="cy" name="cy" />
            <input type="hidden" id="cw" name="cw" />
            <input type="hidden" id="ch" name="ch" />
            <input type="hidden" id="cover_minwidth" name="cover_minwidth" value="" />
           
            <div id="preview-cover-image"></div>
	    <div id="thumbs" style="padding:5px; width:600px"></div>	
            </form>
            <div class="modal-footer">
                <button type="button" id="btn-cover-cancel" class="btn btn-default"><?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
                <button type="button" id="btn-cover-crop" class="btn btn-primary"><?php esc_html_e( 'Crop & Save', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
            </div>
          </div>
            
            
          <form method="post" action="" enctype="multipart/form-data" onsubmit="return pg_prevent_double_click(this);">     
            <?php
            if ( $basic_function && is_callable( array( $basic_function, 'pm_render_nonce_field' ) ) ) {
                $basic_function->pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            } elseif ( function_exists( 'pm_render_nonce_field' ) ) {
                pm_render_nonce_field( 'pm_profile_update_action', 'pm_profile_nonce' );
            }
            ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_info->ID ); ?>" />
            <input type="hidden" name="user_meta" value="<?php echo esc_attr( 'pm_cover_image' ); ?>" />
            <input type="submit" value="<?php esc_attr_e( 'Remove', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="remove_image" id="pg_remove_cover_image_btn" />
          </form>
        </div>
        <p class="pm-popup-info pm-dbfl pm-pad10">
          <?php echo wp_kses_post( 'For best visibility choose a landscape aspect ratio image with size of <span id="pm-cover-image-width">1200</span> x 300 pixels', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </p>
      </div>
    </div>
  </div>

<div class="pm-popup-mask"></div>    

<div id="pm-edit-group-popup" style="display: none;">
    <div class="pm-popup-container" id="pg_edit_group_html_container">
     
        
    </div>
</div>
    <?php if ( $dbhandler->get_global_option_value( 'pm_enable_blog', '1' ) == 1 ) : ?>

      
               <div class="pg-blog-dialog-mask" style="<?php if ( isset( $post_obj['pg_blog_submit'] ) ) echo 'display:block'; ?>"></div>
          <div id="pm-add-blog-dialog" style="<?php if ( isset( $post_obj['pg_blog_submit'] ) ) echo 'display:block'; ?>">
            <div class="pm-popup-container pm-radius5">
              <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                  <i class="fa fa-key" aria-hidden="true"></i>
                <?php esc_html_e( 'Submit New Blog Post', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                  <?php if ( ! isset( $post_obj['pg_blog_submit'] ) ) : ?>
                  <div class="pm-popup-close pm-difr"><img src="<?php echo esc_url( $path . 'images/popup-close.png' ); ?>" height="24px" width="24px"></div>
                  <?php endif; ?>
              </div>
              <div class="pm-popup-image">
                <div class="pm-popup-action pm-dbfl pm-pad10 pm-bg">
                  <?php echo do_shortcode( '[profilegrid_submit_blog]' ); ?>
                </div>
              </div>
            </div>
          </div>
    <?php endif; ?>
  <?php else : ?>
<div class="pm-popup-mask"></div>    

    <div id="pm-show-profile-image-dialog">
        <div class="pm-popup-container">

            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                <div class="pm-popup-close pm-difr">
                    <img src="<?php echo esc_url( $path . 'images/popup-close.png' ); ?>" height="24px" width="24px">
                </div>
            </div> 

            <div class="pm-popup-image pm-dbfl pm-pad10 pm-bg">    
                <?php echo get_avatar( $user_info->user_email, 512, '', false, array( 'force_display' => true ) ); ?>
            </div>

        </div>
    </div>

<div class="pm-popup-mask"></div>    
    <div id="pm-show-cover-image-dialog">
        <div class="pm-popup-container">
            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                <div class="pm-popup-close pm-difr">
                    <img src="<?php echo esc_url( $path . 'images/popup-close.png' ); ?>" height="24px" width="24px">
                </div>
            </div>

            <div class="pm-popup-image pm-dbfl pm-pad10 pm-bg">    
                <?php echo wp_kses_post( $pmrequests->profile_magic_get_cover_image( $user_info->ID, 'pm-cover-image' ) ); ?>
            </div>
        </div>
    </div>



<?php endif; ?>
<?php do_action( 'profile_page_popup_html', $user_info->ID ); ?>
</div>
<div class="pm-popup-mask"></div>    

<div id="pm-edit-group-popup" style="display: none;">
    <div class="pm-popup-container" id="pg_edit_group_html_container">
     
        
    </div>
</div>
