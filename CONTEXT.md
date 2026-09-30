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
One purchasable item with a fixed price, defined once in the Add-ons catalog and chosen by prizes from there.
_Avoid_: Add-on (when meaning a single item)

**Catalog**:
The site-wide list of Safety items and Add-on options, kept only in site settings. A prize picks entries from it and cannot change them; editing an entry applies to every prize using it at once, and an entry a prize uses cannot be deleted.
_Avoid_: Global default, template, library

**Selected items**:
The catalog entries one prize has chosen, in the order the admin chose them. A prize's own text (title, description) and its Full bundle price are the only things a prize can set for itself.
_Avoid_: Prize items, overrides (for the picks)

**Full bundle**:
A fixed price, set per prize, that replaces the total of that prize's selected options, applying only when a customer buys every one of them in one purchase and owns none already. It is offered only when the prize has two or more options and the price is below their total; it can never exceed the total.
_Avoid_: Discount, package

**Purchased**:
The state of an add-on option a customer has already paid for on a prize. It cannot be bought again by that customer.
_Avoid_: Owned, locked

**Add-ons Bundles switch**:
The site-wide on/off for the whole Safety and Add-ons feature. While off, customers see and can buy nothing from it, but saved prize data and already-placed orders are untouched. Off by default.
_Avoid_: Master toggle, feature flag
