<?php
/**
 * WooCommerce PayPal Payments — checkout integration adjustments
 *
 * The plugin's Smart Button, by default, renders in gold and sits above the
 * terms checkbox (hooked to woocommerce_review_order_after_payment). Neither
 * matches the rest of this theme's checkout, where every gateway shares one
 * position and look for its call-to-action. See ADR 0004.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Render the PayPal Smart Button in the same slot as the theme's Place Order
 * button (below the terms checkbox) instead of the plugin's default spot
 * right after the gateway list.
 *
 * @param string $hook Default action name the button renders on.
 * @return string
 */
function nera_ppcp_checkout_button_renderer_hook(string $hook): string
{
  return 'woocommerce_review_order_before_submit';
}
add_filter(
  'woocommerce_paypal_payments_checkout_button_renderer_hook',
  'nera_ppcp_checkout_button_renderer_hook',
);

/**
 * Restyle the Smart Button on the checkout page only, to blend with the
 * theme's primary color instead of PayPal's default gold. PayPal's SDK only
 * offers a fixed palette (gold/blue/silver/white/black) with no gradient or
 * custom corner radius, so "blue" — the closest hue to --color-primary — is
 * as close a match as the SDK allows.
 *
 * @param array $data Localized script data passed to the Smart Button JS.
 * @return array
 */
function nera_ppcp_restyle_checkout_button(array $data): array
{
  if (($data['context'] ?? '') === 'checkout' && isset($data['button']['style'])) {
    $data['button']['style']['color'] = 'blue';
  }

  return $data;
}
add_filter('woocommerce_paypal_payments_localized_script_data', 'nera_ppcp_restyle_checkout_button');

/**
 * Let the PayPal checkout description be edited from Theme Settings →
 * WooCommerce → PayPal Info instead of only from the gateway's own settings.
 *
 * PayPalGateway overrides get_description() and never fires WooCommerce core's
 * `woocommerce_gateway_description` — the hook the CashFlows copy uses — so the
 * plugin's own filter is the only one that reaches this text. It runs both in
 * the gateway constructor and on every get_description() call, which is what
 * the theme's checkout/payment-method.php override reads.
 *
 * An empty field means "no override": the gateway keeps whatever is configured
 * under WooCommerce → Settings → Payments → PayPal, so the ACF tab never has to
 * be filled in for checkout to read correctly.
 *
 * @param string $description Gateway description (already run through wp_kses_post).
 * @return string
 */
function nera_ppcp_gateway_description($description)
{
  if (!function_exists('get_field')) {
    return $description;
  }

  $custom = trim((string) get_field('paypal_gateway_description', 'option'));

  if ('' === $custom) {
    return $description;
  }

  // Matches what the plugin does to its own value before handing it to the filter.
  return wp_kses_post($custom);
}
add_filter('woocommerce_paypal_payments_gateway_description', 'nera_ppcp_gateway_description');
