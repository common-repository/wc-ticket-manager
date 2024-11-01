<?php
/**
 * Order ticket template.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/order-ticket.php.
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
$props          = array(
	array(
		'key'      => 'ticket_number',
		'label'    => __( 'Ticket Number', 'wc-ticket-manager' ),
		'value'    => sprintf( '#%s', $ticket->get_ticket_number() ),
		'priority' => 1,
	),
);
// ticket fields.
$fields = wctm_get_ticket_fields( $ticket->get_product_id(), $ticket->get_fields() );
foreach ( $fields as $field ) {
	$props[] = array(
		'key'      => $field['name'],
		'label'    => $field['label'],
		'value'    => $field['value'],
		'enabled'  => $field['enabled'],
		'priority' => 3,
	);
}

$props = apply_filters( 'wc_ticket_manager_ticket_email_data', $props, $ticket );
?>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<?php if ( ! empty( $ticket_title ) ) : ?>
		<thead>
		<tr>
			<th class="td" scope="col" style="text-align: left;" colspan="2">
				<?php echo esc_html( $ticket_title ); ?>
			</th>
		</tr>
		</thead>
	<?php endif; ?>
	<thead>
	<tbody>
	<?php
	foreach ( $props as $prop ) :
		if ( array_key_exists( 'enabled', $prop ) && 'no' === $prop['enabled'] ) {
			continue;
		}
		?>
		<tr>
			<td class="td" scope="row" style="text-align: left;">
				<?php echo esc_html( $prop['label'] ); ?>
			</td>
			<td class="td" style="text-align: left;">
				<?php echo wp_kses_post( $prop['value'] ); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
