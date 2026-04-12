=== TMS Extensions for Polylang ===
Contributors: entumas
Tags: multilingual, polylang, gutenberg, sitemap
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extend Polylang with multilingual Gutenberg blocks.


== Description ==

Extends Polylang with reusable multilingual tools for block-based sites.

**Included features:**
- Language Switcher component for Polylang, available as:
  - Gutenberg block: `tepll/pll-language-switcher`
  - Shortcode: `[tepll-language-switcher]`
  - PHP function: `tepll_language_switcher_get_html()` / `tepll_language_switcher_print_html()`
- Language Visibility component for Polylang, available as:
  - Gutenberg block: `tepll/pll-language-visibility`
  - Shortcode: `[tepll-language-visibility]`
  - PHP function: `tepll_language_visibility_get_html()` / `tepll_language_visibility_print_html()`
- HTML Sitemap for Polylang component, available as:
  - Gutenberg block: `tepll/html-sitemap`
  - Shortcode: `[tepll-html-sitemap]`
  - PHP function: `tepll_html_sitemap_get_html()` / `tepll_html_sitemap_print_html()`

**Dependencies:**
- Polylang (required)


== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Make sure you have **Polylang** active.
3. Activate it from the Plugins section in the WordPress admin.


== Technical Notes ==

This plugin has a hard dependency on Polylang and cannot run without it.


== Changelog ==

= 1.0.0 =
* Added: Component "Language Visibility", available as Gutenberg block, shortcode, and PHP function.
* Added: Component "Language Switcher", available as Gutenberg block, shortcode, and PHP function.
* Added: Component "HTML Sitemap", available as Gutenberg block, shortcode, and PHP function.