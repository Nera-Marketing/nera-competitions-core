# Nera Competitions Standard 1.3.30

## Feature — Competition orders complete themselves once fulfilled

Paid competition orders used to sit on **Processing** for ever. WooCommerce
only auto-completes when every line item is virtual *and* downloadable, and
lottery products are not downloadable — so nothing in WooCommerce, Lottery for
WooCommerce or the async tickets plugin ever promoted them. It needed an admin
to click Complete on every order. Customers read the stale badge as "my order
is stuck".

The theme now completes the order itself, at the moment fulfilment actually
finishes.

- **Two entry points.** `lty_lottery_ticket_confirmed` for raffle tickets, and
  `woocommerce_payment_complete` / `woocommerce_order_status_processing` at
  priority 30 for Strike A Win runs and Spin To Win spins — a pure Spin or
  Strike A Win order never fires the ticket hook.
- **Per line item, not per order.** A basket holding a Spin product alongside a
  normal competition waits for *both*: the spins granted at payment and the
  raffle tickets generated after. Every item must have its own obligation
  discharged.
- **Never at payment.** With async generation the tickets appear seconds to
  minutes after payment. `Processing` now genuinely means "tickets are being
  generated".
- **Mixed baskets are left alone.** Anything that is not a lottery product
  blocks completion, so an order with merchandise in it still gets shipped.
- The customer "Completed order" email fires as normal.

See `docs/adr/0001-complete-competition-orders-when-every-item-is-fulfilled.md`.

## Feature — One-off migration for historical orders

Orders confirmed before the above existed can never fire the event again. A
background migration sweeps them once, then marks itself done.

- Runs unattended via Action Scheduler in small spaced batches, so a live
  storefront never feels it. Visible and cancellable at WooCommerce → Status →
  Scheduled Actions (group `nera-competitions`).
- **Tier 1** applies the normal rule. **Tier 2** completes orders whose ticket
  numbers were recorded at purchase but whose ticket records were since deleted
  along with the competition — only when nothing has touched the order for at
  least three days. Tier 2 is migration-only and never relaxes the live rule.
- **Customer emails are suppressed for the migration only.** These orders are
  weeks or months old; mailing "your order is complete" now would confuse more
  than the stale badge did.
- Constants: `NERA_LOTTERY_BACKFILL_DISABLED` (kill switch),
  `NERA_LOTTERY_BACKFILL_BATCH`, `NERA_LOTTERY_BACKFILL_DELAY`,
  `NERA_LOTTERY_BACKFILL_STALE_DAYS`.

See `docs/adr/0002-backfill-historical-orders-in-tiers.md`.

## Fix — Historical orders were unreachable when their competition was deleted

Lottery items were detected with `$item->get_product()`, which returns null once
a competition is deleted — routine after a draw. Every historical order was
therefore treated as "not a lottery order" and skipped. Detection now reads the
order-item meta LFW stamps at purchase (`_lty_lottery_tickets`,
`_lty_is_instant_win_lottery`, `_lty_lottery_answers`), which survives the
product being removed.
