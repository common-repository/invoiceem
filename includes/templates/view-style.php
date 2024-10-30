<?php
	/*!
	 * Invoice CSS template.
	 *
	 * @since 1.0.5 Added internal line items styles.
	 * @since 1.0.0
	 *
	 * @package InvoiceEM
	 */

	if (!defined('ABSPATH'))
	{
		exit;
	}
?>

<style type="text/css">

	*
	{
		font-size: 1em;
		line-height: 1.25em;
	}
	
	a
	{
		color: #0073aa;
	}
	a:hover
	{
		text-decoration: none;
	}
	
	button
	{
		background: #0073aa;
		border: 0;
		color: #fff;
		cursor: pointer;
		font-size: 1.25em;
		font-weight: bold;
		margin: 0;
		padding: 0.5em 1em 0.438em;
		text-align: center;
		width: 100%;
	}
	button:hover
	{
		background: #005a8f;
	}
	button[disabled]
	{
		background: #808080;
		cursor: default;
	}
	
	div,
	th,
	td
	{
		color: #4c4c4c;
	}
	
	input[type='text']:not([disabled]),
	select
	{
		-webkit-box-shadow: inset 0 0 4px 1px #0073aa;
		box-shadow: inset 0 0 4px 1px #0073aa;
	}
	
	select
	{
		padding: 2px;
	}
	
	@media print
	{
		div.iem-wrapper
		{
			font-size: 1em;
		}
		
		div.iem-no-print
		{
			display: none;
		}
	}
	
	@media screen
	{
		div.iem-wrapper
		{
			font-size: 1.125em;
		}
	}
	
	div.iem-spacer,
	td.iem-spacer
	{
		height: 1em;
		line-height: 1em;
	}
	
	div.iem-border-spacer
	{
		border-top: 1px solid #282828;
	}
	
	div.iem-hidden
	{
		display: none;
	}
	
	div.iem-message
	{
		background: #c00;
		color: #fff;
		display: none;
		font-weight: bold;
		margin-top: 0.5em;
		padding: 0.5em;
		text-align: center;
	}
	
	div.iem-note
	{
		background: #0073aa;
		color: #fff;
		font-weight: bold;
		padding: 0.75em 1em;
		text-align: center;
	}
	
	div.iem-note form
	{
		margin: 0;
	}
	
	div.iem-note label
	{
		color: #fff;
		display: inline-block;
		line-height: 1em;
		margin-bottom: 0.5em;
	}
	
	div.iem-note #card-element
	{
		background: #fff;
		height: 19px;
		margin: 0 auto;
		max-width: 300px;
		padding: 8px;
	}
	
	div.iem-note #card-errors
	{
		background: #c00;
		color: #fff;
		display: none;
		font-size: 0.875em;
		margin: 0 auto;
		max-width: 300px;
		padding: 4px 8px;
	}
	
	div.iem-note button
	{
		border: 1px solid #fff;
		display: inline-block;
		font-size: 1em;
		margin-top: 0.5em;
		width: auto;
	}
	
	div.iem-status
	{
		color: #808080;
		font-size: 3em;
		line-height: 0.875em;
		font-weight: bold;
		text-align: center;
	}
	
	div.iem-generated-by
	{
		font-size: 0.75em;
		text-align: center;
	}
	
	span.iem-description
	{
		color: #808080;
		display: inline-block;
		font-size: 0.875em;
		line-height: 1.125em;
		padding-bottom: 0.083em;
	}
	
	span.iem-overdue,
	strong.iem-overdue
	{
		color: #dc3232;
	}
	
	strong
	{
		color: #282828;
	}
	
	strong.iem-company
	{
		font-size: 1.167em;
		line-height: 1.125em;
	}
	
	table
	{
		border-collapse: collapse;
		vertical-align: top;
		width: 100%;
	}
	
	tr
	{
		page-break-inside: avoid;
		vertical-align: top;
	}
	
	table.iem-bottom,
	table.iem-bottom tr
	{
		vertical-align: bottom;
	}
	
	th.iem-nowrap,
	td.iem-nowrap
	{
		white-space: nowrap;
	}
	
	td.iem-column
	{
		width: 47.5%;
	}
	
	td.iem-column-spacer
	{
		width: 5%;
	}
	
	td.iem-title
	{
		color: #282828;
		font-size: 1.8em;
		font-weight: bold;
		line-height: 1em;
	}
	
	table.iem-line-items th,
	table.iem-line-items td
	{
		border: 1px solid #282828;
		padding: 0.167em 0.333em;
		vertical-align: top;
	}
	
	table.iem-line-items thead th,
	table.iem-line-items tfoot td
	{
		border-bottom-width: 2px;
		border-top-width: 2px;
		color: #282828;
	}
	
	table.iem-line-items thead th
	{
		font-weight: bold;
		text-align: center;
	}
	
	table.iem-line-items td.iem-borderless
	{
		border-width: 0;
	}
	
	table.iem-line-items tr.iem-double-border td,
	table.iem-line-items tr.iem-double-border td.iem-borderless
	{
		border-top-width: 2px;
	}
	
	table.iem-line-items th.iem-input-checkbox,
	table.iem-line-items td.iem-input-checkbox,
	table.iem-line-items th.iem-input-checkbox input,
	table.iem-line-items td.iem-input-checkbox input
	{
		cursor: pointer;
	}
	
	table.iem-line-items th.iem-input-checkbox,
	table.iem-line-items td.iem-input-checkbox
	{
		-webkit-box-shadow: inset 0 0 4px 1px #0073aa;
		box-shadow: inset 0 0 4px 1px #0073aa;
		text-align: center;
	}
	
	table.iem-line-items td.iem-input-text
	{
		padding: 0;
		position: relative;
	}
	
	table.iem-line-items td.iem-input-text input
	{
		border: 0;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
		color: inherit;
		font: inherit;
		height: 100%;
		left: 0;
		max-height: 100%;
		padding: 0.167em 0.333em;
		position: absolute;
		top: 0;
		width: 100%;
	}
	
	table.iem-internal-line-items td
	{
		border: 0;
		border-bottom: 1px solid #808080;
		padding: 0.167em 0;
	}
	
	table.iem-internal-line-items td.iem-align-opposite
	{
		color: #808080;
	}
	
	table.iem-internal-line-items tr.iem-first-row td
	{
		padding-top: 0;
	}
	
	table.iem-internal-line-items tr.iem-last-row td
	{
		border-bottom: 0;
		padding-bottom: 0;
	}
	
	<?php if (is_rtl()) : ?>
	
		div.iem-wrapper
		{
			direction: rtl;
		}
		
		.iem-align-opposite
		{
			text-align: left;
		}
		
	<?php else : ?>
	
		.iem-align-opposite
		{
			text-align: right;
		}
		
	<?php endif; ?>
	
</style>
