=== Crumbler – Cookie Consent ===
Contributors: compresso
Tags: cookie consent, gdpr, cookie banner, consent management, google consent mode
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your website to the Crumbler cookie consent service: consent banner, automatic script & iframe blocking and Google Consent Mode v2.

== Description ==

Crumbler is a hosted cookie consent service. This free, open-source plugin is the WordPress connector for it: enter your Site Key, enable the widget, done. The plugin provides:

* Cookie consent banner with configurable design
* Automatic script blocking before consent
* Automatic iframe blocking (e.g. YouTube, Google Maps)
* Cookie cleanup for non-accepted categories
* Google Consent Mode v2 support
* Cookie declaration (Gutenberg block and shortcode) with all detected services and cookies
* Multi-language support (DE, FR, IT, EN)

= Free plugin, paid service (no trialware) =

The plugin itself is free and completely open source — none of its functionality is locked, time-limited or restricted to a paid tier. The cookie consent **service** behind it (operated by Compresso AG) requires a Crumbler account and subscription; a free trial is available. See **External services** below for exactly what data is exchanged.

= How it works =

1. Create an account at [crumbler.ch](https://crumbler.ch)
2. Add your domain and copy the Site Key
3. Install and activate this plugin
4. Enter the Site Key under Settings > Crumbler
5. Enable the widget — done!

The settings screen shows a live connection status, so you can see right away whether your domain is set up and active in Crumbler.

== Installation ==

1. Upload the `crumbler-cookie-consent` folder to `/wp-content/plugins/`, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to Settings > Crumbler.
4. Enter your Site Key and enable the widget.

== External services ==

This plugin connects your website to **Crumbler**, a hosted cookie consent service operated by Compresso AG. The plugin is a free, open-source client; the service requires a Crumbler account and subscription (a free trial is available). The plugin contains no locked or paid functionality.

The plugin does not contact the service until you enter a Site Key and enable the widget. It communicates with the Crumbler service (cmp.compresso.ch) in three ways:

1. **Consent widget script.** On every front-end page (once enabled and a Site Key is set) the plugin loads the consent widget from `https://cmp.compresso.ch/widget/cmp.min.js`. The widget renders the consent banner, blocks third-party scripts and iframes before consent, applies Google Consent Mode v2, and records the visitor's anonymised consent decision with the service. Data sent: your Site Key and the visitor's consent interaction.

2. **Cookie declaration data.** When the cookie declaration block or shortcode is displayed, the visitor's browser requests the list of detected services and cookies from `https://cmp.compresso.ch/api/public/cookies`. Data sent: your Site Key and the requested language.

3. **Connection status check.** On the plugin's settings screen (administrators only), the plugin requests `https://cmp.compresso.ch/api/public/config` to show whether your domain is set up and active. Data sent: your Site Key and your site's domain. The result is cached temporarily.

No data is sent to any party other than the Crumbler service.

* Service provider: Compresso AG
* Terms of Service: https://crumbler.ch/agb.php
* Privacy Policy: https://crumbler.ch/datenschutz.php

== Frequently Asked Questions ==

= Do I need a Crumbler account? =

Yes. The plugin is the connector to the Crumbler service; the consent banner, blocking and cookie declaration are provided by that service. You can start with a free trial at [crumbler.ch](https://crumbler.ch).

= Where do I find the Site Key? =

In your Crumbler dashboard, under the respective site. It is a UUID in the format `af232e06-59c2-4810-b09d-7a2b25632d1b`.

= Do I need to manually tag scripts? =

No. The widget automatically detects third-party scripts based on provider patterns from the Crumbler database. You can also tag scripts manually:

`<script type="text/plain" data-cmp-category="analytics" data-cmp-src="https://example.com/script.js"></script>`

= Does the plugin support Google Consent Mode v2? =

Yes. The widget sets `gtag('consent', 'default', {...})` and updates the consent status after the user's decision.

= How do I embed the cookie declaration? =

Two options:

1. **Gutenberg block**: add the "Cookie Declaration" block. The language can optionally be overridden in the block settings.
2. **Shortcode**: use `[crumbler_cookies]` on any page or post, optionally with a language parameter: `[crumbler_cookies lang="fr"]`.

= Can I hide the widget for administrators? =

Yes. Under Settings > Crumbler there is an option to hide the widget for logged-in administrators.

== Credits ==

This plugin bundles the **Press Start 2P** font by Cody Boisclair, licensed under the SIL Open Font License 1.1. The full license is included at `assets/fonts/Press-Start-2P-OFL.txt`.

== Screenshots ==

1. The Crumbler settings page: enter your Site Key and see the live connection status.
2. The "Cookie Declaration" block in the block editor.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Settings page with Site Key, language and advanced options.
* Live connection status check against the Crumbler service.
* Automatic widget integration on the front end.
* Cookie declaration as Gutenberg block (block.json) and shortcode.
* Multi-language support (DE, FR, IT, EN).
