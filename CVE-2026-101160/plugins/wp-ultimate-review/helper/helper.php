<?php

namespace WurReview\Helper;

use WurReview\Utilities\Whip_Ip_Validator\Whip;

defined('ABSPATH') || exit;


class Helper {

	public static function avarage_loop($review, $limit) {

		return $review * (100 / $limit);
	}

	public static function avarage_final($loop, $limit, $avarage) {

		if ($loop == 0 || $limit == 0) {
			return 0;
		}
		return $limit * ($avarage / $loop / 100);
	}

	/**
	 * get request client IP address
	 * Security Fix: CVE-2024-32708 - IP Spoofing Vulnerability
	 * By default, only trust REMOTE_ADDR to prevent authentication bypass by spoofing.
	 * Proxy headers (CF-Connecting-IP, X-Forwarded-For, etc.) can be easily 
	 * spoofed by attackers to bypass IP-based rate limiting.
	 * 
	 * Users can optionally enable proxy header detection if they trust their 
	 * proxy infrastructure (e.g., behind CloudFlare with proper configuration).
	 * 
	 * @return mixed
	 */
	public static function ip_address() {
		//A third party library used from https://github.com/Vectorface/whip
		
		// Get the IP detection method from settings (default to secure REMOTE_ADDR)
		$global_settings = get_option('xs_review_global', []);
		$ip_detection_method = isset($global_settings['ip_detection_method']) ? $global_settings['ip_detection_method'] : 'remote_addr';
		
		// Security: Default to REMOTE_ADDR only - cannot be spoofed by clients
		// Only enable proxy headers if explicitly configured by admin
		if ($ip_detection_method === 'proxy_headers') {
			// Allow CloudFlare and other proxy headers (user opted-in)
			// WARNING: This can be bypassed by spoofing headers
			$whip = new Whip(Whip::CLOUDFLARE_HEADERS | Whip::REMOTE_ADDR);
		} else {
			// Secure default: Only use REMOTE_ADDR
			$whip = new Whip(Whip::REMOTE_ADDR);
		}
		
		$clientAddress = $whip->getValidIpAddress();

		return $clientAddress ?? null;
	}

	/**
	 * todo - remove all usage of \WurReview\Init::$controls; later
	 *
	 * @return array
	 */
	public static function get_review_form_config() {

		return [
			'xs_reviwer_ratting' => [
				'title_name' => esc_html__('Rating', 'wp-ultimate-review'),
				'type'       => 'select',
				'id'         => 'xs_ratting_id',
				'require'    => 'Yes',
				'class'      => 'xs_rating_class',
				'options'    => [
					'1' => '1 Star',
					'2' => '2 Star',
					'3' => '3 Star',
					'4' => '4 Star',
					'5' => '5 Star',
				],
			],
			'xs_reviw_title'     => [
				'title_name' => esc_html__('Review Title', 'wp-ultimate-review'),
				'type'       => 'text',
				'require'    => 'Yes',
				'options'    => [],
			],

			'xs_reviwer_name'    => [
				'title_name' => esc_html__('Reviewer Name', 'wp-ultimate-review'),
				'type'       => 'text',
				'require'    => 'No',
				'options'    => [],
			],
			'xs_reviwer_email'   => [
				'title_name' => esc_html__('Reviewer Email', 'wp-ultimate-review'),
				'type'       => 'text',
				'require'    => 'Yes',
				'options'    => [],
			],
			'xs_reviwer_website' => [
				'title_name' => esc_html__('Website', 'wp-ultimate-review'),
				'type'       => 'text',
				'require'    => 'No',
				'options'    => [],
			],
			'xs_reviw_summery'   => [
				'title_name' => esc_html__('Review Summary', 'wp-ultimate-review'),
				'type'       => 'textarea',
				'require'    => 'Yes',
				'options'    => [],
			],
		];
	}
	public static function _decode_json_unicode($json_string = '') {
		if (empty($json_string)) {
			return null;
		}
		
		// First decode the JSON with proper flags
		$decoded = json_decode($json_string, false, 512, JSON_UNESCAPED_UNICODE);
		
		// If decoding was successful, process the result recursively
		if ($decoded !== null) {
			return self::_process_unicode_data($decoded);
		}
		
		return $decoded;
	}

	private static function _process_unicode_data($data) {
		if (is_object($data)) {
			foreach ($data as $key => $value) {
				if (is_string($value)) {
					$data->$key = self::_fix_unicode_string($value);
				} elseif (is_object($value) || is_array($value)) {
					$data->$key = self::_process_unicode_data($value);
				}
			}
		} elseif (is_array($data)) {
			foreach ($data as $key => $value) {
				if (is_string($value)) {
					$data[$key] = self::_fix_unicode_string($value);
				} elseif (is_object($value) || is_array($value)) {
					$data[$key] = self::_process_unicode_data($value);
				}
			}
		}
		
		return $data;
	}

	private static function _fix_unicode_string($string) {
		// Handle emoji surrogate pairs FIRST (like ud83dude0a for emojis)
		$string = preg_replace_callback('/u([0-9a-fA-F]{4})u([0-9a-fA-F]{4})/', function($matches) {
			// This is a surrogate pair for emojis
			$high = hexdec($matches[1]);
			$low = hexdec($matches[2]);
			
			if ($high >= 0xD800 && $high <= 0xDBFF && $low >= 0xDC00 && $low <= 0xDFFF) {
				// Valid surrogate pair
				$codepoint = 0x10000 + (($high & 0x3FF) << 10) + ($low & 0x3FF);
				return mb_chr($codepoint, 'UTF-8');
			}
			
			// If not a valid surrogate pair, handle individually
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE') . 
				   mb_convert_encoding(pack('H*', $matches[2]), 'UTF-8', 'UCS-2BE');
		}, $string);
		
		// Handle Unicode sequences with backslashes (like \u00c1)
		$string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
		}, $string);
		
		// Handle remaining single Unicode sequences without backslashes (like u00c1)
		$string = preg_replace_callback('/u([0-9a-fA-F]{4})/', function($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
		}, $string);
		
		// Handle other JSON escape sequences
		$string = str_replace(['\\"', '\\/', '\\\\', '\\b', '\\f', '\\n', '\\r', '\\t'], 
						 ['"', '/', '\\', "\b", "\f", "\n", "\r", "\t"], $string);
		
		// Ensure proper UTF-8 encoding
		if (!mb_check_encoding($string, 'UTF-8')) {
			$string = mb_convert_encoding($string, 'UTF-8', 'auto');
		}
		
		return $string;
	}
}
