<?php
/**
 * Sidebar Login Template Tags.
 *
 * Handles replacement of special tags used in the Sidebar Login widget settings.
 *
 * @package MJ\SidebarLogin\Utilities
 */

namespace MJ\SidebarLogin\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * TemplateTags class.
 */
class TemplateTags {
	/**
	 * User object.
	 *
	 * @var \WP_User|null
	 */
	private $user;

	/**
	 * Widget instance.
	 *
	 * @var array
	 */
	private $widget_instance;

	/**
	 * Replacement values, built on first use.
	 *
	 * @var array|null
	 */
	private $replacements;

	/**
	 * Constructor.
	 *
	 * @param \WP_User|null $user User object, or null.
	 * @param array         $widget_instance Widget instance.
	 */
	public function __construct( $user, $widget_instance ) {
		$this->user            = $user;
		$this->widget_instance = $widget_instance;
	}

	/**
	 * Get text string replacements using logged in user data.
	 *
	 * @return array
	 */
	protected function get_replacements() {
		if ( isset( $this->replacements ) ) {
			return $this->replacements;
		}

		$user_data       = (object) array(
			'id'           => $this->user ? $this->user->ID : 0,
			'display_name' => $this->user ? $this->user->display_name : '',
			'first_name'   => $this->user ? $this->user->first_name : '',
			'last_name'    => $this->user ? $this->user->last_name : '',
			'nice_name'    => $this->user ? $this->user->user_nicename : '',
			'avatar'       => get_avatar( $this->user ? $this->user->ID : 0, apply_filters( 'sidebar_login_widget_avatar_size', 48 ) ),
		);
		$logout_redirect = wp_logout_url( empty( $this->widget_instance['logout_redirect_url'] ) ? remove_query_arg( '_login', Request::current_url() ) : $this->widget_instance['logout_redirect_url'] );
		$replacements    = array(
			'%username%'   => ucwords( $user_data->display_name ),
			'%userid%'     => $user_data->id,
			'%firstname%'  => $user_data->first_name,
			'%lastname%'   => $user_data->last_name,
			'%name%'       => trim( $user_data->first_name . ' ' . $user_data->last_name ),
			'%nicename%'   => $user_data->nice_name,
			'%avatar%'     => $user_data->avatar,
			'%site_url%'   => site_url(),
			'%admin_url%'  => admin_url(),
			'%logout_url%' => apply_filters( 'sidebar_login_widget_logout_redirect', $logout_redirect ),
		);

		// Buddypress.
		if ( function_exists( 'bp_loggedin_user_domain' ) ) {
			$replacements['%buddypress_profile_url%'] = bp_loggedin_user_domain();
		}

		// BBpress.
		if ( function_exists( 'bbp_get_user_profile_url' ) ) {
			$replacements['%bbpress_profile_url%'] = bbp_get_user_profile_url( $this->user ? $this->user->ID : 0 );
		}

		$this->replacements = $replacements;

		return $replacements;
	}

	/**
	 * Replace template tags in a string with user data.
	 *
	 * @param string $text Text to replace tags within.
	 * @return string The updated string.
	 */
	public function replace( $text ) {
		return $this->replace_with( $text, $this->get_replacements() );
	}

	/**
	 * Replace template tags in a widget title, which is passed through do_shortcode() afterwards.
	 *
	 * Square brackets in the replaced values are encoded so that values users control, such as their display name,
	 * can't add shortcodes. Shortcodes typed into the title setting still run.
	 *
	 * @param string $text Title to replace tags within.
	 * @return string The updated title.
	 */
	public function replace_in_title( $text ) {
		$replacements = array_map(
			static function ( $value ) {
				return str_replace( array( '[', ']' ), array( '&#91;', '&#93;' ), (string) $value );
			},
			$this->get_replacements()
		);

		return $this->replace_with( $text, $replacements );
	}

	/**
	 * Replace template tags in a string with the given values.
	 *
	 * @param string $text Text to replace tags within.
	 * @param array  $replacements Values keyed by tag.
	 * @return string The updated string.
	 */
	private function replace_with( $text, $replacements ) {
		// %admin_url% ends in a slash, so drop the extra one from settings like the default "%admin_url%/profile.php".
		$text         = str_replace( '%admin_url%/', '%admin_url%', $text );
		$replacements = array_map( 'strval', $replacements );

		// strtr() replaces in one pass, so tags inside replaced values, such as a display name, aren't expanded.
		$new_string = strtr( $text, $replacements );

		// Also replace URL encoded values so nested replacements in, for examples, links are replaced.
		return strtr( $new_string, array_combine( array_map( 'urlencode', array_keys( $replacements ) ), array_map( 'urlencode', $replacements ) ) );
	}
}
