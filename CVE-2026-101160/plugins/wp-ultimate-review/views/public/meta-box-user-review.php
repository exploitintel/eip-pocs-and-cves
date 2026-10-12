<?php

use WurReview\Helper\Helper;
use WurReview\App\Application;

defined('ABSPATH') || exit;

//todo to - need more time to reduce the number of loop & query.


$showPostNo = isset($display_setting['review_show_per']) ? $display_setting['review_show_per'] : 10;
$likeData   = '"xs_post_id":"' . $this->getPostId . '"';
$paged      = isset($_GET['review_page']) ? sanitize_text_field(wp_unslash($_GET['review_page'])) : 1; //phpcs:ignore WordPress.Security.NonceVerification.Recommended


$main_args = [
	'post_type'  => $this->post_type,
	'meta_query' => [
		[
			'key'     => 'xs_public_review_data',
			'value'   => '' . $likeData . '',
			'compare' => 'LIKE',
		],
	],
	'orderby'    => [
		'post_date' => 'DESC',
	],
];

$review_args                   = $main_args;
$review_args['posts_per_page'] = $showPostNo;
$review_args['paged']          = $paged;

$the_queryTotal = new \WP_Query($main_args);
$the_query      = new \WP_Query($review_args);


$content_meta_key = 'xs_submit_review_data';
$review_list      = 'Yes';


if($review_list == 'Yes' || isset($post_review_meta->overview->ratting->enable)) {

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


		/**
		 * Looping through every user review
		 *
		 */
		$rev = 0;

		while($the_queryTotal->have_posts()) {

			$the_queryTotal->the_post();
			$metaReviewID = get_the_ID();

			$getMetaData = \WurReview\App\Wur_Settings::get_xs_post_meta($metaReviewID, 'xs_public_review_data');

			$xs_reviwer_rattingOver = isset($getMetaData->xs_reviwer_ratting) ? intval($getMetaData->xs_reviwer_ratting) : 0;
			$reviwerStyleLimitOver  = isset($getMetaData->review_score_limit) ? intval($getMetaData->review_score_limit) : 5;

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

		$arrayCountValues = array_count_values(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);

		$totalRattingsSum   = array_sum(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);
		$totalRattingsCount = count(isset($overViewArray['xs_reviwer_ratting']) ? $overViewArray['xs_reviwer_ratting'] : []);

		$overViewTotal = round(($totalRattingsSum / $totalRattingsCount), 2);

		wp_reset_postdata();
	}

	?>
    <div class="xs-review-box view-review-list" id="xs-user-review-box">

        <h3 class="total-reivew-headding">
			<?php echo esc_html($num_of_reviews);

			printf(esc_html(_nx(' Review', ' Reviews', $num_of_reviews, 'no of reviews', 'wp-ultimate-review')));

			if(isset($global_setting['review_user_average']) && $global_setting['review_user_average'] == 'Yes'):

				echo ' ( ' . esc_html(round($avarage, 1)) . esc_html__(' out of ', 'wp-ultimate-review') . esc_html($score_limit) . ' )';

			endif; ?>
        </h3>
        <div class="xs-review-box-item">

            <div
                    class="xs-review-media <?php echo empty($post_review_meta->overview->ratting->enable) ? 'review-full' : ''; ?>">

				<?php

				if($the_query->have_posts()) {

					$divider = false;

					while($the_query->have_posts()) {

						$the_query->the_post();
						$metaReviewID = get_the_ID();

						$metaDataJson = get_post_meta($metaReviewID, 'xs_public_review_data', false);

						$getMetaData = \WurReview\App\Wur_Settings::get_xs_post_meta($metaReviewID, 'xs_public_review_data');

						if($divider) {
							echo '<div class="border-div"> </div>';
						}

						$divider = true;

						?>

                        <!-- every review-->
                        <div class="xs-reviewer-details">

							<?php

							if($wur_settings->is_reviewer_profile_enabled() || empty($wur_settings->getDisplaySettings())):

								$profileImage = isset($getMetaData->xs_post_author) ? $getMetaData->xs_post_author : 0; ?>

                                <div class="review-reviwer-image-section">

									<?php

									if(!empty($profileImage)) {
										?>
                                        <div class="xs-reviewer-author-image">
											<?php echo get_avatar($profileImage); ?>
                                        </div>
										<?php
									}

									?>

                                </div> <?php

							endif;

							?>

                            <div class="review-reviwer-info-section">

								<?php

								if($wur_settings->is_reviewer_name_enabled() || empty($wur_settings->getDisplaySettings())):

									if(!empty($getMetaData->xs_reviwer_name)): ?>
                                        <div class="xs-reviewer-author">
											<span
                                                    class="xs_review_name"> <?php echo esc_html($getMetaData->xs_reviwer_name); ?> </span>
											<?php
											if($wur_settings->is_reviewer_email_enabled()):
												if(!empty($getMetaData->xs_reviwer_email)):
													?>
                                                    <span
                                                            class="xs_review_email"> - <?php echo esc_html($getMetaData->xs_reviwer_email); ?> </span>
												<?php
												endif;
											endif;
											?>
                                        </div>
									<?php
									endif;
								endif;


								if($wur_settings->is_reviewer_website_enabled() || empty($wur_settings->getDisplaySettings())) :
									if(!empty($getMetaData->xs_reviwer_website)): ?>
                                        <div class="xs-reviewer-website">
                                            <span> <?php echo esc_html($getMetaData->xs_reviwer_website); ?> </span>
                                        </div>
									<?php endif;
								endif;


								if($wur_settings->is_reviewer_rating_enabled() || empty($wur_settings->getDisplaySettings())):
									if(!empty($getMetaData->xs_reviwer_ratting)):
										$reviwerStyleLimit = isset($getMetaData->review_score_limit) ? $getMetaData->review_score_limit : '5';
										$reviwerScoreStyle = isset($wur_settings->getGlobalSettings()['review_score_style']) ? $wur_settings->getGlobalSettings()['review_score_style'] : 'star';
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


								if($wur_settings->is_reviewer_rating_date_enabled() || empty($wur_settings->getDisplaySettings())):
									if(!empty($post->post_date)): ?>
                                        <div class="xs-review-date">
                                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                                                  itemprop="datePublished">
												<?php echo esc_html(get_the_date('F d, Y')); ?>
                                            </time>
                                        </div>
									<?php endif;
								endif;


								if($wur_settings->is_review_title_showing_enabled() || empty($wur_settings->getDisplaySettings())):
									if(!empty($getMetaData->xs_reviw_title)): ?>
                                        <div class="xs-review-title">
                                            <h3> <?php echo esc_html(get_the_title()); ?> </h3>
                                        </div> <?php
									endif;
								endif;


								if($wur_settings->is_review_text_showing_enabled() || empty($wur_settings->getDisplaySettings())):
									if(!empty($getMetaData->xs_reviw_summery)): 
									
										$allowed_tags_for_protected_post = array(
											'form' => array(
												'action' => array(),
												'class'  => array(),
												'method' => array(),
											),
											'p' => array(
												'class' => array(),
											),
											'label' => array(
												'for' => array(),
												'class' => array(),
											),
											'input' => array(
												'name' => array(),
												'value' => array(),
												'id' => array(),
												'class' => array(),
												'type' => array(),
											),
										);
									?>
                                        <div class="xs-review-summery">
                                            <p> <?php echo wp_kses(get_the_content(), $allowed_tags_for_protected_post); ?> </p>
                                        </div>
									<?php endif;
								endif;

								?>
                            </div>

                        </div>

						<?php

					} ?>

                    <div class="xs-review-pagination">
						<?php
						$this->wur_review_pagination($paged, $the_query->max_num_pages);
						?>
                    </div>

					<?php

					wp_reset_postdata();
				} ?>

            </div>

			<?php

			if(!empty($post_review_meta->user_rating->average->enable)):?>
                <div class="total_overview_rattings_value">
					<?php
					echo wp_kses(self::wur_ratting_view_star_point(esc_html($overViewTotal), $rattingRatting), \WurReview\App\Settings::kses(null, true));
					?>
                    <span> (<?php echo esc_html($overViewTotal); ?>) </span>
                    <div
                            class="total_overview_rattings_text"> <?php echo esc_html($totalRattingsCount); ?><?php echo esc_html__('Votes', 'wp-ultimate-review'); ?></div>
                </div>
			<?php

			endif; ?>

        </div>

    </div>

	<?php
}


$show_user_review_form = true; // from pro we will handle different settings!

if(!empty($global_setting['require_login'])) {

	$show_user_review_form = is_user_logged_in();
}

if($show_user_review_form): ?>

    <form action="<?php echo esc_url(get_permalink($post->ID)); ?>"
          name="xs_review_form_public_data"
          method="post"
          id="xs_review_form_public_data">

        <div class="xs-review-box public-xs-review-box" id="xs-review-box">
            <h3 class="write-reivew-headding">
				<?php echo esc_html__('Write a Review ', 'wp-ultimate-review'); ?>
            </h3>

			<?php
			if(isset($_SESSION['xs_review_message']) && strlen(sanitize_text_field(wp_unslash($_SESSION['xs_review_message']))) > 4 && isset($_POST['xs_review_form_public_data']) ) { //phpcs:ignore  ?>
                <div class="review_message_show">
                    <p><?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION['xs_review_message']))); //phpcs:ignore (WordPress.Security.ValidatedSanitizedInput.MissingUnslash
						unset($_SESSION['xs_review_message']); ?></p>
                </div>
				<?php
			} ?>

            <div class="wur-review-fields"> <?php

                // $this->controls is replaced with and if block is removed as we are getting the array always
                $form_fld = Helper::get_review_form_config();

	            foreach($form_fld as $metaKey => $metaValue):

		            $field_is_enable =  !empty($display_setting) ? !empty($display_setting['form'][$metaKey]) : true;

		            $metaData     = '';
		            $displayFiled = '';
		            $displayFiled = $field_is_enable ? 'display:block;' : 'display:none;';

		            /**
		             * If login required then we are not here yet
		             * If login is not required then we are here
		             * If user is logged in we are here                         *
		             */

		            if(is_user_logged_in()) {

			            $current_user = wp_get_current_user();

			            if($metaKey == 'xs_reviwer_name') {
				            $metaData = (empty($current_user->display_name)) ? $current_user->first_name . ' ' . $current_user->last_name : $current_user->display_name;
			            } elseif($metaKey == 'xs_reviwer_email') {
				            $metaData = isset($current_user->user_email) ? $current_user->user_email : '';
			            }
		            }

		            if($field_is_enable) {

			            // input type, Example: text, checkbox, radio
			            $inputType  = empty($metaValue['type']) ? 'text' : $metaValue['type'];
			            $inputName  = empty($metaValue['name']) ? $metaKey : $metaValue['name'];
			            $inputId    = empty($metaValue['id']) ? $metaKey : $metaValue['id'];
			            $inputClass = empty($metaValue['class']) ? $metaKey : $metaValue['class'];
			            $inputTitle = empty($metaValue['title_name']) ? '' : $metaValue['title_name'];

			            $inputTitle   = empty($display_setting['form'][$metaKey . '_data']['label']['name']) ? $inputTitle : $display_setting['form'][$metaKey . '_data']['label']['name'];
			            $inputOptions = empty($metaValue['options']) ? [] : $metaValue['options'];
			            $requireSet   = empty($metaValue['require']) ? '' : ($metaValue['require'] === 'Yes');


			            if($metaKey == 'xs_reviwer_ratting') {

				            $review_score_style       = isset($global_setting['review_score_style']) ? $global_setting['review_score_style'] : 'star';
				            $review_score_style_input = isset($global_setting['review_score_input']) ? $global_setting['review_score_input'] : 'star';
				            $review_score_limit       = isset($global_setting['review_score_limit']) ? $global_setting['review_score_limit'] : 5;

				           /* if(in_array($review_score_style, ['percentage', 'pie'])):
					            $review_score_style_input = 'slider';
				            endif;*/
				            ?>
				            <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
				                 style="<?php echo esc_attr($displayFiled); ?>">

					            <?php

					            if(in_array($review_score_style_input, [
						            'star',
						            'square',
						            'movie',
						            'bar',
						            'pill',
					            ])):

						            ?>
						            <div class="xs-review-rating-stars text-center">
							            <ul id="xs_review_stars">
								            <?php

								            for($ratting = 1; $ratting <= $review_score_limit; $ratting++): ?>
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
						            <select id="<?php echo esc_attr($inputId); ?>"
						                    name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
						                    class="widefat <?php echo esc_attr($inputClass); ?>" <?php echo esc_attr($requireSet); ?> >
							            <?php
							            if(!empty($inputOptions)):
								            foreach($inputOptions AS $optionsKey => $optionsValue): ?>
									            <option value="<?php echo esc_html($optionsKey); ?>"
										            <?php echo (isset($optionsKey) && $optionsKey == $metaData) ? 'selected' : '' ?> >
										            <?php echo esc_html($optionsValue); ?>
									            </option>
								            <?php
								            endforeach;
							            endif;
							            ?>
						            </select>
					            </label>
				            </div>
				            <?php

			            } elseif(($inputType == 'radio' || $inputType == 'checkbox') && $metaKey != 'xs_reviwer_ratting') { ?>

				            <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
				                 style="<?php echo esc_attr($displayFiled); ?>">

					            <label for="<?php echo esc_attr($inputId); ?>"> <?php echo esc_html($inputTitle) ?></label><br/>
					            <?php
					            if(!empty($inputOptions)):
						            foreach($inputOptions as $optionsKey => $optionsValue): ?>
							            <label for="<?php echo esc_attr($optionsKey); ?>">
								            <input type="<?php echo esc_attr($inputType); ?>"
								                   id="<?php echo esc_attr($optionsKey); ?>"
								                   class="widefat <?php echo esc_attr($inputClass); ?>"
								                   name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
								                   value="<?php echo esc_html($optionsKey) ?>" <?php echo ($optionsKey == $metaData) ? 'checked' : '' ?> <?php echo esc_attr($requireSet); ?> />
								            <?php echo esc_html($optionsValue) ?>
							            </label> <?php
						            endforeach;
					            endif; ?>
				            </div>
				            <?php

			            } elseif($inputType == 'textarea' && $metaKey != 'xs_reviwer_ratting') { ?>

				            <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
				                 style="<?php echo esc_attr($displayFiled); ?>">

                                    <textarea id="<?php echo esc_attr($inputId); ?>"
                                              class="widefat <?php echo esc_attr($inputClass); ?>"
                                              name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]" <?php echo esc_attr($requireSet); ?>
                                              placeholder="<?php echo esc_attr($inputTitle); ?>"><?php echo esc_attr($metaData); ?></textarea>

				            </div> <?php

			            } else { ?>

				            <div class="xs-review xs-<?php echo esc_attr($inputType); ?>"
				                 style="<?php echo esc_attr($displayFiled); ?>">

					            <input type="<?php echo esc_attr($inputType); ?>"
					                   placeholder="<?php echo esc_attr($inputTitle); ?>"
					                   id="<?php echo esc_attr($inputId); ?>"
					                   class="widefat <?php echo esc_attr($inputClass); ?>"
					                   name="<?php echo esc_attr($content_meta_key); ?>[<?php echo esc_attr($inputName); ?>]"
					                   value="<?php echo esc_attr($metaData); ?>" <?php echo esc_attr($requireSet); ?> />

				            </div> <?php

			            }
		            }

	            endforeach;

				//this hooks called from pro version
				do_action('wur_recaptcha_show');
				?>

				<?php wp_nonce_field('meta-box-review-nonce'); //phpcs:ignore?>
                <div class="xs-review xs-save-button">
                    <button type="submit" name="xs_review_form_public_data" class="xs-btn primary">
                        <?php echo esc_html__('Submit Review', 'wp-ultimate-review'); ?>
                    </button>
                </div>

            </div>
        </div>
    </form>

<?php

endif;
