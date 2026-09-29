# Gate Safety & Add-ons behind a site switch and seed prizes from copied global defaults

Not every site sells prize add-ons, and most prizes on a site that does still won't. Theme Settings → WooCommerce → **Add-ons Bundles** therefore holds a site-wide switch (**off by default**) and a **Global default** for Safety and for Add-ons, so an admin types a common list once instead of on every prize. Implemented in `inc/prize-addons-settings.php`; the same field builder produces the prize box and the settings section.

## Why a copy, not a live link

A prize with no list of its own could read the Global default at display time. We copy instead: opening a prize whose Safety/Add-ons list is still empty fills its form from the Global default, and Update saves it as the prize's own data.

- A live link would let one edit of the Global default reprice every prize using it, including baskets already open. Option IDs (the "Purchased" lock, ADR 0012) would then depend on a shared list, so deleting a global option would unlock it on every prize at once.
- Copying keeps each prize self-contained, which is what ADR 0012's order snapshots already assume.
- The cost: editing the Global default later does not update prizes that already saved. Accepted; the client asked for a starting point that a prize can change, not a shared list.

## Consequences

- **Switch off = inert to customers, not destructive.** `nera_prize_addons_config()` and `nera_prize_safety_config()` report "disabled", so the prize page shows nothing, the cart check removes add-on lines with the existing "no longer available" notice, and new add-ons are ignored. Saved prize data, placed orders, their display and the auto-complete exception for add-on lines are untouched. The prize edit box is hidden.
- **Fill happens only in the admin form**, via `acf/prepare_field`, never in `load_value`: `get_field()` on the storefront must not fall back to the Global default.
- **Trigger is "the prize has saved no rows for this section".** Clearing every row and saving therefore fills the form again on the next visit; with no rows nothing is sold anyway.
- **Filled option rows get fresh IDs on save** (the copy leaves `option_id` empty), so prizes never share IDs with each other or with the Global default.
- **Global default is validated like a prize** (price above 0, bundle needs two options and must be below their total), so a copy is always valid.
- **No "reset to defaults" button.** Re-copying over a prize that has sold options would give new IDs and lift the "Purchased" lock, the same hazard as deleting an option by hand (ADM-09), so it is not offered.
- **Currency in admin price fields comes from WooCommerce**, not a literal symbol.
