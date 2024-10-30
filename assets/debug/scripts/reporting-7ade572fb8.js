/*! Plugin reporting JavaScript. * @since 1.0.0 * @package InvoiceEM */

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
 * Chart.js object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var Chart = Chart || {};

/**
 * Reportiong options object.
 * 
 * @since 1.0.0
 * 
 * @var object
 */
var iem_reporting_options = iem_reporting_options || {};

(function ($)
{
	'use strict';
	
	$(document)
	.ready(function ()
	{
		/**
		 * General variables.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem
		 * @var    object
		 */
		var iem = $.invoiceem || {};
		
		/**
		 * Data variable names.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.data
		 * @var    object
		 */
		var iemd = iem.data || {};
		
		$.extend(iemd,
		{
			/**
			 * Invoices chart object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.invoices_chart
			 * @var    object
			 */
			"invoices_chart": 'iem-invoices-chart',
			
			/**
			 * Payments chart object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.data.payments_chart
			 * @var    object
			 */
			"payments_chart": 'iem-payments-chart'
		});
		
		/**
		 * General functions.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.functions
		 * @var    object
		 */
		var iemf = iem.functions || {};
		
		/**
		 * Options object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.options
		 * @var object
		 */
		var iemo = iem.options || {};
		
		/**
		 * Reporting functionality.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.reporting
		 * @var    object
		 */
		iem.reporting = iem.reporting || {};
		
		var iemr = iem.reporting;
		
		$.extend(iemr,
		{
			/** 
			 * Options object.
			 *
			 * @since 1.0.0
			 *
			 * @access jQuery.invoiceem.reporting.options
			 * @var    object
			 */
			"options": iem_reporting_options || {},
			
			/**
			 * Invoices chart object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.invoices_chart_object
			 * @var    object
			 */
			"invoices_chart_object": null,
			
			/**
			 * Payments chart object.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.payments_chart_object
			 * @var    object
			 */
			"payments_chart_object": null,
			
			/**
			 * Get default chart options.
			 * 
			 * @since 1.0.5 Changed money format call for custom number grouping.
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.doughnut_options
			 * @var    object
			 */
			"doughnut_options":
			{
				"aspectRatio": 1,

				"legend":
				{
					"position": 'bottom'
				},

				"tooltips":
				{
					"callbacks":
					{
						"label": function (tooltip_item, data)
						{
							var value = data.datasets[tooltip_item.datasetIndex].data[tooltip_item.index];

							if (tooltip_item.datasetIndex == 0)
							{
								var number_settings = accounting.settings.number;
								number_settings.precision = 0;

								return accounting.formatNumber(value, number_settings);
							}
							else
							{
								return iemf.format_currency(value);
							}
						}
					}
				}
			}
		});
		
		/**
		 * Options object.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.reporting.options
		 * @var    object
		 */
		var iemro = iemr.options || {};
		
		/**
		 * Reporting preparation functionality.
		 * 
		 * @since 1.0.0
		 * 
		 * @access jQuery.invoiceem.reporting.prepare
		 * @var    object
		 */
		iemr.prepare = iemr.prepare || {};
		
		var iemrp = iemr.prepare;
		
		$.extend(iemrp,
		{
			/**
			 * Setup the chart draw functionality.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.chart_draw
			 * @return void
			 */
			"chart_draw": function ()
			{
				Chart.pluginService
				.register(
				{
					"afterDraw": function (chart)
					{
						if (chart.data.datasets.length === 0)
						{
							chart.clear();
							
							var ctx = chart.chart.ctx;
							ctx.save();
							
							ctx.font = 'inherit';
							ctx.textAlign = 'center';
							ctx.textBaseline = 'middle';
							
							ctx.fillText(iemro.no_data, chart.chart.width / 2, chart.chart.height / 2);
							ctx.restore();
						}
					}
				});
			},
			
			/**
			 * Prepare the reporting fields.
			 * 
			 * @since 1.0.5 Removed changes made flag and changed meta box name.
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.fields
			 * @return void
			 */
			"fields": function ()
			{
				$('#invoiceem_meta_box_chart_options select')
				.change(function ()
				{
					var wrapper = $(this).closest('.inside');
					var fields = wrapper.find('.iem-field').addClass('iem-loading');
					var options = fields.find('input[type="hidden"],select').prop('disabled', true);
					
					$.post(
					{
						"url": ajaxurl,
						
						"data":
						{
							"action": 'iem_reporting',
							"currency_id": options.filter('#iem-currency_id').val(),
							"time_period": options.filter('#iem-time_period').val()
						},

						"error": function ()
						{
							fields.removeClass('iem-loading');
							options.prop('disabled', false);
						},

						"success": function (data)
						{
							iemro = data;
							
							iemrp.load_accounting();
							iemrp.invoices_chart();
							iemrp.payments_chart();

							fields.removeClass('iem-loading');
							options.prop('disabled', false);
						}
					});
				});
			},
			
			/**
			 * Loading accounting settings for the current report.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.load_accounting
			 * @return void
			 */
			"load_accounting": function ()
			{
				iemo.accounting = iemro.accounting;

				iemf.load_accounting();
			},
			
			/**
			 * Setup the invoices chart.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.invoices_chart
			 * @return void
			 */
			"invoices_chart": function ()
			{
				if (iemr.invoices_chart_object == null)
				{
					var canvas = $('#iem-invoices-chart');

					var chart = new Chart(canvas.get(0).getContext('2d'),
					{
						"data": iemro.invoices,
						"options": iemr.doughnut_options,
						"type": 'doughnut'
					});

					canvas.data(iemd.invoices_chart, chart);

					iemr.invoices_chart_object = chart;
				}
				else
				{
					iemr.invoices_chart_object.data = iemro.invoices;
					iemr.invoices_chart_object.update();
				}
			},
			
			/**
			 * Setup the payments chart.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.invoiceem.reporting.prepare.payments_chart
			 * @return void
			 */
			"payments_chart": function ()
			{
				if (iemr.payments_chart_object == null)
				{
					var canvas = $('#iem-payments-chart');

					var chart = new Chart(canvas.get(0).getContext('2d'),
					{
						"data": iemro.payments,
						"options": iemr.doughnut_options,
						"type": 'doughnut'
					});

					canvas.data(iemd.payments_chart, chart);

					iemr.payments_chart_object = chart;
				}
				else
				{
					iemr.payments_chart_object.data = iemro.payments;
					iemr.payments_chart_object.update();
				}
			}
		});
		
		iemrp.chart_draw();
		iemrp.fields();
		iemrp.load_accounting();
		iemrp.invoices_chart();
		iemrp.payments_chart();
	});
})(jQuery);
