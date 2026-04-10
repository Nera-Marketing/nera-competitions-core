<?php
/**
 * Ticket Logs Layout — Theme Override
 *
 * Overrides: lottery-for-woocommerce/templates/single-product/tabs/ticket-logs-layout.php
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

// Normalize column order: ticket_number → user_name → date → answer (regardless of which function built the array)
$preferred_order = ['ticket_number', 'user_name', 'date', 'answer'];
$columns_ordered = [];
foreach ($preferred_order as $key) {
  if (isset($columns[$key])) {
    $columns_ordered[$key] = $columns[$key];
  }
}
// Append any extra columns the plugin may add in future
foreach ($columns as $key => $label) {
  if (!isset($columns_ordered[$key])) {
    $columns_ordered[$key] = $label;
  }
}
$columns = $columns_ordered;
?>

<div class="lty-ticket-logs-wrapper lty-data-table-wrapper">

  <div class="mb-4 flex gap-2">
    <input
      type="text"
      class="lty-ticket-logs-search lty-frontend-search flex-1 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary/40"
      value="<?php echo esc_attr($search); ?>"
      placeholder="<?php esc_attr_e('Search by ticket number…', 'lottery-for-woocommerce'); ?>"
    />
    <button
      type="button"
      class="lty-ticket-logs-search-btn lty-frontend-search-btn rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-dark">
      <?php echo esc_html(lty_get_ticket_search_button_label()); ?>
    </button>
    <input type="hidden" class="lty-lottery-product-id" value="<?php echo esc_attr($product->get_id()); ?>" />
  </div>

  <div class="lty-ticket-logs-content-wrapper">
    <?php if (lty_check_is_array($ticket_ids)): ?>

      <div class="overflow-x-auto border border-gray-200">
        <table class="lty-frontend-table shop_table shop_table_responsive lty-ticket-logs-table w-full text-sm">
          <thead>
            <tr class="bg-primary text-white">
              <?php foreach ($columns as $column_name): ?>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                  <?php echo esc_html($column_name); ?>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php
            lty_get_template(
              'single-product/tabs/ticket-logs.php',
              [
                '_columns'   => $columns,
                'ticket_ids' => $ticket_ids,
              ]
            );
            ?>
          </tbody>

          <?php if ($pagination['page_count'] > 1): ?>
            <tfoot>
              <tr>
                <td colspan="3" data-action_name="lty_ticket_logs" class="footable-visible actions px-4 py-3" data-product_id="<?php echo esc_attr($product->get_id()); ?>">
                  <?php lty_get_template('pagination.php', $pagination); ?>
                </td>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>
      </div>

    <?php else: ?>

      <div class="flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-secondary py-10 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-10 w-10 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-sm text-text-secondary">
          <?php esc_html_e('No tickets found.', 'lottery-for-woocommerce'); ?>
        </p>
      </div>

    <?php endif; ?>
  </div>
</div>
