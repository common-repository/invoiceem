<?php
/*!
 * Functionality for plugin uninstallation.
 * 
 * @since 1.0.6 Added functionality to remove the processing option.
 * @since 1.0.0
 * 
 * @package InvoiceEM
 */

if
(
	!defined('WP_UNINSTALL_PLUGIN')
	&&
	!defined('IEM_FAUX_UNINSTALL_PLUGIN')
)
{
	exit;
}

$includes_path = dirname(__FILE__) . '/includes/';

require_once($includes_path . 'static/class-constants.php');

$general_settings = get_option(InvoiceEM_Constants::OPTION_SETTINGS_GENERAL);
$delete_version = true;

if
(
	isset($general_settings[InvoiceEM_Constants::SETTING_DELETE_ROLES])
	&&
	$general_settings[InvoiceEM_Constants::SETTING_DELETE_ROLES]
)
{
	remove_role(InvoiceEM_Constants::ROLE_ACCOUNT_MANAGER);
	
	$capabilities = array
	(
		InvoiceEM_Constants::CAP_ADD_CLIENTS,
		InvoiceEM_Constants::CAP_ADD_INVOICES,
		InvoiceEM_Constants::CAP_ADD_PAYMENTS,
		InvoiceEM_Constants::CAP_ADD_PROJECTS,
		InvoiceEM_Constants::CAP_DELETE_CLIENTS,
		InvoiceEM_Constants::CAP_DELETE_INVOICES,
		InvoiceEM_Constants::CAP_DELETE_PAYMENTS,
		InvoiceEM_Constants::CAP_DELETE_PROJECTS,
		InvoiceEM_Constants::CAP_EDIT_CLIENTS,
		InvoiceEM_Constants::CAP_EDIT_COUNTRIES,
		InvoiceEM_Constants::CAP_EDIT_CURRENCIES,
		InvoiceEM_Constants::CAP_EDIT_INVOICES,
		InvoiceEM_Constants::CAP_EDIT_PAYMENTS,
		InvoiceEM_Constants::CAP_EDIT_PROJECTS,
		InvoiceEM_Constants::CAP_VIEW_REPORTS
	);
	
	$administrator = get_role('administrator');
	
	foreach ($capabilities as $capability)
	{
		$administrator->remove_cap($capability);
	}
}
else
{
	$delete_version = false;
}

if
(
	isset($general_settings[InvoiceEM_Constants::SETTING_DELETE_SETTINGS])
	&&
	$general_settings[InvoiceEM_Constants::SETTING_DELETE_SETTINGS]
)
{
	delete_option(InvoiceEM_Constants::OPTION_SETTINGS_COMPANY);
	delete_option(InvoiceEM_Constants::OPTION_SETTINGS_GENERAL);
	delete_option(InvoiceEM_Constants::OPTION_SETTINGS_INVOICING);
	delete_option(InvoiceEM_Constants::OPTION_SETTINGS_EMAIL);
	delete_option(InvoiceEM_Constants::OPTION_SETTINGS_TRANSLATION);
}
else
{
	$delete_version = false;
}

if ($delete_version)
{
	delete_option(InvoiceEM_Constants::OPTION_PROCESSING);
	delete_option(InvoiceEM_Constants::OPTION_VERSION);
}
