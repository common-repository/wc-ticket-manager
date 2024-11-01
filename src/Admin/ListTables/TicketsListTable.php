<?php

namespace WooCommerceTicketManager\Admin\ListTables;

use WooCommerceTicketManager\Models\Ticket;

defined( 'ABSPATH' ) || exit();

/**
 * Tickets list table class.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 * @extends AbstractListTable
 */
class TicketsListTable extends AbstractListTable {

	/**
	 * Total count.
	 *
	 * @var int $total_count Total count.
	 *
	 * @since 1.4.7
	 */
	protected $total_count;

	/**
	 * Get things started
	 *
	 * @param array $args Optional.
	 *
	 * @see WP_List_Table::__construct()
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		$args         = (array) wp_parse_args(
			$args,
			array(
				'singular' => 'ticket',
				'plural'   => 'tickets',
			)
		);
		$this->screen = get_current_screen();
		parent::__construct( $args );
	}

	/**
	 * Retrieve the search query string.
	 *
	 * @since 1.4.7
	 * @return string Search query.
	 */
	protected function get_search() {
		return ltrim( $this->get_request_var( 's', '' ), '#' );
	}

	/**
	 * Retrieve the order query string.
	 *
	 * @since 1.4.7
	 * @return string Order query.
	 */
	protected function get_order() {
		return $this->get_request_var( 'order', 'ASC' );
	}

	/**
	 * Retrieve the orderby query string.
	 *
	 * @since 1.4.7
	 * @return string Orderby query.
	 */
	protected function get_orderby() {
		return $this->get_request_var( 'orderby', 'post_status' );
	}

	/**
	 * Retrieve the limit query string.
	 *
	 * @since 1.4.7
	 * @return string Limit query.
	 */
	protected function get_per_page() {
		return $this->get_request_var( 'limit', '20' );
	}

	/**
	 * Retrieve the offset query string.
	 *
	 * @since 1.4.7
	 * @return string Offset query.
	 */
	protected function get_offset() {
		return $this->get_request_var( 'offset', '0' );
	}

	/**
	 * Retrieve all the data for the table.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$hidden                = $this->get_hidden_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$args                  = array(
			'limit'       => $this->get_per_page(),
			'offset'      => $this->get_offset(),
			's'           => $this->get_search(),
			'order'       => $this->get_order(),
			'orderby'     => $this->get_orderby(),
			'post_status' => 'any',
		);

		$meta_props = array(
			'order_id'      => '_order_id',
			'product_id'    => '_product_id',
			'order_item_id' => '_order_item_id',
			'customer_id'   => '_customer_id',
		);
		// If the orderby param is within $meta_props.
		if ( in_array( $args['orderby'], array_keys( $meta_props ), true ) ) {
			$args['meta_key'] = $meta_props[ $args['orderby'] ]; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
		}

		$this->items       = wctm_get_tickets( $args );
		$this->total_count = wctm_get_tickets( $args, true );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_count,
				'per_page'    => $this->get_per_page(),
			)
		);
	}

	/**
	 * No items found text.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No tickets found.', 'wc-ticket-manager' );
	}

	/**
	 * Get the table columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'ticket'       => __( 'Ticket', 'wc-ticket-manager' ),
			'product'      => __( 'Product', 'wc-ticket-manager' ),
			'order'        => __( 'Order', 'wc-ticket-manager' ),
			'customer'     => __( 'Customer', 'wc-ticket-manager' ),
			'date_created' => __( 'Date Created', 'wc-ticket-manager' ),
		);
	}

	/**
	 * Get the table sortable columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'ticket'       => array( 'post_title', true ),
			'product'      => array( 'product_id', true ),
			'order'        => array( 'order_id', true ),
			'customer'     => array( 'customer_id', true ),
			'date_created' => array( 'date_created', true ),
		);
	}

	/**
	 * Get the table hidden columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Get bulk actions.
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wc-ticket-manager' ),
		);
	}


	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since 1.0.2
	 * @return void
	 */
	public function process_bulk_action( $doaction ) {
		if ( ! empty( $doaction ) && check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
			$id  = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
			$ids = isset( $_GET['ids'] ) ? wp_parse_id_list( wp_unslash( $_GET['ids'] ) ) : array();
			if ( ! empty( $id ) ) {
				$ids      = wp_parse_id_list( $id );
				$action_1 = isset( $_GET['action1'] ) ? sanitize_text_field( wp_unslash( $_GET['action1'] ) ) : '';
				$action_2 = isset( $_GET['action2'] ) ? sanitize_text_field( wp_unslash( $_GET['action2'] ) ) : '';
				$doaction = ( -1 !== $action_1 ) ? $action_1 : $action_2;
			} elseif ( ! empty( $ids ) ) {
				$ids = array_map( 'absint', $ids );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			switch ( $doaction ) {
				case 'delete':
					$deleted = 0;
					foreach ( $ids as $id ) {
						$thing = wctm_get_ticket( $id );
						if ( $thing && $thing->delete() ) {
							++$deleted;
						}
					}
					// translators: %d: number of things deleted.
					wc_ticket_manager()->add_notice( sprintf( _n( '%d ticket deleted.', '%d tickets deleted.', $deleted, 'wc-ticket-manager' ), number_format_i18n( $deleted ) ) );
					break;
			}

			wp_safe_redirect( remove_query_arg( array( 'action', 'action2', 'id', 'ids', 'paged' ) ) );
			exit();
		}

		parent::process_bulk_actions( $doaction );
	}


	/**
	 * Define primary column.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_primary_column_name() {
		return 'ticket';
	}

	/**
	 * Renders the checkbox column in the tickets list table.
	 *
	 * @param Ticket $item The current ticket object.
	 *
	 * @since  1.0.0
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="ids[]" value="%d"/>', esc_attr( $item->get_id() ) );
	}


	/**
	 * Renders the ticket column in the tickets list table.
	 *
	 * @param Ticket $item The current ticket object.
	 *
	 * @since  1.0.0
	 * @return string Displays a ticket.
	 */
	public function column_ticket( $item ) {
		$actions = array(
			'edit' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-ticket-manager&edit=' . $item->get_id() ) ), __( 'Edit', 'wc-ticket-manager' ) ),
		);
		$title   = $item->get_ticket_number();
		$status  = 'pending' === $item->get_status() ? esc_html__( ' - Pending', 'wc-ticket-manager' ) : '';
		return sprintf( '<strong><a href="%s">#%s</a>%s</strong>%s', esc_url( admin_url( 'admin.php?page=wc-ticket-manager&edit=' . $item->get_id() ) ), esc_html( $title ), esc_html( $status ), $this->row_actions( $actions ) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param Ticket $item The current ticket object.
	 * @param string $column_name The name of the column.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		$value = '&mdash;';
		switch ( $column_name ) {
			case 'product':
				$product = $item->get_product();
				if ( $product ) {
					$value = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' ) ), esc_html( $product->get_name() ) );
				}
				break;
			case 'order':
				$order = $item->get_order();
				if ( $order ) {
					$value = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ), esc_html( $order->get_order_number() ) );
				}
				break;

			case 'customer':
				$order = $item->get_order();
				if ( $order ) {
					$value = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'user-edit.php?user_id=' . $order->get_customer_id() ) ), esc_html( $order->get_formatted_billing_full_name() ) );
				}
				break;
			default:
				$value = parent::column_default( $item, $column_name );
		}

		return $value;
	}
}
