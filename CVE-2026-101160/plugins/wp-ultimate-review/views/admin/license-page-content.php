<?php

use  WurReview\App\Application;
use WurReview\App\License\License;

defined( 'ABSPATH' ) || exit;

  $license_status = License::instance()->status();


?>

<div class="wrap">
    <h2><?php esc_html_e('License Settings', 'wp-ultimate-review'); ?></h2>
    <div class="xs-review-admin-container stuffbox" style="padding:15px">
        <div class="attr-card-body">
            <form action="" method="post" class="form-group attr-input-group mf-admin-input-text mf-admin-input-text--xs-review-license-key">

                <?php if($license_status == 'invalid') :?>
                <p><?php printf(
					/* translators: %s: plugin name */
					esc_html__('Enter your license key here to activate %s. It will enable update notice and auto updates.', 'wp-ultimate-review'),
					esc_html(Application::name())
				); ?></p>

                <ol>
                    <li><?php esc_html_e('Log in to your Wpmet account to get the license key.', 'wp-ultimate-review'); ?></li>
                    <li><?php printf(
							/* translators: %1$s: opening link tag, %2$s: plugin name, %3$s: closing link tag */
							esc_html__('If you don\'t yet buy this product, get %1$s%2$s%3$s now.', 'wp-ultimate-review'),
							'<a href="' . esc_url(Application::landing_page()) . '" target="_blank">',
							esc_html(Application::name()),
							'</a>'
						); ?></li>
                    <li><?php printf(
							/* translators: %s: plugin name */
							esc_html__('Copy the %s license key from your account and paste it below.', 'wp-ultimate-review'),
							esc_html(Application::name())
						); ?></li>
                </ol>

                <label for="mf-admin-option-text-xs-review-license-key"><b><?php esc_html_e('Your License Key', 'wp-ultimate-review'); ?></b></label><br/><br/>

                    <input type="text" class="attr-form-control" id="mf-admin-option-text-xs-review-license-key" required placeholder="<?php esc_attr_e('Please insert your license key here', 'wp-ultimate-review'); ?>" name="xs-review-pro-settings-page-key" value="">

                    <span class="attr-input-group-btn">
                        <input type="hidden" name="xs-review-pro-settings-page-action" value="activate">
                        <button class="button button-primary" type="submit"><div class="mf-spinner"></div><?php esc_html_e('Activate', 'wp-ultimate-review'); ?></button>
                    </span>

                <div class="xs-review-license-form-result">
                    <p class="attr-alert attr-alert-info">
                        <?php esc_html_e("Still can't find your license key? ", 'wp-ultimate-review'); ?><a target="_blank" href="https://wpmet.com/support-ticket"><?php esc_html_e('Knock us here!', 'wp-ultimate-review'); ?></a>
                    </p>
                </div>

                <?php else: ?>
                <div id="xs-review-sites-notice-id-license-status" class="xs-review-notice notice xs-review-active-notice notice-success" dismissible-meta="user">
                    <p><?php printf( esc_html__('Congratulations! You\'r product is activated for "%s"', 'wp-ultimate-review'), esc_html(parse_url(home_url(), PHP_URL_HOST))); ?></p>
                </div>

                <div class="attr-revoke-btn-container">
                <input type="hidden" name="xs-review-pro-settings-page-action" value="deactivate">
                <button type="submit" class="button button-secondary"><?php esc_html_e('Remove license from this domain', 'wp-ultimate-review'); ?></button> <span style="margin: 8px 0 0 20px; display: inline-block;"><?php esc_html_e('See documentation ', 'wp-ultimate-review'); ?><a target="_blank" href="https://wpmet.com/knowledgebase/wp-ultimate-review/"><?php esc_html_e('here', 'wp-ultimate-review'); ?></a>.</span>
                </div>
                <?php endif; ?>

                <?php wp_nonce_field( 'xs-review-pro-settings-page', 'xs-review-pro-settings-page' ); ?>
            </form>
        </div>
    </div>
</div>