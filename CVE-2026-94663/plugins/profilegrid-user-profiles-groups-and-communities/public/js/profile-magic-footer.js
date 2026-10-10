function updateCoords(c)
{
  jQuery('#x').val(c.x);
  jQuery('#y').val(c.y);
  jQuery('#w').val(c.w);
  jQuery('#h').val(c.h);
};

function updateCoverCoords(c)
{
  jQuery('#cx').val(c.x);
  jQuery('#cy').val(c.y);
  jQuery('#cw').val(c.w);
  jQuery('#ch').val(c.h);
};

function checkCoords()
{
  if (parseInt(jQuery('#w').val())) return true;
  alert(pm_error_object.crop_alert_error);
  return false;
};

function checkCoverCoords()
{
  if (parseInt(jQuery('#cw').val())) return true;
  alert(pm_error_object.crop_alert_error);
  return false;
};

 function pm_delete_notification(id){
    var data = {action: 'pm_delete_notification', 'id': id, 'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
         //   console.log("Delete successful");
            jQuery("#notif_"+id).fadeOut(300,function(){jQuery(this).remove();});
        }
    });
}

function pm_load_more_notification(loadnum){
      jQuery("#pm_load_more_notif").remove();
      var data = {action: 'pm_load_more_notification','loadnum':loadnum,'nonce': pm_ajax_object.nonce};
       jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('#pm_notification_view_area').append(response);
        }
    });
  
}

function pm_read_all_notification(){
      var data = {action: 'pm_read_all_notification','nonce': pm_ajax_object.nonce};
       jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
         //   jQuery('#pm_notification_view_area').append(response);
        }
    });
   
}
function read_notification(){
    jQuery("#unread_notification_count").html('');   
    jQuery("#unread_notification_count").removeClass("thread-count-show"); 
    pm_read_all_notification();
    refresh_notification();
    
}

function refresh_notification(){
  //  console.log("refreshing notification");
     var data = {action: 'pm_refresh_notification','nonce': pm_ajax_object.nonce};
       jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('#pm_notification_view_area').html('');
            jQuery('#pm_notification_view_area').append(response);
        }
    });
}

function pm_get_dom_color()
{
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
      jQuery(".pm-color").css('color', pmDomColor);
      return pmDomColor;
      
}

function pg_toggle_dropdown_menu(a)
{
    jQuery(a).find('.pg-dropdown-menu').slideToggle('fast');
    jQuery('.pg-setting-dropdown').not(a).children(".pg-dropdown-menu").slideUp('fast');
        
}

(function( $ ) {
   $(document).on("click", function(event){
        var $trigger = $(".pg-setting-dropdown");
        if($trigger !== event.target && !$trigger.has(event.target).length){
            $(".pg-dropdown-menu").slideUp("fast");
        }            
    });
})(jQuery);

function pg_checked_all_blogs(a)
{
    if (jQuery(a).is(':checked')) 
    {
        jQuery('input.pg-blog-checked:checkbox').attr('checked', true);
        jQuery('.pg-group-setting-blog-batch').show();
    } 
    else 
    {
        jQuery('input.pg-blog-checked:checkbox').attr('checked', false);
        jQuery('.pg-group-setting-blog-batch').hide();
    }
}

function pg_checked_all_member(a)
{
    if (jQuery(a).is(':checked')) 
    {
        var activeids = [];
        jQuery('input.pg-member-checked:checkbox').attr('checked', true);
        jQuery('input.pg-member-checked.active[type="checkbox"]:checked').each(function() {
            activeids.push(jQuery(this).val());
        });

        if(activeids.length === 0)
        {
            jQuery('.pm-suspend-link').addClass('pg-setting-disabled');
        }
        jQuery('.pg-group-setting-member-batch').show();
    } 
    else 
    {
        jQuery('input.pg-member-checked:checkbox').attr('checked', false);
        jQuery('.pg-group-setting-member-batch').hide();
    }
}

function pg_checked_all_requests(a)
{
    if (jQuery(a).is(':checked')) 
    {
        jQuery('input.pg-request-checked:checkbox').attr('checked', true);
        jQuery('.pg-group-setting-request-batch').show();
    } 
    else 
    {
        jQuery('input.pg-request-checked:checkbox').attr('checked', false);
        jQuery('.pg-group-setting-request-batch').hide();
    }
}

function pg_select_blog_posts()
{
    var type = jQuery('input[name="pm_blog_select_type"]:checked').val();
    jQuery('#pg_blog_select_type').val(type);
    jQuery('#pm-edit-group-popup, .pm-popup-mask, .pg-blog-dialog-mask').toggle();
}
function pg_edit_blog_popup(tab,type,id,gid)
{
    jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
       
    jQuery('#pm-edit-group-popup, .pm-popup-mask, .pg-blog-dialog-mask').toggle();
    var data = {action: 'pm_edit_group_popup_html',tab:tab,type:type,id:id,gid:gid,'nonce': pm_ajax_object.nonce};
    
       jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            bgcolor = pmDomColor.replace(')', ',0.2)');
            jQuery('#pg_edit_group_html_container').html(response);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('background-color', bgcolor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('border-color', pmDomColor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('color', pmDomColor);
            jQuery( ".pg-update-message svg" ).css('fill', pmDomColor); 
            jQuery('#pm-edit-group-popup .pm-popup-close, .pg-group-setting-close-btn ').on('click', function(e) {
                jQuery('.pm-popup-mask, #pm-edit-group-popup, .pg-blog-dialog-mask').hide();
            });
        }
    });
    
}

function pg_edit_popup_close()
{
   jQuery('.pm-popup-mask, #pm-edit-group-popup, .pm-popup-close').hide();
}

function pg_edit_blog_bulk_popup(tab,type,gid)
{
             
    var ids = [];
    if(tab=='blog')
    {
        if(type == 'message_bulk')
        {
            jQuery('input.pg-blog-checked.active[type="checkbox"]:checked').each(function() {
                ids.push(jQuery(this).val());
            });
        }
        else
        {
            jQuery('input.pg-blog-checked[type="checkbox"]:checked').each(function() {
                ids.push(jQuery(this).val());
            }); 
        }
     
    }
    else if(tab=='group')
    {
       jQuery('input.pg-request-checked[type="checkbox"]:checked').each(function() {
       ids.push(jQuery(this).val());
     }); 
    }
    else
    {
        if(type == 'deactivate_user_bulk' || type == 'message_bulk')
        {
            jQuery('input.pg-member-checked.active[type="checkbox"]:checked').each(function() {
                ids.push(jQuery(this).val());
            });
        }
        else
        {
            jQuery('input.pg-member-checked[type="checkbox"]:checked').each(function() {
                ids.push(jQuery(this).val());
            }); 
        }
    }
    jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
       
    jQuery('#pm-edit-group-popup, .pm-popup-mask, .pg-blog-dialog-mask').toggle();
    
    var data = {action: 'pm_edit_group_popup_html',tab:tab,type:type,gid:gid,id:ids,'nonce': pm_ajax_object.nonce};
       jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            bgcolor = pmDomColor.replace(')', ',0.2)');
            jQuery('#pg_edit_group_html_container').html(response);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('background-color', bgcolor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('border-color', pmDomColor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('color', pmDomColor);
            jQuery('#pm-edit-group-popup .pm-popup-close, .pg-group-setting-close-btn ').on('click', function(e) {
             jQuery('.pm-popup-mask, #pm-edit-group-popup').hide();
             jQuery('input.pg-member-checked:checkbox').attr('checked', false);
             jQuery('input.pg-request-checked:checkbox').attr('checked', false);
             jQuery('input.pg-blog-checked:checkbox').attr('checked', false);
             jQuery('input.pg-member-checked-all:checkbox').attr('checked', false);
             jQuery('input.pg-requests-checked-all:checkbox').attr('checked', false);
            });
        }
    });
}

function pg_submit_post_status()
{
    
    jQuery("#pg_change_post_status_form").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                      pm_get_all_user_blogs_from_group(1);
                }
        }).trigger( 'submit' );
}

function pg_submit_post_access_content()
{
    jQuery("#pg_change_post_content_access_level").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                       
                }
        }).trigger( 'submit' );
}

function pg_submit_edit_blog_post()
{
    tinyMCE.triggerSave();
    var title = jQuery.trim(jQuery('#blog_title').val());
    if(title!='')
    {
        jQuery('#blog_title').parent('div').children('.errortext').html('');
        jQuery('#blog_title').parent('div').children('.errortext').hide();
        jQuery("#pg_edit_blog_post").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                       pm_get_all_user_blogs_from_group(1);
                }
        }).trigger( 'submit' );
    }
    else
    {
        jQuery('#blog_title').parent('div').children('.errortext').html(pm_error_object.required_field);
        jQuery('#blog_title').parent('div').children('.errortext').show();
    }
}

function pg_submit_post_admin_note_content()
{
    jQuery('#pg_add_admin_note .errortext').html('');
    jQuery('#pg_add_admin_note .errortext').hide();
    var content = jQuery('#pm_admin_note_content').val();
    if(content.trim()!='')
    {
    jQuery("#pg_add_admin_note").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                    pm_get_all_user_blogs_from_group(1);
                }
        }).trigger( 'submit' );
    }
    else
    {
        jQuery('#pg_add_admin_note .errortext').html(pm_error_object.admin_note_error);
        jQuery('#pg_add_admin_note .errortext').show();
    }
}

function pg_submit_delete_admin_note_content()
{
    var data ={delete_note: '1'};
 jQuery("#pg_add_admin_note").ajaxForm({
        target: '#pg_edit_group_html_container',
        data: data,
        success:function() { 
                   
                }
        }).trigger( 'submit' );
}

function pm_delete_admin_note()
{
    jQuery("#pg_delete_admin_note").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                      pm_get_all_user_blogs_from_group(1); 
                }
    }).trigger( 'submit' );
}

function pg_submit_author_message()
{
    jQuery('#pg_send_author_message .errortext').html('');
    jQuery('#pg_send_author_message .errortext').hide();
    var content = jQuery('#pm_author_message').val();
    if(content.trim()!='')
    {
    jQuery("#pg_send_author_message").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                      pm_get_all_user_blogs_from_group(1);
                      pm_get_all_users_from_group('1');
                }
        }).trigger( 'submit' );
    }
    else
    {
        jQuery('#pg_send_author_message .errortext').html('<div class="pg-failed-message pm-dbfl">' + pm_error_object.empty_message_error + '</div>');
        jQuery('#pg_send_author_message .errortext').show();
    }
}
function pg_count_left_charactors(entrance,exit,text,characters) 
{  
    var entranceObj=document.getElementById(entrance);  
    var exitObj=document.getElementById(exit);  
    var length=characters - entranceObj.value.length;  
    if(length <= 0) {  
    length=0;  
    text='<span class="disable"> '+text+' <\/span>';  
    entranceObj.value=entranceObj.value.substr(0,characters);  
    }  
    exitObj.innerHTML = text.replace("{CHAR}",length);  
}

function pm_get_all_user_blogs_from_group(pagenum)
{
    var gid = jQuery('#pg-groupid').val();
    var search_in = jQuery('#blog_search_in').find(":selected").val();
    var sortby = jQuery('#blog_sort_by').find(":selected").val();
    var search = jQuery('#blog_search').val();
    var limit = jQuery('#pg_blog_sort_limit').val();
    //pm_get_pending_post_from_group(gid);
    var data = {action: 'pm_get_all_user_blogs_from_group',gid:gid,sortby:sortby,search_in:search_in,search:search,pagenum:pagenum,limit:limit,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pm-edit-group-blog-html-container').html(response);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        jQuery( ".pg-update-message svg" ).css('fill', pmDomColor); 
    });
}

function pg_invite_user()
{
    jQuery('.errortext').html('');
    jQuery('.errortext').hide();
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    var count = 0;
    jQuery('#pg_add_user .pm_repeat').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
                count++;
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
                else
                {
                    var email = jQuery(this).children('input').val();
                    var isemail = regex.test(email);
                    if (isemail == false && email != "") {
                            jQuery(this).children('input').addClass('warning');
                            jQuery(this).children('.errortext').html(pm_error_object.valid_email);
                            jQuery(this).children('.errortext').show();
                    }
                }
                
                if(count > 10)
                {
                    jQuery(this).children('input').addClass('warning');
                    jQuery(this).children('.errortext').html(pm_error_object.invite_limit_error);
                    jQuery(this).children('.errortext').show();
                }
	});
        
        
     var b = '';
	 jQuery('#pg_add_user .errortext').each(function () {
		var a = jQuery(this).html();
		b = a + b;
	});   
        
 
    if(b=='')
    {
        jQuery("#pg_add_user").ajaxForm({
            target: '#pg_edit_group_html_container',
            success:function() { 

                    }
        }).trigger( 'submit' );
    }
}

function pm_remove_user_from_group()
{
    jQuery("#pg_remove_user_in_group").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                      pm_get_all_users_from_group('1'); 
                }
    }).trigger( 'submit' );
}

function pm_remove_group_from_user()
{
    jQuery("#pg_remove_group_in_user_profile").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                       
                }
    }).trigger( 'submit' );
}

function pg_activate_user(uid,gid)
{
    var data = {action: 'pm_activate_user_in_group',uid:uid,gid:gid,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        pm_get_all_users_from_group('1');
    });
}

function pg_activate_bulk_users(gid)
{
    var ids = [];
    jQuery('input.pg-member-checked.inactive[type="checkbox"]:checked').each(function() {
       ids.push(jQuery(this).val());
     }); 
    var data = {action: 'pm_activate_user_in_group',uid:ids,gid:gid,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function () {
        pm_get_all_users_from_group('1');
    });
}

function pm_get_all_users_from_group(pagenum)
{
    var gid = jQuery('#pg-groupid').val();
    var search_in = jQuery('#member_search_in').find(":selected").val();
    var sortby = jQuery('#member_sort_by').find(":selected").val();
    var search = jQuery('#member_search').val();
    //var limit = jQuery('#pg_member_sort_limit').val();
    var limit = '10';
    var data = {action: 'pm_get_all_users_from_group',gid:gid,sortby:sortby,search_in:search_in,search:search,pagenum:pagenum,limit:limit,nonce: pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pm-edit-group-member-html-container').html(response);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        large_textarea_collasable();
    });
}

function pm_get_all_users_from_group_advanced_group(pagenum)
{
    var gid = jQuery('#pg-groupid').val();
    var search_in = jQuery('#member_search_in').find(":selected").val();
    var sortby = jQuery('#member_sort_by').find(":selected").val();
    var search = jQuery('#member_search').val();
    var limit = jQuery('#pg_member_per_page').val();
    if(limit=='')
    {
        limit = '10';
    }
    
    var data = {action: 'pm_get_all_users_from_group',gid:gid,sortby:sortby,search_in:search_in,search:search,pagenum:pagenum,limit:limit,nonce: pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pm-edit-group-member-html-container').html(response);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
    });
}

function pm_get_all_users_from_group_grid_view(pagenum,view)
{
    var gid = jQuery('#pg-gid').val();
    var search_in = jQuery('#member_search_in_grid').find(":selected").val();
    var sortby = jQuery('#member_sort_by_grid').find(":selected").val();
    var search = jQuery('#member_search_grid').val();
    //var limit = jQuery('#pg_member_sort_limit').val();
    var limit = '10';
    console.log(gid);
    jQuery("#pg_members_grid_view").html('<div class="pm-loader"></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
    
    var data = {action: 'pm_get_all_users_from_group',gid:gid,sortby:sortby,search_in:search_in,search:search,pagenum:pagenum,limit:limit,view:view,nonce: pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pg_members_grid_view').html(response);
        pg_primary_ajustment_during_ajax();
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        large_textarea_collasable();
    });
   
}

function pm_get_all_groups(pagenum)
{
    var view = jQuery("input[name='pg_groups_view']:checked").val();
    var sortby = jQuery('#group_sort_by').find(":selected").val();
    var search = jQuery('#group_search').val();
    var $container = jQuery('.pm-all-group-container');
    var limit = $container.data('pg-groups-limit');
    var include_groups = $container.data('pg-groups-include');
    var exclude_groups = $container.data('pg-groups-exclude');
    var group_type = $container.data('pg-groups-type');
    var paid = $container.data('pg-groups-paid');
    var default_view = $container.data('pg-groups-view');
    var default_sort = $container.data('pg-groups-sort');
    if(limit === undefined || limit === null || limit === '') {
        limit = '10';
    }
    if(view === undefined || view === null || view === '') {
        view = default_view ? default_view : 'grid';
    }
    if(sortby === undefined || sortby === null || sortby === '') {
        sortby = default_sort ? default_sort : 'newest';
    }

    jQuery(".pm-all-group-container").html('<div class="pm-loader"></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
    jQuery( ".pg-select-list-view svg" ).css('fill','');
    jQuery( ".pmagic .pg-group-filters-head .pg-sort-view input:checked+label svg" ).css('fill', pmDomColor);

    var data = {action: 'pm_get_all_groups',sortby:sortby,view:view,search:search,pagenum:pagenum,limit:limit,include:include_groups,exclude:exclude_groups,type:group_type,paid:paid};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('.pm-all-group-container').html(response);
        pg_primary_ajustment_during_ajax();
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery(".pg-select-list-view").removeClass('pg-select-list-view');
        jQuery("input[name='pg_groups_view']:checked").parent('span').children('label').addClass('pg-select-list-view');
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
    });
   
}

function pg_primary_ajustment_during_ajax()
{
     // Sets all user cards equal height
    jQuery('.pmagic').each(function()
    {  
        var highestBox = 0;
        jQuery(this).find('.pm-user-card').each(function(){
            if(jQuery(this).height() > highestBox){  
                highestBox = jQuery(this).height();  
            }
        })
        jQuery(this).find('.pm-user-card.pm50, .pm-user-card.pm33').height(highestBox);
    });
    
    
    var profileArea = jQuery('.pmagic').innerWidth();
    jQuery('span#pm-cover-image-width').text(profileArea);
    jQuery('.pm-cover-image').children('img').css('width', profileArea);
    if (profileArea < 550) {
        jQuery('.pm-user-card, .pm-group, .pm-section').addClass('pm100');
    } else if (profileArea < 900) {
        jQuery('.pm-user-card, .pm-group').addClass('pm50');
    } else if (profileArea >= 900) {
        jQuery('.pm-user-card, .pm-group').addClass('pm33');
    }
    
}

function pm_get_pending_post_from_group(gid)
{
    var data = {action: 'pm_get_pending_post_from_group',gid:gid};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pg_show_pending_post').html(response);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pmagic #pg_show_pending_post .pg-pending-posts").css('background-color', pmDomColor);
    });
}

function pm_deactivate_user_from_group()
{
    jQuery("#pg_deactivate_user_in_group").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                       pm_get_all_users_from_group('1');
                }
    }).trigger( 'submit' );
}

function pg_password_auto_generate(id)
{
    var data = {action: 'pm_generate_auto_password'};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#'+id).val(response);
        pg_check_password_strenth();
    });
}

function pm_reset_user_password()
{
    jQuery("#pg_reset_user_password").ajaxForm({
        target: '#pg_edit_group_html_container',
        success:function() { 
                       
                }
    }).trigger( 'submit' );
}

function pm_show_hide_batch_operation(tab)
{
    if(tab=='blog')
    {
         var blogactiveids = [];
        if(jQuery('.pg-blog-checked:checked').length > 0)
        {
            jQuery('input.pg-blog-checked.active[type="checkbox"]:checked').each(function() {
                blogactiveids.push(jQuery(this).val());
            });

            if(blogactiveids.length === 0)
            {
                jQuery('.pm-blog-message-link').addClass('pg-setting-disabled');
            }
            else
            {
                jQuery('.pm-blog-message-link').removeClass('pg-setting-disabled');
            }
            jQuery('#pg-group-setting-blog-batch').show();
        }
        else
        {
            jQuery('#pg-group-setting-blog-batch').hide();
        }
    }
    
    if(tab=='requests')
    {
        if(jQuery('.pg-request-checked:checked').length > 0)
        {
            jQuery('#pg-group-setting-request-batch').show();
        }
        else
        {
            jQuery('#pg-group-setting-request-batch').hide();
        }
    }
    
    if(tab=='admins')
    {
        if(jQuery('.pg-admin-checked:checked').length > 0)
        {
            jQuery('#pg-group-setting-admins-batch').show();
        }
        else
        {
            jQuery('#pg-group-setting-admins-batch').hide();
        }
    }
    else
    {
        var activeids = [];
        var inactiveids = [];
        if(jQuery('.pg-member-checked:checked').length > 0)
        {
            jQuery('input.pg-member-checked.active[type="checkbox"]:checked').each(function() {
                activeids.push(jQuery(this).val());
            });

            if(activeids.length === 0)
            {
                jQuery('.pm-suspend-link').addClass('pg-setting-disabled');
                jQuery('.pm-message-link').addClass('pg-setting-disabled');
            }
            else
            {
                jQuery('.pm-suspend-link').removeClass('pg-setting-disabled');
                jQuery('.pm-message-link').removeClass('pg-setting-disabled');
            }
            
            jQuery('input.pg-member-checked.inactive[type="checkbox"]:checked').each(function() {
                inactiveids.push(jQuery(this).val());
            });

            if(inactiveids.length === 0)
            {
                jQuery('.pm-activate-link').addClass('pg-setting-disabled');
            }
            else
            {
                jQuery('.pm-activate-link').removeClass('pg-setting-disabled');
            }
            
            jQuery('#pg-group-setting-member-batch').show();
        }
        else
        {
            jQuery('#pg-group-setting-member-batch').hide();
        }
    }
}

function pg_decline_join_request(uid,gid)
{
    jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
    var data = {action: 'pm_decline_join_group_request',gid:gid,uid:uid,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
       
         window.location.reload(true);
        });
}

function pg_approve_join_request(uid,gid)
{
    jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
    var data = {action: 'pm_approve_join_group_request',gid:gid,uid:uid, 'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response === 'success')
        {
            window.location.reload(true);
        }
        else
        {
            jQuery('#pg_edit_group_html_container').html(response);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('background-color', bgcolor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('border-color', pmDomColor);
            jQuery("#pm-edit-group-popup .pg-users-send-box .pm-message-username").css('color', pmDomColor);
            jQuery('#pm-edit-group-popup .pm-popup-close, .pg-group-setting-close-btn ').on('click', function(e) {
             jQuery('.pm-popup-mask, #pm-edit-group-popup').hide();
            });
        }        
    });
}

function pm_get_all_requests_from_group(pagenum)
{
    var gid = jQuery('#pg-groupid').val();
    var sortby = jQuery('#request_sort_by').find(":selected").val();
    var search = jQuery('#request_search').val();
    var data = {action: 'pm_get_all_requests_from_group',gid:gid,sortby:sortby,search:search,pagenum:pagenum,nonce: pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        jQuery('#pm-edit-group-request-html-container').html(response);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
    });
}

function pm_decline_bulk_join_group_requests()
{
    
    var ids = [];
    var gid = jQuery('#pg-groupid').val();
    jQuery('input.pg-request-checked[type="checkbox"]:checked').each(function() {
       ids.push(jQuery(this).val());
     }); 
     jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
       // alert(ids);
    var data = {action: 'pm_decline_join_group_request',uid:ids,gid:gid,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        //alert(response);
        window.location.reload(true);
    });
}

function pm_approve_bulk_join_group_requests()
{
    
    var ids = [];
    var gid = jQuery('#pg-groupid').val();
    jQuery('input.pg-request-checked[type="checkbox"]:checked').each(function() {
       ids.push(jQuery(this).val());
     }); 
     jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
       // alert(ids);
    var data = {action: 'pm_approve_join_group_request',uid:ids,gid:gid,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        //alert(response);
        window.location.reload(true);
    });
}

function pg_prevent_double_click(form)
{
    jQuery(form).children("input[type='submit']").css('visibility','hidden');
    return true;
}

function large_textarea_collasable(){
    var showTotalChar = 250, showChar = pm_error_object.show_more, hideChar = pm_error_object.show_less;
    jQuery('.pm_collapsable_textarea').each(function() 
    {
        var content = jQuery(this).text();
        if (content.length > showTotalChar) 
        {
            var con = content.substr(0, showTotalChar);
            var hcon = content.substr(showTotalChar, content.length - showTotalChar);
            var txt= con +  '<span class="pm_morectnt"><span>' + hcon + '</span>&nbsp;&nbsp;<a href="" class="pm_showmoretxt">' + showChar + '</a></span>';
            jQuery(this).html(txt);
        }
    });
}

// Begin tabbed menu horizental content script
(function( $ ) {        
$(".mymenu").PGresponsiveMenu();
$('#pg-groupwalls,#pg-group-photos,#pg_group_setting,.pg_custom_tab_content,#bbpress_forum,#pg-woocommerce_purchases,#pg-woocommerce_reviews').addClass('pg-profile-tab-content');

$('li.pm-profile-tab a:first').addClass('active');
$('.pg-profile-tab-content').hide();
$('.pg-profile-tab-content:first').show();

    $('li.pm-profile-tab a').on( 'click', function(){
        var t = $(this).attr('href'); 
        $('li.pm-profile-tab a').removeClass('active');         
        $(this).addClass('active');
        $('.pg-profile-tab-content').hide();
        $(t).find('.pm-section-content:first').show();
        $('li.hideshow ul').hide();
        $(t).fadeIn('slow');
        return false;
    });

if($(this).hasClass('active')){ //this is the start of our condition 
    $('li.pm-profile-tab a').removeClass('active');         
    $(this).addClass('active');
    $('.pg-profile-tab-content').hide();
    $(t).fadeIn('slow');    
}

$('.pm-section-left-panel ul li a:first').addClass('active');
$('.pm-section-right-panel .pm-section-content').hide();
$('.pm-section-right-panel .pm-section-content:first').show();
$('.pm-section-left-panel ul li a').on( 'click', function(){
    var t = $(this).attr('href');
    $('.pm-section-left-panel ul li a').removeClass('active');        
    $(this).addClass('active');
    $('.pm-section-right-panel .pm-section-content').hide();
    $(t).fadeIn('slow');
    
    return false;
});

if($(this).hasClass('active')){ //this is the start of our condition 
    $('.pm-section-left-panel ul li a').removeClass('active');         
    $(this).addClass('active');
    $('.pm-section-right-panel .pm-section-content').hide();
    $(t).fadeIn('slow');    
}

//*---Elements Visibility--- *//

if ($('.pm-no-cover-image')[0]) {
    $('.pm-group-view .pm-header-section').addClass('pm-without-cover-image');
} else {
  $('.pm-group-view .pm-header-section').removeClass('pm-without-cover-image');
}


if ($('.pm-no-profile-image')[0]) {
    $('.pm-group-view .pm-profile-title-header').addClass('pm-without-profile-image');
} else {
  $('.pm-group-view .pm-profile-title-header').removeClass('pm-without-profile-image');
}


if ($('.pm-section-no-left-panel')[0]) {
    $('.pmagic .pm-group-view #pg-about .pm-section').addClass('pg-left-pannel-hide');
} else {
  $('.pmagic .pm-group-view #pg-about .pm-section').removeClass('pg-left-pannel-hide');
}

if ($('.pm-no-blog-img-wrap')[0]) {
    $('.pmagic .pm-group-view #pg-blog #pg-blog-container .pm-blog-post-wrap').addClass('pg-blog-image-hide');
} else {
  $('.pmagic .pm-group-view #pg-blog #pg-blog-container .pm-blog-post-wrap').removeClass('pg-blog-image-hide');
}
 

//Toggle Password

$(".pg-toggle-password").on( 'click', function() {
    $(this).toggleClass("fa-eye fa-eye-slash");
    pgPasswordinput = $(this).parent().find("#user_pass");
    if (pgPasswordinput.attr("type") == "password") {
        pgPasswordinput.attr("type", "text");
    } else {
        pgPasswordinput.attr("type", "password");
    }
});

$('.pg-group-setting.pm-profile-tab a').on( 'click', function(e){
    $('#pg_group_setting .pm-section-content').hide();
    $('#pg_group_setting .pm-section-content:first').show(); 
});




})(jQuery);

