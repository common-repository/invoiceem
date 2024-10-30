<?php
/*!
 * Projects functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Projects
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the projects functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Projects extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the projects page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_' . InvoiceEM_Constants::TABLE_PROJECTS;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = array(InvoiceEM_Project::ID_COLUMN, InvoiceEM_Constants::COLUMN_PREVIOUS_ID, InvoiceEM_Project::TITLE_COLUMN, InvoiceEM_Client::ID_COLUMN, 'website', 'rate', 'start_date', 'end_date', InvoiceEM_Constants::COLUMN_IS_ACTIVE, InvoiceEM_Constants::COLUMN_LOCKED);

	/**
	 * Current project object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Project
	 */
	private $_project = null;

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
	 * Add the projects menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = $menu_title = __('Projects', 'invoiceem');

		$current_action = $this->base->cache->action;

		if
		(
			$current_action == InvoiceEM_Constants::ACTION_ADD
			&&
			current_user_can(InvoiceEM_Constants::CAP_ADD_PROJECTS)
		)
		{
			$this->_page_title = __('Add Project', 'invoiceem');
		}
		else if ($current_action == InvoiceEM_Constants::ACTION_EDIT)
		{
			$this->_page_title = __('Edit Project', 'invoiceem');
		}
		else
		{
			$this->_project = null;
		}

		$projects_page = add_submenu_page
		(
			InvoiceEM_Invoices::PAGE_SLUG,
			$this->_page_title,
			$menu_title,
			
			($this->base->cache->has_clients_plus)
			? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_EDIT_PROJECTS)
			: InvoiceEM_Constants::CAP_EDIT_PROJECTS,
			
			self::PAGE_SLUG,
			array($this, 'projects_page')
		);

		if ($projects_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $menu_title);

			add_action('load-' . $projects_page, array($this, 'load_projects_page'));
		}
	}

	/**
	 * Output the projects page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function projects_page()
	{
		if (empty($this->_project))
		{
			InvoiceEM_Output::admin_list_page($this->_page_title, InvoiceEM_Project_List(), InvoiceEM_Constants::CAP_ADD_PROJECTS);
		}
		else
		{
			InvoiceEM_Output::admin_form_page($this->_page_title, '', InvoiceEM_Constants::CAP_ADD_PROJECTS);
		}
	}

	/**
	 * Load projects page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_projects_page()
	{
		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		if (empty($this->_project))
		{
			InvoiceEM_Project_List();

			InvoiceEM_Help::output('project-list');
		}
		else
		{
			$this->_project->prepare();

			InvoiceEM_Help::output('project-form');
		}
	}

	/**
	 * Load the project object if the project page is active.
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
			$this->_project = new InvoiceEM_Project();
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

		$table_name = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);
		$search = "%" . $wpdb->esc_like($search) . "%";

		return $wpdb->prepare
		(
			"(" . $table_name . "." . InvoiceEM_Project::TITLE_COLUMN . " LIKE %s OR " . $table_name . ".website LIKE %s)",
			$search,
			$search
		);
	}
}
