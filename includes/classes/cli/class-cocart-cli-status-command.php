<?php
/**
 * WP-CLI: CoCart Statuses command class file.
 *
 * @author  Sébastien Dumont
 * @package CoCart\CLI
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages CoCart via CLI.
 *
 * @version 5.0.0
 * @package CoCart\CLI
 */
class CoCart_CLI_Status_Command {

	/**
	 * Returns all statuses for CoCart.
	 *
	 * ## OPTIONS
	 *
	 * [--format]
	 * : If set, the command will use the specified format. Possible values are table, json, csv and yaml. By default the table format will be used.
	 *
	 * ## EXAMPLES
	 *
	 *     # List all statuses for CoCart in table format with all the fields.
	 *     wp cocart status
	 *
	 *     # List all statuses for CoCart in csv format with all the fields.
	 *     wp cocart status --format=csv
	 *
	 * @when after_wp_load
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param array $args       WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function status( array $args, array $assoc_args ) {
		global $wpdb;

		$wpdb->hide_errors();

		$items = array(
			array(
				'status'  => esc_html__( 'Carts in Session', 'cocart-core' ),
				'results' => cocart_carts_in_session(),
			),
			array(
				'status'  => esc_html__( 'Carts Active', 'cocart-core' ),
				'results' => cocart_count_carts_active(),
			),
			array(
				'status'  => esc_html__( 'Carts Expiring Soon', 'cocart-core' ),
				'results' => cocart_count_carts_expiring(),
			),
			array(
				'status'  => esc_html__( 'Carts Expired', 'cocart-core' ),
				'results' => cocart_count_carts_expired(),
			),
			array(
				'status'  => sprintf( esc_html__( 'Carts Created (%s)', 'cocart-core' ), esc_html__( 'by CoCart', 'cocart-core' ) ),
				'results' => cocart_carts_source_headless(),
			),
			array(
				'status'  => sprintf( esc_html__( 'Carts Created (%s)', 'cocart-core' ), esc_html__( 'by Web', 'cocart-core' ) ),
				'results' => cocart_carts_source_web(),
			),
			array(
				'status'  => sprintf( esc_html__( 'Carts Created (%s)', 'cocart-core' ), esc_html__( 'by Other', 'cocart-core' ) ),
				'results' => cocart_carts_source_other(),
			),
		);

		WP_CLI\Utils\format_items( $assoc_args['format'], $items, array( 'status', 'results' ) );
	} // END status()
} // END class
