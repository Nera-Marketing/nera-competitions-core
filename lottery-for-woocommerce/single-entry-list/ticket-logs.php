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
  <?php lty_get_template('single-product/tabs/ticket-logs-layout.php', lty_prepare_lottery_entry_list_ticket_log_arguments($product)); ?>
