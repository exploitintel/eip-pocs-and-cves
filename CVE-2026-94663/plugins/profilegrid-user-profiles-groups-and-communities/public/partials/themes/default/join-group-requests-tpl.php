<div class="pg-group-setting-blog">
    <input type="hidden" id="pg-groupid" name="pg-groupid" value="<?php echo esc_attr($gid); ?>" />
    <div id="pg-group-setting-request-batch" class="pg-group-setting-request-batch pm-dbfl pm-pad10" style="display:none;">
         <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('group','decline_request_bulk','<?php echo esc_attr($gid);?>')" class="pm-remove"><?php esc_html_e('Decline','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('group','accept_request_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Accept','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('group','message_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Message','profilegrid-user-profiles-groups-and-communities');?></a></div>
    </div>
    <div class="pg-group-setting-head pm-dbfl" id="pg-members-setting-head">
        <div class="pg-group-sorting-ls pg-members-sortby pm-difl ">
            <div class="pg-sortby-alpha pm-difl">
                <span class="pg-group-sorting-title pm-difl"><?php esc_html_e("Sort by","profilegrid-user-profiles-groups-and-communities");?></span>
            <span class="pg-sort-dropdown pm-border pm-difl">
                <select class="pg-custom-select" name="request_sort_by" id="request_sort_by" onchange="pm_get_all_requests_from_group(1)">
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
                
            
        </div>
      
        <div class="pg-group-sorting-rs pm-difr">
            
            <div class="pm-difl pg-members-sortby">&nbsp;</div>
            <div class="pm-difl pg-add-member"><a onclick="pg_edit_blog_popup('member','add_user','','<?php echo esc_attr($gid);?>')"><?php esc_html_e("Add","profilegrid-user-profiles-groups-and-communities");?></a></div> 
           
            <div class=" pg-member-search pm-difl"><input type="text" name="request_search" id="request_search" placeholder="<?php esc_attr_e('Search', 'profilegrid-user-profiles-groups-and-communities'); ?>" onkeyup="pm_get_all_requests_from_group(1)"></div>
        </div>

    </div>

    <div class="" id="pm-edit-group-request-html-container">
        <?php
        $pmrequests = new PM_request;
        $pmrequests->pm_get_all_join_group_requests($gid, $pagenum = 1, $limit = 10);
        ?>
    </div>

</div>



