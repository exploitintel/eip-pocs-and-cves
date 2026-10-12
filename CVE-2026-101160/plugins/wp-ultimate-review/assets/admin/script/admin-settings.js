
const { __ } = wp.i18n;

// show hide js global function
function xs_review_show_hide(getID){
	var idData = document.getElementById('xs_review_tr__'+getID);
	if(idData){
		idData.classList.toggle('active_tr');
	}
}

// show hide js global function
function xs_review_show_hide_2(getID){
	var idData = document.getElementById('xs_review_tr__custom_code');
	if(getID == 'custom_code'){
		idData.classList.add('active_tr');
	}else{
		idData.classList.remove('active_tr');
	}
}

// this function for copy data of Review Shortcode

function copyTextData(FIledid){
	var FIledidData = document.getElementById(FIledid);
	if(FIledidData){
		FIledidData.select();
		document.execCommand("copy");
	}
}



/*Ratting script*/

function responseMessage(msg) {
  jQuery('#review_data_show').fadeIn(200);  
  jQuery('#review_data_show').html("<span>" + msg + "</span>");
}
jQuery(document).ready(function(){
	click_xs_review_data();
});
function click_xs_review_data(){
	jQuery('#xs_review_stars li').on('mouseover', function(){
		var onStar = parseInt(jQuery(this).data('value'), 10); // The star currently mouse on
	   jQuery(this).parent().children('li.star-li').each(function(e){
		  if (e < onStar) {
			jQuery(this).addClass('hover');
		  }
		  else {
			jQuery(this).removeClass('hover');
		  }
		});
		
	  }).on('mouseout', function(){
		jQuery(this).parent().children('li.star-li').each(function(e){
		  jQuery(this).removeClass('hover');
		});
	  });
  
  
  jQuery('#xs_review_stars li').on('click', function(){
    var onStar = parseInt(jQuery(this).data('value'), 10); // The star currently selected
    var stars = jQuery(this).parent().children('li.star-li');
    
    for (i = 0; i < stars.length; i++) {
      jQuery(stars[i]).removeClass('selected');
    }
    
    for (i = 0; i < onStar; i++) {
      jQuery(stars[i]).addClass('selected');
    }
    
    var displayId = jQuery(this).parent().parent().children('input.right-review-ratting');
	displayId.val(onStar);
  });
}

/*Slider range*/
jQuery(document).ready(function($){
	jQuery('.xs-slidecontainer #xs_review_range').change(function(){
		var onData = parseInt(jQuery(this).val(), 10); 
		var displayId = jQuery(this).parent().parent().children('#review_data_show');
		displayId.html(onData);
	});

	// Review Location
	$('#review_location_id').on('change', function(){
		$('.wur-shortcode-wrapper').hide();
		if($(this).val() === 'custom_code'){
			$('.wur-shortcode-wrapper').fadeIn();
		}
	});
	$('#review_location_id').trigger('change');

	// Review form settings
	$('.wur-review-form-item').on('change', '.review_switch_button', function(){
		$(this).parents('.wur-review-form-item').find('.display-show-review-type').fadeToggle().toggleClass('active_tr');
	});

	// captcha settings
	$('.wur-show-captcha-settings-switch').on('change', function(){
		$('.wur-show-captcha-settings').fadeToggle().toggleClass('active_tr');
	});

	$('.wur-global-select-wrapper #captcha_setting_id').on('change', function(){
		$('.wur-right-content #wur_site_key').val('');
		$('.wur-right-content #wur_secret_key').val('');
	});

});

function click_xs_review_data_slider(dataTHis){
	var onData = parseInt(jQuery(dataTHis).val(), 10); 
	var displayId = jQuery(dataTHis).parent().parent().children('#review_data_show');
	displayId.html(onData);
}


if (typeof readCookie !== "function") {

	function readCookie(name) {
		let cookieName = name + "=";
		let ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(cookieName) === 0) return c.substring(cookieName.length, c.length);
		}
		return null;
	}

}

jQuery(function ($) {

	/**
	 * add new criteria field
	 */
	$(".add-product-criteria").click(function () {

		let limitCriteria = $(this).data('criteria-limit');

		let parentContainer = $(this).closest('.repater-overview-item');

		let currentAllCriteria = parentContainer.find(".reapter-div-xs");

		if (currentAllCriteria.length >= limitCriteria) {
			fireProModal()
		} else {

			let firstItemCopy = currentAllCriteria.first().clone().find("input").val("").end();
			parentContainer.append(firstItemCopy);
		}


	})

	$(".repater-overview-item").on('click', '.xs-review-btnRemove', function () {
		$(this).closest('.reapter-div-xs').fadeOut(600, function () {
			$(this).remove()
		})
	})

	$(".need-pro-version-alert").on('click', function () {
		fireProModal();
	})


	function fireProModal() {
		cuteAlert({
			img: 'cute-alert-img/info.svg',
			type: 'question',
			title: __('Oops...', 'wp-ultimate-review'),
			message: __('<p>You need to upgrade to the <strong><a href="https://wpmet.com/plugin/wp-ultimate-review/pricing/" target="_blank" style="color: red;">Premium</a> </strong> Version.</p>', 'wp-ultimate-review'),
			confirmText: __('Buy Premium', 'wp-ultimate-review'),
			cancelText: __('Close', 'wp-ultimate-review'),
		}).then((e)=>{
			if(e == "confirm"){
				open ("https://wpmet.com/plugin/wp-ultimate-review/pricing/", "_blank");
			}
		})
	}
	$('.wur-visiblity-disable').click(function(){
		cuteAlert({
			img: 'cute-alert-img/info.svg',
			type: "warning",
			title: __('WooCommerce Missing!', 'wp-ultimate-review'),
			message: __('Need to activate the WooCommerce plugin for products review.', 'wp-ultimate-review'),
			buttonText: __('Okay', 'wp-ultimate-review')
		  })
		  $('.wur-non-clickable').prop("checked", false)
	})

	$('#review_user_limit_by_id').change(function(){
		let reivewLimitBy = $(this).val();
		
		// Show/hide IP Detection Method based on selection
		if(reivewLimitBy === "ip"){
			$('.wur-ip-detection-method-wrapper').slideDown(300);
			cuteAlert({
				img: 'cute-alert-img/info.svg',
				type: "warning",
				title: __('Disclaimer!', 'wp-ultimate-review'),
				message: __('IP addresses can be changed or masked by various means, such as VPN services, proxies, or Tor networks. This means that while we strive to prevent multiple reviews from the same IP address, it is not a foolproof method to ensure one review per user.', 'wp-ultimate-review'),
				buttonText: __('Okay', 'wp-ultimate-review'),
				additionalClass: "ip-based-limit-disclaimer",
			  })
		} else {
			$('.wur-ip-detection-method-wrapper').slideUp(300);
		}
	})

	$('#ip_detection_method_id').change(function(){
		let ipDetectionMethod = $(this).val();
		
		// Toggle warning messages
		if(ipDetectionMethod === "proxy_headers"){
			$('.wur-remote-addr-warning').hide();
			$('.wur-proxy-headers-warning').show();
			
			cuteAlert({
				img: 'cute-alert-img/info.svg',
				type: "warning",
				title: __('Security Warning!', 'wp-ultimate-review'),
				message: __('Proxy headers (X-Forwarded-For, CF-Connecting-IP) can be easily spoofed by attackers to bypass IP-based rate limiting. Only enable this option if you are behind a trusted proxy infrastructure (e.g., CloudFlare) with proper configuration. The secure default is REMOTE_ADDR only.', 'wp-ultimate-review'),
				buttonText: __('I Understand', 'wp-ultimate-review'),
				additionalClass: "proxy-headers-security-warning",
			  })
		} else {
			$('.wur-proxy-headers-warning').hide();
			$('.wur-remote-addr-warning').show();
		}
	})

});