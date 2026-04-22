<?php
/**
 * Plugin autoloader.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPAI_Autoloader {

	/**
	 * Registers the autoloader callback.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload callback for CPAI_ prefixed classes.
	 *
	 * @param string $class_name Class being loaded.
	 * @return void
	 */
	private static function autoload( string $class_name ): void {
		if ( 0 !== strpos( $class_name, 'CPAI_' ) ) {
			return;
		}

		$relative = strtolower( str_replace( '_', '-', substr( $class_name, 5 ) ) );
		$file     = CPAI_PLUGIN_PATH . 'includes/class-' . $relative . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
