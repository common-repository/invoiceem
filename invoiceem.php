<?php
/*!
 * Plugin Name: InvoiceEM
 * Plugin URI:  https://wordpress.org/plugins/invoiceem/
 * Description: InvoiceEM is the portable WordPress invoicing plugin. All records are stored in custom database tables instead of mixing records with posts.
 * Version:     1.0.6
 * Author:      Robert Noakes
 * Author URI:  https://robertnoakes.com/
 * Text Domain: invoiceem
 * Domain Path: /languages/
 * Copyright:   (c) 2019-2020 Robert Noakes (mr@robertnoakes.com)
 * License:     GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
 
/**
 * Main plugin file.
 * 
 * @since 1.0.0
 * 
 * @package InvoiceEM
 */
 
if (!defined('ABSPATH'))
{
	exit;
}

require_once(dirname(__FILE__) . '/includes/load.php');

register_activation_hook(__FILE__, array('InvoiceEM_Setup', 'activate'));
register_deactivation_hook(__FILE__, array('InvoiceEM_Setup', 'deactivate'));

InvoiceEM(__FILE__);
