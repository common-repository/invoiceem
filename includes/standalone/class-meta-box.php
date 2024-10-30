<?php
/*!
 * Meta box functionality.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Meta Box
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
final class InvoiceEM_Meta_Box extends InvoiceEM_Wrapper
{
	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $options Optional options for the meta box.
	 * @return void
	 */
	public function __construct($options = array())
	{
		parent::__construct();
		
		$this->_set_properties($options);

		if
		(
			is_callable($this->callback)
			&&
			!empty($this->id)
			&&
			$this->title != ''
		)
		{
			$this->id = InvoiceEM_Constants::TOKEN . '_meta_box_' . $this->id;

			add_action('add_meta_boxes', array($this, 'add_meta_box'));
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
			 * Function used to populate the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var function
			 */
			case 'callback':
			
				return array($this, 'callback');

			/**
			 * Data that should be set as the $args property of the box array.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'callback_args':
			
				return null;

			/**
			 * CSS classes added to the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'classes':
			
			/**
			 * Field displayed in the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			case 'fields':
			
				return array();

			/**
			 * Context within the screen where the boxes should display.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'context':
			
				return 'advanced';

			/**
			 * Base ID for the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'id':
			
			/**
			 * Option name for the fields in the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'option_name':
			
			/**
			 * Title displayed in the meta box.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'title':
			
				return '';

			/**
			 * Priority within the context where the boxes should show.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			case 'priority':
			
				return 'default';
		}
		
		return parent::_default($name);
	}

	/**
	 * Add the meta box to the page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_box()
	{
		$title = esc_html($this->title);
		
		add_meta_box($this->id, $title, $this->callback, $this->base->cache->screen, $this->context, $this->priority, $this->callback_args);

		add_filter('postbox_classes_' . esc_attr($this->base->cache->screen->id) . '_' . esc_attr($this->id), array($this, 'postbox_classes'));
	}

	/**
	 * The default callback that is fired for the meta box when one isn't provided.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function callback()
	{
		$this->fields = InvoiceEM_Utilities::check_array($this->fields);

		foreach ($this->fields as $field)
		{
			if (is_a($field, 'InvoiceEM_Field'))
			{
				$field->output(true);
			}
		}

		wp_nonce_field($this->id, $this->id . '_nonce', false);
	}

	/**
	 * Add additional classes to a meta box.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $classes Current meta box classes.
	 * @return array          Modified meta box classes.
	 */
	public function postbox_classes($classes)
	{
		$add_classes = InvoiceEM_Utilities::check_array($this->classes);
		
		array_unshift($add_classes, 'iem-meta-box');

		return array_merge($classes, $add_classes);
	}

	/**
	 * Add a field to the meta box.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $options Options for the field to add.
	 * @return void
	 */
	public function add_field($options)
	{
		$options = InvoiceEM_Utilities::check_array($options);
		$options['option_name'] = $this->option_name;

		$this->_push('fields', new InvoiceEM_Field($options));
	}

	/**
	 * Generate the side meta boxes.
	 *
	 * @since 1.0.6 WPEngine ad change.
	 * @since 1.0.3 WPEngine ad change.
	 * @since 1.0.0
	 *
	 * @access public static
	 * @param  array $support_box_fields Optional fields to add to the support box.
	 * @return void
	 */
	public static function side_meta_boxes($support_box_fields = array())
	{
		$support_box = new self(array
		(
			'classes' => array('iem-meta-box-locked'),
			'context' => 'side',
			'id' => 'support',
			'title' => __('Support', 'invoiceem')
		));

		$support_box->add_field(array
		(
			'type' => 'html',

			'content' => __('Plugin developed by', 'invoiceem') . '<br />'
			. '<a href="https://robertnoakes.com/" target="_blank" rel="noopener noreferrer"><img src="' . InvoiceEM()->cache->asset_path('images', 'robert-noakes.png') . '" height="67" width="514" alt="Robert Noakes" class="robert-noakes" /></a>'
		));
		
		if
		(
			empty($support_box_fields)
			||
			!is_array($support_box_fields)
		)
		{
			$support_box_fields = array
			(
				array
				(
					'type' => 'html',

					'content' => __('Running into issues with the plugin?', 'invoiceem') . '<br />'
					. '<a href="' . InvoiceEM_Constants::URL_SUPPORT . '" target="_blank" rel="noopener noreferrer"><strong>' . __('Submit a ticket.', 'invoiceem') . '</strong></a>'
				),
				
				array
				(
					'type' => 'html',

					'content' => __('Have some feedback you\'d like to share?', 'invoiceem') . '<br />'
					. '<a href="' . InvoiceEM_Constants::URL_REVIEW . '" target="_blank" rel="noopener noreferrer"><strong>' . __('Provide a review.', 'invoiceem') . '</strong></a>'
				),
				
				array
				(
					'type' => 'html',

					'content' => __('Want to see the plugin in your language?', 'invoiceem') . '<br />'
					. '<a href="' . InvoiceEM_Constants::URL_TRANSLATE . '" target="_blank" rel="noopener noreferrer"><strong>' . __('Assist with translation.', 'invoiceem') . '</strong></a>'
				)
			);
		}
		
		foreach ($support_box_fields as $field)
		{
			$support_box->add_field($field);
		}
		
		$support_box->add_field(array
		(
			'type' => 'html',

			'content' => __('Would you like to support development?', 'invoiceem') . '<br />'
			. '<a href="' . InvoiceEM_Constants::URL_DONATE . '" target="_blank" rel="noopener noreferrer"><strong>' . __('Make a small donation.', 'invoiceem') . '</strong></a>'
		));

		$advertising_box = new self(array
		(
			'classes' => array('iem-meta-box-locked'),
			'context' => 'side',
			'id' => 'advertising',
			'title' => __('Better Hosting with WPEngine', 'invoiceem')
		));

		$advertising_box->add_field(array
		(
			'content' => '<a target="_blank" href="https://shareasale.com/r.cfm?b=1144535&amp;u=1815763&amp;m=41388&amp;urllink=&amp;afftrack=" rel="noopener noreferrer"><img src="' . InvoiceEM()->cache->asset_path('images', 'YourWordPressDXP300x600.png') . '" border="0" /></a>',
			'type' => 'html'
		));
	}

	/**
	 * Finalize the meta boxes.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function finalize_meta_boxes()
	{
		add_action('add_meta_boxes', array(__CLASS__, 'remove_meta_boxes'), 2147483647);
		do_action('add_meta_boxes', InvoiceEM()->cache->screen->id, null);
	}

	/**
	 * Remove unnecessary meta boxes.
	 *
	 * @since 1.0.0
	 *
	 * @access public static
	 * @return void
	 */
	public static function remove_meta_boxes()
	{
		$iem = InvoiceEM();

		remove_meta_box('eg-meta-box', $iem->cache->screen->id, 'normal');
		remove_meta_box('mymetabox_revslider_0', $iem->cache->screen->id, 'normal');
	}
}
