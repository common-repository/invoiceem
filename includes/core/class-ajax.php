<?php
/*!
 * Functionality for AJAX calls.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage AJAX
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement AJAX functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Ajax extends InvoiceEM_Wrapper
{
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
		parent::__construct();

		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_ACCOUNTING, array($this, 'accounting'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_ADD_NOTE, array($this, 'add_note'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_HISTORY, array($this, 'history'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_REPORTING, array($this, 'reporting'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_SELECT2_CLIENTS, array($this, 'select2_clients'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_SELECT2_COUNTRIES, array($this, 'select2_countries'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_SELECT2_CURRENCIES, array($this, 'select2_currencies'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_SELECT2_INVOICES, array($this, 'select2_invoices'));
		add_action('wp_ajax_' . InvoiceEM_Constants::HOOK_SELECT2_PROJECTS, array($this, 'select2_projects'));
	}

	/**
	 * Load accounting settings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function accounting()
	{
		$output = $this->base->cache->accounting;

		if
		(
			isset($_POST[InvoiceEM_Project::ID_COLUMN])
			&&
			is_numeric($_POST[InvoiceEM_Project::ID_COLUMN])
		)
		{
			$output = InvoiceEM_Project::accounting_settings(esc_attr($_POST[InvoiceEM_Project::ID_COLUMN]));
		}
		else if
		(
			isset($_POST[InvoiceEM_Client::ID_COLUMN])
			&&
			is_numeric($_POST[InvoiceEM_Client::ID_COLUMN])
		)
		{
			$output = InvoiceEM_Client::accounting_settings(esc_attr($_POST[InvoiceEM_Client::ID_COLUMN]));
		}
		else if
		(
			isset($_POST[InvoiceEM_Currency::ID_COLUMN])
			&&
			is_numeric($_POST[InvoiceEM_Currency::ID_COLUMN])
		)
		{
			$page = (isset($_POST['page']))
			? esc_attr($_POST['page'])
			: '';

			$output = InvoiceEM_Currency::accounting_settings(esc_attr($_POST[InvoiceEM_Currency::ID_COLUMN]), $page);
		}

		wp_send_json($output);
	}
	
	/**
	 * Add a note for a provided record.
	 *
	 * @since 1.0.6 Cleaned up database calls.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_note()
	{
		global $wpdb;
		
		$success = false;
		
		if
		(
			isset($_POST['note'])
			&&
			!empty($_POST['note'])
			&&
			isset($_POST['object_id'])
			&&
			is_numeric($_POST['object_id'])
			&&
			$_POST['object_id'] > 0
			&&
			isset($_POST['table'])
			&&
			!empty($_POST['table'])
		)
		{
			$class = InvoiceEM_Database::get_table_class(esc_attr($_POST['table']));
			
			if (!empty($class))
			{
				$table_name = InvoiceEM_Database::get_table_name(esc_sql($_POST['table']));
				
				$results = $wpdb->get_results
				(
					$wpdb->prepare
					(
						"SELECT " . InvoiceEM_Client::ID_COLUMN . ", " . $class::TITLE_COLUMN . ", " . InvoiceEM_Constants::COLUMN_HISTORY . " FROM " . $table_name . " WHERE " . $class::ID_COLUMN . " = %d",
						esc_attr($_POST['object_id'])
					),
					
					ARRAY_A
				);

				if (!empty($results))
				{
					$row = $results[0];
					$note = sanitize_textarea_field($_POST['note']);
					$send_to_client = isset($_POST['send_to_client']);
					$has_clients_plus = $this->base->cache->has_clients_plus;
					
					$is_client =
					(
						$has_clients_plus
						&&
						$this->base->cache->is_client
					);
					
					$action =
					(
						$send_to_client
						||
						$is_client
					)
					? InvoiceEM_Constants::ACTION_CLIENT_NOTE
					: InvoiceEM_Constants::ACTION_NOTE;
					
					$history = new InvoiceEM_History($row[InvoiceEM_Constants::COLUMN_HISTORY]);
					$history->add_event($action, 0, '', $note);
					
					$updated = $wpdb->update
					(
						$table_name,

						array
						(
							InvoiceEM_Constants::COLUMN_HISTORY => $history->get_serialized()
						),

						array
						(
							$class::ID_COLUMN => esc_attr($_POST['object_id'])
						),

						'%s',
						'%d'
					);

					if (!empty($updated))
					{
						if ($has_clients_plus)
						{
							if ($send_to_client)
							{
								do_action(InvoiceEM_Constants::HOOK_SEND_NOTE, $row[InvoiceEM_Client::ID_COLUMN], $row[$class::TITLE_COLUMN], $note);
							}
							else if ($is_client)
							{
								do_action(InvoiceEM_Constants::HOOK_SEND_NOTE, false, $row[$class::TITLE_COLUMN], $note);
							}
						}
						
						$success = true;
						
						echo InvoiceEM_Output::admin_notice(sprintf
						(
							__('Note added to %1$s successfully.', 'invoiceem'),
							$row[$class::TITLE_COLUMN]
						));
					}
				}
			}
		}
		
		if (!$success)
		{
			echo InvoiceEM_Output::admin_notice(__('Note could not be added.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		
		exit;
	}
	
	/**
	 * Load the remaining history for an object.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function history()
	{
		global $wpdb;
		
		$output = '';
		
		if
		(
			isset($_POST['column'])
			&&
			!empty($_POST['column'])
			&&
			isset($_POST['object_id'])
			&&
			is_numeric($_POST['object_id'])
			&&
			$_POST['object_id'] > 0
			&&
			isset($_POST['table'])
			&&
			!empty($_POST['table'])
		)
		{
			$events = $wpdb->get_col($wpdb->prepare
			(
				"SELECT " . InvoiceEM_Constants::COLUMN_HISTORY . " FROM " . InvoiceEM_Database::get_table_name(esc_sql($_POST['table'])) . " WHERE " . esc_sql($_POST['column']) . " = %d",
				esc_attr($_POST['object_id'])
			));
			
			if (!empty($events))
			{
				$history = new InvoiceEM_History($events[0]);
				$output = $history->display_remaining();
			}
		}
		
		echo $output;
		
		exit;
	}
	
	/**
	 * Output the requested reporting data.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function reporting()
	{
		wp_send_json($this->base->reporting->output());
	}

	/**
	 * Output a JSON list of clients for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function select2_clients()
	{
		$this->_select2_output('InvoiceEM_Client');
	}

	/**
	 * Output a JSON list of countries for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function select2_countries()
	{
		$this->_select2_output('InvoiceEM_Country');
	}

	/**
	 * Output a JSON list of currencies for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function select2_currencies()
	{
		$this->_select2_output('InvoiceEM_Currency');
	}

	/**
	 * Output a JSON list of invoices for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function select2_invoices()
	{
		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES);
		$not_in = "";
		
		if
		(
			isset($_POST['not_in'])
			&&
			is_array($_POST['not_in'])
		)
		{
			foreach ($_POST['not_in'] as $invoice_id)
			{
				if (is_numeric($invoice_id))
				{
					if (!empty($not_in))
					{
						$not_in .= ", ";
					}
					
					$not_in .= esc_attr($invoice_id);
				}
			}
			
			if (!empty($not_in))
			{
				$not_in = " AND " . InvoiceEM_Invoice::ID_COLUMN . " NOT IN (" . $not_in . ")";
			}
		}
		
		$this->_select2_output('InvoiceEM_Invoice', array(InvoiceEM_Client::ID_COLUMN), "(" . $table_name . ".paid IS NULL OR " . $table_name . ".total > " . $table_name . ".paid)" . $not_in, "send_date DESC");
	}

	/**
	 * Output a JSON list of projects for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function select2_projects()
	{
		$this->_select2_output('InvoiceEM_Project', array(InvoiceEM_Client::ID_COLUMN));
	}

	/**
	 * Output a JSON list for Select2 dropdowns.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $class      Class name for the object list.
	 * @param  array  $filters    Columns to filter the list by.
	 * @param  string $where_base Base WHERE statement.
	 * @param  string $order      Order of the list.
	 * @return void
	 */
	private function _select2_output($class, $filters = array(), $where_base = "", $order = "")
	{
		$output = array
		(
			'results' => array()
		);

		if (method_exists($class, 'select2_list'))
		{
			$search = (isset($_POST['search']))
			? esc_attr($_POST['search'])
			: '';

			$page =
			(
				isset($_POST['page'])
				&&
				is_numeric($_POST['page'])
			)
			? esc_attr($_POST['page'])
			: 1;
			
			$list = $class::select2_list($search, $page, $filters, $where_base, $order);
			
			$output['pagination'] = array
			(
				'more' => $list['more']
			);

			foreach ($list['results'] as $id => $text)
			{
				$output['results'][] = array
				(
					'id' => $id,
					'text' => $text
				);
			}
		}

		wp_send_json($output);
	}
}
