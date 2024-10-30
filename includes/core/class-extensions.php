<?php
/*!
 * Extensions functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Extensions
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the extensions functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Extensions extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the extensions page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_extensions';

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

		add_filter('plugin_action_links_' . plugin_basename($this->base->plugin), array($this, 'plugin_action_links'), 11);
	}

	/**
	 * Add the extensions menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = __('Extensions', 'invoiceem');

		$extensions_page = add_submenu_page(InvoiceEM_Invoices::PAGE_SLUG, $this->_page_title, $this->_page_title, 'manage_options', self::PAGE_SLUG, array($this, 'extensions_page'));

		if ($extensions_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $this->_page_title);

			add_action('load-' . $extensions_page, array($this, 'load_extensions_page'));
		}
	}

	/**
	 * Output the extensions page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function extensions_page()
	{
		InvoiceEM_Output::admin_form_page($this->_page_title);
	}

	/**
	 * Load extensions page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_extensions_page()
	{
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 1000);
		add_action('admin_footer', array('InvoiceEM_Global', 'admin_footer_templates'));
		add_action(InvoiceEM_Constants::HOOK_INLINE_CONTENT, array($this, 'inline_content'));

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

		$this->_add_meta_boxes();

		InvoiceEM_Help::output('extensions');
	}
	
	/**
	 * Enqueue scripts for the extensions page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts()
	{
		InvoiceEM_Global::admin_enqueue_scripts_main();
		
		wp_localize_script
		(
			'iem-script',
			'iem_script_options',
			
			array
			(
				'action' => InvoiceEM_Constants::HOOK_LICENSE_KEY,
				'is_extensions' => 1,
				
				'strings' => array
				(
					'unexpected_error' => __('An unexpected error has occurred.', 'invoiceem')
				)
			)
		);
	}
	
	/**
	 * Add inline content to the extensions page.
	 *
	 * @since 1.0.5 Added Projects+, Reporting+ and InvoiceEM+ Bundle.
	 * @since 1.0.3 Changed bundle.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function inline_content()
	{
		$more_information_label = __('More Information', 'invoiceem');
		$coming_soon_label = __('Coming Soon', 'invoiceem');
		$invoices_plus_url = InvoiceEM_Constants::URL_EXTENSIONS . 'invoices-plus/';
		$payments_plus_url = InvoiceEM_Constants::URL_EXTENSIONS . 'payments-plus/';
		$clients_plus_url = InvoiceEM_Constants::URL_EXTENSIONS . 'clients-plus/';
		$regional_plus_url = InvoiceEM_Constants::URL_EXTENSIONS . 'regional-plus/';
		$reporting_plus_url = InvoiceEM_Constants::URL_EXTENSIONS . 'reporting-plus/';
		$essentials_bundle_url = InvoiceEM_Constants::URL_EXTENSIONS . 'essentials-bundle/';
		
		$extensions = new InvoiceEM_Field(array
		(
			'type' => 'group',
			
			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $invoices_plus_url . '" target="_blank" rel="noopener noreferrer">' . __('Invoices+', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Additional invoice functionality for InvoiceEM including additional types, PDF generation, bulk sending and more.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. apply_filters(InvoiceEM_Constants::HOOK_EXTENSION_INVOICES_PLUS, '<a href="' . $invoices_plus_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>')
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'include_clear' => true,
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $payments_plus_url . '" target="_blank" rel="noopener noreferrer">' . __('Payments+', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Provides the ability to accept online payments and allows clients to pay for multiple invoices at once.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. apply_filters(InvoiceEM_Constants::HOOK_EXTENSION_PAYMENTS_PLUS, '<a href="' . $payments_plus_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>')
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $clients_plus_url . '" target="_blank" rel="noopener noreferrer">' . __('Clients+', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Additional client functionality for InvoiceEM including multiple contacts, client access and reports.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. apply_filters(InvoiceEM_Constants::HOOK_EXTENSION_CLIENTS_PLUS, '<a href="' . $clients_plus_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>')
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'include_clear' => true,
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3>' . __('Projects+', 'invoiceem') . '</h3>'
					. '<p>' . __('Additional project functionality including expenses, time tracking and additional user roles.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. '<button class="button" disabled="disabled">' . $coming_soon_label . '</button>'
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $regional_plus_url . '" target="_blank" rel="noopener noreferrer">' . __('Regional+', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Additional administrator control over InvoiceEM countries and currencies.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. apply_filters(InvoiceEM_Constants::HOOK_EXTENSION_REGIONAL_PLUS, '<a href="' . $regional_plus_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>')
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'include_clear' => true,
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $reporting_plus_url . '" target="_blank" rel="noopener noreferrer">' . __('Reporting+', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Additional reporting functionality including yearly payment summaries and totals for invoice and payment lists.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. apply_filters(InvoiceEM_Constants::HOOK_EXTENSION_REPORTING_PLUS, '<a href="' . $reporting_plus_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>')
					. '</div>'
				)
			)
		));
		
		$bundles = new InvoiceEM_Field(array
		(
			'type' => 'group',
			
			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3>' . __('InvoiceEM+ Bundle', 'invoiceem') . '</h3>'
					. '<p>' . __('Complete collection of all InvoiceEM extensions.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. '<button class="button" disabled="disabled">' . $coming_soon_label . '</button>'
					. '</div>'
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',
					
					'content' => '<div class="iem-extension-top">'
					. '<h3><a href="' . $essentials_bundle_url . '" target="_blank" rel="noopener noreferrer">' . __('Essentials Bundle', 'invoiceem') . '</a></h3>'
					. '<p>' . __('Includes the Invoices+, Payments+, Clients+ and Reporting+ extensions.', 'invoiceem') . '</p>'
					. '</div>'
					. '<div class="iem-extension-bottom">'
					. '<a href="' . $essentials_bundle_url . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . $more_information_label . '</a>'
					. '</div>'
				)
			)
		));
		
		echo '<div id="iem-extensions">'
		. '<h3><span class="iem-text-large">' . __('Extensions', 'invoiceem') . '</span></h3>'
		. $extensions->output()
		. '<h3><span class="iem-text-large">&nbsp;<br />'
		. __('Bundles', 'invoiceem') . '</span></h3>'
		. $bundles->output()
		. '</div>';
	}

	/**
	 * Add meta boxes to the extensions page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _add_meta_boxes()
	{
		InvoiceEM_Meta_Box::side_meta_boxes();
		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}

	/**
	 * Add extentions to the plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $links Existing action links.
	 * @return array        Modified action links.
	 */
	public function plugin_action_links($links)
	{
		array_unshift($links, '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . self::PAGE_SLUG)) . '">' . __('Extensions', 'invoiceem') . '</a>');

		return $links;
	}
}
