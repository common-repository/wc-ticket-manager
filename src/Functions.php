<?php
/**
 * Usefully functions.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */

use WooCommerceTicketManager\Models\Ticket;

defined( 'ABSPATH' ) || exit;

/**
 * Is a given product with ticketing enabled.
 *
 * @param mixed $product Product object or ID.
 *
 * @return bool Returns true if product is ticket product.
 * @version 1.0.0
 */
function wctm_is_ticket_product( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return false;
	}
	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_ID();

	return 'yes' === get_post_meta( $product_id, '_wctm_ticket', true );
}

/**
 * Get ticket.
 *
 * @param mixed $ticket Ticket object or ID.
 *
 * @return Ticket|null
 * @version 1.0.0
 */
function wctm_get_ticket( $ticket ) {
	$ticket = new Ticket( $ticket );
	if ( $ticket->get_id() ) {
		return $ticket;
	}

	return null;
}

/**
 * Get tickets
 *
 * @param array $args The args.
 * @param bool  $count Return only the total found items.
 *
 * @return Ticket[]|int Returns array of tickets or count of tickets.
 */
function wctm_get_tickets( $args = array(), $count = false ) {
	$defaults = array(
		'limit'       => 20,
		'offset'      => 0,
		'orderby'     => 'id',
		'order'       => 'DESC',
		'fields'      => 'all',
		'post_status' => 'publish',
		'post_type'   => 'wctm_ticket',
	);

	$args = wp_parse_args( $args, $defaults );

	$args  = wp_parse_args( $args, $defaults );
	$query = new WP_Query( $args );

	if ( $count ) {
		return $query->found_posts;
	}

	return array_map( 'wctm_get_ticket', $query->posts );
}

/**
 * Update order ticket status.
 *
 * @param int $order_id The order ID.
 * @param int $status The status.
 *
 * @return void
 */
function wctm_update_order_ticket_status( $order_id, $status ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$tickets = wctm_get_tickets(
		array(
			'post_status' => 'any', // phpcs:ignore
			'meta_query'  => array( // phpcs:ignore
				array(
					'key'     => '_order_id',
					'value'   => $order_id,
					'compare' => '=',
				),
			),
		)
	);
	foreach ( $tickets as $ticket ) {
		$ticket->set_data( array( 'status' => $status ) );
		$ticket->save();
	}
}

/**
 * Create ticket.
 *
 * @param array $data The ticket data.
 * @param bool  $wp_error Whether to return a WP_Error object on failure.
 *
 * @return Ticket|\WP_Error|false The ticket object or false on failure.
 * @since 1.0.0
 */
function wctm_insert_ticket( $data, $wp_error = true ) {
	$id     = isset( $data['id'] ) ? $data['id'] : 0;
	$ticket = new Ticket( $id );
	$ticket->set_data( $data );
	$saved = $ticket->save();
	if ( is_wp_error( $saved ) ) {
		return $wp_error ? $saved : false;
	}

	return $ticket;
}

/**
 * Get ticket fields.
 *
 * @param int   $product_id The product ID.
 * @param array $values The values.
 *
 * @return array
 * @since 1.0.0
 */
function wctm_get_ticket_fields( $product_id, $values = array() ) {
	$fields = get_post_meta( $product_id, '_wctm_ticket_fields', true );
	$fields = ! empty( $fields ) ? $fields : array();
	// If values is passed, set the values.
	foreach ( $fields as $key => $field ) {
		$fields[ $key ]['value'] = isset( $values[ $field['name'] ] ) ? $values[ $field['name'] ] : '';
	}

	return $fields;
}

/**
 * Get validated fields data.
 *
 * @param array $fields The fields.
 * @param array $posted_data The posted data.
 *
 * @return array
 * @since 1.0.0
 */
function wctm_get_validated_fields_data( $fields, $posted_data ) {
	$data = array();
	foreach ( $fields as $field ) {
		switch ( $field['type'] ) {
			case 'checkbox':
				$value = ! empty( $posted_data[ $field['name'] ] ) ? 'yes' : 'no';
				break;
			case 'email':
				$value = ! empty( $posted_data[ $field['name'] ] ) && is_email( $posted_data[ $field['name'] ] ) ? $posted_data[ $field['name'] ] : '';
				break;
			case 'date':
				$value = ! empty( $posted_data[ $field['name'] ] ) && strtotime( $posted_data[ $field['name'] ] ) ? $posted_data[ $field['name'] ] : '';
				break;

			case 'textarea':
				$value = ! empty( $posted_data[ $field['name'] ] ) ? sanitize_textarea_field( $posted_data[ $field['name'] ] ) : '';
				break;
			case 'text':
			case 'select':
			default:
				$value = ! empty( $posted_data[ $field['name'] ] ) ? sanitize_text_field( $posted_data[ $field['name'] ] ) : '';
				break;
		}

		$data[ $field['name'] ] = $value;
	}

	return $data;
}
