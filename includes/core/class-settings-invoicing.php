<?php
/*!
 * Invoicing settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Invoicing Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the invoicing settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Settings_Wrapper
 */
final class InvoiceEM_Settings_Invoicing extends InvoiceEM_Settings_Wrapper
{
	/**
	 * Tab slug for the invoicing settings.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = 'invoicing';

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		add_action(InvoiceEM_Constants::HOOK_SETTINGS_TABS, array($this, 'settings_tabs'));
	}
	
	/**
	 * Get a default value based on the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $name Name of the value to return.
	 * @return mixed        Default value if it exists, otherwise an empty string.
	 */
	protected function _default($name)
	{
		switch ($name)
		{
			/**
			 * Rewrite base for viewing invoices.
			 *
			 * @since 1.0.0
			 *
			 * @varstring 
			 */
			case 'rewrite_base':
			
				return 'invoiceem';
			
			/**
			 * Default title displayed at the top of invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_title':
			
				return __('Invoice', 'invoiceem');
				
			/**
			 * Default prefix used in invoice numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'prefix':
			
				return 'INVEM';
			
			/**
			 * Default payment due date for invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var mixed
			 */
			case 'payment_due':
			
				return 30;
			
			/**
			 * Company tax settings.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'taxes':
			
				return array();
			
			/**
			 * True if the date on line items should default to current date.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'default_date':
			
				return true;
			
			/**
			 * Default quantity type for line items.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'quantity_type':
			
				return 'h';
			
			/**
			 * Note displayed directly below the line items on invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'footer_note':
			
				return '';
				
			/**
			 * Note displayed at the very bottom of invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'footer_thank_you':
			
				return __('Thank you for your business!', 'invoiceem');
		}
		
		return parent::_default($name);
	}

	/**
	 * Add the invoicing settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_tabs()
	{
		parent::_add_tab(__('Invoicing', 'invoiceem'));
	}

	/**
	 * Sanitize the invoicing settings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $input Raw invoicing settings array.
	 * @return array        Sanitized invoicing settings array.
	 */
	public function sanitize($input)
	{
		if
		(
			!is_array($input)
			||
			empty($input)
		)
		{
			return array();
		}
		
		$input['default_date'] =
		(
			isset($input['default_date'])
			&&
			!empty($input['default_date'])
		);

		foreach ($input as $name => $value)
		{
			if ($name == 'rewrite_base')
			{
				$input[$name] = sanitize_key($value);
			}
			else if ($name == 'taxes')
			{
				$input[$name] = $this->sanitize_taxes($value);
			}
			else if
			(
				$name == 'footer_note'
				||
				$name == 'footer_thank_you'
			)
			{
				$input[$name] = sanitize_textarea_field($value);
			}
			else if ($name != 'default_date')
			{
				$input[$name] = sanitize_text_field($value);
			}
		}
		
		return $input;
	}

	/**
	 * Add meta boxes to the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes()
	{
		$invoice_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'invoice_settings',
			'option_name' => $this->_option_name,
			'title' => __('Invoice Settings', 'invoiceem')
		));

		$invoice_settings_box->add_field(array
		(
			'description' => __('Rewrite base used for viewing invoices.', 'invoiceem'),
			'label' => __('Rewrite Base', 'invoiceem'),
			'name' => 'rewrite_base',
			'type' => 'text',
			'value' => $this->rewrite_base,

			'attributes' => array
			(
				'maxlength' => 64
			),
			
			'validation' => array
			(
				'required' => true
			)
		));

		$invoice_settings_box->add_field(array
		(
			'description' => __('Primary invoice title.', 'invoiceem'),
			'label' => __('Invoice Title', 'invoiceem'),
			'name' => 'invoice_title',
			'type' => 'text',
			'value' => $this->invoice_title,

			'attributes' => array
			(
				'maxlength' => 255
			),
			
			'validation' => array
			(
				'required' => true
			)
		));

		$invoice_settings_box->add_field(array
		(
			'description' => __('Invoice number prefix.', 'invoiceem'),
			'label' => __('Prefix', 'invoiceem'),
			'name' => 'prefix',
			'type' => 'text',
			'value' => $this->prefix,

			'attributes' => array
			(
				'maxlength' => 8
			),
			
			'validation' => array
			(
				'required' => true
			)
		));
		
		$invoice_settings_box->add_field(array
		(
			'description' => __('Default due date for invoice payments.', 'invoiceem'),
			'label' => __('Payment Due', 'invoiceem'),
			'name' => 'payment_due',
			'options' => $this->get_payment_due_options(),
			'type' => 'select',
			'value' => $this->payment_due,
			
			'validation' => array
			(
				'required' => true
			)
		));
		
		$save_all_field = array
		(
			'content' => __('Save All Invoicing Settings', 'invoiceem'),
			'type' => 'submit'
		);
		
		$invoice_settings_box->add_field($save_all_field);
		
		$tax_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'tax_settings_box',
			'option_name' => $this->_option_name,
			'title' => __('Tax Settings', 'invoiceem')
		));
		
		$tax_settings_box->add_field($this->taxes_field());
		$tax_settings_box->add_field($save_all_field);
		
		$line_item_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'line_item_settings_box',
			'option_name' => $this->_option_name,
			'title' => __('Line Item Settings', 'invoiceem')
		));
		
		$line_item_settings_box->add_field(array
		(
			'description' => __('The date field on line items should default to the current date.', 'invoiceem'),
			'label' => __('Default Date', 'invoiceem'),
			'name' => 'default_date',
			'type' => 'checkbox',
			'value' => $this->default_date
		));
		
		$line_item_settings_box->add_field(array
		(
			'description' => __('Default quantity type for invoice line items.', 'invoiceem'),
			'label' => __('Quantity Type', 'invoiceem'),
			'name' => 'quantity_type',
			'options' => $this->get_quantity_types(),
			'type' => 'select',
			'value' => $this->quantity_type
		));
		
		$line_item_settings_box->add_field($save_all_field);
		
		$footer_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'footer_settings',
			'option_name' => $this->_option_name,
			'title' => __('Footer Settings', 'invoiceem')
		));
		
		$footer_settings_box->add_field(array
		(
			'description' => __('Note displayed directly below the line items.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Footer Note', 'invoiceem'),
			'name' => 'footer_note',
			'type' => 'textarea',
			'value' => $this->footer_note,

			'attributes' => array
			(
				'rows' => 3,
				
				'placeholder' => sprintf
				(
					__('Please make checks payable to %1$s. Overdue invoices are subject to a monthly 2.5%% interest fee.', 'invoiceem'),
					get_bloginfo('site_name')
				)
			)
		));
		
		$footer_settings_box->add_field(array
		(
			'description' => __('Note displayed at the very bottom of invoices.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Footer Thank You', 'invoiceem'),
			'name' => 'footer_thank_you',
			'type' => 'textarea',
			'value' => $this->footer_thank_you,

			'attributes' => array
			(
				'placeholder' => __('Thank you for your business!', 'invoiceem'),
				'rows' => 3
			)
		));
		
		$footer_settings_box->add_field($save_all_field);

		InvoiceEM_Meta_Box::side_meta_boxes();
	}
	
	/**
	 * Get options for the payment due date.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $has_default True if the default payment due option should be used.
	 * @return array                Payment due options.
	 */
	public function get_payment_due_options($use_default = false)
	{
		$active = ($use_default)
		? $this->payment_due
		: '';
		
		$payment_due_options = array();
		$day_options = array(-1, 0, 7, 14, 21, 30, 45, 60, 90, 120);
		$days_label = __('%d Days After Invoice Date', 'invoiceem');
		
		foreach ($day_options as $day_option)
		{
			if ($day_option < 0)
			{
				$payment_due_options[$day_option] = __('Exclude Due Date', 'invoiceem');
			}
			else if ($day_option == 0)
			{
				$payment_due_options[$day_option] = __('Upon Receipt', 'invoiceem');
			}
			else
			{
				$payment_due_options[$day_option] = sprintf
				(
					$days_label,
					$day_option
				);
			}
		}
		
		return $payment_due_options;
	}
	
	/**
	 * Get the label for a quantity type.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Line item quantity types.
	 */
	public function get_quantity_type_label($index, $count)
	{
		$count =
		(
			$count > 0
			&&
			$count <= 1
		)
		? 1
		: $count;
		
		switch ($index)
		{
			case 'm':
			
				return _n('%1$s Minute', '%1$s Minutes', $count, 'invoiceem');
				
			case 'h':
			
				return _n('%1$s Hour', '%1$s Hours', $count, 'invoiceem');
				
			case 'j':
			
				return _n('%1$s Day', '%1$s Days', $count, 'invoiceem');
				
			case 'w':
			
				return _n('%1$s Week', '%1$s Weeks', $count, 'invoiceem');
				
			case 'n':
			
				return _n('%1$s Month', '%1$s Months', $count, 'invoiceem');
				
			case 'y':
			
				return _n('%1$s Year', '%1$s Years', $count, 'invoiceem');
		}
		
		return '%1$s';
	}
	
	/**
	 * Get options for the quantity types.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Line item quantity types.
	 */
	public function get_quantity_types()
	{
		return array
		(
			'd' => __('Disable', 'invoiceem'),
			'm' => __('Minute(s)', 'invoiceem'),
			'h' => __('Hour(s)', 'invoiceem'),
			'j' => __('Day(s)', 'invoiceem'),
			'w' => __('Week(s)', 'invoiceem'),
			'n' => __('Month(s)', 'invoiceem'),
			'y' => __('Year(s)', 'invoiceem')
		);
	}
	
	/**
	 * Sanitize the repeatable taxes field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $taxes_raw Raw repeatable taxes field value.
	 * @return array            Sanitized taxes value.
	 */
	public function sanitize_taxes($taxes_raw)
	{
		$taxes = array();

		if (is_array($taxes_raw))
		{
			foreach ($taxes_raw as $tax)
			{
				$taxes[$tax['o']] = array
				(
					'l' => sanitize_text_field($tax['l']),

					'r' =>
					(
						is_numeric($tax['r'])
						&&
						$tax['r'] > 0
					)
					? esc_attr($tax['r'])
					: 10,
					
					'i' =>
					(
						isset($tax['i'])
						&&
						!empty($tax['i'])
					)
				);
			}
		}

		ksort($taxes);

		return $taxes;
	}
	
	/**
	 * Generate the repeatable taxes field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $value Current value for the repeatable taxes field.
	 * @return array        Repeatable texes field.
	 */
	public function taxes_field($value = null)
	{
		return array
		(
			'add_item' => __('Add Tax', 'invoiceem'),
			'is_simple' => true,
			'name' => 'taxes',
			'type' => 'repeatable',
			
			'repeatable_field' => array
			(
				'hide_labels' => true,
				'type' => 'group',

				'fields' => array
				(
					array
					(
						'classes' => array('iem-hidden'),
						'input_classes' => array('iem-order-index'),
						'name' => 'o',
						'type' => 'hidden'
					),
					
					array
					(
						'classes' => array('iem-col-sm-6 iem-col-xs-12'),
						'description' => __('Standard label for the tax.', 'invoiceem'),
						'input_classes' => array('required'),
						'name' => 'l',
						'type' => 'text',

						'attributes' => array
						(
							'maxlength' => 32,
							'placeholder' => __('Label', 'invoiceem')
						)
					),
					
					array
					(
						'classes' => array('iem-col-sm-3 iem-col-xs-6'),
						'description' => __('Detault rate for this tax.', 'invoiceem'),
						'input_classes' => array('iem-calculate', 'iem-spinner', 'required'),
						'name' => 'r',
						'type' => 'text',

						'attributes' => array
						(
							'data-iem-max' => 99.99,
							'data-iem-min' => 0.01,
							'data-iem-number-format' => 'n',
							'data-iem-step' => 0.01,
							'maxlength' => 5,
							'placeholder' => __('Rate (%)', 'invoiceem')
						)
					),
					
					array
					(
						'classes' => array('iem-col-sm-3 iem-col-xs-6'),
						'description' => __('Prices entered already include this tax.', 'invoiceem'),
						'input_classes' => array('iem-calculate'),
						'name' => 'i',
						'type' => 'checkbox',

						'attributes' => array
						(
							'placeholder' => __('Inclusive', 'invoiceem')
						)
					)
				)
			),
			
			'value' =>
			(
				!is_array($value)
				||
				empty($value)
			)
			? $this->taxes
			: $value
		);
	}
}
