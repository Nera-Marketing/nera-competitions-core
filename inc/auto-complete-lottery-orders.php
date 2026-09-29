<?php
/**
 * Auto-complete lottery orders once their tickets are confirmed.
 *
 * WHY THIS EXISTS
 * ---------------
 * WooCommerce only auto-completes an order when every line item is BOTH virtual
 * and downloadable (WC_Order::needs_processing()). Lottery products are not
 * downloadable, so a paid competition order lands on `processing` and stays
 * there until a human opens it and clicks Complete. There is no step anywhere in
 * WooCommerce, Lottery for WooCommerce, or the async tickets plugin that ever
 * moves it on.
 *
 * That manual step carries no information: by the time tickets are confirmed the
 * order IS fulfilled — a competition entry has nothing to ship. Customers were
 * reading the leftover "Processing" badge as "my order is stuck".
 *
 * WHEN IT FIRES
 * -------------
 * Two entry points, because a competition order can be fulfilled two ways:
 *
 *  - `lty_lottery_ticket_confirmed`, which LFW fires once per order at the end
 *    of LTY_Order_Handler::update_lottery_ticket_in_order() — the moment tickets
 *    move out of `lty_ticket_pending`. Both the synchronous LFW path and the
 *    async tickets plugin's worker route through that method, so this works with
 *    or without the async plugin installed.
 *  - `woocommerce_payment_complete` / `woocommerce_order_status_processing` at
 *    priority 30, after Strike A Win and Spin To Win have written their grant
 *    rows at priority 20. A pure Spin or Strike A Win order never fires the
 *    ticket hook, so without this it would sit on `processing` for ever.
 *
 * Whichever entry point runs, completion itself requires EVERY line item to be
 * fulfilled — see nera_lottery_order_fulfilment_note().
 *
 * Deliberately NOT hooked to payment: with async generation, payment completes
 * seconds-to-minutes before the tickets exist. Completing there would show
 * "Completed" on an order with no tickets — worse than the problem being fixed.
 * Leaving `processing` to mean "tickets are being generated" makes the badge
 * honest rather than noise.
 *
 * WHY THE SHUTDOWN DEFER
 * ----------------------
 * LFW fires the action BEFORE it writes `lty_lottery_ticket_updated_once`.
 * Changing the status inline would fire `woocommerce_order_status_completed`,
 * which the async tickets plugin hooks to re-enqueue generation; its
 * `lfw_tickets_fully_done()` guard would still read the un-saved flag as absent
 * and queue the order for a second generation pass. Deferring to `shutdown`
 * lets LFW finish its save first, so the guard sees the truth.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Catch the confirmation and queue the order for completion at shutdown.
 */
add_action(
  'lty_lottery_ticket_confirmed',
  static function ($ticket_id, $ticket_data, $order_id, $instant_winner_ticket_ids) {
    unset($ticket_id, $ticket_data, $instant_winner_ticket_ids);

    nera_queue_lottery_order_completion((int) $order_id);
  },
  10,
  4
);

/**
 * Second entry point: Strike A Win runs and Spin To Win spins.
 *
 * Those plugins grant on `woocommerce_payment_complete` and
 * `woocommerce_order_status_processing` at priority 20, so run at 30 — after
 * the grant row exists, or the check below would find nothing and give up.
 *
 * A pure Spin or Strike A Win order never fires `lty_lottery_ticket_confirmed`,
 * so without this it would sit on `processing` for ever. The completion itself
 * is still gated on EVERY item being fulfilled, so a mixed basket queued here
 * simply finds its raffle half unfinished and waits for the ticket hook.
 */
foreach (['woocommerce_payment_complete', 'woocommerce_order_status_processing'] as $nera_play_grant_hook) {
  add_action($nera_play_grant_hook, 'nera_queue_lottery_order_completion', 30, 1);
}
unset($nera_play_grant_hook);

/**
 * Schedule one completion attempt per order, per request.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function nera_queue_lottery_order_completion(int $order_id): void
{
  static $queued = [];

  if ($order_id <= 0 || isset($queued[$order_id])) {
    return;
  }

  $queued[$order_id] = true;

  add_action(
    'shutdown',
    static function () use ($order_id) {
      nera_complete_lottery_order($order_id);
    },
    20
  );
}

/**
 * Move a fully-ticketed lottery order to Completed.
 *
 * Reloads the order rather than trusting anything captured earlier: LFW saved
 * the confirmation flag after our hook ran, so an object from that moment is
 * already stale.
 *
 * This is the SINGLE definition of "this order is finished and may be
 * completed". The one-off backfill calls it too, so a rule can never drift
 * between the live path and the migration.
 *
 * @param int $order_id Order ID.
 * @return bool Whether the order was moved to Completed.
 */
function nera_complete_lottery_order(int $order_id): bool
{
  $order = wc_get_order($order_id);
  if (!$order instanceof WC_Order) {
    return false;
  }

  // Only ever promote a paid, unfulfilled order. Leaves cancelled, refunded,
  // failed, on-hold and already-completed orders untouched.
  if ('processing' !== $order->get_status()) {
    return false;
  }

  if (!nera_order_is_lottery_only($order)) {
    return false;
  }

  $order->read_meta_data(true);

  $note = nera_lottery_order_fulfilment_note($order);
  if ('' === $note) {
    return false;
  }

  $order->update_status('completed', $note);

  return true;
}

/**
 * Note describing how the order was fulfilled, or '' if it is not fully so.
 *
 * A competition order can carry two different obligations, and a single order
 * can carry both: raffle tickets to issue, and Strike A Win runs / Spin To Win
 * spins to grant. EVERY line item must have its own obligation discharged
 * before the order counts as done.
 *
 * This is the part that must not be simplified into "tickets OR plays". A
 * basket holding a Spin product alongside a normal competition would then
 * complete the instant the spins are granted at payment — minutes before the
 * raffle tickets finish generating — and the customer would be told their
 * order was finished while half of it did not exist yet. That is the same
 * failure that made "Processing" meaningless, arriving by a different door.
 *
 * An item with neither obligation resolvable is treated as unfulfilled: a
 * lottery item with no ticket numbers yet is simply still generating.
 *
 * @param WC_Order $order Order.
 * @return string Order note, or '' when the order is not fully fulfilled.
 */
function nera_lottery_order_fulfilment_note(WC_Order $order): string
{
  $items = $order->get_items('line_item');
  if (empty($items)) {
    return '';
  }

  $order_id = $order->get_id();

  // Ticket state is an order-level fact in LFW, so resolve it once.
  $tickets_ready = $order->get_meta('lty_lottery_ticket_updated_once')
    && nera_lottery_order_has_tickets($order_id);

  $saw_tickets = false;
  $saw_plays = false;

  foreach ($items as $item) {
    // Prize add-ons are fulfilled offline for the winner only, so they carry
    // nothing the website must issue (ADR 0012).
    if (function_exists('nera_prize_addons_is_order_item') && nera_prize_addons_is_order_item($item)) {
      continue;
    }

    $has_obligation = false;

    if (nera_lottery_item_has_ticket_numbers($item)) {
      if (!$tickets_ready) {
        return '';
      }
      $saw_tickets = true;
      $has_obligation = true;
    }

    if (nera_lottery_item_is_play_product($item)) {
      if (!nera_lottery_item_has_play_grant($order_id, $item)) {
        return '';
      }
      $saw_plays = true;
      $has_obligation = true;
    }

    if (!$has_obligation) {
      return '';
    }
  }

  // Only add-on lines: nothing was actually issued, so nothing to complete.
  if (!$saw_tickets && !$saw_plays) {
    return '';
  }

  if ($saw_tickets && $saw_plays) {
    return __('Tickets confirmed and plays granted — order completed automatically.', 'nera-competitions');
  }

  if ($saw_plays) {
    return __('Plays granted to the player — order completed automatically.', 'nera-competitions');
  }

  return __('Tickets confirmed — order completed automatically.', 'nera-competitions');
}

/**
 * Whether LFW allocated ticket numbers to this line item.
 *
 * @param WC_Order_Item $item Line item.
 * @return bool
 */
function nera_lottery_item_has_ticket_numbers($item): bool
{
  if (!method_exists($item, 'get_meta')) {
    return false;
  }

  $tickets = $item->get_meta('_lty_lottery_tickets', true);

  if (is_array($tickets)) {
    return !empty($tickets);
  }

  return '' !== (string) $tickets;
}

/**
 * Whether this line item is a Strike A Win or Spin To Win product.
 *
 * Read from the product, which is the only place the distinction lives. On a
 * historical order whose product was deleted this returns false — correct, in
 * that there is nothing left to verify a grant against; the backfill handles
 * those separately.
 *
 * @param WC_Order_Item $item Line item.
 * @return bool
 */
function nera_lottery_item_is_play_product($item): bool
{
  if (!method_exists($item, 'get_product')) {
    return false;
  }

  $product = $item->get_product();
  if (!$product) {
    return false;
  }

  if ('1' === (string) $product->get_meta('_saw_is_competition')) {
    return true;
  }

  $stw = (string) $product->get_meta('_nera_stw_enabled');

  return '' !== $stw && 'no' !== $stw && '0' !== $stw;
}

/**
 * Whether the runs/spins for this specific line item were granted.
 *
 * Checked per item rather than per order so a basket with two play products
 * cannot be completed on the strength of one of them having been granted.
 * Strike A Win records the originating `order_item_id`; Spin To Win records the
 * `product_id`.
 *
 * @param int           $order_id Order ID.
 * @param WC_Order_Item $item     Line item.
 * @return bool
 */
function nera_lottery_item_has_play_grant(int $order_id, $item): bool
{
  global $wpdb;

  if ($order_id <= 0) {
    return false;
  }

  if (class_exists('Nera_SAW_Database') && is_callable(['Nera_SAW_Database', 'table'])) {
    $table = Nera_SAW_Database::table('run_grants');
    $found = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT id FROM {$table} WHERE order_id = %d AND order_item_id = %d LIMIT 1",
        $order_id,
        (int) $item->get_id()
      )
    );
    if (null !== $found) {
      return true;
    }
  }

  if (class_exists('Nera_STW_Order_Grants') && method_exists($item, 'get_product_id')) {
    $table = $wpdb->prefix . 'nera_stw_order_grants';
    $found = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT id FROM {$table} WHERE order_id = %d AND product_id = %d LIMIT 1",
        $order_id,
        (int) $item->get_product_id()
      )
    );
    if (null !== $found) {
      return true;
    }
  }

  return false;
}

/**
 * Whether at least one lottery ticket post still exists for this order.
 *
 * Queried directly: ticket posts use custom statuses (`lty_ticket_buyer`,
 * `lty_ticket_pending`, …) that a status-scoped WP_Query handles unreliably.
 *
 * @param int $order_id Order ID.
 * @return bool
 */
function nera_lottery_order_has_tickets(int $order_id): bool
{
  global $wpdb;

  if ($order_id <= 0) {
    return false;
  }

  $found = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT p.ID
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'lty_order_id'
        WHERE p.post_type = 'lty_lottery_ticket'
          AND pm.meta_value = %s
        LIMIT 1",
      (string) $order_id
    )
  );

  return null !== $found;
}

/**
 * Whether EVERY line item on the order is a lottery product.
 *
 * A mixed basket (tickets plus merchandise) still needs a human to ship the
 * physical goods. Auto-completing it would mark the order fulfilled while the
 * merchandise sits unsent, with nothing anywhere to flag it — so anything that
 * is not a lottery product blocks completion.
 *
 * The one exception is a prize add-on line: it is bought with its draw's
 * tickets, has nothing to ship, and is only acted on offline if that customer
 * wins (ADR 0012). It neither blocks completion nor counts as a lottery item.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function nera_order_is_lottery_only(WC_Order $order): bool
{
  $items = $order->get_items('line_item');
  if (empty($items)) {
    return false;
  }

  $saw_lottery = false;
  foreach ($items as $item) {
    if (function_exists('nera_prize_addons_is_order_item') && nera_prize_addons_is_order_item($item)) {
      continue;
    }
    if (!nera_order_item_is_lottery($item)) {
      return false;
    }
    $saw_lottery = true;
  }

  return $saw_lottery;
}

/**
 * Whether one line item was a lottery purchase.
 *
 * The product object is authoritative when it still exists — but on older
 * orders it very often does not. Competitions get deleted once they have run,
 * and then `$item->get_product()` returns null for every item on every order
 * that bought them. Treating "product missing" as "not a lottery item" makes
 * historical orders permanently ineligible, which is exactly what stalled the
 * first backfill run: 25 inspected, 25 skipped, 0 completed.
 *
 * So fall back to the meta LFW stamps on the ORDER ITEM at purchase time. That
 * lives on the order forever and survives the product being deleted.
 * `_lty_is_instant_win_lottery` is included because its value is legitimately
 * the string "no" — presence is the signal, not truthiness.
 *
 * @param WC_Order_Item $item Line item.
 * @return bool
 */
function nera_order_item_is_lottery($item): bool
{
  if (!method_exists($item, 'get_meta')) {
    return false;
  }

  if (method_exists($item, 'get_product') && function_exists('lty_is_lottery_product')) {
    $product = $item->get_product();
    if ($product && lty_is_lottery_product($product)) {
      return true;
    }
  }

  foreach (['_lty_lottery_tickets', '_lty_is_instant_win_lottery', '_lty_lottery_answers'] as $meta_key) {
    $value = $item->get_meta($meta_key, true);

    // `_lty_lottery_tickets` and `_lty_lottery_answers` are arrays of numbers;
    // `_lty_is_instant_win_lottery` is the string "yes"/"no". Presence is the
    // signal in every case, so never cast — an array cast both warns and
    // stringifies to "Array".
    if (is_array($value)) {
      if (!empty($value)) {
        return true;
      }
      continue;
    }

    if ('' !== (string) $value) {
      return true;
    }
  }

  return false;
}
