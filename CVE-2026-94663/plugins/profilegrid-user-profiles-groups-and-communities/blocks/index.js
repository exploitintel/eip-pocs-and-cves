document.addEventListener("DOMContentLoaded", function() {
const {registerBlockType} = wp.blocks; //Blocks API
const {createElement,useState} = wp.element; //React.createElement
const {__} = wp.i18n; //translation functions
const {InspectorControls} = wp.blockEditor; //Block inspector wrapper
const {TextControl,SelectControl,ServerSideRender,PanelBody,ToggleControl} = wp.components; //WordPress form inputs and server-side renderer
const el = wp.element.createElement;
const iconEl = el('svg', { width: 20, height: 20 },
  el('rect',{fill:"none",height:"24",width:"24"}),
  el('rect',{height:"4",width:"4",x:"10",y:"4"}),
  el('rect',{height:"4",width:"4",x:"4",y:"16"}),
  el('rect',{height:"4",width:"4",x:"4",y:"10"}),
  el('rect',{height:"4",width:"4",x:"4",y:"4"}),
  el('rect',{height:"4",width:"4",x:"16",y:"4"}),
  el('polygon', { points: "11,17.86 11,20 13.1,20 19.08,14.03 16.96,11.91" } ),
  el('polygon', { points: "14,12.03 14,10 10,10 10,14 12.03,14" } ),
 // el('polygon', { points: "11,17.86 11,20 13.1,20 19.08,14.03 16.96,11.91" } ),
  el('path', { d: "M20.85,11.56l-1.41-1.41c-0.2-0.2-0.51-0.2-0.71,0l-1.06,1.06l2.12,2.12l1.06-1.06C21.05,12.07,21.05,11.76,20.85,11.56z" } )
);

var blockOptions = window.pg_block_options || {};
var Groups = blockOptions.groups || [];
var Users = blockOptions.users || [];
var Pages = blockOptions.pages || [];

var searchRequest = null; 
function pm_advance_user_search(pagenum)
{


    var form = jQuery("#pm-advance-search-form");
    jQuery("#pm_result_pane").html('<div class="pm-loader"></div>');
    var pmDomColor = jQuery(".pmagic").find("a").css('color');
    jQuery(".pm-loader").css('border-top-color', pmDomColor);
  


       
       
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

    var data = {'nonce': pm_ajax_object.nonce};

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
    searchRequest =    jQuery.post(pm_ajax_object.ajax_url, data, function (resp) 
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

function groups_option()
{
    return group_options();
}

function group_layout()
{
    var gutenbProfileArea = jQuery('.pmagic').innerWidth();    //$('span#pm-cover-image-width').text(profileArea);
    //$('.pm-cover-image').children('img').css('width', profileArea);
    if (gutenbProfileArea < 550) {
        jQuery('.pm-user-card').addClass('pm100');
    } else if (gutenbProfileArea < 900) {
        jQuery('.pm-user-card').addClass('pm50');
    } else if (gutenbProfileArea >= 900) {
        jQuery('.pm-user-card').addClass('pm33');
    }
}

function type_options()
{
    var type = [];
    type[0] = {value: 'single', label: 'No'};
    type[1] = {value: 'paged', label: 'Yes'};
    return type;
}
registerBlockType( 'profilegrid-blocks/group-registration', {
	title: __( 'Sign-Up Form' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M240-160q-33 0-56.5-23.5T160-240q0-33 23.5-56.5T240-320q33 0 56.5 23.5T320-240q0 33-23.5 56.5T240-160Zm0-240q-33 0-56.5-23.5T160-480q0-33 23.5-56.5T240-560q33 0 56.5 23.5T320-480q0 33-23.5 56.5T240-400Zm0-240q-33 0-56.5-23.5T160-720q0-33 23.5-56.5T240-800q33 0 56.5 23.5T320-720q0 33-23.5 56.5T240-640Zm240 240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480l-80 80Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Zm-40 480v-85l212-212 85 85-212 212h-85Zm280-480q-33 0-56.5-23.5T640-720q0-33 23.5-56.5T720-800q33 0 56.5 23.5T800-720q0 33-23.5 56.5T720-640Zm45 240-85-85 29-29q12-12 28.5-12t27.5 12l29 29q12 11 12 27.5T794-429l-29 29Z'
        })
    ]),

	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string'
		},
		type: {
			default: 'single',
            type: 'string'
		}
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGid(gid) {
			setAttributes({gid});
		}

		//Function to update heading level
		function changeType(type) {
			setAttributes({type});
		}

		//Display block preview and UI
        return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/group-registration',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Form Settings', initialOpen: true},
                                //A simple text control for post id
                                createElement(SelectControl, {
                                    value: attributes.gid,
                                    label: __('User Group'),
                                    help: __('Choose the ProfileGrid user group for which you wish to display the sign-up form for.', 'profilegrid-user-profiles-groups-and-communities'),
                                    onChange: changeGid,
                                    options: Groups
                                }),
                                //Select heading level
                                createElement(SelectControl, {
                                    value: attributes.type,
                                    help: __("Display forms with more than one section as multi-page form on frontend. Please note, multi-page forms will render as single page in block editor view for usability reasons.", 'profilegrid-user-profiles-groups-and-communities'),
                                    label: __('Multi-Page'),
                                    onChange: changeType,
                                    options: type_options()
                                })
                                )
                    ]
                    )
        ]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-profile', {
	title: __( 'Current User Profile' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M720-400v-120H600v-80h120v-120h80v120h120v80H800v120h-80Zm-360-80q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm80-80h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-profile'
			} )
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/all-groups', {
	title: __( 'All Groups' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M500-482q29-32 44.5-73t15.5-85q0-44-15.5-85T500-798q60 8 100 53t40 105q0 60-40 105t-100 53Zm220 322v-120q0-36-16-68.5T662-406q51 18 94.5 46.5T800-280v120h-80Zm80-280v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80Zm-480-40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM0-160v-112q0-34 17.5-62.5T64-378q62-31 126-46.5T320-440q66 0 130 15.5T576-378q29 15 46.5 43.5T640-272v112H0Zm320-400q33 0 56.5-23.5T400-640q0-33-23.5-56.5T320-720q-33 0-56.5 23.5T240-640q0 33 23.5 56.5T320-560ZM80-240h480v-32q0-11-5.5-20T540-306q-54-27-109-40.5T320-360q-56 0-111 13.5T100-306q-9 5-14.5 14T80-272v32Zm240-400Zm0 400Z'
        })
    ]),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
        //display the post title
        attributes:  {
		view: {
			default: 'grid',
            type: 'string'
		},
		sortby: {
			default: 'newest',
            type: 'string'
		},
		sorting_dropdown: {
			type: 'boolean',
			default: true
		},
		view_icon: {
			type: 'boolean',
			default: true
		},
		search_box: {
            type: 'boolean',
			default: true
		},
		include: {
			default: '',
			type: 'string'
		},
		exclude: {
			default: '',
			type: 'string'
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;


		//Function to update heading level
		function changeView(view){
			setAttributes({view});
		}

        function changeSortby(sortby){
			setAttributes({sortby});
		}

		function changeInclude(include){
			setAttributes({include});
		}

		function changeExclude(exclude){
			setAttributes({exclude});
		}
                
        const toggleSortingdropdown = () => {
            setAttributes( { sorting_dropdown: ! attributes.sorting_dropdown } );
		};
                
        const toggleViewicon = () => {
            setAttributes( { view_icon: ! attributes.view_icon } );
		};
                
        const toggleSearchBox = () => {
            setAttributes( { search_box: ! attributes.search_box } );
		};

		//Display block preview and UI
		return createElement('div', {}, [		
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/all-groups',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.sortby,
							label: __( 'Default Sorting' ),
							onChange: changeSortby,
							options:[{value:'newest',label:'Newest'},{value:'oldest',label:'Oldest'},{value:'name_asc',label:'Alphabetical (A-Z)'},{value:'name_desc',label:'Alphabetical (Z-A)'}]
						}),
						createElement(SelectControl, {
							value: attributes.view,
							label: __( 'Default View' ),
							onChange: changeView,
							options:[{value:'grid',label:'Grid'},{value:'list',label:'List'}]
						}),
						createElement(ToggleControl, {
							checked: attributes.sorting_dropdown,
							label: __( 'Show Sorting Dropdown' ),
							onChange: toggleSortingdropdown
				
						}),
						createElement(ToggleControl, {
							checked: attributes.view_icon,
							label: __( 'Show View Icons' ),
							onChange: toggleViewicon
							
						}),
						createElement(ToggleControl, {
							checked: attributes.search_box,
							label: __( 'Show Search Box' ),
							onChange: toggleSearchBox
							//options:[{value:'1',label:'Yes'},{value:'0',label:'No'}]
						}),
						createElement(TextControl, {
							value: attributes.include,
							label: __( 'Include Specific Groups (IDs)' ),
							help: __( 'Comma-separated group IDs to include.' ),
							onChange: changeInclude
						}),
						createElement(TextControl, {
							value: attributes.exclude,
							label: __( 'Exclude Groups (IDs)' ),
							help: __( 'Comma-separated group IDs to exclude.' ),
							onChange: changeExclude
						})
                    )
				]
			)
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/all-users', {
	title: __( 'All Users' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        enableBackground: 'new 0 0 24 24',
        height: '24px',
        viewBox: '0 0 24 24',
        width: '24px',
        fill: '#000000'
    }, el('rect', {
        fill: 'none',
        height: '24',
        width: '24'
    }), el('g', null,
            el('path', {
                d: 'M4,13c1.1,0,2-0.9,2-2c0-1.1-0.9-2-2-2s-2,0.9-2,2C2,12.1,2.9,13,4,13z M5.13,14.1C4.76,14.04,4.39,14,4,14 c-0.99,0-1.93,0.21-2.78,0.58C0.48,14.9,0,15.62,0,16.43V18l4.5,0v-1.61C4.5,15.56,4.73,14.78,5.13,14.1z M20,13c1.1,0,2-0.9,2-2 c0-1.1-0.9-2-2-2s-2,0.9-2,2C18,12.1,18.9,13,20,13z M24,16.43c0-0.81-0.48-1.53-1.22-1.85C21.93,14.21,20.99,14,20,14 c-0.39,0-0.76,0.04-1.13,0.1c0.4,0.68,0.63,1.46,0.63,2.29V18l4.5,0V16.43z M16.24,13.65c-1.17-0.52-2.61-0.9-4.24-0.9 c-1.63,0-3.07,0.39-4.24,0.9C6.68,14.13,6,15.21,6,16.39V18h12v-1.61C18,15.21,17.32,14.13,16.24,13.65z M8.07,16 c0.09-0.23,0.13-0.39,0.91-0.69c0.97-0.38,1.99-0.56,3.02-0.56s2.05,0.18,3.02,0.56c0.77,0.3,0.81,0.46,0.91,0.69H8.07z M12,8 c0.55,0,1,0.45,1,1s-0.45,1-1,1s-1-0.45-1-1S11.45,8,12,8 M12,6c-1.66,0-3,1.34-3,3c0,1.66,1.34,3,3,3s3-1.34,3-3 C15,7.34,13.66,6,12,6L12,6z'
            })
            )),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
        //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/all-users'
			} )
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/login-form', {
	title: __( 'Login Form' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        enableBackground: 'new 0 0 24 24',
        height: '24px',
        viewBox: '0 0 24 24',
        width: '24px',
        fill: '#000000'
    }, el('g', null,
            el('rect', {
                fill: 'none',
                height: '24',
                width: '24'
            })),
            el('g', null,
                    el('path', {
                        d: 'M11,7L9.6,8.4l2.6,2.6H2v2h10.2l-2.6,2.6L11,17l5-5L11,7z M20,19h-8v2h8c1.1,0,2-0.9,2-2V5c0-1.1-0.9-2-2-2h-8v2h8V19z'
                    })
                    )),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/login-form'
			} )
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-page', {
	title: __( 'Group Page' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, el('path', {
        d: 'M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66-31 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z'
    })),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string'
		}
	},
        //display the post title
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGid(gid){
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-page',
				attributes: attributes
			} ),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Group Settings', initialOpen: true },
					//A simple text control for post id
                        createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Group' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the information.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGid,
							options:Groups
						})
                    )
				]
			),
            group_layout()
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-blogs', {
	title: __( 'User Blogs' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h440l200 200v440q0 33-23.5 56.5T760-120H200Zm0-80h560v-400H600v-160H200v560Zm80-80h400v-80H280v80Zm0-320h200v-80H280v80Zm0 160h400v-80H280v80Zm-80-320v160-160 560-560Z'
        })
    ]),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
        //display the post title
        attributes:  {
		wpblog: {
            type: 'boolean',
			default: true
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		const toggleWPBlog = () => {
			setAttributes( { wpblog: ! attributes.wpblog } );
		};

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-blogs',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(ToggleControl, {
							checked: attributes.wpblog,
							label: __( 'Show WP Blogs' ),
							onChange: toggleWPBlog
						})
                    )
				]
			)
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/blog-submission', {
	title: __( 'Blog Submission' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, el('path', {
        d: 'M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h360v80H200v560h560v-360h80v360q0 33-23.5 56.5T760-120H200Zm120-160v-80h320v80H320Zm0-120v-80h320v80H320Zm0-120v-80h320v80H320Zm360-80v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80Z'
    })),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [
                    
					
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/blog-submission'
			} )
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/password-recovery-form', {
	title: __( 'Password Recovery Form' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon:  el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M240-640h360v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85h-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640Zm0 480h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM240-160v-400 400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/password-recovery-form'
			} )
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/blogs', {
	title: __( 'Blog Area' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-newspaper',
        viewBox: '0 0 16 16'
    },
            el('path', {
                d: 'M0 2.5A1.5 1.5 0 0 1 1.5 1h11A1.5 1.5 0 0 1 14 2.5v10.528c0 .3-.05.654-.238.972h.738a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 1 1 0v9a1.5 1.5 0 0 1-1.5 1.5H1.497A1.497 1.497 0 0 1 0 13.5v-11zM12 14c.37 0 .654-.211.853-.441.092-.106.147-.279.147-.531V2.5a.5.5 0 0 0-.5-.5h-11a.5.5 0 0 0-.5.5v11c0 .278.223.5.497.5H12z'
            }),
            el('path', {
                d: 'M2 3h10v2H2V3zm0 3h4v3H2V6zm0 4h4v1H2v-1zm0 2h4v1H2v-1zm5-6h2v1H7V6zm3 0h2v1h-2V6zM7 8h2v1H7V8zm3 0h2v1h-2V8zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1z',
            })),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/blogs'
			} )
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-description', {
	title: __( 'Group Description' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        width: '24',
        viewBox: '0 -960 960 960'
    },
            el('path', {
                d: 'M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z',
            })),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string'
		}
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGids(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-description',
				attributes: attributes
			}),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Form Settings', initialOpen: true },
						//A simple text control for post id
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Group' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the information.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),
                    )
				]
			)
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-first-name', {
	title: __( 'User First Name' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon:  el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-person-badge',
        viewBox: '0 0 16 16'
    }, [
        el('path', {
            d: 'M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z',
        }),
        el('path', {
            d: 'M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;
		function selectuid(uid) {
			setAttributes({uid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-first-name',
                attributes: attributes
			} ),
            //Block inspector
			// alert(Users),
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.uid,
							help:__("Select the username for which you wish to display the first name.",'profilegrid-user-profiles-groups-and-communities'),
							label: __( 'Choose Username' ),
							onChange: selectuid,
							options: Users,
						}),
                    )
				]
			)
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/messaging', {
	title: __( 'Messaging' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-chat',
        viewBox: '0 0 16 16'
    }, [
        el('path', {
            d: 'M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-.493 3.905a21.682 21.682 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a9.68 9.68 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/messaging'
			} )
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-member-count', {
	title: __( 'Group Member Count' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        width: '24',
        viewBox: '0 -960 960 960',
    }, [
        el('path', {
            d: 'M500-482q29-32 44.5-73t15.5-85q0-44-15.5-85T500-798q60 8 100 53t40 105q0 60-40 105t-100 53Zm220 322v-120q0-36-16-68.5T662-406q51 18 94.5 46.5T800-280v120h-80Zm80-280v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80Zm-480-40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM0-160v-112q0-34 17.5-62.5T64-378q62-31 126-46.5T320-440q66 0 130 15.5T576-378q29 15 46.5 43.5T640-272v112H0Zm320-400q33 0 56.5-23.5T400-640q0-33-23.5-56.5T320-720q-33 0-56.5 23.5T240-640q0 33 23.5 56.5T320-560ZM80-240h480v-32q0-11-5.5-20T540-306q-54-27-109-40.5T320-360q-56 0-111 13.5T100-306q-9 5-14.5 14T80-272v32Zm240-400Zm0 400Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string'
		}
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGids(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-member-count',
				attributes: attributes
			}),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Form Settings', initialOpen: true },
						//A simple text control for post id
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the total number of members.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),
                    )
				]
			)
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-last-name', {
	title: __( 'User Last Name' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-person-badge-fill',
        viewBox: '0 0 16 16'
    }, [
        el('path', {
            d: 'M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-.245z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-last-name',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.uid,
							help:__("Select the username for which you wish to display the last name.",'profilegrid-user-profiles-groups-and-communities'),
							label: __( 'User ID' ),
							onChange: selectuid,
							options: Users,
						}),
                    )
				]
			)
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/notifications', {
	title: __( 'Notifications' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        width: '24',
        viewBox: '0 -960 960 960'
    }, [
        el('path', {
            d: 'M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/notifications'
			} )
			
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-manager-count', {
	title: __( 'Group Manager Count' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-briefcase',
        viewBox: '0 0 16 16'
    }, [
        el('path', {
            d: 'M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string',
		},
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGids(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-manager-count',
				attributes: attributes
			}),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Form Settings', initialOpen: true },
						//A simple text control for post id
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the total number of managers.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),
                    )
				]
			)
		] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-email', {
	title: __( 'User Email ID' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        width: '24',
        viewBox: '0 -960 960 960'
    }, [
        el('path', {
            d: 'M560-520h280v-200H560v200Zm140-50-100-70v-40l100 70 100-70v40l-100 70ZM80-120q-33 0-56.5-23.5T0-200v-560q0-33 23.5-56.5T80-840h800q33 0 56.5 23.5T960-760v560q0 33-23.5 56.5T880-120H80Zm556-80h244v-560H80v560h4q42-75 116-117.5T360-360q86 0 160 42.5T636-200ZM360-400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35ZM182-200h356q-34-38-80.5-59T360-280q-51 0-97 21t-81 59Zm178-280q-17 0-28.5-11.5T320-520q0-17 11.5-28.5T360-560q17 0 28.5 11.5T400-520q0 17-11.5 28.5T360-480Zm120 0Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		},
	},
    edit(props) {
        const attributes = props.attributes;
        const setAttributes = props.setAttributes;

        function selectuid(uid) {
            setAttributes({uid});
        }

        //Display block preview and UI
        return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/user-email',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Settings', initialOpen: true},
                                createElement(SelectControl, {
                                    value: attributes.uid,
                                    help: __("Select the username for which you wish to display the email ID.", 'profilegrid-user-profiles-groups-and-communities'),
                                    label: __('User ID'),
                                    onChange: selectuid,
                                    options: Users,
                                }),
                                )
                    ]
                    )

        ]);
    },
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/friends', {
	title: __( 'Friends' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M500-482q29-32 44.5-73t15.5-85q0-44-15.5-85T500-798q60 8 100 53t40 105q0 60-40 105t-100 53Zm220 322v-120q0-36-16-68.5T662-406q51 18 94.5 46.5T800-280v120h-80Zm80-280v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80Zm-480-40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM0-160v-112q0-34 17.5-62.5T64-378q62-31 126-46.5T320-440q66 0 130 15.5T576-378q29 15 46.5 43.5T640-272v112H0Zm320-400q33 0 56.5-23.5T400-640q0-33-23.5-56.5T320-720q-33 0-56.5 23.5T240-640q0 33 23.5 56.5T320-560ZM80-240h480v-32q0-11-5.5-20T540-306q-54-27-109-40.5T320-360q-56 0-111 13.5T100-306q-9 5-14.5 14T80-272v32Zm240-400Zm0 400Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/friends'
			} )
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-managers', {
	title: __( 'Group Managers' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M680-360q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM480-160v-56q0-24 12.5-44.5T528-290q36-15 74.5-22.5T680-320q39 0 77.5 7.5T832-290q23 9 35.5 29.5T880-216v56H480Zm-80-320q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-160ZM80-160v-112q0-34 17-62.5t47-43.5q60-30 124.5-46T400-440q35 0 70 6t70 14l-34 34-34 34q-18-5-36-6.5t-36-1.5q-58 0-113.5 14T180-306q-10 5-15 14t-5 20v32h240v80H80Zm320-80Zm0-320q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default: pg_groups[0].id,
            type: 'string'
		},
		sep : {
			default: ",",
            type: 'string'
		}
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changesep(sep) {
			setAttributes({sep});
		}

		function changeGids(gid) {
			setAttributes({gid});
		}

        //Display block preview and UI
        return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/group-managers',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Form Settings', initialOpen: true},
                                //A simple text control for post id
                                createElement(SelectControl, {
                                    value: attributes.gid,
                                    label: __('User Groups'),
                                    help: __('Select the ProfileGrid User Group for which you wish to display the managers.', 'profilegrid-user-profiles-groups-and-communities'),
                                    onChange: changeGids,
                                    options: Groups
                                }),
                                createElement(TextControl, {
                                    value: attributes.sep,
                                    label: __('Seperator'),
                                    help: __('Please specify the separator used to delimit the managers. The default separator is a comma (,).', 'profilegrid-user-profiles-groups-and-communities'),
                                    onChange: changesep,
                                }),
                                )
                    ]
                    )
        ]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-profile-image', {
	title: __( 'User Profile Image' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M440-440ZM120-120q-33 0-56.5-23.5T40-200v-480q0-33 23.5-56.5T120-760h126l74-80h240v80H355l-73 80H120v480h640v-360h80v360q0 33-23.5 56.5T760-120H120Zm640-560v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80ZM440-260q75 0 127.5-52.5T620-440q0-75-52.5-127.5T440-620q-75 0-127.5 52.5T260-440q0 75 52.5 127.5T440-260Zm0-80q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		},
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

        //Display block preview and UI
        return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/user-profile-image',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Settings', initialOpen: true},
                                createElement(SelectControl, {
                                    value: attributes.uid,
                                    help: __("Select the username for which you wish to display the profile image.", 'profilegrid-user-profiles-groups-and-communities'),
                                    label: __('User ID'),
                                    onChange: selectuid,
                                    options: Users
                                }),
                                )
                    ]
                    )

        ]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-managers-list', {
	title: __( 'Group Managers List' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M640-400q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM400-160v-76q0-21 10-40t28-30q45-27 95.5-40.5T640-360q56 0 106.5 13.5T842-306q18 11 28 30t10 40v76H400Zm86-80h308q-35-20-74-30t-80-10q-41 0-80 10t-74 30Zm154-240q17 0 28.5-11.5T680-520q0-17-11.5-28.5T640-560q-17 0-28.5 11.5T600-520q0 17 11.5 28.5T640-480Zm0-40Zm0 280ZM120-400v-80h320v80H120Zm0-320v-80h480v80H120Zm324 160H120v-80h360q-14 17-22.5 37T444-560Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
			default:pg_groups[0].id,
            type: 'string',
		},
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGids(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-managers-list',
				attributes: attributes
			}),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Form Settings', initialOpen: true },
						//A simple text control for post id
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Choose the ProfileGrid user group for which you wish to display list of managers.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),
                    )
				]
			)
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-cover-image', {
	title: __( 'User Cover Image' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h360v80H200v560h560v-360h80v360q0 33-23.5 56.5T760-120H200Zm480-480v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80ZM240-280h480L570-480 450-320l-90-120-120 160Zm-40-480v560-560Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/user-cover-image',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Settings', initialOpen: true},
                                createElement(SelectControl, {
                                    value: attributes.uid,
                                    help: __("Select the username for which you wish to display the profile cover image.", 'profilegrid-user-profiles-groups-and-communities'),
                                    label: __('User ID'),
                                    onChange: selectuid,
                                    options: Users
                                }),
                                )
                    ])

        ] );
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/settings', {
	title: __( 'Settings' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M580-40q-25 0-42.5-17.5T520-100v-280q0-25 17.5-42.5T580-440h280q25 0 42.5 17.5T920-380v280q0 25-17.5 42.5T860-40H580Zm0-60h280v-32q-25-31-61-49.5T720-200q-43 0-79 18.5T580-132v32Zm140-140q25 0 42.5-17.5T780-300q0-25-17.5-42.5T720-360q-25 0-42.5 17.5T660-300q0 25 17.5 42.5T720-240ZM480-480Zm2-140q-58 0-99 41t-41 99q0 48 27 84t71 50v-90q-8-8-13-20.5t-5-23.5q0-25 17.5-42.5T482-540q14 0 25 5.5t19 14.5h90q-13-44-49.5-72T482-620ZM370-80l-16-128q-13-5-24.5-12T307-235l-119 50L78-375l103-78q-1-7-1-13.5v-27q0-6.5 1-13.5L78-585l110-190 119 50q11-8 23-15t24-12l16-128h220l16 128q13 5 24.5 12t22.5 15l119-50 110 190-85 65H696q-1-5-2-10.5t-3-10.5l86-65-39-68-99 42q-22-23-48.5-38.5T533-694l-13-106h-79l-14 106q-31 8-57.5 23.5T321-633l-99-41-39 68 86 64q-5 15-7 30t-2 32q0 16 2 31t7 30l-86 65 39 68 99-42q24 25 54 42t65 22v184h-70Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/settings'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-member-cards', {
	title: __( 'Group Member Cards' ), // Block title.
	category:  __( 'profilegrid' ), //category
        icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '16',
        height: '16',
        fill: 'currentColor',
        class: 'bi bi-person-vcard',
        viewBox: '0 0 16 16'
    }, [
        el('path', {
            d: 'M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5ZM9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8Zm1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Z',
        }),
        el('path', {
            d: 'M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2ZM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96c.026-.163.04-.33.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1.006 1.006 0 0 1 1 12V4Z',
        })
    ]),
        supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		gid : {
			default:pg_groups[0].id,
			type: 'string'
		},
		sortby: {
			default: 'latest_first',
			type: 'string'
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;
                
		function changeGids(gid) {
			setAttributes({gid});
		}
		
        function changeSortby(sortby){
			setAttributes({sortby});
		}

		//Display block preview and UI
		return createElement('div', {}, [		
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-member-cards',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },

						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the members in grid format.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),

						createElement(SelectControl, {
							value: attributes.sortby,
							label: __( 'Default Sorting' ),
							onChange: changeSortby,
							options:[{value:'latest_first',label:'Newest First'},{value:'oldest_first',label:'Oldest First'},{value:'first_name_asc',label:'First Name [ Alphabetical (A-Z) ]'},{value:'first_name_desc',label:'First Name [Alphabetical (Z-A) ]'},{value:'last_name_asc',label:'Last Name [Alphabetical (A-Z) ]'},{value:'last_name_desc',label:'Last Name [Alphabetical (Z-A) ]'}]
						}),
                    )
				]
			)
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-default-group', {
	title: __( 'User Default Group' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		},
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-default-group',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.uid,
							help:__("Select a username to display its default associated Group.",'profilegrid-user-profiles-groups-and-communities'),
							label: __( 'User ID' ),
							onChange: selectuid,
							options: Users
						}),
                    )
				]
			)
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/account', {
	title: __( 'Account' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/account'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-manager-cards', {
	title: __( 'Group Manager Cards' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M200-246q54-53 125.5-83.5T480-360q83 0 154.5 30.5T760-246v-514H200v514Zm280-194q58 0 99-41t41-99q0-58-41-99t-99-41q-58 0-99 41t-41 99q0 58 41 99t99 41ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm80-80h400v-10q-42-35-93-52.5T480-280q-56 0-107 17.5T280-210v10Zm200-320q-25 0-42.5-17.5T420-580q0-25 17.5-42.5T480-640q25 0 42.5 17.5T540-580q0 25-17.5 42.5T480-520Zm0 17Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes:  {
		gid : {
                    default:pg_groups[0].id,
            type: 'string'
		}
	},
        //display the post title
	edit(props) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		//Function to update id attribute
		function changeGids(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-manager-cards',
				attributes: attributes
			}),
			//Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Form Settings', initialOpen: true },
						//A simple text control for post id
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Select the ProfileGrid User Group for which you wish to display the managers in grid format.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),
                    )
				]
			)
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-groups', {
	title: __( 'User Groups' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M360-320q33 0 56.5-23.5T440-400q0-33-23.5-56.5T360-480q-33 0-56.5 23.5T280-400q0 33 23.5 56.5T360-320Zm240 0q33 0 56.5-23.5T680-400q0-33-23.5-56.5T600-480q-33 0-56.5 23.5T520-400q0 33 23.5 56.5T600-320ZM480-520q33 0 56.5-23.5T560-600q0-33-23.5-56.5T480-680q-33 0-56.5 23.5T400-600q0 33 23.5 56.5T480-520Zm0 440q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		},
		sep: {
			type: 'string',
			default: ","
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

		function changesep(sep) {
			setAttributes({sep});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-groups',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.uid,
							help:__("Select the username for which you wish to display the Groups it is a member of.",'profilegrid-user-profiles-groups-and-communities'),
							label: __( 'User ID' ),
							onChange: selectuid,
							options: Users
						}),

						createElement(TextControl, {
							value: attributes.sep,
							label: __( 'Seperator' ),
							help:__('Please specify the separator used to delimit the user groups. The default separator is a comma (,).','profilegrid-user-profiles-groups-and-communities'),
							onChange: changesep,
						}),
                    )
				]
			)
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/change-password', {
	title: __( 'Change Password' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M80-200v-80h800v80H80Zm46-242-52-30 34-60H40v-60h68l-34-58 52-30 34 58 34-58 52 30-34 58h68v60h-68l34 60-52 30-34-60-34 60Zm320 0-52-30 34-60h-68v-60h68l-34-58 52-30 34 58 34-58 52 30-34 58h68v60h-68l34 60-52 30-34-60-34 60Zm320 0-52-30 34-60h-68v-60h68l-34-58 52-30 34 58 34-58 52 30-34 58h68v60h-68l34 60-52 30-34-60-34 60Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/change-password'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/user-group-badges', {
	title: __( 'User Group Badges' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon:el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M480-80q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 152-90.5 276.5T480-80Zm0-84q104-33 172-132t68-220v-189l-240-90-240 90v189q0 121 68 220t172 132Zm0-316Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		uid: {
			type: 'string',
			default: pg_groups[1]
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectuid(uid) {
			setAttributes({uid});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/user-group-badges',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.uid,
							help:__("Select the username for which you wish to display the Group badges.",'profilegrid-user-profiles-groups-and-communities'),
							label: __( 'User ID' ),
							onChange: selectuid,
							options: Users
						}),
                    )
				]
			)
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/unread-notification-count', {
	title: __( 'Unread Notification Count' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M80-560q0-100 44.5-183.5T244-882l47 64q-60 44-95.5 111T160-560H80Zm720 0q0-80-35.5-147T669-818l47-64q75 55 119.5 138.5T880-560h-80ZM160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/unread-notification-count'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/privacy', {
	title: __( 'Privacy' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M480-480Zm0 400q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 10-.5 20t-1.5 20q-9-2-18.5-3t-19.5-1q-11 0-21 1t-21 3q1-10 1.5-19.5t.5-20.5v-189l-240-90-240 90v189q0 121 68 220t172 132q21-7 41-17t39-23v94q-19 10-39 17.5T480-80Zm194 0q-14 0-24-10t-10-24v-132q0-14 10-24t24-10h6v-40q0-33 23.5-56.5T760-400q33 0 56.5 23.5T840-320v40h6q14 0 24 10t10 24v132q0 14-10 24t-24 10H674Zm46-200h80v-40q0-17-11.5-28.5T760-360q-17 0-28.5 11.5T720-320v40Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/privacy'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/unread-message-count', {
	title: __( 'Unread Message Count' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M80-80v-720q0-33 23.5-56.5T160-880h404q-4 20-4 40t4 40H160v525l46-45h594v-324q23-5 43-13.5t37-22.5v360q0 33-23.5 56.5T800-240H240L80-80Zm80-720v480-480Zm600 80q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Z',
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/unread-message-count'
			} )
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/delete-account', {
	title: __( 'Delete Account' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M640-520v-80h240v80H640Zm-280 40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm80-80h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/delete-account'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/about', {
	title: __( 'About' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M160-40v-80h640v80H160Zm0-800v-80h640v80H160Zm320 400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35ZM160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm70-80q45-56 109-88t141-32q77 0 141 32t109 88h70v-480H160v480h70Zm118 0h264q-29-20-62.5-30T480-280q-36 0-69.5 10T348-240Zm132-280q-17 0-28.5-11.5T440-560q0-17 11.5-28.5T480-600q17 0 28.5 11.5T520-560q0 17-11.5 28.5T480-520Zm0 40Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/about'
			} )
			
		]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-cards', {
	title: __( 'Group Cards' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M0-240v-63q0-43 44-70t116-27q13 0 25 .5t23 2.5q-14 21-21 44t-7 48v65H0Zm240 0v-65q0-32 17.5-58.5T307-410q32-20 76.5-30t96.5-10q53 0 97.5 10t76.5 30q32 20 49 46.5t17 58.5v65H240Zm540 0v-65q0-26-6.5-49T754-397q11-2 22.5-2.5t23.5-.5q72 0 116 26.5t44 70.5v63H780Zm-455-80h311q-10-20-55.5-35T480-370q-55 0-100.5 15T325-320ZM160-440q-33 0-56.5-23.5T80-520q0-34 23.5-57t56.5-23q34 0 57 23t23 57q0 33-23 56.5T160-440Zm640 0q-33 0-56.5-23.5T720-520q0-34 23.5-57t56.5-23q34 0 57 23t23 57q0 33-23 56.5T800-440Zm-320-40q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm0-80q17 0 28.5-11.5T520-600q0-17-11.5-28.5T480-640q-17 0-28.5 11.5T440-600q0 17 11.5 28.5T480-560Zm1 240Zm-1-280Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		gid: {
			type: 'array',
			default: [ pg_groups[0].id ]
		}
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function selectgid(gid) {
			setAttributes({gid});
		}

		//Display block preview and UI
        return createElement('div', {}, [
            //Preview a block with a PHP render callback
            createElement(wp.serverSideRender, {
                block: 'profilegrid-blocks/group-cards',
                attributes: attributes
            }),
            //Block inspector
            createElement(InspectorControls, {},
                    [
                        createElement(PanelBody, {title: 'Settings', initialOpen: true},
                                createElement(SelectControl, {
                                    multiple: true,
                                    value: attributes.gid,
                                    help: __("Select the ProfileGrid User Group(s) for which you wish to display Group card(s). [Multi-Select enabled]", 'profilegrid-user-profiles-groups-and-communities'),
                                    label: __('Group ID'),
                                    onChange: selectgid,
                                    options: Groups
                                }),
                                )
                    ]
                    )

        ]);
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/groups', {
	title: __( 'Groups' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
    //display the post title
	edit(props){
		//Display block preview and UI
		return createElement('div', {}, [	
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/groups'
			} )
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});

registerBlockType( 'profilegrid-blocks/group-name', {
	title: __( 'Group Name' ), // Block title.
	category:  __( 'profilegrid' ), //category
	icon: el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        height: '24',
        viewBox: '0 -960 960 960',
        width: '24'
    }, [
        el('path', {
            d: 'M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z'
        })
    ]),
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	//display the post title
	attributes:  {
		gid: {
			type: 'string',
			default: pg_groups[0].id
		},

		link: {
            type: 'boolean',
			default: false
		},
	},
	edit(props){
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;

		function changeGids(gid) {
			setAttributes({gid});
		}

		const togglelink = () => {
			setAttributes( { link: ! attributes.link } );
		};

		//Display block preview and UI
		return createElement('div', {}, [
			//Preview a block with a PHP render callback
			createElement( wp.serverSideRender, {
				block: 'profilegrid-blocks/group-name',
                attributes: attributes
			} ),
            //Block inspector
			createElement( InspectorControls, {},
				[
                    createElement( PanelBody, { title: 'Settings', initialOpen: true },
						createElement(SelectControl, {
							value: attributes.gid,
							label: __( 'User Groups' ),
							help:__('Choose the ProfileGrid user group for which you wish to Group Name.','profilegrid-user-profiles-groups-and-communities'),
							onChange: changeGids,
							options:Groups
						}),

						createElement(ToggleControl, {
							checked: attributes.link,
							label: __( 'link' ),
							onChange: togglelink
						}),
                    )
				]
			)
			
		] )
	},
	save(){
		return null;//save has to exist. This all we need
	}
});
});
