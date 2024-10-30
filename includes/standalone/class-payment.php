<?php
/*!
 * Payment object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Payment
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the payment object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Object
 */
final class InvoiceEM_Payment extends InvoiceEM_Object
{
	/**
	 * Column name for the payment ID.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const ID_COLUMN = 'payment_id';

	/**
	 * Column name for the payment number.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TITLE_COLUMN = 'payment_number';
	
	/**
	 * Column names selected for lists.
	 *
	 * @since 1.0.0
	 *
	 * @const array
	 */
	const SELECT_COLUMNS = InvoiceEM_Payments::SELECT_COLUMNS;
	
	/**
	 * Invoices for this payment.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    InvoiceEM_Payment_Invoices
	 */
	private $_invoices;

	/**
	 * Raw payments table name.
	 *
	 * @since 1.0.0
	 *
	 * @access protected static
	 * @var    string
	 */
	protected static $_raw_table_name = InvoiceEM_Constants::TABLE_PAYMENTS;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.6 Removed upgrade notice filter.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  integer $payment_id Optional ID of the payment to load.
	 * @param  boolean $is_view    True if the payment is being viewed.
	 * @return void
	 */
	public function __construct($payment_id = 0, $is_view = false)
	{
		if
		(
			!$is_view
			&&
			!current_user_can(InvoiceEM_Constants::CAP_EDIT_PAYMENTS)
		)
		{
			wp_die(__('You are not authorized to view this page.', 'invoiceem'));
		}

		parent::__construct();
		
		$this->_finalize($payment_id);
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
			 * ID for the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case self::ID_COLUMN:
			
				return 0;
				
			/**
			 * Method of the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'method':
			
			/**
			 * Number for the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case self::TITLE_COLUMN:
			
				return '';
				
			/**
			 * ID of the client record for the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Client::ID_COLUMN:
			
			/**
			 * Date that this payment was received.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'payment_date':
			
			/**
			 * Total amount of the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'amount':
			
				return 0;
				
			/**
			 * Bonus added to the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'bonus':
			
			/**
			 * Fee charged on the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'fee':
			
				return null;
				
			/**
			 * Failed state of the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'is_failed':
			
				return false;

			/**
			 * Complete state of the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case 'is_completed':
			
			/**
			 * Active state of the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var integer
			 */
			case InvoiceEM_Constants::COLUMN_IS_ACTIVE:
			
				return true;
			
			/**
			 * Locked state for the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_LOCKED:
			
			/**
			 * History of events for the payment.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case InvoiceEM_Constants::COLUMN_HISTORY:
				
			/**
			 * Total calculated amount for this payment.
			 *
			 * @since 1.0.0
			 *
			 * @var float
			 */
			case 'total':
			
				return 0;
		}

		return parent::_default($name);
	}
	
	/**
	 * Finalize the payment.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  integer $payment_id ID of the payment to load.
	 * @return void
	 */
	private function _finalize($payment_id)
	{
		if
		(
			is_numeric($payment_id)
			&&
			$payment_id > 0
		)
		{
			$this->_load($payment_id);
		}
		else if
		(
			isset($_POST['action'])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
		)
		{
			if
			(
				isset($_POST[self::ID_COLUMN])
				&&
				is_numeric($_POST[self::ID_COLUMN])
				&&
				$_POST[self::ID_COLUMN] > 0
			)
			{
				$this->_load(esc_attr($_POST[self::ID_COLUMN]));
			}
			
			$new_amount = 0;
			
			if
			(
				isset($_POST['invoices'])
				&&
				is_array($_POST['invoices'])
			)
			{
				foreach ($_POST['invoices'] as $invoice)
				{
					if
					(
						is_array($invoice)
						&&
						isset($invoice['amount'])
					)
					{
						$new_amount += InvoiceEM_Utilities::unformat_currency($invoice['amount']);
					}
				}
			}
			
			$row = array
			(
				'method' => 
				(
					!isset($_POST['method'])
					||
					empty($_POST['method'])
				)
				? 'c'
				: strtolower(substr(sanitize_text_field($_POST['method']), 0, 1)),
				
				self::TITLE_COLUMN =>
				(
					!isset($_POST[self::TITLE_COLUMN])
					||
					empty($_POST[self::TITLE_COLUMN])
				)
				? $this->{self::TITLE_COLUMN}
				: substr(sanitize_text_field($_POST[self::TITLE_COLUMN]), 0, 255),

				InvoiceEM_Client::ID_COLUMN =>
				(
					!isset($_POST[InvoiceEM_Client::ID_COLUMN])
					||
					!is_numeric($_POST[InvoiceEM_Client::ID_COLUMN])
				)
				? $this->{InvoiceEM_Client::ID_COLUMN}
				: esc_attr($_POST[InvoiceEM_Client::ID_COLUMN]),

				'payment_date' =>
				(
					!isset($_POST['payment_date'])
					||
					empty($_POST['payment_date'])
				)
				? $this->payment_date
				: strtotime(esc_attr($_POST['payment_date'])),
				
				'amount' => $new_amount,

				'bonus' =>
				(
					!isset($_POST['bonus'])
					||
					empty($_POST['bonus'])
				)
				? null
				: InvoiceEM_Utilities::unformat_currency($_POST['bonus']),
				
				'fee' =>
				(
					!isset($_POST['fee'])
					||
					empty($_POST['fee'])
				)
				? null
				: InvoiceEM_Utilities::unformat_currency($_POST['fee']),
				
				'is_failed' =>
				(
					isset($_POST['is_failed'])
					&&
					!empty($_POST['is_failed'])
				),
				
				'is_completed' =>
				(
					isset($_POST['is_completed'])
					&&
					!empty($_POST['is_completed'])
				)
			);
			
			$formats = array('%s', '%s', '%d', '%d', '%f', '%f', '%f', '%d', '%d');
			$is_valid = true;
			
			if (empty($row[self::TITLE_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please enter a payment number.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row[InvoiceEM_Client::ID_COLUMN]))
			{
				InvoiceEM_Output::add_admin_notice(__('Please select a client.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}

			if (empty($row['payment_date']))
			{
				InvoiceEM_Output::add_admin_notice(__('Please select a payment date.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);

				$is_valid = false;
			}
			
			if ($is_valid)
			{
				$this->_load_post($this->base->cache->action, $row, $formats);
				
				if (!$this->_invoices->update($this->{self::ID_COLUMN}))
				{
					InvoiceEM_Output::add_admin_notice(__('The payment was updated successfully, but there was a problem updating invoices for the payment.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
				}
			}
			else
			{
				$row[self::ID_COLUMN] =
				(
					!isset($_POST[self::ID_COLUMN])
					||
					!is_numeric($_POST[self::ID_COLUMN])
				)
				? $this->{self::ID_COLUMN}
				: esc_attr($_POST[self::ID_COLUMN]);

				$this->_set_properties($row);
			}
		}
		else if
		(
			isset($_GET['action'])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
		)
		{
			$is_delete = ($this->base->cache->action == InvoiceEM_Constants::ACTION_DELETE);
			$processed_id = $this->_load_get($this->base->cache->action);
			
			if
			(
				$is_delete
				&&
				$processed_id !== false
			)
			{
				InvoiceEM_Payment_Invoices::delete_payment_records($processed_id);
			}
		}
	}

	/**
	 * Load the payment from the database.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $payment_id ID value for the payment being loaded.
	 * @return void
	 */
	protected function _load($payment_id)
	{
		parent::_load($payment_id);
		
		$this->total = $this->amount;
		
		if (!empty($this->bonus))
		{
			$this->total += $this->bonus;
		}
		
		if (!empty($this->fee))
		{
			$this->total += $this->fee;
		}
		
		$this->_invoices = new InvoiceEM_Payment_Invoices($payment_id);
	}

	/**
	 * Load the payment from GET data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param  string $action Action being taken on the payment.
	 * @return mixed          Payment ID or true on successful action, otherwise false.
	 */
	protected function _load_get($action)
	{
		$payment_id = parent::_load_get($action);
		
		if ($payment_id !== false)
		{
			if
			(
				$action == InvoiceEM_Constants::ACTION_ACTIVATE
				||
				$action == InvoiceEM_Constants::ACTION_DEACTIVATE
			)
			{
				$this->_invoices->update_invoices();
			}
			else if ($action == InvoiceEM_Constants::ACTION_PAYMENT_FAILED)
			{
				return $this->_get_payment_failed($payment_id);
			}
			else if ($action == InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED)
			{
				return $this->_get_payment_completed($payment_id);
			}
		}
		
		return $payment_id;
	}

	/**
	 * Send a payment failed notification.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $payment_id ID of the payment the notification is being sent for.
	 * @return mixed              Payment ID if send is successful, otherwise false.
	 */
	private function _get_payment_failed($payment_id)
	{
		global $wpdb;
		
		$output = false;
		
		if ($payment_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose a failed payment to send the notification for.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Failed payment does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_PAYMENT_FAILED, $payment_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to send the payment failed notification for %1$s.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else if (!$this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE})
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is not active and the notification cannot be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else if (!$this->is_failed)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is not marked as failed and the notification cannot be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else
		{
			$this->_add_history(InvoiceEM_Constants::ACTION_PAYMENT_FAILED);

			$payment_failed = $wpdb->update
			(
				InvoiceEM_Database::get_table_name(static::$_raw_table_name),

				array
				(
					InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
				),

				array
				(
					static::ID_COLUMN => $payment_id
				),

				'%s',
				'%d'
			);

			if ($payment_failed === false)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s payment failed notification could not be sent.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_ERROR
				);
			}
			else
			{
				if (InvoiceEM_Email::send($this, $this->base->settings->email->payment_failed_subject, $this->base->settings->email->payment_failed_title, $this->base->settings->email->payment_failed_body))
				{
					InvoiceEM_Output::add_admin_notice(sprintf
					(
						__('%1$s payment failed notification sent successfully.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					));
				}
				else
				{
					InvoiceEM_Output::add_admin_notice
					(
						sprintf
						(
							__('%1$s payment failed notification could not be sent. If the problem persists, please enable the \'Exclude From\' option in the email settings.', 'invoiceem'),
							$this->{static::TITLE_COLUMN}
						),

						InvoiceEM_Constants::NOTICE_WARNING
					);
				}

				$output = $payment_id;
			}
		}
		
		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}

	/**
	 * Send a payment completed notification.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $payment_id ID of the payment the notification is being sent for.
	 * @return mixed              Payment ID if send is successful, otherwise false.
	 */
	private function _get_payment_completed($payment_id)
	{
		global $wpdb;
		
		$output = false;
		
		if ($payment_id == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Please choose a completed payment to send the notification for.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if ($this->{static::ID_COLUMN} == 0)
		{
			InvoiceEM_Output::add_admin_notice(__('Completed payment does not exist.', 'invoiceem'), InvoiceEM_Constants::NOTICE_ERROR);
		}
		else if
		(
			!isset($_GET[InvoiceEM_Constants::NONCE])
			||
			!InvoiceEM_Utilities::verify_nonce(esc_attr($_GET[InvoiceEM_Constants::NONCE]), InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED, $payment_id)
		)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('You are not authorized to send the payment completed notification for %1$s.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_ERROR
			);
		}
		else if (!$this->{InvoiceEM_Constants::COLUMN_IS_ACTIVE})
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is not active and the notification cannot be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else if (!$this->is_completed)
		{
			InvoiceEM_Output::add_admin_notice
			(
				sprintf
				(
					__('%1$s is not marked as completed and the notification cannot be sent.', 'invoiceem'),
					$this->{static::TITLE_COLUMN}
				),

				InvoiceEM_Constants::NOTICE_WARNING
			);
		}
		else
		{
			$this->_add_history(InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED);

			$payment_completed = $wpdb->update
			(
				InvoiceEM_Database::get_table_name(static::$_raw_table_name),

				array
				(
					InvoiceEM_Constants::COLUMN_HISTORY => $this->_history->get_serialized()
				),

				array
				(
					static::ID_COLUMN => $payment_id
				),

				'%s',
				'%d'
			);

			if ($payment_completed === false)
			{
				InvoiceEM_Output::add_admin_notice
				(
					sprintf
					(
						__('%1$s payment completed notification could not be sent.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					),

					InvoiceEM_Constants::NOTICE_ERROR
				);
			}
			else
			{
				if (InvoiceEM_Email::send($this, $this->base->settings->email->payment_completed_subject, $this->base->settings->email->payment_completed_title, $this->base->settings->email->payment_completed_body))
				{
					InvoiceEM_Output::add_admin_notice(sprintf
					(
						__('%1$s payment completed notification sent successfully.', 'invoiceem'),
						$this->{static::TITLE_COLUMN}
					));
				}
				else
				{
					InvoiceEM_Output::add_admin_notice
					(
						sprintf
						(
							__('%1$s payment completed notification could not be sent. If the problem persists, please enable the \'Exclude From\' option in the email settings.', 'invoiceem'),
							$this->{static::TITLE_COLUMN}
						),

						InvoiceEM_Constants::NOTICE_WARNING
					);
				}

				$output = $payment_id;
			}
		}
		
		$this->base->cache->action = InvoiceEM_Constants::ACTION_LIST;
		
		return $output;
	}

	/**
	 * Prepare the payment output.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare()
	{
		parent::prepare();
		
		$payment_details_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'payment_details',
			'title' => __('Payment Details', 'invoiceem')
		));
		
		if
		(
			empty($this->method)
			||
			$this->method == 'c'
			||
			$this->method == 'd'
		)
		{
			$payment_details_box->add_field(array
			(
				'description' => __('Method for this payment.', 'invoiceem'),
				'label' => __('Method', 'invoiceem'),
				'name' => 'method',
				'options' => $this->_get_payment_methods(),
				'type' => 'select',
				'value' => $this->method
			));
		}
		
		$payment_details_box->add_field(array
		(
			'description' => __('Number for this payment.', 'invoiceem'),
			'label' => __('Payment Number', 'invoiceem'),
			'name' => self::TITLE_COLUMN,
			'type' => 'text',
			'value' => $this->{self::TITLE_COLUMN},

			'attributes' => array
			(
				'maxlength' => 255
			),

			'validation' => array
			(
				'required' => true
			)
		));
		
		$selected_client = array();
		$client_value = $this->{InvoiceEM_Client::ID_COLUMN};
		
		if (!empty($client_value))
		{
			$client_title = $this->{InvoiceEM_Client::TITLE_COLUMN};
			
			$selected_client = (empty($client_title))
			? InvoiceEM_Client::selected_item($client_value)
			: array
			(
				$client_value => $client_title
			);
		}
		else if
		(
			isset($_GET[InvoiceEM_Client::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Client::ID_COLUMN])
		)
		{
			$client_value = esc_attr($_GET[InvoiceEM_Client::ID_COLUMN]);
			$selected_client = InvoiceEM_Client::selected_item($client_value);
		}
		else if
		(
			isset($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
			&&
			!empty($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN])
		)
		{
			$client_value = esc_attr($_GET[InvoiceEM_Constants::FILTER . InvoiceEM_Client::ID_COLUMN]);
			$selected_client = InvoiceEM_Client::selected_item($client_value);
		}
		
		if (!empty($client_value))
		{
			$this->base->cache->accounting = InvoiceEM_Client::accounting_settings($client_value);
		}

		$payment_details_box->add_field(array
		(
			'description' => __('Client that provided this payment.', 'invoiceem'),
			'input_classes' => array('iem-accounting'),
			'label' => __('Client', 'invoiceem'),
			'name' => InvoiceEM_Client::ID_COLUMN,
			'options' => $selected_client,
			'table' => InvoiceEM_Constants::TABLE_CLIENTS,
			'type' => 'select',
			'value' => $client_value,

			'attributes' => array
			(
				'placeholder' => __('Select a Client', 'invoiceem')
			),

			'validation' => array
			(
				'required' => true
			)
		));
		
		$date_format = get_option('date_format');
		
		$payment_details_box->add_field(array
		(
			'description' => __('Date that this payment was received.', 'invoiceem'),
			'input_classes' => array('iem-datepicker'),
			'label' => __('Payment Date', 'invoiceem'),
			'name' => 'payment_date',
			'type' => 'text',

			'attributes' => array
			(
				'autocomplete' => 'off',
				'placeholder' => InvoiceEM_Utilities::format_date($date_format)
			),

			'validation' => array
			(
				'required' => true
			),

			'value' =>
			(
				!is_numeric($this->payment_date)
				||
				$this->payment_date <= 0
			)
			? ''
			: date_i18n($date_format, $this->payment_date)
		));
		
		$invoices_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'invoices',
			'title' => __('Invoices', 'invoiceem')
		));
		
		$invoices_box->add_field(array
		(
			'add_item' => __('Add Invoice', 'invoiceem'),
			'classes' => array('iem-line-items'),
			'is_locked' => true,
			'is_simple' => true,
			'name' => 'invoices',
			'type' => 'repeatable',
			
			'repeatable_field' => array
			(
				'hide_labels' => true,
				'type' => 'group',

				'fields' => array
				(
					array
					(
						'classes' => array('iem-hidden'),
						'name' => 'payment_invoice_id',
						'type' => 'hidden'
					),
					
					array
					(
						'classes' => array('iem-col-xs-9'),
						'description' => __('Invoice covered by this payment.', 'invoiceem'),
						'input_classes' => array('required'),
						'name' => InvoiceEM_Invoice::ID_COLUMN,
						'table' => InvoiceEM_Constants::TABLE_INVOICES,
						'type' => 'select',

						'attributes' => array
						(
							'placeholder' => __('Select an Invoice', 'invoiceem')
						)
					),

					array
					(
						'classes' => array('iem-col-xs-3'),
						'description' => __('Amount covered on this invoice.', 'invoiceem'),
						'input_classes' => array('iem-calculate', 'iem-currency', 'iem-exclude-placeholder', 'required'),
						'name' => 'amount',
						'type' => 'text',

						'attributes' => array
						(
							'placeholder' => __('Amount Paid', 'invoiceem')
						)
					)
				)
			),
			
			'value' => (empty($this->_invoices))
			? array()
			: $this->_invoices->get_value()
		));
		
		$invoices_box->add_field(array
		(
			'type' => 'group',
			
			'fields' => array
			(
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'group',

					'fields' => array
					(
						array
						(
							'description' => __('Bonus added to this payment.', 'invoiceem'),
							'input_classes' => array('iem-calculate', 'iem-currency'),
							'is_simple' => true,
							'name' => 'bonus',
							'type' => 'text',

							'attributes' => array
							(
								'placeholder' => __('Bonus', 'invoiceem')
							),

							'value' =>
							(
								!is_numeric($this->bonus)
								||
								$this->bonus <= 0
							)
							? ''
							: InvoiceEM_Utilities::format_currency($this->bonus)
						),

						array
						(
							'description' => __('Fee charged on this payment.', 'invoiceem'),
							'input_classes' => array('iem-calculate', 'iem-currency'),
							'is_simple' => true,
							'name' => 'fee',
							'type' => 'text',

							'attributes' => array
							(
								'placeholder' => __('Fee', 'invoiceem')
							),

							'value' =>
							(
								!is_numeric($this->fee)
								||
								$this->fee <= 0
							)
							? ''
							: InvoiceEM_Utilities::format_currency($this->fee)
						)
					),
				),
				
				array
				(
					'classes' => array('iem-col-md-6', 'iem-col-sm-12'),
					'type' => 'html',

					'content' => '<div class="iem-totals">'
					. '<table cellspacing="0">'
					. '<tbody>'
					. '<tr class="iem-subtotal-row iem-hidden">'
					. '<td>' . __('Subtotal:', 'invoiceem') . '</td>'
					. '<td class="iem-subtotal-output">--</td>'
					. '</tr>'
					. '<tr class="iem-bonus-row iem-hidden">'
					. '<td>' . __('Bonus:', 'invoiceem') . '</td>'
					. '<td class="iem-bonus-output">--</td>'
					. '</tr>'
					. '<tr class="iem-fee-row iem-hidden">'
					. '<td>' . __('Fee:', 'invoiceem') . '</td>'
					. '<td class="iem-fee-output">--</td>'
					. '</tr>'
					. '<tr>'
					. '<td>' . __('Total:', 'invoiceem') . '</td>'
					. '<td class="iem-total-output">--</td>'
					. '</tr>'
					. '</tbody>'
					. '</table>'
					. '</div>'
				)
			)
		));;
		
		$this->_history_box();
		
		$this->_publish_box
		(
			__('Payment is currently active.', 'invoiceem'),
			false,
			
			array
			(
				array
				(
					'description' => __('Payment failed.', 'invoiceem'),
					'label' => __('Is Failed', 'invoiceem'),
					'name' => 'is_failed',
					'type' => 'checkbox',
					'value' => $this->is_failed
				),
				
				array
				(
					'description' => __('Payment is completed.', 'invoiceem'),
					'label' => __('Is Completed', 'invoiceem'),
					'name' => 'is_completed',
					'type' => 'checkbox',
					'value' => $this->is_completed,

					'classes' => ($this->is_failed)
					? array('iem-hidden')
					: array(),

					'conditional' => array
					(
						array
						(
							'compare' => '!=',
							'field' => 'is_failed',
							'value' => '1'
						)
					)
				)
			)
		);

		InvoiceEM_Meta_Box::finalize_meta_boxes();
	}
	
	/**
	 * Get the invoices for the payment.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Invoices associated with this payment.
	 */
	public function get_payment_invoices()
	{
		return (empty($this->_invoices))
		? array()
		: $this->_invoices->get_payment_invoices();
	}
	
	/**
	 * Mark the payment as submitted.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array   $columns Database columns for the payment update.
	 * @param  array   $formats Database column formats.
	 * @return boolean
	 */
	public function submitted($columns, $formats)
	{
		global $wpdb;
		
		$this->_add_history(InvoiceEM_Constants::ACTION_SUBMITTED);

		$columns[InvoiceEM_Constants::COLUMN_HISTORY] = $this->_history->get_serialized();
		$formats[] = '%s';
		
		$updated = $wpdb->update
		(
			InvoiceEM_Database::get_table_name(self::$_raw_table_name),
			$columns,
			
			array
			(
				self::ID_COLUMN => $this->{self::ID_COLUMN}
			),
			
			$formats,
			'%d'
		);
					
		return ($updated !== false);
	}
	
	/**
	 * Get methods for manual payments.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return array Methods available for manual payments.
	 */
	private function _get_payment_methods()
	{
		if ($this->base->cache->has_payments_plus)
		{
			$iempp = IEM_Payments_Plus();
			
			return array
			(
				'c' => $iempp->settings->get_method_label('c'),
				'd' => $iempp->settings->get_method_label('d')
			);
		}
		else
		{
			return array
			(
				'c' => __('Check', 'invoiceem'),
				'd' => __('Direct Deposit', 'invoiceem')
			);
		}
	}
}
