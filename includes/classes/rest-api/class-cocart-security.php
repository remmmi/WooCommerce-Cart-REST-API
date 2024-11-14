<?php
/**
 * REST API: CoCart_Security
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.7.10 Introduced.
 * @version 4.3.9
 * @license GPL-2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Security
 *
 * Responsible for added protection.
 *
 * @since 3.7.10 Introduced.
 */
class CoCart_Security {

	/**
	 * Setup class.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_filter( 'rest_index', array( $this, 'hide_from_rest_index' ) );

		add_filter( 'cocart_products_ignore_private_meta_keys', array( $this, 'remove_exposed_product_meta' ) );
	} // END __construct()

	/**
	 * Hide any CoCart namespace and routes from showing in the WordPress REST API Index.
	 *
	 * @access public
	 *
	 * @param WP_REST_Response $response Response data.
	 *
	 * @return object $response Altered response.
	 */
	public function hide_from_rest_index( $response ) {
		// Check if WP_DEBUG is not defined or is false.
		if ( ! defined( 'WP_DEBUG' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG !== true ) ) {

			// Loop through each registered route.
			foreach ( $response->data['routes'] as $route => $endpoints ) {
				// Check if the current namespace matches any CoCart namespace.
				if ( ! empty( $route ) && strpos( $route, 'cocart' ) !== false ) {
					unset( $response->data['routes'][ $route ] );
				}
			}

			// Loop through each registered namespace.
			foreach ( $response->data['namespaces'] as $key => $namespace ) {
				// Check if the current namespace matches any CoCart namespace.
				if ( ! empty( $namespace ) && strpos( $namespace, 'cocart' ) !== false ) {
					unset( $response->data['namespaces'][ $key ] );
				}
			}
		}

		return $response;
	} // END hide_from_rest_index()

	/**
	 * Removes meta data that a plugin should NOT be outputting with Products API.
	 *
	 * @access public
	 *
	 * @since 4.3.9 Introduced.
	 *
	 * @hooked: cocart_products_ignore_private_meta_keys - 1
	 *
	 * @param array      $ignored_meta_keys Ignored meta keys.
	 * @param WC_Product $product           The product object.
	 *
	 * @return array $ignored_meta_keys Ignored meta keys.
	 */
	public function remove_exposed_product_meta( $ignored_meta_keys, $product ) {
		$meta_data = $product->get_meta_data();

		foreach ( $meta_data as $meta ) {
			if ( 'wcwl_mailout_errors' == $meta->key ) {
				$ignored_meta_keys[] = $meta->key;
			}
		}

		return $ignored_meta_keys;
	} // END remove_exposed_product_meta()
} // END class

return new CoCart_Security();
