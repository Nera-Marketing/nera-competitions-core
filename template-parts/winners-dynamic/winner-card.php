<?php
/**
 * Dynamic Winners - Card
 *
 * @package Nera_Competitions
 * @var array $args
 */

if (!defined('ABSPATH')) {
  exit();
}

$row = isset($args['row']) && is_array($args['row']) ? $args['row'] : [];
if ($row === []) {
  return;
}

$show_participants = !empty($args['show_participants_cta']);
$stack_layout      = !empty($args['stack_layout']);
$product_id        = isset($row['product_id']) ? (int) $row['product_id'] : 0;

$title    = isset($row['product_title']) ? (string) $row['product_title'] : '';
$url      = isset($row['product_url']) ? (string) $row['product_url'] : '#';
$image_id = isset($row['image_id']) ? (int) $row['image_id'] : 0;
$badge    = isset($row['badge_label']) ? (string) $row['badge_label'] : '';
$draw     = isset($row['draw_label']) ? (string) $row['draw_label'] : '';
$ticket   = isset($row['ticket_number']) ? (string) $row['ticket_number'] : '';
$name     = isset($row['winner_name']) ? (string) $row['winner_name'] : '';
$prize    = isset($row['prize_line']) ? (string) $row['prize_line'] : '';

$entry_list_url = '';
$cta_label      = '';
if ($show_participants && $product_id > 0) {
  $entry_list_url = function_exists('nera_get_entry_list_url')
    ? nera_get_entry_list_url($product_id)
    : '';
  if (!$entry_list_url) {
    $entry_list_url = $url;
  }
  $cta_label = function_exists('lty_get_entry_list_view_participants_label')
    ? lty_get_entry_list_view_participants_label()
    : __('View Participants', 'nera-competitions');
  if (!$cta_label) {
    $cta_label = __('View Participants', 'nera-competitions');
  }
}

$article_class = $stack_layout
  ? 'ncs-entry-list-card group bg-surface rounded-[0.8rem] sm:rounded-[1.2rem] overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 border border-gray-100 flex flex-col h-full'
  : 'group bg-surface rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 border border-gray-100 flex flex-col sm:flex-row sm:items-stretch h-full';

$content_class = $stack_layout
  ? 'p-3 sm:p-5 md:p-6 flex flex-col flex-1 min-w-0 gap-2 sm:gap-2.5'
  : 'p-3 sm:p-5 flex flex-col flex-1 min-w-0 justify-center gap-1.5 sm:gap-2.5';

$title_tag = $stack_layout ? 'h3' : 'h2';
$title_class = $stack_layout
  ? 'text-sm sm:text-lg font-bold text-text-primary mb-2 sm:mb-3 line-clamp-2'
  : 'text-sm sm:text-lg font-bold text-text-primary leading-snug line-clamp-2';

$separator_class = $stack_layout
  ? 'text-text-secondary/40 select-none'
  : 'text-text-secondary/40 select-none hidden sm:inline';
?>

<article class="<?php echo esc_attr($article_class); ?>">

  <?php if ($stack_layout) : ?>

    <div class="relative aspect-5/3 sm:aspect-4/3 overflow-hidden">
      <div class="absolute top-2 left-2 z-10 sm:top-4 sm:left-4 bg-primary text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full uppercase tracking-widest max-w-[calc(100%-1rem)] truncate">
        <?php echo esc_html($badge); ?>
      </div>

      <a href="<?php echo esc_url($url); ?>" class="block w-full h-full" tabindex="-1" aria-hidden="true">
        <?php if ($image_id) : ?>
          <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
          <div
            class="w-full h-full bg-center bg-no-repeat bg-cover transform group-hover:scale-110 transition-transform duration-700"
            style="background-image: url('<?php echo esc_url($image_url); ?>');">
          </div>
        <?php else : ?>
          <div class="w-full h-full flex items-center justify-center bg-gray-100">
            <span class="material-symbols-outlined text-5xl text-gray-300">emoji_events</span>
          </div>
        <?php endif; ?>
      </a>
    </div>

  <?php else : ?>

    <div
      class="relative w-full min-h-[140px] aspect-4/3 overflow-hidden shrink-0 sm:aspect-auto sm:w-[min(40%,11rem)] sm:max-w-[200px] sm:min-h-[160px] sm:self-stretch">
      <div class="absolute top-3 left-3 z-10 bg-primary text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest max-w-[calc(100%-1.5rem)] truncate">
        <?php echo esc_html($badge); ?>
      </div>

      <a href="<?php echo esc_url($url); ?>" class="absolute inset-0 block" tabindex="-1" aria-hidden="true">
        <?php if ($image_id) : ?>
          <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
          <div
            class="h-full w-full bg-center bg-no-repeat bg-cover transform group-hover:scale-105 transition-transform duration-500"
            style="background-image: url('<?php echo esc_url($image_url); ?>');">
          </div>
        <?php else : ?>
          <div class="h-full w-full flex items-center justify-center bg-gray-100">
            <span class="material-symbols-outlined text-5xl text-gray-300">emoji_events</span>
          </div>
        <?php endif; ?>
      </a>
    </div>

  <?php endif; ?>

  <div class="<?php echo esc_attr($content_class); ?>">
    <<?php echo $title_tag; ?> class="<?php echo esc_attr($title_class); ?>">
      <a href="<?php echo esc_url($url); ?>" class="no-underline hover:text-primary transition-colors">
        <?php echo esc_html($title); ?>
      </a>
    </<?php echo $title_tag; ?>>

    <?php if ($draw !== '') : ?>
      <p class="text-[11px] sm:text-xs text-text-secondary leading-relaxed">
        <span class="font-semibold text-text-primary"><?php esc_html_e('Draw', 'nera-competitions'); ?>:</span>
        <?php echo esc_html($draw); ?>
      </p>
    <?php endif; ?>

    <?php if ($ticket !== '' || $name !== '') : ?>
      <div class="flex flex-wrap items-baseline gap-x-2 sm:gap-x-3 gap-y-1 text-xs sm:text-sm text-text-primary">
        <?php if ($ticket !== '') : ?>
          <span class="font-mono tabular-nums">
            <span class="text-text-secondary font-sans text-[9px] sm:text-[10px] font-semibold uppercase tracking-wide mr-1"><?php esc_html_e('Ticket', 'nera-competitions'); ?></span>
            <?php echo esc_html($ticket); ?>
          </span>
        <?php endif; ?>
        <?php if ($ticket !== '' && $name !== '') : ?>
          <span class="<?php echo esc_attr($separator_class); ?>" aria-hidden="true">·</span>
        <?php endif; ?>
        <?php if ($name !== '') : ?>
          <span class="min-w-0 wrap-break-word">
            <span class="text-text-secondary font-sans text-[9px] sm:text-[10px] font-semibold uppercase tracking-wide mr-1"><?php esc_html_e('Winner', 'nera-competitions'); ?></span>
            <?php echo esc_html($name); ?>
          </span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($prize !== '') : ?>
      <p class="text-xs sm:text-sm text-text-secondary line-clamp-3 pt-2 mt-0.5 border-t border-gray-100">
        <?php echo esc_html($prize); ?>
      </p>
    <?php endif; ?>

    <?php if ($show_participants && $product_id > 0) : ?>
      <div class="pt-2 sm:pt-3 mt-auto">
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
    <?php endif; ?>
  </div>
</article>
