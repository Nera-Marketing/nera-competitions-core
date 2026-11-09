<?php
/**
 * Wallet Partial Payment — buyer-chosen Wallet Contribution at checkout.
 *
 * See CONTEXT.md for the vocabulary (Basket Total, Order Total, Wallet Contribution,
 * Short Balance) and docs/adr/0001–0006 for why this is built the way it is. In short:
 *
 *   - A Wallet Contribution is applied as a negative cart fee, not a second gateway,
 *     because WC_Gateway_Wallet::process_payment() hard-refuses any order it cannot
 *     cover in full and cannot hand a remainder to another gateway (ADR-0001).
 *   - `Partial Payment` is the master setting and `Auto Deduct` its child. TeraWallet
 *     treats them as siblings and its gate compares the Basket Total against the whole
 *     Wallet Balance, which makes "spend £30 of my £140" unreachable. We override the
 *     gate rather than the amount filter (ADR-0002).
 *   - Optional contributions are committed at Place order only — no edit-time AJAX
 *     (ADR-0004). Edge amounts reroute in script (ADR-0005). Wallet Debit runs after
 *     the order exists via TeraWallet; failed Card gets a Wallet Refund (ADR-0006).
 *
 * The behaviour matrix this implements (ADR 0007 / 0008):
 *
 *   Balance   Partial   Auto      State           Result
 *   -------   -------   ----      -----           ------
 *   £0        off       -         card_only       Card only; Partial Payment hidden
 *   £0        on        either    zero_disabled   Full Wallet + Partial both disabled
 *   >= basket off       -         full_only       Full Wallet Payment only
 *   >= basket on        either    optional        opt-in Partial Payment chooser
 *   < basket  off       -         card_only       Card only; Partial Payment hidden
 *   < basket  on        on        forced          Auto Deduct + disabled Full Wallet
 *   < basket  on        off       blocked         Full Wallet + Partial both disabled
 *
 * `Partial` above means the setting AND a Card gateway to collect the remainder. With no Card
 * gateway available the `on` rows are unreachable and the matrix reads as if the setting were
 * off — Full Wallet Payment alone when the balance covers the basket, nothing otherwise.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Is the wallet plugin present and usable?
 *
 * @return bool
 */
function nera_wallet_is_active()
{
  return function_exists('woo_wallet') &&
    is_object(woo_wallet()) &&
    is_object(woo_wallet()->wallet) &&
    function_exists('get_woowallet_cart_total');
}

/**
 * The buyer's Wallet Balance as a raw number.
 *
 * Always 'edit' context — 'view' returns formatted HTML and breaks comparisons.
 *
 * @return float
 */
function nera_wallet_balance()
{
  if (!nera_wallet_is_active() || !is_user_logged_in()) {
    return 0.0;
  }

  return (float) woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit');
}

/**
 * The Basket Total — what is owed before any Wallet Contribution.
 *
 * Deliberately NOT `WC()->cart->total`, which already has the wallet fee subtracted;
 * deriving a remainder from that value double-counts the contribution. See ADR-0001.
 *
 * @return float
 */
function nera_wallet_basket_total()
{
  if (!nera_wallet_is_active() || is_null(WC()->cart)) {
    return 0.0;
  }

  return (float) get_woowallet_cart_total();
}

/**
 * Ceiling for a Wallet Contribution: you cannot spend credit you do not have, nor more
 * than the basket costs.
 *
 * @return float
 */
function nera_wallet_max_contribution()
{
  return (float) min(nera_wallet_balance(), nera_wallet_basket_total());
}

/**
 * The Wallet Contribution currently stored in the session, clamped to the ceiling.
 *
 * @return float
 */
function nera_wallet_requested_contribution()
{
  if (!nera_wallet_is_active() || is_null(WC()->session)) {
    return 0.0;
  }

  $requested = (float) WC()->session->get('partial_payment_amount', 0);

  return (float) max(0, min($requested, nera_wallet_max_contribution()));
}

/**
 * The gateway id that collects the Card leg of a Partial Payment.
 *
 * Single source of truth: the split radio posts this value (docs/adr/0003), the card row
 * suppresses its own `checked` against it, and the precondition below tests it.
 *
 * @return string
 */
function nera_wallet_card_gateway_id()
{
  return (string) apply_filters('nera_wallet_card_gateway_id', 'cashflows_card');
}

/**
 * Is there a Card gateway to hand the remainder to?
 *
 * A Partial Payment is a Wallet Contribution *plus a Card payment for the rest*, so with no
 * Card gateway the option cannot exist — see nera_wallet_partial_state().
 *
 * Deliberately NOT `get_available_payment_gateways()`. That sweeps every gateway calling
 * `is_available()`, and TeraWallet's wallet gateway answers that with
 * `is_enable_wallet_partial_payment()`, which fires the filter we hook in
 * nera_wallet_filter_partial_payment_enabled() — straight back into
 * nera_wallet_partial_state() and round again, unbounded (WooCommerce rebuilds that list on
 * every call rather than memoising it). `payment_gateways()` returns the registry without
 * asking anything whether it is available, and the Cashflows gateway does not override
 * `is_available()` at all (iccf_abstract), so asking it directly cannot re-enter the wallet.
 *
 * The trade-off: a late `woocommerce_available_payment_gateways` filter removing Cashflows is
 * invisible here. Running that filter is precisely what re-enters the wallet gateway.
 *
 * @return bool
 */
function nera_wallet_card_gateway_available()
{
  static $available = null;

  if (null !== $available) {
    return $available;
  }

  if (!function_exists('WC') || !is_object(WC()) || !is_object(WC()->payment_gateways())) {
    // Gateways are not registered yet — answer without memoising a premature `false`.
    return false;
  }

  $gateways = WC()->payment_gateways()->payment_gateways();
  $card_id = nera_wallet_card_gateway_id();

  $available = isset($gateways[$card_id]) && $gateways[$card_id]->is_available();

  return $available;
}

/**
 * Resolve which row of the behaviour matrix applies to this request.
 *
 * @return array{
 *   state:string, balance:float, basket:float, contribution:float,
 *   max:float, remainder:float, partial_enabled:bool
 * }
 */
function nera_wallet_partial_state()
{
  $balance = nera_wallet_balance();
  $basket = nera_wallet_basket_total();

  $result = [
    'state' => 'no_balance',
    'balance' => $balance,
    'basket' => $basket,
    'contribution' => 0.0,
    'max' => 0.0,
    'remainder' => $basket,
    'partial_enabled' => false,
  ];

  if (!nera_wallet_is_active() || !is_user_logged_in() || $basket <= 0) {
    return $result;
  }
  if (function_exists('is_wallet_rechargeable_cart') && is_wallet_rechargeable_cart()) {
    return $result;
  }
  if (function_exists('is_wallet_account_locked') && is_wallet_account_locked()) {
    return $result;
  }

  $partial_enabled =
    'on' ===
    woo_wallet()->settings_api->get_option(
      'is_enable_partial_payment',
      '_wallet_settings_general',
      'on',
    );

  // No Card gateway, no Partial Payment — there is nothing to collect the remainder, and the
  // split radio would post a `payment_method` that does not exist (ADR 0008). Folding it into
  // the setting collapses to the same `full_only` / `card_only` rows the setting-off path
  // already uses, so every consumer of this state — both templates, the TeraWallet gate, the
  // clamp, and the Place order commit — drops Partial Payment together and in silence.
  $partial_enabled = $partial_enabled && nera_wallet_card_gateway_available();

  $auto_deduct =
    $partial_enabled &&
    'on' ===
      woo_wallet()->settings_api->get_option(
        'is_auto_deduct_for_partial_payment',
        '_wallet_settings_general',
      );

  $zero_balance = $balance <= 0;
  $short_balance = !$zero_balance && $balance < $basket;
  $result['max'] = (float) min(max(0, $balance), $basket);
  $result['partial_enabled'] = $partial_enabled;

  if (!$partial_enabled) {
    // Partial Payment totally hidden (ADR 0008). Full Wallet only when balance covers.
    $result['state'] = $zero_balance || $short_balance ? 'card_only' : 'full_only';

    return $result;
  }

  if ($zero_balance) {
    $result['state'] = 'zero_disabled';

    return $result;
  }

  if (!$short_balance) {
    $result['state'] = 'optional';
    $result['contribution'] = nera_wallet_requested_contribution();
  } elseif ($auto_deduct) {
    $result['state'] = 'forced';
    $result['contribution'] = $balance;
  } else {
    $result['state'] = 'blocked';
  }

  $result['remainder'] = (float) max(0, $basket - $result['contribution']);

  return $result;
}

/**
 * Whether to inject a non-selectable Full Wallet Payment radio (ADR 0007 / 0008).
 *
 * @return bool
 */
function nera_wallet_show_disabled_full_wallet()
{
  if (!function_exists('nera_wallet_partial_state')) {
    return false;
  }

  return in_array(
    nera_wallet_partial_state()['state'],
    ['zero_disabled', 'forced', 'blocked'],
    true,
  );
}

/**
 * Announcement key for the disabled Full Wallet Payment radio.
 *
 * @return string 'zero'|'short'
 */
function nera_wallet_disabled_full_wallet_reason()
{
  $state = nera_wallet_partial_state();

  return $state['balance'] <= 0 ? 'zero' : 'short';
}

/**
 * Force TeraWallet's partial-payment gate to agree with our matrix.
 *
 * This filter does work in BOTH directions, which is the whole point:
 *
 *   - Turns the gate ON for `optional` once a contribution is set. TeraWallet's own gate
 *     compares the Basket Total to the full Wallet Balance, so £140 credit against a £50
 *     basket fails `50 >= 140` and refuses the split (ADR-0002).
 *   - Turns the gate OFF when Partial Payment is disabled but Auto Deduct is left on.
 *     TeraWallet's gate never reads the parent setting, so it would happily apply a fee
 *     against our hierarchy.
 *
 * We do not filter `woo_wallet_partial_payment_amount`: the plugin's fee calculation
 * already honours an arbitrary session amount, and that filter is applied at two call
 * sites with different inputs, so overriding it would silently change the gate too.
 *
 * @param bool $is_enable TeraWallet's own verdict.
 * @return bool
 */
function nera_wallet_filter_partial_payment_enabled($is_enable)
{
  if (!nera_wallet_is_active()) {
    return $is_enable;
  }

  $state = nera_wallet_partial_state();

  if ('forced' === $state['state']) {
    return true;
  }

  if ('optional' === $state['state']) {
    return $state['contribution'] > 0;
  }

  return false;
}
add_filter('is_enable_wallet_partial_payment', 'nera_wallet_filter_partial_payment_enabled');

/**
 * Re-clamp the stored Wallet Contribution before TeraWallet turns it into a fee.
 *
 * Runs at priority 5, ahead of the plugin's own callback at 10. Validating only on input
 * is not enough: a buyer can set £50 against a £50 basket and then remove a ticket, which
 * would leave a fee larger than the order (ADR-0002).
 *
 * @return void
 */
function nera_wallet_clamp_contribution()
{
  if (!nera_wallet_is_active() || is_null(WC()->session)) {
    return;
  }

  $stored = (float) WC()->session->get('partial_payment_amount', 0);
  if ($stored <= 0) {
    return;
  }

  $state = nera_wallet_partial_state();

  // Any state other than `optional` derives its amount without the session; a stale value
  // left behind by an earlier basket must not leak into it.
  if ('optional' !== $state['state']) {
    update_wallet_partial_payment_session();

    return;
  }

  // A contribution covering the whole basket would leave a £0 card leg on an option that
  // says "part wallet, part card". There is a dedicated control for that (the wallet
  // gateway), so clear it and let the buyer be switched over. See docs/adr/0003.
  if ($stored >= $state['basket']) {
    update_wallet_partial_payment_session();

    return;
  }

  $max = $state['max'];
  if ($stored > $max) {
    update_wallet_partial_payment_session($max);
  }
}
add_action('woocommerce_cart_calculate_fees', 'nera_wallet_clamp_contribution', 5);

/**
 * Parse a buyer-submitted amount into a usable Wallet Contribution.
 *
 * Untrusted input. Tolerates currency symbols, spaces and both decimal conventions, then
 * clamps into range so no caller has to trust the result. Anything unparseable is 0, never
 * an error — a mistyped amount should mean "no contribution", not a failed checkout.
 *
 * @param mixed $raw Submitted value.
 * @param float $max Ceiling, from nera_wallet_partial_state().
 * @return float
 */
function nera_wallet_parse_amount($raw, $max)
{
  $clean = preg_replace('/[^0-9.,-]/', '', (string) $raw);

  if (false !== strpos($clean, ',') && false !== strpos($clean, '.')) {
    // Both separators present, e.g. "1,234.56" — the comma is a thousands separator.
    $clean = str_replace(',', '', $clean);
  } else {
    // Only commas, e.g. "30,50" — comma is the decimal separator in many locales.
    $clean = str_replace(',', '.', $clean);
  }

  return (float) max(0, min((float) $clean, (float) $max));
}

/**
 * Clear any optional session contribution so edit-time / update_checkout never applies a fee.
 *
 * Forced Auto Deduct does not rely on a buyer session amount (ADR 0002).
 *
 * @return void
 */
function nera_wallet_clear_optional_session()
{
  if (!nera_wallet_is_active() || is_null(WC()->session)) {
    return;
  }

  if (!function_exists('update_wallet_partial_payment_session')) {
    return;
  }

  $stored = (float) WC()->session->get('partial_payment_amount', 0);
  if ($stored <= 0) {
    return;
  }

  update_wallet_partial_payment_session();
}
add_action('woocommerce_checkout_update_order_review', 'nera_wallet_clear_optional_session', 1);

/**
 * Commit the Wallet Contribution at Place order (ADR 0004).
 *
 * Reads the posted Partial Payment flag + amount, refuses 0 / full Basket Total edges
 * (ADR 0005), stores the session value, and recalculates cart fees so Card sees the
 * remainder before the order is created. TeraWallet then Wallet-Debits on
 * `woocommerce_checkout_order_processed` (ADR 0006).
 *
 * @return void
 */
function nera_wallet_checkout_commit_contribution()
{
  if (!nera_wallet_is_active() || is_null(WC()->session)) {
    return;
  }

  $wants_partial =
    isset($_POST['nera_wallet_partial']) &&
    '1' === sanitize_text_field(wp_unslash($_POST['nera_wallet_partial']));

  if (!$wants_partial) {
    nera_wallet_clear_optional_session();

    return;
  }

  $state = nera_wallet_partial_state();
  if ('optional' !== $state['state']) {
    wc_add_notice(
      __('Wallet credit cannot be adjusted for this order.', 'nera-competitions'),
      'error',
    );

    return;
  }

  $amount = nera_wallet_parse_amount(
    isset($_POST['nera_wallet_amount']) ? wp_unslash($_POST['nera_wallet_amount']) : '',
    $state['max'],
  );

  if ($amount <= 0) {
    wc_add_notice(
      __(
        'Choose a wallet amount greater than zero, or pay by card.',
        'nera-competitions',
      ),
      'error',
    );
    nera_wallet_clear_optional_session();

    return;
  }

  if ($amount >= $state['basket']) {
    wc_add_notice(
      __(
        'That covers the whole order — please select Wallet payment instead.',
        'nera-competitions',
      ),
      'error',
    );
    nera_wallet_clear_optional_session();

    return;
  }

  update_wallet_partial_payment_session($amount);

  if (WC()->cart) {
    WC()->cart->calculate_totals();
  }
}
add_action('woocommerce_checkout_process', 'nera_wallet_checkout_commit_contribution');

/**
 * Drop a just-committed session contribution if checkout validation failed.
 *
 * `woocommerce_checkout_process` runs before error aggregation; without this, a failed
 * Place order would leave a fee applied on the next fragment refresh.
 *
 * @param array    $data   Posted checkout data.
 * @param WP_Error $errors Validation errors.
 * @return void
 */
function nera_wallet_clear_session_on_checkout_errors($data, $errors)
{
  $has_errors =
    (is_wp_error($errors) && $errors->get_error_codes()) || wc_notice_count('error') > 0;

  if ($has_errors) {
    nera_wallet_clear_optional_session();
  }
}
add_action('woocommerce_after_checkout_validation', 'nera_wallet_clear_session_on_checkout_errors', 999, 2);

/**
 * Wallet Refund when Card payment fails (ADR 0006).
 *
 * TeraWallet already refunds on `cancelled`; failed Cashflows orders need the same path.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function nera_wallet_refund_partial_on_failed($order_id)
{
  nera_wallet_refund_contribution_for_order($order_id, 'failed');
}
add_action('woocommerce_order_status_failed', 'nera_wallet_refund_partial_on_failed', 10, 1);

/**
 * Whether the wallet refund guard (Theme Settings → WooCommerce) is on. Off by default.
 *
 * Reads the raw option so it also works before ACF loads and from WP-CLI.
 *
 * @return bool
 */
function nera_wallet_refund_guard_enabled()
{
  return (bool) get_option('options_nera_wallet_refund_guard', false);
}

/**
 * Return a Wallet Contribution to Wallet Balance for an order (WooCommerce refund path).
 *
 * With the refund guard on, only the part not already refunded to the wallet by hand is credited.
 *
 * @param int    $order_id Order ID.
 * @param string $reason   Short reason for the note (`failed`, `orphan`, etc.).
 * @return bool Whether a Wallet Refund was issued.
 */
function nera_wallet_refund_contribution_for_order($order_id, $reason = 'failed')
{
  if (!nera_wallet_is_active() || !function_exists('get_order_partial_payment_amount')) {
    return false;
  }

  $order = wc_get_order($order_id);
  if (!$order) {
    return false;
  }

  $amount = (float) get_order_partial_payment_amount($order_id);
  if ($amount <= 0 || !$order->get_meta('_partial_pay_through_wallet_compleate')) {
    return false;
  }

  $guard = nera_wallet_refund_guard_enabled();
  if (!$guard && $order->get_meta('_woo_wallet_partial_payment_refunded')) {
    return false;
  }

  $returned = $guard ? nera_wallet_already_returned($order, $amount) : 0.0;
  $remaining = (float) wc_format_decimal(max(0, $amount - $returned), wc_get_price_decimals());
  if ($remaining <= 0) {
    nera_wallet_add_reduced_note($order, $amount, $remaining, $returned);
    nera_wallet_close_restore($order);
    return false;
  }

  $transaction_id = woo_wallet()->wallet->credit(
    $order->get_customer_id(),
    $remaining,
    sprintf(
      /* translators: 1: order number, 2: reason */
      __('Wallet refund for order #%1$s (%2$s)', 'nera-competitions'),
      $order->get_order_number(),
      $reason,
    ),
    ['currency' => $order->get_currency('edit')],
  );

  if (!$transaction_id) {
    return false;
  }

  if ($returned > 0) {
    nera_wallet_add_reduced_note($order, $amount, $remaining, $returned);
  }
  $order->add_order_note(
    sprintf(
      /* translators: %s: refunded amount */
      __('Wallet amount %s credited to customer after card payment failure.', 'nera-competitions'),
      wc_price($remaining, woo_wallet_wc_price_args($order->get_customer_id())),
    ),
  );
  $order->delete_meta_data('_partial_pay_through_wallet_compleate');
  $order->update_meta_data('_woo_wallet_partial_payment_refunded', true);
  $order->update_meta_data('_partial_payment_refund_id', $transaction_id);
  $order->save();

  return true;
}

/**
 * Link a wallet refund to its wallet credit, and add it to an unpaid order's running total.
 *
 * `woo_wallet_order_refunded` fires only after the wallet was credited. On a paid order the
 * panel can only refund the other payment (the wallet fee already lowers the order total), so
 * those refunds don't count against the Wallet Contribution. The wallet gateway passes the
 * amount instead of a refund object, so its credit stays unlinked.
 *
 * @param WC_Order|int                 $order          Refunded order.
 * @param WC_Order_Refund|float|string $refund         Refund object, or the amount from the wallet gateway.
 * @param int                          $transaction_id Wallet credit transaction id.
 * @return void
 */
function nera_wallet_record_refund($order, $refund, $transaction_id = 0)
{
  if (!nera_wallet_is_active()) {
    return;
  }

  $is_refund = is_a($refund, 'WC_Order_Refund');
  if ($is_refund && (int) $transaction_id > 0) {
    $refund->update_meta_data('_nera_wallet_transaction_id', (int) $transaction_id);
    $refund->save();
  }

  $amount = $is_refund ? (float) $refund->get_amount() : (float) $refund;
  $order = wc_get_order($order instanceof WC_Order ? $order->get_id() : $order);
  if (!$order || $amount <= 0 || $order->get_date_paid()) {
    return;
  }

  $total = (float) $order->get_meta('_nera_wallet_refunded_total') + $amount;
  $order->update_meta_data('_nera_wallet_refunded_total', wc_format_decimal($total, wc_get_price_decimals()));
  $order->save();
}
add_action('woo_wallet_order_refunded', 'nera_wallet_record_refund', 10, 3);

/**
 * Refunds on an unpaid order that credited the customer's wallet.
 *
 * A refund linked to its wallet credit counts. An unlinked refund (made before the link was
 * stored) counts only when a wallet credit on this site with the same amount and reason was
 * recorded for the customer within a few minutes of it, that credit is not linked to another
 * refund, and the customer's other unlinked refunds with the same amount and reason can't claim
 * it too. When they could, it is not counted: the customer keeps the credit. A "Refund manually"
 * refund put no money in the wallet and does not count.
 *
 * @param WC_Order $order Unpaid order.
 * @return float
 */
function nera_wallet_refunds_to_wallet(WC_Order $order)
{
  global $wpdb;

  $customer_id = (int) $order->get_customer_id();
  if ($customer_id <= 0) {
    return 0.0;
  }

  $total = 0.0;
  $used = [];
  $counted = [];
  foreach ($order->get_refunds() as $refund) {
    $amount = (float) $refund->get_amount();
    $created = $refund->get_date_created();
    if ($amount <= 0 || !$created) {
      continue;
    }

    $linked = (int) $refund->get_meta('_nera_wallet_transaction_id');
    if ($linked > 0) {
      if (!nera_wallet_linked_credit_exists($linked)) {
        continue;
      }
      $used[] = $linked;
      $counted[] = $refund->get_id();
      $total += $amount;
      continue;
    }

    $reason = $refund->get_reason();
    $competitors = 0;
    foreach (nera_wallet_customer_refunds_near($customer_id, $created->getTimestamp()) as $other) {
      $other_tx = (int) $other->get_meta('_nera_wallet_transaction_id');
      if ($other_tx > 0) {
        $used[] = $other_tx;
      } elseif (
        !in_array($other->get_id(), $counted, true) &&
        abs((float) $other->get_amount() - $amount) < 0.005 &&
        $other->get_reason() === $reason
      ) {
        $competitors++;
      }
    }

    $time = $created->getOffsetTimestamp();
    $exclude = $used ? ' AND transaction_id NOT IN (' . implode(',', array_map('intval', array_unique($used))) . ')' : '';
    $credits = $wpdb->get_col(
      $wpdb->prepare(
        "SELECT transaction_id FROM {$wpdb->base_prefix}woo_wallet_transactions
        WHERE blog_id = %d AND user_id = %d AND type = 'credit' AND deleted = 0 AND ABS(amount - %f) < 0.005
        AND details = %s AND date BETWEEN %s AND %s{$exclude}
        ORDER BY transaction_id ASC",
        get_current_blog_id(),
        $customer_id,
        $amount,
        $reason,
        gmdate('Y-m-d H:i:s', $time - 300),
        gmdate('Y-m-d H:i:s', $time + 300),
      ),
    );
    if ($credits && count($credits) >= max(1, $competitors)) {
      $used[] = (int) $credits[0];
      $counted[] = $refund->get_id();
      $total += $amount;
    }
  }

  return $total;
}

/**
 * Whether a refund's linked wallet credit is still on this site's wallet ledger.
 *
 * A credit an admin deleted is kept as a row marked deleted, so the customer no longer has it.
 *
 * @param int $transaction_id Wallet credit transaction id.
 * @return bool
 */
function nera_wallet_linked_credit_exists($transaction_id)
{
  global $wpdb;

  return (bool) $wpdb->get_var(
    $wpdb->prepare(
      "SELECT transaction_id FROM {$wpdb->base_prefix}woo_wallet_transactions
      WHERE transaction_id = %d AND blog_id = %d AND type = 'credit' AND deleted = 0",
      (int) $transaction_id,
      get_current_blog_id(),
    ),
  );
}

/**
 * Amount of an order's linked wallet refunds whose wallet credit was later deleted.
 *
 * @param WC_Order $order Order.
 * @return float
 */
function nera_wallet_withdrawn_refunds(WC_Order $order)
{
  $total = 0.0;
  foreach ($order->get_refunds() as $refund) {
    $linked = (int) $refund->get_meta('_nera_wallet_transaction_id');
    if ($linked > 0 && !nera_wallet_linked_credit_exists($linked)) {
      $total += max(0.0, (float) $refund->get_amount());
    }
  }

  return $total;
}

/**
 * The customer's refunds, on any of their orders, made within ten minutes of a time.
 *
 * @param int $customer_id Customer user ID.
 * @param int $timestamp   UTC timestamp.
 * @return WC_Order_Refund[]
 */
function nera_wallet_customer_refunds_near($customer_id, $timestamp)
{
  $refunds = wc_get_orders([
    'type' => 'shop_order_refund',
    'date_created' => ($timestamp - 600) . '...' . ($timestamp + 600),
    'limit' => -1,
  ]);

  return array_filter($refunds, function ($refund) use ($customer_id) {
    $parent = wc_get_order($refund->get_parent_id());
    return $parent && (int) $parent->get_customer_id() === (int) $customer_id;
  });
}

/**
 * Amount of an order's Wallet Contribution already refunded to the wallet by hand.
 *
 * @param WC_Order $order  Order.
 * @param float    $amount Wallet Contribution.
 * @return float At most `$amount`.
 */
function nera_wallet_already_returned(WC_Order $order, $amount)
{
  if ($order->get_meta('_woo_wallet_partial_payment_refunded')) {
    return (float) $amount;
  }

  $returned = (float) $order->get_meta('_nera_wallet_refunded_total');
  if (!$order->get_date_paid()) {
    // The running total also holds refunds whose wallet credit was deleted since.
    $returned = max(0.0, $returned - nera_wallet_withdrawn_refunds($order));
    $returned = max($returned, nera_wallet_refunds_to_wallet($order));
  }

  return min($returned, (float) $amount);
}

/**
 * Note that the restore was lowered by what the wallet already got back.
 *
 * @param WC_Order $order     Order.
 * @param float    $amount    Wallet Contribution.
 * @param float    $remaining Amount still to restore.
 * @param float    $returned  Amount already refunded to the wallet.
 * @return void
 */
function nera_wallet_add_reduced_note(WC_Order $order, $amount, $remaining, $returned)
{
  $price_args = function_exists('woo_wallet_wc_price_args') ? woo_wallet_wc_price_args($order->get_customer_id()) : [];
  $order->add_order_note(
    sprintf(
      /* translators: 1: Wallet Contribution, 2: amount still to restore, 3: amount already refunded to the wallet */
      __('Wallet restore reduced from %1$s to %2$s: %3$s was already refunded to the customer wallet.', 'nera-competitions'),
      wc_price($amount, $price_args),
      wc_price($remaining, $price_args),
      wc_price($returned, $price_args),
    ),
  );
}

/**
 * Mark the Wallet Contribution as fully returned, so no later cancel or fail restores it.
 *
 * TeraWallet and the failed handler skip a zero amount without clearing their flag.
 *
 * @param WC_Order $order Order.
 * @return void
 */
function nera_wallet_close_restore(WC_Order $order)
{
  $order->delete_meta_data('_partial_pay_through_wallet_compleate');
  $order->update_meta_data('_woo_wallet_partial_payment_refunded', true);
  $order->save();
}

/**
 * Remaining Wallet Contribution per order while TeraWallet's cancel restore runs.
 *
 * @return array<int, float>
 */
function &nera_wallet_restore_caps()
{
  static $caps = [];
  return $caps;
}

/**
 * Before TeraWallet restores the Wallet Contribution on cancel, cap it at what is left.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function nera_wallet_cap_cancel_restore($order_id)
{
  if (
    !nera_wallet_is_active() ||
    !function_exists('get_order_partial_payment_amount') ||
    !nera_wallet_refund_guard_enabled()
  ) {
    return;
  }

  $order = wc_get_order($order_id);
  if (!$order || !$order->get_meta('_partial_pay_through_wallet_compleate')) {
    return;
  }

  $amount = (float) get_order_partial_payment_amount($order->get_id());
  $returned = $amount > 0 ? nera_wallet_already_returned($order, $amount) : 0.0;
  if ($returned <= 0) {
    return;
  }

  $remaining = (float) wc_format_decimal(max(0, $amount - $returned), wc_get_price_decimals());
  $caps = &nera_wallet_restore_caps();
  $caps[$order->get_id()] = $remaining;

  nera_wallet_add_reduced_note($order, $amount, $remaining, $returned);
  if ($remaining <= 0) {
    nera_wallet_close_restore($order);
  }
}
add_action('woocommerce_order_status_cancelled', 'nera_wallet_cap_cancel_restore', 9, 1);

/**
 * @param int $order_id Order ID.
 * @return void
 */
function nera_wallet_end_cancel_restore($order_id)
{
  $caps = &nera_wallet_restore_caps();
  unset($caps[(int) $order_id]);
}
add_action('woocommerce_order_status_cancelled', 'nera_wallet_end_cancel_restore', 11, 1);

/**
 * @param float $amount   Wallet Contribution.
 * @param int   $order_id Order ID.
 * @return float
 */
function nera_wallet_capped_partial_payment_amount($amount, $order_id)
{
  $caps = &nera_wallet_restore_caps();
  return $caps[(int) $order_id] ?? $amount;
}
add_filter('woo_wallet_order_partial_payment_amount', 'nera_wallet_capped_partial_payment_amount', 20, 2);

/**
 * Safety net: credit Wallet Balance when a debit is known but no order exists (ADR 0006).
 *
 * Normal Partial Payment always debits after order creation; call this only for recovery.
 *
 * @param int    $user_id Buyer user ID.
 * @param float  $amount  Wallet Contribution to return.
 * @param string $note    Transaction note.
 * @return int|false Transaction id or false.
 */
function nera_wallet_refund_orphan_debit($user_id, $amount, $note = '')
{
  if (!nera_wallet_is_active() || $user_id <= 0 || $amount <= 0) {
    return false;
  }

  if ('' === $note) {
    $note = __('Wallet refund (no order found)', 'nera-competitions');
  }

  return woo_wallet()->wallet->credit((int) $user_id, (float) $amount, $note);
}
