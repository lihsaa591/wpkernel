<?php
/**
 * Example migration.
 *
 * Demonstrates the Migration base class end to end. Delete this file
 * (and includes/Examples/) once you no longer need the worked example.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

use WPKernel\Database\Migration;

return new class() extends Migration {

	/**
	 * Create the example items table.
	 */
	public function up(): void {
		$table           = $this->table( 'wpkernel_example_items' );
		$charset_collate = $this->wpdb->get_charset_collate();

		$this->delta(
			"CREATE TABLE {$table} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				title VARCHAR(191) NOT NULL,
				created_at DATETIME NOT NULL,
				PRIMARY KEY  (id)
			) {$charset_collate};"
		);
	}

	/**
	 * Drop the example items table.
	 */
	public function down(): void {
		$table = $this->table( 'wpkernel_example_items' );
		$this->wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is derived from $wpdb->prefix, not user input.
	}
};
