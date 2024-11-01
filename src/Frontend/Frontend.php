<?php

namespace WooCommerceTicketManager\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend class.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Frontend {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init();
		$this->add_actions();
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		wc_ticket_manager()->services['frontend/myaccount'] = new MyAccount();
	}

	/**
	 * Add actions.
	 *
	 * @since 1.0.0
	 */
	public function add_actions() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts' ) );
		add_action( 'wc_ticket_manager_display_ticket_properties', array( __CLASS__, 'display_ticket_properties' ), 10, 1 );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 */
	public static function frontend_scripts() {
		wc_ticket_manager()->register_style( 'wc-ticket-manager', 'css/frontend-style.css' );

		wp_enqueue_style( 'wc-ticket-manager' );
	}

	/**
	 * Ticket properties.
	 *
	 * @param \WooCommerceTicketManager\Models\Ticket $ticket The ticket object.
	 *
	 * @since 1.0.0
	 */
	public static function display_ticket_properties( $ticket ) {
		$props = array(
			array(
				'key'      => 'ticket_number',
				'label'    => __( 'Ticket Number', 'wc-ticket-manager' ),
				'value'    => sprintf( '#%s', $ticket->get_ticket_number() ),
				'priority' => 1,
			),
		);
		// ticket fields.
		$fields = wctm_get_ticket_fields( $ticket->get_product_id(), $ticket->get_fields() );
		foreach ( $fields as $field ) {
			$props[] = array(
				'key'      => $field['name'],
				'label'    => $field['label'],
				'value'    => $field['value'],
				'enabled'  => $field['enabled'],
				'priority' => 3,
			);
		}

		$props = apply_filters( 'wc_ticket_manager_ticket_properties', $props, $ticket );
		usort(
			$props,
			function ( $a, $b ) {
				return $a['priority'] - $b['priority'];
			}
		);

		foreach ( $props as $prop ) {
			if ( ! isset( $prop['key'] ) || ! isset( $prop['label'] ) || ! isset( $prop['value'] ) || ( array_key_exists( 'enabled', $prop ) && 'no' === $prop['enabled'] ) ) {
				continue;
			}
			?>
			<tr class="<?php echo esc_attr( $prop['key'] ); ?>">
				<td><?php echo esc_html( $prop['label'] ); ?></td>
				<td><?php echo empty( $prop['value'] ) ? '&ndash;' : esc_html( $prop['value'] ); ?></td>
			</tr>
			<?php
		}
	}
}
