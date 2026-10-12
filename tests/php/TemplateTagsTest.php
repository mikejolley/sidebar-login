<?php
/**
 * Template tag replacement.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

use MJ\SidebarLogin\Utilities\TemplateTags;

/**
 * TemplateTags tests.
 */
class TemplateTagsTest extends TestCase {
	public function test_user_tags_are_replaced() {
		$user = $this->log_in_as(
			'subscriber',
			array(
				'display_name'  => 'jane doe',
				'first_name'    => 'Jane',
				'last_name'     => 'Doe',
				'user_nicename' => 'jane-d',
			)
		);
		$tags = new TemplateTags( $user, array() );

		$this->assertSame(
			'Jane Doe|Jane|Doe|Jane Doe|jane-d|' . $user->ID,
			$tags->replace( '%username%|%firstname%|%lastname%|%name%|%nicename%|%userid%' )
		);
	}

	public function test_url_tags_are_replaced() {
		$tags = new TemplateTags( null, array() );

		$this->assertSame( site_url() . ' ' . admin_url(), $tags->replace( '%site_url% %admin_url%' ) );
	}

	public function test_url_encoded_tags_are_replaced_with_url_encoded_values() {
		$tags = new TemplateTags( null, array() );

		$this->assertSame(
			'http://example.org/?next=' . rawurlencode( site_url() ),
			$tags->replace( 'http://example.org/?next=' . rawurlencode( '%site_url%' ) )
		);
	}

	public function test_user_tags_are_empty_when_logged_out() {
		$tags = new TemplateTags( null, array() );

		$this->assertSame( '||0', $tags->replace( '%username%|%name%|%userid%' ) );
	}

	public function test_logout_url_uses_the_logout_redirect_setting() {
		$tags = new TemplateTags( null, array( 'logout_redirect_url' => 'http://example.org/bye/' ) );

		$logout_url = $tags->replace( '%logout_url%' );

		$this->assertStringContainsString( 'action=logout', $logout_url );
		$this->assertStringContainsString( 'redirect_to=' . rawurlencode( 'http://example.org/bye/' ), $logout_url );
	}

	public function test_logout_url_returns_to_the_current_page_by_default() {
		$this->go_to( home_url( '/?p=5&_login=abc' ) );
		$tags = new TemplateTags( null, array() );

		$logout_url = $tags->replace( '%logout_url%' );

		$this->assertStringContainsString( 'redirect_to=' . rawurlencode( home_url( '/?p=5' ) ), $logout_url );
		$this->assertStringNotContainsString( '_login', $logout_url );
	}
}
