<?php
/**
 * Database / migrations service provider.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Providers;

use League\Container\Container;
use WPKernel\Database\Migrator;

/**
 * Binds the Migrator and, as a safety net for the "updated the plugin
 * files without deactivating first" case, re-runs pending migrations on
 * admin_init whenever the stored schema version doesn't match the
 * running plugin version. Activation (see Activation.php) is still the
 * primary path — this is a catch-up mechanism, not the main trigger.
 */
final class DatabaseServiceProvider extends AbstractServiceProvider {

	/**
	 * Bind a shared Migrator instance.
	 *
	 * @param Container $container The DI container.
	 */
	public function register( Container $container ): void {
		$container->add(
			Migrator::class,
			static fn () => new Migrator(
				WPKERNEL_PATH . 'database/migrations',
				'wpkernel_applied_migrations'
			)
		)->setShared( true );
	}

	/**
	 * Catch up any pending migrations on admin_init if the stored schema
	 * version doesn't match the running plugin version.
	 *
	 * @param Container $container The DI container.
	 */
	public function boot( Container $container ): void {
		add_action(
			'admin_init',
			static function () use ( $container ) {
				$stored_version = get_option( 'wpkernel_db_version' );

				if ( WPKERNEL_VERSION === $stored_version ) {
					return;
				}

				/**
				 * The shared Migrator instance.
				 *
				 * @var Migrator $migrator
				 */
				$migrator = $container->get( Migrator::class );
				$migrator->migrate();

				update_option( 'wpkernel_db_version', WPKERNEL_VERSION );
			}
		);
	}
}
