<?php
/**
 * Request helpers.
 *
 * @package MJ\SidebarLogin\Utilities
 */

namespace MJ\SidebarLogin\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Request class.
 */
class Request {
	/**
	 * Get the URL of the page being viewed.
	 *
	 * The host comes from the site address rather than the request's Host header, so a forged header can't point the
	 * login and logout redirects at another site.
	 *
	 * @param string|null $scheme Scheme to give the URL. See set_url_scheme().
	 * @return string
	 */
	public static function current_url( $scheme = null ) {
		$home        = wp_parse_url( home_url() );
		$origin      = $home['scheme'] . '://' . $home['host'] . ( isset( $home['port'] ) ? ':' . $home['port'] : '' );
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

		return set_url_scheme( $origin . $request_uri, $scheme );
	}
}
