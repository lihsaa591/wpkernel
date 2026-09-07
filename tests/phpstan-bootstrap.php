<?php
/**
 * Constants normally defined at runtime by the main plugin file
 * (wpsprout.php), declared here purely so PHPStan can resolve them
 * during static analysis — this file is never loaded by WordPress.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

define( 'WPSPROUT_VERSION', '0.1.0' );
define( 'WPSPROUT_FILE', dirname( __DIR__ ) . '/wpsprout.php' );
define( 'WPSPROUT_PATH', dirname( __DIR__ ) . '/' );
define( 'WPSPROUT_URL', 'https://example.test/wp-content/plugins/wpsprout/' );
define( 'WPSPROUT_BASENAME', 'wpsprout/wpsprout.php' );
