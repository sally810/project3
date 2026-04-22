<?php
/**
 * Main plugin runtime orchestrator.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPAI_Plugin {

	/**
	 * Cron hook name for plugin-maintained background jobs.
	 */
	private const CRON_HOOK = 'cpai_cron_minute';

	/**
	 * Boot runtime hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'boot_public_components' ) );
		add_action( 'init', array( $this, 'boot_api_components' ) );
		add_action( 'init', array( $this, 'boot_admin_components' ) );
		add_action( 'init', array( $this, 'register_scheduled_jobs' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( self::CRON_HOOK, array( $this, 'run_scheduled_jobs' ) );
		add_filter( 'cron_schedules', array( $this, 'register_cron_schedule' ) );
	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( CPAI_TEXT_DOMAIN, false, dirname( CPAI_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Boot web-facing runtime components.
	 *
	 * @return void
	 */
	public function boot_public_components(): void {
		if ( class_exists( 'CPAI_Public' ) ) {
			$public = new CPAI_Public();
			if ( method_exists( $public, 'register' ) ) {
				$public->register();
			}
		}
	}

	/**
	 * Boot REST/API runtime components.
	 *
	 * @return void
	 */
	public function boot_api_components(): void {
		if ( class_exists( 'CPAI_Api' ) ) {
			$api = new CPAI_Api();
			if ( method_exists( $api, 'register' ) ) {
				$api->register();
			}
		}
	}

	/**
	 * Boot admin runtime components.
	 *
	 * @return void
	 */
	public function boot_admin_components(): void {
		if ( ! is_admin() ) {
			return;
		}

		if ( class_exists( 'CPAI_Admin' ) ) {
			$admin = new CPAI_Admin();
			if ( method_exists( $admin, 'register' ) ) {
				$admin->register();
			}
		}
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function enqueue_public_assets(): void {
		wp_register_style(
			'cpai-public',
			CPAI_PLUGIN_URL . 'public/assets/css/public.css',
			array(),
			CPAI_VERSION
		);

		wp_register_script(
			'cpai-public',
			CPAI_PLUGIN_URL . 'public/assets/js/public.js',
			array(),
			CPAI_VERSION,
			true
		);
	}

	/**
	 * Register admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		if ( ! is_admin() ) {
			return;
		}

		wp_register_style(
			'cpai-admin',
			CPAI_PLUGIN_URL . 'admin/assets/css/admin.css',
			array(),
			CPAI_VERSION
		);

		wp_register_script(
			'cpai-admin',
			CPAI_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			CPAI_VERSION,
			true
		);
	}

	/**
	 * Adds a one-minute schedule for plugin jobs.
	 *
	 * @param array<string, array<string, int|string>> $schedules Existing schedules.
	 * @return array<string, array<string, int|string>>
	 */
	public function register_cron_schedule( array $schedules ): array {
		if ( ! isset( $schedules['cpai_minute'] ) ) {
			$schedules['cpai_minute'] = array(
				'interval' => MINUTE_IN_SECONDS,
				'display'  => __( 'Every Minute (CaringPays)', CPAI_TEXT_DOMAIN ),
			);
		}

		return $schedules;
	}

	/**
	 * Ensure scheduled jobs are registered.
	 *
	 * @return void
	 */
	public function register_scheduled_jobs(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'cpai_minute', self::CRON_HOOK );
		}
	}

	/**
	 * Execute scheduled runtime jobs.
	 *
	 * @return void
	 */
	public function run_scheduled_jobs(): void {
		do_action( 'cpai_run_scheduled_jobs' );
	}
}
