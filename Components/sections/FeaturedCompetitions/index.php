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
        'orderby'        => 'meta_value',
        'meta_key'       => '_lty_end_date_gmt',
        'order'          => 'ASC',
        'tax_query'      => [
            ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'lottery'],
        ],
        'meta_query'     => function_exists('nera_active_lottery_meta_query')
            ? nera_active_lottery_meta_query()
            : [],
    ];
    $query = new \WP_Query($query_args);

    $cards = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cards[] = ['product_id' => get_the_ID(), 'button_variant' => 'compact'];
        }
        wp_reset_postdata();
    } else {
        $fallback = new \WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 6,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        if ($fallback->have_posts()) {
            while ($fallback->have_posts()) {
                $fallback->the_post();
                $cards[] = ['product_id' => get_the_ID(), 'button_variant' => 'compact'];
            }
            wp_reset_postdata();
        }
    }

    return [
        'title'     => $title,
        'subtitle'  => $subtitle,
        'cards'     => $cards,
        'has_cards' => !empty($cards),
    ];
}
