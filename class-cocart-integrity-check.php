<?php
/**
 * This file checks the integrity of the WordPress plugin once updated.
 */
final class CoCart_Integrity_Check extends Plugin_Integrity_Check {

	public function __construct( string $plugin_slug, string $plugin_file ) {
		parent::__construct( $plugin_slug, $plugin_file );
	} // END __construct()

	public function cocart_activation_checksum_check() {
		parent::plugin_activation_checksum_check();
	}
} // END class
