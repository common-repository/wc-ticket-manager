<?php

namespace WooCommerceTicketManager;

defined( 'ABSPATH' ) || exit;

/**
 * Cart class.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Cart {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_add_to_cart_text', array( __CLASS__, 'change_add_to_cart_text' ), 10, 2 );
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( __CLASS__, 'change_add_to_cart_text_single' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( __CLASS__, 'change_add_to_cart_url' ), 10, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'loop_remove_add_to_cart_class' ), 10, 2 );

		// Ticket fields.
		add_action( 'woocommerce_after_add_to_cart_quantity', array( __CLASS__, 'render_ticket_fields' ), 20 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_ticket_fields' ), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'get_item_data' ), 10, 2 );
	}

	/**
	 * Change add to cart text for ticket products.
	 *
	 * @param string $text Text.
	 * @param object $product Product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function change_add_to_cart_text( $text, $product ) {
		if ( wctm_is_ticket_product( $product ) ) {
			$text = get_option( 'wctm_shop_add_to_cart_text', __( 'Ticket Details', 'wc-ticket-manager' ) );
		}

		return $text;
	}

	/**
	 * Change add to cart text for ticket products.
	 *
	 * @param string $text Text.
	 * @param object $product Product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function change_add_to_cart_text_single( $text, $product ) {
		if ( wctm_is_ticket_product( $product ) ) {
			$text = get_option( 'wctm_product_add_to_cart_text', __( 'Buy Ticket Now', 'wc-ticket-manager' ) );
		}

		return $text;
	}

	/**
	 * Change add to cart url for ticket products.
	 *
	 * @param string $url URL.
	 * @param object $product Product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function change_add_to_cart_url( $url, $product ) {
		if ( wctm_is_ticket_product( $product ) ) {
			$url = get_permalink( $product->get_id() );
		}

		return $url;
	}

	/**
	 * Remove add to cart class from loop.
	 *
	 * @param string $html HTML.
	 * @param object $product Product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function loop_remove_add_to_cart_class( $html, $product ) {
		if ( wctm_is_ticket_product( $product ) ) {
			$html = str_replace( 'add_to_cart_button', '', $html );
		}

		return $html;
	}

	/**
	 * Set sold individually for ticket products.
	 *
	 * @param bool $sold_individually Sold individually.
	 * @param int  $product_id Product ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_sold_individually( $sold_individually, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! wctm_is_ticket_product( $product ) ) {
			return $sold_individually;
		}

		return true;
	}

	/**
	 * Render ticket fields.
	 *
	 * @since 1.0.0
	 */
	public static function render_ticket_fields() {
		$product = wc_get_product( get_the_ID() );
		if ( ! wctm_is_ticket_product( $product ) ) {
			return;
		}
		$fields = get_post_meta( $product->get_id(), '_wctm_ticket_fields', true );
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}

		wc_get_template( 'single-product/ticket-fields.php', array( 'fields' => $fields ), wc_ticket_manager()->get_slug(), wc_ticket_manager()->get_template_path() );
	}

	/**
	 * Validate required fields on add to cart.
	 *
	 * @param bool $passed Passed.
	 * @param int  $product_id Product ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function validate_ticket_fields( $passed, $product_id ) {
		$product   = wc_get_product( $product_id );
		$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		if ( ! $product || ! wctm_is_ticket_product( $parent_id ) ) {
			return $passed;
		}
		$fields = wctm_get_ticket_fields( $parent_id );
		if ( empty( $fields ) ) {
			return $passed;
		}
		$required_fields = wp_list_filter( $fields, array( 'required' => 'yes' ) );
		$posted_data     = isset( $_POST['wctm_ticket_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wctm_ticket_fields'] ) ) : array();
		$data            = wctm_get_validated_fields_data( $fields, $posted_data );

		// Check required fields.
		foreach ( $required_fields as $required_field ) {
			if ( empty( $data[ $required_field['name'] ] ) ) {
				// translators: %s: field label.
				wc_add_notice( sprintf( __( '%s is a required field.', 'wc-ticket-manager' ), $required_field['label'] ), 'error' );
				$passed = false;

				break;
			}
		}

		return $passed;
	}


	/**
	 * Add ticket fields to order item meta.
	 *
	 * @param array $cart_item_meta Cart item meta.
	 * @param int   $product_id Product ID.
	 *
	 * @since 1.0.0
	 */
	public static function add_cart_item_data( $cart_item_meta, $product_id ) {
		if ( ! wctm_is_ticket_product( $product_id ) ) {
			return $cart_item_meta;
		}
		$data          = array();
		$ticket_fields = wctm_get_ticket_fields( $product_id );
		if ( ! empty( $ticket_fields ) ) {
			$posted         = isset( $_POST['wctm_ticket_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wctm_ticket_fields'] ) ) : array();
			$data['fields'] = wctm_get_validated_fields_data( $ticket_fields, $posted );
		}
		$cart_item_meta['wctm_ticket_data'] = $data;

		return $cart_item_meta;
	}

	/**
	 * Update cart message when adding tickets to cart.
	 *
	 * @param string $message Cart message.
	 * @param int    $product_id Product ID.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function add_to_cart_message( $message, $product_id ) {
		if ( ! wctm_is_ticket_product( $product_id ) ) {
			return $message;
		}

		$quantity = filter_input( INPUT_POST, 'quantity', FILTER_VALIDATE_INT );
		$quantity = $quantity ? $quantity : 1;
		// translators: %d: quantity.
		$text = sprintf( _n( '%d ticket added to your cart.', '%d tickets added to your cart.', $quantity, 'wc-ticket-manager' ), $quantity );

		// output success message.
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'wc-ticket-manager' ), esc_html( $text ) );
		} else {
			$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_checkout_url() ), esc_html__( 'Checkout', 'wc-ticket-manager' ), esc_html( $text ) );
		}

		return $message;
	}

	/**
	 * Put metadata into format which can be displayed
	 *
	 * @param mixed $other_data other data to display.
	 * @param mixed $cart_item cart item.
	 *
	 * @return array meta
	 */
	public static function get_item_data( $other_data, $cart_item ) {
		$product_id = $cart_item['product_id'];
		if ( empty( $product_id ) || empty( $cart_item['wctm_ticket_data'] ) ) {
			return $other_data;
		}
		$data         = $cart_item['wctm_ticket_data'];
		$field_values = isset( $data['fields'] ) ? $data['fields'] : array();
		$fields       = wctm_get_ticket_fields( $product_id, $field_values );
		foreach ( $fields as $field ) {
			if ( empty( $field['value'] ) ) {
				continue;
			}
			$other_data[] = array(
				'key'     => isset( $field['label'] ) ? stripslashes( $field['label'] ) : $field['name'], // translators: %s: field label.
				'value'   => esc_html( stripslashes( $field['value'] ) ),
				'display' => '',
			);
		}

		return $other_data;
	}
}
