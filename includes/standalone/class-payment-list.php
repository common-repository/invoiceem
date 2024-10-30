<?php
/*!
 * Payment list object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Payment List
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the paymeent list.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_List
 */
final class InvoiceEM_Payment_List extends InvoiceEM_List
{
	/**
	 * Column name for payment IDs.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = InvoiceEM_Payment::ID_COLUMN;
	
	/**
	 * Column name for payment numbers.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = InvoiceEM_Payment::TITLE_COLUMN;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Payments::SELECT_COLUMNS;

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
	 * True if the current user can edit payments.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    boolean
	 */
	private $_can_edit;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.5 Modified for payment summary output.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $is_summary True if the payment summary report is being viewed.
	 * @return void
	 */
	public function __construct($is_summary = false)
	{
		if (!$is_summary)
		{
			if
			(
				!current_user_can
				(
					(InvoiceEM()->cache->has_clients_plus)
					? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_PAYMENTS)
					: InvoiceEM_Constants::CAP_EDIT_PAYMENTS
				)
			)
			{
				wp_die(__('You are not authorized to view this page.', 'invoiceem'));
			}

			parent::__construct(__('Payment', 'invoiceem'), __('Payments', 'invoiceem'), InvoiceEM_Constants::TABLE_PAYMENTS);

			$this->_can_edit = current_user_can(InvoiceEM_Constants::CAP_EDIT_PAYMENTS);

			if (!$this->_is_client)
			{
				$this->_prepare_filters();
			}

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
	 * Add dialogs associated with the list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function list_dialogs()
	{
		if ($this->_status == InvoiceEM_Constants::STATUS_FAILED)
		{
			echo '<div id="iem-confirm-payment-failed" class="iem-dialog" title="' . esc_attr__('Confirm Payment Failed Notification', 'invoiceem') . '">'
			. sprintf
			(
				__('Are you sure you want to send the payment failed notification for %1$s to %2$s?', 'invoiceem'),
				'<strong class="iem-payment-number"></strong>',
				'<strong class="iem-client-name"></strong>'
			)
			. '</div>';
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_COMPLETED)
		{
			echo '<div id="iem-confirm-payment-completed" class="iem-dialog" title="' . esc_attr__('Confirm Payment Completed Notification', 'invoiceem') . '">'
			. sprintf
			(
				__('Are you sure you want to send the payment completed notification for %1$s to %2$s?', 'invoiceem'),
				'<strong class="iem-payment-number"></strong>',
				'<strong class="iem-client-name"></strong>'
			)
			. '</div>';
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
		return array();
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
		return __('Pending %1$s', 'invoiceem');
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
		$current_class = ' class="current"';
		$filtered_class = ' class="iem-filtered"';
		$viewing_failed = ($this->_status == InvoiceEM_Constants::STATUS_FAILED);
		$viewing_completed = ($this->_status == InvoiceEM_Constants::STATUS_COMPLETED);
		$failed_class = $completed_class = '';
		$failed_count = $this->_item_count(InvoiceEM_Constants::STATUS_FAILED, false);
		$completed_count = $this->_item_count(InvoiceEM_Constants::STATUS_COMPLETED, false);
		
		if ($viewing_failed)
		{
			$failed_class = ($failed_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($viewing_completed)
		{
			$completed_class = ($completed_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		
		if
		(
			$failed_count > 0
			||
			$viewing_failed
		)
		{
			$views[InvoiceEM_Constants::STATUS_FAILED] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_FAILED
				),

				$remove_args
			)
			. '"' . $failed_class . '>'
			. sprintf
			(
				__('Failed %1$s', 'invoiceem'),
				'<span class="count">(' . $failed_count . ')</span>'
			)
			. '</a>';
		}
		
		if
		(
			$completed_count > 0
			||
			$viewing_completed
		)
		{
			$views[InvoiceEM_Constants::STATUS_COMPLETED] = '<a href="'
			. InvoiceEM_Utilities::modify_admin_url
			(
				array
				(
					'status' => InvoiceEM_Constants::STATUS_COMPLETED
				),

				$remove_args
			)
			. '"' . $completed_class . '>'
			. sprintf
			(
				__('Completed %1$s', 'invoiceem'),
				'<span class="count">(' . $completed_count . ')</span>'
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
	public function list_join($join)
	{
		$client_join = ($this->base->cache->is_client)
		? "INNER"
		: "LEFT";
		
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);

		return $client_join . " JOIN " . $clients_table . " ON " . $this->_table_name . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN . apply_filters(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, " AND ", $clients_table);
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
		return ($is_default)
		? " ORDER BY payment_date DESC"
		: $order;
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
	public function list_select($select)
	{
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);

		return $select . ", SUM(" . $this->_table_name . ".amount + COALESCE(" . $this->_table_name . ".bonus, 0) + COALESCE(" . $this->_table_name . ".fee, 0)) AS total, " . $clients_table . "." . InvoiceEM_Client::TITLE_COLUMN . " AS " . InvoiceEM_Client::TITLE_COLUMN . ", " . $clients_table . "." . InvoiceEM_Currency::ID_COLUMN . ", " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " AS client_" . InvoiceEM_Constants::COLUMN_IS_ACTIVE;
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
	public function list_where($where)
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
		
		if ($this->base->cache->has_payments_plus)
		{
			$output = apply_filters(InvoiceEM_Constants::HOOK_PAYMENT_ACTIONS, $output, $item);
		}
		
		if ($this->_can_edit)
		{
			if ($this->_status == InvoiceEM_Constants::STATUS_FAILED)
			{
				$output .= '<a href="javascript:;" class="button iem-button iem-confirm-payment-failed iem-tooltip" data-iem-href="'
				. wp_nonce_url
				(
					InvoiceEM_Utilities::modify_admin_url
					(
						array_merge
						(
							$this->_row_add_args,

							array
							(
								'action' => InvoiceEM_Constants::ACTION_PAYMENT_FAILED,
								static::ID_COLUMN => $item[static::ID_COLUMN]
							)
						)
					),

					InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_PAYMENT_FAILED, $item[static::ID_COLUMN]),
					InvoiceEM_Constants::NONCE
				)
				. '" data-iem-tooltip="' . esc_attr__('Send Payment Failed Notification', 'invoiceem') . '"><span class="dashicons dashicons-email-alt2"></span></a>';
			}
			else if ($this->_status == InvoiceEM_Constants::STATUS_COMPLETED)
			{
				$output .= '<a href="javascript:;" class="button iem-button iem-confirm-payment-completed iem-tooltip" data-iem-href="'
				. wp_nonce_url
				(
					InvoiceEM_Utilities::modify_admin_url
					(
						array_merge
						(
							$this->_row_add_args,

							array
							(
								'action' => InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED,
								static::ID_COLUMN => $item[static::ID_COLUMN]
							)
						)
					),

					InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED, $item[static::ID_COLUMN]),
					InvoiceEM_Constants::NONCE
				)
				. '" data-iem-tooltip="' . esc_attr__('Send Payment Completed Notification', 'invoiceem') . '"><span class="dashicons dashicons-email-alt2"></span></a>';
			}
		}
		
		$output .= '<a href="javascript:;" class="button iem-button iem-add-note iem-tooltip" data-iem-object-id="' . $item[self::ID_COLUMN] . '" data-iem-tooltip="' . esc_attr__('Add Note', 'invoiceem') . '"><span class="dashicons dashicons-edit"></span></a>';
		
		return '<div class="iem-actions">' . $output . '</div>';
	}

	/**
	 * Prepare the payment date column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the payment date column.
	 */
	public function column_payment_date($item)
	{
		return
		(
			empty($item['payment_date'])
			||
			!is_numeric($item['payment_date'])
		)
		? '&ndash;'
		: date_i18n($this->_date_format, $item['payment_date']);
	}

	/**
	 * Prepare the payment number column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the payment number column.
	 */
	public function column_title($item)
	{
		$post_state = '';
		
		if ($this->base->cache->has_payments_plus)
		{
			$post_state = IEM_Payments_Plus()->settings->get_method_label($item['method']);
		}
		else
		{
			switch ($item['method'])
			{
				case 'c':

					$post_state = __('Check', 'invoiceem');

				break;

				case 'd':

					$post_state = __('Direct Deposit', 'invoiceem');

				break;
				
				default:
				
					$post_state = __('Other', 'invoiceem');
			}
		}
		
		return $this->_first_column($item, false, '', $post_state);
	}

	/**
	 * Prepare the total column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the total column.
	 */
	public function column_total($item)
	{
		return (empty($item['total']))
		? '&ndash;'
		: InvoiceEM_Utilities::format_currency($item['total'], InvoiceEM_Currency::accounting_settings($item[InvoiceEM_Currency::ID_COLUMN]));
	}
	
	/**
	 * Display the payment rows.
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

			do_action(InvoiceEM_Constants::HOOK_PAYMENT_ROWS, $this->_table_name, $join, $where, $this->get_column_info());

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
		
		$columns['title'] = __('Payment Number', 'invoiceem');
		$columns['comment'] = __('Actions', 'invoiceem');
		
		if (!$this->_is_client)
		{
			$columns[InvoiceEM_Client::TITLE_COLUMN] = __('Client', 'invoiceem');
		}
		
		$columns['payment_date'] = __('Payment Date', 'invoiceem');
		$columns['total'] = __('Total', 'invoiceem');
		
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
		return array
		(
			'title' => array(self::TITLE_COLUMN),
			InvoiceEM_Client::TITLE_COLUMN => array(InvoiceEM_Client::TITLE_COLUMN),
			'payment_date' => array('payment_date', true),
			'total' => array('total', true)
		);
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
		if ($this->_status == InvoiceEM_Constants::STATUS_FAILED)
		{
			_e('No failed payments found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_COMPLETED)
		{
			_e('No completed payments found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE)
		{
			_e('No canceled payments found.', 'invoiceem');
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_ARCHIVED)
		{
			_e('No archived payments found.', 'invoiceem');
		}
		else
		{
			_e('No pending payments found.', 'invoiceem');
		}
	}
	
	/**
	 * Process the bulk activation.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array $payment_ids Payments IDs for the payments to activate.
	 * @return void
	 */
	protected function _bulk_activate($payment_ids)
	{
		parent::_bulk_activate($payment_ids);
		
		$this->_bulk_update_invoices($payment_ids);
	}
	
	/**
	 * Process the bulk deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array $payment_ids Payments IDs for the payments to deactivate.
	 * @return void
	 */
	protected function _bulk_deactivate($payment_ids)
	{
		parent::_bulk_deactivate($payment_ids);
		
		$this->_bulk_update_invoices($payment_ids);
	}
	
	/**
	 * Update invoices after bulk action.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  array $payment_ids Payments IDs for the payments to update invoices for.
	 * @return void
	 */
	private function _bulk_update_invoices($payment_ids)
	{
		if (is_array($payment_ids))
		{
			foreach ($payment_ids as $payment_id)
			{
				if (is_numeric($payment_id))
				{
					$payment_invoices = new InvoiceEM_Payment_Invoices($payment_id);
					
					$payment_invoices->update_invoices();
				}
			}
		}
	}
	
	/**
	 * Get the WHERE criteria for the provided status.
	 *
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
		
		if ($status == InvoiceEM_Constants::STATUS_FAILED)
		{
			$where_status = parent::_where_status($status) . " AND " . $this->_table_name . ".is_failed = 1";
		}
		else if ($status == InvoiceEM_Constants::STATUS_COMPLETED)
		{
			$where_status = parent::_where_status($status) . " AND " . $this->_table_name . ".is_failed = 0 AND " . $this->_table_name . ".is_completed = 1";
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
			$where_status = parent::_where_status($status) . " AND " . $this->_table_name . ".is_failed = 0 AND " . $this->_table_name . ".is_completed = 0";
		}
		
		return $where_status;
	}
}
