<?php
/**
 * CoCart - Plugin Updates.
 *
 * @author  Sébastien Dumont
 * @package CoCart/Admin/Updates
 * @since   4.4.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Updates' ) ) {

	class CoCart_Admin_Updates extends CoCart_Plugin_Updates {

		/**
		 * Update API URL
		 *
		 * ?Dev note: Will be all connected on the $api_url only once API is completed.
		 *
		 * @access protected
		 *
		 * @static
		 *
		 * @var string
		 */
		protected static $update_api_url = 'https://cocartapi.com/wp-json/lsq/v1';

		/**
		 * Setup class.
		 */
		public function __construct() {
			// Return plugin information and check for updates from remote server.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			add_filter( 'plugins_api', array( $this, 'get_remote_plugin_info' ), 20, 3 );

			// Excludes any CoCart plugins from WP.org updates.
			add_filter( 'http_request_args', array( $this, 'exclude_plugins_from_update_check' ), 10, 2 );

			add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

			// Check each CoCart plugin installed.
			foreach ( array_keys( $this->get_installed_plugins() ) as $plugin_file ) {
				add_action( 'in_plugin_update_message-' . $plugin_file, array( $this, 'modify_plugin_update_message' ), 10, 2 );
				add_action( 'after_plugin_row_' . $plugin_file, array( $this, 'license_information' ), 10, 2 );
			}
		} // END __construct()

		/**
		 * Checks if the license key has expired.
		 *
		 * @access protected
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $license_key License key passed if already fetched.
		 *
		 * @return boolean True if expired, false if not.
		 */
		protected function has_license_expired( $plugin_data, $license_key = '' ) {
			if ( empty( $license_key ) ) {
				$license_key = self::get_license_key( $plugin_data );
			}

			// Bail early if we don't have a key.
			if ( ! $license_key ) {
				return false;
			}

			// Verify the license key.
			$results = json_decode( self::verify_license( $license_key ) );

			// If results failed to return then set as false so it can be checked again laster.
			if ( is_wp_error( $results ) ) {
				return false;
			}

			$license_status = $results->license_key->status;

			// If the results don't return the license key disabled or expired then return as false.
			if ( 'disabled' !== $license_status || 'expired' !== $license_status ) {
				return false;
			}

			return true;
		} // END has_license_expired()

		/**
		 * Generates a unique key based on the product slug.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @return string The cache key.
		 */
		public static function get_cache_key( $product_slug ) {
			return '_[cocart_updater_' . intval( strlen( $product_slug ) / 2 ) . ']_';
		} // END get_cache_key()

		/**
		 * Deletes transients and allows a fresh lookup.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 */
		public static function refresh_plugins_transient() {
			delete_site_transient( 'update_plugins' );
			// delete_transient( self::get_cache_key() );
			delete_transient( '_cocart_updates_count' );
		} // END refresh_plugins_transient()

		/**
		 * Fetch the update info from the remote server.
		 *
		 * @access protected
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string  $product_slug Product slug to identify and filter updates.
		 * @param boolean $force_check  Bypasses cached result. Defaults to false.
		 *
		 * @return object|boolean
		 */
		protected function get_updates( $product_slug, $force_check = false ) {
			$license_key = self::get_license_key();

			// Bail early if we don't have a key.
			if ( ! $license_key ) {
				return false;
			}

			// Check cache if $force_check is not overrided.
			if ( ! $force_check ) {
				$remote = get_transient( self::get_cache_key( $product_slug ) );
				if ( false !== $remote ) {
					if ( 'error' === $remote ) {
						return false;
					}

					return json_decode( $remote );
				}
			}

			if ( wp_doing_cron() ) {
				$timeout = 30;
			} else {
				$timeout = 10;
			}

			/**
			 * ?Dev note: Replace API request to check all plugins from CoCart instead of individually.
			 */
			/*
			$remote = wp_remote_get(
				add_query_arg(
					array(
						'license_key' => $license_key,
						'locale'      => get_locale(),
					),
					self::$api_url . '/update-check'
				),
				array(
					'timeout' => $timeout,
				)
			);*/

			$remote = wp_remote_get(
				add_query_arg(
					array(
						'license_key'  => $license_key,
						'product_slug' => $product_slug,
						'locale'       => get_locale(),
					),
					self::$update_api_url . '/update'
				),
				array(
					'timeout' => $timeout,
				)
			);

			if (
				is_wp_error( $remote )
				|| 200 !== wp_remote_retrieve_response_code( $remote )
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {
				set_transient( self::get_cache_key( $product_slug ), 'error', MINUTE_IN_SECONDS * 10 );

				return false;
			}

			$payload = wp_remote_retrieve_body( $remote );

			set_transient( self::get_cache_key( $product_slug ), $payload, HOUR_IN_SECONDS * 12 );

			return json_decode( $payload );
		} // END get_updates()

		/**
		 * Override the WordPress request to return the correct plugin information.
		 *
		 * If a license key is not provided, return basic plugin information to prevent
		 * returning "Plugin not found." message.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/plugins_api/
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param false|object|array $result Requested results.
		 * @param string             $action The requested plugins_api().
		 * @param object             $args   Arguments passed to plugins_api().
		 *
		 * @return object|boolean Updated response.
		 */
		public function get_remote_plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return false;
			}

			if ( ! isset( $args->slug ) ) {
				return false;
			}

			$plugin_file = $this->get_plugin_file( $args->slug );

			// Bail early if not a CoCart plugin.
			if ( empty( $plugin_file ) || ! $this->is_cocart_plugin( $plugin_file ) ) {
				return $result;
			}

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

			// Connect to CoCart server.
			$remote = $this->get_updates( $args->slug );

			// If unable to get response from remote server then just return details of the plugin installed.
			if ( ! $remote || ! $remote->success || empty( $remote->update ) ) {
				$result->name                  = $plugin_data['Name'];
				$result->slug                  = $plugin_data['TextDomain'];
				$result->description           = $plugin_data['Description'];
				$result->author                = $plugin_data['Author'];
				$result->author_homepage       = $plugin_data['PluginURI'];
				$result->version               = $plugin_data['Version'];
				$result->requires              = $plugin_data['RequiresWP'];
				$result->requires_php          = $plugin_data['RequiresPHP'];
				$result->sections->description = $plugin_data['Description'];
			} else {
				// Remote server returned so let's override data for plugin information.
				$result                  = $remote->update;
				$result->name            = $plugin_data['Name'];
				$result->slug            = $plugin_data['TextDomain'];
				$result->description     = $plugin_data['Description'];
				$result->author          = $plugin_data['Author'];
				$result->author_homepage = $plugin_data['PluginURI'];
				$result->version         = $plugin_data['Version'];
				$result->sections        = (array) $result->sections;

				if ( version_compare( $plugin_data['Version'], $result->version, '<' ) ) {
					$result->new_version   = $result->version;
					$result->download_link = $result->download_link;
					$result->package       = $result->download_link;
				}
			}

			return $result;
		} // END get_remote_plugin_info()

		/**
		 * Override the WordPress request to check if an update is available.
		 *
		 * @see https://make.wordpress.org/core/2020/07/30/recommended-usage-of-the-updates-api-to-support-the-auto-updates-ui-for-plugins-and-themes-in-wordpress-5-5/
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param stdClass|mixed $data The current data for update_plugins.
		 *
		 * @return stdClass Extended data for update_plugins.
		 */
		public function check_for_updates( $data ) {
			if ( empty( $data ) ) {
				return $data;
			}

			$count_updates = 0;

			// Force-check (only once).
			$force_check = ! empty( $_GET['force-check'] ) ? true : false;

			foreach ( $this->get_installed_plugins() as $plugin_file => $installed_plugin ) {
				$plugin_slug = $this->get_slug_by_plugin_file( $plugin_file );

				$result = (object) array(
					'id'               => 'cocart-' . $plugin_slug,
					'name'             => $installed_plugin['Name'],
					'slug'             => $plugin_slug,
					'plugin'           => $plugin_file,
					'new_version'      => $installed_plugin['Version'],
					'url'              => '',
					'last_update'      => '',
					'homepage'         => 'https://cocartapi.com',
					'download_link'    => '',
					'package'          => '',
					'icons'            => array(
						'2x' => esc_url( COCART_URL_PATH . '/assets/images/updater/icon-256x256.png' ),
						'1x' => esc_url( COCART_URL_PATH . '/assets/images/updater/icon-128x128.png' ),
					),
					'banners'          => array(),
					'banners_rtl'      => array(),

					// Rely on core requirements.
					'tested'           => COCART_TESTED_WP,
					'requires'         => COCART_REQUIRED_WP,
					'requires_php'     => COCART_REQUIRED_PHP,
					'requires_woo'     => COCART_REQUIRED_WOO,
					'compatibility'    => new stdClass(),
					'update-supported' => true,
				);

				$updates_paused = get_transient( 'cocart_updates_paused' );

				if ( ! empty( $updates_paused ) ) {
					continue;
				}

				$remote = $this->get_updates( $plugin_slug, $force_check );

				if (
					$remote && $remote->success && ! empty( $remote->update )
					&& version_compare( $installed_plugin['Version'], $remote->update->version, '<' )
				) {
					$result->new_version   = $remote->update->version;
					$result->download_link = $remote->update->download_link;
					$result->package       = $remote->update->download_link;

					$data->response[ $result->plugin ] = $result;
					unset( $data->no_update[ $result->plugin ] );

					++$count_updates;
				} else {
					$data->no_update[ $result->plugin ] = $result;
					unset( $data->response[ $result->plugin ] );
				}

				++$this->checked;
			}

			set_transient( '_cocart_updates_count', $count_updates, 12 * HOUR_IN_SECONDS );

			if ( ! empty( $data ) ) {
				// Enable the two lines below once the translations payload API is created.
				// $translations       = $this->get_translations_update_data();
				// $data->translations = array_merge( isset( $data->translations ) ? $data->translations : array(), $translations );
			}

			return $data;
		} // END check_for_updates()

		/**
		 * Get translations updates information.
		 *
		 * Scans through all active plugins and obtain data for each product.
		 *
		 * ?Dev note: API to fetch translations does not exist in GlotPress yet. Need to create one, then this function can be used.
		 *
		 * @access public
		 *
		 * @return array Update data {product_id => data}
		 */
		public function get_translations_update_data() {
			$installed_translations = wp_get_installed_translations( 'plugins' );

			$locales = array_values( get_available_languages() );

			/**
			 * Filters the locales requested for plugin translations.
			 *
			 * @since 3.7.0
			 * @since 4.5.0 The default value of the `$locales` parameter changed to include all locales.
			 *
			 * @param array $locales Plugin locales. Default is all available locales of the site.
			 */
			$locales = apply_filters( 'plugins_update_check_locales', $locales ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$locales = array_unique( $locales );

			// No locales, the response will be empty, we can return now.
			if ( empty( $locales ) ) {
				return array();
			}

			// Get active plugins.
			$active_plugins = $this->get_active_plugins();

			// Nothing to check for so exit.
			if ( empty( $active_plugins ) ) {
				return array();
			}

			if ( wp_doing_cron() ) {
				$timeout = 30;
			} else {
				// Three seconds, plus one extra second for every 10 plugins.
				$timeout = 3 + (int) ( count( $active_plugins ) / 10 );
			}

			$request_body = array(
				'locales' => $locales,
				'plugins' => array(),
			);

			// For each active plugin fetch the current version installed.
			foreach ( $active_plugins as $plugin ) {
				$request_body['plugins'][ $plugin['slug'] ] = array( 'version' => $plugin['Version'] );
			}

			// Look up CoCart's GlotPress for each requested plugin for translations.
			$raw_response = wp_remote_post(
				$this->api_url . '/translations',
				array(
					'body'    => wp_json_encode( $request_body ),
					'headers' => array( 'Content-Type: application/json' ),
					'timeout' => $timeout,
				)
			);

			// Something wrong happened on the translate server side.
			$response_code = wp_remote_retrieve_response_code( $raw_response );
			if ( 200 !== $response_code ) {
				return array();
			}

			$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

			// API error, api returned but something was wrong.
			if ( array_key_exists( 'success', $response ) && false === $response['success'] ) {
				return array();
			}

			$translations = array();

			foreach ( $response['data'] as $plugin_name => $language_packs ) {
				foreach ( $language_packs as $language_pack ) {
					// Maybe we have this language pack already installed so lets check revision date.
					if ( array_key_exists( $plugin_name, $installed_translations ) && array_key_exists( $language_pack['wp_locale'], $installed_translations[ $plugin_name ] ) ) {
						$installed_translation_revision_time = new DateTime( $installed_translations[ $plugin_name ][ $language_pack['wp_locale'] ]['PO-Revision-Date'] );
						$new_translation_revision_time       = new DateTime( $language_pack['last_modified'] );

						// Skip if translation language pack is not newer than what is installed already.
						if ( $new_translation_revision_time <= $installed_translation_revision_time ) {
							continue;
						}
					}

					// New language pack to download.
					$translations[] = array(
						'type'       => 'plugin',
						'slug'       => $plugin_name,
						'language'   => $language_pack['wp_locale'],
						// 'version'    => $language_pack['version'],
						'updated'    => $language_pack['last_modified'],
						'package'    => $language_pack['package'],
						'autoupdate' => true,
					);
				}
			}

			return $translations;
		} // END get_translations_update_data()

		/**
		 * Get the number of products that have updates.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @return int The number of products with updates.
		 */
		public static function get_updates_count() {
			$count = get_transient( '_cocart_updates_count' );

			if ( false !== $count ) {
				return $count;
			}
		} // END get_updates_count()

		/**
		 * Return the updates count markup.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @return string Updates count markup, empty string if no updates available.
		 */
		public static function get_updates_count_html() {
			$count = self::get_updates_count();

			if ( $count > 0 ) {
				return sprintf( '<span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $count, number_format_i18n( $count ) );
			}

			return '';
		} // END get_updates_count_html()

		/**
		 * Excludes any CoCart plugins from WP.org updates.
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param array  $parsed_args An array of HTTP request arguments.
		 * @param string $url         The request URL.
		 *
		 * @return array $parsed_args An array of HTTP request arguments.
		 */
		public function exclude_plugins_from_update_check( $parsed_args, $url ) {
			if ( ( strpos( $url, 'https://api.wordpress.org/plugins/update-check/' ) !== false ) ) {
				$plugins = isset( $parsed_args['body']['plugins'] ) ? json_decode( $parsed_args['body']['plugins'], true ) : '';

				if ( is_array( $plugins ) ) {
					foreach ( array_keys( $this->get_installed_plugins() ) as $plugin_file ) {
						$plugin_slug = $this->get_slug_by_plugin_file( $plugin_file );

						unset( $plugins['plugins'][ $plugin_slug ] );
						unset( $plugins['active'][ array_search( $plugin_slug, $plugins['active'], true ) ] );
					}

					// Remove legacy core plugin.
					unset( $plugins['plugins']['cart-rest-api-for-woocommerce'] );
					unset( $plugins['active']['cart-rest-api-for-woocommerce'] );

					// Re-encode the plugins back into the request body.
					$plugins = wp_json_encode( $plugins );

					$parsed_args['body']['plugins'] = $plugins;
				}
			}

			return $parsed_args;
		} // END exclude_plugins_from_update_check()

		/**
		 * When the update is complete, purge the cache.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param WP_Upgrader $upgrader WP_Upgrader instance.
		 * @param array       $options  Array of bulk item update data.
		 *
		 * @return void
		 */
		public function purge( $upgrader, $options ) {
			if (
				$this->cache_allowed
				&& 'update' === $options['action']
				&& 'plugin' === $options['type']
				&& ! empty( $options['plugins'] )
			) {
				foreach ( $options['plugins'] as $plugin ) {
					if ( $plugin === $this->plugin_id ) {
						$this->refresh_plugins_transient();
					}
				}
			}
		} // END purge()

		/**
		 * Warn user to enter their license key to enable updates for the plugin.
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param array  $plugin_data An array of plugin metadata.
		 * @param object $response    An object of metadata about the available plugin update.
		 *
		 * @return void
		 */
		public function modify_plugin_update_message( $plugin_data, $response ) {
			// Bail early if we do have a license key.
			if ( self::get_license_key( $plugin_data ) ) {
				return;
			}

			// Display message.
			printf(
				wp_kses(
					/* translators: %s = A link to the updates page. */
					__( ' Please <a href="%s">enter your license key</a> to enable updates.', 'cart-rest-api-for-woocommerce' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				add_query_arg( array( 'page' => 'cocart-updates' ), admin_url( 'admin.php' ) )
			);
		} // END modify_plugin_update_message()

		/**
		 * Displays a message to the user under the listed plugin to enter their license key
		 * if not setup, purchase one or warn them that the license has expired.
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string $file        Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 *
		 * @return void
		 */
		public function license_information( $file, $plugin_data ) {
			$license_key = self::get_license_key( $plugin_data );

			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

			// If plugin is the legacy core plugin.
			if ( 'cart-rest-api-for-woocommerce' == $plugin_data['slug'] ) {
				echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_data['slug'] . '-update-info' ) . '" data-slug="' . $plugin_data['Name'] . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				echo '<p>';
				echo __( 'This legacy plugin will no longer receive updates as you have a newer version active. It is recommended to <strong>delete</strong> it.', 'cart-rest-api-for-woocommerce' );
				echo '</p></div></td></tr>';
				return;
			}

			// Warn users to provide license key to get updates.
			if ( empty( $license_key ) ) {
				echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_data['slug'] . '-update-info' ) . '" data-slug="' . $plugin_data['Name'] . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice notice-info notice-alt inline">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				echo '<p><strong><span class="dashicons dashicons-info"></span></strong> '
					. wp_kses_post(
						sprintf(
							/* translators: %1 = Plugin name, %2 = A link to the updates page. %3 link to the pricing page */
							__( 'To enable updates for %1$s, please <a href="%2$s">enter your license key</a>. If you don\'t have a license key, please consider <a href="%3$s" target="_blank">purchasing one</a> to keep up to date, secure and receive support.', 'cart-rest-api-for-woocommerce' ),
							$plugin_data['Name'],
							add_query_arg( array( 'page' => 'cocart-updates' ), admin_url( 'admin.php' ) ),
							CoCart_Helpers::build_shortlink( COCART_STORE_URL . 'pricing/' ),
							'CoCart upgrade',
							'updates'
						)
					);

				echo '</p></div></td></tr>';
			}

			// Bail early if license has not expired.
			if ( ! $this->has_license_expired( $plugin_data, $license_key ) ) {
				return;
			}

			echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_data['slug'] . '-update-warning' ) . '" data-slug="' . $plugin_data['Name'] . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice notice-warning notice-alt inline">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<p><strong><span class="dashicons dashicons-warning"></span></strong> '
				. sprintf(
					/* translators: %1$s is the plugin name, %2$s and %3$s are a link. */
					esc_html__( '%1$s can\'t be updated because your license is expired. %2$sRenew your license%3$s to get updates again and use all the features of %1$s.', 'cart-rest-api-for-woocommerce' ),
					esc_html( $plugin_data['Name'] ),
					'<a href="' . esc_url( 'https://cocartapi.com/billing' ) . '">',
					'</a>'
				);

			echo '</p></div></td></tr>';
		} // END license_information()

		/**
		 * Verify license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string  $license_key     License key to verify.
		 * @param boolean $verify_instance Verify the license key instance.
		 *
		 * @return object Returns the license information if valid.
		 */
		public static function verify_license( $license_key, $verify_instance = false ) {
			$verify_url = self::$api_url . '/validate/' . $license_key;

			if ( $verify_instance ) {
				$license_details = json_decode( get_option( 'cocart_license_verified' ) );
				$instance_id     = ! empty( $license_details->instance ) ? $license_details->instance->id : '';

				if ( ! empty( $instance_id ) ) {
					$verify_url = self::$api_url . '/validate/' . $license_key . '/' . $instance_id;
				}
			}

			$response = wp_remote_post(
				$verify_url,
				array(
					'sslverify' => false,
					'timeout'   => 10,
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) && 400 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				update_option( 'cocart_license_error', wp_remote_retrieve_body( $response ) );
				return;
			}

			$response = wp_remote_retrieve_body( $response );

			delete_option( 'cocart_license_error' );
			update_option( 'cocart_license_verified', $response ); // @todo update to use transient to be stored for 1 day.

			return $response;
		} // END verify_license()

		/**
		 * Activate license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string $license_key License key to activate.
		 */
		public static function activate_license( $license_key ) {
			$activation_url = self::$api_url . '/activate/' . $license_key . '/' . CoCart_Admin_Status::strip_protocol( CoCart_Admin_Status::get_site_url() );

			$response = wp_remote_post(
				$activation_url,
				array(
					'sslverify' => false,
					'timeout'   => 10,
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) && 400 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				update_option( 'cocart_license_error', wp_remote_retrieve_body( $response ) );
				return;
			}

			delete_option( 'cocart_license_error' );
			update_option( 'cocart_license_details', wp_remote_retrieve_body( $response ) );
		} // END activate_license()

		/**
		 * Deactivate license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string $license_key License key to deactivate.
		 * @param int    $instance_id Instance id assigned to the activation.
		 */
		public static function deactivate_license( $license_key, $instance_id ) {
			$deactivation_url = self::$api_url . '/deactivate/' . $license_key . '/' . $instance_id;

			$response = wp_remote_post(
				$deactivation_url,
				array(
					'sslverify' => false,
					'timeout'   => 10,
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				update_option( 'cocart_license_error', wp_remote_retrieve_body( $response ) );
				return;
			}

			delete_option( 'cocart_license_error' );
			delete_option( 'cocart_license_verified' );
			delete_option( 'cocart_license_details' );
			delete_option( 'cocart_instance_name' );
		} // END deactivate_license()

		/**
		 * Get the license key.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param string|array $plugin_data Plugin slug or plugin data.
		 *
		 * @return string $license_key Returns license key stored for specific plugin.
		 */
		public static function get_license_key( $plugin_data = null ) {
			$cocart_update_settings = maybe_unserialize( get_option( 'cocart_update_settings' ) );

			if ( ! empty( $plugin_data ) ) {
				if ( is_array( $plugin_data ) ) {
					$plugin_slug = $plugin_data['slug'];
				} else {
					$plugin_slug = $plugin_data;
				}

				if ( array_key_exists( $plugin_slug, $cocart_update_settings ) ) {
					$license_key = ! empty( $cocart_update_settings[ $plugin_slug ] ) ? $cocart_update_settings[ $plugin_slug ]['cocart_license_key'] : null;
				} else {
					$license_key = null;
				}
			} else {
				// Fallback for sites using previous versions of CoCart that had the legacy license system for just that plugin.
				$license_key = ! empty( $cocart_update_settings['cocart_license_key'] ) ? $cocart_update_settings['cocart_license_key'] : null;
			}

			return $license_key;
		} // END get_license_key()

		/**
		 * Returns the license type.
		 *
		 * @access protected
		 *
		 * @since 4.4.0 Introduced.
		 *
		 * @param boolean $raw True returns type raw, false returns translated.
		 *
		 * @return string License type.
		 */
		protected function get_license_type( $raw = true ) {
			$license_verified = json_decode( get_option( 'cocart_license_verified' ) );
			$license_details  = json_decode( get_option( 'cocart_license_details' ) );

			if ( empty( $this->cocart_update_settings['cocart_license_key'] ) ) {
				$license_status = ( ! $raw ) ? __( 'Unknown', 'cart-rest-api-for-woocommerce' ) : 'unknown';
			} elseif ( ! empty( $license_details ) ) {
				$meta = ! empty( $license_details->meta ) ? $license_details->meta : array();
			} elseif ( ! empty( $license_verified ) ) {
				$meta = ! empty( $license_verified->meta ) ? $license_verified->meta : array();
			}

			if ( empty( $meta ) ) {
				return '';
			}

			$product_name = esc_attr( $meta->product_name );

			return $license_status;
		} // END cocart_license_status()
	} // END class

	return new CoCart_Admin_Updates();
} // END if class exists
