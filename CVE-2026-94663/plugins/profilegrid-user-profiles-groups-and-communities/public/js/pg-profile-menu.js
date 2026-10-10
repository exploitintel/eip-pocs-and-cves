(function( $ ) {
$.fn.PGresponsiveMenu = function () {
	$(this).each(function () {
		$(this).addClass("pg-horizontal-responsive-menu");
		alignMenu(this);
		var robj = this;
		$(window).on( 'resize', function () {
			$(robj).append($($($(robj).children("li.hideshow")).children("ul")).html());
			$(robj).children("li.hideshow").remove();
			alignMenu(robj);
		});

		function alignMenu(obj) {
		  
		    
			var w = 0;
			var mw = $(obj).width() + 210;
			var i = -1;
			var menuhtml = '';
			$.each($(obj).children(), function () {
				i++;
				w += $(this).outerWidth(true);
				if (mw < w) {
					menuhtml += $('<div>').append($(this).clone()).html();
					$(this).remove();
				}
			});
                        if(menuhtml!='')
                        {
                            $(obj).append('<li  style="position:relative;" href="#" class="hideshow">' + '<a href="javascript:void(0)"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg> ' + '</a><ul>' + menuhtml + '</ul></li>');
                            $(obj).children("li.hideshow ul").css("top", $(obj).children("li.hideshow").outerHeight(true) + "px");
                        }
			$(obj).children("li.hideshow").on( 'click', function () {
				$(this).children("ul").toggle();
			});  
			
                        $('li.hideshow ul li.pm-profile-tab a').on( 'click', function(){
                            var t = $(this).attr('href');
                            $('li.pm-profile-tab a').removeClass('active');         
                            $(this).addClass('active');
                            $('.pg-profile-tab-content').hide();
                            $(t).find('.pm-section-content:first').show();
                            $('li.hideshow ul').hide();
                            $(t).fadeIn('slow');
                            return false;
                        });
		}
	});
        
        setTimeout(function(){
        $(".pg-horizontal-responsive-menu li.hideshow ul:empty").closest('.pg-horizontal-responsive-menu li.hideshow').hide();
            var pmDomColor = $(".pmagic").find("a").css('color');
            $(".pg-horizontal-responsive-menu li.hideshow").css('fill', pmDomColor);
            
            },1000);
}

})(jQuery);
