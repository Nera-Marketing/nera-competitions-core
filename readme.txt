=== Nera Competitions Standard ===
Contributors: nera
Tags: competition, giveaway, lottery, woocommerce
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.0.18
License: GPLv2 or later

Premium competition and giveaway theme for WooCommerce, with Tailwind-powered front-end assets.

== Description ==

Nera Competitions Standard is built for competition and lottery sites: product templates, instant-win integrations, and client-ready customization.

== Installation ==

1. Upload the theme folder to `wp-content/themes/`.
2. Activate **Nera Competitions Standard** in **Appearance → Themes**.
3. Run `yarn build` or `npm run build` in `frontend/` and `lty-result-screens/` after pulling updates that change assets.

== Changelog ==

= 1.0.18 =
* Fix — Pin `timber/timber` to `^2.3.3 <2.4` for PHP 8.1 hosts (Timber 2.4+ requires PHP 8.2); Composer platform set to 8.1.10 so `vendor/` loads without platform_check fatal on Laragon and similar stacks.
* Updated: `readme.txt` Requires PHP aligned with `style.css` (8.1).

= 1.0.17 =
* Updated: Nera Marketing attribution page — template and ACF field refactor (`page-templates/nera-marketing-attribution.php`, `inc/acf/attribution/acf-attribution.php`).
* Fixed: GitHub release delivery — publishes the theme zip for Plugin Update Checker (v1.0.16 tag had no release asset).

= 1.0.15 =
* Updated: Single-product mobile layout — `woocommerce/single-product.php`, `woocommerce/single-product/competitions.php`, tabs and purchase-card partials.
* Updated: Product breadcrumb — flex-wrap, shop page title from WooCommerce settings, layout cleanup in `template-parts/single-product/breadcrumb.php`.
* Updated: Homepage categories bar spacing/behavior in `template-parts/homepage/categories-competitions.php`.
* Enhanced: My Account order view — first 10 ticket badges, expandable "+N more tickets", top-aligned line items, `break-all` on billing email in `woocommerce/myaccount/view-order.php`.
* Updated: Minor CSS in `frontend/src/main.css` (production build via release).

= 1.0.14 =
* Updated: Sticky site header gains `py-2` vertical padding for better spacing.
* Updated: Single-product breadcrumb nav uses `rounded-lg` (was `rounded-full`) and is wrapped in a responsive `px-4 lg:px-0` container for consistent edge padding.

= 1.0.13 =
* Fixed: Competition/product listing grids use a single column on mobile (`grid-cols-1`), two columns from `md`, three from `lg` — replaces cramped two-column mobile layout.
* Updated: Homepage categories filter, entry list listing grid, closed prizes grid, and dynamic winners grid.

= 1.0.12 =
* New: **Winners Entry List** page template (`page-templates/winners-entry-list-template.php`) — dynamic winners from Lottery logs with stacked cards and **View Participants** opening the entry-list modal (REST `nera/v1`) without leaving the page.
* New: Shared entry-list UI partials — `template-parts/entry-list/entry-list-grid-alpine.php` and `participants-modal.php`; `listing-grid.php` refactored to use them.
* Enhanced: `winners-dynamic/winner-card.php` and `winners-grid.php` — opt-in `show_participants_cta`, `stack_layout`, and entry-list modal; AJAX load-more preserves layout/CTA flags.
* Removed: Demo instant-winner admin helper (`inc/demo-instant-winner.php`) and related notice styles.
* `page.php`: drop default `prose` wrapper on page content.

= 1.0.11 =
* `functions.php`: stricter Q&A / competition question validation on add-to-cart.

= 1.0.10 =
* New: Spin-to-Win Prizes section on single product pages — a Vue.js-powered collapsible
  prize list rendered only for products in the `spin-to-win` product category.
* New: `inc/spin-to-win-prizes-section.php` — `nera_competitions_render_spin_to_win_prizes_section()`
  helper; renders the section via a template part, suppressed when empty.
* New: `template-parts/single-product/spin-to-win-prizes-section.php` — collapsible panel with
  toggle button (hidden by default); Vue app is lazy-mounted on first expand.
* New: `frontend/spin-to-win-prizes-vue-init.js` — dedicated Vue entry point for the widget
  (`showStats: false`, `showRemainingBadge: true`, `showWinners: false`).
* `functions.php`: enqueue `nera-spin-to-win-prizes-vue` (with Vue vendor chunk) on
  spin-to-win category product pages; extend `type="module"` filter for the new handle.
* `frontend/vite.config.js`: register `spin-to-win-prizes-vue` as a Vite build entry.
* `InstantWinsContainer.vue`: add `endpoint`, `emptyMessage`, `showStats`, `showRemainingBadge`,
  and `showWinners` props; aggregate "X / Y prizes remaining" badge with skeleton shimmer.
* `PrizeCard.vue`: add `showWinners` prop; per-prize amber progress bar; "All Won" badge;
  "X / Y To Be Won" stock label; card header becomes interactive only when winners are shown.
* `woocommerce/single-product.php` + `competitions.php`: call the spin-to-win render function
  below the instant-win prizes section.

= 1.0.9 =
* WooCommerce My Account: restyle login form and edit-address form for consistent spacing and token-based colors.
* page.php: add dynamic page-template body class and FAQ section layout improvements.
* Frontend: Tailwind/main.css refinements (card padding, form field focus states, responsive tweaks).

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
