<?php
defined('ABSPATH') || exit;

$active_tab = sanitize_text_field(wp_unslash(isset($_GET["tab"]) ? $_GET["tab"] : 'wur_global_setting')); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div id="xs_settings" class="wur-login-main-wrapper">
	<?php if($message_status == 'show') { ?>
        <div class="admin-page-framework-admin-notice-animation-container">
            <div 0="XS_Social_Login_Settings" id="XS_Social_Login_Settings"
                 class="updated admin-page-framework-settings-notice-message admin-page-framework-settings-notice-container notice is-dismissible"
                 style="margin: 1em 0px; visibility: visible; opacity: 1;">
                <p><?php printf(
					/* translators: %s: settings section name, e.g. "Global Settings" */
					esc_html__('%s data have been updated.', 'wp-ultimate-review'),
					$message_text
				); ?></p>
                <button type="button" class="notice-dismiss"><span
                            class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'wp-ultimate-review'); ?></span>
                </button>
            </div>
        </div>
	<?php } ?>

    <div class="wur-main-header">
        <h1>
            <img src="<?php echo esc_url( WUR_REVIEW_PLUGIN_URL . 'assets/admin/img/icon-title.png' ); ?>" alt="">
            <?php esc_html_e('WP Review Settings', 'wp-ultimate-review'); ?>
        </h1>
    </div>

    <div class="wur-nav-tab-wrapper">
        <ul>
            <li>
                <a href="?post_type=xs_review&page=xs_settings&tab=wur_global_setting"
                   class="nav-tab <?php if($active_tab == 'wur_global_setting') {
					   echo esc_attr('nav-tab-active');
				   } ?>"><?php esc_html_e('Global Settings', 'wp-ultimate-review'); ?></a>
            </li>
            <li>
                <a href="?post_type=xs_review&page=xs_settings&tab=wur_criteria"
                   class="nav-tab <?php if($active_tab == 'wur_criteria') {
					   echo esc_attr('nav-tab-active');
				   } ?>"><?php esc_html_e('Criteria', 'wp-ultimate-review'); ?></a>
            </li>
            <li>
                <a href="?post_type=xs_review&page=xs_settings&tab=wur_display_setting"
                   class="nav-tab <?php if($active_tab == 'wur_display_setting') {
					   echo esc_attr('nav-tab-active');
				   } ?>"><?php esc_html_e('Display Settings', 'wp-ultimate-review'); ?></a>
            </li>
            <li>
                <a href="?post_type=xs_review&page=xs_settings&tab=wur_captcha_setting"
                   class="nav-tab <?php if($active_tab == 'wur_captcha_setting') {
					   echo esc_attr('nav-tab-active');
				   } ?>"><?php esc_html_e('reCaptcha', 'wp-ultimate-review'); ?></a>
            </li>

           <!-- <li>
                <a href="?post_type=xs_review&page=xs_settings&tab=form_settings"
                   class="nav-tab <?php /*if($active_tab == 'form_settings') {
			           echo 'nav-tab-active';
		           } */?>"><?php /*_e('Review Form Settings', 'wp-ultimate-review'); */?></a>
            </li>-->
        </ul>
    </div>


    <div class="xs-settings-section_review">

        <form action="<?php echo esc_url(admin_url() . 'edit.php?post_type=' . $this->post_type . '&page=xs_settings&tab=' . $active_tab); ?>"
               method="post" id="global_setting_review_form">
              <?php wp_nonce_field("global_setting_review_form_nonce");?>

			<?php if($active_tab == 'wur_global_setting') : ?>

                <div class="wur-main-wrapper" id="wur-general-settings">


                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label for="wur_enable_user_review" class="wur-sec-title">
								<?php echo esc_html__('Enable user review', 'wp-ultimate-review'); ?>
                            </label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button"
                                   type="checkbox"
                                   id="wur_enable_user_review"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[user_review]"
                                   value="Yes" <?php echo (!empty($return_data_global_setting['user_review']) && $return_data_global_setting['user_review'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="wur_enable_user_review" class="review_switch_button_label"></label>


                            <label class="wur-review-type-help-label" for="label-test__xs_reviwer_ratting_name">
                                <span class="wur-review-type-help-label--text">
                                    <?php echo esc_html__('If enabled, user will see a review form in post/pages and they will be able to submit that review', 'wp-ultimate-review'); ?>
                                </span>
                                <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
                            </label>

                        </div>
                    </div>

                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label for="wur_enable_author_review" class="wur-sec-title">
								<?php echo esc_html__('Enable author review', 'wp-ultimate-review'); ?>
                            </label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button"
                                   type="checkbox"
                                   id="wur_enable_author_review"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[author_review]"
                                   value="Yes" <?php echo (!empty($return_data_global_setting['author_review']) && $return_data_global_setting['author_review'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="wur_enable_author_review" class="review_switch_button_label"></label>

	                        <label class="wur-review-type-help-label">
                                <span class="wur-review-type-help-label--text">
                                    <?php echo esc_html__('If enabled, post/page author will be able to give review/rating for that post/page', 'wp-ultimate-review'); ?>
                                </span>
		                        <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
	                        </label>

                        </div>
                    </div>

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="reviw_require_login"
                                   class="wur-sec-title"><?php echo esc_html__('Restrict to registered users only ', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="reviw_require_login"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[require_login]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['require_login']) && $return_data_global_setting['require_login'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="reviw_require_login" class="review_switch_button_label"></label>
                        </div>
                    </div>

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_require_approval"
                                   class="wur-sec-title"><?php echo esc_html__('Require admin approval', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="review_require_approval"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[require_approval]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['require_approval']) && $return_data_global_setting['require_approval'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="review_require_approval" class="review_switch_button_label"></label>
                        </div>
                    </div>
                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_author_average"
                                   class="wur-sec-title"><?php echo esc_html__('Enable author average', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="review_author_average"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[review_author_average]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['review_author_average']) && $return_data_global_setting['review_author_average'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="review_author_average" class="review_switch_button_label"></label>
                        </div>
                    </div>
                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_user_average"
                                   class="wur-sec-title"><?php echo esc_html__('Enable user average', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="review_user_average"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[review_user_average]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['review_user_average']) && $return_data_global_setting['review_user_average'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="review_user_average" class="review_switch_button_label"></label>
                        </div>
                    </div>
                    <!-- Single Item -->
                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_user_limit_id"
                                   class="wur-sec-title"><?php echo esc_html__('Per post user review limit', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_text_filed wur-global-input wur-number-input" type="number" required
                                   id="review_user_limit_id" min="1" max="100" step="1"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[review_user_limit]"
                                   value="<?php echo (isset($return_data_global_setting['review_user_limit']) && $return_data_global_setting['review_user_limit'] != '0') ? esc_attr($return_data_global_setting['review_user_limit']) : '1'; ?>">
                        </div>
                    </div>
                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_user_limit_by_id"
                                   class="wur-sec-title"><?php echo esc_html__('Per post user review limit by', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <div class="wur-global-select-wrapper">
                                <select name="<?php echo esc_attr($global_setting_optionKey); ?>[review_user_limit_by]"
                                        id="review_user_limit_by_id" class="wur-global-select">
									<?php
									$selectReviewUserLimtBy = isset($return_data_global_setting['review_user_limit_by']) ? $return_data_global_setting['review_user_limit_by'] : 'email';
                                    ?>
                                    <option <?php echo $selectReviewUserLimtBy == 'email' ? 'selected' : '' ?> value="email"><?php echo esc_html__('Email', 'wp-ultimate-review')?></option>
                                    <option <?php echo $selectReviewUserLimtBy == 'ip' ? 'selected' : '' ?> value="ip"><?php echo esc_html__('Ip Address', 'wp-ultimate-review')?></option>
                                    <option <?php echo $selectReviewUserLimtBy == 'browser' ? 'selected' : '' ?> value="browser"><?php echo esc_html__('Browser', 'wp-ultimate-review')?></option>
                                </select>
                            </div>
                        </div>
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle wur-ip-detection-method-wrapper" style="<?php echo $selectReviewUserLimtBy != 'ip' ? 'display:none;' : ''; ?>">
                        <div class="wur-left-label">
                            <label for="ip_detection_method_id"
                                   class="wur-sec-title"><?php echo esc_html__('IP Detection Method', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <div class="wur-global-select-wrapper">
                                <select name="<?php echo esc_attr($global_setting_optionKey); ?>[ip_detection_method]"
                                        id="ip_detection_method_id" class="wur-global-select">
									<?php
									$selectIpDetectionMethod = isset($return_data_global_setting['ip_detection_method']) ? $return_data_global_setting['ip_detection_method'] : 'remote_addr';
                                    ?>
                                    <option <?php echo esc_attr($selectIpDetectionMethod == 'remote_addr' ? 'selected' : '') ?> value="remote_addr"><?php echo esc_html__('Secure (REMOTE_ADDR only)', 'wp-ultimate-review')?></option>
                                    <option <?php echo esc_attr($selectIpDetectionMethod == 'proxy_headers' ? 'selected' : '') ?> value="proxy_headers"><?php echo esc_html__('Proxy Headers (CloudFlare, X-Forwarded-For)', 'wp-ultimate-review')?></option>
                                </select>
                            </div>
                            <div class="wur-ip-detection-warnings" style="margin-top: 10px;">
                                <div class="wur-ip-warning wur-remote-addr-warning" style="<?php echo esc_attr($selectIpDetectionMethod == 'remote_addr' ? '' : 'display:none;'); ?>">
                                    <label class="wur-review-type-help-label">
                                        <span class="wur-review-type-help-label--text">
                                            <?php echo esc_html__('Recommended: Uses REMOTE_ADDR which cannot be spoofed by clients. This is the most secure option for IP-based rate limiting.', 'wp-ultimate-review'); ?>
                                        </span>
                                        <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
                                    </label>
                                </div>
                                <div class="wur-ip-warning wur-proxy-headers-warning" style="<?php echo esc_attr($selectIpDetectionMethod == 'proxy_headers' ? '' : 'display:none;'); ?>">
                                    <label class="wur-review-type-help-label">
                                        <span class="wur-review-type-help-label--text">
                                            <?php echo esc_html__('⚠ Warning: Proxy headers (X-Forwarded-For, CF-Connecting-IP) can be easily spoofed by attackers to bypass IP-based rate limiting. Only use this if you are behind a trusted proxy infrastructure (e.g., CloudFlare) with proper configuration.', 'wp-ultimate-review'); ?>
                                        </span>
                                        <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_score_style_id"
                                   class="wur-sec-title"><?php echo esc_html__('Review Graph Style', 'wp-ultimate-review'); ?></label>
                        </div>


                        <div class="xs-review-graph-style-field-container">

		                    <?php

		                    $selectReviewScoreStyle = isset($return_data_global_setting['review_score_style']) ? $return_data_global_setting['review_score_style'] : 'star';

		                    foreach($this->review_style AS $reviewStyleKey => $style) {

			                    $checked = ($selectReviewScoreStyle == $reviewStyleKey) ? 'checked' : ''; ?>
                                <div class="xs-review-graph-style-field-box-container">
                                    <input type="radio" id="graphStyle-<?php echo esc_attr($reviewStyleKey) ?>"  name="<?php echo esc_attr($global_setting_optionKey); ?>[review_score_style]" value="<?php echo esc_attr($reviewStyleKey) ?>" <?php echo esc_attr($checked); ?>>
                                    <div class="xs-review-graph-style-field-box">
                                        <label for="graphStyle-<?php echo esc_attr($reviewStyleKey) ?>"> <img src="<?php echo esc_url($style['thumbnail']) ?>" alt=""></label>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                    </div>
                                </div>
			                    <?php
		                    }
		                    ?>

                        </div>


                    </div><!-- Single Item -->

                    <?php

                    if(did_action('wur_review_pro/plugin_loaded')) : ?>

                        <div class="wur-single-item wur-single-item-middle">
                            <div class="wur-left-label">
                                <label for="review_score_style_id" class="wur-sec-title">
	                                <?php echo esc_html__('Pie chart circle background color', 'wp-ultimate-review'); ?>
                                </label>
                            </div>
                            <div class="wur-right-content">
                                <div class="wur-global-select-wrapper">
                                    <input name="<?php echo esc_attr($global_setting_optionKey); ?>[review_pie_bg_color]" class="wur-global-input inp-color" type="color" />
                                </div>
                            </div>
                        </div>


                        <?php

                    endif; ?>

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_score_limit_id"
                                   class="wur-sec-title"><?php echo esc_html__('Review Score Limit', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_text_filed wur-global-input wur-number-input" type="number" required
                                   id="review_score_limit_id" min="1" max="100" step="1"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[review_score_limit]"
                                   value="<?php echo (isset($return_data_global_setting['review_score_limit']) && $return_data_global_setting['review_score_limit'] != '0') ? esc_attr($return_data_global_setting['review_score_limit']) : '5'; ?>">
                        </div>
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="review_score_styleInput_id"
                                   class="wur-sec-title"><?php echo esc_html__('Review Score Input Style', 'wp-ultimate-review'); ?></label>
                        </div>


                        <div class="xs-review-score-input-style-container xs-review-graph-style-field-container">

		                    <?php

		                    $selectReviewScoreInput = isset($return_data_global_setting['review_score_input']) ? $return_data_global_setting['review_score_input'] : 'star';

		                    foreach($this->review_score_input_style AS $reviewStyleKey => $style) {

			                    $checked = ($selectReviewScoreInput == $reviewStyleKey) ? 'checked' : ''; ?>
                                <div class="xs-review-graph-style-field-box-container" >
                                    <input type="radio" id="review-score-input-<?php echo esc_attr($reviewStyleKey) ?>"    name="<?php echo esc_attr($global_setting_optionKey); ?>[review_score_input]"  value="<?php echo esc_attr($reviewStyleKey) ?>" <?php echo esc_attr($checked); ?>>
                                    <div class="xs-review-graph-style-field-box">
                                        <label for="review-score-input-<?php echo esc_attr($reviewStyleKey) ?>"> <img src="<?php echo esc_url($style['thumbnail']) ?>" alt=""></label>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                    </div>
                                </div>
			                    <?php
		                    }
		                    ?>

                        </div>


                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="send_administrator_email"
                                   class="wur-sec-title"><?php echo esc_html__('Send to adminstrator Email ', 'wp-ultimate-review'); ?><?php echo esc_html($getAdminEmail); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="send_administrator_email"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[send_administrator]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['send_administrator']) && $return_data_global_setting['send_administrator'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="send_administrator_email" class="review_switch_button_label"></label>
                        </div>
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="send_author_email"
                                   class="wur-sec-title"><?php echo esc_html__('Send to Author Email', 'wp-ultimate-review'); ?></label>
                        </div>
                        <div class="wur-right-content">
                            <input class="review_switch_button" type="checkbox" id="send_author_email"
                                   name="<?php echo esc_attr($global_setting_optionKey); ?>[send_author]"
                                   value="Yes" <?php echo (isset($return_data_global_setting['send_author']) && $return_data_global_setting['send_author'] == 'Yes') ? 'checked' : ''; ?> >
                            <label for="send_author_email" class="review_switch_button_label"></label>
                        </div>
                    </div><!-- Single Item -->

                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label class="wur-sec-title">&nbsp;</label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
                            <button type="submit" name="global_setting_review_form"
                                    class="xs-review-btn xs-btn btn-special small"><?php echo esc_html__('Save Changes', 'wp-ultimate-review'); ?></button>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                </div>


	        <?php elseif($active_tab == 'wur_criteria') : ?>

                <div class="wur-main-wrapper" id="wur-display-settings">

                    <div class="wur-criteria-container">

                        <div class="wur-criteria-box-container">

                            <div class="wur-criteria-box  wur-meta-box-container">
                                <div class="wur-criteria-head"><?php esc_html_e('Product Criteria', 'wp-ultimate-review'); ?></div>
                                <div class="wur-criteria-content">
                                    <div class="repater-overview-item">
                                        <button type="button"
                                                data-type="product"
                                                data-criteria-limit="<?php echo esc_attr($limits['product_criteria']) ?>"
                                                class="add-product-criteria  xs-review-btnAdd xs-review-add-button xs-review-btn xs-btn btn-special small"
                                        >
                                            <span class="dashicons dashicons-plus"></span><?php esc_html_e('Add', 'wp-ultimate-review'); ?>
                                        </button>

										<?php

                   $product_criteria = $global_setting_criteria_setting['product']['criteria_names'] ?? [ '', '', '' ];


                   $post_criteria = $global_setting_criteria_setting['post']['criteria_names'] ?? [ '', '', '' ];

                    foreach ($product_criteria as $criteria){
										?>
                                        <div class="reapter-div-xs">
                                            <div class="xs-wp-review-field overview-item-repeater">
                                                <div class="xs-wp-review-field-option">
                                                    <input
                                                            type="text"
                                                            name="<?php echo esc_attr( $global_setting_criteria_key ); ?>[product][criteria_names][]"
                                                            value="<?php echo esc_attr( $criteria) ?>"
                                                            placeholder="<?php esc_attr_e('Criteria Name', 'wp-ultimate-review'); ?>"
                                                            class="wur-global-input"
                                                    >
                                                </div>
                                            </div>
                                            <button type="button"
                                                    class="xs-review-btnRemove xs-review-remove-button xs-review-btn xs-btn btn-danger small"
                                                    style="display: none;">
                                                <span class="dashicons dashicons-no-alt"></span></button>
                                        </div>
                    <?php  } ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="wur-criteria-box-container">

                            <div class="wur-criteria-box  wur-meta-box-container">
                                <div class="wur-criteria-head"><?php esc_html_e('Post/Page Criteria', 'wp-ultimate-review'); ?></div>
                                <div class="wur-criteria-content">
                                    <div class="repater-overview-item" id="repater_review_item">
                                        <button type="button"
                                                data-type="post"
                                                data-criteria-limit="<?php echo esc_attr($limits['post_criteria']) ?>"
                                                class="add-product-criteria xs-review-btnAdd xs-review-add-button xs-review-btn xs-btn btn-special small">
                                            <span class="dashicons dashicons-plus"></span><?php esc_html_e('Add', 'wp-ultimate-review'); ?>
                                        </button>

                                        <?php
                                        foreach ($post_criteria as $criteria){
                                        ?>
                                        <div class="reapter-div-xs">
                                            <div class="xs-wp-review-field overview-item-repeater">
                                                <div class="xs-wp-review-field-option">
                                                    <input
                                                            type="text"
                                                            name="<?php echo esc_attr( $global_setting_criteria_key ); ?>[post][criteria_names][]"
                                                            value="<?php echo esc_attr($criteria) ?>"
                                                            placeholder="<?php esc_attr_e('Criteria Name', 'wp-ultimate-review'); ?>"
                                                            class="wur-global-input"
                                                    >
                                                </div>
                                            </div>
                                            <button type="button"
                                                    class="xs-review-btnRemove xs-review-remove-button xs-review-btn xs-btn btn-danger small"
                                                    style="display: none;">
                                                <span class="dashicons dashicons-no-alt"></span></button>
                                        </div>
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <!-- Single Item -->

                        <div class="wur-single-item">
                            <div class="wur-left-label">
                                <label class="wur-sec-title">&nbsp;</label>
                            </div> <!-- Label -->

                            <div class="wur-right-content">
                                <button type="submit" name="global_setting_criteria"
                                        class="xs-review-btn xs-btn btn-special small"><?php echo esc_html__( 'Save Changes', 'wp-ultimate-review' ); ?></button>
                            </div> <!-- Content -->
                        </div><!-- Single Item -->

                    </div>

                </div>

			<?php elseif($active_tab == 'wur_display_setting') : ?>

                <div class="wur-main-wrapper" id="wur-display-settings">

                    <div class="wur-single-item">

                        <div class="wur-left-label">
                            <label for="wur_enable_review" class="wur-sec-title">
								<?php echo esc_html__('Enable Review For', 'wp-ultimate-review'); ?>
                            </label>
                        </div>

                        <div class="wur-right-content">

	                        <?php

	                        $enabled_types = isset($return_data_display_setting['page']['data']) ? $return_data_display_setting['page']['data'] : ['post'];

	                        if(!empty($this->page_enable)):
		                        foreach($this->page_enable AS $keyPageEnable => $pageEnableValue):
			                        ?>
                                    <label for="page_enable__<?php echo esc_attr($keyPageEnable); ?>"
                                           class="review_text_filed_label wur-label">
                                        <input class="review_text_filed wur-global-radio-input"
                                               type="checkbox"
                                               id="page_enable__<?php echo esc_attr($keyPageEnable); ?>"
                                               name="<?php echo esc_attr($display_setting_optionKey); ?>[page][data][]"
                                               value="<?php echo esc_attr($keyPageEnable); ?>"
                                            <?php echo (in_array($keyPageEnable, $enabled_types)) ? 'checked' : ''; ?>>
				                        <?php echo esc_html($pageEnableValue); ?>
                                    </label>
			                        <?php
		                        endforeach;
                                ?>
                                <?php if( !class_exists( 'woocommerce' )): ?>
                                    <label for="page_enable__products" class="review_text_filed_label wur-label wur-visiblity-disable">
                                            <input class="wur-non-clickable review_text_filed wur-global-radio-input for"
                                                type="checkbox"
                                                id="page_enable__product; ?>"
                                                value="product">
                                            <?php esc_html_e('Products', 'wp-ultimate-review'); ?>
                                    </label>
                                <?php
                                endif;
	                        endif;
	                        ?>

                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item">

                        <div class="wur-left-label">
                            <label for="review_location_id" class="wur-sec-title">
								<?php echo esc_html__('Review Location', 'wp-ultimate-review'); ?>
                            </label>
                        </div>

                        <div class="wur-right-content">
                            <div class="wur-global-select-wrapper">
								<?php
								$selectReviewType = isset($return_data_display_setting['review_location']) ? $return_data_display_setting['review_location'] : 'after_content';
								?>
                                <select name="<?php echo esc_attr($display_setting_optionKey); ?>[review_location]"
                                        id="review_location_id" class="wur-global-select">
                                    <option value="after_content" <?php if($selectReviewType == 'after_content') {
										echo 'selected';
									} ?> ><?php echo esc_html__('After Content', 'wp-ultimate-review'); ?> </option>
                                    <option value="before_content" <?php if($selectReviewType == 'before_content') {
										echo 'selected';
									} ?> ><?php echo esc_html__('Before Content', 'wp-ultimate-review'); ?> </option>
                                    <option value="custom_code" <?php if($selectReviewType == 'custom_code') {
										echo 'selected';
									} ?> ><?php echo esc_html__('Custom (use shortcode)', 'wp-ultimate-review'); ?> </option>
                                </select>
                            </div>

                            <div class="wur-input-list wur-shortcode-wrapper">
                                <input class="review_text_filed wur-review_shortcode wur-global-input" type="text"
                                       id="wp_review_shortcode" value="[wp-reviews]">
                                <button type="button" onclick="copyTextData('wp_review_shortcode');"
                                        class="wur_copy_button"><span class="wur wur-file-1"></span></button>
                            </div>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label"><label for="display_review_enable"
                                                           class="wur-sec-title"><?php echo esc_html__('Display Review with Comments', 'wp-ultimate-review'); ?>
                            </label></div> <!-- Label -->

                        <div class="wur-right-content">
							<?php
							$review_list = isset($return_data_display_setting['review_list']['enable']) ? 'Yes' : 'No';
							if(!is_array($return_data_display_setting)) {
								$review_list = 'Yes';
							}
							?>
                            <input class="review_switch_button" type="checkbox" id="display_review_enable"
                                   name="<?php echo esc_attr($display_setting_optionKey); ?>[review_list][enable]"
                                   value="Yes" <?php echo ($review_list == 'Yes') ? 'checked' : ''; ?> >
                            <label for="display_review_enable" class="review_switch_button_label"></label>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label"><label for="review_shown_per_page"
                                                           class="wur-sec-title"><?php echo esc_html__('Review Shown Per Page ', 'wp-ultimate-review'); ?></label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
                            <input class="review_text_filed wur-global-input wur-number-input" type="number" required
                                   id="review_shown_per_page"
                                   name="<?php echo esc_attr($display_setting_optionKey); ?>[review_show_per]"
                                   value="<?php echo (isset($return_data_display_setting['review_show_per']) && $return_data_display_setting['review_show_per'] != '0') ? esc_attr(intval($return_data_display_setting['review_show_per'])) : '10'; ?>"
                                   min="1" max="20" step="1">
                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-no-margin">
                        <div class="wur-left-label"><label
                                    class="wur-sec-title"><?php echo esc_html__('Review Form Settings ', 'wp-ultimate-review'); ?></label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
							<?php
							if(is_array($this->controls) AND sizeof($this->controls) > 0): $count = 1;
								foreach($this->controls AS $metaKey => $metaValue):
									// Input Title
									$inputTitle = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('title_name', $metaValue)) ? $metaValue['title_name'] : '';
									$inputTitleText = $inputTitle;

									// input id
									$inputId = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('id', $metaValue)) ? $metaValue['id'] : $metaKey;

									// input require
									$inputRequire = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('require', $metaValue)) ? $metaValue['require'] : 'No';
									if($inputRequire === 'Yes') {
										//$inputTitleText .= '<em>(Required)</em> ';
									}

									$check_value = isset($return_data_display_setting['form'][$metaKey]) ? 'Yes' : 'No';
									if(!is_array($return_data_display_setting)) {
										$check_value = 'Yes';
									}
									?>
                                    <div class="wur-review-form-item <?php echo (count($this->controls) === $count) ? 'wur-single-last' : ''; ?>">
                                        <div class="wur-review-switch-section">
                                            <input class="review_switch_button" type="checkbox"
                                                   id="<?php echo esc_attr($inputId); ?>"
                                                   name="<?php echo esc_attr($display_setting_optionKey); ?>[form][<?php echo esc_attr($metaKey); ?>]"
                                                   value="Yes" <?php echo ($check_value == 'Yes') ? 'checked' : ''; ?>>
                                            <label for="<?php echo esc_attr($inputId); ?>"
                                                   class="review_switch_button_label"></label>
                                            <label for="<?php echo esc_attr($inputId); ?>"
                                                   class="wur-review-switch-text"> <?php echo esc_html($inputTitleText) ?> </label>
                                        </div>
										<?php
										$displayEnableCLass = '';
										if($check_value == 'Yes') {
											$displayEnableCLass = 'active_tr';
										}
										?>
                                        <div class="display-show-review-type deactive_tr <?php echo esc_attr($displayEnableCLass); ?>"
                                             id="xs_review_tr__<?php echo esc_attr($metaKey); ?>">
                                            <div class="xs-review-display-label-box">
                                                <label class="wur-review-type-help-label"
                                                       for="label-test__<?php echo esc_attr($metaKey); ?>_name"> <span
                                                            class="wur-review-type-help-label--text"><?php echo esc_html($inputTitleText) ?></span>
                                                    <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span></label><br/>
                                                <input class="review_text_filed wur-global-input" type="text"
                                                       id="label-test__<?php echo esc_attr($metaKey); ?>_name"
                                                       name="<?php echo esc_attr($display_setting_optionKey); ?>[form][<?php echo esc_attr($metaKey); ?>_data][label][name]"
                                                       value="<?php echo esc_attr((isset($return_data_display_setting['form'][$metaKey . '_data']['label']['name']) && $return_data_display_setting['form'][$metaKey . '_data']['label']['name'] != '') ? $return_data_display_setting['form'][$metaKey . '_data']['label']['name'] : $inputTitle); ?>">
                                            </div>

                                        </div>
                                    </div>
									<?php $count++;
								endforeach;
							endif;
							?>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label class="wur-sec-title"><?php echo esc_html__('Review Display Layout ', 'wp-ultimate-review'); ?></label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
							<?php
							if(is_array($this->controls) AND sizeof($this->controls) > 0):
								// add new element of post date in array
								$this->controls['xs_reviwer_profile_image'] = [
									'title_name' => esc_html__('Reviewer Profile Image', 'wp-ultimate-review'),
									'type'       => 'image',
									'require'    => 'No',
									'options'    => [],
								];
								$this->controls['post_date'] = [
									'title_name' => esc_html__('Review Date', 'wp-ultimate-review'),
									'type'       => 'date',
									'require'    => 'No',
									'options'    => [],
								];
								$count = 1;
								foreach($this->controls AS $metaKey => $metaValue):
									// Input Title
									$inputTitle = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('title_name', $metaValue)) ? $metaValue['title_name'] : '';
									$inputTitleText = $inputTitle;

									// input id
									$inputId = (isset($metaValue) AND is_array($metaValue) AND array_key_exists('id', $metaValue)) ? $metaValue['id'] : $metaKey;

									$check_value1 = isset($return_data_display_setting['form'][$metaKey . '_data']['display']['enable']) ? 'Yes' : 'No';
									if(!is_array($return_data_display_setting)) {
										$check_value1 = 'Yes';
									}
									?>
                                    <div class="review-switch-section  <?php echo (count($this->controls) === $count) ? 'wur-single-last' : ''; ?>">
                                        <div class="wur-review-switch-section">
                                            <input class="review_switch_button" type="checkbox"
                                                   id="enable_display__<?php echo esc_attr($metaKey); ?>"
                                                   name="<?php echo esc_attr($display_setting_optionKey); ?>[form][<?php echo esc_attr($metaKey); ?>_data][display][enable]"
                                                   value="Yes" <?php echo ($check_value1 == 'Yes') ? 'checked' : ''; ?> >
                                            <label for="enable_display__<?= esc_attr($metaKey); ?>"
                                                   class="review_switch_button_label"></label>
                                            <label for="enable_display__<?php echo esc_attr($metaKey); ?>"
                                                   class="review-switch-text wur-review-switch-text"><?php printf(
														/* translators: %s: form field label, e.g. "Reviewer Name" */
														esc_html__('Enable "%s"', 'wp-ultimate-review'),
														esc_html($inputTitle)
													); ?></label>
                                        </div>
                                    </div>

									<?php
									$displayEnableCLass1 = '';
									if($check_value1 == 'Yes') {
										$displayEnableCLass1 = 'active_tr';
									}
									?>

									<?php $count++;
								endforeach;
							endif;
							?>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->


                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="overview_avg_rating_txt" class="wur-sec-title">
								<?php echo esc_html__('Average rating text', 'wp-ultimate-review'); ?>
                            </label>
                        </div>

						<?php

						$placeholder = esc_html__('SUPERB!', 'wp-ultimate-review');
						$val         = isset($return_data_display_setting['overview_avg_rating_superb']) ? esc_html($return_data_display_setting['overview_avg_rating_superb']) : $placeholder;

						?>
                        <div class="wur-right-content">
                            <input
                                    class="review_text_filed wur-global-input"
                                    type="text"
                                    id="overview_avg_rating_txt"
                                    placeholder="<?php echo esc_attr($placeholder) ?>"
                                    name="<?php echo esc_attr($display_setting_optionKey); ?>[overview_avg_rating_superb]"
                                    value="<?php echo esc_attr($val) ?>"/>

                            <label class="wur-review-type-help-label" for="label-test__xs_reviw_summery_name">
                                <span class="wur-review-type-help-label--text"><?php esc_html_e('Shows under overview average rating value. i.e- 4.75 SUPERB!', 'wp-ultimate-review'); ?></span>
                                <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
                            </label>

                        </div> <!-- Content -->
                    </div><!-- Single Item -->

                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="avg_rating_txt_condition" class="wur-sec-title">
								<?php echo esc_html__('Show rating text if ', 'wp-ultimate-review'); ?>
                            </label>
                        </div>

						<?php

						$val = isset($return_data_display_setting['overview_avg_rating_if']) ? floatval($return_data_display_setting['overview_avg_rating_if']) : 3.75;

						?>
                        <div class="wur-right-content">
                            <input
                                    class="review_text_filed wur-global-input wur-number-input"
                                    type="number"
                                    id="avg_rating_txt_condition"
                                    placeholder="3.75"
                                    name="<?php echo esc_attr($display_setting_optionKey); ?>[overview_avg_rating_if]"
                                    value="<?php echo esc_attr($val) ?>"
                                    min="0"
                                    max="5"
                                    step="any"/>

                            <label class="wur-review-type-help-label" for="label-test__xs_reviw_summery_name">
                                <span class="wur-review-type-help-label--text"><?php esc_html_e('Shows average rating text if only rating is grater than or equal given value here', 'wp-ultimate-review'); ?></span>
                                <span class="wur-review-type-help-label--icon dashicons-before dashicons-warning"></span>
                            </label>

                        </div> <!-- Content -->
                    </div><!-- Single Item -->


                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label class="wur-sec-title">&nbsp;</label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
                            <button type="submit" name="display_setting_review_form"
                                    class="xs-review-btn xs-btn btn-special small"><?php echo esc_html__('Save Changes', 'wp-ultimate-review'); ?></button>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->


                </div>
            <?php elseif($active_tab == 'wur_captcha_setting') : ?>
                <div class="wur-main-wrapper" id="wur-captcha-settings">
                    <div class="wur-single-item wur-single-item-middle">
                        <div class="wur-left-label">
                            <label for="captcha_enable" class="wur-sec-title">
                                <?php echo esc_html__('Enable reCaptcha', 'wp-ultimate-review'); ?>
                            </label>
                        </div> <!-- Label -->

                        <div class="wur-right-content wur-show-captcha-settings-switch">
							<?php
                            if($is_pro_exist):
                                $wur_captcha = isset($return_data_captcha_setting['wur_captcha']['enable']) ? 'Yes' : 'No';
                                if(!is_array($return_data_captcha_setting)) {
                                    $wur_captcha = 'No';
                                }
                                ?>
                                <input class="review_switch_button" type="checkbox" id="captcha_enable"
                                    name="<?php echo esc_attr($captcha_setting_optionKey); ?>[wur_captcha][enable]"
                                    value="Yes" <?php echo esc_html(($wur_captcha == 'Yes') ? 'checked' : ''); ?> >
                                <label for="captcha_enable" class="review_switch_button_label"></label>
                            <?php else: ?>
                                <strong><a href="https://wpmet.com/plugin/wp-ultimate-review/pricing/" target="_blank" style="color: red;"><?php echo esc_html__('Buy Premium', 'wp-ultimate-review'); ?></a> </strong>
                            <?php endif; ?>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->
                    <?php if($is_pro_exist): ?>
                    <div class="wur-show-captcha-settings" <?php echo wp_kses((($wur_captcha == 'No') ? 'style="display:none"' : ''), \WurReview\App\Settings::kses(null, true)); ?>>
                        <div class="wur-single-item wur-single-item-middle">
                            <div class="wur-left-label">
                                <label for="captcha_setting_id" class="wur-sec-title">
                                    <?php echo esc_html__('Select version:', 'wp-ultimate-review'); ?>
                                </label>
                            </div>
    
                            <div class="wur-right-content">
                                <div class="wur-global-select-wrapper">
                                    <?php
                                    $select_version = isset($return_data_captcha_setting['captcha_version']) ? $return_data_captcha_setting['captcha_version'] : 'recaptcha-v2';
                                    ?>
                                    <select name="<?php echo esc_attr($captcha_setting_optionKey); ?>[captcha_version]"
                                            id="captcha_setting_id" class="wur-global-select">
                                        <option value="recaptcha-v2" <?php if($select_version == 'recaptcha-v2') {
                                            echo esc_attr('selected');
                                        } ?> ><?php echo esc_html__('reCAPTCHA V2', 'wp-ultimate-review'); ?> </option>
                                        <option value="recaptcha-v3" <?php if($select_version == 'recaptcha-v3') {
                                            echo esc_attr('selected');
                                        } ?> ><?php echo esc_html__('reCAPTCHA V3', 'wp-ultimate-review'); ?> </option>
                                    </select>
                                </div>
                            </div> <!-- Content -->
                        </div><!-- Single Item -->
    
                        <div class="wur-single-item wur-single-item-middle">
                            <div class="wur-left-label">
                                <label for="wur_site_key" class="wur-sec-title">
                                    <?php echo esc_html__('Site Key:', 'wp-ultimate-review'); ?>
                                </label>
                            </div>
    
                            <?php
    
                            $placeholder = esc_html__('Insert site key', 'wp-ultimate-review');
                            $val         = isset($return_data_captcha_setting['captcha_site_key']) ? esc_html($return_data_captcha_setting['captcha_site_key']) : '';
    
                            ?>
                            <div class="wur-right-content">
                                <input
                                        class="review_text_filed wur-global-input wur-width-100"
                                        type="text"
                                        id="wur_site_key"
                                        placeholder="<?php echo esc_attr($placeholder) ?>"
                                        name="<?php echo esc_attr($captcha_setting_optionKey); ?>[captcha_site_key]"
                                        value="<?php echo esc_attr($val) ?>"/>
                                        <p class="description">
                                            <?php esc_html_e('Create google reCaptcha site key from reCaptcha admin panel ', 'wp-ultimate-review'); ?><a target="__blank" href="<?php echo esc_url('https://www.google.com/recaptcha/admin/', 'wp-ultimate-review'); ?>"><?php esc_html_e('Create from here', 'wp-ultimate-review'); ?></a>
                                        </p>
                            </div> <!-- Content -->
                        </div><!-- Single Item -->
    
                        <div class="wur-single-item wur-single-item-middle">
                            <div class="wur-left-label">
                                <label for="wur_secret_key" class="wur-sec-title">
                                    <?php echo esc_html__('Secret Key:', 'wp-ultimate-review'); ?>
                                </label>
                            </div>
    
                            <?php
    
                            $placeholder = esc_html__('Insert secret key', 'wp-ultimate-review');
                            $val         = isset($return_data_captcha_setting['captcha_secret_key']) ? esc_html($return_data_captcha_setting['captcha_secret_key']) : '';
    
                            ?>
                            <div class="wur-right-content">
                                <input
                                        class="review_text_filed wur-global-input wur-width-100"
                                        type="text"
                                        id="wur_secret_key"
                                        placeholder="<?php echo esc_attr($placeholder) ?>"
                                        name="<?php echo esc_attr($captcha_setting_optionKey); ?>[captcha_secret_key]"
                                        value="<?php echo esc_attr($val) ?>"/>
                                        <p class="description">
                                            <?php esc_html_e('Create google reCaptcha secret key from reCaptcha admin panel ', 'wp-ultimate-review'); ?><a target="__blank" href="<?php echo esc_url('https://www.google.com/recaptcha/admin/', 'wp-ultimate-review'); ?>"><?php esc_html_e('Create from here', 'wp-ultimate-review'); ?></a>
                                        </p>
                            </div> <!-- Content -->
                        </div><!-- Single Item -->
    
                    </div>
                    <div class="wur-single-item">
                        <div class="wur-left-label">
                            <label class="wur-sec-title">&nbsp;</label>
                        </div> <!-- Label -->

                        <div class="wur-right-content">
                            <button type="submit" name="captcha_setting_form"
                                    class="xs-review-btn xs-btn btn-special small"><?php echo esc_html__('Save Changes', 'wp-ultimate-review'); ?></button>
                        </div> <!-- Content -->
                    </div><!-- Single Item -->
                    <?php endif; ?>
                </div>
			<?php endif; ?>
            <input type="hidden" name="wur_form_submit" value="submit_form">
        </form>
    </div>
</div>