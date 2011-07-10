<?php

add_action( 'admin_init', 'sidebar_login_options_init' );
add_action( 'admin_menu', 'sidebar_login_options_add_page' );

/**
 * Define Options
 */
global $sidebar_login_options;

$sidebar_login_options = (
	array( 
		array(
			'', 
			array(
				array(
					'name' 		=> 'sidebarlogin_heading', 
					'std' 		=> __('Login', 'sblogin'), 
					'label' 	=> __('Logged out heading', 'sblogin'),  
					'desc'		=> __('Heading for the widget when the user is logged out.', 'sblogin')
				),
				array(
					'name' 		=> 'sidebarlogin_welcome_heading', 
					'std' 		=> __('Welcome %username%', 'sblogin'), 
					'label' 	=> __('Logged in heading', 'sblogin'),  
					'desc'		=> __('Heading for the widget when the user is logged in.', 'sblogin')
				),
			)
		),
		array(
			__('Redirects', 'sblogin'), 
			array(
				array(
					'name' 		=> 'sidebarlogin_login_redirect', 
					'std' 		=> '', 
					'label' 	=> __('Login redirect', 'sblogin'),  
					'desc'		=> __('Url to redirect the user to after login. Leave blank to use the current page.', 'sblogin'),
					'placeholder' => 'http://'
				),
				array(
					'name' 		=> 'sidebarlogin_logout_redirect', 
					'std' 		=> '', 
					'label' 	=> __('Logout redirect', 'sblogin'),  
					'desc'		=> __('Url to redirect the user to after logout. Leave blank to use the current page.', 'sblogin'),
					'placeholder' => 'http://'
				),
			)
		),
		array(
			__('Links', 'sblogin'), 
			array(
				array(
					'name' 		=> 'sidebarlogin_register_link', 
					'std' 		=> '1', 
					'label' 	=> __('Show Register Link', 'sblogin'),  
					'desc'		=> sprintf( __('The <a href="%s" target="_blank">\'Anyone can register\'</a> setting must be turned on for this option to work.', 'sblogin'), admin_url('options-general.php')),
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'sidebarlogin_forgotton_link', 
					'std' 		=> '1', 
					'label' 	=> __('Show Lost Password Link', 'sblogin'),  
					'desc'		=> '',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'sidebar_login_avatar', 
					'std' 		=> '1', 
					'label' 	=> __('Show Logged in Avatar', 'sblogin'),  
					'desc'		=> '',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'sidebarlogin_logged_in_links', 
					'std' 		=> "<a href=\"".get_bloginfo('wpurl')."/wp-admin/\">".__('Dashboard','sblogin')."</a>\n<a href=\"".get_bloginfo('wpurl')."/wp-admin/profile.php\">".__('Profile','sblogin')."</a>", 
					'label' 	=> __('Logged in links', 'sblogin'),  
					'desc'		=> sprintf( __('One link per line. Note: Logout link will always show regardless. Tip: Add <code>|true</code> after a link to only show it to admin users or alternatively use a <code>|user_capability</code> and the link will only be shown to users with that capability (see <a href=\'http://codex.wordpress.org/Roles_and_Capabilities\' target=\'_blank\'>Roles and Capabilities</a>).<br/> You can also type <code>%%USERNAME%%</code> and <code>%%USERID%%</code> which will be replaced by the user\'s info. Default: <br/>&lt;a href="%s/wp-admin/"&gt;Dashboard&lt;/a&gt;<br/>&lt;a href="%s/wp-admin/profile.php"&gt;Profile&lt;/a&gt;', 'sblogin'), get_bloginfo('wpurl'), get_bloginfo('wpurl') ),
					'type' 		=> 'textarea'
				),
			)
		)
	)
);
	
/**
 * Init plugin options to white list our options
 */
function sidebar_login_options_init() {

	global $sidebar_login_options;

	foreach($sidebar_login_options as $section) {
		foreach($section[1] as $option) {
			if (isset($option['std'])) add_option($option['name'], $option['std']);
			register_setting( 'sidebar-login', $option['name'] );
		}
	}

	
}

/**
 * Load up the menu page
 */
function sidebar_login_options_add_page() {
	add_options_page(__('Sidebar Login','sblogin'), __('Sidebar Login','sblogin'), 'manage_options', 'sidebar-login', 'sidebar_login_options');
}

/**
 * Create the options page
 */
function sidebar_login_options() {
	global $sidebar_login_options;

	if ( ! isset( $_REQUEST['settings-updated'] ) ) $_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" .__( 'Sidebar Login Options','sblogin') . "</h2>"; ?>
		
		<form method="post" action="options.php">
		
			<?php settings_fields( 'sidebar-login' ); ?>
	
			<?php
			foreach($sidebar_login_options as $section) {
			
				if ($section[0]) echo '<h3 class="title">'.$section[0].'</h3>';
				
				echo '<table class="form-table">';
				
				foreach($section[1] as $option) {
					
					echo '<tr valign="top"><th scope="row">'.$option['label'].'</th><td>';
					
					if (!isset($option['type'])) $option['type'] = '';
					
					switch ($option['type']) {
						
						case "checkbox" :
						
							$value = get_option($option['name']);
							
							?><input id="<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> /><?php
						
						break;
						case "textarea" :
							
							$value = get_option($option['name']);
							
							?><textarea id="<?php echo $option['name']; ?>" class="large-text" cols="50" rows="10" name="<?php echo $option['name']; ?>" placeholder="<?php if (isset($option['placeholder'])) echo $option['placeholder']; ?>"><?php echo esc_textarea( $value ); ?></textarea><?php
						
						break;
						default :
							
							$value = get_option($option['name']);
							
							?><input id="<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" placeholder="<?php if (isset($option['placeholder'])) echo $option['placeholder']; ?>" /><?php
						
						break;
						
					}
					
					if ($option['desc']) echo '<span class="description">'.$option['desc'].'</span>';
					
					echo '</td></tr>';
				}
				
				echo '</table>';
				
			}
			?>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'sblogin'); ?>" />
			</p>
		</form>
	</div>
	<?php
}