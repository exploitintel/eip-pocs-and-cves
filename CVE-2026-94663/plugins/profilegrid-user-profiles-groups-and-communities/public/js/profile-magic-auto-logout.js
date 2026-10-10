var idleTime = 0;
var pm_autologout_define_time = pm_autologout_obj.pm_auto_logout_time;
var pm_show_prompt_box = pm_autologout_obj.pm_show_logout_prompt;
//Increment the idle time counter every minute.
var idleInterval = setInterval(timerIncrement, 1000); // 1 second
(function( $ ) {
	'use strict';
    //Zero the idle timer on mouse movement.
    $(document).mousemove(function (e) {
        idleTime = 0;
    });
    $(document).on( 'keypress', function (e) {
        idleTime = 0;
    });
})(jQuery);
function timerIncrement() {
    idleTime = idleTime + 1;
    
    if (idleTime > pm_autologout_define_time) { // 20 minutes
        //alert("You are now logged out.");
        if(pm_show_prompt_box=='1')
        {
            jQuery('#pm-autologout-dialog').show();
            jQuery('#pm-autologout-dialog .pm-popup-container').show();
            jQuery('#pm-autologout-dialog .pm-popup-mask').show();
        }
        else
        {
            pg_auto_logout_redirect();
        } 
        idleTime = 0;
    }
}

function pg_auto_logout_redirect()
{
     var data ={action:'pm_auto_logout_user',nonce: pm_ajax_object.nonce};
        jQuery.post(pm_ajax_object.ajax_url, data, function (response)
        {
            
           if(response!='')
           {
               window.location = response;
           }
        });
}

function pg_auto_logout_prompt_close()
{
   jQuery('.pm-popup-mask').hide();
   jQuery('.pm-popup-mask').next().hide();
    
}
