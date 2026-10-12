<?php
/**
 * Plugin bootstrap: widget registration and assets.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

/**
 * Plugin tests.
 */
class PluginTest extends TestCase {
	public function set_up() {
		parent::set_up();
		$this->reset_assets();
	}

	public function tear_down() {
		$this->reset_assets();
		parent::tear_down();
	}

	/**
	 * Start each test with fresh script and style registries, so enqueues don't leak between tests.
	 */
	private function reset_assets() {
		$GLOBALS['wp_scripts'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Recreated by wp_scripts().
		$GLOBALS['wp_styles']  = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Recreated by wp_styles().
	}

	public function test_widget_and_legacy_widget_are_registered() {
		$id_bases = wp_list_pluck( $GLOBALS['wp_widget_factory']->widgets, 'id_base' );

		$this->assertArrayHasKey( 'Sidebar_Login_Widget', $id_bases );
		$this->assertSame( 'wp_sidebarlogin', $id_bases['Sidebar_Login_Widget'] );
		$this->assertContains( 'wp_sidebarlogin', $id_bases );
	}

	public function test_stylesheet_is_enqueued_and_script_is_registered() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'sidebar-login', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'sidebar-login', 'registered' ) );
		$this->assertFalse( wp_script_is( 'sidebar-login', 'enqueued' ), 'The script is only enqueued when the login form renders.' );

		$data = wp_scripts()->get_data( 'sidebar-login', 'data' );
		$this->assertSame( 1, preg_match( '/^var sidebar_login_params = (.*);$/', $data, $matches ) );
		$params = json_decode( $matches[1], true );
		$this->assertSame( '/wp-admin/admin-ajax.php', $params['ajax_url'] );
		$this->assertSame( 'sidebar-login-error', $params['error_class'] );
	}

	public function test_stylesheet_can_be_disabled() {
		add_filter( 'sidebar_login_include_css', '__return_false' );
		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_style_is( 'sidebar-login', 'enqueued' ) );
	}

	public function test_login_form_enqueues_the_script() {
		do_action( 'wp_enqueue_scripts' );
		$this->render_widget();

		$this->assertTrue( wp_script_is( 'sidebar-login', 'enqueued' ) );
	}

	public function test_account_display_does_not_enqueue_the_script() {
		do_action( 'wp_enqueue_scripts' );
		$this->log_in_as();
		$this->render_widget();

		$this->assertFalse( wp_script_is( 'sidebar-login', 'enqueued' ) );
	}

	public function test_rtl_stylesheet_replaces_the_default_in_rtl_languages() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertSame( 'replace', wp_styles()->get_data( 'sidebar-login', 'rtl' ) );
	}
}
