<?php
/*!
 * Invoice list object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Invoice List
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the invoice list.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_List
 */
final class InvoiceEM_Invoice_List extends InvoiceEM_List
{
	/**
	 * Column name for invoice IDs.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = InvoiceEM_Invoice::ID_COLUMN;

	/**
	 * Column name for invoice names.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = InvoiceEM_Invoice::TITLE_COLUMN;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Invoices::SELECT_COLUMNS;

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
	 * Filtered project ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @var    integer
	 */
	public static $filter_project_id = 0;
	
	/**
	 * Current timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    integer
	 */
	private $_current_timestamp;
	
	/**
	 * GMT offset for scheduled invoices.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    integer
	 */
	private $_gmt_offset;
	
	/**
	 * Format for time output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string
	 */
	private $_time_format;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.5 Removed list actions action.
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
				? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_INVOICES)
				: InvoiceEM_Constants::CAP_EDIT_INVOICES
			)
		)
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct(__('Invoice', 'invoiceem'), __('Invoices', 'invoiceem'), InvoiceEM_Constants::TABLE_INVOICES);
		
		if
		(
			$this->_is_client
			&&
			(
				$this->_status == InvoiceEM_Constants::STATUS_ACTIVE
				||
				$this->_status == InvoiceEM_Constants::STATUS_SCHEDULED
			)
		)
		{
			$this->_status = InvoiceEM_Constants::STATUS_UNPAID;
		}
		else if
		(
			$this->_status == InvoiceEM_Constants::STATUS_SCHEDULED
			&&
			!$this->base->cache->has_invoices_plus
		)
		{
			$this->_status = InvoiceEM_Constants::STATUS_ACTIVE;
		}
		
		$this->_current_timestamp = time();
		$this->_gmt_offset = get_option('gmt_offset') * 3600;
		$this->_time_format = get_option('time_format');
		
		$this->_prepare_filters();
		
		add_action(InvoiceEM_Constants::HOOK_LIST_DIALOGS, array($this, 'list_dialogs'));
		
		add_filter('default_hidden_columns', array($this, 'default_hidden_columns'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_ACTIVE_LABEL, array($this, 'list_active_label'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_ADD_VIEWS, array($this, 'list_add_views'), 10, 2);
		add_filter(InvoiceEM_Constants::HOOK_LIST_INACTIVE_LABEL, array($this, 'list_inactive_label'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_JOIN, array($this, 'list_join'));
		add_filter(InvoiceEM_Constants::HOOK_LIST_ORDER, array($this, 'list_order'), 10, 2);
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
			self::$filter_client_id =
			(
				!$this->_is_client
				&&
				isset($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			)
			? esc_attr($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			: 0;

			self::$filter_project_id = (isset($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN]))
			? esc_attr($_POST[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN])
			: 0;
		}
		else
		{
			if
			(
				!$this->_is_client
				&&
				isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			)
			{
				self::$filter_client_id = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]);
			}

			if (isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN]))
			{
				self::$filter_project_id = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN]);
			}
		}

		self::$filter_client_id =
		(
			!$this->_is_client
			&&
			is_numeric(self::$filter_client_id)
			&&
			self::$filter_client_id > 0
		)
		? self::$filter_client_id
		: 0;

		self::$filter_project_id =
		(
			is_numeric(self::$filter_project_id)
			&&
			self::$filter_project_id > 0
		)
		? self::$filter_project_id
		: 0;

		if (!$this->_is_client)
		{
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

		$this->_filters[] = new InvoiceEM_Field(array
		(
			'name' => InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN,
			'options' => InvoiceEM_Project::selected_item(self::$filter_project_id),
			'table' => InvoiceEM_Constants::TABLE_PROJECTS,
			'type' => 'select',
			'value' => self::$filter_project_id,

			'attributes' => array
			(
				'placeholder' => __('Filter by Project', 'invoiceem')
			)
		));
	}
	
	/**
	 * Add dialogs associated with the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function list_dialogs()
	{
		if ($this->_status == InvoiceEM_Constants::STATUS_ACTIVE)
		{
			echo '<div id="iem-confirm-send" class="iem-dialog" title="' . esc_attr__('Confirm Send', 'invoiceem') . '">'
			. sprintf
			(
				__('Are you sure you are ready to send %1$s to %2$s?', 'invoiceem'),
				'<strong class="iem-item-name"></strong>',
				'<strong class="iem-client-name"></strong>'
			)
			. '</div>';
		}
		else if
		(
			$this->_status != InvoiceEM_Constants::STATUS_INACTIVE
			&&
			$this->_status != InvoiceEM_Constants::STATUS_ARCHIVED
		)
		{
			echo '<div id="iem-confirm-edit-selected" class="iem-dialog" title="' . esc_attr__('Confirm Edit Selected', 'invoiceem') . '">'
			. __('The selected invoices have already been sent to the clients. Are you sure you want to make changes to them?', 'invoiceem')
			. '</div>'
			. '<div id="iem-confirm-edit" class="iem-dialog" title="' . esc_attr__('Confirm Edit', 'invoiceem') . '">'
			. sprintf
			(
				__('%1$s has already been sent to %2$s. Are you sure you want to make changes to it?', 'invoiceem'),
				'<strong class="iem-item-name"></strong>',
				'<strong class="iem-client-name"></strong>'
			)
			. '</div>'
			. '<div id="iem-confirm-resend" class="iem-dialog" title="' . esc_attr__('Confirm Resend', 'invoiceem') . '">'
			. sprintf
			(
				__('Are you sure you want to resend %1$s to %2$s?', 'invoiceem'),
				'<strong class="iem-item-name"></strong>',
				'<strong class="iem-client-name"></strong>'
			)
			. '</div>';
		}
		
		if ($this->base->cache->has_invoices_plus)
		{
			do_action(InvoiceEM_Constants::HOOK_INVOICE_LIST_DIALOGS, $this->_status);
		}
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
		return array('po_number', 'invoice_title');
	}
	
	/**
	 * Set the label for the active view.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string Active view label.
	 */
	public function list_active_label()
	{
		return __('Draft %1$s', 'invoiceem');
	}
	
	/**
	 * Add additional views to the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array Current views for the list.
	 * @param  array Arguments to remove from the view URLs.
	 * @return array Modified views for the list.
	 */
	public function list_add_views($views, $remove_args)
	{
		if ($this->_is_client)
		{
			$views = array();
		}
		
		$current_class = ' class="current"';
		$filtered_class = ' class="iem-filtered"';
		$viewing_scheduled = ($this->_status == InvoiceEM_Constants::STATUS_SCHEDULED);
		$viewing_unpaid = ($this->_status == InvoiceEM_Constants::STATUS_UNPAID);
		$viewing_overdue = ($this->_status == InvoiceEM_Constants::STATUS_OVERDUE);
		$viewing_paid = ($this->_status == InvoiceEM_Constants::STATUS_PAID);
		$scheduled_class = $unpaid_class = $overdue_class = $paid_class = '';
		$unpaid_count = $this->_item_count(InvoiceEM_Constants::STATUS_UNPAID, false);
		$overdue_count = $this->_item_count(InvoiceEM_Constants::STATUS_OVERDUE, false);
		$paid_count = $this->_item_count(InvoiceEM_Constants::STATUS_PAID, false);
		
		$scheduled_count = ($this->base->cache->has_invoices_plus)
		? $this->_item_count(InvoiceEM_Constants::STATUS_SCHEDULED, false)
		: 0;
		
		if ($viewing_scheduled)
		{
			$scheduled_class = ($scheduled_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($viewing_unpaid)
		{
			$unpaid_class = ($unpaid_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($viewing_overdue)
		{
			$overdue_class = ($overdue_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($viewing_paid)
		{
			$paid_class = ($paid_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		
		if
		(
			!$this->_is_client
			&&
			(
				$scheduled_count > 0
				||
				$viewing_scheduled
			)
		)
		{
			$views[InvoiceEM_Constants::STATUS_SCHEDULED] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_SCHEDULED
				),

				$remove_args
			)
			. '"' . $scheduled_class . '>'
			. sprintf
			(
				__('Scheduled %1$s', 'invoiceem'),
				'<span class="count">(' . $scheduled_count . ')</span>'
			)
			. '</a>';
		}
		
		if
		(
			$unpaid_count > 0
			||
			$viewing_unpaid
		)
		{
			$views[InvoiceEM_Constants::STATUS_UNPAID] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_UNPAID
				),

				$remove_args
			)
			. '"' . $unpaid_class . '>'
			. sprintf
			(
				__('Unpaid %1$s', 'invoiceem'),
				'<span class="count">(' . $unpaid_count . ')</span>'
			)
			. '</a>';
		}
		
		if
		(
			$overdue_count > 0
			||
			$viewing_overdue
		)
		{
			$views[InvoiceEM_Constants::STATUS_OVERDUE] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_OVERDUE
				),

				$remove_args
			)
			. '"' . $overdue_class . '>'
			. sprintf
			(
				__('Overdue %1$s', 'invoiceem'),
				'<span class="count">(' . $overdue_count . ')</span>'
			)
			. '</a>';
		}
		
		if
		(
			$paid_count > 0
			||
			$viewing_paid
		)
		{
			$views[InvoiceEM_Constants::STATUS_PAID] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_PAID
				),

				$remove_args
			)
			. '"' . $paid_class . '>'
			. sprintf
			(
				__('Paid %1$s', 'invoiceem'),
				'<span class="count">(' . $paid_count . ')</span>'
			)
			. '</a>';
		}
		
		return $views;
	}
	
	/**
	 * Set the label for the inactive view.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string Inactive view label.
	 */
	public function list_inactive_label()
	{
		return __('Canceled %1$s', 'invoiceem');
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
		$projects_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);
		
		return $join . $client_join . " JOIN " . $clients_table . " ON " . $this->_table_name . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN . apply_filters(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, " AND ", $clients_table) . " LEFT JOIN " . $projects_table . " ON " . $this->_table_name . "." . InvoiceEM_Project::ID_COLUMN . " = " . $projects_table . "." . InvoiceEM_Project::ID_COLUMN;
	}
	
	/**
	 * Prepare the SQL ORDER statement for the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string  $order      Existing SQL ORDER statement.
	 * @param  boolean $is_default True if the ORDER statement has not been declared by the user.
	 * @return string              New or modified SQL ORDER statement.
	 */
	public function list_order($order = "", $is_default = true)
	{
		if ($is_default)
		{
			if
			(
				$this->_status == InvoiceEM_Constants::STATUS_SCHEDULED
				||
				$this->_status == InvoiceEM_Constants::STATUS_PAID
			)
			{
				$order = " ORDER BY send_date DESC";
			}
			else if
			(
				$this->_status == InvoiceEM_Constants::STATUS_UNPAID
				||
				$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
			)
			{
				$order = " ORDER BY send_date ASC";
			}
		}
		else
		{
			$direction =
			(
				!isset($_REQUEST['order'])
				||
				empty($_REQUEST['order'])
			)
			? " ASC"
			: " " . strtoupper(esc_sql($_REQUEST['order']));

			if ($_REQUEST['orderby'] == 'client_project')
			{
				return " ORDER BY " . InvoiceEM_Client::TITLE_COLUMN . $direction . ", " . InvoiceEM_Project::TITLE_COLUMN . $direction;
			}
			else if ($_REQUEST['orderby'] == 'paid')
			{
				$order .= ", total " . $direction;
			}
		}
		
		return $order;
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
		$projects_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);

		return $select . ", " . $clients_table . "." . InvoiceEM_Client::TITLE_COLUMN . " AS " . InvoiceEM_Client::TITLE_COLUMN . ", " . $clients_table . ".invoice_prefix AS client_invoice_prefix, " . $clients_table . "." . InvoiceEM_Currency::ID_COLUMN . ", " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " AS client_" . InvoiceEM_Constants::COLUMN_IS_ACTIVE . ", " . $projects_table . "." . InvoiceEM_Project::TITLE_COLUMN . " AS " . InvoiceEM_Project::TITLE_COLUMN . ", " . $projects_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " AS project_" . InvoiceEM_Constants::COLUMN_IS_ACTIVE;
	}

	/**
	 * Prepare the SQL WHERE statement for the list.
	 *
	 * @since 1.0.6 Cleaned up database calls.
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
		
		if (!empty(self::$filter_project_id))
		{
			if (!empty($where))
			{
				$where .= " AND ";
			}

			$where .= $wpdb->prepare
			(
				$this->_table_name . "." . InvoiceEM_Project::ID_COLUMN . " = %d",
				self::$filter_project_id
			);
		}

		return $where;
	}
	
	/**
	 * Prepare the client and project column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the client and project column.
	 */
	public function column_client_project($item)
	{
		if ($this->_is_client)
		{
			return $this->column_project($item);
		}
		
		if (empty($item[InvoiceEM_Client::TITLE_COLUMN]))
		{
			return '&ndash;';
		}
		
		$output = '';
		
		if (!$item['client_' . InvoiceEM_Constants::COLUMN_IS_ACTIVE])
		{
			$output = sprintf
			(
				__('%1$s <em>(Inactive)</em>', 'invoiceem'),
				'<strong>' . $item[InvoiceEM_Client::TITLE_COLUMN] . '</strong>'
			);
		}
		else
		{
			$output = '<strong>' . $item[InvoiceEM_Client::TITLE_COLUMN] . '</strong>';
		}

		if
		(
			!empty($item[InvoiceEM_Project::TITLE_COLUMN])
			&&
			$item[InvoiceEM_Project::TITLE_COLUMN] != $item[InvoiceEM_Client::TITLE_COLUMN]
		)
		{
			$output .= '<br />';
			
			if (!$item['project_' . InvoiceEM_Constants::COLUMN_IS_ACTIVE])
			{
				$output .= sprintf
				(
					__('%1$s <em>(Inactive)</em>', 'invoiceem'),
					$item[InvoiceEM_Project::TITLE_COLUMN]
				);
			}
			else
			{
				$output .= $item[InvoiceEM_Project::TITLE_COLUMN];
			}
		}
		
		return $output;
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
		$invoice_url = esc_url(InvoiceEM_Output::invoice_url($item[self::ID_COLUMN]));
		$output = '<a href="' . $invoice_url . '" class="button iem-button iem-iframe-button iem-ignore-filters iem-tooltip" data-iem-tooltip="' . esc_attr__('View Invoice', 'invoiceem') . '"><span class="dashicons dashicons-visibility"></span></a>';
		
		if ($this->base->cache->has_invoices_plus)
		{
			$output = apply_filters(InvoiceEM_Constants::HOOK_INVOICE_ACTIONS, $output, $this->_status, $item[self::ID_COLUMN], $invoice_url);
		}
		
		$is_active = ($this->_status == InvoiceEM_Constants::STATUS_ACTIVE);
		
		if
		(
			(
				$is_active
				||
				$this->_status == InvoiceEM_Constants::STATUS_UNPAID
				||
				$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
			)
			&&
			current_user_can(InvoiceEM_Constants::CAP_EDIT_INVOICES)
		)
		{
			$action = '';
			$title = '';
			
			if ($is_active)
			{
				$action = InvoiceEM_Constants::ACTION_SEND;
				$title = esc_attr__('Send Invoice', 'invoiceem');
			}
			else
			{
				$action = InvoiceEM_Constants::ACTION_RESEND;
				$title = esc_attr__('Resend Invoice', 'invoiceem');
			}
			
			$output .= '<a href="javascript:;" class="button iem-button iem-confirm-' . $action . ' iem-tooltip" data-iem-href="'
			. wp_nonce_url
			(
				InvoiceEM_Utilities::modify_admin_url
				(
					array_merge
					(
						$this->_row_add_args,

						array
						(
							'action' => $action,
							static::ID_COLUMN => $item[static::ID_COLUMN]
						)
					)
				),
				
				InvoiceEM_Utilities::nonce_action($action, $item[static::ID_COLUMN]),
				InvoiceEM_Constants::NONCE
			)
			. '" data-iem-tooltip="' . $title . '"><span class="dashicons dashicons-email-alt2"></span></a>';
		}
		
		$output .= '<a href="javascript:;" class="button iem-button iem-add-note iem-tooltip" data-iem-object-id="' . $item[self::ID_COLUMN] . '" data-iem-tooltip="' . esc_attr__('Add Note', 'invoiceem') . '"><span class="dashicons dashicons-edit"></span></a>';
		
		return '<div class="iem-actions">' . $output . '</div>';
	}
	
	/**
	 * Prepare the dates column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the dates column.
	 */
	public function column_dates($item)
	{
		$send_date = (empty($item['send_date']))
		? ''
		: abs($item['send_date']) + $this->_gmt_offset;
		
		$no_send_date = (empty($send_date));
		
		$invoice_date = ($no_send_date)
		? '&ndash;'
		: date_i18n($this->_date_format, $send_date);
		
		if ($this->_status == InvoiceEM_Constants::STATUS_SCHEDULED)
		{
			return ($no_send_date)
			? $invoice_date
			: $invoice_date . '<br />'
			. date_i18n($this->_time_format, $send_date);
		}
		else if
		(
			$this->_status == InvoiceEM_Constants::STATUS_PAID
			||
			$this->_status == InvoiceEM_Constants::STATUS_INACTIVE
			||
			$this->_status == InvoiceEM_Constants::STATUS_ARCHIVED
		)
		{
			return $invoice_date;
		}
		
		$dates = sprintf
		(
			__('Invoice Date: %1$s', 'invoiceem'),
			$invoice_date
		);
		
		if (!empty($item['last_viewed']))
		{
			$dates .= '<br />'
			. sprintf
			(
				__('Last Viewed: %1$s', 'invoiceem'),
				InvoiceEM_Utilities::format_date($this->_date_format, $item['last_viewed'])
			);
		}
		
		if
		(
			$item['deposit_due'] > 0
			&&
			InvoiceEM_Utilities::calculate_value($item['deposit'], $item['total']) > $item['paid']
		)
		{
			$deposit_due = InvoiceEM_Utilities::format_date($this->_date_format, $item['deposit_due']);

			if ($item['deposit_due'] < $this->_current_timestamp)
			{
				$deposit_due = '<strong class="iem-text-red">' . $deposit_due . '</strong>';
			}
			
			$dates .= '<br />'
			. '<strong>'
			. sprintf
			(
				__('Deposit Due: %1$s', 'invoiceem'),
				$deposit_due
			)
			. '</strong>';
		}
		
		if ($item['payment_due'] > 0)
		{
			$payment_due = InvoiceEM_Utilities::format_date($this->_date_format, $item['payment_due']);

			if ($item['payment_due'] < $this->_current_timestamp)
			{
				$payment_due = '<strong class="iem-text-red">' . $payment_due . '</strong>';
			}
			
			$dates .= '<br />'
			. '<strong>'
			. sprintf
			(
				__('Payment Due: %1$s', 'invoiceem'),
				$payment_due
			)
			. '</strong>';
		}
		
		return $dates;
	}
	
	/**
	 * Prepare the invoice number column.
	 *
	 * @since 1.0.5 Modified column to support advanced invoice number generation.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the invoice number column.
	 */
	public function column_invoice_number($item)
	{
		if (empty($item['invoice_number']))
		{
			return '<span class="iem-default">' . InvoiceEM_Utilities::generate_invoice_number($item, true) . '</span>';
		}
		
		return $item['invoice_number'];
	}
	
	/**
	 * Prepare the invoice title column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the invoice title column.
	 */
	public function column_invoice_title($item)
	{
		return (empty($item['invoice_title']))
		? '<span class="iem-default">' . $this->base->settings->invoicing->invoice_title . '</span>'
		: $item['invoice_title'];
	}
	
	/**
	 * Prepare the project column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the project column.
	 */
	public function column_project($item)
	{
		return (empty($item[InvoiceEM_Project::TITLE_COLUMN]))
		? '&ndash;'
		: $item[InvoiceEM_Project::TITLE_COLUMN];
	}

	/**
	 * Prepare the regarding column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the regarding column.
	 */
	public function column_title($item)
	{
		$edit_class =
		(
			$this->_status == InvoiceEM_Constants::STATUS_UNPAID
			||
			$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
			||
			$this->_status == InvoiceEM_Constants::STATUS_PAID
		)
		? 'iem-confirm-edit'
		: '';
		
		return $this->_first_column($item, true, $edit_class, apply_filters(InvoiceEM_Constants::HOOK_INVOICE_POST_STATE, '', $item));
	}

	/**
	 * Prepare the regarding column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the regarding column.
	 */
	public function column_total($item)
	{
		$accounting_settings = InvoiceEM_Currency::accounting_settings($item[InvoiceEM_Currency::ID_COLUMN]);
		
		if
		(
			$this->_status == InvoiceEM_Constants::STATUS_UNPAID
			||
			$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
		)
		{
			$due = InvoiceEM_Utilities::format_currency($item['total'] - $item['paid'], $accounting_settings);
			
			if
			(
				$item['payment_due'] > 0
				&&
				$item['payment_due'] < $this->_current_timestamp
			)
			{
				$due = '<strong class="iem-text-red">' . $due . '</strong>';
			}
			
			$output = (empty($item['paid']))
			? ''
			: sprintf
			(
				__('Paid: %1$s', 'invoiceem'),
				InvoiceEM_Utilities::format_currency($item['paid'], $accounting_settings)
			)
			. '<br />';
			
			$output .= '<strong>'
			. sprintf
			(
				__('Due: %1$s', 'invoiceem'),
				$due
			)
			. '</strong>';
			
			return $output;
		}
		
		return InvoiceEM_Utilities::format_currency($item['total'], $accounting_settings);
	}
	
	/**
	 * Display the invoice rows.
	 *
	 * @since 1.0.5
	 *
	 * @access public
	 * @return void
	 */
	public function display_rows()
	{
		$sum_row = '';
		
		if ($this->base->cache->has_reporting_plus)
		{
			$join = trim(apply_filters(InvoiceEM_Constants::HOOK_LIST_JOIN, ''));
			$where_search = $this->_where_search();

			if (!empty($join))
			{
				$join = " " . $join;
			}

			if (!empty($where_search))
			{
				$where_search = " AND " . $where_search;
			}

			$where = apply_filters(InvoiceEM_Constants::HOOK_LIST_WHERE, $this->_where_status() . $where_search);
			
			ob_start();

			do_action(InvoiceEM_Constants::HOOK_INVOICE_ROWS, $this->_table_name, $join, $where, $this->get_column_info(), $this->_status);

			echo $sum_row = ob_get_clean();
		}
		
		parent::display_rows();
		
		echo $sum_row;
	}

	/**
	 * Get the list of invoice columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Columns for the invoices table.
	 */
	public function get_columns()
	{
		$columns = ($this->_is_client)
		? array()
		: array('cb' => '<input type="checkbox" />');
		
		$columns['title'] = __('Regarding', 'invoiceem');
		$columns['comment'] = __('Actions', 'invoiceem');
		$columns['invoice_number'] = __('Invoice Number', 'invoiceem');
		
		$columns['client_project'] = ($this->_is_client)
		? __('Project', 'invoiceem')
		: __('Client &amp; Project', 'invoiceem');
		
		if ($this->_status != InvoiceEM_Constants::STATUS_ACTIVE)
		{
			$columns['dates'] =
			(
				$this->_status == InvoiceEM_Constants::STATUS_UNPAID
				||
				$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
			)
			? __('Dates', 'invoiceem')
			: __('Invoice Date', 'invoiceem');
		}
		
		$columns['total'] = __('Total', 'invoieem');
		$columns['po_number'] = __('PO Number', 'invoiceem');
		$columns['invoice_title'] = __('Invoice Title', 'invoiceem');
		
		return $columns;
	}

	/**
	 * Get the list of sortable invoice columns.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array Sortable columns for the invoices table.
	 */
	protected function get_sortable_columns()
	{
		$columns = array
		(
			'title' => array(self::TITLE_COLUMN),
			'invoice_number' => array('invoice_number'),
			'client_project' => array('client_project')
		);
		
		if ($this->_status != InvoiceEM_Constants::STATUS_ACTIVE)
		{
			$columns['dates'] = array('send_date');
		}
		
		$columns['total'] =
		(
			$this->_status == InvoiceEM_Constants::STATUS_UNPAID
			||
			$this->_status == InvoiceEM_Constants::STATUS_OVERDUE
		)
		? array('paid', true)
		: array('total', true);
		
		$columns['po_number'] = array('po_number');
		$columns['invoice_title'] = array('invoice_title');
		
		return $columns;
	}

	/**
	 * Message displayed when there are no invoices.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function no_items()
	{
		if ($this->_status == InvoiceEM_Constants::STATUS_SCHEDULED)
		{
			_e('No scheduled invoices found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_UNPAID)
		{
			_e('No unpaid invoices found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_OVERDUE)
		{
			_e('No overdue invoices found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_PAID)
		{
			_e('No paid invoices found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE)
		{
			_e('No canceled invoices found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_ARCHIVED)
		{
			_e('No archived invoices found.', 'invoiceem');
		}
		else
		{
			_e('No invoice drafts found.', 'invoiceem');
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
		return InvoiceEM_Invoices::where_search($this->base->cache->search_query);
	}

	/**
	 * Get the WHERE criteria for the provided status.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $status Optional status for the WHERE query.
	 * @return string         Generated WHERE query.
	 */
	protected function _where_status($status = '')
	{
		global $wpdb;
		
		$status = (empty($status))
		? $this->_status
		: $status;
		
		$where_status = '';
		
		if ($status == InvoiceEM_Constants::STATUS_ACTIVE)
		{
			$where_status = parent::_where_status($status) . " AND ";
			
			$where_status .= ($this->base->cache->has_invoices_plus)
			? $this->_table_name . ".send_date IS NULL"
			: "(" . $this->_table_name . ".send_date IS NULL OR " . $this->_table_name . ".send_date < 0)";
		}
		else if ($status == InvoiceEM_Constants::STATUS_SCHEDULED)
		{
			$where_status = parent::_where_status($status) . " AND " . $this->_table_name . ".send_date < 0";
		}
		else if
		(
			$status == InvoiceEM_Constants::STATUS_INACTIVE
			||
			$status == InvoiceEM_Constants::STATUS_ARCHIVED
		)
		{
			$where_status = parent::_where_status($status);
		}
		else
		{
			$where_status = parent::_where_status(InvoiceEM_Constants::STATUS_ACTIVE) . " AND " . $this->_table_name . ".send_date IS NOT NULL AND " . $this->_table_name . ".send_date > 0";

			if ($status == InvoiceEM_Constants::STATUS_UNPAID)
			{
				$where_status .= " AND (" . $this->_table_name . ".total > 0 AND (" . $this->_table_name . ".paid IS NULL OR " . $this->_table_name . ".total > " . $this->_table_name . ".paid))";
			}
			else if ($status == InvoiceEM_Constants::STATUS_OVERDUE)
			{
				$where_status .= $wpdb->prepare
				(
					" AND (" . $this->_table_name . ".total > 0 AND (" . $this->_table_name . ".paid IS NULL OR " . $this->_table_name . ".total > " . $this->_table_name . ".paid) AND " . $this->_table_name . ".payment_due > 0 AND " . $this->_table_name . ".payment_due < %d)",
					$this->_current_timestamp
				);
			}
			else if ($status == InvoiceEM_Constants::STATUS_PAID)
			{
				$where_status .= " AND (" . $this->_table_name . ".total <= 0 OR " . $this->_table_name . ".total <= " . $this->_table_name . ".paid)";
			}
		}
		
		return $where_status;
	}
}
