=== Clarify Password Reset ===
Contributors: fliz, kona
Tags: passwords, password reset, lost your password, user creation, new users, suggest a password, clarifypasswordreset, clarify password reset, save password, store password, saved password, stored password, password bug, password error
Requires at least: 4.3
Tested up to: 5.6
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AS OF WP 5.7, THIS PLUGIN IS NO LONGER NEEDED OR SUPPORTED.
Clears initial suggested password on WP 4.3+ password reset page, and adds a Generate Password button. Fixes new-password save bug (Firefox/Chrome).

== Description ==

PLEASE NOTE: AS OF WP 5.7, THIS PLUGIN IS NO LONGER SUPPORTED.

Since WordPress 4.3, new users are no longer sent their passwords directly by email.  Instead, they are sent a message containing a link to the Reset Password page, where they can choose their own password.  Additionally, the Reset Password page now appears with a strong password already suggested in the "New password" field.

This plugin **removes** the suggested password from the Reset Password page, leaving the "New password" field empty.  Below this field it **adds** a "Suggest a password" button whose action is to fetch a new suggested password via Ajax.

Also since 4.3, Firefox and Chrome users may notice problems saving their new password in their browser from the Password Reset page.  (Firefox saves the password with an empty username; Chrome sets the username and password to the password value.) This plugin tweaks the password reset form so that these browsers can successfully save the username and newly-reset password if the user wishes.

Note that Internet Explorer, Edge and Safari will not offer to save the password from the Password Reset page, and this plugin does not change that behaviour.  Therefore, the plugin adds a configurable warning message to the Password Reset screen, advising users to make a note of their new password.

The form tweaking for Firefox/Chrome and the configurable warning message are enabled by default, but they can be disabled in the administration settings.

== Installation ==

1. Install this plugin via the WordPress plugin control panel, 
or by manually downloading it and uploading the extracted folder 
to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the administration page (reached via Settings / Clarify Password
Reset in the main admin menu).  From there you can configure the optional
password warning message, and switch plugin features off or on.  All features
are enabled by default.

== Frequently Asked Questions ==

= Does this plugin force users to choose strong passwords? =

No. Other plugins can be used for that.

= Suggesting a strong password is a good "nudge" - why would you remove it?  We need to educate users about strong passwords. =

We found that the sort of users requiring "nudging" were also 
the most likely to be confused rather than educated by the nudge.

Users can still get a strong password suggestion using the 
"Suggest a password" button.

= But really, why would you remove the suggested strong password? = 

Unfortunately, in our experience, a significant minority of users 
are confused by the suggested password on the Reset Password page.  

They assume that their password has *already* been reset, and that 
their new password is shown in the box.  Rather than clicking 
"Reset Password", they copy the suggested password, go back to 
the login page and try to log in with it.  Naturally this doesn't work, 
which leads to frustration and excessive helpdesk requests.

This plugin attempts to make the password process more intuitive 
for non-technical users, so less helpdesk support is required - 
and so users who don't read instructions can still get it right.

= Where do the suggested passwords come from? =

The plugin uses the standard, pluggable `wp_generate_password()` 
method to generate suggested passwords (using default parameters).

== Screenshots ==

1. This screenshot shows the reformatted the Reset Password page, 
with no auto-suggested password, the new "Suggest a password" button
and the optional "Please make a note" warning.

2. This screenshot shows the plugin admin page.

== Changelog ==

= 1.2 =
* Updated JS to accommodate changes to front-end login form in WP 5.3 

= 1.1.3 =
* Dutch translation added - many thanks to Johan van der Wijk.

= 1.1.2 =
* Bugfix to strip unwanted sanitization slashes when displaying custom warning text

= 1.1.1 =
* Tweak to default settings handling for upgraded plugin

= 1.1 =
* Added optional form tweak for fixing save-password bug in Firefox and Chrome
* Added optional, configurable warning message advising users to take note of their new password
* Added back-end admin page for enabling / disabling these features

= 1.0.1 =
* Tweak to README.txt

= 1.0.0 =
* Initial version

== Upgrade Notice ==
= 1.1 =
This version helps Firefox and Chrome save new passwords without error.

= 1.0.0 =
Initial version, released for WordPress 4.3+.
