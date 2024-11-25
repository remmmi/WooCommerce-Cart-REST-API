<?php
/**
 * This file checks the integrity of the WordPress plugin once updated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! trait_exists( 'Plugin_Integrity_Check' ) ) {
	trait Plugin_Integrity_Check {
		protected string $plugin_slug;

		protected string $plugin_file;

		protected string $plugin_dir;

		protected string $plugin_integrity_notice;

		protected string $checksum_file_name = 'checksum.md5';

		public function initialize_integrity_check( string $plugin_slug, string $plugin_file ) {
			$this->plugin_slug = $plugin_slug;

			$this->plugin_file = $plugin_file;

			$this->plugin_dir = plugin_dir_path( $this->plugin_file );

			$this->plugin_integrity_notice = $this->plugin_slug . '_integrity_notice';

			// Check integrity once update is completed.
			add_action( 'upgrader_process_complete', array( $this, 'check_plugin_integrity_on_update' ), 10, 2 );

			// Display error notices if any.
			add_action( 'admin_notices', function () {
				if ( file_exists( $this->plugin_dir . $this->checksum_file_name ) ) {
					$errors = get_option( $this->plugin_integrity_notice, null );

					if ( ! empty( $errors ) ) {
						echo '<div class="notice notice-warning">';
						if ( is_array( $errors ) ) { ?>
							<p><strong><?php echo esc_html__( 'Warning:' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain ?></strong> <?php echo esc_html__( 'The following plugin files are either missing or do not match the expected checksum and may have been modified:' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain ?></p>
							<ul>
								<?php foreach ( $errors as $file ) : ?>
									<li><?php echo $file; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
								<?php endforeach; ?>
							</ul>
							<?php
						} else {
							echo '<p><strong>' . esc_html__( 'Warning:' ) . '</strong> ' . $errors . '</p>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						echo '</div>';
					}

					// Clear notice so the next check is fresh.
					delete_option( $this->plugin_integrity_notice );
				}
			} );

			// Hook into admin_init to perform the daily check for the checksum file.
			add_action( 'admin_init', array( $this, 'plugin_daily_checksum_check' ) );
		} // END __construct()

		/**
		 * Check for the checksum file and display an admin notice if missing.
		 */
		public function plugin_daily_checksum_check() {
			if ( ! file_exists( untrailingslashit( $this->plugin_dir ) . '/' . $this->checksum_file_name ) ) {
				// Display warning admin notice.
				add_action( 'admin_notices', array( $this, 'plugin_checksum_missing_warning' ) );
			} else {
				// Validate file integrity.
				self::validate_file_integrity();
			}
		} // END plugin_daily_checksum_check()

		/**
		 * Display an admin notice if the checksum file is missing.
		 */
		public function plugin_checksum_missing_warning() {
			$plugin_data = get_plugin_data( $this->plugin_file );
			$plugin_name = $plugin_data['Name'];
			?>
			<div class="notice notice-warning">
				<p><strong><?php echo esc_html__( 'Warning:' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain ?></strong> 
					<?php
					printf(
						/* translators: %s = Plugin name */
						esc_html__( 'The checksum file is missing for "%s". This may indicate the plugin has been altered. Please check your installation.' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
						esc_html( $plugin_name )
					);
					?>
				</p>
			</div>
			<?php
		} // END plugin_checksum_missing_warning()

		/**
		 * Check for the checksum file upon activation.
		 */
		public function plugin_activation_checksum_check() {
			// Check if the checksum file exists.
			if ( ! file_exists( untrailingslashit( $this->plugin_dir ) . '/' . $this->checksum_file_name ) ) {
				// Deactivate the plugin.
				deactivate_plugins( plugin_basename( $this->plugin_file ) );

				wp_die( esc_html__( 'Warning: For your security, the plugin did not activate because an important file is missing. This may indicate the plugin has been altered. Please contact support for help.' ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
			}
		} // END plugin_activation_checksum_check()

		/**
		 * Check integrity once plugin updated.
		 *
		 * @param object $upgrader   Instance of the Plugin_Upgrader class.
		 * @param array  $hook_extra An associative array that contains additional information about the upgrade process.
		 */
		public function check_plugin_integrity_on_update( $upgrader, $hook_extra ) {
			// Get the current plugin version and slug from the header.
			$plugin_data     = get_plugin_data( $this->plugin_file );
			$plugin_name     = $plugin_data['Name'];
			$current_version = $plugin_data['Version'];

			// Check if the current version is stable.
			if ( preg_match( '/^(dev|alpha|beta|rc|release candidate|rc\d*)/i', $current_version ) ) {
				return; // Exit if the version is not stable.
			}

			// Verify that the plugin slug matches the expected slug.
			if ( 'plugin' === $hook_extra['type'] && ! empty( $hook_extra['result'] ) && is_array( $hook_extra['result'] ) ) {
				$plugin_found = false;

				foreach ( $hook_extra['result'] as $plugin => $details ) {
					if ( strpos( $plugin ) !== false ) {
						$plugin_found = true;
						break;
					}
				}

				if ( ! $plugin_found ) {
					// If the plugin slug wasn't found, output an error message or handle it here.
					update_option( $this->plugin_integrity_notice,
						sprintf(
							/* translators: %s = Plugin name */
							__( 'There appears to be an issue matching the plugin installed for "%s".' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
							$plugin_name
						)
					);
					return;
				}
			}

			// Get the content of the checksum file.
			if ( ! file_exists( $this->plugin_dir . $this->checksum_file_name ) ) {
				update_option(
					$this->plugin_integrity_notice,
					sprintf(
						/* translators: %1$s = Plugin name, %2$s = Plugin name */
						__( 'The checksum file for "%1$s" is missing so we can\'t verify it\'s integrity. Please download "%2$s" from an official source.' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
						$plugin_name,
						$plugin_name
					)
				);
				return;
			}

			self::validate_file_integrity();
		} // END check_plugin_integrity_on_update()

		protected function validate_file_integrity() {
			// Only check once an hour.
			if ( get_transient( $this->plugin_slug . '_integrity_checked' ) ) {
				return;
			}

			// Set a transient for one hour.
			set_transient( $this->plugin_slug . '_integrity_checked', true, HOUR_IN_SECONDS );

			// Parse the .md5 content.
			$checksums = self::fetch_checksums();

			// Store errors detected.
			$errors = array();

			// Verify each file's checksum.
			foreach ( $checksums as $file_path => $expected_hash ) {
				$md5_file_path = $this->plugin_dir . $file_path;

				if ( strpos( $file_path, './' ) === 0 ) { // Check if './' is at the beginning
					$file_path = substr( $file_path, 2 ); // Remove the first two characters
				}

				$file_full_path = $this->plugin_dir . $file_path;

				// Skip if the file doesn't exist.
				if ( ! file_exists( $file_full_path ) ) {
					$errors[] = esc_html__( 'File missing:' ) . ' ' . '<u>' . untrailingslashit( $file_full_path ) . '</u>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( esc_html__( 'File missing:' ) . ' ' . untrailingslashit( $file_full_path ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
					}
					continue;
				}

				// Calculate the MD5 hash of the file.
				$file_hash = md5_file( $md5_file_path );

				// Compare hashes.
				if ( $file_hash !== $expected_hash ) {
					$errors[] = sprintf(
						/* translators: 1: File path, 2: Expected hash, 3: Found hash */
						__( 'File doesn\'t verify against checksum: %1$s. Expected: %2$s, got: %3$s.' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
						'<u>' . esc_html( untrailingslashit( $file_full_path ) ) . '</u>',
						'<strong style="color:green">' . esc_html( $expected_hash ) . '</strong>',
						'<strong style="color:red">' . esc_html( $file_hash ) . '</strong>'
					);

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log(
							sprintf(
								/* translators: 1 = File path, 2 = Expected hash, 3 = Found hash */
								esc_html__( 'File doesn\'t verify against checksum: %1$s. Expected: %2$s got %3$s.' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
								untrailingslashit( $file_full_path ),
								esc_html( $expected_hash ),
								esc_html( $file_hash )
							)
						);
					}
				}
			}

			if ( ! empty( $errors ) ) {
				update_option( $this->plugin_integrity_notice, $errors );
			}
		}

		protected function fetch_checksums() {
			$md5_content = file_get_contents( $this->plugin_dir . $this->checksum_file_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Parse the .md5 content.
			$checksums = array();

			foreach ( explode( "\n", $md5_content ) as $line ) {
				if ( preg_match( '/^([a-f0-9]{32})\s+\*(.+)$/', $line, $matches ) ) {
					$file_path = $matches[2];

					// Skip the checksum file itself.
					if ( $file_path === $this->checksum_file_name ) {
						continue;
					}

					$checksums[ $file_path ] = $matches[1];
				}
			}

			return $checksums;
		}
	} // END class
}