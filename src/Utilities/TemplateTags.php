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
	 * @var array|null
	 */
	private $user;

	/**
	 * Widget instance.
	 *
	 * @var TemplateTags
	 */
	private $widget_instance;

	/**
	 * Constructor.
	 *
	 * @param array|null $user User object, or null.
	 * @param array      $widget_instance Widget instance.
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
		$user_data = (object) array(
			'id'           => $this->user ? $this->user->ID : 0,
			'display_name' => $this->user ? $this->user->display_name : '',
			'first_name'   => $this->user ? $this->user->first_name : '',
			'last_name'    => $this->user ? $this->user->last_name : '',
			'nice_name'    => $this->user ? $this->user->user_nicename : '',
			'avatar'       => get_avatar( $this->user ? $this->user->ID : 0, apply_filters( 'sidebar_login_widget_avatar_size', 48 ) ),
		);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$logout_redirect = wp_logout_url( empty( $this->widget_instance['logout_redirect_url'] ) ? remove_query_arg( '_login', set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) : $this->widget_instance['logout_redirect_url'] );
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

		return $replacements;
	}

	/**
	 * Replace template tags in a string with user data.
	 *
	 * @param string $string Text to replace tags within.
	 * @return string The updated string.
	 */
	public function replace( $string ) {
		$replacements = $this->get_replacements();
		$keys         = array_keys( $replacements );
		$values       = array_values( $replacements );
		$new_string   = str_replace( $keys, $values, $string );

		// Also replace URL encoded values so nested replacements in, for examples, links are replaced.
		return str_replace( array_map( 'urlencode', $keys ), array_map( 'urlencode', $values ), $new_string );
	}
}
