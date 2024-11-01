<?php
/**
 * My Tickets.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/tickets/myaccount/view-tickets.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 1.0.0
 * @package WooCommerceTicketManager/Templates
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! $customer_id ) {
	return;
}

$tickets     = wctm_get_tickets(
	array(
		'_customer_id' => $customer_id,
		'post_status'  => 'publish',
	)
);
$has_tickets = count( $tickets ) > 0;
?>

<?php if ( $has_tickets ) : ?>

	<table class="woocommerce-MyAccount-my-tickets shop_table shop_table_responsive">
		<thead>
		<tr>
			<th class="ticket-number"><span class="nobr"><?php esc_html_e( 'Ticket #', 'wc-ticket-manager' ); ?></span></th>
			<th class="ticket-product"><span class="nobr"><?php esc_html_e( 'Product', 'wc-ticket-manager' ); ?></span></th>
			<th class="ticket-order"><span class="nobr"><?php esc_html_e( 'Order', 'wc-ticket-manager' ); ?></span></th>
			<th class="ticket-actions"><span class="nobr"><?php esc_html_e( 'Actions', 'wc-ticket-manager' ); ?></span></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $tickets as $ticket ) : ?>
			<?php
			$order   = $ticket->get_order();
			$product = $ticket->get_product();
			if ( empty( $product ) ) {
				continue;
			}
			?>
			<tr>
				<td class="ticket-number">
					<a href="<?php echo esc_url( $ticket->get_view_ticket_url() ); ?>">
						#<?php echo esc_html( $ticket->get_ticket_number() ); ?>
					</a>
				</td>
				<td class="ticket-product">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
				</td>
				<td class="ticket-order">
					<?php if ( is_a( $order, 'WC_Order' ) ) : ?>
						<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
					<?php else : ?>
						<?php esc_html_e( 'N/A', 'wc-ticket-manager' ); ?>
					<?php endif; ?>
				</td>
				<td class="ticket-actions">
					<a href="<?php echo esc_url( $ticket->get_view_ticket_url() ); ?>" class="button view"><?php esc_html_e( 'View', 'wc-ticket-manager' ); ?></a>
					<?php
					/**
					 * My Ticket Actions.
					 *
					 * @since 1.0.0
					 *
					 * @param object $ticket Ticket object.
					 */
					do_action( 'wc_ticket_manager_my_ticket_actions', $ticket );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Go Shop', 'wc-ticket-manager' ); ?>
		</a>
		<?php esc_html_e( 'No ticket has been purchased yet.', 'wc-ticket-manager' ); ?>
	</div>

<?php endif; ?>
