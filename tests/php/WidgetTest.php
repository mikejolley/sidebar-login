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

	public function test_shortcodes_in_profile_fields_do_not_run_in_the_title() {
		add_shortcode(
			'sl_test',
			static function () {
				return 'SHORTCODE RAN';
			}
		);
		$this->log_in_as( 'subscriber', array( 'display_name' => '[sl_test]' ) );

		$html = $this->render_widget();

		$this->assertStringNotContainsString( 'SHORTCODE RAN', $html );
		$this->assertStringContainsString( '<h2 class="widget-title">Welcome &#091;sl_test&#093;</h2>', $html );
	}

	public function test_shortcodes_in_the_title_setting_still_run() {
		add_shortcode(
			'sl_test',
			static function ( $atts ) {
				return 'Shortcode for user ' . $atts['id'];
			}
		);
		$user = $this->log_in_as();

		$html = $this->render_widget(
			array(
				'logged_in_title' => '[sl_test id="%userid%"]',
				'logged_in_links' => '',
			)
		);

		$this->assertStringContainsString( '<h2 class="widget-title">Shortcode for user ' . $user->ID . '</h2>', $html );
	}

	public function test_default_redirect_uses_the_site_address_not_the_host_header() {
		$host                 = $_SERVER['HTTP_HOST']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Restored below.
		$_SERVER['HTTP_HOST'] = 'attacker.example';
		$html                 = $this->render_widget();
		$_SERVER['HTTP_HOST'] = $host;

		$this->assertStringContainsString( 'name="redirect_to" value="' . home_url( '/' ), $html );
		$this->assertStringNotContainsString( 'attacker.example', $html );
	}

	public function test_widget_renders_without_a_host_header() {
		$host = $_SERVER['HTTP_HOST']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Restored below.
		unset( $_SERVER['HTTP_HOST'] );
		$this->log_in_as();
		$html                 = $this->render_widget();
		$_SERVER['HTTP_HOST'] = $host;

		$this->assertStringContainsString( 'action=logout', $html );
	}

	public function test_settings_saved_without_unticked_checkboxes_keep_them_off() {
		// Before 3.1.0, unticked checkboxes were left out of the saved settings.
		$html = $this->render_widget(
			array(
				'logged_out_title'   => 'Login',
				'login_redirect_url' => '',
				'logged_out_links'   => '',
			)
		);

		$this->assertStringNotContainsString( 'name="rememberme"', $html );
		$this->assertStringNotContainsString( 'lost_password-link', $html );
	}

	public function test_settings_form_handles_partial_settings() {
		$widget = new Widget();
		$widget->_set( 1 );

		ob_start();
		$widget->form( array( 'logged_out_title' => 'Sign in' ) );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'value="Sign in"', $html );
		$this->assertStringNotContainsString( 'checked=', $html );
	}

	public function test_settings_form_fields_have_unique_ids() {
		$widget = new Widget();
		$widget->_set( 1 );

		ob_start();
		$widget->form( array() );
		$html = ob_get_clean();

		$this->assertSame( 1, substr_count( $html, 'id="widget-wp_sidebarlogin-1-logged_out_title"' ) );
		$this->assertSame( 1, substr_count( $html, 'id="widget-wp_sidebarlogin-1-logged_in_title"' ) );
	}

	public function test_saved_settings_are_sanitized() {
		$this->log_in_as( 'author' );

		$instance = ( new Widget() )->update(
			array(
				'logged_out_title'   => 'Hi <script>alert(1)</script><em>there</em>',
				'logged_in_links'    => array( 'not a string' ),
				'login_redirect_url' => '<b>%site_url%/welcome/</b>',
				'show_avatar'        => 'on',
			),
			array()
		);

		$this->assertSame( 'Hi alert(1)<em>there</em>', $instance['logged_out_title'] );
		$this->assertSame( '', $instance['logged_in_links'] );
		$this->assertSame( '%site_url%/welcome/', $instance['login_redirect_url'] );
		$this->assertSame( '1', $instance['show_avatar'] );
		$this->assertSame( '', $instance['show_rememberme'], 'Unticked checkboxes are saved as empty.' );
	}

	public function test_saving_no_settings_keeps_the_defaults() {
		$this->assertSame( array(), ( new Widget() )->update( array(), array() ) );
	}

	public function test_saved_settings_keep_html_for_users_who_can_post_it() {
		$this->log_in_as( 'administrator' );
		$title = '<span onclick="go()">Login</span>';

		$instance = ( new Widget() )->update( array( 'logged_out_title' => $title ), array() );

		$this->assertSame( current_user_can( 'unfiltered_html' ) ? $title : wp_kses_post( $title ), $instance['logged_out_title'] );
	}

	public function test_each_login_form_on_a_page_gets_its_own_field_ids() {
		$first  = $this->render_widget();
		$second = $this->render_widget();

		preg_match( '#<input type="text" name="log" id="([^"]+)"#', $first, $first_id );
		preg_match( '#<input type="text" name="log" id="([^"]+)"#', $second, $second_id );
		$this->assertNotSame( $first_id[1], $second_id[1] );
		$this->assertStringContainsString( 'for="' . $second_id[1] . '"', $second );
	}
}
