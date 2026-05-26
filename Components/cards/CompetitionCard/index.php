<?php
namespace Nera\Components\CompetitionCard;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    // Support both:
    //   $args['product'] = WC_Product object (from AJAX handler)
    //   $args['product_id'] = int (from loop-based callers)
    // Fall back to current post if neither provided
    if (isset($args['product']) && $args['product'] instanceof \WC_Product) {
        $product = $args['product'];
    } elseif (!empty($args['product_id'])) {
        $product = wc_get_product((int) $args['product_id']);
    } else {
        global $product;
        if (!$product) {
            $product = wc_get_product(get_the_ID());
        }
    }

    if (!$product) return [];

    $product_id = $product->get_id();
    $image_id   = $product->get_image_id();
    $price      = $product->get_price();

    // Category colors
    $category_colors = $args['category_colors'] ?? apply_filters(
        'nera_competition_card_category_colors',
        [
            'cars'        => '#3B82F6',
            'cash'        => '#10B981',
            'luxury'      => '#8B5CF6',
            'electronics' => '#F59E0B',
            'travel'      => '#EC4899',
            'tech'        => '#06B6D4',
            'gadgets'     => '#F97316',
            'watches'     => '#6366F1',
            'lifestyle'   => '#14B8A6',
        ]
    );

    // Lottery meta — prefer Lottery plugin's WC_Product_Lottery subclass API.
    // Falls back to raw postmeta for non-lottery products or if subclass not loaded.
    $max_tickets = method_exists($product, 'get_lty_maximum_tickets')
        ? (int) $product->get_lty_maximum_tickets()
        : (int) get_post_meta($product_id, '_lty_maximum_tickets', true);
    $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
        ? (int) $product->get_purchased_ticket_count()
        : 0;
    $progress  = $max_tickets ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 0;
    $remaining = $max_tickets ? $max_tickets - $sold_tickets : 0;

    // Countdown — prefer Lottery plugin's WC_Product_Lottery subclass API.
    $end_date_gmt = method_exists($product, 'get_lty_end_date_gmt')
        ? $product->get_lty_end_date_gmt()
        : get_post_meta($product_id, '_lty_end_date_gmt', true);
    $end_timestamp_ms  = $end_date_gmt ? strtotime($end_date_gmt) * 1000 : 0;
    $countdown_expired = $end_timestamp_ms && ($end_timestamp_ms < (time() * 1000));
    $countdown_parts   = $end_date_gmt ? nera_get_countdown_parts($end_date_gmt) : ['expired' => true];
    $days_left         = $countdown_parts['days']  ?? 0;
    $hours_left        = $countdown_parts['hours'] ?? 0;

    // Badge
    $badge_text  = '';
    $badge_class = 'bg-gradient-to-r from-danger to-danger-text';
    $is_urgent   = !empty($countdown_parts['urgent']);

    if ($max_tickets && $remaining <= 0) {
        $badge_text = __('Sold Out', 'nera-competitions');
        $is_urgent  = true;
    } elseif ($remaining > 0 && $remaining <= 50) {
        $badge_text = sprintf(__('Last %d Tickets', 'nera-competitions'), $remaining);
        $is_urgent  = true;
    } elseif ($days_left <= 1 && ($days_left > 0 || $hours_left > 0)) {
        $badge_text = __('Ending Soon', 'nera-competitions');
        $is_urgent  = true;
    } elseif ($progress >= 90) {
        $badge_text  = __('Almost Gone', 'nera-competitions');
        $badge_class = 'bg-gradient-to-r from-warning to-warning';
    }

    // Categories
    $product_categories = wp_get_post_terms($product_id, 'product_cat');
    $product_categories = is_wp_error($product_categories) ? [] : $product_categories;
    $category_slugs     = array_map(fn($cat) => $cat->slug, $product_categories);
    $primary_category   = !empty($product_categories) ? $product_categories[0]->slug : '';
    $base_accent_color  = $category_colors[$primary_category]
        ?? apply_filters('nera_competition_card_fallback_accent', '#1313ec');

    // Other categories for tooltip
    $other_cats = array_slice($product_categories, 1);

    // Image URL
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : null;

    // Data attributes (passed through to article element for JS filtering)
    $data_attrs = [
        'price'       => (string) $price,
        'end_date'    => $end_date_gmt ? (string) strtotime($end_date_gmt) : '9999999999',
        'posted_date' => (string) get_the_date('U', $product_id),
        'popularity'  => (string) ($product->get_meta('total_sales') ?: '0'),
        'categories'  => wp_json_encode($category_slugs),
    ];

    return [
        'product_id'       => $product_id,
        'permalink'        => get_permalink($product_id),
        'image_url'        => $image_url,
        'title'            => $product->get_name(),
        'price'            => $price,
        'price_html'       => wc_price($price),
        'accent_color'     => $base_accent_color,
        'badge_text'       => $badge_text,
        'badge_class'      => $badge_class,
        'is_urgent'        => $is_urgent,
        'max_tickets'      => $max_tickets,
        'progress'         => $progress,
        'primary_category' => !empty($product_categories) ? $product_categories[0] : null,
        'other_cats'       => $other_cats,
        'cat_accent'       => $category_colors[$primary_category] ?? $base_accent_color,
        'show_countdown'   => $end_date_gmt && !$countdown_expired,
        'end_timestamp_ms' => $end_timestamp_ms,
        'data_attrs'       => $data_attrs,
        'x_show'           => $args['x_show'] ?? null,
        'extra_attributes' => $args['extra_attributes'] ?? null,
        'sold_tickets'     => $sold_tickets,
        'button_variant'   => $args['button_variant'] ?? 'full',
        'button_mode'      => $args['button_mode']    ?? 'link',
    ];
}
