<?php
/**
 * Entry List Ticket Logs — Theme Override
 *
 * Overrides: lottery-for-woocommerce/templates/single-entry-list/ticket-logs.php
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}
?>

  <h3 class="mb-4 text-lg font-bold text-text-primary">
    <?php esc_html_e('Ticket Logs', 'lottery-for-woocommerce'); ?>
  </h3>
  <?php if (function_exists('nera_entry_list_is_published') && !nera_entry_list_is_published($product)): ?>
    <p class="rounded-xl border border-gray-200 bg-secondary py-6 px-4 text-center text-sm text-text-secondary">
      <?php esc_html_e('The full entry list is published when this drop closes.', 'nera-competitions'); ?>
    </p>
  <?php else: ?>
    <?php lty_get_template('single-product/tabs/ticket-logs-layout.php', lty_prepare_lottery_entry_list_ticket_log_arguments($product)); ?>
  <?php endif; ?>
