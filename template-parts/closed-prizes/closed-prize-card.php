<?php
/**
 * Closed Prize Card
 *
 * Results-style card for finished/closed competitions.
 * No buy CTA — shows draw date, final ticket stats, and instant-win prize count.
 *
 * @package Nera_Competitions
 * @var array $args
 */

if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;
if (!$product) {
  global $product;
}
if (!$product || !is_a($product, 'WC_Product')) {
  return;
}

$product_id  = $product->get_id();
$image_id    = $product->get_image_id();
$permalink   = get_permalink($product_id);
$title       = get_the_title($product_id);

$max_tickets  = (int) get_post_meta($product_id, '_lty_maximum_tickets', true);
$sold_tickets = method_exists($product, 'get_purchased_ticket_count')
  ? (int) $product->get_purchased_ticket_count()
  : 0;
$progress = $max_tickets > 0 ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 100;

$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
$draw_date    = $end_date_gmt ? nera_format_draw_date($end_date_gmt) : '';

$lottery_status = get_post_meta($product_id, '_lty_lottery_status', true);
$status_labels  = function_exists('lty_get_lottery_statuses') ? lty_get_lottery_statuses() : [];
$status_label   = isset($status_labels[$lottery_status]) && $status_labels[$lottery_status]
  ? $status_labels[$lottery_status]
  : ($lottery_status === 'lty_lottery_finished' ? __('Drawn', 'nera-competitions') : __('Closed', 'nera-competitions'));

$won_count = function_exists('nera_get_closed_lottery_won_instant_prize_count')
  ? nera_get_closed_lottery_won_instant_prize_count($product_id)
  : 0;
?>

<article
  class="ncs-closed-prize-card group bg-surface rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
  data-product-id="<?php echo esc_attr($product_id); ?>">

  <!-- Product Image -->
  <div class="relative aspect-[4/3] overflow-hidden">

    <!-- Status Badge -->
    <div class="absolute top-4 left-4 z-10 bg-gray-500 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
      <?php echo esc_html($status_label); ?>
    </div>

    <!-- Image -->
    <a href="<?php echo esc_url($permalink); ?>" class="block w-full h-full" tabindex="-1" aria-hidden="true">
      <?php if ($image_id): ?>
        <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
        <div
          class="w-full h-full bg-center bg-no-repeat bg-cover transform group-hover:scale-110 transition-transform duration-700 opacity-80"
          style="background-image: url('<?php echo esc_url($image_url); ?>');">
        </div>
      <?php else: ?>
        <div class="w-full h-full flex items-center justify-center bg-gray-100 opacity-80">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            class="text-gray-300">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <polyline points="21 15 16 10 5 21" />
          </svg>
        </div>
      <?php endif; ?>
    </a>
  </div>

  <!-- Card Content -->
  <div class="p-6">

    <!-- Title -->
    <h3 class="text-lg font-bold text-text-primary mb-3 line-clamp-2">
      <a href="<?php echo esc_url($permalink); ?>" class="hover:text-primary transition-colors">
        <?php echo esc_html($title); ?>
      </a>
    </h3>

    <!-- Instant-win prizes awarded chip -->
    <?php if ($won_count > 0): ?>
      <div class="mb-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-success-bg text-success-text rounded-full text-xs font-semibold">
          <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1">check_circle</span>
          <?php
          echo esc_html(
            sprintf(
              _n('%d Prize Awarded', '%d Prizes Awarded', $won_count, 'nera-competitions'),
              $won_count,
            ),
          );
          ?>
        </span>
      </div>
    <?php endif; ?>

    <div class="space-y-4">

      <!-- Draw date (replaces countdown) -->
      <?php if ($draw_date): ?>
        <div class="flex items-center gap-1.5 text-text-secondary">
          <span class="material-symbols-outlined text-base opacity-60">calendar_today</span>
          <span class="text-xs font-semibold uppercase tabular-nums">
            <?php
            echo esc_html(
              sprintf(
                /* translators: %s: formatted date */
                __('Drawn: %s', 'nera-competitions'),
                $draw_date,
              ),
            );
            ?>
          </span>
        </div>
      <?php endif; ?>

      <!-- Progress bar (always 100% for closed) -->
      <?php if ($max_tickets > 0): ?>
        <div class="space-y-2">
          <div class="flex justify-between items-center text-xs font-bold">
            <span class="text-text-secondary">
              <?php echo esc_html($sold_tickets); ?>/<?php echo esc_html($max_tickets); ?>
              <?php esc_html_e('sold', 'nera-competitions'); ?>
            </span>
            <span class="text-text-secondary"><?php echo esc_html($progress); ?>%</span>
          </div>
          <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-gray-300 rounded-full" style="width: <?php echo esc_attr($progress); ?>%"></div>
          </div>
        </div>
      <?php endif; ?>

      <!-- View Results CTA -->
      <div class="pt-2">
        <a
          href="<?php echo esc_url($permalink); ?>"
          class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-text-secondary text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-300 no-underline w-full justify-center"
        >
          <span class="material-symbols-outlined text-base">open_in_new</span>
          <?php esc_html_e('View Results', 'nera-competitions'); ?>
        </a>
      </div>

    </div>
  </div>
</article>
