<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$identifier = 'GROUPS';
$wpdb = null;
if ( isset( $GLOBALS['wpdb'] ) && $GLOBALS['wpdb'] instanceof wpdb ) {
    $wpdb = $GLOBALS['wpdb'];
}
$pagenum = filter_input(INPUT_GET, 'pagenum');
$demo_content_popup_default = filter_input(INPUT_GET, 'pg_demo_content_popup');
$pagenum = isset($pagenum) ? absint($pagenum) : 1;
$limit = 10; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$search = filter_input(INPUT_POST, 'pm_group_search');
$search = is_string($search) ? sanitize_text_field($search) : '';
if ( ! empty( $search ) && $wpdb instanceof wpdb ) {
    $like       = '%' . $wpdb->esc_like( $search ) . '%';
    $additional = $wpdb->prepare( 'group_name LIKE %s', $like );
} else {
    $additional = '';
}
$totalgroups = $dbhandler->get_all_result($identifier,'*', 1, 'results', 0, false, null, false, $additional);
if(!empty($totalgroups))
{
    $totalgroups = count($totalgroups);
}
else
{
    $totalgroups = 0;
}
    
$groups =  $dbhandler->get_all_result($identifier,'*',1,'results',$offset,$limit,'id','desc',$additional);
$num_of_pages = ceil( $totalgroups/$limit);
$pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum);
$quick_add_users = get_users(
    array(
        'fields'  => array( 'ID', 'display_name', 'user_login' ),
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'number'  => 200,
    )
);
update_option( 'pg_redirect_to_group_page', '0' );

$pg_demo_content_plugin_file = 'profilegrid-user-profiles-groups-and-communities-profilegrid-demo-content/profilegrid-demo-content.php';
$pg_demo_content_plugin_path = WP_PLUGIN_DIR . '/' . $pg_demo_content_plugin_file;
$pg_demo_content_is_installed = file_exists( $pg_demo_content_plugin_path );
$pg_demo_content_is_active    = false;
$pg_demo_content_url          = 'https://profilegrid.co/extensions/profilegrid-demo-content/?utm_source=pg_plugin&utm_medium=all_groups_top_bar&utm_campaign=demo_content_promo';
$pg_demo_content_target       = '_blank';

if ( $pg_demo_content_is_installed ) {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $pg_demo_content_is_active = is_plugin_active( $pg_demo_content_plugin_file );

    if ( $pg_demo_content_is_active ) {
        $pg_demo_content_url    = admin_url( 'admin.php?page=pm_demo_content_settings' );
        $pg_demo_content_target = '_blank';
    }
}
?>
<div class="pm_notification"></div>
<div class="pmagic pg-box-wrap pg-group-manager">
    
        <div class="pg-box-row">
            <div class="pg-box-col-9 pg-box-col-md-9">
                
       <!-- <div class="pg-box-mb-25 pg-d-flex">
            <div class="pg-uim-notice pg-box-white-bg pg-customize-banner">Looking for something ProfileGrid specific? We provide tailor-made customization <a href="https://metagauss.com/customization-help/" target="_blank"> Click here to know more.</a></div>
    </div>-->
                
            </div>
            <div class="pg-box-col pg-box-col-md-3"></div>
        </div>
    
    
    <div class="pg-box-row">
<div class="pg-box-col-9 pg-box-col-md-9"> 
  <?php do_action('profilegrid_dashboard_groups_filter');?>
  <!-----Operationsbar Starts----->
  <form name="pm_manage_groups" id="pm_manage_groups" action="admin.php?page=pm_add_group" method="post">
      <div class="pg-box-head-row pg-box-row  pg-card-mb-16">
          <div class="pg-box-col-12">         
              <div class="pg-box-border pg-box-white-bg">
                  <div class="pg-box-row pg-box-mb-25 pg-box-center">
                      <div class="pg-box-col-10">
                          <div class="pg-box-title">
                          <?php esc_html_e('Group Manager', 'profilegrid-user-profiles-groups-and-communities'); ?><span id="pg_selected_group_count" class="pg-group-counter">(<span>2</span> <?php esc_html_e('selected','profilegrid-user-profiles-groups-and-communities');?>)</span>
                          </div>
                      </div>
                      <div class="pg-box-col-2 pg-box-setting-icon-col pg-box-text-right"><a href="admin.php?page=pm_settings" class="pg-box-setting-icon"> <img src="<?php echo esc_url($path . 'images/global-settings.png'); ?>"></a> </div>
                  </div>
                  
                  <div class="pg-box-row pg-box-mb-25 pg-box-center">
                      <div class="pg-box-col-6 pg-box-col-md-6">
                          <div class="pg-box-head-nav">
                              <ul>
                                  <li>
                                      <a href="#" onclick="pg_all_groups_popup('pg-add-group-popup')"><?php esc_attr_e('New Group', 'profilegrid-user-profiles-groups-and-communities'); ?></a>                                   
                                  </li>
                                  <li>
                                      <a href="#" onclick="pg_all_groups_popup('pg-duplicate-group-popup')" id="pg-duplicate-groups-nav" class="pm-disabled"><?php esc_attr_e('Duplicate', 'profilegrid-user-profiles-groups-and-communities'); ?></a>                                   
                                  </li>
                                   <li>
                                      <a href="#" onclick="pg_all_groups_popup('pg-delete-group-popup')" id="pg-delete-groups-nav" class="pm-disabled"><?php esc_attr_e('Delete', 'profilegrid-user-profiles-groups-and-communities'); ?></a>                                   
                                  </li>
                                   
                              </ul>
                          </div>
                      </div>
                      
                      <div class="pg-box-col-6 pg-box-col-md-6 pg-box-text-right">
                          <div class="pg-box-head-ext-nav">
                              <ul>
                                  <li><a href="<?php echo esc_url( $pg_demo_content_url ); ?>" target="<?php echo esc_attr( $pg_demo_content_target ); ?>" class="pg-box-border pg-box-white-bg"><?php esc_html_e('Demo Content', 'profilegrid-user-profiles-groups-and-communities'); ?><span class="material-icons"> system_update_alt </span></a></li>
                                  
                                  <!--<li><a href="https://profilegrid.co/profilegrid-starter-guide" target="_blank" class="pg-box-border pg-box-white-bg"><?php esc_html_e('Starter Guide', 'profilegrid-user-profiles-groups-and-communities'); ?><span class="material-icons"> lightbulb_outline </span></a></li>-->
                                  <!--<li><a href="https://profilegrid.co/translate-plugins-profilegrid/" target="_blank" class="pg-box-border pg-box-white-bg"><?php esc_html_e('Translate', 'profilegrid-user-profiles-groups-and-communities'); ?><span class="dashicons dashicons-translation"></span></a></li>-->
                                  <!--<li><a href="https://wordpress.org/support/plugin/profilegrid-user-profiles-groups-and-communities/reviews/?filter=5" target="_blank" class="pg-box-border pg-box-white-bg"><?php esc_html_e('Rate', 'profilegrid-user-profiles-groups-and-communities'); ?><span class="dashicons dashicons-star-filled"></span></a></li>-->
                              </ul> 
                              
                          </div> 
                          
                      </div>
                  </div>
              </div>

          </div>

      </div>
  
      
  <!-------Contentarea Starts----->
  <div class="pmagic-cards pg-box-row">
     
      
    <?php if(!empty($groups)):
    foreach($groups as $group):
       $group_options =($group->group_options!="")? maybe_unserialize($group->group_options):array('group_type'=>'open');
        if(isset($group_options['group_type'])){$group_type = $group_options['group_type'];}else{$group_type='Open';}
	$meta_query_array = $pmrequests->pm_get_user_meta_query(array('gid'=>$group->id));
	$date_query = $pmrequests->pm_get_user_date_query(array('gid'=>$group->id));
	$user_query =  $dbhandler->pm_get_all_users_ajax('',$meta_query_array,'',0,6,'DESC','ID');
        $total_users = $user_query->get_total();
        $users = $user_query->get_results();
        $leaders = $pmrequests->pg_get_group_leaders($group->id);
        
	?>
   <div class="pg-box-col pg-box-col-md-3 pg-card-mb-16">
     <div class="pm-card pg-box-h-100 pg-card-mb-16">
         <label for="pg-group-id-<?php echo esc_attr($group->id);?>" class="pg-box-border pg-box-h-100 pg-box-white-bg">
      <div class="pg-box-head">
        
      <?php  if(isset( $group_options['group_type'] ) && $group_options['group_type'] == 'form' ): ?>
        <a href="admin.php?page=pm_add_form&id=<?php echo esc_attr($group->id);?>"><?php echo esc_html($group->group_name);?></a>
        <?php else: ?>
            <input type="checkbox" class="pg-all-groups-selected" name="selected[]" value="<?php echo esc_attr($group->id);?>" id="pg-group-id-<?php echo esc_attr($group->id);?>" />
        <a href="admin.php?page=pm_add_group&id=<?php echo esc_attr($group->id);?>" class="pg-group-card-title"><?php echo esc_html($group->group_name);?></a>
        <?php endif; ?>

        <div class="pg-group-status-wrap pg-group-status-open">
            <?php if(strtolower($group_type)=='open'):?>
            <span class="material-icons">public</span>
            <?php else: ?>
            <span class="material-icons">locked</span>
            <?php endif;?>
            <span style="text-transform: capitalize;"><?php echo esc_html($group_type); ?></span> <span class="pg-status-sep"> &#8901; </span> <?php if($group->is_group_limit==1){ echo esc_html($total_users).'/'.esc_html($group->group_limit);} else{  echo esc_html($total_users);}?> </div>
      </div>
      <div class="pm-card-icon">
        <?php
        echo wp_kses(
            $pmrequests->pg_get_group_card_icon_link( $group->id ),
            array(
                'a' => array(
                    'href'   => true,
                    'title'  => true,
                    'class'  => true,
                    'data-gid' => true,
                    'target' => true,
                ),
                'i' => array(
                    'class' => true,
                ),
            )
        );
        ?>
      </div>  
      
              <div class="pg-submission-wrap"> 
                  <?php if(!empty($users)):?>
                  <div class="pg-submission-title"><?php esc_html_e("Latest members", 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                  <div class="pg-box-group-users">
                      <?php foreach ($users as $user): ?>
                          <a class="pg-last-submission" title="" href="admin.php?page=pm_profile_view&id=<?php echo esc_attr($user->ID); ?>"><?php echo wp_kses_post(get_avatar($user->user_email, 48, '', false, array('class' => 'pm-user', 'force_display' => true))); ?> <span class="pg-submission-date"><span class="pg-submission-user-name"><?php echo wp_kses_post($pmrequests->pm_get_display_name($user->ID,true));?></span> <?php echo wp_kses_post(date_i18n(get_option('date_format'), strtotime($user->user_registered)) . " <span class='pg-submission-time'>" . date_i18n(get_option('time_format'), strtotime($user->user_registered))); ?></span></span> </a> 
                              <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
              </div>
      
      <div class="pg-submission-wrap"> 
          <?php if(!empty($leaders) && array_filter($leaders)):?>
                  <div class="pg-submission-title"><?php esc_html_e("Group Managers", 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                  <div class="pg-box-group-users">
                      <?php foreach ($leaders as $leader): 
                          $uid = (int)$leader;
                         $leader_profile =  get_user_by('id',$uid);
                         if(!empty($leader_profile)):
                          ?>
                      <a class="pg-last-submission" title="" href="admin.php?page=pm_profile_view&id=<?php echo esc_attr($uid); ?>"><?php echo wp_kses_post(get_avatar($leader_profile->user_email, 48, '', false, array('class' => 'pm-user', 'force_display' => true))); ?> <span class="pg-submission-date"> <span class="pg-submission-user-name"><?php echo wp_kses_post($pmrequests->pm_get_display_name($leader_profile->ID,true)); ?></span> <?php echo wp_kses_post(date_i18n(get_option('date_format'), strtotime($leader_profile->user_registered)) . " <span class='pg-submission-time'>" . date_i18n(get_option('time_format'), strtotime($leader_profile->user_registered))); ?></span></span> </a> 
                              <?php endif; endforeach; ?>
                  </div>
                  <?php endif;?>
              </div>
        <?php $group_page_url = $pmrequests->profile_magic_get_frontend_url( 'pm_group_page', '', $group->id ); ?>
        <div class="pm-form-shortcode-row">
            <a href="<?php echo esc_url( $group_page_url ); ?>" target="_blank" rel="noopener">
                [profilegrid_group <?php echo esc_html('gid="'.$group->id.'');?>"]
            </a>
        </div>
             <div class="pm-form-links">
        <div class="pm-form-row"><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr($group->id);?>"><?php esc_html_e('Settings','profile-magic');?></a></div>
        <div class="pm-form-row"><a href="admin.php?page=pm_profile_fields&gid=<?php echo esc_attr($group->id);?>"><?php esc_html_e('Fields','profile-magic');?></a></div>
      </div>
<!--      <div class="pg-box-card-setting-wrap">
           <?php if($group_type=='closed'):?>
          <div class="pg-box-card-setting-item">
               <span class="pg-box-card-setting-info"><?php esc_attr_e('Membership Requests', 'profilegrid-user-profiles-groups-and-communities'); ?></span>
              <a href="admin.php?page=pm_requests_manager&pagenum=1&gid=<?php echo esc_attr($group->id);?>"><span class="material-icons">person_add</span></a>
          </div>
          <?php endif; ?>
          <div class="pg-box-card-setting-item">
              <span class="pg-box-card-setting-info"><?php esc_attr_e('Members List', 'profilegrid-user-profiles-groups-and-communities'); ?></span>
              <a href="admin.php?page=pm_user_manager&pagenum=1&gid=<?php echo esc_attr($group->id);?>"><span class="material-icons">group</span></a>
          </div>
          
          <div class="pg-box-card-setting-item">
              <span class="pg-box-card-setting-info"><?php esc_attr_e('Add Members', 'profilegrid-user-profiles-groups-and-communities'); ?></span>
              <a href="admin.php?page=pm_user_manager&pagenum=1&pg_assign_group=<?php echo esc_attr($group->id);?>&pg_add_members=1"><span class="material-icons">person_add_alt_1</span></a>
          </div>
          
          
          <div class="pg-box-card-setting-item">
            <span class="pg-box-card-setting-info"><?php esc_attr_e('Group Options', 'profilegrid-user-profiles-groups-and-communities'); ?></span>
            <?php if(isset( $group_options['group_type'] ) && $group_options['group_type'] == 'form'):?>
            <a href="admin.php?page=pm_add_form&id=<?php echo esc_attr($group->id);?>"><span class="material-icons">settings</span></a>
            <?php else: ?>
            <a href="admin.php?page=pm_add_group&id=<?php echo esc_attr($group->id);?>"><span class="material-icons">settings</span></a>
            <?php endif;?>
        </div>
          <div class="pg-box-card-setting-item">
            <span class="pg-box-card-setting-info"><?php esc_attr_e('Group Registration Form', 'profilegrid-user-profiles-groups-and-communities'); ?></span>
            <a href="admin.php?page=pm_profile_fields&gid=<?php echo esc_attr($group->id);?>"><span class="material-icons">view_list</span></a>
          </div>
          
      </div>-->
      </label>
     </div>
              
   </div>
    <?php endforeach;?>
    <?php else: ?>
      <div class="pg-box-col-12 pg-card-mb-16">
              <div class="pg-uim-notice-row ">
                  <div class="pg-uim-notice pg-box-w-100 pg-white-bg"><?php esc_html_e('You have not created any groups yet. Once you have created a new group, it will appear here.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
              </div>
        </div>
    
    <?php endif;?>
    
    
  </div>

  
 </form>
  
  
 <!-- Pagination -->

<div class="pg-box-pagination pg-box-row-wrap"><?php if(!empty($pagination)) echo wp_kses_post($pagination);?></div> 

<!-- Pagination End --> 

</div>
        
        
<!-- Group Side banners -->        
<?php  ?>
<div class="pg-box-col pg-box-col-md-3 pg-group-side-banner-col">
    <div class="pg-group-side-banner pg-box-border pg-box-white-bg">
             <div class="pg-box-row pg-box-text-center">
            <div class="pg-box-col-12 ">
                <div class="pg-sidebanner-image">
                    <img src="<?php echo esc_url($path . 'images/svg/pg-logo-icon.svg'); ?>">
                </div>
               <!-- <div class="pg-side-banner-mg-logo pg-text-a"><img src="<?php echo esc_url($path . 'images/mg-logo.png'); ?>"></div> -->
            </div>
        </div>

        <div class="pg-box-row">
            <div class="pg-box-col-12">
                <div class="pg-side-banner-wrap">
                    <div class="pg-side-banner-content">
                        <div class="pg-side-banner-text"></div>
                        <div class="pg-side-banner-help-text"> <?php esc_html_e('Getting Started?', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                        

                        <p> <?php esc_html_e('Looking for quick answers? Check our Starter Guide or reach out to us directly.', 'profilegrid-user-profiles-groups-and-communities'); ?>   
                        </p>
                        <div class="pg-side-banner-buttons">
                            <a target="_blank" href="https://profilegrid.co/profilegrid-starter-guide/?utm_source=plugin&utm_medium=helpbox" class="pg-banner-support-btn"> <?php esc_html_e(' Starter Guide', 'profilegrid-user-profiles-groups-and-communities'); ?></a>
                            <a target="_blank" href="https://wordpress.org/support/plugin/profilegrid-user-profiles-groups-and-communities/" class="pg-banner-support-btn"> <?php esc_html_e('Create Support Ticket', 'profilegrid-user-profiles-groups-and-communities'); ?></a>
                            </div>

                    </div>
                </div>
                
<!--                 <div class="pg-side-banner-wrap pg-customize-banner-main">
                        <div class="pg-side-banner-content pg-customize-banner">
                        <div class="pg-side-banner-text"></div>
                        <div class="pg-side-banner-help-text"> <?php esc_html_e('Custom Solution', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                        <p class="pg-customize-banner-content">Have us build the exact feature that you need.  	</p>
                         <a target="_blank" href="https://profilegrid.co/help-support/customizations/?utm_source=plugin&utm_medium=helpbox" class="button pg-banner-support-btn"> <?php esc_html_e('Contact Here', 'profilegrid-user-profiles-groups-and-communities'); ?> </a>
               

                    </div>



                </div>-->
            </div>
        </div>
        <div>
           
        </div>


    </div>
    


</div>
   <?php ?> 
<!-- Group Side banners End -->  

    </div>

    <div class="pg-uim-notice-row pg-box-row">
        <div class="pg-box-col-12">
            <div class="pg-uim-notice pg-box-w-100 pg-white-bg">
                <strong><?php esc_html_e('Note:', 'profilegrid-user-profiles-groups-and-communities'); ?></strong>
                <?php esc_html_e(' Groups are optional. If you do not wish to create multiple groups, you can use the default group for all user profiles and sign ups.', 'profilegrid-user-profiles-groups-and-communities'); ?>
            </div>
        </div>
    </div>
    
    
    <!----Form Review Banner---->

    <div class="pm_five_star_Banner">
        <div class="pm_five_star_Banner_wrap">
            <p align="center"><?php esc_html_e('Do you like ProfileGrid? Help us  make it better…Please rate it ', 'profilegrid-user-profiles-groups-and-communities'); ?><span class="pm-star">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                </span>  <?php esc_html_e('stars on', 'profilegrid-user-profiles-groups-and-communities'); ?> <a target="_blank" href="https://wordpress.org/support/plugin/profilegrid-user-profiles-groups-and-communities/reviews/?filter=5">WordPress.org</a></p>
        </div>
    </div>
    
    <!----Form Review Banner End ---->
    
    
    <!--Customize Banner--->
    <!---
    <div class="pg-customize-banner-row pg-box-row pg-mt-5">
        <div class="pg-box-col-12">
            <div class="pg-customize-banner-wrap pg-d-flex pg-justify-content-between pg-box-center pg-p-3 pg-box-w-100 pg-white-bg ">
                <div class="pg-customize-banner-logo"><img width="128" src="<?php echo esc_url($path . 'images/svg/pg-logo.png'); ?>"></div>
                <div class="pg-banner-pitch-content-wrap pg-lh-normal">
                    <div class="pg-banner-pitch-head pg-fs-2 pg-fw-bold">
                        <?php esc_html_e('Customize ProfileGrid', 'profilegrid-user-profiles-groups-and-communities'); ?>
                    </div>
                    <div class="pg-banner-pitch-content pg-fs-5 pg-text-muted">
                        <?php esc_html_e('Have our team build the exact feature that you need.', 'profilegrid-user-profiles-groups-and-communities'); ?>
                    </div>
                </div>
                
                <div class="pg-banner-btn-wrap">
                    <button class="button button-primary pg-customize-banner-btn">Get Help Now</button>
                </div>
                
                
            </div>
        </div>
    </div>
    
    -->
    
    <!--Customize Banner --->
    
</div>


      
    

<!--New Group Modal--->

<div id="pg-add-group-popup" class="pg-group-modal-box pg-modal-box-main" style="display:none">
    <div class="pg-modal-box-overlay pg-modal-box-overlay-fade-in"></div>
    <div class="pg-modal-box-wrap pg-modal-box-out">
        <div class="pg-modal-box-head">
   
           <span class="pg-modal-box-close material-icons">close</span>
                     
        </div>
        <div class="pg-group-modal-wrap">
        <div class="pg-group-modal-title"><?php esc_attr_e('New Group', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
        <div class="pg-group-modal-subtitle"><?php esc_attr_e('Groups are an excellent way to categorize your users and offer memberships.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
        
        <form name="pm_add_group" id="pm_add_group" action="admin.php?page=pm_add_group" method="post">
             
        <div class="pg-new-group-form">
            
             <div class="pm-new-form-row pg-box-row pg-card-mb-16">
                 <div class="pg-box-col-12">
                    <div class="pg-group-field">
                   <label><?php esc_attr_e('Group Name', 'profilegrid-user-profiles-groups-and-communities'); ?></label>
                    </div>
                     <input type="text" name="group_name" id="group_name" placeholder="<?php esc_attr_e( 'Enter Group Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>">
                  <div class="errortext" id="group_error" style="display:none;"><?php esc_html_e('This is required field', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                  <input type="hidden" name="group_id" id="group_id" value="0" />
                  <input type="hidden" name="associate_role" id="associate_role" value="subscriber">
                  
                  <div class="pg-new-group-form-note"><?php esc_attr_e('Name of this Group. It can be changed later.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                 </div>
                
              </div>
             <div class="pm-new-form-row pg-box-row pg-card-mb-16">
                 <div class="pg-box-col-12 pg-group-search-user" style="display:none">
                    <div class="pg-group-field">
                   <label><?php esc_attr_e('Add Members Now (Optional)', 'profilegrid-user-profiles-groups-and-communities'); ?></label>
                    </div>
                    <?php if ( ! empty( $quick_add_users ) ) : ?>
                    <input type="text" id="pg-add-members-search" class="pg-add-members-search" placeholder="<?php esc_attr_e( 'Search users', 'profilegrid-user-profiles-groups-and-communities' ); ?>" />
                    <select multiple name="pg_add_members[]" id="pg_add_members">
                        <?php foreach ( $quick_add_users as $quick_user ) : ?>
                            <option value="<?php echo esc_attr( $quick_user->ID ); ?>">
                                <?php echo esc_html( $quick_user->display_name . ' (' . $quick_user->user_login . ')' ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pg-new-group-form-note"><?php esc_attr_e('Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                    <div class="pg-new-group-form-note"><?php esc_attr_e('For large member lists, use the Members screen after creating the group.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                    <?php else : ?>
                    <div class="pg-new-group-form-note"><?php esc_attr_e('No existing users found to add.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
                    <?php endif; ?>
                 </div>
              </div>
             <div class="pg-box-row pg-box-modal-footer">
                     <div class="pg-box-col-12 pg-box-text-right">
                  <?php wp_nonce_field('save_pm_add_group'); ?>
                  <input type="submit" value="<?php esc_attr_e('Save', 'profilegrid-user-profiles-groups-and-communities'); ?>" name="submit_group" id="submit_group" onclick="return check_validation(this)" />
                  </div>
                    </div>
            
        </div>
        
        </form>
        </div>



    </div>
</div>

<!--New Group Modal End--->


<!--Delete Group Modal--->

<div id="pg-delete-group-popup" class="pg-group-modal-box pg-modal-box-main" style="display:none">
    <div class="pg-modal-box-overlay pg-modal-box-overlay-fade-in"></div>
    <div class="pg-box-row-wrap pg-modal-box-wrap pg-modal-box-out">
        <div class="pg-modal-box-head-wrap pg-box-center pg-box-mb-25 pg-box-row">
            <div class="pg-modal-box-title pg-box-col-10">Delete Group(s)</div>
            <div class="pg-box-col-2"><span class="pg-modal-box-close  material-icons">close</span></div>
                     
        </div>
        <div class="pg-box-row pg-box-mb-25">
            <div class="pg-box-col-12"> 
                <div class="pg-group-modal-wrap">
                    <div class="pg-group-modal-subtitle pg-delete-group-modal">
                        <?php esc_attr_e('You are going to delete the following Groups. This action is irreversible. Please confirm to proceed.', 'profilegrid-user-profiles-groups-and-communities'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="pg-group-modal-wrap">              
        <div class="pg-delete-group-form">
            <form name="pm_delete_group" id="pm_delete_group" class="" action="admin.php?page=pm_add_group" method="post">
             <div class="pg-deletable-group-details">
                
                
              </div>
             <div class="pg-box-row pg-box-modal-footer">
                     <div class="pg-box-col-12 pg-box-text-right">
                  <?php wp_nonce_field('delete_pm_group'); ?>
                        <!-- <input type="checkbox" name="notify_users" id="notify_users"  value="1" /> <label for="notify_users"> <?php //esc_html_e('Also notify users who are members of this group.'); ?> </label>    -->  
                  <input type="submit" value="<?php esc_attr_e('Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?>" name="delete_group" id="delete_group" />
                  </div>
                    </div>
             </form>
            
        </div>
        
        
        </div>



    </div>
</div>

<!--Delete Group Modal End--->

<!--Duplicate Group Modal--->

<div id="pg-duplicate-group-popup" class="pg-group-modal-box pg-modal-box-main" style="display:none">
    <div class="pg-modal-box-overlay pg-modal-box-overlay-fade-in"></div>
    <div class="pg-box-row-wrap pg-modal-box-wrap pg-modal-box-out">
        <div class="pg-modal-box-head-wrap pg-box-center pg-box-mb-25 pg-box-row">
            <div class="pg-modal-box-title pg-box-col-10">Duplicate Group(s)</div>
            <div class="pg-box-col-2"><span class="pg-modal-box-close  material-icons">close</span></div>
        </div>
        
        <div class="pg-group-modal-wrap">
        <div class="pg-group-modal-subtitle pg-duplicate-group-modal"><?php esc_attr_e('You are going to duplicate the following Groups. Please confirm to proceed.', 'profilegrid-user-profiles-groups-and-communities'); ?></div>
               
        <div class="pg-new-group-form">
             <form name="pm_duplicate_group" id="pm_duplicate_group" action="admin.php?page=pm_add_group" method="post">
             <div class="pg-deletable-group-details">
                
                
              </div>
             <div class="pg-box-row pg-box-modal-footer">
                     <div class="pg-box-col-12 pg-box-text-right">
                  <?php wp_nonce_field('duplicate_pm_group'); ?>
                        <!-- <input type="checkbox" name="notify_users" id="notify_users"  value="1" /> <label for="notify_users"> <?php //esc_html_e('Also notify users who are members of this group.'); ?> </label>    -->  
                  <input type="submit" value="<?php esc_attr_e('Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?>" name="duplicate" id="duplicate" />
                  </div>
                    </div>
             </form>
            
        </div>
        
        
        </div>



    </div>
</div>

<!--Duplicate Group Modal End--->

<script>

jQuery(function() {
//  jQuery('.pmagic .pmagic-cards .pm-card label').on( 'click', function() {
//    jQuery(this).parent().toggleClass('ispgbox-checked');
//    });

jQuery('.pmagic .pmagic-cards .pm-card input[type=checkbox]').on( 'change', function(){
    if(jQuery(this).is(":checked")) {
        jQuery(this).parent(this).parent(this).addClass('ispgbox-checked');               
    } else {
         jQuery(this).parent(this).parent(this).removeClass('ispgbox-checked');       
    }
});

pgCardUserImages();

var pgAddMembersSearch = jQuery('#pg-add-members-search');
var pgAddMembersSelect = jQuery('#pg_add_members');
if (pgAddMembersSearch.length && pgAddMembersSelect.length) {
    pgAddMembersSearch.on('input', function () {
        var query = jQuery(this).val().toLowerCase();
        pgAddMembersSelect.find('option').each(function () {
            var option = jQuery(this);
            if (option.is(':selected')) {
                option.prop('hidden', false);
                return;
            }
            var text = option.text().toLowerCase();
            option.prop('hidden', query && text.indexOf(query) === -1);
        });
    });
}


});


function pgCardUserImages() {
    jQuery(".pg-box-group-users a").each(function () {
        var img = jQuery(this);
        setTimeout(function(){
        jQuery(img).addClass("pg_img_roll");  
        }, 800);
    });
}

//  jQuery('.pmagic .pmagic-cards .pm-card label').on( 'click', function () {
//      jQuery(this).parent().removeClass('ispgbox-checked');
//      jQuery(this).parent(this).addClass('ispgbox-checked');
//  });

</script>
