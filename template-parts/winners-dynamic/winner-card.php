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

$title       = isset($row['product_title']) ? (string) $row['product_title'] : '';
$url         = isset($row['product_url']) ? (string) $row['product_url'] : '#';
$image_id    = isset($row['image_id']) ? (int) $row['image_id'] : 0;
$badge       = isset($row['badge_label']) ? (string) $row['badge_label'] : '';
$draw        = isset($row['draw_label']) ? (string) $row['draw_label'] : '';
$ticket      = isset($row['ticket_number']) ? (string) $row['ticket_number'] : '';
$name        = isset($row['winner_name']) ? (string) $row['winner_name'] : '';
$prize       = isset($row['prize_line']) ? (string) $row['prize_line'] : '';
?>

<article
  class="group bg-surface rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 border border-gray-100 flex flex-col sm:flex-row sm:items-stretch h-full">

  <div
    class="relative w-full min-h-[140px] aspect-[4/3] overflow-hidden shrink-0 sm:aspect-auto sm:w-[min(40%,11rem)] sm:max-w-[200px] sm:min-h-[160px] sm:self-stretch">
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

  <div class="p-4 sm:p-5 flex flex-col flex-1 min-w-0 justify-center gap-2 sm:gap-2.5">
    <h2 class="text-base sm:text-lg font-bold text-text-primary leading-snug line-clamp-2">
      <a href="<?php echo esc_url($url); ?>" class="no-underline hover:text-primary transition-colors">
        <?php echo esc_html($title); ?>
      </a>
    </h2>

    <?php if ($draw !== '') : ?>
      <p class="text-xs text-text-secondary leading-relaxed">
        <span class="font-semibold text-text-primary"><?php esc_html_e('Draw', 'nera-competitions'); ?>:</span>
        <?php echo esc_html($draw); ?>
      </p>
    <?php endif; ?>

    <?php if ($ticket !== '' || $name !== '') : ?>
      <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm text-text-primary">
        <?php if ($ticket !== '') : ?>
          <span class="font-mono tabular-nums">
            <span class="text-text-secondary font-sans text-[10px] font-semibold uppercase tracking-wide mr-1.5"><?php esc_html_e('Ticket', 'nera-competitions'); ?></span>
            <?php echo esc_html($ticket); ?>
          </span>
        <?php endif; ?>
        <?php if ($ticket !== '' && $name !== '') : ?>
          <span class="text-text-secondary/40 select-none hidden sm:inline" aria-hidden="true">·</span>
        <?php endif; ?>
        <?php if ($name !== '') : ?>
          <span class="min-w-0 break-words">
            <span class="text-text-secondary font-sans text-[10px] font-semibold uppercase tracking-wide mr-1.5"><?php esc_html_e('Winner', 'nera-competitions'); ?></span>
            <?php echo esc_html($name); ?>
          </span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($prize !== '') : ?>
      <p class="text-sm text-text-secondary line-clamp-3 pt-2 mt-0.5 border-t border-gray-100">
        <?php echo esc_html($prize); ?>
      </p>
    <?php endif; ?>
  </div>
</article>
