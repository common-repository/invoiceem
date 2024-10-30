<?php
/*!
 * Settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Settings extends InvoiceEM_Wrapper
{
	/**
	 * General settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings_General
	 */
	public $general;

	/**
	 * Company settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings_Company
	 */
	public $company;

	/**
	 * Invoicing settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings_Invoicing
	 */
	public $invoicing;

	/**
	 * Email settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings_Email
	 */
	public $email;

	/**
	 * Translation settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings_Translation
	 */
	public $translation;

	/**
	 * Active settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    object
	 */
	public $active_object;

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

		$this->general = new InvoiceEM_Settings_General();
		$this->company = new InvoiceEM_Settings_Company();
		$this->invoicing = new InvoiceEM_Settings_Invoicing();
		$this->email = new InvoiceEM_Settings_Email();
		$this->translation = new InvoiceEM_Settings_Translation();
		
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('init', array($this, 'init'), 20);

		add_filter('plugin_action_links_' . plugin_basename($this->base->plugin), array($this, 'plugin_action_links'), 12);
	}

	/**
	 * Add the settings menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = __('Settings', 'invoiceem');

		$settings_page = add_submenu_page(InvoiceEM_Invoices::PAGE_SLUG, $this->_page_title, $this->_page_title, 'manage_options', InvoiceEM_Constants::OPTION_SETTINGS, array($this, 'settings_page'));

		if ($settings_page)
		{
			add_action('load-' . $settings_page, array($this, 'load_settings_page'));

			InvoiceEM_Output::add_tab('admin.php', InvoiceEM_Constants::OPTION_SETTINGS, $this->_page_title);
		}
	}

	/**
	 * Set the active tab object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{
		if (empty($this->active_object))
		{
			$this->active_object = $this->general;
		}
	}

	/**
	 * Output the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_page()
	{
		do_action(InvoiceEM_Constants::HOOK_SETTINGS_TABS);
		
		$tab_slug = constant(get_class($this->active_object) . '::TAB_SLUG');

		InvoiceEM_Output::admin_form_page($this->_page_title, InvoiceEM_Constants::OPTION_SETTINGS . '_' . str_replace('-', '_', $tab_slug), '', $tab_slug);
	}

	/**
	 * Load settings page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_settings_page()
	{
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 1000);
		add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_templates'));

		add_filter('admin_body_class', array('InvoiceEM_Global', 'admin_body_class'));
		
		add_screen_option
		(
			'layout_columns',

			array
			(
				'default' => 2,
				'max' => 2
			)
		);

		if (method_exists($this->active_object, 'add_meta_boxes'))
		{
			$this->active_object->add_meta_boxes();

			InvoiceEM_Meta_Box::finalize_meta_boxes();
			
			if (method_exists($this->active_object, 'add_help_tab'))
			{
				$this->active_object->add_help_tab();
			}
			else
			{
				InvoiceEM_Help::output('settings-' . constant(get_class($this->active_object) . '::TAB_SLUG'));
			}
			
			if (is_a($this->active_object, 'InvoiceEM_Settings_Email'))
			{
				do_action(InvoiceEM_Constants::HOOK_EMAIL_SETTINGS_HELP);
			}
			else if (is_a($this->active_object, 'InvoiceEM_Settings_Translation'))
			{
				do_action(InvoiceEM_Constants::HOOK_TRANSLATION_SETTINGS_HELP);
			}
		}
	}

	/**
	 * Enqueue the WordPress media functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_media();
		
		InvoiceEM_Global::admin_enqueue_scripts_form();
	}

	/**
	 * Add settings to the plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $links Existing action links.
	 * @return array        Modified action links.
	 */
	public function plugin_action_links($links)
	{
		array_unshift($links, '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . InvoiceEM_Constants::OPTION_SETTINGS)) . '">' . __('Settings', 'invoiceem') . '</a>');

		return $links;
	}
}
