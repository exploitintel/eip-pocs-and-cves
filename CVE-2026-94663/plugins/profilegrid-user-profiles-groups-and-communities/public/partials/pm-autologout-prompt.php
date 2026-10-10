<?php 
$path = plugin_dir_url(__FILE__);
$dbhandler = new PM_DBhandler;
$prompt_message = $dbhandler->get_global_option_value('pm_logout_prompt_text',__('Do you want to logout?','profilegrid-user-profiles-groups-and-communities'));
?>

<div id="pm-autologout-dialog" style="display:none;">
    <div class="pm-popup-mask"></div>
    <div class="pm-popup-container pm-update-image-container pm-radius5">
      <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
          
      </div>
      <div class="pm-popup-image pm-dbfl pm-bg pm-pad10"> 
        <div class="pm-autologout-message"><?php echo wp_kses_post($prompt_message);?></div>  
        <div class="pm-popup-action">
            <div class="pm-modal-footer">
                <button type="button" id="pg_btn_auto_logout" class="btn btn-default" onclick="pg_auto_logout_redirect()"><?php esc_html_e('Logout','profilegrid-user-profiles-groups-and-communities');?></button>
                <button type="button" id="pg_btn_auto_logout_continue" class="btn btn-primary" onclick="pg_auto_logout_prompt_close()"><?php esc_html_e('Continue','profilegrid-user-profiles-groups-and-communities');?></button>
            </div>
        </div>
        
      </div>
    </div>
  </div>