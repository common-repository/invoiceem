<?php
/*!
 * History object.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage History
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the history object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_History extends InvoiceEM_Wrapper
{
	/**
	 * Date format for events.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string
	 */
	private $_date_format;
	
	/**
	 * Number of entries displayed initially.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    integer
	 */
	private $_display = 5;
	
	/**
	 * List of events. Each event contains the following indexes:
	 * u: User ID (integer)
	 * d: Date (integer)
	 * e: Event (string)
	 * c: Content (string)
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    array
	 */
	private $_events;
	
	/**
	 * Output label for events.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string
	 */
	private $_output;
	
	/**
	 * Time format for events.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string
	 */
	private $_time_format;
	
	/**
	 * Number of seconds that need to pass before a duplicate event is no longer considered an undo.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    integer
	 */
	private $_undo_time = 900;
	
	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $events Raw list of events.
	 * @return void
	 */
	public function __construct($events = array())
	{
		parent::__construct();
		
		$this->_events = maybe_unserialize($events);
		
		if (!is_array($this->_events))
		{
			$this->_events = array();
		}
	}
	
	/**
	 * Add an event to the history.
	 *
	 * @since 1.0.6 Modified processing check.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  mixed  $event   Event to add to the history.
	 * @param  mixed  $user_id ID of the user that trigger the event or false for system events.
	 * @param  string $date    Date that the event occurred.
	 * @param  string $content Content associated with the event.
	 * @return void
	 */
	public function add_event($event, $user_id = 0, $date = '', $content = '')
	{
		if
		(
			$user_id === false
			||
			get_option(InvoiceEM_Constants::OPTION_PROCESSING) === 1
		)
		{
			$user_id = -1;
		}
		else if (is_user_logged_in())
		{
			$user_id = get_current_user_id();
		}
		
		$add_event = true;
		
		if (empty($date))
		{
			$date = time();
		}
		
		if (!is_numeric($date))
		{
			$date = strtotime($date);
		}
		
		if (!empty($this->_events))
		{
			$first_event = $this->_events[0];
			
			if ($user_id == $first_event['u'])
			{
				if
				(
					(
						$event == InvoiceEM_Constants::ACTION_ACTIVATE
						&&
						$first_event['e'] == InvoiceEM_Constants::ACTION_DEACTIVATE
					)
					||
					(
						$event == InvoiceEM_Constants::ACTION_DEACTIVATE
						&&
						$first_event['e'] == InvoiceEM_Constants::ACTION_ACTIVATE
					)
				)
				{
					$add_event = ($date - $first_event['d'] > $this->_undo_time);
				}
				else if
				(
					(
						(
							$event == InvoiceEM_Constants::ACTION_EDIT
							&&
							$first_event['e'] == InvoiceEM_Constants::ACTION_EDIT
						)
						||
						(
							$event == InvoiceEM_Constants::ACTION_PAYMENT_FAILED
							&&
							$first_event['e'] == InvoiceEM_Constants::ACTION_PAYMENT_FAILED
						)
						||
						(
							$event == InvoiceEM_Constants::ACTION_RESEND
							&&
							$first_event['e'] == InvoiceEM_Constants::ACTION_RESEND
						)
						||
						(
							$event == InvoiceEM_Constants::ACTION_SEND
							&&
							$first_event['e'] == InvoiceEM_Constants::ACTION_SEND
						)
						||
						(
							$event == InvoiceEM_Constants::ACTION_VIEW
							&&
							$first_event['e'] == InvoiceEM_Constants::ACTION_VIEW
						)
					)
					&&
					$date - $first_event['d'] <= $this->_undo_time
				)
				{
					array_shift($this->_events);
				}
			}
		}
		
		if ($add_event)
		{
			$event_fields = array
			(
				'u' => $user_id,
				'd' => $date,
				'e' => $event
			);
			
			if (!empty($content))
			{
				$event_fields['c'] = $content;
			}
			
			array_unshift($this->_events, $event_fields);
		}
		else
		{
			array_shift($this->_events);
		}
		
		if
		(
			isset($_POST['iem_add_note'])
			&&
			!empty($_POST['iem_add_note'])
		)
		{
			array_unshift
			(
				$this->_events,

				array
				(
					'u' => $user_id,
					'd' => $date,
					'e' => InvoiceEM_Constants::ACTION_NOTE,
					'c' => sanitize_textarea_field($_POST['iem_add_note'])
				)
			);
		}
	}
	
	/**
	 * Display the remaining events.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function display_remaining()
	{
		$output = '';
		$total_events = count($this->_events);
		
		if ($total_events > $this->_display)
		{
			$this->_setup_output();
			
			$i = $this->_display;
			
			for ($i; $i < $total_events; $i++)
			{
				$field = new InvoiceEM_Field($this->_generate_field($this->_events[$i]));
				$output .= $field->output();
			}
		}
		
		return $output;
	}
	
	/**
	 * Return the serialized events.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string Serialized events.
	 */
	public function get_serialized()
	{
		return (is_array($this->_events))
		? maybe_serialize($this->_events)
		: $this->_events;
	}
	
	/**
	 * Add the history meta box.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $raw_table_name Raw table name for the history.
	 * @return void
	 */
	public function meta_box_output($raw_table_name)
	{
		$total_events = count($this->_events);
		
		if ($total_events > 0)
		{
			$display_remaining_label = __('Display Remaining History', 'invoiceem');
			
			$history_box = new InvoiceEM_Meta_Box(array
			(
				'context' => 'normal',
				'id' => 'history',
				'title' => __('History', 'invoiceem')
			));
			
			$history_box->add_field(self::add_note_field($raw_table_name));

			$this->_setup_output();

			foreach ($this->_events as $i => $event)
			{
				$history_box->add_field($this->_generate_field($event));
				
				if
				(
					$i == $this->_display - 1
					&&
					$total_events > $this->_display
				)
				{
					$history_box->add_field(array
					(
						'content' => '<button type="button" class="button iem-button iem-display-history" data-iem-table="' . esc_attr($raw_table_name) . '">' . $display_remaining_label . '</button>',
						'type' => 'html'
					));
					
					break;
				}
			}
		}
	}
	
	/**
	 * GGroup field for the add note form.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $raw_table_name Table name for the current object or list.
	 * @param  boolean $is_list        True if the form is being added to a list.
	 * @return array                   Generate add note form field.
	 */
	public static function add_note_field($raw_table_name, $is_list = false)
	{
		$attributes = array
		(
			'placeholder' => __('Enter note for this record.', 'invoiceem'),
			'rows' => 4
		);
		
		$label_cancel = __('Cancel', 'invoiceem');
		$content = '<button type="button" class="button iem-button iem-add-note" data-iem-cancel="' . esc_attr($label_cancel) . '">' . __('Add Note', 'invoiceem') . '</button>';
		$field_classes = array();
		
		if ($is_list)
		{
			$content .= ' <button type="button" class="button iem-button iem-cancel">' . $label_cancel . '</button>';
		}
		else
		{
			$attributes['disabled'] = 'disabled';
			$field_classes= array('iem-hidden'); 
		}
		
		$fields = array
		(
			array
			(
				'attributes' => $attributes,
				'classes' => $field_classes,
				'input_classes' => array('required'),
				'name' => 'iem_add_note',
				'type' => 'textarea'
			)
		);
		
		if
		(
			InvoiceEM()->cache->has_clients_plus
			&&
			$raw_table_name != InvoiceEM_Constants::TABLE_CLIENTS
			&&
			$raw_table_name != InvoiceEM_Constants::TABLE_COUNTRIES
			&&
			$raw_table_name != InvoiceEM_Constants::TABLE_CURRENCIES
		)
		{
			$fields = apply_filters(InvoiceEM_Constants::HOOK_ADD_NOTE, $fields, $field_classes);
		}
		
		$fields[] = array
		(
			'content' => $content,
			'type' => 'html'
		);
		
		return array
		(
			'classes' => array('iem-notes-form'),
			'fields' => $fields,
			'type' => 'group'
		);
	}
	
	/**
	 * Generate field settings based on the provided event.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  array $event Raw event to generate the field settings for.
	 * @return array        Generate field settings.
	 */
	private function _generate_field($event)
	{
		$action = '';
		$display_name = '';

		$user = ($event['u'] > 0)
		? get_user_by('id', $event['u'])
		: '';

		if (empty($user))
		{
			$display_name = ($event['u'] < 0)
			? $this->base->cache->plugin_data['Name']
			: __('Anonymous User', 'invoiceem');
		}
		else
		{
			$display_name = $user->display_name;
		}

		switch ($event['e'])
		{
			case InvoiceEM_Constants::ACTION_ACTIVATE:

				$action = __('Record activated', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_ADD:

				$action = __('Record created', 'invoiceem');

			break;
			
			case InvoiceEM_Constants::ACTION_CLIENT_NOTE:
			case InvoiceEM_Constants::ACTION_NOTE:
			
				$action = __('Note added', 'invoiceem');
				
			break;

			case InvoiceEM_Constants::ACTION_DEACTIVATE:

				$action = __('Record deactivated', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_EDIT:

				$action = __('Record updated', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_LINE_ITEM:

				$action = __('Line item added', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_PAYMENT_COMPLETED:

				$action = __('Payment completed notification sent', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_PAYMENT_FAILED:

				$action = __('Payment failed notification sent', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_RESEND:

				$action = __('Invoice resent', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_SEND:

				$action = __('Invoice sent', 'invoiceem');

			break;

			case InvoiceEM_Constants::ACTION_SUBMITTED:

				$action = __('Payment submitted', 'invoiceem');

			break;
			
			case InvoiceEM_Constants::ACTION_VIEW:
			
				$action = __('Invoice viewed', 'invoiceem');
				
			break;
		}
		
		$content = (empty($event['c']))
		? ''
		: '<div class="iem-history-content">'
		. '<p>' . nl2br($event['c']) . '</p>'
		. '</div>';
		
		return array
		(
			'type' => 'html',

			'content' => '<p>'
			. sprintf
			(
				$this->_output,
				$action,
				$display_name,
				InvoiceEM_Utilities::format_date($this->_date_format, $event['d']),
				InvoiceEM_Utilities::format_date($this->_time_format, $event['d'])
			)
			. '</p>'
			. $content
		);
	}
	
	/**
	 * Setup the output label and formats.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return void
	 */
	private function _setup_output()
	{
		$this->_output = _x('%1$s by %2$s on %3$s at %4$s.', 'Action, User, Date, Time', 'invoiceem');
		$this->_date_format = get_option('date_format');
		$this->_time_format = get_option('time_format');
	}
}
