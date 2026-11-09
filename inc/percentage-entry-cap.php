<?php
/**
 * Ticket cap per person as a percentage of the drop (Percentage cap).
 *
 * Lottery for WooCommerce caps how many tickets one customer may hold with the
 * "Maximum Tickets per User" product field (`_lty_user_maximum_tickets`) and checks it
 * on add to cart, in the cart and on the product page through
 * WC_Product_Lottery::get_lty_user_maximum_tickets(). This module adds a site switch
 * that replaces that fixed number with a share of the drop's total tickets.
 *
 * While the switch is on, the getter returns max(1, floor(percent x total tickets / 100)),
 * so every LFW check and every theme screen that reads the getter follows the new cap
 * without editing the plugin. While it is off, nothing here runs and the saved fixed
 * number is used as before. The fixed number is never overwritten, so switching off
 * restores the old behaviour.
 *
 *   - Theme Settings -> WooCommerce -> "Entry cap per person": the switch and the
 *     site-wide default percentage (integer, 1 to 100).
 *   - Giveaway tab of each product: an optional percentage under "Maximum Tickets per
 *     User". Empty means "use the site-wide default". The fixed field is locked
 *     (read-only, so it still posts and the saved number survives).
 *   - Drops with no ticket total (0) keep their fixed cap. Closed drops are not checked
 *     by LFW, so they are unaffected.
 *   - Tickets already bought are never removed. A customer over the new cap simply
 *     cannot buy more.
 *
 * @package Nera_Competitions
 * @see CONTEXT.md "Percentage cap"
 */

if (!defined('ABSPATH')) {
  exit();
}

/** ACF field name of the site switch (stored as option `options_nera_pct_cap_enabled`). */
const NERA_PCT_CAP_SWITCH = 'nera_pct_cap_enabled';

/** ACF field name of the site-wide default percentage. */
const NERA_PCT_CAP_DEFAULT = 'nera_pct_cap_default';

/** Percentage used when the switch is on but no default has been saved yet. */
const NERA_PCT_CAP_FALLBACK = 10;

/** Product meta holding the per-product percentage ('' = use the site-wide default). */
const NERA_PCT_CAP_META = '_nera_user_max_tickets_percent';

/**
 * Whether the Percentage cap is switched on for the site.
 *
 * Reads the raw option so an unsaved settings page counts as off.
 */
function nera_pct_cap_enabled(): bool
{
  return (bool) (int) get_option('options_' . NERA_PCT_CAP_SWITCH, 0);
}

/**
 * Clamp a value to a whole percentage from 1 to 100. Returns 0 for empty or non-numeric input.
 *
 * @param mixed $value Raw value.
 */
function nera_pct_cap_sanitize($value): int
{
  if ('' === $value || null === $value || !is_numeric($value)) {
    return 0;
  }

  return max(1, min(100, (int) round((float) $value)));
}

/** Site-wide default percentage. */
function nera_pct_cap_default(): int
{
  $stored = nera_pct_cap_sanitize(get_option('options_' . NERA_PCT_CAP_DEFAULT, ''));

  return $stored ?: NERA_PCT_CAP_FALLBACK;
}

/**
 * Percentage that applies to a product: its own value, else the site-wide default.
 *
 * @param int $product_id Product ID.
 */
function nera_pct_cap_for_product(int $product_id): int
{
  $own = nera_pct_cap_sanitize(get_post_meta($product_id, NERA_PCT_CAP_META, true));

  return $own ?: nera_pct_cap_default();
}

/**
 * Tickets one customer may hold for a drop of $total tickets at $percent.
 *
 * Rounded down, never below one ticket.
 */
function nera_pct_cap_tickets(int $percent, int $total): int
{
  return max(1, (int) floor($percent * $total / 100));
}

/**
 * Swap the fixed per-user cap for the percentage cap on the 'view' context.
 *
 * The 'edit' context (the product admin screen and saves) still returns the stored
 * number, so the fixed field keeps showing and saving the real value.
 *
 * @param mixed      $value   Stored fixed cap.
 * @param WC_Product $product Product.
 * @return mixed
 */
function nera_pct_cap_filter_user_maximum($value, $product)
{
  if (!nera_pct_cap_enabled() || !$product instanceof WC_Product || !method_exists($product, 'get_lty_maximum_tickets')) {
    return $value;
  }

  $total = (int) $product->get_lty_maximum_tickets();
  if ($total <= 0) {
    return $value;
  }

  return nera_pct_cap_tickets(nera_pct_cap_for_product($product->get_id()), $total);
}
add_filter('woocommerce_product_get_lty_user_maximum_tickets', 'nera_pct_cap_filter_user_maximum', 10, 2);

/**
 * Theme Settings -> WooCommerce fields (merged in inc/acf/woocommerce/acf-woocommerce.php).
 *
 * @return array<int, array<string, mixed>>
 */
function nera_pct_cap_settings_fields(): array
{
  $on = [[['field' => 'field_nera_pct_cap_enabled', 'operator' => '==', 'value' => '1']]];

  return [
    [
      'key' => 'field_nera_pct_cap_accordion',
      'label' => 'Entry cap per person',
      'name' => '',
      'type' => 'accordion',
      'placement' => 'top',
      'open' => 0,
      'multi_expand' => 0,
      'endpoint' => 0,
    ],
    [
      'key' => 'field_nera_pct_cap_enabled',
      'label' => 'Enable percentage cap per person',
      'name' => NERA_PCT_CAP_SWITCH,
      'type' => 'true_false',
      'instructions' => 'While on, the most tickets one person can hold for a drop is a percentage of that drop\'s total tickets (rounded down, at least 1) instead of the fixed "Maximum Tickets per User". Applies to every open drop. Tickets already bought are kept. Turn off to go back to the fixed number saved on each product.',
      'default_value' => 0,
      'ui' => 1,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
      'wrapper' => ['width' => '', 'class' => 'nera-acf-field--toggle', 'id' => ''],
    ],
    [
      'key' => 'field_nera_pct_cap_default',
      'label' => 'Default percentage per person',
      'name' => NERA_PCT_CAP_DEFAULT,
      'type' => 'number',
      'instructions' => 'Whole number from 1 to 100. Used by every drop that does not set its own percentage. For example 10 on a drop of 1000 tickets lets one person hold up to 100.',
      'required' => 1,
      'default_value' => NERA_PCT_CAP_FALLBACK,
      'min' => 1,
      'max' => 100,
      'step' => 1,
      'append' => '%',
      'conditional_logic' => $on,
      'wrapper' => ['width' => '', 'class' => 'nera-pct-cap-default', 'id' => ''],
    ],
  ];
}

/**
 * Theme Settings -> WooCommerce: keep the default percentage input short with its "%"
 * joined to it, instead of a full-width input and a detached suffix.
 */
function nera_pct_cap_settings_css(): void
{
  $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  if ('acf-options-woocommerce' !== $page) {
    return;
  }

  wp_add_inline_style(
    'wp-admin',
    // ACF prints the suffix before the input and floats it right; reverse the row so it sits after.
    '.acf-field.nera-pct-cap-default .acf-input{display:flex;flex-direction:row-reverse;justify-content:flex-end;align-items:stretch;}'
    . '.acf-field.nera-pct-cap-default .acf-input-wrap{width:auto;max-width:none;}'
    . '.acf-field.nera-pct-cap-default .acf-input-wrap input[type="number"]{width:6rem !important;max-width:6rem !important;border-radius:3px 0 0 3px !important;}'
    . '.acf-field.nera-pct-cap-default .acf-input-append{position:static;float:none;display:flex;align-items:center;margin:0;border-radius:0 3px 3px 0;}'
  );
}
add_action('admin_enqueue_scripts', 'nera_pct_cap_settings_css');

/**
 * Per-product percentage field, printed at the end of the Giveaway panel (LFW has no
 * hook after the Maximum Tickets per User field). admin-percentage-entry-cap.js moves
 * it directly under that field.
 */
function nera_pct_cap_render_product_field(): void
{
  global $post;

  if (!$post || !nera_pct_cap_enabled()) {
    return;
  }

  $default = nera_pct_cap_default();

  woocommerce_wp_text_input([
    'id' => NERA_PCT_CAP_META,
    'label' => __('Maximum Tickets per User (%)', 'nera-competitions'),
    'value' => get_post_meta($post->ID, NERA_PCT_CAP_META, true),
    'type' => 'number',
    'wrapper_class' => 'nera-pct-cap-field',
    'placeholder' => sprintf(
      /* translators: %d: site-wide default percentage */
      __('Default: %d%%', 'nera-competitions'),
      $default
    ),
    'custom_attributes' => ['step' => '1', 'min' => '1', 'max' => '100'],
    'desc_tip' => true,
    'description' => sprintf(
      /* translators: %d: site-wide default percentage */
      __('The most tickets one person can hold, as a whole-number percentage (1 to 100) of this giveaway\'s total tickets. Leave empty to use the site default (%d%%). Replaces the fixed number above while the percentage cap is on.', 'nera-competitions'),
      $default
    ),
  ]);
}
add_action('woocommerce_product_options_lottery_product_data', 'nera_pct_cap_render_product_field');

/**
 * Save the per-product percentage. An empty or invalid value clears it (use the default).
 *
 * @param int $post_id Product ID.
 */
function nera_pct_cap_save_product_field($post_id): void
{
  // WooCommerce verifies the nonce and capability before firing this hook.
  if (!isset($_POST[NERA_PCT_CAP_META])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    return;
  }

  $percent = nera_pct_cap_sanitize(wc_clean(wp_unslash($_POST[NERA_PCT_CAP_META]))); // phpcs:ignore WordPress.Security.NonceVerification.Missing

  if ($percent) {
    update_post_meta($post_id, NERA_PCT_CAP_META, $percent);
  } else {
    delete_post_meta($post_id, NERA_PCT_CAP_META);
  }
}
add_action('woocommerce_process_product_meta', 'nera_pct_cap_save_product_field');

/**
 * Product edit screen: move the percentage field under the fixed field and lock the
 * fixed field while the percentage cap is on.
 */
function nera_pct_cap_admin_assets(string $hook_suffix): void
{
  if (!in_array($hook_suffix, ['post.php', 'post-new.php'], true) || 'product' !== get_post_type() || !nera_pct_cap_enabled()) {
    return;
  }

  $file = get_template_directory() . '/assets/js/admin-percentage-entry-cap.js';
  if (!file_exists($file)) {
    return;
  }

  wp_enqueue_script(
    'nera-admin-percentage-entry-cap',
    get_template_directory_uri() . '/assets/js/admin-percentage-entry-cap.js',
    ['jquery'],
    filemtime($file),
    true
  );

  wp_localize_script('nera-admin-percentage-entry-cap', 'neraPctCap', [
    'note' => __('Not used while the percentage cap is on (Theme Settings > WooCommerce). The saved number is kept.', 'nera-competitions'),
  ]);

  wp_add_inline_style(
    'wp-admin',
    '#_lty_user_maximum_tickets.nera-pct-cap-locked{background:#f0f0f1;color:#8c8f94;cursor:not-allowed;}'
    . '.nera-pct-cap-note{color:#8c8f94;font-style:italic;}'
  );
}
add_action('admin_enqueue_scripts', 'nera_pct_cap_admin_assets');
