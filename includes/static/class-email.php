<?php
/*!
 * Plugin email functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Email
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the email functionality.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Email
{
	/**
	 * Invoice attached to the email
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @var    array
	 */
	public static $attach_invoices = array();
	
	/**
	 * Current client being processed.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    InvoiceEM_Client
	 */
	private static $_client;
	
	/**
	 * Current invoice being processed.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    InvoiceEM_Invoice
	 */
	private static $_invoice;
	
	/**
	 * Current payment being processed.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    InvoiceEM_Payment
	 */
	private static $_payment;
	
	/**
	 * Set the text body.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  mixed   $object_or_client_id  Main object used to populate the email or a client ID.
	 * @param  string  $subject              Raw email subject line.
	 * @param  string  $title                Raw title displayed at the top of the email.
	 * @param  string  $body                 Raw email body.
	 * @return boolean                       True if the email was sent successfully.
	 */
	public static function send($object_or_client_id, $subject, $title, $body)
	{
		$iem = InvoiceEM();
		$email_to = array();
		
		InvoiceEM_Invoice::disable_filters();
		
		if ($object_or_client_id === false)
		{
			$admins = get_users(array
			(
				'role__in' => array('administrator', InvoiceEM_Constants::ROLE_ACCOUNT_MANAGER)
			));
			
			foreach ($admins as $admin)
			{
				$email_to[] = $admin->user_nicename . ' <' . $admin->user_email . '>';
			}
		}
		else if
		(
			is_numeric($object_or_client_id)
			&&
			$object_or_client_id > 0
		)
		{
			self::$_client = new InvoiceEM_Client($object_or_client_id, true);
		}
		else if
		(
			is_a($object_or_client_id, 'InvoiceEM_Invoice')
			&&
			!empty($object_or_client_id->{InvoiceEM_Invoice::ID_COLUMN})
		)
		{
			self::$attach_invoices[] = self::$_invoice = $object_or_client_id;
			self::$_client = new InvoiceEM_Client(self::$_invoice->{InvoiceEM_Client::ID_COLUMN}, true);
		}
		else if
		(
			is_a($object_or_client_id, 'InvoiceEM_Payment')
			&&
			!empty($object_or_client_id->{InvoiceEM_Payment::ID_COLUMN})
		)
		{
			self::$_payment = $object_or_client_id;
			self::$_client = new InvoiceEM_Client(self::$_payment->{InvoiceEM_Client::ID_COLUMN}, true);
		}
		
		InvoiceEM_Invoice::enable_filters();
		
		if 
		(
			!empty(self::$_client)
			&&
			!empty(self::$_client->{InvoiceEM_Client::ID_COLUMN})
		)
		{
			$email_to[] = self::$_client->{InvoiceEM_Client::TITLE_COLUMN} . ' <' . self::$_client->email . '>';
		}
		else
		{
			if ($object_or_client_id !== false)
			{
				return false;
			}
		}
		
		$email_subject = self::_filter_content($subject);
		
		$from_name = (empty($iem->settings->email->from_name))
		? $iem->settings->company->company_name
		: $iem->settings->email->from_name;
		
		if (empty($from_name))
		{
			$from_name = get_bloginfo('site_name');
		}
		
		$from_email = (empty($iem->settings->email->from_email))
		? $iem->settings->company->email
		: $iem->settings->email->from_email;
		
		if (empty($from_email))
		{
			$from_email = get_bloginfo('admin_email');
		}
		
		$email_from = $from_name . ' <' . $from_email . '>';
		
		$headers = array
		(
			'Content-Type: text/html; charset=UTF-8',
			'MIME-Version: 1.0',
			'Reply-To: ' . $email_from
		);
		
		if (!$iem->settings->email->exclude_from)
		{
			$headers[] = 'From: ' . $email_from;
		}
		
		if ($iem->settings->email->include_bcc)
		{
			$headers[] = 'BCC: ' . $email_from;
		}
		
		$attachments = array();
		
		if
		(
			$iem->cache->has_invoices_plus
			&&
			IEM_Invoices_Plus()->settings->email_attachments
			&&
			is_array(self::$attach_invoices)
		)
		{
			$invoice_template = dirname(__FILE__) . '/../templates/invoice.php';
			
			foreach (self::$attach_invoices as $invoice)
			{
				$attachments = apply_filters(InvoiceEM_Constants::HOOK_INVOICE_ATTACHMENTS, $attachments, $invoice_template, self::$_client, $invoice);
			}
		}
		
		$has_clients_plus = $iem->cache->has_clients_plus;
		
		if
		(
			$has_clients_plus
			&&
			!$iem->cache->is_client
		)
		{
			$email_to = apply_filters(InvoiceEM_Constants::HOOK_EMAIL_TO, $email_to, self::$_client);
			$headers = apply_filters(InvoiceEM_Constants::HOOK_EMAIL_HEADERS, $headers, self::$_client);
		}
		
		ob_start();
		
		include(dirname(__FILE__) . '/../templates/email.php');
		
		$output = wp_mail
		(
			$email_to,
			$email_subject,
			
			str_replace
			(
				array
				(
					'%email_subject%',
					'%email_title%',
					'%email_content%'
				),

				array
				(
					$email_subject,
					self::_filter_content($title),
					self::_filter_content($body, true)
				),

				ob_get_clean()
			),
			
			$headers,
			$attachments
		);
		
		foreach ($attachments as $attachment)
		{
			if (file_exists($attachment))
			{
				unlink($attachment);
			}
		}
		
		return $output;
	}
	
	/**
	 * Filter the content with appropriate replacement strings.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @param  string  $content Raw content to filter.
	 * @param  boolean $is_html True if HTML content is currently being filtered.
	 * @return string           Filtered content.
	 */
	private static function _filter_content($content, $is_html = false)
	{
		$iem = InvoiceEM();
		$hour = InvoiceEM_Utilities::format_date('G');
		$period = '';
		$accounting_settings = InvoiceEM_Client::accounting_settings(self::$_client->{InvoiceEM_Client::ID_COLUMN});
		
		if ($hour < 12)
		{
			$period = $iem->settings->translation->get_label('good_morning');
		}
		else if ($hour >= 18)
		{
			$period = $iem->settings->translation->get_label('good_evening');
		}
		else
		{
			$period = $iem->settings->translation->get_label('good_afternoon');
		}
		
		$replacements = array
		(
			'%company_name%' => (empty($iem->settings->company->company_name))
			? get_bloginfo('site_name')
			: $iem->settings->company->company_name,
			
			'%mor_aft_eve%' => $period
		);
		
		if (!empty(self::$_invoice))
		{
			$invoice_number = self::$_invoice->invoice_number;
			$payment_due = '';
			$total = InvoiceEM_Utilities::format_currency(self::$_invoice->total, $accounting_settings, true);
			$unpaid = InvoiceEM_Utilities::format_currency(self::$_invoice->total - self::$_invoice->paid, $accounting_settings, true);
			$url = InvoiceEM_Output::invoice_url(self::$_invoice->{InvoiceEM_Invoice::ID_COLUMN});
			
			if (self::$_invoice->payment_due > 0)
			{
				$date = InvoiceEM_Utilities::format_date(get_option('date_format'), self::$_invoice->payment_due);

				$payment_due = sprintf
				(
					$iem->settings->translation->get_label('due_on'),

					($is_html)
					? '<strong>' . $date . '</strong>'
					: $date
				);
			}
			else if (self::$_invoice->payment_due == 0)
			{
				$payment_due = $iem->settings->translation->get_label('due_upon_receipt');
			}
			else
			{
				$payment_due = $iem->settings->translation->get_label('due_whenever');
			}
			
			$replacements['%invoice_title%'] = (empty(self::$_invoice->invoice_title))
			? $iem->settings->invoicing->invoice_title
			: self::$_invoice->invoice_title;

			$replacements['%invoice_number%'] = ($is_html)
			? '<strong>' . $invoice_number . '</strong>'
			: $invoice_number;
			
			$replacements['%payment_due%'] = $payment_due;
			$replacements['%regarding%'] = self::$_invoice->{InvoiceEM_Invoice::TITLE_COLUMN};
			
			$replacements['%total%'] = ($is_html)
			? '<strong>' . $total . '</strong>'
			: $total;
			
			$replacements['%unpaid%'] = ($is_html)
			? '<strong>' . $unpaid . '</strong>'
			: $unpaid;
			
			$replacements['%url%'] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" style="color: #0073aa;"><strong>' . $iem->settings->translation->get_label('view_invoice') . '</strong></a>';
		}
		
		if (!empty(self::$_payment))
		{
			$payment_number = self::$_payment->{InvoiceEM_Payment::TITLE_COLUMN};
			$payment_amount = InvoiceEM_Utilities::format_currency(self::$_payment->amount, $accounting_settings, true);
			
			$replacements['%payment_number%'] = ($is_html)
			? '<strong>' . $payment_number . '</strong>'
			: $payment_number;
			
			$replacements['%payment_amount%'] = ($is_html)
			? '<strong>' . $payment_amount . '</strong>'
			: $payment_amount;
		}
		
		$content = str_replace(array_keys($replacements), array_values($replacements), $content);
		
		return ($is_html)
		? nl2br($content)
		: $content;
	}
}
