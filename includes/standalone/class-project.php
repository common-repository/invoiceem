<?php
/*!
 * Project object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Project
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the project object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Project extends InvoiceEM_Object
{
	/**
	 * Column name for the project ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'project_id';

	/**
	 * Column name for the project name.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'project_name';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Projects::SELECT_COLUMNS;

	/**
	 * Raw projects table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_PROJECTS;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $project_id Optional ID of the project to load.
	 * @param  boolean $is_view    True if the project is being viewed.
	 * @return void
	 */
	public function __construct($project_id = 0, $is_view = false)
	{
		if
		(
			!$is_view
			&&
			!current_user_can(InvoiceEM_Constants::CAP_EDIT_PROJECTS)
		)
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct();
		
		$this->_finalize($project_id);
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
			 * ID for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;

			/**
			 * Name for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case self::TITLE_COLUMN:
			
				return '';

			/**
			 * ID of the client record for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Client::ID_COLUMN:
			
				return 0;

			/**
			 * Project website URL.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'website':
			
			/**
			 * Rate for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'rate':
			
			/**
			 * Date that project started.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'start_date':
			
			/**
			 * Date that project ended.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'end_date':
			
				return null;

			/**
			 * Active state of the project.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * History of events for the project.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_HISTORY:
			
				return null;

			/**
			 * Name of the client associated with this project.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Client::TITLE_COLUMN:
			
				return '';

			/**
			 * Invoice prefix for the client associated with this project.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'client_invoice_prefix':
			
			/**
			 * Rate for the client associated with this project.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'client_rate':
			
				return null;
		}

		return parent::_default($name);
	}
	
	/**
	 * Finalize the project.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $project_id ID of the project to load.
	 * @return void
	 */
	private function _finalize($project_id)
	{
		add_filter(InvoiceEM_Constants::HOOK_OBJECT_JOIN, array(__CLASS__, 'object_join'));
		add_filter(InvoiceEM_Constants::HOOK_OBJECT_SELECT, array(__CLASS__, 'object_select'));

		if
		(
			is_numeric($project_id)
			&&
			$project_id > 0
		)
		{
			$this->_load($project_id);
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

				InvoiceEM_Client::ID_COLUMN =>
				(
					!isset($_POST[InvoiceEM_Client::ID_COLUMN])
					||
					!is_numeric($_POST[InvoiceEM_Client::ID_COLUMN])
				)
				? $this->{InvoiceEM_Client::ID_COLUMN}
				: esc_attr($_POST[InvoiceEM_Client::ID_COLUMN]),

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

				'start_date' =>
				(
					!isset($_POST['start_date'])
					||
					empty($_POST['start_date'])
				)
				? $this->start_date
				: date(InvoiceEM_Constants::MYSQL_DATE, strtotime(esc_attr($_POST['start_date']))),

				'end_date' =>
				(
					!isset($_POST['end_date'])
					||
					empty($_POST['end_date'])
				)
				? $this->end_date
				: date(InvoiceEM_Constants::MYSQL_DATE, strtotime(esc_attr($_POST['end_date'])))
			);

			$formats = array('%s', '%d', '%s', '%f', '%s', '%s');
			$is_valid = true;

			if (empty($row[self::TITLE_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a project name.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row[InvoiceEM_Client::ID_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please select a client.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if ($is_valid)
			{
				$this->_load_post($this->base->cache->action, $row, $formats);
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
		
		remove_filter(InvoiceEM_Constants::HOOK_OBJECT_JOIN, array(__CLASS__, 'object_join'));
		remove_filter(InvoiceEM_Constants::HOOK_OBJECT_SELECT, array(__CLASS__, 'object_select'));
	}

	/**
	 * Prepare the SQL JOIN statement for the project.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $join        Raw SQL JOIN statement.
	 * @param  boolean $active_only True if only active records should be included.
	 * @return string               Modified SQL JOIN statement.
	 */
	public static function object_join($join = "", $active_only = false)
	{
		if (!empty($join))
		{
			$join .= " ";
		}
		
		$position = ($active_only)
		? "INNER"
		: "LEFT";

		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);

		$join .= $position . " JOIN " . $clients_table . " ON " . InvoiceEM_Database::get_table_name(self::$_raw_table_name) . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN;
		
		if ($active_only)
		{
			$join .= " AND " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1";
		}
		
		return $join;
	}

	/**
	 * Prepare the SQL SELECT statement for the project.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $select Raw SQL SELECT statement.
	 * @return string         Modified SQL SELECT statement.
	 */
	public static function object_select($select = "")
	{
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);

		return $select . ", " . $clients_table . "." . InvoiceEM_Client::TITLE_COLUMN . " AS " . InvoiceEM_Client::TITLE_COLUMN . ", " . $clients_table . ".rate AS client_rate";
	}

	/**
	 * Setup the project to be added.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  Current action being taken.
	 * @return boolean True if the user can add a project.
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
	 * Prepare the project output.
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
			'description' => __('Name used to identify this project.', 'invoiceem'),
			'label' => __('Project Name', 'invoiceem'),
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
		
		$selected_client = array();
		$client_value = $this->{InvoiceEM_Client::ID_COLUMN};
		
		if (!empty($client_value))
		{
			$client_title = $this->{InvoiceEM_Client::TITLE_COLUMN};
			
			$selected_client = (empty($client_title))
			? InvoiceEM_Client::selected_item($client_value)
			: array
			(
				$client_value => $client_title
			);
		}
		else if
		(
			isset($_GET[InvoiceEM_Client::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Client::ID_COLUMN])
		)
		{
			$client_value = esc_attr($_GET[InvoiceEM_Client::ID_COLUMN]);
			$selected_client = InvoiceEM_Client::selected_item($client_value);
		}
		else if
		(
			isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
		)
		{
			$client_value = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]);
			$selected_client = InvoiceEM_Client::selected_item($client_value);
		}
		
		if (!empty($client_value))
		{
			$this->base->cache->accounting = InvoiceEM_Client::accounting_settings($client_value);
		}

		$general_information_box->add_field(array
		(
			'description' => __('Client that this project is for.', 'invoiceem'),
			'input_classes' => array('iem-accounting'),
			'label' => __('Client', 'invoiceem'),
			'name' => InvoiceEM_Client::ID_COLUMN,
			'options' => $selected_client,
			'table' => InvoiceEM_Constants::TABLE_CLIENTS,
			'type' => 'select',
			'value' => $client_value,

			'attributes' => array
			(
				'placeholder' => __('Select a Client', 'invoiceem')
			),

			'validation' => array
			(
				'required' => true
			)
		));

		$general_information_box->add_field(array
		(
			'description' => __('Primary website URL for this project.', 'invoiceem'),
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
			'description' => __('Rate for this project.', 'invoiceem'),
			'input_classes' => array('iem-currency'),
			'label' => __('Project Rate', 'invoiceem'),
			'name' => 'rate',
			'type' => 'text',
			
			'attributes' => array
			(
				'data-iem-placeholder' => (empty($this->client_rate))
				? $this->base->settings->company->rate
				: $this->client_rate
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
			'description' => __('Date that this project started.', 'invoiceem'),
			'input_classes' => array('iem-datepicker'),
			'label' => __('Start Date', 'invoiceem'),
			'name' => 'start_date',
			'type' => 'text',

			'attributes' => array
			(
				'autocomplete' => 'off',
				'placeholder' => InvoiceEM_Utilities::format_date($date_format)
			),

			'value' => (empty($this->start_date))
			? ''
			: date_i18n($date_format, strtotime($this->start_date))
		));

		$general_information_box->add_field(array
		(
			'description' => __('Date that this project endded.', 'invoiceem'),
			'input_classes' => array('iem-datepicker'),
			'label' => __('End Date', 'invoiceem'),
			'name' => 'end_date',
			'type' => 'text',

			'attributes' => array
			(
				'autocomplete' => 'off',
				'placeholder' => InvoiceEM_Utilities::format_date($date_format, strtotime('+1 month'))
			),

			'value' => (empty($this->end_date))
			? ''
			: date_i18n($date_format, strtotime($this->end_date))
		));

		$this->_history_box();
		$this->_publish_box(__('Project is currently active.', 'invoiceem'));

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}

	/**
	 * Get accounting settings based on a provided project ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $project_id ID of the project to load accounting settings for.
	 * @return array               Formatted accounting settings.
	 */
	public static function accounting_settings($project_id)
	{
		$output = '';
		$results = self::_get_item($project_id);
		
		if (!empty($results))
		{
			$row = $results[0];
			$output = InvoiceEM_Client::accounting_settings($row[InvoiceEM_Client::ID_COLUMN]);
			
			if
			(
				isset($row['rate'])
				&&
				is_numeric($row['rate'])
			)
			{
				$output['rate'] = $row['rate'];
			}
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
		return InvoiceEM_Projects::where_search($search);
	}
}
