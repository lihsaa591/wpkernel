<?php
/**
 * General application bindings.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Providers;

use League\Container\Container;

/**
 * Home for bindings that don't belong to a more specific provider.
 * Kept deliberately empty in the boilerplate — a real plugin's own
 * cross-cutting bindings (a settings repository, a mailer, ...) go here.
 */
final class AppServiceProvider extends AbstractServiceProvider {

	/**
	 * No bindings in the boilerplate — add your own plugin's
	 * cross-cutting services here (a settings repository, a mailer, etc.).
	 *
	 * @param Container $container The DI container.
	 */
	public function register( Container $container ): void {}
}
