<?php
namespace Nera\Components\CategoriesCompetitions;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $title    = get_field('categories_section_title') ?: __('Find Your Dream Prize', 'nera-competitions');
    $subtitle = get_field('categories_section_subtitle') ?: __("Browse competitions by category and discover your next big win.", 'nera-competitions');

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'exclude'    => (int) get_option('default_product_cat'),
        'hide_empty' => true,
    ]);

    $category_colors = function_exists('nera_advanced_filter_category_colors')
        ? nera_advanced_filter_category_colors()
        : [];

    $icon_map = [
        'cars'        => 'directions_car',
        'cash'        => 'payments',
        'luxury'      => 'diamond',
        'electronics' => 'devices',
        'travel'      => 'flight',
        'tech'        => 'memory',
        'gadgets'     => 'smartphone',
        'watches'     => 'watch',
        'lifestyle'   => 'spa',
    ];

    $categories  = [];
    $total_count = 0;
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $count = function_exists('nera_count_active_lottery_products_in_category')
                ? nera_count_active_lottery_products_in_category($term->term_id)
                : (int) $term->count;
            if ($count < 1) {
                continue;
            }
            $categories[] = [
                'slug'  => $term->slug,
                'name'  => $term->name,
                'count' => $count,
                'icon'  => $icon_map[$term->slug] ?? 'category',
                'color' => $category_colors[$term->slug] ?? '#1313ec',
            ];
            $total_count += $count;
        }
    }

    $query = new \WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 9,
        'post_status'    => 'publish',
        'orderby'        => 'meta_value',
        'meta_key'       => '_lty_end_date_gmt',
        'order'          => 'ASC',
        'tax_query'      => [
            ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'lottery'],
        ],
        'meta_query'     => function_exists('nera_active_lottery_meta_query')
            ? nera_active_lottery_meta_query()
            : [],
    ]);

    $cards = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id    = get_the_ID();
            $product_terms = wp_get_post_terms($product_id, 'product_cat');
            $slugs         = array_map(fn($t) => $t->slug, is_wp_error($product_terms) ? [] : $product_terms);
            $cats_js       = "['" . implode("','", $slugs) . "']";
            $cards[]       = [
                'product_id'      => $product_id,
                'x_show'          => "activeCategory === 'all' || " . $cats_js . ".includes(activeCategory)",
                'category_colors' => $category_colors,
            ];
        }
        wp_reset_postdata();
    }

    return [
        'title'       => $title,
        'subtitle'    => $subtitle,
        'categories'  => $categories,
        'total_count' => $total_count,
        'cards'       => $cards,
        'has_cards'   => !empty($cards),
    ];
}
