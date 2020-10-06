(function () {
	const callback = () => {
		const selector = '.widget_wp_sidebarlogin form';
		const errorClassName = sidebar_login_params.error_class;
		const forms = document.querySelectorAll(selector);

		const maybeParseJson = function (text) {
			try {
				const json = JSON.parse(text);
				if (json && 'object' === typeof json) {
					return json;
				}
				return {};
			} catch (e) {
				return {};
			}
		};

		const ajaxPost = async function (url, data) {
			return await fetch(url, {
				method: 'POST',
				cache: 'no-cache',
				credentials: 'same-origin',
				body: data,
			})
				.then((response) =>
					response
						.clone()
						.json()
						.catch(() => response.text())
				)
				.then((data) => {
					if ('object' === typeof data) {
						return data;
					} else {
						const maybe_valid_json = data.match(/{"success.*}/);

						if (maybe_valid_json !== null) {
							console.log(
								'Found malformed JSON. Original:' + data
							);
							return maybeParseJson(maybe_valid_json[0]);
						} else {
							console.log('Unable to fix malformed JSON');
						}

						return {};
					}
				});
		};

		const onSubmit = (event) => {
			const form = event.target;
			const addError = (errorText) => {
				form.insertAdjacentHTML(
					'afterbegin',
					'<p class="' + errorClassName + '">' + errorText + '</div>'
				);
			};

			const removeErrors = () => {
				form.querySelectorAll('.' + errorClassName).forEach((notice) =>
					notice.parentNode.removeChild(notice)
				);
			};

			const validate = () => {
				return (
					validateInput(
						'input[name="log"]',
						sidebar_login_params.i18n_username_required
					) &&
					validateInput(
						'input[name="pwd"]',
						sidebar_login_params.i18n_password_required
					)
				);
			};

			const validateInput = (selector, errorText) => {
				const value = form.querySelector(selector).value;

				if (!value) {
					addError(errorText);
					return false;
				}

				return true;
			};

			removeErrors();

			if (!validate()) {
				event.preventDefault();
				return;
			}

			if (
				sidebar_login_params.force_ssl_admin == 1 &&
				sidebar_login_params.is_ssl == 0
			) {
				return; // Prevent same_origin_policy errors.
			}

			event.preventDefault();
			form.classList.add('is-loading');

			const data = new FormData();
			data.append('action', 'sidebar_login_process');
			data.append(
				'user_login',
				form.querySelector('input[name="log"]').value || ''
			);
			data.append(
				'user_password',
				form.querySelector('input[name="pwd"]').value || ''
			);
			data.append(
				'remember',
				form.querySelector('input[name="rememberme"]:checked').value ||
					''
			);
			data.append(
				'redirect_to',
				form.querySelector('input[name="redirect_to"]').value || ''
			);

			ajaxPost(sidebar_login_params.ajax_url, data).then((response) => {
				if (response.success == 1) {
					window.location = response.redirect;
				} else {
					addError(response.error);
					form.classList.remove('is-loading');
				}
			});
		};

		forms.forEach((form) => {
			form.addEventListener('submit', onSubmit);
		});
	};

	if (
		document.readyState === 'complete' ||
		(document.readyState !== 'loading' &&
			!document.documentElement.doScroll)
	) {
		callback();
	} else {
		document.addEventListener('DOMContentLoaded', callback);
	}
})();
