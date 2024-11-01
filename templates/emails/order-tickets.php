<?php
/**
 * Order tickets template.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-tickets.php.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager/Templates
 */

defined( 'ABSPATH' ) || exit;

?>
	<section class="woocommerce-order-details wctm-order-tickets" style="margin-bottom: 20px;">
		<h2 class="woocommerce-order-details__title"><?php echo esc_html( apply_filters( 'wc_ticket_manager_order_tickets_title', __( 'Order Tickets', 'wc-ticket-manager' ) ) ); ?></h2>

		<?php foreach ( $tickets as $ticket ) : ?>
			<?php wc_get_template( 'emails/order-ticket.php', array( 'ticket' => $ticket ), '', wc_ticket_manager()->get_template_path() ); ?>
		<?php endforeach; ?>

	</section>
<?php
