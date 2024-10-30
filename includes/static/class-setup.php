<?php
/*!
 * Plugin setup functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Setup
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the setup functionality.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Setup
{
	/**
	 * Default country ID for plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    integer
	 */
	private static $_default_country_id = 0;
	
	/**
	 * Default currency ID for plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    integer
	 */
	private static $_default_currency_id = 0;
	
	/**
	 * Plugin activation hook.
	 *
	 * @since 1.0.5 Function name changes.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function activate()
	{
		self::_authentication();
		self::_database();
		self::_event();
		self::_settings();
	}

	/**
	 * Setup the user roles and capabilities.
	 *
	 * @since 1.0.5
	 *
	 * @access private static
	 * @return void
	 */
	private static function _authentication()
	{
		$account_manager = get_role(InvoiceEM_Constants::ROLE_ACCOUNT_MANAGER);
		$administrator = get_role('administrator');

		if (empty($account_manager))
		{
			$account_manager_capabilities = array
			(
				InvoiceEM_Constants::CAP_ADD_CLIENTS => true,
				InvoiceEM_Constants::CAP_ADD_INVOICES => true,
				InvoiceEM_Constants::CAP_ADD_PAYMENTS => true,
				InvoiceEM_Constants::CAP_ADD_PROJECTS => true,
				InvoiceEM_Constants::CAP_DELETE_CLIENTS => true,
				InvoiceEM_Constants::CAP_DELETE_INVOICES => true,
				InvoiceEM_Constants::CAP_DELETE_PAYMENTS => true,
				InvoiceEM_Constants::CAP_DELETE_PROJECTS => true,
				InvoiceEM_Constants::CAP_EDIT_CLIENTS => true,
				InvoiceEM_Constants::CAP_EDIT_INVOICES => true,
				InvoiceEM_Constants::CAP_EDIT_PAYMENTS => true,
				InvoiceEM_Constants::CAP_EDIT_PROJECTS => true,
				InvoiceEM_Constants::CAP_VIEW_REPORTS => true
			);

			$account_manager = add_role(InvoiceEM_Constants::ROLE_ACCOUNT_MANAGER, __('InvoiceEM Account Manager', 'invoiceem'), $account_manager_capabilities);
			$account_manager->add_cap('read');

			$administrator_capabilities = array_merge
			(
				$account_manager_capabilities,

				array
				(
					InvoiceEM_Constants::CAP_EDIT_COUNTRIES => true,
					InvoiceEM_Constants::CAP_EDIT_CURRENCIES => true
				)
			);

			foreach ($administrator_capabilities as $capability => $grant)
			{
				$administrator->add_cap($capability, $grant);
			}
		}
	}
	
	/**
	 * Create/update database tables.
	 *
	 * @since 1.0.5
	 *
	 * @access private static
	 * @return void
	 */
	public static function _database()
	{
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$countries_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_COUNTRIES);
		$currencies_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CURRENCIES);
		$charset_collate = $wpdb->get_charset_collate();
		
		dbDelta("CREATE TABLE " . $countries_table . " (\n"
		. InvoiceEM_Country::ID_COLUMN . " int(10) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Country::TITLE_COLUMN . " varchar(64) NOT NULL,\n"
		. "official_name varchar(128),\n"
		. "three_digit_code char(3) NOT NULL,\n"
		. "two_digit_code char(2) NOT NULL,\n"
		. "flag varchar(16),\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Country::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Country::TITLE_COLUMN . " (" . InvoiceEM_Country::TITLE_COLUMN . "(" . min(64, InvoiceEM_Database::MAX_INDEX_LENGTH) . "))\n"
		. ") " . $charset_collate . ";\n");

		dbDelta("CREATE TABLE " . $currencies_table . " (\n"
		. InvoiceEM_Currency::ID_COLUMN . " int(10) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Currency::TITLE_COLUMN . " char(3) NOT NULL,\n"
		. "currency_name varchar(64) NOT NULL,\n"
		. "symbol varchar(16) NOT NULL,\n"
		. "thousand_separator varchar(4),\n"
		. "number_grouping varchar(16),\n"
		. "decimal_separator varchar(4) NOT NULL,\n"
		. "decimal_digits tinyint(1) unsigned NOT NULL,\n"
		. "positive_format varchar(16) NOT NULL,\n"
		. "negative_format varchar(16) NOT NULL,\n"
		. "zero_format varchar(16),\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Currency::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Currency::TITLE_COLUMN . " (" . InvoiceEM_Currency::TITLE_COLUMN . "(" . min(3, InvoiceEM_Database::MAX_INDEX_LENGTH) . "))\n"
		. ") " . $charset_collate . ";\n");
		
		dbDelta("CREATE TABLE " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS) . " (\n"
		. InvoiceEM_Client::ID_COLUMN . " bigint(20) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Constants::COLUMN_PREVIOUS_ID . " varchar(36),\n"
		. InvoiceEM_Client::TITLE_COLUMN . " varchar(255) NOT NULL,\n"
		. "invoice_prefix varchar(16),\n"
		. "website varchar(255),\n"
		. "rate decimal(30, 8),\n"
		. "since date,\n"
		. "email varchar(100),\n"
		. "phone varchar(64),\n"
		. "fax varchar(64),\n"
		. InvoiceEM_Country::ID_COLUMN . " int(10) unsigned NOT NULL,\n"
		. InvoiceEM_Currency::ID_COLUMN . " int(10) unsigned NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. "address text,\n"
		. "taxes text,\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Client::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Client::TITLE_COLUMN . " (" . InvoiceEM_Client::TITLE_COLUMN . "(" . min(255, InvoiceEM_Database::MAX_INDEX_LENGTH) . ")),\n"
		. "KEY " . InvoiceEM_Country::ID_COLUMN . " (" . InvoiceEM_Country::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Currency::ID_COLUMN . " (" . InvoiceEM_Currency::ID_COLUMN . ")\n"
		. ") " . $charset_collate . ";\n");

		dbDelta("CREATE TABLE " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS) . " (\n"
		. InvoiceEM_Project::ID_COLUMN . " bigint(20) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Constants::COLUMN_PREVIOUS_ID . " varchar(36),\n"
		. InvoiceEM_Project::TITLE_COLUMN . " varchar(255) NOT NULL,\n"
		. InvoiceEM_Client::ID_COLUMN . " bigint(20) unsigned NOT NULL,\n"
		. "website varchar(255),\n"
		. "rate decimal(30, 8),\n"
		. "start_date date,\n"
		. "end_date date,\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Project::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Project::TITLE_COLUMN . " (" . InvoiceEM_Project::TITLE_COLUMN . "(" . min(255, InvoiceEM_Database::MAX_INDEX_LENGTH) . ")),\n"
		. "KEY " . InvoiceEM_Client::ID_COLUMN . " (" . InvoiceEM_Client::ID_COLUMN . ")\n"
		. ") " . $charset_collate . ";\n");

		dbDelta("CREATE TABLE " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES) . " (\n"
		. InvoiceEM_Invoice::ID_COLUMN . " bigint(20) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Constants::COLUMN_PREVIOUS_ID . " varchar(36),\n"
		. "invoice_type char(1) NOT NULL,\n"
		. InvoiceEM_Invoice::TITLE_COLUMN . " varchar(255) NOT NULL,\n"
		. InvoiceEM_Client::ID_COLUMN . " bigint(20) unsigned NOT NULL,\n"
		. InvoiceEM_Project::ID_COLUMN . " bigint(20) unsigned,\n"
		. "po_number varchar(32),\n"
		. "deposit varchar(32),\n"
		. "deposit_due int(10) unsigned,\n"
		. "pre_tax_discount varchar(32),\n"
		. "discount varchar(32),\n"
		. "invoice_title varchar(255),\n"
		. "invoice_number varchar(255),\n"
		. "send_date int(11),\n"
		. "recurrence varchar(3),\n"
		. "total decimal(30, 8) NOT NULL,\n"
		. "payment_due int(11),\n"
		. "paid decimal(30, 8),\n"
		. "last_viewed int(10) unsigned,\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. "footer_note text,\n"
		. "footer_thank_you text,\n"
		. "taxes text,\n"
		. "line_items mediumtext,\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Invoice::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Client::ID_COLUMN . " (" . InvoiceEM_Client::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Project::ID_COLUMN . " (" . InvoiceEM_Project::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Invoice::TITLE_COLUMN . " (" . InvoiceEM_Invoice::TITLE_COLUMN . "(" . min(255, InvoiceEM_Database::MAX_INDEX_LENGTH) . "))\n"
		. ") " . $charset_collate . ";\n");

		dbDelta("CREATE TABLE " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PAYMENTS) . " (\n"
		. InvoiceEM_Payment::ID_COLUMN . " bigint(20) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Constants::COLUMN_PREVIOUS_ID . " varchar(36),\n"
		. "method char(1) NOT NULL,\n"
		. InvoiceEM_Payment::TITLE_COLUMN . " varchar(255) NOT NULL,\n"
		. InvoiceEM_Client::ID_COLUMN . " bigint(20) unsigned NOT NULL,\n"
		. "payment_date int(10) unsigned NOT NULL,\n"
		. "bonus decimal(30, 8) NULL,\n"
		. "fee decimal(30, 8) NULL,\n"
		. "amount decimal(30, 8) NOT NULL,\n"
		. "is_failed bit(1) NOT NULL,\n"
		. "is_completed bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_IS_ACTIVE . " bit(1) NOT NULL,\n"
		. InvoiceEM_Constants::COLUMN_LOCKED . " varchar(32),\n"
		. "charge text,\n"
		. InvoiceEM_Constants::COLUMN_HISTORY . " longtext,\n"
		. "PRIMARY KEY  (" . InvoiceEM_Payment::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Client::ID_COLUMN . " (" . InvoiceEM_Client::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Payment::TITLE_COLUMN . " (" . InvoiceEM_Payment::TITLE_COLUMN . "(" . min(255, InvoiceEM_Database::MAX_INDEX_LENGTH) . "))\n"
		. ") " . $charset_collate . ";\n");

		dbDelta("CREATE TABLE " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PAYMENT_INVOICES) . " (\n"
		. InvoiceEM_Payment_Invoices::ID_COLUMN . " bigint(20) unsigned NOT NULL auto_increment,\n"
		. InvoiceEM_Payment::ID_COLUMN . " bigint(20) unsigned NOT NULL,\n"
		. InvoiceEM_Invoice::ID_COLUMN . " bigint(20) unsigned NOT NULL,\n"
		. "amount decimal(30, 8),\n"
		. "PRIMARY KEY  (" . InvoiceEM_Payment_Invoices::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Payment::ID_COLUMN . " (" . InvoiceEM_Payment::ID_COLUMN . "),\n"
		. "KEY " . InvoiceEM_Invoice::ID_COLUMN . " (" . InvoiceEM_Invoice::ID_COLUMN . ")\n"
		. ") " . $charset_collate . ";\n");
		
		if (InvoiceEM_Utilities::is_plugin_active('iem-clients-plus/iem-clients-plus.php'))
		{
			IEM_Clients_Plus_Setup::activate();
		}
		
		if (InvoiceEM_Utilities::is_plugin_active('iem-invoices-plus/iem-invoices-plus.php'))
		{
			IEM_Invoices_Plus_Setup::activate();
		}
		
		self::_populate_database($countries_table, $currencies_table);
	}

	/**
	 * Populate database with default data.
	 *
	 * @since 1.0.5
	 *
	 * @access private static
	 * @param  string $countries_table  Name for the countries table.
	 * @param  string $currencies_table Name for the currencies table.
	 * @return void
	 */
	private static function _populate_database($countries_table, $currencies_table)
	{
		global $wpdb;
		
		$country_id = $wpdb->get_row("SELECT " . InvoiceEM_Country::ID_COLUMN . " FROM " . $countries_table . " LIMIT 1");
		$populate_countries = empty($country_id);
		$currency_id = $wpdb->get_row("SELECT " . InvoiceEM_Currency::ID_COLUMN . " FROM " . $currencies_table . " LIMIT 1");
		$populate_currencies = empty($country_id);
		
		if
		(
			$populate_countries
			||
			$populate_currencies
		)
		{
			$countries_raw = InvoiceEM_Utilities::load_json('includes/vendor/mledoze/countries/countries.json');
			$currencies = array();

			foreach ($countries_raw as $country)
			{
				if (!empty($country['currencies']))
				{
					foreach ($country['currencies'] as $currency_code => $currency_details)
					{
						$currency_code = strtoupper($currency_code);

						$currencies[$currency_code] = array
						(
							InvoiceEM_Currency::TITLE_COLUMN => $currency_code,
							'currency_name' => $currency_details['name'],
							'symbol' => $currency_details['symbol'],
							'thousand_separator' => ',',
							'number_grouping' => null,
							'decimal_separator' => '.',
							'decimal_digits' => 2,
							'positive_format' => '%s%v',
							'negative_format' => '(%s%v)',
							InvoiceEM_Constants::COLUMN_IS_ACTIVE => 1
						);
					}
				}
			}

			if ($populate_countries)
			{
				$currency_count = 0;
				$country_formats = array('%s', '%s', '%s', '%s', '%s', '%d', '%s');

				foreach ($countries_raw as $country)
				{
					$three_digit_code = strtoupper($country['cca3']);
					$history = new InvoiceEM_History();
					$history->add_event(InvoiceEM_Constants::ACTION_ADD, false);

					$inserted = $wpdb->insert
					(
						$countries_table,

						array
						(
							InvoiceEM_Country::TITLE_COLUMN => $country['name']['common'],
							'official_name' => $country['name']['official'],
							'three_digit_code' => $three_digit_code,
							'two_digit_code' => strtoupper($country['cca2']),
							'flag' => $country['flag'],
							InvoiceEM_Constants::COLUMN_IS_ACTIVE => 1,
							InvoiceEM_Constants::COLUMN_HISTORY => $history->get_serialized()
						),

						$country_formats
					);

					if
					(
						$three_digit_code == 'USA'
						&&
						$inserted !== false
					)
					{
						self::$_default_country_id = $wpdb->insert_id;
					}
				}
			}

			if ($populate_currencies)
			{
				$currencies_raw = InvoiceEM_Utilities::load_json('includes/vendor/smirzaei/currency-formatter/currencies.json');
				$currency_formats = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s');

				foreach ($currencies as $currency_code => $currency_columns)
				{
					if (isset($currencies_raw[$currency_code]))
					{
						$currency_columns['thousand_separator'] = $currencies_raw[$currency_code]['thousandsSeparator'];

						if ($currency_code == 'INR')
						{
							$currency_columns['number_grouping'] = '2,2,3';
						}

						$currency_columns['decimal_separator'] = $currencies_raw[$currency_code]['decimalSeparator'];
						$currency_columns['decimal_digits'] = $currencies_raw[$currency_code]['decimalDigits'];

						if ($currency_columns['thousand_separator'] == $currency_columns['decimal_separator'])
						{
							if (empty($currency_columns['decimal_digits']))
							{
								$currency_columns['decimal_separator'] = '-';
							}
							else
							{
								$currency_columns['thousand_separator'] = (empty($currency_columns['decimal_separator']))
								? ','
								: '';
							}
						}

						$currency_columns['symbol'] = (empty($currency_columns['symbol']))
						? $currencies_raw[$currency_code]['symbol']
						: $currency_columns['symbol'];

						if
						(
							$currencies_raw[$currency_code]['symbolOnLeft']
							&&
							$currencies_raw[$currency_code]['spaceBetweenAmountAndSymbol']
						)
						{
							$currency_columns['positive_format'] = '%s %v';
							$currency_columns['negative_format'] = '(%s %v)';
						}
						else if
						(
							!$currencies_raw[$currency_code]['symbolOnLeft']
							&&
							$currencies_raw[$currency_code]['spaceBetweenAmountAndSymbol']
						)
						{
							$currency_columns['positive_format'] = '%v %s';
							$currency_columns['negative_format'] = '(%v %s)';
						}
						else if
						(
							!$currencies_raw[$currency_code]['symbolOnLeft']
							&&
							!$currencies_raw[$currency_code]['spaceBetweenAmountAndSymbol']
						)
						{
							$currency_columns['positive_format'] = '%v%s';
							$currency_columns['negative_format'] = '(%v%s)';
						}
					}

					$history = new InvoiceEM_History();
					$history->add_event(InvoiceEM_Constants::ACTION_ADD, false);

					$currency_columns[InvoiceEM_Constants::COLUMN_HISTORY] = $history->get_serialized();

					$inserted = $wpdb->insert($currencies_table, $currency_columns, $currency_formats);

					if
					(
						$currency_code == 'USD'
						&&
						$inserted !== false
					)
					{
						self::$_default_currency_id = $wpdb->insert_id;
					}
				}
			}
		}
	}

	/**
	 * Setup the daily event.
	 *
	 * @since 1.0.5
	 *
	 * @access private static
	 * @return void
	 */
	private static function _event()
	{
		if (wp_get_schedule(InvoiceEM_Constants::HOOK_DAILY) === false)
		{
			wp_schedule_event(time(), 'daily', InvoiceEM_Constants::HOOK_DAILY);
		}
	}
	
	/**
	 * Add the default plugin settings.
	 *
	 * @since 1.0.5
	 *
	 * @access private static
	 * @return void
	 */
	private static function _settings()
	{
		$company_settings = get_option(InvoiceEM_Constants::OPTION_SETTINGS_COMPANY);
		
		if
		(
			empty($company_settings)
			&&
			!empty(self::$_default_country_id)
			&&
			!empty(self::$_default_currency_id)
		)
		{
			add_option
			(
				InvoiceEM_Constants::OPTION_SETTINGS_COMPANY,
				
				array
				(
					InvoiceEM_Country::ID_COLUMN => self::$_default_country_id,
					InvoiceEM_Currency::ID_COLUMN => self::$_default_currency_id
				)
			);
		}
	}
	
	/**
	 * Plugin deactivation hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function deactivate()
	{
		flush_rewrite_rules(false);
		
		wp_clear_scheduled_hook(InvoiceEM_Constants::HOOK_DAILY);
	}

	/**
	 * Check and update the plugin version.
	 *
	 * @since 1.0.5 Added database check.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function check_version()
	{
		$current_version = get_option(InvoiceEM_Constants::OPTION_VERSION);

		if (empty($current_version))
		{
			add_option(InvoiceEM_Constants::OPTION_VERSION, InvoiceEM_Constants::VERSION);
		}
		else if ($current_version != InvoiceEM_Constants::VERSION)
		{
			update_option(InvoiceEM_Constants::OPTION_VERSION, InvoiceEM_Constants::VERSION);
			
			self::_database();
		}
	}
}
