<?php
/**
 * Handles plugin deactivation routines.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPAI_Deactivator {

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
	 * Run deactivation lifecycle.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		$this->unschedule_jobs();
		$this->installer->deactivate();
		flush_rewrite_rules();
	}

	/**
	 * Unschedule plugin-maintained cron hooks.
	 *
	 * @return void
	 */
	private function unschedule_jobs(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}
}
