<?php
/**
 * Renders a list of links.
 *
 * @package MJ\SidebarLogin\Utilities
 */

namespace MJ\SidebarLogin\Utilities;

defined( 'ABSPATH' ) || exit;

use MJ\SidebarLogin\Utilities\TemplateTags;

/**
 * ListLinks class.
 */
class ListLinks {
	/**
	 * Stores instance of TemplateTags.
	 *
	 * @var TemplateTags
	 */
	private $template_tags;

	/**
	 * Constructor.
	 *
	 * @param TemplateTags $template_tags Instance of TemplateTags utility class.
	 */
	public function __construct( TemplateTags $template_tags ) {
		$this->template_tags = $template_tags;
	}

	/**
	 * Parse links stored in the widget settings (pipe separated values) into a usable array of data.
	 *
	 * @param string $setting_value Value stored to settings.
	 * @return array
	 */
	public function parse_setting_value( $setting_value ) {
		$raw_values = array_filter( array_map( 'trim', explode( "\n", $setting_value ) ) );
		$links      = array();

		foreach ( $raw_values as $raw_value ) {
			$data = array_map( 'trim', explode( '|', $raw_value ) );
			$text = isset( $data[0] ) ? $data[0] : '';
			$href = isset( $data[1] ) ? $data[1] : '';
			$cap  = isset( $data[2] ) ? strtolower( $data[2] ) : '';

			if ( empty( $text ) || empty( $href ) ) {
				continue;
			}

			if ( ! empty( $cap ) && ! current_user_can( $cap ) ) {
				continue;
			}
			$links[ sanitize_title( $text ) ] = array(
				'text' => $text,
				'href' => $href,
			);
		}

		return $links;
	}

	/**
	 * Render a list of links in a UL element,
	 *
	 * @param array $links List of links.
	 */
	public function render( $links = array() ) {
		if ( ! empty( $links ) && is_array( $links ) && count( $links ) > 0 ) {
			echo '<ul class="sidebar-login-links pagenav sidebar_login_links">';

			foreach ( $links as $id => $link ) {
				echo '<li class="' . esc_attr( $id ) . '-link"><a href="' . esc_url( $this->template_tags->replace( $link['href'] ) ) . '">' . wp_kses_post( $this->template_tags->replace( $link['text'] ) ) . '</a></li>';
			}

			echo '</ul>';
		}
	}
}
