<?php
/*!
 * Client list object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Client List
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the client list.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_List
 */
final class InvoiceEM_Client_List extends InvoiceEM_List
{
	/**
	 * Column name for client IDs.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = InvoiceEM_Client::ID_COLUMN;

	/**
	 * Column name for client names.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = InvoiceEM_Client::TITLE_COLUMN;
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Clients::SELECT_COLUMNS;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		if (!current_user_can(InvoiceEM_Constants::CAP_EDIT_CLIENTS))
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}
		
		parent::__construct(__('Client', 'invoiceem'), __('Clients', 'invoiceem'), InvoiceEM_Constants::TABLE_CLIENTS);

		add_filter('default_hidden_columns', array($this, 'default_hidden_columns'));
	}
	
	/**
	 * Set the default hidden columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Array of default columns to hide.
	 */
	public function default_hidden_columns()
	{
		return array('phone', 'fax', InvoiceEM_Country::ID_COLUMN);
	}
	
	/**
	 * Prepare the actions column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the actions column.
	 */
	public function column_comment($item)
	{
		$output = '';
		
		if ($this->_status != InvoiceEM_Constants::STATUS_INACTIVE)
		{
			if ($this->_can_add_invoices)
			{
				$output = '<a href="' . esc_url(admin_url('admin.php?page=' . InvoiceEM_Invoices::PAGE_SLUG . '&action=' . InvoiceEM_Constants::ACTION_ADD . '&' . self::ID_COLUMN . '=' . $item[self::ID_COLUMN])) . '&' . InvoiceEM_Constants::IFRAME_NONCE . '=' . wp_create_nonce(InvoiceEM_Constants::IFRAME_NONCE) . '" class="button iem-button iem-iframe-button iem-tooltip" data-iem-tooltip="' . esc_attr__('Add Invoice', 'invoiceem') . '"><span class="dashicons dashicons-media-spreadsheet"></span></a>';
			}

			if ($this->_can_add_projects)
			{
				$output .= '<a href="' . esc_url(admin_url('admin.php?page=' . InvoiceEM_Projects::PAGE_SLUG . '&action=' . InvoiceEM_Constants::ACTION_ADD . '&' . self::ID_COLUMN . '=' . $item[self::ID_COLUMN])) . '&' . InvoiceEM_Constants::IFRAME_NONCE . '=' . wp_create_nonce(InvoiceEM_Constants::IFRAME_NONCE) . '" class="button iem-button iem-iframe-button iem-tooltip" data-iem-tooltip="' . esc_attr__('Add Project', 'invoiceem') . '"><span class="dashicons dashicons-portfolio"></span></a>';
			}
		}
		
		$output .= '<a href="javascript:;" class="button iem-button iem-add-note iem-tooltip" data-iem-object-id="' . $item[self::ID_COLUMN] . '" data-iem-tooltip="' . esc_attr__('Add Note', 'invoiceem') . '"><span class="dashicons dashicons-edit"></span></a>';
		
		if (!empty($item['website']))
		{
			$output .= '<a href="' . esc_url($item['website']) . '" target="_blank" rel="noopener noreferrer" class="button iem-button iem-tooltip" data-iem-tooltip="' . esc_attr__('Visit Website', 'invoiceem') . '"><span class="dashicons dashicons-external"></span></a>';
		}
		
		return '<div class="iem-actions">' . $output . '</div>';
	}

	/**
	 * Prepare the country column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the country column.
	 */
	public function column_country_id($item)
	{
		return
		(
			empty($item[InvoiceEM_Country::ID_COLUMN])
			||
			$item[InvoiceEM_Country::ID_COLUMN] == $this->base->settings->company->{InvoiceEM_Country::ID_COLUMN}
		)
		? '<span class="iem-default">' . InvoiceEM_Country::selected_item($this->base->settings->company->{InvoiceEM_Country::ID_COLUMN}, true) . '</span>'
		: InvoiceEM_Country::selected_item($item[InvoiceEM_Country::ID_COLUMN], true);
	}

	/**
	 * Prepare the email column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the email column.
	 */
	public function column_email($item)
	{
		return (empty($item['email']))
		? '&ndash;'
		: '<a href="mailto:' . esc_attr($item['email']) . '">' . $item['email'] . '</a>';
	}

	/**
	 * Prepare the invoice prefix column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the invoice prefix column.
	 */
	public function column_invoice_prefix($item)
	{
		return (empty($item['invoice_prefix']))
		? '<span class="iem-default">' . $this->base->settings->invoicing->prefix . '</span>'
		: $item['invoice_prefix'];
	}

	/**
	 * Prepare the rate column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the rate column.
	 */
	public function column_rate($item)
	{
		$has_different_currency =
		(
			!empty($item[InvoiceEM_Currency::ID_COLUMN])
			&&
			$item[InvoiceEM_Currency::ID_COLUMN] != $this->base->settings->company->{InvoiceEM_Currency::ID_COLUMN}
		);

		$has_different_rate =
		(
			!empty($item['rate'])
			&&
			is_numeric($item['rate'])
		);

		$rate = ($has_different_rate)
		? $item['rate']
		: $this->base->settings->company->rate;
		
		$output = InvoiceEM_Utilities::format_currency($rate, InvoiceEM_Currency::accounting_settings($item[InvoiceEM_Currency::ID_COLUMN]));

		return
		(
			$has_different_currency
			||
			$has_different_rate
		)
		? $output
		: '<span class="iem-default">' . $output . '</span>';
	}

	/**
	 * Prepare the since column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the since column.
	 */
	public function column_since($item)
	{
		return (empty($item['since']))
		? '&ndash;'
		: date_i18n($this->_date_format, strtotime($item['since']));
	}

	/**
	 * Prepare the client name column.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $item Item being processed.
	 * @return string       Value for the client name column.
	 */
	public function column_title($item)
	{
		return $this->_first_column($item, true);
	}

	/**
	 * Get the list of client columns.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Columns for the clients table.
	 */
	public function get_columns()
	{
		return array
		(
			'cb' => '<input type="checkbox" />',
			'title' => __('Name', 'invoiceem'),
			'comment' => __('Actions', 'invoiceem'),
			'invoice_prefix' => __('Invoice Prefix', 'invoiceem'),
			'rate' => __('Rate', 'invoiceem'),
			'since' => __('Since', 'invoiceem'),
			'email' => __('Email', 'invoiceem'),
			'phone' => __('Phone', 'invoiceem'),
			'fax' => __('Fax', 'invoiceem'),
			InvoiceEM_Country::ID_COLUMN => __('Country', 'invoiceem')
		);
	}

	/**
	 * Get the list of sortable client columns.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array Sortable columns for the clients table.
	 */
	protected function get_sortable_columns()
	{
		return array
		(
			'title' => array(self::TITLE_COLUMN),
			'invoice_prefix' => array('invoice_prefix'),
			'rate' => array('rate', true),
			'since' => array('since', true),
			'email' => array('email')
		);
	}

	/**
	 * Message displayed when there are no clients.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function no_items()
	{
		if ($this->_status == InvoiceEM_Constants::STATUS_INACTIVE)
		{
			_e('No inactive clients found.', 'invoiceem');
		}
		else
		{
			_e('No clients found.', 'invoiceem');
		}
	}

	/**
	 * Get the WHERE query for the current search.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return string Generated search WHERE query.
	 */
	protected function _where_search()
	{
		return InvoiceEM_Clients::where_search($this->base->cache->search_query);
	}
}
