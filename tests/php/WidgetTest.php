<?php
/**
 * Widget rendering and settings form.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

use MJ\SidebarLogin\Widget;

/**
 * Widget tests.
 */
class WidgetTest extends TestCase {
	public function test_logged_out_renders_login_form_with_defaults() {
		$html = $this->render_widget();

		$this->assertStringContainsString( '<h2 class="widget-title">Login</h2>', $html );
		$this->assertStringContainsString( '<div class="sidebar-login-form">', $html );
		$this->assertStringContainsString( 'name="log"', $html );
		$this->assertStringContainsString( 'name="pwd"', $html );
		$this->assertStringContainsString( 'name="rememberme"', $html );
		$this->assertStringContainsString( 'class="lost_password-link"', $html );
		$this->assertStringContainsString( esc_url( wp_lostpassword_url() ), $html );
		$this->assertStringNotContainsString( 'register-link', $html, 'Registration is closed by default.' );
	}

	public function test_register_link_is_shown_when_registration_is_open() {
		update_option( 'users_can_register', 1 );

		$this->assertStringContainsString( 'class="register-link"', $this->render_widget() );
	}

	public function test_settings_hide_optional_login_form_parts() {
		update_option( 'users_can_register', 1 );

		$html = $this->render_widget(
			array(
				'logged_out_title'        => '',
				'show_rememberme'         => '',
				'show_lost_password_link' => '',
				'show_register_link'      => '',
			)
		);

		$this->assertStringNotContainsString( 'widget-title', $html );
		$this->assertStringNotContainsString( 'name="rememberme"', $html );
		$this->assertStringNotContainsString( 'lost_password-link', $html );
		$this->assertStringNotContainsString( 'register-link', $html );
	}

	public function test_login_redirect_setting_is_used() {
		$html = $this->render_widget( array( 'login_redirect_url' => 'http://example.org/welcome/' ) );

		$this->assertStringContainsString( 'name="redirect_to" value="http://example.org/welcome/"', $html );
	}

	public function test_login_redirects_to_the_current_page_by_default() {
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$html = $this->render_widget();

		$this->assertMatchesRegularExpression( '#name="redirect_to" value="' . preg_quote( home_url( '/?p=' . $post_id ), '#' ) . '&\#038;_login=[0-9a-f]{10}"#', $html );
	}

	public function test_logged_out_links_setting_is_rendered() {
		$html = $this->render_widget( array( 'logged_out_links' => "Help | http://example.org/help/\nNo URL |\n | http://example.org/no-text/" ) );

		$this->assertStringContainsString( '<li class="help-link"><a href="http://example.org/help/">Help</a></li>', $html );
		$this->assertStringNotContainsString( 'No URL', $html );
		$this->assertStringNotContainsString( 'no-text', $html );
	}

	public function test_logged_in_renders_account_with_defaults() {
		$user = $this->log_in_as( 'subscriber', array( 'display_name' => 'jane doe' ) );

		$html = $this->render_widget();

		$this->assertStringContainsString( '<h2 class="widget-title">Welcome Jane Doe</h2>', $html );
		$this->assertStringContainsString( 'sidebar-login-account__avatar', $html );
		$this->assertStringContainsString( '<li class="dashboard-link"><a href="' . esc_url( admin_url() ) . '">Dashboard</a></li>', $html );
		$this->assertStringContainsString( 'class="profile-link"', $html );
		$this->assertStringContainsString( 'profile.php', $html );
		$this->assertMatchesRegularExpression( '#<li class="logout-link"><a href="[^"]*action=logout[^"]*">Logout</a></li>#', $html );
		$this->assertStringNotContainsString( 'name="log"', $html );
		$this->assertNotEmpty( $user->ID );
	}

	public function test_avatar_can_be_hidden() {
		$this->log_in_as();

		$html = $this->render_widget(
			array(
				'logged_in_title' => 'Hi',
				'logged_in_links' => '',
				'show_avatar'     => '',
			)
		);

		$this->assertStringNotContainsString( 'sidebar-login-account__avatar', $html );
	}

	public function test_links_with_a_capability_are_only_shown_to_users_with_it() {
		$instance = array(
			'logged_in_title' => 'Hi',
			'logged_in_links' => "Settings | %admin_url%options-general.php | manage_options\nHome | %site_url%",
		);

		$this->log_in_as( 'subscriber' );
		$html = $this->render_widget( $instance );
		$this->assertStringNotContainsString( 'settings-link', $html );
		$this->assertStringContainsString( 'home-link', $html );

		$this->log_in_as( 'administrator' );
		$this->assertStringContainsString( 'settings-link', $this->render_widget( $instance ) );
	}

	public function test_display_filter_hides_the_widget() {
		add_filter( 'sidebar_login_widget_display', '__return_false' );

		$this->assertSame( '', $this->render_widget() );
	}

	public function test_widget_actions_fire_around_the_content() {
		$this->render_widget();

		$this->assertSame( 1, did_action( 'sidebar_login_widget_start' ) );
		$this->assertSame( 1, did_action( 'sidebar_login_widget_logged_out_content_start' ) );
		$this->assertSame( 1, did_action( 'sidebar_login_widget_logged_out_content_end' ) );
		$this->assertSame( 1, did_action( 'sidebar_login_widget_end' ) );
	}

	public function test_legacy_class_renders_with_the_widget() {
		ob_start();
		the_widget( 'Sidebar_Login_Widget' );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'widget_wp_sidebarlogin', $html );
		$this->assertStringContainsString( 'name="log"', $html );
	}

	public function test_settings_form_shows_defaults_for_a_new_widget() {
		$widget = new Widget();
		$widget->_set( 1 );

		ob_start();
		$widget->form( array() );
		$html = ob_get_clean();

		foreach ( array( 'logged_out_title', 'login_redirect_url', 'logged_out_links', 'show_rememberme', 'show_lost_password_link', 'show_register_link', 'logged_in_title', 'logout_redirect_url', 'logged_in_links', 'show_avatar' ) as $field ) {
			$this->assertStringContainsString( 'name="widget-wp_sidebarlogin[1][' . $field . ']"', $html );
		}
		$this->assertStringContainsString( 'value="Login"', $html );
		$this->assertStringContainsString( 'value="Welcome %username%"', $html );
	}
}
