# Nera Competitions Standard 1.3.48

## Feature — Add-on terms (buy each add-on for 1–N years)

A prize can now switch on **Term choice** (Prize Safety & Add-ons → Add-ons tab):
each add-on option is bought for a whole number of years (1 up to a site-wide
**Maximum term**, Theme Settings → WooCommerce → Add-ons Bundles, 10 by default)
instead of always one year. An option costs Price per year × Term; the Full
bundle price is charged per complete set of years, with any extra years beyond
the smallest Term charged at each option's own price. The "Purchased" lock now
lasts the bought number of years from the order's payment date instead of
forever.

- **Catalog prices are now per year.** Existing entries are read as per-year
  prices — admins should re-check what is entered against the new "Price per
  year" label before turning Term choice on for a prize.
- Off by default, per prize: a prize with the switch off behaves exactly as
  before (every option one year, no Years input shown).
- Baskets, checkout and orders show the Term bought and the amount charged;
  admin orders and emails additionally show "Valid until".
- Works the same way in the Lucky Dip dialogs as on the prize page, sharing one
  selection per prize so a Term chosen in one place is never silently reset by
  the other.

See `docs/adr/0015-add-on-terms.md` for the full set of decisions.
