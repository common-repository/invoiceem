<?php
/*!
 * Functionality for plugin help.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Help
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement plugin help functionality.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Help
{
	/**
	 * Output the help tabs.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $kb_path     Path to the knowledge base article associated with this help tab.
	 * @param  boolean $plugin_page True if the help tab is being added to a plugin-specific page.
	 * @return void
	 */
	public static function output($kb_path, $plugin_page = true)
	{
		$iem = InvoiceEM();

		if (!empty($kb_path))
		{
			$id = 'iem-' . $iem->cache->current_page;
			
			if ($plugin_page === true)
			{
				$iem->cache->screen->set_help_sidebar('<p><strong>' . __('Plugin developed by', 'invoiceem') . '</strong><br />'
				. '<a href="https://robertnoakes.com/" target="_blank" rel="noopener noreferrer">Robert Noakes</a></p>'
				. '<hr />'
				. '<p><a href="' . InvoiceEM_Constants::URL_SUPPORT . '" target="_blank" rel="noopener noreferrer" class="button">' . __('Plugin Support', 'invoiceem') . '</a></p>'
				. '<p><a href="' . InvoiceEM_Constants::URL_REVIEW . '" target="_blank" rel="noopener noreferrer" class="button">' . __('Review Plugin', 'invoiceem') . '</a></p>'
				. '<p><a href="' . InvoiceEM_Constants::URL_TRANSLATE . '" target="_blank" rel="noopener noreferrer" class="button">' . __('Translate Plugin', 'invoiceem') . '</a></p>'
				. '<p><a href="' . InvoiceEM_Constants::URL_DONATE . '" target="_blank" rel="noopener noreferrer" class="button">' . __('Plugin Donation', 'invoiceem') . '</a></p>');
			}
			else if ($plugin_page !== false)
			{
				$id .= $plugin_page;
			}
			
			$url = InvoiceEM_Constants::URL_KB . $kb_path . '/';
			
			$iem->cache->screen->add_help_tab(array
			(
				'id' => $id,
				'priority' => 20,
				'title' => $iem->cache->plugin_data['Name'],
				
				'content' => '<h3>' . __('For more information about this page, view the knowledge base article at:', 'invoiceem') . '<br />'
				. '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . $url . '</a></h3>'
			));
		}
	}
}
