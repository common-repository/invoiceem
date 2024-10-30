<?php
/*!
 * Base plugin functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Base
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the base plugin functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM extends InvoiceEM_Wrapper
{
	/**
	 * Main instance of InvoiceEM.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    InvoiceEM
	 */
	private static $_instance = null;

	/**
	 * Returns the main instance of InvoiceEM.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string    $file Main plugin file.
	 * @return InvoiceEM       Main InvoiceEM instance. 
	 */
	public static function _get_instance($file)
	{
		if (is_null(self::$_instance))
		{
			self::$_instance = new self($file);
		}

		return self::$_instance;
	}

	/**
	 * File path for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    string
	 */
	public $plugin;

	/**
	 * Global cache object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Cache
	 */
	public $cache;

	/**
	 * Global invoices object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Invoices
	 */
	public $invoices;

	/**
	 * Global payments object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Payments
	 */
	public $payments;

	/**
	 * Global clients object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Clients
	 */
	public $clients;

	/**
	 * Global projects object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Projects
	 */
	public $projects;

	/**
	 * Global countries object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Countries
	 */
	public $countries;

	/**
	 * Global currencies object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Currencies
	 */
	public $currencies;

	/**
	 * Global reporting object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Reporting
	 */
	public $reporting;

	/**
	 * Global settings object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Settings
	 */
	public $settings;

	/**
	 * Global extensions object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Extensions
	 */
	public $extensions;

	/**
	 * Global AJAX object.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    InvoiceEM_Ajax
	 */
	public $ajax;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $file Main plugin file.
	 * @return void
	 */
	public function __construct($file)
	{
		if
		(
			!empty($file)
			&&
			file_exists($file)
		)
		{
			$this->plugin = $file;

			add_action('plugins_loaded', array($this, 'plugins_loaded'));
		}
	}

	/**
	 * Load the plugin functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function plugins_loaded()
	{
		$this->cache = new InvoiceEM_Cache();

		$this->_iframe_check();

		$this->invoices = new InvoiceEM_Invoices();
		$this->payments = new InvoiceEM_Payments();
		$this->clients = new InvoiceEM_Clients();
		$this->projects = new InvoiceEM_Projects();
		
		if ($this->cache->has_regional_plus)
		{
			$this->countries = new IEM_Regional_Plus_Countries();
			$this->currencies = new IEM_Regional_Plus_Currencies();
		}
		else
		{
			$this->countries = new InvoiceEM_Countries();
			$this->currencies = new InvoiceEM_Currencies();
		}
		
		$this->reporting = new InvoiceEM_Reporting();
		$this->settings = new InvoiceEM_Settings();
		$this->extensions = new InvoiceEM_Extensions();

		if
		(
			defined('DOING_AJAX')
			&&
			DOING_AJAX
		)
		{
			$this->ajax = new InvoiceEM_Ajax();
		}

		add_action('admin_init', array('InvoiceEM_Setup', 'check_version'), 0);
		add_action('init', array($this, 'init'), 1000);
		add_action('load-user-edit.php', array($this, 'load_users'));
		add_action('load-user-new.php', array($this, 'load_users'));
		add_action('load-users.php', array($this, 'load_users'));
		add_action(InvoiceEM_Constants::HOOK_VIEW_LOAD, array($this, 'view_load'));
		
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
		add_filter('query_vars', array($this, 'query_vars'));
		add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
		add_filter('template_redirect', array($this, 'template_redirect'));
		
		if (!$this->cache->has_clients_plus)
		{
			add_filter(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, '__return_empty_string');
		}

		do_action(InvoiceEM_Constants::HOOK_LOADED);
	}

	/**
	 * Check to see if the page is loaded in an IFRAME and if so, make sure it was done correctly.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _iframe_check()
	{
		$iframe_nonce = '';

		if
		(
			$this->cache->is_post
			&&
			isset($_POST[InvoiceEM_Constants::IFRAME_NONCE])
		)
		{
			$iframe_nonce = esc_attr($_POST[InvoiceEM_Constants::IFRAME_NONCE]);
		}
		else if (isset($_GET[InvoiceEM_Constants::IFRAME_NONCE]))
		{
			$iframe_nonce = esc_attr($_GET[InvoiceEM_Constants::IFRAME_NONCE]);
		}

		if (!empty($iframe_nonce))
		{
			if (wp_verify_nonce($iframe_nonce, InvoiceEM_Constants::IFRAME_NONCE))
			{
				define('IEM_IFRAME', true);

				add_filter('admin_body_class', array($this, 'admin_body_class'));
			}
			else
			{
				wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
			}
		}
	}

	/**
	 * If loaded in an IFRAME add a BODY class to modify page styles.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_body_class($classes)
	{
		return $classes . ' iem-iframe';
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{
		load_plugin_textdomain('invoiceem', false, dirname(plugin_basename($this->plugin)) . '/languages/');
		
		if
		(
			$this->cache->current_page == InvoiceEM_Constants::OPTION_SETTINGS
			&&
			is_a($this->settings->active_object, 'InvoiceEM_Settings_Invoicing')
			&&
			isset($_GET['settings-updated'])
		)
		{
			flush_rewrite_rules(false);
		}
		
		$rewrite_base = $this->settings->invoicing->rewrite_base;
		
		add_rewrite_rule('^' . $rewrite_base . '/([^/]*)/?', 'index.php?' . $rewrite_base . '=$matches[1]', 'top');
	}
	
	/**
	 * Add a help tabs to the users pages.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_users()
	{
		InvoiceEM_Help::output('users', 'users');
	}
	
	/**
	 * Functionality fired when a view is loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function view_load()
	{
		add_filter('qm/dispatch/html', '__return_false', PHP_INT_MAX);
		add_filter('show_admin_bar', '__return_false', PHP_INT_MAX);
	}

	/**
	 * Add links to the plugin page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $links Default links for the plugin.
	 * @param  string $file  Main plugin file name.
	 * @return array         Modified links for the plugin.
	 */
	public function plugin_row_meta($links, $file)
	{
		if ($file == plugin_basename($this->plugin))
		{
			$links[] = '<a href="' . InvoiceEM_Constants::URL_SUPPORT . '" target="_blank" rel="noopener noreferrer">' . __('Support', 'invoiceem') . '</a>';
			$links[] = '<a href="' . InvoiceEM_Constants::URL_REVIEW . '" target="_blank" rel="noopener noreferrer">' . __('Review', 'invoiceem') . '</a>';
			$links[] = '<a href="' . InvoiceEM_Constants::URL_TRANSLATE . '" target="_blank" rel="noopener noreferrer">' . __('Translate', 'invoiceem') . '</a>';
			$links[] = '<a href="' . InvoiceEM_Constants::URL_DONATE . '" target="_blank" rel="noopener noreferrer">' . __('Donate', 'invoiceem') . '</a>';
		}

		return $links;
	}
	
	/**
	 * Add the plugin query vars.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $vars Current query vars for the plugin.
	 * @return array       Modified query vars for the plugin.
	 */
	public function query_vars($vars)
	{
		$vars[] = $this->settings->invoicing->rewrite_base;
		
		return $vars;
	}

	/**
	 * Set the screen option.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $keep   True if the screen option value should be saved.
	 * @param  string  $option Name of the screen option.
	 * @param  string  $value  Value of the screen option.
	 * @return string          Value of the screen option.
	 */
	public function set_screen_option($keep, $option, $value)
	{
		if
		(
			InvoiceEM_Utilities::starts_with(InvoiceEM_Constants::PREFIX, $option)
			&&
			InvoiceEM_Utilities::ends_with(InvoiceEM_Constants::SETTING_PER_PAGE, $option)
		)
		{
			return (is_numeric($value))
			? $value
			: 20;
		}
		
		return $value;
	}
	
	/**
	 * Output an invoice if one was requested.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function template_redirect()
	{
		$rewrite_base = $this->settings->invoicing->rewrite_base;
		
		if
		(
			is_404()
			&&
			strpos($_SERVER['REQUEST_URI'], '/' . $rewrite_base . '/') !== false
			&&
			!isset($_GET['iem-flush'])
		)
		{
			flush_rewrite_rules(false);
			wp_redirect(add_query_arg('iem-flush'));
			
			exit;
		}
		
		$iem_query_var = get_query_var($rewrite_base);
		
		if ($iem_query_var != '')
		{
			do_action(InvoiceEM_Constants::HOOK_VIEW_LOAD);
			
			require(dirname(__FILE__) . '/../templates/view.php');
			
			exit;
		}
	}
}
