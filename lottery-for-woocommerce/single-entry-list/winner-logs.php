<?php
/**
 * Entry List Winner Logs — Theme Override
 *
 * Overrides: lottery-for-woocommerce/templates/single-entry-list/winner-logs.php
 *
 * @package Nera_Competitions
 */

defined('ABSPATH') || exit();

if (empty($lottery_winners)) {
  return;
}
?>

<div class="mt-8">
  <h3 class="mb-4 text-lg font-bold text-text-primary">
    <?php echo wp_kses_post(lty_get_single_product_lottery_winner_label()); ?>
  </h3>

  <div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="lty-winner-logs-table shop_table w-full text-sm">
      <thead>
        <tr class="bg-primary text-white">
          <?php foreach ($columns as $column_name): ?>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider first:rounded-tl-xl last:rounded-tr-xl">
              <?php echo esc_html($column_name); ?>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php foreach ($lottery_winners as $key => $lottery_winner_id):
          $winner_log = lty_get_lottery_winner($lottery_winner_id);
        ?>
          <tr class="transition-colors hover:bg-secondary">
            <?php foreach ($columns as $col_key => $val): ?>
              <td class="px-4 py-3 text-text-primary" data-title="<?php echo esc_attr($val); ?>">
                <?php
                switch ($col_key) {
                  case 'username':
                    echo '<span class="font-semibold">' . esc_html(nera_mask_username($winner_log->display_user_name())) . '</span>';
                    break;
                  case 'gift_product':
                    echo wp_kses_post(lty_get_winner_gift_products_title(array_unique($winner_log->get_gift_products()), $product));
                    break;
                  case 'ticket_number':
                    echo '<span class="font-mono font-bold text-primary">' . esc_html($winner_log->get_lottery_ticket_number()) . '</span>';
                    break;
                  case 'answer':
                    echo esc_html($winner_log->get_answer());
                    break;
                }
                ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
