jQuery(function(){

	// Ajax Login
	jQuery('.widget_wp_sidebarlogin form').submit(function(){
		
		var thisform = this;
		
		jQuery(thisform).block({ message: null, overlayCSS: { 
	        backgroundColor: '#fff', 
	        opacity:         0.6 
	    } });
	    
	    if ( jQuery('input[name="rememberme"]:checked', thisform ).size() > 0 ) {
	    	remember = jQuery('input[name="rememberme"]:checked', thisform ).val();
	    } else {
	    	remember = '';
	    }

	    var data = {
			action: 		'sidebar_login_process',
			security: 		sidebar_login_params.login_nonce,
			user_login: 	jQuery('input[name="log"]', thisform).val(),
			user_password: 	jQuery('input[name="pwd"]', thisform).val(),
			remember: 		remember,
			redirect_to:	jQuery('input[name="redirect_to"]', thisform).val()
		};
		
		// Ajax action
		jQuery.ajax({
			url: sidebar_login_params.ajax_url,
			data: data,
			type: 'GET',
			dataType: 'jsonp',
			success: function( result ) {
				jQuery('.login_error').remove();
				
				if (result.success==1) {
					window.location = result.redirect;
				} else {
					jQuery(thisform).prepend('<p class="login_error">' + result.error + '</p>');
					jQuery(thisform).unblock();
				}
			}
			
		});			
		
		return false;
	});
	
});