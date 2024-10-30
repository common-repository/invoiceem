<?php
/*!
 * Cached function calls and flags.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Cache
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the cache functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Cache extends InvoiceEM_Wrapper
{
	/**
	 * Get a default cached item based on the provided name.
	 *
	 * @since 1.0.6 Removed unused default for scheduled events.
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $name Name of the cached item to return.
	 * @return mixed        Default cached item if it exists, otherwise an empty string.
	 */
	protected function _default($name)
	{
		switch ($name)
		{
			/**
			 * Current action being taken.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'accounting':

				return InvoiceEM_Currency::accounting_settings();

			/**
			 * Current action being taken.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'action':

				$action = '';

				if ($this->current_page == InvoiceEM_Constants::OPTION_SETTINGS)
				{
					return $action;
				}

				if
				(
					$this->is_post
					&&
					isset($_POST['action'])
				)
				{
					$action = strtolower(esc_attr($_POST['action']));
				}
				else if (isset($_GET['action']))
				{
					$action = strtolower(esc_attr($_GET['action']));
				}

				return
				(
					empty($action)
					||
					!defined('InvoiceEM_Constants::ACTION_' . strtoupper(str_replace('-', '_', $action)))
				)
				? InvoiceEM_Constants::ACTION_LIST
				: $action;

			/**
			 * Path to the plugin assets folder.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'assets_url':

				$folder = 'debug';
				
				if
				(
					!$this->script_debug
					||
					!file_exists(plugin_dir_path($this->base->plugin) . 'assets/' . $folder . '/')
				)
				{
					$folder = 'release';
				}

				return plugins_url('/assets/' . $folder . '/', $this->base->plugin);

			/**
			 * Current page being viewed.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'current_page':

				return
				(
					isset($_GET['page'])
					&&
					!empty($_GET['page'])
				)
				? esc_attr($_GET['page'])
				: '';
				
			/**
			 * Strings used in dialog boxes.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'dialog_strings':
			
				return array
				(
					'cancel' => __('Cancel', 'invoiceem'),
					'close' => __('Close', 'invoiceem'),
					'confirm' => __('Confirm', 'invoiceem')
				);

			/**
			 * Validation rules for the current form.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'form_validation':

				return array();
				
			/**
			 * True if the InvoiceEM Clients+ extension is active and valid.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'has_clients_plus':
			
				return
				(
					InvoiceEM_Utilities::is_plugin_active('iem-clients-plus/iem-clients-plus.php')
					&&
					IEM_Clients_Plus()->cache->lv
				);
				
			/**
			 * True if the InvoiceEM Invoices+ extension is active.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'has_invoices_plus':
			
				return
				(
					InvoiceEM_Utilities::is_plugin_active('iem-invoices-plus/iem-invoices-plus.php')
					&&
					IEM_Invoices_Plus()->cache->lv
				);
				
			/**
			 * True if the InvoiceEM Payments+ extension is active.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'has_payments_plus':
			
				return
				(
					InvoiceEM_Utilities::is_plugin_active('iem-payments-plus/iem-payments-plus.php')
					&&
					IEM_Payments_Plus()->cache->lv
				);
				
			/**
			 * True if the InvoiceEM Reporting+ extension is active.
			 *
			 * @since 1.0.5
			 *
			 * @var boolean
			 */
			case 'has_reporting_plus':
			
				return
				(
					InvoiceEM_Utilities::is_plugin_active('iem-reporting-plus/iem-reporting-plus.php')
					&&
					IEM_Reporting_Plus()->cache->lv
				);
				
			/**
			 * True if the InvoiceEM Regional+ extension is active.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'has_regional_plus':
			
				return
				(
					InvoiceEM_Utilities::is_plugin_active('iem-regional-plus/iem-regional-plus.php')
					&&
					IEM_REgional_Plus()->cache->lv
				);

			/**
			 * True if the page is currently being viewed in an IFRAME.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_iframe':

				return
				(
					defined('IEM_IFRAME')
					&&
					IEM_IFRAME
				);
				
			/**
			 * True if the current user can only view client information.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_client':
			
				return
				(
					$this->has_clients_plus
					&&
					IEM_Clients_Plus()->cache->is_client
				);

			/**
			 * True if the page was requested via a POST.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_post':

				return
				(
					isset($_SERVER['REQUEST_METHOD'])
					&&
					$_SERVER['REQUEST_METHOD'] == 'POST'
				);

			/**
			 * Current list page URL.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'list_url':

				$search = $this->search_query;
				$add_args = array();
				$remove_args = array('action', InvoiceEM_Constants::IFRAME_NONCE, InvoiceEM_Constants::NONCE);
				$current_page = $this->current_page;
				$filter_args = array();

				if
				(
					$this->action != InvoiceEM_Constants::ACTION_LIST
					&&
					isset($_GET['s'])
					&&
					!empty('s')
				)
				{
					$add_args['s'] = esc_attr($_GET['s']);
				}
				else if (empty($search))
				{
					$remove_args[] = 's';
				}
				else
				{
					$add_args['s'] = $search;
				}

				if ($current_page == InvoiceEM_Invoices::PAGE_SLUG)
				{
					$remove_args[] = 'invoice_id';
					$filter_args['InvoiceEM_Invoice_List'] = array(InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN, InvoiceEM_Constants::FILTER . InvoiceEM_Project::ID_COLUMN);
				}
				else if ($current_page == InvoiceEM_Payments::PAGE_SLUG)
				{
					$remove_args[] = 'payment_id';
				}
				else if ($current_page == InvoiceEM_Projects::PAGE_SLUG)
				{
					$remove_args[] = InvoiceEM_Project::ID_COLUMN;
					$filter_args['InvoiceEM_Project_List'] = array(InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN);
				}
				else if ($current_page == InvoiceEM_Clients::PAGE_SLUG)
				{
					$remove_args[] = InvoiceEM_Client::ID_COLUMN;
				}
				else if ($current_page == InvoiceEM_Countries::PAGE_SLUG)
				{
					$remove_args[] = InvoiceEM_Country::ID_COLUMN;
				}
				else if ($current_page == InvoiceEM_Currencies::PAGE_SLUG)
				{
					$remove_args[] = InvoiceEM_Currency::ID_COLUMN;
				}
				
				foreach ($filter_args as $class => $filters)
				{
					foreach ($filters as $filter)
					{
						if
						(
							$this->action != InvoiceEM_Constants::ACTION_LIST
							&&
							isset($_GET[$filter])
							&&
							!empty($filter)
						)
						{
							$add_args[$filter] = esc_attr($_GET[$filter]);
						}
						else if (empty($class::${$filter}))
						{
							$remove_args[] = $filter;
						}
						else
						{
							$add_args[$filter] = $class::${$filter};
						}
					}
				}

				return InvoiceEM_Utilities::modify_admin_url($add_args, $remove_args);

			/**
			 * New URL for the address bar.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'new_url':

				return '';

			/**
			 * Asset file names pulled from the manifest JSON.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'manifest':

				return InvoiceEM_Utilities::load_json('assets/manifest.json');

			/**
			 * General details about the plugin.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'plugin_data':

				return (function_exists('get_plugin_data'))
				? get_plugin_data($this->base->plugin)
				: array
				(
					'AuthorName' => 'Robert Noakes',
					'Name' => 'InvoiceEM'
				);

			/**
			 * Object for the current screen.
			 *
			 * @since 1.0.0
			 *
			 * @var WP_Screen
			 */
			case 'screen':

				return (function_exists('get_current_screen'))
				? get_current_screen()
				: new WP_Screen();
				
			/**
			 * True if script debugging is enabled.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'script_debug':
			
				return
				(
					defined('SCRIPT_DEBUG')
					&&
					SCRIPT_DEBUG
				);

			/**
			 * Current search query.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'search_query':

				$search = '';

				if
				(
					$this->is_post
					&&
					isset($_POST[InvoiceEM_Constants::NONCE])
				)
				{
					$search = (isset($_POST['s']))
					? esc_attr($_POST['s'])
					: '';
				}
				else if (isset($_GET['s']))
				{
					$search = esc_attr($_GET['s']);
				}

				return $search;
				
			/**
			 * Base URL for views.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'view_url_base':
			
				return (empty(get_option('permalink_structure')))
				? 'index.php?' . $this->base->settings->invoicing->rewrite_base . '='
				: $this->base->settings->invoicing->rewrite_base . '/';
		}

		return parent::_default($name);
	}

	/**
	 * Obtain a path to an asset.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $path      Path to the asset folder.
	 * @param  string $file_name File name for the asset.
	 * @return string            Full path to the requested asset.
	 */
	public function asset_path($path, $file_name)
	{
		$manifest = $this->manifest;

		if (isset($manifest[$file_name]))
		{
			$file_name = $manifest[$file_name];
		}

		return trailingslashit($this->assets_url . $path) . $file_name;
	}
}

