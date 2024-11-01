<?php
/**
 * Ticket Manager Uninstall
 *
 * Uninstalling Ticket Manager deletes user roles, pages, tables, and options.
 *
 * @package     WooCommerceTicketManager
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// remove all the options.
if ( ! defined( 'WC_REMOVE_ALL_DATA' ) || true !== WC_REMOVE_ALL_DATA ) {
	return;
}
// Delete all the options.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wctm_%';" );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'wctm_%';" );
