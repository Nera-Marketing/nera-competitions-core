<?php
/**
 * Winner Card Template Part
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$winner = $args['winner'] ?? [];
$show_quotes = !empty($args['show_quotes']);

$name = $winner['name'] ?? '';
$prize = $winner['prize'] ?? '';
$date = $winner['date'] ?? '';
$quote = $winner['quote'] ?? '';
$image_url = $winner['image'] ?? '';
$category = $winner['category'] ?? 'live-draw';
$category_label = $winner['category_label'] ?? __('Live Draw', 'nera-competitions');
$category_class = $category === 'instant-win' ? 'bg-accent' : 'bg-primary';
?>

<article class="nera-winner-card group relative h-full flex flex-col bg-surface rounded-xl md:rounded-3xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-500 ease-out border border-gray-100">
    <div class="relative aspect-[4/3] overflow-hidden">
        <div class="absolute top-4 left-4 z-10 <?php echo esc_attr($category_class); ?> text-white text-[10px] font-bold px-2 py-1 md:px-3 md:py-1.5 rounded-full uppercase tracking-widest">
            <?php echo esc_html($category_label); ?>
        </div>

        <?php if ($image_url): ?>
            <div
                class="w-full h-full bg-center bg-no-repeat bg-cover transform group-hover:scale-110 transition-transform duration-700"
                style="background-image: url('<?php echo esc_url($image_url); ?>');"
            ></div>
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary/10 to-primary/5">
                <svg class="w-10 h-10 md:w-16 md:h-16 text-primary/30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-3 md:p-6 flex-1 flex flex-col">
        <h3 class="text-sm md:text-lg font-bold text-text-primary mb-1 md:mb-2">
            <?php echo esc_html($name); ?>
        </h3>

        <p class="text-xs md:text-sm text-primary font-semibold mb-2 md:mb-3">
            <?php echo esc_html($prize); ?>
        </p>

        <?php if ($date): ?>
            <div class="flex items-center gap-1 md:gap-2 text-xs md:text-sm text-text-secondary mb-2 md:mb-4">
                <span class="material-symbols-outlined text-[14px] md:text-[18px]">calendar_today</span>
                <span><?php echo esc_html($date); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($show_quotes && !empty($quote)): ?>
            <div class="hidden md:block pt-4 border-t border-gray-100 mt-auto">
                <div class="relative">
                    <svg class="absolute -top-1 -left-1 w-6 h-6 text-primary/20" fill="currentColor" viewBox="0 0 32 32">
                        <path d="M10 8c-3.3 0-6 2.7-6 6v10h10V14h-6c0-2.2 1.8-4 4-4V8zm16 0c-3.3 0-6 2.7-6 6v10h10V14h-6c0-2.2 1.8-4 4-4V8z" />
                    </svg>
                    <p class="text-sm text-text-secondary italic pl-6 line-clamp-3">
                        <?php echo esc_html($quote); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</article>
