<?php
/*!
 * Currency object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Currency
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the currency object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Currency extends InvoiceEM_Object
{
	/**
	 * Column name for the currency ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'currency_id';

	/**
	 * Column name for the currency code.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'currency_code';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Currencies::SELECT_COLUMNS;

	/**
	 * Loaded currency settings.
	 *
	 * @since 1.0.5 Made the variable public.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @var    array
	 */
	public static $loaded = array();

	/**
	 * Raw currencies table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_CURRENCIES;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $currency_id Optional ID of the currency to load.
	 * @return void
	 */
	public function __construct($currency_id = 0)
	{
		if (!current_user_can(InvoiceEM_Constants::CAP_EDIT_CURRENCIES))
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct();
		
		$this->_finalize($currency_id);
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
			 * ID for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;

			/**
			 * Code for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case self::TITLE_COLUMN:
			
			/**
			 * Name for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'currency_name':
			
			/**
			 * Symbol for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'symbol':
			
				return '';

			/**
			 * Thousand separator for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'thousand_separator':
			
				return ',';
				
			/**
			 * Number grouping for larger numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'number_grouping':
			
				return null;

			/**
			 * Decimal separator for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'decimal_separator':
			
				return '.';

			/**
			 * Decimal digits for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'decimal_digits':
			
				return 0;

			/**
			 * Decimal separator for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'positive_format':
			
				return '%s%v';
				
			/**
			 * Decimal separator for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'negative_format':
			
				return '(%s%v)';

			/**
			 * Decimal separator for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'zero_format':
			
				return null;

			/**
			 * Active state of the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the currency.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * History of events for the currency.
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
	 * Finalize the currency.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $currency_id ID of the currency to load.
	 * @return void
	 */
	private function _finalize($currency_id)
	{
		if
		(
			is_numeric($currency_id)
			&&
			$currency_id > 0
		)
		{
			$this->_load($currency_id);
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
			
			$row = array
			(
				self::TITLE_COLUMN =>
				(
					!isset($_POST[self::TITLE_COLUMN])
					||
					empty($_POST[self::TITLE_COLUMN])
				)
				? $this->{self::TITLE_COLUMN}
				: substr(strtoupper(sanitize_key($_POST[self::TITLE_COLUMN])), 0, 3),

				'currency_name' =>
				(
					!isset($_POST['currency_name'])
					||
					empty($_POST['currency_name'])
				)
				? $this->currency_name
				: substr(sanitize_text_field($_POST['currency_name']), 0, 64),

				'symbol' =>
				(
					!isset($_POST['symbol'])
					||
					empty($_POST['symbol'])
				)
				? $this->symbol
				: substr(sanitize_text_field($_POST['symbol']), 0, 16),

				'thousand_separator' =>
				(
					!isset($_POST['thousand_separator'])
					||
					empty($_POST['thousand_separator'])
				)
				? $this->thousand_separator
				: substr(sanitize_text_field(preg_replace('/\d/u', '', $_POST['thousand_separator'])), 0, 4),

				'number_grouping' =>
				(
					!isset($_POST['number_grouping'])
					||
					empty($_POST['number_grouping'])
				)
				? $this->number_grouping
				: substr(sanitize_text_field($_POST['number_grouping']), 0, 16),

				'decimal_separator' =>
				(
					!isset($_POST['decimal_separator'])
					||
					empty($_POST['decimal_separator'])
				)
				? $this->decimal_separator
				: substr(sanitize_text_field(preg_replace('/\d/u', '', $_POST['decimal_separator'])), 0, 4),

				'decimal_digits' =>
				(
					!isset($_POST['decimal_digits'])
					||
					!is_numeric($_POST['decimal_digits'])
				)
				? $this->decimal_digits
				: esc_attr($_POST['decimal_digits']),

				'positive_format' =>
				(
					!isset($_POST['positive_format'])
					||
					empty($_POST['positive_format'])
				)
				? $this->positive_format
				: $this->_clean_format(esc_attr($_POST['positive_format'])),

				'negative_format' =>
				(
					!isset($_POST['negative_format'])
					||
					empty($_POST['negative_format'])
				)
				? $this->negative_format
				: $this->_clean_format(esc_attr($_POST['negative_format'])),

				'zero_format' =>
				(
					!isset($_POST['zero_format'])
					||
					empty($_POST['zero_format'])
				)
				? $this->zero_format
				: $this->_clean_format(esc_attr($_POST['zero_format']))
			);

			$formats = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s');
			
			$is_edit =
			(
				isset($_POST[self::ID_COLUMN])
				&&
				is_numeric($_POST[self::ID_COLUMN])
			);
			
			if
			(
				$is_edit
				&&
				$_POST[self::ID_COLUMN] == $this->base->settings->company->{self::ID_COLUMN}
			)
			{
				$row[InvoiceEM_Constants::COLUMN_IS_ACTIVE] = true;
				$formats[] = '%d';
			}
			
			$is_valid = true;

			if
			(
				empty($row[self::TITLE_COLUMN])
				||
				strlen($row[self::TITLE_COLUMN]) < 3
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a three-digit currency code.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row['currency_name']))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a currency name.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row['symbol']))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a currency symbol.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row['decimal_separator']))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a decimal separator.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}
			
			if
			(
				$row['symbol'] == $row['thousand_separator']
				||
				$row['symbol'] == $row['decimal_separator']
				||
				$row['thousand_separator'] == $row['decimal_separator']
			)
			{
				InvoiceEM_Output::add_admin_notice(__('The symbol and separators must be unique.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if ($row['decimal_digits'] < 0)
			{
				InvoiceEM_Output::add_admin_notice(__('Decimal digits cannot be less than zero.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}
			else if ($row['decimal_digits'] > 8)
			{
				InvoiceEM_Output::add_admin_notice(__('Decimal digits cannot be greater than eight.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if
			(
				strpos($row['positive_format'], '%s') === false
				||
				strpos($row['positive_format'], '%v') === false
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Positive format must contain both the \'%s\' and \'%v\' wildcards.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if
			(
				strpos($row['negative_format'], '%s') === false
				||
				strpos($row['negative_format'], '%v') === false
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Negative format must contain both the \'%s\' and \'%v\' wildcards.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if
			(
				!empty($row['zero_format'])
				&&
				strpos($row['zero_format'], '%s') === false
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Zero format must contain the \'%s\' wildcard.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if ($is_valid)
			{
				$this->_load_post($this->base->cache->action, $row, $formats);
			}
			else
			{
				$row[self::ID_COLUMN] = ($is_edit)
				? esc_attr($_POST[self::ID_COLUMN])
				: $this->{self::ID_COLUMN};

				$this->_set_properties($row);
			}
		}
		else if
		(
			isset($_GET['action'])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
		)
		{
			$this->_load_get($this->base->cache->action);
		}
	}

	/**
	 * Prepare the currency output.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare()
	{
		parent::prepare();
		
		$general_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'general_information',
			'title' => __('General Information', 'invoiceem')
		));

		$general_information_box->add_field(array
		(
			'description' => __('Three-digit code for this currency.', 'invoiceem'),
			'label' => __('Currency Code', 'invoiceem'),
			'name' => self::TITLE_COLUMN,
			'type' => 'text',
			'value' => $this->{self::TITLE_COLUMN},

			'attributes' => array
			(
				'maxlength' => 3
			),

			'validation' => array
			(
				'minlength' => 3,
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Name for this currency.', 'invoiceem'),
			'label' => __('Name', 'invoiceem'),
			'name' => 'currency_name',
			'type' => 'text',
			'value' => $this->currency_name,

			'attributes' => array
			(
				'maxlength' => 64
			),

			'validation' => array
			(
				'required' => true
			)
		));
		
		$unique_message =  __('The symbol and separators must be unique.', 'invoiceem');
		$currency_settings = $this->accounting_settings()['currency'];

		$general_information_box->add_field(array
		(
			'description' => __('Native symbol for this currency.', 'invoiceem'),
			'input_classes' => array('iem-unique'),
			'label' => __('Symbol', 'invoiceem'),
			'name' => 'symbol',
			'type' => 'text',
			'value' => $this->symbol,

			'attributes' => array
			(
				'data-msg-iem-unique' => $unique_message,
				'maxlength' => 16,
				'placeholder' => $currency_settings['symbol']
			),

			'validation' => array
			(
				'required' => true,
				'iem-unique' => '.iem-unique'
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Character(s) used to separate thousands.', 'invoiceem'),
			'input_classes' => array('iem-unique'),
			'label' => __('Thousand Separator', 'invoiceem'),
			'name' => 'thousand_separator',
			'type' => 'text',
			'value' => $this->thousand_separator,

			'attributes' => array
			(
				'data-msg-iem-unique' => $unique_message,
				'maxlength' => 4,
				'placeholder' => $currency_settings['thousand']
			),

			'validation' => array
			(
				'iem-unique' => '.iem-unique'
			)
		));
		
		$number_grouping_classes = (empty($this->thousand_separator))
		? array('iem-hidden')
		: array();

		$general_information_box->add_field(array
		(
			'classes' => $number_grouping_classes,
			'description' => __('Number grouping for the thousand separator.', 'invoiceem'),
			'label' => __('Number Grouping', 'invoiceem'),
			'name' => 'number_grouping',
			'type' => 'select',
			'value' => $this->number_grouping,
			
			'conditional' => array
			(
				array
				(
					'compare' => '!=',
					'field' => 'thousand_separator',
					'value' => ''
				)
			),
			
			'options' => array
			(
				'' => '###,###',
				'2,2,3' => '##,##,###'
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Character(s) used to separate decimals.', 'invoiceem'),
			'input_classes' => array('iem-unique'),
			'label' => __('Decimal Separator', 'invoiceem'),
			'name' => 'decimal_separator',
			'type' => 'text',
			'value' => $this->decimal_separator,

			'attributes' => array
			(
				'data-msg-iem-unique' => $unique_message,
				'maxlength' => 4,
				'placeholder' => $currency_settings['decimal']
			),

			'validation' => array
			(
				'required' => true,
				'iem-unique' => '.iem-unique'
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Number of digits displayed after the decimal.', 'invoiceem'),
			'input_classes' => array('iem-spinner'),
			'label' => __('Decimal Digits', 'invoiceem'),
			'name' => 'decimal_digits',
			'type' => 'text',
			'value' => $this->decimal_digits,

			'attributes' => array
			(
				'maxlength' => 1,
				'placeholder' => $currency_settings['precision']
			),

			'validation' => array
			(
				'max' => 8,
				'min' => 0,
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Format used for positive numbers.', 'invoiceem'),
			'input_classes' => array('iem-ltr'),
			'label' => __('Positive Format', 'invoiceem'),
			'name' => 'positive_format',
			'type' => 'text',
			'value' => $this->positive_format,

			'attributes' => array
			(
				'maxlength' => 16,
				'placeholder' => $currency_settings['format']['pos']
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Format used for negative numbers.', 'invoiceem'),
			'input_classes' => array('iem-ltr'),
			'label' => __('Negative Format', 'invoiceem'),
			'name' => 'negative_format',
			'type' => 'text',
			'value' => $this->negative_format,

			'attributes' => array
			(
				'maxlength' => 16,
				'placeholder' => $currency_settings['format']['neg']
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Format used for a zero value.', 'invoiceem'),
			'input_classes' => array('iem-ltr'),
			'label' => __('Zero Format', 'invoiceem'),
			'name' => 'zero_format',
			'type' => 'text',
			'value' => $this->zero_format,

			'attributes' => array
			(
				'maxlength' => 16,
				'placeholder' => $currency_settings['format']['zero']
			)
		));
		
		$sample_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'side',
			'id' => 'sample',
			'title' => __('Sample', 'invoiceem')
		));
		
		$sample_box->add_field(array
		(
			'type' => 'html',

			'content' => '<p>' . __('Below are samples of the currency output for positive, negative and zero values:', 'invoiceem') . '</p>'
			. '<p id="iem-currency-sample"></p>'
		));

		$this->_history_box();
		$this->_publish_box(__('Currency is currently active.', 'invoiceem'), ($this->{self::ID_COLUMN} == $this->base->settings->company->{self::ID_COLUMN}));

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}

	/**
	 * Clean up the provided currency format.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $format Raw currency format to clean up.
	 * @return string         Cleaned up currency format.
	 */
	private function _clean_format($format)
	{
		$symbol_count = substr_count($format, '%s');

		if ($symbol_count > 1)
		{
			$format = preg_replace('/%s/', '%S', $format, 1);
			$format = str_replace('%s', '', $format);
			$format = str_replace('%S', '%s', $format);
		}

		$value_count = substr_count($format, '%v');

		if ($value_count > 1)
		{
			$format = preg_replace('/%v/', '%V', $format, 1);
			$format = str_replace('%v', '', $format);
			$format = str_replace('%V', '%v', $format);
		}

		return substr(sanitize_text_field($format), 0, 16);
	}

	/**
	 * Get accounting settings based on a provided currency ID.
	 *
	 * @since 1.0.5 Added custom grouping verification and modified loaded variable name.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  array/integer $currency_id ID of the currency to load accounting settings for or false if the default settings should be used.
	 * @param  string        $page        Current page being processed.
	 * @return array                      Formatted accounting settings.
	 */
	public static function accounting_settings($currency_id = false, $page = '')
	{
		$iem = InvoiceEM();
		
		$company_currency_id = $iem->settings->company->{self::ID_COLUMN};
		
		$currency_id = ($currency_id === false)
		? $company_currency_id
		: $currency_id;
		
		if (isset(self::$loaded[$currency_id]))
		{
			return self::$loaded[$currency_id];
		}
		
		$results = self::_get_item($currency_id);
		
		if (empty($results))
		{
			if (!isset(self::$loaded[$company_currency_id]))
			{
				self::$loaded[$company_currency_id] = self::accounting_settings();
			}
			
			return self::$loaded[$company_currency_id];
		}
		
		$result = $results[0];
		$grouping = $result['number_grouping'];
		
		if (!empty($grouping))
		{
			$groups = explode(',', $grouping);
			
			foreach ($groups as $group)
			{
				if (!is_numeric($group))
				{
					$grouping = null;
					
					break;
				}
			}
		}
		
		return self::$loaded[$currency_id] = array
		(
			'currency' => array
			(
				'code' => strtolower($result[self::TITLE_COLUMN]),
				'decimal' => $result['decimal_separator'],
				'precision' => $result['decimal_digits'],
				'thousand' => $result['thousand_separator'],
				'grouping' => $grouping,

				'format' => array
				(
					'neg' => $result['negative_format'],
					'pos' => $result['positive_format'],

					'zero' => (empty($result['zero_format']))
					? $result['positive_format']
					: $result['zero_format']
				),

				'symbol' =>
				(
					$page == InvoiceEM_Constants::OPTION_SETTINGS_COMPANY
					||
					$result[self::ID_COLUMN] == $company_currency_id
					||
					self::accounting_settings()['currency']['symbol'] != $result['symbol']
				)
				? $result['symbol']
				: $result[self::TITLE_COLUMN]
			),

			'number' => array
			(
				'decimal' => $result['decimal_separator'],
				'precision' => $result['decimal_digits'],
				'thousand' => ''
			),

			'raw' => array
			(
				'decimal' => '.',
				'precision' => $result['decimal_digits'],
				'thousand' => ''
			),
			
			'invoice_prefix' => $iem->settings->invoicing->prefix,
			'rate' => $iem->settings->company->rate,
			'taxes' => $iem->settings->invoicing->taxes
		);
	}

	/**
	 * Generate a currency output label.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  array  $row Details for the currency.
	 * @return string      Generated currency label based on the provided row.
	 */
	public static function generate_label($row)
	{
		return self::_generate_label($row);
	}

	/**
	 * Generate a currency output label.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array  $row Details for the currency.
	 * @return string      Generated currency label based on the provided row.
	 */
	protected static function _generate_label($row)
	{
		return self::_generate_label_status($row, $row[self::TITLE_COLUMN] . ' (' . $row['symbol'] . ')');
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
		return InvoiceEM_Currencies::where_search($search);
	}
}
