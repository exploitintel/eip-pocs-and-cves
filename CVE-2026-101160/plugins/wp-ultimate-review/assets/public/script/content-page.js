(function ($) {
    'use strict';

    $(function () {
        /**
         * Review Form
         */
        var $reviewForm = $('#xs_review_form_public_data');

        if ( $reviewForm.length && typeof Storage !== 'undefined' && localStorage.getItem('review_form') ) {
            $('html, body').animate({
                scrollTop: $('#xs-user-review-box').offset().top - 30
            }, 1200);

            localStorage.removeItem('review_form');
        }

        $reviewForm.on('submit', function () {
            var $el = $(this);

            if (typeof Storage !== 'undefined') {
                localStorage.setItem('review_form', '1');
            }
        });
    });
}(jQuery));


/* Ratting script */
jQuery(function () {

    jQuery('#xs_review_stars li').on('mouseover', function () {
        var onStar = parseInt(jQuery(this).data('value'), 10); // The star currently mouse on
        jQuery(this).parent().children('li.star-li').each(function (e) {
            if(e < onStar) {
                jQuery(this).addClass('hover');
            }
            else {
                jQuery(this).removeClass('hover');
            }
        });

    }).on('mouseout', function () {
        jQuery(this).parent().children('li.star-li').each(function (e) {
            jQuery(this).removeClass('hover');
        });
    });


    jQuery('#xs_review_stars li').on('click', function () {
        var onStar = parseInt(jQuery(this).data('value'), 10); // The star currently selected
        var stars = jQuery(this).parent().children('li.star-li');

        for(i = 0; i < stars.length; i++) {
            jQuery(stars[i]).removeClass('selected');
        }

        for(i = 0; i < onStar; i++) {
            jQuery(stars[i]).addClass('selected');
        }

        var displayId = jQuery(this).parent().parent().children('input#ratting_review_hidden');
        displayId.val(onStar);

        var msg = "";
        if(onStar > 1) {
            msg = "<strong>" + onStar + "</strong>";
        }
        else {
            msg = "<strong>" + onStar + " </strong>";
        }
        responseMessage(msg);

    });


});


function responseMessage(msg) {
    jQuery('#review_data_show').fadeIn(200);
    jQuery('#review_data_show').html("<span>" + msg + "</span>");
}


/*Slider range*/
jQuery(document).ready(function () {

    var sliderReview = jQuery("#xs_review_range");
    var outputReview = jQuery("#review_data_show");
    if(sliderReview.length > 0) {
        outputReview.html(sliderReview.val());
        sliderReview.on('change', function () {
            outputReview.html(jQuery(this).val());
        });
    }

});

jQuery(document).ready(function ($) {

    $('canvas.wur_pie').each(function (idx, elm) {

        draw_pic_chart(elm, idx);
    });

});

function draw_pic_chart(elm, color_index) {

    var idx = color_index % 10;
    var color_set = [
        '#1f77b4',
        '#ff7f0e',
        '#2ca02c',
        '#d62728',
        '#9467bd',
        '#8c564b',
        '#e377c2',
        '#7f7f7f',
        '#bcbd22',
        '#17becf'
    ];

    var base_color = '#555';
    var arc_color = '#f00';
    var stroke_wd = 10;

    var x = elm.width / 2;
    var y = elm.height / 2;

    var base = elm.dataset.base;
    var point = elm.dataset.rating;

    var radius = elm.dataset.rad || 40;

    var fill_up_angle = 360 * point / base;

    var s_angle = -90;
    var e_angle = s_angle + fill_up_angle;

    var startAngle = s_angle / 180 * Math.PI;
    var endAngle = e_angle / 180 * Math.PI;
    var counterClockwise = false;


    var context = elm.getContext('2d');

    context.beginPath();
    context.lineWidth = 3;
    context.strokeStyle = '#fff';
    context.arc(x, y, radius, 0, 360);
    context.stroke();

    context.beginPath();
    context.lineWidth = 3;
    context.strokeStyle = color_set[idx];
    context.arc(x, y, radius, startAngle, endAngle, counterClockwise);
    context.stroke();

    return true;
}
