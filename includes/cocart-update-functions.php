<?php
/**
 * CoCart Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.0.0
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update CoCart session database structure to version 3.0.0.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_update_300_db_structure() {
	global $wpdb;

	$source_exists = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->prefix}cocart_carts WHERE key_name = 'cart_created'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( is_null( $source_exists ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_created` BIGINT UNSIGNED NOT NULL AFTER `cart_value`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_source` VARCHAR(200) NOT NULL AFTER `cart_expiry`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_hash` VARCHAR(200) NOT NULL AFTER `cart_source`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	}
}

/**
 * Update CoCart session database structure to version 4.3.23.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_update_4323_db_structure() {
	global $wpdb;

	$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts MODIFY cart_id bigint(20) unsigned NOT NULL AUTO_INCREMENT" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts MODIFY cart_created bigint(20) unsigned NOT NULL" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts MODIFY cart_expiry bigint(20) unsigned NOT NULL" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
}

/**
 * Update database version to 3.0.0
 */
function cocart_update_300_db_version() {
	CoCart_Install::update_db_version( '3.0.0' );
}

/**
 * Update database version to 4.1.0
 */
function cocart_update_4323_db_version() {
	CoCart_Install::update_db_version( '4.3.23' );
}
