/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function( $ ) {
	'use strict';

//hook into heartbeat-send
    
    $(document).on('heartbeat-send', function(e, data) {
        data['pm_notify_status'] = 'ready';	//need some data to kick off AJAX call
    });
    
    //hook into heartbeat-tick: client looks in data var for natifications
    $(document).on('heartbeat-tick.profilegrid_tick', function(e, data) 
    {
        var uid = $('#pm-uid').val();
        pm_get_my_friends(1,uid);
        pm_get_friend_requests(1,uid);
        pm_get_friend_requests_sent(1,uid);
        pm_update_counter(uid);	
        var val=$("#notification_tab").attr('aria-selected');
        if(val=== 'true')
        {
          if($("#unread_notification_count").val()!='')
            pm_read_all_notification();
        }
        else{
         //   console.log("inactive notification tab");
        }
        if(data['unread_notif']!=0)
        {
             $("#unread_notification_count").addClass("thread-count-show"); 
             $("#unread_notification_count").html(data['unread_notif']);   

         }
        else
        {
            $("#unread_notification_count").html('');   
            $("#unread_notification_count").removeClass("thread-count-show");     
        }

        if(!data['pm_notify'])
        {
          //  console.log("no data ");     
            return;
        }
        $.each( data['pm_notify'], function( index, notification ) 
        {
            if ( index != 'blabla' )
            {
                if(notification!='')
                {
                    $("#pm_notification_view_area").prepend(notification);
                }
            }
          			
        });
    });

    //hook into heartbeat-error: in case of error, let's log some stuff
    $(document).on('heartbeat-error', function(e, jqXHR, textStatus, error) {
       //     console.log('BEGIN ERROR');
        //    console.log(textStatus);
        //    console.log(error);			
        //    console.log('END ERROR');			
    });
    
    //pm_messenger_notification_extra_data(); 
})(jQuery);