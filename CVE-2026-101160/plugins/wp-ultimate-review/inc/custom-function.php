<?php

defined('ABSPATH') || exit;

if(!function_exists('review_kit_rating')) {

	function review_kit_rating($atts) {
		if(class_exists('\WurReview\App\Content')) {
			$return = \WurReview\App\Content::instance()->wur_review_kit_rating($atts);

			return $return;
		}
	}
}

if(!function_exists('review_kit_forms')) {

	function review_kit_forms($atts = []) {

		if(class_exists('\WurReview\App\Content')) {

			$return = \WurReview\App\Content::instance()->wur_review_kit_shortcode($atts);

			return $return;
		}
	}
}
