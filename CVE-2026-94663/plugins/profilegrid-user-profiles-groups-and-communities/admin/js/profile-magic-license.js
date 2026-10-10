jQuery( function( $ ) {
    $(".pg-license-block").on( 'keyup', function(e) {
        let prefix = $(this).data('key');
        let field = $('#' + prefix + '_license_key' );
        let license_key_length = field.val();
        // Strip whitespace so length checks aren't blocked by spaces.
        let sanitized_key = ( license_key_length || '' ).replace(/\s+/g, '');
        if ( sanitized_key !== license_key_length ) {
            field.val( sanitized_key );
            license_key_length = sanitized_key;
        }
        // let child_length = $('.'+ prefix +  ' .' + prefix + '-license-status-block').children.length;
        if( license_key_length.length === 32 && prefix != 'undefined' && prefix != '' ){
            $('#' + prefix + '_license_activate' ).show();
        }
    });

    $(".pg-license-block").on( 'keydown', function(e) {
        let prefix = $(this).data('key');
        if( prefix != 'undefined' && prefix != '' ){
            $('#' + prefix + '_license_activate' ).hide();
        }
    });

    $( document ).on( 'click', '.pg_license_activate', function(e) {
        e.preventDefault();
        let prefix = $(this).data('prefix');
        let key = $(this).data('key');
        let license_key = ($('#'+key + '_license_key').val() || '').replace(/\s+/g, '');
        $('#'+key + '_license_key').val( license_key );
        let pg_license_activate = $('#' + key + '_license_activate').val();
        // $( '.'+ prefix +  ' .' + prefix + '-license-status-block' ).html( '' );
        $( '.'+ key +  ' .license-expire-date' ).html( '' );
        $( '.'+ key +  ' .' + key + '-license-status-block .pg_license_activate' ).addClass( 'disabled' );
    
        let data = { 
            action: 'pg_activate_license', 
            nonce: pg_admin_license_settings.nonce,
            pg_license_activate : pg_license_activate,
            pg_license : license_key,
            pg_item_id : prefix, 
            pg_item_key: key
        };
        
        $.ajax({
            type: 'POST', 
            url :  pg_admin_license_settings.ajax_url,
            data: data,
            success: function( response ) {
                
                $( '.'+ key +  ' .' + key + '-license-status-block .pg_license_activate' ).removeClass( 'disabled' );
                if(response.success===true && response.data.license_data.success === true )
                {
                    show_pg_toast( 'success', response.data.message );
                    // update license activate/deactivate button
                    console.log('.'+ key +  ' .' + key + '-license-status-block');
                    if( response.data.license_status_block != '' && response.data.license_status_block != 'undefined' ){
                        //console.log('.'+ key +  ' .' + key + '-license-status-block');
                        $('.'+ key +  ' .' + key + '-license-status-block').html(response.data.license_status_block);
                    }
                   
                    // update license expiry date
                    $('.' + key +  ' .license-expire-date').html(response.data.expire_date);
                    
                }else{
                    show_pg_toast( 'error', response.data.message );
                }
            
            }
        });
    });

    $( document ).on( 'click', '.pg_license_deactivate', function(e) {
        e.preventDefault();
        let prefix = $(this).data('prefix');
        let key = $(this).data('key');
        let license_key = $('#'+key + '_license_key').val();
        let pg_license_deactivate = $('#'+ key + '_license_deactivate').val();
        
        // $( '.'+ prefix +  ' .' + prefix + '-license-status-block' ).html( '' );
        $( '.'+ key +  ' .license-expire-date' ).html( '' );
        $( '.'+ key +  ' .' + key + '-license-status-block .pg_license_deactivate' ).addClass( 'disabled' );
        let data = { 
            action: 'pg_deactivate_license', 
            nonce: pg_admin_license_settings.nonce,
            pg_license_deactivate : pg_license_deactivate, 
            pg_license : license_key,
            pg_item_id : prefix, 
            pg_item_key: key
        };
        $.ajax({
            type: 'POST', 
            url :  pg_admin_license_settings.ajax_url,
            data: data,
            success: function( response ) {
                $( '.'+ key +  ' .' + key + '-license-status-block .pg_license_deactivate' ).removeClass( 'disabled' );
                if( response.success===true && response.data.license_data.success === true )
                {
                    show_pg_toast( 'success', response.data.message );
                    // update license activate/deactivate button
                    if( response.data.license_status_block != '' && response.data.license_status_block != 'undefined' ){
                        $('.'+ key +  ' .' + key + '-license-status-block').html(response.data.license_status_block);
                    }
                    // update license expiry date
                    // $('.'+prefix+ ' .license-expire-date').html(response.data.expire_date);
                    $('.'+key+ ' .license-expire-date').html('');
                }else{
                    show_pg_toast( 'error', response.data.message );
                    if( response.data.license_status_block != '' && response.data.license_status_block != 'undefined' ){
                        $('.'+ key +  ' .' + key + '-license-status-block').html(response.data.license_status_block);
                    }
                    // if( response.data.expire_date != '' && response.data.expire_date != 'undefined' ){
                    //     // update license expiry date
                    //     $('.'+prefix+ ' .license-expire-date').html(response.data.expire_date);
                    // }
                    $('.'+key+ ' .license-expire-date').html(''); 
                }
            }
        });
    });


});

// show toast message
function show_pg_toast( type, message, heading = true ) {
    jQuery( "#pg-extension-license-status" ).addClass( 'pg-modal-show' );
    jQuery('#pg-extension-license-message').html(message);
      console.log(type);
    if(type === "error"){
        console.log(message);
        jQuery( "#pg-extension-license-status" ).addClass( 'pg-status-failed-model' );
        jQuery( "#pg-extension-license-status" ).removeClass( 'pg-status-succuess-model' );
    }else{
        jQuery( "#pg-extension-license-status" ).removeClass( 'pg-status-failed-model' );
        jQuery( "#pg-extension-license-status" ).addClass( 'pg-status-succuess-model' );
    }
    
    setTimeout(
    function() 
    {
        pg_close_toast();
      //do something special
    }, 5000);
}

function pg_close_toast()
{
    jQuery( "#pg-extension-license-status" ).removeClass( 'pg-modal-show' );
    jQuery('#pg-extension-license-message').html('');
}

function pg_on_change_bundle(value)
{
    jQuery('#pg_premium_license_key').attr('data-prefix',value);
    jQuery('.pg_premium-license-status-block button').attr('data-prefix',value);
}



  jQuery(function(){
    jQuery('.pg-tooltips').append("<span></span>");
    jQuery('.pg-tooltips:not([tooltip-position])').attr('tooltip-position', 'bottom');


    jQuery(".pg-tooltips").on( 'mouseenter', function () {
        jQuery(this).find('span').empty().append(jQuery(this).attr('tooltip'));
    });
  });
