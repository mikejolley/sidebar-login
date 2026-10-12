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
				'redirect_to'   => 'http://example.org/after-login/',
			)
		);

		$this->assertSame(
			array(
				'success'  => 1,
				'redirect' => 'http://example.org/after-login/',
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
}
