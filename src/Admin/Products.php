<?php

namespace WooCommerceTicketManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products.
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager\Admin
 */
class Products {

	/**
	 * Products constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'product_type_options', array( __CLASS__, 'ticket_type_option' ) );
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'ticket_tabs' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'ticket_panels' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_ticket_options' ) );
		add_action( 'wc_ticket_manager_ticket_options', array( __CLASS__, 'ticket_fields_options' ) );
	}

	/**
	 * Add 'Ticket' option to products.
	 *
	 * @param array $options Default options.
	 *
	 * @since 1.0.0
	 * @return array Modified options.
	 */
	public static function ticket_type_option( $options = array() ) {
		$options['wctm_ticket'] = apply_filters(
			'wc_ticket_manager_product_type_option',
			array(
				'id'            => '_wctm_ticket',
				'wrapper_class' => 'show_if_simple show_if_variable hide_if_subscription hide_if_variable-subscription hide_if_grouped hide_if_external',
				'label'         => __( 'Sell Ticket', 'wc-ticket-manager' ),
				'description'   => __( 'Sell tickets for this product.', 'wc-ticket-manager' ),
				'default'       => 'no',
			)
		);

		return $options;
	}

	/**
	 * Add Ticket related tabs.
	 *
	 * @param array $tabs Default tabs.
	 *
	 * @since 1.0.0
	 * @return array Modified tabs.
	 */
	public static function ticket_tabs( $tabs ) {
		// ticket options.
		$tabs['wctm_ticket_options'] = array(
			'label'  => __( 'Ticket Options', 'wc-ticket-manager' ),
			'target' => 'wctm_ticket_options_data',
			'class'  => array( 'show_if_wctm_ticket' ),
		);

		return $tabs;
	}

	/**
	 * Add Ticket related panels.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function ticket_panels() {
		global $post;
		$product = wc_get_product( $post->ID );
		include __DIR__ . '/views/product-ticket-options.php';
	}

	/**
	 * Ticket fields options.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function ticket_fields_options( $product ) {
		$default = array(
			array(
				'label'       => __( 'Name', 'wc-ticket-manager' ),
				'placeholder' => __( 'Name', 'wc-ticket-manager' ),
				'type'        => 'text',
				'required'    => 'yes',
				'enabled'     => 'yes',
			),
			array(
				'label'       => __( 'Email', 'wc-ticket-manager' ),
				'placeholder' => __( 'Email', 'wc-ticket-manager' ),
				'type'        => 'email',
				'required'    => 'yes',
				'enabled'     => 'yes',
			),
		);
		$types   = array(
			'text'  => __( 'Text', 'wc-ticket-manager' ),
			'email' => __( 'Email', 'wc-ticket-manager' ),
		);
		$fields  = get_post_meta( $product->get_id(), '_wctm_ticket_fields', true );
		$fields  = ! empty( $fields ) && is_array( $fields ) ? $fields : $default;
		require __DIR__ . '/views/product-ticket-fields.php';
	}

	/**
	 * Save ticket options.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_ticket_options( $post_id ) {
		$is_ticket = filter_input( INPUT_POST, '_wctm_ticket', FILTER_SANITIZE_SPECIAL_CHARS ) === 'on' ? 'yes' : 'no';
		update_post_meta( $post_id, '_wctm_ticket', $is_ticket );
		$date_options = array();
		foreach ( $date_options as $date_option ) {
			$date = filter_input( INPUT_POST, $date_option, FILTER_SANITIZE_SPECIAL_CHARS );
			// validate if date is valid.
			if ( ! empty( $date ) && strtotime( $date ) === false ) {
				delete_post_meta( $post_id, $date_option );
			} else {
				update_post_meta( $post_id, $date_option, sanitize_text_field( $date ) );
			}
		}

		$int_options = array(
			'_wctm_ticket_number_length',
		);
		foreach ( $int_options as $int_option ) {
			$int = filter_input( INPUT_POST, $int_option, FILTER_VALIDATE_INT );
			if ( empty( $int ) ) {
				delete_post_meta( $post_id, $int_option );
			} else {
				update_post_meta( $post_id, $int_option, absint( $int ) );
			}
		}

		$radio_options = array(
			'_wctm_ticket_number_settings',
			'_wctm_ticket_number_type',
		);
		foreach ( $radio_options as $radio_option ) {
			$radio = filter_input( INPUT_POST, $radio_option, FILTER_SANITIZE_SPECIAL_CHARS );
			if ( empty( $radio ) ) {
				delete_post_meta( $post_id, $radio_option );
			} else {
				update_post_meta( $post_id, $radio_option, sanitize_key( $radio ) );
			}
		}

		$text_options = array(
			'_wctm_ticket_number_prefix',
			'_wctm_ticket_number_suffix',
		);

		foreach ( $text_options as $text_option ) {
			$text = filter_input( INPUT_POST, $text_option, FILTER_SANITIZE_SPECIAL_CHARS );
			if ( empty( $text ) ) {
				delete_post_meta( $post_id, $text_option );
			} else {
				update_post_meta( $post_id, $text_option, sanitize_text_field( $text ) );
			}
		}

		$textarea_options = array();

		foreach ( $textarea_options as $textarea_option ) {
			$textarea = isset( $_POST[ $textarea_option ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $textarea_option ] ) ) : '';
			if ( empty( $textarea ) ) {
				delete_post_meta( $post_id, $textarea_option );
			} else {
				update_post_meta( $post_id, $textarea_option, sanitize_textarea_field( $textarea ) );
			}
		}

		$fields          = isset( $_POST['_wctm_ticket_fields'] ) ? map_deep( wp_unslash( $_POST['_wctm_ticket_fields'] ), 'sanitize_text_field' ) : array();
		$prepared_fields = array();
		foreach ( $fields as $field ) {
			$prepared_fields[] = array(
				'name'        => isset( $field['name'] ) ? sanitize_key( $field['name'] ) : uniqid( 'wctm_' ),
				'type'        => isset( $field['type'] ) ? sanitize_text_field( $field['type'] ) : 'text',
				'label'       => isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '',
				'placeholder' => isset( $field['placeholder'] ) ? sanitize_text_field( $field['placeholder'] ) : '',
				'required'    => isset( $field['required'] ) ? 'yes' : 'no',
				'enabled'     => isset( $field['enabled'] ) ? 'yes' : 'no',
			);
		}
		update_post_meta( $post_id, '_wctm_ticket_fields', $prepared_fields );
	}
}
