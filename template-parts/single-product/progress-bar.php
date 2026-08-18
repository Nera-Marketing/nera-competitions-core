<?php
/**
 * Progress Bar Template Part
 *
 * Visual progress bar showing tickets sold.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;
$lottery_data = isset($args['lottery_data']) ? $args['lottery_data'] : [];

if (!$product) {
  return;
}

$max_tickets = $lottery_data['maxTickets'] ?? 0;
$sold_tickets = $lottery_data['soldTickets'] ?? 0;
$remaining = $lottery_data['remainingTickets'] ?? 0;
$progress = $lottery_data['progress'] ?? 0;

if (!$max_tickets) {
  return;
}

$product_id = $product->get_id();
$show_counter = function_exists('nera_show_tickets_counter')
  ? nera_show_tickets_counter($product_id)
  : true;
$show_bar = function_exists('nera_show_tickets_bar')
  ? nera_show_tickets_bar($product_id)
  : true;

if (!$show_counter && !$show_bar) {
  return;
}

$is_almost_sold_out = $progress >= 90;
$is_low_stock = $remaining <= 50 && $remaining > 0;
?>

<div class="ncs-progress progress-section">
  <?php if ($show_counter || $show_bar): ?>
  <div class="flex items-center justify-between mb-2">
    <div class="flex items-center gap-2">
      <?php if ($show_counter && $is_low_stock): ?>
        <span class="inline-flex items-center gap-1 text-danger text-xs font-bold">
          <span class="material-symbols-outlined text-sm">local_fire_department</span>
          <?php printf(__('Only %d left!', 'nera-competitions'), $remaining); ?>
        </span>
      <?php elseif ($show_counter): ?>
        <span class="text-sm text-text-secondary">
          <?php printf(
            __('%s of %s sold', 'nera-competitions'),
            number_format($sold_tickets),
            number_format($max_tickets),
          ); ?>
        </span>
      <?php endif; ?>
    </div>
    <?php if ($show_bar): ?>
    <span class="text-sm font-bold <?php echo $is_almost_sold_out
      ? 'text-danger'
      : 'text-primary'; ?>">
      <?php echo esc_html($progress); ?>%
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($show_bar): ?>
  <div class="ncs-progress__track relative h-[14px] w-full rounded-full overflow-hidden shadow-inner">
    <div
      class="ncs-progress__fill h-full rounded-full transition-all duration-1000 ease-out<?php echo $is_almost_sold_out
        ? ' ncs-progress__fill--urgent'
        : ''; ?>"
      style="width: 0%;"
      data-progress="<?php echo esc_attr($progress); ?>"
    ></div>
  </div>
  <?php endif; ?>

  <?php if ($show_counter && $remaining > 0 && !$is_low_stock): ?>
    <p class="mt-2 text-xs text-text-secondary text-center">
      <?php printf(__('%s tickets remaining', 'nera-competitions'), number_format($remaining)); ?>
    </p>
  <?php endif; ?>
</div>
