<?php
class PM_paypal_request {

	private function pg_validate_completed_ipn( $post, $gid, $transaction_id ) {
		$dbhandler = new PM_DBhandler();
		$pmrequests = new PM_request();
		$paypal = new profile_magic_paypal_class();
		$paypal->sendbox = $dbhandler->get_global_option_value( 'pm_paypal_test_mode', '0' );
		$paypal->admin_mail = $pmrequests->profile_magic_get_admin_email();
		$paypal->paypal_mail = $dbhandler->get_global_option_value( 'pm_paypal_email' );
		$paypal->from_mail = $pmrequests->profile_magic_get_from_email();

		$existing_logs = $dbhandler->get_all_result( 'PAYPAL_LOG', array( 'txn_id' ), 1, 'results', 0, false, 'id', 'DESC', '', ARRAY_A );
		$existing_txn_ids = is_array( $existing_logs ) ? wp_list_pluck( $existing_logs, 'txn_id' ) : array();
		$paypal->txn_id = array_diff( $existing_txn_ids, array( $transaction_id ) );

		if ( ! $paypal->validate_ipn() ) {
			return false;
		}

		$expected_amount = (float) $pmrequests->profile_magic_check_paid_group( $gid );
		$actual_amount = isset( $post['mc_gross'] ) ? (float) $post['mc_gross'] : 0.0;
		$expected_currency = strtoupper( (string) $dbhandler->get_global_option_value( 'pm_paypal_currency', 'USD' ) );
		$actual_currency = isset( $post['mc_currency'] ) ? strtoupper( sanitize_text_field( $post['mc_currency'] ) ) : '';

		if ( $expected_amount <= 0 || abs( $expected_amount - $actual_amount ) > 0.00001 ) {
			return false;
		}

		if ( '' !== $expected_currency && $expected_currency !== $actual_currency ) {
			return false;
		}

		return true;
	}

    public function profile_magic_repayment_process( $user_id, $gid ) {
                 $dbhandler = new PM_DBhandler();
                $pmrequests = new PM_request();
		$invoice            = gmdate( 'His' ) . wp_rand( 1234, 9632 );
		$amount             = $pmrequests->profile_magic_check_paid_group( $gid );
		$p                  = new profile_magic_paypal_class(); // paypal class
		$p->admin_mail      = $pmrequests->profile_magic_get_admin_email(); // set notification email
		$p->sendbox         = $dbhandler->get_global_option_value( 'pm_paypal_test_mode' );
		$p->from_mail       = $pmrequests->profile_magic_get_from_email();
		$group_name         = $dbhandler->get_value( 'GROUPS', 'group_name', $gid, 'id' );
                $type       = $pmrequests->profile_magic_get_group_type( $gid );
                $group_payment_array = maybe_unserialize(get_user_meta($user_id,'pm_group_payment_status', true));
                if(empty($group_payment_array))
                {
                    $group_payment_array = array();
                }
                $group_payment_array[$gid] = 'pending';
                update_user_meta( $user_id, 'pm_group_payment_status', $group_payment_array );
		update_user_meta( $user_id, 'pm_user_payment_status', 'pending' );
		update_user_meta( $user_id, 'pm_invoice', $invoice );
		$this_script = get_permalink();
		$sign        = strpos( $this_script, '?' )?'&':'?';
		$p->add_field( 'business', $dbhandler->get_global_option_value( 'pm_paypal_email' ) ); // Call the facilitator eaccount
		$p->add_field( 'cmd', '_cart' ); // cmd should be _cart for cart checkout
		$p->add_field( 'upload', '1' );
		$p->add_field( 'return', $this_script . $sign . 'action=success&uid=' . $user_id . '&gid=' . $gid ); // return URL after the transaction got over
		$p->add_field( 'cancel_return', $this_script . $sign . 'action=cancel' ); // cancel URL if the trasaction was cancelled during half of the transaction
		$p->add_field( 'notify_url', $this_script . $sign . 'action=ipn' ); // Notify URL which received IPN (Instant Payment Notification)
		$p->add_field( 'currency_code', $dbhandler->get_global_option_value( 'pm_paypal_currency' ) );
		$p->add_field( 'invoice', $invoice );
		$p->add_field( 'custom', $user_id . '|' . $gid . '|' . $type );
		$p->add_field( 'page_style', $dbhandler->get_global_option_value( 'pm_paypal_page_style' ) );
		$p->add_field( 'item_name_1', $group_name );
		$p->add_field( 'amount_1', $amount );
		$p->submit_paypal_post(); // POST it to paypal
	}

	public function profile_magic_payment_process( $post, $action, $gid, $user_id, $textdomain ) {
                 $dbhandler = new PM_DBhandler();
                $pmrequests = new PM_request();
                $pmemails   = new PM_Emails();
		$amount             = $pmrequests->profile_magic_check_paid_group( $gid );
                $type       = $pmrequests->profile_magic_get_group_type( $gid );
		$p                  = new profile_magic_paypal_class(); // paypal class
		$p->admin_mail      = $pmrequests->profile_magic_get_admin_email(); // set notification email
		$p->sendbox         = $dbhandler->get_global_option_value( 'pm_paypal_test_mode' );
		$p->from_mail       = $pmrequests->profile_magic_get_from_email();
		$group_name         = $dbhandler->get_value( 'GROUPS', 'group_name', $gid, 'id' );
                
		switch ( $action ) {
			case 'process':
                                
                                $group_payment_array = maybe_unserialize(get_user_meta($user_id,'pm_group_payment_status', true));
                                if(empty($group_payment_array))
                                {
                                    $group_payment_array = array();
                                }
                                $group_payment_array[$gid] = 'pending';
                                update_user_meta( $user_id, 'pm_group_payment_status', $group_payment_array );
				update_user_meta( $user_id, 'rm_user_status', '1' );
				update_user_meta( $user_id, 'pm_user_payment_status', 'pending' );
                                
				update_user_meta( $user_id, 'pm_invoice', $post['invoice'] );
				$this_script =get_permalink();
				$sign        = strpos( $this_script, '?' )?'&':'?';
				$p->add_field( 'business', $dbhandler->get_global_option_value( 'pm_paypal_email' ) ); // Call the facilitator eaccount
				$p->add_field( 'cmd', $post['cmd'] ); // cmd should be _cart for cart checkout
				$p->add_field( 'upload', '1' );
				$p->add_field( 'return', $this_script . $sign . 'action=success&uid=' . $user_id . '&gid=' . $gid ); // return URL after the transaction got over
				$p->add_field( 'cancel_return', $this_script . $sign . 'action=cancel' ); // cancel URL if the trasaction was cancelled during half of the transaction
				$p->add_field( 'notify_url', $this_script . $sign . 'action=ipn' ); // Notify URL which received IPN (Instant Payment Notification)
				$p->add_field( 'currency_code', $dbhandler->get_global_option_value( 'pm_paypal_currency' ) );
				$p->add_field( 'invoice', $post['invoice'] );
				$p->add_field( 'custom', $user_id . '|' . $gid . '|' . $type );
				$p->add_field( 'page_style', $dbhandler->get_global_option_value( 'pm_paypal_page_style' ) );
				$p->add_field( 'item_name_1', $group_name );
				$p->add_field( 'amount_1', $amount );
				$p->submit_paypal_post(); // POST it to paypal
				// Show the posted values for a reference, comment this line before app goes live
				break;
			case 'success': // success case to show the user payment got success
				echo '<div>';
				echo "<div class='info-text'>" . esc_html__( 'Thank you! Your payment was successful.', 'profilegrid-user-profiles-groups-and-communities' ) . '</br>';
				echo '</div></div>';
                                $gid = filter_input( INPUT_GET, 'gid', FILTER_VALIDATE_INT );

				if ( $dbhandler->get_value( 'GROUPS', 'show_success_message', $gid )==1 ) {
					$success_message = $dbhandler->get_value( 'GROUPS', 'success_message', $gid );
					if ( ! empty( $success_message ) ) {
						echo wp_kses_post( wp_unslash( $success_message ) );
					}
				}
				if ( $pmrequests->pm_get_user_redirect( $gid )!='' ) {
					header( 'refresh: 5; url=' . $pmrequests->pm_get_user_redirect( $gid ) );
				}

			    break;
			case 'cancel': // case cancel to show user the transaction was cancelled
				echo '<div id="crf-form">';
				echo "<div class='info-text'>" . esc_html__( 'The payment was canceled before completion.', 'profilegrid-user-profiles-groups-and-communities' ) . '</br>';
				echo '</div></div>';
			    break;
			case 'ipn': // IPN case to receive payment information. this case will not displayed in browser. This is server to server communication. PayPal will send the transactions each and every details to this case in secured POST menthod by server to server.
				$trasaction_id           = $post['txn_id'];
				$payment_status          = strtolower( $post['payment_status'] );
				$invoice                 = $post['invoice'];
				$customs                 = explode( '|', $post['custom'] );
                                $user_id = $customs[0];
                                $gid     = $customs[1];
                                $type    = $customs[2];
				$log_array               = maybe_serialize( $post );
				$log_check               = $dbhandler->pm_count( 'PAYPAL_LOG', array( 'txn_id'=>$trasaction_id ), '*' );
				$amount                  = $post['mc_gross'];

                                $currency = $post['mc_currency'];
				if ( $log_check<= 0 ) {
					$dbhandler->insert_row(
                        'PAYPAL_LOG',
                        array(
							'txn_id'        =>$trasaction_id,
							'log'           =>$log_array,
							'posted_date'   => current_time( 'mysql' ),
							'gid'           =>$gid,
							'status'        =>$payment_status,
							'invoice'       =>$invoice,
							'amount'        =>$amount,
							'currency'      =>$currency,
							'pay_processor' =>'paypal',
							'pay_type'      =>'one_time',
							'uid'           =>$user_id,
                        ),
                        array( '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d' )
                    );
				} else {
					$dbhandler->update_row( 'PAYPAL_LOG', 'txn_id', $trasaction_id, array( 'log'=>$log_array ), array( '%s' ), '%s' );
				} // Save and update the logs array
				$paypal_log_id = $dbhandler->get_value( 'PAYPAL_LOG', 'id', $trasaction_id, 'txn_id' );
				if ( $payment_status=='completed' && $this->pg_validate_completed_ipn( $post, $gid, $trasaction_id ) ) { // validate the IPN, do the others stuffs here as per your app logic
                                    
                                        $group_payment_array = maybe_unserialize(get_user_meta($user_id,'pm_group_payment_status', true));
                                        if(empty($group_payment_array))
                                        {
                                            $group_payment_array = array();
                                        }
                                        $group_payment_array[$gid] = $payment_status;
                                        update_user_meta( $user_id, 'pm_group_payment_status', $group_payment_array );
					update_user_meta( $user_id, 'pm_txn_id', $trasaction_id );
					update_user_meta( $user_id, 'pm_paypal_log_id', $paypal_log_id );
					update_user_meta( $user_id, 'pm_user_payment_status', $payment_status );
					update_user_meta( $user_id, 'pm_invoice', $invoice );
					$subject = __( 'Instant Payment Notification - Recieved Payment', 'profilegrid-user-profiles-groups-and-communities' );
					// Send the notification about the transaction
					
                                            $pmrequests->profile_magic_join_group_fun( $user_id, $gid, $type );
                                            do_action( 'profilegrid_payment_complete', $gid, $user_id );
                                            $autoapproval              = $dbhandler->get_global_option_value( 'pm_auto_approval', 0 );
                                            $send_user_activation_link = $dbhandler->get_global_option_value( 'pm_send_user_activation_link', 0 );
						if ( $autoapproval=='1' ) {
							if ( $send_user_activation_link=='1' ) {
								$userstatus = '1';
								$pmrequests->pm_update_user_activation_code( $user_id );
								$pmemails->pm_send_activation_link( $user_id, $this->profile_magic );
							} else {
								$userstatus = '0';
								$pmemails->pm_send_group_based_notification( $gid, $user_id, 'on_user_activate' );
							}
						} else {
							$userstatus = '1';
						}
						update_user_meta( $user_id, 'rm_user_status', $userstatus );
					
				} else {
					$subject = __( 'Instant Payment Notification - Payment Fail', 'profilegrid-user-profiles-groups-and-communities' );
					// failed notification
                                        do_action( 'profilegrid_payment_failed', $gid, $user_id );
				}
			    break;
		}
	}

	public function profile_magic_join_group_payment_process( $post, $action, $gid, $user_id ) {
            $dbhandler  = new PM_DBhandler();
			$pmrequests = new PM_request();
			$pmemails   = new PM_Emails();
		$amount         = $pmrequests->profile_magic_check_paid_group( $gid );
			$type       = $pmrequests->profile_magic_get_group_type( $gid );
		$p              = new profile_magic_paypal_class(); // paypal class
		$p->admin_mail  = $pmrequests->profile_magic_get_admin_email(); // set notification email
		$p->sendbox     = $dbhandler->get_global_option_value( 'pm_paypal_test_mode' );
		$p->from_mail   = $pmrequests->profile_magic_get_from_email();
		$group_name     = $dbhandler->get_value( 'GROUPS', 'group_name', $gid, 'id' );

		switch ( $action ) {
			case 'process':
                                $group_payment_array = maybe_unserialize(get_user_meta($user_id,'pm_group_payment_status', true));
                                if(empty($group_payment_array))
                                {
                                    $group_payment_array = array();
                                }
                                $group_payment_array[$gid] = 'pending';
                                update_user_meta( $user_id, 'pm_group_payment_status', $group_payment_array );
				update_user_meta( $user_id, 'pm_invoice_' . $gid, $post['invoice'] );
                                update_user_meta( $user_id, 'pm_user_payment_status_' . $gid, 'pending' );
				$this_script =get_permalink();
				$sign        = strpos( $this_script, '?' )?'&':'?';
				$p->add_field( 'business', $dbhandler->get_global_option_value( 'pm_paypal_email' ) ); // Call the facilitator eaccount
				$p->add_field( 'cmd', $post['cmd'] ); // cmd should be _cart for cart checkout
				$p->add_field( 'upload', '1' );
				$p->add_field( 'return', $this_script . $sign . 'action=success&gid=' . $gid ); // return URL after the transaction got over
				$p->add_field( 'cancel_return', $this_script . $sign . 'action=cancel' ); // cancel URL if the trasaction was cancelled during half of the transaction
				$p->add_field( 'notify_url', $this_script . $sign . 'action=ipn' ); // Notify URL which received IPN (Instant Payment Notification)
				$p->add_field( 'currency_code', $dbhandler->get_global_option_value( 'pm_paypal_currency' ) );
				$p->add_field( 'invoice', $post['invoice'] );
				$p->add_field( 'custom', $user_id . '|' . $gid . '|' . $type );
				$p->add_field( 'page_style', $dbhandler->get_global_option_value( 'pm_paypal_page_style' ) );
				$p->add_field( 'item_name_1', $group_name );
				$p->add_field( 'amount_1', $amount );
				$p->submit_paypal_post(); // POST it to paypal
				// Show the posted values for a reference, comment this line before app goes live
				break;
			case 'success': // success case to show the user payment got success
				echo '<div>';
				echo "<div class='info-text'>" . esc_html__( 'Thank you! Your payment was successful.', 'profilegrid-user-profiles-groups-and-communities' ) . '</br>';
				echo '</div></div>';

			    break;
			case 'cancel': // case cancel to show user the transaction was cancelled
				echo '<div id="crf-form">';
				echo "<div class='info-text'>" . esc_html__( 'The payment was canceled before completion.', 'profilegrid-user-profiles-groups-and-communities' ) . '</br>';
				echo '</div></div>';
			    break;
			case 'ipn': // IPN case to receive payment information. this case will not displayed in browser. This is server to server communication. PayPal will send the transactions each and every details to this case in secured POST menthod by server to server.
				$trasaction_id            = $post['txn_id'];
				$payment_status           = strtolower( $post['payment_status'] );
				$invoice                  = $post['invoice'];
				$customs                  = explode( '|', $post['custom'] );
                                $user_id  = $customs[0];
                                $gid      = $customs[1];
                                $type     = $customs[2];
				$log_array                = maybe_serialize( $post );
				$log_check                = $dbhandler->pm_count( 'PAYPAL_LOG', array( 'txn_id'=>$trasaction_id ), '*' );
				$amount                   = $post['mc_gross'];
                                $currency = $post['mc_currency'];
				if ( $log_check<= 0 ) {
					$dbhandler->insert_row(
                        'PAYPAL_LOG',
                        array(
							'txn_id'        =>$trasaction_id,
							'log'           =>$log_array,
							'posted_date'   => current_time( 'mysql' ),
							'gid'           =>$gid,
							'status'        =>$payment_status,
							'invoice'       =>$invoice,
							'amount'        =>$amount,
							'currency'      =>$currency,
							'pay_processor' =>'paypal',
							'pay_type'      =>'one_time',
							'uid'           =>$user_id,
                        ),
                        array( '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d' )
                    );
				} else {
					$dbhandler->update_row( 'PAYPAL_LOG', 'txn_id', $trasaction_id, array( 'log'=>$log_array ), array( '%s' ), '%s' );
				} // Save and update the logs array
				$paypal_log_id = $dbhandler->get_value( 'PAYPAL_LOG', 'id', $trasaction_id, 'txn_id' );
				if ( $payment_status=='completed' && $this->pg_validate_completed_ipn( $post, $gid, $trasaction_id ) ) { // validate the IPN, do the others stuffs here as per your app logic
                                        $group_payment_array = maybe_unserialize(get_user_meta($user_id,'pm_group_payment_status', true));
                                        if(empty($group_payment_array))
                                        {
                                            $group_payment_array = array();
                                        }
                                        $group_payment_array[$gid] = $payment_status;
                                        update_user_meta( $user_id, 'pm_group_payment_status', $group_payment_array );
					update_user_meta( $user_id, 'pm_txn_id_' . $gid, $trasaction_id );
					update_user_meta( $user_id, 'pm_paypal_log_id_' . $gid, $paypal_log_id );
					update_user_meta( $user_id, 'pm_user_payment_status_' . $gid, $payment_status );
					update_user_meta( $user_id, 'pm_invoice_' . $gid, $invoice );
					$subject = __( 'Instant Payment Notification - Recieved Payment', 'profilegrid-user-profiles-groups-and-communities' );
					// Send the notification about the transaction
                                        $pmrequests->profile_magic_join_group_fun( $user_id, $gid, $type );
                                        do_action( 'profilegrid_payment_complete', $gid, $user_id );
					
				} else {
					$subject = __( 'Instant Payment Notification - Payment Fail', 'profilegrid-user-profiles-groups-and-communities' );
					do_action( 'profilegrid_payment_failed', $gid, $user_id );
				}
			    break;
		}
	}

}
