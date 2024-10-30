<?php
/*!
 * Invoices functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Invoices
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the invoices functionality.
 *
 * @since 1.0.6 Removed upgrade notice function.
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Invoices extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the invoices page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_INVOICES;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Invoice::ID_COLUMN, InvoiceEM_Constants::COLUMN_PREVIOUS_ID, 'invoice_type', InvoiceEM_Invoice::TITLE_COLUMN, InvoiceEM_Client::ID_COLUMN, InvoiceEM_Project::ID_COLUMN, 'po_number', 'deposit', 'deposit_due', 'pre_tax_discount', 'discount', 'invoice_title', 'invoice_number', 'send_date', 'payment_due', 'total', 'paid', 'last_viewed', InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED);
	
	/**
	 * Object used for page output.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    object
	 */
	public $action_object = null;

	/**
	 * Current invoice object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Invoice
	 */
	private $_invoice = null;
	
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
	 * Add the main and invoices menu items.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = $menu_title = __('Invoices', 'invoiceem');

		$current_action = $this->base->cache->action;
		$can_add = current_user_can(InvoiceEM_Constants::CAP_ADD_INVOICES);

		if
		(
			$current_action == InvoiceEM_Constants::ACTION_ADD
			&&
			$can_add
		)
		{
			$this->_page_title = __('Add Invoice', 'invoiceem');
		}
		else if ($current_action == InvoiceEM_Constants::ACTION_EDIT)
		{
			$this->_page_title = __('Edit Invoice', 'invoiceem');
		}
		else
		{
			$this->_invoice = null;
		}

		$invoices_cap = ($this->base->cache->has_clients_plus)
		? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_INVOICES)
		: InvoiceEM_Constants::CAP_EDIT_INVOICES;
		
		$invoices_page = add_menu_page($menu_title, __('InvoiceEM', 'invoiceem'), $invoices_cap, self::PAGE_SLUG, array($this, 'invoices_page'), 'dashicons-media-spreadsheet', 3);

		if ($invoices_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $menu_title);

			add_submenu_page(self::PAGE_SLUG, $menu_title, $menu_title, $invoices_cap, self::PAGE_SLUG, array($this, 'invoices_page'));
			
			if ($can_add)
			{
				$add_invoice_label = __('Add Invoice', 'invoiceem');
				
				add_submenu_page(self::PAGE_SLUG, $add_invoice_label, $add_invoice_label, InvoiceEM_Constants::CAP_EDIT_INVOICES, self::PAGE_SLUG . '&action=' . InvoiceEM_Constants::ACTION_ADD, array($this, 'invoices_page'));
			}

			add_action('load-' . $invoices_page, array($this, 'load_invoices_page'));
		}
	}

	/**
	 * Output the invoices page.
	 *
	 * @since 1.0.5 Modified for secondary tab functionality.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function invoices_page()
	{
		if
		(
			!empty($this->action_object)
			&&
			method_exists($this->action_object, 'page')
		)
		{
			$this->action_object->page();
		}
		else if (empty($this->_invoice))
		{
			InvoiceEM_Output::admin_list_page($this->_page_title, InvoiceEM_Invoice_List(), InvoiceEM_Constants::CAP_ADD_INVOICES, InvoiceEM_Constants::ACTION_LIST);
		}
		else
		{
			InvoiceEM_Output::admin_form_page($this->_page_title, '', InvoiceEM_Constants::CAP_ADD_INVOICES, InvoiceEM_Constants::ACTION_ADD);
		}
	}

	/**
	 * Load invoices page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_invoices_page()
	{
		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		if
		(
			!empty($this->action_object)
			&&
			method_exists($this->action_object, 'load_page')
		)
		{
			$this->action_object->load_page();
		}
		else if (empty($this->_invoice))
		{
			InvoiceEM_Invoice_List();
			
			InvoiceEM_Help::output('invoice-list');
			
			do_action(InvoiceEM_Constants::HOOK_INVOICE_LIST_ASSETS);
			do_action(InvoiceEM_Constants::HOOK_INVOICE_LIST_HELP);
		}
		else
		{
			if ($this->base->cache->action == InvoiceEM_Constants::ACTION_ADD)
			{
				add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_next_active'), 1000);
			}
			
			add_filter(InvoiceEM_Constants::HOOK_FORM_OPTIONS, array($this, 'form_options'));

			$this->_invoice->prepare();
			
			InvoiceEM_Help::output('invoice-form');
			
			do_action(InvoiceEM_Constants::HOOK_INVOICE_ASSETS);
			do_action(InvoiceEM_Constants::HOOK_INVOICE_HELP);
		}
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
				'is_invoice' => true
			)
		);
	}

	/**
	 * Load the invoice object if the invoices page is active.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function loaded()
	{
		if
		(
			!$this->base->cache->is_client
			&&
			$this->base->cache->current_page == self::PAGE_SLUG
		)
		{
			$this->_invoice = new InvoiceEM_Invoice();
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

		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES);
		$search = "%" . $wpdb->esc_like($search) . "%";

		return $wpdb->prepare
		(
			"(" . $table_name . "." . InvoiceEM_Invoice::TITLE_COLUMN . " LIKE %s OR " . $table_name . ".po_number LIKE %s OR " . $table_name . ".invoice_title LIKE %s OR " . $table_name . ".invoice_number LIKE %s)",
			$search,
			$search,
			$search,
			$search
		);
	}
}
