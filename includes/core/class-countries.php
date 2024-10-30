<?php
/*!
 * Countries functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Countries
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the countries functionality.
 *
 * @since 1.0.6 Removed upgrade notice function.
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Countries extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the countries page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_COUNTRIES;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Country::ID_COLUMN, InvoiceEM_Country::TITLE_COLUMN, 'official_name', 'three_digit_code', 'two_digit_code', 'flag', InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED);

	/**
	 * Current country object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Country
	 */
	private $_country = null;

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
	 * Add the countries menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = __('Edit Country', 'invoiceem');

		$countries_page = add_submenu_page(null, $this->_page_title, $this->_page_title, InvoiceEM_Constants::CAP_EDIT_COUNTRIES, self::PAGE_SLUG, array($this, 'countries_page'));

		if ($countries_page)
		{
			add_action('load-' . $countries_page, array($this, 'load_countries_page'));
		}
	}

	/**
	 * Output the countries page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function countries_page()
	{
		InvoiceEM_Output::admin_form_page($this->_page_title);
	}

	/**
	 * Load countries page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_countries_page()
	{
		if ($this->base->cache->action != InvoiceEM_Constants::ACTION_EDIT)
		{
			wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
		}

		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		$this->_country->prepare();

		InvoiceEM_Help::output('country-form');
	}

	/**
	 * Load the country object if the countries page is active.
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
			$this->_country = new InvoiceEM_Country();
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

		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_COUNTRIES);
		$search = "%" . $wpdb->esc_like($search) . "%";

		return $wpdb->prepare
		(
			"(" . $table_name . "." . InvoiceEM_Country::TITLE_COLUMN . " LIKE %s OR " . $table_name . ".official_name LIKE %s OR " . $table_name . ".three_digit_code LIKE %s OR " . $table_name . ".two_digit_code LIKE %s)",
			$search,
			$search,
			$search,
			$search
		);
	}
}
