<?php
	/*!
	 * View wrapper.
	 *
	 * @since 1.0.6 Added doctype.
	 * @since 1.0.5 Added reporting view functionality and simplified the view template hooks.
	 * @since 1.0.0
	 *
	 * @package InvoiceEM
	 */

	if (!defined('ABSPATH'))
	{
		exit;
	}
	
	$this->cache->action = InvoiceEM_Constants::ACTION_VIEW;
	$view = maybe_unserialize(InvoiceEM_Utilities::decrypt(strtr($iem_query_var, '-_.', '+/=')));
	$invoice = $client = $project = $payment = null;
	$template = $title = '';
	$show_toolbar = true;
	$is_report = false;
	
	if (is_array($view))
	{
		if (isset($view['i']))
		{
			if
			(
				is_numeric($view['i'])
				&&
				$view['i'] > 0
			)
			{
				$invoice = new InvoiceEM_Invoice($view['i'], true);
			}
			
			if (!empty($invoice))
			{
				$client = new InvoiceEM_Client($invoice->{InvoiceEM_Client::ID_COLUMN}, true);
			}
			
			if (!empty($client))
			{
				$template = dirname(__FILE__) . '/invoice.php';
				
				if (!empty($invoice->{InvoiceEM_Project::ID_COLUMN}))
				{
					$project = new InvoiceEM_Project($invoice->{InvoiceEM_Project::ID_COLUMN}, true);
				}
			}
			
			if (!empty($template))
			{
				if
				(
					isset($_GET['pdf'])
					&&
					$this->cache->has_invoices_plus
				)
				{
					do_action(InvoiceEM_Constants::HOOK_INVOICE_PDF, $template, $client, $invoice, $project);
				}
				else if
				(
					isset($_GET['payment'])
					&&
					$this->cache->has_payments_plus
				)
				{
					$template = apply_filters(InvoiceEM_Constants::HOOK_VIEW_TEMPLATE, $template, new InvoiceEM_Payment(0, true));
					$title = __('Make a Payment', 'invoiceem');
					$show_toolbar = false;
				}
				else
				{
					$title = sprintf
					(
						_x('%1$s for %2$s', 'Invoice, Client', 'invoiceem'),
						$invoice->regarding,
						$invoice->client_name
					);
				}
			}
		}
		else if
		(
			isset($view['p'])
			&&
			$this->cache->has_payments_plus
		)
		{
			if
			(
				is_numeric($view['p'])
				&&
				$view['p'] > 0
			)
			{
				$payment = new InvoiceEM_Payment($view['p'], true);
			}
			
			if (!empty($payment))
			{
				$client = new InvoiceEM_Client($payment->{InvoiceEM_Client::ID_COLUMN}, true);
			}

			if (!empty($client))
			{
				$template = apply_filters(InvoiceEM_Constants::HOOK_VIEW_TEMPLATE, '', $payment);
				
				if (!empty($template))
				{
					if
					(
						isset($_GET['pdf'])
						&&
						$this->cache->has_invoices_plus
					)
					{
						do_action(InvoiceEM_Constants::HOOK_PAYMENT_PDF, $template, $client, $payment);
					}
					else
					{
						$title = __('View Payment', 'invoiceem');
					}
				}
			}
		}
		else if
		(
			$this->cache->has_reporting_plus
			&&
			isset($view['r'])
			&&
			$view['r']
			&&
			isset($_GET['y'])
			&&
			is_numeric($_GET['y'])
			&&
			(
				current_user_can(InvoiceEM_Constants::CAP_VIEW_REPORTS)
				||
				(
					$this->cache->has_clients_plus
					&&
					current_user_can(IEM_Clients_Plus_Constants::CAP_VIEW_CLIENT)
					&&
					(
						!isset($_GET['c'])
						||
						in_array(esc_attr($_GET['c']), IEM_Clients_Plus()->cache->client_ids)
					)
				)
			)
		)
		{
			$template = apply_filters(InvoiceEM_Constants::HOOK_VIEW_TEMPLATE, '', new InvoiceEM_Payment_List(true));
			
			if (!empty($template))
			{
				$is_report = true;
				
				$client =
				(
					isset($_GET['c'])
					&&
					is_numeric($_GET['c'])
				)
				? new InvoiceEM_Client(esc_attr($_GET['c']), true)
				: '';
				
				if
				(
					isset($_GET['pdf'])
					&&
					$this->cache->has_invoices_plus
				)
				{
					do_action(InvoiceEM_Constants::HOOK_REPORTING_PDF, $template, $client);
				}
			}
		}
	}
	
	if (empty($template))
	{
		wp_die(__('You accessed this page incorrectly.', 'invoiceem'));
	}
	
	InvoiceEM_Global::admin_enqueue_scripts_main();
	
	add_action('wp_footer', array('InvoiceEM_Global', 'admin_footer_templates'));

	do_action(InvoiceEM_Constants::HOOK_VIEW_SCRIPTS);

	wp_localize_script
	(
		'iem-script',
		'iem_script_options',

		array
		(
			'is_view' => 1,
			
			'accounting' => (empty($client))
			? InvoiceEM_Currency::accounting_settings()
			: InvoiceEM_Client::accounting_settings($client->{InvoiceEM_Client::ID_COLUMN})
		)
	);
	
	$body_classes = array('iem-view');
	
	if (!$show_toolbar)
	{
		$body_classes[] = 'iem-no-toolbar';
	}
	
	if (is_user_logged_in())
	{
		$body_classes[] = 'admin-color-' . get_user_meta(get_current_user_id(), 'admin_color', true);
	}
?>

<!DOCTYPE html>

<html lang="en-US" prefix="og: http://ogp.me/ns#">

	<head>
	
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="HandheldFriendly" content="true" />
		<meta name="robots" content="noindex,nofollow" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
		
		<title><?php echo $title; ?></title>
		
		<?php wp_print_styles('iem-style'); ?>
		
	</head>
	
	<body class="<?php echo esc_attr(implode(' ', $body_classes)); ?>">
	
		<?php if ($show_toolbar) : ?>
		
			<div class="iem-toolbar">

				<?php
					echo '<a href="javascript:;" onClick="window.print();">Print</a>';

					if ($this->cache->has_invoices_plus)
					{
						if (!empty($invoice))
						{
							do_action(InvoiceEM_Constants::HOOK_INVOICE_TOOLBAR);
						}
						else if
						(
							$this->cache->has_payments_plus
							&&
							!empty($payment)
						)
						{
							do_action(InvoiceEM_Constants::HOOK_PAYMENT_TOOLBAR);
						}
						else if
						(
							$this->cache->has_reporting_plus
							&&
							$is_report
						)
						{
							do_action(InvoiceEM_Constants::HOOK_REPORTING_TOOLBAR);
						}
					}
				?>

			</div>
			
		<?php endif; ?>
		
		<div class="iem-sheet">
		
			<div class="iem-content">
				
				<?php
					ob_start();

					require($template);

					echo InvoiceEM_Utilities::clean_code(ob_get_clean());
				?>
				
			</div>
			
		</div>
		
		<div class="iem-wp-footer">
		
			<?php wp_footer(); ?>
			
		</div>
		
	</body>
	
</html>
