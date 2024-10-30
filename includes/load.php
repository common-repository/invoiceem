<?php
/*!
 * Plugin loader functions.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Loader
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Setup autoloading for plugin classes.
 *
 * @since 1.0.0
 */
spl_autoload_register(function ($class)
{
	$base_class = 'InvoiceEM';

	if (strpos($class, $base_class) === 0)
	{
		$base_path = dirname(__FILE__) . '/';
		$core_path = $base_path . 'core/class-';
		$standalone_path = $base_path . 'standalone/class-';
		$static_path = $base_path . 'static/class-';

		$file_name = ($class == $base_class)
		? 'base'
		: strtolower(str_replace(array($base_class . '_', '_'), array('', '-'), $class));

		$file_name .= '.php';

		if (file_exists($core_path . $file_name))
		{
			require_once($core_path . $file_name);
		}
		else if (file_exists($standalone_path . $file_name))
		{
			require_once($standalone_path . $file_name);
		}
		else if (file_exists($static_path . $file_name))
		{
			require_once($static_path . $file_name);
		}
	}
	else if ($class == 'WP_List_Table')
	{
		require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}
	else if ($class == 'WP_Screen')
	{
		require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
	}
});

/**
 * Returns the main instance of InvoiceEM.
 *
 * @since 1.0.0
 *
 * @param  string    $file Optional main plugin file name.
 * @return InvoiceEM       Main InvoiceEM instance.
 */
function InvoiceEM($file = '')
{
	return InvoiceEM::_get_instance($file);
}

/**
 * Returns the main instance of InvoiceEM_Client_List.
 *
 * @since 1.0.0
 *
 * @return InvoiceEM_Client_List Main InvoiceEM_Client_List instance.
 */
function InvoiceEM_Client_List()
{
	return InvoiceEM_Client_List::_get_instance();
}

/**
 * Returns the main instance of InvoiceEM_Invoice_List.
 *
 * @since 1.0.0
 *
 * @return InvoiceEM_Invoice_List Main InvoiceEM_Invoice_List instance.
 */
function InvoiceEM_Invoice_List()
{
	return InvoiceEM_Invoice_List::_get_instance();
}

/**
 * Returns the main instance of InvoiceEM_Payment_List.
 *
 * @since 1.0.0
 *
 * @return InvoiceEM_Payment_List Main InvoiceEM_Payment_List instance.
 */
function InvoiceEM_Payment_List()
{
	return InvoiceEM_Payment_List::_get_instance();
}

/**
 * Returns the main instance of InvoiceEM_Project_List.
 *
 * @since 1.0.0
 *
 * @return InvoiceEM_Project_List Main InvoiceEM_Project_List instance.
 */
function InvoiceEM_Project_List()
{
	return InvoiceEM_Project_List::_get_instance();
}
