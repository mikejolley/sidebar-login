/**
 * Extends the @wordpress/scripts config with the frontend script and the widget stylesheet. The output file names
 * (build/frontend.js, build/frontend.asset.php, build/sidebar-login.css) are what Plugin::enqueue_scripts() loads.
 */
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		frontend: './assets/js/frontend.js',
		'sidebar-login': './assets/css/sidebar-login.scss',
	},
	// Drop the empty JS file webpack emits for the stylesheet-only entry.
	plugins: [ ...defaultConfig.plugins, new RemoveEmptyScriptsPlugin() ],
};
