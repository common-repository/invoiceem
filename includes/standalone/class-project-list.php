<?php
/*!
 * Project list object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Project List
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the project list.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_List
 */
final class InvoiceEM_Project_List extends InvoiceEM_List
{
	/**
	 * Column name for project IDs.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = InvoiceEM_Project::ID_COLUMN;

	/**
	 * Column name for project names.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = InvoiceEM_Project::TITLE_COLUMN;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Projects::SELECT_COLUMNS;

	/**
	 * Filtered client ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @var    integer
	 */
	public static $filter_client_id = 0;

	/**
	 * Default rate output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string
	 */
	private $_default_rate;

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
		if
		(
			!current_user_can
			(
				(InvoiceEM()->cache->has_clients_plus)
				? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_PROJECTS)
				: InvoiceEM_Constants::CAP_EDIT_PROJECTS
			)
		)
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct(__('Project', 'invoiceem'), __('Projects', 'invoiceem'), InvoiceEM_Constants::TABLE_PROJECTS);

		$this->_default_rate = '<span class="iem-default">' . InvoiceEM_Utilities::format_currency($this->base->settings->company->rate) . '</span>';
		
		if (!$this->_is_client)
		{
			$this->_prepare_filters();
		}

		add_filter('default_hidden_columns', array($this, 'default_hidden_columns'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_JOIN, array($this, 'list_join'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_SELECT, array($this, 'list_select'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_WHERE, array($this, 'list_where'));
	}

	/**
	 * Prepare the filter fields and values.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _prepare_filters()
	{
		if
		(
			$this->base->cache->is_post
			&&
			isset($_POST[InvoiceEM_Constants::NONCE])
		)
		{
			self::$filter_client_id = (isset($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]))
			? esc_attr($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			: 0;
		}
		else if (isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]))
		{
			self::$filter_client_id = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]);
		}

		self::$filter_client_id =
		(
			is_numeric(self::$filter_client_id)
			&&
			self::$filter_client_id > 0
		)
		? self::$filter_client_id
		: 0;

		$this->_filters[] = new InvoiceEM_Field(array
		(
			'name' => InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN,
			'options' => InvoiceEM_Client::selected_item(self::$filter_client_id),
			'table' => InvoiceEM_Constants::TABLE_CLIENTS,
			'type' => 'select',
			'value' => self::$filter_client_id,

			'attributes' => array
			(
				'placeholder' => __('Filter by Client', 'invoiceem')
			)
		));
	}
	
	/**
	 * Set the default hidden columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Array of default columns to hide.
	 */
	public function default_hidden_columns()
	{
		return array('start_date', 'end_date');
	}

	/**
	 * Prepare the SQL JOIN statement for the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $join Raw SQL JOIN statement.
	 * @return string       Modified SQL JOIN statement.
	 */
	public function list_join($join = "")
	{
		if (!empty($join))
		{
			$join .= " ";
		}

		$client_join = ($this->base->cache->is_client)
		? "INNER"
		: "LEFT";
		
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);
		
		return $join . $client_join . " JOIN " . $clients_table . " ON " . $this->_table_name . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN . apply_filters(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, " AND ", $clients_table);
	}

	/**
	 * Prepare the SQL SELECT statement for the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $select Raw SQL SELECT statement.
	 * @return string         Modified SQL SELECT statement.
	 */
	public function list_select($select = "")
	{
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);

		return $select . ", " . $clients_table . "." . InvoiceEM_Client::TITLE_COLUMN . " AS " . InvoiceEM_Client::TITLE_COLUMN . ", " . $clients_table . "." . InvoiceEM_Currency::ID_COLUMN . ", " . $clients_table . ".rate AS client_rate, " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " AS client_" . InvoiceEM_Constants::COLUMN_IS_ACTIVE;
	}

	/**
	 * Prepare the SQL WHERE statement for the list.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $where Raw SQL WHERE statement.
	 * @return string        Modified SQL WHERE statement.
	 */
	public function list_where($where = "")
	{
		global $wpdb;

		if (!empty(self::$filter_client_id))
		{
			if (!empty($where))
			{
				$where .= " AND ";
			}

			$where .= $wpdb->prepare
			(
				$this->_table_name . "." . InvoiceEM_Client::ID_COLUMN . " = %d",
				self::$filter_client_id
			);
		}

		return $where;
	}

	/**
	 * Prepare the client name column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the since column.
	 */
	public function column_client_name($item)
	{
		if (empty($item[InvoiceEM_Client::TITLE_COLUMN]))
		{
			return '&ndash;';
		}

		if (!$item['client_' . InvoiceEM_Constants::COLUMN_IS_ACTIVE])
		{
			return sprintf
			(
				__('%1$s <em>(Inactive)</em>', 'invoiceem'),
				$item[InvoiceEM_Client::TITLE_COLUMN]
			);
		}

		return $item[InvoiceEM_Client::TITLE_COLUMN];
	}
	
	/**
	 * Prepare the actions column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the actions column.
	 */
	public function column_comment($item)
	{
		$output = '';
		
		if
		(
			$this->_status != InvoiceEM_Constants::STATUS_INACTIVE
			&&
			$this->_status != InvoiceEM_Constants::STATUS_ARCHIVED
			&&
			$this->_can_add_invoices
		)
		{
			$output = '<a href="' . esc_url(admin_url('admin.php?page=' . InvoiceEM_Invoices::PAGE_SLUG . '&action=' . InvoiceEM_Constants::ACTION_ADD . '&' . InvoiceEM_Client::ID_COLUMN . '=' . $item[InvoiceEM_Client::ID_COLUMN] . '&' . self::ID_COLUMN . '=' . $item[self::ID_COLUMN])) . '&' . InvoiceEM_Constants::IFRAME_NONCE . '=' . wp_create_nonce(InvoiceEM_Constants::IFRAME_NONCE) . '" class="button iem-button iem-iframe-button iem-tooltip" data-iem-tooltip="' . esc_attr__('Add Invoice', 'invoiceem') . '"><span class="dashicons dashicons-media-spreadsheet"></span></a>';
		}
		
		$output .= '<a href="javascript:;" class="button iem-button iem-add-note iem-tooltip" data-iem-object-id="' . $item[self::ID_COLUMN] . '" data-iem-tooltip="' . esc_attr__('Add Note', 'invoiceem') . '"><span class="dashicons dashicons-edit"></span></a>';
		
		if (!empty($item['website']))
		{
			$output .= '<a href="' . esc_url($item['website']) . '" target="_blank" rel="noopener noreferrer" class="button iem-button iem-tooltip" data-iem-tooltip="' . esc_attr__('Visit Website', 'invoiceem') . '"><span class="dashicons dashicons-external"></span></a>';
		}
		
		return '<div class="iem-actions">' . $output . '</div>';
	}

	/**
	 * Prepare the end date column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the end date column.
	 */
	public function column_end_date($item)
	{
		return (empty($item['end_date']))
		? '&ndash;'
		: date_i18n($this->_date_format, strtotime($item['end_date']));
	}

	/**
	 * Prepare the rate column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the rate column.
	 */
	public function column_rate($item)
	{
		$has_rate = (!empty($item['rate']));

		if
		(
			!$has_rate
			&&
			empty($item['client_rate'])
		)
		{
			return $this->_default_rate;
		}

		$column = ($has_rate)
		? 'rate'
		: 'client_rate';

		$output = InvoiceEM_Utilities::format_currency($item[$column], InvoiceEM_Currency::accounting_settings($item[InvoiceEM_Currency::ID_COLUMN]));
		
		return ($has_rate)
		? $output
		: '<span class="iem-default">' . $output . '</span>';
	}

	/**
	 * Prepare the start date column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the since column.
	 */
	public function column_start_date($item)
	{
		return (empty($item['start_date']))
		? '&ndash;'
		: date_i18n($this->_date_format, strtotime($item['start_date']));
	}

	/**
	 * Prepare the project name column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the project name column.
	 */
	public function column_title($item)
	{
		return $this->_first_column($item, true);
	}

	/**
	 * Get the list of project columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Columns for the projects table.
	 */
	public function get_columns()
	{
		$columns = ($this->_is_client)
		? array()
		: array('cb' => '<input type="checkbox" />');
		
		$columns['title'] = __('Name', 'invoiceem');
		$columns['comment'] = __('Actions', 'invoiceem');
		
		if (!$this->_is_client)
		{
			$columns[InvoiceEM_Client::TITLE_COLUMN] = __('Client', 'invoiceem');
		}
		
		$columns['rate'] = __('Rate', 'invoiceem');
		$columns['start_date'] = __('Start Date', 'invoiceem');
		$columns['end_date'] = __('End Date', 'invoiceem');
		
		return $columns;
	}

	/**
	 * Get the list of sortable project columns.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array Sortable columns for the projects table.
	 */
	protected function get_sortable_columns()
	{
		return array
		(
			'title' => array(self::TITLE_COLUMN),
			InvoiceEM_Client::TITLE_COLUMN => array(InvoiceEM_Client::TITLE_COLUMN),
			'rate' => array('rate', true),
			'start_date' => array('start_date', true),
			'end_date' => array('end_date', true)
		);
	}

	/**
	 * Message displayed when there are no projects.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function no_items()
	{
		if ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE)
		{
			_e('No inactive projects found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_ARCHIVED)
		{
			_e('No archived projects found.', 'invoiceem');
		}
		else
		{
			_e('No projects found.', 'invoiceem');
		}
	}

	/**
	 * Get the WHERE query for the current search.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return string Generated search WHERE query.
	 */
	protected function _where_search()
	{
		return InvoiceEM_Projects::where_search($this->base->cache->search_query);
	}
}
