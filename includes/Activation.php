<?php
/**
 * Plugin activation.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel;

use WPKernel\Database\Migrator;

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
		$migrator = new Migrator( WPKERNEL_PATH . 'database/migrations', 'wpkernel_applied_migrations' );
		$migrator->migrate();

		update_option( 'wpkernel_db_version', WPKERNEL_VERSION );

		flush_rewrite_rules();
	}
}
