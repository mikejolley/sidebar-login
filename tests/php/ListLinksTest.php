<?php
/**
 * Link list parsing and rendering.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

use MJ\SidebarLogin\Utilities\ListLinks;
use MJ\SidebarLogin\Utilities\TemplateTags;

/**
 * ListLinks tests.
 */
class ListLinksTest extends TestCase {
	/**
	 * Get a ListLinks instance for a logged out visitor.
	 *
	 * @return ListLinks
	 */
	private function list_links() {
		return new ListLinks( new TemplateTags( null, array() ) );
	}

	public function test_setting_value_is_parsed_into_links() {
		$links = $this->list_links()->parse_setting_value( "  Help  |  http://example.org/help/  \n\nMissing URL\n| http://example.org/missing-text/" );

		$this->assertSame(
			array(
				'help' => array(
					'text' => 'Help',
					'href' => 'http://example.org/help/',
				),
			),
			$links
		);
	}

	public function test_non_latin_link_text_gets_a_unique_key() {
		$links = $this->list_links()->parse_setting_value( "Профиль | http://example.org/a/\nВыход | http://example.org/b/" );

		$this->assertCount( 2, $links );
	}

	public function test_links_needing_a_capability_are_dropped_for_users_without_it() {
		$this->log_in_as( 'subscriber' );

		$links = $this->list_links()->parse_setting_value( "Settings | http://example.org/s/ | Manage_Options\nRead | http://example.org/r/ | read" );

		$this->assertSame( array( 'read' ), array_keys( $links ) );
	}

	public function test_links_are_escaped_when_rendered() {
		ob_start();
		$this->list_links()->render(
			array(
				'bad"key' => array(
					'text' => 'Hi <script>alert(1)</script><strong>there</strong>',
					'href' => 'javascript:alert(1)',
				),
			)
		);
		$html = ob_get_clean();

		$this->assertStringContainsString( '<ul class="sidebar-login-links pagenav sidebar_login_links">', $html );
		$this->assertStringContainsString( 'class="bad&quot;key-link"', $html );
		$this->assertStringContainsString( '<a href="">', $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '<strong>there</strong>', $html );
	}

	public function test_nothing_is_rendered_without_links() {
		ob_start();
		$this->list_links()->render( array() );

		$this->assertSame( '', ob_get_clean() );
	}
}
