<?php
/**
 * Sidebar Login
 *
 * @package           SidebarLogin
 * @author            Mike Jolley
 * @copyright         2020 Mike Jolley.
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Sidebar Login
 * Plugin URI:        http://wordpress.org/extend/plugins/sidebar-login/
 * Description:       Easily add an ajax-enhanced login widget to the sidebar of your WordPress site.
 * Version:           3.0.0
 * Author:            Mike Jolley
 * Author URI:        http://mikejolley.com
 * Requires at least: 5.2
 * Tested up to:      5.5
 * Requires PHP:      5.6
 * Text Domain:       sidebar-login
 * Domain Path:       /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Require Autoloader, and ensure build is complete. Otherwise abort.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';
$build      = __DIR__ . '/build/frontend.js';
if ( is_readable( $autoloader ) && is_readable( $build ) ) {
	require $autoloader;
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log(  // phpcs:ignore
			sprintf(
				/* translators: 1: composer command. 2: plugin directory */
				esc_html__( 'Your installation of Sidebar Login is incomplete. Please run %1$s within the %2$s directory, or download the built plugin files from wordpress.org.', 'sidebar-login' ),
				'`composer install && && npm install && npm run build`',
				'`' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '`'
			)
		);
	}
	/**
	 * Outputs an admin notice if composer install has not been ran.
	 */
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: 1: composer command. 2: plugin directory */
						esc_html__( 'Your installation of Sidebar Login is incomplete. Please run %1$s within the %2$s directory, or download the built plugin files from wordpress.org.', 'sidebar-login' ),
						'<code>composer install && && npm install && npm run build</code>',
						'<code>' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

/**
 * Fetch instance of plugin.
 *
 * @return \MJ\SidebarLogin\Plugin
 */
function sidebar_login_init() {
	static $instance;

	if ( is_null( $instance ) ) {
		$instance = new \MJ\SidebarLogin\Plugin( __FILE__ );
	}

	return $instance;
}

add_action( 'plugins_loaded', 'sidebar_login_init', 20 );
