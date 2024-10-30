<?php
/*!
 * Plugin output functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Output
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement the output functionality.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Output
{
	/**
	 * Admin notices to display on the admin page.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @var    string
	 */
	public static $admin_notices = '';

	/**
	 * Admin page secondary tabs.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    array
	 */
	private static $_secondary_tabs = array();

	/**
	 * Admin page tabs.
	 *
	 * @since 1.0.0
	 *
	 * @access private static
	 * @var    array
	 */
	private static $_tabs = array();

	/**
	 * Generate and add an admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string       $message        Message to display in the admin notice.
	 * @param  array/string $class          Optional CSS class(es) to add to the admin notice.
	 * @param  boolean      $is_dismissible True if the admin notice should be dismissible.
	 * @return void
	 */
	public static function add_admin_notice($message, $class = array(InvoiceEM_Constants::NOTICE_SUCCESS), $is_dismissible = true)
	{
		self::$admin_notices .= self::admin_notice($message, $class, $is_dismissible);
	}

	/**
	 * Add an admin page secondary tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $menu_parent Parent page for the admin page.
	 * @param  string $menu_slug   Menu slug for the admin page.
	 * @param  string $tab_slug    Slug for the secondary tab.
	 * @param  string $page_title  Title for the admin page tab.
	 * @return void
	 */
	public static function add_secondary_tab($menu_parent, $menu_slug, $tab_slug, $title)
	{
		self::$_secondary_tabs[] = array
		(
			'menu_parent' => $menu_parent,
			'menu_slug' => $menu_slug,
			'tab_slug' => $tab_slug,
			'title' => $title
		);
	}

	/**
	 * Add an admin page tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $menu_parent Parent page for the admin page.
	 * @param  string $menu_slug   Menu slug for the admin page.
	 * @param  string $page_title  Title for the admin page tab.
	 * @return void
	 */
	public static function add_tab($menu_parent, $menu_slug, $title)
	{
		self::$_tabs[] = array
		(
			'menu_parent' => $menu_parent,
			'menu_slug' => $menu_slug,
			'title' => $title
		);
	}

	/**
	 * Output an admin form page.
	 *
	 * @since 1.0.6 Removed upgrade notices filter.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $heading     Heading displayed at the top of the admin form page.
	 * @param  string $option_name Option name to generate the admin form page for.
	 * @param  string $add_cap     Capability name for adding an item, if applicable.
	 * @param  string $active_tab  Slug for the active secondary tab.
	 * @return void
	 */
	public static function admin_form_page($heading, $option_name = '', $add_cap = '', $active_tab = '')
	{
		$iem = InvoiceEM();
		
		self::$admin_notices = self::admin_notice(__('Please correct the error(s) below.', 'invoiceem'), array(InvoiceEM_Constants::NOTICE_ERROR, 'iem-hidden', 'iem-validation-error'), false)
		. self::$admin_notices;

		echo '<div class="wrap">';

		self::admin_nav_bar($heading, $add_cap, $active_tab);
		
		if (!empty($option_name))
		{
			$option_name = sanitize_key($option_name);
		}

		$has_option_name = (!empty($option_name));
		$screen = $iem->cache->screen;
		$columns = $screen->get_columns();

		$action = ($has_option_name)
		? 'options.php'
		: basename($_SERVER['REQUEST_URI']);
		
		if (empty($columns))
		{
			$columns = 2;
		}

		echo '<form action="' . esc_url(admin_url($action)) . '" method="post" class="iem-form">';

		if ($has_option_name)
		{
			settings_fields($option_name);

			echo '<input name="tab" type="hidden" value="' . esc_attr($active_tab) . '" />';
		}
		else
		{
			wp_nonce_field(InvoiceEM_Utilities::nonce_action($iem->cache->action), InvoiceEM_Constants::NONCE, false);

			if ($iem->cache->is_iframe)
			{
				wp_nonce_field(InvoiceEM_Constants::IFRAME_NONCE, InvoiceEM_Constants::IFRAME_NONCE, false);
			}
		}

		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);

		echo '<div id="poststuff">'
		. '<div id="post-body" class="metabox-holder columns-' . $columns . '">'
		. '<div id="postbox-container-1" class="postbox-container">';

		do_meta_boxes($screen->id, 'side', '');

		echo '</div>'
		. '<div id="iem-primary-wrapper">'
		. '<div id="postbox-container-2" class="postbox-container">';

		do_action(InvoiceEM_Constants::HOOK_INLINE_CONTENT);
		do_meta_boxes($screen->id, 'advanced', '');
		do_meta_boxes($screen->id, 'normal', '');

		echo '</div>'
		. '</div>'
		. '<div class="iem-clear"></div>'
		. '</div>'
		. '</div>'
		. '</form>'
		. '</div>';
	}

	/**
	 * Output an admin list page.
	 *
	 * @since 1.0.6 Removed upgrade notices filter.
	 * @since 1.0.5 Added active tab functionality.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $heading     Heading displayed at the top of the admin form page.
	 * @param  mixed  $list_object Object used to display the list.
	 * @param  string $add_cap     Capability name for adding an item, if applicable.
	 * @param  string $active_tab  Slug for the active secondary tab.
	 * @return void
	 */
	public static function admin_list_page($heading, $list_object, $add_cap = '', $active_tab = '')
	{
		if (method_exists($list_object, 'display'))
		{
			echo '<div id="iem-primary-wrapper" class="wrap">';

			self::admin_nav_bar($heading, $add_cap, $active_tab);

			$list_object->display();

			echo '</div>';
		}
	}

	/**
	 * Output the admin page nav bar.
	 *
	 * @since 1.0.6 Added admin notices.
	 * @since 1.0.5 Modified secondary tab output to exclude 'list' tab.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string $heading    Heading displayed in the nav bar.
	 * @param  string $add_cap    Capability name for adding an item, if applicable.
	 * @param  string $active_tab Slug for the active secondary tab.
	 * @return void
	 */
	public static function admin_nav_bar($heading, $add_cap = '', $active_tab = '')
	{
		$iem = InvoiceEM();
		$buttons = '';
		
		if (isset($_GET['settings-updated']))
		{
			self::add_admin_notice(__('Settings saved.', 'invoiceem'));
		}

		echo '<div class="iem-nav">'
		. '<div class="iem-nav-title">'
		. '<h1>'
		. '<strong>' . $iem->cache->plugin_data['Name'] . '</strong> | ' . $heading;

		if ($iem->cache->is_iframe)
		{
			$buttons .= '<a href="javascript:;" class="button button-primary iem-button iem-iframe-close">' . __('Cancel', 'invoiceem') . '</a>';
		}
		else if (!empty($add_cap))
		{
			$current_action = $iem->cache->action;
			$can_add = current_user_can($add_cap);

			if ($current_action == InvoiceEM_Constants::ACTION_ADD)
			{
				if ($can_add)
				{
					$buttons .= '<a href="' . $iem->cache->list_url . '" class="button button-primary iem-button">' . __('Cancel', 'invoiceem') . '</a>';
				}
			}
			else
			{
				if ($current_action != InvoiceEM_Constants::ACTION_LIST)
				{
					$buttons .= '<a href="' . $iem->cache->list_url . '" class="button button-primary iem-button">' . __('Back', 'invoiceem') . '</a>';
				}

				if ($can_add)
				{
					$buttons .= '<a href="'
					. InvoiceEM_Utilities::modify_admin_url
					(
						array
						(
							'action' => InvoiceEM_Constants::ACTION_ADD
						),

						false,
						$iem->cache->list_url
					)
					. '" class="button button-primary iem-button">' . __('Add New', 'invoiceem') . '</a>';
				}
			}
		}

		if (!empty($buttons))
		{
			echo '<span class="iem-buttons">' . $buttons . '</span>';
		}

		echo '</h1>'
		. '<div class="iem-clear"></div>'
		. '</div>';

		if (!$iem->cache->is_iframe)
		{
			$has_secondary_tabs = (!empty(self::$_secondary_tabs));

			if (!empty(self::$_tabs))
			{
				echo '<div class="iem-tab-wrapper">';

				foreach (self::$_tabs as $tab)
				{
					$active_class = ($iem->cache->current_page == $tab['menu_slug'])
					? ' iem-tab-active'
					: '';
					
					if
					(
						!empty($active_class)
						&&
						$has_secondary_tabs
					)
					{
						$active_class .= '-no-arrow';
					}

					echo '<a href="' . esc_url(admin_url($tab['menu_parent'] . '?page=' . $tab['menu_slug'])) . '" class="iem-tab' . $active_class . '">' . $tab['title'] . '</a>';
				}

				echo '</div>';
			}

			if ($has_secondary_tabs)
			{
				echo '<div class="iem-secondary-tab-wrapper">';

				foreach (self::$_secondary_tabs as $tab)
				{
					$tab_query_string =
					(
						empty($tab['tab_slug'])
						||
						$tab['tab_slug'] == InvoiceEM_Settings_General::TAB_SLUG
						||
						$tab['tab_slug'] == InvoiceEM_Constants::ACTION_LIST
					)
					? ''
					: '&tab=' . $tab['tab_slug'];

					$active_class =
					(
						$iem->cache->current_page == $tab['menu_slug']
						&&
						$active_tab == $tab['tab_slug']
					)
					? ' iem-tab-active'
					: '';

					echo '<a href="' . esc_url(admin_url($tab['menu_parent'] . '?page=' . $tab['menu_slug'] . $tab_query_string)) . '" class="iem-secondary-tab' . $active_class . '">' . $tab['title'] . '</a>';
				}

				echo '</div>';
			}
		}

		echo '</div>'
		. '<hr class="wp-header-end" />'
		. self::$admin_notices;
	}

	/**
	 * Generate an admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string       $message        Message to display in the admin notice.
	 * @param  array/string $class          Optional CSS class(es) to add to the admin notice.
	 * @param  boolean      $is_dismissible True if the admin notice should be dismissible.
	 * @return string                       Generated admin notice.
	 */
	public static function admin_notice($message, $class = array(InvoiceEM_Constants::NOTICE_SUCCESS), $is_dismissible = true)
	{
		$classes = array();
		
		if (is_array($class))
		{
			$classes = $class;
		}
		else if (!empty($class))
		{
			$classes = explode(' ', $class);
		}

		if ($is_dismissible)
		{
			$classes[] = 'is-dismissible';
		}

		$admin_notice = '<div class="notice iem-notice';
		
		if (!empty($classes))
		{
			$admin_notice .= ' ' . esc_attr(implode(' ', $classes));
		}

		$admin_notice .= '">'
		. wpautop('<strong>' . $message . '</strong>')
		. '</div>';

		return $admin_notice;
	}
	
	/**
	 * Output the client details for views.
	 *
	 * @since 1.0.5
	 *
	 * @access public static
	 * @param  InvoiceEM_Client $client Client associated with the current view.
	 * @return void
	 */
	public static function client_to($client)
	{
		$iem = InvoiceEM();
		$to = array('<strong>' . $iem->settings->translation->get_label('to') . '</strong>', $client->client_name);

		if (!empty($client->address))
		{
			$to[] = nl2br($client->address);
		}

		if (!empty($client->phone))
		{
			$to[] = sprintf
			(
				$iem->settings->translation->get_label('phone'),
				$client->phone
			);
		}

		if (!empty($client->fax))
		{
			$to[] = sprintf
			(
				$iem->settings->translation->get_label('fax'),
				$client->fax
			);
		}

		echo implode('<br />', $to);
	}
	
	/**
	 * Output the company information for invoices and emails.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function company_information()
	{
		$iem = InvoiceEM();
		$company = array();

		if (!empty($iem->settings->company->address))
		{
			$company[] = nl2br($iem->settings->company->address);
		}

		if (!empty($iem->settings->company->phone))
		{
			$company[] = sprintf
			(
				$iem->settings->translation->get_label('phone'),
				$iem->settings->company->phone
			);
		}

		if (!empty($iem->settings->company->fax))
		{
			$company[] = sprintf
			(
				$iem->settings->translation->get_label('fax'),
				$iem->settings->company->fax
			);
		}

		$email = (empty($iem->settings->company->email))
		? get_option('admin_email')
		: $iem->settings->company->email;

		$email = sanitize_email($email);

		$company[] = '<a href="mailto:' . $email . '" style="color: #0073aa;">' . $email . '</a>';

		echo implode('<br />', $company);
	}
	
	/**
	 * Output the company logo or name.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $image_size Image size for the company logo.
	 * @param  integer $max_height Maximum height for the company logo.
	 * @param  integer $max_width  Maximum width for the company logo.
	 * @return void
	 */
	public static function company_logo($image_size = 'medium', $max_height = '', $max_width = '')
	{
		$iem = InvoiceEM();
		$company_name = $iem->settings->company->company_name;
		
		$max_height = (empty($max_height))
		? ''
		: ' max-height: ' . $max_height . ';';
		
		$max_width = (empty($max_width))
		? ''
		: ' max-width: ' . $max_width . ';';
		
		$logo = (empty($iem->settings->company->logo_id))
		? ''
		: wp_get_attachment_image_src($iem->settings->company->logo_id, $image_size);

		if (empty($company_name))
		{
			$company_name = get_bloginfo('site_name');
		}
		
		if (empty($logo))
		{
			$company_name = '<strong class="iem-company">' . $company_name . '</strong>';
		}
		else
		{
			$company_name = '<img src="' . esc_url($logo[0]) . '" alt="' . esc_attr($company_name) . '" style="height: auto; ' . $max_height . $max_width . ' width: auto;" />';
		}

		echo $company_name;
	}
	
	/**
	 * Generate an invoice URL.
	 *
	 * @since 1.0.5 Modified URL to work better with all permalinks.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  integer $invoice_id ID of the invoice to generate a URL for.
	 * @return string             Generated invoice URL.
	 */
	public static function invoice_url($invoice_id)
	{
		$url = '/' . InvoiceEM()->cache->view_url_base
		. strtr
		(
			InvoiceEM_Utilities::encrypt(maybe_serialize(array
			(
				'i' => $invoice_id
			))),

			'+/=',
			'-_.'
		);
		
		$url .= (strpos($url, '?') === false)
		? '/'
		: '';
		
		return home_url($url);
	}

	/**
	 * Add a required asterisk to a label if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  string  $label      Label to update with an asterisk.
	 * @param  array   $validation Array of validation rules for the object.
	 * @param  boolean $add_class  True if the asterisk should have the required class added.
	 * @return string              Modified label.
	 */
	public static function required_asterisk($label, $validation, $add_class = true)
	{
		if
		(
			$validation === true
			||
			(
				is_array($validation)
				&&
				isset($validation['required'])
				&&
				$validation['required']
			)
		)
		{
			$label = sprintf
			(
				_x('%1$s *', 'Required Field Label', 'invoiceem'),
				$label
			);
		}

		return ($add_class)
		? str_replace('*', '<span class="iem-required">*</span>', $label)
		: $label;
	}
}
