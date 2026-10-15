jQuery( document ).ready( function(){
	jQuery( '#up-count' ).bind( 'click', function(){
		jQuery.ajax({
			url:ajax_object.ajax_url,
			method:'get',
			dataType:'json',
			data:{
				action:'count_vote_062613',
				id:ajax_object.id,
				type:'upvote'
			},
			success:function( data ){
				if ( 'error' == data.response ){
					jQuery( '#pvs-message' ).html( 'You Have Already Voted For This Post Once' );
				}else{
					jQuery( '#pvs-up-counter' ).html( data.response );
				}
			}
		});
	});
	jQuery( '#down-count' ).bind( 'click', function(){
		jQuery.ajax({
			url:ajax_object.ajax_url,
			method:'get',
			dataType:'json',
			data:{
				action:'count_vote_062613',
				id:ajax_object.id,
				type:'downvote'
			},
			success:function( data ){
				if ( 'error' == data.response ){
					jQuery( '#pvs-message' ).html( 'You Have Already Voted For This Post Once' );
				}else{
					jQuery( '#pvs-down-counter' ).html( data.response );
				}
			}
		});
	});
});