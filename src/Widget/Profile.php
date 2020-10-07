<?php
/**
 * Renders the Profile and Logged In Display.
 *
 * @package MJ\SidebarLogin\Widget
 */

namespace MJ\SidebarLogin\Widget;

defined( 'ABSPATH' ) || exit;

use MJ\SidebarLogin\Utilities\TemplateTags;
use MJ\SidebarLogin\Utilities\ListLinks;

/**
 * Profile class.
 */
class Profile {
	/**
	 * Stores instance of TemplateTags.
	 *
	 * @var TemplateTags
	 */
	private $template_tags;

	/**
	 * Stores instance of ListLinks.
	 *
	 * @var ListLinks
	 */
	private $list_links;

	/**
	 * Stores args provided to the widget from the theme.
	 *
	 * @var array
	 */
	private $widget_args = array();

	/**
	 * Stores settings provided to the widget.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Constructor.
	 *
	 * @param TemplateTags $template_tags Instance of TemplateTags utility class.
	 * @param ListLinks    $list_links Instance of ListLinks utility class.
	 * @param array        $widget_args Array of args provided to the widget itself.
	 * @param array        $settings Array of widget settings values.
	 */
	public function __construct( TemplateTags $template_tags, ListLinks $list_links, $widget_args = array(), $settings = array() ) {
		$this->template_tags = $template_tags;
		$this->list_links    = $list_links;
		$this->widget_args   = $widget_args;
		$this->settings      = wp_parse_args(
			$settings,
			array(
				'logged_in_title'     => __( 'Welcome', 'sidebar-login' ) . ' %username%',
				'logged_in_links'     => array(),
				'logout_redirect_url' => '',
				'show_avatar'         => 1,
			)
		);
	}

	/**
	 * Renders the login form.
	 */
	public function render() {
		$logged_in_title = do_shortcode(
			$this->template_tags->replace(
				apply_filters( 'sidebar_login_widget_logged_in_title', $this->settings['logged_in_title'] )
			)
		);

		if ( ! empty( $logged_in_title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->widget_args['before_title'] . wp_kses_post( $logged_in_title ) . $this->widget_args['after_title'];
		}

		do_action( 'sidebar_login_widget_logged_in_content_start' );

		echo '<div class="sidebar-login-profile">';

		if ( ! empty( $this->settings['show_avatar'] ) ) {
			echo '<div class="sidebar-login-profile__avatar avatar_container">' . get_avatar( get_current_user_id(), apply_filters( 'sidebar_login_widget_avatar_size', 48 ) ) . '</div>';
		}

		$this->render_links();

		echo '</div>';

		do_action( 'sidebar_login_widget_logged_in_content_end' );
	}

	/**
	 * Render logged in links setting.
	 */
	protected function render_links() {
		do_action( 'sidebar_login_widget_before_logged_in_links' );

		$links = apply_filters(
			'sidebar_login_widget_logged_in_links',
			$this->list_links->parse_setting_value( $this->settings['logged_in_links'] )
		);

		$this->list_links->render( $links );

		do_action( 'sidebar_login_widget_after_logged_in_links' );
	}
}
