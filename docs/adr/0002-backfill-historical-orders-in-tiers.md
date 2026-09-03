# Backfill historical orders in tiers, and never let the migration relax the live rule

ADR 0001 only fires on events, so it can only help orders fulfilled after it was deployed. Every order confirmed before that is stranded on `processing` permanently — the event will never fire again for them. `inc/backfill-complete-lottery-orders.php` sweeps them once.

Historical data is worse than live data, and the migration needs rules the live path must not have. Competitions get deleted after they run, taking their products *and* their ticket posts with them. On the site this was built against, 16 of 28 product IDs on old orders no longer existed, and 93 orders carried a confirmation flag with zero surviving tickets.

**Two tiers.** Tier 1 is the live rule from ADR 0001, unchanged. Tier 2 completes an order whose ticket *numbers* were recorded on the order item at purchase but whose ticket *posts* are gone, provided nothing has touched the order for at least three days.

**Tier 2 must never reach the live path.** The two answer different questions. Live asks "did this order just finish correctly?" — a new order that confirms with no findable tickets is a fault happening *now*, and must stay visible as `processing`. The migration asks "is this order ever going to move again?" — for an order untouched for days whose data is gone, the answer is no, and leaving it pending tells the customer something is still coming when nothing is.

## Consequences

- The staleness window is the whole safety margin for Tier 2. An order still being worked on keeps getting saved, so its modified date stays recent and it stays out of reach. Configurable via `NERA_LOTTERY_BACKFILL_STALE_DAYS`, default 3.
- Evidence of "tickets were assigned" is `_lty_lottery_tickets` on the order item, not the `lty_lottery_ticket_created_once` order flag. The flag can be set while the create step failed before recording any number; those orders genuinely never had tickets and are left alone deliberately.
- The batch query carries **no** meta filter. Filtering on `lty_lottery_ticket_updated_once` would hide exactly the never-confirmed orders Tier 2 exists to rescue.
- Ineligible orders stay `processing`, so they return in every subsequent query. The batch cursor is advanced past each skip — that, and only that, is what makes the migration terminate.
- Emails are suppressed by removing the WooCommerce **dispatcher** (`WC_Emails::send_transactional_email` / `queue_transactional_email`) from `woocommerce_order_status_completed`, not by filtering `woocommerce_email_enabled_*`. On a site with `woocommerce_defer_transactional_emails` enabled the mail is queued and sent in a later request, where a filter set here no longer applies and the mail goes out anyway.
- Suppression is deliberately narrow. TeraWallet's `wallet_credit_purchase` and `wallet_cashback` still run and must — both are idempotent (guarded by `_wc_wallet_purchase_credited` / `_general_cashback_transaction_id` plus a DB lock), and letting them run is what keeps those guards correct for any later status change. Blanket-unhooking callbacks around money is more dangerous than letting guarded ones fire.
- It runs unattended via Action Scheduler, in small spaced batches, so a live storefront never feels it. Progress is visible and cancellable at WooCommerce → Status → Scheduled Actions (group `nera-competitions`). Kill switch: `NERA_LOTTERY_BACKFILL_DISABLED`.
- Customers will see "Completed" on Tier 2 orders with no tickets to open. This was accepted knowingly: the order will never progress, and the order note records why, so the state remains auditable.
- A gap remains: a Strike A Win or Spin To Win order whose product was deleted and which never recorded ticket numbers is rescued by neither tier. Closing it would need a backfill branch matching grants by `order_id` alone.
