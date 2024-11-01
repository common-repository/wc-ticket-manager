<?php

namespace WooCommerceTicketManager\Admin;

use WooCommerceTicketManager\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menus.
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager\Admin
 */
class Menus {

	/**
	 * Menus constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'main_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 100 );
		add_action( 'wc_ticket_manager_tickets_content', array( $this, 'output_tickets_content' ) );
	}

	/**
	 * Main menu.
	 *
	 * @since 1.0.0
	 */
	public function main_menu() {
		add_menu_page(
			esc_html__( 'Ticket Manager', 'wc-ticket-manager' ),
			esc_html__( 'Ticket Manager', 'wc-ticket-manager' ),
			'manage_options',
			'wc-ticket-manager',
			null,
			'dashicons-tickets-alt',
			'55.5'
		);

		add_submenu_page(
			'wc-ticket-manager',
			esc_html__( 'Tickets', 'wc-ticket-manager' ),
			esc_html__( 'Tickets', 'wc-ticket-manager' ),
			'manage_options',
			'wc-ticket-manager',
			array( $this, 'output_main_page' )
		);
	}

	/**
	 * Settings menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function settings_menu() {
		add_submenu_page(
			'wc-ticket-manager',
			__( 'Settings', 'wc-ticket-manager' ),
			__( 'Settings', 'wc-ticket-manager' ),
			'manage_options',
			'wctm-settings',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Output main page.
	 *
	 * @since 1.0.0
	 */
	public function output_main_page() {
		$page_hook = 'tickets';
		include __DIR__ . '/views/admin-page.php';
	}

	/**
	 * Output tickets content.
	 *
	 * @since 1.0.0
	 */
	public function output_tickets_content() {
		$edit = filter_input( INPUT_GET, 'edit', FILTER_VALIDATE_INT );
		if ( ! empty( $edit ) ) {
			$ticket = wctm_get_ticket( $edit );
			if ( empty( $ticket ) ) {
				wp_safe_redirect( remove_query_arg( 'edit' ) );
				exit();
			}
			include __DIR__ . '/views/edit-ticket.php';
		} else {
			include __DIR__ . '/views/list-ticket.php';
		}
	}
}
