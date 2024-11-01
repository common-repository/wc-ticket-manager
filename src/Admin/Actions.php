<?php

namespace WooCommerceTicketManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Actions class.
 *
 * All actions related to the admin area
 * should be added here.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Actions {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_wctm_edit_ticket', array( $this, 'edit_ticket' ) );
	}

	/**
	 * Edit ticket.
	 *
	 * @since 1.0.0
	 */
	public function edit_ticket() {
		check_admin_referer( 'wctm_edit_ticket' );
		$referer = wp_get_referer();
		$id      = filter_input( INPUT_POST, 'ticket_id', FILTER_SANITIZE_NUMBER_INT );
		$ticket  = wctm_get_ticket( $id );

		if ( empty( $ticket ) ) {
			wc_ticket_manager()->add_notice( __( 'Ticket not found.', 'wc-ticket-manager' ), 'error' );
			wp_safe_redirect( $referer );
			exit;
		}
		$data                  = array();
		$ticket_fields         = wctm_get_ticket_fields( $ticket->get_product_id() );
		$posted                = isset( $_POST['wctm_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wctm_fields'] ) ) : array();
		$data['fields']        = wctm_get_validated_fields_data( $ticket_fields, $posted );
		$data['ticket_number'] = wp_unslash( filter_input( INPUT_POST, 'ticket_number', FILTER_SANITIZE_SPECIAL_CHARS ) );
		$ticket->set_data( $data );
		$saved = $ticket->save();

		if ( is_wp_error( $saved ) ) {
			wc_ticket_manager()->add_notice( $saved->get_error_message(), 'error' );
		} else {
			wc_ticket_manager()->add_notice( __( 'Ticket saved successfully.', 'wc-ticket-manager' ), 'success' );
		}

		wp_safe_redirect( $referer );
		exit;
	}
}
