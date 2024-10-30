<?php
/*!
 * Client object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Client
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the client object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Client extends InvoiceEM_Object
{
	/**
	 * Column name for the client ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'client_id';

	/**
	 * Column name for the client name.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'client_name';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Clients::SELECT_COLUMNS;
	
	/**
	 * Users for this client.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    IEM_Clients_Plus_Client_Users
	 */
	private $_users;

	/**
	 * Raw clients table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_CLIENTS;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $client_id Optional ID of the client to load.
	 * @param  boolean $is_view   True if the client is being viewed.
	 * @return void
	 */
	public function __construct($client_id = 0, $is_view = false)
	{
		if
		(
			!$is_view
			&&
			!current_user_can(InvoiceEM_Constants::CAP_EDIT_CLIENTS)
		)
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct();
		
		$this->_finalize($client_id);
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
			 * ID for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;

			/**
			 * Name for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case self::TITLE_COLUMN:
			
				return '';

			/**
			 * Invoice prefix for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_prefix':
			
			/**
			 * Client's website URL.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'website':
			
			/**
			 * Standard rate for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'rate':
			
			/**
			 * Date that work started for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'since':
			
				return null;

			/**
			 * Client's main email address.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'email':
			
				return '';

			/**
			 * Client's main phone number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'phone':
			
			/**
			 * Client's main fax number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'fax':
			
				return null;

			/**
			 * ID of the country record for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Country::ID_COLUMN:
			
			/**
			 * ID of the currency record for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Currency::ID_COLUMN:
			
				return 0;

			/**
			 * Active state of the client.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the client.
			 *
			 * @since 1.0.0
			 *string
			 * @var 
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * Client's main address.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'address':
			
			/**
			 * Client's tax settings.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'taxes':
			
			/**
			 * History of events for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_HISTORY:
			
				return null;
		}

		return parent::_default($name);
	}
	
	/**
	 * Finalize the client.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $client_id ID of the client to load.
	 * @return void
	 */
	private function _finalize($client_id)
	{
		if
		(
			is_numeric($client_id)
			&&
			$client_id > 0
		)
		{
			$this->_load($client_id);
		}
		else if
		(
			isset($_POST['action'])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
		)
		{
			if
			(
				isset($_POST[self::ID_COLUMN])
				&&
				is_numeric($_POST[self::ID_COLUMN])
				&&
				$_POST[self::ID_COLUMN] > 0
			)
			{
				$this->_load(esc_attr($_POST[self::ID_COLUMN]));
			}
			
			$new_rate = (isset($_POST['rate']))
			? InvoiceEM_Utilities::unformat_currency($_POST['rate'])
			: '';

			$row = array
			(
				self::TITLE_COLUMN =>
				(
					!isset($_POST[self::TITLE_COLUMN])
					||
					empty($_POST[self::TITLE_COLUMN])
				)
				? $this->{self::TITLE_COLUMN}
				: substr(sanitize_text_field($_POST[self::TITLE_COLUMN]), 0, 255),

				'invoice_prefix' =>
				(
					!isset($_POST['invoice_prefix'])
					||
					empty($_POST['invoice_prefix'])
				)
				? $this->invoice_prefix
				: substr(sanitize_text_field($_POST['invoice_prefix']), 0, 16),

				'website' =>
				(
					!isset($_POST['website'])
					||
					empty($_POST['website'])
				)
				? $this->website
				: substr(esc_url($_POST['website']), 0, 255),

				'rate' => (empty($new_rate))
				? $this->rate
				: $new_rate,

				'since' =>
				(
					!isset($_POST['since'])
					||
					empty($_POST['since'])
				)
				? $this->since
				: date(InvoiceEM_Constants::MYSQL_DATE, strtotime(esc_attr($_POST['since']))),

				'email' => substr(sanitize_email($_POST['email']), 0, 255),

				'phone' =>
				(
					!isset($_POST['phone'])
					||
					empty($_POST['phone'])
				)
				? $this->phone
				: substr(sanitize_text_field($_POST['phone']), 0, 64),

				'fax' =>
				(
					!isset($_POST['fax'])
					||
					empty($_POST['fax'])
				)
				? $this->fax
				: substr(sanitize_text_field($_POST['fax']), 0, 64),

				InvoiceEM_Country::ID_COLUMN =>
				(
					!isset($_POST[InvoiceEM_Country::ID_COLUMN])
					||
					!is_numeric($_POST[InvoiceEM_Country::ID_COLUMN])
				)
				? $this->base->settings->company->{InvoiceEM_Country::ID_COLUMN}
				: esc_attr($_POST[InvoiceEM_Country::ID_COLUMN]),

				InvoiceEM_Currency::ID_COLUMN =>
				(
					!isset($_POST[InvoiceEM_Currency::ID_COLUMN])
					||
					!is_numeric($_POST[InvoiceEM_Currency::ID_COLUMN])
				)
				? $this->base->settings->company->{InvoiceEM_Currency::ID_COLUMN}
				: esc_attr($_POST[InvoiceEM_Currency::ID_COLUMN]),

				'address' =>
				(
					!isset($_POST['address'])
					||
					empty($_POST['address'])
				)
				? $this->address
				: sanitize_textarea_field($_POST['address']),
				
				'taxes' =>
				(
					!isset($_POST['override_taxes'])
					||
					empty($_POST['override_taxes'])
				)
				? $this->taxes
				: maybe_serialize($this->base->settings->invoicing->sanitize_taxes($_POST['taxes']))
			);

			$formats = array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s');
			$is_valid = true;

			if (empty($row[self::TITLE_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a client name.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row['email']))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter an email address.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if ($is_valid)
			{
				$this->_load_post($this->base->cache->action, $row, $formats);
				
				if
				(
					$this->base->cache->has_clients_plus
					&&
					!$this->_users->update($this->{self::ID_COLUMN})
				)
				{
					InvoiceEM_Output::add_admin_notice(__('The client was updated successfully, but there was a problem updating users for the client.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
				}
			}
			else
			{
				$row[self::ID_COLUMN] =
				(
					!isset($_POST[self::ID_COLUMN])
					||
					!is_numeric($_POST[self::ID_COLUMN])
				)
				? $this->{self::ID_COLUMN}
				: esc_attr($_POST[self::ID_COLUMN]);

				$this->_set_properties($row);
			}

			$this->base->cache->accounting = InvoiceEM_Currency::accounting_settings($this->{InvoiceEM_Currency::ID_COLUMN});
		}
		else if
		(
			isset($_GET['action'])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
		)
		{
			$has_clients_plus = $this->base->cache->has_clients_plus;
			$current_action = $this->base->cache->action;
			$is_add = ($current_action == InvoiceEM_Constants::ACTION_ADD);
			$is_delete = ($current_action == InvoiceEM_Constants::ACTION_DELETE);
			$processed_id = $this->_load_get($current_action);

			if
			(
				$has_clients_plus
				&&
				$is_delete
				&&
				$processed_id !== false
			)
			{
				IEM_Clients_Plus_Client_Users::delete_client_records($processed_id);
			}
			else if
			(
				$is_add
				||
				$current_action == InvoiceEM_Constants::ACTION_EDIT
			)
			{
				$this->base->cache->accounting = InvoiceEM_Currency::accounting_settings($this->{InvoiceEM_Currency::ID_COLUMN});
				
				if ($has_clients_plus)
				{
					if ($is_add)
					{
						$this->_users = new IEM_Clients_Plus_Client_Users();
					}
					else if (empty($this->_users))
					{
						$this->_users = new IEM_Clients_Plus_Client_Users($processed_id);
					}
				}
			}
		}
	}

	/**
	 * Setup the client to be added.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  Current action being taken.
	 * @return boolean True if the user can add a client.
	 */
	protected function _get_add($action)
	{
		if ($action == InvoiceEM_Constants::ACTION_COPY)
		{
			$this->{self::ID_COLUMN} = 0;
			$this->{InvoiceEM_Constants::COLUMN_LOCKED} = null;
			$this->{InvoiceEM_Constants::COLUMN_HISTORY} = null;
			$this->_history = null;
		}
		
		return parent::_get_add($action);
	}

	/**
	 * Load the client from the database.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $client_id ID value for the client being loaded.
	 * @return void
	 */
	protected function _load($client_id)
	{
		parent::_load($client_id);
		
		if
		(
			$this->base->cache->has_clients_plus
			&&
			!$this->base->cache->is_client
		)
		{
			$this->_users = new IEM_Clients_Plus_Client_Users($client_id);
		}
	}
	
	/**
	 * Get the client user email addresses.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Email addresses associated with this client.
	 */
	public function get_user_emails($email_type)
	{
		return ($this->base->cache->has_clients_plus)
		? $this->_users->get_emails($email_type)
		: array();
	}

	/**
	 * Prepare the client output.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare()
	{
		parent::prepare();
		
		if ($this->base->cache->has_clients_plus)
		{
			do_action(InvoiceEM_Constants::HOOK_CLIENT_META_BOXES, $this, $this->_users->get_value());
		}
		
		$general_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'general_information',
			'title' => __('General Information', 'invoiceem')
		));

		$general_information_box->add_field(array
		(
			'description' => __('Name used to identify this client.', 'invoiceem'),
			'label' => __('Client Name', 'invoiceem'),
			'name' => self::TITLE_COLUMN,
			'type' => 'text',
			'value' => $this->{self::TITLE_COLUMN},

			'attributes' => array
			(
				'maxlength' => 255
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Abbreviation or identifier used in invoice numbers.', 'invoiceem'),
			'label' => __('Invoice Prefix', 'invoiceem'),
			'name' => 'invoice_prefix',
			'type' => 'text',
			'value' => $this->invoice_prefix,

			'attributes' => array
			(
				'maxlength' => 16,
				'placeholder' => $this->base->settings->invoicing->prefix
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Primary website URL for this client.', 'invoiceem'),
			'input_classes' => array('url'),
			'label' => __('Website', 'invoiceem'),
			'name' => 'website',
			'type' => 'url',
			'value' => $this->website,

			'attributes' => array
			(
				'maxlength' => 255,
				'placeholder' => 'https://'
			),

			'validation' => array
			(
				'url' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Standard rate for this client.', 'invoiceem'),
			'input_classes' => array('iem-currency'),
			'label' => __('Client Rate', 'invoiceem'),
			'name' => 'rate',
			'type' => 'text',
			
			'attributes' => array
			(
				'data-iem-placeholder' => $this->base->settings->company->rate
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

		$date_format = get_option('date_format');

		$general_information_box->add_field(array
		(
			'description' => __('Date that work started for this client.', 'invoiceem'),
			'input_classes' => array('iem-datepicker'),
			'label' => __('Client Since', 'invoiceem'),
			'name' => 'since',
			'type' => 'text',

			'attributes' => array
			(
				'autocomplete' => 'off',
				'placeholder' => InvoiceEM_Utilities::format_date($date_format)
			),

			'value' => (empty($this->since))
			? ''
			: date_i18n($date_format, strtotime($this->since))
		));

		$contact_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'contact_information',
			'title' => __('Contact Information', 'invoiceem')
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Main email address for this client.', 'invoiceem'),
			'label' => __('Email Address', 'invoiceem'),
			'name' => 'email',
			'type' => 'email',
			'value' => $this->email,

			'attributes' => array
			(
				'maxlength' => 100
			),

			'validation' => array
			(
				'email' => true,
				'required' => true
			)
		));

		$contact_information_box->add_field(array
		(
			'description' => __('Main address for this client and/or any other details you\'d like to display.', 'invoiceem'),
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
			'description' => __('Main phone number for this client.', 'invoiceem'),
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
			'description' => __('Main fax number for this client.', 'invoiceem'),
			'label' => __('Fax Number', 'invoiceem'),
			'name' => 'fax',
			'type' => 'text',
			'value' => $this->fax,

			'attributes' => array
			(
				'maxlength' => 64
			)
		));
		
		$this->_history_box();
		$this->_publish_box(__('Client is currently active.', 'invoiceem'));
		
		$tax_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'side',
			'id' => 'tax_settings',
			'title' => __('Tax Settings', 'invoiceem')
		));
		
		$taxes_value = maybe_unserialize($this->taxes);
		
		$tax_settings_box->add_field(array
		(
			'description' => __('If checked, taxes for this client will be separate from the company tax settings.', 'invoiceem'),
			'name' => 'override_taxes',
			'type' => 'checkbox',
			'value' => (!empty($taxes_value))
		));
		
		$tax_settings_box->add_field(array_merge
		(
			$this->base->settings->invoicing->taxes_field($taxes_value),
			
			array
			(
				'classes' => (empty($taxes_value))
				? array('iem-hidden')
				: array(),

				'conditional' => array
				(
					array
					(
						'field' => 'override_taxes',
						'value' => '1'
					)
				)
			)
		));

		$regional_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'side',
			'id' => 'regional_information',
			'title' => __('Regional Information', 'invoiceem')
		));

		$same_as_company_label = __('Same as Company', 'invoiceem');

		$regional_information_box->add_field(array
		(
			'description' => __('Country that this client is in.', 'invoiceem'),
			'label' => __('Country', 'invoiceem'),
			'name' => InvoiceEM_Country::ID_COLUMN,
			'options' => InvoiceEM_Country::selected_item($this->{InvoiceEM_Country::ID_COLUMN}),
			'table' => InvoiceEM_Constants::TABLE_COUNTRIES,
			'type' => 'select',
			'value' => $this->{InvoiceEM_Country::ID_COLUMN},

			'attributes' => array
			(
				'placeholder' => $same_as_company_label
			)
		));

		$regional_information_box->add_field(array
		(
			'description' => __('Currency that this client uses.', 'invoiceem'),
			'input_classes' => array('iem-accounting'),
			'label' => __('Currency', 'invoiceem'),
			'name' => InvoiceEM_Currency::ID_COLUMN,
			'options' => InvoiceEM_Currency::selected_item($this->{InvoiceEM_Currency::ID_COLUMN}),
			'table' => InvoiceEM_Constants::TABLE_CURRENCIES,
			'type' => 'select',
			'value' => $this->{InvoiceEM_Currency::ID_COLUMN},

			'attributes' => array
			(
				'placeholder' => $same_as_company_label
			)
		));

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}

	/**
	 * Get accounting settings based on a provided client ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $client_id ID of the client to load accounting settings for.
	 * @return array              Formatted accounting settings.
	 */
	public static function accounting_settings($client_id)
	{
		$output = '';
		$results = self::_get_item($client_id);
		
		if (!empty($results))
		{
			$row = $results[0];
			$output = InvoiceEM_Currency::accounting_settings($row[InvoiceEM_Currency::ID_COLUMN]);
			
			if (!empty($row['invoice_prefix']))
			{
				$output['invoice_prefix'] = $row['invoice_prefix'];
			}
			
			if
			(
				isset($row['rate'])
				&&
				is_numeric($row['rate'])
			)
			{
				$output['rate'] = $row['rate'];
			}
			
			$output['taxes'] = (empty($row['taxes']))
			? InvoiceEM()->settings->invoicing->taxes
			: maybe_unserialize($row['taxes']);
		}
		
		return (empty($output))
		? InvoiceEM_Currency::accounting_settings()
		: $output;
	}

	/**
	 * Get the WHERE query for the current search.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  string $search Term used in the search query.
	 * @return string         Generated search WHERE query.
	 */
	protected static function _where_search($search)
	{
		return InvoiceEM_Clients::where_search($search);
	}
}
