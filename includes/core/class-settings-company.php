<?php
/*!
 * Company settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Company Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the company settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Settings_Wrapper
 */
final class InvoiceEM_Settings_Company extends InvoiceEM_Settings_Wrapper
{
	/**
	 * Tab slug for the company settings.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = 'company';

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
			 * ID of the country record for the company.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Country::ID_COLUMN:
			
				return 0;

			/**
			 * Name of the company.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'company_name':
			
			/**
			 * ID of the company logo image.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'logo_id':
			
				return '';

			/**
			 * ID of the currency record for the company.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Currency::ID_COLUMN:
			
				return 0;

			/**
			 * Standard rate for the company.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'rate':
			
				return 50;

			/**
			 * Date that company fiscal year starts.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'year_starts':
			
				return '';

			/**
			 * Company email address.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'email':
			
			/**
			 * Company address.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'address':
			
			/**
			 * Company phone number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'phone':
			
			/**
			 * Company fax number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'fax':
			
				return '';
		}

		return parent::_default($name);
	}

	/**
	 * Add the company settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_tabs()
	{
		parent::_add_tab(__('Company', 'invoiceem'));
	}

	/**
	 * Sanitize the company settings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $input Raw company settings array.
	 * @return array        Sanitized company settings array.
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

		foreach ($input as $name => $value)
		{
			if
			(
				$name == InvoiceEM_Country::ID_COLUMN
				||
				$name == InvoiceEM_Currency::ID_COLUMN
				||
				$name == 'logo_id'
				||
				$name == 'rate'
			)
			{
				$input[$name] =
				(
					is_numeric($value)
					&&
					$value > 0
				)
				? $value
				: 0;
			}
			else if ($name == 'address')
			{
				$input[$name] = sanitize_textarea_field($value);
			}
			else
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
		$company_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'company_settings',
			'option_name' => $this->_option_name,
			'title' => __('Company Settings', 'invoiceem')
		));

		$company_settings_box->add_field(array
		(
			'description' => __('Country that your company does business from.', 'invoiceem'),
			'label' => __('Country', 'invoiceem'),
			'name' => InvoiceEM_Country::ID_COLUMN,
			'options' => InvoiceEM_Country::selected_item($this->{InvoiceEM_Country::ID_COLUMN}),
			'table' => InvoiceEM_Constants::TABLE_COUNTRIES,
			'type' => 'select',
			'value' => $this->{InvoiceEM_Country::ID_COLUMN},

			'attributes' => array
			(
				'placeholder' => __('Select a Country', 'invoiceem')
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$company_settings_box->add_field(array
		(
			'description' => __('Name of your company.', 'invoiceem'),
			'label' => __('Company Name', 'invoiceem'),
			'name' => 'company_name',
			'type' => 'text',
			'value' => $this->company_name,

			'attributes' => array
			(
				'maxlength' => 255,
				'placeholder' => get_bloginfo('site_name')
			)
		));

		$company_settings_box->add_field(array
		(
			'description' => __('Logo for your company.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Logo', 'invoiceem'),
			'name' => 'logo_id',
			'type' => 'image',
			'value' => $this->logo_id,

			'attributes' => array
			(
				'data-iem-media-button' => __('Set Logo', 'invoiceem'),
				'data-iem-media-title' => __('Select Logo', 'invoiceem')
			)
		));

		$save_all_field = array
		(
			'content' => __('Save All Company Settings', 'invoiceem'),
			'type' => 'submit'
		);

		$company_settings_box->add_field($save_all_field);

		$billing_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'billing_settings',
			'option_name' => $this->_option_name,
			'title' => __('Billing Settings', 'invoiceem')
		));

		$billing_settings_box->add_field(array
		(
			'description' => __('Currency that your company uses.', 'invoiceem'),
			'input_classes' => array('iem-accounting'),
			'label' => __('Currency', 'invoiceem'),
			'name' => InvoiceEM_Currency::ID_COLUMN,
			'options' => InvoiceEM_Currency::selected_item($this->{InvoiceEM_Currency::ID_COLUMN}),
			'table' => InvoiceEM_Constants::TABLE_CURRENCIES,
			'type' => 'select',
			'value' => $this->{InvoiceEM_Currency::ID_COLUMN},

			'attributes' => array
			(
				'placeholder' => __('Select a Currency', 'invoiceem')
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$billing_settings_box->add_field(array
		(
			'description' => __('Standard company rate.', 'invoiceem'),
			'input_classes' => array('iem-currency'),
			'label' => __('Rate', 'invoiceem'),
			'name' => 'rate',
			'type' => 'text',

			'validation' =>  array
			(
				'required' => true
			),

			'value' =>
			(
				!is_numeric($this->rate)
				||
				$this->rate <= 0
			)
			? ''
			: InvoiceEM_Utilities::format_currency($this->rate)
		));

		$date_format = 'F j';

		$billing_settings_box->add_field(array
		(
			'description' => __('Date that your company fiscal year starts.', 'invoiceem'),
			'input_classes' => array('iem-datepicker'),
			'label' => __('Fiscal Year Starts', 'invoiceem'),
			'name' => 'year_starts',
			'type' => 'text',

			'attributes' => array
			(
				'autocomplete' => 'off',
				'data-iem-format' => 'MM d',
				'placeholder' => date_i18n($date_format, strtotime('January 1'))
			),

			'value' => (empty($this->year_starts))
			? ''
			: date_i18n($date_format, strtotime($this->year_starts))
		));

		$billing_settings_box->add_field($save_all_field);

		$contact_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'contact_information',
			'option_name' => $this->_option_name,
			'title' => __('Contact Information', 'invoiceem')
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Primary company email address.', 'invoiceem'),
			'label' => __('Email Address', 'invoiceem'),
			'name' => 'email',
			'type' => 'email',
			'value' => $this->email,

			'attributes' => array
			(
				'maxlength' => 255,
				'placeholder' => get_option('admin_email')
			),

			'validation' => array
			(
				'email' => true
			)
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Primary company address and/or any other details you\'d like to display.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Address', 'invoiceem'),
			'name' => 'address',
			'type' => 'textarea',
			'value' => $this->address,

			'attributes' => array
			(
				'rows' => 4
			)
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Primary company phone number.', 'invoiceem'),
			'label' => __('Phone Number', 'invoiceem'),
			'name' => 'phone',
			'type' => 'text',
			'value' => $this->phone,

			'attributes' => array
			(
				'maxlength' => 64
			)
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Primary company fax number.', 'invoiceem'),
			'label' => __('Fax Number', 'invoiceem'),
			'name' => 'fax',
			'type' => 'text',
			'value' => $this->fax,

			'attributes' => array
			(
				'maxlength' => 64
			)
		));

		$contact_information_box->add_field($save_all_field);

		InvoiceEM_Meta_Box::side_meta_boxes();
	}
}
