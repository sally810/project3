<?php
/**
 * Lightweight plugin autoloader.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cpai_register_autoloader' ) ) {
	/**
	 * Register the CaringPays plugin autoloader.
	 *
	 * @param array<int, string> $directories Optional relative directories to scan.
	 * @return void
	 */
	function cpai_register_autoloader( array $directories = array() ): void {
		$default_directories = array(
			'includes',
			'helpers',
			'domain',
			'data',
			'infra',
			'api',
			'admin',
			'public',
			'cron',
		);

		$directories = ! empty( $directories ) ? $directories : $default_directories;
		$directories = array_values(
			array_filter(
				array_map( 'sanitize_key', $directories )
			)
		);

		spl_autoload_register(
			static function ( string $class_name ) use ( $directories ): void {
				$prefix = 'CPAI_';

				if ( 0 !== strpos( $class_name, $prefix ) ) {
					return;
				}

				$class_suffix = substr( $class_name, strlen( $prefix ) );

				if ( ! is_string( $class_suffix ) || '' === $class_suffix || ! preg_match( '/^[A-Za-z0-9_]+$/', $class_suffix ) ) {
					return;
				}

				$tokens = array_map( 'strtolower', explode( '_', $class_suffix ) );

				if ( empty( $tokens ) ) {
					return;
				}

				$candidate_files = array();
				$file_slug       = 'class-' . implode( '-', $tokens ) . '.php';

				foreach ( $directories as $directory ) {
					$candidate_files[] = trailingslashit( CPAI_PLUGIN_PATH . $directory ) . $file_slug;
				}

				if ( count( $tokens ) > 1 && in_array( $tokens[0], $directories, true ) ) {
					$candidate_files[] = trailingslashit( CPAI_PLUGIN_PATH . $tokens[0] ) . 'class-' . implode( '-', array_slice( $tokens, 1 ) ) . '.php';
				}

				foreach ( $candidate_files as $file_path ) {
					if ( is_readable( $file_path ) ) {
						require_once $file_path;
						return;
					}
				}
			}
		);
	}
}
