<?php
/*!
 * Country object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Country
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the country object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Country extends InvoiceEM_Object
{
	/**
	 * Column name for the country ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'country_id';

	/**
	 * Column name for the country name.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'country_name';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Countries::SELECT_COLUMNS;

	/**
	 * Loaded countries.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    array
	 */
	private static $_loaded = array();

	/**
	 * Raw table name associated with this item.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_COUNTRIES;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $country_id Optional ID of the country to load.
	 * @return void
	 */
	public function __construct($country_id = 0)
	{
		if (!current_user_can(InvoiceEM_Constants::CAP_EDIT_COUNTRIES))
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct();
		
		$this->_finalize($country_id);
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
			 * ID for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;

			/**
			 * Common name for the country.
			 *
			 * @since 1.0.0
			 *string
			 * @var 
			 */
			case self::TITLE_COLUMN:
			
				return '';

			/**
			 * Official name for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'official_name':
			
				return null;

			/**
			 * Three-digit code for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'three_digit_code':
			
			/**
			 * Two-digit code for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'two_digit_code':
			
				return '';

			/**
			 * Emoji flag for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'flag':
			
				return null;

			/**
			 * Active state of the country.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the country.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * History of events for the country.
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
	 * Finalize the country.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $country_id ID of the country to load.
	 * @return void
	 */
	private function _finalize($country_id)
	{
		if
		(
			is_numeric($country_id)
			&&
			$country_id > 0
		)
		{
			$this->_load($country_id);
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
				: substr(sanitize_text_field($_POST[self::TITLE_COLUMN]), 0, 64),

				'official_name' =>
				(
					!isset($_POST['official_name'])
					||
					empty($_POST['official_name'])
				)
				? $this->official_name
				: substr(sanitize_text_field($_POST['official_name']), 0, 128),

				'three_digit_code' =>
				(
					!isset($_POST['three_digit_code'])
					||
					empty($_POST['three_digit_code'])
				)
				? $this->three_digit_code
				: substr(strtoupper(sanitize_key($_POST['three_digit_code'])), 0, 3),

				'two_digit_code' =>
				(
					!isset($_POST['two_digit_code'])
					||
					empty($_POST['two_digit_code'])
				)
				? $this->two_digit_code
				: substr(strtoupper(sanitize_key($_POST['two_digit_code'])), 0, 2),

				'flag' =>
				(
					!isset($_POST['flag'])
					||
					empty($_POST['flag'])
				)
				? $this->flag
				: substr(sanitize_text_field($_POST['flag']), 0, 16)
			);

			$formats = array('%s', '%s', '%s', '%s', '%s');
			
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

			if (empty($row[self::TITLE_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a common name for the country.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if
			(
				empty($row['three_digit_code'])
				||
				strlen($row['three_digit_code']) < 3
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a valid three-digit country code.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if
			(
				empty($row['two_digit_code'])
				||
				strlen($row['two_digit_code']) < 2
			)
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a valid two-digit country code.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

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
	 * Prepare the country output.
	 *
	 * @since 1.0.6 Fixed country name labels.
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
			'description' => __('Common name used to identify this country.', 'invoiceem'),
			'label' => __('Common Name', 'invoiceem'),
			'name' => self::TITLE_COLUMN,
			'type' => 'text',
			'value' => $this->{self::TITLE_COLUMN},

			'attributes' => array
			(
				'maxlength' => 64
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Official name for this country.', 'invoiceem'),
			'label' => __('Official Name', 'invoiceem'),
			'name' => 'official_name',
			'type' => 'text',
			'value' => $this->official_name,

			'attributes' => array
			(
				'maxlength' => 128
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Three-digit code for this country.', 'invoiceem'),
			'label' => __('Three-Digit Code', 'invoiceem'),
			'name' => 'three_digit_code',
			'type' => 'text',
			'value' => $this->three_digit_code,

			'attributes' => array
			(
				'maxlength' => 3
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Two-digit code for this country.', 'invoiceem'),
			'label' => __('Two-Digit Code', 'invoiceem'),
			'name' => 'two_digit_code',
			'type' => 'text',
			'value' => $this->two_digit_code,

			'attributes' => array
			(
				'maxlength' => 2
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Emoji flag for this country.', 'invoiceem'),
			'label' => __('Flag', 'invoiceem'),
			'name' => 'flag',
			'type' => 'text',
			'value' => $this->flag,

			'attributes' => array
			(
				'maxlength' => 16
			)
		));

		$this->_history_box();
		$this->_publish_box(__('Country is currently active.', 'invoiceem'), ($this->{self::ID_COLUMN} == $this->base->settings->company->{self::ID_COLUMN}));

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}

	/**
	 * Get a selected country based on the provided ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $country_id   ID of the selected country.
	 * @param  boolean $return_value True if just the value should be returned.
	 * @return array                 Formatted country based on the provided ID.
	 */
	public static function selected_item($country_id = '', $return_value = false)
	{
		if
		(
			!is_numeric($country_id)
			||
			$country_id <= 0
		)
		{
			return array();
		}
		
		if (!isset(self::$_loaded[$country_id]))
		{
			self::$_loaded += self::_list_format(self::_get_item($country_id));
		}
		
		return ($return_value)
		? self::$_loaded[$country_id]
		: array($country_id => self::$_loaded[$country_id]);
	}

	/**
	 * Generate a country output label.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array  $row Details for the country.
	 * @return string      Generated country label based on the provided row.
	 */
	protected static function _generate_label($row)
	{
		$flag = (empty($row['flag']))
		? ''
		: $row['flag'] . ' ';

		return self::_generate_label_status($row, $flag . $row[self::TITLE_COLUMN]);
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
		return InvoiceEM_Countries::where_search($search);
	}
}
