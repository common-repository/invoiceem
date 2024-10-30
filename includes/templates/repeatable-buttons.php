<?php
	/*!
	 * Repeatable buttons template.
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

<script type="text/html" id="tmpl-iem-repeatable-buttons">

	<a href="javascript:;" title="<?php esc_attr_e('Move Item', 'invoiceem'); ?>" tabindex="-1" class="iem-repeatable-move">
	
		<span class="iem-repeatable-count"></span>
		<span class="iem-repeatable-move-button"><span class="dashicons dashicons-move"></span></span>
		
	</a>
	
	<a href="javascript:;" title="<?php esc_attr_e('Insert Item Above', 'invoiceem'); ?>" tabindex="-1" class="iem-repeatable-insert"><span class="dashicons dashicons-plus"></span></a>
	<a href="javascript:;" title="<?php esc_attr_e('Remove Item', 'invoiceem'); ?>" tabindex="-1" class="iem-repeatable-remove"><span class="dashicons dashicons-no"></span></a>
	<a href="javascript:;" title="<?php esc_attr_e('Move Item Up', 'invoiceem'); ?>" tabindex="-1" class="iem-repeatable-move-up"><span class="dashicons dashicons-arrow-up-alt"></span></a>
	<a href="javascript:;" title="<?php esc_attr_e('Move Item Down', 'invoiceem'); ?>" tabindex="-1" class="iem-repeatable-move-down"><span class="dashicons dashicons-arrow-down-alt"></span></a>
	
</script>
