<?php
/*!
 * Email settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Email Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the email settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Settings_Wrapper
 */
final class InvoiceEM_Settings_Email extends InvoiceEM_Settings_Wrapper
{
	/**
	 * Tab slug for the email settings.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = 'email';

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
			 * Name used in the from field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'from_name':
			
			/**
			 * Email address used in the from field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'from_email':
			
				return '';
			
			/**
			 * True if the from name and email should only be used as the reply to.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'exclude_from':
			
			/**
			 * True if generated email should be blind copied to the company as well.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'include_bcc':
			
				return true;
			
			/**
			 * Subject line for the send invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'send_invoice_subject':
			
				return '%company_name% - '
				. sprintf
				(
					__('Invoice #%1$s', 'invoiceem'),
					'%invoice_number%'
				)
				. ' - %regarding%';
			
			/**
			 * Title displayed at the top of the send invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'send_invoice_title':
			
				return '%invoice_title%';
				
			/**
			 * Body content for the send invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'send_invoice_body':
			
				return '%mor_aft_eve%,' . PHP_EOL
				. PHP_EOL
				. sprintf
				(
					__('You have received invoice #%1$s (%2$s) in the amount of %3$s. Payment for this invoice is due %4$s. The invoice can be viewed by clicking on the following link: %5$s', 'invoiceem'),
					'%invoice_number%',
					'%regarding%',
					'%total%',
					'%payment_due%',
					'%url%'
				) . PHP_EOL
				. PHP_EOL
				. __('Thank you,', 'invoiceem') . PHP_EOL
				. '%company_name%';
				
			/**
			 * Subject line for the overdue invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'overdue_invoice_subject':
			
				return '%company_name% - '
				. sprintf
				(
					__('Invoice #%1$s Overdue', 'invoiceem'),
					'%invoice_number%'
				)
				. ' - %regarding%';
			
			/**
			 * Title displayed at the top of the overdue invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'overdue_invoice_title':
			
				return sprintf
				(
					__('%1$s Overdue', 'invoiceem'),
					'%invoice_title%'
				);
				
			/**
			 * Body content for the overdue invoice email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'overdue_invoice_body':
			
				return '%mor_aft_eve%,' . PHP_EOL
				. PHP_EOL
				. sprintf
				(
					__('Payment is overdue for invoice #%1$s (%2$s). You still owe %3$s which was due on %4$s. Please pay the remaining balance at your earliest convenience. The invoice can be viewed by clicking on the following link: %5$s', 'invoiceem'),
					'%invoice_number%',
					'%regarding%',
					'%unpaid%',
					'%payment_due%',
					'%url%'
				) . PHP_EOL
				. PHP_EOL
				. __('Thank you,', 'invoiceem') . PHP_EOL
				. '%company_name%';
				
			/**
			 * Subject line for the payment failed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_failed_subject':
			
				return '%company_name% - ' .
				sprintf
				(
					__('%1$s Payment Failed', 'invoiceem'),
					'%payment_number%'
				);
			
			/**
			 * Title displayed at the top of the payment failed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_failed_title':
			
				return __('Payment Failed', 'invoiceem');
				
			/**
			 * Body content for the payment failed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_failed_body':
			
				return '%mor_aft_eve%,' . PHP_EOL
				. PHP_EOL
				. sprintf
				(
					__('Payment %1$s for %2$s failed. Please resubmit the payment at your earliest convenience.'),
					'%payment_number%',
					'%payment_amount%'
				) . PHP_EOL
				. PHP_EOL
				. __('Thank you,', 'invoiceem') . PHP_EOL
				. '%company_name%';
				
			/**
			 * Subject line for the payment completed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_completed_subject':
			
				return '%company_name% - ' .
				sprintf
				(
					__('%1$s Payment Completed', 'invoiceem'),
					'%payment_number%'
				);
			
			/**
			 * Title displayed at the top of the payment completed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_completed_title':
			
				return __('Payment Completed', 'invoiceem');
				
			/**
			 * Body content for the payment completed email.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'payment_completed_body':
			
				return '%mor_aft_eve%,' . PHP_EOL
				. PHP_EOL
				. sprintf
				(
					__('Payment %1$s for %2$s has been processed successfully.'),
					'%payment_number%',
					'%payment_amount%'
				) . PHP_EOL
				. PHP_EOL
				. __('Thank you,', 'invoiceem') . PHP_EOL
				. '%company_name%';
		}
		
		return apply_filters(InvoiceEM_Constants::HOOK_EMAIL_DEFAULTS, parent::_default($name), $name);
	}

	/**
	 * Add the invoicing settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_tabs()
	{
		parent::_add_tab(__('Email', 'invoiceem'));
	}

	/**
	 * Sanitize the invoicing settings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $input Raw invoicing settings array.
	 * @return array        Sanitized invoicing settings array.
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
		
		$booleans = array('exclude_from', 'include_bcc');
		
		foreach ($booleans as $boolean)
		{
			$input[$boolean] =
			(
				isset($input[$boolean])
				&&
				!empty($input[$boolean])
			);
		}
		
		foreach ($input as $name => $value)
		{
			if ($name == 'from_email')
			{
				$input[$name] = sanitize_email($value);
			}
			else if
			(
				$name == 'send_invoice_body'
				||
				$name == 'overdue_invoice_body'
				||
				$name == 'payment_failed_body'
			)
			{
				$input[$name] = sanitize_textarea_field($value);
			}
			else if (!in_array($name, $booleans))
			{
				$input[$name] = sanitize_text_field($value);
			}
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
		$global_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'global_settings',
			'option_name' => $this->_option_name,
			'title' => __('Global Settings', 'invoiceem')
		));
		
		$global_settings_box->add_field(array
		(
			'description' => __('Name of the company or person that the plugin emails come from.', 'invoiceem'),
			'label' => __('From Name', 'invoiceem'),
			'name' => 'from_name',
			'type' => 'text',
			'value' => $this->from_name,
			
			'attributes' => array
			(
				'placeholder' => (empty($this->base->settings->company->company_name))
				? get_bloginfo('site_name')
				: $this->base->settings->company->company_name
			)
		));
		
		$global_settings_box->add_field(array
		(
			'description' => __('Email address that the plugin emails come from and clients can reply to.', 'invoiceem'),
			'label' => __('From Email', 'invoiceem'),
			'name' => 'from_email',
			'type' => 'email',
			'value' => $this->from_email,
			
			'attributes' => array
			(
				'placeholder' => (empty($this->base->settings->company->email))
				? get_bloginfo('admin_email')
				: $this->base->settings->company->email
			)
		));
		
		$global_settings_box->add_field(array
		(
			'description' => __('If checked, the generated emails will exclude the from name and email, though they will still be used for the reply to line. This option is recommended if you are on a shared hosting environment or if emails are not being delivered.', 'invoiceem'),
			'label' => __('Exclude From', 'invoiceem'),
			'name' => 'exclude_from',
			'type' => 'checkbox',
			'value' => $this->exclude_from
		));
		
		$global_settings_box->add_field(array
		(
			'description' => __('If checked, the generated emails will be blind carbon copied to your company as well.', 'invoiceem'),
			'label' => __('Include BCC', 'invoiceem'),
			'name' => 'include_bcc',
			'type' => 'checkbox',
			'value' => $this->include_bcc
		));
		
		$save_all_field = array
		(
			'content' => __('Save All Email Settings', 'invoiceem'),
			'type' => 'submit'
		);
		
		$global_settings_box->add_field($save_all_field);
		
		$emails_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'emails',
			'option_name' => $this->_option_name,
			'title' => __('Emails', 'invoiceem')
		));
		
		$label_subject = __('Subject', 'invoiceem');
		$label_title = __('Title', 'invoiceem');
		$label_body = __('Body', 'invoiceem');
		
		$emails_box->add_field(array
		(
			'type' => 'tabs',
			
			'tabs' => apply_filters
			(
				InvoiceEM_Constants::HOOK_EMAIL_TABS,

				array
				(
					array
					(
						'title' => __('Send Invoice', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Subject line for the send invoice email.', 'invoiceem'),
								'label' => $label_subject,
								'name' => 'send_invoice_subject',
								'type' => 'text',
								'value' => $this->send_invoice_subject,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Title displayed at the top of the send invoice email.', 'invoiceem'),
								'label' => $label_title,
								'name' => 'send_invoice_title',
								'type' => 'text',
								'value' => $this->send_invoice_title,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Body content for the send invoice email.', 'invoiceem'),
								'is_tall' => true,
								'label' => $label_body,
								'name' => 'send_invoice_body',
								'type' => 'textarea',
								'value' => $this->send_invoice_body,

								'attributes' => array
								(
									'rows' => 12
								),

								'validation' => array
								(
									'required' => true
								)
							)
						)
					),

					array
					(
						'title' => __('Overdue Invoice', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Subject line for the overdue invoice email.', 'invoiceem'),
								'label' => $label_subject,
								'name' => 'overdue_invoice_subject',
								'type' => 'text',
								'value' => $this->overdue_invoice_subject,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Title displayed at the top of the overdue invoice email.', 'invoiceem'),
								'label' => $label_title,
								'name' => 'overdue_invoice_title',
								'type' => 'text',
								'value' => $this->overdue_invoice_title,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Body content for the overdue invoice email.', 'invoiceem'),
								'is_tall' => true,
								'label' => $label_body,
								'name' => 'overdue_invoice_body',
								'type' => 'textarea',
								'value' => $this->overdue_invoice_body,

								'attributes' => array
								(
									'rows' => 12
								),

								'validation' => array
								(
									'required' => true
								)
							)
						)
					),

					array
					(
						'title' => __('Payment Failed', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Subject line for the payment failed email.', 'invoiceem'),
								'label' => $label_subject,
								'name' => 'payment_failed_subject',
								'type' => 'text',
								'value' => $this->payment_failed_subject,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Title displayed at the top of the payment failed email.', 'invoiceem'),
								'label' => $label_title,
								'name' => 'payment_failed_title',
								'type' => 'text',
								'value' => $this->payment_failed_title,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Body content for the payment failed email.', 'invoiceem'),
								'is_tall' => true,
								'label' => $label_body,
								'name' => 'payment_failed_body',
								'type' => 'textarea',
								'value' => $this->payment_failed_body,

								'attributes' => array
								(
									'rows' => 12
								),

								'validation' => array
								(
									'required' => true
								)
							)
						)
					),

					array
					(
						'title' => __('Payment Completed', 'invoiceem'),

						'fields' => array
						(
							array
							(
								'description' => __('Subject line for the payment completed email.', 'invoiceem'),
								'label' => $label_subject,
								'name' => 'payment_completed_subject',
								'type' => 'text',
								'value' => $this->payment_completed_subject,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Title displayed at the top of the payment completed email.', 'invoiceem'),
								'label' => $label_title,
								'name' => 'payment_completed_title',
								'type' => 'text',
								'value' => $this->payment_completed_title,

								'validation' => array
								(
									'required' => true
								)
							),

							array
							(
								'description' => __('Body content for the payment completed email.', 'invoiceem'),
								'is_tall' => true,
								'label' => $label_body,
								'name' => 'payment_completed_body',
								'type' => 'textarea',
								'value' => $this->payment_completed_body,

								'attributes' => array
								(
									'rows' => 12
								),

								'validation' => array
								(
									'required' => true
								)
							)
						)
					)
				)
			)
		));
		
		$emails_box->add_field($save_all_field);
		
		$wildcards_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'wildcards',
			'title' => __('Wildcards', 'invoiceem')
		));
		
		$wildcards_box->add_field(array
		(
			'content' => __('The following wilcards can be added to the email fields:', 'invoiceem'),
			'type' => 'html'
		));
		
		$wildcards_box->add_field(array
		(
			'type' => 'html',
			
			'content' => '<strong>' . __('Global', 'invoiceem') . '</strong>'
			. '<ul class="iem-wildcards">'
			. '<li><em>%company_name%</em> - ' . __('Your company name or the site name.', 'invoiceem') . '</li>'
			. '<li><em>%mor_noo_eve%</em> - ' . __('Good morning, good afternoon or good evening depending on the time of day. These can be customized in the Translation Settings.', 'invoiceem') . '</li>'
			. '</ul>'
		));
		
		$wildcards_box->add_field(array
		(
			'type' => 'html',
			
			'content' => '<strong>' . __('Invoice Emails', 'invoiceem') . '</strong>'
			. '<ul class="iem-wildcards">'
			. '<li><em>%invoice_number%</em> - ' . __('Generated number for the invoice.', 'invoiceem') . '</li>'
			. '<li><em>%invoice_title%</em> - ' . __('Title of the invoice or the global invoice title.', 'invoiceem') . '</li>'
			. '<li><em>%payment_due%</em> - ' . __('Date that the invoice is due or appropriate text. The text can be customized in the Translation Settings.', 'invoiceem') . '</li>'
			. '<li><em>%regarding%</em> - ' . __('What the invoice is for.', 'invoiceem') . '</li>'
			. '<li><em>%total%</em> - ' . __('Total amount due for the invoice.', 'invoiceem') . '</li>'
			. '<li><em>%unpaid%</em> - ' . __('Total unpaid amount for the invoice.', 'invoiceem') . '</li>'
			. '<li><em>%url%</em> - ' . __('Link to view the invoice. The link text can be customized in the Translation Settings.', 'invoiceem') . '</li>'
			. '</ul>'
		));
		
		$wildcards_box->add_field(array
		(
			'type' => 'html',
			
			'content' => '<strong>' . __('Payment Emails', 'invoiceem') . '</strong>'
			. '<ul class="iem-wildcards">'
			. '<li><em>%payment_amount%</em> - ' . __('Total amount of the payment.', 'invoiceem') . '</li>'
			. '<li><em>%payment_number%</em> - ' . __('Number associated with the payment.', 'invoiceem') . '</li>'
			. '</ul>'
		));
		
		do_action(InvoiceEM_Constants::HOOK_EMAIL_WILDCARDS, $wildcards_box);

		InvoiceEM_Meta_Box::side_meta_boxes();
	}
}
