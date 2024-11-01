<?php
/**
 * Order tickets template.
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
 * @var Ticket[] $tickets The ticket objects.
 * @package WooCommerceTicketManager/Templates
 */

use WooCommerceTicketManager\Models\Ticket;

defined( 'ABSPATH' ) || exit;

if ( ! $tickets ) {
	return;
}

?>

<section class="woocommerce-order-details wctm-order-tickets">
	<h2 class="woocommerce-order-details__title"><?php echo esc_html( apply_filters( 'wc_ticket_manager_order_tickets_title', __( 'Order Tickets', 'wc-ticket-manager' ) ) ); ?></h2>

	<?php foreach ( $tickets as $ticket ) : ?>
		<?php wc_get_template( 'order/order-ticket.php', array( 'ticket' => $ticket ), '', wc_ticket_manager()->get_template_path() ); ?>
	<?php endforeach; ?>

</section>
<?php
