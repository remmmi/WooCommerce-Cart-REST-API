<?php
/**
 * Abstract: CoCart_REST_Cart_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   5.0.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Cart Controller Class.
 *
 * This class extends `CoCart_REST_Controller`. It's required to follow "Controller Classes" guide
 * before extending this class: <https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/>
 *
 * NOTE THAT ONLY CODE RELEVANT FOR THE CART ENDPOINTS SHOULD BE INCLUDED INTO THIS CLASS.
 *
 * @since   5.0.0 Introduced.
 * @extends CoCart_REST_Controller
 * @see     https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/
 */
abstract class CoCart_REST_Cart_Controller extends WP_REST_Controller {
	// @todo Change to extend `CoCart_REST_Controller` once abstract branch is merged.

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->get_path_regex();
	}

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	protected function get_path_regex() {
		return '/cart';
	}

	/**
	 * Permission callback checks if the cart was initialized.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return \WP_Error|bool Returns error if failed else true.
	 */
	public function check_cart_instance() {
		$cart_instance = $this->get_cart_instance();

		if ( ! is_wp_error( $cart_instance ) ) {
			return true;
		}

		return $cart_instance;
	} // END check_cart_instance()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return \WP_Error|\WC_Cart Error response or the cart object.
	 */
	public function get_cart_instance() {
		$cart = WC()->cart;

		if ( is_wp_error( $cart ) ) {
			return $cart;
		}

		try {
			if ( ! $cart || ! $cart instanceof \WC_Cart ) {
				throw new CoCart_Data_Exception( 'cocart_cart_error', esc_html__( 'Unable to retrieve cart.', 'cocart-core' ), 500 );
			}

			return $cart;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_instance()

	/**
	 * Gets the cart contents.
	 *
	 * @access public
	 *
	 * @since 2.0.0 Introduced.
	 *
	 * @deprecated 5.0.0 No longer use `$cart_item_key` parameter. Left for declaration compatibility.
	 *
	 * @see CoCart_REST_Cart_V2_Controller::is_completely_empty()
	 * @see CoCart_REST_Cart_V2_Controller::calculate_totals()
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param string          $cart_item_key Cart item key.
	 *
	 * @return array $cart_contents The cart contents.
	 */
	public function get_cart_contents( $request = array(), $cart_item_key = '' ) {
		$show_raw       = ! empty( $request['raw'] ) ? $request['raw'] : false; // Internal parameter request.
		$dont_check     = ! empty( $request['dont_check'] ) ? $request['dont_check'] : false; // Internal parameter request.
		$dont_calculate = ! empty( $request['dont_calculate'] ) ? $request['dont_calculate'] : false; // Internal parameter request.
		$cart_contents  = ! $this->is_completely_empty() ? $this->get_cart_instance()->cart_contents : array();

		// Return cart contents raw if requested.
		if ( $show_raw ) {
			return $cart_contents;
		}

		if ( ! $dont_check ) {
			/**
			 * Filter allows you to modify the cart contents before it calculate totals.
			 *
			 * WARNING: Unsetting any default data will cause the API to fail. Only use this filter if really necessary.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @hooked: check_cart_validity - 0
			 * @hooked: check_cart_item_stock - 10
			 * @hooked: check_cart_coupons - 15
			 *
			 * @param array           $cart_contents The cart contents.
			 * @param WC_Cart         $cart          The cart object.
			 * @param WP_REST_Request $request       The request object.
			 */
			$cart_contents = apply_filters( 'cocart_before_get_cart', $cart_contents, $this->get_cart_instance(), $request );
		}

		// Ensures the cart totals are calculated before an API response is returned.
		if ( ! $dont_calculate ) {
			$this->calculate_totals();
		}

		if ( ! $dont_check ) {
			/**
			 * Filter allows you to modify the cart contents after it has calculated totals.
			 *
			 * WARNING: Unsetting any default data will cause the API to fail. Only use this filter if really necessary.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @param array           $cart_contents The cart contents.
			 * @param WC_Cart         $cart          The cart object.
			 * @param WP_REST_Request $request       The request object.
			 */
			$cart_contents = apply_filters( 'cocart_after_get_cart', $cart_contents, $this->get_cart_instance(), $request );
		}

		return $cart_contents;
	} // END get_cart_contents()

	/**
	 * Return a cart item from the cart.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @param string $item_id   The item we are looking up in the cart.
	 * @param string $condition Default is 'add', other conditions are: container, update, remove, restore.
	 *
	 * @return array $item Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$item = isset( $this->get_cart_instance()->cart_contents[ $item_id ] ) ? $this->get_cart_instance()->cart_contents[ $item_id ] : array();

		/**
		 * Filters the cart item before it is returned.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array  $item      Details of the item in the cart if it exists.
		 * @param string $condition Condition of item. Default: "add", Option: "add", "remove", "restore", "update".
		 */
		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // EMD get_cart_item()

	/**
	 * Returns all cart items.
	 *
	 * @access public
	 *
	 * @param callable $callback Optional callback to apply to the array filter.
	 *
	 * @return array $items Returns all cart items.
	 */
	public function get_cart_items( $callback = null ) {
		return $callback ? array_filter( $this->get_cart_instance()->get_cart(), $callback ) : array_filter( $this->get_cart_instance()->get_cart() );
	} // END get_cart_items()

	/**
	 * Returns true if the cart is completely empty.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @see CoCart_REST_Cart_V2_Controller::get_removed_cart_contents_count()
	 *
	 * @return bool True if the cart is completely empty.
	 */
	public function is_completely_empty() {
		if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && $this->get_removed_cart_contents_count() <= 0 ) {
			return true;
		}

		return false;
	} // END is_completely_empty()

	/**
	 * Get number of removed items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return int Number of removed items in the cart.
	 */
	public function get_removed_cart_contents_count() {
		return array_sum( wp_list_pluck( $this->get_cart_instance()->get_removed_cart_contents(), 'quantity' ) );
	} // END get_removed_cart_contents_count()

	/**
	 * Ensures the cart totals are calculated before an API response is returned.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Calculate shipping was removed here because it's called already by calculate_totals.
	 */
	public function calculate_totals() {
		$this->get_cart_instance()->calculate_fees();
		$this->get_cart_instance()->calculate_totals();
	} // END calculate_totals()

	/**
	 * Validates item and check for errors before added to cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @see CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable()
	 * @see CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product()
	 * @see CoCart_REST_Cart_V2_Controller::get_product_quantity_in_cart()
	 *
	 * @param WC_Product $product  The product object.
	 * @param int|float  $quantity Quantity of product to validate availability.
	 */
	public function validate_add_to_cart( $product, $quantity ) {
		try {
			// Product is purchasable check.
			if ( ! $product->is_purchasable() ) {
				CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable( $product );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product->is_in_stock() ) {
				$message = sprintf(
					/* translators: %s: Product name */
					__( 'You cannot add "%s" to the cart because the product is out of stock.', 'cocart-core' ),
					$product->get_name()
				);

				/**
				 * Filters message about product is out of stock.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_out_of_stock', $message, 404 );
			}

			if ( ! $product->has_enough_stock( $quantity ) ) {
				$stock_quantity = $product->get_stock_quantity();

				if ( $stock_quantity > 0 ) {
					$message = sprintf(
						/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
						__( 'You cannot add that amount of (%1$s) for "%2$s" to the cart because there is not enough stock, only (%3$s remaining).', 'cocart-core' ),
						$quantity,
						$product->get_name(),
						wc_format_stock_quantity_for_display( $stock_quantity, $product )
					);
				} else {
					$message = sprintf(
						/* translators: 1: Product Name */
						__( 'You cannot add %1$s to the cart as it is no longer in stock.', 'cocart-core' ),
						$product->get_name()
					);
				}

				/**
				 * Filters message about product not having enough stock.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param string     $message        Message.
				 * @param WC_Product $product        The product object.
				 * @param int        $stock_quantity Quantity remaining.
				 */
				$message = apply_filters( 'cocart_product_not_enough_stock_message', $message, $product, $stock_quantity );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 404 );
			}

			// Stock check - this time accounting for whats already in-cart and look up what's reserved.
			if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
				$qty_remaining = CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product( $product );
				$qty_in_cart   = $this->get_product_quantity_in_cart( $product );

				if ( $qty_remaining < $qty_in_cart + $quantity ) {
					$message = sprintf(
						/* translators: 1: product name, 2: Quantity in Stock, 3: Quantity in Cart */
						__( 'You cannot add that amount of "%1$s" to the cart &mdash; we have (%2$s) in stock remaining. You already have (%3$s) in your cart.', 'cocart-core' ),
						$product->get_name(),
						wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ),
						wc_format_stock_quantity_for_display( $qty_in_cart, $product )
					);

					throw new CoCart_Data_Exception( 'cocart_not_enough_stock_remaining', $message, 404 );
				}
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_add_to_cart()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @deprecated 3.0.0 `$variation_id` parameter is no longer used.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::validate_product_for_cart()
	 * @see CoCart_Utilities_Cart_Helpers::validate_variable_product()
	 * @see CoCart_Utilities_Product_Helpers::get_variation_id_from_variation_data()
	 * @see CoCart_REST_Cart_V2_Controller::find_product_in_cart()
	 * @see CoCart_REST_Cart_V2_Controller::is_product_sold_individually()
	 * @see CoCart_REST_Cart_V2_Controller::validate_add_to_cart()
	 *
	 * @param int             $product_id   The product ID.
	 * @param int|float       $quantity     The item quantity.
	 * @param null            $variation_id The variation ID.
	 * @param array           $variation    The variation attributes.
	 * @param array           $item_data    The cart item data
	 * @param string          $product_type The product type.
	 * @param WP_REST_Request $request      The request object.
	 *
	 * @return array Item data.
	 */
	protected function validate_product( $product_id = null, $quantity = 1, $variation_id = null, $variation = array(), $item_data = array(), $product_type = '', $request = array() ) {
		try {
			// Get product and validate product for the cart.
			$product = wc_get_product( $product_id );
			$product = CoCart_Utilities_Cart_Helpers::validate_product_for_cart( $product );

			// Look up the product type if not passed.
			if ( empty( $product_type ) ) {
				$product_type = $product->get_type();
			}

			$variation_id = 0;

			// Set correct product ID's if product type is a variation.
			if ( $product->is_type( 'variation' ) ) {
				$product_id   = $product->get_parent_id();
				$variation_id = $product->get_id();
			}

			// If we have a parent product and no variation ID, find the variation ID.
			if ( $product->is_type( 'variable' ) && 0 === $variation_id ) {
				$variation_id = CoCart_Utilities_Product_Helpers::get_variation_id_from_variation_data( $variation, $product );
			}

			// Throw exception if no variation is found.
			if ( is_wp_error( $variation_id ) ) {
				return $variation_id;
			}

			// Validate variable/variation product.
			if ( 'variable' === $product_type || 'variation' === $product_type ) {
				$variation = CoCart_Utilities_Cart_Helpers::validate_variable_product( $variation_id, $variation, $product );
			}

			// If variables are not valid then return error response.
			if ( is_wp_error( $variation ) ) {
				return $variation;
			}

			/**
			 * Filters add to cart validation.
			 *
			 * @since 2.1.2 Introduced.
			 * @since 3.1.0 Added the request object as parameter.
			 *
			 * @param bool|WP_Error   $passed       Default is true to allow the product to pass validation.
			 * @param int             $product_id   The product ID.
			 * @param int             $quantity     The item quantity.
			 * @param int             $variation_id The variation ID.
			 * @param array           $variation    The variation attributes.
			 * @param array           $item_data    The cart item data.
			 * @param string          $product_type The product type.
			 * @param WP_REST_Request $request      The request object.
			 */
			$passed_validation = apply_filters( 'cocart_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation, $item_data, $product_type, $request );

			// If validation returned an error return error response.
			if ( is_wp_error( $passed_validation ) ) {
				return $passed_validation;
			}

			// If validation returned false.
			if ( ! $passed_validation ) {
				$message = __( 'Product did not pass validation!', 'cocart-core' );

				/**
				 * Filters message about product failing validation.
				 *
				 * @since 1.0.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_failed_validation', $message, 400 );
			}

			// Set cart item data - maybe added by other plugins.
			$item_data = CoCart_Utilities_Cart_Helpers::set_cart_item_data( $item_data, $product_id, $variation_id, $quantity, $product_type, $request );

			// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $item_data );

			// Find the cart item key in the existing cart.
			$item_key = $this->find_product_in_cart( $item_key );

			// The quantity of item added to the cart.
			$quantity = CoCart_Utilities_Cart_Helpers::set_cart_item_quantity( $quantity, $product_id, $variation_id, $variation, $item_data, $request );

			// Validates if item is sold individually.
			$quantity = $this->is_product_sold_individually( $product, $quantity, $product_id, $variation_id, $item_data, $item_key, $request );

			// If product validation returned an error return error response.
			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			// Validates the item before adding to cart.
			$is_valid = $this->validate_add_to_cart( $product, $quantity );

			// If product validation returned an error return error response.
			if ( is_wp_error( $is_valid ) ) {
				return $is_valid;
			}

			// Returns all valid data.
			return array(
				'product_id'   => $product_id,
				'quantity'     => $quantity,
				'variation_id' => $variation_id,
				'variation'    => $variation,
				'item_data'    => $item_data,
				'item_key'     => $item_key,
				'product_data' => $product,
				'request'      => $request,
			);
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product()

	/**
	 * Check if product is in the cart and return cart item key if found.
	 *
	 * Cart item key will be unique based on the item and its properties, such as variations.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @see CoCart_REST_Cart_V2_Controller::get_cart_contents()
	 *
	 * @param mixed $cart_item_key of product to find in the cart.
	 *
	 * @return string Returns the same cart item key if valid.
	 */
	public function find_product_in_cart( $cart_item_key = false ) {
		if ( false !== $cart_item_key ) {
			if ( is_array( $this->get_cart_contents( array( 'raw' => true ) ) ) && ! empty( $this->get_cart_contents( array( 'raw' => true ), $cart_item_key ) ) ) {
				return $cart_item_key;
			}
		}

		return '';
	} // END find_product_in_cart()

	/**
	 * Validates if item is sold individually.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @see CoCart_REST_Cart_V2_Controller::get_cart_contents()
	 *
	 * @param WC_Product      $product      The product object.
	 * @param int|float       $quantity     The quantity to validate.
	 * @param int             $product_id   The product ID.
	 * @param int             $variation_id The variation ID.
	 * @param array           $item_data    The cart item data.
	 * @param string          $item_key     Generated ID based on the product information when added to the cart.
	 * @param WP_REST_Request $request      The request object.
	 *
	 * @return float $quantity The quantity returned.
	 */
	public function is_product_sold_individually( $product, $quantity, $product_id, $variation_id, $item_data, $item_key, $request = array() ) {
		try {
			// Force quantity to 1 if sold individually and check for existing item in cart.
			if ( $product->is_sold_individually() ) {
				$quantity = CoCart_Utilities_Cart_Helpers::set_cart_item_quantity_sold_individually( $quantity, $product_id, $variation_id, $item_data, $request );

				$cart_contents = $this->get_cart_contents( array( 'raw' => true ) );

				$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $item_key && $cart_contents[ $item_key ]['quantity'] > 0, $product_id, $variation_id, $item_data, $item_key );

				if ( $found_in_cart ) {
					$message = sprintf(
						/* translators: %s: Product Name */
						__( "You cannot add another '%s' to your cart.", 'cocart-core' ),
						$product->get_name()
					);

					/**
					 * Filters message about product not being allowed to add another.
					 *
					 * @since 3.0.0 Introduced.
					 *
					 * @param string     $message Message.
					 * @param WC_Product $product The product object.
					 */
					$message = apply_filters( 'cocart_product_can_not_add_another_message', $message, $product );

					throw new CoCart_Data_Exception( 'cocart_product_sold_individually', $message, 403 );
				}
			}

			return $quantity;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END is_product_sold_individually()

	/**
	 * Adds item to cart internally rather than WC.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param int        $product_id     The product ID.
	 * @param int        $quantity       The item quantity.
	 * @param int        $variation_id   The variation ID.
	 * @param array      $variation      The variation attributes.
	 * @param array      $cart_item_data The cart item data
	 * @param WC_Product $product_data   The product object.
	 *
	 * @return string|boolean $item_key Cart item key or false if error.
	 */
	public function add_cart_item( int $product_id, int $quantity, $variation_id, array $variation, array $cart_item_data, WC_Product $product_data ) {
		try {
			// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
			$this->get_cart_instance()->cart_contents[ $item_key ] = apply_filters(
				'cocart_add_cart_item',
				array_merge(
					$cart_item_data,
					array(
						'key'          => $item_key,
						'product_id'   => $product_id,
						'variation_id' => $variation_id,
						'variation'    => $variation,
						'quantity'     => $quantity,
						'data'         => $product_data,
						'data_hash'    => wc_get_cart_item_data_hash( $product_data ),
					)
				),
				$item_key
			);

			$this->get_cart_instance()->cart_contents = apply_filters( 'cocart_cart_contents_changed', $this->get_cart_instance()->cart_contents );

			/**
			 * Fires after item has been added to cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $item_key       Generated ID based on the product information provided.
			 * @param int    $product_id     The product ID.
			 * @param int    $quantity       The item quantity.
			 * @param int    $variation_id   The variation ID.
			 * @param array  $variation      The variation attributes.
			 * @param array  $cart_item_data The cart item data
			 */
			do_action( 'cocart_add_to_cart', $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			return $item_key;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_cart_item()

	/**
	 * Get the query params for the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return array $params The query params.
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

	/**
	 * Extends the query parameters for the cart.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 */
	public function add_additional_params_to_cart( $params ) {
		/**
		 * This filter allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @return array $params The query params.
		 */
		$params += apply_filters( 'cocart_cart_query_parameters', array() );

		return $params;
	} // END add_additional_params_to_cart()
}
