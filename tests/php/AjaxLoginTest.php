<?php
/**
 * The AJAX login handler.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

use WPAjaxDieContinueException;
use WP_Ajax_UnitTestCase;

/**
 * AJAX login tests.
 */
class AjaxLoginTest extends WP_Ajax_UnitTestCase {
	/**
	 * Test user.
	 *
	 * @var \WP_User
	 */
	private $user;

	public function set_up() {
		parent::set_up();
		// Headers have already been sent by PHPUnit, so auth cookies can't be set.
		add_filter( 'send_auth_cookies', '__return_false' );
		$this->user = self::factory()->user->create_and_get(
			array(
				'user_login' => 'sidebar_user',
				'user_email' => 'sidebar@example.org',
				'user_pass'  => 'correct horse',
			)
		);
		wp_set_current_user( 0 );
	}

	/**
	 * Post the login form to the AJAX handler.
	 *
	 * @param array $fields Posted fields.
	 * @return array Decoded JSON response.
	 */
	private function post_login( $fields ) {
		$_POST = $fields;

		try {
			$this->_handleAjax( 'sidebar_login_process' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		return json_decode( $this->_last_response, true );
	}

	public function test_valid_credentials_log_in_and_return_the_redirect() {
		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'correct horse',
				'redirect_to'   => home_url( '/after-login/' ),
			)
		);

		$this->assertSame(
			array(
				'success'  => 1,
				'redirect' => home_url( '/after-login/' ),
				'error'    => '',
			),
			$response
		);
		$this->assertSame( 1, did_action( 'wp_login' ) );
	}

	public function test_email_address_can_be_used_to_log_in() {
		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar@example.org',
				'user_password' => 'correct horse',
			)
		);

		$this->assertSame( 1, $response['success'] );
	}

	public function test_wrong_password_returns_an_error() {
		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'wrong',
			)
		);

		$this->assertSame( 0, $response['success'] );
		$this->assertStringContainsString( 'password you entered', $response['error'] );
		$this->assertSame( 0, did_action( 'wp_login' ) );
	}

	public function test_missing_credentials_return_an_error() {
		$response = $this->post_login( array() );

		$this->assertSame( 0, $response['success'] );
		$this->assertNotEmpty( $response['error'] );
	}

	public function test_redirect_to_another_site_is_dropped() {
		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'correct horse',
				'redirect_to'   => 'https://attacker.example/phishing-page',
			)
		);

		$this->assertSame( 1, $response['success'] );
		$this->assertSame( '', $response['redirect'], 'An empty redirect makes the page reload instead.' );
	}

	public function test_redirect_to_an_allowed_host_is_kept() {
		add_filter(
			'allowed_redirect_hosts',
			static function ( $hosts ) {
				return array_merge( $hosts, array( 'partner.example' ) );
			}
		);

		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'correct horse',
				'redirect_to'   => 'https://partner.example/welcome/',
			)
		);

		$this->assertSame( 'https://partner.example/welcome/', $response['redirect'] );
	}

	public function test_login_redirect_filter_is_applied() {
		add_filter(
			'login_redirect',
			static function ( $redirect_to, $requested_redirect_to, $user ) {
				return $user instanceof \WP_User ? home_url( '/members/' ) : $redirect_to;
			},
			10,
			3
		);

		$response = $this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'correct horse',
				'redirect_to'   => home_url( '/page/' ),
			)
		);

		$this->assertSame( home_url( '/members/' ), $response['redirect'] );
	}

	public function test_login_errors_filter_is_applied() {
		add_filter(
			'login_errors',
			static function () {
				return 'Login failed.';
			}
		);

		$response = $this->post_login(
			array(
				'user_login'    => 'nobody',
				'user_password' => 'wrong',
			)
		);

		$this->assertSame( 'Login failed.', $response['error'] );
	}

	public function test_non_string_fields_are_ignored() {
		$response = $this->post_login(
			array(
				'user_login'    => array( 'sidebar_user' ),
				'user_password' => array( 'correct horse' ),
				'remember'      => array( '1' ),
				'redirect_to'   => array( 'https://attacker.example/' ),
			)
		);

		$this->assertSame( 0, $response['success'] );
		$this->assertSame( '', $response['redirect'] );
	}

	public function test_secure_cookie_defaults_to_the_connection_scheme() {
		$_SERVER['HTTPS'] = 'on';
		$secure           = 'not called';
		add_filter(
			'secure_signon_cookie',
			static function ( $secure_cookie ) use ( &$secure ) {
				$secure = $secure_cookie;
				return $secure_cookie;
			}
		);

		$this->post_login(
			array(
				'user_login'    => 'sidebar_user',
				'user_password' => 'correct horse',
			)
		);
		unset( $_SERVER['HTTPS'] );

		$this->assertTrue( $secure );
	}
}
