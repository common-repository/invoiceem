<?php
/*!
 * Wrapper for settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Settings Wrapper
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Abstract class used to implement the settings class functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
abstract class InvoiceEM_Settings_Wrapper extends InvoiceEM_Wrapper
{
	/**
	 * Tab slug for the settings object.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = '';

	/**
	 * Option name for the current settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected $_option_name = InvoiceEM_Constants::OPTION_SETTINGS;

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

		if (!empty(static::TAB_SLUG))
		{
			$this->_option_name .= '_' . str_replace('-', '_', static::TAB_SLUG);
		}

		$this->load_option();

		add_action('admin_init', array($this, 'admin_init'));
		add_action('init', array($this, 'init'));
	}

	/**
	 * Load the settings option.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $settings Setting array to load, or null of the settings should be loaded from the database.
	 * @return void
	 */
	public function load_option($settings = null)
	{
		if (empty($settings))
		{
			$settings = get_option($this->_option_name);
		}

		$this->_set_properties($settings);
	}

	/**
	 * Register the settings option.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_init()
	{
		register_setting
		(
			$this->_option_name,
			$this->_option_name,
			
			(method_exists($this, 'sanitize'))
			? array('sanitize_callback' => array($this, 'sanitize'))
			: array()
		);
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
		if
		(
			isset($_REQUEST['tab'])
			&&
			sanitize_key($_REQUEST['tab']) == static::TAB_SLUG
		)
		{
			$this->base->settings->active_object = $this;
		}
	}

	/**
	 * Add a secondary tab.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $title Title for the secondary tab.
	 * @return void
	 */
	protected function _add_tab($title = '')
	{
		if (!empty($title))
		{
			InvoiceEM_Output::add_secondary_tab('admin.php', InvoiceEM_Constants::OPTION_SETTINGS, static::TAB_SLUG, $title);
		}
	}
}
