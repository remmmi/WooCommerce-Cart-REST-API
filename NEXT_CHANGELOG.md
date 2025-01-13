# Next Changelog for CoCart Core <!-- omit in toc -->

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

## What's new?

* REST API: Products now return the global unique id. (API v2 Only)
* REST API: Products can now be filtered to include `include_types` or exclude `exclude_types` by multiple types simultaneously using the new parameters. Note: `include_types` takes precedence over `type` parameter should both be used.
* REST API: Products can now be filtered to return virtual products by the boolean `virtual` parameter.
* REST API: New POST method for the cart to create an empty cart for guest customers.

> Developer note: Cart creation is normally done the moment the first item is added to the cart as it has something to save to session. But some users are confused with creating a cart for guest customers. This route can help create an empty cart, storing just the cart key and return it in the response. This guides the developer to check the documentation for more information on how to use the cart key for a guest customer. It is not a requirement to use this route first.

* Plugin: New WP-CLI command `wp cocart status` shows the status of carts in session.
* Plugin: Updates are provided from us for all supported CoCart plugins.
* Plugin: Will deactivate legacy core version if one found installed and active. If you try to activate legacy version while the new core version is active it will deactivate. Recommend deleting.

> Developer note: If you have the `COCART_REMOVE_ALL_DATA` constant set to true. Recommend setting it to false before uninstalling the legacy version from your WordPress dashboard to prevent any issues.

* Plugin: Integrity check of all files.

> Developer note: Know if the version of CoCart is official and has not been tampered with. This is just the first iteration introduced.
> You will never see the warning messages if all is in order, but if you do alter any files within the plugin yourself directly. You will be notified in your WordPress dashboard and in your error log that something has changed and does not match.

## Breaking Changes

* REST API: Avatars only return if requested now when using the login endpoint.
* REST API: Store API now returns array of CoCart versions installed not just the core version.
* Plugin: Text domain a.k.a the plugin slug, has changed from `cart-rest-api-for-woocommerce` to `cocart-core`. This affects any translations including custom. If you did a custom translation you will need to rename the text domain to match.

## Changes

* REST API: The following endpoints for Cart API v2 now extend `CoCart_REST_Cart_V2_Controller` instead of Cart API v1 controller: `cart/add-item`, `cart/add-items`, `cart/calculate`
* WordPress Dashboard: Style adjustments.

## Improvements

* REST API: Only registers CoCart endpoints if requesting it. Helps performance in backend such as when using Gutenberg/Block editor as it loads many API's in the background.
* REST API: Moved more functions and filters to utility class to help improve the complexity of the cart controller so we get better performance.
* REST API: Prevent having to check cart validity, stock and coupons on most cart endpoints other than when getting the cart to help with performance.
* REST API: Optimized how many times we calculate the totals when adding items to the cart to help with performance.
* REST API: Optimized shipping data, added validation and support for recurring carts for subscriptions.
* REST API: Fallback to a wildcard if the origin has yet to be determined for CORS.
* REST API: Override sale and regular price too so the set price is what is shown even if there prices are originally lower.
* Feature: Load cart from session now supports registered customers.
* Localization: Similar messages are now consistent with each other.
* WordPress Dashboard: CoCart is prevented from running in the backend should the REST API server be called by another plugin.

## Third Party Support

* Plugin: LiteSpeed Cache will now exclude CoCart from being cached.

### Load Cart from Session

Originally only designed for guest customers to allow them to checkout via the native site, registered customers can now auto login and load their carts to do the same without exposing login details.

#### How does a registered customer load in without authenticating?

To help customers skip the process of having to login again, we use two data points to validate with that can only be accessed if the user was logged in via the REST API to begin with. This then allows the WordPress site setup as though they had gone through the login process and loads their shopping cart.

The two data points required are the cart key which for the customer logged in via the REST API will be their user ID and the cart hash which represents the last data change of the cart. By using the two together, the customer is able to transfer from the headless version of the store to the native store.

Simply provide these two parameters with the data point values on any page and that's it.

`https://your.store/?cocart-load-cart={cart_key}&c_hash={cart_hash}`

> Developer note: By default, both the cart and checkout pages are still accessible to support the feature "Load cart from Session".

#### Developers

##### New Filters

* Introduced new filter `cocart_load_cart_from_session` allows you to decide if the cart should load user meta when initialized.
* Introduced new filter `cocart_load_cart_redirect_home` allows you to change where to redirect should loading the cart fail.
* Introduced new filter `cocart_cross_sell_item_thumbnail_src` that allows you to change the thumbnail source for a cross sell item.
* Introduced new filter `cocart_http_allowed_safe_ports` that allows you to control the list of ports considered safe for accessing the API.
* Introduced new filter `cocart_allowed_http_origins` that allows you to change the origin types allowed for HTTP requests.
* Introduced new filter `cocart_wp_frontend_url` that allows you to control where to redirect users when visiting your WordPress site if you have disabled access to it.
* Introduced new filter `cocart_wp_disable_access` to disable access to WordPress.
* Introduced new filter `cocart_wp_accessible_page_ids` to allow you to set the page ID's that are still accessible when you disable access to WordPress.
* Introduced new filter `cocart_get_product_slug` to change the product slug returned.
* Introduced new filter `cocart_products_allowed_meta_keys` allows you to specify the allowed meta keys for the product.

> Note: List other filters that have been changed here.

##### New parameters

* Added the request object as a parameter for filter `cocart_add_to_cart_quantity`.
* Added parameters for filter `cocart_add_to_cart_sold_individually_quantity`.
* Added the request object as a parameter for filter `cocart_allow_origin`.
* Added the product object as a parameter for filters `cocart_cart_item_price`, `cocart_cart_item_quantity` and `cocart_cart_item_data`.
* Added the cart class as a parameter for filter `cocart_shipping_package_name`.
* Added new parameter `$recurring_cart` for filter `cocart_available_shipping_packages`.

##### New Functions

* Introduced new function `cocart_get_requested_namespace()` to get the requested CoCart namespace.
* Introduced new function `cocart_get_requested_namespace_version()` to get the requested CoCart namespace version.
* Introduced new function `cocart_get_requested_api()` to get the requested CoCart API.
* Introduced new function `cocart_get_frontend_url()` to get the frontend URL.
* Introduced new function `cocart_is_wp_disabled_access()` to check if WordPress has been disabled access.
* Introduced new function `cocart_get_permalink()` to return the permalink for a page/post/product where the frontend URL maybe replaced.

#### Deprecation's

* Function `cocart_prepare_money_response()` is replaced with function `cocart_format_money()`.

The following filters are no longer used:

* `cocart_load_cart_override`
* `cocart_load_cart`
* `cocart_merge_cart_content`
* `cocart_cart_loaded_successful_message`
* `cocart_use_cookie_monster`
* `cocart_filter_request_data`
* `cocart_products_ignore_private_meta_keys`
