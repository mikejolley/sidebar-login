( function () {
	const callback = () => {
		// Added by wp_localize_script() as `sidebar_login_params`; the name is kept for back-compat.
		// eslint-disable-next-line camelcase
		const params = sidebar_login_params;
		const selector = '.widget_wp_sidebarlogin form';
		const errorClassName = params.error_class;
		const forms = document.querySelectorAll( selector );

		const maybeParseJson = function ( text ) {
			try {
				const json = JSON.parse( text );
				if ( json && 'object' === typeof json ) {
					return json;
				}
				return {};
			} catch {
				return {};
			}
		};

		const ajaxPost = async function ( url, data ) {
			return await fetch( url, {
				method: 'POST',
				cache: 'no-cache',
				credentials: 'same-origin',
				body: data,
			} )
				.then( ( response ) =>
					response
						.clone()
						.json()
						.catch( () => response.text() )
				)
				.then( ( body ) => {
					if ( 'object' === typeof body ) {
						return body;
					}

					// Other plugins or PHP notices can print output before the JSON response.
					const maybeValidJson = body.match( /{"success.*}/ );

					if ( maybeValidJson !== null ) {
						// eslint-disable-next-line no-console
						console.log( 'Found malformed JSON. Original:' + body );
						return maybeParseJson( maybeValidJson[ 0 ] );
					}

					// eslint-disable-next-line no-console
					console.log( 'Unable to fix malformed JSON' );
					return {};
				} );
		};

		const onSubmit = ( event ) => {
			const form = event.target;
			const addError = ( errorText ) => {
				form.insertAdjacentHTML(
					'afterbegin',
					'<p class="' + errorClassName + '">' + errorText + '</div>'
				);
			};

			const removeErrors = () => {
				form.querySelectorAll( '.' + errorClassName ).forEach(
					( notice ) => notice.parentNode.removeChild( notice )
				);
			};

			const validate = () => {
				return (
					validateInput(
						'input[name="log"]',
						params.i18n_username_required
					) &&
					validateInput(
						'input[name="pwd"]',
						params.i18n_password_required
					)
				);
			};

			const validateInput = ( inputSelector, errorText ) => {
				const value = form.querySelector( inputSelector ).value;

				if ( ! value ) {
					addError( errorText );
					return false;
				}

				return true;
			};

			removeErrors();

			if ( ! validate() ) {
				event.preventDefault();
				return;
			}

			if (
				Number( params.force_ssl_admin ) === 1 &&
				Number( params.is_ssl ) === 0
			) {
				return; // Prevent same_origin_policy errors.
			}

			event.preventDefault();
			form.classList.add( 'is-loading' );

			const data = new FormData();
			data.append( 'action', 'sidebar_login_process' );
			data.append(
				'user_login',
				form.querySelector( 'input[name="log"]' )?.value || ''
			);
			data.append(
				'user_password',
				form.querySelector( 'input[name="pwd"]' )?.value || ''
			);
			data.append(
				'remember',
				form.querySelector( 'input[name="rememberme"]:checked' )
					?.value || ''
			);
			data.append(
				'redirect_to',
				form.querySelector( 'input[name="redirect_to"]' )?.value || ''
			);

			ajaxPost( params.ajax_url, data ).then( ( response ) => {
				if ( Number( response.success ) === 1 ) {
					if ( response.redirect ) {
						window.location.replace( response.redirect );
					} else {
						window.location.reload();
					}
				} else {
					addError( response.error );
					form.classList.remove( 'is-loading' );
				}
			} );
		};

		forms.forEach( ( form ) => {
			form.addEventListener( 'submit', onSubmit );
		} );
	};

	if (
		document.readyState === 'complete' ||
		( document.readyState !== 'loading' &&
			! document.documentElement.doScroll )
	) {
		callback();
	} else {
		document.addEventListener( 'DOMContentLoaded', callback );
	}
} )();
