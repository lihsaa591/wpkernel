<?php
/**
 * Migration runner.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Database;

use WPKernel\Contracts\MigrationInterface;

/**
 * Discovers migration files, runs the ones not yet applied, and can
 * roll the most recent batch back. Applied migrations are tracked as a
 * JSON list on a single wp_options row — no schema is needed to track
 * schema changes.
 *
 * Migration files live in `$migrations_path` and are named
 * `YYYY_MM_DD_HHMMSS_description.php`. Each file must `return` either
 * an instance implementing MigrationInterface, or an anonymous class:
 *
 *     <?php
 *     use WPKernel\Database\Migration;
 *
 *     return new class() extends Migration {
 *         public function up(): void {
 *             $this->delta( "CREATE TABLE {$this->table( 'example_items' )} ( ... );" );
 *         }
 *         public function down(): void {
 *             $this->wpdb->query( "DROP TABLE IF EXISTS {$this->table( 'example_items' )}" );
 *         }
 *     };
 */
final class Migrator {

	/**
	 * Absolute path to the directory containing migration files.
	 *
	 * @var string
	 */
	private string $migrations_path;

	/**
	 * The wp_options key this migrator's applied-migration record is stored under.
	 *
	 * @var string
	 */
	private string $option_key;

	/**
	 * Construct a migrator scoped to one migrations directory and one options key.
	 *
	 * @param string $migrations_path Absolute path to the migrations directory.
	 * @param string $option_key      wp_options key to track applied migrations under.
	 */
	public function __construct( string $migrations_path, string $option_key ) {
		$this->migrations_path = rtrim( $migrations_path, '/' );
		$this->option_key      = $option_key;
	}

	/**
	 * Run every migration that has not been applied yet, in filename order.
	 *
	 * @return string[] Keys of the migrations that were applied this run.
	 *
	 * @throws \RuntimeException When a migration fails; migrations already
	 *                            applied earlier in this run are NOT rolled
	 *                            back automatically — fix the failing
	 *                            migration and re-run.
	 */
	public function migrate(): array {
		$applied = $this->applied_keys();
		$batch   = $this->next_batch_number();
		$ran     = array();

		foreach ( $this->discover() as $key => $file ) {
			if ( isset( $applied[ $key ] ) ) {
				continue;
			}

			$migration = $this->load( $file );

			try {
				$migration->up();
			} catch ( \Throwable $e ) {
				throw new \RuntimeException(
					sprintf( 'Migration "%s" failed: %s', $key, $e->getMessage() ), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- developer-facing exception message (logs/CLI), not rendered as markup.
					0,
					$e // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- passed as the exception's $previous, not output.
				);
			}

			$applied[ $key ] = array(
				'batch'  => $batch,
				'ran_at' => gmdate( 'Y-m-d H:i:s' ),
			);
			$ran[]           = $key;

			// Persist after every migration, not just at the end — if
			// migration N+1 fails, migrations 1..N must not be re-run.
			update_option( $this->option_key, $applied, false );
		}

		return $ran;
	}

	/**
	 * Reverse the most recently applied batch.
	 *
	 * @return string[] Keys of the migrations that were rolled back.
	 */
	public function rollback(): array {
		$applied = $this->applied_keys();

		if ( array() === $applied ) {
			return array();
		}

		$latest_batch = max( array_column( $applied, 'batch' ) );
		$to_rollback  = array_filter(
			$applied,
			static fn( array $meta ) => $meta['batch'] === $latest_batch
		);

		// Reverse chronological order within the batch.
		$keys        = array_reverse( array_keys( $to_rollback ) );
		$files       = $this->discover();
		$rolled_back = array();

		foreach ( $keys as $key ) {
			if ( ! isset( $files[ $key ] ) ) {
				// Migration file has since been deleted from disk — drop
				// its record but there is nothing left to reverse.
				unset( $applied[ $key ] );
				continue;
			}

			$this->load( $files[ $key ] )->down();
			unset( $applied[ $key ] );
			$rolled_back[] = $key;
		}

		update_option( $this->option_key, $applied, false );

		return $rolled_back;
	}

	/**
	 * Applied-migration records, keyed by migration key.
	 *
	 * @return array<string, array{batch:int, ran_at:string}>
	 */
	private function applied_keys(): array {
		$stored = get_option( $this->option_key, array() );

		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * The batch number the next migrate() run should record its migrations under.
	 */
	private function next_batch_number(): int {
		$applied = $this->applied_keys();

		if ( array() === $applied ) {
			return 1;
		}

		return 1 + max( array_column( $applied, 'batch' ) );
	}

	/**
	 * Every migration file on disk, sorted ascending by filename.
	 *
	 * @return array<string, string> Migration key (filename stem) => absolute file path.
	 */
	private function discover(): array {
		if ( ! is_dir( $this->migrations_path ) ) {
			return array();
		}

		$found = glob( $this->migrations_path . '/*.php' );
		$files = false === $found ? array() : $found;
		sort( $files, SORT_STRING );

		$map = array();
		foreach ( $files as $file ) {
			$key         = basename( $file, '.php' );
			$map[ $key ] = $file;
		}

		return $map;
	}

	/**
	 * Require a migration file and validate its return value.
	 *
	 * @param string $file Absolute path to the migration file.
	 *
	 * @throws \RuntimeException When the file does not return a MigrationInterface.
	 */
	private function load( string $file ): MigrationInterface {
		$migration = require $file;

		if ( ! $migration instanceof MigrationInterface ) {
			throw new \RuntimeException(
				sprintf( 'Migration file "%s" must return a MigrationInterface instance.', $file ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- developer-facing exception message (logs/CLI), not rendered as markup.
			);
		}

		return $migration;
	}
}
