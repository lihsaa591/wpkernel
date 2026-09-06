<?php
/**
 * Constants normally defined at runtime by the main plugin file
 * (wpkernel.php), declared here purely so PHPStan can resolve them
 * during static analysis — this file is never loaded by WordPress.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

define( 'WPKERNEL_VERSION', '0.1.0' );
define( 'WPKERNEL_FILE', dirname( __DIR__ ) . '/wpkernel.php' );
define( 'WPKERNEL_PATH', dirname( __DIR__ ) . '/' );
define( 'WPKERNEL_URL', 'https://example.test/wp-content/plugins/wpkernel/' );
define( 'WPKERNEL_BASENAME', 'wpkernel/wpkernel.php' );
