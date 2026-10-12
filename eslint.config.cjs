/**
 * Extends the @wordpress/scripts ESLint config. The frontend script reads its settings from the
 * `sidebar_login_params` global, which Plugin::enqueue_scripts() adds with wp_localize_script().
 */
const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...defaultConfig,
	{
		files: [ 'assets/js/**/*.js' ],
		languageOptions: {
			globals: {
				sidebar_login_params: 'readonly',
			},
		},
	},
];
