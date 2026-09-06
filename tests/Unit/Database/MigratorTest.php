<?php
/**
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Tests\Unit\Database;

use Brain\Monkey\Functions;
use WPKernel\Database\Migrator;
use WPKernel\Tests\TestCase;

final class MigratorTest extends TestCase {

	private string $fixtures_dir;

	/**
	 * In-memory stand-in for the single wp_options row Migrator reads/writes.
	 *
	 * @var array<string, mixed>
	 */
	private array $options_store = array();

	protected function setUp(): void {
		parent::setUp();

		$this->fixtures_dir = sys_get_temp_dir() . '/wpkernel-migrator-test-' . uniqid();
		mkdir( $this->fixtures_dir, 0777, true );

		$this->options_store = array();

		Functions\when( 'get_option' )->alias(
			fn ( string $key, $default = false ) => $this->options_store[ $key ] ?? $default
		);
		Functions\when( 'update_option' )->alias(
			function ( string $key, $value ) {
				$this->options_store[ $key ] = $value;
				return true;
			}
		);
	}

	protected function tearDown(): void {
		$this->remove_directory( $this->fixtures_dir );
		parent::tearDown();
	}

	public function test_migrate_runs_pending_migrations_in_filename_order(): void {
		$order = array();

		$this->write_migration( '2024_01_01_000000_first', $order );
		$this->write_migration( '2024_01_02_000000_second', $order );

		$migrator = new Migrator( $this->fixtures_dir, 'wpkernel_test_migrations' );
		$ran      = $migrator->migrate();

		self::assertSame( array( '2024_01_01_000000_first', '2024_01_02_000000_second' ), $ran );
		self::assertSame( array( 'first-up', 'second-up' ), $order );
	}

	public function test_migrate_skips_already_applied_migrations(): void {
		$order = array();

		$this->write_migration( '2024_01_01_000000_first', $order );

		$migrator = new Migrator( $this->fixtures_dir, 'wpkernel_test_migrations' );
		$migrator->migrate();

		$this->write_migration( '2024_01_02_000000_second', $order );

		$ran = $migrator->migrate();

		self::assertSame( array( '2024_01_02_000000_second' ), $ran, 'Only the new migration should run on the second pass.' );
		self::assertSame( array( 'first-up', 'second-up' ), $order, 'The first migration must not run again.' );
	}

	public function test_rollback_reverses_the_latest_batch_in_reverse_order(): void {
		$order = array();

		$this->write_migration( '2024_01_01_000000_first', $order );
		$this->write_migration( '2024_01_02_000000_second', $order );

		$migrator = new Migrator( $this->fixtures_dir, 'wpkernel_test_migrations' );
		$migrator->migrate();

		$order = array(); // Reset so we only observe the rollback calls.

		$rolled_back = $migrator->rollback();

		self::assertSame( array( '2024_01_02_000000_second', '2024_01_01_000000_first' ), $rolled_back );
		self::assertSame( array( 'second-down', 'first-down' ), $order );

		// A second migrate() run should re-apply both, proving rollback truly cleared their record.
		$order = array();
		$ran   = $migrator->migrate();

		self::assertSame( array( '2024_01_01_000000_first', '2024_01_02_000000_second' ), $ran );
	}

	public function test_rollback_only_reverses_the_most_recent_batch(): void {
		$order = array();

		$this->write_migration( '2024_01_01_000000_first', $order );

		$migrator = new Migrator( $this->fixtures_dir, 'wpkernel_test_migrations' );
		$migrator->migrate(); // Batch 1.

		$this->write_migration( '2024_01_02_000000_second', $order );
		$migrator->migrate(); // Batch 2.

		$order = array();
		$rolled_back = $migrator->rollback();

		self::assertSame( array( '2024_01_02_000000_second' ), $rolled_back, 'Only batch 2 should be reversed.' );
		self::assertSame( array( 'second-down' ), $order );
	}

	/**
	 * Writes a fixture migration file that appends "{name}-up"/"{name}-down"
	 * to the given $order array (by reference, captured via `use (&$order)`)
	 * so tests can assert both which migrations ran AND in what order.
	 */
	private function write_migration( string $key, array &$order ): void {
		$name = substr( $key, strrpos( $key, '_' ) + 1 );
		$path = $this->fixtures_dir . "/{$key}.php";

		file_put_contents(
			$path,
			'<?php
			return new class( "' . $name . '" ) implements \WPKernel\Contracts\MigrationInterface {
				public function __construct( private string $name ) {}
				public function up(): void {
					\WPKernel\Tests\Unit\Database\MigratorTest::$order[] = $this->name . "-up";
				}
				public function down(): void {
					\WPKernel\Tests\Unit\Database\MigratorTest::$order[] = $this->name . "-down";
				}
			};'
		);

		// PHP closures/`use (&$order)` can't cross the `require` boundary
		// cleanly for an anonymous class defined in another file, so the
		// fixture writes to a static property instead; mirror it back
		// into the local $order the test asserts against.
		self::$order = &$order;
	}

	/**
	 * @var array<int, string>
	 */
	public static array $order = array();

	private function remove_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		foreach ( glob( "{$dir}/*" ) ?: array() as $file ) {
			unlink( $file );
		}

		rmdir( $dir );
	}
}
