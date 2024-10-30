<?php
/*!
 * Reporting functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Reporting
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the reporting functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Reporting extends InvoiceEM_Wrapper
{
	/**
	 * Page slug for the reporting page.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PAGE_SLUG = InvoiceEM_Constants::TOKEN . '_reporting';
	
	/**
	 * ID for the first reported currency.
	 *
	 * @since 1.0.0
	 *
	 * @var integer
	 */
	private $_first_currency;

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
	}

	/**
	 * Add the reporting menu item.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu()
	{
		$this->_page_title = __('Reporting', 'invoiceem');

		$reporting_page = add_submenu_page
		(
			InvoiceEM_Invoices::PAGE_SLUG,
			$this->_page_title,
			$this->_page_title,
			
			($this->base->cache->has_clients_plus)
			? apply_filters(InvoiceEM_Constants::HOOK_VIEW, InvoiceEM_Constants::CAP_VIEW_REPORTS)
			: InvoiceEM_Constants::CAP_VIEW_REPORTS,
			
			self::PAGE_SLUG,
			array($this, 'reporting_page')
		);

		if ($reporting_page)
		{
			InvoiceEM_Output::add_tab('admin.php', self::PAGE_SLUG, $this->_page_title);

			add_action('load-' . $reporting_page, array($this, 'load_reporting_page'));
		}
	}

	/**
	 * Output the reporting page.
	 *
	 * @since 1.0.5 Added the plus notice.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function reporting_page()
	{
		if (!$this->base->cache->has_reporting_plus)
		{
			$link_open = '<a href="' . esc_url(InvoiceEM_Constants::URL_EXTENSIONS . 'reporting-plus/') . '" target="_blank" rel="noopener noreferrer">';
			$link_close = '</a>';
			
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s provides additional reporting functionality like year-end, client payments and table totals. %2$s', 'invoiceem'),
					$link_open . __('InvoiceEM Reporting+', 'invoiceem') . $link_close,
					$link_open . __('More information &raquo;', 'invoiceem') . $link_close
				),

				InvoiceEM_Constants::NOTICE_INFO,
				false
			);
		}
		
		InvoiceEM_Output::admin_form_page($this->_page_title);
	}

	/**
	 * Load reporting page functionality.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_reporting_page()
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

		$this->_add_meta_boxes();

		InvoiceEM_Help::output('reporting');
	}

	/**
	 * Enqueue the reporting assets.
	 *
	 * @since 1.0.6 Changed Chart.js asset path.
	 * @since 1.0.5 Added assets hook.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts()
	{
		InvoiceEM_Global::admin_enqueue_scripts_form();
		
		wp_dequeue_style('chartjs');
		wp_dequeue_script('chartjs');
		
		$chartjs_path = plugins_url('/assets/vendor/Chart.js/', $this->base->plugin);
		
		if ($this->base->cache->script_debug)
		{
			wp_enqueue_style('chartjs', $chartjs_path . 'Chart.css', array(), '2.9.3');
			wp_enqueue_script('chartjs', $chartjs_path . 'Chart.bundle.js', array(), '2.9.3', true);
		}
		else
		{
			wp_enqueue_style('chartjs', $chartjs_path . 'Chart.min.css', array(), '2.9.3');
			wp_enqueue_script('chartjs', $chartjs_path . 'Chart.bundle.min.js', array(), '2.9.3', true);
		}
		
		wp_enqueue_script('iem-reporting-script', $this->base->cache->asset_path('scripts', 'reporting.js'), array('iem-script', 'chartjs'), InvoiceEM_Constants::VERSION, true);
		wp_localize_script('iem-reporting-script', 'iem_reporting_options', $this->output());
		
		do_action(InvoiceEM_Constants::HOOK_REPORTING_ASSETS);
	}
	
	/**
	 * Generate the report data.
	 *
	 * @since 1.0.6 Cleaned up database call.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function output()
	{
		global $wpdb;
		
		$currency_id =
		(
			!isset($_POST[InvoiceEM_Currency::ID_COLUMN])
			||
			!is_numeric($_POST[InvoiceEM_Currency::ID_COLUMN])
			||
			$_POST[InvoiceEM_Currency::ID_COLUMN] <= 0
		)
		? $this->_first_currency
		: esc_attr($_POST[InvoiceEM_Currency::ID_COLUMN]);
		
		$time_period = (isset($_POST['time_period']))
		? esc_attr($_POST['time_period'])
		: '';
		
		$now = $gte_date = $lt_date = time();
		$year_starts_day = '01';
		$year_starts = '01-' . $year_starts_day;
		$month_starts = date('m') . '-' . $year_starts_day;
		
		if (!empty($this->base->settings->company->year_starts))
		{
			$year_starts_raw = strtotime($this->base->settings->company->year_starts);
			$year_starts_day = date('d', $year_starts_raw);
			
			if ($year_starts_day > 28)
			{
				$year_starts_day = 28;
			}
			
			$year_starts = date('m', $year_starts_raw) . '-' . $year_starts_day;
			$month_starts = date('m') . '-' . $year_starts_day;
		}
		
		$year_start_date = strtotime(date('Y') . '-' . $year_starts . ' 00:00:00');
		$month_start_date = strtotime(date('Y') . '-' . $month_starts . ' 00:00:00');
		
		if ($year_start_date > $now)
		{
			$year_start_date = strtotime('-1 year', $year_start_date);
		}
		
		if ($month_start_date > $now)
		{
			$month_start_date = strtotime('-1 month', $month_start_date);
		}
		
		switch ($time_period)
		{
			case 'tw':
			
				$gte_date = strtotime('-1 week');
				
			break;
			
			case 'lw':
			
				$gte_date = strtotime('-2 weeks');
				$lt_date = strtotime('-1 week');
				
			break;
			
			case 'mtd':
			
				$gte_date = $month_start_date;
				
			break;
			
			case 'lfm':
			
				$gte_date = strtotime('-1 month', $month_start_date);
				$lt_date = $month_start_date;
				
			break;
			
			case 'lm':
			
				$gte_date = strtotime('-2 months');
				$lt_date = strtotime('-1 month');
				
			break;
			
			case 'ytd':
			
				$gte_date = $year_start_date;
				
			break;
			
			case 'lfy':
			
				$gte_date = strtotime('-1 year', $year_start_date);
				$lt_date = $year_start_date;
				
			break;
			
			case 'ty':
			
				$gte_date = strtotime('-1 year');
				
			break;
			
			case 'ly':
			
				$gte_date = strtotime('-2 years');
				$lt_date = strtotime('-1 year');
				
			break;
			
			case 'at':
			
				$gte_date = $lt_date = '';
				
			break;
			
			default:
			
				$gte_date = strtotime('-1 month');
				
			break;
		}
		
		$invoices_index = 'invoices';
		$payments_index = 'payments';
		
		$data = array
		(
			$invoices_index => array
			(
				'paid' => array
				(
					'amount' => 0,
					'background' => '#46b450',
					'count' => 0,
					'label' => __('Paid', 'invoiceem')
				),
				
				'unpaid' => array
				(
					'amount' => 0,
					'background' => '#ffb900',
					'count' => 0,
					'label' => __('Unpaid', 'invoiceem')
				),
				
				'overdue' => array
				(
					'amount' => 0,
					'background' => '#dc3232',
					'count' => 0,
					'label' => __('Overdue', 'invoiceem')
				)
			),
			
			$payments_index => array
			(
				'completed' => array
				(
					'amount' => 0,
					'background' => '#46b450',
					'count' => 0,
					'label' => __('Completed', 'invoiceem')
				),
				
				'pending' => array
				(
					'amount' => 0,
					'background' => '#ffb900',
					'count' => 0,
					'label' => __('Pending', 'invoiceem')
				),
				
				'failed' => array
				(
					'amount' => 0,
					'background' => '#dc3232',
					'count' => 0,
					'label' => __('Failed', 'invoiceem')
				)
			)
		);
		
		$clients_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS);
		$table_placeholder = "[__t__]";
		$invoices_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_INVOICES);
		$projects_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PROJECTS);
		
		$join = $wpdb->prepare
		(
			"INNER JOIN " . $clients_table . " ON " . $table_placeholder . "." . InvoiceEM_Client::ID_COLUMN . " = " . $clients_table . "." . InvoiceEM_Client::ID_COLUMN . " AND " . $clients_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1 AND " . $clients_table . "." . InvoiceEM_Currency::ID_COLUMN . " = %d",
			$currency_id
		)
		. apply_filters(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, " AND ", $clients_table);
		
		$invoices_where = (empty($gte_date))
		? ""
		: " AND " . $invoices_table . ".send_date >= " . $gte_date . " AND " . $invoices_table . ".send_date < " . $lt_date;
		
		$invoices = $wpdb->get_results("SELECT " . $invoices_table . ".total, " . $invoices_table . ".payment_due, " . $invoices_table . ".paid FROM " . $invoices_table . " " . str_replace($table_placeholder, $invoices_table, $join) . " LEFT JOIN " . $projects_table . " ON " . $invoices_table . "." . InvoiceEM_Project::ID_COLUMN . " = " . $projects_table . "." . InvoiceEM_Project::ID_COLUMN . " AND " . $projects_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1 WHERE " . $invoices_table . ".send_date IS NOT NULL AND " . $invoices_table . ".send_date > 0 AND " . $invoices_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1" . $invoices_where, ARRAY_A);
		
		foreach ($invoices as $invoice)
		{
			if ($invoice['paid'] >= $invoice['total'])
			{
				$data['invoices']['paid']['count']++;
				$data['invoices']['paid']['amount'] += $invoice['paid'];
			}
			else if ($invoice['payment_due'] <= $now)
			{
				$data['invoices']['overdue']['count']++;
				$data['invoices']['overdue']['amount'] += $invoice['total'] - $invoice['paid'];
				$data['invoices']['paid']['amount'] += $invoice['paid'];
			}
			else
			{
				$data['invoices']['unpaid']['count']++;
				$data['invoices']['unpaid']['amount'] += $invoice['total'] - $invoice['paid'];
				$data['invoices']['paid']['amount'] += $invoice['paid'];
			}
		}
		
		$payments_table = InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_PAYMENTS);
		
		$payments_where = (empty($gte_date))
		? ""
		: " AND " . $payments_table . ".payment_date >= " . $gte_date . " AND " . $payments_table . ".payment_date < " . $lt_date;
		
		$payments = $wpdb->get_results("SELECT SUM(" . $payments_table . ".amount + COALESCE(" . $payments_table . ".bonus, 0) + COALESCE(" . $payments_table . ".fee, 0)) AS total, " . $payments_table . ".is_failed, " . $payments_table . ".is_completed FROM " . $payments_table . " " . str_replace($table_placeholder, $payments_table, $join) . " WHERE " . $payments_table . "." . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " = 1" . $payments_where . " GROUP BY " . $payments_table . "." . InvoiceEM_Payment::ID_COLUMN, ARRAY_A);
		
		foreach ($payments as $payment)
		{
			if
			(
				$payment['is_failed'] == 0
				&&
				$payment['is_completed'] == 0
			)
			{
				$data['payments']['pending']['count']++;
				$data['payments']['pending']['amount'] += $payment['total'];
			}
			else if ($payment['is_failed'] != 0)
			{
				$data['payments']['failed']['count']++;
				$data['payments']['failed']['amount'] += $payment['total'];
			}
			else
			{
				$data['payments']['completed']['count']++;
				$data['payments']['completed']['amount'] += $payment['total'];
			}
		}
		
		$datasets = array
		(
			array
			(
				'backgroundColor' => array(),
				'data' => array()
			),
			
			array
			(
				'backgroundColor' => array(),
				'data' => array()
			)
		);
		
		$output = array
		(
			'accounting' => InvoiceEM_Currency::accounting_settings($currency_id),
			'no_data' => __('No chart data to display.', 'invoiceem'),
			
			$invoices_index => array
			(
				'datasets' => $datasets,
				'labels' => array()
			),
			
			$payments_index => array
			(
				'datasets' => $datasets,
				'labels' => array()
			)
		);
		
		foreach ($data as $index => $types)
		{
			$is_empty = true;
			
			foreach ($types as $type)
			{
				if
				(
					$type['amount'] > 0
					||
					$type['count'] > 0
				)
				{
					$is_empty = false;
					$output[$index]['datasets'][0]['backgroundColor'][] = $type['background'];
					$output[$index]['datasets'][0]['data'][] = $type['count'];
					$output[$index]['datasets'][1]['backgroundColor'][] = $type['background'];
					$output[$index]['datasets'][1]['data'][] = $type['amount'];
					$output[$index]['labels'][] = $type['label'];
				}
			}
			
			if ($is_empty)
			{
				unset($output[$index]['datasets'][0]);
				unset($output[$index]['datasets'][1]);
			}
		}
		
		return $output;
	}

	/**
	 * Add meta boxes to the reporting page.
	 *
	 * @since 1.0.5 Changed meta box name and title.
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _add_meta_boxes()
	{
		if ($this->base->cache->has_reporting_plus)
		{
			do_action(InvoiceEM_Constants::HOOK_REPORTING_META_BOXES);
		}
		
		$chart_options_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'chart_options',
			'title' => __('Chart Options', 'invoiceem')
		));
		
		$currencies = $this->_currencies();
		
		if (count($currencies) > 1)
		{
			$chart_options_box->add_field(array
			(
				'description' => __('Currency for the reports to display.', 'invoiceem'),
				'input_classes' => array('iem-ignore-change'),
				'label' => __('Currency', 'invoiceem'),
				'name' => InvoiceEM_Currency::ID_COLUMN,
				'options' => $currencies,
				'type' => 'select',
				'value' => $this->_first_currency
			));
		}
		else
		{
			$chart_options_box->add_field(array
			(
				'classes' => array('iem-hidden'),
				'name' => InvoiceEM_Currency::ID_COLUMN,
				'type' => 'hidden',
				'value' => $this->_first_currency
			));
		}
		
		$chart_options_box->add_field(array
		(
			'description' => __('Time period to display reports for.', 'invoiceem'),
			'input_classes' => array('iem-ignore-change'),
			'label' => __('Time Period', 'invoiceem'),
			'name' => 'time_period',
			'type' => 'select',
			'value' => 'tm',
			
			'options' => array
			(
				'tw' => __('This Week (Past 7 Days)', 'invoiceem'),
				'lw' => __('Last Week', 'invoiceem'),
				'mtd' => __('Fiscal Month to Date', 'invoiceem'),
				'lfm' => __('Last Fiscal Month', 'invoiceem'),
				'tm' => __('This Month (Past 30 Days)', 'invoiceem'),
				'lm' => __('Last Month', 'invoiceem'),
				'ytd' => __('Fiscal Year to Date', 'invoiceem'),
				'lfy' => __('Last Fiscal Year', 'invoiceem'),
				'ty' => __('This Year (Past 365 Days)', 'invoiceem'),
				'ly' => __('Last Year', 'invoiceem'),
				'at' => __('All Time', 'invoiceem')
			)
		));
		
		$charts_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'charts',
			'title' => __('Charts', 'invoiceem')
		));
		
		$charts_box->add_field(array
		(
			'type' => 'group',
			
			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-sm-6', 'iem-col-xs-12'),
					'type' => 'html',
					
					'content' => '<p class="iem-text-center"><strong>' . __('Invoices', 'invoiceem') . '</strong></p>'
					. '<div><canvas id="iem-invoices-chart"></canvas></div>'
				),
				
				array
				(
					'classes' => array('iem-col-sm-6', 'iem-col-xs-12'),
					'type' => 'html',
					
					'content' => '<p class="iem-text-center"><strong>' . __('Payments', 'invoiceem') . '</strong></p>'
					. '<div><canvas id="iem-payments-chart"></canvas></div>'
				)
			)
		));

		InvoiceEM_Meta_Box::side_meta_boxes();
		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}
	
	/**
	 * Get all of the currencies used by the company.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return array All currencies used by the countries, formatted for a SELECT box.
	 */
	private function _currencies()
	{
		global $wpdb;
		
		$output = array();
		$results = $wpdb->get_results("SELECT " . InvoiceEM_Currency::ID_COLUMN . ", " . InvoiceEM_Currency::TITLE_COLUMN . ", symbol, " . InvoiceEM_Constants::COLUMN_IS_ACTIVE . " FROM " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CURRENCIES) . " WHERE " . InvoiceEM_Currency::ID_COLUMN . " IN (SELECT DISTINCT " . InvoiceEM_Currency::ID_COLUMN . " FROM " . InvoiceEM_Database::get_table_name(InvoiceEM_Constants::TABLE_CLIENTS) . apply_filters(InvoiceEM_Constants::HOOK_CLIENT_LIMIT, " WHERE ") . ") ORDER BY " . InvoiceEM_Currency::TITLE_COLUMN . " ASC", ARRAY_A);
		
		if (!empty($results))
		{
			foreach ($results as $row)
			{
				if (empty($this->_first_currency))
				{
					$this->_first_currency = ($this->base->cache->is_client)
					? $row[InvoiceEM_Currency::ID_COLUMN]
					: $this->base->settings->company->{InvoiceEM_Currency::ID_COLUMN};
				}
				
				$output[$row[InvoiceEM_Currency::ID_COLUMN]] = InvoiceEM_Currency::generate_label($row);
			}
		}
		
		return $output;
	}
}
