jQuery(function(){

	// Ajax Login
	jQuery('.widget_wp_sidebarlogin form').submit(function(){
		var thisform = this;
		jQuery(thisform).block({ message: null, overlayCSS: { 
	        backgroundColor: '#fff', 
	        opacity:         0.6 
	    } });
		jQuery.ajax({
			type: 'POST',
			url: jQuery(thisform).attr('action'),
			data: jQuery(thisform).serialize(),
			success: function( result ) {
				jQuery('.login_error').remove();
				result = jQuery.trim( result );
				if (result=='SUCCESS') {
					window.location = jQuery(thisform).attr('action');
				} else {
					jQuery(thisform).prepend('<p class="login_error">' + result + '</p>');
					jQuery(thisform).unblock();
				}
			}
		});
		return false;
	});
	
});