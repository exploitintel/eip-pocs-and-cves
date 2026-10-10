/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function( $ ) {
	'use strict';
       
function threadWindowOpen (){
    var openThread = document.querySelector(".pg-thread-open a");
    if (openThread) {
        openThread.addEventListener("click", (e) => {
            var sidebar = document.querySelector(".pg-message-box-sidebar");
            sidebar.classList.toggle("opened");
        }); 
    }
}

threadWindowOpen();

    $(".pg-message-action").on( 'click', function () {
        $(this).toggleClass("pg-action-active");

    });


//$(".open.secondthread a").on( 'click', function(){
//  alert('Hey you clicked')
//});


if($('#pg-messages').length)
    {
        // console.log("working");
        //refresh_messenger();
        setTimeout(function(){pm_get_messenger_notification('','nottyping');}, 1000);
        setTimeout(function(){pm_messenger_notification_extra_data();}, 1200);
        setTimeout(function(){pg_msg_bootstrap_from_deeplink(false);}, 1400);
        setTimeout(function(){pg_msg_restore_thread_messages();}, 1700);
        $("#typing_on .pm-typing-inner").hide();
    }
    
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery( ".pg-msg-list-wrap" ).css('border-color', pmDomColor); 
    jQuery( ".pg-msg-list-wrap" ).css('background', pmDomColor); 
    jQuery( ".pg-no-thread" ).parent( ".pg-msg-list-wrap" ).removeAttr('style');
    jQuery( "#chat_message_form svg" ).css('fill', pmDomColor); 
    jQuery( "#send_msg_btn svg" ).css('fill', pmDomColor); 
    jQuery(".pmagic #unread_thread_count").css('background-color', pmDomColor);
    jQuery(".pg-msg-conversation-list .pg-unread-count").css('background-color', pmDomColor);
    jQuery(".pg-message-box-sidebar .pg-message-box-action .pg-new-thread svg").css('fill', pmDomColor);
    jQuery(".pg-msg-thread-container .pg-msg-thread-header .pg-msg-thread-wrap svg").css('fill', pmDomColor);
    jQuery(".pg-msg-thread-container .pg-msg-thread-header .pg-msg-thread-wrap span").css('color', pmDomColor);
    
 
//--- General action  -----   
    
    
  $(".pg-new-thread-action svg, .pg-thread-action-controller-overlay").on( 'click', function(){
  $(".pg-thread-action-controller, .pg-thread-action-controller-overlay").toggle();
});




    
})(jQuery);
var notification_request = null;
var pg_unread_thread_count_cache = null;
function pg_clear_active_thread_unread_badge() {
    var activeThread = jQuery('.pg-msg-conversation-list.active');
    if (!activeThread.length) {
        return;
    }
    activeThread.find('.pg-thread-msg').remove();
    activeThread.find('.pg-thread-notification').remove();
    activeThread.find('.pg-msg-conversation-unread').remove();
}
var pg_msg_poll_timer = null;
var pg_msg_poll_in_flight = false;
var pg_msg_poll_next_timestamp = '';
var pg_msg_poll_pending_activity = '';
var pg_msg_poll_no_change_streak = 0;
var pg_msg_poll_error_streak = 0;
var pg_msg_last_user_activity_at = Date.now();
var pg_msg_poll_events_bound = false;
var pg_msg_deeplink_bootstrap_done = false;
function pg_msg_bind_poll_events() {
    if (pg_msg_poll_events_bound) {
        return;
    }
    pg_msg_poll_events_bound = true;
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            pg_msg_mark_user_active();
            pg_msg_schedule_poll(1200);
        }
    });
    jQuery(window).on('focus', function () {
        pg_msg_mark_user_active();
        pg_msg_schedule_poll(1200);
    });
    window.addEventListener('hashchange', function () {
        pg_msg_deeplink_bootstrap_done = false;
        pg_msg_bootstrap_from_deeplink(true);
    });
    window.addEventListener('pageshow', function (event) {
        if (event && event.persisted) {
            pg_msg_deeplink_bootstrap_done = false;
            pg_msg_bootstrap_from_deeplink(true);
        }
    });
}

function pg_msg_mark_user_active() {
    pg_msg_last_user_activity_at = Date.now();
    pg_msg_poll_no_change_streak = 0;
}

function pg_msg_sync_unread_state() {
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

function pg_msg_mark_thread_read_silent(tid) {
    tid = parseInt(tid, 10) || 0;
    if (!tid || !window.pg_msg_object || !pg_msg_object.ajax_url) {
        return;
    }
    jQuery.post(pg_msg_object.ajax_url, { action: 'pm_messages_mark_as_read', tid: tid, nonce: pg_msg_object.nonce }, function () {
        pg_msg_sync_unread_state();
        pm_messenger_notification_extra_data('');
    });
}

function pg_msg_get_query_int(name) {
    var query = window.location.search || '';
    var match = new RegExp('[?&]' + name + '=([^&#]*)').exec(query);
    if (!match || !match[1]) {
        return 0;
    }
    var value = parseInt(decodeURIComponent(match[1].replace(/\+/g, ' ')), 10);
    return isNaN(value) ? 0 : value;
}

function pg_msg_bootstrap_from_deeplink(force) {
    if (!jQuery('#pg-messages').length) {
        return;
    }
    if (pg_msg_deeplink_bootstrap_done && !force) {
        return;
    }
    var hash = (window.location.hash || '').toLowerCase();
    var hasMessageHash = (hash === '#pg-messages');
    var ridFromUrl = pg_msg_get_query_int('rid');
    var shouldBootstrap = hasMessageHash || ridFromUrl > 0;
    if (!shouldBootstrap) {
        return;
    }
    var sid = parseInt(jQuery('#chat_message_form input[name="sid"]').val(), 10) || 0;
    var rid = parseInt(jQuery('#receipent_field_rid').val(), 10) || 0;
    var tid = parseInt(jQuery('#thread_hidden_field').val(), 10) || 0;
    var targetRid = ridFromUrl > 0 ? ridFromUrl : rid;
    pg_msg_deeplink_bootstrap_done = true;
    if (sid > 0 && targetRid > 0 && tid > 0) {
        pg_show_msg_panel(sid, targetRid, tid);
        return;
    }
    if (targetRid > 0 && typeof pg_activate_new_thread === 'function') {
        pg_msg_mark_user_active();
        pg_msg_schedule_poll(800);
        setTimeout(function () {
            pg_activate_new_thread(targetRid);
        }, 150);
        setTimeout(function () {
            pm_messenger_notification_extra_data('');
        }, 450);
        return;
    }
    pg_msg_mark_user_active();
    pg_msg_schedule_poll(800);
    setTimeout(function () {
        pm_messenger_notification_extra_data('');
    }, 300);
}

function pg_msg_restore_thread_messages() {
    if (!jQuery('#pg-messages').length) {
        return;
    }
    var tid = parseInt(jQuery('#thread_hidden_field').val(), 10) || 0;
    if (tid <= 0) {
        return;
    }
    if (jQuery('.pg-users-search-list-wrap .pg-message-list').length > 0 && jQuery('.pg-msg-conversation-list').length > 0) {
        return;
    }
    pg_show_all_threads(tid);
    show_thread_messages(tid, 1);
}

function pg_parse_json_if_possible(response) {
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

function pg_is_json_error_response(response) {
    var parsed = pg_parse_json_if_possible(response);
    return !!(parsed && parsed.success === false);
}

function pg_msg_compute_poll_interval(changed) {
    var now = Date.now();
    var isVisible = document.visibilityState === 'visible';
    var idleMs = now - pg_msg_last_user_activity_at;
    var activeTid = parseInt(jQuery('#thread_hidden_field').val(), 10) || 0;
    var messagesVisible = jQuery('#pg-messages:visible').length > 0;
    var interval;
    if (!isVisible) {
        interval = 10000;
    } else if (messagesVisible && activeTid > 0 && idleMs < 30000) {
        interval = 1400;
    } else if (messagesVisible && idleMs < 30000) {
        interval = 2400;
    } else if (idleMs < 20000) {
        interval = 3500;
    } else {
        interval = 6000;
    }
    if (!changed) {
        interval += Math.min(pg_msg_poll_no_change_streak * 800, 5000);
    }
    if (pg_msg_poll_error_streak > 0) {
        interval += Math.min(pg_msg_poll_error_streak * 2000, 8000);
    }
    interval = Math.max(1200, Math.min(interval, 12000));
    var jitter = Math.floor(interval * ((Math.random() * 0.2) - 0.1));
    return Math.max(1100, interval + jitter);
}

function pg_msg_schedule_poll(delay) {
    if (pg_msg_poll_timer) {
        clearTimeout(pg_msg_poll_timer);
        pg_msg_poll_timer = null;
    }
    pg_msg_poll_timer = setTimeout(function () {
        pg_msg_run_notification_poll();
    }, delay);
}

function pg_msg_handle_notification_response(parsed, tid) {
    if (!parsed) {
        return false;
    }
    if (jQuery.isEmptyObject(parsed)) {
        pg_msg_poll_next_timestamp = '';
        return false;
    }
    if (parsed.activity === 'typing') {
        jQuery("#typing_on .pm-typing-inner").show();
        if (jQuery('.pg-users-search-list-wrap div.pg-message-list').length) {
            jQuery(".pg-users-search-list-wrap").scrollTop(jQuery(".pg-users-search-list-wrap div.pg-message-list:last").offset().top);
        }
    }
    if (parsed.activity === 'nottyping') {
        jQuery("#typing_on .pm-typing-inner").hide();
    }
    if (parsed.data_changed === true) {
        pg_show_all_threads(tid);
        show_thread_messages(tid, 1);
        // Let the unread summary update first so the existing badge/sound logic can
        // react to the incoming message before the active thread is silently marked read.
        pm_messenger_notification_extra_data('').always(function () {
            pg_msg_mark_thread_read_silent(tid);
        });
    }
    pg_msg_poll_next_timestamp = parsed.timestamp || '';
    return parsed.data_changed === true;
}

function pg_msg_send_notification_request(payload) {
    return jQuery.get(pg_msg_object.ajax_url, {
        action: 'pm_get_messenger_notification',
        timestamp: payload.timestamp,
        activity: payload.activity,
        tid: payload.tid,
        nonce: pg_msg_object.nonce,
        ts: Date.now()
    });
}

function pm_get_messenger_notification(timestamp, activity)
{
    if (activity === undefined) {
        activity = '';
    }
    if (timestamp !== undefined && timestamp !== null && timestamp !== '') {
        pg_msg_poll_next_timestamp = timestamp;
    }
    if (activity !== '') {
        pg_msg_poll_pending_activity = activity;
        pg_msg_mark_user_active();
    }
    pg_msg_bind_poll_events();
    pg_msg_schedule_poll(0);
}

function pg_msg_run_notification_poll() {
    if (pg_msg_poll_in_flight) {
        return;
    }
    var tid = jQuery('#thread_hidden_field').val();
    if (!tid) {
        pg_msg_poll_in_flight = true;
        pm_messenger_notification_extra_data('').done(function () {
            pg_msg_poll_error_streak = 0;
            pg_msg_poll_no_change_streak++;
        }).fail(function (jqXHR) {
            if (jqXHR && jqXHR.statusText === 'abort') {
                return;
            }
            pg_msg_poll_error_streak++;
        }).always(function (jqXHR) {
            pg_msg_poll_in_flight = false;
            if (jqXHR && jqXHR.statusText === 'abort') {
                return;
            }
            pg_msg_schedule_poll(pg_msg_compute_poll_interval(false));
        });
        return;
    }
    var payload = {
        timestamp: pg_msg_poll_next_timestamp,
        activity: pg_msg_poll_pending_activity || '',
        tid: tid
    };
    pg_msg_poll_pending_activity = '';
    pg_msg_poll_in_flight = true;
    notification_request = pg_msg_send_notification_request(payload);
    notification_request.done(function (response) {
        if (pg_is_json_error_response(response)) {
            pg_msg_poll_error_streak++;
            return;
        }
        var parsed = pg_parse_json_if_possible(response);
        var changed = pg_msg_handle_notification_response(parsed, tid);
        pg_msg_poll_error_streak = 0;
        if (changed) {
            pg_msg_mark_user_active();
            pg_msg_poll_no_change_streak = 0;
        } else {
            pg_msg_poll_no_change_streak++;
        }
    }).fail(function (jqXHR) {
        if (jqXHR && jqXHR.statusText === 'abort') {
            return;
        }
        pg_msg_poll_error_streak++;
    }).always(function (jqXHR) {
        pg_msg_poll_in_flight = false;
        if (jqXHR && jqXHR.statusText === 'abort') {
            return;
        }
        pg_msg_schedule_poll(pg_msg_compute_poll_interval(false));
    });
}


function pm_messenger_notification_extra_data(x){
  //console.log(x);
    //console.log("extra data working");
    var data = {'action': 'pm_messenger_notification_extra_data', 'ts': Date.now(), 'nonce': pg_msg_object.nonce};

    return jQuery.get(pg_msg_object.ajax_url, data, function (response)
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
               
//                if(jQuery('#thread_pane').length)
//                {
//                    pg_activate_new_thread(obj.rid);
//                }
                
                if(previousCount < unreadCount)
                {
                    jQuery("#msg_tone")[0].play();
                }
                
                   
            }else{
                    jQuery("#unread_thread_count").html('');   
                    jQuery("#unread_thread_count").removeClass("thread-count-show"); 
                    pg_clear_active_thread_unread_badge();
            }
            pg_unread_thread_count_cache = unreadCount;
          
        }

    });
}


function pg_msg_open_tab()
{
    pg_msg_loader();  
    jQuery("#unread_thread_count").html('');
    jQuery("#unread_thread_count").removeClass("thread-count-show");
    pg_msg_mark_user_active();
    pg_msg_schedule_poll(1000);
    pg_msg_sync_unread_state();
   jQuery('.pg-message-box-sidebar').addClass('opened');
   setTimeout(function(){pm_messenger_notification_extra_data();}, 500);
}

function pg_show_new_thread()
{
    jQuery('#pg-msg-thread-container .pg-msg-thread-header').hide();
    jQuery('#pg-msg-thread-container #pg-new-msg').show();
    jQuery('.pg-users-search-list-wrap').html('');
    jQuery('.emojionearea-editor').attr('placeholder','');
    jQuery('#thread_hidden_field').val('');
    jQuery('#receipent_field_rid').val('');
    jQuery('#mid').val('');
    jQuery('#new_thread').val('1');
    jQuery('#send_msg_btn').attr('disabled','disabled');
    jQuery(".pg-message-box-sidebar").removeClass('opened');
}

function pg_start_new_thread(){
    var autocomplete_request = null;
jQuery("#receipent_field").autocomplete({
     appendTo: ".pg-users-search-list-wrap",
     minLength: 3,
    source: function (request, response) 
            {
                    if (autocomplete_request != null) 
                    {
                        autocomplete_request.abort();
                    }

                    var name = jQuery("#receipent_field").val();
                    var data = {'action': 'pm_autocomplete_user_search', 'name': name, 'nonce': pg_msg_object.nonce};
                    autocomplete_request = jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
                            if(resp) 
                            {
                                var x = jQuery.parseJSON(resp);
                                response(x);
                                jQuery("#pm-autocomplete ul li").attr("tabindex",'0');
                            }
                            else
                            {
                                //response([{ label: 'No results found.', val: ''}]);
                               // jQuery('.pg-users-search-list-wrap').text('No user found');
                               // console.log("err in autocomplete field");
                               
                            }
                        });

            },
    select: function (event, ui) 
            {
                event.preventDefault();
                //jQuery("#receipent_field").attr("value", "@"+ui.item.label);
                if(ui.item.id!="")
                {
                    jQuery("#receipent_field_rid").val(ui.item.id);
                    pg_activate_new_thread(ui.item.id);
                }
                //activate_thread_with_uid(ui.item.id,0);

            }

});
}

function pg_msg_loader()
{
    jQuery(function() {
        /*emoji area JS */
    var el = jQuery("#pg_messaging_text").emojioneArea({
  	pickerPosition: "top",
  	filtersPosition: "bottom",
    tones: false,
    autocomplete: false,
    inline: true,
    hidePickerOnBlur: true,
    events: {
    keyup: function (editor, event) {
        if(event.which == 13) {
            jQuery(this).trigger( 'blur' );
            jQuery('#send_msg_btn').trigger( 'focus' ).trigger( 'click' );
        }
        
    }}
  });
  if(jQuery('.pg-users-search-list-wrap div.pg-message-list').length)
    {
      jQuery(".pg-users-search-list-wrap").scrollTop( jQuery(".pg-users-search-list-wrap div.pg-message-list:last").offset().top);
      var date = jQuery(".pg-msg-list-wrap .active .pg-msg-thread-time").html();
      jQuery("#pg-msg-thread-container .pg-msg-thread-header .pg-msg-thread-time").html(date);
    }
  /*message scrolling js */
    jQuery('.pg-users-search-list-wrap').on( 'scroll', function()
    {
        var tid = jQuery('#thread_hidden_field').val();

        if(jQuery('#load_more_message').length)
        {
            
                if (jQuery('.pg-users-search-list-wrap').offset().top - 100 <= jQuery('#load_more_message').offset().top)
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
    
    
    jQuery(".emojionearea-editor").focusin(function() {
          
            var activity = 'typing';
            pm_get_messenger_notification('', activity);
        });

    jQuery(".emojionearea-editor").focusout(function() {
         
            var activity = 'nottyping';
            pm_get_messenger_notification('', activity);
        });
    
    jQuery(".pg-message-action").on( 'click', function () {
        jQuery(this).toggleClass("pg-action-active");

    });

    jQuery(document).on('click', '.pg-msg-conversation-list', function (e) {
        if (jQuery(e.target).closest('.pg-msg-conversation-action, .pg-msg-conversation-delete, .pg-msg-conversation-read, .pg-msg-conversation-unread').length) {
            return;
        }
        var sid = parseInt(jQuery(this).data('sid'), 10) || 0;
        var rid = parseInt(jQuery(this).data('rid'), 10) || 0;
        var tid = parseInt(jQuery(this).data('thread'), 10) || 0;
        if (sid > 0 && rid > 0 && tid > 0) {
            pg_show_msg_panel(sid, rid, tid);
        }
    });

    jQuery(document).on('click', '.pg-msg-conversation-read', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var tid = parseInt(jQuery(this).data('tid'), 10) || 0;
        if (tid > 0) {
            pg_msg_read_messages(this, tid);
        }
    });

    jQuery(document).on('click', '.pg-msg-conversation-unread', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var tid = parseInt(jQuery(this).data('tid'), 10) || 0;
        if (tid > 0) {
            pg_msg_unread_messages(this, tid);
        }
    });

    jQuery(document).on('click', '.pg-msg-conversation-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var tid = parseInt(jQuery(this).data('tid'), 10) || 0;
        var uid = parseInt(jQuery(this).data('uid'), 10) || 0;
        var mid = parseInt(jQuery(this).data('mid'), 10) || 0;
        if (tid > 0 && uid > 0 && mid > 0) {
            pg_msg_delete_thread_confirmbox(tid, uid, mid);
        }
    });

    jQuery(document).on('click', '.pg-message-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var mid = parseInt(jQuery(this).data('mid'), 10) || 0;
        if (mid > 0) {
            pg_msg_edit(mid);
        }
    });

    jQuery(document).on('click', '.pg-message-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var mid = parseInt(jQuery(this).data('mid'), 10) || 0;
        if (mid > 0) {
            pg_msg_delete(mid);
        }
    });


        
        
        jQuery(".pg-thread-open a").on( 'click', function () {
            //alert('Yes you clicked');
            jQuery(".pg-message-box-sidebar").toggleClass("opened");
        });
        
        
        
        
       jQuery(".pg-thread-new-msg a").on( 'click', function () {
           // alert('Yes you clicked');
         jQuery(".pg-message-box-sidebar").addClass("opened");
        });
        
        
        // Reponsive//GUI Engine
        jQuery(window).on( 'resize', function () {

         mobileSizer();


        });
        
        jQuery(function () {

         mobileSizer();

        });
        
        
        function mobileSizer() {


            var messageBoxArea = jQuery('.pmagic').innerWidth();

            if (messageBoxArea < 460) {

                jQuery('.pg-message-box-container').addClass('pg-message-box-small');
                jQuery('.pg-message-box-container').removeClass('pg-message-box-medium');


            } else {

                jQuery('.pg-message-box-container').addClass('pg-message-box-medium');
                jQuery('.pg-message-box-container').removeClass('pg-message-box-small');
            }

        }
        ;
        
      
        
    
    });
    
}

pg_msg_loader();

function pm_messenger_send_chat_message(event) {
    var activity = 'nottyping';
    pm_get_messenger_notification('', activity);
    event.preventDefault();
    if( jQuery("#pg_messaging_text").val()===''){
        alert(pg_msg_object.empty_chat_message);
        return false;
    }
    if(jQuery("#receipent_field_rid").val()===''){
        alert("Enter a valid receipent");
        return false;
    }
    var form = jQuery("#chat_message_form");
    var mid = jQuery("#mid").val();
    var form_values = form.serializeArray();
    pm_messenger_send_message(form_values);
    var content = jQuery.trim(jQuery(".emojionearea-editor").html());
    jQuery(".emojionearea-editor").html('');
     jQuery("#pg_messaging_text").val('');
    if(mid=="")
    {
     var html = '<div id="" class="pg-message-list pg-user-self-msg pm-sending-msg" ><div class="pg-message-box"><p>'+content+'</p></div><div class="pg-msg-thread-time">'+ pg_msg_object.seding_text +'</div></div>';
     jQuery(".pg-users-search-list-wrap").append(html);
    }
    else
    {
        var html = '<p>'+content+'</p>';
        jQuery('#pg-msg_id_'+mid+' .pg-message-box').html(html);
        jQuery('#pg-msg_id_'+mid).trigger( 'focus' );
    }
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
    
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
        var resolvedTid = parseInt(resp, 10);
        if (!resolvedTid) {
            if (typeof resp === 'string' && jQuery.trim(resp) !== '') {
                alert(jQuery.trim(resp));
            }
            pg_msg_sync_unread_state();
            return;
        }
        if(data['new_thread']=="1")
        {
            pg_show_all_threads(resolvedTid);
        }
        show_thread_messages(resolvedTid,1);
        pg_msg_mark_user_active();
        pg_msg_schedule_poll(1200);
        jQuery('#new_thread').val("0");
        jQuery('#mid').val("");
        pg_msg_sync_unread_state();
//        jQuery(".pg-users-search-list-wrap").scrollTop( jQuery(".pg-users-search-list-wrap div.pg-message-list:last").offset().top);       
    });
}


function pg_show_msg_panel(uid,rid,tid)
{
     jQuery("#unread_thread_count").html('');   
    jQuery("#unread_thread_count").removeClass("thread-count-show");
    pg_clear_active_thread_unread_badge();
    pg_msg_mark_user_active();
    pg_msg_schedule_poll(1200);
    var search = jQuery('#pg-msg-search-box').val();
    var data = {'action': 'pg_show_msg_panel', 'uid': uid,'rid': rid,'tid':tid,search:search,'nonce':pg_msg_object.nonce};
   //console.log(data);
    jQuery("#pg-msg-thread-container").html('<div><div class="pm-loader"></div></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
    jQuery('.pg-msg-conversation-list').removeClass('active');
    jQuery(".pg-message-box-sidebar").removeClass('opened');
    jQuery('#pg-msg-thread-'+tid).addClass('active');
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
        if (pg_is_json_error_response(resp)) {
            return;
        }
        jQuery('#pg-msg-thread-'+tid+' .pg-unread-count' ).remove();
        jQuery('#pg-msg-thread-container').html(resp);
        jQuery('#new_thread').val("1");
        pg_msg_loader();
        pm_messenger_notification_extra_data();
        setTimeout(function(){pm_messenger_notification_extra_data();}, 300);
        pg_msg_sync_unread_state();
         
    if(search!=='')
    {
     
        jQuery(".pg-users-search-list-wrap:contains("+search+")").html(function(_, html) {
            return  html.replaceAll(search, '<span class="msg-search-result">'+search+'</span>')
        });

     setTimeout(function () {
     jQuery(".pg-users-search-list-wrap").scrollTop(jQuery(".pg-users-search-list-wrap div.pg-message-list .msg-search-result:last").offset().top - jQuery(".pg-users-search-list-wrap div:first").offset().top);
      //console.log(jQuery(".pg-users-search-list-wrap div.pg-message-list .msg-search-result:first").offset().top - jQuery(".pg-users-search-list-wrap div:first").offset().top);
      }, 400);
    }
        
    });
}

function pg_activate_new_thread(uid)
{
    var data = {action:'pm_activate_new_thread',uid: uid,nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) 
    {
        //console.log(resp.tid);
        jQuery('.pg-msg-list-wrap').html(resp.threads);
        pg_show_msg_panel(resp.sid,resp.rid,resp.tid);
    },'JSON');
    
     jQuery(".pg-message-box-sidebar").removeClass('opened');
}

function pg_activate_last_thread()
{
    var data = {action:'pm_activate_last_thread',nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) 
    {
        //console.log(resp.tid);
        jQuery('.pg-msg-list-wrap').html(resp.threads);
        pg_show_msg_panel(resp.sid,resp.rid,resp.tid);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery( ".pg-msg-list-wrap" ).css('border-color', pmDomColor); 
        jQuery( ".pg-msg-list-wrap" ).css('background', pmDomColor); 
        jQuery( ".pg-no-thread" ).parent( ".pg-msg-list-wrap" ).removeAttr('style');
    },'JSON');
}

function pg_show_all_threads(tid)
{
     var data = {action: 'pg_show_all_threads',tid:tid,nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
        jQuery('.pg-msg-list-wrap').html(resp);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery( ".pg-msg-list-wrap" ).css('border-color', pmDomColor); 
        jQuery( ".pg-msg-list-wrap" ).css('background', pmDomColor); 
        jQuery( ".pg-no-thread" ).parent( ".pg-msg-list-wrap" ).removeAttr('style');
     });
    
}

function pg_search_threads(search)
{
    
     var data = {action: 'pg_search_threads',search:search,nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
        jQuery('.pg-msg-list-wrap').html(resp);
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery( ".pg-msg-list-wrap" ).css('border-color', pmDomColor); 
        jQuery( ".pg-msg-list-wrap" ).css('background', pmDomColor); 
        jQuery( ".pg-no-thread" ).parent( ".pg-msg-list-wrap" ).removeAttr('style');
     });
    
}

function show_thread_messages(tid,loadnum) 
{
    jQuery("#unread_thread_count").html('');   
    jQuery("#unread_thread_count").removeClass("thread-count-show");
    pg_clear_active_thread_unread_badge();
                    
    var offset = new Date().getTimezoneOffset();
    var nonce = pg_msg_object.nonce;
    var data = {'action': 'pm_messenger_show_messages', 'tid': tid,'loadnum': loadnum,'timezone':offset,'nonce':nonce};
   //console.log(data);
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
       pm_messenger_notification_extra_data(1);
        //console.log(resp);
        if(jQuery('.pg-msg-list-wrap').length)
        {
            if(loadnum == "1" )
            {
                jQuery(".pg-users-search-list-wrap").html(resp);
                if (jQuery(".pg-users-search-list-wrap div.pg-message-list:last").length)
                {
                    jQuery(".pg-users-search-list-wrap").scrollTop( jQuery(".pg-users-search-list-wrap div.pg-message-list:last").offset().top);
                }
            }
            else
            {
                jQuery(".pg-users-search-list-wrap").prepend(resp);
                jQuery(".pg-users-search-list-wrap").scrollTop( jQuery("#load_more_message").offset().top+500);
            }
            
            jQuery(".pg-message-action").on( 'click', function () {
                jQuery(this).toggleClass("pg-action-active");

            });
            pg_msg_sync_unread_state();
        
        }
    });
    

}

function pg_msg_delete_thread(tid,uid,mid)
{
    
    if (tid == undefined)
    {   
        return false;
    }
    else
   {
       jQuery('#pg_edit_group_html_container').html('');
       jQuery('.pm-popup-mask, #pm-edit-group-popup, .pg-blog-dialog-mask').hide();
       jQuery('#pg-msg-thread-'+tid).remove();
   }
    var data = {action: 'pm_messenger_delete_threads', 'tid':tid,'uid':uid,'mid':mid,'nonce':pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (resp) {
       if(resp=="true")
       {
           pg_activate_last_thread();
       }
         
    });
    
    
}

function pg_msg_delete_thread_confirmbox(tid,uid,mid)
{
    
    if (tid == undefined)
    {   
        return false;
    }
    
    jQuery('#pg_message_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
       
    jQuery('#pm-edit-group-popup, .pm-popup-mask, .pg-blog-dialog-mask').toggle();
    var data = {action: 'pm_messenger_delete_threads_popup', 'tid':tid,'uid':uid,'mid':mid,'nonce': pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (response) {
       if(response)
        {
            bgcolor = pmDomColor.replace(')', ',0.2)');
            jQuery('#pg_message_html_container').html(response);
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

function pg_msg_read_messages(e,tid)
{
    var data = {action: 'pm_messages_mark_as_read', tid: tid, nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function () {
       jQuery('#pg-msg-thread-'+tid+' .pg-unread-count' ).remove(); 
        pg_show_all_threads(tid);
        pm_messenger_notification_extra_data('');
    });
}

function pg_msg_unread_messages(e,tid)
{
    var data = {action: 'pm_messages_mark_as_unread', tid: tid, nonce: pg_msg_object.nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (result) {
        if(result)
        {
            pg_show_all_threads(tid); 
        }
    });
}

function pg_msg_edit(mid)
{
    jQuery('#chat_message_form .emojionearea-editor').trigger( 'focus' );
    var msg = jQuery('#pg-msg_id_'+mid+' .pg-message-box').html();
    
    jQuery('#chat_message_form .emojionearea-editor').html(jQuery.trim(msg));
    jQuery('#chat_message_form #mid').val(mid);
}

function pg_msg_delete(mid)
{
    var tid = jQuery('#thread_hidden_field').val();
    var nonce = (pg_msg_object && pg_msg_object.pg_delete_msg_nonce) ? pg_msg_object.pg_delete_msg_nonce : (pg_msg_object ? pg_msg_object.nonce : '');
    var data = {action: 'pg_delete_msg', mid: mid, tid: tid, nonce: nonce};
    jQuery.post(pg_msg_object.ajax_url, data, function (result) {
        jQuery('#pg-msg_id_'+mid+' .pg-message-box').html(pg_msg_object.remove_msg);
    });
    
}
