<?php
/*!
 * Database object wrapper.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Object
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Wrapper class used for database objects.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
abstract class InvoiceEM_Object extends InvoiceEM_Wrapper
{
	/**
	 * Column name for the object ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = '';

	/**
	 * Column name for the object title.
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
	 * Raw table name associated with this item.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = '';

	/**
	 * True if the current user can add an item.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $_can_add = false;

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
	 * History for this item.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    InvoiceEM_History
	 */
	protected $_history;

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
			empty(static::ID_COLUMN)
			||
			empty(static::TITLE_COLUMN)
		)
		{
			wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
		}
		
		parent::__construct();

		$this->_can_add = current_user_can(InvoiceEM_Constants::CAP_ADD . static::$_raw_table_name);
		$this->_can_delete = current_user_can(InvoiceEM_Constants::CAP_DELETE . static::$_raw_table_name);
	}

	/**
	 * Load the object from the database.
	 *
	 * @since 1.0.6 Cleaned up database calls.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  integer $object_id ID value for the object being loaded.
	 * @return void
	 */
	protected function _load($object_id)
	{
		global $wpdb;

		$table_name = InvoiceEM_Database::get_table_name(static::$_raw_table_name);
		$select = trim(apply_filters(InvoiceEM_Constants::HOOK_OBJECT_SELECT, $table_name . ".*"));
		$join = trim(apply_filters(InvoiceEM_Constants::HOOK_OBJECT_JOIN, ""));
		
		if (!empty($join))
		{
			$join = " " . $join;
		}
		
		$this->_set_properties($wpdb->get_row
		(
			$wpdb->prepare
			(
				"SELECT " . $select . " FROM " . $table_name . $join . " WHERE " . $table_name . "." . sanitize_key(static::ID_COLUMN) . " = %d LIMIT 1",
				$object_id
			),
			
			ARRAY_A
		));
		
		$this->_history = new InvoiceEM_History($this->history);
	}

	/**
	 * Load the object from POST data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $action  Action being taken on the object.
	 * @param  array  $row     Sanitized data for the object being loaded.
	 * @param  array  $formats Format for each column.
	 * @return void
	 */
	protected function _load_post($action, $row, $formats)
	{
		$object_id = false;
		
		if (!isset($row[InvoiceEM_Constants::COLUMN_IS_ACTIVE]))
		{
			$row[InvoiceEM_Constants::COLUMN_IS_ACTIVE] =
			(
				isset($_POST[InvoiceEM_Constants::COLUMN_IS_ACTIVE])
				&&
				!empty($_POST[InvoiceEM_Constants::COLUMN_IS_ACTIVE])
			);

			$formats[] = '%d';
		}
		
		$row = apply_filters(InvoiceEM_Constants::HOOK_OBJECT_ROW . static::$_raw_table_name, $row);

		if ($action == InvoiceEM_Constants::ACTION_ADD)
		{
			$object_id = $this->_post_add($row, $formats);
		}
		else if ($action == InvoiceEM_Constants::ACTION_EDIT)
		{
			$object_id = $this->_post_edit($row, $formats);
		}

		if ($object_id)
		{
			$this->_load($object_id);

			if ($this->base->cache->is_iframe)
			{
				add_filter('admin_body_class', array($this, 'admin_body_class'));
				add_filter(InvoiceEM_Constants::HOOK_FORM_OPTIONS, array($this, 'form_options'));
			}
		}
	}

	/**
	 * Insert object into the database.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  array   $row     Sanitized data for the object being inserted.
	 * @param  array   $formats Format for each column.
	 * @param  string  $nonce   True if the invoice should be added without user or nonce verification.
	 * @return integer          ID of the inserted object.
	 */
	protected function _post_add($row, $formats, $nonce = '')
	{
		global $wpdb;
		
		$can_add = (empty($nonce))
		? $this->_can_add
		: true;
		
		$nonce =
		(
			empty($nonce)
			&&
			isset($_POST[InvoiceEM_Constants::NONCE])
		)
		? esc_attr($_POST[InvoiceEM_Constants::NONCE])
		: $nonce;

		if
		(
			!$can_add
			||
			empty($nonce)
			||
			!InvoiceEM_Utilities::verify_nonce($nonce, InvoiceEM_Constants::ACTION_ADD)
		)
		{
			InvoiceEM_Output::add_admin_notice(__('You are not authorized to add an item.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

			return false;
		}
		
		$this->_add_history(InvoiceEM_Constants::ACTION_ADD);

		$row[InvoiceEM_Constants::COLUMN_HISTORY] = $this->_history->get_serialized();
		$formats[] = '%s';

		$wpdb->insert(InvoiceEM_Database::get_table_name(static::$_raw_table_name), $row, $formats);

		if ($wpdb->insert_id)
		{
			InvoiceEM_Output::add_admin_notice(sprintf
			(
				__('%1$s created successfully.', 'invoiceem'),
				$row[static::TITLE_COLUMN]
			));

			$this->base->cache->action = InvoiceEM_Constants::ACTION_EDIT;

			$this->base->cache->new_url = InvoiceEM_Utilities::modify_admin_url(array
			(
				'action' => InvoiceEM_Constants::ACTION_EDIT,
				static::ID_COLUMN => $wpdb->insert_id
			));
		}
		else
		{
			InvoiceEM_Output::add_admin_notice(__('Item could not be created.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
			
			$this->_set_properties($row);
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update object in the database.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  array  $row     Sanitized data for the object being updated.
	 * @param  array  $formats Format for each column.
	 * @return integer         ID of the updated object.
	 */
	private function _post_edit($row, $formats)
	{
		global $wpdb;

		if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an item to edit.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

			$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		}
		else if
		(
			!isset($_POST[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_POST[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_EDIT)
		)
		{
			InvoiceEM_Output::add_admin_notice(__('You are not authorized to edit this item.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else
		{
			$this->_add_history(InvoiceEM_Constants::ACTION_EDIT);

			$row[InvoiceEM_Constants::COLUMN_HISTORY] = $this->_history->get_serialized();
			$formats[] = '%s';

			if ($wpdb->update(InvoiceEM_Database::get_table_name(static::$_raw_table_name), $row, array(static::ID_COLUMN => $this->{static::ID_COLUMN}), $formats, '%d') === false)
			{
				InvoiceEM_Output::add_admin_notice(__('Item could not be updated.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$this->_set_properties($row);
			}
			else
			{
				if
				(
					$this->base->cache->has_clients_plus
					&&
					isset($_POST['iem_add_note'])
					&&
					isset($_POST['iem_send_to_client'])
				)
				{
					do_action(InvoiceEM_Constants::HOOK_SEND_NOTE, $this->{InvoiceEM_Client::ID_COLUMN}, $this->{static::TITLE_COLUMN}, sanitize_textarea_field($_POST['iem_add_note']));
				}

				InvoiceEM_Output::add_admin_notice(sprintf
				(
					__('%1$s updated successfully.', 'invoiceem'),
					$row[static::TITLE_COLUMN]
				));
			}
		}

		return $this->{static::ID_COLUMN};
	}

	/**
	 * Add the IFRAME closing class to the BODY.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_body_class($classes)
	{
		return $classes . ' iem-iframe-closing';
	}
	
	/**
	 * Amend the form options for jQuery.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $form_options Current form options that should be amended to.
	 * @return void
	 */
	public function form_options($form_options)
	{
		return array_merge
		(
			$form_options,
			
			array
			(
				'iframe' => array
				(
					'id' => $this->{static::ID_COLUMN},
					'label' => static::_generate_label($this->_properties),
					'notices' => InvoiceEM_Output::$admin_notices
				)
			)
		);
	}

	/**
	 * Load the object from GET data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $action Action being taken on the object.
	 * @return mixed          Object ID or true on successful action, otherwise false.
	 */
	protected function _load_get($action)
	{
		$object_id =
		(
			isset($_GET[static::ID_COLUMN])
			&&
			is_numeric($_GET[static::ID_COLUMN])
			&&
			$_GET[static::ID_COLUMN] > 0
		)
		? esc_attr($_GET[static::ID_COLUMN])
		: false;

		if ($object_id)
		{
			$this->_load($object_id);
		}
		
		switch ($action)
		{
			case InvoiceEM_Constants::ACTION_ACTIVATE:
			
				return $this->_get_activate($object_id);
				
			case InvoiceEM_Constants::ACTION_ADD:
			case InvoiceEM_Constants::ACTION_COPY:
			
				return $this->_get_add($action);
				
			case InvoiceEM_Constants::ACTION_DEACTIVATE:
			
				return $this->_get_deactivate($object_id);
				
			case InvoiceEM_Constants::ACTION_DELETE:
			
				return $this->_get_delete($object_id);
				
			case InvoiceEM_Constants::ACTION_EDIT:
			
				return $this->_get_edit($object_id);
		}
		
		return $object_id;
	}

	/**
	 * Activate an object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $object_id ID value for the object being activated.
	 * @return mixed             Object ID if activation is successful, otherwise false.
	 */
	private function _get_activate($object_id)
	{
		global $wpdb;
		
		$output = false;

		if ($object_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an item to activate.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Item does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_ACTIVATE, $object_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to activate %1$s.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else if ($this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE})
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is already active.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else
		{
			$this->_add_history(InvoiceEM_Constants::ACTION_ACTIVATE);
			
			$activated = $wpdb->update
			(
				InvoiceEM_Database::get_table_name(static::$_raw_table_name),

				array
				(
					InvoiceEM_Constants::COLUMN_IS_ACTIVE => 1,
					InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
				),

				array
				(
					static::ID_COLUMN => $object_id
				),

				array('%d', '%s'),
				'%d'
			);

			if ($activated === false)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s could not be activated.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_ERROR
				);
			}
			else
			{
				InvoiceEM_Output::add_admin_notice(sprintf
				(
					__('%1$s activated successfully. %2$s', 'invoiceem'),
					$this->{static::TITLE_COLUMN},

					'<a href="'
					. wp_nonce_url
					(
						InvoiceEM_Utilities::modify_admin_url(array
						(
							'action' => InvoiceEM_Constants::ACTION_DEACTIVATE,
							static::ID_COLUMN => $this->{static::ID_COLUMN}
						)),

						InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_DEACTIVATE, $this->{static::ID_COLUMN}),
						InvoiceEM_Constants::NONCE
					)
					. '" class="iem-single-click">' . __('Undo', 'invoiceem') . '</a>'
				));
				
				$output = $object_id;
			}
		}

		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}

	/**
	 * Check to see if an object can be added.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  Current action being taken.
	 * @return boolean True if the user can add an object.
	 */
	protected function _get_add($action)
	{
		if (!$this->_can_add)
		{
			InvoiceEM_Output::add_admin_notice(__('You are not authorized to add an item.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

			$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
			
			return false;
		}
		
		$this->base->cache->action = InvoiceEM_Constants::ACTION_ADD;
		
		return true;
	}

	/**
	 * Deactivate an object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $object_id ID value for the object being deactivated.
	 * @return mixed             Object ID if deactivation is successful, otherwise false.
	 */
	private function _get_deactivate($object_id)
	{
		global $wpdb;
		
		$output = false;

		if ($object_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an item to deactivate.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Item does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_DEACTIVATE, $object_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to deactivate %1$s.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else if (!$this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE})
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is already inactive.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else
		{
			$this->_add_history(InvoiceEM_Constants::ACTION_DEACTIVATE);
			
			$deactivated = $wpdb->update
			(
				InvoiceEM_Database::get_table_name(static::$_raw_table_name),

				array
				(
					InvoiceEM_Constants::COLUMN_IS_ACTIVE => 0,
					InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
				),

				array
				(
					static::ID_COLUMN => $object_id
				),

				array('%d', '%s'),
				'%d'
			);

			if ($deactivated === false)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s could not be deactivated.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_ERROR
				);
			}
			else
			{
				InvoiceEM_Output::add_admin_notice(sprintf
				(
					__('%1$s deactivated successfully. %2$s', 'invoiceem'),
					$this->{static::TITLE_COLUMN},

					'<a href="'
					. wp_nonce_url
					(
						InvoiceEM_Utilities::modify_admin_url(array
						(
							'action' => InvoiceEM_Constants::ACTION_ACTIVATE,
							static::ID_COLUMN => $this->{static::ID_COLUMN}
						)),

						InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_ACTIVATE, $this->{static::ID_COLUMN}),
						InvoiceEM_Constants::NONCE
					)
					. '" class="iem-single-click">' . __('Undo', 'invoiceem') . '</a>'
				));
				
				$output = $object_id;
			}
		}

		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}

	/**
	 * Delete an object from the database.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $object_id ID value for the object being deleted.
	 * @return mixed             Object ID if deletion is successful, otherwise false.
	 */
	private function _get_delete($object_id)
	{
		global $wpdb;
		
		$output = false;

		if ($object_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an item to delete.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Item does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!$this->_can_delete
			||
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_DELETE, $object_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to delete %1$s.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else
		{
			$deleted = $wpdb->delete
			(
				InvoiceEM_Database::get_table_name(static::$_raw_table_name),

				array
				(
					static::ID_COLUMN => $object_id
				),

				'%d'
			);

			if ($deleted === false)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s could not be deleted.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_ERROR
				);
			}
			else
			{
				InvoiceEM_Output::add_admin_notice(sprintf
				(
					__('%1$s deleted successfully.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				));
				
				$output = $object_id;
			}
		}

		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}

	/**
	 * Load an object to be updated.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $object_id ID of the object being updated.
	 * @return mixed             Object ID if the object load is successful, otherwise false.
	 */
	private function _get_edit($object_id)
	{
		$output = false;
		
		if ($object_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an item to edit.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

			$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Item does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

			$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		}
		else
		{
			$output = $object_id;
		}
		
		return $output;
	}
	
	/**
	 * Prepare the object output.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare()
	{
		add_action('admin_enqueue_scripts', array('InvoiceEM_Global', 'admin_enqueue_scripts_form'), 1000);
		add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_templates'));

		add_screen_option
		(
			'layout_columns',

			array
			(
				'default' => 2,
				'max' => 2
			)
		);
	}

	/**
	 * Get a list of items for a Select2 dropdown.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $search     Search criteria for the list.
	 * @param  integer $page       Current page number for the results.
	 * @param  array   $filters    Additional filters used for the search.
	 * @param  string  $where_base Base WHERE statement.
	 * @param  string  $order      Order of the list.
	 * @return array               Formatted list of items based on the provided search.
	 */
	public static function select2_list($search, $page = 1, $filters = array(), $where_base = "", $order = "")
	{
		global $wpdb;
		
		$where = static::_where_search($search);
		$filters = InvoiceEM_Utilities::check_array($filters);
		$table_name = InvoiceEM_Database::get_table_name(static::$_raw_table_name);
		
		if (!empty($where))
		{
			$where .= " AND ";
		}
		
		$where .= $table_name . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1";
		
		if (!empty($where_base))
		{
			$where .= " AND " . $where_base;
		}
		
		foreach ($filters as $filter)
		{
			$filter_value =
			(
				isset($_POST[$filter])
				&&
				is_numeric($_POST[$filter])
				&&
				$_POST[$filter] > 0
			)
			? esc_attr($_POST[$filter])
			: '';
			
			if
			(
				isset($_POST[InvoiceEM_Constants::FILTER . $filter])
				&&
				is_numeric($_POST[InvoiceEM_Constants::FILTER . $filter])
				&&
				$_POST[InvoiceEM_Constants::FILTER . $filter] > 0
			)
			{
				$filter_value = esc_attr($_POST[InvoiceEM_Constants::FILTER . $filter]);
			}
			
			if (!empty($filter_value))
			{
				$where .= " AND "
				. $wpdb->prepare
				(
					$table_name . "." . $filter . " = %d",
					$filter_value
				);
			}
		}
		
		$join = (method_exists(get_called_class(), 'object_join'))
		? static::object_join("", true)
		: "";
		
		if (!empty($join))
		{
			$join = " " . $join;
		}
		
		$count_select = "COUNT(" . $table_name . "." . static::ID_COLUMN . ")";
		
		if (method_exists(get_called_class(), 'object_select'))
		{
			$count_select = static::object_select($count_select);
		}
		
		return array
		(
			'more' => ($wpdb->get_var("SELECT " . $count_select . " FROM " . $table_name . $join . " WHERE " . $where) > $page * 20),
			'results' => static::_list_format(static::_get_results($where, true, $page, $order))
		);
	}

	/**
	 * Get a selected item based on the provided ID.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $object_id ID of the selected item.
	 * @return array              Formatted item based on the provided ID.
	 */
	public static function selected_item($object_id)
	{
		return static::_list_format(static::_get_item($object_id));
	}
	
	/**
	 * Add a history event to the item.
	 *
	 * @since 1.0.6 Cleaned up database calls.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $event Event that occurred for the item.
	 * @return void
	 */
	protected function _add_history($event)
	{
		global $wpdb;
		
		if (empty($this->_history))
		{
			$history = array();
			
			if (!empty($this->{InvoiceEM_Constants::COLUMN_HISTORY}))
			{
				$history = $this->{InvoiceEM_Constants::COLUMN_HISTORY};
			}
			else if (!empty($this->{static::ID_COLUMN}))
			{
				$events = $wpdb->get_col($wpdb->prepare
				(
					"SELECT " . InvoiceEM_Constants::COLUMN_HISTORY . " FROM " . InvoiceEM_Database::get_table_name(static::$_raw_table_name) . " WHERE " . static::ID_COLUMN . " = %d",
					$this->{static::ID_COLUMN}
				));
				
				if (!empty($events))
				{
					$history = $events[0];
				}
			}
			
			$this->_history = new InvoiceEM_History($history);
		}
		
		$this->_history->add_event($event);
	}

	/**
	 * Generate an object output label.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array  $row Details for the item.
	 * @return string      Generated object label based on the provided row.
	 */
	protected static function _generate_label($row)
	{
		return static::_generate_label_status($row, $row[static::TITLE_COLUMN]);
	}

	/**
	 * Append an inactive status to a label if appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array  $row   Details for the item.
	 * @param  string $label Generated label to append the status to.
	 * @return string        Item label including the status, if applicable.
	 */
	protected static function _generate_label_status($row, $label)
	{
		if (!$row[InvoiceEM_Constants::COLUMN_IS_ACTIVE])
		{
			$label = sprintf
			(
				__('%1$s (Inactive)', 'invoiceem'),
				$label
			);
		}

		return $label;
	}

	/**
	 * Get an item based on the provided ID.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  integer $object_id ID of the item to load.
	 * @return array              Item that matches the provided ID.
	 */
	protected static function _get_item($object_id)
	{
		global $wpdb;
		
		$table_name = InvoiceEM_Database::get_table_name(static::$_raw_table_name);

		return
		(
			empty($object_id)
			||
			!is_numeric($object_id)
			||
			$object_id < 1
		)
		? array()
		: self::_get_results($wpdb->prepare
		(
			InvoiceEM_Database::get_table_name(static::$_raw_table_name) . "." . static::ID_COLUMN . " = %d",
			$object_id
		));
	}

	/**
	 * Get results based on the provided WHERE query.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  string  $where       WHERE query for the results to return.
	 * @param  boolean $active_only True if only active records should be pulled.
	 * @param  integer $page        Current page number for the results.
	 * @param  string  $order       Order of the results.
	 * @return array                Results that match the WHERE query.
	 */
	protected static function _get_results($where = "", $active_only = false, $page = 1, $order = "")
	{
		global $wpdb;

		if (empty(static::$_raw_table_name))
		{
			return array();
		}

		$table_name = InvoiceEM_Database::get_table_name(static::$_raw_table_name);
		$select = "";
		
		foreach (static::SELECT_COLUMNS as $column)
		{
			if (!empty($select))
			{
				$select .= ", ";
			}
			
			$select .= $table_name . "." . $column;
		}
		
		if (method_exists(get_called_class(), 'object_select'))
		{
			$select = static::object_select($select);
		}
		
		$join = (method_exists(get_called_class(), 'object_join'))
		? static::object_join("", $active_only)
		: "";
		
		if (!empty($join))
		{
			$join = " " . $join;
		}
		
		if (!empty($where))
		{
			$where = " WHERE " . $where;
		}
		
		$per_page = 20;
		
		$offset =
		(
			is_numeric($page)
			&&
			$page > 1
		)
		? " OFFSET " . ($per_page * ($page - 1))
		: "";
		
		if (empty($order))
		{
			$order = static::TITLE_COLUMN . " ASC";
		}
		
		return $wpdb->get_results("SELECT " . $select . " FROM " . $table_name . $join . $where . " ORDER BY " . $order . " LIMIT " . $per_page . $offset, ARRAY_A);
	}

	/**
	 * Generate the history meta box.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return void
	 */
	protected function _history_box()
	{
		if (!empty($this->_history))
		{
			$this->_history->meta_box_output(static::$_raw_table_name);
		}
	}

	/**
	 * Format provided results for use in dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array $results Results to format.
	 * @return array          Formatted list of results.
	 */
	protected static function _list_format($results)
	{
		$output = array();

		if (is_array($results))
		{
			foreach ($results as $row)
			{
				$output[$row[static::ID_COLUMN]] = static::_generate_label($row);
			}
		}

		return $output;
	}

	/**
	 * Generate the publish meta box.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  $active_description Description used for the active field.
	 * @param  boolean $always_active      True if the current object cannot be deactivated.
	 * @param  array   $publish_fields     Additional field to add the the publish box.
	 * @return void
	 */
	protected function _publish_box($active_description, $always_active = false, $publish_fields = array())
	{
		$publish_box = new InvoiceEM_Meta_Box(array
		(
			'classes' => array('iem-meta-box-locked'),
			'context' => 'side',
			'id' => 'publish',
			'title' => __('Publish', 'invoiceem')
		));
		
		$publish_fields = InvoiceEM_Utilities::check_array($publish_fields);
		
		foreach ($publish_fields as $field)
		{
			if (is_array($field))
			{
				$publish_box->add_field($field);
			}
		}

		if (!$always_active)
		{
			$publish_box->add_field(array
			(
				'description' => $active_description,
				'label' => __('Is Active', 'invoiceem'),
				'name' => InvoiceEM_Constants::COLUMN_IS_ACTIVE,
				'type' => 'checkbox',
				'value' => $this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE}
			));
		}

		$button_label = $object_id = '';

		if (empty($this->{static::ID_COLUMN}))
		{
			$button_label = __('Publish', 'invoiceem');
		}
		else
		{
			$button_label = __('Update', 'invoiceem');
			$object_id = '<input name="' . static::ID_COLUMN . '" type="hidden" value="' . $this->{static::ID_COLUMN} . '" class="iem-object-id" />';
		}

		$publish_box->add_field(array
		(
			'type' => 'html',

			'content' => '<button type="submit" disabled="disabled" class="button button-large button-primary iem-button"><span>' . $button_label . '</span></button>'
			. '<input name="action" type="hidden" value="' . $this->base->cache->action . '" />'
			. $object_id
		));
	}

	/**
	 * Get the WHERE query for the current search.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  string $search Term used in the search query.
	 * @return string         Generated search WHERE query.
	 */
	protected static function _where_search($search)
	{
		global $wpdb;

		return (empty($search))
		? ""
		: $wpdb->prepare
		(
			$this->_table_name . "." . static::TITLE_COLUMN . " LIKE %s",
			"%" . $wpdb->esc_like($search) . "%"
		);
	}
}
