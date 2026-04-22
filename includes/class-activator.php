<?php
/**
 * Handles plugin activation routines.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPAI_Activator {

	/**
	 * Cron hook for runtime scheduled jobs.
	 */
	private const CRON_HOOK = 'cpai_cron_minute';

	/**
	 * Installer dependency.
	 *
	 * @var CPAI_Installer
	 */
	private CPAI_Installer $installer;

	/**
	 * Constructor.
	 *
	 * @param CPAI_Installer|null $installer Installer dependency.
	 */
	public function __construct( ?CPAI_Installer $installer = null ) {
		$this->installer = $installer ?? new CPAI_Installer();
	}

	/**
	 * Run activation lifecycle.
	 *
	 * @return void
	 */
	public function activate(): void {
		$this->installer->activate();
		$this->installer->create_tables();
		$this->schedule_jobs();
		flush_rewrite_rules();
	}

	/**
	 * Register core cron jobs.
	 *
	 * @return void
	 */
	private function schedule_jobs(): void {
		if ( wp_next_scheduled( self::CRON_HOOK ) ) {
			return;
		}

		$schedules    = wp_get_schedules();
		$schedule_key = isset( $schedules['cpai_minute'] ) ? 'cpai_minute' : 'hourly';

		wp_schedule_event( time(), $schedule_key, self::CRON_HOOK );
	}
}
