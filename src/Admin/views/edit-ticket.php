<?php
/**
 * Ticket edit view.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager\Admin\Views
 *
 * @var \WooCommerceTicketManager\Models\Ticket $ticket Ticket object.
 */

defined( 'ABSPATH' ) || exit();

$ticket_fields = wctm_get_ticket_fields( $ticket->get_product_id(), $ticket->get_fields() );
?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'Edit Ticket', 'wc-ticket-manager' ); ?>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-ticket-manager' ) ); ?>">
		<?php esc_html_e( 'Go Back', 'wc-ticket-manager' ); ?>
	</a>
</h1>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="pev-poststuff">
		<div class="column-1">
			<div class="pev-card">
				<div class="pev-card__header">
					<h3 class="pev-card__title"> <?php esc_html_e( 'Ticket Details', 'wc-ticket-manager' ); ?> </h3>
					<p class="pev-card__subtitle">
						#<?php echo esc_html( $ticket->get_id() ); ?>
					</p>
				</div>
				<div class="pev-card__body inline--fields">
					<div class="pev-form-field">
						<label>
							<?php esc_html_e( 'Product', 'wc-ticket-manager' ); ?>
						</label>
						<?php
						$product = $ticket->get_product();
						if ( $product ) {
							printf( '<a href="%s">#%d %s</a>', esc_url( get_edit_post_link( $product->get_id() ) ), esc_html( $product->get_id() ), esc_html( $product->get_formatted_name() ) );
						} else {
							esc_html_e( 'No product assigned.', 'wc-ticket-manager' );
						}
						?>
					</div>

					<div class="pev-form-field">
						<label>
							<?php esc_html_e( 'Order', 'wc-ticket-manager' ); ?>
						</label>
						<?php
						$order = $ticket->get_order();
						if ( $order ) {
							printf( '<a href="%s">#%d %s</a>', esc_url( get_edit_post_link( $order->get_id() ) ), esc_html( $order->get_id() ), esc_html( $order->get_formatted_billing_full_name() ) );
						} else {
							esc_html_e( 'No order assigned.', 'wc-ticket-manager' );
						}
						?>
					</div>

					<?php if ( $ticket->get_order() ) : ?>
						<div class="pev-form-field">
							<label>
								<?php esc_html_e( 'Customer', 'wc-ticket-manager' ); ?>
							</label>
							<?php
							$customer = $ticket->get_customer();
							if ( $customer ) {
								printf( '<a href="%s">#%d %s</a>', esc_url( get_edit_post_link( $customer->get_id() ) ), esc_html( $customer->get_id() ), esc_html( $ticket->get_customer_name() ) );
							} else {
								esc_html_e( 'Customer not found.', 'wc-ticket-manager' );
							}
							?>
						</div>
					<?php endif; ?>

					<div class="pev-form-field">
						<label for="ticket_number">
							<?php esc_html_e( 'Ticket Number', 'wc-ticket-manager' ); ?>
						</label>
						<input class="regular-text" type="text" name="ticket_number" id="ticket_number" value="<?php echo esc_attr( $ticket->get_ticket_number() ); ?>" required>
					</div>

					<?php if ( is_array( $ticket_fields ) ) : ?>
						<?php foreach ( $ticket_fields as $field ) : ?>
							<div class="pev-form-field">
								<label for="wctm-field-<?php echo esc_attr( $field['name'] ); ?>">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
								<input
									class="regular-text"
									type="<?php echo esc_attr( ! empty( $field['type'] ) ? $field['type'] : 'text' ); ?>"
									name="wctm_fields[<?php echo esc_attr( $field['name'] ); ?>]"
									id="wctm-field-<?php echo esc_attr( $field['name'] ); ?>"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
									value="<?php echo esc_attr( $field['value'] ); ?>"
									<?php if ( 'yes' === $field['required'] ) : ?>
										required="required"
									<?php endif; ?>
								/>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

				</div>
			</div>
		</div><!-- .column-1 -->
		<div class="column-2">
			<div class="pev-card">
				<div class="pev-card__header">
					<h3 class="pev-card__title"><?php esc_html_e( 'Actions', 'wc-ticket-manager' ); ?></h3>
				</div>
				<div class="pev-card__footer">
					<a class="del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=wc-ticket-manager&id=' . $ticket->get_id() ) ), 'bulk-tickets' ) ); ?>"><?php esc_html_e( 'Delete', 'wc-ticket-manager' ); ?></a>
					<button class="button button-primary"><?php esc_html_e( 'Save Ticket', 'wc-ticket-manager' ); ?></button>
				</div>
			</div>
		</div><!-- .column-2 -->
	</div><!-- .pev-poststuff -->

	<?php wp_nonce_field( 'wctm_edit_ticket' ); ?>
	<input type="hidden" name="action" value="wctm_edit_ticket">
	<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $ticket->get_id() ); ?>"/>
</form>
