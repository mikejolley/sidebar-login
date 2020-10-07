<?php
/**
 * Renders the Login Form and logged out display.
 *
 * @package MJ\SidebarLogin\Widget
 */

namespace MJ\SidebarLogin\Widget;

defined( 'ABSPATH' ) || exit;

use MJ\SidebarLogin\Utilities\TemplateTags;
use MJ\SidebarLogin\Utilities\ListLinks;

/**
 * LoginForm class.
 */
class LoginForm {
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
				'logged_out_title'   => __( 'Login', 'sidebar-login' ),
				'logged_out_links'   => array(),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'login_redirect_url' => add_query_arg( '_login', substr( md5( uniqid( wp_rand(), true ) ), 0, 10 ), set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'login' ) ),
				'show_rememberme'    => 0,
			)
		);
	}

	/**
	 * Renders the login form.
	 */
	public function render() {
		wp_enqueue_script( 'sidebar-login' );

		$logged_out_title = do_shortcode(
			$this->template_tags->replace(
				apply_filters( 'sidebar_login_widget_logged_out_title', $this->settings['logged_out_title'] )
			)
		);

		if ( ! empty( $logged_out_title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->widget_args['before_title'] . wp_kses_post( $logged_out_title ) . $this->widget_args['after_title'];
		}

		do_action( 'sidebar_login_widget_logged_out_content_start' );

		echo '<div class="sidebar-login-form">';

		$login_form_args = apply_filters(
			'sidebar_login_widget_form_args',
			array(
				'echo'           => false,
				'redirect'       => esc_url( apply_filters( 'sidebar_login_widget_login_redirect', $this->settings['login_redirect_url'] ) ),
				'label_username' => __( 'Username', 'sidebar-login' ),
				'label_password' => __( 'Password', 'sidebar-login' ),
				'label_remember' => __( 'Remember Me', 'sidebar-login' ),
				'label_log_in'   => __( 'Log In', 'sidebar-login' ),
				'remember'       => $this->settings['show_rememberme'],
				'value_remember' => true,
			)
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'sidebar_login_widget_wp_login_form', wp_login_form( $login_form_args ), $login_form_args );

		$this->render_links();

		echo '</div>';

		do_action( 'sidebar_login_widget_logged_out_content_end' );
	}

	/**
	 * Render logged out links setting.
	 */
	protected function render_links() {
		do_action( 'sidebar_login_widget_before_logged_out_links' );

		$links = apply_filters(
			'sidebar_login_widget_logged_out_links',
			$this->list_links->parse_setting_value( $this->settings['logged_out_links'] )
		);

		if ( get_option( 'users_can_register' ) && ! empty( $this->settings['show_register_link'] ) ) {
			$links['register'] = array(
				'text' => __( 'Register', 'sidebar-login' ),
				'href' => apply_filters( 'sidebar_login_widget_register_url', wp_registration_url() ),
			);
		}

		if ( ! empty( $this->settings['show_lost_password_link'] ) ) {
			$links['lost_password'] = array(
				'text' => __( 'Lost Password', 'sidebar-login' ),
				'href' => apply_filters( 'sidebar_login_widget_lost_password_url', wp_lostpassword_url() ),
			);
		}

		$this->list_links->render( $links );

		do_action( 'sidebar_login_widget_after_logged_out_links' );
	}
}
