<?php
/*
Plugin Name: Sidebar Login
Plugin URI: http://wordpress.org/extend/plugins/sidebar-login/
Description: Allows you to easily add an ajax-enhanced login widget to the sidebar on your WordPress site.
Version: 2.7.3
Author: Mike Jolley
Author URI: http://mikejolley.com
Requires at least: 3.5
Tested up to: 4.4
Text Domain: sidebar-login
Domain Path: /languages/

	Copyright: 2015 Mike Jolley.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Sidebar_Login class.
 */
class Sidebar_Login {

	private $version = '2.7.3';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Hook-in
		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'wp_authenticate', array( $this, 'convert_email_to_username' ), 10, 2 );

		// Ajax events
		add_action( 'wp_ajax_sidebar_login_process', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_sidebar_login_process', array( $this, 'ajax_handler' ) );
	}

	/**
	 * i18n function.
	 *
	 * @access public
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'sidebar-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * enqueue function.
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue() {
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$js_in_footer = apply_filters( 'sidebar_login_js_in_footer', false );

		// Register BLOCK UI
		wp_register_script( 'jquery-blockui', plugins_url( 'assets/js/jquery.blockUI.min.js', __FILE__ ), array( 'jquery' ), '2.70', $js_in_footer );

		// Enqueue Sidebar Login JS
		wp_enqueue_script( 'sidebar-login', plugins_url( 'assets/js/sidebar-login' . $suffix . '.js', __FILE__ ), array( 'jquery', 'jquery-blockui' ), $this->version, $js_in_footer );

		// Enqueue Styles
		if ( apply_filters( 'sidebar_login_include_css', true ) ) {
			wp_enqueue_style( 'sidebar-login', plugins_url( 'assets/css/sidebar-login.css', __FILE__ ), '', $this->version );
		}

		// Pass variables
		$sidebar_login_params = array(
			'ajax_url'         => $this->ajax_url(),
			'force_ssl_admin'  => force_ssl_admin() ? 1 : 0,
			'is_ssl'           => is_ssl() ? 1 : 0,
			'i18n_username_required' => __( 'Please enter your username', 'sidebar-login' ),
			'i18n_password_required' => __( 'Please enter your password', 'sidebar-login' ),
			'error_class'      => apply_filters( 'sidebar_login_widget_error_class', 'sidebar_login_error' )
		);

		wp_localize_script( 'sidebar-login', 'sidebar_login_params', $sidebar_login_params );
	}

	/**
	 * Include and register the widget class.
	 *
	 * @access public
	 * @return void
	 */
	public function register_widget() {
		include_once( 'includes/class-sidebar-login-widget.php' );
	}

	/**
	 * ajax_url function.
	 *
	 * @access public
	 * @return void
	 */
	private function ajax_url() {
		if ( is_ssl() ) {
			return str_replace( 'http:', 'https:', admin_url( 'admin-ajax.php' ) );
		} else {
			return str_replace( 'https:', 'http:', admin_url( 'admin-ajax.php' ) );
		}
	}

	/**
	 * When posting an email, convert to a username
	 */
	public function convert_email_to_username( &$username, &$password ) {
		// If the user inputs an email address instead of a username, try to convert it
		if ( is_email( $username ) ) {
			if ( $user = get_user_by( 'email', $username ) ) {
				$username = $user->user_login;
			}
		}
	}

	/**
	 * ajax_handler function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_handler() {
		// Get post data
		$creds                  = array();
		$creds['user_login']    = stripslashes( trim( $_POST['user_login'] ) );
		$creds['user_password'] = stripslashes( trim( $_POST['user_password'] ) );
		$creds['remember']      = isset( $_POST['remember'] ) ? sanitize_text_field( $_POST['remember'] ) : '';
		$redirect_to            = esc_url_raw( $_POST['redirect_to'] );
		$secure_cookie          = null;

		// If the user wants ssl but the session is not ssl, force a secure cookie.
		if ( ! force_ssl_admin() ) {
			$user = is_email( $creds['user_login'] ) ? get_user_by( 'email', $creds['user_login'] ) : get_user_by( 'login', sanitize_user( $creds['user_login'] ) );

			if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = true;
				force_ssl_admin( true );
			}
		}

		if ( force_ssl_admin() ) {
			$secure_cookie = true;
		}

		if ( is_null( $secure_cookie ) && force_ssl_admin() ) {
			$secure_cookie = false;
		}

		// Login
		$user = wp_signon( $creds, $secure_cookie );

		// Redirect filter
		if ( $secure_cookie && strstr( $redirect_to, 'wp-admin' ) ) {
			$redirect_to = str_replace( 'http:', 'https:', $redirect_to );
		}

		// Result
		$result = array();

		if ( ! is_wp_error( $user ) ) {
			$result['success']  = 1;
			$result['redirect'] = $redirect_to;
		} else {
			$result['success'] = 0;
			if ( $user->errors ) {
				foreach ( $user->errors as $error ) {
					$result['error'] = $error[0];
					break;
				}
			} else {
				$result['error'] = __( 'Please enter your username and password to login.', 'sidebar-login' );
			}
		}

		echo '<!--SBL-->';
		echo json_encode( $result );
		echo '<!--SBL_END-->';

		die();
	}

}

new Sidebar_Login();
