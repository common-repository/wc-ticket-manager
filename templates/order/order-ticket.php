<?php
/**
 * Order ticket template.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-tickets.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 1.0.0
 *
 * @var Ticket $ticket The ticket objects.
 * @package WooCommerceTicketManager/Templates/Emails
 */

use WooCommerceTicketManager\Models\Ticket;

defined( 'ABSPATH' ) || exit;

if ( ! $ticket ) {
	return;
}

$ticket_actions = apply_filters( 'wc_ticket_manager_order_ticket_actions', array(), $ticket );
$ticket_title   = apply_filters( 'wc_ticket_manager_order_ticket_title', $ticket->get_product_title(), $ticket );
?>
<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
	<?php if ( ! empty( $ticket_title ) || ! empty( $ticket_actions ) ) : ?>
		<thead>
		<tr>
			<td class="woocommerce-table__product-name product-name">
				<?php echo esc_html( $ticket_title ); ?>
			</td>
			<td class="woocommerce-table__product-name ticket-actions" style="text-align: right;">
				<?php foreach ( $ticket_actions as $action ) : ?>
					<a href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['text'] ); ?></a>
				<?php endforeach; ?>
			</td>
		</tr>
		</thead>
	<?php endif; ?>
	<thead>
	<tbody>
	<?php
	/**
	 * Hook: wc_ticket_manager_before_ticket_details.
	 *
	 * @param Ticket $ticket The ticket object.
	 * @since 1.0.0
	 */
	do_action( 'wc_ticket_manager_display_ticket_properties', $ticket );
	?>
	</tbody>
</table>
