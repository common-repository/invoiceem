/*! Primary plugin JavaScript. * @since 1.0.0 * @package InvoiceEM */

/**
 * Accounting object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var accounting = accounting || {};

/**
 * WordPress AJAX URL.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
var ajaxurl = ajaxurl || '';

/**
 * Current WordPress admin page ID.
 * 
 * @since 1.0.0
 * 
 * @var string
 */
var pagenow = pagenow || '';

/**
 * WordPress postboxes object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var postboxes = postboxes || {};

/**
 * Main WordPress utilities object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var wp = window.wp || {};

/**
 * Options object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var iem_script_options = iem_script_options || {};

/**
 * Validation rules object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var iem_script_validation = iem_script_validation || {};

(function ($)
{
	'use strict';

	$(document)
	.ready(function ()
	{
		$.fn.extend(
		{
			/**
			 * Add a custom event to all provided elements.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.fn.iem_add_event
			 * @this   object     Elements to add the event to.
			 * @param  string   e Event name to add to all elements.
			 * @param  function f Function executed when the event is fired.
			 * @return object     Updated elements.
			 */
			"iem_add_event": function (e, f)
			{
				return this.addClass(e).on(e, f).iem_trigger_all(e);
			},

			/**
			 * Equalize the height of the provided elements.
			 * 
			 * @since 1.0.5
			 * 
			 * @access jQuery.fn.iem_equalize_height
			 * @this   object Elements to equalize the height for.
			 * @return object Updated elements.
			 */
			"iem_equalize_height": function ()
			{
				var tallest = 0;
				
				return this.css('min-height', '')
				.each(function ()
				{
					var height = $(this).height();
					
					tallest = (height > tallest)
					? height
					: tallest;
				})
				.css('min-height', tallest + 'px');
			},

			/**
			 * Fire an event on all provided elements.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.fn.iem_trigger_all
			 * @this   object      Elements to fire the event on.
			 * @param  string e    Event name to fire on all elements.
			 * @param  array  args Extra arguments to pass to the event call.
			 * @return object      Triggered elements.
			 */
			"iem_trigger_all": function (e, args)
			{
				args = ($.type(args) === 'undefined')
				? []
				: args;

				if (!$.isArray(args))
				{
					args = [args];
				}

				return this
				.each(function ()
				{
					$(this).triggerHandler(e, args);
				});
			},

			/**
			 * Check for and return unprepared elements.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.fn.iem_unprepared
			 * @this   object              Elements to check.
			 * @param  string class_suffix Suffix to add to the prepared class name.
			 * @return object              Unprepared elements.
			 */
			"iem_unprepared": function (class_suffix)
			{
				var class_name = 'iem-prepared';
				
				if (class_suffix)
				{
					class_name += '-' + class_suffix;
				}

				return this.not('.' + class_name).addClass(class_name);
			}
		});

		/**
		 * General variables.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem
		 * @var    object
		 */
		$.invoiceem = $.invoiceem || {};
		
		var iem = $.invoiceem;

		$.extend(iem,
		{
			/**
			 * WordPress admin bar layer.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.admin_bar
			 * @var    object
			 */
			"admin_bar": $('#wpadminbar'),
			
			/**
			 * Current document BODY layer.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.body
			 * @var    object
			 */
			"body": $(document.body),
			
			/**
			 * Current document object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.document
			 * @var    object
			 */
			"document": $(document),
			
			/**
			 * Current window object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.window
			 * @var    object
			 */
			"window": $(window),
			
			/**
			 * Options object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.options
			 * @var    object
			 */
			"options": iem_script_options || {},
			
			/**
			 * Validation rules object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.validation
			 * @var    object
			 */
			"validation": iem_script_validation || {},
			
			/**
			 * Most recent object that triggered an IFRAME.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.iframe_trigger
			 * @var    object
			 */
			"iframe_trigger": null,
			
			/**
			 * Current top scroll position.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.scroll_top
			 * @var    integer
			 */
			"scroll_top": 0,

			/**
			 * Layers used for scrolling.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.scroll_element
			 * @var    object
			 */
			"scroll_element": $('html,body')
			.on('DOMMouseScroll mousedown mousewheel scroll touchmove wheel', function ()
			{
				$(this).stop();
			})
		});
		
		/**
		 * Options object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.options
		 * @var    object
		 */
		var iemo = iem.options || {};

		/**
		 * Validation rules object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.validation
		 * @var    object
		 */
		var iemv = iem.validation || {};
		
		/**
		 * Data variable names.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.data
		 * @var    object
		 */
		iem.data = iem.data || {};
		
		var iemd = iem.data;
		
		$.extend(iemd,
		{
			/**
			 * Action used for Select2 AJAX calls.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.ajax_action
			 * @var    string
			 */
			"ajax_action": 'iem-ajax-action',

			/**
			 * Label used for a cancel button.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.cancel
			 * @var    string
			 */
			"cancel": 'iem-cancel',

			/**
			 * Flag used for clearing Select2 fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.clear
			 * @var    boolean
			 */
			"clear": 'iem-clear',

			/**
			 * Compare operator used for a field being compared for a conditional field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.compare
			 * @var    string
			 */
			"compare": 'iem-compare',

			/**
			 * Name for a conditional field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.conditional
			 * @var    string
			 */
			"conditional": 'iem-conditional',

			/**
			 * Name of the field being compared for a conditional field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.field
			 * @var    string
			 */
			"field": 'iem-field',

			/**
			 * Format used for datepickers fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.format
			 * @var    string
			 */
			"format": 'iem-format',

			/**
			 * HREF for confirmed dialog boxes.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.href
			 * @var    string
			 */
			"href": 'iem-href',

			/**
			 * Raw field identifier for repeatable fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.identifier
			 * @var    string
			 */
			"identifier": 'iem-identifier',
			
			/**
			 * Index used for tab buttons.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.index
			 * @var    integer
			 */
			"index": 'iem-index',
			
			/**
			 * Initial value for a form field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.initial_value
			 * @var    string
			 */
			"initial_value": 'iem-initial-value',

			/**
			 * Current number of items in a repeatable field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.item_count
			 * @var    integer
			 */
			"item_count": 'iem-item-count',

			/**
			 * Media window object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.media
			 * @var    object
			 */
			"media": 'iem-media',

			/**
			 * Text used for the media window button.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.media_button
			 * @var    string
			 */
			"media_button": 'iem-media-button',

			/**
			 * Text used for the media window title.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.media_title
			 * @var    string
			 */
			"media_title": 'iem-media-title',
			
			/**
			 * Maximum value for spinner fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.max
			 * @var    float
			 */
			"max": 'iem-max',
			
			/**
			 * Minimum value for spinner fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.min
			 * @var    float
			 */
			"min": 'iem-min',
			
			/**
			 * Field name for repeatable fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.name
			 * @var    string
			 */
			"name": 'iem-name',

			/**
			 * Number format used for spinner fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.number_format
			 * @var    string
			 */
			"number_format": 'iem-number-format',

			/**
			 * ID of the current object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.object_id
			 * @var    integer
			 */
			"object_id": 'iem-object-id',

			/**
			 * Placeholder value for currency and invoice number fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.placeholder
			 * @var    string
			 */
			"placeholder": 'iem-placeholder',

			/**
			 * Source URL for an IFRAME.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.src
			 * @var    string
			 */
			"src": 'iem-src',
			
			/**
			 * Starting index for a repeatable item.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.starting_index
			 * @var    integer
			 */
			"starting_index": 'iem-starting-index',

			/**
			 * Number indicating the value for each step in a spinner field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.step
			 * @var    float
			 */
			"step": 'iem-step',

			/**
			 * Raw database table name used for history output.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.table
			 * @var    string
			 */
			"table": 'iem-table',
			
			/**
			 * Calculated tax.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.tax
			 * @var    float
			 */
			"tax": 'iem-tax',

			/**
			 * Text displayed for tooltip fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.tooltip
			 * @var    string
			 */
			"tooltip": 'iem-tooltip',

			/**
			 * Object that triggered a dialog box.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.trigger
			 * @var    object
			 */
			"trigger": 'iem-trigger',

			/**
			 * Value to check for a field being compared for a conditional field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.value
			 * @var    string
			 */
			"value": 'iem-value'
		});
		
		/**
		 * Event names.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.events
		 * @var    object
		 */
		iem.events = iem.events || {};
		
		var ieme = iem.events;
		
		$.extend(ieme,
		{
			/**
			 * Event used calculating invoice totals.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.calculate
			 * @var    string
			 */
			"calculate": 'iem-calculate',
			
			/**
			 * Event used for final field changes.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.change
			 * @var    string
			 */
			"change": 'iem-change',
			
			/**
			 * Event used for closing IFRAMEs.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.close
			 * @var    string
			 */
			"close": 'iem-close',
			
			/**
			 * Event used to finalize repeatable fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.finalize
			 * @var    string
			 */
			"finalize": 'iem-finalize',
			
			/**
			 * Event used for rebuilding a repeatable field.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.rebuild
			 * @var    string
			 */
			"rebuild": 'iem-rebuild',
			
			/**
			 * Event used for sorting repeatable fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.events.sort
			 * @var    string
			 */
			"sort": 'iem-sort'
		});

		/**
		 * General functions.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.functions
		 * @var    object
		 */
		iem.functions = iem.functions || {};
		
		var iemf = iem.functions;

		$.extend(iemf,
		{
			/**
			 * Escape HTML from front-end output.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.escape_html
			 * @param  string html Unescaped HTML.
			 * @return object      Escaped HTML.
			 */
			"escape_html": function (html)
			{
				var map =
				{
					"&": '&amp;',
					"<": '&lt;',
					">": '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				};
				
				return html
				.replace(/[&<>"']/g, function (char)
				{
					return map[char];
				});
			},
			
			/**
			 * Finalize the IFRAME action.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.finalize_iframe
			 * @param  object iframe Details for the IFRAME submission.
			 * @return void
			 */
			"finalize_iframe": function (iframe)
			{
				if (iem.iframe_trigger != null)
				{
					$('#iem-iframe').triggerHandler(ieme.close);

					var select = iem.iframe_trigger.closest('.iem-field-input').children('select');
					var option = new Option(iframe.label, iframe.id, true, true);
					
					if (!select.is('[multiple]'))
					{
						select.empty();
					}
					
					$(option).appendTo(select);
					$(iframe.notices).hide().insertBefore('.iem-form').slideDown('fast');
					
					iem.document.triggerHandler('wp-updates-notice-added');
					
					select.trigger('change');

					iem.iframe_trigger = null;
				}
			},
			
			/**
			 * Apply currency formatting to a number.
			 * 
			 * @since 1.0.5
			 * 
			 * @access jQuery.invoiceem.functions.format_currency
			 * @param  float  number   Number to be formatted.
			 * @param  object settings Currency settings for the number.
			 * @return float           Currency formatted number.
			 */
			"format_currency": function (number, settings)
			{
				if ($.type(settings) === 'undefined')
				{
					settings = accounting.settings;
				}
				
				if (settings.grouping != null)
				{
					var number_raw = accounting.formatNumber(Math.abs(accounting.unformat(number)), settings.number).split(settings.currency.decimal);
					var groups = settings.currency.grouping.split(',').reverse();
					var group_index = 0;
					var grouped = '';
					
					do
					{
						if (grouped != '')
						{
							grouped = settings.currency.thousand + grouped;
						}

						if (number_raw[0].length > groups[group_index])
						{
							var group = number_raw[0].length - groups[group_index];
							
							grouped = number_raw[0].substring(group) + grouped;
							number_raw[0] = number_raw[0].substring(0, group);
						}
						else
						{
							grouped = number_raw[0] + grouped;
							number_raw[0] = '';
						}

						group_index = (group_index < groups.length - 1)
						? group_index + 1
						: 0;
					}
					while (number_raw[0] != '');

					number_raw[0] = grouped;
					
					var format = settings.currency.format.zero;
					
					if (number > 0)
					{
						format = settings.currency.format.pos;
					}
					else if (number < 0)
					{
						format = settings.currency.format.neg;
					}
					
					return format.replace('%s', settings.currency.symbol).replace('%v', number_raw.join(settings.currency.decimal));
				}
				
				return accounting.formatMoney(number, settings);
			},
			
			/**
			 * Format a number for calculations.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.format_number
			 * @param  float number Number to be formatted.
			 * @return float        Formatted number.
			 */
			"format_number": function (number)
			{
				if (!$.isNumeric(number))
				{
					return number;
				}
				
				number *= 1;
				
				var round_fix = 0.000000001;
				
				if (number < 0)
				{
					round_fix *= -1;
				}
				
				return accounting.formatNumber(number + round_fix, accounting.settings.raw) * 1;
			},
			
			/**
			 * Load accounting settings.
			 * 
			 * @since 1.0.5 Changed money format call for custom number grouping and modified for advanced invoice number generation.
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.load_accounting
			 * @return void
			 */
			"load_accounting": function ()
			{
				var currency = $('.iem-currency');
				var unprepared = currency.iem_unprepared();
				var placeholder = 0;

				currency.not(unprepared).iem_trigger_all('focus')
				.each(function ()
				{
					var current = $(this);
					current.val(current.val().replace(accounting.settings.currency.decimal, iemo.accounting.currency.decimal));
				});

				accounting.settings = iemo.accounting;
				
				if (accounting.settings.invoice_prefix)
				{
					$('.iem-invoice-number')
					.each(function ()
					{
						var current = $(this);
						current.attr('placeholder', current.data(iemd.placeholder).replace('{p}', accounting.settings.invoice_prefix)).iem_trigger_all(ieme.change);
					});
				}
				
				if (!currency.is('.iem-exclude-placeholder'))
				{
					if
					(
						accounting.settings.rate
						&&
						$.isNumeric(accounting.settings.rate)
					)
					{
						placeholder = accounting.settings.rate;
					}
					else if (currency.is('[data-' + iemd.placeholder + ']'))
					{
						placeholder = currency.data(iemd.placeholder);
					}

					placeholder = iemf.format_currency(placeholder.toString().replace('.', accounting.settings.currency.decimal));

					currency.attr('placeholder', placeholder).filter(':hidden').val(placeholder);
				}
				
				currency.iem_trigger_all('blur');
				
				$('.iem-discount-field .iem-discount-amount').text(accounting.settings.currency.symbol);
				
				var override_taxes = $('#iem-override_taxes');
				
				if
				(
					accounting.settings.taxes
					&&
					override_taxes.length > 0
					&&
					!override_taxes.is(':checked')
				)
				{
					$('.iem-taxes-repeatable .iem-repeatable').triggerHandler(ieme.rebuild, [accounting.settings.taxes]);
				}
				
				$('.iem-line-items').triggerHandler(ieme.calculate);
			},
			
			/**
			 * Finalize a list form.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.list_form
			 * @param  object   button   Button clicked to trigger the form.
			 * @param  object   template Loaded form template to display.
			 * @param  function callback Function to fire once the template is added.
			 * @return void
			 */
			"list_form": function (button, template, callback)
			{
				var row = button.closest('tr');
				
				if (!row.hasClass('iem-list-form-trigger'))
				{
					row.addClass('iem-list-form-trigger').siblings('.iem-list-form').find('.iem-cancel').iem_trigger_all('click');

					$('.iem-notice.is-dismissible .notice-dismiss').iem_trigger_all('click');

					var colspan_modifier = row.find('.check-column').length;
					var cell = $('<td/>').attr('colspan', row.children(':visible').length - colspan_modifier).append(template);

					template.find('.iem-cancel')
					.click(function ()
					{
						var children = $(this).closest('td').children();
						children.last().addClass('iem-last');

						children
						.slideUp('fast', function ()
						{
							var current = $(this);
							current.find('.iem-datepicker').datepicker('destroy');
							current.find('.iem-spinner').spinner('destroy');
							current.find('.iem-tooltip').tooltip('destroy');

							if (current.hasClass('iem-last'))
							{
								var tr = current.closest('tr');
								var prev = tr.prev();
								
								prev.prev().removeClass('iem-list-form-trigger');
								prev.remove();
								tr.remove();
							}
						});
					});

					var temporary_row = $('<tr/>').addClass('iem-list-form');
					var children = template.children().hide();

					if (colspan_modifier > 0)
					{
						temporary_row.append($('<td/>').addClass('check-column'));
					}

					temporary_row.append(cell).insertAfter(row);

					$('<tr/>').addClass('iem-hidden iem-list-form').insertAfter(row);

					iems.currency(children);
					iems.datepicker(children);
					iems.spinner(children);
					iems.tooltip(children);

					children.slideDown('fast');
					
					if ($.type(callback) === 'function')
					{
						callback();
					}
				}
			},

			/**
			 * Load and prepare an IFRAME wrapper.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.load_iframe
			 * @param  string src Source URL for the IFRAME.
			 * @return void
			 */
			"load_iframe": function (src)
			{
				iem.scroll_top = iem.window.scrollTop();
				
				iem.scroll_element.scrollTop(0).css('overflow', 'hidden');

				var wrapper = $(wp.template('iem-iframe-wrapper')())
				.on(ieme.close, function ()
				{
					$(this)
					.fadeOut('fast', function ()
					{
						iem.scroll_element.css('overflow', '');
						iem.window.scrollTop(iem.scroll_top);
						
						$(this).remove();
					});
				});

				$('<iframe/>').attr('src', src).appendTo(wrapper)
				.on('load ready', function ()
				{
					var iframe = $(this);
					var parent = iframe.parent();
					var iframe_dom = iframe.get(0);
					var loading = window.parent.jQuery('#iem-iframe').find('#iem-iframe-loading').fadeOut('fast');

					parent.find('#iem-iframe-loading').fadeOut('fast');
					
					$(iframe_dom.contentWindow)
					.on('beforeunload', function ()
					{
						loading.fadeIn('fast');
					});
					
					$(iframe_dom.contentDocument).find('a[href]').not('[target]').not('.iem-same-iframe')
					.click(function (e)
					{
						e.preventDefault();
					});
				});

				wrapper.find('#iem-iframe-close')
				.click(function ()
				{
					var iframe = $(this).siblings('iframe').get(0);
					
					var close_iframe =
					(
						$(iframe.contentDocument).find('.iem-iframe-close').length == 0
						||
						!iframe.contentWindow.jQuery.invoiceem.forms.changes_made
					);
					
					if (!close_iframe)
					{
						close_iframe = confirm(iemo.strings.save_alert);
						
						if (!close_iframe)
						{
							$('#iem-iframe-loading').fadeOut('fast');
						}
					}
					
					if (close_iframe)
					{
						$('#iem-iframe').triggerHandler(ieme.close);
					}
				});

				wrapper.appendTo(iem.body).fadeIn('fast');
			},
			
			/**
			 * Pad a number with leading zeroes.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.pad_number
			 * @param  integer number Number to pad.
			 * @param  integer length Minimum length of the number.
			 * @return string         Padded number.
			 */
			"pad_number": function (number, length)
			{
				var padded = number.toString();
				
				while (padded.length < length)
				{
					padded = '0' + padded;
				}
				
				return padded;
			},
			
			/**
			 * Unfor,mat a currency value.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.functions.unformat_currency
			 * @param  mixed currency Currency value to unformat. 
			 * @return float          Unformatted currency value.
			 */
			"unformat_currency": function (currency)
			{
				if ($.isNumeric(currency))
				{
					return currency * 1;
				}
				
				currency = (currency == '')
				? '0'
				: accounting.unformat(currency.replace(accounting.settings.currency.symbol, '')).toString();
				
				var multiplier = 1;
				
				if (currency.indexOf('-') === 0)
				{
					currency = currency.substring(1);
					multiplier = -1;
				}
				
				return iemf.format_number(currency.replace(accounting.settings.currency.decimal, '.')) * multiplier;
			}
		});

		/**
		 * Global JSON object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.global
		 * @var    object
		 */
		iem.global = iem.global || {};
		
		var iemg = iem.global;

		$.extend(iemg,
		{
			/**
			 * Modify the URL in the address bar if a new one was provided.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.global.modify_url
			 * @return void
			 */
			"modify_url": function ()
			{
				if
				(
					iemo.new_url
					&&
					iemo.new_url != ''
					&&
					$.isFunction(window.history.replaceState)
				)
				{
					window.history.replaceState(null, null, iemo.new_url);
				}
			},

			/**
			 * Include postboxes functionality.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.global.postboxes
			 * @return void
			 */
			"postboxes": function ()
			{
				if
				(
					$.type(postboxes) !== 'undefined'
					&&
					!$.isEmptyObject(postboxes)
					&&
					$.type(pagenow) !== 'undefined'
				)
				{
					$('.if-js-closed').removeClass('if-js-closed').not('.iem-meta-box-locked').addClass('closed');

					postboxes.add_postbox_toggles(pagenow);

					$('.iem-meta-box-locked')
					.each(function ()
					{
						var current = $(this);
						current.find('.handlediv').remove();
						current.find('.hndle').off('click.postboxes');

						var hider = $('#' + current.attr('id') + '-hide');

						if (!hider.is(':checked'))
						{
							hider.click();
						}

						hider.parent().remove();
					});
				}
			}
		});

		iemg.modify_url();
		iemg.postboxes();

		/**
		 * Setup JSON object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.setup
		 * @var    object
		 */
		iem.setup = iem.setup || {};
		
		var iems = iem.setup;

		$.extend(iems,
		{
			/**
			 * Setup the currency fields.
			 * 
			 * @since 1.0.5 Changed money format call for custom number grouping.
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.currency
			 * @return void
			 */
			"currency": function ()
			{
				$('.iem-currency').not('[readonly]')
				.focus(function ()
				{
					var focused = $(this);
					var value = focused.val();

					if (value != '')
					{
						focused.val(accounting.formatNumber(value.replace(accounting.settings.currency.symbol, '')));
					}
				})
				.blur(function ()
				{
					var blurred = $(this);
					var value = blurred.val();

					if (value != '')
					{
						blurred.val(iemf.format_currency(value.replace(accounting.settings.currency.symbol, '')));
					}
				});
			},

			/**
			 * Prepare the datepicker fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.datepicker
			 * @param  object parent The parent object contining the datepicker fields to prepare.
			 * @return void
			 */
			"datepicker": function (parent)
			{
				var fields = ($.type(parent) === 'undefined')
				? $('.iem-datepicker').not('.iem-input-template')
				: parent.find('.iem-datepicker');

				fields
				.each(function ()
				{
					var current = $(this);

					current
					.datepicker(
					{
						"dateFormat": (current.is('[data-' + iemd.format + ']'))
						? current.data(iemd.format)
						: iemo.date_format
					});
				});	
			},

			/**
			 * Prepare the dialog fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.dialog
			 * @return void
			 */
			"dialog": function ()
			{
				var buttons = {};
				
				buttons[iemo.strings.cancel] = function ()
				{
					$(this).dialog('close');
				};
				
				buttons[iemo.strings.confirm] = function ()
				{
					var current = $(this);
					var trigger = current.data(iemd.trigger);
					
					current.dialog('widget').find('button').prop('disabled', true);
					
					if (trigger.is('[data-' + iemd.href + ']'))
					{
						window.location = trigger.data(iemd.href);
					}
					else
					{
						trigger.trigger('click', [true]);
					}
				};
				
				$('.iem-dialog')
				.dialog(
				{
					"autoOpen": false,
					"buttons": buttons,
					"closeText": iemo.strings.close,
					"draggable": false,
					"modal": true,
					"resizable": false,
					"width": 280,
					
					"close": function ()
					{
						$(this).dialog('widget').find('button').prop('disabled', false);
					}
				});
			},
			
			/**
			 * Setup the IFRAME buttons.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.iframes
			 * @return void
			 */
			"iframes": function ()
			{
				$('.iem-iframe-button')
				.click(function (e)
				{
					e.preventDefault();

					var clicked = $(this);
					var field = clicked.closest('.iem-field-input').children('select');
					var open_iframe = false;
					
					var src = (clicked.is('a'))
					? clicked.attr('href')
					: clicked.data(iemd.src);

					if (field.length > 0)
					{
						var id = (clicked.is('.iem-edit-button'))
						? field.val()
						: 0;

						if ($.isNumeric(id))
						{
							$('.iem-notice.is-dismissible .notice-dismiss').iem_trigger_all('click');

							if (id != 0)
							{
								src = src.replace('__id__', id);
							}

							if (!clicked.is('.iem-ignore-filters'))
							{
								$('select[data-' + iemd.ajax_action + ']').not(field)
								.each(function ()
								{
									var sibling = $(this);
									var value = sibling.val();

									if (value)
									{
										src += '&' + sibling.attr('name') + '=' +value;
									}
								});
							}
							
							open_iframe = true;
						}
					}
					else
					{
						open_iframe = true;
					}
					
					if (open_iframe)
					{
						iem.iframe_trigger = clicked;

						iemf.load_iframe(src);
					}
				});
			},

			/**
			 * Prepare AJAX-driven dropdowns.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.select2
			 * @param  object parent The parent object contining the dropdown field to prepare.
			 * @return void
			 */
			"select2": function (parent)
			{
				var fields = ($.type(parent) === 'undefined')
				? $('select[data-' + iemd.ajax_action + ']').not('.iem-input-template')
				: parent.find('select[data-' + iemd.ajax_action + ']');
				
				fields
				.each(function ()
				{
					var current = $(this);
					var current_action = current.data(iemd.ajax_action);
					
					current
					.select2(
					{
						"containerCssClass": 'iem-select2-selection',
						"delay": 200,
						"dropdownCssClass": 'iem-select2-dropdown',

						"ajax":
						{
							"cache": true,
							"dataType": 'json',
							"method": 'POST',
							"url": ajaxurl,

							"data": function (params)
							{
								var data =
								{
									"action": current_action,
									"not_in": [],
									"page": params.page || 1,
									"search": params.term
								};
								
								$('select[data-' + iemd.ajax_action + ']').not('.iem-input-template').not(current)
								.each(function ()
								{
									var sibling = $(this);
									var value = sibling.val();

									if (sibling.data(iemd.ajax_action) == current_action)
									{
										data.not_in.push(value);
									}
									else
									{
										data[sibling.attr('name')] = value;
									}
								});

								return data;
							}
						}
					});
					
					if (current.is('.iem-tooltip'))
					{
						current.next().addClass('iem-tooltip').attr('data-' + iemd.tooltip, current.data(iemd.tooltip));
						iems.tooltip(current.parent());
					}
				})
				.on('change.select2', function ()
				{
					var changed = $(this);
					changed.parent().find('.iem-edit-button').prop('disabled', (!$.isNumeric(changed.val())));
				})
				.on('focus', function ()
				{
					$(this).select2('open');
				})
				.on('select2:opening', function (e)
				{
					var opening = $(this);

					if (opening.data(iemd.clear) === true)
					{
						opening.removeData(iemd.clear);
						e.preventDefault();
					}
				})
				.on('select2:select', function ()
				{
					$(this).valid();
				})
				.on('select2:unselecting', function ()
				{
					$(this).data(iemd.clear, true);
				});
			},
			
			/**
			 * Prepare the spinner fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.spinner
			 * @param  object parent The parent object contining the tooltip fields to prepare.
			 * @return void
			 */
			"spinner": function (parent)
			{
				var fields = ($.type(parent) === 'undefined')
				? $('.iem-spinner').not('.iem-input-template')
				: parent.find('.iem-spinner');

				fields
				.each(function ()
				{
					var current = $(this);
					var name = current.attr('name');
					var options = {};

					if (iemv[name])
					{
						if ($.isNumeric(iemv[name].max))
						{
							options.max = iemv[name].max;
						}

						if ($.isNumeric(iemv[name].min))
						{
							options.min = iemv[name].min;
						}
					}
					else
					{
						if (current.is('[data-' + iemd.max + ']'))
						{
							options.max = current.data(iemd.max);
						}

						if (current.is('[data-' + iemd.min + ']'))
						{
							options.min = current.data(iemd.min);
						}
					}

					if (current.is('[data-' + iemd.number_format + ']'))
					{
						options.numberFormat = current.data(iemd.number_format);
					}

					if (current.is('[data-' + iemd.step + ']'))
					{
						options.step = current.data(iemd.step);
					}

					current.spinner(options).off('mousewheel');

					current.siblings('a')
					.on('mouseleave mouseup', function ()
					{
						$(this).siblings('input').change();
					});
				})
				.on('change', function ()
				{
					var changed = $(this);
					var val = changed.val();

					if (isNaN(val))
					{
						changed.val('');
					}
				})
				.on('keyup spin', function ()
				{
					$(this).change();
				});
			},

			/**
			 * Prepare the tooltip fields.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.setup.tooltip
			 * @param  object parent The parent object contining the tooltip fields to prepare.
			 * @return void
			 */
			"tooltip": function (parent)
			{
				var fields = ($.type(parent) === 'undefined')
				? $('.iem-tooltip').not('.iem-input-template')
				: parent.find('.iem-tooltip');

				var horizontal = (iem.body.hasClass('rtl'))
				? 'right'
				: 'left';

				fields
				.tooltip(
				{
					"hide": false,
					"items": '[data-' + iemd.tooltip + ']',

					"content": function ()
					{
						return $(this).data(iemd.tooltip);
					},

					"position":
					{
						"at": horizontal + ' top',
						"collision": 'flipfit',
						"my": horizontal + ' bottom-1'
					}
				});
			}
		});

		var iframe_closing = false;
		
		if (iem.body.hasClass('iem-iframe'))
		{
			if
			(
				$.isPlainObject(iemo.iframe)
				&&
				$.isNumeric(iemo.iframe.id)
			)
			{
				iframe_closing = true;
				
				window.parent.jQuery.invoiceem.functions.finalize_iframe(iemo.iframe);
			}
			else
			{
				/**
				 * IFRAMEs JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.iframes
				 * @var    object
				 */
				iem.iframes = iem.iframes || {};
				
				var iemi = iem.iframes;

				$.extend(iemi,
				{
					/**
					 * Setup the IFRAME buttons.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.iframes.buttons
					 * @return void
					 */
					"buttons": function ()
					{
						$('.iem-iframe-close')
						.click(function ()
						{
							if
							(
								!iemfo.changes_made
								||
								confirm(iemo.strings.save_alert)
							)
							{
								window.parent.jQuery('#iem-iframe').triggerHandler(ieme.close);
							}
						});
					}
				});

				iemi.buttons();
			}
		}
		
		if (!iframe_closing)
		{
			if (iemo.has_list)
			{
				/**
				 * List JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.list
				 * @var    object
				 */
				iem.list = iem.list || {};
				
				var ieml = iem.list;

				$.extend(ieml,
				{
					/**
					 * Disable multiple clicks on single-click actions.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.list.actions
					 * @return void
					 */
					"actions": function ()
					{
						$('.iem-single-click')
						.click(function (e)
						{
							var clicked = $(this);
							
							if (clicked.hasClass('iem-clicked'))
							{
								e.preventDefault();
							}
							else
							{
								clicked.addClass('iem-clicked');
							}
						});
					},
					
					/**
					 * Prepare the list-specific dialog triggers.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.list.dialogs
					 * @return void
					 */
					"dialogs": function ()
					{
						$('#doaction,#doaction2')
						.click(function (e, confirmed)
						{
							var clicked = $(this);
							var value = clicked.prev('select').val();
							var delete_selected = $('#iem-confirm-delete-selected');
							var edit_selected = $('#iem-confirm-edit-selected');
							var unconfirmed = (confirmed !== true);
							
							if
							(
								value == 'iem-bulk-delete'
								&&
								delete_selected.length > 0
								&&
								unconfirmed
							)
							{
								e.preventDefault();

								delete_selected.data(iemd.trigger, clicked).dialog('open');
							}
							else if
							(
								value == 'iem-bulk-deactivate'
								&&
								edit_selected.length > 0
								&&
								unconfirmed
							)
							{
								e.preventDefault();

								edit_selected.data(iemd.trigger, clicked).dialog('open');
							}
						});

						$('#iem-delete-all')
						.click(function (e, confirmed)
						{
							if (confirmed !== true)
							{
								e.preventDefault();

								$('#iem-confirm-delete-all').data(iemd.trigger, $(this)).dialog('open');
							}
						});

						$('.iem-confirm-delete')
						.click(function ()
						{
							var clicked = $(this);

							var dialog = $('#iem-confirm-delete').data(iemd.trigger, clicked);
							dialog.find('.iem-item-name').text(clicked.closest('td').find('> strong > a').text());
							dialog.dialog('open');
						});

						$('.iem-confirm-edit')
						.click(function ()
						{
							var clicked = $(this);

							var dialog = $('#iem-confirm-edit').data(iemd.trigger, clicked);
							dialog.find('.iem-item-name').text(clicked.closest('td').find('> strong > a').text());
							dialog.find('.iem-client-name').text(clicked.closest('tr').find('> .client_project > strong').text());
							dialog.dialog('open');
						});

						$('.iem-confirm-payment-completed')
						.click(function ()
						{
							var clicked = $(this);
							var tr = clicked.closest('tr');

							var dialog = $('#iem-confirm-payment-completed').data(iemd.trigger, clicked);
							dialog.find('.iem-payment-number').text(tr.find('> .title > strong > a').text());
							dialog.find('.iem-client-name').text(tr.find('> .client_name').text());
							dialog.dialog('open');
						});

						$('.iem-confirm-payment-failed')
						.click(function ()
						{
							var clicked = $(this);
							var tr = clicked.closest('tr');

							var dialog = $('#iem-confirm-payment-failed').data(iemd.trigger, clicked);
							dialog.find('.iem-payment-number').text(tr.find('> .title > strong > a').text());
							dialog.find('.iem-client-name').text(tr.find('> .client_name').text());
							dialog.dialog('open');
						});

						$('.iem-confirm-send,.iem-confirm-resend')
						.click(function ()
						{
							var clicked = $(this);
							var tr = clicked.closest('tr');
							
							var id = (clicked.is('.iem-confirm-send'))
							? 'send'
							: 'resend';

							var dialog = $('#iem-confirm-' + id).data(iemd.trigger, clicked);
							dialog.find('.iem-item-name').text(tr.find('> .title > strong > a').text());
							dialog.find('.iem-client-name').text(tr.find('> .client_project > strong').text());
							dialog.dialog('open');
						});
					},
					
					/**
					 * Prepare the hide toggle checkboxes.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.list.hide_toggles
					 * @return void
					 */
					"hide_toggles": function ()
					{
						$('#adv-settings,.hide-column-tog')
						.click(function ()
						{
							var table = $('.wp-list-table');
							var trigger = table.find('tr.iem-list-form-trigger');
							
							if (trigger.length > 0)
							{
								table.find('tr.iem-list-form td[colspan]').attr('colspan', trigger.children(':visible').length - 1);
							}
						});
					},
					
					/**
					 * Prepare the add note buttons.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.list.notes
					 * @return void
					 */
					"notes": function ()
					{
						$('.iem-add-note')
						.click(function ()
						{
							var template = $(wp.template('iem-add-note')());
							
							template.find('.iem-add-note')
							.click(function ()
							{
								var clicked = $(this);
								var group = clicked.closest('.iem-group');
								var textarea = group.find('textarea');
								var send_to_client = group.find('input[type="checkbox"]');
								
								if (textarea.valid())
								{
									var fields = textarea.add(group.find('button')).prop('disabled', true);
									var form = group.closest('.iem-notes-form').addClass('iem-loading');
									var page = pagenow.split('_');
									
									var data =
									{
										"action": 'iem_add_note',
										"note": textarea.val(),
										"object_id": form.closest('tr').prev().prev().find('.iem-add-note').data(iemd.object_id),
										"table": page[page.length - 1]
									};
									
									if
									(
										send_to_client.length > 0
										&&
										send_to_client.is(':checked')
									)
									{
										data.send_to_client = true;
									}
									
									$.post(
									{
										"data": data,
										"url": ajaxurl,

										"error": function ()
										{
											alert(iemo.strings.unexpected_error);
											
											fields.prop('disabled', false);
											form.removeClass('iem-loading');
										},

										"success": function (notices)
										{
											$(notices).hide().insertBefore('.iem-form').slideDown('fast');
											
											iem.document.triggerHandler('wp-updates-notice-added');
											form.find('.iem-cancel').triggerHandler('click');
										}
									});
								}
							});
							
							iemf.list_form($(this), template);
						});
					}
				});
				
				iems.dialog();
				iems.iframes();
				iems.select2();
				iems.tooltip();
				
				ieml.actions();
				ieml.dialogs();
				ieml.hide_toggles();
				ieml.notes();
			}
			else if (iemo.has_form)
			{
				/**
				 * Fields JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.forms
				 * @var    object
				 */
				iem.fields = iem.fields || {};
				
				var iemfi = iem.fields;

				$.extend(iemfi,
				{
					/**
					 * Prepare the accounting fields.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.accounting
					 * @param  object parent The parent object contining the dropdown fields to prepare.
					 * @return void
					 */
					"accounting": function (parent)
					{
						var fields = ($.type(parent) === 'undefined')
						? $('.iem-accounting').not('.iem-input-template')
						: parent.find('.iem-accounting');
						
						fields
						.on('change.select2', function ()
						{
							var changed = $(this).select2('close');
							var name_raw = changed.attr('name').split('[');

							var data =
							{
								"action": 'iem_accounting'
							};

							if (name_raw.length > 1)
							{
								data.page = name_raw[0];
								data[name_raw[1].replace(']', '')] = changed.val();
							}
							else
							{
								data[name_raw[0]] = changed.val();
							}
							
							changed.prop('disabled', true).add('.iem-currency').closest('.iem-field').addClass('iem-loading');

							$.post(
							{
								"data": data,
								"url": ajaxurl,

								"error": function ()
								{
									alert(iemo.strings.unexpected_error);
									
									changed.prop('disabled', false).add('.iem-currency').closest('.iem-field').removeClass('iem-loading');
								},

								"success": function (data)
								{
									iemo.accounting = data;

									iemf.load_accounting();

									changed.prop('disabled', false).add('.iem-currency').closest('.iem-field').removeClass('iem-loading');
								}
							});
						});
					},
						
					/**
					 * Prepare the calculate fields.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.calculate
					 * @param  object parent The parent object contining the calculate fields to prepare.
					 * @return void
					 */
					"calculate": function (parent)
					{
						var fields = ($.type(parent) === 'undefined')
						? $('.iem-calculate').not('.iem-input-template')
						: parent.find('.iem-calculate');
						
						fields
						.on('change', function ()
						{
							var item = $(this).closest('.iem-repeatable-item');
							
							var wrapper = (item.length == 0)
							? $()
							: item.closest('.iem-field-repeatable');
							
							if
							(
								wrapper.length == 0
								||
								wrapper.is('.iem-taxes-repeatable')
							)
							{
								$('.iem-line-items').triggerHandler(ieme.calculate);
							}
							else
							{
								wrapper.closest('.iem-line-items').triggerHandler(ieme.calculate, [item]);
							}
						});
					},
					
					/**
					 * Prepare fields with conditional logic.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.conditional
					 * @return void
					 */
					"conditional": function ()
					{
						$('.iem-condition[data-' + iemd.conditional + '][data-' + iemd.field + '][data-' + iemd.value + '][data-' + iemd.compare + ']')
						.each(function ()
						{
							var condition = $(this);
							var conditional = $('[name="' + condition.data(iemd.conditional) + '"],[data-' + iemd.name + '="' + condition.data(iemd.conditional) + '"]');
							var field = $('[name="' + condition.data(iemd.field) + '"]');

							if
							(
								!conditional.hasClass('iem-check-conditions')
								&&
								field.length > 0
							)
							{
								conditional
								.iem_add_event('iem-check-conditions', function ()
								{
									var current_conditional = $(this);
									var show_field = true;
									
									var conditional_name = (current_conditional.is('[name]'))
									? current_conditional.attr('name')
									: current_conditional.data(iemd.name);

									$('.iem-condition[data-' + iemd.conditional + '="' + conditional_name + '"][data-' + iemd.field + '][data-' + iemd.value + '][data-' + iemd.compare + ']')
									.each(function ()
									{
										var current_condition = $(this);
										var current_field = $('[name="' + current_condition.data(iemd.field) + '"]');
										var compare = current_condition.data(iemd.compare);
										var compare_matched = false;

										var current_value = (current_field.is(':radio'))
										? current_field.filter(':checked').val()
										: current_field.val();

										if (current_field.is(':checkbox'))
										{
											current_value = (current_field.is(':checked'))
											? current_value
											: '';
										}

										if (compare === '!=')
										{
											compare_matched = (current_condition.data(iemd.value) + '' !== current_value + '');
										}
										else
										{
											compare_matched = (current_condition.data(iemd.value) + '' === current_value + '');
										}

										show_field =
										(
											show_field
											&&
											compare_matched
										);
									});

									var parent = current_conditional.closest('.iem-field');
									parent.next('.iem-field-spacer').remove();

									if (show_field)
									{
										parent.stop(true).slideDown('fast');
									}
									else
									{
										parent.stop(true).slideUp('fast').after($('<div/>').addClass('iem-hidden iem-field-spacer'));
									}
								});
							}

							if (!field.hasClass('iem-has-condition'))
							{
								field.addClass('iem-has-condition')
								.on('change', function ()
								{
									$('.iem-condition[data-' + iemd.conditional + '][data-' + iemd.field + '="' + $(this).attr('name') + '"][data-' + iemd.value + '][data-' + iemd.compare + ']')
									.each(function ()
									{
										$('[name="' + $(this).data(iemd.conditional) + '"],[data-' + iemd.name + '="' + condition.data(iemd.conditional) + '"]').iem_trigger_all('iem-check-conditions');
									});
								});
							}
						});
					},

					/**
					 * Prepare the display history buttons.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.display_history
					 * @return void
					 */
					"display_history": function ()
					{
						$('.iem-display-history[data-' + iemd.table + ']')
						.click(function ()
						{
							var clicked = $(this).prop('disabled', true);
							var object_id = $('input.iem-object-id');
							
							if (object_id.length > 0)
							{
								var field = clicked.closest('.iem-field').addClass('iem-loading');
								
								$.post(
								{
									"url": ajaxurl,

									"data":
									{
										"action": 'iem_history',
										"column": object_id.attr('name'),
										"object_id": object_id.val(),
										"table": clicked.data(iemd.table)
									},

									"error": function ()
									{
										alert(iemo.strings.unexpected_error);
										
										clicked.prop('disabled', false);
										field.removeClass('iem-loading');
									},

									"success": function (html)
									{
										if (html)
										{
											$(html).hide().insertBefore(field).slideDown('fast');

											field
											.slideUp('fast', function ()
											{
												$(this).remove();
											});
										}
										else
										{
											field.removeClass('iem-loading');
										}
									}
								});
							}
						});
					},

					/**
					 * Prepare the image fields.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.images
					 * @return void
					 */
					"images": function ()
					{
						if ($.type(wp.media) === 'function')
						{
							var fields = $('.iem-field-image');

							fields.find('.iem-add-button').data(iemd.media, false)
							.click(function ()
							{
								var clicked = $(this);
								var parent = clicked.closest('.iem-field-image');
								var media = parent.data(iemd.media);

								if (!media)
								{
									var options =
									{
										"multiple": false,
										"type": 'image'
									};

									if (clicked.is('[data-' + iemd.media_button + ']'))
									{
										options.button =
										{
											"text": clicked.data(iemd.media_button)
										};
									}

									if (clicked.is('[data-' + iemd.media_title + ']'))
									{
										options.title = clicked.data(iemd.media_title);
									}

									media = wp.media(options);

									media
									.on('open',function ()
									{
										var id = clicked.closest('.iem-field-image').find('input').val();

										if ($.isNumeric(id))
										{
											var attachment = wp.media.attachment(id);
											attachment.fetch();

											if (attachment)
											{
												media.state().get('selection').add([attachment]);
											}
										}
									})
									.on('select', function ()
									{
										var selected = media.state().get('selection').first().toJSON();

										var url =
										(
											selected.sizes
											&&
											selected.sizes.medium
										)
										? selected.sizes.medium.url
										: selected.url;

										var parent = clicked.closest('.iem-field-image');
										parent.find('.iem-image-preview').empty().append($('<img/>').attr('src', url));
										parent.find('.iem-remove-button').prop('disabled', false);
										parent.find('input').val(selected.id).change();
									});

									parent.data(iemd.media, media);
								}

								media.open();
							});

							fields.find('.iem-remove-button')
							.click(function ()
							{
								var parent = $(this).prop('disabled', true).closest('.iem-field-image');
								parent.find('.iem-image-preview').empty();

								var media = parent.data(iemd.media);
								var input = parent.find('input');

								if (media)
								{
									var id = input.val();

									if ($.isNumeric(id))
									{
										var attachment = wp.media.attachment(id);
										attachment.fetch();

										if (attachment)
										{
											media.state().get('selection').remove([attachment]);
										}
									}
								}

								input.val('').change();
							});
						}
					},
					
					/**
					 * Prepare the repeatable fields.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.fields.repeatable
					 * @return void
					 */
					"repeatable": function ()
					{
						$('.iem-repeatable-tools .iem-add-button')
						.click(function (e, insert_before, duration, data)
						{
							duration = ($.isNumeric(duration))
							? duration
							: 'fast';
							
							var wrapper = $(this).closest('.iem-repeatable');
							var field_wrapper = wrapper.closest('.iem-field-repeatable');
							var item_count = wrapper.data(iemd.item_count) + 1;
							var template = wrapper.find('.iem-repeatable-template');
							
							wrapper.data(iemd.item_count, item_count);
							
							var item = template.clone(true).attr('data-' + iemd.starting_index, item_count - 1).removeClass('iem-repeatable-template').hide();
							item.find('.iem-input-template').removeClass('iem-input-template');
							
							item.find('[data-' + iemd.identifier + ']')
							.each(function ()
							{
								var field = $(this);
								var identifier = field.data(iemd.identifier).replace('[__i__]', '[' + (item_count - 1) + ']');

								if (field.is('label'))
								{
									field.attr('for', identifier);
								}
								else
								{
									field
									.attr(
									{
										"id": 'iem-' + identifier,
										"name": identifier
									});
								}
							});

							if
							(
								insert_before === false
								||
								$.type(insert_before) === 'undefined'
							)
							{
								item.insertBefore(template.prev());
							}
							else
							{
								item.insertBefore(insert_before);
							}
							
							if (item_count % 2 == 0)
							{
								field_wrapper.next('.iem-field-spacer').remove();
							}
							else
							{
								$('<div/>').addClass('iem-hidden iem-field-spacer').insertAfter(field_wrapper);
							}
							
							iems.datepicker(item);
							iems.select2(item);
							iems.spinner(item);
							iems.tooltip(item);
							
							iemfi.accounting(item);
							iemfi.calculate(item);
							
							if ($.isPlainObject(data))
							{
								$.each(data, function (index, value)
								{
									var field = item.find('[name$="[' + index + ']"]');
									
									if (field.is(':checkbox'))
									{
										field.prop('checked', value);
									}
									else
									{
										field.val(value);
									}
								});
								
								wrapper.triggerHandler(ieme.sort, [false]);
							}
							else
							{
								wrapper.triggerHandler(ieme.sort);
							}
							
							item.slideDown(duration);
							
							if (duration != 0)
							{
								iemfo.changes_made = true;
							}
						});
						
						var buttons = $(wp.template('iem-repeatable-buttons')())
						.click(function (e)
						{
							if ($(this).closest('.iem-repeatable').is(':animated'))
							{
								e.stopImmediatePropagation();
							}
							else
							{
								iemfo.changes_made = true;
							}
						});

						buttons.filter('.iem-repeatable-move-up')
						.click(function ()
						{
							var parent = $(this).parent();
							var prev = parent.prev('.iem-repeatable-item');

							if (prev.length > 0)
							{
								parent.insertBefore(prev).parent().triggerHandler(ieme.sort);
							}
						});

						buttons.filter('.iem-repeatable-move-down')
						.click(function ()
						{
							var parent = $(this).parent();
							var next = parent.next('.iem-repeatable-item').not('.iem-repeatable-template');

							if (next.length > 0)
							{
								parent.insertAfter(next).parent().triggerHandler(ieme.sort);
							}
						});
						
						buttons.filter('.iem-repeatable-insert')
						.click(function ()
						{
							var parent = $(this).parent();
							parent.parent().find('.iem-add-button').triggerHandler('click', [parent]);
						});
						
						buttons.filter('.iem-repeatable-remove')
						.click(function ()
						{
							var parent = $(this).parent();
							var wrapper = parent.parent();
							
							parent
							.slideUp('fast', function ()
							{
								parent.find('.iem-datepicker').datepicker('destroy');
								parent.find('.iem-spinner').spinner('destroy');
								parent.find('.iem-tooltip').tooltip('destroy');
								parent.remove();
								wrapper.triggerHandler(ieme.sort);
							});
						});
						
						$('.iem-repeatable-item')
						.each(function ()
						{
							buttons.clone(true).appendTo($(this));
						});
						
						$('.iem-repeatable')
						.each(function ()
						{
							var repeatable = $(this);
							repeatable.data(iemd.item_count, repeatable.find('.iem-repeatable-item').not('.iem-repeatable-template').length);
						})
						.on(ieme.rebuild, function (e, data)
						{
							if (data)
							{
								var repeatable = $(this).data(iemd.item_count, 0);
								repeatable.find('.iem-repeatable-item').not('.iem-repeatable-template').remove();

								var add_button = repeatable.find('.iem-add-button');

								$.each(data, function (index, value)
								{
									add_button.triggerHandler('click', [false, 0, value]);
								});

								repeatable.triggerHandler(ieme.finalize);
							}
						})
						.on(ieme.sort, function (e, finalize)
						{
							var repeatable = $(this);
							var parent = repeatable.closest('.iem-field-repeatable');
							var current_items = repeatable.children('.iem-repeatable-item').not('.iem-repeatable-template');

							if (parent.is('.iem-repeatable-locked'))
							{
								repeatable.addClass('ui-sortable-disabled');
							}
							else
							{
								if (!repeatable.hasClass('ui-sortable'))
								{
									repeatable
									.mousedown(function ()
									{
										var clicked = $(this);
										clicked.height(clicked.height());
									})
									.mouseup(function ()
									{
										$(this).css('height', '');
									})
									.sortable(
									{
										"containment": 'parent',
										"cursor": 'move',
										"forcePlaceholderSize": true,
										"handle": '> .iem-repeatable-move',
										"items": '> .iem-repeatable-item',
										"opacity": 0.75,
										"placeholder": 'iem-repeatable-placeholder',
										"revert": 'fast',
										"tolerance": 'pointer',

										"stop": function (e, ui)
										{
											iemfo.changes_made = true;
											
											var repetable = ui.item.parent('.iem-repeatable');
											repetable.triggerHandler(ieme.sort);
										}
									});
								}

								if (current_items.length > 1)
								{
									repeatable.sortable('enable');
								}
								else
								{
									repeatable.sortable('disable');
								}
							}
							
							current_items
							.each(function (index)
							{
								var current = $(this);
								current.find('.iem-order-index').val(index);
								current.find('.iem-repeatable-count').text(index + 1);
							});
							
							if (finalize !== false)
							{
								repeatable.triggerHandler(ieme.finalize);
							}
						})
						.iem_trigger_all(ieme.sort);
					}
				});

				/**
				 * Forms JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.forms
				 * @var    object
				 */
				iem.forms = iem.forms || {};
				
				var iemfo = iem.forms;

				$.extend(iemfo,
				{
					/**
					 * True if changes have been made to the form.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.changes_made
					 * @var    boolean
					 */
					"changes_made": false,

					/**
					 * Setup the before upload event.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.before_unload
					 * @return void
					 */
					"before_unload": function ()
					{
						iem.window
						.on('beforeunload', function ()
						{
							if (iemfo.changes_made)
							{
								return iemo.strings.save_alert;
							}
						});
					},

					/**
					 * Setup the form fields.
					 * 
					 * @since 1.0.5 Added ability to ignore changes made check
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.fields
					 * @return void
					 */
					"fields": function ()
					{
						$('.iem-form').find('input,select,textarea').not('.iem-ignore-change')
						.each(function ()
						{
							var current = $(this);
							current.data(iemd.initial_value, current.val());
						})
						.change(function ()
						{
							var changed = $(this);
							
							if (changed.val() != changed.data(iemd.initial_value))
							{
								iemfo.changes_made = true;
							}
						});
						
						iems.currency();
						iems.datepicker();
						iems.spinner();
						iems.tooltip();
						
						iemfi.accounting();
						iemfi.calculate();
						iemfi.conditional();
						iemfi.display_history();
						iemfi.images();
						iemfi.repeatable();
						
						iemf.load_accounting();
					},
					
					/**
					 * Setup the notes form.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.notes
					 * @return void
					 */
					"notes": function ()
					{
						$('.iem-notes-form button')
						.click(function ()
						{
							var clicked = $(this);
							var previous_label = clicked.text();
							var fields = clicked.closest('.iem-group').children('.iem-hidden').stop(true, true);
							
							clicked.text(clicked.data(iemd.cancel)).data(iemd.cancel, previous_label);
							
							if (fields.is(':visible'))
							{
								fields.slideUp('fast').find('textarea').prop('disabled', true);
							}
							else
							{
								fields.slideDown('fast').find('textarea').prop('disabled', false);
							}
						});
					},
					
					/**
					 * Setup field tabs.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.tabs
					 * @return void
					 */
					"tabs": function ()
					{
						$('.iem-tabs .iem-secondary-tab-wrapper a')
						.each(function (index)
						{
							$(this).data(iemd.index, index)
							.click(function ()
							{
								var clicked = $(this);
								
								if (!clicked.hasClass('iem-tab-active'))
								{
									var content = clicked.closest('.iem-tabs').find('.iem-tab-content > div').eq(clicked.data(iemd.index));
									
									if (content.length > 0)
									{
										clicked.add(content).addClass('iem-tab-active').siblings().removeClass('iem-tab-active');
									}
								}
							});
						});
					},

					/**
					 * Setup the form validation.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.forms.validation
					 * @return void
					 */
					"validation": function ()
					{
						$.validator
						.addMethod('iem-unique', function (value, element, selector)
						{
							var current = $(element);
							var matches = 0;
							
							current.closest('form').find(selector).not(current)
							.each(function ()
							{
								if ($(this).val() == value)
								{
									matches++;
								}
							});
							
							var is_valid =
							(
								this.optional(element)
								||
								matches == 0
							);
							
							return is_valid;
						},
						$.validator.format(iemo.strings.unique_message));
						
						$('.iem-form')
						.each(function ()
						{
							$(this)
							.validate(
							{
								"focusInvalid": false,
								"rules": iemv,

								"errorPlacement": function (error, element)
								{
									var insert_after = element.next('.select2');
									
									if (insert_after.length == 0)
									{
										insert_after = element.parent('.ui-spinner');
									}
									
									if (insert_after.length == 0)
									{
										insert_after = element;
									}

									error.hide().insertAfter(insert_after).slideDown('fast');
								},

								"highlight": function (element, error_class)
								{
									var select2 = $(element).addClass(error_class).next('.select2');

									if (select2.length > 0)
									{
										select2.addClass(error_class);
									}
								},

								"invalidHandler": function (e, validator)
								{
									if (!validator.numberOfInvalids())
									{
										return;
									}
									
									var element = $(validator.errorList[0].element);
									var tab = element.closest('.iem-tab');
									
									if
									(
										tab.length > 0
										&&
										!tab.hasClass('.iem-tab-active')
									)
									{
										var tab_index = tab.parent().children('.iem-tab').index(tab);
										
										tab.closest('.iem-tabs').find('.iem-tab-buttons a').eq(tab_index).triggerHandler('click');
									}

									var admin_bar_height = iem.admin_bar.height();
									var window_height = iem.window.height() - admin_bar_height;
									var element_height = element.outerHeight();
									var scroll_top = element.offset().top - admin_bar_height;
									
									if (element_height < window_height)
									{
										scroll_top -= Math.floor((window_height - element_height - admin_bar_height) / 2);
									}

									iem.scroll_element
									.animate(
									{
										"scrollTop": Math.max(0, Math.min(iem.document.height() - window_height, scroll_top)) + 'px'
									},
									{
										"queue": false
									});
								},

								"showErrors": function()
								{
									$('.iem-notice.is-dismissible .notice-dismiss').iem_trigger_all('click');

									var message = $('.iem-notice.iem-validation-error');

									if
									(
										this.numberOfInvalids() > 0
										&&
										!message.hasClass('iem-invalid')
									)
									{
										message.stop(true).addClass('iem-invalid').slideDown('fast');
									}
									else if
									(
										this.numberOfInvalids() == 0
										&&
										message.hasClass('iem-invalid')
									)
									{
										message.stop(true).removeClass('iem-invalid').slideUp('fast');
									}

									this.defaultShowErrors();
								},

								"submitHandler": function (form)
								{
									var $form = $(form);
									$form.find('[type="submit"]').prop('disabled', true);
									$form.find('.iem-currency').iem_trigger_all('focus');
									
									$form.find('.iem-discount-field')
									.each(function ()
									{
										var current = $(this);
										
										if (current.find('.iem-discount-percentage').hasClass('button-primary'))
										{
											var input = current.find('input');
											var value = input.val();
											
											if (value != '')
											{
												input.val(input.val() + '%');
											}
										}
									});

									iemfo.changes_made = false;

									form.submit();
								},

								"unhighlight": function (element, error_class)
								{
									var select2 = $(element).removeClass(error_class).next('.select2');

									if (select2.length > 0)
									{
										select2.removeClass(error_class);
									}
								}
							});
						})
						.find('[type="submit"]')
						.click(function ()
						{
							var clicked = $(this).addClass('iem-clicked');
							clicked.closest('form').find('[type="submit"]').not(clicked).removeClass('iem-clicked');
						})
						.prop('disabled', false);
					}
				});

				iems.dialog();
				iems.iframes();
				iems.select2();

				iemfo.before_unload();
				iemfo.fields();
				iemfo.notes();
				iemfo.tabs();
				iemfo.validation();
			}
			
			if (iemo.is_currency)
			{
				/**
				 * Currency JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.currency
				 * @var    object
				 */
				iem.currency = iem.currency || {};
				
				var iemc = iem.currency;

				$.extend(iemc,
				{
					/**
					 * Setup the currency samples.
					 * 
					 * @since 1.0.5 Changed money format calls for custom number grouping.
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.currency.samples
					 * @return void
					 */
					"samples": function ()
					{
						$('.iem-form input')
						.change(function (e)
						{
							var key = e.keyCode || e.which;
							
							if
							(
								key != 9
								&&
								key != 16
							)
							{
								$(this).triggerHandler(ieme.change);
							}
						})
						.on(ieme.change, function ()
						{
							var symbol = $('#iem-symbol').val();
							var decimal = $('#iem-decimal_separator').val();
							var thousand = $('#iem-thousand_separator').val();
							var precision = $('#iem-decimal_digits').val();
							var pos = $('#iem-positive_format').val();
							var neg = $('#iem-negative_format').val();
							var zero = $('#iem-zero_format').val();
							var positive_output = '';
							var negative_output = '';
							var zero_output = '';

							if
							(
								symbol != ''
								&&
								decimal != ''
								&&
								decimal != thousand
								&&
								precision != ''
								&&
								pos != ''
								&&
								neg != ''
							)
							{
								var settings =
								{
									"symbol": symbol,
									"decimal": decimal,
									"thousand": thousand,
									"precision": precision,

									"format":
									{
										"pos": pos,
										"neg": neg,

										"zero": (zero == '')
										? pos
										: zero
									}
								};
								
								positive_output = iemf.format_currency(1234567890.987654321, settings);
								negative_output = iemf.format_currency(-1234567890.987654321, settings);
								zero_output = iemf.format_currency(0, settings);
							}
							else
							{
								positive_output = iemf.format_currency(1234567890.987654321);
								negative_output = iemf.format_currency(-1234567890.987654321);
								zero_output = iemf.format_currency(0);
							}
							
							$('#iem-currency-sample')
							.html(iemf.escape_html(positive_output) + '<br />'
							+ iemf.escape_html(negative_output) + '<br />'
							+ iemf.escape_html(zero_output));
						})
						.first().triggerHandler(ieme.change);
					}
				});
				
				iemc.samples();
			}
			else if (iemo.is_extensions)
			{
				/**
				 * Extensions JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.extensions
				 * @var    object
				 */
				iem.extensions = iem.extensions || {};
				
				var iemex = iem.extensions;

				$.extend(iemex,
				{
					/**
					 * Setup the extension boxes.
					 * 
					 * @since 1.0.5
					 * 
					 * @access jQuery.invoiceem.extensions.boxes
					 * @return void
					 */
					"boxes": function ()
					{
						iem.window
						.resize(function ()
						{
							$('#iem-extensions .iem-field-group')
							.each(function ()
							{
								var current = $(this);
								current.find('.iem-extension-top').iem_equalize_height();
								current.find('.iem-extension-bottom').iem_equalize_height();
							});
						})
						.triggerHandler('resize');
					},
					
					/**
					 * Setup the license key fields.
					 * 
					 * @since 1.0.1 Changed variable names.
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.extensions.license_keys
					 * @return void
					 */
					"license_keys": function ()
					{
						$('.iem-license-key')
						.on('keypress', function(e)
						{
							var keycode = e.keyCode || e.which;
							
							if (keycode == 13)
							{
								e.preventDefault();
								
								$(this).siblings('button:visible').triggerHandler('click');
							}
						});
						
						$('.iem-license-key-button')
						.click(function ()
						{
							var clicked = $(this);
							var input = clicked.siblings('input');
							var field = clicked.closest('.iem-field');
							
							if
							(
								input.is(':disabled')
								||
								input.valid()
							)
							{
								var disabled = clicked.add(input).prop('disabled', true);
								var deactivating = clicked.hasClass('iem-deactivate-button');
								
								field.addClass('iem-loading');
								
								$('.iem-notice.is-dismissible .notice-dismiss').iem_trigger_all('click');
								
								$.post(
								{
									"url": ajaxurl,

									"data":
									{
										"action": iemo.action,
										"iem-extension": input.attr('name'),
										"iem-license-key": input.val(),
										
										"iem-edd-action": (deactivating)
										? 'deactivate_license'
										: 'activate_license'
									},

									"error": function ()
									{
										alert(iemo.strings.unexpected_error);
										
										disabled.prop('disabled', false);
										field.removeClass('iem-loading');
									},

									"success": function (response)
									{
										if (response.notice)
										{
											$(response.notice).hide().insertBefore('.iem-form').slideDown('fast');
										}
										
										iem.document.triggerHandler('wp-updates-notice-added');
										
										if (response.success)
										{
											clicked.siblings('button').prop('disabled', false);
											
											var p = clicked.closest('p');
											
											if (deactivating)
											{
												p.removeClass('iem-activated');
												input.prop('disabled', false);
											}
											else
											{
												p.addClass('iem-activated');
											}
										}
										else
										{
											if (deactivating)
											{
												clicked.prop('disabled', false);
											}
											else
											{
												disabled.prop('disabled', false);
											}
										}
										
										field.removeClass('iem-loading');
									}
								});
							}
						});
					}
				});
				
				iemex.boxes();
				iemex.license_keys();
			}
			else if (iemo.is_invoice)
			{
				/**
				 * Invoice JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.invoice
				 * @var    object
				 */
				iem.invoice = iem.invoice || {};
				
				var iemin = iem.invoice;

				$.extend(iemin,
				{
					/**
					 * Setup the line item calculations.
					 * 
					 * @since 1.0.5 Changed money format calls for custom number grouping.
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.invoice.calculations
					 * @return void
					 */
					"calculations": function ()
					{
						var wrapper = $('.iem-line-items')
						.on(ieme.calculate, function (e, line_item)
						{
							var current = $(this);
							var line_items = current.find('.iem-repeatable-item').not('.iem-repeatable-template');
							var subtotal = 0;
							var has_tax = false;
							var show_subtotal = false;
							var pre_tax_discount = 0;
							var tax = 0;
							var discount = 0;
							
							if ($.type(line_item) === 'undefined')
							{
								line_items.iem_trigger_all(ieme.calculate);
							}
							else if (line_item !== false)
							{
								line_item.triggerHandler(ieme.calculate);
							}
							
							line_items
							.each(function ()
							{
								var current_line_item = $(this);
								
								subtotal += current_line_item.find('.iem-totals input.iem-subtotal').val() * 1;
								
								if (!has_tax)
								{
									has_tax = ($(this).find('.iem-taxes :checkbox:checked').length > 0);

									if (has_tax)
									{
										show_subtotal = true;
									}
								}
							});
							
							var pre_tax_discount_field = $('#iem-pre_tax_discount').stop(true);
							var pre_tax_discount_row = current.find('.iem-pre-tax-discount-row');
							
							if (has_tax)
							{
								var pre_tax_discount_value = pre_tax_discount_field.val();
								
								if
								(
									$.isNumeric(pre_tax_discount_value)
									&&
									pre_tax_discount_value != 0
								)
								{
									if (pre_tax_discount_field.closest('.iem-discount-field').find('.iem-discount-amount').hasClass('button-primary'))
									{
										pre_tax_discount = pre_tax_discount_value * -1;
									}
									else
									{
										pre_tax_discount = iemf.format_number(subtotal * (pre_tax_discount_value / 100) * -1);
									}
									
									line_items
									.each(function ()
									{
										var current_line_item = $(this);
										var line_item_subtotal = current_line_item.find('.iem-totals input.iem-subtotal').val() * 1;

										current_line_item.find('.iem-totals input.iem-discounted-subtotal').val(iemf.format_number(line_item_subtotal + ((line_item_subtotal / subtotal) * pre_tax_discount)));
									});
									
									pre_tax_discount_row.show();
								}
								else
								{
									pre_tax_discount_row.hide();
								}
								
								pre_tax_discount_field.closest('.iem-field').slideDown(true);
								
								var tax_row = current.find('.iem-tax-row.iem-hidden');
								tax_row.nextAll('.iem-tax-row').remove();

								var last_tax_row = tax_row;

								$('.iem-taxes-repeatable .iem-repeatable-item').not('.iem-repeatable-template')
								.each(function ()
								{
									var tax_item = $(this);
									var tax_rate = tax_item.find('[name$="[r]"]').val();

									if ($.isNumeric(tax_rate))
									{
										tax_rate /= 100;

										var current_tax = 0;

										line_items.find('.iem-taxes .iem-field[data-' + iemd.starting_index + '="' + tax_item.data(iemd.starting_index) + '"] :checkbox:checked')
										.each(function ()
										{
											current_tax += tax_rate * $(this).closest('.iem-repeatable-item').find('.iem-totals input.iem-discounted-subtotal').val();
										});

										current_tax = iemf.format_number(current_tax);

										if (current_tax > 0)
										{
											var current_tax_row = tax_row.clone().removeClass('iem-hidden').insertAfter(last_tax_row);
											current_tax_row.find('.iem-tax-label').text(tax_item.find('[name$="[l]"]').val());
											current_tax_row.find('.iem-tax-output').text(iemf.format_currency(current_tax));

											tax += current_tax;
											last_tax_row = current_tax_row;
										}
									}
								});
							}
							else
							{
								pre_tax_discount_field.closest('.iem-field').slideUp(true);
								pre_tax_discount_row.hide();
								current.find('.iem-tax-row').nextAll('.iem-tax-row').remove();
							}
							
							var discount_field = $('#iem-discount');
							var discount_value = discount_field.val();
							var discount_row = current.find('.iem-discount-row');
							
							if
							(
								$.isNumeric(discount_value)
								&&
								discount_value != 0
							)
							{
								show_subtotal = true;
								
								if (discount_field.closest('.iem-discount-field').find('.iem-discount-amount').hasClass('button-primary'))
								{
									discount = discount_value * -1;
								}
								else
								{
									discount = (subtotal + pre_tax_discount + tax) * (discount_value / 100) * -1;
								}
								
								discount = iemf.format_number(discount);
								
								discount_row.show();
							}
							else
							{
								discount_row.hide();
							}
							
							var subtotal_row = current.find('.iem-subtotal-row');
							var paid = $('#iem-paid').val() * -1;
							
							if (paid < 0)
							{
								show_subtotal = true;
								
								current.find('.iem-paid-row').show().find('.iem-paid-output').text(iemf.format_currency(paid));
							}
							
							subtotal_row.find('.iem-subtotal-output').text(iemf.format_currency(subtotal));
							current.find('.iem-pre-tax-discount-output').text(iemf.format_currency(pre_tax_discount));
							current.find('.iem-discount-output').text(iemf.format_currency(discount));
							current.find('.iem-total-output').text(iemf.format_currency(subtotal + pre_tax_discount + tax + discount + paid));
							
							if (show_subtotal)
							{
								subtotal_row.show();
							}
							else
							{
								subtotal_row.hide();
							}
						});
						
						wrapper.find('.iem-repeatable')
						.on(ieme.finalize, function ()
						{
							$(this).closest('.iem-line-items').triggerHandler(ieme.calculate);
						})
						.find('.iem-repeatable-item')
						.on(ieme.calculate, function ()
						{
							var line_item = $(this);
							var rate = iemf.unformat_currency(line_item.find('.iem-rate').val());
							var taxes = line_item.find('.iem-taxes :checkbox:checked');
							
							if (taxes.length > 0)
							{
								var rate_fields = $('.iem-taxes-repeatable .iem-repeatable-item').not('.iem-repeatable-template');
								var inclusive_rate = 0;
								
								taxes
								.each(function ()
								{
									var tax_field = $(this).closest('.iem-field');
									var rate_details = rate_fields.filter('[data-' + iemd.starting_index + '="' + tax_field.data(iemd.starting_index) + '"]');
									
									if
									(
										rate_details.length > 0
										&&
										rate_details.find('[name$="[i]"]').is(':checked')
									)
									{
										var rate = rate_details.find('[name$="[r]"]').val();
										
										if ($.isNumeric(rate))
										{
											inclusive_rate += (rate * 1) / 100;
										}
									}
								});
								
								if (inclusive_rate > 0)
								{
									rate = iemf.format_number(rate / (1 + inclusive_rate));
								}
							}
							
							var quantity_field = line_item.find('.iem-quantity');
							var quantity = quantity_field.val();

							if (!$.isNumeric(quantity))
							{
								quantity_field.val('');

								quantity = 0;
							}
							
							var subtotal = iemf.format_number(rate * quantity);
							var adjustment_field = line_item.find('.iem-adjustment');
							var adjustment = adjustment_field.val();

							if (!$.isNumeric(adjustment))
							{
								adjustment_field.val('');

								adjustment = 0;
							}
							
							adjustment = iemf.format_number(subtotal * (adjustment / 100));
							subtotal += adjustment;
							
							var totals = line_item.find('.iem-totals');
							totals.find('input').val(subtotal);
							totals.find('.iem-line-item-subtotal').text(iemf.format_currency(subtotal));
						});
						
						wrapper.triggerHandler(ieme.calculate);
					},
					
					/**
					 * Prepare the discount fields.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.invoice.discount_fields
					 * @return void
					 */
					"discount_fields": function ()
					{
						$('.iem-discount-field').find('button')
						.click(function ()
						{
							$(this).addClass('button-primary').siblings('button').removeClass('button-primary');
							
							$('.iem-line-items').triggerHandler(ieme.calculate, [false]);
							
							iemfo.changes_made = true;
						});
					},
					
					/**
					 * Setup the line item sort button.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.invoice.sort_button
					 * @return void
					 */
					"sort_button": function ()
					{
						$('.iem-sort-button')
						.click(function ()
						{
							var wrapper = $(this).closest('.iem-repeatable');
							var items = wrapper.children('.iem-repeatable-item').not('.iem-repeatable-template');
							var placeholder = $('<div/>').insertBefore(items.first());
							var sortable = $();
							
							items
							.each(function ()
							{
								var current = $(this);
								
								if (current.find('.iem-datepicker').val())
								{
									sortable = sortable.add(current);
								}
							});
							
							sortable
							.sort(function (a, b)
							{
								var output = 0;
								var a_date = $(a).find('.iem-datepicker').datepicker('getDate');
								var b_date = $(b).find('.iem-datepicker').datepicker('getDate');
								
								if
								(
									a_date
									&&
									b_date
								)
								{
									var a_time = a_date.getTime();
									var b_time = b_date.getTime();
									
									if (a_time < b_time)
									{
										output = -1;
									}
									else if (a_time > b_time)
									{
										output = 1;
									}
								}
								
								return output;
							})
							.insertAfter(placeholder);
							
							wrapper.triggerHandler(ieme.sort);
							placeholder.remove();
							
							iemfo.changes_made = true;
						});
					},
					
					/**
					 * Setup the taxes.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.invoice.taxes
					 * @return void
					 */
					"taxes": function ()
					{
						$('#iem-override_taxes')
						.change(function ()
						{
							var changed = $(this);
							
							if (!changed.is(':checked'))
							{
								changed.closest('.inside').find('.iem-repeatable').triggerHandler(ieme.rebuild, [accounting.settings.taxes]);
							}
						});
						
						$('.iem-taxes-repeatable .iem-repeatable')
						.on(ieme.finalize, function ()
						{
							var tax_items = $(this).find('.iem-repeatable-item').not('.iem-repeatable-template');
							var line_items = $('.iem-line-items');
							
							if (tax_items.length == 0)
							{
								line_items.find('.iem-taxes').addClass('iem-hidden').find('.iem-group .iem-field').remove();
							}
							else
							{
								var template = '<div class="iem-field iem-field-checkbox" data-iem-starting-index="_si_"><div class="iem-field-input"><label class="iem-description"><input id="iem-line_items[_li_][taxes][]" name="line_items[_li_][taxes][]" type="checkbox" value="1" class="iem-calculate" data-iem-identifier="line_items[__i__][taxes][]" /><span>_la_</span></label></div></div>';

								line_items.find('.iem-repeatable-item')
								.each(function ()
								{
									var item = $(this);
									var is_template = item.is('.iem-repeatable-template');
									var last_item = null;
									
									var item_index = (is_template)
									? ''
									: item.data(iemd.starting_index);
									
									var taxes = item.find('.iem-taxes').removeClass('iem-hidden').find('.iem-group');
									taxes.children('.iem-field').addClass('iem-remove');
									
									tax_items
									.each(function (index)
									{
										var current = $(this);
										var starting_index = current.data(iemd.starting_index);
										var match = taxes.children('[data-' + iemd.starting_index + '="' + starting_index + '"]');
										var label = current.find('[name$="[l]"]').val();
										
										if (label == '')
										{
											label = '--';
										}
										
										if (match.length == 0)
										{
											var current_template = template.replace(/_si_/g, starting_index).replace(/_la_/g, label);
											
											if (!is_template)
											{
												current_template = current_template.replace(/_li_/g, item_index);
											}
											
											match = $(current_template);
											
											var match_checkbox = match.find(':checkbox');
											
											if (is_template)
											{
												match_checkbox.removeAttr('id name').addClass('iem-input-template').prop('checked', true);
											}
											else
											{
												match_checkbox
												.change(function ()
												{
													var changed = $(this);
													changed.closest('.iem-line-items').triggerHandler(ieme.calculate, [changed.closest('.iem-repeatable-item')]);
												});
											}
										}
										else
										{
											match.removeClass('iem-remove').find('span').text(label);
										}
										
										match.find(':checkbox').val(index);
										
										if (last_item == null)
										{
											match.prependTo(taxes);
										}
										else
										{
											match.insertAfter(last_item);
										}
										
										last_item = match;
									});
									
									taxes.children('.iem-remove').remove();
								});
							}
							
							$('.iem-line-items').triggerHandler(ieme.calculate);
							
							$(this).find('[name$="[l]"]').iem_unprepared()
							.change(function ()
							{
								$(this).closest('.iem-repeatable').triggerHandler(ieme.finalize);
							});
						});
					}
				});
				
				iemin.calculations();
				iemin.discount_fields();
				iemin.sort_button();
				iemin.taxes();
			}
			else if (iemo.is_payment)
			{
				/**
				 * Payment JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.payment
				 * @var    object
				 */
				iem.payment = iem.payment || {};
				
				var iemp = iem.payment;

				$.extend(iemp,
				{
					/**
					 * Setup the payment calculations.
					 * 
					 * @since 1.0.5 Changed money format calls for custom number grouping.
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.payment.calculations
					 * @return void
					 */
					"calculations": function ()
					{
						$('.iem-line-items')
						.on(ieme.calculate, function ()
						{
							var subtotal = 0;
							var bonus = iemf.unformat_currency($('#iem-bonus').val());
							var has_bonus = (bonus != 0);
							var fee = iemf.unformat_currency($('#iem-fee').val());
							var has_fee = (fee != 0);
							var subtotal_row = $('.iem-subtotal-row');
							var bonus_row = $('.iem-bonus-row');
							var fee_row = $('.iem-fee-row');
							
							$(this).find('.iem-calculate')
							.each(function ()
							{
								subtotal += iemf.unformat_currency($(this).val());
							});
							
							if
							(
								has_bonus
								||
								has_fee
							)
							{
								subtotal_row.show();
								
								$('.iem-subtotal-output').text(iemf.format_currency(subtotal));
								
								if (has_bonus)
								{
									bonus_row.show();
									
									$('.iem-bonus-output').text(iemf.format_currency(bonus));
								}
								else
								{
									bonus_row.hide();
								}
								
								if (has_fee)
								{
									fee_row.show();
									
									$('.iem-fee-output').text(iemf.format_currency(fee));
								}
								else
								{
									fee_row.hide();
								}
							}
							else
							{
								subtotal_row.add(bonus_row).add(fee_row).hide();
							}
							
							$('.iem-total-output').text(iemf.format_currency(subtotal + bonus + fee));
						})
						.iem_trigger_all(ieme.calculate);
					}
				});
				
				iemp.calculations();
			}
			else if (iemo.is_view)
			{
				iems.iframes();
				
				/**
				 * View JSON object.
				 * 
				 * @since 1.0.0
				 * 
				 * @access jQuery.invoiceem.view
				 * @var    object
				 */
				iem.view = iem.view || {};
				
				var iemvi = iem.view;

				$.extend(iemvi,
				{
					/**
					 * Scale the invoice view.
					 * 
					 * @since 1.0.0
					 * 
					 * @access jQuery.invoiceem.view.scale
					 * @return void
					 */
					"scale": function ()
					{
						iem.window
						.resize(function ()
						{
							var base_width = 918;
							var width = iem.body.width();
							var sheet = $('.iem-sheet');
							var font_size = '';
							
							if (width < base_width + 40)
							{
								font_size = (Math.max(260, width) / base_width) + 'em';
							}
							
							sheet.css('font-size', font_size);
						})
						.triggerHandler('resize');
					}
				});
				
				iemvi.scale();
			}
		}
	});
})(jQuery);
