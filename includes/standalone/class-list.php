<?php
/*!
 * Database list wrapper.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage List
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Wrapper class used for database lists.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_List
 */
abstract class InvoiceEM_List extends WP_List_Table
{
	/**
	 * Main instance of InvoiceEM.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    InvoiceEM
	 */
	private static $_instance = null;

	/**
	 * Returns the main instance of InvoiceEM.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return mixed Main list class instance. 
	 */
	public static function _get_instance()
	{
		if (is_null(self::$_instance))
		{
			self::$_instance = new static();
		}

		return self::$_instance;
	}

	/**
	 * Column name for item IDs.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = '';

	/**
	 * Column name for item titles.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = '';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(self::ID_COLUMN, self::TITLE_COLUMN, InvoiceEM_Constants::COLUMN_IS_ACTIVE);

	/**
	 * True if the current user can add invoices.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $_can_add_invoices = false;

	/**
	 * True if the current user can add projects.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $_can_add_projects = false;

	/**
	 * True if the current user can delete an item.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $_can_delete = false;
	
	/**
	 * Date output format.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_date_format;

	/**
	 * Collection of filters to add above the table.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    array
	 */
	protected $_filters = array();
	
	/**
	 * True if a client is viewing the list.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $_is_client;

	/**
	 * Raw database table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected $_raw_table_name;

	/**
	 * Current item status.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    array
	 */
	protected $_row_add_args;

	/**
	 * Current item status.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected $_status;

	/**
	 * Database table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected $_table_name;

	/**
	 * Base object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM
	 */
	public $base = null;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $singular       Singular label for the list.
	 * @param  string $plural         Plural label for the list.
	 * @param  string $raw_table_name Raw table name for the list data.
	 * @return void
	 */
	public function __construct($singular, $plural, $raw_table_name)
	{
		if
		(
			empty(static::ID_COLUMN)
			||
			empty(static::TITLE_COLUMN)
		)
		{
			wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
		}
		
		$this->base = InvoiceEM();
		$this->_raw_table_name = $raw_table_name;
		$this->_can_delete = current_user_can(InvoiceEM_Constants::CAP_ADD . $this->_raw_table_name);
		$this->_can_add_invoices = current_user_can(InvoiceEM_Constants::CAP_ADD_INVOICES);
		$this->_can_add_projects = current_user_can(InvoiceEM_Constants::CAP_ADD_PROJECTS);
		$this->_date_format = get_option('date_format');
		$this->_is_client = $this->base->cache->is_client;
		$this->_table_name = InvoiceEM_Database::get_table_name($this->_raw_table_name);

		$this->_status = (isset($_REQUEST['status']))
		? sanitize_key($_REQUEST['status'])
		: InvoiceEM_Constants::STATUS_ACTIVE;
		
		if
		(
			$this->_is_client
			&&
			(
				$this->_status == InvoiceEM_Constants::STATUS_INACTIVE
				||
				$this->_status == InvoiceEM_Constants::STATUS_ARCHIVED
			)
		)
		{
			$this->_status = InvoiceEM_Constants::STATUS_ACTIVE;
		}

		parent::__construct(array
		(
			'singular' => $singular,
			'plural' => $plural
		));

		add_action('admin_enqueue_scripts', array('InvoiceEM_Global', 'admin_enqueue_scripts_list'), 1000);
		add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_templates'));
		
		add_screen_option
		(
			InvoiceEM_Constants::SETTING_PER_PAGE,

			array
			(
				'default' => 20,
				'label' => __('Number of items per page:', 'invoiceem'),
				'option' => InvoiceEM_Constants::PREFIX . $this->_raw_table_name . '_' . InvoiceEM_Constants::SETTING_PER_PAGE
			)
		);
	}

	/**
	 * Prepare the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the column.
	 */
	public function column_cb($item)
	{
		return '<input name="' . InvoiceEM_Constants::ACTION_BULK . '-action[]" type="checkbox" value="' . $item[static::ID_COLUMN] . '" />';
	}

	/**
	 * Prepare basic columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item        Item being processed.
	 * @param  string $column_name Name of the column being processed.
	 * @return string              Value for the column.
	 */
	public function column_default($item, $column_name)
	{
		return (empty($item[$column_name]))
		? '&ndash;'
		: $item[$column_name];
	}

	/**
	 * Display the item list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function display()
	{
		$this->prepare_items();

		$search = $this->base->cache->search_query;

		$this->_row_add_args = array
		(
			'action' => ''
		);

		if (!empty($search))
		{
			$this->_row_add_args['s'] = $search;
		}

		foreach ($this->_filters as $filter)
		{
			if (is_a($filter, 'InvoiceEM_Field'))
			{
				$value =
				(
					isset(static::${$filter->name})
					&&
					!empty(static::${$filter->name})
				)
				? static::${$filter->name}
				: 0;
				
				if (!empty($value))
				{
					$this->_row_add_args[$filter->name] = $value;
				}
			}
		}

		$remove_args = array('action', 's', InvoiceEM_Constants::NONCE, static::ID_COLUMN);

		foreach ($this->_filters as $filter)
		{
			if
			(
				is_a($filter, 'InvoiceEM_Field')
				&&
				isset(static::${$filter->name})
			)
			{
				$remove_args[] = $filter->name;
			}
		}
		
		$extra_class = ($this->_is_client)
		? ' iem-is-client'
		: '';
		
		echo '<form action="' . InvoiceEM_Utilities::modify_admin_url(false, $remove_args) . '" method="post" class="iem-form">'
		. '<div class="iem-list' . $extra_class . '">';

		wp_nonce_field(InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_BULK), InvoiceEM_Constants::NONCE, false);

		$this->views();
		$this->search_box(__('Search', 'invoiceem'), 'iem-search');

		parent::display();
		
		if
		(
			$this->_status == InvoiceEM_Constants::STATUS_INACTIVE
			||
			$this->_status == InvoiceEM_Constants::STATUS_ARCHIVED
		)
		{
			echo '<div id="iem-confirm-delete-selected" class="iem-dialog" title="' . esc_attr__('Confirm Delete Selected', 'invoiceem') . '">'
			. __('Are you sure you want to delete the selected items? This action cannot be undone.', 'invoiceem')
			. '</div>'
			. '<div id="iem-confirm-delete-all" class="iem-dialog" title="' . esc_attr__('Confirm Delete All', 'invoiceem') . '">'
			. __('Are you sure you want to delete all inactive items? This action cannot be undone.', 'invoiceem')
			. '</div>'
			. '<div id="iem-confirm-delete" class="iem-dialog" title="' . esc_attr__('Confirm Delete', 'invoiceem') . '">'
			. sprintf
			(
				__('Are you sure you want to delete %1$s? This action cannot be undone.', 'invoiceem'),
				'<strong class="iem-item-name"></strong>'
			)
			. '</div>';
		}
		
		do_action(InvoiceEM_Constants::HOOK_LIST_DIALOGS);
		
		echo '</div>'
		. '</form>';
	}

	/**
	 * Generate the table navigation above or below the table.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $which Table nav that is currently being processed.
	 * @return void
	 */
	protected function display_tablenav($which)
	{
		ob_start();

		parent::display_tablenav($which);

		echo $this->_fix_urls(ob_get_clean());
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $which Table nav that is currently being processed.
	 * @return void
	 */
	protected function extra_tablenav($which)
	{
		$actions_open = '<div class="alignleft actions">';
		$actions_close = '</div>';
		$filtered = false;
		
		if ($which == 'top')
		{
			if (!empty($this->_filters))
			{
				$filters = '';

				foreach ($this->_filters as $filter)
				{
					if (is_a($filter, 'InvoiceEM_Field'))
					{
						$filtered =
						(
							$filtered
							||
							!empty($filter->value)
						);

						$filters .= $filter->output();
					}
				}

				if
				(
					$filtered
					||
					$this->_pagination_args['total_items'] > 0
				)
				{
					echo $actions_open
					. $filters;

					submit_button(__('Filter', 'invoiceem'), '', 'iem-filter', false);

					echo $actions_close;
				}
			}
			
			if
			(
				$this->_can_delete
				&&
				$this->_status == InvoiceEM_Constants::STATUS_INACTIVE
				&&
				!$filtered
				&&
				empty($this->base->cache->search_query)
				&&
				!empty($this->items)
			)
			{
				echo $actions_open;

				submit_button(__('Delete All', 'invoiceem'), 'apply', InvoiceEM_Constants::ACTION_DELETE_ALL, false);

				echo $actions_close;
			}
			
			do_action(InvoiceEM_Constants::HOOK_LIST_ACTIONS);
		}
	}

	/**
	 * Get the list of bulk actions.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Bulk actions for the list.
	 */
	public function get_bulk_actions()
	{
		$actions = array();
		
		if (!$this->_is_client)
		{
			$is_inactive = ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE);
			$prefix = InvoiceEM_Constants::ACTION_BULK . '-';

			if
			(
				$is_inactive
				||
				$this->_status == InvoiceEM_Constants::STATUS_ARCHIVED
			)
			{
				if ($is_inactive)
				{
					$actions[$prefix . InvoiceEM_Constants::ACTION_ACTIVATE] = __('Activate', 'invoiceem');
				}

				if ($this->_can_delete)
				{
					$actions[$prefix . InvoiceEM_Constants::ACTION_DELETE] = __('Delete Permanently', 'invoiceem');
				}
			}
			else
			{
				$actions[$prefix . InvoiceEM_Constants::ACTION_DEACTIVATE] = __('Deactivate', 'invoiceem');
			}
		}

		return $actions;
	}

	/**
	 * Get the views for this list.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_views()
	{
		$current_class = ' class="current"';
		$filtered_class = ' class="iem-filtered"';
		$active_class = $inactive_class = $archived_class = '';
		$viewing_inactive = ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE);
		$viewing_archived = ($this->_status == InvoiceEM_Constants::STATUS_ARCHIVED);
		$active_count = $this->_item_count(InvoiceEM_Constants::STATUS_ACTIVE, false);
		$inactive_count = $this->_item_count(InvoiceEM_Constants::STATUS_INACTIVE, false);
		$archived_count = $this->_item_count(InvoiceEM_Constants::STATUS_ARCHIVED, false);
		$remove_args = array('action', 'paged', 's', InvoiceEM_Constants::NONCE, static::ID_COLUMN);

		if ($viewing_inactive)
		{
			$inactive_class = ($inactive_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($viewing_archived)
		{
			$archived_class = ($archived_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}
		else if ($this->_status == InvoiceEM_Constants::STATUS_ACTIVE)
		{
			$active_class = ($active_count == $this->_pagination_args['total_items'])
			? $current_class
			: $filtered_class;
		}

		foreach ($this->_filters as $filter)
		{
			if
			(
				is_a($filter, 'InvoiceEM_Field')
				&&
				isset(static::${$filter->name})
			)
			{
				$remove_args[] = $filter->name;
			}
		}

		$views = array
		(
			InvoiceEM_Constants::STATUS_ACTIVE => '<a href="' . InvoiceEM_Utilities::modify_admin_url(false, array_merge($remove_args, array('status'))) . '"' . $active_class . '>'
			. sprintf
			(
				apply_filters(InvoiceEM_Constants::HOOK_LIST_ACTIVE_LABEL, __('Active %1$s', 'invoiceem')),
				'<span class="count">(' . $active_count . ')</span>'
			)
			. '</a>'
		);
		
		$views = apply_filters(InvoiceEM_Constants::HOOK_LIST_ADD_VIEWS, $views, $remove_args);

		if (!$this->_is_client)
		{
			if
			(
				$inactive_count > 0
				||
				$viewing_inactive
			)
			{
				$views[InvoiceEM_Constants::STATUS_INACTIVE] = '<a href="'
				. InvoiceEM_Utilities::modify_admin_url
				(
					array
					(
						'status' => InvoiceEM_Constants::STATUS_INACTIVE
					),

					$remove_args
				)
				. '"' . $inactive_class . '>'
				. sprintf
				( 
					apply_filters(InvoiceEM_Constants::HOOK_LIST_INACTIVE_LABEL, __('Inactive %1$s', 'invoiceem')),
					'<span class="count">(' . $inactive_count . ')</span>'
				)
				. '</a>';
			}

			if
			(
				$archived_count > 0
				||
				$viewing_archived
			)
			{
				$views[InvoiceEM_Constants::STATUS_ARCHIVED] = '<a href="'
				. InvoiceEM_Utilities::modify_admin_url
				(
					array
					(
						'status' => InvoiceEM_Constants::STATUS_ARCHIVED
					),

					$remove_args
				)
				. '"' . $archived_class . '>'
				. sprintf
				( 
					__('Archived %1$s', 'invoiceem'),
					'<span class="count">(' . $archived_count . ')</span>'
				)
				. '</a>';
			}
		}

		return $views;
	}

	/**
	 * Prepare the items to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare_items()
	{
		$this->_column_headers = $this->get_column_info();

		$this->process_bulk_action();

		$per_page = $this->get_items_per_page(InvoiceEM_Constants::PREFIX . $this->_raw_table_name . '_per_page', 20);
		$total_items = $this->_item_count();

		$this->set_pagination_args(array
		(
			'total_items' => $total_items,
			'per_page' => $per_page
		));

		$this->items = $this->_get_items($per_page, $this->get_pagenum());
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $with_id True if the ID attribute should be set.
	 * @return void
	 */
	public function print_column_headers($with_id = true)
	{
		ob_start();

		parent::print_column_headers($with_id);

		echo $this->_fix_urls(ob_get_clean());
	}

	/**
	 * Process a bulk action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function process_bulk_action()
	{
		global $wpdb;

		if
		(
			isset($_POST[InvoiceEM_Constants::NONCE])
			&&
			InvoiceEM_Utilities::verify_nonce(esc_attr($_POST[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_BULK)
		)
		{
			if (isset($_POST[InvoiceEM_Constants::ACTION_DELETE_ALL]))
			{
				$this->_bulk_delete_all();
			}
			else
			{
				$prefix = InvoiceEM_Constants::ACTION_BULK . '-';
				$prefix_length = strlen($prefix);

				$action =
				(
					isset($_POST['action'])
					&&
					substr($_POST['action'], 0, $prefix_length) == $prefix
				)
				? esc_attr($_POST['action'])
				: '';

				if
				(
					empty($action)
					&&
					isset($_POST['action2'])
					&&
					substr($_POST['action2'], 0, $prefix_length) == $prefix
				)
				{
					$action = esc_attr($_POST['action2']);
				}

				if (!empty($action))
				{
					$object_ids = (isset($_POST[InvoiceEM_Constants::ACTION_BULK . '-action']))
					? InvoiceEM_Utilities::check_array($_POST[InvoiceEM_Constants::ACTION_BULK . '-action'])
					: array();

					if ($action == $prefix . InvoiceEM_Constants::ACTION_ACTIVATE)
					{
						$this->_bulk_activate($object_ids);
					}
					else if ($action == $prefix . InvoiceEM_Constants::ACTION_DEACTIVATE)
					{
						$this->_bulk_deactivate($object_ids);
					}
					else if ($action == $prefix . InvoiceEM_Constants::ACTION_DELETE)
					{
						$this->_bulk_delete($object_ids);
					}
				}
			}
		}
	}

	/**
	 * Process the bulk activation.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array $object_ids Item IDs for the objects to activate.
	 * @return void
	 */
	protected function _bulk_activate($object_ids)
	{
		global $wpdb;

		if (empty($object_ids))
		{
			echo InvoiceEM_Output::admin_notice(__('Please select at least one item to activate.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
		}
		else
		{
			$total_activated = 0;

			foreach ($object_ids as $object_id)
			{
				if ($total_activated !== false)
				{
					$activated = $wpdb->update
					(
						$this->_table_name,

						array
						(
							InvoiceEM_Constants::COLUMN_IS_ACTIVE => 1
						),

						array
						(
							static::ID_COLUMN => $object_id
						),

						'%d',
						'%d'
					);

					$total_activated = ($activated === false)
					? false
					: $total_activated + $activated;
				}
			}

			if ($total_activated === false)
			{
				echo InvoiceEM_Output::admin_notice(__('Items could not be activated at this time.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
			}
			else if ($total_activated == 0)
			{
				echo InvoiceEM_Output::admin_notice(__('None of the selected items were inactive.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
			}
			else
			{
				echo InvoiceEM_Output::admin_notice(sprintf
				(
					_n('%1$s item was activated successfully.', '%1$s items were activated successfully.', $total_activated, 'invoiceem'),
					$total_activated
				));
			}
		}
	}

	/**
	 * Process the bulk deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array $object_ids Item IDs for the objects to deactivate.
	 * @return void
	 */
	protected function _bulk_deactivate($object_ids)
	{
		global $wpdb;

		if (empty($object_ids))
		{
			echo InvoiceEM_Output::admin_notice(__('Please select at least one item to deactivate.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
		}
		else
		{
			$total_deactivated = 0;

			foreach ($object_ids as $object_id)
			{
				if ($total_deactivated !== false)
				{
					$deactivated = $wpdb->update
					(
						$this->_table_name,

						array
						(
							InvoiceEM_Constants::COLUMN_IS_ACTIVE => 0
						),

						array
						(
							static::ID_COLUMN => $object_id
						),

						'%d',
						'%d'
					);

					$total_deactivated = ($deactivated === false)
					? false
					: $total_deactivated + $deactivated;
				}
			}

			if ($total_deactivated === false)
			{
				echo InvoiceEM_Output::admin_notice(__('Items could not be deactivated at this time.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
			}
			else if ($total_deactivated == 0)
			{
				echo InvoiceEM_Output::admin_notice(__('None of the selected items were active.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
			}
			else
			{
				echo InvoiceEM_Output::admin_notice(sprintf
				(
					_n('%1$s item was deactivated successfully.', '%1$s items were deactivated successfully.', $total_deactivated, 'invoiceem'),
					$total_deactivated
				));
			}
		}
	}

	/**
	 * Process the bulk deletion.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  array $object_ids Item IDs for the objects to delete.
	 * @return void
	 */
	private function _bulk_delete($object_ids)
	{
		global $wpdb;

		if (!$this->_can_delete)
		{
			echo InvoiceEM_Output::admin_notice(__('You are not authorized to delete items.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if (empty($object_ids))
		{
			echo InvoiceEM_Output::admin_notice(__('Please select at least one item to delete.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
		}
		else
		{
			$total_deleted = 0;

			foreach ($object_ids as $object_id)
			{
				if ($total_deleted !== false)
				{
					$deleted = $wpdb->delete
					(
						$this->_table_name,

						array
						(
							static::ID_COLUMN => $object_id
						),

						'%d'
					);

					$total_deleted = ($deleted === false)
					? false
					: $total_deleted + $deleted;
				}
			}

			if ($total_deleted === false)
			{
				echo InvoiceEM_Output::admin_notice(__('Items could not be deleted at this time.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
			}
			else if ($total_deleted == 0)
			{
				echo InvoiceEM_Output::admin_notice(__('None of the selected items existed.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
			}
			else
			{
				echo InvoiceEM_Output::admin_notice(sprintf
				(
					_n('%1$s item was deleted successfully.', '%1$s items were deleted successfully.', $total_deleted, 'invoiceem'),
					$total_deleted
				));
			}
		}
	}

	/**
	 * Delete all inactive items.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _bulk_delete_all()
	{
		global $wpdb;

		if (!$this->_can_delete)
		{
			echo InvoiceEM_Output::admin_notice(__('You are not authorized to delete all inactive items.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else
		{
			$deleted = $wpdb->delete
			(
				$this->_table_name,

				array
				(
					InvoiceEM_Constants::COLUMN_IS_ACTIVE => 0
				),

				'%d'
			);

			if ($deleted === false)
			{
				echo InvoiceEM_Output::admin_notice(__('The inactive items could not be deleted at this time.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
			}
			else if ($deleted == 0)
			{
				echo InvoiceEM_Output::admin_notice(__('There were no inactive items to delete.', 'invoiceem'), InvoiceEM_Constants::NOTICE_WARNING);
			}
			else
			{
				echo InvoiceEM_Output::admin_notice(sprintf
				(
					_n('%1$s inactive item was successfully deleted.', '%1$s inactive items were successfully deleted.', $deleted, 'invoiceem'),
					$deleted
				));
			}
		}
	}

	/**
	 * Default content for the first column.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array   $item       Item being processed.
	 * @param  boolean $copyable   True if the items in the current list can be copied.
	 * @param  string  $edit_class Optional class to add to edit links.
	 * @param  string  $post_state Optional state to append to the title.
	 * @return string              First column content.
	 */
	protected function _first_column($item, $copyable = false, $edit_class = '', $post_state = '')
	{
		$edit_href = 'href';
		$deactivate_class = $edit_link_open = $edit_link_close = '';
		$row_actions = array();
		
		if (!$this->_is_client)
		{
			if (!empty($edit_class))
			{
				$edit_href = 'href="javascript:;" data-iem-href';
				$edit_class = esc_attr($edit_class);
				$deactivate_class = ' ' . $edit_class;
				$edit_class = ' class="' . $edit_class . '"';
			}

			$add_args = array_merge
			(
				$this->_row_add_args,

				array
				(
					static::ID_COLUMN => $item[static::ID_COLUMN]
				)
			);

			$add_args['action'] = InvoiceEM_Constants::ACTION_EDIT;
			$edit_link_open = '<a ' . $edit_href . '="' . InvoiceEM_Utilities::modify_admin_url($add_args) . '"' . $edit_class . '>';
			$edit_link_close = '</a>';
			$row_actions[InvoiceEM_Constants::ACTION_EDIT] = $edit_link_open . __('Edit', 'invoiceem') . $edit_link_close;
			$is_archive = ($this->_status == InvoiceEM_Constants::STATUS_ARCHIVED);
			
			if
			(
				$copyable
				&&
				!$is_archive
			)
			{
				$add_args['action'] = InvoiceEM_Constants::ACTION_COPY;
				$row_actions[InvoiceEM_Constants::ACTION_COPY] = '<a href="' . InvoiceEM_Utilities::modify_admin_url($add_args) . '">' . __('Copy', 'invoiceem') . '</a>';
			}

			if ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE)
			{
				$add_args['action'] = InvoiceEM_Constants::ACTION_ACTIVATE;

				$row_actions[InvoiceEM_Constants::ACTION_ACTIVATE] = '<a href="' . wp_nonce_url(InvoiceEM_Utilities::modify_admin_url($add_args), InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_ACTIVATE, $item[static::ID_COLUMN]), InvoiceEM_Constants::NONCE) . '" class="iem-single-click">' . __('Activate', 'invoiceem') . '</a>';

				if ($this->_can_delete)
				{
					$add_args['action'] = InvoiceEM_Constants::ACTION_DELETE;

					$row_actions[InvoiceEM_Constants::ACTION_DELETE] = '<a href="javascript:;" class="submitdelete iem-confirm-delete" data-iem-href="' . wp_nonce_url(InvoiceEM_Utilities::modify_admin_url($add_args), InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_DELETE, $item[static::ID_COLUMN]), InvoiceEM_Constants::NONCE) . '">' . __('Delete Permanently', 'invoiceem') . '</a>';
				}
			}
			else if ($is_archive)
			{
				if ($this->_can_delete)
				{
					$add_args['action'] = InvoiceEM_Constants::ACTION_DELETE;

					$row_actions[InvoiceEM_Constants::ACTION_DELETE] = '<a href="javascript:;" class="submitdelete iem-confirm-delete" data-iem-href="' . wp_nonce_url(InvoiceEM_Utilities::modify_admin_url($add_args), InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_DELETE, $item[static::ID_COLUMN]), InvoiceEM_Constants::NONCE) . '">' . __('Delete Permanently', 'invoiceem') . '</a>';
				}
			}
			else
			{
				$add_args['action'] = InvoiceEM_Constants::ACTION_DEACTIVATE;

				$row_actions[InvoiceEM_Constants::ACTION_DEACTIVATE] = '<a ' . $edit_href . '="' . wp_nonce_url(InvoiceEM_Utilities::modify_admin_url($add_args), InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_DEACTIVATE, $item[static::ID_COLUMN]), InvoiceEM_Constants::NONCE) . '" class="iem-single-click' . $deactivate_class . '">' . __('Deactivate', 'invoiceem') . '</a>';
			}
		}
		
		if (!empty($post_state))
		{
			$post_state = ' &mdash; ' . $post_state;
		}
		
		return '<strong>' . $edit_link_open . $item[static::TITLE_COLUMN] . $edit_link_close . $post_state . '</strong>'
		. $this->row_actions($row_actions);
	}

	/**
	 * Fix URLs in provided content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $content Contain containing the URLs to fix.
	 * @return string          Content with fixed URLs.
	 */
	protected function _fix_urls($content)
	{
		$page = '?page=' . $this->base->cache->current_page;
		$search = $this->base->cache->search_query;
		$remove_args = array('action', InvoiceEM_Constants::NONCE, static::ID_COLUMN);
		$append = '';
		$remove = array();

		if (empty($search))
		{
			$remove_args[] = 's';
		}
		else if (!isset($_GET['s']))
		{
			$append = '&s=' . esc_attr($search);
		}

		foreach ($this->_filters as $filter)
		{
			if (is_a($filter, 'InvoiceEM_Field'))
			{
				$value =
				(
					isset(static::${$filter->name})
					&&
					!empty(static::${$filter->name})
				)
				? static::${$filter->name}
				: 0;

				if (empty($value))
				{
					$remove_args[] = $filter->name;
				}
				else if (!isset($_GET[$filter->name]))
				{
					$append .= '&' . $filter->name . '=' . esc_attr($value);
				}
			}
		}

		$content = (empty($append))
		? $content
		: str_replace($page, $page . $append, $content);

		foreach ($remove_args as $arg)
		{
			if (isset($_GET[$arg]))
			{
				$remove[] = '&#038;' . $arg . '=' . esc_attr($_GET[$arg]);
			}
		}

		return str_replace($remove, '', $content);
	}

	/**
	 * Get the items from the database.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $per_page Optional number of items to display on each page.
	 * @param  integer $page     Optional current page number.
	 * @return array             Items from the database.
	 */
	private function _get_items($per_page = 20, $page = 1)
	{
		global $wpdb;
		
		$select = "";
		
		foreach (static::SELECT_COLUMNS as $column)
		{
			if (!empty($select))
			{
				$select .= ", ";
			}
			
			$select .= $this->_table_name . "." . $column;
		}

		$select = trim(apply_filters(InvoiceEM_Constants::HOOK_LIST_SELECT, $select));
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
		$sql = "SELECT " . $select . " FROM " . $this->_table_name . " " . $join . " WHERE " . $where . " GROUP BY " . $this->_table_name . "." . static::ID_COLUMN;

		if
		(
			!isset($_REQUEST['orderby'])
			||
			empty($_REQUEST['orderby'])
		)
		{
			$sql .= apply_filters(InvoiceEM_Constants::HOOK_LIST_ORDER, " ORDER BY " . static::TITLE_COLUMN . " ASC");
		}
		else
		{
			$order = " ORDER BY " . esc_sql($_REQUEST['orderby']);

			$order .=
			(
				!isset($_REQUEST['order'])
				||
				empty($_REQUEST['order'])
			)
			? " ASC"
			: " " . strtoupper(esc_sql($_REQUEST['order']));
			
			$sql .= apply_filters(InvoiceEM_Constants::HOOK_LIST_ORDER, $order, false);
		}

		$sql .= " LIMIT " . $per_page;
		$sql .= " OFFSET " . (($page - 1) * $per_page);

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get the total number of items.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  $status         Optional status for the count to obtain.
	 * @param  boolean $include_search True if the search should be included in the count.
	 * @return integer                 Number of items in the database.
	 */
	protected function _item_count($status = '', $include_search = true)
	{
		global $wpdb;

		$join = trim(apply_filters(InvoiceEM_Constants::HOOK_LIST_JOIN, ''));
		
		if (!empty($join))
		{
			$join = " " . $join;
		}
		
		$where = $this->_where_status($status);
		
		if ($include_search)
		{
			$where_search = ($include_search)
			? $this->_where_search()
			: "";
			
			if (!empty($where_search))
			{
				$where .= " AND " . $where_search;
			}
			
			$where = trim(apply_filters(InvoiceEM_Constants::HOOK_LIST_WHERE, $where));
		}
		
		return (empty($where))
		? 0
		: $wpdb->get_var("SELECT COUNT(*) FROM " . $this->_table_name . $join . " WHERE " . $where);
	}

	/**
	 * Get the WHERE query for the current search.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return string Generated search WHERE query.
	 */
	protected function _where_search()
	{
		global $wpdb;

		$search = $this->base->cache->search_query;
		
		return (empty($search))
		? ""
		: $wpdb->prepare
		(
			$this->_table_name . "." . static::TITLE_COLUMN . " LIKE %s",
			"%" . $wpdb->esc_like($search) . "%"
		);
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
		$status = (empty($status))
		? $this->_status
		: $status;

		$where_status = '';
		$related = array();

		foreach ($this->_filters as $filter)
		{
			if
			(
				is_a($filter, 'InvoiceEM_Field')
				&&
				!isset($related[$filter->table])
			)
			{
				$related[$filter->table] = InvoiceEM_Database::get_table_name($filter->table);
			}
		}

		if ($status == InvoiceEM_Constants::STATUS_INACTIVE)
		{
			$where_status = $this->_table_name . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 0";
		}
		else if ($status == InvoiceEM_Constants::STATUS_ARCHIVED)
		{
			foreach ($related as $table)
			{
				if (!empty($where_status))
				{
					$where_status .= " OR ";
				}
				
				$where_status .= $table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 0";
			}
			
			if (!empty($where_status))
			{
				$where_status = "(" . $where_status . ")";
			}
		}
		else
		{
			$where_status = $this->_table_name . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1";

			foreach ($related as $table)
			{
				$where_status .= " AND (" . $table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1 OR " . $table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " IS NULL)";
			}
		}

		return $where_status;
	}
}
