# Keep Safety items and Add-on options in one Catalog that prizes pick from

Supersedes the copy-once part of [ADR 0013](0013-add-ons-site-switch-and-copied-global-defaults.md); its site switch stays.

Every Safety item and Add-on option is now defined once, in Theme Settings > WooCommerce > **Add-ons Bundles** (the **Catalog**). A prize's edit page no longer creates or edits items: it searches the Catalog and adds entries (drag to reorder). The reason is control: the site owner wants wording and prices decided in one place, not retyped and drifting prize by prize.

What a prize still sets for itself: the Show Safety / Show Add-ons switches, an optional title and description (empty falls back to the default text in settings), the entries it picked, and the **Full bundle price**.

## Why a live link now, when ADR 0013 chose a copy

ADR 0013 copied a "Global default" into a prize once, so a later edit could never reprice prizes already set up. That only made sense while a prize could change its copy. With nothing editable on the prize, a copy would just be a second, drifting version of the Catalog. So a prize stores the IDs it picked and reads the Catalog when it is shown: editing an entry changes it on every prize using it, open baskets included. Placed orders are unaffected, as before: they keep the snapshot of what was paid (ADR 0012).

## Consequences

- **Stable IDs are the contract.** Each entry has a hidden ID generated on save (`si_…` for Safety, `ao_…` for Add-on options); a prize's picks (`safety_item_ids`, `addon_option_ids`) and the "Purchased" lock refer to it, so renaming or reordering never breaks either. The order on the prize page is the order the admin picked.
- **A used entry cannot be deleted.** Saving the settings is refused with the names of the prizes using it and the address of each edit page. ACF escapes error text, so the addresses arrive as text and `assets/js/admin-prize-addons.js` turns them into links. To stop selling an entry everywhere, remove it from those prizes first.
- **Full bundle price stays per prize** (a fixed price cannot be shared by prizes with different options). The field is locked until an option is picked and capped at their total; the server checks it on save. Customers are offered it only when the prize has two or more options and the price is **below** their total, so a price equal to the total is accepted but never shown as a saving.
- **A price change can end a bundle offer.** If the Catalog prices drop so that a prize's Full bundle price is no longer below the total, the offer disappears for that prize until the price is adjusted.
- **A stale pick is skipped**, not shown broken (the Catalog refuses deletions of used entries, so this is a safety net).
- **No prefill, no copy.** The admin-form prefill from the Global default is removed; the fields it used (`psa_default_*`) are the Catalog and the default texts.
- **Moving existing data (one time, `inc/prize-addons-migration.php`).** Each prize's own rows go into the Catalog and the prize picks them. Add-on options keep their ID and are not merged (two prizes that each had a "Storage" for £24 leave two Catalog entries to tidy by hand), because merging would change an ID and lift the "Purchased" lock for someone who already bought it. Safety items have no lock, so identical ones are shared. The old rows are left in the database untouched, and the prize's own title, description and Full bundle price carry over unchanged.
- **Prizes that had no text of their own now show the default text.** Before, an empty prize description meant no description; now it means "use the default", so where the settings hold a default description it appears on those prizes.
