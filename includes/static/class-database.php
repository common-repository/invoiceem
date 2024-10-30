<?php
/*!
 * Plugin database functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Database
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the database functionality.
 *
 * @since 1.0.6 Removed unnecessary function.
 * @since 1.0.0
 */
final class InvoiceEM_Database
{
	/**
	 * Maximum database index length.
	 *
	 * @since 1.0.0
	 *
	 * @const integer
	 */
	const MAX_INDEX_LENGTH = 191;
	
	/**
	 * Get a class name for a table.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $raw_table_name Raw database table name.
	 * @return string                 Class name associated with the table.
	 */
	public static function get_table_class($raw_table_name)
	{
		switch ($raw_table_name)
		{
			case InvoiceEM_Constants::TABLE_CLIENTS:
			
				return 'InvoiceEM_Client';
				
			case InvoiceEM_Constants::TABLE_COUNTRIES:
			
				return 'InvoiceEM_Country';
				
			case InvoiceEM_Constants::TABLE_CURRENCIES:
			
				return 'InvoiceEM_Currency';
				
			case InvoiceEM_Constants::TABLE_INVOICES:
			
				return 'InvoiceEM_Invoice';
				
			case InvoiceEM_Constants::TABLE_PAYMENTS:
			
				return 'InvoiceEM_Payment';
				
			case InvoiceEM_Constants::TABLE_PROJECTS:
			
				return 'InvoiceEM_Project';
		}
		
		return '';
	}

	/**
	 * Get a full database table name.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $raw_table_name Raw database table name.
	 * @return string                 Full table name.
	 */
	public static function get_table_name($raw_table_name)
	{
		global $wpdb;

		return $wpdb->prefix . InvoiceEM_Constants::PREFIX . sanitize_key($raw_table_name);
	}
}
