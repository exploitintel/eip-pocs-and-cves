jQuery( document ).ready( function() {
	jQuery( '.reset_button' ).bind( 'click', function( event ) {
		event.preventDefault();
		var parent_row = jQuery( jQuery( this ).parent() ).parent().attr( 'id' );
		jQuery.ajax({
			url:ajax_object.ajax_url,
			method:'get',
			dataType:'json',
			data:{
				action:'reset_counter_062613',
				row:parent_row
			},
			success:function( data ){
				jQuery( '#' + parent_row ).empty();
				jQuery( '#' + parent_row ).html( data.response );
			}
		});
	} );
} );