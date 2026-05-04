=== Nera Competitions Standard ===
Contributors: nera
Tags: competition, giveaway, lottery, woocommerce
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later

Premium competition and giveaway theme for WooCommerce, with Tailwind-powered front-end assets.

== Description ==

Nera Competitions Standard is built for competition and lottery sites: product templates, instant-win integrations, and client-ready customization.

== Installation ==

1. Upload the theme folder to `wp-content/themes/`.
2. Activate **Nera Competitions Standard** in **Appearance → Themes**.
3. Run `yarn build` or `npm run build` in `frontend/` and `lty-result-screens/` after pulling updates that change assets.

== Changelog ==

= 1.0.5 =
* WordPress 6.3+ updates: pre-create `upgrade-temp-backup` plugin/theme directories; optional skip of Core’s temp-backup move via `inc/upgrade-temp-backup-helper.php` when `NERA_SKIP_UPGRADE_TEMP_BACKUP`, `WP_ENVIRONMENT_TYPE=local`, or the `nera_skip_upgrade_temp_backup` filter is used (helps Windows/Laragon when updates show “Could not move the old version to the upgrade-temp-backup directory”).

= 1.0.4 =
* Fix `preg_match()` / `preg_replace()` in `nera_cstd_theme_update_json_raw_url()`: use `~` delimiters so `#` inside `[^/#]` is not treated as the end of the pattern (removes “Unknown modifier ']'” warnings).

= 1.0.3 =
* `release.sh`: add `GITHUB_REMOTE` and release commit `git config` (parity with plugin release scripts); document push target.
* Continues PHP `ZipArchive`-first zip and PUC `nera-theme-update.json` sync from `release.sh`.

= 1.0.2 =
* Theme update zip: prefer PHP `ZipArchive` in `release.sh` before `zip.exe` so archive paths use `/` only (fixes “Could not copy file …\\assets\\” on some hosts).
* Exclude `*.bak` from release packages; remove stray theme-root `assets/` backup file.
* Harden `build-wp-release-zip.php` path prefix logic on Windows.

= 1.0.1 =
* Release pipeline test (PUC + GitHub release + zip asset).
* Release zip uses forward-slash paths only (`build-wp-release-zip.php` / `release.sh`), matching **Nera – Instant Win Rules** plugin update tooling (avoids WordPress copy failures from PowerShell `Compress-Archive`).

= 1.0.0 =
* Initial documented release track for GitHub / Plugin Update Checker.
