<?php
/**
 * ACF Theme Settings — store controls on the top-level Theme Settings page.
 *
 * One field group, "WooCommerce", on `admin.php?page=theme-settings`:
 *
 *   Basket Hold (minutes)                 — ADR 0009
 *   Show buyer emails on Giveaway lists   — ADR 0010
 *
 * Both were previously registered on WooCommerce → Settings → General and have moved here
 * so operators find the theme's store controls in one place. The option rows changed shape
 * with them (`options_*` rather than the bare `nera_*` keys), so the values are always read
 * through nera_basket_hold_minutes() / nera_show_giveaway_buyer_emails(), which fall back to
 * the legacy rows until the page is saved. Do not read these fields directly.
 *
 * Field names deliberately match the legacy option keys, so the ACF rows land on
 * `options_nera_basket_hold_minutes` / `options_nera_show_giveaway_buyer_emails` — adjacent
 * to the originals and impossible to confuse with them.
 *
 * The two settings are unrelated to each other (a cart timer and a wp-admin list toggle), so
 * each field carries its full context in its own instructions rather than relying on a group
 * description that could only honestly describe one of them.
 *
 * @package Nera_Competitions
 * @see docs/adr/0009-basket-hold-lty-reserve-woo-setting.md
 * @see docs/adr/0010-giveaway-buyer-email-visibility.md
 */

if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
  // Ensure the shared Theme Settings parent exists (any of several files may create it).
  if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
    acf_add_options_page([
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
    ]);
  }
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

/**
 * WooCommerce.
 *
 * Negative menu_order so this sits above Result Screens (which registers at the default 0)
 * — store settings belong above result-screen copy.
 */
acf_add_local_field_group([
  'key' => 'group_nera_theme_settings_woocommerce',
  'title' => 'WooCommerce',

  'fields' => [
    [
      'key' => 'field_nera_basket_hold_minutes',
      'label' => 'Basket Hold (minutes)',
      'name' => 'nera_basket_hold_minutes',
      'type' => 'number',
      'instructions' =>
        'How long picked ticket numbers stay reserved in the cart before that line is removed (1–30). Only applies to Competitions where Ticket Generation Type is “User Chooses the Ticket” — automatic ticket Competitions are not timed. Set to 0 to disable Basket Hold entirely: no reserve, no countdown, no auto-remove. Default 5.',
      'required' => 0,
      'min' => 0,
      'max' => 30,
      'step' => 1,
      'default_value' => 5,
      'placeholder' => '5',
      'append' => '',
      'wrapper' => [
        'width' => '',
        'class' => 'nera-acf-field--compact-minutes',
        'id' => '',
      ],
    ],
    [
      'key' => 'field_nera_show_giveaway_buyer_emails',
      'label' => 'Show buyer emails on Giveaway lists',
      'name' => 'nera_show_giveaway_buyer_emails',
      'type' => 'true_false',
      'instructions' =>
        'Whether buyer email addresses appear beside usernames on the Giveaway → View screen (Tickets, Winners, and Instant Win Prizes). Show = the email appears next to the username. Hide = it is removed from those lists (default). Does not change Export CSV, order links, or the billing-name tooltip.',
      'required' => 0,
      'ui' => 1,
      'ui_on_text' => 'Show',
      'ui_off_text' => 'Hide',
      'default_value' => 0,
      'wrapper' => [
        'width' => '',
        'class' => 'nera-acf-field--toggle',
        'id' => '',
      ],
    ],
  ],

  'location' => [
    [
      [
        'param' => 'options_page',
        'operator' => '==',
        'value' => 'theme-settings',
      ],
    ],
  ],

  'menu_order' => -2,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);

/**
 * Small square Basket Hold number input on Theme Settings (no ACF append chip).
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function nera_theme_settings_woocommerce_admin_css($hook_suffix)
{
  // Options pages vary by menu depth; the query arg is the stable gate.
  $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  if ('theme-settings' !== $page) {
    return;
  }

  wp_register_style('nera-theme-settings-woocommerce', false, [], null);
  wp_enqueue_style('nera-theme-settings-woocommerce');
  wp_add_inline_style(
    'nera-theme-settings-woocommerce',
    '.acf-field.nera-acf-field--compact-minutes .acf-input-wrap{'
    . 'width:auto;'
    . 'max-width:5rem;'
    . '}'
    . '.acf-field.nera-acf-field--compact-minutes .acf-input-wrap input[type="number"]{'
    . 'width:5rem !important;'
    . 'max-width:5rem !important;'
    . 'border-radius:0 !important;'
    . '}'
  );
}
add_action('admin_enqueue_scripts', 'nera_theme_settings_woocommerce_admin_css');
