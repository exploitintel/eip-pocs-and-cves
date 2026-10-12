<?php
use WurReview\Helper\Helper;

defined('ABSPATH') || exit;

if((isset($return_data_overview->overview->enable) ? $return_data_overview->overview->enable : 'No') == 'Yes') {
	$itemRattingHeatting = isset($return_data_overview->overview->heading) ? $return_data_overview->overview->heading : 'Overview';
	?>
    <div class="xs-review-box view-review-list overview-xs-review " id="xs-review-box">
        <h3 class="total-reivew-headding"> <?php echo esc_html($itemRattingHeatting); ?></h3>
        <div class="xs-reviewer-details">
			<?php

			$itemRatting  = isset($return_data_overview->overview->item) ? $return_data_overview->overview->item : [];
			$totalRatting = count($itemRatting);

			$itemRattingStyle = isset($return_data_overview->overview->style) ? $return_data_overview->overview->style : 'star';

			$itemRattingSummary = isset($return_data_overview->overview->summary->data) ? $return_data_overview->overview->summary->data : '';
			$arrayCountValues   = [];

			$totalRattingsSum   = 1;
			$totalRattingsCount = 1;

			$overViewTotal = 1;

			?>
            <div class="xs-review-overview-list <?php if(!isset($return_data_overview->overview->average->enable)) { ?>left-full<?php } ?> custom-rat ">
				<?php
				$ratCount             = 0;
				$totalNumberSumRat    = 0;
				$totalNumberSumRange  = 0;
				$avgRat               = 0;
				foreach($itemRatting AS $ratValue):

					$rattinfDataName = $ratValue->name;
					$rattinfDataRat   = $ratValue->ratting;
					$rattinfDataRange = $ratValue->rat_range;
					if(strlen(trim($rattinfDataName)) > 2 && $rattinfDataRat > 0) {

						if($itemRattingStyle == 'star') {
							/*if($ratCount != 0) {
								echo '<div class="border-div "> </div>';
							}*/
							$rattingData = self::wur_ratting_view_star_point($rattinfDataRat, $rattinfDataRange);
						} elseif($itemRattingStyle == 'point') {
							/*if($ratCount != 0) {
								echo '<div class="border-div no-border-div"> </div>';
							}*/
							$rattingData = self::wur_ratting_view_point_per($rattinfDataRat, $rattinfDataRange);
						} elseif($itemRattingStyle == 'percentage') {
							/*if($ratCount != 0) {
								echo '<div class="border-div no-border-div"> </div>';
							}*/
							$rattingData = self::wur_ratting_view_percentange_per($rattinfDataRat, $rattinfDataRange);
						} elseif($itemRattingStyle == 'pie') {
							$rattingData = self::wur_ratting_view_pie_per($rattinfDataRat, $rattinfDataRange);
						} else {
							/*if($ratCount != 0) {
								echo '<div class="border-div "> </div>';
							}*/
							$rattingData = self::wur_ratting_view_star_point($rattinfDataRat, $rattinfDataRange);
						}
						if($itemRattingStyle == 'pie') {
							$rattinfDataName = substr($rattinfDataName, 0, 11);
						}
						?>
                        <div class="xs-overview-data xs-overview-<?php echo esc_attr($itemRattingStyle) ?>">
                            <div class="data-rat-label"><?php echo esc_html($rattinfDataName); ?> </div>
                            <div class="data-rat-label-range"><?php echo esc_html($rattinfDataRat); ?>
                                / <?php echo esc_html($rattinfDataRange); ?> </div>
                            <div class="data-rat"><?php echo wp_kses($rattingData, \WurReview\App\Settings::kses(null, true)); ?> </div>

                        </div>
						<?php if($itemRattingStyle != 'pie') { ?>
                            <div style="clear:both;"></div>
						<?php } ?>
						<?php
						$ratCount++;
						$totalNumberSumRat   += $rattinfDataRat;
						$totalNumberSumRange += $rattinfDataRange;
					}
				endforeach;
				if($ratCount > 0) {
					$avgRat = round(($totalNumberSumRat / $ratCount), 1);
				}
				?>
				<?php if(isset($return_data_overview->overview->summary->enable)): ?>
                    <div class="overview-summary">
                        <h3> <?php echo esc_html__('Summary', 'wp-ultimate-review'); ?></h3>
                        <p><?php echo esc_html($itemRattingSummary); ?></p>
                    </div>
				<?php endif; ?>
            </div>
			<?php if(isset($return_data_overview->overview->average->enable)) { ?>
                <div class="xs-review-overview-list-right custom-rat">
                    <div class="total_overview_rattings">
                        <span class="total_rattings_review"> <?php echo esc_html($avgRat); ?>  </span> <br/>

						<?php

						$condition = isset($return_data_display_setting['overview_avg_rating_if']) ? floatval($return_data_display_setting['overview_avg_rating_if']) : 3.75;

						if(!empty($return_data_display_setting['overview_avg_rating_superb']) && $condition <= floatval($avgRat)) :

							?>
                            <strong><?php echo esc_html($return_data_display_setting['overview_avg_rating_superb']); ?></strong><?php
						endif;
						?>

                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
<?php } ?>

<?php
$review_list = isset($return_data_display_setting['review_list']['enable']) ? 'Yes' : 'No';

if(!is_array($return_data_display_setting)) {
	$review_list = 'Yes';
}

if($review_list == 'Yes' || isset($return_data_overview->overview->ratting->enable)) {

	/*Start avarage ratting of user review*/
	$overViewTotal      = 0;
	$totalRattingsCount = 0;
	$rattingRatting     = 5;
	$overViewArray      = [];
	$num_of_reviews     = 0;
	$avarage            = 0;
	$score_limit        = 0;

	if($the_queryTotal->have_posts()) {

		$num_of_reviews = empty($the_queryTotal->found_posts) ? 0 : $the_queryTotal->found_posts;
		$rev = 0;

		while($the_queryTotal->have_posts()) {
			$the_queryTotal->the_post();
			$metaReviewID = get_the_ID();

			$metaDataJson = get_post_meta($metaReviewID, 'xs_public_review_data', false);
			if(is_array($metaDataJson) AND sizeof($metaDataJson) > 0) {
				$getMetaData = Helper::_decode_json_unicode(end($metaDataJson));
			} else {
				$getMetaData = (object) [];
			}
			//print_r( $getMetaData );
			$xs_reviwer_rattingOver = isset($getMetaData->xs_reviwer_ratting) ? $getMetaData->xs_reviwer_ratting : '0';
			$reviwerStyleLimitOver  = isset($getMetaData->review_score_limit) ? $getMetaData->review_score_limit : '5';

			$overViewArray['xs_reviwer_ratting'][] = $xs_reviwer_rattingOver;
			$overViewArray['review_score_limit'][] = $reviwerStyleLimitOver;

			$avarage += Helper::avarage_loop($xs_reviwer_rattingOver, $reviwerStyleLimitOver);
			
			if($reviwerStyleLimitOver > $score_limit) {
				$score_limit = $reviwerStyleLimitOver;
			}

			$rev++;
		}

		$avarage = Helper::avarage_final($rev, $score_limit, $avarage);

		$rattingRatting = max(isset($overViewArray['review_score_limit']) ? $overViewArray['review_score_limit'] : []);
		$rattingRatting = 5;
		// count same values in array. Return array by unique.
		$arrayCountValues = array_count_values(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);

		$totalRattingsSum   = array_sum(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);
		$totalRattingsCount = count(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);

		$overViewTotal = round(($totalRattingsSum / $totalRattingsCount), 2);
		wp_reset_postdata();
	}
	/*end avarage ratting of user review*/
	?>
    <div class="xs-review-box view-review-list" id="xs-review-box">
        <h3 class="total-reivew-headding"> 
			<?php 
				echo esc_html($num_of_reviews);
				printf(esc_html(_nx(' Review', ' Reviews', $num_of_reviews, 'no of reviews', 'wp-ultimate-review')));
				
				if(isset($global_setting['review_user_average']) && $global_setting['review_user_average'] == 'Yes'):
					echo ' ( ' . esc_html(round($avarage, 1)) . esc_html__(' out of ', 'wp-ultimate-review') . esc_html($score_limit) . ' )'; 
				endif;
			?>
		</h3>
        <div class="xs-review-box-item">
			<?php

			if($review_list == 'Yes'):
			?>
            <div class="xs-review-media <?php if(!isset($return_data_overview->overview->ratting->enable)) {
				echo esc_attr('review-full');
			} ?>">
				<?php
				$postCount = 1;
				if($the_query->have_posts()) {
				while($the_query->have_posts()) {
				$the_query->the_post();
				$metaReviewID = get_the_ID();
				$metaDataJson = get_post_meta($metaReviewID, 'xs_public_review_data', false);
				if(is_array($metaDataJson) AND sizeof($metaDataJson) > 0) {
					$getMetaData = Helper::_decode_json_unicode(end($metaDataJson));
				} else {
					$getMetaData = [];
				}
				if($postCount != 1) {
					echo '<div class="border-div"> </div>';
				}

				?>
                <div class="xs-reviewer-details">
					<?php

					// reviwer image
					$enable_profile = isset($return_data_display_setting['form']['xs_reviwer_profile_image_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_profile = 'Yes';
					}

					if($enable_profile == 'Yes'):
						echo '<div class="review-reviwer-image-section">';

						$profileImage = isset($getMetaData->xs_post_author) ? $getMetaData->xs_post_author : 0;
						if(strlen($profileImage) > 0) {
							?>
                            <div class="xs-reviewer-author-image">
								<?php echo get_avatar($profileImage); ?>
                            </div>
							<?php
						}
						echo '</div>';
					endif;

					// reviwer details
					echo '<div class="review-reviwer-info-section">';
					// reviwer name
					$enable_reviwer_name = isset($return_data_display_setting['form']['xs_reviwer_name_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_reviwer_name = 'Yes';
					}
					if($enable_reviwer_name == 'Yes'):
						if(isset($getMetaData->xs_reviwer_name) AND strlen($getMetaData->xs_reviwer_name) > 0): ?>
                            <div class="xs-reviewer-author">
                                <span class="xs_review_name"> <?php echo esc_html($getMetaData->xs_reviwer_name); ?> </span>
								<?php
								if((isset($return_data_display_setting['form']['xs_reviwer_email_data']['display']['enable']) && $return_data_display_setting['form']['xs_reviwer_email_data']['display']['enable'] == 'Yes')):
									if(isset($getMetaData->xs_reviwer_email) AND strlen($getMetaData->xs_reviwer_email) > 0):
										?>
                                        <span class="xs_review_email"> - <?php echo esc_html($getMetaData->xs_reviwer_email); ?> </span>
										<?php
									endif;
								endif;
								?>
                            </div>
						<?php endif;
					endif;
					// author website
					$enable_website = isset($return_data_display_setting['form']['xs_reviwer_website_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						//$enable_website = 'Yes';
					}
					if($enable_website == 'Yes') :
						if(isset($getMetaData->xs_reviwer_website) AND strlen($getMetaData->xs_reviwer_website) > 0): ?>
                            <div class="xs-reviewer-website">
                                <span> <?php echo esc_html($getMetaData->xs_reviwer_website); ?> </span>
                            </div>
						<?php endif;
					endif;
					// ratting
					$enable_ratting = isset($return_data_display_setting['form']['xs_reviwer_ratting_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_ratting = 'Yes';
					}
					if($enable_ratting == 'Yes'):
						if(isset($getMetaData->xs_reviwer_ratting) AND $getMetaData->xs_reviwer_ratting > 0):
							$reviwerStyleLimit = isset($getMetaData->review_score_limit) ? $getMetaData->review_score_limit : '5';
							$reviwerScoreStyle = isset($getMetaData->review_score_style) ? $getMetaData->review_score_style : 'star';
							if($reviwerScoreStyle == 'star') {
								echo wp_kses(self::wur_ratting_view_star_point($getMetaData->xs_reviwer_ratting, $reviwerStyleLimit), \WurReview\App\Settings::kses(null, true));
							} elseif($reviwerScoreStyle == 'point') {
								echo wp_kses(self::wur_ratting_view_point_per($getMetaData->xs_reviwer_ratting, $reviwerStyleLimit), \WurReview\App\Settings::kses(null, true));
							} elseif($reviwerScoreStyle == 'percentage') {
								echo wp_kses(self::wur_ratting_view_percentange_per($getMetaData->xs_reviwer_ratting, $reviwerStyleLimit), \WurReview\App\Settings::kses(null, true));
							} elseif($reviwerScoreStyle == 'pie') {
								echo wp_kses(self::wur_ratting_view_pie_per($getMetaData->xs_reviwer_ratting, $reviwerStyleLimit), \WurReview\App\Settings::kses(null, true));
							} else {
								echo wp_kses(self::wur_ratting_view_star_point($getMetaData->wur_reviwer_ratting, $reviwerStyleLimit), \WurReview\App\Settings::kses(null, true));
							}
						endif;
					endif;
					// ratting date 
					$enable_date = isset($return_data_display_setting['form']['post_date_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_date = 'Yes';
					}
					if($enable_date == 'Yes'):
						if(isset($post->post_date) AND strlen($post->post_date) > 2): ?>
                            <div class="xs-review-date">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                                      itemprop="datePublished"><?php echo esc_html(get_the_date('F d, Y')); ?></time>
                            </div>
						<?php endif;
					endif;
					// ratting title
					$enable_title = isset($return_data_display_setting['form']['xs_reviw_title_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_title = 'Yes';
					}
					if($enable_title == 'Yes'):
						if(isset($getMetaData->xs_reviw_title) AND strlen($getMetaData->xs_reviw_title) > 0):

							?>
                            <div class="xs-review-title">
                                <h3> <?php echo esc_html($getMetaData->xs_reviw_title); ?> </h3>
                            </div>
						<?php endif;
					endif;
					$enable_summery = isset($return_data_display_setting['form']['xs_reviw_summery_data']['display']['enable']) ? 'Yes' : 'No';
					if(!is_array($return_data_display_setting)) {
						$enable_summery = 'Yes';
					}
					if($enable_summery == 'Yes'):
						if(isset($getMetaData->xs_reviw_summery) AND strlen($getMetaData->xs_reviw_summery) > 0):

							?>
                            <div class="xs-review-summery">
                                <p> <?php echo esc_html($getMetaData->xs_reviw_summery); ?> </p>
                            </div>
						<?php endif;
					endif;

					?>
                </div>
            </div>
		<?php
		$postCount++;
		}
		?>
            <div class="xs-review-pagination">
				<?php
				$this->wur_review_pagination($paged, $the_query->max_num_pages);
				?>
            </div>
		<?php
		wp_reset_postdata();
		}
		?>
        </div>
		<?php endif; ?>
		<?php
		if(isset($return_data_overview->overview->ratting->enable)):?>
            <div class="total_overview_rattings_value">
				<?php
				echo wp_kses(self::wur_ratting_view_star_point(esc_html($overViewTotal), $rattingRatting), \WurReview\App\Settings::kses(null, true));
				?>
                <span> (<?php echo esc_html($overViewTotal); ?>) </span>
                <div class="total_overview_rattings_text"> <?php echo esc_html($totalRattingsCount); ?><?php echo esc_html__('Votes', 'wp-ultimate-review'); ?></div>
            </div>
		<?php endif; ?>
    </div>
    </div>

	<?php
}
// end enable review list

$viewRattingPage = 'Yes';
if((isset($return_data_global_setting['require_login']) ? $return_data_global_setting['require_login'] : 'No') == 'Yes') {
	if(is_user_logged_in()) {
		$viewRattingPage = 'Yes';
	} else {
		$viewRattingPage = 'No';
	}
}
if($viewRattingPage == 'Yes'):
	?>
    <form action="<?php echo esc_url(get_permalink($post->ID)); ?>" name="xs_review_form_public_data" method="post"
          id="xs_review_form_public_data">

        <div class="xs-review-box public-xs-review-box" id="xs-review-box">
            <h3 class="write-reivew-headding">
				<?php echo esc_html__('Write a Review ', 'wp-ultimate-review'); ?></h3>
			<?php
			if(isset($_SESSION['xs_review_message']) AND strlen(sanitize_text_field(wp_unslash($_SESSION['xs_review_message']))) > 4 && isset($_POST['xs_review_form_public_data'])){ //phpcs:ignore  (WordPress.Security.NonceVerification.Missing
			?>
            <div class="review_message_show">
                <p><?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION['xs_review_message'])));
					unset($_SESSION['xs_review_message']); ?></p>
            </div>
            <div class="wur-review-fields">
				<?php
				}
				if(is_array($this->controls) AND sizeof($this->controls) > 0) {

					$showTextFiledWIthOutLogin = ['xs_reviwer_name', 'xs_reviwer_email'];

					foreach($this->controls AS $metaKey => $metaValue):

						// CHeck filed enable
						$checkEnable = (isset($return_data_display_setting['form'][$metaKey]) && $return_data_display_setting['form'][$metaKey] == 'Yes') ? 'Yes' : 'No';
						if(!is_array($return_data_display_setting)) {
							$checkEnable = 'Yes';
						}

						$metaData     = '';
						$displayFiled = '';
						// check login user or not

						if(in_array($metaKey, $showTextFiledWIthOutLogin)) {
							if($checkEnable == 'Yes') {
								$displayFiled = 'display:block;';
								$checkEnable  = 'Yes';
							} else {
								$displayFiled = 'display:none;';
							}

							if(is_user_logged_in()) {
								// current user information
								$current_user = wp_get_current_user();
								if($metaKey == 'xs_reviwer_name') {
									$metaData = (isset($current_user->display_name) && strlen($current_user->display_name) > 0) ? $current_user->display_name : $current_user->first_name . ' ' . $current_user->last_name;
								} elseif($metaKey == 'xs_reviwer_email') {
									$metaData = isset($current_user->user_email) ? $current_user->user_email : '';
								}
							} else {
								$displayFiled = 'display:block;';
								$checkEnable  = 'Yes';
							}
						}


						// check enable filed
						if($checkEnable === 'Yes') {

							// input type, Example: text, checkbox, radio
							$inputType = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('type', $metaValue)) ? $metaValue['type'] : 'text';

							// input name
							$inputName = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('name', $metaValue)) ? $metaValue['name'] : $metaKey;

							// input id
							$inputId = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('id', $metaValue)) ? $metaValue['id'] : $metaKey;

							// input class
							$inputClass = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('class', $metaValue)) ? $metaValue['class'] : $metaKey;

							// Input Ttitle
							$inputTitle   = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('title_name', $metaValue)) ? $metaValue['title_name'] : '';
							$inputRequire = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('require', $metaValue)) ? $metaValue['require'] : 'No';

							// dynamic title
							$inputTitle = (isset($return_data_display_setting['form'][$metaKey . '_data']['label']['name']) && $return_data_display_setting['form'][$metaKey . '_data']['label']['name'] != '') ? $return_data_display_setting['form'][$metaKey . '_data']['label']['name'] : $inputTitle;

							// set require option in fileds
							$requireSet = '';
							if($inputRequire === 'Yes') {
								//$inputTitle .= '<em>(Required)</em> ';
								$requireSet = 'required';
							}
							// Input Options
							$inputOptions = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('options', $metaValue)) ? $metaValue['options'] : [];

							if($metaKey == 'xs_reviwer_ratting') {
								$review_score_style       = isset($return_data_global_setting['review_score_style']) ? $return_data_global_setting['review_score_style'] : 'star';
								$review_score_style_input = isset($return_data_global_setting['review_score_input']) ? $return_data_global_setting['review_score_input'] : 'star';

								$review_score_limit = isset($return_data_global_setting['review_score_limit']) ? $return_data_global_setting['review_score_limit'] : 5;

								if(in_array($review_score_style, ['percentage', 'pie'])):
									$review_score_style_input = 'slider';
								endif;
								?>
                                <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
                                     style="<?php echo esc_attr($displayFiled); ?>">
									<?php if(in_array($review_score_style_input, array(
										'star',
										'square',
										'movie',
										'bar',
										'pill',
									))): ?>
                                        <div class="xs-review-rating-stars text-center">
                                            <ul id="xs_review_stars">
												<?php for($ratting = 1; $ratting <= $review_score_limit; $ratting++): ?>
                                                    <li class="star-li <?php echo esc_attr($review_score_style_input); ?>  <?php if($ratting == 1) {
														echo esc_html('selected');
													} ?>" data-value="<?php echo esc_attr(intval($ratting)); ?>">
														<?php if($review_score_style_input == 'star') { ?>
                                                            <i class="xs-star dashicons-before dashicons-star-filled"></i>
														<?php } else {
															echo '<span>' . esc_html($ratting) . '<span>';
														} ?>
                                                    </li>
												<?php endfor; ?>
                                            </ul>
                                            <div id="review_data_show"></div>
                                            <input type="hidden" id="ratting_review_hidden"
                                                   name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
                                                   value="1" <?php echo esc_attr($requireSet); ?> />
                                        </div>
									<?php endif;
									if($review_score_style_input == 'slider'):?>
                                        <div class="xs-review-rating-slider text-center">
                                            <div class="xs-slidecontainer">
                                                <input type="range" min="1"
                                                       max="<?php echo intval($review_score_limit); ?>" value="1"
                                                       name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
                                                       class="xs-slider-range" id="xs_review_range">

                                            </div>
                                            <div id="review_data_show"></div>
                                        </div>
									<?php endif;

									?>
                                </div>
								<?php
							} elseif($inputType == 'select' && $metaKey != 'xs_reviwer_ratting') {
								?>
                                <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
                                     style="<?php echo esc_html($displayFiled); ?>">
                                    <label for="<?php echo esc_attr($inputId); ?>"> <?php echo esc_html($inputTitle) ?>
                                        <select name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
                                                id="<?php echo esc_attr($inputId); ?>"
                                                class="widefat <?php echo esc_attr($inputClass); ?>" <?php echo esc_attr($requireSet); ?> >
											<?php
											if(is_array($inputOptions) AND sizeof($inputOptions) > 0):
												foreach($inputOptions AS $optionsKey => $optionsValue):
													?>
                                                    <option value="<?php echo esc_html($optionsKey); ?>" <?php echo (isset($optionsKey) && $optionsKey == $metaData) ? 'selected' : '' ?> > <?php echo esc_html($optionsValue); ?> </option>
													<?php
												endforeach;
											endif;
											?>
                                        </select>
                                    </label>
                                </div>
								<?php
							} elseif(($inputType == 'radio' OR $inputType == 'checkbox') && $metaKey != 'xs_reviwer_ratting') {
								?>
                                <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
                                     style="<?php echo esc_attr($displayFiled); ?>">
                                    <label for="<?php echo esc_attr($inputId); ?>"> <?php echo esc_html($inputTitle) ?></label><br/>
									<?php
									if(is_array($inputOptions) AND sizeof($inputOptions) > 0):
										foreach($inputOptions AS $optionsKey => $optionsValue):
											?>
                                            <label for="<?php echo esc_attr($optionsKey); ?>">
                                                <input type="<?php echo esc_attr($inputType); ?>"
                                                       id="<?php echo esc_attr($optionsKey); ?>"
                                                       class="widefat <?php echo esc_attr($inputClass); ?>"
                                                       name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
                                                       value="<?php echo esc_html($optionsKey) ?>" <?php echo (isset($optionsKey) && $optionsKey == $metaData) ? 'checked' : '' ?> <?php echo esc_attr($requireSet); ?> />
												<?php echo esc_html($optionsValue) ?>
                                            </label>
											<?php
										endforeach;
									endif;
									?>
                                </div>
								<?php
							} elseif($inputType == 'textarea' && $metaKey != 'xs_reviwer_ratting') {
								?>
                                <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
                                     style="<?php echo esc_attr($displayFiled); ?>">
                                    <!-- <label for="<?php echo esc_attr($inputId); ?>" > <?php echo esc_html($inputTitle) ?> -->
                                    <textarea id="<?php echo esc_attr($inputId); ?>"
                                              class="widefat <?php echo esc_attr($inputClass); ?>"
                                              name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]" <?php echo esc_attr($requireSet); ?>
                                              placeholder="<?php echo esc_attr($inputTitle); ?>"><?php echo esc_attr($metaData); ?></textarea>
                                    <!--</label>-->
                                </div>
								<?php
							} else {
								?>
                                <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
                                     style="<?php echo esc_attr($displayFiled); ?>">
                                    <!-- <label for="<?php echo esc_attr($inputId); ?>" > <?php echo esc_html($inputTitle) ?> -->
                                    <input type="<?php echo esc_attr($inputType); ?>"
                                           placeholder="<?php echo esc_html($inputTitle); ?>"
                                           id="<?php echo esc_attr($inputId); ?>"
                                           class="widefat <?php echo esc_attr($inputClass); ?>"
                                           name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
                                           value="<?php echo esc_attr($metaData); ?>" <?php echo esc_attr($requireSet); ?> />
                                    <!--</label>-->
                                </div>
							<?php }
						}
					endforeach;
				}
					//this hooks called from pro version
					do_action('wur_recaptcha_show');
				?>
				<?php wp_nonce_field('meta-box-review-nonce'); //phpcs:ignore ?>
                <div class="xs-review xs-save-button">
                    <button type="submit" name="xs_review_form_public_data"
                            class="xs-btn primary"><?php echo esc_html__('Submit Review', 'wp-ultimate-review'); ?></button>
                </div>

				<?php if(isset($_SESSION['xs_review_message']) AND strlen(sanitize_text_field(wp_unslash($_SESSION['xs_review_message']))) > 4 && isset($_POST['xs_review_form_public_data']) && wp_verify_nonce('_wpnonce')) : ?>
            </div>
		<?php endif; ?>
        </div>
    </form>
<?php endif; ?>
