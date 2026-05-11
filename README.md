# TMS Extensions for Polylang

Development monorepo for [TMS Extensions for Polylang](https://wordpress.org/plugins/tms-extensions-polylang): Polylang companion blocks and helpers.

**Requirements:** WordPress **6.0+**, PHP **8.0+**, and **Polylang** active (`Requires Plugins: polylang`).

**Install:** Copy **only** the folder `tms-extensions-polylang/` from this repository into `wp-content/plugins/` (do not place the whole monorepo root there). The folder name under `plugins/` becomes the plugin directory name in WordPress. Activate **TMS Extensions for Polylang** in the admin (Polylang must already be active).

**License:** GPLv2 or later (same as declared in the plugin header and `readme.txt`).

End-user documentation and changelog live in `tms-extensions-polylang/readme.txt` (WordPress.org format).

---

## Development

Monorepo layout: installable plugin under `tms-extensions-polylang/`; block editor/front assets are authored under `_src/` and compiled with **[Prepros](https://prepros.io/)** using `prepros.config` at the repo root (open that folder in Prepros and use watch or manual compile). No Prepros needed to run a release or directory copy—compiled files are already in the plugin tree.