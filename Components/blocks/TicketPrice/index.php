<?php
namespace Nera\Components\TicketPrice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   price_html: string, // required — WC-formatted price HTML (use |raw in twig); derived from $args['price'] (default 0)
 *   base_price: float,  // required — numeric unit price for live JS updates
 *   i18n: array<string,string> // required — each, total labels
 * }
 */
function get_data(array $args = []): array
{
    $price = (float) ($args['price'] ?? 0);

    return [
        'price_html' => wc_price($price),
        'base_price' => $price,
        'i18n'       => [
            'label' => __('Ticket Price', 'nera-competitions'),
            'each'  => __('each', 'nera-competitions'),
            'total' => __('Total', 'nera-competitions'),
        ],
    ];
}
