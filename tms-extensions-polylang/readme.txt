=== TMS Extensions for Polylang ===
Contributors: entumas
Tags: gutenberg, multilingual, navigation, polylang, sitemap
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extend Polylang with multilingual Gutenberg blocks.


== Description ==

Extends Polylang with reusable multilingual tools for block-based sites.

**Included features:**
- Language Switcher Advanced block
- Language Visibility block
- Menu by Language block
- HTML Sitemap for Polylang block

**Bundled translations:**
- Catalan (`ca`)
- English (US) (`en_US`)
- French (France) (`fr_FR`)
- German (`de_DE`)
- Italian (`it_IT`)
- Portuguese (Portugal) (`pt_PT`)
- Spanish (Spain) (`es_ES`)

WordPress loads the matching file when the site language uses the same locale.

**Dependencies:**
- Polylang (required)


== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Make sure you have **Polylang** active.
3. Activate it from the Plugins section in the WordPress admin.


== Changelog ==

= 1.1.0 =
* Added: "Menu by Language" block and inner "Menu by Language Slot" block to map a different Navigation menu per Polylang language.
* Removed: shortcodes; use the Gutenberg blocks or the `tepll_*_get_html()` PHP functions instead.
* Changed: removed PHP helpers that printed markup directly; use the `*_get_html()` functions and output the returned HTML where needed.
* Added: Italian (`it_IT`) and Portuguese (Portugal) (`pt_PT`) language packs.

= 1.0.0 =
* Added: Component "Language Visibility", available as Gutenberg block, shortcode, and PHP function.
* Added: Component "Language Switcher", available as Gutenberg block, shortcode, and PHP function.
* Added: Component "HTML Sitemap", available as Gutenberg block, shortcode, and PHP function.
* Added: Catalan, English (US), German, French (France) and Spanish (Spain) language packs.