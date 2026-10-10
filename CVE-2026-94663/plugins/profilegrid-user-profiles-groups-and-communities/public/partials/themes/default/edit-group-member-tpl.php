<div class="pg-group-setting-blog">
    <input type="hidden" id="pg-groupid" name="pg-groupid" value="<?php echo esc_attr($gid); ?>" />
    <div id="pg-group-setting-member-batch" class="pg-group-setting-member-batch pm-dbfl pm-pad10" style="display:none;">
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('member','remove_user_bulk','<?php echo esc_attr($gid);?>')" class="pm-remove"><?php esc_html_e('Remove','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link pm-suspend-link"><a onclick="pg_edit_blog_bulk_popup('member','deactivate_user_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Suspend','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link pm-activate-link"><a onclick="pg_activate_bulk_users('<?php echo esc_attr($gid);?>')"><?php esc_html_e('Activate','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link pm-message-link"><a onclick="pg_edit_blog_bulk_popup('member','message_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Message','profilegrid-user-profiles-groups-and-communities');?></a></div>
    </div>
    <div class="pg-group-setting-head pm-dbfl" id="pg-members-setting-head">
        <div class="pg-group-sorting-ls pg-members-sortby pm-difl ">
            <div class="pg-sortby-alpha pm-difl">
                <span class="pg-group-sorting-title pm-difl"><?php esc_html_e("Sort by","profilegrid-user-profiles-groups-and-communities");?></span>
            <span class="pg-sort-dropdown pm-border pm-difl">
                <select class="pg-custom-select" name="member_sort_by" id="member_sort_by" onchange="pm_get_all_users_from_group(1)">
                    <option value="first_name_asc"><?php esc_html_e('First Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="first_name_desc"><?php esc_html_e('First Name Alphabetically Z - A', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="last_name_asc"><?php esc_html_e('Last Name Alphabetically A - Z', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="last_name_desc"><?php esc_html_e('Last Name Alphabetically Z- A', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="latest_first"><?php esc_html_e('Latest First', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="oldest_first"><?php esc_html_e('Oldest First', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="suspended"><?php esc_html_e('Suspended', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                </select>
            </span>
                </div>
            <?php do_action('profilegrid_additional_option_for_manage_members_for_admin',$gid); ?>
        </div>
      
        <div class="pg-group-sorting-rs pm-difr">
            <div class="pm-difl pg-add-member"><a onclick="pg_edit_blog_popup('member','add_user','','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Add','profilegrid-user-profiles-groups-and-communities');?></a></div> 
            <div class="pm-difl pg-members-sortby">
                <span class="pg-sort-dropdown pm-border">
                    <select class="pg-custom-select" id="member_search_in" name="member_search_in" onchange="pm_get_all_users_from_group(1)">
                        <option value=""><?php esc_html_e('Select a Field','profilegrid-user-profiles-groups-and-communities');?></option>
                        <?php
                        $fields = $dbhandler->get_all_result('FIELDS', $column = '*', array('associate_group' => $gid), 'results', 0, false, $sort_by = 'ordering');
                        if(isset($fields) && !empty($fields)):
                        foreach($fields as $field)
                        {
                            $exclude = array('file','user_avatar','heading','paragraph','confirm_pass','user_pass');
                            if (!in_array($field->field_type, $exclude))
                            {
                                echo '<option value="'.esc_attr($field->field_key).'">'.esc_html($field->field_name).'</option>';	
                            }
                        }
                        endif;
                        ?>
                    </select>
                </span>

            </div>
            <div class=" pg-member-search pm-difl"><input type="text" name="member_search" id="member_search" placeholder="<?php esc_attr_e('Search', 'profilegrid-user-profiles-groups-and-communities'); ?>" onkeyup="pm_get_all_users_from_group(1)"></div>
        </div>

    </div>

    <div class="" id="pm-edit-group-member-html-container">
        <?php
        $pmrequests = new PM_request;
        $pmrequests->pm_get_all_users_from_group($gid, $pagenum = 1, $limit = 10);
        ?>
    </div>

</div>



