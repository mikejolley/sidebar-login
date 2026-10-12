<?php
/**
 * Loads plugin functionality.
 *
 * @package MJ/SidebarLogin
 */

namespace MJ\SidebarLogin;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Plugin {
	/**
	 * Main __FILE__ reference.
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin __FILE__ reference.
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->init();
	}

	/**
	 * Initialize class features.
	 */
	protected function init() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_sidebar_login_process', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_sidebar_login_process', array( $this, 'ajax_handler' ) );
	}

	/**
	 * Init localizations.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'sidebar-login', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Register Widget classes with WordPress.
	 */
	public function register_widgets() {
		include dirname( __DIR__ ) . '/class-sidebar-login-widget.php';
		register_widget( '\MJ\SidebarLogin\Widget' );
		register_widget( 'Sidebar_Login_Widget' );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( apply_filters( 'sidebar_login_include_css', true ) ) {
			wp_enqueue_style( 'sidebar-login', plugins_url( 'build/sidebar-login.css', $this->file ), '', filemtime( dirname( __DIR__ ) . '/build/sidebar-login.css' ) );
			wp_style_add_data( 'sidebar-login', 'rtl', 'replace' );
		}

		$asset_path   = dirname( __DIR__ ) . '/build/frontend.asset.php';
		$asset        = require $asset_path;
		$dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : array();
		$version      = ! empty( $asset['version'] ) ? $asset['version'] : filemtime( $asset_path );
		wp_register_script( 'sidebar-login', plugins_url( 'build/frontend.js', $this->file ), $dependencies, $version, true );
		wp_localize_script(
			'sidebar-login',
			'sidebar_login_params',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php', 'relative' ),
				'force_ssl_admin'        => force_ssl_admin() ? 1 : 0,
				'is_ssl'                 => is_ssl() ? 1 : 0,
				'i18n_username_required' => __( 'Please enter your username', 'sidebar-login' ),
				'i18n_password_required' => __( 'Please enter your password', 'sidebar-login' ),
				'error_class'            => apply_filters( 'sidebar_login_widget_error_class', 'sidebar-login-error' ),
			)
		);
	}

	/**
	 * Process the form when using AJAX post.
	 *
	 * Mirrors the login handling in wp-login.php, which the form posts to when JavaScript is unavailable.
	 */
	public function ajax_handler() {
		$credentials           = array(
			'user_login'    => trim( wp_unslash( $this->get_posted_string( 'user_login' ) ) ),
			// Left slashed, as wp-login.php does.
			'user_password' => trim( $this->get_posted_string( 'user_password' ) ),
			'remember'      => sanitize_text_field( wp_unslash( $this->get_posted_string( 'remember' ) ) ),
		);
		$requested_redirect_to = esc_url_raw( trim( wp_unslash( $this->get_posted_string( 'redirect_to' ) ) ) );
		$secure_cookie         = '';

		// If the user wants ssl but the session is not ssl, force a secure cookie.
		if ( ! force_ssl_admin() ) {
			$user = is_email( $credentials['user_login'] ) ? get_user_by( 'email', $credentials['user_login'] ) : get_user_by( 'login', sanitize_user( $credentials['user_login'] ) );

			if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = true;
				force_ssl_admin( true );
			}
		} else {
			$secure_cookie = true;
		}

		$user = wp_signon( $credentials, $secure_cookie );

		/** This filter is documented in wp-login.php */
		$redirect_to = apply_filters( 'login_redirect', $requested_redirect_to, $requested_redirect_to, $user );

		// Only redirect within this site, or to hosts allowed with the allowed_redirect_hosts filter. Otherwise the
		// page reloads.
		$redirect_to = wp_validate_redirect( $redirect_to, '' );

		if ( $secure_cookie && strstr( $redirect_to, 'wp-admin' ) ) {
			$redirect_to = str_replace( 'http:', 'https:', $redirect_to );
		}

		wp_send_json(
			array(
				'success'  => is_wp_error( $user ) ? 0 : 1,
				'redirect' => $redirect_to,
				/** This filter is documented in wp-login.php */
				'error'    => is_wp_error( $user ) ? apply_filters( 'login_errors', $user->get_error_message() ) : '',
			)
		);
	}

	/**
	 * Get a posted login form field. Values that aren't strings, such as arrays, are ignored.
	 *
	 * @param string $key Field name.
	 * @return string Raw, slashed value.
	 */
	private function get_posted_string( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- Login form, like wp-login.php; the caller sanitises.
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? $_POST[ $key ] : '';
	}
}
