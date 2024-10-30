<?php
/*!
 * Clients functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Clients
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the clients functionality.
 *
 * @since 1.0.6 Removed upgrade notice function.
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Clients extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the clients page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_CLIENTS;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Client::ID_COLUMN, InvoiceEM_Constants::COLUMN_PREVIOUS_ID, InvoiceEM_Client::TITLE_COLUMN, 'invoice_prefix', 'website', 'rate', 'since', 'email', 'phone', 'fax', InvoiceEM_Country::ID_COLUMN, InvoiceEM_Currency::ID_COLUMN, InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED, 'taxes');

	/**
	 * Current client object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Client
	 */
	private $_client = null;

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

		add_action('admin_menu', array($this, 'admin_menu'));
		add_action(InvoiceEM_Constants::HOOK_LOADED, array($this, 'loaded'));
	}

	/**
	 * Add the clients menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = $menu_title = __('Clients', 'invoiceem');

		$current_action = $this->base->cache->action;

		if
		(
			$current_action == InvoiceEM_Constants::ACTION_ADD
			&&
			current_user_can(InvoiceEM_Constants::CAP_ADD_CLIENTS)
		)
		{
			$this->_page_title = __('Add Client', 'invoiceem');
		}
		else if ($current_action == InvoiceEM_Constants::ACTION_EDIT)
		{
			$this->_page_title = __('Edit Client', 'invoiceem');
		}
		else
		{
			$this->_client = null;
		}

		$clients_page = add_submenu_page(InvoiceEM_Invoices::PAGE_SLUG, $this->_page_title, $menu_title, InvoiceEM_Constants::CAP_EDIT_CLIENTS, self::PAGE_SLUG, array($this, 'clients_page'));

		if ($clients_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $menu_title);

			add_action('load-' . $clients_page, array($this, 'load_clients_page'));
		}
	}

	/**
	 * Output the clients page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function clients_page()
	{
		if (empty($this->_client))
		{
			InvoiceEM_Output::admin_list_page($this->_page_title, InvoiceEM_Client_List(), InvoiceEM_Constants::CAP_ADD_CLIENTS);
		}
		else
		{
			InvoiceEM_Output::admin_form_page($this->_page_title, '', InvoiceEM_Constants::CAP_ADD_CLIENTS);
		}
	}

	/**
	 * Load clients page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_clients_page()
	{
		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		if (empty($this->_client))
		{
			InvoiceEM_Client_List();
			
			InvoiceEM_Help::output('client-list');
		}
		else
		{
			$this->_client->prepare();
			
			InvoiceEM_Help::output('client-form');
			
			do_action(InvoiceEM_Constants::HOOK_CLIENT_ASSETS);
			do_action(InvoiceEM_Constants::HOOK_CLIENT_HELP);
		}
	}

	/**
	 * Load the client object if the client page is active.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function loaded()
	{
		if ($this->base->cache->current_page == self::PAGE_SLUG)
		{
			$this->_client = new InvoiceEM_Client();
		}
	}

	/**
	 * Get the search WHERE query.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $search Term used in the search query.
	 * @return string         Generated search WHERE query.
	 */
	public static function where_search($search)
	{
		global $wpdb;

		if (empty($search))
		{
			return "";
		}

		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);
		$search = "%" . $wpdb->esc_like($search) . "%";

		return $wpdb->prepare
		(
			"(" . $table_name . "." . InvoiceEM_Client::TITLE_COLUMN . " LIKE %s OR " . $table_name . ".invoice_prefix LIKE %s OR " . $table_name . ".website LIKE %s OR " . $table_name . ".email LIKE %s)",
			$search,
			$search,
			$search,
			$search
		);
	}
}
