<?php
/**
 * Product ticket options.
 *
 * @package WooCommerceTicketManager\Admin\Views
 * @since   1.0.0
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="wctm_ticket_options_data" class="panel woocommerce_options_panel wctm_ticket_options">
	<div id="wctm_ticket_options"></div>
	<?php
	/**
	 * Action before ticket options.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_ticket_manager_before_ticket_options' );
	echo '<div class="options_group">';
	// Whether to autogenerate tickets or sell from generated tickets.
	$ticket_number = get_post_meta( $product->get_id(), '_wctm_ticket_number_settings', true );
	woocommerce_wp_radio(
		array(
			'id'          => '_wctm_ticket_number_settings',
			'label'       => __( 'Ticket Number Settings', 'wc-ticket-manager' ),
			'description' => __( 'Customize ticket number or use global setting.', 'wc-ticket-manager' ),
			'value'       => empty( $ticket_number ) ? 'global' : $ticket_number,
			'options'     => array(
				'global'    => __( 'Global Setting', 'wc-ticket-manager' ),
				'customize' => __( 'Customize', 'wc-ticket-manager' ),
			),
		)
	);
	// Ticket prefix.
	woocommerce_wp_text_input(
		array(
			'id'          => '_wctm_ticket_number_prefix',
			'label'       => __( 'Ticket Prefix', 'wc-ticket-manager' ),
			'description' => __( 'Prefix for the ticket number. Leave blank for no prefix.', 'wc-ticket-manager' ),
			'placeholder' => 'e.g. PREFIX',
			'type'        => 'text',
			'value'       => get_post_meta( $product->get_id(), '_wctm_ticket_number_prefix', true ),
			'class'       => 'short',
		)
	);

	// Ticket suffix.
	woocommerce_wp_text_input(
		array(
			'id'          => '_wctm_ticket_number_suffix',
			'label'       => __( 'Ticket Suffix', 'wc-ticket-manager' ),
			'description' => __( 'Suffix for the ticket number. Leave blank for no suffix.', 'wc-ticket-manager' ),
			'placeholder' => 'e.g. SUFFIX',
			'type'        => 'text',
			'value'       => get_post_meta( $product->get_id(), '_wctm_ticket_number_suffix', true ),
			'class'       => 'short',
		)
	);

	// minimum ticket number length.
	woocommerce_wp_text_input(
		array(
			'id'          => '_wctm_ticket_number_length',
			'label'       => __( 'Min Number Length', 'wc-ticket-manager' ),
			'description' => __( 'Minimum length of the ticket number. Leave blank for no limit.', 'wc-ticket-manager' ),
			'placeholder' => 'e.g. 6',
			'type'        => 'number',
			'value'       => (int) get_post_meta( $product->get_id(), '_wctm_ticket_number_length', true ),
			'class'       => 'short',
		)
	);

	// type of ticket number.
	woocommerce_wp_select(
		array(
			'id'          => '_wctm_ticket_number_type',
			'label'       => __( 'Ticket Number Type', 'wc-ticket-manager' ),
			'desc_tip'    => true,
			'description' => __( 'Type of the ticket number.', 'wc-ticket-manager' ),
			'value'       => get_post_meta( $product->get_id(), '_wctm_ticket_number_type', true ),
			'options'     => array(
				'random'     => __( 'Random', 'wc-ticket-manager' ),
				'sequential' => __( 'Sequential', 'wc-ticket-manager' ),
			),
		)
	);
	echo '</div>';
	/**
	 * Action hook to add more ticket options.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_ticket_manager_ticket_options', $product );
	?>
</div>
<?php
