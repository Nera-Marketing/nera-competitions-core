<?php
/**
 * Basket Hold — choose-your-own Ticket cart timer (ADR 0009).
 *
 * Reuses Lottery for WooCommerce’s Reserve Ticket Number. WooCommerce owns the
 * minutes setting; LTY options are filtered to match. Cart/checkout show a live
 * countdown; on expiry a toast fires and that cart line is removed.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Option key for Basket Hold duration in minutes. */
const NERA_BASKET_HOLD_MINUTES_OPTION = 'nera_basket_hold_minutes';

/**
 * Configured Basket Hold duration in minutes (0–30).
 *
 * 0 disables Basket Hold entirely (no LTY reserve, no countdown, no auto-remove).
 * Default when unset: 5.
 *
 * @return int
 */
function nera_basket_hold_minutes()
{
  $mins = (int) get_option(NERA_BASKET_HOLD_MINUTES_OPTION, 5);

  return max(0, min(30, $mins));
}

/**
 * Whether Basket Hold is active (minutes > 0).
 *
 * @return bool
 */
function nera_basket_hold_enabled()
{
  return nera_basket_hold_minutes() > 0;
}

/**
 * Drive LTY reserve enable from our Woo setting.
 *
 * @param mixed $pre Short-circuit value.
 * @return string
 */
function nera_basket_hold_filter_lty_enable($pre)
{
  return nera_basket_hold_enabled() ? 'yes' : 'no';
}
add_filter(
  'pre_option_lty_settings_enable_reserve_ticket_manual_selection_type',
  'nera_basket_hold_filter_lty_enable',
);

/**
 * Drive LTY reserve minutes from our Woo setting.
 *
 * @param mixed $pre Short-circuit value.
 * @return string
 */
function nera_basket_hold_filter_lty_minutes($pre)
{
  return (string) nera_basket_hold_minutes();
}
add_filter(
  'pre_option_lty_settings_reserve_ticket_time_in_min',
  'nera_basket_hold_filter_lty_minutes',
);

/**
 * Add Basket Hold as a global section on WooCommerce → Settings → General.
 *
 * Not a per-product field. Duration applies store-wide; only Competitions with
 * Ticket Generation Type = “User Chooses the Ticket” are held in the cart.
 * Set to 0 to turn Basket Hold off.
 *
 * @param array $settings General settings fields.
 * @return array
 */
function nera_basket_hold_general_settings($settings)
{
  $section = [
    [
      'title' => __('Basket Hold', 'nera-competitions'),
      'type' => 'title',
      'desc' => __(
        'Global timer for choose-your-own ticket numbers reserved in the cart. Only applies to Competitions where Ticket Generation Type is “User Chooses the Ticket”. Automatic ticket Competitions are not timed. Set minutes to 0 to disable Basket Hold.',
        'nera-competitions',
      ),
      'id' => 'nera_basket_hold_options',
    ],
    [
      'title' => __('Basket Hold (minutes)', 'nera-competitions'),
      'desc' => __(
        'How long picked ticket numbers stay reserved before that cart line is removed (1–30). Set to 0 to disable. Default 5.',
        'nera-competitions',
      ),
      'id' => NERA_BASKET_HOLD_MINUTES_OPTION,
      'type' => 'number',
      'custom_attributes' => [
        'min' => 0,
        'max' => 30,
        'step' => 1,
      ],
      'css' => 'width: 80px;',
      'default' => '5',
      'autoload' => true,
      'desc_tip' => true,
    ],
    [
      'type' => 'sectionend',
      'id' => 'nera_basket_hold_options',
    ],
  ];

  return array_merge($settings, $section);
}
add_filter('woocommerce_general_settings', 'nera_basket_hold_general_settings');

/**
 * Unix expiry timestamp for a cart line’s Basket Hold, or 0 if not held.
 *
 * Only Competitions with Ticket Generation Type = User Chooses the Ticket
 * (`is_manual_ticket()` / generation type `2`) can be held. Automatic products return 0.
 * Returns 0 when Basket Hold is disabled (minutes = 0).
 *
 * @param array $cart_item Cart item.
 * @return int
 */
function nera_basket_hold_cart_item_expires_at($cart_item)
{
  if (!nera_basket_hold_enabled()) {
    return 0;
  }

  if (!function_exists('lty_is_reserved_ticket') || !function_exists('lty_get_current_user_cart_session_value')) {
    return 0;
  }

  $product = isset($cart_item['data']) ? $cart_item['data'] : null;
  if (!is_object($product) || !method_exists($product, 'is_manual_ticket') || !$product->is_manual_ticket()) {
    return 0;
  }

  // false = don’t require existing reserved meta yet (settings gate only).
  if (!lty_is_reserved_ticket($product, false)) {
    return 0;
  }

  $tickets = isset($cart_item['lty_lottery']['tickets']) ? $cart_item['lty_lottery']['tickets'] : [];
  if (!is_array($tickets) || empty($tickets)) {
    return 0;
  }

  $customer_id = lty_get_current_user_cart_session_value();
  if (!$customer_id || !method_exists($product, 'get_reserved_ticket_values')) {
    return 0;
  }

  $mins = nera_basket_hold_minutes();
  $earliest = 0;

  foreach ($tickets as $ticket) {
    $reserved = $product->get_reserved_ticket_values($ticket);
    if (!is_array($reserved) || !isset($reserved[$customer_id])) {
      continue;
    }

    $expires = (int) $reserved[$customer_id] + $mins * 60;
    if (!$earliest || $expires < $earliest) {
      $earliest = $expires;
    }
  }

  return $earliest;
}

/**
 * All held cart lines for the current session (for JS).
 *
 * @return array<string, array{expires_at:int, name:string}>
 */
function nera_basket_hold_cart_payload()
{
  $payload = [];

  if (!function_exists('WC') || !WC()->cart) {
    return $payload;
  }

  foreach (WC()->cart->get_cart() as $key => $item) {
    $expires = nera_basket_hold_cart_item_expires_at($item);
    if ($expires <= 0) {
      continue;
    }

    $product = isset($item['data']) ? $item['data'] : null;
    $payload[$key] = [
      'expires_at' => $expires,
      'name' => is_object($product) ? $product->get_name() : '',
    ];
  }

  return $payload;
}

/**
 * AJAX: remove an expired Basket Hold cart line.
 *
 * @return void
 */
function nera_basket_hold_ajax_expire()
{
  check_ajax_referer('nera_basket_hold', 'nonce');

  if (!nera_basket_hold_enabled()) {
    wp_send_json_error(['message' => __('Basket Hold is disabled.', 'nera-competitions')], 400);
  }

  if (!function_exists('WC') || !WC()->cart) {
    wp_send_json_error(['message' => __('Cart unavailable.', 'nera-competitions')], 400);
  }

  $key = isset($_POST['cart_item_key'])
    ? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
    : '';

  if ('' === $key || !isset(WC()->cart->get_cart()[$key])) {
    wp_send_json_success(['removed' => false, 'already_gone' => true]);
  }

  $item = WC()->cart->get_cart()[$key];
  $expires = nera_basket_hold_cart_item_expires_at($item);

  // Allow a small skew so the client timer and server clock agree.
  if ($expires > 0 && time() < $expires - 5) {
    wp_send_json_error(['message' => __('Basket Hold has not expired yet.', 'nera-competitions')], 400);
  }

  $name = isset($item['data']) && is_object($item['data']) ? $item['data']->get_name() : '';
  WC()->cart->remove_cart_item($key);
  WC()->cart->calculate_totals();

  if ($name !== '') {
    wc_add_notice(
      sprintf(
        /* translators: %s: product name */
        __('%s was removed from your cart after Basket Hold expired.', 'nera-competitions'),
        $name
      ),
      'error'
    );
  } else {
    wc_add_notice(
      __('An item was removed from your cart after Basket Hold expired.', 'nera-competitions'),
      'error'
    );
  }

  wp_send_json_success([
    'removed' => true,
    'name' => $name,
    'cart_empty' => WC()->cart->is_empty(),
    'cart_url' => wc_get_cart_url(),
    'checkout_url' => wc_get_checkout_url(),
  ]);
}
add_action('wp_ajax_nera_basket_hold_expire', 'nera_basket_hold_ajax_expire');
add_action('wp_ajax_nopriv_nera_basket_hold_expire', 'nera_basket_hold_ajax_expire');

/**
 * Enqueue Basket Hold countdown on cart and checkout.
 *
 * Sound helper always loads on cart/checkout so a pending sessionStorage chime
 * still plays after reload when no holds remain (countdown script skipped).
 *
 * @return void
 */
function nera_basket_hold_enqueue()
{
  if (!function_exists('is_cart') || (!is_cart() && !(is_checkout() && !is_order_received_page()))) {
    return;
  }

  if (!nera_basket_hold_enabled()) {
    return;
  }

  wp_enqueue_script(
    'nera-basket-hold-sound',
    NERA_ASSETS_URI . '/js/basket-hold-sound.js',
    [],
    NERA_VERSION,
    true,
  );

  $holds = nera_basket_hold_cart_payload();
  if (empty($holds)) {
    return;
  }

  wp_enqueue_style(
    'nera-basket-hold',
    NERA_ASSETS_URI . '/css/basket-hold.css',
    [],
    NERA_VERSION,
  );

  wp_enqueue_script(
    'nera-basket-hold',
    NERA_ASSETS_URI . '/js/basket-hold.js',
    ['jquery', 'nera-basket-hold-sound'],
    NERA_VERSION,
    true,
  );

  wp_localize_script('nera-basket-hold', 'neraBasketHold', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nera_basket_hold'),
    'serverNow' => time(),
    'holds' => $holds,
    'i18n' => [
      'label' => __('Tickets reserved:', 'nera-competitions'),
      'expiredToast' => __(
        'Basket Hold ended — %s will be removed from your cart so others can choose those tickets.',
        'nera-competitions',
      ),
      'removedToast' => __('%s was removed from your cart after Basket Hold expired.', 'nera-competitions'),
    ],
  ]);
}
add_action('wp_enqueue_scripts', 'nera_basket_hold_enqueue', 30);

/**
 * Markup for a cart/checkout Basket Hold countdown (empty until JS paints).
 *
 * @param string $cart_item_key Cart item key.
 * @param array  $cart_item     Cart item.
 * @return string
 */
function nera_basket_hold_countdown_html($cart_item_key, $cart_item)
{
  $expires = nera_basket_hold_cart_item_expires_at($cart_item);
  if ($expires <= 0) {
    return '';
  }

  return sprintf(
    '<div class="nera-basket-hold mt-2 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold tracking-tight" data-nera-basket-hold data-cart-item-key="%1$s" data-expires-at="%2$d" role="timer" aria-live="polite">
      <span class="material-symbols-outlined nera-basket-hold__icon text-sm" aria-hidden="true">timer</span>
      <span class="nera-basket-hold__label">%3$s</span>
      <span class="nera-basket-hold__time tabular-nums"></span>
    </div>',
    esc_attr($cart_item_key),
    (int) $expires,
    esc_html__('Tickets reserved:', 'nera-competitions'),
  );
}
