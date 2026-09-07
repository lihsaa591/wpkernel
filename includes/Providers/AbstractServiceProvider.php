<?php
/**
 * Base service provider.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Providers;

use League\Container\Container;
use WPSprout\Contracts\ServiceProviderInterface;

/**
 * Most providers only need to bind services, not hook into WordPress —
 * extend this and override boot() only when you actually need it.
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface {

	/**
	 * No-op by default. Override when the provider needs to hook resolved
	 * services into WordPress after every provider has registered.
	 *
	 * @param Container $container The DI container.
	 */
	public function boot( Container $container ): void {}
}
