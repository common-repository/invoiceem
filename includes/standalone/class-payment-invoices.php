<?php
/*!
 * Payment invoices object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Payment Invoices
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the payment invoices object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Payment_Invoices extends InvoiceEM_Object
{
	/**
	 * Column name for the payment invoice ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'payment_invoice_id';

	/**
	 * Unused title column.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'n/a';
	
	/**
	 * Payment invoice rows.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    array
	 */
	private $_rows;

	/**
	 * Raw payment invoices table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_PAYMENT_INVOICES;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $payment_id ID of the payment to load.
	 * @return void
	 */
	public function __construct($payment_id)
	{
		parent::__construct();
		
		$this->_finalize($payment_id);
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
			 * ID for the payment invoice.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
			/**
			 * ID of the payment record.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Payment::ID_COLUMN:
			
			/**
			 * ID of the invoice record.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Invoice::ID_COLUMN:
			
			/**
			 * Amount of the invoice payment.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'amount':
			
				return 0;
		}

		return parent::_default($name);
	}
	
	/**
	 * Finalize the payment invoices load.
	 *
	 * @since 1.0.6 Cleaned up database calls.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $payment_id ID for the payment being loaded.
	 * @return void
	 */
	private function _finalize($payment_id)
	{
		global $wpdb;
		
		$this->_rows = $wpdb->get_results
		(
			$wpdb->prepare
			(
				"SELECT * FROM " . InvoiceEM_Database::get_table_name(self::$_raw_table_name) . " WHERE " . InvoiceEM_Payment::ID_COLUMN . " = %d ORDER BY amount DESC, invoice_id ASC",
				$payment_id
			),
			
			ARRAY_A
		);
	}
	
	/**
	 * Get the invoices for the payment.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Invoices associated with this payment.
	 */
	public function get_payment_invoices()
	{
		$output = array();
		
		foreach ($this->_rows as $row)
		{
			if
			(
				isset($row[InvoiceEM_Invoice::ID_COLUMN])
				&&
				is_numeric($row[InvoiceEM_Invoice::ID_COLUMN])
			)
			{
				$output[$row[InvoiceEM_Invoice::ID_COLUMN]] = $row['amount'];
			}
		}
		
		return $output;
	}
	
	/**
	 * Get the field value for the payment invoices.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Field value for the payment invoices.
	 */
	public function get_value()
	{
		$i = 0;
		$count = count($this->_rows);
		
		for ($i; $i < $count; $i++)
		{
			$this->_rows[$i][InvoiceEM_Invoice::ID_COLUMN] = InvoiceEM_Invoice::selected_item($this->_rows[$i][InvoiceEM_Invoice::ID_COLUMN]);
			$this->_rows[$i]['amount'] = InvoiceEM_Utilities::format_currency($this->_rows[$i]['amount']);
		}
		
		return $this->_rows;
	}
	
	/**
	 * Update the payment invoice rows.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $payment_id ID of the payment being updated.
	 * @return boolean             True if everything was updated successfully.
	 */
	public function update($payment_id)
	{
		global $wpdb;
		
		$success = true;
		$inserting = $updating = $deleting = array();
		$update_invoices = array();
		$payment_invoices_table = InvoiceEM_Database::get_table_name(self::$_raw_table_name);
		
		$invoices =
		(
			!isset($_POST['invoices'])
			||
			!is_array($_POST['invoices'])
		)
		? array()
		: $_POST['invoices'];
		
		foreach ($invoices as $invoice)
		{
			$exists = false;
			
			foreach ($this->_rows as $row)
			{
				if
				(
					$row[InvoiceEM_Payment::ID_COLUMN] == $payment_id
					&&
					$row[InvoiceEM_Invoice::ID_COLUMN] == $invoice[InvoiceEM_Invoice::ID_COLUMN]
					&&
					!empty($row[self::ID_COLUMN])
				)
				{
					$updating[] = $invoice;
					$exists = true;
					
					break;
				}
			}
			
			if (!$exists)
			{
				$inserting[] = $invoice;
			}
		}
		
		foreach ($this->_rows as $row)
		{
			$being_updated = false;
			
			foreach ($updating as $invoice)
			{
				if ($invoice[self::ID_COLUMN] == $row[self::ID_COLUMN])
				{
					$being_updated = true;
					
					break;
				}
			}
			
			if (!$being_updated)
			{
				$deleting[] = $row;
			}
		}
		
		foreach ($inserting as $invoice)
		{
			if (!in_array($invoice[InvoiceEM_Invoice::ID_COLUMN], $update_invoices))
			{
				$update_invoices[] = $invoice[InvoiceEM_Invoice::ID_COLUMN];
			}
			
			$inserted = $wpdb->insert
			(
				$payment_invoices_table,
				
				array
				(
					InvoiceEM_Payment::ID_COLUMN => $payment_id,
					InvoiceEM_Invoice::ID_COLUMN => $invoice[InvoiceEM_Invoice::ID_COLUMN],
					'amount' => $invoice['amount']
				),
				
				array('%d', '%d', '%f')
			);
			
			$success =
			(
				$success
				&&
				$inserted != false
			);
			
			if (!$success)
			{
				break;
			}
		}
		
		if ($success)
		{
			foreach ($updating as $invoice)
			{
				if (!in_array($invoice[InvoiceEM_Invoice::ID_COLUMN], $update_invoices))
				{
					$update_invoices[] = $invoice[InvoiceEM_Invoice::ID_COLUMN];
				}
				
				$updated = $wpdb->update
				(
					$payment_invoices_table,

					array
					(
						'amount' => $invoice['amount']
					),

					array
					(
						self::ID_COLUMN => $invoice[self::ID_COLUMN]
					),

					'%f',
					'%d'
				);
				
				$success =
				(
					$success
					&&
					$updated !== false
				);

				if (!$success)
				{
					break;
				}
			}
		}
		
		if ($success)
		{
			foreach ($deleting as $invoice)
			{
				if (!in_array($invoice[InvoiceEM_Invoice::ID_COLUMN], $update_invoices))
				{
					$update_invoices[] = $invoice[InvoiceEM_Invoice::ID_COLUMN];
				}
				
				$deleted = $wpdb->delete
				(
					$payment_invoices_table,

					array
					(
						self::ID_COLUMN => $invoice[self::ID_COLUMN]
					),

					'%d'
				);

				$success =
				(
					$success
					&&
					$deleted !== false
				);

				if (!$success)
				{
					break;
				}
			}
		}
		
		if ($success)
		{
			self::_update_invoices($update_invoices);
		}
		
		$this->_finalize($payment_id);
		
		return $success;
	}
	
	/**
	 * Update paid column for payment invoices.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return boolean True if the payment invoices are updated successfully.
	 */
	public function update_invoices()
	{
		return self::_update_invoices(array_keys($this->get_payment_invoices()));
	}
	
	/**
	 * Delete unnecessary records when an invoice is deleted.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $invoice_id ID of the invoice that was deleted.
	 * @return boolean             True if the records are deleted successfully.
	 */
	public static function delete_invoice_records($invoice_id)
	{
		global $wpdb;
		
		$success = true;
		
		if (!empty($invoice_id))
		{
			$deleted = $wpdb->delete
			(
				InvoiceEM_Database::get_table_name(self::$_raw_table_name),

				array
				(
					InvoiceEM_Invoice::ID_COLUMN => $invoice_id
				),

				'%d'
			);
			
			$success = ($deleted !== false);
		}
		
		return $success;
	}
	
	/**
	 * Delete unnecessary record when a payment is deleted.
	 *
	 * @since 1.0.6 Cleaned up database calls.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $payment_id ID of the payment that was deleted.
	 * @return boolean             True if the records are deleted successfully.
	 */
	public static function delete_payment_records($payment_id)
	{
		global $wpdb;
		
		$success = true;
		
		if (!empty($payment_id))
		{
			$payment_invoices_table = InvoiceEM_Database::get_table_name(self::$_raw_table_name);
			$update_invoices = array();
			
			$invoice_ids = $wpdb->get_results
			(
				$wpdb->prepare
				(
					"SELECT " . InvoiceEM_Invoice::ID_COLUMN . " FROM " . $payment_invoices_table . " WHERE " . InvoiceEM_Payment::ID_COLUMN . " = %d",
					$payment_id
				),
				
				ARRAY_A
			);
			
			foreach ($invoice_ids as $row)
			{
				$update_invoices[] = $row[InvoiceEM_Invoice::ID_COLUMN];
			}
			
			$deleted = $wpdb->delete
			(
				$payment_invoices_table,

				array
				(
					InvoiceEM_Payment::ID_COLUMN => $payment_id
				),

				'%d'
			);
			
			if ($deleted !== false)
			{
				$success = self::_update_invoices($update_invoices);
			}
		}
		
		return $success;
	}
	
	/**
	 * Update paid column on provided invoices.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access private static
	 * @param  array   $invoices Array of invoice IDs to update.
	 * @return boolean           True if all invoices are updated successfully.
	 */
	private static function _update_invoices($invoices)
	{
		global $wpdb;
		
		$success = true;
		
		if (is_array($invoices))
		{
			$invoices_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES);
			$payment_invoices_table = InvoiceEM_Database::get_table_name(self::$_raw_table_name);
			$payments_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PAYMENTS);
			
			foreach ($invoices as $invoice_id)
			{
				$updated = $wpdb->query($wpdb->prepare
				(
					"UPDATE " . $invoices_table . " SET paid = (SELECT SUM(" . $payment_invoices_table . ".amount) FROM " . $payment_invoices_table . " INNER JOIN " . $payments_table . " ON " . $payment_invoices_table . "." . InvoiceEM_Payment::ID_COLUMN . " = " . $payments_table . "." . InvoiceEM_Payment::ID_COLUMN . " AND " . $payments_table . ".is_failed = 0 AND " . $payments_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1 WHERE " . InvoiceEM_Invoice::ID_COLUMN . " = %d) WHERE " . InvoiceEM_Invoice::ID_COLUMN . " = %d",
					$invoice_id,
					$invoice_id
				));

				$success =
				(
					$success
					&&
					$updated !== false
				);
			}
		}
		
		return $success;
	}
}
