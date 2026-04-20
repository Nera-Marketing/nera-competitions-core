<?php
/**
 * Entry List Card
 *
 * Competition card variant for /giveaway-entry-list/ archive.
 *
 * @package Nera_Competitions
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

$product_id = $product->get_id();
$image_id = $product->get_image_id();
$title = get_the_title($product_id);
$permalink = get_permalink($product_id);
$entry_list_url = function_exists('nera_get_entry_list_url') ? nera_get_entry_list_url($product_id) : '';
if (!$entry_list_url) {
  $entry_list_url = $permalink;
}

$max_tickets = (int) get_post_meta($product_id, '_lty_maximum_tickets', true);
$sold_tickets = method_exists($product, 'get_purchased_ticket_count')
  ? (int) $product->get_purchased_ticket_count()
  : 0;
$progress = $max_tickets > 0 ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 0;

$lottery_status = get_post_meta($product_id, '_lty_lottery_status', true);
$status_labels  = function_exists('lty_get_lottery_statuses') ? lty_get_lottery_statuses() : [];
$status_label   = isset($status_labels[$lottery_status]) && $status_labels[$lottery_status]
  ? $status_labels[$lottery_status]
  : __('Giveaway', 'nera-competitions');

$is_active = in_array($lottery_status, ['lty_lottery_not_started', 'lty_lottery_started'], true);
$status_badge_class = $is_active ? 'bg-success' : 'bg-gray-500';

$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
$countdown_timestamp = $end_date_gmt ? strtotime($end_date_gmt) * 1000 : 0;
$draw_date = $end_date_gmt && function_exists('nera_format_draw_date')
  ? nera_format_draw_date($end_date_gmt)
  : '';

$won_count = function_exists('nera_get_closed_lottery_won_instant_prize_count')
  ? nera_get_closed_lottery_won_instant_prize_count($product_id)
  : 0;

$cta_label = function_exists('lty_get_entry_list_view_participants_label')
  ? lty_get_entry_list_view_participants_label()
  : __('View Participants', 'nera-competitions');
if (!$cta_label) {
  $cta_label = __('View Participants', 'nera-competitions');
}
?>

<article
  class="ncs-entry-list-card group bg-surface rounded-[0.8rem] sm:rounded-[1.2rem] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
  data-product-id="<?php echo esc_attr($product_id); ?>"
  data-entry-fallback-url="<?php echo esc_url($entry_list_url); ?>">
  <div class="relative aspect-5/3 sm:aspect-4/3 overflow-hidden">
    <div class="absolute top-2 left-2 z-10 sm:top-4 sm:left-4 <?php echo esc_attr($status_badge_class); ?> text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full uppercase tracking-widest">
      <?php echo esc_html($status_label); ?>
    </div>

    <a
      href="<?php echo esc_url($entry_list_url); ?>"
      class="block w-full h-full"
      aria-label="<?php echo esc_attr($title); ?>"
      @click.prevent='$dispatch("nera-open-entry-list", { id: <?php echo (int) $product_id; ?>, title: <?php echo wp_json_encode($title); ?> })'
    >
      <?php if ($image_id): ?>
        <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
        <div
          class="w-full h-full bg-center bg-no-repeat bg-cover transform group-hover:scale-110 transition-transform duration-700"
          style="background-image: url('<?php echo esc_url($image_url); ?>');">
        </div>
      <?php else: ?>
        <div class="w-full h-full flex items-center justify-center bg-gray-100">
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

  <div class="p-3 sm:p-5 md:p-6">
    <h3 class="text-sm sm:text-lg font-bold text-text-primary mb-2 sm:mb-3 line-clamp-2">
      <a
        href="<?php echo esc_url($entry_list_url); ?>"
        class="hover:text-primary transition-colors"
        @click.prevent='$dispatch("nera-open-entry-list", { id: <?php echo (int) $product_id; ?>, title: <?php echo wp_json_encode($title); ?> })'
      >
        <?php echo esc_html($title); ?>
      </a>
    </h3>

    <?php if ($won_count > 0 && $lottery_status === 'lty_lottery_finished'): ?>
      <div class="mb-2 sm:mb-3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:gap-1.5 sm:px-3 sm:py-1 bg-success-bg text-success-text rounded-full text-[10px] sm:text-xs font-semibold">
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

    <div class="space-y-2 sm:space-y-4">
      <?php if ($is_active && $countdown_timestamp > 0): ?>
        <div class="flex items-center gap-1 sm:gap-1.5 text-text-secondary"
          x-data="countdown('<?php echo esc_attr($countdown_timestamp); ?>')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" class="opacity-60 shrink-0">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
          <span class="text-[11px] sm:text-xs font-bold uppercase tabular-nums">
            <span x-text="days">00</span>d : <span x-text="hours">00</span>h : <span x-text="minutes">00</span>m : <span x-text="seconds">00</span>s
          </span>
        </div>
      <?php elseif ($draw_date): ?>
        <div
          class="flex items-center gap-1 sm:gap-1.5 text-text-secondary min-w-0"
          aria-label="<?php echo esc_attr(sprintf(__('Drawn %s', 'nera-competitions'), $draw_date)); ?>"
        >
          <span class="material-symbols-outlined text-sm sm:text-base opacity-60 shrink-0" aria-hidden="true">calendar_today</span>
          <span class="text-[11px] sm:text-xs font-semibold uppercase tabular-nums leading-snug">
            <?php echo esc_html($draw_date); ?>
          </span>
        </div>
      <?php endif; ?>

      <div class="space-y-1.5 sm:space-y-2">
        <div class="flex justify-between items-center gap-2 text-[11px] sm:text-xs font-bold">
          <span class="text-text-secondary">
            <?php echo esc_html($sold_tickets); ?>/<?php echo esc_html($max_tickets); ?>
            <?php esc_html_e('sold', 'nera-competitions'); ?>
          </span>
          <span class="text-primary"><?php echo esc_html($progress); ?>%</span>
        </div>
        <div class="h-1.5 sm:h-2 w-full bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-primary rounded-full transition-all duration-500"
            style="width: <?php echo esc_attr($progress); ?>%">
          </div>
        </div>
      </div>

      <div class="pt-1 sm:pt-2">
        <a
          href="<?php echo esc_url($entry_list_url); ?>"
          role="button"
          class="inline-flex items-center gap-1.5 sm:gap-2 bg-primary hover:bg-primary-dark text-white text-xs sm:text-sm font-bold px-3 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl shadow-primary hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 no-underline w-full justify-center min-h-[44px] sm:min-h-0"
          @click.prevent='$dispatch("nera-open-entry-list", { id: <?php echo (int) $product_id; ?>, title: <?php echo wp_json_encode($title); ?> })'
        >
          <span class="material-symbols-outlined text-base">groups</span>
          <?php echo esc_html($cta_label); ?>
        </a>
      </div>
    </div>
  </div>
</article>
