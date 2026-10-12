<?php
/**
 * PHPUnit bootstrap. Loads the WordPress test library (provided by wp-env at WP_TESTS_DIR) and the plugin.
 *
 * @package MJ/SidebarLogin
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	fwrite( STDERR, "Could not find the WordPress test library in {$_tests_dir}. Run the tests with `npm run test:php`.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/sidebar-login.php';
	}
);

require $_tests_dir . '/includes/bootstrap.php';
require __DIR__ . '/TestCase.php';
