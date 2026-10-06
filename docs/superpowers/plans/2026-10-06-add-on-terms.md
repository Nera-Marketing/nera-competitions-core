# Add-on Terms (buy each add-on for 1–N years) — Implementation Plan

> **For the AI agent picking this up in the `nera` project:** this file is the full brief. Read "Read first", then work through the tasks in order. The product decisions are **locked** (they were agreed with the project owner and the client over a long grilling session); do not reopen them, and do not re-derive the code map: the file/line references below were checked against `main` at v1.3.46. If a reference has drifted, re-grep the function name.

**Goal:** On a prize that has the new **Term choice** switch on, each add-on option takes a whole number of years (1 up to a site-wide **Maximum term**). The customer enters it beside the option's checkbox. An option costs **Price per year × Term**; the **Full bundle** is priced by complete sets of years; the **Purchased** lock lasts the bought number of years. It must carry through basket, checkout and order against the correct draw, exactly like add-ons do today. Not needed for launch; the client asked for a time estimate first (≈ 30–34 h, 4 working days).

**Architecture:** Selection changes from a list of option IDs to a map `option_id → years` (a missing year means 1). All pricing stays on the server in `nera_prize_addons_quote()`, which is the single choke point (9 call sites), so it is changed first and kept backward compatible; the browser only ever sends IDs and whole-number years, which the server clamps. The order snapshot gains `years`; lock expiry and "valid until" are derived from the order date plus `years` at read time (no stored dates, no cron).

**Tech Stack:** PHP 7.4+, WooCommerce, ACF Pro, Alpine.js (prize card) + plain JS (Lucky Dip), Twig (`Components/blocks/PrizeAddOns`), Tailwind v4 via Vite.

---

## Read first

1. `CONTEXT.md` — new terms **Price per year**, **Term**, **Maximum term**; updated **Add-on option**, **Full bundle**, **Purchased**. Use these words in code comments, labels and UI copy; avoid the "_Avoid_" words.
2. `docs/adr/0015-add-on-terms.md` — the decisions and their reasons (written before any code; keep it in step if something changes).
3. `docs/adr/0012-prize-add-on-lines.md` (one cart line per draw, server pricing, order snapshot, Lucky Dip), `0013-…` (site switch), `0014-add-ons-catalog-that-prizes-pick-from.md` (catalog + prize picks).

## Where to work (important)

- Build **here**, in this theme repo (`Nera-Marketing/nera-competitions-core`, local copy `D:\laragon\www\nera\wp-content\themes\nera-competitions-standard`, site `nera.test`). Branch from `main` (v1.3.46): `feature/add-on-terms`.
- The `marine` site (`D:\laragon\www\marine`) and the live Marine Draw site only **consume** this theme through its auto-update. **Never edit the parent copy inside `marine`**: it is overwritten within hours. Roll out after release (see "Release and rollout").
- `CONTEXT.md` and `docs/adr/0015-add-on-terms.md` were created uncommitted in this working tree. Commit them first, on their own, before the code. (The ADR is 0015, not 0014: 0014 was already taken by the catalog ADR.)
- The repo has **no automated test suite**. Verify with `wp eval-file` scripts against `nera.test` and by hand (checklist below).
- Match surrounding style: 2-space indent, `nera_prize_addons_*` prefix, PHPDoc blocks, text domain `nera-competitions`, labels through `nera_prize_addons_label()`.

## Decisions (locked)

| Topic | Decision |
|---|---|
| Unit | Catalog price = **Price per year**. Admin labels say "per year". Existing catalog entries are read as per-year prices. |
| Term | Whole number, `1`…**Maximum term**, per option, entered in a number field beside the checkbox. |
| Maximum term | Site setting, Theme Settings → WooCommerce → Add-ons Bundles. Field shows **10 as a placeholder**; empty means 10. Minimum 1. |
| Switch | Per prize, in the Prize Safety & Add-ons panel, **off by default**. Off ⇒ every option is 1 year and no Term field is shown. |
| Mixed terms | Allowed (one option 1 year, another 2…). |
| Full bundle | Bundle price is **per year**. When **every** option is selected and the customer owns none: `sets = min(years)`, `total = sets × bundle + Σ price_i × (years_i − sets)`. Otherwise `total = Σ price_i × years_i`. |
| Purchased lock | Per account + draw + option, whatever the Term. Lasts `years` from when the order succeeded (`date_paid`, else `date_created`); after that the option can be bought again. No top-up of years while the lock lasts. Orders without `years` are read as 1 year. |
| Lucky Dip | Same Term field in the Lucky Dip copies, **sharing one selection** with the main card so neither silently resets the other's Term. |
| Display | Basket/checkout/order show "× N years" and the charged amount; admin order and emails show **"Valid until"**. |
| Switch turned off / max lowered while carts hold longer terms | Reprice: clamp to the valid range and tell the customer with the existing "no longer available" style notice. Placed orders keep what was paid. |

## Contract changes (names to use)

- **Request:** keep `nera_addons_submitted` and `nera_addon_ids[]`; add `nera_addon_years[<option_id>]=<int>` (only meaningful for ids present in `nera_addon_ids[]`). Same on the Lucky Dip requests and the `nera_prize_addons_save` AJAX.
- **Cart item data** `nera_prize_addon`: `{ draw_id, option_ids, option_years }`, where `option_years` is `id → int` and a missing id means 1.
- **`nera_prize_addons_config($draw_id)`** adds `terms_enabled: bool`, `max_term: int`.
- **`nera_prize_addons_quote($draw_id, $selection, $purchased_ids)`**: `$selection` is either the old `string[]` of ids (each 1 year) or a map `id → years`. Keep the existing return keys (`options`, `dropped`, `subtotal`, `total`, `full_bundle`); each entry in `options` gains `years` and `charged`; add `bundle_sets` (int) and `extra_years` (`id → int`).
- **Order item meta:** `_nera_addon_options` entries gain `years` and `charged`; add `_nera_addon_bundle_sets`. Keep `_nera_addon_full_bundle`. Add the new keys to `nera_prize_addons_hidden_order_itemmeta()`.
- **Labels** (add to `nera_prize_addons_label()`): `per_year` ("/ year"), `years` ("Years"), `valid_until` ("Valid until"), `bundle_sets` (e.g. "%1$d bundle set(s) + %2$d extra year(s)"), `meta_term`.

## Code map (checked against v1.3.46)

| Where | What | Task |
|---|---|---|
| `inc/prize-addons-settings.php` ~line 388 (catalog `Price` field) and `nera_prize_addons_settings_fields()` ~203 | label "Price per year"; add the Maximum term field | 1 |
| `inc/acf/single-product/acf-prize-addons.php` (bundle field ~142) | Term switch; "per year" on the bundle label | 1 |
| `inc/helpers/prize-addons.php` `nera_prize_addons_config()` ~195, `nera_prize_addons_quote()` ~246 | max term, terms flag, year-set pricing | 1, 2 |
| `inc/helpers/prize-addons.php` `nera_prize_addons_purchased_map()` ~305 | lock expiry | 4 |
| `inc/prize-addons.php`: `sync_from_request` ~439, `sync_from_lucky_dip` ~466, `ajax_save_selection` ~496, `set_cart_selection` ~535, `merge_duplicate_lines` ~582, `set_prices` ~692, `check_cart_items` ~731 | carry/validate years in the basket | 3 |
| `inc/prize-addons.php`: `cart_item_data` ~967, `create_order_line_item` ~1060, `formatted_meta` ~1096, `hidden_order_itemmeta` ~1140; `template-parts/cart/cart-item-addon.php` | display + order snapshot | 3, 4 |
| `Components/blocks/PrizeAddOns/index.php` (quote at ~116), `template.twig`; `template-parts/single-product/purchase-card-body-inner.php`; `frontend/assets/js/alpine-prize-addons.js` | prize page UI | 5 |
| `template-parts/single-product/lucky-dip-addons.php`; `frontend/assets/js/lucky-dip-addons.js`; `frontend/assets/js/lucky-dip-limit.js`; **`functions.php` ~2413–2431** (`nera_add_to_cart_limit_preview` builds its own quote from posted ids) | Lucky Dip + spending-limit preview | 6 |
| `inc/prize-addons.php` ~99–149 (bundle/price validators) | **no change**: "bundle below total" now means below the per-year total | — |
| `inc/prize-addons-migration.php` | **not related** | — |

## Test table for the pricing function

Catalog (per year): Storage 24, Servicing 16, Marine bundle 12, RYA training 24, Fuel money 24; per-year total 100; bundle 80.

| Selection | Expected total |
|---|---|
| all five, 1 year each | 80.00 (full bundle, 1 set) |
| all five, 2 years each | 160.00 (2 sets) |
| all five; Servicing 2 years, rest 1 | 96.00 (1 set + 1 extra year of Servicing) |
| all five; Storage 2 years, rest 1 | 104.00 |
| Storage only, 2 years | 48.00 |
| four of five, 1 year each (no Fuel money) | 76.00 (no bundle) |
| all five, switch **off**, years posted as 3 | 80.00 (years clamped to 1) |
| all five, switch on, Maximum term 10, years 11 | clamped to 10 |
| all five, but one already purchased | no bundle; the other four at their own price × years |

---

## Tasks

### Task 0: Branch and docs
- [ ] `git switch -c feature/add-on-terms`
- [ ] Commit `CONTEXT.md` and `docs/adr/0015-add-on-terms.md` and this plan together as the first commit ("Document add-on terms").

### Task 1: Settings, switch and `config` (≈ 2 h)
**Files:** `inc/prize-addons-settings.php`, `inc/acf/single-product/acf-prize-addons.php`, `inc/helpers/prize-addons.php`
- [ ] Relabel the catalog price field "Price per year" with an instruction saying every price is for one year.
- [ ] Add the **Maximum term** number field (min 1, step 1, `placeholder` 10) to the Add-ons Bundles settings section; add `nera_prize_addons_max_term(): int` (empty or below 1 ⇒ 10).
- [ ] Add the per-prize switch (true/false, UI, default 0) named `addons_terms_enabled` to the Add-ons tab, shown only while Add-ons are enabled; relabel the bundle price "Full bundle price (per year)".
- [ ] `nera_prize_addons_config()` returns `terms_enabled` and `max_term` (cached like the rest).

### Task 2: Pricing in `quote()` (≈ 3 h) — do this before anything else that uses it
**File:** `inc/helpers/prize-addons.php`
- [ ] Accept `string[]` (old) or `id → years`; normalise to a map; clamp each year to `1…max_term`, or to 1 when `terms_enabled` is false.
- [ ] Implement the formula in "Decisions". Keep the admin's option order and the existing `dropped` handling (`unavailable`, `purchased`).
- [ ] Return the extended structure from "Contract changes". Round to 2 decimals.
- [ ] Verify with a `wp eval-file` script that prints the whole test table above and fails loudly on a mismatch. **Do not move on until every row matches.**

### Task 3: Basket (≈ 4 h)
**File:** `inc/prize-addons.php`, `template-parts/cart/cart-item-addon.php`
- [ ] A small helper to read `nera_addon_years` (sanitised ints) next to `nera_addon_ids`; use it in `sync_from_request` and `ajax_save_selection` (the latter also returns the saved years).
- [ ] `set_cart_selection($draw_id, $ids, $years = [])` stores `option_ids` and `option_years`; unchanged callers still work (empty years ⇒ 1).
- [ ] `merge_duplicate_lines`: union of options; for the same option keep the **larger** years.
- [ ] `set_prices` and `check_cart_items`: pass the stored years to `quote()`; if the stored years fall outside `1…max_term` or the switch is off, clamp and add the notice.
- [ ] Show "× N years" in `cart_item_data` and `cart-item-addon.php`.

### Task 4: Order and the lock (≈ 3 h)
**Files:** `inc/prize-addons.php`, `inc/helpers/prize-addons.php`
- [ ] `create_order_line_item`: write `years` and `charged` per option and `_nera_addon_bundle_sets`; hide the new keys in `hidden_order_itemmeta`.
- [ ] `purchased_map`: for each add-on line compute `start = date_paid ?: date_created`; skip the option when `start + years` (years from the snapshot, default 1) is in the past. Keep the existing "refunded in full does not count" rule and the paid-status list. The per-user static cache must not hide expiry within one request (compute against `time()` once per call).
- [ ] `formatted_meta`: show years per option and **Valid until** (date only), in admin, emails and My Account.

### Task 5: Prize page UI (≈ 5 h)
**Files:** `Components/blocks/PrizeAddOns/index.php`, `template.twig`, `template-parts/single-product/purchase-card-body-inner.php`, `frontend/assets/js/alpine-prize-addons.js`
- [ ] Pass `terms_enabled`, `max_term`, per-year price labels and the new i18n strings to the block config.
- [ ] Per option, when `terms_enabled`: `[checkbox] Title … £24 / year` plus a number input "Years" (min 1, max `max_term`, step 1, default 1, enabled only while ticked, with an accessible label), and the option's amount (£48) beside it. Name the input `nera_addon_years[<id>]` and make sure the purchase card submits it with `nera_addon_ids[]`.
- [ ] Alpine: `selected` becomes `id → years`; clamp on input; `subtotal()` and `total()` use the same formula as the server (display only); a line such as "1 bundle set + 1 extra year of Servicing" when the bundle applies with extra years. Switch off ⇒ behave exactly as today.
- [ ] `cd frontend && yarn build` (the repo tracks `yarn.lock`; `package-lock.json` was removed in v1.3.46: do not recreate or commit it).

### Task 6: Lucky Dip (≈ 6–8 h)
**Files:** `template-parts/single-product/lucky-dip-addons.php`, `lucky-dip-addons.js`, `lucky-dip-limit.js`, `functions.php` (~2413–2431)
- [ ] Add the same Years input to the Lucky Dip copies; keep one shared selection (`id → years`) for the prize across every copy and the main card.
- [ ] Send `nera_addon_years[...]` with `lty_process_lucky_dip`, `lty_regenerate_lucky_dip_add_to_cart` and `nera_prize_addons_save`.
- [ ] `nera_add_to_cart_limit_preview` (functions.php): read the posted years and pass them to `quote()` so the Spending Limit projection includes terms. **Easy to forget; it silently under-counts otherwise.**

### Task 7: QA (≈ 5–6 h)
- [ ] The pricing table (Task 2) again after all tasks.
- [ ] Guest adds add-ons with terms, then signs in (basket merge keeps the larger term).
- [ ] Coupons still discount tickets only; the Spending Limit plugin counts the term-priced add-on line.
- [ ] Refunded in full ⇒ option unlocked; partial refund behaviour unchanged.
- [ ] Lock expiry: create an order whose `date_paid` is older than `years`, confirm the option unlocks; and one still inside its term stays locked. An order with no `years` in its snapshot behaves as 1 year.
- [ ] Turn the prize switch off, then lower the Maximum term, while a basket holds longer terms: clamped with a notice, no error at checkout.
- [ ] Old basket/order data (no `option_years`/`years`) still reads as 1 year; prizes with the switch off look and behave exactly as before.
- [ ] Lucky Dip: "add directly" then tick in the popup; regenerate; a Term set on the card survives a popup save and vice versa.
- [ ] Mobile layout, keyboard use of the number input, light and dark (the Marine child theme has dark mode).
- [ ] Order screen, customer email and My Account show years, charged amounts and "Valid until".

### Task 8: Docs and release (≈ 1.5 h)
- [ ] Update `docs/adr/0015-add-on-terms.md` if anything changed during the build.
- [ ] Add `CHANGELOG-1.3.47.md` in the style of the existing ones; mention the catalog price is now per year (admins should re-check entered prices).
- [ ] Follow the repo's release convention (see the "Release v1.3.46" commit): bump `Version` in `style.css`, `NERA_VERSION` in `functions.php`, `readme.txt` stable tag, and `nera-theme-update.json`; the zip is published as a GitHub release (`nera-competitions-standard-<version>.zip`). Confirm the exact steps with the repo owner before tagging.

## Release and rollout to `marine` and live

1. Release v1.3.47 from this repo.
2. `marine` (local) updates the parent theme from the release (or by hand: replace the parent folder with the release zip), then run the Marine child theme checks: `the-marine-draw` has no override of the add-on templates or styles today, so the work is a visual check of the new Years input, mainly in dark mode.
3. Live: back up, update the parent theme to 1.3.47, clear Batcache/CDN, then in wp-admin: set the **Maximum term** (or leave empty for 10), review the catalog prices (now per year), and switch **Term choice** on for the prizes that should offer it.

## Out of scope

- Adding years to an option that is still locked (no top-up).
- Starting the term at handover to the winner (not recorded on the site).
- Auto-expiry notices or emails.
