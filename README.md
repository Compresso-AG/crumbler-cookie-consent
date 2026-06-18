# Crumbler – Cookie Consent

WordPress plugin that connects your website to the **[Crumbler](https://crumbler.ch)** cookie consent service. It loads the consent widget, blocks third-party scripts and iframes before consent, supports Google Consent Mode v2, and renders a cookie declaration via a Gutenberg block or shortcode.

> **Serviceware, not trialware.** Crumbler is a hosted service (SaaS) operated by Compresso AG. A Crumbler account and a site key are required for the widget to work. The **plugin itself is free and fully open source** (GPL-2.0-or-later); the paid functionality lives entirely in the service.

## Features

- Cookie consent banner with configurable design (served by the Crumbler widget)
- Automatic script & iframe blocking before consent
- Cookie cleanup for non-accepted categories
- Google Consent Mode v2 support
- Cookie declaration as Gutenberg block (`block.json`) and shortcode `[crumbler_cookies]`
- Multi-language: DE, FR, IT, EN

## Requirements

- WordPress 5.0+ (tested up to 7.0)
- PHP 7.4+
- A Crumbler account → [crumbler.ch](https://crumbler.ch)

## Local development

This repository contains **only the plugin** — it maps 1:1 to `wp-content/plugins/crumbler-cookie-consent`. For local testing it is bind-mounted into a separate DDEV WordPress install that lives **outside** this repo (e.g. `~/Sites/crumbler-wp`), so no WordPress core ever lands in the public repository.

```
~/Sites/crumbler-cookie-consent   # this repo (the plugin)
~/Sites/crumbler-wp               # DDEV WordPress (de_CH), plugin mounted in
```

## Contributing

- Work on feature branches, open a Pull Request against `main`.
- Keep `main` releasable at all times; tagged releases are deployed to the WordPress.org SVN repository.
- Follow the [WordPress Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

## License

[GPL-2.0-or-later](LICENSE) © Compresso AG
