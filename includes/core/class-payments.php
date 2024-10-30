<?php
/*!
 * Payments functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Payments
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the payments functionality.
 *
 * @since 1.0.6 Removed upgrade notice function.
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Payments extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the payments page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_PAYMENTS;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Payment::ID_COLUMN, InvoiceEM_Constants::COLUMN_PREVIOUS_ID, 'method', InvoiceEM_Payment::TITLE_COLUMN, InvoiceEM_Client::ID_COLUMN, 'payment_date', 'amount', 'bonus', 'fee', InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED);

	/**
	 * Current payment object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Payment
	 */
	private $_payment = null;

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
	 * Add the payments menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = $menu_title = __('Payments', 'invoiceem');

		$current_action = $this->base->cache->action;
		$can_add = current_user_can(InvoiceEM_Constants::CAP_ADD_PAYMENTS);

		if
		(
			$current_action == InvoiceEM_Constants::ACTION_ADD
			&&
			$can_add
		)
		{
			$this->_page_title = __('Add Payment', 'invoiceem');
		}
		else if ($current_action == InvoiceEM_Constants::ACTION_EDIT)
		{
			$this->_page_title = __('Edit Payment', 'invoiceem');
		}
		else
		{
			$this->_payment = null;
		}

		$payments_page = add_submenu_page
		(
			InvoiceEM_Invoices::PAGE_SLUG,
			$menu_title,
			$menu_title,
			
			($this->base->cache->has_clients_plus)
			? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_PAYMENTS)
			: InvoiceEM_Constants::CAP_EDIT_PAYMENTS,
			
			self::PAGE_SLUG,
			array($this, 'payments_page')
		);

		if ($payments_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $menu_title);

			if ($can_add)
			{
				$add_payment_label = __('Add Payment', 'invoiceem');
				
				add_submenu_page(InvoiceEM_Invoices::PAGE_SLUG, $add_payment_label, $add_payment_label, InvoiceEM_Constants::CAP_EDIT_PAYMENTS, self::PAGE_SLUG . '&action=' . InvoiceEM_Constants::ACTION_ADD, array($this, 'payments_page'));
			}

			add_action('load-' . $payments_page, array($this, 'load_payments_page'));
		}
	}

	/**
	 * Output the payments page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function payments_page()
	{
		if (empty($this->_payment))
		{
			InvoiceEM_Output::admin_list_page($this->_page_title, InvoiceEM_Payment_List(), InvoiceEM_Constants::CAP_ADD_PAYMENTS);
		}
		else
		{
			InvoiceEM_Output::admin_form_page($this->_page_title, '', InvoiceEM_Constants::CAP_ADD_PAYMENTS);
		}
	}

	/**
	 * Load payments page functionality.
	 *
	 * @since 1.0.5 Added list actions for reporting.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_payments_page()
	{
		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		if (empty($this->_payment))
		{
			InvoiceEM_Payment_List();

			InvoiceEM_Help::output('payment-list');
			
			do_action(InvoiceEM_Constants::HOOK_PAYMENT_LIST_ASSETS);
			do_action(InvoiceEM_Constants::HOOK_PAYMENT_LIST_HELP);
		}
		else
		{
			if ($this->base->cache->action == InvoiceEM_Constants::ACTION_ADD)
			{
				add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_next_active'), 1000);
			}
			
			add_filter(InvoiceEM_Constants::HOOK_FORM_OPTIONS, array($this, 'form_options'));

			$this->_payment->prepare();

			InvoiceEM_Help::output('payment-form');
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
				'is_payment' => true
			)
		);
	}

	/**
	 * Load the payment object if the payments page is active.
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
			$this->_payment = new InvoiceEM_Payment();
		}
	}
}
