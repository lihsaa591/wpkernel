<?php
/**
 * Uninstall routine.
 *
 * WordPress only includes this file when the plugin is deleted from the
 * Plugins screen (or via WP-CLI `wp plugin delete`), never on ordinary
 * deactivation — so it is the correct place for genuinely destructive
 * cleanup, and the ONLY place it should live.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

// If this file is not called by WordPress, bail.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Destructive by default is a bad default for a boilerplate: a real
 * plugin should gate this behind a "delete my data on uninstall"
 * setting the site owner explicitly opted into, e.g.:
 *
 *     if ( '1' !== get_option( 'wpsprout_delete_data_on_uninstall' ) ) {
 *         return;
 *     }
 *
 * The example below drops the schema-version and migration-tracking
 * options unconditionally because they are WPSprout's own bookkeeping,
 * not plugin data — replace/extend this once you have real tables.
 */
delete_option( 'wpsprout_db_version' );
delete_option( 'wpsprout_applied_migrations' );
