<?php
	/*!
	 * IFRAME wrapper template.
	 *
	 * @since 1.0.0
	 *
	 * @package InvoiceEM
	 */

	if (!defined('ABSPATH'))
	{
		exit;
	}
?>

<script type="text/html" id="tmpl-iem-iframe-wrapper">

	<div id="iem-iframe">

		<a title="<?php _e('Close', 'invoiceem'); ?>" id="iem-iframe-close"></a>
			
		<div id="iem-iframe-loading"></div>

	</div>

</script>
