<?php
	/*!
	 * Add note template.
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

<script type="text/html" id="tmpl-iem-add-note">

	<?php
		$field = new InvoiceEM_Field(InvoiceEM_History::add_note_field(str_replace(InvoiceEM_Constants::TOKEN . '_', '', InvoiceEM()->cache->current_page), true));
		$field->output(true);
	?>
	
</script>
