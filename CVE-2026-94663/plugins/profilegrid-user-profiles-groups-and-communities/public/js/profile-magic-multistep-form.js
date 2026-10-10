/* multistep form js */
(function( $ ) {
    'use strict';
   
    function getState(form) {
        return form.data('pgMultipageState') || null;
    }

    function setState(form, state) {
        form.data('pgMultipageState', state);
    }

    $.fn.transitionPage = function(from,to) {
        var state = getState($(this));
        if (!state || !state.settings) {
            return false;
        }

        if (state.settings.transitionFunction) {
            state.settings.transitionFunction(from,to);
        } else {
            $(from).hide();
            $(to).show();
        }
        $(state.id + ' fieldset').removeClass('active');
        $(to).addClass('active');
    }
    
    $.fn.showState = function(page) {
        var state = getState($(this));
        if (!state || !state.settings) {
            return false;
        }

        var settings = state.settings;
        if (settings.stateFunction) {
            return settings.stateFunction(state.id+"_nav .multipage_state",page,settings.pages.length);
        }
        var stateText = '';
        for (var x = 1; x <= settings.pages.length; x++) {
            if(x==page) {
                stateText = stateText + settings.activeDot;
            } else {
                stateText = stateText + settings.inactiveDot;
            }
        }
        $(state.id+"_nav .multipage_state").html(stateText);
    }
    
    $.fn.gotopage = function(page) {
        var state = getState($(this));
        if (!state || !state.settings || !state.settings.pages) {
            return false;
        }
        var settings = state.settings;
        var id = state.id;

        $(id + '_nav .multipage_next').html(pm_error_object.next);

        if (isNaN(page)) {
            var q = page;
            page = 1;
            $(id+' fieldset').each(function(index) {
                if ('#'+$(this).attr('id')==q) {
                    state.curpage = page = index+1;
                }
            });
        }

        var np = null;
        var cp = $(id+' fieldset.active');
        $(id+' fieldset').each(function(index) {
            index++;
            if (index==page) {
                np = this;
            }
        });

        $(this).transitionPage(cp,np);

        $(this).showState(page);

        $(id + '_nav .multipage_next').removeClass('submit');

        var page_title = settings.pages[page-1].title;

        if (settings.stayLinkable) {
            var hashtag = '#' + settings.pages[page-1].id;
            document.location.hash = hashtag;
        }
        
        if (page==1) {
            $(id + '_nav .multipage_back').hide();
            $(id + '_nav .multipage_next').show();
            if(page==settings.pages.length) {
                $(id + '_nav .multipage_next').addClass('submit');
                $(id + '_nav .multipage_next').html(settings.submitLabel);
            } else {
                if (settings.pages[page].title) {
                    $(id + '_nav .multipage_next').html( pm_error_object.next + ': ' + settings.pages[page].title);
                } else {
                    $(id + '_nav .multipage_next').html(pm_error_object.next);
                }
            }
        } else if (page==settings.pages.length) {
            $(id + '_nav .multipage_back').show();
            $(id + '_nav .multipage_next').show();

            if (settings.pages[page-2].title) {
                $(id + '_nav .multipage_back').html(pm_error_object.back + ': ' + settings.pages[page-2].title);
            } else {
                $(id + '_nav .multipage_back').html(pm_error_object.back);
            }

            $(id + '_nav .multipage_next').addClass('submit');
            $(id + '_nav .multipage_next').html(settings.submitLabel);
        } else {
            if (settings.pages[page-2].title) {
                $(id + '_nav .multipage_back').html(pm_error_object.back + ': ' + settings.pages[page-2].title);
            } else {
                $(id + '_nav .multipage_back').html(pm_error_object.back);
            }
            if (settings.pages[page].title) {
                $(id + '_nav .multipage_next').html(pm_error_object.next + ': ' + settings.pages[page].title);
            } else {
                $(id + '_nav .multipage_next').html(pm_error_object.next);
            }

            $(id + '_nav .multipage_back').show();
            $(id + '_nav .multipage_next').show();
        }

        $(id + ' fieldset.active input:first').trigger( 'focus' );
        state.curpage = page;
        setState($(this), state);
        return false;
    }
    
    $.fn.validatePage = function(page) { 
        return true;
    };
    
    $.fn.validateAll = function() { 
        var state = getState($(this));
        if (!state || !state.settings) {
            return false;
        }
        var settings = state.settings;
        
        for (var x = 1; x <= settings.pages.length; x++) {
            if (!$(this).validatePage(x)) {
                $(this).gotopage(x);
                return false;
            }
        }
        return true;
    };
    
    $.fn.gotofirst = function() {
        var state = getState($(this));
        if (!state) { 
            return false; 
        }
        state.curpage = 1;
        setState($(this), state);
        $(this).gotopage(state.curpage);
        return false;
    }
    
    $.fn.gotolast = function() {
        var state = getState($(this));
        if (!state || !state.settings || !state.settings.pages) { 
            return false; 
        }
        state.curpage = state.settings.pages.length;
        setState($(this), state);
        $(this).gotopage(state.curpage);
        return false;
    }

    $.fn.nextpage = function() {
        var state = getState($(this));
        if (!state || !state.settings || !state.settings.pages || !state.id) {
            return false;
        }
        var settings = state.settings;
        var id = state.id;

        // Get the current fieldset using the correct selector
        var curfieldset = $(id + ' fieldset:nth-of-type(' + state.curpage + ')');
        
        // Validate the current page
        if(profile_magic_multistep_form_validation(curfieldset)) {
            // Check if current page is valid
            if ($(this).validatePage(state.curpage)) {  
                state.curpage++;
                
                if (state.curpage > settings.pages.length) {
                    var payment_type = $("input[name='pm_payment_method']:checked").val();
                    
                    if(payment_type == 'stripe') {
                        if (typeof multistep_stripe_form === 'function') {
                            multistep_stripe_form(this);
                        }
                    } else {
                        $(this).trigger( 'submit' );
                    }
                    
                    state.curpage = settings.pages.length;
                    setState($(this), state);
                    return false;
                }
                setState($(this), state);
                $(this).gotopage(state.curpage);
            }
            return false;
        }
        return false;
    }
    
    $.fn.getPages = function() {
        var state = getState($(this));
        if (!state || !state.settings) {
            return [];
        }
        return state.settings.pages;
    };
    
    $.fn.prevpage = function() {
        var state = getState($(this));
        if (!state || !state.settings || !state.settings.pages || !state.id) {
            return false;
        }

        state.curpage--;

        if (state.curpage < 1) {
            state.curpage = 1;
        }
        setState($(this), state);
        $(this).gotopage(state.curpage);
        return false;
    }
    
    $.fn.multipage = function(options) { 
        var settings = $.extend({
            stayLinkable: false,
            submitLabel: pm_error_object.submit,
            hideLegend: false,
            hideSubmit: true,
            generateNavigation: true,
            activeDot: '&nbsp;&#x25CF;',
            inactiveDot: '&nbsp;&middot;'
        }, options);
        
        var id = '#' + $(this).attr('id');
        var form = $(this);
        var state = { 
            id: id, 
            settings: settings, 
            curpage: 1 
        };
        setState(form, state);
        
        form.addClass('multipage');
        

        form.on( 'submit', function(e) {
            if (!$(this).validateAll()) {
                e.preventDefault();
            }
        });
        
        // Hide all the pages 
        $(id +' fieldset').hide();
        
        if (settings.hideSubmit) { 
            $(id+' input[type="submit"]').hide();
        }
        
        if ($(id+' input[type="submit"]').val()!='') { 
            settings.submitLabel = $(id+' input[type="submit"]').val();
        }
        
        settings.pages = [];
        
        $(this).children('fieldset').each(function(index) { 
            var label = $(this).children('legend').html();
            settings.pages[index] = {
                number: index+1,
                title: label,
                id: $(this).attr('id')
            };
        });
        
        if (settings.hideLegend) { 
            // Hide legend tags
            $(id+' fieldset legend').hide();
        }
        
        // Show the first page.
        $(id+' fieldset:first').addClass('active');
        $(id+' fieldset:first').show();
                                    
        if (settings.generateNavigation) { 
            if (settings.navigationFunction) { 
                settings.navigationFunction($(this).getPages());
            } else {
                // Insert navigation
                var id_name = $(this).attr('id');
                var escaped_id = id.replace(/'/g, "\\'");
                $('<div class="multipage_nav" id="'+id_name+'_nav">' +
                    '<a href="#" class="multipage_back" onclick="return jQuery(\''+escaped_id+'\').prevpage();">' + pm_error_object.back + '</a>' +
                    '<a href="#" class="multipage_next" onclick="return jQuery(\''+escaped_id+'\').nextpage();">' + pm_error_object.next + '</a>' +
                    '<span class="multipage_state"></span>' +
                    '<div class="clearer"></div>' +
                  '</div>').insertAfter(this);
            }
        }
        
        if (document.location.hash) { 
            $(this).gotopage('#'+document.location.hash.substring(1,document.location.hash.length));
        } else {
            $(this).gotopage(1);
        }
        
        return false;
    }
    
    $(function() {
        var $multipageForms = $('form.multipage, #multipage');
        if (!$multipageForms.length || typeof $multipageForms.multipage !== 'function') {
            return;
        }

        var transitionFn = (typeof transition === 'function') ? transition : function(from, to) {
            $(from).hide();
            $(to).show();
        };

        var stateFn = (typeof textpages === 'function') ? textpages : function(obj, page, pages) {
            $(obj).html(page + ' of ' + pages);
        };

        $multipageForms.each(function() {
            var $form = $(this);
            $form.multipage({
                transitionFunction: transitionFn, 
                stateFunction: stateFn
            });
        });
        
        $('form').on( 'submit', function(){
            return true;
        });
    });
})(jQuery);
