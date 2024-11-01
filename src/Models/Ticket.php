<?php

namespace WooCommerceTicketManager\Models;

use WooCommerceTicketManager\Lib\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Ticket class
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager
 */
class Ticket extends Data {
	/**
	 * Post type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $post_type = 'wctm_ticket';

	/**
	 * All data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array All data.
	 */
	protected $data = array(
		'ticket_number' => '',
		'ticket_token'  => 'post_name',
		'status'        => 'pending',
		'date_created'  => null,
		'product_id'    => 0,
		'variation_id'  => 0,
		'order_id'      => 0,
		'order_item_id' => 0,
		'customer_id'   => 0,
		'fields'        => array(),
	);

	/**
	 * Post data to property map.
	 *
	 * Post data key => property key.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $postdata_map = array(
		'ticket_number' => 'post_title',
		'ticket_token'  => 'post_name',
		'status'        => 'post_status',
		'date_created'  => 'post_date',
		'fields'        => 'post_content',
	);

	/**
	 * Populate data.
	 *
	 * @param int|\WP_Post $data Post ID or object.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function populate_data( $data ) {
		if ( is_string( $data ) && get_page_by_path( $data, OBJECT, $this->post_type ) ) {
			$data = get_page_by_path( $data, OBJECT, $this->post_type );
		}

		return parent::populate_data( $data );
	}

	/**
	 * Save data.
	 *
	 * @since 1.0.0
	 * @return $this|\WP_Error Post object (or WP_Error on failure).
	 */
	public function save() {
		if ( empty( $this->get_prop( 'product_id' ) ) ) {
			return new \WP_Error( 'missing_required', __( 'Missing required product_id', 'wc-ticket-manager' ) );
		}

		if ( empty( $this->get_ticket_token() ) ) {
			$this->set_ticket_token( md5( wp_generate_uuid4() ) );
		}
		// If the product is a variation, set the parent ID. and set the variation ID.
		if ( $this->get_product() && $this->get_product()->is_type( 'variation' ) ) {
			$this->set_prop( 'parent_id', $this->get_product()->get_parent_id() );
			$this->set_prop( 'variation_id', $this->get_product()->get_id() );
		}

		// if date created is not set, set it to now.
		if ( empty( $this->get_prop( 'date_created' ) ) ) {
			$this->set_prop( 'date_created', current_time( 'mysql' ) );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters.
	|--------------------------------------------------------------------------
	| Getters and setters for the data properties.
	*/

	/**
	 * Get ticket number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_ticket_number() {
		return $this->get_prop( 'ticket_number' );
	}

	/**
	 * Set ticket number.
	 *
	 * @param string $ticket_number Ticket number.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_ticket_number( $ticket_number ) {
		$this->set_prop( 'ticket_number', $ticket_number );
	}

	/**
	 * Get ticket token.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_ticket_token() {
		return $this->get_prop( 'ticket_token' );
	}

	/**
	 * Set ticket token.
	 *
	 * @param string $ticket_token Ticket token.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_ticket_token( $ticket_token ) {
		$this->set_prop( 'ticket_token', sanitize_key( $ticket_token ) );
	}

	/**
	 * Get product id.
	 *
	 * @since 1.0.0
	 * @return int Product id.
	 */
	public function get_product_id() {
		return (int) $this->get_prop( 'product_id' );
	}

	/**
	 * Set product id.
	 *
	 * @param int $product_id Product id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_product_id( $product_id ) {
		$this->set_prop( 'product_id', absint( $product_id ) );
	}

	/**
	 * Get variation id.
	 *
	 * @since 1.0.0
	 * @return int Variation id.
	 */
	public function get_variation_id() {
		return (int) $this->get_prop( 'variation_id' );
	}

	/**
	 * Set variation id.
	 *
	 * @param int $variation_id Product id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_variation_id( $variation_id ) {
		$this->set_prop( 'variation_id', absint( $variation_id ) );
	}

	/**
	 * Get order id.
	 *
	 * @since 1.0.0
	 * @return int Order id.
	 */
	public function get_order_id() {
		return (int) $this->get_prop( 'order_id' );
	}

	/**
	 * Set order id.
	 *
	 * @param int $order_id Product id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_order_id( $order_id ) {
		$this->set_prop( 'order_id', absint( $order_id ) );
	}

	/**
	 * Get order item id.
	 *
	 * @since 1.0.0
	 * @return int Order item id.
	 */
	public function get_order_item_id() {
		return (int) $this->get_prop( 'order_item_id' );
	}


	/**
	 * Set order_item id.
	 *
	 * @param int $order_item_id Product id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_order_item_id( $order_item_id ) {
		$this->set_prop( 'order_item_id', absint( $order_item_id ) );
	}

	/**
	 * Get customer id.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_customer_id() {
		return (int) $this->get_prop( 'customer_id' );
	}


	/**
	 * Set customer id.
	 *
	 * @param int $customer_id Product id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_customer_id( $customer_id ) {
		$this->set_prop( 'customer_id', absint( $customer_id ) );
	}

	/**
	 * Get fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_fields() {
		$fields = $this->get_prop( 'fields' );

		return is_array( $fields ) ? $fields : array();
	}

	/**
	 * Set fields.
	 *
	 * @param array $fields Fields.
	 *
	 * @return void
	 */
	public function set_fields( $fields ) {
		$fields = is_array( $fields ) ? $fields : array();
		$this->set_prop( 'fields', $fields );
	}

	/**
	 * Get status.
	 *
	 * @since 1.0.0
	 * @return string Post status.
	 */
	public function get_status() {
		return $this->get_prop( 'post_status' );
	}

	/**
	 * Set status.
	 *
	 * @param string $status Post status.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_status( $status ) {
		$this->set_prop( 'status', sanitize_key( $status ) );
	}

	/**
	 * Get date created.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_date_created() {
		$prop = $this->get_prop( 'date_created' );
		// check the date is valid then format as July 17, 2023.
		if ( ! empty( $prop ) && strtotime( $prop ) ) {
			return date_i18n( 'F j, Y', strtotime( $prop ) );
		}

		return null;
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	| Methods which do not modify class properties but are used by the class.
	*/

	/**
	 * Get product.
	 *
	 * @since 1.0.0
	 * @return \WC_Product|false The product, or false if not found.
	 */
	public function get_product() {
		if ( $this->get_product_id() ) {
			return wc_get_product( $this->get_product_id() );
		}

		return false;
	}

	/**
	 * Get product title.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_product_title() {
		return $this->get_product() ? get_the_title( $this->get_product()->get_id() ) : '';
	}

	/**
	 * Get order.
	 *
	 * @since 1.0.0
	 * @return \WC_Order|false The order, or false if not found.
	 */
	public function get_order() {
		if ( $this->get_order_id() ) {
			return wc_get_order( $this->get_order_id() );
		}

		return false;
	}

	/**
	 * Get customer.
	 *
	 * @since 1.0.0
	 * @return \WC_Customer|false The customer, or false if not found.
	 */
	public function get_customer() {
		if ( $this->get_customer_id() ) {
			return new \WC_Customer( $this->get_customer_id() );
		}

		return false;
	}

	/**
	 * Get customer name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_customer_name() {
		$customer = $this->get_customer();
		if ( $customer ) {
			return implode( ' ', array_filter( array( $customer->get_first_name(), $customer->get_last_name() ) ) );
		}

		return '';
	}


	/**
	 * Is ticket editable.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_editable() {
		$product_id = $this->get_product_id();
		$settings   = get_post_meta( $product_id, '_wctm_allow_edit', true );
		if ( empty( $settings ) || 'disable' === $settings ) {
			return false;
		}
		if ( 'global' === $settings ) {
			return 'yes' === get_option( 'wctm_allow_edit', 'no' );
		}

		return 'enable' === $settings;
	}

	/**
	 * Ticket URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_ticket_url() {
		return wc_get_endpoint_url( 'view-ticket', $this->get_ticket_token(), wc_get_page_permalink( 'myaccount' ) );
	}
}
