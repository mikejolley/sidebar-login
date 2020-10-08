<?php
/**
 * Controls the WordPress widget.
 *
 * @package MJ\SidebarLogin
 */

namespace MJ\SidebarLogin;

defined( 'ABSPATH' ) || exit;

use \WP_Widget;
use MJ\SidebarLogin\Utilities\TemplateTags;
use MJ\SidebarLogin\Utilities\ListLinks;
use MJ\SidebarLogin\Widget\Account;
use MJ\SidebarLogin\Widget\LoginForm;

/**
 * Widget.
 *
 * @extends WP_Widget
 */
class Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'wp_sidebarlogin', __( 'Sidebar Login', 'sidebar-login' ), array( 'description' => __( 'Displays a login form.', 'sidebar-login' ) ) );
	}

	/**
	 * Render the widget on the frontend.
	 *
	 * @param array $args Widget args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		/**
		 * Filter: sidebar_login_widget_display.
		 *
		 * Used to conditionally disable the visibility of the widget.
		 *
		 * @param bool $show The widget will be shown if true.
		 */
		if ( ! apply_filters( 'sidebar_login_widget_display', true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		/**
		 * Action: sidebar_login_widget_end.
		 */
		do_action( 'sidebar_login_widget_start' );

		$user          = is_user_logged_in() ? get_user_by( 'id', get_current_user_id() ) : null;
		$template_tags = new TemplateTags( $user, $instance );
		$list_links    = new ListLinks( $template_tags );

		if ( is_user_logged_in() ) {
			$account = new Account( $template_tags, $list_links, $args, $instance );
			$account->render();
		} else {
			$login_form = new LoginForm( $template_tags, $list_links, $args, $instance );
			$login_form->render();
		}

		/**
		 * Action: sidebar_login_widget_end.
		 */
		do_action( 'sidebar_login_widget_end' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	/**
	 * Renders the settings form when editing the widget.
	 *
	 * @see WP_Widget->form
	 * @param array $instance Widget instance containing settings.
	 */
	public function form( $instance ) {
		$defaults = array(
			'logged_out_title'        => __( 'Login', 'sidebar-login' ),
			'login_redirect_url'      => '',
			'logged_out_links'        => '',
			'show_rememberme'         => '1',
			'show_lost_password_link' => '1',
			'show_register_link'      => '1',
			'logged_in_title'         => __( 'Welcome', 'sidebar-login' ) . ' %username%',
			'logout_redirect_url'     => '',
			'logged_in_links'         => __( "Dashboard | %admin_url%\nProfile | %admin_url%/profile.php\nLogout | %logout_url%", 'sidebar-login' ),
			'show_avatar'             => '1',
		);
		if ( empty( $instance ) ) {
			$instance = $defaults;
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logged_out_title' ) ); ?>"><?php esc_html_e( 'Login Form Title', 'sidebar-login' ); ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'logged_out_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_out_title' ) ); ?>" value="<?php echo esc_attr( $instance['logged_out_title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>"><?php esc_html_e( 'Redirect After Logging In', 'sidebar-login' ); ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>" placeholder="http://" name="<?php echo esc_attr( $this->get_field_name( 'login_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['login_redirect_url'] ); ?>" />
			<br>
			<small><?php esc_html_e( 'Defaults to the current page', 'sidebar-login' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logged_out_links' ) ); ?>"><?php esc_html_e( 'Login Form Additional Links', 'sidebar-login' ); ?>:</label>
			<textarea class="widefat" cols="20" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'logged_out_links' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>"><?php echo esc_textarea( $instance['logged_out_links'] ); ?></textarea>
			<br>
			<small><?php esc_html_e( 'List one per line with the format: Link Text | Link URL', 'sidebar-login' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_rememberme' ) ); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_rememberme' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_rememberme' ) ); ?>" <?php checked( ! empty( $instance['show_rememberme'] ) ); ?> value="1" />
				<?php esc_html_e( 'Show "Remember me" checkbox', 'sidebar-login' ); ?>
			</label>
			<br>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_lost_password_link' ) ); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_lost_password_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_lost_password_link' ) ); ?>" <?php checked( ! empty( $instance['show_lost_password_link'] ) ); ?> value="1" />
				<?php esc_html_e( 'Show lost password link', 'sidebar-login' ); ?>
			</label>
			<br>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_register_link' ) ); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_register_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_register_link' ) ); ?>" <?php checked( ! empty( $instance['show_register_link'] ) ); ?> value="1" />
				<?php
					/* Translators: %s Settings page link. */
					echo wp_kses_post( sprintf( __( 'Show register link (<a href="%s">must be enabled in General Settings</a>)', 'sidebar-login' ), admin_url( 'options-general.php' ) ) );
				?>
			</label>
		</p>
		<h3><?php esc_html_e( 'Logged-in account display', 'sidebar-login' ); ?></h3>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_title' ) ); ?>"><?php esc_html_e( 'Title', 'sidebar-login' ); ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'logged_out_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_title' ) ); ?>" value="<?php echo esc_attr( $instance['logged_in_title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>"><?php esc_html_e( 'Redirect After Logging Out', 'sidebar-login' ); ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>" placeholder="http://" name="<?php echo esc_attr( $this->get_field_name( 'logout_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['logout_redirect_url'] ); ?>" />
			<br>
			<small><?php esc_html_e( 'Defaults to the current page. Must be a page on the current domain.', 'sidebar-login' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_links' ) ); ?>"><?php esc_html_e( 'Additional Links', 'sidebar-login' ); ?>:</label>
			<textarea class="widefat" cols="20" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'logged_in_links' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>"><?php echo esc_textarea( $instance['logged_in_links'] ); ?></textarea>
			<br>
			<small>
			<?php
				/* Translators: %s Link to documentation. */
				echo wp_kses_post( sprintf( __( 'List one per line with the format: Link Text | Link URL | Optional <a href="%s">User Capability</a>', 'sidebar-login' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ) );
			?>
			</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_avatar' ) ); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_avatar' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_avatar' ) ); ?>" <?php checked( ! empty( $instance['show_avatar'] ) ); ?> value="1" />
				<?php esc_html_e( 'Show Avatars', 'sidebar-login' ); ?>
			</label>
		</p>
		<?php
	}
}
