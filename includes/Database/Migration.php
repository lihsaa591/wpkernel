<?php
/**
 * Abstract base migration.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Database;

use WPKernel\Contracts\MigrationInterface;

/**
 * Convenience base class: gives subclasses the global $wpdb and the
 * table-prefix helper, and defaults down() to "not reversible" so an
 * author must opt in rather than silently ship a no-op rollback.
 */
abstract class Migration implements MigrationInterface {

	/**
	 * The global $wpdb instance, captured at construction time.
	 *
	 * @var \wpdb
	 */
	protected \wpdb $wpdb;

	/**
	 * Capture the global $wpdb for use by table() and delta().
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Full, prefixed table name for a table this plugin owns.
	 *
	 * @param string $name Table name without the WordPress prefix.
	 */
	protected function table( string $name ): string {
		return $this->wpdb->prefix . $name;
	}

	/**
	 * Run CREATE TABLE / ALTER TABLE SQL through dbDelta() so it is
	 * safe to re-run on every migration pass.
	 *
	 * @param string $sql One or more CREATE TABLE statements.
	 */
	protected function delta( string $sql ): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Default "not reversible" implementation. Override in a migration
	 * that can genuinely be undone.
	 *
	 * @throws \RuntimeException Always, unless overridden.
	 */
	public function down(): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- developer-facing exception message (logs/CLI), not rendered as markup.
		throw new \RuntimeException(
			sprintf( '%s does not implement down() — this migration was authored as one-way.', static::class )
		);
	}
}
