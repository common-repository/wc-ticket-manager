<?php

namespace WooCommerceTicketManager\Admin;

use WooCommerceTicketManager\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceTicketManager\Admin
 */
class Settings extends Lib\Settings {
	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
		add_action( 'woocommerce_admin_field_wctm_email_message', array( $this, 'email_message_wp_editor' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wctm_email_message', array( $this, 'sanitize_email_message' ), 10, 3 );
	}

	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general' => __( 'General', 'wc-ticket-manager' ),
		);

		return apply_filters( 'wc_ticket_manager_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();

		switch ( $tab ) {
			case 'general':
				$settings = array(
					// ticket number settings.
					array(
						'title' => __( 'Ticket Number Settings', 'wc-ticket-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Set how the ticket number will be generated. The number is also configurable per product.', 'wc-ticket-manager' ),
						'id'    => 'ticket_number_settings',
					),
					array(
						'title'    => __( 'Number type', 'wc-ticket-manager' ),
						'id'       => 'wctm_ticket_number_type',
						'desc'     => __( 'Select the type of ticket number.', 'wc-ticket-manager' ),
						'desc_tip' => __( 'Sequential will start at 1 and go up. Random will generate a random number.', 'wc-ticket-manager' ),
						'default'  => 'sequential',
						'type'     => 'select',
						'options'  => array(
							'sequential' => __( 'Sequential', 'wc-ticket-manager' ),
							'random'     => __( 'Random', 'wc-ticket-manager' ),
						),
					),
					array(
						'title'       => __( 'Number prefix', 'wc-ticket-manager' ),
						'id'          => 'wctm_ticket_number_prefix',
						'desc'        => __( 'Enter text for ticket prefix.', 'wc-ticket-manager' ),
						'desc_tip'    => __( 'This will be added to the beginning of the ticket number.', 'wc-ticket-manager' ),
						'placeholder' => __( 'EVNT-', 'wc-ticket-manager' ),
						'default'     => __( 'EVNT-', 'wc-ticket-manager' ),
						'type'        => 'text',
					),
					array(
						'title'       => __( 'Number suffix', 'wc-ticket-manager' ),
						'id'          => 'wctm_ticket_number_suffix',
						'desc'        => __( 'Enter text for ticket suffix.', 'wc-ticket-manager' ),
						'desc_tip'    => __( 'This will be added to the end of the ticket number.', 'wc-ticket-manager' ),
						'placeholder' => __( 'Suffix', 'wc-ticket-manager' ),
						'type'        => 'text',
					),
					array(
						'title'       => __( 'Number length', 'wc-ticket-manager' ),
						'id'          => 'wctm_ticket_number_length',
						'desc'        => __( 'Enter the length of the ticket number.', 'wc-ticket-manager' ),
						'desc_tip'    => __( 'This will be the length of the ticket number. If the number is less than the length, it will be padded with zeros.', 'wc-ticket-manager' ),
						'placeholder' => __( '6', 'wc-ticket-manager' ),
						'default'     => __( '6', 'wc-ticket-manager' ),
						'type'        => 'number',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'ticket_number_settings',
					),
					array(
						'title' => __( 'Display Options', 'wc-ticket-manager' ),
						'type'  => 'title',
						'id'    => 'display_options',
					),
					// Shop page add to cart text.
					array(
						'title'       => __( 'Shop page add to cart text', 'wc-ticket-manager' ),
						'id'          => 'wctm_shop_add_to_cart_text',
						'desc'        => __( 'Enter text for add to cart button on shop page.', 'wc-ticket-manager' ),
						'placeholder' => __( 'Ticket Details', 'wc-ticket-manager' ),
						'default'     => __( 'Ticket Details', 'wc-ticket-manager' ),
						'type'        => 'text',
					),
					// Product page add to cart text.
					array(
						'title'       => __( 'Product page add to cart text', 'wc-ticket-manager' ),
						'id'          => 'wctm_product_add_to_cart_text',
						'desc'        => __( 'Enter text for add to cart button on product page.', 'wc-ticket-manager' ),
						'placeholder' => __( 'Buy Ticket Now', 'wc-ticket-manager' ),
						'default'     => __( 'Buy Ticket Now', 'wc-ticket-manager' ),
						'type'        => 'text',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'display_options',
					),
				);
				break;
		}

		return apply_filters( 'wc_ticket_manager_get_settings_' . $tab, $settings );
	}

	/**
	 * WP Editor for email message.
	 *
	 * @param array $args Arguments.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function email_message_wp_editor( $args ) {
		$settings     = array(
			'textarea_rows' => 10,
			'teeny'         => true,
			'quicktags'     => true,
		);
		$default_body = '
<p>Hi {first_name},</p>
<p>Thank you so much for purchasing a ticket and hope to see you soon at our event. You can edit your information at any time before the event, by visiting the following link:</p>
<p><a href="{edit_ticket_url}">Edit Ticket</a></p>
		';
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( $args['type'] ); ?>">
				<?php wp_editor( get_option( $args['id'], $default_body ), $args['id'], $settings ); ?>
				<?php echo wp_kses_post( $args['desc_tip'] ); ?>
				<p><?php esc_html_e( 'You can use the following tags in the email subject and content.', 'wc-ticket-manager' ); ?></p>
				<br>
				<table class="widefat striped wctm-template-tags">
					<thead>
					<tr>
						<th style="padding: 8px 10px;"><?php esc_html_e( 'Tag', 'wc-ticket-manager' ); ?></th>
						<th style="padding: 8px 10px;"><?php esc_html_e( 'Description', 'wc-ticket-manager' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td><code>{event_name}</code></td>
						<td><?php esc_html_e( 'Name of the event.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{event_date}</code></td>
						<td><?php esc_html_e( 'Date of the event.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{event_time}</code></td>
						<td><?php esc_html_e( 'Time of the event.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{event_location}</code></td>
						<td><?php esc_html_e( 'Location of the event.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{event_organizer}</code></td>
						<td><?php esc_html_e( 'Organizer of the event.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{ticket_number}</code></td>
						<td><?php esc_html_e( 'Ticket number.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{ticket_url}</code></td>
						<td><?php esc_html_e( 'Ticket URL.', 'wc-ticket-manager' ); ?></td>
					</tr>
					<tr>
						<td><code>{ticket_barcode}</code></td>
						<td><?php esc_html_e( 'Ticket barcode.', 'wc-ticket-manager' ); ?></td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Sanitize email message.
	 *
	 * @param string $value Value.
	 * @param string $option Option name.
	 * @param string $raw_value Raw value.
	 *
	 * @return string
	 */
	public function sanitize_email_message( $value, $option, $raw_value ) {
		return wp_kses_post( $raw_value );
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
	}
}
