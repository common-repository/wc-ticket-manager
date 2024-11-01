<?php

namespace WooCommerceTicketManager\Admin;

use WooCommerceTicketManager\Admin\ListTables\AbstractListTable;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Admin {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		wc_ticket_manager()->services['admin/menus']    = new Menus();
		wc_ticket_manager()->services['admin/products'] = new Products();
		wc_ticket_manager()->services['admin/orders']   = new Orders();
		wc_ticket_manager()->services['admin/actions']  = new Actions();
		wc_ticket_manager()->services['admin/settings'] = Settings::instance();
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wc_ticket_manager()->enqueue_style( 'wctm-halloween', 'css/admin-halloween.css' );
		wc_ticket_manager()->enqueue_style( 'wc-ticket-manager-admin', 'css/admin-style.css' );
		wc_ticket_manager()->enqueue_script( 'wc-ticket-manager-admin', 'js/admin-script.js' );
		$vars = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'wc-ticket-manager' ),
		);
		wp_localize_script( 'wc-ticket-manager-admin', 'wctm_admin_vars', $vars );
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, self::get_screen_ids() );
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( wc_ticket_manager()->get_review_url() && in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'wc-ticket-manager' ),
				'<strong>' . esc_html( wc_ticket_manager()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_ticket_manager()->get_review_url() ) . '" target="_blank" class="wc-ticket-manager-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-ticket-manager' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		if ( in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-ticket-manager' ), wc_ticket_manager()->get_version() );
		}

		return $footer_text;
	}

	/**
	 * Get screen ids.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_ids = array(
			'toplevel_page_wc-ticket-manager',
			'woocommerce_page_wc-ticket-manager',
			'admin_page_wc-ticket-manager',
			'ticket-manager_page_wctm-tools',
			'ticket-manager_page_wctm-report',
			'ticket-manager_page_wctm-settings',
		);

		return apply_filters( 'wc_ticket_manager_screen_ids', $screen_ids );
	}

	/**
	 * Get list table class.
	 *
	 * @param string $list_table List table class name.
	 *
	 * @return AbstractListTable
	 */
	public static function get_list_table( $list_table ) {
		static $instances = array();
		switch ( $list_table ) {
			case 'tickets':
				$class = 'WooCommerceTicketManager\Admin\ListTables\TicketsListTable';
				break;
			case 'generators':
				$class = 'WooCommerceTicketManager\Admin\ListTables\Generators';
				break;
		}

		if ( $class && class_exists( $class ) && ! isset( $instances[ $class ] ) ) {
			$instances[ $class ] = new $class();
		}

		return $instances[ $class ];
	}
}
