/**
 * External dependencies
 */
const { execFileSync } = require( 'child_process' );

/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Run a WP-CLI command in the wp-env CLI container.
 *
 * @param {string} command WP-CLI command, without the leading `wp`.
 */
const wpCli = ( command ) =>
	execFileSync(
		'npx',
		[ 'wp-env', 'run', 'cli', 'bash', '-c', `wp ${ command }` ],
		{
			stdio: 'pipe',
		}
	);

const USER = {
	username: 'widgetuser',
	email: 'widgetuser@example.com',
	password: 'widget-password',
	firstName: 'Widget',
	lastName: 'User',
};

test.describe( 'Sidebar Login widget', () => {
	let previousTheme;

	test.beforeAll( async ( { requestUtils } ) => {
		[ previousTheme ] = await requestUtils.rest( {
			path: '/wp/v2/themes',
			params: { status: 'active' },
		} );
		await requestUtils.activatePlugin( 'sidebar-login' );
		await requestUtils.deleteAllUsers();
		await requestUtils.createUser( USER );
		// The widget needs a classic theme with a widget area. WP-CLI is used because the REST widgets endpoint moves
		// newly created legacy widgets to the inactive widgets when it re-checks the sidebars in the same request.
		wpCli( 'theme activate twentytwentyone' );
		wpCli( 'widget reset sidebar-1' );
		// With no settings saved, the widget uses its defaults.
		wpCli( 'widget add wp_sidebarlogin sidebar-1' );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		wpCli( 'widget reset sidebar-1' );
		await requestUtils.deleteAllUsers();
		wpCli( `theme activate ${ previousTheme.stylesheet }` );
	} );

	test.describe( 'logged out', () => {
		test.use( { storageState: {} } );

		test( 'validates the form before sending it', async ( { page } ) => {
			await page.goto( '/' );
			const widget = page.locator( '.widget_wp_sidebarlogin' );
			await expect(
				widget.getByRole( 'heading', { name: 'Login' } )
			).toBeVisible();

			await widget.getByRole( 'button', { name: 'Log In' } ).click();
			await expect( widget.locator( '.sidebar-login-error' ) ).toHaveText(
				'Please enter your username'
			);

			await widget.getByLabel( 'Username' ).fill( USER.username );
			await widget.getByRole( 'button', { name: 'Log In' } ).click();
			await expect( widget.locator( '.sidebar-login-error' ) ).toHaveText(
				'Please enter your password'
			);
		} );

		test( 'shows the login error without leaving the page', async ( {
			page,
		} ) => {
			await page.goto( '/' );
			const widget = page.locator( '.widget_wp_sidebarlogin' );

			await widget.getByLabel( 'Username' ).fill( USER.username );
			await widget.getByLabel( 'Password' ).fill( 'not-the-password' );
			const response = page.waitForResponse( /admin-ajax\.php/ );
			await widget.getByRole( 'button', { name: 'Log In' } ).click();
			await response;

			await expect(
				widget.locator( '.sidebar-login-error' )
			).toContainText( 'The password you entered for the username' );
			await expect( widget.locator( 'form' ) ).not.toHaveClass(
				/is-loading/
			);
			await expect( page ).toHaveURL( /\/$/ );
		} );

		test( 'logs in with AJAX and returns to the page', async ( {
			page,
		} ) => {
			await page.goto( '/' );
			const widget = page.locator( '.widget_wp_sidebarlogin' );

			await widget.getByLabel( 'Username' ).fill( USER.username );
			await widget.getByLabel( 'Password' ).fill( USER.password );
			const response = page.waitForResponse( /admin-ajax\.php/ );
			await widget.getByRole( 'button', { name: 'Log In' } ).click();
			expect( ( await response ).ok() ).toBe( true );
			// The whole form is posted, so fields added by other plugins reach their checks.
			expect( ( await response ).request().postData() ).toContain(
				'name="log"'
			);

			await expect( page ).toHaveURL( /[?&]_login=[0-9a-f]{10}/ );
			await expect(
				widget.getByRole( 'heading', { name: 'Welcome Widget User' } )
			).toBeVisible();
			await expect(
				widget.getByRole( 'link', { name: 'Logout' } )
			).toBeVisible();
		} );

		test( 'falls back to wp-login.php when the AJAX response is unusable', async ( {
			page,
		} ) => {
			// For example, a two-factor plugin that prints its own form.
			await page.route( /admin-ajax\.php/, ( route ) =>
				route.fulfill( {
					contentType: 'text/html',
					body: '<p>Enter your code</p>',
				} )
			);
			await page.goto( '/' );
			const widget = page.locator( '.widget_wp_sidebarlogin' );

			await widget.getByLabel( 'Username' ).fill( USER.username );
			await widget.getByLabel( 'Password' ).fill( USER.password );
			await widget.getByRole( 'button', { name: 'Log In' } ).click();

			await expect( page ).toHaveURL( /[?&]_login=[0-9a-f]{10}/ );
			await expect(
				widget.getByRole( 'heading', { name: 'Welcome Widget User' } )
			).toBeVisible();
		} );

		test.describe( 'without JavaScript', () => {
			test.use( { javaScriptEnabled: false } );

			test( 'logs in through wp-login.php and returns to the page', async ( {
				page,
			} ) => {
				await page.goto( '/' );
				const widget = page.locator( '.widget_wp_sidebarlogin' );

				await widget.getByLabel( 'Username' ).fill( USER.username );
				await widget.getByLabel( 'Password' ).fill( USER.password );
				await widget.getByRole( 'button', { name: 'Log In' } ).click();

				await expect( page ).toHaveURL( /[?&]_login=[0-9a-f]{10}/ );
				await expect(
					widget.getByRole( 'heading', {
						name: 'Welcome Widget User',
					} )
				).toBeVisible();
			} );
		} );
	} );

	test.describe( 'logged in', () => {
		test( 'shows the account links and logs out', async ( { page } ) => {
			await page.goto( '/' );
			const widget = page.locator( '.widget_wp_sidebarlogin' );

			await expect(
				widget.getByRole( 'heading', { name: 'Welcome Admin' } )
			).toBeVisible();
			await expect(
				widget.locator( '.sidebar-login-account__avatar img' )
			).toBeVisible();
			await expect(
				widget.getByRole( 'link', { name: 'Dashboard' } )
			).toHaveAttribute( 'href', /\/wp-admin\/$/ );

			await widget.getByRole( 'link', { name: 'Logout' } ).click();

			await expect( page ).toHaveURL( /\/$/ );
			await expect(
				page
					.locator( '.widget_wp_sidebarlogin' )
					.getByRole( 'heading', { name: 'Login' } )
			).toBeVisible();
		} );
	} );
} );
