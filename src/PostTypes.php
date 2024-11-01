<?php

namespace WooCommerceTicketManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class PostTypes.
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager
 */
class PostTypes {

	/**
	 * PostType constructor.
	 *
	 * Responsible for registering all post types.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_post_statuses' ) );
	}

	/**
	 * Register post types.
	 *
	 * @since 1.0.0
	 */
	public static function register_post_types() {
		$labels = array(
			'name'               => _x( 'Tickets', 'post type general name', 'wc-ticket-manager' ),
			'singular_name'      => _x( 'Ticket', 'post type singular name', 'wc-ticket-manager' ),
			'menu_name'          => _x( 'Tickets', 'admin menu', 'wc-ticket-manager' ),
			'name_admin_bar'     => _x( 'Ticket', 'add new on admin bar', 'wc-ticket-manager' ),
			'add_new'            => _x( 'Add New', 'ticket', 'wc-ticket-manager' ),
			'add_new_item'       => __( 'Add New Ticket', 'wc-ticket-manager' ),
			'new_item'           => __( 'New Ticket', 'wc-ticket-manager' ),
			'edit_item'          => __( 'Edit Ticket', 'wc-ticket-manager' ),
			'view_item'          => __( 'View Ticket', 'wc-ticket-manager' ),
			'all_items'          => __( 'All Tickets', 'wc-ticket-manager' ),
			'search_items'       => __( 'Search Tickets', 'wc-ticket-manager' ),
			'parent_item_colon'  => __( 'Parent Tickets:', 'wc-ticket-manager' ),
			'not_found'          => __( 'No tickets found.', 'wc-ticket-manager' ),
			'not_found_in_trash' => __( 'No tickets found in Trash.', 'wc-ticket-manager' ),
		);

		$args = array(
			'labels'              => apply_filters( 'wc_ticket_manager_ticket_post_type_labels', $labels ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'query_var'           => true,
			'can_export'          => false,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'menu_position'       => 50,
			'menu_icon'           => 'dashicons-admin-post',
		);

		register_post_type( 'wctm_ticket', apply_filters( 'wc_ticket_manager_ticket_post_type_args', $args ) );
	}

	/**
	 * Register post statuses.
	 *
	 * @since 1.0.0
	 */
	public static function register_post_statuses() {
		// For a ticket, that is event is over and ticket is not used.
		// For a ticket, that is event is over and ticket is used.
		//
	}
}
