<?php

namespace WooCommerceTicketManager\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Account class
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager\Frontend
 * @category Class
 */
class MyAccount {

	/**
	 * Account constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_items' ) );
		add_action( 'woocommerce_account_tickets_endpoint', array( __CLASS__, 'my_tickets' ) );
		add_action( 'woocommerce_account_view-ticket_endpoint', array( __CLASS__, 'view_ticket' ) );
	}

	/**
	 * Add tickets query var.
	 *
	 * @param array $vars Query vars.
	 *
	 * @return array altered query vars
	 * @since 1.0.0
	 */
	public static function add_query_vars( $vars ) {
		$vars['tickets']     = 'tickets';
		$vars['view-ticket'] = 'view-ticket';

		return $vars;
	}

	/**
	 * Change title for tickets endpoint.
	 *
	 * @param string $title Current page title.
	 *
	 * @return string Altered page title.
	 * @since 1.0.0
	 */
	public static function endpoint_title( $title ) {
		if ( is_wc_endpoint_url( 'tickets' ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$title = __( 'Tickets', 'wc-ticket-manager' );
			remove_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		} elseif ( is_wc_endpoint_url( 'view-ticket' ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$editing = filter_input( INPUT_GET, 'edit', FILTER_VALIDATE_BOOLEAN );
			$title   = $editing ? __( 'Edit Ticket', 'wc-ticket-manager' ) : __( 'View Ticket', 'wc-ticket-manager' );
			remove_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Add tickets endpoint to My Account menu.
	 *
	 * @param array $items Menu items.
	 *
	 * @return array Altered menu items.
	 * @since 1.0.0
	 */
	public static function menu_items( $items ) {
		$new_items            = array();
		$new_items['tickets'] = __( 'Tickets', 'wc-ticket-manager' );

		return self::insert_new_items_after( $items, $new_items, 'dashboard' );
	}

	/**
	 * My Tickets content.
	 *
	 * @since 1.0.0
	 */
	public static function my_tickets() {
		wc_get_template( 'myaccount/view-tickets.php', array(), '', wc_ticket_manager()->get_template_path() );
	}

	/**
	 * View Ticket content.
	 *
	 * @since 1.0.0
	 */
	public static function view_ticket() {
		$token  = get_query_var( 'view-ticket' );
		$ticket = wctm_get_ticket( $token );
		wc_get_template( 'myaccount/view-ticket.php', array( 'ticket' => $ticket ), '', wc_ticket_manager()->get_template_path() );
	}

	/**
	 * Helper to add new items into an array after a selected item.
	 *
	 * @param array  $items Menu items.
	 * @param array  $new_items New menu items.
	 * @param string $after Key in items.
	 *
	 * @return array Menu items
	 * @since 1.0.0
	 */
	protected static function insert_new_items_after( $items, $new_items, $after ) {
		// Search for the item position and +1 since is after the selected item key.
		$position = array_search( $after, array_keys( $items ), true ) + 1;

		// Insert the new item.
		$array  = array_slice( $items, 0, $position, true );
		$array += $new_items;
		$array += array_slice( $items, $position, count( $items ) - $position, true );

		return $array;
	}
}
