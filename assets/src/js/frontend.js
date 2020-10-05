(function () {
	var callback = function () {
		var forms = document.querySelectorAll('.widget_wp_sidebarlogin form');

		forms.forEach((form) => {
			form.addEventListener('submit', function (e) {
				if (!validateForm(form)) {
					e.preventDefault();
				}

				// Prevent same_origin_policy errors.
				if (
					sidebar_login_params.force_ssl_admin == 1 &&
					sidebar_login_params.is_ssl == 0
				) {
					return;
				}

				e.preventDefault();
				submitForm(form);
			});
		});

		var validateForm = function (form) {
			formRemoveNotices(form, sidebar_login_params.error_class);

			var logInput = form.querySelector('input[name="log"]');
			var pwdInput = form.querySelector('input[name="pwd"]');

			if (!logInput.value) {
				formShowNotice(
					form,
					sidebar_login_params.error_class,
					sidebar_login_params.i18n_username_required
				);
				return false;
			}

			if (!pwdInput.value) {
				formShowNotice(
					form,
					sidebar_login_params.error_class,
					sidebar_login_params.i18n_password_required
				);
				return false;
			}

			return true;
		};

		var formRemoveNotices = function (form, noticeClass) {
			var notices = form.querySelectorAll('.' + noticeClass);
			notices.forEach((notice) => {
				notice.parentNode.removeChild(notice);
			});
		};

		var formShowNotice = function (form, noticeClass, noticeContent) {
			form.insertAdjacentHTML(
				'afterbegin',
				'<p class="' + noticeClass + '">' + noticeContent + '</div>'
			);
		};

		var submitForm = function (form) {
			form.classList.add('is-loading');

			var data = new FormData();
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
					formShowNotice(
						form,
						sidebar_login_params.error_class,
						response.error
					);
					form.classList.remove('is-loading');
				}
			});

			return false;
		};

		var isValidJSON = function (maybeJson) {
			try {
				var json = JSON.parse(maybeJson);
				return json && 'object' === typeof json;
			} catch (e) {
				return false;
			}
		};

		var ajaxPost = async function (url, data) {
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
						// Attempt to fix the malformed JSON
						var maybe_valid_json = data.match(/{"success.*}/);

						if (
							maybe_valid_json !== null &&
							isValidJSON(maybe_valid_json[0])
						) {
							console.log(
								'Fixed malformed JSON. Original:' + data
							);
							return JSON.parse(maybe_valid_json[0]);
						} else {
							console.log('Unable to fix malformed JSON');
						}

						return {};
					}
				});
		};
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
