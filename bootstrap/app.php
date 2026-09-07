<?php
/**
 * Application bootstrap.
 *
 * Wires config/app.php into the Plugin singleton and runs it. This file
 * intentionally contains no logic beyond that wiring — anything more
 * belongs in a provider.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

use WPSprout\Plugin;

$wpsprout_config = require WPSPROUT_PATH . 'config/app.php';

$wpsprout = Plugin::instance();

// Bind config before any provider registers, so register()/boot() can
// read it via $container->get( 'config' ).
$wpsprout->container()->add( 'config', $wpsprout_config )->setShared( true );

foreach ( $wpsprout_config['providers'] as $wpsprout_provider_class ) {
	$wpsprout->add_provider( $wpsprout_provider_class );
}

$wpsprout->run();
