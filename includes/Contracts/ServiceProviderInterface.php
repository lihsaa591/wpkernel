<?php
/**
 * Service provider contract.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Contracts;

use League\Container\Container;

/**
 * Every domain concern (database, REST API, admin UI, ...) is registered
 * with the container through a class implementing this interface.
 *
 * `register()` should only bind things into the container — no WordPress
 * hooks. `boot()` runs after every provider has registered, so it is safe
 * to resolve other providers' bindings here and hook them into WordPress.
 */
interface ServiceProviderInterface {

	/**
	 * Bind services into the container.
	 *
	 * @param Container $container The DI container.
	 */
	public function register( Container $container ): void;

	/**
	 * Hook resolved services into WordPress.
	 *
	 * Runs after every provider's register() has been called.
	 *
	 * @param Container $container The DI container.
	 */
	public function boot( Container $container ): void;
}
