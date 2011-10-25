jQuery(function(){

	// Ajax Login
	jQuery('.widget_wp_sidebarlogin form').submit(function(){
		
		var thisform = this;
		
		jQuery(thisform).block({ message: null, overlayCSS: { 
	        backgroundColor: '#fff', 
	        opacity:         0.6 
	    } });
	    
	    var data = {
			action: 		'sidebar_login_process',
			security: 		sidebar_login_params.login_nonce,
			user_login: 	jQuery('input[name="log"]', thisform).val(),
			user_password: 	jQuery('input[name="pwd"]', thisform).val(),
			remember: 		jQuery('input[name="rememberme"]', thisform).val(),
			redirect_to:	jQuery('.redirect_to:eq(0)', thisform).val()
		};
		
		// Ajax action
		jQuery.post( sidebar_login_params.ajax_url, data, function(response) {
			jQuery('.login_error').remove();
			
			result = jQuery.parseJSON( response );
			
			if (result.success==1) {
				window.location = result.redirect;
			} else {
				jQuery(thisform).prepend('<p class="login_error">' + result.error + '</p>');
				jQuery(thisform).unblock();
			}
		});
		
		return false;
	});
	
});