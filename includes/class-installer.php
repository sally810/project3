<?php
/**
 * Handles plugin install lifecycle events.
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPAI_Installer {

	/**
	 * Database schema version.
	 */
	private const DB_VERSION = '1.1.0';

	/**
	 * Option key used to store the installed DB version.
	 */
	private const DB_VERSION_OPTION = 'cpai_db_version';

	/**
	 * Database adapter.
	 *
	 * @var wpdb
	 */
	private wpdb $wpdb;

	/**
	 * Constructor.
	 *
	 * @param wpdb|null $db Database adapter.
	 */
	public function __construct( ?wpdb $db = null ) {
		global $wpdb;

		$this->wpdb = $db instanceof wpdb ? $db : $wpdb;
	}

	/**
	 * Run activation routines.
	 *
	 * @return void
	 */
	public function activate(): void {
		if ( $this->is_schema_current() ) {
			return;
		}

		$this->create_tables();
	}

	/**
	 * Run deactivation routines.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		// Intentionally does not remove user data.
	}

	/**
	 * Create or update plugin database tables.
	 *
	 * @return void
	 */
	public function create_tables(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $this->get_table_schemas() as $schema ) {
			dbDelta( $schema );
		}

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	/**
	 * Check if the stored schema version matches current schema version.
	 *
	 * @return bool
	 */
	private function is_schema_current(): bool {
		$stored_version = $this->get_stored_db_version();

		if ( '' === $stored_version ) {
			return false;
		}

		return version_compare( $stored_version, self::DB_VERSION, '>=' );
	}

	/**
	 * Retrieve stored DB version from the options table.
	 *
	 * @return string
	 */
	private function get_stored_db_version(): string {
		$sql = $this->wpdb->prepare(
			"SELECT option_value FROM {$this->wpdb->options} WHERE option_name = %s LIMIT 1",
			self::DB_VERSION_OPTION
		);

		$version = $this->wpdb->get_var( $sql );

		return is_string( $version ) ? $version : '';
	}

	/**
	 * Returns SQL schema statements for plugin tables.
	 *
	 * @return array<int, string>
	 */
	private function get_table_schemas(): array {
		$charset_collate = $this->wpdb->get_charset_collate();
		$prefix          = $this->wpdb->prefix;

		return array(
			"CREATE TABLE {$prefix}cp_sessions (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				session_id varchar(100) NOT NULL,
				current_state varchar(64) NOT NULL DEFAULT 'WELCOME',
				session_start datetime NOT NULL,
				session_end datetime NULL,
				last_activity datetime NOT NULL,
				resume_token varchar(190) NULL,
				soft_breach_count int(10) unsigned NOT NULL DEFAULT 0,
				hard_breach_count int(10) unsigned NOT NULL DEFAULT 0,
				escalation_level varchar(30) NOT NULL DEFAULT 'none',
				escalation_reason varchar(255) NULL,
				escalated_at datetime NULL,
				is_locked tinyint(1) NOT NULL DEFAULT 0,
				lock_reason varchar(255) NULL,
				locked_at datetime NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY session_id (session_id),
				KEY current_state (current_state),
				KEY last_activity (last_activity)
			) {$charset_collate};",
			"CREATE TABLE {$prefix}cpai_conversations (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				session_id varchar(100) NOT NULL,
				user_id bigint(20) unsigned NULL,
				state varchar(64) NOT NULL DEFAULT 'WELCOME',
				channel varchar(30) NOT NULL DEFAULT 'web',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY session_id (session_id),
				KEY user_id (user_id),
				KEY state (state)
			) {$charset_collate};",
			"CREATE TABLE {$prefix}cpai_messages (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				conversation_id bigint(20) unsigned NOT NULL,
				role varchar(20) NOT NULL,
				message longtext NOT NULL,
				metadata longtext NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY conversation_id (conversation_id),
				KEY role (role)
			) {$charset_collate};",
			"CREATE TABLE {$prefix}cpai_screenings (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				conversation_id bigint(20) unsigned NOT NULL,
				status varchar(64) NOT NULL DEFAULT 'SCREENING_IN_PROGRESS',
				payload longtext NULL,
				result longtext NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY conversation_id (conversation_id),
				KEY status (status)
			) {$charset_collate};",
			"CREATE TABLE {$prefix}cpai_leads (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				conversation_id bigint(20) unsigned NOT NULL,
				full_name varchar(190) NULL,
				email varchar(190) NULL,
				phone varchar(40) NULL,
				consent_given tinyint(1) NOT NULL DEFAULT 0,
				consent_timestamp datetime NULL,
				crm_status varchar(50) NOT NULL DEFAULT 'pending',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY conversation_id (conversation_id),
				KEY crm_status (crm_status)
			) {$charset_collate};",
			"CREATE TABLE {$prefix}cpai_audit_log (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				event_type varchar(64) NOT NULL,
				event_source varchar(40) NOT NULL,
				entity_id varchar(100) NULL,
				context longtext NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY event_type (event_type),
				KEY event_source (event_source),
				KEY created_at (created_at)
			) {$charset_collate};",
		);
	}
}
