<?php
namespace Nera\Components\TicketPrice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   price_html: string, // required — WC-formatted price HTML (use |raw in twig); derived from $args['price'] (default 0)
 * }
 */
function get_data(array $args = []): array
{
    $price = $args['price'] ?? 0;

    return [
        'price_html' => wc_price($price),
    ];
}
