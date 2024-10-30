<?php
/*!
 * Plugin utility functions.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Utilities
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement utility functions.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Utilities
{
	/**
	 * Make an API call for paid extensions.
	 *
	 * @since 1.0.3 Updated license key storage.
	 * @since 1.0.1 Changed variable names.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $url         URL used for the API call.
	 * @param  integer $item_id     ID for the extention used for the API call.
	 * @param  string  $option_name Name of the option that contains the license key.
	 * @param  string  $license_key License key to check.
	 * @param  string  $edd_action  API call action to take.
	 * @return array                Response details for the API call.
	 */
	public static function api_call($url, $item_id, $option_name, $license_key = '', $edd_action = '')
	{
		$output = array
		(
			'notice' => '',
			'success' => false
		);
		
		if
		(
			empty($license_key)
			&&
			isset($_POST['iem-license-key'])
			&&
			!empty($_POST['iem-license-key'])
		)
		{
			$license_key = sanitize_text_field($_POST['iem-license-key']);
		}
		
		if (!empty($license_key))
		{
			if
			(
				empty($edd_action)
				&&
				isset($_POST['iem-edd-action'])
				&&
				!empty($_POST['iem-edd-action'])
			)
			{
				$edd_action = sanitize_text_field($_POST['iem-edd-action']);
			}

			$response = wp_remote_post
			(
				$url,

				array
				(
					'sslverify' => true,
					'timeout' => 15,

					'body' => array
					(
						'item_id' => $item_id,
						'license' => $license_key,
						'url' => home_url(),

						'edd_action' => ($edd_action == 'cl')
						? 'check_license'
						: $edd_action
					)
				)
			);

			$body =
			(
				is_wp_error($response)
				||
				wp_remote_retrieve_response_code($response) !== 200
			)
			? ''
			: json_decode(wp_remote_retrieve_body($response));

			if
			(
				!empty($body)
				&&
				isset($body->license)
			)
			{
				$option_value = InvoiceEM_Utilities::check_array(maybe_unserialize(base64_decode(get_option($option_name))));
				$option_value['k'] = $license_key;
				
				if (isset($body->expires))
				{
					$option_value['e'] = strtotime($body->expires);
				}

				if ($body->success)
				{
					if ($body->license == 'valid')
					{
						$output['notice'] = InvoiceEM_Output::admin_notice(sprintf
						(
							__('License key for %1$s activated successfully.', 'invoiceem'),
							$body->item_name
						));
					}
					else
					{
						$option_value['e'] = 0;
						
						$output['notice'] = InvoiceEM_Output::admin_notice(sprintf
						(
							__('License key for %1$s deactivated successfully.', 'invoiceem'),
							$body->item_name
						));
					}
					
					$output['success'] = true;
				}
				else
				{
					$message = '';
					
					switch ($body->license)
					{
						case 'expired':

							$message =  sprintf
							(
								__('Your license key for %1$s expired on %2$s.', 'invoiceem'),
								'%1$s',
								date_i18n(get_option('date_format'), $option_value['e'])
							);

						break;

						case 'failed':

							$message = __('Your license key for %1$s could not be activated at this time.', 'invoiceem');

						break;

						case 'invalid':
						case 'missing':

							$message = __('Invalid license key for %1$s provided.', 'invoiceem');

						break;

						case 'item_name_mismatch':

							$message = __('The license key provided is invalid for %1$s.', 'invoiceem');

						break;

						case 'no_activations_left':

							$message = __('Your license key for %1$s has reached its activation limit.', 'invoiceem');

						break;

						case 'revoked':

							$message = __('Your license key for %1$s has been disabled.', 'invoiceem');

						break;

						case 'site_inactive':

							$message = __('Your license key for %1$s is not active for this URL.', 'invoiceem');

						break;
					}

					if (!empty($message))
					{
						$output['notice'] = InvoiceEM_Output::admin_notice
						(
							sprintf
							(
								$message,
								$body->item_name
							),

							InvoiceEM_Constants::NOTICE_ERROR
						);
					}
				}

				update_option($option_name, base64_encode(maybe_serialize($option_value)));
			}
		}

		if
		(
			empty($output['notice'])
			&&
			!$output['success']
		)
		{
			$output['notice'] = InvoiceEM_Output::admin_notice(__('An unexpected error occurred, please try again later.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		
		return $output;
	}
	
	/**
	 * Calculate a value from a whole number or percentage.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $amount Whole number of percentage to calculate.
	 * @param  float  $total  Total value to calculate again when the amount is a percentage.
	 * @return float          Calculated value.
	 */
	public static function calculate_value($amount, $total)
	{
		if (!is_numeric($total))
		{
			$total = 0;
		}
		
		return (is_numeric($amount))
		? $amount
		: $total * (preg_replace('/[^\d.]/u', '', $amount) / 100);
	}
	
	/**
	 * Check a value to see if it is an array or convert to an array if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  mixed $value        Value to turn into an array.
	 * @param  mixed $return_false True if a false value should be returned as-is.
	 * @return array               Checked value as an array.
	 */
	public static function check_array($value, $return_false = false)
	{
		if
		(
			$value === false
			&&
			$return_false
		)
		{
			return $value;
		}

		if (empty($value))
		{
			$value = array();
		}

		if (!is_array($value))
		{
			$value = array($value);
		}

		return $value;
	}

	/**
	 * Remove comments, line breaks and tabs from provided code.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $code Raw code to clean up.
	 * @return string       Code without comments, line breaks and tabs.
	 */
	public static function clean_code($code)
	{
		$code = preg_replace('/<!--(.*)-->/Uis', '', $code);
		$code = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '', $code);

		return str_replace(array(PHP_EOL, "\r", "\n", "\t"), '', $code);
	}
	
	/**
	 * Decrypt a provided string.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $number Raw string to decrypt.
	 * @return string         Decrypted string.
	 */
	public static function decrypt($string)
	{
		$string = base64_decode($string);
		
		if (function_exists('openssl_decrypt'))
		{
			$data = explode('::', $string, 2);
			
			$string = (count($data) == 2)
			? openssl_decrypt($data[0], InvoiceEM_Constants::ENCRYPTION_METHOD, AUTH_KEY, 0, $data[1])
			: $data[0];
		}
		
		return $string;
	}
	
	/**
	 * Encode provided text.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $text Raw text to encode.
	 * @return string       Encoded text.
	 */
	public static function encode_text($text)
	{
		return preg_replace_callback
		(
			'/[\x{80}-\x{10FFFF}]/u',
			
			function ($match)
			{
				$character = current($match);
				$converted = iconv('UTF-8', 'UCS-4', $character);
				
				return sprintf('&#x%s;', ltrim(strtoupper(bin2hex($converted)), '0'));
			},
			
			$text
		);
	}
	
	/**
	 * Encrypt a provided string.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $number Raw string to encrypt.
	 * @return string         Encrypted string.
	 */
	public static function encrypt($string)
	{
		if (function_exists('openssl_encrypt'))
		{
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(InvoiceEM_Constants::ENCRYPTION_METHOD));
			$string = openssl_encrypt($string, InvoiceEM_Constants::ENCRYPTION_METHOD, AUTH_KEY, 0, $iv) . '::' . $iv;
		}
		
		return base64_encode($string);
	}
	
	/**
	 * Check to see if a full string end with a specified string.
	 * 
	 * @since 1.0.0
	 * 
	 * @access public static
	 * @param  string  $needle   String to check for.
	 * @param  string  $haystack Full string to check.
	 * @return boolean           True if the full string ends with the specified string.
	 */
	public static function ends_with($needle, $haystack)
	{
		$length = strlen($needle);
		
		if ($length == 0)
		{
			return true;
		}
		
		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * Format a number into currency based on accounting settings.
	 *
	 * @since 1.0.5 Added support for custom number grouping.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $number   Raw number to format.
	 * @param  array   $settings Optional accounting settings. Defaults to the main company accounting settings if not provided.
	 * @param  boolean $encode   True if the output string should be encoded.
	 * @return string            Formatted currency number.
	 */
	public static function format_currency($number, $settings = null, $encode = false)
	{
		$number = (is_numeric($number))
		? $number
		: 0;

		$currency_settings =
		(
			is_array($settings)
			&&
			isset($settings['currency'])
		)
		? $settings['currency']
		: InvoiceEM()->cache->accounting['currency'];

		$format = $currency_settings['format']['zero'];

		if ($number > 0)
		{
			$format = $currency_settings['format']['pos'];
		}
		else if ($number < 0)
		{
			$format = $currency_settings['format']['neg'];
		}
		
		if (empty($currency_settings['grouping']))
		{
			$number = number_format(abs($number), $currency_settings['precision'], $currency_settings['decimal'], $currency_settings['thousand']);
		}
		else
		{
			$number_raw = explode($currency_settings['decimal'], '' . number_format(abs($number), $currency_settings['precision'], $currency_settings['decimal'], ''));
			$groups = array_reverse(explode(',', $currency_settings['grouping']));
			$group_count = count($groups);
			$group_index = 0;
			$grouped = '';
			
			do
			{
				if (!empty($grouped))
				{
					$grouped = $currency_settings['thousand'] . $grouped;
				}
				
				if (strlen($number_raw[0]) > $groups[$group_index])
				{
					$group = -1 * $groups[$group_index];
					$grouped = substr($number_raw[0], $group) . $grouped;
					$number_raw[0] = substr($number_raw[0], 0, $group);
				}
				else
				{
					$grouped = $number_raw[0] . $grouped;
					$number_raw[0] = '';
				}
				
				$group_index = ($group_index < $group_count - 1)
				? $group_index + 1
				: 0;
			}
			while (!empty($number_raw[0]));
			
			$number_raw[0] = $grouped;
			$number = implode($currency_settings['decimal'], $number_raw);
		}
		
		$output = str_replace(array('%s', '%v'), array($currency_settings['symbol'], $number), $format);
		
		return ($encode)
		? self::encode_text($output)
		: $output;
	}
	
	/**
	 * Localize and format a date.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $format   Output format for the date.
	 * @param  array  $raw_date Raw date or timestamp to format.
	 * @return string           Localized and formatted date.
	 */
	public static function format_date($format, $raw_date = '')
	{
		global $wp_locale;
		
		if (is_null($wp_locale))
		{
			wp_load_translations_early();
		}
		
		if (empty($raw_date))
		{
			$raw_date = time();
		}
		else if (!is_numeric($raw_date))
		{
			$raw_date = strtotime($raw_date);
		}
		
		$timezone = get_option('timezone_string');
		
		$timezone = (empty($timezone))
		? 'UTC'
		: $timezone;
		
		$date = new DateTime(null, new DateTimeZone($timezone));
		$date->setTimestamp($raw_date);
		
		$utc_date = new DateTime($date->format('Y-m-d H:i:s'), new DateTimeZone('UTC'));
		
		return date_i18n($format, $utc_date->getTimestamp(), true);
	}
	
	/**
	 * Generate an invoice number.
	 *
	 * @since 1.0.5
	 *
	 * @access public static
	 * @param  array   $invoice        Array of invoice values.
	 * @param  boolean $is_simple      True if the dynamic elements should be simplified.
	 * @param  boolean $is_placeholder True is the suffix should be included in the invoice number.
	 * @return string                  Generated invoice number.
	 */
	public static function generate_invoice_number($invoice, $is_simple = false, $is_placeholder = false)
	{
		$iem = InvoiceEM();
		
		if (!empty($invoice['invoice_number']))
		{
			return $invoice['invoice_number'];
		}
		else if
		(
			$iem->cache->has_invoices_plus
			&&
			!empty(IEM_Invoices_Plus()->settings->invoice_number_format)
		)
		{
			return IEM_Invoices_Plus_Utilities::generate_invoice_number($invoice, $is_simple, $is_placeholder);
		}
		
		$invoice_number = (empty($invoice['client_invoice_prefix']))
		? $iem->settings->invoicing->prefix
		: $invoice['client_invoice_prefix'];
		
		$invoice_number .= '-' . sprintf('%05d', $invoice[InvoiceEM_Invoice::ID_COLUMN]);
		
		return $invoice_number;
	}

	/**
	 * Check to see if a plugin is active.
	 *
	 * @since 1.0.2 Added additional check to make sure the plugin exists.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $path Path for the plugin to check.
	 * @return boolean       True if the plugin is active.
	 */
	public static function is_plugin_active($path)
	{
		if (!function_exists('is_plugin_active'))
		{
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		return
		(
			is_plugin_active($path)
			&&
			file_exists(WP_PLUGIN_DIR . '/' . $path)
		);
	}

	/**
	 * Load and decode JSON from a provided file path.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $file_path   Path to the JSON file.
	 * @param  string $plugin_path Path for the current plugin.
	 * @return string              Decoded JSON file.
	 */
	public static function load_json($file_path, $plugin_path = '')
	{
		if (empty($plugin_path))
		{
			$plugin_path = InvoiceEM()->plugin;
		}
		
		$file = plugin_dir_path($plugin_path) . $file_path;

		if (!file_exists($file))
		{
			return '';
		}

		ob_start();

		require($file);

		return json_decode(ob_get_clean(), true);
	}

	/**
	 * Modify the current admin URL.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  array  $add_args    Optional arguments to add to the query string.
	 * @param  array  $remove_args Optional arguments to remove from the query string.
	 * @param  string $url         Optional URL to modify instead of the current URL.
	 * @return string              Modified URL.
	 */
	public static function modify_admin_url($add_args = array(), $remove_args = array(), $url = '')
	{
		$remove_args = self::check_array($remove_args, true);

		$modified_url = (empty($url))
		? $_SERVER['REQUEST_URI']
		: $url;

		if (!empty($remove_args))
		{
			$modified_url = remove_query_arg($remove_args, $modified_url);
		}

		if
		(
			is_array($add_args)
			&&
			!empty($add_args)
		)
		{
			$modified_url = add_query_arg($add_args, $modified_url);
		}

		return esc_url_raw($modified_url);
	}

	/**
	 * Generate a plugin-specific nonce action.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $raw_action Action being taken.
	 * @param  integer $object_id  Optional ID of the object being processed.
	 * @return string              Generated nonce action.
	 */
	public static function nonce_action($raw_action, $object_id = 0)
	{
		$action = InvoiceEM_Constants::PREFIX . $raw_action;
		
		if (!empty($object_id))
		{
			$action .= '_' . $object_id;
		}

		return $action;
	}
	
	/**
	 * Check to see if a full string starts with a specified string.
	 * 
	 * @since 1.0.0
	 * 
	 * @access public static
	 * @param  string  $needle   String to check for.
	 * @param  string  $haystack Full string to check.
	 * @return boolean           True if the full string starts with the specified string.
	 */
	public static function starts_with($needle, $haystack)
	{
		return
		(
			empty($needle)
			||
			strpos($haystack, $needle) === 0
		);
	}
	
	/**
	 * Unformat currency for database storage.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $raw_currency Raw currency containing possible non-numeric characters.
	 * @return float                Unformatted number.
	 */
	public static function unformat_currency($raw_currency)
	{
		if (empty($raw_currency))
		{
			return 0;
		}
		
		$multiplier = 1;
		
		if (self::starts_with('-', $raw_currency))
		{
			$raw_currency = substr($raw_currency, 1);
			$multiplier = -1;
		}
		
		$number = preg_replace('/[^\d]/u', '.', $raw_currency);
		
		return (is_numeric($number))
		? $number * $multiplier
		: 0;
	}

	/**
	 * Verify a plugin-specific nonce.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $nonce     Nonce value to verify.
	 * @param  string  $action    Action being taken.
	 * @param  integer $object_id Optional ID of the object being processed.
	 * @return string             Modified URL.
	 */
	public static function verify_nonce($nonce, $action, $object_id = 0)
	{
		return wp_verify_nonce($nonce, self::nonce_action($action, $object_id));
	}
}
