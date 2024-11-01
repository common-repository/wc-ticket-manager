<?php
/**
 * Template for product enabled-ticket that renders ticket fields in add to cart.
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 * @var array $fields List of ticket fields.
 * @package WooCommerceTicketManager/Templates
 */

defined( 'ABSPATH' ) || exit;

$fields = wp_list_filter( $fields, array( 'enabled' => 'yes' ) );

if ( empty( $fields ) ) {
	return;
}

?>
<div class="wctm-ticket-form">

	<?php
	/**
	 * Hook: wc_ticket_manager_before_ticket_fields.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_ticket_manager_before_ticket_fields' );
	?>

	<?php foreach ( $fields as $index => $field ) : ?>
		<p class="wctm-ticket-field form-row">
			<label for="wctm-ticket-field-<?php echo esc_attr( $field['name'] ); ?>">
				<?php echo esc_html( $field['label'] ); ?>
				<?php if ( 'yes' === $field['required'] ) : ?>
					<span class="required">*</span>
				<?php endif; ?>
			</label>
			<input
				type="<?php echo esc_attr( ! empty( $field['type'] ) ? $field['type'] : 'text' ); ?>"
				name="wctm_ticket_fields[<?php echo esc_attr( $field['name'] ); ?>]"
				id="wctm-ticket-field-<?php echo esc_attr( $field['name'] ); ?>"
				placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
				<?php if ( 'yes' === $field['required'] ) : ?>
					required="required"
				<?php endif; ?>
			/>
		</p>
	<?php endforeach; ?>

	<?php
	/**
	 * Hook: wc_ticket_manager_after_ticket_fields.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_ticket_manager_after_ticket_fields' );
	?>

</div>
