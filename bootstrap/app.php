<?php
/**
 * Application bootstrap.
 *
 * Wires config/app.php into the Plugin singleton and runs it. This file
 * intentionally contains no logic beyond that wiring — anything more
 * belongs in a provider.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

use WPKernel\Plugin;

$wpkernel_config = require WPKERNEL_PATH . 'config/app.php';

$wpkernel = Plugin::instance();

// Bind config before any provider registers, so register()/boot() can
// read it via $container->get( 'config' ).
$wpkernel->container()->add( 'config', $wpkernel_config )->setShared( true );

foreach ( $wpkernel_config['providers'] as $wpkernel_provider_class ) {
	$wpkernel->add_provider( $wpkernel_provider_class );
}

$wpkernel->run();
