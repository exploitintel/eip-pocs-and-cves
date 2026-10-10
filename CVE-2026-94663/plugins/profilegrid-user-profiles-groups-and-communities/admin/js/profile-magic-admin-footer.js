/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery( '.soundcloud_play_button_color' ).wpColorPicker();
jQuery(
    function() {
		jQuery( ".pg_profile_tab" ).on( 'click',
            function() {
                jQuery( this ).children( ".pm-slab-buttons" ).children( 'span' ).toggleClass( "dashicons-arrow-up" );
                jQuery( this ).next( ".pg_profile_tab-setting" ).slideToggle();
            }
		);
	}
);

jQuery(
    function(){
        jQuery( '.pg-upgrade-banner' ).on( 'mouseenter',
            function() {
                jQuery( '.pg-upgrade-banner-box' ).addClass( 'pg-banner-hop' );
                jQuery( this ).siblings().addClass( 'pg-blur' );
            }
        );
        jQuery( '.pg-upgrade-banner' ).on( 'mouseleave',
            function() {
                jQuery( '.pg-upgrade-banner-box' ).removeClass( 'pg-banner-hop' );
                jQuery( this ).siblings().removeClass( 'pg-blur' );
            }
        );
    }
);

jQuery(
    function(){
		jQuery( "#selectall" ).on( 'click',
            function(){
                if (this.checked) {
                    jQuery( '.pm_selectable input[type="checkbox"]' ).each(
                        function(){
                            jQuery( '.pm_selectable input[type="checkbox"]' ).prop( 'checked', true );
                        }
                    );
                    jQuery( '.pm_action_button input' ).removeClass( 'pm_disabled' );
                    jQuery( '.pm_action_button input' ).removeAttr( 'disabled' );
                } else {
                    jQuery( '.pm_selectable input[type="checkbox"]' ).each(
                        function(){
                            jQuery( '.pm_selectable input[type="checkbox"]' ).prop( 'checked', false );
                        }
                    );
                    jQuery( '.pm_action_button input' ).addClass( 'pm_disabled' );
                    jQuery( '.pm_action_button input' ).attr( 'disabled','disabled' );
                }
            }
		);
	}
);


jQuery( '.pg-extension-modal' ).on( 'click',
    function(){
        jQuery( '.pg-extension-wrap' ).hide();
        jQuery( '#' + jQuery( this ).attr( 'data-popup' ) ).show();
    }
);


function CallExtensionModal(ele) {
	jQuery( "#pg-setting-popup" ).toggle();
	jQuery( '.pg-setting-modal-wrap' ).removeClass( 'pg-setting-popup-out' );
	jQuery( '.pg-setting-modal-wrap' ).addClass( 'pg-setting-popup-in' );
	jQuery( '.pg-setting-modal-overlay' ).removeClass( 'pg-setting-popup-overlay-fade-out' );
	jQuery( '.pg-setting-modal-overlay' ).addClass( 'pg-setting-popup-overlay-fade-in' );

}




    jQuery(
        function () {
            jQuery( '.pg-setting-modal-close, .pg-setting-modal-overlay' ).on( 'click',
                function () {
                    setTimeout(
                        function () {
                            //jQuery(this).parents('.rm-modal-view').hide();
                            jQuery( '.pg-setting-modal-view' ).hide();
                        },
                        400
                    );
                }
            );
            jQuery( '.pg-setting-modal-close, .pg-setting-modal-overlay' ).on(
                'click',
                function () {
                    jQuery( '.pg-setting-modal-wrap' ).removeClass( 'pg-setting-popup-in' );
                    jQuery( '.pg-setting-modal-wrap' ).addClass( 'pg-setting-popup-out' );

                    jQuery( '.pg-setting-modal-overlay' ).removeClass( 'pg-setting-popup-overlay-fade-in' );
                    jQuery( '.pg-setting-modal-overlay' ).addClass( 'pg-setting-popup-overlay-fade-out' );
                }
            );

        }
    );



    jQuery(
		function($) {
			var a = jQuery( '.pm-group-fields-modal .pm-field-selection .pm-popup-field-box' );
			for ( var i = 0; i < a.length; i+=2 ) {
				 a.slice( i, i+2 ).wrapAll( '<div class="pm-popup-field-box-wrap"></div>' );
			}
		}
    );



	function  CallFieldSelectionModal(ele) {
		jQuery( "#pm-group-fields-popup" ).toggle();
		jQuery( '.pm-group-fields-popup-wrap' ).removeClass( 'pm-group-fields-popup-out' );
		jQuery( '.pm-group-fields-popup-wrap' ).addClass( 'pm-group-fields-popup-in' );
		jQuery( '.pm-group-fields-popup-overlay' ).removeClass( 'pm-group-fields-popup-overlay-fade-out' );
		jQuery( '.pm-group-fields-popup-overlay' ).addClass( 'pm-group-fields-popup-overlay-fade-in' );

	}




    jQuery(
        function () {
            jQuery( '.pm-group-fields-popup-close, .pm-group-fields-popup-overlay' ).on( 'click',
                function () {
                    setTimeout(
                        function () {
                            //jQuery(this).parents('.rm-modal-view').hide();
                            jQuery( '#pm-group-fields-popup' ).hide();
                        },
                        400
                    );
                }
            );
            jQuery( '.pm-group-fields-popup-close, .pm-group-fields-popup-overlay' ).on(
                'click',
                function () {
                    jQuery( '.pm-group-fields-popup-wrap' ).removeClass( 'pm-group-fields-popup-in' );
                    jQuery( '.pm-group-fields-popup-wrap' ).addClass( 'pm-group-fields-popup-out' );

                    jQuery( '.pm-group-fields-popup-overlay' ).removeClass( 'pm-group-fields-popup-overlay-fade-in' );
                    jQuery( '.pm-group-fields-popup-overlay' ).addClass( 'pm-group-fields-popup-overlay-fade-out' );
                }
            );

        }
    );



	   (function($){

        $.fn.extend(
            {

				addTemporaryClass: function(className, duration) {
					var elements = this;
					setTimeout(
                        function() {
                            elements.removeClass( className );
                        },
                        duration
					);

					return this.each(
                        function() {
                            $( this ).addClass( className );
                        }
					);
				}
			}
        );

        $(
            function(){

                $( ".pg-pr-cards-wrap .pg-pr-card.pg-pr-card-2" ).addTemporaryClass( "myClass", 1000 );
                $( ".pg-pr-cards-wrap .pg-pr-card.pg-pr-card-3" ).addTemporaryClass( "myClass", 1500 );

            }
        );

	   })( jQuery );

	   (function($){

		$(
            function() {
                $( '#pg-promo-tabs a:first' ).addClass( 'nav-tab-active' );
                $( '#pg-promo-tabs a:not(:first)' ).addClass( 'nav-tab-inactive' );
                $( '.pg-promo-nav-container' ).hide();
                $( '.pg-promo-nav-container:first' ).show();

                $( '#pg-promo-tabs a' ).on( 'click',
                    function(){
                        var t = $( this ).attr( 'id' );
                        if ($( this ).hasClass( 'nav-tab-inactive' )) {
                            $( '#pg-promo-tabs a' ).addClass( 'nav-tab-inactive' );
                            $( this ).removeClass( 'nav-tab-inactive' );
                            $( this ).addClass( 'nav-tab-active' );

                            $( '.pg-promo-nav-container' ).hide();
                            $( '#'+ t + 'C' ).fadeIn( 'slow' );
                        }
                    }
                );

            }
		);

	   })( jQuery );


	   (function($){

		$(
            function(){
                $( "#pg-group-promo-toggle" ).on( 'click',
                    function(){
                        $( ".pg-group-promo-content" ).toggle();
                    }
                );
            }
		);

		$( "#pg-group-promo-toggle" ).on(
            "click",
            function() {
                var el = $( this );
                if (el.text() == el.data( "text-swap" )) {
                         el.text( el.data( "text-original" ) );
                } else {
                     el.data( "text-original", el.text() );
                     el.text( el.data( "text-swap" ) );
                }
            }
		);

	   })( jQuery );

    jQuery(
        function($) {
               $( "#tabs" ).tabs();  }
    );

	   jQuery( '.pmagic .pm-user-info .pm-profile-image img.user-profile-image' ).closest( '.pm-profile-image' ).addClass( 'pm-profile-defualt' );

	   filterSelection( "all" )
	function filterSelection(c) {
		var x, i;
		x = document.getElementsByClassName( "pgfilterDiv" );
		if (c == "all") {
			c = "";
		}
		// Add the "show" class (display:block) to the filtered elements, and remove the "show" class from the elements that are not selected
		for (i = 0; i < x.length; i++) {
			w3RemoveClass( x[i], "pgshow" );
			if (x[i].className.indexOf( c ) > -1) {
				w3AddClass( x[i], "pgshow" );
			}
		}
	}

	   // Show filtered elements
	function w3AddClass(element, name) {
		var i, arr1, arr2;
		arr1 = element.className.split( " " );
		arr2 = name.split( " " );
		for (i = 0; i < arr2.length; i++) {
			if (arr1.indexOf( arr2[i] ) == -1) {
					  element.className += " " + arr2[i];
			}
		}
	}

	   // Hide elements that are not selected
	function w3RemoveClass(element, name) {
		var i, arr1, arr2;
		arr1 = element.className.split( " " );
		arr2 = name.split( " " );
		for (i = 0; i < arr2.length; i++) {
			while (arr1.indexOf( arr2[i] ) > -1) {
					  arr1.splice( arr1.indexOf( arr2[i] ), 1 );
			}
		}
		element.className = arr1.join( " " );
	}

	   // Add active class to the current control button (highlight it)
	   const el2 = document.querySelector( '#pgmyBtnContainer' );
	if (el2 !== null) {
		var btnContainer = document.getElementById( "pgmyBtnContainer" );
		var btns         = btnContainer.getElementsByClassName( "pgbtn" );
		for (var i = 0; i < btns.length; i++) {
			btns[i].addEventListener(
                "click",
                function() {
                    var current          = document.getElementsByClassName( "pgactive" );
                    current[0].className = current[0].className.replace( " pgactive", "" );
                    this.className      += " pgactive";
                }
			);
		}
	}



jQuery(function(e){
   
        let blockLoaded = false;
        let blockLoadedInterval = setInterval(function(){
            if(jQuery('.pg-group-menu-widgets .pg-group-icon-toggle').length){
                blockLoaded = true;
                jQuery('.pg-group-menu-widgets .pm_sortable_groups').sortable({
                        axis: 'y',
                        opacity: 0.7,
                        handle: '.pg-widget-drag-handle',
                        update: function (event, ui) {
                                        var group_sortable = jQuery(this).sortable('toArray').toString();
                                        var data = {
                                        'action': 'pm_set_group_order',
                                        'list_order': group_sortable,
                                        'nonce': pm_ajax_object.nonce
                                };
                                jQuery.post(pm_ajax_object.ajax_url, data, function(response) {});
                        }
                });
                var list = new Array();
    jQuery(".pg-group-menu-widgets .pg-group-icon-toggle").on( 'change', function(){
        if(this.checked)
        {
            var icon = 'yes';
        }
        else
        {
            var icon = 'no';
        }
        var data = {
                        'action': 'pm_set_group_items',
                        'icon': icon,
                        'nonce': pm_ajax_object.nonce
                   };
        jQuery.post(pm_ajax_object.ajax_url, data, function(response) {});
    });
    jQuery(".pg-group-menu-widgets ul.pm_sortable_groups li .pm-group-buttons input[type=checkbox]").each(function(){
        console.log(list);
        if (this.checked) {
                var id = jQuery(this).val();
                list.push(id);
        }else{
            var id = jQuery(this).val();
            if(list.indexOf(id)){
                list.pop(id);
            }
        }
        jQuery(this).on( 'change', function() {
            if (this.checked) {
                var id = jQuery(this).val();
                list.push(id);
                var items = list.toString();
                var data = {
                                  'action': 'pm_set_group_items',
                                  'list_items': items,
                                  'nonce': pm_ajax_object.nonce
                          };
                jQuery.post(pm_ajax_object.ajax_url, data, function(response) {});
            } 
            else{
                var id = jQuery(this).val();
                var i = list.indexOf(id);
                list.splice(i, 1);
                var items = list.toString();
               var data = {
                              'action': 'pm_set_group_items',
                              'list_items': items,
                              'nonce': pm_ajax_object.nonce
                      };
                jQuery.post(pm_ajax_object.ajax_url, data, function(response) {});
            }
        }); 
    });
            }
            if(blockLoaded){
                clearInterval(blockLoadedInterval);
            }
        }, 500); 
});
