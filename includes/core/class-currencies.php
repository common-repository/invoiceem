<?php
/*!
 * Currencies functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Currencies
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the currencies functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Currencies extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the currencies page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_CURRENCIES;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Currency::ID_COLUMN, InvoiceEM_Currency::TITLE_COLUMN, 'currency_name', 'symbol', 'thousand_separator', 'number_grouping', 'decimal_separator', 'decimal_digits', 'positive_format', 'negative_format', 'zero_format', InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED);

	/**
	 * Current currency object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Currency
	 */
	private $_currency = null;

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
		add_action(InvoiceEM_Constants::HOOK_LOADED, array($this, 'loaded'), 9);
	}

	/**
	 * Add the currencies menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = __('Edit Currency', 'invoiceem');

		$currencies_page = add_submenu_page(null, $this->_page_title, $this->_page_title, InvoiceEM_Constants::CAP_EDIT_CURRENCIES, self::PAGE_SLUG, array($this, 'currencies_page'));

		if ($currencies_page)
		{
			add_action('load-' . $currencies_page, array($this, 'load_currencies_page'));
		}
	}

	/**
	 * Output the currencies page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function currencies_page()
	{
		InvoiceEM_Output::admin_form_page($this->_page_title);
	}

	/**
	 * Load currencies page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_currencies_page()
	{
		if ($this->base->cache->action != InvoiceEM_Constants::ACTION_EDIT)
		{
			wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
		}
		
		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		add_filter(InvoiceEM_Constants::HOOK_FORM_OPTIONS, array($this, 'form_options'));
		
		$this->_currency->prepare();

		InvoiceEM_Help::output('currency-form');
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
				'is_currency' => true
			)
		);
	}

	/**
	 * Load the currency object if the currencies page is active.
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
			$this->_currency = new InvoiceEM_Currency();
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

		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CURRENCIES);
		$search = "%" . $wpdb->esc_like($search) . "%";

		return $wpdb->prepare
		(
			"(" . $table_name . "." . InvoiceEM_Currency::TITLE_COLUMN . " LIKE %s OR " . $table_name . ".currency_name LIKE %s OR " . $table_name . ".symbol LIKE %s)",
			$search,
			$search,
			$search
		);
	}
}
