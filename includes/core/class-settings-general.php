<?php
/*!
 * General settings functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage General Settings
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the general settings functionality.
 *
 * @since 1.0.0
 *
 * @uses InvoiceEM_Settings_Wrapper
 */
final class InvoiceEM_Settings_General extends InvoiceEM_Settings_Wrapper
{
	/**
	 * Tab slug for the general settings.
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const TAB_SLUG = 'general';

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
	 * @since 1.0.4 Added Show Generated By setting.
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
			 * True if the Generated By line should be added to invoices and emails.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case 'show_generated_by':
			
			/**
			 * True if plugin settings should be deleted.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case InvoiceEM_Constants::SETTING_DELETE_SETTINGS:
			
			/**
			 * True if plugin settings should be deleted.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case InvoiceEM_Constants::SETTING_DELETE_SETTINGS . '_unconfirmed':
			
			/**
			 * True if plugin user roles and capabilities should be deleted.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case InvoiceEM_Constants::SETTING_DELETE_ROLES:
			
			/**
			 * True if plugin user roles and capabilities should be deleted.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean
			 */
			case InvoiceEM_Constants::SETTING_DELETE_ROLES . '_unconfirmed':
			
				return false;
		}

		return parent::_default($name);
	}

	/**
	 * Add the general settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function settings_tabs()
	{
		parent::_add_tab(__('General', 'invoiceem'));
	}

	/**
	 * Sanitize the general settings.
	 *
	 * @since 1.0.4 Added sanitization for the Show Generated By setting.
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $input Raw general settings array.
	 * @return array        Sanitized general settings array.
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
		
		$input['show_generated_by'] =
		(
			isset($input['show_generated_by'])
			&&
			!empty($input['show_generated_by'])
		);

		foreach ($input as $name => $value)
		{
			if ($name == InvoiceEM_Constants::SETTING_DELETE_SETTINGS)
			{
				$delete_settings_unconfirmed = InvoiceEM_Constants::SETTING_DELETE_SETTINGS . '_unconfirmed';

				$input[$name] =
				(
					isset($input[$delete_settings_unconfirmed])
					&&
					$input[$delete_settings_unconfirmed]
				)
				? $value
				: false;
			}
			else if ($name == InvoiceEM_Constants::SETTING_DELETE_ROLES)
			{
				$delete_roles_unconfirmed = InvoiceEM_Constants::SETTING_DELETE_ROLES . '_unconfirmed';

				$input[$name] =
				(
					isset($input[$delete_roles_unconfirmed])
					&&
					$input[$delete_roles_unconfirmed]
				)
				? $value
				: false;
			}
			else
			{
				$input[$name] = sanitize_text_field($value);
			}
		}

		return $input;
	}

	/**
	 * Add meta boxes to the settings page.
	 *
	 * @since 1.0.4 Added Show Generated By setting.
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes()
	{
		$plugin_label = $this->base->cache->plugin_data['Name'];
		
		$view_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'view_settings',
			'option_name' => $this->_option_name,
			'title' => __('View Settings', 'invoiceem')
		));
		
		$view_settings_box->add_field(array
		(
			'description' => __('If checked, a small \'Generated By\' line is added to invoices, emails and extension views.', 'invoiceem'),
			'label' => __('Show Generated By', 'invoiceem'),
			'name' => 'show_generated_by',
			'type' => 'checkbox',
			'value' => $this->show_generated_by
		));
		
		$save_all_field = array
		(
			'content' => __('Save All General Settings', 'invoiceem'),
			'type' => 'submit'
		);
		
		$view_settings_box->add_field($save_all_field);
		
		$uninstall_settings_box = new InvoiceEM_Meta_Box(array
		(
			'context' => 'normal',
			'id' => 'uninstall_settings',
			'option_name' => $this->_option_name,
			'title' => __('Uninstall Settings', 'invoiceem')
		));

		$delete_settings_description = sprintf
		(
			__('Delete settings for %1$s when the plugin is uninstalled.', 'invoiceem'),
			$plugin_label
		);

		$delete_settings_label = __('Delete Plugin Settings', 'invoiceem');
		$delete_settings_unconfirmed = InvoiceEM_Constants::SETTING_DELETE_SETTINGS . '_unconfirmed';
		$delete_settings_value = $this->{InvoiceEM_Constants::SETTING_DELETE_SETTINGS};

		$uninstall_settings_box->add_field(array
		(
			'description' => $delete_settings_description,
			'label' => $delete_settings_label,
			'name' => $delete_settings_unconfirmed,
			'type' => 'checkbox',
			'value' => $delete_settings_value,

			'classes' => ($delete_settings_value)
			? array('iem-hidden')
			: array()
		));

		$uninstall_settings_box->add_field(array
		(
			'description' => $delete_settings_description,
			'name' => InvoiceEM_Constants::SETTING_DELETE_SETTINGS,
			'type' => 'checkbox',
			'value' => $delete_settings_value,

			'classes' => ($delete_settings_value)
			? array()
			: array('iem-confirmation iem-hidden'),

			'conditional' => array
			(
				array
				(
					'field' => $delete_settings_unconfirmed,
					'value' => '1'
				)
			),

			'label' => ($delete_settings_value)
			? $delete_settings_label
			: __('Confirm Delete Plugin Settings', 'invoiceem')
		));

		$delete_roles_description = sprintf
		(
			__('Delete user roles and capabilities for %1$s when the plugin is uninstalled.', 'invoiceem'),
			$plugin_label
		);

		$delete_roles_label = __('Delete Plugin Roles', 'invoiceem');
		$delete_roles_unconfirmed = InvoiceEM_Constants::SETTING_DELETE_ROLES . '_unconfirmed';
		$delete_roles_value = $this->{InvoiceEM_Constants::SETTING_DELETE_ROLES};

		$uninstall_settings_box->add_field(array
		(
			'description' => $delete_roles_description,
			'label' => $delete_roles_label,
			'name' => $delete_roles_unconfirmed,
			'type' => 'checkbox',
			'value' => $delete_roles_value,

			'classes' => ($delete_roles_value)
			? array('iem-hidden')
			: array()
		));

		$uninstall_settings_box->add_field(array
		(
			'description' => $delete_roles_description,
			'name' => InvoiceEM_Constants::SETTING_DELETE_ROLES,
			'type' => 'checkbox',
			'value' => $delete_roles_value,

			'classes' => ($delete_roles_value)
			? array()
			: array('iem-confirmation iem-hidden'),

			'conditional' => array
			(
				array
				(
					'field' => $delete_roles_unconfirmed,
					'value' => '1'
				)
			),

			'label' => ($delete_roles_value)
			? $delete_roles_label
			: __('Confirm Delete Plugin Roles', 'invoiceem')
		));

		$uninstall_settings_box->add_field($save_all_field);

		InvoiceEM_Meta_Box::side_meta_boxes();
	}
}