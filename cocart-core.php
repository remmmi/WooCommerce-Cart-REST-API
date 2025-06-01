<?php
/**
 * Plugin Name: CoCart Core
 * Plugin URI:  https://cocartapi.com
 * Description: The core of CoCart helps you get started to decouple your WooCommerce store easy.
 * Author:      CoCart Headless, LLC
 * Author URI:  https://cocartapi.com
 * Version:     5.0.0-beta.10
 * Text Domain: cocart-core
 * Domain Path: /languages/
 * Requires at least: 6.3
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * Copyright:   CoCart Headless, LLC
 * License:     GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

if ( ! defined( 'COCART_SLUG' ) ) {
	define( 'COCART_SLUG', 'cocart-core' );
}

require_once untrailingslashit( __DIR__ ) . '/class-cocart-integrity-check.php';

// Include the main CoCart class.
if ( ! class_exists( 'CoCart', false ) ) {
	include_once untrailingslashit( __DIR__ ) . '/includes/class-cocart.php';
}

/**
 * Returns the main instance of CoCart and only runs if it does not already exists.
 *
 * @since   2.1.0
 * @version 3.0.7
 * @return CoCart
 */
if ( ! function_exists( 'CoCart' ) ) {
	/**
	 * Initialize CoCart.
	 */
	function CoCart() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return CoCart::init();
	}

	CoCart();
}
