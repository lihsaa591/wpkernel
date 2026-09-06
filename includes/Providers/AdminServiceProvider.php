<?php
/**
 * Admin UI service provider.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Providers;

use League\Container\Container;
use WPKernel\Admin\AdminPage;

/**
 * Builds one AdminPage per entry in config('admin.pages') — add a page
 * by adding an entry to config/app.php, no other wiring required.
 */
final class AdminServiceProvider extends AbstractServiceProvider {

	/**
	 * No bindings — pages are constructed directly in boot() since
	 * AdminPage takes plain scalar config, not resolved services.
	 *
	 * @param Container $container The DI container.
	 */
	public function register( Container $container ): void {}

	/**
	 * Build and register one AdminPage per config('admin.pages') entry.
	 *
	 * @param Container $container The DI container.
	 */
	public function boot( Container $container ): void {
		$config = $container->get( 'config' );

		foreach ( (array) ( $config['admin']['pages'] ?? array() ) as $page_config ) {
			( new AdminPage(
				page_title: $page_config['page_title'],
				menu_title: $page_config['menu_title'],
				capability: $page_config['capability'],
				menu_slug: $page_config['menu_slug'],
				mount_element_id: $page_config['mount_element_id'],
				script_entry: $page_config['script_entry'],
				parent_slug: $page_config['parent_slug'] ?? '',
				icon: $page_config['icon'] ?? 'dashicons-chart-area',
				position: $page_config['position'] ?? 30
			) )->register();
		}
	}
}
