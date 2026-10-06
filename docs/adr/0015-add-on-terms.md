# Let customers buy each add-on for several years, priced per year

A prize can switch on a **Term** choice. Each add-on option then takes a whole number of years, from 1 up to a site-wide **Maximum term** (set in the Add-ons settings; the field shows 10 as a placeholder and left empty it means 10), and the customer enters it beside the option's checkbox. The catalog price becomes a **Price per year**, and an option costs Price per year × Term. A prize without the switch behaves exactly as before (every option is one year).

## Decisions

- **Terms may be mixed.** One option for 1 year and another for 2 is allowed.
- **Full bundle is priced by complete sets of years.** When every option is chosen (and the customer owns none), the number of sets is the smallest Term; each set costs the Full bundle price (also per year), and every year beyond the sets costs that option's own Price per year. 1, 2, 1 years across three options is one set plus one extra year of the second; 2, 2, 2 is two sets. We rejected "bundle only when every Term is equal" because it removes the discount from a customer who wants one longer option.
- **The "Purchased" lock lapses.** It lasts the bought number of years from the moment the order succeeds (payment date, else creation date), then the option can be bought again on that prize. We rejected "locked for ever, whatever the Term" because a Term would then mean nothing after purchase, and "start from handover" because handover happens offline and is not recorded on the site.
- **No top-up of years.** While the lock lasts, an option cannot be bought again, so a customer cannot add years to one they already hold. The Term has to be right at first purchase; the site owner handles corrections offline, as with every add-on.
- **Lucky Dip gets the same Term field**, sharing one selection per prize with the main card, so a Term chosen in one place is never silently reset by the other.

## Consequences

- **Selection becomes option → years.** Cart data keeps `option_ids` and adds `option_years` (missing means 1), so carts and orders created before this change read as one year with no migration.
- **Prices are still computed on the server** in `nera_prize_addons_quote()`; the browser sends only IDs and whole-number years, which the server clamps to 1..Maximum term (and to 1 when the prize's switch is off).
- **The order item snapshot gains `years`** and the amount charged per option; the "valid until" date and the lock expiry are derived from the order's date plus that `years` when read, so nothing is stored that could drift and no scheduled job exists.
- **Turning the switch off, or lowering the Maximum term, reprices carts that already hold longer terms** (they are brought back into range with the existing "no longer available" style notice). Placed orders keep what was paid.
- **The lock lapsing is worth little unless a draw stays open for years**, because the lock is per account and per draw. It is kept because the client asked for terms to mean something; it also makes the rule safe if the lock is ever widened beyond one draw.
- **The unit of the catalog price changes meaning.** Existing entries are read as per-year prices; the admin labels say so.
