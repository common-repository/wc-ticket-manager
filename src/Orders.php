<?php

namespace WooCommerceTicketManager;

defined( 'ABSPATH' ) || exit;

/**
 * Orders class.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Orders {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_new_order_item', array( __CLASS__, 'add_order_item_meta' ), 50, 3 );
		add_action( 'woocommerce_order_item_meta_end', array( __CLASS__, 'display_order_item_meta' ), 10, 3 );

		// Process completed orders.
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'publish_tickets' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'publish_tickets' ), 10, 1 );

		add_action( 'woocommerce_order_status_processing_to_on-hold', array( __CLASS__, 'unpublish_tickets' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed_to_on-hold', array( __CLASS__, 'unpublish_tickets' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing_to_pending', array( __CLASS__, 'unpublish_tickets' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed_to_pending', array( __CLASS__, 'unpublish_tickets' ), 10, 1 );

		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'display_purchased_tickets' ), 10, 1 );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_purchased_tickets' ), 10, 1 );
	}

	/**
	 * Add order item meta.
	 *
	 * @param int            $item_id The item ID.
	 * @param \WC_Order_Item $values The item values.
	 * @param int|false      $order_id The order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_order_item_meta( $item_id, $values, $order_id = 0 ) {
		// Check if you get_product_id method exists.
		if ( ! method_exists( $values, 'get_product_id' ) ) {
			return;
		}
		$order      = wc_get_order( $order_id );
		$product_id = $values->get_product_id();
		$product    = wc_get_product( $product_id );
		$quantity   = $values->get_quantity();
		if ( ! $product || ! wctm_is_ticket_product( $product ) ) {
			return;
		}
		$product_id   = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;
		$variation_id = $product->is_type( 'variation' ) ? $product_id : 0;
		$data         = isset( $values->legacy_values['wctm_ticket_data'] ) ? $values->legacy_values['wctm_ticket_data'] : array();
		$ticket_data  = array(
			'product_id'   => $product_id,
			'variation_id' => $variation_id,
			'fields'       => array(),
		);
		wc_add_order_item_meta( $item_id, '_wctm_ticket_data', array_merge( $ticket_data, $data ) );
		$field_values    = isset( $data['fields'] ) ? $data['fields'] : array();
		$number_settings = get_post_meta( $product_id, '_wctm_ticket_number_settings', true );
		$number_settings = ! empty( $number_settings ) ? $number_settings : 'global';
		if ( 'customize' === $number_settings ) {
			$number_type = get_post_meta( $product_id, '_wctm_ticket_number_type', true );
			$prefix      = get_post_meta( $product_id, '_wctm_ticket_number_prefix', true );
			$suffix      = get_post_meta( $product_id, '_wctm_ticket_number_suffix', true );
			$min_length  = get_post_meta( $product_id, '_wctm_ticket_number_length', true );
			$min_length  = ! empty( $min_length ) ? $min_length : 6;
		} else {
			$number_type = get_option( 'wctm_ticket_number_type', 'sequential' );
			$prefix      = get_option( 'wctm_ticket_number_prefix', '' );
			$suffix      = get_option( 'wctm_ticket_number_suffix', '' );
			$min_length  = get_option( 'wctm_ticket_number_length', 6 );
		}
		$last_number = get_post_meta( $product_id, '_wctm_ticket_number_last', true );
		$last_number = ! empty( $last_number ) ? $last_number : 0;

		$data = array(
			'ticket_number' => '',
			'ticket_token'  => '',
			'post_status'   => 'pending',
			'product_id'    => $product_id,
			'variation_id'  => $variation_id,
			'order_item_id' => $item_id,
			'order_id'      => $order_id,
			'customer_id'   => $order->get_customer_id(),
			'fields'        => $field_values,
		);
		for ( $i = 0; $i < $quantity; $i++ ) {
			if ( 'sequential' === $number_type ) {
				++$last_number;
				$number = str_pad( $last_number, $min_length, '0', STR_PAD_LEFT );
			} elseif ( 'random' === $number_type ) {
				$number = substr( md5( uniqid( wp_rand(), true ) ), 0, $min_length );
			}
			$number = strtoupper( $number );
			$number = $prefix . $number . $suffix;

			$data['ticket_number'] = $number;
			wctm_insert_ticket( $data );
		}

		// Update last number.
		if ( 'sequential' === $number_type ) {
			update_post_meta( $product_id, '_wctm_ticket_number_last', $last_number );
		}
	}

	/**
	 * Display order item meta.
	 *
	 * @param int            $item_id The item ID.
	 * @param \WC_Order_Item $values The item values.
	 * @param string         $product The product.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function display_order_item_meta( $item_id, $values, $product ) {
		// Check if you get_product_id method exists.
		if ( ! method_exists( $values, 'get_product_id' ) ) {
			return;
		}
		$product_id = $values->get_product_id();
		$product    = wc_get_product( $product_id );
		if ( ! $product || ! wctm_is_ticket_product( $product ) ) {
			return;
		}
		$ticket_data = wc_get_order_item_meta( $item_id, '_wctm_ticket_data', true );
		$ticket_data = ! empty( $ticket_data ) ? $ticket_data : array();
		$values      = isset( $ticket_data['fields'] ) ? $ticket_data['fields'] : array();
		$fields      = wctm_get_ticket_fields( $product_id, $values );
		if ( empty( $fields ) ) {
			return;
		}
		?>
		<ul class="wc-item-meta">
			<?php
			foreach ( $fields as $field ) {
				if ( 'no' === $field['enabled'] ) {
					continue;
				}
				?>
				<li>
					<strong><?php echo esc_html( $field['label'] ); ?>:</strong>
					<?php echo esc_html( $field['value'] ); ?>
				</li>
			<?php } ?>
		</ul>
		<?php
	}

	/**
	 * Publish tickets.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function publish_tickets( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		wctm_update_order_ticket_status( $order_id, 'publish' );
	}

	/**
	 * Unpublished tickets.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function unpublish_tickets( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		wctm_update_order_ticket_status( $order_id, 'pending' );
	}

	/**
	 * Display purchased tickets.
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function display_purchased_tickets( $order ) {
		if ( ! $order ) {
			return;
		}
		$tickets = wctm_get_tickets(
			array(
				'meta_key'    => '_order_id', // phpcs:ignore
				'meta_value'  => $order->get_id(), // phpcs:ignore
				'post_status' => 'publish',
				'per_page'    => - 1,
			)
		);

		if ( empty( $tickets ) ) {
			return;
		}

		$args = array(
			'tickets' => $tickets,
			'order'   => $order,
		);

		wc_get_template( 'order/order-tickets.php', $args, '', wc_ticket_manager()->get_template_path() );
	}

	/**
	 * Email purchased tickets.
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function email_purchased_tickets( $order ) {
		if ( ! $order ) {
			return;
		}
		$tickets = wctm_get_tickets(
			array(
				'meta_key'    => '_order_id', // phpcs:ignore
				'meta_value'  => $order->get_id(), // phpcs:ignore
				'post_status' => 'publish',
				'per_page'    => - 1,
			)
		);

		if ( empty( $tickets ) ) {
			return;
		}

		$args = array(
			'tickets' => $tickets,
			'order'   => $order,
		);

		wc_get_template( 'emails/order-tickets.php', $args, '', wc_ticket_manager()->get_template_path() );
	}
}
