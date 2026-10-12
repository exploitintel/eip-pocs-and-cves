<?php

use WurReview\Helper\Helper;

$review_heading = empty($post_review_meta->overview->heading) ? __('Overview', 'wp-ultimate-review') : $post_review_meta->overview->heading;

$author_review_enable = false;
if(isset($global_setting['review_author_average']) && $global_setting['review_author_average'] == 'Yes') {
	$author_review_enable = true;
}

?>

    <div class="xs-review-box view-review-list overview-xs-review " id="xs-author-review-box">

        <h3 class="total-reivew-headding"><?php echo esc_html($review_heading); ?></h3>

        <div class="xs-reviewer-details">
			<?php

			$itemRatting  = isset($post_review_meta->overview->item) ? $post_review_meta->overview->item : [];
			$totalRatting = count($itemRatting);

			$itemRattingStyle = isset($post_review_meta->overview->style) ? $post_review_meta->overview->style : 'star';

			$itemRattingSummary = isset($post_review_meta->overview->summary->data) ? $post_review_meta->overview->summary->data : '';
			$arrayCountValues   = [];

			$totalRattingsSum   = 1;
			$totalRattingsCount = 1;

			$overViewTotal = 1;

			$avarage = 0;
			
			$score_limit = 0;

			?>
            <div class="xs-review-overview-list <?php echo $author_review_enable === true ?  : 'left-full'; ?> custom-rat ">
				<?php
				$ratCount             = 0;
				$totalNumberSumRange  = 0;
				$rev = 0;

				foreach($itemRatting AS $ratValue):

					$rattinfDataName = $ratValue->name;
					$rattinfDataRat   = intval($ratValue->ratting);
					$rattinfDataRange = intval($ratValue->rat_range);
					
					$avarage += Helper::avarage_loop($rattinfDataRat, $rattinfDataRange);

					if($rattinfDataRange > $score_limit) {
						$score_limit = $rattinfDataRange;
					}

					$item_name  = $ratValue->name;
					$item_rate  = $ratValue->ratting;
					$rate_range = $ratValue->rat_range;

					if(!empty($item_name) && $item_rate > 0):


						?>
                        <div class="xs-overview-data xs-overview-<?php echo esc_attr($itemRattingStyle) ?>">
							<?php if($itemRattingStyle != 'pie') : ?>
                            	<div class="data-rat-label"><?php echo esc_html($item_name); ?> </div>
							<?php endif; ?>
                            <div class="data-rat-label-range">
								<?php
								if($itemRattingStyle == 'percentage'){
									echo round(($item_rate * 100) / $rate_range) ?> / <?php echo 100 .' (%)'; //phpcs:ignore
								} else {
									echo $item_rate; ?> / <?php echo $rate_range;//phpcs:ignore
								} ?>
                            </div>
                            <div class="data-rat">

								<?php

								if($itemRattingStyle == 'star') :

									/*if($ratCount != 0) { */?><!--
                                        <div class="border-div "></div>--><?php
/*									}*/

									echo wp_kses(self::wur_ratting_view_star_point($item_rate, $rate_range), \WurReview\App\Settings::kses(null, true));

                                elseif($itemRattingStyle == 'point'):

									if($ratCount != 0) {
										echo '<div class="border-div no-border-div"> </div>';
									}

									echo wp_kses(self::wur_ratting_view_point_per($item_rate, $rate_range), \WurReview\App\Settings::kses(null, true));

                                elseif($itemRattingStyle == 'percentage'):

									if($ratCount != 0) {
										echo '<div class="border-div no-border-div"> </div>';
									}

									echo wp_kses(self::wur_ratting_view_percentange_per($item_rate, $rate_range), \WurReview\App\Settings::kses(null, true));

                                elseif($itemRattingStyle == 'pie'):

	                                ?>

                                    <div class="wur-rat-cont">
										<div class="wur-pie-data">
											<p class="data-rat-label"><?php echo esc_html($item_name); ?> </p>
											<p class="data-pie-rating"><?php echo esc_html($ratValue->ratting); ?></p>
										</div>
                                        <canvas class="wur_pie"
                                                width="100"
                                                height="100"
                                                data-base="<?php echo esc_attr($ratValue->rat_range) ?>"
                                                data-rating="<?php echo esc_attr($ratValue->ratting) ?>">

                                        </canvas>

                                    </div>

	                                <?php

								else:

								/*	if($ratCount != 0) { */?><!--
                                        <div class="border-div "></div>--><?php
/*									}*/

									echo wp_kses(self::wur_ratting_view_star_point($item_rate, $rate_range), \WurReview\App\Settings::kses(null, true));
								endif;

								?>

                            </div>
                        </div>

						<?php

						if($itemRattingStyle != 'pie') { ?>

                            <div style="clear:both;"></div><?php
						}

						$ratCount++;
					endif;
						$rev++;
				endforeach;

				$avarage = Helper::avarage_final($rev, $score_limit, $avarage);
				
				?>

            </div>



			<?php

			if($author_review_enable === true) { ?>
                <div class="xs-review-overview-list-right custom-rat">
                    <div class="total_overview_rattings">
                        <span class="total_rattings_review"> <?php echo esc_html(round($avarage)); ?>  </span>
                        <br/>

						<?php

						$condition = isset($display_setting['overview_avg_rating_if']) ? floatval($display_setting['overview_avg_rating_if']) : 3.75;

						if(!empty($display_setting['overview_avg_rating_superb']) && $condition <= floatval($avarage)) :

							?>
                            <strong><?php echo esc_html($display_setting['overview_avg_rating_superb']); ?></strong><?php
						endif;
						?>

                    </div>
                </div>
			<?php } ?>
        </div>


		<?php

		if(isset($post_review_meta->overview->summary->enable)): ?>
		    <div class="overview-summary">
			    <h3><?php echo esc_html__('Summary', 'wp-ultimate-review'); ?></h3>
			    <p><?php echo esc_html($itemRattingSummary); ?></p>
		    </div>
		<?php

		endif; ?>

    </div>

<?php
