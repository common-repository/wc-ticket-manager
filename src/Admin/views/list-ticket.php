<?php
/**
 * Ticket List Page
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager\Admin
 */

defined( 'ABSPATH' ) || exit;

$list_table = \WooCommerceTicketManager\Admin\Admin::get_list_table( 'tickets' );
$action     = $list_table->current_action();
$list_table->process_bulk_action( $action );
$list_table->prepare_items();

?>

<h1 class="wp-heading-inline">
	<?php esc_html_e( 'Tickets', 'wc-ticket-manager' ); ?>
</h1>

<form id="wctm-tickets-table" method="get">
	<?php
	$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$list_table->views();
	$list_table->search_box( __( 'Search', 'wc-ticket-manager' ), 'key' );
	$list_table->display();
	?>
	<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
	<input type="hidden" name="page" value="wc-ticket-manager">
</form>


