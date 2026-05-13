# Async Lottery Ticket Generation

Offloads the per-ticket `wp_insert_post()` loop in `lottery-for-woocommerce` from the user's checkout request onto Action Scheduler, so checkout responds in near-constant time regardless of ticket-bundle size.

## What it does

- Unhooks `LTY_Order_Handler::create_ticket_on_placing_order` from `woocommerce_checkout_update_order_meta` and `woocommerce_store_api_checkout_order_processed`.
- Enqueues an `nera_lty_generate_tickets` Action Scheduler job in its place (group: `lottery-tickets`).
- Worker calls the same plugin routine, wrapped with `wp_suspend_cache_invalidation(true)` and `wp_defer_term_counting(true)` for a 5–10× speedup.
- Thank-you page shows a "tickets being prepared" banner that polls a tiny REST endpoint and reloads when the worker finishes.

## Hard dependency: real cron

Action Scheduler must actually run. WP-Cron is request-driven and will starve under exactly the load this fix is meant to handle. On the server:

```cron
* * * * * cd /var/www/site && wp action-scheduler run --batch-size=25 --group=lottery-tickets >/dev/null 2>&1
```

And in `wp-config.php`:

```php
define( 'DISABLE_WP_CRON', true );
```

Without this, orders will queue but never drain.

## Files

| File | Role |
|---|---|
| `loader.php` | Dependency gate + bootstraps the classes below |
| `class-nera-lty-async-tickets.php` | Unhooks plugin sync callback, enqueues AS job, runs worker |
| `order-received-polling.php` | Thank-you page banner + `GET /wp-json/nera/v1/order/{id}/tickets-status` |

Loaded from `functions.php` via `require_once NERA_DIR . '/inc/lty-async-tickets/loader.php'`.

## Order meta keys this module owns

| Key | Set when | Cleared when |
|---|---|---|
| `_nera_lty_tickets_queued`    | Job enqueued                                | Worker throws (so AS can retry) |
| `_nera_lty_tickets_queued_at` | Job enqueued (microtime)                    | — |
| `_nera_lty_tickets_done_at`   | Worker finishes successfully (microtime)    | — |

Also clears the plugin-owned flag `lty_lottery_ticket_created_once` on worker failure so the plugin's own idempotency guard doesn't block the AS retry.

## What this does NOT change

- REST API order-creation path (`woocommerce_after_order_object_save` → `create_ticket_on_placing_order_via_rest_api`) is left alone — out of scope.
- Ticket status transitions on `woocommerce_order_status_*` (pending → buyer) are left alone.
- No files inside `wp-content/plugins/lottery-for-woocommerce/` are modified.

## Verification

1. Place a checkout order with a small lottery product (5 tickets, Cash on Delivery).
2. Thank-you page shows the "tickets being prepared" banner.
3. In a terminal: `wp action-scheduler run --group=lottery-tickets`.
4. Reload the thank-you page → ticket list renders.
5. `wp post list --post_type=lty_lottery_ticket --meta_key=lty_order_id --meta_value=<ID>` returns 5 rows.
6. `wp post meta get <ID> _nera_lty_tickets_done_at` returns a timestamp.

## Local-stress sanity check

Pre-rebuild baseline (sync) vs post-rebuild (async), 20 k6 VUs for 60s against a 50-ticket bundle:
- Checkout p95 should drop by ≥5×.
- AS queue (`wp action-scheduler queue --group=lottery-tickets --status=pending --format=count`) should grow during the spike and drain within ~2× spike duration.
- No duplicate ticket numbers; no orders left without tickets after queue drains.

## Disabling

Comment out the `require_once` line in `functions.php` (search for `lty-async-tickets/loader.php`). The plugin's original sync callbacks resume on the next page load.
