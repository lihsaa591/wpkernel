<?php
/**
 * Plugin deactivation.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout;

/**
 * Deactivation is reversible housekeeping only (flush rewrite rules,
 * clear scheduled events). Destructive cleanup — dropping tables,
 * deleting options — belongs in uninstall.php, gated behind an explicit
 * "remove data on uninstall" setting, and only runs when the user
 * deletes the plugin, not merely deactivates it.
 */
final class Deactivation {

	/**
	 * Reversible housekeeping only. See the class docblock for why
	 * destructive cleanup does not belong here.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
