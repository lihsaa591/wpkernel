<?php
/**
 * Renames WPKernel into your own plugin: namespace, slug, text domain,
 * and plugin display name, across the whole codebase.
 *
 * Usage:
 *   php bin/scaffold.php --namespace=YourPlugin --slug=your-plugin \
 *       --text-domain=your-plugin --name="Your Plugin" [--dry-run]
 *
 * This is a best-effort search/replace, not a guarantee — review the
 * diff before committing. Re-run is not idempotent; run it once, on a
 * clean checkout.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

$options = getopt( '', array( 'namespace:', 'slug:', 'text-domain:', 'name:', 'dry-run' ) );

foreach ( array( 'namespace', 'slug', 'text-domain', 'name' ) as $required ) {
	if ( ! isset( $options[ $required ] ) ) {
		fwrite( STDERR, "Missing required --{$required}=... argument.\n" );
		exit( 1 );
	}
}

$namespace   = trim( (string) $options['namespace'], '\\' );
$slug        = (string) $options['slug'];
$text_domain = (string) $options['text-domain'];
$plugin_name = (string) $options['name'];
$dry_run     = isset( $options['dry-run'] );

if ( ! preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $namespace ) ) {
	fwrite( STDERR, "--namespace must be a valid PHP namespace segment (letters, digits, underscore; no backslashes needed).\n" );
	exit( 1 );
}
if ( ! preg_match( '/^[a-z][a-z0-9-]*$/', $slug ) ) {
	fwrite( STDERR, "--slug must be lowercase letters, digits and hyphens (e.g. \"your-plugin\").\n" );
	exit( 1 );
}

$root = dirname( __DIR__ );

$constant_prefix = strtoupper( str_replace( '-', '_', $slug ) );

// Token map applied to every matched file. "WPKernel" is ambiguous (it's
// both the PHP namespace root AND, in the plugin header's "Plugin Name:"
// line, the human-readable name) — that one collision is resolved as an
// explicit extra pass on the main plugin file, below.
$skip_dirs = array( '.git', 'node_modules', 'vendor', 'build', 'dist', 'coverage' );

$extensions = array( 'php', 'json', 'md', 'xml', 'neon', 'yml', 'yaml', 'js' );

$files_changed = 0;

$iterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
		static function ( SplFileInfo $file ) use ( $skip_dirs ) {
			return ! ( $file->isDir() && in_array( $file->getFilename(), $skip_dirs, true ) );
		}
	)
);

foreach ( $iterator as $file ) {
	/** @var SplFileInfo $file */
	if ( ! $file->isFile() ) {
		continue;
	}

	if ( ! in_array( strtolower( $file->getExtension() ), $extensions, true ) ) {
		continue;
	}

	$path     = $file->getPathname();
	$original = file_get_contents( $path );
	$updated  = strtr( $original, array( 'WPKernel' => $namespace, 'wpkernel' => $slug, 'WPKERNEL' => $constant_prefix ) );

	if ( str_ends_with( $path, DIRECTORY_SEPARATOR . 'wpkernel.php' ) ) {
		$updated = str_replace( "Plugin Name:       {$namespace}", "Plugin Name:       {$plugin_name}", $updated );
	}

	if ( $updated !== $original ) {
		++$files_changed;
		if ( ! $dry_run ) {
			file_put_contents( $path, $updated );
		} else {
			echo "Would update: {$path}\n";
		}
	}
}

$main_file_old = $root . '/wpkernel.php';
$main_file_new = $root . "/{$slug}.php";

if ( file_exists( $main_file_old ) ) {
	if ( $dry_run ) {
		echo "Would rename: wpkernel.php -> {$slug}.php\n";
	} else {
		rename( $main_file_old, $main_file_new );
	}
}

printf(
	"%s %d file(s)%s.\n",
	$dry_run ? 'Would update' : 'Updated',
	$files_changed,
	$dry_run ? ' (dry run — nothing written)' : ''
);

if ( ! $dry_run ) {
	echo "Text domain and constant prefix set to \"{$text_domain}\" via the slug replacement above.\n";
	echo "Now: composer install && npm install && npm run build, then review the diff.\n";
}
