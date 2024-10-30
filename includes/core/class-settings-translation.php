<?php
/*!
 * Translation settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Translation Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the translation settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Settings_Wrapper
 */
final class InvoiceEM_Settings_Translation extends InvoiceEM_Settings_Wrapper
{
	/**
	 * Tab slug for the translation settings.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = 'translation';

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

		add_action(InvoiceEM_Constants::HOOK_SETTINGS_TABS, array($this, 'settings_tabs'));
	}
	
	/**
	 * Get a default value based on the provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $name Name of the value to return.
	 * @return mixed        Default value if it exists, otherwise an empty string.
	 */
	protected function _default($name)
	{
		switch ($name)
		{
			/**
			 * Default label for the invoice number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_number_default':
			
				return __('Invoice Number: %s', 'invoiceem');
				
			/**
			 * Default label for the invoice date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_date_default':
			
				return __('Invoice Date: %s', 'invoiceem');
				
			/**
			 * Default label for the deposit due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'deposit_due_default':
			
				return __('Deposit Due: %s', 'invoiceem');
				
			/**
			 * Default label for the payment due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_due_default':
			
				return __('Payment Due: %s', 'invoiceem');
				
			/**
			 * Default label for the invoices with payment due upon receipt.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'upon_receipt_default':
			
				return __('Upon Receipt', 'invoiceem');
				
			/**
			 * Default label for phone numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'phone_default':
			
				return __('Phone: %s', 'invoiceem');
				
			/**
			 * Default label for fax numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'fax_default':
			
				return __('Fax: %s', 'invoiceem');
				
			/**
			 * Default label for scheduled invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'scheduled_default':
			
				return __('— SCHEDULED —', 'invoiceem');
				
			/**
			 * Default label for draft invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'draft_default':
			
				return __('— DRAFT —', 'invoiceem');
				
			/**
			 * Default label for overdue invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'overdue_default':
			
				return __('— OVERDUE —', 'invoiceem');
				
			/**
			 * Default label for paid invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'paid_default':
			
				return __('— PAID —', 'invoiceem');
				
			/**
			 * Default label for invoice to details.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'to_default':
			
				return __('TO:', 'invoiceem');
				
			/**
			 * Default label for invoice for details.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'for_default':
			
				return __('FOR:', 'invoiceem');
				
			/**
			 * Default label for PO numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'po_number_default':
			
				return __('PO Number: %s', 'invoiceem');
				
			/**
			 * Default label for line items date column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'date_column_default':
			
				return __('DATE', 'invoiceem');
				
			/**
			 * Default label for line items details column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'details_column_default':
			
				return __('DETAILS', 'invoiceem');
				
			/**
			 * Default label for line items quantity column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'quantity_column_default':
			
				return __('QTY.', 'invoiceem');
				
			/**
			 * Default label for line items rate column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'rate_column_default':
			
				return __('RATE', 'invoiceem');
				
			/**
			 * Default label for line items adjustment column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'adjustment_column_default':
			
				return __('ADJ.', 'invoiceem');
				
			/**
			 * Default label for line items total column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'total_column_default':
			
				return __('TOTAL', 'invoiceem');
				
			/**
			 * Default label for the subtotal.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'subtotal_default':
			
				return __('Subtotal:', 'invoiceem');
				
			/**
			 * Default label for the discounts.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'discount_default':
			
				return __('Discount:', 'invoiceem');
				
			/**
			 * Default label for taxes.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'tax_default':
			
				return _x('%s:', 'Tax Label', 'invoiceem');
				
			/**
			 * Default label for the amount paid.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'amount_paid_default':
			
				return __('Paid:', 'invoiceem');
				
			/**
			 * Default label for the deposit amount due.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'deposit_amount_due_default':
			
				return __('Deposit Amount Due:', 'invoiceem');
				
			/**
			 * Default label for the total amount due.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'total_amount_due_default':
			
				return __('Total Amount Due:', 'invoiceem');
				
			/**
			 * Default label for the good morning line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_morning_default':
			
				return __('Good Morning', 'invoiceem');
				
			/**
			 * Default label for the good afternoon line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_afternoon_default':
			
				return __('Good Afternoon', 'invoiceem');
				
			/**
			 * Default label for the good evening line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_evening_default':
			
				return __('Good Evening', 'invoiceem');
				
			/**
			 * Default label for the payment due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_on_default':
			
				return _x('on %s', 'Payment Due Date', 'invoiceem');
				
			/**
			 * Default label for payment due upon receipt.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_upon_receipt_default':
			
				return __('upon receipt', 'invoiceem');
				
			/**
			 * Default label for payment due when convenient.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_whenever_default':
			
				return __('when convenient', 'invoiceem');
				
			/**
			 * Default label for the view invoice link.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'view_invoice_default':
			
				return __('View Invoice »', 'invoiceem');
				
			/**
			 * Label for the invoice number.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_number_label':
			
			/**
			 * Label for the invoice date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'invoice_date_label':
			
			/**
			 * Label for deposit due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'deposit_due_label':
			
			/**
			 * Label for payment due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_due_label':
			
			/**
			 * Label for the invoices with payment due upon receipt.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'upon_receipt_label':
			
			/**
			 * Label for phone numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'phone_label':
			
			/**
			 * Label for fax numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'fax_label':
			
			/**
			 * Label for scheduled invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'scheduled_label':
			
			/**
			 * Label for draft invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'draft_label':
			
			/**
			 * Label for overdue invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'overdue_label':
			
			/**
			 * Label for paid invoices.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'paid_label':
			
			/**
			 * Label for invoice to details.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'to_label':
			
			/**
			 * Label for invoice for details.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'for_label':
			
			/**
			 * Label for PO numbers.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'po_number_label':
			
			/**
			 * Label for line items date column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'date_column_label':
			
			/**
			 * Label for line items details column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'details_column_label':
			
			/**
			 * Label for line items quantity column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'quantity_column_label':
			
			/**
			 * Label for line items rate column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'rate_column_label':
			
			/**
			 * Label for line items adjustment column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'adjustment_column_label':
			
			/**
			 * Label for line items total column.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'total_column_label':
			
			/**
			 * Label for the subtotal.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'subtotal_label':
			
			/**
			 * Label for the discounts.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'discount_label':
			
			/**
			 * Label for taxes.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'tax_label':
			
			/**
			 * Label for the total amount paid.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'amount_paid_label':
			
			/**
			 * Label for the deposit amount due.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'deposit_amount_due_label':
			
			/**
			 * Label for the total amount due.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'total_amount_due_label':
			
			/**
			 * Label for the good morning line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_morning_label':
			
			/**
			 * Label for the good afternoon line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_afternoon_label':
			
			/**
			 * Label for the good evening line on emails.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'good_evening_label':
			
			/**
			 * Label for the payment due date.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_on_label':
			
			/**
			 * Label for payment due upon receipt.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_upon_receipt_label':
			
			/**
			 * Label for payment due when convenient.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'due_whenever_label':
			
			/**
			 * Label for the view invoice link.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'view_invoice_label':
			
				return '';
		}

		return apply_filters(InvoiceEM_Constants::HOOK_TRANSLATION_DEFAULTS, parent::_default($name), $name);
	}

	/**
	 * Add the translation settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_tabs()
	{
		parent::_add_tab(__('Translation', 'invoiceem'));
	}

	/**
	 * Sanitize the translation settings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $input Raw translation settings array.
	 * @return array        Sanitized translation settings array.
	 */
	public function sanitize($input)
	{
		if
		(
			!is_array($input)
			||
			empty($input)
		)
		{
			return array();
		}

		foreach ($input as $name => $value)
		{
			$input[$name] = sanitize_text_field($value);
		}

		return $input;
	}

	/**
	 * Add meta boxes to the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes()
	{
		$quick_translations_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'quick_translations',
			'option_name' => $this->_option_name,
			'title' => __('Quick Translations', 'invoiceem')
		));
		
		$quick_translations_box->add_field(array
		(
			'type' => 'tabs',
			
			'tabs' => apply_filters
			(
				InvoiceEM_Constants::HOOK_TRANSLATION_TABS,

				array
				(
					array
					(
						'title' => __('Invoices', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Label used for the invoice number. \'%s\' is required and will be replaced with the invoice number on invoices.', 'invoiceem'),
								'label' => __('Invoice Number', 'invoiceem'),
								'name' => 'invoice_number_label',
								'type' => 'text',
								'value' => $this->invoice_number_label,

								'attributes' => array
								(
									'placeholder' => $this->invoice_number_default
								)
							),

							array
							(
								'description' => __('Label used for the invoice date. \'%s\' is required and will be replaced with the invoice date.', 'invoiceem'),
								'label' => __('Invoice Date', 'invoiceem'),
								'name' => 'invoice_date_label',
								'type' => 'text',
								'value' => $this->invoice_date_label,

								'attributes' => array
								(
									'placeholder' => $this->invoice_date_default
								)
							),

							array
							(
								'description' => __('Label used for the deposit due output. \'%s\' is required and will be replaced with the deposit due details.', 'invoiceem'),
								'label' => __('Deposit Due', 'invoiceem'),
								'name' => 'deposit_due_label',
								'type' => 'text',
								'value' => $this->deposit_due_label,

								'attributes' => array
								(
									'placeholder' => $this->deposit_due_default
								)
							),

							array
							(
								'description' => __('Label used for the payment due output. \'%s\' is required and will be replaced with the payment due details.', 'invoiceem'),
								'label' => __('Payment Due', 'invoiceem'),
								'name' => 'payment_due_label',
								'type' => 'text',
								'value' => $this->payment_due_label,

								'attributes' => array
								(
									'placeholder' => $this->payment_due_default
								)
							),

							array
							(
								'description' => __('Label used in the payment due area for invoices with payment due upon receipt.', 'invoiceem'),
								'label' => __('Upon Receipt', 'invoiceem'),
								'name' => 'upon_receipt_label',
								'type' => 'text',
								'value' => $this->upon_receipt_label,

								'attributes' => array
								(
									'placeholder' => $this->upon_receipt_default
								)
							),

							array
							(
								'description' => __('Label used for phone number output. \'%s\' is required and will be replaced with the phone number.', 'invoiceem'),
								'label' => __('Phone', 'invoiceem'),
								'name' => 'phone_label',
								'type' => 'text',
								'value' => $this->phone_label,

								'attributes' => array
								(
									'placeholder' => $this->phone_default
								)
							),

							array
							(
								'description' => __('Label used for fax number output. \'%s\' is required and will be replaced with the fax number.', 'invoiceem'),
								'label' => __('Fax', 'invoiceem'),
								'name' => 'fax_label',
								'type' => 'text',
								'value' => $this->fax_label,

								'attributes' => array
								(
									'placeholder' => $this->fax_default
								)
							),

							array
							(
								'description' => __('Status text for scheduled invoices.', 'invoiceem'),
								'label' => __('Scheduled', 'invoiceem'),
								'name' => 'scheduled_label',
								'type' => 'text',
								'value' => $this->scheduled_label,

								'attributes' => array
								(
									'placeholder' => $this->scheduled_default
								)
							),

							array
							(
								'description' => __('Status text for draft invoices.', 'invoiceem'),
								'label' => __('Draft', 'invoiceem'),
								'name' => 'draft_label',
								'type' => 'text',
								'value' => $this->draft_label,

								'attributes' => array
								(
									'placeholder' => $this->draft_default
								)
							),

							array
							(
								'description' => __('Status text for overdue invoices.', 'invoiceem'),
								'label' => __('Overdue', 'invoiceem'),
								'name' => 'overdue_label',
								'type' => 'text',
								'value' => $this->overdue_label,

								'attributes' => array
								(
									'placeholder' => $this->overdue_default
								)
							),

							array
							(
								'description' => __('Status text for paid invoices.', 'invoiceem'),
								'label' => __('Paid', 'invoiceem'),
								'name' => 'paid_label',
								'type' => 'text',
								'value' => $this->paid_label,

								'attributes' => array
								(
									'placeholder' => $this->paid_default
								)
							),

							array
							(
								'description' => __('Label displayed above the invoice to details.', 'invoiceem'),
								'label' => __('To', 'invoiceem'),
								'name' => 'to_label',
								'type' => 'text',
								'value' => $this->to_label,

								'attributes' => array
								(
									'placeholder' => $this->to_default
								)
							),

							array
							(
								'description' => __('Label displayed above the invoice for details.', 'invoiceem'),
								'label' => __('For', 'invoiceem'),
								'name' => 'for_label',
								'type' => 'text',
								'value' => $this->for_label,

								'attributes' => array
								(
									'placeholder' => $this->for_default
								)
							),

							array
							(
								'description' => __('Label used for PO number output. \'%s\' is required and will be replaced with the PO number.', 'invoiceem'),
								'label' => __('PO Number', 'invoiceem'),
								'name' => 'po_number_label',
								'type' => 'text',
								'value' => $this->po_number_label,

								'attributes' => array
								(
									'placeholder' => $this->po_number_default
								)
							),

							array
							(
								'description' => __('Label used for the line items date column.', 'invoiceem'),
								'label' => __('Date Column', 'invoiceem'),
								'name' => 'date_column_label',
								'type' => 'text',
								'value' => $this->date_column_label,

								'attributes' => array
								(
									'placeholder' => $this->date_column_default
								)
							),

							array
							(
								'description' => __('Label used for the line items details column.', 'invoiceem'),
								'label' => __('Details Column', 'invoiceem'),
								'name' => 'details_column_label',
								'type' => 'text',
								'value' => $this->details_column_label,

								'attributes' => array
								(
									'placeholder' => $this->details_column_default
								)
							),

							array
							(
								'description' => __('Label used for the line items quantity column.', 'invoiceem'),
								'label' => __('Quantity Column', 'invoiceem'),
								'name' => 'quantity_column_label',
								'type' => 'text',
								'value' => $this->quantity_column_label,

								'attributes' => array
								(
									'placeholder' => $this->quantity_column_default
								)
							),

							array
							(
								'description' => __('Label used for the line items rate column.', 'invoiceem'),
								'label' => __('Rate Column', 'invoiceem'),
								'name' => 'rate_column_label',
								'type' => 'text',
								'value' => $this->rate_column_label,

								'attributes' => array
								(
									'placeholder' => $this->rate_column_default
								)
							),

							array
							(
								'description' => __('Label used for the line items adjustment column.', 'invoiceem'),
								'label' => __('Adjustment Column', 'invoiceem'),
								'name' => 'adjustment_column_label',
								'type' => 'text',
								'value' => $this->adjustment_column_label,

								'attributes' => array
								(
									'placeholder' => $this->adjustment_column_default
								)
							),

							array
							(
								'description' => __('Label used for the line items total column.', 'invoiceem'),
								'label' => __('Total Column', 'invoiceem'),
								'name' => 'total_column_label',
								'type' => 'text',
								'value' => $this->total_column_label,

								'attributes' => array
								(
									'placeholder' => $this->total_column_default
								)
							),

							array
							(
								'description' => __('Label used for the subtotal.', 'invoiceem'),
								'label' => __('Subtotal', 'invoiceem'),
								'name' => 'subtotal_label',
								'type' => 'text',
								'value' => $this->subtotal_label,

								'attributes' => array
								(
									'placeholder' => $this->subtotal_default
								)
							),

							array
							(
								'description' => __('Label used for the discounts.', 'invoiceem'),
								'label' => __('Discounts', 'invoiceem'),
								'name' => 'discount_label',
								'type' => 'text',
								'value' => $this->discount_label,

								'attributes' => array
								(
									'placeholder' => $this->discount_default
								)
							),

							array
							(
								'description' => __('Label used for taxes. \'%s\' is required and will be replaced with the tax label.', 'invoiceem'),
								'label' => __('Tax', 'invoiceem'),
								'name' => 'tax_label',
								'type' => 'text',
								'value' => $this->tax_label,

								'attributes' => array
								(
									'placeholder' => $this->tax_default
								)
							),

							array
							(
								'description' => __('Label used for the amount paid.', 'invoiceem'),
								'label' => __('Amount Paid', 'invoiceem'),
								'name' => 'amount_paid_label',
								'type' => 'text',
								'value' => $this->amount_paid_label,

								'attributes' => array
								(
									'placeholder' => $this->amount_paid_default
								)
							),

							array
							(
								'description' => __('Label used for the deposit amount due.', 'invoiceem'),
								'label' => __('Deposit Amount Due', 'invoiceem'),
								'name' => 'deposit_amount_due_label',
								'type' => 'text',
								'value' => $this->deposit_amount_due_label,

								'attributes' => array
								(
									'placeholder' => $this->deposit_amount_due_default
								)
							),

							array
							(
								'description' => __('Label used for the line items total amount due.', 'invoiceem'),
								'label' => __('Total Amount Due', 'invoiceem'),
								'name' => 'total_amount_due_label',
								'type' => 'text',
								'value' => $this->total_amount_due_label,

								'attributes' => array
								(
									'placeholder' => $this->total_amount_due_default
								)
							)
						)
					),

					array
					(
						'title' => __('Emails', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Label used for the good morning line.', 'invoiceem'),
								'label' => __('Good Morning', 'invoiceem'),
								'name' => 'good_morning_label',
								'type' => 'text',
								'value' => $this->good_morning_label,

								'attributes' => array
								(
									'placeholder' => $this->good_morning_default
								)
							),

							array
							(
								'description' => __('Label used for the good afternoon line.', 'invoiceem'),
								'label' => __('Good Afternoon', 'invoiceem'),
								'name' => 'good_afternoon_label',
								'type' => 'text',
								'value' => $this->good_afternoon_label,

								'attributes' => array
								(
									'placeholder' => $this->good_afternoon_default
								)
							),

							array
							(
								'description' => __('Label used for the good evening line.', 'invoiceem'),
								'label' => __('Good Evening', 'invoiceem'),
								'name' => 'good_evening_label',
								'type' => 'text',
								'value' => $this->good_evening_label,

								'attributes' => array
								(
									'placeholder' => $this->good_evening_default
								)
							),

							array
							(
								'description' => __('Label used for the payment due date.', 'invoiceem'),
								'label' => __('Due On', 'invoiceem'),
								'name' => 'due_on_label',
								'type' => 'text',
								'value' => $this->due_on_label,

								'attributes' => array
								(
									'placeholder' => $this->due_on_default
								)
							),

							array
							(
								'description' => __('Label used for payment due upon receipt.', 'invoiceem'),
								'label' => __('Due Upon Receipt', 'invoiceem'),
								'name' => 'due_upon_receipt_label',
								'type' => 'text',
								'value' => $this->due_upon_receipt_label,

								'attributes' => array
								(
									'placeholder' => $this->due_upon_receipt_default
								)
							),

							array
							(
								'description' => __('Label used for payment due when convenient.', 'invoiceem'),
								'label' => __('Due Whenever', 'invoiceem'),
								'name' => 'due_whenever_label',
								'type' => 'text',
								'value' => $this->due_whenever_label,

								'attributes' => array
								(
									'placeholder' => $this->due_whenever_default
								)
							),

							array
							(
								'description' => __('Label used for the view invoice link.', 'invoiceem'),
								'label' => __('View Invoice', 'invoiceem'),
								'name' => 'view_invoice_label',
								'type' => 'text',
								'value' => $this->view_invoice_label,

								'attributes' => array
								(
									'placeholder' => $this->view_invoice_default
								)
							)
						)
					)
				)
			)
		));
		
		$quick_translations_box->add_field(array
		(
			'content' => __('Save All Translation Settings', 'invoiceem'),
			'type' => 'submit'
		));

		InvoiceEM_Meta_Box::side_meta_boxes();
	}
	
	/**
	 * Get a translated label based on a provided name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $name Name of the label to return.
	 * @return string       Translated label if it is found, otherwise the default label.
	 */
	public function get_label($name)
	{
		$label = $this->{$name . '_label'};
		
		if (empty($label))
		{
			$label = $this->{$name . '_default'};
		}
		
		return htmlentities($label);
	}
}
