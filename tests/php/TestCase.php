<?php
/**
 * Shared helpers for plugin tests.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin\Tests;

use MJ\SidebarLogin\Widget;
use WP_UnitTestCase;

/**
 * Base test case.
 */
abstract class TestCase extends WP_UnitTestCase {
	/**
	 * Theme-provided widget wrapper args.
	 *
	 * @var array
	 */
	protected $widget_args = array(
		'before_widget' => '<section class="widget widget_wp_sidebarlogin">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	public function set_up() {
		parent::set_up();
		// The widget builds its default redirect from the current request.
		$this->go_to( home_url( '/' ) );
	}

	/**
	 * Render the widget and return its HTML.
	 *
	 * @param array $instance Widget settings. Empty uses the defaults.
	 * @return string
	 */
	protected function render_widget( $instance = array() ) {
		ob_start();
		( new Widget() )->widget( $this->widget_args, $instance );
		return ob_get_clean();
	}

	/**
	 * Create a user and log them in.
	 *
	 * @param string $role User role.
	 * @param array  $args Extra user fields.
	 * @return \WP_User
	 */
	protected function log_in_as( $role = 'subscriber', $args = array() ) {
		$user = self::factory()->user->create_and_get( array_merge( array( 'role' => $role ), $args ) );
		wp_set_current_user( $user->ID );
		return $user;
	}
}
