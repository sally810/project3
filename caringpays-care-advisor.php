<?php
/**
 * Plugin Name:       CaringPays Care Advisor
 * Plugin URI:        https://caringpays.com/
 * Description:       Compliance-first care advisor chatbot for knowledge responses, screening, and guided routing.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            CaringPays
 * Author URI:        https://caringpays.com/
 * Text Domain:       caringpays-care-advisor
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package CaringPaysCareAdvisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CPAI_VERSION' ) ) {
	define( 'CPAI_VERSION', '0.1.0' );
}

if ( ! defined( 'CPAI_PLUGIN_FILE' ) ) {
	define( 'CPAI_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CPAI_PLUGIN_BASENAME' ) ) {
	define( 'CPAI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'CPAI_PLUGIN_PATH' ) ) {
	define( 'CPAI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CPAI_PLUGIN_URL' ) ) {
	define( 'CPAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CPAI_TEXT_DOMAIN' ) ) {
	define( 'CPAI_TEXT_DOMAIN', 'caringpays-care-advisor' );
}

require_once CPAI_PLUGIN_PATH . 'includes/autoload.php';

cpai_register_autoloader();

/**
 * Fired during plugin activation.
 *
 * @return void
 */
function cpai_activate(): void {
	$activator = new CPAI_Activator();
	$activator->activate();
}

/**
 * Fired during plugin deactivation.
 *
 * @return void
 */
function cpai_deactivate(): void {
	$deactivator = new CPAI_Deactivator();
	$deactivator->deactivate();
}

register_activation_hook( CPAI_PLUGIN_FILE, 'cpai_activate' );
register_deactivation_hook( CPAI_PLUGIN_FILE, 'cpai_deactivate' );

/**
 * Boots the plugin.
 *
 * @return void
 */
function cpai_bootstrap(): void {
	$plugin = new CPAI_Plugin();
	$plugin->run();
}

cpai_bootstrap();
