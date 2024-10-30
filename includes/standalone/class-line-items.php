<?php
/*!
 * Line items object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Line Items
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the line items object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Line_Items extends InvoiceEM_Wrapper
{
	/**
	 * List of entries. Each entry contains the following indexes:
	 * i: List Item ID (integer)
	 * u: User ID (integer)
	 * d: Date (integer)
	 * t: Title (string)
	 * e: Description (string)
	 * a: Taxes (array)
	 * q: Quantity (integer)
	 * t: Quantity Type (string)
	 * r: Rate (float)
	 * j: Adjustment (float)
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    array
	 */
	private $_entries;
	
	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $events Raw list of events.
	 * @return void
	 */
	public function __construct($entries = array())
	{
		parent::__construct();
		
		if
		(
			empty($entries)
			&&
			isset($_POST['line_items'])
			&&
			is_array($_POST['line_items'])
		)
		{
			$this->_post_entries();
		}
		else
		{
			$this->_entries = maybe_unserialize($entries);

			if (!is_array($this->_entries))
			{
				$this->_entries = array();
			}
		}
	}
	
	/**
	 * Calculate the totals for all of the line items.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $row Raw invoice row.
	 * @return array      Array containing the line items and totals.
	 */
	public function calculate_totals($row)
	{
		$accounting_settings = InvoiceEM_Client::accounting_settings($row[InvoiceEM_Client::ID_COLUMN]);
		
		if
		(
			!isset($row['taxes'])
			||
			empty($row['taxes'])
		)
		{
			$row['taxes'] = maybe_unserialize($accounting_settings['taxes']);
		}
		else
		{
			$row['taxes'] = maybe_unserialize($row['taxes']);
		}
		
		$precision = $accounting_settings['currency']['precision'];
		$has_tax = false;
		$subtotal = $pre_tax_discount = $tax = $discount = 0;
		$lines = $taxes = array();
		
		foreach ($this->_entries as $i => $entry)
		{
			if (is_array($row['taxes']))
			{
				$entry['r'] = round($entry['r'], $precision);
				$inclusive_rate = 0;

				foreach ($row['taxes'] as $j => $details)
				{
					if
					(
						is_array($entry['a'])
						&&
						in_array($j, $entry['a'])
						&&
						is_numeric($details['r'])
					)
					{
						$has_tax = true;

						if ($details['i'])
						{
							$inclusive_rate += $details['r'] / 100;
						}
					}
				}

				if ($inclusive_rate > 0)
				{
					$entry['r'] = round($entry['r'] / (1 + $inclusive_rate), $precision);
				}

				$line_subtotal = round($entry['q'] * $entry['r'], $precision);
				$adjustment = 0;

				if (is_numeric($entry['j']))
				{
					$adjustment = round($line_subtotal * ($entry['j'] / 100), $precision);
					$line_subtotal += $adjustment;
				}

				$lines[$i] = array
				(
					'date' => (is_numeric($entry['d']))
					? $entry['d']
					: '',

					'title' => $entry['t'],
					'description' =>$entry['e'],
					'quantity' => $entry['q'],
					'type' => $entry['y'],
					'rate' => $entry['r'],
					'adjustment' => $adjustment,
					'subtotal' => $line_subtotal,
					'discounted_subtotal' => $line_subtotal,
					'taxes' => $entry['a']
				);

				$subtotal += $line_subtotal;
			}
		}
		
		if ($has_tax)
		{
			if
			(
				isset($row['pre_tax_discount'])
				&&
				!empty($row['pre_tax_discount'])
			)
			{
				$pre_tax_discount = round(InvoiceEM_Utilities::calculate_value($row['pre_tax_discount'], $subtotal) * -1, $precision);
				
				foreach ($lines as $i => $line)
				{
					$lines[$i]['discounted_subtotal'] = round($line['subtotal'] + (($line['subtotal'] / $subtotal) * $pre_tax_discount), $precision);
				}
			}
			
			foreach ($row['taxes'] as $i => $details)
			{
				$tax_rate = $details['r'] / 100;
				$current_tax = 0;

				foreach ($lines as $line)
				{
					if
					(
						is_array($line['taxes'])
						&&
						in_array($i, $line['taxes'])
					)
					{
						$current_tax += $tax_rate * $line['discounted_subtotal'];
					}
				}
				
				if ($current_tax > 0)
				{
					$taxes[] = array
					(
						'label' => $details['l'],
						'tax' => round($current_tax, $precision)
					);
				}
			}
		}
		
		foreach ($taxes as $details)
		{
			$tax += $details['tax'];
		}
		
		if
		(
			isset($row['discount'])
			&&
			!empty($row['discount'])
		)
		{
			$discount = round(InvoiceEM_Utilities::calculate_value($row['discount'], $subtotal + $pre_tax_discount + $tax) * -1, $precision);
		}
		
		return array
		(
			'lines' => $lines,
			'subtotal' => $subtotal,
			'pre_tax_discount' => $pre_tax_discount,
			'taxes' => $taxes,
			'discount' => $discount,
			'total' => round($subtotal + $pre_tax_discount + $tax + $discount, $precision)
		);
	}
	
	/**
	 * Generate an entry from a raw line item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array   $line_item Raw line item contining the entry details.
	 * @param  boolean $add_entry True if the entry should be added directly.
	 * @return array              Generated entry array.
	 */
	public function generate_entry($line_item, $add_entry = false)
	{
		$new_rate = (isset($line_item['rate']))
		? InvoiceEM_Utilities::unformat_currency($line_item['rate'])
		: '';
		
		$entry = array
		(
			'i' =>
			(
				!isset($line_item['line_item_id'])
				||
				!is_numeric($line_item['line_item_id'])
			)
			? 0
			: esc_attr($line_item['line_item_id']),

			'u' =>
			(
				!isset($line_item['user_id'])
				||
				!is_numeric($line_item['user_id'])
			)
			? $current_user_id
			: esc_attr($line_item['user_id']),

			'd' =>
			(
				!isset($line_item['date'])
				||
				empty($line_item['date'])
			)
			? ''
			: strtotime(esc_attr($line_item['date'])),

			't' => (isset($line_item['title']))
			? sanitize_text_field($line_item['title'])
			: '',

			'e' => (isset($line_item['description']))
			? sanitize_textarea_field($line_item['description'])
			: '',

			'a' =>
			(
				!isset($line_item['taxes'])
				||
				!is_array($line_item['taxes'])
			)
			? array()
			: $line_item['taxes'],

			'q' =>
			(
				!isset($line_item['quantity'])
				||
				!is_numeric($line_item['quantity'])
			)
			? 0
			: esc_attr($line_item['quantity']),

			'y' => (isset($line_item['type']))
			? esc_attr($line_item['type'])
			: $this->base->settings->invoicing->quantity_type,

			'r' => $new_rate,

			'j' =>
			(
				!isset($line_item['adjustment'])
				||
				!is_numeric($line_item['adjustment'])
				||
				$line_item['adjustment'] == 0
			)
			? ''
			: esc_attr($line_item['adjustment'])
		);
		
		if ($add_entry)
		{
			$this->_entries[] = $entry;
			
			$this->_sort_entries();
		}
		
		return $entry;
	}
	
	/**
	 * Return the serialized line items.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string Serialized events
	 */
	public function get_serialized()
	{
		return (is_array($this->_entries))
		? maybe_serialize($this->_entries)
		: $this->_entries;
	}
	
	/**
	 * Return the line item entries for the invoice form.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Formatted line item entries.
	 */
	public function get_value()
	{
		$output = array();
		$date_format = get_option('date_format');
		
		foreach ($this->_entries as $entry)
		{
			$output[] = array
			(
				'order_index' => '',
				'line_item_id' => $entry['i'],
				'user_id' => $entry['u'],
				
				'date' => (is_numeric($entry['d']))
				? date_i18n($date_format, $entry['d'])
				: '',
				
				'title' => $entry['t'],
				'description' => $entry['e'],
				'taxes' => $entry['a'],
				'quantity' => $entry['q'],
				'type' => $entry['y'],
				'rate' => InvoiceEM_Utilities::format_currency($entry['r']),
				'adjustment' => $entry['j']
			);
		}
		
		return $output;
	}
	
	/**
	 * Add line item entries from the POST object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _post_entries()
	{
		$current_user_id = (is_user_logged_in())
		? get_current_user_id()
		: 0;
		
		foreach ($_POST['line_items'] as $line_item)
		{
			if (isset($line_item['order_index']))
			{
				$this->_entries[esc_attr($line_item['order_index'])] = $this->generate_entry($line_item);
			}
		}
		
		$this->_sort_entries();
	}
	
	/**
	 * Sort the current post entries.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _sort_entries()
	{
		$last_id = 0;
		
		foreach ($this->_entries as $index => $entry)
		{
			if ($this->_entries[$index]['i'] > $last_id)
			{
				$last_id = $this->_entries[$index]['i'];
			}
		}
		
		foreach ($this->_entries as $index => $entry)
		{
			if (empty($this->_entries[$index]['i']))
			{
				$this->_entries[$index]['i'] = ++$last_id;
			}
		}
		
		ksort($this->_entries);
	}
}
