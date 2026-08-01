<?php
namespace Nera\Components\FeaturedCompetitions;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,       // required, default 'Ending Soon' — section heading
 *   subtitle: string,    // required, default 'Grab your tickets before time runs out…' — section subheading
 *   cards: list<array{   // required, default [] — one entry per queried product
 *     product_id: int,
 *     button_variant: string, // always 'compact'
 *   }>,
 *   has_cards: bool,     // required — true when cards is non-empty
 *   show_progress: bool, // required — site-wide Tickets Sold / progress for placeholder cards
 * }
 */
function get_data(array $args = []): array
{
    $title    = nera_component_field($args, 'title',    'featured_title',    __('Ending Soon', 'nera-competitions'));
    $subtitle = nera_component_field($args, 'subtitle', 'featured_subtitle', __("Grab your tickets before time runs out — these competitions are about to close.", 'nera-competitions'));

    $query_args = [
        'post_type'      => 'product',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'tax_query'      => [
            ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'lottery'],
        ],
        'meta_query'     => function_exists('nera_active_lottery_meta_query')
            ? nera_active_lottery_meta_query()
            : [],
        'post__not_in'   => function_exists('nera_sold_out_lottery_ids') ? nera_sold_out_lottery_ids() : [],
    ];
    if (function_exists('nera_wp_query_args_with_catalog_order')) {
        $query_args = nera_wp_query_args_with_catalog_order($query_args);
    } else {
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
    }
    $query = new \WP_Query($query_args);

    $cards = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cards[] = ['product_id' => get_the_ID(), 'button_variant' => 'compact'];
        }
        wp_reset_postdata();
    } else {
        $fallback_args = [
            'post_type'      => 'product',
            'posts_per_page' => 6,
            'post_status'    => 'publish',
        ];
        $fallback_args = function_exists('nera_wp_query_args_with_catalog_order')
            ? nera_wp_query_args_with_catalog_order($fallback_args)
            : array_merge($fallback_args, ['orderby' => 'date', 'order' => 'DESC']);
        $fallback = new \WP_Query($fallback_args);
        if ($fallback->have_posts()) {
            while ($fallback->have_posts()) {
                $fallback->the_post();
                $cards[] = ['product_id' => get_the_ID(), 'button_variant' => 'compact'];
            }
            wp_reset_postdata();
        }
    }

    return [
        'title'         => $title,
        'subtitle'      => $subtitle,
        'cards'         => $cards,
        'has_cards'     => !empty($cards),
        'show_progress' => function_exists('nera_show_tickets_progress')
            ? nera_show_tickets_progress()
            : true,
    ];
}
