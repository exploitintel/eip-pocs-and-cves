<?php
/**
 * PHP Paypal IPN Integration Class
 * 6.25.2008 - Eric Wang, http://code.google.com/p/paypal-ipn-class-php/
 *
 * This file provides neat and simple method to validate the paid result with Paypal IPN.
 * It's NOT intended to make the paypal integration "plug 'n' play".
 * It still requires the developer to understand the paypal process and know the variables
 * you want/need to pass to paypal to achieve what you want.
 *
 * @author      Eric Wang <eric.wzy@gmail.com>
 * @copyright  (C) 2008 - 2009 Eric.Wang
 *
 */
/** filename of the IPN log */
define( 'PM_LOG_FILE', 'ipn_results.log' );
define( 'PM_SSL_P_URL', 'https://www.paypal.com/cgi-bin/webscr' );
define( 'PM_SSL_SAND_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr' );
class profile_magic_paypal_class {

	private $profile_magic;
	private $version;
	private $ipn_status;                // holds the last status
	public $admin_mail;                 // receive the ipn status report pre transaction
	public $paypal_mail;
	public $from_mail;              // paypal account, if set, class need to verify receiver
	public $txn_id;                     // array: if the txn_id array existed, class need to verified the txn_id duplicate
	public $ipn_log;                    // bool: log IPN results to text file?
	private $ipn_response;              // holds the IPN response from paypal
	public $ipn_data = array();         // array contains the POST values for IPN
	private $fields  = array();          // array holds the fields to submit to paypal
	private $ipn_debug;                 // ipn_debug
	private $send_box;
        public $tax;
        public $sendbox;

	// initialization constructor.  Called when class is created.
	function __construct( $profile_magic = 'profilegrid-user-profiles-groups-and-communities', $version = '' ) {
		$this->profile_magic = $profile_magic;
		$this->version       = ( '' !== $version ) ? $version : ( defined( 'PROGRID_PLUGIN_VERSION' ) ? PROGRID_PLUGIN_VERSION : null );
		$this->ipn_status   = '';
		$this->admin_mail   = null;
		$this->from_mail    = null;
		$this->paypal_mail  = null;
		$this->txn_id       = null;
		$this->tax          = null;
		$this->ipn_log      = true;
		$this->ipn_response = '';
		$this->ipn_debug    = false;
		$this->sendbox      = 'no';
	}
	// adds a key=>value pair to the fields array, which is what will be
	// sent to paypal as POST variables.
	public function add_field( $field, $value ) {
		$this->fields[ "$field" ] = $value;
	}
	// this function actually generates an entire HTML page consisting of
	// a form with hidden elements which is submitted to paypal via the
	// BODY element's onLoad attribute.  We do this so that you can validate
	// any POST vars from you custom form before submitting to paypal.  So
	// basically, you'll have your own form which is submitted to your script
	// to validate the data, which in turn calls this function to create
	// another hidden form and submit to paypal.

	// The user will briefly see a message on the screen that reads:
	// "Please wait, your order is being processed..." and then immediately
	// is redirected to paypal.
	public function submit_paypal_post() {
		$paypal_url = ( $this->sendbox == '1' ) ? PM_SSL_SAND_URL : PM_SSL_P_URL;
                ob_start();
		echo "<html>\n";
		echo '<head><title>' . esc_html__( 'Processing Payment...', 'profilegrid-user-profiles-groups-and-communities' ) . "</title></head>\n";
		echo "<body onLoad=\"document.forms['paypal_form'].submit();\">\n";
		echo '<center><h2>' . esc_html__( 'Please wait, your order is being processed and you will be redirected to the paypal website.', 'profilegrid-user-profiles-groups-and-communities' ) . "</h2></center>\n";
		echo '<form method="post" name="paypal_form" ';
		echo 'action="' . esc_url( $paypal_url ) . "\">\n";
		if ( class_exists( 'Profile_Magic_Basic_Functions' ) ) {
			$basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
			if ( method_exists( $basic_function, 'pm_render_nonce_field' ) ) {
				$basic_function->pm_render_nonce_field( 'pm_payment_action', 'pm_payment_nonce' );
			}
		}
		if ( isset( $this->paypal_mail ) ) {
			echo '<input type="hidden" name="business" value="' . esc_attr( $this->paypal_mail ) . "\"/>\n";
        }
		foreach ( $this->fields as $name => $value ) {
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . "\"/>\n";
		}
		echo '<center><br/><br/>' . esc_html__( 'If you are not automatically redirected to paypal within 5 seconds...', 'profilegrid-user-profiles-groups-and-communities' ) . "<br/><br/>\n";
		echo '<input type="submit" value="' . esc_attr__( 'Click Here', 'profilegrid-user-profiles-groups-and-communities' ) . '"></center>';
		echo "\n";
		echo "</form>\n";
		echo "</body></html>\n";
                return ob_get_contents();
	}

	/**
	 * validate the IPN
	 *
	 * @return bool IPN validation result
	 */
	public function validate_ipn() {
		$this->ipn_data = array();
		$post = wp_unslash( $_POST );
		foreach ( $post as $value ) {
			if ( ! is_scalar( $value ) ) {
				return false;
			}
		}
		$is_sandbox = '1' === (string) $this->sendbox;
		$is_test = isset( $post['test_ipn'] ) && '1' === (string) $post['test_ipn'];
		if ( $is_sandbox !== $is_test || empty( $this->paypal_mail ) || empty( $post['receiver_email'] ) || empty( $post['txn_id'] ) ) {
			return false;
		}
		if ( strtolower( trim( $post['receiver_email'] ) ) !== strtolower( trim( $this->paypal_mail ) ) ) {
			return false;
		}
		if ( is_array( $this->txn_id ) && in_array( $post['txn_id'], $this->txn_id, true ) ) {
			return false;
		}
		$url = $is_sandbox ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
		// Send the original encoded notification back without sanitizing signed values.
		$raw_body = file_get_contents( 'php://input' );
		if ( ! is_string( $raw_body ) || '' === $raw_body ) {
			$raw_body = http_build_query( $post, '', '&' );
		}
		$response = wp_remote_post( $url, array(
			'timeout' => 30,
			'redirection' => 0,
			'sslverify' => true,
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body' => 'cmd=_notify-validate&' . $raw_body,
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) || 'VERIFIED' !== trim( wp_remote_retrieve_body( $response ) ) ) {
			$this->ipn_status = esc_html__( 'IPN Validation Failed', 'profilegrid-user-profiles-groups-and-communities' );
			return false;
		}
		$sanitizer = new PM_sanitizer();
		$this->ipn_data = $sanitizer->sanitize( $post );
		$this->ipn_status = esc_html__( 'IPN VERIFIED', 'profilegrid-user-profiles-groups-and-communities' );
		return true;
	}

	private function log_ipn_results( $success ) {
		$hostname = gethostbyaddr( filter_var( $_SERVER ['REMOTE_ADDR'], FILTER_VALIDATE_IP ) );
		// Timestamp
		$text = '[' . gmdate( 'm/d/Y g:i A' ) . '] - ';
		// Success or failure being logged?
		if ( $success ) {
			$this->ipn_status = $text . 'SUCCESS:' . $this->ipn_status . "\r\n";
		} else {
			$this->ipn_status = $text . 'FAIL: ' . $this->ipn_status . "\r\n";
        }
			// Log the POST variables
		$this->ipn_status .= '[From:' . $hostname . '|' . filter_var( $_SERVER ['REMOTE_ADDR'], FILTER_VALIDATE_IP ) . "]IPN POST Vars Received By Paypal_IPN Response API:\r\n";
		foreach ( $this->ipn_data as $key => $value ) {
			$this->ipn_status .= "<p>$key=$value </p>\r\n";
		}
		// Log the response from the paypal server
		$this->ipn_status .=  esc_html__( 'IPN Response from Paypal Server:', 'profilegrid-user-profiles-groups-and-communities' ) . '\r\n' . $this->ipn_response;
		$this->write_to_log();
	}

	private function write_to_log() {
		if ( ! $this->ipn_log ) {
			return; // is logging turned off?
        }

		if ( file_exists( PM_LOG_FILE ) ) {
			unlink( PM_LOG_FILE );
		}
                    return;

                // Write to log
		$fp = fopen( PM_LOG_FILE, 'w' );
		fwrite( $fp, '' );
		fclose( $fp ); // close file
		chmod( PM_LOG_FILE, 0600 );
	}
	public function send_report( $subject ) {
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers .= 'From:' . $this->from_mail . "\r\n";
		$body    .=  esc_html__( 'from ', 'profilegrid-user-profiles-groups-and-communities' ) . esc_html( $this->ipn_data ['payer_email'] ) . esc_html__( ' on ', 'profilegrid-user-profiles-groups-and-communities' ) . gmdate( 'm/d/Y' );
		$body    .=  esc_html__( ' at ', 'profilegrid-user-profiles-groups-and-communities' ) . gmdate( 'g:i A' ) . "\n\n" . esc_html__( 'Details:', 'profilegrid-user-profiles-groups-and-communities' ) . "\n" . $this->ipn_status;

		wp_mail( $this->admin_mail, $subject, $body, $headers );
	}
	public function print_report() {
		$find []      = "\n";
		$replace []   = '<br/>';
		$html_content = str_replace( $find, $replace, $this->ipn_status );
		echo wp_kses_post( $html_content );
	}

	public function dump_fields() {

	}
	private function debug( $msg ) {
		if ( ! $this->ipn_debug ) {
			return;
        }

		$today  =  gmdate( 'Y-m-d H:i:s ' );
		$myFile = 'ipn_debugs.log';

		if ( file_exists( $myFile ) ) {
			@unlink( $myFile );
		}
                return;

		$fh = fopen( $myFile, 'w' ) or die( esc_html__( "Can't open debug file. Please manually create the 'debug.log' file and make it writable.", 'profilegrid-user-profiles-groups-and-communities' ) );

		fwrite( $fh, '' );
		fclose( $fh );
		chmod( $myFile, 0600 );

	}
}
