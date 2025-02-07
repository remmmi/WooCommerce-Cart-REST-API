<?php
/**
 * CoCart - Product Brands controller
 *
 * Handles requests to the products/brands endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Brands controller class.
 *
 * @package CoCart/API
 * @extends CoCart_REST_Product_Categories_V2_Controller
 */
class CoCart_REST_Product_Brands_V2_Controller extends CoCart_REST_Product_Categories_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/brands';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_brand';
}
