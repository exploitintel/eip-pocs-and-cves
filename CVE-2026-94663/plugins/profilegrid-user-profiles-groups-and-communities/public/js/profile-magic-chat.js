
function start_messenger()
{
    
var autocomplete_request = null;

jQuery("#receipent_field").autocomplete({
     appendTo: "#pm-autocomplete",
     minLength: 3,
    source: function (request, response) 
            {
                    if (autocomplete_request != null) 
                    {
                        autocomplete_request.abort();
                    }

                    var name = jQuery("#receipent_field").val();
                    if(name.charAt(0)=="@")
                    {
                        name = name.substr(1);
                    }

                    var data = {'action': 'pm_autocomplete_user_search', 'name': name, 'nonce': pm_chat_object.nonce};
                    autocomplete_request = jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
                            if (resp) 
                            {
                                var x = jQuery.parseJSON(resp);
                                response(x);
                                jQuery("#pm-autocomplete ul li").attr("tabindex",'0');
                            }
                            else
                            {
                               // console.log("err in autocomplete field");
                            }
                        });

            },
    select: function (event, ui) 
            {
                event.preventDefault();
                //jQuery("#receipent_field").attr("value", "@"+ui.item.label);
                jQuery("#receipent_field_rid").val(ui.item.id);
                pg_activate_new_thread(ui.item.id);
                //activate_thread_with_uid(ui.item.id,0);

            }

});

jQuery('#message_display_area').on( 'scroll', function()
{
    var tid = get_active_thread_id();
    
    if(jQuery('#load_more_message').length)
    {
        console.log(jQuery('#load_more_message').length);
            if (jQuery('#message_display_area').offset().top - 100 <= jQuery('#load_more_message').offset().top)
            {
                    if(!jQuery('#load_more_message').attr('loaded'))
                    {
                        jQuery('#load_more_message').attr('loaded', true);
                        var pagenum= jQuery('#load_more_message').attr('pagenum');
                        pagenum=parseInt(pagenum)+1;
                        show_thread_messages(tid,pagenum);
                    }
            }
    }
});

jQuery(function() {
        // Initializes and creates emoji set from sprite sheet
        window.emojiPicker = new EmojiPicker({
          emojiable_selector: '[data-emojiable=true]',
          assetsPath: pm_chat_object.plugin_emoji_url,
          popupButtonClasses: 'fa fa-smile-o'
        });
         window.emojiPicker.discover();
      });
      
    jQuery(".emoji-wysiwyg-editor").focusin(function() {
            var tid = get_active_thread_id();
            var activity = 'typing';
            pm_get_messenger_notification('', activity);
        });

    jQuery(".emoji-wysiwyg-editor").focusout(function() {
            var tid = get_active_thread_id();
            var activity = 'nottyping';
            pm_get_messenger_notification('', activity);
        });
    
   jQuery(function(){
   
       var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
        jQuery(".pmagic .pm-blog-time").css('color', pmDomColor);
        jQuery(".pmagic .pm-user-conversations-counter").css('color', pmDomColor);
        jQuery(".pmagic #unread_thread_count").css('background-color', pmDomColor);
        jQuery(".pmagic #unread_notification_count").css('background-color', pmDomColor);
        jQuery(".pmagic .pm-blog-desc-wrap #chat_message_form input#receipent_field").css('color', pmDomColor);
        jQuery(".pmagic .pm-new-message-area button").css('color', pmDomColor);
        jQuery(".pmagic .pm-messenger-button svg").css('fill', pmDomColor);
        jQuery(".pmagic .pm-thread-active .pm-conversations-container .pm-thread-user").css('color', pmDomColor);
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery("#pg-friends .pm-selected-image svg").css('fill', pmDomColor);
        jQuery( ".pmagic .page-numbers .page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor);
        jQuery( ".pm-group-view.pg-theme-seven .pg-profile-area-wrap" ) .css('background', pmDomColor); 

    
        
     
        jQuery('.pmagic .pm-profile-tab-wrap .pm-profile-tab').hover(
               function() {
                   jQuery(this).css('border-bottom-color',pmDomColor);
               },
               
               function() {
                   jQuery(this).css('border-bottom-color','transparent');
                   jQuery('.pm-section-nav-horizental .pm-profile-tab.ui-state-active').css('border-bottom-color',pmDomColor); 
               }
                         
       );
     
   }); 

}


function update_thread() {

    //console.log("updating thread");
    pg_show_all_threads();
    var tid = get_active_thread_id();
    
    show_thread_messages(tid,1);
    //show_threads(tid);
}

function pm_messenger_send_chat_message(event) {
    event.preventDefault();
    if( jQuery("#messenger_textarea").val()===''){
        alert(pm_chat_object.empty_chat_message);
        return false;
    }
    if(jQuery("#receipent_field_rid").val()===''){
        alert("Enter a valid receipent");
        return false;
    }
    var form = jQuery("#chat_message_form");
    var form_values = form.serializeArray();
    pm_messenger_send_message(form_values);
    var content = jQuery.trim(jQuery(".emoji-wysiwyg-editor").html());
    jQuery(".emoji-wysiwyg-editor").html('');
     jQuery("#messenger_textarea").val('');
     var img = jQuery('.pm-messenger-user-profile-pic').html();
     var html = '<div id="" class="pm_msg_rf  pm-sending-msg" >'+img+'<div class="pm-user-description-row pm-dbfl pm-border">'+content+'</div>'+ pm_chat_object.seding_text +'</div>';
     jQuery("#message_display_area").append(html);
}

function pm_messenger_send_message(form_values) {
    //console.log("sending message ");
    var tid = jQuery('#thread_hidden_field').val();
    var data = {};
    jQuery.each(form_values, function () {
        if (data[this.name] !== undefined) {
            if (!data[this.name].push) {
                data[this.name] = [data[this.name]];
            }
            data[this.name].push(this.value);
        } else {
            data[this.name] = this.value;
        }
    });
    
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
        show_thread_messages(tid,1);
        pm_chat_mark_user_active();
        pm_chat_schedule_poll(1200);
        pm_chat_sync_unread_state();
        jQuery("#message_display_area").scrollTop( jQuery("#message_display_area div:last").offset().top)
    });
}

function get_active_thread_id() {
    var cur_thread = jQuery("#threads_ul [active='true']");
    var id = jQuery(cur_thread).attr("id");
    if (id === undefined){
        id='';
    }else{
    var tid = id.replace('t_id_', '');
    }
    return tid;

}

function pg_activate_new_thread(uid)
{
    var data = {action:'pm_activate_new_thread',uid: uid,nonce: pm_chat_object.nonce};
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) 
    {
        console.log(resp.tid);
        jQuery('#threads_ul').html(resp.threads);
        pm_get_active_thread_header(resp.rid);
        show_thread_messages(resp.tid,1); 
        pm_chat_sync_unread_state();
        //show_message_pane(resp.tid,resp.rid);
    },'JSON');
}

function pm_get_rid_by_uname(uname)
{ 
    
}

function show_thread_messages(tid,loadnum) 
{
    pm_chat_clear_active_thread_unread_badge();
    
    //var tid = id.replace('t_id_', '');
    //console.log("showing thread  message of tid : "+tid);
   var offset = new Date().getTimezoneOffset();
   //console.log("offset is "+offset);
    var data = {'action': 'pm_messenger_show_messages', 'tid': tid,'loadnum': loadnum,'timezone':offset,'nonce': pm_chat_object.nonce};
   console.log(data);
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
       
        //console.log(resp);
        if(jQuery('#thread_pane').length)
        {
            if(loadnum == "1" )
            {
                jQuery("#message_display_area").html(resp);
                 if (jQuery("#message_display_area div:last").length)
                {
                jQuery("#message_display_area").scrollTop( jQuery("#message_display_area div:last").offset().top);
                }
            }
            else
            {
                jQuery("#message_display_area").prepend(resp);
                jQuery("#message_display_area").scrollTop( jQuery("#load_more_message").offset().top+500);
            }
        
        }
        pm_chat_sync_unread_state();
    });

}

function show_message_pane(tid,rid) {
    
        //jQuery("#receipent_field").prop("disabled",true);
         //jQuery("#receipent_field").addClass("pm-recipent-disable");
        jQuery('#pm-username-error').html('');
        show_pg_section_right_panel();
      
        jQuery("#threads_ul li").attr("active", "false");
        jQuery("#t_id_"+tid).attr("active", "true");
        jQuery("#t_id_"+tid+" #unread_msg_count").html(" ");
        pm_chat_clear_active_thread_unread_badge();
        var uid = jQuery("#t_id_"+tid).attr("uid");
        pm_get_active_thread_header(rid);
        show_thread_messages(tid,1); 
        
    //console.log("showing message pane of tid : "+tid+" and uid : "+uid);
    jQuery("#receipent_field_rid").attr('value', uid);
    jQuery("#thread_hidden_field").attr("value", tid);
    
    pm_messages_mark_as_read(tid);
    pm_chat_mark_user_active();
    pm_chat_schedule_poll(1200);
    pm_chat_sync_unread_state();
    //activate_thread_with_uid(uid,mid);

}

function pm_get_active_thread_header(uid)
{
    var data = {action: 'pm_get_active_thread_header', uid: uid, nonce: pm_chat_object.nonce};
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
        jQuery('#userSection').html(resp);
    });
}

function pm_messenger_notification_extra_data(x){
  //console.log(x);
    //console.log("extra data working");
    var data = {'action': 'pm_messenger_notification_extra_data', 'ts': Date.now(), 'nonce': pm_chat_object.nonce};

    return jQuery.get(pm_chat_object.ajax_url, data, function (response)
    {
        if (response)
        {
            
            var obj = jQuery.parseJSON(response);
            //console.log(obj.unread_threads);
            var unreadCount = parseInt(obj.unread_threads, 10);
            if (isNaN(unreadCount)) {
                unreadCount = 0;
            }
            var previousCount = pg_unread_thread_count_cache;
            if (previousCount === null || isNaN(previousCount)) {
                previousCount = parseInt(jQuery("#unread_thread_count").html(), 10);
                if (isNaN(previousCount)) {
                    previousCount = 0;
                }
            }
            if (unreadCount > 0)
            {
                console.log(unreadCount);
                console.log(previousCount);
                jQuery("#unread_thread_count").addClass("thread-count-show"); 
                jQuery("#unread_thread_count").html(unreadCount);   
                if(jQuery('#thread_pane').length)
                {
                    pg_activate_new_thread(obj.rid);
                }
                if(previousCount < unreadCount)
                {
                    jQuery("#msg_tone")[0].play();
                }
                
                   
            }else{
                    jQuery("#unread_thread_count").html('');   
                    jQuery("#unread_thread_count").removeClass("thread-count-show"); 
                    pm_chat_clear_active_thread_unread_badge();
            }
            pg_unread_thread_count_cache = unreadCount;
          
        }

    });
}

function refresh_messenger()
{
    start_messenger();
    pm_messenger_notification_extra_data(); 
    var tid = get_active_thread_id();
    show_thread_messages(tid,1);


}
var notification_request = null;
var pg_unread_thread_count_cache = null;
var pm_chat_poll_timer = null;
var pm_chat_poll_in_flight = false;
var pm_chat_poll_next_timestamp = '';
var pm_chat_poll_pending_activity = '';
var pm_chat_poll_no_change_streak = 0;
var pm_chat_poll_error_streak = 0;
var pm_chat_last_user_activity_at = Date.now();
var pm_chat_poll_events_bound = false;

function pm_chat_get_client_config() {
    if (typeof pm_chat_object !== 'undefined' && pm_chat_object) {
        return pm_chat_object;
    }
    if (typeof pg_msg_object !== 'undefined' && pg_msg_object) {
        return pg_msg_object;
    }
    return {};
}

function pm_chat_clear_active_thread_unread_badge() {
    var activeThread = jQuery('.pg-msg-conversation-list.active');
    if (!activeThread.length) {
        return;
    }
    activeThread.find('.pg-thread-msg').remove();
    activeThread.find('.pg-thread-notification').remove();
    activeThread.find('.pg-msg-conversation-unread').remove();
}

function pm_chat_bind_poll_events() {
    if (pm_chat_poll_events_bound) {
        return;
    }
    pm_chat_poll_events_bound = true;
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            pm_chat_mark_user_active();
            pm_chat_schedule_poll(1200);
        }
    });
    jQuery(window).on('focus', function () {
        pm_chat_mark_user_active();
        pm_chat_schedule_poll(1200);
    });
}

function pm_chat_mark_user_active() {
    pm_chat_last_user_activity_at = Date.now();
    pm_chat_poll_no_change_streak = 0;
}

function pm_chat_sync_unread_state() {
    if (!window.pgUnreadToastSync) {
        return;
    }
    if (typeof window.pgUnreadToastSync.acknowledgeCurrent === 'function') {
        window.pgUnreadToastSync.acknowledgeCurrent();
    }
    if (typeof window.pgUnreadToastSync.hideWhileMessagesVisible === 'function') {
        window.pgUnreadToastSync.hideWhileMessagesVisible();
    }
    if (typeof window.pgUnreadToastSync.refreshNow === 'function') {
        window.pgUnreadToastSync.refreshNow();
    }
}

function pm_chat_mark_thread_read_silent(tid) {
    tid = parseInt(tid, 10) || 0;
    if (!tid || !window.pm_chat_object || !pm_chat_object.ajax_url) {
        return;
    }
    jQuery.post(pm_chat_object.ajax_url, { action: 'pm_messages_mark_as_read', tid: tid, nonce: pm_chat_object.nonce }, function () {
        pm_chat_sync_unread_state();
        pm_messenger_notification_extra_data('');
    });
}

function pm_chat_compute_poll_interval(changed) {
    var now = Date.now();
    var isVisible = document.visibilityState === 'visible';
    var idleMs = now - pm_chat_last_user_activity_at;
    var activeTid = parseInt(get_active_thread_id(), 10) || 0;
    var interval;
    if (!isVisible) {
        interval = 10000;
    } else if (activeTid > 0 && idleMs < 30000) {
        interval = 1400;
    } else if (idleMs < 30000) {
        interval = 2400;
    } else if (idleMs < 20000) {
        interval = 3500;
    } else {
        interval = 6000;
    }
    if (!changed) {
        interval += Math.min(pm_chat_poll_no_change_streak * 800, 5000);
    }
    if (pm_chat_poll_error_streak > 0) {
        interval += Math.min(pm_chat_poll_error_streak * 2000, 8000);
    }
    interval = Math.max(1200, Math.min(interval, 12000));
    var jitter = Math.floor(interval * ((Math.random() * 0.2) - 0.1));
    return Math.max(1100, interval + jitter);
}

function pm_chat_schedule_poll(delay) {
    if (pm_chat_poll_timer) {
        clearTimeout(pm_chat_poll_timer);
        pm_chat_poll_timer = null;
    }
    pm_chat_poll_timer = setTimeout(function () {
        pm_chat_run_notification_poll();
    }, delay);
}

function pm_chat_parse_json_if_possible(response) {
    if (typeof response === 'object' && response !== null) {
        return response;
    }
    if (typeof response !== 'string' || response === '') {
        return null;
    }
    try {
        return jQuery.parseJSON(response);
    } catch (e) {
        return null;
    }
}

function pm_chat_send_notification_request(payload) {
    var cfg = pm_chat_get_client_config();
    return jQuery.get(cfg.ajax_url, {
        action: 'pm_get_messenger_notification',
        timestamp: payload.timestamp,
        activity: payload.activity,
        tid: payload.tid,
        nonce: cfg.nonce || '',
        ts: Date.now()
    });
}

function pm_get_messenger_notification(timestamp, activity)
{
    if (activity === undefined) {
        activity = '';
    }
    if (timestamp !== undefined && timestamp !== null && timestamp !== '') {
        pm_chat_poll_next_timestamp = timestamp;
    }
    if (activity !== '') {
        pm_chat_poll_pending_activity = activity;
        pm_chat_mark_user_active();
    }
    pm_chat_bind_poll_events();
    pm_chat_schedule_poll(0);
}

function pm_chat_run_notification_poll() {
    if (pm_chat_poll_in_flight) {
        return;
    }
    var tid = get_active_thread_id();
    if (!tid) {
        pm_chat_poll_in_flight = true;
        pm_messenger_notification_extra_data('').done(function () {
            pm_chat_poll_error_streak = 0;
            pm_chat_poll_no_change_streak++;
        }).fail(function (jqXHR) {
            if (jqXHR && jqXHR.statusText === 'abort') {
                return;
            }
            pm_chat_poll_error_streak++;
        }).always(function (jqXHR) {
            pm_chat_poll_in_flight = false;
            if (jqXHR && jqXHR.statusText === 'abort') {
                return;
            }
            pm_chat_schedule_poll(pm_chat_compute_poll_interval(false));
        });
        return;
    }
    var payload = {
        timestamp: pm_chat_poll_next_timestamp,
        activity: pm_chat_poll_pending_activity || '',
        tid: tid
    };
    pm_chat_poll_pending_activity = '';
    pm_chat_poll_in_flight = true;
    notification_request = pm_chat_send_notification_request(payload);
    notification_request.done(function (response) {
        var obj = pm_chat_parse_json_if_possible(response);
        if (!obj) {
            pm_chat_poll_error_streak++;
            return;
        }
        if (jQuery.isEmptyObject(obj)) {
            pm_chat_poll_next_timestamp = '';
            pm_chat_poll_no_change_streak++;
            return;
        }
        if (obj.activity === 'typing') {
            jQuery("#typing_on .pm-typing-inner").show();
        }
        if (obj.activity === 'nottyping') {
            jQuery("#typing_on .pm-typing-inner").hide();
        }
        if (obj.data_changed === true) {
            update_thread();
            pm_chat_mark_thread_read_silent(tid);
            pm_messenger_notification_extra_data('');
            pm_chat_mark_user_active();
            pm_chat_poll_no_change_streak = 0;
        } else {
            pm_chat_poll_no_change_streak++;
        }
        pm_chat_poll_next_timestamp = obj.timestamp || '';
        pm_chat_poll_error_streak = 0;
    }).fail(function (jqXHR) {
        if (jqXHR && jqXHR.statusText === 'abort') {
            return;
        }
        pm_chat_poll_error_streak++;
    }).always(function (jqXHR) {
        pm_chat_poll_in_flight = false;
        if (jqXHR && jqXHR.statusText === 'abort') {
            return;
        }
        pm_chat_schedule_poll(pm_chat_compute_poll_interval(false));
    });
}

function pm_messages_mark_as_read(tid)
{
    var data = {action: 'pm_messages_mark_as_read', tid: tid, nonce: pm_chat_object.nonce};
    jQuery.post(pm_chat_object.ajax_url, data, function () {
        pm_messenger_notification_extra_data('');
    });
}

function pm_messenger_delete_thread(a,tid){
 
    if (tid == undefined){   
        return false;
    }else{
   // console.log("Deleting thread with  tid :" + tid);
   jQuery(a).parent('div').parent('li').remove();
    }
    var data = {action: 'pm_messenger_delete_threads', 'tid': tid, 'nonce': pm_chat_object.nonce};
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
        jQuery('.pm-message-thread-section').show();
       // console.log(resp);
        var obj = jQuery.parseJSON(resp);
        console.log(obj);
         //pg_activate_new_thread(obj.uid);
         pm_get_active_thread_header(obj.uid);
         show_thread_messages(obj.tid,1);
    });

}

function pg_show_all_threads()
{
     var data = {action: 'pg_show_all_threads', nonce: pm_chat_object.nonce};
    jQuery.post(pm_chat_object.ajax_url, data, function (resp) {
        jQuery('#threads_ul').html(resp);
     });
    
}
