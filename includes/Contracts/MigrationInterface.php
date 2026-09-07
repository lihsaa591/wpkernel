<?php
/**
 * Migration contract.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Contracts;

/**
 * A single, reversible database change.
 *
 * Concrete migrations live in `database/migrations/` and are named
 * `YYYY_MM_DD_HHMMSS_description.php`, mirroring Laravel's convention.
 * The timestamp prefix is the migration's sort key and identity — never
 * rename a migration file after it has shipped.
 */
interface MigrationInterface {

	/**
	 * Apply the migration.
	 *
	 * Must be idempotent: safe to run again against a database that
	 * already has this migration applied (e.g. `dbDelta()` already is).
	 */
	public function up(): void;

	/**
	 * Reverse the migration.
	 *
	 * Implement this whenever the change is safely reversible. When it
	 * genuinely is not (e.g. a destructive data migration), throw a
	 * \RuntimeException with an explanation instead of silently no-op-ing.
	 */
	public function down(): void;
}
