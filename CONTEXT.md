# Nera Competitions Theme

Storefront theme for prize competitions (giveaways). Customers buy tickets for a prize; this glossary covers the optional Safety and Add-ons that can accompany a prize.

## Language

**Prize**:
A single giveaway a customer buys tickets for. Also called a draw in the code.
_Avoid_: Product (when meaning the customer-facing thing), competition

**Safety**:
A free list of items the winner of a prize receives with it. Shown on the prize page only; never sold.
_Avoid_: Free extras, inclusions

**Add-on**:
An optional paid extra a customer can buy with their tickets for a prize. Charged once per prize however many tickets are bought, not refunded if the customer does not win, and arranged offline for the winner only.
_Avoid_: Upsell, extra (in customer-facing copy "extras" is tolerated)

**Add-on option**:
One purchasable item, defined once in the Add-ons catalog and chosen by prizes from there. The catalog holds its **Price per year**; what a customer pays is that price times the **Term** they choose.
_Avoid_: Add-on (when meaning a single item)

**Price per year**:
The price stored on a catalog entry: what one year of that option costs. It is the only price an admin ever enters, and the settings must label it as per year. The prize's Full bundle price is also per year.
_Avoid_: Price (unqualified), base price

**Term**:
The whole number of years (1 up to the **Maximum term**) a customer buys one add-on option for, chosen per option on prizes that have the term choice switched on. Without the switch every option is one year. A customer's cost for an option is Price per year × Term.
_Avoid_: Duration, length, years (as a noun on its own)

**Maximum term**:
The site-wide upper limit for a Term, set in the Add-ons settings (WooCommerce settings). The field shows 10 as a placeholder and the admin can change it; left empty, 10 applies. The customer's Term field accepts 1 to this number.
_Avoid_: Max years

**Catalog**:
The site-wide list of Safety items and Add-on options, kept only in site settings. A prize picks entries from it and cannot change them; editing an entry applies to every prize using it at once, and an entry a prize uses cannot be deleted.
_Avoid_: Global default, template, library

**Selected items**:
The catalog entries one prize has chosen, in the order the admin chose them. A prize's own text (title, description) and its Full bundle price are the only things a prize can set for itself.
_Avoid_: Prize items, overrides (for the picks)

**Full bundle**:
A fixed per-year price, set per prize, that replaces the total of that prize's selected options for each **complete set of years**. When a customer buys every option in one purchase and owns none already, the number of complete sets is the smallest Term among the options; each set is charged the Full bundle price, and every year beyond the sets is charged at that option's own Price per year. So 1, 2 and 1 years across three options is one set plus one extra year of the second option; 2, 2 and 2 years is two sets. It is offered only when the prize has two or more options and the price is below their per-year total; it can never exceed that total.
_Avoid_: Discount, package

**Purchased**:
The state of an add-on option a customer has already paid for on a prize. It cannot be bought again by that customer while it lasts, whatever Term they bought. It lapses once the Term has run: the clock starts when the order succeeds (its payment date, else its creation date) and lasts the bought number of years, after which the option can be bought again on that prize. It is worked out from the order when read, and the order shows its "valid until" date.
_Avoid_: Owned, locked

**Add-ons Bundles switch**:
The site-wide on/off for the whole Safety and Add-ons feature. While off, customers see and can buy nothing from it, but saved prize data and already-placed orders are untouched. Off by default.
_Avoid_: Master toggle, feature flag

**Ticket cap per person**:
The most tickets one customer may hold for a single drop, counting tickets already bought. Set per drop either as a fixed number of tickets or as a percentage of the drop.
_Avoid_: Max per user, purchase limit

**Percentage cap**:
A Ticket cap per person expressed as a share of the drop's total tickets, rounded down and never below one ticket. Fixed for the life of the drop, it does not shrink as tickets sell.
_Avoid_: Percent limit

**Auto end date**:
A site switch that fills a drop's End Date from its Start Date plus a configured number of days (default 14) while an admin edits the product. Applies only to Limited Duration drops, never overwrites an End Date set by hand, and never changes dates already saved. A drop still closes early when all its tickets sell (an LFW setting), so the drop ends when full or after the configured days, whichever comes first.
_Avoid_: Auto close, auto schedule

**Postal entry**:
A free entry into a drop that someone posted in, logged by an admin on WooCommerce > Postal entries (needs the Postal entries switch in Theme Settings > WooCommerce). It is one postal entry (one order) per person per drop, but the admin can type how many free entries to generate (field "Number of free entries to generate", default 1, never more than the drop has left; each one gets its own entry number and all are in the confirmation email), held by a member (a registered account), issued as a GBP 0 order marked `_nera_postal_entry` and given its entry number the normal way, so it counts towards the drop's total but not towards the member's own Ticket cap per person. It shows as "postal" on the published entry list. The person is emailed: a confirmation with the entry number, or "not entered" when the drop was full or closed first.
_Avoid_: Free ticket, manual entry, offline entry

**Pending postal entry**:
A Postal entry logged for an email that has no member account yet. No order exists. The person is emailed an invitation to register; when they register, the entry is issued automatically if the drop is still open and not full, otherwise they get the "not entered" email. An admin can cancel it or resend the invitation. Held in the postal entry log (an option), with the name, email and address, never shown publicly.
_Avoid_: Reserved entry, draft entry
