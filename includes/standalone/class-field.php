<?php
/*!
 * Meta box field functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Field
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the meta box object.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Wrapper
 */
final class InvoiceEM_Field extends InvoiceEM_Wrapper
{
	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $options Options for the field.
	 * @return void
	 */
	public function __construct($options)
	{
		parent::__construct();

		$this->_set_properties($options);

		if
		(
			!empty($this->name)
			&&
			is_array($this->validation)
			&&
			!empty($this->validation)
		)
		{
			$this->base->cache->_push('form_validation', $this->validation, $this->id);
		}
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
			 * Add item button text for repeatable fields.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'add_item':
			
			/**
			 * Custom value for a checkbox field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'checkbox_value':
			
			/**
			 * Content added to the field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'content':
			
			/**
			 * Short description display with the field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'description':
			
			/**
			 * Output label displayed with the field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'label':
			
			/**
			 * Base name for the field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'name':
			
			/**
			 * Meta box option name.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'option_name':
			
			/**
			 * Sort items button text for repeatable fields.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'sort_items':
			
			/**
			 * Database table associated with the dropdown. Only works with Select fields.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'table':
			
			/**
			 * Current value for the field.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'value':
			
				return '';

			/**
			 * Additional attributes to add to the field.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'attributes':
			
			/**
			 * CSS classes added to the field wrapper.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'classes':
			
			/**
			 * Conditions for a field to be visible.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'conditional':
			
			/**
			 * Attributes for the main field wrapper.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'field_attributes':
			
			/**
			 * Fields associated with this field. Only works with Repeatable and Group fields.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'fields':
			
			/**
			 * CSS classes added to the field input element.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'input_classes':
			
			/**
			 * Field options. Only works with Select fields.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'options':
			
			/**
			 * Repeatable field options.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'repeatable_field':
			
			/**
			 * Tabs for the tabs field.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'tabs':
			
			/**
			 * Validation options for the field.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'validation':
			
				return array();

			/**
			 * True if the labels should be hidden from the field output.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'hide_labels':
			
			/**
			 * True if a clear DIV should be added after this field.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'include_clear':
			
			/**
			 * True if the repeatable items cannot be sorted.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_locked':
			
			/**
			 * True if the repeatable field is simple and should not contain sorting options.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_simple':
			
			/**
			 * True if the field is tall and the description should be displayed below the label.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_tall':
			
			/**
			 * True if the current field is a template.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'is_template':
			
				return false;

			/**
			 * Type of field to output.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'type':
			
				return 'text';
				
			/**
			 * Generated DOM ID.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'id':

				return $this->_generate_id();

			/**
			 * Generated field identifier attributes.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'input_attributes':

				$attributes = '';
				
				if (!empty($this->id))
				{
					$attributes = (strpos($this->id, '[__i__]') === false)
					? ' id="iem-' . $this->id . '" name="' . $this->id . '"'
					: ' data-iem-identifier="' . $this->id . '"';
				}
				
				if ($this->type != 'image')
				{
					$attributes .= $this->_general_attributes();
				}

				return $attributes;

			/**
			 * Generated label attributes.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'label_attribute':

				if (empty($this->id))
				{
					return '';
				}
				
				return (strpos($this->id, '[__i__]') === false)
				? ' for="iem-' . $this->id . '"'
				: ' data-iem-identifier="iem-' . $this->id . '"';
		}

		return parent::_default($name);
	}

	/**
	 * Generate the output for the field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean     $echo True if the field should be echoed.
	 * @return string/void       Generated field if $echo is false.
	 */
	public function output($echo = false)
	{
		$this->_push('classes', 'iem-field');
		$this->_push('classes', 'iem-field-' . str_replace('_', '-', $this->type));
		
		$output = '';

		if ($this->type == 'group')
		{
			$output = $this->_group_output();
		}
		else if ($this->type == 'repeatable')
		{
			$output = $this->_repeatable_output();
		}
		else if ($this->type == 'tabs')
		{
			$output = $this->_tabs_output();
		}
		else
		{
			$output = $this->_main_output();
		}

		if
		(
			!empty($output)
			&&
			in_array('iem-hidden', $this->classes)
		)
		{
			$output .= '<div class="iem-hidden iem-field-spacer"></div>';
		}

		if (!$echo)
		{
			return $output;
		}

		echo $output;
	}

	/**
	 * Group field output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated group field.
	 */
	private function _group_output()
	{
		$output = '<div class="iem-group">';

		foreach ($this->fields as $options)
		{
			$group_options = array
			(
				'hide_labels' => $this->hide_labels,
				'is_simple' => $this->is_simple,
				'is_template' => $this->is_template,
				'option_name' => $this->option_name
			);
			
			if
			(
				!$this->is_template
				&&
				is_array($this->value)
			)
			{
				$key = (isset($options['name']))
				? str_replace('[]', '', $options['name'])
				: '';
				
				$group_options['value'] =
				(
					empty($key)
					||
					!isset($this->value[$key])
				)
				? $this->value
				: $this->value[$key];
			}
			
			$group_field = new self(array_merge($options, $group_options));
			$output .= $group_field->output();
		}

		$output .= '<div class="iem-clear"></div>'
		. '</div>';
		
		return $this->_main_output($output);
	}
	
	/**
	 * Repeatable field output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated repeatable field.
	 */
	private function _repeatable_output()
	{
		if
		(
			!is_array($this->repeatable_field)
			||
			empty($this->repeatable_field)
		)
		{
			return '';
		}
		
		if ($this->is_locked)
		{
			$this->_push('classes', 'iem-repeatable-locked');
		}
		
		if ($this->is_simple)
		{
			$this->_push('classes', 'iem-repeatable-simple');
		}
		
		$output = '<div class="iem-repeatable">';
		
		$options = $this->repeatable_field;
		$options['is_simple'] = $this->is_simple;
		
		$options['option_name'] = (empty($this->option_name))
		? $this->name . '[__i__]'
		: $this->option_name . '[' . $this->name . '][__i__]';

		if (is_array($this->value))
		{
			foreach ($this->value as $i => $value)
			{
				$field = new self(array_merge
				(
					$options,

					array
					(
						'classes' => array('iem-repeatable-item'),
						'option_name' => str_replace('__i__', $i, $options['option_name']),
						'value' => $value,
						
						'field_attributes' => array
						(
							'data-iem-starting-index' => $i
						)
					)
				));

				$output .= $field->output();
			}
		}

		$template_field = new self(array_merge
		(
			$options,
			
			array
			(
				'classes' => array('iem-repeatable-item', 'iem-repeatable-template'),
				'is_template' => true
			)
		));
		
		$output .= '<div class="iem-hidden iem-field-spacer"></div>'
		. $template_field->output();

		$add_button_text = (empty($this->add_item))
		? __('Add Item', 'invoiceem')
		: $this->add_item;
		
		$sort_button = (empty($this->sort_items))
		? ''
		: '<button type="button" class="button iem-button iem-sort-button">' . $this->sort_items . '</button>';
		
		$add_new_field = new self(array
		(
			'classes' => array('iem-repeatable-tools'),
			'type' => 'html',
			
			'content' => '<button type="button" class="button button-primary iem-button iem-add-button">' . $add_button_text . '</button>'
			. $sort_button
		));
		
		$output .= $add_new_field->output()
		. '</div>';

		return $this->_main_output($output, ' data-iem-name="' . esc_attr($this->name) . '"');
	}
	
	/**
	 * Tabs field output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated tabs field.
	 */
	private function _tabs_output()
	{
		if
		(
			!is_array($this->tabs)
			||
			empty($this->tabs)
		)
		{
			return '';
		}
		
		$tab_buttons = $tab_content = '';
		$tab_count = 0;
		
		foreach ($this->tabs as $tab)
		{
			if
			(
				isset($tab['title'])
				&&
				!empty($tab['title'])
				&&
				isset($tab['fields'])
				&&
				is_array($tab['fields'])
				&&
				!empty($tab['fields'])
			)
			{
				$active_class = (++$tab_count == 1)
				? ' iem-tab-active'
				: '';
				
				$tab_buttons .= '<a href="javascript:;" class="iem-tab-link' . $active_class . '">' . $tab['title'] . '</a>';
				$tab_content .= '<div class="iem-tab' . $active_class . '">';
				
				foreach ($tab['fields'] as $field)
				{
					$field['option_name'] = $this->option_name;
					$iem_field = new InvoiceEM_Field($field);
					$tab_content .= $iem_field->output();
				}
				
				$tab_content .= '</div>';
			}
		}
		
		return '<div class="iem-tabs">'
		. '<div class="iem-tab-buttons iem-secondary-tab-wrapper">' . $tab_buttons . '</div>'
		. '<div class="iem-tab-content">' . $tab_content . '</div>'
		. '</div>';
	}

	/**
	 * Main field output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $field            Current field output.
	 * @param  string $field_attributes Additional attributes added to the field wrapper.
	 * @return string                   Generated field.
	 */
	private function _main_output($field = '', $field_attributes = '')
	{
		$output = '';

		if (empty($field))
		{
			if (!method_exists($this, '_field_' . $this->type))
			{
				$field = apply_filters(InvoiceEM_Constants::HOOK_FIELD . $this->type, $this->_field_text(), $this);
			}
			
			if
			(
				empty($field)
				&&
				method_exists($this, '_field_' . $this->type)
			)
			{
				$field = call_user_func(array($this, '_field_' . $this->type));
			}
		}

		if (!empty($field))
		{
			if (is_array($this->field_attributes))
			{
				foreach ($this->field_attributes as $name => $value)
				{
					$field_attributes .= ' ' . sanitize_key($name) . '="' . esc_attr($value) . '"';
				}
			}
			
			$field .= $this->_generate_condition_fields();
			$label_description = '';

			$description =
			(
				$this->hide_labels
				||
				empty($this->description)
			)
			? ''
			: '<div class="iem-description">'
			. '<label' . $this->label_attribute . '>' . $this->description . '</label>'
			. '</div>';

			if ($this->is_tall)
			{
				$label_description = $description;
				$description = '';
			}

			$label =
			(
				$this->hide_labels
				||
				empty($this->label)
			)
			? ''
			: '<div class="iem-field-label">'
			. $this->_generate_label()
			. $label_description
			. '</div>';

			$output = '<div class="' . esc_attr(implode(' ', $this->classes)) . '"' . $field_attributes . '>'
			. $label
			. '<div class="iem-field-input">'
			. $field
			. $description
			. '</div>'
			. '</div>';
			
			if ($this->include_clear)
			{
				$output .= '<div class="iem-clear"></div>';
			}
		}

		return $output;
	}

	/**
	 * Generate a checkbox field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated checkbox field.
	 */
	private function _field_checkbox()
	{
		if (empty($this->id))
		{
			return '';
		}
		
		if ($this->checkbox_value === '')
		{
			$this->checkbox_value = '1';
		}
		
		if ($this->value === true)
		{
			$this->value = '1';
		}
		else if (is_array($this->value))
		{
			$this->value = in_array($this->checkbox_value, $this->value)
			? $this->checkbox_value
			: '';
		}

		$checkbox = '<input' . $this->input_attributes . ' type="checkbox" value="' . esc_attr($this->checkbox_value) . '"' . $this->get_input_classes() . ' ' . checked($this->checkbox_value, $this->value, false) . ' />';

		if
		(
			is_array($this->attributes)
			&&
			isset($this->attributes['placeholder'])
		)
		{
			$label_attr =
			(
				!$this->hide_labels
				||
				empty($this->description)
			)
			? ''
			: ' iem-tooltip" data-iem-tooltip="' . esc_attr($this->description);
			
			$checkbox = '<label class="iem-description' . $label_attr . '">' . $checkbox . '<span>' . $this->attributes['placeholder'] . '</span></label>';
		}

		return $checkbox;
	}
	
	/**
	 * Generate a discount field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated discount field.
	 */
	private function _field_discount()
	{
		$this->type = 'text';
		
		$amount_class = ' button-primary';
		$percentage_class = '';
		
		if
		(
			!empty($this->value)
			&&
			!is_numeric($this->value)
		)
		{
			$percentage_class = $amount_class;
			$amount_class = '';
			
			$this->value = preg_replace('/[^\d.]/u', '', $this->value);
		}
		
		return '<div class="iem-discount-field">'
		. '<button type="button" class="button' . $amount_class . ' iem-button iem-discount-amount">--</button>'
		. '<button type="button" class="button' . $percentage_class . ' iem-button iem-discount-percentage">%</button>'
		. '<div class="iem-discount-field-spinner">'
		. $this->_field_text()
		. '</div>'
		. '</div>';
	}

	/**
	 * Generate an HTML field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated HMTL field.
	 */
	private function _field_html()
	{
		return '<div class="iem-html' . $this->get_input_classes(false) . '">'
		. wpautop(do_shortcode($this->content))
		. '</div>';
	}

	/**
	 * Generate an image field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated image field.
	 */
	private function _field_image()
	{
		if (empty($this->id))
		{
			return '';
		}

		$image = $disabled = '';

		if
		(
			empty($this->value)
			||
			!is_numeric($this->value)
		)
		{
			$disabled = ' disabled="disabled"';
		}
		else
		{
			$image =  wp_get_attachment_image($this->value, 'medium');
		}

		return '<div class="iem-image-preview">' . $image . '</div>'
		. '<div class="iem-image-actions">'
		. '<button type="button" class="button iem-button iem-add-button"' . $this->_general_attributes() . '>' . __('Add or Replace Image', 'invoiceem') . '</button>'
		. '<button type="button" class="button iem-button iem-remove-button"' . $disabled . '>' . __('Remove Image', 'invoiceem') . '</button>'
		. '</div>'
		. '<input' . $this->input_attributes . ' type="hidden" value="' . esc_attr($this->value) . '"' . $this->get_input_classes() . ' />';
	}

	/**
	 * Generate a select field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated select field.
	 */
	private function _field_select()
	{
		if (empty($this->id))
		{
			return '';
		}

		$has_table = (!empty($this->table));
		$ajax_action = '';
		
		if ($has_table)
		{
			$ajax_action = ' data-iem-ajax-action="' . InvoiceEM_Constants::HOOK_SELECT2 . esc_attr($this->table) . '"';
			
			if
			(
				empty($this->options)
				&&
				is_array($this->value)
				&&
				!empty($this->value)
			)
			{
				$this->options = $this->value;
				$this->value = array_keys($this->options)[0];
			}
		}

		$field = '<select' . $this->input_attributes . $ajax_action . $this->get_input_classes() . '>';
		
		if (!empty($this->options))
		{
			foreach ($this->options as $value => $label)
			{
				if (is_array($label))
				{
					$field .= '<optgroup label="' . esc_attr($value) . '">';
					
					foreach ($label as $group_value => $group_label)
					{
						$field .= '<option value="' . esc_attr($group_value) . '" ' . selected($this->value, $group_value, false) . '>' . $group_label . '</option>';
					}
					
					$field .= '</optgroup>';
				}
				else
				{
					$field .= '<option value="' . esc_attr($value) . '" ' . selected($this->value, $value, false) . '>' . $label . '</option>';
				}
			}
		}

		$field .= '</select>';

		if
		(
			!$this->is_simple
			&&
			!isset($_GET[$this->name])
			&&
			$this->base->cache->action != InvoiceEM_Constants::ACTION_LIST
			&&
			$has_table
			&&
			current_user_can(InvoiceEM_Constants::CAP_EDIT . $this->table)
		)
		{
			$disabled = (empty($this->value))
			? ' disabled="disabled"'
			: '';

			$actions = '<button type="button" tabindex="-1"' . $disabled . ' class="button iem-button iem-edit-button iem-iframe-button" data-iem-src="' . esc_url(admin_url('admin.php?page=' . InvoiceEM_Constants::TOKEN . '_' . esc_attr($this->table) . '&action=' . InvoiceEM_Constants::ACTION_EDIT . '&' . $this->name . '=__id__&' . InvoiceEM_Constants::IFRAME_NONCE . '=' . wp_create_nonce(InvoiceEM_Constants::IFRAME_NONCE))) . '">' . __('Edit Selected', 'invoiceem') . '</button>';
			
			if
			(
				(
					(
						$this->table != InvoiceEM_Constants::TABLE_COUNTRIES
						&&
						$this->table != InvoiceEM_Constants::TABLE_CURRENCIES
					)
					||
					$this->base->cache->has_regional_plus
				)
				&&
				current_user_can(InvoiceEM_Constants::CAP_ADD . $this->table)
			)
			{
				$actions .= '<a href="' . esc_url(admin_url('admin.php?page=' . InvoiceEM_Constants::TOKEN . '_' . esc_attr($this->table) . '&action=' . InvoiceEM_Constants::ACTION_ADD . '&' . InvoiceEM_Constants::IFRAME_NONCE . '=' . wp_create_nonce(InvoiceEM_Constants::IFRAME_NONCE))) . '" tabindex="-1" class="button iem-button iem-iframe-button">' . __('Add New', 'invoiceem') . '</a>';
			}

			if (!empty($actions))
			{
				$this->is_tall = true;

				$field .= '<div class="iem-field-actions">' . $actions . '</div>';
			}
		}

		return $field;
	}

	/**
	 * Generate a submit button.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated submit button.
	 */
	private function _field_submit()
	{
		$this->content = (empty($this->content))
		? __('Submit', 'invoiceem')
		: $this->content;

		return '<button' . $this->input_attributes . ' type="submit" disabled="disabled" class="button button-large button-primary iem-button' . $this->get_input_classes(false) . '"><span>' . $this->content . '</span></button>';
	}

	/**
	 * Generate a text field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated text field.
	 */
	private function _field_text()
	{
		if (empty($this->id))
		{
			return '';
		}
		
		if (is_array($this->value))
		{
			$this->value = '';
		}

		$input_type = esc_attr($this->type);
		$alt_field = '';
		$max_width_open = $max_width_close = '';

		if
		(
			is_array($this->attributes)
			&&
			isset($this->attributes['maxlength'])
			&&
			is_numeric($this->attributes['maxlength'])
		)
		{
			$max_width_open = '<div style="max-width: ' . ($this->attributes['maxlength'] * 14 + 32) . 'px;">';
			$max_width_close = '</div>';
		}

		$this->type = 'text';

		return $max_width_open
		. $alt_field
		. '<input' . $this->input_attributes . ' type="' . $input_type . '" value="' . esc_attr($this->value) . '"' . $this->get_input_classes() . ' />'
		. $max_width_close;
	}

	/**
	 * Generate a textarea field.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated textarea field.
	 */
	private function _field_textarea()
	{
		return (empty($this->id))
		? ''
		: '<textarea' . $this->input_attributes . $this->get_input_classes() . '>' . esc_textarea($this->value) . '</textarea>';
	}

	/**
	 * Get the input class(es).
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  boolean $add_attr True if the class attribute should be added.
	 * @return string            Generated field class(es).
	 */
	public function get_input_classes($add_attr = true)
	{
		if ($this->is_template)
		{
			$this->_push('input_classes', 'iem-input-template');
		}

		if (!empty($this->input_classes))
		{
			$classes = InvoiceEM_Utilities::check_array($this->input_classes);
			$classes = esc_attr(implode(' ', $classes));

			return ($add_attr)
			? ' class="' . $classes . '"'
			: ' ' . $classes;
		}

		return '';
	}

	/**
	 * Assemble the general attributes.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Assembled attributes.
	 */
	private function _general_attributes()
	{
		$is_checkbox = ($this->type == 'checkbox');
		$raw_attributes = InvoiceEM_Utilities::check_array($this->attributes);
		$attributes = '';
		
		if
		(
			$this->hide_labels
			&&
			!$is_checkbox
			&&
			!empty($this->description)
			&&
			!isset($raw_attributes['title'])
		)
		{
			$raw_attributes['data-iem-tooltip'] = $this->description;
			
			$this->_push('input_classes', 'iem-tooltip');
		}

		foreach ($raw_attributes as $name => $value)
		{
			if ($value != '')
			{
				if
				(
					$this->type == 'select'
					&&
					$name == 'placeholder'
				)
				{
					$attributes .= ' data-allow-clear="true" data-placeholder="' . esc_attr($value) . '"';
				}
				else if
				(
					!$is_checkbox
					||
					$name != 'placeholder'
				)
				{
					$attributes .= ' ' . sanitize_key($name) . '="' . esc_attr($value) . '"';
				}
			}
		}

		return $attributes;
	}

	/**
	 * Generate contition fields.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated condition fields.
	 */
	private function _generate_condition_fields()
	{
		$output = '';

		if
		(
			is_array($this->conditional)
			&&
			!empty($this->conditional)
		)
		{
			foreach ($this->conditional as $condition)
			{
				if
				(
					is_array($condition)
					&&
					isset($condition['field'])
					&&
					isset($condition['value'])
				)
				{
					if (!isset($condition['compare']))
					{
						$condition['compare'] = '=';
					}

					$output .= '<div class="iem-hidden iem-condition" '
					. 'data-iem-conditional="' . $this->id . '" '
					. 'data-iem-field="' . esc_attr($this->_generate_id($condition['field'])) . '" '
					. 'data-iem-value="' . esc_attr($condition['value']) . '" '
					. 'data-iem-compare="' . esc_attr($condition['compare']) . '">'
					. '</div>';
				}
			}
		}

		return $output;
	}

	/**
	 * Generate a field ID.
	 *  
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  string $name The base name for the field. If excluded the default field name will be used.
	 * @return string       Generated field ID.
	 */
	private function _generate_id($name = '')
	{
		$name = (empty($name))
		? $this->name
		: $name;

		return
		(
			empty($name)
			||
			empty($this->option_name)
		)
		? $name
		: $this->option_name . str_replace('[]]', '][]', '[' . $name . ']');
	}
	
	/**
	 * Generate the label output.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @return string Generated label output.
	 */
	private function _generate_label()
	{
		if
		(
			empty($this->validation)
			&&
			is_array($this->input_classes)
			&&
			in_array('required', $this->input_classes)
		)
		{
			$this->validation = true;
		}
		
		return (empty($this->label))
		? ''
		: '<label' . $this->label_attribute . '><strong>' . InvoiceEM_Output::required_asterisk($this->label, $this->validation) . '</strong></label>';
	}
}
