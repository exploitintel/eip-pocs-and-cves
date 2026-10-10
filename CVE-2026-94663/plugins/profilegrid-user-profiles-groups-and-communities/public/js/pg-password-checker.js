function pg_check_password_strenth() 
{
    var strength = checkStrength(jQuery('#pm_new_pass').val());
    jQuery('#pg_password_strenth_text').html(strength);
    if(strength=='Medium' || strength=='Strong')
    {
        jQuery('#pm_member_reset_password_link').attr('onclick','pm_reset_user_password()');
    }
}

function checkStrength(password) 
{
    jQuery('#pg_password_meter_outer').show();
    var strength = 0
//    if (password.length < 6) {
//        jQuery('#pg_password_meter_inner').removeClass()
//        jQuery('#pg_password_meter_inner').addClass('pg-pass-short')
//        return 'Too short'
//    }
    if (password.length > 7) strength += 1
    // If password contains both lower and uppercase characters, increase strength value.
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
    // If it has numbers and characters, increase strength value.
    if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
    // If it has one special character, increase strength value.
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
    // If it has two special characters, increase strength value.
    if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
    // Calculated strength value, we can return messages
    // If value is less than 2
    if (strength < 2) 
    {
        jQuery('#pg_password_meter_inner').removeClass()
        jQuery('#pg_password_meter_inner').addClass('pg-pass-weak')
        jQuery('#pm_member_reset_password_link').removeClass();
        jQuery('#pm_member_reset_password_link').addClass('pg-setting-disabled');
        return 'Weak';
    } 
    else if (strength == 2) 
    {
        jQuery('#pg_password_meter_inner').removeClass()
        jQuery('#pg_password_meter_inner').addClass('pg-pass-good')
        jQuery('#pm_member_reset_password_link').removeClass();
        return 'Medium';
    } 
    else 
    {
        jQuery('#pg_password_meter_inner').removeClass()
        jQuery('#pg_password_meter_inner').addClass('pg-pass-strong')
        jQuery('#pm_member_reset_password_link').removeClass();
        return 'Strong';
    }
}