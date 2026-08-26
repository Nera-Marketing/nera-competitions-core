<?php
/**
 * Ticket bundles priced as threshold tiers rather than exact quantities.
 *
 * Lottery for WooCommerce prices its "predefined buttons" on an **exact** quantity
 * match (get_predefined_button_price_by_quantity() in inc/entity/class-lty-lottery-product.php
 * `continue`s unless `absint( $quantity ) === $ticket_quantity`). With bundles at
 * 1/10/20/50, a customer buying 30 tickets pays the undiscounted unit price, and the
 * result is non-monotonic: 49 tickets cost more than 50.
 *
 * This file reprices the cart item so the highest bundle at or below the quantity sets
 * the per-ticket rate. 30 tickets bill at the 20-bundle rate, 60 at the 50-bundle rate.
 *
 * Where this applies: the plugin sets the cart item price once, on
 * `woocommerce_before_calculate_totals` (inc/frontend/class-lty-lottery-cart.php).
 * WooCommerce derives checkout totals and the created order's line items from those
 * same cart totals, so filtering there covers cart, checkout and the order together.
 * There is exactly one `set_price()` call for lottery cart items, so there is no second
 * pricing path to keep in step.
 *
 * The override can only ever *reduce* the price (see nera_lty_bundle_tier_cart_price).
 * A misconfigured bundle must not become a way to overcharge.
 *
 * @package Nera_Competitions
 * @see docs/adr/0011-ticket-bundle-tier-pricing.md
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Bundle tiers for a lottery product, as `quantity threshold => per-ticket price`,
 * ordered highest threshold first so the first match wins.
 *
 * Reads the plugin's own rule set and its own per-ticket maths, so percentage and
 * fixed-price bundles both resolve correctly and an admin edit needs no change here.
 *
 * @param WC_Product|object $product Lottery product.
 * @return array<int,float> Empty when the product has no usable bundles.
 */
function nera_lty_bundle_tiers($product)
{
  if (!is_object($product) || !method_exists($product, 'get_predefined_buttons_rule')) {
    return [];
  }

  if (method_exists($product, 'is_predefined_button_enabled') && !$product->is_predefined_button_enabled()) {
    return [];
  }

  $rules = $product->get_predefined_buttons_rule();
  if (!is_array($rules) || empty($rules)) {
    return [];
  }

  $tiers = [];

  foreach ($rules as $button_id => $rule) {
    $quantity = isset($rule['ticket_quantity']) ? absint($rule['ticket_quantity']) : 0;
    if ($quantity < 1) {
      continue;
    }

    $per_ticket = (float) $product->get_predefined_buttons_per_ticket_amount($button_id);
    if ($per_ticket <= 0) {
      continue;
    }

    // Two bundles on the same threshold: keep the one that favours the customer.
    if (!isset($tiers[$quantity]) || $per_ticket < $tiers[$quantity]) {
      $tiers[$quantity] = $per_ticket;
    }
  }

  if (empty($tiers)) {
    return [];
  }

  krsort($tiers, SORT_NUMERIC);

  return $tiers;
}

/**
 * The per-ticket price a quantity earns under threshold tiers.
 *
 * @param WC_Product|object $product  Lottery product.
 * @param int               $quantity Quantity being bought.
 * @return float|null Null when no tier applies (below the smallest bundle, or none set),
 *                    leaving the plugin's own price untouched.
 */
function nera_lty_bundle_tier_price($product, $quantity)
{
  $quantity = absint($quantity);
  if ($quantity < 1) {
    return null;
  }

  foreach (nera_lty_bundle_tiers($product) as $threshold => $per_ticket) {
    if ($quantity >= $threshold) {
      return $per_ticket;
    }
  }

  return null;
}

/**
 * Reprice a lottery cart item onto its bundle tier.
 *
 * Hooked to the plugin's own extension point, which runs at the moment the price is
 * set — so this rides along with whatever the plugin does around it.
 *
 * @param float $price     Per-ticket price the plugin calculated.
 * @param array $cart_item Cart item being priced.
 * @return float
 */
function nera_lty_bundle_tier_cart_price($price, $cart_item)
{
  if (!is_array($cart_item) || !isset($cart_item['data'], $cart_item['quantity'])) {
    return $price;
  }

  $tiered = nera_lty_bundle_tier_price($cart_item['data'], $cart_item['quantity']);
  if (null === $tiered) {
    return $price;
  }

  // Only ever downwards. If a bundle is configured worse than the plain unit price,
  // the customer keeps the cheaper of the two rather than being penalised for it.
  return min((float) $tiered, (float) $price);
}

/*
 * The plugin's filter tag carries a leading space — `apply_filters(' lty_predefined_..')`
 * in inc/frontend/class-lty-lottery-cart.php. That is a typo on their side, and one they
 * may fix in any release. Both spellings are hooked because the failure mode of missing
 * it is silent: prices would revert to undiscounted and customers would be overcharged
 * with nothing in the logs. Two lines is a cheap price for that not happening.
 */
add_filter(' lty_predefined_button_cart_item_price', 'nera_lty_bundle_tier_cart_price', 10, 2);
add_filter('lty_predefined_button_cart_item_price', 'nera_lty_bundle_tier_cart_price', 10, 2);

/**
 * Feed the tier table to the front end so the displayed unit price and running total
 * agree with what the cart will charge.
 *
 * The plugin has its own quantity/price display code, but it binds to
 * `.lty-participate-now .qty` and writes into `.lty-lottery-price` — selectors this
 * theme's QuantitySelector does not render, so none of it fires here. That is also why
 * a selected bundle stays highlighted after the quantity moves away from it.
 *
 * @return void
 */
function nera_lty_bundle_tier_enqueue()
{
  if (!function_exists('is_product') || !is_product() || !function_exists('lty_is_lottery_product')) {
    return;
  }

  $product = wc_get_product(get_queried_object_id());
  if (!$product || !lty_is_lottery_product($product)) {
    return;
  }

  $tiers = nera_lty_bundle_tiers($product);
  if (empty($tiers)) {
    return;
  }

  $file = get_template_directory() . '/assets/js/lty-bundle-tier-pricing.js';
  if (!file_exists($file)) {
    return;
  }

  wp_enqueue_script(
    'nera-lty-bundle-tier-pricing',
    get_template_directory_uri() . '/assets/js/lty-bundle-tier-pricing.js',
    [],
    filemtime($file),
    true,
  );

  // Highest threshold first, matching the resolution order in PHP.
  $payload = [];
  foreach ($tiers as $threshold => $per_ticket) {
    $payload[] = [
      'qty' => (int) $threshold,
      'per' => (float) $per_ticket,
    ];
  }

  wp_localize_script('nera-lty-bundle-tier-pricing', 'neraBundleTiers', [
    'productId' => (int) $product->get_id(),
    'tiers' => $payload,
    'basePrice' => (float) $product->get_price(),
    'currency' => [
      'symbol' => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
      'decimals' => wc_get_price_decimals(),
      'decimalSep' => wc_get_price_decimal_separator(),
      'thousandSep' => wc_get_price_thousand_separator(),
      'format' => get_woocommerce_price_format(),
    ],
    'i18n' => [
      'total' => __('Total', 'nera-competitions'),
    ],
  ]);
}
add_action('wp_enqueue_scripts', 'nera_lty_bundle_tier_enqueue', 20);
