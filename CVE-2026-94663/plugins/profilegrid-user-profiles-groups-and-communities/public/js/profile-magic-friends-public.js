function pm_load_hash_url(url)
{
	//window.location(url);
	window.location.href = url;
	location.reload();
}

function pm_load_more_friends(uid,page,total_page)
{
	page =  parseInt(page);
	if(page>0)
	{
	pm_f_search = jQuery('#pm_f_search').val();
		var data = {
						'action': 'pm_fetch_my_friends',
						'uid' : uid,
						'pagenum' :page,
						'pm_f_search':pm_f_search,
						'nonce': pm_ajax_object.nonce
					};
	jQuery('#pm_load_more_result').hide();
	jQuery('.pm-loader-img').show();
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
			if(response)
			{
				jQuery('.pm-my-friends').append(response);
				jQuery('.pm-loader-img').hide();
				
				if(total_page>page)
				{
					value = page + 1;
					jQuery('#pm_load_more_result').attr('value',value);
				}
				else
				{
					jQuery('#pm_load_more_result').attr('value','0');
					jQuery('#pm_load_more_result').html(pm_error_object.no_more_result)
						
				}
				jQuery('#pm_load_more_result').show();
				
			}
		});	
		
	}
}

function pm_add_friend_request(user1,user2,button)
{
   
	var data = {'action': 'pm_add_friend_request','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	jQuery(button).parent('.pm-request-button').children('button').hide();
	jQuery(button).parent('.pm-request-button').children('img').show();
	
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
			jQuery(button).parent('div').html(response);
			//pm_get_notification();					
		}
	});	
}

var pm_friend_notification_timer = null;
var pm_friend_notification_in_flight = false;
var pm_friend_notification_timestamp = '';

function pm_schedule_friend_notification_poll(delay)
{
	if (pm_friend_notification_timer) {
		clearTimeout(pm_friend_notification_timer);
		pm_friend_notification_timer = null;
	}
	pm_friend_notification_timer = setTimeout(function () {
		pm_get_notification(pm_friend_notification_timestamp);
	}, delay);
}

function pm_get_notification(timestamp)
{ 
	if (pm_friend_notification_in_flight) {
		return;
	}
	if (timestamp !== undefined && timestamp !== null && timestamp !== '') {
		pm_friend_notification_timestamp = timestamp;
	}
	var data = {
		'action': 'pm_get_friends_notification',
		'timestamp': pm_friend_notification_timestamp,
		'nonce': pm_ajax_object.nonce
	};
	pm_friend_notification_in_flight = true;
	jQuery.get(pm_ajax_object.ajax_url, data, function(response) 
	{
		if(response)
		{
			var obj = jQuery.parseJSON(response);
			jQuery('#pm_waiting_request').html(obj.data_from_file);
			if (obj && obj.timestamp) {
				pm_friend_notification_timestamp = obj.timestamp;
			}
		}
	}).always(function () {
		pm_friend_notification_in_flight = false;
		var delay = (document.visibilityState === 'visible') ? 12000 : 30000;
		pm_schedule_friend_notification_poll(delay);
	});
}

function pm_confirm_request_from_notification(user1,user2,button,id){
    pm_delete_notification(id);
    pm_confirm_request(user1,user2,button);
}
function pm_confirm_request(user1,user2,button)
{
	var data = {'action': 'pm_confirm_friend_request','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	jQuery("#pg-friend-requests").html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
        
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                        var uid = jQuery('#pm-uid').val();
                        pm_get_my_friends(1,uid);
                        pm_get_friend_requests(1,uid);
                        pm_get_friend_requests_sent(1,uid);
                        pm_update_counter(uid);					
		}
	});	
}

function pm_confirm_request_right_side(user1,user2,button)
{
	var data = {'action': 'pm_confirm_friend_request','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
        var selector = jQuery(button).parent('div');
	jQuery(selector).html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                        var uid = jQuery('#pm-uid').val();
                        jQuery(selector).html(response);
                         pm_get_my_friends(1,uid);
		}
	});	
}

function pm_update_counter(uid)
{
    
    var data = {'action': 'pm_fetch_friend_list_counter','uid' : uid,'pm_friend_view' :1,'nonce': pm_ajax_object.nonce};	
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    jQuery('#pg-friends-container .notification-count:eq(0)').html(response);
                }
	});	
        
        var data2 = {'action': 'pm_fetch_friend_list_counter','uid' : uid,'pm_friend_view' :2,'nonce': pm_ajax_object.nonce};	
	jQuery.post(pm_ajax_object.ajax_url, data2, function(response2) {
		if(response2)
		{
                    jQuery('#pg-friends-container .notification-count:eq(1)').html(response2);
                }
	});	
        
        var data3 = {'action': 'pm_fetch_friend_list_counter','uid' : uid,'pm_friend_view' :3,'nonce': pm_ajax_object.nonce};	
	jQuery.post(pm_ajax_object.ajax_url, data3, function(response3) {
		if(response3)
		{
                    jQuery('#pg-friends-container .notification-count:eq(2)').html(response3);
                }
	});	
        
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
}
function pm_remove_suggestions(user1,user2,button)
{
	
	var data = {'action': 'pm_remove_friend_suggestion','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	jQuery(button).parent('.pm-request-button').children('button').hide();
	jQuery(button).parent('.pm-request-button').children('img').show();
	
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
			jQuery(button).parent('.pm-request-button').parent('.pm-my-friend').addClass('pm_remove_suggestions');
			jQuery('.pm_remove_suggestions').remove();								
		}
	});		
}
function pm_reject_friend_request_from_notification(user1,user2,button,id){
    pm_delete_notification(id);
    pm_reject_friend_request(user1,user2,button);
}

function pm_reject_friend_request(user1,user2,button)
{
    if(button!='multiple')
    {
        var result = confirm(pm_error_object.delete_friend_request);
    }
    else
    {
        result = true;
    }
    if(result)
    {
	var data = {'action': 'pm_reject_friend_request','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	jQuery("#pg-friend-requests").html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
	
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    var uid = jQuery('#pm-uid').val();
                    pm_get_my_friends(1,uid);
                    pm_get_friend_requests(1,uid);
                    pm_get_friend_requests_sent(1,uid);
                    pm_update_counter(uid);						
		}
	});
    }
}

function pm_reject_friend_request_right_side(user1,user2,button)
{
     var result = confirm(pm_error_object.delete_friend_request);
    if(result)
    {
	var data = {'action': 'pm_reject_friend_request','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	var selector = jQuery(button).parent('div');
	jQuery(selector).html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    var uid = jQuery('#pm-uid').val();
                    jQuery(selector).html(response);
                    pm_get_my_friends(1,uid);						
		}
	});
    }
}

function pm_unfriend_request(user1,user2,button)
{
    var data = {'action': 'pm_unfriend_friend','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
    jQuery("#pg-myfriends").html('<div><div class="pm-loader"></div></div>');
    var pmDomColor = jQuery(".pmagic").children("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
    jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
            if(response)
            {
                var uid = jQuery('#pm-uid').val();
                pm_get_my_friends(1,uid);
                pm_get_friend_requests(1,uid);
                pm_get_friend_requests_sent(1,uid);
                pm_update_counter(uid);	
            }
    });
}

function pm_unfriend_request_rightside(user1,user2,button)
{
	  var result = confirm(pm_error_object.remove_friend);
    if(result)
    {
        var data = {'action': 'pm_unfriend_friend','user1' : user1,'user2' :user2,'nonce': pm_ajax_object.nonce};
	jQuery(button).html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    var uid = jQuery('#pm-uid').val();
                    pm_get_my_friends(1,uid);	
                    jQuery(button).html(response);
                }
	});	
    }
}
function pm_cancel_request_rightside(user1,user2,button)
{
	var data = {'action': 'pm_unfriend_friend','user1' : user1,'user2' :user2,'cancel_request':'1','nonce': pm_ajax_object.nonce};
        jQuery(button).html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);

	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    var uid = jQuery('#pm-uid').val();
                    pm_get_my_friends(1,uid);	
                    jQuery(button).html(response);		
		}
	});
}

function pm_cancel_request(user1,user2,button)
{
	var data = {'action': 'pm_unfriend_friend','user1' : user1,'user2' :user2,'cancel_request':'1','nonce': pm_ajax_object.nonce};
	jQuery("#pg-requests-sent").html('<div><div class="pm-loader"></div></div>');
        var pmDomColor = jQuery(".pmagic").children("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
	jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
		if(response)
		{
                    var uid = jQuery('#pm-uid').val();
                    pm_get_my_friends(1,uid);
                    pm_get_friend_requests(1,uid);
                    pm_get_friend_requests_sent(1,uid);
                    pm_update_counter(uid);		
		}
	});
}

function pm_load_more_suggestion(uid,page,total_page)
{
	
	page =  parseInt(page);
	if(page>0)
	{
	pm_u_search = jQuery('#pm_u_search').val();
		var data = {
						'action': 'pm_fetch_my_suggestion',
						'uid' : uid,
						'pagenum' :page,
						'pm_u_search':pm_u_search,
						'nonce': pm_ajax_object.nonce
					};
	jQuery('#pm_load_more_suggestion').hide();
	jQuery('.pm-loader-img-suggestion').show();
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
			if(response)
			{
				jQuery('.pm-my-suggestions').append(response);
				jQuery('.pm-loader-img-suggestion').hide();
				
				if(total_page>page)
				{
					value = page + 1;
					jQuery('#pm_load_more_suggestion').attr('value',value);
				}
				else
				{
					jQuery('#pm_load_more_suggestion').attr('value','0');
					jQuery('#pm_load_more_suggestion').html(pm_error_object.no_more_result);
						
				}
				jQuery('#pm_load_more_suggestion').show();
				
			}
		});	
		
	}
}


function pm_select_friend_checkbox(a)
{
      var img = '<div class="pm-selected-image" onclick="pm_unselect_friend(this)"> <svg fill="#000000" height="100%" viewBox="0 0 24 24" width="100%" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"/><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>',
        isChecked = jQuery(a).is(':checked');
         if(isChecked)
         {
            jQuery(a).parent('label').parent('.pm-friend-select').parent('.pm-myfriends-list').append(img);
         }
         else
         {
             jQuery(a).parent('label').parent('.pm-friend-select').parent('.pm-myfriends-list').children('.pm-selected-image').remove();
         }
          var pmDomColor = jQuery(".pmagic").children("a").css('color');
          jQuery("#pg-friends .pm-selected-image svg").css('fill', pmDomColor);
}

function pm_multiple_friends_remove(uid)
{
    var result = confirm(pm_error_object.remove_friend);
    if(result)
    {
        var handled = false;
        jQuery('.pm-my-friends-select-checkbox').each(function (index, element) { //Validation for number type custom field
                    var isChecked = jQuery(this).is(':checked');
                    var u2 = jQuery(this).val();
                    if(isChecked)
                    {

                        pm_unfriend_request(uid,u2,this);
                        handled = true;
                    }

        });
        if (!handled) {
            var allCheckboxes = jQuery('.pm-my-friends-select-checkbox');
            if (allCheckboxes.length === 1) {
                var single = allCheckboxes.first();
                pm_unfriend_request(uid, single.val(), single);
            }
        }
    }
}

function pm_multiple_friends_request_accept(uid)
{
    var result = confirm(pm_error_object.accept_friend_request_conf);
    if(result)
    {
        var handled = false;
        jQuery('.pm-request-friends-select-checkbox').each(function (index, element) { //Validation for number type custom field
                    var isChecked = jQuery(this).is(':checked');
                    var u2 = jQuery(this).val();
                    if(isChecked)
                    {
                        pm_confirm_request(uid,u2,this);
                        handled = true;
                    }

        });
        if (!handled) {
            var allCheckboxes = jQuery('.pm-request-friends-select-checkbox');
            if (allCheckboxes.length === 1) {
                var single = allCheckboxes.first();
                pm_confirm_request(uid, single.val(), single);
            }
        }
    }        
}

function pm_multiple_friends_request_delete(uid)
{
    var result = confirm(pm_error_object.delete_friend_request);
    if(result)
    {
        var handled = false;
        jQuery('.pm-request-friends-select-checkbox').each(function (index, element) { //Validation for number type custom field
                    var isChecked = jQuery(this).is(':checked');
                    var u2 = jQuery(this).val();
                    if(isChecked)
                    {
                        pm_reject_friend_request(uid,u2,'multiple');
                        handled = true;
                    }

        });
        if (!handled) {
            var allCheckboxes = jQuery('.pm-request-friends-select-checkbox');
            if (allCheckboxes.length === 1) {
                var single = allCheckboxes.first();
                pm_reject_friend_request(uid, single.val(), 'multiple');
            }
        }
    }
}

function pm_multiple_friends_request_cancel(uid)
{
    var result = confirm(pm_error_object.cancel_friend_request);
    if(result)
    {
        jQuery('.pm-request-sent-select-checkbox').each(function (index, element) { //Validation for number type custom field
                    var isChecked = jQuery(this).is(':checked');
                    var u2 = jQuery(this).val();
                    if(isChecked)
                    {
                        pm_cancel_request(uid,u2,this);
                    }

	});
    }
}

function pm_unselect_friend(a)
{
    jQuery(a).parent('div').children('.pm-friend-select').children('label').children('.pm-friends-select-checkbox').prop('checked', false);
    //jQuery(a).toggleClass('flipper');
    jQuery(a).remove();
}

function pm_get_my_friends(pagenum,uid)
{
  //  console.log('hi you are entring in my friends section.');
    var page = parseInt(pagenum);
    var data = {action: 'pm_fetch_my_friends', 'uid': uid,'pagenum':page,'pm_friend_view':1,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
         //   console.log('hi you are entring in my friends response section.');
            jQuery('#pg-myfriends').html(response);
             var pmDomColor = jQuery(".pmagic").children("a").css('color');
             jQuery(".pm-color").css('color', pmDomColor);
            jQuery( ".pmagic .page-numbers .page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        }
    });
}


function pm_get_friend_requests_sent(pagenum,uid)
{
    var page = parseInt(pagenum);
    var data = {action: 'pm_fetch_my_friends', 'uid': uid,'pagenum':page,'pm_friend_view':3,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('#pg-requests-sent').html(response);
             var pmDomColor = jQuery(".pmagic").children("a").css('color');
             jQuery(".pm-color").css('color', pmDomColor);
            jQuery( ".pmagic .page-numbers .page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        }
    });
}


function pm_get_friend_requests(pagenum,uid)
{
    var page = parseInt(pagenum);
    var data = {action: 'pm_fetch_my_friends', 'uid': uid,'pagenum':page,'pm_friend_view':2,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('#pg-friend-requests').html(response);
             var pmDomColor = jQuery(".pmagic").children("a").css('color');
             jQuery(".pm-color").css('color', pmDomColor);
            jQuery( ".pmagic .page-numbers .page-numbers.current" ).addClass( "pm-bg" ).css('background', pmDomColor); 
        }
    });
}
