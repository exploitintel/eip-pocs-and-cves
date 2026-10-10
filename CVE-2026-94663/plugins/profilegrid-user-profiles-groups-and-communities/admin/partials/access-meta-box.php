<div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Apply custom access settings?', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <input name="pm_enable_custom_access" id="pm_enable_custom_access" type="checkbox"  class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_access_html')" <?php checked( get_post_meta( $post->ID, 'pm_enable_custom_access', true ), '1' ); ?> />
          <label for="pm_enable_custom_access"></label>
        </div>
      </div>
  
 <div class="childfieldsrow" id="pm_access_html" style=" 
 <?php
	if ( get_post_meta( $post->ID, 'pm_enable_custom_access', true )!=1 ) {
		echo 'display:none';
	} else {
		'display:block';
	}
	?>
    " >
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Content Availability', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <ul class="uimradio">
              <li>
                <input type="radio" name="pm_content_access" id="pm_content_access_admin_1" value="1" onClick="pm_show_hide(this,'','pm_group_html')"  <?php checked( get_post_meta( $post->ID, 'pm_content_access', true ), '1' ); ?>>
                <?php esc_html_e( 'Content accessible to Everyone', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
              <li>
                <input type="radio" name="pm_content_access" id="pm_content_access_admin_2" value="2" onClick="pm_show_hide(this,'pm_group_html')" <?php checked( get_post_meta( $post->ID, 'pm_content_access', true ), '2' ); ?>>
                <?php esc_html_e( 'Content accessible to Logged In Users', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
              <li>
                <input type="radio" name="pm_content_access" id="pm_content_access_admin_5" value="5" onClick="pm_show_hide(this,'','pm_group_html')" <?php checked( get_post_meta( $post->ID, 'pm_content_access', true ), '5' ); ?>>
                <?php esc_html_e( "Members of Author's Groups", 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
             <li>
                <input type="radio" name="pm_content_access" id="pm_content_access_admin_3" value="3" onClick="pm_show_hide(this,'','pm_group_html')" <?php checked( get_post_meta( $post->ID, 'pm_content_access', true ), '3' ); ?>>
                <?php esc_html_e( 'Content accessible to My Friends', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
              <li>
                <input type="radio" name="pm_content_access" id="pm_content_access_admin_4" value="4" onClick="pm_show_hide(this,'','pm_group_html')" <?php checked( get_post_meta( $post->ID, 'pm_content_access', true ), '4' ); ?>>
                <?php esc_html_e( 'Content accessible to Only Me', 'profilegrid-user-profiles-groups-and-communities' ); ?>
              </li>
              <?php do_action('pg_custom_content_access_html',$post); ?>
            </ul>
        </div>
      </div>

   <div class="childfieldsrow" id="pm_group_html" style=" 
   <?php
	if ( get_post_meta( $post->ID, 'pm_content_access', true )!=2 ) {
		echo 'display:none';
	} else {
		'display:block';
	}
	?>
    ">
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Select the User Group that can see this content?', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
          <?php
                  $dbhandler = new PM_DBhandler();
			$groups          =  $dbhandler->get_all_result( 'GROUPS' );
			?>
          <select name="pm_content_access_group" id="pm_content_access_group">
          <option value="all" <?php selected( get_post_meta( $post->ID, 'pm_content_access_group', true ), 'all' ); ?>><?php esc_html_e( 'All Registered Users', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <?php foreach ( $groups as $group ) : ?>
          <option value="<?php echo esc_attr( $group->id ); ?>" <?php selected( get_post_meta( $post->ID, 'pm_content_access_group', true ), $group->id ); ?>><?php echo esc_html( $group->group_name ); ?></option>
          <?php endforeach; ?>
          </select>
        </div>
      </div>
  </div>
    </div>
        
