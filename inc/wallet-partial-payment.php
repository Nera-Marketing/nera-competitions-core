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
 * Return a Wallet Contribution to Wallet Balance for an order (WooCommerce refund path).
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

  if ($order->get_meta('_woo_wallet_partial_payment_refunded')) {
    return false;
  }

  $transaction_id = woo_wallet()->wallet->credit(
    $order->get_customer_id(),
    $amount,
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

  $order->add_order_note(
    sprintf(
      /* translators: %s: refunded amount */
      __('Wallet amount %s credited to customer after card payment failure.', 'nera-competitions'),
      wc_price($amount, woo_wallet_wc_price_args($order->get_customer_id())),
    ),
  );
  $order->delete_meta_data('_partial_pay_through_wallet_compleate');
  $order->update_meta_data('_woo_wallet_partial_payment_refunded', true);
  $order->update_meta_data('_partial_payment_refund_id', $transaction_id);
  $order->save();

  return true;
}

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
