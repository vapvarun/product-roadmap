<?php
/**
 * Uninstall Product Roadmap.
 *
 * Removes the free plugin's options, transients and the custom votes table.
 * Roadmap content (roadmap_item / roadmap_product / roadmap_changelog posts and
 * their terms) is intentionally left in place so it survives a reinstall.
 *
 * @package Product_Roadmap
 */

// Exit if uninstall is not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete the free plugin's options (roadmap_*), but leave Pro's roadmap_pro_*
// keys for that plugin's own uninstall.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$option_names = $wpdb->get_col(
	"SELECT option_name FROM {$wpdb->options}
	 WHERE option_name LIKE 'roadmap\_%' AND option_name NOT LIKE 'roadmap\_pro\_%'"
);

foreach ( (array) $option_names as $option_name ) {
	delete_option( $option_name );
}

// Drop the custom votes table.
$votes_table = $wpdb->prefix . 'roadmap_votes';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$votes_table}" );

// Clear any leftover transients created by the plugin.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\_transient\_roadmap\_%'
	    OR option_name LIKE '\_transient\_timeout\_roadmap\_%'"
);

// Delete per-user notification opt-out meta for all users.
delete_metadata( 'user', 0, 'roadmap_notifications_optout', '', true );
