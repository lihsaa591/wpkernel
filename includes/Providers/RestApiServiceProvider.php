<?php
/**
 * REST API service provider.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Providers;

use League\Container\Container;

/**
 * Reads the list of controller classes from config('rest.controllers')
 * and registers each one's routes on rest_api_init. Add a controller by
 * listing its class in config/app.php — nothing else wires it up.
 */
final class RestApiServiceProvider extends AbstractServiceProvider {

	/**
	 * Bind each configured controller class as a shared service.
	 *
	 * @param Container $container The DI container.
	 */
	public function register( Container $container ): void {
		$config = $container->get( 'config' );

		foreach ( (array) ( $config['rest']['controllers'] ?? array() ) as $controller_class ) {
			$container->add( $controller_class )->setShared( true );
		}
	}

	/**
	 * Register every configured controller's routes on rest_api_init.
	 *
	 * @param Container $container The DI container.
	 */
	public function boot( Container $container ): void {
		$config = $container->get( 'config' );

		add_action(
			'rest_api_init',
			static function () use ( $container, $config ) {
				foreach ( (array) ( $config['rest']['controllers'] ?? array() ) as $controller_class ) {
					$container->get( $controller_class )->register_routes();
				}
			}
		);
	}
}
