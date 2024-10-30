<?php
/*!
 * Global plugin hooks.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Global
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement global hooks.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Global
{
	/**
	 * Add additional classes to the admin BODY tag.
	 * 
	 * @since 1.1.0
	 * 
	 * @access public static
	 * @param  string $classes Existing classes.
	 * @return string          Modified classes.
	 */
	public static function admin_body_class($classes)
	{
		global $wp_version;
		
		if (version_compare($wp_version, '5.3-dev', '>='))
		{
			$classes .= ' iem-wp-5-3';
		}

		return $classes;
	}
	
	/**
	 * Default scripts for form pages.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function admin_enqueue_scripts_form()
	{
		self::admin_enqueue_scripts_main();
		
		$iem = InvoiceEM();

		wp_localize_script
		(
			'iem-script',
			'iem_script_options',
			
			apply_filters
			(
				InvoiceEM_Constants::HOOK_FORM_OPTIONS,

				array
				(
					'accounting' => $iem->cache->accounting,
					'date_format' => str_replace(array('d', 'j', 'l', 'z', 'F', 'm', 'n', 'Y', 'c', 'r'), array('dd', 'd', 'DD', 'o', 'MM', 'mm', 'm', 'yy', 'ISO_8601', 'RFC_2822'), get_option('date_format')),
					'has_form' => true,
					'new_url' => $iem->cache->new_url,
					
					'strings' => array_merge
					(
						$iem->cache->dialog_strings,
						
						array
						(
							'save_alert' => __('The changes you made will be lost if you navigate away from this page.', 'invoiceem'),
							'unique_message' => __('Please enter unique values.', 'invoiceem')
						)
					)
				)
			)
		);
		
		wp_localize_script('iem-script', 'iem_script_validation', $iem->cache->form_validation);
	}

	/**
	 * Default scripts for list pages.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function admin_enqueue_scripts_list()
	{
		self::admin_enqueue_scripts_main();
		
		$iem = InvoiceEM();

		wp_localize_script
		(
			'iem-script',
			'iem_script_options',
			
			array
			(
				'date_format' => str_replace(array('d', 'j', 'l', 'z', 'F', 'm', 'n', 'Y', 'c', 'r'), array('dd', 'd', 'DD', 'o', 'MM', 'mm', 'm', 'yy', 'ISO_8601', 'RFC_2822'), get_option('date_format')),
				'has_list' => true,
				'new_url' => $iem->cache->list_url,
				
				'strings' => array_merge
				(
					$iem->cache->dialog_strings,

					array
					(
						'save_alert' => __('The changes you made will be lost if you navigate away from this page.', 'invoiceem'),
						'unexpected_error' => __('An unexpected error has occurred.', 'invoiceem'),
						'unique_message' => __('Please enter unique values.', 'invoiceem')
					)
				)
			)
		);
	}
	
	/**
	 * Enqueue main plugin scripts and styles.
	 *
	 * @since 1.0.6 Changed vendor asset paths.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function admin_enqueue_scripts_main()
	{
		wp_deregister_script('accountingjs');
		wp_deregister_style('jquery-ui');
		wp_deregister_script('jquery-validation');
		wp_deregister_style('select2');
		wp_deregister_script('select2');
		
		wp_dequeue_script('accountingjs');
		wp_dequeue_style('jquery-ui');
		wp_dequeue_script('jquery-validation');
		wp_dequeue_style('select2');
		wp_dequeue_script('select2');
		
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-spinner');
		wp_enqueue_script('jquery-ui-tooltip');
		wp_enqueue_script('postbox');
		wp_enqueue_script('wp-util');
		
		$iem = InvoiceEM();
		$full_vendor_path = plugins_url('/assets/vendor/', $iem->plugin);
		
		$asset_suffix = ($iem->cache->script_debug)
		? ''
		: '.min';
		
		wp_enqueue_script('accountingjs', $full_vendor_path . 'accounting.js/accounting' . $asset_suffix . '.js', array(), '0.4.1', true);
		wp_enqueue_style('jquery-ui', $full_vendor_path . 'jquery-ui/themes/smoothness/jquery-ui' . $asset_suffix . '.css', array(), '1.11.4');
		wp_enqueue_script('jquery-validation', $full_vendor_path . 'jquery-validation/jquery.validate' . $asset_suffix . '.js', array(), '1.19.1', true);
		wp_enqueue_style('select2', $full_vendor_path . 'select2/css/select2' . $asset_suffix . '.css', array(), '4.0.12');
		wp_enqueue_script('select2', $full_vendor_path . 'select2/js/select2.full' . $asset_suffix . '.js', array(), '4.0.12', true);
		
		$home_url = home_url();
		$vendor_path = str_replace($home_url, '', $full_vendor_path);
		$locale = get_locale();
		$locale_split = explode('_', $locale);
		$locale_hyphen = implode('-', $locale_split);
		
		$jquery_ui_path = $vendor_path . 'jquery-ui/ui/i18n/datepicker-';
		$jquery_ui_file = $jquery_ui_path . $locale_hyphen . '.js';
		$jquery_ui_file_simple = $jquery_ui_path . $locale_split[0] . '.js';

		if (file_exists(ABSPATH . $jquery_ui_file))
		{
			wp_enqueue_script('jquery-ui-i18n', $home_url . $jquery_ui_file, array(), '1.11.4', true);
		}
		else if (file_exists(ABSPATH . $jquery_ui_file_simple))
		{
			wp_enqueue_script('jquery-ui-i18n', $home_url . $jquery_ui_file_simple, array(), '1.11.4', true);
		}
		
		$jquery_validation_path = $vendor_path . 'jquery-validation/localization/';
		$jquery_validation_messages_file = $jquery_validation_path . 'messages_' . $locale . '.min.js';
		$jquery_validation_messages_file_simple = $jquery_validation_path . 'messages_' . $locale_split[0] . '.min.js';

		if (file_exists(ABSPATH . $jquery_validation_messages_file))
		{
			wp_enqueue_script('jquery-validation-localization-messages', $home_url . $jquery_validation_messages_file, array(), '1.19.1', true);
		}
		else if (file_exists(ABSPATH . $jquery_validation_messages_file_simple))
		{
			wp_enqueue_script('jquery-validation-localization-messages', $home_url . $jquery_validation_messages_file_simple, array(), '1.19.1', true);
		}

		$jquery_validation_methods_file = $jquery_validation_path . 'methods_' . $locale . '.min.js';
		$jquery_validation_methods_file_simple = $jquery_validation_path . 'methods_' . $locale_split[0] . '.min.js';

		if (file_exists(ABSPATH . $jquery_validation_methods_file))
		{
			wp_enqueue_script('jquery-validation-localization-methods', $home_url . $jquery_validation_methods_file, array(), '1.19.1', true);
		}
		else if (file_exists(ABSPATH . $jquery_validation_methods_file_simple))
		{
			wp_enqueue_script('jquery-validation-localization-methods', $home_url . $jquery_validation_methods_file_simple, array(), '1.19.1', true);
		}
		
		wp_enqueue_style('iem-style', $iem->cache->asset_path('styles', 'style.css'), array(), InvoiceEM_Constants::VERSION);
		wp_enqueue_script('iem-script', $iem->cache->asset_path('scripts', 'script.js'), array(), InvoiceEM_Constants::VERSION, true);
	}

	/**
	 * Add jQuery that changes the active menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function admin_footer_next_active()
	{
		echo '<script>'
		. 'jQuery("#toplevel_page_' . InvoiceEM_Invoices::PAGE_SLUG . '").find("li.current").removeClass("current").children("a").removeAttr("aria-current").removeClass("current").end().next("li").addClass("current").children("a").attr("aria-current", "page").addClass("current");'
		. '</script>';
	}

	/**
	 * Include the HTML templates in the admin footer.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function admin_footer_templates()
	{
		ob_start();

		$templates_path = dirname(__FILE__) . '/../templates/';

		require($templates_path . 'add-note.php');
		require($templates_path . 'iframe-wrapper.php');
		require($templates_path . 'repeatable-buttons.php');

		echo InvoiceEM_Utilities::clean_code(ob_get_clean());
	}
}
