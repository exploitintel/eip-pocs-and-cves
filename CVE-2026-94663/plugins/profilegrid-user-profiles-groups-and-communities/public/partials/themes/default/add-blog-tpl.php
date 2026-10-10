<div class="pmagic">
<!-----Form Starts----->
  <form class="pmagic-form pm-dbfl pg-group-reg-form" method="post" action="" id="pm_add_blog_post" name="pm_add_blog_post" onsubmit="return profile_magic_blogpost_validation()" enctype="multipart/form-data">
   
       <div class="pmrow">        
        <div class="pm-col">
        <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                    <label for="blog_title"><?php esc_html_e('Title','profilegrid-user-profiles-groups-and-communities');?><sup class="pm_estric">*</sup></label>
              </div>
              <div class="pm-field-input pm_required">
                    <input title="Enter your title" type="text" class="" maxlength="" value="" id="blog_title" name="blog_title" placeholder="">
                    <div class="errortext" style="display:none;"></div>
              </div>
            </div>
            
        </div>
      
      <div class="pmrow">        
        <div class="pm-col">
        <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                    <label for="blog_description"><?php esc_html_e('Description','profilegrid-user-profiles-groups-and-communities');?></label>
              </div>
              <div class="pm-field-input">
                  <?php wp_editor('', 'blog_description',$settings);?>                
                    <div class="errortext" style="display:none;"></div>
              </div>
            </div>
        </div>
       <?php if($dbhandler->get_global_option_value('pm_blog_tags','0')==1):?>
      <div class="pmrow">        
        <div class="pm-col">
        <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                    <label for="blog_tags"><?php esc_html_e('Tags','profilegrid-user-profiles-groups-and-communities');?></label>
              </div>
              <div class="pm-field-input">
                   <input type="text" value="" tabindex="5" size="16" name="blog_tags" id="blog_tags" />
                    <div class="errortext" style="display:none;"></div>
              </div>
            </div>
            
        </div>
      <?php endif;?>
      <?php if($dbhandler->get_global_option_value('pm_blog_feature_image','0')==1):?>
       <div class="pmrow">        
        <div class="pm-col">
        <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="blog_image"><?php esc_html_e('Image','profilegrid-user-profiles-groups-and-communities');?></label>
            </div>
            <div class="pm-field-input pm_fileinput">
               <div class="pm_repeat">
                 <input title="" type="file" class="" id="blog_image" name="blog_image" data-filter-placeholder="" />
                  <div class="errortext" style="display:none;"></div>
               </div>
            </div>             
            </div>
        </div>
      <?php endif;?>
      
       <?php if($dbhandler->get_global_option_value('pm_blog_privacy_level','0')==1):
           $default_privacy_level = absint($dbhandler->get_global_option_value('pm_default_privacy_level','1'));
           ?>
        
       <div class="pmrow">        
        <div class="pm-col">
        <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pm_content_access_blog_1"><?php esc_html_e('Content Privacy','profilegrid-user-profiles-groups-and-communities');?></label>
            </div>
            <div class="pm-field-input">
               <div class="pmradio">
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_blog_1" value="1" <?php checked($default_privacy_level, 1);?>/>
                <?php esc_html_e('Content accessible to Everyone','profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_blog_2" value="2" <?php checked($default_privacy_level, 2);?>/>
                    <?php esc_html_e('Content accessible to Logged In Users','profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_blog_3" value="3" <?php checked($default_privacy_level, 3);?>/>
                <?php esc_html_e('Content accessible to My Friends','profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                   <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_blog_5" value="5" <?php checked($default_privacy_level, 5);?>/>
                    <?php esc_html_e('Content accessible to my fellow Group Members','profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                    <div class="pm-radio-option">
                    <input type="radio" name="pm_content_access" id="pm_content_access_blog_4" value="4" <?php checked($default_privacy_level, 4);?>/>
                <?php esc_html_e('Content accessible only to me','profilegrid-user-profiles-groups-and-communities' ); ?>
                   </div>
                </div>
            </div>             
            </div>
        </div>
      <?php endif;?>
      
      <div class="all_errors" style="display:none;"></div>
        <div class="buttonarea pm-full-width-container">
            <input type="submit" value="<?php esc_attr_e('Submit','profilegrid-user-profiles-groups-and-communities');?>" name="pg_blog_submit">
		<?php
    $basic_function=  new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
    $basic_function->pm_render_nonce_field( 'pg_blog_post' ); 
     ?>
    </div>
  </form>
</div>
