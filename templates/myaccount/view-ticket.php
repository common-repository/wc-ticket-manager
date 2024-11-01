<?php
/**
 * View Ticket.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-tickets.php.
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
 * @var Ticket $ticket The ticket object.
 * @package WooCommerceTicketManager/Templates
 */

use WooCommerceTicketManager\Models\Ticket;

defined( 'ABSPATH' ) || exit;

?>

<section class="woocommerce-order-details">
	<h3 class="woocommerce-order-details__title"><?php esc_html_e( 'Ticket Details', 'wc-ticket-manager' ); ?></h3>
	<?php if ( ! $ticket ) : ?>
		<p class="woocommerce-notice woocommerce-notice--info woocommerce-box-office-no-tickets"><?php esc_html_e( 'No tickets found.', 'wc-ticket-manager' ); ?></p>
	<?php else : ?>
		<?php wc_get_template( 'order/order-ticket.php', array( 'ticket' => $ticket ), '', wc_ticket_manager()->get_template_path() ); ?>
	<?php endif; ?>
</section>
