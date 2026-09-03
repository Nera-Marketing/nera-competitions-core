# Complete competition orders when every line item is fulfilled

A paid competition order stays on `processing` for ever unless something promotes it. WooCommerce only auto-completes when **every** line item is both virtual *and* downloadable (`WC_Order::needs_processing()`, `class-wc-order.php`); lottery products are not downloadable, so `payment_complete()` always resolves to `processing`. Neither Lottery for WooCommerce, nor the async tickets plugin, nor COD's own status branch ever moves it on — WooCommerce leaves that to a human clicking Complete.

That click carries no information here. "Processing" means *paid, awaiting shipment* — a model for physical goods. A competition entry has nothing to ship: fulfilment IS the issuing of tickets or plays, and that is already automated. So the theme completes the order itself, at the moment fulfilment finishes.

**Not at payment.** With async generation, payment completes seconds to minutes before the tickets exist. Completing there would show "Completed" on an order with no tickets — a worse lie than the stale badge it replaced. Leaving `processing` to mean *tickets are being generated* makes the badge honest.

**Per line item, not per order.** An order can carry two different obligations at once: raffle tickets to issue, and Strike A Win runs / Spin To Win spins to grant. Each item must have its own obligation discharged. Collapsing this to "tickets OR plays" would complete a mixed basket the instant its spins are granted at payment, while the raffle half was still generating — the same failure arriving by a different door.

Implemented in `inc/auto-complete-lottery-orders.php`; `nera_lottery_order_fulfilment_note()` is the single definition of "this order is finished".

## Consequences

- Two entry points are needed, because the two fulfilment paths signal differently: `lty_lottery_ticket_confirmed` (LFW, fires for both the synchronous and async ticket paths), and `woocommerce_payment_complete` / `woocommerce_order_status_processing` at priority **30** — after Strike A Win and Spin To Win write their grant rows at priority 20. A pure Spin or Strike A Win order never fires the ticket hook and would otherwise sit on `processing` for ever.
- The status change is deferred to `shutdown`. LFW fires `lty_lottery_ticket_confirmed` *before* it saves `lty_lottery_ticket_updated_once`; completing inline fires `woocommerce_order_status_completed`, which the async tickets plugin hooks to re-enqueue generation, and its `lfw_tickets_fully_done()` guard would read the un-saved flag as absent and queue a second generation pass.
- Item type is read from the product (`_saw_is_competition`, `_nera_stw_enabled`), which does not survive the competition being deleted. Historical orders whose products are gone therefore fall through to the migration in ADR 0002 rather than this rule.
- Lottery items are detected from order-item meta (`_lty_lottery_tickets`, `_lty_is_instant_win_lottery`, `_lty_lottery_answers`), **not** from `$item->get_product()`. Competitions are routinely deleted after they run, and treating a missing product as "not a lottery item" made every historical order permanently ineligible.
- Grants are matched per item — Strike A Win by `order_item_id`, Spin To Win by `product_id` — so a basket holding two play products cannot be completed on the strength of one of them.
- Orders containing anything that is not a lottery product are never touched. Auto-completing a basket with merchandise in it would skip shipping silently, with nothing anywhere to flag it.
- Completing an order sends WooCommerce's customer "Completed order" email. That is correct and wanted on the live path, and deliberately suppressed in the migration — see ADR 0002.
