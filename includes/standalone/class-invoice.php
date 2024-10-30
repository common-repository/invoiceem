<?php
/*!
 * Invoice object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Invoice
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the invoice object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Invoice extends InvoiceEM_Object
{
	/**
	 * Column name for the invoice ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'invoice_id';

	/**
	 * Column name for the invoice name.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'regarding';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Invoices::SELECT_COLUMNS;

	/**
	 * Raw invoices table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_INVOICES;
	
	/**
	 * Line items for this invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    InvoiceEM_Line_Items
	 */
	protected $_line_items;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Modified processing check and removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer/array $invoice_id_or_row Optional ID of the invoice to load or an invoice row.
	 * @param  boolean       $is_view           True if the invoice is being viewed.
	 * @return void
	 */
	public function __construct($invoice_id_or_row = 0, $is_view = false)
	{
		global $wpdb;
		
		$can_edit = current_user_can(InvoiceEM_Constants::CAP_EDIT_INVOICES);
		$label_not_authorized = __('You are not authorized to view this page.', 'invoiceem');
		
		if
		(
			!$is_view
			&&
			!$can_edit
		)
		{
			wp_die($label_not_authorized);
		}

		parent::__construct();
		
		if (is_array($invoice_id_or_row))
		{
			$this->_properties = $invoice_id_or_row;
			
			if (!empty($this->line_items))
			{
				$this->_line_items = new InvoiceEM_Line_Items($this->line_items);
			}
			
			if (!empty($this->{InvoiceEM_Constants::COLUMN_HISTORY}))
			{
				$this->_history = new InvoiceEM_History($this->{InvoiceEM_Constants::COLUMN_HISTORY});
			}
		}
		else
		{
			$this->_finalize($invoice_id_or_row);
		}
		
		if
		(
			get_option(InvoiceEM_Constants::OPTION_PROCESSING) !== 1
			&&
			$is_view
			&&
			!$can_edit
		)
		{
			if
			(
				is_numeric($this->send_date)
				&&
				$this->send_date > 0
				&&
				$this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE}
			)
			{
				$this->_add_history(InvoiceEM_Constants::ACTION_VIEW);

				$wpdb->update
				(
					InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES),

					array
					(
						'last_viewed' => time(),
						InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
					),

					array
					(
						InvoiceEM_Invoice::ID_COLUMN => $this->{InvoiceEM_Invoice::ID_COLUMN}
					),

					array('%d', '%s'),
					'%d'
				);
			}
			else
			{
				wp_die($label_not_authorized);
			}
		}
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
			 * ID for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;
				
			/**
			 * Type of invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_type':
			
				return 'i';
				
			/**
			 * Name for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case self::TITLE_COLUMN:
			
				return '';

			/**
			 * ID of the client record for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Client::ID_COLUMN:
			
				return 0;

			/**
			 * ID of the project record for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Project::ID_COLUMN:
			
			/**
			 * Final invoice number for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'po_number':
			
			/**
			 * Deposit amount for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'deposit':
			
			/**
			 * Deposit due date for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'deposit_due':
			
			/**
			 * Pre-tax discount. It will contain the '%' sign if percentage should be calculated.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'pre_tax_discount':
			
			/**
			 * Post-tax discount. It will contain the '%' sign if percentage should be calculated.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'discount':
			
			/**
			 * Primary title for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_title':
			
			/**
			 * Final invoice number for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_number':
			
			/**
			 * Date and time that the invoice was sent on.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'send_date':
			
			/**
			 * Recurrence details for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'recurrence':
			
				return null;
			
			/**
			 * Total due for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'total':
			
				return 0;
			
			/**
			 * Payment due date for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'payment_due':
			
				return $this->base->settings->invoicing->payment_due;
			
			/**
			 * Amount paid for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'paid':
			
			/**
			 * Date the invoice was last viewed.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'last_viewed':
			
				return null;

			/**
			 * Active state of the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * Note displayed directly below the line items on this invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'footer_note':
			
			/**
			 * Note displayed at the very bottom of this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'footer_thank_you':
			
			/**
			 * Tax settings for this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'taxes':
			
			/**
			 * Line items for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'line_items':
			
			/**
			 * History of events for the invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_HISTORY:
			
			/**
			 * Name of the client associated with this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Client::TITLE_COLUMN:
			
			/**
			 * Invoice prefix for the selected client.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'client_invoice_prefix':
			
			/**
			 * Standard rate for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'client_rate':
			
			/**
			 * Taxes for the client.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'client_taxes':
			
			/**
			 * Name of the project associated with this invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Project::TITLE_COLUMN:
			
				return null;
		}

		return parent::_default($name);
	}
	
	/**
	 * Finalize the invoice.
	 *
	 * @since 1.0.5 Modified for advanced invoice number generation.
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $invoice_id ID of the invoice to load.
	 * @return void
	 */
	private function _finalize($invoice_id)
	{
		self::enable_filters();

		if
		(
			is_numeric($invoice_id)
			&&
			$invoice_id > 0
		)
		{
			$this->_load($invoice_id);
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
				'invoice_type' => $this->invoice_type,

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

				InvoiceEM_Project::ID_COLUMN =>
				(
					!isset($_POST[InvoiceEM_Project::ID_COLUMN])
					||
					!is_numeric($_POST[InvoiceEM_Project::ID_COLUMN])
				)
				? $this->{InvoiceEM_Project::ID_COLUMN}
				: esc_attr($_POST[InvoiceEM_Project::ID_COLUMN]),
				
				'po_number' =>
				(
					!isset($_POST['po_number'])
					||
					empty($_POST['po_number'])
				)
				? $this->po_number
				: substr(sanitize_text_field($_POST['po_number']), 0, 32),
				
				'pre_tax_discount' => $this->pre_tax_discount,
				
				'discount' =>
				(
					!isset($_POST['discount'])
					||
					empty($_POST['discount'])
				)
				? $this->discount
				: substr(sanitize_text_field($_POST['discount']), 0, 32),
				
				'invoice_title' =>
				(
					!isset($_POST['invoice_title'])
					||
					empty($_POST['invoice_title'])
				)
				? $this->invoice_title
				: substr(sanitize_text_field($_POST['invoice_title']), 0, 255),
				
				'invoice_number' =>
				(
					!isset($_POST['invoice_number'])
					||
					empty($_POST['invoice_number'])
				)
				? $this->invoice_number
				: substr(sanitize_text_field($_POST['invoice_number']), 0, 255),
				
				'send_date' => $this->send_date,
				'recurrence' => $this->recurrence,
				
				'footer_note' =>
				(
					!isset($_POST['footer_note'])
					||
					empty($_POST['footer_note'])
				)
				? $this->footer_note
				: sanitize_textarea_field($_POST['footer_note']),
				
				'footer_thank_you' =>
				(
					!isset($_POST['footer_thank_you'])
					||
					empty($_POST['footer_thank_you'])
				)
				? $this->footer_thank_you
				: sanitize_textarea_field($_POST['footer_thank_you']),
				
				'taxes' => $this->taxes
			);
			
			$formats = array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');
			$is_valid = true;
			
			if
			(
				isset($_POST['deposit'])
				&&
				!empty($_POST['deposit'])
				&&
				isset($_POST['deposit_due'])
				&&
				is_numeric($_POST['deposit_due'])
			)
			{
				$row['deposit'] = substr(sanitize_text_field($_POST['deposit']), 0, 32);
				$formats[] = '%s';
				
				$row['deposit_due'] = esc_attr($_POST['deposit_due']);
				$formats[] = '%d';
			}
			
			if
			(
				isset($_POST['pre_tax_discount'])
				&&
				!empty($_POST['pre_tax_discount'])
			)
			{
				$row['pre_tax_discount'] = substr(sanitize_text_field($_POST['pre_tax_discount']), 0, 32);
			}
			
			if (isset($_POST['payment_due']))
			{
				$row['payment_due'] =
				(
					!is_numeric($_POST['payment_due'])
					||
					$_POST['payment_due'] == $this->base->settings->invoicing->payment_due
				)
				? null
				: esc_attr($_POST['payment_due']);
				
				$formats[] = '%d';
			}
			
			if
			(
				(
					!empty($this->send_date)
					&&
					$this->send_date > 0
				)
				||
				(
					isset($_POST['override_taxes'])
					&&
					!empty($_POST['override_taxes'])
				)
			)
			{
				$row['taxes'] = (isset($_POST['taxes']))
				? maybe_serialize($this->base->settings->invoicing->sanitize_taxes($_POST['taxes']))
				: maybe_serialize(true);
			}
			else
			{
				$row['taxes'] = null;
			}
			
			if (empty($row[self::TITLE_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please specify what the invoice is regarding.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row[InvoiceEM_Client::ID_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please select a client.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}
			
			if ($is_valid)
			{
				$this->_line_items = new InvoiceEM_Line_Items();
				
				$row['total'] = $this->_line_items->calculate_totals($row)['total'];
				$formats[] = '%f';

				$row['line_items'] = $this->_line_items->get_serialized();
				$formats[] = '%s';
				
				$this->_load_post($this->base->cache->action, $row, $formats);
				
				if
				(
					isset($_POST[InvoiceEM_Constants::ACTION_SEND])
					&&
					!empty($_POST[InvoiceEM_Constants::ACTION_SEND])
				)
				{
					$this->send($this->{self::ID_COLUMN});
					$this->_load($this->{self::ID_COLUMN});
				}
				else if
				(
					isset($_POST[InvoiceEM_Constants::ACTION_RESEND])
					&&
					!empty($_POST[InvoiceEM_Constants::ACTION_RESEND])
				)
				{
					$this->resend($this->{self::ID_COLUMN});
					$this->_load($this->{self::ID_COLUMN});
				}
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
			$is_delete = ($this->base->cache->action == InvoiceEM_Constants::ACTION_DELETE);
			$processed_id = $this->_load_get($this->base->cache->action);
			
			if
			(
				$is_delete
				&&
				$processed_id !== false
			)
			{
				InvoiceEM_Payment_Invoices::delete_invoice_records($processed_id);
			}
			else if (empty($this->_line_items))
			{
				$this->_line_items = new InvoiceEM_Line_Items($this->line_items);
			}
		}
		
		self::disable_filters();
	}

	/**
	 * Prepare the SQL JOIN statement for the invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $join        Raw SQL JOIN statement.
	 * @param  boolean $active_only True if only active records should be pulled.
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

		$invoices_table = InvoiceEM_Database::get_table_name(self::$_raw_table_name);
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);
		$projects_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);
		
		$join .= $position . " JOIN " . $clients_table . " ON " . $invoices_table . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN;
		
		if ($active_only)
		{
			$join .= " AND " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1";
		}
		
		$join .= " LEFT JOIN " . $projects_table . " ON " . $invoices_table . "." . InvoiceEM_Project::ID_COLUMN . " = " . $projects_table . "." . InvoiceEM_Project::ID_COLUMN;
		
		if ($active_only)
		{
			$join .= " AND " . $projects_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1";
		}
		
		return $join;
	}

	/**
	 * Prepare the SQL SELECT statement for the invoice.
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
		$projects_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);
		
		return $select . ", " . $clients_table . "." . InvoiceEM_Client::TITLE_COLUMN . " AS " . InvoiceEM_Client::TITLE_COLUMN . ", " . $clients_table . ".invoice_prefix AS client_invoice_prefix, " . $clients_table . ".rate AS client_rate, " . $clients_table . ".taxes AS client_taxes, " . $projects_table . "." . InvoiceEM_Project::TITLE_COLUMN . " AS " . InvoiceEM_Project::TITLE_COLUMN;
	}

	/**
	 * Load the invoice from the database.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $invoice_id ID value for the invoice being loaded.
	 * @return void
	 */
	protected function _load($invoice_id)
	{
		parent::_load($invoice_id);
		
		$this->_line_items = new InvoiceEM_Line_Items($this->line_items);
	}

	/**
	 * Load the invoice from GET data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $action Action being taken on the invoice.
	 * @return mixed          Invoice ID or true on successful action, otherwise false.
	 */
	protected function _load_get($action)
	{
		$invoice_id = parent::_load_get($action);
		
		if ($invoice_id !== false)
		{
			if ($action == InvoiceEM_Constants::ACTION_SEND)
			{
				return $this->_get_send($invoice_id);
			}
			else if ($action == InvoiceEM_Constants::ACTION_RESEND)
			{
				return $this->_get_resend($invoice_id);
			}
		}
		
		return $invoice_id;
	}

	/**
	 * Setup the invoice to be added.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string  Current action being taken.
	 * @return boolean True if the user can add a invoice.
	 */
	protected function _get_add($action)
	{
		if ($action == InvoiceEM_Constants::ACTION_COPY)
		{
			$this->setup_copy();
		}
		
		return parent::_get_add($action);
	}

	/**
	 * Send an invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $invoice_id ID of the invoice being sent.
	 * @return mixed               Invoice ID if send is successful, otherwise false.
	 */
	private function _get_send($invoice_id)
	{
		$output = false;
		
		if ($invoice_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an invoice to send.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Invoice does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_SEND, $invoice_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to send %1$s.', 'invoiceem'),
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
					__('%1$s is not active and cannot be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else if
		(
			empty($this->send_date)
			||
			$this->send_date < 0
		)
		{
			$output = $this->send($invoice_id);
		}
		else
		{
			$output = $this->resend($invoice_id);
		}
		
		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}
	
	/**
	 * Send functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $invoice_id Optional ID of the invoice being sent.
	 * @return mixed               Invoice ID if send is successful, otherwise false.
	 */
	public function send($invoice_id = 0)
	{
		$output = false;
		$suppress_notices = false;
		
		if
		(
			empty($invoice_id)
			&&
			!empty($this->{self::ID_COLUMN})
		)
		{
			$invoice_id = $this->{self::ID_COLUMN};
			$suppress_notices = true;
		}
		
		if ($this->mark_sent($invoice_id) === false)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s could not be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else
		{
			if (!$this->base->cache->is_post)
			{
				$this->_load($this->{self::ID_COLUMN});
			}
			
			if (InvoiceEM_Email::send($this, $this->base->settings->email->send_invoice_subject, $this->base->settings->email->send_invoice_title, $this->base->settings->email->send_invoice_body))
			{
				if (!$suppress_notices)
				{
					InvoiceEM_Output::add_admin_notice(sprintf
					(
						__('%1$s sent successfully.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					));
				}
			}
			else if (!$suppress_notices)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s was finalized, but the email could not be sent. If the problem persists, please enable the \'Exclude From\' option in the email settings.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_WARNING
				);
			}
			
			$output = $invoice_id;
		}
		
		return $output;
	}
	
	/**
	 * Mark an invoice as sent.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $invoice_id Optional ID of the invoice being marked as sent.
	 * @return mixed               Number of invoice rows updated or false if unsuccessful.
	 */
	public function mark_sent($invoice_id = 0)
	{
		global $wpdb;

		if
		(
			empty($invoice_id)
			&&
			!empty($this->{self::ID_COLUMN})
		)
		{
			$invoice_id = $this->{self::ID_COLUMN};
		}
		
		$this->_add_history(InvoiceEM_Constants::ACTION_SEND);
		
		$columns = array
		(
			'taxes' => (is_array($this->taxes))
			? maybe_serialize($this->taxes)
			: $this->taxes,
			
			InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
		);

		$formats = array('%s', '%s');
		
		if (empty($columns['taxes']))
		{
			$accounting_settings = InvoiceEM_Client::accounting_settings($this->{InvoiceEM_Client::ID_COLUMN});
			$this->taxes = $columns['taxes'] = $accounting_settings['taxes'];
			$columns['taxes'] = maybe_serialize($columns['taxes']);
		}
		
		if
		(
			is_numeric($this->deposit_due)
			&&
			$this->deposit_due > 0
		)
		{
			$this->deposit_due = $columns['deposit_due'] = strtotime('+' . $this->deposit_due . ' days');
			$formats[] = '%d';
		}

		$original_send_date = $this->send_date;
		$this->send_date = $columns['send_date'] = time();
		$formats[] = '%d';
		
		if (empty($this->invoice_title))
		{
			$this->invoice_title = $columns['invoice_title'] = $this->base->settings->invoicing->invoice_title;
			$formats[] = '%s';
		}

		if (empty($this->invoice_number))
		{
			$this->invoice_number = $columns['invoice_number'] = $this->generate_invoice_number();
			$formats[] = '%s';
		}

		if ($this->payment_due > 0)
		{
			$this->payment_due = $columns['payment_due'] = strtotime('+' . $this->payment_due . ' days');
			$formats[] = '%d';
		}
		
		if (empty($this->footer_note))
		{
			$this->footer_note = $columns['footer_note'] = $this->base->settings->invoicing->footer_note;
			$formats[] = '%s';
		}
		
		if (empty($this->footer_thank_you))
		{
			$this->footer_thank_you = $columns['footer_thank_you'] = $this->base->settings->invoicing->footer_thank_you;
			$formats[] = '%s';
		}

		$output = $wpdb->update
		(
			InvoiceEM_Database::get_table_name(static::$_raw_table_name),
			$columns,

			array
			(
				static::ID_COLUMN => $invoice_id
			),

			$formats,
			'%d'
		);
		
		if
		(
			$this->base->cache->has_invoices_plus
			&&
			is_numeric($output)
			&&
			$output > 0
		)
		{
			do_action(InvoiceEM_Constants::HOOK_INVOICE_MARK_SENT, new self($this->_properties, true), $original_send_date);
		}
		
		return $output;
	}

	/**
	 * Resend an invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $invoice_id ID of the invoice being resent.
	 * @return mixed               Invoice ID if resend is successful, otherwise false.
	 */
	private function _get_resend($invoice_id)
	{
		$output = false;
		
		if ($invoice_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose an invoice to resend.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Invoice does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_RESEND, $invoice_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to resend %1$s.', 'invoiceem'),
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
					__('%1$s is not active and cannot be resent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else
		{
			$output = $this->resend($invoice_id);
		}
		
		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}
	
	/**
	 * Resend functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $invoice_id Optional ID of the invoice being resent.
	 * @return mixed              Invoice ID if redsend is successful, otherwise false.
	 */
	public function resend($invoice_id = 0)
	{
		global $wpdb;
		
		$output = false;
		$suppress_notices = false;

		if
		(
			empty($invoice_id)
			&&
			!empty($this->{self::ID_COLUMN})
		)
		{
			$invoice_id = $this->{self::ID_COLUMN};
			$suppress_notices = true;
		}
		
		$this->_add_history(InvoiceEM_Constants::ACTION_RESEND);

		$resent = $wpdb->update
		(
			InvoiceEM_Database::get_table_name(static::$_raw_table_name),

			array
			(
				InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
			),

			array
			(
				static::ID_COLUMN => $invoice_id
			),

			'%s',
			'%d'
		);

		if ($resent === false)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s could not be resent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else
		{
			if (!$this->base->cache->is_post)
			{
				$this->_load($this->{self::ID_COLUMN});
			}
			
			$subject = $this->base->settings->email->send_invoice_subject;
			$title = $this->base->settings->email->send_invoice_title;
			$body = $this->base->settings->email->send_invoice_body;
			
			if
			(
				$this->payment_due > 0
				&&
				$this->payment_due < time()
			)
			{
				$subject = $this->base->settings->email->overdue_invoice_subject;
				$title = $this->base->settings->email->overdue_invoice_title;
				$body = $this->base->settings->email->overdue_invoice_body;
			}
			
			if (InvoiceEM_Email::send($this, $subject, $title, $body))
			{
				if (!$suppress_notices)
				{
					InvoiceEM_Output::add_admin_notice(sprintf
					(
						__('%1$s resent successfully.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					));
				}
			}
			else if (!$suppress_notices)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s email could not be sent. If the problem persists, please enable the \'Exclude From\' option in the email settings.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_WARNING
				);
			}
			
			$output = $invoice_id;
		}
		
		return $output;
	}
	
	/**
	 * Setup the invoice to be copied.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  integer $send_date Send date to apply to the copied invoice.
	 * @return void
	 */
	public function setup_copy($send_date = null)
	{
		$y2k = 946684799;
		
		if ($this->deposit_due > $y2k)
		{
			$this->deposit_due = round(($this->deposit_due - $this->send_date) / 86400);
		}
		
		if ($this->payment_due > $y2k)
		{
			$this->payment_due = round(($this->payment_due - $this->send_date) / 86400);
		}
		
		$this->send_date =
		(
			is_numeric($send_date)
			&&
			$send_date < 0
		)
		? $send_date
		: null;
		
		$this->{self::ID_COLUMN} = 0;
		$this->invoice_number = null;
		$this->paid = null;
		$this->last_viewed = null;
		$this->{InvoiceEM_Constants::COLUMN_LOCKED} = null;
		$this->{InvoiceEM_Constants::COLUMN_HISTORY} = null;
		$this->_history = null;
		
		if (!is_null($this->send_date))
		{
			$this->_post_add
			(
				array
				(
					'invoice_type' => $this->invoice_type,
					self::TITLE_COLUMN => $this->{self::TITLE_COLUMN},
					InvoiceEM_Client::ID_COLUMN => $this->{InvoiceEM_Client::ID_COLUMN},
					InvoiceEM_Project::ID_COLUMN => $this->{InvoiceEM_Project::ID_COLUMN},
					'po_number' => $this->po_number,
					'deposit' => $this->deposit,
					'deposit_due' => $this->deposit_due,
					'pre_tax_discount' => $this->pre_tax_discount,
					'discount' => $this->discount,
					'invoice_title' => $this->invoice_title,
					'send_date' => $this->send_date,
					'recurrence' => $this->recurrence,
					'total' => $this->total,
					'payment_due' => $this->payment_due,
					InvoiceEM_Constants::COLUMN_IS_ACTIVE => $this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE},
					'footer_note' => $this->footer_note,
					'footer_thank_you' => $this->footer_thank_you,
					
					'taxes' => (is_array($this->taxes))
					? maybe_serialize($this->taxes)
					: $this->taxes,
					
					'line_items' => (is_array($this->line_items))
					? maybe_serialize($this->line_items)
					: $this->line_items
				),
				
				array('%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%f', '%d', '%d', '%s', '%s', '%s', '%s'),
				wp_create_nonce(InvoiceEM_Utilities::nonce_action(InvoiceEM_Constants::ACTION_ADD))
			);
		}
	}

	/**
	 * Prepare the invoice output.
	 *
	 * @since 1.0.5 Modified for advanced invoice number generation.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare()
	{
		parent::prepare();
		
		if ($this->base->cache->has_invoices_plus)
		{
			do_action(InvoiceEM_Constants::HOOK_INVOICE_META_BOXES, $this);
		}
		
		$general_information_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'general_information',
			'title' => __('General Information', 'invoiceem')
		));

		$general_information_box->add_field(array
		(
			'description' => __('Short description of what this invoice is regarding.', 'invoiceem'),
			'label' => __('Regarding', 'invoiceem'),
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

		$general_information_box->add_field(array
		(
			'description' => __('Client that this invoice is for.', 'invoiceem'),
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
		
		$selected_project = array();
		$project_value = $this->{InvoiceEM_Project::ID_COLUMN};
		
		if (!empty($project_value))
		{
			$project_title = $this->{InvoiceEM_Project::TITLE_COLUMN};
			
			$selected_project = (empty($project_title))
			? InvoiceEM_Project::selected_item($project_value)
			: array
			(
				$project_value => $project_title
			);
		}
		else if
		(
			isset($_GET[InvoiceEM_Project::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Project::ID_COLUMN])
		)
		{
			$project_value = esc_attr($_GET[InvoiceEM_Project::ID_COLUMN]);
			$selected_project = InvoiceEM_Project::selected_item($project_value);
		}
		else if
		(
			isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN])
		)
		{
			$project_value = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN]);
			$selected_project = InvoiceEM_Project::selected_item($project_value);
		}
		
		if (!empty($project_value))
		{
			$this->base->cache->accounting = InvoiceEM_Project::accounting_settings($project_value);
		}
		else if (!empty($client_value))
		{
			$this->base->cache->accounting = InvoiceEM_Client::accounting_settings($client_value);
		}

		$general_information_box->add_field(array
		(
			'description' => __('Project that this invoice is for.', 'invoiceem'),
			'input_classes' => array('iem-accounting'),
			'label' => __('Project', 'invoiceem'),
			'name' => InvoiceEM_Project::ID_COLUMN,
			'options' => $selected_project,
			'table' => InvoiceEM_Constants::TABLE_PROJECTS,
			'type' => 'select',
			'value' => $project_value,

			'attributes' => array
			(
				'placeholder' => __('Select a Project', 'invoiceem')
			)
		));
		
		$general_information_box->add_field(array
		(
			'description' => __('Purchase order number for this invoice.', 'invoiceem'),
			'label' => __('PO Number', 'invoiceem'),
			'name' => 'po_number',
			'type' => 'text',
			'value' => $this->po_number,

			'attributes' => array
			(
				'maxlength' => 32
			)
		));
		
		$is_sent =
		(
			!empty($this->send_date)
			&&
			$this->send_date > 0
		);
		
		if (!$is_sent)
		{
			$general_information_box->add_field(array
			(
				'description' => __('Deposit required for this invoice.', 'invoiceem'),
				'input_classes' => array('iem-spinner'),
				'label' => __('Deposit', 'invoiceem'),
				'name' => 'deposit',
				'type' => 'discount',
				'value' => $this->deposit
			));
			
			$general_information_box->add_field(array
			(
				'description' => __('When the deposit for this invoice is due.', 'invoiceem'),
				'label' => __('Deposit Due', 'invoiceem'),
				'name' => 'deposit_due',
				'options' => $this->base->settings->invoicing->get_payment_due_options(),
				'type' => 'select',
				
				'classes' => (empty($this->deposit))
				? array('iem-hidden')
				: array(),
				
				'conditional' => array
				(
					array
					(
						'compare' => '!=',
						'field' => 'deposit',
						'value' => ''
					),
					
					array
					(
						'compare' => '!=',
						'field' => 'deposit',
						'value' => '0'
					)
				),
				
				'value' => (is_numeric($this->deposit_due))
				? $this->deposit_due
				: 14
			));
		}
		
		$line_items_box = new InvoiceEM_Meta_Box(array
		(
			'classes' => array('iem-line-items'),
			'context' => 'normal',
			'id' => 'line_items',
			'title' => __('Line Items', 'invoiceem')
		));
		
		$line_item_taxes = $this->get_line_item_taxes();
		$taxes_fields = $this->get_line_item_taxes_fields($line_item_taxes);
		
		$taxes_overridden =
		(
			$is_sent
			||
			!empty(maybe_unserialize($this->taxes))
		);
		
		$line_items_box->add_field(array
		(
			'add_item' => __('Add Line Item', 'invoiceem'),
			'name' => 'line_items',
			'repeatable_field' => self::line_item_field(false, $taxes_fields),
			'sort_items' => __('Sort Line Items by Date', 'invoiceem'),
			'type' => 'repeatable',
			'value' => $this->_line_items->get_value()
		));
		
		$discount_label = __('Discount:', 'invoiceem');
		
		$line_items_box->add_field(array
		(
			'hide_labels' => true,
			'type' => 'group',
			
			'fields' => array
			(
				array
				(
					'classes' => array('iem-hidden'),
					'name' => 'paid',
					'type' => 'hidden',
					
					'value' => (is_numeric($this->paid))
					? $this->paid
					: 0
				),

				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'group',
					
					'fields' => array
					(
						array
						(
							'type' => 'html',
							
							'content' => '<div style="margin-bottom: -4px;">'
							. '<strong>' . __('APPLY DISCOUNTS', 'invoiceem') . '</strong>'
							. '</div>'
						),
						
						array
						(
							'classes' => array('iem-hidden', 'iem-pre-tax-discount'),
							'description' => __('This discount is applied before tax is calculated.', 'invoiceem'),
							'input_classes' => array('iem-calculate', 'iem-spinner', 'iem-tooltip'),
							'name' => 'pre_tax_discount',
							'type' => 'discount',
							'value' => $this->pre_tax_discount,

							'attributes' => array
							(
								'data-iem-number-format' => 'n',
								'data-iem-step' => 1,
								'placeholder' => __('Pre-tax Discount', 'invoiceem')
							)
						),
						
						array
						(
							'classes' => array('iem-discount'),
							'description' => __('This discount is applied after tax is calculated.', 'invoiceem'),
							'input_classes' => array('iem-calculate', 'iem-spinner', 'iem-tooltip'),
							'name' => 'discount',
							'type' => 'discount',
							'value' => $this->discount,

							'attributes' => array
							(
								'data-iem-number-format' => 'n',
								'data-iem-step' => 1,
								'placeholder' => __('Post-tax Discount', 'invoiceem')
							)
						)
					)
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-totals">'
					. '<table cellspacing="0">'
					. '<tbody>'
					. '<tr class="iem-subtotal-row iem-hidden">'
					. '<td>' . __('Subtotal:', 'invoiceem') . '</td>'
					. '<td class="iem-subtotal-output"></td>'
					. '</tr>'
					. '<tr class="iem-pre-tax-discount-row iem-hidden">'
					. '<td>' . $discount_label . '</td>'
					. '<td class="iem-pre-tax-discount-output"></td>'
					. '</tr>'
					. '<tr class="iem-tax-row iem-hidden">'
					. '<td>'
					. sprintf
					(
						_x('%1$s:', 'Tax Label Placeholder', 'invoiceem'),
						'<span class="iem-tax-label"></span>'
					)
					. '</td>'
					. '<td class="iem-tax-output"></td>'
					. '</tr>'
					. '<tr class="iem-discount-row iem-hidden">'
					. '<td>' . $discount_label . '</td>'
					. '<td class="iem-discount-output"></td>'
					. '</tr>'
					. '<tr class="iem-paid-row iem-hidden">'
					. '<td>' . __('Paid:', 'invoiceem') . '</td>'
					. '<td class="iem-paid-output">--</td>'
					. '</tr>'
					. '<tr>'
					. '<td>' . __('Total:', 'invoiceem') . '</td>'
					. '<td class="iem-total-output">--</td>'
					. '</tr>'
					. '</tbody>'
					. '</table>'
					. '</div>',
				)
			)
		));
		
		$footer_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'footer_settings',
			'title' => __('Footer Settings', 'invoiceem')
		));
		
		$footer_settings_box->add_field(array
		(
			'description' => __('Note displayed directly below the line items on this invoice.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Footer Note', 'invoiceem'),
			'name' => 'footer_note',
			'type' => 'textarea',
			'value' => $this->footer_note,

			'attributes' => array
			(
				'placeholder' => $this->base->settings->invoicing->footer_note,
				'rows' => 3
			)
		));
		
		$footer_settings_box->add_field(array
		(
			'description' => __('Note displayed at the very bottom of this invoice.', 'invoiceem'),
			'is_tall' => true,
			'label' => __('Footer Thank You', 'invoiceem'),
			'name' => 'footer_thank_you',
			'type' => 'textarea',
			'value' => $this->footer_thank_you,

			'attributes' => array
			(
				'placeholder' => $this->base->settings->invoicing->footer_thank_you,
				'rows' => 3
			)
		));
		
		$this->_history_box();
		
		$publish_fields = array();
		
		if ($is_sent)
		{
			$date_format = get_option('date_format');
			
			if
			(
				$this->deposit_due > 0
				&&
				InvoiceEM_Utilities::calculate_value($this->deposit, $this->total) > $this->paid
			)
			{
				$publish_fields[] = array
				(
					'content' => InvoiceEM_Utilities::format_date($date_format, $this->deposit_due),
					'label' => __('Deposit Due', 'invoiceem'),
					'type' => 'html'
				);
			}
			
			if ($this->payment_due > 0)
			{
				$publish_fields[] = array
				(
					'content' => InvoiceEM_Utilities::format_date($date_format, $this->payment_due),
					'label' => __('Payment Due', 'invoiceem'),
					'type' => 'html'
				);
			}
		}
		
		if ($this->total != $this->paid)
		{
			$conditional = array();
			$send_description = '';
			$send_label = '';
			$send_name = InvoiceEM_Constants::ACTION_SEND;
			
			if ($is_sent)
			{
				$send_description = __('Resend the invoice after it is saved.', 'invoiceem');
				$send_label = __('Resend Invoice', 'invoiceem');
				$send_name = InvoiceEM_Constants::ACTION_RESEND;
			}
			else
			{
				$conditional[] = array
				(
					'compare' => '!=',
					'field' => 'invoice_type',
					'value' => 'r'
				);
				
				$conditional[] = array
				(
					'compare' => '=',
					'field' => 'schedule_date',
					'value' => ''
				);
				
				$send_description = __('Send the invoice after it is saved.', 'invoiceem');
				$send_label = __('Send Invoice', 'invoiceem');
			}
			
			$publish_fields[] = array
			(
				'conditional' => $conditional,
				'description' => $send_description,
				'label' => $send_label,
				'name' => $send_name,
				'type' => 'checkbox'
			);
		}
		
		$this->_publish_box(__('Invoice is currently active.', 'invoiceem'), false, $publish_fields);
		
		$override_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'side',
			'id' => 'override_settings',
			'title' => __('Override Settings', 'invoiceem')
		));
		
		$override_settings_box->add_field(array
		(
			'description' => __('Primary invoice title.', 'invoiceem'),
			'label' => __('Invoice Title', 'invoiceem'),
			'name' => 'invoice_title',
			'type' => 'text',
			'value' => $this->invoice_title,

			'attributes' => array
			(
				'maxlength' => 255,
				'placeholder' => $this->base->settings->invoicing->invoice_title
			)
		));
		
		$override_settings_box->add_field(array
		(
			'description' => __('Full number for this invoice.', 'invoiceem'),
			'input_classes' => array('iem-invoice-number'),
			'label' => __('Invoice Number', 'invoiceem'),
			'name' => 'invoice_number',
			'type' => 'text',
			'value' => $this->invoice_number,

			'attributes' => array
			(
				'data-iem-placeholder' => $this->generate_invoice_number(true, true),
				'maxlength' => 32,
				'placeholder' => $this->generate_invoice_number(true)
			)
		));
		
		$taxes_conditional = array();
		
		if ($is_sent)
		{
			$override_settings_box->add_field(array
			(
				'content' => '<strong>' . __('Taxes', 'invoiceem') . '</strong>',
				'type' => 'html'
			));
		}
		else
		{
			$override_settings_box->add_field(array
			(
				'description' => __('Due date for payment on this invoice.', 'invoiceem'),
				'label' => __('Payment Due', 'invoiceem'),
				'name' => 'payment_due',
				'options' => $this->base->settings->invoicing->get_payment_due_options(true),
				'type' => 'select',
				'value' => $this->payment_due
			));
			
			$override_settings_box->add_field(array
			(
				'description' => __('If checked, taxes for this invoice will be separate from the client tax settings.', 'invoiceem'),
				'label' => __('Taxes', 'invoiceem'),
				'name' => 'override_taxes',
				'type' => 'checkbox',
				'value' => $taxes_overridden
			));
			
			$taxes_conditional = array
			(
				array
				(
					'field' => 'override_taxes',
					'value' => '1'
				)
			);
		}
		
		$taxes_override_classes = array('iem-taxes-repeatable');
		
		if (!$taxes_overridden)
		{
			$taxes_override_classes[] = 'iem-hidden';
		}
		
		$override_settings_box->add_field(array_merge
		(
			$this->base->settings->invoicing->taxes_field($line_item_taxes),
			
			array
			(
				'classes' => $taxes_override_classes,
				'conditional' => $taxes_conditional
			)
		));

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}
	
	/**
	 * Add a line item to the current invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array Calculated lines and totals for the invoice.
	 */
	public function add_line_item($line_item)
	{
		global $wpdb;
		
		$this->_line_items->generate_entry($line_item, true);
		$this->_add_history(InvoiceEM_Constants::ACTION_LINE_ITEM);
		
		return $wpdb->update
		(
			InvoiceEM_Database::get_table_name(self::$_raw_table_name),
			
			array
			(
				'line_items' => $this->_line_items->get_serialized(),
				InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
			),
			
			array
			(
				self::ID_COLUMN => $line_item[self::ID_COLUMN]
			),
			
			'%s',
			'%d'
		);
	}
	
	/**
	 * Get the calculated totals for the current invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array Calculated lines and totals for the invoice.
	 */
	public function calculate_totals()
	{
		return $this->_line_items->calculate_totals(array
		(
			InvoiceEM_Client::ID_COLUMN => $this->{InvoiceEM_Client::ID_COLUMN},
			'pre_tax_discount' => $this->pre_tax_discount,
			'discount' => $this->discount,
			'taxes' => maybe_unserialize($this->taxes)
		));
	}
	
	/**
	 * Disable the invoice load filters.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function disable_filters()
	{
		remove_filter(InvoiceEM_Constants::HOOK_OBJECT_JOIN, array(__CLASS__, 'object_join'));
		remove_filter(InvoiceEM_Constants::HOOK_OBJECT_SELECT, array(__CLASS__, 'object_select'));
	}
	
	/**
	 * Enable the invoice load filters.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function enable_filters()
	{
		add_filter(InvoiceEM_Constants::HOOK_OBJECT_JOIN, array(__CLASS__, 'object_join'));
		add_filter(InvoiceEM_Constants::HOOK_OBJECT_SELECT, array(__CLASS__, 'object_select'));
	}
	
	/**
	 * Generate a number for this invoice.
	 *
	 * @since 1.0.5 Modified to support advanced invoice number generation.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $is_simple      True if the dynamic elements should be simplified.
	 * @param  boolean $is_placeholder True is the suffix should be included in the invoice number.
	 * @return string                  Generated invoice number.
	 */
	public function generate_invoice_number($is_simple = false, $is_placeholder = false)
	{
		return InvoiceEM_Utilities::generate_invoice_number($this->_properties, $is_simple, $is_placeholder);
	}
	
	/**
	 * Get the line item taxes for this invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Line item taxes for this invoice.
	 */
	public function get_line_item_taxes()
	{
		$line_item_taxes = maybe_unserialize($this->taxes);
		
		if (empty($line_item_taxes))
		{
			if
			(
				$this->base->cache->action != InvoiceEM_Constants::ACTION_ADD
				||
				!isset($_GET[InvoiceEM_Client::ID_COLUMN])
				||
				!is_numeric($_GET[InvoiceEM_Client::ID_COLUMN])
				||
				$_GET[InvoiceEM_Client::ID_COLUMN] <= 0
			)
			{
				$line_item_taxes = (empty($this->client_taxes))
				? $this->base->settings->invoicing->taxes
				: maybe_unserialize($this->client_taxes);
			}
			else
			{
				$line_item_taxes = InvoiceEM_Client::accounting_settings(esc_attr($_GET[InvoiceEM_Client::ID_COLUMN]))['taxes'];
			}
		}
		
		return $line_item_taxes;
	}
	
	/**
	 * Get the line item taxes fields for this invoice.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $line_item_taxes Raw line item taxes for this invoice.
	 * @return array                  Line item taxes fields for this invoice.
	 */
	public function get_line_item_taxes_fields($line_item_taxes = array())
	{
		if (empty($line_item_taxes))
		{
			$line_item_taxes = $this->get_line_item_taxes();
		}
		
		$tax_fields = array();
		
		if (is_array($line_item_taxes))
		{
			foreach ($line_item_taxes as $i => $tax)
			{
				$tax_fields[] = array
				(
					'checkbox_value' => $i,
					'input_classes' => array('iem-calculate'),
					'name' => 'taxes[]',
					'type' => 'checkbox',
					'value' => $i,

					'attributes' => array
					(
						'placeholder' => $tax['l']
					),

					'field_attributes' => array
					(
						'data-iem-starting-index' => $i
					)
				);
			}
		}
		
		return $tax_fields;
	}
	
	/**
	 * Generate a line item group field.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  boolean $is_list      True ifthe field is being added to a list.
	 * @param  array   $taxes_fields Array of fields added to the taxes group field.
	 * @return array                 Generated line item group field.
	 */
	public static function line_item_field($is_list = true, $taxes_fields = array())
	{
		$iem = InvoiceEM();
		$fields = array();
		$rate_classes = array('iem-col-xs-6');
		
		if ($is_list)
		{
			$fields[] = array
			(
				'classes' => array('iem-hidden'),
				'name' => 'action',
				'type' => 'hidden',
				'value' => 'iem_add_line_item'
			);
			
			$fields[] = array
			(
				'classes' => array('iem-hidden'),
				'name' => 'invoice_id',
				'type' => 'hidden'
			);
			
			$rate_classes[] = 'iem-loading';
		}
		else
		{
			$fields[] = array
			(
				'classes' => array('iem-hidden'),
				'input_classes' => array('iem-order-index'),
				'name' => 'order_index',
				'type' => 'hidden'
			);
			
			$fields[] = array
			(
				'classes' => array('iem-hidden'),
				'name' => 'line_item_id',
				'type' => 'hidden'
			);
		}
		
		$fields[] = array
		(
			'classes' => array('iem-hidden'),
			'name' => 'user_id',
			'type' => 'hidden',

			'value' => (is_user_logged_in())
			? get_current_user_id()
			: 0
		);
		
		$fields[] = array
		(
			'classes' => array('iem-col-md-7', 'iem-col-sm-12'),
			'type' => 'group',

			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-xs-12'),
					'description' => __('Item entry date.', 'invoiceem'),
					'input_classes' => array('iem-datepicker'),
					'name' => 'date',
					'type' => 'text',

					'attributes' => array
					(
						'placeholder' => __('Date', 'invoiceem')
					),

					'value' => ($iem->settings->invoicing->default_date)
					? InvoiceEM_Utilities::format_date(get_option('date_format'))
					: ''
				),

				array
				(
					'classes' => array('iem-col-xs-12'),
					'description' => __('Main title for the line item.', 'invoiceem'),
					'input_classes' => array('required'),
					'name' => 'title',
					'type' => 'text',

					'attributes' => array
					(
						'placeholder' => InvoiceEM_Output::required_asterisk(__('Title', 'invoiceem'), true, false)
					)
				),

				array
				(
					'classes' => array('iem-col-xs-12'),
					'description' => __('Optional additional description for the line item.', 'invoiceem'),
					'name' => 'description',
					'type' => 'textarea',

					'attributes' => array
					(
						'placeholder' => __('Description', 'invoiceem'),
						'rows' => 2
					)
				)
			)
		);
		
		$taxes_classes = array('iem-col-xs-12', 'iem-taxes');
		
		if (empty($taxes_fields))
		{
			$taxes_classes[] = 'iem-hidden';
		}
		
		$fields[] = array
		(
			'classes' => array('iem-col-md-5', 'iem-col-sm-12'),
			'type' => 'group',

			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-xs-6'),
					'description' => __('Quantity count for this line item.', 'invoiceem'),
					'input_classes' => array('iem-calculate', 'iem-quantity', 'iem-spinner', 'required'),
					'name' => 'quantity',
					'type' => 'text',
					'value' => 1,

					'attributes' => array
					(
						'data-iem-number-format' => 'n',
						'data-iem-step' => 1,
						'placeholder' => InvoiceEM_Output::required_asterisk(__('Qty.', 'invoiceem'), true, false)
					)
				),

				array
				(
					'classes' => array('iem-col-xs-6'),
					'description' => __('Type associated with the quantity for this line item.', 'invoiceem'),
					'include_clear' => true,
					'name' => 'type',
					'options' => $iem->settings->invoicing->get_quantity_types(),
					'type' => 'select',
					'value' => $iem->settings->invoicing->quantity_type
				),

				array
				(
					'classes' => $rate_classes,
					'description' => __('Rate for this line item.', 'invoiceem'),
					'input_classes' => array('iem-calculate', 'iem-currency', 'iem-rate', 'required'),
					'name' => 'rate',
					'type' => 'text'
				),

				array
				(
					'classes' => array('iem-col-xs-6'),
					'description' => __('Simple percentage adjustment for this line item.', 'invoiceem'),
					'include_clear' => true,
					'input_classes' => array('iem-adjustment', 'iem-calculate', 'iem-spinner'),
					'name' => 'adjustment',
					'type' => 'text',

					'attributes' => array
					(
						'data-iem-number-format' => 'n',
						'data-iem-step' => 0.05,
						'placeholder' => __('Adj. (%)', 'invoiceem')
					)
				),

				array
				(
					'classes' => $taxes_classes,
					'fields' => $taxes_fields,
					'type' => 'group'
				)
			)
		);
		
		$fields[] = array
		(
			'classes' => array('iem-col-xs-12'),
			'type' => 'html',

			'content' => ($is_list)
			? '<button type="button" class="button iem-button iem-add-line-item" disabled="disabled">' . __('Add Line Item', 'invoiceem') . '</button> <button type="button" class="button iem-button iem-cancel">' . __('Cancel', 'invoiceem') . '</button>'
			: '<div class="iem-totals">'
			. '<table cellspacing="0">'
			. '<tbody>'
			. '<tr>'
			. '<td>'
			. __('Line Item Subtotal:', 'invoiceem')
			. '<input type="hidden" class="iem-subtotal" />'
			. '<input type="hidden" class="iem-discounted-subtotal" />'
			. '</td>'
			. '<td class="iem-line-item-subtotal">--</td>'
			. '</tr>'
			. '</tbody>'
			. '</table>'
			. '</div>'
		);
		
		return array
		(
			'fields' => $fields,
			'hide_labels' => true,
			'type' => 'group'
		);
	}

	/**
	 * Generate an invoice output label.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @param  array  $row Details for the invoice.
	 * @return string      Generated invoice label based on the provided row.
	 */
	protected static function _generate_label($row)
	{
		$label = '';
		$unpaid = $row['total'] - $row['paid'];
		
		$unpaid_label = ($unpaid > 0)
		? sprintf
		(
			__('Unpaid: %1$s', 'invoiceem'),
			InvoiceEM_Utilities::format_currency($row['total'] - $row['paid'])
		)
		: __('Paid In Full', 'invoiceem');
		
		if (empty($row[InvoiceEM_Project::TITLE_COLUMN]))
		{
			$label = sprintf
			(
				_x('%1$s - %2$s', 'Invoice, Client', 'invoiceem'),
				$row[self::TITLE_COLUMN],
				$unpaid_label
			);
		}
		else
		{
			$label = sprintf
			(
				_x('%1$s - %2$s - %3$s', 'Invoice, Client, Project', 'invoiceem'),
				$row[InvoiceEM_Project::TITLE_COLUMN],
				$row[self::TITLE_COLUMN],
				$unpaid_label
			);
		}
		
		if
		(
			empty($row['send_date'])
			||
			$row['send_date'] < 0
		)
		{
			$label = sprintf
			(
				__('%1$s (Draft)', 'invoieem'),
				$label
			);
		}
		
		return self::_generate_label_status($row, $label);
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
		return InvoiceEM_Invoices::where_search($search);
	}
}
