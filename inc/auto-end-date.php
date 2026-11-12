<?php
/**
 * Auto end date: end date = start date + a number of days (Auto end date).
 *
 * Lottery for WooCommerce asks for a Start Date and an End Date on every Limited Duration
 * drop (Giveaway tab, fields `_lty_start_date` and `_lty_end_date`). This module adds a site
 * switch so the End Date is filled for the admin from the Start Date plus a configured
 * number of days (for example 14 for "closes after 14 days, or when full").
 *
 *   - Theme Settings -> WooCommerce -> "Auto end date": the switch and the number of days
 *     (integer, 1 to 365, default 14).
 *   - Product edit screen, Giveaway tab: while the switch is on, picking or typing a Start
 *     Date fills the End Date with the same time of day, N calendar days later. An End Date
 *     the admin has edited by hand is never overwritten, and an existing End Date that does
 *     not match "start + N days" is treated as hand-set from the start.
 *   - On save: if the switch is on, the drop is Limited Duration, a Start Date is posted and
 *     the End Date is empty, the End Date is filled the same way before LFW saves it.
 *   - While the switch is off nothing here runs and the End Date is entered by hand.
 *   - Dates already saved on products are never changed. "Close when all tickets are sold"
 *     (LFW setting) is separate and still closes a drop early when it sells out.
 *
 * Date maths uses the WordPress site timezone (wp_timezone(), Europe/London on this site),
 * adding calendar days so the wall-clock time is kept across the clock change.
 *
 * @package Nera_Competitions
 * @see CONTEXT.md "Auto end date"
 */

if (!defined('ABSPATH')) {
  exit();
}

/** ACF field name of the site switch (stored as option `options_nera_auto_end_enabled`). */
const NERA_AUTO_END_SWITCH = 'nera_auto_end_enabled';

/** ACF field name of the number of days. */
const NERA_AUTO_END_DAYS = 'nera_auto_end_days';

/** Days used when the switch is on but no number has been saved yet. */
const NERA_AUTO_END_FALLBACK = 14;

/** Whether the Auto end date is switched on. Reads the raw option so an unsaved page counts as off. */
function nera_auto_end_enabled(): bool
{
  return (bool) (int) get_option('options_' . NERA_AUTO_END_SWITCH, 0);
}

/**
 * Clamp a value to a whole number of days from 1 to 365. Returns 0 for empty or non-numeric input.
 *
 * @param mixed $value Raw value.
 */
function nera_auto_end_sanitize($value): int
{
  if ('' === $value || null === $value || !is_numeric($value)) {
    return 0;
  }

  return max(1, min(365, (int) round((float) $value)));
}

/** Configured number of days between start and end. */
function nera_auto_end_days(): int
{
  $stored = nera_auto_end_sanitize(get_option('options_' . NERA_AUTO_END_DAYS, ''));

  return $stored ?: NERA_AUTO_END_FALLBACK;
}

/**
 * End date for a start date: same time of day, $days calendar days later, in the site timezone.
 *
 * @param string $start Start date as posted by LFW ("Y-m-d H:i" or "Y-m-d H:i:s", site time).
 * @param int    $days  Number of days to add.
 * @return string "Y-m-d H:i" or '' when the start date cannot be read.
 */
function nera_auto_end_date_from_start(string $start, int $days): string
{
  $start = trim($start);
  if ('' === $start) {
    return '';
  }

  try {
    $date = new DateTimeImmutable($start, wp_timezone());
  } catch (Exception $e) {
    return '';
  }

  return $date->modify('+' . max(1, $days) . ' days')->format('Y-m-d H:i');
}

/**
 * On saving a Limited Duration drop: fill an empty End Date from the Start Date.
 *
 * Runs before LFW's own save (same hook, earlier priority) because LFW reads
 * $_REQUEST['_lty_end_date'].
 *
 * @param int $post_id Product ID.
 */
function nera_auto_end_fill_on_save($post_id): void
{
  // WooCommerce verifies the nonce and capability before firing this hook.
  if (!nera_auto_end_enabled() || !isset($_POST['_lty_start_date'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    return;
  }

  // Extending or relisting a closed drop is handled by LFW with its own dates.
  if (isset($_REQUEST['lty_lottery_extend']) || isset($_REQUEST['lty_lottery_manual_relist'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return;
  }

  $schedule = isset($_POST['lty_lottery_schedule_type']) ? wc_clean(wp_unslash($_POST['lty_lottery_schedule_type'])) : '1'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
  $start = wc_clean(wp_unslash($_POST['_lty_start_date'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing
  $end = isset($_POST['_lty_end_date']) ? wc_clean(wp_unslash($_POST['_lty_end_date'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

  if ('2' === $schedule || '' === $start || '' !== $end) {
    return;
  }

  $filled = nera_auto_end_date_from_start($start, nera_auto_end_days());
  if ('' === $filled) {
    return;
  }

  $_POST['_lty_end_date'] = $filled;
  $_REQUEST['_lty_end_date'] = $filled;
}
add_action('woocommerce_process_product_meta_lottery', 'nera_auto_end_fill_on_save', 5);

/**
 * Theme Settings -> WooCommerce fields (merged in inc/acf/woocommerce/acf-woocommerce.php).
 *
 * @return array<int, array<string, mixed>>
 */
function nera_auto_end_settings_fields(): array
{
  $on = [[['field' => 'field_nera_auto_end_enabled', 'operator' => '==', 'value' => '1']]];

  return [
    [
      'key' => 'field_nera_auto_end_accordion',
      'label' => 'Auto end date',
      'name' => '',
      'type' => 'accordion',
      'placement' => 'top',
      'open' => 0,
      'multi_expand' => 0,
      'endpoint' => 0,
    ],
    [
      'key' => 'field_nera_auto_end_enabled',
      'label' => 'Set the end date from the start date',
      'name' => NERA_AUTO_END_SWITCH,
      'type' => 'true_false',
      'instructions' => 'While on, setting a drop\'s Start Date on the product page fills its End Date with the start plus the number of days below (you can still edit it by hand). Only affects drops you create or edit from now on. Turn off to enter the End Date by hand as before.',
      'default_value' => 0,
      'ui' => 1,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
      'wrapper' => ['width' => '', 'class' => 'nera-acf-field--toggle', 'id' => ''],
    ],
    [
      'key' => 'field_nera_auto_end_days',
      'label' => 'Days from start to end',
      'name' => NERA_AUTO_END_DAYS,
      'type' => 'number',
      'instructions' => 'Whole number from 1 to 365. For example 14 gives a drop that starts on Sunday 7pm an End Date of Sunday 7pm two weeks later.',
      'required' => 1,
      'default_value' => NERA_AUTO_END_FALLBACK,
      'min' => 1,
      'max' => 365,
      'step' => 1,
      'append' => 'days',
      'conditional_logic' => $on,
      'wrapper' => ['width' => '', 'class' => 'nera-auto-end-days', 'id' => ''],
    ],
  ];
}

/**
 * Theme Settings -> WooCommerce: keep the days input short with its "days" suffix joined to
 * it (same treatment as the percentage cap field).
 */
function nera_auto_end_settings_css(): void
{
  $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  if ('acf-options-woocommerce' !== $page) {
    return;
  }

  wp_add_inline_style(
    'wp-admin',
    '.acf-field.nera-auto-end-days .acf-input{display:flex;flex-direction:row-reverse;justify-content:flex-end;align-items:stretch;}'
    . '.acf-field.nera-auto-end-days .acf-input-wrap{width:auto;max-width:none;}'
    . '.acf-field.nera-auto-end-days .acf-input-wrap input[type="number"]{width:6rem !important;max-width:6rem !important;border-radius:3px 0 0 3px !important;}'
    . '.acf-field.nera-auto-end-days .acf-input-append{position:static;float:none;display:flex;align-items:center;margin:0;border-radius:0 3px 3px 0;}'
  );
}
add_action('admin_enqueue_scripts', 'nera_auto_end_settings_css');

/**
 * Product edit screen: load the script that fills the End Date while the switch is on.
 */
function nera_auto_end_admin_assets(string $hook_suffix): void
{
  if (!in_array($hook_suffix, ['post.php', 'post-new.php'], true) || 'product' !== get_post_type() || !nera_auto_end_enabled()) {
    return;
  }

  $file = get_template_directory() . '/assets/js/admin-auto-end-date.js';
  if (!file_exists($file)) {
    return;
  }

  wp_enqueue_script(
    'nera-admin-auto-end-date',
    get_template_directory_uri() . '/assets/js/admin-auto-end-date.js',
    ['jquery', 'jquery-ui-datepicker'],
    filemtime($file),
    true
  );

  $days = nera_auto_end_days();

  wp_localize_script('nera-admin-auto-end-date', 'neraAutoEnd', [
    'days' => $days,
    'note' => sprintf(
      /* translators: %d: number of days */
      _n(
        'Set automatically from the start date + %d day. You can edit it.',
        'Set automatically from the start date + %d days. You can edit it.',
        $days,
        'nera-competitions'
      ),
      $days
    ),
  ]);

  wp_add_inline_style('wp-admin', '.woocommerce_options_panel p.nera-auto-end-note{margin:-4px 0 10px;padding:0 9px 0 162px;color:#8c8f94;font-style:italic;}');
}
add_action('admin_enqueue_scripts', 'nera_auto_end_admin_assets');
