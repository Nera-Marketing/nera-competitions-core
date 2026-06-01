<?php
/**
 * Related articles band for single posts (three cards).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$current_id = (int) get_the_ID();
if ($current_id < 1) {
  return;
}

$cat_ids = wp_get_post_categories($current_id);
$post_ids = [];

if (!empty($cat_ids)) {
  $q_cat = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3,
    'post__not_in' => [$current_id],
    'category__in' => $cat_ids,
    'ignore_sticky_posts' => true,
    'fields' => 'ids',
    'no_found_rows' => true,
  ]);
  $post_ids = $q_cat->posts;
  wp_reset_postdata();
}

if (count($post_ids) < 3) {
  $exclude = array_merge([$current_id], $post_ids);
  $q_recent = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3 - count($post_ids),
    'post__not_in' => $exclude,
    'ignore_sticky_posts' => true,
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'ids',
    'no_found_rows' => true,
  ]);
  $post_ids = array_merge($post_ids, $q_recent->posts);
  wp_reset_postdata();
}

$post_ids = array_slice(array_unique(array_map('intval', $post_ids)), 0, 3);

if (empty($post_ids)) {
  return;
}

$related_query = new WP_Query([
  'post_type' => 'post',
  'post__in' => $post_ids,
  'posts_per_page' => 3,
  'orderby' => 'post__in',
]);

$page_for_posts = (int) get_option('page_for_posts');
$journal_url = $page_for_posts ? get_permalink($page_for_posts) : home_url('/');
?>

<section
    class="border-t border-gray-200 py-12 md:py-16"
    aria-label="<?php esc_attr_e('Related articles', 'nera-competitions'); ?>"
>
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                    <?php esc_html_e('More to explore', 'nera-competitions'); ?>
                </p>
                <h2 class="font-heading mt-2 text-2xl font-bold text-text-primary md:text-3xl">
                    <?php esc_html_e('Related Articles', 'nera-competitions'); ?>
                </h2>
            </div>
            <a
                href="<?php echo esc_url($journal_url); ?>"
                class="inline-flex shrink-0 items-center text-sm font-semibold text-primary transition-colors hover:text-primary-dark"
            >
                <?php esc_html_e('View More', 'nera-competitions'); ?>
                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php
            while ($related_query->have_posts()) {
              $related_query->the_post();
              get_template_part('template-parts/blog/post-card', null, [
                'variant' => 'standard',
                'tone' => 'dark',
              ]);
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
