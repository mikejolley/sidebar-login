jQuery(function(){

	// Ajax Login
	jQuery('.widget_wp_sidebarlogin form').submit(function(){

		var $thisform = jQuery( this );
		var action    = $thisform.attr('action');

	    jQuery('.sidebar_login_error').remove();

	    // Check required fields as a minimum
	    var user_login = $thisform.find('input[name="log"]').val();
	    var user_password = $thisform.find('input[name="pwd"]').val();

	    if ( ! user_login ) {
	    	$thisform.prepend('<p class="sidebar_login_error">' + sidebar_login_params.i18n_username_required + '</p>');
	    	return false;
	    }
	    if ( ! user_password ) {
	    	$thisform.prepend('<p class="sidebar_login_error">' + sidebar_login_params.i18n_password_required + '</p>');
	    	return false;
	    }

		// Check for SSL/FORCE SSL LOGIN
		if ( action.indexOf( 'https:' ) >= 0 && sidebar_login_params.is_ssl == 0 )
			return true;

		$thisform.block({ message: null, overlayCSS: {
	        backgroundColor: '#fff',
	        opacity:         0.6
	    }});

	    if ( $thisform.find('input[name="rememberme"]:checked' ).size() > 0 ) {
	    	remember = $thisform.find('input[name="rememberme"]:checked').val();
	    } else {
	    	remember = '';
	    }

	    var data = {
			action: 		'sidebar_login_process',
			security: 		sidebar_login_params.login_nonce,
			user_login: 	user_login,
			user_password: 	user_password,
			remember: 		remember,
			redirect_to:	$thisform.find('input[name="redirect_to"]').val()
		};

		// Ajax action
		jQuery.ajax({
			url: sidebar_login_params.ajax_url,
			data: data,
			type: 'POST',
			success: function( result ) {
				if ( result.success == 1 ) {
					window.location = result.redirect;
				} else {
					$thisform.prepend('<p class="sidebar_login_error">' + result.error + '</p>');
					$thisform.unblock();
				}
			}

		});

		return false;
	});

});