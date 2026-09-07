<?php
/**
 * Plugin Name:       WPSprout
 * Plugin URI:        https://github.com/wpsprout/wpsprout
 * Description:       A PHP DI + REST + React foundation for building modern WordPress plugins. Replace this description when you scaffold your own plugin.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            WPSprout Contributors
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpsprout
 * Domain Path:       /languages
 *
 * @package WPSprout
 */

declare( strict_types=1 );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSPROUT_VERSION', '0.1.0' );
define( 'WPSPROUT_FILE', __FILE__ );
define( 'WPSPROUT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSPROUT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSPROUT_BASENAME', plugin_basename( __FILE__ ) );

// Composer autoloader.
$wpsprout_autoloader = WPSPROUT_PATH . 'vendor/autoload.php';

if ( ! file_exists( $wpsprout_autoloader ) ) {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'WPSprout: run "composer install" before activating this plugin.', 'wpsprout' )
			);
		}
	);
	return;
}

require_once $wpsprout_autoloader;

/**
 * Boot the plugin.
 *
 * Deferred to plugins_loaded so every other plugin's autoloader
 * and text-domain setup has already run.
 */
add_action(
	'plugins_loaded',
	static function () {
		require_once WPSPROUT_PATH . 'bootstrap/app.php';
	},
	9 // Before default priority 10, so dependents can hook in at 10+.
);

register_activation_hook( __FILE__, array( \WPSprout\Activation::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \WPSprout\Deactivation::class, 'deactivate' ) );
