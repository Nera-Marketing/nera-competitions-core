<?php
/**
 * Blog post card (used inside The Loop).
 *
 * @package Nera_Competitions
 *
 * @param string $variant Optional. `featured` (horizontal split) or `standard` (stacked card).
 * @param string $tone    Optional. `dark` for cards on dark sections (standard variant only); default `light`.
 */

if (!defined('ABSPATH')) {
  exit();
}

$variant = isset($variant) && $variant === 'featured' ? 'featured' : 'standard';
$tone_dark = isset($tone) && $tone === 'dark';

$content_raw = get_post_field('post_content', get_the_ID());
$word_count = $content_raw ? str_word_count(wp_strip_all_tags($content_raw)) : 0;
$read_mins = max(1, (int) ceil($word_count / 200));

$categories = get_the_category();
if (!empty($categories)) {
  $primary_cat = $categories[0];
  $category_label = $primary_cat->name;
  $category_url = get_category_link($primary_cat->term_id);
} else {
  $default_cat_id = (int) get_option('default_category');
  $category_label = __('Uncategorized', 'nera-competitions');
  $category_url = $default_cat_id ? get_category_link($default_cat_id) : '';
}

$date_upper = strtoupper(get_the_date('F j, Y'));
$read_label = sprintf(
  /* translators: %d: estimated reading time in minutes */
  _n('%d MIN READ', '%d MIN READ', $read_mins, 'nera-competitions'),
  $read_mins,
);

$card_classes_light =
  'group bg-surface rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-shadow';
$card_classes_dark =
  'group rounded-2xl border border-gray-700/80 bg-gray-800/90 shadow-sm overflow-hidden hover:shadow-md hover:border-gray-600/80 transition-shadow';
if ($variant === 'featured') {
  $card_classes = $card_classes_light;
} else {
  $card_classes = $tone_dark ? $card_classes_dark : $card_classes_light;
}
?>

<?php if ($variant === 'featured'): ?>

<article id="post-<?php the_ID(); ?>" <?php post_class($card_classes . ' flex flex-col lg:flex-row lg:items-stretch'); ?>>
    <div class="relative w-full lg:w-1/2 lg:min-h-[min(22rem,50vw)] min-h-[12rem] shrink-0">
        <?php if ($category_url): ?>
            <a href="<?php echo esc_url($category_url); ?>" class="absolute left-4 top-4 z-10 inline-block rounded-full bg-gray-900/80 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white backdrop-blur-sm">
                <?php echo esc_html(strtoupper($category_label)); ?>
            </a>
        <?php else: ?>
            <span class="absolute left-4 top-4 z-10 inline-block rounded-full bg-gray-900/80 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white backdrop-blur-sm">
                <?php echo esc_html(strtoupper($category_label)); ?>
            </span>
        <?php endif; ?>
        <?php if (has_post_thumbnail()): ?>
            <a href="<?php the_permalink(); ?>" class="block h-full min-h-[12rem] lg:min-h-full">
                <?php the_post_thumbnail('large', [
                  'class' => 'h-full w-full object-cover',
                ]); ?>
            </a>
        <?php else: ?>
            <a href="<?php the_permalink(); ?>" class="flex h-full min-h-[12rem] items-center justify-center bg-gray-200 lg:min-h-full">
                <span class="text-sm text-text-secondary"><?php esc_html_e('No image', 'nera-competitions'); ?></span>
            </a>
        <?php endif; ?>
    </div>

    <div class="flex w-full flex-col justify-center p-6 lg:w-1/2 lg:p-10">
        <div class="mb-3 flex items-center gap-2">
            <?php echo nera_render_author_avatar(get_the_author_meta('ID'), 32, [
              'class' => 'h-8 w-8 rounded-full object-cover',
            ]); ?>
            <span class="text-sm font-semibold text-text-primary"><?php the_author(); ?></span>
        </div>
        <p class="mb-3 text-xs font-medium uppercase tracking-wide text-text-secondary">
            <?php echo esc_html($date_upper); ?>
            <span class="mx-1" aria-hidden="true">•</span>
            <?php echo esc_html($read_label); ?>
        </p>
        <?php the_title(
          '<h2 class="font-heading mb-4 text-2xl font-bold text-text-primary transition-colors group-hover:text-primary md:text-3xl lg:text-4xl"><a href="' .
            esc_url(get_permalink()) .
            '" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">',
          '</a></h2>',
        ); ?>
        <div class="mb-6 line-clamp-4 text-text-secondary">
            <?php the_excerpt(); ?>
        </div>
        <a href="<?php the_permalink(); ?>" class="mt-auto inline-flex w-fit items-center text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
            <?php esc_html_e('Read Article', 'nera-competitions'); ?>
            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</article>

<?php else: ?>

<?php
$meta_cls = $tone_dark
  ? 'mb-2 text-[11px] font-medium uppercase tracking-wide text-gray-400 sm:text-xs'
  : 'mb-2 text-[11px] font-medium uppercase tracking-wide text-text-secondary sm:text-xs';
$title_cls =
  'font-heading mb-3 text-lg font-bold transition-colors sm:text-xl group-hover:text-primary ' .
  ($tone_dark ? 'text-gray-100' : 'text-text-primary');
$title_link_cls = $tone_dark
  ? 'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-gray-800'
  : 'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2';
$excerpt_cls = $tone_dark
  ? 'mb-4 line-clamp-3 flex-1 text-sm text-gray-400'
  : 'mb-4 line-clamp-3 flex-1 text-sm text-text-secondary';
$placeholder_cls = $tone_dark
  ? 'flex h-full items-center justify-center bg-gray-700'
  : 'flex h-full items-center justify-center bg-gray-200';
$placeholder_text_cls = $tone_dark ? 'text-sm text-gray-400' : 'text-sm text-text-secondary';
$author_cls = $tone_dark ? 'text-sm font-semibold text-gray-200' : 'text-sm font-semibold text-text-primary';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class($card_classes . ' flex h-full flex-col'); ?>>
    <div class="relative aspect-video w-full shrink-0 overflow-hidden">
        <?php if ($category_url): ?>
            <a href="<?php echo esc_url($category_url); ?>" class="absolute left-3 top-3 z-10 inline-block rounded-full bg-gray-900/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white backdrop-blur-sm sm:left-4 sm:top-4 sm:px-3 sm:text-xs">
                <?php echo esc_html(strtoupper($category_label)); ?>
            </a>
        <?php else: ?>
            <span class="absolute left-3 top-3 z-10 inline-block rounded-full bg-gray-900/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white backdrop-blur-sm sm:left-4 sm:top-4 sm:px-3 sm:text-xs">
                <?php echo esc_html(strtoupper($category_label)); ?>
            </span>
        <?php endif; ?>
        <?php if (has_post_thumbnail()): ?>
            <a href="<?php the_permalink(); ?>" class="block h-full">
                <?php the_post_thumbnail('medium_large', [
                  'class' => 'h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]',
                ]); ?>
            </a>
        <?php else: ?>
            <a href="<?php the_permalink(); ?>" class="<?php echo esc_attr($placeholder_cls); ?>">
                <span class="<?php echo esc_attr($placeholder_text_cls); ?>"><?php esc_html_e('No image', 'nera-competitions'); ?></span>
            </a>
        <?php endif; ?>
    </div>

    <div class="flex flex-1 flex-col p-6">
        <div class="mb-2 flex items-center gap-2">
            <?php echo nera_render_author_avatar(get_the_author_meta('ID'), 32, [
              'class' => 'h-8 w-8 rounded-full object-cover',
            ]); ?>
            <span class="<?php echo esc_attr($author_cls); ?>"><?php the_author(); ?></span>
        </div>
        <p class="<?php echo esc_attr($meta_cls); ?>">
            <?php echo esc_html($date_upper); ?>
            <span class="mx-1" aria-hidden="true">•</span>
            <?php echo esc_html($read_label); ?>
        </p>
        <?php the_title(
          '<h2 class="' .
            esc_attr($title_cls) .
            '"><a href="' .
            esc_url(get_permalink()) .
            '" class="' .
            esc_attr($title_link_cls) .
            '">',
          '</a></h2>',
        ); ?>
        <div class="<?php echo esc_attr($excerpt_cls); ?>">
            <?php the_excerpt(); ?>
        </div>
        <a href="<?php the_permalink(); ?>" class="mt-auto inline-flex items-center text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
            <?php esc_html_e('Read Article', 'nera-competitions'); ?>
            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</article>

<?php endif; ?>
