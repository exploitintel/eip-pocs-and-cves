<?php $dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$current_user = wp_get_current_user();
?>
<div class="pmagic">
  <div class="pm-group-container pm-dbfl">
      
   <div class="pg-user-groups-wrap pm-dbfl">
    <?php
    if(isset($gid_array) && !empty($gid_array)):
    
    foreach($gid_array as $revers_gid) 
    {
            $group = $dbhandler->get_row('GROUPS', $revers_gid);
            $group_url  = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$group->id);
            //$group_url = add_query_arg( 'gid',$group->id, $group_url );
            $registration_url  = $pmrequests->profile_magic_get_frontend_url('pm_registration_page','');
            $registration_url = add_query_arg( 'gid',$group->id, $registration_url );
    ?>
       
           <div id="pg-user-group-box-<?php echo esc_attr($group->id);?>" class="pm-group pg-user-group-box pm-difl pm-border pm-radius5 pm50">
               <div class="pg-user-group-head pm-dbfl pm-pad10 pm-border-bt">
                   <div class="pm-dbfl">
                       <?php if($dbhandler->get_global_option_value('pm_show_user_group_title_links','1')=='1'):?>
                       <a href="<?php echo esc_url($group_url); ?>"><?php echo esc_html($group->group_name); ?></a>
                       <?php else: 
                           echo esc_html($group->group_name);
                           endif; ?>
                       <?php if($dbhandler->get_global_option_value('pm_show_group_card_menu_group_page','1')=='1' || $dbhandler->get_global_option_value('pm_show_group_card_menu_group_wall','1')=='1' || $dbhandler->get_global_option_value('pm_show_group_card_menu_group_photos','1')=='1' || $dbhandler->get_global_option_value('pm_show_group_card_menu_leave_group','1')=='1'):?>
                       <?php if($uid == $current_user->ID && $dbhandler->get_global_option_value('pm_show_user_group_card_menu','1')=='1'):?>
                             <div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
                                 <div class="pg-dropdown-icon"> <i class="fa fa-cog pm-color" aria-hidden="true"></i></div>
                       
                       <ul class="pg-dropdown-menu">
                           <?php if($dbhandler->get_global_option_value('pm_show_group_card_menu_group_page','1')=='1'): ?>
                           <li><a href="<?php echo esc_url($group_url);?>"><?php esc_html_e('Group Page','profilegrid-user-profiles-groups-and-communities');?></a></li>
                           <?php endif; ?>
                           
                           <?php if(class_exists('Profilegrid_Group_Wall') && $dbhandler->get_global_option_value('pm_enable_wall','0')=='1' && $dbhandler->get_global_option_value('pm_show_group_card_menu_group_wall','1')=='1'):?>
                           <li><a href="<?php echo esc_url($group_url);?>#pg-groupwalls"><?php esc_html_e('Group Wall','profilegrid-user-profiles-groups-and-communities');?></a></li>
                           <?php endif;?>
                           <?php if(class_exists('Profilegrid_Group_photos') && $dbhandler->get_global_option_value('pm_enable_photos','0')=='1' && $dbhandler->get_global_option_value('pm_show_group_card_menu_group_photos','1')=='1'):?>
                           <li><a href="<?php echo esc_url($group_url);?>#pg-group-photos"><?php esc_html_e('Group Photos','profilegrid-user-profiles-groups-and-communities');?></a></li>
                           <?php endif;?>
                           <?php if(class_exists('Profilegrid_Admin_Power') &&  $pmrequests->pg_check_in_single_group_is_user_group_leader($uid,$group->id )):?>
                           <li><a href="<?php echo esc_url($group_url);?>#pg_group_setting"><?php esc_html_e('Manage Group','profilegrid-user-profiles-groups-and-communities');?></a></li>
                           <?php endif;?>
                           <?php if($dbhandler->get_global_option_value('pm_show_group_card_menu_leave_group','1')=='1'): ?>
                           <li><a onclick="pg_edit_blog_popup('group','remove_group','<?php echo esc_attr($uid); ?>','<?php echo esc_attr($group->id); ?>')" class="pm-remove"><?php esc_html_e('Leave Group','profilegrid-user-profiles-groups-and-communities');?></a></li>
                           <?php endif; ?>
                       </ul>
                                
                   </div>
                       <?php endif;?>
                       <?php endif; ?> 
                   </div>
         
               </div>
               <div class="pm-dbfl pm-bg">
                   <div class="pg-user-group-icon pm-difl">
                       <a href="<?php echo esc_url($group_url); ?>"><?php echo wp_kses_post($pmrequests->profile_magic_get_group_icon($group)); ?></a>
                   </div>
                   <div class="pg-user-group-desc pm-difl">
                       <?php
                       $groupdesc = '';
                       if (!empty($group->group_desc) && strlen($group->group_desc) > 150) {
                           $groupdesc = substr($group->group_desc, 0, 150);
                           $groupdesc .= "...";
                       } else {
                           $groupdesc = $group->group_desc;
                       }
                       ?>
                       <div class="pm-dbfl"><?php if(!empty($groupdesc)) { echo wp_kses_post($groupdesc);} ?></div>
                   </div>
               </div>
           </div>

        <?php	
    }
    endif;
    ?>
</div>
<div class="pm_clear"></div>

  </div>
</div>
