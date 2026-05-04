=== Nera Competitions Standard ===
Contributors: nera
Tags: competition, giveaway, lottery, woocommerce
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later

Premium competition and giveaway theme for WooCommerce, with Tailwind-powered front-end assets.

== Description ==

Nera Competitions Standard is built for competition and lottery sites: product templates, instant-win integrations, and client-ready customization.

== Installation ==

1. Upload the theme folder to `wp-content/themes/`.
2. Activate **Nera Competitions Standard** in **Appearance → Themes**.
3. Run `yarn build` or `npm run build` in `frontend/` and `lty-result-screens/` after pulling updates that change assets.

== Changelog ==

= 1.0.2 =
* Theme update zip: prefer PHP `ZipArchive` in `release.sh` before `zip.exe` so archive paths use `/` only (fixes “Could not copy file …\\assets\\” on some hosts).
* Exclude `*.bak` from release packages; remove stray theme-root `assets/` backup file.
* Harden `build-wp-release-zip.php` path prefix logic on Windows.

= 1.0.1 =
* Release pipeline test (PUC + GitHub release + zip asset).
* Release zip uses forward-slash paths only (`build-wp-release-zip.php` / `release.sh`), matching **Nera – Instant Win Rules** plugin update tooling (avoids WordPress copy failures from PowerShell `Compress-Archive`).

= 1.0.0 =
* Initial documented release track for GitHub / Plugin Update Checker.
