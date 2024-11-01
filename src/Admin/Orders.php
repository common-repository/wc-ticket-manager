<?php

namespace WooCommerceTicketManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager\Admin
 */
class Orders {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// show order item meta ticket fields.
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'show_order_item_meta' ), 10, 3 );
	}

	/**
	 * Show order item meta ticket fields.
	 *
	 * @param int         $item_id Item ID.
	 * @param array       $item Item.
	 * @param \WC_Product $product Product.
	 *
	 * @since 1.0.0
	 */
	public static function show_order_item_meta( $item_id, $item, $product ) {
		if ( ! wctm_is_ticket_product( $product ) ) {
			return;
		}
		$tickets = wctm_get_tickets(
			array(
				'meta_key'    => '_order_item_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => $item_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'post_status' => 'any',
			)
		);
		if ( empty( $tickets ) ) {
			return;
		}
		printf( '<p>%s</p>', esc_html__( 'Here are the tickets associated with this item:', 'wc-ticket-manager' ) );
		foreach ( $tickets as $key => $ticket ) :?>
			<?php $fields = wctm_get_ticket_fields( $product->get_id(), $ticket->get_fields() ); ?>
			<table cellspacing="0" class="display_meta" style="margin-bottom: 10px;">
				<tbody>
				<tr>
					<th colspan="2">
						#<?php echo esc_html( $key + 1 ); ?>
						<?php if ( 'pending' === $ticket->post_status ) : ?>
							- <span class="ticket-status ticket-status-pending"><?php echo esc_html__( 'Pending', 'wc-ticket-manager' ); ?></span>
						<?php endif; ?>
					</th>
				</tr>
				<tr>
					<th>
						<?php echo esc_html__( 'Ticket', 'wc-ticket-manager' ); ?>:
					</th>
					<td>
						#<?php echo esc_html( $ticket->get_ticket_number() ); ?>
					</td>
				</tr>

				<?php foreach ( $fields as $field ) : ?>
					<?php if ( ! empty( $field['value'] ) ) : ?>
						<tr>
							<th>
								<?php echo esc_html( $field['label'] ); ?>:
							</th>
							<td>
								<?php echo esc_html( $field['value'] ); ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>

				<tr>
					<th colspan="2">
						<?php printf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-ticket-manager&edit=' . $ticket->get_id() ) ), esc_html__( 'View Ticket', 'wc-ticket-manager' ) ); ?>
					</th>
				</tr>
				</tbody>
			</table>
		<?php endforeach; ?>
		<?php
	}
}
