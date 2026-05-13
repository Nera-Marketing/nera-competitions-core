# Stress Test — Async Ticket Generation

A k6 script that drives real WooCommerce checkouts against a local site to verify the async-ticket-generation fix in `../class-nera-lty-async-tickets.php`.

## Prerequisites

- **k6** installed (`brew install k6`).
- Local site running at `http://competitions-core.local` (or set `BASE`).
- Test product `783` ("The Money Wheel") exists and is purchasable.
- **TeraWallet path:** admin user has a wallet balance ≥ `TICKETS × product_price × planned_checkouts`. Easiest: top up by a lot in WP Admin → Users → admin → wallet.
- **OR COD path:** if wallet fails or you don't want to bother with balance, enable Cash on Delivery in WC → Settings → Payments and pass `-e PAYMENT_METHOD=cod`.

## Files in this folder

| File | Role |
|---|---|
| `lottery-stress.js` | k6 script: login, add-to-cart, checkout, loop. |
| `README.md` | This file. |

## Credentials

Pass at runtime, never commit:

```bash
-e USERNAME=adm1n -e PASSWORD='your-password'
```

If you've already shared credentials over chat/email/etc., **rotate them after the test**.

## Sanity run (do this first)

```bash
cd wp-content/themes/nera-competitions-standard/inc/lty-async-tickets/stress-test
k6 run --vus 1 --duration 10s \
  -e USERNAME=adm1n -e PASSWORD='your-password' \
  lottery-stress.js
```

Expect:
- `✓ cart 2xx` and `✓ checkout 2xx` both at 100%.
- A handful of iterations completed.
- `http_req_failed` near 0.

If anything fails, fix it before scaling up:
- `login failed` → wrong username/password.
- `add-to-cart` 4xx → product `783` doesn't exist on this site, or isn't purchasable.
- `checkout` 4xx with body mentioning wallet → admin wallet balance is empty; top it up or switch to COD.
- `checkout` 4xx with `rest_invalid_param` on `state` → your WC store country isn't UK; adjust the address in `lottery-stress.js`.

## A/B comparison procedure

This is the real experiment. Run it twice and compare.

### Run A — sync (baseline)

1. In `wp-content/themes/nera-competitions-standard/functions.php`, **comment out** the async loader:
   ```php
   // if (function_exists('LTY') && class_exists('WooCommerce') && function_exists('as_enqueue_async_action')) {
   //   require_once NERA_DIR . '/inc/lty-async-tickets/loader.php';
   // }
   ```
2. If you have OPcache with `validate_timestamps=0`, restart PHP.
3. Run k6 with the full ramp (default):
   ```bash
   k6 run -e USERNAME=adm1n -e PASSWORD='your-password' lottery-stress.js
   ```
4. **Record** `http_req_duration{name:checkout}` p(95) from the end-of-run summary.

### Run B — async (the fix)

1. **Uncomment** the loader in `functions.php`.
2. Start the Action Scheduler worker loop in a second terminal:
   ```bash
   while true; do wp action-scheduler run --batch-size=25 --group=lottery-tickets; sleep 2; done
   ```
3. Run k6 again with the same command.
4. **Record** `http_req_duration{name:checkout}` p(95).

Expect Run B's p(95) to be substantially lower than Run A — that's the win.

### Monitor the queue (Run B only)

In another terminal during the test:
```bash
watch -n 2 'wp action-scheduler queue --group=lottery-tickets --status=pending --format=count'
```
The pending count should grow during the spike and drain to 0 after k6 finishes (assuming the worker loop is running). If it doesn't drain, the worker loop isn't running fast enough — bump `--batch-size`.

## Result tells you

- **Run A p(95) high, Run B p(95) low** → fix works. Ship it.
- **Both runs have similar high p(95)** → either:
  - The async path isn't engaged (the product isn't an LTY product, or the loader isn't included — check `_nera_lty_tickets_queued` order meta on a test order).
  - The bottleneck is somewhere else (wallet contention is the most likely suspect with a shared admin user — see caveat below).
- **Both runs have similar low p(95)** → localhost is too fast / load too low; bump VUs in `lottery-stress.js` `options.scenarios.ramp.stages`.

## Verify async actually engaged

Pick an order ID from one of the k6-created orders (`wp post list --post_type=shop_order --orderby=ID --order=DESC --posts_per_page=5`) and check:

```bash
wp post meta get <ORDER_ID> _nera_lty_tickets_queued
wp post meta get <ORDER_ID> _nera_lty_tickets_queued_at
wp post meta get <ORDER_ID> _nera_lty_tickets_done_at
```

In Run B (async), all three should be set. In Run A (sync), none should be set (the async hook is disabled, so the meta is never written).

The wall-clock time the worker took = `done_at − queued_at`. That's the *actual* per-order ticket-creation cost, now isolated from the user's checkout response time.

## Wallet contention caveat

With one shared admin user, 30 VUs all debit the same TeraWallet row → MySQL serializes those updates → some of the checkout latency you measure is wallet contention, not ticket creation. The async fix's win should still be visible (it removes ticket creation entirely from the checkout request), but the numbers will be muddier than with a per-VU user pool.

If the result is ambiguous, the next step is `setup-test-users.sh` (not implemented yet — ask Claude to write it) that pre-creates 50 customer users each with their own credited wallet, then bump the k6 script to pick one per VU.

## Tuning knobs

All overridable via `-e`:

| Env var | Default | Notes |
|---|---|---|
| `BASE` | `http://competitions-core.local` | Site URL |
| `PRODUCT_ID` | `783` | Lottery product ID |
| `TICKETS` | `5` | Quantity per checkout |
| `USERNAME` | *(required)* | WP login |
| `PASSWORD` | *(required)* | WP login |
| `PAYMENT_METHOD` | `wallet` | WC gateway ID; try `cod` if wallet errors |

For more concurrency, edit `options.scenarios.ramp.stages` in `lottery-stress.js`.

## Cleanup

Synthetic orders accumulate. To clear them:
```bash
# DRY RUN first
wp post list --post_type=shop_order --posts_per_page=200 --field=ID

# Delete (CAREFUL — be sure you're on the local site)
for id in $(wp post list --post_type=shop_order --posts_per_page=200 --field=ID); do
  wp post delete "$id" --force
done
```

Tickets are linked to orders and get deleted by the plugin's cascade. Verify with:
```bash
wp post list --post_type=lty_lottery_ticket --format=count
```

### Synthetic users

Each successful checkout creates one WP user (WC auto-create-account-at-checkout). After heavy testing you'll have hundreds named like `vu1_iter3_lz4k8a@loadtest.local`. To clean up:

```bash
# DRY RUN — see what would be deleted
wp user list --search='*loadtest.local' --format=table

# Delete (any orders get reassigned to user ID 1 = admin)
wp user list --search='*loadtest.local' --field=ID | xargs -I {} wp user delete {} --yes --reassign=1
```

## Localhost caveat

Localhost numbers are for **relative comparison only**. Your machine runs k6 and the LAMP stack on the same CPU — the tester and the target are fighting for resources, so absolute p(95) values are not predictive of production. For capacity planning, run k6 from a separate machine against a staging host that mirrors prod's PHP-FPM worker count, MariaDB version, and Redis configuration.
