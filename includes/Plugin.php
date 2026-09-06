<?php
/**
 * Main plugin class.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel;

use League\Container\Container;
use WPKernel\Contracts\ServiceProviderInterface;

/**
 * Owns the DI container and the two-phase provider boot sequence:
 * every provider registers its bindings first, then every provider boots
 * (hooks into WordPress) — so a provider can safely depend on another
 * provider's bindings during boot regardless of registration order.
 */
final class Plugin {

	/**
	 * The singleton instance.
	 *
	 * @var self|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * The DI container shared by every provider.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Registered providers, in registration order.
	 *
	 * @var ServiceProviderInterface[]
	 */
	private array $providers = array();

	/**
	 * Whether run() has already executed.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Private: use instance() to obtain the singleton.
	 */
	private function __construct() {
		$this->container = new Container();
		$this->container->add( self::class, $this )->setShared( true );
		$this->container->add( Container::class, $this->container )->setShared( true );
	}

	/**
	 * Get the singleton instance, constructing it on first call.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register a service provider class. Safe to call multiple times
	 * before run() — providers are booted in registration order.
	 *
	 * @param string $provider_class Fully-qualified class-string<ServiceProviderInterface>.
	 *
	 * @throws \RuntimeException If called after run() has already booted the plugin.
	 */
	public function add_provider( string $provider_class ): self {
		if ( $this->booted ) {
			throw new \RuntimeException(
				sprintf( 'Cannot register provider "%s" after the plugin has booted.', $provider_class ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- developer-facing exception message (logs/CLI), not rendered as markup.
			);
		}

		/**
		 * The newly constructed provider.
		 *
		 * @var ServiceProviderInterface $provider
		 */
		$provider          = new $provider_class();
		$this->providers[] = $provider;

		return $this;
	}

	/**
	 * Run every provider's register() phase, then every boot() phase.
	 */
	public function run(): void {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->register( $this->container );
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot( $this->container );
		}

		$this->booted = true;

		/**
		 * Fires once WPKernel and all of its registered providers have booted.
		 *
		 * @param Plugin $plugin The booted plugin instance.
		 */
		do_action( 'wpkernel_booted', $this );
	}

	/**
	 * The shared DI container.
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Resolve a binding out of the container. Thin convenience wrapper —
	 * prefer constructor injection inside providers; reach for this only
	 * at WordPress hook boundaries where the container isn't otherwise reachable.
	 *
	 * @param string $id Fully-qualified class name, or a plain string key for a non-class binding.
	 * @return mixed The resolved binding.
	 */
	public function make( string $id ) {
		return $this->container->get( $id );
	}
}
