<?php
/**
 * Migration engine: complete competition orders that were fulfilled before
 * auto-completion existed.
 *
 * WHY THIS EXISTS
 * ---------------
 * inc/auto-complete-lottery-orders.php only fires on events, so it can only help
 * orders fulfilled AFTER it was deployed. Every order fulfilled before that is
 * stranded on `processing` for good — the event will never fire again for them.
 * This sweeps them up.
 *
 * It also doubles as a safety net for the live path: if a request dies before
 * the `shutdown` hook runs, that order is stranded the same way, and re-running
 * this picks it up. Both entry points are idempotent, so re-running is always
 * safe — it simply finds nothing left to do.
 *
 * NOT AUTOMATIC
 * -------------
 * This file is the engine only. It schedules nothing and hooks nothing. It is
 * driven by hand from Tools → Nera Orders Status Migration, which only exists
 * when NERA_ORDERS_STATUS_MIGRATION is defined in wp-config.php. A migration
 * that changes order status across a whole fleet should have a human watching
 * the numbers, and a page that lingers after the job is done just confuses the
 * next person to open Tools.
 *
 * EMAILS ARE SUPPRESSED HERE, AND ONLY HERE
 * -----------------------------------------
 * These orders are weeks or months old; mailing "your order is complete" now
 * would confuse customers far more than the stale badge did. The live path in
 * auto-complete-lottery-orders.php keeps its email, because there the message is
 * both timely and true.
 *
 * Suppression removes the WooCommerce email DISPATCHER rather than filtering
 * `woocommerce_email_enabled_*`. On a site with `woocommerce_defer_transactional_emails`
 * enabled the mail is queued and sent in a LATER request, where a filter set here
 * would no longer apply and the mail would go out anyway. Removing the dispatcher
 * stops `..._notification` from ever firing, so it works in both modes.
 *
 * Deliberately narrow: only the completed-order email is blocked. TeraWallet's
 * `wallet_credit_purchase` and `wallet_cashback` still run, and must — both are
 * idempotent (guarded by `_wc_wallet_purchase_credited` /
 * `_general_cashback_transaction_id` plus a DB lock), and letting them run is
 * what keeps those guards correct for any later status change.
 *
 * @see docs/adr/0002-backfill-historical-orders-in-tiers.md
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Option holding the summary of the last completed run. */
const NERA_LOTTERY_MIGRATION_REPORT_OPTION = 'nera_lottery_migration_report';

/**
 * Orders inspected per batch.
 *
 * Small on purpose. Each batch is one AJAX request, so this trades round trips
 * against request duration — too large and a slow host times out mid-batch.
 *
 * @return int
 */
function nera_lottery_backfill_batch_size(): int
{
  $size = defined('NERA_LOTTERY_BACKFILL_BATCH') ? (int) NERA_LOTTERY_BACKFILL_BATCH : 25;

  return max(1, min(200, $size));
}

/**
 * How stale an order must be before Tier 2 will touch it, in days.
 *
 * This is the whole safety margin for Tier 2. An order still being worked on —
 * generation retrying, a worker mid-run — keeps getting saved, so its modified
 * date stays recent and it stays out of reach. Only orders nothing has touched
 * for days are treated as "this is never going to finish on its own".
 *
 * @return int
 */
function nera_lottery_backfill_stale_days(): int
{
  $days = defined('NERA_LOTTERY_BACKFILL_STALE_DAYS') ? (int) NERA_LOTTERY_BACKFILL_STALE_DAYS : 3;

  return max(1, $days);
}

/**
 * How many orders are still sitting on `processing`.
 *
 * @return int
 */
function nera_lottery_migration_processing_count(): int
{
  // wc_orders_count() is a counting query. Asking wc_get_orders() for every id
  // just to count them would load the whole set into memory on a busy store.
  if (function_exists('wc_orders_count')) {
    return (int) wc_orders_count('processing');
  }

  $ids = wc_get_orders([
    'status' => 'processing',
    'limit' => -1,
    'return' => 'ids',
  ]);

  return is_array($ids) ? count($ids) : 0;
}

/**
 * Process (or preview) one batch of orders.
 *
 * Scan and Run share this one function, differing only by $dry. Duplicating the
 * loop for a preview would let the two drift, and a preview that reports
 * something other than what Run will do is worse than no preview at all.
 *
 * @param int  $offset Where to resume from.
 * @param bool $dry    Report what would happen; write nothing.
 * @return array{offset:int,scanned:int,completed:int,forced:int,skipped:int,done:bool}
 */
function nera_lottery_migration_run_batch(int $offset, bool $dry = false): array
{
  $result = [
    'offset' => max(0, $offset),
    'scanned' => 0,
    'completed' => 0,
    'forced' => 0,
    'skipped' => 0,
    'done' => false,
  ];

  if (!$dry) {
    nera_lottery_backfill_suppress_completed_email();
  }

  $order_ids = wc_get_orders([
    'status' => 'processing',
    'limit' => nera_lottery_backfill_batch_size(),
    'offset' => $result['offset'],
    'orderby' => 'ID',
    'order' => 'ASC',
    'return' => 'ids',
  ]);

  if (empty($order_ids)) {
    $result['done'] = true;

    return $result;
  }

  $result['scanned'] = count($order_ids);

  foreach ($order_ids as $order_id) {
    $order_id = (int) $order_id;

    if ($dry) {
      $verdict = nera_lottery_migration_preview($order_id);

      if ('tier1' === $verdict) {
        $result['completed']++;
      } elseif ('tier2' === $verdict) {
        $result['forced']++;
      } else {
        $result['skipped']++;
      }

      continue;
    }

    // Tier 1 — everything the customer bought is present and correct.
    if (nera_complete_lottery_order($order_id)) {
      $result['completed']++;
      continue;
    }

    // Tier 2 — ticket numbers were assigned but the ticket data is gone.
    if (nera_lottery_backfill_force_complete($order_id)) {
      $result['forced']++;
      continue;
    }

    // Neither. It stays `processing`, so it would come back in every later
    // query. Stepping the offset past it is what makes this terminate.
    $result['skipped']++;
    $result['offset']++;
  }

  // Nothing left the result set during a dry run, so step past the whole batch.
  if ($dry) {
    $result['offset'] += $result['scanned'];
  }

  return $result;
}

/**
 * What Run would do to this order, without doing it.
 *
 * Calls exactly the predicates Run calls, so Scan cannot promise something Run
 * will not deliver.
 *
 * @param int $order_id Order ID.
 * @return string 'tier1', 'tier2', or '' for no change.
 */
function nera_lottery_migration_preview(int $order_id): string
{
  $order = wc_get_order($order_id);
  if (!$order instanceof WC_Order || 'processing' !== $order->get_status()) {
    return '';
  }

  if (!nera_order_is_lottery_only($order)) {
    return '';
  }

  $order->read_meta_data(true);

  if ('' !== nera_lottery_order_fulfilment_note($order)) {
    return 'tier1';
  }

  if ('' !== nera_lottery_backfill_force_reason($order_id)) {
    return 'tier2';
  }

  return '';
}

/**
 * TIER 2 — complete an order whose tickets were assigned but whose ticket data
 * no longer exists.
 *
 * @param int $order_id Order ID.
 * @return bool Whether the order was completed.
 */
function nera_lottery_backfill_force_complete(int $order_id): bool
{
  $reason = nera_lottery_backfill_force_reason($order_id);
  if ('' === $reason) {
    return false;
  }

  $order = wc_get_order($order_id);
  if (!$order instanceof WC_Order) {
    return false;
  }

  $order->update_status('completed', $reason);

  return true;
}

/**
 * Tier 2's verdict for an order: the note to record, or '' to leave it alone.
 *
 * MIGRATION ONLY. This deliberately lives here and NOT in
 * nera_complete_lottery_order(), because the two answer different questions.
 * The live path asks "did this order just finish correctly?" — if a brand new
 * order confirms and its tickets cannot be found, that is a fault happening
 * right now and it must stay visible as `processing`. Completing it would hide
 * a live bug behind a green badge.
 *
 * This asks a historical question instead: "is this order ever going to move
 * again?" For an order untouched for days, whose ticket numbers were recorded
 * at purchase but whose ticket posts were since deleted, the answer is no.
 * Nothing will ever confirm it. Leaving it on `processing` tells the customer
 * something is still coming when nothing is.
 *
 * @param int $order_id Order ID.
 * @return string
 */
function nera_lottery_backfill_force_reason(int $order_id): string
{
  $order = wc_get_order($order_id);
  if (!$order instanceof WC_Order) {
    return '';
  }

  if ('processing' !== $order->get_status()) {
    return '';
  }

  if (!function_exists('nera_order_is_lottery_only') || !nera_order_is_lottery_only($order)) {
    return '';
  }

  // Tickets still present means this is not a data-loss case at all.
  if (nera_lottery_order_has_tickets($order_id)) {
    return '';
  }

  $modified = $order->get_date_modified();
  if (!$modified) {
    return '';
  }

  if ($modified->getTimestamp() > (time() - (nera_lottery_backfill_stale_days() * DAY_IN_SECONDS))) {
    return '';
  }

  if (!nera_lottery_order_had_ticket_numbers($order)) {
    return '';
  }

  return __(
    'Tickets were assigned to this order but the ticket records no longer exist. Order untouched for over the migration threshold, so it was completed by the one-off migration rather than left pending forever.',
    'nera-competitions'
  );
}

/**
 * Whether LFW recorded actual ticket numbers against this order at purchase.
 *
 * `_lty_lottery_tickets` is written onto the ORDER ITEM when the tickets are
 * allocated, and stays there even after the ticket posts and the product are
 * both deleted. It is the only durable proof that this customer was ever given
 * ticket numbers — which is exactly the line between "data was lost" and
 * "generation never got that far".
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function nera_lottery_order_had_ticket_numbers(WC_Order $order): bool
{
  foreach ($order->get_items('line_item') as $item) {
    if (!method_exists($item, 'get_meta')) {
      continue;
    }

    $tickets = $item->get_meta('_lty_lottery_tickets', true);

    if (is_array($tickets)) {
      if (!empty($tickets)) {
        return true;
      }
      continue;
    }

    if ('' !== (string) $tickets) {
      return true;
    }
  }

  return false;
}

/**
 * Stop WooCommerce mailing customers about orders finished long ago.
 *
 * Removes the dispatcher for this one status transition. See the file header
 * for why this is not a `woocommerce_email_enabled_*` filter.
 *
 * @return void
 */
function nera_lottery_backfill_suppress_completed_email(): void
{
  if (!class_exists('WC_Emails')) {
    return;
  }

  // Force the mailer to wire itself up first; removing a hook that has not been
  // added yet is a no-op, and WC_Emails registers these lazily. WC() can still
  // be null if anything calls this before WooCommerce has booted.
  if (function_exists('WC')) {
    $wc = WC();
    if (is_object($wc) && method_exists($wc, 'mailer')) {
      $wc->mailer();
    }
  }

  remove_action('woocommerce_order_status_completed', ['WC_Emails', 'send_transactional_email'], 10);
  remove_action('woocommerce_order_status_completed', ['WC_Emails', 'queue_transactional_email'], 10);
}
