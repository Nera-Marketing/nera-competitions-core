<?php
/**
 * Giveaway view screen (wp-admin) — optional hide of buyer emails beside usernames.
 *
 * On Giveaway → View (`?page=lty_lottery&lty_action=view&product_id=…`) Lottery for
 * WooCommerce prints `username (email@example.com)` in three separate places:
 *
 *   1. Giveaway Tickets   — inc/admin/menu/wp-list-table/class-lty-lottery-ticket-list-table.php:470
 *   2. Giveaway Winners   — inc/admin/menu/views/html-lottery-winner-details.php:33
 *   3. Instant Win Prizes — inc/admin/menu/wp-list-table/class-lty-lottery-instant-winners-list-table.php:797
 *
 * None of those are filterable: the cells are built inside `column_default()` and
 * inline view markup with no hook, and the underlying `get_user_email()` reads meta
 * that abstract-lty-post.php:197 bulk-loads via `get_post_meta( $id )` (no key), so
 * there is no per-key `get_post_metadata` interception point either. The addresses
 * are therefore removed on the client when Giveaway Buyer Email Visibility is off.
 *
 * Deliberately left alone (even when emails are hidden in the cells):
 *   - The `Billing name:` tooltip on the tickets cell — admins need it to tie a
 *     ticket back to a real person when reconciling a draw or a disputed order.
 *   - Export CSV (its own `lty_user_email` column) and the per-row order links.
 *     Those are the escape hatches when an address is genuinely needed, which is why
 *     this applies to every role that can reach the screen rather than rendering
 *     differently for administrators and shop managers.
 *
 * This is display-layer only: the address is gone from the DOM but still present in
 * the raw HTML response. It is a clarity measure, not a data-minimisation control.
 *
 * WooCommerce → Settings → General → “Giveaway admin privacy” owns the Yes/No.
 * Child themes can still force-show with:
 *
 *     add_filter('nera_hide_giveaway_ticket_emails', '__return_false');
 *
 * @package Nera_Competitions
 * @see docs/adr/0010-giveaway-buyer-email-visibility.md
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/** Option key: Woo checkbox 'yes' = show emails on Giveaway → View lists. */
const NERA_SHOW_GIVEAWAY_BUYER_EMAILS_OPTION = 'nera_show_giveaway_buyer_emails';

/**
 * Whether buyer emails should appear beside usernames on Giveaway → View lists.
 *
 * Driven by WooCommerce → Settings → General → Show buyer emails on Giveaway lists.
 * Default: no (hidden).
 *
 * @return bool
 */
function nera_show_giveaway_buyer_emails()
{
  return 'yes' === get_option(NERA_SHOW_GIVEAWAY_BUYER_EMAILS_OPTION, 'no');
}

/**
 * Whether the client redaction script should run (inverse of show setting).
 *
 * @return bool
 */
function nera_hide_giveaway_buyer_emails()
{
  return !nera_show_giveaway_buyer_emails();
}

/**
 * Add Giveaway admin privacy section immediately after other General settings
 * (Basket Hold registers earlier, so this section appears below it).
 *
 * @param array $settings General settings fields.
 * @return array
 */
function nera_giveaway_email_visibility_general_settings($settings)
{
  $section = [
    [
      'title' => __('Giveaway admin privacy', 'nera-competitions'),
      'type' => 'title',
      'desc' => __(
        'Controls whether buyer email addresses appear beside usernames on the Giveaway → View screen (Tickets, Winners, and Instant Win Prizes). Does not change Export CSV, order links, or the billing-name tooltip.',
        'nera-competitions',
      ),
      'id' => 'nera_giveaway_admin_privacy_options',
    ],
    [
      'title' => __('Show buyer emails on Giveaway lists', 'nera-competitions'),
      'desc' => __(
        'Yes = show email next to the username. No = hide it (default).',
        'nera-competitions',
      ),
      'id' => NERA_SHOW_GIVEAWAY_BUYER_EMAILS_OPTION,
      'type' => 'checkbox',
      'default' => 'no',
      'autoload' => true,
      'desc_tip' => true,
    ],
    [
      'type' => 'sectionend',
      'id' => 'nera_giveaway_admin_privacy_options',
    ],
  ];

  return array_merge($settings, $section);
}
add_filter('woocommerce_general_settings', 'nera_giveaway_email_visibility_general_settings');

/**
 * Is the current request the lottery plugin's single-giveaway view screen?
 *
 * Mirrors the plugin's own gate (class-lty-menu-management.php:228), which reads
 * `lty_action` from `$_REQUEST` via `sanitize_title()`.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return bool
 */
function nera_is_giveaway_view_screen($hook_suffix)
{
  if ('toplevel_page_lty_lottery' !== $hook_suffix) {
    return false;
  }

  // Read-only display gate — no state change, so no nonce is required here.
  $action = empty($_REQUEST['lty_action'])
    ? ''
    : sanitize_title(wp_unslash($_REQUEST['lty_action'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

  return 'view' === $action;
}

/**
 * Enqueue the email-hiding assets on the giveaway view screen only.
 *
 * The script loads in `<head>` (`$in_footer = false`) on purpose — it adds the guard
 * class that hides the affected cells before the tables paint, so no address is
 * briefly visible. The CSS goes inline against `wp-admin` rather than into its own
 * stylesheet, matching inc/catalog-order.php.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return void
 */
function nera_enqueue_giveaway_ticket_privacy($hook_suffix)
{
  if (!nera_is_giveaway_view_screen($hook_suffix)) {
    return;
  }

  /**
   * Whether to hide buyer email addresses on the giveaway view screen.
   *
   * Default follows Giveaway Buyer Email Visibility (Woo setting). Return false to
   * force-show (e.g. child theme).
   *
   * @since 1.2.8
   * @param bool $hide True when emails should be redacted in the User Name cells.
   */
  if (!apply_filters('nera_hide_giveaway_ticket_emails', nera_hide_giveaway_buyer_emails())) {
    return;
  }

  $file = get_template_directory() . '/assets/js/admin-giveaway-ticket-privacy.js';
  if (!file_exists($file)) {
    return;
  }

  wp_enqueue_script(
    'nera-admin-giveaway-ticket-privacy',
    get_template_directory_uri() . '/assets/js/admin-giveaway-ticket-privacy.js',
    [],
    filemtime($file),
    false
  );

  // Hide the affected cells for as long as the guard class is set on <html>. The
  // script adds it while the head parses and removes it once the cells are rewritten,
  // so if the script never runs nothing is hidden. `visibility` rather than `display`
  // keeps the table from reflowing when the addresses go.
  wp_add_inline_style(
    'wp-admin',
    // Giveaway Tickets + Instant Win Prizes both use the `user_details` column.
    'html.nera-redacting-ticket-emails td.column-user_details,'
    // Giveaway Winners has no class on the cell and its only other hook is the
    // translated `data-title`, so it is targeted positionally. `user_name` is always
    // the 2nd column of that table (`id`, `user_name`, …), with or without `answer`.
    . 'html.nera-redacting-ticket-emails #lty-view-winner-log tbody td:nth-child(2){'
    . 'visibility:hidden;'
    . '}'
  );
}
add_action('admin_enqueue_scripts', 'nera_enqueue_giveaway_ticket_privacy');
