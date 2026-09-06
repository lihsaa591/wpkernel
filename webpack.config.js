/**
 * Extends the @wordpress/scripts default webpack config to build multiple
 * named entries into build/<entry>.js (+ matching .asset.php), instead of
 * the single src/index.js the default config assumes.
 *
 * Add a new admin/frontend script by adding an entry here and creating
 * the matching src/<name>/index.js file.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve( __dirname, 'src/admin/index.js' ),
	},
};
