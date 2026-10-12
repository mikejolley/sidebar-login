/**
 * Extends the `@wordpress/scripts` Playwright config, which targets wp-env at http://localhost:8889.
 */
const path = require( 'path' );
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

module.exports = {
	...baseConfig,
	testDir: path.join( __dirname, 'specs' ),
};
