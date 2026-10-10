function pm_change_search_field(a)
{
    var group = a;
    var data ={'action':'pm_advance_search_get_search_fields_by_gid', 'gid' : group, 'match_fields': ' '};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response){
       if(response){
           jQuery('#advance_seach_ul').empty();
         jQuery('#advance_seach_ul').append(response);
         pm_advance_user_search('');
         if ( typeof pg_refresh_search_field == 'function' ) { 
            pg_refresh_search_field();
        }

     }else{
           //console.log("err");
       }

    });
}
function pm_remove_attachment(obj,key,value)
{
    jQuery('#pm-edit-group-popup, .pm-popup-mask, .pg-blog-dialog-mask').toggle();
    ( function($) {
        $( "#pg-remove-attachment-dialog" ).dialog({
          resizable: false,
          height: "auto",
          width: 400,
          modal: true,
          buttons: {
            "Yes": function() {
                
                var data = {
                        'action': 'pm_remove_attachment',
                        'key': key,
                        'value':value,
                        'nonce':pm_ajax_object.nonce
                };
                $.post(pm_ajax_object.ajax_url, data, function(response) {
                    if(response)
                    {
                        $(obj).parent('a').parent('span.pm_frontend_attachment').remove();
                    }
                });

                $( this ).dialog( "close" );
            },
            "No": function() {
              $( this ).dialog( "close" );
            }
          }
        });
    } )(jQuery);
}

function pm_expand_all_conent()
{
	jQuery("#pm-accordion .pm-accordian-content").show();	
}

function pm_collapse_all_conent()
{
	jQuery("#pm-accordion .pm-accordian-content").hide();	
}
 
function pm_show_hide(obj,primary,secondary,trinary)
{	
	a = jQuery(obj).is(':checked');
	if (a == true)
	 {
		jQuery('#'+primary).show(500);
		if(secondary!='')
		{
			jQuery('#'+secondary).hide(500);
		}
		if(trinary!='')
		{
			jQuery('#'+trinary).hide(500);
		}		
	}
	else 
	{
		jQuery('#'+primary).hide(500);
		if(secondary!='')
		{
			jQuery('#'+secondary).show(500);
		}
		if(trinary!='')
		{
			jQuery('#'+trinary).show(500);
		}
	}
	
}

function pm_add_repeat(obj)
{
	a= jQuery(obj).parent('a').parent('div.pm_repeat').clone();
	jQuery(a).children('input').val('');
	jQuery(obj).parent('a').parent('div.pm_repeat').parent('div.pm-field-input').append(a);
}

function pm_remove_repeat(obj)
{
	jQuery(obj).parent('a').parent('div.pm_repeat').remove();
}

function validate_phone_number2(number)
{
    var isnumber = jQuery.isNumeric(number);
    var regex = /^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]\d{4}$/;
    var phone_num = number.replace(/[^\d]/g, '');
    if ( number != "")
    {
//        if(isnumber == false )
//        return false;
    
    if(phone_num.length <10 || phone_num.length > 13)
        return false;
    
//    if(!regex.test(number))
//        return false;
    
    return true;
    }else
    {
        return true;
    }
   

}

function validate_phone_number(number) {
    if(number!=""){
    var phone_num = number.replace(/[^\d]/g, '');
    var a = number;
       var phone_num = number.replace(/[^\d]/g, '');
    var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
    if (filter.test(a)&&(phone_num.length >=10 && phone_num.length <= 13)) {
        //console.log(phone_num);
        return true;
    }
    else {
        return false;
    }
    }else{
        return true;
    }
}

function validateMobileNumber(number) {
    if(number!=""){
        var phone_num = number.replace(/[^\d]/g, '');
        var a = number;
        var phone_num = number.replace(/[^\d]/g, '');
        var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
        if (filter.test(a)&&(phone_num.length >=10 && phone_num.length <= 13)) {
            //console.log(phone_num);
            return true;
        }
        else {
            return false;
        }
    }else{
        return true;
    }
    /*if (number != "") {
        const regex = /^\+?(\d{1,4})?[-.\s]?((\d{10,15})|(\d{6,9}))$/;
        return regex.test(number);
    }
    else
    {
        return true;
    }*/
}

function validate_facebook_url(val)
{
    if (val != "") {
        if (/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*?(\/)?([\w\-\.]*)/i.test(val))
        {
            return true;
        } else
        {
            return false;
        }
    } else {
        return true;
    }

}

function validate_twitter_url(val) {
    if (val !== '') {
        const regex = /^(https?:\/\/)(www\.)?(twitter\.com|x\.com)\/([A-Za-z0-9_]{1,15})\/?$/i;
        return regex.test(val);
    } else {
        return true;
    }
}


function validate_google_url(val)
{
    if (val != '') {
        if (/((http:\/\/(plus\.google\.com\/.*|www\.google\.com\/profiles\/.*|google\.com\/profiles\/.*))|(https:\/\/(plus\.google\.com\/.*)))/i.test(val)) {
            return true;
        } else {
            return false;
        }

    } else {
        return true;
    }
}

function validate_linked_in_url(val)
{   if (val != '') {
        if (/(ftp|http|https):\/\/?((www|\w\w)\.)?linkedin.com(\w+:{0,1}\w*@)?(\S+)(:([0-9])+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(val)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function validate_youtube_url(val)
{
    if (val != '') {
        if (/(ftp|http|https):\/\/?((www|\w\w)\.)?(youtube\.com|youtu\.be)(\w+:{0,1}\w*@)?(\S+)(:([0-9])+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(val)) {
            return true;
        } else {
            return false;
        }

    } else {
        return true;
    }
}

function validate_soundcloud_url(val)
{
    if (val != '') {
        if (/(ftp|http|https):\/\/?((www|\w\w)\.)?soundcloud.com(\w+:{0,1}\w*@)?(\S+)(:([0-9])+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(val)) {
            return true;
        } else {
            return false;
        }

    } else {
        return true;
    }
}

function validate_mixcloud_url(val)
{
    if (val != '') {
        if (/(ftp|http|https):\/\/?((www|\w\w)\.)?mixcloud.com(\w+:{0,1}\w*@)?(\S+)(:([0-9])+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(val)) {
            return true;
        } else {
            return false;
        }

    } else {
        return true;
    }
}

function validate_instagram_url(val)
{
    if (val != '') {
        var regex = /(?:(?:http|https):\/\/)?(?:www.)?(?:instagram.com|instagr.am)\/([A-Za-z0-9-_]+)/;
        if (val.match(regex)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function profile_magic_frontend_validation(form)
{
	
	var email_val = "";
	var formid = form.id;
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	jQuery('.errortext').html('');
	jQuery('.errortext').hide();
	jQuery('.all_errors').html('');
	jQuery('.warning').removeClass('warning');
        jQuery('.pg-form-validation-error').removeClass('pg-form-validation-error');

        jQuery('#'+formid+' .pm_email').each(function (index, element) {
		var email = jQuery(this).children('input').val();
		var isemail = regex.test(email);
		if (isemail == false && email != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_email);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
		var isnumber = jQuery.isNumeric(number);
		if (isnumber == false && number != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_number);
			jQuery(this).children('.errortext').show();
		}
	});
	
        	
	jQuery('#'+formid+' .pm_phone_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_phone_number(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_phone_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mobile_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validateMobileNumber(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mobile_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_facebook_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_facebook_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_facebook_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_twitter_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_twitter_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_twitter_url);
			jQuery(this).children('.errortext').show();
		}
	});

            
        jQuery('#'+formid+' .pm_google_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_google_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_google_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_linked_in_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_linked_in_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_linked_in_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_youtube_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_youtube_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_youtube_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mixcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_mixcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mixcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_soundcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_soundcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_soundcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_instagram_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_instagram_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_instagram_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
	jQuery('#'+formid+' .pm_datepicker').each(function (index, element) {
		var date = jQuery(this).children('input').val();
		var pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                if(pm_fields_object.dateformat == 'dd-mm-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy-mm-dd'){
                    pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm-dd-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat == 'dd.mm.yy'){
                    pattern = /^([0-9]{2}).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy.mm.dd'){
                    pattern = /^([0-9]{4}).([0-9]{2}).([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm.dd.yy'){
                    pattern = /^([0-9]{2}).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy/mm/dd'){
                    pattern = /^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm/dd/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }else if(pm_fields_object.dateformat =='dd/mm/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }
                if (date != "" && !pattern.test(date)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_date);
			jQuery(this).children('.errortext').show();
		}
            
	});
	
	jQuery('#'+formid+' .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_select_required').each(function (index, element) {
		var value = jQuery(this).children('select').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('select').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
        
	jQuery('#'+formid+' .pm_rich_editor_required').each(function (index, element) {
           
	});
        
	jQuery('#'+formid+' .pm_textarearequired').each(function (index, element) {
		var value = jQuery(this).children('textarea').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_checkboxrequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="checkbox"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_radiorequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="radio"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_fileinput .pm_repeat').each(function (index, element) {
		var val = jQuery(this).children('input').val().toLowerCase();
		var allowextensions = jQuery(this).children('input').attr('data-filter-placeholder');
		if(allowextensions=='')
		{
			allowextensions = pm_error_object.allow_file_ext;
		}
		
		allowextensions = allowextensions.toLowerCase();
		var regex = new RegExp("(.*?)\.(" + allowextensions + ")$");
		if(!(regex.test(val)) && val!="") {
		
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.file_type);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_repeat_required .pm_repeat').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_user_pass').each(function (index, element) {
		var password = jQuery(this).children('input').val();
		var passwordlength = password.length;
		if(password !="")
		{
			if(passwordlength < 7)
			{
				jQuery(this).children('input').addClass('warning');
				jQuery(this).children('.errortext').html(pm_error_object.short_password);
				jQuery(this).children('.errortext').show();
			}
		}
	});
	
	jQuery('#'+formid+' .pm_confirm_pass').each(function (index, element) {
		var confirm_pass = jQuery(this).children('input').val();
		var password = password = jQuery('#'+formid+' .pm_user_pass').children('input').val();
		if(password != confirm_pass)
		{
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.pass_not_match);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_recaptcha').each(function (index, element) {
		if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.getResponse === 'function') {
			var response = grecaptcha.getResponse();
			// recaptcha failed validation
			if (response.length == 0) {
				jQuery(this).children('.errortext').html(pm_error_object.required_field);
				jQuery(this).children('.errortext').show();
			}
		}
	});
	
	var b = '';
	 jQuery('#'+formid+' .errortext').each(function () {
		var a = jQuery(this).html();
                if(a!=='')
                {
                    jQuery(this).addClass('pg-form-validation-error');
                }
		b = a + b;
	});
	
	if (jQuery('#'+formid+' .usernameerror').length > 0) 
		{
			c = jQuery('#'+formid+' .usernameerror').html();
                        if(c!=='')
                        {
                            jQuery('#'+formid+' .usernameerror').addClass('pg-form-validation-error');
                        }
			b = c + b;
		}
                else
                {
                    c = '';
                }
		
		if (jQuery('#'+formid+' .useremailerror').length > 0) 
		{
			d = jQuery('.useremailerror').html();
                         if(d!=='')
                        {
                            jQuery('#'+formid+' .useremailerror').addClass('pg-form-validation-error');
                        }
			b = c + b;
			b = d + b;
		}
	jQuery('#'+formid+' .all_errors').html(b);
	var error = jQuery('#'+formid+' .all_errors').html();
	if (error == '') {
		return true;
	} else {
            jQuery('html, body').animate({
                scrollTop: jQuery('#'+formid+' .pg-form-validation-error').first().offset().top-40
            }, 500);
            jQuery('.pg-edit-group-popup-loader').remove();
            jQuery('input[type=submit][name=reg_form_submit]').removeClass('pg-stripe-submit-disabled');
		return false;
	}
}

function profile_magic_frontend_validation_edit_profile(form)
{
	
	var email_val = "";
	var formid = form.id;
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	jQuery('.errortext').html('');
	jQuery('.errortext').hide();
	jQuery('.all_errors').html('');
	jQuery('.warning').removeClass('warning');

        jQuery('#'+formid+' .pm_email').each(function (index, element) {
		var email = jQuery(this).children('input').val();
		var isemail = regex.test(email);
		if (isemail == false && email != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_email);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
		var isnumber = jQuery.isNumeric(number);
		if (isnumber == false && number != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_number);
			jQuery(this).children('.errortext').show();
		}
	});
	
        	
	jQuery('#'+formid+' .pm_phone_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_phone_number(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_phone_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mobile_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validateMobileNumber(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mobile_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_facebook_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_facebook_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_facebook_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_twitter_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_twitter_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_twitter_url);
			jQuery(this).children('.errortext').show();
		}
	});

            
        jQuery('#'+formid+' .pm_google_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_google_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_google_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_linked_in_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_linked_in_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_linked_in_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_youtube_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_youtube_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_youtube_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mixcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_mixcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mixcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_soundcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_soundcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_soundcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_instagram_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_instagram_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_instagram_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
	jQuery('#'+formid+' .pm_datepicker').each(function (index, element) {
		var date = jQuery(this).children('input').val();
		var pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                if(pm_fields_object.dateformat == 'dd-mm-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy-mm-dd'){
                    pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm-dd-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat == 'dd.mm.yy'){
                    pattern = /^([0-9]{2}).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy.mm.dd'){
                    pattern = /^([0-9]{4}).([0-9]{2}).([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm.dd.yy'){
                    pattern = /^([0-9]{2]).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy/mm/dd'){
                    pattern = /^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm/dd/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }else if(pm_fields_object.dateformat =='dd/mm/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }
                if (date != "" && !pattern.test(date)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_date);
			jQuery(this).children('.errortext').show();
		}
            
	});
	
	jQuery('#'+formid+' .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_select_required').each(function (index, element) {
		var value = jQuery(this).children('select').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('select').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
        
	jQuery('#'+formid+' .pm_rich_editor_required').each(function (index, element) {
           
	});
        
	jQuery('#'+formid+' .pm_textarearequired').each(function (index, element) {
		var value = jQuery(this).children('textarea').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_checkboxrequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="checkbox"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_radiorequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="radio"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_fileinput .pm_repeat').each(function (index, element) {
		var val = jQuery(this).children('input').val().toLowerCase();
		var allowextensions = jQuery(this).children('input').attr('data-filter-placeholder');
		if(allowextensions=='')
		{
			allowextensions = pm_error_object.allow_file_ext;
		}
		
		allowextensions = allowextensions.toLowerCase();
		var regex = new RegExp("(.*?)\.(" + allowextensions + ")$");
		if(!(regex.test(val)) && val!="") {
		
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.file_type);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_repeat_required .pm_repeat').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_user_pass').each(function (index, element) {
		var password = jQuery(this).children('input').val();
		var passwordlength = password.length;
		if(password !="")
		{
			if(passwordlength < 7)
			{
				jQuery(this).children('input').addClass('warning');
				jQuery(this).children('.errortext').html(pm_error_object.short_password);
				jQuery(this).children('.errortext').show();
			}
		}
	});
	
	jQuery('#'+formid+' .pm_confirm_pass').each(function (index, element) {
		var confirm_pass = jQuery(this).children('input').val();
		var password = password = jQuery('#'+formid+' .pm_user_pass').children('input').val();
		if(password != confirm_pass)
		{
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.pass_not_match);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_recaptcha').each(function (index, element) {
		var response = grecaptcha.getResponse();
				//recaptcha failed validation
		if (response.length == 0) {
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	var b = '';
	 jQuery('#'+formid+' .errortext').each(function () {
		var a = jQuery(this).html();
		b = a + b;
	});
	
	if (jQuery('#'+formid+' .usernameerror').length > 0) 
		{
			c = jQuery('.usernameerror').html();
			b = c + b;
		}
		
		if (jQuery('#'+formid+' .useremailerror').length > 0) 
		{
			d = jQuery('.useremailerror').html();
			b = d + b;
		}
	jQuery('#'+formid+' .all_errors').html(b);
	var error = jQuery('#'+formid+' .all_errors').html();
	if (error == '') {
		return true;
	} else {
            pm_expand_all_conent();
            jQuery(window).scrollTop( jQuery(".warning:first").offset().top);
            console.log(jQuery(".warning:first").offset().top);
		return false;
	}
}

function pm_frontend_check_username(formid)
{
	jQuery('.pm_user_name').each(function (index, element) {
			var field = this;
			var username = jQuery(this).children('input').val();
			var data = {
							'action': 'pm_check_user_exist',
                                                        'nonce': pm_ajax_object.nonce,
							'type': 'validateUserName',
							'userdata' : username
						};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
				if(response=="true")
				{
					jQuery(field).children('input').addClass('warning');
					jQuery(field).children('.usernameerror').html(pm_error_object.user_exist);
					jQuery(field).children('.usernameerror').show();
				}
				else
				{
					jQuery(field).children('input').removeClass('warning');
					jQuery(field).children('.usernameerror').html('');
					jQuery(field).children('.usernameerror').hide();
				}
				
			});		
		});	
}

function pm_frontend_check_useremail(previous)
{
	jQuery('.pm_user_email').each(function (index, element) {
		var field = this;
		var username = jQuery(this).children('input').val();
		var data = {
						'action': 'pm_check_user_exist',
                                                'nonce': pm_ajax_object.nonce, 
						'type': 'validateUserEmail',
						'userdata' : username,
                                                'previous_data':previous
					};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
			if(response=="true")
			{
				jQuery(field).children('input').addClass('warning');
				jQuery(field).children('.useremailerror').html(pm_error_object.email_exist);
				jQuery(field).children('.useremailerror').show();
			}
			else
			{
				jQuery(field).children('input').removeClass('warning');
				jQuery(field).children('.useremailerror').html('');
				jQuery(field).children('.useremailerror').hide();	
			}
		});		
	});
}

function pm_frontend_change_password(form)
{
	var pass1 = jQuery(form).children('.pmrow').children('.pm-col').children('.pm-field-input').children('#pass1').val();	
        var pass2 = jQuery(form).children('.pmrow').children('.pm-col').children('.pm-field-input').children('#pass2').val();	
	var userid = jQuery(form).find('input[name="user_id"]').val();
        jQuery('#pm_reset_passerror').removeClass('pm_password_error');
        jQuery('#pm_reset_passerror').removeClass('pm_password_success');
	var data = {'action': 'pm_change_frontend_user_pass','pass1': pass1,'pass2' : pass2, 'nonce': pm_ajax_object.nonce};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
			if(response==true)
			{
                            jQuery('#pm_reset_passerror').addClass('pm_password_success');
				jQuery('#pm_reset_passerror').html(pm_error_object.password_change_successfully);
				jQuery('#pm_reset_passerror').show();
                                window.location = pm_error_object.login_url;
                        }
                        else
                        {
                            
                            jQuery('#pm_reset_passerror').addClass('pm_password_error');
                            jQuery('#pm_reset_passerror').html(response);
			    jQuery('#pm_reset_passerror').show();
                        }
		});		
	return false;
}

var searchRequest = null; 
function pm_advance_user_search(pagenum)
{
    var form = jQuery("#pm-advance-search-form");
    var ajaxUrl = (window.pm_ajax_object && pm_ajax_object.ajax_url) ? pm_ajax_object.ajax_url : (typeof window.ajaxurl === 'string' ? window.ajaxurl : '');
    var ajaxNonce = (window.pm_ajax_object && pm_ajax_object.nonce) ? pm_ajax_object.nonce : '';

    if (ajaxUrl === '') {
        return;
    }

    jQuery("#pm_result_pane").html('<div class="pm-loader"></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
  

    if (jQuery('#advance_seach_ul li').length === 0){
       jQuery('#pm-advance-search-form #advance_search_pane #advance_seach_ul').addClass("pg-advance-search-hide"); 
    }else{
        jQuery('#pm-advance-search-form #advance_search_pane #advance_seach_ul').removeClass("pg-advance-search-hide"); 
    }

       
       
    if(pagenum!== '')
    {
            if(pagenum=='Reset')
            {
                form.trigger('reset');
                jQuery('#advance_search_pane').hide(200);
                jQuery('#pagenum').attr("value",1);
                jQuery('input[type=checkbox]').attr("checked",false);
                pm_change_search_field('');
            }
            else
            {
                jQuery('#pagenum').attr("value",pagenum);
            }
        
    }
    else
    {
         jQuery('#pagenum').attr("value",1);
    }
    var form_values = form.serializeArray();

    var data = {};

    if (ajaxNonce !== '') {
        data.nonce = ajaxNonce;
    }

    //creating data in object format and array for multiple checkbox
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
    //console.log(data);
   
    if(searchRequest != null)
        searchRequest.abort();
        //ajax call start
    searchRequest =    jQuery.post(ajaxUrl, data, function (resp) 
        {
        
                if (resp)
                {   
                    jQuery("#pm_result_pane").html(resp);
                    
        var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-color").css('color', pmDomColor);
        jQuery( ".page-numbers.current" ).css('background', pmDomColor); 
                } 
                else
                {
                    //console.log("err");
                }
            
         });
         //ajax call ends here
         
         


}

function profile_magic_send_email(userid)
{
    var data = {'action': 'pm_send_change_pass_email','userid': userid,'nonce': pm_ajax_object.nonce};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(pm_ajax_object.ajax_url, data, function(response) {
                    
		});		
	return false;
}

function profile_magic_multistep_form_validation(form)
{
	
	var email_val = "";
	var formid = form.attr('id');
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (typeof pm_fields_object === 'undefined' || pm_fields_object === null) {
		window.pm_fields_object = { dateformat: 'yy-mm-dd' };
	}
	jQuery('.errortext').html('');
	jQuery('.errortext').hide();
	jQuery('.all_errors').html('');
	jQuery('.warning').removeClass('warning');
jQuery('#'+formid+' .pm_email').each(function (index, element) {
		var email = jQuery(this).children('input').val();
		var isemail = regex.test(email);
		if (isemail == false && email != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_email);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
		var isnumber = jQuery.isNumeric(number);
		if (isnumber == false && number != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_number);
			jQuery(this).children('.errortext').show();
		}
	});
	
        jQuery('#'+formid+' .pm_phone_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_phone_number(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_phone_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mobile_number').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validateMobileNumber(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mobile_number);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_facebook_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_facebook_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_facebook_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_twitter_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_twitter_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_twitter_url);
			jQuery(this).children('.errortext').show();
		}
	});

            
        jQuery('#'+formid+' .pm_google_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_google_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_google_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_linked_in_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_linked_in_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_linked_in_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
                
        jQuery('#'+formid+' .pm_youtube_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_youtube_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_youtube_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_mixcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_mixcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_mixcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_soundcloud_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_soundcloud_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_soundcloud_url);
			jQuery(this).children('.errortext').show();
		}
	});
                
        jQuery('#'+formid+' .pm_instagram_url').each(function (index, element) {
		var number = jQuery(this).children('input').val();
                if (!validate_instagram_url(number)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_instagram_url);
			jQuery(this).children('.errortext').show();
		}
	});
        
	jQuery('#'+formid+' .pm_datepicker').each(function (index, element) {
		var date = jQuery(this).children('input').val();
		var pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                if(pm_fields_object.dateformat == 'dd-mm-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy-mm-dd'){
                    pattern = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm-dd-yy'){
                    pattern = /^([0-9]{2})-([0-9]{2})-([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat == 'dd.mm.yy'){
                    pattern = /^([0-9]{2}).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy.mm.dd'){
                    pattern = /^([0-9]{4}).([0-9]{2}).([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm.dd.yy'){
                    pattern = /^([0-9]{2}).([0-9]{2}).([0-9]{4})$/;
                }
                else if(pm_fields_object.dateformat =='yy/mm/dd'){
                    pattern = /^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/;
                }
                else if(pm_fields_object.dateformat =='mm/dd/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }else if(pm_fields_object.dateformat =='dd/mm/yy'){
                    pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                }
    	if (date != "" && !pattern.test(date)) {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_date);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_select_required').each(function (index, element) {
		var value = jQuery(this).children('select').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('select').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_textarearequired').each(function (index, element) {
		var value = jQuery(this).children('textarea').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_checkboxrequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="checkbox"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_radiorequired').each(function (index, element) {
		var checkboxlenght = jQuery(this).children('.pmradio').children('.pm-radio-option').children('input[type="radio"]:checked');
		var atLeastOneIsChecked = checkboxlenght.length > 0;
		if (atLeastOneIsChecked == true) {
		}else{
			//jQuery(this).children('textarea').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_fileinput .pm_repeat').each(function (index, element) {
		var val = jQuery(this).children('input').val().toLowerCase();
		var allowextensions = jQuery(this).children('input').attr('data-filter-placeholder');
		if(allowextensions=='')
		{
			allowextensions = pm_error_object.allow_file_ext;
		}
		
		allowextensions = allowextensions.toLowerCase();
		var regex = new RegExp("(.*?)\.(" + allowextensions + ")$");
		if(!(regex.test(val)) && val!="") {
		
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.file_type);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_repeat_required .pm_repeat').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_user_pass').each(function (index, element) {
		var password = jQuery(this).children('input').val();
		var passwordlength = password.length;
		if(password !="")
		{
			if(passwordlength < 7)
			{
				jQuery(this).children('input').addClass('warning');
				jQuery(this).children('.errortext').html(pm_error_object.short_password);
				jQuery(this).children('.errortext').show();
			}
		}
	});
	
	jQuery('#'+formid+' .pm_confirm_pass').each(function (index, element) {
		var confirm_pass = jQuery(this).children('input').val();
		var password = password = jQuery('#'+formid+' .pm_user_pass').children('input').val();
		if(password != confirm_pass)
		{
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.pass_not_match);
			jQuery(this).children('.errortext').show();
		}
	});
	
	jQuery('#'+formid+' .pm_recaptcha').each(function (index, element) {
		var response = grecaptcha.getResponse();
				//recaptcha failed validation
		if (response.length == 0) {
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
	
	var all_errors = '';
	jQuery('#'+formid+' .errortext').each(function () {
		var a = jQuery(this).html();
		all_errors = a + all_errors;
	});
		if (jQuery('#'+formid+' .usernameerror').length > 0) 
		{
			c = jQuery('.usernameerror').html();
			if(jQuery.trim(c)!='')
			jQuery('.pm_user_name').children('input').addClass('warning');
			all_errors = c + all_errors;
		}
		
		if (jQuery('#'+formid+' .useremailerror').length > 0) 
		{
			d = jQuery('.useremailerror').html();
			if(jQuery.trim(d)!='')
			jQuery('.pm_user_email').children('input').addClass('warning');
			all_errors = d + all_errors;
		}
	jQuery('#'+formid+' .all_errors').html(all_errors);
	var error = jQuery('#'+formid+' .all_errors').html();
	if (error == '') {
		return true;
	} else {
		return false;
	}
}

function openParentTab() 
{
	locationHash = location.hash.substring( 1 );
	//console.log(locationHash);
	// Check if we have an location Hash
	if (locationHash) {
		// Check if the location hash exsist.
		var hash = jQuery('#'+locationHash);
		if (hash.length) {
			 var t = hash;
                        jQuery('li.pm-profile-tab a').removeClass('active');         
                        jQuery(this).addClass('active');
                        jQuery('.pg-profile-tab-content').hide();
                        jQuery(t).find('.pm-section-content:first').show();
                        jQuery('li.hideshow ul').hide();
                        jQuery(t).fadeIn('slow');
                        return false;
		}
	}
}

function generateTabs(tabs) { 

	html = '';
	for (var i in tabs) { 
		tab = tabs[i];
		html = html + '<li class="multipage_tab"><a href="#" onclick="return jQuery(\'#multipage\').gotopage(' + tab.number + ');">' + tab.title + '</a></li>';				
	}
	jQuery('<ul class="multipage_tabs" id="multipage_tabs">'+html+'<div class="clearer"></div></ul>').insertBefore('#multipage');
}
function setActiveTab(selector,page) { 
	jQuery('#multipage_tabs li').each(function(index){ 
		if ((index+1)==page) { 
			jQuery(this).addClass('active');
		} else {
			jQuery(this).removeClass('active');
		}
	});			
}
		
function transition(from,to) {
	jQuery(from).fadeOut('fast',function(){jQuery(to).fadeIn('fast');});

}
function textpages(obj,page,pages) { 
	jQuery(obj).html(page + ' of ' + pages);
}

function pm_user_image_validation(a)
{
	var val = jQuery(a).children('.pm-user-image').val().toLowerCase();
	if(val=='')
	{
		jQuery(a).children('pm-user-image').addClass('warning');
		jQuery(a).children('.pm-popup-error').html(pm_error_object.required_field);
		jQuery(a).children('.pm-popup-error').show();
		return false;
	}
	
	var allowextensions = 'jpg|jpeg|png|gif|webp|avif';
	if(allowextensions=='')
	{
		allowextensions = pm_error_object.allow_file_ext;
	}
	allowextensions = allowextensions.toLowerCase();
	var regex = new RegExp("(.*?)\.(" + allowextensions + ")$");
	if(!(regex.test(val)) && val!="") {
		jQuery(a).children('pm-user-image').addClass('warning');
		jQuery(a).children('.pm-popup-error').html(pm_error_object.file_type);
		jQuery(a).children('.pm-popup-error').show();
		return false;
	}
	else
	{
		jQuery(a).children('.pm-popup-error').html('');
		jQuery(a).children('.pm-popup-error').hide();
		return true;
	}
}





//GUI Functions
function callPmPopup(dialog) {
    var pmId = dialog + "-dialog";
    jQuery(pmId).siblings('.pm-popup-mask').show();
    jQuery(pmId).show();
    jQuery('.pm-popup-container').css("animation", "pm-popup-in 0.3s ease-out 1");
}




function profile_magic_blogpost_validation()
{
	jQuery('.errortext').html('');
	jQuery('.errortext').hide();
	jQuery('.all_errors').html('');
	jQuery('.warning').removeClass('warning');
        jQuery('#pm_add_blog_post .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#pm_add_blog_post .pm_fileinput .pm_repeat').each(function (index, element) {
		var val = jQuery(this).children('input').val().toLowerCase();
		var allowextensions = 'jpg|jpeg|png|gif|webp|avif';
		if(allowextensions=='')
		{
			allowextensions = pm_error_object.allow_file_ext;
		}
		
		allowextensions = allowextensions.toLowerCase();
		var regex = new RegExp("(.*?)\.(" + allowextensions + ")$");
		if(!(regex.test(val)) && val!="") {
		
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.file_type);
			jQuery(this).children('.errortext').show();
		}
	});
        var all_errors = '';
	jQuery('#pm_add_blog_post .errortext').each(function () {
		var a = jQuery(this).html();
		all_errors = a + all_errors;
	});
        jQuery('#pm_add_blog_post .all_errors').html(all_errors);
	var error = jQuery('#pm_add_blog_post .all_errors').html();
	if (error == '') {
            
            jQuery('input[name="pg_blog_submit"]').attr('disabled','disabled');
		return true;
	} else {
		return false;
	}
}

function load_more_pg_blogs(uid)
{
    jQuery('.pm-load-more-blogs').hide();
    jQuery('.pg-load-more-container .pm-loader').show();
    var page = parseInt(jQuery('#pg_next_blog_page').val());
    var nextpage = page +1;
    var data = {action: 'pm_load_pg_blogs', 'uid': uid,'page':page,'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('.pg-load-more-container .pm-loader').hide();
            jQuery('#pg_next_blog_page').val(nextpage);
            jQuery('#pg-blog-container').append(response);
        }
    });

}

function load_more_user_blogs_shortcode_posts(authors,posttypes)
{
    jQuery('.pm-load-more-blogs').hide();
    jQuery('.pg-load-more-container .pm-loader').show();
    var page = parseInt(jQuery('#pg_next_blog_page').val());
    var nextpage = page +1;
    var data = {action: 'pm_load_user_blogs_shortcode_posts',authors: authors, posttypes:posttypes, page:page, 'nonce': pm_ajax_object.nonce};
    jQuery.post(pm_ajax_object.ajax_url, data, function (response) {
        if(response)
        {
            jQuery('.pg-load-more-container .pm-loader').hide();
            jQuery('#pg_next_blog_page').val(nextpage);
            jQuery('#pg-user-blog-container').append(response);
        }
    });

}



function pm_delete_account_setting(form)
{
        var formid = form.id;
        jQuery('#'+formid+' .errortext').html('');
	jQuery('#'+formid+' .errortext').hide();
	jQuery('#'+formid+' .all_errors').html('');
	jQuery('#'+formid+' .warning').removeClass('warning');
        jQuery('#'+formid+' .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
        var all_errors = '';
	jQuery('#'+formid+' .errortext').each(function () {
		var a = jQuery(this).html();
		all_errors = a + all_errors;
	});
        
        jQuery('#'+formid+' .all_errors').html(all_errors);
	var error = jQuery('#'+formid+' .all_errors').html();
	if (error == '') 
        {
		return true;
	} 
        else 
        {
		return false;
	}
        
}
function pm_save_account_setting(form)
{
    var email_val = "";
    var formid = form.id;
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    jQuery('#'+formid+' .errortext').html('');
	jQuery('#'+formid+' .errortext').hide();
	jQuery('#'+formid+' .all_errors').html('');
	jQuery('#'+formid+' .warning').removeClass('warning');
        jQuery('#'+formid+' .pm_required').each(function (index, element) {
		var value = jQuery(this).children('input').val();
		var value = jQuery.trim(value);
		if (value == "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.required_field);
			jQuery(this).children('.errortext').show();
		}
	});
        
        jQuery('#'+formid+' .pm_email').each(function (index, element) {
		var email = jQuery(this).children('input').val();
		var isemail = regex.test(email);
		if (isemail == false && email != "") {
			jQuery(this).children('input').addClass('warning');
			jQuery(this).children('.errortext').html(pm_error_object.valid_email);
			jQuery(this).children('.errortext').show();
		}
	});
	
        var all_errors = '';
	jQuery('#'+formid+' .errortext').each(function () {
		var a = jQuery(this).html();
		all_errors = a + all_errors;
	});
        
        jQuery('#'+formid+' .all_errors').html(all_errors);
	var error = jQuery('#'+formid+' .all_errors').html();
	if (error == '') 
        {
		return true;
	} 
        else 
        {
		return false;
	}
        
}

(function PG_mobile_resposve ($) {
    var $window = $(window),
        $html = $('html'),
        $is_resize = 0;
        

    $window.on( 'resize', function resize() {
        if ($window.width() < 479) {
            if($is_resize==0)
            {
                show_pg_section_left_panel();
                $is_resize = 1;
            }
            
            return $html.addClass('pg-mobile-479');
        }
        else
        {
            $('.pm-section-left-panel').show();
            $('.pm-section-right-panel').show();
        }

        $html.removeClass('pg-mobile-479');
        
        if ($window.width() < 760) {
            return $html.addClass('pg-mobile-760');
            
        }
        
        $html.removeClass('pg-mobile-760');
        
        
         if ($window.width() < 979) {
            return $html.addClass('pg-mobile-979');
            
        }

        $html.removeClass('pg-mobile-979');
        
    }).trigger('resize');
})(jQuery);

function show_pg_section_right_panel()
{
    jQuery(".pg-mobile-479 .pm-section-right-panel").show();
    jQuery(".pg-mobile-479 .pm-section-left-panel").hide();
    jQuery(".pg-mobile-479 .pg-left-panel-icon").show();
}

function show_pg_section_left_panel()
{
    jQuery(".pg-mobile-479 .pm-section-right-panel").hide();
    jQuery(".pg-mobile-479 .pm-section-left-panel").show();
    jQuery(".pg-mobile-479 .pg-left-panel-icon").hide();
}

function pg_remove_user_group(uid,gid)
{
    var boxid = '#pg-user-group-box-'+gid;
    var data = {action:'pm_remove_user_group',uid:uid,gid:gid,'nonce': pm_ajax_object.nonce};
    jQuery('#pg_edit_group_html_container').html('<div class="pg-edit-group-popup-loader"><div class="pm-loader"></div></div>');
     var pmDomColor = jQuery(".pmagic").find("a").css('color');
        jQuery(".pm-loader").css('border-top-color', pmDomColor);
    jQuery.post(pm_ajax_object.ajax_url, data, function (resp) 
    {
        //window.location.reload(true);
       if(resp=='success')
       {
            
            //jQuery(boxid).remove();
            //jQuery('.pm-popup-mask, #pm-edit-group-popup, .pg-blog-dialog-mask').hide();
            window.location.reload(true);

       }
    });

}

function pg_open_group_tab()
{
  var child =  jQuery('.pm-profile-tab ul li.pg-group-tab');
var parent = jQuery('ul.pm-profile-tab-wrap');

     var i = 0;
      var tabindex=0;
       jQuery('ul.pm-profile-tab-wrap').children('li').each(function () {
           jQuery(this).find('a').removeClass("active");
        var obj = jQuery(this).find('a[href="#pg-groups"]'); // "this" is the current element in the loop
     if(obj.length)
        {
          jQuery('.pg-profile-tab-content').css("display","none");
          obj.addClass("active");
          tabindex = i;
       }
      
    i++;
  
});
 
 jQuery('#pg-groups').css("display","block");
}

function pg_init_unread_message_toast() {
    var toast = document.getElementById('pg-unread-toast');
    if (!toast || toast.getAttribute('data-pg-initialized') === '1') {
        return;
    }

    toast.setAttribute('data-pg-initialized', '1');

    var target = toast.getAttribute('data-target');
    var baseTarget = toast.getAttribute('data-base-target') || '';
    var latest = parseInt(toast.getAttribute('data-latest'), 10) || 0;
    var latestRid = parseInt(toast.getAttribute('data-latest-rid'), 10) || 0;
    var latestTid = parseInt(toast.getAttribute('data-latest-tid'), 10) || 0;
    var dismissed = parseInt(toast.getAttribute('data-dismissed'), 10) || 0;
    var count = parseInt(toast.getAttribute('data-count'), 10) || 0;
    var show = parseInt(toast.getAttribute('data-show'), 10) || 0;
    var text = toast.querySelector('.pg-unread-toast__text');
    var openBtn = toast.querySelector('.pg-unread-toast__action');
    var closeBtn = toast.querySelector('.pg-unread-toast__close');
    var singleLabel = toast.getAttribute('data-single-label') || '';
    var multiLabel = toast.getAttribute('data-multi-label') || '';
    var summaryTimer = null;
    var summaryInFlight = false;
    var summaryNoChangeStreak = 0;
    var summaryErrorStreak = 0;
    var summaryLastActivityAt = Date.now();
    var dismissStorageKey = 'pgUnreadToastDismissedAt';
    var lastAutoOpenKey = '';

    var buildMessageTarget = function (ridValue) {
        if (!baseTarget) {
            return target;
        }

        var url = baseTarget;
        if (ridValue > 0) {
            url += (url.indexOf('?') === -1 ? '?' : '&') + 'rid=' + encodeURIComponent(ridValue);
        }

        return url + '#pg-messages';
    };

    var isMessagesTabActive = function () {
        var messagesTab = document.getElementById('pg-messages');
        if (!messagesTab) {
            return false;
        }

        if (messagesTab.offsetParent !== null) {
            return true;
        }

        return (window.location.hash || '').toLowerCase() === '#pg-messages';
    };

    var getStoredDismissed = function () {
        var storedValue;

        try {
            storedValue = window.localStorage ? parseInt(window.localStorage.getItem(dismissStorageKey), 10) || 0 : 0;
        } catch (e) {
            storedValue = 0;
        }

        return storedValue;
    };

    var storeDismissed = function (latestValue) {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(dismissStorageKey, String(latestValue || 0));
            }
        } catch (e) {
        }
    };

    var clearStoredDismissed = function () {
        try {
            if (window.localStorage) {
                window.localStorage.removeItem(dismissStorageKey);
            }
        } catch (e) {
        }
    };

    dismissed = Math.max(dismissed, getStoredDismissed());

    var hideToast = function () {
        toast.classList.remove('pg-unread-toast--show');
    };

    var playUnreadSound = function () {
        var tone = document.getElementById('msg_tone');
        var now = Date.now();

        if (!tone) {
            return;
        }

        if (window.pgUnreadSoundLastPlayedAt && (now - window.pgUnreadSoundLastPlayedAt) < 1200) {
            return;
        }

        window.pgUnreadSoundLastPlayedAt = now;

        try {
            if (typeof tone.currentTime === 'number') {
                tone.currentTime = 0;
            }
            if (typeof tone.play === 'function') {
                var promise = tone.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(function () {});
                }
            }
        } catch (e) {
        }
    };

    var acknowledgeLatestUnread = function () {
        if (!latest) {
            return;
        }

        dismissed = Math.max(dismissed, latest);
        storeDismissed(latest);
        persistToastDismissal(latest);
        hideToast();
    };
    var buildLabel = function (countValue) {
        if (countValue === 1) {
            return singleLabel;
        }

        return multiLabel.replace('{{count}}', countValue);
    };

    var renderToast = function (countValue, latestValue, dismissedValue) {
        if (!countValue || isMessagesTabActive() || (latestValue > 0 && dismissedValue >= latestValue)) {
            if (!countValue) {
                clearStoredDismissed();
            }
            hideToast();
            return;
        }

        if (text) {
            text.textContent = buildLabel(countValue);
        }

        toast.classList.add('pg-unread-toast--show');
    };

    var markSummaryActive = function () {
        summaryLastActivityAt = Date.now();
        summaryNoChangeStreak = 0;
    };

    var parseSummaryPayload = function (response) {
        if (!response) {
            return null;
        }

        if (typeof response.success !== 'undefined') {
            if (!response.success || !response.data) {
                return null;
            }

            return response.data;
        }

        return response;
    };

    var computeSummaryInterval = function (changed) {
        var isVisible = document.visibilityState === 'visible';
        var idleMs = Date.now() - summaryLastActivityAt;
        var interval = 9000;

        if (!isVisible) {
            interval = 25000;
        } else if (isMessagesTabActive()) {
            interval = 2500;
        } else if (idleMs > 30000) {
            interval = 12000;
        }

        if (!changed) {
            interval += Math.min(summaryNoChangeStreak * 2000, 12000);
        }

        if (summaryErrorStreak > 0) {
            interval += Math.min(summaryErrorStreak * 4000, 12000);
        }

        interval = Math.max(2000, Math.min(interval, 30000));

        var jitter = Math.floor(interval * ((Math.random() * 0.2) - 0.1));
        return Math.max(1800, interval + jitter);
    };

    var scheduleSummary = function (delay) {
        if (summaryTimer) {
            clearTimeout(summaryTimer);
        }

        summaryTimer = setTimeout(runSummaryPoll, delay);
    };

    var fetchSummaryAjax = function () {
        if (!window.pm_ajax_object || !pm_ajax_object.ajax_url || !pm_ajax_object.nonce) {
            return Promise.reject(new Error('ajax_not_available'));
        }

        var data = new FormData();
        data.append('action', 'pm_unread_message_summary');
        data.append('nonce', pm_ajax_object.nonce);

        return fetch(pm_ajax_object.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json();
        });
    };

    var persistToastDismissal = function (latestValue) {
        if (!latestValue || !window.pm_ajax_object || !pm_ajax_object.ajax_url || !pm_ajax_object.nonce) {
            return;
        }

        var formData = new FormData();
        formData.append('action', 'pm_dismiss_unread_message_toast');
        formData.append('nonce', pm_ajax_object.nonce);
        formData.append('latest_ts', latestValue);

        if (navigator.sendBeacon && typeof URLSearchParams !== 'undefined') {
            var beaconData = new URLSearchParams();
            beaconData.append('action', 'pm_dismiss_unread_message_toast');
            beaconData.append('nonce', pm_ajax_object.nonce);
            beaconData.append('latest_ts', latestValue);
            navigator.sendBeacon(pm_ajax_object.ajax_url, beaconData);
            return;
        }

        fetch(pm_ajax_object.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
            keepalive: true
        });
    };

    var runSummaryPoll = function () {
        if (summaryInFlight) {
            return;
        }

        summaryInFlight = true;

        var request = fetchSummaryAjax();

        request.then(function (response) {
            var payload = parseSummaryPayload(response);

            if (!payload) {
                summaryErrorStreak++;
                return;
            }

            var latestValue = parseInt(payload.latest_ts, 10) || 0;
            var latestRidValue = parseInt(payload.latest_rid, 10) || 0;
            var latestTidValue = parseInt(payload.latest_tid, 10) || 0;
            var dismissedValue = parseInt(payload.dismissed, 10) || 0;
            var countValue = parseInt(payload.count, 10) || 0;
            var previousCountValue = count;
            var changed = (latestValue !== latest || latestRidValue !== latestRid || latestTidValue !== latestTid || dismissedValue !== dismissed || countValue !== count);

            latest = latestValue;
            latestRid = latestRidValue;
            latestTid = latestTidValue;
            dismissed = dismissedValue;
            count = countValue;
            target = buildMessageTarget(latestRid);
            toast.setAttribute('data-target', target);
            toast.setAttribute('data-latest-rid', String(latestRid));
            toast.setAttribute('data-latest-tid', String(latestTid));

            if (!isMessagesTabActive() && countValue > previousCountValue) {
                playUnreadSound();
            }

            renderToast(countValue, latestValue, dismissedValue);

            if (changed) {
                markSummaryActive();
            } else {
                summaryNoChangeStreak++;
            }

            summaryErrorStreak = 0;
        }).catch(function () {
            summaryErrorStreak++;
        }).then(function () {
            summaryInFlight = false;
            scheduleSummary(computeSummaryInterval(false));
        });
    };

    if (show) {
        renderToast(count, latest, dismissed);
    }

    if (openBtn && target) {
        openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            acknowledgeLatestUnread();
            window.location.href = target;
        });
    }

    if (closeBtn) {
        closeBtn.textContent = String.fromCharCode(215);
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            acknowledgeLatestUnread();
        });
    }

    var activateLatestUnreadThread = function () {
        if (!latestRid || typeof window.pg_activate_new_thread !== 'function') {
            return;
        }

        var autoOpenKey = String(latest) + ':' + String(latestRid) + ':' + String(latestTid);
        if (lastAutoOpenKey === autoOpenKey) {
            return;
        }

        lastAutoOpenKey = autoOpenKey;
        setTimeout(function () {
            window.pg_activate_new_thread(latestRid);
            scheduleSummary(1200);
        }, 250);
    };

    var syncUnreadStateOnMessagesVisit = function () {
        if (!count || !latest) {
            return;
        }

        acknowledgeLatestUnread();
        activateLatestUnreadThread();
    };

    document.querySelectorAll('a[href="#pg-messages"]').forEach(function (link) {
        link.addEventListener('click', function () {
            markSummaryActive();
            syncUnreadStateOnMessagesVisit();
        });
    });
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            markSummaryActive();
            scheduleSummary(1500);
        }
    });

    window.addEventListener('focus', function () {
        markSummaryActive();
        scheduleSummary(1500);
    });

    if ((window.location.hash || '').toLowerCase() === '#pg-messages') {
        syncUnreadStateOnMessagesVisit();
    }

    window.pgUnreadToastSync = {
        acknowledgeCurrent: acknowledgeLatestUnread,
        refreshNow: function () {
            markSummaryActive();
            scheduleSummary(200);
        },
        hideWhileMessagesVisible: function () {
            if (isMessagesTabActive()) {
                hideToast();
            }
        }
    };
    scheduleSummary(6000);
}

jQuery(function() {
    jQuery('.pm_linked_in_url, .pm_twitter_url, .pm_youtube_url, .pm_soundcloud_url, .pm_mixcloud_url, .pm_instagram_url, .pm_google_url').each(function(index, element) {
        var $wrapper = jQuery(element);
        var $input = $wrapper.find('input');

        $input.on('blur', function() {
            var val = jQuery(this).val().trim();
            // Simple URL check: contains at least one dot and no spaces
            var urlPattern = /^[^\s]+\.[^\s]+$/; // rough check for URL
            if (val && urlPattern.test(val)) {
                // Prepend https:// if missing
                if (!/^https?:\/\//i.test(val)) {
                    jQuery(this).val('https://' + val);
                }
            }
        });
    });

    pg_init_unread_message_toast();
});
