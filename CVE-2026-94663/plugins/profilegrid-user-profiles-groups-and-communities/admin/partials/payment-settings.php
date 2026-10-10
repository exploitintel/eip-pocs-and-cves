<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = 'profilegrid-user-profiles-groups-and-communities';
$path       =  plugin_dir_url( __FILE__ );
$identifier = 'SETTINGS';

if ( filter_input( INPUT_POST, 'submit_settings' ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( !wp_verify_nonce( $retrieved_nonce, 'save_payment_settings' ) ) {
		die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
    }
	$exclude = array( '_wpnonce', '_wp_http_referer', 'submit_settings' );
	$post    = $pmrequests->sanitize_request( $_POST, $identifier, $exclude );
	if ( $post!=false ) {
		if ( isset( $post['pm_enable_paypal'] ) ) {
			$post['pm_enable_paypal'] = absint( $post['pm_enable_paypal'] );
		}
		if ( !isset( $post['pm_paypal_test_mode'] ) ) {
			$post['pm_paypal_test_mode'] = 0;
		} else {
			$post['pm_paypal_test_mode'] = absint( $post['pm_paypal_test_mode'] );
        }
		if ( !isset( $post['pm_enable_paypal'] ) ) {
			$post['pm_enable_paypal'] = 0;
		}
		if ( isset( $post['pm_przelewy24_test_mode'] ) ) {
			$post['pm_przelewy24_test_mode'] = absint( $post['pm_przelewy24_test_mode'] );
		}
		if ( !isset( $post['pm_przelewy24_test_mode'] ) ) {
			$post['pm_przelewy24_test_mode'] = 0;
        }
		if ( isset( $post['pm_enable_przelewy24'] ) ) {
			$post['pm_enable_przelewy24'] = absint( $post['pm_enable_przelewy24'] );
		}
		if ( !isset( $post['pm_enable_przelewy24'] ) ) {
			$post['pm_enable_przelewy24'] = 0;
		}
		foreach ( $post as $key=>$value ) {
			$dbhandler->update_global_option_value( $key, $value );
		}
	}
	do_action( 'profile_magic_save_payment_setting', $post );
	wp_safe_redirect( esc_url_raw( 'admin.php?page=pm_settings' ) );
	exit;
}
?>

<div class="uimagic">
  <form name="pm_user_settings" id="pm_user_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <span class="alignright pg-view-payments-log-button"><a href="<?php echo esc_url( admin_url( 'admin.php?page=pm_group_membership_payments' ) ); ?>" class="page-title-action pg-view-payments-log-button"><?php esc_html_e( 'View Payments Log', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>
        <?php esc_html_e( 'Payments', 'profilegrid-user-profiles-groups-and-communities' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
        
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Paypal Payment Processor', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_paypal" id="pm_enable_paypal" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_enable_paypal', '1' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_paypal_html')" />
          <label for="pm_enable_paypal"></label>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Turn on the payment system(s) you want to use for accepting payments. Make sure you configure them right.', 'profilegrid-user-profiles-groups-and-communities' ); ?><a target="_blank" href="https://profilegrid.co/documentation/payments/"><?php esc_html_e( 'More', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
      </div>
      <div class="childfieldsrow" id="pm_paypal_html" style=" 
      <?php
		if ( $dbhandler->get_global_option_value( 'pm_enable_paypal', '1' )==1 ) {
			echo 'display:block;';
		} else {
			echo 'display:none;';}
		?>
        ">  
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Test Mode:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_paypal_test_mode" id="pm_paypal_test_mode" type="checkbox" <?php checked( $dbhandler->get_global_option_value( 'pm_paypal_test_mode' ), '1' ); ?> class="pm_toggle" value="1" style="display:none;" />
          <label for="pm_paypal_test_mode"></label>
        </div>
        <div class="uimnote"> <?php esc_html_e( 'This will put ProfileGrid payments on test mode. Useful for testing payment system.', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>
      </div>
          
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'PayPal Email:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input name="pm_paypal_email" id="pm_paypal_email" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_paypal_email' ) ); ?>" />
        </div>
        <div class="uimnote"> <?php esc_html_e( 'Your PayPal account email, to which you will accept the payments.', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>
      </div>  
          
        <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'PayPal Page Style:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <input name="pm_paypal_page_style" id="pm_paypal_page_style" type="text" value="<?php echo esc_attr( $dbhandler->get_global_option_value( 'pm_paypal_page_style' ) ); ?>" />
        </div>
        <div class="uimnote"><?php esc_html_e( 'If you have created checkout pages in your PayPal account and want to show a specific page, you can enter it’s name here.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
          
      </div>
      
      <?php do_action( 'profile_magic_payment_setting_option' ); ?>
      
      
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Currency:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <select name="pm_paypal_currency" id="pm_paypal_currency">
          <option value="USD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'USD' ); ?>><?php esc_html_e( 'US Dollars', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="EUR" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'EUR' ); ?>><?php esc_html_e( 'Euros', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&euro;)</option>
          <option value="GBP" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'GBP' ); ?>><?php esc_html_e( 'Pounds Sterling', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&pound;)</option>
          <option value="AUD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'AUD' ); ?>><?php esc_html_e( 'Australian Dollars', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="BRL" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'BRL' ); ?>><?php esc_html_e( 'Brazilian Real', 'profilegrid-user-profiles-groups-and-communities' ); ?> (R$)</option>
          <option value="CAD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'CAD' ); ?>><?php esc_html_e( 'Canadian Dollars', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="CZK" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'CZK' ); ?>><?php esc_html_e( 'Czech Koruna', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="DKK" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'DKK' ); ?>><?php esc_html_e( 'Danish Krone', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="HKD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'HKD' ); ?>><?php esc_html_e( 'Hong Kong Dollar', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="HUF" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'HUF' ); ?>><?php esc_html_e( 'Hungarian Forint', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="ILS" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'ILS' ); ?>><?php esc_html_e( 'Israeli Shekel', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&#x20aa;)</option>
          <option value="JPY" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'JPY' ); ?>><?php esc_html_e( 'Japanese Yen', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&yen;)</option>
          <option value="MYR" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'MYR' ); ?>><?php esc_html_e( 'Malaysian Ringgits', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="MXN" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'MXN' ); ?>><?php esc_html_e( 'Mexican Peso', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="NZD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'NZD' ); ?>><?php esc_html_e( 'New Zealand Dollar', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="NOK" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'NOK' ); ?>><?php esc_html_e( 'Norwegian Krone', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="PHP" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'PHP' ); ?>><?php esc_html_e( 'Philippine Pesos', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="PLN" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'PLN' ); ?>><?php esc_html_e( 'Polish Zloty', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="SGD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'SGD' ); ?>><?php esc_html_e( 'Singapore Dollar', 'profilegrid-user-profiles-groups-and-communities' ); ?> ($)</option>
          <option value="SEK" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'SEK' ); ?>><?php esc_html_e( 'Swedish Krona', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="CHF" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'CHF' ); ?>><?php esc_html_e( 'Swiss Franc', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="TWD" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'TWD' ); ?>><?php esc_html_e( 'Taiwan New Dollars', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="THB" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'THB' ); ?>><?php esc_html_e( 'Thai Baht', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&#3647;)</option>
          <option value="INR" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'INR' ); ?>><?php esc_html_e( 'Indian Rupee', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&#x20B9;)</option>
          <option value="TRY" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'TRY' ); ?>><?php esc_html_e( 'Turkish Lira', 'profilegrid-user-profiles-groups-and-communities' ); ?> (&#8378;)</option>
          <option value="RIAL" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'RIAL' ); ?>><?php esc_html_e( 'Iranian Rial', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
          <option value="RUB" <?php selected( $dbhandler->get_global_option_value( 'pm_paypal_currency' ), 'RUB' ); ?>><?php esc_html_e( 'Russian Rubles', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
        </select>
        </div>
        <div class="uimnote"> <?php esc_html_e( 'Default Currency for accepting payments. Usually, this will be default currency in your PayPal account.', 'profilegrid-user-profiles-groups-and-communities' ); ?> </div>
      </div>
      
      
      
      <div class="uimrow">
        <div class="uimfield">
          <?php esc_html_e( 'Currency Symbol Position:', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="uiminput">
         <select id="pm_currency_position" name="pm_currency_position">
         <option value="before" <?php selected( $dbhandler->get_global_option_value( 'pm_currency_position' ), 'before' ); ?>><?php esc_html_e( 'Before - $10', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
         <option value="after" <?php selected( $dbhandler->get_global_option_value( 'pm_currency_position' ), 'after' ); ?>><?php esc_html_e( 'After - 10$', 'profilegrid-user-profiles-groups-and-communities' ); ?></option>
         </select>
        </div>
        <div class="uimnote"><?php esc_html_e( 'Choose the location of the currency sign.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
      </div>
      
    

      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        </a>
        <?php wp_nonce_field( 'save_payment_settings' ); ?>
          <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
