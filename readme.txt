=== Plugin Name ===
Contributors: mikejolley
Donate link: http://mikejolley.com/projects/sidebar-login-for-wordpress/
Tags: login, sidebar, widget, sidebar login, meta, form, register
Requires at least: 2.8
Tested up to: 3.3
Stable tag: 2.3.4

Easily add an ajax-enhanced login widget to your site's sidebar.

== Description ==

Sidebar-Login has both a widget and a template tag to allow you to have a login form in the sidebar of your wordpress powered blog.

It lets users login, and then redirects them back to the page they logged in from rather than the backend, it also shows error messages.

You can configure the plugin in <code>Admin > Settings > Sidebar Login</code> after installing it.

If you'd like to contribute to the plugin you can find it on GitHub: https://github.com/mikejolley/sidebar-login.

== Localization ==

Added localizations are listed below. If you want to contribute or improve a localisation, please contribute via GitHub (https://github.com/mikejolley/sidebar-login).

*	Catalan Translation by Marc Vinyals
*	French Translation by Andy
*	Estonian Translation by Marko Punnar
*	Dutch Translation by Ruben Janssen
*	German Translation by GhostLyrics
*	Italian Translation by Alessandro Spadavecchia
*	Hungarian translation by Laszlo Dvornik
*	Hungarian (2) translation by Balint Vereskuti
*	Russian translation by Fat Cow
*	Romanian translation by Victor Osorhan
*	Spanish translation by Tribak
*	Spanish (2) translation by Ricardo Vilella
*	Danish translation by Per Bovbjerg
*	Portuguese translation by Alvaro Becker
*	Polish translation by merito
*	Polish (2) translation by Darek Wapinski
*	Icelandic translation by Hákon Ásgeirsson
*	Arabic translation by khalid
*	Turkish translation by Muzo B
*	Chinese translation by seven
*	Persian by Gonahkar
*	Persian (farsi, alt) translation Amir Beitollahi
*	Russian translation by Vorotnikov Boris
*	Croatian translation by Zarko Pintar
*	Indonesian translation by Masino Sinaga 
*	Indonesian (2) translation by Hendry Lee
*	Lithuanian translation by Justas Kalinauskas
*	Hebrew translation by Yosi
*	Latvian translation by Reinis
*	Hindi translation by Outshine Solutions
*	Bulgarian translation by Siteground
*	Greek translation by Meet-sos

Note: Those with more than one translation are found in langs/alternate/. To use the alternatives move them from /alternate/ into /langs/.

== Installation ==

= First time installation instructions =

   1. Unzip and upload the php file to your wordpress plugin directory
   2. Activate the plugin
   3. For a sidebar widget: Goto the design > widgets tab - Drag the widget into a sidebar and save!
   4. To use the template tag: Add &lt;?php sidebarlogin(); ?&gt; to your template.
   
= Configuration =

You will find a config page in tools/settings > Sidebar Login. Here you can set links and redirects up.

== Screenshots ==

1. Login Form
2. After Login

== Changelog ==

= 2.3.4 =
*	SSL URL tweak
*	Better handling for force_ssl_login and force_ssl_admin

= 2.3.3 =
*	Removed a link after request from WordPress.org staff
*	wp_lostpassword_url() for lost password link
*	sanitized user_login
*	Uses wp_ajax for ajax login instead of init functions
* 	Secure cookie logic change

= 2.3.2 = 
*	Login redirect fix

= 2.3.1 = 
*	Error loop fix
*	Added filter for errors - sidebar_login_error

= 2.3 =
*	Put the project on GitHub
*	Added new localisations
*	New options panel
* 	AJAX Login

= 2.2.15 =
*	FORCE_SSL_LOGIN/ADMIN double login issue fix (Thanks to bmaupin)
*	Only added openid styling if other plugin is installed
*	Added more languages

= 2.2.14 =
*	Further revised the |true / |user_capability code - only need to use one or the other now.

= 2.2.13 =
*	Updated translations
*	Support for https and style.css
*	is_date fix
*	Added option for headings
*	Removed attribute_escape for esc_attr - therefore this version needs wp 2.8 and above
*	USER LEVEL option gone - replaced with USER CAPABILITY instead - use a capability like 'manage_options'

= 2.2.12 =
*	Headers sent bugs fixed
*	Avatar display option

= 2.2.11 =
*	More/Updated langs

= 2.2.10 =
*	Moved settings to appearance menu
*	Changed min user level to capilbilty 'manage_options'
*	Fixed menu showing in wordpress 3.0
*	Added %USERID% for links
*	Fixed white space bug for link options

= 2.2.8 =
*	Min level setting for links. Add user level after |true when defining the logged in links.
*	Moved 'settings' from tools to settings.
*	Encoded ampersand for valid markup
*	Moved Labels about
*	Fixed SSL url
*	Reusable widget

= 2.2.6 =
*	Added changelog to readme.
*	OpenID Plugin (http://wordpress.org/extend/plugins/openid/) Integration.
*	%username% can be used in your custom links shown when logged in (gets replaced with username)
*	WP-FacebookConnect (http://wordpress.org/extend/plugins/wp-facebookconnect/) integration (untested!)
*	Minor fixes (worked through a big list of em!)
