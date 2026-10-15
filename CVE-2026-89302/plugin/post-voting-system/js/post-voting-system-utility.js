jQuery( document ).ready( function() {
	jQuery( '#all_reset_selector' ).change( function() {
		jQuery( ".reset_selector" ).trigger( "click" );
	} );
	jQuery( '#group_op_selector' ).change( function( event ) {
		if ( jQuery( this ).val() == 0 ){
			event.preventDefault();
		}else{
			jQuery( '#reset_vote_counter_form' ).submit();
		}
	} );
} );