<?php
/**
 * Plugin activation.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout;

use WPSprout\Database\Migrator;

/**
 * Deliberately self-contained: activation hooks run outside the normal
 * plugins_loaded lifecycle, so this does NOT depend on Plugin::run()
 * having booted the container — it builds what it needs directly.
 */
final class Activation {

	/**
	 * Run pending migrations and flush rewrite rules on activation.
	 */
	public static function activate(): void {
		$migrator = new Migrator( WPSPROUT_PATH . 'database/migrations', 'wpsprout_applied_migrations' );
		$migrator->migrate();

		update_option( 'wpsprout_db_version', WPSPROUT_VERSION );

		flush_rewrite_rules();
	}
}
