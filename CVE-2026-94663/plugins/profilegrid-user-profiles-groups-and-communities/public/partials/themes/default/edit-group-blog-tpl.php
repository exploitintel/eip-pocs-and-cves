
<div class="pg-group-setting-blog">
    <input type="hidden" id="pg-groupid" name="pg-groupid" value="<?php echo esc_attr($gid); ?>" />
    <div id="pg-group-setting-blog-batch" class="pg-group-setting-blog-batch pm-dbfl pm-pad10" style="display:none;">
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('blog','change_status_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Change Status','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('blog','access_control_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Access Control','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link"><a onclick="pg_edit_blog_bulk_popup('blog','add_admin_note_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Add Note','profilegrid-user-profiles-groups-and-communities');?></a></div>
        <div class="pm-difl pg-group-setting-blog-link pm-blog-message-link"><a onclick="pg_edit_blog_bulk_popup('blog','message_bulk','<?php echo esc_attr($gid);?>')"><?php esc_html_e('Message','profilegrid-user-profiles-groups-and-communities');?></a></div>
    </div>
    <div class="pg-group-setting-head pm-dbfl" id="pg-group-setting-head">
        <div class="pg-group-sorting-ls pg-members-sortby pm-difl ">
            <div class="pg-sortby-alpha pm-difl">
                <span class="pg-group-sorting-title pm-difl"><?php esc_html_e("Sort by","profilegrid-user-profiles-groups-and-communities");?></span>
                <span class="pg-sort-dropdown pm-border pm-difl">
                <select class="pg-custom-select" name="blog_sort_by" id="blog_sort_by" onchange="pm_get_all_user_blogs_from_group(1)">
                    <option value="title_asc"><?php esc_html_e('Alphabetically A-Z', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="title_desc"><?php esc_html_e('Alphabetically Z-A', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="modified_desc"><?php esc_html_e('Last Modified First', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="modified_asc"><?php esc_html_e('Oldest Modified First', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    <option value="pending_post"><?php esc_html_e('Pending Review', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                </select>
            </span>
            </div>
            
        </div>
     
        <div class="pg-group-sorting-rs pm-difr">
            <div class="pm-difl pg-add-member"><a href="">&nbsp;</a></div> 
            <div class="pm-difl pg-members-sortby">
                <span class="pg-sort-dropdown pm-border">
                    <select class="pg-custom-select" id="blog_search_in" name="blog_search_in" onchange="pm_get_all_user_blogs_from_group(1)">
                        <option value="post_title"><?php esc_html_e('Post Title', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                        <option value="author_name"><?php esc_html_e('Author Name', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                        <option value="post_tag"><?php esc_html_e('Post Tags', 'profilegrid-user-profiles-groups-and-communities'); ?></option>
                    </select>
                </span>

            </div>
            <div class="pg-member-search pm-difl"><input type="text" name="blog_search" id="blog_search" placeholder="<?php esc_attr_e('Search', 'profilegrid-user-profiles-groups-and-communities'); ?>" onkeyup="pm_get_all_user_blogs_from_group(1)"></div>
        </div>

    </div>
    
    <div class="" id="pm-edit-group-blog-html-container">
        <?php
        $pmrequests = new PM_request;
        $pmrequests->pm_get_all_group_blogs($gid, $pagenum = 1, $limit = 10);
        ?>
    </div>

</div>



