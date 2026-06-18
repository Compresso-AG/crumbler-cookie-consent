=== Crumbler - Cookie Consent ===
Contributors: samuelrueegger
Tags: cookie consent, gdpr, dsgvo, cookie banner, consent management
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cookie consent management for your website. Powered by Crumbler.

== Description ==

This plugin integrates the **Compresso Cookie Consent Widget** into your website. It provides:

* Cookie banner with configurable design
* Automatic script blocking before consent
* Automatic iframe blocking (e.g. YouTube, Google Maps)
* Cookie cleanup for non-accepted categories
* Google Consent Mode v2 support
* Multi-language support (DE, FR, IT, EN)

= How it works =

1. Create an account at [cmp.compresso.ch](https://cmp.compresso.ch)
2. Add a new site and copy the Site Key
3. Install and activate this plugin
4. Enter the Site Key under Settings > Crumbler
5. Enable the widget - done!

The widget is automatically added to the `<head>` section of every page and manages cookie consent, script blocking and Google Consent Mode fully automatically.

= Features =

* **Easy setup** - Just enter the Site Key and enable
* **Cookie declaration** - Gutenberg block and shortcode to display all detected services and cookies
* **Automatic language detection** - Or manual language selection (DE/FR/IT/EN)
* **Admin hide** - Optionally hide the widget for logged-in administrators
* **Custom widget URL** - For self-hosted installations

== Installation ==

1. Upload the `crumbler-cookie-consent` plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the "Plugins" menu in the admin area
3. Go to Settings > Crumbler
4. Enter your Site Key and enable the widget

== Frequently Asked Questions ==

= Where do I find the Site Key? =

You can find the Site Key in the [Crumbler Dashboard](https://cmp.compresso.ch) under the respective site. It is a UUID in the format `af232e06-59c2-4810-b09d-7a2b25632d1b`.

= Do I need to manually tag scripts? =

No. The widget automatically detects third-party scripts based on provider patterns from the CMP database. You can also manually tag scripts:

`<script type="text/plain" data-cmp-category="analytics" data-cmp-src="https://example.com/script.js"></script>`

= Does the plugin support Google Consent Mode v2? =

Yes. The widget automatically sets `gtag('consent', 'default', {...})` and updates the consent status after the user's decision.

= How do I embed the cookie declaration? =

There are two options:

1. **Gutenberg block**: Add the "Cookie Declaration" block via the block editor. Optionally, the language can be overridden in the block settings.
2. **Shortcode**: Use `[crumbler_cookies]` on any page or post. Optionally with a language parameter: `[crumbler_cookies lang="fr"]`

The cookie declaration automatically displays all detected services and cookies, grouped by category (Necessary, Functional, Statistics, Marketing).

= Can I hide the widget for administrators? =

Yes. Under Settings > Crumbler there is an option "Hide for admins" that disables the widget for logged-in administrators.

== Changelog ==

= 1.3.1 =
* Fix: Text Domain corrected (removed restricted term)
* Fix: Widget script loaded via wp_enqueue_script() instead of inline output
* Readme translated to English as required by Plugin Directory
* Tested up to WordPress 6.9

= 1.3.0 =
* Preparation for Plugin Directory
* License header and Text Domain added
* Security: All output escaped with esc_attr/esc_html
* index.php files in all directories

= 1.2.2 =
* Fix: Preserve native fetch against override by third-party scripts
* Fix: Cookie declaration works on pages with custom fetch function

= 1.2.1 =
* Fix: Consent status correctly shows "All accepted"/"All rejected"

= 1.2.0 =
* Introductory text with cookie declaration and legal basis
* Consent status box with domain, status, date and consent ID
* Buttons to change and revoke consent directly in the cookie declaration
* Complete translations for DE, FR, IT and EN

= 1.1.0 =
* Gutenberg block "Cookie Declaration" to display all detected services and cookies
* Shortcode [crumbler_cookies] as fallback for the Classic Editor
* Optional language parameter for block and shortcode
* Data is automatically loaded from the CMP API and grouped by category

= 1.0.0 =
* Initial release
* Settings page with Site Key, language and advanced options
* Automatic script integration in the frontend
* UUID validation for Site Key
* Admin hide option
* Custom widget URL for self-hosted installations
