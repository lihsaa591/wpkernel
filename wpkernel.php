<?php
/**
 * Plugin Name:       WPKernel
 * Plugin URI:        https://github.com/wpkernel/wpkernel
 * Description:       A PHP DI + REST + React foundation for building modern WordPress plugins. Replace this description when you scaffold your own plugin.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            WPKernel Contributors
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpkernel
 * Domain Path:       /languages
 *
 * @package WPKernel
 */

declare( strict_types=1 );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPKERNEL_VERSION', '0.1.0' );
define( 'WPKERNEL_FILE', __FILE__ );
define( 'WPKERNEL_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPKERNEL_URL', plugin_dir_url( __FILE__ ) );
define( 'WPKERNEL_BASENAME', plugin_basename( __FILE__ ) );

// Composer autoloader.
$wpkernel_autoloader = WPKERNEL_PATH . 'vendor/autoload.php';

if ( ! file_exists( $wpkernel_autoloader ) ) {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'WPKernel: run "composer install" before activating this plugin.', 'wpkernel' )
			);
		}
	);
	return;
}

require_once $wpkernel_autoloader;

/**
 * Boot the plugin.
 *
 * Deferred to plugins_loaded so every other plugin's autoloader
 * and text-domain setup has already run.
 */
add_action(
	'plugins_loaded',
	static function () {
		require_once WPKERNEL_PATH . 'bootstrap/app.php';
	},
	9 // Before default priority 10, so dependents can hook in at 10+.
);

register_activation_hook( __FILE__, array( \WPKernel\Activation::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \WPKernel\Deactivation::class, 'deactivate' ) );
